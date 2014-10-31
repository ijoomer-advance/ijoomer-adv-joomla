<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.tables
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For JTableIJMenuType which will Extends JTable
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.table
 * @since       1.0
 */
class JTableIjmenuType extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object.
	 *
	 * @since  1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__ijoomeradv_menu_types', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     JTable::check
	 * @since   1.0
	 */
	public function check()
	{
		$this->menutype = JApplication::stringURLSafe($this->menutype);

		if (empty($this->menutype))
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENUTYPE_EMPTY'));

			return false;
		}

		// Sanitise data.

		if (trim($this->title) == '')
		{
			$this->title = $this->menutype;
		}

		// Check for unique menutype.
		$query = $this->_db->getQuery(true);

		$query->select('COUNT(id)')
			->from($this->_db->qn('#__ijoomeradv_menu_types'))
			->where($this->_db->qn('menutype') . ' = ' . $this->_db->q($this->menutype))
			->where($this->_db->qn('id') . ' <> ' . (int) $this->id);

		$this->_db->setQuery($query);

		if ($this->_db->loadResult())
		{
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_MENUTYPE_EXISTS', $this->menutype));

			return false;
		}

		return true;
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/delete
	 * @since   1.0
	 */
	public function delete($pk = null)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk !== null)
		{
			// Get the user id
			$userId = JFactory::getUser()->id;

			// Get the old value of the table
			$table = JTable::getInstance('IjmenuType', 'JTable');
			$table->load($pk);

			// Delete the menu items
			$query = $this->_db->getQuery(true);
			$query->delete()
				->from('#__ijoomeradv_menu')
				->where('id=' . $this->_db->q($table->id));

			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));

				return false;
			}

			// Update the module items
			$query = $this->_db->getQuery(true);
			$query->delete();

			if (!$this->_db->execute())
			{
				$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));

				return false;
			}
		}

		return parent::delete($pk);
	}
}
