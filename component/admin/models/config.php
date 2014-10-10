<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.models
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For IJoomeradvModelconfig which will Extends JModelLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.models
 * @since       1.0
 */

class IjoomeradvModelconfig extends JModelLegacy
{
	var $db;

	/**
	 * Function Construct
	 */
	function __construct()
	{
		$this->db = JFactory::getDBO();
		parent::__construct();
	}

	/**
	 * Function
	 *
	 * @param   [type]  $filter  [description]
	 *
	 * @return  [type]           [description]
	 */
	function getConfig($filter = null)
	{
		$where = ($filter) ? "WHERE `group`= '" . $filter . "'" : '';
		$query = "SELECT *
				FROM #__ijoomeradv_config
				{$where}";
		$this->db->setQuery($query);

		return $this->db->loadObjectList('name');
	}

	/**
	 * The Function Store
	 *
	 * @return  boolean it will returns the value in true or false.
	 */
	function store()
	{
		$config = $this->getConfig();
		$post = JRequest::get('post');

		foreach ($config as $key => $value)
		{
			$setvalue = (isset($post[$value->name]) && ($post[$value->name]) || !is_int($post[$value->name])) ? $post[$value->name] : '';

			if ( $value->type === 'select' && $this->checkOptionAvail($post[$value->name], $value->options))
			{
				$query = "UPDATE `#__ijoomeradv_config`
						SET `value` = '{$setvalue}'
						WHERE `name` = '{$value->name}'";
				$this->db->setQuery($query);

				if ( !$this->db->Query())
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

	/**
	 * The Function Check Option Avail
	 *
	 * @param   [type]  $selectvalue  [description]
	 * @param   [type]  $availvalue   [description]
	 *
	 * @return  boolean it will returns the value in true or false.
	 */
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
