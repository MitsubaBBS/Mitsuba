if (typeof $.cookie("mitsuba_style") !== "undefined")
{
	$("#switch").attr("href", $.cookie("mitsuba_style"));
}

String.prototype.contains = function(it) { return this.indexOf(it) != -1; };

function strStartsWith(str, prefix) {
    return str.indexOf(prefix) === 0;
}


$(document).ready(function () {
	fillFields();
	
	if ($(".postingMode").length == 0) //outside thread
	{
		addThreadHider("body");
		addThreadExpander("body");
		hideThreads();
		addLoader();
	} else { //in thread
		addThreadUpdater();
	}
	
	addStylechanger();
	addBacklinks("body");
	addPostpreview("body");
	addImgExpand("body");
	addQuotelinks();
});

var currentPage = 0;
function addLoader()
{
	var strong = $(".pagelist").find("strong")[0];
	currentPage = $(strong).html();
	var nextPageEl = $(strong).parent().next();
	if (nextPageEl.length >= 1)
	{
		$(window).scroll(function() {   
			if($(window).scrollTop() + $(window).height() == $(document).height()) {
				$(".pagelist").css("opacity", "0.5");
				$.ajax({
				type: 'get',
				url: "./"+$(nextPageEl).html()+".html",
				success: function(data, textStatus, xhr){
					$(".pagelist").css("opacity", "");
					var html = xhr.responseText;
					var nodes = $.parseHTML( html );
					currentPage = $(nextPageEl).html();
					$(".deleteform").before("<div class='board' id='b"+currentPage+"'><br /><b>Page "+currentPage+"</b><hr />"+$($(".board", nodes)[0]).html()+"</div>");
					$(".prev").html($(".prev", nodes).html());
					$(".pages").html($(".pages", nodes).html());
					$(".next").html($(".next", nodes).html());
					addBacklinks("#b"+currentPage);
					addPostpreview("#b"+currentPage);
					addImgExpand("#b"+currentPage);
					addThreadHider("#b"+currentPage);
					addThreadExpander("#b"+currentPage);
					hideThreads();
					addLoader();
						
					}
				});
				$(window).unbind('scroll');
			}
		});
	}
	
}

function fillFields()
{
	if (typeof $.cookie("mitsuba_name") !== "undefined")
	{
		$("input[name='name']").val($.cookie("mitsuba_name"));
	}
	
	if (typeof $.cookie("mitsuba_email") !== "undefined")
	{
		$("input[name='email']").val($.cookie("mitsuba_email"));
	}
	
	if (typeof $.cookie("mitsuba_fakeid") !== "undefined")
	{
		$("input[name='fake_id']").val($.cookie("mitsuba_fakeid"));
	}
}

function addThreadUpdater()
{
	
}

function addQuotelinks()
{
	$(".quotePost").click(function () {
		try {
			event.preventDefault();
			var id = $(this).attr("id").substr(1);
			var textarea = $("#postForm textarea[name='com']")[0];
			$(textarea).val($(textarea).val()+'>>'+id+'\n'); 
		} catch (ex) {
			
		}
	});
}

function addStylechanger()
{
	$("#stylechangerDiv").css("display", "block");
	$("link[rel='alternate stylesheet']").each(function () {
		var selected = "";
		if (typeof $.cookie("mitsuba_style") !== "undefined")
		{
			if (absolutizeURI(window.location.href, $(this).attr("href")) == $.cookie("mitsuba_style"))
			{
				selected = " selected";
			}
		}
		$("#stylechanger").append("<option value='"+$(this).attr("href")+"'"+selected+">"+$(this).attr("title")+"</option>");
	});
	$("#stylechanger").change(function (e) {
		$("#switch").attr("href", e.target.options[e.target.selectedIndex].value);
		$.cookie("mitsuba_style", absolutizeURI(window.location.href, e.target.options[e.target.selectedIndex].value), {expires: 31, path: '/'});
	});
	
	
}

