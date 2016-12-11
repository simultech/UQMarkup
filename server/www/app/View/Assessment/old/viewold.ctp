<?php
//The marks (may have empty)
/*
echo "Marks: <br />";
print_r($marks);
*/

//The rubrics for the project
/*
echo "<br />Rubrics: <br />";
print_r($rubrics);
*/
$annotations = array();
$annotations['audio'] = array();
foreach($annots as $annot) {
	$annot = get_object_vars($annot);
	if($annot['type'] == 'Recording') {
		$annot['page'] = $annot['page_no'];
		$annot['name'] = $annot['title'];
		$annot['duration'] = gmdate("i:s",$annot['duration']);
		$annot['xpos'] = $annot['x_percentage']*100;
		$annot['ypos'] = $annot['y_percentage']*100;
		$annot['filename'] = str_replace(".m4a", ".mp3", $annot['filename']);
		$annotations['audio'][] = $annot;
	}
}
?>

<style type="text/css">
div#marks {
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
	top:-48px;
	left:620px;
	font-style:italic;
}

</style>

<div id='zoomhintwrapper'>
<p id='zoomhint'>To zoom, use ctrl+ and ctrl- (cmd+ and cmd-)</p>
</div>

<script src="<?php echo $baseURL ?>/js/audiojs/audio.js"></script>
<script type="text/javascript" src="<?php echo $baseURL ?>/js/pdf.js"></script>
<script type="text/javascript" src="<?php echo $baseURL ?>/js/pdfloader.js"></script>
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
	<div id='surveyholder'>
		<div id='surveynotice'>
			<a href='<?php echo $baseURL; ?>/surveys/survey/student/<?php echo $project_id; ?>' class='btn btn-small btn-warning'>Please tell us about your experience in using UQMarkUP and utilising the feedback provided.  It should take around 10 minutes to complete.</a>
		</div>
	</div>
<?php
	}
?>
<style type='text/css'>
	div#surveynotice {
		position:absolute;
		top:-98px;
		right:0;
	}
	div#surveyholder {
		position:relative;
	}
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
		<audio preload></audio>
	</div>
	<div id='readerframe'>
		<div id='reader'></div>
	</div>
	
	<div id='marksframe' style="display: none;">
	   <?php 
	   if (count($marks) > 0) {
	       echo $this->element('markedrubrics'); 
	   }
	   ?>
	</div>
</div>







