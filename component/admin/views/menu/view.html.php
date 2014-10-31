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
 * The Class For IJoomeradvViewMenu which will Extends JViewLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.view
 * @since       1.0
 */
class IjoomeradvViewMenu extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $state;

	protected $views;

	protected $menuitems;

	/**
	 * The Display Function
	 *
	 * @param   [type]  $tpl  $tpl
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->form      = $this->get('Form');
		$this->item      = $this->get('Item');
		$this->state     = $this->get('State');
		$this->menuitems = $this->get('Menuitems');

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.6
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user  = JFactory::getUser();
		$isNew = ($this->item->id == 0);

		JToolBarHelper::title(JText::_($isNew ? 'COM_IJOOMERADV_VIEW_NEW_MENU_TITLE' : 'COM_IJOOMERADV_VIEW_EDIT_MENU_TITLE'), 'menu.png');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		JToolBarHelper::apply('apply');
		JToolBarHelper::save('save');

		JToolBarHelper::save2new('save2new');
		JToolBarHelper::cancel('cancel');
	}
}
