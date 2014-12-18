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
class IjoomeradvModelPushnotif extends JModelAdmin
{
	private $_data         = null;

	private $_total        = null;

	private $_pagination   = null;

	private $_table_prefix = null;

	private $_all_list     = false;

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
		$limit      = $mainframe->getUserStateFromRequest($context . 'limit', 'limit', $mainframe->getCfg('list_limit'), 0);
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
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('username')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('id') . ' IN (SELECT userid FROM #__ijoomeradv_users)');

		// Set the query and load the result.
		$db->setQuery($query);

		$user = $db->loadColumn();

		return $user;
	}

	/**
	 * Method to delete groups.
	 *
	 * @param   [type]  &$pks  An array of item ids.
	 *
	 * @return  boolean    Returns true on success, false on failure.
	 */
	public function delete(&$pks)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		// Get a group row instance.
		$table = $this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $itemId)
		{
			// @TODO: Delete the menu associations - Menu items and Modules
			if (!$table->delete($itemId))
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed               A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// The folder and element vars are passed when saving the form.
		if (empty($data))
		{
			// The type should already be set.
			$Notifications = $this->getPushNotifications();
		}

		// Get the form.
		$form = $this->loadForm('com_ijoomeradv.pushnotif', 'pushnotif', array('control' => 'jform', 'load_data' => $loadData), true);

		if (empty($form))
		{
			return false;
		}

		return $form;
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
			->from($db->quoteName('#__ijoomeradv_push_notification'))
			->order($db->quoteName('id') . 'DESC');

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
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		jimport( 'joomla.form.form' );
		$input      = JFactory::getApplication()->input;
		$formData = $input->get('jform',array(),'array');

		$usernm=$formData['ijoomeradv'];

		// Initialiase variables.
		$db    = JFactory::getDbo();

		foreach ($usernm as $key => $value)
		{
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('name')
				->from($db->quoteName('#__users'))
				->where($db->quoteName('id') . ' = ' . $db->quote($value));

			// Set the query and load the result.
			$db->setQuery($query);

			$result = $db->loadObjectList();
			$user[]= $result;

		}

   		foreach ($user as $key => $value)
   		{
   			foreach ($value as $key1 => $unm)
   			{

   				$a[]=$unm;

   			}
   		}

   		foreach ($a as $key => $value)
   		{
   			$c[]=$value->name;
   		}

   		$usernm=implode(',',$c);

		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__ijoomeradv_push_notification'))
			->set($db->quoteName('to_user') . ' = ' . $db->quote($usernm))
			->where($db->quoteName('to_user') . ' = ' . $db->quote(''));


		// Set the query and execute the update.
		$db->setQuery($query);

		$db->execute();

		$dispatcher = JEventDispatcher::getInstance();
		$table      = $this->getTable();
		$pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew      = true;

		return parent::save($data);
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
		$server          = ($options['live']) ? 'ssl://gateway.push.apple.com:2195' : 'ssl://gateway.sandbox.push.apple.com:2195';
		$keyCertFilePath = JPATH_SITE . '/components/com_ijoomer/certificates/certificates.pem';

		// Construct the notification payload
		$body                 = array();
		$body['aps']          = $options['aps'];
		$body['aps']['badge'] = (isset($options['aps']['badge']) && !empty($options['aps']['badge'])) ? $options['aps']['badge'] : 1;
		$body['aps']['sound'] = (isset($options['aps']['sound']) && !empty($options['aps']['sound'])) ? $options['aps']['sound'] : 'default';
		$payload              = json_encode($body);

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $keyCertFilePath);
		$fp  = stream_socket_client($server, $error, $errorString, 60, STREAM_CLIENT_CONNECT, $ctx);

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
		$url                        = 'https://android.googleapis.com/gcm/send';
		$options['data']['badge']   = (isset($options['data']['badge']) && !empty($options['data']['badge'])) ? $options['data']['badge'] : 1;
		$fields['registration_ids'] = $options['registration_ids'];
		$fields['data']             = $options['data'];

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


	public static function searchParent($filters = array())
	{
		$input = JFactory::getApplication()->input;
		$id = $input->getInt('id');

		$db = JFactory::getDbo();
		$filters['like'] = str_replace("-", " ", $filters['like']);

		$query = $db->getQuery(true)
			->select('a.id AS value, a.name AS text')
			->from($db->qn('#__users') . ' AS a')
			->join('LEFT', '#__ijoomeradv_users AS b ON b.userid = a.id');

		if ($id != 0)
		{
			$query->join('LEFT', $db->quoteName('#__users') . ' AS p ON p.id = ' . (int) $id);
		}

		// Search in title or path
		if (!empty($filters['like']))
		{
			$query->where('(' . $db->quoteName('a.name') . ' LIKE ' . $db->quote('%' . $filters['like'] . '%') . ')');
		}

		// Filter title
		if (!empty($filters['name']))
		{
			$query->where($db->quoteName('a.name') . ' = ' . $db->quote($filters['name']));
		}

		// Get the options.
		$db->setQuery($query);

		try
		{
			$results = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return false;
		}


		for ($i = 0;$i < count($results);$i++)
		{
			$results[$i]->text = $results[$i]->text;
		}

		return $results;
	}
}
