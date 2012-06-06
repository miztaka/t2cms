<?php
/**
 * 画像をリサイズして表示
 */

class ImageCache {
    
    const INFINITE = 100000;
    
    private $origImagePath;
    
    public function execute() {
        
        $base = dirname(__FILE__);
        $documentRoot = dirname($base);
        $libs = dirname($documentRoot)."/webapp/libs";
        require_once $libs."/pear/class.image.php";
        require_once $libs."/pear/FileTool.php";

        $path_orig = $_SERVER['PATH_INFO'];
        $path = trim($path_orig, "/");
        list($param, $path) = explode("/", $path, 2);
        $this->origImagePath = realpath($documentRoot."/".$path);

        $w = self::INFINITE;
        $h = self::INFINITE;
        $b = FALSE;
        if (preg_match('/^(w\d+)?(h\d+)?(b[0-9a-fA-F]{6})?$/', $param, $m)) {
            if (@$m[1]) {
                $w = intval(substr($m[1], 1));
            }
            if (@$m[2]) {
                $h = intval(substr($m[2], 1));
            }
            if (@$m[3]) {
                if ($w != self::INFINITE && $h != self::INFINITE) {
                    $b = '#'. substr($m[3], 1);
                }
            }
        } else {
            $this->exit404();
            return;
        }
        if ($w == self::INFINITE && $h == self::INFINITE) {
            $this->exit404('invalid param.');
            return;
        }
        
        // キャッシュファイルが存在する場合
        $cachePath = $base.$path_orig;
        if ($this->serveFromCache($cachePath)) {
            return;
        }
        
        // キャッシュファイルを生成
        $ft = new FileTool();
        $resource = $ft->resizeImage($this->origImagePath, $w, $h, $b);
        if ($resource === FALSE) {
            $this->exit404('resize failed.');
            return;
        }
        
        if (file_exists($cachePath)) {
            if (! is_writable($cachePath)) {
                $this->exit404('cache file not writable.');
                return;
            }
        } else {
            if (! @mkdir(dirname($cachePath), 0775, true)) {
                $this->exit404("mkdir failed. path=". $cachePath);
                return;
            }
        }
        
        if (! $ft->imagecreateto($resource, $cachePath)) {
            $this->exit404('image create failed.');
            return;
        }
        
        $this->sendImageFile($cachePath);
        return;
    }
    
    private function exit404($msg=null) {
        header("HTTP/1.1 404 Not Found");
        if ($msg) {
            print($msg);
        }
        return;
    }
    
    /**
     * キャッシュからファイルを提供します。
     * @param string $cachePath
     */
    private function serveFromCache($cachePath) {
        if (! is_readable($cachePath) || ! is_readable($this->origImagePath)) {
            return false;
        }
        $cacheStat = stat($cachePath);
        $origStat = stat($this->origImagePath);
        if ($cacheStat['mtime'] < $origStat['mtime']) {
            return false;
        }
        
        // not modified
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && 
            strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $cacheStat['mtime']) {
            header('HTTP/1.0 304 Not Modified');
            return true;
        }
        
        // from cache file
        $this->sendImageFile($cachePath, $cacheStat);
    }
    
    /**
     * キャッシュファイルから出力します。
     * @param unknown_type $path
     * @param unknown_type $stat
     */
    private function sendImageFile($cachePath, $stat=null) {
        if (! $stat) {
            $stat = stat($cachePath);
        }
        $img = new Image($cachePath);
        header('Content-Type: image/'.($img->output_type ? $img->output_type : $img->type));
        header("Content-Length: ".$stat['size']);
        //header("Last-Modified: ". gmdate('D, d M Y H:i:s T', $stat['mtime']));
        header("Last-Modified: ". gmdate('D, d M Y H:i:s T', time())); // TODO readfileがmtimeを更新してしまうようなので。。
        readfile($cachePath);
        return;
    }
    
}

$obj = new ImageCache();
$obj->execute();
exit;
?>