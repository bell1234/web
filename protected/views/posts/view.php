<?php
/* @var $this PostsController */
/* @var $model Posts */

$this->pageTitle = $model->name . " － 没六儿";

$self = 0;
$guest = 0;
$alreadyUp = 0;
$alreadyDown = 0;
$user = Users::model()->findByPk($model->user_id);
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

	<div id="post_cell_<?php echo $model->id; ?>" style="border-bottom:none; margin-top:-20px; margin-bottom:0px; <?php if($model->hide): ?>background-color:#FBCDCD;<?php endif; ?>" class="post_cell col-lg-12 col-md-12 col-sm-12 col-xs-12">

		<div class="post_content">

	<?php if($admin && $admin->vote): ?>

		<div class="post_votes">
			<input id="admin_vote_field_<?php echo $model->id; ?>" class="admin_vote_field" value="<?php echo ($model->fake_up + $model->up - $model->down); ?>" />
			<a id="admin_vote_up_<?php echo $model->id; ?>" href="#" onclick="admin_vote(<?php echo $model->id; ?>); return false;" class="btn btn-sm btn-default top30">
				修改
			</a>
		</div>

	<?php else: ?>

			<div class="post_votes" style="height:100%;">
				<a id="vote_up_<?php echo $model->id; ?>" class="vote_up <?php if($alreadyUp): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $model->id; ?>, 1, <?php echo $guest; ?>, 0); return false;">
					<div><i class="glyphicon glyphicon-triangle-top"></i></div>
					<div class="vote_num"><?php echo ($model->fake_up + $model->up - $model->down); ?></div>
				</a>
				<a class="vote_down <?php if($alreadyDown): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $model->id; ?>, 2, <?php echo $guest; ?>, 0); return false;"><i class="glyphicon glyphicon-triangle-bottom"></i></a>
			</div>

	<?php endif; ?>
			<div style="min-height:60px;">
				<h1 class="post_header bottom15" style="padding-left:65px;">
					<?php echo $model->name; ?>
				</h1>

				<div class="post_description" style="padding-left:65px;">
					<?php echo $model->description; ?>
				</div>
			</div>

			<div class="post_footer grey small paddingleft50 top20 bottom10">
				<img class="extra_small_avatar img-circle" src="<?php echo $user->avatar; ?>" />
				<a class="grey" href="/users/<?php echo $user->id; ?>"><?php echo $user->username; ?></a>
				发布于
				<abbr class="timeago" title="<?php echo date('c',($model->create_time)); ?>">
					<?php echo date('M jS, Y',($model->create_time)); ?>
				</abbr>
				<div style="margin-top:4px;">
					<span class="left grey bold">分享到:</span>

            	    			<div class="bdsharebuttonbox" style="position:absolute; margin-top:-25px; margin-left:45px;">
                					<a title="分享到微信" class="bds_weixin" href="#" data-cmd="weixin" data-title="<?php echo $model->name; ?>" data-img="<?php echo $model->thumb_pic; ?>" data-id="<?php echo $model->id;?>"></a>
                					<a title="分享到新浪微博" class="bds_tsina" href="#" data-cmd="tsina" data-title="<?php echo $model->name; ?>" data-img="<?php echo $model->thumb_pic; ?>" data-id="<?php echo $model->id;?>"></a>
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

			<div class="post_comment_counter left50 bottom10">
				<?php echo $model->comments; ?> 个评论
			</div>

			<div class="post_comments">
				<?php
					$this->renderPartial('_comments', array('dataProvider'=>$dataProvider, 'model'=>$model,'reply'=>$reply)); 
				?>
			</div>


</div>




<div class="col-lg-4 col-md-4 col-sm-4 hidden-xs paddingleft50 top20">
	<?php $this->renderPartial('_sidebar'); ?>
</div>