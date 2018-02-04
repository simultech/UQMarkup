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
		console.log('bbb', newType);
		$('#rubricdetails').empty();
		switch (newType) {
			case "table":
				$('#rubricdetails').append($('<div class="control-group"><label for="">Number of columns (<strong>change value and press refresh</strong>):</label><input type="text" id="tabletypelength" placeholder="0" /> <a style="margin-top:-10px;" href="javascript:refreshTable();" class="btn">refresh</a></div><div id="tabletypes" style="overflow: auto"></div>'));
				$('#createrubric').prop('disabled', true);
				break;
			case "boolean":
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_description">Description:</label><input id="data_meta_description" type="text" name="data[meta][description]" /></div>'));
				$('#createrubric').prop('disabled', false);
				break;
			case "text":
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_description">Description:</label><input id="data_meta_description" type="text" name="data[meta][description]" /></div>'));
				$('#createrubric').prop('disabled', false);
				break;
			case "number":
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_description">Description:</label><input id="data_meta_description" type="text" name="data[meta][description]" /></div>'));
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_min">Minimum Value:</label><input id="data_meta_min" type="text" name="data[meta][min]" value="0" /></div>'));
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_max">Maximum Value:</label><input id="data_meta_max" type="text" name="data[meta][max]" value="100" /></div>'));
				$('#rubricdetails').append($('<div class="control-group"><label for="data_meta_range">Step (gap per value):</label><input id="data_meta_range" type="text" name="data[meta][range]" value="1" /></div>'));
				$('#createrubric').prop('disabled', false);
				break;
		}
	}
	
	function refreshTable() {
		
		var numberofoptions = $('#tabletypelength').val()+"";
		var currentCount = $('#tabletypes .ruberic_cell').length;
		
		numberofoptions = parseInt(numberofoptions);
		if(currentCount > 0) {
			$('#createrubric').prop('disabled', true);
		} else {
			$('#createrubric').prop('disabled', false);
		}
		if(numberofoptions >= currentCount) {
			for(var i=0; i<numberofoptions-currentCount; i++) {
				var cell = $('<div class="ruberic_cell"></div>');
				cell.append($('<h4>Column '+(i+1+currentCount)+'</h4>'));
				cell.append($('<div class="control-group"><label for="data_meta_'+i+'_name">Grade:</label><input id="data_meta_'+i+'_name" type="text" name="data[meta]['+i+'][name]" /></div>'));
				cell.append($('<div class="control-group"><label for="data_meta_'+i+'_grade">Mark (optional):</label><input id="data_meta_'+i+'_grade" type="text" name="data[meta]['+i+'][grade]" /></div>'));
				cell.append($('<div class="control-group"><label for="data_meta_'+i+'_description">Description:</label><textarea style="height:140px;" id="data_meta_'+i+'_description" name="data[meta]['+i+'][description]" /></textarea></div>'));
				$('#tabletypes').append(cell);
			}
		} else {
			for(var i=currentCount; i>numberofoptions; i--) {
				console.log('Removing', i);
				$('#tabletypes .ruberic_cell:last').remove();
			}
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