<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("pages.update");
	if (!empty($_GET['b']))
	{
	$result = $conn->query("SELECT * FROM pages WHERE name=".$_GET['b']);
	if ($result->num_rows != 0)
	{
	if ((empty($_POST['text'])) || (empty($_POST['name'])))
	{
	$data = $result->fetch_assoc();
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/edit_page']); ?>

<form action="?/pages/edit&b=<?php echo $_GET['b']; ?>" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/name']; ?>: <input type="text" name="name" value="<?php echo $data['name']; ?>" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="<?php echo $data['title']; ?>"/><br />
<?php echo $lang['mod/text']; ?>: <br />
<textarea name="text" cols="70" rows="10"><?php echo $data['text']; ?></textarea><br />
<input type="checkbox" name="raw" value="1" <?php if ($data['raw'] == 1) { echo "checked='checked'"; }?> /><?php echo $lang['mod/raw_html']; ?><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?><br />
	<?php
	} else {
			$mitsuba->admin->ui->checkToken($_POST['token']);
		if ($_SESSION['type']==2)
		{
			if (($_POST['name']=="news") || ($_POST['name']=="frontpage") || ($_POST['name']=="index"))
			{
				die($lang['mod/page_wrong_name']);
			}
			$result = $conn->query("UPDATE pages SET name='".$conn->real_escape_string($_POST['name'])."', title='".$conn->real_escape_string($_POST['title'])."', text='".$conn->real_escape_string($_POST['text'])."' WHERE name='".$conn->real_escape_string($_GET['b'])."'");
			if ($result)
			{
				$mitsuba->caching->generatePage($_POST['name']);
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/page_updated']); ?>
<a href="?/pages"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			} else {
				?>
<?php $mitsuba->admin->ui->startSection($lang['mod/error']); ?>
<a href="?/pages"><?php echo $lang['mod/back']; ?></a><?php $mitsuba->admin->ui->endSection(); ?>
				<?php
			}
		}
		
		
	}
	} else {
	?>
	<meta http-equiv="refresh" content="0;URL='?/pages'" />
	<?php
	}
	} else {
	?>
	<meta http-equiv="refresh" content="0;URL='?/pages'" />
	<?php
	}
?>