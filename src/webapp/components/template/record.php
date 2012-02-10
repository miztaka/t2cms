<hr />
<h4>レコードID {{$record->id}} のデータ</h4>
<table border="0">
<tr>
<th>項目名</th>
<th>実際のデータ</th>
<th>差込記号</th>
</tr>
<?php foreach ($metaAttribute as $attr): ?>
<tr>
<td><?php echo $attr->label ?></td>
<td><?php
if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_CHECK || $attr->data_type == Entity_MetaAttribute::DATA_TYPE_MULTISELECT): 
?>{{foreach from=$record-><?php echo $attr->pname ?> item=i}}{{$i}}<br/>{{/foreach}}<?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_IMAGE):
?>{{if $record-><?php echo $attr->pname ?>}}<img src="{{$a->basePath()}}/upload/{{$record-><?php echo $attr->pname ?>}}" />{{/if}}<?php 
else:
?>{{$record-><?php echo $attr->pname ?>}}<?php 
endif;
?></td>
<td>{{literal}}{{$record-><?php echo $attr->pname ?>}}{{/literal}}</td>
</tr>
<?php endforeach; ?>
</table>
