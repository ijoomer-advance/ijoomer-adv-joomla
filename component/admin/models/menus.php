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
 * The Class For IJoomeradvModelMenus which will Extends The JModelList
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.models
 * @since       1.0
 */
class IjoomeradvModelMenus extends JModelList
{
	/**
	 * Constructor.
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
				'title', 'a.title',
				'menutype', 'a.menutype',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Overrides the getItems method to attach additional metrics to the list.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.0
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId('getItems');

		// Try to load the data from internal storage.
		if (!empty($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$items = parent::getItems();

		// If emtpy or an error, just return.
		if (empty($items))
		{
			return array();
		}

		// Getting the following metric by joins is WAY TOO SLOW.
		// Faster to do three queries for very large menu trees.

		// Get the menu types of menus in the list.
		$db = $this->getDbo();
		$menuTypes = JArrayHelper::getColumn($items, 'id');

		// Quote the strings.
		$menuTypes = implode(
			',',
			array_map(array($db, 'quote'), $menuTypes)
		);

		foreach ($items as $key => $value)
		{
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('count(id) as countPublished')
				->from($db->qn('#__ijoomeradv_menu'))
				->where($db->qn('published') . ' = ' . $db->q('1'))
				->where($db->qn('menutype') . ' IN (' . $db->q($value->id) . ')');

			// Set the query and load the result.
			$db->setQuery($query);

			try
			{
				$count = $db->loadObject();

				$countPublished[$value->id] = $count->countPublished;

				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('count(id) as countUnpublished')
					->from($db->qn('#__ijoomeradv_menu'))
					->where($db->qn('published') . ' = ' . $db->q('0'))
					->where($db->qn('menutype') . ' IN (' . $db->q($value->id) . ')');

				$db->setQuery($query);

				$count = $db->loadObject();
				$countUnpublished[$value->id] = $count->countUnpublished;

				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('count(id) as countTrashed')
					->from($db->qn('#__ijoomeradv_menu'))
					->where($db->qn('published') . ' = ' . $db->q('-2'))
					->where($db->qn('menutype') . ' IN (' . $db->q($value->id) . ')');

				$db->setQuery($query);

				$count = $db->loadObject();
				$countTrashed[$value->id] = $count->countTrashed;

			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException($e->getMessage(), $e->getCode());
			}
		}

		// Inject the values back into the array.
		foreach ($items as $item)
		{
			$item->count_published = isset($countPublished[$item->id]) ? $countPublished[$item->id] : 0;
			$item->count_unpublished = isset($countUnpublished[$item->id]) ? $countUnpublished[$item->id] : 0;
			$item->count_trashed = isset($countTrashed[$item->id]) ? $countTrashed[$item->id] : 0;
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string  An SQL query
	 *
	 * @since   1.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select all fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->from($db->quoteName('#__ijoomeradv_menu_types') . ' AS a')
			->group('a.id, a.title, a.description');

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.id')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	/**
	 * Gets the extension id of the core mod_menu module.
	 *
	 * @return  integer
	 *
	 * @since   1.0
	 */
	public function getModMenuId()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('e.extension_id')
			->from('#__extensions AS e')
			->where('e.type = ' . $db->quote('module'))
			->where('e.element = ' . $db->quote('mod_menu'))
			->where('e.client_id = 0');

		$db->setQuery($query);

		try
		{
			return $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Gets a list of all mod_mainmenu modules and collates them by menutype
	 *
	 * @return  array
	 */
	public function &getModules()
	{
		$model = JModelLegacy::getInstance('Menu', 'IjoomeradvModel', array('ignore_request' => true));
		$result = $model->getModules();

		return $result;
	}
}
