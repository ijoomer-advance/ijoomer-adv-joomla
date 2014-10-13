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
 * The Class For IJoomeradvViewMenuTypes which will Extends JViewLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.view
 * @since       1.0
 */
class IjoomeradvViewMenutypes extends JViewLegacy
{
	/**
	 * The Display Function
	 *
	 * @param   [type]  $tpl  contains the value of tpl
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->recordId = JRequest::getInt('recordId');
		$this->types = $this->get('TypeOptions');
		$this->menuitems = $this->get('Menuitems');

		parent::display($tpl);
	}
}
