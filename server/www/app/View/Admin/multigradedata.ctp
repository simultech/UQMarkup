<?php
	echo 'ID,Submission ID,User ID,Marker ID,Audio Annotations,Log Type,Interaction,Meta,Session,Run,Date,';
	echo 'State,Pages,Start,End,Page Size,Seconds Before Scroll,';
	echo 'AnnotationID,From Time,Current Time,Duration,Filename';
	$showall = false;
	$rubricids = array();
	$metrics = array();
	echo "\n";
	foreach($submissions as $submission) {
		foreach($submission['Log'] as $log) {
			if(isset($markerlogs[$log['runhash']])) {
				echo $log['id'].',';
				echo $log['submission_id'].',';
				echo $log['user_id'].',';
				echo $markerlogs[$log['runhash']].',';
				echo $annotcounts[$log['runhash']].',';
				echo $log['logtype_id'].',';
				echo $log['interaction'].',';
				echo str_replace(",",".",$log['meta']).',';
				echo $log['sessionhash'].',';
				echo $log['runhash'].',';
				echo $log['created'].',';
				
				$meta = json_decode($log['meta']);
				
				if(isset($meta->state)) {
					echo $meta->state.',';
				} else {
					echo ',';
				}
				
				if(isset($meta->pages)) {
					echo $meta->pages.',';
				} else {
					echo ',';
				}
				
				if(isset($meta->start)) {
					echo $meta->start.',';
				} else {
					echo ',';
				}
				if(isset($meta->end)) {
					echo $meta->end.',';
				} else {
					echo ',';
				}
				if(isset($meta->pagesize)) {
					echo $meta->pagesize.',';
				} else {
					echo ',';
				}
				if(isset($meta->timespentatstart)) {
					echo $meta->timespentatstart.',';
				} else {
					echo ',';
				}
				
				if(isset($meta->annotation)) {
					echo $meta->annotation.',';
				} else {
					echo ',';
				}
				if(isset($meta->fromTime)) {
					echo $meta->fromTime.',';
				} else {
					echo ',';
				}
				if(isset($meta->currentTime)) {
					echo $meta->currentTime.',';
				} else {
					echo ',';
				}
				if(isset($meta->duration)) {
					echo $meta->duration.',';
				} else {
					echo ',';
				}
				if(isset($meta->filename)) {
					echo $meta->filename.',';
				} else {
					echo ',';
				}
				
				echo "\n";
			}
		}
		/*$audiolengths = array();
		foreach($submission['annotations'] as $annotation) {
			if($annotation->type == 'Recording') {
				if(isset($annotation->duration)) {
					$audiolengths[] = $annotation->duration;
				}
			}
		}
		$student_id = '';
		$marker_id = '';
		$markingtime = $submission['markingtime'];
		$readingtime = $submission['reading_time'];
		foreach($submission['Activity'] as $activity) {
			if($activity['state_id'] == 1) {
				$student_id = $activity['meta'];
			}
			if($activity['state_id'] == 2) {
				$marker_id = $activity['meta'];
			}
		}
		echo $student_id.','.$markingtime.','.$readingtime;
		foreach($audiolengths as $audiolength) {
			echo ','.$audiolength;
		}
		echo "\n";*/
	}
?>