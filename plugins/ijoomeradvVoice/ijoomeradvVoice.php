<?php
/**
 * @package     IJoomer
 * @subpackage  ijoomeradvVoice
 *
 * @copyright   Copyright (C) 2010 - 2015 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

if (version_compare(JVERSION, '1.6.0', 'ge'))
{
	jimport('joomla.html.parameter');
}

/**
 * The Plugin System Ijoomeradv voice Function
 *
 * @since  1.0
 */
class PlgSystemIjoomeradvVoice extends JPlugin
{
	public $plg_name = "ijoomeradvVoice";

	/**
	 * The On Before Render Function
	 *
	 * @return void
	 */
	public function OnBeforeRender()
	{
		$siteUrl = JURI::root(true);
		$pluginLivePath = $siteUrl . '/plugins/system/' . $this->plg_name;
		$document = JFactory::getDocument();
		$document->addScript($pluginLivePath . '/ijoomeradvVoice.js');
	}
}
