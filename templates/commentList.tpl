{foreach from=$commentList item=comment}
	<li data-commentID="{@$comment->commentID}" data-type="comment">
		<div>
			<a href="{link controller='User' id=$comment->userID}{/link}">
				{if $comment->getUserProfile()->getAvatar()}
					{assign var=__dummy value=$comment->getUserProfile()->getAvatar()->setMaxSize(48, 48)}
					<p>{@$comment->getUserProfile()->getAvatar()}</p>
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
					<ul class="commentResponseList">
						{if $comment|count}
							{include file='commentResponseList' sandbox=false responseList=$comment}
						{/if}
					</ul>
					{if $comment->responses > 3}
						<div class="commentResponsePrevious"> <!-- previous <> recent -->
							<a>Show previous responses</a>
						</div>
					{/if}
				</div>
			</div>
		</div>
	</li>
{/foreach}
