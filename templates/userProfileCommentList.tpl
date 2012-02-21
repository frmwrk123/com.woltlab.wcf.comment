<script type="text/javascript" src="{@$__wcf->getPath('wcf')}js/WCF.Comment.js"></script>
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.comment.response.previous': '{lang}wcf.comment.response.previous{/lang}',
			'wcf.comment.response.recent': '{lang}wcf.comment.response.recent{/lang}',
			'wcf.comment.add': '{lang}wcf.comment.add{/lang}',
			'wcf.comment.description': '{lang}wcf.comment.description{/lang}',
			'wcf.comment.response.add': '{lang}wcf.comment.response.add{/lang}',
		});
		WCF.Icon.addObject({
			'wcf.icon.write': '{icon size='M'}write1{/icon}',
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
