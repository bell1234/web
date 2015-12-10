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
			if($user->isBanned()){
				throw new CHttpException(404,'您的账户已经被停权。');
			}
		}
		//else{	Users::guestSignup(); }		//automatically sign up for you
		

		$model = $this->loadModel($id);
		$model->views++;
		$model->save(false);

		if(Yii::app()->user->id){
			//$comment = Comments::model()->findByAttributes(array('user_id'=>Yii::app()->user->id, 'post_id'=>$model->id));
			$user = Users::model()->findByPk(Yii::app()->user->id);
			$user->userActed(); 
		}else{
			$comment = new Comments;
		}
		//if(!$comment){
		$comment = new Comments;
		$comment->post_id = $model->id;
		//}

		$reply = new Reply;
		$reply->user_id = Yii::app()->user->id;
		if(isset($_POST['Reply']) && Yii::app()->user->id){
			$reply->attributes = $_POST['Reply'];
			$reply->create_time = time();
			$reply->description = strip_tags($reply->description);
			if($reply->save()){
				$model->comments++;
				$model->save(false);
				if($reply->receiver != Yii::app()->user->id){
					$noti = Notification::model()->findByAttributes(array('post_id'=>$model->id, 'receiver'=>$reply->receiver, 'type_id'=>3));
					if($noti){
						$noti->sender = Yii::app()->user->id;
						$noti->other++;
						$noti->post_id = $model->id;
						$noti->create_time = time();
						$noti->read = 0;
						$noti->save();
						$notificationData = array(
							"user_id"=>$noti->receiver,
							"title"=>$noti->senderx->username." 和另外".$noti->other."人在下列发布中回复了您的评论: ".$model->name,
						);
						Notification::model()->sendiOSNotification($notificationData);
					}else{
						$noti = new Notification;
						$noti->type_id = 3; //reply
						$noti->post_id = $model->id;
						$noti->sender = Yii::app()->user->id;
						$noti->receiver = $reply->receiver;
						$noti->create_time = time();
						$noti->save();
						$notificationData = array(
							"user_id"=>$noti->receiver,
							"title"=>$noti->senderx->username." 在下列发布中回复了您的评论: ".$model->name,
						);
						Notification::model()->sendiOSNotification($notificationData);
					}
				}
			}
		}

		if(isset($_POST['Comments']) && Yii::app()->user->id){
			$comment->attributes = $_POST['Comments'];
			$comment->user_id = Yii::app()->user->id;
			if($comment->isNewRecord){
				$comment->create_time = time();
			}else{
				$comment->edited = time();
			}
			if($comment->isNewRecord){
				$model->comments++;
			}
			$comment->description = strip_tags($comment->description);
			$comment->description = nl2br($comment->description);
			
			if($comment->save()){
				$model->save(false);

				if($comment->user_id != $model->user_id){
					$noti = Notification::model()->findByAttributes(array('post_id'=>$model->id, 'receiver'=>$model->user_id, 'type_id'=>2));
					if($noti){
						$noti->sender = Yii::app()->user->id;
						$noti->other++;
						$noti->post_id = $model->id;
						$noti->create_time = time();
						$noti->read = 0;
						$noti->save();
						$notificationData = array(
							"user_id"=>$noti->receiver,
							"title"=>$noti->senderx->username." 和另外".$noti->other."人评论了您的发布: ".$model->name,
						);
						Notification::model()->sendiOSNotification($notificationData);
					}else{
						$noti = new Notification;
						$noti->type_id = 2; //comment
						$noti->post_id = $model->id;
						$noti->sender = Yii::app()->user->id;
						$noti->receiver = $model->user_id;
						$noti->create_time = time();
						$noti->save();
						$notificationData = array(
							"user_id"=>$noti->receiver,
							"title"=>$noti->senderx->username." 评论了您的发布: ".$model->name,
						);
						Notification::model()->sendiOSNotification($notificationData);
					}
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
				'defaultOrder' => 'rank DESC, create_time ASC' // this is it.
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
				'reply'=>$reply,
			));
		}else{
			$this->render('view',array(
				'model'=>$model,
				'comment'=>$comment,
				'dataProvider'=>$dataProvider,
				'admin'=>$admin,
				'reply'=>$reply,
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
				$post->fake_up = $_POST['up'] + $post->down;	//offset down vote
				$post->save(false);
				echo "success";	
				return;
			}
		}
		return;
	}


	/*
	 * Vote ajax function
	 */
	public function actionVote(){
		if(isset($_POST['post_id']) && isset($_POST['type'])){
			$post = Posts::model()->findByPk($_POST['post_id']);
			$user = Users::model()->findByPk(Yii::app()->user->id);
			if($post && $user){	//&& $post->user_id != $user->id
				$already = PostsVotes::model()->findByAttributes(array('post_id'=>$post->id, 'user_id'=>$user->id));
				if($already){
					if($already->type != $_POST['type']){	//只考虑和曾经的相反的情况，因为无法重复投票
						$already->type = $_POST['type'];
						$already->create_time = time();
						$already->save(false);
						if($already->type == 1){	//
							if($post->user_id == $user->id){
								$post->fake_up += 2;
							}else{
								$post->up += 2;		//+=2 would be more accurate?
							}
						}else{
							if($post->user_id == $user->id){
								$post->fake_up -= 2;
							}else{
								$post->down += 2;		//+=2 would be more accurate?
							}
						}
						$post->save(false);
					}
				}else{
					$vote = new PostsVotes;
					$vote->type = $_POST['type'];
					$vote->post_id = $post->id;
					$vote->receiver = $post->user_id;
					$vote->user_id = $user->id;
					$vote->create_time = time();
					$vote->save(false);
					if($vote->type == 1){
						if($post->user_id == $user->id){
							$post->fake_up++;
						}else{
							$post->up++;
						}
					}else{
						if($post->user_id == $user->id){
							$post->fake_up--;
						}else{
							$post->down++;
						}
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
			if($comment && $user){
				$already = CommentsVotes::model()->findByAttributes(array('comment_id'=>$comment->id, 'user_id'=>$user->id));
				if($already){
					if($already->type != $_POST['type']){
						$already->type = $_POST['type'];
						$already->create_time = time();
						$already->save(false);
						if($already->type == 1){
							$comment->up += 2;		//+=2 would be more accurate?
						}else{
							$comment->down += 2;		//+=2 would be more accurate?
						}
						$comment->save(false);
					}
				}else{
					$vote = new CommentsVotes;
					$vote->type = $_POST['type'];
					$vote->comment_id = $comment->id;
					$vote->user_id = $user->id;
					$vote->receiver = $comment->user_id;
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
			if($post && $user){
				$already = PostsVotes::model()->findByAttributes(array('post_id'=>$post->id, 'user_id'=>$user->id, 'type'=>$_POST['type']));
				if($already){
					$already->delete();
					if($already->type == 1){
						if($post->user_id == $user->id){
							$post->fake_up--;
						}else{
							$post->up--;
						}
					}else{
						if($post->user_id == $user->id){
							$post->fake_up++;
						}else{
							$post->down--;
						}
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
			if($comment && $user){
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
		$url = urldecode($url);
		echo Posts::model()->getTitle($url);
	}

	//接受ajax，非同步获取保存图片
	public function actionSaveTitle(){	//太慢了 需要优化。ajax保存的时候点别的链接反应很慢。
		$model = "";
		if(isset($_GET['id'])){
			$model = Posts::model()->findByPk($_GET['id']);
		}
		$array = array();
		if($model){
			$json = Posts::model()->getTitle($model->link);
			$array = json_decode($json, TRUE);
			if(isset($array['thumbnail_url'])){
				$model->thumb_pic = $array['thumbnail_url'];
			}
			if(isset($array['html'])){
				$model->video_html = $array['html'];
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
		if(isset($_POST['post_id'])){
			$model = Posts::model()->findByPk($_POST['post_id']);
			if($model){
				$admin = Admins::model()->findByPk(Yii::app()->user->id);
				if(!$admin && Yii::app()->user->id != $model->user_id){
					return;
				}
				$model->hide = 1;
				$model->save(false);

				//如果是管理员提交，减钱
				$owner_admin = Admins::model()->findByPk($model->user_id);
				if($owner_admin){
					$owner_admin->balance -= $admin->salary;
					$owner_admin->total_posts -= 1;
					$owner_admin->save(false);
				}

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

				//如果是管理员提交，加钱
				$owner_admin = Admins::model()->findByPk($model->user_id);
				if($owner_admin){
					$owner_admin->balance += $admin->salary;
					$owner_admin->total_posts += 1;
					$owner_admin->save(false);
				}

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
		if(!Yii::app()->user->id){
				$this->render('/site/index');
		}else{

		if(isset(Yii::app()->session['pictures'])){
			unset(Yii::app()->session['pictures']);
		}
		if(Yii::app()->user->id){
			$user = Users::model()->findByPk(Yii::app()->user->id);
			if(!$user){
				$this->redirect('site/logout');
			}
			$user->userActed(); 
			if($user->isBanned()){
				throw new CHttpException(404,'您的账户已被停权');
			}
		}
		//else{	Users::guestSignup(); }		//automatically sign up for you

		$admin = Admins::model()->findByPk(Yii::app()->user->id);
		if(isset($_GET['category_id'])){
			$criteria = array(
				'select'=>'*, postrank(fake_up, up, down, CAST(create_time as decimal(18,7))) as rank',
				'condition'=>'hide = 0 AND category_id = :cid',
				'params'=>array(
					':cid'=>$_GET['category_id']
				)
			);
		}else{
			$criteria = array(
				'select'=>'*, postrank(fake_up, up, down, CAST(create_time as decimal(18,7))) as rank',
				'condition'=>'hide = 0',
			);
		}

		$pageSize = 15;

/*
		if(isset($_GET['Posts_page']) && $_GET['Posts_page'] > 0){
			$pageSize = $_GET['Posts_page'] * 15;
		}
*/

		$dataProvider = new CActiveDataProvider('Posts', array(
			'criteria' => $criteria,
			'sort' => array(
				'defaultOrder' => 'rank DESC, create_time DESC' // this is it.
			),
			'pagination' => array(
				'pageSize' => $pageSize,
			)
		));

		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'admin'=>$admin
		));

		}
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
