<h2>Submission Management for <?php echo $project['Course']['coursecode']; ?>: <?php echo $project['Project']['name']; ?></h2>
<?php
	if($project['Project']['option_autopublish'] == 1) {
		echo '<div style="margin-top:20px; text-align:center;" class="alert alert-error"><h4>Warning: Auto-publish is enabled</h4></div>';
	}
?>
<h3>Submission Workflow</h3>
<?php
	echo $this->element('submission_workflow');
?>
<p><a href="<?php echo $baseURL; ?>/admin/projectstats/<?php echo $project['Project']['id']; ?>" target="_blank" class="btn">View Project Statistics</a> <a href="<?php echo $baseURL; ?>/projects/submissionlist/<?php echo $project['Project']['id']; ?>" target="_blank" class="btn">View Your Assigned Submissions</a> <a href="<?php echo $baseURL; ?>/admin/getMarks/<?php echo $project['Project']['id']; ?>" target="_blank" class="btn">View Rubric Marks</a></p>
<h3>Automated Tasks</h3>
<p><a id='automatedtoggle' href="javascript:toggleautomatedtasks();">Show automated tasks</a></p>
<div id='automated' style='display:none'>
<h5>Identify unidentified submissions with Turn-It-In CSV File (individual)</h5>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/parsewithturnitin/<?php echo $project['Project']['id']; ?>' class="well">
	<input type='file' name='csv' /><input class='btn' type='submit' value='Parse Turn-It-In CSV file' />
</form>
<h5>Identify unidentified submissions from student filename</h5>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/biol1040identify/<?php echo $project['Project']['id']; ?>' class="well">
	<input class='btn btn-danger' type='submit' value='BIOL1040 Identify submissions' />
</form>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/biol1040identifyblackboard/<?php echo $project['Project']['id']; ?>' class="well">
	<input class='btn btn-danger' type='submit' value='BIOL1040 Identify submissions (Blackboard)' />
</form>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/identifysubmissionsfromname/<?php echo $project['Project']['id']; ?>' class="well">
	<input class='btn' type='submit' value='Identify submissions' />
</form>
<h5>Assign tutors to unassigned submissions</h5>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/assigntutors/<?php echo $project['Project']['id']; ?>' class="well">
	<p><a target='_blank' href='<?php echo $baseURL; ?>/projects/getassigntutorscsv/<?php echo $project['Project']['id']; ?>'>Get tutor assignment CSV file</a></p>
	<input type='file' name='csv' /><input class='btn' type='submit' value='Assign tutors' />
</form>
</div>
<h3>Upload Additional Submissions</h3>
<div class='well'>
<p>Webdav Upload URL <a href='<?php echo $baseURL; ?>/webdav.pdf' target='_blank'>(tutorial)</a>: <strong><a href='<?php echo $uploadfolder; ?>' target='_blank'><?php echo $uploadfolder; ?></a></strong></p>
<a href='<?php echo $baseURL; ?>/projects/loadSubmissions/<?php echo $project['Project']['id']; ?>' class='btn'><i class='icon-refresh icon'></i> Check for uploaded submissions</a>
</div>
<h3>Publish Submissions</h3>
<?php
	$tobepublished = 0;
	$tonotbepublished = 0;
	foreach($submissions as $submission) {
		$publishing = false;
		foreach($submission['Activity'] as $activity) {
			if($activity['state_id'] == '4') {
				$tobepublished++;
				$publishing = true;
				break;
			}
		}
		if(!$publishing) {
			$tonotbepublished++;
		}
	}
	if($tobepublished == 1) {
		$tobepublished = '<strong>1</strong> submission';
	} else {
		$tobepublished = '<strong>'.$tobepublished.'</strong> submissions';
	}
	if($tonotbepublished == 1) {
		$tonotbepublished = '<strong>1</strong> submission';
	} else {
		$tonotbepublished = '<strong>'.$tonotbepublished.'</strong> submissions';
	}
