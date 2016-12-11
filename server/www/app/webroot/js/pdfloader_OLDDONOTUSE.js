	PDFJS.disableWorker = true;
	var pdf;
	
	function loadPDF(url) {
    	PDFJS.getDocument(url).then(function getPdfHelloWorld(_pdf) {
    	  	var pagesCount = _pdf.numPages;
	      	console.log(pagesCount);
      		var viewer = $('#reader');
    	  	for(var i=1; i<_pdf.numPages+1; i++) {
	      		var pagecontainer = $('<div class="page"></div>');
      			viewer.append(pagecontainer);
      			var pageCanvas = $('<canvas></canvas>');
    	  		pagecontainer.append(pageCanvas);
	      		loadPage(_pdf,i,pagecontainer);
      		}
      		pdf = _pdf;
      		console.log("DONE");
      		loadedPDF();
    	});
    }
    
    var annotationWidth = 30;
	var annotationHeight = 30;
	
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
		$("#readerframe").animate({scrollTop:pageoffset},1000);
	}
	
	function getPDFPageDimensions(page,usemargins) {
		var obj = new Object();
		obj.width = $($('.annotationLayer').get(page-1)).parent().outerWidth(usemargins);
		obj.height = $($('.annotationLayer').get(page-1)).parent().outerHeight(usemargins);
		return obj;
	}
	
	function getPDFAnnotationLayerForPage(page) {
		return $($('.annotationLayer').get(page-1));
	}
    
    function loadPage(pdf,pageNumber,pagecontainer) {
    	var frameWidth = 640;
        pdf.getPage(pageNumber).then(function getPageHelloWorld(page) {
        	console.log(page);
	    	var scale = 1;
		   	var viewport = page.getViewport(scale);
		   	var canvas = pagecontainer.find('canvas')[0];
		   	var pageWidth = page.view[2]*scale;
		   	pagecontainer.css({'width':pageWidth});
		   	if(pageWidth > frameWidth) {
			   	pagecontainer.css({'margin-left':10});
			   	pagecontainer.css({'margin-right':60});
			   	$('reader').width(pageWidth);
		   	}
		   	pagecontainer.css({'height':page.view[3]});
		   	
			var textLayerDiv = document.createElement('div');
			textLayerDiv.className = 'textLayer';
			pagecontainer[0].appendChild(textLayerDiv);
		    var textLayer = new TextLayerBuilder(textLayerDiv);
	   	    var renderContext = {
				canvasContext: canvas.getContext('2d'),
				viewport: viewport,
				textLayer: textLayer,
			};
	       	canvas.height = viewport.height;
	        canvas.width = viewport.width;
	        page.render(renderContext);
			
			var annotationLayerDiv = document.createElement('div');
			annotationLayerDiv.className = 'annotationLayer';
			pagecontainer[0].appendChild(annotationLayerDiv);
		});
    }
    
    /*
    var audio = $("<a href='javascript:play_annotation(1);' class='audio'></a>");
			audio.css({'top':'250px','left':'200px'});
			$(annotationLayerDiv).append(audio);
	*/
    
    
    
    var TextLayerBuilder = function textLayerBuilder(textLayerDiv) {
  		this.textLayerDiv = textLayerDiv;
		this.beginLayout = function textLayerBuilderBeginLayout() {
			this.textDivs = [];
			this.textLayerQueue = [];
  		};
		this.endLayout = function textLayerBuilderEndLayout() {
			var self = this;
			var textDivs = this.textDivs;
			var textLayerDiv = this.textLayerDiv;
			var renderTimer = null;
			var renderingDone = false;
			var renderInterval = 0;
			var resumeInterval = 500; // in ms
    		function renderTextLayer() {
				if (textDivs.length === 0) {
					clearInterval(renderTimer);
					renderingDone = true;
					return;
				}
				var textDiv = textDivs.shift();
				if (textDiv.dataset.textLength > 0) {
					textLayerDiv.appendChild(textDiv);
					if (textDiv.dataset.textLength > 1) { // avoid div by zero
						// Adjust div width to match canvas text
						// Due to the .offsetWidth calls, this is slow
						// This needs to come after appending to the DOM
						var textScale = textDiv.dataset.canvasWidth / textDiv.offsetWidth;
						CustomStyle.setProp('transform' , textDiv,
						  'scale(' + textScale + ', 1)');
						CustomStyle.setProp('transformOrigin' , textDiv, '0% 0%');
					}
				} // textLength > 0
			}
    		renderTimer = setInterval(renderTextLayer, renderInterval);

			// Stop rendering when user scrolls. Resume after XXX milliseconds
			// of no scroll events
			var scrollTimer = null;
			function textLayerOnScroll() {
				if (renderingDone) {
					window.removeEventListener('scroll', textLayerOnScroll, false);
					return;
				}

				// Immediately pause rendering
				clearInterval(renderTimer);

				clearTimeout(scrollTimer);
				scrollTimer = setTimeout(function textLayerScrollTimer() {
					// Resume rendering
					renderTimer = setInterval(renderTextLayer, renderInterval);
				}, resumeInterval);
			}; // textLayerOnScroll

			window.addEventListener('scroll', textLayerOnScroll, false);
		}; // endLayout

		this.appendText = function textLayerBuilderAppendText(text, fontName, fontSize) {
			var textDiv = document.createElement('div');

			// vScale and hScale already contain the scaling to pixel units
			var fontHeight = fontSize * text.geom.vScale;
			textDiv.dataset.canvasWidth = text.canvasWidth * text.geom.hScale;
			textDiv.dataset.fontName = fontName;

			textDiv.style.fontSize = fontHeight + 'px';
			textDiv.style.left = text.geom.x + 'px';
			textDiv.style.top = (text.geom.y - fontHeight) + 'px';
			textDiv.textContent = PDFJS.bidi(text, -1);
			textDiv.dir = text.direction;
			textDiv.dataset.textLength = text.length;
			this.textDivs.push(textDiv);
		};
	};



	// optimised CSS custom property getter/setter
	var CustomStyle = (function CustomStyleClosure() {

		// As noted on: http://www.zachstronaut.com/posts/2009/02/17/
		//              animate-css-transforms-firefox-webkit.html
		// in some versions of IE9 it is critical that ms appear in this list
		// before Moz
		var prefixes = ['ms', 'Moz', 'Webkit', 'O'];
		var _cache = { };

		function CustomStyle() {
		}

		CustomStyle.getProp = function get(propName, element) {
			// check cache only when no element is given
			if (arguments.length == 1 && typeof _cache[propName] == 'string') {
				return _cache[propName];
			}

			element = element || document.documentElement;
			var style = element.style, prefixed, uPropName;

			// test standard property first
			if (typeof style[propName] == 'string') {
				return (_cache[propName] = propName);
			}

			// capitalize
			uPropName = propName.charAt(0).toUpperCase() + propName.slice(1);

			// test vendor specific properties
			for (var i = 0, l = prefixes.length; i < l; i++) {
				prefixed = prefixes[i] + uPropName;
				if (typeof style[prefixed] == 'string') {
					return (_cache[propName] = prefixed);
				}
    		}

			//if all fails then set to undefined
			return (_cache[propName] = 'undefined');
		}

		CustomStyle.setProp = function set(propName, element, str) {
			var prop = this.getProp(propName);
			if (prop != 'undefined') element.style[prop] = str;
  		}

		return CustomStyle;
	})();
	
	
