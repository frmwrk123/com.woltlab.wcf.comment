{foreach from=$commentList item=comment}
	<li class="wcf-container" data-comment-id="{@$comment->commentID}" data-type="comment" data-object-type="com.woltlab.wcf.comment" data-like-liked="{if $likeData[$comment->commentID]|isset}{@$likeData[$comment->commentID]->liked}{/if}" data-like-cumulative-likes="{if $likeData[$comment->commentID]|isset}{@$likeData[$comment->commentID]->cumulativeLikes}{else}0{/if}" data-like-users='{if $likeData[$comment->commentID]|isset}{ {implode from=$likeData[$comment->commentID]->getUsers() item=likeUser}"{@$likeUser->userID}": { "username": "{$likeUser->username|encodeJS}" }{/implode} }{else}{ }{/if}' data-can-edit="{if $comment->isEditable()}true{else}false{/if}">
		<a href="{link controller='User' object=$comment->getUserProfile()}{/link}" title="{$comment->getUserProfile()->username}" class="wcf-containerIcon wcf-userAvatarFramed">
			{if $comment->getUserProfile()->getAvatar()}
				{@$comment->getUserProfile()->getAvatar()->getImageTag(48)}
			{/if}
		</a>

		<div class="wcf-containerContent wcf-commentContent">
			<div>
				<p class="wcf-username"><a href="{link controller='User' object=$comment->getUserProfile()}{/link}">{$comment->getUserProfile()->username}</a> - {@$comment->time|time}</p>
				<p class="userMessage">{@$comment->getFormattedMessage()}</p>
				<ul class="wcf-commentOptions"></ul>
			</div> 
			
			<ul data-responses="{@$comment->responses}" class="wcf-commentResponseList">
				{if $comment|count}
					{include file='commentResponseList' sandbox=false responseList=$comment}
				{/if}
			</ul>
		</div>
	</li>
{/foreach}
