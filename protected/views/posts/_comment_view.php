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
	$alreadyUp = CommentsVotes::model()->findByAttributes(array('comment_id'=>$data->id, 'user_id'=>$me->id, 'type'=>1));
	$alreadyDown = CommentsVotes::model()->findByAttributes(array('comment_id'=>$data->id, 'user_id'=>$me->id, 'type'=>2));
}
?>


<div id="comment_cell_<?php echo $data->id; ?>" class="post_cell comment_cell col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="post_votes">
			<a id="vote_up_comment_<?php echo $data->id; ?>" class="vote_up <?php if($alreadyUp): ?>voted<?php endif; ?>" href="#" ontouchend="comment_vote(<?php echo $data->id; ?>, 1, <?php echo $guest; ?>, <?php echo $self; ?>); return false;" onclick="comment_vote(<?php echo $data->id; ?>, 1, <?php echo $guest; ?>, <?php echo $self; ?>); return false;">
				<div><i class="glyphicon glyphicon-triangle-top"></i></div>
				<div class="vote_num"><?php echo ($data->up - $data->down); ?></div>
			</a>
			<a class="vote_down <?php if($alreadyDown): ?>voted<?php endif; ?>" href="#" ontouchend="comment_vote(<?php echo $data->id; ?>, 2, <?php echo $guest; ?>, <?php echo $self; ?>); return false;" onclick="comment_vote(<?php echo $data->id; ?>, 2, <?php echo $guest; ?>, <?php echo $self; ?>); return false;"><i class="glyphicon glyphicon-triangle-bottom"></i></a>
		</div>

		<div class="post_content col-lg-9 col-md-8 col-sm-8 col-xs-7 nopaddingleft">
			<div style="min-height:62px;">
				<?php echo $data->description; ?>
			</div>
			<div class="post_footer grey small">
				<a class="grey" target="_blank" href="/users/<?php echo $user->name_token; ?>"><?php echo $user->username; ?></a>
				发布于
				<abbr class="timeago" title="<?php echo date('c',($data->create_time)); ?>">
					<?php echo date('M jS, Y',($data->create_time)); ?>
				</abbr>
				<?php if($data->edited): ?>
				<abbr class="timeago" title="<?php echo date('c',($data->create_time)); ?>">
					<?php echo date('M jS, Y',($data->create_time)); ?>
				</abbr>
				编辑过
				<?php endif; ?>
				<?php if($data->user_id == Yii::app()->user->id): ?>
				<script>
					function edit_comment(){
						$('div.comments_form').show();
					}

					function cancel_edit_comment(){
						$('div.comments_form').hide();
					}
				</script>
				<style>
					div.comments_form{
						display:none;
					}
				</style>
				<a href="#" onclick="edit_comment();">编辑评论</a>
				<?php endif; ?>
			</div>
		</div>
		<div class="clear left50 paddingtop10" style="border-bottom: solid 1px #efefef;"></div>
</div>