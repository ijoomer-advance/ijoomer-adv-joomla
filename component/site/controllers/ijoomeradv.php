<?php
/**
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.controller
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For IJoomerControllerijoomeradv which will extends JControllerLegacy
 *
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.controller
 * @since       1.0
 */

class IjoomeradvControllerijoomeradv extends JControllerLegacy
{
	private $mainframe;

	private $session_pass = 0;

	private $IJUserID = null;

/**
 * Constructor
 *
 * @param   array  $default  $default
 */
	public function __construct($default = array())
	{
		$this->mainframe = JFactory::getApplication();
		parent::__construct($default);
		$this->defineApplicationConfig();
	}

	/**
	 * defines ijoomeradv application configuration
	 *
	 * @return  void
	 */
	private function defineApplicationConfig()
	{
		$model = $this->getModel('ijoomeradv');

		// Get application config
		$result = $model->getApplicationConfig();

		foreach ($result as $value)
		{
			defined($value->name) or define($value->name, $value->value);
		}
	}

	/**
	 * defines extension configuration
	 *
	 * @param   [type]  $extName  $extName
	 *
	 * @return  void
	 */
	private function defineExtensionConfig($extName)
	{
		$model = $this->getModel('ijoomeradv');

		// Get extension config
		$result = $model->getExtensionConfig($extName);

		foreach ($result as $value)
		{
			defined($value->name) or define($value->name, $value->value);
		}
	}

	/**
	 * Generates and displays JSON output with JSON mime type
	 *
	 * @param   [type]  $jsonarray  $jsonarray
	 *
	 * @return  void
	 */
	private function outputJSON($jsonarray)
	{
		// Set all warning/notice in json response
		$jsonarray['php_server_error'] = ($_SESSION['ijoomeradv_error']) ? $_SESSION['ijoomeradv_error'] : '';
		unset($_SESSION['ijoomeradv_error']);

		// Set the header content type to JSON format
		header("content-type: application/json");

		// Import ijoomeradv helper file
		require_once IJ_HELPER . '/helper.php';

		// Create hepler object
		$IJHelperObj = new ijoomeradvHelper;

		$encryption = $IJHelperObj->getencryption_config();

		if ($encryption == 1)
		{
			// Output the JSON encoded string
			$json = json_encode($jsonarray);

			// Add  code for replace back slases to forward slases.
			$json = str_replace('\\\\', '/', $json);

			require_once IJ_SITE . '/encryption/MCrypt.php';
			$RSA = new MCrypt;

			$encoded = $RSA->encrypt($json);
			echo $encoded;
			exit;
		}
		else
		{
			echo json_encode(str_replace("\\\\", "/", $jsonarray));

			if (!empty($jsonarray['pushNotificationData']))
			{
				$db = JFactory::getDbo();

				$memberlist = $jsonarray['pushNotificationData']['to'];

				if ($memberlist)
				{
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('userid, jomsocial_params, device_token, device_type')
						->from($db->qn('#__ijoomeradv_users'))
						->where($db->qn('userid') . ' = ' . $db->q($memberlist));

					// Set the query and load the result.
					$db->setQuery($query);

					$puserlist = $db->loadObjectList();

					foreach ($puserlist as $puser)
					{
						// Check config allow for jomsocial

						if (!empty($jsonarray['pushNotificationData']['configtype']) and $jsonarray['pushNotificationData']['configtype'] != '')
						{
							$ijparams = json_decode($puser->jomsocial_params);
							$configallow = $jsonarray['pushNotificationData']['configtype'];
						}
						else
						{
							$configallow = 1;
						}

						if ($configallow && $puser->userid != $this->IJUserID && !empty($puser))
						{
							if (IJOOMER_PUSH_ENABLE_IPHONE == 1 && $puser->device_type == 'iphone')
							{
								$options = array();
								$options['device_token'] = $puser->device_token;
								$options['live'] = intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
								$options['aps']['alert'] = strip_tags($jsonarray['pushNotificationData']['message']);
								$options['aps']['type'] = $jsonarray['pushNotificationData']['type'];
								$options['aps']['id'] = ($jsonarray['pushNotificationData']['id'] != 0) ? $jsonarray['pushNotificationData']['id'] : $jsonarray['pushNotificationData']['multiid'][$puser->userid];
								IJPushNotif::sendIphonePushNotification($options);
							}

							if (IJOOMER_PUSH_ENABLE_ANDROID == 1 && $puser->device_type == 'android')
							{
								$options = array();
								$options['registration_ids'] = array($puser->device_token);
								$options['data']['message'] = strip_tags($jsonarray['pushNotificationData']['message']);
								$options['data']['type'] = $jsonarray['pushNotificationData']['type'];
								$options['data']['id'] = ($jsonarray['pushNotificationData']['id'] != 0) ? $jsonarray['pushNotificationData']['id'] : $jsonarray['pushNotificationData']['multiid'][$puser->userid];
								IJPushNotif::sendAndroidPushNotification($options);
							}
						}
					}
				}

				unset($jsonarray['pushNotificationData']);
			}

			// Output the JSON encoded string
			exit;
		}
	}

