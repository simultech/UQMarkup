<div id="marks">
	<?php
	//check whether there are marks
    if(!empty($marks->marks) && $submission['Project']['id'] != '100') {
	?>

    <h3>Marks</h3>

    <?php
    $finalgrade = false;
    $gradecount = 0;
    $fullgradecount = 0;
    
    foreach($rubrics as $rubric) {
    	//print_r($rubric);
        $meta = json_decode($rubric['Rubric']['meta']);
        $output = "";
        
        $output .= '<div class="rubric_header">'. $rubric['Rubric']['name'] .'</div>';
        
        // Grab the mark for this rubric, if one exists
        $mark_value = null;
        foreach($marks->marks as $mark) {
            if ($mark->rubric_id == $rubric['Rubric']['id']) {
                $mark_value = $mark->value;
            }
        }
        
        switch($rubric['Rubric']['type']) {
            case "table":
                $output .= "<table class='table_rubric'>";
                $ths = "<tr>";
                $tds = "<tr>";
                $count = 0;
                foreach($meta as $option) {
                	if(isset($option->grade)) {
	                	$finalgrade = true;
	                	if($count == 0) {
		                	$fullgradecount += $option->grade;
	                	}
                	}
                    $ths .= "<th class='rubric_option_header'>" . $option->name . '</th>';
                    $desc = '';
                    if (isset($mark_value) && intval($mark_value) == $count) {
                    	$thegrade = '('.$option->grade.') ';
	                	if($submission['Project']['course_id'] == '31') {
                			$thegrade = '';
						}
	                    $desc = "<td class='rubric_option_body marked'>"."" . $option->description . '</td>';
	                    if(isset($option->grade)) {
		                    $gradecount += $option->grade;
		                }
                    } else {
                        $desc .= "<td class='rubric_option_body'>" . $option->description . '</td>';
                    }
                    $tds .= str_replace("\n", "<br />", $desc);
                    $count++;
                }
                $ths .= "</tr>";
                $tds .= "</td>";
                $output .= $ths;
                $output .= $tds;
                $output .= "</table>";
                break;
            case "boolean":
                $output .= '<div class="rubric_description">' . $meta->description . '</div>';
                $passfail = '';
                echo '*'.$mark_value.'*';
                if (isset($mark_value) && $mark_value == 'true') {
                    $passfail = 'Pass';
                } else if (isset($mark_value) && $mark_value == 'false') {
                    $passfail = 'Fail';
                }
                $output .= '<div class="rubric_value">'. $passfail . '</div>';
                break;
            case "text":
                $output .= '<div class="rubric_description">' . $meta->description . '</div>';
                $comment = '';
                if (isset($mark_value)) {
                    $comment = $mark_value;
                    $comment = str_replace("\n", "<br />", $comment);
                }
                
                $output .= '<div class="text_rubric_value">' . $comment . '</div>';
                break;
            case "number":
                $output .= '<div class="rubric_description">' . $meta->description . '</div>';
                $num = '';
                if (isset($mark_value)) {
                    $num = $mark_value;
                }
                
                $output .= '<div class="number_rubric"><span class="mark_value">'. $num . '</span> / <span class="mark_max">' . $meta->max . '</span></div>';
                break;
        }
        
        echo $output;
    }
    if($finalgrade && $submission['Project']['option_gradeshown'] == 1) {
	    if ($submission['Project']['option_gradeusefunction'] == 0) {
		    if($submission['Project']['course_id'] != '31' && $submission['Project']['course_id'] != '37' && $submission['Project']['course_id'] != '44' && $submission['Project']['course_id'] != '51' && $submission['Project']['course_id'] != '56' && $submission['Project']['course_id'] != '58') {
		    	if(intval($fullgradecount > 0)) {
			    	$precision = $submission['Project']['option_gradeprecision'];
			    	$gradecount = round(intval($gradecount)/($submission['Project']['option_gradescaling']), $precision);
			    	$fullgradecount = round(intval($fullgradecount)/($submission['Project']['option_gradescaling']), $precision);
				    echo '<div class="rubric_header">Final Grade</div>';
					echo '<strong>'.$gradecount.' / '.$fullgradecount.'</strong>';
				}
		    }
		} else {
			echo '<div class="rubric_header">Final Grade</div>';
			echo '<div id="final_grade_output" style="font-weight:bold"></div>';
			echo '
			<script>
			var grade_input = '.$gradecount.';
			var grade_output = "";
			'.$submission['Project']['option_gradefunction'].'
			$("#final_grade_output").text(grade_output);
			</script>';
		}
    }
    ?>
    <table class="table_rubric">

    </table>
<?php
	}
?>
</div>