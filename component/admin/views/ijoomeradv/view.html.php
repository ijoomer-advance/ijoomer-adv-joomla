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
 * The Class For IJoomeradvViewijoomeradv which will Extends JViewLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.view
 * @since       1.0
 */
class IjoomeradvViewijoomeradv extends JViewLegacy
{
	/**
	 * The Function For Display
	 *
	 * @param   [type]  $tmpl  $tmpl
	 *
	 * @return  void
	 */
	public function display($tmpl = null)
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_IJOOMERADV_TITLE'));

		JToolBarHelper::title(JText::_('COM_IJOOMERADV_HOME_TITLE'), 'ijoomer_48');
		parent::display($tmpl);
	}
}
