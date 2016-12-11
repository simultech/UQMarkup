<div id='iframewrapper'>
	<iframe src='<?php echo $baseURL;?>/assessment/view/<?php echo $submission_hash; ?>/nowrapper'></iframe>
</div>
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
		height:500px;
		border-right:1px solid #333;
	}
	textarea {
		width:100%;
		padding:10px;
		margin:10px;
		margin-left:-10px;
		height:200px;
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
</style>