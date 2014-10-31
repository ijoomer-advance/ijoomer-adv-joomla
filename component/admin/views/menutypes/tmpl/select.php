<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

?>
<script type="text/javascript">
	setmenutype = function () {
		var lstGroups = document.getElementsByName('screen[]');
		var selectedMenu = '{"result":[{';
		for (i = 0, k = 0; i < lstGroups.length; i++) {
			if (lstGroups[i].checked == true) {
				selectedMenu += '"' + k + '":"' + lstGroups[i].value + '",';
				k++;
			}
		}
		var strLen   = selectedMenu.length;
		selectedMenu = selectedMenu.slice(0, strLen - 1);
		selectedMenu += '}]}';

		window.parent.document.forms[0].elements['jform[screen]'].value = selectedMenu;
		window.parent.Joomla.submitbutton('setType');
		window.parent.SqueezeBox.close();
	}
</script>
<form name="menuselect" method="post" action="index.php?option=com_ijoomeradv&view=menu&layout=edit">
	<div class="fltrt">
		<button type="button" onclick="javascript:setmenutype();"><?php echo JText::_('IJSAVE') ?></button>
		<button type="button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('IJCANCEL') ?></button>
	</div>

	<?php echo JHtml::_('bootstrap.startAccordion', 'collapseTypes', array('active' => 'slide1')); ?>
	<h2 class="modal-title"><?php echo JText::_('COM_IJOOMERADV_TYPE_CHOOSE'); ?></h2>

	<?php
	$i = 0;

	foreach ($this->types as $name => $list):
	?>
		<?php echo JHtml::_('bootstrap.addSlide', 'collapseTypes', JText::_($name), 'collapse' . ($i++)); ?>
		<ul class="nav nav-tabs nav-stacked">
			<?php
				foreach ($list as $item):
			?>
				<li>
					<input type="checkbox" <?php if ($item->checked)
					{
						echo 'checked="checked"';
} ?>
					       id="<?php echo $name . '.' . $item->view; ?>"
					       value="<?php echo $name . '.' . $item->view . '.' . $item->task . '.' . $item->remoteTask; ?>"
					       name="screen[]" class="chkbox chk-menulink-2">&nbsp;-&nbsp;<?php echo $item->caption; ?>
				</li>
			<?php
				endforeach;
			?>
		</ul>
		<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php
	endforeach;
	?>
	<!-- </ul> -->
	<?php echo JHtml::_('bootstrap.endAccordion'); ?>
	<input type="hidden" name="option" value="com_ijoomeradv"/>
	<input type="hidden" name="view" value="menu"/>
	<input type="hidden" name="task" value="setType"/>
	<input type="hidden" name="boxchecked" value=""/>
</form>
