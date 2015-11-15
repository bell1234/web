<?php if(Yii::app()->user->id): ?>
<div class="form left50 comments_form" style="border-top: solid 1px #efefef; padding-bottom:15px;">
	<?php CHtml::$errorCss = "has-error"; ?>
<?php 
	$form=$this->beginWidget('CActiveForm', array(
	'id'=>'comments-form',
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
		<label class="control-label" for="Comments_description">
		<?php if($model->isNewRecord): ?>
			提交评论
		<?php else: ?>
			编辑评论
		<?php endif; ?>
		</label>

<?php
/*
$this->widget('ImperaviRedactorWidget', array(
    // You can either use it for model attribute
    'model' => $model,
    'attribute' => 'description',
    // Some options, see http://imperavi.com/redactor/docs/
    'options' => array(
        'lang' => 'zh_cn',
	'focus'=>true,
	'buttons'=>array('bold', 'italic', 'horizontalrule', 'image', 'video', 'link'),
	'placeholder' => '想说点儿什么?',
	'minHeight'=>60,

        //'fileUpload'=>Yii::app()->createAbsoluteUrl('posts/commentfileupload'),

        'imageUpload'=>Yii::app()->createAbsoluteUrl('posts/commentimageupload'),

	 'imageUploadErrorCallback'=>new CJavaScriptExpression(
            'function(obj,json) { alert(json.error); }'
        ),

	// 'fileUploadErrorCallback'=>new CJavaScriptExpression(
        //   'function(obj,json) { alert(json.error); }'
        //),
    ),
 'plugins' => array(
        'video' => array(
            'js' => array('video.js',),
        ),
    ),

));
*/
?>
<?php echo $form->textArea($model,'description',array('placeholder'=>'想说点什么?','class'=>'form-control', 'style'=>'resize:vertical; height:60px;')); ?>
<?php echo $form->error($model,'description'); ?>

	</div>

	<div class="row buttons top20">
		<!--
			<?php echo $form->checkBox($model,'private'); ?>
			<span class="dark_grey v_middle top5">匿名</span>
		-->
		<div class="float_right">
			<?php if(!$model->isNewRecord): ?>
				<a class="right20" href="#" onclick="cancel_edit_comment(); return false;">取消</a>
			<?php endif; ?>

			<?php echo CHtml::submitButton($model->isNewRecord ? '评论' : '编辑', array('class'=>'btn btn-warning btn-default paddingleft30 paddingright30')); ?>
		</div>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
<?php endif; ?>
