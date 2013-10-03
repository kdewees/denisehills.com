<?
echo doctype();
$this_page = new Page();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<?
echo link_tag('css/reset.css');
echo link_tag('css/text.css');
echo link_tag('css/960_12_col.css');
echo link_tag('css/main.css');
echo link_tag('css/slideshow.css');
?>
	<script src="js/mootools-1.3.2-core.js"></script>
	<script src="js/mootools-1.3.2.1-more.js"></script>
	<script src="js/slideshow.js"></script>
	<script src="js/slideshow.push.js"></script>
	<script>
		window.addEvent('domready', function(){
			var data = [ 'slide1.jpg', 'slide2.jpg', 'slide3.jpg', 'slide4.jpg', 'slide5.jpg' ];

			new Slideshow.Push('theshow', data, { delay: 3000, height: 200, hu: 'images/slides/', transition: 'back:in:out', width: 680 });
		});
	</script>
	<title>LEAP - <?= $title; ?></title>
</head>
<body>
<div class="header">
	<div class="header-container">
		<? echo img(array('src' => 'images/leap_logo.png', 'alt' => 'LEAP: Learning Engineering and Applying Principles. By Southern Company.', 'class' => 'logo')); ?>
		<div id="theshow" class="slideshow">
			<div class="slideshow-images">
				<? echo img('images/slides/slide1.jpg'); ?>
			</div>
		</div>
	</div>
</div>
<div class="clear"></div>
<div class="navigation">
	<?= $header_links; ?>
</div>
<div class="clear"></div>
<div class="container_12">
<html>
	<div class="container row1">
		<div class="grid_6 box one">
			<div class="top"></div>
			<div class="middle">
				<h1><? echo img(array('src' => 'images/what_is_leap.png', 'alt' => 'What is leap?')); ?></h1>
				<div class="scroll">
<?
echo $this_page->get_pagepart(1);
?>
				</div><!-- scroll box -->
				<div class="sub-box">
					<a href="mission/"><? echo img(array('src' => 'images/buttons/btn_mission.png', 'alt' => 'Mission')); ?></a>
					<a href="history/"><? echo img(array('src' => 'images/buttons/btn_history.png', 'alt' => 'History')); ?></a>
					<a href="team/"><? echo img(array('src' => 'images/buttons/btn_team.png', 'alt' => 'Team')); ?></a>
				</div>
			</div>
			<div class="bottom green"></div>
		</div>
		<div class="grid_6 box center">
			<div class="top green"></div>
			<div class="middle">
				<h1><? img(array('src' => 'images/featured_videos.png', 'alt' => 'Featured Videos')); ?></h1>
				<div class="sub-box">
					<a href="#"><img src="/images/video1_thumbnail.png" alt="Watch a video about the power generation team." /></a>
					<a href="#"><img src="/images/video2_thumbnail.png" alt="Watch a video about the power transmission team." /></a>
					<a href="#"><img src="/images/video3_thumbnail.png" alt="Watch a video about the power distribution team." /></a>
				</div>
				<div><img src="/images/video_placeholder.jpg" alt="watch the video" /></div>
			</div>
			<div class="bottom"></div>
		</div>
	</div><!-- end row1 container -->
	<div class="clear"></div>
	<div class="container row2">
		<div class="grid_4 box news-feed">
			<div class="top"></div>
			<div class="middle">
				<h1><img src="/images/news_feed.png" alt="News Feed" /></h1>
				<div style='margin-left: 45px;'>
					<div id="fb-root"></div><script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like-box href="http://www.facebook.com/pages/LEAP/144639668921129" width="296" show_faces="false" border_color="white" stream="true" header="false"></fb:like-box>
				</div>
			</div>
			<div class="bottom"></div>
		</div>
		<div class="grid_4 box">
			<div class="top"></div>
			<div class="middle">
				<h1><img src="/images/smart_links.png" alt="Smart Links" /></h1>
				<div class="links">
<?
echo $this_page->create_navigation(2);
?>
				</div>
			</div>
			<div class="bottom"></div>
		</div>
		<div class="grid_4 box center">
			<div class="top"></div>
			<div class="middle">
				<h1><img src="/images/contact.png" alt="Contact" /></h1>
<?
echo $this_page->get_pagepart(2);
?>
			</div>
			<div class="bottom"></div>
		</div>
	</div><!-- end row2 container -->
	<div class="clear"></div>
	<div class="container row3">
		<div class="grid_12 box timeline">
			<div class="top"></div>
			<div class="middle">
				<h1><img src="/images/annual_timeline.png" alt="Annual Timeline" /></h1>
				<div>
					<img src="/images/sept.png" alt="September" />
					<img src="/images/blank_timeline.png" alt="timeline" />
					<img src="/images/may.png" alt="May" />
					<?= $timeline; ?>
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div><!-- end row3 container -->
	<div class='clear'></div>
	<br /><br />
</div><!-- end main container -->
</body>
</html>