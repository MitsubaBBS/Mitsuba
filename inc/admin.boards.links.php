<?php

function deleteBoardLink($conn, $id)
{
	if (!is_numeric($id))
	{
		return -1;
	}
	mysqli_query($conn, "DELETE FROM links WHERE parent=".$id.";");
	mysqli_query($conn, "DELETE FROM links WHERE id=".$id.";");
	rebuildBoardLinks($conn);
}

function addLinkCategory($conn, $name)
{
	$allcat = mysqli_query($conn, "SELECT * FROM links WHERE url='' AND parent=-1");
	$catnum = mysqli_num_rows($allcat);
	$name = mysqli_real_escape_string($conn, $name);
	mysqli_query($conn, "INSERT INTO links (parent, url, url_thread, url_index, title, short) VALUES (-1, '', '', '', '".$name."', 'c".($catnum + 1)."');");
	rebuildBoardLinks($conn);
}


function updateBoardLink($conn, $id, $url, $url_thread, $url_index, $title, $short)
{
	if (!is_numeric($id))
	{
		return -1;
	}
	$title = mysqli_real_escape_string($conn, $title);
	$url = mysqli_real_escape_string($conn, $url);
	$url_thread = mysqli_real_escape_string($conn, $url_thread);
	$url_index = mysqli_real_escape_string($conn, $url_index);
	$short = mysqli_real_escape_string($conn, $short);
	$cat = mysqli_query($conn, "SELECT * FROM links WHERE id=".$id);
	if (mysqli_num_rows($cat) == 1)
	{
		mysqli_query($conn, "UPDATE links SET title='".$title."', url='".$url."', url_thread='".$url_thread."', url_index='".$url_index."', short='".$short."' WHERE id=".$id);
		rebuildBoardLinks($conn);
		return 1;
	} else {
		return 0;
	}
}

function addBoardLink($conn, $parent, $url, $url_thread, $url_index, $title, $short)
{
	$parent = mysqli_real_escape_string($conn, $parent);
	$title = mysqli_real_escape_string($conn, $title);
	$url = mysqli_real_escape_string($conn, $url);
	$url_thread = mysqli_real_escape_string($conn, $url_thread);
	$url_index = mysqli_real_escape_string($conn, $url_index);
	$short = mysqli_real_escape_string($conn, $short);
	$cat = mysqli_query($conn, "SELECT * FROM links WHERE id=".$parent);
	if (mysqli_num_rows($cat) == 1)
	{
		mysqli_query($conn, "INSERT INTO links (parent, url, url_thread, url_index, title, short) VALUES (".$parent.", '".$url."', '".$url_thread."', '".$url_index."', '".$title."', '".$short."');");
		rebuildBoardLinks($conn);
		return 1;
	} else {
		return 0;
	}
}

function moveDownCategory($conn, $id)
{
	$result = mysqli_query($conn, "SELECT * FROM links WHERE id=".$id.";");
	if (mysqli_num_rows($result) == 1)
	{
		$allcat = mysqli_query($conn, "SELECT * FROM links WHERE url='' AND parent=-1");
		$row = mysqli_fetch_assoc($result);
		$curpos = substr($row['short'], 1);
		$catnum = mysqli_num_rows($allcat);
		if ($curpos < $catnum)
		{
			mysqli_query($conn, "UPDATE links SET short='c".($curpos)."' WHERE short='c".($curpos+1)."';");
			mysqli_query($conn, "UPDATE links SET short='c".($curpos+1)."' WHERE id=".$id);
			rebuildBoardLinks($conn);
		}
		return 1;
	} else {
		return 0;
	}
}

function moveUpCategory($conn, $id)
{
	$result = mysqli_query($conn, "SELECT * FROM links WHERE id=".$id.";");
	if (mysqli_num_rows($result) == 1)
	{
		//$allcat = mysqli_query($conn, "SELECT * FROM links WHERE url='' AND parent=-1");
		$row = mysqli_fetch_assoc($result);
		$curpos = substr($row['short'], 1);
		//$catnum = mysqli_num_rows($allcat);
		if ($curpos > 1)
		{
			mysqli_query($conn, "UPDATE links SET short='c".($curpos)."' WHERE short='c".($curpos-1)."';");
			mysqli_query($conn, "UPDATE links SET short='c".($curpos-1)."' WHERE id=".$id);
			rebuildBoardLinks($conn);
		}
		return 1;
	} else {
		return 0;
	}
}

function getLinkTable($conn, $id)
{
$result = mysqli_query($conn, "SELECT * FROM links WHERE parent=".$id." ORDER BY short ASC, title ASC, id DESC;");
	if (mysqli_num_rows($result) > 0)
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

while ($row = mysqli_fetch_assoc($result))
{
$table .= "<tr>";
if (empty($row['url'])){
$table .= "<td colspan=2 style='text-align: center;'><b>".$row['title']."</b> <a href='?/links/up&l=".$row['id']."'>Up</a> <a href='?/links/down&l=".$row['id']."'>Down</a> <a href='?/links/add&p=".$row['id']."'>Add child</a></td>";
} else {
$table .= "<td>".$row['short']."</td>";
$table .= "<td>".$row['title']."</td>";
}
$table .= "<td><a href='?/links/edit&i=".$row['id']."'>Edit</a></td>";
$table .= "<td><a href='?/links/delete&i=".$row['id']."'>Delete</a></td>";
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