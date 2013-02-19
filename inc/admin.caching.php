<?php
function rebuildBoardLinks($conn)
{
	updateConfig($conn, "boardLinks", generateBoardLinks($conn));
	updateConfig($conn, "boardLinks_thread", generateBoardLinks($conn, 1));
}

function rebuildStyles($conn)
{
	updateConfig($conn, "styles", generateStyles($conn));
	updateConfig($conn, "styles_thread", generateStyles($conn, 1));
}
?>