<?php
namespace wcf\data\comment;
use wcf\data\object\type\AbstractObjectTypeProvider;

/**
 * Object type provider for comments
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment
 * @category 	Community Framework
 */
class LikeableCommentProvider extends AbstractObjectTypeProvider {
	/**
	 * @see	wcf\data\object\type\AbstractObjectTypeProvider::$className
	 */
	public $className = 'wcf\data\comment\Comment';
	
	/**
	 * @see	wcf\data\object\type\AbstractObjectTypeProvider::$listClassName
	 */
	public $listClassName = 'wcf\data\comment\CommentList';
	
	/**
	 * @see	wcf\data\object\type\IObjectTypeProvider::getObjectByID()
	 */
	public function getObjectByID($objectID) {
		$object = parent::getObjectByID($objectID);
		
		return new LikeableComment($object);
	}
	
	/**
	 * @see	wcf\data\object\type\IObjectTypeProvider::getObjectsByIDs()
	 */
	public function getObjectsByIDs(array $objectIDs) {
		$objects = parent::getObjectsByIDs($objectIDs);
		foreach ($objects as &$object) {
			$object = new LikeableComment($object);
		}
		
		return $objects;
	}
}
