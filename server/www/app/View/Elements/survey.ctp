<div id="survey">

    <h3>Questionaire</h3>
    <p>As you select boxes, the information will be automatically saved</p>
  <form method="POST" id="surveyform" data-survey_name="<?php echo $survey_name; ?>" data-project_id="<?php echo $project_id; ?>">
<div id='surveyarea'>
<?php
$headersdone = false;
$currentpreamble = '';
foreach($questions as $question) {
	if(!$headersdone) {
		$headersdone = true;
		continue;
	}
	$newpreambleclass = "";
	if($currentpreamble != $question[1]) {
		$newpreambleclass = "preamblequestion";
	}
	$subquestion = '';
	if($question[4] != '') {
		if ($question[5] != '') {
			if ($question[5] == 'Yes') {
				$subquestion .= ' yes';
			} else {
				$subquestion .= ' no';
			}
		}
	}
	
	echo '<div id="question_'.$question[0].'" class="surveyquestion '.$newpreambleclass.' '.$subquestion.'" data-question_id="'.$question[0].'" data-parent_id="'. $question[4] .'">';
	if($currentpreamble != $question[1]) {
		$currentpreamble = $question[1];
		echo '<p class="preamble">'.$currentpreamble.'</p>';
	}
	
	echo '<p class="question '.$subquestion.'">'.$question[2].':</p>';
	$fields = '';
	switch($question[3]) {
		case 'boolean':
    $yes_checked = "";
    $no_checked = "";
    if (isset($existingresponses[$question[0]])) {
      if ($existingresponses[$question[0]] == "yes") {
        $yes_checked = 'checked="true"';
      } else {
        $no_checked = 'checked="true"';
      }
    }
			$fields .= '<label for="data_response_'.$question[0].'_1">Yes: </label><input type="radio" class="yes_selector" name="data[response]['.$question[0].']" id="data_response_'.$question[0].'_1"  data-parent_id="'.$question[0].'" value="yes" '.$yes_checked.' />';
			$fields .= '<label for="data_response_'.$question[0].'_2">No: </label><input type="radio" class="no_selector" name="data[response]['.$question[0].']" id="data_response_'.$question[0].'_2" data-parent_id="'.$question[0].'" value="no" '.$no_checked.' />';
			break;
		case 'text':
			$content = "";
			if (isset($existingresponses[$question[0]])) {
        		$content = $existingresponses[$question[0]];
        	}
			$fields = '<label for="data_response_'.$question[0].'">Response: </label><textarea id="data_response_'.$question[0].'" name="data[response]['.$question[0].']">'.$content.'</textarea>';
			break;
		case 'likeit':
			$fields .= '<div class="likeitspacer">';
			$box1 = '';
			$box2 = '';
			$box3 = '';
			$box4 = '';
			$box5 = '';
			$box6 = '';
			if(isset($existingresponses[$question[0]])) {
				switch($existingresponses[$question[0]]) {
					case '1':
						$box1 = 'checked="true"';
						break;
					case '2':
						$box2 = 'checked="true"';
						break;
					case '3':
						$box3 = 'checked="true"';
						break;
					case '4':
						$box4 = 'checked="true"';
						break;
					case '5':
						$box5 = 'checked="true"';
						break;
				}
			}
			if(!isset($question[7]) || $question[7] != "B") {
				$fields .= '<div class="likeitbox"><input type="radio" '.$box1.' name="data[response]['.$question[0].']" value="1" id="data_response_'.$question[0].'_1" /><label for="data_response_'.$question[0].'_1">Strongly Disagree</label></div>';
				$fields .= '<div class="likeitbox"><input type="radio" '.$box2.' name="data[response]['.$question[0].']" value="2" id="data_response_'.$question[0].'_2" /><label for="data_response_'.$question[0].'_2">Disagree</label></div>';
				$fields .= '<div class="likeitbox"><input type="radio" '.$box3.' name="data[response]['.$question[0].']" value="3" id="data_response_'.$question[0].'_3" /><label for="data_response_'.$question[0].'_3">Moderately Agree</label></div>';
				$fields .= '<div class="likeitbox"><input type="radio" '.$box4.' name="data[response]['.$question[0].']" value="4" id="data_response_'.$question[0].'_4" /><label for="data_response_'.$question[0].'_4">Agree</label></div>';
				$fields .= '<div class="likeitbox"><input type="radio" '.$box5.' name="data[response]['.$question[0].']" value="5" id="data_response_'.$question[0].'_5" /><label for="data_response_'.$question[0].'_5">Strongly Agree</label></div>';
			} else {
				$fields .= '<div class="likeitbox"><input type="radio" '.$box1.' name="data[response]['.$question[0].']" value="1" id="data_response_'.$question[0].'_1" /><label for="data_response_'.$question[0].'_1">Not at all confident</label></div>';
				$fields .= '<div class="likeitbox"><input type="radio" '.$box2.' name="data[response]['.$question[0].']" value="2" id="data_response_'.$question[0].'_2" /><label for="data_response_'.$question[0].'_2">Only a little confident</label></div>';
				$fields .= '<div class="likeitbox"><input type="radio" '.$box3.' name="data[response]['.$question[0].']" value="3" id="data_response_'.$question[0].'_3" /><label for="data_response_'.$question[0].'_3">Fairly confident</label></div>';
				$fields .= '<div class="likeitbox"><input type="radio" '.$box4.' name="data[response]['.$question[0].']" value="4" id="data_response_'.$question[0].'_4" /><label for="data_response_'.$question[0].'_4">Very confident</label></div>';
				$fields .= '<div class="likeitbox"><input type="radio" '.$box5.' name="data[response]['.$question[0].']" value="5" id="data_response_'.$question[0].'_5" /><label for="data_response_'.$question[0].'_5">Totally confident</label></div>';
			}
			$fields .= '<div style="clear:both;"></div>';
			$fields .= '</div>';
			break;		
		case 'likeit_optional':
			$fields .= '<div class="likeitspacer">';
			$box1 = '';
			$box2 = '';
			$box3 = '';
			$box4 = '';
			$box5 = '';
			$box6 = '';
			if(isset($existingresponses[$question[0]])) {
				switch($existingresponses[$question[0]]) {
					case '1':
						$box1 = 'checked="true"';
						break;
					case '2':
						$box2 = 'checked="true"';
						break;
					case '3':
						$box3 = 'checked="true"';
						break;
					case '4':
						$box4 = 'checked="true"';
						break;
					case '5':
						$box5 = 'checked="true"';
						break;
					case '0':
						$box6 = 'checked="true"';
						break;
				}
			}
			$labels = array(
				'Strongly Disagree',
				'Disagree',
				'Moderately Agree',
				'Agree',
				'Strongly Agree',
			);
			switch($question[7]) {
				case 'B':
					$labels = array(
					    'Not at all confident',
					    'Only a little confident',
					    'Fairly confident',
					    'Very confident',
					    'Totally confident',
					);
					break;
				case 'C':
					$labels = array(
					    'No attention at all',
					    '',
					    'A moderate amount of attention',
					    '',
					    'A great deal of attention',
					);
					break;
				case 'D':
					$labels = array(
					    'Not understandable at all',
					    '',
					    'Moderately understandable',
					    '',
					    'Very understandable',
					);
					break;
				case 'E':
					$labels = array(
					    'Not meaningful at all',
					    '',
					    'Moderately meaningful',
					    '',
					    'Very meaningful',
					);
					break;
			}
			
			$fields .= '<div class="likeitbox"><input type="radio" '.$box1.' name="'.$question[0].'" value="1" id="data_response_'.$question[0].'_1" /><label for="data_response_'.$question[0].'_1">'.$labels[0].'</label></div>';
			$fields .= '<div class="likeitbox"><input type="radio" '.$box2.' name="'.$question[0].'" value="2" id="data_response_'.$question[0].'_2" /><label for="data_response_'.$question[0].'_2">'.$labels[1].'</label></div>';
			$fields .= '<div class="likeitbox"><input type="radio" '.$box3.' name="'.$question[0].'" value="3" id="data_response_'.$question[0].'_3" /><label for="data_response_'.$question[0].'_3">'.$labels[2].'</label></div>';
			$fields .= '<div class="likeitbox"><input type="radio" '.$box4.' name="'.$question[0].'" value="4" id="data_response_'.$question[0].'_4" /><label for="data_response_'.$question[0].'_4">'.$labels[3].'</label></div>';
			$fields .= '<div class="likeitbox"><input type="radio" '.$box5.' name="'.$question[0].'" value="5" id="data_response_'.$question[0].'_5" /><label for="data_response_'.$question[0].'_5">'.$labels[4].'</label></div>';
			$fields .= '<div class="likeitbox"><input type="radio" '.$box6.' name="'.$question[0].'" value="0" id="data_response_'.$question[0].'_0" /><label for="data_response_'.$question[0].'_0">N/A</label></div>';
			$fields .= '<div style="clear:both;"></div>';
			$fields .= '</div>';
			break;
	}
	echo '<div class="surveyresponse">'.$fields.'</div>';
	echo '<div style="clear:both"></div>';
	echo '</div>';
}
?>
<p>
</div>
</p>
</form>


