<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission(3);
$mitsuba->admin->ui->checkToken($_POST['token']);
		if ((!empty($_POST['short'])) && (!empty($_POST['name'])))
		{
			$spoilers = 0;
			if ((!empty($_POST['spoilers'])) && ($_POST['spoilers'] == 1))
			{
				$spoilers = 1;
			}
			$noname = 0;
			if ((!empty($_POST['noname'])) && ($_POST['noname'] == 1))
			{
				$noname = 1;
			}
			$ids = 0;
			if ((!empty($_POST['ids'])) && ($_POST['ids'] == 1))
			{
				$ids = 1;
			}
			$embeds = 0;
			if ((!empty($_POST['embeds'])) && ($_POST['embeds'] == 1))
			{
				$embeds = 1;
			}
			$bbcode = 0;
			if ((!empty($_POST['bbcode'])) && ($_POST['bbcode'] == 1))
			{
				$bbcode = 1;
			}
			$hidden = 0;
			if ((!empty($_POST['hidden'])) && ($_POST['hidden'] == 1))
			{
				$hidden = 1;
			}
			$unlisted = 0;
			if ((!empty($_POST['unlisted'])) && ($_POST['unlisted'] == 1))
			{
				$unlisted = 1;
			}
			$nodup = 0;
			if ((!empty($_POST['nodup'])) && ($_POST['nodup'] == 1))
			{
				$nodup = 1;
			}
			$nofile = 0;
			if ((!empty($_POST['nofile'])) && ($_POST['nofile'] == 1))
			{
				$nofile = 1;
			}
			$catalog = 0;
			if ((!empty($_POST['catalog'])) && ($_POST['catalog'] == 1))
			{
				$catalog = 1;
			}
			$captcha = 0;
			if ((!empty($_POST['captcha'])) && ($_POST['captcha'] == 1))
			{
				$captcha = 1;
			}
			$filesize = 2097152;
			if ((!empty($_POST['filesize'])) && (is_numeric($_POST['filesize'])))
			{
				$filesize = $_POST['filesize'];
			}
			$time_to_delete = 120;
			if ((!empty($_POST['time_to_delete'])) && (is_numeric($_POST['time_to_delete'])))
			{
				$time_to_delete = $_POST['time_to_delete'];
			}
			$time_between_posts = 20;
			if ((!empty($_POST['time_between_posts'])) && (is_numeric($_POST['time_between_posts'])))
			{
				$time_between_posts = $_POST['time_between_posts'];
			}
			$time_between_threads = 60;
			if ((!empty($_POST['time_between_threads'])) && (is_numeric($_POST['time_between_threads'])))
			{
				$time_between_threads = $_POST['time_between_threads'];
			}
			$maxchars = 2000;
			if ((!empty($_POST['maxchars'])) && (is_numeric($_POST['maxchars'])))
			{
				$maxchars = $_POST['maxchars'];
			}
			$pages = 15;
			if ((!empty($_POST['pages'])) && (is_numeric($_POST['pages'])))
			{
				$pages = $_POST['pages'];
			}
			$anonymous = "Anonymous";
			if (!empty($_POST['anonymous']))
			{
				$anonymous = $_POST['anonymous'];
			}
			$extensions = "png,jpg,gif";
			if ($mitsuba->admin->boards->addBoard($_POST['short'], "imageboard", $_POST['name'], $_POST['des'], $_POST['msg'], $_POST['limit'], $spoilers, $noname, $ids, $embeds, $bbcode, $time_between_posts, $time_between_threads, $time_to_delete, $filesize, $pages, $hidden, $unlisted, $nodup, $nofile, $maxchars, $anonymous, $extensions, $catalog, $captcha) > 0)
			{
				$mitsuba->admin->logAction(sprintf($lang['log/added_board'], $conn->real_escape_string($_POST['short'])));
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_created']); ?>
<script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			} else {
			?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_exists_mysql_error']); ?>
<a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
			<?php
			}
		} else {
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/fill_all_fields']); ?>
<a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
	<?php
		}
?>