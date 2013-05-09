<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		if (!empty($_GET['n']))
		{
		$result = $conn->query("SELECT * FROM bbcodes WHERE name='".$conn->real_escape_string($_GET['n'])."'");
		if ($result->num_rows == 1)
		{
		$binfo = $result->fetch_assoc();
		?>
		<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/edit_bbcode']; ?></h2></div>
<div class="boxcontent">
<form action="?/bbcodes" method="POST">
<input type="hidden" name="mode" value="edit">
<input type="hidden" name="name2" value="<?php echo $conn->real_escape_string($_GET['n']); ?>">
<?php echo $lang['mod/bbcode']; ?>: <input type="text" name="name" value="<?php echo $binfo['name']; ?>"/><br />
<?php echo $lang['mod/html_code']; ?>:<textarea cols=40 rows=9 name="code"><?php echo $binfo['code']; ?>"</textarea><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
</div>
</div>
</div>
		<?php
		}
		}
?>