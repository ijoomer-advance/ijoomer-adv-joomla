<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.framework');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.modal');

$document       = JFactory::getDocument();
$document->addscript(JURI::root() . "media/system/js/tabs-state.js");
$app            = JFactory::getApplication();
$this->item     = $this->get('Item');
$data           = $this->item->itemimage;
$dirpath        = JURI::root() . "administrator/components/com_ijoomeradv/theme/custom/android/xhdpi/";
$home           = $data . "_icon.png";
$tab_img        = $data . "_tab.png";
$active_tab_img = $data . "_tab_active.png";
$fieldsets      = $this->form->getFieldset('menus');

if (JRequest::getVar('ajax'))
{
	$db = JFactory::getDbo();
	$menuid = JRequest::getVar('menuid');
	$sql = 'SELECT 	position
			FROM #__ijoomeradv_menu_types
			WHERE id =' . $menuid;

	$db->setQuery($sql);
	$position = $db->loadResult();

	if ($position == 1 or $position == 2)
	{
		$text = '<span style="font-weight: bold"><font color="red">Note:</font></span><br/>Please make sure the image size should be <font color="green">114x114 px</font>';
	}
	else
	{
		$text = '<span style="font-weight: bold"><font color="red">Note:</font></span><br/>Please make sure the image size should be <font color="green">64x64 px</font>';
	}

	$response['text'] = $text;
	$response['position'] = $position;
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
	 			console.log(xmlhttp.response);
		 		var responseobj = JSON.parse(xmlhttp.response);
		 		var position = responseobj['position'];
	 			document.getElementById('imagedescnote').innerHTML = responseobj['text'];
	 			if(position==1 || position==2){
	 				document.getElementById('imagetab').style.display = 'none';
	 				document.getElementById('imagetabactive').style.display = 'none';
	 				document.getElementById('imageicon').style.display = 'table-row';
	 			}else{
	 				document.getElementById('imageicon').style.display = 'none';
	 				document.getElementById('imagetab').style.display = 'table-row';
	 				document.getElementById('imagetabactive').style.display = 'table-row';
			 	}
		    }
		}
		xmlhttp.open("GET","index.php?option=com_ijoomeradv&view=item&layout=edit&ajax=1&menuid="+menuid,true);
		xmlhttp.send();
	}

</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=item&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="item-form" class="form-validate">

	<div class="fltlft span12">
		<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('Menu', true)); ?>
			<fieldset class="adminform">
			<legend><?php echo JText::_('COM_IJOOMERADV_ITEM_DETAILS'); ?></legend>

				<div class="span9">
					<?php foreach ($fieldsets as $field) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php endforeach; ?>
							<?php
								if ( $this->item->type == 'url')
								{
									$this->form->setFieldAttribute('link', 'readonly', 'false');
									echo $this->form->getLabel('link');
									echo $this->form->getInput('link');
								}
							?>
							</div>
							<?php echo JHtml::_('bootstrap.endTab'); ?>

							<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'image', JText::_('Images', true)); ?>

							<div class="span10">
								<div class="Icon-Image" id="imageicon">
								<label title="" class="hasTip" for="jform_image_icon" id="jform_image_icon-lbl"
								           aria-invalid="false">Icon Image</label>
								<input type="file" value="" id="jform_image_icon" name="jform[imageicon]" class=""
								           aria-invalid="false">

									<?php

									if ($data)
									{
										echo "<img src='" . $dirpath . $home . "'>";
									}
									else
									{
										$dirpath = JURI::root() . "administrator/components/com_ijoomeradv/theme/no-image.jpeg";
										echo "<img src='" . $dirpath . "'>";
									}


									?>
								</div>

								<div class="Tab-Image" id="imagetab">
								<label title="" class="hasTip" for="jform_image_tab" id="jform_image_tab-lbl"
								           aria-invalid="false">Tab Image</label>
								<input type="file" value="" id="jform_image_tab" name="jform[imagetab]" class=""
								           aria-invalid="false">

									<?php

									if ($data)
									{
										echo "<img src='" . $dirpath . $tab_img . "'>";
									}
									else
									{
										$dirpath = JURI::root() . "administrator/components/com_ijoomeradv/theme/no-image.jpeg";
										echo "<img src='" . $dirpath . "'>";
									}

									?>
									</div>

								<div class = "Tab-Active-Image" id="imagetabactive">
								<label title="" class="hasTip" for="jform_image_tab_active"
								           id="jform_image_tab_active-lbl" aria-invalid="false">Tab Active Image</label>
								<input type="file" value="" id="jform_image_tab_active" name="jform[imagetabactive]"
								           class="" aria-invalid="false">
											<?php
											if ($data)
											{
												echo "<img src='" . $dirpath . $active_tab_img . "'>";
											}
											else
											{
												$dirpath = JURI::root() . "administrator/components/com_ijoomeradv/theme/no-image.jpeg";
												echo "<img src='" . $dirpath . "'>";
											}
											?>
								</div>

											<div class="notice" id="imagedescnote">
											<span style="font-weight: bold"><font
											color="red">Note:</font></span><br/>Please make sure the image size should
									be <font color="green">114x114 px</font></div>

						<?php if ($this->form->getValue('requiredField') == 1): ?>
							<div class="">
								<?php echo JHtml::_('sliders.start', 'menu-sliders-' . $this->item->id); ?>

								<div class="clr"></div>

								<?php echo JHtml::_('sliders.panel', JText::_('COM_IJOOMERADV_ITEM_EXTRA_PARAMS_ASSIGNMENT'), 'module-options'); ?>
								<fieldset>
									<?php
									if ($this->form->getValue('views'))
									{
										$view        = explode('.', $this->form->getValue('views'));
										$extension   = $view[0];
										$extView     = $view[2];
										$menuoptions = $this->form->getValue('menuoptions');

										if ($extView == 'custom')
										{
											$menuoptions = json_decode($menuoptions, true);

											if ($this->form->getValue('id'))
											{
												$menuoptions['remotetask'] = $view[3];
											}

											$menuoptions = json_encode($menuoptions);
										}

										if ($extension != 'default')
										{
											require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/' . $extension . '/' . $extension . '.php';
										}
										else
										{
											require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/' . $extension . '.php';
										}

										$extClass = $extension . '_menu';
										$extClass = new $extClass;
										echo $extClass->getRequiredInput($extension, $extView, $menuoptions);
									}
									?>
								</fieldset>
								<?php echo JHtml::_('sliders.end'); ?>
							</div>
						<?php endif; ?>
		</fieldset>
		</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>
	<div class="width-40 fltrt">
		<input type="hidden" name="task" value=""/>
		<?php echo $this->form->getInput('component_id'); ?>
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" id="fieldtype" name="fieldtype" value=""/>
		<input type="hidden" id="extData" name="extData" value="<?php echo $app->getUserState('com_ijoomeradv.edit.item.data'); ?>"/>
	</div>
</form>
