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

$options = array(
	'onActive' => 'function(title, description){
		description.setStyle("display", "block");
		title.addClass("open").removeClass("closed");
	}',
	'onBackground' => 'function(title, description){
		description.setStyle("display", "none");
		title.addClass("closed").removeClass("open");
	}',

	// 0 starts on the first tab, 1 starts the second, etc...
	'startOffset' => 0,

	// This must not be a string. Don't use quotes.
	'useCookie' => true,
);
?>

<script type="text/javascript">
	function randomString() {
			var	gencode = document.getElementById('IJOOMER_ENC_KEY'),
				length  = document.getElementById('length').value,
				chars   = document.getElementById('chars').value,
				mask    = '',
				result  = '';

		console.log(length, chars);

		if (chars.indexOf('a') > -1) mask += 'abcdefghijklmnopqrstuvwxyz';
		if (chars.indexOf('A') > -1) mask += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if (chars.indexOf('#') > -1) mask += '0123456789';

		for (var i = length; i > 0; --i) {
			result += mask[Math.round(Math.random() * (mask.length - 1))];
		}

		console.log(result);

		gencode.value = result;
	}

	window.addEventListener('load', function load() {
		document.getElementById('generate').addEventListener('click', randomString);
	});


	jQuery(document).ready(function()
	{
		jQuery("#SandBox").show();
		jQuery("#Live").hide();

		jQuery("#IJOOMER_PUSH_DEPLOYMENT_IPHONE").change(function(event) {
			jQuery("#SandBox").hide();
			jQuery("#Live").hide();
			var selectedValue = jQuery("#IJOOMER_PUSH_DEPLOYMENT_IPHONE").val();

		if (selectedValue == '0') {
			jQuery("#SandBox").show();
			jQuery("#Live").hide();
		};

		if (selectedValue == '1') {
			jQuery("#SandBox").hide();
			jQuery("#Live").show();
		};

	});
});


