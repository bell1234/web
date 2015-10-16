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
		$this->controller->performAjaxValidation($model);

		if(isset($_POST['Posts']))
		{
			$model->attributes=$_POST['Posts'];
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
			if(!$model->thumb_pic){
				if($model->private){
					//default pic here...
				}else{
					$model->thumb_pic = $user->avatar;
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
						$image->post_id = $model->id;
						$image->save();
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