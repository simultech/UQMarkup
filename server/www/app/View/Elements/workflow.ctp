<div id='workflow'>
<?php
	
	if(!isset($incompleteclass)) {
		$incompleteclass = '';
	}
	for($i=0; $i<sizeOf($workflow); $i++) {
		if(isset($workflow[$i]['link']) && $incompleteclass == '') {
			echo "<a href='".$workflow[$i]['link']."'>";
		}
		echo "<div class='step ".$workflow[$i]['class'].' '.$incompleteclass."'><p>".$workflow[$i]['name']."</p><em class='workflow_status workflow_".$workflow[$i]['status']."'></em></div>";
		if(isset($workflow[$i]['link'])) {
			echo '</a>';
		}
		if($workflow[$i]['status'] == 'incomplete') {
			$incompleteclass = 'step_incomplete';
		}
		if($i < sizeOf($workflow)-1) {
			echo '<div class="arrow '.$incompleteclass.'"></div>';
		}
	}
?>
<div style='clear:both;'></div>
</div>