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
 * Menu Item Model for Menus.
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.models
 * @since       1.6
 */
class IjoomeradvModelMenutypes extends JModelLegacy
{
	/**
	 * A reverse lookup of the base link URL to Title
	 *
	 * @var    array
	 */
	protected $rlu = array();

	/**
	 * Method to get the reverse lookup of the base link URL to Title
	 *
	 * @return    array    Array of reverse lookup of the base link URL to Title
	 * @since    1.6
	 */
	public function getReverseLookup()
	{
		if (empty($this->rlu))
		{
			$this->getTypeOptions();
		}
		return $this->rlu;
	}

	/**
	 * Method to get the available menu item type options.
	 *
	 * @return    array    Array of groups with menu item types.
	 * @since    1.6
	 */
	public function getTypeOptions()
	{
		jimport('joomla.filesystem.file');

		// Initialise variables.
		$id = JRequest::getInt('recordId');
		$layout = JRequest::getVar('layout');
		$lang = JFactory::getLanguage();
		$list = array();
		$defaults = array();

		// Get the list of components.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query = 'SELECT *
				  FROM #__ijoomeradv_extensions
				  WHERE published=1';

		$db->setQuery($query);
		$components = $db->loadObjectList();

		if ($layout == 'select')
		{
			$query = 'SELECT screen
				  FROM #__ijoomeradv_menu_types
				  WHERE id=' . $id;

			$db->setQuery($query);
			$default = json_decode($db->loadResult());

			if ($default)
			{
				foreach ($default as $key => $value)
				{
					$keys = $key;
					foreach ($value as $screens)
					{
						$defaults[] = $keys . '.' . $screens;
					}
				}
			}
		}

		if ($options = $this->getTypeOptionsByComponent('default', $defaults))
		{
			$list['default'] = $options;

			foreach ($options as $option)
			{
				if (isset($option->request))
				{
					$this->rlu[IjoomerHelper::getLinkKey($option->request)] = $option->get('caption');
				}
			}
		}

		foreach ($components as $component)
		{
			if ($options = $this->getTypeOptionsByComponent($component->classname, $defaults))
			{
				$list[$component->classname] = $options;

				// Create the reverse lookup for link-to-name.
				foreach ($options as $option)
				{
					if (isset($option->request))
					{
						$this->rlu[IjoomerHelper::getLinkKey($option->request)] = $option->get('caption');
					}
				}
			}
		}

		return $list;
	}

	public function getMenuitems()
	{
		$id = JRequest::getInt('recordId');
		$db = JFactory::getDbo();

		$query = 'SELECT m.id as itemid,m.title as itemtitle,m.type as itemtype,m.published
				  FROM #__ijoomeradv_menu as m
				  WHERE m.published=1';

		$db->setQuery($query);
		$result = $db->loadObjectList();

		$query = 'SELECT menuitem
				  FROM #__ijoomeradv_menu_types
				  WHERE id=' . $id;

		$db->setQuery($query);
		$default = explode(',', $db->loadResult());
		$result1 = array();

		foreach ($result as $key => $value)
		{
			if (in_array($value->itemid, $default))
			{
				$checked = true;
			}
			else
			{
				$checked = false;
			}

			$o = new stdClass;
			$o->itemid = $value->itemid;
			$o->itemtitle = $value->itemtitle;
			$o->itemtype = $value->itemtype;
			$o->checked = $checked;

			$result1[] = $o;
		}
		return $result1;
	}

	protected function getTypeOptionsByComponent($component, $defaults)
	{
		// Initialise variables.
		$options = array();

		$mainXML = JPATH_SITE . '/components/com_ijoomeradv/extensions/' . $component . '.xml';

		if (is_file($mainXML))
		{
			$options = $this->getTypeOptionsFromXML($mainXML, $component, $defaults);
		}

		if (empty($options))
		{
			$options = $this->getTypeOptionsFromMVC($component);
		}

		return $options;
	}

	protected function getTypeOptionsFromXML($file, $component, $defaults)
	{
		$options = array();

		if ($xml = simplexml_load_file($file))
		{
			$views = $menu = $xml->xpath('views');

			if ($views)
			{
				foreach ($views[0]->view as $key => $value)
				{

					$o = new JObject;
					$o->caption = (string) $value->caption;
					$o->view = (string) $value->extView;
					$o->task = (string) $value->extTask;
					$o->remoteTask = (string) $value->remoteTask;
					$o->requiredField = (int) $value->requiredField;

					if ($defaults)
					{
						if (in_array($component . '.' . $value->extView . '.' . $value->extTask . '.' . $value->remoteTask, $defaults))
						{
							$checked = true;
						}
						else
						{
							$checked = false;
						}
					}
					else
					{
						$checked = false;
					}
					$o->checked = $checked;
					$options[] = $o;
				}
			}
		}

		return $options;
	}


