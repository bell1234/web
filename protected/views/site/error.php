<?php
/* @var $this SiteController */
/* @var $error array */

$cs=Yii::app()->clientScript;
$cs->registerCssFile("/css/404styles.css");

$this->pageTitle=Yii::app()->name . ' - Error';
$this->breadcrumbs=array(
	'Error',
);
?>

<section id="full-404">
	<div class="row margin-top-l text-center">
		<h1 class=" margin-top-l margin-bottom-m huge-txt"><?php echo $code; ?></h1>
		<?php if($code == 500): 
			Analytics::didEncounterErrorPage($code, $message);
		?>
			<h2 class="big-txt margin-bottom-m">没六儿的服务器出错啦，请稍后再试！</h2>
		<?php endif; ?>

		<?php if($code == 403): 
			Analytics::didEncounterErrorPage($code, $message);
		?>
			<h2 class="big-txt margin-bottom-m">没有权限进行这个操作，不要做坏事哦！</h2>
		<?php endif; ?>

		<?php if($code == 404): ?>
			<h2 class="big-txt margin-bottom-m">太没六儿啦，这个界面好像不存在哦！</h2>
		<?php endif; ?>
		
	</div>
	<div class="row text-center">
		<div>
			<img id="img404" src="/images/404ufo.png">
		</div>
	</div>
	<div class="row margin-top-m text-center">
		<p class="p1">
			<a class="blue-txt underline" href="/">点击这里</a> 回到首页。网站使用中遇到问题？请<a href="mailto:contact@meiliuer.com" class="blue-txt underline">联系我们</a>。
		</p>
	</div>
</section>

<code style="display:none"> <?php echo CHtml::encode($message); ?> </code>