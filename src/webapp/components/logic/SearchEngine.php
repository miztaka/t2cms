<?php

class Logic_SearchEngine extends Logic_Base
{
    
    /**
     * @return Logic_SearchEngine
     */
    public static function neu() {
        return Teeple_Container::getInstance()->getComponent(__CLASS__);
    }
    
    /**
     * 全文検索を行ないます。
     * 結果はEntity_MetaRecordの配列を返します。(meta_entity join済み)
     * 
     * @param string $words 検索ワード
     * @param Teeple_Pager $pager ページネーションクラス
     * @param string $pname エンティティの指定がある場合
     */
    public function fullTextSearch($words, $pager=NULL, $pname=NULL) {
        
        $query = Entity_MetaValue::get()
            ->join('meta_record')
            ->join('meta_record$meta_entity')
            ->eq('meta_record$meta_entity.exclude_search_flg', 0)
            ->eq('meta_record.delete_flg', 0)
            ->eq('meta_record.publish_flg', 1)
            ->where('meta_record.publish_start_dt IS NULL OR meta_record.publish_start_dt <= now()')
            ->where('meta_record.publish_end_dt IS NULL OR meta_record.publish_end_dt >= now()')
            ->contains('base.value', $words);
        if ($pname) {
            $query->eq('meta_record$meta_entity.pname', $pname, FALSE);
        }
        
        if ($pager) {
            $pager->total = $query->count('DISTINCT base.meta_record_id');
            $query->limit($pager->limit)->offset($pager->offset());
        }
        
        $values = $query
            ->order('meta_record.id DESC')
            ->select('DISTINCT base.meta_record_id AS base$meta_record_id');
        $record_ids = array();
        foreach ($values as $value) {
            $record_ids[] = $value->meta_record_id;
        }
        
        if (empty($record_ids)) {
            return array();
        }
        
        return Entity_MetaRecord::get()
            ->join('meta_entity')
            ->in("base.id", $record_ids)
            ->order('base.id DESC')
            ->select();
    }

}
