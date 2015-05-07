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
$I->fillField('//*[@id=\'install_directory\']','/home/travis/build/ijoomer-advance/ijoomer-adv-joomla/');
$I->Click('//input[contains(@onclick,\'Joomla.submitbutton3()\')]');
$I->wait(50);
$I->see('Users');
$I->see('Extensions');
$I->click('Components');
$I->wait(1);
$I->moveMouseOver('//a[text()=\'iJoomer Advance\']');
$I->wait(1);
$I->click('//a[text()=\'Extensions\']');
$I->wait(10);
?>