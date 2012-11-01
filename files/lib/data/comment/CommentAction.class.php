<?php
namespace wcf\data\comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\ValidateActionException;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Executes comment-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment
 * @category	Community Framework
 */
class CommentAction extends AbstractDatabaseObjectAction {
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
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\comment\CommentEditor';
	
	/**
	 * Validates parameters to add a comment.
	 */
	public function validateAddComment() {
		$this->validateContainerID();
		$this->validateMessage();
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if (!$this->commentProcessor->canAdd($this->parameters['data']['objectID'])) {
			throw new ValidateActionException("Insufficient permissions");
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
		
		// fire activity event
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
		if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType.'.recentActivityEvent')) {
			UserActivityEventHandler::getInstance()->fireEvent($objectType->objectType.'.recentActivityEvent', $objectType->packageID, $comment->commentID);
		}
		
		// fire notification event
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.notification')) {
			$notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor($objectType->objectType.'.notification');
			$userID = $notificationObjectType->getOwnerID($this->parameters['data']['objectID']);
			if ($userID != WCF::getUser()->userID) {
				$notificationObject = new CommentUserNotificationObject($comment);
				
				UserNotificationHandler::getInstance()->fireEvent('comment', $objectType->objectType.'.notification', $notificationObject, array($userID));
			}
		}
		
		return array(
			'containerID' => $this->parameters['data']['containerID'],
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
			throw new ValidateActionException("Insufficient permissions");
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
			'containerID' => $this->parameters['data']['containerID'],
			'template' => $this->renderResponse($response),
			'responses' => $responses
		);
	}
	
	/**
	 * Validates parameters to edit a comment or a response.
	 */
	public function validatePrepareEdit() {
		$this->validateContainerID();
		
		// validate comment id or response id
		try {
			$this->validateCommentID();
		}
		catch (ValidateActionException $e) {
			try {
				$this->validateResponseID();
			}
			catch (ValidateActionException $e) {
				throw new ValidateActionException("Incomplete request");
			}
		}
		
		// validate object type id
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		$commentID = ($this->comment === null) ?: $this->comment->commentID;
		$responseID = ($this->response === null) ?: $this->response->responseID;
		if (!$this->commentProcessor->canEdit($this->parameters['data']['objectID'], $commentID, $responseID)) {
			throw new ValidateActionException("Insufficient permissions");
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
			'containerID' => $this->parameters['data']['containerID'],
			'message' => $message
		);
		if ($this->response !== null) {
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
			'containerID' => $this->parameters['data']['containerID']
		);
		
		if ($this->response === null) {
			$editor = new CommentEditor($this->comment);
			$editor->update(array(
				'message' => $this->parameters['data']['message']
			));
			$comment = new Comment($this->comment->commentID);
			$returnValues['message'] = $comment->message;
		}
		else {
			$editor = new CommentResponseEditor($this->response);
			$editor->update(array(
				'message' => $this->parameters['data']['message']
			));
			$response = new CommentResponse($this->response->responseID);
			$returnValues['message'] = $response->message;
		}
		
		return $returnValues;
	}
	
	/**
	 * Renders a comment.
	 * 
	 * @param	wcf\data\comment\Comment	$comment
	 * @return	string
	 */
	protected function renderComment(Comment $comment) {
		$comment = new StructuredComment($comment);
		$comment->setIsEditable($this->commentProcessor->canEdit($this->parameters['data']['objectID'], $comment->commentID));
		
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
		$response->setIsEditable($this->commentProcessor->canEdit($this->parameters['data']['objectID'], null, $response->responseID));
		
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
			throw new ValidateActionException("Invalid message given");
		}
		$this->parameters['data']['message'] = StringUtil::trim($this->parameters['data']['message']);
		if (empty($this->parameters['data']['message'])) {
			throw new ValidateActionException("Invalid message given");
		}
	}
	
	/**
	 * Validates container id parameter.
	 */
	protected function validateContainerID() {
		if (!isset($this->parameters['data']['containerID']) || empty($this->parameters['data']['containerID'])) {
			throw new ValidateActionException("Invalid container id given");
		}
	}
	
	/**
	 * Validates object type id parameter.
	 * 
	 * @return	wcf\data\object\type\ObjectType
	 */
	protected function validateObjectType() {
		if (!isset($this->parameters['data']['objectTypeID'])) {
			throw new ValidateActionException("Invalid object type id given");
		}
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
		if ($objectType === null) {
			throw new ValidateActionException("Invalid object type id given");
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
			throw new ValidateActionException("Invalid comment id given");
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
			throw new ValidateActionException("Invalid response id given");
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
