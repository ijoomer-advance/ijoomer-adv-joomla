<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  config
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die;

class ijoomeradvControllerExtensions extends JControllerLegacy
{
	public function home()
	{
		$this->setRedirect('index.php?option=com_ijoomeradv', null);
	}

	function add()
	{
		JFactory::getApplication()->input->set('layout', 'install');

		parent::display();
	}

	function detail()
	{
		$app = JFactory::getApplication();
		$app->input->set('layout', 'detail');
		$app->input->set('hidemainmenu', 1);

		parent::display();
	}

	function save()
	{
		$app = JFactory::getApplication();
		$post = $app->input->get('post');
		$task = $app->input->get('task');
		$model = $this->getModel('extensions');

		if ($model->setExtConfig($post))
		{
			$msg = JText::_('COM_IJOOMERADV_CONFIG_SAVED');
		}
		else
		{
			$msg = JText::_('COM_IJOOMERADV_ERROR_SAVING_CONFIG');
		}

		$this->setRedirect('index.php?option=com_ijoomeradv&view=extensions', $msg);
	}

	function apply()
	{
		$app = JFactory::getApplication();
		$post = $app->input->get('post');
		$task = $app->input->get('task');
		$model = $this->getModel('extensions');

		if ($model->setExtConfig($post))
		{
			$msg = JText::_('COM_IJOOMERADV_CONFIG_SAVED');
		}
		else
		{
			$msg = JText::_('COM_IJOOMERADV_ERROR_SAVING_CONFIG');
		}

		$this->setRedirect('index.php?option=com_ijoomeradv&view=extensions&task=detail&cid[]=' . $post['extid'], $msg);
	}

	function install()
	{
		$app = JFactory::getApplication();
		$model = $this->getModel('extensions');
		$model->install();

		$app->input->set('view', 'extensions');
		$app->input->set('layout', 'default');
		$app->input->set('hidemainmenu', 0);

		parent::display();
	}

	function uninstall()
	{

	}

	function extensionmanage()
	{
		$app = JFactory::getApplication();
		$app->input->set('layout', 'manage');

		parent::display();
	}

	function publish()
	{
		$app = JFactory::getApplication();
		$post = $app->input->getArray('post', array());
		$cid = $post['cid'];

		if (!is_array($cid) || count($cid) < 1 || $cid[0] === 0)
		{
			throw new RuntimeException(JText::_('COM_IJOOMERADV_SELECT_EXTENSION_TO_PUBLISH'), 500);
		}

		$model = $this->getModel('extensions');

		if (!$model->publish($cid, 1))
		{
			echo "<script>alert('" . $model->getError(true) . "');</script>\n";
		}

		$app->input->set('layout', 'manage');

		parent::display();
	}

	function unpublish()
	{
		$app = JFactory::getApplication();
		$post = $app->input->getArray('post', array());
		$cid = $post['cid'];

		if (!is_array($cid) || count($cid) < 1 || $cid[0] === 0)
		{
			throw new RuntimeException(JText::_('COM_IJOOMERADV_SELECT_EXTENSION_TO_UNPUBLISH'), 500);
		}

		$model = $this->getModel('extensions');

		if (!$model->publish($cid, 0))
		{
			echo "<script>alert('" . $model->getError(true) . "');</script>\n";
		}

		$app->input->set('layout', 'manage');

		parent::display();
	}
}