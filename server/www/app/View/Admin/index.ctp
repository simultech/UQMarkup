<h2>Manage Administrators</h2>
<h3>Administrators</h3>
	<p>Admins can create courses.  Super Admins have full access across the system.</p>
	<table>
		<thead>
			<tr>
				<th class="header">Username</th>
				<th class="header headerSortDown">Name</th>
				<th class="header">Super Admin</th>
				<th class="header">Receives System Email</th>
				<th class="header">Remove</th></tr>
		</thead>
		<tbody>
<?php
	foreach($admins as $admin) {
?>
			<tr>
				<td><?php echo $admin['User']['uqid']; ?></td>
				<td><?php echo $admin['User']['name']; ?></td>
				<td class='center'><input disabled type="checkbox" <?php if($admin['Adminuser']['super'] == 1) { echo 'checked'; } ?> /></td>
				<td class='center'><input disabled type="checkbox" <?php if($admin['Adminuser']['email'] == 1) { echo 'checked'; } ?> /></td>
				<?php echo '<td class="center">'.$this->Html->link('Remove',array('action'=>'index_remove',$admin['Adminuser']['id']),array(),'Are you sure you wish to remove this administrator?').'</td>'; ?>
			</tr>
<?php
	}
?>
		</tbody>
	</table>
<h3>Add Administrator</h3>
<form class="well" method="POST" action="/admin/index_add">
	<?php
		echo $this->element('formfield',array('label'=>'UQ Username','placeholder'=>'UQ Username','id'=>'uqid', 'value'=>''));
		echo $this->element('checkboxfield',array('label'=>'Super User (Warning: Superusers have full control of the system)','placeholder'=>'','id'=>'super', 'value'=>'0'));
		echo $this->element('checkboxfield',array('label'=>'Receives System Email','placeholder'=>'','id'=>'email', 'value'=>'0'));
	?>
  <br />
  <button type="submit" class="btn btn-primary"><i class="icon-user icon-white"></i> Add Administrator</button>
</form>
<script type='text/javascript'>
function checkboxchanged(checkbox,field) {
	var box = $(field);
	if($(checkbox).attr("checked")) {
		console.log();
		box.val("1");
	} else {
		box.val("0");
	}
}
</script>