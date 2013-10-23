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
<frameset cols="200px,*" frameborder="1" border="1">
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

<?php $mitsuba->admin->ui->startSection($lang['mod/log_in']); ?>

<form action="?/login" method="POST">
<table class="logForm">
	<tbody>
		<tr>
			<td style="text-align: center; width: 65px;"><?php echo $lang['mod/username']; ?></td>
			<td><input type="text" name="username" style="width: 145px; text-align: center;"></td>
		</tr>

		<tr>
			<td style="text-align: center; width: 65px;"><?php echo $lang['mod/password']; ?></td>
			<td><input type="password" name="password" style="width: 145px; text-align: center;"></td>
		</tr>

		<tr>
			<td colspan="2" style="padding: 5px 0; border: none; background: none; text-align: center; font-weight: normal; padding-bottom: 20px;">
				<input type="submit" value="<?php echo $lang['mod/log_in']; ?>" style="margin: 0px;">
			</td>
		</tr>
	</tbody>
</table>
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		}
?>