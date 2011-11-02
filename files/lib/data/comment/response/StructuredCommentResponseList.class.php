<?php
namespace wcf\data\comment\response;
use wcf\data\user\UserProfile;


/**
 * Provides a structured comment response list.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment.response
 * @category 	Community Framework
 */
class StructuredCommentResponseList extends CommentResponseList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$sqlLimit
	 */
	public $sqlLimit = 20;
	
	/**
	 * @see	wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'comment_response.time DESC';
	
	/**
	 * Creates a new structured comment response list.
	 * 
	 * @param	integer		$commentID
	 */
	public function __construct($commentID) {
		parent::__construct();
		
		$this->getConditionBuilder()->add("comment_response.commentID = ?", array($commentID));
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
