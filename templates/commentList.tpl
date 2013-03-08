{foreach from=$commentList item=comment}
	<li class="comment jsComment" data-comment-id="{@$comment->commentID}" data-object-type="com.woltlab.wcf.comment" data-like-liked="{if $likeData[comment][$comment->commentID]|isset}{@$likeData[comment][$comment->commentID]->liked}{/if}" data-like-likes="{if $likeData[comment][$comment->commentID]|isset}{@$likeData[comment][$comment->commentID]->likes}{else}0{/if}" data-like-dislikes="{if $likeData[comment][$comment->commentID]|isset}{@$likeData[comment][$comment->commentID]->dislikes}{else}0{/if}" data-like-users='{if $likeData[comment][$comment->commentID]|isset}{ {implode from=$likeData[comment][$comment->commentID]->getUsers() item=likeUser}"{@$likeUser->userID}": { "username": "{$likeUser->username|encodeJS}" }{/implode} }{else}{ }{/if}' data-can-edit="{if $comment->isEditable()}true{else}false{/if}" data-can-delete="{if $comment->isDeletable()}true{else}false{/if}" data-responses="{@$comment->responses}" data-last-response-time="{@$comment->getLastResponseTime()}">
		<div class="box32">
			<a href="{link controller='User' object=$comment->getUserProfile()}{/link}" title="{$comment->getUserProfile()->username}" class="framed">
				{@$comment->getUserProfile()->getAvatar()->getImageTag(32)}
			</a>
			
			<div>
				<div class="commentContent">
					<hgroup class="containerHeadline">
						<h1><a href="{link controller='User' object=$comment->getUserProfile()}{/link}">{$comment->username}</a><small> - {@$comment->time|time}</small></h1> 
					</hgroup>
					
					<p class="userMessage">{@$comment->getFormattedMessage()}</p>
					
					<ul class="commentOptions">
						<li class="jsReportCommentComment jsOnly" data-object-id="{@$comment->commentID}"><a title="{lang}wcf.moderation.report.reportContent{/lang}" class="jsTooltip"><span class="icon icon16 icon-warning-sign"></span></a></li>
						
						{event name='commentOptions'}
					</ul>
				</div>
				
				<ul data-responses="{@$comment->responses}" class="commentResponseList">
					{if $comment|count}
						{include file='commentResponseList' responseList=$comment}
					{/if}
				</ul>
			</div>
		</div>
	</li>
{/foreach}
