<?php
if ((isset($_SESSION['logged'])) && ($_SESSION['logged']==1))
		{
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Mitsuba</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<frameset cols="200px,*" frameborder="1" border="1" bordercolor="#800">
<frame src="./mod.php?/nav" id="nav">
<frame src="./mod.php?/announcements" name="main" id="main">
<noframes>
<h1>Mitsuba</h1>
<p>This page uses frames!</p>
</noframes>
</frameset>
</html>
		<?php
		} else {
			?>

<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/log_in']; ?></h2></div>
<div class="boxcontent">
<form action="?/login" method="POST">
<center><?php echo $lang['mod/username']; ?>: <input type="text" name="username" /> | <?php echo $lang['mod/password']; ?>: <input type="password" name="password" /> <input type="submit" value="<?php echo $lang['mod/log_in']; ?>" /></center>
</form>
</div>
</div>
</div>
		<?php
		}
?>