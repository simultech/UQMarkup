<?php
	$graphwidth = 600;
	$graphheight = 400;
	$audiocount = 0;
?>
<audio controls="controls" id="audioplayer_106" src="<?php echo $baseURL; ?>/assessment/audio/229/annot__2012_09_16_09_28_14.m4a"></audio>
<h2>Statistics for Submission <?php echo $submission['Submission']['id']; ?></h2>
<dl>
	<dt>Link: </dt><dd><a target="_blank" href="<?php echo $submission['data']['uri']; ?>"><?php echo Configure::read('url_base'); ?><?php echo $submission['data']['uri']; ?></a></dd>
	<dt>Submitted by: </dt><dd><?php echo $submission['data']['submittedby']['name']; ?> (<?php echo $submission['data']['submittedby']['uqid']; ?>)</dd>
	<dt>Marked by: </dt><dd><?php echo $submission['data']['markedby']['name']; ?> (<?php echo $submission['data']['markedby']['uqid']; ?>)</dd>
	<dt>Moderated: </dt>
	<dd>
	<?php
		if(isset($submission['data']['moderated'])) {
			echo $submission['data']['markedby']['name']. '('.$submission['data']['markedby']['uqid'].')'; 
		} else {
			echo "Not moderated";
		}
	?>
	</dd>
	<dt>Marking time: </dt><dd>
	<?php
		if(isset($submission['data']['marks']->time_spent_marking)) {
			$time = $submission['data']['marks']->time_spent_marking;
			echo date('i:s',$time).'';
		} else {
			echo 'Not recorded';
		}
	?>
	</dd>
	<dt>Grades: </dt><dd><ul>
	<?php
		foreach($submission['data']['rubricmarks'] as $rubricmark) {
			echo '<li><strong>'.$rubricmark['name'].'</strong> - '.$rubricmark['value'].'</li>';
		}
	?>
	</ul></dd>
	<dt>Viewing sessions: </dt><dd><?php echo sizeOf($submission['sessions']); ?></dd>
