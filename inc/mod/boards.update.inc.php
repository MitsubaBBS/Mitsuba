<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
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
				if (updateBoard($conn, $_GET['board'], $_POST['name'], $_POST['des'], $_POST['msg'], $_POST['limit'], $spoilers, $noname, $ids, $embeds))
				{
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