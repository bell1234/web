<?php

class ApiController extends Controller
{


	public function actionSignup(){

		$user = new Users;

		if(isset($_GET['username']) && isset($_GET['email']) && isset($_GET['password']))
		{
			$user->username = $_GET['username'];
			$user->email = $_GET['email'];
			$user->password = $_GET['password'];

			//for phone app, not limitation
			$user->invitation = "ML999";

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

				if($user->email){
					$activation_url = $this->createAbsoluteUrl('/site/activation', array(
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

				DeviceToken::model()->addNewToken($user->id);

				$this->sendJSONResponse(array(
					'user_id'=>(int)$user->id,	//user_id
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


	//接受ajax，非同步获取保存图片
	public function actionSaveTitle(){	//太慢了 需要优化。ajax保存的时候点别的链接反应很慢。

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

		$model = "";
		if(isset($_GET['id'])){
			$model = Posts::model()->findByPk($_GET['id']);
		}
		$array = array();
		if($model){
			$json = Posts::getTitle($model->link);
			$array = json_decode($json, TRUE);
			if(isset($array['thumbnail_url'])){
				$model->thumb_pic = $array['thumbnail_url'];
			}
			if(isset($array['html'])){
				$model->video_html = $array['html'];
			}
			$model->save(false);
		}
		$this->sendJSONResponse($array);
	}


	/**
	 * 账户设置
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionSetting()
	{


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

		if(isset($_GET['email']) || isset($_GET['avatar']))
		{

			if(isset($_GET['email'])){
				$user->email=$_GET['email'];
			}

			if(isset($_GET['avatar'])){
				$user->avatar=$_GET['avatar'];
			}

			$user->auto = 0;

			if($user->save()){
				$this->sendJSONResponse(array(
					'success'=>'changed',
				));
			}else{
				$this->sendJSONResponse(array(
					$user->getErrors()
				));
			}
		}else{
			$this->sendJSONResponse(array(
				'error'=>'nothing changed',
			));
		}

	}


    /**
     * 修改密码
     */
    public function actionChangepassword()
    {

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
            
            if (isset($_GET['oldPassword']) && isset($_GET['newPassword']) && isset($_GET['verifyPassword'])) {
   				$model = new UserChangePassword;
   				$model->password = $_GET['oldPassword'];
   				$model->oldPassword = $_GET['oldPassword'];
   				$model->verifyPassword = $_GET['verifyPassword'];
                if ($model->validate()) {
                    $find           = Users::model()->findByPk(Yii::app()->user->id);
                    $find->password = hash('sha256', $model->password);
                    $find->activkey= hash('sha256', microtime() . $find->username);
                    $find->save();
                    $this->sendJSONResponse(array(
						'success'=>'changed',
					));
                }else{
          			$this->sendJSONResponse(array(
						$model->getErrors()
					));      	
                }
            }else{
				$this->sendJSONResponse(array(
					'error'=>'nothing changed',
				));            	
            }

    }


    /*
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
								//Yii::app()->user->setFlash('recoveryMessage', "<p>您的新密码已保存</p><p>请<a href='/site/login'>点击这里登陆</a></p>");
								$this->sendJSONResponse(array(
									'success'=>'Password has changed',
								)); 
							}
						} 
						$this->render('changePassword',array('form'=>$form2));
		    			} else {
		    				$this->sendJSONResponse(array(
								'error'=>'Wrong link',
							)); 
		    				//throw new CHttpException(404, '该找回密码链接无效，请复制粘贴邮件中的完整地址至浏览器地址栏中');
		    			}
		    		} else {
			    		if(isset($_GET['email'])) {
			    			$form->login_or_email = $_GET['email'];

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
							//Yii::app()->user->setFlash('recoveryMessage', "没六儿已经向您的邮箱发送了一封帮您重置密码的邮件，请查收。");
			    				$this->sendJSONResponse(array(
									'success'=>'recovery email sent',
								)); 
			    			}
			    		}
		  
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

					DeviceToken::model()->addNewToken($user->id);

					$this->sendJSONResponse(array(
						'user_id'=>(int)$user->id,	//user_id
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

		DeviceToken::model()->addNewToken($user->id);

		$arr = array();

		foreach($posts as $post):
			$vote = PostsVotes::model()->findByAttributes(array('post_id'=>$post->id, 'user_id'=>$post->user_id));
			$self_vote = 0;			//0为尚未投票
			if($vote){
				$self_vote = $vote->type;		//1为up，2为down
			}
			array_push($arr, array(
				'post_id' => (int)$post->id,				//post id
				'category'=>(int)$post->category_id,		//category id
				'up' => (int)$post->up + $post->fake_up,	//up votes
				'down'=>(int)$post->down,				//down votes
				'create_time' => (int)$post->create_time,		//create time in unix time stamp
				'title' => $post->name,				//名字
				'url' => $post->link,				//链接（仅限链接分享） 当为内容分享时(type=2)，link为/posts/id
				'thumb_pic'=>$post->thumb_pic,		//小图链接
				'comments'=>(int)$post->comments,		//评论数
				'type'=>(int)$post->type,				//1为link分享 2为内容分享 3为AMA, 当为内容分享时，link为/posts/id
				'username'=>$post->user->username,	//发布人用户名
				'avatar'=>$post->user->avatar,		//发布人头像
				'user_id'=>(int)$post->user_id,			//发布人id. 链接为/users/id
				'self_vote'=>(int)$self_vote,			//1为up, 2为down, 3为nothing
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
			$vote = PostsVotes::model()->findByAttributes(array('post_id'=>$post->id, 'user_id'=>$post->user_id));
			$self_vote = 0;			//0为尚未投票
			if($vote){
				$self_vote = $vote->type;		//1为up，2为down
			}
			array_push($arr, array(
				'post_id' => (int)$post->id,				//post id
				'category'=>(int)$post->category_id,		//category id
				'up' => (int)$post->up + $post->fake_up,	//up votes
				'down'=>(int)$post->down,				//down votes
				'create_time' => (int)$post->create_time,		//create time in unix time stamp
				'title' => $post->name,				//名字
				'description'=>$post->description,  //type != 1时（内容分享或者ama）
				'url' => $post->link,				//链接（仅限链接分享） 当为内容分享时(type=2)，link为/posts/id
				'thumb_pic'=>$post->thumb_pic,		//小图链接
				'comments'=>(int)$post->comments,		//评论数
				'type'=>(int)$post->type,				//1为link分享 2为内容分享 3为AMA, 当为内容分享时，link为/posts/id
				'username'=>$post->user->username,	//发布人用户名
				'avatar'=>$post->user->avatar,		//发布人头像
				'user_id'=>(int)$post->user_id,			//发布人id. 链接为/users/id
				'self_vote'=>(int)$self_vote,			//0 not vote, 1->up, 2->Down
			));
		endforeach;

		$this->sendJSONResponse($arr);
	}





	public function actionCreate(){

		$user = null;

		if(isset($_GET['device_token'])){
			$token = DeviceToken::model()->findByAttributes(array('token'=>$_GET['device_token']));
			if($token){
				$user = Users::model()->findByPk($token->user_id);
			}
		}

		if(!$user){
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
				$this->sendJSONResponse(array(
					$model->getErrors()
				));

			}else if($model->type == 3 && !$model->description){

				$model->addError('description', '请简单介绍自己/推荐提供身份证明');
				$this->sendJSONResponse(array(
					$model->getErrors()
				));

			}else if($model->type == 1 && !$model->link){

				$model->addError('link', '请输入要提交的链接');
				$this->sendJSONResponse(array(
					$model->getErrors()
				));

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


	public function actionCreatePost(){


		if(isset($_POST['device_token'])){
			$token = DeviceToken::model()->findByAttributes(array('token'=>$_POST['device_token']));
			if($token){
				$user = Users::model()->findByPk($token->user_id);
			}
		}

		if(!$user){
			if (!isset($_POST['token'])) {
				$this->sendJSONResponse(array(
					'error' => 'No token'
				));
				exit();
			} else {
				$user = Users::model()->findByAttributes(array(
					'activkey' => $_POST['token']
				));
				if (!$user) {
					$this->sendJSONResponse(array(
						'error' => 'Invalid token'
					));
					exit();
				}
			}
		}

		$model = new Posts;

		if(isset($_POST['name']) && isset($_POST['type']))
		{
			if(isset($_POST['description'])){
				$model->description = $_POST['description'];
			}
			if(isset($_GET['category_id'])){
				$model->category_id = $_POST['category_id'];
			}
			if(isset($_POST['link'])){
				$model->link = $_POST['link'];
			}

			$model->name = $_POST['name'];
			$model->type = $_POST['type'];
			
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
				$this->sendJSONResponse(array(
					$model->getErrors()
				));

			}else if($model->type == 3 && !$model->description){

				$model->addError('description', '请简单介绍自己/推荐提供身份证明');
				$this->sendJSONResponse(array(
					$model->getErrors()
				));

			}else if($model->type == 1 && !$model->link){

				$model->addError('link', '请输入要提交的链接');
				$this->sendJSONResponse(array(
					$model->getErrors()
				));

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
						$this->sendJSONResponse(array(
							'success' => 'success',
						));
					}else{
						$this->sendJSONResponse(array(
							'error' => 'dup vote',
						));
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
					$this->sendJSONResponse(array(
						'success' => 'success',
					));
				}
			}
		}else{
			$this->sendJSONResponse(array(
				'error' => 'failed',
			));
		}
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
						$this->sendJSONResponse(array(
							'success' => 'success',
						));
					}else{
						$this->sendJSONResponse(array(
							'error' => 'dup vote',
						));
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
					$this->sendJSONResponse(array(
						'success' => 'success',
					));
				}
			}
		}else{
			$this->sendJSONResponse(array(
				'error' => 'failed',
			));
		}
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
				'post_id' => (int)$post->id,				//post id
				'category'=>(int)$post->category_id,		//category id
				'up' => (int)$post->up + $post->fake_up,	//up votes
				'down'=>(int)$post->down,				//down votes
				'create_time' => (int)$post->create_time,		//create time in unix time stamp
				'title' => $post->name,				//名字
				'description'=>$post->description,  //type != 1时（内容分享或者ama）
				'url' => $post->link,				//链接（仅限链接分享） 当为内容分享时(type=2)，link为/posts/id
				'thumb_pic'=>$post->thumb_pic,		//小图链接
				'comments'=>(int)$post->comments,		//评论数
				'type'=>(int)$post->type,				//1为link分享 2为内容分享 3为AMA, 当为内容分享时，link为/posts/id
				'username'=>$post->user->username,	//发布人用户名
				'avatar'=>$post->user->avatar,		//发布人头像
				'user_id'=>(int)$post->user_id			//发布人id. 链接为/users/id
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

		if(isset($_GET['description'])){
			$comment->description = $_GET['description'];
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
						$noti->post_id = $post->id;
						$noti->create_time = time();
						$noti->read = 0;
						$noti->save();
						$notificationData = array(
							"user_id"=>$noti->receiver,
							"title"=>$noti->senderx->username." 和另外".$noti->other."人评论了您的发布: ".$post->name,
						);
						Notification::model()->sendiOSNotification($notificationData);
					}else{
						$noti = new Notification;
						$noti->type_id = 2; //comment
						$noti->post_id = $post->id;
						$noti->sender = $user->id;
						$noti->receiver = $post->user_id;
						$noti->create_time = time();
						$noti->save();
						$notificationData = array(
							"user_id"=>$noti->receiver,
							"title"=>$noti->senderx->username." 评论了您的发布: ".$post->name,
						);
						Notification::model()->sendiOSNotification($notificationData);
					}
				}

				$this->sendJSONResponse(array(
					'success'=>$comment->id,
				));
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
		$reply->user_id = $user->id;

		if(isset($_GET['receiver'])){
			$reply->receiver = $_GET['receiver'];
		}
		$post = $comment->post;

		if(isset($_GET['description'])){
			$reply->description = $_GET['description'];
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
						$noti->post_id = $post->id;
						$noti->create_time = time();
						$noti->read = 0;
						$noti->save();
						$notificationData = array(
							"user_id"=>$noti->receiver,
							"title"=>$noti->senderx->username." 和另外".$noti->other."人在下列发布中回复了您的评论: ".$post->name,
						);
						Notification::model()->sendiOSNotification($notificationData);
					}else{
						$noti = new Notification;
						$noti->type_id = 3; //reply
						$noti->post_id = $post->id;
						$noti->sender = $user->id;
						$noti->receiver = $reply->receiver;
						$noti->create_time = time();
						$noti->save();
						$notificationData = array(
							"user_id"=>$noti->receiver,
							"title"=>$noti->senderx->username." 在下列发布中回复了您的评论: ".$post->name,
						);
						Notification::model()->sendiOSNotification($notificationData);
					}
				}


					$this->sendJSONResponse(array(
						'success'=>$reply->id,
					));

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


	public function actionUnreadNotifications(){

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

		$notifications = Notification::model()->count(array(
				'condition'=>'`read` = 0 AND receiver = '.$user->id,
		));

		$this->sendJSONResponse(array(
			'unread' => (int)$notifications,
		));

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
			$post = Posts::model()->findByPk($notification->post_id);
			$vote = PostsVotes::model()->findByAttributes(array('post_id'=>$post->id, 'user_id'=>$post->user_id));
			$self_vote = 0;			//0为尚未投票
			if($vote){
				$self_vote = $vote->type;		//1为up，2为down
			}
			array_push($arr, array(
				'post_id' => (int)$notification->post_id,		//post id
				'post_name' => $notification->post->name,	//post name
				'type_id' => (int)$notification->type_id,		//notification type
				'noti_username'=>$notification->senderx->username,	//回复人用户名
				'noti_user_id'=>(int)$notification->sender,				//回复人id. 链接为/users/id
				'noti_avatar'=>$notification->senderx->avatar,		//回复人头像
				'read'=>(int)$notification->read,					//read or not

				'category'=>(int)$post->category_id,		//category id
				'up' => (int)$post->up + $post->fake_up,	//up votes
				'down'=>(int)$post->down,				//down votes
				'create_time' => (int)$post->create_time,		//create time in unix time stamp
				'title' => $post->name,				//名字
				'url' => $post->link,				//链接（仅限链接分享） 当为内容分享时(type=2)，link为/posts/id
				'thumb_pic'=>$post->thumb_pic,		//小图链接
				'comments'=>(int)$post->comments,		//评论数
				'type'=>(int)$post->type,				//1为link分享 2为内容分享 3为AMA, 当为内容分享时，link为/posts/id
				'username'=>$post->user->username,	//发布人用户名
				'avatar'=>$post->user->avatar,		//发布人头像
				'user_id'=>(int)$post->user_id,			//发布人id. 链接为/users/id
				'self_vote'=>(int)$self_vote,			//1为up, 2为down, 3为nothing

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
			'user_id'=>(int)$user->id,	//user_id
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
				'order' => 'rank DESC, create_time ASC',
				'limit' => 15
			));
		}

		$arr = array();

		foreach($comments as $comment):
			$replies = $this->getReply($comment->id);

			$vote = CommentsVotes::model()->findByAttributes(array('comment_id'=>$comment->id, 'user_id'=>$comment->user_id));
			$self_vote = 0;			//0为尚未投票
			if($vote){
				$self_vote = $vote->type;		//1为up，2为down
			}
			array_push($arr, array(
				'comment_id' => (int)$comment->id,				//post id
				'up' => (int)$comment->up,	//up votes
				'down'=>(int)$comment->down,				//down votes
				'create_time' => (int)$comment->create_time,		//create time in unix time stamp
				'description' => (string)$comment->description,				//内容
				'username'=>$comment->user->username,	//发布人用户名
				'user_id'=>(int)$comment->user_id,			//发布人id. 链接为/users/id
				'avatar'=>$comment->user->avatar,		//发布人头像
				'replies'=>$replies,					//replies
				'self_vote'=>(int)$self_vote,				//0->no, 1->up, 2->down
			));
		endforeach;

		$this->sendJSONResponse($arr);

	}



	protected function getReply($comment_id){

		$replies = Reply::model()->findAllByAttributes(array('comment_id'=>$comment_id));

		$arr = array();

		foreach($replies as $reply):
			if($reply->receiverx){
				array_push($arr, array(
					'reply_id' => (int)$reply->id,				//reply id
					'create_time' => (int)$reply->create_time,		//create time in unix time stamp
					'description' => (string)$reply->description,				//内容
					'username'=>$reply->user->username,	//发布人用户名
					'user_id'=>(int)$reply->user_id,			//发布人id. 链接为/users/id
					'avatar'=>$reply->user->avatar,		//发布人头像
					'receiver_id'=>$reply->receiver,		//回复人
					'receiver_username'=>$reply->receiverx->username,		//回复人用户名
				));
			}else{
				array_push($arr, array(
					'reply_id' => (int)$reply->id,				//reply id
					'create_time' => (int)$reply->create_time,		//create time in unix time stamp
					'description' => (string)$reply->description,				//内容
					'username'=>$reply->user->username,	//发布人用户名
					'user_id'=>(int)$reply->user_id,			//发布人id. 链接为/users/id
					'avatar'=>$reply->user->avatar,		//发布人头像
				));
			}
		endforeach;

		return $arr;
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