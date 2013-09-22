<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("boards.view");
?>
<?php $mitsuba->admin->ui->startSection($lang['mod/create_new_board']); ?>

<form action="?/boards/add" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/board_directory']; ?>: <input type="text" name="short" maxlength=10 /><br />
<?php echo $lang['mod/board_name']; ?>: <input type="text" name="name" maxlength=100 /><br />
<?php echo $lang['mod/board_short']; ?>: <input type="text" name="des" maxlength=100 /><br />
<?php echo $lang['mod/board_msg']; ?>: <br /><textarea cols=70 rows=7 name="msg"></textarea><br />
<?php echo $lang['mod/board_type']; ?>: <select name="type">
<option value="imageboard"><?php echo $lang['mod/imageboard']; ?></option>
<option value="textboard"><?php echo $lang['mod/textboard']; ?></option>
<option value="overboard"><?php echo $lang['mod/overboard']; ?></option>
<option value="fileboard"><?php echo $lang['mod/fileboard']; ?></option>
<option value="linkboard"><?php echo $lang['mod/linkboard']; ?></option>
<option value="archive"><?php echo $lang['mod/archive']; ?></option>
</select>
<span class="opt t-ib t-tb t-fb t-lb"><?php echo $lang['mod/board_limit']; ?>: <input type="text" name="limit" maxlength=9 value="0" /><br /></span>
<span class="opt t-ib t-tb t-fb t-lb"><?php echo $lang['mod/board_time_between_posts']; ?>: <input type="text" name="time_between_posts" maxlength=20 value="20" /><br /></span>
<span class="opt t-ib t-tb t-fb t-lb"><?php echo $lang['mod/board_time_between_threads']; ?>: <input type="text" name="time_between_threads" maxlength=20 value="60" /><br /></span>
<span class="opt t-ib t-tb t-fb t-lb"><?php echo $lang['mod/board_time_to_delete']; ?>: <input type="text" name="time_to_delete" maxlength=20 value="120" /><br /></span>
<span class="opt t-ib t-tb t-fb t-lb"><?php echo $lang['mod/board_maxchars']; ?>: <input type="text" name="maxchars" maxlength=8 value="2000" /><br /></span>
<span class="opt t-ib t-tb t-fb t-lb"><?php echo $lang['mod/board_default_name']; ?>: <input type="text" name="anonymous" maxlength=60 value="<?php echo $lang['img/anonymous']; ?>" /><br /></span>
<span class="opt t-ib t-fb"><?php echo $lang['mod/board_filesize']; ?>: <input type="text" name="filesize" maxlength=20 value="2097152" /><br /></span>
<span class="opt t-ib t-tb t-ob"><?php echo $lang['mod/board_pages']; ?>: <input type="text" name="pages" maxlength=4 value="15" /><br /></span>
<span class="opt t-fb"><?php echo $lang['mod/board_files']; ?>: <input type="text" name="files" maxlength=4 value="15" /><br /></span>
<?php echo $lang['mod/board_options']; ?>: 
<span class="opt t-ib t-tb t-fb t-lb"><br /><input type="checkbox" name="noname" value="1" /><?php echo $lang['mod/board_no_name']; ?></span>
<span class="opt t-ib t-tb t-fb t-lb"><br /><input type="checkbox" name="ids" value="1" /><?php echo $lang['mod/board_ids']; ?></span>
<span class="opt t-ib t-tb t-fb t-lb"><br /><input type="checkbox" name="bbcode" value="1" checked/><?php echo $lang['mod/board_bbcode']; ?></span>
<span class="opt t-ib t-tb t-fb t-lb"><br /><input type="checkbox" name="hidden" value="1"/><?php echo $lang['mod/board_hidden']; ?></span>
<span class="opt t-ib t-tb t-fb t-lb"><br /><input type="checkbox" name="unlisted" value="1"/><?php echo $lang['mod/board_unlisted']; ?></span>
<span class="opt t-ib t-tb t-fb t-lb"><br /><input type="checkbox" name="captcha" value="1"/><?php echo $lang['mod/board_captcha']; ?></span>
<span class="opt t-ib t-fb t-lb"><br /><input type="checkbox" name="spoilers" value="1" /><?php echo $lang['mod/board_spoilers']; ?></span>
<span class="opt t-ib t-fb t-lb"><br /><input type="checkbox" name="nodup" value="1"/><?php echo $lang['mod/board_nodup']; ?></span>
<span class="opt t-ib"><br /><input type="checkbox" name="embeds" value="1" /><?php echo $lang['mod/board_embeds']; ?></span>
<span class="opt t-ib"><br /><input type="checkbox" name="nofile" value="1"/><?php echo $lang['mod/board_nofile']; ?></span>
<span class="opt t-ib"><br /><input type="checkbox" name="catalog" value="1"/><?php echo $lang['mod/board_catalog']; ?></span>
<span class="opt t-fb t-lb"><br /><input type="checkbox" name="replies" value="1" checked/><?php echo $lang['mod/board_allow_replies']; ?></span>
<span class="opt t-fb"><br /><input type="checkbox" name="file_replies" value="1"/><?php echo $lang['mod/board_allow_file_replies']; ?></span>
<br />
<span class="opt t-ib t-fb"><?php $mitsuba->admin->ui->getExtensionList(); ?></span>
<span class="opt t-lb"><?php $mitsuba->admin->ui->getLinkList(); ?></span>
<span class="opt t-ob"><?php $mitsuba->admin->ui->getBoardList(); ?></span>
<br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<script type="text/javascript">
$(".opt").hide();
$(".t-ib").show();
$("select[name='type']").change(function () {
	var value = $(this).val();
	switch (value)
	{
		case "imageboard":
			$(".opt").hide();
			$(".t-ib").show();
			break;
		case "textboard":
			$(".opt").hide();
			$(".t-tb").show();
			break;
		case "overboard":
			$(".opt").hide();
			$(".t-ob").show();
			break;
		case "fileboard":
			$(".opt").hide();
			$(".t-fb").show();
			break;
		case "linkboard":
			$(".opt").hide();
			$(".t-lb").show();
			break;
		case "archive":
			$(".opt").hide();
			$(".t-ar").show();
			break;
	}
});
</script>
<?php $mitsuba->admin->ui->endSection(); ?>
<br />
<?php $mitsuba->admin->ui->startSection($lang['mod/manage_boards']); ?>

