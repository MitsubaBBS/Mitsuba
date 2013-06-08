<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
	$config = getConfig($conn);
	?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/rebuild_cache']; ?></h2></div>
<div class="boxcontent">
<form action="?/cache" method="POST">
<input type="checkbox" name="links" value=1 /><?php echo $lang['mod/board_links']; ?><br />
<input type="checkbox" name="boards" value=1 /><?php echo $lang['mod/all_boards']; ?><br />
<input type="checkbox" name="thumbs" value=1 /><?php echo $lang['mod/thumbnails']; ?><br />
<input type="checkbox" name="static" value=1 /><?php echo $lang['mod/all_static']; ?><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>"><br />
</form>
</div>
</div>
</div>
<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/rebuild_static']; ?></h2></div>
<div class="boxcontent">
<form action="?/static" method="POST">
<input type="checkbox" name="frontpage" value=1 /><?php echo $lang['mod/frontpage']; ?> (./<?php echo $config['frontpage_url']; ?>)</input><br />
<input type="checkbox" name="news" value=1 /><?php echo $lang['mod/news_page']; ?> (./<?php echo $config['news_url']; ?>)</input><br />
<?php
$result = $conn->query("SELECT * FROM pages;");
while ($row = $result->fetch_assoc())
{
	echo '<input type="checkbox" name="'.$row['name'].'" value=1 />'.$row['title'].' (./'.$row['name'].'.html)<br />';
}
?>
<input type="submit" value="<?php echo $lang['mod/submit']; ?>"><br />
</form>
</div>
</div>
</div>