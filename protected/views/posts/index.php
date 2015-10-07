<?php
/* @var $this PostsController */
/* @var $dataProvider CActiveDataProvider */
?>

<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
	<?php $this->widget('zii.widgets.CListView', array(
		'dataProvider'=>$dataProvider,
		'itemView'=>'_view',
                'template'=>'{items}{pager}',	//get pager from sp.
	)); ?>
</div>

<div class="col-lg-4 col-md-4 col-sm-4 hidden-xs paddingleft50 top10">
	<img src="/images/placeholder.png" style="width:100%;" /> 
</div>