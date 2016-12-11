<h2>Manage Colour Tags</h2>
<link rel='stylesheet' type='text/css' href='/_dev/css//jquery/jquery.colorpicker.css' />
<script type='text/javascript' src='/_dev/js/jquery.colorpicker.js'></script>
<h3>Colour Tags for '<?php echo $project['Project']['name']; ?>'</h3>
<table>
	<th>Colour</th><th>Tag Name</th><th>Actions</th>
	<?php
	foreach($tags as $tag) {
		echo '<tr><td><div class="colortd" style="background-color:#'.$tag['Tag']['color'].'"></div></td><td>'.$tag['Tag']['name'].'</td>';
		echo '<td>'.$this->Html->link('Remove',array('action'=>'removetag',$tag['Tag']['id']),array(),'Are you sure you wish to remove this colour tag?').'</td></tr>';
	}
	?>
</table>
<h3>Add Colour Tag</h3>
<form class="well" method="POST">
	<input type='hidden' name='data[project_id]' value='<?php echo $project['Project']['id']; ?>' />
	<?php
		echo $this->element('formfield',array('label'=>'Tag Name','placeholder'=>'Tag Name','id'=>'name'));
		echo $this->element('formfield',array('label'=>'Tag Colour','placeholder'=>'000000','id'=>'color','colorbox'=>true));
	?>
	<script>
       	$( function() {
	        $('#color').colorpicker({
		        altField: '.colorbox',
				altProperties: 'background-color,color',
	        });
		});
	</script>
  <br />
  <button type="submit" class="btn btn-primary"><i class="icon-comment icon-white"></i> Create Colour Tag</button>
</form>