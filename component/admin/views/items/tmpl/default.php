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

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$app		= JFactory::getApplication();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$ordering 	= ($listOrder == 'a.ordering');
$canOrder	= $user->authorise('core.edit.state',	'com_ijoomeradv');
$saveOrder 	= ($listOrder == 'a.ordering' && $listDirn == 'asc');
?>
<?php //Set up the filter bar. ?>
<form action="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=items');?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_IJOOMERADV_ITEMS_SEARCH_FILTER'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">

			<select name="menutype" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $this->menuOptions, 'value', 'text', $this->state->get('filter.menutype'));?>
			</select>

            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('archived' => false)), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
	</fieldset>
	<div class="clr"> </div>
<?php //Set up the grid heading. ?>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th width="13%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
					<?php if(count($this->items)>1){echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'saveorder'); }?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
				</th>
				<?php
				$assoc = isset($app->menu_associations) ? $app->menu_associations : 0;
				if ($assoc):
				?>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_IJOOMERADV_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
				</th>
				<?php endif;?>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<?php // Grid layout ?>
		<tbody>
		<?php
		$originalOrders = array();
		$inc=1;
		foreach ($this->items as $i => $item) :
			$orderkey = $item->ordering;//array_search($item->id, $this->ordering[$item->id]);
			$canCreate	= $user->authorise('core.create',		'com_ijoomeradv');
			$canEdit	= $user->authorise('core.edit',			'com_ijoomeradv');
			$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out==$user->get('id')|| $item->checked_out==0;
			$canChange	= $user->authorise('core.edit.state',	'com_ijoomeradv') && $canCheckin;
			$disabled	= 'disabled="disable"';
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=item&task=edit&id='.(int) $item->id);?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('grid.published', $item->published, $i); ?>
				</td>
				<!--<td class="order">
					<span><?php echo $this->pagination->orderUpIcon($i, $i!=0, 'orderup', 'JLIB_HTML_MOVE_UP', 1); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, $i<$this->pagination->total, 'orderdown', 'JLIB_HTML_MOVE_DOWN', 1); ?></span>
					<input type="text" name="order[]" size="5" value="<?php echo $inc;?>" <?php echo $disabled ?> class="text-area-order" />
					<?php $originalOrders[] = $orderkey; ?>
				</td>
				-->
				<td class="order" nowrap="nowrap">
	            	<span><?php echo $this->pagination->orderUpIcon( $i, $i!=0,'orderup', 'Move Up', $item->ordering); ?></span>
					<span><?php echo $this->pagination->orderDownIcon( $i, $this->pagination->total, $i<$this->pagination->total, 'orderdown', 'Move Down', $item->ordering ); ?></span>
					<?php $disabled = $item->ordering ?  '' : 'disabled="disabled"'; ?>
					<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
	            </td>
				<td class="center">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<?php
				$assoc = isset($app->menu_associations) ? $app->menu_associations : 0;
				if ($assoc):
				?>
				<td class="center">
					<?php if ($item->association):?>
						<?php echo JHtml::_('MenusHtml.Menus.association', $item->id);?>
					<?php endif;?>
				</td>
				<?php endif;?>
				
				<td class="center">
					<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt);?>">
						<?php echo (int) $item->id; ?></span>
				</td>
			</tr>
			<?php 
			$inc++;
			endforeach; ?>
		</tbody>
	</table>
	<?php //Load the batch processing form.is user is allowed ?>
	<?php if($user->authorize('core.create', 'com_ijoomeradv') || $user->authorize('core.edit', 'com_ijoomeradv')) : ?>
		<?php //echo $this->loadTemplate('batch'); ?>
	<?php endif;?>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
