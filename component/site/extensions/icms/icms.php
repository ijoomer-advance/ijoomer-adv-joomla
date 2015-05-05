<?php
/**
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.extensions
 *
 * @copyright   Copyright (C) 2010 - 2015 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For The Icms
 *
 * @since  1.0
 */
class Icms
{
	public $classname = "icms";

	public $sessionWhiteList = array('articles.archive', 'articles.featured', 'articles.singleArticle', 'articles.articleDetail', 'categories.allCategories', 'categories.singleCategory', 'categories.category', 'categories.categoryBlog');

	/**
	 * The Init Function
	 *
	 * @return  void
	 */
	public function init()
	{
		include_once JPATH_SITE . '/components/com_content/models/category.php';
		include_once JPATH_SITE . '/components/com_content/models/archive.php';
		include_once JPATH_SITE . '/components/com_content/helpers/query.php';

		$lang = JFactory::getLanguage();
		$lang->load('com_content');
		$plugin_path = JPATH_COMPONENT_SITE . '/extensions';
		$lang->load('icms', $plugin_path . '/icms', $lang->getTag(), true);
	}

	/**
	 * The Write_Configuration Function
	 *
	 * @param   [type]  &$d  it will contain the value of d
	 *
	 * @return  array   $jassonarray
	 */
	public function write_configuration(&$d)
	{
		$db =JFactory::getDbo();

		$query = 'SELECT *
				  FROM #__ijoomeradv_icms_config';
		$db->setQuery($query);
		$my_config_array = $db->loadObjectList();

		foreach ($my_config_array as $ke => $val)
		{

			if (isset($d[$val->name]))
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base update statement.
				$query->update($db->qn('#__ijoomeradv_icms_config'))
					->set($db->qn('value') . ' = ' . $db->q($d[$val->name]))
					->where($db->qn('name') . ' = ' . $db->q($val->name));

				// Set the query and execute the update.
				$db->setQuery($query);

				$db->execute();

			}
		}
	}

	/**
	 * The Get Config Function
	 *
	 * @return  array $jsonarray
	 */
	public function getconfig()
	{
		$jsonarray = array();

		return $jsonarray;
	}

	/**
	 * The Prepare HTML Function
	 *
	 * @param   [type]  &$Config  contains the value of config
	 *
	 * @return  void
	 */
	public function prepareHTML(&$Config)
	{
		// @TODO : Prepare custom html for ICMS
	}
}

/**
 * The Class For The Icms_Menu
 *
 * @since  1.0
 */
