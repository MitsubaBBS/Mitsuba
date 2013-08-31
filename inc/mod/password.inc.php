<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("user.change_password");
if ((!empty($_POST['old'])) && (!empty($_POST['new'])) && (!empty($_POST['new2'])))
		{
			$mitsuba->admin->ui->checkToken($_POST['token']);
			if ($_POST['new']==$_POST['new2'])
			{
		
			$result = $conn->query("SELECT password,salt FROM users WHERE id=".$_SESSION['id']);
			$row = $result->fetch_assoc();
				if ($row['password'] != hash("sha512", $_POST['old'].$row['salt']))
				{
							?>
<?php $mitsuba->admin->ui->startSection($lang['mod/pwd_no_match']); ?>
<a href="?/password"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
			<?php
				} else {
					$conn->query("UPDATE users SET password='".hash("sha512", $_POST['new'].$row['salt'])."' WHERE id=".$_SESSION['id']);
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/pwd_updated']); ?>
<a href="?/password"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
				}
			} else {
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/pwd_wrong']); ?>
<a href="?/password"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
			<?php
			}
		} else {
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/pwd_change']); ?>

<form action="?/password" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/pwd_current']; ?>: <input type="password" name="old"><br />
<?php echo $lang['mod/pwd_new']; ?>: <input type="password" name="new"><br />
<?php echo $lang['mod/pwd_confirm']; ?>: <input type="password" name="new2"><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>"><br />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		}
?>