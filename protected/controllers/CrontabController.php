<?php

class CrontabController extends Controller
{

	public function actionTest(){
		$admins = Admins::model()->findAll();
		foreach($admins as $admin){
			$user = Users::model()->findByPk($admin->user_id);
			if(!$user){
				$admin->delete();
			}
		}
	}



	public function actionUpdateOnYourPost(){
        //prevent anyone else from using our cron
        if ($_SERVER['REMOTE_ADDR'] !== '52.8.247.253' && (!isset($_SERVER['HTTP_CF_CONNECTING_IP']) || $_SERVER['HTTP_CF_CONNECTING_IP'] != '52.8.247.253')) {
            throw new CHttpException(404, "The requested link does not exist.");
        }
        $posts = Posts::model()->findAll('informed = 0 AND hide = 0 AND (up + fake_up) >= 2');
        foreach($posts as $post){

			if($post->user_id != 175205){
				//continue;
				//仅供测试
			}
			$lead = PostsVotes::model()->find('post_id = '.$post->id.' AND user_id != '.$post->user_id.' AND type = 1');
			if(!$lead){
				continue;
			}else{
				$user = Users::model()->findByPk($lead->user_id);
			}

			$data["user_id"] = $post->user_id;
			$data["title"] = $user->username.'和另外'.($post->up + $post->fake_up - 1).'人赞了您的发布: '.$post->name;
			$data["type"] = 2;				//1 to inbox, 2 is to homepage

			Notification::model()->sendiOSNotification($data);

        	$post->informed = 1;
        	$post->save(false);
        }
        echo 200;
	}


	public function actionDailyNews(){

        //prevent anyone else from using our cron
        if ($_SERVER['REMOTE_ADDR'] !== '52.8.247.253' && (!isset($_SERVER['HTTP_CF_CONNECTING_IP']) || $_SERVER['HTTP_CF_CONNECTING_IP'] != '52.8.247.253')) {
            throw new CHttpException(404, "The requested link does not exist.");
        }

		$notifs = DeviceToken::model()->findAll();
		foreach($notifs as $notif){
			if($notif->user_id != 175205){
				//continue;
				//仅供测试
			}
			if ($notif && $notif->token) {
				$post = Yii::app()->db->createCommand('select * from tbl_posts where unix_timestamp() - create_time < 86400 order by `up` desc limit 1')->queryRow();
				if(!$post){
					continue;
				}
				$data["user_id"] = $notif->user_id;
				$data["title"] = "今日最佳: ".$post['name'];
				$data["token"] = $notif->token;
				$data["unread"] = 1;
				$data["type"] = 2;				//1 to inbox, 2 is to homepage
			 	$url = Yii::app()->params['globalURL'].'/simplepush/iospush.php?'.http_build_query($data);
				$ch  = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //this prevent printing the 200json code
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); //timeout 1s
				curl_setopt($ch, CURLOPT_TIMEOUT, 1); //timeout 1s
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$result = curl_exec($ch);
				curl_close($ch);
			}
		}
		echo 200;
	}


    public function actionToutiao(){

    	throw new CHttpException(404, "The requested link does not exist.");

	ini_set('max_execution_time', 600);	//10 mins function max
	set_time_limit(600);

	Yii::import('ext.Scrapper.Scrapper', true);	//toutiao only for now
	$array = Scrapper::toutiao();

	foreach($array as $arr){
	
		$post = Posts::model()->findByAttributes(array('link'=>$arr['url'], 'auto'=>1));
		if($post){
			//是不是应该跳过整个？
			continue;
		}
		$post = new Posts;
		$post->auto = 1;
		$post->link = $arr['url'];
		$post->name = $arr['title'];
		if (strpos($arr['category'],'tech') !== false) {
			$post->category_id = 3;
		}
		else if (strpos($arr['category'],'news') !== false) {
			$post->category_id = 2;
		}
		else if (strpos($arr['category'],'funny') !== false) {
			$post->category_id = 1;
		}else{
			$post->category_id = 30;
		}

		if(isset($arr['images']) && isset($arr['images'][0])){
			$image = $arr['images'][0];
		}

		$rand_time = rand(1, 1200);	//20 mins
		$post->create_time = time() - $rand_time;

		$post->fake_up = $arr['likes'] - $arr['dislikes'];	//rand for now

		if($post->fake_up > 500){
			$post->fake_up = 500 - rand(1, 100);
		}

		$post->user_id = rand(175235, 175327);	//system accounts
		$post->type = 1;		//link
		if(isset($image) && $image){
			$post->thumb_pic = $image;
		}
		$post->save();

	}
		echo 200;
    }




    public function actionPengpai(){	//this is the function that calls paperCN

    	throw new CHttpException(404, "The requested link does not exist.");

	ini_set('max_execution_time', 600);	//10 mins function max
	set_time_limit(600);

	Yii::import('ext.Scrapper.Scrapper', true);	//toutiao only for now
	$array = Scrapper::paperCn();

	foreach($array as $arr){

		$post = Posts::model()->findByAttributes(array('link'=>$arr['url'], 'auto'=>1));
		if($post){
			//是不是应该跳过整个？
			continue;
		}
		$post = new Posts;
		$post->auto = 1;
		$post->link = $arr['url'];
		$post->name = $arr['title'];
		$post->category_id = 2;

		if(isset($arr['images']) && isset($arr['images'][0])){
			$image = $arr['images'][0];
		}

		$rand_time = rand(1, 1200);	//20 mins
		$post->create_time = time() - $rand_time;

		$post->fake_up = rand(50, 300);

		$post->user_id = rand(175235, 175327);	//system accounts
		$post->type = 1;		//link
		if(isset($image) && $image){
			$post->thumb_pic = $image;
		}
		$post->save();
	}

	echo 200;
    }



    public function actionDouban(){	//this is the function that calls paperCN

    	throw new CHttpException(404, "The requested link does not exist.");

	ini_set('max_execution_time', 600);	//10 mins function max
	set_time_limit(600);

	Yii::import('ext.Scrapper.Scrapper', true);	//toutiao only for now
	$array = Scrapper::douban();

	foreach($array as $arr){

		$post = Posts::model()->findByAttributes(array('link'=>$arr['url'], 'auto'=>1));
		if($post){
			//是不是应该跳过整个？
			continue;
		}
		$post = new Posts;
		$post->auto = 1;
		$post->link = $arr['url'];
		$post->name = $arr['title'];

		$post->category_id = 30;

		$rand_time = rand(1, 1200);	//20 mins
		$post->create_time = time() - $rand_time;

		$post->fake_up = rand(50, 300);

		$post->user_id = rand(175235, 175327);	//system accounts
		$post->type = 1;		//link
		$post->thumb_pic = '/images/douban.jpeg';	//douban logo
		$post->save();

	}
	
	echo 200;
   }



