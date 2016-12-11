<?php

$annotations = array();
$annotations['audio'] = array();
foreach($annots as $annot) {
	$annot = get_object_vars($annot);
	if($annot['type'] == 'Recording') {
		if(isset($annot['filename'])) {
			$annot['page'] = $annot['page_no'];
			$annot['name'] = $annot['title'];
			$annot['duration'] = gmdate("i:s",$annot['duration']);
			$annot['xpos'] = $annot['x_percentage']*100;
			$annot['ypos'] = $annot['y_percentage']*100;
			$annot['filename'] = str_replace(".m4a", ".mp3", $annot['filename']);
			$annotations['audio'][] = $annot;
		}
	}
}
?>
<script type='text/javascript'>
(function() {
  try {
    var a = new Uint8Array(1);
    return; //no need
  } catch(e) { }
  alert("Your browser does not currently support HTML based PDF rendering.\n\nPlease download the latest version of Google Chrome, Safari, Firefox, or install Google Chrome Frame for Internet Explorer.");
})();
</script>
<style type="text/css">
div#marks {
    background: #fff;
    width: 475px;
    padding: 36px 60px;
    font-size: 0.7em;
}
div#survey {
    background: #fff;
    width: 475px;
    padding: 36px 60px;
    font-size: 0.7em;
}

table.table_rubric {
    max-width: 475px;
    table-layout: fixed;
}

div.rubric_header {
    font-size: 1.1em;
    font-weight: bold;
    line-height: 1.4em;
    margin: 24px 0 6px 0;
}

div.rubric_header:first-child {
    margin-top: 0;
}

th.rubric_option_header {
    background: #333;
    color: #fff;
    font-weight: bold;
}

td.rubric_option_body {
    vertical-align: top;
}

.marked {
    background: #dfd url('/_dev/img/rubriccheckbox.png') no-repeat center center;
}

#zoomhintwrapper {
	position:relative;
}

#zoomhint {
	position: absolute;
	color:green;
	right:50px;
	font-style:italic;
}

div#content {
	padding:0;
	width:980px;
}
ul#audio_annotations {
	background:#fff;
}
div#contentwrapper {
	width:100%;
	background:#fff;
	border:0;
	padding-top:3px;
}
div#content {
	width:100%;
}
div#content ul.breadcrumb {
	width:900px;
	margin:0 auto !important;
	position:relative;
	padding-left:220px;
	background:none;
}

div#header h1 a.logo {
	position:absolute;
	height:160px;
}
div#content ul.breadcrumb {
	margin:0 20px 10px 20px;
}
div#surveynotice {
	position:absolute;
	top:-76px;
	right:20px;
}
div#surveyholder {
	position:relative;
}
a.jp-play,a.jp-pause {
	display:block;
	width:35px;
	height:35px;
	background:#333;
	position:absolute;
	top:0;
	left:0;
	text-indent:-10000px;
	background:#444 url('/_dev/js/jplayer/player-graphics.gif') 3px 3px;
	border-right:1px solid #222;
}
a.jp-pause {
	background:green;
	background:#333 url('/_dev/js/jplayer/player-graphics.gif') 3px 33px;
}
div#jp_container_1 {
	position:relative;
	background:#666;
	height:35px;
	color:#fafafa;
	border-top:1px solid #222;
}
div#jp_container_1 p {
	height:40px;
	float:right;
	font-size:80%;
	padding:0 5px;
	padding-top:0;
	margin-top:-5px;
}
div#jp_container_1 p.name {
	float:left;
	margin-left:36px;
}
div.jp-progress {
	border:1px solid #222;
	height:10px;
	margin:4px;
	margin-left:40px;
	background:#aaa;
}
div.jp-seek-bar {
	background:#ddd;
	cursor: hand; cursor: pointer;
}
div.jp-play-bar {
	background:#60419D;
	height:10px;
	cursor: hand; cursor: pointer;
}
ul.nav li.firsttab {
	margin-left:260px;
}
ul.nav li a {
	color:#60419D;
}
ul.nav li.active a {
	background:#60419D;
	border-radius:10px 10px 0 0;
	color:#fff;
}
ul.nav li.active a:hover {
	background:#60419D;
	border-radius:10px 10px 0 0;
	color:#fff;
}
ul.nav {
	margin-bottom:0;
}
div#details {
	position:absolute;
	top:0;
	left:0;
	background:#333;
	color:#fff;
	padding:3px;
	border:1px solid #fff;
}
<?php
if($nowrapper=='nowrapper') {
?>
div#header h1 {
	display:none;
}
div#header {
	height:27px;
}
<?php
}
?>
a#toggleheader {
	position:fixed;
	top:0;
	right:0;
	z-index:300;
	width:18px;
	height:18px;
	border:1px solid #333;
	text-align:center;
	color:#ddf;
	background:#333;
	text-decoration:none;
}
</style>
<script>
function toggleHeader() {
	console.log("HELLO");
	if($('a#toggleheader').hasClass('hiddenheader')) {
		$('a#toggleheader').removeClass('hiddenheader');
		$('div#header').css({'margin-top':'0px'});
		$('a#toggleheader').text('^');
		$('div#details').css({'display':'block'});
		resizePage();
	} else {
		$('a#toggleheader').addClass('hiddenheader');
		$('div#header').css({'margin-top':'-134px'});
		$('a#toggleheader').text('v');
		$('div#details').css({'display':'none'});
		resizePage();
	}
}
</script>
<a id='toggleheader' href='javascript:toggleHeader();'>^</a>
<div id='details'><?php echo $details; ?><br />
<a href='<?php echo $baseURL; ?>/assessment/view_old/<?php echo $submission_id_hash; ?>'>Reader not behaving?  Try the old player here.</a>
</div>
<div id='zoomhintwrapper'>
<?php
if($downloadable) {
	echo $this->Html->link('Download feedback',array('action'=>'download',$submission_id_hash),array('class'=>'btn'));
	echo '&nbsp;&nbsp;&nbsp;';
}
?>
Zoom: 
<select id='zoom_select'>
	<option value="2">200%</option>
	<option value="1.75">175%</option>
	<option value="1.5">150%</option>
	<option value="1.25">125%</option>
	<option selected='selected' value="1">100%</option>
	<option value=".75">75%</option>
	<option value=".5">50%</option>
	<option value=".25">25%</option>
