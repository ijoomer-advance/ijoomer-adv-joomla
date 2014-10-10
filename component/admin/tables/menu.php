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
 * Menu Types table
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.table
 * @since       1.6
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
	 * @since   2.5
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

	function IjoomeradvTableMenu(& $db)
	{
		parent::__construct('#__ijoomeradv_menu', 'id', $db);
	}

	public function delete($pk = null, $children = false)
	{
		return parent::delete($pk, $children);
	}

	public function getNextOrder()
	{
		$sql = 'SELECT max(ordering)
				FROM #__ijoomeradv_menu
				WHERE menutype=' . $this->menutype;
		$this->_db->setQuery($sql);
		$maxvalue = $this->_db->loadResult();
		return $maxvalue + 1;
	}

	public function saveorder($idArray, $lft_array)
	{
		if (is_array($idArray) && is_array($lft_array) && count($idArray) == count($lft_array))
		{
			for ($i = 0, $count = count($idArray); $i < $count; $i++)
			{
				// Do an update to change the lft values in the table for each id
				$query = $this->_db->getQuery(true);
				$query->update($this->_tbl);
				$query->where($this->_tbl_key . ' = ' . (int) $idArray[$i]);
				$query->set('ordering = ' . (int) $lft_array[$i]);
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
