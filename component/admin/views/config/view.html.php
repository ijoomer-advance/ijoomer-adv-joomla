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

class ijoomeradvViewconfig extends JViewLegacy
{
	function display($tpl = null)
	{

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_IJOOMERADV_CONFIGURATION'));

		$uri = JFactory::getURI();

		JToolBarHelper::title(JText::_('COM_IJOOMERADV_CONFIGURATION'), 'config_48');
		JToolBarHelper::custom('home', 'home', '', JText::_('COM_IJOOMERADV_HOME'), false, false);
		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::cancel('cancel', 'Close');

		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_EXTENSIONS'), 'index.php?option=com_ijoomeradv&view=extensions', (JRequest::getVar('view') == 'extensions' && JRequest::getVar('layout') != 'manage'));
		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_EXTENSIONS_MANAGER'), 'index.php?option=com_ijoomeradv&view=extensions&layout=manage', (JRequest::getVar('view') == 'extensions' && JRequest::getVar('layout') == 'manage'));
		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_GLOBAL_CONFIGURATION'), 'index.php?option=com_ijoomeradv&view=config', JRequest::getVar('view') == 'config');
		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_MENUS'), 'index.php?option=com_ijoomeradv&view=menus', JRequest::getVar('view') == 'menus');
		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION'), 'index.php?option=com_ijoomeradv&view=pushnotif', JRequest::getVar('view') == 'pushnotif');
		JSubMenuHelper::addEntry(JText::_('COM_IJOOMERADV_REPORT'), 'index.php?option=com_ijoomeradv&view=report', JRequest::getVar('view') == 'report');

		$model = $this->getModel('config');

		$globalConfig = $model->getConfig('global');
		$this->prepareHTML($globalConfig);

		$themeConfig = $model->getConfig('theme');
		$this->prepareHTML($themeConfig);

		$pushConfigIphone = $model->getConfig('push>>iphone');
		$this->prepareHTML($pushConfigIphone);

		$pushConfigAndroid = $model->getConfig('push>>android');
		$this->prepareHTML($pushConfigAndroid);

		$encryption = $model->getConfig('encryption');
		$this->prepareHTML($encryption);


		$this->assignRef('globalConfig', $globalConfig);
		$this->assignRef('themeConfig', $themeConfig);
		$this->assignRef('pushConfigIphone', $pushConfigIphone);
		$this->assignRef('pushConfigAndroid', $pushConfigAndroid);
		$this->assignRef('encryption', $encryption);
		$this->assignRef('request_url', $uri->toString());

		parent::display($tpl);
	}

	function prepareHTML(&$config)
	{
		foreach ($config as $key => $value)
		{
			$config[$key]->caption = JText::_($value->caption);
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
					if ($value->name == 'IJOOMER_ENC_KEY')
					{
						if (empty($value->value))
						{
							$input .= '<input type="' . $value->type . '" name="' . $value->name . '" id="' . $value->name . '" value="' . $value->value . '" disabled = "disable" maxlength="16" />';
						}
						else
						{
							$input .= '<input type="' . $value->type . '" name="' . $value->name . '" id="' . $value->name . '" value="' . $value->value . '" maxlength="16" />';
						}
					}
					else
					{
						$input .= '<input type="' . $value->type . '" name="' . $value->name . '" id="' . $value->name . '" value="' . $value->value . '" />';
					}

					break;
			}
			$config[$key]->html = $input;
		}
	}

}