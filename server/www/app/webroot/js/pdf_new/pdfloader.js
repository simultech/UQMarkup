	//PDFJS.disableWorker = true;
	var pdf;
	
	function loadPDF(url,baseurl) {
		PDFJS.workerSrc = baseurl+'/js/pdf_new/pdf.worker.js';
  		PDFJS.getDocument(url).then(function getPdfHelloWorld(_pdf) {
    	  	var pagesCount = _pdf.numPages;
      		var viewer = $('#reader');
      		var viewer_outerwrapper = $('<div class="reader_outerwrapper">');
      		var viewer_wrapper = $('<div class="reader_wrapper">');
      		viewer.append(viewer_outerwrapper);
      		viewer_outerwrapper.append(viewer_wrapper);
    	  	for(var i=1; i<_pdf.numPages+1; i++) {
	      		var pagecontainer = $('<div class="page"></div>');
      			viewer_wrapper.append(pagecontainer);
      			var pageCanvas = $('<canvas></canvas>');
    	  		pagecontainer.append(pageCanvas);
	      		loadPage(_pdf,i,pagecontainer);
      		}
      		pdf = _pdf;
      		setTimeout(function() {
	      		loadedPDF();
      		}, 200);
    	});
    }
    
    function reloadPDF(url) {
	    $('#reader').empty();
	    loadPDF(url);
    }
    
    var annotationWidth = 30;
	var annotationHeight = 30;
	
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
    	var frameWidth = 1200;
        pdf.getPage(pageNumber).then(function getPageHelloWorld(page) {
	    	var scale = 1.5;
		   	var viewport = page.getViewport(scale);
		   	var canvas = pagecontainer.find('canvas')[0];
		   	var pageWidth = page.view[2]*scale;
		   	pagecontainer.css({'width':pageWidth});
		   	if(pageWidth > frameWidth) {
			   	pagecontainer.css({'margin-left':10});
			   	pagecontainer.css({'margin-right':60});
			   	$('reader').width(pageWidth);
		   	}
		   	pagecontainer.css({'height':page.view[3]*scale});
		   	
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
    
    
    
var TextLayerBuilder = function textLayerBuilder(options) {
  var textLayerFrag = document.createDocumentFragment();

  this.textLayerDiv = options.textLayerDiv;
  this.layoutDone = false;
  this.divContentDone = false;
  this.pageIdx = options.pageIndex;
  this.matches = [];
  this.lastScrollSource = options.lastScrollSource;
  this.viewport = options.viewport;
  this.isViewerInPresentationMode = options.isViewerInPresentationMode;
  this.textDivs = [];

  if (typeof PDFFindController === 'undefined') {
    window.PDFFindController = null;
  }

  if (typeof this.lastScrollSource === 'undefined') {
    this.lastScrollSource = null;
  }

  this.renderLayer = function textLayerBuilderRenderLayer() {
    var textDivs = this.textDivs;
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');

    // No point in rendering so many divs as it'd make the browser unusable
    // even after the divs are rendered
    var MAX_TEXT_DIVS_TO_RENDER = 100000;
    if (textDivs.length > MAX_TEXT_DIVS_TO_RENDER) {
      return;
    }

    for (var i = 0, ii = textDivs.length; i < ii; i++) {
      var textDiv = textDivs[i];
      if ('isWhitespace' in textDiv.dataset) {
        continue;
      }

      ctx.font = textDiv.style.fontSize + ' ' + textDiv.style.fontFamily;
      var width = ctx.measureText(textDiv.textContent).width;

      if (width > 0) {
        textLayerFrag.appendChild(textDiv);
        var textScale = textDiv.dataset.canvasWidth / width;
        var rotation = textDiv.dataset.angle;
        var transform = 'scale(' + textScale + ', 1)';
        transform = 'rotate(' + rotation + 'deg) ' + transform;
        CustomStyle.setProp('transform' , textDiv, transform);
        CustomStyle.setProp('transformOrigin' , textDiv, '0% 0%');
      }
    }

    this.textLayerDiv.appendChild(textLayerFrag);
    this.renderingDone = true;
    this.updateMatches();
  };

  this.setupRenderLayoutTimer = function textLayerSetupRenderLayoutTimer() {
    // Schedule renderLayout() if user has been scrolling, otherwise
    // run it right away
    var RENDER_DELAY = 200; // in ms
    var self = this;
    var lastScroll = (this.lastScrollSource === null ?
                      0 : this.lastScrollSource.lastScroll);

    if (Date.now() - lastScroll > RENDER_DELAY) {
      // Render right away
      this.renderLayer();
    } else {
      // Schedule
      if (this.renderTimer) {
        clearTimeout(this.renderTimer);
      }
      this.renderTimer = setTimeout(function() {
        self.setupRenderLayoutTimer();
      }, RENDER_DELAY);
    }
  };

  this.appendText = function textLayerBuilderAppendText(geom, styles) {
    var style = styles[geom.fontName];
    var textDiv = document.createElement('div');
    this.textDivs.push(textDiv);
    if (!/\S/.test(geom.str)) {
      textDiv.dataset.isWhitespace = true;
      return;
    }
    var tx = PDFJS.Util.transform(this.viewport.transform, geom.transform);
    var angle = Math.atan2(tx[1], tx[0]);
    if (style.vertical) {
      angle += Math.PI / 2;
    }
    var fontHeight = Math.sqrt((tx[2] * tx[2]) + (tx[3] * tx[3]));
    var fontAscent = (style.ascent ? style.ascent * fontHeight :
      (style.descent ? (1 + style.descent) * fontHeight : fontHeight));

    textDiv.style.position = 'absolute';
    textDiv.style.left = (tx[4] + (fontAscent * Math.sin(angle))) + 'px';
    textDiv.style.top = (tx[5] - (fontAscent * Math.cos(angle))) + 'px';
    textDiv.style.fontSize = fontHeight + 'px';
    textDiv.style.fontFamily = style.fontFamily;

    textDiv.textContent = geom.str;
    textDiv.dataset.fontName = geom.fontName;
    textDiv.dataset.angle = angle * (180 / Math.PI);
    if (style.vertical) {
      textDiv.dataset.canvasWidth = geom.height * this.viewport.scale;
    } else {
      textDiv.dataset.canvasWidth = geom.width * this.viewport.scale;
    }

  };

  this.setTextContent = function textLayerBuilderSetTextContent(textContent) {
    this.textContent = textContent;

    var textItems = textContent.items;
    for (var i = 0; i < textItems.length; i++) {
      this.appendText(textItems[i], textContent.styles);
    }
    this.divContentDone = true;

    this.setupRenderLayoutTimer();
  };

  this.convertMatches = function textLayerBuilderConvertMatches(matches) {
    var i = 0;
    var iIndex = 0;
    var bidiTexts = this.textContent.items;
    var end = bidiTexts.length - 1;
    var queryLen = (PDFFindController === null ?
                    0 : PDFFindController.state.query.length);

    var ret = [];

    // Loop over all the matches.
    for (var m = 0; m < matches.length; m++) {
      var matchIdx = matches[m];
      // # Calculate the begin position.

      // Loop over the divIdxs.
      while (i !== end && matchIdx >= (iIndex + bidiTexts[i].str.length)) {
        iIndex += bidiTexts[i].str.length;
        i++;
      }

      // TODO: Do proper handling here if something goes wrong.
      if (i == bidiTexts.length) {
        console.error('Could not find matching mapping');
      }

      var match = {
        begin: {
          divIdx: i,
          offset: matchIdx - iIndex
        }
      };

      // # Calculate the end position.
      matchIdx += queryLen;

      // Somewhat same array as above, but use a > instead of >= to get the end
      // position right.
      while (i !== end && matchIdx > (iIndex + bidiTexts[i].str.length)) {
        iIndex += bidiTexts[i].str.length;
        i++;
      }

      match.end = {
        divIdx: i,
        offset: matchIdx - iIndex
      };
      ret.push(match);
    }

    return ret;
  };

  this.renderMatches = function textLayerBuilder_renderMatches(matches) {
    // Early exit if there is nothing to render.
    if (matches.length === 0) {
      return;
    }

    var bidiTexts = this.textContent.items;
    var textDivs = this.textDivs;
    var prevEnd = null;
    var isSelectedPage = (PDFFindController === null ?
      false : (this.pageIdx === PDFFindController.selected.pageIdx));

    var selectedMatchIdx = (PDFFindController === null ?
                            -1 : PDFFindController.selected.matchIdx);

    var highlightAll = (PDFFindController === null ?
                        false : PDFFindController.state.highlightAll);

    var infty = {
      divIdx: -1,
      offset: undefined
    };

    function beginText(begin, className) {
      var divIdx = begin.divIdx;
      var div = textDivs[divIdx];
      div.textContent = '';
      appendTextToDiv(divIdx, 0, begin.offset, className);
    }

    function appendText(from, to, className) {
      appendTextToDiv(from.divIdx, from.offset, to.offset, className);
    }

    function appendTextToDiv(divIdx, fromOffset, toOffset, className) {
      var div = textDivs[divIdx];

      var content = bidiTexts[divIdx].str.substring(fromOffset, toOffset);
      var node = document.createTextNode(content);
      if (className) {
        var span = document.createElement('span');
        span.className = className;
        span.appendChild(node);
        div.appendChild(span);
        return;
      }
      div.appendChild(node);
    }

    function highlightDiv(divIdx, className) {
      textDivs[divIdx].className = className;
    }

    var i0 = selectedMatchIdx, i1 = i0 + 1, i;

    if (highlightAll) {
      i0 = 0;
      i1 = matches.length;
    } else if (!isSelectedPage) {
      // Not highlighting all and this isn't the selected page, so do nothing.
      return;
    }

    for (i = i0; i < i1; i++) {
      var match = matches[i];
      var begin = match.begin;
      var end = match.end;

      var isSelected = isSelectedPage && i === selectedMatchIdx;
      var highlightSuffix = (isSelected ? ' selected' : '');
      if (isSelected && !this.isViewerInPresentationMode) {
        scrollIntoView(textDivs[begin.divIdx], { top: FIND_SCROLL_OFFSET_TOP,
                                               left: FIND_SCROLL_OFFSET_LEFT });
      }

      // Match inside new div.
      if (!prevEnd || begin.divIdx !== prevEnd.divIdx) {
        // If there was a previous div, then add the text at the end
        if (prevEnd !== null) {
          appendText(prevEnd, infty);
        }
        // clears the divs and set the content until the begin point.
        beginText(begin);
      } else {
        appendText(prevEnd, begin);
      }

      if (begin.divIdx === end.divIdx) {
        appendText(begin, end, 'highlight' + highlightSuffix);
      } else {
        appendText(begin, infty, 'highlight begin' + highlightSuffix);
        for (var n = begin.divIdx + 1; n < end.divIdx; n++) {
          highlightDiv(n, 'highlight middle' + highlightSuffix);
        }
        beginText(end, 'highlight end' + highlightSuffix);
      }
      prevEnd = end;
    }

    if (prevEnd) {
      appendText(prevEnd, infty);
    }
  };

  this.updateMatches = function textLayerUpdateMatches() {
    // Only show matches, once all rendering is done.
    if (!this.renderingDone) {
      return;
    }

    // Clear out all matches.
    var matches = this.matches;
    var textDivs = this.textDivs;
    var bidiTexts = this.textContent.items;
    var clearedUntilDivIdx = -1;

    // Clear out all current matches.
    for (var i = 0; i < matches.length; i++) {
      var match = matches[i];
      var begin = Math.max(clearedUntilDivIdx, match.begin.divIdx);
      for (var n = begin; n <= match.end.divIdx; n++) {
        var div = textDivs[n];
        div.textContent = bidiTexts[n].str;
        div.className = '';
      }
      clearedUntilDivIdx = match.end.divIdx + 1;
    }

    if (PDFFindController === null || !PDFFindController.active) {
      return;
    }

    // Convert the matches on the page controller into the match format used
    // for the textLayer.
    this.matches = matches = (this.convertMatches(PDFFindController === null ?
      [] : (PDFFindController.pageMatches[this.pageIdx] || [])));

    this.renderMatches(this.matches);
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
	