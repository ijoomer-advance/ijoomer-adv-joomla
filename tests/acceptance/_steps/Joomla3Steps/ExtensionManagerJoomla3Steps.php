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
 * Class ExtensionManagerJoomla3Steps
 *
 * @package  AcceptanceTester
 *
 * @since    1.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */
class ExtensionManagerJoomla3Steps extends AdminManagerJoomla3Steps
{
	/**
	 * Function  to Create a New Extension
	 *
	 * @param   String  $ExtensionName  Name of the Extension
	 *
	 * @return void
	 */
	public function changeState($state = 'unpublish')
	{
		$I = $this;
		$I->amOnPage(\ExtensionManagerJ3Page::$URL);
		$I->verifyNotices(false, $this->checkForNotices(), 'Category Manager Page');
		$I->click(\ExtensionManagerJ3Page::$checkAll);	
	    if ($state == 'unpublish')
		{
			$I->click("Unpublish");
		}
		else
		{
			$I->click("Publish");
		}
	}

	public function changeState1($state = 'publish')
	{
		$I = $this;
		$I->amOnPage(\ExtensionManagerJ3Page::$URL);
		$I->verifyNotices(false, $this->checkForNotices(), 'Category Manager Page');
		$I->click(\ExtensionManagerJ3Page::$checkAll);	
	    if ($state == 'publish')
		{
			$I->click("publish");
		}
		else
		{
			$I->click("UnPublish");
		}
	}

		
}
