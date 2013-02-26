<?php
include("IPlugin.php");
function loadPlugins($conn)
{
	foreach (glob("./plugins/*.php") as $pluginname)
	{
		include($pluginname);
	}
	foreach (get_declared_classes() as $classname)
	{
		if (substr($classname, 0, 7) == "plugin_")
		{
			$plugin = new $classname();
			if ($plugin instanceof IPlugin)
			{
				$return = $plugin->load($conn);
			}
		}
	}
}

function runHooks($hook, $params)
{
	$return = "";
	foreach (get_declared_classes() as $classname)
	{
		if (substr($classname, 0, 7) == "plugin_")
		{
			$plugin = new $classname();
			if ($plugin instanceof IPlugin)
			{
				$return = $plugin->runHook($hook, $params) . "\n";
			}
		}
	}
	return $return;
}
?>