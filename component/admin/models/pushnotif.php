<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.models
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.filesystem.file');

/**
 * The Class For IJoomeradvModelPushnoif which will Extends The JModelLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.models
 * @since       1.0
 */
class IjoomeradvModelPushnotif extends JModelLegacy
{
	private $_data = null;

	private $_total = null;

	private $_pagination = null;

	private $_table_prefix = null;

	private $_all_list = false;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		global $context;
		$mainframe = JFactory::getApplication();

		// $context='id';
		$this->_table_prefix = '#__ijoomeradv_';
		$limit = $mainframe->getUserStateFromRequest($context . 'limit', 'limit', $mainframe->getCfg('list_limit'), 0);
		$limitstart = $mainframe->getUserStateFromRequest($context . 'limitstart', 'limitstart', 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * The Function For The Getting Users
	 *
	 * @return  User
	 */
	public function getUsers()
	{
		// Initialiase variables.
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);
		$subQuery = $db->getQuery(true);

		$subQuery->select('userid')
				->from($db->qn('#__ijoomeradv_users'));

		// Create the base select statement.
		$query->select('username')
			->from($db->qn('#__users'))
			->where($db->qn('id') . ' IN (' . $subQuery->__toString() . ')');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$user = $db->loadResultArray();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $user;
	}

