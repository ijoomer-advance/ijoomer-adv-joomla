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
jimport ( 'joomla.html.pane' );
$pane = JPane::getInstance ('tabs');
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
	<div class="editcell width-100">
		<?php 
		echo $pane->startPane('globalConfig');
		//Global Config
		echo $pane->startPanel(JText::_('COM_IJOOMERADV_GLOBAL_CONFIG'),'COM_IJOOMERADV_GLOBAL_CONFIG'); ?>
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
		<?php echo $pane->endPanel(); ?>
		
		<?php 
		// Theme Config
		echo $pane->startPanel(JText::_('COM_IJOOMERADV_THEME_CONFIG'),'COM_IJOOMERADV_THEME_CONFIG'); ?>
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
		<?php echo $pane->endPanel(); ?>
		
		<?php 
		// Push Notification
		echo $pane->startPanel(JText::_('COM_IJOOMERADV_PUSH_CONFIG'),'COM_IJOOMERADV_PUSH_CONFIG'); ?>
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
		<?php echo $pane->endPanel();
		//encryption 
		echo $pane->startPanel(JText::_('COM_IJOOMERADV_ENCRYPTION'),'COM_IJOOMERADV_ENCRYPTION'); ?>
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
					/*$md5_hash = md5(rand(0,999)); 
					//We don't need a 32 character long string so we trim it down to 5 
					$security_code = substr($md5_hash, 15, 5);*/
						?>
							<form>
			    				<label for="length" style="display:none;">Length: </label>
			    				<input type="text" id="length" name="length" value="16" style="display:none;">
							    <label for="characters" style="display:none;">Characters: </label>
							    <input type="text" id="chars" name="chars" value="abcde" style="display:none;">
							    <label for="gencode" style="display:none;"> Generated Code: </label>
							    <!-- input type="text" id="IJOOMER_ENC_KEY" name="IJOOMER_ENC_KEY" style="display:none;"-->
							   	<label style="display:none;"> Generate Key: </label>
							   	<button type="button" id="generate" style=" float: left; height: 20px; margin-right: -8px;">Generate Key</button>
							</form>
				</td>
				<?php }?>
				</tr>			
				</table>
		<?php echo $pane->endPanel(); 
		
				echo $pane->endPane();?>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="option" value="com_ijoomeradv" />
	<input type="hidden" name="view" value="config" />
	<input type="hidden" name="task" value="" />
</form>