
<div>
<img src="/images/placeholder.jpg" class="top10 sidebar_head_pic">


<ul class="nostyle nopaddingleft top20">
	<?php if(Yii::app()->user->isGuest): ?>
		<li><a href="/site/login"><i class="fa fa-external-link"></i> <span style="margin-left:4px;">分享链接</span></a></li>
		<li><a href="/site/login"><i class="fa fa-pencil-square-o"></i> <span style="margin-left:4px;">分享内容</span></a></li>
	<?php else: ?>
		<li><a href="/submit"><i class="fa fa-external-link"></i> <span style="margin-left:4px;">分享链接</span></a></li>
		<li><a href="/submit?content"><i class="fa fa-pencil-square-o"></i> <span style="margin-left:4px;">分享内容</span></a></li>
		<li><a href="/users/<?php echo Yii::app()->user->id; ?>"><i class="fa fa-history"></i> <span style="margin-left:6px;">我的分享</a></span></li>
	<?php endif; ?>
	<hr>
	<li><a href="/"><i class="fa fa-fire"></i> <span style="margin-left:7px;">热门</span></a></li>
	<li><a href="/funny"><i class="fa fa-smile-o"></i> <span style="margin-left:6px;">搞笑</span></a></li>
	<li><a href="/news"><i class="fa fa-newspaper-o"></i> <span style="margin-left:3px;">新闻</span></a></li>
	<li><a href="/tech"><i class="fa fa-desktop"></i> <span style="margin-left:4px;">科技</span></a></li>
	<li><a href="/other"><i class="fa fa-comments"></i> <span style="margin-left:6px;">杂谈</span></a></li>
</ul>

<hr>

<ul class="dotul align_left nopaddingleft">
	<li><a class="dark_grey small" href="/site/contact?feedback">建议反馈</a></li>
	<li><a class="dark_grey small" href="/site/contact">联系我们</a></li>
	<li class="dark_grey small">&copy; <?php echo date('Y'); ?> 没六儿</li>
</ul>
</div>