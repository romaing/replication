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
if(!defined('DS')){ define('DS',DIRECTORY_SEPARATOR);}

jimport('joomla.application.component.view');


//class replicationViewreplication_bdd extends JView
class replicationViewreplication_bdd extends JViewLegacy
{
	public function display($tpl = null)
	{
		
		$this->task = JRequest::getVar('task');
		$this->une = "";
		$this->msg = "";

		//config param composant
		$config = JComponentHelper::getParams( 'com_replication' );

		$this->base_source = $config->get( 'base_source', '');
		$this->base_dest = $config->get( 'base_destination', '');
		$this->path_backups = realpath($config->get( 'pathbackups', './backups/')).DS;
		$this->path_backups = str_replace(DS, "/", $this->path_backups);

		$namelog = $config->get( 'namelog', 'replication.log');  
		
		if ($this->task=="appliquer"){
		
			### MISE A JOUR BASE
			//$this->replication_bdd($msg );
			ReplicationHelper::replication_bdd($msg );
			$this->msg = $msg;
				
			ReplicationHelper::write_replic($this->path_backups, $namelog);

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
								 $this->base_source,
								 $this->base_dest,
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

		JToolbarHelper::title(JText::_('COM_REPLICATION_MANAGER_REPLICATIONS'), 'replication');

		if ($canDo->get('replication.apply_bdd')) 
		{
			JToolbarHelper::publish('replication.apply_bdd','Lancer la réplication');
	
				// Overwrite the old FTP credentials with the new ones.
			$temp = JFactory::getConfig();
			if($temp->get('offline')){
				JToolbarHelper::custom('replication.online', 'unpublish', '', 'Site Offline' , false, false );
			}else{
				JToolbarHelper::custom('replication.offline', 'unblock', '', 'Site Online' , false, false );
			}
		}
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
}
