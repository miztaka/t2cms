<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * らくらくケータイコンバータ ver 0.11
 *
 * Developed on PHP versions 5.1.6
 *
 * @category   converter
 * @package    ke-tai
 * @author     松井　健太郎（matsui@ke-tai.org）
 * @copyright  ke-tai.org
 * @license    BSD License
 * @version    SVN: $Id$
 * @link       http://www.ke-tai.org/
 * @see
 * @since
 * @deprecated
 *
 * 修正履歴:
 * 2009/11/14 新規作成
 * 2009/11/21 REQUEST_URIをエスケープするようにした
 */

if (preg_match('/viewer\.php$/', $_SERVER['PHP_SELF'])) {
	// 直接呼び出されたときだけ起動
	KetaiConverter::directOutput();
}


/**
 * コンバータクラス
 */
class KetaiConverter {

	/**
	 * 出力メソッド（直接起動用）
	 */
	function directOutput() {
		// 表示ページを取得
		$page = $_GET['p'];
		if ('' == $page or false !== strpos($page, '..')) {
			// 403エラーを返す
			KetaiConverter::outputHttpStatus(403);
		}
		$page = './' . $page . '.html';
		
		if (!file_exists($page)) {
			// 404エラーを返す
			KetaiConverter::outputHttpStatus(404);
		}
		
		// ページ内容を取得
		$output = file_get_contents($page);
		
		// 変換
		$output = KetaiConverter::convert($output);
		
		// ページを出力
		print $output;
	}


	/**
	 * 変換メソッド
	 *
	 * @param	string		$output			変換対象文字列
	 * @param	string		$agent			変換ルールを決めるユーザエージェント
	 * @return	string						変換後の文字列
	 */
	function convert($output, $agent = '')
	{
		// 文字コードを取得
		$encoding = mb_detect_encoding($output);
		
		// キャリアを取得
		if ('' == $agent) {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}
		$carrier = KetaiConverter::getCarrier($agent);
		
		// 格納ディレクトリを取得
		$basedir = dirname(__FILE__) . '/';
		
		if ($carrier == 'DoCoMo') {
			// ドコモの場合、XHTML用のヘッダを出力
			header('Content-type: application/xhtml+xml');
			
			// formのactionにguid=ONを付与
			$output = KetaiConverter::convert_form_action($output);
			
			// HTML_CSS_Mobileがあればそれを読み込み
			/*
			$html_css_mobile_path = $basedir . 'HTML/CSS/Mobile.php';
			if (file_exists($html_css_mobile_path)) {
				// CSS変換処理
				require_once($html_css_mobile_path);
				try {
					$output = HTML_CSS_Mobile::getInstance()->setBaseDir('./')->setMode('strict')->apply($output);
				} catch (Exception $e) {
					var_dump($e->getMessage());
					exit;
				}
			}
			*/
			
			// 入力モード指定の変換を行う
			//$output = KetaiConverter::istyle_imode($output);
		} elseif ($carrier == 'EZweb') {
		    /*
			if ($encoding == 'UTF-8') {
				// auの絵文字変換処理（UTF-8用）を行う
				$output = KetaiConverter::emoji_i2ez_utf8($output);
			} else {
				// auの絵文字変換処理（SJIS用）を行う
				$output = KetaiConverter::emoji_i2ez_sjis($output);
			}
			*/
		    $output = KetaiConverter::emoji_i2ez_sjis($output);
		    
		} elseif ($carrier == 'SoftBank') {
			// ソフトバンクの絵文字変換処理を行う
			$output = KetaiConverter::emoji_i2sb($output);
		}
		
		// istyleの変換
		$output = KetaiConverter::convert_istyle($output, $agent);
		return $output;
	}

	/**
	 * HTTPステータス出力メソッド
	 *
	 * @param	int		$code			ステータスコード
	 */
	function outputHttpStatus($code)
	{
		switch ($code) {
			case 403:
				$header_str = 'HTTP/1.0 403 Forbidden';
				$title = '403 Forbidden';
				$message = 'You don\'t have permission to access ' . htmlspecialchars($_SERVER['REQUEST_URI']) . ' on this server.';
				break;
				
			case 404:
				$header_str = 'HTTP/1.0 404 Not Found';
				$title = '404 Not Found';
				$message = 'The requested URL ' . htmlspecialchars($_SERVER['REQUEST_URI']) . ' was not found on this server.';
				break;
				
			default:
				exit;
		}
		
		header($header_str, true);
		$html = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' . "\n" . 
				'<html><head>' . "\n" . 
				'<title>' . $title . '</title>' . "\n" . 
				'</head><body>' . "\n" . 
				'<h1>' . $title . '</h1>' . "\n" . 
				'<p>' . $message . '</p>' . "\n" . 
				'</body>' . "\n" . 
				'</html>';
		print $html;
		exit;
	}


	/**
	 * キャリア判定
	 *
	 * @param	string	$agent			ユーザエージェント
	 *
	 * @return	string					キャリア
	 */
	function getCarrier($agent)
	{
		if (preg_match('/^J-PHONE/', $agent) or preg_match('/^Vodafone/', $agent) or preg_match('/^SoftBank/', $agent) or preg_match('/^MOT-/', $agent)) {
			// J-PHONE, Vodafone, SoftBankの場合
			return 'SoftBank';
		} elseif (preg_match('/^DoCoMo/', $agent)) {
			// DoCoMoの場合
			return 'DoCoMo';
		} elseif (preg_match('/^KDDI/', $agent)) {
			// auの場合
			return 'EZweb';
		} else {
			// その他(PC等)
			return 'NonMobile';
		}
	}


    /**
     * 入力モード指定変換（iモード用）
     *
     * iモード用の入力モードの変換を行う
     *
     * @param   string  $input  変換対象の文字列
     *
     * @return  string          変換後の文字列
     */
    function istyle_imode($input)
    {
        $rep_arr = array(
            'istyle="1"' => 'istyle="1" style="-wap-input-format:&quot;*&lt;ja:h&gt;&quot;;"', 
            'istyle="2"' => 'istyle="2" style="-wap-input-format:&quot;*&lt;ja:hk&gt;&quot;;"', 
            'istyle="3"' => 'istyle="3" style="-wap-input-format:&quot;*&lt;ja:en&gt;&quot;;"', 
            'istyle="4"' => 'istyle="4" style="-wap-input-format:&quot;*&lt;ja:n&gt;&quot;;"'
        );
        
        $output = $input;
        preg_match_all('/<input (?:.*)(istyle=("|\')?[1-4](?:"|\')?)(?: (?:.*?))?>/i', $input, $arr);       // inputタグを検索し、配列に格納
        for ($i = 0; $i < count($arr[0]); $i++) {
            // 必要な変数を取得
            $target = $arr[0][$i];          // 置換対象
            $istyle = $arr[1][$i];          // istyle属性
            $quote = $arr[2][$i];           // クォート
            $replacement = strtr($target, $rep_arr);
            $output = str_replace($target, $replacement, $output);
        }
        
        return $output;
    }
	
