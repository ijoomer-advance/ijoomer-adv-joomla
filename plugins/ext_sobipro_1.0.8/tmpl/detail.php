<?php

 /**
 * @copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
 * @license GNU/GPL, see license.txt or http://www.gnu.org/copyleft/gpl.html
 * Developed by Tailored Solutions - ijoomer.com
 *
 * ijoomer can be downloaded from www.ijoomer.com
 * ijoomer is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * You should have received a copy of the GNU General Public License
 * along with ijoomer; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');

?>
<form action="<?php echo JRoute::_ ( $this->request_url )?>" method="post" name="adminForm" id="adminForm">
	<table width="100%">
		<tr>
			<td width="50%" valign="top">
			<?php 
				$i=0;
				foreach ($this->groups as $group){
					if($i==4){
						echo '<td valign="top">';
					}
					?>
					<fieldset>
   					<legend><?php echo JText::_('COM_IJOOMERADV_SOBI_'.strtoupper($group).'_CONFIG')?></legend>
   						<table style="text-align: left;" class="paramlist admintable">
							<?php 
							 foreach($this->{$group.'Config'} as $key=>$value){?>
									<tr>
										<td class="paramlist_key" width="40%">
											<span class="hasTip" title="<?php echo $value->caption; ?>::<?php echo $value->description; ?>">
												<?php echo $value->caption; ?>
											</span>
										</td>
										<td><?php echo $value->html; ?></td>
									</tr>
							<?php } ?>
						</table>
					</fieldset>
					<?php
					if($i==4){
						echo '<td>';
					} 
					$i++;
				}?>
			</td>
		</tr>
	</table>
	
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_ijoomeradv" />
	<input type="hidden" name="view" value="extensions" />
	<input type="hidden" name="extid" value="<?php echo $this->extension->id; ?>" />
	<input type="hidden" name="task" value="" />
</form>