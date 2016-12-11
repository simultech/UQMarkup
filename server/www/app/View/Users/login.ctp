<h2>Login to <?php echo $projectname; ?></h2>
<form class="well" method="POST">
  <?php
  	$errortoggle = '';
	if(isset($error)) {
		$errortoggle = 'error';
		echo "<div class='error'>Invalid username/password combination</div><br />";
	}
  ?>
  <span class="help-block">Login with your UQ credentials</span>
  <div class='control-group <?php echo $errortoggle; ?>'><input type="text" id='username' class="input" name="data[username]" placeholder="Username"></div>
  <div class='control-group <?php echo $errortoggle; ?>'><input type="password" class="input" name="data[password]" placeholder="Password"></div>
  <label class="checkbox">
    <input type="checkbox" name="rememberme"> Remember me
  </label><br />
  <button type="submit" class="btn btn-primary">Sign in</button>
</form>
<script type='text/javascript'>
$('document').ready(function() {
	$('#username').focus();
});
</script>