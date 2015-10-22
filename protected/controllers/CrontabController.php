<?php

class CrontabController extends Controller
{


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

    	$x = simplexml_load_string($content);

	if(!$x){
		$rss->failed = 1;
		$rss->save();
		return;
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

			$post->up = round($rand_time / rand(1, 6));	//test...

			//注意 这里要改随机

			$post->user_id = rand(175235, 175327);	//system accounts
			$post->type = 1;		//link
			
			if($rss->video){
				$post->video_html = (string)$entry->link;
			}

			if(!$post->thumb_pic){
				$json = $post->getTitle((string)$entry->link);
				$array = json_decode($json);
				if(isset($array[1])){
					$post->thumb_pic = $array[1];
				}
				if(isset($array[2])){
					$post->video_html = $array[2];
				}
			}
			$post->save();

		}else{
			$rss->failed = 1;
			$rss->save();
		}

    	}

	return;

    }



    public function actionTech(){	

	ini_set('max_execution_time', 900);	//15 mins function max
	set_time_limit(0);	

        //prevent anyone else from using our cron
        if ($_SERVER['REMOTE_ADDR'] !== '54.204.37.100' && (!isset($_SERVER['HTTP_CF_CONNECTING_IP']) || $_SERVER['HTTP_CF_CONNECTING_IP'] != '54.204.37.100')) {
            throw new CHttpException(404, "The requested link does not exist.");
        }

	$rsss = RSS::model()->findAllByAttributes(array('category_id'=>3));
	
	foreach($rsss as $rss){
		$this->ParseRss($rss);
	}

	return;

    }


    public function actionFunny(){	

	ini_set('max_execution_time', 900);	//15 mins function max
	set_time_limit(0);		

        //prevent anyone else from using our cron
        if ($_SERVER['REMOTE_ADDR'] !== '54.204.37.100' && (!isset($_SERVER['HTTP_CF_CONNECTING_IP']) || $_SERVER['HTTP_CF_CONNECTING_IP'] != '54.204.37.100')) {
            throw new CHttpException(404, "The requested link does not exist.");
        }

	$rsss = RSS::model()->findAllByAttributes(array('category_id'=>1));
	
	foreach($rsss as $rss){
		$this->ParseRss($rss);
	}

	return;

    }


    public function actionOther(){		

        //prevent anyone else from using our cron
        if ($_SERVER['REMOTE_ADDR'] !== '54.204.37.100' && (!isset($_SERVER['HTTP_CF_CONNECTING_IP']) || $_SERVER['HTTP_CF_CONNECTING_IP'] != '54.204.37.100')) {
            throw new CHttpException(404, "The requested link does not exist.");
        }

	$rsss = RSS::model()->findAllByAttributes(array('category_id'=>30));
	
	foreach($rsss as $rss){
		$this->ParseRss($rss);
	}

	return;
	

    }


    public function actionNews(){		

        //prevent anyone else from using our cron
        if ($_SERVER['REMOTE_ADDR'] !== '54.204.37.100' && (!isset($_SERVER['HTTP_CF_CONNECTING_IP']) || $_SERVER['HTTP_CF_CONNECTING_IP'] != '54.204.37.100')) {
            throw new CHttpException(404, "The requested link does not exist.");
        }

	$rsss = RSS::model()->findAllByAttributes(array('category_id'=>2));
	
	foreach($rsss as $rss){
		$this->ParseRss($rss);
	}

	return;

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