	/**
	 * This function is used to check session
	 *
	 * @param   [type]  $whiteListTask  $whiteListTask
	 *
	 * @return  boolean returns true or false
	 */
	private function checkSession($whiteListTask)
	{
		// Get requested extension task (function inside view file)
		$extTask = IJReq::getExtTask();

		// Get requested extension view (file name of extension)
		$extView = IJReq::getExtView();
		$my = JFactory::getUser();

		if ($my->id > 0)
		{
			$this->session_pass = 1;
			$this->IJUserID = $my->id;
			$this->mainframe->setUserState('com_ijoomeradv.IJUserID', $my->id);
			$_SESSION['IJUserID'] = $my->id;
		}
		else
		{
			$this->session_pass = 0;
			$this->IJUserID = null;
			$this->mainframe->setUserState('com_ijoomeradv.IJUserID', null);
			unset($_SESSION['IJUserID']);
		}

		if ((IJOOMER_GC_LOGIN_REQUIRED && $this->session_pass == 1) || (in_array($extView . '.' . $extTask, $whiteListTask)) || !IJOOMER_GC_LOGIN_REQUIRED)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * The Ping Function
	 *
	 * @uses   this function will be used to any other request which is part of the installed extension
	 * @example the json string will be like, :
	 *    {
	 *        "task":"ping"
	 *    }
	 *
	 * @return void
	 */
	public function ping()
	{
		$model = $this->getModel('ijoomeradv');
		$results = $model->getExtensions();

		if (count($results) > 0)
		{
			$jsonarray['code'] = 200;
		}
		else
		{
			$jsonarray['code'] = 204;
			$this->outputJSON($jsonarray);
		}

		foreach ($results as $result)
		{
			$jsonarray['extensions'][] = $result->name;
		}

		$this->outputJSON($jsonarray);
	}

	/**
	 * The GetUrlContent Function
	 *
	 * @uses    this function will be used to get url params from url to send data in pushnotification from admin side by passing url
	 * @example the json string will be like, :
	 *    {
	 *        "task":"getUrlContent",
	 *        "taskData":{
	 *            "url":""//url
	 *        }
	 *    }
	 *
	 * @return void
	 */
	public function getUrlContent()
	{
		$url = IJReq::getTaskData('url');
		$options['mode'] = 1;
		$router = JApplication::getRouter('site', $options);
		$results = $router->parse(JURI::getInstance($url));

		// Define('JROUTER_MODE_SEF',1);
		$model = $this->getModel('ijoomeradv');
		$extensions = $model->getExtensions();
		$isExtAvail = false;

		foreach ($extensions as $extension)
		{
			if ($extension->option == $results['option'])
			{
				$isExtAvail = true;
				break;
			}
		}

		// Set url as external weblink if component not found
		if (!$isExtAvail)
		{
			$jsonarray['itemview'] = 'Web';
			$jsonarray['url'] = $url;
		}

		switch ($results['option'])
		{
			case 'com_content':
				require_once JPATH_COMPONENT . '/extensions/icms/helper.php';
				$helperClass = new icms_helper;
				$urlResults = $helperClass->getParseData($results);
				break;
		}

		if (!empty($urlResults))
		{
			$jsonarray = $urlResults;
		}
		else
		{
			$jsonarray['itemview'] = 'Web';
			$jsonarray['url'] = $url;
		}

		$this->outputJSON($jsonarray);
	}

	/**
	 * The Display Function
	 *
	 * @uses    this function will be used to any other request which is part of the installed extension
	 * @example the json string will be like, :
	 *    {
	 *        "taskData":{
	 *            "extName":"jomsocial",
	 *            "extView":"user",
	 *            "extTask":"userDetail",
	 *            "taskData":{
	 *            }
	 *        }
	 *    }
	 *
	 * @return void
	 */
	public function display()
	{

		// Get ijoomeradv model object
		$model = $this->getModel('ijoomeradv');

		// Get requested extension task (function inside view file)
		$menuid = IJReq::getTaskData('menuId', '');

		// Set request variable from manu
		if (!empty($menuid))
		{
			$model->setMenuRequest($menuid);
		}

		// Get requested extension name
		$extName = IJReq::getExtName();

		// Get requested extension view (file name of extension)
		$extView = IJReq::getExtView();

		// Get requested extension task (function inside view file)
		$extTask = IJReq::getExtTask();

		$jsonarray = array();

		if (!$model->checkIJExtension($extName))
		{
			// Check ijoomeradv extension and related component status from extension name passed in the request
			$jsonarray['code'] = IJReq::getResponseCode();

			$jsonarray['message'] = IJReq::getResponseMessage();

			$this->outputJSON($jsonarray);
		}

		// Main existance file

		$extensionmain = IJ_EXTENSION . '/' . $extName . '/' . $extName . ".php";

		// Extension view file
		$extensionview = IJ_EXTENSION . '/' . $extName . '/' . $extView . ".php";

		if (!file_exists($extensionview) or !file_exists($extensionmain))
		{
			$jsonarray['code'] = 404;

			// Extension File Not Found.';
			$jsonarray['message'] = null;
			$this->outputJSON($jsonarray);
		}

		// Define extension configuration so it can be directly used
		$this->defineExtensionConfig($extName);

		// Include main extension file
		include_once $extensionmain;

		// Create main extension class object
		$extMainObj = new $extName;

		if (!$this->checkSession($extMainObj->sessionWhiteList))
		{
			// CheckSession checks the session sent in task data and if session found it will all needed data.
			$jsonarray['code'] = 704;

			// Method Not Found.';
			$jsonarray['message'] = null;

			$this->outputJSON($jsonarray);
		}

		if (method_exists($extMainObj, 'init'))
		{
			// Check if initialization method exists
			$extMainObj->init();

			// Call init method
		}

		include_once $extensionview;
		$extObj = new $extView;

		if (!method_exists($extObj, $extTask))
		{
			// Check if method exists
			$jsonarray['code'] = 404;

			// Method Not Found.';
			$jsonarray['message'] = null;

			$this->outputJSON($jsonarray);
		}

		$jsonarray = $extObj->$extTask();

		if (!$jsonarray)
		{
			// If anything goes wrong; return error code and message in response
			$jsonarray['code'] = IJReq::getResponseCode();

			$jsonarray['message'] = IJReq::getResponseMessage();

			// Edd exception to log file
			IJException::addLog();
		}

		// Send data array to create jason string and output
		$this->outputJSON($jsonarray);
	}

	/**
	 * The Function ApplicationConfig
	 *
	 * @uses    this function is used to fetch application (global) config.
	 * @example the json string will be like, :
	 *    {
	 *        "task":"applicationConfig",
	 *        "taskData": {
	 *            "device":"android/iphone",
	 *            "type":"device type"
	 *        }
	 *    }
	 *
	 * @return theamarray
	 */
	public function applicationConfig()
	{

		$model = $this->getModel('ijoomeradv');

		// Get application config
		$result = $model->getApplicationConfig();

		$jsonarray = array();

		if ($result)
		{
			// Response ok
			$jsonarray['code'] = 200;

			foreach ($result as $value)
			{
				$jsonarray['configuration']['globalconfig'][$value->name] = $value->value;

				if ($value->name == 'IJOOMER_GC_REGISTRATION')
				{
					switch ($value->value)
					{
						case 'jomsocial':
							require_once JPATH_ROOT . '/components/com_community/libraries/' . 'core.php';
							require_once JPATH_COMPONENT_SITE . '/extensions/jomsocial/' . "helper.php";
							$jomHelper = new jomHelper;
							$jomsocial_version = $jomHelper->getjomsocialversion();

							if ($jomsocial_version >= 3)
							{
								$jsonarray['configuration']['globalconfig']['defaultAvatar'] = JURI::base() . 'components/com_community/assets/user-Male.png';
								$jsonarray['configuration']['globalconfig']['defaultAvatarFemale'] = JURI::base() . 'components/com_community/assets/user-Female.png';
							}
							else
							{
								$jsonarray['configuration']['globalconfig']['defaultAvatar'] = JURI::base() . 'components/com_community/assets/user.png';
							}
							break;
					}
				}
			}
		}
		else
		{
			// No data
			$jsonarray['code'] = 204;
			$jsonarray['message'] = JText::_('COM_IJOOMERADV_NO_CONFIGURATION_FOUND');
		}

		// Get all extension config
		$results = $model->getExtensions();

		foreach ($results as $result)
		{
			require_once IJ_EXTENSION . '/' . $result->classname . '/' . $result->classname . ".php";

			$classobj = new $result->classname;
			$extconfig = $classobj->getconfig();

			foreach ($extconfig as $key => $value)
			{
				$jsonarray['configuration']['extentionconfig'][$result->classname][$key] = $value;
			}
		}

		$jsonarray['configuration']['globalconfig']['timeStamp'] = time();
		$jsonarray['configuration']['globalconfig']['offset'] = (date_offset_get(new DateTime) / 3600);
		$jsonarray['configuration']['globalconfig']['offsetLocation'] = date_default_timezone_get();

		$homeMenus = $model->getHomeMenu();

		if ($homeMenus)
		{
			foreach ($homeMenus as $key => $homeMenu)
			{
				$homeMenuobj = new stdClass;
				$homeMenuobj->itemid = $homeMenu->id;
				$homeMenuobj->itemcaption = $homeMenu->title;
				$viewname = explode('.', $homeMenu->views);
				$homeMenuobj->itemview = $viewname[3];

				$remotedata = json_decode($homeMenu->menuoptions);
				$remotedata = ($remotedata) ? $remotedata->remoteUse : '';

				$homeMenuobj->itemdata = $remotedata;
				$jsonarray['configuration']['globalconfig']['default_landing_screen'] = $homeMenuobj;
			}
		}
		else
		{
			$jsonarray['configuration']['globalconfig']['default_landing_screen'] = '';
		}

		// Application get extension version info

		if (file_exists(JPATH_COMPONENT_SITE . '/extensions/jomsocial/' . "helper.php") && file_exists(JPATH_SITE . '/components/com_community/' . "community.php"))
		{
			require_once JPATH_COMPONENT_SITE . '/extensions/jomsocial/' . "helper.php";
			$jomHelper = new jomHelper;
			$jomsocial_version = $jomHelper->getjomsocialversion();
			$jsonarray['configuration']['versioninfo']["jomsocial"] = $jomsocial_version;
		}

		// Application theme list
		$jsonarray['configuration']['theme'] = $this->statictheme();

		// Application menu list
		$jsonarray['configuration']['menus'] = $model->getMenus();

		// Send data array to create jason string and output

		$this->outputJSON($jsonarray);
	}

	/**
	 * The StaticTheme Function
	 *
	 * @return  returns TheamArray
	 */
	private function statictheme()
	{
		$device = IJReq::getTaskData('device');
		$theme = IJOOMER_THM_SELECTED_THEME;
		$model = $this->getModel('ijoomeradv');
		$viewnames = $model->getViewNames();

		if ($device == 'android')
		{
			$device_type = IJReq::getTaskData('type', 'hdpi');
		}
		elseif ($device == 'iphone')
		{
			$device_type = IJReq::getTaskData('type', '3');
		}

		$i = 0;

		foreach ($viewnames as $key => $value)
		{
			foreach ($value as $ky => $val)
			{
				//@todo remaining task for images xxhdpi and xxhdpi
				$themearray['theme'][$i]['viewname'] = $val;
				$themearray['theme'][$i]['icon'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/' . $key . '/' . $device . '/' . $device_type . '/' . $val . '_icon.png';
				//$themearray['theme'][$i]['icon'] = "http://www.ijoomer.com/" . $theme . '/' . $key . '/' . $device . '/' . $device_type . '/' . $val . '_icon.png';
				$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/' . $key . '/' . $device . '/' . $device_type . '/' . $val . '_tab.png';
				$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/' . $key . '/' . $device . '/' . $device_type . '/' . $val . '_tab_active.png';
				$i++;
			}
		}

		$themearray['theme'][$i]['viewname'] = 'Home';
		$themearray['theme'][$i]['icon'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Home_icon.png';
		$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Home_tab.png';
		$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Home_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname'] = 'More';
		$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/More_tab.png';
		$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/More_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname'] = 'Registration';
		$themearray['theme'][$i]['icon'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Registration_icon.png';
		$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Registration_tab.png';
		$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Registration_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname'] = 'Web';
		$themearray['theme'][$i]['icon'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Web_icon.png';
		$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Web_tab.png';
		$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Web_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname'] = 'Login';
		$themearray['theme'][$i]['icon'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Login_icon.png';
		$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Login_tab.png';
		$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Login_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname'] = 'Logout';
		$themearray['theme'][$i]['icon'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Logout_icon.png';
		$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Logout_tab.png';
		$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/Logout_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname'] = 'PluginsContactUs';
		$themearray['theme'][$i]['icon'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/PluginsContactUs_icon.png';
		$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/PluginsContactUs_tab.png';
		$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/PluginsContactUs_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname'] = 'PluginsFacebookNearByVenues';
		$themearray['theme'][$i]['icon'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/PluginsFacebookNearByVenues_icon.png';
		$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/PluginsFacebookNearByVenues_tab.png';
		$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/PluginsFacebookNearByVenues_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname'] = 'PluginsYoutubePlaylist';
		$themearray['theme'][$i]['icon'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/PluginsYoutubePlaylist_icon.png';
		$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/PluginsYoutubePlaylist_tab.png';
		$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/PluginsYoutubePlaylist_tab_active.png';
		$i++;

		$customView = $model->getCustomView();

		foreach ($customView as $key => $value)
		{
			$viewname = explode('.', $value->views);
			$themearray['theme'][$i]['viewname'] = $viewname[3];
			$themearray['theme'][$i]['icon'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/defaultActivity_icon.png';
			$themearray['theme'][$i]['tab'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/defaultActivity_tab.png';
			$themearray['theme'][$i]['tab_active'] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/' . $theme . '/default/' . $device . '/' . $device_type . '/defaultActivity_tab_active.png';
			$i++;
		}

		return $themearray['theme'];
	}

	/**
	 * The Login Function
	 *
	 * @uses    this function is used to log into the application
	 * @example the json string will be like, :
	 *    {
	 *        "task":"login",
	 *        "taskData":{
	 *            "username":"abc",
	 *            "password":"xyz",
	 *            "lat":"23.00",
	 *            "long":"72.40",
	 *            "devicetoken"/"android_devicetoken"/"bb_devicetoken":"abc123xyz"
	 *        }
	 *    }
	 *
	 * @return void
	 */
	public function login()
	{
		if (!IJReq::getTaskData('username') or !IJReq::getTaskData('password'))
		{
			// Check if username or password not blank
			$jsonarray['code'] = 400;

			$jsonarray['message'] = null;

			// Send data array to create jason string and output
			$this->outputJSON($jsonarray);
		}

		$credentials = array();

		// Get username
		$credentials['username'] = IJReq::getTaskData('username');

		// Get password
		$credentials['password'] = IJReq::getTaskData('password');

		if ($this->mainframe->login($credentials) == '1')
		{
			$model = $this->getModel('ijoomeradv');
			$jsonarray = $model->loginProccess();
		}
		else
		{
			$jsonarray['code'] = 401;
			$jsonarray['message'] = JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE');
		}

		// Send data array to create jason string and output
		$this->outputJSON($jsonarray);
	}

	/**
	 * The Logout Function
	 *
	 * @uses    this function will use to log out of the application
	 * @example the json string will be like, :
	 *    {
	 *        "task":"logout"
	 *    }
	 *
	 * @return void
	 */
	public function logout()
	{
		$my = JFactory::getUser();

		if (!$my->id)
		{
			// If userid not passed or null
			$jsonarray['code'] = 400;

			$jsonarray['message'] = null;

			$this->outputJSON($jsonarray);
		}

		if ($this->mainframe->logout($my->id))
		{
			ob_end_clean();

			// Logout success
			$jsonarray['code'] = 200;

			$jsonarray['message'] = null;
		}
		else
		{
			// Logout unsuccess
			$jsonarray['code'] = 500;
			$jsonarray['message'] = JText::_('COM_IJOOMERADV_UNABLE_LOGOUT');
		}

		$this->outputJSON($jsonarray);
	}

	/**
	 * The GetPushNotification
	 *
	 * @uses    this function will use to get pushnotification from id
	 * @example the json string will be like, :
	 *    {
	 *        "task":"getPushNotification"
	 *        "taskData":{
	 *                        "id":
	 *                   }
	 *    }
	 *
	 * @return void
	 */
	public function getPushNotification()
	{
		$id 	= IJReq::getTaskData('id',0);
		$user 	= JFactory::getUser();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->qn('#__ijoomeradv_push_notification_data'))
			->where($db->qn('id') . ' = ' . $db->q($id));

		// Set the query and load the result.
		$db->setQuery($query);

		$pushData = $db->loadObject();

		if (!empty($pushData))
		{
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->qn('#__ijoomeradv_push_notification_data'))
				->set($db->qn('readcount') . ' = ' . $db->qn('readcount') . '+1')
				->where($db->qn('id') . ' = ' . $db->q($id));

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();

			$pushOptions       = gzuncompress($pushData->detail);
			$jsonarrayDetail   = json_decode($pushOptions,true);
			$jsonarray['code'] = 200;
			$jsonarray['data'] = $jsonarrayDetail['detail'];
		}
		else
		{
			$jsonarray['code'] = 204;
		}

		$this->outputJSON($jsonarray);
	}

	/**
	 * The FBLogin Function
	 *
	 * @uses    this function used to log in with Facebook
	 * @example the json string will be like, :
	 *    {
	 *        "task":"fblogin",
	 *        "taskData":{
	 *            "name":"name",
	 *            "username":"username",
	 *            "password":"password", // fbid as password
	 *            "email":"email",
	 *            "lat":"lat",
	 *            "long":"long",
	 *            "bigpic":"bigpic",
	 *            "devicetoken/android_devicetoken/bb_devicetoken":"devicetoken",
	 *            "regopt":"regopt", // 0: Check if user exist, 1: Existing user, 2: New user
	 *            "fbid":"fbid" // facebook userid
	 *        }
	 *    }
	 *
	 * @return void
	 */
	public function fblogin()
	{
		$model = $this->getModel('ijoomeradv');
		$jsonarray = $model->fblogin();

		if (!$jsonarray)
		{
			$jsonarray['code'] = IJReq::getResponseCode();
			$jsonarray['message'] = IJReq::getResponseMessage();
			$this->outputJSON($jsonarray);
		}

		$this->outputJSON($jsonarray);
	}

	/**
	 * The Registration Function
	 *
	 * @uses    this function is used to register new user
	 * @example the json string will be like, :
	 *    {
	 *        "task":"registration",
	 *        "taskData":{
	 *            "name":"name",
	 *            "username":"username",
	 *            "password":"password",
	 *            "email":"email",
	 *            "full":"0/1", // 0: for default registration form 1: for jomsocial extra fields
	 *            "type":"type" // profile type if any otherwise "default" pass
	 *        }
	 *    }
	 *
	 * @return void
	 */
	public function registration()
	{
		$model = $this->getModel('ijoomeradv');
		$jsonarray = $model->registration();

		if (!$jsonarray)
		{
			$jsonarray['code'] = IJReq::getResponseCode();
			$jsonarray['message'] = IJReq::getResponseMessage();
			$this->outputJSON($jsonarray);
		}

		$this->outputJSON($jsonarray);
	}

	/**
	 * The ResetPassword Function
	 *
	 * @uses    this function is used to retrive password
	 * @example the json string will be like, :
	 *    {
	 *        "task":"resetPassword",
	 *        "taskData":{
	 *            "step":"1/2/3",
	 *            "email":"email", (if step1),
	 *            "username":"username", (if step2)
	 *            "token":"token", (if step2)
	 *            "crypt":"crypt", (if step3)
	 *            "userid":"userid", (if step3)
	 *            "password":"password" (if step3)
	 *        }
	 *    }
	 *
	 * @return void
	 */
	public function resetPassword()
	{
		$model = $this->getModel('ijoomeradv');
		$step = IJReq::getTaskData('step', 1, 'int');

		switch ($step)
		{
			case 3:
				$jsonarray = $model->resetPassword();
				break;
			case 2:
				$jsonarray = $model->validateToken();
				break;
			case 1:
			default:
				$jsonarray = $model->retriveToken();
				break;
		}

		if (!$jsonarray)
		{
			$jsonarray['code'] = IJReq::getResponseCode();
			$jsonarray['message'] = IJReq::getResponseMessage();
			$this->outputJSON($jsonarray);
		}

		$this->outputJSON($jsonarray);
	}

	/**
	 * The RetriveUserName Function
	 *
	 * @uses    this function is use to retrive username
	 * @example the json string will be like, :
	 *    {
	 *        "task":"retriveUsername",
	 *        "taskData":{
	 *            "email":"email"
	 *        }
	 *    }
	 *
	 * @return void
	 */
	public function retriveUsername()
	{
		$model = $this->getModel('ijoomeradv');
		$jsonarray = $model->retriveUsername();

		if (!$jsonarray)
		{
			// If return value is false
			// get response code
			$jsonarray['code'] = IJReq::getResponseCode();

			// Get response message
			$jsonarray['message'] = IJReq::getResponseMessage();
			$this->outputJSON($jsonarray);
		}

		$this->outputJSON($jsonarray);
	}

	/**
	 * The ContactUs Function
	 *
	 * @uses    this function is use to mail of contactUs Form
	 * @example the json string will be like, :
	 *    {
	 *        "task":"contactUs",
	 *        "taskData":{
	 *            "form":"form"(1/0)(if 1 then form,toID,menuID)
	 *            "toID":"toID",
	 *            "menuID":"menuID"
	 *            "name":"name",
	 *            "email":"email",
	 *            "subject":"subject",
	 *            "message":"message"
	 *        }
	 *    }
	 *
	 * @return void
	 */

	public function contactUs()
	{
		$form   = IJReq::getTaskData('form');
		$toID   = IJReq::getTaskData('toID');
		$menuID = IJReq::getTaskData('menuID');
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);

		// Create the base select statement.
		$query->select('menuoptions')
			->from($db->qn('#__ijoomeradv_menu'))
			->where($db->qn('id') . ' = ' . $db->q($menuID));

		// Set the query and load the result.
		$db->setQuery($query);

		$options     = $db->loadObjectList();
		$menuoptions = json_decode($options[0]->menuoptions);
		$serverUse   = $menuoptions->serverUse;
		$remoteUse   = $menuoptions->remoteUse;

		if($form == 1)
		{
			$query  = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->qn('#__contact_details'))
				->where($db->qn('id') . ' = ' . $db->q($toID));

			// Set the query and load the result.
			$db->setQuery($query);

			$row = $db->loadObject();

			$count = count($row);

			if ($count <= 0)
			{
				$jsonarray['code'] = 204;
				IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);
				$this->outputJSON($jsonarray);
			}
			else
			{
				$jsonarray['code'] = 200;
			}

			$jsonarray['contact']['id'] = $row->id;
			$jsonarray['contact']['name'] = ($serverUse->showName == 1) ? $row->name : "";
			$jsonarray['contact']['position'] = ($serverUse->showPosition == 1) ? $row->con_position : "";
			$jsonarray['contact']['address'] = ($serverUse->showStreet == 1) ? $row->address : "";
			$jsonarray['contact']['state'] = ($serverUse->showState == 1) ? $row->state : "";
			$jsonarray['contact']['country'] = ($serverUse->showCountry == 1) ? $row->country : "";
			$jsonarray['contact']['postcode'] = ($serverUse->showPostalCode == 1) ? $row->postcode : "";
			$jsonarray['contact']['city'] = ($serverUse->showCity == 1) ? $row->suburb : "";
			$jsonarray['contact']['telephone'] = ($serverUse->showTelephone == 1) ? $row->telephone : "";
			$jsonarray['contact']['fax'] = ($serverUse->showFax == 1) ? $row->fax : "";
			$jsonarray['contact']['mobile'] = ($serverUse->showMobile == 1) ? $row->mobile : "";
			$jsonarray['contact']['webpage'] = ($serverUse->showWebpage == 1) ? $row->webpage : "";
			$jsonarray['contact']['misc'] = ($serverUse->showMiscInfo == 1) ? strip_tags($row->misc) : "";
			$jsonarray['contact']['emailTo'] = ($serverUse->showEmail == 1) ? $row->email_to : "";
			$jsonarray['contact']['image'] = ($serverUse->showMiscImage == 1) ? JURI::base() . $row->image : "";

			$decodeParams = json_decode($row->params);

			if ($decodeParams->linka_name || $decodeParams->linka)
			{
				$jsonarray['contact']['links'][0]['caption'] = $decodeParams->linka_name;
				$jsonarray['contact']['links'][0]['url'] = $decodeParams->linka;
			}

			if ($decodeParams->linkb_name || $decodeParams->linkb)
			{
				$jsonarray['contact']['links'][1]['caption'] = $decodeParams->linkb_name;
				$jsonarray['contact']['links'][1]['url'] = $decodeParams->linkb;
			}

			if ($decodeParams->linkc_name || $decodeParams->linkc)
			{
				$jsonarray['contact']['links'][2]['caption'] = $decodeParams->linkc_name;
				$jsonarray['contact']['links'][2]['url'] = $decodeParams->linkc;
			}

			if ($decodeParams->linkd_name || $decodeParams->linkd)
			{
				$jsonarray['contact']['links'][3]['caption'] = $decodeParams->linkd_name;
				$jsonarray['contact']['links'][3]['url'] = $decodeParams->linkd;
			}

			if ($decodeParams->linke_name || $decodeParams->linke)
			{
				$jsonarray['contact']['links'][4]['caption'] = $decodeParams->linke_name;
				$jsonarray['contact']['links'][4]['url'] = $decodeParams->linke;
			}

			$this->outputJSON($jsonarray);
		}
		else
		{
			$name = IJReq::getTaskData('name');
			$email = IJReq::getTaskData('email');
			$subject = IJReq::getTaskData('subject');
			$message = IJReq::getTaskData('message');
			$thankYouText = $serverUse->thankYouText;
			$sendCopy = $serverUse->sendCopy;
			$data = array();
			$data['contact_name'] = $name;
			$data['contact_email'] = $email;
			$data['contact_subject'] = $subject;
			$data['contact_message'] = $message;

			if ($sendCopy == 'on')
			{
				$data['contact_email_copy'] = $sendCopy;
			}

			$app = JFactory::getApplication();
			require_once JPATH_SITE . '/components/com_contact/models/contact.php';
			$ContactModelContact = new ContactModelContact;
			$params = JComponentHelper::getParams('com_contact');
			$contact = $ContactModelContact->getItem($toID);
			$params->merge($contact->params);

			// Send the email
			$sent = false;

			if (!$params->get('custom_reply'))
			{
				$sent = $this->_sendEmail($data, $contact);
				$jsonarry['code'] = 200;
				$jsonarry['message'] = $thankYouText;
				$this->outputJSON($jsonarry);
			}

			return true;
		}
	}

	/**
	 * The SendEmail Function
	 *
	 * @param   [type]  $data     $data
	 * @param   [type]  $contact  $contact
	 *
	 * @return  it will return $sent
	 */
	public function _sendEmail($data, $contact)
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_contact');

		if ($contact->email_to == '' && $contact->user_id != 0)
		{
			$contact_user = JUser::getInstance($contact->user_id);
			$contact->email_to = $contact_user->get('email');
		}

		$mailfrom = $app->getCfg('mailfrom');
		$fromname = $app->getCfg('fromname');
		$sitename = $app->getCfg('sitename');
		$copytext = JText::sprintf('COM_IJOOMERADV_COPYTEXT_OF', $contact->name, $sitename);

		$name = $data['contact_name'];
		$email = $data['contact_email'];
		$subject = $data['contact_subject'];
		$body = $data['contact_message'];

		// Prepare email body
		$prefix = JText::sprintf('COM_IJOOMERADV_ENQUIRY_TEXT', JURI::base());
		$body = $prefix . "\n" . "from:" . $name . ' <' . $email . '>' . "\r\n\r\n" . stripslashes($body);

		$mail = JFactory::getMailer();
		$mail->addRecipient($contact->email_to);
		$mail->addReplyTo(array($email, $name));
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename . ': ' . $subject);
		$mail->setBody($body);
		$sent = $mail->Send();

		// If we are supposed to copy the sender, do so.
		// Check whether email copy function activated
		if (array_key_exists('contact_email_copy', $data))
		{
			$copytext = JText::sprintf('COM_IJOOMERADV_COPYTEXT_OF', $contact->name, $sitename);
			$copytext .= "\r\n\r\n" . $body;
			$copysubject = JText::sprintf('COM_IJOOMERADV_COPYSUBJECT_OF', $subject);

			$mail = JFactory::getMailer();
			$mail->addRecipient($email);
			$mail->addReplyTo(array($email, $name));
			$mail->setSender(array($mailfrom, $fromname));
			$mail->setSubject($copysubject);
			$mail->setBody($copytext);
			$sent = $mail->Send();
		}

		return $sent;
	}

	/**
	 * The Verbose Function
	 *
	 * @return  void
	 */
	public function verbose()
	{
		echo '<b>iJoomer Advance : <b>';
		echo IJADV_VERSION;
		echo '<br/><br/>Extensions:<br/>';
		$model = $this->getModel('ijoomeradv');
		$extensions = $model->getExtensions();

		foreach ($extensions as $extension)
		{
			echo '<br/>&nbsp;&nbsp;&nbsp;' . $extension->name . ' : ';
			$mainXML = JPATH_SITE . '/components/com_ijoomeradv/extensions/' . $extension->classname . '.xml';

			if (is_file($mainXML))
			{
				if ($xml = simplexml_load_file($mainXML))
				{
					$version = $xml->xpath('version');
					$version = (double) $version[0][0];
				}
			}

			echo $version;

			if ($extension->name != 'ICMS')
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('manifest_cache')
					->from($db->qn('#__extensions'))
					->where($db->qn('element') . ' = ' . $db->q($extension->option));

				// Set the query and load the result.
				$db->setQuery($query);

				$extension = $db->loadResult();

				$extension = json_decode($extension);

				if ($extension->version)
				{
					echo ' / ' . $extension->version;
				}
			}
			else
			{
				echo ' / ' . IJ_JOOMLA_VERSION;
			}
		}
	}
}
