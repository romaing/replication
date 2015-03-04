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
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

jimport( 'joomla.application.component.helper' );

/**
 * Replications Model
 */
class ReplicationModelExclusion extends JModelList
{
	/**
	 * Returns an object list
	 *
	 * @param	string	The query
	 * @param	int		Offset
	 * @param	int		The number of records
	 * @return	array
	 * @since	1.5
	 *
	 * supprime la limit de la query
	 * 
	 */
	protected function _getList($query, $limitstart=0, $limit=0) {
		$limit=0;
		$this->_db->setQuery($query, $limitstart, $limit);
		$result = $this->_db->loadObjectList();
		return $result;
	}
	

	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);


		// Select some fields
		$query->select('id_table,status');

		// From the Replication table
		$query->from('#__replicationexclusion');
		
		/*
		 //config de base joomla
		$mainframe = JFactory::getApplication();
		$dbprefix = $mainframe->getCfg('dbprefix');
		*/
		//config param composant
		$config = JComponentHelper::getParams( 'com_replication' );
		$dbprefix = $config->get( 'prefix_source', 'jom_');  
		
		$prefixstrlen = strlen($dbprefix);
		$where = "`id_table` LIKE '$dbprefix%' ";
		$query->where($where);

		return $query;
	}

	public function getTableListe() 
	{
		$db = JFactory::getDBO();
		$rows = ReplicationHelper::getTableListe($db);
		return $this->rows = $rows;
	}
	
	public static function getStatuslevel( &$row )
	{
		$db = JFactory::getDBO();
		$query = 'SELECT id AS value, name AS text'
			. ' FROM #__replicationstatut'
			. ' ORDER BY id'
			;
		$db->setQuery( $query );
		$groups = $db->loadObjectList();
		return $groups;
	}
	function setStatuslevel( &$row, $groups )
	{
		$status = JHTML::_('select.genericlist', $groups, "listetable[".$row->id_table."]" , 'class="inputbox" size="1"', 'value', 'text', intval( $row->status ), '', 1 );
		return $status;
	}


}
