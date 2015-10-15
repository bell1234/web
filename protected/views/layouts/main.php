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
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/timeago.js"></script>
    
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/universal.js"></script>
    
    <script type="text/javascript">
!function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","page","once","off","on"];analytics.factory=function(t){return function(){var e=Array.prototype.slice.call(arguments);e.unshift(t);analytics.push(e);return analytics}};for(var t=0;t<analytics.methods.length;t++){var e=analytics.methods[t];analytics[e]=analytics.factory(e)}analytics.load=function(t){var e=document.createElement("script");e.type="text/javascript";e.async=!0;e.src=("https:"===document.location.protocol?"https://":"http://")+"cdn.segment.com/analytics.js/v1/"+t+"/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(e,n)};analytics.SNIPPET_VERSION="3.1.0";
analytics.load("kEGvVw6uhrd6l8WXPJaVeIzMlCqBG24O");
analytics.page()
}}();
	</script>
    
    <?php if(Yii::app()->user->id): 
			$user = Users::model()->findByPk(Yii::app()->user->id);
	?>
            <script>
            $('document').ready(function() {
                analytics.identify('<?php echo Yii::app()->user->id ?>', {
                  email: '<?php echo $user->email;?>',
                  username: '<?php echo $user->username;?>',
                  create_time: '<?php echo $user->create_time; ?>',
                  logins: <?php echo $user->logins; ?>,
                  avatar: '<?php echo $user->avatar; ?>',
                  address: {
                    city: '<?php echo $user->city; ?>',
                    country: '<?php echo $user->country; ?>' 
                  }
                });
            });
        </script>
    <?php endif; ?>

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
      <a id="nav_col_post" <?php if(Yii::app()->user->id): ?>target="_blank"<?php endif; ?> href="/submit" class="navbar-toggle collapsed">
        <span class="sr-only">New post</span>
        <i class="fa fa-pencil-square-o"></i>
      </a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-top-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <?php $host = $_SERVER['REQUEST_URI']; ?>
        <li <?php if($host == "/" || $host == "" || $host == "/site/index" || $host == "/posts/index"): ?>class="active"<?php endif; ?>><a href="/">热点</a></li>
        <li <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 1): ?>class="active"<?php endif; ?>><a href="/funny">搞笑</a></li>
        <li <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 2): ?>class="active"<?php endif; ?>><a href="/news">新闻</a></li>
        <li <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 3): ?>class="active"<?php endif; ?>><a href="/tech">科技</a></li>
        <li <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 30): ?>class="active"<?php endif; ?>><a href="/other">杂谈</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
      	 <?php if(Yii::app()->user->isGuest): ?>
        <li><a href="#" onclick="signup(); return false;">10秒注册/登陆</a></li>
        <?php else: ?>
        <li><a href="/submit"><span class="bold" style="font-size:16px;"><i class="fa fa-pencil-square-o fa"></i></span> 分享</a></li><!--make popup-->
        <li class="dropdown">

          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="padding-top:0px; padding-bottom:0px;">
	<img id="small_avatar_nav" class="img-circle" src="<?php echo $user->avatar; ?>" />
	<span style="line-height:48px;">
		<?php echo (strlen($user->username) > 12) ? mb_substr($user->username, 0, 10,'utf-8') . '..' : $user->username; ?> 
	</span>
	<span class="caret"></span>
	</a>
          <ul class="dropdown-menu">
            <li><a href="/users/view/<?php echo Yii::app()->user->id; ?>">我的分享</a></li>
            <li><a href="/users/setting">账户设置</a></li>
            <li><a href="/site/contact">联系我们</a></li>
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
		Copyright &copy; <?php echo date('Y'); ?> by Meiliuer<br/>
	</div><!-- footer -->

	<?php if(Yii::app()->user->isGuest): ?>
		<!-- Modal -->
		<div class="modal fade" id="signup_or_login" tabindex="-1" role="dialog" aria-labelledby="signup_or_login_modal">
  			<div class="modal-dialog" role="document">
   				<div class="modal-content">
     		 			<div class="modal-body nopaddingtop paddingbottom20" style="margin-top:-8px;">
						<div class="row">
       							<button class="body-close close type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      		  					<?php $this->widget('application.components.LoginPop');?>
						</div>
     		 			</div>
    				</div>
  			</div>
		</div>
	<?php endif; ?>

</body>
</html>

