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
?>


<div id="post_cell_<?php echo $data->id; ?>" class="post_cell col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="post_votes">
			<a id="vote_up_<?php echo $data->id; ?>" class="vote_up <?php if($alreadyUp): ?>voted<?php endif; ?>" href="#" ontouchend="vote(<?php echo $data->id; ?>, 1, <?php echo $guest; ?>, <?php echo $self; ?>); return false;" onclick="vote(<?php echo $data->id; ?>, 1, <?php echo $guest; ?>, <?php echo $self; ?>); return false;">
				<div><i class="glyphicon glyphicon-triangle-top"></i></div>
				<div class="vote_num"><?php echo ($data->up - $data->down); ?></div>
			</a>
			<a class="vote_down <?php if($alreadyDown): ?>voted<?php endif; ?>" href="#" ontouchend="vote(<?php echo $data->id; ?>, 2, <?php echo $guest; ?>, <?php echo $self; ?>); return false;" onclick="vote(<?php echo $data->id; ?>, 2, <?php echo $guest; ?>, <?php echo $self; ?>); return false;"><i class="glyphicon glyphicon-triangle-bottom"></i></a>
		</div>

		<div class="post_pic">
			<a class="black_link" target="_blank" href="<?php echo $post_link;?>" rel="nofollow">
				<img style="width:90px; height:90px;" src="<?php echo $data->thumb_pic; ?>" />
			</a>
		</div>

		<div class="post_content col-lg-9 col-md-8 col-sm-8 col-xs-7 nopaddingleft">
			<h4 class="post_header" style="height:45px;">
				<a class="black_link" target="_blank" href="<?php echo $post_link;?>" rel="nofollow">
					<?php echo $data->name; ?>
				</a>
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