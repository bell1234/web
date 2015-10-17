<?php
class PostPop extends CWidget
{
	
	public function init()
	{
		
	}
	
	
	public function run()
	{

		if(Yii::app()->user->isGuest){
			exit(0);
		}

		$user = Users::model()->findByPk(Yii::app()->user->id);

		$model=new Posts;

		// Uncomment the following line if AJAX validation is needed

		if(isset($_POST['ajax']) && $_POST['ajax']==='posts-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if(isset($_POST['Posts']))
		{
			$model->attributes=$_POST['Posts'];
			$model->name = strip_tags($model->name); 	//净化tags
			$model->create_time = time();
			$model->user_id = Yii::app()->user->id;
			$model->points = 0;	//starting with 0 points?
			if($model->type == 1){		//link
				//nothing
			}else if($model->type == 2){	//content
				$model->link = "";

			}else if($model->type == 3){	//ama
				$model->link = "";
				$model->category_id = 4;	//force AMA
			}
			if(!$model->thumb_pic){		//说明用户没有自己点推荐链接
				if($model->link){
					$json = Posts::getTitle($model->link);
					$arr = json_decode($json);
					if(isset($arr['1'])){	//thumb_pic
						$model->thumb_pic = $arr['1'];
					}
					if(isset($arr['2'])){	//video_html
						$model->video_html = $arr['2'];
					}
				}else if($model->type == 2){	
					$pictures = Yii::app()->session['pictures'];
					if($pictures){
						foreach ($pictures as $picture => $pic) {
							$image = PostsPictures::model()->findByAttributes(array(
								'path' => $pic
							));
							if($image){
								$model->thumb_pic = $image->path;
								break;
							}
						}
					}
					//如果上述还是没有找到图片
					if(!$model->thumb_pic){
						$model->thumb_pic = ""; 	//avatar? or default content pic?
					}
				}else if($model->type == 3){	//AMA
					$model->thumb_pic = ""; //avatar? or default ama pic?
				}
			}

			if($model->type != 1 && !$model->description){
				$model->addError('description', '请输入要提交的内容');
			}else if($model->save()){
				$pictures = Yii::app()->session['pictures'];
				if ($pictures) {
					foreach ($pictures as $picture => $pic) {
						$image = PostsPictures::model()->findByAttributes(array(
							'path' => $pic
						));
						if($image){
							$image->post_id = $model->id;
							$image->save();
						}
					}
				}
				unset(Yii::app()->session['pictures']);
				$this->controller->redirect(array('view','id'=>$model->id));
			}
		}

		$this->render('PostPop',array(
			'model'=>$model,
		));
		
	}
	
}