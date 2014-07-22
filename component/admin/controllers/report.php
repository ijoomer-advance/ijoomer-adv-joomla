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

jimport ( 'joomla.application.component.controller' );

class ijoomeradvControllerReport extends JControllerLegacy 
{
	function __construct($default = array()) 
	{
		parent::__construct ( $default );
	}
	
	function display($cachable = false, $urlparams = false) 
	{
		parent::display ();
	}
	
	public function home()
	{
		$this->setRedirect('index.php?option=com_ijoomeradv',null);
	}
	
	function delete()
	{
		$model = $this->getModel('report');
		if(!$model->delete()) {
	        $msg = JText::_( 'COM_IJOOMERADV_REPORT_DELETE_ERROR' );
	    } else {
	        $msg = JText::_('COM_IJOOMERADV_REPORT_DELETE');
	    }
		$this->setRedirect ( 'index.php?option=com_ijoomeradv&view=report', $msg );
	}
	
	function action()
	{
		$action = JRequest::getVar('action','');
		$model = $this->getModel('report');
		if($model->{$action}()){
			$msg = ($action=='deletereport')?JText::_('COM_IJOOMERADV_REPORT_ACTION_DELETE'):JText::_('COM_IJOOMERADV_REPORT_ACTION_IGNORE');
		}else{
			$msg = ($action=='deletereport')?JText::_('COM_IJOOMERADV_REPORT_ACTION_DELETE_ERROR'):JText::_('COM_IJOOMERADV_REPORT_ACTION_IGNORE_ERROR');
		}
		$this->setRedirect ( 'index.php?option=com_ijoomeradv&view=report', $msg );
	}
}