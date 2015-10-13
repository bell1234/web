<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'id'=>'commentlist',
        'pager' => array(
            'class' => 'ext.infiniteScroll.IasPager',
            'rowSelector'=>'.question_view_only',
            'listViewId' => 'questionslistview',
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
        'template'=>'{items}{pager}',	//infinite scroll.
	'emptyText'=>'',

)); ?>