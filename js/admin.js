String.prototype.contains = function(it) { return this.indexOf(it) != -1; };

function strStartsWith(str, prefix) {
    return str.indexOf(prefix) === 0;
}


$(document).ready(function () {
	$("a").each( function () {
		if ($(this).attr("href") != null)
		{
			if ($(this).attr("href").indexOf("delete_post&") != -1)
			{
				$(this).attr("href", $(this).attr("href").replace("delete_post", "delete_post/yes"));
				$(this).click(function (event) {
					return confirm('Are you sure you want to delete this post?');
				});
			}
		}
		
	});
	var expires = $("input[name=expires]");
	$("<a href='#' class='lnkSmall'>never</a> ").insertAfter(expires).before(" ").click(function (event) { event.preventDefault(); expires.val("0"); });
	$("<a href='#' class='lnkSmall'>1y</a> ").insertAfter(expires).before(" ").click(function (event) { event.preventDefault(); expires.val("1y"); });
	$("<a href='#' class='lnkSmall'>30d</a> ").insertAfter(expires).before(" ").click(function (event) { event.preventDefault(); expires.val("30d"); });
	$("<a href='#' class='lnkSmall'>2w</a> ").insertAfter(expires).before(" ").click(function (event) { event.preventDefault(); expires.val("2w"); });
	$("<a href='#' class='lnkSmall'>1w</a> ").insertAfter(expires).before(" ").click(function (event) { event.preventDefault(); expires.val("1w"); });
	$("<a href='#' class='lnkSmall'>3d</a> ").insertAfter(expires).before(" ").click(function (event) { event.preventDefault(); expires.val("3d"); });
	$("<a href='#' class='lnkSmall'>1d</a> ").insertAfter(expires).before(" ").click(function (event) { event.preventDefault(); expires.val("1d"); });
	$("<a href='#' class='lnkSmall'>1h</a> ").insertAfter(expires).before(" ").click(function (event) { event.preventDefault(); expires.val("1h"); });
	
	if (window.location.href.indexOf("bans/add") != -1)
	{
		var reason = $("input[name=reason]");
		$("<a href='#' class='lnkSmall'>Proxy</a> ").insertAfter(reason).before(" ").click(function (event) { event.preventDefault(); reason.val("Proxy"); });
	}
	if (window.location.href.indexOf("/board") != -1)
	{
		$("a.edit").click(inlineEdit);
		
		$(document).ajaxComplete(function(event, xhr, opts) {
			$("a.edit").click(inlineEdit);
		});
	}
});

function inlineEdit(event)
{
	event.preventDefault();
	var element = this;
	var dataString = $(this).attr("href").split("?/edit_post")[1];
	$.ajax({
		type: 'get',
		url: "?/api/get_post"+dataString,
		success: function(data, textStatus, xhr){
			var json = $.parseJSON(xhr.responseText);
			var block = $(element).parents("div.post").children("blockquote");
			var el_old = element.outerHTML;
			var old_html = $(block).html();
			$(block).css("display", "block");
			var raw = "";
			if (json.raw == 1)
			{
				raw = "checked='checked'";
			}
			$(block).html("<textarea rows='5' cols='50' id='edit_"+json.id+"'>"+json.comment+"</textarea><br /><input type='checkbox' "+raw+" value='1' id='raw_"+json.id+"' />Raw HTML<input type='submit' value='Update!' id='s_"+json.id+"' /><input type='submit' value='Cancel' id='cancel_"+json.id+"' />");
			
			$(element).replaceWith("<b>E</b>");
			
			$("#cancel_"+json.id).click(function () {
				event.preventDefault();
				$(block).html(old_html);
			});
			
			$("#s_"+json.id).click(function () {
				event.preventDefault();
				$(this).attr("disabled", "disabled");
				var raw_n = 0;
				if ($("#raw_"+json.id).is(':checked'))
				{
					raw_n = 1;
				}
				$.ajax({
					type: 'post',
					url: "?/api/update_post"+dataString,
					data: { comment : $("#edit_"+json.id).val(), raw : raw_n },
					success: function(data, textStatus, xhr){
						window.location.reload();
						
					}
				});
			});
			
		}
	});
}