/**
 * jQuery.fn.sortElements
 * --------------
 * @param Function comparator:
 *   Exactly the same behaviour as [1,2,3].sort(comparator)
 *   
 * @param Function getSortable
 *   A function that should return the element that is
 *   to be sorted. The comparator will run on the
 *   current collection, but you may want the actual
 *   resulting sort to occur on a parent or another
 *   associated element.
 *   
 *   E.g. $('td').sortElements(comparator, function(){
 *      return this.parentNode; 
 *   })
 *   
 *   The <td>'s parent (<tr>) will be sorted instead
 *   of the <td> itself.
 */
jQuery.fn.sortElements = (function(){
 
    var sort = [].sort;
 
    return function(comparator, getSortable) {
 
        getSortable = getSortable || function(){return this;};
 
        var placements = this.map(function(){
 
            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,
 
                // Since the element itself will change position, we have
                // to have some way of storing its original position in
                // the DOM. The easiest way is to have a 'flag' node:
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );
 
            return function() {
 
                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }
 
                // Insert before flag:
                parentNode.insertBefore(this, nextSibling);
                // Remove flag:
                parentNode.removeChild(nextSibling);
 
            };
 
        });
 
        return sort.call(this, comparator).each(function(i){
            placements[i].call(getSortable.call(this));
        });
 
    };
 
})();
	