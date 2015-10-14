<?php
/* @var $this PostsController */
/* @var $dataProvider CActiveDataProvider */
?>

<?php $this->pageTitle=Yii::app()->name . ' - 账户设置'; ?>

<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">

<?php if($model->id == Yii::app()->user->id): ?>
	<h1 class="bottom30" style="font-size:28px;">我的发布历史</h1>
<?php else: ?>
	<h1 class="bottom30" style="font-size:28px;"><?php echo $model->username;?>的发布历史</h1>
<?php endif; ?>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'id'=>'postlist',
        'pager' => array(
            'class' => 'ext.infiniteScroll.IasPager',
            'rowSelector'=>'.post_cell',
            'listViewId' => 'postlist',
            'header' => '',
            'options'=>array(
                'triggerPageTreshold' => 8,
                'onRenderComplete'=>'js:function () {
	 		$(".timeago").timeago();
		}'),
        ),
        'afterAjaxUpdate'=>'function(id,data){ 
		$(".timeago").timeago();
	 }',
	'itemView'=>'/posts/_view',
        'template'=>'{items}{pager}',	//infinite scroll.
	'emptyText'=>'',

)); ?>	

</div>

<div class="col-lg-4 col-md-4 col-sm-4 hidden-xs paddingleft50 top10">
	<?php $this->renderPartial('/posts/_sidebar'); ?>
</div>