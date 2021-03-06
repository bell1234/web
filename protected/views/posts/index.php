<?php
/* @var $this PostsController */
/* @var $dataProvider CActiveDataProvider */
?>

<?php 
$this->pageTitle=Yii::app()->name . ' - 最火热的话题与信息'; 
$href = 'http://meiliuer.com'; 
Yii::app()->clientScript->registerLinkTag('canonical', null, $href); 
?>

<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 bottom50 top15">

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'id'=>'postlist',
        'pager' => array(
            'class' => 'ext.infiniteScroll.IasPager',
            'rowSelector'=>'.post_cell',
            'listViewId' => 'postlist',
            'header' => '',
        ),
	'viewData' => array('admin' => $admin),    //自己的variables
	'itemView'=>'_view',
    'template'=>'{items}{pager}',	//infinite scroll.
	'emptyText'=>'',

)); ?>	

</div>

<div class="col-lg-4 col-md-4 col-sm-4 hidden-xs paddingleft50 top20">
	<?php $this->renderPartial('_sidebar'); ?>
</div>

<div id="toTop" style="display: none;"><i class="glyphicon glyphicon-triangle-top"></i></div>
