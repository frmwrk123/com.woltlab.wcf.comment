<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\user\UserProfile;
use wcf\system\comment\manager\ICommentManager;

/**
 * Provides a structured comment response list.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class StructuredCommentResponseList extends CommentResponseList {
	/**
	 * comment object
	 * @var	wcf\data\comment\Comment;
	 */
	public $comment = null;
	
	/**
	 * comment manager
	 * @var	wcf\system\comment\manager\ICommentManager
	 */
	public $commentManager = null;
	
	/**
	 * @see	wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'comment_response.time DESC';
	
	/**
	 * Creates a new structured comment response list.
	 * 
	 * @param	wcf\system\comment\manager\ICommentManager	$commentManager
	 * @param	wcf\data\comment\Comment			$comment
	 */
	public function __construct(ICommentManager $commentManager, Comment $comment) {
		parent::__construct();
		
		$this->comment = $comment;
		$this->commentManager = $commentManager;
		
		$this->getConditionBuilder()->add("comment_response.commentID = ?", array($this->comment->commentID));
		$this->sqlLimit = $this->commentManager->getCommentsPerPage();
	}
	
	/**
	 * @see	wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		// get user ids
		$userIDs = array();
		foreach ($this->objects as &$response) {
			$userIDs[] = $response->userID;
			
			$response = new StructuredCommentResponse($response);
			$response->setIsDeletable($this->commentManager->canDeleteResponse($response->getDecoratedObject()));
			$response->setIsEditable($this->commentManager->canEditResponse($response->getDecoratedObject()));
		}
		unset($response);
		
		// fetch user data and avatars
		if (!empty($userIDs)) {
			$userIDs = array_unique($userIDs);
			
			$users = UserProfile::getUserProfiles($userIDs);
			foreach ($this->objects as $response) {
				if (isset($users[$response->userID])) {
					$response->setUserProfile($users[$response->userID]);
				}
			}
		}
	}
}
