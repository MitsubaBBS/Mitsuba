<?php

namespace Mitsuba;

class Frontpage

{
	private $conn;
	private $config;
	private $mitsuba;
	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
		$this->config = $this->mitsuba->config;
	}
	function generateFrontpage($action = "none")

	{
	
		$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$file .= '<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<title>'.$this->config['sitename'].'</title>';
$first_default = 1;
$styles = $this->conn->query("SELECT * FROM styles ORDER BY `default` DESC");
while ($row = $styles->fetch_assoc())
{
	if ($first_default == 1)
	{
		$file .= '<link rel="stylesheet" id="switch" href="'.$this->mitsuba->getPath($row['path'], "index", $row['relative']).'">';
		$first_default = 0;
	}

	$file .= '<link rel="alternate stylesheet" style="text/css" href="'.$this->mitsuba->getPath($row['path'], "index", $row['relative']).'" title="'.$row['name'].'">';
}
$file .= "<script type='text/javascript' src='./js/style.js'></script>
</head>";

		$file .= '<body>
	<div id="doc">
		<div id="hd">
			<div id="logo">
				<h1>'.$this->config['sitename'].'</h1>
			</div>
		</div>

		<div id="bd">';
		$file .= '<div class="box-outer top-box" id="boards">
				<div class="box-inner">
					<div class="boxbar">
						<h2>Boards</h2>
					</div>
					<div class="boxcontent">';
		$cats = $this->conn->query("SELECT * FROM links WHERE parent=-1 ORDER BY short ASC;");
		while ($row = $cats->fetch_assoc())
		{
			$file .= '<div class="column">';
			$file .= '<h3 style="text-decoration: underline; display: inline;">'.$row['title'].'</h3>';
			$file .= '<ul>';
			$children = $this->conn->query("SELECT * FROM links WHERE parent=".$row['id']." ORDER BY short ASC");
			while ($child = $children->fetch_assoc())
			{
				if (!empty($child['url_index']))
				{
					$file .= '<li>
						<a class="boardlink" href="'.$child['url_index'].'" title="'.$child['title'].'">'.$child['title'].'</a>
					</li>';
				} else {
					$file .= '<li>
						<a class="boardlink" href="'.$child['url'].'" title="'.$child['title'].'">'.$child['title'].'</a>
					</li>';
				}
			}
			$file .= '</div>';
		}

		$file .= '<br class="clear-bug" />
					</div>
				</div>
			</div>';



		$file .= '<div class="wrapper">
				<div class="left-boxesz">
					<div class="box-outer left-box" id="recent-images">
						<div class="box-inner">
							<div class="boxbar">
								<h2>Recent Images</h2>
							</div>
							<div class="boxcontent">';

		$recent_images = $this->conn->query("SELECT posts.*, boards.hidden, boards.unlisted FROM posts LEFT JOIN boards ON posts.board=boards.short WHERE boards.hidden=0 AND boards.unlisted=0 AND filename<>'' AND filename<>'deleted' AND filename NOT LIKE 'embed%' AND filename NOT LIKE 'spoiler%' AND deleted=0 ORDER BY date DESC LIMIT 0, 3;");
		while ($row = $recent_images->fetch_assoc())
		{
			$postfile = $row['id'].".html#p".$row['id'];
			if (!empty($row['resto'])) { $postfile = $row['resto'].".html#p".$row['id']; }
			$file .= '<ul>
				<li>
					<a class="tooltiplink-ws boardlink" href="./'.$row['board'].'/res/'.$postfile.'" /><img alt="'.$row['orig_filename'].'" height="'.$row['t_h'].'" src="./'.$row['board'].'/src/thumb/'.$row['filename'].'" width="'.$row['t_w'].'" /></a>
				</li>
			</ul>';
		}
		$file .= '</div>
						</div>
					</div>
				</div>';


		$file .= '<div class="right-boxess">
					<div class="box-outer right-box" id="recent-threads">
						<div class="box-inner">
							<div class="boxbar">
								<h2>Latest Posts</h2>
								<div class="yui-skin-sam menubutton" id="options-container"></div>
							</div>
							<div class="boxcontent">';
		$recent_posts = $this->conn->query("SELECT posts.*, boards.hidden, boards.unlisted, boards.name AS bname FROM posts LEFT JOIN boards ON posts.board=boards.short WHERE boards.hidden=0 AND boards.unlisted=0 AND deleted=0 ORDER BY date DESC LIMIT 0, 15;");
		while ($row = $recent_posts->fetch_assoc())
		{
			$postfile = $row['id'].".html#p".$row['id'];
			if (!empty($row['resto'])) { $postfile = $row['resto'].".html#p".$row['id']; }
			$file .= '<ul>
									<li>'.$row['bname'].': <a class="tooltiplink-ws boardlink" href="./'.$row['board'].'/res/'.$postfile.'" >'.htmlspecialchars(substr($row['comment'], 0, 30)).'...</a>
									</li>
								</ul>';
		}

		$file .= '</div>
						</div>
					</div>
				</div>';

		$file .= '<div class="box-outer news-box">
			<div class="box-inner">
			<div class="boxbar"><h2>News</h2></div>
			<div class="boxcontent">';
		$result = $this->conn->query("SELECT * FROM news ORDER BY date DESC;");
		while ($row = $result->fetch_assoc())
		{
			$file .= '<div class="content">';
			$file .= '<h3><span class="newssub">'.$row['title'].' by '.$row['who'].' - '.date("d/m/Y @ H:i", $row['date']).'</span></span></h3>';
			$file .= $row['text'];
			$file .= '</div>';
		}
		$file .= '</div>
			</div>
			</div>
			';

		$file .= '<div class="box-outer stats-box">
			<div class="box-inner">
			<div class="boxbar"><h2>Stats</h2></div>
			<div class="boxcontent">';

$result = $this->conn->query("SELECT * FROM posts");
$num_rows = $result->num_rows;

$result = $this->conn->query("SELECT DISTINCT ip FROM posts");
$num_users = $result->num_rows;

$result = $this->conn->query("SELECT sum(orig_filesize) FROM posts");
$num_bytes = $result->fetch_array()[0];

		{

			$file .= '<li>Total posts: '.$num_rows.'</li>
					  <li>Unique posters: '.$num_users.'</li>
					  <li>Active content: '.$this->mitsuba->common->human_filesize($num_bytes).'</li>
			';
		}
		$file .= '</div>
			</div>
			</div>
			</div>';



		$file .= '</div>
				</div>
			</div>
		</div>
		<div id="ft" class=" ">
			<br class="clear-bug">
			<div id="copyright" class=" ">- <a href="http://github.com/MitsubaBBS/Mitsuba">mitsuba</a> + <a href="https://github.com/infamousbutterly/Mitsuba/">homepage made with butter</a> -</div>
		</div>
	</div>
</body>
</html>';
	
		$handle = fopen("./".$this->config['frontpage_url'], "w");
		fwrite($handle, $file);
		fclose($handle);
	}

	function generateNews()
	{
		
		$file = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		$file .= '<html>
			<head>
			<title>'.$this->config['sitename'].'</title>';
			$first_default = 1;
			$styles = $this->conn->query("SELECT * FROM styles ORDER BY `default` DESC");
			while ($row = $styles->fetch_assoc())
			{
				if ($first_default == 1)
				{
					$file .= '<link rel="stylesheet" id="switch" href="'.$this->mitsuba->getPath($row['path'], "index", $row['relative']).'">';
					$first_default = 0;
				}
				$file .= '<link rel="alternate stylesheet" style="text/css" href="'.$this->mitsuba->getPath($row['path'], "index", $row['relative']).'" title="'.$row['name'].'">';
			}
			$file .= "
			<script type='text/javascript' src='./js/style.js'></script>
			</head>
			<body>";
		$file .= '<div id="doc">
			<br /><br />';
		$file .= '<div class="box-outer top-box">
			<div class="box-inner">
			<div class="boxbar"><h2>News</h2></div>
			<div class="boxcontent">';
		$result = $this->conn->query("SELECT * FROM news ORDER BY date DESC;");
		while ($row = $result->fetch_assoc())
		{
			$file .= '<div class="content">';
			$file .= '<h3><span class="newssub">'.$row['title'].' by '.$row['who'].' - '.date("d/m/Y @ H:i", $row['date']).'</span></span></h3>';
			$file .= $row['text'];
			$file .= '</div>';
		}
		$file .= '</div>
			</div>
			</div>
			</div>
			</body>
			</html>';

		$handle = fopen("./".$this->config['news_url'], "w");
		fwrite($handle, $file);
		fclose($handle);
	}
}
?>