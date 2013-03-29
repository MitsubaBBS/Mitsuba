<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
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
			$pages = 15;
			if ((!empty($_POST['pages'])) && (is_numeric($_POST['pages'])))
			{
				$pages = $_POST['pages'];
			}
			if (addBoard($conn, $_POST['short'], $_POST['name'], $_POST['des'], $_POST['msg'], $_POST['limit'], $spoilers, $noname, $ids, $embeds, $bbcode, $time_between_posts, $time_to_delete, $filesize, $pages) > 0)
			{
				?>
							<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_created']; ?></h2></div>
<div class="boxcontent"><script type="text/javascript">parent.nav.location.reload();</script><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			} else {
			?>
						<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/board_exists_mysql_error']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
			<?php
			}
		} else {
	?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/fill_all_fields']; ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
	<?php
		}
?>