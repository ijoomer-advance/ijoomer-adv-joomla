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

jimport( 'joomla.application.component.controller' );

class ijoomeradvControllerconfig extends JControllerLegacy 
{

	function __construct( $default = array()) 
	{
		parent::__construct( $default );
	}

	function display($cachable = false, $urlparams = false) 
	{
		parent::display();
	}
	
	public function home()
	{
		$this->setRedirect('index.php?option=com_ijoomeradv',null);
	}
	
	function save()
	{
		$model = $this->getModel('config');		
		$config=$model->store();
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv', true));
	}
	
	function cancel()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv', true));
	}
}