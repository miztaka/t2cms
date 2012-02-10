<?php

/**
 * 改行マーキングを改行文字に変換する
 */
class Teeple_Converter_N2nl extends Teeple_Converter_MbConvertBase
{
    
    protected function convertMethod($value) {
        return str_replace('|n|', "\n", $value);
    }
    
}
?>
