<?php

	$url = "/posts/".$data->post_id;

	$notification = "";
	$user = Users::model()->findByPk($data->sender);	//check error
	$post = Posts::model()->findByPk($data->post_id);	//check error

	$post_name = (strlen($post->name) > 50) ? mb_substr($post->name, 0, 48,'utf-8') . '...' : $post->name;

	$nameHtml = "<span class='color_blue' >".$user->username."</span>";
	if($data->other > 0){
		$nameHtml = "<span class='color_blue' >".$user->username."</span> 和另外<span class='color_blue' >".$data->other."</span>人";
	}

	$postHtml = "<span class='color_blue' >".$post_name."</span>";

if ( $data->type_id == 1 ) {		//post share
	//$notification = $nameHtml." is answering your question: <br>".$questionHtml;
}
else if ($data->type_id == 2){	//评论
	$notification = $nameHtml." 评论了您的发布: <br>".$postHtml;
}
else if ($data->type_id == 3 ){
	$notification = $nameHtml." 在下列发布中回复了您的评论: <br>".$postHtml;
}
?>

<div class="red-notification" style="<?php if(!$data->read): ?>background-color:#fffff0;<?php endif; ?>">
	<a href="<?php echo $url; ?>">

		<li class="notification_item">

			<div class="avatar" >
				<img class="img-circle" alt="none" src="<?php echo $user->avatar;?>" />
			</div>

			<div class="noti" >
				<p style="margin-bottom:0px;" >
					<?php echo $notification; ?>
				</p>

				<acronym class="timeago" style="font-size:10px; color:#999; display:block;" title="<?php echo date(DATE_ISO8601, $data->create_time); ?>">
					<?php echo date('M jS, Y', $data->create_time); ?>
				</acronym>
			</div>

			<div class="clear"></div>
		</li>
	</a>
</div>
