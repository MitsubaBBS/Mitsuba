<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}

$mitsuba->admin->reqPermission("post.delete.single");
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && ($mitsuba->common->isBoard($_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$f = "";
			if ((!empty($_GET['f'])) && ($_GET['f'] == 1))
			{
				$f = "&f=1";
			}
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/want_delete_post']); ?>
<a href="javascript:history.back(-1);"><?php echo $lang['mod/no_big']; ?></a> <a href="?/delete_post/yes&b=<?php echo $_GET['b']; ?>&p=<?php echo $_GET['p'].$f; ?>"><?php echo $lang['mod/yes_big']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
		<?php
		}
?>