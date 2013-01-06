<?php
namespace wcf\system\comment;
use wcf\data\comment\StructuredCommentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Provides methods for comment object handling.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	system.comment
 * @category	Community Framework
 */
class CommentHandler extends SingletonFactory {
	/**
	 * cached object types
	 * @var	array<array>
	 */
	protected $cache = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->cache = array(
			'objectTypes' => array(),
			'objectTypeIDs' => array()
		);
		
		$cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.comment.commentableContent');
		foreach ($cache as $objectType) {
			$this->cache['objectTypes'][$objectType->objectTypeID] = $objectType;
			$this->cache['objectTypeIDs'][$objectType->objectType] = $objectType->objectTypeID;
		}
	}
	
	/**
	 * Returns the object type id for a given object type.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($objectType) {
		if (isset($this->cache['objectTypeIDs'][$objectType])) {
			return $this->cache['objectTypeIDs'][$objectType];
		}
		
		return null;
	}
	
	/**
	 * Returns the object type for a given object type id.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->cache['objectTypes'][$objectTypeID])) {
			return $this->cache['objectTypes'][$objectTypeID];
		}
		
		return null;
	}
	
	/**
	 * Returns comment manager object for given object type.
	 * 
	 * @param	string		$objectType
	 * @return	wcf\system\comment\manager\ICommentManager
	 */
	public function getCommentManager($objectType) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		if ($objectTypeID === null) {
			throw new SystemException("Unable to find object type for '".$objectType."'");
		}
		
		return $this->getObjectType($objectTypeID)->getProcessor();
		
	}
	
	/**
	 * Returns a comment list for a given object type and object id.
	 * 
	 * @param	wcf\data\comment\manager\ICommentManager	$commentManager
	 * @param	integer						$objectTypeID
	 * @param	integer						$objectID
	 * @param	boolean						$readObjects
	 * @return	wcf\data\comment\StructuredCommentList
	 */
	public function getCommentList(ICommentManager $commentManager, $objectTypeID, $objectID, $readObjects = true) {
		$commentList = new StructuredCommentList($commentManager, $objectTypeID, $objectID);
		if ($readObjects) {
			$commentList->readObjects();
		}
		
		return $commentList;
	}
}