    /**
     * 入力モード指定変換（全キャリア）
     *
     * iモード用の入力モードの変換を行う
     *
     * @param   string  $input  変換対象の文字列
     *
     * @return  string          変換後の文字列
     */
    function convert_istyle($input, $agent)
    {
        $rep_arr_master = array(
            'DoCoMo' => array(
                'istyle="1"' => 'istyle="1" style="-wap-input-format:&quot;*&lt;ja:h&gt;&quot;;"',
                'istyle="2"' => 'istyle="2" style="-wap-input-format:&quot;*&lt;ja:hk&gt;&quot;;"',
                'istyle="3"' => 'istyle="3" style="-wap-input-format:&quot;*&lt;ja:en&gt;&quot;;"',
                'istyle="4"' => 'istyle="4" style="-wap-input-format:&quot;*&lt;ja:n&gt;&quot;;"'
            ),
            'SoftBank' => array(
                'istyle="1"' => 'mode="hiragana" istyle="1" format="*M"',
                'istyle="2"' => 'mode="hankakukana" istyle="2" format="*M"',
                'istyle="3"' => 'mode="alphabet" istyle="3" format="*x"',
                'istyle="4"' => 'mode="numeric" istyle="4" format="*N"'
            ),
            'EZweb' => array(
                'istyle="1"' => 'istyle="1" format="*M"',
                'istyle="2"' => 'istyle="2" format="*M"',
                'istyle="3"' => 'istyle="3" format="*x"',
                'istyle="4"' => 'istyle="4" format="*N"'
            ),
        );
        
        $carrier = KetaiConverter::getCarrier($agent);
        if (! preg_match('/DoCoMo|SoftBank|EZweb/', $carrier)) {
            return $input;
        }
        $rep_arr = $rep_arr_master[$carrier];
        
        $output = $input;
        preg_match_all('/<input (?:.*)(istyle=("|\')?[1-4](?:"|\')?)(?: (?:.*?))?>/i', $input, $arr);       // inputタグを検索し、配列に格納
        for ($i = 0; $i < count($arr[0]); $i++) {
            // 必要な変数を取得
            $target = $arr[0][$i];          // 置換対象
            $istyle = $arr[1][$i];          // istyle属性
            $quote = $arr[2][$i];           // クォート
            $replacement = strtr($target, $rep_arr);
            $output = str_replace($target, $replacement, $output);
        }
        
        return $output;
    }
    
    /**
     * フォームのactionにguid=ONを付加します。
     * @param $input
     */
    function convert_form_action($input)
    {
        $output = $input;
        preg_match_all('/<form (?:.*)action=(?:"|\')?([^"\' ]+)(?:"|\')?[^>]*>/i', $input, $arr);       // <form>タグを検索し、配列に格納
        for ($i = 0; $i < count($arr[0]); $i++) {
            // 必要な変数を取得
            $target = $arr[0][$i];          // 置換対象
            $url = $arr[1][$i];             // istyle属性
            $url_guid = $url."?guid=ON";
            $p = strpos($url, '?');
            if ($p !== FALSE) {
                if ($p == strlen($url)-1) {
                    $url_guid = $url."guid=ON";
                } else {
                    $url_guid = $url."&guid=ON";
                }
            }
            $replacement = str_replace($url, $url_guid, $target);
            $output = str_replace($target, $replacement, $output);
        }
        return $output;
    }

