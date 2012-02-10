<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<title>「<?php echo $metaEntity->label ?>」の詳細ページ</title>
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

<h1>「<?php echo $metaEntity->label ?>」の詳細ページです。</h1>
<ul>
<li>このページのURL: <?php echo $page_url ?></li>
<li>このページのテンプレート: <?php echo $template_path ?></li>
</ul>
<p>このテンプレートファイルを参考にしてテンプレートを作成してください。その後このファイルを置き換えてください。</p>

<?php include 'template/record.php' ?>
</body>
</html>
