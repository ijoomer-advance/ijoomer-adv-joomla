<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.helper
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die;

JLoader::register('IjoomeradvHelper', JPATH_ADMINISTRATOR . '/components/com_ijoomeradv/helpers/menus.php');

/**
 * The Menu Item Helper
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.Helper
 * @since       1.0
 */
abstract class MenusHtmlMenus
{
	/**
	 * The Function For Association
	 *
	 * @param   int  $itemid  The menu item id
	 *
	 * @since  1.0
	 *
	 * @return it will return a value in false or JHtml
	 */
	static function association($itemid)
	{
		// Get the associations
		$associations = MenusHelper::getAssociations($itemid);

		// Get the associated menu items
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.*');
		$query->select('mt.title as menu_title');
		$query->from('#__ijmenu as m');
		$query->leftJoin('#__ijmenu_types as mt ON mt.menutype=m.menutype');
		$query->where('m.id IN (' . implode(',', array_values($associations)) . ')');
		$query->leftJoin('#__languages as l ON m.language=l.lang_code');
		$query->select('l.image');
		$query->select('l.title as language_title');
		$db->setQuery($query);
		$items = $db->loadObjectList('id');

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);

			return false;
		}

		// Construct html
		$text = array();

		foreach ($associations as $tag => $associated)
		{
			if ($associated != $itemid)
			{
				$text[] = JText::sprintf('COM_IJOOMERADV_TIP_ASSOCIATED_LANGUAGE', JHtml::_('image', 'mod_languages/' . $items[$associated]->image . '.gif', $items[$associated]->language_title, array('title' => $items[$associated]->language_title), true), $items[$associated]->title, $items[$associated]->menu_title);
			}
		}

		return JHtml::_('tooltip', implode('<br />', $text), JText::_('COM_IJOOMERADV_TIP_ASSOCIATION'), 'menu/icon-16-links.png');
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param   integer  $value     The state value.
	 * @param   integer  $i         The row index
	 * @param   boolean  $enabled   An optional setting for access control on the action.
	 * @param   string   $checkbox  An optional prefix for checkboxes.
	 *
	 * @return  string        The Html code
	 *
	 * @see     JHtmlJGrid::state
	 *
	 * @since   1.0
	 */
	public static function state($value, $i, $enabled = true, $checkbox = 'cb')
	{
		$states = array(
			7 => array(
				'unpublish',
				'',
				'COM_IJOOMERADV_HTML_UNPUBLISH_SEPARATOR',
				'',
				false,
				'publish',
				'publish'
			),
			6 => array(
				'publish',
				'',
				'COM_IJOOMERADV_HTML_PUBLISH_SEPARATOR',
				'',
				false,
				'unpublish',
				'unpublish'
			),
			5 => array(
				'unpublish',
				'',
				'COM_IJOOMERADV_HTML_UNPUBLISH_ALIAS',
				'',
				false,
				'publish',
				'publish'
			),
			4 => array(
				'publish',
				'',
				'COM_IJOOMERADV_HTML_PUBLISH_ALIAS',
				'',
				false,
				'unpublish',
				'unpublish'
			),
			3 => array(
				'unpublish',
				'',
				'COM_IJOOMERADV_HTML_UNPUBLISH_URL',
				'',
				false,
				'publish',
				'publish'
			),
			2 => array(
				'publish',
				'',
				'COM_IJOOMERADV_HTML_PUBLISH_URL',
				'',
				false,
				'unpublish',
				'unpublish'
			),
			1 => array(
				'unpublish',
				'COM_IJOOMERADV_EXTENSION_PUBLISHED_ENABLED',
				'COM_IJOOMERADV_HTML_UNPUBLISH_ENABLED',
				'COM_IJOOMERADV_EXTENSION_PUBLISHED_ENABLED',
				true,
				'publish',
				'publish'
			),
			0 => array(
				'publish',
				'COM_IJOOMERADV_EXTENSION_UNPUBLISHED_ENABLED',
				'COM_IJOOMERADV_HTML_PUBLISH_ENABLED',
				'COM_IJOOMERADV_EXTENSION_UNPUBLISHED_ENABLED',
				true,
				'unpublish',
				'unpublish'
			),
			-1 => array(
				'unpublish',
				'COM_IJOOMERADV_EXTENSION_PUBLISHED_DISABLED',
				'COM_IJOOMERADV_HTML_UNPUBLISH_DISABLED',
				'COM_IJOOMERADV_EXTENSION_PUBLISHED_DISABLED',
				true,
				'warning',
				'warning'
			),
			-2 => array(
				'publish',
				'COM_IJOOMERADV_EXTENSION_UNPUBLISHED_DISABLED',
				'COM_IJOOMERADV_HTML_PUBLISH_DISABLED',
				'COM_IJOOMERADV_EXTENSION_UNPUBLISHED_DISABLED',
				true,
				'unpublish',
				'unpublish'
			),
		);

		return JHtml::_('jgrid.state', $states, $value, $i, 'items.', $enabled, true, $checkbox);
	}
}
