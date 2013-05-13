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

function addWarning($conn, $ip, $reason, $note)
{
	if (!empty($ip))
	{
		$ip = $conn->real_escape_string($ip);
		$reason = $conn->real_escape_string($reason);
		$note = $conn->real_escape_string($note);
		$created = time();
		$conn->query("INSERT INTO warnings (ip, mod_id, reason, note, created, shown) VALUES ('".$ip."', ".$_SESSION['id'].", '".$reason."', '".$note."', ".$created.", 0);");
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
?>