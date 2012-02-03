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
			{assign var=userAvatar value=$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}
		{/if}
		new WCF.Comment.Handler({if $commentCanAdd}true{else}false{/if}, {@$commentsPerPage}, '{@$userAvatar}');
		new WCF.Comment.Like();	
	});
	//]]>
</script>

<ul data-object-id="{@$userID}" data-object-type-id="{@$commentObjectTypeID}" class="wcf-commentList">
	{include file='commentList' sandbox=false}
</ul>
