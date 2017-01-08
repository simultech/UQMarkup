<h2>Manage Teaching Staff</h2>
<div class="alert alert-info">
	<ul style='margin-bottom:0;'>
		<li>Course Coordinators - Course Coordinators have full access to upload, assign, moderate and publish submissions</li>
		<li>Tutors - Tutors can be assigned submissions for marking</li>
	</ul></div>
<h3>Teaching Staff</h3>
<table>
	<th>Role</th><th>UQ ID</th><th>Name</th><th>Email</th><th>Remove</th>
	<?php
	foreach($staff as $stafftype=>$stafflist) {
		foreach($stafflist as $staffmember) {
			echo '<tr><td>'.$stafftype;
			if ($staffmember['User']['uqid'] != $userid) {
                echo ' ('.$this->Html->link('change',array('action'=>'changestaff',$course['Course']['uid'],$staffmember['User']['id']),array(),'Are you sure you wish to change this staff members access?').')';
            }
			echo '</td>';
			echo '<td>'.$staffmember['User']['uqid'].'</td><td>'.$staffmember['User']['name'].'</td><td>'.$staffmember['User']['email'].'</td>';
			echo '<td>'.$this->Html->link('Remove',array('action'=>'removestaff',$course['Course']['uid'],$staffmember['User']['id']),array(),'Are you sure you wish to remove this staff member?').'</td></tr>';
		}
	}
	?>
</table>
<h3>Add Staff Member</h3>
<form class="well" method="POST">
	<input type='hidden' name='data[course_id]' value='<?php echo $course['Course']['id']; ?>' />
	<?php
		echo $this->element('formfield',array('label'=>'UQ Username','placeholder'=>'UQ Username','id'=>'uqid'));
	?>
	<select name="data[role_id]">
	<?php
		foreach($roles as $roleid=>$rolename) {
			if($roleid > 1) {
				echo '<option value="'.$roleid.'">'.$rolename.'</option>';
			}
		}
	?>	
	</select>
  <br />
  <button type="submit" class="btn btn-primary"><i class="icon-user icon-white"></i> Add Staff Member</button>
</form>
<h3>Add Multiple Staff</h3>
<form class="well" method="POST" enctype="multipart/form-data" action='<?php echo $baseURL;?>/course/staffcsvupload'>
	<p><a target='_blank' href='<?php echo $baseURL;?>/course/staffcsv'>Download CSV Form</a></p>
	<input type='hidden' name='data[course_id]' value='<?php echo $course['Course']['id']; ?>' />
	<div class='input'>CSV File: <label for='csvfile'></label><input id='csvfile' type='file' name='csv' /></div>
	<br />
  <button type="submit" class="btn btn-primary"><i class="icon-user icon-white"></i> Add Staff Member</button>
</form>