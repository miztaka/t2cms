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
<h1>「<?php echo $metaEntity->label ?>」の投稿が完了しました。</h1>
<?php include 'template/m/instruction.php' ?>
<hr/>
<p>※コンバージョンを取得したいときはこのページにタグを埋め込んでください。</p>

</body>
</html>
