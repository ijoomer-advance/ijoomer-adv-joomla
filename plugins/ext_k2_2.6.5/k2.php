<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : Jomsocial_1.5 (compatible with k2 2.6.5)
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/
class k2 {
	public $classname = "k2";
	public $sessionWhiteList=array(	'items.items',
									'items.getitemdetail',
									'items.TagRelatedItems',
									'items.Userpage',
									'items.LatestItems',
									'getitems',
									'taglisting',
									'getitemdetail',
									'tagitems',
									'rating',
									'attachments',
									'addrating',
									'commentform',
									'addcomment',
									'getExtraField',
									'getExtraFieldGroup',
									'categoriesTree');
	
	function init(){		
		$lang =& JFactory::getLanguage();
		$lang->load('com_k2');
		$lang->load('k2',JPATH_COMPONENT_SITE.DS.'extensions'.DS.'k2', $lang->getTag(), true);
	}
	
	function getconfig(){
		$jsonarray=array();
		$db =& JFactory::getDBO();
		$query="SELECT value 
				From #__ijoomeradv_k2_config
				WHERE name='COMMENT_SETTINGS'";
		$db->setQuery($query);
		$configVal=$db->loadResult();
		$user = JFactory::getUser();
		
		$jsonarray['isEnableComment']=($configVal==0 && empty($user->id)) ? 0 : 1;
		return $jsonarray; 
		
	}
	
	function write_configuration(&$d) {
		$db =JFactory::getDbo();
		$query = 'SELECT * 
				  FROM #__ijoomeradv_k2_config';
		$db->setQuery($query);
		$my_config_array = $db->loadObjectList();
		foreach ($my_config_array as $ke=>$val){
			if(isset($d[$val->name])){
				$sql = "UPDATE #__ijoomeradv_k2_config 
						SET value='{$d[$val->name]}' 
						WHERE name='{$val->name}'";
				$db->setQuery($sql);
				$db->query();
			}
		}
	}	
	
	function prepareHTML(&$config){
		//k2 related html tags	
	}
}

class k2_menu {
	public function getRequiredInput($extension,$extTask,$menuoptions){
		require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';
		$class1 = new K2ModelCategories();
		$menuoptions = json_decode($menuoptions,true);
		$db = JFactory::getDbo();
		switch ($extTask){
			case 'items':
				$script = array();
				$script[] = '   function disableParams(){$("jform_request_childcat").setProperty("disabled","disabled");$("jform_request_latestItemsLimit").setProperty("disabled","disabled");$("jform_request_pagelayout").setProperty("disabled","disabled");}';
				$script[] = '   function enableParams(){$("jform_request_childcat").removeProperty("disabled");$("jform_request_latestItemsLimit").removeProperty("disabled");$("jform_request_pagelayout").removeProperty("disabled");}';
				$script[] = '	function setTask() {';
				$script[] = '		var counter=0;';
				$script[] = '		$$("#jform_request_catid option").each(function(el) {if (el.selected){value=el.value;counter++;}});';
				$script[] = '		if (counter>1 || counter==0){$("jform_request_itemordering").setProperty("disabled", "disabled");enableParams();}';
				$script[] = '       if (counter==1){ $("jform_request_itemordering").removeProperty("disabled");$("jform_request_id_itempage").removeProperty("disabled");disableParams();}';
				$script[] = '	}';
		
				// Add the script to the document head.
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
				
				require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';
				$class = new K2ModelCategories();
				$items = $class->categoriesTree();
				
				$html = '<fieldset class="panelform">
							<label title=""  for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_CATEGORIES').'
								<span class="star">&nbsp;*</span>
							</label>';
				
				$html .= '<select multiple="multiple" style="height: 200px;" name="jform[request][catid][]" onchange="setTask();" id="jform_request_catid">';
				
				foreach ($items as $key1=>$value1){
					$selected = '';
					for($j=0;$j<count($menuoptions['remoteUse']['catId']);$j++){
						if($menuoptions['remoteUse']['catId'][$j] == $value1->value){
							$selected = 'selected';
						}
					}
				 $html .= '<option value="'.$value1->value.'" '.$selected.'>'.$value1->text.'</option>';
				}
				
				$html .= '</select>';
				$html .= '<label title=""  for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">
							'.JText::_('COM_IJOOMERADV_FETCHITEMS_FROM_CHILDCAT').'
								<span class="star">&nbsp;*</span>
							</label>';
				
				$disable=(count($menuoptions['remoteUse']['catId'])==1) ? 'disabled="disabled"' : '';
				$html .= '<select name="jform[request][childcat]" id="jform_request_childcat"'.$disable.'>';
				
				$select = ($menuoptions['remoteUse']['itemsChildCat']) ? 'selected' : ''; 
				$html .= '<option value="0" '.$select.'>No</option><option value="1" '.$select.'>Yes</option>';
				$html .= '</select>';
				$html .= '<label id="jform_request_latestItemsLimit-lbl" for="jform_request_latestItemsLimit">'.JText::_('COM_IJOOMERADV_MAX_ITEMS_PER_CATEGORY').'<span class="star">&nbsp;*</span></label>';
				
				$select = ($menuoptions['remoteUse']['itemsPerCategoryLimit']) ? $menuoptions['remoteUse']['itemsPerCategoryLimit'] : '';
				$disable = (count($menuoptions['remoteUse']['catId'])==1) ? 'disabled="disabled"' : '';
				$html .= '  <input type="text" style="width: 50px;" name="jform[request][latestItemsLimit]" id="jform_request_latestItemsLimit" value="'.$select.'" '.$disable.' />';
				$html .= '<div><label title="" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_ITEMS_PER_PAGE').'
								<span class="star">&nbsp;*</span></div>
							</label>';
				
				$select =($menuoptions['remoteUse']['pageLimit']) ? $menuoptions['remoteUse']['pageLimit'] : '';
				$html .= '<input type="text" style="width: 50px;" name="jform[request][itempage]" id="jform_request_id_itempage"  value="'.$select.'"/>';	
				$html .= '<label title=""  for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_LEADING_COUNT').'
								<span class="star">&nbsp;*</span>
							</label>';
				
