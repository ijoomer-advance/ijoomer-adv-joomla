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

JHTML::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
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
			foreach ($this->items as $key=>$value){
				$params = json_decode($value->params);
				?>
				<tr>
					<td class="center">
						<?php echo $key+1;?>
					</td>
					<td class="center">
						<?php echo $value->message;?>
					</td>
					<td class="center">
						<?php 
							$user = JFactory::getUser($value->created_by);
							echo $user->name;
						?>
					</td>
					<td class="center">
						<?php echo $value->created;?>
					</td>
				</tr>
			<?php 
			}
		?>
		</tbody>
	</table>
	
	<input type="hidden" name="option" value="com_ijoomeradv" />
	<input type="hidden" name="view" value="report" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<input type="hidden" name="task" value="" />
</form>