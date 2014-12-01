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
 * The Class For IJoomeradvTableMenu which will Extends JTable
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.table
 * @since       1.0
 */
class IjoomeradvTableMenu extends JTable
{
	/**
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer $pk       The primary key of the node to delete.
	 * @param   boolean $children True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     http://docs.joomla.org/JTableNested/delete
	 * @since   1.0
	 */

	var $id = null;

	var $title = null;

	var $note = null;

	var $type = null;

	var $published = 1;

	var $access = 1;

	var $views = null;

	var $requiredField = 0;

	var $itemimage = null;

	/**
	 * The Function For The IjoomeradvTableMenu
	 *
	 * @param   &  &$db  &$db
	 *
	 * @return void
	 */
	public function IjoomeradvTableMenu(& $db)
	{
		parent::__construct('#__ijoomeradv_menu', 'id', $db);
	}

	/**
	 * The Function For The Delete
	 *
	 * @param   [type]   $pk        $pk
	 * @param   boolean  $children  $children
	 *
	 * @return  returns the value
	 */
	public function delete($pk = null, $children = false)
	{
		return parent::delete($pk, $children);
	}

	/**
	 * Method to get the next ordering value for a group of rows defined by an SQL WHERE clause.
	 * This is useful for placing a new item last in a group of items in the table.
	 *
	 * @param   string  $where  WHERE clause to use for selecting the MAX(ordering) for the table.
	 *
	 * @return  mixed  Boolean false an failure or the next ordering value as an integer.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function getNextOrder($where = '')
	{
		$query = $this->_db->getQuery(true);

		// Create the base select statement.
		$query->select('max(ordering)')
			->from($this->_db->quoteName('#__ijoomeradv_menu'))
			->where($this->_db->quoteName('menutype') . ' = ' . $this->_db->quote($this->menutype));

		// Set the query and load the result.
		$this->_db->setQuery($query);

		try
		{
			$maxvalue = $this->_db->loadResult();

			return $maxvalue + 1;
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * The Function For The SaveOrder
	 *
	 * @param   [type]  $idArray    $idArray
	 * @param   [type]  $lft_array  $lft_array
	 *
	 * @return  boolean It will returns the value in true or false
	 */
	public function saveorder($idArray, $lft_array)
	{
		if (is_array($idArray) && is_array($lft_array) && count($idArray) == count($lft_array))
		{
			for ($i = 0, $count = count($idArray); $i < $count; $i++)
			{
				// Do an update to change the lft values in the table for each id
				$query = $this->_db->getQuery(true);

				$query->update($this->_tbl)
					->where($this->_tbl_key . ' = ' . (int) $idArray[$i])
					->set('ordering = ' . (int) $lft_array[$i]);

				$this->_db->setQuery($query);

				// Check for a database error.
				if (!$this->_db->execute())
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REORDER_FAILED', get_class($this), $this->_db->getErrorMsg()));
					$this->setError($e);

					return false;
				}
			}

			return true;
		}
		else
		{
			return false;
		}
	}
}
