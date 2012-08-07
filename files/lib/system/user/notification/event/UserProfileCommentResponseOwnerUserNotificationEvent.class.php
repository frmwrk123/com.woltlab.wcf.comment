<?php
namespace wcf\system\user\notification\event;
use wcf\data\comment\Comment;
use wcf\data\user\User;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;
use wcf\system\user\notification\type\IUserNotificationType;
use wcf\system\WCF;

/**
 * User notification event for profile's owner for commment responses.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.user.notification.event
 * @category 	Community Framework
 */
class UserProfileCommentResponseOwnerUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * @see	wcf\system\user\notification\event\IUserNotificationEvent::getTitle()
	 */
	public function getTitle() {
		return WCF::getLanguage()->get('wcf.user.notification.commentResponseOwner.shortOutput');
	}
	
	/**
	 * @see	wcf\system\user\notification\event\IUserNotificationEvent::getMessage()
	 */
	public function getMessage() {
		$comment = new Comment($this->userNotificationObject->commentID);
		$commentAuthor = new User($comment->userID);
		
		return WCF::getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.output', array(
			'author' => $this->author,
			'commentAuthor' => $commentAuthor
		));
	}
	
	/**
	 * @see	wcf\system\user\notification\event\IUserNotificationEvent::getRenderedOutput()
	 */
	public function getRenderedOutput() {
		WCF::getTPL()->assign(array(
			'author' => $this->author,
			'buttons' => $this->getActions(),
			'message' => $this->getMessage(),
			'time' => $this->userNotificationObject->time
		));
		
		return WCF::getTPL()->fetch('userNotificationDetails');
	}
}
