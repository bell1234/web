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

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		if(!Yii::app()->user->isGuest){
			$model = Users::model()->findByPk(Yii::app()->user->id);
			$model->userActed(); 	//get lastaction time and location updated
		}
		$this->render('index');
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
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}


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
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}