function addPostpreview(parent)
{
	$("body").append('<div id="quote-preview" class="post preview" style="display: none; position: absolute; z-index:999;"></div>');
	$(parent).find(".quotelink").mousein(function () { showPostPreview(this); });
	$(parent).find(".quotelink").mouseout(function () { hidePostPreview(this); });
}

function addBacklinks(parent)
{
	$(parent).find(".postMessage").each(function () {
		
		$(this).append('<div class="backlink" id="bl'+$(this).attr("id").substr(1)+'"></div>');
		
	});
	$(parent).find(".quotelink:not(cross)").each(function () {
		var hr = $(this).attr("href");
		var postid = hr.substr(hr.indexOf('#')+2);
		//here
		try {
			
		if ($("#bl"+postid).html() == "")
		{
			$("#bl"+postid).append("<hr />");
		}
		$("#bl"+postid).append("<span><a href='#p"+$(this).parent(".postMessage").attr("id").substr(1)+"' class='quotelink'>>>"+$(this).parent(".postMessage").attr("id").substr(1)+"</a> </span>");
		} catch(ex) {
			
		}
	});
}

function addThreadExpander(parent)
{
	
	$(parent).find(".thread").each(function () {
		$('<a href="javascript:;" class="expander" id="e'+$(this).attr("id")+'">[+]</a>').insertAfter($("div#"+$(this).attr("id")+" > span.summary")).click(function () {
			var tid = "#"+$(this).attr("id").substr(1);
			var href = absolutizeURI(window.location.href, $(tid).find(".replylink").attr("href"));
			$.ajax({
			type: 'get',
			url: href,
			success: function(data, textStatus, xhr){
				var html = xhr.responseText;
				var nodes = $.parseHTML( html );
				$(tid).html($(tid, nodes).html());
				$('<a href="javascript:;" class="hider" id="ht'+tid.substr(2)+'">[-]</a>').appendTo($(tid+" div.op div.postInfo")).click(function () {
					var id = $(this).attr("id").substr(2);
					thread_toggle(id);
				});
				$('<span> &nbsp; [<a href="'+href+'" class="replylink">Reply</a>] </span>').insertAfter($(tid+" div.op span.postNum"));
				$(tid).find("a").each( function () { if ($(this).attr("href") !== null) { $(this).attr("href", absolutizeURI(href, $(this).attr("href"))); } } );
				$(tid).find("img").each( function () { $(this).attr("src", absolutizeURI(href, $(this).attr("src")));  } );
				
				addBacklinks(tid);
				addPostpreview(tid);
				addImgExpand(tid);
					
				}
			});
		});
	});
}

function addThreadHider(parent)
{
	$(parent).find(".op .postInfo").each(function () {
		var id = $(this).attr("id").substr(2);
		$(this).append(' <a href="javascript:;" class="hider" id="ht'+id+'">[-]</a>');
	});

	$(parent).find(".hider").click(function () {
		var id = $(this).attr("id").substr(2);
		thread_toggle(id);
	});
}

function showPostPreview( el )
{
	var href = $(el).attr("href").split("#");
	var curl = window.location.href.split("#");
	curl = curl[0];
	href = absolutizeURI(curl, href[0]);
	if (href == curl)
	{
		var hr = $(el).attr("href");
		var postid = hr.substr(hr.indexOf('#'));
		$("#quote-preview").html($(postid).html());
		var off = $( el ).offset();
		off.left = off.left + $(el).width();
		off.top = off.top - $("#quote-preview").height()/2
		$("#quote-preview").css("display", "block");
				$("#quote-preview").find("a").each( function () { if ($(this).attr("href") !== null) { $(this).attr("href", absolutizeURI(href, $(this).attr("href"))); } } );
				$("#quote-preview").find("img").each( function () { $(this).attr("src", absolutizeURI(href, $(this).attr("src")));  } );
		$("#quote-preview").offset(off);
	} else {
		$.ajax({
			type: 'get',
			url: href,
			success: function(data, textStatus, xhr){
				var html = xhr.responseText;
				var nodes = $.parseHTML( html );
				var hr = $(el).attr("href");
				var postid = hr.substr(hr.indexOf('#'));
				$("#quote-preview").html($(postid, nodes).html());
				var off = $( el ).offset();
				off.left = off.left + $(el).width();
				off.top = off.top - $("#quote-preview").height()/2
				$("#quote-preview").css("display", "block");
				$("#quote-preview").find("a").each( function () { if ($(this).attr("href") !== null) { $(this).attr("href", absolutizeURI(href, $(this).attr("href"))); } } );
				$("#quote-preview").find("img").each( function () { $(this).attr("src", absolutizeURI(href, $(this).attr("src")));  } );
				$("#quote-preview").offset(off);
			}
		});
	}
}

