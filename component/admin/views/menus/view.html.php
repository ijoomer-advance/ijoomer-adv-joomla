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
 * The Class For IJoomeradvViewMenus which will Extends JViewLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.view
 * @since       1.0
 */
class IjoomeradvViewMenus extends JViewLegacy
{
	protected $items;

	protected $modules;

	protected $pagination;

	protected $state;

	/**
	 * The Display Function
	 *
	 * @param   [type]  $tpl  contains the value of tpl
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->modules    = $this->get('Modules');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.0
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/menus.php';

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_IJOOMERADV_MENUS'));

		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_SUBMENU_MENUS'), 'index.php?option=com_ijoomeradv&view=menus', JRequest::getVar('view') == 'menus');
		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_SUBMENU_ITEMS'), 'index.php?option=com_ijoomeradv&view=items', JRequest::getVar('view') == 'items');

		JToolBarHelper::title(JText::_('COM_IJOOMERADV_MENUS'), 'menumanager_48');
		JToolBarHelper::custom('home', 'home', '', JText::_('COM_IJOOMERADV_HOME'), false, false);
		JToolBarHelper::divider();
		JToolBarHelper::addNew('add');
		JToolBarHelper::editList('edit');
		JToolBarHelper::divider();
		JToolBarHelper::deleteList('', 'delete');
	}
}
