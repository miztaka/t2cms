<?php

/**
 * ユーティリティクラスです。
 * @author miztaka
 *
 */
class U
{
    /**
     * 分の選択肢です。
     * @var unknown_type
     */
    public static $minuteOptions = array(
        '00' => '00',
        '10' => '10',
        '20' => '20',
        '30' => '30',
        '40' => '40',
        '50' => '50'
    );
    
    /**
     * 月、日、時、分など連続した数字の選択肢を作り出します。
     *  
     * @param int $start 始まりの値
     * @param int $end 終わりの値
     * @param mixed $blank 設定されている場合はブランクの選択肢を先頭に入れます。
     */
    public static function getDateArray($start, $end, $blank = FALSE) {
        
        $result = array();
        if ($blank) {
            $result[] = $blank;
        }
        for($i=$start; $i<=$end; $i++) {
            $result["{$i}"] = $i;
        }
        return $result;
    }
    
    /**
     * ランダムな文字列を生成します
     * @param int $len 長さ
     * @param string $kind 文字種を指定する
     * @return string
     */
    public static function randString($len=6, $kind='9aA') {
        
        // 表示する文字の設定
        $chars_ar = array(
            '9' => '0123456789',
            'a' => 'abcdefghijklmnopqrstuvwxyz',
            'A' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
        );
        $chars = "";
        for ($i=0; $i<strlen($kind); $i++) {
            $k = $kind{$i};
            $chars .= $chars_ar[$k];
        }

        $string = "";
        for ($j = 0; $j < $len; $j++){
            $pos = rand(0, strlen($chars)-1);
            $string .= $chars[$pos];
        }
        return $string;
    }
    
    /**
     * ケータイのアドレスかどうかをチェックします
     * @param string $address
     */
    public static function isMobileAddress($address) {
        
        $mobile_domain = array(
            'docomo.ne.jp',
            'ezweb.ne.jp',
            'disney.ne.jp',
            'softbank.ne.jp',
            'vodafone.ne.jp',
            'willcom.com',
            'pdx.ne.jp',
            'emnet.ne.jp'
        );
        $reg = implode('|', $mobile_domain);
        
        return preg_match("/({$reg})$/", $address);
    }
    
    /**
     * 指定した日から○日前、○日後を返します。
     * 
     * @param string $date  基準日
     * @param string $diff  日数差分　「+3」なら3日後
     * @return string
     */
    public static function dayFrom($date, $diff) {
        return Date('Y-m-d', strtotime("{$diff} day", strtotime($date)));
    }
    
    /**
     * 指定した日の前日を返します。
     * 
     * @param string $date  基準日
     * @return string
     */
    public static function previousDayOf($date) {
        return self::dayFrom($date, '-1');
    }
    
    /**
     * 指定した日の翌日を返します。
     * 
     * @param string $date  基準日
     * @return string
     */
    public static function nextDayOf($date) {
        return self::dayFrom($date, '+1');
    }
    
    /**
     * query文字列を作ります。
     * @param unknown_type $q
     */
    public static function getQueryString($q) {
        
        return http_build_query($q);
        /*
        $buff = array();
        foreach($q as $n => $v) {
            $buff[] = $n."=".urlencode($v);
        }
        return implode('&', $buff);
        */
    }
    
    /**
     * 日本語表記の週を取得します。
     * @param string $day 日付 (YYYY-MM-DD)
     * @param string $style short or long
     */
    public static function getJapaneseWeek($day, $style='short') {
        
        $short = array('日','月','火','水','木','金','土');
        $long = array('日曜日','月曜日','火曜日','水曜日','木曜日','金曜日','土曜日');
        
        $w = date('w', strtotime($day));
        return $style == 'short' ? $short[$w] : $long[$w];
    }
    
    /**
     * パスワードをハッシュします。
     * @param unknown $pass
     */
    public static function hashPassword($pass) {
    	return sha1($pass.PW_SALT);
    }
    
}

?>