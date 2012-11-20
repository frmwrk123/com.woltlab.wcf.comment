<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\comment\StructuredComment;
use wcf\data\user\UserProfile;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes comment response-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class CommentResponseAction extends AbstractDatabaseObjectAction {
	/**
	 * comment object
	 * @var	wcf\data\comment\Comment
	 */
	protected $comment = null;
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\comment\response\CommentResponseEditor';
	
	/**
	 * Validates parameters for response list.
	 */
	public function validateGetResponseList() {
		// validate container id
		if (!isset($this->parameters['data']['containerID']) || empty($this->parameters['data']['containerID'])) {
			throw new UserInputException('containerID');
		}
		
		// validate page no
		$this->parameters['data']['pageNo'] = (isset($this->parameters['data']['pageNo'])) ? intval($this->parameters['data']['pageNo']) : 0;
		if (!$this->parameters['data']['pageNo']) {
			throw new UserInputException('pageNo');
		}
		
		// validate comment id
		if (isset($this->parameters['data']['commentID'])) {
			$this->comment = new Comment($this->parameters['data']['commentID']);
		}
		if ($this->comment === null || !$this->comment->commentID) {
			throw new UserInputException('commentID');
		}
	}
	
	/**
	 * Returns a structured response list.
	 * 
	 * @return	array
	 */
	public function getResponseList() {
		// populate comment
		$this->comment = new StructuredComment($this->comment);
		$userProfile = UserProfile::getUserProfile($this->comment->userID);
		$this->comment->setUserProfile($userProfile);
		
		// get response list
		$responseList = new StructuredCommentResponseList($this->comment->commentID);
		$responseList->sqlOffset = (($this->parameters['data']['pageNo'] - 1) * 20);
		$responseList->sqlLimit = 20;
		$responseList->readObjects();
		
		WCF::getTPL()->assign(array(
			'responseList' => $responseList
		));
		
		return array(
			'containerID' => $this->parameters['data']['containerID'],
			'template' => WCF::getTPL()->fetch('commentResponseList')
		);
	}
}
