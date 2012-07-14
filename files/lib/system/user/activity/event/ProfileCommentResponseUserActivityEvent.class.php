<?php
namespace wcf\system\user\activity\event;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\data\user\UserList;
use wcf\system\style\StyleHandler;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for profile comment responses.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.user.activity.event
 * @category 	Community Framework
 */
class ProfileCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @see	wcf\system\user\activity\event\IUserActivityEvent::prepare()
	 */
	public function prepare(array $events) {
		$responseIDs = array();
		foreach ($events as $event) {
			$responseIDs[] = $event->objectID;
		}
		
		// fetch responses
		$responseList = new CommentResponseList();
		$responseList->getConditionBuilder()->add("comment_response.responseID IN (?)", array($responseIDs));
		$responseList->sqlLimit = 0;
		$responseList->readObjects();
		$responses = $responseList->getObjects();
		
		// fetch comments
		$commentIDs = array();
		foreach ($responses as $response) {
			$commentIDs[] = $response->commentID;
		}
		$commentList = new CommentList();
		$commentList->getConditionBuilder()->add("comment.commentID IN (?)", array($commentIDs));
		$commentList->sqlLimit = 0;
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// fetch users
		$userIDs = array();
		foreach ($comments as $comment) {
			$userIDs[] = $comment->objectID;
			$userIDs[] = $comment->userID;
		}
		
		$userList = new UserList();
		$userList->getConditionBuilder()->add("user_table.userID IN (?)", array($userIDs));
		$userList->sqlLimit = 0;
		$userList->readObjects();
		$users = $userList->getObjects();
		
		// get icon
		// $icon = StyleHandler::getInstance()->getStyle()->getIconPath('comment1', 'S');
		
		// set message
		foreach ($events as $event) {
			if (isset($responses[$event->objectID])) {
				$response = $responses[$event->objectID];
				if (isset($comments[$response->commentID])) {
					$comment = $comments[$response->commentID];
					if (isset($users[$comment->objectID]) && isset($users[$comment->userID])) {
						// title
						$text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.profileCommentResponse', array(
							'commentAuthor' => $users[$comment->userID],
							'user' => $users[$comment->objectID]
						));
						$event->setTitle($text);
						
						// description
						$event->setDescription($response->getFormattedMessage());
						
						// icon
						// $event->setIcon($icon);
					}
				}
			}
		}
	}
}
