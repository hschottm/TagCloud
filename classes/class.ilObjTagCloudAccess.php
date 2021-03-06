<?php

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");

/**
* Access/Condition checking for TagCloud object
*
* Please do not create instances of large application classes (like ilObjMediaGallery)
* Write small methods within this class to determin the status.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*/
class ilObjTagCloudAccess extends ilObjectPluginAccess
{

	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do usual RBAC checks.
	*
	* @param	string		$a_cmd			command (not permission!)
 	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	int			$a_user_id		user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $ilAccess;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		switch ($a_permission)
		{
			case "read":
				if (!$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))
				{
				#	return false;
				}
				break;
		}

		return true;
	}
	
	
}

?>
