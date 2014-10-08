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
?>

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
	<div id="editcell">
    	<table class="adminlist table table-striped">
    		<thead>
        		<tr>
            		<th width="10px">
                		<?php echo JText::_('#'); ?>
            		</th>
            		<th width="10px">
            		</th>
            		<th width="50px">
  						&nbsp;
            		</th>
		            <th>
		                <?php echo JText::_('COM_IJOOMERADV_EXTENSIONS_NAME'); ?>
		            </th>
		            <th width="50px">
  						<?php echo JText::_('COM_IJOOMERADV_EXTENSIONS_VERSION'); ?>
            		</th>
		            <th width="300px">
		            	<?php echo JText::_('COM_IJOOMERADV_RELATED_COMPONENT'); ?>
		            </th>
		            <th width="50px">
		                <?php echo JText::_('COM_IJOOMERADV_PUBLISH'); ?>
		            </th>
		        </tr>
		    </thead>
		    <tbody>
		    <?php
			    $k = 0;
			    foreach ($this->extensions as $key=>$value){
			    	$task=($value->published)?"unpublish":"publish";
			?>
			        <tr class="<?php echo "row" . $k; ?>">
			            <td align="center" width="10px">
			                <?php echo $key+1; ?>
			            </td>
			            <td align="center" width="10px">
			            	<?php echo JHtml::_('grid.id', $key, $value->id); ?>
			            </td>
			            <td align="center" width="50px">
			                <img src="<?php echo JURI::base().'components/com_ijoomeradv/assets/images/'.$value->classname.'_48.png'; ?>" alt="<?php echo $value->name; ?>"/>
			            </td>
			            <td align="center">
			                <b><?php echo $value->name; ?></b>
			            </td>
			           	<td align="center" width="20px">
			           		<?php
			    				//get version
								$mainXML = JPATH_SITE.'/components/com_ijoomeradv/extensions/'.$value->classname.'.xml';
								if (is_file($mainXML)) {
									if($xml = simplexml_load_file($mainXML)){
										$version = $xml->xpath('version');
										$version = (double)$version[0][0];
										echo $version;
									}
								}

			           		?>
			           	</td>
			            <td align="center">
			            	<?php echo $value->option; ?>
			            </td>
			          	<td align="center">
			            	<?php echo $published  = JHTML::_('grid.published', $value, $key); ?>
			            </td>
			        </tr>
			<?php
					$k = 1 - $k;
			    }
		    ?>
		</tbody>
		<tfoot>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tfoot>
    </table>
</div>

<input type="hidden" name="option" value="com_ijoomeradv" />
<input type="hidden" name="view" value="extensions" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="task" value="" />
</form>