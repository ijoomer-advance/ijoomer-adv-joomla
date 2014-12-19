<?php
/**
 * iJoomer is a mobile platform which provides native applications for
 * iPhone, Android and BlackBerry and works in real-time sync with Joomla!
 * You can launch your very own Joomla! Mobile Apps on the respective appStore.
 * Users of your website will be able to download the Joomla Mobile Application
 * and install on the device.
 * For more info visit: http://www.ijoomer.com
 * For Technical Support: Forum - http://www.ijoomer.com/Forum/
 *
 * iJoomer is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License 2 as published by the
 * Free Software Foundation.
 *
 * You should have received a copy of the GNU General Public License
 * along with iJoomer; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package     IJoomer
 * @subpackage  com_ijoomeradv
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Menu Item Controller
 *
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv
 * @since       1.0
 */
class Com_IjoomeradvInstallerScript
{
	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $adapter)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Set default menu items if no menu present
		$query->select('count(*)')
			->from($db->qn('#__ijoomeradv_menu'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadResult();

		if ($result <= 0)
		{
			$query = "INSERT INTO `#__ijoomeradv_menu` (`id`, `title`, `menutype`, `note`, `type`, `published`, `access`, `views`, `home`, `ordering`, `requiredField`, `menuoptions` , `itemimage`) VALUES
					(1, 'Home', 1, 'This will lead user to home screen', 'Home', 1, 1, 'default.default.home.Home', 0, 1, 0, '', ''),
					(2, 'Featured', 1, 'This will list the featured Articles', 'Featured article', 1, 1, 'icms.articles.featured.IcmsFeaturedArticles', 1, 2, 0, '', ''),
					(3, 'Categories', 1, 'This will list all the categories of icms', 'All category', 1, 1, 'icms.categories.allCategories.IcmsAllCategory', 0, 3, 0, '', ''),
					(4, 'Saved', 1, 'This will list all the articles which are made favourite', 'Favourite articl', 1, 1, 'icms.articles.favourite.IcmsFavouriteArticles', 0, 4, 0, '', ''),
					(5, 'Home', 2, 'This will lead user to home screen', 'Home', 1, 1, 'default.default.home.Home', 0, 1, 0, '', ''),
					(6, 'Featured', 2, 'This will list the featured Articles', 'Featured article', 1, 1, 'icms.articles.featured.IcmsFeaturedArticles', 0, 2, 0, '', ''),
					(7, 'Categories', 2, 'This will list all the categories in Joomla Article', 'All category', 1, 1, 'icms.categories.allCategories.IcmsAllCategory', 0, 3, 0, '', ''),
					(8, 'Saved', 2, 'This will list all the articles which are made favourite', 'Favourite articl', 1, 1, 'icms.articles.favourite.IcmsFavouriteArticles', 0, 4, 0, '', ''),
					(9, 'External URL', 3, '', 'WebLink', 1, 1, 'default.default.web.Web', 0, 1, 1, '', ''),
					(10, 'Login', 3, 'This will lead user to login screen', 'Login', 1, 1, 'default.default.login.Login', 0, 2, 0, '', '')";
			$db->setQuery($query);
			$db->Query();
		}

		// Set default menu types if not installed
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('count(*)')
			->from($db->qn('#__ijoomeradv_menu_types'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadResult();

		if ($result <= 0)
		{
			$query = 'INSERT INTO `#__ijoomeradv_menu_types` (`id`, `menutype`, `title`, `description`, `position`, `screen`, `menuitem`) VALUES
					(1, \'\', \'Bottom Tab Bar Menu (iCMS)\', \'Bottom Tab Bar Menu of iCMS\', 3, \'{"icms":["categories.allCategories.IcmsAllCategory","categories.categoryBlog.IcmsCategoryBlog","categories.singleCategory.IcmsSingleCategory","articles.featured.IcmsFeaturedArticles","articles.archive.IcmsArchivedArticles","articles.singleArticle.IcmsSingleArticle","articles.favourite.IcmsFavouriteArticles"]}\', \'\'),
					(2, \'\', \'Home icon menu\', \'This will manage Home screen\', 1, \'\', \'\'),
					(3, \'\', \'Side Bar Slide Out Menu(iCMS)\', \'This is for side bar in all screens of iCMS\', 2, \'{"icms":["categories.allCategories.IcmsAllCategory","categories.categoryBlog.IcmsCategoryBlog","categories.singleCategory.IcmsSingleCategory","articles.featured.IcmsFeaturedArticles","articles.archive.IcmsArchivedArticles","articles.singleArticle.IcmsSingleArticle","articles.favourite.IcmsFavouriteArticles"]}\', \'\')';
			$db->setQuery($query);
			$db->Query();
		}

		$this->displayMessage();
	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $adapter)
	{
		$this->displayMessage();
	}

	/**
	 * Display iJoomer installation message
	 *
	 * @return  void
	 */
	public function displayMessage()
	{
		ob_start();
		?>
		<style type="text/css">
			.button-next {
				height: 45px;
				line-height: 45px;
				width: 250px;
				text-align: center;
				font-weight: bold;
				font-size: 12px;
				color: #000;
				border: solid 0px #690;
				border-radius: 5px;
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
				cursor: pointer;
				padding-top: 9px;
				text-shadow: 1px 1px 1px #AAAAAA;
				box-shadow: 0px 0px 9px #000000;

				background: rgb(73, 72, 75); /* Old browsers */
				background: -moz-linear-gradient(top, rgba(73, 72, 75, 1) 0%, rgba(55, 55, 57, 1) 26%, rgba(0, 0, 0, 1) 27%, rgba(255, 152, 51, 1) 28%, rgba(255, 118, 2, 1) 100%); /* FF3.6+ */
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, rgba(73, 72, 75, 1)), color-stop(26%, rgba(55, 55, 57, 1)), color-stop(27%, rgba(0, 0, 0, 1)), color-stop(28%, rgba(255, 152, 51, 1)), color-stop(100%, rgba(255, 118, 2, 1))); /* Chrome,Safari4+ */
				background: -webkit-linear-gradient(top, rgba(73, 72, 75, 1) 0%, rgba(55, 55, 57, 1) 26%, rgba(0, 0, 0, 1) 27%, rgba(255, 152, 51, 1) 28%, rgba(255, 118, 2, 1) 100%); /* Chrome10+,Safari5.1+ */
				background: -o-linear-gradient(top, rgba(73, 72, 75, 1) 0%, rgba(55, 55, 57, 1) 26%, rgba(0, 0, 0, 1) 27%, rgba(255, 152, 51, 1) 28%, rgba(255, 118, 2, 1) 100%); /* Opera 11.10+ */
				background: -ms-linear-gradient(top, rgba(73, 72, 75, 1) 0%, rgba(55, 55, 57, 1) 26%, rgba(0, 0, 0, 1) 27%, rgba(255, 152, 51, 1) 28%, rgba(255, 118, 2, 1) 100%); /* IE10+ */
				background: linear-gradient(to bottom, rgba(73, 72, 75, 1) 0%, rgba(55, 55, 57, 1) 26%, rgba(0, 0, 0, 1) 27%, rgba(255, 152, 51, 1) 28%, rgba(255, 118, 2, 1) 100%); /* W3C */
				filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#49484b', endColorstr='#ff7602', GradientType=0); /* IE6-9 */
			}

			.column {
				border-top: 0px;
				border-right: 1px solid #CCCCCC;
				border-bottom: 0px;
				border-left: 1px solid #AAAAAA;
				background-color: #FFFFDD;
				text-align: center;
				width: 50%;
				padding: 20px;
			}

		</style>

		<table width="81%" border="0" align="center" cellspacing="0px" cellpadding="10px">
			<tr>
				<td colspan="2">
					<div style="text-align:center;">
						<h1>Thank you for choosing,</h1>
						<?php $imgsrc = JURI::root() . 'media/com_ijoomeradv/images/ijoomeradv_logo.png'; ?>
						<img src="<?php echo $imgsrc; ?>" align="center">
						<br><font color="#105A8D" size="2"><b>Version 1.5</b></font></br>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div style="text-align:center;">
						<p>iJoomeradv is a mobile platform which provides native applications for iPhone, Android and
							BlackBerry and works in real-time sync with Joomla.</p>

						<p>iJoomer Advance provides Android SDK(iPhone SDK is in pipeline) for Joomla CMS. You can
							manage your mobile screens directly from the Administrative Panel of Joomla like you have
							used Joomla Menu Manager. You can customize mobile app by your own as source code of Mobile
							App is provided. Joomla Articles Component is FREE to use now. Visit iJoomer.com for more
							details.</p>
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
						<input type="button" class="button-next" onclick="window.location = 'http://www.ijoomer.com/'"
						       value="<?php echo JText::_('www.ijoomer.com'); ?>"/>
					</div>
				</td>
				<td class="column">
					<div style="text-align:center;">
						Click the below button to continue with iJoomeradv settings.<br/><br/>
						<input type="button" class="button-next"
						       onclick="window.location = 'index.php?option=com_ijoomeradv&view=config'"
						       value="<?php echo JText::_('Configuration Settings'); ?>"/>
					</div>
				</td>
			</tr>
		</table>

		<?php
		$html = ob_get_contents();
		@ob_end_clean();
		echo $html;
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  void
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Delete plugin config
		$query->select('*')
			->from($db->qn('#__ijoomeradv_extensions'));

		// Set the query and load the result.
		$db->setQuery($query);

		$rows = $db->loadObjectlist();

		for ($i = 0, $cnt = count($rows); $i < $cnt; $i++)
		{
			$query = "DROP TABLE `#__ijoomeradv_{$rows[$i]->classname}_config`";
			$db->setQuery($query);
			$db->Query();
		}
	}
}
