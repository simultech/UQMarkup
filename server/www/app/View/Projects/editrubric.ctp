<h2>Edit Rubric for '<?php echo $project['Project']['name']; ?>'</h2>

<form class="well" method="POST">
	<input type='hidden' name='data[id]' value='<?php echo $rubric['Rubric']['id']; ?>' />
	<input type='hidden' name='data[type]' value='<?php echo $rubric['Rubric']['type']; ?>' />
	<?php
	echo $this->element('formfield',array('label'=>'Rubric Name','placeholder'=>'Rubric Name','id'=>'name','value'=>$rubric['Rubric']['name']));
	echo $this->element('formfield',array('label'=>'Section (optional)','placeholder'=>'Section 1','id'=>'section','value'=>$rubric['Rubric']['section']));
	$order = '';
	if ($rubric['Rubric']['order'] < 99) {
		$order = $rubric['Rubric']['order'];
	}
	echo $this->element('formfield',array('label'=>'Section Order (optional)','placeholder'=>'1','id'=>'order','value'=>$order));
	switch($rubric['Rubric']['type']) {
		case 'table':
			echo '<div id="tabletypes">';
			$i=0;
			foreach($meta as $metadata) {
				echo '<div class="ruberic_cell">';
				echo '<h4>Column '.($i+1).'</h4><p></p>';
				echo '<div class="control-group"><label for="data_meta_'.$i.'_name">Grade:</label><input id="data_meta_'.$i.'_name" type="text" name="data[meta]['.$i.'][name]" value="'.$metadata->name.'" /></div>';
				echo '<div class="control-group"><label for="data_meta_'.$i.'_grade">Mark (optional):</label><input id="data_meta_'.$i.'_grade" type="text" name="data[meta]['.$i.'][grade]" value="'.$metadata->grade.'" /></div>';
				echo '<div class="control-group"><label for="data_meta_'.$i.'_description">Description:</label><textarea style="height:140px;" id="data_meta_'.$i.'_description" name="data[meta]['.$i.'][description]">'.$metadata->description.'</textarea></div>';
				echo '</div>';
				$i++;
			}
			echo '</div>';
			break;
		case 'boolean':
			echo '<input id="data_meta_description" type="text" name="data[meta][description]" />';
			break;
		case 'text':
			echo '<input id="data_meta_description" type="text" name="data[meta][description]" />';
			break;
		case 'number':
			echo '<div class="control-group"><label for="data_meta_description">Description:</label><input id="data_meta_description" type="text" name="data[meta][description]" /></div>';
			echo '<div class="control-group"><label for="data_meta_min">Minimum Value:</label><input id="data_meta_min" type="text" name="data[meta][min]" value="'.$meta->min.'" /></div>';
			echo '<div class="control-group"><label for="data_meta_max">Maximum Value:</label><input id="data_meta_max" type="text" name="data[meta][max]" value="'.$meta->max.'" /></div>';
			echo '<div class="control-group"><label for="data_meta_range">Step:</label><input id="data_meta_range" type="text" name="data[meta][range]" value="'.$meta->range.'" /></div>';
			break;
	}
	?>
	<br />
	<button type="submit" class="btn btn-primary"><i class="icon-edit icon-white"></i> Update Rubric</button>
</form>