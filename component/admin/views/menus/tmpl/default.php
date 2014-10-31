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

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$uri       = JFactory::getUri();
$return    = base64_encode($uri);
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$modMenuId = (int) $this->get('ModMenuId');
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task != 'menus.delete' || confirm('<?php echo JText::_('COM_IJOOMERADV_MENU_CONFIRM_DELETE', true);?>')) {
			Joomla.submitform(task);
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=menus'); ?>" method="post" name="adminForm"
      id="adminForm">
	<table class="adminlist table table-striped">
		<thead>
		<tr>
			<th width="1%" rowspan="2">
				<input type="checkbox" name="checkall-toggle" value=""
				       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
			</th>
			<th rowspan="2">
				<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
			</th>
			<th width="30%" colspan="3">
				<?php echo JText::_('COM_IJOOMERADV_HEADING_NUMBER_MENU_ITEMS'); ?>
			</th>

			<th width="1%" class="nowrap" rowspan="2">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
			</th>
		</tr>
		<tr>
			<th width="12%">
				<?php echo JText::_('COM_IJOOMERADV_HEADING_PUBLISHED_ITEMS'); ?>
			</th>
			<th width="12%">
				<?php echo JText::_('COM_IJOOMERADV_HEADING_UNPUBLISHED_ITEMS'); ?>
			</th>
			<th width="12%">
				<?php echo JText::_('COM_IJOOMERADV_HEADING_TRASHED_ITEMS'); ?>
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
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$canCreate = $user->authorise('core.create', 'com_ijoomeradv');
			$canEdit   = $user->authorise('core.edit', 'com_ijoomeradv');
			$canChange = $user->authorise('core.edit.state', 'com_ijoomeradv');

			if ($item->position == 1)
			{
				$item->menutype = JText::_('IJHOME');
			}
			elseif ($item->position == 2)
			{
				$item->menutype = JText::_('IJSLIDE');
			}
			else
			{
				$item->menutype = JText::_('IJBOTTOM');
			}
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=menu&task=edit&id=' . $item->id) ?> ">
						<?php echo $this->escape($item->title); ?></a>

					<p class="smallsub">(<span><?php echo JText::_('COM_IJOOMERADV_MENU_MENUTYPE_LABEL') ?></span>
						<?php echo $this->escape($item->menutype) ?>)
					</p>
				</td>
				<td class="center btns">
					<a href="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=items&menutype=' . $item->id . '&filter_published=1'); ?>">
						<?php echo $item->count_published; ?></a>
				</td>
				<td class="center btns">
					<a href="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=items&menutype=' . $item->id . '&filter_published=0'); ?>">
						<?php echo $item->count_unpublished; ?></a>
				</td>
				<td class="center btns">
					<a href="<?php echo JRoute::_('index.php?option=com_ijoomeradv&view=items&menutype=' . $item->id . '&filter_published=-2'); ?>">
						<?php echo $item->count_trashed; ?></a>
				</td>

				<td class="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
		<?php
endforeach;
		?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
