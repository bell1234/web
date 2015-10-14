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
				'actions'=>array('index','view', 'inf'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update', 'getTitle', 'getPic', 'getDupURL', 'vote', 'voteCancel', 'ajaxUpload', 'voteComment', 'voteCommentCancel'),
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
		$model = $this->loadModel($id);

		if(Yii::app()->user->id){
			$comment = Comments::model()->findByAttributes(array('user_id'=>Yii::app()->user->id, 'post_id'=>$model->id));
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
			if($comment->isNewRecord && $comment->save()){
				$model->comments++;
				$model->save(false);
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
			));
		}else{
			$this->render('view',array(
				'model'=>$model,
				'comment'=>$comment,
				'dataProvider'=>$dataProvider,
			));
		}
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
			echo "error";
		}

		


	}


	/**
	 * grab picture from the link
	 */
	public function actionGetPic($url)
	{

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
		preg_match_all( '/<img[^>]+src=[\'"]([^\'"]+)[\'"].*>/i', $data, $array);

$biggestImage = 'path to "no image found" image';

// process
$maxSize = -1;
$visited = array();
// base url
$parts=parse_url($url);
$host=$parts['scheme'].'://'.$parts['host'];
// loop a few times
$i = 0;
shuffle($array[1]);
foreach($array[1] as $key=>$element){
    $i++;
    $pic = $element;
    if($i > 3){
	continue;
    }
    if($pic=='')continue;// it happens on your test url
	$absUrl = $this->nodots($this->absurl($url, $pic));
    // ignore already seen images, add new images
    if(in_array($absUrl, $visited))continue;
    $visited[]=$absUrl;
    // get image
    $image=@getimagesize($absUrl);// get the rest images width and height
    if (($image[0] * $image[1]) > $maxSize) {   
        $maxSize = $image[0] * $image[1];  //compare images' sise
        $biggestImage = $absUrl;
    }
}
if($biggestImage){
	echo $biggestImage; 
}else{
	echo "error";
}

	}



	public function actionAjaxUpload()
	{

        Yii::import("ext.EAjaxUpload.qqFileUploader");
 
        $folder='uploads/posts/'.Yii::app()->user->id.'/'; // folder for uploaded files

        if (!file_exists ($folder)){
            mkdir ($folder, 0777, true);
        }

        $allowedExtensions = array('jpg','png','svg','gif','jpeg','tiff','tif','ico','bmp');
        $sizeLimit = 5 * 1024 * 1024;	// maximum file size in bytes
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload($folder);
        $return = htmlspecialchars(json_encode($result), ENT_NOQUOTES);
 
        $fileSize=filesize($folder.$result['filename']);//GETTING FILE SIZE
        $fileName=$result['filename'];//GETTING FILE NAME
	$filename = $fileName;

 	$thumb = new Imagick("uploads/posts/" . Yii::app()->user->id . "/" . $filename);
        $thumb->setImageFormat("png");
        $thumb->thumbnailImage(90, 90);

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
				'pageSize' => 10
			)
		));

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





protected function absurl($pgurl, $url) {
 $pgurl;
 if(strpos($url,'://')) return $url; //already absolute
 if(substr($url,0,2)=='//') return 'http:'.$url; //shorthand scheme
 if($url[0]=='/') return parse_url($pgurl,PHP_URL_SCHEME).'://'.parse_url($pgurl,PHP_URL_HOST).$url; //just add domain
 if(strpos($pgurl,'/',9)===false) $pgurl .= '/'; //add slash to domain if needed
 return substr($pgurl,0,strrpos($pgurl,'/')+1).$url; //for relative links, gets current directory and appends new filename
}

protected function nodots($path) {
 $arr1 = explode('/',$path);
 $arr2 = array();
 foreach($arr1 as $seg) {
  switch($seg) {
   case '.':
    break;
   case '..':
    array_pop($arr2);
    break;
   case '...':
    array_pop($arr2); array_pop($arr2);
    break;
   case '....':
    array_pop($arr2); array_pop($arr2); array_pop($arr2);
    break;
   case '.....':
    array_pop($arr2); array_pop($arr2); array_pop($arr2); array_pop($arr2);
    break;
   default:
    $arr2[] = $seg;
  }
 }
 return implode('/',$arr2);
}


}