/*
    public function actionTianya(){	//this is the function that calls paperCN

	ini_set('max_execution_time', 600);	//10 mins function max
	set_time_limit(600);

	Yii::import('ext.Scrapper.Scrapper', true);	//toutiao only for now
	$array = Scrapper::tianya();

	foreach($array as $arr){

		$post = Posts::model()->findByAttributes(array('link'=>$arr['url'], 'auto'=>1));
		if($post){
			//是不是应该跳过整个？
			continue;
		}
		$post = new Posts;
		$post->auto = 1;
		$post->link = $arr['url'];
		$post->name = $arr['title'];

		$post->category_id = 30;

		$rand_time = rand(1, 1200);	//20 mins
		$post->create_time = time() - $rand_time;

		$post->fake_up = rand(50, 300);

		$post->user_id = rand(175235, 175327);	//system accounts
		$post->type = 1;		//link
		$post->thumb_pic = '/images/douban.jpeg';	//douban logo
		$post->save();

	}
	
	echo 200;
   }
*/



    protected function ParseRss($rss){

	$feed_url = $rss->url;
    	// create curl resource
    	$ch = curl_init();
    	// set url
    	curl_setopt($ch, CURLOPT_URL, $feed_url);
    	//return the transfer as a string
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    	// $output contains the output string
    	$content = curl_exec($ch);
    	// close curl resource to free up system resources
    	curl_close($ch); 

	$x = @simplexml_load_string($content);

	if (!$x) {
		$rss->failed = 1;
		$rss->save(false);
		return;
	}else{
		$rss->failed = 0;
		$rss->save(false);
	}

    	foreach($x->channel->item as $entry) {
		//check dup URL.
		//save them(check if isset), update rss feature.

		if(isset($entry) && isset($entry->link) && isset($entry->title)){

			$post = Posts::model()->findByAttributes(array('link'=>(string)$entry->link, 'auto'=>1, 'name'=>(string)$entry->title));
			if($post){
				//因为所有的feed都是按时间倒序排列，一旦我们找到一样的，说明接下来的也一样，我们可以不去算这个feed了。
				return;
			}

			$post = new Posts;
			$post->auto = 1;
			$post->link = $entry->link;
			$post->name = strip_tags($entry->title);
			$post->category_id = $rss->category_id;

			$rand_time = rand(1, 1200);	//20 mins

			$post->create_time = time() - $rand_time;

			$post->fake_up = rand(50, 300);

			//注意 这里要改随机

			$post->user_id = rand(175235, 175327);	//system accounts
			$post->type = 1;		//link
			
			if($rss->video){
				$post->video_html = (string)$entry->link;
			}

			if(!$post->thumb_pic){
				$json = $post->getTitle((string)$entry->link);
				$array = json_decode($json, TRUE);
				if(isset($array['thumbnail_url'])){
					$post->thumb_pic = $array['thumbnail_url'];
				}
				if(isset($array['html'])){
					$post->video_html = $array['html'];
				}
			}
			if(!$post->thumb_pic){
				$post->fake_up = round($post->up / 2);
			}
			$post->save();
			$rss->failed = 0;
			$rss->save(false);

		}else{
			$rss->failed = 1;
			$rss->save(false);
		}

    	}

	echo "finished processing RSS ".$rss->id."<br>";

	return;

    }


    public function actionTech(){	

    	throw new CHttpException(404, "The requested link does not exist.");

	ini_set('max_execution_time', 900);	//15 mins function max
	set_time_limit(900);	

        //prevent anyone else from using our cron
        if ($_SERVER['REMOTE_ADDR'] !== '52.8.247.253' && (!isset($_SERVER['HTTP_CF_CONNECTING_IP']) || $_SERVER['HTTP_CF_CONNECTING_IP'] != '52.8.247.253')) {
        	//throw new CHttpException(404, "The requested link does not exist.");
        }

	$rsss = RSS::model()->findAllByAttributes(array('category_id'=>3, 'pause'=>0));

	
	foreach($rsss as $rss){
		$this->ParseRss($rss);
		$rss->processed = time();
		$rss->save(false);
	}

	echo 200;

    }


    public function actionFunny(){	

    	throw new CHttpException(404, "The requested link does not exist.");

	ini_set('max_execution_time', 900);	//15 mins function max
	set_time_limit(900);		

        //prevent anyone else from using our cron
        if ($_SERVER['REMOTE_ADDR'] !== '52.8.247.253' && (!isset($_SERVER['HTTP_CF_CONNECTING_IP']) || $_SERVER['HTTP_CF_CONNECTING_IP'] != '52.8.247.253')) {
           // throw new CHttpException(404, "The requested link does not exist.");
        }

	$rsss = RSS::model()->findAllByAttributes(array('category_id'=>1, 'pause'=>0));

	foreach($rsss as $rss){
		$this->ParseRss($rss);
		$rss->processed = time();
		$rss->save(false);
	}

	echo 200;

    }


    public function actionOther(){	

    	throw new CHttpException(404, "The requested link does not exist.");

	ini_set('max_execution_time', 900);	//15 mins function max
	set_time_limit(900);	

        //prevent anyone else from using our cron
        if ($_SERVER['REMOTE_ADDR'] !== '52.8.247.253' && (!isset($_SERVER['HTTP_CF_CONNECTING_IP']) || $_SERVER['HTTP_CF_CONNECTING_IP'] != '52.8.247.253')) {
          //  throw new CHttpException(404, "The requested link does not exist.");
        }

	$rsss = RSS::model()->findAllByAttributes(array('category_id'=>30, 'pause'=>0));

	foreach($rsss as $rss){

		$this->ParseRss($rss);
		$rss->processed = time();
		$rss->save(false);
	}

	echo 200;
    }


    public function actionNews(){	

    	throw new CHttpException(404, "The requested link does not exist.");

	ini_set('max_execution_time', 900);	//15 mins function max
	set_time_limit(900);	

        //prevent anyone else from using our cron
        if ($_SERVER['REMOTE_ADDR'] !== '52.8.247.253' && (!isset($_SERVER['HTTP_CF_CONNECTING_IP']) || $_SERVER['HTTP_CF_CONNECTING_IP'] != '52.8.247.253')) {
           // throw new CHttpException(404, "The requested link does not exist.");
        }

	$rsss = RSS::model()->findAllByAttributes(array('category_id'=>2, 'pause'=>0));
	
	foreach($rsss as $rss){
		$this->ParseRss($rss);
		$rss->processed = time();
		$rss->save(false);
	}

	echo 200;

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