<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : Jomsocial_1.5 (compatible with jomsocial 2.6)
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );
class jomsocial{
	var $classname = 'jomsocial';
	var $sessionWhiteList=array("user.profileTypes","user.getTermsNCondition");
	
	function init(){
		jimport('joomla.utilities.date');
		jimport('joomla.html.pagination');
		
		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'helpers' .DS. 'time.php');
		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'helpers' .DS. 'url.php');
		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'helpers' . DS . 'owner.php');
		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' .DS. 'core.php');
		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' .DS. 'template.php');
		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'controllers' . DS . 'controller.php');
		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'models' . DS . 'models.php' );
		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'views' .DS. 'views.php');
		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'views' .DS.'inbox' .DS. 'view.html.php');
		
		$lang =& JFactory::getLanguage();
		$lang->load('com_community');
		$plugin_path = JPATH_COMPONENT_SITE.DS.'extensions';
		$lang->load('jomsocial',$plugin_path.DS.'jomsocial', $lang->getTag(), true);
		if(file_exists(JPATH_COMPONENT_SITE.DS.'extensions'.DS.'jomsocial'.DS."helper.php")){
			require_once(JPATH_COMPONENT_SITE.DS.'extensions'.DS.'jomsocial'.DS."helper.php"); 
		}
		
		if(IJ_JOOMLA_VERSION >= 3.0){
			define('JOOMLA_DB_NAMEQOUTE','quoteName');
		}else{
			define('JOOMLA_DB_NAMEQOUTE','nameQuote');
		}
	}

	function getconfig(){
		$this->init();
		$config = CFactory::getConfig ();
		$jsonarray=array();
		$jsonarray['createEvent']=intval(($config->get('enableevents') && $config->get('createevents') && $config->get('eventcreatelimit')));
		$jsonarray['createGroup']=intval(($config->get('enablegroups') && $config->get('creategroups') && $config->get('groupcreatelimit')));
		$jsonarray['isVideoUpload']=intval(($config->get('enablevideos') && $config->get('enablevideosupload') && $config->get('videouploadlimit')));
		$jsonarray['videoUploadSize']=intval($config->get('maxvideouploadsize'));
		$jsonarray['isPhotoUpload']=intval(($config->get('enablephotos') && $config->get('photouploadlimit')));
		$jsonarray['PhotoUploadSize']=intval($config->get('maxuploadsize'));
		$jsonarray['isEnableTerms']=intval($config->get('enableterms'));
		$jsonarray['termsObject']='{"extName":"jomsocial","extView":"user","extTask":"getTermsNCondition"}';
		
		//List ijoomeradv jomsocial config in applicationConfig
		$db =& JFactory::getDBO();
		$query="SELECT * 
				From #__ijoomeradv_jomsocial_config
				WHERE name='ENABLE_VOICE'";
		$db->setQuery($query);
		$config_array=$db->loadObject();
		$jsonarray['isEnableVoice']=intval($config_array->value);
		
		return $jsonarray;
	}
	
	function write_configuration( &$d ) {
		$db =& JFactory::getDBO();
		$query="SELECT * 
				From #__ijoomeradv_jomsocial_config";
		$db->setQuery($query);
		$config_array=$db->loadObjectList();
		foreach($config_array as $config){
			$config_name=$config->name;
			if(isset($d[$config_name])){
				$query="UPDATE #__ijoomeradv_jomsocial_config 
						SET value = '{$d[$config_name]}' 
						WHERE name = '{$config_name}' ";
				$db->setQuery($query);
				$db->query();
			}
		}
	   return true;	
   }
   
   /*
    * Prepares special type of html for jomsocial
    */
	function prepareHTML(&$config){
		$db =& JFactory::getDBO();
		foreach($config as $key=>$value){
			$config[$key]->caption=JText::_($value->caption);
			$config[$key]->description=JText::_($value->description);
			
			switch($value->type){
				case 'jom_field':
					$query="SELECT * 
							FROM #__community_fields 
							WHERE type!='group'";
					$db->setQuery($query);
					$fields=$db->loadObjectList();
					
					$input='<select name="'.$value->name.'" id="'.$value->name.'">';
					$input.='<option value="">Select Field...</option>';
					if($fields){
						foreach($fields as $field){
							$selected=($field->id===$value->value)?'selected="selected"':'';
							$input.='<option value="'.$field->id.'" '.$selected.'>'.$field->name.'</option>';
						}
					}
					$input.='</select>';
					$config[$key]->html=$input;
					break;	
			}
		}
	}
}
?>
