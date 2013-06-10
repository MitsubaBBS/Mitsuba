<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
	if (!empty($_GET['b']))
	{
	$result = $conn->query("SELECT * FROM pages WHERE name=".$_GET['b']);
	if ($result->num_rows != 0)
	{
	if ((empty($_POST['text'])) || (empty($_POST['name'])))
	{
	$data = $result->fetch_assoc();
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/edit_page']; ?></h2></div>
<div class="boxcontent">
<form action="?/pages/edit&b=<?php echo $_GET['b']; ?>" method="POST">
<?php echo $lang['mod/name']; ?>: <input type="text" name="name" value="<?php echo $data['name']; ?>" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" value="<?php echo $data['title']; ?>"/><br />
<?php echo $lang['mod/text']; ?>: <br />
<textarea name="text" cols="70" rows="10"><?php echo $data['text']; ?></textarea><br />
<input type="checkbox" name="raw" value="1" <?php if ($data['raw'] == 1) { echo "checked='checked'"; }?> /><?php echo $lang['mod/raw_html']; ?><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div><br />
	<?php
	} else {
		if ($_SESSION['type']==2)
		{
			if (($_POST['name']=="news") || ($_POST['name']=="frontpage") || ($_POST['name']=="index"))
			{
				die($lang['mod/page_wrong_name']);
			}
			$result = $conn->query("UPDATE pages SET name='".$conn->real_escape_string($_POST['name'])."', title='".$conn->real_escape_string($_POST['title'])."', text='".$conn->real_escape_string($_POST['text'])."' WHERE name='".$conn->real_escape_string($_GET['b'])."'");
			if ($result)
			{
				$cacher->generatePage($_POST['name']);
				?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/page_updated']; ?></h2></div>
<div class="boxcontent"><a href="?/pages"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			} else {
				?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/error']; ?></h2></div>
<div class="boxcontent"><a href="?/pages"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
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