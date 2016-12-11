<?php
	echo 'Student ID,Marking Time (sec),Reading Time (sec),Audio Lengths (sec)';
	$showall = false;
	$rubricids = array();
	$metrics = array();
	echo "\n";
	foreach($submissions as $submission) {
		$audiolengths = array();
		foreach($submission['annotations'] as $annotation) {
			if($annotation->type == 'Recording') {
				if(isset($annotation->duration)) {
					$audiolengths[] = $annotation->duration;
				}
			}
		}
		$student_id = '';
		$marker_id = '';
		$markingtime = $submission['markingtime'];
		$readingtime = $submission['reading_time'];
		foreach($submission['Activity'] as $activity) {
			if($activity['state_id'] == 1) {
				$student_id = $activity['meta'];
			}
			if($activity['state_id'] == 2) {
				$marker_id = $activity['meta'];
			}
		}
		echo $student_id.','./*$marker_id.','.*/$markingtime.','.$readingtime;
		foreach($audiolengths as $audiolength) {
			echo ','.$audiolength;
		}
		echo "\n";
	}
?>