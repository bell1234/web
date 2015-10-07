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
?>


<div id="post_cell_<?php echo $data->id; ?>" class="post_cell col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="post_votes">
			<a class="vote_up <?php if($alreadyUp): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $data->id; ?>, 1, <?php echo $guest; ?>, <?php echo $self; ?>); return false;">
				<div><i class="glyphicon glyphicon-triangle-top"></i></div>
				<div class="vote_num"><?php echo ($data->up - $data->down); ?></div>
			</a>
			<a class="vote_down <?php if($alreadyDown): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $data->id; ?>, 2, <?php echo $guest; ?>, <?php echo $self; ?>); return false;"><i class="glyphicon glyphicon-triangle-bottom"></i></a>
		</div>

		<div class="post_pic">
			<img src="http://placehold.it/90x90" />
		</div>

		<div class="post_content">
			<h4 style="height:45px;">
			<?php if($data->type == 1): ?>
				<a class="black_link" target="_blank" href="<?php echo $data->link;?>">
					<?php echo $data->name; ?>
				</a>
			<?php else: ?>
				<a class="black_link" target="_blank" href="/posts/<?php echo $data->id;?>">
					<?php echo $data->name; ?>
				</a>
			<?php endif; ?>
			</h4>
			<div class="post_footer grey small">
				<a class="grey" target="_blank" href="/users/<?php echo $user->name_token; ?>"><?php echo $user->username; ?></a>
				发布于
				<abbr class="timeago" title="<?php echo date('c',($data->create_time)); ?>">
					<?php echo date('M jS, Y',($data->create_time)); ?>
				</abbr>
			</div>
		</div>
</div>

<!--
<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
	<?php echo CHtml::encode($data->name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('description')); ?>:</b>
	<?php echo CHtml::encode($data->description); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('link')); ?>:</b>
	<?php echo CHtml::encode($data->link); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('shorturl')); ?>:</b>
	<?php echo CHtml::encode($data->shorturl); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('user_id')); ?>:</b>
	<?php echo CHtml::encode($data->user_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('create_time')); ?>:</b>
	<?php echo CHtml::encode($data->create_time); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('up')); ?>:</b>
	<?php echo CHtml::encode($data->up); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('down')); ?>:</b>
	<?php echo CHtml::encode($data->down); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('comments')); ?>:</b>
	<?php echo CHtml::encode($data->comments); ?>
	<br />

</div>
-->