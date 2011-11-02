<?php
namespace wcf\data\comment;
use wcf\data\DatabaseObject;

/**
 * Represents a comment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment
 * @category 	Community Framework
 */
class Comment extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'comment';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'commentID';
	
	/**
	 * Returns a list of last response ids.
	 * 
	 * @return array<integer>
	 */
	public function getLastResponseIDs() {
		if ($this->lastResponseIDs === null || $this->lastResponseIDs == '') {
			return array();
		}
		
		$lastResponseIDs = @unserialize($this->lastResponseIDs);
		if ($lastResponseIDs === false) {
			return array();
		}
		
		return $lastResponseIDs;
	}
}
