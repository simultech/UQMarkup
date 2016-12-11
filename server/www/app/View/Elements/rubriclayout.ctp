<table>
	<?php
	if(!isset($editrubrics) || $editrubrics == false) {
		echo '<table>';
	}
	foreach($rubrics as $rubric) {
		$meta = json_decode($rubric['Rubric']['meta']);
		$output = '';
		if(isset($editrubrics) && $editrubrics == true) {
			echo '<table class="rubricheader"><tr><th>Name</th><th>Section</th><th>Type</th><th>Actions</th></tr>';
			echo '<tr><td><strong>'.$rubric['Rubric']['name'].'</strong></td><td>'.$rubric['Rubric']['section'].'</td><td>'.$rubrictypes[$rubric['Rubric']['type']].'</td>';
			echo '<td style="text-align:center;">'.$this->Html->link('Edit',array('action'=>'editrubric',$rubric['Rubric']['id']),array()).' | '.$this->Html->link('Remove',array('action'=>'removerubric',$rubric['Rubric']['id']),array(),'Are you sure you wish to remove this rubric?').'</td></tr>';
		} else {
			$output .= '<p><strong>Section '.$rubric['Rubric']['section'].'</strong>: '.$rubric['Rubric']['name'].'</p>';
		}
		switch($rubric['Rubric']['type']) {
			case "table":
				foreach($meta as $option) {
					$extrap = '';
					if(isset($option->grade)) {
						$extrap = '<em>('.$option->grade.')</em>';
					}
					$output .= '<div class="table"><h3>'.$option->name.' '.$extrap.'</h3><p>'.str_replace("\n","<br />",$option->description).'</p></div>';	
				}
				break;
			case "boolean":
				$output .= "".$meta->description.': <input type="checkbox" />';
				break;
			case "text":
				$output .= "".$meta->description.': <input type="text" />';
				break;
			case "number":
				$output .= "".$meta->description.': <input type="text" /> ('.$meta->min.'-'.$meta->max.')';
				break;
		}
		echo '<tr><td class="showrubric" colspan="4">'.$output.'</td></tr>';
		if(isset($editrubrics) && $editrubrics == true) {
			echo '</table>';
		}
	}
	?>
</table>

<style type='text/css'>
	table tr.topborder td {
		border:0;
		border-top:3px solid #333;
	}
	table.rubricheader {
		margin-bottom:30px;
	}
</style>