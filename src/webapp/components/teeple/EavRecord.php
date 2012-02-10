<?php

require_once 'plugin/ObjectPlugin.php';

/**
 * EAV型エンティティを操作するためのクラスです。
 * @author miztaka
 *
 */
class Teeple_EavRecord extends Teeple_SqlBuilder {
    
    public static $_SELECT_FIELD = array('publish_start_dt', 'publish_end_dt', 'seq', 'create_time', 'timestamp');
    
    /**
     * @var Teeple_Bean
     */
    protected $_bean;
    
    /**
     * 
     * @var array
     */
    protected $_columns;
    
    /**
     * @var array
     */
    protected $_refObj = array();
    
    /**
     * 
     * @var Entity_MetaEntity
     */
    public $_metaEntity;
    
    /**
     * 
     * @var array
     */
    public $_metaAttributes;
    
    /**
     * インスタンスを取得します。
     * @param mixed $type エンティティ名 or meta_entity_id
     */
    public function __construct($type=NULL) {
        
        if ($type == NULL) {
            return;
        }
        if (is_numeric($type)) {
            // id
            $this->_metaEntity = Entity_MetaEntity::get()
                ->eq('id', $type, false)
                ->eq('delete_flg', 0)
                ->find();
        } else {
            $this->_metaEntity = Entity_MetaEntity::get()
                ->eq('pname', $type, false)
                ->eq('delete_flg', 0)
                ->find();
        }
        if ($this->_metaEntity == null) {
            throw new Exception("オブジェクトが見つかりません。");
        }
        $this->_metaAttributes = Entity_MetaAttribute::get()
            ->eq('meta_entity_id', $this->_metaEntity->id)
            ->eq('delete_flg', 0)
            ->order('seq')
            ->select();
        
        $this->_columns = array('id', 'meta_entity_id', 'publish_flg', 'publish_start_dt', 'publish_end_dt', 'seq', 'create_time', 'timestamp');
        foreach ($this->_metaAttributes as $attr) {
            $this->_columns[] = $attr->pname;
        }
        $this->_bean = new Teeple_Bean($this->_columns);
        
        // list_control
        if ($this->_metaEntity->list_control) {
            $this->_metaEntity->list_control = unserialize($this->_metaEntity->list_control);
        }
    }
    
    /**
     * @return Teeple_EavRecord
     */
    protected function newInstance() {
        $instance = new Teeple_EavRecord();
        $instance->_metaEntity = $this->_metaEntity;
        $instance->_metaAttributes = $this->_metaAttributes;
        $instance->_columns = $this->_columns;
        $instance->_bean = new Teeple_Bean($this->_columns);
        return $instance;
    }
    
    /**
     * インスタンスを作成します。
     * @param string $type
     * @return Teeple_EavRecord
     */
    public static function neu($type) {
        $eav = new Teeple_EavRecord($type);
        return $eav;
    }

    /**
     * レコードを取得します。
     * @param $id
     * @return Teeple_EavRecord
     */
	public static function find($id, $publishedOnly=false) {
	    
	    if ($publishedOnly) {
	        $record = Entity_MetaRecord::get()
	           ->eq('id', $id, false)
	           ->eq('publish_flg', 1)
	           ->eq('delete_flg', 0)
	           ->where('publish_start_dt IS NULL OR publish_start_dt <= now()')
	           ->where('publish_end_dt IS NULL OR publish_end_dt >= now()')
	           ->find($id);
	    } else {
	        $record = Entity_MetaRecord::get()->find($id);
	    }
	    
	    if ($record == NULL) {
	        return NULL;
	    }
	    
	    // インスタンス作成
	    $instance = new Teeple_EavRecord($record->meta_entity_id);
	    $instance->setRecord($record);
	    
	    return $instance;
	}
	
	/**
	 * @param Entity_MetaRecord $record
	 * @return Teeple_EavRecord
	 */
	protected function setRecord($record) {
	    
	    $this->_bean = new Teeple_Bean($this->_columns);
	    $this->_bean->copyFrom($record);
	    
	    $values = Entity_MetaValue::get()
	       ->join('meta_attribute')
	       ->eq('base.meta_record_id', $record->id)
	       ->eq('base.delete_flg', 0)
	       ->select();
	    foreach ($values as $value) {
	        $this->setValue($value);
	    }
	    return $this;
	}
	
