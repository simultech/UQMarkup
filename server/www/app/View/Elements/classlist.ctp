<table>
	<thead>
	<tr><th>UQ ID</th><th>Name</th><th>Email</th><th>Assigned To</th></tr>
	</thead>
	<?php
	foreach($students as $student) {
		echo '<tr><td>'.$student['User']['uqid'].'</td><td>'.$student['User']['name'].'</td><td>'.$student['User']['email'].'</td>';
		if(isset($automarklist[$student['User']['id']])) {
			echo '<td>'.$automarklist[$student['User']['id']].'</td>';
		} else {
			echo '<td>Unassigned</td>';
		}
		echo '</tr>';
	}
	?>
</table>