function hidePostPreview( el )
{
	
	$("#quote-preview").css("display", "none");
	$("#quote-preview").offset({top: "0px", left: "0px"});
	$("#quote-preview").html("");
	
}

function hideThreads()
{
	$.each(document.cookie.split(/; */), function()  {
		var splitCookie = this.split('=');
		// name is splitCookie[0], value is splitCookie[1]
		if (strStartsWith(splitCookie[0], "h_"))
		{
			var id = splitCookie[0].substr(2);
			if (splitCookie[1] == 1)
			{
				hideThread(id);
			}
		}
	});
}

function hideThread(id)
{
	$("#pc"+id+" .file").css("display", "none");
	$("#m"+id).css("display", "none");
	$("#et"+id).css("display", "none");
	$("#t"+id).find(".replyContainer").css("display", "none");
	$("#t"+id).find("span.summary").css("display", "none");
	$("#ht"+id).html("[+]");
}

function showThread(id)
{
	$("#pc"+id+" .file").css("display", "block");
	$("#m"+id).css("display", "block");
	$("#et"+id).css("display", "inline");
	$("#t"+id).find(".replyContainer").css("display", "block");
	$("#t"+id).find("span.summary").css("display", "inline");
	$("#ht"+id).html("[-]");
}

function thread_toggle(id)
{
	if (typeof $.cookie("h_"+id) === "undefined")
	{
		$.cookie("h_"+id, 1, {expires: 31});
		hideThread(id);
	} else {
		if ($.cookie("h_"+id) == 1)
		{
			
			$.cookie("h_"+id, 0, {expires: 31});
			showThread(id);
		} else {
			$.cookie("h_"+id, 1, {expires: 31});
			hideThread(id);
		}
	}
}

function addImgExpand(parent)
{
	$(parent).find(".fileThumb").click(function (e) {
		imgExpand($(this).parent());
		e.preventDefault();
	});
}

function imgExpand(element)
{
	$(element).children(".fileThumb").css("opacity", "0.7");
	var id = $(element).attr("id");
	$(element).append("<img src='"+$($(element).children(".fileThumb")[0]).attr("href")+"' style='display: none;' id='x"+id+"' />");
	$("#x"+id).bind("load", function () { 
		var iw = $('body').innerWidth();
		$(element).children(".fileThumb").css("opacity", "").css("display", "none");
		$(this).css("display", "");
		var newWidth = Math.min($(this).width(), (iw-100));
		$(this).css("width", newWidth+"px");
		$(this).css("max-width", newWidth+"px");
		$(this).css("height", "auto");
		$(this).css("max-height", "auto");
		addZoom(this);
	});
	$("#x"+id).bind("error", function () { 
		$(element).children(".fileThumb").css("opacity", "");
		$(this).remove();
	});
}

function imgThumbnail(element)
{
	$(element).siblings(".fileThumb").css("display", "");
	$(element).remove();
}

