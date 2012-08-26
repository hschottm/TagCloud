<?php

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

define("CLOUD_POSITION_RIGHT", 0);
define("CLOUD_POSITION_BOTTOM", 1);
define("CLOUD_POSITION_LEFT", 2);
define("CLOUD_POSITION_TOP", 3);

define("CLOUD_FILTER_NONE", 0);
define("CLOUD_FILTER_COURSE", 1);
define("CLOUD_FILTER_GROUP", 2);
define("CLOUD_FILTER_CATEGORY", 4);
define("CLOUD_FILTER_FOLDER", 8);

/**
* Application class for gallery repository object.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
*
* $Id$
*/
class ilObjTagCloud extends ilObjectPlugin
{
	protected $plugin;
	protected $position;
	protected $filtertype;
	protected $max_nr_of_tags;
	protected $nr_of_sizes;
	protected $tag_classname;
	protected $related;
	protected $topten;
	protected $filter_own;
	protected $filter_objects;
	protected $object_selection;

	protected $forTable = "";
	protected $strTagTable = "tl_tag";
	protected $strTagField = "tag";
	protected $arrCloudTags = array();
	protected $arrPages = array();
	protected $arrArticles = array();
	
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "TagCloud");
	}
	
	function valueForProperty($property)
	{
		if (property_exists($this, $property)) return $this->$property;
	 else return NULL;
	}
	
	function setValueForProperty($value, $property)
	{
		if (property_exists($this, $property)) $this->$property = $value;
	}
	

	/**
	* Get type.
	* The initType() method must set the same ID as the plugin ID.
	*/
	final function initType()
	{
		$this->setType("xtc");
	}
	
	/**
	* Create object
	* This method is called, when a new repository object is created.
	* The Object-ID of the new object can be obtained by $this->getId().
	* You can store any properties of your object that you need.
	* It is also possible to use multiple tables.
	* Standard properites like title and description are handled by the parent classes.
	*/
	function doCreate()
	{
		global $ilDB;
		// $myID = $this->getId();
		$this->setValueForProperty(CLOUD_POSITION_RIGHT, 'position');
		$this->setValueForProperty(CLOUD_FILTER_NONE, 'filtertype');
		$this->setValueForProperty(0, 'filter_own');
		$this->setValueForProperty(0, 'max_nr_of_tags');
		$this->setValueForProperty(4, 'nr_of_sizes');
		$this->setValueForProperty(1, 'tag_classname');
		$this->setValueForProperty(0, 'related');
		$this->setValueForProperty(0, 'topten');
		$this->setValueForProperty(0, 'filter_objects');
		$this->doUpdate();
	}
	
	/**
	* Read data from db
	* This method is called when an instance of a repository object is created and an existing Reference-ID is provided to the constructor.
	* All you need to do is to read the properties of your object from the database and to call the corresponding set-methods.
	*/
	function doRead()
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT * FROM rep_robj_xtc_object WHERE xtc_fi = %s",
			array('integer'),
			array($this->getId())
		);
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			foreach ($row as $key => $value)
			{
				$this->setValueForProperty($value, $key);
			}
		}

		$result = $ilDB->queryF("SELECT * FROM rep_robj_xtc_fltobjs WHERE xtc_fi = %s",
			array('integer'),
			array($this->getId())
		);
		if ($result->numRows() > 0)
		{
			$sel = array();
			while ($row = $ilDB->fetchAssoc($result))
			{
				array_push($sel, $row['obj_type']);
			}
			$this->setValueForProperty($sel, 'object_selection');
		}
	}
	
	/**
	* Update data
	* This method is called, when an existing object is updated.
	*/
	function doUpdate()
	{
		global $ilDB;

		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xtc_object WHERE xtc_fi = %s",
			array('integer'),
			array($this->getId())
		);
		$result = $ilDB->manipulateF("INSERT INTO rep_robj_xtc_object (xtc_fi, position, filtertype, filter_objects, filter_own, max_nr_of_tags, nr_of_sizes, tag_classname, related, topten) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
			array('integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'text', 'integer', 'integer'),
			array(
				$this->getId(), 
				$this->valueForProperty('position'),
				0,//$this->valueForProperty('filtertype'),
				$this->valueForProperty('filter_objects'),
				$this->valueForProperty('filter_own'),
				$this->valueForProperty('max_nr_of_tags'),
				$this->valueForProperty('nr_of_sizes'),
				$this->valueForProperty('tag_classname'),
				$this->valueForProperty('related'),
				$this->valueForProperty('topten')
			)
		);
		
		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xtc_fltobjs WHERE xtc_fi = %s",
			array('integer'),
			array($this->getId())
		);
		if ($this->valueForProperty('filter_objects') == 1)
		{
			$sel = $this->valueForProperty('object_selection');
			if (is_array($sel))
			{
				foreach ($sel as $idx => $obj)
				{
					$result = $ilDB->manipulateF("INSERT INTO rep_robj_xtc_fltobjs (xtc_fi, obj_type) VALUES (%s, %s)",
						array('integer', 'text'),
						array(
							$this->getId(), 
							$obj
						)
					);
				}
			}
		}
	}
	
	/**
	* Delete data from db
	* This method is called, when a repository object is finally deleted from the system.
	* It is not called if an object is moved to the trash.
	*/
	function doDelete()
	{
		global $ilDB;
		// $myID = $this->getId();
		
	}
	
	/**
	* Do Cloning
	* This method is called, when a repository object is copied.
	*/
	function doClone($a_target_id,$a_copy_id,$new_obj)
	{
		global $ilDB;

	}
	
	public function getTaggedObjectTypes()
	{
		global $ilDB;
		global $objDefinition;
		global $lng;

		$result = $ilDB->query("SELECT DISTINCT(obj_type) type FROM il_tag");
		$subobj = array();
		if ($result->numRows() > 0)
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				if (!$objDefinition->isPlugin($row['type']))
				{
					$subobj[] = array("value" => $row["type"],
						"title" => $lng->txt("obj_".$row["type"]),
						"img" => ilObject::_getIcon("", "tiny", $row["type"]),
						"alt" => $lng->txt("obj_".$row["type"]));
				}
				else
				{
					include_once("./Services/Component/classes/class.ilPlugin.php");
					$subobj[] = array("value" => $row["type"],
						"title" => ilPlugin::lookupTxt("rep_robj", $row["type"], "obj_".$row["type"]),
						"img" => ilObject::_getIcon("", "tiny", $row["type"]),
						"alt" => $lng->txt("obj_".$row["type"]));
				}
			}
		}
		return $subobj;
	}

	public static function _getConfigurationValue($key)
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$setting = new ilSetting("xtc");
		return $setting->get($key);
	}

	public static function _setConfiguration($key, $value)
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$setting = new ilSetting("xtc");
		$setting->set($key, $value);
	}
	
	/* tag cloud processing */

	public function getRelatedTagList($for_tags)
	{
		global $ilDB;
		
		if (!is_array($for_tags)) return array();

		$ids = array();
		for ($i = 0; $i < count($for_tags); $i++)
		{
			$result = $ilDB->queryF("SELECT DISTINCT obj_id FROM il_tag WHERE tag = %s ORDER BY obj_id ASC",
				array('text'),
				array($for_tags[$i])
			);
			$arr = array();
			if ($result->numRows() > 0)
			{
				while ($row = $ilDB->fetchAssoc($result))
				{
					array_push($arr, $row['obj_id']);
				}
			}
			if ($i == 0)
			{
				$ids = $arr;
			}
			else
			{
				$ids = array_intersect($ids, $arr);
			}
		}

		$arrCloudTags = array();
		if (count($ids))
		{
			$filters = array();
			if ($this->filter_objects == 1 && count($this->object_selection))
			{
				array_push($filters, $ilDB->in('obj_type', $this->object_selection, false, 'text'));
			}
			if ($this->filter_own == 1)
			{
				array_push($filters, 'user_id = ' . $ilDB->quote($ilUser->getId(), 'integer'));
			}
			$inids = $ilDB->in('obj_id', $ids, false, 'integer');
			if (count($filters) > 0)
			{
				$filtertext = 'WHERE ' . implode('AND', $filters) . ' AND ' . $inids;
			}
			else
			{
				$filtertext = 'WHERE ' . $inids;
			}
			$result = $ilDB->query("SELECT tag tag_name, obj_id, obj_type, sub_obj_id, sub_obj_type, user_id, is_offline, COUNT(tag) as tag_count FROM il_tag $filtertext GROUP BY tag ORDER BY tag ASC");
			$tags = array();
			if ($result->numRows() > 0)
			{
				while ($row = $ilDB->fetchAssoc($result))
				{
					if (!in_array($row['tag_name'], $for_tags))
					{
						array_push($tags, $row);
					}
				}
			}
			if (count($tags))
			{
				$arrCloudTags = $this->cloud_tags($tags);
			}
		}
		return $arrCloudTags;
	}

	public function getTagList()
	{
		global $ilDB;
		
		if (count($this->arrCloudTags) == 0)
		{
			$filters = array();
			if ($this->filter_objects == 1 && count($this->object_selection))
			{
				array_push($filters, $ilDB->in('obj_type', $this->object_selection, false, 'text'));
			}
			if ($this->filter_own == 1)
			{
				array_push($filters, 'user_id = ' . $ilDB->quote($ilUser->getId(), 'integer'));
			}
			if (count($filters) > 0)
			{
				$filtertext = 'WHERE ' . implode('AND', $filters);
			}
			else
			{
				$filtertext = '';
			}
			$result = $ilDB->query("SELECT tag tag_name, obj_id, obj_type, sub_obj_id, sub_obj_type, user_id, is_offline, COUNT(tag) as tag_count FROM il_tag GROUP BY tag $filtertext ORDER BY tag ASC");
			$tags = array();
			if ($result->numRows() > 0)
			{
				while ($row = $ilDB->fetchAssoc($result))
				{
					array_push($tags, $row);
				}
			}
			if (count($tags))
			{
				$this->arrCloudTags = $this->cloud_tags($tags);
			}
		}
		return $this->arrCloudTags;
	}
	
	public function getTopTenTagList()
	{
		$list = $this->getTagList();
		usort($list, array($this, "tag_asort"));
		if (count($list) > 10) $list = array_reverse(array_slice($list, -10, 10));
		return $list;
	}

	public function tag_asort($tag1, $tag2)
	{
		if($tag1['tag_count'] == $tag2['tag_count'])
		{
			return 0;
		}
		return ($tag1['tag_count'] < $tag2['tag_count']) ? -1 : 1;
	}

	public function tag_alphasort($tag1, $tag2)
	{
		return strnatcasecmp($tag1['tag_name'], $tag2['tag_name']);
	}

	protected function cloud_tags($tags)
	{
		usort($tags, array($this, "tag_asort"));
		if ($this->max_nr_of_tags > 0)
		{
			if (count($tags) > $this->max_nr_of_tags)
			{
				$tags = array_slice($tags, -$this->max_nr_of_tags, $this->max_nr_of_tags);
			}
		}
		if(count($tags) > 0)
		{
			// Start with the sorted list of tags and divide by the number of font sizes (buckets).
			// Then proceed to put an even number of tags into each bucket.  The only restriction is
			// that tags of the same count can't span 2 buckets, so some buckets may have more tags
			// than others.  Because of this, the sorted list of remaining tags is divided by the
			// remaining 'buckets' to evenly distribute the remainder of the tags and to fill as
			// many 'buckets' as possible up to the largest font size.

			$total_tags = count($tags);
			$min_tags = $total_tags / $this->nr_of_sizes;
			$bucket_count = 1;
			$bucket_items = 0;
			$tags_set = 0;
			foreach($tags as $key => $tag)
			{
				$tag_count = $tag['tag_count'];

				// If we've met the minimum number of tags for this class and the current tag
				// does not equal the last tag, we can proceed to the next class.

				if(($bucket_items >= $min_tags) and $last_count != $tag_count and $bucket_count < $this->nr_of_sizes)
				{
					$bucket_count++;
					$bucket_items = 0;

					// Calculate a new minimum number of tags for the remaining classes.

					$remaining_tags = $total_tags - $tags_set;
					$min_tags = $remaining_tags / $bucket_count;
				}

				// Set the tag to the current class.
				$tags[$key]['tag_class'] = 'size'.$bucket_count . (($this->tag_classname) ? (' ' . $this->getTagNameClass($tag['tag_name'])) : '');
				$bucket_items++;
				$tags_set++;

				$last_count = $tag_count;
			}
			usort($tags, array($this, 'tag_alphasort'));
		}

		return $tags;
	}

	/**
	 * Generate a class name from a tag name
	 * @param string
	 * @return string
	 */
	protected function getTagNameClass($tag)
	{
		return str_replace('"', '', str_replace(' ', '_', $tag));
	}

	public static function _getTagNameClass($tag)
	{
		return str_replace('"', '', str_replace(' ', '_', $tag));
	}
}
?>
