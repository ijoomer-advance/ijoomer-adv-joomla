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

defined('_JEXEC') or die;

class default_menu {
	public function getRequiredInput($extension,$extTask,$menuoptions){
		$menuoptions = json_decode($menuoptions,true);
		switch ($extTask){
			case 'web':
				$html = '<fieldset class="panelform">
							<label title="" class="hasTip required" for="jform_request_url" id="jform_request_url-lbl" aria-invalid="false">URL :
								<span class="star">&nbsp;*</span>
							</label>';

				$html .= '<input type="text" name="jform[request][url]" id="jform_request_url" value="'.$menuoptions['remoteUse']['url'].'" class="required">';
				break;

			case 'custom':
				$str = '';
				if(isset($menuoptions['remoteUse'])){
					$str = json_encode($menuoptions['remoteUse']);
				}
				$customactivityname = JText::_('COM_IJOOMERADV_CUSTOM_ACTIVITY_NAME');
				$html = '<fieldset class="panelform">
							<label title="" class="hasTip required" for="jform_request_actname" id="jform_request_actname-lbl" aria-invalid="false">'.$customactivityname.'
								<span class="star">&nbsp;*</span>
							</label>';

				$html .= '<input type="text" name="jform[request][actname]" id="jform_request_actname" value="'.$menuoptions['remotetask'].'" class="required">';

				$customactivityparam = JText::_('COM_IJOOMERADV_CUSTOM_ACTIVITY_PARAMS');
				$html .= '<label title="" for="jform_request_actparam" id="jform_request_actparam-lbl" aria-invalid="false">'.$customactivityparam.'
								<span class="star">&nbsp;*</span>
							</label>';

				$html .= '<input type="text" name="jform[request][actparam]" id="jform_request_actparam" value="'.htmlentities($str).'">';

				$html .= '<div style="float:right">Please enter param in JSON object';
				$html .= '<div>For EX.</div>';
				$html .= '<div>
<pre>{
	"id":1
}</pre>					</div></div>';

				break;

			case 'youtubePlaylist':
				$html = '<label title="" for="jform_request_youtubeuser" id="jform_request_youtubeuser-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_YOUTUBE_USERNAME').'
								<span class="star">&nbsp;*</span>
							</label>';

				$html .= '<input type="text" name="jform[request][youtubeuser]" id="jform_request_youtubeuser" value="'.$menuoptions['remoteUse']['username'].'">';
				break;

			case 'contactUs':
				$ID = (isset($menuoptions['remoteUse']['id']))? $menuoptions['remoteUse']['id'] : 0;
				$db =JFactory::getDbo();
				$sql = "SELECT c.name
    					FROM #__contact_details as c
    					WHERE c.id=".$ID;
				$db->setQuery($sql);
				$contactName = $db->loadResult();
				$script = array();

				$script[] = '	function jSelectChart_jform_request_id(id, name, object) {';
				$script[] = '		document.id("jform_request_id_id").value = id;';
				$script[] = '		document.id("jform_request_id_name").value = name;';
				$script[] = '		SqueezeBox.close();';
				$script[] = '	}';

				// Add the script to the document head.
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
				$html='
				<fieldset class="panelform">
					<legend>Recommonded Options</legend>
					<ul class="adminformlist">
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showcontact" id="jform_request_contact_showcontact-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_CONTACT').'
							</label>
							<div class="fltlft">
							<input id="jform_request_id_name" type="text" disabled="disabled" value="'.$contactName.'">
							</div>
							<div class="button2-left"><div class="blank">
							<a class="modal" rel="{handler: \'iframe\', size: {x: 800, y: 450}}" href="index.php?option=com_contact&view=contacts&layout=modal&tmpl=component&function=jSelectChart_jform_request_id">'.JText::_('COM_IJOOMERADV_CONTACT_CHANGE_CONTACT').'
							</a>
							</div></div>
							<input id="jform_request_id_id" class="required modal-value" type="hidden" value="'.$ID.'" name="jform[request][id]" aria-required="true" required="required">
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_subjectLine" id="jform_request_contact_subjectLine-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SUBJECT_LINE').'
							<br/><small>(Comma Saparated)</small>
							</label>
							<textarea name="jform[request][subjectLine]" id="jform_request_contact_subjectLine">'.$menuoptions['remoteUse']['subjectLine'].'</textarea>
						</li>

					</ul>
				</fieldset>

				<fieldset class="panelform">
					<legend>Display Options</legend>
					<ul class="adminformlist">
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showName" id="jform_request_contact_showName-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_NAME').'
							</label>
							<select name="jform[request][showName]" id="jform_request_contact_showName">
								'.$this->getOptions($menuoptions['serverUse']['showName']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showPosition" id="jform_request_contact_showPosition-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_POSITION').'
							</label>
							<select name="jform[request][showPosition]" id="jform_request_contact_showPosition">
								'.$this->getOptions($menuoptions['serverUse']['showPosition']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showEmail" id="jform_request_contact_showEmail-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_EMAIL').'
							</label>
							<select name="jform[request][showEmail]" id="jform_request_contact_showEmail">
								'.$this->getOptions($menuoptions['serverUse']['showEmail']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showStreet" id="jform_request_contact_showStreet-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_STREET').'
							</label>
							<select name="jform[request][showStreet]" id="jform_request_contact_showStreet">
								'.$this->getOptions($menuoptions['serverUse']['showStreet']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showCity" id="jform_request_contact_showCity-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_CITY').'
							</label>
							<select name="jform[request][showCity]" id="jform_request_contact_showCity">
								'.$this->getOptions($menuoptions['serverUse']['showCity']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showState" id="jform_request_contact_showState-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_STATE').'
							</label>
							<select name="jform[request][showState]" id="jform_request_contact_showState">
								'.$this->getOptions($menuoptions['serverUse']['showState']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showPostalCode" id="jform_request_contact_showPostalCode-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_POSTAL_CODE').'
							</label>
							<select name="jform[request][showPostalCode]" id="jform_request_contact_showPostalCode">
								'.$this->getOptions($menuoptions['serverUse']['showPostalCode']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showCountry" id="jform_request_contact_showCountry-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_COUNTRY').'
							</label>
							<select name="jform[request][showCountry]" id="jform_request_contact_showCountry">
								'.$this->getOptions($menuoptions['serverUse']['showCountry']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showTelephone" id="jform_request_contact_showTelephone-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_TELEPHONE').'
							</label>
							<select name="jform[request][showTelephone]" id="jform_request_contact_showTelephone">
								'.$this->getOptions($menuoptions['serverUse']['showTelephone']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showMobile" id="jform_request_contact_showMobile-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_MOBILE').'
							</label>
							<select name="jform[request][showMobile]" id="jform_request_contact_showMobile">
								'.$this->getOptions($menuoptions['serverUse']['showMobile']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showFax" id="jform_request_contact_showFax-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_FAX').'
							</label>
							<select name="jform[request][showFax]" id="jform_request_contact_showFax">
								'.$this->getOptions($menuoptions['serverUse']['showFax']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showWebpage" id="jform_request_contact_showWebpage-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_WEBPAGE').'
							</label>
							<select name="jform[request][showWebpage]" id="jform_request_contact_showWebpage">
								'.$this->getOptions($menuoptions['serverUse']['showWebpage']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showMiscInfo" id="jform_request_contact_showMiscInfo-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_MISC_INFO').'
							</label>
							<select name="jform[request][showMiscInfo]" id="jform_request_contact_showMiscInfo">
								'.$this->getOptions($menuoptions['serverUse']['showMiscInfo']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showImage" id="jform_request_contact_showImage-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_IMAGE').'
							</label>
							<select name="jform[request][showMiscImage]" id="jform_request_contact_showImage">
								'.$this->getOptions($menuoptions['serverUse']['showMiscImage']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showLinks" id="jform_request_contact_showLinks-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_LINKS').'
							</label>
							<select name="jform[request][showLinks]" id="jform_request_contact_showLinks">
								'.$this->getOptions($menuoptions['serverUse']['showLinks']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_LinkALable" id="jform_request_contact_LinkALable-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_LINKALABLE').'
							</label>
							<input type="text" name="jform[request][linkALable]" id="jform_request_contact_LinkALable" value="'.$menuoptions['serverUse']['linkALable'].'">
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_LinkBLable" id="jform_request_contact_LinkBLable-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_LINKBLABLE').'
							</label>
							<input type="text" name="jform[request][linkBLable]" id="jform_request_contact_LinkBLable" value="'.$menuoptions['serverUse']['linkBLable'].'">
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_LinkCLable" id="jform_request_contact_LinkCLable-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_LINKCLABLE').'
							</label>
							<input type="text" name="jform[request][linkCLable]" id="jform_request_contact_LinkCLable" value="'.$menuoptions['serverUse']['linkCLable'].'">
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_LinkDLable" id="jform_request_contact_LinkDLable-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_LINKDLABLE').'
							</label>
							<input type="text" name="jform[request][linkDLable]" id="jform_request_contact_LinkDLable" value="'.$menuoptions['serverUse']['linkDLable'].'">
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_LinkELable" id="jform_request_contact_LinkELable-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_LINKELABLE').'
							</label>
							<input type="text" name="jform[request][linkELable]" id="jform_request_contact_LinkELable" value="'.$menuoptions['serverUse']['linkELable'].'">
						</li>
					</ul>

				</fieldset>

				<fieldset class="panelform">
					<legend>Mail Options</legend>
					<ul class="adminformlist">
						<li>
							<label class="hasTip" title="" for="jform_request_contact_showContactForm" id="jform_request_contact_showContactForm-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SHOW_CONTACT_FORM').'
							</label>
							<select name="jform[request][showContactForm]" id="jform_request_contact_showContactForm">
								'.$this->getOptions($menuoptions['remoteUse']['showContactForm']).'
							</select>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_sendCopy" id="jform_request_contact_sendCopy-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_SEND_COPY').'
							</label>
							<input type="checkbox" name="jform[request][sendCopy]" id="jform_request_contact_sendCopy" ';
						$html.=($menuoptions['serverUse']['sendCopy']=='on')?'checked="checked"':"";
						$html.='>
						</li>
						<li>
							<label class="hasTip" title="" for="jform_request_contact_thankYouText" id="jform_request_contact_thankYouText-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_CONTACT_THANK_YOU_TEXT').'
							</label>
							<input type="text" name="jform[request][thankYouText]" id="jform_request_contact_thankYouText" value="'.$menuoptions['serverUse']['thankYouText'].'">
						</li>
					</ul>
				</fieldset>';
				break;
		}
		return $html;
	}

	public function setRequiredInput($extension,$extView,$extTask,$remoteTask,$menuoptions,$data){
		$db = &JFactory::getDBO();
		$options = null;

		switch ($extTask){
			case 'web':
				$url = $menuoptions['url'];
				$options = '{"serverUse":"","remoteUse":{"url":"'.$url.'"}}';
				break;

			case 'custom':
				$params = $menuoptions['actparam'];
				if(!$params){
					$params='""';
				}
				$options = '{"serverUse":"","remoteUse":'.$params.'}';
				break;

				case 'youtubePlaylist':
					$params = $menuoptions['youtubeuser'];
					$options = '{"serverUse":"","remoteUse":{"username":"'.$params.'"}}';
				break;
				case 'contactUs':
					$id =  $menuoptions['id'];
					$subjectLine =  $menuoptions['subjectLine'];
					$showName = $menuoptions['showName'];
					$showPosition =  $menuoptions['showPosition'];
					$showEmail =  $menuoptions['showEmail'];
					$showStreet =  $menuoptions['showStreet'];
					$showCity =  $menuoptions['showCity'];
					$showState =  $menuoptions['showState'];
					$showPostalCode =  $menuoptions['showPostalCode'];
					$showCountry =  $menuoptions['showCountry'];
					$showTelephone =  $menuoptions['showTelephone'];
					$showMobile =  $menuoptions['showMobile'];
					$showFax =  $menuoptions['showFax'];
					$showWebpage =  $menuoptions['showWebpage'];
					$showMiscInfo =  $menuoptions['showMiscInfo'];
					$showMiscImage =  $menuoptions['showMiscImage'];
					$showLinks =  $menuoptions['showLinks'];
					$linkALable =  $menuoptions['linkALable'];
					$linkBLable =  $menuoptions['linkBLable'];
					$linkCLable =  $menuoptions['linkCLable'];
					$linkDLable =  $menuoptions['linkDLable'];
					$linkELable =  $menuoptions['linkELable'];
					$showContactForm =  $menuoptions['showContactForm'];
					$sendCopy = $menuoptions['sendCopy'];
					$thankYouText =  $menuoptions['thankYouText'];
					$options = '{"serverUse":{"showName":"'.$showName.'","showPosition":"'.$showPosition.'","showEmail":"'.$showEmail.'","showStreet":"'.$showStreet.'","showCity":"'.$showCity.'","showState":"'.$showState.'","showPostalCode":"'.$showPostalCode.'","showCountry":"'.$showCountry.'","showTelephone":"'.$showTelephone.'","showMobile":"'.$showMobile.'","showFax":"'.$showFax.'","showWebpage":"'.$showWebpage.'","showMiscInfo":"'.$showMiscInfo.'","showMiscImage":"'.$showMiscImage.'","showLinks":"'.$showLinks.'","linkALable":"'.$linkALable.'","linkBLable":"'.$linkBLable.'","linkCLable":"'.$linkCLable.'","linkDLable":"'.$linkDLable.'","linkELable":"'.$linkELable.'","sendCopy":"'.$sendCopy.'","thankYouText":"'.$thankYouText.'"},"remoteUse":{"id":"'.$id.'","subjectLine":"'.$subjectLine.'","showContactForm":"'.$showContactForm.'"}}';
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

	function getOptions($selectedValue){
		if($selectedValue==1){
			$options='<option value="0">Hide</option><option value="1" selected="selected">Show</option>';
		}else{
			$options='<option value="0" selected="selected">Hide</option><option value="1">Show</option>';
		}
		return $options;
	}
}