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
 * The Class For IJoomer Controller Extension which will extends JControllerLegacy
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.controller
 * @since       1.0
 */
class IjoomeradvControllerExtensions extends JControllerLegacy
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
	 * Add Function
	 *
	 * @return  void
	 */
	public function add()
	{
		JFactory::getApplication()->input->set('layout', 'install');

		parent::display();
	}

	/**
	 * Detail Function
	 *
	 * @return  void
	 */
	public function detail()
	{
		$app = JFactory::getApplication();
		$app->input->set('layout', 'detail');
		$app->input->set('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * Save Function
	 *
	 * @return  void
	 */
	public function save()
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

	/**
	 * Apply Function For Applying The Latest Changes
	 *
	 * @return  void
	 */
	public function apply()
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

	/**
	 * Install Function For installing The Extension
	 *
	 * @return  void
	 */
	public function install()
	{
		$app = JFactory::getApplication();
		$model = $this->getModel('extensions');
		$model->install();

		$app->input->set('view', 'extensions');
		$app->input->set('layout', 'default');
		$app->input->set('hidemainmenu', 0);

		parent::display();
	}

	/**
	 * Uninstall Function For uninstalling The Extension
	 *
	 * @return  void
	 */

	public function uninstall()
	{
	}

	/**
	 * Extensionmanage Function For Managing The Extension
	 *
	 * @return  void
	 */
	public function extensionmanage()
	{
		$app = JFactory::getApplication();
		$app->input->set('layout', 'manage');

		parent::display();
	}

	/**
	 * Publish Function For Publishing The Extension
	 *
	 * @return  void
	 */
	public function publish()
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

	/**
	 * Unpublish Function For unpublishing The Extension
	 *
	 * @return  void
	 */
	public function unpublish()
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
