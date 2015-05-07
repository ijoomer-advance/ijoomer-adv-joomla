<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('log in as regular user');
$I->amOnPage('/administrator/index.php');
$I->fillField('username','admin');
$I->fillField('passwd','admin');
$I->click('Log in');
//$I->checkForPhpNoticesOrWarnings('administrator/index.php');
$I->see('Users');
$I->see('Extensions');
$I->click('Components');
$I->wait(1);
$I->moveMouseOver('//a[text()=\'iJoomer Advance\']');
$I->wait(1);
$I->click('//a[text()=\'Extensions\']');
$I->wait(10);
