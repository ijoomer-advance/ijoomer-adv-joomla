	<?php
/**
 * @package     ijoomer
 * @subpackage  Page Class
 * @copyright   Copyright (C) 2008 - 2015 ijoomer.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class AdminManagerPage
 *
 * @link   http://codeception.com/docs/07-AdvancedUsage#PageObjects
 *
 * @since  1.4
 */
class AdminManagerPage
{

	public static $allExtensionPages = array (
		
		'Extension' => '/administrator/index.php?option=com_ijoomeradv&view=extensions',
		'Menu Manager' => '/administrator/index.php?option=com_ijoomeradv&view=menus',
		//'Menu items' => '/administrator/index.php?option=com_ijoomeradv&view=items'
	);
}
