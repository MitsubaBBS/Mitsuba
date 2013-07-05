<?php
session_start();

include("libs/cool-php-captcha/simplecaptcha.php");

$captcha = new SimpleCaptcha();



// OPTIONAL Change configuration...
//$captcha->wordsFile = 'words/es.php';
//$captcha->session_var = 'secretword';
//$captcha->imageFormat = 'png';
$captcha->lineWidth = 3;
//$captcha->scale = 3; $captcha->blur = true;
//$captcha->resourcesPath = "/var/cool-php-captcha/resources";

// OPTIONAL Simple autodetect language example
/*
if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	$langs = array('en', 'es');
	$lang  = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	if (in_array($lang, $langs)) {
		$captcha->wordsFile = "words/$lang.php";
	}
}
*/



// Image generation
$captcha->useImageMagick = true;
$captcha->CreateImage();
?>