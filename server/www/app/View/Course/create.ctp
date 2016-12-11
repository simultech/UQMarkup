<h2>Add a new course</h2>
<form class="well" method="POST">
	<?php
		echo $this->element('formfield',array('label'=>'Course Code','placeholder'=>'Code','id'=>'coursecode'));
		echo $this->element('formfield',array('label'=>'Shadow Course Code','placeholder'=>'No Shadow Course','id'=>'shadowcode'));
		echo $this->element('formfield',array('label'=>'Course Title','placeholder'=>'Title','id'=>'name'));
		echo $this->element('formfield',array('label'=>'Year','placeholder'=>'Year','id'=>'year','value'=>date('Y')));
		$sem = 1;
		if(date('m') > 6) {
			$sem = 2;
		}
		echo $this->element('formfield',array('label'=>'Semester','placeholder'=>'Semester','id'=>'semester','value'=>$sem));
	?>
  <br />
  <button type="submit" class="btn btn-primary"><i class="icon-plus-sign icon-white"></i> Add Course</button>
</form>