	/**
	 * 絵文字変換（iモード→EZweb SJIS）
	 *
	 * iモード用からEZweb用に絵文字を変換する。（SJIS用）
	 *
	 * @param	string	$input	変換対象の文字列
	 *
	 * @return	string			変換後の文字列
	 */
	function emoji_i2ez_sjis($input)
	{
		$emoji_i2ez = array(
				'&#xE63E;' => '&#xE488;', 
				'&#xE63F;' => '&#xE48D;', 
				'&#xE640;' => '&#xE48C;', 
				'&#xE641;' => '&#xE485;', 
				'&#xE642;' => '&#xE487;', 
				'&#xE643;' => '&#xE469;', 
				'&#xE644;' => '&#xE598;', 
				'&#xE645;' => '&#xEAE8;', 
				'&#xE646;' => '&#xE48F;', 
				'&#xE647;' => '&#xE490;', 
				'&#xE648;' => '&#xE491;', 
				'&#xE649;' => '&#xE492;', 
				'&#xE64A;' => '&#xE493;', 
				'&#xE64B;' => '&#xE494;', 
				'&#xE64C;' => '&#xE495;', 
				'&#xE64D;' => '&#xE496;', 
				'&#xE64E;' => '&#xE497;', 
				'&#xE64F;' => '&#xE498;', 
				'&#xE650;' => '&#xE499;', 
				'&#xE651;' => '&#xE49A;', 
				'&#xE652;' => '&#xE46B;', 
				'&#xE653;' => '&#xE4BA;', 
				'&#xE654;' => '&#xE599;', 
				'&#xE655;' => '&#xE4B7;', 
				'&#xE656;' => '&#xE4B6;', 
				'&#xE657;' => '&#xEAAC;', 
				'&#xE658;' => '&#xE59A;', 
				'&#xE659;' => '&#xE4B9;', 
				'&#xE65A;' => '&#xE59B;', 
				'&#xE65B;' => '&#xE4B5;', 
				'&#xE65C;' => '&#xE5BC;', 
				'&#xE65D;' => '&#xE4B0;', 
				'&#xE65E;' => '&#xE4B1;', 
				'&#xE65F;' => '&#xE4B1;', 
				'&#xE660;' => '&#xE4AF;', 
				'&#xE661;' => '&#xEA82;', 
				'&#xE662;' => '&#xE4B3;', 
				'&#xE663;' => '&#xE4AB;', 
				'&#xE664;' => '&#xE4AD;', 
				'&#xE665;' => '&#xE5DE;', 
				'&#xE666;' => '&#xE5DF;', 
				'&#xE667;' => '&#xE4AA;', 
				'&#xE668;' => '&#xE4A3;', 
				'&#xE669;' => '&#xEA81;', 
				'&#xE66A;' => '&#xE4A4;', 
				'&#xE66B;' => '&#xE571;', 
				'&#xE66C;' => '&#xE4A6;', 
				'&#xE66D;' => '&#xE46A;', 
				'&#xE66E;' => '&#xE4A5;', 
				'&#xE66F;' => '&#xE4AC;', 
				'&#xE670;' => '&#xE597;', 
				'&#xE671;' => '&#xE4C2;', 
				'&#xE672;' => '&#xE4C3;', 
				'&#xE673;' => '&#xE4D6;', 
				'&#xE674;' => '&#xE51A;', 
				'&#xE675;' => '&#xE516;', 
				'&#xE676;' => '&#xE503;', 
				'&#xE677;' => '&#xE517;', 
				'&#xE678;' => '&#xE555;', 
				'&#xE679;' => '&#xE46D;', 
				'&#xE67A;' => '&#xE508;', 
				'&#xE67B;' => '&#xE59C;', 
				'&#xE67C;' => '&#xEAF5;', 
				'&#xE67D;' => '&#xE59E;', 
				'&#xE67E;' => '&#xE49E;', 
				'&#xE67F;' => '&#xE47D;', 
				'&#xE680;' => '&#xE47E;', 
				'&#xE681;' => '&#xE515;', 
				'&#xE682;' => '&#xE49C;', 
				'&#xE683;' => '&#xE49F;', 
				'&#xE684;' => '&#xE59F;', 
				'&#xE685;' => '&#xE4CF;', 
				'&#xE686;' => '&#xE5A0;', 
				'&#xE687;' => '&#xE596;', 
				'&#xE688;' => '&#xE588;', 
				'&#xE689;' => '&#xE561;', 
				'&#xE68A;' => '&#xE502;', 
				'&#xE68B;' => '&#xE4C6;', 
				'&#xE68C;' => '&#xE50C;', 
				'&#xE68D;' => '&#xEAA5;', 
				'&#xE68E;' => '&#xE5A1;', 
				'&#xE68F;' => '&#xE5A2;', 
				'&#xE690;' => '&#xE5A3;', 
				'&#xE691;' => '&#xE5A4;', 
				'&#xE692;' => '&#xE5A5;', 
				'&#xE693;' => '&#xEB83;', 
				'&#xE694;' => '&#xE5A6;', 
				'&#xE695;' => '&#xE5A7;', 
				'&#xE696;' => '&#xE54D;', 
				'&#xE697;' => '&#xE54C;', 
				'&#xE698;' => '&#xEB2A;', 
				'&#xE699;' => '&#xEB2B;', 
				'&#xE69A;' => '&#xE4FE;', 
				'&#xE69B;' => '&#xE47F;', 
				'&#xE69C;' => '&#xE5A8;', 
				'&#xE69D;' => '&#xE5A9;', 
				'&#xE69E;' => '&#xE5AA;', 
				'&#xE69F;' => '&#xE486;', 
				'&#xE6A0;' => '&#xE489;', 
				'&#xE6A1;' => '&#xE4E1;', 
				'&#xE6A2;' => '&#xE4DB;', 
				'&#xE6A3;' => '&#xE4B4;', 
				'&#xE6A4;' => '&#xE4C9;', 
				'&#xE6A5;' => '&#xE556;', 
				'&#xE6CE;' => '&#xEB08;', 
				'&#xE6CF;' => '&#xEB62;', 
				'&#xE6D0;' => '&#xE520;', 
				'&#xE6D1;' => 'ｉ', 
				'&#xE6D2;' => 'ｉ', 
				'&#xE6D3;' => '&#xE521;', 
				'&#xE6D4;' => 'Ｄ', 
				'&#xE6D5;' => 'Ｄ', 
				'&#xE6D6;' => '&#xE57D;', 
				'&#xE6D7;' => '&#xE578;', 
				'&#xE6D8;' => '&#xEA88;', 
				'&#xE6D9;' => '&#xE519;', 
				'&#xE6DA;' => '&#xE55D;', 
				'&#xE6DB;' => '&#xE5AB;', 
				'&#xE6DC;' => '&#xE518;', 
				'&#xE6DD;' => '&#xE5B5;', 
				'&#xE6DE;' => '&#xEB2C;', 
				'&#xE6DF;' => 'Ｆ', 
				'&#xE6E0;' => '&#xEB84;', 
				'&#xE6E1;' => '&#xE52C;', 
				'&#xE6E2;' => '&#xE522;', 
				'&#xE6E3;' => '&#xE523;', 
				'&#xE6E4;' => '&#xE524;', 
				'&#xE6E5;' => '&#xE525;', 
				'&#xE6E6;' => '&#xE526;', 
				'&#xE6E7;' => '&#xE527;', 
				'&#xE6E8;' => '&#xE528;', 
				'&#xE6E9;' => '&#xE529;', 
				'&#xE6EA;' => '&#xE52A;', 
				'&#xE6EB;' => '&#xE5AC;', 
				'&#xE70B;' => '&#xE5AD;', 
				'&#xE6EC;' => '&#xE595;', 
				'&#xE6ED;' => '&#xEB75;', 
				'&#xE6EE;' => '&#xE477;', 
				'&#xE6EF;' => '&#xE478;', 
				'&#xE6F0;' => '&#xE471;', 
				'&#xE6F1;' => '&#xE472;', 
				'&#xE6F2;' => '&#xE474;', 
				'&#xE6F3;' => '&#xE473;', 
				'&#xE6F4;' => '&#xE5AE;', 
				'&#xE6F5;' => '&#xEB2D;', 
				'&#xE6F6;' => '&#xE5BE;', 
				'&#xE6F7;' => '&#xE4BC;', 
				'&#xE6F8;' => '&#xE536;', 
				'&#xE6F9;' => '&#xE4EB;', 
				'&#xE6FA;' => '&#xEAAB;', 
				'&#xE6FB;' => '&#xE476;', 
				'&#xE6FC;' => '&#xE4E5;', 
				'&#xE6FD;' => '&#xE4F3;', 
				'&#xE6FE;' => '&#xE47A;', 
				'&#xE6FF;' => '&#xE505;', 
				'&#xE700;' => '&#xEB2E;', 
				'&#xE701;' => '&#xE475;', 
				'&#xE702;' => '&#xE482;', 
				'&#xE703;' => '&#xEB2F;', 
				'&#xE704;' => '&#xEB30;', 
				'&#xE705;' => '&#xE5B0;', 
				'&#xE706;' => '&#xE5B1;', 
				'&#xE707;' => '&#xE4E6;', 
				'&#xE708;' => '&#xE4F4;', 
				'&#xE709;' => '&#xEB7C;', 
				'&#xE70A;' => '&#xEB31;', 
				'&#xE6AC;' => '&#xE4BE;', 
				'&#xE6AD;' => '&#xE4C7;', 
				'&#xE6AE;' => '&#xEB03;', 
				'&#xE6B1;' => '&#xE4FC;', 
				'&#xE6B2;' => '　', 
				'&#xE6B3;' => '&#xEAF1;', 
				'&#xE6B7;' => '&#xE552;', 
				'&#xE6B8;' => '&#xEB7A;', 
				'&#xE6B9;' => '&#xE553;', 
				'&#xE6BA;' => '&#xE594;', 
				'&#xE70C;' => 'α', 
				'&#xE70D;' => 'α', 
				'&#xE70E;' => '&#xE5B6;', 
				'&#xE70F;' => '&#xE504;', 
				'&#xE710;' => '&#xE509;', 
				'&#xE711;' => '&#xEB77;', 
				'&#xE712;' => '&#xE4B8;', 
				'&#xE713;' => '&#xE512;', 
				'&#xE714;' => '　', 
				'&#xE715;' => '&#xE4C7;', 
				'&#xE716;' => '&#xE5B8;', 
				'&#xE717;' => '&#xEB78;', 
				'&#xE718;' => '&#xE587;', 
				'&#xE719;' => '&#xE4A1;', 
				'&#xE71A;' => '&#xE5C9;', 
				'&#xE71B;' => '&#xE514;', 
				'&#xE71C;' => '&#xE47C;', 
				'&#xE71D;' => '&#xE4AE;', 
				'&#xE71E;' => '&#xEAAE;', 
				'&#xE71F;' => '&#xE57A;', 
				'&#xE720;' => '&#xEAC2;', 
				'&#xE721;' => '&#xEACD;', 
				'&#xE722;' => '&#xE5C6;', 
				'&#xE723;' => '&#xE5C6;', 
				'&#xE724;' => '&#xEB5D;', 
				'&#xE725;' => '&#xEB67;', 
				'&#xE726;' => '&#xE5C4;', 
				'&#xE727;' => '&#xE4F9;', 
				'&#xE728;' => '&#xE4E7;', 
				'&#xE729;' => '&#xE5C3;', 
				'&#xE72A;' => '&#xE471;', 
				'&#xE72B;' => '&#xE474;', 
				'&#xE72C;' => '&#xEB61;', 
				'&#xE72D;' => '&#xE473;', 
				'&#xE72E;' => '&#xEB69;', 
				'&#xE72F;' => '　', 
				'&#xE730;' => '&#xE4A0;', 
				'&#xE731;' => '&#xE558;', 
				'&#xE732;' => '&#xE54E;', 
				'&#xE733;' => '&#xE46B;', 
				'&#xE734;' => '&#xE4F1;', 
				'&#xE735;' => '&#xEB79;', 
				'&#xE736;' => '&#xE559;', 
				'&#xE737;' => '&#xE481;', 
				'&#xE738;' => '禁', 
				'&#xE739;' => '&#xEA8A;', 
				'&#xE73A;' => '合', 
				'&#xE73B;' => '&#xEA89;', 
				'&#xE73C;' => '&#xEB7A;', 
				'&#xE73D;' => '&#xEB7B;', 
				'&#xE73E;' => '&#xEA80;', 
				'&#xE73F;' => '&#xEB7C;', 
				'&#xE740;' => '&#xE5BD;', 
				'&#xE741;' => '&#xE513;', 
				'&#xE742;' => '&#xE4D2;', 
				'&#xE743;' => '&#xE4E4;', 
				'&#xE744;' => '&#xEB35;', 
				'&#xE745;' => '&#xEAB9;', 
				'&#xE746;' => '&#xEB7D;', 
				'&#xE747;' => '&#xE4CE;', 
				'&#xE748;' => '&#xE4CA;', 
				'&#xE749;' => '&#xE4D5;', 
				'&#xE74A;' => '&#xE4D0;', 
				'&#xE74B;' => '&#xEA97;', 
				'&#xE74C;' => '&#xEAB4;', 
				'&#xE74D;' => '&#xEAAF;', 
				'&#xE74E;' => '&#xEB7E;', 
				'&#xE74F;' => '&#xE4E0;', 
				'&#xE750;' => '&#xE4DC;', 
				'&#xE751;' => '&#xE49A;', 
				'&#xE752;' => '&#xEACD;', 
				'&#xE753;' => '&#xEB80;', 
				'&#xE754;' => '&#xE4D8;', 
				'&#xE755;' => '&#xEB48;', 
				'&#xE756;' => '&#xE4C1;', 
				'&#xE757;' => '&#xE5C5;'
		);
		
		// 変換処理
		$output = strtr($input, $emoji_i2ez);
		
		return $output;
	}


