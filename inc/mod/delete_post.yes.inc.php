<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("post.delete.single");
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && ($mitsuba->common->isBoard($_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$imageonly = 0;
			$mitsuba->admin->canBoard($_GET['b']);
			if ((!empty($_GET['f'])) && ($_GET['f'] == 1))
			{
				$imageonly = 1;
			}
			$mitsuba->posting->deletePost($_GET['b'], $_GET['p'], "", $imageonly, true);
			if ($imageonly == 1)
			{
			?>
	
<?php $mitsuba->admin->ui->startSection($lang['mod/file_deleted']); ?>
<a href="?/board&b=<?php echo $_GET['b']; ?>"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
		<?php
			} else {
			?>
	
<?php $mitsuba->admin->ui->startSection($lang['mod/post_deleted_short']); ?>
<a href="?/board&b=<?php echo $_GET['b']; ?>"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		}
		} else {
		
		}
?>