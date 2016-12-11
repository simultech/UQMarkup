<h2>Number of access times for <?php echo $project_name; ?></h2>
<?php
	$timeblockhtml = "";
	$maxwidth = 800;
	$maxheight = 500;
	$maxsize = 0;
	$formattedtimes = array(
		'_0'=>0,
		'_1'=>0,
		'_2'=>0,
		'A'=>0,
		'_3'=>0,
		'_6'=>0,
		'_9'=>0,
		'_12'=>0,
		'_15'=>0,
		'_30'=>0,
		'_45'=>0,
		'B'=>0,
		'_60'=>0,//1
		'_120'=>0,
		'_180'=>0,
		'_240'=>0,
		'_300'=>0,//5
		'_360'=>0,
		'_420'=>0,
		'_480'=>0,
		'_540'=>0,
		'C'=>0,
		'_600'=>0,//10
		'_1200'=>0,//10
	);
	sort($times);
	foreach($times as $time) {
		$minutes = round($time/60);
		
		if($minutes < 3) {
			//$minutes = round($minutes/3)*3;	
		} else if($minutes < 15) {
			$minutes = round($minutes/3)*3;	
		} else if($minutes < 60) {
			$minutes = round($minutes/15)*15;	
		} else if ($minutes < 600) {
			$minutes = round($minutes/60)*60;
		} else {
			$minutes = round($minutes/600)*600;
		}
		if(isset($formattedtimes['_'.$minutes])) {
			$formattedtimes['_'.$minutes] = $formattedtimes['_'.$minutes] + 1;
		} else {
			$formattedtimes['_'.$minutes] = 1;
		}
	}
	//echo '<pre>';
	//print_r($formattedtimes);
	//echo '</pre>';
	$barwidth = $maxwidth/sizeOf($formattedtimes);
	foreach($formattedtimes as $time) {
		if($time > $maxsize) {
			$maxsize = $time;
		}
	}
	$count = 0;
	foreach($formattedtimes as $timescale=>$time) {
		if($timescale == 'A' || $timescale == 'B' || $timescale == 'C') {
			$timeblockhtml .= '<div class="barsplit" style="top:0px; left:'.$count*$barwidth.'px; height:'.$maxheight.'px"></div>';
			$count++;
			continue;
		}
		$timescale = str_replace("_", "", $timescale);
		//$formattedtimes[]
		$theheight = ($time/$maxsize*$maxheight);
		if($timescale < 3) {
			$formattimescale = $timescale.'-'.($timescale+1).' minutes';
		} else if($timescale < 15) {
			$formattimescale = $timescale.'-'.($timescale+3).' minutes';
		} else if($timescale < 60) {
			$formattimescale = $timescale.'-'.($timescale+15).' minutes';
		} else if($timescale < 600) {
			$formattimescale = ($timescale/60).'-'.(($timescale/60)+1).' hours';
		} else {
			$formattimescale = ($timescale/60).'-'.(($timescale/60)+10).' hours';
		}
		$timeblockhtml .= '<div class="bar" style="top:'.($maxheight-$theheight).'px; left:'.($count*$barwidth-1).'px; height:'.($theheight-1).'px"></div><div class="barlegend" style="left:'.$count*$barwidth.'px">'.$formattimescale.'</div>';
		$count++;
		//$top = ($maxwidth - $time/$maxsize*$maxwidth);
		//$timehtml .= "<div style='top:".$top."px'></div><span style='top:".$top."px'>".round($time/60/60)." hours (".round($time/60)." minutes)</span>";
	}
?>
<ul class='y_values'>
	<?php
		for($i=0; $i<10; $i++) {
			echo '<li>'.round($maxsize-($maxsize*$i/10)).'</li>';
		}
	?>
</ul>
<p class='y_axis'>Number of sessions</p>
<div id='timegraph'>
	<?php echo $timeblockhtml; ?>
</div>
<p class='x_axis'>Duration Open</p>

<style type='text/css'>
	div#timegraph {
		border-left:1px solid #333;
		border-bottom:1px solid #333;
		width:<?php echo $maxwidth; ?>px;
		height:<?php echo $maxheight; ?>px;
		position:relative;
		margin-left:80px;
		margin-bottom:140px;
	}
	div#timegraph div.bar {
		width:<?php echo $barwidth-2; ?>px;
		left:100px;
		border:1px solid #c10dfd;
		background:#ddf;
		position:absolute;
	}
	div#timegraph div.barsplit {
		width:<?php echo ($barwidth-2)/2; ?>px;
		border-right:1px dashed red;
		position:absolute;
	}
	div#timegraph div.barlegend {
		border-top:1px solid #333;
		top:<?php echo $maxheight; ?>px;
		position:absolute;
		-webkit-transform: rotate(-90deg); 
		-moz-transform: rotate(-90deg);
		height:<?php echo $barwidth-22; ?>px;
		text-align:right;
		padding-right:20px;
		width:100px;
		padding-top:6px;
		margin-top:50px;
		margin-left:-52px;
		filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
	}
	p.y_axis {
		-webkit-transform: rotate(-90deg); 
		-moz-transform: rotate(-90deg);
		filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
		width:<?php echo $maxheight; ?>px;
		text-align:center;
		margin-left:-<?php echo $maxheight/2; ?>px;
		margin-top:<?php echo $maxheight/2; ?>px;
		position:absolute;
	}
	p.x_axis {
		width:<?php echo $maxwidth; ?>px;
		text-align:center;
		margin-left:30px;
		margin-top:10px;
	}
	ul.y_values {
		position:absolute;
		z-index:4;
		height:<?php echo $maxheight; ?>px;
		padding:0;
		margin:0;
		list-style-type:none;
		margin-left:50px;
	}
	ul.y_values li {
		height:<?php echo $maxheight/10; ?>px;
		border-top:1px solid green;
		width:30px;
		padding:0;
		margin:0;
	}
</style>