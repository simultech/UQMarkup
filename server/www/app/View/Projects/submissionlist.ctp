<?php
	if(!isset($type) || $type == 'all') {
?>
<h2>Submission List</h2>
<table>
	<thead>
		<tr><th>Status</th><th>ID</th><th>Filename</th><th>Next Action</th></tr>
	</thead>
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
</table>
<br />
<script type='text/javascript'>
	$(document).ready(function() {
		$.tablesorter.defaults.widgets = ['zebra'];
		$.tablesorter.defaults.sortList = [[1,0]];
		$("table").tablesorter({debug: true});
	});
</script>
<?php
} else if($type == 'raw') {
	echo 'Student,State,Method,Audio Annotations,Marked By'."\n";
	foreach($submissions as $submission) {
		$currentstate = 1;
		$meta = '';
		$markedby = '';
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
						$markedby = $meta;
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
				$nexttask = ''.$markedby;
				$submissionlink = $baseURL.'/assessment/view/'.$submission['Submission']['encode_id'];
				break;
		}
		$title = str_replace('.pdf','',$submission['Attachment'][0]['title']);
		$maxlength = 26;
		if(strlen($title) > $maxlength) {
			$title = substr($title,0,$maxlength).'...';
		}
		$audiocount = 0;
		if(isset($submission['annotationz'])) {
			foreach($submission['annotationz'] as $annot) {
				if($annot->type == 'Recording') {
					$audiocount++;
				}
			}
		}
		if($current == 'published') {
			$method = 'UQMarkup';
			if($nexttask == 'uqadekke' || $nexttask == '') {
				$method = 'Traditional';
				$nexttask = "";
			}
			if(!isset($submission['Student'])) {
				print_r($submission);
			}
			echo ''.$submission['Student']['uqid'].",".$current.",".$method.",".$audiocount.",".$nexttask."\n";
		}
	}
	}
?>