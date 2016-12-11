<code>
Survey Responses for <?php echo $project_name; ?>
<?php
	foreach($surveys as $surveytype=>$survey) {
		switch($surveytype) {
			case 1:
				$survey_name = 'Student Survey';
				break;
			case 2:
				$survey_name = 'Tutors Getting Feedback Survey';
				break;
			case 3:
				$survey_name = 'Tutors Giving Feedback Survey';
				break;
			case 4:
				$survey_name = 'Coordinator Survey';
				break;
		}
		echo "\n\n\n\n".$survey_name."\n";
		//echo "Question,Parent Question,Type,Total,Avg,SD,R1 (SD or YES),R2 (D or NO),R3 (N),R4 (A),R5 (SA),R6 (NA)\n";
		echo 'Submission ID,Student ID,Marker';
		foreach($questions[$surveytype] as $question) {
			echo ','.$question;
		}
		echo "\n";
		foreach($responses[$surveytype] as $student_id=>$response) {
			echo $submissionids[$student_id].',';
			echo $users[$student_id].',';
			echo $markers[$student_id];
			foreach($questions[$surveytype] as $question) {
				if(isset($response[$question])) {
					echo ','.str_replace("\r",".  ",str_replace("\n",".  ",str_replace(",",".  ",$response[$question])));
				} else {
					echo ",";
				}
			}
			echo "\n";
		}
	}
?>