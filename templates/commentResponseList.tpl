{foreach from=$responseList item=response}
	<li data-response-id="{@$response->responseID}" data-type="response" class="wcf-container">
		<a href="{link controller='User' object=$response->getUserProfile()}{/link}" title="{$response->getUserProfile()->username}" class="wcf-containerIcon wcf-userAvatarFramed">
			{if $response->getUserProfile()->getAvatar()}
				{@$response->getUserProfile()->getAvatar()->getImageTag(32)}
			{/if}
		</a>

		<div class="wcf-containerContent wcf-commentContent">
			<p class="wcf-username"><a href="{link controller='User' object=$response->getUserProfile()}{/link}">{$response->getUserProfile()->username}</a> - {@$response->time|time}</p>
			<p class="userResponse">{@$response->getFormattedMessage()}</p>
			
			<ul class="wcf-commentOptions"></ul>
		</div>
	</li>
{/foreach}
