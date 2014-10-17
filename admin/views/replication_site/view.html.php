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

jimport('joomla.application.component.view');

/**
 * Statistics view class for Finder.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
//class replicationViewreplication_site extends JView
class replicationViewreplication_site extends JViewLegacy
{

	protected $path_backups;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		
		$this->task = JRequest::getVar('task');
		$this->une = "";
		$this->msg = "";
			
		//config param composant
		$config = &JComponentHelper::getParams( 'com_replication' );

		$this->path_source = $config->get( 'url_source', '');
		$this->path_dest = $config->get( 'url_destination', '');
		$this->path_backups = realpath($config->get( 'pathbackups', './backups/')).DS;
		$this->path_backups = str_replace(DS, "/", $this->path_backups);

		$namelog = $config->get( 'namelog', 'replication.log');  
			
		if ($this->task=="appliquer"){			
			### MISE A JOUR SITE
			$msg="";
			$this->replication_site($msg );
			$this->msg = $msg;

		}elseif ($this->task=="offline"){
			ReplicationHelper::mettre_offline('',1);
		}elseif ($this->task=="online"){
			ReplicationHelper::mettre_offline('',0);
			
		}elseif ($this->task=="clearlog"){
			$clearlog=ReplicationHelper::clearlog();
			if($clearlog){
				JFactory::getApplication()->enqueueMessage(
					JText::_('COM_REPLICATION_CLEARLOG_ALERT')
					, 'notice'
				);

			}
		}else{
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_REPLICATION_BDD_ALERT')
				, 'warning'
			);
			//JError::raiseError(500, implode("\n", $errors));
			$this->une = sprintf(JText::_('COM_REPLICATION_SITE_TITRE_UNE'),
								 $this->path_source,
								 $this->path_dest,
								 $this->path_backups
								);
			
		}
		
		$this->fichierrsync = ReplicationHelper::recherche_fichierrsync();
		
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		// Set the toolbar
		$this->addToolBar();

		parent::display($tpl);
	}
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		$canDo = ReplicationHelper::getActions();
		
		JToolbarHelper::title(JText::_('COM_REPLICATION_MANAGER_REPLICATIONS'), 'replication');

		if ($canDo->get('replication.apply_site')) 
		{
			JToolbarHelper::publish('replication.apply_site','Lancer la réplication');
	
				// Overwrite the old FTP credentials with the new ones.
			$temp = JFactory::getConfig();
			if($temp->get('offline')){
				JToolbarHelper::custom('replication.online', 'unpublish', '', 'Site Offline' , false, false );
			}else{
				JToolbarHelper::custom('replication.offline', 'unblock', '', 'Site Online' , false, false );
			}
		}
		
		JToolbarHelper::custom('replication.clearlog', 'trash', '', 'clear log' , false, false );
		/*
		 if ($canDo->get('core.create')) 
		{
			JToolbarHelper::addNew('replication.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) 
		{
			JToolbarHelper::editList('replication.edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.delete')) 
		{
			JToolbarHelper::deleteList('', 'replication.delete', 'JTOOLBAR_DELETE');
		}*/
		if ($canDo->get('core.admin')) 
		{
			JToolbarHelper::divider();
			JToolbarHelper::preferences('com_replication');
		}

		//JToolbarHelper::help(JText::_('JHELP_REPLICATION_MANAGER_REPLICATIONS') ,true);
		JToolbarHelper::custom('affiche-iframe-rsync',  'help', ' ', 'Aide rsync',false,false);


	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_REPLICATION_ADMINISTRATION'));
	}
	
	 	
	private function replication_site(&$msg = "") {
	
		### pour enregistrer les preferences
		jimport('joomla.filesystem.file');
		
		$cheminURL 				= JURI::root().'administrator'.DS.'components'.DS.'com_replication'.DS;
		$assets 				= JURI::root().'administrator'.DS.'components'.DS.'com_replication'.DS.'assets'.DS;
    	//$modelsspath 			= JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS;
		$Component_admin_path 	= JPATH_COMPONENT_ADMINISTRATOR.DS;
		
		$msg ="";

		### param settings
		jimport( 'joomla.application.component.helper' );
		//$config	= JComponentHelper::getParams( 'com_replication' );
		
		$config = &JComponentHelper::getParams( 'com_replication' );

		### param site			
			
		$url_source 		= $config->get('url_source', '').DS;
		$url_destination 	= $config->get('url_destination', '');
		$rs_logfile 		= realpath($config->get('rs_logfile', './backups/rsync-log.txt'));
		$exclusion 			= $config->get('exclusion', '');

		
		//effacement du log a chaque replication
		if ($config->get('rs_newlog', '0')){
			$clearlog=ReplicationHelper::clearlog();
			if($clearlog){
				JFactory::getApplication()->enqueueMessage(
					JText::_('COM_REPLICATION_CLEARLOG_ALERT')
					, 'message'
				);

			}
		}
		$rs_exclusion 		= $Component_admin_path.$config->get('rs_exclusion', '');
		//$rs_inclusion 		= $Component_admin_path.$config->get('rs_inclusion', '');
		$rs_option  		= $config->get('rs_option', '');
		$rs_userssh 		= $config->get('rs_userssh', 'login@serveur.principal.fr');

		
		//windows
		
		ReplicationHelper::cygdrive_path($rs_exclusion);
		ReplicationHelper::cygdrive_path($url_destination);
		ReplicationHelper::cygdrive_path($url_source);
		ReplicationHelper::cygdrive_path($rs_logfile);
		/**/
		
		### Autre option
		$options = "";
		if ($config->get('rs_progress', '0')){
			$options .= "--progress ";	
		}
		if ($config->get('rs_stats', '0')){
			$options .= "--stats ";	
		}
		if ($config->get('rs_log', '0')){
			$options .= "--log-file=".$rs_logfile;	
		}
		### SSH
		$ssh_dest = "";
		if ($config->get('rs_ssh', '0')){
			$ssh_dest .= "-e ssh $rs_userssh:";	
		}


		### Autre config
		$mode               = 3;
		$sens               = 1; // 1=msg erreur,  0=msg base de destination est vide;
		/*
		 $nomfichier_sour	= "source_".$base_destination."_";
		$nomfichier_recup	= "recup_".$base_destination."_";
		$nomfichier_dest	= "desti_".$base_destination."_";
		*/
			
		$testpref = $config->get( 'url_destination' );
		
		if( empty( $testpref )){
			### METTRE A JOUR LES PREFS
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_REPLICATION_METTRE_A_JOUR_LES_PREFS')
				, 'warning'
			);
			return false;
		
		}else{
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_REPLICATION_SITE_APPLIQUER')
				, 'warning'
			);
			
			### creer dossier backup
			ReplicationHelper::creer_dossier_backups();
			
			### creer dossier backup en local
			if ($config->get('rs_ssh', '0')==0){
				ReplicationHelper::creer_dossier_destination();
			}						
			### Creation du fichier d'exclusion
			$write_exclude = ReplicationHelper::write_exclude();
			//$write_include = ReplicationHelper::write_include();
			


			/**/
			### MISE A JOUR SITE
			$command  = "rsync " ;
			$command .= "$rs_option " ;
			$command .= "$options " ;
			//$command .= "--exclude-from $rs_exclusion " ;
			$command .= "$url_source " ;
			$command .= "$ssh_dest$url_destination" ;
			
			
			//$command  = 'rsync -az --verbose  --delete   "/cygdrive/D/romain/2012/wwwroot/test" "/cygdrive/C/test2"  ' ; 
			//$command  = 'rsync --help ' ; 
			ob_start();
				//Protège les caractères spéciaux du Shell
				$escaped_command = escapeshellcmd ($command );
				
				// la cmd system return direct les message dans la sortie standard
				$last_line = @system( $escaped_command, $retval );
				$out2 = ob_get_contents();
			ob_end_clean();
			
			if ($retval === 0 ){
				$msg .= JText::_( 'COM_REPLICATION_SITE_FICHIER_REUSSI' );
				$msg .= JText::_( 'COM_REPLICATION_SITE_FICHIER_REPLIQUE' );
				$msg .= '<pre>';
					//$msg .= "$command <br>";
					//$msg .= sprintf (JText::_( 'COM_REPLICATION_SITE_FICHIER_RETVAL' ), $retval );
					$msg .= "$out2 <br>";
				$msg .= "</pre>";
			}else{

				$msg .= JText::_( 'COM_REPLICATION_SITE_FICHIER_FAILED' );
				$msg .= JText::_( 'COM_REPLICATION_SITE_FICHIER_ATTENTION_ERREUR' );
				$msg .= "";
				$msg .= "";
				$msg .= '<pre>';
					$msg .= "$command <br>";
					$msg .= sprintf (JText::_( 'COM_REPLICATION_SITE_FICHIER_ERREUR' ), $retval );
					$msg .= "$out2 <br>";
					if(!empty($last_line)){
						$msg .= sprintf (JText::_( 'COM_REPLICATION_SITE_FICHIER_LAST_LINE' ), $last_line );
					}
				$msg .= "</pre>";
				$msg .= "<br>";
				$msg .= JText::_( 'COM_REPLICATION_RSYNC_AIDE_WINDOWS' );
				$msg .= "<br>";
				$msg .= "<br>";
			}
			
			### Path config sur site de destination
			$url_destination 	= $config->get('url_destination', '');
			
			### Creation du fichier de config sur site de destination
			if (!is_file($url_destination.DS.'configuration.php')){
				$write_onfig_dest = ReplicationHelper::write_config_dest();
			}
			
			### s'il n'existe toujours pas, alert
			if (!is_file($url_destination.DS.'configuration.php')){
				//JError::raiseNotice( '', JText::_( 'COM_REPLICATION_PAS_DE_FICHIER_CONFIGURATION' ));
				JFactory::getApplication()->enqueueMessage(
					JText::_('COM_REPLICATION_PAS_DE_FICHIER_CONFIGURATION')
					, 'message'
				);
				//$msg .= JText::_('COM_REPLICATION_CREER_FICHIER_CONFIGURATION');
				return false ;
			}
			
			return $msg ;
		}
	}
}
