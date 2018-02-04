<h2>Manage Rubrics</h2>
<link rel='stylesheet' type='text/css' href='<?php echo $baseURL; ?>/css/jquery/jquery.colorpicker.css' />
<script type='text/javascript' src='<?php echo $baseURL; ?>/js/jquery.colorpicker.js'></script>
<h3>Rubrics for '<?php echo $project['Project']['name']; ?>'</h3>
<?php echo $this->element('rubriclayout',array('editrubrics'=>true)); ?>
<h3>Import Rubrics '<?php echo $project['Project']['name']; ?>'</h3>
<p><a href='<?php echo $baseURL; ?>/files/rubrics_template.csv'>Download this template</a>, edit and upload here:</p>
<form class="well" method="POST" enctype="multipart/form-data">
	<input name='rubric' type='file' />
	<button name='importrubric' class="btn btn-primary"><i class="icon-plus icon-white"></i> Import Rubrics</button>
</form>
<form class="well" method="POST" action="<?php echo $baseURL; ?>/projects/duplicaterubrics/<?php echo $project['Project']['id']; ?>">
<p>Duplicate from other projects: </p>
	<select name="othercourse">
	<?php
		foreach($othercourses as $othercourse) {
			foreach($othercourse['Project'] as $otherproject) {
				echo '<option value="'.$otherproject['id'].'">'.$othercourse['Course']['uid'].' - '.$otherproject['name'].'</option>';	
			}
		}
	?>
	</select>
	<input type='submit' class="btn" />
</form>
<h3>Add Ruberic Row</h3>
<form class="well" method="POST">
	<input type='hidden' name='data[project_id]' value='<?php echo $project['Project']['id']; ?>' />
	<?php
		echo $this->element('formfield',array('label'=>'Rubric Name','placeholder'=>'Rubric Name','id'=>'name'));
		echo $this->element('formfield',array('label'=>'Section (optional)','placeholder'=>'Section 1','id'=>'section'));
		echo $this->element('formfield',array('label'=>'Section Order (optional)','placeholder'=>'99','id'=>'order'));
		echo $this->element('rubrictypes');
	?>
  <br />
  <button disabled id='createrubric' type="submit" class="btn btn-primary"><i class="icon-circle-arrow-up icon-white"></i> Create Rubric</button>
</form>