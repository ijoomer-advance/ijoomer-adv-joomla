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

/**
 * The Menu Item Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_ijoomer
 * @since       1.6
 */
class IjoomeradvControllerItem extends JControllerForm
{

	public function display($cachable = false, $urlparams = false)
	{
		JControllerLegacy::display();
	}

	/**
	 * Method to add a new menu item.
	 *
	 * @return  mixed  True if the record can be added, a JError object if not.
	 *
	 * @since   1.6
	 */
	public function add()
	{
		// Initialise variables.
		$app     = JFactory::getApplication();
		$context = 'com_ijoomeradv.edit.item';
		$result  = parent::add();

		if ($result)
		{
			$app->setUserState($context . '.type', null);
			$app->setUserState($context . '.views', null);

			$menuType = $app->getUserStateFromRequest($this->context . '.filter.menutype', 'menutype', 'mainmenu', 'cmd');

			$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=item&menutype=' . $menuType . $this->getRedirectToItemAppend(), false));
		}

		return $result;
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean	 True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$model = $this->getModel('Item', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=items' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   1.6
	 */
	public function cancel($key = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app     = JFactory::getApplication();
		$context = 'com_ijoomeradv.edit.item';
		$app->setUserState('com_ijoomeradv.edit.item.data', null);
		$this->setRedirect(JRoute::_('index.php?option=com_ijoomeradv&view=items', false));
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 * (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   1.6
	 */
	public function edit($key = null, $urlVar = null)
	{
		// Initialise variables.
		$app    = JFactory::getApplication();
		$result = parent::edit();

		if ($result)
		{
			// Push the new ancillary data into the session.
			$app->setUserState('com_ijoomeradv.edit.item.type', null);
			$app->setUserState('com_ijoomeradv.edit.item.views', null);
		}

		return true;
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app           = JFactory::getApplication();
		$model         = $this->getModel('Item', '', array());
		$post          = $app->input->getArray('post', array());
		$data          = $post['jform'];
		$task          = $this->getTask();
		$context       = 'com_ijoomeradv.edit.item';
		$recordId      = $app->input->getInt('id', 0);
		$itemimagedata = $app->input->files->get('jform');
		$position      = $model->getMenuPostion($data['menutype']);

		if ($itemimagedata['name']['imageicon'] or $itemimagedata['name']['imagetab'] or $itemimagedata['name']['imagetabactive'])
		{
			$dirpath = JPATH_ADMINISTRATOR . '/components/com_ijoomeradv/theme/custom';
			shell_exec('chmod 777 '.$dirpath.' -R');

			$form = $model->getForm($data);

			if($form->getValue('itemimage'))
			{
				$imagename = $form->getValue('itemimage');
			}
			else
			{
				$imagename 	= explode('.',$data['views']);
				$postfix	= rand();
				$imagename	= $imagename[3].$postfix;
			}

			$imagename_home = $imagename.'_icon.png';
			$imagename_tab 	= $imagename.'_tab.png';
			$imagename_tab_active 	= $imagename.'_tab_active.png';
		}
		else
		{
			$imagename = null;
		}

		if ($position == 1 or $position == 2)
		{
			if ($itemimagedata['name']['imageicon'] && $itemimagedata['error']['imageicon']<=0 && $itemimagedata['size']['imageicon']>0)
			{

				$imagetype 	= $itemimagedata['type']['imageicon'];

				if($imagetype =='image/jpg' || $imagetype =='image/jpeg' )
				{
					$image = imagecreatefromjpeg($itemimagedata['tmp_name']['imageicon']);
				}
				else if($imagetype=='image/png')
				{
					$image = imagecreatefrompng($itemimagedata['tmp_name']['imageicon']);
				}
				else if($imagetype=='image/gif')
				{
					$image = imagecreatefromgif($itemimagedata['tmp_name']['imageicon']);
				}
				else
				{
					$image = imagecreatefromjpeg($itemimagedata['tmp_name']['imageicon']);
				}

				$devicetypearray = array('xhdpi'=>96,
										'hdpi'=>72,
										'mdpi'=>48,
										'ldpi'=>36,
										3=>57,
										4=>114,
										5=>114);

				foreach ($devicetypearray as $dkey=>$dvalue)
				{
					$imageResized = imagecreatetruecolor($dvalue, $dvalue);

					if (function_exists ("imageAntiAlias" ))
					{
						imageAntiAlias ( $imageResized, true );
					}
					imagealphablending ( $imageResized, false );

					if (function_exists ( "imagesavealpha" )){
						imagesavealpha ( $imageResized, true );
					}

					if (function_exists ( "imagecolorallocatealpha" ))
					{
						$transparent = imagecolorallocatealpha ( $imageResized, 255, 255, 255, 127 );
					}

					imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $dvalue, $dvalue, imagesx($image), imagesy($image));

					if($dkey == 'xhdpi' or $dkey == 'hdpi' or $dkey == 'mdpi' or $dkey == 'ldpi')
					{
						imagepng($imageResized, $dirpath.'/android/'.$dkey.'/'.$imagename_home);
					}
					else
					{
						imagepng($imageResized, $dirpath.'/iphone/'.$dkey.'/'.$imagename_home);
					}
				}

				imagedestroy($image);
			}
		}
		else
		{
			if ($itemimagedata['name']['imagetab'] && $itemimagedata['error']['imagetab']<=0 && $itemimagedata['size']['imagetab']>0){

				$imagetype 	= $itemimagedata['type']['imagetab'];
				if($imagetype=="image/jpg" || $imagetype=="image/jpeg" ){
					$image = imagecreatefromjpeg($itemimagedata['tmp_name']['imagetab']);
				}else if($imagetype=="image/png"){
					$image = imagecreatefrompng($itemimagedata['tmp_name']['imagetab']);
				}else if($imagetype=='image/gif'){
					$image = imagecreatefromgif($itemimagedata['tmp_name']['imagetab']);
				}else{
					$image = imagecreatefromjpeg($itemimagedata['tmp_name']['imagetab']);
				}

				$devicetypearray = array('xhdpi'=>array('height'=>64,'width'=>64),
										'hdpi'=>array('height'=>48,'width'=>48),
										'mdpi'=>array('height'=>32,'width'=>32),
										'ldpi'=>array('height'=>24,'width'=>24),
										3=>array('height'=>32,'width'=>32),
										4=>array('height'=>64,'width'=>64),
										5=>array('height'=>64,'width'=>64));

				foreach ($devicetypearray as $dkey=>$dvalue){
					$imageResized = imagecreatetruecolor($dvalue['width'], $dvalue['height']);

					if (function_exists ( "imageAntiAlias" )){
						imageAntiAlias ( $imageResized, true );
					}
					imagealphablending ( $imageResized, false );
					if (function_exists ( "imagesavealpha" )){
						imagesavealpha ( $imageResized, true );
					}
					if (function_exists ( "imagecolorallocatealpha" )){
						$transparent = imagecolorallocatealpha ( $imageResized, 255, 255, 255, 127 );
					}

					imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $dvalue['width'], $dvalue['height'], imagesx($image), imagesy($image));
					if($dkey == 'xhdpi' or $dkey == 'hdpi' or $dkey == 'mdpi' or $dkey == 'ldpi'){
						imagepng($imageResized, $dirpath.'/android/'.$dkey.'/'.$imagename_tab);
					}else{
						imagepng($imageResized, $dirpath.'/iphone/'.$dkey.'/'.$imagename_tab);
					}
				}

				imagedestroy($image);
			}

			if ($itemimagedata['name']['imagetabactive'] && $itemimagedata['error']['imagetabactive']<=0 && $itemimagedata['size']['imagetabactive']>0)
			{
				$imagetype 	= $itemimagedata['type']['imagetabactive'];

				if ($imagetype=="image/jpg" || $imagetype=="image/jpeg" )
				{
					$image1 = imagecreatefromjpeg($itemimagedata['tmp_name']['imagetabactive']);
				}
				else if($imagetype=="image/png")
				{
					$image1 = imagecreatefrompng($itemimagedata['tmp_name']['imagetabactive']);
				}
				else if($imagetype=='image/gif')
				{
					$image = imagecreatefromgif($itemimagedata['tmp_name']['imagetabactive']);
				}
				else
				{
					$image1 = imagecreatefromjpeg($itemimagedata['tmp_name']['imagetabactive']);
				}

				$devicetypearray = array('xhdpi'=>array('height'=>64,'width'=>64),
										'hdpi'=>array('height'=>48,'width'=>48),
										'mdpi'=>array('height'=>32,'width'=>32),
										'ldpi'=>array('height'=>24,'width'=>24),
										3=>array('height'=>32,'width'=>32),
										4=>array('height'=>64,'width'=>64),
										5=>array('height'=>64,'width'=>64));

				foreach ($devicetypearray as $dkey=>$dvalue)
				{
					$imageResized = imagecreatetruecolor($dvalue['width'], $dvalue['height']);

					if (function_exists ( "imageAntiAlias" ))
					{
						imageAntiAlias ( $imageResized, true );
					}

					imagealphablending ( $imageResized, false );

					if (function_exists ( "imagesavealpha" ))
					{
						imagesavealpha ( $imageResized, true );
					}

					if (function_exists ( "imagecolorallocatealpha" )){
						$transparent = imagecolorallocatealpha ( $imageResized, 255, 255, 255, 127 );
					}

					imagecopyresampled($imageResized, $image1, 0, 0, 0, 0, $dvalue['width'], $dvalue['height'], imagesx($image1), imagesy($image1));

					if ($dkey == 'xhdpi' or $dkey == 'hdpi' or $dkey == 'mdpi' or $dkey == 'ldpi'){
						imagepng($imageResized, $dirpath.'/android/'.$dkey.'/'.$imagename_tab_active);
					}
					else
					{
						imagepng($imageResized, $dirpath.'/iphone/'.$dkey.'/'.$imagename_tab_active);
					}
				}

				imagedestroy($image1);
			}
		}

		unset($data['itemimage']);
		$data['itemimage'] = $imagename;

		// Populate the row id from the session.
		$data['id'] = $recordId;

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy')
		{
			// Check-in the original row.
			if ($model->checkin($data['id']) === false)
			{
				// Check-in failed, go back to the item and display a notice.
				$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'warning');
				return false;
			}

			// Reset the ID and then treat the request as for Apply.
			$data['id'] = 0;
			$data['associations'] = array();
			$task = 'apply';
		}

		// Validate the posted data.
		// This post is made up of two forms, one for the item and one for params.

		$form = $model->getForm($data);

		if (!$form)
		{
			throw new RuntimeException($model->getError(), 500);

			return false;
		}

		$menuoptions = (isset($data['request'])) ? $data['request'] : Null;
		$data = $model->validate($form, $data);

		//changes for custom menu type
		$chcustom = explode('.',$data['views']);

		if ($chcustom[2] == 'custom')
		{
			$chcustom[3] = $menuoptions['actname'];
			$data['views'] = implode('.',$chcustom);
			unset($menuoptions['actname']);
		}

		// Check for the special 'request' entry.
		if ($data['type'] == 'component' && isset($data['request']) && is_array($data['request']) && !empty($data['request']))
		{
			// Parse the submitted link arguments.
			$args = array();
			parse_str(parse_url($data['link'], PHP_URL_QUERY), $args);

			// Merge in the user supplied request arguments.
			$args = array_merge($args, $data['request']);
			$data['link'] = 'index.php?' . urldecode(http_build_query($args, '', '&'));
			unset($data['request']);
		}
		//saurin
		//echo '<pre>';print_r($data);exit;
		// Check for validation errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception){
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_ijoomeradv.edit.item.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId), false));

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Save the data in the session.
			$app->setUserState('com_ijoomeradv.edit.item.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId), false));

			return false;
		}

		// Save succeeded, check-in the row.
		if ($model->checkin($data['id']) === false)
		{
			// Check-in failed, go back to the row and display a notice.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId), false));

			return false;
		}

		$this->setMessage(JText::_('COM_IJOOMERADV_SAVE_SUCCESS'));

		if($data['views'])
		{
			$view = explode('.',$data['views']);

			if($data['requiredField'])
			{
				$extension	 = $view[0];
				$extView	 = $view[1];
				$extTask	 = $view[2];
				$remoteTask	 = $view[3];

				if($extension != 'default')
				{
					require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/'.$extension.'/'.$extension.'.php';
				}
				else
				{
					require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/'.$extension.'.php';
				}

				$extClass	= $extension.'_menu';
				$extClass 	= new $extClass();

				if ($data['id'] <= 0)
				{
					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('MAX(id)')
						->from($db->qn('#__ijoomeradv_menu'));

					// Set the query and load the result.
					$db->setQuery($query);

					$data['id'] = $db->loadResult();
				}

				$extClass->setRequiredInput($extension,$extView,$extTask,$remoteTask,$menuoptions,$data);
			}
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the row data in the session.
				$recordId = $model->getState($this->context . '.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState('com_ijoomeradv.edit.item.data', null);
				$app->setUserState('com_ijoomeradv.edit.item.type', null);
				$app->setUserState('com_ijoomeradv.edit.item.views', null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId), false));
				break;

			case 'save2new':
				// Clear the row id and data in the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState('com_ijoomeradv.edit.item.data', null);
				$app->setUserState('com_ijoomeradv.edit.item.type', null);
				$app->setUserState('com_ijoomeradv.edit.item.views', null);
				$app->setUserState('com_ijoomeradv.edit.item.menutype', $model->getState('item.menutype'));

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend(), false));
				break;

			default:
				// Clear the row id and data in the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState('com_ijoomeradv.edit.item.data', null);
				$app->setUserState('com_ijoomeradv.edit.item.type', null);
				$app->setUserState('com_ijoomeradv.edit.item.views', null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));
				break;
		}
	}

	/**
	 * Sets the type of the menu item currently being edited.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	function setType()
	{
		// Initialise variables.
		$app  = JFactory::getApplication();
		$data = array();

		// Get the posted values from the request.
		$post     = $app->input->getArray('post', array());
		$data     = $post['jform'];
		$recordId = $app->input->getInt('id', 0);

		// Get the type.
		$type   = $data['type'];
		$type 	= json_decode(base64_decode($type));
		$title 	= isset($type->caption) ? $type->caption : null;

		if (isset($type->extension) && isset($type->view))
		{
			$views = strtolower($type->extension).'.'.$type->view.'.'.$type->task.'.'.$type->remoteTask;
		}
		else
		{
			return false;
		}

		if (isset($type->requiredField))
		{
			$requiredField = $type->requiredField;
		}
		else
		{
			$requiredField = 0;
		}

		$recordId = isset($type->id) ? $type->id : 0;

		$app->setUserState('com_ijoomeradv.edit.item.type', $title);
		$app->setUserState('com_ijoomeradv.edit.item.views', $views);
		$app->setUserState('com_ijoomeradv.edit.item.requiredField', $requiredField);

		unset($data['request']);
		$data['type']          = $title;
		$data['views']         = $views;
		$data['requiredField'] = $requiredField;

		//Save the data in the session.
		$app->setUserState('com_ijoomeradv.edit.item.data', $data);

		$this->type = $type;
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId), false));
	}
}
