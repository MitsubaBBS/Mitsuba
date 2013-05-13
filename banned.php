<?php
if (!file_exists("./config.php"))
{
header("Location: ./install.php");
}

include("config.php");
include("inc/common.php");
$conn = new mysqli($db_host, $db_username, $db_password, $db_database);
banMessage($conn, "*");
warningMessage($conn);
?>
<h1>NOT BANNED</h1>