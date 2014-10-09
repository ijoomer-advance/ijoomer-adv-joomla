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
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('IJADV_VERSION', 1.5);

jimport('joomla.version');
$version = new JVersion;

//define joomla version
defined('IJ_JOOMLA_VERSION') or define('IJ_JOOMLA_VERSION', floatval($version->RELEASE));

defined('IJ_ADMIN') or define('IJ_ADMIN', JPATH_COMPONENT);
defined('IJ_SITE') or define('IJ_SITE', JPATH_ROOT . '/components/com_ijoomeradv');
defined('IJ_ASSET') or define('IJ_ASSET', IJ_ADMIN . '/assets');
defined('IJ_CONTROLLER') or define('IJ_CONTROLLER', IJ_ADMIN . '/controllers');
defined('IJ_HELPER') or define('IJ_HELPER', IJ_ADMIN . '/helpers');
defined('IJ_MODEL') or define('IJ_MODEL', IJ_ADMIN . '/models');
defined('IJ_TABLE') or define('IJ_TABLE', IJ_ADMIN . '/tables');
defined('IJ_VIEW') or define('IJ_VIEW', IJ_ADMIN . '/views');

require_once IJ_HELPER . '/helper.php';

$document = JFactory::getDocument();
$document->addStyleSheet('components/com_ijoomeradv/assets/css/ijoomeradv.css');

$controller = JRequest::getVar('view', 'ijoomeradv');
$path = IJ_CONTROLLER . '/' . $controller . '.php';
if (file_exists($path))
{
	require_once $path;
}
else
{
	$classname = '';
}

$classname = 'ijoomeradvController' . $controller;
$controller = new $classname();

// Perform the Request task
$task = JRequest::getVar('task');
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
