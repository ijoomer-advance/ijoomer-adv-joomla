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
 * The Class For IJoomeradvControllerPushnotifiaction which will extends JControllerLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.controller
 * @since       1.0
 */

class IjoomeradvControllerPushnotif extends JControllerForm
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
	public function add()
	{
		$app = JFactory::getApplication();
		$app->input->set('layout', 'detail');
		$app->input->set('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * [searchAjax description]
	 *
	 * @return  void
	 */
	public function searchAjax()
	{
		// Required objects
		$app   = JFactory::getApplication();
		$model = $this->getModel('Pushnotif', ' IjoomeradvModel');
		$input = JFactory::getApplication()->input;
		$like  = $input->get('like', null, 'word');
		$like  = str_replace(" ", "-", $like);

		// Receive request data
		$filters = array(
			'like'     => $like,
			'name'     => trim($app->input->get('name', null))
		);

		if ($categories = $model->searchParent($filters))
		{
			// Output a JSON object
			echo json_encode($categories);
		}

		$app->close();
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
	public function getModel($name = 'Pushnotif', $prefix = 'IjoomeradvModel', $config = array('ignore_request' => true))
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
		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

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
				$this->setMessage(JText::plural('COM_IJOOMERADV_N_NOTIF_DELETED', count($cid)));
			}
		}

		$this->setRedirect('index.php?option=com_ijoomeradv&view=pushnotif');
	}

	/**
	 * The SendPushNotification Function
	 *
	 * @return  boolean  returns the value in true or false
	 */
	public function sendPushNotification()
	{
		$model = $this->getModel('pushnotif');

		$model->send_push_notification($device_token, $message = '', $badge = 1, $type = '');
		parent::display();
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$db    = JFactory::getDBO();
		$query = "SELECT `name`, `value`
				FROM `#__ijoomeradv_config`
				WHERE `name` in ('IJOOMER_PUSH_ENABLE_IPHONE','IJOOMER_PUSH_DEPLOYMENT_IPHONE','IJOOMER_PUSH_ENABLE_SOUND_IPHONE','IJOOMER_PUSH_ENABLE_ANDROID','IJOOMER_PUSH_API_KEY_ANDROID')";
		$db->setQuery($query);
		$configvalue = $db->loadAssocList('name');

		if ($validData['to_all'] == 1)
		{
			switch ($validData['device_type'])
			{
				case 'iphone':
					if (array_key_exists('IJOOMER_PUSH_ENABLE_IPHONE', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_IPHONE']['value'] == 1)
					{
						// Initialiase variables.
						$db    = JFactory::getDbo();
						$query = $db->getQuery(true);

						// Create the base select statement.
						$query->select('device_token')
							->from($db->quoteName('#__ijoomeradv_users'))
							->where($db->quoteName('device_type') . ' = ' . $db->quote('iphone'))
							->order($db->quoteName('userid') . ' ASC');

						// Set the query and load the result.
						$db->setQuery($query);
						$users = $db->loadObjectList();

						foreach ($users as $user)
						{
							$options                   = array();
							$options['device_token']   = $user->device_token;
							$options['live']           = intval($configvalue['IJOOMER_PUSH_DEPLOYMENT_IPHONE']['value']);
							$options['aps']['message'] = 'backend';
							$model->sendIphonePushNotification($options);
						}
					}
					break;

				case 'android':
					if (array_key_exists('IJOOMER_PUSH_ENABLE_ANDROID', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_ANDROID']['value'] == 1)
					{
						$db    = JFactory::getDbo();
						$query = $db->getQuery(true);

						// Create the base select statement.
						$query->select('device_token')
							->from($db->quoteName('#__ijoomeradv_users'))
							->where($db->quoteName('device_type') . ' = ' . $db->quote('android'))
							->order($db->quoteName('userid') . ' ASC');

						// Set the query and load the result.
						$db->setQuery($query);
						$users = $db->loadObjectList();

						if (!empty($users))
						{
							$dtoken = array();

							foreach ($users as $user)
							{
								$dtoken[] = $user->device_token;
							}

							$options                         = array();
							$options['registration_ids']     = $dtoken;
							$options['api_key']              = $configvalue['IJOOMER_PUSH_API_KEY_ANDROID']['value'];
							$options['validData']['message'] = $validData['message'];
							$options['validData']['type']    = 'backend';
							$model->sendAndroidPushNotification($options);
						}
					}
					break;

				case 'both':
				default:
					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('device_token,device_type')
						->from($db->quoteName('#__ijoomeradv_users'))
						->order($db->quoteName('userid') . ' ASC');

					// Set the query and load the result.
					$db->setQuery($query);
					$users = $db->loadObjectList();

					$dtoken = array();

					foreach ($users as $user)
					{
						if ($user->device_type == 'iphone' && array_key_exists('IJOOMER_PUSH_ENABLE_IPHONE', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_IPHONE']['value'] == 1)
						{
							$options                   = array();
							$options['device_token']   = $user->device_token;
							$options['live']           = intval($configvalue['IJOOMER_PUSH_DEPLOYMENT_IPHONE']['value']);
							$options['aps']['message'] = $validData['message'];
							$model->sendIphonePushNotification($options);
						}
						elseif ($user->device_type == 'android' && array_key_exists('IJOOMER_PUSH_ENABLE_ANDROID', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_ANDROID']['value'] == 1)
						{
							$dtoken[] = $user->device_token;
						}
					}

					$options                         = array();
					$options['registration_ids']     = $dtoken;
					$options['api_key']              = $configvalue['IJOOMER_PUSH_API_KEY_ANDROID']['value'];
					$options['validData']['message'] = $validData['message'];
					$options['validData']['type']    = 'backend';
					$model->sendAndroidPushNotification($options);
					break;
			}
		}

		if ($validData['Username'])
		{
			$users      = explode(",", $validData['Username']);
			$sendtouser = array();

			foreach ($users as $user)
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('id')
					->from($db->quoteName('#__users'))
					->where($db->quoteName('username') . ' = ' . $db->quote($user));

				// Set the query and load the result.
				$db->setQuery($query);

				$uid = $db->loadResult();

				if ($uid)
				{
					$sendtouser[] = $uid;
				}
			}

			$comma_separated = implode(",", $sendtouser);

			switch ($validData['device_type'])
			{
				case 'iphone':
					if (array_key_exists('IJOOMER_PUSH_ENABLE_IPHONE', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_IPHONE']['value'] == 1)
					{
						$query = "SELECT `device_token`
								FROM #__ijoomeradv_users
								WHERE `device_type`='iphone'
								AND `userid` IN ({$comma_separated})
								ORDER BY `userid` ";
						$db->setQuery($query);
						$users = $db->loadobjectList();

						if (!empty($users))
						{
							foreach ($users as $user)
							{
								$options                   = array();
								$options['device_token']   = $user->device_token;
								$options['live']           = intval($configvalue['IJOOMER_PUSH_DEPLOYMENT_IPHONE']['value']);
								$options['aps']['message'] = $validData['message'];
								$model->sendIphonePushNotification($options);
							}
						}
					}
					break;

				case 'android':
					if (array_key_exists('IJOOMER_PUSH_ENABLE_ANDROID', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_ANDROID']['value'] == 1)
					{
						$query = "SELECT `device_token`
								FROM #__ijoomeradv_users
								WHERE `device_type`='android'
								AND `userid` IN ({$comma_separated})
								ORDER BY `userid` ";
						$db->setQuery($query);
						$users  = $db->loadobjectList();
						$dtoken = array();

						foreach ($users as $user)
						{
							$dtoken[] = $user->device_token;
						}

						$options                         = array();
						$options['registration_ids']     = $dtoken;
						$options['api_key']              = $configvalue['IJOOMER_PUSH_API_KEY_ANDROID']['value'];
						$options['validData']['message'] = $validData['message'];
						$options['validData']['type']    = 'backend';
						$model->sendAndroidPushNotification($options);
					}
					break;

				case 'both':
				default:
					/*$query = "SELECT `device_token`,`device_type` FROM #__ijoomeradv_users
							AND `userid` IN ({$comma_separated})
							ORDER BY `userid`";

							echo $query;exit;*/
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('device_token, device_type')
					->from($db->quoteName('#__ijoomeradv_users'))
					->where('userid IN (' . $comma_separated . ')')
					->order($db->quoteName('userid') . ' ASC');

				$db->setQuery($query);
				$users = $db->loadobjectList();
				$dtoken = array();

					foreach ($users as $user)
					{
						if ($user->device_type == 'iphone' && array_key_exists('IJOOMER_PUSH_ENABLE_IPHONE', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_IPHONE']['value'] == 1)
						{
							$options                   = array();
							$options['device_token']   = $user->device_token;
							$options['live']           = intval($configvalue['IJOOMER_PUSH_DEPLOYMENT_IPHONE']['value']);
							$options['aps']['message'] = $validData['message'];
							$model->sendIphonePushNotification($options);
						}
						elseif ($user->device_type == 'android' && array_key_exists('IJOOMER_PUSH_ENABLE_ANDROID', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_ANDROID']['value'] == 1)
						{
							$dtoken[] = $user->device_token;
						}

						$options                         = array();
						$options['registration_ids']     = $dtoken;
						$options['api_key']              = $configvalue['IJOOMER_PUSH_API_KEY_ANDROID']['value'];
						$options['validData']['message'] = $validData['message'];
						$options['validData']['type']    = 'backend';
						$model->sendAndroidPushNotification($options);
					}
					break;
			}
		}

		if ($model->save($validData))
		{
			$msg = JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_SAVED');
		}
		else
		{
			$msg = JText::_('COM_IJOOMERADV_ERROR_SAVING_PUSH_NOTIFICATION');
		}

		$link = 'index.php?option=com_ijoomeradv&view=pushnotif';
		$this->setRedirect($link, $msg);
	}
}
