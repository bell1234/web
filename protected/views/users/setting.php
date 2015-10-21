<div class="form col-md-6 col-sm-6 col-lg-6 col-lg-offset-3 col-md-offset-3 col-sm-offset-3">

<?php if($model->auto): ?>
	<?php $this->pageTitle=Yii::app()->name . ' - 账户注册'; ?>
	<h3 class="bottom30">账户注册</h3>
<?php else: ?>
	<?php $this->pageTitle=Yii::app()->name . ' - 账户设置'; ?>
	<h3 class="bottom30">账户设置</h3>
<?php endif; ?>

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


<?php if(!$model->auto): ?>

	<div class="row bottom30">
		<label class="control-label" for="Users_avatar">头像</label>
		<div id="avatar_holder">
			<img id="avatar_now" class="img-circle" src="<?php echo $model->avatar; ?>" />
		</div>
		<?php $this->widget('ext.EAjaxUpload.EAjaxUpload', array(
			'id'=>'uploadFile',
	        	'config'=>array(
	                       'action'=>'/users/AjaxUpload',
				'template'=>'<div class="qq-uploader"><div class="qq-upload-drop-area">将图片拖至这里上传</div><div class="qq-upload-button btn btn-primary btn-block">上传新头像</div><ul class="qq-upload-list" style="display:none;"></ul></div>',
				'onComplete'=>"js:function(id, fileName, responseJSON){ $('#avatar_now').attr('src', '/uploads/avatar/".Yii::app()->user->id."/' + fileName);}",
	                        'allowedExtensions'=>array('jpg','png','gif','jpeg','tiff','tif','bmp'),
	                        'sizeLimit'=>5*1024*1024,// maximum file size in bytes                    
               			 'minSizeLimit'=>1,// minimum file size in bytes
	                        'messages'=>array(
               
	                                      	'typeError'=>'文件格式不符, 请上传以下格式的文件: {extensions}',
               
	                                        'sizeError'=>'文件过大, 请上传{sizeLimit}以下的文件',        
               
	                                         ),
               					'showMessage'=>"js:function(message){ alert(message); }"
	                                       )                            
	                                                    
)); ?>


		<?php echo $form->error($model,'avatar'); ?>
	</div>

<?php endif; ?>

	<div class="row">
		<label class="control-label" for="Users_username">用户名/昵称</label>
		<?php echo $form->textField($model,'username',array('placeholder'=>'用户名/昵称','class'=>'form-control')); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

<?php if($model->auto): ?>
	<div class="row">
		<label class="control-label" for="Users_password">密码</label>
		<?php echo $form->passwordField($model,'password',array('placeholder'=>'密码(至少6位)','class'=>'form-control', 'value'=>'')); ?>
		<?php echo $form->error($model,'password'); ?>
	</div>
<?php endif; ?>

	<div class="row">
		<label class="control-label" for="Users_email">邮箱(选填)</label>
		<?php echo $form->textField($model,'email',array('placeholder'=>'供找回密码','class'=>'form-control')); ?>
		<?php echo $form->error($model,'email'); ?>
	</div>


	<div class="row buttons" style="margin-top:15px;">
		<?php echo CHtml::submitButton('保存信息', array('class'=>'btn btn-primary btn-block')); ?>
	</div>

<?php $this->endWidget(); ?>

<?php if(!$model->auto): ?>
<hr>
<h3 class="bottom30">安全设置</h3>

<a href="/site/changepassword" class="btn btn-default btn-block btn-light">修改密码</a>
<?php endif; ?>

</div>