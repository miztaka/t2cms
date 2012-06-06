<?php

/**
 * ファイルアップロードに関するロジックです。
 *
 */
class Logic_FileUpload
{
    
    /**
     * Teeple_Request
     *
     * @var Teeple_Request
     */
    protected $request;
    public function setComponent_Teeple_Request($c) {
        $this->request = $c;
    }

    /**
     * @var Logger
     */
    protected $log;

    /**
     * コンストラクタです。
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }    
        
    /**
     * ファイルを読み取りオブジェクトの配列として返します。
     * @param $paramName string フォームパラメータ名
     * @param $fileReader Logic_FileReader FileReaderオブジェクト
     * @param $validate boolean Validationを実施するか？
     * @return array stdClassの配列
     */
    public function getFileAsObjArray($paramName, $fileReader, $validate = true) {
        
        if (! is_uploaded_file($_FILES[$paramName]["tmp_name"])) {
            $this->request->addErrorMessage("ファイルが選択されていません。");
            return NULL;
        }
        
        return $fileReader->readFile($_FILES[$paramName]["tmp_name"], $validate);
    }
    
    /**
     * アップロードされたファイルを一時ディレクトリに配置します。
     * @return array fileのname属性とパスの連想配列
     */
    public function uploadTmpFile() {
        
        $result = array();
        foreach($_FILES as $name => $data) {
            if ($data['error'] != 0) {
                continue;
            }
            $path = pathinfo($data['name']);
          	//$tmppath = tempnam(UPLOAD_TMP_DIR, $name) . "." . strtolower($path['extension']);
          	$tmppath = tempnam(UPLOAD_TMP_DIR, 'tp,') . "." . strtolower($path['extension']);
            $tmpname = basename($tmppath);
            
            if (! move_uploaded_file($data['tmp_name'], $tmppath)) {
                continue;
            }
            $result[] = array(
                'name' => $name,
                'value' => $tmpname
            );
        }
        return $result;
    }
    
    /**
     * TODO 修正が必要かも
     * アップロードされたファイルを更新します。
     * @param Teeple_ActionBase $action
     * @param Teeple_ActiveRecord $entity
     * @param array $img_fields
     * @param string $prefix 
     * @return unknown_type
     */
    public function updateFile($action, $entity, $img_fields, $prefix) {
        
        $update_flg = FALSE;
        foreach ($img_fields as $prop) {
            $prop_h = "{$prop}_h";
            if (Teeple_Util::isBlank($action->$prop_h)) {
                if ($action->crudType == "U" && ! Teeple_Util::isBlank($entity->$prop)) {
                    // 画像が削除された
                    @unlink(UPLOAD_DIR."/".$entity->$prop);
                    $entity->$prop = '';
                    $update_flg = TRUE;
                }
                continue;
            }
            $tmpname = $action->$prop_h;
            $realname = $this->createRealFileName($prefix, $prop, $entity->id);
            if (strpos($tmpname, $realname) !== FALSE) {
                // ファイル変更無し
                continue;
            }
            // ファイルを配置してDBを更新
            $pathinfo = pathinfo($tmpname);
            $realname .= "_". time() .".". $pathinfo['extension'];
            if (! copy(UPLOAD_TMP_DIR."/".$tmpname, UPLOAD_DIR."/".$realname)) {
                $this->log->error("ファイルのコピーに失敗しました。$tmpname -> $realname");
                $this->request->addErrorMessage('画像ファイルの更新に失敗しました。');
                break;
            }
            $this->log->debug("ファイルのコピー $tmpname -> $realname");
            @unlink(UPLOAD_TMP_DIR."/".$tmpname);
            $entity->$prop = $realname;
            $update_flg = TRUE;
        }
        /*
        if ($update_flg) {
            $entity->update();
        }
        */
        return $update_flg;
    }
	
    /**
     * 画像ファイルのファイル名を定義する。
     * @param $prefix
     * @param $prop
     * @param $id
     * @return unknown_type
     */
    function createRealFileName($prefix, $prop, $id) {
    	return "{$prefix}_{$prop}_{$id}";
    }
    
}

?>