<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
	if (!empty($_GET['m']))
	{
		if ($_GET['m'] == "del")
		{
			if (!empty($_GET['i']))
			{
				$id = $conn->real_escape_string($_GET['i']);
				deleteBoardLink($conn, $cacher, $id);
			}
		}
		if ($_GET['m'] == "addc")
		{
			if (!empty($_POST['title']))
			{
				addLinkCategory($conn, $cacher, $_POST['title']);
			}
		}
		
		if ($_GET['m'] == "up")
		{
			if (!empty($_GET['l']))
			{
				$id = $conn->real_escape_string($_GET['l']);
				moveUpCategory($conn, $cacher, $id);
			}
		}
		
		if ($_GET['m'] == "down")
		{
			if (!empty($_GET['l']))
			{
				$id = $conn->real_escape_string($_GET['l']);
				moveDownCategory($conn, $cacher, $id);
			}
		}
	}
		?>
				<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/manage_board_links']; ?></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<?php
echo getLinkTable($conn, -1);
?>
</div>
</div>
</div>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_link_category']; ?></h2></div>
<div class="boxcontent">
<b><?php echo $lang['mod/rebuild_notice']; ?></b><br />
<form action="?/links&m=addc" method="POST">
<?php echo $lang['mod/name']; ?>: <input type="text" name="title" value="<?php echo $lang['mod/category']; ?>" /><input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>