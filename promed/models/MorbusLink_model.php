<?php defined('BASEPATH') or die ('No direct script access allowed');
/**!!!!!!!!!!!!!!! создавалось для связи общего - простого заболевания,
 * и планировалось потом доработать объект связи для создания других типов связей (заболевание-осложнение, роды-ребенок и т.п.)
 * но эта связь была реализована через поле Morbus.MorbusBase_id, необходимость в этом объекте исчезла и он на данный момент не используется.
 * Он будет использоваться для создания других типов связей.
 * TODO После доработки класса для других типов связей убрать этот комментарий
 *
 * MorbusLink_model - Модель для работы со связью между заболеваниями (таблица MorbusLink)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       IGabdushev
 * @version      12 2011
 */
/**
 * @property int $MorbusLink_id
 * @property MorbusBase_model $morbus
 * @property Morbus_model $morbus_l
 * @property int $MorbusLinkType_id
 * @property int $pmUser_id
 */
class MorbusLink_model extends Abstract_model
{
    var $scheme = "dbo";
    var $morbus;//основное заболевание. Может быть Общим.
    var $morbus_l;//связанное заболевание. Класс - Morbus_model (простое заболевание)

    protected $fields = array(
        'MorbusLink_id' => null,
        'MorbusLinkType_id' => null,
        'pmUser_id' => null,
    );

    /**
     * MorbusLink_model constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * @param $name
	 * @param $value
	 * @throws Exception
	 */
    public function __set($name, $value)
    {
        $result  = parent::__set($name, $value);
        if (($name == 'morbus')||($name == 'morbus_l')){
            $this->tryFindLink();
        }
        return $result;
    }

	/**
	 * Некая функция
	 */
    private function tryFindLink(){
        if (
            $this->morbus && ($this->morbus instanceof MorbusBase_model)
            && $this->morbus_l && ($this->morbus_l instanceof Morbus_model)
            && $this->morbus_l->Morbus_id && $this->morbus->MorbusBase_id) {

        }
    }

	/**
	 * @return array|mixed
	 */
    public function save(){
        if ($this->validate()->isValid()) {
            if ($this->MorbusLink_id > 0) {
                $proc = 'p_MorbusLink_upd';
            } else {
                $proc = 'p_MorbusLink_ins';
            }
            $params = array();
            foreach ($this->fields as $key => $value) {
                $params[$key] = $value;
            }
            $query = "
       			declare
       				@Error_Code bigint,
       				@Error_Message varchar(4000),
       				@MorbusLink_id bigint = :MorbusLink_id;
       			exec {$this->scheme}.$proc
                    @MorbusLink_id     = @MorbusLink_id    , -- bigint
                    @Morbus_id         = :Morbus_id        , -- bigint
                    @Morbus_lid        = :Morbus_lid       , -- bigint
                    @MorbusLinkType_id = :MorbusLinkType_id, -- bigint
                    @pmUser_id         = :pmUser_id             , -- bigint
                    @Error_Code        = @Error_Code      output, -- int
                    @Error_Message     = @Error_Message   output  -- varchar(4000)
                select @MorbusLink_id as MorbusLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
       		";
            $dbresponse = $this->db->query($query, $params);
            if (is_object($dbresponse)) {
                $result = $dbresponse->result('array');
                $this->MorbusLink_id = $result[0]['MorbusLink_id'];
            }else {
                $result = array(
                    0 => array(
                        'Morbus_id' => null,
                        'Error_Code'=> null,
                        'Error_Msg'=>  'При сохранении связи между заболеваниями произошли ошибки'
                    )
                );
            }
        } else {
            $result = array(
                0 => array(
                    'Morbus_id' => null,
                    'Error_Code'=> null,
                    'Error_Msg'=>  'При сохранении связи между заболеваниями произошли ошибки: <br/>'.implode('<br/>', $this->getErrors())
                )
            );
        }
        return $result;
    }

    /**
     * @return MorbusLink_model
     */
    public function validate()
    {
        $this->clearErrors();
        $this->valid = true;
        if (!($this->MorbusLinkType_id)) {
            $this->addError('Не указан тип сохраняемой связи');
            if (in_array($this->MorbusLinkType_id, array())) {

            }
        } else {
            //проверка наличия связываемых заболеваний
            if ((!($this->morbus instanceof MorbusBase_model))) {
                $this->addError('Для сохранения связи не указано основное заболевание');
            }
            if (!($this->morbus_l instanceof Morbus_model)) {
                $this->addError('Для сохранения связи не указано связываемое заболевание');
            }
            if (!($this->morbus->isValid())) {
                $this->addError('Основное заболевание в связи содержит ошибки');
            }
            if (!($this->morbus_l->isValid())) {
                $this->addError('Связываемое заболевание в связи содержит ошибки');
            }
            if (!($this->morbus->MorbusBase_id)) {
                $this->addError('У основного общего заболевания в связи не указан идентификатор (Оно еще не сохранено?)');
            }
            if (!($this->morbus_l->Morbus_id)) {
                $this->addError('У связываемого заболевания в связи не указан идентификатор (Оно еще не сохранено?)');
            }
            if (!($this->MorbusLinkType_id)) {
                $this->addError('Не указан тип сохраняемой связи');
            }
        }
        return $this;
    }
}