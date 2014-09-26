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

//jimport ( 'joomla.html.pane' );
//$pane = JPane::getInstance ('tabs');
$options = array(
	'onActive' => 'function(title, description){
		description.setStyle("display", "block");
		title.addClass("open").removeClass("closed");
	}',
	'onBackground' => 'function(title, description){
		description.setStyle("display", "none");
		title.addClass("closed").removeClass("open");
	}',
	'startOffset' => 0, // 0 starts on the first tab, 1 starts the second, etc...
	'useCookie' => true, // this must not be a string. Don't use quotes.
);
?>
<script type="text/javascript">
 		function randomString() {
	    var gencode = document.getElementById('IJOOMER_ENC_KEY'),
	        length = document.getElementById('length').value,
	        chars = document.getElementById('chars').value,
	        mask = '',
	        result = '';

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

	window.addEventListener('load', function load(){
	    document.getElementById('generate').addEventListener('click', randomString);
	});
</script>
<form action="<?php echo JRoute::_ ( $this->request_url )?>" method="post" name="adminForm" id="adminForm">
	<div class="span12">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#page-GLOBAL" data-toggle="tab"><?php echo JText::_('COM_IJOOMERADV_GLOBAL_CONFIG');?></a></li>
			<li><a href="#page-THEME" data-toggle="tab"><?php echo JText::_('COM_IJOOMERADV_THEME_CONFIG');?></a></li>
			<li><a href="#page-PUSH" data-toggle="tab"><?php echo JText::_('COM_IJOOMERADV_PUSH_CONFIG');?></a></li>
			<li><a href="#page-ENCRYPTION" data-toggle="tab"><?php echo JText::_('COM_IJOOMERADV_ENCRYPTION');?></a></li>
		</ul>

		<div id="config-document" class="tab-content">
			<div id="page-GLOBAL" class="tab-pane active">
				<div class="row-fluid">
					<div class="span12">
						<table class="paramlist admintable" width="50%">
							<?php foreach($this->globalConfig as $key=>$value):?>
								<tr>
									<td class="paramlist_key" width="40%">
										<span class="hasTip" title="<?php echo $value->caption; ?>::<?php echo $value->description; ?>">
											<?php echo $value->caption; ?>
										</span>
									</td>
									<td><?php echo $value->html; ?></td>
								</tr>
							<?php endforeach; ?>
						</table>
					</div>
				</div>
			</div>

			<div id="page-THEME" class="tab-pane">
				<div class="row-fluid">
					<div class="span12">
						<table class="paramlist admintable" width="50%" cellspacing="0" cellpadding="0">
							<?php foreach($this->themeConfig as $key=>$value):?>
								<tr>
									<td class="paramlist_key" width="40%">
										<span class="hasTip" title="<?php echo $value->caption; ?>::<?php echo $value->description; ?>">
											<?php echo $value->caption; ?>
										</span>
									</td>
									<td><?php echo $value->html; ?></td>
								</tr>
							<?php endforeach; ?>
						</table>
					</div>
				</div>
			</div>

			<div id="page-PUSH" class="tab-pane">
				<div class="row-fluid">
					<div class="span12">
						<fieldset class="adminform">
							<legend><?php echo JText::_('COM_IJOOMERADV_IPHONE');?></legend>
							<table class="paramlist admintable" width="50%" cellspacing="0" cellpadding="0">
								<?php
									foreach($this->pushConfigIphone as $key=>$value):
								?>
										<tr>
											<td class="paramlist_key" width="40%">
												<span class="hasTip" title="<?php echo $value->caption; ?>::<?php echo $value->description; ?>">
													<?php echo $value->caption; ?>
												</span>
											</td>
											<td><?php echo $value->html; ?></td>
										</tr>
								<?php endforeach; ?>
							</table>
						</fieldset>
						<br/>
						<fieldset class="adminform">
							<legend><?php echo JText::_('COM_IJOOMERADV_ANDROID');?></legend>
							<table class="paramlist admintable" width="50%" cellspacing="0" cellpadding="0">
								<?php
									foreach($this->pushConfigAndroid as $key=>$value):
								?>
										<tr>
											<td class="paramlist_key" width="40%">
												<span class="hasTip" title="<?php echo $value->caption; ?>::<?php echo $value->description; ?>">
													<?php echo $value->caption; ?>
												</span>
											</td>
											<td><?php echo $value->html; ?></td>
										</tr>
								<?php endforeach; ?>
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
				foreach($this->encryption as $key=>$value):?>
					<tr>
						<td class="paramlist_key" width="40%">
							<span class="hasTip" title="<?php echo $value->caption; ?>::<?php echo $value->description; ?>">
								<?php echo $value->caption; ?>
							</span>
						</td>
						<td><?php echo $value->html; ?></td>

				<?php
				$keyval =  $value->value;
				endforeach;?>
				<?php if($keyval == ''){?>
				<td>
				<?php
					//$md5_hash = md5(rand(0,999));
					//We don't need a 32 character long string so we trim it down to 5
					//$security_code = substr($md5_hash, 15, 5);
						?>
							<form>
			    				<label for="length" style="display:none;">Length: </label>
			    				<input type="text" id="length" name="length" value="16" style="display:none;">
							    <label for="characters" style="display:none;">Characters: </label>
							    <input type="text" id="chars" name="chars" value="abcde" style="display:none;">
							    <label for="gencode" style="display:none;"> Generated Code: </label>
							    <!-- input type="text" id="IJOOMER_ENC_KEY" name="IJOOMER_ENC_KEY" style="display:none;"-->
							   	<label style="display:none;"> Generate Key: </label>
							   	<button class="btn btn-small" type="button" id="generate" style="margin-top:-10px;">Generate&nbsp;Key</button>
							</form>
				</td>
				<?php }?>
				</tr>
				</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="clr"></div>



	<input type="hidden" name="option" value="com_ijoomeradv" />
	<input type="hidden" name="view" value="config" />
	<input type="hidden" name="task" value="" />
</form>