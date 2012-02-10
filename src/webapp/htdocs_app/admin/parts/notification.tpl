{{if $r->hasNotification()}}
{{foreach from=$r->getNotification() item=message}}
<div class="notification success png_bg">
<div>{{$message|escape}}</div>
</div>
{{/foreach}}
{{/if}}
