<?php
/* @var $this PostsController */
/* @var $data Posts */

$self = 0;
$guest = 0;
$alreadyUp = 0;
$alreadyDown = 0;
$user = Users::model()->findByPk($data->user_id);
if($data->user_id == Yii::app()->user->id){
	$self = 1;
}
if(Yii::app()->user->isGuest){
	$guest = 1;
}else{
	$me = Users::model()->findByPk(Yii::app()->user->id);
	$alreadyUp = PostsVotes::model()->findByAttributes(array('post_id'=>$data->id, 'user_id'=>$me->id, 'type'=>1));
	$alreadyDown = PostsVotes::model()->findByAttributes(array('post_id'=>$data->id, 'user_id'=>$me->id, 'type'=>2));
}
if($data->type == 1){
	$post_link = $data->link;
}else{
	$post_link = "/posts/".$data->id;
}

//need to do yum install php-mbstring
$truncated = (strlen($data->name) > 150) ? mb_substr($data->name, 0, 147,'utf-8') . '...' : $data->name;

$truncated_name = (strlen($user->username) > 15) ? mb_substr($user->username, 0, 12,'utf-8') . '...' : $user->username;
?>


<div id="post_cell_<?php echo $data->id; ?>" class="post_cell col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="post_votes">
			<a id="vote_up_<?php echo $data->id; ?>" class="vote_up <?php if($alreadyUp): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $data->id; ?>, 1, <?php echo $guest; ?>, <?php echo $self; ?>); return false;">
				<div><i class="glyphicon glyphicon-triangle-top"></i></div>
				<div class="vote_num"><?php echo ($data->up - $data->down); ?></div>
			</a>
			<a class="vote_down <?php if($alreadyDown): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $data->id; ?>, 2, <?php echo $guest; ?>, <?php echo $self; ?>); return false;"><i class="glyphicon glyphicon-triangle-bottom"></i></a>
		</div>

		<div class="post_pic">
			<a class="black_link" target="_blank" href="<?php echo $post_link;?>" rel="nofollow">
				<?php if(!$data->thumb_pic): 
					  $thumb = "http://meiliuer.com/images/shaka.png";
				      else:
					  $thumb = $data->thumb_pic;
				      endif;
				?>
					<img id="link_thumb_pic" style="width:90px; height:90px;" src="<?php echo $thumb; ?>" />
			</a>
		</div>

		<div class="post_content col-lg-9 col-md-8 col-sm-8 col-xs-7 nopaddingleft">
			<h4 class="post_header">
				<a class="black_link" target="_blank" href="<?php echo $post_link;?>" rel="nofollow">
					<?php echo $truncated; ?>
				</a>
			</h4>
			<div class="post_footer grey small" style="margin-top:6px;">
				<a class="grey" target="_blank" href="/users/<?php echo $user->id; ?>"><?php echo $truncated_name; ?></a>
				发布于
				<abbr class="timeago" title="<?php echo date('c',($data->create_time)); ?>">
					<?php echo date('M jS, Y',($data->create_time)); ?>
				</abbr>
				
				<div style="margin-top:4px;">
					<a target="_blank" href="<?php echo "/posts/".$data->id; ?>" class="left grey bold">评论<!--(<?php echo $data->comments;?>)--></a>
					<b class="left10">分享: </b>
            	    			<div class="bdsharebuttonbox" style="position:absolute; margin-top:-25px; margin-left:70px;"> <!--90-->
                				<a title="分享到微信" class="bds_weixin" href="#" data-cmd="weixin" data-title="<?php echo $data->name; ?>" data-img="<?php echo $data->thumb_pic; ?>" data-id="<?php echo $data->id;?>"></a>
                				<a title="分享到新浪微博" class="bds_tsina" href="#" data-cmd="tsina" data-title="<?php echo $data->name; ?>" data-img="<?php echo $data->thumb_pic; ?>" data-id="<?php echo $data->id;?>"></a>
                				<a title="分享到QQ空间" class="bds_qzone" href="#" data-cmd="qzone" data-title="<?php echo $data->name; ?>" data-img="<?php echo $data->thumb_pic; ?>" data-id="<?php echo $data->id;?>"></a>
            				</div>
				</div>


			</div>
		</div>
</div>