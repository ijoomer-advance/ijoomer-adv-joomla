<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.controller
 *
 * @copyright   Copyright (C) 2010 - 2015 Tailored Solutions PVT. Ltd. All rights reserved.
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

		$sandext = JFile::getExt($sandFilenm);
		$liveext = JFile::getExt($liveFilenm);

		if(key($_FILES) == "SandBox" && !empty($sandFilenm) && $sandFiletype=="application/x-x509-ca-cert" && $sandext == "pem")
		{
			$sandFilenm = preg_replace("/^[^_]*_\s*/", "", $sandFilenm);

			$defName = 'certificates';

			// Split the file name by the dot
			$splitName = explode(".", $sandFilenm);

			// Get the file extension
			$fileExt = end($splitName);
			$newFileName  = strtolower($defName.'.'.$fileExt);

			$file1 = 'dev_'.$newFileName;

			$dest1 = JPATH_SITE ."/components/com_ijoomeradv/certificates/".$file1;

			JFile::upload($sandFiletmpnm, $dest1);
			chmod ($dest1, 0777);
		}
		elseif ($key == "live" && !empty($liveFilenm) && $liveFiletype=="application/x-x509-ca-cert" && $liveext == "pem")
		{
			$liveFilenm = preg_replace("/^[^_]*_\s*/", "", $liveFilenm);

			$defName = 'certificates';

			// Split the file name by the dot
			$splitName = explode(".", $liveFilenm);

			// Get the file extension
			$fileExt = end($splitName);
			$newFileName  = strtolower($defName.'.'.$fileExt);

			$file = 'pro_'.$newFileName;
			$dest = JPATH_SITE ."/components/com_ijoomeradv/certificates/$file";

			JFile::upload($liveFiletmpnm, $dest);
			chmod ($dest, 0777);
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
