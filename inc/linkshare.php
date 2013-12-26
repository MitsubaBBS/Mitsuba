<?php
namespace Mitsuba;

class Linkshare {
	private $conn;
	private $mitsuba;

	function __construct($connection, &$mitsuba) {
		$this->conn = $connection;
		$this->mitsuba = $mitsuba;
	}
	
	function prepareUrl($url) {
		$retn = $url;
	
		if (strncmp($url, "www", 3) == 0) {
			$retn = "http://".$url;
		}
		
		if (strncmp($url, "https://", 8) == 0) {
			$retn = str_ireplace("https://", "http://", $url);
		}
	
		if (!filter_var($retn,FILTER_VALIDATE_URL)) {
			$retn = false;
		}
		
		return $retn;
	}
	
	function checkUrl($url) {
		$parserFile = false;
	
		if (strncmp($url, 'http', 4) != 0) {
			return false;
		}
		
		$result = $this->conn->query("SELECT * FROM `link`");
		
		while ($row = $result->fetch_row()) {
		
			if(preg_match($row[1], $url)) {
				$parserFile = $row[2];
				break;
			}
		}
		
		return $parserFile;
	}
	
	function getStatus($url) {
	
		if (strncmp($url, 'http', 4) != 0) {
			return false;
		}

		$test = get_headers($url);
		$test = strstr($test[0],"200"); //HTTP OK
		
		return ($test == TRUE);	
	}
	
	function openLinkParser($parser) {
	
		if ( file_exists("inc/links/".$parser) ) {
		//parser isn't working at this time
			include("links/".$parser);
			//echo('dbg: exist');
			return true;
		} else {
			return false;
		}
	}
	
	function getTitle($url, $parserOpened)  {
		$title = "";
		
		if ($parserOpened) {
			$title = parseGetTitle($url);
		}
		
		if (!$title) {
		
			$page = file_get_contents($url);
				
			if(strlen($page) > 0){
				preg_match("/\<title\>(.*)\<\/title\>/",$page,$pt);
				$title = $pt[1];
			} else {
				$title = "None";
			}	
		}
		
		return $title;
	}
	
	function getSize($url, $parserOpened) {
	
		if ($parserOpened) {
			return parseGetSize($url);
		} else {
			return false;
		}
	}
	
	function getServiceName($url, $parserOpened) {
	
		if ($parserOpened) {
			return parseGetServiceName();
		} else {
			$parse = parse_url($url);
			return $parse['host'];
		}
		
	}
	
}

?>