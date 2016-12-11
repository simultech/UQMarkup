<table>
	<thead>
		<tr><th>Status</th><th>ID</th><th>Filename</th><th>Next Action</th></tr>
	</thead>
	<tbody>
<?php
	if(empty($submissions)) {
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
	}
	foreach($submissions as $submission) {
		$currentstate = 1;
		$meta = '';
		$shouldcontinue = false;
		foreach($submission['Activity'] as $activity) {
			switch($activity['state_id']) {
				case '1':
					if($currentstate < 2) {
						$currentstate = 2;
						$meta = $activity['meta'];
					}
					break;
				case '2':
					if($currentstate < 3) {
						$currentstate = 3;
						$meta = $activity['meta'];
						if($meta == $tutordetails['User']['uqid']) {
							$shouldcontinue = true;
						}
					}
					break;
				case '4':
					if($currentstate < 4) {
						$currentstate = 4;
						$meta = $activity['meta'];
					}
					break;
				case '6':
					if($currentstate < 6) {
						$currentstate = 6;
						$meta = $activity['meta'];
					}
					break;
			}
		}
		$current = 'uploaded';
		$submissionlink = $baseURL.'/submission/'.$submission['Submission']['id'];
		if(!$shouldcontinue) {
			continue;
		}
		switch($currentstate) {
			case 1:
				$status = 'Uploaded';
				$nexttask = '<td></td>';		
				break;
			case 2:
				$current = 'identified';
				$status = 'Identified';
				$nexttask = '<td></td>';
				break;
			case 3:
				$current = 'assigned';
				$status = 'Assigned';
				$nexttask = '<td>Waiting for mark from '.$meta.' (iPad)</td>';
				break;
			case 4:
				$current = 'marked';
				$status = 'Marked';
				$nexttask = '<td>Marked by '.$meta.'</td>';
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
			case 5:
				$current = 'moderated';
				$status = 'Moderated';
				$nexttask = '<td>Publish</td>';
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
			case 6:
				$current = 'published';
				$status = 'Published';
				$nexttask = '<td></td>';
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
		}
		echo '<tr>';
			echo '<td class="state_'.$current.'">'.$status.'</td>';
			echo '<td>'.$submission['Submission']['id'].'</td>';
			$title = str_replace('.pdf','',$submission['Attachment'][0]['title']);
			$maxlength = 26;
			if(strlen($title) > $maxlength) {
				$title = substr($title,0,$maxlength).'...';
			}
			echo '<td><a href="'.$submissionlink.'" target="_blank">'.$title.'</a></td>';
			echo $nexttask;
		echo '</tr>';
	}
?>
	</tbody>
</table>