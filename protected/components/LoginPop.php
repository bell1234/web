<?php
class LoginPop extends CWidget
{
	
	public function init()
	{
		
	}
	
	
	public function run()
	{


		if(!Yii::app()->user->isGuest){
			$this->controller->redirect('/');
		}

		$user = new Users;
		
		$model= new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='signup-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// login part
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login()){
				$user = Users::model()->findByPk(Yii::app()->user->id);
				$user->logins++;
				$user->save(false);
				$this->controller->redirect('/');

			}
		}

		// sign up part
		if(isset($_POST['Users']))
		{
			$user->attributes=$_POST['Users'];
			if($user->validate()){ //validate
				if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {		//cloudflare for now, maybe Baidu in the future.
					$user->ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
				} else {
					$user->ip = $_SERVER['REMOTE_ADDR'];
				}
				$user->password = hash('sha256', $user->password);	//sha 256, no salt for now.
				$user->create_time = $user->lastaction;
				$user->userActed(); 	//update IP, location
				$user->saveDupStats(); //save user dup accounts, cookie etc.
				$user->status = 0; //not verify email
				$user->activkey = hash('sha256', microtime() . $model->email);	//sha256 email + microtome for activkey
				$user->save(false);	//after validation, so we can save(false).

				//send out verification emails here...

				$this->controller->redirect('/');
			}
		}

		$this->render('LoginPop', array(
			'model' => $model,
			'user' => $user
		));

		
	}
	
}