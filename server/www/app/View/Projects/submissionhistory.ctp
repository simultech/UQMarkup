<h2>Submission History for ID: <?php echo $submission['Submission']['id']; ?></h2>
<?php

	function createsort($a, $b) {
	    return strcmp($a["created"], $b["created"]);
	}
	
	function idsort($a, $b) {
	    return strcmp($b["id"],$a["id"]);
	}
	
	function statesort($a, $b) {
	    return strcmp($a["state_id"],$b["state_id"]);
	}

	echo '<p><strong>'.$submission['Submission']['created'].'</strong>: Submission uploaded</p>';
	$activities = $submission['Activity'];
	usort($activities, "idsort");
	usort($activities, "createsort");
	usort($activities, "statesort");
	$i = 0;
	foreach($activities as $activity) {
		$verb = 'to';
		if($activity['state_id'] == 6) {
			$activity['state_id'] = 5;
			$verb = 'by';
		}
		if($activity['state_id'] == 4) {
			$activity['state_id'] = 3;
			$verb = 'by';
		}
		echo '<p><strong>('.$activity['id'].') '.$activity['created'].'</strong>: '.$states[$activity['state_id']+1].' '.$verb.' '.$activity['meta'];
		if($i+2 > sizeOf($activities)) {
			echo $this->Html->link(' Remove',array('controller'=>'projects','action'=>'deleteactivity',$activity['id']),array(),"Are you sure you wish to delete this process? All information regarding this process will be deleted.");
		}
		echo '</p>';
		$i++;
	}
?>

<h2>Rename Submission:</h2>
<form method='POST' action='<?php echo $baseURL; ?>/projects/renameattachment/<?php echo $submission['Attachment'][0]['id']; ?>'>
	<input type='text' name='filename' value='<?php echo substr($submission['Attachment'][0]['title'],0,-4); ?>' />
	<button class='btn'>Rename</button>
</form>
<h2>Reassign Students:</h2>
<form method='POST' action='<?php echo $baseURL; ?>/projects/reassignstudents/<?php echo $submission['Submission']['id']; ?>'>
	<label>Student IDs (seperated by ,):</label>
	<input type='text' name='studentids' value='' />
	<button class='btn'>Reassign</button>
</form>
<h2>Download:</h2>
<?php if(isset($version['Version'])) {?>
<p>Current Version (download): <em><a href='<?php echo $baseURL; ?>/projects/downloadsubmission/<?php echo $submissionhash; ?>'>/<?php echo $submission['Submission']['id']; ?>/<?php echo $version['Version']['path']; ?></a></em></p>
<?php } else { ?>
<p>Submission is not yet marked</p>
<?php } ?>

