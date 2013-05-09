<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
	if (isset($_GET['b']))
	{
		if ($_SESSION['type']==3)
		{
			deleteEntry($conn, 1, $_GET['b']);
		} else {
			deleteEntry($conn, 1, $_GET['b'], 1);
		}
	?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/post_deleted']; ?></h2></div>
<div class="boxcontent"><a href="?/news"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
	}
?>