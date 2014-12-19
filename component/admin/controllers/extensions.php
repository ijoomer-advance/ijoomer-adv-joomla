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
		$jinput = JFactory::getApplication()->input;
		$cid    = $jinput->getArray(array('cid' => ''));

		// Initialiase variables.
		$db    = JFactory::getDbo();

		$query2 = $db->getQuery(true);

			// Create the base select statement.
			$query2->select('options')
				->from($db->qn('#__ijoomeradv_config'))
				->where($db->qn('name') . ' = ' . $db->q('IJOOMER_GC_REGISTRATION'));

			// Set the query and load the result.
			$db->setQuery($query2);

			$options = $db->loadResult();
			$cfgoptions = explode(';;', $options);


		$catid  = $cid['cid'];

		foreach ($catid as $key => $value)
		{
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('classname')
				->from($db->quoteName('#__ijoomeradv_extensions'))
				->where($db->quoteName('id') . ' = ' . $db->quote($value));

			// Set the query and load the result.
			$db->setQuery($query);

			$results = $db->loadObjectList();

			foreach ($results as $key1 => $value1)
			{
				$extname = $value1->classname;
				$matches = array_filter($cfgoptions, function($var) use ($extname) { return preg_match("/\b$extname\b/i", $var); });

				if($value1->classname != "icms")
				{
					$rmkey = key($matches);
					unset($cfgoptions[$rmkey]);
					$cfgvalue = implode(';;',$cfgoptions);

					$querycfg = $db->getQuery(true);

					// Create the base update statement.
					$querycfg->update($db->quoteName('#__ijoomeradv_config'))
						->set($db->quoteName('options') . ' = ' . $db->quote($cfgvalue))
						->where($db->quoteName('name') . ' = ' . $db->quote('IJOOMER_GC_REGISTRATION'));

					// Set the query and execute the update.
					$db->setQuery($querycfg);

					$db->execute();

					$query1 = $db->getQuery(true);

					// Create the base delete statement.
					$query1->delete()
						->from($db->quoteName('#__ijoomeradv_extensions'))
						->where($db->quoteName('id') . ' = ' . $db->quote($value));

					$db->setQuery($query1);

					$db->execute();

					$this->setMessage(JText::_('Extension Uninstalled SuccessFully'));
				}
				else
				{
					JError::raiseWarning('COM_IJOOMERADV_SOME_ERROR_CODE', JText::_('Default Extension Does Not Uninstall'));
				}
			}
		}

		$this->setRedirect('index.php?option=com_ijoomeradv&view=extensions&layout=manage');

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
		$app    = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;
		$cid    = $jinput->getArray(array('cid' => ''));

		$cid    = $cid['cid'];

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
		$app    = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;
		$cid    = $jinput->getArray(array('cid' => ''));

		$cid    = $cid['cid'];

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
