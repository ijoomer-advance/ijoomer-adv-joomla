<?php
/**
 * iJoomer is a mobile platform which provides native applications for
 * iPhone, Android and BlackBerry and works in real-time sync with Joomla!
 * You can launch your very own Joomla! Mobile Apps on the respective appStore.
 * Users of your website will be able to download the Joomla Mobile Application
 * and install on the device.
 * For more info visit: http://www.ijoomer.com
 * For Technical Support: Forum - http://www.ijoomer.com/Forum/
 *
 * iJoomer is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License 2 as published by the
 * Free Software Foundation.
 *
 * You should have received a copy of the GNU General Public License
 * along with iJoomer; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

define('IJADV_VERSION', '1.5.1');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

// Get document object
$document = JFactory::getDocument();

// Add ijoomeradv default style to document stack
$document->addStyleSheet('components/com_ijoomeradv/assets/css/ijoomeradv.css');

// Import version file
jimport('joomla.version');

// Create version object
$version = new JVersion;

// Define joomla version
defined('IJ_JOOMLA_VERSION') or define('IJ_JOOMLA_VERSION', floatval($version->RELEASE));

defined('IJ_ADMIN') or define('IJ_ADMIN', JPATH_ROOT . '/administrator/components/com_ijoomeradv');
defined('IJ_SITE') or define('IJ_SITE', JPATH_COMPONENT);
defined('IJ_MEDIA') or define('IJ_MEDIA', IJ_SITE . '/media/com_ijoomeradv');
defined('IJ_CONTROLLER') or define('IJ_CONTROLLER', IJ_SITE . '/controllers');
defined('IJ_EXTENSION') or define('IJ_EXTENSION', IJ_SITE . '/extensions');
defined('IJ_HELPER') or define('IJ_HELPER', IJ_SITE . '/helpers');
defined('IJ_MODEL') or define('IJ_MODEL', IJ_SITE . '/models');
defined('IJ_TABLE') or define('IJ_TABLE', IJ_SITE . '/tables');
defined('IJ_VIEW') or define('IJ_VIEW', IJ_SITE . '/views');

// Import ijoomeradv helper file
require_once IJ_HELPER . '/helper.php';

// Set custom error handler
set_error_handler(array('ijoomeradvError', 'ijErrorHandler'));

// Create hepler object
$IJHelperObj = new ijoomeradvHelper;

// Get requested json object
$IJHelperObj->getRequestedObject();

// Get the view
$controller = IJReq::getView();
$path = IJ_CONTROLLER . '/' . $controller . '.php';

if (file_exists($path))
{
// Check if controller file exist
	require_once $path;
}
else
{
	$controller = '';
}

$classname = 'ijoomeradvController' . $controller;

// Create controller class object
$controller = new $classname;

$task = IJReq::getTask();

// Get the task to execute
$task = (!empty($task)) ? $task : ((JRequest::getVar('task')) ? JRequest::getVar('task') : null);

// Perform the Request task
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
