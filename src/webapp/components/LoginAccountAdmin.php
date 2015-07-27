<?php

/**
 * Session上に保持されるログインアカウントです。(管理画面)
 *
 */
class LoginAccountAdmin {
    
    const SESSION_TIME = 3600; // 1時間
    
    private $authed = false;
    private $lasttime;
    
    /**
     * このアカウントが保持しているロール名称のリスト
     * @var array
     */
    public $roles = array();
    
    /**
     * このアカウントの情報(通常はエンティティの内容をコピーして使う)
     * @var object
     */
    public $info;
    
    /**
     * 認証許可をします。
     *
     */
    public function authorize() {
        $this->authed = true;
        $this->lasttime = time();
    }
    
    /**
     * 認証されているかどうか？
     *
     * @return boolean
     */
    public function isAuthed() {
        return ($this->authed && $this->intime()) ? true : false;
    }
    
    /**
     * 指定されたロールを持っているかどうか
     *
     * @param string $rolename 複数のロールを指定する場合はカンマ区切り
     * @return boolean
     */
    public function hasRole($rolename) {
        
        $roles = explode(',', $rolename);
        foreach ($roles as $role) {
            if (in_array($role, $this->roles)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 指定されたエンティティにアクセス可能かどうか
     * limited以外のアカウント 全アクセス可能
     * limitedでアクセス可能メニューが選択されていればアクセス可能
     * 
     * @param unknown $meta_entity_id
     */
    public function canAccess($meta_entity_id) {
    	
    	if ($this->info->role != 'limited') {
    		return true;
    	}
    	if (! Teeple_Util::isBlank($this->info->allowed_entity)) {
    		$ids = unserialize($this->info->allowed_entity);
    		if (in_array($meta_entity_id, $ids)) {
    			return true;
    		}
    	}
    	return false;
    }
    
    /**
     * セッションが有効かどうか
     *
     * @return boolean
     */
    private function intime() {
        $now = time();
        if ($this->lasttime != NULL && ($now - $this->lasttime < self::SESSION_TIME)) {
            $this->lasttime = $now;
            return true;
        }
        return false;
    }
    
}

?>