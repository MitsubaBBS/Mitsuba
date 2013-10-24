<?php
namespace Mitsuba\Admin;
class Links {
	private $conn;
	private $mitsuba;

	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
	}

	function deleteBoardLink($id)
	{
		if (!is_numeric($id))
		{
			return -1;
		}
		$this->conn->query("DELETE FROM links WHERE parent=".$id.";");
		$this->conn->query("DELETE FROM links WHERE id=".$id.";");
		$this->mitsuba->caching->rebuildBoardLinks();
	}

	function addLinkCategory($name)
	{
		$allcat = $this->conn->query("SELECT * FROM links WHERE url='' AND parent=-1");
		$catnum = $allcat->num_rows;
		$name = $this->conn->real_escape_string($name);
		$this->conn->query("INSERT INTO links (parent, url, relative, title, short) VALUES (-1, '', 0, '".$name."', 'c".($catnum + 1)."');");
		$this->mitsuba->caching->rebuildBoardLinks();
	}


	function updateBoardLink($id, $url, $relativity, $title, $short)
	{
		if (!is_numeric($id))
		{
			return -1;
		}
		$title = $this->conn->real_escape_string($title);
		$url = $this->conn->real_escape_string($url);
		if (!is_numeric($relativity))
		{
			$relativity = 1;
		}
		$short = $this->conn->real_escape_string($short);
		$cat = $this->conn->query("SELECT * FROM links WHERE id=".$id);
		if ($cat->num_rows == 1)
		{
			$this->conn->query("UPDATE links SET title='".$title."', url='".$url."', relative=".$relativity.", short='".$short."' WHERE id=".$id);
			$this->mitsuba->caching->rebuildBoardLinks();
			return 1;
		} else {
			return 0;
		}
	}

	function addBoardLink($parent, $url, $relativity, $title, $short)
	{
		$parent = $this->conn->real_escape_string($parent);
		$title = $this->conn->real_escape_string($title);
		$url = $this->conn->real_escape_string($url);
		$short = $this->conn->real_escape_string($short);
		if (!is_numeric($relativity))
		{
			$relativity = 1;
		}
		$cat = $this->conn->query("SELECT * FROM links WHERE id=".$parent);
		if ($cat->num_rows == 1)
		{
			$this->conn->query("INSERT INTO links (parent, url, relative, title, short) VALUES (".$parent.", '".$url."', ".$relativity.", '".$title."', '".$short."');");
			$this->mitsuba->caching->rebuildBoardLinks();
			return 1;
		} else {
			return 0;
		}
	}

	function moveDownCategory($id)
	{
		$result = $this->conn->query("SELECT * FROM links WHERE id=".$id.";");
		if ($result->num_rows == 1)
		{
			$allcat = $this->conn->query("SELECT * FROM links WHERE url='' AND parent=-1");
			$row = $result->fetch_assoc();
			$curpos = substr($row['short'], 1);
			$catnum = $allcat->num_rows;
			if ($curpos < $catnum)
			{
				$this->conn->query("UPDATE links SET short='c".($curpos)."' WHERE short='c".($curpos+1)."';");
				$this->conn->query("UPDATE links SET short='c".($curpos+1)."' WHERE id=".$id);
				$this->mitsuba->caching->rebuildBoardLinks();
			}
			return 1;
		} else {
			return 0;
		}
	}

	function moveUpCategory($id)
	{
		$result = $this->conn->query("SELECT * FROM links WHERE id=".$id.";");
		if ($result->num_rows == 1)
		{
			//$allcat = $this->conn->query("SELECT * FROM links WHERE url='' AND parent=-1");
			$row = $result->fetch_assoc();
			$curpos = substr($row['short'], 1);
			//$catnum = $allcat->num_rows;
			if ($curpos > 1)
			{
				$this->conn->query("UPDATE links SET short='c".($curpos)."' WHERE short='c".($curpos-1)."';");
				$this->conn->query("UPDATE links SET short='c".($curpos-1)."' WHERE id=".$id);
				$this->mitsuba->caching->rebuildBoardLinks();
			}
			return 1;
		} else {
			return 0;
		}
	}

	function getLinkTable($id)
	{
		$result = $this->conn->query("SELECT * FROM links WHERE parent=".$id." ORDER BY short ASC, title ASC, id DESC;");
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
			$table .= "<td class='text-center'><a href='?/links/edit&i=".$row['id']."'>Edit</a></td>";
			$table .= "<td class='text-center'><a href='?/links&m=del&i=".$row['id']."'>Delete</a></td>";
			$table .= "</tr>";
			$t2 = $this->getLinkTable($row['id']);
			if (!empty($t2))
			{
				$table .= "<tr><td colspan=6 style='text-align: center;'>".$t2."</td></tr>";
			}
		}
		$table .= "</tbody></table>";
		return $table;
	}
}
?>