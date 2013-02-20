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
});