<?php echo $lang['mod/all_boards']; ?>: <br />
<table>
<thead>
<tr>
<td><?php echo $lang['mod/directory']; ?></td>
<td><?php echo $lang['mod/name']; ?></td>
<td><?php echo $lang['mod/description']; ?></td>
<td><?php echo $lang['mod/board_type']; ?></td>
<td><?php echo $lang['mod/bump_limit']; ?></td>
<td><?php echo $lang['mod/message']; ?></td>
<td><?php echo $lang['mod/special']; ?></td>
<td><?php echo $lang['mod/edit']; ?></td>
<td><?php echo $lang['mod/delete']; ?></td>
<td><?php echo $lang['mod/rebuild_cache']; ?></td>
</tr>
</thead>
<tbody>
<?php
$result = $conn->query("SELECT * FROM boards ORDER BY short ASC;");
while ($row = $result->fetch_assoc())
{
echo '<tr>';
echo "<td><center><a href='./".$row['short']."/'>/".$row['short']."/</a></center></td>";
echo "<td><center>".$row['name']."</center></td>";
echo "<td>".$row['des']."</td>";
switch ($row['type'])
{
	case "imageboard":
		echo "<td>".$lang['mod/imageboard']."</td>";
		break;
	case "textboard":
		echo "<td>".$lang['mod/textboard']."</td>";
		break;
	case "overboard":
		echo "<td>".$lang['mod/overboard']."</td>";
		break;
	case "fileboard":
		echo "<td>".$lang['mod/fileboard']."</td>";
		break;
	case "linkboard":
		echo "<td>".$lang['mod/linkboard']."</td>";
		break;
	case "archive":
		echo "<td>".$lang['mod/archive']."</td>";
		break;
	default:
		echo "<td>".$lang['mod/fool']."</td>";
		break;
}
echo "<td><center>".$row['bumplimit']."</center></td>";
if (!empty($row['message']))
{
echo "<td><center>".$lang['mod/yes']."</center></td>";
} else {
echo "<td><center>".$lang['mod/no']."</center></td>";
}
echo "<td>";
if ($row['spoilers']==1) { echo "<b>".$lang['mod/spoilers']."</b><br />"; }
if ($row['noname']==1) { echo "<b>".$lang['mod/noname']."</b><br />"; }
if ($row['ids']==1) { echo "<b>".$lang['mod/ids']."</b><br />"; }
if ($row['embeds']==1) { echo "<b>".$lang['mod/embeds']."</b><br />"; }
if ($row['bbcode']==1) { echo "<b>".$lang['mod/board_bbcode']."</b><br />"; }
if ($row['hidden']==1) { echo "<b>".$lang['mod/board_hidden']."</b><br />"; }
if ($row['nodup']==1) { echo "<b>".$lang['mod/board_nodup']."</b><br />"; }
if ($row['catalog']==1) { echo "<b>".$lang['mod/board_catalog']."</b><br />"; }
if ($row['captcha']==1) { echo "<b>".$lang['mod/board_captcha']."</b><br />"; }
if ($row['nofile']==1) { echo "<b>".$lang['mod/board_nofile']."</b><br />"; }
echo "</td>";
echo "<td><center><a href='?/boards/edit&board=".$row['short']."'>".$lang['mod/edit']."</a></center></td>";
echo "<td><center><a href='?/boards/delete&board=".$row['short']."'>".$lang['mod/delete']."</a></center></td>";
echo "<td><center><a href='?/boards/rebuild&board=".$row['short']."'>".$lang['mod/rebuild_cache']."</a></center></td>";
echo '</tr>';
}
?>
</tbody>
</table>
<?php $mitsuba->admin->ui->endSection(); ?>