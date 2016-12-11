<style type='text/css'>
	div#ethicalframe {
		border:1px solid #333;
		height:400px;
		margin:20px;
		overflow:scroll;
		padding:20px;
	}
</style>
<div id='ethicalframe'>
<?php
	echo $content;
?>
</div>
<form method='post' action='<?php echo $baseURL; ?>/pages/ethicalclearance/yes'>
<style>
	textarea {
		width:100%;
		margin-right:20px;
		height:100px;
	}
	
</style>
<div>
<p><a href="<?php echo Configure::read('url_base'); ?>/studenttermsofuse.html" target="_blank">Open in a new page</a></p>
<label>What sorts of feedback have you received in the past:</label><textarea name='pastfeedback' class='form-control'></textarea>
</div>
<div>
<label>What sorts of feedback do expect you will receive at university:</label><textarea name='futurefeedback' class='form-control'></textarea>
</div>
<p style='text-align:center;'>
	<input type='submit' class='btn btn-primary' value='I give consent' />
	<a href='<?php echo $baseURL; ?>/pages/ethicalclearance/no' type='submit' class='btn'>I do not give consent</a>
</p>
</form>
