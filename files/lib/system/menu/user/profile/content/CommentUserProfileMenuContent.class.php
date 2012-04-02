<?php
namespace wcf\system\menu\user\profile\content;
use wcf\system\comment\CommentHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user profile comment content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.menu.user.profile.content
 * @category 	Community Framework
 */
class CommentUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
	/**
	 * comment manager object
	 * @var	wcf\system\comment\manager\ICommentManager
	 */
	public $commentManager = null;
	
	/**
	 * object type id
	 * @var	integer
	 */
	public $objectTypeID = 0;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->objectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user.profileComment');
		$objectType = CommentHandler::getInstance()->getObjectType($this->objectTypeID);
		$this->commentManager = $objectType->getProcessor();
	}
	
	/**
	 * @see	wcf\system\menu\user\profile\content\IUserProfileMenuContent::getContent()
	 */
	public function getContent($userID) {
		$commentList = CommentHandler::getInstance()->getCommentList($this->objectTypeID, $this->commentManager, $userID);
		
		// assign variables
		WCF::getTPL()->assign(array(
			'commentCanAdd' => $this->commentManager->canAdd($userID),
			'commentsPerPage' => $this->commentManager->commentsPerPage(),
			'commentList' => $commentList,
			'commentObjectTypeID' => $this->objectTypeID,
			'userID' => $userID,
			'likeData' => (MODULE_LIKE ? $commentList->getLikeData() : array())
		));
		
		return WCF::getTPL()->fetch('userProfileCommentList');
	}
}
