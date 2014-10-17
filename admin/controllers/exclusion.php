<?php
/**
 * @version 3.0
 * @subpackage Components
 * @package replication
 * @copyright Copyright (C) 2007 - 2013 romain gires. All rights reserved.
 * @author      romain gires
 * @link        http://composant.gires.net/
 * @license     License GNU General Public License version 2 or later
 */
defined( '_JEXEC' ) or die;

jimport('joomla.application.component.controller');


//class ReplicationControllerExclusion extends JController
class ReplicationControllerExclusion extends JControllerLegacy
{
	/**
	 * Method to save a record.
	 */
	public function save()
	{
    	// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));


		// Initialise variables.
		$app		= JFactory::getApplication();
		//$model		= $this->getModel('Replication', 'ExclusionModel');
		$ar_listetable		= JRequest::getVar('listetable', array(), 'post', 'array');

        /*
        // Validate the posted data.
		$form	= $model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data = $model->validate($form, $data);
        */
        
    	if(!is_array($ar_listetable)){
            echo "probleme avec le post";
            $this->setMessage(JText::_('COM_REPLICATION_EXCLUSION_SAVE_PROBLEME'));
            $this->setRedirect(JRoute::_('index.php?option=com_replication&view=exclusion', false));
            return false;
        }else{
            $db =& JFactory::getDBO();
            while (list($key, $val) = each($ar_listetable)) {
                if ($val!= 0 ){
                    $query = "REPLACE #__replicationexclusion ";
            	    $query.= " SET status  = ".$db->Quote($val);
            	    $query.= " , id_table  = ".$db->Quote($key);            	    
            	    $db->setQuery( $query );
                	if ( !$db->query() ) {
            	    	JError::raiseError(500, $db->getErrorMsg() );
            	    } 
                }else{
                    $query = "DELETE FROM #__replicationexclusion ";
                    $query.= " WHERE id_table = ".$db->Quote($key);
            	    $db->setQuery( $query );
                	if ( !$db->query() ) {
            	    	JError::raiseError(500, $db->getErrorMsg() );
            	    } 
                }

        	}
        }
        

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_REPLICATION_EXCLUSION_SAVED'));
		$this->setRedirect(JRoute::_('index.php?option=com_replication&view=exclusion', false));
        return true;
    }
    

}
