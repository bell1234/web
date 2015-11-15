<?php
/* @var $this PostsController */
/* @var $dataProvider CActiveDataProvider */
?>

<?php $this->pageTitle=Yii::app()->name . ' - 账户设置'; ?>

<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">

<?php if($model->id == Yii::app()->user->id): ?>
	<h1 class="bottom30" style="font-size:26px;"><img class="big_avatar img-circle" src="<?php echo $model->avatar; ?>" /> 我的提交</h1>
<?php else: ?>
	<h1 class="bottom30" style="font-size:26px;"><img class="big_avatar img-circle" src="<?php echo $model->avatar; ?>" /> <?php echo $model->username;?>的提交</h1>
<?php endif; ?>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'id'=>'mypostlist',
        'pager' => array(
            'class' => 'ext.infiniteScroll.IasPager',
            'rowSelector'=>'.post_cell',
            'listViewId' => 'mypostlist',
            'header' => '',
        ),
	'viewData' => array('admin' => $admin),    //自己的variables
	'itemView'=>'/posts/_view',
        'template'=>'{items}{pager}',	//infinite scroll.
	'emptyText'=>'你还没有提交过任何信息!',

)); ?>	


</div>

<div class="col-lg-4 col-md-4 col-sm-4 hidden-xs paddingleft50 top10">
	<?php $this->renderPartial('/posts/_sidebar'); ?>
</div>