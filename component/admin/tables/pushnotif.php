<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.tables
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For TablePushnotif which will Extends JTable
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.table
 * @since       1.0
 */

class TablePushnotif extends JTable
{
	var $id          = null;

	var $device_type = null;

	var $to_user     = null;

	var $to_all      = null;

	var $message     = null;

	var $time        = null;

	/**
	 * The Function For The TablePush Not If
	 *
	 * @param   &  &$db  &$db
	 *
	 * @return void
	 */
	public function TablePushnotif(& $db)
	{
		$this->_table_prefix = '#__ijoomeradv_';
		parent::__construct($this->_table_prefix . 'push_notification', 'id', $db);
	}
}
