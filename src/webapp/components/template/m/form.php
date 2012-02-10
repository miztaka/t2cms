<?php echo '<?xml version="1.0" encoding="'.Entity_Page::$_encodingOptions[$page->encoding].'"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml;" charset="<?php echo Entity_Page::$_encodingOptions[$page->encoding] ?>" />
<title>「<?php echo $metaEntity->label ?>」の投稿フォーム</title>
<meta name="robots" content="index,follow" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
</head>
<body>
<h1>「<?php echo $metaEntity->label ?>」の投稿フォームです</h1>
<?php include 'template/m/instruction.php' ?>
<hr/>
<!-- エラーメッセージの表示 -->
{{include file='common/error_message_mb.tpl'}}

<!-- フォーム -->
<form method="post">
<?php foreach ($metaAttribute as $attr): ?>
■<?php echo $attr->label ?><br/>
<?php
if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_TEXT): 
?><input type="text" name="<?php echo $attr->pname ?>" value="{{$a-><?php echo $attr->pname ?>|escape}}" /><?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_TEXTAREA):
?><textarea name="<?php echo $attr->pname ?>" rows="5">{{$a-><?php echo $attr->pname ?>|escape}}</textarea><?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_CHECK):
?>{{html_checkboxes name=<?php echo $attr->pname ?> options=$a->getAttrOptions('<?php echo $attr->pname ?>') checked=$a-><?php echo $attr->pname ?> assign=cbs}}
{{foreach item=cb from=$cbs}}
{{$cb}}<br/>
{{/foreach}}<?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_RADIO):
?>{{html_radios name=<?php echo $attr->pname ?> options=$a->getAttrOptions('<?php echo $attr->pname ?>') checked=$a-><?php echo $attr->pname ?> assign=cbs}}
{{foreach item=cb from=$cbs}}
{{$cb}}<br/>
{{/foreach}}<?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_SELECT || $attr->data_type == Entity_MetaAttribute::DATA_TYPE_REF):
?><select name="<?php echo $attr->pname ?>">
<option value=""></option>
{{html_options options=$a->getAttrOptions('<?php echo $attr->pname ?>') selected=$a-><?php echo $attr->pname ?>}}
</select><?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_MULTISELECT):
?><select name="<?php echo $attr->pname ?>[]" multiple="multiple" size="5">
<option value=""></option>
{{html_options options=$a->getAttrOptions('<?php echo $attr->pname ?>') selected=$a-><?php echo $attr->pname ?>}}
</select>
<?php endif; ?>
<br/>
<?php endforeach; ?>
<br/>
<input type="submit" name="action:doConfirm" value="次へ" /><!-- 確認ページへ -->
<!-- ※確認ページをはさまずいきなり投稿する場合はこちら 
<input type="submit" name="action:doRegist" value="投稿" />
 -->
</form>
</body>
</html>
