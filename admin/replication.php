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
if(!defined('DS')){ define('DS',DIRECTORY_SEPARATOR);}

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_replication')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// require helper file
JLoader::register('ReplicationHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'replication.php');

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by Replication
//$controller = JController::getInstance('Replication');
$controller = JControllerLegacy::getInstance('Replication');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
