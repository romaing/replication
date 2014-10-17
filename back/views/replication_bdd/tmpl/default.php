<?php
/**
 * @version 2.5
 * @subpackage Components
 * @package replication
 * @copyright Copyright (C) 2007 - 2012 romain gires. All rights reserved.
 * @author		romain gires
 * @link		http://composant.gires.net/
 * @license		License GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::_('behavior.tooltip');

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task){
		Joomla.submitform(task, document.getElementById('replication-form'));
	}
</script>


<form class="form-validate" id="replication-form" name="adminForm" method="post"
action="<?php echo JRoute::_('index.php?option=com_replication&view=replication_bdd'); ?>">
	<div class="width-60 fltlft">

		<?php if( !empty($this->msg)  ):?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_REPLICATION_BDD_TITRE_RESULTAT_REPLICATION_BDD');?></legend>
				<div style="width:100%; height:200px; overflow: auto; border:0px #000 solid;">
					<?php echo $this->msg; ?>
				</div>
		</fieldset>
			<?php else:?>
				<?php echo $this->une; ?>
			<?php endif;?>
		
		<?php if( !empty($this->archivedumps) ):?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_REPLICATION_BDD_TITRE_ARCHIVE_BDD');?></legend>
				<div style="width:100%; height:200px; overflow: auto; border:0px #000 solid;">
					<?php echo $this->archivedumps; ?>
				</div>
		</fieldset>
			<?php endif;?>

		<div class="clr"></div>
		<div>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>