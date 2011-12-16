{foreach from=$responseList item=response}
	<li data-response-id="{@$response->responseID}" data-type="response">
		<a href="{link controller='User' id=$response->userID}{/link}" title="{$response->getUserProfile()->username}" class="userAvatar">
			{if $response->getUserProfile()->getAvatar()}
				{assign var=__dummy value=$response->getUserProfile()->getAvatar()->setMaxSize(32, 32)}
				{@$response->getUserProfile()->getAvatar()}
			{/if}
		</a>

		<div class="commentContent">
			<p class="userName"><a href="{link controller='User' id=$response->userID}{/link}">{$response->getUserProfile()->username}</a> - {@$response->time|time}</p>
			<p class="userResponse">{$response->message}</p>
			
			<ul class="commentOptions"></ul>
		</div>
	</li>
{/foreach}
