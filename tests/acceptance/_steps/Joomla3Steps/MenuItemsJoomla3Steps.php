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
class MenuItemsJoomla3Steps extends AdminManagerJoomla3Steps
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
		$I->amOnPage(\MenuItemsJ3Page::$URL);
		$I->verifyNotices(false, $this->checkForNotices(), 'Menu Items Page');
		$I->click("New");
		$I->verifyNotices(false, $this->checkForNotices(), 'Menu Items Page New');
	}
	
}
