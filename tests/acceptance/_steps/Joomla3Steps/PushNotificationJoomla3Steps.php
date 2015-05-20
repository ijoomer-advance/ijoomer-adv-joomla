<?php
/**
 * @package     ijoomer
 * @subpackage  Step Class
 * @copyright   Copyright (C) 2008 - 2015 ijoomer.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace AcceptanceTester;
use Codeception\Module\WebDriver;

/**
 * Class CategoryManagerJoomla3Steps
 *
 * @package  AcceptanceTester
 *
 * @since    1.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */
class PushNotificationJoomla3Steps extends AdminManagerJoomla3Steps
{
	/**
	 * Function  to Create a New Category
	 *
	 * @param   String  $categoryName  Name of the Category
	 *
	 * @return void
	 */
	public function addCategoryForAndroid()
	{
		$I = $this;
		$I->amOnPage(\PushNotificationJ3Page::$URL);
		$I->see('Push Notification');
		$I->verifyNotices(false, $this->checkForNotices(), 'Push Notification Page');
		$I->click(\PushNotificationJ3Page::$AndroidLink);
		$I->click(\PushNotificationJ3Page::$AllUserLink);
		$I->fillField(\PushNotificationJ3Page::$TextMessage, 'Testing1');
		$I->fillField(\PushNotificationJ3Page::$TextLink, 'Test Link');
		$I->click("Send");
		$I->see('Push Notification Sent.');
	}
	public function addCategoryForIphone()
	{
		$I = $this;
		$I->amOnPage(\PushNotificationJ3Page::$URL);
		$I->see('Push Notification');
		$I->verifyNotices(false, $this->checkForNotices(), 'Push Notification Page');
		$I->click(\PushNotificationJ3Page::$IphoneLink);
		$I->click(\PushNotificationJ3Page::$AllUserLink);
		$I->fillField(\PushNotificationJ3Page::$TextMessage, 'Testing2');
		$I->fillField(\PushNotificationJ3Page::$TextLink, 'Test Link');
		$I->click("Send");
		$I->see('Push Notification Sent.');
	}
	public function addCategoryForBoth()
	{
		$I = $this;
		$I->amOnPage(\PushNotificationJ3Page::$URL);
		$I->see('Push Notification');
		$I->verifyNotices(false, $this->checkForNotices(), 'Push Notification Page');
		$I->click(\PushNotificationJ3Page::$BothLink);
		$I->click(\PushNotificationJ3Page::$AllUserLink);
		$I->fillField(\PushNotificationJ3Page::$TextMessage, 'Testing3');
		$I->fillField(\PushNotificationJ3Page::$TextLink, 'Test Link');
		$I->click("Send");
		$I->see('Push Notification Sent.');
	}

	public function searchForSelectToUser()
	{
		$I = $this;
		$I->amOnPage(\PushNotificationJ3Page::$URL);
		$I->see('Push Notification');
		$I->verifyNotices(false, $this->checkForNotices(), 'Push Notification Page');
		$I->click(\PushNotificationJ3Page::$AndroidLink);
		$I->click(\PushNotificationJ3Page::$CustomLink);
		$I->wait(2);
		$I->fillField(\PushNotificationJ3Page::$SelectSomeOption, 'Test');
		$I->wait(2);
		$I->see($categoryName, \PushNotificationJ3Page::$SelectSomeOption);
		$I->fillField(\PushNotificationJ3Page::$TextMessage, 'Testing1');
		$I->fillField(\PushNotificationJ3Page::$TextLink, 'Test Link');
		$I->click("Send");
		$I->see('Push Notification Sent.');

		
	}



	public function deleteCategory()
	{
		$I = $this;
		$I->amOnPage(\PushNotificationJ3Page::$URL);
		$I->click(\PushNotificationJ3Page::$checkallCheckBox);
		$I->click("Delete");
		//$I->acceptPopup();
		$I->see('Notification Deleted Successfully');
	}
}
