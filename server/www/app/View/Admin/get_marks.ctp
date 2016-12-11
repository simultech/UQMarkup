<p><a href='<?php echo $baseURL; ?>/admin/getMarksCsv/<?php echo $project_id; ?>' class='btn'>CSV Download</a></p>
<table>
	<tr>
		<th>Student ID</th>
		<th>Marker ID</th>
<?php
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
			$columnname = 'Rubric <br />('.$rubric['section'].')';
			$rubricgrades = array();
			$metrics[$rubric['id']] = $rubricdata;
		}
		$rubricids[] = $rubric['id'];
		echo '<th>'.$columnname.'</th>';
	}
	if($graded) {
		echo '<th>Final Grade</th>';
	}
?> 
	</tr>
<?php
	foreach($submissions as $submission) {
		$students = array();
		$markers = array();
		foreach($submission['Activity'] as $activity) {
			if($activity['state_id'] == 1) {
				$students[] = $activity['meta'];
			}
			if($activity['state_id'] == 2) {
				$markers[] = $activity['meta'];
			}
		}
		$orderedmarks = array();
		if(!empty($submission['marks'])) {
			foreach($submission['marks'] as $marker=>$marks) {
				if(sizeOf($markers) == 1 && ($marker == 0)) {
					$marker = $markers[0];
				}
				if(isset($marks->marks)) {
					foreach($marks->marks as $mark) {
						$orderedmarks[$marker][$mark->rubric_id] = $mark->value;
					}
				}
			}
		}
		if(!empty($submission['marks']) || $showall) {
			echo '<tr>';
			echo '<td rowspan="'.sizeOf($markers).'">'.implode("<br />",$students).'</td>';
			if(empty($markers)) {
				echo '<td>'.'No Marker'.'</td>';
				foreach($rubricids as $rubricid) {
					$mark = 'N/A';
					echo '<td>'.$mark.'</td>';
				}
			} else {
				foreach($markers as $marker) {
					$grade = 0;
					echo '<td>'.$marker.'</td>';
					foreach($rubricids as $rubricid) {
						$mark = 'N/A';
						if(isset($orderedmarks[$marker][$rubricid])) {
							$mark = $orderedmarks[$marker][$rubricid];
							if(isset($metrics[$rubricid])) {
								$grade += $metrics[$rubricid][$mark]->grade;
								$mark = $metrics[$rubricid][$mark]->name;
							}
						}
						if(strlen($mark) > 50) {
							$mark = '<div class="comments">'.$mark.'</div>';
						}
						echo '<td>'.$mark.'</td>';
					}
					if($graded) {
						echo '<td>'.$grade.' / '.$fullgrade.'</td>';
					}
					echo '</tr>';
				}
			}
		} else {
			/*
echo '<tr>';
			echo '<td>zzz'.$student_id.'</td>';
			echo '<td>'.print_r($submission['marks'],true).'</td>';
			foreach($rubricids as $rubricid) {
				echo '<td>'.print_r($submission['marks'],true).'</td>';
			}
			echo '</tr>';
*/
		}
	}
?>
</table>
<style type='text/css'>
	table {
		border:1px solid #666;
		background:#fff;
	}
	div.comments {
		height:50px;
		overflow:scroll;
	}
</style>