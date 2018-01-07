<style>
body {
  overflow-x:hidden;
}
div#header {
  display:none;
}
div#contentwrapper {
  margin-top:0;
  padding-top:0;
  width:100%;
  border:0;
}
div#content {
  width:100%;
  padding:0;
}
div#iframewrapper {
  width:100% !important;
  border:0 !important;
  padding:0 !important;
  margin:0 !important;
  margin-right:200px;
}
form.well {
  padding:0 22px 0 22px !important;
}
form.well p {
  margin-bottom:0;
}
h3 {
  margin:0 !important;
  padding:0 15px !important;
  background:#fafafa; 
  font-size:110%;
}
ul.breadcrumb {
  margin-top:0px !important;;
  margin-bottom:0 !important;
}
div#footer {
  display:none;
}
#showhidefeedback {
	float: right;
	margin:4px 10px;
}
</style>
<div id='iframewrapper'>
	<iframe src='<?php echo $baseURL;?>/assessment/view/<?php echo $submission_hash; ?>/nowrapper'></iframe>
</div>
<div id='feedbackwrapper'>
<a href="#" id="showhidefeedback" class="btn btn-default btn-small">Hide feedback</a>
<h3>Feedback:</h3>
<form class='well' method='post'>
<textarea name='feedback'></textarea>
<?php
	$markerString = "Marker";
	$studentString = "Student";
?>
<p class='button'>
	<input type='submit' class='btn btn-primary btn-large' value='Send Feedback To <?php echo $markerString; ?>' />
</p>
<p>
<?php echo $studentString; ?>: <strong><?php echo $students; ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo $markerString; ?>: <strong><?php echo $markers; ?></strong>
</p>
</form>
</div>
<script>
	var showfeedback = true;
	$("#showhidefeedback").click(function() {
		showfeedback = !showfeedback;
		if (showfeedback) {
			$("#showhidefeedback").text('Hide feedback');
		} else {
			$("#showhidefeedback").text('Show feedback');
		}
		recalculateFeedbackHeight();
	});
	function recalculateFeedbackHeight() {
		var winHeight = $(window).height();
		var frameHeight = winHeight - 320;
		if (!showfeedback) {
			frameHeight += 260;
		}
		$("div#iframewrapper iframe").css({height:frameHeight+'px'});
		
		if (!showfeedback) {
			$('#feedbackwrapper').css({height:'40px'});
		} else {
			$('#feedbackwrapper').css({height:'300px'});
		}
	}
	$(document).ready(function() {
		recalculateFeedbackHeight();
	});
</script>
<style type='text/css'>
	form.well {
		margin-top:5px;
		margin:0;
	}
	div#iframewrapper {
		padding:10px;
		border:1px solid #613aa4;
		width:1200px;
		margin-left:-150px;
		background:#fff;
	}
	div#iframewrapper iframe {
		width:100%;
		height:580px;
		border-right:1px solid #333;
	}
	textarea {
		width:100%;
		padding:10px;
		margin:10px;
		margin-left:-10px;
		height:180px;
		font-size:110%;
	}
	p {
		padding:10px;
		font-size:120%;
	}
	p.button {
		padding:0;
		float:right;
	}
	h3 {
		color:#60419D;
		margin:0;
		padding:0;
		padding-top:10px;
	}
	#feedbackwrapper {
		margin-top:-30px;
		width: 100%;
		position:absolute;
		overflow:hidden;
	}
</style>
