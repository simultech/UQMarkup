<?php
	if(!isset($type) || $type == 'all') {
?>
<?php
	foreach($submissions as $submission) {
		$currentstate = 1;
		$meta = '';
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
		switch($currentstate) {
			case 1:
				$status = 'Uploaded';
				$nexttask = '<td>Link to student/s: <input name="data[identify]['.$submission['Submission']['id'].']" type="text" /> <span class="label label-info">, separated</span></td>';		
				break;
			case 2:
				$current = 'identified';
				$status = 'Identified';
				$nexttask = '<td>Assign to tutor: <input name="data[assign]['.$submission['Submission']['id'].']" type="text" /></td>';
				break;
			case 3:
				$current = 'assigned';
				$status = 'Assigned';
				$nexttask = 'waiting'.",".$meta;
				break;
			case 4:
				$current = 'marked';
				$status = 'Marked';
				$nexttask = 'marked'.",".$meta;
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
			case 5:
				$current = 'moderated';
				$status = 'Moderated';
				$nexttask = '<td>Publish or remark</td>';
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
			case 6:
				$current = 'published';
				$status = 'Published';
				$nexttask = 'published';
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
		}
			//echo '<td class="state_'.$current.'">'.$status.'</td>';
			//echo '<td>'.$submission['Submission']['id'].'</td>';
			$title = str_replace('.pdf','',$submission['Attachment'][0]['title']);
			$maxlength = 26;
			if(strlen($title) > $maxlength) {
				$title = substr($title,0,$maxlength).'...';
			}
			echo $title.",";
			echo $nexttask."\n";
	}
?>
</table>
<br />
<script type='text/javascript'>
	$(document).ready(function() {
		$.tablesorter.defaults.widgets = ['zebra'];
		$.tablesorter.defaults.sortList = [[1,0]];
		$("table").tablesorter();
	});
</script>
<?php
} else if($type == 'raw') {
	foreach($submissions as $submission) {
		$currentstate = 1;
		$meta = '';
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
		switch($currentstate) {
			case 1:
				$status = 'Uploaded';
				$nexttask = '<td>Link to student/s: <input name="data[identify]['.$submission['Submission']['id'].']" type="text" /> <span class="label label-info">, separated</span></td>';		
				break;
			case 2:
				$current = 'identified';
				$status = 'Identified';
				$nexttask = 'Traditional,,';
				break;
			case 3:
				$current = 'assigned';
				$status = 'Assigned';
				$nexttask = 'UQMarkup,'.$meta.',';
				break;
			case 4:
				$current = 'marked';
				$status = 'Marked';
				$nexttask = 'UQMarkup'.",".$meta.',Yes';
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
			case 5:
				$current = 'moderated';
				$status = 'Moderated';
				$nexttask = '<td>Publish or remark</td>';
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
			case 6:
				$current = 'published';
				$status = 'Published';
				$nexttask = '<td><a href="'.$baseURL.'/admin/submissionstats/'.$submission['Submission']['id'].'" target="_blank">View statistics</a></td>';
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
		}
		$title = str_replace('.pdf','',$submission['Attachment'][0]['title']);
		$maxlength = 26;
		if(strlen($title) > $maxlength) {
			$title = substr($title,0,$maxlength).'...';
		}
		echo ''.$title.",".$nexttask.'<br />';
	}
	}
?>