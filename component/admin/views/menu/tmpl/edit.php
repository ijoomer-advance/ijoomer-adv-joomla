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

defined( '_JEXEC' ) or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=menu&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form">
<div class="width-100 span12">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_IJOOMERADV_MENU_DETAILS');?></legend>
			<table class="table table-striped">
				<tr>
					<td><?php echo $this->form->getLabel('title'); ?></td>
					<td><?php echo $this->form->getInput('title'); ?></td>
				</tr>
				<tr>
					<td><?php echo $this->form->getLabel('description'); ?></td>
					<td><?php echo $this->form->getInput('description'); ?></td>
				</tr>
				<tr>
					<td><?php echo $this->form->getLabel('position'); ?></td>
					<td><?php echo $this->form->getInput('position'); ?></td>
				</tr>
				<tr>
					<td><?php echo $this->form->getLabel('screen'); ?></td>
					<td><?php echo $this->form->getInput('screen'); ?></td>
				</tr>
			</table>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<div class="clr"></div>