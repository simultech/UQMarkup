<h2>Grade Aggregation for "<?php echo $project['Project']['name']; ?>"</h2>
<form method='post'>
<div id='multigrade'>
<?php
foreach($rubrics as &$therubric) {
	$therubric['Rubric']['meta'] = json_decode($therubric['Rubric']['meta']);
}

foreach($submissions as $submission) {?>
	<?php
		$students = array();
		foreach($submission['Activity'] as $activity) {
			if($activity['state_id'] == 1) {
				$students[] = $activity['meta'];
			}
		}
	?>
	<table>
		<tr><th class="students" style='text-align:left;' colspan='<?php echo sizeOf($rubrics)+1; ?>'>Student/s: <?php echo implode(',',$students); ?></th></tr>
		<tr>
		<th>Marker</th>
		<?php
			$agggrades = array();
			foreach($rubrics as $arubric) {
				$style = '';
				if($arubric['Rubric']['type'] == 'text') {
					$style = 'style="width:100px;"';
				}
				echo '<th '.$style.'>('.$arubric['Rubric']['id'].') '.$arubric['Rubric']['section'].': '.$arubric['Rubric']['name'].'</th>';
				$agggrades[$arubric['Rubric']['id']] = array();
			}
		?>
		</tr>
		<?php
			$hasmarks = false;
			foreach($submission['marks'] as $marker=>$marks) {
				if($marker == '__final') {
					continue;
				}
				echo '<tr><td><strong>'.$marker.'</strong></td>';
				foreach($rubrics as $rubric) {
					$output = '<td>&nbsp;</td>';
					foreach($marks->marks as $mark) {
						$hasmarks = true;
						if($mark->rubric_id == $rubric['Rubric']['id']) {
							switch($rubric['Rubric']['type']) {
								case 'table':
									$output = '<td>'.$rubric['Rubric']['meta'][$mark->value]->name.'</td>';	
									break;								
								default:
									$output = '<td>'.$mark->value.'</td>';								
									break;
							}
							$agggrades[$rubric['Rubric']['id']][] = $mark->value;
						}
					}
					echo $output;
				}
				echo '</tr>';
			}
		?>
		<tr>
		<th>Final</th>
		<?php
			//print_r($submission['finalmarks']);
			foreach($rubrics as $rubric) {
				if($hasmarks) {
					$gradeval = '';
					echo '<td>';
					$existinggrade = false;
					if(isset($submission['finalmarks'])) {
						foreach($submission['finalmarks'] as $rubricid=>$finalmark) {
						    if($rubricid == $rubric['Rubric']['id']) {
						    	$existinggrade = $finalmark;
							}
						}
					}
					switch($rubric['Rubric']['type']) {
						case 'table':
							$gradeval = 0;
							if($existinggrade) {
								$gradeval = $existinggrade;
							} else {
								if(sizeOf($agggrades[$rubric['Rubric']['id']]) > 0) {
									foreach($agggrades[$rubric['Rubric']['id']] as $grade) {
										$gradeval += $grade;
									}
									$gradeval = round($gradeval/sizeOf($agggrades[$rubric['Rubric']['id']]));
									//echo '<input type="text" class="input-small" value="'.$gradeval.'" />';
								}
							}
							echo '<select class="input-small" name="data['.$submission['Submission']['id'].']['.$rubric['Rubric']['id'].']">';
							foreach($rubric['Rubric']['meta'] as $id=>$option) {
								$selected = '';
								if($id == $gradeval) {
									$selected = "selected='selected'";
								}
								echo '<option '.$selected.' value="'.$id.'">'.$option->name.': </option>';
							}
							echo '</select>';
							break;
						case 'number':
							$gradeval = 0;
							if($existinggrade) {
								$gradeval = $existinggrade;
							} else {
								if(sizeOf($agggrades[$rubric['Rubric']['id']]) > 0) {
								    foreach($agggrades[$rubric['Rubric']['id']] as $grade) {
								    	$gradeval += $grade;
								    }
								    $gradeval = round($gradeval/sizeOf($agggrades[$rubric['Rubric']['id']]));
								}
							}
							echo '<input name="data['.$submission['Submission']['id'].']['.$rubric['Rubric']['id'].']" type="text" class="input-small" value="'.$gradeval.'" />';
							break;
						case 'text':
							$gradeval = '';
							if($existinggrade) {
								$gradeval = $existinggrade;
							} else {
								foreach($agggrades[$rubric['Rubric']['id']] as $grade) {
									$gradeval .= $grade."\n\n";
								}
							}
							echo '<textarea name="data['.$submission['Submission']['id'].']['.$rubric['Rubric']['id'].']">'.$gradeval.'</textarea>';
							break;
						case 'boolean':
							$gradeval = 'No';
							foreach($agggrades[$rubric['Rubric']['id']] as $grade) {
								if($grade == 'true') {
									$gradeval = 'Yes';
								}
							}
							echo '<input name="data['.$submission['Submission']['id'].']['.$rubric['Rubric']['id'].']" type="text" class="input-small" value="'.$gradeval.'" />';
							break;
						default:
							$gradeval = 'unknown type';
							echo '<input name="data['.$submission['Submission']['id'].']['.$rubric['Rubric']['id'].']" type="text" class="input-small" value="'.$gradeval.'" />';
							break;
					}
					echo '</td>';
				}
			}
		?>
		</tr>
	</table>
<?php
}
?>
</div>
<div id='footer_spacer'></div>
<p style='text-align:center;'>
<input type='submit' class='btn btn-primary large' value='Update Grades' />
</p>
</form>
<style type='text/css'>
	table {
		background:#fff;
		margin-bottom:20px;
		padding:10px;
	}
	tr td, tr th {
		background:#fff;
	}
	tr th.students {
		background:#60419d;
		color:#fff;
	}
	div#multigrade {
		overflow:scroll;
		height:640px;
		border-top:3px solid #60419d;
		border-bottom:3px solid #60419d;
		width:100%;
		position:absolute;
		left:0;
		background:#eee;
	}
	div#footer_spacer {
		height:658px;
	}
</style>