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
 * The HTML Menus Menu Item View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_ijoomer
 * @since		1.6
 */
class IjoomeradvViewMenu extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;
	protected $views;
	protected $menuitems;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->form	= $this->get('Form');
		$this->item	= $this->get('Item');
		$this->state		= $this->get('State');
		$this->menuitems	= $this->get('Menuitems');

		// Check for errors.
		/*if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}*/

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
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);

		JToolBarHelper::title(JText::_($isNew ? 'COM_IJOOMERADV_VIEW_NEW_MENU_TITLE' : 'COM_IJOOMERADV_VIEW_EDIT_MENU_TITLE'), 'menu.png');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		JToolBarHelper::apply('apply');
		JToolBarHelper::save('save');

		JToolBarHelper::save2new('save2new');
		JToolBarHelper::cancel('cancel');
	}
}