</select>
<!--<p id='zoomhint'>To zoom, use ctrl+ and ctrl- (cmd+ and cmd-)</p>-->
</div>

<?php
	if($multiplemarkers) {
?>
<ul class="nav nav-tabs">
<?php
	$i=1;
	foreach($themarkers as $themarker) {
		$activetab = '';
		if(isset($selectedmarker)) {
			if($selectedmarker == $themarker) {
				$activetab = 'active';
			}
		}
		if($i == 1) {
			if($selectedmarker == '') {
				$activetab = 'active';
			}
			$activetab .= ' firsttab';
		}
		if($activetab != '') {
			$activetab = 'class="'.$activetab.'"';
		}
		echo '<li '.$activetab.'><a href="'.$baseURL.'/assessment/view/'.$submission_id_hash.'/false/'.$themarker.'">Marker '.$i.'</a></li>';
		$i++;
	}
?>
</ul>
<?php
}
?>

<script src="<?php echo $baseURL ?>/js/jplayer/jquery.jplayer.min.js"></script>
<script src="<?php echo $baseURL ?>/js/audiojs/audio.js"></script>
<script type="text/javascript" src="<?php echo $baseURL ?>/js/pdf_new/pdf.js"></script>
<script type="text/javascript" src="<?php echo $baseURL ?>/js/pdf_new/pdfloader.js"></script>
<script type="text/javascript" src="<?php echo $baseURL ?>/js/flashdetect.min.js"></script>
<script type="text/javascript">
	if (navigator.userAgent.indexOf("Firefox")!=-1) {
		if(!FlashDetect.installed){
			var conf = confirm("Adobe Flash is not installed on this computer.\n\nPlease install Adobe Flash or use Google Chrome to view your feedback.\n\nClick OK to continue to download Adobe Flash.\nOnce Adobe Flash is installed, restart your browser.");
			if(conf == true) {
				window.location = "http://get.adobe.com/flashplayer/";
			} else {
				alert("If you cannot install Adobe Flash, please use Google Chrome to view your feedback");
			}
		}
	}
</script>
<?php
	if($logactions && $surveyavailable) {
?>
	<!--
<div id='surveyholder'>
		<div id='surveynotice'>
			<a href='<?php echo $baseURL; ?>/surveys/survey/student/<?php echo $project_id; ?>' class='btn btn-small btn-warning'>Please tell us about your experience in using UQMarkUP and utilising the feedback provided.  It should take around 10 minutes to complete.</a>
		</div>
	</div>
-->
<?php
	}
