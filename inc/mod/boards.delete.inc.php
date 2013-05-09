<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		if (!empty($_GET['board']))
		{
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/want_delete_board'], $_GET['board']); ?></h2></div>
<div class="boxcontent"><a href="?/boards"><?php echo $lang['mod/no_big']; ?></a> <a href="?/boards/delete_yes&board=<?php echo $_GET['board']; ?>"><?php echo $lang['mod/yes_big']; ?></a></div>
</div>
</div>
				<?php
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