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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * Replications View
 */
class ReplicationViewExclusion extends JView
{

	/**
	 * Replications view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Initialiase variables.
		$items = $this->get('Items');
		if (empty($items) ){

			JError::raiseNotice( '', JText::_('COM_REPLICATION_VERIFIER_PARAM_VIDE') );
			$items = $this->get('Tablelist');
			
		}
		//config de base joomla
		$mainframe = JFactory::getApplication();
		$dbprefix = $mainframe->getCfg('dbprefix');
		//config param composant
		$config = &JComponentHelper::getParams( 'com_replication' );
		$prefix_source = $config->get( 'prefix_source', 'jom_');
		
		if ( $dbprefix != $prefix_source ){

			JError::raiseNotice( '', sprintf(JText::_('COM_REPLICATION_EXCLUSION_TABLE_DEFAULT'),  $dbprefix , $prefix_source) );
			$items = $this->get('Tablelist');
		}
		//$select = $this->get('Select');
		//$pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign data to the view
		$this->items = $items;
		//$this->select = $select;
		//$this->pagination = $pagination;

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
		
		
		JToolBarHelper::save('exclusion.save');
		/*
		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||$canDo->get('core.create'))) {	
			JToolBarHelper::apply('client.apply', 'JTOOLBAR_APPLY');
			//JToolBarHelper::save('client.save', 'JTOOLBAR_SAVE');
		}
		if (!$checkedOut && $canDo->get('core.create')) {
		
			JToolBarHelper::custom('client.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('client.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('client.cancel');
		} else {
			JToolBarHelper::cancel('client.cancel', 'JTOOLBAR_CLOSE');
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
}
