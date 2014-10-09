<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class IjoomeradvViewExtensions extends JViewLegacy
{

	function display($tpl = null)
	{
		global $context;

		$mainframe = JFactory::getApplication();

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_IJOOMERADV_TITLE'));

		$filter_order = $mainframe->getUserStateFromRequest($context . 'filter_order', 'filter_order', 'id');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', '');

		if (JRequest::getVar('layout') == 'install')
		{
			JRequest::setVar('hidemainmenu', 1);
			JToolBarHelper::title(JText::_('COM_IJOOMERADV_EXTENSIONS_INSTALL_TITLE'), 'extensions_48');
			JToolBarHelper::cancel();
		}
		else if (JRequest::getVar('layout') == 'manage')
		{
			JToolBarHelper::title(JText::_('COM_IJOOMERADV_EXTENSIONS_MANAGE_TITLE'), 'extensionmanager_48');
			JToolBarHelper::custom('home', 'home', '', JText::_('COM_IJOOMERADV_HOME'), false, false);
			JToolBarHelper::divider();
			JToolBarHelper::publish();
			JToolBarHelper::unpublish();
			JToolBarHelper::divider();
			JToolBarHelper::deleteList(null, 'uninstall', 'Uninstall');

			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_EXTENSIONS'), 'index.php?option=com_ijoomeradv&view=extensions', (JRequest::getVar('view') == 'extensions' && JRequest::getVar('layout') != 'manage'));
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_EXTENSIONS_MANAGER'), 'index.php?option=com_ijoomeradv&view=extensions&layout=manage', (JRequest::getVar('view') == 'extensions' && JRequest::getVar('layout') == 'manage'));
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_GLOBAL_CONFIGURATION'), 'index.php?option=com_ijoomeradv&view=config', JRequest::getVar('view') == 'config');
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_MENUS'), 'index.php?option=com_ijoomeradv&view=menus', JRequest::getVar('view') == 'menus');
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION'), 'index.php?option=com_ijoomeradv&view=pushnotif', JRequest::getVar('view') == 'pushnotif');
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_REPORT'), 'index.php?option=com_ijoomeradv&view=report', JRequest::getVar('view') == 'report');

			$extensions = $this->get('Data');
			$pagination = $this->get('Pagination');

			$this->assignRef('extensions', $extensions);
			$this->assignRef('pagination', $pagination);
		}
		else if (JRequest::getVar('layout') == 'detail')
		{
			$extension = $this->get('ExtensionData');

			$stylelink = ".icon-48-" . $extension->classname . "_48 {";
			$stylelink .= "background-image: url('components/com_ijoomeradv/assets/images/" . $extension->classname . "_48.png')";
			$stylelink .= "}";

			$document->addStyleDeclaration($stylelink);

			JToolBarHelper::title($extension->name . ' ' . JText::_('COM_IJOOMERADV_EXTENSION') . ': <small><small>[ ' . JText::_('COM_IJOOMERADV_CONFIGURATION') . ' ]</small></small>', $extension->classname . '_48');
			JToolBarHelper::apply();
			JToolBarHelper::save();
			JToolBarHelper::divider();
			JToolBarHelper::cancel('cancel', 'Close');

			require_once JPATH_ADMINISTRATOR . '/components/com_ijoomeradv/helpers/helper.php';
			$ijoomerHelper = new ijoomeradvAdminHelper;
			$orig_comp_avail = $ijoomerHelper->getComponent($extension->option);
			if (!$orig_comp_avail)
			{
				$mainframe->redirect('index.php?option=com_ijoomeradv&view=extensions', JText::sprintf('COM_IJOOMERADV_MAIN_COMPONENT_NOT_FOUND', $extension->option, $extension->option));
			}
			$model = $this->getModel();

			$groups = $this->get('ExtGroups');

			$lang = JFactory::getLanguage();
			$base_dir = JPATH_COMPONENT_SITE . '/' . "extensions" . '/' . $extension->classname;
			$lang->load($extension->classname, $base_dir, null, true);

			foreach ($groups as $key => $value)
			{
				// Get config by group and prepare html
				${$value . 'Config'} = $model->getExtConfig($value);
				$ijoomerHelper->prepareHTML(${$value . 'Config'});

				//assign variable to template
				$this->assignRef($value . 'Config', ${$value . 'Config'});
			}

			$this->assignRef('groups', $groups);
			$this->assignRef('extension', $extension);

			$this->addTemplatePath(JPATH_COMPONENT_SITE . '/' . "extensions" . '/' . $extension->classname . '/' . "tmpl");
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_IJOOMERADV_EXTENSIONS_TITLE'), 'extensions_48');
			JToolBarHelper::custom('home', 'home', '', JText::_('COM_IJOOMERADV_HOME'), false, false);
			JToolBarHelper::divider();
			JToolBarHelper::addNew();
			JToolBarHelper::custom('extensionmanage', 'options', 'options', 'Extension Management', false);

			//Code for add submenu for joomla version 1.6 and 1.7
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_EXTENSIONS'), 'index.php?option=com_ijoomeradv&view=extensions', (JRequest::getVar('view') == 'extensions' && JRequest::getVar('layout') != 'manage'));
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_EXTENSIONS_MANAGER'), 'index.php?option=com_ijoomeradv&view=extensions&layout=manage', (JRequest::getVar('view') == 'extensions' && JRequest::getVar('layout') == 'manage'));
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_GLOBAL_CONFIGURATION'), 'index.php?option=com_ijoomeradv&view=config', JRequest::getVar('view') == 'config');
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_MENUS'), 'index.php?option=com_ijoomeradv&view=menus', JRequest::getVar('view') == 'menus');
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION'), 'index.php?option=com_ijoomeradv&view=pushnotif', JRequest::getVar('view') == 'pushnotif');
			JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_REPORT'), 'index.php?option=com_ijoomeradv&view=report', JRequest::getVar('view') == 'report');

			$lists = array();
			$extensions = $this->get('Data');
			$pagination = $this->get('Pagination');

			$this->assignRef('extensions', $extensions);
			$this->assignRef('pagination', $pagination);
		}

		$lists = array();
		$lists['order'] = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;
		$this->assignRef('lists', $lists);

		$uri = JFactory::getURI()->toString();
		$this->assignRef('request_url', $uri);

		parent::display($tpl);
	}
}