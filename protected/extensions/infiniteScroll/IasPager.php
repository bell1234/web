<?php

class IasPager extends CLinkPager {

    public $listViewId;
    public $rowSelector = '.row';
    public $itemsSelector = ' > .items';
    public $nextSelector = '.next:not(.disabled):not(.hidden) a';
    public $pagerSelector = '.pager';
    private $baseUrl;
    public $options = array();
    public $linkOptions = array();

    public function init() {

        parent::init();

        $assets = dirname(__FILE__) . '/assets';
        $this->baseUrl = Yii::app()->assetManager->publish($assets);

        $cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerCSSFile($this->baseUrl . '/css/jquery.ias.css');

        $cs->registerScriptFile($this->baseUrl . '/js/jquery-ias.min.js', CClientScript::POS_END);
    }

    public function run() {

        $js = "var ias = jQuery.ias(" .
                CJavaScript::encode(
                        CMap::mergeArray($this->options, array(
                            'container' => '#' . $this->listViewId . '' . $this->itemsSelector,
                            'item' => $this->rowSelector,
                            'pagination' => '#' . $this->listViewId . ' ' . $this->pagerSelector,
                            'next' => '#' . $this->listViewId . ' ' . $this->nextSelector,
                        ))) . ");";


        $cs = Yii::app()->clientScript;
        $cs->registerScript(__CLASS__ . $this->id, $js, CClientScript::POS_READY);


$script = <<<EOD
	ias.extension(new IASTriggerExtension({
		offset: 20,
		html: '<div class="ias-trigger ias-trigger-prev" style="text-align: center; cursor: pointer;"><a class="btn btn-sm btn-default">读取更多分享</a></div>',
	}));
	ias.extension(new IASPagingExtension());
	ias.extension(new IASHistoryExtension());
	ias.extension(new IASSpinnerExtension({
    		src: '/images/loadingb.gif', // optionally
	}));
	ias.on('rendered', function(items) {
 		$(".timeago").timeago();
		window._bd_share_main.init();
               $(".bdsharebuttonbox a").mouseover(function () {
                   ShareId = $(this).attr("data-id");
                   ShareTitle = $(this).attr("data-title");
                   SharePic = $(this).attr("data-img");
               });
	})
EOD;

Yii::app()->clientScript->registerScript('iashistory', $script, CClientScript::POS_READY);

        $buttons = $this->createPageButtons();

        echo $this->header; // if any
        echo CHtml::tag('ul', $this->htmlOptions, implode("\n", $buttons));
        echo $this->footer;  // if any
    }

    protected function createPageButton($label, $page, $class, $hidden, $selected) {
        if ($hidden || $selected)
            $class .= ' ' . ($hidden ? 'disabled' : 'active');

        return CHtml::tag('li', array('class' => $class), CHtml::link($label, $this->createPageUrl($page), $this->linkOptions));
    }

}