<?php
/* @var $this PostsController */
/* @var $model Posts */

$this->pageTitle = $model->name . " － 没六儿";

$self = 0;
$guest = 0;
$alreadyUp = 0;
$alreadyDown = 0;
$user = Users::model()->findByPk($model->user_id);
if($model->user_id == Yii::app()->user->id){
	$self = 1;
}
if(Yii::app()->user->isGuest){
	$guest = 1;
}else{
	$me = Users::model()->findByPk(Yii::app()->user->id);
	$alreadyUp = PostsVotes::model()->findByAttributes(array('post_id'=>$model->id, 'user_id'=>$me->id, 'type'=>1));
	$alreadyDown = PostsVotes::model()->findByAttributes(array('post_id'=>$model->id, 'user_id'=>$me->id, 'type'=>2));
}
if($model->type == 1){
	$post_link = $model->link;
}else{
	$post_link = "/posts/".$model->id;
}
?>

<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 bottom50 top10">

	<div id="post_cell_<?php echo $model->id; ?>" style="border-bottom:none; <?php if($model->hide): ?>background-color:#FBCDCD;<?php endif; ?>" class="post_cell col-lg-12 col-md-12 col-sm-12 col-xs-12">

	<?php if($admin): ?>

		<div class="post_votes">
			<input id="admin_vote_field_<?php echo $model->id; ?>" class="admin_vote_field" value="<?php echo ($model->up - $model->down); ?>" />
			<a id="admin_vote_up_<?php echo $model->id; ?>" href="#" onclick="admin_vote(<?php echo $model->id; ?>); return false;" class="btn btn-sm btn-default top30">
				修改
			</a>
		</div>

	<?php else: ?>

		<div class="post_votes">
			<a id="vote_up_<?php echo $model->id; ?>" class="vote_up <?php if($alreadyUp): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $model->id; ?>, 1, <?php echo $guest; ?>, <?php echo $self; ?>); return false;">
				<div><i class="glyphicon glyphicon-triangle-top"></i></div>
				<div class="vote_num"><?php echo ($model->up - $model->down); ?></div>
			</a>
			<a class="vote_down <?php if($alreadyDown): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $model->id; ?>, 2, <?php echo $guest; ?>, <?php echo $self; ?>); return false;"><i class="glyphicon glyphicon-triangle-bottom"></i></a>
		</div>
	
	<?php endif; ?>

		<div class="post_pic">

			<a class="black_link" target="_blank" href="<?php echo $post_link;?>" rel="nofollow">
				<?php if(!$model->thumb_pic): ?>
					<img id="link_thumb_pic" class="link_thumb_pic" src="/images/shaka.png" />
				<?php else: ?>
					<img id="link_thumb_pic" class="link_thumb_pic" src="<?php echo $model->thumb_pic; ?>" />
				<?php endif; ?>
			</a>
		</div>

		<div class="post_content col-lg-9 col-md-8 col-sm-8 col-xs-7 nopaddingleft">

			<div style="min-height:66px;">
				<h1 class="post_header" style="margin-top:0px;">
					<a class="black_link" target="_blank" href="<?php echo $post_link;?>" rel="nofollow">
						<?php echo $model->name; ?>
					</a>
				</h1>
				<p><a style="word-wrap:break-word;" href="<?php echo $post_link;?>" rel="nofollow"><?php echo $post_link;?></a></p>
			</div>

			<div class="post_footer grey small">
				<a class="grey" target="_blank" href="/users/<?php echo $user->id; ?>"><?php echo $user->username; ?></a>
				发布于
				<abbr class="timeago" title="<?php echo date('c',($model->create_time)); ?>">
					<?php echo date('M jS, Y',($model->create_time)); ?>
				</abbr>


				<div style="margin-top:4px;">
					<span class="left grey bold">分享到:</span>

            	    			<div class="bdsharebuttonbox" style="position:absolute; margin-top:-25px; margin-left:45px;">
                				<a title="分享到微信" class="bds_weixin" href="#" data-cmd="weixin" data-title="<?php echo $model->name; ?>" data-img="<?php echo $model->thumb_pic; ?>" data-id="<?php echo $model->id;?>"></a>
                				<a title="分享到新浪微博" class="bds_tsina" href="#" data-cmd="tsina" data-title="<?php echo $model->name; ?>" data-img="<?php echo $model->thumb_pic; ?>" data-id="<?php echo $model->id;?>"></a>
                				<a title="分享到QQ空间" class="bds_qzone" href="#" data-cmd="qzone" data-title="<?php echo $model->name; ?>" data-img="<?php echo $model->thumb_pic; ?>" data-id="<?php echo $model->id;?>"></a>
            				</div>

					<?php if(($admin && $admin->regulate) || Yii::app()->user->id == $model->user_id): ?>
						<a id="delete_btn_<?php echo $model->id; ?>" class="left bold delete_btn" style="margin-left:80px; <?php if($model->hide): ?>display:none;<?php endif; ?>" href="#" onclick="delete_post(<?php echo $model->id; ?>, 0); return false;">删除</a>
						<a id="undelete_btn_<?php echo $model->id; ?>" class="left bold" style="margin-left:80px; <?php if(!$model->hide): ?>display:none;<?php endif; ?>" href="#" onclick="undelete_post(<?php echo $model->id; ?>, 0); return false;">已删除 - 点击恢复</a>
					<?php endif; ?>

				</div>

			</div>
		</div>
	</div>

		<div class="clear"></div>

		<div class="post_comments_box">
		<?php 
			$this->renderPartial('_comment_form', array('model'=>$comment)); 
		?>
		</div>

		<div class="post_comment_counter left50">
			<?php echo $model->comments; ?> 个评论
		</div>

		<div class="post_comments">
			<?php
				$this->renderPartial('_comments', array('dataProvider'=>$dataProvider, 'model'=>$model));  
			?>
		</div>

</div>




<div class="col-lg-4 col-md-4 col-sm-4 hidden-xs paddingleft50 top20">
	<?php $this->renderPartial('_sidebar'); ?>
</div>
<?php
	if($model->views == 1 && !$model->thumb_pic){
		$this->renderPartial('_hidden_field_for_ajax', array('model'=>$model)); 
	}
?>