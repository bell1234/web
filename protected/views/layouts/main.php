<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

  <link rel = "shortcut icon" type = "image/x-icactionon" href = "/images/shaka.png" />
  <meta property="og:image" content="/images/shaka.png" />
	
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/font-awesome/css/font-awesome.min.css">
    <!--<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/css/bootstrap-theme.min.css">-->
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/universal_v5.css">
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/details.css">
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/notification_v1.css">
    
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/js/jquery-1.11.3.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/js/jquery-migrate-1.2.1.min.js"></script>
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/timeago.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/fastclick.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/universal_v2.js"></script>
    
    <script type="text/javascript">
!function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","page","once","off","on"];analytics.factory=function(t){return function(){var e=Array.prototype.slice.call(arguments);e.unshift(t);analytics.push(e);return analytics}};for(var t=0;t<analytics.methods.length;t++){var e=analytics.methods[t];analytics[e]=analytics.factory(e)}analytics.load=function(t){var e=document.createElement("script");e.type="text/javascript";e.async=!0;e.src=("https:"===document.location.protocol?"https://":"http://")+"cdn.segment.com/analytics.js/v1/"+t+"/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(e,n)};analytics.SNIPPET_VERSION="3.1.0";
analytics.load("kEGvVw6uhrd6l8WXPJaVeIzMlCqBG24O");
analytics.page()
}}();

var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?c3281480f32d56f1bc53d5f175bbe6d6";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>

   <script type="text/javascript">
        var ShareId = "";
        $(function () {
            $(".bdsharebuttonbox a").mouseover(function () {
                ShareId = $(this).attr("data-id");
                ShareTitle = $(this).attr("data-title");
                SharePic = $(this).attr("data-img");
            });
        });
        function SetShareUrl(cmd, config) {            
            if (ShareId) {
                config.bdUrl = "http://meiliuer.com/posts/" + ShareId; 
                config.bdPic = "http://meiliuer.com/" + SharePic;    
                config.bdText = ShareTitle + " - 没六儿";    
            }
            return config;
        }

        window._bd_share_config = {
            "common": {
                onBeforeClick: SetShareUrl, "bdSnsKey": {}, "bdText": ""
                , "bdMini": "2", "bdMiniList": false, "bdPic": "", "bdStyle": "0", "bdSize": "16"
            }, "share": {}
        };

        //插件的JS加载部分
        with (document) 0[(getElementsByTagName('head')[0] || body)
            .appendChild(createElement('script'))
            .src = 'http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='
            + ~(-new Date() / 36e5)];
    </script>

    
    <?php if(Yii::app()->user->id): 
			$user = Users::model()->findByPk(Yii::app()->user->id);
			$admin = Admins::model()->findByPk(Yii::app()->user->id);

      $notifications=new CActiveDataProvider('Notification', array(
        'criteria'=>array(
            'condition'=>'receiver = :user_id',
            'params'=> array(':user_id'=>Yii::app()->user->id),
            'order'=>'create_time DESC',
            'offset' => 0,
            'limit' => 10,
        ),
        'pagination' => array('pageSize' =>10),
        'totalItemCount' => 10,
      ));
      $notificationsCount = Notification::model()->count('`read`=0 AND receiver=:user_id', array(':user_id'=>Yii::app()->user->id));
      if($notificationsCount > 0){
        $redplum = "redplum_c";
        $reddisplay = "block";
      }else{
        $redplum = "redplum";
        $reddisplay = "none";
      }
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

