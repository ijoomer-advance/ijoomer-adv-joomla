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
class ExtensionManagerJ3Page
{
	// Include url of current page
	public static $URL = 'administrator/index.php?option=com_ijoomeradv&view=extensions&layout=manage';

	public static $ExtensionName = "//*[@id='editcell']/table/thead/tr/th[4]";

	public static $checkAll = "//input[@onclick='Joomla.isChecked(this.checked);']";

}
