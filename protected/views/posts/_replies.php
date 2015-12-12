<?php foreach($replies as $reply): 
$user = Users::model()->findByPk($reply->user_id);
$receiver = null;
if($user->id != $reply->receiver && $reply->receiver){
	$receiver = Users::model()->findByPk($reply->receiver);
}
?>
	<div class="reply_cell left50" style="word-break: break-all; font-size:12px; border-bottom:solid 1px #efefef; padding:5px; padding-left:16px;">
		<span class="right10"><b><a class="dark_grey" href="/users/<?php echo $user->id; ?>"><?php echo $user->username; ?></a><?php if($receiver): ?> @ <a class="dark_grey" href="/users/<?php echo $receiver->id; ?>"><?php echo $receiver->username; ?></a><?php endif; ?>: </b> <?php echo $reply->description; ?></span> 
		<?php  if(Yii::app()->user->id != $user->id):  ?>
			<b><a href="#" onclick="reply_comment_<?php echo $comment->id;?>('<?php echo $user->username; ?>', '<?php echo $user->id; ?>'); return false;">回复</a></b>
		<?php endif; ?>
	</div>
<?php endforeach; ?>
