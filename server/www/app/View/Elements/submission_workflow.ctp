<?php
	$totalsubmissions = sizeOf($submissions);
	$number_uploaded = 0;
	$number_identified = 0;
	$number_assigned = 0;
	$number_marked = 0;
	$number_moderated = 0;
	$number_published = 0;
	foreach($submissions as $submission) {
		$currentactivity = 0;
		foreach($submission['Activity'] as $activity) {
			if($activity['state_id'] > $currentactivity) {
				$currentactivity = $activity['state_id'];
			}
		}
		switch($currentactivity) {
			case 0:
				$number_uploaded++;
				break;
			case 1:
				$number_identified++;
				break;
			case 2:
				$number_assigned++;
				break;
			case 4:
				$number_marked++;
				break;
			case 5:
				$number_moderated++;
				break;
			case 6:
				$number_published++;
				break;
		}
	}
	$workflowstates = array(
		'uploaded'=>array(
			'name'=>'Uploaded',
			'number'=>$number_uploaded,
			'bartext'=>'#333',
		),
		'linked'=>array(
			'name'=>'Identified',
			'number'=>$number_identified,
			'bartext'=>'#333',
		),
		'assigned'=>array(
			'name'=>'Tutor assigned',
			'number'=>$number_assigned,
			'bartext'=>'#333',
		),
		'marked'=>array(
			'name'=>'Marked',
			'number'=>$number_marked,
			'bartext'=>'#ddd',
			'light'=>true,
		),
		'moderated'=>array(
			'name'=>'Moderated',
			'number'=>$number_moderated,
			'bartext'=>'#ddd',
			'light'=>true,
		),
		'published'=>array(
			'name'=>'Published',
			'number'=>$number_published,
			'bartext'=>'#ddd',
			'light'=>true,
		),
	);
?>

<div id='workflowstates'>
	<div id='totalprogress'>
		<p>Submission Status</p>
		<div class="progress">
			<?php
			foreach($workflowstates as $state=>$statedata) {
				$textshadow = 'text-shadow: 0em 0em 2px #fff;';
				if($statedata['bartext'] == '#ddd') {
					$textshadow = 'text-shadow: 0em 0em 2px #000;';	
				}
				$text = $statedata['name'].' ('.$statedata['number'].')';
				if($statedata['number'] < 15) {
					//$text = '';
				}
				echo '<div rel="tooltip" title="'.$text.'" class="bar bar-state-'.$state.'" style="width: '.($statedata['number']/$totalsubmissions*100).'%;"><span style="color:'.$statedata['bartext'].'; '.$textshadow.'">'.$text.'</span></div>';
			}
			?>
    		<div class="bar bar-state-Uploaded" style="width: 35%;"></div>
	    	<div class="bar bar-warning" style="width: 20%;"></div>
    		<div class="bar bar-danger" style="width: 10%;"></div>
    	</div>
    </div>
	<?php
	if((!isset($hidefields) || $hidefields == false) && $totalsubmissions > 0) {
		echo "<div id='states'>";
		$i = 0;
		foreach($workflowstates as $state=>$statedata) {
			$number = 0;
			$number = $statedata['number'];
			$count = 0;
			foreach($workflowstates as $statecount=>$statecountdata) {
				if($count > $i) {
					$number += $statecountdata['number'];
				}
				$count++;
			}
			$progress = ($number/$totalsubmissions*100);
			echo "<div class='state state_$state'><p>".$statedata['name']."</p>";
  			    echo "<div class='progress progress'><div class='bar bar-state-".$state."' style='width: ".$progress."%'></div></div>";
  			echo "<div class='progress_text'>".$number.'/'.$totalsubmissions."</div>";
  			echo "</div>";
  			$i++;
  			if($i < sizeOf($workflowstates)) {
	  			echo "<div class='arrow'></div>";
	  		}
	  	}	
	  	echo '</div>';
	}	
	?>		 	
</div>