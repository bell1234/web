
<div class="form">
	<?php CHtml::$errorCss = "has-error"; ?>
<?php 

	$form=$this->beginWidget('CActiveForm', array(
	'id'=>'posts-form',
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

<?php if(isset($_GET['content'])){ $type = 2; ?>
	<script>
		$(function (){
			show_content();
		});
	</script>
<?php }else{ $type = 1; ?>
	<script>
		$(function (){
			show_link();
		});
	</script>
<?php } ?>
	

	<div class="row">
		<?php echo $form->hiddenField($model,'type', array('value'=>$type)); ?>
		<ul class="nav nav-tabs top10 bottom10">
  			<li role="presentation" class="link_tab active"><a href="#" onclick="show_link(); return false;">发布链接</a></li>
  			<li role="presentation" class="content_tab"><a href="#" onclick="show_content(); return false;">发布内容</a></li>
		</ul>
	</div>

	<div id="link_post_field" class="row link_post form-group">
		<label class="control-label" for="post_link">要分享的链接/网址</label>
		<?php echo $form->textField($model,'link',array('class'=>'form-control','maxlength'=>500, 'placeholder'=>'请复制粘贴您想分享的微信/微博/视频/图片/或者任意网站地址', 'id'=>'post_link')); ?>
		<div id="url_invalid" class="errorMessage no_show top5">请输入一个有效的地址</div>
		<div id="dup_url" class="top5 no_show small">
			该地址已被提交过，若非必要请尽量不要重复提交: 
			<div id="dup_actual_url"></div>
		</div>

		<?php echo $form->error($model,'link'); ?>
	</div>

	<div class="row form-group">
		<label class="control-label" for="Posts_name">标题</label>
		<a href="#" onclick="read_title(); return false;" class="left15 small link_post post_title_before">从网址读取标题</a>
		<span class="no_show post_title_loading left10">读取中...</span>
		<span class="no_show post_title_error left10">未找到合适的标题，请手动输入</span>
		
		<?php echo $form->textArea($model,'name',array('class'=>'form-control','maxlength'=>500, 'placeholder'=>'“一个吸引人的的标题是成功的一半” － 爱迪生', 'style'=>'resize: vertical;')); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

	<div class="row content_post form-group">
		<label class="control-label" for="Posts_description">内容</label>
		<?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50, 'class'=>'form-control', 'placeholder'=>'要分享的信息 / 内容', 'style'=>'resize: vertical;')); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>

	<div class="row form-group">
		<label class="control-label" for="Posts_category_id">请选择分类</label>
		<?php echo $form->dropDownList($model,'category_id', array(1=>'例子'), array('class'=>'form-control','empty'=>'点击选择分类',)); ?>
		<?php echo $form->error($model,'category_id'); ?>
	</div>


	<div class="row buttons top20">
		<?php echo $form->checkBox($model,'private'); ?>
		<span class="dark_grey v_middle top5">匿名</span>
		<div class="float_right">
			<a class="right25" href="#" onclick="dismiss_post_popup(); return false;">取消</a>
			<?php echo CHtml::submitButton($model->isNewRecord ? '发布' : '保存', array('class'=>'btn btn-danger btn-default paddingleft30 paddingright30')); ?>
		</div>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->