?>
<p><?php echo $tobepublished; ?> ready for publishing</p>
<p><?php echo $tonotbepublished; ?> not ready for publishing</p>
<?php
	echo $this->Html->link('Publish submissions',array('controller'=>'projects','action'=>'publishsubmissions',$project['Project']['id']),array('class'=>'btn btn-warning'),"Are you sure you are ready to publish ready submissions?");
?>
<h3>Manage Submissions</h3>
<form method='post'>
<table>
	<thead>
		<tr><th></th><th>Status</th><th>ID</th><th>Filename</th><th>Next Action</th><th>Actions</th></tr>
	</thead>
<?php
	foreach($submissions as $submission) {
		$currentstate = 1;
		$meta = '';
		$nummarkers = 0;
		$nummarked = 0;
		$markerswaiting = array();
		$markersdone = array();
		foreach($submission['Activity'] as $activity) {
			switch($activity['state_id']) {
				case '1':
					if($currentstate < 2) {
						$currentstate = 2;
						$meta = $activity['meta'];
					}
					break;
				case '2':
					if($currentstate < 3) {
						$currentstate = 3;
						$meta = $activity['meta'];
					}
					$markerswaiting[] = $activity['meta'];
					$nummarkers++;
					break;
				case '4':
					if($currentstate < 4) {
						$currentstate = 4;
						$meta = $activity['meta'];
					}
					$nummarked++;
					$markersdone[] = $activity['meta'];
					break;
				case '5';
					if($currentstate < 5) {
						$currentstate = 5;
						//$meta = $activity['meta'];
						//$markersdone[] = $activity['meta'];
						$nummarked++;
					}
					break;
				case '6':
					if($currentstate < 6) {
						$currentstate = 6;
						$meta = $activity['meta'];
					}
					break;
			}
		}
		$markerswaiting = array_diff($markerswaiting,$markersdone);
		$current = 'uploaded';
		$submissionlink = $baseURL.'/submission/'.$submission['Submission']['id'];
		switch($currentstate) {
			case 1:
				$status = 'Uploaded';
				$nexttask = '<td>Link to student/s: <input name="data[identify]['.$submission['Submission']['id'].']" type="text" /> <span class="label label-info">, separated</span></td>';		
				break;
			case 2:
				$current = 'identified';
				$status = 'Identified';
				$nexttask = '<td>Assign to tutor: <input name="data[assign]['.$submission['Submission']['id'].']" type="text" /> <span class="label label-info">, separated</span></td>';
				break;
			case 3:
				$current = 'assigned';
				$status = 'Assigned';
				$nexttask = '<td>Waiting for mark from '.$meta.' (iPad)</td>';
				if($project['Project']['option_multiple_markers'] == 1) {
					$nexttask = '<td>Waiting for mark from '.implode(',',$markerswaiting).' (iPad)</td>';
				}
				break;
			case 4:
				$current = 'marked';
				$status = 'Marked';
				$nexttask = '<td>Marked by '.$meta.', <strong><a target="_blank" href="'.$baseURL.'/assessment/moderation/'.$submission['Submission']['id'].'">moderate</a></strong> or publish?</td>';
				if($project['Project']['option_multiple_markers'] == 1) {
					$nexttask = '<td>('.$nummarked.'/'.$nummarkers.') Received marks from '.implode(',', $markersdone).'.';
					if($nummarked < $nummarkers) {
						$nexttask .= " Waiting for ".implode(',', $markerswaiting).".";
					}
					echo '</td>';
				}
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
			case 5:
				$current = 'moderated';
				$status = 'Moderated';
				$nexttask = '<td>Moderation complete for ('.$meta.') (<a href="'.$baseURL.'/assessment/view_moderation/'.$submission['Submission']['encode_id'].'" target="_blank">moderation</a>)</td>';
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
			case 6:
				$current = 'published';
				$status = 'Published';
				$nexttask = '<td><a href="'.$baseURL.'/admin/submissionstats/'.$submission['Submission']['id'].'" target="_blank">View statistics</a> / <a href="'.$baseURL.'/admin/surveystats/'.$submission['Submission']['id'].'" target="_blank">View survey results</a></td>';
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
		}
		echo '<tr>';
			echo '<td><input type="checkbox" name="data[submissionchecked]['.$submission['Submission']['id'].']" /></td>';
			echo '<td class="state_'.$current.'">'.$status.'</td>';
			echo '<td>'.$submission['Submission']['id'].'</td>';
			$title = str_replace('.pdf','',$submission['Attachment'][0]['title']);
			$maxlength = 29;
			if(strlen($title) > $maxlength) {
				$title = substr($title,0,$maxlength).'...';
			}
			echo '<td><a style="font-size:90%;" href="'.$submissionlink.'" target="_blank">'.$title.'</a></td>';
			echo $nexttask;
			echo '<td>';
				echo '<div class="btn-group">';
					echo '<a title="Submission History" href="'.$baseURL.'/projects/submissionhistory/'.$submission['Submission']['id'].'" class="btn"><i class="icon-time"></i></a>';
					echo '<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">Actions <span class="caret"></span></a>';
					echo '<ul class="dropdown-menu">';
						if($current < '4') {
							echo '<li>'.$this->Html->link('Set to ready for publishing',array('controller'=>'projects','action'=>'submissionfinish',$submission['Submission']['id']),array(),"Are you sure you wish to bypass the marking step?").'</li>';
						}
						echo '<li>'.$this->Html->link('Submission History',array('controller'=>'projects','action'=>'submissionhistory',$submission['Submission']['id']),array()).'</li>';
						echo '<li class="divider"></li>';
						echo '<li>'.$this->Html->link('Delete submission',array('controller'=>'projects','action'=>'deletesubmission',$submission['Submission']['id']),array(),"Are you sure you wish to delete this submission? All information regarding this submission will be deleted.").'</li>';
					echo '</ul>';
				echo '</div>';
			echo '</td>';
		echo '</tr>';
	}