<!--background-color:#fee123; border-bottom: solid 2px #111111;-->
<nav class="navbar navbar-fixed-top navbar-default bottom_blackborder">
  <div class="container-fluid col-lg-10 col-md-10 col-lg-offset-1 col-md-offset-1">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">

	<a class="navbar-brand" href="/">
		<span class="logo_text">没六儿</span>
		<img class="logo_img" alt="Brand" src="/images/shaka.png" /> 
	</a>

	<?php if(Yii::app()->user->isGuest || $user->auto): ?>
		<a id="nav_col_signup" onclick="signup(); return false;" class="navbar-toggle collapsed">
        		<span class="sr-only">New post</span>
			7秒注册/登录
		</a>
	<?php else: ?>
     		<a id="small_avatar_mobile" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-top-navbar-collapse-1" aria-expanded="false">
        		<span class="sr-only">Toggle navigation</span>
			<img id="small_avatar_nav" class="img-circle" src="<?php echo $user->avatar; ?>" />
			<span style="line-height:48px;">
				<?php echo (strlen($user->username) > 12) ? mb_substr($user->username, 0, 10,'utf-8') . '..' : $user->username; ?> 
			</span>
      		</a>
	<?php endif; ?>



	<?php if (strpos($_SERVER['REQUEST_URI'], "submit") !== false): ?>
		<a id="nav_col_post" href="/submit" class="navbar-toggle collapsed">
			提交
      		</a>
	<?php else: ?>
             <?php if(Yii::app()->user->isGuest): ?>
                    <a id="nav_col_post" onclick="signup(); return false;" href="#" class="navbar-toggle collapsed">
            提交
                    </a>
        <?php else: ?>

    <a id="nav_col_noti" href="/site/notifications" class="navbar-toggle collapsed">
       <i style="font-size:17px;" id="globeIcon" class="fa fa-inbox"></i> <span class="noti_bubble" style="display:<?php echo $reddisplay;?>;"><?php echo $notificationsCount;?></span>
    </a>

		<a id="nav_col_post" onclick="post_new();  setTimeout(show_link, 3);  return false;" href="#" class="navbar-toggle collapsed">
			提交
		</a>
    <?php endif; ?>
	<?php endif; ?>
    
    </div>

    <div class="collapse navbar-collapse"  aria-expanded="false" id="bs-top-navbar-collapse-1">
      <ul class="nav navbar-nav">
           	<li><a href="/users/view/<?php echo Yii::app()->user->id; ?>">我的提交</a></li>
            <li><a href="#" onclick="invite(); return false;">邀请朋友</a></li>
		<?php if(isset($admin) && $admin): ?>
           		<li><a href="/users/withdraw">管理员取款</a></li>
		<?php endif; ?>
           	<li><a href="/users/setting">账户设置</a></li>
           	<li><a href="/site/contact">联系我们</a></li>
           	<li role="separator" class="divider"></li>
           	<li><a href="/site/logout">退出此账号</a></li>
      </ul>  
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-top-navbar-collapse">
    
      <!--
      <ul class="nav navbar-nav" style="margin-left:80px;">
        <?php $host = $_SERVER['REQUEST_URI']; ?>
        <li <?php if($host == "/" || $host == "" || $host == "/site/index" || $host == "/posts/index"): ?>class="active"<?php endif; ?>><a href="/">热点</a></li>
        <li <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 1): ?>class="active"<?php endif; ?>><a href="/funny">娱乐</a></li>
        <li <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 2): ?>class="active"<?php endif; ?>><a href="/news">新闻</a></li>
        <li <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 3): ?>class="active"<?php endif; ?>><a href="/tech">科技</a></li>
        <li <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 30): ?>class="active"<?php endif; ?>><a href="/other">杂谈</a></li>
        <li <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 4): ?>class="active"<?php endif; ?>><a href="/ama">有问必答</a></li>
      </ul>
      -->
      
      <ul class="nav navbar-nav navbar-right">

         <?php if(Yii::app()->user->isGuest): ?>
            <li><a onclick="signup(); return false;" href="#"><span class="bold" style="font-size:16px;"><i class="fa fa-pencil-square-o"></i></span> 提交</a></li>        
     <?php else: ?>



		<?php if (strpos($_SERVER['REQUEST_URI'], "submit") !== false): ?>
        		<li><a href="/submit"><span class="bold" style="font-size:16px;"><i class="fa fa-pencil-square-o fa"></i></span> 提交</a></li>
        	<?php else: ?>
       			<li><a onclick="post_new(); return false;" href="#"><span class="bold" style="font-size:16px;"><i class="fa fa-pencil-square-o"></i></span> 提交</a></li>        
        	<?php endif; ?>

        <?php endif; ?>

      	 <?php if(Yii::app()->user->isGuest || $user->auto): ?>
        	<li><a href="#" onclick="signup(); return false;">7秒注册/登陆</a></li>
        <?php else: ?>


          <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" onClick="hidered();return false;" style="padding-top:15px;">
                    <i style="font-size:17px;" id="globeIcon" class="fa fa-inbox"></i>
                    <span class="noti_bubble" id="n2" style="display:<?php echo $reddisplay;?>;"><?php echo $notificationsCount;?></span>
                    <ul class="dropdown-menu" style="width:350px; padding-bottom:0px;">
                        <li class="dropdown-header bold" style="padding-left:5px; color:#333; border-bottom: solid 1px #bfbfbf; text-decoration:none;">消息中心</li>
                        <div class="menu-inside" >
                            <div class="scroll-here">
                                <div id="redplum-dropdown">
                                    <?php
                                    $this->widget('zii.widgets.CListView', array(
                                        'dataProvider'=>$notifications,
                                        'id'=>'red_plum_noti_list',
                                        'itemView'=>'application.views.site._notification',
                                        'template'=>'{items}{pager}',
                                        'emptyText'=>'<p><center>目前还没有新消息</center></p>',
                                    ));
                                    ?>
                                </div>
                            </div>
                            <!--
                              <div class="dropdown-bottom" style="border-radius:0;height:35px;width:auto;">
                                  <center>
                                     <a href="#"><p>查看全部</p>
                                  </center>
                              </div>
                            -->
                        </div>
                    </ul>
                </a>
            </li>

  
       		<li class="dropdown">
        	<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="padding-top:0px; padding-bottom:0px;">
			<img id="small_avatar_nav" class="img-circle" src="<?php echo $user->avatar; ?>" />
			<span style="line-height:48px;">
				<?php echo (strlen($user->username) > 12) ? mb_substr($user->username, 0, 10,'utf-8') . '..' : $user->username; ?> 
			</span>
			<span class="caret"></span>
		</a>
          	<ul class="dropdown-menu">
           	<li><a href="/users/view/<?php echo Yii::app()->user->id; ?>">我的提交</a></li>
            <li><a href="#" onclick="invite(); return false;">邀请朋友</a></li>
		<?php if(isset($admin) && $admin): ?>
           		<li><a href="/users/withdraw">管理员取款</a></li>
		<?php endif; ?>
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



