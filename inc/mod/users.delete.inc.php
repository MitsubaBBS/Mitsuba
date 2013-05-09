<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
	reqPermission(3);
		if (!empty($_GET['id']))
		{
					?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_want_delete']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/no_big']; ?></a> <a href="?/users/delete_yes&id=<?php echo $_GET['id']; ?>"><?php echo $lang['mod/yes_big']; ?></a></div>
</div>
</div>
				<?php
		} else {
						?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_not_exists']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
?>