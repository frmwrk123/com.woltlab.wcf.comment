{foreach from=$commentList item=comment}
	<li data-comment-id="{@$comment->commentID}" data-type="comment" data-object-type="com.woltlab.wcf.comment" data-like-liked="{if $likeData[$comment->commentID]|isset}{@$likeData[$comment->commentID]->liked}{/if}" data-like-cumulative-likes="{if $likeData[$comment->commentID]|isset}{@$likeData[$comment->commentID]->cumulativeLikes}{else}0{/if}" data-like-users='{if $likeData[$comment->commentID]|isset}{ {implode from=$likeData[$comment->commentID]->getUsers() item=likeUser}"{@$likeUser->userID}": { "username": "{$likeUser->username|encodeJS}" }{/implode} }{else}{ }{/if}'>
		<a href="{link controller='User' id=$comment->userID}{/link}" title="{$comment->getUserProfile()->username}" class="userAvatar">
			{if $comment->getUserProfile()->getAvatar()}
				{@$comment->getUserProfile()->getAvatar()->getImageTag(48)}
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
