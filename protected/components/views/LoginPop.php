
<div class="form col-md-4 col-sm-4 col-md-offset-2 col-sm-offset-2">

<h1>注册账号</h1>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'signup-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<div class="row">
		<?php echo $form->textField($user,'username',array('placeholder'=>'用户名/昵称')); ?>
		<?php echo $form->error($user,'username'); ?>
	</div>

	<div class="row">
		<?php echo $form->textField($user,'email',array('placeholder'=>'邮箱')); ?>
		<?php echo $form->error($user,'email'); ?>
	</div>

	<div class="row">
		<?php echo $form->passwordField($user,'password',array('placeholder'=>'密码(不少于6位)')); ?>
		<?php echo $form->error($user,'password'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('注册', array('class'=>'btn btn-primary')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->




<div class="form col-md-4 col-sm-4">

<h1>登陆</h1>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>


	<div class="row">
		<?php echo $form->textField($model,'username',array('placeholder'=>'邮箱')); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

	<div class="row">
		<?php echo $form->passwordField($model,'password',array('placeholder'=>'密码')); ?>
		<?php echo $form->error($model,'password'); ?>
	</div>


	<div class="row buttons">
		<?php echo CHtml::submitButton('登陆', array('class'=>'btn btn-primary')); ?>
	</div>

	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe', array('checked'=>'checked')); ?>
		记住我
		<?php echo $form->error($model,'rememberMe'); ?>

		<a href="/site/forgetpassword">找回密码</a>
	</div>

	<div class="row">
		社交账号登陆
		微信－微博－QQ
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

