<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		if (isBoard($conn, $_GET['board']))
		{
			if (!empty($_POST['name']))
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
					foreach(glob('./'.$_GET['board'].'/*.html') as $file)
					{
						if(is_file($file))
						{
							unlink($file);
						}
					}
					foreach(glob('./'.$_GET['board'].'/res/*.html') as $file)
					{
						if(is_file($file))
						{
							unlink($file);
						}
					}
				}
				$nodup = 0;
				if ((!empty($_POST['nodup'])) && ($_POST['nodup'] == 1))
				{
					$nodup = 1;
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
				if (updateBoard($conn, $_GET['board'], $_POST['name'], $_POST['des'], $_POST['msg'], $_POST['limit'], $spoilers, $noname, $ids, $embeds, $bbcode, $time_between_posts, $time_between_threads, $time_to_delete, $filesize, $pages, $hidden, $nodup, $maxchars, $anonymous))
				{
				logAction($conn, sprintf($lang['log/updated_board'], $conn->real_escape_string($_GET['board'])));
				?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_updated']; ?></h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
				} else {
				?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/some_error']; ?></h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
				}
			}
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_not_found']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
?>