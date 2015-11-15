<?php

Yii::import('ext.PHPDomParser.simple_html_dom', true);

//header('Content-type: application/json; charset=utf-8');
//require_once Yii::app()->basePath . '/extensions/PHPDomParser/simple_html_dom.php';

class Scrapper
{

	public function toutiao($category = '__all__', $posts = 60, $count = 60)
	{

		$offset = 0;

		$pages = $posts/$count;
	
		$url = "http://toutiao.com/api/article/recent/?count=:count&category={$category}&offset=:offset";

		//$url = "http://toutiao.com/api/article/recent/?count=:count";

		$response = array();

		$i = $j = 0;

		$next = null;

		while($pages > 0)
		{
			$str_replace = array(
				':count'	=> ($pages < 1?ceil($count*$pages):$count),
				':offset'	=> 0, //$i*$count
			);

			$urlToScrape = str_replace(array_keys($str_replace), array_values($str_replace), $url);

			$data = json_decode(file_get_contents($urlToScrape . ($next?'&max_behot_time='.$next:'')), true);

			if(json_last_error() === JSON_ERROR_NONE)
			{
				foreach($data['data'] as $post)
				{
					$response[$j] = array(
						'title'		=> $post['title'],
						'url'		=> $post['article_url'],
						'images'	=> array(),
						'category'	=> $post['tag'],
						'likes'		=> $post['digg_count'],
						'dislikes'	=> $post['bury_count']
					);

					foreach($post['image_list'] as $image)
					{
						$response[$j]['images'][] = $image['url'];
					}
					$j++;
				}

				$next = isset($data['next']['max_behot_time'])?$data['next']['max_behot_time']:null;
			}

			$i++;
			$pages--;
		}

		return $response;
	}


	public function paperCn($category = '__all__', $pages = 3)
	{

		$offset = 0;

		//$pages = $posts/$count;

		$urlFirstPage = "http://www.thepaper.cn/index_masonry.jsp?topCids=";
		$urlloadPage  = "http://www.thepaper.cn/load_chosen.jsp?nodeids=25949&topCids=:topCids&pageidx=:pageIndex&lastTime=:lastTime";
		
		$response = array();

		$i = $j = 0;

		$milliseconds = number_format(microtime(true), 0, '', '');
		$pageIndex = 1;
		$topCids = array();

		$domParser = new simple_html_dom();

		while($pageIndex <= $pages)
		{	
			if($pageIndex === 1)
			{
				$domParser->load(file_get_contents($urlFirstPage . '&_=' . $milliseconds));
			}
			else
			{
				$str_replace = array(
						':topCids'	=> implode(',', array_slice($topCids, 0, 5, true)),
						':pageIndex' => $pageIndex,
						':lastTime'  => $lastTime,
					);

				$urlToLoad = str_replace(array_keys($str_replace), array_values($str_replace), $urlloadPage);

				$domParser->load(file_get_contents($urlToLoad));
			}
		
			$list = $domParser->find('div[class=news_li]');
			
			foreach($list as $post)
			{
				$children = $post->children();
				
				if($children)
				{
					$response[] = array(
						'title'	=> $children[1]->plaintext,
						'url'	=> 'http://thepaper.cn/' . $children[0]->children(0)->attr['href'],
						'images' => array(
								$children[0]->children(0)->children(0)->attr['src']
							),
					);
					$topCids[] = $children[0]->children(0)->attr['data-id'];
				}
	
				if($post->attr['id'] == 'last1')
				{
					$lastTime = $post->attr['lasttime'];
					$pageIndex = $post->attr['pageindex'];
				}
			}
	
			$pageIndex++;
		}

		return $response;
	}




	public function douban()
	{
		$url = "http://www.douban.com";
		
		$response = array();
		
		$options = array(
			'method'	=> 'GET',
			'headers'	=> array(
    			'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36',
    			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
				'Accept-Encoding: gzip, deflate, sdch',
				'Accept-Language: en-US,en;q=0.8',
				'Cookie: bid="j0u2LeDCYOs"; ll="108161"; _pk_id.100001.8cb4=19a2a74f767126f3.1446866592.2.1446959806.1446866592.; _pk_ses.100001.8cb4=*; __utma=30149280.1855095563.1446866602.1446866602.1446959812.2; __utmb=30149280.1.10.1446959812; __utmc=30149280; __utmz=30149280.1446866602.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)'
  			)
		);

		$domParser = new simple_html_dom();

		$html = Scrapper::sendCurlRequest($url, $options);

		$domParser->load($html);
	
		$list = $domParser->find('div[class=notes]');
		$list = $list[0];

		foreach($list->children(0)->children() as $post)
		{
			if(isset($post->attr['class']) && $post->attr['class'] == 'first')
			{
				$response[] = array(
					'title'	=> $post->children(0)->children(0)->plaintext,
					'url'	=> $post->children(0)->children(0)->attr['href']
				);
			}
			else
			{
				$response[] = array(
					'title'	=> $post->children(0)->plaintext,
					'url'	=> $post->children(0)->attr['href']
				);
			}
		}

		return $response;
	}



  public function sendCurlRequest($url, $options)
  {
    $ch = curl_init();

    $options['method'] = $options['method']?:'GET';

    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST,  $options['method']);

    if($options['method'] != 'GET')
    {
      curl_setopt( $ch, CURLOPT_POST, true );
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $options['body']);
    }

    curl_setopt( $ch, CURLOPT_HTTPHEADER, $options['headers']);
    curl_setopt( $ch, CURLOPT_COOKIEJAR, './cookie');
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_ENCODING, '');

    $result = curl_exec( $ch );
    $info = curl_getinfo($ch);

    return $result;
  }




}