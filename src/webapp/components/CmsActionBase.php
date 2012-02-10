<?php

class CmsActionBase extends AdminActionBase {

    protected function init() {
        
        if (Teeple_Util::isBlank($this->meta_entity_id)) {
            return false;
        }
        $this->object = Entity_MetaEntity::get()
            ->eq('id', $this->meta_entity_id, false)
            ->eq('delete_flg', 0)
            ->find();
        if ($this->object == null) {
            return false;
        }
        
        $this->attributes = Entity_MetaAttribute::get()
            ->eq('meta_entity_id', $this->meta_entity_id, false)
            ->eq('delete_flg', 0)
            ->order('seq')
            ->select();
        
        return true;
    }
        
}

?>