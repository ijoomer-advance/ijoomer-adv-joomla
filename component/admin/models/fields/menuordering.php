<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.models
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.models
 * @since       1.0
 */
class JFormFieldMenuOrdering extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.0
	 */
	protected $type = 'MenuOrdering';

	/**
	 * Method to get the list of siblings in a menu.
	 * The method requires that parent be set.
	 *
	 * @return  array  The field option objects or false if the parent field has not been set
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Get the parent
		$parent_id = $this->form->getValue('parent_id', 0);

		if (empty($parent_id))
		{
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text')
			->from('#__ijoomeradv_menu', 'a')
			->where($db->qn('a.published') . ' >= ' .  $db->q('0'))
			->where($db->qn('a.parent_id') . ' = ' . (int) $parent_id);

		if ($menuType = $this->form->getValue('menutype'))
		{
			$query->where('a.menutype = ' . $db->quote($menuType));
		}
		else
		{
			$query->where('a.menutype != ' . $db->quote(''));
		}

		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		$options = array_merge(
			array(array('value' => '-1', 'text' => JText::_('COM_IJOOMERADV_ITEM_FIELD_ORDERING_VALUE_FIRST'))),
			$options,
			array(array('value' => '-2', 'text' => JText::_('COM_IJOOMERADV_ITEM_FIELD_ORDERING_VALUE_LAST')))
		);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Method to get the field input markup
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		if ($this->form->getValue('id', 0) == 0)
		{
			return '<span class="readonly">' . JText::_('COM_IJOOMERADV_ITEM_FIELD_ORDERING_TEXT') . '</span>';
		}
		else
		{
			return parent::getInput();
		}
	}
}
