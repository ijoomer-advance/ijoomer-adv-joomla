<?php
 /*--------------------------------------------------------------------------------
# com_ijoomeradv_1.5 - iJoomer Advanced
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * The HTML Menus Menu Items View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_ijoomer
 * @version		1.6
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
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$lang 		= JFactory::getLanguage();
		$this->items		= $this->get('Items');
		$this->menus		= $this->get('Menus');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		/*if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}*/

		$this->ordering = array();

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as $item) {
			$this->ordering[$item->id][] = $item->id;
		}

		//menu filter
		$menuOptionsList	= array();
		//$menuOptionsList[] = JHtml::_('select.option', '*', JText::_('COM_IJOOMER_MENU_MENUTYPE'));
		foreach ($this->menus as $menus) {
			$menuOptionsList[] = JHtml::_('select.option', $menus->id, JText::_($menus->title));
		}
		$this->menuOptions = $menuOptionsList;

		// Levels filter.
		$options	= array();
		$options[]	= JHtml::_('select.option', '1', JText::_('J1'));
		$options[]	= JHtml::_('select.option', '2', JText::_('J2'));
		$options[]	= JHtml::_('select.option', '3', JText::_('J3'));
		$options[]	= JHtml::_('select.option', '4', JText::_('J4'));
		$options[]	= JHtml::_('select.option', '5', JText::_('J5'));
		$options[]	= JHtml::_('select.option', '6', JText::_('J6'));
		$options[]	= JHtml::_('select.option', '7', JText::_('J7'));
		$options[]	= JHtml::_('select.option', '8', JText::_('J8'));
		$options[]	= JHtml::_('select.option', '9', JText::_('J9'));
		$options[]	= JHtml::_('select.option', '10', JText::_('J10'));

		$this->f_levels = $options;

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/menus.php';

		JToolBarHelper::title(   JText::_( 'COM_IJOOMERADV_MENUS' )	, 'menumanager_48' );

		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_SUBMENU_MENUS'),'index.php?option=com_ijoomeradv&view=menus',JRequest::getVar('view') == 'menus');
		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_SUBMENU_ITEMS'),'index.php?option=com_ijoomeradv&view=items',JRequest::getVar('view') == 'items');

		JToolBarHelper::custom('home','home','', JText::_('COM_IJOOMERADV_HOME'), false, false);
		JToolBarHelper::divider();
		JToolBarHelper::addNew('add');
		JToolBarHelper::editList('edit');
		JToolBarHelper::divider();
		JToolBarHelper::publish('publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('unpublish', 'JTOOLBAR_UNPUBLISH', true);

		JToolBarHelper::divider();


		if ($this->state->get('filter.published') == -2 ) {
			JToolBarHelper::deleteList('', 'delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		JToolBarHelper::trash('trash');
	}
}
