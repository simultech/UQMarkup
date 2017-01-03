<h2>API Documentation</h2>
<p><strong>Auth</strong><br />
Each call needs either a GET or POST param called secret, with the value: <br />
shared=<?php echo $secret; ?>
</p>
<?php
	$path = $baseURL.'/api/';
	ksort($endpoints);
	foreach($endpoints as $name=>$endpoint) {
		echo '<h3>/'.$name.'</h3>';
		echo '<dl>';
			echo '<dt>Method: </dt><dd>'.$endpoint['method'].'</dd>';
			echo '<dt>URI: </dt><dd><a href="'.$path.$endpoint['url'].'?secret='.$secret.'" target="_blank">'.$path.$endpoint['url'].'</a></dd>';
			if(isset($endpoint['fields'])) {
				echo '<dt>Fields: </dt><dd><ul>';
				foreach($endpoint['fields'] as $field) {
					echo '<li>'.$field.'</li>';
				}
				echo '</ul></dd>';
			}
			if(isset($endpoint['auth']) && $endpoint['auth']) {
				echo '<dt>Require Auth: </dt><dd>Yes</dd>';
			} else {
				echo '<dt>Require Auth: </dt><dd>No</dd>';
			}
			echo '<dt>Status Codes: </dt><dd><ul><li>200</li>';
				foreach($endpoint['errors'] as $error) {
					echo '<li>'.$error.'</li>';
				}
			echo '</ul></dd>';
		echo '<dt>Response: </dt><dd><pre>'.$endpoint['response'].'</pre></dd>';
		echo '</dl>';
	}
?>