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
WCF.Comment.Handler = function(canAdd, commentsPerPage, userAvatar) { this.init(canAdd, commentsPerPage, userAvatar); };
WCF.Comment.Handler.prototype = {
	/**
	 * user can add comments and responses
	 * @var	boolean
	 */
	_canAdd: false,
	
	/**
	 * comments displayed at once
	 * @var	integer
	 */
	_commentsPerPage: 0,

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
	init: function(canAdd, commentsPerPage, userAvatar) {
		this._canAdd = canAdd;
		this._commentsPerPage = commentsPerPage;
		this._userAvatar = userAvatar;

		// init containers
		$('.wcf-commentList').each($.proxy(function(index, container) {
			var $container = $(container);
			var $containerID = $container.wcfIdentify();

			// store API
			$container.data('WCF-Comment-Handler-API', this);

			this._containers[$containerID] = $container;
			if (this._canAdd) new WCF.Comment.Add($containerID, $container);
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
	},

	/**
	 * Returns true, if user can add comments and responses.
	 * 
	 * @return	boolean
	 */
	canAdd: function() {
		return this._canAdd;
	},

	/**
	 * Returns comments per page.
	 * 
	 * @return	integer
	 */
	commentsPerPage: function() {
		return this._commentsPerPage;
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
		var $listItem = $('<li class="wcf-container wcf-commentAdd"><img src="' + WCF.Icon.get('wcf.icon.write') + '" alt="" width="24" height="24" class="wcf-containerIcon" /><div class="wcf-containerContent"></div></li>');
		var $inputContainer = $listItem.find('div.wcf-containerContent');
		var $input = $('<input type="text" placeholder="' + WCF.Language.get('wcf.comment.add') + '" />').addClass('long').appendTo($inputContainer);
		$('<small>' + WCF.Language.get('wcf.comment.description') + '</small>').hide().appendTo($inputContainer);

		$input.focus($.proxy(this._expandInput, this)).blur($.proxy(this._foldInput, this)).keyup($.proxy(this._addComment, this));
		$listItem.prependTo(this._container);

		// init proxy
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},

	/**
	 * Create a new comment if input isn't empty.
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
	 * user can add comments and responses
	 * @var	boolean
	 */
	_canAdd: null,

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
				
				if ($comment.data('canEdit')) {
					new WCF.Comment.Editor($containerID, $comment);
				}
				if (this.canAdd()) new WCF.Comment.Response.Add($containerID, $comment);
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
	},

	/**
	 * Returns true, if user can add comments and responses.
	 * 
	 * @return	boolean
	 */
	canAdd: function() {
		if (this._canAdd === null) {
			this._canAdd = this._container.data('WCF-Comment-Handler-API').canAdd();
		}

		return this._canAdd;
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
		var $optionList = this._container.find('ul.wcf-commentOptions:eq(0)');

		$('<li><a>' + WCF.Language.get('wcf.global.button.edit') + '</a></li>').addClass('wcf-commentEdit').appendTo($optionList).click($.proxy(this._prepare, this));
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
		var $content = this._container.find('div.wcf-commentContent:eq(0) p.userMessage:eq(0)');
		
		// replace content with input field
		$content.html($.proxy(function(index, oldhtml) {
			this._data.edit = oldhtml;
			var $input = $('<input type="text" value="' + message + '" class="long" /> <small>' + WCF.Language.get('wcf.comment.description') + '</small>').keydown($.proxy(this._keyDown, this)).keyup($.proxy(this._save, this));

			return $input;
		}, this));

		// hide elements
		$content.parent().find('.wcf-username:eq(0)').hide();
		$content.parent().find('.wcf-commentOptions:eq(0)').hide();
		$content.parent().find('.wcf-likesDisplay:eq(0)').hide();

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

		// restore elements
		input.parent().parent().find('.wcf-username:eq(0)').show();
		input.parent().parent().find('.wcf-commentOptions:eq(0)').show();
		input.parent().parent().find('.wcf-likesDisplay:eq(0)').show();

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
		var $content = this._container.find('div.wcf-commentContent:eq(0) p.userMessage:eq(0)');

		// restore original view
		this._cancelEdit($content.children('input'));

		// update message
		$content.html(message);
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
		var $listItem = $('<div class="wcf-container wcf-commentResponseAdd"><img src="' + WCF.Icon.get('wcf.icon.write') + '" alt="" width="24" height="24" class="wcf-containerIcon" /><div class="wcf-containerContent"></div></div>');
		var $inputContainer = $listItem.find('div.wcf-containerContent');
		var $input = $('<input type="text" placeholder="' + WCF.Language.get('wcf.comment.response.add') + '" />').addClass('long').data('containerID', this._containerID).appendTo($inputContainer);
		$('<small>' + WCF.Language.get('wcf.comment.description') + '</small>').hide().appendTo($inputContainer);

		$input.focus($.proxy(this._expandInput, this)).blur($.proxy(this._foldInput, this)).keyup($.proxy(this._addResponse, this));
		$listItem.insertBefore(this._container.find('ul.wcf-commentResponseList'));

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
		var $list = this._container.find('ul.wcf-commentResponseList');

		// get list items
		var $listItems = $list.children('li');

		if ($listItems.length === 3) {
			// remove last comment
			var $lastResponse = $listItems.last();
			$lastResponse.wcfBlindOut('vertical', $.proxy(function() {
				$lastResponse.empty().remove();
			}, this));
		}

		// update response count
		$list.data('responses', data.returnValues.responses);

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
		var $responses = this._container.find('ul.wcf-commentResponseList > li');
		$responses.each($.proxy(function(index, response) {
			var $container = $(response);
			var $containerID = $container.wcfIdentify();
			var $responseID = $container.data('responseID') || null;

			if ($responseID !== null && !this._responses[$containerID]) {
				this._responses[$containerID] = $container;
				
				if ($container.data('canEdit')) {
					new WCF.Comment.Response.Editor($containerID, $container);
				}
			}
		}, this));

		if (!this._didInit) {
			new WCF.Comment.Response.Loader(this._containerID, this._container);

			WCF.DOMNodeInsertedHandler.addCallback('WCF.Comment.Response.List.' + this._containerID, $.proxy(this._domNodeInserted, this));
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

/**
 * Loads previous responses.
 * 
 * @see	WCF.Comment.Base
 */
WCF.Comment.Response.Loader = WCF.Comment.Base.extend({
	/**
	 * list of navigation buttons
	 * @var	object
	 */
	_buttons: { },

	/**
	 * button states
	 * @var	object
	 */
	_buttonState: { },

	/**
	 * response list cache
	 * @var	object
	 */
	_cache: { },

	/**
	 * current page number, whereas 0 is the default view
	 * @var	integer
	 */
	_pageNo: 0,
	
	/**
	 * response count
	 * @var	integer
	 */
	_responses: 0,

	/**
	 * response list element
	 * @var	jQuery
	 */
	_responseList: null,

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

		this._responseList = this._container.find('.wcf-commentResponseList');

		// create buttons
		this._buttons = {
			previous: $('<div class="wcf-commentResponsePrevious"><a class="wcf-button">'+WCF.Language.get('wcf.comment.response.previous')+'</a></div>'),
			recent: $('<div class="wcf-commentResponseRecent"><a class="wcf-button">'+WCF.Language.get('wcf.comment.response.recent')+'</a></div>')
		};
		this._buttonState = {
			previous: {
				visible: false
			},
			recent: {
				visible: false
			}
		};

		// show previous button if applicable
		if (this._responseList.data('responses') > 3) {
			this._showPreviousButton();
		}
	},

	/**
	 * Triggers previous responses.
	 * 
	 * @param	object		event
	 */
	_previous: function(event) {
		this._pageNo++;

		// populate cache and display list afterwards
		if (!this._cache[this._pageNo]) {
			this._load();
		}
		else {
			this._showPrevious();
		}
	},

	/**
	 * Fetches response list from server.
	 */
	_load: function() {
		this._proxy.setOption('data', {
			actionName: 'getResponseList',
			className: 'wcf\\data\\comment\\response\\CommentResponseAction',
			parameters: {
				data: {
					containerID: this._containerID,
					commentID: this._container.data('commentID'),
					pageNo: this._pageNo
				}
			}
		});
		this._proxy.sendRequest();
	},

	/**
	 * Shows a list of previous responses.
	 */
	_showPrevious: function() {
		// validate if there's another page
		if (this._countPages() <= this._pageNo) {
			this._responseList.next().remove();
			this._buttonState.previous.visible = false;
		}

		// add recent button
		if (this._pageNo > 1) {
			this._showRecentButton();
		}

		// some more or less fancy list exchange
		var $responseList = this._responseList.wrap('<div />').wcfBlindOut('vertical', $.proxy(function() {
			$responseList.html(this._cache[this._pageNo]).wcfBlindIn('vertical', function() {
				$responseList.unwrap('<div />');
			}, 600);
		}, this), 600);
	},

	/**
	 * Triggers display of more recent responses.
	 * 
	 * @param	object		event
	 */
	_recent: function(event) {
		this._pageNo--;

		this._showRecent();
	},

	/**
	 * Shows a list of more recent responses.
	 */
	_showRecent: function() {
		// validate if there's another page
		if (this._pageNo <= 1) {
			this._responseList.prev().remove();
			this._buttonState.recent.visible = false;
		}

		// add previous button
		this._showPreviousButton();

		// once again some more or less fancy list exchange
		var $responseList = this._responseList.wrap('<div />').wcfBlindOut('vertical', $.proxy(function() {
			$responseList.html(this._cache[this._pageNo]).wcfBlindIn('vertical', function() {
				$responseList.unwrap('<div />');
			}, 600);
		}, this), 600);
	},

	/**
	 * Evaluates server response and populates response list cache.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.containerID != this._containerID) return;

		this._cache[this._pageNo] = data.returnValues.template;
		
		this._showPrevious();
	},

	/**
	 * Display the previous button if applicable.
	 */
	_showPreviousButton: function() {
		if (!this._buttonState.previous.visible) {
			this._buttons.previous.click($.proxy(this._previous, this)).insertAfter(this._responseList);
			this._buttonState.previous.visible = true;
		}
	},

	/**
	 * Displays the recent button if applicable.
	 */
	_showRecentButton: function() {
		if (!this._buttonState.recent.visible) {
			this._buttons.recent.click($.proxy(this._recent, this)).insertBefore(this._responseList);
			this._buttonState.recent.visible = true;
		}
	},

	/**
	 * Counts the total amount of pages.
	 * 
	 * @return	integer
	 */
	_countPages: function() {
		return Math.ceil(this._responseList.data('responses') / 20);
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
		return $('.wcf-commentList > li:not(.wcf-commentAdd)');
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
	_buildWidget: function(containerID, likeButton, dislikeButton, cumulativeLikes) {
		this._containers[containerID].find('.wcf-username:eq(0)').after(cumulativeLikes);
		
		likeButton.appendTo(this._containers[containerID].find('.wcf-commentOptions:eq(0)'));
		dislikeButton.appendTo(this._containers[containerID].find('.wcf-commentOptions:eq(0)'));
		cumulativeLikes.removeClass('wcf-likesDisplay').addClass('wcf-badge');
	},
	
	_updateBadge: function(containerID) {
		this._super(containerID);
		
		if (this._containerData[containerID].cumulativeLikes) {
			var $icon = WCF.Icon.get('wcf.icon.'+(this._containerData[containerID].cumulativeLikes > 0 ? 'like' : 'dislike'));
			if (!this._containerData[containerID].badge.find('img').length) {
				var $image = $('<span> <img src="' + $icon + '" alt="" /></span>');
				$image.appendTo(this._containerData[containerID].badge.find('a'));
			}
			else {
				this._containerData[containerID].badge.find('img').attr('src', $icon);
			}
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