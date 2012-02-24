<?php
namespace wcf\system\comment\manager;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\Comment;
use wcf\data\DatabaseObject;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default implementation for comment managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.comment.manager
 * @category 	Community Framework
 */
abstract class AbstractCommentManager extends SingletonFactory implements ICommentManager {
	/**
	 * current user can add comments
	 * @var	boolean
	 */
	public $canAdd = false;
	
	/**
	 * current user can delete comments
	 * @var	boolean
	 */
	public $canDelete = false;
	
	/**
	 * current user can edit comments
	 * @var	boolean
	 */
	public $canEdit = false;
	
	/**
	 * display comments per page on init
	 * @var	integer
	 */
	public $commentsPerPage = 10;
	
	/**
	 * comment id
	 * @var	integer
	 */
	public $commentID = 0;
	
	/**
	 * object id
	 * @var	integer
	 */
	public $objectID = 0;
	
	/**
	 * comment response id
	 * @var	integer
	 */
	public $responseID = 0;
	
	/**
	 * @see wcf\system\comment\manager\ICommentManager::canAdd()
	 */
	public function canAdd($objectID) {
		$this->objectID = $objectID;
		
		return false;
	}
	
	/**
	 * @see wcf\system\comment\manager\ICommentManager::canDelete()
	 */
	public function canDelete($objectID, $commentID = null, $responseID = null) {
		if (!$this->canDelete) {
			return false;
		}
		
		// store ids
		$this->objectID = $objectID;
		$this->commentID = $commentID;
		$this->responseID = $responseID;
	
		// check ownership
		if (!$this->checkOwnership('delete')) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @see wcf\system\comment\manager\ICommentManager::canEdit()
	 */
	public function canEdit($objectID, $commentID = null, $responseID = null) {
		if (!$this->canEdit) {
			return false;
		}
		
		// store ids
		$this->objectID = $objectID;
		$this->commentID = $commentID;
		$this->responseID = $responseID;
		
		// check ownership
		if (!$this->checkOwnership('edit')) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validates ownership.
	 *
	 * @param	string		$action
	 * @return	boolean
	 */
	protected function checkOwnership($action) {
		if ($this->commentID) {
			$comment = new Comment($this->commentID);
			if (!$this->validateObject($comment, 'commentID', $action)) {
				return false;
			}
		}
		else if ($this->responseID) {
			$response = new CommentResponse($this->responseID);
			if (!$this->validateObject($response, 'responseID', $action)) {
				return false;
			}
		}
		else {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validates object access.
	 *
	 * @param	wcf\data\DatabaseObject	$object
	 * @param	string			$field
	 * @param	string			$action
	 * @return	boolean
	 */
	protected function validateObject(DatabaseObject $object, $field, $action) {
		if (!$object->$field) {
			return false;
		}
		
		if ($object->userID != WCF::getUser()->userID) {
			if ($this->override($action)) {
				return true;
			}
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns true, if you want to override previous validation.
	 * 
	 * @param	string		$action
	 * @return	boolean
	 */
	protected function override($action) {
		return false;
	}
	
	/**
	 * @see	wcf\system\comment\manager\ICommentManager::commentsPerPage()
	 */
	public function commentsPerPage() {
		return $this->commentsPerPage;
	}
}
