<?php

//require_once 'Net/UserAgent/Mobile.php';

/**
 * ケータイサイト、店舗ツール、営業ツールのベース
 * @author miztaka
 *
 */
class MobileActionBase extends MyActionBase {
    
    const DOCTYPE_DOCOMO = '<!DOCTYPE html PUBLIC "-//i-mode group (ja)//DTD XHTML i-XHTML(Locale/Ver.=ja/2.0) 1.0//EN" "i-xhtml_4ja_10.dtd">';
    const DOCTYPE_KDDI = '<!DOCTYPE html PUBLIC "-//OPENWAVE//DTD XHTML 1.0//EN" "http://www.openwave.com/DTD/xhtml-basic.dtd">';
    const DOCTYPE_SOFTBANK = '<!DOCTYPE html PUBLIC "-//JPHONE//DTD XHTML Basic 1.0 Plus//EN" "xhtml-basic10-plus.dtd">';

    /**
     *
     * @var Net_UserAgent_Mobile_Common
     */
    protected $mobileAgent;
    
    public function __construct() {
        
        parent::__construct();;
        
        $this->mobileAgent = Net_UserAgent_Mobile::singleton();
        //if ($this->mobileAgent->isDoCoMo()) {
        //    header('Content-Type: application/xhtml+xml');
        //}
        
    }
    
    public function doctype() {
        switch (true) {
            case $this->mobileAgent->isDoCoMo():
                return self::DOCTYPE_DOCOMO;
            case $this->mobileAgent->isEZweb():
                return self::DOCTYPE_KDDI;
            case $this->mobileAgent->isSoftBank():
                return self::DOCTYPE_SOFTBANK;
            default:
                return self::DOCTYPE_DOCOMO;
        }
    }
    
}

?>