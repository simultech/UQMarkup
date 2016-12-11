<h2>Survey Responses for <?php echo $project_name; ?></h2>
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
		echo '<h3>'.$survey_name.'</h3>';
		echo '<table class="survey" cellspacing="0" cellpadding="0"><tr><th width="288">Question</th><th width="80">Data</th><th>Response</th></tr>';
		foreach($survey['questions'] as $row=>$question) {
			if($row == 0) {
				continue;
			}
			if($question[4] == '') {
				echo '<tr><td>'.$question[1]."<br><strong>".$question[2].'</strong></td>';
			} else {
				echo '<tr><td>'.$question[2].'</td>';
			}
			$responsesize = sizeOf($responses[$surveytype][$question[0]]);
			echo '<td style="text-align:center;">';
			echo 'Tot: '.$responsesize.'<br />';
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
				echo 'Avg: '.round($resptotal/$responsesize,2).'<br />';
				
				$sdstuff = $responses[$surveytype][$question[0]];
				if($question[3] == 'likeit_optional') {
					//$sdstuff = array_slice($sdstuff,1,5);
					for($i=sizeOf($sdstuff)-1; $i>-1; $i--) {
						if($sdstuff[$i] == 0) {
							array_splice($sdstuff, $i,1);
						}
					}
				} 
				
				echo 'Sd: '.round($this->Extra->sd($sdstuff),3).'<br />';
			}
			echo '</td>';
			$responsesize = sizeOf($responses[$surveytype][$question[0]]);
			echo '<td class="nopadding">';
			switch($question[3]) {
				case 'boolean':
					echo '<table class="inner_survey">';
					echo '<tr><th>Yes</th><th>No</th></tr>';
					echo '<tr>';
					$yescount = 0;
					$nocount = 0;
					foreach($responses[$surveytype][$question[0]] as $response) {
						if($response == 'yes') {
							$yescount++;
						} else {
							$nocount++;
						}
					}
					echo '<td><span>'.$yescount.' ('.round($yescount/$responsesize*100).'%)</span><div class="bar" style="height:'.round($yescount/$responsesize*100).'%"></div></td>';
					echo '<td><span>'.$nocount.' ('.round($nocount/$responsesize*100).'%)</span><div class="bar" style="height:'.round($nocount/$responsesize*100).'%"></div></td>';
					echo '</tr>';
					echo '</table>';
					break;
				case 'text':
					$text = "";
					foreach($responses[$surveytype][$question[0]] as $response) {
						$text .= '<p>'.$response.'</p>';
					}
					echo '<div class="textarea">';
					echo $text;
					echo '</div>';
					break;
				case 'likeit_optional':
					$counts = array(0,0,0,0,0,0);
					foreach($responses[$surveytype][$question[0]] as $response) {
						$counts[$response]++;
					}
					echo '<table class="inner_survey">';
					if($question[7] == 'A') {
						echo '<tr><th>Strongly Disagree</th><th>Disagree</th><th>Moderately Agree</th><th>Agree</th><th>Strongly Agree</th><th>N/A</th></tr>';
					} else {
						echo '<tr><th>Not at all confident</th><th>Only a little confident</th><th>Fairly confident</th><th>Very confident</th><th>Totally confident</th><th>N/A</th></tr>';
					}
					echo '<tr><td><span>'.$counts[1].' ('.round($counts[1]/$responsesize*100).'%)</span><div class="bar" style="height:'.round($counts[1]/$responsesize*100).'%"></div></td><td><span>'.$counts[2].' ('.round($counts[2]/$responsesize*100).'%)</span><div class="bar" style="height:'.round($counts[2]/$responsesize*100).'%"></div></td><td><span>'.$counts[3].' ('.round($counts[3]/$responsesize*100).'%)</span><div class="bar" style="height:'.round($counts[3]/$responsesize*100).'%"></div></td><td><span>'.$counts[4].' ('.round($counts[4]/$responsesize*100).'%)</span><div class="bar" style="height:'.round($counts[4]/$responsesize*100).'%"></div></td><td><span>'.$counts[5].' ('.round($counts[5]/$responsesize*100).'%)</span><div class="bar" style="height:'.round($counts[5]/$responsesize*100).'%"></div></td><td><span>'.$counts[0].' ('.round($counts[0]/$responsesize*100).'%)</span><div class="bar" style="height:'.round($counts[0]/$responsesize*100).'%"></span></div></td></tr>';
					echo '</table>';
					break;
				case 'likeit':
					$counts = array(0,0,0,0,0,0);
					foreach($responses[$surveytype][$question[0]] as $response) {
						$counts[$response]++;
					}
					echo '<table class="inner_survey">';
					if($question[7] == 'A') {
						echo '<tr><th>Strongly Disagree</th><th>Disagree</th><th>Moderately Agree</th><th>Agree</th><th>Strongly Agree</th></tr>';
					} else {
						echo '<tr><th>Not at all confident</th><th>Only a little confident</th><th>Fairly confident</th><th>Very confident</th><th>Totally confident</th></tr>';
					}
					echo '<tr><td>'.$counts[1].' ('.round($counts[1]/$responsesize*100).'%)</td><td>'.$counts[2].' ('.round($counts[2]/$responsesize*100).'%)</td><td>'.$counts[3].' ('.round($counts[3]/$responsesize*100).'%)</td><td>'.$counts[4].' ('.round($counts[4]/$responsesize*100).'%)</td><td>'.$counts[5].' ('.round($counts[5]/$responsesize*100).'%)</td></tr>';
					echo '</table>';
					echo '</table>';
					break;
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}
?>

