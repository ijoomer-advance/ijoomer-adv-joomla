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
		setmenutype = function (type) {
			window.parent.document.forms[0].elements['jform[type]'].value = type;
			window.parent.Joomla.submitbutton('setType', type);
			window.parent.SqueezeBox.close();
		}
	</script>

	<h2 class="modal-title"><?php echo JText::_('COM_IJOOMERADV_TYPE_CHOOSE'); ?></h2>
<?php echo JHtml::_('bootstrap.startAccordion', 'collapseTypes', array('active' => 'slide1')); ?>
<?php
$i = 1;

	foreach ($this->types as $name => $list):
?>
	<?php
		echo JHtml::_('bootstrap.addSlide', 'collapseTypes', JText::_($name), 'slide' . ($i++));
	?>
	<ul>
		<?php
			foreach ($list as $item):
		?>
			<li><a class="choose_type" href="#" title="<?php echo JText::_($item->description); ?>"
			       onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'extension' => $name, 'caption' => $item->caption, 'task' => $item->task, 'view' => $item->view, 'remoteTask' => $item->remoteTask, 'requiredField' => $item->requiredField))); ?>')">
					<?php echo JText::_($item->caption); ?>
				</a>
			</li>
		<?php
			endforeach;
		?>
	</ul>
	<?php echo JHtml::_('bootstrap.endSlide'); ?>
<?php
	endforeach;
?>
<?php
	echo JHtml::_('bootstrap.endAccordion');
