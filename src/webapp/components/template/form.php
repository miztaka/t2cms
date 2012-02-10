<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<title>「<?php echo $metaEntity->label ?>」の投稿フォーム</title>
<meta http-equiv="Content-type" content="text/html; charset=<?php echo Entity_Page::$_encodingOptions[$page->encoding] ?>" />
<style>
table {
  border-collapse: collapse;
  border-spacing: 0;
  }
td {
  border:1px solid;
  }
</style>
</head>
<body>

<h1>「<?php echo $metaEntity->label ?>」の投稿フォームです</h1>
<ul>
<li>このページのURL: <?php echo $page_url ?></li>
<li>このページのテンプレート: <?php echo $template_path ?></li>
</ul>
<p>このテンプレートファイルを参考にしてテンプレートを作成してください。その後このファイルを置き換えてください。</p>
<hr/>

<!-- エラーメッセージの表示 -->
{{include file='common/error_message.tpl'}}

<!-- フォーム -->
<form method="post">
<?php foreach ($metaAttribute as $attr): ?>
<p>
<span><?php echo $attr->label ?></span>
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
</p>
<?php endforeach; ?>
<p>
<input type="submit" name="action:doConfirm" value="次へ" /><!-- 確認ページへ -->
</p>
<!-- ※確認ページをはさまずいきなり投稿する場合はこちら 
<p>
<input type="submit" name="action:doRegist" value="投稿" />
</p>
 -->
</form>
</body>
</html>
