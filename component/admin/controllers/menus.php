<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.controller
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die;

/**
 * The Menu Item Controller
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.controller
 * @since       1.0
 */
class IjoomeradvControllerMenus extends JControllerLegacy
{
	/**
	 * The Home Function For Redirectiong To Home.
	 *
	 * @return  boolean  returns the link to Home.
	 */
	public function home()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv', true), null);
	}

	/**
	 * The Add Function
	 *
	 * @return boolean  returns the value in true or false
	 */
	function add()
	{
		$this->setRedirect('index.php?option=com_ijoomeradv&view=menu&layout=edit', null);
	}

	/**
	 * Edit Function
	 *
	 * @return  boolean  returns the value in true or false
	 */
	function edit()
	{
		$id = JFactory::getApplication()->input->getArray('cid', array());
		$this->setRedirect('index.php?option=com_ijoomeradv&view=menu&layout=edit&id=' . $id[0], null);
	}

	/**
	 * Get Model Function
	 *
	 * @param   string  $name    contains Name
	 * @param   string  $prefix  contains prefix
	 * @param   array   $config  contains config
	 *
	 * @return  the value of model
	 */
	public function getModel($name = 'Menu', $prefix = 'IjoomeradvModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Delete Function
	 *
	 * @return  void
	 */
	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->getArray('cid', array());

		if (!is_array($cid) || count($cid) < 1)
		{
			throw new RuntimeException(JText::_('COM_IJOOMERADV_NO_MENUS_SELECTED'), 500);
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if (!$model->delete($cid))
			{
				$this->setMessage($model->getError());
			}
			else
			{
				$this->setMessage(JText::plural('COM_IJOOMERADV_N_MENUS_DELETED', count($cid)));
			}
		}

		$this->setRedirect('index.php?option=com_ijoomeradv&view=menus');
	}

	/**
	 * Rebuild the menu tree.
	 *
	 * @return    bool    False on failure or error, true on success.
	 */
	public function rebuild()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_ijoomeradv&view=menus');

		// Initialise variables.
		$model = $this->getModel('Item');

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(JText::_('JTOOLBAR_REBUILD_SUCCESS'));

			return true;
		}

		else
		{
			// Rebuild failed.
			$this->setMessage(JText::sprintf('JTOOLBAR_REBUILD_FAILED', $model->getMessage()));

			return false;
		}
	}

	/**
	 * Temporary method. This should go into the 1.5 to 1.6 upgrade routines.
	 *
	 * @return  boolean  true or false value
	 */
	public function resync()
	{
		// Initialise variables.
		$db = JFactory::getDbo();
		$parts = null;

		// Load a lookup table of all the component id's.

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('element, extension_id')
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$components = $db->loadAssocList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		// Load all the component menu links
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('id, link, component_id')
			->from($db->qn('#__ijoomeradv_menu'))
			->where($db->qn('type') . ' = ' . $db->q('component'));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$items = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		foreach ($items as $item)
		{
			// Parse the link.
			parse_str(parse_url($item->link, PHP_URL_QUERY), $parts);

			// Tease out the option.
			if (isset($parts['option']))
			{
				$option = $parts['option'];

				// Lookup the component ID
				if (isset($components[$option]))
				{
					$componentId = $components[$option];
				}
				else
				{
					// Mismatch. Needs human intervention.
					$componentId = -1;
				}

				// Check for mis-matched component id's in the menu link.
				if ($item->component_id != $componentId)
				{
					// Update the menu table.
					$log = "Link $item->id refers to $item->component_id, converting to $componentId ($item->link)";
					echo "<br/>$log";

					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->qn('#__ijoomeradv_menu'))
						->set($db->qn('component_id') . ' = ' . $db->q($componentId))
						->where($db->qn('id') . ' = ' . $db->q($item->id));

					// Set the query and execute the update.
					$db->setQuery($query);

					try
					{
						$db->execute();
					}
					catch (RuntimeException $e)
					{
						throw new RuntimeException($e->getMessage(), $e->getCode());
					}
				}
			}
		}
	}
}
