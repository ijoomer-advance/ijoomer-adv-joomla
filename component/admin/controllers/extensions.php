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

class ijoomeradvControllerExtensions extends JControllerLegacy
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

		parent::__construct ($default);
	}

	function display($cachable = false, $urlparams = false)
	{
		parent::display ();
	}

	public function home()
	{
		$this->setRedirect('index.php?option=com_ijoomeradv',null);
	}

	function add()
	{
		$this->app->input->set('layout', 'install');
		//JRequest::setVar('layout', 'install');

		parent::display();
	}

	function detail()
	{
		$this->app->input->set('layout', 'detail');
		$this->app->input->set('hidemainmenu', 1);

		/*JRequest::setVar('layout', 'detail');
		JRequest::setVar('hidemainmenu', 1);*/

		parent::display();
	}

	function save()
	{
		$post = $this->app->input->get('post');
		//$post = JRequest::get('post');

		$task =	$this->app->input->get('task');
		//$task = JRequest::getVar ('task');

		$model = $this->getModel('extensions');

		if ($model->setExtConfig($post))
		{
			$msg = JText::_('COM_IJOOMERADV_CONFIG_SAVED');
		}
		else
		{
			$msg = JText::_('COM_IJOOMERADV_ERROR_SAVING_CONFIG');
		}

		$this->setRedirect ('index.php?option=com_ijoomeradv&view=extensions', $msg);
	}

	function apply()
	{
		$post = $this->app->input->get('post');
		//$post = JRequest::get('post');

		$task =	$this->app->input->get('task');
		//$task = JRequest::getVar('task');

		$model = $this->getModel('extensions');

		if ($model->setExtConfig($post))
		{
			$msg = JText::_('COM_IJOOMERADV_CONFIG_SAVED');
		}
		else
		{
			$msg = JText::_('COM_IJOOMERADV_ERROR_SAVING_CONFIG');
		}

		$this->setRedirect ('index.php?option=com_ijoomeradv&view=extensions&task=detail&cid[]='.$post['extid'], $msg);
	}

	function install()
	{
		$model = $this->getModel('extensions');
		$model->install();

		$this->app->input->set('view', 'extensions');
		$this->app->input->set('layout', 'default');
		$this->app->input->set('hidemainmenu', 0 );

		/*JRequest::setVar( 'view', 'extensions' );
		JRequest::setVar( 'layout', 'default' );
		JRequest::setVar( 'hidemainmenu', 0 );*/

		parent::display();
	}

	function uninstall()
	{

	}

	function extensionmanage()
	{
		$this->app->input->set('layout', 'manage');
		//JRequest::setVar ( 'layout', 'manage' );
		parent::display ();
	}

	function publish()
	{
		$post = $this->app->input->getArray('post',array());
		$cid  = $post['cid']; //JRequest::getVar ( 'cid', array (0 ), 'post', 'array' );

		if (!is_array($cid) || count($cid) < 1 || $cid[0] === 0)
		{
			throw new RuntimeException(JText::_( 'COM_IJOOMERADV_SELECT_EXTENSION_TO_PUBLISH'), 500);
		}

		$model = $this->getModel( 'extensions' );

		if (!$model->publish ( $cid, 1 ))
		{
			echo "<script>alert('" . $model->getError ( true ) . "');</script>\n";
		}

		$this->app->input->set('layout', 'manage');
		//JRequest::setVar ( 'layout', 'manage' );

		parent::display ();
	}

	function unpublish()
	{
		$post = $this->app->input->getArray('post',array());
		$cid  = $post['cid']; //JRequest::getVar ( 'cid', array (0), 'post', 'array' );

		if (!is_array($cid) || count($cid) < 1 || $cid[0] === 0)
		{
			throw new RuntimeException(JText::_( 'COM_IJOOMERADV_SELECT_EXTENSION_TO_UNPUBLISH'), 500);
		}

		$model = $this->getModel ( 'extensions' );

		if (!$model->publish ($cid, 0))
		{
			echo "<script>alert('" . $model->getError ( true ) . "');</script>\n";
		}

		$this->app->input->set('layout', 'manage');
		//JRequest::setVar ( 'layout', 'manage' );

		parent::display ();
	}
}