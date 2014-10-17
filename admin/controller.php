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

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * General Controller of Replication component
 */
//class ReplicationController extends JController
class ReplicationController extends JControllerLegacy
{
	/**
	 * display task
	 *
	 * @return void
	 */
	function display($cachable = false) 
	{
		require_once JPATH_COMPONENT.'/helpers/replication.php';

		$view   = $this->input->get('view', 'replications');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		// set default view if not set
		JRequest::setVar('view', JRequest::getCmd('view', 'Replications'));

		// call parent behavior
        //parent::display($cachable);

		// Set the submenu
		//ReplicationHelper::addSubmenu('replication_site');
		ReplicationHelper::addSubmenu(JRequest::getCmd('view', 'Replications'));
		
		parent::display();

		return $this;	}

}
