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
		var lstGroups = document.getElementsByName('menuitems[]');
		var selectedMenu = '';
		for (i = 0, k = 0; i < lstGroups.length; i++) {
			if (lstGroups[i].checked == true) {
				selectedMenu += lstGroups[i].value + ',';
				k++;
			}
		}
		var strLen = selectedMenu.length;
		selectedMenu = selectedMenu.slice(0, strLen - 1);

		window.parent.document.forms[0].elements['jform[menuitem]'].value = selectedMenu;
		window.parent.SqueezeBox.close();
	}
</script>
<form name="menuselect">
	<div class="fltrt">
		<button type="button" onclick="javascript:setmenutype();"><?php echo JText::_('IJSAVE') ?></button>
		<button type="button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('IJCANCEL') ?></button>
	</div>

	<h2 class="modal-title"><?php echo JText::_('COM_IJOOMERADV_ITEM_CHOOSE'); ?></h2>
	<ul class="menu_types">
		<?php
			foreach ($this->menuitems as $name => $list):
		?>
			<li>
				<dl class="menu_type">
					<dt><?php echo JText::_('Menu : ' . $name); ?></dt>
					<dd>
						<ul>
							<?php
								foreach ($list as $item):
							?>
								<li class="menu-link">
									<input type="checkbox" <?php if ($item->checked)
									{
										echo 'checked="checked"';
}
							?>id="<?php echo $item->itemid; ?>" value=
							"<?php echo $item->itemid; ?>"
									       name="menuitems[]" class="chkbox chk-menulink-2">
									<label for="<?php echo $item->itemid; ?>">-
									<?php echo $item->itemtitle; ?></label>
								</li>
							<?php
								endforeach;
							?>
						</ul>
					</dd>
				</dl>
			</li>
		<?php
			endforeach;
		?>
	</ul>
</form>
