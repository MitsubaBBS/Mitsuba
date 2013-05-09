<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if ((!empty($_POST['username'])) && (!empty($_POST['password'])))
		{
			$username = $conn->real_escape_string($_POST['username']);
			$password = hash("sha512", $_POST['password']);
			$result = $conn->query("SELECT * FROM users WHERE username='".$username."' AND type>=1");
			if ($result->num_rows == 1)
			{
				$data = $result->fetch_assoc();
				if ($data['password'] == $password)
				{
					$_SESSION['logged']=1;
					$_SESSION['id']=$data['id'];
					$_SESSION['username']=$username;
					$_SESSION['type']=$data['type'];
					$_SESSION['boards']=$data['boards'];
					$_SESSION['ip']=$_SERVER['REMOTE_ADDR'];
					$_SESSION['cookie_set']=2;
					logAction($conn, sprintf($lang['log/logged_in'], $_SERVER['REMOTE_ADDR']));
					header("Location: ./mod.php");
				} else {
					die($lang['mod/bad_password']);
				}
			} else {
				die($lang['mod/bad_password']);
			}
		} else {
			die($lang['mod/error']);
		}
?>