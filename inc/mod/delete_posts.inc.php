<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if ((!empty($_GET['ip'])) && (filter_var($_GET['ip'], FILTER_VALIDATE_IP)))
		{
	?>
	
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php printf($lang['mod/want_delete_ip'], $_GET['ip']); ?></h2></div>
<div class="boxcontent"><a href="?/info&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/no_big']; ?></a> <a href="?/delete_posts/yes&ip=<?php echo $_GET['ip']; ?>"><?php echo $lang['mod/yes_big']; ?></a></div>
</div>
</div>
		<?php
		}
?>