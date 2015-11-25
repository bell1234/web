	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

  <link rel = "shortcut icon" type = "image/x-icactionon" href = "/images/shaka.png" />
  <meta property="og:image" content="/images/shaka.png" />
	
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/font-awesome/css/font-awesome.min.css">
    <!--<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/css/bootstrap-theme.min.css">-->
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/universal_v7.css">
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/details.css">
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/notification_v1.css">
    
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/js/jquery-1.11.3.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/js/jquery-migrate-1.2.1.min.js"></script>
	  <script src="<?php echo Yii::app()->request->baseUrl; ?>/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/timeago.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/fastclick.js"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/universal_v4.js"></script>
    
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
