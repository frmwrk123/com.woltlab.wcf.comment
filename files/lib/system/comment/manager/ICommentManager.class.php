<?php
namespace wcf\system\comment\manager;

/**
 * Default interface for comment managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.comment.manager
 * @category 	Community Framework
 */
interface ICommentManager {
	/**
	 * Returns true, if current user may add comments or responses.
	 * 
	 * @return	boolean
	 */
	public function canAdd();
	
	/**
	 * Returns true, if current user may edit the comment or response.
	 * 
	 * @param	integer		$userID
	 * @param	integer		$time
	 * @return	boolean
	 */
	public function canEdit($userID, $time);
	
	/**
	 * Returns the amount of comments per page.
	 * 
	 * @return	boolean
	 */
	public function commentsPerPage();
}
