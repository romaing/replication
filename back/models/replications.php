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

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

/**
 * Replications Model
 */
class ReplicationModelReplications extends JModelList
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('id_table,status');

		// From the Replication table
		$query->from('#__replicationexclusion');
		return $query;
	}
}
