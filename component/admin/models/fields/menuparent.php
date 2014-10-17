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
class JFormFieldMenuParent extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.0
	 */
	protected $type = 'MenuParent';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text')
			->from('#__ijoomeradv_menu', 'a');

		if ($menuType = $this->form->getValue('menutype'))
		{
			$query->where('a.menutype = ' . $db->q($menuType));
		}
		else
		{
			$query->where('a.menutype != ' . $db->q(''));
		}

		$query->where('a.published != -2')
			->group('a.id, a.title, a.menutype, a.published');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
