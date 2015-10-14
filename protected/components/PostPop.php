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

		$model=new Posts;

		// Uncomment the following line if AJAX validation is needed
		$this->controller->performAjaxValidation($model);

		if(isset($_POST['Posts']))
		{
			$model->attributes=$_POST['Posts'];
			$model->create_time = time();
			$model->user_id = Yii::app()->user->id;
			$model->points = 0;	//starting with 0 points?
			if($model->type == 1){

			}else{
				$model->link = "";	//not link post, let's clear it just in case it's passed.
				//text 2
				//with picture 3
				//with video 4
				//...
			}	
			if($model->type != 1 && !$model->description){
				$model->addError('description', '请输入要分享的内容');
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