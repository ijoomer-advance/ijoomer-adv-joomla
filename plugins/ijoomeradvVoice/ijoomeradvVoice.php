<?php
/**
 * @copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
 * @license GNU/GPL, see license.txt or http://www.gnu.org/copyleft/gpl.html
 * Developed by Tailored Solutions - ijoomer.com
 *
 * ijoomer can be downloaded from www.ijoomer.com
 * ijoomer is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * You should have received a copy of the GNU General Public License
 * along with ijoomer; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
if (version_compare(JVERSION, '1.6.0', 'ge')){
	jimport('joomla.html.parameter');
}

class plgSystemIjoomeradvVoice extends JPlugin
{
	public $plg_name	= "ijoomeradvVoice";

	function OnBeforeRender(){
		$siteUrl  = JURI::root(true);
		$pluginLivePath = $siteUrl.'/plugins/system/'.$this->plg_name;
		$document  = JFactory::getDocument();
		$document->addScript($pluginLivePath.'/ijoomeradvVoice.js');
	}
}