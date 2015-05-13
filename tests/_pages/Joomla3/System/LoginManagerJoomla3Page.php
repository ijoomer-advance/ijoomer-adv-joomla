<?php
/**
 * @package     ijoomer
 * @subpackage  Page Class
 * @copyright   Copyright (C) 2008 - 2015 ijoomer.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class LoginManagerJoomla3Page
 *
 * @since  1.4
 *
 * @link   http://codeception.com/docs/07-AdvancedUsage#PageObjects
 */
class LoginManagerJoomla3Page
{
	// Include url of current page
	public static $URL = '/administrator/index.php';

	public static $userName = "username";

	public static $password = "passwd";

	public static $loginSuccessCheck = "//a//span[text() = 'Category Manager']";
	//public static $loginSuccessCheck1 = "//a[text()='Extensions']";
}
