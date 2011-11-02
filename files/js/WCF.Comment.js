// DEBUG ONLY - REMOVE LATER
if (!WCF) var WCF = {};

/**
 * Namespace for comments
 */
WCF.Comment = {};

/**
 * Comment support for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Comment.Handler = function(userAvatar) { this.init(userAvatar); };
WCF.Comment.Handler.prototype = {
	/**
	 * list of comment containers
	 * @var	object
	 */
	_containers: { },

	/**
	 * current user's avatar
	 */
	_userAvatar: '',
	
	/**
	 * Initializes the comment handler.
	 * 
	 * @param	string		userAvatar
	 */
	init: function(userAvatar) {
		this._userAvatar = userAvatar;

		// init containers
		$('.commentList').each($.proxy(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();

			// store API
			$container.data('WCF-Comment-Handler-API', this);

			this._containers[$containerID] = $container;
			new WCF.Comment.Add($containerID, $container);
			new WCF.Comment.List($containerID, $container);
		}, this));
	},

	/**
	 * Returns a comment container identified by its id.
	 * 
	 * @param	string		containerID
	 * @return	jQuery
	 */
	getContainer: function(containerID) {
		if (this._containers[containerID]) {
			return this._containers[containerID];
		}

		return null;
	},

	/**
	 * Returns the user's avatar.
	 * 
	 * @return	string
	 */
	getUserAvatar: function() {
		return this._userAvatar;
	}
};

/**
 * Base implementation for all comment classes, providing a consistent API
 */
WCF.Comment.Base = Class.extend({
	/**
	 * associated container
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * container id
	 * @var	string
	 */
	_containerID: '',

	/**
	 * Initializes a new container-based object.
	 * 
	 * @param	integer		containerID
	 * @param	jQuery		container
	 */
	init: function(containerID, container) {
		this._containerID = containerID;
		this._container = container;

		this._init();
	},

	/**
	 * Empty method, will be called after class was initialized. You should
	 * override this method in your class.
	 */
	_init: function() { },

	/**
	 * Returns the associated container.
	 * 
	 * @return	jQuery
	 */
	getContainer: function() {
		return this._container;
	},

	/**
	 * Returns the container id.
	 * 
	 * @return	string
	 */
	getContainerID: function() {
		return this._containerID;
	}
});

/**
 * Provides an UI for adding comments.
 * 
 * @see	WCF.Comment.Base
 */
WCF.Comment.Add = WCF.Comment.Base.extend({
	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,

	/**
	 * @see	WCF.Comment.Base._init()
	 */
	_init: function() {
		// create UI
		var $listItem = $('<li><div><p>' + this.getUserAvatar() + '</p><div></div></div></li>');
		var $inputContainer = $listItem.find('div > div');
		var $input = $('<input type="text" placeholder="' + WCF.Language.get('wcf.comment.add') + '" />').addClass('long').appendTo($inputContainer);
		var $description = $('<small>' + WCF.Language.get('wcf.comment.description') + '</small>').hide().appendTo($inputContainer);

		$input.focus($.proxy(this._expandInput, this)).blur($.proxy(this._foldInput, this)).keyup($.proxy(this._addComment, this));
		$listItem.prependTo(this._container);

		// init proxy
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},

	/**
	 * Create a new comment if it not empty.
	 * 
	 * @param	object		event
	 */
	_addComment: function(event) {
		// ignore every key except for 13 = [Enter]
		if ((event.keyCode || event.which) != 13) {
			return;
		}

		var $input = $(event.target);
		var $value = $.trim($input.val());

		// ignore empty comments
		if ($value == '') {
			return;
		}
		
		this._proxy.setOption('data', {
			actionName: 'addComment',
			className: 'wcf\\data\\comment\\CommentAction',
			parameters: {
				data: {
					containerID: this._containerID,
					message: $value,
					objectID: this._container.data('objectID'),
					objectTypeID: this._container.data('objectTypeID')
				}
			}
		});
		this._proxy.sendRequest();

		// reset input
		$input.val('').blur();
	},

	/**
	 * Insert previously created comment.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $containerID = data.returnValues.containerID;
		if (this._containerID != $containerID) return;

		$('' + data.returnValues.template).insertAfter(this._container.children('li:eq(0)')).wcfBlindIn();
	},

	/**
	 * Expands container to display a description.
	 * 
	 * @param	object		event
	 */
	_expandInput: function(event) {
		$(event.target).next().show();
	},

	/**
	 * Folds container if input is empty, restores previous state.
	 * 
	 * @param	object		event
	 */
	_foldInput: function(event) {
		var $input = $(event.target);
		if ($input.val() == '') {
			$input.next().hide();
		}
	},

	/**
	 * Returns user's avatar.
	 * 
	 * @return	string
	 */
	getUserAvatar: function() {
		return this._container.data('WCF-Comment-Handler-API').getUserAvatar();
	}
});

