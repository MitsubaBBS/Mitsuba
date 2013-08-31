<?php
$mitsuba->admin->reqPermission("config.extras");
if (isset($_GET['m']))
{
	$mitsuba->admin->ui->checkToken($_POST['token']);
	switch ($_GET['m'])
	{
		case "ecatalog":
			$conn->query("UPDATE boards SET catalog=1;");
			break;
		case "dcatalog":
			$conn->query("UPDATE boards SET catalog=0;");
			break;
		case "ecaptcha":
			$conn->query("UPDATE boards SET captcha=1;");
			break;
		case "dcaptcha":
			$conn->query("UPDATE boards SET captcha=0;");
			break;
	}
	$result = $conn->query("SELECT * FROM boards ORDER BY short ASC;");
	while ($row = $result->fetch_assoc())
	{
		$mitsuba->caching->rebuildBoardCache($row['short']);
	}
}
?>
<?php $mitsuba->admin->ui->startSection($lang['mod/config_updated']); ?>
<a href="?/config"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>