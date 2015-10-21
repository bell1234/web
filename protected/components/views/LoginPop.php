
<div class="form col-lg-6 col-md-6 col-sm-6 col-xs-12">

<h3 class="bottom15">注册账号</h3>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'signup-form',
	'enableAjaxValidation'=>false,
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
	'action'=>'/site/setting',	//我们加了这一行 因为每个人都已经是用户了...我们存下每一个访问
)); ?>

	<div class="row">
		<?php echo $form->textField($user,'username',array('placeholder'=>'用户名/昵称','class'=>'form-control')); ?>
		<?php echo $form->error($user,'username'); ?>
	</div>

	<div class="row">
		<?php echo $form->textField($user,'email',array('placeholder'=>'邮箱(选填)','class'=>'form-control')); ?>
		<?php echo $form->error($user,'email'); ?>
	</div>

	<div class="row">
		<?php echo $form->passwordField($user,'password',array('placeholder'=>'密码(不少于6位)','class'=>'form-control')); ?>
		<?php echo $form->error($user,'password'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('注册', array('class'=>'btn btn-danger btn-block')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->




<div class="form col-lg-6 col-md-6 col-sm-6 col-xs-12">

<h3 class="bottom15">登录没六儿</h3>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableAjaxValidation'=> false,
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>
	<div class="row">
		<?php echo $form->textField($model,'username',array('placeholder'=>'用户名/邮箱','class'=>'form-control')); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

	<div class="row">
		<?php echo $form->passwordField($model,'password',array('placeholder'=>'密码','class'=>'form-control')); ?>
		<?php echo $form->error($model,'password'); ?>
	</div>


	<div class="row buttons">
		<?php echo CHtml::SubmitButton('登陆', array('class'=>'btn btn-danger btn-block')); ?>
	</div>


	<div class="row">

		<?php echo $form->checkBox($model,'rememberMe', array('checked'=>'checked')); ?>
		记住我
		<?php echo $form->error($model,'rememberMe'); ?>

		<a style="float:right;" href="/site/forgetpassword">找回密码</a>

	</div>

	<div class="row">
		社交账号登陆
		微信－微博－QQ
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

