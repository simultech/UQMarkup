<?php
if(isset($superadmin)) {
?>
<a class="btn btn-warning" style="float:right;" href="/admin">Super Administration</a>
<?php
}
?>
<h2>Welcome to UQMarkup</h2>
<div class="alert alert-danger">
    <strong style='text-align:center'>
        <h1>WARNING</h1>
        UQMarkup is currently undergoing a major upgrade.  During this period, please avoid conducting new marking with in the system</strong>
</div>
<?php
if(isset($courses_teaching)) {
?>
<h3>Courses you are coordinating</h3>
<div class='courselist'>
<?php
	$donecurrent = false;
	$currentyear = date('Y')+4;
	$currentsemester = 99;
	foreach($courses_teaching as $course) {
		if($course['Course']['year'] != $currentyear || $course['Course']['semester'] != $currentsemester) {
			$currentyear = $course['Course']['year']; 
			$currentsemester = $course['Course']['semester'];
			if($currentyear < date('Y') && !$donecurrent) {
			 	$donecurrent = true;
			 	echo '<a class="showhide" style="margin-bottom:10px;" data-element="older_coordinating">Show previous semesters</a>';
				echo '<div class="toggle_hidden_default" id="older_coordinating">';
			}
			echo '<h4>'.$currentyear.' Semester '.$currentsemester.'</h4>';
		}
		$coursecode = $course['Course']['coursecode'];
		if($course['Course']['shadowcode'] != '') {
			$coursecode .= '/'.$course['Course']['shadowcode'];
		}
		echo "<div class='course'>";
			echo '<a href="'.$baseURL.'/course/admin/'.$course['Course']['uid'].'">'.strtoupper($coursecode).' - '.$course['Course']['name'];
			$projectext = 'Project';
			if(sizeOf($course['Project']) != 1) {
				$projectext = 'Projects';
			}
			echo '<em>'.sizeOf($course['Project']).' '.$projectext.'</em>';
			echo '</a>';
		echo "</div>";
	}
	if($donecurrent) {
		echo '</div>';
	}
?>
</div>
<div class='actions'>
	<a href='<?php echo $baseURL; ?>/course/create' class='btn'><i class="icon-plus-sign icon"></i> Create a new course</a>
</div>
<?php
}
?>



<?php
if(!empty($courses_tutoring)) {
?>
<h3>Courses you are marking</h3>
<div class='courselist'>
<?php
	$donecurrent = false;
	$currentyear = date('Y')+4;
	$currentsemester = 99;
	foreach($courses_tutoring as $course) {
		if($course['Course']['year'] != $currentyear || $course['Course']['semester'] != $currentsemester) {
			$currentyear = $course['Course']['year']; 
			$currentsemester = $course['Course']['semester'];
			if($currentyear < date('Y') && !$donecurrent) {
			 	$donecurrent = true;
			 	echo '<a class="showhide" style="margin-bottom:10px;" data-element="older_marking">Show previous semesters</a>';
				echo '<div class="toggle_hidden_default" id="older_marking">';
			}
			echo '<h4>'.$currentyear.' Semester '.$currentsemester.'</h4>';
		}
		$coursecode = $course['Course']['coursecode'];
		if($course['Course']['shadowcode'] != '') {
			$coursecode .= '/'.$course['Course']['shadowcode'];
		}
		echo "<div class='course'>";
			echo '<a href="'.$baseURL.'/tutor/admin/'.$course['Course']['uid'].'">'.strtoupper($coursecode).' - '.$course['Course']['name'];
			$projectext = 'Project';
			if(sizeOf($course['Project']) != 1) {
				$projectext = 'Projects';
			}
			echo '<em>'.sizeOf($course['Project']).' '.$projectext.'</em>';
			echo '</a>';
		echo "</div>";
	}
	if($donecurrent) {
		echo '</div>';
	}
?>
</div>
<?php
}
?>




<h3>Your project submissions</h3>
<div class='courselist'>
<?php
if(sizeOf($submissions) == 0) {
	echo '<p>You currently have no submitted assignments within UQMarkup.</p>';
}
$currentsemester = '';
$donecurrent = false;
foreach($submissions as $submission) {
	$thissemester = $submission['Course']['year'].' Semester '.$submission['Course']['semester'];
	if($thissemester != $currentsemester) {
		if($submission['Course']['year'] < date('Y') && !$donecurrent) {
		 	$donecurrent = true;
		 	echo '<a class="showhide" style="margin-bottom:10px;" data-element="older_student">Show previous semesters</a>';
			echo '<div class="toggle_hidden_default" id="older_student">';
		}
		echo '<h4>'.$thissemester.'</h4>';
		$currentsemester = $thissemester;
	}
	$target = '';
	if(isset($submission['published']) && $submission['published']) {
			$publishstatus = "<span class='status status_available'>Feedback available</span>";
			$publishlink = $baseURL . '/assessment/view/'.$submission['encodedid'];
			$target = "target='_blank'";
		} else {
			$publishstatus = "<span class='status status_unavailable'>No feedback available</span>";
			$publishlink = '';
		}
	?>
	<div class='course'><a <?php echo $target; ?> href='<?php echo $publishlink; ?>'>
		<?php echo $submission['Course']['coursecode'].' '.$submission['Project']['name']; ?>
		<?php echo $publishstatus; ?>
	</a></div>
	<?php
}
if($donecurrent) {
	echo '</div>';
}
?>
</div>

<script type='text/javascript'>
$(document).ready(function() {
	$('a.showhide').click(function() {
		var theLink = $(this);
		if($('#'+$(this).data('element')).css('display') == 'none') {
			theLink.text('Hide previous semesters');
		} else {
			theLink.text('Show previous semesters');
		}
		$('#'+$(this).data('element')).fadeToggle();
	});
	
});
function showHide(theDiv) {
	$('#'+theDiv).fadeToggle("slow", "linear",function () {

    });
}
</script>


<style type='text/css'>
div.courselist div.course a span.status {
	float:right;
	border:1px solid #468847;
	background:#DFF0D8;
	color:#468847;
	margin:-6px -6px 0 0;
	padding:5px;
	font-weight:normal;
	width:150px;
	text-align:center;
}
div.courselist div.course a span.status_unavailable {
	border:1px solid #C09853;
	background:#FCF8E3;
	color:#C09853;
}
div.toggle_hidden_default {
	display:none;
}
a.showhide {
	display:block;
	cursor:pointer;
	cursor:hand;
}
</style>