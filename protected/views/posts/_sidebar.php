
<div>
<a href="/">
	<img src="/images/chinesead.png" class="sidebar_head_pic">
</a>

<ul class="nostyle nopaddingleft top20">
	<?php if(Yii::app()->user->isGuest): ?>
		<?php if(!isset($_GET['category_id']) || $_GET['category_id'] != 4): ?>
			<li><a href="#" onclick="signup(); return false;"><i class="fa fa-link"></i> <span style="margin-left:4px;">提交链接</span></a></li>
			<li><a href="#" onclick="signup(); return false;"><i class="fa fa-pencil-square-o"></i> <span style="margin-left:4px;">提交内容</span></a></li>
		<?php endif; ?>
			<li><a href="#" onclick="signup(); return false;"><i class="fa fa-microphone"></i> <span style="margin-left:9px;">有问必答</span></a></li>
	<?php else: ?>


		<?php if(!isset($_GET['category_id']) || $_GET['category_id'] != 4): ?>
			<li><a href="#" onclick="post_new(); setTimeout(show_link, 3); return false;"><i class="fa fa-link"></i> <span style="margin-left:4px;">提交链接</span></a></li>
			<li><a href="#" onclick="post_new(); setTimeout(show_content, 3); return false;"><i class="fa fa-pencil-square-o"></i> <span style="margin-left:4px;">提交内容</span></a></li>
		<?php endif; ?>
			<li><a href="#" onclick="post_new(); setTimeout(show_ama, 3); return false;"><i class="fa fa-microphone"></i> <span style="margin-left:9px;">有问必答</span></a></li>

	<?php endif; ?>
<!--
<?php if(Yii::app()->user->id): ?>
	<hr>
	没六儿目前仅限邀请注册<br>
	邀请朋友加入请使用邀请码:<br>
	<b style="font-size:16px;"><u>ML999</u></b>
<?php endif; ?>
-->
<!--
	<hr>
	<li><a href="/"><i class="fa fa-fire"></i> <span style="margin-left:7px;">热点</span></a></li>
	<li><a href="/funny"><i class="fa fa-smile-o"></i> <span style="margin-left:6px;">娱乐</span></a></li>
	<li><a href="/news"><i class="fa fa-newspaper-o"></i> <span style="margin-left:3px;">新闻</span></a></li>
	<li><a href="/tech"><i class="fa fa-desktop"></i> <span style="margin-left:4px;">科技</span></a></li>
	<li><a href="/other"><i class="fa fa-commenting-o"></i> <span style="margin-left:6px;">杂谈</span></a></li>
	<li><a href="/ama"><i class="fa fa-comments"></i> <span style="margin-left:6px;">有问必答</span></a></li>
</ul>
-->

<hr>

<ul class="dotul align_left nopaddingleft">
	<li><a class="dark_grey small" href="/site/contact?feedback">建议反馈</a></li>
	<li><a class="dark_grey small" href="/site/contact">联系我们</a></li>
	<li class="dark_grey small">&copy; <?php echo date('Y'); ?> 没六儿</li>
</ul>
</div>