<style type='text/css'>
	div#surveyarea {
		border:1px solid #ccc;
		padding:10px 10px 10px 0;
		margin-bottom:10px;
	}
	div.surveyquestion {
		clear:right;
		border:1px solid #eee;
		border-bottom:1px solid #ddd;
		background:#fafafa;
		padding:10px;
		margin-left:10px;
		margin-bottom:5px;
		position:relative;
	}
	div.surveyquestion p.preamble {
		font-size:110%;
		font-weight:bold;
		margin-top:10px;
		clear:left;
		position:absolute;
		left:0;
		top:-40px;
	}
	div.preamblequestion {
		margin-top:40px;
	}
	div.surveyquestion p.question {
		font-size:110%;
	}
	div.surveyquestion div.surveyresponse {
		background:#ddd;
		padding:5px;
		padding-left:10px;
		border-radius:5px;
		-moz-border-radius:5px;
		-webkit-border-radius:5px;
	}
	div.surveyquestion div.surveyresponse label {
		display:inline;
	}
	div.surveyquestion div.surveyresponse input {
		margin:3px 8px 5px 2px;
	}
	div.surveyquestion div.surveyresponse textarea {
		width:390px;
		height:50px;
		display:block;
	}
	div.surveyquestion div.surveyresponse div.likeitbox input {
		display:block;
		margin:0 auto;
	}
	div.likeitbox {
		width:68px;
		float:left;
		text-align:center;
		margin-top:5px;
		margin-bottom:2px;
	}
	div.likeitbox label {
		font-size:120%;
	}
	div.likeitspacer {
		background:url('<?php echo $baseURL ?>/img/likeitline2.png') no-repeat 28px 10px;
	}
  
  span#submit_result {
    margin-left: 5px;
    display: inline-block;
    padding: 5px;
  }
  
  .success {
    background: #dfd;
    border-radius: 3px;
    color: green;
    border: 1px solid green;
  }
  
  .failure {
    background: #fdd;
    border-radius: 3px;
    color: red;
    border: 1px solid red;
    
  }
</style>
<script src="<?php echo $baseURL ?>/js/jquery.surveytools.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
$('#surveyarea').surveytools('conditionalSections');
$('form#surveyform').surveytools('saveSurvey');
$('form#surveyform input').click(function() {
	var survey_id = "1";
	var question_id = $(this).attr('name');
	var answer = $(this).val();
	var theURL = '<?php echo $baseURL; ?>/assessment/savesurveyanswer/<?php echo $project_id; ?>/'+survey_id;
	$.ajax({
		type: "POST",
		url: theURL,
		data: { data: {question_id: question_id, answer: answer} }
		}).done(function( msg ) {

		});
	});
</script>
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
</div>