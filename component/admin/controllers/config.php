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
		// Move uploaded file
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');
		jimport('joomla.filesystem.folder');

		foreach($_FILES as $key=>$value)
		{

		}

		$sandFilenm    = $_FILES['SandBox']['name'];
		$sandFiletype  = $_FILES['SandBox']['type'];
		$sandFiletmpnm = $_FILES['SandBox']['tmp_name'];

		$liveFilenm    = $_FILES['live']['name'];
		$liveFiletype  = $_FILES['live']['type'];
		$liveFiletmpnm = $_FILES['live']['tmp_name'];

		if(key($_FILES) == "SandBox" && !empty($sandFilenm) && $sandFiletype=="application/x-x509-ca-cert")
		{
			$file1 = 'dev_'.$sandFilenm;
			$dest1 = JPATH_SITE ."/components/com_ijoomeradv/certificates/".$file1;

			JFile::upload($sandFiletmpnm, $dest1);
			chmod ($dest1, 0777);
		}
		elseif ($key == "live" && !empty($liveFilenm) && $liveFiletype=="application/x-x509-ca-cert")
		{
			$file = 'pro_'.$liveFilenm;
			$dest = JPATH_SITE ."/components/com_ijoomeradv/certificates/$file";

			JFile::upload($liveFiletmpnm, $dest);
			chmod ($dest, 0777);
		}
		else
		{
			JLog::add(JText::_($this->text_prefix . 'Invalid File '), JLog::WARNING, 'jerror');
			JLog::add(JText::_($this->text_prefix . 'File Is Not Upload '), JLog::WARNING, 'jerror');

		}

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
