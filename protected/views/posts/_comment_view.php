<?php
/* @var $this PostsController */
/* @var $data Posts */

$self = 0;
$guest = 0;
$alreadyUp = 0;
$alreadyDown = 0;
$user = Users::model()->findByPk($data->user_id);
if(Yii::app()->user->isGuest){
	$guest = 1;
}else{
	$me = Users::model()->findByPk(Yii::app()->user->id);
	$alreadyUp = CommentsVotes::model()->findByAttributes(array('comment_id'=>$data->id, 'user_id'=>$me->id, 'type'=>1));
	$alreadyDown = CommentsVotes::model()->findByAttributes(array('comment_id'=>$data->id, 'user_id'=>$me->id, 'type'=>2));
}
$replies = Reply::model()->findAllByAttributes(array('comment_id'=>$data->id), array('order'=>'create_time ASC'));
?>

<script>
function reply_comment_<?php echo $data->id; ?>(name, uid){
	if(name == 1 ){
		$('#reply_form_<?php echo $data->id;?>').find('.reply-field').val('');
	}else if(name){
		$('#reply_form_<?php echo $data->id;?>').find('.reply-field').val('@'+name+' ');
	}
	$('#reply_form_<?php echo $data->id;?>').show();
	
 	$('#reply_form_<?php echo $data->id;?>').find('.reply-field').focus();
	
	$('#reply_form_<?php echo $data->id;?>').find('.reply-receiver-field').val(uid);
}
function hide_reply_comment_<?php echo $data->id; ?>(){
	$('#reply_form_<?php echo $data->id;?>').hide();
}
</script>

<div id="comment_cell_<?php echo $data->id; ?>" class="post_cell comment_cell col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="post_votes">
			<a id="vote_up_comment_<?php echo $data->id; ?>" class="vote_up <?php if($alreadyUp): ?>voted<?php endif; ?>" href="#" onclick="comment_vote(<?php echo $data->id; ?>, 1, <?php echo $guest; ?>, <?php echo $self; ?>); return false;">
				<div><i class="glyphicon glyphicon-triangle-top"></i></div>
				<div class="vote_num"><?php echo ($data->up - $data->down); ?></div>
			</a>
			<a class="vote_down <?php if($alreadyDown): ?>voted<?php endif; ?>" href="#" onclick="comment_vote(<?php echo $data->id; ?>, 2, <?php echo $guest; ?>, <?php echo $self; ?>); return false;"><i class="glyphicon glyphicon-triangle-bottom"></i></a>
		</div>

		<div class="post_content" style="padding-left:65px;">
			<div class="comment_des">
				<?php echo $data->description; ?>
			</div>
			<div class="post_footer grey small top10">
				<img class="extra_small_avatar img-circle" src="<?php echo $user->avatar; ?>" />
				<a class="grey" href="/users/<?php echo $user->id; ?>"><?php echo $user->username; ?></a>
				发布于
				<abbr class="timeago" title="<?php echo date('c',($data->create_time)); ?>">
					<?php echo date('M jS, Y',($data->create_time)); ?>
				</abbr>
				<?php if($data->edited): ?>
				(<abbr class="timeago" title="<?php echo date('c',($data->edited)); ?>">
					<?php echo date('M jS, Y',($data->edited)); ?>
				</abbr>编辑过)
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
				<!--<a href="#" onclick="edit_comment();">编辑</a>-->
				<?php endif; ?>
				<?php if($data->user_id == Yii::app()->user->id): ?>
					<b><a class="left10" href="#" onclick="reply_comment_<?php echo $data->id;?>(1, <?php echo $data->user_id; ?>); return false;">回复<!--(<?php echo Reply::model()->countByAttributes(array('comment_id'=>$data->id));?>)--></a></b>
				<?php else: ?>
					<b><a class="left10" href="#" onclick="reply_comment_<?php echo $data->id;?>('<?php echo $data->user->username;?>', <?php echo $data->user_id; ?>); return false;">回复<!--(<?php echo Reply::model()->countByAttributes(array('comment_id'=>$data->id));?>)--></a></b>
				<?php endif; ?>
			</div>
		</div>
		<div class="clear left50 paddingtop10" style="border-bottom: solid 1px #efefef;"></div>
		<?php 
			$this->renderPartial('_replies', array('comment'=>$data, 'replies'=>$replies)); 
		?>
		<?php
			$this->renderPartial('_reply', array('comment'=>$data, 'model'=>$reply)); 
		?>
</div>