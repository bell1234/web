<?php
Yii::import('ext.PHPDomParser.simple_html_dom', true);

class PostsController extends Controller
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
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update', 'parseLink', 'getTitle', 'getDupURL', 'vote', 'voteCancel'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}


	/**
	 * Vote ajax function
	 */
	public function actionVote(){
		if(isset($_POST['post_id']) && isset($_POST['type'])){
			$post = Posts::model()->findByPk($_POST['post_id']);
			$user = Users::model()->findByPk(Yii::app()->user->id);
			if($post && $user && $post->user_id != $user->id){
				$already = PostsVotes::model()->findByAttributes(array('post_id'=>$post->id, 'user_id'=>$user->id));
				if($already){
					if($already->type != $_POST['type']){
						$already->type = $_POST['type'];
						$already->create_time = time();
						$already->save(false);
						if($already->type == 1){
							$post->up++;		//+=2 would be more accurate?
						}else{
							$post->down++;		//+=2 would be more accurate?
						}
						$post->save(false);
					}
				}else{
					$vote = new PostsVotes;
					$vote->type = $_POST['type'];
					$vote->post_id = $post->id;
					$vote->user_id = $user->id;
					$vote->create_time = time();
					$vote->save(false);
					if($vote->type == 1){
						$post->up++;
					}else{
						$post->down++;
					}
					$post->save(false);
				}
				echo "success";	
				return;		
			}
		}
		echo "error";
		return;
	}



	/**
	 * Vote ajax function
	 */
	public function actionVoteCancel(){
		if(isset($_POST['post_id']) && isset($_POST['type'])){
			$post = Posts::model()->findByPk($_POST['post_id']);
			$user = Users::model()->findByPk(Yii::app()->user->id);
			if($post && $user && $post->user_id != $user->id){
				$already = PostsVotes::model()->findByAttributes(array('post_id'=>$post->id, 'user_id'=>$user->id, 'type'=>$_POST['type']));
				if($already){
					$already->delete();
					if($already->type == 1){
						$post->up--;
					}else{
						$post->down--;
					}
					$post->save(false);
					echo "success";	
					return;	
				}	
			}
		}
		echo "error";
		return;
	}


	/**
	 * grab duplicate URL
	 */
	public function actionGetDupURL($url){
		$old = Posts::model()->findByAttributes(array('link'=>$url));
		if($old){
			echo Yii::app()->request->hostInfo."/posts/".$old->id; 
		}else{
			echo "";
		}
	}
	


	/**
	 * grab title from the link
	 */
	public function actionGetTitle($url){

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (compatible; MSIE 8.0)');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$data = curl_exec($ch);
		curl_close($ch);
		$pos = strpos($data,'utf-8');
		if($pos===false){$data = iconv("gbk","utf-8",$data);}
		preg_match("/<title>(.*)<\/title>/i",$data, $title);
		if($title){
			echo $title[1];
		}else{
			return "error";
		}
	}


	/**
	 * grab picture from the link
	 */
	public function actionParseLink()
	{
		//grab pictures..

   		$post_html = file_get_html($html);

		/*
		//first picture
    		$first_img = $post_html->find('img', 0);

    		if($first_img !== null) {
        		return url_to_absolute($first_img->src);
    		}

		//all pictures
		foreach($html->find('img') as $element) {
			return url_to_absolute($url, $element->src);
		}
		*/


	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{	
		//handled by widget PostPop
		$this->render('create');
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

		if(isset($_POST['Posts']))
		{
			$model->attributes=$_POST['Posts'];
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
		$dataProvider=new CActiveDataProvider('Posts');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Posts('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Posts']))
			$model->attributes=$_GET['Posts'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Posts the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Posts::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Posts $model the model to be validated
	 */
	public function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='posts-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
