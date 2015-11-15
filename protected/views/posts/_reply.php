
<div id="reply_form_<?php echo $comment->id;?>" class="form left50 reply_form" style="padding-bottom:15px; display:none;">
	<?php CHtml::$errorCss = "has-error"; ?>
<?php 

	$form=$this->beginWidget('CActiveForm', array(
	'id'=>'reply-form'.$model->id,
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'enableClientValidation'=>true,
          'clientOptions'=>array(
              'validateOnSubmit'=>false,
              'afterValidate' => 'js:function(form, data, hasError) { 
                  if(hasError) {
                      for(var i in data) $("#"+i).parent(".form-group").addClass("has-error");
                      return false;
                  }
                  else {
                      form.children().removeClass("has-error");
                      return true;
                  }
              }',
              'afterValidateAttribute' => 'js:function(form, attribute, data, hasError) {
                  if(hasError) $("#"+attribute.id).parent(".form-group").addClass("has-error");
                      else $("#"+attribute.id).parent(".form-group").removeClass("has-error"); 
              }'
	)
)); 
?>

	<div class="row form-group">
		<label class="control-label" for="Reply_description">
			回复评论
		</label>

		<?php echo $form->hiddenField($model,'comment_id',array('value'=>$comment->id)); ?>
		<?php echo $form->hiddenField($model,'receiver', array('class'=>'reply-receiver-field')); ?>
	
<?php echo $form->textArea($model,'description',array('placeholder'=>'回复评论','class'=>'form-control reply-field', 'style'=>'resize:vertical; height:33px;')); ?>
<?php echo $form->error($model,'description'); ?>

	</div>

	<div class="row buttons top20">
		<div class="float_right">
			<?php if($model->isNewRecord): ?>
				<a class="right20" href="#" onclick="hide_reply_comment_<?php echo $comment->id;?>(); return false;">取消</a>
			<?php endif; ?>

			<?php echo CHtml::submitButton('回复', array('class'=>'btn btn-warning btn-default paddingleft30 paddingright30')); ?>
		</div>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
