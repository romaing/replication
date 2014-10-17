<?php
/**
 * @version 3.0
 * @subpackage Components
 * @package replication
 * @copyright Copyright (C) 2007 - 2013 romain gires. All rights reserved.
 * @author		romain gires
 * @link		http://composant.gires.net/
 * @license		License GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::_('behavior.tooltip');

$lang =& JFactory::getLanguage();
$langTag = $lang->getTag();
?>

	<script type="text/javascript">
		Joomla.submitbutton = function(task){
			if(task=='affiche-iframe-rsync'){
				task ="rsync";
				displayDoc(task);
			}else {
				// offline ou online
				Joomla.submitform(task, document.getElementById('replication-form'));
			}
		}
		var openHelp = true;
		
		function displayDoc(task){
			var box=$('iframedoc');
			if(openHelp){
				url = 'components/com_replication/help/<?php echo $langTag; ?>/'+task+'.html';
				box.innerHTML = '<div id="element-box" style="height:260px;" ><div class="m" style="height:100%;"><iframe width="100%" scrolling="auto" height="100%" src="'+url+'"></iframe><div class="clr"></div></div>';
				box.style.display = 'block';
				box.style.height = '0';
			};
			try{
				var fx = box.effects({duration: 1500, transition:
				Fx.Transitions.Quart.easeOut});
				if(openHelp){
					fx.start({'height': 300});
				}else{
					fx.start({'height': 0}).chain(function() {
						box.innerHTML='';
						box.setStyle('display','none');
					});
				}
			}catch(err){
				box.style.height = '300px';
				var myVerticalSlide = new Fx.Slide('iframedoc');
				if(openHelp){
					myVerticalSlide.hide().slideIn();
				}else{
					myVerticalSlide.slideOut().chain(function() {
						box.innerHTML='';
						box.setStyle('display','none');
					});
				}
			};
			openHelp = !openHelp;
		}
	</script>
	   
	<div id="iframedoc"></div>

<form class="form-validate" id="replication-form" name="adminForm" method="post"
action="<?php echo JRoute::_('index.php?option=com_replication&view=replication_site'); ?>">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<?php if( !empty($this->msg)  ):?>
				<fieldset class="adminform">
					<legend><?php echo JText::_('COM_REPLICATION_SITE_TITRE_RESULTAT_REPLICATION_SITE');?></legend>
						<div style="width:575px; height:300px; overflow: auto; border:0px #000 solid;">
							<?php echo $this->msg; ?>
						</div>
				</fieldset>
			<?php else:?>
				<?php echo $this->une; ?>
			<?php endif;?>
		
			<?php if( !empty($this->fichierrsync) ):?>
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_REPLICATION_SITE_TITRE_ARCHIVE_SITE');?></legend>
					<div style="width:100%; height:200px; overflow: auto; border:0px #000 solid;">
						<?php echo $this->fichierrsync; ?>
					</div>
			</fieldset>
				<?php endif;?>

			<div class="clr"></div>
			<div>
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>