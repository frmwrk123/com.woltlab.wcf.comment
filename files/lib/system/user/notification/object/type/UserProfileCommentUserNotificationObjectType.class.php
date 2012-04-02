<?php
namespace wcf\system\user\notification\object\type;
use wcf\system\WCF;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;
use wcf\system\user\notification\object\CommentUserNotificationObject;

/**
 * Represents a comment notification object type.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.user.notification.object.type
 * @category 	Community Framework
 */
class UserProfileCommentUserNotificationObjectType extends AbstractObjectTypeProcessor implements ICommentUserNotificationObjectType, IUserNotificationObjectType {
	/**
	 * @see wcf\system\user\notification\object\type\IUserNotificationObjectType::getObjectByID()
	 */
	public function getObjectByID($objectID) {
		$object = new Comment($objectID);
		if (!$object->commentID) {
			// create empty object for unknown request id
			$object = new Comment(null, array('commentID' => $objectID));
		}
		
		return array($object->commentID => new CommentUserNotificationObject($object));
	}

	/**
	 * @see wcf\system\user\notification\object\type\IUserNotificationObjectType::getObjectsByIDs()
	 */
	public function getObjectsByIDs(array $objectIDs) {
		$objectList = new CommentList();
		$objectList->getConditionBuilder()->add("comment.commentID IN (?)", array($objectIDs));
		$objectList->readObjects();
		
		$objects = array();
		foreach ($objectList as $object) {
			$objects[$object->commentID] = new CommentUserNotificationObject($object);
		}
		
		foreach ($objectIDs as $objectID) {
			// append empty objects for unknown ids
			if (!isset($objects[$objectID])) {
				$objects[$objectID] = new CommentUserNotificationObject(new Comment(null, array('commentID' => $objectID)));
			}
		}
		
		return $objects;
	}
	
	/**
	 * @see	wcf\system\user\notification\object\type\ICommentUserNotificationObjectType::getOwnerID()
	 */
	public function getOwnerID($objectID) {
		$sql = "SELECT	objectID
			FROM	wcf".WCF_N."_comment
			WHERE	commentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($objectID));
		$row = $statement->fetchArray();
		
		return ($row ? $row['objectID'] : 0);
	}
}
