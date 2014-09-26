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

define('IJADV_VERSION',1.4);
defined ( 'DS' ) or define ( 'DS', DIRECTORY_SEPARATOR);

$document = JFactory::getDocument (); // get document object
$document->addStyleSheet('components'.DS.'com_ijoomeradv'.DS.'assets'.DS.'css'.DS.'ijoomeradv.css' ); // add ijoomeradv default style to document stack

jimport ('joomla.version'); // import version file
$version = new JVersion ( ); // create version object

defined ( 'IJ_JOOMLA_VERSION' ) or define ( 'IJ_JOOMLA_VERSION', floatval ( $version->RELEASE ) ); // define joomla version

defined ( 'IJ_ADMIN' )			or define ( 'IJ_ADMIN', 		JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_ijoomeradv' );
defined ( 'IJ_SITE' )			or define ( 'IJ_SITE', 			JPATH_COMPONENT );
defined ( 'IJ_ASSET' )			or define ( 'IJ_ASSET', 		IJ_SITE . DS . 'assets' );
defined ( 'IJ_CONTROLLER' ) 	or define ( 'IJ_CONTROLLER',	IJ_SITE . DS . 'controllers' );
defined ( 'IJ_EXTENSION' ) 		or define ( 'IJ_EXTENSION',		IJ_SITE . DS . 'extensions' );
defined ( 'IJ_HELPER' )			or define ( 'IJ_HELPER', 		IJ_SITE . DS . 'helpers' );
defined ( 'IJ_MODEL' )			or define ( 'IJ_MODEL', 		IJ_SITE . DS . 'models' );
defined ( 'IJ_TABLE' )			or define ( 'IJ_TABLE', 		IJ_SITE . DS . 'tables' );
defined ( 'IJ_VIEW' )			or define ( 'IJ_VIEW', 			IJ_SITE . DS . 'views' );

require_once (IJ_HELPER.DS.'helper.php'); // import ijoomeradv helper file
set_error_handler( array( 'ijoomeradvError', 'ijErrorHandler' ));//set custom error handler
$IJHelperObj= new ijoomeradvHelper(); // create hepler object
$IJHelperObj->getRequestedObject(); // get requested json object

//defined ( 'IJ_JOMSOCIAL_VERSION' ) or define ( 'IJ_JOMSOCIAL_VERSION', $IJHelperObj->getJomSocialVersion() ); // define jomsocial version

$controller = IJReq::getView(); // get the view
$path = IJ_CONTROLLER . DS . $controller . '.php';
if (file_exists ( $path )) { // check if controller file exist
	require_once ($path);
} else {
	$controller = '';
}

$classname = 'ijoomeradvController' . $controller;
$controller = new $classname(); // create controller class object

$task=IJReq::getTask();

$task = (!empty($task))? $task :((JRequest::getVar('task'))?JRequest::getVar('task'):NULL); // get the task to execute
$controller->execute ($task); // Perform the Request task

$controller->redirect(); // Redirect if set by the controller