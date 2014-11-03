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
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm"
      enctype="multipart/form-data">
	<fieldset id="filter-bar">
		<div class="filter-select fltrt">
			<select name="extensiontype" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $this->extension, 'classname', 'name', $this->state->get('filter.extensiontype')); ?>
			</select>
		</div>
	</fieldset>

	<table class="adminlist" width="100%">
		<thead>
		<tr>
			<th width="1%">
				<input type="checkbox" name="checkall-toggle" value=""
				       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
			</th>
			<th class="title">
				<?php echo JText::_('COM_IJOOMERADV_REPORT_ITEM'); ?>
			</th>
			<th class="title" width="20%">
				<?php echo JText::_('COM_IJOOMERADV_REPORT_VIEW'); ?>
			</th>
			<th width="5%">
				<?php echo JHtml::_('grid.sort', 'COM_IJOOMERADV_REPORT_STATUS', 'status', $listDirn, $listOrder); ?>
			</th>
			<th class="title" width="20%">
				<?php echo JText::_('COM_IJOOMERADV_REPORT_ACTION'); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('grid.sort', 'COM_IJOOMERADV_REPORT_SUBMITTED_DATE', 'created', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($this->items as $key => $value)
		{
			$params = json_decode($value->params);
			?>
			<tr>
				<td class="center">
					<?php echo JHtml::_('grid.id', $key, $value->id); ?>
				</td>
				<td class="center">
					<?php echo (isset($params->content->file)) ? '<div>{voice}ijoomeradv_9344d.3gp&5{/voice}<div>' : ''; ?>
				</td>
				<td class="center">
					<a href="<?php echo JRoute::_($this->request_url) . '&layout=detail&cid=' . $value->id; ?>">Reports
						[<?php echo $value->itemcount; ?>]</a>
				</td>
				<td class="center">
					<?php

					if ($value->status == 0)
					{
						echo JText::_('COM_IJOOMERADV_REPORT_PENDING');
					}
					elseif ($value->status == 1)
					{
						echo JText::_('COM_IJOOMERADV_REPORT_DELETED');
					}
					else
					{
						echo JText::_('COM_IJOOMERADV_REPORT_IGNORED');
					}
					?>
				</td>
				<td class="center">
						<span>
							<a href="<?php echo JRoute::_($this->request_url) . '&task=action&action=deletereport&cid=' . $value->id; ?>">
							<?php echo JText::_('COM_IJOOMERADV_REPORT_DELETE_ACTION'); ?></a>
						</span>
					<?php echo ' | '; ?>
					<span>
							<a href="<?php echo JRoute::_($this->request_url) . '&task=action&action=ignore&cid=' . $value->id; ?>">
							<?php echo JText::_('COM_IJOOMERADV_REPORT_IGNORE_ACTION'); ?></a>
						</span>
				</td>
				<td class="center">
					<?php echo $value->created; ?>
				</td>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="com_ijoomeradv"/>
	<input type="hidden" name="view" value="report"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<input type="hidden" name="task" value=""/>
</form>
