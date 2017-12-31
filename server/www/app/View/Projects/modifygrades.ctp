<h2>Modify grades for <?php echo $submission['Project']['name']; ?> (UQ ID: <?php echo $submission['Activity'][0]['meta']; ?>)</h2>
<style>
select, input {
	width: 100%;
}	
</style>
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
		if ($rubric['Rubric']['type'] != 'table') {
			echo '<input type="text" name="'.$rubric['Rubric']['id'].'" value="'.$themark.'" />';	
		} else {
			echo '<select name="'.$rubric['Rubric']['id'].'">';
			$i = 0;
			$found = false;
			foreach(json_decode($rubric['Rubric']['meta']) as $score) {
				$selected = '';
				if ($i == $themark && $themark != '') {
					$selected = 'selected="selected"';
					$found = true;
				}
				echo '<option '.$selected.' value="'.$i.'">'.$score->name.' (Grade '.$score->grade.' - '.$score->description.')'.'</option>';
				$i++;
			}
			if (!$found) {
				echo '<option value="'.$themark.'" selected="selected">-- Not graded --</option>';
			}
			echo '</select>';
			//echo '<input type="text" name="'.$rubric['Rubric']['id'].'" value="'.$themark.'" />';	
		}
	}
?>
	<p><input type='submit' class='btn btn-primary' value='Update grades' /></p>
</form>
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
<p>Please note that this form may not support rich text characters.</p>
<p>Raw marks available here: <?php echo $file; ?></p>