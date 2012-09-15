<?php
namespace wcf\data\comment\response;
use wcf\data\DatabaseObject;
use wcf\util\StringUtil;

/**
 * Represents a comment response.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class CommentResponse extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'comment_response';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'responseID';
	
	/**
	 * Returns a formatted message.
	 * 
	 * @return	string
	 */
	public function getFormattedMessage() {
		return StringUtil::encodeHTML($this->message);
	}
}
