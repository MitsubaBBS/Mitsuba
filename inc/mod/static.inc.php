<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(3);
		if ((!empty($_POST['frontpage'])) && ($_POST['frontpage']==1))
		{
			$cacher->generateFrontpage();
		}
		
		if ((!empty($_POST['news'])) && ($_POST['news']==1))
		{
			$cacher->generateNews();
		}

		$result = $conn->query("SELECT * FROM pages;");
		while ($row = $result->fetch_assoc())
		{
			if ((!empty($_POST[$row['name']])) && ($_POST[$row['name']] == 1))
			{
				$cacher->generatePage($row['name']);
			}
		}
		?>
					<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/rebuilding_done']; ?></h2></div>
<div class="boxcontent">
<a href="?/rebuild"><?php echo $lang['mod/back']; ?></a>
</div>
</div>
</div>