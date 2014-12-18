<?php
	/**
	 * @package     Joomla.Administrator
	 * @subpackage  com_category
	 *
	 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE.txt
	 */
// No direct access to this file
defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_category
 *
 * @since       1.6
 */
class JFormFieldPushnotif extends JFormFieldList
{
	/**
	 * A flexible category list that respects access controls
	 *
	 * @var string
	 * @since 1.6
	 */
	public $type = 'Pushnotif';

	/**
	 * Ajax Field function.
	 *
	 * @param   string  $selector  parameter of ajaxfield function.
	 *
	 * @return  void
	 */
	public function ajaxfield($selector='#jform_parent_id')
	{

		// Tags field ajax
		$input = JFactory::getApplication()->input;
		$id = $input->getInt('id');

		$chosenAjaxSettings = new JRegistry(
			array(
				'selector'    => $selector,
				'type'        => 'GET',
				'url'         => JUri::root() . 'administrator/index.php?option=com_ijoomeradv&view=pushnotif&task=searchAjax&id=' . $id,
				'dataType'    => 'json',
				'jsonTermKey' => 'like'
			)
		);
		JHtml::_('formbehavior.ajaxchosen', $chosenAjaxSettings);
	}

	/**
	 * Get Input function.
	 *
	 * @return  void
	 */
	public function getInput()
	{
		$id    = isset($this->element['id']) ? $this->element['id'] : null;
		$cssId = '#' . $this->getId($id, $this->element['name']);

		// Load the ajax-chosen customised field
		$this->ajaxfield($cssId);

		// Make options selected by setting value to an array
		if (!is_array($this->value))
		{
			$this->value = explode(',', $this->value);
		}

		$input = parent::getInput();

		return $input;
	}

}
