<h2>Overall access times (longest to shortest)</h2>
<?php
	$timehtml = "";
	$maxwidth = 4800;
	$maxsize = 0;
	foreach($times as $time) {
		if($time > $maxsize) {
			$maxsize = $time;
		}
	}
	foreach($times as $time) {
		$top = ($maxwidth - $time/$maxsize*$maxwidth);
		$timehtml .= "<div style='top:".$top."px'></div><span style='top:".$top."px'>".round($time/60/60)." hours (".round($time/60)." minutes)</span>";
	}
?>

<div id='times'>
	<?php echo $timehtml; ?>
</div>

<style type='text/css'>
	div#times {
		border-left:1px solid red;
		height:<?php echo $maxwidth; ?>px;
		margin-left:200px;
		position:relative;
	}
	div#times div {
		width:10px;
		height:10px;
		background:#00f;
		border-radius:5px;
		position:absolute;
		left:-6px;
	}
	div#times span {
		display:block;
		position:absolute;
		width:100%;
		top:300px;
		left:20px;
	}
</style>