?>
</table>
<br />
<h5>Advanced Options:</h5>
<p><a id='advancedtoggle' href="javascript:toggleadvancedoptions();">Show advanced options</a></p>
<div id='advanced' style='display:none'>
<div class='well'>
	<label>Identify as tutors instead of students: <input type='checkbox' name='usetutors' id='usetutors' /></label>
	<label>Selected - No action: <input type='radio' name='selected_action' value='none' checked='checked' /></label>
	<label>Selected - Set selected as already marked: <input type='radio' name='selected_action' value='marked' /></label>
	<label>Selected - Publish selected: <input type='radio' name='selected_action' value='publish' /></label>
	<!--<label>Selected - Delete submissions: <input type='radio' name='selected_action' value='delete' /></label>-->
</div>
</div>
	<input type='submit' value='Update Submissions' class='btn' />
</form>
<script type='text/javascript'>
	$(document).ready(function() {
		$.tablesorter.defaults.widgets = ['zebra'];
		$.tablesorter.defaults.sortList = [[1,0]];
		$("table").tablesorter({debug: true});
	});
	function toggleautomatedtasks() {
		if($("#automated").css("display") == 'block') {
			$("#automated").fadeOut('fast');
			$("#automatedtoggle").text("Show automated tasks");
		} else {
			$("#automated").fadeIn();
			$("#automatedtoggle").text("Hide automated tasks");
		}
	}
	function toggleadvancedoptions() {
		if($("#advanced").css("display") == 'block') {
			$("#advanced").fadeOut('fast');
			$("#advancedtoggle").text("Show advanced tasks");
		} else {
			$("#advanced").fadeIn();
			$("#advancedtoggle").text("Hide advanced tasks");
		}
	}
</script>