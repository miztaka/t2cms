<?php

/**
 * Admin_Object_Attribute_List
 */
class Admin_Object_Attribute_List extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    // 特定のメソッド時にValidationを実行したいときに定義。
    //const VALIDATION_TARGET = "";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    //const VALIDATION_CONFIG = '';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    const CONVERTER_CONFIG = '
__all:
  trim: {}
    ';
    
    public $searchResult = array();
    
    /**
     * 指定されたオブジェクトのAttributeを返します。
     */
    public function execute() {
        
        if (! Teeple_Util::isBlank($this->meta_entity_id)) {
            if (is_array($this->attributeTable)) {
                $this->updateAttributeSeq();
            }
            $this->searchResult = Entity_MetaAttribute::get()
                ->eq('meta_entity_id', $this->meta_entity_id, false)
                ->eq('delete_flg', 0)
                ->order('seq')
                ->select();
        }
        return NULL;
    }
    
    /**
     * 属性の並び順を変更します。
     * 
     */
    private function updateAttributeSeq() {
        
        // 先頭とおしりに空のデータが入ってくるので落とす
        array_pop($this->attributeTable);
        array_shift($this->attributeTable);
        
        // ハッシュにする (id => seq)
        $hash = array();
        for ($i=0; $i<count($this->attributeTable); $i++) {
            $hash[$this->attributeTable[$i]] = $i+1;
        }
        
        // 変更されているものは更新する
        $attributes = Entity_MetaAttribute::get()
            ->eq('meta_entity_id', $this->meta_entity_id)
            ->eq('delete_flg', 0)
            ->select();
        foreach ($attributes as $attr) {
            if ($attr->seq != $hash[$attr->id]) {
                $attr->seq = $hash[$attr->id];
                $attr->update();
            }
        }
        return;
    }

}

?>