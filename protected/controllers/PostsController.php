<?php
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
				'actions'=>array('create','update', 'getTitle', 'getDupURL', 'vote', 'voteCancel', 'ajaxUpload', 'voteComment', 'voteCommentCancel', 'imageUpload', 'commentimageupload', 'saveTitle', 'VoteAdmin', 'delete', 'undelete'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}



	//ajax image upload for redactor

	//for comment
	public function actionCommentimageupload() {
		$image              = new CommentsPictures;
		$image->create_time = time();
		$image->file        = CUploadedFile::getInstanceByName('file');
		if ($image->validate()) {
			$fileSavePath = "uploads/comments/" . Yii::app()->user->id . "/";
			if (!file_exists($fileSavePath)) {
				mkdir($fileSavePath, 0777, true);
			}
			$date     = date("YmdHis");
			$filename = strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '_', $image->file));
			$image->file->saveAs("uploads/comments/" . Yii::app()->user->id . "/" . $date ."_". $filename);
			$image->path = "/uploads/comments/" . Yii::app()->user->id . "/" . $date ."_". $filename;
			$image->save();
			$pictures = Yii::app()->session['comment_pictures'];
			if (!is_array($pictures)) {
				$pictures = array();
			}
			array_push($pictures, $image->path);
			Yii::app()->session['comment_pictures'] = $pictures; //update the session
			$array  = array(
				'filelink' => "/uploads/comments/" . Yii::app()->user->id . "/" . $date ."_". $filename
			);
			echo stripslashes(json_encode($array));
		} else {
			throw new CHttpException(500, CJSON::encode(array(
				'error' => 'You can only upload images here. If you want to upload  files with other formats, please choose attach files.'
			)));
		}
	}

	//for post
	public function actionImageUpload() {
		$image              = new PostsPictures;
		$image->create_time = time();
		$image->file        = CUploadedFile::getInstanceByName('file');
		if ($image->validate()) {
			$fileSavePath = "uploads/posts/" . Yii::app()->user->id . "/";
			if (!file_exists($fileSavePath)) {
				mkdir($fileSavePath, 0777, true);
			}
			$date     = date("YmdHis");
			$filename = strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '_', $image->file));
			$image->file->saveAs("uploads/posts/" . Yii::app()->user->id . "/" . $date ."_". $filename);
			$image->path = "/uploads/posts/" . Yii::app()->user->id . "/" . $date ."_". $filename;
			$image->save();
			$pictures = Yii::app()->session['pictures'];
			if (!is_array($pictures)) {
				$pictures = array();
			}
			array_push($pictures, $image->path);
			Yii::app()->session['pictures'] = $pictures; //update the session
			$array  = array(
				'filelink' => "/uploads/posts/" . Yii::app()->user->id . "/" . $date ."_". $filename
			);
			echo stripslashes(json_encode($array));
		} else {
			throw new CHttpException(500, CJSON::encode(array(
				'error' => 'You can only upload images here. If you want to upload  files with other formats, please choose attach files.'
			)));
		}
	}


	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{

		$model = $this->loadModel($id);
		$admin = Admins::model()->findByPk(Yii::app()->user->id);

		//只有管理员可以看隐藏的post
		if(!$admin && $model->hide){
			throw new CHttpException(404,'The requested page does not exist.');
		}

		if(Yii::app()->user->id){
			$user = Users::model()->findByPk(Yii::app()->user->id);
			$user->userActed(); 
		}else{
			Users::guestSignup();		//automatically sign up for you
		}

		$model = $this->loadModel($id);
		$model->views++;
		$model->save(false);

		if(Yii::app()->user->id){
			$comment = Comments::model()->findByAttributes(array('user_id'=>Yii::app()->user->id, 'post_id'=>$model->id));
			$user = Users::model()->findByPk(Yii::app()->user->id);
			$user->userActed(); 
		}else{
			$comment = new Comments;
		}
		if(!$comment){
			$comment = new Comments;
			$comment->post_id = $model->id;
		}
		if(isset($_POST['Comments']) && Yii::app()->user->id){
			$comment->attributes = $_POST['Comments'];
			$comment->user_id = Yii::app()->user->id;
			if($comment->isNewRecord){
				$comment->create_time = time();
			}else{
				$comment->edited = time();
			}
			if($comment->save()){

				if($comment->isNewRecord){
					$model->comments++;
					$model->save(false);
				}

				$pictures = Yii::app()->session['comment_pictures'];
				if ($pictures) {
					foreach ($pictures as $picture => $pic) {
						$image = CommentsPictures::model()->findByAttributes(array(
							'path' => $pic
						));
						$image->comment_id = $model->id;
						$image->save();
					}
				}
				unset(Yii::app()->session['comment_pictures']);

			}
		}



		$dataProvider = new CActiveDataProvider('Comments', array(
			'criteria' => array(
				'select'=>'*, commentrank(up, CAST(down as decimal(18,7))) as rank',
				'condition' => 'post_id = :pid',
				'params'=>array(
					"pid" => $model->id
				),
			),
			'sort' => array(
				'defaultOrder' => 'rank DESC, create_time DESC' // this is it.
			),
			'pagination' => array(
				'pageSize' => 10
			)
		));
		
		if($model->type == 1){
			$this->render('linkview',array(
				'model'=>$model,
				'comment'=>$comment,
				'dataProvider'=>$dataProvider,
				'admin'=>$admin,
			));
		}else{
			$this->render('view',array(
				'model'=>$model,
				'comment'=>$comment,
				'dataProvider'=>$dataProvider,
				'admin'=>$admin,
			));
		}
	}



	//admin vote. pass any number and change up directly.
	public function actionVoteAdmin(){
		$admin = Admins::model()->findByPk(Yii::app()->user->id);
		if(!$admin){
			return;
		}
		if(isset($_POST['post_id']) && isset($_POST['up'])){
			$post = Posts::model()->findByPk($_POST['post_id']);
			if($post){
				$post->up = $_POST['up'] + $post->down;	//offset down vote
				$post->save(false);
				echo "success";	
				return;
			}
		}
		return;
	}


	/**

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
	 * Comment Vote ajax function
	 */
	public function actionVoteComment(){
		if(isset($_POST['comment_id']) && isset($_POST['type'])){
			$comment = Comments::model()->findByPk($_POST['comment_id']);
			$user = Users::model()->findByPk(Yii::app()->user->id);
			if($comment && $user && $comment->user_id != $user->id){
				$already = CommentsVotes::model()->findByAttributes(array('comment_id'=>$comment->id, 'user_id'=>$user->id));
				if($already){
					if($already->type != $_POST['type']){
						$already->type = $_POST['type'];
						$already->create_time = time();
						$already->save(false);
						if($already->type == 1){
							$comment->up++;		//+=2 would be more accurate?
						}else{
							$comment->down++;		//+=2 would be more accurate?
						}
						$comment->save(false);
					}
				}else{
					$vote = new CommentsVotes;
					$vote->type = $_POST['type'];
					$vote->comment_id = $comment->id;
					$vote->user_id = $user->id;
					$vote->create_time = time();
					$vote->save(false);
					if($vote->type == 1){
						$comment->up++;
					}else{
						$comment->down++;
					}
					$comment->save(false);
				}
				echo "success";	
				return;		
			}
		}
		echo "error";
		return;
	}

	/**
	 * Vote ajax cancel function
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
	 * Comment Vote ajax cancel function
	 */
	public function actionVoteCommentCancel(){
		if(isset($_POST['comment_id']) && isset($_POST['type'])){
			$comment = Comments::model()->findByPk($_POST['comment_id']);
			$user = Users::model()->findByPk(Yii::app()->user->id);
			if($comment && $user && $comment->user_id != $user->id){
				$already = CommentsVotes::model()->findByAttributes(array('comment_id'=>$comment->id, 'user_id'=>$user->id, 'type'=>$_POST['type']));
				if($already){
					$already->delete();
					if($already->type == 1){
						$comment->up--;
					}else{
						$comment->down--;
					}
					$comment->save(false);
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
	 * grab title, pic and video from the link
	 */
	public function actionGetTitle($url){
		echo Posts::getTitle($url);
	}

	//接受ajax，非同步获取保存图片
	public function actionSaveTitle(){	//太慢了 需要优化。ajax保存的时候点别的链接反应很慢。
		$model = "";
		if(isset($_GET['id'])){
			$model = Posts::model()->findByPk($_GET['id']);
		}
		$array = array();
		if($model){
			$json = Posts::getTitle($model->link);
			$array = json_decode($json);
			if(isset($array[1])){
				$model->thumb_pic = $array[1];
			}
			if(isset($array[2])){
				$model->video_html = $array[2];
			}
			$model->save(false);
		}
		echo $json;
	}


	public function actionAjaxUpload()
	{

        Yii::import("ext.EAjaxUpload.qqFileUploader");
 
        $folder='uploads/posts/'.Yii::app()->user->id.'/'; // folder for uploaded files

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

 	$thumb = new Imagick("uploads/posts/" . Yii::app()->user->id . "/" . $filename);
        $thumb->setImageFormat("png");
        $thumb->thumbnailImage(180, 180);

        if (file_exists("uploads/posts/" . Yii::app()->user->id . "/" . $filename)) {
        	unlink("uploads/posts/" . Yii::app()->user->id . "/" . $filename);
        }
        $thumb->writeImage("uploads/posts/" . Yii::app()->user->id . "/" . $filename);                  
       
        echo $return;// it's array
	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{	
		//handled by widget PostPop
		$user = Users::model()->findByPk(Yii::app()->user->id);
		$user->userActed(); 
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
	 * @param integer $id the ID of the model to be hidden
	 */
	public function actionDelete()
	{
		$admin = Admins::model()->findByPk(Yii::app()->user->id);
		if(!$admin){
			return;
		}
		if(isset($_POST['post_id'])){
			$model = Posts::model()->findByPk($_POST['post_id']);
			if($model){
				$model->hide = 1;
				$model->save(false);
				echo "success";	
				return;
			}
		}
		return;
	}



	/**
	 * Restores a particular model.
	 * @param integer $id the ID of the model to be shown again
	 */
	public function actionUnDelete()
	{
		$admin = Admins::model()->findByPk(Yii::app()->user->id);
		if(!$admin){
			return;
		}
		if(isset($_POST['post_id'])){
			$model = Posts::model()->findByPk($_POST['post_id']);
			if($model){
				$model->hide = 0;
				$model->save(false);
				echo "success";	
				return;
			}
		}
		return;
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		if(Yii::app()->user->id){
			$user = Users::model()->findByPk(Yii::app()->user->id);
			$user->userActed(); 
		}else{
			Users::guestSignup();		//automatically sign up for you
		}
		$admin = Admins::model()->findByPk(Yii::app()->user->id);
		if(isset($_GET['category_id'])){
			$criteria = array(
				'select'=>'*, postrank(up, down, CAST(create_time as decimal(18,7))) as rank',
				'condition'=>'hide = 0 AND category_id = :cid',
				'params'=>array(
					':cid'=>$_GET['category_id']
				)
			);
		}else{
			$criteria = array(
				'select'=>'*, postrank(up, down, CAST(create_time as decimal(18,7))) as rank',
				'condition'=>'hide = 0',
			);
		}

		$dataProvider = new CActiveDataProvider('Posts', array(
			'criteria' => $criteria,
			'sort' => array(
				'defaultOrder' => 'rank DESC, create_time DESC' // this is it.
			),
			'pagination' => array(
				'pageSize' => 15
			)
		));

		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'admin'=>$admin
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