<style type='text/css'>
	div.bool {
		width:269px;
		float:left;
		background:#eee;
		padding-bottom:10px;
		border:1px solid #999;
		text-align:center;
		font-size:200%;
	}
	div.bool strong {
		display:block;
		background:#999;
		color:#fafafa;
		font-size:60%;
		margin-bottom:10px;
	}
	table.survey, table.survey th, table.survey td {
		border-color:#999;
	}
	table.survey td {
		border-bottom:15px solid #333;
	}
	table.survey tr td:first-child {
		padding:0 15px;
	}
	table.inner_survey {
		border:0;
		table-layout: fixed;
	}
	table.inner_survey th {
		color:#60419D;
		border-color:#ccc;
		font-weight:normal;
		padding:10px 0;
		background:#fafafa;
	}
	table.inner_survey td {
		border:0;
		border-right:1px solid #ccc;
		text-align:center;
		height:50px;
		position:relative;
	}
	table.inner_survey td span {
		display:block;
		position:absolute;
		z-index:20;
		width:100%;
		left:0;
		top:19px;
		text-shadow:-1px -1px 0px #fff, 1px -1px 0px #fff, -1px 1px 0px #fff, 1px 1px 0px #fff,-2px 0px 0px #fff, 2px 0px 0px #fff, 0px 2px 0px #fff, 0px -2px 0px #fff;
	}
	table.inner_survey th:last-child, table.inner_survey td:last-child {
		border-right:0;
	}
	div.likert {
		width:100px;
		float:left;
		border:1px solid #999;
		text-align:center;
		font-size:140%;
		height:90px;
		background:#fafafa;
	}
	div.likert strong {
		display:block;
		background:#ddd;
		color:#666;
		height:42px;
		font-weight:normal;
		font-size:60%;
		margin-bottom:5px;
	}
	div.likert_optional {
		width:88px;
	}
	td.nopadding {
		padding:0;
	}
	div.textarea {
		height:200px;
		padding:10px;
		overflow-y:scroll;
	}
	div.textarea p {
		border:1px solid #ddd;
		padding:5px;
		background:#fafafa;
	}
	div.bar {
		position:absolute;
		width:100%;
		background:#0096ff;
		bottom:0;
		left:0;
	}
</style>