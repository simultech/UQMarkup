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
		echo "Question,Parent Question,Type,Total,Avg,SD,R1 (SD or YES),R2 (D or NO),R3 (N),R4 (A),R5 (SA),R6 (NA)\n";
		foreach($survey['questions'] as $row=>$question) {
			if($row == 0) {
				continue;
			}
			if($question[4] == '') {
				echo str_replace(",",". ",($question[1].' - '.$question[2])).','; //question
			} else {
				echo str_replace(",",". ",$question[2]).','; //question
			}
			//echo str_replace(",",". ",$question[2]).','; //question
			echo $question[4].','; //parent
			echo $question[3].','; //type
			$responsesize = sizeOf($responses[$surveytype][$question[0]]);
			$avg = 'N/A';
			$sd = 'N/A';
			echo $responsesize[4].','; //total
			$realresponses = array();
			if($question[3] == 'likeit_optional') {
				foreach($responses[$surveytype][$question[0]] as $respkey=>$resp) {
					if($resp != 0) {
						$realresponses[$respkey] = $resp;
					}
				}
				$responsesize = sizeOf($realresponses);				
			} else {
				$realresponses = $responses[$surveytype][$question[0]];
			}
			if($question[3] == 'likeit_optional' || $question[3] == 'likeit') {
				$resptotal = 0;
				foreach($responses[$surveytype][$question[0]] as $response) {
					$resptotal += $response;
				}
				$avg = round($resptotal/$responsesize,2);
				$sdstuff = $responses[$surveytype][$question[0]];
				if($question[3] == 'likeit_optional') {
					//$sdstuff = array_slice($sdstuff,1,5);
					for($i=sizeOf($sdstuff)-1; $i>-1; $i--) {
						if($sdstuff[$i] == 0) {
							array_splice($sdstuff, $i,1);
						}
					}
				}
				$sd = round($this->Extra->sd($sdstuff),3);
			}
			echo $avg.',';
			echo $sd.',';
			$responsesize = sizeOf($responses[$surveytype][$question[0]]);
			switch($question[3]) {
				case 'boolean':
					$yescount = 0;
					$nocount = 0;
					foreach($responses[$surveytype][$question[0]] as $response) {
						if($response == 'yes') {
							$yescount++;
						} else {
							$nocount++;
						}
					}
					echo $yescount.','.$nocount.',,,,';
					break;
				case 'likeit_optional':
					$counts = array(0,0,0,0,0,0);
					foreach($responses[$surveytype][$question[0]] as $response) {
						$counts[$response]++;
					}
					echo $counts[1].','.$counts[2].','.$counts[3].','.$counts[4].','.$counts[5].','.$counts[0];
					break;
				case 'likeit':
					$counts = array(0,0,0,0,0,0);
					foreach($responses[$surveytype][$question[0]] as $response) {
						$counts[$response]++;
					}
					echo $counts[1].','.$counts[2].','.$counts[3].','.$counts[4].','.$counts[5].',';
					break;
			}
			echo "\n";
		}
	}
?>