var targetImageWidth = 0;
var targetDiagonal = 0;
var targetDragging = false;
function addZoom(img) {
	$(img).mousedown(function(e) {
		if (e.button == 0) {
			targetImageWidth = $(this).width();
			var rc = e.target.getBoundingClientRect();
			var p = Math.pow;
			var dragSize = p(p(e.clientX-rc.left, 2)+p(e.clientY-rc.top, 2), .5);
			targetDiagonal = Math.round(dragSize);
			targetDragging = false;
			e.preventDefault();
		}
	});
	$(img).mousemove(function(e) {
		if (targetDiagonal){
			var rc = e.target.getBoundingClientRect();
			var p = Math.pow;
			var dragSize = p(p(e.clientX-rc.left, 2)+p(e.clientY-rc.top, 2), .5);
			var newDiagonal = Math.round(dragSize);
			var oldDiagonal = targetDiagonal;
			var imageWidth = targetImageWidth;
			var newWidth = Math.max(250, newDiagonal/oldDiagonal*imageWidth)+'px';
			$(this).css("width", newWidth);
			$(this).css("maxWidth", newWidth);

			$(this).css("maxHeight", "");
			$(this).css("height", "auto");

			targetDragging = true;
		}
	});
	$(img).mouseout(function(e) {
		targetDiagonal = 0;
	});
	$(img).mouseup(function(e) {
		if (targetDiagonal) {
			var rc = e.target.getBoundingClientRect();
			var p = Math.pow;
			var dragSize = p(p(e.clientX-rc.left, 2)+p(e.clientY-rc.top, 2), .5);
			var newDiagonal = Math.round(dragSize);
			var oldDiagonal = targetDiagonal;
			var imageWidth = targetImageWidth;
			var newWidth = Math.max(250, newDiagonal/oldDiagonal*imageWidth)+'px';
			//$(this).width(newWidth);
			$(this).css("width", newWidth);
			$(this).css("maxWidth", newWidth);

		}
	});
	$(img).click(function(e) {
		targetDiagonal = 0;
		if (targetDragging) {
			targetDragging = false;
			e.preventDefault();
			return false;
		}
	});
	
	$(img).dblclick(function (e) {
		imgThumbnail(this);
	});
}

/* 
 * ==============================
 * | URI manipulation functions |
 * ==============================
 */
function parseURI(url) {
  var m = String(url).replace(/^\s+|\s+$/g, '').match(/^([^:\/?#]+:)?(\/\/(?:[^:@]*(?::[^:@]*)?@)?(([^:\/?#]*)(?::(\d*))?))?([^?#]*)(\?[^#]*)?(#[\s\S]*)?/);
  // authority = '//' + user + ':' + pass '@' + hostname + ':' port
  return (m ? {
    href     : m[0] || '',
    protocol : m[1] || '',
    authority: m[2] || '',
    host     : m[3] || '',
    hostname : m[4] || '',
    port     : m[5] || '',
    pathname : m[6] || '',
    search   : m[7] || '',
    hash     : m[8] || ''
  } : null);
}
 
function absolutizeURI(base, href) {// RFC 3986
 
  function removeDotSegments(input) {
    var output = [];
    input.replace(/^(\.\.?(\/|$))+/, '')
         .replace(/\/(\.(\/|$))+/g, '/')
         .replace(/\/\.\.$/, '/../')
         .replace(/\/?[^\/]*/g, function (p) {
      if (p === '/..') {
        output.pop();
      } else {
        output.push(p);
      }
    });
    return output.join('').replace(/^\//, input.charAt(0) === '/' ? '/' : '');
  }
 
  href = parseURI(href || '');
  base = parseURI(base || '');
 
  return !href || !base ? null : (href.protocol || base.protocol) +
         (href.protocol || href.authority ? href.authority : base.authority) +
         removeDotSegments(href.protocol || href.authority || href.pathname.charAt(0) === '/' ? href.pathname : (href.pathname ? ((base.authority && !base.pathname ? '/' : '') + base.pathname.slice(0, base.pathname.lastIndexOf('/') + 1) + href.pathname) : base.pathname)) +
         (href.protocol || href.authority || href.pathname ? href.search : (href.search || base.search)) +
         href.hash;
}