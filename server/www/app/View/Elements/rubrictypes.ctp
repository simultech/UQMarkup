<?php
	echo $this->element('selectfield',array('label'=>'Rubric Type','data'=>$rubrictypes,'id'=>'type'));
?>
<div id='rubricdetails'></div>
<script type='text/javascript'>
	$('#type').change(function() {
		refreshOptions();
	});
	
	function refreshOptions() {
		var newType = $('#type').val();
		console.log(newType);
		$('#rubricdetails').empty();
		switch (newType) {
			case "table":
				$('#rubricdetails').append($('<div class="control-group"><label for="">Number of columns:</label><input type="text" id="tabletypelength" placeholder="0" /> <a style="margin-top:-10px;" href="javascript:refreshTable();" class="btn">refresh</a></div><div id="tabletypes"></div>'));
				break;
			case "boolean":
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_description">Description:</label><input id="data_meta_description" type="text" name="data[meta][description]" /></div>'));
				break;
			case "text":
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_description">Description:</label><input id="data_meta_description" type="text" name="data[meta][description]" /></div>'));
				break;
			case "number":
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_description">Description:</label><input id="data_meta_description" type="text" name="data[meta][description]" /></div>'));
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_min">Minimum Value:</label><input id="data_meta_min" type="text" name="data[meta][min]" value="0" /></div>'));
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_max">Maximum Value:</label><input id="data_meta_max" type="text" name="data[meta][max]" value="100" /></div>'));
				break;
		}
	}
	
	function refreshTable() {
		var numberofoptions = $('#tabletypelength').val()+"";
		numberofoptions = parseInt(numberofoptions);
		if(numberofoptions > 0 || numberofoptions == 0) {
			console.log(numberofoptions);
			for(var i=0; i<numberofoptions; i++) {
				var cell = $('<div class="ruberic_cell"></div>');
				cell.append($('<h4>Column '+(i+1)+'</h4>'));
				cell.append($('<div class="control-group"><label for="data_meta_'+i+'_name">Grade:</label><input id="data_meta_'+i+'_name" type="text" name="data[meta]['+i+'][name]" /></div>'));
				cell.append($('<div class="control-group"><label for="data_meta_'+i+'_grade">Mark (optional):</label><input id="data_meta_'+i+'_grade" type="text" name="data[meta]['+i+'][grade]" /></div>'));
				cell.append($('<div class="control-group"><label for="data_meta_'+i+'_description">Description:</label><textarea style="height:140px;" id="data_meta_'+i+'_description" name="data[meta]['+i+'][description]" /></textarea></div>'));
				$('#tabletypes').append(cell);
			}
			$('#tabletypes').append($('<div style="clear:both;"></div>'));
		}
	}
	
	refreshOptions();
</script>
<style type='text/css'>
	.ruberic_cell {
		border:1px solid #ddd;
		background:#eee;
		padding:10px;
		width:220px;
		float:left;
		margin:0 20px 20px 0;
	}
</style>