</dl>
<h3>Reviewing Feedback</h3>
<?php
$runcount = 0;
if(sizeOf($submission['sessions']) == 0) {
	echo '<p>No logs for this submission</p>';
}
foreach($submission['sessions'] as $sessionid=>$session) {
?>
	<h4>Session: <?php echo $sessionid;?></h4>
	<?php
	foreach($session['runs'] as $run) {
		echo '<h5>Run</h5>';
		if(!isset($run['starttime']) || !isset($run['endtime'])) {
			echo '<p>unfinished or incomplete run</p>';
			continue;
		}
		$runcount++;
	?>
	<dl>
		<dt>Viewing time: </dt><dd><?php echo strtotime($run['endtime'])-strtotime($run['starttime']); ?> seconds</dd>
		<dt>Start time: </dt><dd><?php echo $run['starttime']; ?></dd>
		<dt>End time: </dt><dd><?php echo $run['endtime']; ?></dd>
		<dt>Raw data: </dt><dd id="rawdata_<?php echo $runcount; ?>"><a href="javascript:showrawdata('<?php echo $runcount; ?>');">Show data</a><pre style="display:none"><?php print_r($run); ?></pre></dd>
		<dt>Position in document: </dt>
		<dd>
		<?php
			$annotationpositions = array();
			foreach($submission['data']['annotations'] as $annotation) {
				if(!isset($annotationpositions[$annotation->page_no-1])) {
					$annotationpositions[$annotation->page_no-1] = array();
				}
				$newannot = array('type'=>$annotation->type,'title'=>$annotation->title,'yPercentage'=>$annotation->y_percentage);
				$annotationpositions[$annotation->page_no-1][] = $newannot;
			}
			$positions = array();
			$lastmoved = strtotime($run['starttime']);
			$lastposition = 0;
			$pagesize = 0;
			$maxtimediff = 1;
			foreach($run['data']['Scroll'] as $scroll) {
				if($scroll['meta']->pagesize > 0) {
				    $pagesize = $scroll['meta']->pagesize;
				}
			    $timediff = strtotime($scroll['created']) - $lastmoved;
			    if(!isset($positions['_'.$lastposition])) {
			    	$positions[$lastposition] = 0;
			    }
			    $positions[$lastposition] += $timediff;
			    if($maxtimediff < $positions[$lastposition]) {
			    	$maxtimediff = $positions[$lastposition];
			    }
			    $lastposition = $scroll['meta']->end;
			    $lastmoved = strtotime($scroll['created']);
			}
			echo '<div class="graph">';
				$offset = 0;
				for($i=1; $i<=$run['pages']; $i++) {
				    $percentage = $run['pagelengths'][$i-1]/100;
				    echo '<div class="pagebit" style="left:'.$offset.'px; width:'.$graphwidth*$percentage.'px; ">'.$i.'</div>';
				    if(isset($annotationpositions[$i-1])) {
				    	$annots = 0;
					    foreach($annotationpositions[$i-1] as $annot) {
					    	$leftpos = $offset + $annot['yPercentage']*$graphwidth*$percentage;
					    	$toppos = $graphheight - ($annots * 40)-40;
					    	$txt = "G";
					    	if($annot['type'] == 'Recording') {
						    	$txt = "A";
					    	}
					    	if($annot['type'] == 'Text') {
						    	$txt = "T";
					    	}
					    	echo '<div class="annotation_graph_icon annotation_graph_'.$annot['type'].'" title="'.$annot['title'].'" style="left:'.$leftpos.'px; top:'.$toppos.'px">'.$txt.'</div>';
						    $annots++;
					    }
				    }
				    $offset += $graphwidth*$percentage;
				}
				foreach($positions as $position=>$time) {
				    $startx = ($position/100)*($graphwidth);
				    $endx = $startx + ($graphwidth*$pagesize/100);
				    $barwidthminus = ($endx-$startx)*$position/100;
				    $startx -= $barwidthminus;
				    $endx -= $barwidthminus;
				    $heightperchunk = $graphheight/$maxtimediff;
				    $endy = $heightperchunk*($maxtimediff-$time);
				    $theheight = $graphheight-$endy;
				    echo '<div class="cell" style="width:'.($graphwidth*$pagesize/100).'px; left:'.$startx.'px; top:'.$endy.'px; height:'.$theheight.'px;"></div>';
				}
				echo '<div class="yaxis">Time</div>';
				echo '<div class="xaxis">Page</div>';
			echo '</div>';
		?>
		</dd>
		<dt>Audio annotations listening</dt>
			<dd>
			<?php
			$audioannotationcount = 0;
			foreach($submission['data']['annotations'] as $audioannotation) {
				if($audioannotation->type == 'Recording') {
					echo '<div class="audioannotationgraph" onClick="playPauseAudio(\'audioplayer_'.$audiocount.'\');">';
					echo '<p>'.$audioannotation->title.' ('.$audioannotation->duration.' seconds)</p>';
					for($i=0; $i<10; $i++) {
					    $w = rand(0,100);
					    $l = rand(0,200);
					    //echo '<div class="audiocell" style="width:'.$w.'px; left:'.$l.'px;"></div>';
					}
					$playing = false;
					$switched = 2;
					$lastplayingtime = 0;
					$timesnapshots = array();
					foreach($run['data']['Audio'] as $audiolog) {
						if($audiolog['meta']->annotation == $audioannotationcount) {
							$time = explode(":",$audiolog['meta']->currentTime);
							$time = $time[0]*60+$time[1];
							if($audiolog['meta']->state == 'playing') {
								$lastplayingtime = $time;
								$playing = true;
								if($switched > 0) {
									$lastplayingtime = 0;
									$switched--;
								}
							}
							if($audiolog['meta']->state == 'pause') {
								if($time > $audioannotation->duration) {
									$time = $audioannotation->duration;
								}
								$snapshot = array('start'=>$lastplayingtime,'end'=>$time);
								$timesnapshots[] = $snapshot;
								$lastplayingtime = $time;
								$playing = false;
							}
							if($audiolog['meta']->state == 'finished') {
								$snapshot = array('start'=>$lastplayingtime,'end'=>$audioannotation->duration);
								$timesnapshots[] = $snapshot;
								$lastplayingtime = $time;
								$playing = false;
							}
							if($audiolog['meta']->state == 'skip') {
								$lastplayingtime = $time;
								$playing = true;
							}
						}
					}
					foreach($timesnapshots as $timesnapshot) {
						if($timesnapshot['start'] > $timesnapshot['end']) {
							$tmp = $timesnapshot['end'];
							$timesnapshot['end'] = $timesnapshot['start'];
							$timesnapshot['start'] = $tmp;
						}
						$w = ($timesnapshot['end']-$timesnapshot['start'])/$audioannotation->duration * 600;
						$l = $timesnapshot['start']/$audioannotation->duration * 600;
						echo '<div class="audiocell" style="width:'.$w.'px; left:'.$l.'px;"></div>';
					}
					echo '<div class="audioprogress" id="audioprogress_'.$audiocount.'"></div>';
					echo '</div>';
					?>
					<audio controls="controls" id="audioplayer_<?php echo $audiocount; ?>" src="<?php echo $baseURL ?>/assessment/audio/<?php echo $submission['Submission']['id']; ?>/<?php echo $audioannotation->filename; ?>"></audio>
					<?php
					$audiocount++;
					$audioannotationcount++;
				}
			}
			?>
			</dd>
	</dl>
	<?php
	}
	?>
<?php
}
?>

