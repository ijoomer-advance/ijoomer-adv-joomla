<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHTML::_('behavior.tooltip');

$document     = JFactory::getdocument();
$document->addscript(JURI::root() . 'media/com_ijoomeradv/js/jquery.js');
$document->addscript(JURI::root() . 'media/com_ijoomeradv/js/jquery.autocomplete.js');
$document->addstyleSheet(JURI::root() . 'media/com_ijoomeradv/css/jquery.autocomplete.css');
$document->addstyleSheet(JURI::root() . 'media/com_ijoomeradv/css/ijoomeradv.css');

$fieldsets    = $this->form->getFieldset('notification');
$msgfieldsets = $this->form->getFieldset('message');
?>
<script language="javascript" type="text/javascript">

jQuery(document).ready(function() {
	jQuery('#jform_customsuser_chzn').css('display', 'none');
	jQuery('#jform_customsuser-lbl').css('display', 'none');

	jQuery('#jform_customs0').click(function(event) {
		jQuery('#jform_customsuser_chzn').css('display', 'none');
		jQuery('#jform_customsuser-lbl').css('display', 'none');
	});
	jQuery('#jform_customs1').click(function(event) {
		jQuery('#jform_customsuser_chzn').css('display', 'block');
		jQuery('#jform_customsuser-lbl').css('display', 'block');
	});
});

</script>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm"
      enctype="multipart/form-data">
     <div class="form-horizontal">
			<div class="span5">
				<div>
					<?php foreach ($fieldsets as $field) : ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label; ?></div>
							<div class="controls"><?php echo $field->input; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div>
					<?php foreach ($msgfieldsets as $fields) : ?>
						<div class="control-group">
							<div class="control-label"><?php echo $fields->label; ?></div>
							<div class="controls"><?php echo $fields->input; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="span7">
				<table class="adminlist table table-striped" width="100%">
					<thead>
					<tr>
						<th ><?php echo JHtml::_('grid.checkall'); ?></th>
						<th ><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_ID') ?></th>
						<th ><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_DEVICE_TYPE') ?></th>
						<th ><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_TO_USER') ?></th>
						<th ><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_SEND_NOTIFICATION_TO_ALL_USERS') ?></th>
						<th><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_NOTIFICATION_TEXT') ?></th>
						<th ><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_TIME') ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					$k = 0;

					if (!empty($this->pushNotifications))
					{
						foreach ($this->pushNotifications as $key => $value):?>
							<tr class="row<?php echo $k; ?>">
								<td><?php echo JHtml::_('grid.id', $key, $value['id']); ?></td>
								<td><?php echo $value['id']; ?></td>
								<td><?php echo $value['device_type']; ?></td>
								<td><?php echo $value['to_user']; ?></td>
								<td><?php echo $value['to_all']; ?></td>
								<td><?php echo $value['message']; ?></td>
								<td><?php echo $value['time']; ?></td>
							</tr>
						<?php endforeach;

						$k = 1 - $k;
					}
					else
					{
						echo '<tr><td colspan="15" align="center">There is no data to show.</td></tr>';
					}
					?>
					</tbody>

					<tr><td colspan="15" align="center"></td></tr>
			</table>
		</div>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_ijoomeradv"/>
	<input type="hidden" name="view" value="pushnotif"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value=""/>
	<?php echo Jhtml::_('form.token');?>
</form>
