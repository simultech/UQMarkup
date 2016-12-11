<h2>Statistics for <?php echo $project['Project']['name']; ?></h2>
<dl>
	<dt>Number of submissions: </dt><dd><?php echo sizeOf($submissions); ?></dd>
</dl>
<?php

$showrawdata = true;

foreach($submissions as $submission) {
	echo '<h3>Submission: '.$submission['Submission']['id'].'</h3>';
	$sessions = array();
	foreach($submission['Log'] as $interaction) {
		$sessions[$interaction['sessionhash']]['entries'][] = $interaction;
	}
		
	//organise sessions
	foreach($sessions as $sessionid=>&$session) {
		$session['runs'] = array();
		$currentrun = array();
		
		foreach($session['entries'] as &$entry) {
			//closed
			$entry['meta'] = json_decode($entry['meta']);
			if($entry['interaction'] == 'Automatic' && $entry['meta']->state == 'opened') {
				if(!isset($currentrun['data']['Audio'])) {
					$currentrun['data']['Audio'] = array();
				}
				if(!isset($currentrun['data']['Scroll'])) {
					$currentrun['data']['Scroll'] = array();
				}
				if(isset($currentrun['data']['Details'])) {
					$currentrun['pages'] = $currentrun['data']['Details'][0]['meta']->pages;
				}
				if(!empty($currentrun)) {
					$session['runs'][] = $currentrun;
				}
				$currentrun = array();
				$currentrun['starttime'] = $entry['created'];
				$currentrun['user_id'] = $entry['user_id'];
				$currentrun['platform'] = $entry['platform'];
			} else if($entry['interaction'] == 'Automatic' && $entry['meta']->state == 'closed') {
				$currentrun['endtime'] = $entry['created'];
			} else {
				$currentrun['data'][$entry['interaction']][] = $entry;
			}
		}
		unset($session['entries']);
		if(!empty($currentrun)) {
			if(!isset($currentrun['data']['Audio'])) {
				$currentrun['data']['Audio'] = array();
			}
			if(!isset($currentrun['data']['Scroll'])) {
				$currentrun['data']['Scroll'] = array();
			}
			if(isset($currentrun['data']['Details'])) {
				$currentrun['pages'] = $currentrun['data']['Details'][0]['meta']->pages;
			}
			$session['runs'][] = $currentrun;
		}
	}
	
	foreach($sessions as $sessionid=>$sess) {
		echo '<h4>Session: '.$sessionid.' ('.sizeOf($sess['runs']).' run/s)</h4>';
		foreach($sess['runs'] as $run) {
			echo '<div class="run">';
			if(!isset($run['starttime']) || !isset($run['endtime'])) {
				echo '<p>missing data</p>';
				echo '</div>';
				continue;
			}
			if($showrawdata) {
				echo '<pre>';
				print_r($run);
				echo '</pre>';
			}
			echo '<dl>';
				echo '<dt>Total time:</dt><dd>'.(strtotime($run['endtime'])-strtotime($run['starttime'])).' seconds</dd>';
				echo '<dt>Start time:</dt><dd>'.$run['starttime'].'</dd>';
				echo '<dt>End time:</dt><dd>'.$run['endtime'].'</dd>';
				echo '<dt>Audio Annotations:</dt><dd>'.sizeOf($run['data']['Annotations'][0]['meta']->audio).'</dd>';
				echo '<dt>Document Positioning:</dt>';
				echo '<dd>';
				$positions = array();
				$lastmoved = strtotime($run['starttime']);
				$lastposition = 0;
				$pagesize = 0;
				$maxtimediff = 1;
				foreach($run['data']['Scroll'] as $scroll) {
					$pagesize = $scroll['meta']->pagesize;
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
				$audiologs = array();
				foreach($run['data']['Audio'] as $audio) {
					$audiologs[$audio['meta']->annotation][] = array('state'=>$audio['meta']->state,'time'=>$audio['meta']->currentTime);
				}
				foreach($audiologs as $audiolog) {
					
				}
				
				ksort($positions);
				
				//now get ready to display
				$width = 600;
				$height = 400;
				echo '<div class="graph">';
				for($i=1; $i<=$run['pages']; $i++) {
					echo '<div class="pagebit" style="left:'.$width/$run['pages']*($i-1).'px; width:'.$width/$run['pages'].'px; ">Page '.$i.'</div>';
				}
				
				foreach($positions as $position=>$time) {
					$startx = ($position/100)*($width-($width/$run['pages']));
					$endx = $startx + ($width*$pagesize/100);
					$heightperchunk = $height/$maxtimediff;
					$endy = $heightperchunk*($maxtimediff-$time);
					$theheight = $height-$endy;
					
					echo '<div class="cell" style="width:'.($width*$pagesize/100).'px; left:'.$startx.'px; top:'.$endy.'px; height:'.$theheight.'px;"></div>';
				}
				echo '<div class="yaxis">Time</div>';
				echo '<div class="xaxis">Document</div>';
				echo '</div>';
				echo '</dd>';
				echo '<dt>Audio annotations listening</td>';
				echo '<dd>';
				foreach($run['data']['Annotations'][0]['meta']->audio as $audioannotation) {
					echo '<div class="audioannotationgraph">';
						echo '<p>'.$audioannotation->name.'</p>';
						for($i=0; $i<10; $i++) {
							$w = rand(0,100);
							$l = rand(0,200);
							echo '<div class="audiocell" style="width:'.$w.'px; left:'.$l.'px;"></div>';
						}
					echo '</div>';
				}
				echo '</dd>';
			echo '</dl>';
			echo '</div>';
		}
	}
	
}
?>
<style type='text/css'>
	div.run {
		border:1px solid #333;
		padding:10px;
		margin:10px;
	}
	div.graph {
		width:600px;
		height:400px;
		border-left:1px solid red;
		border-bottom:1px solid red;
		position:relative;
		margin-left:20px;
		margin-bottom:60px;
	}
	div.graph div.pagebit {
		border:1px solid red;
		position:absolute;
		top:400px;
		text-align:center;
		margin-top:4px;
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
	}
	div.audioannotationgraph p {
		position:absolute;
		top:52px;
	}
</style>