	/**
	 * 設定された検索条件でレコードを検索します。
	 * @param bool $publishedOnly
	 * @param Teeple_Pager $pager
	 */
	public function select($publishedOnly=false, $pager=null) {
	    
        // meta_record検索用のエンティティー
        $record = Entity_MetaRecord::get()
            ->eq('meta_entity_id', $this->_metaEntity->id, false)
            ->eq('delete_flg', 0)
        ;
	    
	    // meta_valueから条件に合う record_idをselect
	    $ids = NULL;
	    if (count($this->_criteria)) {
	        $where = array();
	        $bindValue = array();
	        $pnames = array();
	        foreach ($this->_criteria as $cri) {
	            $str = $cri['str'];
                $val = $cri['val'];
                $buf = explode(" ", $str, 2);
                
                // meta_recordの検索項目の場合
                if (in_array($buf[0], self::$_SELECT_FIELD)) {
                    $record->where($str, $val);
                    continue;
                }
                
                // 以下、meta_valueから検索
                $pnames[] = $buf[0];
                $where[] = "meta_attribute.pname = ? AND base.value ". $buf[1];
                $bindValue[] = $buf[0];
                if ($val != null && is_array($val)) {
                    foreach ($val as $v) {
                        $bindValue[] = $v;
                    }
                }
	        }
	        
	        // meta_valueから検索
	        if (count($where) > 0) {
                $where_str = "(". implode(") OR (", $where) .")";
                $metaValue = Entity_MetaValue::get()
                    ->join('meta_record')
                    ->join('meta_attribute')
                    ->eq('meta_record.meta_entity_id', $this->_metaEntity->id, false)
                    ->eq('meta_record.delete_flg', 0)
                    ->eq('base.delete_flg', 0)
                    ->where($where_str, $bindValue)
                    ->select("base.meta_record_id AS base\$meta_record_id, meta_attribute.pname AS meta_attribute\$pname");
            
                // selectしたもののうち、条件に指定されているpnameがすべて存在するidを対象とする
                $hash = array();
                foreach ($metaValue as $v) {
                    if (! isset($hash[$v->meta_record_id])) {
                        $hash[$v->meta_record_id] = array();
                    }
                    $hash[$v->meta_record_id][] = $v->meta_attribute->pname;
                }
                $ids = array();
                foreach ($hash as $id => $p) {
                    $check = array_diff($pnames, $p);
                    if (empty($check)) {
                        $ids[] = $id;
                    }
                }
                if (empty($ids)) {
                    return array();
                }
	        }
	    }
	    
	    // meta_recordから条件に合うものをselect
	    if ($ids != NULL) {
	        $record->in('id', $ids);
	    }
	    if ($publishedOnly) {
	        $record
	           ->eq('publish_flg', 1)
               ->where('publish_start_dt IS NULL OR publish_start_dt <= now()')
               ->where('publish_end_dt IS NULL OR publish_end_dt >= now()')
	        ;
	    }
	    
	    // 並び順
	    $order_by = $this->getOrderBy(); 
	    $record->order($order_by);
	    
	    // 全件数取得
	    $total = $record->count();
	    if ($pager) {
	        $pager->total = $total;
	    }
	    
	    // ページング
	    if ($pager && $pager->limit > 0) {
	        $record->limit($pager->limit);
	    } elseif (isset($this->_afterwhere['limit'])) {
            $record->limit($this->_afterwhere['limit']);
        }
        if ($pager && $pager->page > 1 && $pager->limit > 0) {
            $record->offset($pager->offset());
        } elseif (isset($this->_afterwhere['offset'])) {
            $record->offset($this->_afterwhere['offset']);
        }
        $metaRecords = $record->select("base.id AS base\$id");
        if ($pager) {
            $pager->hit = count($metaRecords);
            if ($pager->limit == 0) {
                $pager->limit = $pager->hit;
            }
        }
        
        $page_ids = array();
        foreach ($metaRecords as $rec) {
            $page_ids[] = $rec->id;
        }
        if (empty($page_ids)) {
            return array();
        }
        
        // オブジェクト構築
        $order_by_rec = str_replace("base.", "meta_record.", $order_by);
        $metaValues = Entity_MetaValue::get()
            ->join('meta_record')
            ->join('meta_attribute')
            ->in('base.meta_record_id', $page_ids)
            ->eq('base.delete_flg', 0)
            ->order($order_by_rec)
            ->select();
        
        $items = array();
        $one = $this->newInstance();
        //$one = clone($this);
        $current_id = NULL;
        foreach ($metaValues as $metaV) {
            if ($current_id == NULL) {
                $current_id = $metaV->meta_record_id;
                $one->convert2Entity($metaV->meta_record);
            }
            if ($current_id != $metaV->meta_record_id) {
                $items[] = $one;
                //$one = clone($this);
                $one = $this->newInstance();
                $one->convert2Entity($metaV->meta_record);
                $current_id = $metaV->meta_record_id;
            }
            $one->setValue($metaV);
        }
        $items[] = $one;
        return $items;
	}
	
