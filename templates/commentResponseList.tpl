{foreach from=$responseList item=response}
	<li class="commentResponse jsCommentResponse" data-response-id="{@$response->responseID}" data-can-edit="{if $response->isEditable()}true{else}false{/if}" data-can-delete="{if $response->isDeletable()}true{else}false{/if}">
		<div class="box32">
			<a href="{link controller='User' object=$response->getUserProfile()}{/link}" title="{$response->getUserProfile()->username}" class="framed">
				{if $response->getUserProfile()->getAvatar()}
					{@$response->getUserProfile()->getAvatar()->getImageTag(32)}
				{/if}
			</a>
			
			<div class="commentContent commentResponseContent">
				<hgroup class="containerHeadline">
					<h1><a href="{link controller='User' object=$response->getUserProfile()}{/link}">{$response->getUserProfile()->username}</a><small> - {@$response->time|time}</small></h1> 
				</hgroup>
				
				<p class="userMessage">{@$response->getFormattedMessage()}</p>
				
				<ul class="commentOptions">
					<li class="jsReportCommentResponse" data-object-id="{@$response->responseID}"><a title="{lang}wcf.moderation.report.reportContent{/lang}" class="jsTooltip"><img src="{icon}warning{/icon}" alt="" class="icon16" /></a></li>
				</ul>
			</div>
		</div>
	</li>
{/foreach}
