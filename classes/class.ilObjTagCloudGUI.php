<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
* User Interface class for gallery repository object.
*
* User interface classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjTagCloudGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjTagCloudGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
*
*/
class ilObjTagCloudGUI extends ilObjectPluginGUI
{
	protected $plugin;
	protected $sortkey;
	
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
		// - gallery: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "TagCloud");
	}

	/**
	* Get type.
	*/
	final function getType()
	{
		return "xtc";
	}

	/**
	* Handles all commmands of this class, centralizes permission checks
  */
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "editProperties":		// list all commands that need write permission here
			case "updateProperties":
				$this->checkPermission("write");
				$this->$cmd();
				break;
			case "tagcloud":			// list all commands that need read permission here
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	* Get standard command
  */
	function getStandardCmd()
	{
		return "tagcloud";
	}


	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess, $ilUser, $lng, $ilCtrl, $tpl, $ilTabs;

		$ilTabs->setTabActive("info_short");

		$this->checkPermission("visible");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->addSection($this->txt("plugininfo"));
		$info->addProperty('Name', 'Tag Cloud');
		$info->addProperty('Version', xmg_version);
		$info->addProperty('Developer', 'Helmut Schottmüller');
		$info->addProperty('Kontakt', 'ilias@aurealis.de');
		$info->addProperty('&nbsp;', 'Aurealis');
		$info->addProperty('&nbsp;', '');
		$info->addProperty('&nbsp;', "http://www.aurealis.de");



		$info->enablePrivateNotes();

		// general information
		$lng->loadLanguageModule("meta");

		$this->addInfoItems($info);


		// forward the command
		$ret = $ilCtrl->forwardCommand($info);


		//$tpl->setContent($ret);
	}
	//
	// DISPLAY TABS
	//

	protected function setSubTabs($cmd)
	{
		/*
		global $ilTabs;
	
		switch ($cmd)
		{
			case "mediafiles":
				$ilTabs->addSubTabTarget("list",
					$this->ctrl->getLinkTarget($this, "mediafiles"),
					array("mediafiles"),
					"", "");
			case 'upload':
				$ilTabs->addSubTabTarget("upload",
					$this->ctrl->getLinkTarget($this, "upload"),
					array("upload"),
					"", "");
				break;
		}
		*/
	}

	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;

		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("tagcloud", $this->txt("tagcloud"), $ilCtrl->getLinkTarget($this, "tagcloud"));
		}

