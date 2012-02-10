<?php
/**
 * 画像をリサイズして表示
 */
$base = dirname(__FILE__);
$libs = dirname(dirname(__FILE__))."/libs";
$u = @$_GET['u']; // ファイルパス
$w = @$_GET['w']; // width
$h = @$_GET['h']; // height
$f = @$_GET['f']; // 元のファイルタイプ
$output_type = NULL;

if (empty($u)) {
    echo "no image";
    exit;
}

// 画像変換
if (strlen($f)) {
    $p = strrpos($u, '.');
    $suffix = substr($u, 0, $p);
    $prefix = substr($u, $p+1);
    $output_type = strtolower($prefix) == 'jpg' ? 'jpeg' : strtolower($prefix);
    $u = $suffix.'.'.$f;
}

if (! file_exists($base.$u)) {
    echo "no file";
    exit;
}

require_once $libs."/pear/class.image.php";
$thumb = new Image($base.$u);
if (preg_match("/^[0-9]+$/", $w)) {
    $thumb->width($w);
}
if (preg_match("/^[0-9]+$/", $h)) {
    $thumb->height($h);
}

if ($output_type) {
    $thumb->output_type = $output_type;
}

if (! (@isset($_GET['c']) && 'on' == $_GET['c'])) {
    $thumb->show();
} else {
    // cacheに保存
    $p = strrpos($u, '.');
    $suffix = substr($u, 0, $p);
    $prefix = substr($u, $p);
    $filepath = "{$base}/cache{$suffix},{$w}_{$h}";
    if ($f) {
        $filepath .= "_{$f}";
    }
    $dir = dirname($filepath);
    $name = basename($filepath);
    if (file_exists($dir) || @mkdir($dir, 0777, TRUE)) {
        $thumb->name($name);
        $thumb->dir($dir);
        $thumb->save();
        $stats = stat($filepath.'.'.$thumb->ext);
        $etag = sprintf('"%x-%x-%x"', $stats['ino'], $stats['size'], $stats['mtime'] * 1000000);
        
        @header('Content-Type: image/'.($thumb->output_type ? $thumb->output_type : $thumb->type));
        @header("Etag: $etag");
        readfile($filepath.'.'.$thumb->ext);
    } else {
        $thumb->show();
    }
}

exit;
?>