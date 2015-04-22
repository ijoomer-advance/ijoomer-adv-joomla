<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('log in as regular user');
$I->amOnPage('/administrator/index.php');
$I->fillField("//*[@id='mod-login-username']", "admin");
$I->fillField("//*[@id='mod-login-password']", "admin");
$I->click('//*[@id=\'form-login\']/fieldset/div[3]/div/div/button');
$I->see('Users');
$I->wait(3);
$I->see('Extensions');
$I->wait(3);
$I->click('//*[@id=\'menu\']/li[5]/a/span');
$I->click('//*[@id=\'menu\']/li[5]/ul/li[3]/a');
//$I->click('//*[@id=\'menu-com-ijoomeradv\']/li[1]/a'); 
/*$I->click('//*[@id=\'submenu\']/li[2]/a');
$I->movBeack();
$I->click('//*[@id=\'submenu\']/li[3]/a');
$I->movBeack();
$I->click('//*[@id=\'submenu\']/li[4]/a');
$I->movBeack();
$I->click('//*[@id=\'submenu\']/li[5]/a');
$I->movBeack();
$I->click('//*[@id=\'submenu\']/li[6]/a');
$I->movBeack();
$I->click('//html/body/nav/div/div/div/ul[2]/li/a/b');
$I->click('//html/body/nav/div/div/div/ul[2]/li/ul/li[5]/a');
$I->wait(3); // secs*/
?>