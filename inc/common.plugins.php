<?php
interface ILibrary {
	public function __construct($conn);
	public function getName();
	public function getUpdateURL();
}

interface IPlugin {
	public function __construct($conn);
	public function runHook($hook, $params);
	public function getName();
	public function getUpdateURL();
}
$libraries_array = array();
$plugins_array = array();

function loadPlugins($conn)
{
	global $plugins_array;
	global $libraries_array;
	$libs = array();
	$plugins = array();
	if ($array = glob("./libs/*.php")) { $libs = $array; }
	if ($array = glob("./plugins/*.php")) { $plugins = $array; }
	foreach ($libs as $libname)
	{
		include($libname);
	}
	foreach (get_declared_classes() as $classname)
	{
		if (substr($classname, 0, 4) == "lib_") {
			try {
				$lib = new $classname();
				if ($lib instanceof ILibrary)
				{
					$libraries_array[] = $lib;
				}
			} catch (Exception $e)
			{
				//we do nothing because we can't
			}
			
		}
	}
	
	foreach ($plugins as $pluginname)
	{
		include($pluginname);
	}
	foreach (get_declared_classes() as $classname)
	{
		if (substr($classname, 0, 7) == "plugin_")
		{
			try {
				$plugin = new $classname($conn);
				if ($plugin instanceof IPlugin)
				{
					$plugins_array[] = $plugin;
				} 
			} catch (Exception $e)
			{
				//we do nothing because we can't
			}
		}
	}
}

function runHooks($hook, $params)
{
	global $plugins_array;
	$return = "";
	foreach ($plugins_array as $class)
	{
		if ($class instanceof IPlugin)
		{
			$return = $class->runHook($hook, $params) . "\n";
		}
	}
	return $return;
}
?>