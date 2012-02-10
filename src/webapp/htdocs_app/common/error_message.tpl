{{assign var=messages value=$r->getAllErrorMessages()}}
{{if count($messages) > 0}}
<div class="error_message">
<ul>
{{foreach from=$messages item=message name=error}}
<li>{{$message|escape}}</li>
{{/foreach}}
</ul>
</div>
{{/if}}
