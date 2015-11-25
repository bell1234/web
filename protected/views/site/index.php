<?php
/* @var $this SiteController */

$this->pageTitle = "没六儿";
$this->layout = "//layouts/nobar";
?>

<div class="align_center">
	<img src="/images/half_trans.png" style="width:140px;" />
</div>

<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 col-lg-offset-3 col-md-offset-3 col-sm-offset-3">

<h4 class="bottom15 top20 paddingleft15 paddingright15">
	
  		<div class="hori_tab signup_tab active"><a href="#" onclick="show_signup(); return false;">注册</a></div>

  		<div class="hori_tab login_tab" style="float:right;"><a href="#" onclick="show_login(); return false;">登陆</a></div>
	
</h4>

<?php
 	$this->widget('application.components.LoginPop');
?>


<img src="/images/apple_store.svg" class="col-lg-6 col-md-6 col-sm-6 col-xs-6 col-lg-offset-3 col-md-offset-3 col-sm-offset-3 col-xs-offset-3 top30" />


</div>