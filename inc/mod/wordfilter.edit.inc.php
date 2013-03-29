<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
		if (!empty($_GET['n']))
		{
		$result = $conn->query("SELECT * FROM wordfilter WHERE id=".$conn->real_escape_string($_GET['n']));
		if ($result->num_rows == 1)
		{
		$info = $result->fetch_assoc();
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2>Edit wordfilter</h2></div>
<div class="boxcontent">
<form action="?/wordfilter" method="POST">
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="id" value="<?php echo $_GET['n']; ?>">
Search: <input type="text" name="search" value="<?php echo htmlspecialchars($info['search']); ?>"/><br />
Replace: <input type="text" name="replace" value="<?php echo htmlspecialchars($info['replace']); ?>"/><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
		}
		}
?>