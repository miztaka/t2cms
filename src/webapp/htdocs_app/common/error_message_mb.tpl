{{assign var=messages value=$r->getAllErrorMessages()}}
{{if count($messages) > 0}}
<div style="font-size:small; color:red; font-weight:bold;">
{{foreach from=$messages item=message}}
[!]{{$message|escape}}<br/>
{{/foreach}}
</div>
{{/if}}