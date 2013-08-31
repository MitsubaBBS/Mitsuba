<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("post.sticky");
if ((!empty($_GET['b'])) && (!empty($_GET['t'])) && ($bdata = $mitsuba->common->isBoard($_GET['b'])) && (is_numeric($_GET['t'])))
		{
			$mitsuba->admin->canBoard($_GET['b']);
			$result = $conn->query("SELECT * FROM posts WHERE id=".$_GET['t']." AND resto=0 AND board='".$_GET['b']."'");
			if ($result->num_rows == 1)
			{
				$pdata = $result->fetch_assoc();
				if ($pdata['sticky'] == 1)
				{
					$conn->query("UPDATE posts SET sticky=0 WHERE id=".$_GET['t']." AND board='".$_GET['b']."'");
					$mitsuba->caching->generatePost($_GET['b'], $_GET['t']);
				?>
	
<?php $mitsuba->admin->ui->startSection($lang['mod/unstickied']); ?>
<meta http-equiv="refresh" content="1;URL='./<?php echo $_GET['b']."/".$_GET['t']; ?>.html'" />
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
				} else {
					$conn->query("UPDATE posts SET sticky=1 WHERE id=".$_GET['t']." AND board='".$_GET['b']."'");
					$mitsuba->caching->generatePost($_GET['b'], $_GET['t']);
				?>
	
<?php $mitsuba->admin->ui->startSection($lang['mod/stickied']); ?>
<meta http-equiv="refresh" content="1;URL='./<?php echo $_GET['b']."/".$_GET['t']; ?>.html'" />
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
				}
			} else {
			?>
	
<?php $mitsuba->admin->ui->startSection($lang['mod/thread_not_found']); ?>
<?php $mitsuba->admin->ui->endSection(); ?>
		<?php
			}
		} else {
		
		}
?>