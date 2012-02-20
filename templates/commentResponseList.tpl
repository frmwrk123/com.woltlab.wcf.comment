{foreach from=$responseList item=response}
	<li data-response-id="{@$response->responseID}" data-type="response" class="wcf-container">
		<a href="{link controller='User' id=$response->userID}{/link}" title="{$response->getUserProfile()->username}" class="wcf-containerIcon userAvatar">
			{if $response->getUserProfile()->getAvatar()}
				{@$response->getUserProfile()->getAvatar()->getImageTag(32)}
			{/if}
		</a>

		<div class="wcf-containerContent wcf-commentContent">
			<p class="username"><a href="{link controller='User' id=$response->userID}{/link}">{$response->getUserProfile()->username}</a> - {@$response->time|time}</p>
			<p class="userResponse">{$response->message}</p>
			
			<ul class="wcf-commentOptions"></ul>
		</div>
	</li>
{/foreach}
