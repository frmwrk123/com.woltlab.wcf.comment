<?php
namespace wcf\system\comment\manager;
use wcf\data\DatabaseObject;
use wcf\system\event\EventHandler;

/**
 * Default implementation for comment managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.comment.manager
 * @category 	Community Framework
 */
abstract class AbstractCommentManager implements ICommentManager {
	/**
	 * set to true to allow creation of comments or responses
	 * @var	boolean
	 */	
	protected $canAdd = false;
	
	/**
	 * set to true to allow edit of a specific comment or response
	 * @var	boolean
	 */
	protected $canEdit = false;
	
	/**
	 * display comments per page on init
	 * @var	integer
	 */
	protected $commentsPerPage = 10;
	
	/**
	 * target object
	 * @var	wcf\data\DatabaseObject
	 */
	protected $object = null;
	
	/**
	 * @see	wcf\system\comment\manager\ICommentManager::__construct()
	 */
	public final function __construct(DatabaseObject $object = null) {
		$this->object = $object;
		$this->setOptions();
		
		EventHandler::getInstance()->fireAction($this, 'didInit');
	}
	
	/**
	 * Should be used to set options and validate permissions.
	 * 
	 * @see	wcf\system\comment\manager\AbstractCommentManager::__construct()
	 */
	abstract protected function setOptions();
	
	/**
	 * @see	wcf\system\comment\manager\ICommentManager::canAdd()
	 */
	public final function canAdd() {
		return $this->canAdd;
	}
	
	/**
	 * @see	wcf\system\comment\manager\ICommentManager::canEdit()
	 */
	public function canEdit($userID, $time) {
		EventHandler::getInstance()->fireAction($this, 'canEdit');
		
		return $this->canEdit;
	}
	
	/**
	 * @see	wcf\system\comment\manager\ICommentManager::commentsPerPage()
	 */
	public final function commentsPerPage() {
		return $this->commentsPerPage;
	}
}
