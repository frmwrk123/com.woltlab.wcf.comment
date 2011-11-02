<?php
namespace wcf\data\comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\user\UserProfile;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes comment-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment
 * @category 	Community Framework
 */
class CommentAction extends AbstractDatabaseObjectAction {
	protected $comment = null;
	protected $response = null;
	
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\comment\CommentEditor';
	
	public function validateAddComment() { }
	
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
		
		return array(
			'containerID' => $this->parameters['data']['containerID'],
			'template' => $this->renderComment($comment)
		);
	}
	
	public function validateAddResponse() {
		if (isset($this->parameters['data']['commentID'])) {
			$this->comment = new Comment($this->parameters['data']['commentID']);
		}
		
		if ($this->comment === null || !$this->comment->commentID) {
			// yada yada yada
		}
	}
	
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
		
		return array(
			'containerID' => $this->parameters['data']['containerID'],
			'template' => $this->renderResponse($response),
			'responses' => $responses
		);
	}
	
	public function validatePrepareEdit() {
		if (isset($this->parameters['data']['commentID'])) {
			$this->comment = new Comment($this->parameters['data']['commentID']);
		}
		else if (isset($this->parameters['data']['responseID'])) {
			$this->response = new CommentResponse($this->parameters['data']['responseID']);
		}
	}
	
	public function prepareEdit() {
		$message = '';
		if ($this->parameters['data']['type'] == 'comment') {
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
		if ($this->parameters['data']['type'] == 'response') {
			$returnValues['responseID'] = $this->response->responseID;
		}
		
		return $returnValues;
	}
	
	public function validateEdit() {
		$this->validatePrepareEdit();
	}
	
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
	
	protected function renderComment(Comment $comment) {
		$comment = new StructuredComment($comment);
		
		// set user profile
		$userProfile = UserProfile::getUserProfile($comment->userID);
		$comment->setUserProfile($userProfile);
		
		WCF::getTPL()->assign(array(
			'commentList' => array($comment)
		));
		return WCF::getTPL()->fetch('commentList');
	}
	
	protected function renderResponse(CommentResponse $response) {
		$response = new StructuredCommentResponse($response);
		
		// set user profile
		$userProfile = UserProfile::getUserProfile($response->userID);
		$response->setUserProfile($userProfile);
		
		// render response
		WCF::getTPL()->assign(array(
			'responseList' => array($response)
		));
		return WCF::getTPL()->fetch('commentResponseList');
	}
}
