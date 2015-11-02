<?php
/* @var $this PostsController */
/* @var $data Posts */

$self = 0;
$guest = 0;
$alreadyUp = 0;
$alreadyDown = 0;
$user = Users::model()->findByPk($data->user_id);
if(!$user){
	echo $data->id;
}else{
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

?>

<div id="post_cell_<?php echo $data->id; ?>" class="post_cell col-lg-12 col-md-12 col-sm-12 col-xs-12">

	<?php if($admin): ?>

		<div class="post_votes">
			<input id="admin_vote_field_<?php echo $data->id; ?>" class="admin_vote_field" value="<?php echo ($data->up - $data->down); ?>" />
			<a id="admin_vote_up_<?php echo $data->id; ?>" href="#" onclick="admin_vote(<?php echo $data->id; ?>); return false;" class="btn btn-sm btn-default top20">
				修改
			</a>
		</div>

	<?php else: ?>

		<div class="post_votes">
			<a id="vote_up_<?php echo $data->id; ?>" class="vote_up <?php if($alreadyUp): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $data->id; ?>, 1, <?php echo $guest; ?>, <?php echo $self; ?>); return false;">
				<div><i class="glyphicon glyphicon-triangle-top"></i></div>
				<div class="vote_num"><?php echo ($data->up - $data->down); ?></div>
			</a>
			<a class="vote_down <?php if($alreadyDown): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $data->id; ?>, 2, <?php echo $guest; ?>, <?php echo $self; ?>); return false;"><i class="glyphicon glyphicon-triangle-bottom"></i></a>
		</div>

	<?php endif; ?>

		<div class="post_pic">
			<a class="black_link" target="_blank" href="<?php echo $post_link;?>" rel="nofollow">
				<?php if(!$data->thumb_pic): 
					  $thumb = "http://meiliuer.com/images/shaka.png";
				      else:
					  $thumb = $data->thumb_pic;
				      endif;
				?>
					<img class="link_thumb_pic" id="link_thumb_pic" src="<?php echo $thumb; ?>" />
			</a>
		</div>

		<div class="post_content col-lg-9 col-md-8 col-sm-8 col-xs-7 nopaddingleft nopaddingright" style="margin-right:-10px;">
			<h4 class="post_header">
				<a class="black_link" target="_blank" href="<?php echo $post_link;?>" rel="nofollow">
					<?php echo $truncated; ?>
				</a>
			</h4>
			<div class="post_footer grey small" style="margin-top:6px;">
				<div style="margin-top:4px;">

					<a target="_blank" href="<?php echo "/posts/".$data->id; ?>" class="left dark_grey bold">评论<!--(<?php echo $data->comments;?>)--></a>
					<b class="left10 dark_grey">分享: </b>
            	    			<div class="bdsharebuttonbox" style="position:absolute; margin-top:-25px; margin-left:70px;"> <!--90-->
                				<a title="分享到微信" class="bds_weixin" href="#" data-cmd="weixin" data-title="<?php echo $data->name; ?>" data-img="<?php echo $data->thumb_pic; ?>" data-id="<?php echo $data->id;?>"></a>
                				<a title="分享到新浪微博" class="bds_tsina" href="#" data-cmd="tsina" data-title="<?php echo $data->name; ?>" data-img="<?php echo $data->thumb_pic; ?>" data-id="<?php echo $data->id;?>"></a>
                				<a title="分享到QQ空间" class="bds_qzone" href="#" data-cmd="qzone" data-title="<?php echo $data->name; ?>" data-img="<?php echo $data->thumb_pic; ?>" data-id="<?php echo $data->id;?>"></a>
            				</div>

				<a class="view_username grey" target="_blank" href="/users/<?php echo $user->id; ?>"><?php echo $user->username; ?></a>

<!--
				<abbr class="timeago" title="<?php echo date('c',($data->create_time)); ?>">
					<?php echo date('M jS, Y',($data->create_time)); ?>
				</abbr>
-->				

					<?php if($admin): ?>
						<a id="delete_btn_<?php echo $data->id; ?>" class="left bold delete_btn" style="margin-left:80px; <?php if($data->hide): ?>display:none;<?php endif; ?>" href="#" onclick="delete_post(<?php echo $data->id; ?>, 0); return false;">删除</a>
						<a id="undelete_btn_<?php echo $data->id; ?>" class="left bold" style="margin-left:80px; <?php if(!$data->hide): ?>display:none;<?php endif; ?>" href="#" onclick="undelete_post(<?php echo $data->id; ?>, 0); return false;">已删除 - 点击恢复</a>
					<?php endif; ?>

				</div>

			</div>
		</div>
</div>
<?php } ?>