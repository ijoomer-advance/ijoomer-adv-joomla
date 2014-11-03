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
 * The Class For IjoomeradvcontrollerMenu which Will Extends JControllerForm
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.controller
 * @since       1.0
 */
class IjoomeradvControllerMenu extends JControllerForm
{
	/**
	 * Dummy method to redirect back to standard controller
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   boolean  $urlparams  $urlparams
	 *
	 * @return  JController           This object to support chaining.
	 */
	public function display($cachable = false, $urlparams = false)
	{
		JControllerLegacy::display();
	}

	/**
	 * Method to save a menu item.
	 *
	 * @param   [type]  $key     contains the value of key
	 * @param   [type]  $urlVar  contains the value of urlvariable
	 *
	 * @return  void
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app  = JFactory::getApplication();
		$data = $app->input->get('jform', array(), 'array');

		$context  = 'com_ijoomeradv.edit.menu';
		$task     = $this->getTask();
		$recordId = $app->input->getInt('id', 0);

		// Make sure we are not trying to modify an administrator menu.
		if (isset($data['client_id']) && $data['client_id'] == 1)
		{
			throw new RuntimeException(JText::_('COM_IJOOMERADV_MENU_TYPE_NOT_ALLOWED'), 0);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=menu&layout=edit', false));

			return false;
		}

		// Populate the row id from the session.
		$data['id'] = $recordId;

		// Get the model and attempt to validate the posted data.
		$model = $this->getModel('Menu');
		$form  = $model->getForm();

		if (!$form)
		{
			throw new RuntimeException($model->getError(), 500);

			return false;
		}

		$data = $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
			// Save the data in the session.
			$app->setUserState('com_ijoomeradv.edit.menu.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=menu&layout=edit', true));

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Save the data in the session.
			$app->setUserState('com_ijoomeradv.edit.menu.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=menu&layout=edit', false));

			return false;
		}

		$this->setMessage(JText::_('COM_IJOOMERADV_MENU_SAVE_SUCCESS'));

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the record data in the session.
				$recordId = $model->getState($this->context . '.id');
				$this->holdEditId($context, $recordId);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=menu&layout=edit' . $this->getRedirectToItemAppend($recordId), false));
				break;

			case 'save2new':
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=menu&layout=edit', false));
				break;

			default:
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=menus', false));
				break;
		}
	}

	/**
	 * The SetType Function
	 *
	 * @return void
	 */
	public function setType()
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		$id  = $app->input->getInt('id', 0);

		// Get the posted values from the request.
		$data  = $app->input->get('jform', array(), 'array');
		$reqid = '';

		if ($id)
		{
			$data['id'] = $id;
			$reqid      = '&id=' . $id;
		}

		$data['screen'] = json_decode($data['screen'])->result[0];

		foreach ($data['screen'] as $key => $value)
		{
			$views             = explode('.', $value);
			$view[$views[0]][] = $views[1] . '.' . $views[2] . '.' . $views[3];
		}

		$data['screen'] = json_encode($view);
		$app->setUserState('com_ijoomeradv.edit.menu.data', $data);

		$this->type = $type;
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=menu&layout=edit' . $reqid, false));
	}

	/**
	 * The Cancle Function
	 *
	 * @param   [type]  $key  contains the value of key
	 *
	 * @return  boolean returns false value.
	 */
	public function cancel($key = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$context = 'com_ijoomeradv.edit.menu';
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=menus', false));
	}
}
