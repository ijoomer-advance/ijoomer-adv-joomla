<?php
/**
 * @package     IJoomer
 * @subpackage  ijoomeradvVoice
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
if (version_compare(JVERSION, '1.6.0', 'ge'))
{
	jimport('joomla.html.parameter');
}

class plgSystemIjoomeradvVoice extends JPlugin
{
	public $plg_name = "ijoomeradvVoice";

	function OnBeforeRender()
	{
		$siteUrl = JURI::root(true);
		$pluginLivePath = $siteUrl . '/plugins/system/' . $this->plg_name;
		$document = JFactory::getDocument();
		$document->addScript($pluginLivePath . '/ijoomeradvVoice.js');
	}
}