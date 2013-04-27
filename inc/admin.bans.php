<?php
function addBan($conn, $ip, $reason, $note, $expires, $boards)
{
	if (!empty($ip))
	{
		$ip = $conn->real_escape_string($ip);
		$reason = $conn->real_escape_string($reason);
		$note = $conn->real_escape_string($note);
		$boards = $conn->real_escape_string($boards);
		$created = time();
		$perma = 1;
		if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban"))
		{
			$expires = 0;
			$perma = 1;
		} else {
			$expires = parse_time($expires);
			$perma = 0;
		}
		if (($expires == false) && ($perma == 0))
		{
			return -2;
		}
		$conn->query("INSERT INTO bans (ip, mod_id, reason, note, created, expires, boards) VALUES ('".$ip."', ".$_SESSION['id'].", '".$reason."', '".$note."', ".$created.", ".$expires.", '".$boards."');");
		return 1;
	}
}

function addSystemBan($conn, $ip, $reason, $note, $expires, $boards)
{
	if (!empty($ip))
	{
		$ip = $conn->real_escape_string($ip);
		$reason = $conn->real_escape_string($reason);
		$note = $conn->real_escape_string($note);
		$boards = $conn->real_escape_string($boards);
		$created = time();
		$perma = 1;
		if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban"))
		{
			$expires = 0;
			$perma = 1;
		} else {
			$expires = parse_time($expires);
			$perma = 0;
		}
		if (($expires == false) && ($perma == 0))
		{
			return -2;
		}
		$conn->query("INSERT INTO bans (ip, mod_id, reason, note, created, expires, boards) VALUES ('".$ip."', 0, '".$reason."', '".$note."', ".$created.", ".$expires.", '".$boards."');");
		return 1;
	}
}

function addBanRequest($conn, $ip, $reason, $note, $board = "", $post = 0, $append = 0)
{
	if (!empty($ip))
	{
		$ip = $conn->real_escape_string($ip);
		$reason = $conn->real_escape_string($reason);
		$note = $conn->real_escape_string($note);
		if (is_numeric($post))
		{
		
		}
		$created = time();
	
		$conn->query("INSERT INTO ban_requests (ip, mod_id, reason, note, created, board, post, append) VALUES ('".$ip."', ".$_SESSION['id'].", '".$reason."', '".$note."', ".$created.", '".$board."', ".$post.", ".$append.");");
		return 1;
	}
}

function parse_time($str) {
	if (empty($str))
		return false;

	if (($time = @strtotime($str)) !== false)
		return $time;

	if (!preg_match('/^((\d+)\s?ye?a?r?s?)?\s?+((\d+)\s?mon?t?h?s?)?\s?+((\d+)\s?we?e?k?s?)?\s?+((\d+)\s?da?y?s?)?((\d+)\s?ho?u?r?s?)?\s?+((\d+)\s?mi?n?u?t?e?s?)?\s?+((\d+)\s?se?c?o?n?d?s?)?$/', $str, $matches))
		return false;

	$expire = 0;

	if (isset($matches[2])) {
		// Years
		$expire += $matches[2]*60*60*24*365;
	}
	if (isset($matches[4])) {
		// Months
		$expire += $matches[4]*60*60*24*30;
	}
	if (isset($matches[6])) {
		// Weeks
		$expire += $matches[6]*60*60*24*7;
	}
	if (isset($matches[8])) {
		// Days
		$expire += $matches[8]*60*60*24;
	}
	if (isset($matches[10])) {
		// Hours
		$expire += $matches[10]*60*60;
	}
	if (isset($matches[12])) {
		// Minutes
		$expire += $matches[12]*60;
	}
	if (isset($matches[14])) {
		// Seconds
		$expire += $matches[14];
	}

	return time() + $expire;
}
?>