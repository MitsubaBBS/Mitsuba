<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
		if ((!empty($_GET['b'])) && (!empty($_GET['p'])) && (isBoard($conn, $_GET['b'])) && (is_numeric($_GET['p'])))
		{
			$imageonly = 0;
			canBoard($_GET['b']);
			if ((!empty($_GET['f'])) && ($_GET['f'] == 1))
			{
				$imageonly = 1;
			}
			deletePost($conn, $cacher, $_GET['b'], $_GET['p'], "", $imageonly, $_SESSION['type']);
			if ($imageonly == 1)
			{
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/file_deleted']; ?></h2></div>
<div class="boxcontent"><a href="?/board&b=<?php echo $_GET['b']; ?>"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
			} else {
			?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/post_deleted_short']; ?></h2></div>
<div class="boxcontent"><a href="?/board&b=<?php echo $_GET['b']; ?>"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
		}
		} else {
		
		}
?>