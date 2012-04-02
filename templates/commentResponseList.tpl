{foreach from=$responseList item=response}
	<li data-response-id="{@$response->responseID}" data-type="response" data-can-edit="{if $response->isEditable()}true{else}false{/if}">
		<div class="box32">
			<a href="{link controller='User' object=$response->getUserProfile()}{/link}" title="{$response->getUserProfile()->username}" class="framed">
				{if $response->getUserProfile()->getAvatar()}
					{@$response->getUserProfile()->getAvatar()->getImageTag(32)}
				{/if}
			</a>
		
			<div class="commentContent">
				<hgroup class="containerHeadline">
					<h1><a href="{link controller='User' object=$response->getUserProfile()}{/link}">{$response->getUserProfile()->username}</a><small> - {@$response->time|time}</small></h1> 
				</hgroup>
				
				<p class="userMessage">{@$response->getFormattedMessage()}</p>
				
				<ul class="commentOptions"></ul>
			</div>
		</div>
	</li>
{/foreach}
