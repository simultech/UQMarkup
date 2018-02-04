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
	<input id='<?php echo $id; ?>' type="text" class="span3" placeholder="<?php echo $placeholder; ?>" name='data[<?php echo $id; ?>]' value='<?php echo $value; ?>'>
	<?php
		if(strlen($errortext) > 0) {
			echo '<span class="help-inline">'.$errortext.'</span>';
		}
		if(isset($colorbox) && $colorbox) {
			echo '<label for="'.$id.'" class="colorbox" style="position:absolute; display: inline-block; border: thin solid black; padding:10px; margin-left:5px; border-radius:2px; -moz-border-radius:2px; -webkit-border-radius:2px;"></label>';
		}
	?>
</div>