	/**
	 * 絵文字変換（iモード→EZweb UTF-8）
	 *
	 * iモード用からEZweb用に絵文字を変換する。（UTF-8用）
	 *
	 * @param	string	$input	変換対象の文字列
	 *
	 * @return	string			変換後の文字列
	 */
	function emoji_i2ez_utf8($input)
	{
		$emoji_i2ez = array(
			'&#xE63E;' => sprintf("\xEE\xBD\xA0"), 
			'&#xE63F;' => sprintf("\xEE\xBD\xA5"), 
			'&#xE640;' => sprintf("\xEE\xBD\xA4"), 
			'&#xE641;' => sprintf("\xEE\xBD\x9D"), 
			'&#xE642;' => sprintf("\xEE\xBD\x9F"), 
			'&#xE643;' => sprintf("\xEE\xBD\x81"), 
			'&#xE644;' => sprintf("\xEF\x82\xB5"), 
			'&#xE645;' => sprintf("\xEE\xB2\xBC"), 
			'&#xE646;' => sprintf("\xEE\xBD\xA7"), 
			'&#xE647;' => sprintf("\xEE\xBD\xA8"), 
			'&#xE648;' => sprintf("\xEE\xBD\xA9"), 
			'&#xE649;' => sprintf("\xEE\xBD\xAA"), 
			'&#xE64A;' => sprintf("\xEE\xBD\xAB"), 
			'&#xE64B;' => sprintf("\xEE\xBD\xAC"), 
			'&#xE64C;' => sprintf("\xEE\xBD\xAD"), 
			'&#xE64D;' => sprintf("\xEE\xBD\xAE"), 
			'&#xE64E;' => sprintf("\xEE\xBD\xAF"), 
			'&#xE64F;' => sprintf("\xEE\xBD\xB0"), 
			'&#xE650;' => sprintf("\xEE\xBD\xB1"), 
			'&#xE651;' => sprintf("\xEE\xBD\xB2"), 
			'&#xE652;' => sprintf("\xEE\xBD\x83"), 
			'&#xE653;' => sprintf("\xEE\xBE\x93"), 
			'&#xE654;' => sprintf("\xEF\x82\xB6"), 
			'&#xE655;' => sprintf("\xEE\xBE\x90"), 
			'&#xE656;' => sprintf("\xEE\xBE\x8F"), 
			'&#xE657;' => sprintf("\xEE\xB2\x80"), 
			'&#xE658;' => sprintf("\xEF\x82\xB7"), 
			'&#xE659;' => sprintf("\xEE\xBE\x92"), 
			'&#xE65A;' => sprintf("\xEF\x82\xB8"), 
			'&#xE65B;' => sprintf("\xEE\xBE\x8E"), 
			'&#xE65C;' => sprintf("\xEF\x83\xAC"), 
			'&#xE65D;' => sprintf("\xEE\xBE\x89"), 
			'&#xE65E;' => sprintf("\xEE\xBE\x8A"), 
			'&#xE65F;' => sprintf("\xEE\xBE\x8A"), 
			'&#xE660;' => sprintf("\xEE\xBE\x88"), 
			'&#xE661;' => sprintf("\xEE\xB1\x95"), 
			'&#xE662;' => sprintf("\xEE\xBE\x8C"), 
			'&#xE663;' => sprintf("\xEE\xBE\x84"), 
			'&#xE664;' => sprintf("\xEE\xBE\x86"), 
			'&#xE665;' => sprintf("\xEE\xB1\x91"), 
			'&#xE666;' => sprintf("\xEE\xB1\x92"), 
			'&#xE667;' => sprintf("\xEE\xBE\x83"), 
			'&#xE668;' => sprintf("\xEE\xBD\xBB"), 
			'&#xE669;' => sprintf("\xEE\xB1\x94"), 
			'&#xE66A;' => sprintf("\xEE\xBD\xBC"), 
			'&#xE66B;' => sprintf("\xEF\x82\x8E"), 
			'&#xE66C;' => sprintf("\xEE\xBD\xBE"), 
			'&#xE66D;' => sprintf("\xEE\xBD\x82"), 
			'&#xE66E;' => sprintf("\xEE\xBD\xBD"), 
			'&#xE66F;' => sprintf("\xEE\xBE\x85"), 
			'&#xE670;' => sprintf("\xEF\x82\xB4"), 
			'&#xE671;' => sprintf("\xEE\xBE\x9B"), 
			'&#xE672;' => sprintf("\xEE\xBE\x9C"), 
			'&#xE673;' => sprintf("\xEE\xBE\xAF"), 
			'&#xE674;' => sprintf("\xEE\xBF\xB3"), 
			'&#xE675;' => sprintf("\xEE\xBF\xAF"), 
			'&#xE676;' => sprintf("\xEE\xBF\x9C"), 
			'&#xE677;' => sprintf("\xEE\xBF\xB0"), 
			'&#xE678;' => sprintf("\xEF\x81\xB1"), 
			'&#xE679;' => sprintf("\xEE\xBD\x85"), 
			'&#xE67A;' => sprintf("\xEE\xBF\xA1"), 
			'&#xE67B;' => sprintf("\xEF\x82\xB9"), 
			'&#xE67C;' => sprintf("\xEF\x82\xBA"), 
			'&#xE67D;' => sprintf("\xEF\x82\xBB"), 
			'&#xE67E;' => sprintf("\xEE\xBD\xB6"), 
			'&#xE67F;' => sprintf("\xEE\xBD\x95"), 
			'&#xE680;' => sprintf("\xEE\xBD\x96"), 
			'&#xE681;' => sprintf("\xEE\xBF\xAE"), 
			'&#xE682;' => sprintf("\xEE\xBD\xB4"), 
			'&#xE683;' => sprintf("\xEE\xBD\xB7"), 
			'&#xE684;' => sprintf("\xEF\x82\xBC"), 
			'&#xE685;' => sprintf("\xEE\xBE\xA8"), 
			'&#xE686;' => sprintf("\xEF\x82\xBD"), 
			'&#xE687;' => sprintf("\xEF\x82\xB3"), 
			'&#xE688;' => sprintf("\xEF\x82\xA5"), 
			'&#xE689;' => sprintf("\xEF\x81\xBD"), 
			'&#xE68A;' => sprintf("\xEE\xBF\x9B"), 
			'&#xE68B;' => sprintf("\xEE\xBE\x9F"), 
			'&#xE68C;' => sprintf("\xEE\xBF\xA5"), 
			'&#xE68D;' => sprintf("\xEE\xB1\xB8"), 
			'&#xE68E;' => sprintf("\xEF\x82\xBE"), 
			'&#xE68F;' => sprintf("\xEF\x82\xBF"), 
			'&#xE690;' => sprintf("\xEF\x83\x80"), 
			'&#xE691;' => sprintf("\xEF\x83\x81"), 
			'&#xE692;' => sprintf("\xEF\x83\x82"), 
			'&#xE693;' => sprintf("\xEE\xB6\x88"), 
			'&#xE694;' => sprintf("\xEF\x83\x83"), 
			'&#xE695;' => sprintf("\xEF\x83\x84"), 
			'&#xE696;' => sprintf("\xEF\x81\xA9"), 
			'&#xE697;' => sprintf("\xEF\x81\xA8"), 
			'&#xE698;' => sprintf("\xEE\xB3\xAB"), 
			'&#xE699;' => sprintf("\xEE\xB3\xAC"), 
			'&#xE69A;' => sprintf("\xEE\xBF\x97"), 
			'&#xE69B;' => sprintf("\xEE\xBD\x97"), 
			'&#xE69C;' => sprintf("\xEF\x83\x85"), 
			'&#xE69D;' => sprintf("\xEF\x83\x86"), 
			'&#xE69E;' => sprintf("\xEF\x83\x87"), 
			'&#xE69F;' => sprintf("\xEE\xBD\x9E"), 
			'&#xE6A0;' => sprintf("\xEE\xBD\xA1"), 
			'&#xE6A1;' => sprintf("\xEE\xBE\xBA"), 
			'&#xE6A2;' => sprintf("\xEE\xBE\xB4"), 
			'&#xE6A3;' => sprintf("\xEE\xBE\x8D"), 
			'&#xE6A4;' => sprintf("\xEE\xBE\xA2"), 
			'&#xE6A5;' => sprintf("\xEF\x81\xB2"), 
			'&#xE6CE;' => sprintf("\xEF\x83\x9F"), 
			'&#xE6CF;' => sprintf("\xEE\xB5\xA6"), 
			'&#xE6D0;' => sprintf("\xEE\xBF\xB9"), 
			'&#xE6D1;' => 'ｉ', 
			'&#xE6D2;' => 'ｉ', 
			'&#xE6D3;' => sprintf("\xEE\xBF\xBA"), 
			'&#xE6D4;' => 'Ｄ', 
			'&#xE6D5;' => 'Ｄ', 
			'&#xE6D6;' => sprintf("\xEF\x82\x9A"), 
			'&#xE6D7;' => sprintf("\xEF\x82\x95"), 
			'&#xE6D8;' => sprintf("\xEE\xB1\x9B"), 
			'&#xE6D9;' => sprintf("\xEE\xBF\xB2"), 
			'&#xE6DA;' => sprintf("\xEF\x81\xB9"), 
			'&#xE6DB;' => sprintf("\xEF\x83\x88"), 
			'&#xE6DC;' => sprintf("\xEE\xBF\xB1"), 
			'&#xE6DD;' => sprintf("\xEF\x83\xA5"), 
			'&#xE6DE;' => sprintf("\xEE\xB3\xAD"), 
			'&#xE6DF;' => 'Ｆ', 
			'&#xE6E0;' => sprintf("\xEE\xB6\x89"), 
			'&#xE6E1;' => sprintf("\xEF\x81\x88"), 
			'&#xE6E2;' => sprintf("\xEE\xBF\xBB"), 
			'&#xE6E3;' => sprintf("\xEE\xBF\xBC"), 
			'&#xE6E4;' => sprintf("\xEF\x81\x80"), 
			'&#xE6E5;' => sprintf("\xEF\x81\x81"), 
			'&#xE6E6;' => sprintf("\xEF\x81\x82"), 
			'&#xE6E7;' => sprintf("\xEF\x81\x83"), 
			'&#xE6E8;' => sprintf("\xEF\x81\x84"), 
			'&#xE6E9;' => sprintf("\xEF\x81\x85"), 
			'&#xE6EA;' => sprintf("\xEF\x81\x86"), 
			'&#xE6EB;' => sprintf("\xEF\x83\x89"), 
			'&#xE70B;' => sprintf("\xEF\x83\x8A"), 
			'&#xE6EC;' => sprintf("\xEF\x82\xB2"), 
			'&#xE6ED;' => sprintf("\xEE\xB5\xB9"), 
			'&#xE6EE;' => sprintf("\xEE\xBD\x8F"), 
			'&#xE6EF;' => sprintf("\xEE\xBD\x90"), 
			'&#xE6F0;' => sprintf("\xEE\xBD\x89"), 
			'&#xE6F1;' => sprintf("\xEE\xBD\x8A"), 
			'&#xE6F2;' => sprintf("\xEE\xBD\x8C"), 
			'&#xE6F3;' => sprintf("\xEE\xBD\x8B"), 
			'&#xE6F4;' => sprintf("\xEF\x83\x8B"), 
			'&#xE6F5;' => sprintf("\xEE\xB3\xAE"), 
			'&#xE6F6;' => sprintf("\xEF\x83\xAE"), 
			'&#xE6F7;' => sprintf("\xEE\xBE\x95"), 
			'&#xE6F8;' => sprintf("\xEF\x81\x92"), 
			'&#xE6F9;' => sprintf("\xEE\xBF\x84"), 
			'&#xE6FA;' => sprintf("\xEE\xB1\xBE"), 
			'&#xE6FB;' => sprintf("\xEE\xBD\x8E"), 
			'&#xE6FC;' => sprintf("\xEE\xBE\xBE"), 
			'&#xE6FD;' => sprintf("\xEE\xBF\x8C"), 
			'&#xE6FE;' => sprintf("\xEE\xBD\x92"), 
			'&#xE6FF;' => sprintf("\xEE\xBF\x9E"), 
			'&#xE700;' => sprintf("\xEE\xB3\xAF"), 
			'&#xE701;' => sprintf("\xEE\xBD\x8D"), 
			'&#xE702;' => sprintf("\xEE\xBD\x9A"), 
			'&#xE703;' => sprintf("\xEE\xB3\xB0"), 
			'&#xE704;' => sprintf("\xEE\xB3\xB1"), 
			'&#xE705;' => sprintf("\xEF\x83\x8D"), 
			'&#xE706;' => sprintf("\xEF\x83\x8E"), 
			'&#xE707;' => sprintf("\xEE\xBE\xBF"), 
			'&#xE708;' => sprintf("\xEE\xBF\x8D"), 
			'&#xE709;' => sprintf("\xEE\xB6\x81"), 
			'&#xE70A;' => sprintf("\xEE\xB3\xB2"), 
			'&#xE6AC;' => sprintf("\xEE\xBE\x97"), 
			'&#xE6AD;' => sprintf("\xEE\xBE\xA0"), 
			'&#xE6AE;' => sprintf("\xEF\x83\x9A"), 
			'&#xE6B1;' => sprintf("\xEE\xBF\x95"), 
			'&#xE6B2;' => '　', 
			'&#xE6B3;' => sprintf("\xEE\xB3\x85"), 
			'&#xE6B7;' => sprintf("\xEF\x81\xAE"), 
			'&#xE6B8;' => sprintf("\xEE\xB5\xBE"), 
			'&#xE6B9;' => sprintf("\xEF\x81\xAF"), 
			'&#xE6BA;' => sprintf("\xEF\x82\xB1"), 
			'&#xE70C;' => 'α', 
			'&#xE70D;' => 'α', 
			'&#xE70E;' => sprintf("\xEF\x83\xA6"), 
			'&#xE70F;' => sprintf("\xEE\xBF\x9D"), 
			'&#xE710;' => sprintf("\xEE\xBF\xA2"), 
			'&#xE711;' => sprintf("\xEE\xB5\xBB"), 
			'&#xE712;' => sprintf("\xEE\xBE\x91"), 
			'&#xE713;' => sprintf("\xEE\xBF\xAB"), 
			'&#xE714;' => '　', 
			'&#xE715;' => sprintf("\xEE\xBE\xA0"), 
			'&#xE716;' => sprintf("\xEF\x83\xA8"), 
			'&#xE717;' => sprintf("\xEE\xB5\xBC"), 
			'&#xE718;' => sprintf("\xEF\x82\xA4"), 
			'&#xE719;' => sprintf("\xEE\xBD\xB9"), 
			'&#xE71A;' => sprintf("\xEF\x83\xB9"), 
			'&#xE71B;' => sprintf("\xEE\xBF\xAD"), 
			'&#xE71C;' => sprintf("\xEE\xBD\x94"), 
			'&#xE71D;' => sprintf("\xEE\xBE\x87"), 
			'&#xE71E;' => sprintf("\xEE\xB2\x82"), 
			'&#xE71F;' => sprintf("\xEF\x82\x97"), 
			'&#xE720;' => sprintf("\xEE\xB2\x96"), 
			'&#xE721;' => sprintf("\xEE\xB2\xA1"), 
			'&#xE722;' => sprintf("\xEF\x83\xB6"), 
			'&#xE723;' => sprintf("\xEF\x83\xB6"), 
			'&#xE724;' => sprintf("\xEE\xB5\xA1"), 
			'&#xE725;' => sprintf("\xEE\xB5\xAB"), 
			'&#xE726;' => sprintf("\xEF\x83\xB4"), 
			'&#xE727;' => sprintf("\xEE\xBF\x92"), 
			'&#xE728;' => sprintf("\xEE\xBF\x80"), 
			'&#xE729;' => sprintf("\xEF\x83\xB3"), 
			'&#xE72A;' => sprintf("\xEE\xBD\x89"), 
			'&#xE72B;' => sprintf("\xEE\xBD\x8C"), 
			'&#xE72C;' => sprintf("\xEE\xB5\xA5"), 
			'&#xE72D;' => sprintf("\xEE\xBD\x8B"), 
			'&#xE72E;' => sprintf("\xEE\xB5\xAD"), 
			'&#xE72F;' => '　', 
			'&#xE730;' => sprintf("\xEE\xBD\xB8"), 
			'&#xE731;' => sprintf("\xEF\x81\xB4"), 
			'&#xE732;' => sprintf("\xEF\x81\xAA"), 
			'&#xE733;' => sprintf("\xEE\xBD\x83"), 
			'&#xE734;' => sprintf("\xEE\xBF\x8A"), 
			'&#xE735;' => sprintf("\xEE\xB5\xBD"), 
			'&#xE736;' => sprintf("\xEF\x81\xB5"), 
			'&#xE737;' => sprintf("\xEE\xBD\x99"), 
			'&#xE738;' => '禁', 
			'&#xE739;' => sprintf("\xEE\xB1\x9D"), 
			'&#xE73A;' => '合', 
			'&#xE73B;' => sprintf("\xEE\xB1\x9C"), 
			'&#xE73C;' => sprintf("\xEE\xB5\xBE"), 
			'&#xE73D;' => sprintf("\xEE\xB6\x80"), 
			'&#xE73E;' => sprintf("\xEE\xB1\x93"), 
			'&#xE73F;' => sprintf("\xEE\xB6\x81"), 
			'&#xE740;' => sprintf("\xEF\x83\xAD"), 
			'&#xE741;' => sprintf("\xEE\xBF\xAC"), 
			'&#xE742;' => sprintf("\xEE\xBE\xAB"), 
			'&#xE743;' => sprintf("\xEE\xBE\xBD"), 
			'&#xE744;' => sprintf("\xEE\xB3\xB6"), 
			'&#xE745;' => sprintf("\xEE\xB2\x8D"), 
			'&#xE746;' => sprintf("\xEE\xB6\x82"), 
			'&#xE747;' => sprintf("\xEE\xBE\xA7"), 
			'&#xE748;' => sprintf("\xEE\xBE\xA3"), 
			'&#xE749;' => sprintf("\xEE\xBE\xAE"), 
			'&#xE74A;' => sprintf("\xEE\xBE\xA9"), 
			'&#xE74B;' => sprintf("\xEE\xB1\xAA"), 
			'&#xE74C;' => sprintf("\xEE\xB2\x88"), 
			'&#xE74D;' => sprintf("\xEE\xB2\x83"), 
			'&#xE74E;' => sprintf("\xEE\xB6\x83"), 
			'&#xE74F;' => sprintf("\xEE\xBE\xB9"), 
			'&#xE750;' => sprintf("\xEE\xBE\xB5"), 
			'&#xE751;' => sprintf("\xEE\xBD\xB2"), 
			'&#xE752;' => sprintf("\xEE\xB2\xA1"), 
			'&#xE753;' => sprintf("\xEE\xB6\x85"), 
			'&#xE754;' => sprintf("\xEE\xBE\xB1"), 
			'&#xE755;' => sprintf("\xEE\xB5\x8C"), 
			'&#xE756;' => sprintf("\xEE\xBE\x9A"), 
			'&#xE757;' => sprintf("\xEF\x83\xB5")
		);
		
		// 変換処理
		$output = strtr($input, $emoji_i2ez);
		
		return $output;
	}


