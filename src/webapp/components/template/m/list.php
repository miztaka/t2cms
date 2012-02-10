<?php echo '<?xml version="1.0" encoding="'.Entity_Page::$_encodingOptions[$page->encoding].'"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml;" charset="<?php echo Entity_Page::$_encodingOptions[$page->encoding] ?>" />
<title>「<?php echo $metaEntity->label ?>」の一覧ページ</title>
<meta name="robots" content="index,follow" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
</head>
<body>
<h1>「<?php echo $metaEntity->label ?>」の一覧ページです。</h1>
<?php include 'template/m/instruction.php' ?>
<hr/>
<h2>レコード一覧表示の例</h2>
{{literal}}レコードの一覧を表示するには各レコードを表示するためのブロックを
 <span style="color:red">{{foreach from=$records item=record}}</span>
 と
 <span style="color:red">{{/foreach}}</span>
 で囲みます。<br/>
{{/literal}}
{{foreach from=$records item=record}}
<?php include 'template/m/record.php' ?>
{{/foreach}}
</body>
</html>