	/**
	 * MetaValueから値をセットします。
	 * 配列はunserializeしてセットされます。
	 * @param Entity_MetaValue $metaValue
	 */
	protected function setValue($metaValue) {
	    
	    $pname = $metaValue->meta_attribute->pname;
        switch($metaValue->meta_attribute->data_type) {
            case Entity_MetaAttribute::DATA_TYPE_CHECK:
            case Entity_MetaAttribute::DATA_TYPE_MULTISELECT:
                if (! Teeple_Util::isBlank($metaValue->value)) {
                    $this->_bean->$pname = unserialize($metaValue->value);
                }
                break;
            default:
                $this->_bean->$pname = $metaValue->value;
        }
        return;
	} 

	/**
	 * レコードを作成します。
	 */
	public function insert() {
	    
	    $clsname = "Plugin_Object_{$this->_metaEntity->pname}";
	    if (class_exists($clsname)) {
	        $plugin = new $clsname();
	        if (method_exists($plugin, 'beforeInsert')) {
	            $plugin->beforeInsert($this);
	        }
	    }
	    
	    // meta_record作成
        $record = Entity_MetaRecord::get();
        $record->convert2Entity($this->_bean);
        $record->meta_entity_id = $this->_metaEntity->id;
        $record->insert();
        
        // meta_value作成
        foreach ($this->_metaAttributes as $attr) {
            $pname = $attr->pname;
            $metaValue = Entity_MetaValue::get();
            $metaValue->meta_record_id = $record->id;
            $metaValue->meta_attribute_id = $attr->id;
            $metaValue->value = $this->_bean->$pname;
            $metaValue->insert();
        }
        $this->id = $record->id;
        return true;
    }
    
    /**
     * レコードを更新します。
     */
    public function update() {
        
        $clsname = "Plugin_Object_{$this->_metaEntity->pname}";
        if (class_exists($clsname)) {
            $plugin = new $clsname();
            if (method_exists($plugin, 'beforeUpdate')) {
                $plugin->beforeUpdate($this);
            }
        }
        if (Teeple_Util::isBlank($this->_bean->id)) {
            throw new Exception("IDがセットされていません。");
        }
        // meta_record更新
        $record = Entity_MetaRecord::get()->find($this->_bean->id);
        if ($record == null) {
            throw new Exception("レコードが見つかりません。");
        }
        $record->convert2Entity($this->_bean);
        $record->update();
        
        // meta_value更新
        foreach ($this->_metaAttributes as $attr) {
            $pname = $attr->pname;
            $metaValue = Entity_MetaValue::get()
                ->eq('meta_record_id', $record->id, FALSE)
                ->eq('meta_attribute_id', $attr->id, FALSE)
                ->find();
            if ($metaValue == null) {
                $metaValue = Entity_MetaValue::get();
                $metaValue->meta_record_id = $record->id;
                $metaValue->meta_attribute_id = $attr->id;
                $metaValue->value = $this->_bean->$pname;
                $metaValue->insert();
            } else {
                $value = $this->_bean->$pname;
                if (is_array($value)) {
                    $value = serialize($value);
                }
                if ($metaValue->value !== $value) {
                    $metaValue->value = $this->_bean->$pname;
                    $metaValue->update();
                }
            }
        }
        return true;
    }
    
