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
     * スペースで区切られたwordでAND検索をします。
     * pnameが指定されている場合は並び順を指定されたものにします。
     * 
     * @param string $words 検索ワード
     * @param Teeple_Pager $pager ページネーションクラス
     * @param string $pname エンティティの指定がある場合
     */
    public function fullTextSearch($words, $pager=NULL, $pname=NULL) {
        
        // order
        $order1 = 'meta_record.id DESC';
        $order2 = 'base.id DESC';
        if ($pname) {
            $metaEntity = Entity_MetaEntity::get()->eq("pname", $pname, false)->find();
            if ($metaEntity) {
                $order2 = (! Teeple_Util::isBlank($metaEntity->order_by)) ?
                    $metaEntity->order_by : "IFNULL(base.seq,999) ASC, base.id DESC";
                $order1 = str_replace("base.", "meta_record.", $order2);
            }
        }
        
        $query = Entity_MetaValue::get()
            ->join('meta_record')
            ->join('meta_record$meta_entity')
            ->eq('meta_record$meta_entity.exclude_search_flg', 0)
            ->eq('meta_record.delete_flg', 0)
            ->eq('meta_record.publish_flg', 1)
            ->where('meta_record.publish_start_dt IS NULL OR meta_record.publish_start_dt <= now()')
            ->where('meta_record.publish_end_dt IS NULL OR meta_record.publish_end_dt >= now()')
        ;
        
        // 検索ワード
        $chunk = preg_split("/(\s|　|,|、)+/", $words);
        if (! empty($chunk)) {
            foreach($chunk as $w) {
                $query->contains('base.value', $w);
            }
        }
        if ($pname) {
            $query->eq('meta_record$meta_entity.pname', $pname, FALSE);
        }
        
        if ($pager) {
            $pager->total = $query->count('DISTINCT base.meta_record_id');
            $query->limit($pager->limit)->offset($pager->offset());
        }
        
        $values = $query
            ->order($order1)
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
            ->order($order2)
            ->select();
    }

}
