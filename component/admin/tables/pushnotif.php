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

defined('_JEXEC') or die;

class TablePushnotif extends JTable{
	var $id				= null;
	var $device_type	= null;
	var $to_user		= null;
	var $to_all			= null;
	var $message		= null;
	var $time			= null;

	function TablePushnotif(& $db) {
		$this->_table_prefix = '#__ijoomeradv_';
		parent::__construct($this->_table_prefix.'push_notification', 'id', $db);
	}
}