<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
?>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/your_notes']; ?></h2></div>
<div class="boxcontent">
<?php
$result = $conn->query("SELECT * FROM notes WHERE mod_id=".$_SESSION['id']." ORDER BY created DESC;");
while ($row = $result->fetch_assoc())
{
echo '<div class="content">';
echo '<h3><span class="newssub">'.date("d/m/Y @ H:i", $row['created']).'</span> <a href="?/notes/delete&id='.$row['id'].'">Delete</a></span></h3>';
echo $row['note'];
echo '</div>';
}
?>
</div>
</div>
</div><br />
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/add_note']; ?></h2></div>
<div class="boxcontent">
<form action="?/notes/add" method="POST">
<textarea name="note" cols=70 rows=12></textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>