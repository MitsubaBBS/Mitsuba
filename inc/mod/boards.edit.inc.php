<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
$mitsuba->admin->reqPermission("boards.update");
		if ($mitsuba->common->isBoard($_GET['board']))
		{
			$data = $mitsuba->common->getBoardData($_GET['board']);
			?>
<?php $mitsuba->admin->ui->startSection(sprintf($lang['mod/edit_board'], $_GET['board'])); ?>

<form action="?/boards/update&board=<?php echo $_GET['board']; ?>" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/board_type'].":"; ?>
<b>
<?php
switch ($data['type'])
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
?>
</b>
<?php if (($data['type'] == "imageboard") || ($data['type'] == "textboard") || ($data['type'] == "fileboard") || ($data['type'] == "linkboard")) { ?>
<?php echo $lang['mod/board_directory']; ?>: <input disabled type="text" name="short" maxlength=10 value="<?php echo $data['short']; ?>" /><br />
<?php echo $lang['mod/board_name']; ?>: <input type="text" name="name" maxlength=100 value="<?php echo $data['name']; ?>" /><br />
<?php echo $lang['mod/board_short']; ?>: <input type="text" name="des" maxlength=100 value="<?php echo $data['des']; ?>" /><br />
<?php echo $lang['mod/board_msg']; ?>: <br /><textarea cols=70 rows=7 name="msg"><?php echo $data['message']; ?></textarea><br />
<?php echo $lang['mod/board_limit']; ?>: <input type="text" name="limit" maxlength=9 value="<?php echo $data['bumplimit']; ?>" /><br />
<?php echo $lang['mod/board_time_between_posts']; ?>: <input type="text" name="time_between_posts" maxlength=20 value="<?php echo $data['time_between_posts']; ?>" /><br />
<?php echo $lang['mod/board_time_between_threads']; ?>: <input type="text" name="time_between_threads" maxlength=20 value="<?php echo $data['time_between_threads']; ?>" /><br />
<?php echo $lang['mod/board_time_to_delete']; ?>: <input type="text" name="time_to_delete" maxlength=20 value="<?php echo $data['time_to_delete']; ?>" /><br />
<?php echo $lang['mod/board_maxchars']; ?>: <input type="text" name="maxchars" maxlength=8 value="<?php echo $data['maxchars']; ?>" /><br />
<?php echo $lang['mod/board_default_name']; ?>: <input type="text" name="anonymous" maxlength=60 value="<?php echo $data['anonymous']; ?>" /><br />
<?php } ?>
<?php if (($data['type'] == "imageboard") || ($data['type'] == "fileboard")) { ?>
<?php echo $lang['mod/board_filesize']; ?>: <input type="text" name="filesize" maxlength=20 value="<?php echo $data['filesize']; ?>" /><br />
<?php } ?>
<?php if (($data['type'] == "imageboard") || ($data['type'] == "textboard") || ($data['type'] == "overboard")) { ?>
<?php echo $lang['mod/board_pages']; ?>: <input type="text" name="pages" maxlength=4 value="<?php echo $data['pages']; ?>" /><br />
<?php } ?>
<?php if ($data['type'] == "fileboard") { ?>
<?php echo $lang['mod/board_files']; ?>: <input type="text" name="files" maxlength=4 value="<?php echo $data['files']; ?>" /><br />
<?php } ?>
<?php echo $lang['mod/board_options']; ?>:
<?php if (($data['type'] == "imageboard") || ($data['type'] == "textboard") || ($data['type'] == "fileboard") || ($data['type'] == "linkboard")) { ?><br />
<input type="checkbox" name="noname" value="1" <?php if ($data['noname'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_no_name']; ?><br />
<input type="checkbox" name="ids" value="1" <?php if ($data['ids'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_ids']; ?><br />
<input type="checkbox" name="bbcode" value="1" <?php if ($data['bbcode'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_bbcode']; ?><br />
<input type="checkbox" name="hidden" value="1" <?php if ($data['hidden'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_hidden']; ?><br />
<input type="checkbox" name="unlisted" value="1" <?php if ($data['unlisted'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_unlisted']; ?><br />
<input type="checkbox" name="captcha" value="1" <?php if ($data['captcha'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_captcha']; ?> <br />
<?php } ?>
<?php if (($data['type'] == "imageboard") || ($data['type'] == "fileboard") || ($data['type'] == "linkboard")) { ?>
<input type="checkbox" name="spoilers" value="1" <?php if ($data['spoilers'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_spoilers']; ?><br />
<input type="checkbox" name="nodup" value="1" <?php if ($data['nodup'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_nodup']; ?>
<?php } ?><br />
<?php if ($data['type'] == "imageboard") { ?>
<input type="checkbox" name="embeds" value="1" <?php if ($data['embeds'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_embeds']; ?><br />
<input type="checkbox" name="nofile" value="1" <?php if ($data['nofile'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_nofile']; ?><br />
<input type="checkbox" name="catalog" value="1" <?php if ($data['catalog'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_catalog']; ?><br />
<?php } ?>
<?php if (($data['type'] == "fileboard") || ($data['type'] == "linkboard")) { ?>
<input type="checkbox" name="replies" value="1" <?php if ($data['allow_replies'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_allow_replies']; ?><br />
<?php } ?>
<?php if ($data['type'] == "fileboard") { ?>
<input type="checkbox" name="file_replies" value="1" <?php if ($data['file_replies'] == 1) { echo "checked "; } ?> /><?php echo $lang['mod/board_allow_file_replies']; ?><br />
<?php } ?>
<br />
<?php if (($data['type'] == "imageboard") || ($data['type'] == "fileboard")) { ?>
<?php $mitsuba->admin->ui->getExtensionList($data['extensions']); ?>
<?php } ?>
<?php if ($data['type'] == "linkboard") { ?>
<?php $mitsuba->admin->ui->getLinkList($data['links']); ?>
<?php } ?>
<?php if ($data['type'] == "overboard") { ?>
<?php $mitsuba->admin->ui->getBoardList($data['overboard_boards']); ?>
<?php } ?>
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?><br />
<?php $mitsuba->admin->ui->startSection(sprintf($lang['mod/move_board'], $_GET['board'])); ?>
<form action="?/boards/move&board=<?php echo $_GET['board']; ?>" method="POST">
<?php $mitsuba->admin->ui->getToken($path); ?>
<?php echo $lang['mod/board_new_dir']; ?>: <input type="text" name="new" maxlength=10 /><br />
<input type="submit" value="<?php echo $lang['mod/submit']; ?>" />
</form>
<?php $mitsuba->admin->ui->endSection(); ?>
<?php
		} else {
		?>
<?php $mitsuba->admin->ui->startSection($lang['mod/board_not_found']); ?>
<a href="?/boards"><?php echo $lang['mod/back']; ?></a>
<?php $mitsuba->admin->ui->endSection(); ?>
				<?php
		}
?>