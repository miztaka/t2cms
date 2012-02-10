<?php

/**
 * Admin_Account_Password
 */
class Admin_Account_Password extends AdminActionBase
{

    // 特定のメソッド時にValidationを実行したいときに定義。
    const VALIDATION_TARGET = "commit";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    const VALIDATION_CONFIG = '
"password.パスワード":
    required: {}
    mask: { mask: "/^[0-9A-Za-z:!\\"#$%\'()=~|@`\\[\\]{},<.>\\/?+_;*\\-]{8,16}$/" }
"password_confirm.パスワード(確認)":
    required: {}
    equal: { compareTo: "password" }
    ';
    
    //!"#$%'()-=~\|@` [{;+:*]},<.>/?_
    
    /*
     * ①8～16桁の文字列
②英字/数字/記号はいずれも一文字以上含む必要があります
　・英字：　A～Z、a～z
　・数時：　0～9
　・記号：　! # $ % - _ \ ; *
     */
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    const CONVERTER_CONFIG = '
__all:
    trim: {}
    ';
    
    /**
     * Entity_LoginAccount
     *
     * @var Entity_LoginAccount
     */
    private $entity;
    public function setEntity_LoginAccount($c) {
        $this->entity = $c;
    }    
    
    /**
     * 標準で実行されるメソッドです。
     */
    public function execute() {
        return NULL;
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        return NULL;
    }

    public function commit() {
        
        if (! $this->validate()) {
            return $this->onValidateError();
        }
        
        $account = Entity_LoginAccount::get()->find($this->loginaccount->info->id);
        if ($account == NULL) {
            $this->log->error("アカウントが取得できません。");
            $this->request->addErrorMessage("システムエラーが発生しました。");
            return $this->redirect(Admin_Home::actionName());
        }
        $account->login_pw = $this->password;
        $account->pw_change_date = $account->now();
        $account->update();
        
        $this->request->addNotification("パスワードを変更しました。");
        return $this->redirect(Admin_Home::actionName());
    }
    
    public function validate() {
        
        $message = 'パスワードには数字、アルファベット、記号を含む必要があります。';
        if (! preg_match("/[0-9]/", $this->password)) {
            $this->request->addErrorMessage($message);
            return FALSE;
        }
        if (! preg_match("/[a-zA-Z]/", $this->password)) {
            $this->request->addErrorMessage($message);
            return FALSE;
        }
        if (! preg_match("/[:!\"#$%\'()=~|@`\\[\\]{},<.>\\/?+_;*\\-]/", $this->password)) {
            $this->request->addErrorMessage($message);
            return FALSE;
        }
        if ($this->loginaccount->info->login_pw == $this->password) {
            $this->request->addErrorMessage('現在のパスワードと同じです。');
            return FALSE;
        }
        return TRUE;
    }
}

?>