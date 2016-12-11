<?php
	$graphwidth = 600;
	$graphheight = 400;
	$audiocount = 0;
?>
<script type='text/javascript' src="https://raw.github.com/DmitryBaranovskiy/raphael/master/raphael-min.js"></script>
<h2>Statistics for Submission <?php echo $submission['Submission']['id']; ?></h2>
<dl>
	<dt>Link: </dt><dd><a target="_blank" href="<?php echo $submission['data']['uri']; ?>">https://uqmarkup.ceit.uq.edu.au<?php echo $submission['data']['uri']; ?></a></dd>
	<dt>Submitted by: </dt>
	<dd>
		<?php 
			if(isset($submission['data']['submittedbygroup'])) {
				$i=0;
				foreach($submission['data']['submittedbygroup'] as $auser) {
					echo $auser['name'].' ('.$auser['uqid'].')'; 
					if($i < sizeOf($submission['data']['submittedbygroup'])-1) {
						echo ' | ';
					}
					$i++;
				}
			} else {
				echo $submission['data']['submittedby']['name'].' ('.$submission['data']['submittedby']['uqid'].')'; 
			}
		?> 
	</dd>
	<dt>Marked by: </dt>
	<dd>
		<?php 
			if(isset($submission['data']['markedbygroup'])) {
				$i=0;
				foreach($submission['data']['markedbygroup'] as $auser) {
					echo $auser['name'].' ('.$auser['uqid'].')'; 
					if($i < sizeOf($submission['data']['markedbygroup'])-1) {
						echo ' | ';	
					}
					$i++;
				}
			} else {
				echo $submission['data']['markedby']['name'].' ('.$submission['data']['markedby']['uqid'].')'; 
			}
		?> 
	</dd>
	<dt>Moderated: </dt>
	<dd>
	<?php
		if(isset($submission['data']['moderated'])) {
			echo $submission['data']['markedby']['name']. '('.$submission['data']['markedby']['uqid'].')'; 
		} else {
			echo "Not moderated";
		}
	?>
	</dd>
	<dt>Marking time: </dt><dd>
	<?php
		if(isset($submission['data']['marks']->time_spent_marking)) {
			$time = $submission['data']['marks']->time_spent_marking;
			echo date('i:s',$time).'';
		} else {
			echo 'Not recorded';
		}
	?>
	</dd>
	<dt>Grades: </dt><dd><ul>
	<?php
		foreach($submission['data']['rubricmarks'] as $rubricmark) {
			echo '<li><strong>'.$rubricmark['name'].'</strong> - '.$rubricmark['value'].'</li>';
		}
	?>
	</ul></dd>
	<dt>Viewing sessions: </dt><dd><?php echo sizeOf($submission['sessions']); ?></dd>
</dl>

<h3>Log Details</h3>

