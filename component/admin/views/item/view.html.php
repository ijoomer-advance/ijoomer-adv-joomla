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

/**
 * The HTML Menus Menu Item View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_ijoomer
 * @since		1.6
 */
class IjoomeradvViewItem extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $modules;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->modules	= $this->get('Modules');
		$this->state	= $this->get('State');
		$this->menutypes	= $this->get('Menutypes');

		$extention = explode('.',$this->form->getValue('views'));
		$extention = $extention[0];

		$lang =& JFactory::getLanguage();
		$base_dir = JPATH_COMPONENT_SITE.DS."extensions".DS.$extention;
		$lang->load($extention, $base_dir,null,true);

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

		JToolBarHelper::title(JText::_($isNew ? 'COM_IJOOMERADV_VIEW_NEW_ITEM_TITLE' : 'COM_IJOOMERADV_VIEW_EDIT_ITEM_TITLE'), 'menu-add');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		JToolBarHelper::apply('apply');
		JToolBarHelper::save('save');
		JToolBarHelper::save2new('save2new');

		JToolBarHelper::cancel('cancel');
	}
}
