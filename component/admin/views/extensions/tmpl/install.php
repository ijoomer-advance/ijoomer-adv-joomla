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
JHTML::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm"
      enctype="multipart/form-data">
	<div class="col50">
		<fieldset class="uploadform">
			<legend><?php echo JText::_('COM_IJOOMERADV_INSTALL_UPDATE_EXTENSIONS'); ?></legend>
			<div class="control-group">
				<label class="control-label" for="install_package">Package File</label>

				<div class="controls">
					<input type="file" class="input_box" name="install_extension" size="75">
				</div>
			</div>
			<div class="form-actions">
				<input class="btn btn-primary" type="submit" value="Upload &amp; Install"
				       onclick="Joomla.submitbutton()"></td>
			</div>
		</fieldset>
	</div>

	<div class="clr"></div>

	<input type="hidden" name="option" value="com_ijoomeradv"/>
	<input type="hidden" name="view" value="extensions"/>
	<input type="hidden" name="task" value="install"/>
</form>