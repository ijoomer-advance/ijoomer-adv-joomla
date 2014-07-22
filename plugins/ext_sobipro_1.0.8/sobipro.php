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
class sobipro
{
	public $classname = "sobipro";
	public $sessionWhiteList=array(	'isobipro.sectionCategories',
									'isobipro.getsearchField',
									'isobipro.addentryField');
	
	function init(){		
		$lang =& JFactory::getLanguage();
		$lang->load('com_sobipro');
		$plugin_path = JPATH_COMPONENT_SITE.DS.'extensions';
		$lang->load('sobipro',$plugin_path.DS.'sobipro', $lang->getTag(), true);
	}
	
	function getconfig(){
		$jsonarray=array();
		return $jsonarray;
	}
	
	function write_configuration( &$d ) {
		$db =& JFactory::getDBO();
		$query="SELECT * 
				From #__ijoomeradv_sobipro_config";
		$db->setQuery($query);
		$config_array=$db->loadObjectList();
		foreach($config_array as $config){
			$config_name=$config->name;
			if(isset($d[$config_name])){
			$implode = implode(',',$d[$config_name]);
				$query="UPDATE #__ijoomeradv_sobipro_config";
				$query.=(is_array($d[$config_name])) ? " SET value='{$implode}'" : " SET value='{$d[$config_name]}'" ; 
				$query.=" WHERE name='{$config_name}'";
				$db->setQuery($query);
				$db->query();
			}
		}
	   return true;	
   }
	
   function prepareHTML(&$config){
		$db =& JFactory::getDBO();
		foreach($config as $key=>$value){
			$config[$key]->caption=JText::_($value->caption);
			$config[$key]->description=JText::_($value->description);
			
			switch($value->type){
				case 'sobi_field':
					$query="SELECT so.id,so.name  
							FROM #__sobipro_object as so 
							WHERE so.approved='1' 
							AND so.confirmed 
							AND so.oType='section' 
							AND so.parent=0 
							AND so.state='1'";
					$db->setQuery($query);
					$sections = $db->loadObjectList();
					
					$space="@&nbsp;";
					$explode=explode(',',$value->value);
					$a = array();
					foreach($explode as $ex){
						$explode1=explode(':',$ex);
						$a[]=$explode1[1];
					}
					
					$input='<select class="inputbox" multiple="multiple" name="'.$value->name.'[]" id="'.$value->name.'" style="width: 180px;height:180px">';
					foreach ($sections as $section){
						$input.='<optgroup value="'.$section->id.'" label="'.$space."&nbsp;". $section->name.'">\n';
						$query="SELECT sf.fid,sf.nid  
								FROM #__sobipro_field as sf 
								WHERE sf.enabled=1
								AND sf.section={$section->id}";
						$db->setQuery($query);
						$fields = $db->loadObjectList();
						if($fields){
							foreach($fields as $field){
								if(in_array($field->fid,$a)){ 
									$selected = 'selected="selected"'; 
								}else{ 
									$selected = '';
								}
								$input.='<option value="'.$section->id.":".$field->fid.'" '.$selected.'>'.$field->nid.'</option>';
							}
						}
						$input.='</optgroup>';
						$config[$key]->html=$input;
					}
					$input.='</select>';
					break;
			}
		}
	}
}

