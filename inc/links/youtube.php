<?php

function parseGetServiceName() {
	return "Youtube";
}

function parseGetTitle($url) {
	parse_str( parse_url( $url, PHP_URL_QUERY ), $var );

	$xmlData = simplexml_load_string(file_get_contents("http://gdata.youtube.com/feeds/api/videos/{$var['v']}?fields=title"));
	$title = (string)$xmlData->title;
	
	return $title;
}

function parseGetSize($url) {
	parse_str( parse_url( $url, PHP_URL_QUERY ), $var );
	
	$data=@file_get_contents("http://gdata.youtube.com/feeds/api/videos/{$var['v']}?v=2&alt=jsonc");
	
	if ( ($data) && ($obj=json_decode($data)) ) {
		return gmdate("H:i:s", $obj->data->duration);
	} else {
		return "00:00:00";
	}
}

?>