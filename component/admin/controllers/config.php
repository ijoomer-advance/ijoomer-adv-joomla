<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  config
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die;

class ijoomeradvControllerconfig extends JControllerLegacy
{
	public function home()
	{
		$this->setRedirect('index.php?option=com_ijoomeradv', null);
	}

	function save()
	{
		$model = $this->getModel('config');
		$config = $model->store();
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv', true));
	}

	function cancel()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv', true));
	}
}