<?php
if(sizeOf($submission['sessions']) == 0) {
	echo '<p>No logs for this submission</p>';
}
foreach($submission['sessions'] as $sessionid=>$session) {
	foreach($session as $runid=>$run) {
		$uid = $sessionid.'_'.$runid;
		?>
		<h4>Viewing session <?php echo $sessionid.' - '.$runid; ?></h4>	
			<?php
			if(!isset($run['Closed'])) {
				echo '<div class="session_progress">Session still in progress</div>';
				continue;
			}
			?>
			<dt>Viewer: </dt><dd><?php echo $run['User']['uqid']; ?></dd>
			<dt>Viewing time: </dt><dd><?php echo strtotime($run['Closed'])-strtotime($run['Opened']); ?> seconds</dd>
			<dt>Start time: </dt><dd><?php echo $run['Opened']; ?></dd>
			<dt>End time: </dt><dd><?php echo $run['Closed']; ?></dd>
			<dt>Interactions: </dt><dd><?php echo $run['Interactioncount']; ?></dd>
			<dt>Raw data: </dt><dd id="rawdata_<?php echo $uid; ?>"><a href="javascript:showrawdata('<?php echo $uid; ?>');">Show data</a><pre style='display:none;'><?php print_r($run); ?></pre></dd>
			<dt>Document Viewing:</dt><dd>
			<div class='graph' id='graph_<?php echo $uid; ?>'></div></dd>
			<script type='text/javascript'>
				<?php 
					$pages = explode(',', $run['Details']->pagelengths);
					$totalpagelength = array_sum($pages);
					$annotations = json_encode($submission['data']['annotations']);
				?>
				$(document).ready(function() {
					var uid = '<?php echo $uid; ?>';
					var scrolls = <?php echo json_encode($run['Logs']['Scroll']); ?>;
					var annotationtypes = <?php echo $annotations; ?>;
					var pages = <?php echo json_encode($pages); ?>;
					var totalpagelength = <?php echo $totalpagelength; ?>;
					scrollingGraph(uid,scrolls,annotationtypes,pages,totalpagelength);					
				});
			</script>
			<dt>Audio Viewing:</dt><dd>
				<?php
					$audiointeractions = array();
					if(isset($run['Logs']['Audio'])) {
						foreach($run['Logs']['Audio'] as $audiolog) {
							$filename = str_replace('.mp3','',$audiolog['meta']->filename);
							$audiointeractions[$filename][] = $audiolog['meta'];
						}
					}
					foreach($submission['data']['annotations'] as $annotation) {
						if($annotation->type == 'Recording') {
							?>
							<div class='audiowrapper'>
								<div class='audiobox'>
								<?php
								$filename = str_replace('.m4a','',$annotation->filename);
								$totallisteningtime = 0;
								if(isset($audiointeractions[$filename])) {
									foreach($audiointeractions[$filename] as $audiointeraction) {
										$fromTime = $audiointeraction->fromTime;
										$currentTime = $audiointeraction->currentTime;
										$duration = $audiointeraction->duration;
										
										$audioXPos = $fromTime/$annotation->duration*660;
										$audioWidth = $currentTime/$duration*660 - $audioXPos;
										echo "<div style='width:".$audioWidth."px; left:".$audioXPos."px;' class='audiointeraction'></div>";
										$totallisteningtime += ($currentTime-$fromTime);
									}
								}
								?>
								</div>
								<div class='audioname'><?php echo $annotation->title; ?> (listened for <?php echo $totallisteningtime; ?> seconds) </div>
								<div class='duration'><?php echo $annotation->duration; ?> seconds duration</div>
							</div>
							<?php
						}
					}
				?>
			</dd>
		<?php
		
	}
}
?>


