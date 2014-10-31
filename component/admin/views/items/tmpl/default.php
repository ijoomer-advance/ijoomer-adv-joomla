<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.views
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user      = JFactory::getUser();
$app       = JFactory::getApplication();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$canOrder  = $user->authorise('core.edit.state', 'com_ijoomeradv');
$saveOrder = ($listOrder == 'a.ordering' && $listDirn == 'asc');
?>

<!--  Set up the filter bar. -->
<form action="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=items'); ?>" method="post" name="adminForm"
      id="adminForm">
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<input type="text" name="filter_search" id="filter_search"
			       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
			       title="<?php echo JText::_('COM_IJOOMERADV_ITEMS_SEARCH_FILTER'); ?>"/>
		</div>
		<div class="btn-group pull-left hidden-phone">
			<button type="submit" class="btn tip hasTooltip" data-original-title="Search"><i class="icon-search"></i>
			</button>
			<button type="button" class="btn tip hasTooltip" data-original-title="Clear"
			        onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i>
			</button>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<select name="menutype" class="input-medium chzn-done" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $this->menuOptions, 'value', 'text', $this->state->get('filter.menutype')); ?>
			</select>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<select name="filter_published" class="input-medium chzn-done" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('archived' => false)), 'value', 'text', $this->state->get('filter.published'), true); ?>
			</select>
		</div>
	</div>
	<div class="clr"></div>

	<!-- Set up the grid heading. -->
	<table class="adminlist table table-striped">
		<thead>
		<tr>
			<th class="nowrap left" width="20px">
				<input type="checkbox" name="checkall-toggle" value=""
				       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
			</th>
			<th class="nowrap left">
				<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
			</th>
			<th class="nowrap left">
				<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
			</th>
			<th class="center" width="20px">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.id', $listDirn, $listOrder); ?>

				<?php
				if (count($this->items) > 1)
				{
					echo JHtml::_('grid.order', $this->items, 'filesave.png', 'saveorder');
				}
				?>
			</th>
			<th class="nowrap left" width="50px">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
			</th>
			<th class="nowrap right" width="20px">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
		<!-- Grid layout -->
		<tbody>
		<?php
		$originalOrders = array();
		$inc = 1;

		foreach ($this->items as $i => $item) :
			// Array_search($item->id, $this->ordering[$item->id]);
			$orderkey   = $item->ordering;
			$canCreate  = $user->authorise('core.create', 'com_ijoomeradv');
			$canEdit    = $user->authorise('core.edit', 'com_ijoomeradv');
			$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
			$canChange  = $user->authorise('core.edit.state', 'com_ijoomeradv') && $canCheckin;
			$disabled   = 'disabled="disable"';
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="nowrap left">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php
						if ($canEdit)
						{
					?>
						<a href="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=item&task=edit&id=' . (int) $item->id); ?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php
						}
						else
						{
					?>
						<?php echo $this->escape($item->title); ?>
					<?php
						}
					?>
				</td>
				<td class="center" width="20px">
					<?php echo JHtml::_('grid.published', $item->published, $i); ?>
				</td>
				<td class="nowrap order" style="text-align:right;">
					<span><?php echo $this->pagination->orderUpIcon($i, $i != 0, 'orderup', 'Move Up', $item->ordering); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, $i < $this->pagination->total, 'orderdown', 'Move Down', $item->ordering); ?></span>
					<?php $disabled = $item->ordering ? '' : 'disabled="disabled"'; ?>
					<input type="text" name="order[]" size="5"
					       value="<?php echo $item->ordering; ?>" <?php echo $disabled ?> class="text_area"
					       style="text-align: center"/>
				</td>
				<td class="center">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td class="center">
					<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
						<?php echo (int) $item->id; ?></span>
				</td>
			</tr>
			<?php
			$inc++;
		endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
