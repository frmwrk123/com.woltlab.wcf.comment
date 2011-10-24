<?php
namespace wcf\data\comment\response;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes comment response-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment.response
 * @category 	Community Framework
 */
class CommentResponseAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\comment\response\CommentResponseEditor';
}
