<?php
/**
 * @package     ijoomer
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 ijoomer.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Before executing this tests configuration.php is removed at tests/_groups/InstallationGroup.php
//$scenario->group('installationJ3');
$scenario->group('Joomla3');

// Load the Step Object Page

$I = new AcceptanceTester($scenario);
$config = $I->getConfig();
$className = 'AcceptanceTester\Login' . $config['env'] . 'Steps';
$I = new $className($scenario);


$I->wantTo('Set Error Reporting Level');
$I->doAdminLogin();
$config = $I->getConfig();
$className = 'AcceptanceTester\GlobalConfigurationManager' . $config['env'] . 'Steps';
$I = new $className($scenario);
$I->setErrorReportingLevel();

