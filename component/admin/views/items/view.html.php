<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * The Class For IJoomeradvViewItems which will Extends JViewLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.view
 * @since       1.0
 */
class IjoomeradvViewItems extends JViewLegacy
{
	protected $f_levels;

	protected $items;

	protected $pagination;

	protected $state;

	protected $menus;

	protected $menuOptions;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   12.2
	 */
	public function display($tpl = null)
	{
		$lang             = JFactory::getLanguage();
		$this->items      = $this->get('Items');
		$this->menus      = $this->get('Menus');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->ordering   = array();

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as $item)
		{
			$this->ordering[$item->id][] = $item->id;
		}

		// Menu filter
		$menuOptionsList = array();

		foreach ($this->menus as $menus)
		{
			$menuOptionsList[] = JHtml::_('select.option', $menus->id, JText::_($menus->title));
		}

		$this->menuOptions = $menuOptionsList;

		// Levels filter.
		$options   = array();
		$options[] = JHtml::_('select.option', '1', JText::_('J1'));
		$options[] = JHtml::_('select.option', '2', JText::_('J2'));
		$options[] = JHtml::_('select.option', '3', JText::_('J3'));
		$options[] = JHtml::_('select.option', '4', JText::_('J4'));
		$options[] = JHtml::_('select.option', '5', JText::_('J5'));
		$options[] = JHtml::_('select.option', '6', JText::_('J6'));
		$options[] = JHtml::_('select.option', '7', JText::_('J7'));
		$options[] = JHtml::_('select.option', '8', JText::_('J8'));
		$options[] = JHtml::_('select.option', '9', JText::_('J9'));
		$options[] = JHtml::_('select.option', '10', JText::_('J10'));

		$this->f_levels = $options;

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

		JToolBarHelper::title(JText::_('COM_IJOOMERADV_MENUS'), 'menumanager_48');

		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_SUBMENU_MENUS'), 'index.php?option=com_ijoomeradv&view=menus', JRequest::getVar('view') == 'menus');
		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_SUBMENU_ITEMS'), 'index.php?option=com_ijoomeradv&view=items', JRequest::getVar('view') == 'items');

		JToolBarHelper::custom('home', 'home', '', JText::_('COM_IJOOMERADV_HOME'), false, false);
		JToolBarHelper::divider();
		JToolBarHelper::addNew('add');
		JToolBarHelper::editList('edit');
		JToolBarHelper::divider();
		JToolBarHelper::publish('publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('unpublish', 'JTOOLBAR_UNPUBLISH', true);

		JToolBarHelper::divider();

		if ($this->state->get('filter.published') == -2)
		{
			JToolBarHelper::deleteList('', 'delete', 'JTOOLBAR_EMPTY_TRASH');
		}

		JToolBarHelper::trash('trash');
	}
}
