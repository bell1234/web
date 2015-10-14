<div class="form col-md-6 col-sm-6 col-lg-6 col-lg-offset-3 col-md-offset-3 col-sm-offset-3">
<?php $this->pageTitle=Yii::app()->name . ' - 账户设置'; ?>

<h3 class="bottom30">账户设置</h3>

<?php if(Yii::app()->user->hasFlash('settingMessage')): ?>
<div class="alert alert-success">
<?php echo Yii::app()->user->getFlash('settingMessage'); ?>
</div>
<?php endif; ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'setting-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<div class="row">
		<label class="control-label" for="Users_username">用户名/昵称</label>
		<?php echo $form->textField($model,'username',array('placeholder'=>'用户名/昵称','class'=>'form-control')); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>


	<div class="row buttons">
		<?php echo CHtml::submitButton('保存', array('class'=>'btn btn-primary btn-block')); ?>

	</div>

<?php $this->endWidget(); ?>

<hr>
<h3 class="bottom30">安全设置</h3>

<a href="/site/changepassword" class="btn btn-default btn-block btn-light">修改密码</a>



</div>