<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		if ((!empty($_POST['username'])) && (!empty($_POST['password'])) && (is_numeric($_POST['type'])))
		{
			$type = $_POST['type'];

			if (empty($type)) { $type = 0; }
			$boards = "";
			if (((!empty($_POST['all'])) && ($_POST['all']==1)) || ($type == 2))
			{
				$boards = "*";
			} else {
				if (!empty($_POST['boards']))
				{
					foreach ($_POST['boards'] as $board)
					{
						$boards .= $board.",";
					}
				} else {
					$board = "*";
				}
			}
			if ($boards != "*") { $boards = substr($boards, 0, strlen($boards) - 1); }
			$result = addUser($conn, $_POST['username'], $_POST['password'], $type, $boards);
			if ($result == 1)
			{
				logAction($conn, sprintf($lang['log/user_added'], $conn->real_escape_string($_POST['username'])));
			?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_added']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			} else {
			?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/user_exists']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
			}
		} else {
		?>
								<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/fill_all_fields']; ?></h2></div>
<div class="boxcontent"><a href="?/users"><?php echo $lang['mod/back']; ?></a></div>
</div>
</div>
				<?php
		}
?>