<?php
/**
 * ここにオブジェクトプラグインのクラスを定義します。
 * 
 * クラス名の命名規則: Plugin_Object_`オブジェクト名`
 * トリガーメソッド:
 * 　beforeInsert - 新規作成トリガー
 *   beforeUpdate - 更新トリガー
 *   ※引数: Teeple_EavRecordのオブジェクトが渡されます。
 * 入力値検証メソッド:
 *   validate     - 入力値検証処理を記述します。
 * 
 */

/**
 * オブジェクトプラグインクラスのサンプルです。
 * hogeという名前のオブジェクト用です。
 * @author miztaka
 *
 */
class Plugin_Object_hoge {
    
    /**
     * レコード作成時に実行されます。
     * @param $record Teeple_EavRecord
     */
    /*
    public function beforeInsert($record) {
        
        // 日付のデフォルト値をセット
        if (Teeple_Util::isBlank($record->date)) {
            $record->date = date('Y-m-d');
        }
        
        return;
    }
    */
    
    /**
     * 独自の入力値検証を行います。
     * エラーがある場合は $request->addErrorMessage() でエラーメッセージを追加します。
     * @param $action Teeple_ActionBase
     * @param $request Teeple_Request
     */
    /*
    public function validate($action, $request) {
        
        $request->addErrorMessage('プラグインで追加したエラーメッセージです。');
        return;
    }
    */
    
}

// End of File
