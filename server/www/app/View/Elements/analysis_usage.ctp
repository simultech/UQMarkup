<?php
	$width_size = 900;
	$height_size = 400;
	$biggest = 0;
	$biggest_id = '';
	foreach($usagelogs as $usagelogid=>$usagelog) {
		if($usagelog > $biggest) {
			$biggest = $usagelog;
			$biggest_id = $usagelogid;
		}
	}
	if(!isset($yaxis)) {
		$yaxis = 'Number of Student Interactions';
	}
?>
<div id='usage'>
	<div class='left_bar'><?php echo $yaxis; ?></div>
	<?php
		$offset = 0;
		$barwidth = $width_size/sizeOf($usagelogs);
		foreach($usagelogs as $usagetime=>$usagelog) {
			echo '<p style="left:'.($offset-40).'px; height:'.$barwidth.'px">'.$usagetime.'</p>';
			$barheight = $usagelog/$biggest*$height_size;
			echo '<div class="usagebar" style="width:'.$barwidth.'px; height:'.$barheight.'px; left:'.$offset.'px; top:'.($height_size-$barheight).'px;"><em style="height:'.$barwidth.'px">'.$usagelog.'</em></div>';
			$offset += $barwidth;
		}
	?>
</div>
<style type='text/css'>
	div#usage {
		border-left:1px solid #333;
		border-bottom:1px solid #333;
		width:<?php echo $width_size; ?>px;
		height:<?php echo $height_size; ?>px;
		position:relative;
		margin-bottom:150px;
		margin-top:50px;
		margin-left:20px;
	}
	div#usage div.left_bar {
		-webkit-transform:rotate(270deg);
		-moz-transform:rotate(270deg);
		-o-transform: rotate(270deg);
		width:400px;
		text-align:center;
		position:absolute;
		top:190px;
		left:-215px;
		font-weight:bold;
		font-size:85%;
		color:#333;
	}
	div#usage div.usagebar {
		background:#ddf;
		position:absolute;
	}
	div#usage div.usagebar em {
		position:absolute;
		display:block;
		-webkit-transform:rotate(270deg);
		-moz-transform:rotate(270deg);
		-o-transform: rotate(270deg);
		font-size:80%;
		margin-top:-75px;
		margin-left:-24px;
		width:100px;
		color:#226;
		font-style:normal;
	}
	div#usage p {
		-webkit-transform:rotate(270deg);
		-moz-transform:rotate(270deg);
		-o-transform: rotate(270deg);
		position:absolute;
		text-align:right;
		top:<?php echo $height_size+40; ?>px;
		text-indent:5px;
		font-size:70%;
		color:#333;
		margin:0;
		padding:0;
		padding-right:5px;
		padding-top:8px;
		width:100px;
		border-top:1px solid #eee;
		margin-left:7px;
		margin-top:-5px;
	}
</style>