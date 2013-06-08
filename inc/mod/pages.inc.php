<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
if (!empty($_GET['m']))
{
	switch ($_GET['m'])
	{
		case "add":
			if (!empty($_POST['name']))
			{
				if (($_POST['name']=="news") || ($_POST['name']=="frontpage") || ($_POST['name']=="index"))
				{
					echo $lang['mod/page_wrong_name'];
				} else {
					$result = $conn->query("INSERT INTO pages (`name`,`title`,`text`) VALUES ('".$conn->real_escape_string($_POST['name'])."', '".$conn->real_escape_string($_POST['title'])."', '".$conn->real_escape_string($_POST['text'])."')");
					$cacher->generatePage($_POST['name']);
				}
			} else {
				echo $lang['mod/fill_all_fields'];
			}
			break;
		case "delete":
			$conn->query("DELETE FROM pages WHERE name='".$conn->real_escape_string($_GET['b'])."'");
			if (file_exists("./".$_GET['b'].".html"))
			{
				unlink("./".$_GET['b'].".html");
			}
			break;
	}
}
	?>
	<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/all_pages']; ?></h2></div>
<div class="boxcontent">
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
</div>
</div>
</div>

<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_page']; ?></h2></div>
<div class="boxcontent">
<form action="?/pages&m=add" method="POST">
<?php echo $lang['mod/name']; ?>: <input type="text" name="name" /><br />
<?php echo $lang['mod/title']; ?>: <input type="text" name="title" /><br />
<?php echo $lang['mod/text']; ?>: <br />
<textarea name="text" cols="70" rows="10"></textarea><br />
<input type="checkbox" name="raw" value="1" /><?php echo $lang['mod/raw_html']; ?><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>