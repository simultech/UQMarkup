<?php
	echo 'Submission ID,Student ID,Marker ID,Publish Time';
	$showall = false;
	$rubricids = array();
	$metrics = array();
	$graded = false;
	$fullgrade = 0;
	foreach($rubrics as $rubric) {
		$rubricdata = json_decode($rubric['meta']);
		$columnname = $rubric['name'];
		if(is_array($rubricdata)) {
			if(isset($rubricdata[0]->grade)) {
				$graded = true;
				$fullgrade += $rubricdata[0]->grade;
			}
			$columnname = 'Rubric ('.$rubric['section'].')';
			$rubricgrades = array();
			$metrics[$rubric['id']] = $rubricdata;
		}
		$rubricids[] = $rubric['id'];
		echo ','.$columnname;
	}
	if($graded) {
		echo ',Final Grade';
		echo ',Final Grade (scaled)';
		echo ',Custom Function Grade';
	}
	echo "\n";
	foreach($submissions as $submission) {
		echo ''.$submission['Submission']['id'].',';
		$student_id = '';
		$marker_id = '';
		$created = 'N/A';
		foreach($submission['Activity'] as $activity) {
			if($activity['state_id'] == 1) {
				$student_id .= $activity['meta'];
			}
			if($activity['state_id'] == 2) {
				$marker_id = $activity['meta'];
			}
			if($activity['state_id'] == 6) {
				$created = $activity['created'];
			}
		}
		$orderedmarks = array();
		if(!empty($submission['marks'])) {
			foreach($submission['marks']->marks as $mark) {
				$orderedmarks[$mark->rubric_id] = $mark->value;
			}
		}
		if(!empty($submission['marks']) || $showall) {
			echo $student_id.','.$marker_id.','.$created;
			$grade = 0;
			foreach($rubricids as $rubricid) {
				$mark = 'N/A';
				if(isset($orderedmarks[$rubricid])) {
					$mark = $orderedmarks[$rubricid];
					if(isset($metrics[$rubricid])) {
						if(isset($metrics[$rubricid][$mark]->grade)) {
							$grade += $metrics[$rubricid][$mark]->grade;
						}
						$mark = $metrics[$rubricid][$mark]->name;
					}
				}
				$mark = str_replace("\n","  ",str_replace(', ', '-', $mark));
				echo ','.$mark;
			}
			if($graded) {
				echo ','.$grade.'';
				$precision = $submission['Project']['option_gradeprecision'];
				$gradescale = $submission['Project']['option_gradescaling'];
				if ($gradescale == 0) {
					$gradescale = 1;
				}
				$rounded = round(intval($grade)/($gradescale), $precision);
				echo ','.$rounded.'';
				if ($submission['Project']['option_gradeusefunction'] == 1) {
					try {
						$grade_input = intval($grade);
						$grade_output = '';
						$code = $submission['Project']['option_gradefunction'];
						$code = str_replace('grade_input', '$grade_input', $code);
						$code = str_replace('grade_output', '$grade_output', $code);
						eval($code);
						echo ','.$grade_output.'';
					} catch (Exception $e) {
						echo ',Could not calculate function.';
					}
				} else {
					echo ',';
				}
			}
			echo "\n";
		} else {
			echo ''.$student_id.','.'N/A'.',';
			foreach($rubricids as $rubricid) {
				echo 'N/A,';
			}
			echo "\n";
		}
	}
?>