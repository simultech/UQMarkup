<h2>Modify grades </h2>
<p>PLEASE NOTE: THESE ARE DATABASE VALUES, NOT STUDENT FACING VALUES</p>
<p><?php echo $file; ?></p>
<form method='post'>
<?php
	foreach($rubrics as $rubric) {
		echo '<h3>'.$rubric['Rubric']['name'].' ('.$rubric['Rubric']['section'].')</h3>';
		$themark = '';
		foreach($marks->marks as $mark) {
			if($mark->rubric_id == $rubric['Rubric']['id']) {
				$themark = $mark->value;
				break;
			}
		}
		echo '<input type="text" name="'.$rubric['Rubric']['id'].'" value="'.$themark.'" />';
	}
?>
	<p><input type='submit' class='btn btn-primary' value='Update grades' /></p>
</form>