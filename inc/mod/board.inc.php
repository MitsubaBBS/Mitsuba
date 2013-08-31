<?php
if (!defined("IN_MOD"))
{
	die("Nah, I won't serve that file to you.");
}
if ((!empty($_GET['b'])) && ($mitsuba->common->isBoard($_GET['b'])))
		{
			$mitsuba->admin->canBoard($_GET['b']);
			$board = $mitsuba->common->getBoardData($_GET['b']);
			$mode = "page";
			$page = 0;
			if ((!empty($_GET['p'])) && (is_numeric($_GET['p'])) && ($_GET['p'] >= 0) && ($_GET['p'] <= 15))
			{
				$page = $_GET['p'];
				echo $mitsuba->caching->generateView($_GET['b'], $page, 1, 0, $_SESSION['group']);
			} elseif ((!empty($_GET['t'])) && (is_numeric($_GET['t'])))
			{
				$mode = "thread";
				$page = $_GET['t'];
				echo $mitsuba->caching->generateView($_GET['b'], $page, 1, 1, $_SESSION['group']);
			} else {
			
				echo $mitsuba->caching->generateView($_GET['b'], 0, 1, 0, $_SESSION['group']);
			}
			
			
		}
?>