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
if(!defined('BACKUPDS')){ define('BACKUPDS','.'.DS.'backups'.DS);}

jimport('joomla.application.component.view');

/**
 * Statistics view class for Finder.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class replicationViewreplication_bdd extends JView
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

		$this->base_source = $config->get( 'base_source', '');
		$this->base_dest = $config->get( 'base_destination', '');
		$this->prefix_source = $config->get( 'prefix_source', '');
		$this->prefix_dest = $config->get( 'prefix_destination', '');
		$this->path_backups = realpath($config->get( 'pathbackups', 'xxx')).DS;

		$namelog = $config->get( 'namelog', 'replication.log');  
		
		if ($this->task=="appliquer"){
		
			### MISE A JOUR BASE
			$this->replication_bdd($msg );
			$this->msg = $msg;
			
			//config param composant
			$config = &JComponentHelper::getParams( 'com_replication' );
			$pathbackups = $config->get( 'pathbackups', BACKUPDS);  
			$namelog = $config->get( 'namelog', 'replication.log');  
	
			ReplicationHelper::write_replic($pathbackups, $namelog);

		}elseif ($this->task=="offline"){
			//$this->mettre_offline('',1);
			ReplicationHelper::mettre_offline('',1);
		}elseif ($this->task=="online"){
			//$this->mettre_offline('',0);
			ReplicationHelper::mettre_offline('',0);
		}else{
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_REPLICATION_BDD_ALERT')
				, 'warning'
			);
			//JError::raiseError(500, implode("\n", $errors));
			$this->une = sprintf(JText::_('COM_REPLICATION_BDD_TITRE_UNE'),
								 $this->base_source . " - " . $this->prefix_source,
								 $this->base_dest . " - " . $this->prefix_dest,
								 $this->path_backups
								 );
		}
		
		$this->archivedumps = ReplicationHelper::recherche_dumps();
		
		
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

		JToolBarHelper::title(JText::_('COM_REPLICATION_MANAGER_REPLICATIONS'), 'replication');

		if ($canDo->get('replication.apply_bdd')) 
		{
			JToolBarHelper::publish('replication.apply_bdd','Lancer la réplication');
	
				// Overwrite the old FTP credentials with the new ones.
			$temp = JFactory::getConfig();
			if($temp->get('offline')){
				JToolBarHelper::custom('replication.online', 'unpublish', '', 'Site Offline' , false, false );
			}else{
				JToolBarHelper::custom('replication.offline', 'unblock', '', 'Site Online' , false, false );
			}
		}
		/*
		 if ($canDo->get('core.create')) 
		{
			JToolBarHelper::addNew('replication.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) 
		{
			JToolBarHelper::editList('replication.edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.delete')) 
		{
			JToolBarHelper::deleteList('', 'replication.delete', 'JTOOLBAR_DELETE');
		}*/
		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_replication');
		}
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
	
	private function replication_bdd(&$msg = "") {
	
		### pour enregistrer les preferences
		jimport('joomla.filesystem.file');
		
		$cheminURL 				= JURI::root().'administrator'.DS.'components'.DS.'com_replication'.DS;
		$assets 				= JURI::root().'administrator'.DS.'components'.DS.'com_replication'.DS.'assets'.DS;
    	//$modelsspath 			= JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS;
		$Component_admin_path 	= JPATH_COMPONENT_ADMINISTRATOR.DS;
		$path_backups =$this->path_backups ;

		/*
		 if(! is_file($modelsspath.'dumpMySQL.php') ){
			return false;
		}
		### dumpMySQL
		include($modelsspath.'dumpMySQL.php');
		*/
		
		
		### param settings
		jimport( 'joomla.application.component.helper' );
		//$config	= JComponentHelper::getParams( 'com_replication' );
		
		$config = &JComponentHelper::getParams( 'com_replication' );
		
		/*
		$exclusion            = $config->get('exclusion', '');
		$destination_path     = $config->get('url_destination', '');
		*/

        ### param config general
		$conf =& JFactory::getConfig();
		//echo "<pre>", print_r($conf,1),"</pre>";
		$host_db       = $conf->getValue('config.host');
		$user_db       = $conf->getValue('config.user');
		$password_db   = $conf->getValue('config.password');
		$database_db   = $conf->getValue('config.db');
		$prefix_db     = $conf->getValue('config.dbprefix');
		$driver_db     = $conf->getValue('config.dbtype');
		$debug_db      = $conf->getValue('config.debug');
		
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
				JText::_('COM_REPLICATION_BDD_APPLIQUER')
				, 'warning'
			);
			### MISE A JOUR BASE

			$numlign1 			= 0;
			$numlign2 			= 0;
			$numlign3 			= 0;
			$numlign4 			= 0;

			jimport('joomla.database.database');
			jimport( 'joomla.database.table' );
			
			### param BDD source			
			$options_sour['host'] 		= $config->get( 'host_source', '');         
			$options_sour['user'] 		= $config->get( 'login_source', '');        
			$options_sour['password']   = $config->get( 'pass_source', '');         
			$options_sour['database']   = $config->get( 'base_source', '');         
			$options_sour['prefix']     = $config->get( 'prefix_source', 'jos');  
			$base_source                =  $options_sour['database']; 
			$prefix_source                =  $options_sour['prefix']; 

			### param BDD destination			
			$options_dest['host'] 		= $config->get( 'host_destination', '');      
			$options_dest['user'] 		= $config->get( 'login_destination', '');     
			$options_dest['password']   = $config->get( 'pass_destination', '');      
			$options_dest['database']   = $config->get( 'base_destination', '');      
			$options_dest['prefix']     = $config->get( 'prefix_destination', 'jos'); 
			$base_destination           =  $options_dest['database']; 
			$prefix_destination         =  $options_dest['prefix']; 

			### Autre config
			$mode               = 3;
			$sens               = 1; // 1=msg erreur,  0=msg base de destination est vide;
			$nomfichier_sour	= "source_".$base_destination."_";
			$nomfichier_recup	= "recup_".$base_destination."_";
			$nomfichier_dest	= "desti_".$base_destination."_";
			
			//test param base source
			if ( ! ReplicationHelper::testBDD($base_source, $db_sour, $options_sour) ) {
				return false;
			}
			
			//test param base destination
			if ( ! ReplicationHelper::testBDD($base_destination, $db_dest, $options_dest, 'COM_REPLICATION_DUMPMYSQL_MSG_DATABASE_ERROR_DEST')) {
				return false;
			}
			
			//valid creation table exclusion
			if ( ! ReplicationHelper::dumpMySQL_valid_table_exclu($base_source, $db_sour, $options_sour) ) {
				return false;
			}
			if ( !$db_sour = ReplicationHelper::createBDD($base_source, $db_sour, $options_sour)) {
				return false;
			}
			
			if ( !$db_dest = ReplicationHelper::createBDD($base_destination, $db_dest, $options_dest)) {
				return false;
			}
