<h2>Manage Administrators</h2>
<h3>Current Administrators</h3>
	<table>
		<thead>
			<tr>
				<th class="header">Username</th>
				<th class="header headerSortDown">Name</th>
				<th class="header">Super Admin</th>
				<th class="header">Receives Email</th>
				<th class="header">Remove</th></tr>
		</thead>
		<tbody>
<?php
	foreach($admins as $admin) {
?>
			<tr>
				<td><?php echo $admin['User']['uqid']; ?></td>
				<td><?php echo $admin['User']['name']; ?></td>
				<td><input type="checkbox" /></td>
				<td><input type="checkbox" /></td>
				<?php echo '<td>'.$this->Html->link('Remove',array('action'=>'removeadministrator',$admin['User']['id']),array(),'Are you sure you wish to remove this administrator?').'</td>'; ?>
			</tr>
<?php
	}
?>
		</tbody>
	</table>
<h3>Add Administrator</h3>
<form class="well" method="POST">
	<?php
		echo $this->element('formfield',array('label'=>'UQ Username','placeholder'=>'UQ Username','id'=>'uqid'));
	?>
	<?php
		echo $this->element('formfield',array('label'=>'Super User','placeholder'=>'','id'=>'super'));
	?>
	<?php
		echo $this->element('formfield',array('label'=>'Receives Email','placeholder'=>'','id'=>'email'));
	?>
  <br />
  <button type="submit" class="btn btn-primary"><i class="icon-user icon-white"></i> Add Administrator</button>
</form>