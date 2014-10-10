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
 * The Class For IJoomeradvControllerPushnotifiaction which will extends JControllerLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.controller
 * @since       1.0
 */

class IjoomeradvControllerPushnotif extends JControllerLegacy
{
	/**
	 * The Home Function For Redirectiong To Home.
	 *
	 * @return  boolean  returns the link to Home.
	 */
	public function home()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv', true), null);
	}

	/**
	 * The Add Function
	 *
	 * @return boolean  returns the value in true or false
	 */
	function add()
	{
		$app = JFactory::getApplication();
		$app->input->set('layout', 'detail');
		$app->input->set('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * The Apply Function
	 *
	 * @return boolean  returns the value in true or false
	 */
	function apply()
	{
		$model = $this->getModel('pushnotif');

		if ($model->store())
		{
			$msg = JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_SAVED');
		}
		else
		{
			$msg = JText::_('COM_IJOOMERADV_ERROR_SAVING_PUSH_NOTIFICATION');
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = JRoute::_('index.php?option=com_ijoomeradv&view=pushnotif');
		$this->setRedirect($link, $msg);
	}

	/**
	 * The SendPushNotification Function
	 *
	 * @return  boolean  returns the value in true or false
	 */
	function sendPushNotification()
	{
		$model = $this->getModel('pushnotif');

		$model->send_push_notification($device_token, $message = '', $badge = 1, $type = '');
		parent::display();
	}
}
