<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	public function actionNotifications(){
	  $user = Users::model()->findByPk(Yii::app()->user->id);
	  if(!$user){
	  		$this->redirect('/');
	  }
      $notifications=new CActiveDataProvider('Notification', array(
        'criteria'=>array(
            'condition'=>'receiver = :user_id',
            'params'=> array(':user_id'=>Yii::app()->user->id),
            'order'=>'create_time DESC',
            'offset' => 0,
        ),
        'pagination' => array('pageSize' =>40),
      ));
      $this->render('notifications',array('notifications'=>$notifications));
	}


	public function actionNotification(){
		$notifications = Notification::model()->findAllByAttributes(array('receiver'=>Yii::app()->user->id, 'read'=>0));
		foreach($notifications as $noti):
			$noti->read = 1;
			$noti->save(false);
		endforeach;
		echo 200;
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$this->redirect('/');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','多谢您联系没六儿，我们的客服人员会尽快给您回复。');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}


	/**
	 * 激活邮箱
	 */
	public function actionActivation(){
		if(!isset($_GET['email']) || !isset($_GET['activkey'])){
			throw new CHttpException(400, '该激活链接无效，请复制粘贴邮件中的完整地址至浏览器地址栏中');
		}
		$email = $_GET['email'];
		$activkey = $_GET['activkey'];
		if ($email && $activkey) {
			$find = Users::model()->findByAttributes(array('email'=>$email, 'activkey'=>$_GET['activkey'], 'status'=>0));
			if($find){
				$find->status = 1;
				$find->save(false);
				$find->userActed();
				$find->saveDupStats();

				$model = new LoginForm;
				$model->username = $find->email;
				$model->password = $find->password;
				$model->rememberMe = 1;
				if($model->validate() && $model->login()){
					$this->redirect('/');
				}
			}
		}
		throw new CHttpException(404, '该激活链接无效，请复制粘贴邮件中的完整地址至浏览器地址栏中');
	}



    /**
     * 修改密码
     */
    public function actionChangepassword()
    {
        $model = new UserChangePassword;
        if (Yii::app()->user->id) {
            
            // ajax validator
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'changepassword-form') {
                echo UActiveForm::validate($model);
                Yii::app()->end();
            }
            
            if (isset($_POST['UserChangePassword'])) {
                $model->attributes = $_POST['UserChangePassword'];
                if ($model->validate()) {
                    $find           = Users::model()->findByPk(Yii::app()->user->id);
                    $find->password = hash('sha256', $model->password);
                    $find->activkey= hash('sha256', microtime() . $find->username);
                    $find->save();
                    Yii::app()->user->setFlash('recoveryMessage', "您的新密码已经保存");
                    $this->refresh();
                }
            }
            $this->render('changePassword', array(
                'form' => $model
            ));
        }else{
		throw new CHttpException(400, '请先登录');
	}
    }

	/**
	 * 找回密码
	 */
	public function actionForgetPassword(){

		$form = new UserRecoveryForm;

				$email = ((isset($_GET['email']))?$_GET['email']:'');
				$activkey = ((isset($_GET['activkey']))?$_GET['activkey']:'');
				if ($email && $activkey) {
					$form2 = new UserChangePassword;
		    			$find = Users::model()->findByAttributes(array('email'=>$email));
		    			if(isset($find) && $find->activkey == $activkey) {
			    			if(isset($_POST['UserChangePassword'])) {
							$form2->attributes=$_POST['UserChangePassword'];
							if($form2->validate()) {
								$find->password = hash('sha256', $form2->password);
								$find->activkey= hash('sha256', microtime() . $find->username);

								if ($find->status==0) {
									$find->status = 1;
								}
								$find->save();
								Yii::app()->user->setFlash('recoveryMessage', "<p>您的新密码已保存</p><p>请<a href='/site/login'>点击这里登陆</a></p>");
								$this->redirect('/site/forgetpassword');
							}
						} 
						$this->render('changePassword',array('form'=>$form2));
		    			} else {
		    				throw new CHttpException(404, '该找回密码链接无效，请复制粘贴邮件中的完整地址至浏览器地址栏中');
		    			}
		    		} else {
			    		if(isset($_POST['UserRecoveryForm'])) {
			    			$form->attributes=$_POST['UserRecoveryForm'];
			    			if($form->validate()) {
			    				$user = Users::model()->findbyPk($form->user_id);
							$activation_url = Yii::app()->params['url'].'/site/forgetpassword/activkey/'.$user->activkey.'/email/'.$user->email;
							
							$message = new YiiMailMessage;
							$message->view = 'forgetpassword';
							$username = $user->username;
							$message->setBody(array('username'=>$user->username,'act_link'=>$activation_url), 'text/html');
							$message->setSubject('找回密码 - 没六儿');
							$message->addTo($user->email);
							$message->setFrom(array(
								'no-reply@meiliuer.com' => '没六儿'
							));
							Yii::app()->mail->send($message);
							Yii::app()->user->setFlash('recoveryMessage', "没六儿已经向您的邮箱发送了一封帮您重置密码的邮件，请查收。");
			    				$this->refresh();
			    			}
			    		}
		    			$this->render('forgetPassword',array('form'=>$form));
		    		}
	}


	public function actionSignup()
	{
		$this->redirect('login');
	}


	/**
	 * Displays the login & sign up page
	 */
	public function actionLogin()
	{
		// display the login form. everything's handled by widget LoginPop in components
		$this->render('login');
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		if(Yii::app()->user->id){
			//$user = Users::model()->findByPk(Yii::app()->user->id);
			//if($user && !$user->auto){
				Yii::app()->user->logout();
			//}
		}
		$this->redirect(Yii::app()->homeUrl);
	}
}