<script type='text/javascript'>
	function scrollingGraph(uid,scrolls,annotationtypes,pages,totalpagelength) {
		
		//Options
		var scrollBGColor = '#2f2';
		var annotBGColor = '#999';
		var annotColor = '#fff';
		var annotSize = 12;
		var width = 660;
		var height = 400;
		
		//Generate the graph
		var paper = Raphael("graph_"+uid, width+1, height+20);
		
		//Max time spent
		var maxtime = 0;
		for(var scroll in scrolls) {
		    if(parseFloat(scrolls[scroll].meta.timespentatstart) > maxtime) {
		    	maxtime = parseFloat(scrolls[scroll].meta.timespentatstart);
		    }
		}
		
		//Axis
		var axisOffset = 40;
		var yAxis = paper.path("M60,10L60,"+(height-axisOffset+20));
		var xAxis = paper.path("M60,"+(10+height-axisOffset)+"L"+width+","+(10+height-axisOffset));
		var yAxisLabel = paper.text(10, height/2-20, "Time (seconds)");
		yAxisLabel.transform("R270");
		yAxisLabel.attr('font-size',14);
		yAxisLabel.attr('font-weight','bold');
		var xAxisLabel = paper.text(width/2+20, height+10, "Page");
		xAxisLabel.attr('font-size',14);
		xAxisLabel.attr('font-weight','bold');
		//X Axis labels
		var currentPos = axisOffset+20;
		for(var i=0; i<pages.length; i++) {
		    var theWidth = (pages[i]/totalpagelength)*(width-axisOffset-20);
		    currentPos = Math.round(currentPos + theWidth);
		    var xAxisLine = paper.path("M"+currentPos+","+(10+height-axisOffset)+"L"+currentPos+","+(10+height-20));
		    var xAxisLineText = paper.text(currentPos-(theWidth/2), 10+height-30, "Page "+(i+1));
		}
		//Y Axis labels
		var currentPos = axisOffset;
		for(var i=0; i<10; i++) {
		    var theHeight = (height-axisOffset);
		    currentPos = Math.round(currentPos + theWidth);
		    var yPos = 10+i*theHeight/9;
		    var theTime = Math.round((maxtime-(maxtime/9*i))/100)/10;
		    var yAxisLine = paper.path("M40,"+yPos+"L61,"+yPos+"");
		    var yAxisLineText = paper.text(38, yPos, theTime+"");
		    yAxisLineText.attr('text-anchor','end');
		}
		
		//Scroll
		var validRectWidth = 0;
		for(var scroll in scrolls) {
		    var xOffset = (width-axisOffset)*(scrolls[scroll].meta.start/100);
		    var rectWidth = (width-axisOffset+20)*(scrolls[scroll].meta.pagesize/100);
		    if(rectWidth > 999999) {
			    rectWidth = validRectWidth;
		    } else {
			    validRectWidth = rectWidth;
		    }
		    var rectHeight = scrolls[scroll].meta.timespentatstart/maxtime*(height-40);
		    
		    var xPos = axisOffset+1+xOffset+20;
		    var yPos = 10+height-axisOffset-1-rectHeight;
		    var viewing = paper.rect(xPos,yPos,rectWidth,rectHeight);
		    var secText = Math.round(scrolls[scroll].meta.timespentatstart/100)/10+"s";
		    var viewingText = paper.text(xPos+rectWidth/2,yPos+10,secText);
		    viewingText.attr('font-size',14);
		    viewingText.attr('fill',"#333");
		    viewingText.hide();
		    viewing.data({'label':viewingText});
		    viewing.attr("stroke-width",0);
		    viewing.attr("fill", scrollBGColor);
		    viewing.attr("fill-opacity", 0.2);
		    viewing.hover(function() {
		    	this.data('label').show();
		    	this.attr("fill-opacity", 0.6);
		    },function() {
		    	this.data('label').hide();
				this.attr("fill-opacity", 0.2);
		    });
		}
		
		//Annotations
		var xPositions = new Array();
		for(var annotationtype in annotationtypes) {
		    var annot = annotationtypes[annotationtype];
		    var posX = axisOffset;
		    var totalWidth = (width-axisOffset)-18;
		    for(var i=1; i<annot.page_no; i++) {
		    	posX += pages[i]/100*totalWidth;
		    }
		    posX += (pages[annot.page_no-1]/100*totalWidth)*(annot.y_percentage);
		    var xSamePosition = 0;
		    for(var sameX in xPositions) {
		    	var diff = Math.abs(posX - xPositions[sameX]);
		    	if(diff < 30) {
			    	xSamePosition++;
		    	}
		    }
		    xPositions.push(posX);
		    posX += 20;
		    var posY = 10+height-40-40-(50*xSamePosition);
		    var annotCircle = paper.circle(posX, posY, annotSize);
		    var annotTitle = annot.type;
		    if(annot.type == 'Text') {
			    annotTitle += '\n"'+annot.title+'"';
		    }
		    if(annot.type == 'Recording') {
		    	annotTitle += '\n"'+annot.title+'"';
			    annotTitle += '\n'+annot.duration+' seconds';
		    }
		    annotCircle.data({
		    	'x':posX,
		    	'y':posY,
		    	'tooltip':annotTitle,
		    	'y-offset':-20
		    });
		    annotCircle.attr("fill", annotBGColor);
		    annotCircle.attr("stroke", annotColor);
		    annotCircle.attr("stroke-width", 2);
		    var annotText = paper.text(posX,posY,annot.type.substr(0,1));
		    annotText.attr('fill',annotColor);
		    annotText.data({'circ':annotCircle});
		    annotCircle.hover(function() {
		    	this.attr("fill","#333");
		    	setTooltip(this);
		    },function() {
			    this.attr("fill",annotBGColor);
			    hideTooltip();
		    });
		    annotText.hover(function() {
		    	this.data('circ').attr("fill","#333");
		    	setTooltip(this.data('circ'));
		    },function() {
			    this.data('circ').attr("fill",annotBGColor);
			    hideTooltip();
		    });
		}
		
		//tooltip
		var tooltip = paper.rect(40, 40, 50, 50, 10);
		var tooltiptext = paper.text(65, 65, "Raphaël\nkicks\nbutt!");
		tooltip.hide();
		tooltiptext.hide();
		
		function setTooltip(object) {
			var tooltipWidht = 120;
			var tooltipHeight = 60;
			tooltip.show();
			tooltiptext.show();
			tooltip.attr('x',object.data('x')-tooltipWidht/2);
			tooltip.attr('y',object.data('y')-tooltipHeight+object.data('y-offset'));
			tooltip.attr('stroke','#fff');
			tooltip.attr('stroke-width','2');
			tooltip.attr('fill','#333');
			tooltip.attr('fill-opacity','0.7');
			tooltiptext.attr('fill','#fff');
			tooltip.attr('width',tooltipWidht);
			tooltip.attr('height',tooltipHeight);
			tooltiptext.attr('x',tooltip.attr('x')+(tooltip.attr('width')/2));
			tooltiptext.attr('y',tooltip.attr('y')+(tooltip.attr('height')/2));
			tooltiptext.attr('text',object.data('tooltip'));
		}
		
		function hideTooltip() {
			tooltip.hide();
			tooltiptext.hide();
		}
		
	}
	
	function showrawdata(runnum) {
	    var pre = $('#rawdata_'+runnum).find('pre');
	    var a = $('#rawdata_'+runnum).find('a');
	    if(pre.css("display") == "block") {
	    	pre.fadeOut('fast');
	    	a.text("Show data");
	    } else {
	    	pre.fadeIn();
	    	a.text("Hide data");
	    }
	}
