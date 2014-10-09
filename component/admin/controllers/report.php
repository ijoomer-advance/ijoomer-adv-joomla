<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  config
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class ijoomeradvControllerReport extends JControllerLegacy
{
	public function home()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv', true), null);
	}

	function delete()
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

	function action()
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