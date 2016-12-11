<h2>
<?php
if (Configure::read('debug') > 0 ) {
	echo $name;
} else {
	echo '500 Error: Internal Error';
}
?>
</h2>
<p class="error">
	<strong><?php echo __d('cake', 'Error'); ?>: </strong>
	<?php printf(__d('cake', 'Internal error occurred at %s.'),"<strong>'{$url}'</strong>"); ?>
</p>
<?php
if (Configure::read('debug') > 0 ):
	echo $this->element('exception_stack_trace');
endif;
?>