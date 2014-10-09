<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.tables
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');

class TableExtensions extends JTable
{
	var $id = null;
	var $name = null;
	var $classname = null;
	var $option = null;
	var $published = null;

	function TableExtensions(& $db)
	{
		$this->_table_prefix = '#__ijoomeradv_';
		parent::__construct($this->_table_prefix . 'extensions', 'id', $db);
	}

	function bind($array, $ignore = '')
	{
		if (key_exists('params', $array) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}
		return parent::bind($array, $ignore);
	}
}