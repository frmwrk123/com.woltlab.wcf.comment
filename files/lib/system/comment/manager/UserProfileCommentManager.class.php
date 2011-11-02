<?php
namespace wcf\system\comment\manager;

/**
 * User profile comment manager implementation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.comment.manager
 * @category 	Community Framework
 */
class UserProfileCommentManager extends AbstractCommentManager {
	/**
	 * @see	wcf\system\comment\manager\AbstractCommentManager::setOptions()
	 */
	protected function setOptions() {
		$this->canAdd = true;
	}
	
	/**
	 * @see	wcf\system\comment\manager\ICommentManager::canEdit()
	 */
	public function canEdit($userID, $time) {
		parent::canEdit($userID, $time);
		
		return true;
	}
}
