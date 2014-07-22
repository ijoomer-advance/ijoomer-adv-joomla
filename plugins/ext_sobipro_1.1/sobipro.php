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
				define( 'SOBIPRO'		,	true);
				require_once JPATH_SITE.'/components/com_sobipro/lib/base/request.php';
				$sectionId 		= $menuoptions['remoteUse']['sectionID'];
				$categoryID = $menuoptions['remoteUse']['categoryID'];
				$entryID    = $menuoptions['remoteUse']['entryID'];
				if($menuoptions['remoteUse']['entryID']!=0){
					$catId 		= $menuoptions['remoteUse']['entryID'];
				}else if($menuoptions['remoteUse']['categoryID']!=0){
					$catId 		= $menuoptions['remoteUse']['categoryID'];
				}else{
					$catId 		= $menuoptions['remoteUse']['sectionID'];
				}
				if($menuoptions['remoteUse']['entryID']!=0){
					$oType 		= 'Entry';
				}else if($menuoptions['remoteUse']['categoryID']!=0){
					$oType 		= 'Category';
				}else{
					$oType 		= 'Section';
				}
				$selvalue4  = $menuoptions['remoteUse']['pageLayout'];
				$featuredFirst  = $menuoptions['remoteUse']['featuredFirst'];
				$script = array();
				$script[] ='</script><link rel="stylesheet" href="'.JURI::root().'media/sobipro/css/bootstrap/bootstrap.css" type="text/css"  />
							<script type="text/javascript">function checkVal(changeVal){
								//alert("Value is " + changeVal.value);
								document.getElementById("otype").value = "Section";
								document.getElementById("selectedCat").value = 0;
								document.getElementById("selectedCatName").value = "";
								//document.getElementById("selectedEntry").value = 0;
								//document.getElementById("selectedEntryName").value = "";
								document.getElementById("sid").value = changeVal.value;
							}
							function checkcatVal(catVal){
								//document.getElementById("selectedEntry").value = 0;
								//document.getElementById("selectedEntryName").value = "";
								var cat = document.getElementById("sid").value;
								if (cat == 0) {
   									alert("Please Select a Section First");
  			 					}else{
  			 						var NAME = document.getElementById("spCat")
									NAME.className="modal hide in";
									document.getElementById("spCat").style.display = "block";
  			 						var url = "'.JURI::root().'administrator/index.php?option=com_sobipro&task=category.chooser&treetpl=rchooser&multiple=1&tmpl=component&sid=" + $( "sid" ).value
  			 						var content = "<iframe id=\"spCatSelectFrame\" src=\"'.JURI::root().'administrator/index.php?option=com_sobipro&task=category.chooser&treetpl=rchooser&multiple=1&tmpl=component&sid=" + $( "sid" ).value + "\" style=\"width: 480px; height: 400px; border: none;\"></iframe>";	
  			 						document.getElementById("spCatsChooser").innerHTML = content;
								}
							}function checkentryVal(entryVal){
								var cat = document.getElementById("sid").value;
								if (cat == 0) {
   									alert("Please Select a Section First");
  			 					}else{
  			 						var NAME = document.getElementById("spEntry")
									NAME.className="modal hide in";
									document.getElementById("spEntry").style.display = "block";
								}
							}
							function saveclick(){
								document.getElementById("ullist").innerHTML="";
								var str = escape(document.getElementById("spEntryChooser").value);
								if(str==""){
									//document.getElementById("ullist").innerHTML="";
									var ulDisplay = document.getElementById("ullist");
               					 	ulDisplay.style.display = "none";
								}
								var searchReq = window.XMLHttpRequest ? new XMLHttpRequest(): new ActiveXObject("Microsoft.XMLHTTP");
								var sid=document.getElementById("sid").value;
								document.getElementById("selectedCat").value = sid;
								var oMyForm = new FormData();
								oMyForm.append("task", "entry.search");
								oMyForm.append("sid", sid);
								oMyForm.append("format", "raw");
								oMyForm.append("search", str);
								searchReq.open("POST", "index.php?option=com_sobipro&task=entry.search&sid=" +sid+"&format=raw&search=" + str, false);
								searchReq.send(oMyForm);
								var jsonData= searchReq.responseText;
								//var collection = new Array();
								var myObject = eval("(" + jsonData + ")");
								//alert(myObject.length);
								if(str!=""){
									for (var m=0; m<myObject.length; m++)
									{
										var collection = new Array();
										var liId = myObject[m]["id"];
										var liTitle = myObject[m]["name"];
										var liName = myObject[m]["name"]+" ( "+myObject[m]["id"]+" )";
										//collection = {"name":myObject[m]["name"]+" ( "+myObject[m]["id"]+" )","id":myObject[m]["id"],"title":myObject[m]["name"]};
										//alert(collection.toString);
										console.log(collection);
									 	var ulDisplay = document.getElementById("ullist");
		               					ulDisplay.style.display = "block";
		               					var li = document.createElement("li");
	               					 	var a = document.createElement("a");
	      							 	a.href = "#"; 
	      							  	a.innerHTML = liName;
	      							  	a.setAttribute("name",liTitle);
	      							  	a.setAttribute("id",liId);  
	      							  	a.setAttribute("onclick","saveVal(this)");
	               					  	li.setAttribute("title", liTitle);  
	               					  	ulDisplay.appendChild(li);
	               					  	li.appendChild(a);
									}
								}
							}
							function saveVal(saveval){
									document.getElementById("selectedEntryName").value = saveval.name;
									document.getElementById("selectedEntry").value = saveval.id;
									document.getElementById("spEntryChooser").value = saveval.name;
									var ulDisplay = document.getElementById("ullist");
               					 	ulDisplay.style.display = "none";
							}
							function catselect(){
								var selectedcat = document.getElementById("selectedCat").value;
								document.getElementById("otype").value = "Category";
								document.getElementById("sid").value = selectedcat;
								var selectedcatName = document.getElementById("selectedCatName").value;
								document.getElementById("sp_category").value = selectedcatName;
								var NAME = document.getElementById("spCat")
								NAME.className="modal hide";
								document.getElementById("spCat").style.display = "none";
							}function entryselect(){
								var selectedentry = document.getElementById("selectedEntry").value;
								document.getElementById("otype").value = "Entry";
								document.getElementById("sid").value = selectedentry;
								
								var entryNAME = document.getElementById("spEntry")
								entryNAME.className="modal hide";
								document.getElementById("spEntry").style.display = "none";
								document.getElementById("selectedEntry").value;
							}function closewindow(){
								var NAME = document.getElementById("spCat")
								NAME.className="modal hide";
								document.getElementById("spCat").style.display = "none";
								
								var entryNAME = document.getElementById("spEntry")
								entryNAME.className="modal hide";
								document.getElementById("spEntry").style.display = "none";
							}</script>';
		
				// Add the script to the document head.
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
				
				$html = '<fieldset class="panelform"><ul class="adminformlist"><li>
							<label title="" class="" for="jform_request_SOBI_SELECT_SECTION" id="jform_request_SOBI_SELECT_SECTION-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_SECTION').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html .= '<div class="SobiPro" style="margin-top: 2px;"><select id="spsection" class="text_area required" name="spsection" onchange="checkVal(this)" aria-required="true" required="required" aria-invalid="false">';
				$html .= '<option value="0">Select section</option>';
				
				$query = "SELECT so.id,so.name 
							FROM #__sobipro_object as so 
							WHERE so.oType='section' AND so.parent=0"; 
				$db->setQuery($query);
				$sections = $db->loadObjectList();
				foreach ($sections as $key=>$value){
					$selected = '';
						if($sectionId == $value->id){
							$selected = 'selected="selected"';
						}
					$html .= '<option value="'.$value->id.'" '.$selected.'>'.$value->name.'</option>';
				}
				$html .= '</select></div></li>';
				$html .= '<li><label id="jform_request_cid-lbl" class="" title="" for="jform_request_cid">'.JText::_('COM_IJOOMERADV_SELECT_CATEGORY').'
							<span class="star">&nbsp;*</span>
							</label>';
				$html .= '<div class="SobiPro"><button id="sp_category" class="btn input-medium" onclick="checkcatVal(this)" style="margin-top: 5px; width: 300px" name="sp_category" value="" type="button">'.JText::_('COM_IJOOMERADV_SELECT_CATEGORY').'</button>';
				$html .= '<div id="spCat" class="modal hide" style="width:500px;">
						  	<div class="modal-header">
						  		<button class="close" onclick="closewindow()" data-dismiss="modal" type="button">×</button>
						  		<h3>Select Category</h3>
						  	</div>
						  	<div class="modal-body"><div id="spCatsChooser"></div></div>
						  	<div class="modal-footer"><a class="btn" onclick="closewindow()" data-dismiss="modal" href="#">Close</a>
						  		<a id="spCatSelect" onclick="catselect()" class="btn btn-primary" data-dismiss="modal" href="#">Save Selection</a>
						  	</div>
						  </div>
						  <input id="selectedCat" type="hidden" value="'.$categoryID.'" name="selectedCat">
						  <input id="selectedCatName" type="hidden" value="" name="selectedCatName">
						  </div></li>';
			/*	$html .= '<li><label id="jform_request_cid-lbl" class="" title="" for="jform_request_cid">'.JText::_('COM_IJOOMERADV_SELECT_ENTRY').'
							<span class="star">&nbsp;*</span>
							</label>';
				$html .= '<div class="SobiPro">
						  <button id="sp_entry" class="btn input-large btn-primary" onclick="checkentryVal(this)" style="margin-top: 5px; width: 300px" name="sp_entry" type="button" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_ENTRY').'</button>
							<div id="spEntry" class="modal hide" style="width: 500px; overflow: visible;" aria-hidden="true">
								<div class="modal-header">
									<button class="close" onclick="closewindow()" data-dismiss="modal" type="button">×</button>
									<h3>Select Entry</h3>
								</div>
								<div class="modal-body" style="overflow-y: visible;">
									<label>Entry name</label>
									<input id="spEntryChooser" onkeyup="saveclick()" class="span6" type="text" placeholder="Type something..." style="width: 95%" autocomplete="off" data-provide="typeahead" aria-invalid="false">
									<ul class="typeahead dropdown-menu typeahead-width" id="ullist" style="top: 70px; left: 15px; display: none; font-size: 13px;">
									</ul>
								</div>
								<div class="modal-footer">
									<a class="btn" onclick="closewindow()" data-dismiss="modal" href="#">Close</a>
									<a id="spEntrySelect" onclick="entryselect()" class="btn btn-primary" data-dismiss="modal" href="#">Save Selection</a>
								</div>
							</div>
							<input id="selectedEntry" type="hidden" value="'.$entryID.'" name="selectedEntry">
							<input id="selectedEntryName" type="hidden" value="" name="selectedEntryName">
						</div>
						</li>';*/
				
				$html .= '<li>
						  <label id="jform_request_sid-lbl" class="" for="jform_request_sid">Selected
                          <span class="star">&nbsp;*</span></label>
						  <div id="jform_request_sid" class="SobiPro">
						  <input id="otype" class="input-medium" type="text" readonly="readonly" style="text-align: center; margin-top: 10px;" value="'.$oType.'" name="type">
						  <input id="sid" class="input-mini" type="text" readonly="readonly" style="text-align: center; margin-top: 10px; margin-left: 10px;" value="'.$catId.'" name="jform[request][sid]">
						  </div>
						  </li>';
				$html .= '</ul>';
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
				
				case 'getDateListing':
				define( 'SOBIPRO'		,	true);
				require_once JPATH_SITE.'/components/com_sobipro/lib/base/request.php';
				$date=$menuoptions['remoteUse']['date'];
				$explode=explode('.',$date);
				$year=$explode[0];
				$month=$explode[1];
				$day=$explode[2];
				$selectedDate=$year.".".$month.".".$day;
				
				$sectionId 		= $menuoptions['remoteUse']['sectionID'];
				$selvalue4  	= $menuoptions['remoteUse']['pageLayout'];
				//$featuredFirst  = $menuoptions['remoteUse']['featuredFirst'];
				$script = array();
				$script[] ='</script><link rel="stylesheet" href="'.JURI::root().'media/sobipro/css/bootstrap/bootstrap.css" type="text/css"  />
							<script type="text/javascript">function checkVal(changeVal){
								//alert("Value is " + changeVal.value);
								document.getElementById("sid").value = changeVal.value;
							}
							</script>';
		
				// Add the script to the document head.
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
				
				$html = '<fieldset class="panelform"><ul class="adminformlist"><li>
							<label title="" class="" for="jform_request_SOBI_SELECT_SECTION" id="jform_request_SOBI_SELECT_SECTION-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_SECTION').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html .= '<div class="SobiPro" style="margin-top: 2px;"><select id="spsection" class="text_area required" name="spsection" onchange="checkVal(this)" aria-required="true" required="required" aria-invalid="false">';
				$html .= '<option value="0">Select section</option>';
				
				$query = "SELECT so.id,so.name 
							FROM #__sobipro_object as so 
							WHERE so.oType='section' AND so.parent=0"; 
				$db->setQuery($query);
				$sections = $db->loadObjectList();
				foreach ($sections as $key=>$value){
					$selected = '';
						if($sectionId == $value->id){
							$selected = 'selected="selected"';
						}
					$html .= '<option value="'.$value->id.'" '.$selected.'>'.$value->name.'</option>';
				}
				$html .= '</select></div></li>';
				$html .= '<label title="" class="" for="jform_request_SOBI_SELECT_DATE" id="jform_request_SOBI_SELECT_DATE-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_SELECT_DATE').'
								<span class="star">&nbsp;*</span>
							</label>';
				$html .= '<div class="SobiPro SobiProCalendar">
						<select name="sp_year" style="width:70px">';
				$query = "SELECT so.createdTime 
						  FROM #__sobipro_object as so 
						  WHERE so.oType='entry'"; 
				$db->setQuery($query);
				$createdDates = $db->loadResultArray();
				foreach($createdDates as $crkey=>$crVal){
					$explodedates=explode('-',$crVal);
					$Years[]=$explodedates[0];
				}
				$UniqueArray=array_unique($Years);
				$html .= '<option value="">select</option>';
				foreach($UniqueArray as $yeark=>$yearv){
					$select = ($year == $yearv) ? 'selected': '';
					$html .= '<option '.$select.' value="'.$yearv.'">'.$yearv.'</option>';
				}
				$html .= '</select>';
				$html .= '<select name="sp_month" style="width:110px">';
			  	$monthArray = array(	'None'=>"",
									'January'=>"1",
									'February'=>"2",
									'March'=>"3",
									'April'=>"4",
									'May'=>"5",
									'June'=>"6",
									'July'=>"7",
									'August'=>"8",
									'September'=>"9",
									'October'=>"10",
									'November'=>"11",
									'December'=>"12"
							);
				foreach($monthArray as $k=>$v){
					$select = ($month == $v) ? 'selected': '';
					$html .= '<option '.$select.' value="'.$v.'">'.$k.'</option>';
				}
				$html .= '</select>';
				$html .= '<select name="sp_day" style="width:70px">';
				$dayArray = array(	'None'=>"",
									'1'=>"1",'2'=>"2",
									'3'=>"3",'4'=>"4",
									'5'=>"5",'6'=>"6",
									'7'=>"7",'8'=>"8",
									'9'=>"9",'10'=>"10",
									'11'=>"11",'12'=>"12",
									'13'=>"13",'14'=>"14",
									'15'=>"15",'16'=>"16",
									'17'=>"17",'18'=>"18",
									'19'=>"19",'20'=>"20",
									'21'=>"21",'22'=>"22",
									'23'=>"23",'24'=>"24",
									'25'=>"25",'26'=>"26",
									'27'=>"27",'28'=>"28",
									'29'=>"29",'30'=>"30",
									'31'=>"31"
							);
				foreach($dayArray as $dayk=>$dayv){
					$select = ($day == $dayv) ? 'selected': '';
					$html .= '<option '.$select.' value="'.$dayv.'">'.$dayk.'</option>';
				}
				$html .= '</select>';
				$html .= '<input id="selectedDate" type="hidden" value="'.$selectedDate.'" name="jform[request][date]">
				</div>';
				$html .= '<li>
				<label id="jform_request_sid-lbl" class="" for="jform_request_sid">Selected
				<span class="star">&nbsp;*</span></label>
				<div id="jform_request_sid" class="SobiPro">
				<input id="otype" class="input-medium" type="text" readonly="readonly" style="text-align: center; margin-top: 10px;" value="Listing by Date" name="type">
				<input id="sid" class="input-mini" type="text" readonly="readonly" style="text-align: center; margin-top: 10px; margin-left: 10px;" value="'.$sectionId.'" name="jform[request][sid]">
				</div>
				</li>';
				$html .= '</ul>';
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
				$options = '{"serverUse":{},"remoteUse":{"sectionID":"'.$_POST['spsection'].'","categoryID":"'.$_POST['selectedCat'].'","entryID":"'.$_POST['selectedEntry'].'","pageLayout":"'.$pagelayout.'","featuredFirst":"'.$featuredFirst.'"}}';
				break;
			
			case 'addentryField':
				$sectionID = $_POST['spsection'];
				$pagelayout = $menuoptions['pagelayout'];
				$options = '{"serverUse":{},"remoteUse":{"sectionID":"'.$sectionID.'","pageLayout":"'.$pagelayout.'"}}';
				break;
				
			case 'getDateListing':
				$Date=$_POST['sp_year'].".".$_POST['sp_month'].".".$_POST['sp_day'];
				$sectionID = $_POST['spsection'];
				$pagelayout = $menuoptions['pagelayout'];
				$options = '{"serverUse":{},"remoteUse":{"sectionID":"'.$sectionID.'","date":"'.$Date.'","pageLayout":"'.$pagelayout.'"}}';
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
