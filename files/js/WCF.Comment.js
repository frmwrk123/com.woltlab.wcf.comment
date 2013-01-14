/**
 * Namespace for comments
 */
WCF.Comment = {};

/**
 * Comment support for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Comment.Handler = Class.extend({
	/**
	 * input element to add a comment
	 * @var	jQuery
	 */
	_commentAdd: null,
	
	/**
	 * list of comment objects
	 * @var	object
	 */
	_comments: { },
	
	/**
	 * comment container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * container id
	 * @var	string
	 */
	_containerID: '',
	
	/**
	 * number of currently displayed comments
	 * @var	integer
	 */
	_displayedComments: 0,
	
	/**
	 * button to load next comments
	 * @var	jQuery
	 */
	_loadNextComments: null,
	
	/**
	 * buttons to load next responses per comment
	 * @var	object
	 */
	_loadNextResponses: { },
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of response objects
	 * @var	object
	 */
	_responses: { },
	
	/**
	 * user's avatar
	 * @var	string
	 */
	_userAvatar: '',
	
	/**
	 * Initializes the WCF.Comment.Handler class.
	 * 
	 * @param	string		containerID
	 * @param	string		userAvatar
	 */
	init: function(containerID, userAvatar) {
		this._commentAdd = null;
		this._comments = { };
		this._containerID = containerID;
		this._displayedComments = 0;
		this._loadNextComments = null;
		this._loadNextResponses = { };
		this._responses = { };
		this._userAvatar = userAvatar;
		
		this._container = $('#' + $.wcfEscapeID(this._containerID));
		if (!this._container.length) {
			console.debug("[WCF.Comment.Handler] Unable to find container identified by '" + this._containerID + "'");
		}
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		WCF.DOMNodeInsertedHandler.enable();
		
		this._initComments();
		this._initResponses();
		
		// add new comment
		if (this._container.data('canAdd')) {
			this._initAddComment();
		}
		
		WCF.DOMNodeInsertedHandler.disable();
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Comment.Handler', $.proxy(this._domNodeInserted, this));
	},
	
	/**
	 * Shows a button to load next comments.
	 */
	_handleLoadNextComments: function() {
		if (this._displayedComments < this._container.data('comments')) {
			if (this._loadNextComments === null) {
				this._loadNextComments = $('<li class="commentLoadNext"><button class="buttonPrimary small">' + WCF.Language.get('wcf.comment.more') + '</button></li>').appendTo(this._container);
				this._loadNextComments.children('button').click($.proxy(this._loadComments, this));
			}
			
			this._loadNextComments.children('button').enable();
		}
		else if (this._loadNextComments !== null) {
			this._loadNextComments.hide();
		}
	},
	
	/**
	 * Shows a button to load next responses per comment.
	 * 
	 * @param	integer		commentID
	 */
	_handleLoadNextResponses: function(commentID) {
		var $comment = this._comments[commentID];
		$comment.data('displayedResponses', $comment.find('ul.commentResponseList > li').length);
		
		if ($comment.data('displayedResponses') < $comment.data('responses')) {
			if (this._loadNextResponses[commentID] === undefined) {
				this._loadNextResponses[commentID] = $('<div class="responseLoadNext"><button class="small">' + WCF.Language.get('wcf.comment.response.more') + '</button></div>').insertAfter($comment.find('ul.commentResponseList'));
				this._loadNextResponses[commentID].children('button').data('commentID', commentID).click($.proxy(this._loadResponses, this));
			}
			
			this._loadNextResponses[commentID].children('button').enable();
		}
		else if (this._loadNextResponses[commentID] !== undefined) {
			this._loadNextResponses[commentID].hide();
		}
	},
	
	/**
	 * Loads next comments.
	 */
	_loadComments: function() {
		this._loadNextComments.children('button').disable();
		
		this._proxy.setOption('data', {
			actionName: 'loadComments',
			className: 'wcf\\data\\comment\\CommentAction',
			parameters: {
				data: {
					objectID: this._container.data('objectID'),
					objectTypeID: this._container.data('objectTypeID'),
					lastCommentTime: this._container.data('lastCommentTime')
				}
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Loads next responses for given comment.
	 * 
	 * @param	object		event
	 */
	_loadResponses: function(event) {
		var $button = $(event.currentTarget).disable();
		var $commentID = $button.data('commentID');
		
		this._proxy.setOption('data', {
			actionName: 'loadResponses',
			className: 'wcf\\data\\comment\\response\\CommentResponseAction',
			parameters: {
				data: {
					commentID: $commentID,
					lastResponseTime: this._comments[$commentID].data('lastResponseTime')
				}
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Handles DOMNodeInserted events.
	 */
	_domNodeInserted: function() {
		this._initComments();
		this._initResponses();
	},
	
	/**
	 * Initializes available comments.
	 */
	_initComments: function() {
		var self = this;
		var $loadedComments = false;
		this._container.find('.jsComment').each(function(index, comment) {
			var $comment = $(comment).removeClass('jsComment');
			var $commentID = $comment.data('commentID');
			self._comments[$commentID] = $comment;
			
			self._initComment($commentID, $comment);
			self._displayedComments++;
			
			$loadedComments = true;
			self._handleLoadNextResponses($commentID);
		});
		
		if ($loadedComments) {
			this._handleLoadNextComments();
		}
	},
	
	/**
	 * Initializes a specific comment.
	 * 
	 * @param	integer		commentID
	 * @param	jQuery		comment
	 */
	_initComment: function(commentID, comment) {
		if (this._container.data('canAdd')) {
			this._initAddResponse(commentID, comment);
		}
		
		if (comment.data('canEdit')) {
			var $editButton = $('<li><a class="jsTooltip" title="' + WCF.Language.get('wcf.global.button.edit') + '"><img src="' + WCF.Icon.get('wcf.icon.edit') + '" alt="" class="icon16" /></a></li>');
			$editButton.data('commentID', commentID).appendTo(comment.find('ul.commentOptions:eq(0)')).click($.proxy(this._prepareEdit, this));
		}
		
		if (comment.data('canDelete')) {
			var $deleteButton = $('<li><a class="jsTooltip" title="' + WCF.Language.get('wcf.global.button.delete') + '"><img src="' + WCF.Icon.get('wcf.icon.delete') + '" alt="" class="icon16" /></a></li>');
			$deleteButton.data('commentID', commentID).appendTo(comment.find('ul.commentOptions:eq(0)')).click($.proxy(this._delete, this));
		}
	},
	
	/**
	 * Initializes available responses.
	 */
	_initResponses: function() {
		var self = this;
		this._container.find('.jsCommentResponse').each(function(index, response) {
			var $response = $(response).removeClass('jsCommentResponse');
			var $responseID = $response.data('responseID');
			self._responses[$responseID] = $response;
			
			self._initResponse($responseID, $response);
			
			$loadedResponses = true;
		});
	},
	
	/**
	 * Initializes a specific response.
	 * 
	 * @param	integer		responseID
	 * @param	jQuery		response
	 */
	_initResponse: function(responseID, response) {
		if (response.data('canEdit')) {
			var $editButton = $('<li><a class="jsTooltip" title="' + WCF.Language.get('wcf.global.button.edit') + '"><img src="' + WCF.Icon.get('wcf.icon.edit') + '" alt="" class="icon16" /></a></li>');
			
			var self = this;
			$editButton.data('responseID', responseID).appendTo(response.find('ul.commentOptions:eq(0)')).click(function(event) { self._prepareEdit(event, true); });
		}
		
		if (response.data('canDelete')) {
			var $deleteButton = $('<li><a class="jsTooltip" title="' + WCF.Language.get('wcf.global.button.delete') + '"><img src="' + WCF.Icon.get('wcf.icon.delete') + '" alt="" class="icon16" /></a></li>');
			
			var self = this;
			$deleteButton.data('responseID', responseID).appendTo(response.find('ul.commentOptions:eq(0)')).click(function(event) { self._delete(event, true); });
		}
	},
	
	/**
	 * Initializes the UI components to add a comment.
	 */
	_initAddComment: function() {
		// create UI
		this._commentAdd = $('<li class="box32 jsCommentAdd"><span class="framed">' + this._userAvatar + '</span><div /></li>').prependTo(this._container);
		var $inputContainer = this._commentAdd.children('div');
		var $input = $('<input type="text" placeholder="' + WCF.Language.get('wcf.comment.add') + '" class="long" />').appendTo($inputContainer);
		$('<small>' + WCF.Language.get('wcf.comment.description') + '</small>').appendTo($inputContainer);
		
		$input.keyup($.proxy(this._keyUp, this));
	},
	
	/**
	 * Initializes the UI elements to add a response.
	 * 
	 * @param	integer		commentID
	 * @param	jQuery		comment
	 */
	_initAddResponse: function(commentID, comment) {
		var $placeholder = $('<div class="commentResponseAdd jsCommentResponseAddPlaceholder"><a>' + WCF.Language.get('wcf.comment.button.response.add') + '</a></div>').insertBefore(comment.find('ul.commentResponseList'));
		$placeholder.data('commentID', commentID).click($.proxy(this._showAddResponse, this));
		
		var $listItem = $('<div class="box32 commentResponseAdd jsCommentResponseAdd"><span class="framed">' + this._userAvatar + '</span><div /></div>').hide().insertAfter($placeholder);
		var $inputContainer = $listItem.children('div');
		var $input = $('<input type="text" placeholder="' + WCF.Language.get('wcf.comment.response.add') + '" class="long" />').data('commentID', commentID).appendTo($inputContainer);
		$('<small>' + WCF.Language.get('wcf.comment.description') + '</small>').appendTo($inputContainer);
		
		var self = this;
		$input.keyup(function(event) { self._keyUp(event, true); }).blur($.proxy(this._hideAddResponse, this));
		
		comment.data('responsePlaceholder', $placeholder).data('responseInput', $listItem);
	},
	
	/**
	 * Prepares editing of a comment or response.
	 * 
	 * @param	object		event
	 * @param	boolean		isResponse
	 */
	_prepareEdit: function(event, isResponse) {
		var $button = $(event.currentTarget);
		var $data = {
			objectID: this._container.data('objectID'),
			objectTypeID: this._container.data('objectTypeID')
		};
		
		if (isResponse === true) {
			$data.responseID = $button.data('responseID');
		}
		else {
			$data.commentID = $button.data('commentID');
		}
		
		this._proxy.setOption('data', {
			actionName: 'prepareEdit',
			className: 'wcf\\data\\comment\\CommentAction',
			parameters: {
				data: $data
			}
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Displays the UI elements to create a response.
	 * 
	 * @param	object		event
	 */
	_showAddResponse: function(event) {
		var $commentID = $(event.currentTarget).data('commentID');
		this._comments[$commentID].data('responsePlaceholder').hide();
		
		var $responseInput = this._comments[$commentID].data('responseInput').show();
		$responseInput.find('input').focus();
	},
	
	/**
	 * Hides the UI elements to create a response.
	 * 
	 * @param	object		event
	 */
	_hideAddResponse: function(event) {
		var $input = $(event.currentTarget);
		if ($.trim($input.val()) !== '') {
			return;
		}
		
		// delay execution by 50ms
		var self = this;
		new WCF.PeriodicalExecuter(function(pe) {
			pe.stop();
			
			self._comments[$input.data('commentID')].data('responsePlaceholder').show();
			
			var $responseInput = self._comments[$input.data('commentID')].data('responseInput');
			$responseInput.hide().find('input').val('');
		}, 50);
	},
	
	/**
	 * Handles the keyup event for comments and responses.
	 * 
	 * @param	object		event
	 * @param	boolean		isResponse
	 */
	_keyUp: function(event, isResponse) {
		// ignore every key except for [Enter] and [Esc]
		if (event.which !== 13 && event.which !== 27) {
			return;
		}
		
		var $input = $(event.currentTarget);
		
		// cancel input
		if (event.which === 27) {
			$input.val('').trigger('blur', event);
			return;
		}
		
		var $value = $.trim($input.val());
		
		// ignore empty comments
		if ($value == '') {
			return;
		}
		
		var $actionName = 'addComment';
		var $data = {
			message: $value,
			objectID: this._container.data('objectID'),
			objectTypeID: this._container.data('objectTypeID')
		};
		if (isResponse === true) {
			$actionName = 'addResponse';
			$data.commentID = $input.data('commentID');
		}
		
		this._proxy.setOption('data', {
			actionName: $actionName,
			className: 'wcf\\data\\comment\\CommentAction',
			parameters: {
				data: $data
			}
		});
		this._proxy.sendRequest();
		
		// reset input
		$input.val('').blur();
	},
	
	/**
	 * Shows a confirmation message prior to comment or response deletion.
	 * 
	 * @param	object		event
	 * @param	boolean		isResponse
	 */
	_delete: function(event, isResponse) {
		WCF.System.Confirmation.show(WCF.Language.get('wcf.comment.delete.confirmMessage'), $.proxy(function(action) {
			if (action === 'confirm') {
				var $data = {
					objectID: this._container.data('objectID'),
					objectTypeID: this._container.data('objectTypeID')
				};
				if (isResponse !== true) {
					$data.commentID = $(event.currentTarget).data('commentID');
				}
				else {
					$data.responseID = $(event.currentTarget).data('responseID');
				}
				
				this._proxy.setOption('data', {
					actionName: 'remove',
					className: 'wcf\\data\\comment\\CommentAction',
					parameters: {
						data: $data
					}
				});
				this._proxy.sendRequest();
			}
		}, this));
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		switch (data.actionName) {
			case 'addComment':
				$(data.returnValues.template).insertAfter(this._commentAdd).wcfFadeIn();
			break;
			
			case 'addResponse':
				$(data.returnValues.template).prependTo(this._comments[data.returnValues.commentID].find('ul.commentResponseList')).wcfFadeIn();
			break;
			
			case 'edit':
				this._update(data);
			break;
			
			case 'loadComments':
				this._insertComments(data);
			break;
			
			case 'loadResponses':
				this._insertResponses(data);
			break;
			
			case 'prepareEdit':
				this._edit(data);
			break;
			
			case 'remove':
				this._remove(data);
			break;
		}
	},
	
	/**
	 * Inserts previously loaded comments.
	 * 
	 * @param	object		data
	 */
	_insertComments: function(data) {
		// insert comments
		$(data.returnValues.template).insertBefore(this._loadNextComments);
		
		// update time of last comment
		this._container.data('lastCommentTime', data.returnValues.lastCommentTime);
	},
	
	/**
	 * Inserts previously loaded responses.
	 * 
	 * @param	object		data
	 */
	_insertResponses: function(data) {
		var $comment = this._comments[data.returnValues.commentID];
		
		// insert responses
		$(data.returnValues.template).appendTo($comment.find('ul.commentResponseList'));
		
		// update time of last response
		$comment.data('lastResponseTime', data.returnValues.lastResponseTime);
		
		// update button state to load next responses
		this._handleLoadNextResponses(data.returnValues.commentID);
	},
	
	/**
	 * Removes a comment or response from list.
	 * 
	 * @param	object		data
	 */
	_remove: function(data) {
		if (data.returnValues.commentID) {
			this._comments[data.returnValues.commentID].remove();
			delete this._comments[data.returnValues.commentID];
		}
		else {
			this._responses[data.returnValues.responseID].remove();
			delete this._responses[data.returnValues.responseID];
		}
	},
	
	/**
	 * Prepares editing of a comment or response.
	 * 
	 * @param	object		data
	 */
	_edit: function(data) {
		if (data.returnValues.commentID) {
			var $content = this._comments[data.returnValues.commentID].find('.commentContent:eq(0) .userMessage:eq(0)');
		}
		else {
			var $content = this._responses[data.returnValues.responseID].find('.commentContent:eq(0) .userMessage:eq(0)');
		}
		
		// replace content with input field
		$content.html($.proxy(function(index, oldHTML) {
			var $input = $('<input type="text" value="' + data.returnValues.message + '" class="long" /><small>' + WCF.Language.get('wcf.comment.description') + '</small>');
			$input.data('__html', oldHTML).keyup($.proxy(this._saveEdit, this));
			
			if (data.returnValues.commentID) {
				$input.data('commentID', data.returnValues.commentID);
			}
			else {
				$input.data('responseID', data.returnValues.responseID);
			}
			
			return $input;
		}, this));
		$content.children('input').focus();
		
		// hide elements
		$content.parent().find('hgroup:eq(0)').hide();
		$content.parent().find('.commentOptions:eq(0)').hide();
	},
	
	/**
	 * Updates a comment or response.
	 * 
	 * @param	object		data
	 */
	_update: function(data) {
		if (data.returnValues.commentID) {
			var $input = this._comments[data.returnValues.commentID].find('.commentContent:eq(0) .userMessage:eq(0) > input');
		}
		else {
			var $input = this._responses[data.returnValues.responseID].find('.commentContent:eq(0) .userMessage:eq(0) > input');
		}
		
		$input.data('__html', data.returnValues.message);
		
		this._cancelEdit($input);
	},
	
	/**
	 * Saves editing of a comment or response.
	 * 
	 * @param	object		event
	 */
	_saveEdit: function(event) {
		var $input = $(event.currentTarget);
		
		// abort with [Esc]
		if (event.which === 27) {
			this._cancelEdit($input);
			return;
		}
		else if (event.which !== 13) {
			// ignore everything except for [Enter]
			return;
		}
		
		var $message = $.trim($input.val());
		
		// ignore empty message
		if ($message === '') {
			return;
		}
		
		var $data = {
			message: $message,
			objectID: this._container.data('objectID'),
			objectTypeID: this._container.data('objectTypeID')
		};
		if ($input.data('commentID')) {
			$data.commentID = $input.data('commentID');
		}
		else {
			$data.responseID = $input.data('responseID');
		}
		
		this._proxy.setOption('data', {
			actionName: 'edit',
			className: 'wcf\\data\\comment\\CommentAction',
			parameters: {
				data: $data
			}
		});
		this._proxy.sendRequest()
	},
	
	/**
	 * Cancels editing of a comment or response.
	 * 
	 * @param	jQuery		input
	 */
	_cancelEdit: function(input) {
		// restore elements
		input.parent().prev('hgroup:eq(0)').show();
		input.parent().next('.commentOptions:eq(0)').show();
		
		// restore HTML
		input.parent().html(input.data('__html'));
	}
});

/**
 * Like support for comments
 * 
 * @see	WCF.Like
 */
WCF.Comment.Like = WCF.Like.extend({
	/**
	 * @see	WCF.Like._getContainers()
	 */
	_getContainers: function() {
		return $('.commentList > li.comment');
	},
	
	/**
	 * @see	WCF.Like._getObjectID()
	 */
	_getObjectID: function(containerID) {
		return this._containers[containerID].data('commentID');
	},
	
	/**
	 * @see	WCF.Like._buildWidget()
	 */
	_buildWidget: function(containerID, likeButton, dislikeButton, badge, summary) {
		this._containers[containerID].find('hgroup:eq(0) > h1').append(badge);
		
		if (this._canLike) {
			dislikeButton.appendTo(this._containers[containerID].find('.commentOptions:eq(0)'));
			likeButton.appendTo(this._containers[containerID].find('.commentOptions:eq(0)'));
		}
	},
	
	/**
	 * @see	WCF.Like._getWidgetContainer()
	 */
	_getWidgetContainer: function(containerID) {},
	
	/**
	 * @see	WCF.Like._addWidget()
	 */
	_addWidget: function(containerID, widget) {}
});