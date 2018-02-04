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
<h3>Upload Submissions</h3>
<div class='well'>
    <p>Webdav Upload URL <a href='<?php echo $baseURL; ?>/webdav.pdf' target='_blank'>(tutorial)</a>: <strong><a href='<?php echo $uploadfolder; ?>' target='_blank'><?php echo $uploadfolder; ?></a></strong></p>
    <a href='<?php echo $baseURL; ?>/projects/loadSubmissions/<?php echo $project['Project']['id']; ?>' class='btn'><i class='icon-refresh icon'></i> Check for uploaded submissions</a>
</div>
<h3>Statistics &amp; Downloads</h3>
<div style="font-size:60%;">
<p><a href="<?php echo $baseURL; ?>/admin/projectstats/<?php echo $project['Project']['id']; ?>" target="_blank" class="btn btn-small">View Project Statistics</a>
<a href="<?php echo $baseURL; ?>/admin/visualisation/<?php echo $project['Project']['id']; ?>" target="_blank" class="btn btn-small">View Analytics</a>
<a href="<?php echo $baseURL; ?>/surveys/results/<?php echo $project['Project']['id']; ?>" target="_blank" class="btn btn-small">Survey Responses</a>
<a href="<?php echo $baseURL; ?>/projects/submissionlist/<?php echo $project['Project']['id']; ?>" target="_blank" class="btn btn-small">View Your Assigned Submissions</a>
<a href="<?php echo $baseURL; ?>/admin/getMarksCsv/<?php echo $project['Project']['id']; ?>" target="_blank" class="btn btn-small">View Rubric Marks (CSV)</a>
<a href="<?php echo $baseURL; ?>/projects/sbms_students/<?php echo $project['Project']['id']; ?>" target="_blank" class="btn btn-small">Submission List (CSV)</a>
<?php
if($project['Project']['option_multiple_markers'] == 1) {
?>
 <a href="<?php echo $baseURL; ?>/admin/multigrade/<?php echo $project['Project']['id']; ?>" target="_blank" class="btn btn-warning btn-small">Perform Grade Aggregation</a>
<?php
}
?>
</p>
</div>
<h3>Student Identification</h3>
<h5>Identify unidentified submissions with Turn-It-In CSV File (individual)</h5>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/parsewithturnitin/<?php echo $project['Project']['id']; ?>' class="well">
    P Group (eg. P4): <input type='text' name='prepend' />
    CSV File: <input type='file' name='csv' /><input class='btn' type='submit' value='Parse Turn-It-In CSV file' />
</form>
<?php if ($project['Project']['option_group_project'] == 1) { ?>
<h3>Group Identification</h3>
<h5>Identify unidentified submissions with Group CSV (<a href='<?php echo $baseURL; ?>/groupidentification.csv' target='_blank'>download template here</a>)</h5>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/groupidentification/<?php echo $project['Project']['id']; ?>' class="well">
    CSV File: <input type='file' name='csv' /><input class='btn' type='submit' value='Parse Group Identification CSV file' />
</form>
<h5>TURNITIN - Identify unidentified submissions with Group CSV (<a href='<?php echo $baseURL; ?>/tiigroupidentification.csv' target='_blank'>download template here</a>)</h5>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/tiigroupidentification/<?php echo $project['Project']['id']; ?>' class="well">
    CSV File: <input type='file' name='csv' /><input class='btn' type='submit' value='Parse TII Group Identification CSV file' />
</form>
<?php } ?>
<p><a id='automatedtoggle' href="javascript:toggleautomatedtasks();">Show additional tasks</a></p>
<div id='automated' style='display:none'>
<!--<h5>Add P group to existing Turn-It-In CSV File (individual)</h5>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/addpgroup/<?php echo $project['Project']['id']; ?>' class="well">
	<input type='text' name='pgroup' />
	<input type='file' name='csv' /><input class='btn' type='submit' value='Add P Group Prepend' />
</form>-->
<h5>Identify unidentified submissions from student filename</h5>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/biol1040identify/<?php echo $project['Project']['id']; ?>' class="well">
	<input class='btn btn-danger' type='submit' value='BIOL1040 Identify submissions' />
</form>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/biol1040identifyblackboard/<?php echo $project['Project']['id']; ?>' class="well">
	<input class='btn btn-danger' type='submit' value='BIOL1040 Identify submissions (Blackboard)' />
</form>
<form method='post' enctype="multipart/form-data" action='<?php echo $baseURL; ?>/projects/checkfilelistblackboard/<?php echo $project['Project']['id']; ?>' class="well">
	<textarea name='data[text]'></textarea>
	<input class='btn btn-info' type='submit' value='Check blackboard file list' />
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
<p><?php echo $tobepublished; ?> ready for publishing, <?php echo $tonotbepublished; ?> not ready for publishing</p>
<?php
	echo $this->Html->link('Publish submissions',array('controller'=>'projects','action'=>'publishsubmissions',$project['Project']['id']),array('class'=>'btn btn-warning'),"Are you sure you are ready to publish ready submissions?");
