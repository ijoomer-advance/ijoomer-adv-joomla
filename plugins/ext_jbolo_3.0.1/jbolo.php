<?php
/**
 * @copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
 * @license GNU/GPL, see license.txt or http://www.gnu.org/copyleft/gpl.html
 * Developed by Tailored Solutions - ijoomer.com
 *
 * ijoomer can be downloaded from www.ijoomer.com
 * ijoomer is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * You should have received a copy of the GNU General Public License
 * along with ijoomer; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
class jbolo {
	public $classname = "jbolo";
	public $sessionWhiteList=array(	'ichatmain.polling',
									'ichatmain.pushChatToNode',
									'ichatmain.initiateNode',
									'ichatmain.chathistory',
									'ichatmain.upload_file');
	
	function init(){		
		$lang =& JFactory::getLanguage();
		$lang->load('com_jbolo');
		$lang->load('jbolo',JPATH_COMPONENT_SITE.DS.'extensions'.DS.'jbolo', $lang->getTag(), true);
	}
	
	function getconfig(){
		$jsonarray=array();
		$params = JComponentHelper::getParams('com_jbolo');
		$jsonarray['chathistory'] 	= $params->get('chathistory');
		$jsonarray['groupchat'] 	= $params->get('groupchat');
		$jsonarray['maxChatUsers'] 	= $params->get('maxChatUsers');
		$jsonarray['sendfile'] 		= $params->get('sendfile');
		$jsonarray['maxSizeLimit'] 	= $params->get('maxSizeLimit');
		return $jsonarray; 
	}
	
	function write_configuration(&$d) {
		$db =JFactory::getDbo();
		$query = 'SELECT * 
				  FROM #__ijoomeradv_jbolo_config';
		$db->setQuery($query);
		$my_config_array = $db->loadObjectList();
		foreach ($my_config_array as $ke=>$val){
			if(isset($d[$val->name])){
				$sql = "UPDATE #__ijoomeradv_jbolo_config 
						SET value='{$d[$val->name]}' 
						WHERE name='{$val->name}'";
				$db->setQuery($sql);
				$db->query();
			}
		}
	}	
	
	function prepareHTML(&$config){
		//jbolo related html tags	
	}
}

class jbolo_menu {
	public function getRequiredInput($extension,$extTask,$menuoptions){
		$menuoptions = json_decode($menuoptions,true);
		$db = JFactory::getDbo();
		switch ($extTask){
			
		}
	}
	
	public function setRequiredInput($extension,$extView,$extTask,$remoteTask,$menuoptions,$data){
		$db = &JFactory::getDBO();
		$options = null;
		switch ($extTask){
				
		}
		
		if($options){
			$sql = "UPDATE #__ijoomeradv_menu 
					SET menuoptions = '".$options."' 
					WHERE views = '".$extension.".".$extView.".".$extTask.".".$remoteTask."'
					AND id='".$data['id']."'";
			
			$db->setQuery($sql);
			$db->query();
		}
	}
}