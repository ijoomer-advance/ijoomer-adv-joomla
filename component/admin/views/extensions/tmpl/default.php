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

JHTML::_ ( 'behavior.tooltip' );
jimport ( 'joomla.html.pane' );
?>

<form action="<?php echo JRoute::_ ( $this->request_url )?>" method="post" name="adminForm" id="adminForm">
	<div class="editcell">
		<table width="100%" cellspacing="2" cellpadding="2">
			<tr>
				<td valign="top">
					<table cellpadding="15" cellspacing="10" width="100%">
						<?php for($i = 0, $n = count ( $this->extensions ); $i < $n; $i += 7) { ?>
							<tr>
							<?php for($j = 0; $j < 7; $j ++) {
									if (isset ( $this->extensions [$i + $j] )) {
										$row = & $this->extensions [$i + $j];

										//get version
										$mainXML = JPATH_SITE.'/components/com_ijoomeradv/extensions/'.$row->classname.'.xml';
										if (is_file($mainXML)) {
											if($xml = simplexml_load_file($mainXML)){
												$version = $xml->xpath('version');
												$version = (double)$version[0][0];
											}
										}

										//get images
										$link = JRoute::_ ( 'index.php?option=com_ijoomeradv&view=extensions&task=detail&cid[]=' . $row->id );
										if (file_exists ( "components/com_ijoomeradv/assets/images/" . $row->classname . ".png" )) {
											$plg_img = "components/com_ijoomeradv/assets/images/" . $row->classname . ".png";
										} else {
											$app = & JFactory::getApplication ();
											$template = $app->getTemplate ();
											$plg_img = JURI::base () . "components" . DS . "com_ijoomeradv" . DS . "assets" . DS . "images" . DS . "default.png";
										} ?>
								<td align="center" width="33%">
									<a href=<?php echo $link?>>
										<img src="<?php echo $plg_img?>" alt="<?php echo $row->name; ?>" />
									</a><br />
									<?php if (basename ( $plg_img ) == "default.png") { ?>
										<span style="font-size: 10px;" title="<?php echo JText::_ ( 'COM_IJOOMERADV_EXTENSION_EDIT' ); ?>::<?php echo $row->name;?>">
											<a href="<?php echo $link;?>"><?php echo $row->name;?></a>
										</span>
									<?php }
									?>
									<span style="color:#333; font-size:9px; width:60px; margin-left:-3px; padding:3px;display:block; background-color:#DDD">ver - <?php echo $version;?></span>
								</td>
								<?php
									} else
									echo "<td>&nbsp;</td>";
								}
								?>
							</tr>
					<?php } ?>
					</table>
				</td>

				<td valign="top" width="100%">
				</td>
			</tr>
		</table>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="option" value="com_ijoomeradv" />
	<input type="hidden" name="view" value="extensions" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>