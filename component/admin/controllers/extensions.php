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

class ijoomeradvControllerExtensions extends JControllerLegacy 
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
	
	function add() 
	{
		JRequest::setVar ( 'layout', 'install' );
		parent::display ();
	}
	
	function detail() 
	{
		JRequest::setVar ( 'layout', 'detail' );
		JRequest::setVar ( 'hidemainmenu', 1 );
		parent::display ();
	}
	
	function save()
	{
		$post = JRequest::get ( 'post' );
		$task = JRequest::getVar ( 'task');
		$model = $this->getModel ( 'extensions' );
			 	
		if ($model->setExtConfig($post)) {
			$msg = JText::_('COM_IJOOMERADV_CONFIG_SAVED');
		} else {
			$msg = JText::_('COM_IJOOMERADV_ERROR_SAVING_CONFIG');
		}
		
		$this->setRedirect ('index.php?option=com_ijoomeradv&view=extensions', $msg);
	}
	
	function apply()
	{
		$post = JRequest::get ( 'post' );
		$task = JRequest::getVar ( 'task');
		$model = $this->getModel ( 'extensions' );
			 	
		if ($model->setExtConfig($post)) {
			$msg = JText::_('COM_IJOOMERADV_CONFIG_SAVED');
		} else {
			$msg = JText::_('COM_IJOOMERADV_ERROR_SAVING_CONFIG');
		}
		
		$this->setRedirect ('index.php?option=com_ijoomeradv&view=extensions&task=detail&cid[]='.$post['extid'], $msg);
	}
	
	function install() 
	{
		$model = $this->getModel ( 'extensions' );
		$model->install ();
		
		JRequest::setVar ( 'view', 'extensions' );
		JRequest::setVar ( 'layout', 'default' );
		JRequest::setVar ( 'hidemainmenu', 0 );
		parent::display ();
	}
	
	function uninstall()
	{
		
	}
	
	function extensionmanage()
	{
		JRequest::setVar ( 'layout', 'manage' );
		parent::display ();
	}
	
	function publish()
	{
		$cid = JRequest::getVar ( 'cid', array (0 ), 'post', 'array' );
		
		if (! is_array ( $cid ) || count ( $cid ) < 1 || $cid[0]===0) {
			JError::raiseError ( 500, JText::_ ( 'COM_IJOOMERADV_SELECT_EXTENSION_TO_PUBLISH' ) );
		}
		
		$model = $this->getModel ( 'extensions' );
		if (! $model->publish ( $cid, 1 )) {
			echo "<script>alert('" . $model->getError ( true ) . "');</script>\n";
		}
		
		JRequest::setVar ( 'layout', 'manage' );
		parent::display ();
	}
	
	function unpublish()
	{
		$cid = JRequest::getVar ( 'cid', array (0), 'post', 'array' );
		
		if (! is_array ( $cid ) || count ( $cid ) < 1 || $cid[0]===0) {
			JError::raiseError ( 500, JText::_ ( 'COM_IJOOMERADV_SELECT_EXTENSION_TO_UNPUBLISH' ) );
		}
		$model = $this->getModel ( 'extensions' );
		if (! $model->publish ( $cid, 0 )) {
			echo "<script>alert('" . $model->getError ( true ) . "');</script>\n";
		}
		
		JRequest::setVar ( 'layout', 'manage' );
		parent::display ();
	}
}