<!--
<div id="option_bar_mobile" class="btn-group visible-xs btn-block" role="group">
  <a href="/" class="btn btn-default <?php if($host == "/" || $host == "" || $host == "/site/index" || $host == "/posts/index"): ?>active<?php endif; ?>" style="border-left:none;">热点</a>
  <a href="/funny" class="btn btn-default <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 1): ?>active<?php endif; ?>" >娱乐</a>
  <a href="/news" class="btn btn-default <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 2): ?>active<?php endif; ?>" >新闻</a>
  <a href="/tech" class="btn btn-default <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 3): ?>active<?php endif; ?>" >科技</a>
  <a href="/other" class="btn btn-default <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 30): ?>active<?php endif; ?>" >杂谈</a>
  <a href="/ama" class="btn btn-default <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 4): ?>active<?php endif; ?>" style="width:22.5%;">有问必答</a>
</div>

<div id="option_bar_mobile" class="btn-group visible-xs btn-block" role="group">
  <a href="/" class="btn btn-default <?php if($host == "/" || $host == "" || $host == "/site/index" || $host == "/posts/index"): ?>active<?php endif; ?>" style="border-left:none; width:20%;">热点</a>
  <a href="/funny" class="btn btn-default <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 1): ?>active<?php endif; ?>" style="width:20%;">娱乐</a>
  <a href="/news" class="btn btn-default <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 2): ?>active<?php endif; ?>" style="width:20%;">新闻</a>
  <a href="/tech" class="btn btn-default <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 3): ?>active<?php endif; ?>" style="width:20%;">科技</a>
  <a href="/other" class="btn btn-default <?php if(isset($_GET['category_id']) && $_GET['category_id'] == 30): ?>active<?php endif; ?>" style="width:20%;" >杂谈</a>
</div>
-->

	<div id="main-page" class="container-fluid col-lg-10 col-md-10 col-sm-12 col-lg-offset-1 col-md-offset-1 nopaddingleft nopaddingright">
		<?php echo $content; ?>
	</div>

	<div class="clear"></div>
    
	<div id="footer" class="container no_show">
		Copyright &copy; <?php echo date('Y'); ?> by Meiliuer<br/>
	</div><!-- footer -->


<!-- modals starts, move everything out for partial --> 

	<?php if(Yii::app()->user->isGuest || $user->auto): ?>
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

  <?php else: ?>

  <div class="modal fade " id="invite_friend" tabindex="-1" role="dialog" aria-labelledby="invite_friend_modal">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">邀请好友</h4>
      </div>
      <div class="modal-body">
            <div class="row">
                     <p>没六儿目前仅限邀请注册</p>
                    <p>邀请朋友加入请使用邀请码:</p>
                    <p><b style="font-size:16px;"><u>ML999</u></b></p>
            </div>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

  <?php endif; ?>

		<!-- Modal -->
		<div class="modal fade" id="post_popup" tabindex="-1" role="dialog" aria-labelledby="post_popup_modal">
  			<div class="modal-dialog" role="document">
   				<div class="modal-content">
     		 			<div class="modal-body nopaddingtop" style="margin-top:-8px;">
						<div class="row">
       							<button class="body-close close type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      		  					<?php $this->widget('application.components.PostPop');?>
						</div>
     		 			</div>
    				</div>
  			</div>
		</div> 

</body>
</html>

