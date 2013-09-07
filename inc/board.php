<?php
namespace Mitsuba;
class Board
{
	private $conn;
	private $mitsuba;
	private $config;

	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
		$this->config = $this->mitsuba->config;
	}

	function checkSpam($comment, $board)
	{
		$spam = $this->conn->query("SELECT * FROM spamfilter WHERE active=1");
		while ($row = $spam->fetch_assoc())
		{
			if ($row['boards'] != "%")
			{
				$boards = explode(",", $row['boards']);
				if (in_array($board, $boards))
				{
					if ($row['regex'] == 1)
					{
						try {
							if (preg_match($row['search'], $comment) !== false) {
								$this->mitsuba->common->addSystemBan($_SERVER['REMOTE_ADDR'], $row['reason'], htmlspecialchars($_POST['com']), $row['expires'], "%");
								echo '<meta http-equiv="refresh" content="2;URL='."'./banned.php'".'">';
								die();
							}
						} catch (Exception $ex)
						{

						}
					} else {
						if (stripos($comment, $row['search']) !== false) {
							$this->mitsuba->common->addSystemBan($_SERVER['REMOTE_ADDR'], $row['reason'], htmlspecialchars($_POST['com']), $row['expires'], "%");
							echo '<meta http-equiv="refresh" content="2;URL='."'./banned.php'".'">';
							die();
						}
					}
				}
			} else {
				if ($row['regex'] == 1)
				{
					try {
						if (preg_match($row['search'], $comment) !== false) {
							$this->mitsuba->common->addSystemBan($_SERVER['REMOTE_ADDR'], $row['reason'], htmlspecialchars($_POST['com']), $row['expires'], "%");
							echo '<meta http-equiv="refresh" content="2;URL='."'./banned.php'".'">';
							die();
						}
					} catch (Exception $ex)
					{

					}
				} else {
					if (stripos($comment, $row['search']) !== false) {
							$this->mitsuba->common->addSystemBan($_SERVER['REMOTE_ADDR'], $row['reason'], htmlspecialchars($_POST['com']), $row['expires'], "%");
							echo '<meta http-equiv="refresh" content="2;URL='."'./banned.php'".'">';
							die();
					}
				}
			}
		}
	}

	function checkThreadDate($bdata, $return_url)
	{
		global $lang;
		$lastdate = $this->conn->query("SELECT date FROM posts WHERE ip='".$_SERVER['REMOTE_ADDR']."' AND resto=0 AND board='".$bdata['short']."' ORDER BY date DESC LIMIT 0, 1");
		if ($lastdate->num_rows == 1)
		{
			$pdate = $lastdate->fetch_assoc();
			$pdate = $pdate['date'];
			
			if (($pdate + $bdata['time_between_threads']) > time())
			{
				$this->mitsuba->common->showMsg($lang['img/error'], $lang['img/wait_more_thread']);
				exit;
			}
		}
	}

	function checkPostDate($bdata, $return_url)
	{
		global $lang;
		$lastdate = $this->conn->query("SELECT date FROM posts WHERE ip='".$_SERVER['REMOTE_ADDR']."' AND board='".$bdata['short']."' ORDER BY date DESC LIMIT 0, 1");
		if ($lastdate->num_rows == 1)
		{
			$pdate = $lastdate->fetch_assoc();
			$pdate = $pdate['date'];
			
			if (($pdate + $bdata['time_between_posts']) > time())
			{
				$this->mitsuba->common->showMsg($lang['img/error'], $lang['img/wait_more_post']);
				exit;
			}
		}
	}

	function checkEmbed($bdata, $embed, $return_url)
	{
		global $lang;
		if ($bdata['embeds']==0)
		{
			echo "<center><h1>".$lang['img/embed_not_supported']." [<a href='".$return_url."'>".$lang['img/return']."</a>]</h1></center></body></html>";
			exit;
		}
		
		$embed_table = array();
		$result = $this->conn->query("SELECT * FROM embeds;");
		while ($row = $result->fetch_assoc())
		{
			$embed_table[] = $row;
		}
		if ($this->mitsuba->common->isEmbed($embed, $embed_table))
		{
			return "embed:".$embed;
		} else {
			echo "<center><h1>".$lang['img/embed_not_supported']." [<a href='".$return_url."'>".$lang['img/return']."</a>]</h1></center></body></html>";
			exit;
		}
	}
}

?>