	/**
	 * 絵文字変換（iモード→ソフトバンク）
	 *
	 * iモード用からソフトバンク用に絵文字を変換する
	 *
	 * @param	string	$input	変換対象の文字列
	 *
	 * @return	string			変換後の文字列
	 */
	function emoji_i2sb($input)
	{
		$emoji_i2sb = array(
			'&#xE63E;'=>'$Gj',
			'&#xE63F;'=>'$Gi',
			'&#xE640;'=>'$Gk',
			'&#xE641;'=>'$Gh',
			'&#xE642;'=>'$E]',
			'&#xE643;'=>'$Pc',
			'&#xE644;'=>'$Pc',
			'&#xE645;'=>'$P\',
			'&#xE646;'=>'$F_',
			'&#xE647;'=>'$F`',
			'&#xE648;'=>'$Fa',
			'&#xE649;'=>'$Fb',
			'&#xE64A;'=>'$Fc',
			'&#xE64B;'=>'$Fd',
			'&#xE64C;'=>'$Fe',
			'&#xE64D;'=>'$Ff',
			'&#xE64E;'=>'$Fg',
			'&#xE64F;'=>'$Fh',
			'&#xE650;'=>'$Fi',
			'&#xE651;'=>'$Fj',
			'&#xE652;'=>'$El',
			'&#xE653;'=>'$PK',
			'&#xE654;'=>'$G4',
			'&#xE655;'=>'$G5',
			'&#xE656;'=>'$G8',
			'&#xE657;'=>'$G3',
			'&#xE658;'=>'$PJ',
			'&#xE659;'=>'$ER',
			'&#xE65A;'=>'$G*',
			'&#xE65B;'=>'$G>',
			'&#xE65C;'=>'$PT',
			'&#xE65D;'=>'$PU',
			'&#xE65E;'=>'$PN',
			'&#xE65F;'=>'$Ez',
			'&#xE660;'=>'$Ey',
			'&#xE661;'=>'$F"',
			'&#xE662;'=>'$G=',
			'&#xE663;'=>'$GV',
			'&#xE664;'=>'$GX',
			'&#xE665;'=>'$Es',
			'&#xE666;'=>'$Eu',
			'&#xE667;'=>'$Em',
			'&#xE668;'=>'$Et',
			'&#xE669;'=>'$Ex',
			'&#xE66A;'=>'$Ev',
			'&#xE66B;'=>'$GZ',
			'&#xE66C;'=>'$Eo',
			'&#xE66D;'=>'$En',
			'&#xE66E;'=>'$PH',
			'&#xE66F;'=>'$Gc',
			'&#xE670;'=>'$Ge',
			'&#xE671;'=>'$Gd',
			'&#xE672;'=>'$Gg',
			'&#xE673;'=>'$E@',
			'&#xE674;'=>'$E^',
			'&#xE675;'=>'$O3',
			'&#xE676;'=>'$G\',
			'&#xE677;'=>'$G]',
			'&#xE678;'=>'$FV',
			'&#xE679;'=>'$ED',
			'&#xE67A;'=>'$O*',
			'&#xE67B;'=>'$Q"',
			'&#xE67C;'=>'$Q#',
			'&#xE67D;'=>'$PI',
			'&#xE67E;'=>'$EE',
			'&#xE67F;'=>'$O.',
			'&#xE680;'=>'$F(',
			'&#xE681;'=>'$G(',
			'&#xE682;'=>'$OC',
			'&#xE683;'=>'$Eh',
			'&#xE684;'=>'$O4',
			'&#xE685;'=>'$E2',
			'&#xE686;'=>'$Ok',
			'&#xE687;'=>'$G)',
			'&#xE688;'=>'$G*',
			'&#xE689;'=>'$O!',
			'&#xE68A;'=>'$EJ',
			'&#xE68B;'=>'$EK',
			'&#xE68C;'=>'$EF',
			'&#xE68D;'=>'$F,',
			'&#xE68E;'=>'$F.',
			'&#xE68F;'=>'$F-',
			'&#xE690;'=>'$F/',
			'&#xE691;'=>'$P9',
			'&#xE692;'=>'$P;',
			'&#xE693;'=>'$G0',
			'&#xE694;'=>'$G1',
			'&#xE695;'=>'$G2',
			'&#xE696;'=>'$FX',
			'&#xE697;'=>'$FW',
			'&#xE698;'=>'$QV',
			'&#xE699;'=>'$G\'',
			'&#xE69A;'=>'$Q9',
			'&#xE69B;'=>'$F*',
			'&#xE69C;'=>'$Gl',
			'&#xE69D;'=>'$Gl',
			'&#xE69E;'=>'$Gl',
			'&#xE69F;'=>'$Gl',
			'&#xE6A0;'=>'$Gl',
			'&#xE6A1;'=>'$Gr',
			'&#xE6A2;'=>'$Go',
			'&#xE6A3;'=>'$G<',
			'&#xE6A4;'=>'$GS',
			'&#xE6A5;'=>'$FY',
			'&#xE6AC;'=>'$OD',
			'&#xE6AD;'=>'$F9',
			'&#xE6AE;'=>'$O!',
			'&#xE6B1;'=>'$F!',
			'&#xE6B2;'=>'$E?',
			'&#xE6B3;'=>'$Pk',
			'&#xE6B7;'=>'$FQ',
			'&#xE6B8;'=>'$FO',
			'&#xE6B9;'=>'$FN',
			'&#xE6BA;'=>'$GF',
			'&#xE6CE;'=>'$E$',
			'&#xE6CF;'=>'$E"',
			'&#xE6D0;'=>'$G+',
			'&#xE6D1;'=>'$QX',
			'&#xE6D2;'=>'$QY',
			'&#xE6D3;'=>'$E#',
			'&#xE6D4;'=>'$Ft',
			'&#xE6D5;'=>'$Fs',
			'&#xE6D6;'=>'$EO',
			'&#xE6D7;'=>'$F6',
			'&#xE6D8;'=>'$FI',
			'&#xE6D9;'=>'$G_',
			'&#xE6DA;'=>'$FZ',
			'&#xE6DB;'=>'$FK',
			'&#xE6DC;'=>'$E4',
			'&#xE6DD;'=>'$F2',
			'&#xE6DE;'=>'$Ec',
			'&#xE6DF;'=>'$F1',
			'&#xE6E0;'=>'$F0',
			'&#xE6E1;'=>'$G@',
			'&#xE6E2;'=>'$F<',
			'&#xE6E3;'=>'$F=',
			'&#xE6E4;'=>'$F>',
			'&#xE6E5;'=>'$F?',
			'&#xE6E6;'=>'$F@',
			'&#xE6E7;'=>'$FA',
			'&#xE6E8;'=>'$FB',
			'&#xE6E9;'=>'$FC',
			'&#xE6EA;'=>'$FD',
			'&#xE6EB;'=>'$FE',
			'&#xE6EC;'=>'$OG',
			'&#xE6ED;'=>'$OH',
			'&#xE6EE;'=>'$GC',
			'&#xE6EF;'=>'$OJ',
			'&#xE6F0;'=>'$P5',
			'&#xE6F1;'=>'$P6',
			'&#xE6F2;'=>'$P#',
			'&#xE6F3;'=>'$P\'',
			'&#xE6F4;'=>'$P&',
			'&#xE6F5;'=>'$FV',
			'&#xE6F6;'=>'$G^',
			'&#xE6F7;'=>'$EC',
			'&#xE6F8;'=>'$F$',
			'&#xE6F9;'=>'$P<',
			'&#xE6FA;'=>'$ON',
			'&#xE6FB;'=>'$E/',
			'&#xE6FC;'=>'$OT',
			'&#xE6FD;'=>'$G-',
			'&#xE6FE;'=>'$O1',
			'&#xE6FF;'=>'$OF',
			'&#xE700;'=>'$FX',
			'&#xE701;'=>'$E\',
			'&#xE702;'=>'$GA',
			'&#xE703;'=>'$OV',
			'&#xE704;'=>'$OW',
			'&#xE705;'=>'$OP',
			'&#xE706;'=>'$OQ',
			'&#xE707;'=>'$E(',
			'&#xE708;'=>'$OP',
			'&#xE709;'=>'$EL',
			'&#xE70A;'=>'$QM',
			'&#xE70B;'=>'$Fm',
			'&#xE70C;'=>'$Fu',
			'&#xE70D;'=>'$Fu',
			'&#xE70E;'=>'$G&',
			'&#xE70F;'=>'$Ei',
			'&#xE710;'=>'$O<',
			'&#xE711;'=>'$O9',
			'&#xE712;'=>'$G3',
			'&#xE713;'=>'$OE',
			'&#xE714;'=>'$Q$',
			'&#xE715;'=>'$EO',
			'&#xE716;'=>'$G,',
			'&#xE717;'=>'$E!',
			'&#xE718;'=>'$E6',
			'&#xE719;'=>'$O!',
			'&#xE71A;'=>'$E.',
			'&#xE71B;'=>'$GT',
			'&#xE71C;'=>'$GL',
			'&#xE71D;'=>'$EV',
			'&#xE71E;'=>'$OX',
			'&#xE71F;'=>'$GM',
			'&#xE720;'=>'$P.',
			'&#xE721;'=>'$P*',
			'&#xE722;'=>'$P2',
			'&#xE723;'=>'$P!',
			'&#xE724;'=>'$P6',
			'&#xE725;'=>'$P*',
			'&#xE726;'=>'$P7',
			'&#xE727;'=>'$G.',
			'&#xE728;'=>'$P)',
			'&#xE729;'=>'$P%',
			'&#xE72A;'=>'$P-',
			'&#xE72B;'=>'$P&',
			'&#xE72C;'=>'$Gp',
			'&#xE72D;'=>'$P1',
			'&#xE72E;'=>'$P3',
			'&#xE72F;'=>'$OS',
			'&#xE730;'=>'$Ed',
			'&#xE731;'=>'$Fn',
			'&#xE732;'=>'$QW',
			'&#xE733;'=>'$E5',
			'&#xE734;'=>'$O5',
			'&#xE735;'=>'$OR',
			'&#xE736;'=>'$Fo',
			'&#xE737;'=>'$Fr',
			'&#xE738;'=>'$OS',
			'&#xE739;'=>'$FK',
			'&#xE73A;'=>'$O-',
			'&#xE73B;'=>'$FJ',
			'&#xE73C;'=>'$FT',
			'&#xE73D;'=>'$FR',
			'&#xE73E;'=>'$Ew',
			'&#xE73F;'=>'$P^',
			'&#xE740;'=>'$G[',
			'&#xE741;'=>'$E0',
			'&#xE742;'=>'$Og',
			'&#xE743;'=>'$O$',
			'&#xE744;'=>'$Of',
			'&#xE745;'=>'$Oe',
			'&#xE746;'=>'$O\'',
			'&#xE747;'=>'$E8',
			'&#xE748;'=>'$GP',
			'&#xE749;'=>'$Ob',
			'&#xE74A;'=>'$Gf',
			'&#xE74B;'=>'$O+',
			'&#xE74C;'=>'$O`',
			'&#xE74D;'=>'$OY',
			'&#xE74E;'=>'$QB',
			'&#xE74F;'=>'$QC',
			'&#xE750;'=>'$Gu',
			'&#xE751;'=>'$G9',
			'&#xE752;'=>'$Gv',
			'&#xE753;'=>'$P$',
			'&#xE754;'=>'$G:',
			'&#xE755;'=>'$E+',
			'&#xE756;'=>'$Gd',
			'&#xE757;'=>'$P+'
		);
		
		// 変換処理
		$output = strtr($input, $emoji_i2sb);
		
		return $output;
	}
}

?>