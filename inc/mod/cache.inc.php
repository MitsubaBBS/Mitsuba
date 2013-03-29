<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
reqPermission(2);
		if ((!empty($_POST['links'])) && ($_POST['links']==1))
		{
			
			rebuildBoardLinks($conn);
		}
		
		if ((!empty($_POST['styles'])) && ($_POST['styles']==1))
		{
			rebuildStyles($conn);
		}
		
		
		if ((!empty($_POST['boards'])) && ($_POST['boards']==1))
		{
			$result = $conn->query("SELECT * FROM boards ORDER BY short ASC;");
			while ($row = $result->fetch_assoc())
			{
				rebuildBoardCache($conn, $row['short']);
			}
		}
		
		if ((!empty($_POST['static'])) && ($_POST['static']==1))
		{
			generateFrontpage($conn);
			generateNews($conn);
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