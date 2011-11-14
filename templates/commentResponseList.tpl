{foreach from=$responseList item=response}
	<li data-response-id="{@$response->responseID}" data-type="response">
		<div>
			<a href="{link controller='User' id=$response->userID}{/link}">
				{if $response->getUserProfile()->getAvatar()}
					{assign var=__dummy value=$response->getUserProfile()->getAvatar()->setMaxSize(32, 32)}
					<p>{@$response->getUserProfile()->getAvatar()}</p>
				{/if}
			</a>

			<div class="commentContent">
				<p>
					<a href="{link controller='User' id=$response->userID}{/link}">{$response->getUserProfile()->username}</a>
					<span>{$response->message}</span>
				</p>
				<ul class="commentOptions">
					<li>{@$response->time|time}</li>
				</ul>
			</div>
		</div>
	</li>
{/foreach}
