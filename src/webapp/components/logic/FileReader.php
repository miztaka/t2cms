<?php

interface Logic_FileReader
{
    /**
     * ファイルを読み込みオブジェクトの配列を返します。
     * @param $filename string ファイル名(パス)
     * @param $validate boolean バリデーションするかどうか
     * @return array オブジェクトのリスト
     */
    public function readFile($filename, $validate = true);

    /**
     * バリデーション定義をセットします。
     * 
     * @param $config string YAML形式のバリデーション定義
     * @return void
     */
    public function setValidationConfig($config);

}

?>