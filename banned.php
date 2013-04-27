<?php
if (!file_exists("./config.php"))
{
header("Location: ./install.php");
}

include("config.php");
include("inc/common.php");
banMessage($conn, $board);
?>
<h1>NOT BANNED</h1>