<script type='text/javascript'>
function playPauseAudio(id) {
	var htmlAudio = $('#'+id).get(0);
	if(htmlAudio.paused) {
		htmlAudio.play();
	} else {
		htmlAudio.pause();
	}
}
$(document).ready(function() {
	var audioFiles = $('audio');
	audioFiles.each(function() {
		//console.log($(this));
		var theAudio = this;
		theAudio.addEventListener("timeupdate", updateProgress, false);
		theAudio.addEventListener('ended', function() {
			this.currentTime = 0;
		    this.play();
    	}, false);
	});
});
function updateProgress() {
	var progressid = $(this).attr('id').replace('audioplayer_','audioprogress_');
	var progressBar = $('#'+progressid);
	console.log("PING PING"+$(this).attr('id'));
	var value = 0;
	if (this.currentTime > 0) {
    	value = Math.floor((100 / this.duration) * this.currentTime);
    }
    progressBar.css('width',value*6);
}
function showrawdata(runnum) {
	var pre = $('#rawdata_'+runnum).find('pre');
	var a = $('#rawdata_'+runnum).find('a');
	if(pre.css("display") == "block") {
		pre.fadeOut('fast');
		a.text("Show data");
	} else {
		pre.fadeIn();
		a.text("Hide data");
	}
}
</script>





<style type='text/css'>
	div.annotation_graph_icon {
		background:#f0f0f0;
		border:1px solid #333;
		width:20px;
		height:20px;
		position:absolute;
		text-align:center;
		border-radius:10px;
		font-size:80%;
		color:#333;
		z-index:100;
		cursor:pointer;
		cursor:hand;
	}
	div.run {
		border:1px solid #333;
		padding:10px;
		margin:10px;
	}
	div.graph {
		width:600px;
		height:400px;
		border-left:1px solid #333;
		border-bottom:1px solid #333;
		position:relative;
		margin-left:20px;
		margin-bottom:60px;
		margin-top:10px;
	}
	div.graph div.pagebit {
		border-right:1px solid #333;
		position:absolute;
		top:397px;
		text-align:center;
		margin-top:4px;
		font-size:80%;
	}
	div.graph div.cell {
		background:rgba(0,255,0,0.2);
		position:absolute;
	}
	div.graph div.xaxis {
		position:absolute;
		top:430px;
		width:600px;
		text-align:center;
		font-weight:bold;
	}
	div.graph div.yaxis {
		position:absolute;
		writing-mode:tb-rl;
		-webkit-transform:rotate(90deg);
		-moz-transform:rotate(90deg);
		-o-transform: rotate(90deg);
		width:400px;
		top:200px;
		text-align:center;
		left:-220px;
		font-weight:bold;
	}
	div.audioannotationgraph {
		position:relative;
		width:600px;
		border:1px solid #333;
		height:50px;
		margin-bottom:30px;
	}
	div.audioannotationgraph div.audiocell {
		height:50px;
		background:rgba(0,0,255,0.2);
		position:absolute;
		top:0;
		z-index:10;
	}
	div.audioannotationgraph div.audioprogress {
		height:50px;
		background:rgba(200,200,200,0.2);
		border-right:3px solid red;
		width:0px;
		position:absolute;
		top:0;
		z-index:0;
	}
	div.audioannotationgraph p {
		position:absolute;
		top:52px;
	}
</style>