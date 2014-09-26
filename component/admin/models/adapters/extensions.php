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

defined( '_JEXEC' ) or die;

class JInstallerExtensions extends JObject {

	function __construct(&$parent) {
		$this->parent =& $parent;
		$this->tbl_prefix = '#__ijoomeradv_';
	}


	function install() {
		// Get a database connector object
		$db =& $this->parent->getDBO();

		// Get the extension manifest object
		$this->manifest =& $this->parent->getManifest();

		$query="SELECT `manifest_cache`
				FROM #__extensions
				WHERE `name`='ijoomeradv'
				AND `element`='com_ijoomeradv'";
		$db->setQuery($query);
		$extension=json_decode($db->loadResult($query));

		// check version
		if(floatval($this->manifest->version) != intval($extension->version)){
			$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSIONS').' '.JText::_('COM_IJOOMERADV_INSTALL').': '.JText::_('COM_IJOOMERADV_VERSION_NOT_SUPPORTED'));
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// Set the extensions name
		$name =$this->manifest->name;
		$filter =& JFilterInput::getInstance();
		$name = $filter->clean($name, 'string');
		$this->set('name', $name);

		// Get the component description
		$description =(string) $this->manifest->description;
		$this->parent->set('message', $description);

		/*
		 * Backward Compatability
		 * @todo Deprecate in future version
		 */
		$type = (string)$this->manifest->attributes()->type;

		// Set the installation path
		$element =& $this->manifest->files;
		$ename =(string) $element->children()->attributes()->extensions;

		//collect images to $images variable and remove the entry from the files element
		if (is_a($element, 'SimpleXMLElement') && count($element->children())) {
			$tm=0;
			foreach ($element->children()->image as $key=>$value){
				$images[$tm]=(string)$value;
				$tm++;
			}
		}

		//set extension name
		$extension_classname = (string)$this->manifest->extension_classname;
		$this->set('extension_classname', $extension_classname);

		// set extension option
		$extension_option = (string)$this->manifest->extension_option;
		$this->set('extension_option', $extension_option);

		if (!empty ($ename) && !empty($extension_classname)) {
			$this->parent->setPath('extension_root',IJ_SITE.DS.'extensions');
		} else {
			$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSION').' '.JText::_('COM_IJOOMERADV_INSTALL').': '.JText::_('COM_IJOOMERADV_NO_EXTENSION_FILE_OR_CLASS_NAME_SPECIFIED'));
			return false;
		}

		$registration = (boolean)$this->manifest->registration;
		$this->set('registration', $registration);

		$default_registration = (boolean)$this->manifest->default_registration;
		$this->set('default_registration', $default_registration);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// If the plugin directory does not exist, lets create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_root'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_root'))) {
				$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSION').' '.JText::_('COM_IJOOMERADV_INSTALL').': '.JText::_('COM_IJOOMERADV_FAILED_TO_CREATE_DIRECTORY').': "'.$this->parent->getPath('extension_root').'"');
				return false;
			}
		}