    /**
     * 画像フィールドのカラム名を取得します。
     * @return array
     */
    public function getImageFieldNames() {
        $result = array();
        foreach ($this->_metaAttributes as $attr) {
            if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_IMAGE) {
                $result[] = $attr->pname;
            }
        }
        return $result;
    }
    
    /**
     * 一覧に表示するカラム名を配列で返します。
     */
    public function getListColumns() {
        
        $result = array('id' => 'ID'); // IDは必須
        foreach ($this->_metaAttributes as $attr) {
            if ($attr->list_flg) {
                $result[$attr->pname] = $attr->label;
            }
        }
        if (is_array($this->_metaEntity->list_control)) {
            foreach ($this->_metaEntity->list_control as $col) {
                $result[$col] = Entity_MetaEntity::$_list_controlOptions[$col];
            }
        }
        return $result;
    }
	
	public function convert2Page($page, $colmap=NULL) {
	    $this->_bean->copyTo($page, $colmap);
	}
	
	public function convert2Entity($page, $colmap=NULL) {
	    $this->_bean->copyFrom($page, $colmap);
	}
	
	public function __get($name) {
	    if (! Teeple_Util::isBlank($this->_bean->$name)) {
	        return $this->_bean->$name;
	    }
	    if (preg_match("/__r$/", $name)) {
	        return $this->refObj(substr($name, 0, strlen($name)-3));
	    }
	    return "";
	}
	
	public function __set($name, $value) {
	    $this->_bean->$name = $value;
	    return $value;
	}
	
	/**
	 * 指定されたpnameのattributeを取得します。
	 * @param string $pname
	 * @return Entity_MetaAttribute
	 */
	public function getAttributeByPname($pname) {
	    foreach ($this->_metaAttributes as $attr) {
	        if ($attr->pname == $pname) {
	            return $attr;
	        }
	    }
	    return null;
	}
	
	/**
	 * 参照フィールドのラベル表示
	 * @param $pname
	 */
	public function refLabel($pname) {
	    
	    if (Teeple_Util::isBlank($this->$pname)) {
	        return "";
	    }
	    $attr = $this->getAttributeByPname($pname);
	    if ($attr->data_type != Entity_MetaAttribute::DATA_TYPE_REF) {
	        return "";
	    }
	    $value = Entity_MetaValue::get()
	       ->join('meta_attribute')
	       ->eq('base.meta_record_id', $this->$pname)
	       ->eq('meta_attribute.seq', 1)
	       ->find();
	    if ($value) {
	        return $value->value;
	    }
	    return "";
	}
	
	/**
	 * 参照先オブジェクトの取得
	 * @param string $pname
	 * @return Teeple_EavRecord 
	 */
	public function refObj($pname) {
	    
	    // キャッシュから
	    if (isset($this->_refObj[$pname])) {
	        return $this->_refObj[$pname];
	    }
	    
        if (Teeple_Util::isBlank($this->$pname)) {
            return null;
        }
        $attr = $this->getAttributeByPname($pname);
        if ($attr->data_type != Entity_MetaAttribute::DATA_TYPE_REF) {
            return null;
        }
        
        $this->_refObj[$pname] = self::find($this->$pname);
        return $this->_refObj[$pname];
	}
	
	/**
	 * カラムの表示ラベル
	 * ※参照フィールドだった場合に参照レコードの値を表示
	 * @param string $pname
	 */
	public function label($pname) {
	    
	    $val = $this->$pname;
	    if (Teeple_Util::isBlank($val)) {
	        return "";
	    }
	    
	    $attr = $this->getAttributeByPname($pname);
	    if ($attr == null) {
	        return $val; // 標準カラム
	    }
	    if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_REF) {
	        return $this->refLabel($pname);
	    }
        if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_RADIO ||
            $attr->data_type == Entity_MetaAttribute::DATA_TYPE_SELECT) {
            $opts = $attr->getOptions();
            return $opts[$val];
        }
        if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_CHECK ||
            $attr->data_type == Entity_MetaAttribute::DATA_TYPE_MULTISELECT) {
            $opts = $attr->getOptions();
            $result = array();
            if (is_array($val)) {
                foreach ($val as $v) {
                    $result[] = $opts[$v]; 
                }
            }
            return implode(", ", $result); 
        }
	    return $val;
	}
	
    /**
     * 前のレコードを取得します。
     */
    public function prevRecord() {
        
        $nextid = $this->getIdByOffset(1);
        if ($nextid != NULL) {
            $next = Teeple_EavRecord::neu($this->_metaEntity->pname)->find($nextid, true);
            return $next;
        }
        return NULL;
    }
    
    /**
     * 次のレコードを取得します。
     */
    public function nextRecord() {
        
        $nextid = $this->getIdByOffset(-1);
        if ($nextid != NULL) {
            $next = Teeple_EavRecord::neu($this->_metaEntity->pname)->find($nextid, true);
            return $next;
        }
        return NULL;
    }
    
	/**
	 * order by句を取得します。
	 */
	protected function getOrderBy() {
        $order_by = Teeple_Util::isBlank($this->_metaEntity->order_by) ?
           "IFNULL(base.seq,999) ASC, base.id DESC" : $this->_metaEntity->order_by;
        return $order_by;
	}
	
	/**
	 * 全てのレコードIDを並び順どおりに取得します。
	 */
	protected function getAllRecordIds() {
	    return Entity_MetaRecord::get()
            ->eq('meta_entity_id', $this->_metaEntity->id, false)
            ->eq('delete_flg', 0)
            ->eq('publish_flg', 1)
            ->where('publish_start_dt IS NULL OR publish_start_dt <= now()')
            ->where('publish_end_dt IS NULL OR publish_end_dt >= now()')
            ->order($this->getOrderBy())
            ->select("base.id AS base\$id");
	}
	
	/**
	 * 現在のIDからoffset分次、または前のレコードを取得します。
	 * @param unknown_type $offset
	 */
	protected function getIdByOffset($offset) {
	    
	    // meta_recordからidの一覧を取得
        $records = $this->getAllRecordIds();
        $thisnum = -1;
        for($i=0; $i<count($records); $i++) {
            if ($records[$i]->id == $this->id) {
                $thisnum = $i;
                break;
            }
        }
        if ($thisnum < 0) {
            return NULL;
        }
        
        $nextnum = $thisnum + $offset;
        if ($nextnum >= 0 && $nextnum < count($records)) {
            $nextid = $records[$nextnum]->id;
            return $nextid;
        }
        return NULL;	    
	}
	
}
