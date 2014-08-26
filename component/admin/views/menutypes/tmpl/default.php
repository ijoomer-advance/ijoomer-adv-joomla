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
	setmenutype = function(type){
		//window.parent.document.forms[0].elements['jform[type]'].value=type;
		window.parent.document.forms.namedItem('item-form').elements['jform[type]'];
		window.parent.Joomla.submitbutton('setType', type);
		window.parent.SqueezeBox.close();
	}
</script>

<h2 class="modal-title"><?php echo JText::_('COM_IJOOMERADV_TYPE_CHOOSE'); ?></h2>
<ul class="menu_types">
	<?php foreach ($this->types as $name => $list): ?>
	<li><dl class="menu_type">
			<dt><?php echo JText::_($name) ;?></dt>
			<dd><ul>
					<?php foreach ($list as $item): ?>
					<li><a class="choose_type" href="#" title="<?php echo JText::_($item->description); ?>"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'extension' =>$name,'caption' => $item->caption, 'task' => $item->task , 'view' => $item->view, 'remoteTask' => $item->remoteTask,'requiredField' =>$item->requiredField))); ?>')">
							<?php echo JText::_($item->caption);?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
			</dd>
		</dl>
	</li>
	<?php endforeach; ?>
</ul>
