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
class ExtensionJoomla3Steps extends AdminManagerJoomla3Steps
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
		$I->amOnPage(\ExtensionJ3Page::$URL);
		$I->verifyNotices(false, $this->checkForNotices(), 'Extension Page');
		$I->click("New");
		$I->verifyNotices(false, $this->checkForNotices(), 'Install/Update Extesions Page New');
		$I->click("Cancel");


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