?>
<h3>Manage Submissions</h3>
<script src="<?php echo $baseURL; ?>/js/angular/controllers/userlookupController.js"></script>
<div id="studentLookup">
	<span id="studentLookupHelper">Quickly find the names or IDs of course students and staff</span>
<div ng-app="uqmarkupApp">
	<userlookup course="<?php echo $project['Project']['course_id']; ?>"></userlookup>
</div>
</div>
<script type='text/javascript'>
	var toggleType = 'or';
	function toggleFilterType(type) {
		toggleType = type;
		if (toggleType == 'or') {
			$('#type_or').addClass('btn-primary');
			$('#type_and').removeClass('btn-primary');
		} else {
			$('#type_or').removeClass('btn-primary');
			$('#type_and').addClass('btn-primary');
		}
		updateFilter();
	}
	$(document).ready(function() {
		toggleFilterType('or');	
	});
	function selectallsubmissions() {
		var allChecked = true;
		$('.submissionrow:not(.hidden)').each(function(i,e) {
			if (!$(e).find('.subcheck').prop('checked')) {
				allChecked = false;
			}
		});
		$('.submissionrow:not(.hidden)').each(function(i,e) {
			$(e).find('.subcheck').prop('checked', !allChecked);
			if (allChecked) {
				$('#selectall').html('Select Visible Submissions');
			} else {
				$('#selectall').html('Deselect Visible Submissions');
			}
		});
	}
	function updateFilter() {
		var newFilter = $('#filter').val().toLowerCase();
		if (newFilter === '') {
			$('.submissionrow').removeClass('hidden');
		} else {
			var allChecked = true;
			newFilter = newFilter.split(' ');
			$('.submissionrow').each(function(i,e) {
				var el = $(e);
				el.addClass('hidden');
				var data = el.data('filter');
				var stillValid = true;
				for (var i=0; i<newFilter.length; i++) {
					if (toggleType == 'or') {
						if (data.indexOf(newFilter[i]) > -1) {
							el.removeClass('hidden');
							if (!el.find('.subcheck').prop('checked')) {
								allChecked = false;
							}
							break;
						}
					} else {
						if (data.indexOf(newFilter[i]) == -1) {
							stillValid = false;
						}
					}
				}
				if (stillValid) {
					el.removeClass('hidden');
					if (!el.find('.subcheck').prop('checked')) {
						allChecked = false;
					}
				}
			});
			if (allChecked) {
				$('#selectall').html('Deselect Visible Submissions');
			} else {
				$('#selectall').html('Select Visible Submissions');
			}
		}
	}
	function forceLower(strInput) {
		strInput.value=strInput.value.toLowerCase();
	}
	function checkBulkSubmit() {
		if($('#bulk_delete_radio').prop('checked')) {
			return confirm('Are you sure you want to bulk delete?  This operation is not reversable!');
		}
		return true;
	}
</script>
<a class='btn btn-default' style='float:right' id='selectall' onclick="selectallsubmissions();">Select Visible Submissions</a>
<div id='filterbox'>
	<strong style="float:left; margin-right: 5px; margin-top:5px;">Live filter:</strong>
	<input type="text" id="filter" style="width:300px; float:left; " onkeyup="updateFilter()" /></label>
	<div class="btn-group" style="float:left">
		<a class='btn btn-default' id='type_or' onclick="toggleFilterType('or');">Any terms</a>
		<a class='btn btn-default' id='type_and' onclick="toggleFilterType('and');">All terms</a>
	</div>
</div>
<form method='post' onsubmit="return checkBulkSubmit();">
<table>
	<thead>
		<tr><th></th><th>Status</th><th>ID</th><th>Filename</th><th>Next Action</th><th style="width:100px;">Actions</th></tr>
	</thead>