/**
 * Manages comment lists.
 * 
 * @see	WCF.Comment.Base
 */
WCF.Comment.List = WCF.Comment.Base.extend({
	/**
	 * list of comments
	 * @var	object
	 */
	_comments: { },

	/**
	 * initialization status
	 * @var	boolean
	 */
	_didInit: false,

	/**
	 * @see	WCF.Comment.Base._init()
	 */
	_init: function() {
		this._container.children('li').each($.proxy(function(index, comment) {
			var $comment = $(comment);
			var $containerID = $comment.wcfIdentify();
			var $commentID = $comment.data('commentID') || null;

			if ($commentID !== null && !this._comments[$containerID]) {
				this._comments[$containerID] = $comment;

				new WCF.Comment.Editor($containerID, $comment);
				new WCF.Comment.Response.Add($containerID, $comment);
				new WCF.Comment.Response.List($containerID, $comment);
			}
		}, this));

		if (!this._didInit) {
			WCF.DOMNodeInsertedHandler.addCallback('WCF.Comment.List', $.proxy(this._domNodeInserted, this));
			this._didInit = true;
		}
	},

	/**
	 * Enables options and responses for dynamically inserted comments.
	 */
	_domNodeInserted: function() {
		this._init();
	}
});

/**
 * Provides edit access for comments.
 * 
 * @see	WCF.Comment.Base
 */
WCF.Comment.Editor = WCF.Comment.Base.extend({
	/**
	 * comment meta data
	 * @var	string
	 */
	_data: {
		edit: ''
	},

	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,

	/**
	 * @see	WCF.Comment.Base._init()
	 */
	_init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});

		this._insert();
	},

	/**
	 * Inserts an edit link.
	 */
	_insert: function() {
		var $optionList = this._container.find('ul.commentOptions:eq(0)');

		$('<li><a>' + WCF.Language.get('wcf.comment.edit') + '</a></li>').addClass('commentEdit').appendTo($optionList).click($.proxy(this._prepare, this));
	},

	/**
	 * Prepares editing by fetching raw message from server.
	 */
	_prepare: function() {
		var $data = {
			containerID: this._containerID,
			type: this._getType()
		};
		$data = this._addObjectID($data);
		
		// fetch raw message
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
	 * Prepares an item for edit or updates it.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $containerID = data.returnValues.containerID;
		if (this._containerID != $containerID) return;

		// determine action type
		switch (data.returnValues.action) {
			case 'prepare':
				this._beginEdit(data.returnValues.message);
			break;

			case 'saved':
				this._update(data.returnValues.message);
			break;
		}
	},

	/**
	 * Toggles UI to show an edit input.
	 * 
	 * @param	string		message
	 */
	_beginEdit: function(message) {
		var $content = this._container.find('div.commentContent:eq(0) > p');
		
		// replace content with input field
		$content.html($.proxy(function(index, oldhtml) {
			this._data.edit = oldhtml;
			var $input = $('<input type="text" value="' + message + '" class="long" /><small>' + WCF.Language.get('wcf.comment.edit.description') + '</small>').keydown($.proxy(this._keyDown, this)).keyup($.proxy(this._save, this));

			return $input;
		}, this));

		// hide options
		$content.next().hide();

		// set focus (not possible before returned above)
		$content.children('input').focus();
	},

	/**
	 * Cancels editing once the user pushes [Esc].
	 * 
	 * @param	object		event
	 */
	_keyDown: function(event) {
		// 27 = [Esc]
		if ((event.keyCode || event.which) != 27) {
			return;
		}

		this._cancelEdit($(event.target));
	},

	/**
	 * Cancels editing for request element.
	 * 
	 * @param	jQuery		input
	 */
	_cancelEdit: function(input) {
		// discard events
		input.unbind('keyup').unbind('keydown');

		// restore options
		input.parent().next().show();

		// restore html
		input.parent().html(this._data.edit);
	},

	/**
	 * Send a save request to server once editing is completed.
	 * 
	 * @param	object		event
	 */
	_save: function(event) {
		// 13 = [Enter]
		if ((event.keyCode || event.which) != 13) {
			return;
		}

		var $input = $(event.target);
		var $message = $.trim($input.val());

		// ignore empty message
		if ($message === '') {
			return;
		}

		var $data = {
			containerID: this._containerID,
			message: $message
		};
		$data = this._addObjectID($data);
		
		this._proxy.setOption('data', {
			actionName: 'edit',
			className: 'wcf\\data\\comment\\CommentAction',
			parameters: {
				data: $data
			}
		});
		this._proxy.sendRequest();
	},

	/**
	 * Updates the currently display message with the previously entered one.
	 * 
	 * @param	string		message
	 */
	_update: function(message) {
		var $content = this._container.find('div.commentContent:eq(0) > p');

		// restore original view
		this._cancelEdit($content.children('input'));

		// update message
		$content.children('span').html(message);
	},

	/**
	 * Adds the type-specific object id to data collection.
	 * 
	 * @param	object		data
	 * @return	object
	 */
	_addObjectID: function(data) {
		data.commentID = this._container.data('commentID');
		return data;
	},

	/**
	 * Returns object type.
	 * 
	 * @return	string
	 */
	_getType: function() {
		return 'comment';
	}
});

