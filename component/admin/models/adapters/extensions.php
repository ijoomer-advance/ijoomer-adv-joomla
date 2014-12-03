<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.models
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.models
 * @since       1.0
 */
class JInstallerExtensions extends JObject
{
	/**
	 * The Construct Function
	 *
	 * @param   [type]  &$parent  contains the value of $parent
	 */
	public function __construct(&$parent)
	{
		$this->parent = $parent;
		$this->tbl_prefix = '#__ijoomeradv_';
	}

/**
 * The Function For Installation
 *
 * @return  boolean returns the value in true or false
 */
	public function install()
	{
		// Get a database connector object
		$db = $this->parent->getDBO();

		// Get the extension manifest object
		$this->manifest = $this->parent->getManifest();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('manifest_cache')
			->from($db->qn('#__extensions'))
			->where($db->qn('name') . ' = ' . $db->q('ijoomeradv'))
			->where($db->qn('element') . ' = ' . $db->q('com_ijoomeradv'));

		// Set the query and load the result.
		$db->setQuery($query);

		$extension = json_decode($db->loadResult($query));

		// Check version
		if (floatval($this->manifest->version) != intval($extension->version))
		{
			$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSIONS') . ' ' . JText::_('COM_IJOOMERADV_INSTALL') . ': ' . JText::_('COM_IJOOMERADV_VERSION_NOT_SUPPORTED'));
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// Set the extensions name
		$name = $this->manifest->name;
		$filter = JFilterInput::getInstance();
		$name = $filter->clean($name, 'string');
		$this->set('name', $name);

		// Get the component description
		$description = (string) $this->manifest->description;
		$this->parent->set('message', $description);

		/*
		 * Backward Compatability
		 * @todo Deprecate in future version
		 */
		$type = (string) $this->manifest->attributes()->type;

		// Set the installation path
		$element = $this->manifest->files;
		$ename = (string) $element->children()->attributes()->extensions;

		// Collect images to $images variable and remove the entry from the files element
		if (is_a($element, 'SimpleXMLElement') && count($element->children()))
		{
			$tm = 0;

			foreach ($element->children()->image as $key => $value)
			{
				$images[$tm] = (string) $value;
				$tm++;
			}
		}

		// Set extension name
		$extension_classname = (string) $this->manifest->extension_classname;
		$this->set('extension_classname', $extension_classname);

		// Set extension option
		$extension_option = (string) $this->manifest->extension_option;
		$this->set('extension_option', $extension_option);

		if (!empty ($ename) && !empty($extension_classname))
		{
			$this->parent->setPath('extension_root', IJ_SITE . '/extensions');
		}
		else
		{
			$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSION') . ' ' . JText::_('COM_IJOOMERADV_INSTALL') . ': ' . JText::_('COM_IJOOMERADV_NO_EXTENSION_FILE_OR_CLASS_NAME_SPECIFIED'));

			return false;
		}

		$registration = (boolean) $this->manifest->registration;
		$this->set('registration', $registration);

		$default_registration = (boolean) $this->manifest->default_registration;
		$this->set('default_registration', $default_registration);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// If the plugin directory does not exist, lets create it
		$created = false;

		if (!file_exists($this->parent->getPath('extension_root')))
		{
			if (!$created = JFolder::create($this->parent->getPath('extension_root')))
			{
				$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSION') . ' ' . JText::_('COM_IJOOMERADV_INSTALL') . ': ' . JText::_('COM_IJOOMERADV_FAILED_TO_CREATE_DIRECTORY') . ': "' . $this->parent->getPath('extension_root') . '"');

				return false;
			}
		}

		/*
		 * If we created the plugin directory and will want to remove it if we
		 * have to roll back the installation, lets add it to the installation
		 * step stack
		 */
		if ($created)
		{
			$this->parent->pushStep(array('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}

		// Copy all necessary files
		if ($this->parent->parseFiles($element, -1, $ename) === false)
		{
			// Install failed, roll back changes
			$this->parent->abort();

			return false;
		}

		// Copy images to images folder
		if (count($images))
		{
			foreach ($images as $image)
			{
				$sorc = IJ_SITE . '/' . "extensions" . '/' . $ename . '/' . $image;
				$dest = IJ_ASSET . '/' . "images" . '/' . $image;

				if (file_exists($sorc))
				{
					copy($sorc, $dest);
					rename($sorc, $dest);
				}
			}
		}

		// Theme move to theme folder at admin side
		$folderTree = JFolder::listFolderTree($this->parent->getPath('extension_root') . '/' . $ename . '/theme' . DS, null);

		foreach ($folderTree as $key => $value)
		{
			$dir = str_replace($this->parent->getPath('extension_root') . '/' . $ename . '/theme' . DS, '', $value['fullname']);
			$cdir = explode(DS, $dir);

			if (count($cdir) == 1)
			{
				// If theme folder is not there
				if (!is_dir(IJ_ADMIN . '/theme/' . $dir))
				{
					JFolder::create(IJ_ADMIN . '/theme/' . $dir);
				}

				// If extension theme already installed remove it
				if (is_dir(IJ_ADMIN . '/theme/' . $dir . '/' . $ename))
				{
					JFolder::delete(IJ_ADMIN . '/theme/' . $dir . '/' . $ename);
				}

				// Move theme file
				JFolder::move($value['fullname'] . '/' . $ename, IJ_ADMIN . '/theme/' . $dir . '/' . $ename);

				$query = $db->getQuery(true);

				$query->select('options')
					->from($db->qn('#__ijoomeradv_config'))
					->where($db->qn('name') . ' = ' . $db->q('IJOOMER_THM_SELECTED_THEME'));

				// Set the query and load the result.
				$db->setQuery($query);

				$themeoptions = $db->loadResult();
				$themeoptions = explode(';;', $themeoptions);

				$top = array();

				foreach ($themeoptions as $value)
				{
					$tmp = explode('::', $value);
					$top[] = $tmp[0];
				}

				if (!in_array($dir, $top))
				{
					$themeoptions[] = strtolower($dir) . '::' . ucfirst($dir);
				}

				$themeoptions = implode(';;', $themeoptions);

				$query = $db->getQuery(true);

				// Create the base update statement.
				$query->update($db->qn('#__ijoomeradv_config'))
					->set($db->qn('options') . ' = ' . $db->q($themeoptions))
					->where($db->qn('name') . ' = ' . $db->q('IJOOMER_THM_SELECTED_THEME'));

				// Set the query and execute the update.
				$db->setQuery($query);

				$db->execute();
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Check to see if a plugin by the same name is already installed
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('id')
			->from($db->qn($this->tbl_prefix . 'extensions'))
			->where($db->qn('classname') . ' = ' . $db->q($extension_classname));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$extension_id = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			// Install failed, roll back changes
			$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSION') . ' ' . JText::_('COM_IJOOMERADV_INSTALL') . ': ' . $db->stderr(true));
			return false;
		}

		// Was there a module already installed with the same name?
		if ($extension_id)
		{
			if (!$this->parent->isOverwrite())
			{
				// Install failed, roll back changes
				$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSION') . ' ' . JText::_('COM_IJOOMERADV_INSTALL') . ': ' . JText::_('COM_IJOOMERADV_EXTENSION') . ' "' . $ename . '" ' . JText::_('COM_IJOOMERADV_ALREADY_EXISTS'));

				return false;
			}

			// Create config table
			$query = "CREATE TABLE IF NOT EXISTS `#__ijoomeradv_{$this->get('extension_classname')}_config` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `caption` varchar(255) NOT NULL,
					  `description` varchar(255) NOT NULL,
					  `name` varchar(255) NOT NULL,
					  `value` varchar(255) NOT NULL,
					  `options` text,
					  `type` varchar(255) NOT NULL,
					  `group` varchar(255) NOT NULL,
					  `server` int(1) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`id`)
					)";
			$db->setQuery($query);
			$db->Query();

			$pconfig = $this->manifest->config;

			if (is_a($pconfig, 'SimpleXMLElement') && count($pconfig->children()))
			{
				$cfgs = $pconfig->children();

				foreach ($cfgs as $cfg)
				{
					$cnfg = trim((string) $cfg);
					$query = "SELECT count(*)
								FROM `#__ijoomeradv_{$this->get('extension_classname')}_config`
								WHERE `name`='{$cnfg}'";
					$db->setQuery($query);

					if (!$db->loadResult())
					{
						$query = "INSERT INTO #__ijoomeradv_{$this->get('extension_classname')}_config (`id`, `caption`, `description`, `name`, `value`, `options`, `type`, `group`, `server`)
									VALUES (NULL,
									'" . trim((string) $cfg->attributes()->caption) . "',
									'" . trim((string) $cfg->attributes()->description) . "',
									'" . trim((string) $cfg) . "',
									'" . trim((string) $cfg->attributes()->value) . "',
									'" . trim((string) $cfg->attributes()->options) . "',
									'" . trim((string) $cfg->attributes()->type) . "',
									'" . trim((string) $cfg->attributes()->group) . "',
									'" . trim((string) $cfg->attributes()->server) . "')";
						$db->setQuery($query);
						$db->query();
					}
				}
			}
		}
		else
		{
			$row = JTable::getInstance('extensions', 'Table');
			$row->name = $this->get('name');
			$row->classname = $this->get('extension_classname');
			$row->option = $this->get('extension_option');
			$row->published = 1;

			if (!$row->store())
			{
				// Install failed, roll back changes
				$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSION') . ' ' . JText::_('COM_IJOOMERADV_INSTALL') . ': ' . $db->stderr(true));

				return false;
			}

			// Create config table
			$query = "CREATE TABLE IF NOT EXISTS `#__ijoomeradv_{$row->classname}_config` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `caption` varchar(255) NOT NULL,
					  `description` varchar(255) NOT NULL,
					  `name` varchar(255) NOT NULL,
					  `value` varchar(255) NOT NULL,
					  `options` text,
					  `type` varchar(255) NOT NULL,
					  `group` varchar(255) NOT NULL,
					  `server` int(1) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`id`)
					)";

			$db->setQuery($query);

			if (!$db->query())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}
			else
			{
				$pconfig = $this->manifest->config;

				if (is_a($pconfig, 'SimpleXMLElement') && count($pconfig->children()))
				{
					$cfgs = $pconfig->children();

					foreach ($cfgs as $cfg)
					{
						$query = "INSERT INTO #__ijoomeradv_{$this->get('extension_classname')}_config (`id`, `caption`, `description`, `name`, `value`, `options`, `type`, `group`, `server`)
									VALUES (NULL,
									'" . trim((string) $cfg->attributes()->caption) . "',
									'" . trim((string) $cfg->attributes()->description) . "',
									'" . trim((string) $cfg) . "',
									'" . trim((string) $cfg->attributes()->value) . "',
									'" . trim((string) $cfg->attributes()->options) . "',
									'" . trim((string) $cfg->attributes()->type) . "',
									'" . trim((string) $cfg->attributes()->group) . "',
									'" . trim((string) $cfg->attributes()->server) . "')";
						$db->setQuery($query);
						$db->query();
					}
				}
			}

			$this->parent->pushStep(array('type' => 'extensions', 'id' => $row->id));
		}

		// Check if extension needs to add into registration option
		if ($registration == 1)
		{
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('options')
				->from($db->qn('#__ijoomeradv_config'))
				->where($db->qn('name') . ' = ' . $db->q('IJOOMER_GC_REGISTRATION'));

			// Set the query and load the result.
			$db->setQuery($query);

			$options = $db->loadResult();

			$query = $db->getQuery(true);

			if (preg_match("|" . $extension_classname . "|", $options, $match) == 0)
			{
				$options .= ';;' . $extension_classname . '::' . $name;

				// Check if need to set to default registration option
				if ($default_registration == 1)
				{
					// Create the base update statement.
					$query->update($db->qn('#__ijoomeradv_config'))
						->set($db->qn('options') . ' = ' . $db->q($options))
						->set($db->qn('value') . ' = ' . $db->q($extension_classname))
						->where($db->qn('name') . ' = ' . $db->q('IJOOMER_GC_REGISTRATION'));
				}
				else
				{
					// Create the base update statement.
					$query->update($db->qn('#__ijoomeradv_config'))
						->set($db->qn('value') . ' = ' . $db->q($extension_classname))
						->where($db->qn('name') . ' = ' . $db->q('IJOOMER_GC_REGISTRATION'));
				}
			}
			else
			{
				// Check if need to set to default registration option
				if ($default_registration == 1)
				{
					// Create the base update statement.
					$query->update($db->qn('#__ijoomeradv_config'))
						->set($db->qn('value') . ' = ' . $db->q($extension_classname))
						->where($db->qn('name') . ' = ' . $db->q('IJOOMER_GC_REGISTRATION'));
				}
			}

			$db->setQuery($query);
			$db->execute();
		}

		if (!$this->parent->copyManifest(-1))
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSIONS') . ' ' . JText::_('COM_IJOOMERADV_INSTALL') . ': ' . JText::_('COM_IJOOMERADV_COULD_NOT_COPY_SETUP_FILE'));

			return false;
		}

