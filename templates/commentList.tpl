{foreach from=$commentList item=comment}
	<li data-comment-id="{@$comment->commentID}" data-type="comment">
		<a href="{link controller='User' id=$comment->userID}{/link}" title="{$comment->getUserProfile()->username}" class="userAvatar">
			{if $comment->getUserProfile()->getAvatar()}
				{assign var=__dummy value=$comment->getUserProfile()->getAvatar()->setMaxSize(48, 48)}
				{@$comment->getUserProfile()->getAvatar()}
			{/if}
		</a>

		<div class="commentContent">
			<p class="userName"><a href="{link controller='User' id=$comment->userID}{/link}">{$comment->getUserProfile()->username}</a> - {@$comment->time|time}</p>
			<p class="userMessage">{$comment->message}</p>
			
			<ul class="commentOptions"></ul>

			<ul data-responses="{@$comment->responses}" class="commentResponseList">
				{if $comment|count}
					{include file='commentResponseList' sandbox=false responseList=$comment}
				{/if}
			</ul>
		</div>
	</li>
{/foreach}
