<?php 

class Entity_Base_RecordUrl extends Teeple_ActiveRecord
{

    /**
     * �g�p����f�[�^�\�[�X�����w�肵�܂��B
     * �w�肪�����ꍇ�́ADEFAULT_DATASOURCE �Őݒ肳��Ă���DataSource�����g�p����܂��B
     *
     * @var string
     */
    public static $_DATASOURCE = 'teeple2cms';
    
    /**
     * ���̃G���e�B�e�B�̃e�[�u������ݒ肵�܂��B
     * 
     * <pre>
     * �X�L�[�}��ݒ肷��ꍇ�́A"�X�L�[�}.�e�[�u����"�Ƃ��܂��B
     * �q�N���X�ɂĕK���Z�b�g����K�v������܂��B
     * </pre>
     *
     * @var string
     */
    public static $_TABLENAME = 'record_url';
    
    /**
     * �v���C�}���L�[���ݒ肵�܂��B
     * 
     * <pre>
     * �v���C�}���L�[�ƂȂ�J��������z��Ŏw�肵�܂��B
     * �q�N���X�ɂĕK���Z�b�g����K�v������܂��B
     * </pre>
     * 
     * @var array 
     */
    public static $_PK = array(
        'meta_record_id'
    );
    
    /**
     * ���̃e�[�u���̃J��������public�v���p�e�B�Ƃ��Đݒ肵�܂��B(�v���C�}���L�[������)
     * <pre>
     * �q�N���X�ɂĕK���Z�b�g����K�v������܂��B
     * </pre>
     */
    public $meta_record_id;
    public $url;
    public $page_id;
    public $create_time;
    public $timestamp;
    public $created_by;
    public $modified_by;
    public $delete_flg;
    public $delete_time;
    public $version;

    
    /**
     * �v���C�}���L�[�������Z�b�g(auto increment)���ǂ�����ݒ肵�܂��B
     * 
     * <pre>
     * �q�N���X�ɂĕK���Z�b�g����K�v������܂��B
     * </pre>
     * 
     * @var bool 
     */
    public static $_AUTO = FALSE;

}

?>
