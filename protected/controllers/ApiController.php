<?php

class ApiController extends Controller
{


	public function actionSignup(){

		$user = new Users;
		$model= new LoginForm;

		if(isset($_GET['Users']))
		{
			$user->attributes=$_GET['Users'];

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

				$invite = Invitation::model()->findByAttributes(array('code' => $user->invitation));
				if($invite){
					//$invite->delete();
				}
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

				$this->sendJSONResponse(array(
					'user_id'=>$user->id,	//user_id
					'token'=>$user->activkey,	//token
					'username'=>$user->username,	//用户名
					'avatar'=>$user->avatar,	//头像
				));
			}else{
				$this->sendJSONResponse(array(
					$user->getErrors()
				));
			}
		}else{
			$this->sendJSONResponse(array(
				'error'=>'无输入',		//密码错误
			));
		}

	}





	public function actionLogin(){

		if(isset($_GET['username']) && isset($_GET['password'])){

			if (strpos($_GET['username'],"@")) {
				$user=Users::model()->findByAttributes(array('email'=>$_GET['username']));
			} else {
				$user=Users::model()->findByAttributes(array('username'=>$_GET['username']));
			}
			if($user){
				if(hash('sha256', $_GET['password']) === $user->password){
					
					$user->logins++;
					$user->save(false);
					$this->sendJSONResponse(array(
						'user_id'=>$user->id,	//user_id
						'token'=>$user->activkey,	//token
						'username'=>$user->username,	//用户名
						'avatar'=>$user->avatar,	//头像
					));
				}else{
					$this->sendJSONResponse(array(
						'error'=>'用户名/邮箱或密码不正确',		//密码错误
					));
				}
			}else{
				$this->sendJSONResponse(array(
					'error'=>'用户名/邮箱或密码不正确',		//用户名错误
				));
			}
		}else{
			$this->sendJSONResponse(array(
				'error'=>'请输入用户名/邮箱和密码',		//未输入用户名或密码
			));
		}

	}



	public function actionIndex(){

		if (!isset($_GET['token'])) {
			$this->sendJSONResponse(array(
				'error' => 'No token'
			));
			exit();
		} else {
			$user = Users::model()->findByAttributes(array(
				'activkey' => $_GET['token']
			));
			if (!$user) {
				$this->sendJSONResponse(array(
					'error' => 'Invalid token'
				));
				exit();
			}
		}

		$page = 1;
		
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		}

		if (isset($_GET['refresh_all'])) {
			
			$posts = Posts::model()->findAll(array(
				'select'=>'*, postrank(fake_up, up, down, CAST(create_time as decimal(18,7))) as rank',
				'condition'=>'hide = 0',
				'offset' => ($page - 1) * 15,
				'order' => 'rank DESC, create_time DESC',
				'limit' => 15 * $_GET['refresh_all']
			));
			
		} else {
			
			$posts = Posts::model()->findAll(array(
				'select'=>'*, postrank(fake_up, up, down, CAST(create_time as decimal(18,7))) as rank',
				'condition'=>'hide = 0',
				'offset' => ($page - 1) * 15,
				'order' => 'rank DESC, create_time DESC',
				'limit' => 15
			));
		}

		$arr = array();

		foreach($posts as $post):
			array_push($arr, array(
				'post_id' => $post->id,				//post id
				'category'=>$post->category_id,		//category id
				'up' => $post->up + $post->fake_up,	//up votes
				'down'=>$post->down,				//down votes
				'create_time' => $post->create_time,		//create time in unix time stamp
				'title' => $post->name,				//名字
				'url' => $post->link,				//链接（仅限链接分享） 当为内容分享时(type=2)，link为/posts/id
				'thumb_pic'=>$post->thumb_pic,		//小图链接
				'comments'=>$post->comments,		//评论数
				'type'=>$post->type,				//1为link分享 2为内容分享 3为AMA, 当为内容分享时，link为/posts/id
				'username'=>$post->user->username,	//发布人用户名
				'avatar'=>$post->user->avatar,		//发布人头像
				'user_id'=>$post->user_id			//发布人id. 链接为/users/id
			));
		endforeach;

		$this->sendJSONResponse($arr);
	}



	public function actionUser(){

		if (!isset($_GET['token'])) {
			$this->sendJSONResponse(array(
				'error' => 'No token'
			));
			exit();
		} else {
			$user = Users::model()->findByAttributes(array(
				'activkey' => $_GET['token']
			));
			if (!$user) {
				$this->sendJSONResponse(array(
					'error' => 'Invalid token'
				));
				exit();
			}
		}

		if (!isset($_GET['user_id'])) {
			$this->sendJSONResponse(array(
				'error' => 'No user_id'
			));
			exit();
		}else{
			$other = Users::model()->findByPk($_GET['user_id']);
		}

		$page = 1;
		
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		}

		if (isset($_GET['refresh_all'])) {
			
			$posts = Posts::model()->findAll(array(
				'select'=>'*, postrank(fake_up, up, down, CAST(create_time as decimal(18,7))) as rank',
				'condition'=>'hide = 0 AND user_id = '.$other->id,
				'offset' => ($page - 1) * 15,
				'order' => 'rank DESC, create_time DESC',
				'limit' => 15 * $_GET['refresh_all']
			));
			
		} else {
			
			$posts = Posts::model()->findAll(array(
				'select'=>'*, postrank(fake_up, up, down, CAST(create_time as decimal(18,7))) as rank',
				'condition'=>'hide = 0 AND user_id = '.$other->id,
				'offset' => ($page - 1) * 15,
				'order' => 'rank DESC, create_time DESC',
				'limit' => 15
			));
		}

		$arr = array();

		foreach($posts as $post):
			array_push($arr, array(
				'post_id' => $post->id,				//post id
				'category'=>$post->category_id,		//category id
				'up' => $post->up + $post->fake_up,	//up votes
				'down'=>$post->down,				//down votes
				'create_time' => $post->create_time,		//create time in unix time stamp
				'title' => $post->name,				//名字
				'description'=>$post->description,  //type != 1时（内容分享或者ama）
				'url' => $post->link,				//链接（仅限链接分享） 当为内容分享时(type=2)，link为/posts/id
				'thumb_pic'=>$post->thumb_pic,		//小图链接
				'comments'=>$post->comments,		//评论数
				'type'=>$post->type,				//1为link分享 2为内容分享 3为AMA, 当为内容分享时，link为/posts/id
				'username'=>$post->user->username,	//发布人用户名
				'avatar'=>$post->user->avatar,		//发布人头像
				'user_id'=>$post->user_id			//发布人id. 链接为/users/id
			));
		endforeach;

		$this->sendJSONResponse($arr);
	}





	public function actionCreate(){

		if (!isset($_GET['token'])) {
			$this->sendJSONResponse(array(
				'error' => 'No token'
			));
			exit();
		} else {
			$user = Users::model()->findByAttributes(array(
				'activkey' => $_GET['token']
			));
			if (!$user) {
				$this->sendJSONResponse(array(
					'error' => 'Invalid token'
				));
				exit();
			}
		}

		$model = new Posts;

		if(isset($_GET['name']) && isset($_GET['type']))
		{
			if(isset($_GET['description'])){
				$model->description = $_GET['description'];
			}
			if(isset($_GET['category_id'])){
				$model->category_id = $_GET['category_id'];
			}
			if(isset($_GET['link'])){
				$model->link = $_GET['link'];
			}

			$model->name = $_GET['name'];
			$model->type = $_GET['type'];
			
			$model->name = strip_tags($model->name); 	//净化tags
			$model->create_time = time();
			$model->user_id = $user->id;
			$model->points = 0;	//starting with 0 points?

			if($model->type == 1){		//link
				//nothing
			}else if($model->type == 2){	//content
				$model->link = "";

			}else if($model->type == 3){	//ama
				$model->link = "";
				$model->category_id = 4;	//force AMA
			}
			if($model->type == 2 && !$model->description){

				$model->addError('description', '请输入要提交的内容');
				echo json_encode(array($model->getErrors()));

			}else if($model->type == 3 && !$model->description){

				$model->addError('description', '请简单介绍自己/推荐提供身份证明');
				echo json_encode(array($model->getErrors()));

			}else if($model->type == 1 && !$model->link){

				$model->addError('link', '请输入要提交的链接');
				echo json_encode(array($model->getErrors()));

			}else if($model->validate()){

				$model->save(false);

				$admin = Admins::model()->findByPk($user->id);
				if($admin){
					$admin->total_posts++;
					$admin->balance += $admin->salary;		//1块钱1条 或者 1块5 1条
					$admin->save(false);
				}

				$pictures = Yii::app()->session['pictures'];
				if ($pictures) {
					foreach ($pictures as $picture => $pic) {
						$image = PostsPictures::model()->findByAttributes(array(
							'path' => $pic
						));
						if($image){
							$image->post_id = $model->id;
							$image->save();
						}
					}
				}
				unset(Yii::app()->session['pictures']);

				if(!$model->thumb_pic){		//说明用户没有自己点推荐链接
					if($model->link){
						//We fill in thumb_pic, etc, in a async way, in ajax. not here.
						//nothing here
					}else if($model->type == 2){	

						preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $model->description, $match);

						if(isset($match[1])){
							$model->thumb_pic = $match[1];
						}

						//注意:上传视频怎么办....
						//$model->video_html = "";

						//如果上述还是没有找到图片
						if(!$model->thumb_pic){
							$model->thumb_pic = ""; 	//avatar? or default content pic?
						}
						$model->save(false);
					}else if($model->type == 3){	//AMA
						$model->thumb_pic = ""; //avatar? or default ama pic?
						$model->save(false);
					}
				}
				echo json_encode(array('success'=>$model->id));

				$this->sendJSONResponse(array(
					'success' => $model->id,
				));

			}else{
				$this->sendJSONResponse(array(
					$model->getErrors()
				));
			}
		}else{
			$this->sendJSONResponse(array(
				'error' => 'no name',
			));	
		}

	}


	public function actionVote(){

		if (!isset($_GET['token'])) {
			$this->sendJSONResponse(array(
				'error' => 'No token'
			));
			exit();
		} else {
			$user = Users::model()->findByAttributes(array(
				'activkey' => $_GET['token']
			));
			if (!$user) {
				$this->sendJSONResponse(array(
					'error' => 'Invalid token'
				));
				exit();
			}
		}

		if(isset($_GET['post_id']) && isset($_GET['type'])){
			$post = Posts::model()->findByPk($_GET['post_id']);
			if($post && $user){	//&& $post->user_id != $user->id
				$already = PostsVotes::model()->findByAttributes(array('post_id'=>$post->id, 'user_id'=>$user->id));
				if($already){
					if($already->type != $_GET['type']){
						$already->type = $_GET['type'];
						$already->create_time = time();
						$already->save(false);
						if($already->type == 1){
							if($post->user_id == $user->id){
								$post->fake_up++;
							}else{
								$post->up++;		//+=2 would be more accurate?
							}
						}else{
							if($post->user_id == $user->id){
								$post->fake_up--;
							}else{
								$post->down++;		//+=2 would be more accurate?
							}
						}
						$post->save(false);
					}
				}else{
					$vote = new PostsVotes;
					$vote->type = $_GET['type'];
					$vote->post_id = $post->id;
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
				$this->sendJSONResponse(array(
					'success' => 'success',
				));
			}
		}

		$this->sendJSONResponse(array(
			'error' => 'failed',
		));
	}


	/**
	 * Comment Vote ajax function
	 */
	public function actionVoteComment(){

		if (!isset($_GET['token'])) {
			$this->sendJSONResponse(array(
				'error' => 'No token'
			));
			exit();
		} else {
			$user = Users::model()->findByAttributes(array(
				'activkey' => $_GET['token']
			));
			if (!$user) {
				$this->sendJSONResponse(array(
					'error' => 'Invalid token'
				));
				exit();
			}
		}
		if(isset($_GET['comment_id']) && isset($_GET['type'])){
			$comment = Comments::model()->findByPk($_GET['comment_id']);
			if($comment && $user){
				$already = CommentsVotes::model()->findByAttributes(array('comment_id'=>$comment->id, 'user_id'=>$user->id));
				if($already){
					if($already->type != $_GET['type']){
						$already->type = $_GET['type'];
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
					$vote->type = $_GET['type'];
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
				$this->sendJSONResponse(array(
					'success' => 'success',
				));
			}
		}
		$this->sendJSONResponse(array(
			'error' => 'failed',
		));
	}


	public function actionView(){

		if (!isset($_GET['post_id'])) {
			$this->sendJSONResponse(array(
				'error' => 'No post_id'
			));
			exit();
		} else {
			$post = Posts::model()->findByPk($_GET['post_id']);
			if (!$post) {
				$this->sendJSONResponse(array(
					'error' => '404'
				));
				exit();
			}
		}

		$this->sendJSONResponse(array(
				'post_id' => $post->id,				//post id
				'category'=>$post->category_id,		//category id
				'up' => $post->up + $post->fake_up,	//up votes
				'down'=>$post->down,				//down votes
				'create_time' => $post->create_time,		//create time in unix time stamp
				'title' => $post->name,				//名字
				'description'=>$post->description,  //type != 1时（内容分享或者ama）
				'url' => $post->link,				//链接（仅限链接分享） 当为内容分享时(type=2)，link为/posts/id
				'thumb_pic'=>$post->thumb_pic,		//小图链接
				'comments'=>$post->comments,		//评论数
				'type'=>$post->type,				//1为link分享 2为内容分享 3为AMA, 当为内容分享时，link为/posts/id
				'username'=>$post->user->username,	//发布人用户名
				'avatar'=>$post->user->avatar,		//发布人头像
				'user_id'=>$post->user_id			//发布人id. 链接为/users/id
		));

	}



	public function actionCreateComment(){


		if (!isset($_GET['token'])) {
			$this->sendJSONResponse(array(
				'error' => 'No token'
			));
			exit();
		} else {
			$user = Users::model()->findByAttributes(array(
				'activkey' => $_GET['token']
			));
			if (!$user) {
				$this->sendJSONResponse(array(
					'error' => 'Invalid token'
				));
				exit();
			}
		}

		if (!isset($_GET['post_id'])) {
			$this->sendJSONResponse(array(
				'error' => 'No post_id'
			));
			exit();
		} else {
			$post = Posts::model()->findByPk($_GET['post_id']);
			if (!$post) {
				$this->sendJSONResponse(array(
					'error' => '404'
				));
				exit();
			}
		}

		$comment = new Comments;
		$comment->post_id = $post->id;

		if(isset($_GET['Comments'])){
			$comment->attributes = $_GET['Comments'];
			$comment->user_id = $user->id;
			if($comment->isNewRecord){
				$comment->create_time = time();
			}else{
				$comment->edited = time();
			}
			if($comment->isNewRecord){
				$post->comments++;
			}
			$comment->description = strip_tags($comment->description);
			$comment->description = nl2br($comment->description);
			
			if($comment->save()){
				$post->save(false);
				if($comment->user_id != $post->user_id){
					$noti = Notification::model()->findByAttributes(array('post_id'=>$post->id, 'receiver'=>$post->user_id, 'type_id'=>2));
					if($noti){
						$noti->sender = $user->id;
						$noti->other++;
						$noti->post_id = $model->id;
						$noti->create_time = time();
						$noti->read = 0;
						$noti->save();
					}else{
						$noti = new Notification;
						$noti->type_id = 2; //comment
						$noti->post_id = $model->id;
						$noti->sender = $user->id;
						$noti->receiver = $model->user_id;
						$noti->create_time = time();
						$noti->save();
					}
					
					$this->sendJSONResponse(array(
						'success'=>$comment->id,
					));

				}
			}else{
				$this->sendJSONResponse(array(
					$comment->getErrors()
				));
			}
		}else{
			$this->sendJSONResponse(array(
				'error' => '无输入'
			));
		}

	}





	public function actionCreateReply(){

		if (!isset($_GET['token'])) {
			$this->sendJSONResponse(array(
				'error' => 'No token'
			));
			exit();
		} else {
			$user = Users::model()->findByAttributes(array(
				'activkey' => $_GET['token']
			));
			if (!$user) {
				$this->sendJSONResponse(array(
					'error' => 'Invalid token'
				));
				exit();
			}
		}

		if (!isset($_GET['comment_id'])) {
			$this->sendJSONResponse(array(
				'error' => 'No comment_id'
			));
			exit();
		} else {
			$comment = Comments::model()->findByPk($_GET['comment_id']);
			if (!$comment) {
				$this->sendJSONResponse(array(
					'error' => '404'
				));
				exit();
			}
		}

		$reply = new Reply;
		$reply->comment_id = $comment->id;

		if(isset($_GET['Reply'])){
			$reply->attributes = $_GET['Reply'];
			$reply->create_time = time();
			$reply->description = strip_tags($reply->description);

			if($reply->save()){
				$reply->comment->post->comments++;
				$reply->comment->post->save(false);
				if($reply->receiver != $user->id){
					$noti = Notification::model()->findByAttributes(array('post_id'=>$reply->comment->post_id, 'receiver'=>$reply->receiver, 'type_id'=>3));
					if($noti){
						$noti->sender = $user->id;
						$noti->other++;
						$noti->post_id = $model->id;
						$noti->create_time = time();
						$noti->read = 0;
						$noti->save();
					}else{
						$noti = new Notification;
						$noti->type_id = 3; //reply
						$noti->post_id = $model->id;
						$noti->sender = $user->id;
						$noti->receiver = $reply->receiver;
						$noti->create_time = time();
						$noti->save();
					}
				}
			}else{
				$this->sendJSONResponse(array(
					$reply->getErrors()
				));
			}
		}else{
			$this->sendJSONResponse(array(
				'error' => '无输入'
			));
		}

	}


	public function actionNotifications(){

		if (!isset($_GET['token'])) {
			$this->sendJSONResponse(array(
				'error' => 'No token'
			));
			exit();
		} else {
			$user = Users::model()->findByAttributes(array(
				'activkey' => $_GET['token']
			));
			if (!$user) {
				$this->sendJSONResponse(array(
					'error' => 'Invalid token'
				));
				exit();
			}
		}

		$page = 1;
		
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		}

		$notifications = Notification::model()->findAll(array(
				'condition'=>'receiver = '.$user->id,
				'offset' => ($page - 1) * 20,
				'order' => 'create_time DESC',
				'limit' => 20
		));

		$arr = array();

		foreach($notifications as $notification):
			array_push($arr, array(
				'post_id' => $notification->post_id,		//post id
				'post_name' => $notification->post->name,	//post name
				'type_id' => $notification->type_id,		//notification type
				'username'=>$notification->senderx->username,	//回复人用户名
				'user_id'=>$notification->sender,				//回复人id. 链接为/users/id
				'avatar'=>$notification->senderx->avatar,		//回复人头像
			));
		endforeach;

		$this->sendJSONResponse($arr);


	}



	public function actionReadNotifications(){
		if (!isset($_GET['token'])) {
			$this->sendJSONResponse(array(
				'error' => 'No token'
			));
			exit();
		} else {
			$user = Users::model()->findByAttributes(array(
				'activkey' => $_GET['token']
			));
			if (!$user) {
				$this->sendJSONResponse(array(
					'error' => 'Invalid token'
				));
				exit();
			}
		}

		$notifications = Notification::model()->findAllByAttributes(array('receiver'=>$user->id, 'read'=>0));
		foreach($notifications as $noti):
			$noti->read = 1;
			$noti->save(false);
		endforeach;
		$this->sendJSONResponse(array(
			'success' => 'all marked'
		));
	}


	public function actionGetOwnInfo(){
		if (!isset($_GET['token'])) {
			$this->sendJSONResponse(array(
				'error' => 'No token'
			));
			exit();
		} else {
			$user = Users::model()->findByAttributes(array(
				'activkey' => $_GET['token']
			));
			if (!$user) {
				$this->sendJSONResponse(array(
					'error' => 'Invalid token'
				));
				exit();
			}
		}

		$this->sendJSONResponse(array(
			'user_id'=>$user->id,	//user_id
			'username'=>$user->username,	//用户名
			'avatar'=>$user->avatar,	//头像
		));
	}


	public function actionGetAllComments(){

		if (!isset($_GET['post_id'])) {
			$this->sendJSONResponse(array(
				'error' => 'No post_id'
			));
			exit();
		} else {
			$post = Posts::model()->findByPk($_GET['post_id']);
			if (!$post) {
				$this->sendJSONResponse(array(
					'error' => '404'
				));
				exit();
			}
		}

		$page = 1;
		
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		}

		if (isset($_GET['refresh_all'])) {
			
			$comments = Comments::model()->findAll(array(
				'select'=>'*, commentrank(up, CAST(down as decimal(18,7))) as rank',
				'condition'=>'post_id = '.$post->id,
				'offset' => ($page - 1) * 15,
				'order' => 'rank DESC, create_time DESC',
				'limit' => 15 * $_GET['refresh_all']
			));
			
		} else {
			
			$comments = Comments::model()->findAll(array(
				'select'=>'*, commentrank(up, CAST(down as decimal(18,7))) as rank',
				'condition'=>'post_id = '.$post->id,
				'offset' => ($page - 1) * 15,
				'order' => 'rank DESC, create_time DESC',
				'limit' => 15
			));
		}

		$arr = array();

		foreach($comments as $comment):
			array_push($arr, array(
				'comment_id' => $comment->id,				//post id
				'up' => $comment->up,	//up votes
				'down'=>$comment->down,				//down votes
				'create_time' => $comment->create_time,		//create time in unix time stamp
				'description' => $comment->description,				//内容
				'username'=>$comment->user->username,	//发布人用户名
				'user_id'=>$comment->user_id,			//发布人id. 链接为/users/id
				'avatar'=>$comment->user->avatar,		//发布人头像
			));
		endforeach;

		$this->sendJSONResponse($arr);

	}



	public function actionGetReply(){

		if (!isset($_GET['comment_id'])) {
			$this->sendJSONResponse(array(
				'error' => 'No comment_id'
			));
			exit();
		} else {
			$comment = Comments::model()->findByPk($_GET['comment_id']);
			if (!$post) {
				$this->sendJSONResponse(array(
					'error' => '404'
				));
				exit();
			}
		}

		$replies = Reply::model()->findAllByAttributes(array('comment_id'=>$comment->id));

		$arr = array();

		foreach($replies as $reply):
			array_push($arr, array(
				'reply_id' => $reply->id,				//reply id
				'create_time' => $reply->create_time,		//create time in unix time stamp
				'description' => $reply->description,				//内容
				'username'=>$reply->user->username,	//发布人用户名
				'user_id'=>$reply->user_id,			//发布人id. 链接为/users/id
			));
		endforeach;

		$this->sendJSONResponse($arr);
	}




	public function sendJSONResponse($arr) {
		if (!isset($_GET['callback'])) {
			echo "no callback from jsonp";
			exit();
		}
		header('content-type: application/json; charset=utf-8');
		echo $_GET['callback'] . '(' . json_encode($arr) . ')';
	}

	public function sendJSONPost($arr) {
		header('content-type: application/json; charset=utf-8');
		echo json_encode($arr);
	}




	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}