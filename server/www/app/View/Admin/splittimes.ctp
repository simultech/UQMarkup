Submission ID,Marking_Time (seconds),Text (%),Drawing (%),Recording (%)
<?php
	foreach($submissions as $submission) {
		echo $submission['Submission']['id'].',';
		echo $submission['Markingtime'].',';
		if(isset($submission['Annotationcountpercentages']['Text'])) {
			echo round($submission['Annotationcountpercentages']['Text']*100).',';
		} else {
			echo '0,';
		}
		$drawing = 0;
		if(isset($submission['Annotationcountpercentages']['Highlight'])) {
			$drawing += round($submission['Annotationcountpercentages']['Highlight']*100);
		}
		if(isset($submission['Annotationcountpercentages']['Freehand'])) {
			$drawing += round($submission['Annotationcountpercentages']['Freehand']*100);
		}
		echo $drawing.',';
		if(isset($submission['Annotationcountpercentages']['Recording'])) {
			echo round($submission['Annotationcountpercentages']['Recording']*100).',';
		} else {
			echo '0,';
		}
		echo "\n";
	}
?>