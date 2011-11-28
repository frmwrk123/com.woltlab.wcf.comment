{foreach from=$commentList item=comment}
	<li data-comment-id="{@$comment->commentID}" data-type="comment">
		<div>
			<a href="{link controller='User' id=$comment->userID}{/link}" class="userAvatar">
				{if $comment->getUserProfile()->getAvatar()}
					{assign var=__dummy value=$comment->getUserProfile()->getAvatar()->setMaxSize(48, 48)}
					{@$comment->getUserProfile()->getAvatar()}
				{/if}
			</a>

			<div class="commentContent">
				<p>
					<a href="{link controller='User' id=$comment->userID}{/link}">{$comment->getUserProfile()->username}</a>
					<span>{$comment->message}</span>
				</p>
				<ul class="commentOptions">
					<li>{@$comment->time|time}</li>
				</ul>

				<div class="border">
					<ul data-responses="{@$comment->responses}" class="commentResponseList">
						{if $comment|count}
							{include file='commentResponseList' sandbox=false responseList=$comment}
						{/if}
					</ul>
				</div>
			</div>
		</div>
	</li>
{/foreach}