class sobipro_menu {
	public function getRequiredInput($extension,$extTask,$menuoptions){
		$db = JFactory::getDBo();
		$menuoptions = json_decode($menuoptions,true);
		switch ($extTask){
			case 'sectionCategories':
				$sectionId 		= $menuoptions['remoteUse']['sectionID'];
				$catId 		= ($menuoptions['remoteUse']['categoryID']) ? $menuoptions['remoteUse']['categoryID'] : 0;
				$selvalue4  = $menuoptions['remoteUse']['pageLayout'];
				$featuredFirst  = $menuoptions['remoteUse']['featuredFirst'];
				$script = array();
				$script[] ='window.addEvent( "domready", function() {
						var semaphor = 0;
						var spApply = $$( "#toolbar-apply a" )[ 0 ];
						var spSave = $$( "#toolbar-save a" )[ 0 ];
						spApplyFn = spApply.onclick;
						spApply.onclick = null;
						spSaveFn = spSave.onclick;
						spSave.onclick = null;
						try {
							var spSaveNew = $$( "#toolbar-save-new a" )[ 0 ];
							spSaveNewFn = spSaveNew.onclick;
							spSaveNew.onclick = null;
							spSaveNew.addEvent( "click", function() {
								if( SPValidate() ) {
									spSaveNewFn();
								}
							} );
						} catch( e ) {}
	
						function SPValidate()
						{
							if( $( "spsection" ).value == 0 || $( "spsection" ).value == "" ) {
								alert( "You must at least select a section" );
								return false;
							}
							else {
								return true;
							}
						}
						spApply.addEvent( "click", function() {
							if( SPValidate() ) {
								spApplyFn();
							}
						} );
						spSave.addEvent( "click", function() {
							if( SPValidate() ) {
								spSaveFn();
							}
						} );
						$( "spsection" ).addEvent( "change", function( event ) {
							//sid = $( "spsection" ).options[ $( "spsection" ).selectedIndex ].value;
							$( "sid" ).value = 0;
							//semaphor = 0;
						} );
						if( $( "sp_category" ) != null ) {
							$( "sp_category" ).addEvent( "click", function( ev ) {
								if( semaphor == 1 ) {
									return false;
								}
								semaphor = 1;
								new Event( ev ).stop();
								if( $( "spsection" ).value == 0 ) {
									alert( "Please select section first" );
									semaphor = 0;
									return false;
								}
								else {
									url = "'.JURI::root().'administrator/index.php?option=com_sobipro&task=category.chooser&treetpl=rchooser&tmpl=component&sid=" + $( "spsection" ).value
									try {
										SqueezeBox.open( $( "sp_category" ), { handler: "iframe", size: { x: 700, y: 500 }, url: url });
									}
									catch( x ) {
										SqueezeBox.fromElement( $( "sp_category" ), { url: url, handler: "iframe", size: { x: 700, y: 500 } } );
									}
								}
							} );
						}
					} );
					function SP_close()
					{
						$( "sbox-btn-close" ).fireEvent( "click" );
						semaphor = 0;
					}';
		
				// Add the script to the document head.
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
				
				$html = '<fieldset class="panelform"><ul class="adminformlist"><li>
							<label title="" class="" for="jform_request_SOBI_SELECT_SECTION" id="jform_request_SOBI_SELECT_SECTION-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_SECTION').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html .= '<div style="margin-top: 2px;"><select id="spsection" class="text_area required" name="spsection" aria-required="true" required="required" aria-invalid="false">';
				$html .= '<option value="0">Select section</option>';
				
