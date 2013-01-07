<?php
namespace wcf\data\comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseAction;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\like\LikeHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Executes comment-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment
 * @category	Community Framework
 */
class CommentAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('loadComments');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\comment\CommentEditor';
	
	/**
	 * comment object
	 * @var	wcf\data\comment\Comment
	 */
	protected $comment = null;
	
	/**
	 * comment processor
	 * @var	wcf\system\comment\manager\ICommentManager
	 */
	protected $commentProcessor = null;
	
	/**
	 * response object
	 * @var	wcf\data\comment\response\CommentResponse
	 */
	protected $response = null;
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		// update counters
		$processors = array();
		$commentIDs = array();
		foreach ($this->objects as $comment) {
			if (!isset($processors[$comment->objectTypeID])) {
				$objectType = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID);
				$processors[$comment->objectTypeID] = $objectType->getProcessor();
				
				$commentIDs[$comment->objectTypeID] = array();
			}
			
			$processors[$comment->objectTypeID]->updateCounter($comment->objectID, -1 * ($comment->responses + 1));
			$commentIDs[$comment->objectTypeID][] = $comment->objectID;
		}
		
		if (!empty($commentIDs)) {
			$likeObjectIDs = array();
			foreach ($commentIDs as $objectTypeID => $objectIDs) {
				// remove activity events
				$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
				if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType.'.recentActivityEvent')) {
					UserActivityEventHandler::getInstance()->removeEvents($objectType->objectType.'.recentActivityEvent', $objectType->packageID, $objectIDs);
				}
				
				$likeObjectIDs = array_merge($likeObjectIDs, $objectIDs);
			}
			
			$likeObjectsProvider = new LikeableCommentProvider();
			$likeObjects = $likeObjectsProvider->getObjectsByIDs($likeObjectIDs);
			foreach ($likeObjects as $likeObject) {
				LikeHandler::getInstance()->removeLikes($likeObject);
			}
		}
		
		return parent::delete();
	}
	
	/**
	 * Validates parameters to load comments.
	 */
	public function validateLoadComments() {
		$this->readInteger('lastCommentTime', false, 'data');
		$this->readInteger('objectID', false, 'data');
		
		$objectType = $this->validateObjectType();
		$this->commentProcessor = $objectType->getProcessor();
		if (!$this->commentProcessor->isAccessible($this->parameters['data']['objectID'])) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns parsed comments.
	 * 
	 * @return	array
	 */
	public function loadComments() {
		$commentList = CommentHandler::getInstance()->getCommentList($this->commentProcessor, $this->parameters['data']['objectTypeID'], $this->parameters['data']['objectID'], false);
		$commentList->getConditionBuilder()->add("comment.time < ?", array($this->parameters['data']['lastCommentTime']));
		$commentList->readObjects();
		
		WCF::getTPL()->assign(array(
			'commentList' => $commentList
		));
		
		return array(
			'template' => WCF::getTPL()->fetch('commentList')
		);
	}
	
	/**
	 * Validates parameters to add a comment.
	 */
	public function validateAddComment() {
		$this->validateMessage();
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if (!$this->commentProcessor->canAdd($this->parameters['data']['objectID'])) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Adds a comment.
	 * 
	 * @return	array
	 */
	public function addComment() {
		// create comment
		$comment = CommentEditor::create(array(
			'objectTypeID' => $this->parameters['data']['objectTypeID'],
			'objectID' => $this->parameters['data']['objectID'],
			'time' => TIME_NOW,
			'userID' => WCF::getUser()->userID,
			'message' => $this->parameters['data']['message'],
			'responses' => 0,
			'lastResponseIDs' => serialize(array())
		));
		
		// update counter
		$this->commentProcessor->updateCounter($this->parameters['data']['objectID'], 1);
		
		// fire activity event
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
		if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType.'.recentActivityEvent')) {
			UserActivityEventHandler::getInstance()->fireEvent($objectType->objectType.'.recentActivityEvent', $objectType->packageID, $comment->commentID);
		}
		
		// fire notification event
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.notification')) {
			$notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor($objectType->objectType.'.notification');
			$userID = $notificationObjectType->getOwnerID($comment->commentID);
			if ($userID != WCF::getUser()->userID) {
				$notificationObject = new CommentUserNotificationObject($comment);
				
				UserNotificationHandler::getInstance()->fireEvent('comment', $objectType->objectType.'.notification', $notificationObject, array($userID));
			}
		}
		
		return array(
			'template' => $this->renderComment($comment)
		);
	}
	
	/**
	 * Validates parameters to add a response.
	 */
	public function validateAddResponse() {
		// validate comment id
		$this->validateCommentID();
		
		// validate object type id
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if (!$this->commentProcessor->canAdd($this->parameters['data']['objectID'])) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Adds a response.
	 * 
	 * @return	array
	 */
	public function addResponse() {
		// create response
		$response = CommentResponseEditor::create(array(
			'commentID' => $this->comment->commentID,
			'time' => TIME_NOW,
			'userID' => WCF::getUser()->userID,
			'message' => $this->parameters['data']['message']
		));
		
		// update response data
		$lastResponseIDs = $this->comment->getLastResponseIDs();
		if (count($lastResponseIDs) == 3) array_shift($lastResponseIDs);
		$lastResponseIDs[] = $response->responseID;
		$responses = $this->comment->responses + 1;
		
		// update comment
		$commentEditor = new CommentEditor($this->comment);
		$commentEditor->update(array(
			'lastResponseIDs' => serialize($lastResponseIDs),
			'responses' => $responses
		));
		
		// update counter
		$this->commentProcessor->updateCounter($this->parameters['data']['objectID'], 1);
		
		// fire activity event
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->comment->objectTypeID);
		if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType.'.response.recentActivityEvent')) {
			UserActivityEventHandler::getInstance()->fireEvent($objectType->objectType.'.response.recentActivityEvent', $objectType->packageID, $response->responseID);
		}
		
		// fire notification event
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.response.notification')) {
			$notificationObject = new CommentResponseUserNotificationObject($response);
			if ($this->comment->userID != WCF::getUser()->userID) {
				UserNotificationHandler::getInstance()->fireEvent('commentResponse', $objectType->objectType.'.response.notification', $notificationObject, array($this->comment->userID));
			}
			
			// notify the container owner
			if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.notification')) {
				$notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor($objectType->objectType.'.notification');
				$userID = $notificationObjectType->getOwnerID($this->comment->commentID);
				if ($userID != WCF::getUser()->userID) {
					UserNotificationHandler::getInstance()->fireEvent('commentResponseOwner', $objectType->objectType.'.response.notification', $notificationObject, array($userID));
				}
			}
		}
		
		return array(
			'commentID' => $this->comment->commentID,
			'template' => $this->renderResponse($response),
			'responses' => $responses
		);
	}
	
	/**
	 * Validates parameters to edit a comment or a response.
	 */
	public function validatePrepareEdit() {
		// validate comment id or response id
		try {
			$this->validateCommentID();
		}
		catch (UserInputException $e) {
			try {
				$this->validateResponseID();
			}
			catch (UserInputException $e) {
				throw new UserInputException('objectIDs');
			}
		}
		
		// validate object type id
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if ($this->comment !== null) {
			if (!$this->commentProcessor->canEditComment($this->comment)) {
				throw new PermissionDeniedException();
			}
		}
		else {
			if (!$this->commentProcessor->canEditResponse($this->response)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Prepares editing of a comment or a response.
	 * 
	 * @return	array
	 */
	public function prepareEdit() {
		$message = '';
		if ($this->comment !== null) {
			$message = $this->comment->message;
		}
		else {
			$message = $this->response->message;
		}
		
		$returnValues = array(
			'action' => 'prepare',
			'message' => $message
		);
		if ($this->comment !== null) {
			$returnValues['commentID'] = $this->comment->commentID;
		}
		else {
			$returnValues['responseID'] = $this->response->responseID;
		}
		
		return $returnValues;
	}
	
	/**
	 * @see	wcf\data\comment\CommentAction::validatePrepareEdit()
	 */
	public function validateEdit() {
		$this->validatePrepareEdit();
		
		$this->validateMessage();
	}
	
	/**
	 * Edits a comment or response.
	 * 
	 * @return	array
	 */
	public function edit() {
		$returnValues = array(
			'action' => 'saved',
		);
		
		if ($this->response === null) {
			$editor = new CommentEditor($this->comment);
			$editor->update(array(
				'message' => $this->parameters['data']['message']
			));
			$comment = new Comment($this->comment->commentID);
			$returnValues['commentID'] = $this->comment->commentID;
			$returnValues['message'] = $comment->message;
		}
		else {
			$editor = new CommentResponseEditor($this->response);
			$editor->update(array(
				'message' => $this->parameters['data']['message']
			));
			$response = new CommentResponse($this->response->responseID);
			$returnValues['responseID'] = $this->response->responseID;
			$returnValues['message'] = $response->message;
		}
		
		return $returnValues;
	}
	
	/**
	 * Validates parameters to remove a comment or response.
	 */
	public function validateRemove() {
		// validate comment id or response id
		try {
			$this->validateCommentID();
		}
		catch (UserInputException $e) {
			try {
				$this->validateResponseID();
			}
			catch (UserInputException $e) {
				throw new UserInputException('objectIDs');
			}
		}
		
		// validate object type id
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if ($this->comment !== null) {
			if (!$this->commentProcessor->canDeleteComment($this->comment)) {
				throw new PermissionDeniedException();
			}
		}
		else {
			if (!$this->commentProcessor->canDeleteResponse($this->response)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Removes a comment or response.
	 * 
	 * @return	array
	 */
	public function remove() {
		if ($this->comment !== null) {
			$objectAction = new CommentAction(array($this->comment), 'delete');
			$objectAction->executeAction();
			
			return array(
				'commentID' => $this->comment->commentID
			);
		}
		else {
			$objectAction = new CommentResponseAction(array($this->response), 'delete');
			$objectAction->executeAction();
			
			return array(
				'responseID' => $this->response->responseID
			);
		}
	}
	
	/**
	 * Renders a comment.
	 * 
	 * @param	wcf\data\comment\Comment	$comment
	 * @return	string
	 */
	protected function renderComment(Comment $comment) {
		$comment = new StructuredComment($comment);
		$comment->setIsEditable($this->commentProcessor->canEditComment($comment->getDecoratedObject()));
		
		// set user profile
		$userProfile = UserProfile::getUserProfile($comment->userID);
		$comment->setUserProfile($userProfile);
		
		WCF::getTPL()->assign(array(
			'commentList' => array($comment)
		));
		return WCF::getTPL()->fetch('commentList');
	}
	
	/**
	 * Renders a response.
	 * 
	 * @param	wcf\data\comment\response\CommentResponse	$response
	 * @return	string
	 */
	protected function renderResponse(CommentResponse $response) {
		$response = new StructuredCommentResponse($response);
		$response->setIsEditable($this->commentProcessor->canEditResponse($response->getDecoratedObject()));
		
		// set user profile
		$userProfile = UserProfile::getUserProfile($response->userID);
		$response->setUserProfile($userProfile);
		
		// render response
		WCF::getTPL()->assign(array(
			'responseList' => array($response)
		));
		return WCF::getTPL()->fetch('commentResponseList');
	}
	
	/**
	 * Validates message parameter.
	 */
	protected function validateMessage() {
		// validate message
		if (!isset($this->parameters['data']['message'])) {
			throw new UserInputException('message');
		}
		$this->parameters['data']['message'] = StringUtil::trim($this->parameters['data']['message']);
		if (empty($this->parameters['data']['message'])) {
			throw new UserInputException('message');
		}
	}
	
	/**
	 * Validates object type id parameter.
	 * 
	 * @return	wcf\data\object\type\ObjectType
	 */
	protected function validateObjectType() {
		if (!isset($this->parameters['data']['objectTypeID'])) {
			throw new UserInputException('objectTypeID');
		}
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
		if ($objectType === null) {
			throw new UserInputException('objectTypeID');
		}
		
		return $objectType;
	}
	
	/**
	 * Validates comment id parameter.
	 */
	protected function validateCommentID() {
		if (isset($this->parameters['data']['commentID'])) {
			$this->comment = new Comment($this->parameters['data']['commentID']);
		}
		if ($this->comment === null || !$this->comment->commentID) {
			throw new UserInputException('commentID');
		}
	}
	
	/**
	 * Validates response id parameter.
	 */
	protected function validateResponseID() {
		if (isset($this->parameters['data']['responseID'])) {
			$this->response = new CommentResponse($this->parameters['data']['responseID']);
		}
		if ($this->response === null || !$this->response->responseID) {
			throw new UserInputException('responseID');
		}
	}
	
	/**
	 * Returns the comment object.
	 * 
	 * @return	wcf\data\comment\Comment
	 */
	public function getComment() {
		return $this->comment;
	}
	
	/**
	 * Returns the comment response object.
	 * 
	 * @return	wcf\data\comment\response\CommentResponse
	 */
	public function getResponse() {
		return $this->response;
	}
	
	/**
	 * Returns the comment manager.
	 * 
	 * @return	wcf\system\comment\manager\ICommentManager
	 */
	public function getCommentManager() {
		return $this->commentProcessor;
	}
}
