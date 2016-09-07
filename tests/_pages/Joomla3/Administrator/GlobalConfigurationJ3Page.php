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
class GlobalConfigurationJ3Page
{
	// Include url of current page
	public static $URL = '/administrator/index.php?option=com_ijoomeradv&view=config';
	
	public static $GlobalConfigLink = "//a[text()='Global Config']";

	public static $ThemeConfigLink = "//a[text()='Theme Config']";

	public static $PushNotificationConfigLink = "//a[text()='Push Notification Config']";

	public static $EncryptionLink = "//a[text()='Encryption']";

	public static $Errormessage="; echo 'View File'; } ?>";



}
