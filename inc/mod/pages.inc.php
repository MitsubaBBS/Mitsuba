<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("pages.view");
if (!empty($_GET['m']))
{
	switch ($_GET['m'])
	{
		case "add":
$mitsuba->admin->reqPermission("pages.add");
			$mitsuba->admin->ui->checkToken($_POST['token']);
			if (!empty($_POST['name']))
			{
				if (($_POST['name']=="news") || ($_POST['name']=="frontpage") || ($_POST['name']=="index"))
				{
					echo $lang['mod/page_wrong_name'];
				} else {
					$result = $conn->query("INSERT INTO pages (`name`,`title`,`text`) VALUES ('".$conn->real_escape_string($_POST['name'])."', '".$conn->real_escape_string($_POST['title'])."', '".$conn->real_escape_string($_POST['text'])."')");
					$mitsuba->caching->generatePage($_POST['name']);
				}
			} else {
				echo $lang['mod/fill_all_fields'];
			}
			break;
		case "delete":
$mitsuba->admin->reqPermission("pages.delete");
			$conn->query("DELETE FROM pages WHERE name='".$conn->real_escape_string($_GET['b'])."'");
			if (file_exists("./".$_GET['b'].".html"))
			{
				unlink("./".$_GET['b'].".html");
			}
			break;
	}
}
	?>
<?php $mitsuba->admin->ui->startSection($lang['mod/all_pages']); ?>

<table>
<thead>
<tr>
<td><?php echo $lang['mod/title']; ?></td>
<td><?php echo $lang['mod/name']; ?></td>
<td><?php echo $lang['mod/edit']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM pages ORDER BY name ASC;");
while ($row = $result->fetch_assoc())
{
echo "<tr>";
echo "<td><center>".$row['title']."</td>";
echo "<td><center>".$row['name']."</center></td>";
echo "<td><center><a href='?/pages/edit&b=".$row['name']."'>".$lang['mod/edit']."</a></center></td>";
echo "<td><center><a href='?/pages&m=delete&b=".$row['name']."'>".$lang['mod/delete']."</a></center></td>";
echo "</tr>";
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>

<?php $mitsuba->admin->ui->startSection($lang['mod/add_page']); ?>

<form action="?/pages&m=add" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/name']; ?>: <input type="text" name="name" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" /><br />
<?php echo $lang['mod/text']; ?>: <br />
<textarea name="text" cols="70" rows="10"></textarea><br />
<input type="checkbox" name="raw" value="1" /><?php echo $lang['mod/raw_html']; ?><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>