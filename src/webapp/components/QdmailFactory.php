<?php

class QdmailFactory {
    
    /**
     * @return QdmailFactory
     */
    public static function instance() {
        return Teeple_Container::getInstance()->getComponent(__CLASS__);
    }    
    
    /**
     * @var Logger
     */
    protected $log;    

    /**
     * Teeple_Resource
     * @var Teeple_Resource
     */
    private $resource;
    public function setComponent_Teeple_Resource($c) {
        $this->resource = $c;
    }
    
    /**
     * コンストラクタです。
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }    

    /**
     * Qdmailのインスタンスを返却します。
     * @return Qdmail
     */
    public function getQdmail() {
        $qdmail = new Qdmail();
        $qdmail->errorDisplay(false);
        $qdmail->lineFeed("\n");
        $qdmail->charsetBody('utf-8', 'base64');
        $qdmail->kana(true);
        //$qdmail->addHeader('Errors-To', '');
        
        $from = $this->resource->getResource("mail.from");
        $name = $this->resource->getResource("mail.name");
        $qdmail->from($from, $name);
        $qdmail->mtaOption("-f$from");
        
        return $qdmail;
    }
    
    /**
     * テキストメールの送信を行ないます。
     * @param $data array
     * @param $subject string
     * @param $body string
     * @param $replace bool
     * @return bool
     */
    public function sendTextEmail($data, $subject, $body, $replace=true) {
        
        $qdmail = $this->getQdmail();
        $email = $data['mail'];
        // Willcomの場合は7bitでおくる
        if (strpos($email, 'pdx.ne.jp') !== FALSE ||
            strpos($email, 'willcom.com') !== FALSE) {
            $qdmail->charsetBody('iso-2022-jp', '7bit');
        }

        $qdmail->to($data);
        //$qdmail->addressField("mail","name");
        $qdmail->subject($subject);
        $qdmail->body("text", $body);
        if ($replace) {
            $qdmail->simpleReplace(true);
        }
        
        $result = $qdmail->send();
        if (! $result) {
            $this->log->error(var_export($qdmail->errorStatment()));
        }
        return $result;
    }

}

?>