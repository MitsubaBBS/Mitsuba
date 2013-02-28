Example plugin:

<?php
class plugin_TestMenu implements IPlugin {
	public function __construct($conn)
	{
	
	}
	
	public function runHook($hook, $params)
	{
		if ($hook == "menu")
		{
			return '<li><a href="?/test" target="main">Test menu</a></li>';
		}
		if ($hook == "panel")
		{
			if ($params == "/test")
			{
				return "<b>Test</b>";
			}
		}
	}
	
	public function getName()
	{
		return "TestMenu";
	}
	
	public function getUpdateURL()
	{
		return null;
	}
}
?>