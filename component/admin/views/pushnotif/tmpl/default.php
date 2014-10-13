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
<script type="text/javascript"
        src="<?php echo JURI::root() ?>administrator/components/com_ijoomeradv/assets/js/jquery.js"></script>
<script type='text/javascript'
        src="<?php echo JURI::root() ?>administrator/components/com_ijoomeradv/assets/js/jquery.autocomplete.js"></script>
<link rel="stylesheet" type="text/css"
      href="<?php echo JURI::root() ?>administrator/components/com_ijoomeradv/assets/css/jquery.autocomplete.css"/>
<script>
	$().ready(function () {
		//$.noConflict();
		$('input[value="customs"]').click(function () {
			$('#userid').show(1000);
		});

		$('input[value="1"]').click(function () {
			$('#userid').hide(100);
		});

		$('#customs').click(function () {
			$('#disp_btn').show()
		});

		var months = [
			<?php

			for ($i = 0; $i < count($this->users); $i++)
			{
			?>
			'<?php echo $this->users[$i]; ?>',
			<?php
			}
		?>
		];

		$("#send_to_username").autocomplete(months, {
			minChars: 0,
			max: 12,
			autoFill: true,
			mustMatch: true,
			matchContains: false,
			scrollHeight: 220,
			formatItem: function (data, i, total) {
				return data[0];
			}
		});

	});
</script>

<script language="javascript" type="text/javascript">

	public function changeVal() {

		if (document.adminForm.send_to_username.value == "") {
			alert("Please select User Name");
		} else {
			if (document.adminForm.to_user.value == "") {
				document.adminForm.to_user.value = document.adminForm.send_to_username.value;
			} else {
				if (document.adminForm.to_user.value.indexOf(document.adminForm.send_to_username.value) != -1) {
					alert("User already Exists");
				} else {
					document.adminForm.to_user.value += "," + document.adminForm.send_to_username.value;
				}
			}
		}
		document.adminForm.send_to_username.value = "";
	}
</script>


<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm"
      enctype="multipart/form-data">
	<div class="editcell">
		<table class="adminlist table table-striped" width="100%">
			<tbody>
			<tr>
				<td width="300px" valign="top">
					<table>
						<tr>
							<td class="title"><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_DEVICE_TYPE'); ?></td>
							<td>
								<!-- <input type='Radio' Name='device_type' value='iphone' /><?PHP echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_DEVICE_TYPE_IPHONE'); ?>  -->
								<input type='Radio' Name='device_type'
								       value='android'/><?PHP echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_DEVICE_TYPE_ENDROID'); ?>
								<!-- <input type='Radio' Name ='device_type' value='both'  /><?PHP echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_DEVICE_TYPE_BOTH'); ?>  -->
							</td>
						</tr>

						<tr>
							<td class="title"><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_SEND_NOTIFICATION_TO'); ?></td>
							<td>
								<input type='radio' name="to_all" id="to_all"
								       value='1'/><?PHP echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_SEND_NOTIFICATION_TO_ALL_USERS'); ?>
								<Input type='radio' name="to_all" value='customs'
								       id="customs"/><?PHP echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_SEND_NOTIFICATION_TO_CUSTOMS'); ?>
								<div style="display:none" id="userid">
									<input type="text" name="send_to_username" id="send_to_username" value=""/>&nbsp;&nbsp;
									<input type="button" name="add_uid" id="add_uid" value="Add User"
									       onClick="changeVal()"/>&nbsp;&nbsp;
									<input type="text" name="to_user" id="to_user" value=""/>
								</div>
							</td>
						</tr>

						<tr>
							<td class="title"><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_NOTIFICATION_TEXT'); ?></td>
							<td>
								<textarea rows="9" cols="30" name="message" id="message"></textarea>
							</td>
						</tr>

						<tr>
							<td class="title"><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_NOTIFICATION_LINK'); ?></td>
							<td>
								<input type="text" id="link" name="link" size="30"/>
							</td>
						</tr>
					</table>
				</td>

				<td valign="top">
					<table class="adminlist table table-striped" width="100%">
						<thead>
						<tr>
							<th width="20px"><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_ID') ?></th>
							<th width="90px"><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_DEVICE_TYPE') ?></th>
							<th width="60px"><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_SEND_NOTIFICATION_TO_ALL_USERS') ?></th>
							<th width="90px"><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_TO_USER') ?></th>
							<th><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_NOTIFICATION_TEXT') ?></th>
							<th width="90px"><?php echo JText::_('COM_IJOOMERADV_PUSH_NOTIFICATION_TIME') ?></th>
						</tr>
						</thead>
						<tbody>
						<?php
						$k = 0;

						if (!empty($this->pushNotifications))
						{
							foreach ($this->pushNotifications as $key => $value)
							{
								?>
								<tr class="row<?php echo $k; ?>">
									<td><?php echo $value['id']; ?></td>
									<td><?php echo $value['device_type']; ?></td>
									<td><?php echo $value['to_user']; ?></td>
									<td><?php echo $value['to_all']; ?></td>
									<td><?php echo $value['message']; ?></td>
									<td><?php echo $value['time']; ?></td>
								</tr>
							<?php
							}

							$k = 1 - $k;
						}
						else
						{
							echo '<tr><td colspan="6" align="center">There is no data to show.</td></tr>';
						}
						?>
						</tbody>
						<tfoot>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
						<th>&nbsp;
						</td>
						<th>&nbsp;
						</td>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
						</tfoot>
					</table>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_ijoomeradv"/>
	<input type="hidden" name="view" value="pushnotif"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value=""/>
</form>