</script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

	<div class="span12" >
		<ul class="nav nav-tabs">
			<li class="active"><a href="#page-GLOBAL"
			                      data-toggle="tab"><?php echo JText::_('COM_IJOOMERADV_GLOBAL_CONFIG'); ?></a></li>
			<li><a href="#page-THEME" data-toggle="tab"><?php echo JText::_('COM_IJOOMERADV_THEME_CONFIG'); ?></a></li>
			<li><a href="#page-PUSH" data-toggle="tab"><?php echo JText::_('COM_IJOOMERADV_PUSH_CONFIG'); ?></a></li>
			<li><a href="#page-ENCRYPTION" data-toggle="tab"><?php echo JText::_('COM_IJOOMERADV_ENCRYPTION'); ?></a>
			</li>
		</ul>

		<div id="config-document" class="tab-content">
			<div id="page-GLOBAL" class="tab-pane active">
				<div class="row-fluid">
					<div class="span12">
						<table class="paramlist admintable" width="50%">
							<?php
								foreach ( $this->globalConfig as $key => $value):
							?>
								<tr>
									<td class="paramlist_key" width="40%">
										<span class="hasTip"
										      title="<?php echo $value->caption; ?>::
										      <?php echo $value->description; ?>">
											<?php echo $value->caption; ?>
										</span>
									</td>
									<td><?php echo $value->html; ?></td>
								</tr>
							<?php
								endforeach;
							?>
						</table>
					</div>
				</div>
			</div>

			<div id="page-THEME" class="tab-pane">
				<div class="row-fluid">
					<div class="span12">
						<table class="paramlist admintable" width="50%" cellspacing="0" cellpadding="0">
							<?php
								foreach ($this->themeConfig as $key => $value):
							?>
								<tr>
									<td class="paramlist_key" width="40%">
										<span class="hasTip"
										      title="<?php echo $value->caption; ?>::
										      <?php echo $value->description; ?>">
											<?php echo $value->caption; ?>
										</span>
									</td>
									<td><?php echo $value->html; ?></td>
								</tr>
							<?php
								endforeach;
							?>
						</table>
					</div>
				</div>
			</div>

			<div id="page-PUSH" class="tab-pane">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="adminform">
							<legend><?php echo JText::_('COM_IJOOMERADV_IPHONE'); ?></legend>
							<table class="paramlist admintable" width="50%" cellspacing="0" cellpadding="0">
								<?php
								foreach ($this->pushConfigIphone as $key => $value):
									?>
									<tr>
										<td class="paramlist_key" width="40%">
												<span class="hasTip"
												      title="<?php echo $value->caption; ?>::
												      <?php echo $value->description; ?>">
													<?php echo $value->caption; ?>
												</span>
										</td>
										<td><?php echo $value->html; ?></td>
									</tr>
								<?php
								endforeach;
								?>
							</table>

								<div class="row" id="SandBox" style="margin-left:2px;margin-top:8px;">

										<div class="span3">
											Upload File For SandBox
										</div>

										<div class="span3" width="40%">
											<input type="file" name="SandBox" id="sandbox">
										</div>
										<div class="span3" width="40%">
											<?
											if(file_exists(JPATH_SITE ."/components/com_ijoomeradv/certificates/dev_certificates.pem"))
											{
													echo '<a href="../administrator/components/com_ijoomeradv/views/config/tmpl/downloadsandbox.php">Download File</a>';
											}
											?>
										</div>
								</div>
								<div class="row" id="Live" style="margin-left:2px;margin-top:8px;">
									<div class="span3">
											Upload File For Live
									</div>

									<div class="span3">
										<input type="file" name="live">
									</div>
									<div class="span3" width="40%">
											<?
											if(file_exists(JPATH_SITE ."/components/com_ijoomeradv/certificates/pro_certificates.pem"))
											{
													echo '<a href="../administrator/components/com_ijoomeradv/views/config/tmpl/downloadlive.php">Download File</a>';
											}
											?>
									</div>
								</div>
						</fieldset>
						<br/>
						<fieldset class="adminform">
							<legend><?php echo JText::_('COM_IJOOMERADV_ANDROID'); ?></legend>
							<table class="paramlist admintable" width="50%" cellspacing="0" cellpadding="0">
								<?php
								foreach ($this->pushConfigAndroid as $key => $value):
									?>
									<tr>
										<td class="paramlist_key" width="40%">
												<span class="hasTip"
												      title="<?php echo $value->caption; ?>::
												      <?php echo $value->description; ?>">
													<?php echo $value->caption; ?>
												</span>
										</td>
										<td><?php echo $value->html; ?></td>
									</tr>
								<?php
								endforeach;
								?>
							</table>
						</fieldset>
					</div>
				</div>
			</div>

			<div id="page-ENCRYPTION" class="tab-pane">
				<div class="row-fluid">
					<div class="span12">
						<table class="paramlist admintable" width="50%">
							<?php
							foreach ($this->encryption as $key => $value):
							?>
							<tr>
								<td class="paramlist_key" width="40%">
							<span class="hasTip"
							      title="<?php echo $value->caption; ?>::
							      <?php echo $value->description; ?>">
								<?php echo $value->caption; ?>
							</span>
								</td>
								<td><?php echo $value->html; ?></td>

								<?php
								$keyval = $value->value;
							endforeach;
								?>
								<?php if ($keyval == '')
								{
								?>
									<td>
										<form>
											<label for="length" style="display:none;">Length: </label>
											<input type="text" id="length" name="length" value="16"
											       style="display:none;">
											<label for="characters" style="display:none;">Characters: </label>
											<input type="text" id="chars" name="chars" value="abcde"
											       style="display:none;">
											<label for="gencode" style="display:none;"> Generated Code: </label>
											<label style="display:none;"> Generate Key: </label>
											<button class="btn btn-small" type="button" id="generate"
											        style="margin-top:-10px;">Generate&nbsp;Key
											</button>
										</form>
									</td>
								<?php
}
								?>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="clr"></div>


	<input type="hidden" name="option" value="com_ijoomeradv"/>
	<input type="hidden" name="view" value="config"/>
	<input type="hidden" name="task" value=""/>
</form>
