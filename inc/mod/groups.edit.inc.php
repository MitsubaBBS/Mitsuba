<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("groups.update");
		if ((!empty($_GET['id'])) && (is_numeric($_GET['id'])))
		{
			$id = $_GET['id'];
			if ($groupname = $mitsuba->admin->users->isUser($id))
			{
				if ((!empty($_POST['username'])) && (is_numeric($_POST['type'])))
				{
					$mitsuba->admin->ui->checkToken($_POST['token']);
					//STUFF
					$mitsuba->admin->logAction(sprintf($lang['log/edited_group'], $groupname));
					$mitsuba->admin->users->updateGroup(STUFF);
					?>
<?php $mitsuba->admin->ui->startSection($lang['mod/group_updated']); ?>

<a href="?/users"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
					<?php
				} else {
					$result = $conn->query("SELECT * FROM groups WHERE id=".$_GET['id']);
					$data = $result->fetch_assoc();
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/edit_group']); ?>

<form action="?/users/edit&id=<?php echo $id; ?>" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/username']; ?>: <input type="text" name="username" value="<?php echo $data['username']; ?>"/><br />
<?php echo $lang['mod/password_leave_blank']; ?>: <input type="password" name="password"/><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?><br />
<?php
				}
			}
		}
?>