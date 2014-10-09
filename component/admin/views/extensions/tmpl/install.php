<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

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
