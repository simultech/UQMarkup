<h2>Manage '<?php echo $course['Course']['name']; ?>'</h2>
<?php
	echo $this->element('workflow');
?>

<h3>Assessment Items</h3>
<table>
	<thead><tr><th>Name</th><th width='132'>Actions</th><th>Stage</th><th>Start Date</th><th>Submission Date</th><th>Return Date</th></tr></thead>
<?php
	foreach($projects as $project) {
		echo '<tr>';
		$status = 'Pre-Submission';
		if(sizeOf($project['Rubric']) > 0/* && sizeOf($project['Tag']) > 0*/) {
			$status = 'Marking';
		}
		echo '<td><strong>'.$project['Project']['name'].'</strong></td>';
		echo '<td> ';
			if($status == 'Marking') {
				echo $this->Html->link('Edit',array('controller'=>'projects','action'=>'admin',$project['Project']['id']),array('class'=>'btn')).' ';
				echo $this->Html->link('Manage',array('controller'=>'projects','action'=>'submissionmanager',$project['Project']['id']),array('class'=>'btn btn-primary')).' ';				
			} else {
				echo $this->Html->link('Edit',array('controller'=>'projects','action'=>'admin',$project['Project']['id']),array('class'=>'btn btn-primary')).' ';
				echo $this->Html->link('Manage','#',array('class'=>'btn disabled')).' ';				
			}
		echo '</td>';
		echo '<td>'.$status.'</td>';
		echo '<td>'.date('d-m-Y',strtotime($project['Project']['start_date'])).'</td><td>'.date('d-m-Y',strtotime($project['Project']['submission_date'])).'</td><td>'.date('d-m-Y',strtotime($project['Project']['end_date'])).'</td>';
		
		echo '</tr>';
	}
?>
</table>
<p></p>
<div class='actions'>
	<a href='<?php echo $baseURL; ?>/projects/create/<?php echo $course['Course']['uid']; ?>' class='btn'><i class="icon-plus-sign icon"></i> Create Assessment</a>
</div>
<h3>Edit Course Details</h3>
<form class="well" method="POST">
	<input type='hidden' name='data[id]' value='<?php echo $course['Course']['id']; ?>' />
	<?php
		echo $this->element('formfield',array('label'=>'Course Code','placeholder'=>'Code','id'=>'coursecode','value'=>$course['Course']['coursecode']));
		echo $this->element('formfield',array('label'=>'Shadow Course Code','placeholder'=>'No Shadow Course','id'=>'shadowcode','value'=>$course['Course']['shadowcode']));
		echo $this->element('formfield',array('label'=>'Course Title','placeholder'=>'Title','id'=>'name','value'=>$course['Course']['name']));
		echo $this->element('formfield',array('label'=>'Year','placeholder'=>'Year','id'=>'year','value'=>$course['Course']['year']));
		echo $this->element('formfield',array('label'=>'Semester','placeholder'=>'Semester','id'=>'semester','value'=>$course['Course']['semester']));
	?>
  <br />
  <button type="submit" class="btn btn-primary"><i class="icon-edit icon-white"></i> Update Course</button>
</form>
<h3>Teaching Staff</h3>
<table>
	<thead><tr><th>ID</th><th>Role</th><th>UQ ID</th><th>Name</th><th>Email</th><th>Ethics</th></tr></thead>
	<?php
	foreach($staff as $stafftype=>$stafflist) {
		foreach($stafflist as $staffmember) {
			$terms = 'N/A';
			if($staffmember['User']['termsagreed'] == 1) {
				$terms = 'Yes';
			}
			if($staffmember['User']['termsagreed'] == 2) {
				$terms = 'No';
			}
			echo '<tr><td>'.$staffmember['User']['id'].'</td><td>'.$stafftype.'</td><td>'.$staffmember['User']['uqid'].'</td><td>'.$staffmember['User']['name'].'</td><td>'.$staffmember['User']['email'].'</td><td>'.$terms.'</td></tr>';
		}
	}
	?>
</table>
<p></p>
<div class='actions'>
	<a href='<?php echo $baseURL; ?>/course/managestaff/<?php echo $course['Course']['uid']; ?>' class='btn'><i class="icon-user icon"></i> Manage Teaching Staff</a>
</div>
<h3>Class List</h3>
<?php
if(!isset($students)) {
	echo '<p><a class="btn" href="'.$baseURL.'/course/admin/'.$course['Course']['uid'].'/showclass">Show class list</a>';
	echo ' <a class="btn" href="'.$baseURL.'/course/refreshlist/'.$course['Course']['uid'].'">Refresh</a></p>';
} else {
	echo '<p><a class="btn" href="'.$baseURL.'/course/admin/'.$course['Course']['uid'].'">Hide class list</a>';
	echo ' <a class="btn" href="'.$baseURL.'/course/refreshlist/'.$course['Course']['uid'].'">Refresh</a></p>';
	echo $this->element('classlist');
}
?>
	<form method='POST' enctype="multipart/form-data" action="<?php echo $baseURL; ?>/course/updateassign/<?php echo $course['Course']['uid']; ?>">
		<div class="fileupload fileupload-new" data-provides="fileupload">
			<div class="input-append">
				<label style='display:inline'>Upload tutor-student assignment list (<a href='<?php echo $baseURL; ?>/files/coordinator_tutor_template.csv'>template</a>):</label> 
				<div class="uneditable-input span3"><i class="icon-file fileupload-exists"></i>
					<span class="fileupload-preview"></span>
				</div>
				<span class="btn btn-file"><span class="fileupload-new">Select file</span>
				<span class="fileupload-exists">Change</span>
					<input type='file' name='uq_id_list' />
				</span>
				<a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
				<input type='submit' name="fileupload" style='margin-top:-2px; margin-left:10px;' value='Update List' class='btn' />
			</div>
		</div>
	</form>
<script type='text/javascript'>
	$(document).ready(function() {
		$.tablesorter.defaults.widgets = ['zebra'];
		$.tablesorter.defaults.sortList = [[1,0]];
		$("table").tablesorter({debug: true});
		$("table").each(function() {
			console.log($(this));
		});
	});
</script>