				$select = ($menuoptions['remoteUse']['leadLimit']) ? $menuoptions['remoteUse']['leadLimit'] : '';
				$html .= '<input type="text" style="width: 50px;" name="jform[request][leadcount]" id="jform_request_id_leadcount" value="'.$select.'" />';
				$html .= '<label title="" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_PAGELAYOUT').'
								<span class="star">&nbsp;*</span>
							</label>';
				
				$disable = (count($menuoptions['remoteUse']['catId'])==1) ? 'disabled="disabled"' : '';
				$html .= '<select name="jform[request][pagelayout]" id="jform_request_pagelayout" '.$disable.'>';
				
				$values = array("simpleList"=>"Simple List",
								"scrollingGrid"=>"Scrolling Grid",
								"news"=>"News",
								"directory"=>"Directory",
								"catalog"=>"Catalog");
				foreach($values as $plk=>$plv){
					$select = ($menuoptions['remoteUse']['pageLayout'] == $plk) ? 'selected="selected"' : '';
					$html .= '<option '.$select.' value="'.$plk.'">'.$plv.'</option>';
				}
				
				$html .= '</select>';
				$html .= '<label title=""  for="jform_request_itemordering" id="jform_request_itemordering-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_ITEM_ORDERING').'
								<span class="star">&nbsp;*</span>
							</label>';
				
				$disable = (count($menuoptions['remoteUse']['catId'])>1 || count($menuoptions['remoteUse']['catId'])==0) ? 'disabled="disabled"' : '';
				$html .= '<select name="jform[request][itemordering]" id="jform_request_itemordering" '.$disable.'>';
				
				$itemArray = array(	'Inherit from category'=>"",
									'Oldest First(by date created)'=>"date",
									'Most recent First(by date created)'=>"rdate",
									'Most recent first (by date published)'=>"publishUp",
									'Title - alphabetical'=>"alpha",
									'Title - reverse alphabetical'=>"ralpha",
									'Ordering'=>"order",
									'Ordering reverse'=>"rorder",
									'Featured first'=>"featured",
									'Most popular'=>"hits",
									'Highest rated'=>"best",
									'Latest modified'=>"modified",
									'Random'=>"rand"
							);
				foreach($itemArray as $k=>$v){
					$select = ($menuoptions['remoteUse']['ordering'] == $v) ? 'selected': '';
					$html .= '<option '.$select.' value="'.$v.'">'.$k.'</option>';
				}
				$html .= '</select></div>';
				$html .= '</fieldset>';
				return $html;
				break;
				
			
			case 'ItemDetail':
				$queryy = "SELECT i.title 
    					FROM #__k2_items as i 
    					WHERE i.id>0 
    					AND i.trash=0 
    					AND i.published=1
    					AND i.id=".$menuoptions['remoteUse']['itemID']; 
				$db->setQuery($queryy);
				$itemname = $db->loadResult();
				
				JHtml::_('behavior.modal', 'a.modal');
				
				$script = array();
				$script[] = '	function jSelectItem(id, title, object) {';
				$script[] = '		document.getElementById("jform[request][id]" + "_id").value = id;';
				$script[] = '		document.getElementById("jform[request][id]" + "_name").value = title;';
				$script[] = '		SqueezeBox.close();';
				$script[] = '	}';
		
				// Add the script to the document head.
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
				// Setup variables for display.
				$html	= array();
				
				$link = 'index.php?option=com_k2&view=items&task=element&tmpl=component&object=jform[request][id];';
				// The current user display field.
				$html[] = '<label id="jform_request_id-lbl" for="jform_request_id">'.JText::_('COM_IJOOMERADV_SELECT_ITEM').'<span class="star">&nbsp;*</span></label>';
				$html[] = '<div class="fltlft">';
				$html[] = '  <input type="text" id="jform[request][id]_name" value="'.$itemname.'" disabled="disabled" size="20" />';
				$html[] = '</div>';
		
				// The user select button.
				$html[] = '<div class="button2-left">';
				$html[] = '<div class="blank">';
				$html[] = '<a class="modal btn" title="Select an item"  href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="{handler: \'iframe\', size: {x: 700, y: 450}}">select</a>';
				$html[] = '</div>';
				$html[] = '</div>';
		
				$html[] = '<input type="hidden" id="jform[request][id]_id" name="jform[request][id]" value="'.$menuoptions['remoteUse']['itemID'].'" />';
				//
				$html[] = '<label title="" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_PAGELAYOUT').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html[] = '<select name="jform[request][pagelayout]" id="jform_request_pagelayout">';
				
				$values = array("simpleDetail"=>"Simple List","newsDetail"=>"News","catalogDetail"=>"Catalog");
				foreach($values as $plk=>$plv){
					$select = ($menuoptions['remoteUse']['pageLayout'] == $plk) ? 'selected="selected"' : '';
					$html[] = '<option '.$select.' value="'.$plk.'">'.$plv.'</option>';
				}
				
				$html[] = '</select>';
				//
				return implode("\n", $html);
				break;	
					
					
				
			case 'TagRelatedItems':
				// Load the modal behavior script.
				JHtml::_('behavior.modal', 'a.modal');
				$script = array();
				$script[] = '	function jSelectTag(id, title, object) {';
				$script[] = '		document.getElementById("jform[request][tag]" + "_id").value = id;';
				$script[] = '		document.getElementById("jform[request][tag]" + "_name").value = title;';
				$script[] = '		SqueezeBox.close();';
				$script[] = '	}';
		
				// Add the script to the document head.
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
				// Setup variables for display.
				$html	= array();
				
				$link = 'index.php?option=com_k2&view=tags&task=element&tmpl=component&object=jform[request][tag];';
				// The current user display field.
				$html[] = '<label id="jform_request_tag-lbl" for="jform_request_tag">'.JText::_('COM_IJOOMERADV_SELECT_TAG').'<span class="star">&nbsp;*</span></label>';
				$html[] = '<div class="fltlft">';
				$html[] = '  <input type="text" id="jform[request][tag]_name" value="'.$menuoptions['remoteUse']['tag'].'" disabled="disabled" size="20" />';
				$html[] = '</div>';
		
				// The user select button.
				$html[] = '<div class="button2-left">';
				$html[] = '  <div class="blank">';
				$html[] = '	<a class="modal" title="Select a tag"  href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">select</a>';
				$html[] = '  </div>';
				$html[] = '</div>';
				$html[] = '<input type="hidden" id="jform[request][tag]_id" name="jform[request][tag]" value="'.$menuoptions['remoteUse']['tag'].'" />';
				
				require_once JPATH_ADMINISTRATOR.'/components/com_k2/models/categories.php';
				$class1 = new K2ModelCategories();
				$Allcats = $class1->categoriesTree();
				
				$html[] = '<label title=""  for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_FILTER_CATEGORIES').'
								<span class="star">&nbsp;*</span>
							</label>';
				
				$html[] = '<select multiple="multiple" style="height: 200px;" name="jform[request][catid][]" id="jform_request_catid">';
				foreach ($Allcats as $keyy=>$vall){
					$selcat = '';
					for($i=0;$i<count($menuoptions['remoteUse']['catId']);$i++){
						if($menuoptions['remoteUse']['catId'][$i] == $vall->value){
							$selcat = 'selected';
						}
					}
					
				 $html[] = '<option value="'.$vall->value.'" '.$selcat.'>'.$vall->text.'</option>';
				}
				
				$html[] = '</select>';
				$html[] = '<label title="" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_ITEM_ORDERING').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html[] = '<select name="jform[request][item_order]" id="jform_request_item_order">';
				
				$itemsArray = array('Inherit from category'=>"",
									'Default'=>"id",
									'Oldest First(by date created)'=>"date",
									'Most recent First(by date created)'=>"rdate",
									'Most recent first (by date published)'=>"publishUp",
									'Title - alphabetical'=>"alpha",
									'Title - reverse alphabetical'=>"ralpha",
									'Ordering'=>"order",
									'Ordering reverse'=>"rorder",
									'Featured first'=>"featured",
									'Most popular'=>"hits",
									'Highest rated'=>"best",
									'Latest modified'=>"modified",
									'Random'=>"rand");
				foreach($itemsArray as $ke=>$ve){
					$selorder = ($menuoptions['remoteUse']['itemorder'] == $ve) ? 'selected' : '';
					$html[] = '<option '.$selorder.' value="'.$ve.'">'.$ke.'</option>';
				}
				
				$html[] = '</select>';
				$html[] = '<label title="" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_PAGELAYOUT').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html[] = '<select name="jform[request][pagelayout]" id="jform_request_pagelayout">';
				
				$values = array("simpleList"=>"Simple List",
								"scrollingGrid"=>"Scrolling Grid");
				foreach($values as $plk=>$plv){
					$select = ($menuoptions['remoteUse']['pageLayout'] == $plk) ? 'selected="selected"' : '';
					$html[] = '<option '.$select.' value="'.$plk.'">'.$plv.'</option>';
				}
				
				$html[] = '</select>';
				$html[] = '<div><label title=""  for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_ITEMS_PER_PAGE').'
								<span class="star">&nbsp;*</span></div>
							</label>';
				$select2 = ($menuoptions['remoteUse']['pageLimit']) ? $menuoptions['remoteUse']['pageLimit'] : '';
				$html[] = '<input type="text" style="width: 50px;" name="jform[request][itempage]" id="jform_request_id_itempage"  value="'.$select2.'"/>';	
				
				return implode("\n", $html);
				break;
				
				
			case 'Userpage':
				$queryy = "SELECT u.name 
    					FROM #__users as u 
    					WHERE u.id=".$menuoptions['remoteUse']['userID']; 
				$db->setQuery($queryy);
				$selecteduser = $db->loadResult();
					JHtml::_('behavior.modal', 'a.modal');
				$script = array();
				$script[] = '	function jSelectUser(id, title, object) {';
				$script[] = '		document.getElementById("jform[request][id]" + "_id").value = id;';
				$script[] = '		document.getElementById("jform[request][id]" + "_name").value = title;';
				$script[] = '		SqueezeBox.close();';
				$script[] = '	}';
		
				// Add the script to the document head.
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
				// Setup variables for display.
				$html	= array();
				$link = 'index.php?option=com_k2&view=users&task=element&tmpl=component&object=jform[request][id];';
				// The current user display field.
				$html[] = '<label id="jform_request_id-lbl" for="jform_request_id">'.JText::_('COM_IJOOMERADV_SELECT_USER').'<span class="star">&nbsp;*</span></label>';
				$html[] = '<div class="fltlft">';
				$html[] = '  <input type="text" id="jform[request][id]_name" value="'.$selecteduser.'" disabled="disabled" size="20" />';
				$html[] = '</div>';
		
				// The user select button.
				$html[] = '<div class="button2-left">';
				$html[] = '  <div class="blank">';
				$html[] = '	<a class="modal btn" title="Select a user"  href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="{handler: \'iframe\', size: {x: 700, y: 450}}">select</a>';
				$html[] = '  </div>';
				$html[] = '</div>';
				$html[] = '<input type="hidden" id="jform[request][id]_id" name="jform[request][id]" value="'.$menuoptions['remoteUse']['userID'].'" />';
				
				$Allcategories = $class1->categoriesTree();
				$html[] = '<label title="" for="jform_request_userCategoriesFilter" id="jform_request_userCategoriesFilter-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_FILTER_CATEGORIES').'
								<span class="star">&nbsp;*</span>
							</label>';
				
				$html[] = '<select multiple="multiple" style="height: 200px;" name="jform[request][userCategoriesFilter][]" id="jform_request_userCategoriesFilter">';
				foreach ($Allcategories as $ke=>$val){
					$catselected = '';
					for($i=0;$i<count($menuoptions['remoteUse']['catId']);$i++){
						if($menuoptions['remoteUse']['catId'][$i] == $val->value){
							$catselected = 'selected';
						}
					}
					
				 $html[] = '<option value="'.$val->value.'" '.$catselected.'>'.$val->text.'</option>';
				}
				$html[] = '</select>';
				
				$html[] = '<label title="" for="jform_request_userOrdering" id="jform_request_userOrdering-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_ITEM_ORDERING').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html[] = '<select name="jform[request][userOrdering]" id="jform_request_item_order">';
			
				$itemsArray = array('Inherit from category'=>"",
									'Default'=>"id",
									'Oldest First(by date created)'=>"date",
									'Most recent First(by date created)'=>"rdate",
									'Most recent first (by date published)'=>"publishUp",
									'Title - alphabetical'=>"alpha",
									'Title - reverse alphabetical'=>"ralpha",
									'Ordering'=>"order",
									'Ordering reverse'=>"rorder",
									'Featured first'=>"featured",
									'Most popular'=>"hits",
									'Highest rated'=>"best",
									'Latest modified'=>"modified",
									'Random'=>"rand");
				foreach($itemsArray as $ke=>$ve){
					
					if($menuoptions['remoteUse']['ordering'] == $ve){
						$orderselected = 'selected';
					}else{
						$orderselected = '';
					}
					$html[] = '<option '.$orderselected.' value="'.$ve.'">'.$ke.'</option>';
				}
				$html[] = '</select>';
				$html[] = '<label title="" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_PAGELAYOUT').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html[] = '<select name="jform[request][pagelayout]" id="jform_request_pagelayout">';
				$values = array("simpleList"=>"Simple List","scrollingGrid"=>"Scrolling Grid");
				foreach($values as $plk=>$plv){
					$select = ($menuoptions['remoteUse']['pageLayout'] == $plk) ? 'selected="selected"':'';					
					$html[] = '<option '.$select.' value="'.$plk.'">'.$plv.'</option>';
				}
				
				$html[] = '</select>';
				$html[] = '<div><label title="" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_ITEMS_PER_PAGE').'
								<span class="star">&nbsp;*</span></div>
							</label>';
				$select = ($menuoptions['remoteUse']['pageLimit']) ? $menuoptions['remoteUse']['pageLimit'] : '';
				$html[] = '<input type="text" style="width: 50px;" name="jform[request][itempage]" id="jform_request_id_itempage"  value="'.$select.'"/>';
				return implode("\n", $html);
				break;	
				
				case 'LatestItems':
					JHtml::_('behavior.modal', 'a.modal');
					$script = array();
				
					$script[] = '	function jSelectUser(id, title, object) {';
					$script[] = 'var ul = document.getElementById("usersList");
								if(!document.getElementById("removediv_"+id)){
												alert("User added in the list");
												
								var newLI = document.createElement("LI");
								
								
								newLI.innerHTML = "<div id=removediv_"+id+"><img onclick=deletediv("+id+") id=remove_"+ id +" alt=Removeentryfromthelist src='.JURI::root().'administrator/templates/bluestork/images/admin/publish_x.png /><span class=handle>"+ title +"</span><input type=hidden name=jform[request][userIDs][] value="+id+"></div>";
								
								 
								ul.appendChild(newLI);
								
								}else{alert("User is already in the list");
										}
									}
										function deletediv(id) {
										document.getElementById("removediv_"+id).innerHTML = \'\';
									}';
												$script[] = '	function jSelectCategory(id, title, object) {';
												$script[] = 'var ul = document.getElementById("categoriesList");
											
								
								if(!document.getElementById("removecatdiv_"+id)){
												alert("Category added in the list");
												
								var newLI = document.createElement("LI");
								
								
								newLI.innerHTML = "<div id=removecatdiv_"+id+"><img onclick=deletecatdiv("+id+") id=removecat_"+ id +" alt=Removeentryfromthelist src='.JURI::root().'administrator/templates/bluestork/images/admin/publish_x.png /><span class=handle>"+ title +"</span><input type=hidden name=jform[request][categoryIDs][] value="+id+"></div>";
								
								 
								ul.appendChild(newLI);
								
								}else{alert("Category is already in the list");
										}
									}
										function deletecatdiv(id) {
										document.getElementById("removecatdiv_"+id).innerHTML = \'\';
									}';
		
					// Add the script to the document head.
					JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
					// Setup variables for display.
				
					$html = array();
					$html[] = '<label id="jform_request_source-lbl" for="jform_request_source">'.JText::_('COM_IJOOMERADV_CHOOSE_CONTENT_SOURCE').'<span class="star">&nbsp;*</span></label>';
					$html[] = '<select name="jform[request][source]" id="jform_request_source">';
					$values = array("Users","Categories");
					for($l=0;$l<count($values);$l++){
						$sourceselected = ($menuoptions['remoteUse']['source'] == $values[$l]) ? 'selected' : '';
					}
					$html[] = '<option value="Users" '.$sourceselected.'>Users</option><option value="Categories" '.$sourceselected.'>Categories</option>';
					$html[] = '</select>';
			
					
					$link = 'index.php?option=com_k2&view=users&task=element&tmpl=component';
					// The current user display field.
					$html[] = '<label id="jform_request_userIDs-lbl"  for="jform_request_userIDs">'.JText::_('COM_IJOOMERADV_SELECTED_USERS').'<span class="star">&nbsp;*</span></label>';
					// The user select button.
					$html[] = '<div class="button2-left">';
					$html[] = '  <div class="blank">';
					$html[] = '	<a class="modal btn" title="Click to select one or more users"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}">Click to select one or more users</a>';
					$html[] = '  </div>';
					$html[] = '</div>';
					
					$html[] = '<div style="clear:both;"></div>';
					$html[] = '<ul id="usersList" class="ui-sortable">';
					
					if($menuoptions['remoteUse']['userIDs']!=""){
						foreach($menuoptions['remoteUse']['userIDs'] as $kk=>$vv){
							$db = &JFactory::getDBO();
							$sql = "SELECT u.name 
		    						FROM #__users as u 
		    						WHERE u.id=".$vv; 
							$db->setQuery($sql);
							$UserName = $db->loadResult();
							$html[] ='<li><div id=removediv_'.$vv.'><img onclick=deletediv('.$vv.') id=remove_'.$vv.' alt=Removeentryfromthelist src='.JURI::root().'administrator/templates/bluestork/images/admin/publish_x.png /><span class=handle>'.$UserName.'</span><input type=hidden name=jform[request][userIDs][] value='.$vv.'></div></li>';
						}
					}
					$html[] = '</ul>'; 
					$link1 = 'index.php?option=com_k2&view=categories&task=element&tmpl=component';
					// The current user display field.
					$html[] = '<label id="jform_request_categoryIDs-lbl"  for="jform_request_categoryIDs">'.JText::_('COM_IJOOMERADV_SELECTED_CATEGORIES').'<span class="star">&nbsp;*</span></label>';
					
					// The user select button.
					$html[] = '<div class="button2-left">';
					$html[] = '  <div class="blank">';
					$html[] = '	<a class="modal btn" title="Click to select one or more users"  href="'.$link1.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}">Click to select one or more categories</a>';
					$html[] = '  </div>';
					$html[] = '</div>';
			
					$html[] = '<div style="clear:both;"></div>';
					$html[] = '<ul id="categoriesList" class="ui-sortable">';
					
					if($menuoptions['remoteUse']['catId']!=""){
						foreach($menuoptions['remoteUse']['catId'] as $ke=>$ve){
							$db = &JFactory::getDBO();
							$sql = "SELECT c.name 
	    						FROM #__k2_categories as c 
	    						WHERE c.id=".$ve; 
							$db->setQuery($sql);
							$CatName = $db->loadResult();
							$html[] ='<li><div id=removecatdiv_'.$ve.'><img onclick=deletecatdiv('.$ve.') id=removecat_'.$ve.' alt=Removeentryfromthelist src='.JURI::root().'administrator/templates/bluestork/images/admin/publish_x.png /><span class=handle>'.$CatName.'</span><input type=hidden name=jform[request][categoryIDs][] value='.$ve.'></div></li>';
						}
					}
					$html[] = '</ul>'; 
					$html[] = '<label title=""  for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_PAGELAYOUT').'
								<span class="star">&nbsp;*</span>
							</label>';
					$html[] = '<select name="jform[request][pagelayout]" id="jform_request_pagelayout">';
					$values = array("simpleList"=>"Simple List","scrollingGrid"=>"Scrolling Grid");
					foreach($values as $plk=>$plv){
						$select = ($menuoptions['remoteUse']['pageLayout'] == $plk) ? 'selected="selected"' : '';
						$html[] = '<option '.$select.' value="'.$plk.'">'.$plv.'</option>';
					}
					$html[] = '</select>';
					$html[] = '<div><label title=""  for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_ITEMS_PER_PAGE').'
								<span class="star">&nbsp;*</span></div>
							</label>';
					$select = ($menuoptions['remoteUse']['pageLimit']) ? $menuoptions['remoteUse']['pageLimit'] : '';
					$html[] = '<input type="text" style="width: 50px;" name="jform[request][itempage]" id="jform_request_id_itempage"  value="'.$select.'"/>';
					return implode("\n", $html);
					break;	
		}
	}
	
	public function setRequiredInput($extension,$extView,$extTask,$remoteTask,$menuoptions,$data){
		$db = &JFactory::getDBO();
		$options = null;
		switch ($extTask){
			case 'items':
				$jsoncats = json_encode($menuoptions['catid']);
				$options = '{"serverUse":{},
							 "remoteUse":{"catId":'.$jsoncats.',
										  "itemsChildCat":"'.$menuoptions['childcat'].'",
										  "itemsPerCategoryLimit":"'.$menuoptions['latestItemsLimit'].'",
										  "pageLimit":"'.$menuoptions['itempage'].'",
										  "leadLimit":"'.$menuoptions['leadcount'].'",
										  "pageLayout":"'.$menuoptions['pagelayout'].'",
										  "ordering":"'.$menuoptions['itemordering'].'"}}';
				break;
				
			case 'ItemDetail':
				$options = '{"serverUse":{},
							 "remoteUse":{"itemID":"'.$menuoptions['id'].'",
							 "pageLayout":"'.$menuoptions['pagelayout'].'"}}';
				break;
				
			case 'TagRelatedItems':
				$catid = json_encode($menuoptions['catid']);
				$options = '{"serverUse":{},
							 "remoteUse":{"tag":"'.$menuoptions['tag'].'",
							 "catId":'.$catid.',
							 "itemorder":"'.$menuoptions['item_order'].'",
							 "pageLayout":"'.$menuoptions['pagelayout'].'",
							 "pageLimit":"'.$menuoptions['itempage'].'"}}';
				break;
				
			case 'Userpage':
				$catIDs = json_encode($menuoptions['userCategoriesFilter']);
				$options = '{"serverUse":{},
							 "remoteUse":{"userID":"'.$menuoptions['id'].'",
							 "catId":'.$catIDs.',
							 "ordering":"'.$menuoptions['userOrdering'].'",
							 "pageLayout":"'.$menuoptions['pagelayout'].'",
							 "pageLimit":"'.$menuoptions['itempage'].'"}}';
				break;	
				
			case 'LatestItems':
				$userIDs = json_encode($menuoptions['userIDs']);
				$categoryIDs = json_encode($menuoptions['categoryIDs']);
				$options = '{"serverUse":{},
							 "remoteUse":{"source":"'.$menuoptions['source'].'",
							 "userIDs":'.$userIDs.',
							 "catId":'.$categoryIDs.',
							 "pageLayout":"'.$menuoptions['pagelayout'].'",
							 "pageLimit":"'.$menuoptions['itempage'].'"}}';
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