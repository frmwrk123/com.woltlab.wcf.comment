{foreach from=$responseList item=response}
	<li data-response-id="{@$response->responseID}" data-type="response">
		<div>
			<a href="{link controller='User' id=$response->userID}{/link}" title="{$response->getUserProfile()->username}" class="userAvatar">
				{if $response->getUserProfile()->getAvatar()}
					{assign var=__dummy value=$response->getUserProfile()->getAvatar()->setMaxSize(32, 32)}
					{@$response->getUserProfile()->getAvatar()}
				{/if}
			</a>

			<div class="commentContent">
				<p class="userName"><a href="{link controller='User' id=$response->userID}{/link}">{$response->getUserProfile()->username}</a></p>
				<p class="userResponse">{$response->message}</p>
				
				<ul class="commentOptions">
					<li>{@$response->time|time}</li>
				</ul>
			</div>
		</div>
	</li>
{/foreach}
