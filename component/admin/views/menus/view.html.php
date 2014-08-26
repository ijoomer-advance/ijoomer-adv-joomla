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

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * The HTML Menus Menu Menus View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_ijoomer
 * @version		1.6
 */
class IjoomeradvViewMenus extends JViewLegacy
{
	protected $items;
	protected $modules;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->modules		= $this->get('Modules');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

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

		$document = JFactory::getDocument();
		$document->setTitle( JText::_('COM_IJOOMERADV_MENUS') );
		
		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_SUBMENU_MENUS'),'index.php?option=com_ijoomeradv&view=menus',JRequest::getVar('view') == 'menus');
		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_SUBMENU_ITEMS'),'index.php?option=com_ijoomeradv&view=items',JRequest::getVar('view') == 'items');

		JToolBarHelper::title(   JText::_( 'COM_IJOOMERADV_MENUS' )	, 'menumanager_48' );
		JToolBarHelper::customX('home','home_32','', JText::_('COM_IJOOMERADV_HOME'), false, false);
		JToolBarHelper::divider();
		JToolBarHelper::addNew('add');
		JToolBarHelper::editList('edit');
		JToolBarHelper::divider();
		JToolBarHelper::deleteList('', 'delete');
	}
}
