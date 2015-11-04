<?php
/* @var $this SiteController */
/* @var $model ContactForm */
/* @var $form CActiveForm */

$this->pageTitle=Yii::app()->name . ' - 联系我们';
?>

<div class="form col-md-6 col-sm-6 col-lg-6 col-lg-offset-3 col-md-offset-3 col-sm-offset-3">

<?php if(isset($_GET['feedback'])): ?>
	<h1 class="bottom30" style="font-size:28px;">建议反馈</h1>
<?php else: ?>
	<h1 class="bottom30" style="font-size:28px;">联系我们</h1>
<?php endif; ?>

<?php if(Yii::app()->user->hasFlash('contact')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('contact'); ?>
</div>

<?php else: ?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'contact-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<div class="row">
		<label class="control-label" for="ContactForm_name">姓名</label>
		<?php echo $form->textField($model,'name',array('placeholder'=>'请输入您的姓名','class'=>'form-control')); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

	<div class="row">
		<label class="control-label" for="ContactForm_email">邮箱</label>
		<?php echo $form->textField($model,'email',array('placeholder'=>'请输入您的邮箱，以便我们给您回复','class'=>'form-control')); ?>
		<?php echo $form->error($model,'email'); ?>
	</div>

	<div class="row">
		<label class="control-label" for="ContactForm_subject">标题</label>
		<?php echo $form->textField($model,'subject',array('size'=>60,'maxlength'=>128, 'placeholder'=>'请问联系没六儿有何贵干?','class'=>'form-control')); ?>
		<?php echo $form->error($model,'subject'); ?>
	</div>

	<div class="row">
		<label class="control-label" for="ContactForm_subject">内容</label>
		<?php echo $form->textArea($model,'body',array('style'=>'width:100%; height:100px; resize:vertical;','placeholder'=>'请补充细节','class'=>'form-control')); ?>
		<?php echo $form->error($model,'body'); ?>
	</div>

	<?php if(CCaptcha::checkRequirements()): ?>
	<div class="row">

		<label class="top5 control-label right10" style="margin-bottom:0px;" for="ContactForm_verifyCode">请输入验证码</label>	
		<div>
		<?php echo $form->textField($model,'verifyCode'); ?>
		<?php $this->widget('CCaptcha',array('buttonLabel'=>'获取新验证码')); ?>
		</div>

		<?php echo $form->error($model,'verifyCode'); ?>
	</div>
	<?php endif; ?>

	<div class="row buttons top10">
		<?php echo CHtml::submitButton('提交', array('class'=>'btn btn-warning btn-block')); ?>

	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<?php endif; ?>

</div>