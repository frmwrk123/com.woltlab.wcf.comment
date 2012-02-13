<?php
namespace wcf\system\comment\manager;
use wcf\data\DatabaseObject;

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
	 * Initializes the comment manager.
	 * 
	 * @param	wcf\data\DatabaseObject		$object
	 */
	public function __construct(DatabaseObject $object = null);
	
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