/**
 * Namespace for comment responses
 */
WCF.Comment.Response = {};

/**
 * Provides methods to add a response.
 * 
 * @see	WCF.Comment.Base
 */
WCF.Comment.Response.Add = WCF.Comment.Base.extend({
	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,

	/**
	 * @see	WCF.Comment.Base._init()
	 */
	_init: function() {
		// create UI
		var $listItem = $('<div class="commentResponseAdd"><div><p>' + this.getUserAvatar() + '</p><div></div></div></div>');
		var $inputContainer = $listItem.find('div > div');
		var $input = $('<input type="text" placeholder="' + WCF.Language.get('wcf.comment.response.add') + '" />').addClass('long').data('containerID', this._containerID).appendTo($inputContainer);
		var $description = $('<small>' + WCF.Language.get('wcf.comment.response.description') + '</small>').hide().appendTo($inputContainer);

		$input.focus($.proxy(this._expandInput, this)).blur($.proxy(this._foldInput, this)).keyup($.proxy(this._addResponse, this));
		$listItem.insertBefore(this._container.find('ul.commentResponseList'));

		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},

	/**
	 * Adds a new response.
	 * 
	 * @param	object		event
	 */
	_addResponse: function(event) {
		// ignore every key except for 13 = [Enter]
		if ((event.keyCode || event.which) != 13) {
			return;
		}

		var $input = $(event.target);
		var $value = $.trim($input.val());

		// ignore empty comments
		if ($value == '') {
			return;
		}
		
		this._proxy.setOption('data', {
			actionName: 'addResponse',
			className: 'wcf\\data\\comment\\CommentAction',
			parameters: {
				data: {
					commentID: this._container.data('commentID'),
					containerID: this._containerID,
					message: $value
				}
			}
		});
		this._proxy.sendRequest();

		// reset input
		$input.val('').blur();
	},

	/**
	 * Inserts the previously created response.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $containerID = data.returnValues.containerID;
		if (this._containerID != $containerID) return;

		// get list
		var $list = this._container.find('ul.commentResponseList');

		// get list items
		var $listItems = $list.children('li');

		var $showAll = this._container.find('div.commentResponsePrevious');

		if ($listItems.length === 3) {
			if ($showAll.length === 0) {
				$showAll = $('<div class="commentResponsePrevious"><a></a></div>').insertAfter($list);
			}

			// remove last comment
			var $lastResponse = $listItems.last();
			$lastResponse.wcfBlindOut('vertical', $.proxy(function() {
				$lastResponse.empty().remove();
			}, this));
		}

		// update message
		$showAll.children('a').text(eval(WCF.Language.get('wcf.comment.response.count')));

		// insert new response
		$(data.returnValues.template).hide().prependTo($list).wcfBlindIn();
	},

	/**
	 * Expands container to show a description.
	 * 
	 * @param	object		event
	 */
	_expandInput: function(event) {
		$(event.target).next().show();
	},

	/**
	 * Folds a container, restoring previous state.
	 * 
	 * @param	object		event
	 */
	_foldInput: function(event) {
		var $input = $(event.target);
		if ($input.val() == '') {
			$input.next().hide();
		}
	},

	/**
	 * Returns the user's avatar.
	 * 
	 * @return	string
	 */
	getUserAvatar: function() {
		return this._container.parent().data('WCF-Comment-Handler-API').getUserAvatar();
	}
});

