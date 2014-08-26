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

function com_uninstall(){
	$db = JFactory::getDBO();
	
	//Delete plugin config	
	$query="SELECT * 
			FROM #__ijoomeradv_extensions";
	$db->setQuery($query);
	$rows=$db->loadObjectlist();
		
	for($i=0,$cnt=count($rows);$i<$cnt;$i++){
		$query="DROP TABLE `#__ijoomeradv_{$rows[$i]->classname}_config`";
		$db->setQuery($query);
		$db->Query();
	}
}