String.prototype.contains = function(it) { return this.indexOf(it) != -1; };

function strStartsWith(str, prefix) {
    return str.indexOf(prefix) === 0;
}


$(document).ready(function () {
	//ready
	
	

	
	if (window.location.href.indexOf("res") == -1)
	{
		$(".op .postInfo").each(function () {
			var id = $(this).attr("id").substr(2);
			$(this).append(' <a href="javascript:;" class="hider" id="h'+id+'">[-]</a>');
		});
	
		$(".hider").click(function () {
			var id = $(this).attr("id").substr(1);
			thread_toggle(id);
		});
		
		$(".thread").each(function () {
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
					$(tid+" div.op span.postNum").append('<span> &nbsp; [<a href="'+href+'" class="replylink">Reply</a>]</span>');
					$(tid+" div.op span.postNum").append(' <a href="javascript:;" class="hider" id="h'+tid.substr(2)+'">[-]</a>');
					$(tid).find("a").each( function () { if ($(this).attr("href") !== null) { $(this).attr("href", absolutizeURI(href, $(this).attr("href"))); } } );
					$(tid).find("img").each( function () { $(this).attr("src", absolutizeURI(href, $(this).attr("src")));  } );
					$(tid).find(".hider").click(function () {
						var id = $(this).attr("id").substr(1);
						thread_toggle(id);
					});
					$(tid+" .postInfo").each(function () {
						
						$(this).append('<div class="backlink" id="bl'+$(this).attr("id").substr(2)+'"></div>');
						
					});
					$(tid+" .quotelink:not(cross)").each(function () {
						var hr = $(this).attr("href");
						var postid = hr.substr(hr.indexOf('#')+2);
						//here
						try {
						$("#bl"+postid).append("<span><a href='#p"+$(this).parent(".postMessage").attr("id").substr(1)+"' class='quotelink'>>>"+$(this).parent(".postMessage").attr("id").substr(1)+"</a> </span>");
						} catch(ex) {
							
						}
					});
					$(tid).find(".quotelink").hover(function () {
						showPostPreview(this);
					}, function () {
						hidePostPreview(this);
					});
					
					}
				});
			});
		});
		hideThreads();
	}
	
	$(".postInfo").each(function () {
		
		$(this).append('<div class="backlink" id="bl'+$(this).attr("id").substr(2)+'"></div>');
		
	});
	$(".quotelink:not(cross)").each(function () {
		var hr = $(this).attr("href");
		var postid = hr.substr(hr.indexOf('#')+2);
		//here
		try {
		$("#bl"+postid).append("<span><a href='#p"+$(this).parent(".postMessage").attr("id").substr(1)+"' class='quotelink'>>>"+$(this).parent(".postMessage").attr("id").substr(1)+"</a> </span>");
		} catch(ex) {
			
		}
	});
	
	$("body").append('<div id="quote-preview" class="post preview" style="display: none; position: absolute; z-index:999;"></div>');
	$(".quotelink").hover(function () {
		showPostPreview(this);
	}, function () {
		hidePostPreview(this);
	}
	);
	
	
	
});

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
	$("#f"+id).css("display", "none");
	$("#m"+id).css("display", "none");
	$("#e"+id).css("display", "none");
	$("#t"+id).find(".replyContainer").css("display", "none");
	$("#t"+id).find("span.summary").css("display", "none");
	$("#h"+id).html("[+]");
}

function showThread(id)
{
	$("#f"+id).css("display", "block");
	$("#m"+id).css("display", "block");
	$("#e"+id).css("display", "inline");
	$("#t"+id).find(".replyContainer").css("display", "block");
	$("#t"+id).find("span.summary").css("display", "inline");
	$("#h"+id).html("[-]");
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