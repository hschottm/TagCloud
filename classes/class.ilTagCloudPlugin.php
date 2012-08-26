<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
 
/**
* TagCloud repository object plugin
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
*/
class ilTagCloudPlugin extends ilRepositoryObjectPlugin
{
	function getPluginName()
	{
		return "TagCloud";
	}
}
?>
