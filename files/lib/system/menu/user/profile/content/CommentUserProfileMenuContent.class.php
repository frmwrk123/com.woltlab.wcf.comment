<?php
namespace wcf\system\menu\user\profile\content;

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
class CommentUserProfileMenuContent implements IUserProfileMenuContent {
	/**
	 * @see	wcf\system\menu\user\profile\content\IUserProfileMenuContent::getContent()
	 */
	public function getContent() {
		return 'IMPLEMENT ME: '.get_class($this);
	}
}