	/**
	 * The Function For The GetPushNotofications
	 *
	 * @return  it will returns the pushnotification
	 */
	public function getPushNotifications()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->qn('#__ijoomeradv_push_notification'))
			->order($db->qn('id') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$pushNotifications = $db->loadAssocList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $pushNotifications;
	}

	/**
	 * The Function For The Store
	 *
	 * @return  void
	 */
	public function store()
	{
		$row   = $this->getTable();
		$data  = JRequest::get('post');
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('name, value')
			->from($db->qn('#__ijoomeradv_config'))
			->where($db->qn('name') . ' IN (IJOOMER_PUSH_ENABLE_IPHONE , IJOOMER_PUSH_DEPLOYMENT_IPHONE, IJOOMER_PUSH_ENABLE_SOUND_IPHONE, IJOOMER_PUSH_ENABLE_ANDROID, IJOOMER_PUSH_API_KEY_ANDROID)');

		// Set the query and load the result.
		$db->setQuery($query);

		$configvalue = $db->loadAssocList('name');

		if ($data['to_all'] == 1)
		{
			switch ($data['device_type'])
			{
				case 'iphone':
					if (array_key_exists('IJOOMER_PUSH_ENABLE_IPHONE', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_IPHONE']['value'] == 1)
					{
						$query = $db->getQuery(true);

						// Create the base select statement.
						$query->select('device_token')
							->from($db->qn('#__ijoomeradv_users'))
							->where($db->qn('device_type') . ' = ' . $db->q('iphone'))
							->order($db->qn('userid') . ' ASC');

						$db->setQuery($query);
						$users = $db->loadobjectList();

						foreach ($users as $user)
						{
							$options = array();
							$options['device_token'] = $user->device_token;
							$options['live'] = intval($configvalue['IJOOMER_PUSH_DEPLOYMENT_IPHONE']['value']);
							$options['aps']['message'] = 'backend';
							$this->sendIphonePushNotification($options);
						}
					}
					break;

				case 'android':
					if (array_key_exists('IJOOMER_PUSH_ENABLE_ANDROID', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_ANDROID']['value'] == 1)
					{
						$query = $db->getQuery(true);

						// Create the base select statement.
						$query->select('device_token')
							->from($db->qn('#__ijoomeradv_users'))
							->where($db->qn('device_type') . ' = ' . $db->q('android'))
							->order($db->qn('userid') . ' ASC');

						$db->setQuery($query);
						$users = $db->loadobjectList();

						if (!empty($users))
						{
							$dtoken = array();

							foreach ($users as $user)
							{
								$dtoken[] = $user->device_token;
							}

							$options = array();
							$options['registration_ids'] = $dtoken;
							$options['api_key'] = $configvalue['IJOOMER_PUSH_API_KEY_ANDROID']['value'];
							$options['data']['message'] = $data['message'];
							$options['data']['type'] = 'backend';
							$this->sendAndroidPushNotification($options);
						}
					}
					break;

				case 'both':
				default:
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('device_token, device_type')
						->from($db->qn('#__ijoomeradv_users'))
						->order($db->qn('userid') . ' ASC');

					$this->_db->setQuery($query);
					$users  = $this->_db->loadobjectList();
					$dtoken = array();

					foreach ($users as $user)
					{
						if ($user->device_type == 'iphone' && array_key_exists('IJOOMER_PUSH_ENABLE_IPHONE', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_IPHONE']['value'] == 1)
						{
							$options = array();
							$options['device_token'] = $user->device_token;
							$options['live'] = intval($configvalue['IJOOMER_PUSH_DEPLOYMENT_IPHONE']['value']);
							$options['aps']['message'] = $data['message'];
							$this->sendIphonePushNotification($options);
						}
						elseif ($user->device_type == 'android' && array_key_exists('IJOOMER_PUSH_ENABLE_ANDROID', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_ANDROID']['value'] == 1)
						{
							$dtoken[] = $user->device_token;
						}
					}

					$options = array();
					$options['registration_ids'] = $dtoken;
					$options['api_key'] = $configvalue['IJOOMER_PUSH_API_KEY_ANDROID']['value'];
					$options['data']['message'] = $data['message'];
					$options['data']['type'] = 'backend';
					$this->sendAndroidPushNotification($options);
					break;
			}
		}

		if ($data['to_user'])
		{
			$users = explode(",", $data['to_user']);
			$sendtouser = array();

			foreach ($users as $user)
			{
				$query = $db->getQuery(true);

				// fetch userid and store to array to save
				$query->select('id')
					->from($db->qn('#__users'))
					->where($db->qn('username') . ' = ' . $user);

				$db->setQuery($query);
				$uid = $db->loadResult();

				if ($uid)
				{
					$sendtouser[] = $uid;
				}
			}

			$comma_separated = implode(",", $sendtouser);

			switch ($data['device_type'])
			{
				case 'iphone':
					if (array_key_exists('IJOOMER_PUSH_ENABLE_IPHONE', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_IPHONE']['value'] == 1)
					{
						$query = $db->getQuery(true);

						// Create the base select statement.
						$query->select('device_token')
							->from($db->qn('#__ijoomeradv_users'))
							->where($db->qn('device_type') . ' = ' . $db->q('iphone'))
							->where($db->qn('userid') . ' IN (' . $comma_separated . ')')
							->order($db->qn('userid') . ' ASC');

						$db->setQuery($query);
						$users = $db->loadobjectList();

						if (!empty($users))
						{
							foreach ($users as $user)
							{
								$options = array();
								$options['device_token'] = $user->device_token;
								$options['live'] = intval($configvalue['IJOOMER_PUSH_DEPLOYMENT_IPHONE']['value']);
								$options['aps']['message'] = $data['message'];
								$this->sendIphonePushNotification($options);
							}
						}
					}
					break;

				case 'android':
					if (array_key_exists('IJOOMER_PUSH_ENABLE_ANDROID', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_ANDROID']['value'] == 1)
					{
						$query = $db->getQuery(true);

						// Create the base select statement.
						$query->select('device_token')
							->from($db->qn('#__ijoomeradv_users'))
							->where($db->qn('device_type') . ' = ' . $db->q('android'))
							->where($db->qn('userid') . ' IN (' . $comma_separated . ')')
							->order($db->qn('userid') . ' ASC');

						$db->setQuery($query);
						$users  = $db->loadobjectList();
						$dtoken = array();

						foreach ($users as $user)
						{
							$dtoken[] = $user->device_token;
						}

						$options = array();
						$options['registration_ids'] = $dtoken;
						$options['api_key'] = $configvalue['IJOOMER_PUSH_API_KEY_ANDROID']['value'];
						$options['data']['message'] = $data['message'];
						$options['data']['type'] = 'backend';
						$this->sendAndroidPushNotification($options);
					}
					break;

				case 'both':
				default:
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('device_token, device_type')
						->from($db->qn('#__ijoomeradv_users'))
						->where($db->qn('userid') . ' IN (' . $comma_separated . ')')
						->order($db->qn('userid') . ' ASC');

					$this->_db->setQuery($query);
					$users  = $this->_db->loadobjectList();
					$dtoken = array();

					foreach ($users as $user)
					{
						if ($user->device_type == 'iphone' && array_key_exists('IJOOMER_PUSH_ENABLE_IPHONE', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_IPHONE']['value'] == 1)
						{
							$options = array();
							$options['device_token'] = $user->device_token;
							$options['live'] = intval($configvalue['IJOOMER_PUSH_DEPLOYMENT_IPHONE']['value']);
							$options['aps']['message'] = $data['message'];
							$this->sendIphonePushNotification($options);
						}
						elseif ($user->device_type == 'android' && array_key_exists('IJOOMER_PUSH_ENABLE_ANDROID', $configvalue) && $configvalue['IJOOMER_PUSH_ENABLE_ANDROID']['value'] == 1)
						{
							$dtoken[] = $user->device_token;
						}

						$options = array();
						$options['registration_ids'] = $dtoken;
						$options['api_key'] = $configvalue['IJOOMER_PUSH_API_KEY_ANDROID']['value'];
						$options['data']['message'] = $data['message'];
						$options['data']['type'] = 'backend';
						$this->sendAndroidPushNotification($options);
					}
					break;
			}
		}

		if (isset($comma_separated))
		{
			$data['to_user'] = $comma_separated;
		}

		// Bind the form fields to the hello table
		if (!$row->bind($data))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Make sure the record is valid
		if (!$row->check())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Store the web link table to the database
		if (!$row->store())
		{
			$this->setError($row->getErrorMsg());

			return false;
		}

		return true;
	}

	/**
	 * iphone push notification
	 *
	 * @param   [type]  $options  contains the value of options
	 *
	 * @return  it will return a value
	 */
	public function sendIphonePushNotification($options)
	{
		$server = ($options['live']) ? 'ssl://gateway.push.apple.com:2195' : 'ssl://gateway.sandbox.push.apple.com:2195';
		$keyCertFilePath = JPATH_SITE . '/components/com_ijoomer/certificates/certificates.pem';

		// Construct the notification payload
		$body = array();
		$body['aps'] = $options['aps'];
		$body['aps']['badge'] = (isset($options['aps']['badge']) && !empty($options['aps']['badge'])) ? $options['aps']['badge'] : 1;
		$body['aps']['sound'] = (isset($options['aps']['sound']) && !empty($options['aps']['sound'])) ? $options['aps']['sound'] : 'default';
		$payload = json_encode($body);

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $keyCertFilePath);
		$fp = stream_socket_client($server, $error, $errorString, 60, STREAM_CLIENT_CONNECT, $ctx);

		if (!$fp)
		{
			// Global mainframe;
			print "Failed to connect " . $error . " " . $errorString;

			return;
		}

		$msg = chr(0) . pack("n", 32) . pack('H*', str_replace(' ', '', $options['device_token'])) . pack("n", strlen($payload)) . $payload;
		fwrite($fp, $msg);
		fclose($fp);
	}

	/**
	 * android push notification
	 *
	 * @param   [type]  $options  contains the value of options
	 *
	 * @return  void
	 */
	public function sendAndroidPushNotification($options)
	{
		$url = 'https://android.googleapis.com/gcm/send';
		$options['data']['badge'] = (isset($options['data']['badge']) && !empty($options['data']['badge'])) ? $options['data']['badge'] : 1;
		$fields['registration_ids'] = $options['registration_ids'];
		$fields['data'] = $options['data'];

		$headers = array(
			'Authorization: key=' . $options['api_key'],
			'Content-Type: application/json'
		);

		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Disabling SSL Certificate support temporarly
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute post
		$result = curl_exec($ch);

		if ( $result === false)
		{
			die('Curl failed: ' . curl_error($ch));
		}
		// Close connection
		curl_close($ch);
	}
}
