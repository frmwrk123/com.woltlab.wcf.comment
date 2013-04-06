<?php
namespace wcf\system\user\notification\event;
use wcf\data\comment\Comment;
use wcf\data\user\User;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;

/**
 * User notification event for profile's owner for commment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.user.notification.event
 * @category	Community Framework
 */
class UserProfileCommentResponseOwnerUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * @see	wcf\system\user\notification\event\IUserNotificationEvent::getTitle()
	 */
	public function getTitle() {
		return $this->getLanguage()->get('wcf.user.notification.commentResponseOwner.title');
	}
	
	/**
	 * @see	wcf\system\user\notification\event\IUserNotificationEvent::getMessage()
	 */
	public function getMessage() {
		// @todo: use cache or a single query to retrieve required data
		$comment = new Comment($this->userNotificationObject->commentID);
		$commentAuthor = new User($comment->userID);
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.message', array(
			'author' => $this->author,
			'commentAuthor' => $commentAuthor
		));
	}
	
	/**
	 * @see	wcf\system\user\notification\event\IUserNotificationEvent::getEmailMessage()
	 */
	public function getEmailMessage() {
		$comment = new Comment($this->userNotificationObject->commentID);
		$commentAuthor = new User($comment->userID);
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.mail', array(
			'author' => $this->author,
			'commentAuthor' => $commentAuthor
		));
	}
}
