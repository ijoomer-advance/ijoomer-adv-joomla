<?php
/**
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.extensions
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHTML::_('behavior.tooltip');

?>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
	<table width="100%">
		<tr>
			<td width="50%" valign="top">
				<?php
				$i = 0;
				foreach ($this->groups as $group)
				{
					if ($i == 3)
					{
						echo '<td valign="top">';
					}
					?>
					<fieldset>
						<legend><?php echo JText::_('COM_IJOOMERADV_ICMS_' . strtoupper($group) . '_CONFIG') ?></legend>
						<table style="text-align: left;" class="paramlist admintable">
							<?php
							foreach ($this->{$group . 'Config'} as $key => $value)
							{
								?>
								<tr>
									<td class="paramlist_key" width="40%">
											<span class="hasTip"
											      title="<?php echo JText::_($value->caption . '_LBL'); ?>::<?php echo JText::_($value->description); ?>">
												<?php echo JText::_($value->caption); ?>
											</span>
									</td>
									<td>
										<?php echo $value->html; ?>
									</td>
								</tr>
							<?php } ?>
						</table>
					</fieldset>
					<?php
					if ($i == 3)
					{
						echo '<td>';
					}
					$i++;
				}?>
			</td>
		</tr>
	</table>

	<div class="clr"></div>
	<input type="hidden" name="option" value="com_ijoomeradv"/>
	<input type="hidden" name="view" value="extensions"/>
	<input type="hidden" name="extid" value="<?php echo $this->extension->id; ?>"/>
	<input type="hidden" name="task" value=""/>
</form>