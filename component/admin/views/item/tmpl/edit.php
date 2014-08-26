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

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.framework');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.modal');
$app = JFactory::getApplication();

if(JRequest::getVar('ajax')){
	$db = JFactory::getDbo();
	$menuid = JRequest::getVar('menuid');
	$sql = 'SELECT 	position
			FROM #__ijoomeradv_menu_types
			WHERE id='.$menuid;
	
	$db->setQuery($sql);
	$position = $db->loadResult();
	if($position==1 or $position==2){
		$text = '<span style="font-weight: bold"><font color="red">Note:</font></span><br/>Please make sure the image size should be <font color="green">114x114 px</font>';
	}else{
		$text = '<span style="font-weight: bold"><font color="red">Note:</font></span><br/>Please make sure the image size should be <font color="green">64x64 px</font>';
	}
	$response['text']=$text;
	$response['position']=$position;
	echo json_encode($response);
	exit;
}
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task, type)
	{
		if (task == 'setType' || task == 'setMenuType') {
			if(task == 'setType') {
				document.id('item-form').elements['jform[type]'].value = type;
				document.id('fieldtype').value = 'type';
			} else {
				document.id('item-form').elements['jform[menutype]'].value = type;
			}
			Joomla.submitform('setType', document.id('item-form'));
		} else if (task == 'cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.id('item-form'));
		} else {
			// special case for modal popups validation response
			$$('#item-form .modal-value.invalid').each(function(field){
				var idReversed = field.id.split("").reverse().join("");
				var separatorLocation = idReversed.indexOf('_');
				var name = idReversed.substr(separatorLocation).split("").reverse().join("")+'name';
				document.id(name).addClass('invalid');
			});
		}
	}

	window.onload = function(){
		changeimage();
	};

	changeimage = function(){
		var xmlhttp;
		var menuid = document.id('jform_menutype').value;
		if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
	  		xmlhttp=new XMLHttpRequest();
	  	}else{// code for IE6, IE5
	  		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  	}
		xmlhttp.onreadystatechange=function(){
	 		if (xmlhttp.readyState==4 && xmlhttp.status==200){
		 		var responseobj = JSON.parse(xmlhttp.response);
		 		var position = responseobj['position'];
	 			document.getElementById('imagedescnote').innerHTML = responseobj['text'];
	 			if(position==1 || position==2){
	 				document.getElementById('imagetab').style.display = 'none';
	 				document.getElementById('imagetabactive').style.display = 'none';
	 				document.getElementById('imageicon').style.display = 'inline';
	 			}else{
	 				document.getElementById('imageicon').style.display = 'none';
	 				document.getElementById('imagetab').style.display = 'inline';
	 				document.getElementById('imagetabactive').style.display = 'inline';
			 	}
		    }
		}
		xmlhttp.open("GET","index.php?option=com_ijoomeradv&view=item&layout=edit&ajax=1&menuid="+menuid,true);
		xmlhttp.send();	
	}
	
</script>

<form action="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=item&layout=edit&id='.(int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="item-form" class="form-validate">

