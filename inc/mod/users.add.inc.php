<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("users.add");
$mitsuba->admin->ui->checkToken($_POST['token']);
		if ((!empty($_POST['username'])) && (!empty($_POST['password'])) && (is_numeric($_POST['type'])))
		{
			$type = $_POST['type'];

			if (empty($type)) { $type = 0; }
			$boards = "";
			if ((!empty($_POST['all'])) && ($_POST['all']==1))
			{
				$boards = "%";
			} else {
				if (!empty($_POST['boards']))
				{
					foreach ($_POST['boards'] as $board)
					{
						$boards .= $board.",";
					}
				} else {
					$board = "%";
				}
			}
			if ($boards != "%") { $boards = substr($boards, 0, strlen($boards) - 1); }
			$result = $mitsuba->admin->users->addUser($_POST['username'], $_POST['password'], $type, $boards);
			if ($result == 1)
			{
				$mitsuba->admin->logAction(sprintf($lang['log/user_added'], $conn->real_escape_string($_POST['username'])));
			?>
<?php $mitsuba->admin->ui->startSection($lang['mod/user_added']); ?>
<a href="?/users"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			} else {
			?>
<?php $mitsuba->admin->ui->startSection($lang['mod/user_exists']); ?>
<a href="?/users"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			}
		} else {
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/fill_all_fields']); ?>
<a href="?/users"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
?>