		return true;
	}

	/**
	 * The function RecurseDir
	 *
	 * @param   [type]  $dir  contains the value of dir
	 *
	 * @return  void
	 */
	private function recurseDir($dir)
	{
		$dirHandle = opendir($dir);

		while ($file = readdir($dirHandle))
		{
			if ( is_dir($dir . $file) && $file != '.' && $file != '..')
			{
				// Correct call and fixed counting
				$count = recurseDirs($main . $file . "/", $count);
			}
		}
	}

	/**
	 * The Uninstall Function
	 *
	 * @param   [type]  $id        contains the value of Id
	 * @param   [type]  $clientId  contains the value of Client_Id
	 *
	 * @return  it will returns the value in false or $retval
	 */
	public function uninstall($id, $clientId)
	{
		// Initialize variables
		$row = null;
		$retval = true;
		$db = $this->parent->getDBO();

		$row = JTable::getInstance('extensions', 'Table');

		if (!$row->load((int) $clientId))
		{
			JError::raiseWarning(100, JText::_('COM_IJOOMERADV_ERROR_UNKOWN_EXTENSION'));

			return false;
		}

		// Set the plugin root path
		$this->parent->setPath('extension_root', JPATH_COMPONENT_SITE . '/extensions');
		$manifestFile = JPATH_COMPONENT_SITE . '/extensions/' . $row->classname . '.xml';

		if (file_exists($manifestFile))
		{
			$xml = JFactory::getXMLParser('Simple');

			// If we cannot load the xml file return null
			if (!$xml->loadFile($manifestFile))
			{
				JError::raiseWarning(100, JText::_('COM_IJOOMERADV_EXTENSIONS') . ' ' . JText::_('COM_IJOOMERADV_UNINSTALL') . ': ' . JText::_('COM_IJOOMERADV_COULD_NOT_LOAD_MANIFEST_FILE'));

				return false;
			}

			$root = $xml->document;

			if ($root->name() != 'install' && $root->name() != 'mosinstall')
			{
				JError::raiseWarning(100, JText::_('COM_IJOOMERADV_EXTENSIONS') . ' ' . JText::_('COM_IJOOMERADV_UNINSTALL') . ': ' . JText::_('COM_IJOOMERADV_INVALID_MANIFIEST_FILE'));

				return false;
			}

			JFile::delete($manifestFile);
		}
		else
		{
			JError::raiseWarning(100, JText::_('COM_IJOOMERADV_EXTENSIONS') . ' ' . JText::_('COM_IJOOMERADV_UNINSTALL') . ': ' . JText::_('COM_IJOOMERADV_MANIFEST_FILE_INVALID_OR_FILE_NOT_FOUND'));
		}

		// Now we will no longer need the plugin object, so lets delete it
		$row->delete($row->id);

		// If the folder is empty, let's delete it
		$files = JFolder::files($this->parent->getPath('extension_root') . '/' . $row->classname);

		JFolder::delete($this->parent->getPath('extension_root') . '/' . $row->classname);
		unset ($row);

		return $retval;
	}

	/**
	 * The Function For Rollbackplugin
	 *
	 * @param   [type]  $arg  contains the value of $arg
	 *
	 * @return  boolean returns the values in true or false
	 */
	public function _rollback_plugin($arg)
	{
		// Get database connector object
		$db = $this->parent->getDBO();

		// Remove the entry from the #__plugins table
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->qn('#__' . TABLE_PREFIX . '_extensions'))
			->where($db->qn('id') . ' = ' . $db->q((int) $arg['id']));

		// Set the query and execute the delete.
		$db->setQuery($query);

		try
		{
			return ($db->execute() !== false);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}
}
