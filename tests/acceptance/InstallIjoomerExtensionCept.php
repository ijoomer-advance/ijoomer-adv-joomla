<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('log in as regular user');
$I->amOnPage('/administrator/index.php');
$I->fillField('username','admin');
$I->fillField('passwd','admin');
$I->click('Log in');
$I->waitForText('Control Panel',5);
$I->click('.//*[@id=\'menu\']/li[6]/a');
$I->click('//*[@id=\'menu\']/li[6]/ul/li[1]/a');
$I->waitForText('Extension Manager: Install',5);
$I->click('Install from Directory');
$I->fillField('//*[@id=\'install_directory\']','/home/travis/build/ijoomer-advance/ijoomer-adv-joomla');
$I->Click('//input[contains(@onclick,\'Joomla.submitbutton3()\')]');
$I->waitForText('Installing component was successful.',60);
$I->wait(15);
?>