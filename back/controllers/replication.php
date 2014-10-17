<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

jimport('joomla.application.component.controllerform');

/**
 * Messages Component Message Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class ReplicationControllerReplication extends JControllerForm
{
		
	/*public function bdd()
	{
		$this->setRedirect('index.php?option=com_replication&view=replication_bdd');
	}
	public function site()
	{
		$this->setRedirect('index.php?option=com_replication&view=replication_site');
	}*/
	public function offline()
	{
		$this->setRedirect('index.php?option=com_replication&view='.JRequest::getCmd('view').'&task=offline');
	}
	public function online()
	{
		$this->setRedirect('index.php?option=com_replication&view='.JRequest::getCmd('view').'&task=online');
	}
	public function apply_bdd()
	{
		
		$this->setRedirect('index.php?option=com_replication&view=replication_bdd&task=appliquer');

		/*
		if ($replyId = JRequest::getInt('reply_id')) {
			$this->setRedirect('index.php?option=com_replication&view=message&layout=edit&reply_id='.$replyId);
		} else {
			$this->setMessage(JText::_('COM_MESSAGES_INVALID_REPLY_ID'));
			$this->setRedirect('index.php?option=com_replication&view=messages');
		}*/
	}

	public function apply_site()
	{
		
		$this->setRedirect('index.php?option=com_replication&view=replication_site&task=appliquer');

	}
	public function clearlog()
	{
		$this->setRedirect('index.php?option=com_replication&view=replication_site&task=clearlog');
	}
	
	/**
	 * Method (override) to check if you can save a new or existing record.
	 *
	 * Adjusts for the primary key name and hands off to the parent class.
	 *
	 * @param	array	An array of input data.
	 * @param	string	The name of the key for the primary key.
	 *
	 * @return	boolean
	 */
	/*
	protected function allowSave($data, $key = 'table_id')
	{
		return parent::allowSave($data, $key);
	}
	*/
	/**
	 * Reply to an existing message.
	 *
	 * This is a simple redirect to the compose form.
	 */
	/*
	public function reply()
	{
		if ($replyId = JRequest::getInt('reply_id')) {
			$this->setRedirect('index.php?option=com_replication&view=message&layout=edit&reply_id='.$replyId);
		} else {
			$this->setMessage(JText::_('COM_MESSAGES_INVALID_REPLY_ID'));
			$this->setRedirect('index.php?option=com_replication&view=messages');
		}
	}
	*/
}
