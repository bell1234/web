<?php
/* @var $this PostsController */
/* @var $data Posts */

$guest = 0;
$alreadyUp = 0;
$alreadyDown = 0;
$user = Users::model()->findByPk($data->user_id);
if(!$user){
	echo $data->id;
}else{
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

<div id="post_cell_<?php echo $data->id; ?>" class="post_cell col-lg-12 col-md-12 col-sm-12 col-xs-12" style="<?php if($data->hide): ?>background-color: #FBCDCD;<?php endif; ?>">

	<?php if($admin && $admin->vote): ?>

		<div class="post_votes">
			<input id="admin_vote_field_<?php echo $data->id; ?>" class="admin_vote_field" value="<?php echo ($data->fake_up + $data->up - $data->down); ?>" />
			<a id="admin_vote_up_<?php echo $data->id; ?>" href="#" onclick="admin_vote(<?php echo $data->id; ?>); return false;" class="btn btn-sm btn-default top20">
				修改
			</a>
		</div>

	<?php else: ?>

		<div class="post_votes">
			<a id="vote_up_<?php echo $data->id; ?>" class="vote_up <?php if($alreadyUp): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $data->id; ?>, 1, <?php echo $guest; ?>, 0); return false;">
				<div><i class="glyphicon glyphicon-triangle-top"></i></div>
				<div class="vote_num"><?php echo ($data->fake_up + $data->up - $data->down); ?></div>
			</a>
			<a class="vote_down <?php if($alreadyDown): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $data->id; ?>, 2, <?php echo $guest; ?>, 0); return false;"><i class="glyphicon glyphicon-triangle-bottom"></i></a>
		</div>

	<?php endif; ?>

		<div class="post_pic">
			<a class="black_link" target="_blank" href="<?php echo $post_link;?>" rel="nofollow">
				<?php if(!$data->thumb_pic): 
					  $thumb = "/images/shaka.png";
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

					<a href="<?php echo "/posts/".$data->id; ?>" class="left dark_grey bold">评论(<?php echo $data->comments;?>)</a>
					
					<span class="hidden-xs">
								<b class="left10 dark_grey">分享: </b>
            	    			<div class="bdsharebuttonbox" style="position:absolute; margin-top:-25px; margin-left:85px;">
                					<a title="分享到微信" class="bds_weixin" href="#" data-cmd="weixin" data-title="<?php echo $data->name; ?>" data-img="<?php echo $data->thumb_pic; ?>" data-id="<?php echo $data->id;?>"></a>
                					<a title="分享到新浪微博" class="bds_tsina" href="#" data-cmd="tsina" data-title="<?php echo $data->name; ?>" data-img="<?php echo $data->thumb_pic; ?>" data-id="<?php echo $data->id;?>"></a>
                          		</div>
                    </span>

                    <span class="right" style="float:right;">
						<img class="extra_small_avatar img-circle" src="<?php echo $user->avatar; ?>" />
						<a class="grey" href="/users/<?php echo $user->id; ?>"><?php echo $user->username; ?></a>
					</span>

<!--
				<abbr class="timeago" title="<?php echo date('c',($data->create_time)); ?>">
					<?php echo date('M jS, Y',($data->create_time)); ?>
				</abbr>
-->				

					<?php if($admin && $admin->regulate): ?>

<?php 
if($data->category_id): 
	echo "(".$data->category->name.")"; 
endif; 
?>

						<a id="delete_btn_<?php echo $data->id; ?>" class="left bold delete_btn" style="float:right;  margin-right:15px; <?php if($data->hide): ?>display:none;<?php endif; ?>" href="#" onclick="delete_post(<?php echo $data->id; ?>, 0); return false;">删除</a>
						<a id="undelete_btn_<?php echo $data->id; ?>" class="left bold" style="float:right; margin-right:15px; <?php if(!$data->hide): ?>display:none;<?php endif; ?>" href="#" onclick="undelete_post(<?php echo $data->id; ?>, 0); return false;">已删除 - 点击恢复</a>


					<?php endif; ?>

				</div>

			</div>
		</div>
</div>
<?php } ?>