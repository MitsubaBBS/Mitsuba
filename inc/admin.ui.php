<?php
namespace Mitsuba\Admin;
class UI {
	private $conn;
	private $mitsuba;

	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
	}

	function getToken($path)
	{
		global $id_salt;
		$token = "";
		if ((empty($_SESSION['tokenpath'])) || ($_SESSION['tokenpath'] != $path) || (empty($_SESSION['token'])))
		{
			$token = md5($this->mitsuba->common->randomSalt().$id_salt);
			$_SESSION['tokenpath'] = $path;
			$_SESSION['token'] = $token;
		} else {
			$token = $_SESSION['token'];
		}
		echo '<input type="hidden" name="token" value="'.$token.'" />';
	}

	function checkToken($token)
	{
		if ($_SESSION['token'] != $token)
		{
			die("Invalid form.");
		}
	}

	function getBoardList($boards = "")
	{
		global $lang;

		if ($boards == "%") $all = " checked"; else $all = '';
		echo $lang['mod/boards'].': <input type="checkbox" name="all" id="all" value=1'.$all.'/> ';
		echo "<label style='float:none;display:inline' for='all'>".$lang['mod/all']."</label>";

		?>
		<fieldset id="boardSelect">
		<?php
		if (($boards != "%") && ($boards != "")) { $boards = explode(",", $boards); }
		$result = $this->conn->query("SELECT * FROM boards ORDER BY short ASC;");
		while ($row = $result->fetch_assoc()) {
			$checked = "";
			if (($boards !== "%") && ($boards !== "")) {
				if (in_array($boards, $row['short'])) {
					$checked = " checked ";
				}
			}
			echo "<div style='float:left'>";
			echo "<label for='{$row['short']}'>/".$row['short']."/ - ".$row['name']."</label>";
			echo "<input id='{$row['short']}' type='checkbox' name='boards[]' value='".$row['short']."'".$checked."/>";
			echo "</div>";
		}
		?>
		</fieldset>
		<?php
	}

	function getLinkList($links = "")
	{
		global $lang;
		if ($links == "%")
		{
		?>
		<?php echo $lang['mod/board_links']; ?>: <input type="checkbox" name="l_all" id="l_all" onClick="$('#linkSelect').toggle()" value=1 checked/> <?php echo $lang['mod/all']; ?>
		<?php
		} else {
		?>
		<?php echo $lang['mod/board_links']; ?>: <input type="checkbox" name="l_all" id="l_all" onClick="$('#linkSelect').toggle()" value=1/> <?php echo $lang['mod/all']; ?>
		<?php
		}
		?>
		<br/>
		<fieldset id="linkSelect">
		<?php
		if (($links != "%") && ($links != "")) { $links = explode(",", $links); }
		$result = $this->conn->query("SELECT * FROM link ORDER BY name ASC;");
		while ($row = $result->fetch_assoc())
		{
		$checked = "";
		if (($links !== "%") && ($links !== ""))
		{
			if (in_array($links, $row['name']))
			{
				$checked = " checked ";
			}
		}
		echo "<label for='links'>".$row['name']."</label>";
		echo "<input type='checkbox' onClick='document.getElementById(\"all\").checked=false;' name='links[]' value='".$row['name']."'".$checked."/>";
		}
		?>
		</fieldset>
		<?php
	}

	function getExtensionList($extensions = "")
	{
		global $lang;
		if ($extensions == "%")
		{
		?>
		<?php echo $lang['mod/extensions']; ?>: <input type="checkbox" name="ext_all" id="ext_all" onClick="$('#extSelect').toggle()" value=1 checked/> <?php echo $lang['mod/all']; ?><br/>
		<?php
		} else {
		?>
		<?php echo $lang['mod/extensions']; ?>: <input type="checkbox" name="ext_all" id="ext_all" onClick="$('#extSelect').toggle()" value=1/> <?php echo $lang['mod/all']; ?><br/>
		<?php
		}
		?>
		<fieldset id="extSelect">
		<?php
		if (($extensions != "%") && ($extensions != "")) { $extensions = explode(",", $extensions); }
		$result = $this->conn->query("SELECT DISTINCT ext FROM extensions ORDER BY ext ASC;");
		while ($row = $result->fetch_assoc())
		{
		$checked = "";
		if (($extensions !== "%") && ($extensions !== ""))
		{
			if (in_array($extensions, $row['ext']))
			{
				$checked = " checked ";
			}
		}
		if (empty($extensions))
		{
			if (($row['ext']=="jpg") || ($row['ext']=="gif") || ($row['ext']=="png"))
			{
				$checked = " checked ";
			}
		}
		echo "<label for='ext'>".$row['ext']."</label>";
		echo "<input type='checkbox' onClick='document.getElementById(\"ext_all\").checked=false;' name='ext[]' value='".$row['ext']."'".$checked."/>";
		}
		?>
		</fieldset>
		<?php
	}

	function parseList($input, $all = 0)
	{
		$out = "";
		if ((!empty($_POST[$all])) && ($_POST[$all]==1))
		{
			$out = "%";
		} else {
			if (!empty($_POST[$input]))
			{
				foreach ($_POST[$input] as $s)
				{
					$s .= $s.",";
				}
			} else {
				$out = "%";
			}
		}
		if ($out != "%") { $out = substr($out, 0, strlen($out) - 1); }
		return $out;
	}

	function startSection($title)
	{
		?>
		<div class="box-outer top-box">
		<div class="box-inner">
		<div class="boxbar"><h2><?php echo $title; ?></h2></div>
		<div class="boxcontent">
		<?php
	}

	function endSection()
	{
		?>
		</div>
		</div>
		</div>
		<?php
	}
}
?>