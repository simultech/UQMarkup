<h2>Manage Teaching Staff</h2>
<h3>Student List</h3>
<?php
    echo $this->element('classlist');
?>
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