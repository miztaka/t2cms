{{assign var=messages value=$r->getAllErrorMessages()}}
{{if count($messages) > 0}}
<div class="notification error png_bg">
<div>
{{foreach from=$messages item=message name=error}}
{{$message|escape}}{{if ! $smarty.foreach.error.last}}<br/>{{/if}}
{{/foreach}}
</div>
</div>
{{/if}}
