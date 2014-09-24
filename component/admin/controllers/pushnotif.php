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

defined('_JEXEC') or die;

jimport ('joomla.application.component.controller');

class ijoomeradvControllerPushnotif extends JControllerLegacy
{
	/**
	 * $app  application context object
	 *
	 * @var  object
	 */
	public $app;

	function __construct($default = array())
	{
		$this->app = JFactory::getApplication();
		parent::__construct ( $default );
	}

	function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	public function home()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv'), null);
	}

	function add()
	{
		$this->app->input->set('layout', 'detail');
		$this->app->input->set('hidemainmenu', 1);

		parent::display ();
	}

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

	function sendPushNotification()
	{
		$model = $this->getModel('pushnotif');

		$model->send_push_notification($device_token, $message = '', $badge = 1, $type = '');
		parent::display();
	}
}