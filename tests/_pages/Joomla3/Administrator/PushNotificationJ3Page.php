<?php

/**
 * @package     ijoomer
 * @subpackage  Page Class
 * @copyright   Copyright (C) 2008 - 2015 ijoomer.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class CategoryManagerJ3Page
 *
 * @since  1.4
 *
 * @link   http://codeception.com/docs/07-AdvancedUsage#PageObjects
 */
class PushNotificationJ3Page
{
	// Include url of current page
	public static $URL = '/administrator/index.php?option=com_ijoomeradv&view=pushnotif';
	
    public static $categoryName = "Test";

	public static $TextMessage = "//*[@id='jform_message']";

	public static $TextLink = "//*[@id='jform_link']";

	public static $AndroidLink ="//label[text()='Android']";

	public static $IphoneLink ="//label[text()='iPhone']";

	public static $BothLink ="//label[text()='Both']";

	public static $AllUserLink ="//label[text()='All Users']";

	public static $CustomLink ="//label[text()='Custom']";

	public static $SelectSomeOption ="//*[@id='jform_customsuser_chzn']/ul";

  	public static $checkallCheckBox ="//input[@name='checkall-toggle']";

}
