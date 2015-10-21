<?php

class UsersController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('setting', 'ajaxUpload'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete', 'create', 'update', 'index'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	//Ajax 上传头像
	public function actionAjaxUpload()
	{

        Yii::import("ext.EAjaxUpload.qqFileUploader");
 
        $folder='uploads/avatars/'.Yii::app()->user->id.'/'; // folder for uploaded files

        if (!file_exists ($folder)){
            mkdir ($folder, 0777, true);
        }

        $allowedExtensions = array('jpg','png','gif','jpeg','tiff','tif','bmp');
        $sizeLimit = 5 * 1024 * 1024;	// maximum file size in bytes
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload($folder);
        $return = htmlspecialchars(json_encode($result), ENT_NOQUOTES);
 
        $fileSize=filesize($folder.$result['filename']);//GETTING FILE SIZE
        $fileName=$result['filename'];//GETTING FILE NAME
	$filename = $fileName;

 	$thumb = new Imagick("uploads/avatars/" . Yii::app()->user->id . "/" . $filename);
        $thumb->setImageFormat("png");
        $thumb->thumbnailImage(200, 200);

        if (file_exists("uploads/avatars/" . Yii::app()->user->id . "/" . $filename)) {
        	unlink("uploads/avatars/" . Yii::app()->user->id . "/" . $filename);
        }
        $thumb->writeImage("uploads/avatars/" . Yii::app()->user->id . "/" . $filename);                  
       
	$user = Users::model()->findByPk(Yii::app()->user->id);
	$user->avatar = "/uploads/avatars/" . Yii::app()->user->id . "/" . $filename;
	$user->save();

        echo $return;// it's array
	}



	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{

		$model = $this->loadModel($id);

		if($model->id == Yii::app()->user->id){
			$criteria = array(
				'select'=>'*, postrank(up, down, CAST(create_time as decimal(18,7))) as rank',
				'condition'=>'user_id = :uid',
				'params'=>array(
					':uid'=>$model->id
				)
			);
		}else{
			$criteria = array(
				'select'=>'*, postrank(up, down, CAST(create_time as decimal(18,7))) as rank',
				'condition'=>'hide = 0 AND user_id = :uid',
				'params'=>array(
					':uid'=>$model->id
				)
			);

		}

		$dataProvider = new CActiveDataProvider('Posts', array(
			'criteria' => $criteria,
			'sort' => array(
				'defaultOrder' => 'rank DESC, create_time DESC' // this is it.
			),
			'pagination' => array(
				'pageSize' => 10
			)
		));

		$this->render('view',array(
			'model'=>$model,
			'dataProvider'=>$dataProvider,
		));
	}


	/**
	 * 账户设置
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionSetting()
	{
		$model = $this->loadModel(Yii::app()->user->id);

		if(isset($_POST['Users']))
		{
			$model->attributes=$_POST['Users'];
			$model->auto = 0;

			if($model->save()){
				Yii::app()->user->setFlash('settingMessage', "您的设置已经保存");
				$this->refresh();
			}
		}		

		$this->render('setting',array(
			'model'=>$model,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Users;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Users']))
		{
			$model->attributes=$_POST['Users'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Users']))
		{
			$model->attributes=$_POST['Users'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Users');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Users('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Users']))
			$model->attributes=$_GET['Users'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Users the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Users::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Users $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='users-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