class icms_menu
{
	/**
	 * The Get Required Input Function
	 *
	 * @param   [type]  $extension    contains the value of extension
	 * @param   [type]  $extView      contains the value of extview
	 * @param   [type]  $menuoptions  contains the value of menuoptions
	 *
	 * @return  it will returns the value of $html
	 */
	public function getRequiredInput($extension, $extView, $menuoptions)
	{
		$menuoptions = json_decode($menuoptions, true);

		switch ($extView)
		{
			case 'categoryBlog':
				$selvalue = $menuoptions['remoteUse']['id'];
				require_once JPATH_ADMINISTRATOR . '/components/com_categories/models/categories.php';

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('*')
					->from($db->qn('#__categories'))
					->where($db->qn('extension') . ' = ' . $db->q('com_content'));

				// Set the query and load the result.
				$db->setQuery($query);

				$items = $db->loadObjectList();

				$html = '<fieldset class="panelform">
							<label title="" class="hasTip required" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">' . JText::_('COM_IJOOMERADV_ICMS_SELECT_CATEGORY') . '
								<span class="star">&nbsp;*</span>
							</label>';

				$html .= '<select name="jform[request][id]" id="jform_request_id">';

				foreach ($items as $key1 => $value1)
				{
					$selected = '';

					if ($selvalue == $value1->id)
					{
						$selected = 'selected';
					}

					$level = '';

					for ($i = 1; $i < $value1->level; $i++)
					{
						$level .= '-';
					}

					$html .= '<option value="' . $value1->id . '" ' . $selected . '>' . $level . $value1->title . '</option>';
				}

				$html .= '</select>';
				$html .= '</fieldset>';

				return $html;
				break;

			case 'singleCategory':
				$selvalue = $menuoptions['remoteUse']['id'];
				require_once JPATH_ADMINISTRATOR . '/components/com_categories/models/categories.php';

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('*')
					->from($db->qn('#__categories'))
					->where($db->qn('extension') . ' = ' . $db->q('com_content'));

				// Set the query and load the result.
				$db->setQuery($query);
				$items = $db->loadObjectList();

				$html = '<fieldset class="panelform">
							<label title="" class="hasTip required" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">' . JText::_('COM_IJOOMERADV_ICMS_SELECT_CATEGORY') . '
								<span class="star">&nbsp;*</span>
							</label>';

				$html .= '<select name="jform[request][id]" id="jform_request_id">';

				foreach ($items as $key1 => $value1)
				{
					$selected = '';

					if ($selvalue == $value1->id)
					{
						$selected = 'selected';
					}

					$level = '';

					for ($i = 1; $i < $value1->level; $i++)
					{
						$level .= '-';
					}

					$html .= '<option value="' . $value1->id . '" ' . $selected . '>' . $level . $value1->title . '</option>';
				}

				$html .= '</select>';
				$html .= '</fieldset>';

				return $html;
				break;

			case 'singleArticle':
				$selvalue = (isset($menuoptions['remoteUse']['id'])) ? $menuoptions['remoteUse']['id'] : 0;

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('title')
					->from($db->qn('#__content'))
					->where($db->qn('id') . ' = ' . $db->q($selvalue));

				// Set the query and load the result.
				$db->setQuery($query);

				$result = $db->loadResult();

				if ($result)
				{
					$title = $result;
				}
				else
				{
					$title = 'COM_IJOOMERADV_ICMS_CHANGE_ARTICLE';
				}
				// Load the modal behavior script.
				JHtml::_('behavior.modal', 'a.modal');

				// Build the script.
				$script = array();
				$script[] = '	function jSelectArticle_jform_request_id(id, title, catid, object) {';
				$script[] = '		document.id("jform_request_id_id").value = id;';
				$script[] = '		document.id("jform_request_id_name").value = title;';
				$script[] = '		SqueezeBox.close();';
				$script[] = '	}';

				// Add the script to the document head.
				JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

				// Setup variables for display.
				$html = array();
				$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_jform_request_id';

				// The current user display field.
				$html[] = '<div class="controls">';
				$html[] = '<span class="input-append">';
				$html[] = '<input class="input-medium" type="text" id="jform_request_id_name" value="' . JText::_($title) . '" disabled="disabled" size="35" />';

				// The user select button.
				$html[] = '<a class="modal btn" title="' . JText::_('COM_IJOOMERADV_ICMS_CHANGE_ARTICLE') . '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">' . JText::_('COM_IJOOMERADV_ICMS_CHANGE_ARTICLE_BUTTON') . '</a>';
				$html[] = '</span>';
				$html[] = '</div>';

				$html[] = '<input type="hidden" id="jform_request_id_id" name="jform[request][id]" value="" />';

				return implode("\n", $html);
				break;
		}
	}

	/**
	 * The Set Required Input Function
	 *
	 * @param   [type]  $extension    contains The Value of $extension
	 * @param   [type]  $extView      contains The Value of $extview
	 * @param   [type]  $extTask      contains The Value of $exttask
	 * @param   [type]  $remoteTask   contains The Value of $remotetask
	 * @param   [type]  $menuoptions  contains The Value of $menuoption
	 * @param   [type]  $data         contains The Value of $data
	 *
	 * @return  void
	 */
	public function setRequiredInput($extension, $extView, $extTask, $remoteTask, $menuoptions, $data)
	{
		$db = JFactory::getDBO();
		$options = null;

		switch ($extTask)
		{
			case 'categoryBlog':
				$categoryid = $menuoptions['id'];
				$options = '{"serverUse":{},"remoteUse":{"id":' . $categoryid . '}}';
				break;

			case 'singleCategory':
				$categoryid = $menuoptions['id'];
				$options = '{"serverUse":{},"remoteUse":{"id":' . $categoryid . '}}';
				break;

			case 'singleArticle':
				$articleid = $menuoptions['id'];
				$options = '{"serverUse":{},"remoteUse":{"id":' . $articleid . '}}';
				break;
		}

		if ($options)
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$where = $extension . "." . $extView . "." . $extTask . "." . $remoteTask;

			// Create the base update statement.
			$query->update($db->qn('#__ijoomeradv_menu'))
				->set($db->qn('menuoptions') . ' = ' . $db->q($options))
				->where($db->qn('views') . ' = ' . $db->q($where))
				->where($db->qn('id') . ' = ' . $db->q($data['id']));

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();
		}
	}
}
