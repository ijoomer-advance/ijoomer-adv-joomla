<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : ICMS_1.5 (compatible with joomla 3.0)
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined('_JEXEC') or die;

class icms_helper
{

	private $db_helper;

	function __construct()
	{
		$this->db_helper =  JFactory::getDBO();
	}

}