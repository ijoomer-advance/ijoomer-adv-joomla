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

jimport('joomla.database.tablenested');

class IjoomeradvTableReport extends JTable{
	var $id 		= null;
	var $message 	= null;
	var $created_by = null;
	var $created	= null;
	var $extension	= null;
	var $status		= null;
	var $params		= null;
	
	function IjoomeradvTableReport(& $db) 
	{
		parent::__construct('#__ijoomeradv_report', 'id', $db);
	}
}

