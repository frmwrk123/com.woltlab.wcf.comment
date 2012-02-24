<?php
namespace wcf\system\comment\manager;
use wcf\data\DatabaseObject;

/**
 * Default interface for comment managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.comment.manager
 * @category 	Community Framework
 */
interface ICommentManager {
	/**
	 * Returns true, if current user may add comments.
	 * 
	 * @param	integer		$objectID
	 * @return	boolean
	 */
	public function canAdd($objectID);
	
	/**
	 * Returns true, if current user may delete the comment / response.
	 * 
	 * @param	integer		$objectID
	 * @param	integer		$commentID
	 * @param	integer		$responseID
	 * @return	boolean
	 */
	public function canDelete($objectID, $commentID = null, $responseID = null);
	
	/**
	 * Returns true, if current user may delete edit comment / response.
	 *
	 * @param	integer		$objectID
	 * @param	integer		$commentID
	 * @param	integer		$responseID
	 * @return	boolean
	 */
	public function canEdit($objectID, $commentID = null, $responseID = null);
	
	/**
	 * Returns the amount of comments per page.
	 * 
	 * @return	boolean
	 */
	public function commentsPerPage();
}
