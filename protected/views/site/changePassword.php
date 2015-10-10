<div class="form col-md-6 col-sm-6 col-lg-6 col-lg-offset-3 col-md-offset-3 col-sm-offset-3">

<?php $this->pageTitle=Yii::app()->name . ' - 修改密码'; ?>

<h3 class="bottom30">修改密码</h3>

<?php if(Yii::app()->user->hasFlash('recoveryMessage')): ?>
<div class="alert alert-success">
<?php echo Yii::app()->user->getFlash('recoveryMessage'); ?>
</div>
<?php endif; ?>

<div class="form">
<?php echo CHtml::beginForm(); ?>


<?php if(Yii::app()->controller->action->id != "forgetpassword"): ?>
	<div class="row">
		<?php echo CHtml::activePasswordField($form,'oldPassword',array('placeholder'=>'请输入旧密码(现有密码)','class'=>'form-control')); ?>
		<?php echo CHtml::error($form,'oldPassword'); ?>
	</div>
<?php endif; ?>


	<div class="row">
		<?php echo CHtml::activePasswordField($form,'password',array('placeholder'=>'请输入新密码(不少于6位)','class'=>'form-control')); ?>
		<?php echo CHtml::error($form,'password'); ?>
	</div>
	
	<div class="row bottom15">
		<?php echo CHtml::activePasswordField($form,'verifyPassword',array('placeholder'=>'请再输入一次新密码','class'=>'form-control')); ?>
		<?php echo CHtml::error($form,'verifyPassword'); ?>
	</div>

	
	<div class="row submit1">
                <?php echo CHtml::submitButton('修改密码', array('class'=>'btn btn-primary btn-block')); ?>
	</div>

<?php echo CHtml::endForm(); ?>
</div><!-- form -->

</div>



