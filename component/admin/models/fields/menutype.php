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

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_ijoomer
 * @since		1.6
 */
class JFormFieldMenutype extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'menutype';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialise variables.
		$html 		= array();
		$recordId	= (int) $this->form->getValue('id');
		$size		= ($v = $this->element['size']) ? ' size="'.$v.'"' : '';
		$class		= ($v = $this->element['class']) ? ' class="'.$v.'"' : 'class="text_area"';

		// Load the javascript and css
		JHtml::_('behavior.framework');
		JHtml::_('behavior.modal');

		$html[] = '<input type="text" readonly="readonly" disabled="disabled" value="'.$this->value.'"'.$size.$class.' />';
		$html[] = '<input type="button" value="'.JText::_('JSELECT').'" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\''.JRoute::_('index.php?option=com_ijoomeradv&view=menutypes&tmpl=component&recordId='.$recordId).'\'})" />';
		$html[] = '<input type="hidden" name="'.$this->name.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';

		return implode("\n", $html);
	}
}
