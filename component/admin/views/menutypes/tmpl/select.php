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
?>
<script type="text/javascript">
	setmenutype = function()
	{
		var lstGroups = document.getElementsByName('screen[]');
		var selectedMenu='{"result":[{';
		for (i = 0,k=0; i < lstGroups.length; i++) {
			if (lstGroups[i].checked == true) {
				selectedMenu += '"'+k+'":"'+lstGroups[i].value+'",';
				k++;
			}
		}
		var strLen = selectedMenu.length;
		selectedMenu = selectedMenu.slice(0,strLen-1);
		selectedMenu+='}]}';

		//window.parent.document.forms[0].elements['jform[screen]'].value=selectedMenu;
		window.parent.document.forms.namedItem('item-form').elements['jform[screen]'].value=selectedMenu;
		window.parent.Joomla.submitbutton('setType');
		window.parent.SqueezeBox.close();
	}
</script>
<form name="menuselect" method="post" action="index.php?option=com_ijoomeradv&view=menu&layout=edit">
<div class="fltrt">
	<button type="button" onclick="javascript:setmenutype();"><?php echo JText::_('IJSAVE') ?></button>
	<button type="button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('IJCANCEL') ?></button>
</div>

<h2 class="modal-title"><?php echo JText::_('COM_IJOOMERADV_TYPE_CHOOSE'); ?></h2>
<ul class="menu_types">
	<?php foreach ($this->types as $name => $list): ?>
	<li><dl class="menu_type">
			<dt><?php echo JText::_($name) ;?></dt>
			<dd><ul>
					<?php foreach ($list as $item): ?>
						<li class="menu-link">
							<input type="checkbox" <?php if($item->checked){echo 'checked="checked"';}?> id="<?php echo $name.'.'.$item->view; ?>" value="<?php echo $name.'.'.$item->view.'.'.$item->task.'.'.$item->remoteTask; ?>" name="screen[]" class="chkbox chk-menulink-2">
							<label for="<?php echo $name.'.'.$item->view; ?>">- <?php echo $item->caption; ?></label>
					 	</li>
					<?php endforeach; ?>
				</ul>
			</dd>
		</dl>
	</li>
	<?php endforeach; ?>
</ul>
<input type="hidden" name="option" value="com_ijoomeradv" />
<input type="hidden" name="view" value="menu" />
<input type="hidden" name="task" value="setType" />
<input type="hidden" name="boxchecked" value="" />
</form>