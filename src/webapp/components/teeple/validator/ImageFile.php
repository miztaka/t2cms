<?php
/**
 * Teeple2 - PHP5 Web Application Framework inspired by Seasar2
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     teeple
 * @author      Kenzoh Sekitoh <sekitoh@acudbase.jp>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */

/**
 * イメージファイルかどうかを判別します。
 *
 * @package teeple.validator
 */
class Teeple_Validator_ImageFile extends Teeple_Validator
{
    protected function execute($obj, $fieldName) {
        $filePath = $this->getTargetValue($obj, $fieldName);
        if (Teeple_Util::isBlank($filePath)) {
            return TRUE;
        }

    	return $this->isImage($filePath);
    }

    private function isImage($img_path="")
    {
    	if (!(file_exists($img_path) and $fp=fopen($img_path, "rb"))) return false;
    	$head= fread($fp, 8); fclose($fp);
    	if ($head === "\x89PNG\x0d\x0a\x1a\x0a") return true;
    	else if (substr($head, 0, 2) === "\xff\xd8") return true;
    	else if (preg_match('/^GIF8[79]a/', $head)) return true;
    	return false;
    }

    private function isImageByExif($filePath) {
    	if (!(file_exists($filePath) and $type=exif_imagetype($filePath))) return false;
    	if (IMAGETYPE_GIF == $type) {
    		return true;
    	} else if (IMAGETYPE_JPEG == $type) {
    		return true;
    	} else if (IMAGETYPE_PNG == $type) {
    		return true;
    	}

    	return false;
    }

}
?>