<?php
/**
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.extensions
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class icms_helper
{

	private $db_helper;

	function __construct()
	{
		$this->db_helper =  JFactory::getDBO();
	}

}