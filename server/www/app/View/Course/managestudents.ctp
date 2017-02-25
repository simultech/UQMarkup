<h2>Manage Students For <?php echo $course['Course']['name']; ?></h2>
<h3>Update Students From Sinet</h3>
<?php
    echo ' <a class="btn" href="'.$baseURL.'/course/refreshlist/'.$course['Course']['uid'].'">Refresh</a></p>';
?>
<h3>Student List</h3>
<form method="POST">
<table>
    <thead>
    <tr><th>UQ ID</th><th>Name</th><th>Email</th><th>Assigned To</th><th>Update</th></tr>
    </thead>
    <?php
    foreach($students as $student) {
        echo '<tr><td>'.$student['User']['uqid'].'</td><td>'.$student['User']['name'].'</td><td>'.$student['User']['email'].'</td>';
        if(isset($automarklist[$student['User']['id']])) {
            echo '<td>'.$automarklist[$student['User']['id']].'</td>';
        } else {
            echo '<td>Unassigned</td>';
        }
        echo '<td><input type="text" name="studentassign['.$student['User']['uqid'].']" /></td>';
        echo '</tr>';
    }
    ?>
</table>
    <p>
        <input type='submit' value='Update Tutor Assignments' class='btn btn-primary' />
    </p>
</form>
<h3>Bulk Update Student Auto-assign</h3>
<form method='POST' enctype="multipart/form-data" action="<?php echo $baseURL; ?>/course/updateassign/<?php echo $course['Course']['uid']; ?>">
    <div class="fileupload fileupload-new" data-provides="fileupload">
        <div class="input-append">
            <label style='display:inline'>Upload tutor-student assignment list (<a href='<?php echo $baseURL; ?>/files/coordinator_tutor_template.csv'>template</a>):</label>
            <div class="uneditable-input span3"><i class="icon-file fileupload-exists"></i>
                <span class="fileupload-preview"></span>
            </div>
            <span class="btn btn-file"><span class="fileupload-new">Select file</span>
				<span class="fileupload-exists">Change</span>
					<input type='file' name='uq_id_list' />
				</span>
            <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
            <input type='submit' name="fileupload" style='margin-top:-2px; margin-left:10px;' value='Update List' class='btn' />
        </div>
    </div>
</form>
<script type='text/javascript'>
    $(document).ready(function() {
        $.tablesorter.defaults.widgets = ['zebra'];
        $.tablesorter.defaults.sortList = [[1,0]];
        $("table").tablesorter();
    });
</script>