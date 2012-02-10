<?php

class AdminActionBase extends MyActionBase {
    
    /**
     * ログインアカウント
     *
     * @var LoginAccountAdmin
     */
    public $loginaccount;
    public function setSessionComponent_LoginAccountAdmin($c) {
        $this->loginaccount = $c;
    }
    
    public function json_response($value) {
        print json_encode($value);
        $this->request->completeResponse();
        return null; 
    }
    
    /**
     * オブジェクトの一覧を取得します。
     */
    public function objects() {
        if ($this->_objects == null) {
            $this->_objects = Entity_MetaEntity::get()
                ->eq('delete_flg', 0)
                ->order("IFNULL(seq, 999), id")
                ->select();
        }
        return $this->_objects;
    }
    private $_objects;
    
    public function publish_flgOptions() {
        return array('1' => '公開', '0' => '非公開');
    }
    
    public function yearOptions() {
        $y = date('Y');
        return U::getDateArray($y,$y+1,FALSE);
    }
    
    public function monthOptions() {
        return U::getDateArray(1,12,FALSE);
    }
    
    public function dayOptions() {
        return U::getDateArray(1,31,FALSE);
    }
    
    public function imgPath($path) {
        if (! Teeple_Util::isBlank($path)) {
            return strpos($path, 'tp,') === 0 ?
                $this->basePath()."/upload/tmp/{$path}" :
                $this->basePath()."/upload/{$path}";
        }
        return "";
    }    
    
    
    public function meta_entity_idOptions() {
        if (! is_array($this->_meta_entity_idOptions)) {
            $result = array();
            $list = Entity_MetaEntity::get()
                ->eq('delete_flg', 0)
                ->order('id')
                ->select();
            foreach ($list as $e) {
                $result[$e->id] = $e->label;
            }
            $this->_meta_entity_idOptions = $result;
        }
        return $this->_meta_entity_idOptions;
    }
    private $_meta_entity_idOptions;
}

?>