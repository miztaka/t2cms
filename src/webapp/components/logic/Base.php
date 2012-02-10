<?php

class Logic_Base
{
    
    /**
     * @var Logger
     */
    protected $log;

    /**
     * コンストラクタです。
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }
    
    /**
     * @return Logic_Base
     */
    public static function neu() {
        return Teeple_Container::getInstance()->getComponent(__CLASS__);
    }

}