/**
 * Manages response lists for each comment.
 */
WCF.Comment.Response.List = WCF.Comment.Base.extend({
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,

	/**
	 * list of responses
	 * @var	object
	 */
	_responses: { },

	/**
	 * @see	WCF.Comment.Base._init()
	 */
	_init: function() {
		var $responses = this._container.find('ul.commentResponseList > li');
		$responses.each($.proxy(function(index, response) {
			var $container = $(response);
			var $containerID = $container.wcfIdentify();
			var $responseID = $container.data('responseID') || null;

			if ($responseID !== null && !this._responses[$containerID]) {
				this._responses[$containerID] = $container;
				
				new WCF.Comment.Response.Editor($containerID, $container);
			}
		}, this));

		if (!this._didInit) {
			if (this._container.find('.commentResponsePrevious').length == 1) {
				new WCF.Comment.Response.Loader(this._containerID, this._container);
			}

			WCF.DOMNodeInsertedHandler.addCallback('WCF.Comment.Response.List', $.proxy(this._domNodeInserted, this));
			this._didInit = true;
		}
	},

	/**
	 * Enables options for dynamically inserted responses.
	 */
	_domNodeInserted: function() {
		this._init();
	}
});

/**
 * Provides edit access for responses.
 * 
 * @see	WCF.Comment.Editor
 */
WCF.Comment.Response.Editor = WCF.Comment.Editor.extend({
	/**
	 * @see	WCF.Comment.Editor._addObjectID()
	 */
	_addObjectID: function(data) {
		data.responseID = this._container.data('responseID');
		return data;
	},

	/**
	 * @see	WCF.Comment.Editor._getType()
	 */
	_getType: function() {
		return 'response';
	}
});

WCF.Comment.Response.Loader = WCF.Comment.Base.extend({
	_pageNo: 0,
	
	_responseList: { },

	_proxy: null,

	_init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});

		this._container.find('.commentResponsePrevious').click($.proxy(this._previous, this));
	},

	_previous: function() {
		this._pageNo++;

		if (!this._responseList[this._pageNo]) {
			this._proxy.setOption('data', {
				actionName: 'getResponseList',
				className: 'wcf\\data\\comment\\response\\CommentResponseAction',
				parameters: {
					data: {
						action: 'previous',
						containerID: this._containerID,
						commentID: this._container.data('commentID'),
						pageNo: this._pageNo
					}
				}
			});
			this._proxy.sendRequest();
			return;
		}

		var $responseList = this._container.find('.commentResponseList').wrap('<div />').wcfBlindOut('vertical', $.proxy(function() {
			$responseList.html(this._responseList[this._pageNo]).wcfBlindIn('vertical', function() {
				$responseList.unwrap('<div />');
			}, 600);
		}, this), 600);
	},

	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.containerID != this._containerID) return;

		this._responseList[this._pageNo] = data.returnValues.template;
		
		if (data.returnValues.action == 'previous') {
			this._pageNo--;
			this._previous();
		}
	}
});
