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

defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col50">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'COM_IJOOMERADV_INSTALL_UPDATE_EXTENSIONS' ); ?></legend>
		<table class="admintable" width="100%"> 
		 <tr>
			 <td>
			 	<input type="file" name="install_extension" size="75">
			 	<input class="extensionInstallButton" type="submit" value="Install"></td>
		</tr>		
	  </table>
	</fieldset>
</div> 

<div class="clr"></div>

<input type="hidden" name="option" value="com_ijoomeradv" />
<input type="hidden" name="view" value="extensions" />
<input type="hidden" name="task" value="install" />
</form>