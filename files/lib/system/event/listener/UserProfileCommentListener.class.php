<?php
namespace wcf\system\event\listener;
use wcf\system\comment\CommentHandler;
use wcf\system\event\IEventListener;
use wcf\system\WCF;

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
	 * comment list object
	 * @var	wcf\data\comment\StructuredCommentList
	 */
	public $commentList = null;
	
	/**
	 * comment manager object
	 * @var	wcf\system\comment\manager\ICommentManager
	 */
	public $commentManager = null;
	
	/**
	 * object type id
	 * @var	integer
	 */
	public $objectTypeID = 0;
	
	/**
	 * @see wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		switch ($eventName) {
			case 'readParameters':
				$this->readParameters();
			break;
			
			case 'readData':
				$this->readData($eventObj);
			break;
			
			case 'assignVariables':
				$this->assignVariables();
			break;
		}
	}
	
	/**
	 * Initializes user profile comment system.
	 */
	protected function readParameters() {
		$this->objectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user.profileComment');
		
		$objectType = CommentHandler::getInstance()->getObjectType($this->objectTypeID);
		$this->commentManager = $objectType->getProcessor();
	}
	
	/**
	 * Fetches comment list data.
	 */
	protected function readData($eventObj) {
		$this->commentList = CommentHandler::getInstance()->getCommentList($this->objectTypeID, $this->commentManager, $eventObj->userID);
	}
	
	/**
	 * Assigns comment data to template.
	 */
	protected function assignVariables() {
		WCF::getTPL()->assign(array(
			'commentCanAdd' => $this->commentManager->canAdd(),
			'commentsPerPage' => $this->commentManager->commentsPerPage(),
			'commentList' => $this->commentList,
			'commentObjectTypeID' => $this->objectTypeID
		));
	}
}
