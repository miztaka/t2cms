<?php

/**
 * モバイルの絵文字などを変換するフィルターです。
 * Smartyにoutputfilterを登録します。
 *
 * @package     teeple.filter
 * @author      Mitsutaka Sato
 */
class Teeple_Filter_MobileContentsFilter extends Teeple_Filter
{
    
    /**
     * smartyに登録するoutputfilterです
     * @param unknown_type $source
     * @param unknown_type $Smarty
     */
    public static function outputfilter($source, &$Smarty) {
        $source = mb_convert_kana($source, 'k', 'sjis-win');
        return KetaiConverter::convert($source);
    }
    
    /**
     * コンストラクター
     *
     * @access  public
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Smartyにoutputfilterを登録します。
     *
     * @access  public
     */
    public function prefilter()
    {
        // pageのmobile_flgを見て実行するかどうか決める
        $action = $this->actionChain->getCurAction();
        $cmsAction = "Teeple_Cms_Action";
        if ($action instanceof $cmsAction && $action->_pageInfo->mobile_flg != 1) {
            // 適用しない
            return;
        }
        
        // linkにguid=ONを強制的につける
        output_add_rewrite_var('guid','ON');
        
        // Smartyにoutputfilterを登録
        $renderer = Teeple_Smarty4Maple::getInstance();
        $renderer->registerFilters();
        $renderer->register_outputfilter(array(__CLASS__, "outputfilter"));
        return;
    }
    
    public function postfilter() {}

}
?>