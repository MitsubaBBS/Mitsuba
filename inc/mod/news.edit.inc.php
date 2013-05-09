<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
		if ((isset($_GET['b'])) && (is_numeric($_GET['b'])))
	{
	$result = $conn->query("SELECT * FROM news WHERE id=".$_GET['b']);
	if ($result->num_rows != 0)
	{
	if (empty($_POST['text']))
	{
	$data = $result->fetch_assoc();
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/edit_news_entry']; ?></h2></div>
<div class="boxcontent">
<form action="?/news/edit&b=<?php echo $_GET['b']; ?>" method="POST">
<?php echo $lang['mod/by']; ?>: <input type="text" name="who" value="<?php echo $data['who']; ?>" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="<?php echo $data['title']; ?>"/><br />
<?php echo $lang['mod/text']; ?>: <br />
<textarea name="text" cols="70" rows="10"><?php echo $data['text']; ?></textarea>
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div><br />
	<?php
	} else {
		if ($_SESSION['type']==3)
		{
		updateEntry($conn, 1, $_GET['b'], $_POST['who'], $_POST['title'], $_POST['text']);
		} else {
		updateEntry($conn, 1, $_GET['b'], $_POST['who'], $_POST['title'], $_POST['text'], 1);
		}
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/post_updated']; ?></h2></div>
<div class="boxcontent"><a href="?/news"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
		<?php
	}
	} else {
	?>
	<meta http-equiv="refresh" content="0;URL='?/news'" />
	<?php
	}
	} else {
	?>
	<meta http-equiv="refresh" content="0;URL='?/news'" />
	<?php
	}
?>