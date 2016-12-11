<?php echo $this->element('classlist'); ?>

<script type='text/javascript'>
	$(document).ready(function() {
		$.tablesorter.defaults.widgets = ['zebra'];
		$.tablesorter.defaults.sortList = [[1,0]];
		$("table").tablesorter({debug: true});
		$("table").each(function() {
			console.log($(this));
		});
	});
</script>