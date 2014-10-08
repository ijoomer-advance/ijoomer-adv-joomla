<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : Jomsocial_1.5 (compatible with Jomsocial 3.0)
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die;
class jomsocial{
	var $classname = 'jomsocial';
	var $sessionWhiteList=array("user.profileTypes");

	function init(){
		jimport('joomla.utilities.date');
		jimport('joomla.html.pagination');

		require_once  JPATH_ROOT . '/components/com_community/helpers' .'/'. 'time.php';
		require_once  JPATH_ROOT . '/components/com_community/helpers' .'/'. 'url.php';
		require_once  JPATH_ROOT . '/components/com_community/helpers/owner.php';
		require_once  JPATH_ROOT . '/components/com_community/libraries' .'/'. 'core.php';
		require_once  JPATH_ROOT . '/components/com_community/libraries' .'/'. 'template.php';
		require_once  JPATH_ROOT . '/components/com_community/controllers/controller.php';
		require_once  JPATH_ROOT . '/components/com_community/models/models.php' ;
		require_once  JPATH_ROOT . '/components/com_community/views' .'/'. 'views.php';
		require_once  JPATH_ROOT . '/components/com_community/views' .'/inbox' .'/'. 'view.html.php';

		$lang =& JFactory::getLanguage();
		$lang->load('com_community');
		$plugin_path = JPATH_COMPONENT_SITE.'/extensions';
		$lang->load('jomsocial',$plugin_path.'/jomsocial', $lang->getTag(), true);
		if(file_exists(JPATH_COMPONENT_SITE.'/extensions/jomsocial/'."helper.php")){
			require_once JPATH_COMPONENT_SITE.'/extensions/jomsocial/'."helper.php";
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
				if(is_array($d[$config_name])){
					$d[$config_name] = implode(',',$d[$config_name]);
				}
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