				$query = "SELECT so.id,so.name 
							FROM #__sobipro_object as so 
							WHERE so.oType='section' AND so.parent=0"; 
				$db->setQuery($query);
				$sections = $db->loadObjectList();
				foreach ($sections as $key=>$value){
					$selected = '';
						if(isset($catId) && ($sectionID == $value->id)){
							$selected = 'selected';
						}else if(!isset($parent) && ($sectionId == $value->id)){
							$selected = 'selected';
						}
					$html .= '<option value="'.$value->id.'" '.$selected.'>'.$value->name.'</option>';
				}
				$html .= '</select></div></li>';
				$html .= '<li><label id="jform_request_cid-lbl" class="" title="" for="jform_request_cid">'.JText::_('COM_IJOOMERADV_SELECT_CATEGORY').'
							<span class="star">&nbsp;*</span>
							</label>';
				$html .= '<button id="sp_category" class="inputbox" style="border: 1px solid silver;" name="sp_category" type="button">'.JText::_('COM_IJOOMERADV_SELECT_CATEGORY').'</button></li>';
				$html .= '<li><label id="jform_request_sid-lbl" class="" for="jform_request_sid"></label>';
				$html .= '<input id="sid" class="text_area" type="text" readonly="readonly" style="text-align: center;" size="5" value="'.$catId.'" name="jform[request][sid]">';
				$html .= '</li></ul>';
				$html .= '<label title="" class="" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_PAGELAYOUT').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html .= '<select name="jform[request][pagelayout]" id="jform_request_pagelayout">';
				$values = array("Business","Cars","Restaurant");
				foreach($values as $pagevalue){
					$select = ($selvalue4 == $pagevalue) ? 'selected="selected"' : '';
					$html .= '<option '.$select.' value="'.$pagevalue.'">'.$pagevalue.'</option>';
				}
				$html .= '</select>';
				$html .= '<label title="" class="" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_FEATURED_FIRST').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html .= '<select name="jform[request][featuredFirst]" id="jform_request_featuredFirst">';
				$featuredoptions = array("Yes","No");
				foreach($featuredoptions as $featuredoption){
					$featureselected = ($featuredFirst == $featuredoption) ? 'selected="selected"' : '';
					$html .= '<option '.$featureselected.' value="'.$featuredoption.'">'.$featuredoption.'</option>';
				}
				$html .= '</select>';
				$html .= '</fieldset>';
				return $html;
				break;
				
			case 'addentryField':
				$sid = $menuoptions['remoteUse']['sectionID'];
				
				$html = '<fieldset class="panelform"><ul class="adminformlist"><li>
							<label title="" class="" for="jform_request_SOBI_SELECT_SECTION" id="jform_request_SOBI_SELECT_SECTION-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_SECTION').'
								<span class="star">&nbsp;*</span>
							</label>';
				
				$html .= '<div style="margin-top: 2px;"><select id="spsection" class="text_area required" name="spsection" aria-required="true" required="required" aria-invalid="false">';
				$html .= '<option value="0">Select section</option>';
				$db = JFactory::getDBo();
				$query="SELECT so.id,so.name 
						FROM #__sobipro_object as so 
						WHERE so.oType='section' 
						AND so.parent=0"; 
				$db->setQuery($query);
				$sections = $db->loadObjectList();
				$query="SELECT so.parent 
						FROM #__sobipro_object as so 
						WHERE so.id={$sid} 
						AND so.parent!=0"; 
				$db->setQuery($query);
				$parent = $db->loadResult();
				foreach ($sections as $key=>$value){
					$selected = ((isset($parent) && ($parent == $value->id)) || (!isset($parent) && ($sid == $value->id))) ? 'selected' : '';
					$html .= '<option value="'.$value->id.'" '.$selected.'>'.$value->name.'</option>';
				}
				$html .= '</select></div></li>';
				$html .= '</ul>';
				$html .= '<label title="" class="" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_PAGELAYOUT').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html .= '<select name="jform[request][pagelayout]" id="jform_request_pagelayout">';
				$values = array("Business",
								"Cars",
								"Restaurant");
				foreach($values as $pagevalue){
					$select = ($menuoptions['remoteUse']['pageLayout'] == $pagevalue) ? 'selected="selected"' : '';
					$html .= '<option '.$select.' value="'.$pagevalue.'">'.$pagevalue.'</option>';
				}
				$html .= '</select>';
				$html .= '</fieldset>';
				return $html;
				break;
		}
	}
	
	public function setRequiredInput($extension,$extView,$extTask,$remoteTask,$menuoptions,$data){
		$db = &JFactory::getDBO();
		$options = null;
		switch ($extTask){
			case 'sectionCategories':
				$pagelayout = $menuoptions['pagelayout'];
				$featuredFirst = $menuoptions['featuredFirst'];
				$options = '{"serverUse":{},"remoteUse":{"sectionID":"'.$_POST['spsection'].'","categoryID":"'.$menuoptions['sid'].'","pageLayout":"'.$pagelayout.'","featuredFirst":"'.$featuredFirst.'"}}';
				break;
			
			case 'addentryField':
				$sectionID = $_POST['spsection'];
				$pagelayout = $menuoptions['pagelayout'];
				$options = '{"serverUse":{},"remoteUse":{"sectionID":"'.$sectionID.'","pageLayout":"'.$pagelayout.'"}}';
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
