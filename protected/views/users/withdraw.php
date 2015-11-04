<div class="form col-md-6 col-sm-6 col-lg-6 col-lg-offset-3 col-md-offset-3 col-sm-offset-3">


<?php $this->pageTitle=Yii::app()->name . ' - 管理员取款'; ?>
<h3 class="bottom30">管理员取款</h3>

<?php if(Yii::app()->user->hasFlash('withdrawMessage')): ?>
<div class="alert alert-success">
<?php echo Yii::app()->user->getFlash('withdrawMessage'); ?>
</div>
<?php endif; ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'withdraw-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>


	<div class="row">
		<label class="control-label" for="Withdraw_alipay">余额: ¥<?php echo $admin->balance; ?>, 输入支付宝邮箱／账号取款 (请仔细核对)</label>
		<?php echo $form->textField($model,'alipay',array('placeholder'=>'支付宝邮箱/账号','class'=>'form-control')); ?>
		<?php echo $form->error($model,'alipay'); ?>
		<?php echo $form->error($model,'money'); ?>
	</div>


	<?php if($admin->balance < 20): ?>

	<div class="row buttons" style="margin-top:15px;">
		<a href="" class="disabled btn btn-warning btn-block">余额: ¥<?php echo $admin->balance; ?> - 最低取现金额为20元</a>
	</div>

	<?php else: ?>

	<div class="row buttons" style="margin-top:15px;">
		<?php echo CHtml::submitButton('取现 ¥'.$admin->balance, array('class'=>'btn btn-warning btn-block')); ?>
	</div>

	<?php endif; ?>

<?php $this->endWidget(); ?>

</div>