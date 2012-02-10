<?php
/**
 * ページプラグイン
 * @author miztaka
 * 
 * このクラスに用意したメソッドはテンプレートから、
 * $plugin->hogehoge() という形で呼び出すことができます。
 *
 */
class Plugin_PagePlugin
{
    
    /**
     * 現在表示しているページの情報
     * @var Entity_Page
     */
    public $pageInfo;

    //
    // 以下、プラグインメソッドを記述
    //
    
    /**
     * ページプラグインのサンプルです。
     * blog_categoriesというオブジェクトのレコード一覧を取得します。
     * 
     */
    /*
    public function blogCategories() {
        
        $eav = new Teeple_EavRecord('blog_categories');
        return $eav->select(true);
    }
    */
    
}
