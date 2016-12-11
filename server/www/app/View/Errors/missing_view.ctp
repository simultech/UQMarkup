<h2>
<?php
if (Configure::read('debug') > 0 ) {
	echo $name;
} else {
	echo '404 Error: File not found (View)';
}
?>
</h2>
<p class="error">
	<strong><?php echo __d('cake', 'Error'); ?>: </strong>
	<?php printf(__d('cake', 'The requested address %s was not found on this server.'),"<strong>'{$url}'</strong>"); ?>
</p>
<?php
if (Configure::read('debug') > 0 ):
	echo $this->element('exception_stack_trace');
endif;
?>