{include file='__commentJavaScript'}

{if $commentCanAdd}
	<ul data-object-id="{@$userID}" data-object-type-id="{@$commentObjectTypeID}" class="commentList containerList">
		{include file='commentList'}
	</ul>
{else}
	{hascontent}
		<ul data-object-id="{@$userID}" data-object-type-id="{@$commentObjectTypeID}" class="commentList containerList">
			{content}
				{include file='commentList'}
			{/content}
		</ul>
	{hascontentelse}
		<div class="containerPadding">
			{lang}wcf.user.profile.content.wall.noEntries{/lang}
		</div>
	{/hascontent}
{/if}
