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
 * The Class For IJoomeradvTableReport which will Extends JTable
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.table
 * @since       1.0
 */
class IjoomeradvTableReport extends JTable
{
	var $id = null;

	var $message = null;

	var $created_by = null;

	var $created = null;

	var $extension = null;

	var $status = null;

	var $params = null;

	/**
	 * The Function For The IJoomeradvTableReport
	 *
	 * @param   &  &$db  &$db
	 *
	 * @return void
	 */
	public function IjoomeradvTableReport(& $db)
	{
		parent::__construct('#__ijoomeradv_report', 'id', $db);
	}
}
