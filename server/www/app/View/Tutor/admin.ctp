<?php
	$DISABLE_AUTO_ASSIGN = false;
?>

<h2>Marking tools for <?php echo $course['Course']['coursecode']; ?> (Semester <?php echo $course['Course']['semester'].', 
'.$course['Course']['year']; ?>)</h2>
<?php
	echo $this->Html->link('Class list',array('action'=>'classlist', $course['Course']['uid']),array('target'=>'_blank','class'=>'btn'));
?>
<h3>My Students</h3>
<div class='tutor_section'>
<p>Students that are listed will automatically be assigned to you as they submit assessment.  This only affects future submissions for the course.  You can only reassign students to you, not to another tutor.</p>
<table>
	<thead>
		<tr>
			<th>Student ID</th><th>Title</th><th>First Name</th><th>Last Name</th><th style='width:58px;'>Action</th>
		</tr>
	</thead>
	<?php
		if(empty($students)) {
			echo '<tr><td></td><td></td></tr>';
		}
		foreach($students as $student) {
			echo '<tr><td>'.$student['User']['uqid'].'</td>';
			$name = split(" ",$student['User']['name']);
			if(sizeOf($name) > 2) {
				echo '<td>'.$name[0].'</td>';
				echo '<td>'.$name[1].'</td>';
				unset($name[0]);
				unset($name[1]);
				$name = implode(" ",$name);
				echo '<td>'.$name.'</td>';
			} else {
				echo '<td>&nbsp;</td><td>&nbsp;</td><td>'.$student['User']['name'].'</td>';
			}
			echo '<td>'.$this->Html->link('Remove',array('action'=>'unassignstudent',$course['Course']['id'],$student['User']['id']),array('class'=>'btn btn-danger btn-small'),'Are you sure you want to remove this student from your assigned list?').'</td>';
			echo '</tr>';
		}
	?>
</table>
	<div class='bottomwrap'>
		<?php if(!$DISABLE_AUTO_ASSIGN) { ?>
		<form method='POST'>
		<p><label for='student_uq_id' style='display:inline'>Assign student (UQ username):</label>
		<input type='text' id='student_uq_id' name='uq_id' />
		<input type='hidden' name='course_id' value='<?php echo $course['Course']['id']; ?>' />
		<input type='submit' style='margin-top:-8px' value='Assign' class='btn' /></p>
		</form>
		<p>
		<form method='POST' enctype="multipart/form-data">
		<div class="fileupload fileupload-new" data-provides="fileupload">
			<div class="input-append">
				<label style='display:inline'>Upload student list (<a href='<?php echo $baseURL; ?>/files/tutor_template.csv'>template</a>):</label> 
				<div class="uneditable-input span3"><i class="icon-file fileupload-exists"></i>
					<span class="fileupload-preview"></span>
				</div>
				<span class="btn btn-file"><span class="fileupload-new">Select file</span>
				<span class="fileupload-exists">Change</span>
					<input type='file' name='uq_id_list' />
				</span>
				<a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
				<input type='submit' name="fileupload" style='margin-top:-2px; margin-left:10px;' value='Update List' class='btn' /></p>
			</div>
		</div>
		</form>
		<?php } else { ?>
		<p><strong>Tutor assignment is not currently available, please contact <a href='mailto:uqadekke@uq.edu.au'>uqadekke@uq.edu.au</a></strong></p>
		<?php } ?>
	</div>
</div>
<?php
foreach($projects as $project) {
?>
	<h3>My Marking: <?php echo $project['Project']['name']; ?></h3>
	<div class='tutor_section'>
	<?php echo $this->element('listsubmissions',array('submissions'=>$project['Submission'])); ?>
	<div class='bottomwrap'>
		<form method='POST'>
			<label style='display:inline'>Reassign submission for student (UQ username):</label>
			<input type='text' name='uq_id' />
			<input type='hidden' name='project_id' value='<?php echo $project['Project']['id']; ?>' />
			<input type='submit' style='margin-top:-8px' value='Reassign' class='btn' />
		</form>
	</div>
	</div>
<?php
}
?>


<script type='text/javascript'>
	$(document).ready(function() {
		$.tablesorter.defaults.widgets = ['zebra'];
		$.tablesorter.defaults.sortList = [[1,0]];
		$("table").tablesorter();
	});
</script>

<style>
div.bottomwrap {
	border:1px solid #ccc;
	padding:6px 0 3px 10px;
	background:#eee;
	margin-top:10px;
	border-radius:5px;
}
div.bottomwrap form {
	margin:0;
	margin-bottom:-3px;
}
div.bottomwrap input {
	margin-top:5px;
}
div.tutor_section {
	margin:20px;
}
div.uneditable-input[class*="span"] {
	margin-left:5px;
}
</style>
