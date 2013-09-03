<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if ((!empty($_POST['username'])) && (!empty($_POST['password'])))
		{
			$username = $conn->real_escape_string($_POST['username']);
			
			$result = $conn->query("SELECT * FROM users WHERE username='".$username."'");
			if ($result->num_rows == 1)
			{
				$data = $result->fetch_assoc();
				$mitsuba->admin->reqPermission("user.login", $data['id']);
				$password = hash("sha512", $_POST['password'].$data['salt']);
				if ($data['password'] == $password)
				{
					$group = $conn->query("SELECT * FROM groups WHERE id=".$data['group']);
					$gdata = $group->fetch_assoc();
					$_SESSION['logged'] = 1;
					$_SESSION['id'] = $data['id'];
					$_SESSION['username'] = $username;
					$_SESSION['group'] = $data['group'];
					$_SESSION['boards'] = $data['boards'];
					$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
					$_SESSION['capcode_text'] = $gdata['capcode'];
					$_SESSION['capcode_style'] = $gdata['capcode_style'];
					$_SESSION['capcode_icon'] = $gdata['capcode_icon'];
					$_SESSION['group_name'] = $gdata['name'];
					$_SESSION['cookie_set'] = 2;
					$mitsuba->admin->logAction(sprintf($lang['log/logged_in'], $_SERVER['REMOTE_ADDR']));
					header("Location: ./mod.php");
				} else {
					$ip = $_SERVER['REMOTE_ADDR'];
					$result = $conn->query("SELECT * FROM bruteforce_tries WHERE ip='".$ip."';");
					if ($result->num_rows >= 1)
					{
						$row = $result->fetch_assoc();
						if ($row['lasttry'] > (time() - 3600))
						{
							$conn->query("UPDATE bruteforce_tries SET tries=tries+1, lasttry=".time()." WHERE ip='".$ip."';");
							$conn->query("DELETE FROM bruteforce_tries WHERE lasttry<".(time() - 3600));
							if ($row['tries'] > 3)
							{
								die($lang['mod/bad_password']);
							}
						} else {
							$conn->query("UPDATE bruteforce_tries SET tries=1, lasttry=".time()." WHERE ip='".$ip."';");
							$conn->query("DELETE FROM bruteforce_tries WHERE lasttry<".(time() - 3600));
						}
					}
					die($lang['mod/bad_password']);
				}
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
				$result = $conn->query("SELECT * FROM bruteforce_tries WHERE ip='".$ip."';");
				if ($result->num_rows >= 1)
				{
					$row = $result->fetch_assoc();
					if ($row['lasttry'] > (time() - 3600))
					{
						$conn->query("UPDATE bruteforce_tries SET tries=tries+1, lasttry=".time()." WHERE ip='".$ip."';");
						$conn->query("DELETE FROM bruteforce_tries WHERE lasttry<".(time() - 3600));
						if ($row['tries'] > 3)
						{
							die($lang['mod/bad_password']);
						}
					} else {
						$conn->query("UPDATE bruteforce_tries SET tries=1, lasttry=".time()." WHERE ip='".$ip."';");
						$conn->query("DELETE FROM bruteforce_tries WHERE lasttry<".(time() - 3600));
					}
				}
				die($lang['mod/bad_password']);
			}
		} else {
			die($lang['mod/error']);
		}
?>