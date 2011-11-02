<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\comment\StructuredComment;
use wcf\data\user\UserProfile;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes comment response-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment.response
 * @category 	Community Framework
 */
class CommentResponseAction extends AbstractDatabaseObjectAction {
	protected $comment = null;
	
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\comment\response\CommentResponseEditor';
	
	public function validateGetResponseList() {
		if (isset($this->parameters['data']['commentID'])) {
			$this->comment = new Comment($this->parameters['data']['commentID']);
		}
		
		if ($this->comment === null || !$this->comment->commentID) {
			// yada yada yada
		}
	}
	
	public function getResponseList() {
		// populate comment
		$this->comment = new StructuredComment($this->comment);
		$userProfile = UserProfile::getUserProfile($this->comment->userID);
		$this->comment->setUserProfile($userProfile);
		
		// get response list
		$responseList = new StructuredCommentResponseList($this->comment->commentID);
		$responseList->sqlOffset = (($this->parameters['data']['pageNo'] - 1) * 20);
		$responseList->sqlLimit = 20;
		$responseCount = $responseList->countObjects();
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
