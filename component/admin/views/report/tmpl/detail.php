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
?>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm"
      enctype="multipart/form-data">
	<table cellspacing="1" class="adminlist table table-striped" style="border: 1px solid #CCCCCC;">
		<thead>
		<tr>
			<th width="1%">
				<?php echo JText::_('COM_IJOOMERADV_REPORT_NUMBER'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('COM_IJOOMERADV_REPORT_MESSAGE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('COM_IJOOMERADV_REPORT_CREATED_BY'); ?>
			</th>
			<th width="10%">
				<?php echo JText::_('COM_IJOOMERADV_REPORT_SUBMITTED_DATE'); ?>
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
					<?php echo $key + 1; ?>
				</td>
				<td class="center">
					<?php echo $value->message; ?>
				</td>
				<td class="center">
					<?php
					$user = JFactory::getUser($value->created_by);
					echo $user->name;
					?>
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
