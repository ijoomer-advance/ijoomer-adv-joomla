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
<?php
$fieldSets = $this->form->getFieldsets('request');

if (!empty($fieldSets))
{
	$fieldSet = array_shift($fieldSets);
	$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_IJOOMERADV_' . $fieldSet->name . '_FIELDSET_LABEL';
	echo JHtml::_('sliders.panel', JText::_($label), 'request-options');

	if (isset($fieldSet->description) && trim($fieldSet->description))
	{
		echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	}
	?>
	<fieldset class="panelform">
		<?php $hidden_fields = ''; ?>
		<ul class="adminformlist">
			<?php
				foreach ($this->form->getFieldset('request') as $field)
				{
			?>
				<?php
					if (!$field->hidden)
					{
				?>
					<li>
						<?php echo $field->label; ?>
						<?php echo $field->input; ?>
					</li>
				<?php
					}
					else
					{
						$hidden_fields .= $field->input;
					}
				}
			?>
		</ul>
		<?php echo $hidden_fields; ?>
	</fieldset>
<?php
}

$fieldSets = $this->form->getFieldsets('params');

foreach ($fieldSets as $name => $fieldSet) :
	$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_IJOOMERADV_' . $name . '_FIELDSET_LABEL';
	echo JHtml::_('sliders.panel', JText::_($label), $name . '-options');

	if (isset($fieldSet->description) && trim($fieldSet->description))
	{
		echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	}
	?>
	<div class="clr"></div>
	<fieldset class="panelform">
		<ul class="adminformlist">
			<?php
				foreach ($this->form->getFieldset($name) as $field) :
			?>
				<li><?php echo $field->label; ?>
					<?php echo $field->input; ?></li>
			<?php
				endforeach;
			?>
		</ul>
	</fieldset>
<?php
endforeach;
?>
<?php

$fieldSets = $this->form->getFieldsets('associations');

foreach ($fieldSets as $name => $fieldSet) :
	$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_IJOOMERADV_' . $name . '_FIELDSET_LABEL';
	echo JHtml::_('sliders.panel', JText::_($label), $name . '-options');

	if (isset($fieldSet->description) && trim($fieldSet->description))
	{
		echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	}
	?>
	<div class="clr"></div>
	<fieldset class="panelform">
		<ul class="adminformlist">
			<?php
				foreach ($this->form->getFieldset($name) as $field) :
			?>
				<li><?php echo $field->label; ?>
					<?php echo $field->input; ?></li>
			<?php
				endforeach;
			?>
		</ul>
	</fieldset>
<?php
endforeach;
