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

class ijoomeradvAdminHelper
{
	protected $db;

	function __construct()
	{
		$this->db = JFactory::getDBO();
	}

	function getComponent($option)
	{
		$version = new JVersion;

		$query = "SELECT e.extension_id AS 'id', e.element AS 'option', e.params, e.enabled
				FROM #__extensions as e
				WHERE e.type='component'
				AND e.element = '{$option}'";
		$this->db->setQuery($query);
		$components = $this->db->loadObject();

		if (count($components) > 0 && $components->enabled == 1)
		{
			return true;
		}
		return false;
	}

	function getPlugin($option)
	{
		$query = "SELECT count(*)
				FROM #__ijoomeradv_extensions
				WHERE `option` = '{$option}' ";
		$this->db->setQuery($query);
		$plugins = $this->db->loadResult();

		return ($plugins) ? 1 : 0;
	}

	function getJomSocialVersion()
	{
		JHTML::_('behavior.tooltip', '.hasTip');
		$parser =& JFactory::getXMLParser('Simple');
		$xml = JPATH_ADMINISTRATOR . '/components/com_community/community.xml';

		if (file_exists($xml))
		{
			$parser->loadFile($xml);
			$doc =& $parser->document;
			$element =& $doc->getElementByPath('version');
			$version = $element->data();

			$cv = explode('.', $version);
			$cversion = $cv[0] . $cv[1];

			return $cversion;
		}
		return true;
	}

	//function to define global config
	function getglobalconfig()
	{
		$query = "SELECT *
				FROM #__ijoomeradv_config";
		$this->db->setQuery($query);
		$rows = $this->db->loadObjectlist();

		foreach ($rows as $row)
		{
			define($row->config_name, $row->config_value);
		}

		return true;
	}

	function prepareHTML(&$config)
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