
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
              'validateOnSubmit'=>true,
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

<?php if(isset($_GET['ama'])){ $type = 3; ?>
	<script>
		$(function (){
			show_ama();
		});
	</script>
<?php }else if(isset($_GET['content'])){ $type = 2; ?>
	<script>
		$(function (){
			show_content();
		});
	</script>
	<style>
		.link_post{
			display:none;
		}
	</style>
<?php }else{ $type = 1; 	//ama ?>
	<script>
		$(function (){
			show_link();
		});
	</script>
	<style>
		.link_post{
			display:none;
		}
	</style>
<?php } ?>
	

	<div class="row">
		<?php echo $form->hiddenField($model,'type', array('value'=>$type)); ?>
		<ul class="nav nav-tabs top10 bottom10 bold">
  			<li role="presentation" class="link_tab active"><a href="#" onclick="show_link(); return false;">提交链接</a></li>
  			<li role="presentation" class="content_tab"><a href="#" onclick="show_content(); return false;">提交内容</a></li>
  			<li role="presentation" class="ama_tab"><a href="#" onclick="show_ama(); return false;">有问必答</a></li>
		</ul>
	</div>

	<div class="ama_alert alert alert-warning alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<p><b>有问必答(Ask Me Anything)</b></p>
		<p>例如: 我是XXX／我是一个XXX (简介自己), 有问必答！</p>
		<p class="small">* 名人/机构发起有问必答: 请先<a href="mailto:contact@meiliuer.com">联系我们验证帐号</a>或在发表后将链接分享到您的微博/微信公众号。</p>
	</div>

	<div id="link_post_field" class="row link_post form-group">
		<label class="control-label" for="post_link">要分享的链接/网址</label>
		<?php echo $form->textField($model,'link',array('class'=>'form-control','maxlength'=>500, 'placeholder'=>'请复制粘贴微信/微博/视频/图片/或者任意网站地址', 'id'=>'post_link')); ?>
		<div id="url_invalid" class="errorMessage no_show top5">请输入一个有效的地址</div>
		<div id="dup_url" class="top5 no_show small">
			该地址已被提交过，若非必要请尽量不要重复提交: 
			<div id="dup_actual_url"></div>
		</div>

		<?php echo $form->error($model,'link'); ?>
	</div>

	<div class="row form-group">
		<label class="control-label" for="Posts_name">标题</label>
		<a href="#" onclick="read_title(); return false;" class="left15 small link_post post_title_before">自动推荐标题</a>
		<span class="no_show post_title_loading left10">读取中...</span>
		<span class="no_show post_title_error left10">未找到合适的标题，请手动输入</span>
		
		<?php echo $form->textArea($model,'name',array('class'=>'form-control','maxlength'=>500, 'placeholder'=>'“一个吸引人的的标题是成功的一半” － 爱迪生', 'style'=>'resize: vertical;')); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

		<?php echo $form->hiddenField($model,'thumb_pic'); ?>

		<?php echo $form->hiddenField($model,'video_html'); ?>
		
		<input type="hidden" style="display:none;" id="temp_title" />

<?php /****** ?>
	<div class="row form-group">
		<label class="control-label" for="Posts_thumb_pic">配图（可选）</label>

		<div>
			<img id="thumb_pic" style="width:100px; height:100px; margin-top:-35px; float:left;" src='' />

	<?php $this->widget('ext.EAjaxUpload.EAjaxUpload',
	array(
		'id'=>'uploadFile',        
        	'config'=>array(
	                       'action'=>'/posts/AjaxUpload',

					      'template'=>'<div class="qq-uploader" style="margin-top:35px;"><div class="qq-upload-button btn btn-primary btn-sm" style="width:70px; float:left; margin-left:20px;">上传</div><div class="link_post" style="margin-left:10px; float:left; margin-top:6px;">或 <a href="#" onclick="read_pic(); return false;" class=" small pic_post post_pic_before left5">从网址读取配图</a><span class="no_show post_pic_loading left5">读取中...</span><span class="no_show post_pic_error left5">未找到合适的配图，建议手动上传</span></div><ul class="qq-upload-list" style="display:none;"></ul><br><br><br><div class="qq-upload-drop-area">将图片拖至这里上传</div></div>',

					      'onComplete'=>"js:function(id, fileName, responseJSON){ $('#thumb_pic').attr('src', '/uploads/posts/".Yii::app()->user->id."/' + fileName); $('#Posts_thumb_pic').val('/uploads/posts/".Yii::app()->user->id."/' + fileName);}",
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

		</div>
	</div>
<?php ***/ ?>



	<div class="row content_post form-group">
		<label class="control-label" for="Posts_description">内容</label>

<?php
$this->widget('ImperaviRedactorWidget', array(
    // You can either use it for model attribute
    'model' => $model,
    'attribute' => 'description',
    // Some options, see http://imperavi.com/redactor/docs/
    'options' => array(
        'lang' => 'zh_cn',
	'focus'=>true,
	'buttons'=>array('bold', 'italic', 'horizontalrule', 'image', 'video', 'link'),
	'placeholder' => '请输入要提交的信息 / 内容',
	'minHeight'=>100,

        //'fileUpload'=>Yii::app()->createAbsoluteUrl('posts/fileupload'),

        'imageUpload'=>Yii::app()->createAbsoluteUrl('posts/imageupload'),

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
?>
		<?php echo $form->error($model,'description'); ?>
	</div>


	<div class="row form-group category_drop" style="display:none;">
		<label class="control-label" for="Posts_category_id">请选择分类</label>
		<?php echo $form->dropDownList($model,'category_id', array(1=>'搞笑', 2=>'新闻', 3=>'科技', 30=>'杂谈'), array('class'=>'form-control','empty'=>'点击选择分类',)); ?>
		<?php echo $form->error($model,'category_id'); ?>
	</div>



	<div class="row buttons top20">
<?php if(Yii::app()->user->id): ?>
		<?php echo $form->checkBox($model,'private'); ?>
		<span class="dark_grey v_middle top5">匿名</span>		
<?php endif; ?>
		<div class="float_right">
			<?php echo CHtml::submitButton($model->isNewRecord ? '提交' : '保存', array('class'=>'btn btn-danger btn-default paddingleft30 paddingright30')); ?>
		</div>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->