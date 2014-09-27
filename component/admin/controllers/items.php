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

/**
 * The Menu Item Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_ijoomer
 * @since		1.6
 */
class IjoomeradvControllerItems extends JControllerAdmin
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unsetDefault',	'setDefault');
	}

	public function display($cachable = false, $urlparams = false)
	{
		JControllerLegacy::display();
	}

	public function home()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv', true), null);
	}

	/*
	 * Add New Menu
	 */
	function add()
	{
		$this->setRedirect('index.php?option=com_ijoomeradv&view=item&layout=edit',null);
	}

	/*
	 * Edit Menu
	 */
	function edit()
	{
		$app = JFactory::getApplication();
		$id  = $app->input->getArray('cid', array());
		$this->setRedirect('index.php?option=com_ijoomeradv&view=item&layout=edit&id='.$id[0],null);
	}

	/**
	 * Proxy for getModel
	 * @since	1.6
	 */
	function getModel($name = 'Item', $prefix = 'ijoomeradvModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.6
	 */
	public function rebuild()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_ijoomeradv&view=items');

		// Initialise variables.
		$model = $this->getModel();

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(JText::_('COM_IJOOMERADV_ITEMS_REBUILD_SUCCESS'));
			return true;
		}
		else
		{
			// Rebuild failed.
			$this->setMessage(JText::sprintf('COM_IJOOMERADV_ITEMS_REBUILD_FAILED'));
			return false;
		}
	}

	public function saveorder()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the arrays from the Request
		$app           = JFactory::getApplication();
		$post          = $app->input->getArray('post', array());
		$order         = $post['order'];
		$originalOrder = explode(',', JRequest::getString('original_order_values'));

		// Make sure something has changed
		if (!($order === $originalOrder))
		{
			parent::saveorder();
		}
		else
		{
			// Nothing to reorder
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
			return true;
		}
	}

	/**
	 * Method to set the home property for a list of items
	 *
	 * @since	1.6
	 */
	function setDefault()
	{
		// Check for request forgeries
		JSession::checkToken('request') or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$app    = JFactory::getApplication();
		$cid    = $app->input->getArray('cid', array());
		$data	= array('setDefault' => 1, 'unsetDefault' => 0);
		$task 	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			throw new RuntimeException(JText::_('JERROR_NO_ITEMS_SELECTED'), 500);
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->setHome($cid, $value))
			{
				throw new RuntimeException($model->getError(), 500);
			}
			else
			{
				if ($value == 1)
				{
					$ntext = 'COM_IJOOMERADV_ITEMS_SET_HOME';
				}
				else
				{
					$ntext = 'COM_IJOOMERADV_ITEMS_UNSET_HOME';
				}

				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=item', false));
	}
}