	protected function getTypeOptionsFromMVC($component)
	{
		// Initialise variables.
		$options = array();

		// Get the views for this component.
		$path = JPATH_SITE . '/components/' . $component . '/views';

		if (JFolder::exists($path))
		{
			$views = JFolder::folders($path);
		}
		else
		{
			return false;
		}

		foreach ($views as $view)
		{
			// Ignore private views.
			if (strpos($view, '_') !== 0)
			{
				// Determine if a metadata file exists for the view.
				$file = $path . '/' . $view . '/metadata.xml';

				if (is_file($file))
				{
					// Attempt to load the xml file.
					if ($xml = simplexml_load_file($file))
					{
						// Look for the first view node off of the root node.
						if ($menu = $xml->xpath('view[1]'))
						{
							$menu = $menu[0];

							// If the view is hidden from the menu, discard it and move on to the next view.
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true')
							{
								unset($xml);
								continue;
							}

							// Do we have an options node or should we process layouts?
							// Look for the first options node off of the menu node.
							if ($optionsNode = $menu->xpath('options[1]'))
							{
								$optionsNode = $optionsNode[0];

								// Make sure the options node has children.
								if ($children = $optionsNode->children())
								{
									// Process each child as an option.
									foreach ($children as $child)
									{
										if ($child->getName() == 'option')
										{
											// Create the menu option for the component.
											$o = new JObject;
											$o->title = (string) $child['name'];
											$o->description = (string) $child['msg'];
											$o->request = array('option' => $component, 'view' => $view, (string) $optionsNode['var'] => (string) $child['value']);

											$options[] = $o;
										}
										elseif ($child->getName() == 'default')
										{
											// Create the menu option for the component.
											$o = new JObject;
											$o->title = (string) $child['name'];
											$o->description = (string) $child['msg'];
											$o->request = array('option' => $component, 'view' => $view);

											$options[] = $o;
										}
									}
								}
							}
							else
							{
								$options = array_merge($options, (array) $this->getTypeOptionsFromLayouts($component, $view));
							}
						}
						unset($xml);
					}

				}
				else
				{
					$options = array_merge($options, (array) $this->getTypeOptionsFromLayouts($component, $view));
				}
			}
		}

		return $options;
	}

	protected function getTypeOptionsFromLayouts($component, $view)
	{
		// Initialise variables.
		$options = array();
		$layouts = array();
		$layoutNames = array();
		$templateLayouts = array();
		$lang = JFactory::getLanguage();

		// Get the layouts from the view folder.
		$path = JPATH_SITE . '/components/' . $component . '/views/' . $view . '/tmpl';
		if (JFolder::exists($path))
		{
			$layouts = array_merge($layouts, JFolder::files($path, '.xml$', false, true));
		}
		else
		{
			return $options;
		}

		// build list of standard layout names
		foreach ($layouts as $layout)
		{
			// Ignore private layouts.
			if (strpos(JFile::getName($layout), '_') === false)
			{
				$file = $layout;
				// Get the layout name.
				$layoutNames[] = JFile::stripext(JFile::getName($layout));
			}
		}

		// get the template layouts
		// @TODO: This should only search one template -- the current template for this item (default of specified)
		$folders = JFolder::folders(JPATH_SITE . '/templates', '', false, true);
		// Array to hold association between template file names and templates
		$templateName = array();
		foreach ($folders as $folder)
		{
			if (JFolder::exists($folder . '/html/' . $component . '/' . $view))
			{
				$template = JFile::getName($folder);
				$lang->load('tpl_' . $template . '.sys', JPATH_SITE, null, false, false)
				|| $lang->load('tpl_' . $template . '.sys', JPATH_SITE . '/templates/' . $template, null, false, false)
				|| $lang->load('tpl_' . $template . '.sys', JPATH_SITE, $lang->getDefault(), false, false)
				|| $lang->load('tpl_' . $template . '.sys', JPATH_SITE . '/templates/' . $template, $lang->getDefault(), false, false);

				$templateLayouts = JFolder::files($folder . '/html/' . $component . '/' . $view, '.xml$', false, true);

				foreach ($templateLayouts as $layout)
				{
					$file = $layout;
					// Get the layout name.
					$templateLayoutName = JFile::stripext(JFile::getName($layout));

					// add to the list only if it is not a standard layout
					if (array_search($templateLayoutName, $layoutNames) === false)
					{
						$layouts[] = $layout;
						// Set template name array so we can get the right template for the layout
						$templateName[$layout] = JFile::getName($folder);
					}
				}
			}
		}

		// Process the found layouts.
		foreach ($layouts as $layout)
		{
			// Ignore private layouts.
			if (strpos(JFile::getName($layout), '_') === false)
			{
				$file = $layout;
				// Get the layout name.
				$layout = JFile::stripext(JFile::getName($layout));

				// Create the menu option for the layout.
				$o = new JObject;
				$o->title = ucfirst($layout);
				$o->description = '';
				$o->request = array('option' => $component, 'view' => $view);

				// Only add the layout request argument if not the default layout.
				if ($layout != 'default')
				{
					// If the template is set, add in format template:layout so we save the template name
					$o->request['layout'] = (isset($templateName[$file])) ? $templateName[$file] . ':' . $layout : $layout;
				}

				// Load layout metadata if it exists.
				if (is_file($file))
				{
					// Attempt to load the xml file.
					if ($xml = simplexml_load_file($file))
					{
						// Look for the first view node off of the root node.
						if ($menu = $xml->xpath('layout[1]'))
						{
							$menu = $menu[0];

							// If the view is hidden from the menu, discard it and move on to the next view.
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true')
							{
								unset($xml);
								unset($o);
								continue;
							}

							// Populate the title and description if they exist.
							if (!empty($menu['title']))
							{
								$o->title = trim((string) $menu['title']);
							}

							if (!empty($menu->message[0]))
							{
								$o->description = trim((string) $menu->message[0]);
							}
						}
					}
				}
				// Add the layout to the options array.
				$options[] = $o;
			}
		}
		return $options;
	}
}
