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

defined('_JEXEC') or die;

jimport('joomla.database.table');

/**
 * Menu Types table
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @since       11.1
 */
class JTableIjmenuType extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object.
	 *
	 * @since  11.1
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
	 * @since   11.1
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
		$query->select('COUNT(id)');
		$query->from($this->_db->quoteName('#__ijoomeradv_menu_types'));
		$query->where($this->_db->quoteName('menutype') . ' = ' . $this->_db->quote($this->menutype));
		$query->where($this->_db->quoteName('id') . ' <> ' . (int) $this->id);
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
	 * @since   11.1
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
			$query->delete();
			$query->from('#__ijoomeradv_menu');
			$query->where('id=' . $this->_db->quote($table->id));

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
