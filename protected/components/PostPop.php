<?php
class PostPop extends CWidget
{
	
	public function init()
	{
		
	}
	
	
	public function run()
	{


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
			if($model->type != 1 && !$model->description){
				$model->addError('description', '请输入要提交的内容');
				echo "<script>post_new();</script>";
			}else if($model->type == 1 && !$model->link){
				$model->addError('link', '请输入要提交的链接');
				echo "<script>post_new();</script>";
			}else if($model->validate()){
				$model->save(false);

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

				if(!$model->thumb_pic){		//说明用户没有自己点推荐链接
					if($model->link){
						//We fill in thumb_pic, etc, in a async way, in ajax. not here.
						//nothing here
					}else if($model->type == 2){	

						preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $model->description, $match);

						if(isset($match[1])){
							$model->thumb_pic = $match[1];
						}

						//注意:上传视频怎么办....
						//$model->video_html = "";

						//如果上述还是没有找到图片
						if(!$model->thumb_pic){
							$model->thumb_pic = ""; 	//avatar? or default content pic?
						}
						$model->save(false);
					}else if($model->type == 3){	//AMA
						$model->thumb_pic = ""; //avatar? or default ama pic?
						$model->save(false);
					}
				}


				$this->controller->redirect('/posts/'.$model->id);
			}else{
				echo "<script>post_new();</script>";
			}
		}

		$this->render('PostPop',array(
			'model'=>$model,
		));
		
	}
	
}