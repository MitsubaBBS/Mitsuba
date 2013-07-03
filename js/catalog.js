localStorage.setItem("meow", 1);
if (localStorage.getItem("firsttime") != 1)
{
	localStorage.setItem("firsttime", 1);
	localStorage.setItem("o_hider", 1);
	localStorage.setItem("o_expander", 1);
	localStorage.setItem("o_backlinks", 1);
	localStorage.setItem("o_preview", 1);

	localStorage.setItem("o_loader", 0);
	localStorage.setItem("o_watched", 0);
	localStorage.setItem("o_updater", 0);
	localStorage.setItem("o_imgexpand", 0);
	localStorage.setItem("o_fastreply", 0);
}
if (typeof $.cookie("mitsuba_style") !== "undefined")
{
	$("#switch").attr("href", $.cookie("mitsuba_style"));
}

String.prototype.contains = function(it) { return this.indexOf(it) != -1; };

function strStartsWith(str, prefix) {
	return str.indexOf(prefix) === 0;
}


$(document).ready(function () {
	fillFields("body");
	addStylechanger();
	addSettings();
});

var settingsShown = 0;

function addSettings()
{
	$("body").prepend("<span style='float:right;'>[<a id='settingsbutton' href='#'>Settings</a>]</span>");
	$("body").prepend("<div id='settingsDivWrap' style='z-index:9000; display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.247);'><div id='settingsDiv' style='width: 400px; height: 100%; margin: auto; background: rgb(241, 225, 215); overflow: auto; z-index: 9001;'> \
		<span id='settingstitle' style='font-size:20px; display: block; text-align: center; background: #ffccaa;'>Settings</span> \
		<hr /> \
		<input type='checkbox' name='o_hider' /> Enable thread hider<br />\
		<input type='checkbox' name='o_expander' /> Enable thread expander<br />\
		<input type='checkbox' name='o_backlinks' /> Enable backlinks<br />\
		<input type='checkbox' name='o_preview' /> Enable post preview<br />\
		<input type='checkbox' name='o_loader' /> Enable page loader<br />\
		<input type='checkbox' name='o_watched' /> Enable watched threads<br />\
		<input type='checkbox' name='o_updater' /> Enable updater<br />\
		<input type='checkbox' name='o_imgexpand' /> Enable image expander (RES)<br />\
		<input type='checkbox' name='o_fastreply' /> Enable fast reply<br />\
		<hr /> \
		<input type='button' value='Save' id='settingsSave'/> <input type='button' value='Reset' id='settingsReset'/>\
		</div></div>");
	
	$("#settingsSave").click(function (e) {
		$("input[name^='o_']").each(function ()
		{
			if ($(this).prop("checked"))
			{
				localStorage.setItem($(this).attr("name"), 1);
			} else {
				localStorage.setItem($(this).attr("name"), 0);
			}
		});
		window.location.reload();
	});
	$("#settingsReset").click(function (e) {
		localStorage.setItem("firsttime", 0);
		window.location.reload();
	});
	$("#settingsDivWrap").click(function (e) {
		if( e.target !== this ) 
			return;
		$(this).css("display", "none");
		e.preventDefault();
	});
	$("#settingsbutton").click(function (e) {
		$("#settingsDivWrap").css("display","");
		for (var key in localStorage)
		{
			if (key.substring(0, 2) == "o_")
			{
				if (localStorage[key] == 1)
				{
					$("input[name='"+key+"']").attr("checked", true);
				} else {
					$("input[name='"+key+"']").attr("checked", false);
				}
			}
		}
		e.preventDefault();
	});
}

function fillFields(parent)
{
	if (typeof $.cookie("mitsuba_name") !== "undefined")
	{
		$(parent).find("input[name='name']").val($.cookie("mitsuba_name"));
	}
	
	if (typeof $.cookie("mitsuba_email") !== "undefined")
	{
		$(parent).find("input[name='email']").val($.cookie("mitsuba_email"));
	}
	
	if (typeof $.cookie("mitsuba_fakeid") !== "undefined")
	{
		$(parent).find("input[name='fake_id']").val($.cookie("mitsuba_fakeid"));
	}
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