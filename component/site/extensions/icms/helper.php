<?php
/**
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.extensions
 *
 * @copyright   Copyright (C) 2010 - 2015 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For The Icms_Helper
 *
 * @since  1.0
 */
class Icms_Helper
{
	private $db_helper;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->db_helper = JFactory::getDBO();
	}
}
