<!DOCTYPE html>
<html lang="en">
	<head>
    	<meta charset="utf-8">
    	<title><?php echo $projectname; ?> - <?php echo $title_for_layout; ?></title>
    	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    	<meta name="description" content="">
    	<meta name="author" content="">
    	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('bootstrap.min');
		echo $this->Html->css('bootstrap-fileupload.min');
		echo $this->Html->css('style');
		echo $this->Html->css('jquery/ui-lightness/jquery-ui-1.8.22.custom.css');
		echo $this->fetch('meta');
		echo $this->fetch('script');
		?>
		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
      		<script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
      	<![endif]-->
      	<?php
      	echo $this->Html->script('jquery.min');
      	echo $this->Html->script('bootstrap.min');
      	echo $this->Html->script('bootstrap-fileupload.min');
      	echo $this->Html->script('jquery-ui-1.8.22.custom.min');
      	echo $this->Html->script('jquery.tablesorter.min');
      	echo $this->Html->script('angular.min');
      	echo $this->Html->script('angular/app');
      	echo $this->Html->script('angular/services');
      	
      	?>
      	<!-- Chrome Frame -->
    	<meta http-equiv="X-UA-Compatible" content="chrome=1">
    	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js"></script>
    	<!-- Google Analytics -->
      	<script type="text/javascript">
	      	var _gaq = _gaq || [];
	      	_gaq.push(['_setAccount', 'UA-34847760-1']);
	      	_gaq.push(['_trackPageview']);
	      	(function() {
	      	  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	      	  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	      	  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	      	})();
  		</script>
	</head>
	<body>
		<style>
		.chromeFrameInstallDefaultStyle {
		    top:140px;
		    border:3px solid black;
		    left:12px;
		    margin:0 auto;
       	}
     	</style>
		<script type='text/javascript'>
		/*$(document).ready(function() {
		if(CFInstall) {
		 	CFInstall.check({
		 	});
		 	$('.chromeFrameInstallDefaultStyle').width($(document).width()-30);
		 	$('.chromeFrameInstallDefaultStyle').height($(document).height()-306);
		}
		});*/
		</script>
		<div id="container">
			<div id="header">
				<h1>
					<a class='ceit' href='http://ceit.uq.edu.au' target='_blank'><?php echo $this->Html->image("ceit.png"); ?></a>
					<?php
						if(isset($loggedIn) && $loggedIn) {
							echo '<p id="logindetails">Logged in as '.$userid.'</p>';
							echo $this->Html->link("Logout", '/users/logout',array('escape' => false,'class'=>'logout'));
						}
					?>
					<?php echo $this->Html->link("<strong>UQ</strong>Markup", '/',array('escape' => false,'class'=>'logo')); ?>
				</h1>
			</div>
			<div id="contentwrapper">
				<div id="content">
					<ul class="breadcrumb">
						<?php
							$bci=1;
							foreach($breadcrumbs as $bcurl=>$bcname) {
								if($bci == sizeOf($breadcrumbs)) {
									echo '<li class="active">'.$bcname.'</li>';
								} else {
									echo '<li><a href="'.$baseURL.$bcurl.'">'.$bcname.'</a><span class="divider">/</span></li>';	
								}
								$bci++;
							}
						?>
					</ul>
					<?php echo $this->Session->flash(); ?>
					<?php echo $this->fetch('content'); ?>
				</div>
			</div>
			<div id="footer">
				&copy; 2012 CEIT, UQ, Ably, Lovely Head
				<?php if(isset($loggedIn) && $loggedIn) { ?>
					<em><a href='<?php echo $baseURL; ?>/pages/contactus'>Got an issue? Let us know.</a></em>
					<em><a href='<?php echo $baseURL; ?>/pages/ethicalclearance'>Ethical clearance status.</a></em>
				<?php } ?>
			</div>
		</div>
		<div id='debug'>
		<?php echo $this->element('sql_dump'); ?>
		</div>
	</body>
</html>
