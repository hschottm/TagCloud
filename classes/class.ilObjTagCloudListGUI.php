<?php


include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
* ListGUI implementation for TagCloud object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*
* PLEASE do not create instances of larger classes here. Use the
* ...Access class to get DB data and keep it small.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
*/
class ilObjTagCloudListGUI extends ilObjectPluginListGUI
{
	
	/**
	* Init type
	*/
	function initType()
	{
		$this->setType("xtc");
	}
	
	/**
	* Get name of gui class handling the commands
	*/
	function getGuiClass()
	{
		return "ilObjTagCloudGUI";
	}
	
	/**
	* Get commands
	*/
	function initCommands()
	{
		return array
		(
			array(
				"permission" => "read",
				"cmd" => "tagcloud",
				"default" => true),
			array(
				"permission" => "write",
				"cmd" => "properties",
				"txt" => $this->txt("edit"),
				"default" => false),
		);
	}

	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
		global $lng, $ilUser;

		$props = array();
		
		$this->plugin->includeClass("class.ilObjTagCloudAccess.php");

		return $props;
	}
}
?>
