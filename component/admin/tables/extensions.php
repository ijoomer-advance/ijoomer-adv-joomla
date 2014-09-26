<?php
 /*--------------------------------------------------------------------------------
# com_ijoomeradv_1.5 - iJoomer Advanced
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die;

jimport('joomla.application.component.model');

class TableExtensions extends JTable{
	var $id = null;
	var $name = null;
	var $classname = null;
	var $option = null;
	var $published = null;

	function TableExtensions(& $db) {
		$this->_table_prefix = '#__ijoomeradv_';
		parent::__construct($this->_table_prefix.'extensions', 'id', $db);
	}

	function bind($array, $ignore = '') {
		if (key_exists( 'params', $array ) && is_array( $array['params'] )) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}
		return parent::bind($array, $ignore);
	}
}