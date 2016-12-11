<h2>Survey Results for <?php echo $submission['Project']['name']; ?></h2>
<h3>Student: <?php echo $student; ?></h3>
<table>
<tr><th>ID</th><th>Question</th><th>Response</th></tr>
<?php
$headersdone = false;
$currentpreamble = '';

foreach($questions as $question) {
	if(!$headersdone) {
		$headersdone = true;
		continue;
	}
	echo '<tr>';
	echo '<td>'.$question[0].'</td>';
	echo '<td width="300">'.$question[2].'</td>';
	$answer = 'No response';
	if(isset($existingresponses[$question[0]])) {
		$answer = $existingresponses[$question[0]];
	}
	switch($question[3]) {
		case 'boolean':
			break;
		case 'text':
			break;
		case 'likeit':
			if($answer != 'No response') {
				if(!isset($question[7]) || $question[7] != "B") {
					switch($answer) {
						case '1':
							$answer = 'Strongly Disagree';
							break;
						case '2':
							$answer = 'Disagree';
							break;
						case '3':
							$answer = 'Moderately Agree';
							break;
						case '4':
							$answer = 'Agree';
							break;
						case '5':
							$answer = 'Strongly Agree';
							break;
					}
				} else {
					switch($answer) {
						case '1':
							$answer = 'Not at all confident';
							break;
						case '2':
							$answer = 'Only a little confident';
							break;
						case '3':
							$answer = 'Fairly confident';
							break;
						case '4':
							$answer = 'Very confident';
							break;
						case '5':
							$answer = 'Totally confident';
							break;
				}
			}
		}
		case 'likeit_optional':
			if($answer != 'No response') {
				if(!isset($question[7]) || $question[7] != "B") {
					switch($answer) {
						case '1':
							$answer = 'Strongly Disagree';
							break;
						case '2':
							$answer = 'Disagree';
							break;
						case '3':
							$answer = 'Moderately Agree';
							break;
						case '4':
							$answer = 'Agree';
							break;
						case '5':
							$answer = 'Strongly Agree';
							break;
						case '0':
							$answer = 'N/A';
							break;
					}
				} else {
					switch($answer) {
						case '1':
							$answer = 'Not at all confident';
							break;
						case '2':
							$answer = 'Only a little confident';
							break;
						case '3':
							$answer = 'Fairly confident';
							break;
						case '4':
							$answer = 'Very confident';
							break;
						case '5':
							$answer = 'Totally confident';
							break;
						case '0':
							$answer = 'N/A';
							break;
					}
				}
			}
		break;	
	}
	echo '<td>'.$answer.'</td>';
	echo '</tr>';
}
?>
</table>