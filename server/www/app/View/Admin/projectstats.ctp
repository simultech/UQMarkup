<script src='<?php echo $baseURL; ?>/js/icanhaz.min.js'></script>
<script src='<?php echo $baseURL; ?>/js/date.js'></script>
<script src="<?php echo $baseURL; ?>/js/charts/raphael.js"></script>
<script src="<?php echo $baseURL; ?>/js/charts/g.raphael-min.js"></script>
<script src="<?php echo $baseURL; ?>/js/charts/g.bar-min.js"></script>
<script src="<?php echo $baseURL; ?>/js/charts/g.dot-min.js"></script>
<script src="<?php echo $baseURL; ?>/js/charts/g.line-min.js"></script>
<script src="<?php echo $baseURL; ?>/js/charts/g.pie-min.js"></script>
<h2>Statistics for <?php echo $project['Project']['name']; ?></h2>
<?php echo $this->Html->link('Download Raw Data (JSON)','/admin/rawprojectstats/'.$project['Project']['id'],array('class'=>'btn','target'=>'_blank'),'This download could take some time, do you wish to continue?'); ?> 
<?php echo $this->Html->link('Download Meta Data (JSON)','/admin/rawprojectstats/'.$project['Project']['id'].'/meta',array('class'=>'btn','target'=>'_blank'),'This download could take some time, do you wish to continue?'); ?> 
<?php echo $this->Html->link('Download Raw Data (XML)','/admin/rawprojectstats/'.$project['Project']['id'].'?format=xml',array('class'=>'btn','target'=>'_blank'),'This download could take some time, do you wish to continue?'); ?> 
<?php echo $this->Html->link('Download Meta Data (XML)','/admin/rawprojectstats/'.$project['Project']['id'].'/meta?format=xml',array('class'=>'btn','target'=>'_blank'),'This download could take some time, do you wish to continue?'); ?> 


<div id="holder"></div>




<script>
var submissions = <?php echo json_encode($submissions); ?>;
			/*window.onload = function () {
                var r = Raphael("holder"),
                    xs = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
                    ys = [7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
                    data = [294, 300, 204, 255, 348, 383, 334, 217, 114, 33, 44, 26, 41, 39, 52, 17, 13, 2, 0, 2, 5, 6, 64, 153, 294, 313, 195, 280, 365, 392, 340, 184, 87, 35, 43, 55, 53, 79, 49, 19, 6, 1, 0, 1, 1, 10, 50, 181, 246, 246, 220, 249, 355, 373, 332, 233, 85, 54, 28, 33, 45, 72, 54, 28, 5, 5, 0, 1, 2, 3, 58, 167, 206, 245, 194, 207, 334, 290, 261, 160, 61, 28, 11, 26, 33, 46, 36, 5, 6, 0, 0, 0, 0, 0, 0, 9, 9, 10, 7, 10, 14, 3, 3, 7, 0, 3, 4, 4, 6, 28, 24, 3, 5, 0, 0, 0, 0, 0, 0, 4, 3, 4, 4, 3, 4, 13, 10, 7, 2, 3, 6, 1, 9, 33, 32, 6, 2, 1, 3, 0, 0, 4, 40, 128, 212, 263, 202, 248, 307, 306, 284, 222, 79, 39, 26, 33, 40, 61, 54, 17, 3, 0, 0, 0, 3, 7, 70, 199],
                    axisy = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
                    axisx = ["12am", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12pm", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11"];

                r.dotchart(10, 10, 620, 260, xs, ys, data, {symbol: "o", max: 10, heat: true, axis: "0 0 1 1", axisxstep: 23, axisystep: 6, axisxlabels: axisx, axisxtype: " ", axisytype: " ", axisylabels: axisy}).hover(function () {
                    this.marker = this.marker || r.tag(this.x, this.y, this.value, 0, this.r + 2).insertBefore(this);
                    this.marker.show();
                }, function () {
                    this.marker && this.marker.hide();
                });
            };*/
</script>
<script id="submissionview" type="text/html">
	<div class='submissiondata'>
		<h3>{{data.student_id}}</h3>
		<dl>
		    <dt>Student: </dt><dd>{{data.submittedby.name}} ({{data.submittedby.uqid}})</dd>
		    <dt>Marker: </dt><dd>{{data.markedby.name}} ({{data.markedby.uqid}})</dd>
		    <dt>Moderated: </dt><dd>No</dd>
		    <dt>Marking Time: </dt><dd>{{data.marks.time_spent_marking}}</dd>
		    <dt>Survey Responses: </dt><dd><a href="{{surveylink}}" target="_blank">{{data.surveyresponses}} entries</a></dd>
		    <dt>Pages: </dt><dd>{{data.pages}}</dd>
		    <dt>Annotations: </dt><dd>{{data.numaudio}}</dd>
		    <dt>Audio Annotations length: </dt><dd>{{data.audiotime}}</dd>
		    <dt>Viewing sessions: </dt><dd>{{data.sessions}}</dd>
		    <dt>Viewing time: </dt><dd>{{data.viewingtime}}</dd>
		    <!--<dt>Viewing times: </dt><dd>{{data.viewingtimestring}}</dd>-->
		    <dt>Details: </dt><dd><a target="_blank" href="<?php echo $baseURL; ?>/admin/submissionstats/{{Submission.id}}">View</a></dd>
		</dl>
	</div>
</script>
<div id='datafields'></div>
<script type='text/javascript'>
$(document).ready(function() {
	for(var i=0; i<submissions.length; i++) {
		if(submissions[i].data.surveyresponses > 0) {
			submissions[i].surveylink = '<?php echo $baseURL; ?>/admin/surveystats/'+submissions[i].Submission.id;
		} else {
			submissions[i].surveylink = '#';
		}
		submissions[i].data.audiotime = (new Date).clearTime().addSeconds(submissions[i].data.audiotime).toString('HH:mm:ss');
		submissions[i].data.viewingtime = (new Date).clearTime().addSeconds(submissions[i].data.viewingtime).toString('HH:mm:ss');
		if(submissions[i].data.marks.time_spent_marking > 0) {
			submissions[i].data.marks.time_spent_marking = (new Date).clearTime().addSeconds(submissions[i].data.marks.time_spent_marking).toString('HH:mm:ss');
		}
		var view = ich.submissionview(submissions[i]);
		$('#datafields').append(view);
	}
});
</script>