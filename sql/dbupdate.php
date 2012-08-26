<#1>
<?php
if (!$ilDB->tableExists('rep_robj_xtc_object'))
{
	$fields = array (
	'xtc_fi'    => array(
		'type' => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0),
	'position'   => array(
		'type' => 'integer',
		'length'  => 2,
		'notnull' => true,
		'default' => 1),
	'filtertype'   => array(
		'type' => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0),
	'filter_objects'   => array(
		'type' => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0),
	'filter_own'   => array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0),
	'max_nr_of_tags'   => array(
		'type' => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0),
	'nr_of_sizes'   => array(
		'type' => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 4),
	'tag_classname'   => array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1),
	'related'   => array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0),
	'topten'   => array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0)
	);
	$ilDB->createTable('rep_robj_xtc_object', $fields);
	$ilDB->addIndex("rep_robj_xtc_object", array("xtc_fi"), "i1");
}
?>
<#2>
<?php

include_once './Services/Administration/classes/class.ilSetting.php';
$setting = new ilSetting("xtc");
$setting->set('respect_permissions', '0');

?>
<#3>
<?php
if (!$ilDB->tableExists('rep_robj_xtc_fltobjs'))
{
	$fields = array (
	'xtc_fi'    => array(
		'type' => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0),
	'obj_type'   => array(
		'type' => 'text',
		'notnull' => false,
		'length' => 10,
		'fixed' => false,
		'default' => NULL)
	);
	$ilDB->createTable('rep_robj_xtc_fltobjs', $fields);
	$ilDB->addIndex("rep_robj_xtc_fltobjs", array("xtc_fi"), "i1");
	$ilDB->addIndex("rep_robj_xtc_fltobjs", array("obj_type"), "i2");
}
?>
