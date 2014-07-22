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

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * The Menu List Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_ijoomer
 * @since		1.6
 */
class IjoomeradvControllerMenus extends JControllerLegacy
{
	/**
	 * Display the view
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.6
	 */
	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	public function home()
	{
		$this->setRedirect('index.php?option=com_ijoomeradv',null);
	}

	/*
	 * Add New Menu
	 */
	function add()
	{
		$this->setRedirect('index.php?option=com_ijoomeradv&view=menu&layout=edit',null);
	}

	/*
	 * Edit Menu
	 */
	function edit()
	{
		$id=JRequest::getVar('cid',null,'','array');
		$this->setRedirect('index.php?option=com_ijoomeradv&view=menu&layout=edit&id='.$id[0],null);
	}
	
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Menu', $prefix = 'IjoomeradvModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	/**
	 * Removes an item
	 */
	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid	= JRequest::getVar('cid', array(), '', 'array');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_('COM_IJOOMERADV_NO_MENUS_SELECTED'));
		} else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if (!$model->delete($cid)) {
				$this->setMessage($model->getError());
			} else {
			$this->setMessage(JText::plural('COM_IJOOMERADV_N_MENUS_DELETED', count($cid)));
			}
		}

		$this->setRedirect('index.php?option=com_ijoomeradv&view=menus');
	}

	/**
	 * Rebuild the menu tree.
	 *
	 * @return	bool	False on failure or error, true on success.
	 */
	public function rebuild()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_ijoomeradv&view=menus');

		// Initialise variables.
		$model = $this->getModel('Item');

		if ($model->rebuild()) {
			// Reorder succeeded.
			$this->setMessage(JText::_('JTOOLBAR_REBUILD_SUCCESS'));
			return true;
		} else {
			// Rebuild failed.
			$this->setMessage(JText::sprintf('JTOOLBAR_REBUILD_FAILED', $model->getMessage()));
			return false;
		}
	}

	/**
	 * Temporary method. This should go into the 1.5 to 1.6 upgrade routines.
	 */
	public function resync()
	{
		// Initialise variables.
		$db = JFactory::getDbo();
		$parts = null;

		// Load a lookup table of all the component id's.
		$components = $db->setQuery(
			'SELECT element, extension_id' .
			' FROM #__extensions' .
			' WHERE type = '.$db->quote('component')
		)->loadAssocList('element', 'extension_id');

		if ($error = $db->getErrorMsg()) {
			return JError::raiseWarning(500, $error);
		}

		// Load all the component menu links
		$items = $db->setQuery(
			'SELECT id, link, component_id' .
			' FROM #__ijoomeradv_menu' .
			' WHERE type = '.$db->quote('component')
		)->loadObjectList();

		if ($error = $db->getErrorMsg()) {
			return JError::raiseWarning(500, $error);
		}

		foreach ($items as $item) {
			// Parse the link.
			parse_str(parse_url($item->link, PHP_URL_QUERY), $parts);

			// Tease out the option.
			if (isset($parts['option'])) {
				$option = $parts['option'];

				// Lookup the component ID
				if (isset($components[$option])) {
					$componentId = $components[$option];
				} else {
					// Mismatch. Needs human intervention.
					$componentId = -1;
				}

				// Check for mis-matched component id's in the menu link.
				if ($item->component_id != $componentId) {
					// Update the menu table.
					$log = "Link $item->id refers to $item->component_id, converting to $componentId ($item->link)";
					echo "<br/>$log";

					$db->setQuery(
						'UPDATE #__ijoomeradv_menu' .
						' SET component_id = '.$componentId.
						' WHERE id = '.$item->id
					)->query();

					if ($error = $db->getErrorMsg()) {
						return JError::raiseWarning(500, $error);
					}
				}
			}
		}
	}
}