<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<title>「<?php echo $metaEntity->label ?>」の投稿フォーム(確認画面)</title>
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

<h1>「<?php echo $metaEntity->label ?>」の投稿フォーム(確認画面)です</h1>
<ul>
<li>このページのURL: <?php echo $page_url ?></li>
<li>このページのテンプレート: <?php echo $template_path ?></li>
</ul>
<p>このテンプレートファイルを参考にしてテンプレートを作成してください。その後このファイルを置き換えてください。</p>
<hr/>

<p>以下の内容で投稿してよろしいですか？</p>

<?php foreach ($metaAttribute as $attr): ?>
<p>
<span><?php echo $attr->label ?></span>
<span><?php
if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_CHECK || $attr->data_type == Entity_MetaAttribute::DATA_TYPE_MULTISELECT): 
?>{{foreach from=$a-><?php echo $attr->pname ?> item=i}}{{$i}}<br/>{{/foreach}}<?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_IMAGE):
?>{{if $a-><?php echo $attr->pname ?>}}<img src="{{$a->basePath()}}/upload/{{$a-><?php echo $attr->pname ?>}}" />{{/if}}<?php 
elseif ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_REF):
?>{{if $a-><?php echo $attr->pname ?>}}{{$a->refLabel('<?php echo $attr->pname ?>')}}{{/if}}<?php 
else:
?>{{$a-><?php echo $attr->pname ?>|escape|nl2br}}<?php 
endif;
?></span>
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
