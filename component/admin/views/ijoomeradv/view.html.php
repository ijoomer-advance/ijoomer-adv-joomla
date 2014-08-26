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

jimport('joomla.application.component.view');

class ijoomeradvViewijoomeradv extends JView {
	function display($tmpl=null){
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_IJOOMERADV_TITLE'));
		
		JToolBarHelper::title(JText::_('COM_IJOOMERADV_HOME_TITLE'), 'ijoomer_48');
		parent::display($tmpl);
	}
}