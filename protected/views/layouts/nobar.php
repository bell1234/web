<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>

   <?php $this->renderPartial('//layouts/_header'); ?>
   
   <style>
    body{
        margin-top:10px;
        background: rgba(0, 0, 0, 0) url("/images/bgpic.png") no-repeat scroll center top / 1757px 845px;
    }
   </style>
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>
    <div id="main-page" class="container-fluid col-lg-10 col-md-10 col-sm-12 col-xs-12 col-lg-offset-1 col-md-offset-1 nopaddingleft nopaddingright" style="margin-top:20px;">
        <?php echo $content; ?>
    </div>
</body>
</html>