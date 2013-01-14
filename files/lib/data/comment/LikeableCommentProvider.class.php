<?php
namespace wcf\data\comment;
use wcf\data\object\type\AbstractObjectTypeProvider;

/**
 * Object type provider for comments
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment
 * @category	Community Framework
 */
class LikeableCommentProvider extends AbstractObjectTypeProvider {
	/**
	 * @see	wcf\data\object\type\AbstractObjectTypeProvider::$className
	 */
	public $className = 'wcf\data\comment\Comment';
	
	/**
	 * @see	wcf\data\object\type\AbstractObjectTypeProvider::$decoratorClassName
	 */
	public $decoratorClassName = 'wcf\data\comment\LikeableComment';
	
	/**
	 * @see	wcf\data\object\type\AbstractObjectTypeProvider::$listClassName
	 */
	public $listClassName = 'wcf\data\comment\CommentList';
}
