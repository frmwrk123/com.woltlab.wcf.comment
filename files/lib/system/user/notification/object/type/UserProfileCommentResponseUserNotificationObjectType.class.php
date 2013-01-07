<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\Comment;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;

/**
 * Represents a comment response notification object type.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.user.notification.object.type
 * @category	Community Framework
 */
class UserProfileCommentResponseUserNotificationObjectType extends AbstractObjectTypeProcessor implements IUserNotificationObjectType {
	/**
	 * @see	wcf\system\user\notification\object\type\IUserNotificationObjectType::getObjectByID()
	 */
	public function getObjectByID($objectID) {
		$object = new CommentResponse($objectID);
		if (!$object->responseID) {
			// create empty object for unknown request id
			$object = new CommentResponse(null, array('responseID' => $objectID));
		}
		
		return array($object->responseID => new CommentResponseUserNotificationObject($object));
	}
	
	/**
	 * @see	wcf\system\user\notification\object\type\IUserNotificationObjectType::getObjectsByIDs()
	 */
	public function getObjectsByIDs(array $objectIDs) {
		$objectList = new CommentResponseList();
		$objectList->getConditionBuilder()->add("comment_response.responseID IN (?)", array($objectIDs));
		$objectList->readObjects();
		
		$objects = array();
		foreach ($objectList as $object) {
			$objects[$object->responseID] = new CommentResponseUserNotificationObject($object);
		}
		
		foreach ($objectIDs as $objectID) {
			// append empty objects for unknown ids
			if (!isset($objects[$objectID])) {
				$objects[$objectID] = new CommentResponseUserNotificationObject(new Comment(null, array('responseID' => $objectID)));
			}
		}
		
		return $objects;
	}
}
