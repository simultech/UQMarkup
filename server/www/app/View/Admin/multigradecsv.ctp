Grade Aggregation for "<?php echo $project['Project']['name']; ?>"
<?php
echo 'Student,Marker,';
foreach($rubrics as &$therubric) {
	$therubric['Rubric']['meta'] = json_decode($therubric['Rubric']['meta']);
	echo $therubric['Rubric']['section'].': '.$therubric['Rubric']['name'].',';
}
echo "\n";
foreach($submissions as $submission) {
	$students = array();
	foreach($submission['Activity'] as $activity) {
	    if($activity['state_id'] == 1) {
	    	$students[] = $activity['meta'];
	    }
	}
	$hasmarks = false;
	foreach($submission['marks'] as $marker=>$marks) {
	    if($marker == '__final') {
	    	continue;
	    }
	    echo implode(' ',$students).','.$marker.',';
	    foreach($rubrics as $rubric) {
	    	$output = '';
	    	foreach($marks->marks as $mark) {
	    		$hasmarks = true;
	    		if($mark->rubric_id == $rubric['Rubric']['id']) {
	    			switch($rubric['Rubric']['type']) {
	    				case 'table':
	    					$output = ''.$rubric['Rubric']['meta'][$mark->value]->name.'';	
	    					break;								
	    				default:
	    					$output = ''.$mark->value.'';								
	    					break;
	    			}
	    		}
	    	}
	    	echo $output.',';
	    }
	    echo "\n";
	}
	
	echo implode(' ',$students).','.'Final'.',';
	//print_r($submission['finalmarks']);
	foreach($rubrics as $rubric) {
	    if($hasmarks) {
	    	$gradeval = '';
	    	$existinggrade = false;
	    	if(isset($submission['finalmarks'])) {
	    		foreach($submission['finalmarks'] as $rubricid=>$finalmark) {
	    		    if($rubricid == $rubric['Rubric']['id']) {
	    		    	$existinggrade = $finalmark;
	    			}
	    		}
	    	}
	    	switch($rubric['Rubric']['type']) {
	    		case 'table':
	    			$gradeval = 0;
	    			if($existinggrade) {
	    				$gradeval = $existinggrade;
	    			} else {
	    				$gradeval = '';
	    			}
	    			$done = false;
	    			foreach($rubric['Rubric']['meta'] as $id=>$option) {
	    				$selected = '';
	    				if($id == $gradeval) {
	    					echo $option->name.',';
	    					$done = true;
	    				}
	    			}
	    			if(!$done) {
	    				echo 'U,';
	    			}
	    			break;
	    	}
	    }
	}
	echo "\n";
}
?>