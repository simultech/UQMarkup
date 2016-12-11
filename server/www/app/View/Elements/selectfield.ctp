<?php
	if(!isset($value)) {
		$value = '';
	}
	if(isset($this->data)) {
		if(isset($this->data[$id])) {
			$value = $this->data[$id];
		}
	}
	$errorclass = '';
	$errortext = '';
	if(isset($formerrors[$id])) {
		$errorclass = 'error';
		$errortext = $formerrors[$id][0];
	}
?>

<div class="control-group <?php echo $errorclass; ?>">
	<label for='<?php echo $id; ?>'><?php echo $label; ?>:</label>
	<select id='<?php echo $id; ?>' class="span3" name='data[<?php echo $id; ?>]'>
	<?php
		foreach($data as $optionid=>$optionvalue) {
			$selected = '';
			if($optionid == $value) {
				$selected = 'selected="selected"';
			}
			echo '<option '.$selected.' value="'.$optionid.'">'.$optionvalue.'</option>';
		}
	?>
	</select>
	<?php
		if(strlen($errortext) > 0) {
			echo '<span class="help-inline">'.$errortext.'</span>';
		}
	?>
</div>