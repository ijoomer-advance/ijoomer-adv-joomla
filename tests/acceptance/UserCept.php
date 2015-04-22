<?php
//namespace AcceptanceTester;
//include('/var/www/html/iJoomer/tests/acceptance/AcceptanceTester.php');


use Codeception\Module\WebDriver;
use Codeception\Module\AcceptanceHelper;


class UserCept extends AcceptanceTester
{
   
      public function checkLogin() 
     {
         $I = $this;
         $I->wantTo('Signup and check i get signed in automaticly');
         $I->amOnUrl('http://store.demoqa.com/');
         $I->see('Your Account');
         $I->click('//*[@id=\'account\']/a');
         $I->fillField("//*[@id='log']", "testuser_1");
         $I->fillField("//*[@id='pwd']", "Test@123");
         $I->click('//*[@id=\'login\']');
         $I->see('My Account');
     }
}
    $J = new AcceptanceTester();
    $J->$checkLogin();


?>