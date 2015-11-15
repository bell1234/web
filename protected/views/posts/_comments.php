<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'id'=>'commentlist',
        'pager' => array(
            'class' => 'ext.infiniteScroll.IasPager',
            'rowSelector'=>'.comment_cell',
            'listViewId' => 'commentlist',
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
	'itemView'=>'_comment_view',
	'viewData' => array('reply' => $reply),    //自己的variables
        'template'=>'{items}{pager}',	//infinite scroll.
	'emptyText'=>'',

)); ?>