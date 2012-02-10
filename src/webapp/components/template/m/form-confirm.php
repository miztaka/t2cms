<?php echo '<?xml version="1.0" encoding="'.Entity_Page::$_encodingOptions[$page->encoding].'"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml;" charset="<?php echo Entity_Page::$_encodingOptions[$page->encoding] ?>" />
<title>「<?php echo $metaEntity->label ?>」の投稿フォーム(確認画面)</title>
<meta name="robots" content="index,follow" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
</head>
<body>
<h1>「<?php echo $metaEntity->label ?>」の投稿フォーム(確認画面)です</h1>
<?php include 'template/m/instruction.php' ?>
<hr/>
<p>以下の内容で投稿してよろしいですか？</p>
<?php foreach ($metaAttribute as $attr): ?>
<p>
■<?php echo $attr->label ?><br/>
　<?php
if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_CHECK || $attr->data_type == Entity_MetaAttribute::DATA_TYPE_MULTISELECT): 
?>{{foreach from=$a-><?php echo $attr->pname ?> item=i}}{{$i}}<br/>{{/foreach}}<?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_IMAGE):
?>{{if $a-><?php echo $attr->pname ?>}}<img src="{{$a->basePath()}}/upload/{{$a-><?php echo $attr->pname ?>}}" />{{/if}}<?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_REF):
?>{{if $a-><?php echo $attr->pname ?>}}{{$a->refLabel('<?php echo $attr->pname ?>')}}{{/if}}<?php 
else:
?>{{$a-><?php echo $attr->pname ?>|escape|nl2br}}<?php 
endif;
?><br/>
</p>
<?php endforeach; ?>

<!-- フォーム -->
<form method="post">
<p>
<input type="submit" name="action:doRegist" value="投稿" />
<input type="submit" name="action:execute" value="戻る" />
</p>
{{html_hidden values=$r->getParameters()}}
</form>
</body>
</html>
