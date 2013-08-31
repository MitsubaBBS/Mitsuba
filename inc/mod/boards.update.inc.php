<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("boards.update");
$mitsuba->admin->ui->checkToken($_POST['token']);
		if ($bdata = $mitsuba->common->isBoard($_GET['board']))
		{
			if (!empty($_POST['name']))
			{
				$spoilers = 0;
				$nodup = 0;
				$noname = 0;
				$ids = 0;
				$embeds = 0;
				$nofile = 0;
				$catalog = 0;
				$limit = 0;
				$bbcode = 0;
				$hidden = 0;
				$unlisted = 0;
				$captcha = 0;
				$filesize = 2097152;
				$time_to_delete = 120;
				$time_between_posts = 20;
				$time_between_threads = 60;
				$maxchars = 2000;
				$pages = 15;
				$anonymous = "Anonymous";
				$extensions = "png,jpg,gif";
				$boards = "";
				$allow_replies = 0;
				$file_replies = 0;
				$links = "";
				$files = 15;
				if ((empty($_POST['limit'])) || (!is_numeric($_POST['limit'])))
				{
					$limit = 0;
				}
				if ((!empty($_POST['spoilers'])) && ($_POST['spoilers'] == 1))
				{
					$spoilers = 1;
				}
				if ((!empty($_POST['noname'])) && ($_POST['noname'] == 1))
				{
					$noname = 1;
				}
				if ((!empty($_POST['ids'])) && ($_POST['ids'] == 1))
				{
					$ids = 1;
				}
				if ((!empty($_POST['embeds'])) && ($_POST['embeds'] == 1))
				{
					$embeds = 1;
				}
				if ((!empty($_POST['bbcode'])) && ($_POST['bbcode'] == 1))
				{
					$bbcode = 1;
				}
				if ((!empty($_POST['hidden'])) && ($_POST['hidden'] == 1))
				{
					$hidden = 1;
				}
				if ((!empty($_POST['unlisted'])) && ($_POST['unlisted'] == 1))
				{
					$unlisted = 1;
				}
				if ((!empty($_POST['nodup'])) && ($_POST['nodup'] == 1))
				{
					$nodup = 1;
				}
				if ((!empty($_POST['nofile'])) && ($_POST['nofile'] == 1))
				{
					$nofile = 1;
				}
				if ((!empty($_POST['catalog'])) && ($_POST['catalog'] == 1))
				{
					$catalog = 1;
				}
				if ((!empty($_POST['captcha'])) && ($_POST['captcha'] == 1))
				{
					$captcha = 1;
				}
				if ((!empty($_POST['replies'])) && ($_POST['replies'] == 1))
				{
					$allow_replies = 1;
				}
				if ((!empty($_POST['file_replies'])) && ($_POST['file_replies'] == 1))
				{
					$file_replies = 1;
				}
				if ((!empty($_POST['filesize'])) && (is_numeric($_POST['filesize'])))
				{
					$filesize = $_POST['filesize'];
				}
				if ((!empty($_POST['time_to_delete'])) && (is_numeric($_POST['time_to_delete'])))
				{
					$time_to_delete = $_POST['time_to_delete'];
				}
				if ((!empty($_POST['time_between_posts'])) && (is_numeric($_POST['time_between_posts'])))
				{
					$time_between_posts = $_POST['time_between_posts'];
				}
				if ((!empty($_POST['time_between_threads'])) && (is_numeric($_POST['time_between_threads'])))
				{
					$time_between_threads = $_POST['time_between_threads'];
				}
				if ((!empty($_POST['maxchars'])) && (is_numeric($_POST['maxchars'])))
				{
					$maxchars = $_POST['maxchars'];
				}
				if ((!empty($_POST['pages'])) && (is_numeric($_POST['pages'])))
				{
					$pages = $_POST['pages'];
				}
				if ((!empty($_POST['files'])) && (is_numeric($_POST['files'])))
				{
					$pages = $_POST['files'];
				}
				if (!empty($_POST['anonymous']))
				{
					$anonymous = $_POST['anonymous'];
				}
				switch ($bdata['type'])
				{
					case "imageboard":
						$files = 0;
						$allow_replies = 1;
						$file_replies = 1;
						$links = "";
						$extensions = $mitsuba->admin->ui->parseList('ext', 'ext_all');
						break;
					case "textboard":
						$spoilers = 0;
						$nodup = 0;
						$filesize = 0;
						$files = 0;
						$embeds = 0;
						$nofile = 0;
						$catalog = 0;
						$allow_replies = 1;
						$file_replies = 1;
						$extensions = "";
						$links = "";
						break;
					case "fileboard":
						$pages = 0;
						$embeds = 0;
						$nofile = 0;
						$catalog = 0;
						$links = "";
						$extensions = $mitsuba->admin->ui->parseList('ext', 'ext_all');
						break;
					case "linkboard":
						$filesize = 0;
						$pages = 0;
						$files = 0;
						$embeds = 0;
						$nofile = 0;
						$catalog = 0;
						$file_replies = 1;
						$extensions = "";
						$links = $mitsuba->admin->ui->parseList('links', 'l_all');
						break;
					case "overboard":
						$noname = 0;
						$ids = 0;
						$time_between_posts = 0;
						$time_between_threads = 0;
						$time_to_delete = 0;
						$anonymous = "";
						$limit = 0;
						$filesize = 0;
						$files = 0;
						$bbcode = 0;
						$hidden = 0;
						$unlisted = 0;
						$captcha = 0;
						$spoilers = 0;
						$nodup = 0;
						$embeds = 0;
						$nofile = 0;
						$catalog = 0;
						$allow_replies = 0;
						$file_replies = 0;
						$extensions = "";
						$links = "";
						$boards = $mitsuba->admin->ui->parseList('boards', 'all');
						break;
					case "archive":
						die("Archive not supported yet");
						break;
					default:
						die("Wrong type ;___;");
						break;
				}
				if ($mitsuba->admin->boards->updateBoard($_GET['board'], $_POST['name'], $_POST['des'], $_POST['msg'], $_POST['limit'], $spoilers, $noname, $ids, $embeds, $bbcode, $time_between_posts, $time_between_threads, $time_to_delete, $filesize, $pages, $hidden, $unlisted, $nodup, $nofile, $maxchars, $anonymous, $extensions, $catalog, $captcha, $boards, $allow_replies, $file_replies, $links, $files))
				{
				$mitsuba->admin->logAction(sprintf($lang['log/updated_board'], $conn->real_escape_string($_GET['board'])));
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_updated']); ?>
<script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
				} else {
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/some_error']); ?>
<script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
				}
			}
		} else {
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_not_found']); ?>
<a href="?/boards"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
?>