<script type="text/javascript">

	var audio;
	var playingTriggered = false;
	var playingannotation = -1;
	var lastplayingannotation = -1;
	var playingannotationtime = "00:00";
	
	$(window).resize(function() {
		resizePage();
  	});
  	
  	$(document).ready(function() {
	  resizePage();
  	});
  	
  	function resizePage() {
		var offset = $('div#content').offset();
		$('div#content').height($(window).height()-offset.top-79);
		$('div#annotationframe').height($(window).height()-offset.top-139);
		$('div#readerframe').height($(window).height()-offset.top-139);
		$('ul#audio_annotations').height($(window).height()-offset.top-264);
  	}
	
	audiojs.events.ready(function() {
    	var as = audiojs.createAll({
	    	autoplay:true,loop:true,
    	});
    	audio = as[0];
    	audio.settings.callbacks.play = function() {
    		var time_current = $(".audiojs div.time em").text();
    		var time_duration = $(".audiojs div.time strong").text();
   			if(playingannotation > -1) {
	    		annotations[playingannotation].icon.addClass('audioannotation_icon_selected');
	    	}
	    	var loggedannotation = playingannotation;
	    	if(loggedannotation == -1) {
		    	loggedannotation = lastplayingannotation;
	    	}
	    	if(audio.duration > 0) {
		    	presenterlog(2,"Audio",'{"state":"playing","annotation":"'+loggedannotation+'","currentTime":"'+time_current+'","duration":"'+time_duration+'"}');
		    }
    	}
    	audio.settings.callbacks.pause = function() {
    		var time_current = $(".audiojs div.time em").text();
    		var time_duration = $(".audiojs div.time strong").text();
    		var loggedannotation = playingannotation;
    		if(loggedannotation == -1) {
		    	loggedannotation = lastplayingannotation;
	    	}
	    	presenterlog(2,"Audio",'{"state":"pause","annotation":"'+loggedannotation+'","currentTime":"'+time_current+'","duration":"'+time_duration+'"}');
	    	playingannotation = -1;
	    	removePlayingAnnotationsIconHighlight();
	    	playingTriggered = false;
    	}
    	audio.settings.callbacks.trackEnded = function() {
    		var time_current = $(".audiojs div.time em").text();
    		var time_duration = $(".audiojs div.time strong").text();
    		var loggedannotation = playingannotation;
    		if(loggedannotation == -1) {
		    	loggedannotation = lastplayingannotation;
	    	}
	    	presenterlog(2,"Audio",'{"state":"finished","annotation":"'+loggedannotation+'","currentTime":"'+time_current+'","duration":"'+time_duration+'"}');
	    	removePlayingAnnotationsIconHighlight();
	    	playingTriggered = false;
    	}
    	audio.settings.callbacks.skipTo = function() {
    		var time_current = $(".audiojs div.time em").text();
    		var time_duration = $(".audiojs div.time strong").text();
    		var loggedannotation = playingannotation;
    		if(loggedannotation == -1) {
		    	loggedannotation = lastplayingannotation;
	    	}
	    	presenterlog(2,"Audio",'{"state":"pause","annotation":"'+loggedannotation+'","currentTime":"'+playingannotationtime+'","duration":"'+time_duration+'"}');
	    	setTimeout(function() {
		    	presenterlog(2,"Audio",'{"state":"skip","annotation":"'+loggedannotation+'","currentTime":"'+time_current+'","duration":"'+time_duration+'"}');
	    	},300);
    	}
    	setInterval(function(){
	    	playingannotationtime = $(".audiojs div.time em").text();
    	},500);
    });
    
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
    loadPDF(url);
    	
    
    function play_annotation(annotation_id,shouldscroll) {
    	removePlayingAnnotationsIconHighlight();
    	lastplayingannotation = annotation_id;
    	if(playingannotation != annotation_id) {
	    	if(shouldscroll) {
    			scrollTo(annotations[annotation_id]);
    		}
		    playingannotation = annotation_id;	
		    console.log(baseaudiourl+annotations[annotation_id].filename);
		    audio.load(baseaudiourl+annotations[annotation_id].filename);
		    audio.play();
    	} else {
	    	audio.pause();
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
		
		var $marksSheet = $('div#marks');
		if ($marksSheet.children().length) {
    		var $newPage = $("<div class='page' width='595' style='width:595px' />");
    		$newPage.append($marksSheet);
    		$('#reader').append($newPage);
		}
		var pagelengths = new Array();
		var total = 0;
		for(var i=0; i<$('#reader').children().length; i++) {
			var child = $('#reader').children();
			child = child[i];
			pagelengths.push($(child).height()/$("#reader").height());
			total += $(child).height()/$("#reader").height();
		}
		var extrapadding = (1-total) / +$('#reader').children().length;
		var pagelengthstring = "";
		for(j=0; j<pagelengths.length; j++) {
			pagelengths[j] += extrapadding;
			pagelengths[j] = (Math.round(pagelengths[j]*1000)/10);
		}
		pagelengthstring = pagelengths.join(',');
		setTimeout(function() {
			presenterlog(2,"Details",'{"pages":"'+$('#reader').children().length+'","pagelengths":"'+pagelengthstring+'"}');
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
		    var submission_id = <?php echo $submission['Submission']['id']; ?>;
		    var logurl = "<?php echo $baseURL; ?>/logs/presenter/"+submission_id+"/"+logtype_id+"/"+interaction+"/"+encodeURIComponent(target);
		    //console.log(logurl);
		    $.ajax({
		      url: logurl,
		      cache: false,
		      async : doasync,
		    }).done(function( html ) {
		        if(target == '{"state":"opened"}') {
		        	presenterlog(2,"Annotations",'<?php echo str_replace("'","\'",json_encode($annotations)); ?>');
		        }
		        console.log("LOGGED TYPE: "+logtype_id+", INTERACTION: "+interaction+", TARGET: "+target.substring(0,100));
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
		var totalheight = Math.round($("#reader").height() - $("#readerframe").height());
		var percentscrolled = (Math.round((offset/totalheight*1000)))/10;
		if(percentscrolled > 100) {
			percentscrolled = 100;
		}
		return percentscrolled;
    }
    
    function setScrollingTimeout() {
	    scrollTimeout = setTimeout(function() {
	    	var pagesize = Math.round($("#reader").height() / $("#readerframe").height());
	    	presenterlog(2,"Scroll",'{"start":"'+startScroll+'","end":"'+getCurrentScrollPosition()+'","pagesize":"'+pagesize+'"}');
			isScrolling = false;
			scrollTimeout = false;
		}, 500);
    }
    
    function logCurrentScroll() {
	    var pagesize = Math.round($("#reader").height() / $("#readerframe").height());
	    if(startScroll == undefined) {
		    startScroll = 0;
	    }
	    var endpos = getCurrentScrollPosition();
	    if(endpos < 0) {
		    endpos = 0;
	    }
	    presenterlog(2,"Scroll",'{"start":"'+startScroll+'","end":"'+endpos+'","pagesize":"'+pagesize+'"}');
    }
    
</script>