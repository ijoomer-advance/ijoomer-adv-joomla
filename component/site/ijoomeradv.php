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

define('IJADV_VERSION', 1.4);
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

$document = JFactory::getDocument(); // get document object
$document->addStyleSheet('components/com_ijoomeradv/assets/css/ijoomeradv.css'); // add ijoomeradv default style to document stack

jimport('joomla.version'); // import version file
$version = new JVersion (); // create version object

defined('IJ_JOOMLA_VERSION') or define('IJ_JOOMLA_VERSION', floatval($version->RELEASE)); // define joomla version

defined('IJ_ADMIN') or define('IJ_ADMIN', JPATH_ROOT . '/administrator/components/com_ijoomeradv');
defined('IJ_SITE') or define('IJ_SITE', JPATH_COMPONENT);
defined('IJ_ASSET') or define('IJ_ASSET', IJ_SITE . '/assets');
defined('IJ_CONTROLLER') or define('IJ_CONTROLLER', IJ_SITE . '/controllers');
defined('IJ_EXTENSION') or define('IJ_EXTENSION', IJ_SITE . '/extensions');
defined('IJ_HELPER') or define('IJ_HELPER', IJ_SITE . '/helpers');
defined('IJ_MODEL') or define('IJ_MODEL', IJ_SITE . '/models');
defined('IJ_TABLE') or define('IJ_TABLE', IJ_SITE . '/tables');
defined('IJ_VIEW') or define('IJ_VIEW', IJ_SITE . '/views');

require_once IJ_HELPER . '/helper.php'; // import ijoomeradv helper file
set_error_handler(array('ijoomeradvError', 'ijErrorHandler'));//set custom error handler
$IJHelperObj = new ijoomeradvHelper; // create hepler object
$IJHelperObj->getRequestedObject(); // get requested json object

$controller = IJReq::getView(); // get the view
$path = IJ_CONTROLLER . '/' . $controller . '.php';
if (file_exists($path))
{ // check if controller file exist
	require_once $path;
}
else
{
	$controller = '';
}

$classname = 'ijoomeradvController' . $controller;
$controller = new $classname(); // create controller class object

$task = IJReq::getTask();

$task = (!empty($task)) ? $task : ((JRequest::getVar('task')) ? JRequest::getVar('task') : NULL); // get the task to execute
$controller->execute($task); // Perform the Request task

$controller->redirect(); // Redirect if set by the controller
