<h2>Manage <?php echo $project['Course']['coursecode']; ?> Assessment: <?php echo $project['Project']['name']; ?></h2>
<h3>Project Configuration</h3>
<?php
	echo $this->element('workflow');
?>
<!--<h3>2. Markup Workflow (<?php echo sizeOf($project['Submission']); ?> Submissions)</h3>
<?php
	$workflowdata = array('workflow'=>$workflow_iterative,'incompleteclass'=>'step_incomplete');
	if(sizeOf($project['Submission']) > 0) {
		$workflowdata['incompleteclass'] = '';
	}
	echo $this->element('workflow',$workflowdata);
?>
-->
<h3>Workflow Details</h3>
<?php
	echo $this->element('submission_workflow',array('hidefields'=>true));
?>
<p><a href='<?php echo $baseURL; ?>/projects/submissionmanager/<?php echo $project['Project']['id']; ?>' class='btn-large btn-primary'><i class='icon-list icon-white'></i> Submission Workflow Manager</a></p>
<h3>Edit Assessment Details</h3>
<form class="well" method="POST">
	<input type='hidden' name='data[id]' value='<?php echo $project['Project']['id']; ?>' />
	<input type='hidden' name='data[course_id]' value='<?php echo $project['Course']['id']; ?>' />
	<?php
		echo $this->element('formfield',array('label'=>'Assessment Name','placeholder'=>'Assessment Name','id'=>'name','value'=>$project['Project']['name']));
		echo $this->element('textareafield',array('label'=>'Assessment Description','id'=>'description','value'=>$project['Project']['description']));
		echo $this->element('formfield',array('label'=>'Assessment Creation Date','placeholder'=>date('d-m-Y'),'id'=>'start_date','value'=>$project['Project']['start_date']));
		echo $this->element('formfield',array('label'=>'Assessment Submission Date','placeholder'=>date('d-m-Y'),'id'=>'submission_date','value'=>$project['Project']['submission_date']));
		echo $this->element('formfield',array('label'=>'Assessment Return Date','placeholder'=>date('d-m-Y'),'id'=>'end_date','value'=>$project['Project']['end_date']));
		echo $this->element('checkboxfield',array('label'=>'Group Project','placeholder'=>'','id'=>'option_group_project','value'=>$project['Project']['option_group_project']));
		echo $this->element('checkboxfield',array('label'=>'Collaborative Marking','placeholder'=>'','id'=>'option_multiple_markers','value'=>$project['Project']['option_multiple_markers']));
		echo $this->element('checkboxfield',array('label'=>'Students can download their feedback','placeholder'=>'','id'=>'option_downloadable','value'=>$project['Project']['option_downloadable']));
		echo $this->element('checkboxfield',array('label'=>'Automatically publish feedback','placeholder'=>'','id'=>'option_autopublish','value'=>$project['Project']['option_autopublish']));
		echo $this->element('checkboxfield',array('label'=>'Do not automatically assign submissions to tutors','placeholder'=>'','id'=>'option_disable_autoassign','value'=>$project['Project']['option_disable_autoassign']));
		echo '<h3>Grading Options</h3>';
		echo $this->element('checkboxfield',array('label'=>'Grade visible to student','placeholder'=>'','id'=>'option_gradeshown','value'=>$project['Project']['option_gradeshown']));
		echo '<p>There are two options with grade display, either by using the grade scaling, or by using a custom grade function</p>';
		echo '<h4>A: Grading Scaling Options</h4>';
		echo '<p><em>Grade scaling will dynamically modify the display of the final grade into something different than the sum of all rubrics.  <br /><br />Example: <code>47/60 => 3.13/4</code> (value 15 for grade scaling and 2 for precision)
		<br />Example: <code>62/100 => 6.2/10</code> (value 10 for grade scaling and 1 for precision)
		<br />Example: <code>77/100 => 8/10</code> (value 10 for grade scaling and 0 for precision)</em></p>';
		echo $this->element('formfield',array('label'=>'Grade Scaling (round divide final grade by this number, use 1 for no scaling)','placeholder'=>'1','id'=>'option_gradescaling','value'=>$project['Project']['option_gradescaling']));
		echo $this->element('formfield',array('label'=>'Grade Precision (number of decimal places for grade scaling, default 0)','placeholder'=>'0','id'=>'option_gradeprecision','value'=>$project['Project']['option_gradeprecision']));
		echo '<h4>B: Custom Grading Function</h4>';
		echo '<p><em>A custom grading function (written in Javascript) will replace the default output of the grade scaling.  The custom grade scaler takes "<code>grade_input</code>" (the summed grade) and sets "<code>grade_output</code>".  </em><br /><br />Example:<br /><code>if (grade_input < 3) {<br />
  grade_output = "C";<br />
} else if (grade_input < 7) {<br />
  grade_output = "B";<br />
} else {<br />
  grade_output = "A";<br />
}</code></p>';
		echo $this->element('checkboxfield',array('label'=>'Use custom grade function','placeholder'=>'','id'=>'option_gradeusefunction','value'=>$project['Project']['option_gradeusefunction']));
		echo $this->element('textareafield',array('label'=>'Custom grading function','placeholder'=>'','id'=>'option_gradefunction','value'=>$project['Project']['option_gradefunction']));
	?>
  <br />
  <button type="submit" class="btn btn-primary"><i class="icon-edit icon-white"></i> Update Assessment</button>
</form>
<script>
	$(function() {
		$( "#start_date" ).datepicker();
		$( "#start_date" ).datepicker( "option", "dateFormat", 'dd-mm-yy');
		$( "#start_date" ).datepicker('setDate', new Date('<?php echo date('Y-m-d',strtotime($project['Project']['start_date'])); ?>'));
		$( "#submission_date" ).datepicker();
		$( "#submission_date" ).datepicker( "option", "dateFormat", 'dd-mm-yy');
		$( "#submission_date" ).datepicker('setDate', new Date('<?php echo date('Y-m-d',strtotime($project['Project']['submission_date'])); ?>'));
		$( "#end_date" ).datepicker({defaultDate:null});
		$( "#end_date" ).datepicker( "option", "dateFormat", 'dd-mm-yy');
		$( "#end_date" ).datepicker('setDate', new Date('<?php echo date('Y-m-d',strtotime($project['Project']['end_date'])); ?>'));
	});
</script>
<h3>Rubrics</h3>
<?php echo $this->element('rubriclayout'); ?>
<p></p>
<div class='actions'>
	<a href='<?php echo $baseURL; ?>/projects/rubrics/<?php echo $project['Project']['id']; ?>' class='btn'><i class="icon-th-list icon"></i> Manage Rubrics</a>
</div>
<style type='text/css'>
	div#status_holder {
		border:1px solid #333; 
		padding:10px; 
		border-radius:5px; 
		-moz-border-radius:5px; 
		-webkit-border-radius:5px; 
		background:#556;
	}
</style>
<script type='text/javascript'>
function checkboxchanged(checkbox,field) {
	var box = $(field);
	if($(checkbox).attr("checked")) {
		console.log();
		box.val("1");
	} else {
		box.val("0");
	}
}
</script>