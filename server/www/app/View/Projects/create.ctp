<h2>Create Assessment for <?php echo $course['Course']['coursecode']; ?></h2>
<form class="well" method="POST">
	<input type='hidden' name='data[course_id]' value='<?php echo $course['Course']['id']; ?>' />
	<?php
		echo $this->element('formfield',array('label'=>'Assessment Name','placeholder'=>'Assessment Name','id'=>'name'));
		echo $this->element('textareafield',array('label'=>'Assessment Description','id'=>'description'));
		echo $this->element('formfield',array('label'=>'Assessment Creation Date','placeholder'=>date('d-m-Y'),'id'=>'start_date'));
		echo $this->element('formfield',array('label'=>'Assessment Submission Date','placeholder'=>date('d-m-Y'),'id'=>'submission_date'));
		echo $this->element('formfield',array('label'=>'Assessment Return Date','placeholder'=>date('d-m-Y'),'id'=>'end_date'));
		echo $this->element('checkboxfield',array('label'=>'Group Project','placeholder'=>'','id'=>'option_group_project'));
		echo $this->element('checkboxfield',array('label'=>'Collaborative Marking','placeholder'=>'','id'=>'option_multiple_markers'));
		echo $this->element('checkboxfield',array('label'=>'Students can download their feedback','placeholder'=>'','id'=>'option_downloadable'));
		echo $this->element('checkboxfield',array('label'=>'Automatically publish feedback','placeholder'=>'','id'=>'option_autopublish'));
		echo $this->element('checkboxfield',array('label'=>'Do not automatically assign submissions to tutors','placeholder'=>'','id'=>'option_disable_autoassign'));
		echo $this->element('formfield',array('label'=>'Grade Scaling','placeholder'=>'1','id'=>'option_gradescaling','value'=>''));
	?>
  <br />
  <button type="submit" class="btn btn-primary"><i class="icon-plus-sign icon-white"></i> Create Assessment</button>
</form>
<script>
	$(function() {
		$( "#start_date" ).datepicker();
		$( "#start_date" ).datepicker( "option", "dateFormat", 'dd-mm-yy');
		$( "#submission_date" ).datepicker();
		$( "#submission_date" ).datepicker( "option", "dateFormat", 'dd-mm-yy');
		$( "#end_date" ).datepicker();
		$( "#end_date" ).datepicker( "option", "dateFormat", 'dd-mm-yy');
	});
</script>
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