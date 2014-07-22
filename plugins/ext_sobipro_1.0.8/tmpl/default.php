<?php
 /**
 * @copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
 * @license GNU/GPL, see license.txt or http://www.gnu.org/copyleft/gpl.html
 * Developed by Tailored Solutions - ijoomer.com
 *
 * ijoomer can be downloaded from www.ijoomer.com
 * ijoomer is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * You should have received a copy of the GNU General Public License
 * along with ijoomer; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
jimport('joomla.html.pane');
?>

<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		if(document.getElementById("product_list2")){ 	
			if(document.getElementById("product_list2").value)
			{
				var varToBox = document.getElementById("product_list2");
				for(i=0;i<varToBox.length;i++){
					varToBox.options[i].selected=true;
				}
			}
		}
		
		if(document.getElementById("section_list2")){ 	
			if(document.getElementById("section_list2").value)
			{
				var varToBox1 = document.getElementById("section_list2");
				for(i=0;i<varToBox1.length;i++){
					varToBox1.options[i].selected=true;
				}
			}
		}		
		
		submitform( pressbutton );
		return;
	}

	function moveitem(list1,list2) 
	{
		var varFromBox = list1;
	 	var varToBox = list2;
	 	
	 	if ((varFromBox != null) && (varToBox != null)) 
	 	{ 
	  		if(varFromBox.length < 1) 
	  		{
	   			alert('There are no items in the source ListBox');
	   			return false;
	  		}
	  		if(varFromBox.options.selectedIndex == -1) // when no Item is selected the index will be -1
	  		{
	   			alert('Please select an Item to move');
	   			return false;
	  		}
	  		while ( varFromBox.options.selectedIndex >= 0 ) 
	  		{ 
	   			var newOption = new Option(); // Create a new instance of ListItem

	   			newOption.text = varFromBox.options[varFromBox.options.selectedIndex].text; 
		   		newOption.value = varFromBox.options[varFromBox.options.selectedIndex].value;

	   			for(x=0;x<varToBox.length;x++){
					if(varToBox.options[x].value==newOption.value){
						alert("Selected Item is already in list");
						return false;		
					}
				} 		   		 
		   		varToBox.options[varToBox.length] = newOption; //Append the item in Target Listbox		
		   		varFromBox.options[varFromBox.options.selectedIndex].selected=false;
		   	   //varFromBox.remove(varFromBox.options.selectedIndex); //Remove the item from Source Listbox	    
			} 
			for(i=0;i<varToBox.length;i++){
				varToBox.options[i].selected=true;
			}
		}	
	}
	function remove1(list2)
	{
		var varToBox = list2;
		
		for(i=varToBox.options.length-1;i>=0;i--)
		{
			if(varToBox.options[i].selected)
			{
				varToBox.remove(i);
			}
		}
		for(i=0;i<varToBox.length;i++){
			varToBox.options[i].selected=true;
		}
	}
</script>
<form action="<?php echo JRoute::_($this->request_url) ?>" method="post" name="adminForm" id="adminForm">
 	<table width="100%">
	   	<tr>
	  		<td width="50%" valign="top">
				<fieldset>
				<!-- ////////// GENERAL SETTINGS ////////// -->
	  				<legend><?php echo JText::_('SOBIPRO_GENERAL'); ?></legend>
					<table style="text-align: left;" class="paramlist admintable">
						<tr>
	      					<td class="paramlist_key" width="40%">
	      						<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_CHECK_SESSION_LBL' ); ?>::<?php echo JText::_('SOBIPRO_CHECK_SESSION_EXPLAIN'); ?>">
									<?php echo JText::_( 'SOBIPRO_CHECK_SESSION_LBL' ); ?>
								</span>
	           				</td>
							<td>	
	           					<?php echo JHTML :: _('select.booleanlist','sobipro_CHECK_SESSION','',$this->cfg['sobipro_CHECK_SESSION']); ?>
	           				</td>
	       				</tr>
	       				<tr>
							<td class="paramlist_key" width="40%">
								<b style="color:#0B55C4">Section</b>
							</td>
							<tr>
	       					<tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_SECTION_LISTING' ); ?>::<?php echo JText::_('SOBIPRO_SECTION_LISTING_EXPLAIN'); ?>">
										<?php echo JText::_( 'SOBIPRO_SECTION_LISTING' ); ?>
									</span>	
			   					</td>
						   		<td style="width: 100px">SobiPro Sections<br/>
						   			<select name="section_list1" id="section_list1" class = "inputbox" multiple="multiple" style="width: 180px;height:120px">
										<?php $this->class_obj->sections(); ?>
						   			</select>
						   		</td>
						   		<td style="width: 20px">
						   			<p>
						   				<input type = "button" value = ">>" onclick = "moveitem(section_list1,section_list2);">
						   			</p>
									<p>
						   				<input type = "button" value = "<<" onclick= "remove1(section_list2);" >
						   			</p>
						   		</td>
						   		<td style="width: 100px">Sections To Show<br/>	   				
						   			<select name="sobipro_Section_List[]" id="section_list2" class = "inputbox" multiple="multiple" style="width: 180px;height:120px">
						   				<?php $this->class_obj->show_sections(); ?>
									</select>
						   		</td>
						   		<td valign="bottom">
						   		<p>
						   			<b style="color:#0B55C4">(Note : Please hit apply button to see the appropriate sections and category listing in latitude/longitude settings & Featured listing)</b>
						   		</p>
						   		</td>
							</tr>
						<tr>
	       						<td>
	       							<b style="color:#0B55C4">Display Settings</b>
	       						</td>
	       					<tr>
	       					<tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_SECTION_DISPLAY' ); ?>::<?php echo JText::_('SOBIPRO_SECTION_DISPLAY_EXPLAIN'); ?>">
										<?php echo JText::_( 'SOBIPRO_SECTION_DISPLAY' ); ?>
									</span>	
								</td>
								<?php 
									if(empty($this->cfg["sobipro_Section"])){
										$sel="selected='selected'";
									}else if($this->cfg["sobipro_Section"] == "thumb"){
										$sel="selected='selected'";
									}else{
										$sel="";
									} 
								 ?>
								<td>
									<select name="sobipro_Section" id="sobipro_Section" class = "inputbox">
										<option value="thumb" <?php echo $sel;?>>Thumbnails</option>
										<option value="list" <?php if($this->cfg["sobipro_Section"]=="list") { echo "selected='selected'";} ?>>List</option>
									</select>
								</td>	
							</tr>
	       					<tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_CATEGORY_DISPLAY' ); ?>::<?php echo JText::_('SOBIPRO_CATEGORY_DISPLAY_EXPLAIN'); ?>">
										<?php echo JText::_( 'SOBIPRO_CATEGORY_DISPLAY' ); ?>
									</span>	
								</td>
								<?php 
									if(empty($this->cfg["sobipro_Category"])){
										$sel="selected='selected'";
									}else if($this->cfg["sobipro_Category"] == "thumb"){
										$sel="selected='selected'";
									}else{
										$sel="";
									} 
								 ?>
								<td>
									<select name="sobipro_Category" id="sobipro_Category" class = "inputbox">
										<option value="thumb" <?php echo $sel;?>>Thumbnails</option>
										<option value="list" <?php if($this->cfg["sobipro_Category"]=="list") { echo "selected='selected'";} ?>>List</option>
									</select>
								</td>	
							</tr>
							<tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_ENTRY_DISPLAY' ); ?>::<?php echo JText::_('SOBIPRO_ENTRY_DISPLAY_EXPLAIN'); ?>">
										<?php echo JText::_( 'SOBIPRO_ENTRY_DISPLAY' ); ?>
									</span>	
								</td>
								<?php if(empty($this->cfg["sobipro_List"])) {$sel="selected='selected'";}
								else if($this->cfg["sobipro_List"] == "thumb") {$sel="selected='selected'";}
								else {$sel="";}?> 
								<td>
									<select name="sobipro_List" id ="sobipro_List" class="inputbox">
											<option value="thumb" <?php echo $sel;?>>Thumbnails</option>
											<option value="list" <?php if($this->cfg["sobipro_List"]=="list") { echo "selected='selected'";} ?>>List</option>
									</select>
								</td>
							</tr>
							<tr>
	       						<td>
	       							<b style="color:#0B55C4">Google Map</b>
	       						</td>
	       					<tr>
	       					<tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_MAP_API_KEY' ); ?>::<?php echo JText::_('SOBIPRO_MAP_API_KEY_EXPLAIN'); ?>">
									<?php echo JText::_( 'SOBIPRO_MAP_API_KEY' ); ?>
									</span>	
								</td>
								<td>
						    		<input type="text" name="sobipro_MAP_API_KEY" id="sobipro_MAP_API_KEY" value="<?php echo $this->cfg["sobipro_MAP_API_KEY"];?>" />
						    	</td>
							</tr>
							<tr>
	       						<td>
	       							<b style="color:#0B55C4">Radius & Advance&nbsp;Search</b>
	       						</td>
	       					<tr>
							<tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_RADIUS_SEARCH' ); ?>::<?php echo JText::_('SOBIPRO_RADIUS_SEARCH_EXPLAIN'); ?>">
									<?php echo JText::_( 'SOBIPRO_RADIUS_SEARCH' ); ?>
									</span>	
								</td>
								<td>
									<?php echo JHTML :: _('select.booleanlist','sobipro_Radius_Search','',$this->cfg["sobipro_Radius_Search"]); ?>
								</td>
							</tr>
							<tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_RADIUS_SEARCH_UNIT' ); ?>::<?php echo JText::_('SOBIPRO_RADIUS_SEARCH_UNIT_EXPLAIN'); ?>">
									<?php echo JText::_( 'SOBIPRO_RADIUS_SEARCH_UNIT' ); ?>
									</span>	
								</td>
								<td>
									<select name="sobipro_Radius_Search_Unit" id="sobipro_Radius_Search_Unit">
										<option value="km" <?php echo ($this->cfg["sobipro_Radius_Search_Unit"]=='km')?'selected="selected"':'';?>>KM</option>
										<option value="mile" <?php echo ($this->cfg["sobipro_Radius_Search_Unit"]=='mile')?'selected="selected"':'';?>>Mile</option>
									</select>
								</td>
							</tr>
							<!-- <tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php //echo JText::_( 'SOBIPRO_MAX_RADIUS_SEARCH' ); ?>::<?php //echo JText::_('SOBIPRO_MAX_RADIUS_SEARCH_EXPLAIN'); ?>">
									<?php //echo JText::_( 'SOBIPRO_MAX_RADIUS_SEARCH' ); ?>
									</span>	
								</td>
								<td>
									<input type="text" name="sobipro_Max_Radius_Search" id="sobipro_Max_Radius_Search" value="<?php //echo $this->cfg["sobipro_Max_Radius_Search"];?>" />
								</td>
							</tr> -->
							<tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_MAX_SEARCH' ); ?>::<?php echo JText::_('SOBIPRO_MAX_SEARCH_EXPLAIN'); ?>">
									<?php echo JText::_( 'SOBIPRO_MAX_SEARCH' ); ?>
									</span>	
								</td>
								<td>
									<input type="text" name="sobipro_Max_Search" id="sobipro_Max_Search" value="<?php echo $this->cfg["sobipro_Max_Search"];?>" />
								</td>
							</tr>
						</table>
					</fieldset>
					
					
					<fieldset>
	    				<legend><?php echo JText::_('SOBIPRO_FIELDS_SETTING'); ?></legend>
						<table style="text-align: left;width:90%;" class="paramlist admintable">
							<tr>
						   		<td style="width: 100px" colspan=4>
						   			<table class="adminlist" cellspacing="1">
						   				<thead>
							   				<tr>
							   					<th>
							   						<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_SECTION_LBL' ); ?>::<?php echo JText::_('SOBIPRO_SECTION_LBL_EXPLAIN'); ?>">
														<?php echo JText::_( 'SOBIPRO_SECTION_LBL' ); ?>
													</span>	
												</th>
							   					<th width="150px">
							   						<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_FIELD_NAME' ); ?>::<?php echo JText::_('SOBIPRO_FIELD_NAME_EXPLAIN'); ?>">
														<?php echo JText::_( 'SOBIPRO_FIELD_NAME' ); ?>
													</span>
												</th>
							   					<th width="250px">
							   						<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_FIELDS' ); ?>::<?php echo JText::_('SOBIPRO_FIELDS_EXPLAIN'); ?>">
														<?php echo JText::_( 'SOBIPRO_FIELDS' ); ?>
													</span>
							   					</th>
							   				</tr>
						   				</thead>
						   				<tbody>
						   				<?php $this->class_obj->get_section_lat();?>
						   				</tbody>
						   			</table>
						   		</td>
							</tr>
						</table>
					</fieldset>
					
					
					<fieldset>
	    				<legend><?php echo JText::_('SOBIPRO_FEATURED_PRODUCT'); ?></legend>
						<table style="text-align: left;" class="paramlist admintable">
							<tr>
								<td class="paramlist_key" width="40%">
						 			<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_FEATURED_LBL' ); ?>::<?php echo JText::_('SOBIPRO_FEATURED_EXPLAIN'); ?>">
										<?php echo JText::_( 'SOBIPRO_FEATURED_LBL' ); ?>
									</span>
			    				</td>
								<td>
					    			<?php echo JHTML :: _('select.booleanlist','sobipro_FEATURED','',$this->cfg["sobipro_FEATURED"]); ?>
					    		</td>
							</tr>
							<tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_LISTING_LBL' ); ?>::<?php echo JText::_('SOBIPRO_LISTING_EXPLAIN'); ?>">
										<?php echo JText::_( 'SOBIPRO_LISTING_LBL' ); ?>
									</span>	
			   					</td>
						   		<td style="width: 100px">SobiPro Entries<br/>
						   			<select name="product_list1" id="product_list1" class = "inputbox" multiple="multiple" style="width: 180px;height:220px">
										<?php $this->class_obj->category_item_tree(); ?>
						   			</select>
						   		</td>
						   		<td style="width: 20px">
						   			<p>
						   				<input type = "button" value = ">>" onclick = "moveitem(product_list1,product_list2);">
						   			</p>
									<p>
						   				<input type = "button" value = "<<" onclick= "remove1(product_list2);" >
						   			</p>	
						   		</td>
						   		<td style="width: 100px">Featured For iJoomer<br/>	   				
						   			<select name="sobipro_Featured_List[]" id="product_list2" class = "inputbox" multiple="multiple" style="width: 180px;height:220px">
						   				<?php $this->class_obj->show_list(); ?>
									</select>
						   		</td>
							</tr>
							<tr>
								<td class="paramlist_key" width="40%">
									<span class="hasTip" title="<?php echo JText::_( 'SOBIPRO_FEATURED_DISPLAY' ); ?>::<?php echo JText::_('SOBIPRO_FEATURED_DISPLAY_EXPLAIN'); ?>">
									<?php echo JText::_( 'SOBIPRO_FEATURED_DISPLAY' ); ?>
									</span>	
								</td>
								<td>
									<select name="sobipro_Featured_View" id ="sobipro_Featured_View" class="inputbox">
										<option value="global" <?php if($this->cfg["sobipro_Featured_View"]=="global") { echo "selected='selected'";} ?>>Global</option>
										<option value="thumb" <?php if($this->cfg["sobipro_Featured_View"]=="thumb") { echo "selected='selected'";} ?>>Thumbnails</option>
										<option value="list" <?php if($this->cfg["sobipro_Featured_View"]=="list") { echo "selected='selected'";} ?>>List</option>
									</select>
								</td>
							</tr> 			
						</table>
					</fieldset>
				</td>
		</tr>
	</table>
<div class="clr"></div>
<div style="text-align:center"><?php echo JText::_('VERSION')." ".$this->version?></div>
<input type="hidden" name="plugin_id" value="<?php echo $this->detail->plugin_id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="view" value="plugin_detail" />
</form>