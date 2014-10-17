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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
if(!defined('BACKUPDS')){ define('BACKUPDS','.'.DS.'backups'.DS);}

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * Replications View
 */
class ReplicationViewReplications extends JView
{
	/**
	 * Replications view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		
			/*JFactory::getApplication()->enqueueMessage(
				JText::_('COM_REPLICATION_BDD_ALERT')
				, 'warning'
			);*/

		//config param composant
		$config = &JComponentHelper::getParams( 'com_replication' );
		$pathbackups = $config->get( 'pathbackups', BACKUPDS);  
		$namelog = $config->get( 'namelog', 'replication.log');  

		//ReplicationHelper::write_replic($pathbackups, $namelog);
		$this->nbrepli= ReplicationHelper::read_replic($pathbackups, $namelog);
		

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		$canDo = ReplicationHelper::getActions();
		JToolBarHelper::title(JText::_('COM_REPLICATION_MANAGER_REPLICATIONS'), 'replication');
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
		
		//JToolBarHelper::custom('replication.site',  'edit', ' ', 'SITE',false,false);
		//JToolBarHelper::custom('replication.bdd',  'edit', ' ', 'BDD',false,false);
		
		JToolBarHelper::divider();

		$temp = JFactory::getConfig();
		if($temp->get('offline')){
			JToolBarHelper::custom('replication.online', 'unpublish', '', 'Site Offline' , false, false );
		}else{
			JToolBarHelper::custom('replication.offline', 'unblock', '', 'Site Online' , false, false );
		}

		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_replication');
		}
		
		//JToolBarHelper::help(JText::_('JHELP_REPLICATION_MANAGER_REPLICATIONS') ,true);
		JToolBarHelper::custom('affiche-iframe-config',  'help', ' ', 'Installation et config.',false,false);
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
