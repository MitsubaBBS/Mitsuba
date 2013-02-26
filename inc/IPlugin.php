<?php
interface IPlugin {
	public function load($conn);
	public function runHook($hook, $params);
}
?>