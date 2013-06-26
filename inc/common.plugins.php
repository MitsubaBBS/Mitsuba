<?php
interface IPlugin {
	public function __construct($conn);
	public function getName();
	public function getUpdateURL();
}
$libraries_array = array();
$plugins_array = array();

function loadPlugins($conn)
{
	global $plugins_array;
	$plugins = array();
	if ($array = glob("./plugins/*.php")) { $plugins = $array; }
	$libs = array();
	if ($array = glob("./libs/*.php")) { $libs = $array; }
	foreach ($libs as $libname)
	{
		include($libname);
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

function triggerEvent($event, $eventData)
{
	global $plugins_array;
	$return = "";
	foreach ($plugins_array as $class)
	{
		if ($class instanceof IPlugin)
		{
			if (method_exists($class, $event))
			{
				$class->$event($eventData);
			}
		}
	}
}
?>