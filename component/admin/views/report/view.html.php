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

defined('_JEXEC') or die;

jimport( 'joomla.application.component.view' );

class ijoomeradvViewReport extends JViewLegacy
{
	function display($tpl = null)
	{
		global $context;

		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_IJOOMERADV_TITLE'));

		JToolBarHelper::title(JText::_( 'COM_IJOOMERADV_REPORT_TITLE' ), 'report_48');

		if(JRequest::getVar('layout')!='detail'){
			JToolBarHelper::custom('home','home','', JText::_('COM_IJOOMERADV_HOME'), false, false);
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('', 'delete');
		}else{
			JToolBarHelper::back();
		}

		//Code for add submenu for joomla version 1.6 and 1.7
		if(IJ_JOOMLA_VERSION > 1.5){
			JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_EXTENSIONS'), 'index.php?option=com_ijoomeradv&view=extensions', (JRequest::getVar('view') == 'extensions' && JRequest::getVar('layout') != 'manage'));
			JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_EXTENSIONS_MANAGER'), 'index.php?option=com_ijoomeradv&view=extensions&layout=manage', (JRequest::getVar('view') == 'extensions' && JRequest::getVar('layout') == 'manage'));
			JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_GLOBAL_CONFIGURATION'), 'index.php?option=com_ijoomeradv&view=config', JRequest::getVar('view') == 'config' );
			JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_MENUS'), 'index.php?option=com_ijoomeradv&view=menus', JRequest::getVar('view') == 'menus' );
			JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION'), 'index.php?option=com_ijoomeradv&view=pushnotif', JRequest::getVar('view') == 'pushnotif' );
			JSubMenuHelper::addEntry( JText::_('COM_IJOOMERADV_REPORT'), 'index.php?option=com_ijoomeradv&view=report', JRequest::getVar('view') == 'report' );
		}

		$this->items		= $this->get('Items');
		$this->extension	= $this->get('extensions');
		$this->state		= $this->get('State');
		$uri=JFactory::getURI()->toString()	;
		$this->request_url	= $uri;

		//set default list all in extension list
		$defaultext = new stdClass();
		$defaultext->name=JText::_('COM_IJOOMERADV_SELECT_EXTENSION');
		$defaultext->classname='default';
        array_unshift($this->extension,$defaultext);

		parent::display($tpl);
	}
}