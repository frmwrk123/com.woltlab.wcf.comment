<?php
namespace wcf\data\comment;
use wcf\data\like\object\AbstractLikeObject;

/**
 * Likeable object implementation for comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment
 * @category	Community Framework
 */
class LikeableComment extends AbstractLikeObject {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\comment\Comment';
	
	/**
	 * @see	wcf\data\like\object\ILikeObject::getTitle()
	 */
	public function getTitle() {
		return $this->message;
	}
	
	/**
	 * @see	wcf\data\like\object\ILikeObject::getURL()
	 */
	public function getURL() {
		return '';
	}
	
	/**
	 * @see	wcf\data\like\object\ILikeObject::getUserID()
	 */
	public function getUserID() {
		return $this->userID;
	}
}
