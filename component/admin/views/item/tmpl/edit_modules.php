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

JHtml::_('behavior.framework', true);
?>
	<script type="text/javascript">
		// Hide/show all rows which are not assigned.
		window.addEvent('domready', function() {
			document.id('showmods').addEvent('click', function(e) {
				$$('.adminlist tr.no').toggle();
			});
		});
	</script>

	<label style="margin-right: 5px;" for="showmods"><?php echo JText::_('COM_IJOOMERADV_ITEM_FIELD_HIDE_UNASSIGNED');?></label>
	<input type="checkbox" id="showmods" />
	<table class="adminlist">
		<thead>
		<tr>
			<th class="left">
				<?php echo JText::_('COM_IJOOMERADV_HEADING_ASSIGN_MODULE');?>
			</th>
			<th>
				<?php echo JText::_('COM_IJOOMERADV_HEADING_DISPLAY');?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->modules as $i => &$module) : ?>
 			<?php if (is_null($module->menuid)) : ?>
				<?php if(!$module->except || $module->menuid < 0) : ?>
					<tr class="no row<?php echo $i % 2;?>">
				<?php else : ?>
			<tr class="row<?php echo $i % 2;?>">
				<?php endif; ?>
			<?php endif; ?>
				<td>
					<?php $link = 'index.php?option=com_modules&amp;client_id=0&amp;task=module.edit&amp;id='. $module->id.'&amp;tmpl=component&amp;view=module&amp;layout=modal' ; ?>
					<a class="modal" href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 900, y: 550}}" title="<?php echo JText::_('COM_IJOOMERADV_EDIT_MODULE_SETTINGS');?>">
						<?php echo JText::sprintf('COM_IJOOMERADV_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></a>
				</td>
				<td class="center">
					<?php if (is_null($module->menuid)) : ?>
						<?php if ($module->except):?>
							<?php echo JText::_('JYES'); ?>
						<?php else : ?>
							<?php echo JText::_('JNO'); ?>
						<?php endif;?>
					<?php elseif ($module->menuid > 0) : ?>
						<?php echo JText::_('JYES'); ?>
					<?php elseif ($module->menuid < 0) : ?>
						<?php echo JText::_('JNO'); ?>
					<?php else : ?>
						<?php echo JText::_('JALL'); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
