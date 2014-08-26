<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : ICMS_1.5 (compatible with joomla 3.0)
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );

class icms {
	public $classname = "icms";
	public $sessionWhiteList=array('articles.archive','articles.featured','articles.singleArticle','articles.articleDetail','categories.allCategories','categories.singleCategory','categories.category','categories.categoryBlog');
	
	function init(){
		include_once JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'models' . DS . 'category.php';
		include_once JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'models' . DS . 'archive.php';
		include_once JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'query.php';
		
		$lang =& JFactory::getLanguage();
		$lang->load('com_content');
		$plugin_path = JPATH_COMPONENT_SITE.DS.'extensions';
		$lang->load('icms',$plugin_path.DS.'icms', $lang->getTag(), true);
	}
	
	function write_configuration(&$d) {
		$db =JFactory::getDbo();
		$query = 'SELECT * 
				  FROM #__ijoomeradv_icms_config';
		$db->setQuery($query);
		$my_config_array = $db->loadObjectList();
		foreach ($my_config_array as $ke=>$val){
			if(isset($d[$val->name])){
				$sql = "UPDATE #__ijoomeradv_icms_config 
						SET value='{$d[$val->name]}' 
						WHERE name='{$val->name}'";
				$db->setQuery($sql);
				$db->query();
			}
		}
	}

	function getconfig(){
		$jsonarray=array();
		return $jsonarray;
	}
	
	function prepareHTML(&$Config)
	{
		//TODO : Prepare custom html for ICMS
	}
}

class icms_menu {
	public function getRequiredInput($extension,$extView,$menuoptions){
		$menuoptions = json_decode($menuoptions,true);
		switch ($extView){
			case 'categoryBlog':
				$selvalue = $menuoptions['remoteUse']['id'];
				require_once JPATH_ADMINISTRATOR.'/components/com_categories/models/categories.php';
			
				//$class = new CategoriesModelCategories();
				//$query = $class->getListQuery();
				
				$query = " SELECT * FROM #__categories WHERE `extension` ='com_content' ";
				$db = JFactory::getDbo();
				$db->setQuery($query);
				$items = $db->loadObjectList();
				
				$html = '<fieldset class="panelform">
							<label title="" class="hasTip required" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_ICMS_SELECT_CATEGORY').'
								<span class="star">&nbsp;*</span>
							</label>';
				
				$html .= '<select name="jform[request][id]" id="jform_request_id">';
				foreach ($items as $key1=>$value1){
					$selected = '';
					if($selvalue == $value1->id){
						$selected = 'selected';
					}
					$level = '';
					for ($i=1; $i<$value1->level; $i++){
						$level .= '-';
					}
					$html .= '<option value="'.$value1->id.'" '.$selected.'>'.$level.$value1->title.'</option>';
				}
				$html .= '</select>';
				$html .= '</fieldset>';
				return $html;
				break;
				
			case 'singleCategory':
				$selvalue = $menuoptions['remoteUse']['id'];
				require_once JPATH_ADMINISTRATOR.'/components/com_categories/models/categories.php';
			
				//$class = new CategoriesModelCategories();
				//$query = $class->getListQuery();
				
				$query = " SELECT * FROM #__categories WHERE `extension` ='com_content' ";
				$db = JFactory::getDbo();
				$db->setQuery($query);
				$items = $db->loadObjectList();
				
				$html = '<fieldset class="panelform">
							<label title="" class="hasTip required" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_ICMS_SELECT_CATEGORY').'
								<span class="star">&nbsp;*</span>
							</label>';
				
				$html .= '<select name="jform[request][id]" id="jform_request_id">';
				foreach ($items as $key1=>$value1){
					$selected = '';
					if($selvalue == $value1->id){
						$selected = 'selected';
					}
					$level = '';
					for ($i=1; $i<$value1->level; $i++){
						$level .= '-';
					}
					$html .= '<option value="'.$value1->id.'" '.$selected.'>'.$level.$value1->title.'</option>';
				}
				$html .= '</select>';
				$html .= '</fieldset>';
				return $html;
				break;
				
			case 'singleArticle':
				$selvalue = (isset($menuoptions['remoteUse']['id']))?$menuoptions['remoteUse']['id']:0;
				$db = &JFactory::getDBO();
				$sql = "SELECT title FROM #__content
						WHERE id=".$selvalue;
				$db->setQuery($sql);
				$result = $db->loadResult();
				if($result){
					$title = $result; 
				}else{
					$title = 'COM_IJOOMERADV_ICMS_CHANGE_ARTICLE';
				}
				// Load the modal behavior script.
				JHtml::_('behavior.modal', 'a.modal');
		
				// Build the script.
				$script = array();
				$script[] = '	function jSelectArticle_jform_request_id(id, title, catid, object) {';
				$script[] = '		document.id("jform_request_id_id").value = id;';
				$script[] = '		document.id("jform_request_id_name").value = title;';
				$script[] = '		SqueezeBox.close();';
				$script[] = '	}';
		
				// Add the script to the document head.
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
				// Setup variables for display.
				$html	= array();
				$link	= 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_jform_request_id';
		
				// The current user display field.
				$html[] = '<div class="controls">';
				$html[] = '<span class="input-append">';
				$html[] = '<input class="input-medium" type="text" id="jform_request_id_name" value="'.JText::_($title).'" disabled="disabled" size="35" />';
		
				// The user select button.
				$html[] = '<a class="modal btn" title="'.JText::_('COM_IJOOMERADV_ICMS_CHANGE_ARTICLE').'"  href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('COM_IJOOMERADV_ICMS_CHANGE_ARTICLE_BUTTON').'</a>';
				$html[] = '</span>';
				$html[] = '</div>';
		
				$html[] = '<input type="hidden" id="jform_request_id_id" name="jform[request][id]" value="" />';
		
				return implode("\n", $html);
				break;
		}
	}
	
	public function setRequiredInput($extension,$extView,$extTask,$remoteTask,$menuoptions,$data){
		$db = &JFactory::getDBO();
		$options = null;
		switch ($extTask){
			case 'categoryBlog':
				$categoryid = $menuoptions['id'];
				$options = '{"serverUse":{},"remoteUse":{"id":'.$categoryid.'}}';
				break;
				
			case 'singleCategory':
				$categoryid = $menuoptions['id'];
				$options = '{"serverUse":{},"remoteUse":{"id":'.$categoryid.'}}';
				break;
				
			case 'singleArticle':
				$articleid = $menuoptions['id'];
				$options = '{"serverUse":{},"remoteUse":{"id":'.$articleid.'}}';
				break;
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