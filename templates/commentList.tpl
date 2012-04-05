{foreach from=$commentList item=comment}
	<li class="comment" data-comment-id="{@$comment->commentID}" data-type="comment" data-object-type="com.woltlab.wcf.comment" data-like-liked="{if $likeData[$comment->commentID]|isset}{@$likeData[$comment->commentID]->liked}{/if}" data-like-cumulative-likes="{if $likeData[$comment->commentID]|isset}{@$likeData[$comment->commentID]->cumulativeLikes}{else}0{/if}" data-like-users='{if $likeData[$comment->commentID]|isset}{ {implode from=$likeData[$comment->commentID]->getUsers() item=likeUser}"{@$likeUser->userID}": { "username": "{$likeUser->username|encodeJS}" }{/implode} }{else}{ }{/if}' data-can-edit="{if $comment->isEditable()}true{else}false{/if}">
		<div class="box48">
			<a href="{link controller='User' object=$comment->getUserProfile()}{/link}" title="{$comment->getUserProfile()->username}" class="framed">
				{if $comment->getUserProfile()->getAvatar()}
					{@$comment->getUserProfile()->getAvatar()->getImageTag(48)}
				{/if}
			</a>
	
			<div>
				<div class="commentContent">
					<hgroup class="containerHeadline">
						<h1><a href="{link controller='User' object=$comment->getUserProfile()}{/link}">{$comment->getUserProfile()->username}</a><small> - {@$comment->time|time}</small></h1> 
					</hgroup>
					
					<p class="userMessage">{@$comment->getFormattedMessage()}</p>
					<ul class="commentOptions"></ul>
				</div>
				
				<ul data-responses="{@$comment->responses}" class="commentResponseList">
					{if $comment|count}
						{include file='commentResponseList' sandbox=false responseList=$comment}
					{/if}
				</ul>
			</div>
		</div>
	</li>
{/foreach}
