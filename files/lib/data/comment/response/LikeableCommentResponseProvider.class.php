<?php
namespace wcf\data\comment\response;
use wcf\data\object\type\AbstractObjectTypeProvider;

/**
 * Object type provider for likeable comment responses.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class LikeableCommentResponseProvider extends AbstractObjectTypeProvider {
	/**
	 * @see	wcf\data\object\type\AbstractObjectTypeProvider::$className
	 */
	public $className = 'wcf\data\comment\response\CommentResponse';
	
	/**
	 * @see	wcf\data\object\type\AbstractObjectTypeProvider::$decoratorClassName
	 */
	public $decoratorClassName = 'wcf\data\comment\response\LikeableCommentResponse';
	
	/**
	 * @see	wcf\data\object\type\AbstractObjectTypeProvider::$listClassName
	 */
	public $listClassName = 'wcf\data\comment\response\CommentResponseList';
}
