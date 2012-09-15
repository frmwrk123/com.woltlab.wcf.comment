<?php
namespace wcf\data\comment\response;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides methods to handle response data.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.comment
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class StructuredCommentResponse extends DatabaseObjectDecorator {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	public static $baseClass = 'wcf\data\comment\response\CommentResponse';
	
	/**
	 * editable for current user
	 * @var	boolean
	 */
	public $editable = false;
	
	/**
	 * user profile object
	 * @var	wcf\data\user\UserProfile
	 */
	public $userProfile = null;
	
	/**
	 * Sets the user's profile.
	 * 
	 * @param	wcf\data\user\UserProfile	$userProfile
	 */
	public function setUserProfile(UserProfile $userProfile) {
		$this->userProfile = $userProfile;
	}
	
	/**
	 * Returns the user's profile.
	 * 
	 * @return	wcf\data\user\UserProfile
	 */
	public function getUserProfile() {
		return $this->userProfile;
	}
	
	/**
	 * Returns a structured response.
	 * 
	 * @param	integer		$responseID
	 * @return	wcf\data\comment\response\StructuredCommentResponse
	 */
	public static function getResponse($responseID) {
		$response = new CommentResponse($responseID);
		if (!$response->responseID) {
			return null;
		}
		
		// prepare structured response
		$response = new StructuredCommentResponse($response);
		
		// add user profile
		$userProfile = UserProfile::getUserProfile($response->userID);
		$response->setUserProfile($userProfile);
		
		return $response;
	}
	
	/**
	 * Sets editable state.
	 *
	 * @param	boolean		$editable
	 */
	public function setIsEditable($editable) {
		$this->editable = $editable;
	}
	
	/**
	 * Returns true, if response is editable by current user.
	 *
	 * @return	boolean
	 */
	public function isEditable() {
		return $this->editable;
	}
}
