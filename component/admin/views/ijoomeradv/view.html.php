<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class ijoomeradvViewijoomeradv extends JViewLegacy
{
	function display($tmpl = null)
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_IJOOMERADV_TITLE'));

		JToolBarHelper::title(JText::_('COM_IJOOMERADV_HOME_TITLE'), 'ijoomer_48');
		parent::display($tmpl);
	}
}