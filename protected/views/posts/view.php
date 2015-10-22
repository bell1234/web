<?php
/* @var $this PostsController */
/* @var $model Posts */

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

<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 bottom50">

	<div id="post_cell_<?php echo $model->id; ?>" style="border-bottom:none;" class="post_cell col-lg-12 col-md-12 col-sm-12 col-xs-12">


		<div class="post_content">

			<div class="post_votes" style="height:100%;">
				<a id="vote_up_<?php echo $model->id; ?>" class="vote_up <?php if($alreadyUp): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $model->id; ?>, 1, <?php echo $guest; ?>, <?php echo $self; ?>); return false;">
					<div><i class="glyphicon glyphicon-triangle-top"></i></div>
					<div class="vote_num"><?php echo ($model->up - $model->down); ?></div>
				</a>
				<a class="vote_down <?php if($alreadyDown): ?>voted<?php endif; ?>" href="#" onclick="vote(<?php echo $model->id; ?>, 2, <?php echo $guest; ?>, <?php echo $self; ?>); return false;"><i class="glyphicon glyphicon-triangle-bottom"></i></a>
			</div>

			<div style="min-height:60px;">
				<h1 class="post_header paddingleft50 bottom15">
					<?php echo $model->name; ?>
				</h1>

				<div class="post_description paddingleft50">
					<?php echo $model->description; ?>
				</div>
			</div>

			<div class="post_footer grey small paddingleft50 top20 bottom10">
				<a class="grey" target="_blank" href="/users/<?php echo $user->id; ?>"><?php echo $user->username; ?></a>
				发布于
				<abbr class="timeago" title="<?php echo date('c',($model->create_time)); ?>">
					<?php echo date('M jS, Y',($model->create_time)); ?>
				</abbr>
			</div>

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

	</div>

</div>




<div class="col-lg-4 col-md-4 col-sm-4 hidden-xs paddingleft50 top10">
	<?php $this->renderPartial('_sidebar'); ?>
</div>