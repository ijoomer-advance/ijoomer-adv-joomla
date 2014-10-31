<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.controller
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class IJoomeradvControllerConfig will extends JControllerLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.controller
 * @since       1.0
 */
class IjoomeradvControllerconfig extends JControllerLegacy
{
	/**
	 * Home Function For Redirecting To Home
	 *
	 * @return  void
	 */
	public function home()
	{
		$this->setRedirect('index.php?option=com_ijoomeradv', null);
	}

	/**
	 * Save Function
	 *
	 * @return  void
	 */
	public function save()
	{
		$model  = $this->getModel('config');
		$config = $model->store();
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv', true));
	}

	/**
	 * Save Function
	 *
	 * @return  void
	 */
	public function cancel()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv', true));
	}
}