<div class="width-60 fltlft">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_IJOOMERADV_ITEM_DETAILS');?></legend>
			<ul class="adminformlist">

				<li><?php echo $this->form->getLabel('type'); ?>
				<?php echo $this->form->getInput('type'); ?></li>

				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li><?php echo $this->form->getLabel('menutype'); ?>
				<select size="1" class="inputbox" name="jform[menutype]" id="jform_menutype" aria-invalid="false" onchange="changeimage()">
				<?php 
					foreach ($this->menutypes as $key=>$value){
						$selected = ($value->checked)?'selected="selected"':'';
						echo '<option '.$selected.' value="'.$value->id.'">'.$value->title.'</option>';
					}
				?></select>
				</li>
				
				<?php if ($this->item->type =='url'): ?>
					<?php $this->form->setFieldAttribute('link', 'readonly', 'false');?>
					<li><?php echo $this->form->getLabel('link'); ?>
					<?php echo $this->form->getInput('link'); ?></li>
				<?php endif; ?>
				
				<li><?php echo $this->form->getLabel('menudevice'); ?>
				<?php echo $this->form->getInput('menudevice'); ?></li>

				<li><?php echo $this->form->getLabel('note'); ?>
				<?php echo $this->form->getInput('note'); ?></li>

				<li><?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?></li>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>

				<li><?php echo $this->form->getLabel('views'); ?>
				<?php echo $this->form->getInput('views'); ?></li>
				
				<li><?php echo $this->form->getLabel('home'); ?>
				<?php echo $this->form->getInput('home'); ?></li>
				
				<li><?php echo $this->form->getLabel('requiredField'); ?>
				<?php echo $this->form->getInput('requiredField'); ?></li>
				
				<div id="imageicon">
					<li><label title="" class="hasTip" for="jform_image_icon" id="jform_image_icon-lbl" aria-invalid="false">Icon Image</label>				
					<input type="file" value="" id="jform_image_icon" name="jform[imageicon]" class="" aria-invalid="false"></li>
				</div>
				
				<div id="imagetab">
					<li><label title="" class="hasTip" for="jform_image_tab" id="jform_image_tab-lbl" aria-invalid="false">Tab Image</label>				
					<input type="file" value="" id="jform_image_tab" name="jform[imagetab]" class="" aria-invalid="false"></li>
				</div>
				
				<div id="imagetabactive">
					<li><label title="" class="hasTip" for="jform_image_tab_active" id="jform_image_tab_active-lbl" aria-invalid="false">Tab Active Image</label>				
					<input type="file" value="" id="jform_image_tab_active" name="jform[imagetabactive]" class="" aria-invalid="false"></li>
				</div>
				
				<div style="clear:both;float:left;padding-left:25%;" id="imagedescnote">
					<span style="font-weight: bold"><font color="red">Note:</font></span><br/>Please make sure the image size should be <font color="green">114x114 px</font>
				</div>
		</ul>

	</fieldset>
</div>
<?php if($this->form->getValue('requiredField') == 1){ ?>
<div class="width-40 fltrt">
	<?php echo JHtml::_('sliders.start', 'menu-sliders-'.$this->item->id); ?>

		<div class="clr"></div>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_IJOOMERADV_ITEM_EXTRA_PARAMS_ASSIGNMENT'), 'module-options'); ?>
			<fieldset>
				<?php 
					if($this->form->getValue('views')){
						$view = explode('.',$this->form->getValue('views'));
						$extension	 = $view[0];
						$extView	 = $view[2];
						$menuoptions = $this->form->getValue('menuoptions');
						if($extView == 'custom'){
							$menuoptions = json_decode($menuoptions,true);
							if($this->form->getValue('id')){
								$menuoptions['remotetask'] = $view[3];
							}
							$menuoptions = json_encode($menuoptions);
						}
						
						if($extension != 'default'){
							require_once (JPATH_SITE.DS.'components'.DS.'com_ijoomeradv'.DS.'extensions'.DS.$extension.DS.$extension.'.php');
						}else{
							require_once (JPATH_SITE.DS.'components'.DS.'com_ijoomeradv'.DS.'extensions'.DS.$extension.'.php');
						}
						
						$extClass	= $extension.'_menu';
						$extClass 	= new $extClass();
						echo $extClass->getRequiredInput($extension,$extView,$menuoptions);
					}
				?>
			</fieldset>

	<?php echo JHtml::_('sliders.end'); ?>

</div>
<?php }?>
<div class="width-40 fltrt">
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('component_id'); ?>
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" id="fieldtype" name="fieldtype" value="" />
	<input type="hidden" id="extData" name="extData" value="<?php echo $app->getUserState('com_ijoomeradv.edit.item.data');?>" />
</div>
</form>
