<?php
/*--------------------------------------------------------------------------------
# com_ijoomeradv_1.5 - iJoomer Advanced
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );

function com_install(){	
	$db= & JFactory::getDBO();
	
	/*
	 * DEPRECATED
	 * 
	 * create report table
	 * This is to add ijoomeradv_report table.
	 * Need to remove it from here and add to main sql file.
	 */
	$query = "CREATE TABLE IF NOT EXISTS `#__ijoomeradv_report` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `message` varchar(255) NOT NULL,
				  `created_by` int(11) NOT NULL,
				  `created` varchar(255) NOT NULL,
				  `extension` varchar(255) NOT NULL,
				  `status` int(2) NOT NULL,
				  `params` text NOT NULL,
				  PRIMARY KEY (`id`)
				)";
	$db->setQuery($query);
	$db->Query();
	
	/* 
	 * DEPRECATED
	 * 
	 * This is to add link fild to ijoomeradv_push_notification table. 
	 * Need to remove it from here and add to main sql file.
	 */
	$query="SELECT COUNT(*) 
			FROM information_schema.COLUMNS 
			WHERE TABLE_SCHEMA=DATABASE()
			AND COLUMN_NAME='link' 
			AND TABLE_NAME='#__ijoomeradv_push_notification'";
	$db->setQuery($query);
	$result=$db->loadResult();
	
	if($result<=0){
		$query="ALTER TABLE `#__ijoomeradv_push_notification` 
				ADD `link` varchar(255) NOT NULL";
		$db->setQuery($query);
		$db->query();
	}
	/*****/

	/* 
	 * DEPRECATED
	 * 
	 * This is to add menudevice filed to ijoomeradv_menu table. 
	 * Need to remove it from here and add to main sql file.
	 */
	$query="SELECT COUNT(*) 
			FROM information_schema.COLUMNS 
			WHERE TABLE_SCHEMA=DATABASE()
			AND COLUMN_NAME='menudevice' 
			AND TABLE_NAME='#__ijoomeradv_menu'";
	$db->setQuery($query);
	$result=$db->loadResult();
	
	if($result<=0){
		$query="ALTER TABLE `#__ijoomeradv_menu` 
				ADD `menudevice` INT(1) NOT NULL DEFAULT '1' COMMENT 'Global:1,Android:2,Iphone:3,Both:4' 
				AFTER `type`";
		$db->setQuery($query);
		$db->query();
	}
	/*****/
	
	/* 
	 * DEPRECATED
	 * 
	 * This is to add menudevice filed to ijoomeradv_menu_types table. 
	 * Need to remove it from here and add to main sql file.
	 */
	$query="SELECT COUNT(*) 
			FROM information_schema.COLUMNS 
			WHERE TABLE_SCHEMA=DATABASE()
			AND COLUMN_NAME='menudevice' 
			AND TABLE_NAME='#__ijoomeradv_menu_types'";
	$db->setQuery($query);
	$result=$db->loadResult();
			
	if($result<=0){
		$query="ALTER TABLE `#__ijoomeradv_menu_types`  ADD `menudevice` INT(1) NOT NULL DEFAULT '1' COMMENT 'Both:1,Android:2,Iphone:3' AFTER `position`";
		$db->setQuery($query);
		$db->query();
	}
	/*****/
	
	/* 
	 * DEPRECATED
	 * 
	 * This is to add menudevice filed to ijoomeradv_menu_types table. 
	 * Need to remove it from here and add to main sql file.
	 */
	$query="SELECT COUNT(*) 
			FROM information_schema.COLUMNS 
			WHERE TABLE_SCHEMA=DATABASE()
			AND COLUMN_NAME='itemimage' 
			AND TABLE_NAME='#__ijoomeradv_menu'";
	$db->setQuery($query);
	$result=$db->loadResult();
			
	if($result<=0){
		$query="ALTER TABLE `#__ijoomeradv_menu`  ADD `itemimage` VARCHAR(255) NOT NULL";
		$db->setQuery($query);
		$db->query();
	}
	
	//change menuoption datatype
	$query="ALTER TABLE `#__ijoomeradv_menu` CHANGE `menuoptions` `menuoptions` TEXT NULL";
	$db->setQuery($query);
	$db->query();
	
	/*****/
	
	
	 
	
	// set default menu items if no menu present
	$query="SELECT count(*) 
			FROM #__ijoomeradv_menu";
	$db->setQuery($query);
	$result=$db->loadResult();
	
	if($result<=0){
		$query="INSERT INTO `#__ijoomeradv_menu` (`id`, `title`, `menutype`, `note`, `type`, `menudevice`, `published`, `access`, `views`, `home`, `ordering`, `requiredField`, `menuoptions` , `itemimage`) VALUES
				(1, 'Home', 1, 'This will lead user to home screen', 'Home', 1, 1, 1, 'default.default.home.Home', 0, 1, 0, '', ''),
				(2, 'Featured', 1, 'This will list the featured Articles', 'Featured article', 1, 1, 1, 'icms.articles.featured.IcmsFeaturedArticles', 1, 2, 0, '', ''),
				(3, 'Categories', 1, 'This will list all the categories of icms', 'All category', 1, 1, 1, 'icms.categories.allCategories.IcmsAllCategory', 0, 3, 0, '', ''),
				(4, 'Saved', 1, 'This will list all the articles which are made favourite', 'Favourite article', 1, 1, 1, 'icms.articles.favourite.IcmsFavouriteArticles', 0, 4, 0, '', ''),
				(5, 'Home', 2, 'This will lead user to home screen', 'Home', 1, 1, 1, 'default.default.home.Home', 0, 1, 0, '', ''),
				(6, 'Featured', 2, 'This will list the featured Articles', 'Featured article', 1, 1, 1, 'icms.articles.featured.IcmsFeaturedArticles', 0, 2, 0, '', ''),
				(7, 'Categories', 2, 'This will list all the categories in Joomla Article', 'All category', 1, 1, 1, 'icms.categories.allCategories.IcmsAllCategory', 0, 3, 0, '', ''),
				(8, 'Saved', 2, 'This will list all the articles which are made favourite', 'Favourite articl', 1, 1, 1, 'icms.articles.favourite.IcmsFavouriteArticles', 0, 4, 0, '', ''),
				(9, 'Login', 3, 'This will lead user to login screen', 'Login', 1, 1, 1, 'default.default.login.Login', 0, 1, 0, '', '')";
		$db->setQuery($query);
		$db->Query();
	}
	
	// set default menu types if not installed
	$query="SELECT count(*) 
			FROM #__ijoomeradv_menu_types";
	$db->setQuery($query);
	$result=$db->loadResult();
	
	if($result<=0){
		$query='INSERT INTO `#__ijoomeradv_menu_types` (`id`, `menutype`, `title`, `description`, `position`, `menudevice`, `screen`, `menuitem`) VALUES
				(1, \'\', \'Bottom Tab Bar Menu (iCMS)\', \'Bottom Tab Bar Menu of iCMS\', 3, 1,\'{"icms":["categories.allCategories.IcmsAllCategory","categories.categoryBlog.IcmsCategoryBlog","categories.singleCategory.IcmsSingleCategory","articles.featured.IcmsFeaturedArticles","articles.archive.IcmsArchivedArticles","articles.singleArticle.IcmsSingleArticle","articles.favourite.IcmsFavouriteArticles"]}\', \'\'),
				(2, \'\', \'Home icon menu\', \'This will manage Home screen\', 1, 1, \'\', \'\'),
				(3, \'\', \'Side Bar Slide Out Menu(iCMS)\', \'This is for side bar in all screens of iCMS\', 2, 1, \'{"icms":["categories.allCategories.IcmsAllCategory","categories.categoryBlog.IcmsCategoryBlog","categories.singleCategory.IcmsSingleCategory","articles.featured.IcmsFeaturedArticles","articles.archive.IcmsArchivedArticles","articles.singleArticle.IcmsSingleArticle","articles.favourite.IcmsFavouriteArticles"]}\', \'\')';
		$db->setQuery($query);
		$db->Query();
	}
	//Start-check if GD or ffmpeg library installed or not on server
	$ffmpeg = trim(shell_exec('which ffmpeg')); 
	if (empty($ffmpeg))
	{
		echo "<div class=\"library\">But it looks like ffmpeg library is not available on your server.</div>";
	}
	if (extension_loaded('gd') && function_exists('gd_info')) {
	    //nothing
	}else{
		echo "<div class=\"library\">But it looks like GD library is not available on your server.</div>";
	}
	//End
	ob_start();
	?>
	<style type="text/css">
		.library{
			color: red;
			font-weight: bold;
		}
		.button-next {
			height: 45px;
			line-height: 45px;
			width: 250px;
			text-align: center;
			font-weight: bold;
			font-size:12px;
			color: #000;
			border: solid 0px #690;
			border-radius:5px;
			-moz-border-radius:5px;
			-webkit-border-radius:5px;
			cursor: pointer;
			padding-top:9px;
			text-shadow:1px 1px 1px #AAAAAA;
			box-shadow: 0px 0px 9px #000000;
			
			background: rgb(73,72,75); /* Old browsers */
			background: -moz-linear-gradient(top,  rgba(73,72,75,1) 0%, rgba(55,55,57,1) 26%, rgba(0,0,0,1) 27%, rgba(255,152,51,1) 28%, rgba(255,118,2,1) 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(73,72,75,1)), color-stop(26%,rgba(55,55,57,1)), color-stop(27%,rgba(0,0,0,1)), color-stop(28%,rgba(255,152,51,1)), color-stop(100%,rgba(255,118,2,1))); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top,  rgba(73,72,75,1) 0%,rgba(55,55,57,1) 26%,rgba(0,0,0,1) 27%,rgba(255,152,51,1) 28%,rgba(255,118,2,1) 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top,  rgba(73,72,75,1) 0%,rgba(55,55,57,1) 26%,rgba(0,0,0,1) 27%,rgba(255,152,51,1) 28%,rgba(255,118,2,1) 100%); /* Opera 11.10+ */
			background: -ms-linear-gradient(top,  rgba(73,72,75,1) 0%,rgba(55,55,57,1) 26%,rgba(0,0,0,1) 27%,rgba(255,152,51,1) 28%,rgba(255,118,2,1) 100%); /* IE10+ */
			background: linear-gradient(to bottom,  rgba(73,72,75,1) 0%,rgba(55,55,57,1) 26%,rgba(0,0,0,1) 27%,rgba(255,152,51,1) 28%,rgba(255,118,2,1) 100%); /* W3C */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#49484b', endColorstr='#ff7602',GradientType=0 ); /* IE6-9 */
		}
		
		.column{
			border-top: 0px;
			border-right: 1px solid #CCCCCC;
			border-bottom: 0px;
			border-left: 1px solid #AAAAAA;
			background-color: #FFFFDD;
			text-align: center;
			width:50%;
			padding:20px;
		}
		
	</style>
	
	<table width="81%" border="0" align="center" cellspacing="0px" cellpadding="10px">
		<tr>
			<td colspan="2">
				<div style="text-align:center;">
					<h1>Thank you for choosing,</h1>
					<img src="components/com_ijoomeradv/assets/images/ijoomeradv_logo.png" align="center">
					<br><font color="#105A8D" size="2"><b>Version 1.5</b></font></br>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div style="text-align:center;">
					<p>iJoomeradv is a mobile platform which provides native applications for iPhone, Android and BlackBerry and works in real-time sync with Joomla.</p>
					<p>iJoomer Advance provides Android SDK(iPhone SDK is in pipeline) for Joomla CMS. You can manage your mobile screens directly from the Administrative Panel of Joomla like you have used Joomla Menu Manager. You can customize mobile app by your own as source code of Mobile App is provided. Joomla Articles Component is FREE to use now. Visit iJoomer.com for more details.</p> 
				</div>
			</td>
		</tr>
		<tr>
			<td class="column">
				<p><b>How to test it with my app?</b></p>
				<b>STEP 1:</b> Download Android Demo App From Google Play here.<br/>
				<b>STEP 2:</b> Enter your URL in the app.<br/>
				<b>STEP 3:</b> There is no third step.
			</td>
			
			<td class="column">
				<p><b>How to customize my App?</b></p>
				- Are you Android developer? Download Android Source Code here.<br/>
				- Not a developer? The World doesn't end here!! Contact Developer Team here.
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="column">
				<div style="text-align:center;">
					To know more about iJoomeradv, Please visit<br/><br/>
					<input type="button" class="button-next" onclick="window.location = 'http://www.ijoomer.com/'" value="<?php echo JText::_('www.ijoomer.com');?>" />
				</div> 
			</td>
			<td class="column">
				<div style="text-align:center;">				
					Click the below button to continue with iJoomeradv settings.<br/><br/>
					<input type="button" class="button-next" onclick="window.location = 'index.php?option=com_ijoomeradv&view=config'" value="<?php echo JText::_('Configuration Settings');?>"/>
				</div>
			</td>
		</tr>
	</table>
	
	<?php
	$html = ob_get_contents();
	@ob_end_clean();
	echo $html;
}