<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("config.rebuild");
$mitsuba->admin->ui->checkToken($_POST['token']);
		if ((!empty($_POST['links'])) && ($_POST['links']==1))
		{
			
			$mitsuba->caching->rebuildBoardLinks();
		}
		
		if ((!empty($_POST['boards'])) && ($_POST['boards']==1))
		{
			$result = $conn->query("SELECT * FROM boards ORDER BY short ASC;");
			while ($row = $result->fetch_assoc())
			{
				$mitsuba->caching->rebuildBoardCache($row['short']);
			}
			$mitsuba->admin->logAction($lang['log/rebuilt_cache']);
		}
		
		if ((!empty($_POST['thumbs'])) && ($_POST['thumbs']==1))
		{
			$result = $conn->query("SELECT * FROM boards ORDER BY short ASC;");
			while ($row = $result->fetch_assoc())
			{
				$mitsuba->caching->regenThumbnails($row['short']);
			}
			$mitsuba->admin->logAction($lang['log/rebuilt_thumbs']);
		}
		
		if ((!empty($_POST['static'])) && ($_POST['static']==1))
		{
			$mitsuba->caching->generateFrontpage();
			$mitsuba->caching->generateNews();
			$result = $conn->query("SELECT * FROM pages;");
			while ($row = $result->fetch_assoc())
			{
				$mitsuba->caching->generatePage($row['name']);
			}
		}
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/rebuilding_done']); ?>

<a href="?/rebuild"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>