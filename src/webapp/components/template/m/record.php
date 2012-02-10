<hr />
■レコードID<br/>
　{{$record->id}}<br/>
<?php foreach ($metaAttribute as $attr): ?>
■<?php echo $attr->label ?><br />
<?php if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_CHECK || $attr->data_type == Entity_MetaAttribute::DATA_TYPE_MULTISELECT): 
?>{{foreach from=$record-><?php echo $attr->pname ?> item=i}}{{$i}}<br/>{{/foreach}}<?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_IMAGE):
?>{{if $record-><?php echo $attr->pname ?>}}<img src="{{$a->basePath()}}/upload/{{$record-><?php echo $attr->pname ?>}}" />{{/if}}<?php 
else:
?>{{$record-><?php echo $attr->pname ?>}}<?php 
endif;
?><br />
<?php endforeach; ?>