?>
<style type='text/css'>
	
</style>
<div id='readerpage'>
	<div id='annotationframe'>
		<h3>Audio Annotations</h3>
		<div id='audioorder'>
			<label>Order by </label>
			<select id="audioorderselect">
				<option>Page</option>
				<option>Name</option>
				<option>Length</option>
			</select>
		</div>
		<ul id='audio_annotations'></ul>
		<div id="jquery_jplayer_1"></div>
		<div id="jp_container_1">
			<div class="jp-progress">
				<div class="jp-seek-bar">
					<div class="jp-play-bar"></div>
				</div>
			</div>
			 <p class='name'>
			 	Choose an annotation
			 </p>
			 <p class='right'>
				 <span class="jp-current-time"></span> /
				 <span class="jp-duration"></span>
			 </p>
			 <a href="#" class="jp-play">Play</a>
			 <a href="#" class="jp-pause">Pause</a>
 		</div>
	</div>
	<div id='readerframe'>
		<div id='reader'></div>
	</div>
	<div id='surveyframe' style="display: none;">
		<?php
		if($logactions && $surveyavailable) {
	       	echo $this->element('survey'); 
	    }
	    ?>
	</div>
	<div id='marksframe' style="display: none;">
	   <?php 
	   if (count($marks) > 0) {
	       echo $this->element('markedrubrics'); 
	   }
	   ?>
	</div>
</div>

<!--<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Complete the feedback form</h3>
  </div>
  <div class="modal-body">
    <p>How much did you engage (read, listen to, consider ..) with the feedback</p>
    <p>Could you understand the feedback?</p>
    <p>How useful did you find the following forms of feedback?</p>
    <p>Do you think the feedback was consistent with the mark you received?</p>
    <p>How useful is this information in preparing your responses to future assessment?</p>
    <p>How easy was it to access your marked report?</p>
  </div>
  
  <div class="modal-footer">
    <button class="btn btn-primary">Save changes</button>
  </div>
</div>-->

<style>
	div.reader_wrapper {
		transform-origin:50% 0;
		-webkit-transform-origin:50% 0;
		-ms-transform-origin:50% 0;
		-o-transform-origin:50% 0;
		-moz-transform-origin:50% 0;
		/*
		transition-duration: 0.5s;
		-ms-transition-duration: 0.5s;
		-moz-transition-duration: 0.5s;
		-webkit-transition-duration: 0.5s;
		-o-transition-duration: 0.5s;
		*/
		width:900px;
		margin:0 auto;
	}
	div#footer {
		position:absolute;
		bottom:0;
		width:100%;
	}
	div#zoomhintwrapper {
		float:right;
		margin-top:-34px;
		margin-right:24px;
	}
	div#zoomhintwrapper select {
		margin:0;
	}
	div#footer {
		padding:5px 5px;
	}
</style>

