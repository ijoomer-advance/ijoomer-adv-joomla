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
 * The Class For IJoomeradvControllerReport which will extends JControllerLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.controller
 * @since       1.0
 */
class IjoomeradvControllerReport extends JControllerLegacy
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
	 * Delete Function
	 *
	 * @return  void
	 */
	public function delete()
	{
		$model = $this->getModel('report');

		if (!$model->delete())
		{
			$msg = JText::_('COM_IJOOMERADV_REPORT_DELETE_ERROR');
		}
		else
		{
			$msg = JText::_('COM_IJOOMERADV_REPORT_DELETE');
		}

		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=report'), $msg);
	}

	/**
	 * Action Function
	 *
	 * @return  void
	 */
	public function action()
	{
		$action = JFactory::getApplication()->input->getString('action', '');
		$model = $this->getModel('report');

		if ($model->{$action}())
		{
			$msg = ($action == 'deletereport') ? JText::_('COM_IJOOMERADV_REPORT_ACTION_DELETE') : JText::_('COM_IJOOMERADV_REPORT_ACTION_IGNORE');
		}
		else
		{
			$msg = ($action == 'deletereport') ? JText::_('COM_IJOOMERADV_REPORT_ACTION_DELETE_ERROR') : JText::_('COM_IJOOMERADV_REPORT_ACTION_IGNORE_ERROR');
		}

		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=report'), $msg);
	}
}
