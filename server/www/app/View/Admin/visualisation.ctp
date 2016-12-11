<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(loadCharts);
    function loadCharts() {
	    //alert("LOADING CHARTS");
	    drawChartA();
	    drawChartB();
	    drawChartC();
	    drawChartD();
	    drawChartE();
	    drawChartF();
    }
    var options = {
		'colors':['#60419D','#ddd','#ddd','#ddd','#ddd'],
		'legend':{position:'none'},
		'pieSliceText':'none'
	};
</script>
<h2>Statistics for <?php echo $project_name; ?></h2>
<?php
	if(isset($marker_id)) {
		echo '<h3>'.$marker_id.'</h3>';
	}
?>
<p class='gentime'>Report generated at <?php echo date('l jS \of F Y h:i:s A',strtotime("-10 hours")); ?></p>
<div class='block_half'>
	<h3>Submission Statistics</h3>
		<div class='stat'>
			<em><?php echo $stats['count_total']; ?><span>documents</span></em>
			<p>have been published in total</p>
		</div>
		<div class='stat'>
			<div id='chart_uqmarkup' class='inline_chart'></div>
			<em><?php echo $stats['count_uqmarkup']; ?><span>documents</span></em>
			<p>were marked within UQMarkup</p>
			<script type='text/javascript'>
				function drawChartA() {
					var data = google.visualization.arrayToDataTable([['Method', 'Number of submissions'],
						['UQMarkup',<?php echo $stats['count_uqmarkup']; ?>],
						['Traditional',<?php echo $stats['count_traditional']; ?>]
					]);
					var chart = new google.visualization.PieChart(document.getElementById('chart_uqmarkup')).draw(data, options);
				}
			</script>
		</div>
		<div class='stat'>
			<div id='chart_traditional' class='inline_chart'></div>
			<em><?php echo $stats['count_traditional']; ?><span>documents</span></em>
			<p>were marked traditionally</p>
			<script type='text/javascript'>
				function drawChartB() {
					var data = google.visualization.arrayToDataTable([['Method', 'Number of submissions'],
						['Traditional',<?php echo $stats['count_traditional']; ?>],
						['UQMarkup',<?php echo $stats['count_uqmarkup']; ?>]
					]);
					var chart = new google.visualization.PieChart(document.getElementById('chart_traditional')).draw(data, options);
				}
			</script>
		</div>
		<div class='stat'>
			<div id='chart_viewed' class='inline_chart'></div>
			<em><?php echo sizeOf($stats['read_submissions']); ?><span>documents</span></em>
			<p>have been opened by students</p>
			<script type='text/javascript'>
				function drawChartC() {
					var data = google.visualization.arrayToDataTable([['Method', 'Number of submissions'],
						['Viewed',<?php echo sizeOf($stats['read_submissions']); ?>],
						['Not Viewed',<?php echo $stats['count_total']-sizeOf($stats['read_submissions']); ?>]
					]);
					var chart = new google.visualization.PieChart(document.getElementById('chart_viewed')).draw(data, options);
				}
			</script>
		</div>
</div>
<div class='block_half'>
	<h3>Time-based Analytics</h3>
		<div class='stat'>
			<em><?php echo $stats['marking_time']; ?><span>hours</span></em>
			<p>were spent marking 
			<?php
				if($stats['marking_notrecorded'] > 0) {
					echo '- (based on '.($stats['count_total']-$stats['marking_notrecorded']).' documents)';
				}
			?>	
			</p>
		</div>
		<div class='stat'>
			<em><?php echo round($stats['marking_time']/$stats['count_uqmarkup']*60); ?><span>minutes</span></em>
			<p>was the average marking time per document</p>
		</div>
		<div class='stat'>
			<em><?php echo $stats['reading_time']; ?><span>hours</span></em>
			<p>were spent by student viewing feedback</p>
		</div>
		<div class='stat'>
			<em><?php echo round($stats['reading_time']/sizeOf($stats['read_submissions'])*60); ?><span>minutes</span></em>
			<p>average was spent viewing feedback per document</p>
		</div>
