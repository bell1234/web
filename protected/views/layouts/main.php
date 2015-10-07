<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/font-awesome/css/font-awesome.min.css">
    <!--<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/css/bootstrap-theme.min.css">-->
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/universal.css">
    
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/js/jquery-1.11.3.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/js/jquery-migrate-1.2.1.min.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/universal.js"></script>
    

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>

<nav class="navbar navbar-default navbar-fixed-top bottom_redborder">
  <div class="container-fluid col-lg-10 col-md-10 col-lg-offset-1 col-md-offset-1">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-top-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Brand</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-top-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="active"><a href="#">热点</a></li>
        <li><a href="#">类别1</a></li>
        <li><a href="#">类别2</a></li>
        <li><a href="#">类别3</a></li>
        <li><a href="#">类别4</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
      	 <?php if(Yii::app()->user->isGuest): ?>
        <li><a href="/site/login">10秒注册/登陆</a></li><!--make popup-->
        <?php else: 
			   $user = Users::model()->findByPk(Yii::app()->user->id);
		 ?>
        <li><a href="/submit"><i class="fa fa-pencil-square-o fa"></i> 发布</a></li><!--make popup-->
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="<?php echo $user->avatar; ?>" /><?php echo (strlen($user->username) > 12) ? mb_substr($user->username, 0, 10,'utf-8') . '..' : $user->username; ?> <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#">我的账户</a></li>
            <li><a href="#">安全设定</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/site/logout">退出此账号</a></li>
          </ul>
        </li>
        <?php endif; ?>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

	<div id="main-page" class="container-fluid col-lg-10 col-md-10 col-sm-12 col-lg-offset-1 col-md-offset-1 nopaddingleft nopaddingright top60">
		<?php echo $content; ?>
	</div>

	<div class="clear"></div>
    
	<div id="footer" class="container no_show">
		Copyright &copy; <?php echo date('Y'); ?> by My Company.<br/>
		All Rights Reserved.<br/>
	</div><!-- footer -->

</body>
</html>

