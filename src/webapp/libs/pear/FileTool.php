<?php
/**
 * ファイルを総合的に扱うクラス
 *
 * 画像ファイルのリサイズをおこなう
 */
class FileTool
{
        /**
         * 画像ファイルから画像リソースを取得しリサイズする
         *
         * @param string $srcCile : ソース画像ファイル名
         * @param integer $dstW : 変換後幅
         * @param integer $dstH : 変換後高さ
         * @param string $bgcolor : 背景色、指定すると幅・高さ固定になる
         * @param boolean $expand : 変換後のサイズが元サイズより大きい場合、拡大する
         * @param string $position : 位置。L(左寄せ)R(右寄せ)T(上寄せ)B(下寄せ)
         */
        function resizeImage($srcFile, $dstW, $dstH, $bgcolor=FALSE, $expand=FALSE, $position='')
        {
                if(! file_exists($srcFile)){
                        print('"' . $srcFile . '" is not found.');
                        return FALSE;
                }

                list($srcW, $srcH) = getimagesize($srcFile);

                $rate = $dstW / $srcW;
                if($rate > $dstH / $srcH){
                        $rate = $dstH / $srcH;
                        $rszH = $dstH;
                        $rszW = round($srcW * $rate);
                }else{
                        $rszW = $dstW;
                        $rszH = round($srcH * $rate);
                }
                if(! $expand && $rate > 1){
                        $rszW = $srcW;
                        $rszH = $srcH;
                }

                if($bgcolor === FALSE){
                        $dstImage = imagecreatetruecolor($rszW, $rszH);
                        $dstL = $dstT = 0;
                }else{
                        $dstImage = imagecreatetruecolor($dstW, $dstH);
                        preg_match('/#?(..)(..)(..)/i', $bgcolor ,$c);
                        imagefill($dstImage, 0, 0, imagecolorallocate($dstImage, intval($c[1], 16), intval($c[2], 16), intval($c[3], 16)));

                        if(strpos($position, 'L') !== FALSE){
                                $dstL = 0;
                        }else if(strpos($position, 'R') !== FALSE){
                                $dstL = $dstW - $rszW;
                        }else{
                                $dstL = intval(($dstW - $rszW) / 2);
                        }
                        if(strpos($position, 'T') !== FALSE){
                                $dstT = 0;
                        }else if(strpos($position, 'B') !== FALSE){
                                $dstT = $dstH - $rszH;
                        }else{
                                $dstT = intval(($dstH - $rszH) / 2);
                        }
                }

                imagecopyresampled($dstImage, self::imagecreatefrom($srcFile), $dstL, $dstT, 0, 0, $rszW, $rszH, $srcW, $srcH);
                return $dstImage;
        }

        /**
         * ファイル名の拡張子から推定したイメージリソースを作成する
         *
         * @param string $filename : ソースファイル名
         */
        function imagecreatefrom($filename)
        {
                $extension = self::getExtension($filename);

                if($extension == 'GIF'){
                        return imagecreatefromgif($filename);
                }else if($extension == 'PNG'){
                        return imagecreatefrompng($filename);
                }else if($extension == 'JPEG'){
                        return imagecreatefromjpeg($filename);
                }else{
                        return FALSE;
                }
        }
        /**
         * ファイル名の拡張子から推定したファイルを作成する
         *
         * @param imageResource $image : イメージリソース
         * @param string $filename : 出力先ファイル名
         */
        function imagecreateto($image, $filename=NULL)
        {
                $extension = self::getExtension($filename);
                if($extension === FALSE){
                        $extension = $filename;
                        $filename = NULL;
                }

                if($extension == 'GIF'){
                        return ($filename) ? imagegif($image, $filename) : imagegif($image);
                }else if($extension == 'PNG'){
                        return ($filename) ? imagepng($image, $filename) : imagepng($image);
                }else if($extension == 'JPEG'){
                        return ($filename) ? imagejpeg($image, $filename, 90) : imagejpeg($image, 90);
                }else{
                        return FALSE;
                }
        }

        function getExtension($filename)
        {
                if(mb_ereg('^.+\.([\w]+)$', $filename, $match)){
                        $extension = mb_strtolower($match[1]);
                }else{
                        return FALSE;
                }

                if($extension == 'gif'){
                        return 'GIF';
                }else if($extension == 'png'){
                        return 'PNG';
                }else if($extension == 'jpg' || $extension == 'jpeg'){
                        return 'JPEG';
                }else{
                        return FALSE;
                }
        }
}
