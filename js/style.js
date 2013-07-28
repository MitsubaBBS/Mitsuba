//IT'S TEMPORARY SOLUTION
if (typeof String.prototype.trimLeft !== "function") {
	String.prototype.trimLeft = function() {
		return this.replace(/^\s+/, "");
	};
}
if (typeof String.prototype.trimRight !== "function") {
	String.prototype.trimRight = function() {
		return this.replace(/\s+$/, "");
	};
}
if (typeof Array.prototype.map !== "function") {
	Array.prototype.map = function(callback, thisArg) {
		for (var i=0, n=this.length, a=[]; i<n; i++) {
			if (i in this) a[i] = callback.call(thisArg, this[i]);
		}
		return a;
	};
}
var c = document.cookie, v = 0, cookies = {};
if (document.cookie.match(/^\s*\$Version=(?:"1"|1);\s*(.*)/)) {
	c = RegExp.$1;
	v = 1;
}
if (v === 0) {
	c.split(/[,;]/).map(function(cookie) {
		var parts = cookie.split(/=/, 2),
			name = decodeURIComponent(parts[0].trimLeft()),
			value = parts.length > 1 ? decodeURIComponent(parts[1].trimRight()) : null;
		if (name == "mitsuba_style")
		{
			document.getElementById("switch").href = value;
		}
	});
} else {
	c.match(/(?:^|\s+)([!#$%&'*+\-.0-9A-Z^`a-z|~]+)=([!#$%&'*+\-.0-9A-Z^`a-z|~]*|"(?:[\x20-\x7E\x80\xFF]|\\[\x00-\x7F])*")(?=\s*[,;]|$)/g).map(function($0, $1) {
		var name = $0,
			value = $1.charAt(0) === '"'
					  ? $1.substr(1, -1).replace(/\\(.)/g, "$1")
					  : $1;
		if (name == "mitsuba_style")
		{
			document.getElementById("switch").href = value;
		}
	});
}