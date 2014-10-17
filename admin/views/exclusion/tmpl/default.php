<?php
/**
 * default template file for Replications view of Replication component
 * 
 * @version 3.0
 * @subpackage Components
 * @package replication
 * @copyright Copyright (C) 2007 - 2013 romain gires. All rights reserved.
 * @author		romain gires
 * @link		http://composant.gires.net/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

// load tooltip behavior
JHtml::_('behavior.tooltip');

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task){
		Joomla.submitform(task, document.getElementById('replication-form'));
	}
</script>

<form class="form-validate" id="replication-form" name="adminForm" method="post"
action="<?php echo JRoute::_('index.php?option=com_replication'); ?>">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<table class="adminlist">
				<thead>
					<tr>
						<th width="5">
							<?php echo JText::_('COM_REPLICATION_REPLICATION_HEADING_ID'); ?>
						</th>
						<!--
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
						</th>
						-->
						<th>
							<?php echo JText::_('COM_REPLICATION_REPLICATION_HEADING_EXCLUSIONS'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_REPLICATION_REPLICATION_HEADING_EXCLUSIONS_STATUS'); ?>
						</th>
					</tr>		
				</thead>
				<tfoot>
					<tr>
						<td colspan="3">
							<?php
							//echo $this->pagination->getListFooter(); 
							?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php
					$liststatus = ReplicationModelExclusion::getStatuslevel( $item );
					foreach($this->items as $i => $item):
				?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<?php echo $i; ?>
						</td>
						<!--
						<td>
							
							<?php echo JHtml::_('grid.id', $i, $item->id_table); ?>
						</td>
						-->
						<td>
							<?php echo $item->id_table; ?>
							<!--
							<a href="<?php echo JRoute::_('index.php?option=com_replication&task=replication.edit&id=' . $item->id_table); ?>">
								<?php echo $item->id_table; ?>
							</a>
							-->
						</td>
						<td>
							<?php
							echo $status = ReplicationModelExclusion::setStatuslevel( $item, $liststatus );
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<div>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
