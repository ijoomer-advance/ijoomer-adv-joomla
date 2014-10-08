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

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('IJADV_VERSION',1.5);

jimport('joomla.version');
$version = new JVersion();

//define joomla version
defined('IJ_JOOMLA_VERSION') or define('IJ_JOOMLA_VERSION', floatval ($version->RELEASE));

defined('IJ_ADMIN')			or define('IJ_ADMIN', JPATH_COMPONENT);
defined('IJ_SITE')			or define('IJ_SITE', JPATH_ROOT . '/components/com_ijoomeradv');
defined('IJ_ASSET')			or define('IJ_ASSET', IJ_ADMIN . '/assets');
defined('IJ_CONTROLLER') 	or define('IJ_CONTROLLER', IJ_ADMIN . '/controllers' );
defined('IJ_HELPER')		or define('IJ_HELPER', IJ_ADMIN . '/helpers');
defined('IJ_MODEL')			or define('IJ_MODEL', IJ_ADMIN . '/models');
defined('IJ_TABLE')			or define('IJ_TABLE', IJ_ADMIN . '/tables');
defined('IJ_VIEW')			or define('IJ_VIEW', IJ_ADMIN . '/views');

require_once IJ_HELPER.'/helper.php';

$document = JFactory::getDocument ();
$document->addStyleSheet('components/com_ijoomeradv/assets/css/ijoomeradv.css' );

$controller = JRequest::getVar ('view','ijoomeradv');
$path = IJ_CONTROLLER .'/'. $controller . '.php';
if (file_exists ( $path )) {
	require_once $path;
} else {
	$classname = '';
}

$classname = 'ijoomeradvController' . $controller;
$controller = new $classname();

// Perform the Request task
$task=JRequest::getVar('task');
$controller->execute ($task);

// Redirect if set by the controller
$controller->redirect();