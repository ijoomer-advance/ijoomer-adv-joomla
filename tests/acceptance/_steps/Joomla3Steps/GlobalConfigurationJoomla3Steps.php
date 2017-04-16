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
class GlobalConfigurationJoomla3Steps extends AdminManagerJoomla3Steps
{
	/**
	 * Function  to Create a New Category
	 *
	 * @param   String  $categoryName  Name of the Category
	 *
	 * @return void
	 */
	public function addCategory()
	{
		$I = $this;
		$I->amOnPage(\GlobalConfigurationJ3Page::$URL);
		$I->verifyNotices(false, $this->checkForNotices(), 'Global Configuration Page');
		$I->click(\GlobalConfigurationJ3Page::$GlobalConfigLink);
		$I->verifyNotices(false, $this->checkForNotices(), 'Global Config Page ');
		$I->click(\GlobalConfigurationJ3Page::$ThemeConfigLink);
		$I->verifyNotices(false, $this->checkForNotices(), 'Theme Config Page');
		$I->click(\GlobalConfigurationJ3Page::$PushNotificationConfigLink);
		$I->verifyNotices(false, $this->checkForNotices(), 'Push Notification Config Page');
		$I->dontSee(\GlobalConfigurationJ3Page::$Errormessage);
		$I->click(\GlobalConfigurationJ3Page::$EncryptionLink);
		$I->verifyNotices(false, $this->checkForNotices(), 'Encryption Page');


		/*$I->fillField(\CategoryManagerJ3Page::$categoryName, $categoryName);
		$I->click(\CategoryManagerJ3Page::$categoryTemplateIDDropDown);
		$categoryManagerPage = new \CategoryManagerJ3Page;
		$I->click($categoryManagerPage->categoryTemplateID("compare_product"));
		$I->click(\CategoryManagerJ3Page::$categoryTemplateDropDown);
		$I->click($categoryManagerPage->categoryTemplate("list"));
		$I->click("Save & Close");
		$I->waitForElement(\CategoryManagerJ3Page::$categoryFilter, 30);
	    */
	}
	
}
