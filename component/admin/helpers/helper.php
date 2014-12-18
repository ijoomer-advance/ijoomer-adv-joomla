<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.helper
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die;

/**
 * The Class For IJoomeradvAdminHelper
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.Helper
 * @since       1.0
 */
class IjoomeradvAdminHelper
{
	protected $db;

	/**
	 * The Construct Function
	 */
	public function __construct()
	{
		$this->db = JFactory::getDbo();
	}

	/**
	 * The Function For Getting The Component
	 *
	 * @param   string  $option  contains the value of option
	 *
	 * @return  boolean  true or false value
	 */
	public function getComponent($option)
	{
		$this->db = JFactory::getDbo();
		$query    = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('e.enabled')
			->from($this->db->qn('#__extensions') . 'AS e')
			->where($this->db->qn('e.type') . ' = ' . $this->db->q('component'))
			->where($this->db->qn('e.element') . ' = ' . $this->db->q($option));

		// Set the query and load the result.
		$this->db->setQuery($query);
		$components = $this->db->loadObject();

		if (count($components) > 0 && $components->enabled == 1)
		{
			return true;
		}

		return false;
	}

	/**
	 * The Function For Getting The Plugin
	 *
	 * @param   [type]  $option  contains the value of option
	 *
	 * @return  boolean  true or false value
	 */
	public function getPlugin($option)
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('count(*)')
			->from($this->db->qn('#__ijoomeradv_extensions'))
			->where($this->db->qn('option') . ' = ' . $this->db->q($option));

		$this->db->setQuery($query);

		$plugins = $this->db->loadResult();

		return ($plugins) ? 1 : 0;
	}

	/**
	 * The Function For Getting The JomSocialVersion
	 *
	 * @return  boolean  true or false value
	 */
	public function getJomSocialVersion()
	{
		JHTML::_('behavior.tooltip', '.hasTip');
		$parser = JFactory::getXMLParser('Simple');
		$xml = JPATH_ADMINISTRATOR . '/components/com_community/community.xml';

		if (file_exists($xml))
		{
			$parser->loadFile($xml);
			$doc = $parser->document;
			$element = $doc->getElementByPath('version');
			$version = $element->data();

			$cv = explode('.', $version);
			$cversion = $cv[0] . $cv[1];

			return $cversion;
		}

		return true;
	}

	/**
	 * The Function For Getting The Global Config
	 *
	 * @return  boolean  true or false value
	 */
	public function getglobalconfig()
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->qn('#__ijoomeradv_config'));

		$this->db->setQuery($query);
		$rows = $this->db->loadObjectlist();

		foreach ($rows as $row)
		{
			define($row->config_name, $row->config_value);
		}

		return true;
	}

	/**
	 * The Function For Preparing The HTML
	 *
	 * @param   [type]  &$config  The  Config  Variable
	 *
	 * @return  boolean  true or false value
	 */
	public function prepareHTML(&$config)
	{
		foreach ($config as $key => $value)
		{
			$config[$key]->caption = JText::_($value->caption . '_LBL');
			$config[$key]->description = JText::_($value->description);

			$input = null;

			switch ($value->type)
			{
				case 'select':
					$input .= '<select name="' . $value->name . '" id="' . $value->name . '">';
					$options = explode(';;', $value->options);

					foreach ($options as $val)
					{
						$option = explode('::', $val);
						$selected = ($option[0] === $value->value) ? 'selected="selected"' : '';
						$input .= '<option value="' . $option[0] . '" ' . $selected . '>' . $option[1] . '</option>';
					}

					$input .= '</select>';
					break;

				case 'text':
					if ($value->name == 'IJOOMER_ENC_KEY' && $value->value != '')
					{
						$input .= '<input type="' . $value->type . '" name="' . $value->name . '" id="' . $value->name . '" value="' . $value->value . '" disabled = "disable"/>';
						$input .= '<input type="hidden" name="' . $value->name . '" id="' . $value->name . '" value="' . $value->value . '"/>';
					}
					else
					{
						$input .= '<input type="' . $value->type . '" name="' . $value->name . '" id="' . $value->name . '" value="' . $value->value . '"/>';
					}

					break;

				case 'jom_field':
					$query = "SELECT *
							FROM #__community_fields
							WHERE type!='group'";
					$this->db->setQuery($query);
					$fields = $this->db->loadObjectList();

					$input .= '<select name="' . $value->name . '" id="' . $value->name . '">';
					$input .= '<option value="">Select Field...</option>';

					if ($fields)
					{
						foreach ($fields as $field)
						{
							$selected = ($field->id === $value->value) ? 'selected="selected"' : '';
							$input .= '<option value="' . $field->id . '" ' . $selected . '>' . $field->name . '</option>';
						}
					}

					$input .= '</select>';
					break;
			}

			$config[$key]->html = $input;
		}
	}
}
