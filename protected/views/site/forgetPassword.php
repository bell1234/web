<div class="form col-md-6 col-sm-6 col-lg-6 col-lg-offset-3 col-md-offset-3 col-sm-offset-3">

<?php $this->pageTitle=Yii::app()->name . ' - 找回密码'; ?>

<h3 class="bottom30">找回密码</h3>

<?php if(Yii::app()->user->hasFlash('recoveryMessage')): ?>
<div class="alert alert-success">
<?php echo Yii::app()->user->getFlash('recoveryMessage'); ?>
</div>
<?php else: ?>

<div class="form">
<?php echo CHtml::beginForm(); ?>

	<div class="row bottom15">
		<?php echo CHtml::activeTextField($form,'login_or_email',array('placeholder'=>'请输入注册用的邮箱','class'=>'form-control')) ?>
		<?php echo CHtml::error($form,'login_or_email'); ?>
	</div>
	
	<div class="row submit1">
                <?php echo CHtml::submitButton('找回密码', array('class'=>'btn btn-primary btn-block')); ?>
	</div>

<?php echo CHtml::endForm(); ?>
</div><!-- form -->
<?php endif; ?>

</div>