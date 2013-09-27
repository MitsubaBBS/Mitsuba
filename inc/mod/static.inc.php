<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("config.rebuild");
$mitsuba->admin->ui->checkToken($_POST['token']);
		if ((!empty($_POST['frontpage'])) && ($_POST['frontpage']==1))
		{
			$mitsuba->caching->generateFrontpage();
		}
		
		if ((!empty($_POST['news'])) && ($_POST['news']==1))
		{
			$mitsuba->caching->generateNews();
		}

		$result = $conn->query("SELECT * FROM pages;");
		while ($row = $result->fetch_assoc())
		{
			if ((!empty($_POST[$row['name']])) && ($_POST[$row['name']] == 1))
			{
				$mitsuba->caching->generatePage($row['name']);
			}
		}
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/rebuilding_done']); ?>

<a href="?/rebuild"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>