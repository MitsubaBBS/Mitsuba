Example plugin:

<?php
class plugin_TestMenu implements IPlugin {
	public function __construct($conn)
	{
	
	}
	
	public function eventname(&$eventData)
	{
		
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