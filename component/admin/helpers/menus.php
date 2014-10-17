<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.helper
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For IJoomerHelper
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.Helper
 * @since       1.0
 */
class IjoomeradvHelper
{
	/**
	 * Defines the valid request variables for the reverse lookup.
	 */
	protected static $_filter = array('option', 'view', 'layout');

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return void
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_IJOOMERADV_SUBMENU_MENUS'),
			'index.php?option=com_ijoomeradv&view=menus',
			$vName == 'menus'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_IJOOMERADV_SUBMENU_ITEMS'),
			'index.php?option=com_ijoomeradv&view=items',
			$vName == 'items'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  $parentId  The menu ID.
	 *
	 * @return  JObject
	 */
	public static function getActions($parentId = 0)
	{
		$user = JFactory::getUser();
		$result = new JObject;

		if (empty($parentId))
		{
			$assetName = 'com_ijoomeradv';
		}
		else
		{
			$assetName = 'com_ijoomeradv.item.' . (int) $parentId;
		}

		$actions = JAccess::getActions('com_ijoomeradv');

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Gets a standard form of a link for lookups.
	 *
	 * @param    mixed    A link string or array of request variables.
	 *
	 * @return    mixed    A link in standard option-view-layout form, or false if the supplied response is invalid.
	 */
	/**
	 * Gets a standard form of a link for lookups.
	 *
	 * @param   mixed  $request  A link string or array of request variables.
	 *
	 * @return  mixed  A link in standard option-view-layout form, or false if the supplied response is invalid.
	 */
	public static function getLinkKey($request)
	{
		if (empty($request))
		{
			return false;
		}

		// Check if the link is in the form of index.php?...
		if (is_string($request))
		{
			$args = array();

			if (strpos($request, 'index.php') === 0)
			{
				parse_str(parse_url(htmlspecialchars_decode($request), PHP_URL_QUERY), $args);
			}
			else
			{
				parse_str($request, $args);
			}

			$request = $args;
		}

		// Only take the option, view and layout parts.
		foreach ($request as $name => $value)
		{
			if (!in_array($name, self::$_filter))
			{
				// Remove the variables we want to ignore.
				unset($request[$name]);
			}
		}

		ksort($request);

		return 'index.php?' . http_build_query($request, '', '&');
	}

	/**
	 * Get the menu list for create a menu module
	 *
	 * @return  array  The menu array list.
	 */
	public static function getMenuTypes()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.menutype')
			->from($db->qn('#__ijoomeradv_menu_types', 'a'));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			return $db->loadColumn();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Get a list of menu links for one or all menus.
	 *
	 * @param   string   $menuType   An option menu to filter the list on, otherwise all menu links are returned as a grouped array.
	 * @param   integer  $parentId   An optional parent ID to pivot results around.
	 * @param   integer  $mode       An optional mode. If parent ID is set and mode=2, the parent and children are excluded from the list.
	 * @param   array    $published  An optional array of states
	 * @param   array    $languages  contains the value of language
	 *
	 * @return  boolean returns the falue in true or false.
	 */
	public static function getMenuLinks($menuType = null, $parentId = 0, $mode = 0, $published = array(), $languages = array())
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text, a.level, a.menutype, a.type, a.template_style_id, a.checked_out');
		$query->from('#__ijoomeradv_menu AS a');
		$query->join('LEFT', $db->qn('#__ijoomeradv_menu') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');

		// Filter by the type
		if ($menuType)
		{
			$query->where('(a.menutype = ' . $db->q($menuType) . ' OR a.parent_id = 0)');
		}

		if ($parentId)
		{
			if ($mode == 2)
			{
				// Prevent the parent and children from showing.
				$query->join('LEFT', '#__ijoomeradv_menu AS p ON p.id = ' . (int) $parentId);
				$query->where('(a.lft <= p.lft OR a.rgt >= p.rgt)');
			}
		}

		if (!empty($languages))
		{
			if (is_array($languages))
			{
				$languages = '(' . implode(',', array_map(array($db, 'q'), $languages)) . ')';
			}

			$query->where('a.language IN ' . $languages);
		}

		if (!empty($published))
		{
			if ( is_array($published))
			{
				$published = '(' . implode(',', $published) . ')';

				$query->where('a.published IN ' . $published);
			}
		}

		$query->where('a.published != -2');
		$query->group('a.id, a.title, a.level, a.menutype, a.type, a.template_style_id, a.checked_out, a.lft');
		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		$links = $db->loadObjectList();

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);

			return false;
		}

		// Pad the option text with spaces using depth level as a multiplier.
		foreach ($links as &$link)
		{
			$link->text = str_repeat('- ', $link->level) . $link->text;
		}

		if (empty($menuType))
		{
			// If the menutype is empty, group the items by menutype.
			$query->clear();
			$query->select('*');
			$query->from('#__ijoomeradv_menu_types');
			$query->where('menutype <> ' . $db->q(''));
			$query->order('title, menutype');
			$db->setQuery($query);

			$menuTypes = $db->loadObjectList();

			// Check for a database error.
			if ($error = $db->getErrorMsg())
			{
				JError::raiseWarning(500, $error);

				return false;
			}

			// Create a reverse lookup and aggregate the links.
			$rlu = array();

			foreach ($menuTypes as &$type)
			{
				$rlu[$type->menutype] = $type;
				$type->links = array();
			}

			// Loop through the list of menu links.
			foreach ($links as &$link)
			{
				if (isset($rlu[$link->menutype]))
				{
					$rlu[$link->menutype]->links[] = $link;

					// Cleanup garbage.
					unset($link->menutype);
				}
			}

			return $menuTypes;
		}
		else
		{
			return $links;
		}
	}

	/**
	 * The Function For Getting The Associations
	 *
	 * @param   [type]  $pk  contains the value of Pk
	 *
	 * @return  array $associations
	 */
	static public function getAssociations($pk)
	{
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->from('#__ijoomeradv_menu as m');
		$query->innerJoin('#__associations as a ON a.id=m.id AND a.context=' . $db->q('com_ijoomeradv.item'));
		$query->innerJoin('#__associations as a2 ON a.key=a2.key');
		$query->innerJoin('#__ijoomeradv_menu as m2 ON a2.id=m2.id');
		$query->where('m.id=' . (int) $pk);
		$query->select('m2.language, m2.id');
		$db->setQuery($query);
		$menuitems = $db->loadObjectList('language');

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);

			return false;
		}

		foreach ($menuitems as $tag => $item)
		{
			$associations[$tag] = $item->id;
		}

		return $associations;
	}
}
