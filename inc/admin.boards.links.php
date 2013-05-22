<?php

function deleteBoardLink($conn, $cacher, $id)
{
	if (!is_numeric($id))
	{
		return -1;
	}
	$conn->query("DELETE FROM links WHERE parent=".$id.";");
	$conn->query("DELETE FROM links WHERE id=".$id.";");
	$cacher->rebuildBoardLinks();
}

function addLinkCategory($conn, $cacher, $name)
{
	$allcat = $conn->query("SELECT * FROM links WHERE url='' AND parent=-1");
	$catnum = $allcat->num_rows;
	$name = $conn->real_escape_string($name);
	$conn->query("INSERT INTO links (parent, url, url_thread, url_index, title, short) VALUES (-1, '', '', '', '".$name."', 'c".($catnum + 1)."');");
	$cacher->rebuildBoardLinks();
}


function updateBoardLink($conn, $cacher, $id, $url, $url_thread, $url_index, $title, $short)
{
	if (!is_numeric($id))
	{
		return -1;
	}
	$title = $conn->real_escape_string($title);
	$url = $conn->real_escape_string($url);
	$url_thread = $conn->real_escape_string($url_thread);
	$url_index = $conn->real_escape_string($url_index);
	$short = $conn->real_escape_string($short);
	$cat = $conn->query("SELECT * FROM links WHERE id=".$id);
	if ($cat->num_rows == 1)
	{
		$conn->query("UPDATE links SET title='".$title."', url='".$url."', url_thread='".$url_thread."', url_index='".$url_index."', short='".$short."' WHERE id=".$id);
		$cacher->rebuildBoardLinks();
		return 1;
	} else {
		return 0;
	}
}

function addBoardLink($conn, $cacher, $parent, $url, $url_thread, $url_index, $title, $short)
{
	$parent = $conn->real_escape_string($parent);
	$title = $conn->real_escape_string($title);
	$url = $conn->real_escape_string($url);
	$url_thread = $conn->real_escape_string($url_thread);
	$url_index = $conn->real_escape_string($url_index);
	$short = $conn->real_escape_string($short);
	$cat = $conn->query("SELECT * FROM links WHERE id=".$parent);
	if ($cat->num_rows == 1)
	{
		$conn->query("INSERT INTO links (parent, url, url_thread, url_index, title, short) VALUES (".$parent.", '".$url."', '".$url_thread."', '".$url_index."', '".$title."', '".$short."');");
		$cacher->rebuildBoardLinks();
		return 1;
	} else {
		return 0;
	}
}

function moveDownCategory($conn, $cacher, $id)
{
	$result = $conn->query("SELECT * FROM links WHERE id=".$id.";");
	if ($result->num_rows == 1)
	{
		$allcat = $conn->query("SELECT * FROM links WHERE url='' AND parent=-1");
		$row = $result->fetch_assoc();
		$curpos = substr($row['short'], 1);
		$catnum = $allcat->num_rows;
		if ($curpos < $catnum)
		{
			$conn->query("UPDATE links SET short='c".($curpos)."' WHERE short='c".($curpos+1)."';");
			$conn->query("UPDATE links SET short='c".($curpos+1)."' WHERE id=".$id);
			$cacher->rebuildBoardLinks();
		}
		return 1;
	} else {
		return 0;
	}
}

function moveUpCategory($conn, $cacher, $id)
{
	$result = $conn->query("SELECT * FROM links WHERE id=".$id.";");
	if ($result->num_rows == 1)
	{
		//$allcat = $conn->query("SELECT * FROM links WHERE url='' AND parent=-1");
		$row = $result->fetch_assoc();
		$curpos = substr($row['short'], 1);
		//$catnum = $allcat->num_rows;
		if ($curpos > 1)
		{
			$conn->query("UPDATE links SET short='c".($curpos)."' WHERE short='c".($curpos-1)."';");
			$conn->query("UPDATE links SET short='c".($curpos-1)."' WHERE id=".$id);
			$cacher->rebuildBoardLinks();
		}
		return 1;
	} else {
		return 0;
	}
}

function getLinkTable($conn, $id)
{
$result = $conn->query("SELECT * FROM links WHERE parent=".$id." ORDER BY short ASC, title ASC, id DESC;");
	if ($result->num_rows > 0)
	{
		if ($id != -1) { $table = "<table style='width: 92% !important;'>"; } else { $table = "<table style='width: 100%;'>"; }
$table .= "<thead>
<tr>
<td>Short</td>
<td>Title</td>
<td style='width: 40px;'>Edit</td>
<td style='width: 40px;'>Delete</td>
</tr>
</thead>
<tbody>";
	} else {
		return "";
	}

while ($row = $result->fetch_assoc())
{
$table .= "<tr>";
if (empty($row['url'])){
$table .= "<td colspan=2 style='text-align: center;'><b>".$row['title']."</b> <a href='?/links&m=up&l=".$row['id']."'>Up</a> <a href='?/links&m=down&l=".$row['id']."'>Down</a> <a href='?/links/add&p=".$row['id']."'>Add child</a></td>";
} else {
$table .= "<td>".$row['short']."</td>";
$table .= "<td>".$row['title']."</td>";
}
$table .= "<td><center><a href='?/links/edit&i=".$row['id']."'>Edit</a></center></td>";
$table .= "<td><center><a href='?/links&m=del&i=".$row['id']."'>Delete</a></center></td>";
$table .= "</tr>";
$t2 = getLinkTable($conn, $row['id']);
if (!empty($t2))
{
$table .= "<tr><td colspan=6 style='text-align: center;'>".$t2."</td></tr>";
}
}
$table .= "</tbody>
</table>";
return $table;
}
?>