		/*
		 * If we created the plugin directory and will want to remove it if we
		 * have to roll back the installation, lets add it to the installation
		 * step stack
		 */
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}

		// Copy all necessary files
		if ($this->parent->parseFiles($element, -1,$ename) === false) {
			// Install failed, roll back changes
			$this->parent->abort();
			return false;
		}

		// copy images to images folder
		if(count($images)){
			foreach ($images as $image){
				$sorc=IJ_SITE.DS."extensions".DS.$ename.DS.$image;
				$dest=IJ_ASSET.DS."images".DS.$image;
				if(file_exists($sorc)){
					copy($sorc,$dest);
					rename($sorc,$dest);
				}
			}
		}

		// theme move to theme folder at admin side
		$folderTree=JFolder::listFolderTree($this->parent->getPath('extension_root').DS.$ename.DS.'theme'.DS,null);

		foreach($folderTree as $key=>$value){
			$dir=str_replace($this->parent->getPath('extension_root').DS.$ename.DS.'theme'.DS,'',$value['fullname']);
			$cdir=explode(DS,$dir);
			if(count($cdir)==1){
				// if theme folder is not there
				if(!is_dir(IJ_ADMIN.DS.'theme'.DS.$dir)){
					JFolder::create(IJ_ADMIN.DS.'theme'.DS.$dir);
				}

				// if extension theme already installed remove it
				if(is_dir(IJ_ADMIN.DS.'theme'.DS.$dir.DS.$ename)){
					JFolder::delete(IJ_ADMIN.DS.'theme'.DS.$dir.DS.$ename);
				}

				//move theme file
				JFolder::move($value['fullname'].DS.$ename,IJ_ADMIN.DS.'theme'.DS.$dir.DS.$ename);

				// update theme option
				$query="SELECT `options`
						FROM #__ijoomeradv_config
						WHERE `name`='IJOOMER_THM_SELECTED_THEME'";
				$db->setQuery($query);
				$themeoptions=$db->loadResult();
				$themeoptions=explode(';;',$themeoptions);

				$top=array();
				foreach ($themeoptions as $value){
					$tmp=explode('::',$value);
					$top[]=$tmp[0];
				}

				if(!in_array($dir,$top)){
					$themeoptions[]=strtolower($dir).'::'.ucfirst($dir);
				}
				$themeoptions=implode(';;',$themeoptions);
				$query="UPDATE #__ijoomeradv_config
						SET `options`='{$themeoptions}'
						WHERE `name`='IJOOMER_THM_SELECTED_THEME'";
				$db->setQuery($query);
				$db->Query();
			}
		}


	   /**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Check to see if a plugin by the same name is already installed
		$query="SELECT `id`
				FROM `{$this->tbl_prefix}extensions`
				WHERE `classname` = ".$db->Quote($extension_classname);
		$db->setQuery($query);
		if (!$db->Query()) { // Install failed, roll back changes
			$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSION').' '.JText::_('COM_IJOOMERADV_INSTALL').': '.$db->stderr(true));
			return false;
		}
		$extension_id = $db->loadResult();

		// Was there a module already installed with the same name?
		if ($extension_id) {
			if (!$this->parent->isOverwrite()){
				// Install failed, roll back changes
				$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSION').' '.JText::_('COM_IJOOMERADV_INSTALL').': '.JText::_('COM_IJOOMERADV_EXTENSION').' "'.$ename.'" '.JText::_('COM_IJOOMERADV_ALREADY_EXISTS'));
				return false;
			}

			//create config table
			$query="CREATE TABLE IF NOT EXISTS `#__ijoomeradv_{$this->get('extension_classname')}_config` (
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

			$pconfig =& $this->manifest->config;

			if (is_a($pconfig, 'SimpleXMLElement') && count($pconfig->children())) {
				$cfgs =& $pconfig->children();

				foreach ($cfgs as $cfg) {
					//if($cfg->_name=="cfg"){
						$cnfg=trim((string)$cfg);
						$query="SELECT count(*)
								FROM `#__ijoomeradv_{$this->get('extension_classname')}_config`
								WHERE `name`='{$cnfg}'";
						$db->setQuery($query);
						if(!$db->loadResult()){
							$query="INSERT INTO #__ijoomeradv_{$this->get('extension_classname')}_config (`id`, `caption`, `description`, `name`, `value`, `options`, `type`, `group`, `server`)
									VALUES (NULL,
									'".trim( (string)$cfg->attributes()->caption )."',
									'".trim( (string)$cfg->attributes()->description )."',
									'".trim( (string)$cfg)."',
									'".trim( (string)$cfg->attributes()->value )."',
									'".trim( (string)$cfg->attributes()->options )."',
									'".trim( (string)$cfg->attributes()->type )."',
									'".trim( (string)$cfg->attributes()->group )."',
									'".trim( (string)$cfg->attributes()->server )."')";
							$db->setQuery($query);
							$db->query();
						}
					//}
				}
			}

		} else {
			$row =& JTable::getInstance('extensions', 'Table');
	  		$row->name = $this->get('name');
			$row->classname = $this->get('extension_classname');
			$row->option = $this->get('extension_option');
			$row->published = 1;

		   	if (!$row->store()) {
				// Install failed, roll back changes
				$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSION').' '.JText::_('COM_IJOOMERADV_INSTALL').': '.$db->stderr(true));
			 	return false;
			}

			//create config table
			$query="CREATE TABLE IF NOT EXISTS `#__ijoomeradv_{$row->classname}_config` (
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
			if(!$db->query()) {
				$this->setError($db->getErrorMsg());
				return false;
			}else{
				$pconfig =& $this->manifest->config;

				if (is_a($pconfig, 'SimpleXMLElement') && count($pconfig->children())) {
					$cfgs =& $pconfig->children();
					foreach ($cfgs as $cfg) {
						//if($cfg->_name=="cfg"){
							$query="INSERT INTO #__ijoomeradv_{$this->get('extension_classname')}_config (`id`, `caption`, `description`, `name`, `value`, `options`, `type`, `group`, `server`)
									VALUES (NULL,
									'".trim( (string)$cfg->attributes()->caption )."',
									'".trim( (string)$cfg->attributes()->description )."',
									'".trim( (string)$cfg )."',
									'".trim( (string)$cfg->attributes()->value )."',
									'".trim( (string)$cfg->attributes()->options )."',
									'".trim( (string)$cfg->attributes()->type )."',
									'".trim( (string)$cfg->attributes()->group )."',
									'".trim( (string)$cfg->attributes()->server )."')";
							$db->setQuery($query);
							$db->query();
						//}
					}
				}
			}

			$this->parent->pushStep(array ('type' => 'extensions', 'id' => $row->id));
		}

		// check if extension needs to add into registration option
		if($registration==1){
			$query="SELECT `options`
					FROM `#__ijoomeradv_config`
					WHERE `name`='IJOOMER_GC_REGISTRATION'";
			$db->setQuery($query);
			$options=$db->loadResult();

			if(preg_match("|".$extension_classname."|",$options,$match)==0){
				$options.=';;'.$extension_classname.'::'.$name;
				// check if need to set to default registration option
				if($default_registration==1){
					$query="UPDATE #__ijoomeradv_config
							SET `options`='{$options}', `value`='{$extension_classname}'
							WHERE `name`='IJOOMER_GC_REGISTRATION'";
				}else{
					$query="UPDATE #__ijoomeradv_config
							SET `options`='{$options}'
							WHERE `name`='IJOOMER_GC_REGISTRATION'";
				}
			}else{
				// check if need to set to default registration option
				if($default_registration==1){
					$query="UPDATE #__ijoomeradv_config
							SET `value`='{$extension_classname}'
							WHERE `name`='IJOOMER_GC_REGISTRATION'";
				}
			}
			$db->setQuery($query);
			$db->Query();
		}


		if (!$this->parent->copyManifest(-1)) {
			// Install failed, rollback changes
			$this->parent->abort(JText::_('COM_IJOOMERADV_EXTENSIONS').' '.JText::_('COM_IJOOMERADV_INSTALL').': '.JText::_('COM_IJOOMERADV_COULD_NOT_COPY_SETUP_FILE'));
			return false;
		}
		return true;
	}

	private function recurseDir($dir){
		$dirHandle = opendir($dir);
	    while($file = readdir($dirHandle)){
	        if(is_dir($dir.$file) && $file != '.' && $file != '..'){
				//echo '<pre>';print_r($file);

	        	$count = recurseDirs($main.$file."/",$count); // Correct call and fixed counting
	        }/*else{
	            $count++;
	            echo "$count: filename: $file in $main \n<br />";
	        }*/
	    }
	}

	function uninstall($id, $clientId ){
		// Initialize variables
		$row	= null;
		$retval = true;
		$db		=& $this->parent->getDBO();

		$row =& JTable::getInstance('extensions', 'Table');
		if ( !$row->load((int) $clientId) ) {
			JError::raiseWarning(100, JText::_('COM_IJOOMERADV_ERROR_UNKOWN_EXTENSION'));
			return false;
		}

		// Set the plugin root path
		$this->parent->setPath('extension_root',JPATH_COMPONENT_SITE.DS.'extensions');
		$manifestFile = JPATH_COMPONENT_SITE.DS.'extensions'.DS.$row->classname.'.xml';

		if (file_exists($manifestFile)){
			$xml =& JFactory::getXMLParser('Simple');

			// If we cannot load the xml file return null
			if (!$xml->loadFile($manifestFile)) {
				JError::raiseWarning(100, JText::_('COM_IJOOMERADV_EXTENSIONS').' '.JText::_('COM_IJOOMERADV_UNINSTALL').': '.JText::_('COM_IJOOMERADV_COULD_NOT_LOAD_MANIFEST_FILE'));
				return false;
			}

			$root =& $xml->document;
			if ($root->name() != 'install' && $root->name() != 'mosinstall') {
				JError::raiseWarning(100, JText::_('COM_IJOOMERADV_EXTENSIONS').' '.JText::_('COM_IJOOMERADV_UNINSTALL').': '.JText::_('COM_IJOOMERADV_INVALID_MANIFIEST_FILE'));
				return false;
			}

			JFile::delete($manifestFile);
		} else {
			JError::raiseWarning(100, JText::_('COM_IJOOMERADV_EXTENSIONS').' '.JText::_('COM_IJOOMERADV_UNINSTALL').': '.JText::_('COM_IJOOMERADV_MANIFEST_FILE_INVALID_OR_FILE_NOT_FOUND'));
		}

		// Now we will no longer need the plugin object, so lets delete it
		$row->delete($row->id);

		// If the folder is empty, let's delete it
		$files = JFolder::files($this->parent->getPath('extension_root').DS.$row->classname);

		JFolder::delete($this->parent->getPath('extension_root').DS.$row->classname);
		unset ($row);
		return $retval;
	}

	function _rollback_plugin($arg){
		// Get database connector object
		$db =& $this->parent->getDBO();

		// Remove the entry from the #__plugins table
		$query = 'DELETE' .
				' FROM `#__'.TABLE_PREFIX.'_extensions`' .
				' WHERE `id` ='.(int)$arg['id '];
		$db->setQuery($query);
		return ($db->query() !== false);
	}
}