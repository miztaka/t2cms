<?php 

/**
 * Entity Class for record_url
 *
 * �G���e�B�e�B�Ɋւ��郍�W�b�N���͂����Ɏ������܂��B
 * @package entity
 */
class Entity_RecordUrl extends Entity_Base_RecordUrl
{
    /**
     * �C���X�^���X���擾���܂��B
     * @return Entity_RecordUrl
     */
    public static function get() {
        return Teeple_Container::getInstance()->getEntity('Entity_RecordUrl');
    }
    
    /**
     * �P��s�̌��������s���܂��B
     * @param $id
     * @return Entity_RecordUrl
     */
    public function find($id=null) {
        return parent::find($id);
    }
    
    /**
     * JOIN����e�[�u����ݒ肵�܂��B
     * ��generator���f���o�������`���C�����Ă��������B
     * 
     * �����ɐݒ肵�Ă����`�́A$this->join('aliasname') �ŗ��p�ł���B<br/>
     * �������ɐݒ肵�������ł�JOIN����Ȃ��B
     * 
     * <pre>
     * �w����@: '�A�N�Z�X���邽�߂̕ʖ�' => �ݒ�l�̔z��
     * �ݒ�l�̔z��F
     *   'entity' => �G���e�B�e�B�̃N���X��
     * �@'columns' => �擾����J����������(SQL�ɃZ�b�g����̂Ɠ����`��)
     *   'type' => JOIN�̃^�C�v(SQL�ɏ����`���Ɠ���)(�ȗ������ꍇ��INNER JOIN)
     *   'relation' => JOIN���邽�߂̃����[�V�����ݒ�
     *      �u�{�N���X�̃L�[�� => �ΏۃN���X�̃L�[���v�ƂȂ�܂��B
     *   'condition' => JOIN���邽�߂̐ݒ肾�����e�����Ŏw�肷�����
     * 
     * �l�̗�:
     * 
     * $join_config = array(
     *     'aliasname' => array(
     *         'entity' => 'Entity_Fuga',
     *         'columns' => 'foo, bar, hoge',
     *         'type' => 'LEFT JOIN',
     *         'relation' => array(
     *             'foo_id' => 'bar_id'
     *         ),
     *         'condition' => 'aliasname.status = 1 AND parent.status = 1'
     *     )
     * );
     * </pre>
     * 
     * @var array
     */
    public static $_JOINCONFIG = array(
        'page' => array(
            'entity' => 'Entity_Page',
            'type' => 'INNER JOIN',
            'relation' => array(
                'page_id' => 'id'
            )
        ),
        'meta_record' => array(
            'entity' => 'Entity_MetaRecord',
            'type' => 'INNER JOIN',
            'relation' => array(
                'meta_record_id' => 'id'
            )
        )
    );
    
    /**
     * @var Entity_Page 
     */
    public $page;

    /**
     * @var Entity_MetaRecord 
     */
    public $meta_record;



}

?>
