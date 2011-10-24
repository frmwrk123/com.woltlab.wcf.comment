<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;

/**
 * Handles user profile comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class UserProfileCommentListener implements IEventListener {
	/**
	 * @see wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		switch ($eventName) {
			case 'readParameters':
				$this->readParameters();
			break;
			
			case 'readData':
				$this->readData();
			break;
			
			case 'assignVariables':
				$this->assignVariables();
			break;
		}
	}
	
	protected function readParameters() {
	}
	
	protected function readData() {
	}
	
	protected function assignVariables() {
	}
}
