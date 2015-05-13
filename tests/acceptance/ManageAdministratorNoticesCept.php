<?php
/**
 * @package     ijoomer
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 ijoomer.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
/*echo getcwd();
error_reporting(E_ALL | E_STRICT);
ini_set("display_errors",1);*/
//include_once('../_pages/Joomla3/Administrator/AdminManagerPage.php');
$scenario->group('Joomla2');
$scenario->group('Joomla3');

// Load the Step Object Page

$I = new AcceptanceTester($scenario);
$config = $I->getConfig();
$className = 'AcceptanceTester\Login' . $config['env'] . 'Steps';
$I = new $className($scenario);

$I->wantTo('Test Presence of Notices, Warnings on Administrator');
$I->doAdminLogin();
$config = $I->getConfig();
$className = 'AcceptanceTester\AdminManager' . $config['env'] . 'Steps';
$I = new $className($scenario);
$I->CheckAllLinks();