/*
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("archives", $this->txt("archives"), $ilCtrl->getLinkTarget($this, "archives"));
		}
*/
		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
		}

		// standard epermission tab
		$this->addPermissionTab();
	}


	// THE FOLLOWING METHODS IMPLEMENT SOME EXAMPLE COMMANDS WITH COMMON FEATURES
	// YOU MAY REMOVE THEM COMPLETELY AND REPLACE THEM WITH YOUR OWN METHODS.

	//
	// Edit properties form
	//

	/**
	* Edit Properties. This commands uses the form class to display an input form.
	*/
	function editProperties()
	{
		global $tpl, $ilTabs;

		$ilTabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPropertiesForm()
	{
		global $ilCtrl;
		global $lng;

		$this->tpl->addCss($this->plugin->getStyleSheetLocation("xtc.css"));
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// title
		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
		$this->form->addItem($ta);

		$position = new ilRadioGroupInputGUI($this->txt('position'), 'position');
		$positions = array('right','bottom','left','top');
		foreach ($positions as $idx => $pos)
		{
			$option = new ilRadioOption($this->txt('position_'.$pos), $idx);
			$option->setValue($idx);
			$position->addOption($option);
		}
		$this->form->addItem($position);

		$numbers_and_sizes = new ilFormSectionHeaderGUI();
		$numbers_and_sizes->setTitle($this->txt("numbers_and_sizes"));
		$this->form->addItem($numbers_and_sizes);

		$max_nr_of_tags = new ilNumberInputGUI($this->txt("max_nr_of_tags"), "max_nr_of_tags");
		$max_nr_of_tags->setRequired(true);
		$max_nr_of_tags->setMinValue(0);
		$max_nr_of_tags->setMaxLength(2);
		$max_nr_of_tags->setSize(2);
		$this->form->addItem($max_nr_of_tags);

		$nr_of_sizes = new ilNumberInputGUI($this->txt("nr_of_sizes"), "nr_of_sizes");
		$nr_of_sizes->setRequired(true);
		$nr_of_sizes->setMinValue(3);
		$nr_of_sizes->setMaxValue(10);
		$nr_of_sizes->setMaxLength(2);
		$nr_of_sizes->setSize(2);
		$this->form->addItem($nr_of_sizes);

		$tag_classname = new ilCheckboxInputGUI($this->txt('tag_classname'), 'tag_classname');
		$tag_classname->setInfo($this->txt("tag_classname_desc"));
		$this->form->addItem($tag_classname);

		$additional_tag_lists = new ilFormSectionHeaderGUI();
		$additional_tag_lists->setTitle($this->txt("additional_tag_lists"));
		$this->form->addItem($additional_tag_lists);

		$related = new ilCheckboxInputGUI($this->txt('related'), 'related');
		$related->setInfo($this->txt("related_desc"));
		$this->form->addItem($related);

		$topten = new ilCheckboxInputGUI($this->txt('topten'), 'topten');
		$topten->setInfo($this->txt("topten_desc"));
		$this->form->addItem($topten);
		
		$filter_settings = new ilFormSectionHeaderGUI();
		$filter_settings->setTitle($this->txt("filter_settings"));
		$this->form->addItem($filter_settings);

		$filter_own = new ilCheckboxInputGUI($this->txt('filter_own'), 'filter_own');
		$filter_own->setInfo($this->txt("filter_own_desc"));
		$this->form->addItem($filter_own);
		
		$types = $this->object->getTaggedObjectTypes();
		$filter_objects = new ilRadioGroupInputGUI($this->txt('filter_objects'), 'filter_objects');
		$option = new ilRadioOption($this->txt('all_objects'), '0');
		$option->setValue(0);
		$filter_objects->addOption($option);
		$option = new ilRadioOption($this->txt('some_objects'), '1');
		$option->setValue(1);
		$filter_objects->addOption($option);
		$object_selection = new ilCheckboxGroupInputGUI('', 'object_selection');
		foreach ($types as $idx => $type)
		{
			$option = new ilCheckboxOption($type['title'], $type['value']);
			$option->setValue($type['value']);
			$object_selection->addOption($option);
		}
		$object_selection->setInfo($this->txt("filter_objects_desc"));
		$filter_objects->addSubItem($object_selection);
		$this->form->addItem($filter_objects);

		$this->form->addCommandButton("updateProperties", $this->txt("save"));

		$this->form->setTitle($this->txt("edit_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	* Get values for edit properties form
	*/
	function getPropertiesValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$properties = array('position', 'filtertype', 'filter_own', 'max_nr_of_tags', 'nr_of_sizes', 'tag_classname', 'related', 'topten');
		foreach ($properties as $property)
		{
			$values[$property] = $this->object->valueForProperty($property);
		}
		$this->form->setValuesByArray($values);
	}

	/**
	* Update properties
	*/
	public function updateProperties()
	{
		global $tpl, $lng, $ilCtrl;

		$this->initPropertiesForm();
		if ($this->form->checkInput())
		{
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$properties = array('position', 'filtertype', 'filter_own', 'max_nr_of_tags', 'nr_of_sizes', 'tag_classname', 'related', 'topten', 'object_selection');
			foreach ($properties as $property)
			{
				$this->object->setValueForProperty($this->form->getInput($property), $property);
			}
			$this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	public function tagcloud()
	{
		global $ilTabs;
		global $ilCtrl;
		
		$ilTabs->activateTab("tagcloud");
		$this->tpl->addCss($this->plugin->getStyleSheetLocation("tagcloud.css"));
		$this->tpl->addCss($this->plugin->getStyleSheetLocation("gridpc.css"));
		$templatecloud = $this->plugin->getTemplate("tpl.cloud.html");
		$tags = $this->object->getTagList();
		if ($this->object->valueForProperty('position') == 0 || $this->object->valueForProperty('position') == 2)
		{
			$templatecloud->setVariable("CLOUD_CLASS", " g3");
		}
		foreach ($tags as $tag)
		{
			$templatecloud->setCurrentBlock('tag');
			$templatecloud->setVariable("TAG_CLASS", $tag['tag_class']);
			$templatecloud->setVariable("TAG_NAME", ilUtil::prepareFormOutput($tag['tag_name']));
			$templatecloud->setVariable("TAG_COUNT", $tag['tag_count']);
			$ilCtrl->setParameter($this, "tag", $tag['tag_name']);
			$templatecloud->setVariable("TAG_URL", $ilCtrl->getLinkTarget($this, 'tagcloud'));
			$ilCtrl->clearParameters($this);
			$templatecloud->parseCurrentBlock();
		}

		$str_related = '';
		if (strlen($_GET['tag']) && $this->object->valueForProperty('related'))
		{
			$relatedlist = (strlen($_GET['related'])) ? preg_split("/,/", $_GET['related']) : array();
			$arrRelated = $this->object->getRelatedTagList(array_merge(array($_GET['tag']), $relatedlist));
			$templaterelated = $this->plugin->getTemplate("tpl.related.html");
			foreach ($arrRelated as $tag)
			{
				$templaterelated->setCurrentBlock('related');
				$templaterelated->setVariable("TAG_NAME", ilUtil::prepareFormOutput($tag['tag_name']));
				$templaterelated->setVariable("TAG_COUNT", $tag['tag_count']);
				$param = (strlen($_GET['related'])) ? $_GET['related'] . ',' . $tag['tag_name'] : $tag['tag_name'];
				$ilCtrl->setParameter($this, "related", $param);
				$ilCtrl->setParameter($this, "tag", $_GET['tag']);
				$templaterelated->setVariable("TAG_URL", $ilCtrl->getLinkTarget($this, 'tagcloud'));
				$ilCtrl->clearParameters($this);
				$templaterelated->parseCurrentBlock();
			}
			$templaterelated->setVariable("TEXT_RELATED", $this->txt('related'));
			$templatecloud->setVariable("RELATED_LIST", $templaterelated->get());
		}
		$templatelist = $this->plugin->getTemplate("tpl.contentlist.html");
		if ($this->object->valueForProperty('position') == 0 || $this->object->valueForProperty('position') == 2)
		{
			$templatelist->setVariable("LIST_CLASS", " g7");
		}

		$lr = (strlen($_GET['related'])) ? preg_split("/,/", $_GET['related']) : array();
		$at = array_merge(array($_GET['tag']), $lr);

		$templatelist->setVariable("CONTENT_HEADING", ilUtil::prepareFormOutput(implode(' + ', $at)));
		$templatelist->setVariable("CONTENT_LIST", "Sed posuere consectetur est at lobortis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nulla vitae elit libero, a pharetra augue. Donec ullamcorper nulla non metus auctor fringilla. Praesent commodo cursus magna, vel scelerisque nisl consectetur et.");
		
		$template = $this->plugin->getTemplate("tpl.tagcloud.html");
		if ($this->object->valueForProperty('position') == 0 || $this->object->valueForProperty('position') == 1)
		{
			$template->setVariable("BLOCK_A", $templatelist->get());
			$template->setVariable("BLOCK_B", $templatecloud->get());
		}
		else
		{
			$template->setVariable("BLOCK_A", $templatecloud->get());
			$template->setVariable("BLOCK_B", $templatelist->get());
		}
		$this->tpl->setVariable("ADM_CONTENT", $template->get());
	}
}
?>