<?php
	foreach($submissions as $submission) {
		$currentstate = 1;
		$meta = '';
		$nummarkers = 0;
		$nummarked = 0;
		$markerswaiting = array();
		$markersdone = array();
		$identifieds = array();
		$moderation = false;
		foreach($submission['Activity'] as $activity) {
			switch($activity['state_id']) {
				case '1':
					if($currentstate < 2) {
						$currentstate = 2;
						$meta = $activity['meta'];
					}
					$identifieds[] = $activity['meta'];
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
					if(!in_array($activity['meta'], $markersdone)) {
						$nummarked++;
						$markersdone[] = $activity['meta'];
					}
					break;
				case '5';
					if($currentstate < 5) {
						$currentstate = 5;
						//$meta = $activity['meta'];
						//$markersdone[] = $activity['meta'];
						$nummarked++;
						$moderation = true;
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
				$nexttask = '<td>Link to student/s: <input name="data[identify]['.$submission['Submission']['id'].']" type="text" onkeyup="return forceLower(this);" /> <span class="label label-info">, separated</span></td>';		
				break;
			case 2:
				$current = 'identified';
				$status = 'Identified';
				$nexttask = '<td>Assign to tutor: <input name="data[assign]['.$submission['Submission']['id'].']" type="text" onkeyup="return forceLower(this);" /> <span class="label label-info">, separated</span></td>';
				break;
			case 3:
				$current = 'assigned';
				$status = 'Assigned';
				$nexttask = '<td>Waiting for mark from '.$meta.' (iPad) - <em>(ID: '.implode(',',$identifieds).')</em></td>';
				if($project['Project']['option_multiple_markers'] == 1) {
					$nexttask = '<td>Waiting for mark from '.implode(',',$markerswaiting).' (iPad) - (ID: '.implode(',',$identifieds).')</td>';
				}
				break;
			case 4:
				$current = 'marked';
				$status = 'Marked';
				$nexttask = '<td>Marked by '.$meta.', <strong><a target="_blank" href="'.$baseURL.'/assessment/moderation/'.$submission['Submission']['id'].'">moderate</a></strong>/publish? - <em>(ID: '.implode(',',$identifieds).')</em></td>';
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
				$nexttask = '<td><a href="'.$baseURL.'/admin/submissionstats/'.$submission['Submission']['id'].'" target="_blank">Stats</a> / <a href="'.$baseURL.'/admin/surveystats/'.$submission['Submission']['id'].'" target="_blank">Survey</a> (by '.$markersdone[0].') <em>(ID: '.implode(',',$identifieds).')</em>';
				if($moderation) {
					$nexttask .= ' (<a href="'.$baseURL.'/assessment/view_moderation/'.$submission['Submission']['encode_id'].'" target="_blank">moderation</a>)';
				}
				$nexttask .= "</td>";
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
		}
		$title = str_replace('.pdf','',$submission['Attachment'][0]['title']);
		$fulltitle = $title;
		$maxlength = 29;
		if(strlen($title) > $maxlength) {
			$title = substr($title,0,$maxlength).'...';
		}
		$filterdata = $status . ' ' . $submission['Submission']['id'] . ' ' . $nexttask . ' ' . $fulltitle;
		$filterdata = str_replace('"','', strtolower($filterdata));
		echo '<tr class="submissionrow" data-filter="'.$filterdata.'">';
			echo '<td><input type="checkbox" class="subcheck" name="data[submissionchecked]['.$submission['Submission']['id'].']" /></td>';
			echo '<td class="state_'.$current.'">'.$status.'</td>';
			echo '<td>'.$submission['Submission']['id'].'</td>';
			echo '<td><a style="font-size:90%;" href="'.$submissionlink.'" target="_blank" title="'.$fulltitle.'">'.$title.'</a></td>';
			echo $nexttask;
			echo '<td>';
				echo '<div class="btn-group">';
					echo '<a title="Submission History" href="'.$baseURL.'/projects/submissionhistory/'.$submission['Submission']['id'].'" class="btn"><i class="icon-time"></i></a>';
					if($current == 'marked') {
						echo '<a title="Change Marks" target="_blank" href="'.$baseURL.'/projects/modifygrades/'.$submission['Submission']['id'].'" class="btn"><i class="icon-tasks"></i></a>';
					} else {
						echo '<a disabled title="Change Marks" class="btn"><i class="icon-tasks"></i></a>';
					}
					echo $this->Html->link('<i class="icon-remove"></i>',array('controller'=>'projects','action'=>'deletesubmission',$submission['Submission']['id']),array('class'=>'btn btn-danger', 'style'=> 'color:white', 'escape' => false),"Are you sure you wish to delete this submission? All information regarding this submission will be deleted.");
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
<label>Associate submissions to markers instead of students: <input type='checkbox' name='usetutors' id='usetutors' /></label>
<div class='well'>
	<label>Selected - No action: <input type='radio' name='selected_action' value='none' checked='checked' /></label>
	<label>Selected - Set selected as already marked: <input type='radio' name='selected_action' value='marked' /></label>
	<label>Selected - Publish selected: <input type='radio' name='selected_action' value='publish' /></label>
	<label>Selected - Delete submissions: <input type='radio' id='bulk_delete_radio' name='selected_action' value='delete' /></label>
</div>
</div>
	<input type='submit' value='Update Submissions' class='btn' />
</form>
<script type='text/javascript'>
	$(document).ready(function() {
		$.tablesorter.defaults.widgets = ['zebra'];
		$.tablesorter.defaults.sortList = [[1,0]];
		$("table").tablesorter();
	});
	function toggleautomatedtasks() {
		if($("#automated").css("display") == 'block') {
			$("#automated").fadeOut('fast');
			$("#automatedtoggle").text("Show additional tasks");
		} else {
			$("#automated").fadeIn();
			$("#automatedtoggle").text("Hide additional tasks");
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