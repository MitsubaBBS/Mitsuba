<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}

		reqPermission(2);
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$f = "";
			if ((!empty($_GET['f'])) && ($_GET['f'] == 1))
			{
				$f = "&f=1";
			}
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/want_delete_post']; ?></h2></div>
<div class="boxcontent"><a href="javascript:history.back(-1);"><?php echo $lang['mod/no_big']; ?></a> <a href="?/delete_post/yes&b=<?php echo $_GET['b']; ?>&p=<?php echo $_GET['p'].$f; ?>"><?php echo $lang['mod/yes_big']; ?></a></div>
</div>
</div>
		<?php
		}
?>