<script type="text/javascript">

	function showMarks() {
		alert("ZING");
	}

	var playingannotation = -1;
	var playerpaused = false;
	
	$(window).resize(function() {
		resizePage();
  	});
  	
  	$(document).ready(function() {
	  resizePage();
  	});
  	
  	function resizePage() {
  		var multipageoffset = 0;
  		<?php
  		if($multiplemarkers) {
	  		echo 'multipageoffset = 38;';
  		}
  		?>
  		var fullwidth = $('div#container').css('width');
  		$('div#readerpage').css({'width':fullwidth-30});
		var offset = $('div#content').offset();
		$('div#contentwrapper').height($(window).height()-offset.top-33-multipageoffset);
		$('div#content').height($(window).height()-offset.top-33-multipageoffset);
		$('div#annotationframe').height($(window).height()-offset.top-68-multipageoffset);
		$('div#readerframe').height($(window).height()-offset.top-68-multipageoffset);
		$('ul#audio_annotations').height($(window).height()-offset.top-192-multipageoffset);
  	}
    
	$('#audioorderselect').change(function() {
		sortAnnotations();
  	});
  	
  	function sortAnnotations() {
  		var sortOrder = $('#audioorderselect').val();
	  	$('#audio_annotations li').sortElements(function(a, b){
	  		if(sortOrder == "Name") {
				return $(a).text() > $(b).text() ? 1 : -1;
			} else if(sortOrder == "Page") {
				return $(a).find('span.badge').text() > $(b).find('span.badge').text() ? 1 : -1;
			} else if(sortOrder == "Length") {
				return $(a).find('em').text() > $(b).find('em').text() ? 1 : -1;
			} else {
				return 0;
			}
		});
  	}
	
	var annotations = <?php echo json_encode($annotations['audio']); ?>;
  		
  	var baseaudiourl = '<?php echo $baseURL ?>/assessment/audio/<?php echo $submission['Submission']['id']; ?>/';
    var url = '<?php echo $baseURL ?>/assessment/markedPDF/<?php echo $submission['Submission']['id']; ?>';
    var extraaudio = '';
    <?php
    	if(isset($selectedmarker) && $selectedmarker != '') {
    ?>
    		url += '/<?php echo $selectedmarker; ?>';
    		extraaudio += '?version=<?php echo $selectedmarker; ?>';
    <?php
    	}
    ?>
    loadPDF(url,'<?php echo $baseURL ?>');
    
    var startTime = 0;
    var initialReaderHeight = 0;
    var lastZoom = 1;
    
    $(document).ready(function() {
	   	$('#zoom_select').change(function() {
		   	console.log("ZOOOMING"+$(this).val());
		   	var theVal = $(this).val();
		   	$('div.reader_wrapper').css({
		   		'transform':'scale('+theVal+','+theVal+')',
		   		'-webkit-transform':'scale('+theVal+','+theVal+')',
		   		'-moz-transform':'scale('+theVal+','+theVal+')'
		   	});
		   	$('div.reader_outerwrapper').height(initialReaderHeight*theVal);
		   	var yOffset = $("#readerframe").scrollTop()/lastZoom*theVal;
		   	lastZoom = theVal;
		   	if($('div.reader_wrapper').width()*theVal > $('#reader').width()) {
			   	$('div.reader_wrapper').css({
			   		'transform-origin':'0 0',
		   			'-webkit-transform-origin':'0 0',
		   			'-ms-transform-origin':'0 0',
		   			'-moz-transform-origin':'0 0',
		   			'-o-transform-origin':'0 0',
		   			'margin':'0'
		   		});
		   	} else {
			   	$('div.reader_wrapper').css({
			   		'transform-origin':'50% '+0+'%',
		   			'-webkit-transform-origin':'50% 0'+0+'%',
		   			'-ms-transform-origin':'50% 0',
		   			'-moz-transform-origin':'50% 0',
		   			'-o-transform-origin':'50% 0',
		   			'margin':'0 auto'
		   		});
		   	}
		   	$("#readerframe").animate({scrollTop:yOffset},0);
		   	//$('#reader').css({'overflow':'auto'});
	   	});
    });
    
    /*Audio*/
    $(document).ready(function(){
	    $("#jquery_jplayer_1").jPlayer({
		    ready: function () {
			    /*$(this).jPlayer("setMedia", {
	                mp3: "/_dev/files/audio/1.mp3"
	            });*/
		    },
		    swfPath: "/_dev/js/jplayer",
		    supplied: "mp3",
		    wmode: "window"
		});
		$("#jquery_jplayer_1").bind($.jPlayer.event.play, function(event) { 
			startTime = getCurrentTime();
			if(playingannotation > -1) {
	    		annotations[playingannotation].icon.addClass('audioannotation_icon_selected');
	    	}
		});
		$("#jquery_jplayer_1").bind($.jPlayer.event.pause, function(event) { 
			if(getCurrentTime() != getDuration()) {
				logAudio('pause');
			}
			removePlayingAnnotationsIconHighlight();
		});
		$("#jquery_jplayer_1").bind($.jPlayer.event.ended, function(event) { 
			logAudio('finished');
			removePlayingAnnotationsIconHighlight();
			playerpaused = true;
			$("#jquery_jplayer_1").jPlayer("setMedia", {
				mp3: baseaudiourl+annotations[playingannotation].filename+extraaudio
			});
			$('#jp_container_1 p.name').html(annotations[playingannotation].title);
		});
		$("div.jp-seek-bar,jp-play-bar").mousemove(function(event) {
			if(isScrubbing) {
				var parentOffset = $(this).parent().offset(); 
				var relX = event.pageX - parentOffset.left;
				$("#jquery_jplayer_1").jPlayer("playHead",(relX/$(this).width()*100));
			}
		});
		$("div.jp-seek-bar,jp-play-bar").mousedown(function(event) {
			isScrubbing = true;
			logAudio('scrubbed');
		});
		$("div.jp-seek-bar,jp-play-bar").mouseup(function(event) {
			isScrubbing = false;
			var parentOffset = $(this).parent().offset(); 
			var relX = event.pageX - parentOffset.left;
			var newPercent = relX/$(this).width()*100;
			$("#jquery_jplayer_1").jPlayer("playHead",newPercent);
			startTime = newPercent/100*getDuration();
		});
	});
	
	function logAudio(state) {
		var time_current = getCurrentTime();
		if(state == 'finished') {
			time_current = getDuration();
		}
		presenterlog(2,"Audio",'{"state":"'+state+'","annotation":"'+playingannotation+'","fromTime":"'+startTime+'","currentTime":"'+time_current+'","duration":"'+getDuration()+'","filename":"'+annotations[playingannotation].filename+'"}');
	}
	
	var isScrubbing = false;
	
	function getCurrentTime() {
		return $("#jquery_jplayer_1").data("jPlayer").status.currentTime;
	}
	
	function getDuration() {
		return $("#jquery_jplayer_1").data("jPlayer").status.duration;
	}
	
	function scrollTo(annotation) {
		pageoffset = 0;
		for(var i=1; i<annotation.page; i++) {
			pageoffset += getPDFPageDimensions(i).height;
			pageoffset += 10;
		}
		pageoffset += 10 + parseInt(annotation.icon.css('top'));
		pageoffset -= ($('#readerframe').height()/2);
		if(pageoffset < 0) {
			pageoffset = 0;
		}
		$("#readerframe").animate({scrollTop:pageoffset*$('#zoom_select').val()},1000);
	}
    
    function play_annotation(annotation_id,shouldscroll) {
    	removePlayingAnnotationsIconHighlight();
    	if(playingannotation != annotation_id) {
	    	playingannotation = annotation_id;
	    	if(shouldscroll) {
    			scrollTo(annotations[annotation_id]);
    		}
	    	$("#jquery_jplayer_1").jPlayer("setMedia", {
				mp3: baseaudiourl+annotations[annotation_id].filename+extraaudio
			});
			$('#jp_container_1 p.name').html(annotations[playingannotation].title);
			$("#jquery_jplayer_1").jPlayer("play");
		} else {
			if(playerpaused) {
				$("#jquery_jplayer_1").jPlayer("play");
				playerpaused = false;
				if(shouldscroll) {
    				scrollTo(annotations[annotation_id]);
    			}
			} else {
				$("#jquery_jplayer_1").jPlayer("pause");
				playerpaused = true;
			}
		}
	}
	
	function removePlayingAnnotationsIconHighlight() {
		$('a.audioannotation_icon').each(function(){
    		if($(this).hasClass('audioannotation_icon_selected')) {
	    		$(this).removeClass('audioannotation_icon_selected');
	    	}
    	});
	}

	function loadedPDF() {
		$('#audio_annotations').empty();
		for(var annotation_id in annotations) {
			var ann_data = annotations[annotation_id];
			var annotation = $("<li><a href='javascript:play_annotation("+annotation_id+",true)'>"+ann_data['name']+"<em> "+ann_data['duration']+"</em> <span class='badge'>Page "+ann_data['page']+"</span></a></li>");
			$('#audio_annotations').append(annotation);
			annotations[annotation_id].icon = $("<a class='audioannotation_icon' href='javascript:play_annotation("+annotation_id+",false)'><em>"+ann_data['name']+"</em></a>");
			addAnnotation(annotations[annotation_id].icon,ann_data['page'],ann_data['xpos'],ann_data['ypos']);
		}
		sortAnnotations();
		logCurrentScroll();
		resizePage();
		var maxWidth = 0;
		$('.page').each(function(){
			if($(this).width() > maxWidth) {
				maxWidth = $(this).width();
			}
		});
		maxWidth+=22;
		$('.reader_wrapper').css({'width':maxWidth+'px'});
		var $surveySheet = $('div#survey');
		if ($surveySheet.children().length) {
    		var $newPage = $("<div class='page' width='595' style='width:595px' />");
    		$newPage.append($surveySheet);
    		$('#reader .reader_wrapper').append($newPage);
		}
		var $marksSheet = $('div#marks');
		if($marksSheet) {
			if ($marksSheet.children().length) {
    			var $newPage = $("<div class='page' width='595' style='width:595px' />");
    			$newPage.append($marksSheet);
    			$('#reader .reader_wrapper').append($newPage);
			}
		}
		var pagelengths = new Array();
		var total = 0;
		for(var i=0; i<$('#reader .reader_wrapper').children().length; i++) {
			var child = $('#reader .reader_wrapper').children();
			child = child[i];
			pagelengths.push($(child).height()/$("#reader").height());
			total += $(child).height()/$("#reader").height();
		}
		var extrapadding = (1-total) / +$('#reader .reader_wrapper').children().length;
		var pagelengthstring = "";
		for(j=0; j<pagelengths.length; j++) {
			pagelengths[j] += extrapadding;
			pagelengths[j] = (Math.round(pagelengths[j]*1000)/10);
		}
		initialReaderHeight = $('#reader').height();
		pagelengthstring = pagelengths.join(',');
		setTimeout(function() {
			presenterlog(2,"Details",'{"pages":"'+$('#reader .reader_wrapper').children().length+'","pagelengths":"'+pagelengthstring+'"}');
		}, 200);
	}
	
	function addAnnotation(annotation,page,xpercentage,ypercentage) {
		var dimensions = getPDFPageDimensions(page);
		var xpos = xpercentage/100*dimensions.width - annotationWidth+15;
		var ypos = ypercentage/100*dimensions.height - annotationHeight+15;
		annotation.css({'left':xpos,'top':ypos});
		getPDFAnnotationLayerForPage(page).append(annotation);
	}
	
	var logtypes = <?php echo json_encode($logtypes); ?>;
	
	function presenterlog(logtype_id,interaction,target,useasync) {
		<?php
		if($logactions) {
		?>
		    var doasync = true;
		    if(useasync && useasync == 'sync') {
		        doasync = false;
		    }
		    var run_hash = "<?php echo uniqid('',true); ?>";
		    var submission_id = <?php echo $submission['Submission']['id']; ?>;
		    var logurl = "<?php echo $baseURL; ?>/logs/presenter/"+run_hash+"/"+submission_id+"/"+logtype_id+"/"+interaction+"/"+encodeURIComponent(target);
		    $.ajax({
		      url: logurl,
		      cache: false,
		      async : doasync,
		    }).done(function( html ) {
		        if(target == '{"state":"opened"}') {
		        	presenterlog(2,"Annotations",'<?php echo str_replace("'","\'",json_encode($annotations)); ?>');
		        }
		        console.log("INTERACTION: "+interaction+", TARGET: "+target);
		    });
  		<?php
  		}
  		?>
	}
	
	//Open and close
	setupStartAndCloseDocuments();
	
	function setupStartAndCloseDocuments() {
		$(window).unload( function () { logCurrentScroll(); presenterlog(2,"Automatic",'{"state":"closed"}','sync'); } );
		presenterlog(2,"Automatic",'{"state":"opened"}');
	}
	
	var startScroll = 0;
	var isScrolling = false;
	var scrollTimeout = false;
	var scrollTime = new Date().getTime();

	
	$("#readerframe").scroll(function () { 
		if(!isScrolling) {
			startScroll = getCurrentScrollPosition();
			isScrolling = true;
			setScrollingTimeout();
		} else {
			if(scrollTimeout) {
				clearTimeout(scrollTimeout);
				setScrollingTimeout();
			}
		}
    });
    
    function getCurrentScrollPosition() {
	    var offset = Math.round(-1 * ($("#reader").offset().top - $("#readerframe").offset().top - 10)); //minus padding
		var totalheight = Math.round($("#reader").height());
		var percentscrolled = (Math.round((offset/totalheight*1000)))/10;
		if(percentscrolled > 100) {
			percentscrolled = 100;
		}
		if(percentscrolled < 0) {
			percentscrolled = 0;
		}
		return percentscrolled;
    }
    
    function setScrollingTimeout() {
	    scrollTimeout = setTimeout(function() {
	    	logScroll();
			isScrolling = false;
			scrollTimeout = false;
		}, 500);
    }
    
    function logCurrentScroll() {
	    if(startScroll == undefined) {
		    startScroll = 0;
	    }
	    logScroll();
    }
    
    function logScroll() {
    	var pagesize = Math.round($("#readerframe").height()/$("#reader").height()*100);
	    var endpos = getCurrentScrollPosition();
	    var now = new Date().getTime();
	    var timesincelastscroll = now - scrollTime;
	    scrollTime = now;
	    presenterlog(2,"Scroll",'{"start":"'+startScroll+'","end":"'+endpos+'","pagesize":"'+pagesize+'","timespentatstart":"'+timesincelastscroll+'"}');
    }
    
</script>