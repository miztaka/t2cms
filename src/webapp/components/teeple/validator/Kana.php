<?php

/**
 * 仮名（ひらがな、全角カタカナ、半角カタカナ）であるかどうかをチェックします。
 * 引数 "space: true" で全角/半角スペースも許可します。（デフォルトはfalse）
 * 
 */
class Teeple_Validator_Kana extends Teeple_Validator
{

    public $args = array('space');
    
	protected function execute($obj, $fieldName) {

        $value = $this->getTargetValue($obj, $fieldName);
        if (Teeple_Util::isBlank($value)) {
            return true;
        }
        
        if(!isset($this->space)) {
        	$this->space = false;
        } elseif(!is_bool($this->space)) {
            throw new Teeple_Exception("spaceが正しくセットされていません。");
        }

        mb_regex_encoding(INTERNAL_CODE);
        
        $pattern = $this->space ? "^[ァ-ヶー ]+$" : "^[ァ-ヶー]+$";
        $value = mb_convert_kana($value, 'KVCs', INTERNAL_CODE);
        return (mb_ereg($pattern, $value) == 1);
    }

}
?>
