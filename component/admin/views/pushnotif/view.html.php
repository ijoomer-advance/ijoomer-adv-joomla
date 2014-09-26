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

jimport( 'joomla.application.component.view' );

class IjoomeradvViewPushnotif extends JViewLegacy {

	function display($tpl = null) {
		global $context;

		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_IJOOMERADV_TITLE'));

		JToolBarHelper::title(JText::_( 'COM_IJOOMERADV_PUSH_NOTIFICATION_TITLE' ), 'pushnotification_48');
		JToolBarHelper::custom('home','home','', JText::_('COM_IJOOMERADV_HOME'), false, false);
		JToolBarHelper::divider();
		JToolBarHelper::apply();

		//Code for add submenu for joomla version 1.6 and 1.7
		JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_EXTENSIONS'), 'index.php?option=com_ijoomeradv&view=extensions', (JRequest::getVar('view') == 'extensions' && JRequest::getVar('layout') != 'manage'));
		JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_EXTENSIONS_MANAGER'), 'index.php?option=com_ijoomeradv&view=extensions&layout=manage', (JRequest::getVar('view') == 'extensions' && JRequest::getVar('layout') == 'manage'));
		JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_GLOBAL_CONFIGURATION'), 'index.php?option=com_ijoomeradv&view=config', JRequest::getVar('view') == 'config' );
		JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_MENUS'), 'index.php?option=com_ijoomeradv&view=menus', JRequest::getVar('view') == 'menus' );
		JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION'), 'index.php?option=com_ijoomeradv&view=pushnotif', JRequest::getVar('view') == 'pushnotif' );
		JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_REPORT'), 'index.php?option=com_ijoomeradv&view=report', JRequest::getVar('view') == 'report' );

		$users=$this->get('Users');
		$this->assignRef('users', $users);

		$pushNotifications=$this->get('PushNotifications');
		$this->assignRef('pushNotifications',$pushNotifications);

		$uri=JFactory::getURI()->toString()	;
		$this->assignRef('request_url',	$uri);

		parent::display($tpl);
	}
}