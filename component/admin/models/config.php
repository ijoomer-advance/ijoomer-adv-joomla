<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.models
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class ijoomeradvModelconfig extends JModelLegacy
{

	var $db;

	function __construct()
	{
		$this->db = JFactory::getDBO();
		parent::__construct();
	}

	function getConfig($filter = null)
	{
		$where = ($filter) ? "WHERE `group`= '" . $filter . "'" : '';
		$query = "SELECT *
				FROM #__ijoomeradv_config
				{$where}";
		$this->db->setQuery($query);
		return $this->db->loadObjectList('name');
	}

	function store()
	{
		$config = $this->getConfig();
		$post = JRequest::get('post');

		foreach ($config as $key => $value)
		{
			$setvalue = (isset($post[$value->name]) && ($post[$value->name]) || !is_int($post[$value->name])) ? $post[$value->name] : '';
			if ($value->type === 'select' && $this->checkOptionAvail($post[$value->name], $value->options))
			{
				$query = "UPDATE `#__ijoomeradv_config`
						SET `value` = '{$setvalue}'
						WHERE `name` = '{$value->name}'";
				$this->db->setQuery($query);
				if (!$this->db->Query())
				{
					return false;
				}
			}
			else if ($value->type != 'button')
			{
				$query = "UPDATE `#__ijoomeradv_config`
						SET `value` = '{$setvalue}'
						WHERE `name` = '{$value->name}'";
				$this->db->setQuery($query);
				if (!$this->db->Query())
				{
					return false;
				}
			}
		}
		return true;
	}

	function checkOptionAvail($selectvalue, $availvalue)
	{
		$availvalue = explode(';;', $availvalue);
		foreach ($availvalue as $value)
		{
			$availoption = explode('::', $value);
			if ($availoption[0] === $selectvalue)
			{
				return true;
			}
		}
		return false;
	}
}