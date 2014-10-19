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

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');
$iFrame =  '<div id="element-box" style="height:260px;" ><div class="m" style="height:100%;"><iframe width="100%" scrolling="auto" height="100%" src="components/com_replication/help/fr-FR/configuration.html"></iframe><div class="clr"></div></div>';
?>
	<script type="text/javascript">
		Joomla.submitbutton = function(task){
			if(task=='affiche-iframe-config'){
				displayDoc();
			/*}else {
				// site ou bdd
				Joomla.submitform(task, document.getElementById('replication-form'));
			*/
			}
		}
		var openHelp = true;

		function displayDoc(){
			var box=$('iframedoc');
			if(openHelp){
				box.innerHTML = '<?php echo $iFrame; ?>';
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
<!--
<form class="form-validate" id="replication-form" name="adminForm" method="post"
action="<?php echo JRoute::_('index.php?option=com_replication'); ?>">
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
</form>
-->
	<div id="iframedoc"></div>

	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<div class="panel">
				<h3 id="info-panel" class="title pane-toggler-down">
					<a href="javascript:void(0);">
						<span><?php echo JText::_('COM_REPLICATION_PANEL_TITRE_COMPOSANT_REPLICATION'); ?></span>
					</a>
				</h3>
				<div class="pane-slider content pane-down" style="padding-top: 0px; border-top: medium none; padding-bottom: 0px; border-bottom: medium none; overflow: hidden; height: auto;">
					<table class="adminlist">
					   <tbody>
					   <tr>
							<th>
							</th><td>
								<a target="_blank" href="http://composant.gires.net">
									<img align="middle" style="border: none; margin: 8px;" alt="Replication logo" src="<?php echo JURI::root(true); ?>/administrator/components/com_replication/assets/images/replication-120x120.png" width="120" height="120" />
								</a>
							</td>
						</tr>
					   <tr>
						  <th width="120">
						  </th><td><?php echo JText::_('COM_REPLICATION_SITE_LIEN'); ?></td>
					   </tr>
					   <tr>
						  <th><?php echo JText::_('COM_REPLICATION_SITE_VERSION'); ?></th>
						  <td><?php echo JText::_('COM_REPLICATION_SITE_NUMVERSION'); ?></td>
					   </tr>
					   <!-- tr>
						  <th>Newest version:
						  </th><td>4.2.4 <input type="button" value="Disable version checker" onclick="disableStatus('versioncheck');"></td>
					   </tr -->


					   <tr>
						  <th><?php echo JText::_('COM_REPLICATION_SITE_DATE'); ?></th>
						  <td><?php echo JText::_('COM_REPLICATION_SITE_JOURDATE'); ?></td>
					   </tr>
					   <tr>
						  <th valign="top"><?php echo JText::_('COM_REPLICATION_SITE_COPYRIGHT'); ?></th>
						  <td>&copy; <?php echo JText::_('COM_REPLICATION_SITE_COPYRIGHTC'); ?></td>
					   </tr>

					   <tr>
						  <th><?php echo JText::_('COM_REPLICATION_SITE_AUTHOR'); ?>
						  </th><td><?php echo JText::_('COM_REPLICATION_SITE_LIEN'); ?>,
						  <?php echo JText::_('COM_REPLICATION_SITE_EMAIL'); ?></td>
					   </tr>
					   <tr>
						  <th><?php echo JText::_('COM_REPLICATION_SITE_LICENSE'); ?></th>
						  <td><a target="_blank" href="/administrator/components/com_replication/licence.html"><?php echo JText::_('COM_REPLICATION_SITE_COMBINED_LICENSE'); ?></a></td>
					   </tr>
					   <tr>
						  <th valign="top"><?php echo JText::_('COM_REPLICATION_SITE_PROMODESCRIPTION'); ?>
						  </th><td>	<?php echo JText::_('COM_REPLICATION_SITE_PROMO'); ?>
						  </td>
					   </tr>
					   <tr>
						  <th valign="top"><?php echo JText::_('COM_REPLICATION_SITE_UTILISATION'); ?></th>
						  <td><?php echo $this->nbrepli; ?> <?php echo JText::_('COM_REPLICATION_SITE_NBREPLI'); ?></td>
					   </tr>
					   <tr>
						  <th><?php echo JText::_('COM_REPLICATION_SITE_SUPPORT'); ?>
						  </th><td>


						  <form method="post" action="https://www.paypal.com/cgi-bin/webscr">
						  <input type="hidden" value="_s-xclick" name="cmd">
						  <input type="hidden" value="8159768" name="hosted_button_id">
						  <input type="image" border="0" alt="<?php echo JText::_('COM_REPLICATION_SITE_PAYPALIMG'); ?>" name="submit" mce_src="https://www.paypal.com/fr_FR/FR/i/btn/btn_donateCC_LG.gif" src="https://www.paypal.com/fr_FR/FR/i/btn/btn_donateCC_LG.gif">
						  <img width="1" height="1" border="0" mce_src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" alt="" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif">
						  </form>


						  </td>
						</tr>
						</tbody>
					</table>

				</div>
			</div>
		</div>
	</div>

	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<div id="element-box">
				<div class="m">
					<?php echo JText::_('COM_REPLICATION_SITE_BIENVENU'); ?>
					<div class="clr"></div>
				</div>
			</div>
			<div id="element-box">
				<div class="m">
					<strong><?php echo JText::_('COM_REPLICATION_BDD_ALERT'); ?></strong>
					<div class="clr"></div>
				</div>
			</div>

			<div id="element-box">
				<div class="m">
					<?php echo JText::_('COM_REPLICATION_SITE_COMMENT_REPLIQUER'); ?>
					<div class="clr"></div>
				</div>
			</div>
		</div>
	</div>

