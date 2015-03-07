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
defined('_JEXEC') or die;
if(!defined('DS')){ define('DS',DIRECTORY_SEPARATOR);}
if(!defined('POINTDS')){ define('POINTDS',".".DIRECTORY_SEPARATOR);}
if(!defined('BACKUPDS')){ define('BACKUPDS','.'.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR);}



/**
 * Replication component helper.
 */
class ReplicationHelper
{
	
	protected $path_backups;
	public static $extension = 'com_replication';


	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu) 
	{
		
		JSubMenuHelper::addEntry(JText::_('COM_REPLICATION_SUBMENU_REPLICATIONS'), 'index.php?option=com_replication', $submenu == 'Replications');
		JSubMenuHelper::addEntry(JText::_('COM_REPLICATION_SUBMENU_REPLICATION_SITE'), 'index.php?option=com_replication&view=replication_site', $submenu == 'replication_site');
		JSubMenuHelper::addEntry(JText::_('COM_REPLICATION_SUBMENU_REPLICATION_BDD'), 'index.php?option=com_replication&view=replication_bdd', $submenu == 'replication_bdd');
		JSubMenuHelper::addEntry(JText::_('COM_REPLICATION_SUBMENU_EXCLUSIONS'), 'index.php?option=com_replication&view=exclusion', $submenu == 'exclusion');
		
		// set some global property
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-replication {background-image: url(administration/components/com_replication/assets/images/replication-48x48.png);}');

		if ($submenu == 'exclusion'){
			$document->setTitle(JText::_('COM_REPLICATION_SUBMENU_EXCLUSIONS'));
		}elseif ($submenu == 'replication_bdd'){
			$document->setTitle(JText::_('COM_REPLICATION_SUBMENU_REPLICATION_BDD'));
		}elseif ($submenu == 'replication_site'){
			$document->setTitle(JText::_('COM_REPLICATION_SUBMENU_REPLICATION_SITE'));
		}
	}
	/**
	 * Get the actions
	 */
	public static function getActions($messageId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($messageId)) {
			$assetName = 'com_replication';
		}else {
			$assetName = 'com_replication.message.'.(int) $messageId;
		}
		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete',
			'replication.apply_site', 'replication.apply_bdd',
		);
		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}
		return $result;
	}

	/**
	 * Crée une instance de base
	 * @param  array $options param de connection
	 * @return instance          insatnce en fonction des params
	 */
	public function createBDD($options)
	{
		$nameBDD = $options['database']; 
		$db =& JDatabase::getInstance( $options );
		$db->_errorNum =0;
		if ( JError::isError($db) ) {
			JError::raiseNotice( '', JText::_('COM_REPLICATION_PROBLEME_DE_PASSWORD_LA_PLUPART_DU_TEMP_AVEC_LA_BASE') ." " . $nameBDD );
			JError::raiseWarning( '', $nameBDD." ".JText::_('COM_REPLICATION_REPLICATION_ALL_DATABASE_ERROR') . $db->toString() );
			return false;
		}				
		return $db;
		
	}
	public function testBDD($options, $mess="" )
	{
		$nameBDD = $options['database']; 
		$db =& JDatabase::getInstance( $options );
		$query = "SHOW TABLES";
		$db->setQuery( $query );
		if ( !$db->query() ) {
			JError::raiseNotice( '', JText::_('COM_REPLICATION_VERIFIER_PARAM_CONNECTION')." : \"$nameBDD\"" .  JText::_($mess));
			return false;
		} 
		return true;
	}

	private function getTableFilterDefaut($id_table) {
		$mainframe = JFactory::getApplication();
		$dbprefix = $mainframe->getCfg('dbprefix');
		$table = substr ( $id_table , strlen($dbprefix) );
		//	1 = 'REPLIQUER'  Defaut
		//	2 = 'REP RAPATRIER AVANT DE REPLIQUER'
		//	3 = 'REP NE PAS REPLIQUER'
		$filtre = array(
			'session'=>'3',
			'users'=>'2',
			'banner_tracks'=>'2',
			'messages'=>'2',
			'content_rating'=>'2',
			'core_log_searches'=>'2',
			'user_notes'=>'2', 
		);
		return  (array_key_exists($table, $filtre)) ? $filtre[$table]: 1;
	}

	public static function getTableListe(&$db) 
	{
		$rows = array();

		// Create a new query object.
		//$db		= $this->getDbo();
		$query = $db->getQuery(true);
		$ar_nametable = $db->getTableList();
		// Check for errors.
		if ($db->getErrorNum()) {
			echo $db->stderr();
			return false;
		}

		//config param composant
		$config = JComponentHelper::getParams( 'com_replication' );
		$dbprefix = $config->get( 'prefix_source', 'jom_');  
		
		$prefixstrlen = strlen($dbprefix);

        for ($i=0; $i < count($ar_nametable); $i++) {
			if(substr($ar_nametable[$i],0,$prefixstrlen) == $dbprefix){
				$val = new stdclass;
				$val->id_table  = $ar_nametable[$i];
				$status = ReplicationHelper::getTableFilterDefaut($val->id_table);
				$val->status    = $status;
				$rows[$i]=  $val;
			}
		}
		return $rows = $rows;
	}
	
	/**
	 * met à jour la table exclusion
	 * @param  array $options param de la base
	 * @return boolean          true = pas d'erreur
	 */
	public function dumpMySQL_valid_table_exclu( $options )
	{
		$db =& JDatabase::getInstance( $options );
		$query = "SELECT COUNT(*) FROM #__replicationexclusion ";
		$db->setQuery( $query );
		$count = $db->loadResult();

		/*
		// supprime tous avant de l'INSERT
		$query = "TRUNCATE TABLE `#__replicationexclusion` ";
		$db->setQuery( $query );
		$db->query();
		*/

		if ($count == 0) {
			//JError::raiseNotice( '', JText::_('COM_REPLICATION_SAUVEGARDER_EXCLUSION')  );
			//$db = JFactory::getDBO();
			$items = ReplicationHelper::getTableListe($db);

			foreach($items as $value) {
				$query = "INSERT #__replicationexclusion ";
				$query.= " SET status  = ".$db->Quote($value->status);
				$query.= " , id_table  = ".$db->Quote($value->id_table);            	    
				$db->setQuery( $query );
				if ( !$db->query() ) {
					JError::raiseError(500, $db->getErrorMsg() );
					return false;
				} 
	    	}
	    }

		
		return true;
	}		
	public function dumpMySQL_recup_table($nameBDD, &$db, &$ar_nametable_rapatrier )
	{
		//RAPATRIER AVANT DE REPLIQUER
		$query = "SELECT * FROM #__replicationexclusion ";
		$query.= " WHERE status = 2";
		$db->setQuery( $query );
		$ar_nametable_rapatrier = $db->loadColumn();

		if ($db->getErrorNum()) {
			JError::raiseNotice( '', JText::_('COM_REPLICATION_DUMPMYSQL_MSG_ERROR_PREFIX_DE_LA_BASE')." $nameBDD ". $db->stderr() );
			return false;
		}
		
		if (count($ar_nametable_rapatrier)==0) {
			$filename = "";
			$numlign = "";
			//JError::raiseNotice( '', JText::_('COM_REPLICATION_PAS_DE_RECUPERATION_PREVU')  );
			return false;
		}
		return true;
	}		
	public function dumpMySQL_recup_desti($nameBDD, &$db, &$filename, &$numlign, $mode=3, $prefix_in, $prefix_out, $path=POINTDS,$ar_nametable_rapatrier )
	{
		$numlign = 0;
   
		$numlign++;
		$entete = "-- -----------------------------\n";
		$entete .= "-- ".JText::_('COM_REPLICATION_DUMPMYSQL_MSG_DUMP_DE_LA_BASE');
		$entete .= " ".$nameBDD." au ".date("d-M-Y H:i:s")."\n";
		$numlign++;
		$entete .= "-- -----------------------------\n\n\n";
			   
		$datex = date('U');
		$max_execution_time = ini_get('max_execution_time');
		$max = $datex + $max_execution_time;

		$inserts = "";
		foreach ($ar_nametable_rapatrier as $nametable) {
			
			ReplicationHelper::modif_time_limit( $max );
			
			$inserts .= ReplicationHelper::dump_insert($db,$nametable,$mode, $prefix_in, $prefix_out, $numlign,0);
		}
		$filename = ReplicationHelper::sauvegarde_fichier($entete."\n\n".$inserts, $path, $filename);
		return true;
	
	}
	
	public function dumpMySQL_all($nameBDD, &$db, &$filename, &$numlign, $mode=3, $prefix_in, $prefix_out, $path=POINTDS , $sens, $alltable=0)
	{
		$numlign = 0;

		$query = "SHOW TABLES";
		$db->setQuery( $query );
		$ar_nametable = $db->loadColumn();

		if($alltable==0){
			try {
				//NE PAS SAUVEGARDER
				$query = "SELECT * FROM #__replicationexclusion ";
				$query.= " WHERE status = 4";
				$db->setQuery( $query );
				$ar_nametable_exclude = $db->loadColumn();
			} catch (Exception $e)
			{
				if ($db->getErrorNum()==1146) {
					//JError::raiseNotice( '', JText::_("TABLE 'REPLICATION_EXCLUDE' DOESN'T EXIST MAIS C'EST NORMAL SI C'EST LA PREMIERE FOIS") );
					//return false;
				}elseif ($db->getErrorNum()) {
					echo $db->stderr();
					return false;
				}
				//$this->setError($e->getMessage());
				//return false;
			}
		}

		$ar_nametable_exclude = is_array($ar_nametable_exclude)? $ar_nametable_exclude : array();


		$numlign++;
		$entete = "-- -----------------------------\n";
		$entete .= "-- ".JText::_('COM_REPLICATION_DUMPMYSQL_MSG_DUMP_DE_LA_BASE');
		$entete .= " ".$nameBDD." au ".date("d-M-Y H:i:s")."\n";
		$numlign++;
		$entete .= "-- -----------------------------\n\n\n";
	

		$datex = date('U');
		$max_execution_time = ini_get('max_execution_time');
		$max = $datex + $max_execution_time;



		$inserts = "";
		foreach ($ar_nametable as $nametable) {
			
			ReplicationHelper::modif_time_limit( $max );

			if (!in_array ($nametable, $ar_nametable_exclude)) {
			  $inserts .= ReplicationHelper::dump_insert($db,$nametable,$mode, $prefix_in, $prefix_out , $numlign, $sens);
			}
		}
	
		$filename = ReplicationHelper::sauvegarde_fichier($entete."\n\n".$inserts, $path, $filename);
		return true;
		
	}
		
	### DROP TABLE
	public function loadMySQL_all($nameBDD, &$db, $filename, &$numlign ){
		$numlign= -1;
	
		$handle = gzopen ($filename, "r");
		$contents = gzread ($handle,filesize($filename)*15); //15 pour la compression w9, mettre + si besion
		gzclose ($handle);
		
		### on force la table en UTF-8 avant importer
		### SHOW VARIABLES LIKE 'character_set_system';
	
		$query = "ALTER DATABASE `".$nameBDD."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci"."\n";
		//mysql_query($query);
		$db->setQuery( "$query" );
		if ( !$db->query() ) {
			//JError::raiseWarning('', JText::_( 'COM_REPLICATION_DUMPMYSQL_MSG_DATABASE_ERROR' ).$db->getErrorMsg() );
		}
		$insertions = "-- -----------------------------\n";
		$ar_contents = explode($insertions, $contents);
			
		$datex = date('U');
		$max_execution_time = ini_get('max_execution_time');
		$max = $datex + $max_execution_time;

		foreach ($ar_contents as $query) {

			ReplicationHelper::modif_time_limit( $max , 15 );
			$numlign ++;
			$query = trim($query);
			if (!empty($query)){
				$db->setQuery( "$query" );
				if ( !$db->query() ) {
					//echo "<hr>"." numlign = $numlign query = '$query'";
					JError::raiseWarning('', JText::_( 'COM_REPLICATION_DUMPMYSQL_MSG_DATABASE_ERROR' ).$db->getErrorMsg() );
					return false;
				} 
			}
		}
		return true;
	}
	/**
	 * Cree le contenu des fichier de dump
	 * @param  instance $db         instance de la base en cours
	 * @param  int $mode       si l'on souhaite une sauvegarde de structure ou total
	 * @param  string $prefix_in  
	 * @param  string $prefix_out 
	 * @param  int $numlign    nombre de ligne qui seront inserer
	 * @param  int $sens       1=msg erreur,  0=msg base de destination est vide
	 * @return string             ligne d'insert = commentaire
	 */
	private function dump_insert(&$db, $nametable, $mode, $prefix_in, $prefix_out ,&$numlign, $sens)
	{

		$creations  = "";
		$insertions = "";
		$table = substr(strstr($nametable, '_'), 1);    
		$table_in = $prefix_in.$table;
		$table_out = $prefix_out.$table;
		
		$query = "SELECT * FROM ".$table_in;
		$db->setQuery( "$query" );
		if ( !$db->query() ) {
			if ( $sens == 0 ) { 
				### message si la base de destination est vide
				JError::raiseNotice('', JText::_( 'COM_REPLICATION_TABLE_NON_CREER_POUR_INSTANT' )); 
			}else{
				JError::raiseNotice('', JText::_( 'COM_REPLICATION_LA_TABLE_NON_TROUVER' )." ".$db->getErrorMsg() ); 
			}
			return ;
		}
		
		### si l'utilisateur a demandé la structure ou la totale
		if($mode == 1 || $mode == 3){
			$numlign++;
			$creations .= "-- -----------------------------\n";
			$creations .= "-- ".JText::_('COM_REPLICATION_DUMPMYSQL_MSG_CREATION_DE_LA_TABLE')." ".$table_out."\n";
			
			//SHOW CREATE table
			$ar_creationTable = $db->getTableCreate( $table_in );
			if (!is_array($ar_creationTable)){
				return ;    
			}
		
			$creationTable = str_replace($table_in, $table_out,$ar_creationTable[$table_in] );
			$creations .= "DROP TABLE IF EXISTS `".$table_out."` ;\n\n";
			$numlign++;
			$creations .= "-- -----------------------------\n";
			$creations .= $creationTable.";;\n\n";
		}

		### si l'utilisateur a demandé les données ou la totale
		if($mode > 1)
		{
			$query = "SELECT * FROM ".$table_in;
			$db->setQuery( "$query" );
	
			//$ar_donnees = $db->loadAssocList();
			$ar_donnees = $db->loadRowList();
		   
			$numlign++;
			$insertions .= "-- -----------------------------\n";
			$insertions .= "-- ".JText::_('COM_REPLICATION_DUMPMYSQL_MSG_INSERSIONS_DANS_LA_TABLE')." ".$table_out."\n";
			foreach ($ar_donnees as $nuplet) {
				$numlign++;
				$insertions .= "-- -----------------------------\n";
				$insertions .= "INSERT INTO ".$table_out." VALUES(";
				//for($i=0; $i < count($nuplet); $i++){
				$i=0;
				foreach ($nuplet as $val) {
					if($i != 0){
						$insertions .=  ", ";
					}
					$insertions .=  "'".addslashes($val)."'";
					$i++;
				}
				$insertions .=  ");\n";
			}
			$insertions .= "\n";
		}
		return $creations.$insertions;
	}
	public static  function vieuxfichier($chemin , $nombrearchive = 5, $name_ereg = '/^dump/'){
		$num = 0;
		$list = "";
		$url=JURI::root().'administrator'.substr(str_replace('\\', DS, $chemin),1);

		if (is_dir($chemin)) {
			$dh = opendir($chemin);
			$listeentry=array();
			while (false !== ($entry =readdir($dh)) ) {
				if(preg_match($name_ereg, $entry)){
					$num++ ;
					$listeentry[] = $entry;
				}
			}
			sort($listeentry);
			while ($num > $nombrearchive)  {
				$num--;
				$entry = array_shift($listeentry);
				unlink($chemin.$entry);
				$list .=  JText::_('COM_REPLICATION_DUMPMYSQL_MSG_SUPPRIMER_SQL')." $entry<br>";
			}
			### affiche les fichiers si je suis admin
			foreach ($listeentry as $key => $entry) {
				$list .= "<a href='$url$entry'  target=_blank>".JText::_('COM_REPLICATION_DUMPMYSQL_MSG_ARCHIVE_SQL')." $entry</a><br>\n ";
			}
		}
		return $list;
	}
	public static function read_replic( $path=POINTDS, $name){
		
		//si dossier backups n'existe pas, on le creer
		ReplicationHelper::creer_dossier( $path);
		
		if(is_file($path.$name)) {
			$fp = fopen($path.$name, 'r');
			$nbvisites = fread($fp, 100);
			fclose($fp);
			if ($nbvisites=="") $nbvisites = 0;
			return $nbvisites;
		}
		return false;
	}
	public function write_replic( $path=POINTDS, $name){
		$fp = fopen($path.$name,"c+");
		$nbvisites = fread($fp, 100);
		if ($nbvisites=="") $nbvisites = 0;
		$nbvisites++;
		fseek($fp,0);
		fputs($fp,$nbvisites);
		fclose($fp);
	}
	public function write_config_dest(){
		$config = JComponentHelper::getParams( 'com_replication' );

		$data['host'] 		= $config->get( 'host_destination', '');      
		$data['user'] 		= $config->get( 'login_destination', '');     
		$data['password']   = $config->get( 'pass_destination', '');      
		$data['database']   = $config->get( 'base_destination', '');      
		$data['dbprefix']    = $config->get( 'prefix_destination', '');
		
		$url_destination     = $config->get( 'url_destination', 'site2'); 
		$data['log_path']     = $url_destination.DS.'logs'; 
		$data['tmp_path']     = $url_destination.DS.'tmp'; 
		
		// Get the previous configuration.
		$prev = new JConfig();
		$prev = JArrayHelper::fromObject($prev);
		
		// Merge the new data in. We do this to preserve values that were not in the form.
		$data = array_merge($prev, $data);
		
		// Create the new configuration object.
		$config = new JRegistry('config');
		$config->loadArray($data);
		
		/*
		 * Write the configuration file.
		 */
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');
		
		// Set the configuration file path.
		$file = realpath($url_destination).DS.'configuration.php';
		
		// Overwrite the old FTP credentials with the new ones.
		$temp = JFactory::getConfig();
		$temp->set('host', 		$data['host']);
		$temp->set('user', 		$data['user']);
		$temp->set('password', 	$data['password']);
		$temp->set('database', 	$data['database']);
		$temp->set('dbprefix', 	$data['dbprefix']);
		$temp->set('log_path', 	$data['log_path']);
		$temp->set('tmp_path', 	$data['tmp_path']);
		
		$temp->set('offline', 1);
 
		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);

		// Attempt to make the file writeable if using FTP.
		if (is_file($file) && !$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0644')) {
				JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_REPLICATION_ERROR_CONFIGURATION_PHP_NOTWRITABLE'));
		}
		
		// Attempt to write the configuration file as a PHP class named JConfig.
		$configString = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
		if (!JFile::write($file, $configString)) {
				$this->setError(JText::_('COM_CONFIG_ERROR_WRITE_FAILED'));
				return false;
		}
		
		// Attempt to make the file unwriteable if using FTP.
		if ($data['ftp_enable'] == 0 && !$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0444')) {
				JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_REPLICATION_ERROR_CONFIGURATION_PHP_NOTWRITABLE'));
		}		
		return true;
	}
	public static function write_exclude(){
		$config = JComponentHelper::getParams( 'com_replication' );
		$rs_exclusion = $config->get( 'rs_exclusion', 'exclusion.txt');  
		$Component_admin_path 	= JPATH_COMPONENT_ADMINISTRATOR.DS;
		$exclusion = $config->get( 'exclusion', 'configuration.php');
		$ar_exclusion = array_map('trim',explode ( ';', $exclusion ));
		$exclusiontxt = implode("\n", $ar_exclusion);		
		$exclusion = $config->set( 'exclusion', $exclusiontxt);


		if($fp = fopen($Component_admin_path.$rs_exclusion,"w+") ){
			fputs($fp,$exclusiontxt);
			fclose($fp);
			return true;
		}
		return false;
	}
	public static function clearlog(){
		$config = JComponentHelper::getParams( 'com_replication' );
		$rs_logfile = $config->get( 'rs_logfile', BACKUPDS.'rsync-log.txt');  
		$Component_admin_path 	= JPATH_ADMINISTRATOR.DS;

		if($fp = fopen($Component_admin_path.$rs_logfile,"w+") ){
			fputs($fp,"");
			fclose($fp);
			return true;
		}
		return false;
	}
 	public static function creer_dossier_backups(){
		$config = JComponentHelper::getParams( 'com_replication' );
		$pathbackups = $config->get( 'pathbackups', BACKUPDS);
		ReplicationHelper::creer_dossier( $pathbackups );
	}
 	public static function creer_dossier_destination(){
		$config = JComponentHelper::getParams( 'com_replication' );
		$pathdestination = $config->get( 'url_destination', '');
		ReplicationHelper::creer_dossier( $pathdestination.DS );
	}
 	private static function creer_dossier( $path){
		if(!is_dir($path)) {
			//creer le dossoer backups
			mkdir($path, 0755, true);
			
			//ecrit fichier index.html
			$fp = fopen($path."index.html", 'w');
			fclose($fp);
			return true;
		}
		return false;
	}
	private function sauvegarde_fichier( $insertions, $path=POINTDS, $name){
		$datex = date('U');
		$max_execution_time = ini_get('max_execution_time');
		$max = $datex + $max_execution_time;

		ReplicationHelper::modif_time_limit( $max );
		ReplicationHelper::creer_dossier( $path);
		
		### sauvegarde du fichier
		$datef = date("ymd_His");
		$filename = $path.$name.$datef.".sql.gz";
		$fichierDumpgz = gzopen("$filename",'w9');
		gzwrite($fichierDumpgz, $insertions);
		gzclose($fichierDumpgz);
		return $filename;
	}
	public function mettre_offline($path=JPATH_CONFIGURATION, $offline=0)	{
		$data['offline']	= 	$offline;
		
		// Get the previous configuration.
		$prev = new JConfig();
		$prev = JArrayHelper::fromObject($prev);
		
		// Merge the new data in. We do this to preserve values that were not in the form.
		$data = array_merge($prev, $data);
		// Create the new configuration object.
		$config = new JRegistry('config');
		$config->loadArray($data);
		
		/*
		 * Write the configuration file.
		 */
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');
		
		// Set the configuration file path.
		if(!empty($path) ){
			$file = realpath($path).DS.'configuration.php';
		}else{
			$file = JPATH_CONFIGURATION.DS.'configuration.php';
		}
		
		// Overwrite the old FTP credentials with the new ones.
		$temp = JFactory::getConfig();
		$temp->set('offline', $data['offline']);
		
		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);
		
		// Attempt to make the file writeable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0644')) {
				JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTWRITABLE'));
		}
		
		// Attempt to write the configuration file as a PHP class named JConfig.
		$configString = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
		if (!JFile::write($file, $configString)) {
				$this->setError(JText::_('COM_CONFIG_ERROR_WRITE_FAILED'));
				return false;
		}
		
		// Attempt to make the file unwriteable if using FTP.
		if ($data['ftp_enable'] == 0 && !$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0444')) {
				JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'));
		}
		
		return true;
	}
	/**
	 * $datex = date('U');
	 * $max_execution_time = ini_get('max_execution_time');
	 * $max = $datex + $max_execution_time;
	 * modif_time_limit( $max );
	 */
	public function modif_time_limit( & $max , $plus = 10){
		$safe_mode = ini_get('safe_mode');
		if( !$safe_mode AND  ( $max == (date('U')+$plus) ) ){
				$max = $max+$plus;
				set_time_limit($max);
		}
	} 	
	/**
	 * replicationViewreplication_site::cygdrive_path
	 * transforme les Path windows d:\wwwroot\joomla\
	 * en path /cygdrive/d/wwwroot/joomla/
	 */
	public static function cygdrive_path(& $path) {
		//si le 2eme caratere est :, je considere que l'on est sur window
		if (substr($path,1,2) == ':'.DS ){
			// on passe tous les antislash en slash 
			$path = str_replace(DS, "/", $path);
			// recupere la lettre 
			$lettre = substr($path,0,1);
			// recupere le path sans la lettre et les :/
			$path = substr($path,3);
			// on rajoute /cygdrive/
			$path = DS."cygdrive".DS."$lettre".DS."$path";
			// on rajoute des doublequotes
			$path = '"'.$path.'"';
		}
	}
	public static  function recherche_fichierrsync() {
		$config = JComponentHelper::getParams( 'com_replication' );
		$pathbackups = $config->get( 'pathbackups', BACKUPDS);

		### affiche les fichiers si je suis admin
		$archive="";
		$canDo = ReplicationHelper::getActions();
		if ($canDo->get('core.admin')) {
			//retourne la liste des anciennes sauvegardes
			$archive .=  "<b>".JText::_('COM_REPLICATION_DERNIERE_MISE_A_JOUR_SITE')."</b><br>";
			$archive .= ReplicationHelper::vieuxfichier($pathbackups,5,'/^rsync/');
		}
		return $archive;
	}
	public static  function recherche_dumps() {
		$config = JComponentHelper::getParams( 'com_replication' );
		$pathbackups = $config->get( 'pathbackups', BACKUPDS);

		### affiche les fichiers si je suis admin
		$archive="";
		$canDo = ReplicationHelper::getActions();
		if ($canDo->get('core.admin')) {
			//retourne la liste des anciennes sauvegardes
			$archive .=  "<b>".JText::_('COM_REPLICATION_DERNIERE_MISE_A_JOUR')."</b><br>";
			$archive .= ReplicationHelper::vieuxfichier($pathbackups,5,'/^source/');
			$archive .=   "<hr><b>".JText::_('COM_REPLICATION_TABLE_RECUPERER_POUR_MISE_A_JOUR_DE_LA_SOURCE')."</b><br>";
			$archive .= ReplicationHelper::vieuxfichier($pathbackups,5,'/^recup/');
			$archive .=   "<hr><b>".JText::_('COM_REPLICATION_BACKUP_AVANT_MISE_A_JOUR_DE_LA_BASE_DESTINATION')."</b><br>";
			$archive .= ReplicationHelper::vieuxfichier($pathbackups,5,'/^desti/');
		}
		return $archive;
	}
	public function replication_bdd(&$msg = "") {
		
		### pour enregistrer les preferences
		jimport('joomla.filesystem.file');

		$cheminURL 				= JURI::root().'administrator'.DS.'components'.DS.'com_replication'.DS;
		$assets 				= JURI::root().'administrator'.DS.'components'.DS.'com_replication'.DS.'assets'.DS;
    	//$modelsspath 			= JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS;
		$Component_admin_path 	= JPATH_COMPONENT_ADMINISTRATOR.DS;

		### param settings
		$config = JComponentHelper::getParams( 'com_replication' );
		$url_destination = $config->get('url_destination');

		if( empty($url_destination) ){
		
			### METTRE A JOUR LES PREFS
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_REPLICATION_METTRE_A_JOUR_LES_PREFS')
				, 'warning'
			);
			return false;
		
		}else{
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_REPLICATION_BDD_APPLIQUER')
				, 'notice'
			);
			### MISE A JOUR BASE

			$numlign1 			= 0;
			$numlign2 			= 0;
			$numlign3 			= 0;
			$numlign4 			= 0;

			jimport('joomla.database.database');
			jimport( 'joomla.database.table' );

			### param BDD source			
			$options['sour']['host'] 		= $config->get( 'host_source', '');         
			$options['sour']['user'] 		= $config->get( 'login_source', '');        
			$options['sour']['password']   	= $config->get( 'pass_source', '');         
			$options['sour']['database']   	= $config->get( 'base_source', '');         
			$options['sour']['prefix']     	= $config->get( 'prefix_source', 'jos');  

			### param BDD destination			
			$options['dest']['host'] 		= $config->get( 'host_destination', '');      
			$options['dest']['user'] 		= $config->get( 'login_destination', '');     
			$options['dest']['password']   	= $config->get( 'pass_destination', '');      
			$options['dest']['database']   	= $config->get( 'base_destination', '');      
			$options['dest']['prefix']     	= $config->get( 'prefix_destination', 'jos'); 

			### Autre config
			$mode               = 3;
			$sens               = 1; // 1=msg erreur,  0=msg base de destination est vide;
			$nomfichier_sour	= "source_".$options['sour']['database']."_";
			$nomfichier_recup	= "recup_".$options['dest']['database']."_";
			$nomfichier_dest	= "desti_".$options['dest']['database']."_";
			
			//test param base source
			if ( ! ReplicationHelper::testBDD($options['sour']) ) {
				return false;
			}

			//test param base destination
			if ( ! ReplicationHelper::testBDD($options['dest'], 'COM_REPLICATION_DUMPMYSQL_MSG_DATABASE_ERROR_DEST')) {
				return false;
			}
	
			//valid creation table exclusion
			if ( ! ReplicationHelper::dumpMySQL_valid_table_exclu($options['sour']) ) {
				return false;
			}
			if ( !$db_sour = ReplicationHelper::createBDD($options['sour'])) {
				return false;
			}
			
			if ( !$db_dest = ReplicationHelper::createBDD($options['dest'])) {
				return false;
			}
			$base_source            =  $options['sour']['database']; 
			$prefix_source          =  $options['sour']['prefix']; 
			$base_destination 		=  $options['dest']['database']; 
			$prefix_destination 	=  $options['dest']['prefix']; 
			$path_backups			=  $this->path_backups;

			### sauvegarde ancienne base destination en backup
			$sens = 1;
			$alltable = 1;
			$ret5 = ReplicationHelper::dumpMySQL_all($base_destination, $db_dest, $nomfichier_dest, $numlign5, $mode, $prefix_destination, $prefix_source, $path_backups, $sens, $alltable);
			if($ret5){
				$msg .= JText::_('COM_REPLICATION_BACKUP_REALISEE_AVEC_SUCCES')."<br>";
			}else{
				### Error
				JError::raiseWarning( '', JText::_( 'COM_REPLICATION_ERROR_BACKUP' ));
				$msg .= "$numlign1 == $numlign2";
				return false;
			}

			### sauvegarde ancienne base en ligne en backup
			$ret6 = ReplicationHelper::dumpMySQL_recup_table($base_source, $db_sour, $ar_nametable_rapatrier);
			$ar_nametable_rapatrier = is_array($ar_nametable_rapatrier)? $ar_nametable_rapatrier : array();

			### mise à jour des table sur base source 
			//$sens = 0;
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


			### sauvegarder la base source pour importation dans destination
			$sens = 1;
			$alltable = 0;
			$ret1 = ReplicationHelper::dumpMySQL_all($base_source, $db_sour, $nomfichier_sour, $numlign1, $mode , $prefix_source, $prefix_destination,$path_backups, $sens, $alltable);		

			if($ret1){

				$url=JURI::root().'administrator'.substr(str_replace('\\', '/', $nomfichier_sour),1);
				$nomfichier =basename ($url);
				$msg .= JText::_('COM_REPLICATION_DUMPMYSQL_MSG_SAUVEGARDE_REALISEE_AVEC_SUCCES')."<br>";
				$msg .= JText::_('COM_REPLICATION_DUMPMYSQL_MSG_LIGNES_AFFECTEES')."$numlign1<br>\n";
				$msg .= "<br><a href='$url'  target=_blank>".JText::_('COM_REPLICATION_DUMPMYSQL_MSG_FICHIER_SQL')." : $nomfichier</a><hr>";
				
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








