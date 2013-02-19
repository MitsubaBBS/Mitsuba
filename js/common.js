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
		hideThreads();
	}
	
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
	$.ajax({
		type: 'get',
		url: $(el).attr("href"),
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
			$("#quote-preview").offset(off);
		}
	});
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
	$("#t"+id).find(".replyContainer").css("display", "none");
	$("#t"+id).find("span.summary").css("display", "none");
	$("#h"+id).html("[+]");	
}

function showThread(id)
{
	$("#f"+id).css("display", "block");
	$("#m"+id).css("display", "block");
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