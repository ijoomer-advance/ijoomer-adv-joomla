<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;
?>

<table class="frontmenu">
	<tr>
		<td width="50%" valign="top">
			<div class="menucontainer">
				<div id="frontmenuitem">
					<a href="index.php?option=com_ijoomeradv&view=extensions">
						<img src="<?php echo JURI::root() . 'media/com_ijoomeradv/images/extensions.png';?>" alt="Extensions">
					</a>
				</div>
				<div id="frontmenuitem">
					<a href="index.php?option=com_ijoomeradv&view=extensions&layout=manage">
						<img src="<?php echo JURI::root() . 'media/com_ijoomeradv/images/extensionmanager.png';?>" alt="Extension Manager">
					</a>
				</div>
				<div id="frontmenuitem">
					<a href="index.php?option=com_ijoomeradv&view=config">
						<img src="<?php echo JURI::root() . 'media/com_ijoomeradv/images/configuration.png';?>" alt="Global Configuration">
					</a>
				</div>
				<div id="frontmenuitem">
					<a href="index.php?option=com_ijoomeradv&view=menus">
						<img src="<?php echo JURI::root() . 'media/com_ijoomeradv/images/menumanager.png';?>" alt="Manu Manager">
					</a>
				</div>
				<div id="frontmenuitem">
					<a href="index.php?option=com_ijoomeradv&view=pushnotif">
						<img src="<?php echo JURI::root() . 'media/com_ijoomeradv/images/pushnotification.png';?>" alt="Push Notification">
					</a>
				</div>
				<div id="frontmenuitem">
					<a href="index.php?option=com_ijoomeradv&view=report">
						<img src="<?php echo JURI::root() . 'media/com_ijoomeradv/images/report.png';?>" alt="Reports">
					</a>
				</div>
			</div>
		</td>
		<td width="50%" valign="top">
			<table>
				<tr>
					<td>
						<?php
						echo JHtml::_('tabs.start', 'tab_group_id');
						echo JHtml::_('tabs.panel', JText::_('COM_IJOOMERADV_GENERAL_NOTE'), 'panel_1_id');
						?>
						<br/>
						<fieldset>
							<legend>Creating Menu Items.</legend>
							<ul>
								<li>From the Joomla Admin Panel, iJoomer Advance will allow you to manage the menu items
									specific to the Mobile Application from the Joomla Admin panel. You can add a New
									Menu to any of the three pre-defined positions i.e. "Bottom Tab Bar", "Home Screen
									Menu" and "Slide-Out Menu". To do so, simply add a new menu from the admin panel,
									and assign it to any of the above three positions. Now, select the screen(s) where
									you want this menu to be display (remember the position, it will display on that
									position).<br/><img
										src="<?php echo JURI::root() . 'media/com_ijoomeradv/images/menuitmes_img.jpg'?>"
										style="width:100%"/></li>
								<li>items (similar to Joomla). 'Select Menu Type' will list up all the menu types
									available in the iJoomer app. Once you select the menu type, param in accordance
									with the menu type selected will be displayed. For example, Single Article menu type
									will allow you to select that particular article. Keep adding menu items as you
									like.
								</li>
							</ul>
						</fieldset>

						<br/>
						<fieldset>
							<legend>Testing On Android Device.</legend>
							<ul>
								<li>Now, the iJoomer Advance is installed, you can download the Android app from <a
										href="https://play.google.com/store/apps/details?id=com.ijoomer.src"
										title="iJoomer Advance @ Google Play">Google Play here</a> and start testing.
								</li>
								<li>Once you launch the app, you will be prompted to enter the URL of your website.
									Click Yes there and enter your Joomla URL. (If you are not getting this prompt, then
									KILL the application from Android and restart, it will ask again). It may happen
									that Facebook may not work there because of FB key settings etc.
								</li>
								<li>Now on, the full app will talk with your VERY own server and you can test out the
									functionality.
								</li>
								<li>Still got any queries, <a
										href="https://www.ijoomer.com/component/option,com_rsform/Itemid,67/"
										title="Contact Us">Contact Us</a> else, you can get to <a
										href="https://www.ijoomer.com/subscription-plans.html"
										title="Buy iJoomer Advance Now">Buy Now</a> section.
								</li>
							</ul>
						</fieldset>

						<fieldset>
							<legend>How Do I Get My Own App?</legend>
							<ul>
								<li>If you are an Android/iPhone Developer, you can simply download SDK which comes with
									Example Codes and design “As Seen On Demo App” and there on, start customizing the
									app as per your requirement.
								</li>
								<li>If you are a Consumer having no prior knowledge of Mobile Programming and want
									Professional help to Integrate theme and submit to Mobile Stores, you can buy
									iJoomer’s Consumer Plan get our assistance.
								</li>
								<li>Still got any queries, <a
										href="https://www.ijoomer.com/component/option,com_rsform/Itemid,67/"
										title="Contact Us">Contact Us</a>.
								</li>
							</ul>
						</fieldset>

						<fieldset>
							<legend>Which other components are supported?</legend>
							<ul>
								<li>iJoomer Advance integrates with several popular Joomla! Components. This can be
									achieved by using iJoomer Advance's extensions. All supported <a
										href="https://www.ijoomer.com/free-downloads.html"
										title="Other Supported Extensions">extensions are available here</a>.
								</li>
								<li>Downloaded package can be installed via <b>iJoomer Advance component ->
										Extensions</b>.
								</li>
							</ul>
						</fieldset>

						<fieldset>
							<legend>Technical Support.</legend>
							<ul>
								<li>If you require any technical support just trail on to the forums at <a
										href="http://www.ijoomer.com/Forum/" title="iJoomer Advance Forum">http://www.ijoomer.com/Forum/</a>
								</li>
								<li>For any additional support or inquiry, you can drop us an email at <a
										href="mailto:support@ijoomer.com" title="Mail Us">support@ijoomer.com</a></li>
							</ul>
						</fieldset>

						<fieldset>
							<legend>Getting involved.</legend>
							<ul>
								<li>If you have got any suggestions or feedback, please do share it with us at <a
										href="mailto:support@ijoomer.com" title="Mail Us">support@ijoomer.com</a></li>
								<li><b>Have fun using iJoomer Advance!</b></li>
							</ul>
						</fieldset>
						<?php
						echo JHtml::_('tabs.end');
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<div style="text-align:center;width:100%;">iJoomer Advance <?php echo IJADV_VERSION ?></div>