</div>
<div class='block_half'>
	<h3>Annotation Statistics</h3>
		<div class='stat'>
			<em><?php echo $stats['annots']['Total']; ?><span>annotations</span></em>
			<p>were created in total</p>
		</div>
		<div class='stat'>
			<div id='chart_audio' class='inline_chart'></div>
			<em><?php echo $stats['annots']['Recording']; ?><span>annotations</span></em>
			<p>were audio based</p>
			<script type='text/javascript'>
				function drawChartD() {
					var data = google.visualization.arrayToDataTable([['Method', 'Number of submissions'],
						['Audio',<?php echo $stats['annots']['Recording']; ?>],
						['Graphics',<?php echo $stats['annots']['Freehand']+$stats['annots']['Highlight']; ?>],
						['Text',<?php echo $stats['annots']['Text']; ?>],
					]);
					var chart = new google.visualization.PieChart(document.getElementById('chart_audio')).draw(data, options);
				}
			</script>
		</div>
		<div class='stat'>
			<div id='chart_graphics' class='inline_chart'></div>
			<em><?php echo $stats['annots']['Freehand']+$stats['annots']['Highlight']; ?><span>annotations</span></em>
			<p>were drawing based (pen or highlights)</p>
			<script type='text/javascript'>
				function drawChartE() {
					var data = google.visualization.arrayToDataTable([['Method', 'Number of submissions'],
						['Graphics',<?php echo $stats['annots']['Freehand']+$stats['annots']['Highlight']; ?>],
						['Audio',<?php echo $stats['annots']['Recording']; ?>],
						['Text',<?php echo $stats['annots']['Text']; ?>],
					]);
					var chart = new google.visualization.PieChart(document.getElementById('chart_graphics')).draw(data, options);
				}
			</script>
		</div>
		<div class='stat'>
			<div id='chart_text' class='inline_chart'></div>
			<em><?php echo $stats['annots']['Text']; ?><span>annotations</span></em>
			<p>were text based</p>
			<script type='text/javascript'>
				function drawChartF() {
					var data = google.visualization.arrayToDataTable([['Method', 'Number of submissions'],
						['Text',<?php echo $stats['annots']['Text']; ?>],
						['Audio',<?php echo $stats['annots']['Recording']; ?>],
						['Graphics',<?php echo $stats['annots']['Freehand']+$stats['annots']['Highlight']; ?>],
					]);
					var chart = new google.visualization.PieChart(document.getElementById('chart_text')).draw(data, options);
				}
			</script>
		</div>
</div>
<div class='block_half'>
	<h3>Audio Analytics</h3>
		<div class='stat'>
			<em><?php echo $stats['audio_time']; ?><span>hours</span></em>
			<p>worth of audio annotations were created</p>
		</div>
		<div class='stat'>
			<em><?php echo round($audio_listening['listenedtime']/$audio_listening['totaltime']*100); ?><span>%</span></em>
			<p>of audio in opened documents were listened to by students</p>
		</div>
		<div class='stat'>
			<?php
			$totalaudiotime = $stats['audio_time']*60/$stats['annots']['Recording'];
			if($totalaudiotime > 5) {
				echo '<em>'.round($totalaudiotime,2).'<span>minutes</span></em>';
				echo '<p>was the average length of an audio annotation</p>';
			} else {
				echo '<em>'.round($totalaudiotime*60).'<span>seconds</span></em>';
				echo '<p>was the average length of an audio annotation</p>';
			}
			?>
		</div>
		<div class='stat'>
			<em><?php echo round($stats['annots']['Recording']/sizeOf($stats['audio_submissions'])); ?><span>annotations</span></em>
			<p>was the average number of audio annotations within audio enabled documents</p>
		</div>
</div>
<div style='clear:both'></div>
<p>&nbsp;</p>
<p>&nbsp;</p>
<h2>Student Activity (Daily)</h2>
<?php echo $this->element('analysis_usage',array('usagelogs'=>$orderedlogs_day,'yaxis'=>'Number of recorded interations (activity)')); ?>
<?php echo $this->element('analysis_usage',array('usagelogs'=>$openedlogs_day,'yaxis'=>'Number of documents opened')); ?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<h2>Student Activity (Hourly)</h2>
<?php echo $this->element('analysis_usage',array('usagelogs'=>$orderedlogs_hr,'yaxis'=>'Number of recorded interations (activity)')); ?>
<?php echo $this->element('analysis_usage',array('usagelogs'=>$openedlogs_hr,'yaxis'=>'Number of documents opened')); ?>

<p>Generated in <?php echo $totaltime; ?> seconds</p>

<style type='text/css'>
div.block_half {
	border:1px solid #aaa;
	border-bottom:0;
	width:452px;
	float:left;
	margin-right:11px;
	margin-bottom:12px;
}
div.block_half dl {
	border-bottom:0;
	margin-bottom:0;
}
div.block_half h3 {
	background:#888;
	color:#fff;
	font-size:110%;
	margin:0;
	padding:10px;
	text-align:center;
	line-height:1em;
}
div.block_half div.stat {
	padding-top:30px;
}
div.block_half div.stat em {
	margin-left:10px;
	font-size:500%;
	color:#60419D;
	font-style:normal;
	font-weight:bold;
	letter-spacing:0.1em;
	display:block;
	line-height:0.3em;
}
div.block_half div.stat em span {
	font-size:35%;
	color:#d0d0d0;
	font-weight:lighter;
	letter-spacing:0.1em;
}
div.block_half div.stat p {
	margin:0;
	padding:0;
	border-bottom:1px solid #ccc;
	display:block;
	width:432px;
	font-size:110%;
	padding:10px;
	font-weight:lighter;
	letter-spacing:0.1em;
	font-family:"Helvetica";
	color:#888;
}
div.inline_chart {
	float:right;
	width:100px;
	height:100px;
	margin-top:-30px;
}
p.gentime {
	color:#999;
}
</style>