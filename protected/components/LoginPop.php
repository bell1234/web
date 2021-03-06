<?php
class LoginPop extends CWidget
{

	public function init()
	{
		
	}
	
	
	public function run()
	{

		if(!Yii::app()->user->isGuest){
			$user = Users::model()->findByPk(Yii::app()->user->id);
			if(!$user->auto){
				$this->controller->redirect('/');
			}
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
			echo CActiveForm::validate($user);
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
				if($_SERVER['REQUEST_URI'] == '/site/login' || $_SERVER['REQUEST_URI'] == '/site/login/'){
					$this->controller->redirect('/');
				}else{
					$this->controller->refresh();
				}

			}else{
				echo "<script>signup(); setTimeout(show_login, 3);</script>";
			}
		}

		// sign up partin
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
				$user->create_time = time();
				$user->status = 0; //not verify email
				$user->activkey = hash('sha256', microtime() . $user->username);	//sha256 email + microtome for activkey
				$user->save(false);	//after validation, so we can save(false).

				$user->userActed(); 	//update IP, location
				$user->saveDupStats(); //save user dup accounts, cookie etc.

				//$invite = Invitation::model()->findByAttributes(array('code' => $user->invitation));
				//if($invite){
				//	//$invite->delete();
				//}
				if($user->email){
					$activation_url = $this->controller->createAbsoluteUrl('/site/activation', array(
						"activkey" => $user->activkey,
						"email" => $user->email
					));

					try{
					//send out verification emails here...
					$message = new YiiMailMessage;
                           		$message->view = 'signup';    //layout name
                            		$message->setBody(array(    //variable you want to pass
                                		'username' => $user->username,
                                		'act_link'=>$activation_url,
                           		 ), 'text/html');
					$message->setSubject('欢迎加入没六儿');
					$message->addTo($user->email);
					$message->setFrom(array(
						'no-reply@meiliuer.com' => '没六儿'
					));
					$message->setReplyTo($user->email);
					Yii::app()->mail->send($message);
					} catch (Exception $e) {}
				}

				$model->username = $user->username;
				$model->password = $_POST['Users']['password'];
				$model->rememberMe = 1;
				if($model->validate() && $model->login()){
					$this->controller->redirect('/');
				}else{
					echo "<script>signup(); setTimeout(show_signup, 1);</script>";
				}
			}else{
				echo "<script>signup(); setTimeout(show_signup, 1);</script>";
			}
		}

		$this->render('LoginPop', array(
			'model' => $model,
			'user' => $user
		));

	}
	
}