<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/WCF.Comment.js"></script>
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.comment.add': 'Write a comment …',
			'wcf.comment.description': 'Confirm with Enter to add your comment.',
			'wcf.comment.edit': 'Edit',
			'wcf.comment.edit.description': 'Push Esc to cancel or Enter to save',
			'wcf.comment.response.add': 'Write a response …',
			'wcf.comment.response.count': '\'Show all \' + data.returnValues.responses + \' comments\'',
			'wcf.comment.response.description': 'Confirm with Enter to add your response.'
		});
		{assign var=userAvatar value=''}
		{if $__wcf->getUserProfileHandler()->getAvatar()}
			{assign var=__dummy value=$__wcf->getUserProfileHandler()->getAvatar()->setMaxSize(32, 32)}
			{assign var=userAvatar value=$__wcf->getUserProfileHandler()->getAvatar()}
		{/if}
		new WCF.Comment.Handler('{@$userAvatar}');
	});
	//]]>
</script>
<style type="text/css">
	div.commentResponsePrevious {
		background-color: rgb(231, 242, 253);
		padding: 7px;
	}

	ul.commentList {
		background-color: rgb(243, 252, 255);
		margin: 0 auto;
		width: 450px;
	}

	ul.commentList li,
	div.commentResponseAdd {
		border-bottom: 1px solid rgb(204, 204, 204);
		display: table;
		padding: 7px;
	}

	div.commentResponseAdd {
		background-color: rgb(231, 242, 253);
	}

	ul.commentList li:last-child {
		border-bottom-width: 0;
	}

	ul.commentList li > div,
	div.commentResponseAdd > div {
		display: table-row;
	}

	ul.commentList li > div > :first-child,
	div.commentResponseAdd > div > :first-child {
		display: table-cell;
		padding-right: 7px;
	}

	ul.commentList li > div > :last-child,
	div.commentResponseAdd > div > :last-child {
		display: table-cell;
		padding-right: 3px;
		vertical-align: top;
		width: 100%;
	}

	div.commentContent > p > a {
		display: block;
	}

	ul.commentResponseList {
		background-color: rgb(231, 242, 253);
		width: 100%;
	}

	ul.commentOptions li {
		border-width: 0;
		display: inline-block;
		font-color: rgb(192, 192, 192);
		font-size: 85%;
		margin: 7px 0 3px 0;
		padding: 0 3px 0 0;
	}

	ul.commentOptions li:last-child {
		padding-right: 0;
	}

	ul.commentOptions li a {
		cursor: pointer;
	}

	ul.commentResponseList li:last-child {
		border-bottom: 1px solid rgb(204, 204, 204);
	}

	ul.commentResponseList ul.commentOptions li {
		margin-bottom: 0;
	}

	ul.commentList small {
		color: rgb(192, 192, 192);
		font-size: 85%;
	}

	div.commentContent div.border > :first-child {
		border-top-left-radius: 5px;
		border-top-right-radius: 5px;
	}

	div.commentContent div.border > :last-child {
		border-bottom-left-radius: 5px;
		border-bottom-right-radius: 5px;
	}
</style>

<div id="commentList" class="border tabMenuContent">
	<ul class="border commentList" data-objectID="{@$user->userID}" data-objectTypeID="{@$commentObjectTypeID}">
		{include file='commentList' sandbox=false}
	</ul>
</div>