</script>


<script type='text/javascript'>
function playPauseAudio(id) {
	var htmlAudio = $('#'+id).get(0);
	if(htmlAudio.paused) {
		htmlAudio.play();
	} else {
		htmlAudio.pause();
	}
}
$(document).ready(function() {
	var audioFiles = $('audio');
	audioFiles.each(function() {
		var theAudio = this;
		theAudio.addEventListener("timeupdate", updateProgress, false);
		theAudio.addEventListener('ended', function() {
			this.currentTime = 0;
		    this.play();
    	}, false);
	});
});
function updateProgress() {
	var progressid = $(this).attr('id').replace('audioplayer_','audioprogress_');
	var progressBar = $('#'+progressid);
	var value = 0;
	if (this.currentTime > 0) {
    	value = Math.floor((100 / this.duration) * this.currentTime);
    }
    progressBar.css('width',value*6);
}
/*function showrawdata(runnum) {
	var pre = $('#rawdata_'+runnum).find('pre');
	var a = $('#rawdata_'+runnum).find('a');
	if(pre.css("display") == "block") {
		pre.fadeOut('fast');
		a.text("Show data");
	} else {
		pre.fadeIn();
		a.text("Hide data");
	}
}*/
</script>





<style type='text/css'>
	div.audiowrapper {
		width:662px;
		overflow:auto;
	}
	div.audiowrapper div.audiobox {
		height:60px;
		border:1px solid #333;
		margin-top:10px;
		position:relative;
	}
	div.audiowrapper div.audioname {
		float:left;
		font-size:80%;
	}
	div.audiowrapper div.duration {
		font-size:80%;
		float:right;
	}
	div.audiowrapper div.audiointeraction {
		height:60px;
		background:rgba(200,200,255,0.2);
		position:absolute;
		top:0;
	}
	div.graph {
		margin-top:20px;
	}
	div.session_progress {
		border:1px dashed orange;
		padding:20px;
		text-align:center;
		color:orange;
		background:#ffe;
		margin:20px;
	}
</style>