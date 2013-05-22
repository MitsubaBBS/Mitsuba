<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
?>
			<div class="box-outer top-box">
<div class="box-inner">
<div class="boxbar"><h2><?php echo $lang['mod/locked']; ?></h2></div>
<div class="boxcontent">
<table>
<thead>
<tr>
<td><?php echo $lang['mod/id']; ?></td>
<td><?php echo $lang['mod/comment']; ?></td>
<td><?php echo $lang['mod/unlock']; ?></td>
</tr>
</thead>
<tbody>
	<?php
	require_once( "./jbbcode/Parser.php" );
	$parser = new JBBCode\Parser();
	$bbcode = $conn->query("SELECT * FROM bbcodes;");
	
	while ($row = $bbcode->fetch_assoc())
	{
		$parser->addBBCode($row['name'], $row['code']);
	}
	$threads = $conn->query("SELECT * FROM posts WHERE locked=1 AND resto=0 ORDER BY lastbumped DESC;");
	while ($thread = $threads->fetch_assoc())
	{
		echo "<tr>";
		echo "<td><center><a href='?/board&b=".$thread['board']."&t=".$thread['id']."#p".$thread['id']."'>/".$thread['board']."/".$thread['id']."</a></center></td>";
		if ($thread['raw'] == 0)
		{
			echo "<td>".$this->processComment($thread['board'], $thread['comment'], $parser, 2)."</td>";
		} elseif ($thread['raw'] == 2)
		{
			echo "<td>".$this->processComment($thread['board'], $thread['comment'], $parser, 2, 0)."</td>";
		} else {
			echo "<td>".$thread['comment']."</td>";
		}
		echo "<td><center><a href='?/locked/toggle&b=".$thread['board']."&t=".$thread['id']."'>".$lang['mod/unlock']."</a></center></td>";
		echo "</tr>";
	}
	?>
</tbody>
</table>
</div>
</div>
</div>