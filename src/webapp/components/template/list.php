<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<title>「<?php echo $metaEntity->label ?>」の一覧ページ</title>
<meta http-equiv="Content-type" content="text/html; charset=<?php echo Entity_Page::$_encodingOptions[$page->encoding] ?>" />
<meta name="X-T2C-Pager-page" content="{{$pager->page}}" />
<meta name="X-T2C-Pager-limit" content="{{$pager->limit}}" />
<meta name="X-T2C-Pager-total" content="{{$pager->total}}" />
<meta name="X-T2C-Pager-hit" content="{{$pager->hit}}" />
<link rel="stylesheet" href="{{$a->basePath()}}/resources/css/pagination.css" type="text/css" media="screen" />
<script type="text/javascript" src="{{$a->basePath()}}/resources/scripts/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="{{$a->basePath()}}/resources/scripts/jquery.pagination.ex.js"></script>
<script type="text/javascript" src="{{$a->basePath()}}/resources/scripts/t2c.js"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[
var L = {};
jQuery(function($){
    // pagination
    L.pager = T2C.Pager();
    L.pager.loadMeta();
    $("#pagination").pagination(L.pager.total, {
        current_page: L.pager.page - 1,
        items_per_page: L.pager.limit,
        num_display_entries: 10,
        num_edge_entries: 2,
        prev_text: "前へ",
        next_text: "次へ",
        link_to: "<?php echo $page_url ?>?page=__id__",
        callback: function(){return true;}
    });
});

//]]>
</script>
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

<h1>「<?php echo $metaEntity->label ?>」の一覧ページです。</h1>
<ul>
<li>このページのURL: <?php echo $page_url ?></li>
<li>このページのテンプレート: <?php echo $template_path ?></li>
</ul>
<p>このテンプレートファイルを参考にしてテンプレートを作成してください。その後このファイルを置き換えてください。</p>
{{literal}}<p>※レコードの一覧を表示するには各レコードを表示するためのブロックを
 <span style="color:red">{{foreach from=$records item=record}}</span>
 と
 <span style="color:red">{{/foreach}}</span>
 で囲みます。
</p>{{/literal}}

<div id="pagination" class="pagination"></div>
<div style="clear:both;"></div>
{{foreach from=$records item=record}}
<?php include 'template/record.php' ?>
{{/foreach}}
</body>
</html>
