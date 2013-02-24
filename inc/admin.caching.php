<?php
function rebuildBoardLinks($conn)
{
	updateConfig($conn, "boardLinks", generateBoardLinks($conn));
	updateConfig($conn, "boardLinks_thread", generateBoardLinks($conn, 1));
	updateConfig($conn, "boardLinks_index", generateBoardLinks($conn, 2));
}

function rebuildStyles($conn)
{
	updateConfig($conn, "styles", generateStyles($conn));
	updateConfig($conn, "styles_thread", generateStyles($conn, 1));
}
?>