//echo __FILE__.'('.__LINE__.')'."<pre>", print_r("base ancienne",1),"</pre>"	;			
			### sauvegarde ancienne base en ligne en backup
			$sens = 1;
			$ret5 = ReplicationHelper::dumpMySQL_all($base_destination, $db_dest, $nomfichier_dest, $numlign5, $mode, $prefix_destination, $prefix_destination, $path_backups, $sens);
			if($ret5){
				$msg .= JText::_('COM_REPLICATION_BACKUP_REALISEE_AVEC_SUCCES')."<br>";
			}else{
				### Error
				JError::raiseWarning( '', JText::_( 'COM_REPLICATION_ERROR_BACKUP' ));
				$msg .= "$numlign1 == $numlign2";
				return false;
			}
		
//echo __FILE__.'('.__LINE__.')'."<pre>", print_r("base destination",1),"</pre>"	;			
			### recup base des table sur base destination
			//$sens = 0;
			$ret6 = ReplicationHelper::dumpMySQL_recup_table($base_source, $db_sour, $ar_nametable_rapatrier);
			$ar_nametable_rapatrier = is_array($ar_nametable_rapatrier)? $ar_nametable_rapatrier : array();
			
			$ret3 = ReplicationHelper::dumpMySQL_recup_desti($base_destination, $db_dest, $nomfichier_recup, $numlign3, $mode, $prefix_destination, $prefix_source, $path_backups, $ar_nametable_rapatrier);   				
			if($ret3){
				$msg .= JText::_('COM_REPLICATION_RECUP_REALISEE_AVEC_SUCCES')."<br>";
				$ret4 = ReplicationHelper::loadMySQL_all($base_source, $db_sour, $nomfichier_recup, $numlign4 );
				### avant de passer a la suite, on regarde si tous c'est bien passer
				if( ($numlign3 == $numlign4) && ($ret3 === TRUE)&& ($ret4 === TRUE) ){
					$msg .= JText::_('COM_REPLICATION_DUMPMYSQL_MSG_IMPORTATION_RECUP_REALISEE_AVEC_SUCCES')."<br>";
				}else{
					### Error
					JError::raiseWarning( '', JText::_( 'COM_REPLICATION_ERROR_IMPORT_RECUP' ));
					$msg .= "$numlign1 == $numlign2";
					return false;
				}
			}
				

//echo __FILE__.'('.__LINE__.')'."<pre>", print_r("importation",1),"</pre>"	;			
			### sauvegarder la base source pour importation dans destination
			$sens = 1;
			$ret1 = ReplicationHelper::dumpMySQL_all($base_source, $db_sour, $nomfichier_sour, $numlign1, $mode , $prefix_source, $prefix_destination,$path_backups, $sens);		
			if($ret1){

				$url=JURI::root().'administrator'.substr(str_replace('\\', '/', $nomfichier_sour),1);
				$nomfichier =basename ($url);
				$msg .= JText::_('COM_REPLICATION_DUMPMYSQL_MSG_SAUVEGARDE_REALISEE_AVEC_SUCCES')."<br>";
				$msg .= JText::_('COM_REPLICATION_DUMPMYSQL_MSG_LIGNES_AFFECTEES')."$numlign1<br>\n";
				$msg .= "<br><a href='$url'  target=_blank>".JText::_('COM_REPLICATION_DUMPMYSQL_MSG_FICHIER_SQL')." : $nomfichier</a><hr>";
				
//echo __FILE__.'('.__LINE__.')'."<pre>", print_r("load la base source ",1),"</pre>"	;			
				### load la base source dans la destination
				$ret2 = ReplicationHelper::loadMySQL_all($base_destination, $db_dest, $nomfichier_sour, $numlign2);
				
				### affiche le resutat
				if( ($numlign1 == $numlign2) && ($ret2 === TRUE) ){
					$msg .= JText::_('COM_REPLICATION_DUMPMYSQL_MSG_IMPORTATION_REALISEE_AVEC_SUCCES')."<br>";
					$msg .= JText::_('COM_REPLICATION_DUMPMYSQL_MSG_LIGNES_AFFECTEES')."$numlign2<br>\n";
					
				}else{
					### Error
					JError::raiseWarning( '', JText::_( 'COM_REPLICATION_ERROR_IMPORT' ));
					$msg .= "$numlign1 == $numlign2";
					return false;
				}
			}
			return true;
		}
	}
}
