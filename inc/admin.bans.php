<?php
namespace Mitsuba\Admin;
class Bans {
	private $conn;
	private $mitsuba;

	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
	}

	function addBan($ip, $reason, $note, $expires, $boards, $appeal = 0)
	{
		if (!empty($ip))
		{
			$ip = $this->conn->real_escape_string($ip);
			$reason = $this->conn->real_escape_string($reason);
			$note = $this->conn->real_escape_string($note);
			$boards = $this->conn->real_escape_string($boards);
			$created = time();
			$perma = 1;
			$noappeal = 1;
			if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban"))
			{
				$expires = 0;
				$perma = 1;
			} else {
				$expires = $this->mitsuba->common->parse_time($expires);
				$perma = 0;
			}
			if (($expires == false) && ($perma == 0))
			{
				return -2;
			}
			if (($appeal == "0") || ($appeal == "never") || ($appeal == ""))
			{
				$appeal = 0;
				$noappeal = 1;
			} else {
				$appeal = $this->mitsuba->common->parse_time($appeal);
				$noappeal = 0;
			}
			if (($appeal == false) && ($noappeal == 0))
			{
				return -2;
			}
			$this->conn->query("INSERT INTO bans (ip, mod_id, reason, note, created, expires, appeal, boards, seen) VALUES ('".$ip."', ".$_SESSION['id'].", '".$reason."', '".$note."', ".$created.", ".$expires.", ".$appeal.", '".$boards."', 0);");
			return 1;
		}
	}

	function addRangeBan($ip, $reason, $note, $expires, $boards)
	{
		if (!empty($ip))
		{
			$ip = $this->conn->real_escape_string($ip);
			$reason = $this->conn->real_escape_string($reason);
			$note = $this->conn->real_escape_string($note);
			$boards = $this->conn->real_escape_string($boards);
			$created = time();
			$perma = 1;
			if (($expires == "0") || ($expires == "never") || ($expires == "") || ($expires == "perm") || ($expires == "permaban"))
			{
				$expires = 0;
				$perma = 1;
			} else {
				$expires = $this->mitsuba->common->parse_time($expires);
				$perma = 0;
			}
			if (($expires == false) && ($perma == 0))
			{
				return -2;
			}
			$this->conn->query("INSERT INTO rangebans (ip, mod_id, reason, note, created, expires, boards) VALUES ('".$ip."', ".$_SESSION['id'].", '".$reason."', '".$note."', ".$created.", ".$expires.", '".$boards."');");
			return 1;
		}
	}
	function addWarning($ip, $reason, $note)
	{
		if (!empty($ip))
		{
			$ip = $this->conn->real_escape_string($ip);
			$reason = $this->conn->real_escape_string($reason);
			$note = $this->conn->real_escape_string($note);
			$created = time();
			$this->conn->query("INSERT INTO warnings (ip, mod_id, reason, note, created, seen) VALUES ('".$ip."', ".$_SESSION['id'].", '".$reason."', '".$note."', ".$created.", 0);");
			return 1;
		}
	}

	function addBanRequest($ip, $reason, $note, $board = "", $post = 0, $append = 0)
	{
		if (!empty($ip))
		{
			$ip = $this->conn->real_escape_string($ip);
			$reason = $this->conn->real_escape_string($reason);
			$note = $this->conn->real_escape_string($note);
			if (is_numeric($post))
			{
			
			}
			$created = time();
		
			$this->conn->query("INSERT INTO ban_requests (ip, mod_id, reason, note, created, board, post, append) VALUES ('".$ip."', ".$_SESSION['id'].", '".$reason."', '".$note."', ".$created.", '".$board."', ".$post.", ".$append.");");
			return 1;
		}
	}
}
?>