<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.models
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For IJoomeradvModelItems which will Extends The JModelList
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.models
 * @since       1.0
 */
class IjoomeradvModelItems extends JModelList
{
	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		$config = null;

		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'menutype', 'a.menutype',
				'title', 'a.title',
				'published', 'a.published',
				'ordering', 'a.ordering'
			);

			if (JFactory::getApplication()->input->get('menu_associations', 0))
			{
				$config['filter_fields'][] = 'association';
			}
		}

		parent::__construct($config);
	}

	/**
	 * The Function For Getting The Menus
	 *
	 * @return  [array]  returns The loadobjectlist
	 */
	public function getMenus()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('id, title')
			->from($db->quoteName('#__ijoomeradv_menu_types'));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $result;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   [type]  $ordering   contains the value of ordering
	 * @param   [type]  $direction  contains the value of direction
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		$search = $this->getUserStateFromRequest($this->context . '.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$parentId = $this->getUserStateFromRequest($this->context . '.filter.parent_id', 'filter_parent_id', 0, 'int');
		$this->setState('filter.parent_id', $parentId);

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', 0, 'int');
		$this->setState('filter.level', $level);

		$menuType = JRequest::getVar('menutype', null);

		if ($menuType)
		{
			if ($menuType != $app->getUserState($this->context . '.filter.menutype'))
			{
				$app->setUserState($this->context . '.filter.menutype', $menuType);
				JRequest::setVar('limitstart', 0);
			}
		}
		else
		{
			$menuType = $app->getUserState($this->context . '.filter.menutype');

			if (!$menuType)
			{
				$menuType = $this->getDefaultMenuType();
			}
		}

		$this->setState('filter.menutype', $menuType);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Component parameters.
		$params = JComponentHelper::getParams('com_ijoomeradv');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.ordering', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  contains the value of id
	 *
	 * @return  string        A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.menutype');

		return parent::getStoreId($id);
	}

	/**
	 * Finds the default menu type.
	 *
	 * In the absence of better information, this is the first menu ordered by title.
	 *
	 * @return    string    The default menu type
	 *
	 * @since    1.0
	 */
	protected function getDefaultMenuType()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('menutype')
			->from($db->quoteName('#__ijoomeradv_menu_types'))
			->order($db->quoteName('title') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query, 0, 1);

		try
		{
			$menuType = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $menuType;
	}

	/**
	 * Builds an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery    A query object.
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();
		$app   = JFactory::getApplication();

		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$published = '= ' . (int) $published;
		}
		elseif ($published === '')
		{
			$published = 'IN (0, 1)';
		}
		else
		{
			$published = 'IN (0, 1, -2)';
		}

		// Select all fields from the table.
		$menutype = $this->getState('filter.menutype');

		if (is_numeric($menutype))
		{
			$sql = 'SELECT id FROM `#__ijoomeradv_menu_types` WHERE id=' . $menutype;
			$db->setQuery($sql);
			$menutypes = $db->loadResult();
			$menutype = 'AND a.menutype IN (' . $menutypes . ')';
		}
		elseif ($menutype === '' || $menutype === '*')
		{
			$menutype = $this->getMenus();
			$menutype = 'AND a.menutype IN (' . $menutype[0]->id . ')';
		}

		$where = " WHERE a.published $published
				   $menutype ";

		if ($search = trim($this->getState('filter.search')))
		{
			$where .= "AND a.title LIKE '%$search%' ";
		}

		$query = 'SELECT a.id, a.title, a.note, a.published as published,a.ordering,ag.title AS access_level
				  FROM `#__ijoomeradv_menu` AS a
				  LEFT JOIN #__viewlevels AS ag ON ag.id = a.access' .
			$where .
			'ORDER BY ' . $this->getState('list.ordering', 'menutype') . ' ' . $this->getState('list.direction', 'ASC');

		return $query;
	}
}
