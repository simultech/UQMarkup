<h2>Contact Us</h2>

<?php
	$feedbacktypes = array(
		"Functionality bug",
		"Usability issue",
		"UQMarkup design feedback",
		"Feature Request",
		"Other"
	);
?>

<form class="well" method="POST">
	<?php
		echo $this->element('formfield',array('label'=>'Name','placeholder'=>'Name','id'=>'name','value'=>$userdetails[0]['cn'][0]));
		echo $this->element('formfield',array('label'=>'Email','placeholder'=>'Email','id'=>'email','value'=>$userdetails[0]['mail'][0]));
	?>
	<div class="control-group ">
		<label for='name'>Feedback Type:</label>
		<select name='feedbacktype'>
		<?php
			foreach($feedbacktypes as $feedbacktype) {
				echo '<option>'.$feedbacktype.'</option>';
			}
		?>
		</select>
	</div>
	<div class="control-group ">
		<label for='name'>Feedback:</label>
		<textarea name='comments' style='width:400px; height:200px;'></textarea>
	</div>
  <p>Do not hesitate to send anything, we are looking for any feedback, and we will be in touch as soon as possible.</p>
  <br />
  <button type="submit" class="btn btn-primary"><i class="icon-comment icon-white"></i> Send feedback</button>
</form>