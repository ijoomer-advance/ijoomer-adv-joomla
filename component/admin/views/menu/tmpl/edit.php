<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$fieldsets = $this->form->getFieldset('menudetail');
?>

<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>
<form
	action="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=menu&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="item-form">
	<div class="span12">
		<div class="form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_IJOOMERADV_MENU_DETAILS'); ?></legend>
				<div>
					<?php foreach ($fieldsets as $field) : ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label; ?></div>
							<div class="controls"><?php echo $field->input; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</fieldset>
		</div>
		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<div class="clr"></div>
