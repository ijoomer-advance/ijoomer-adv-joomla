<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Item View.
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.view
 * @since       1.6
 */
class IjoomeradvViewMenutypes extends JViewLegacy
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->recordId = JRequest::getInt('recordId');
		$this->types = $this->get('TypeOptions');
		$this->menuitems = $this->get('Menuitems');

		parent::display($tpl);
	}
}
