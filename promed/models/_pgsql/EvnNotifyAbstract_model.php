<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnAbstract_model.php');
/**
 * EvnNotifyAbstract_model - Модель "Базовое извещение"
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnNotifyBase
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      11.2014
 *
 * @property-read int $MorbusType_id Тип заболевания
 * @property int $MedPersonal_id Врач, создавший извещение
 * @property DateTime $niDate Дата не включения в  регистр
 * @property int $MedPersonal_niid Врач, не включивший в регистр
 * @property int $Lpu_niid Лпу, не включившее в регистр
 * @property int $PersonRegisterFailIncludeCause_id Причина невключения в регистр
 *
 * @property-read string $morbusTypeSysNick Тип заболевания
 */
abstract class EvnNotifyAbstract_model extends EvnAbstract_model
{
    protected $_MorbusType_id = null;

    /**
     * Определение типа заболевания
     * @return string
     */
    function getMorbusTypeSysNick()
    {
        return 'common';
    }

    /**
     * Определение типа заболевания
     * @return int
     * @throws Exception
     */
    function getMorbusType_id()
    {
        if (empty($this->_MorbusType_id)) {
            $this->load->library('swMorbus');
            $this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->morbusTypeSysNick);
            if (empty($this->_MorbusType_id)) {
                throw new Exception('Попытка получить идентификатор типа заболевания провалилась', 500);
            }
            if (!empty($this->_savedData['morbustype_id']) && $this->_MorbusType_id != $this->_savedData['morbustype_id']) {
                $this->_savedData['morbustype_id'] = $this->_MorbusType_id;
            }
        }
        return $this->_MorbusType_id;
    }

    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
        $this->_setScenarioList(array(
            self::SCENARIO_LOAD_EDIT_FORM,
            self::SCENARIO_AUTO_CREATE,
            self::SCENARIO_DO_SAVE,
        ));
    }

    /**
     * Проверка корректности данных модели для указанного сценария
     * @throws Exception
     */
    protected function _validate()
    {
        parent::_validate();
        if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
            if ( empty($this->MedPersonal_id) ) {
                throw new Exception('Не указан врач, создавший извещение');
            }
            if (empty($this->pid) && $this->evnClassId != 176) {
                throw new Exception('Не указан Учетный документ', 500);
            }
            if (empty($this->setDT) || empty($this->setDate)) {
                throw new Exception('Не указана Дата заполнения извещения', 500);
            }
        }
    }

    /**
     * Проверки и другая логика перед сохранением объекта
     * @param array $data Массив входящих параметров
     * @throws Exception
     */
    protected function _beforeSave($data = array())
    {
        parent::_beforeSave($data);
        if ($this->evnClassId != 176 && $this->evnClassId != 173) {
            $this->load->library('swMorbus');
            $tmp = swMorbus::onBeforeSaveEvnNotify($this->morbusTypeSysNick, $this->pid, $this->sessionParams);
            $this->setAttribute('morbustype_id', $tmp['MorbusType_id']);
            $this->setAttribute('morbus_id', $tmp['Morbus_id']);
            $this->_params['Morbus_Diag_id'] = $tmp['Diag_id'];
        }
    }

    /**
     * Логика после успешного выполнения запроса сохранения объекта
     * @param array $result Результат выполнения запроса
     * @throws Exception
     */
    protected function _afterSave($result)
    {
        $this->load->library('swMorbus');
        $tmp = swMorbus::onAfterSaveEvnNotify($this->morbusTypeSysNick, array(
            'EvnNotifyBase_id' => $this->id,
            'EvnNotifyBase_pid' => $this->pid,
            'EvnNotifyBase_setDate' => $this->setDate,
            'Server_id' => $this->Server_id,
            'PersonEvn_id' => $this->PersonEvn_id,
            'Person_id' => $this->Person_id,
            'Morbus_id' => $this->Morbus_id,
            'MorbusType_id' => $this->MorbusType_id,
            'Morbus_Diag_id' => $this->_params['Morbus_Diag_id'],
            'Lpu_id' => $this->Lpu_id,
            'MedPersonal_id' => $this->MedPersonal_id,
            'session' => $this->sessionParams
        ));
        $this->_saveResponse = array_merge($this->_saveResponse, $tmp);
    }

    /**
     * Возвращает список всех используемых ключей атрибутов объекта
     * @return array
     */
    static function defAttributes()
    {
        $arr = parent::defAttributes();
        $arr['pid']['label'] = 'Учетный документ';
        $arr['pid']['save'] = 'trim|required';
        $arr['setdate']['label'] = 'Дата создания извещения';
        if (isset($arr['setdt']['timeKey'])) unset($arr['setdt']['timeKey']);
        if (isset($arr['settime'])) unset($arr['settime']);
        $arr['morbus_id'] = array(
            'properties' => array(
                self::PROPERTY_IS_SP_PARAM,
                self::PROPERTY_NOT_SAFE,
            ),
            'alias' => 'Morbus_id',
            'label' => 'Заболевание',
            'save' => 'trim',
            'type' => 'id'
        );
        $arr['morbustype_id'] = array(
            'properties' => array(
                self::PROPERTY_IS_SP_PARAM,
                self::PROPERTY_NOT_SAFE,
            ),
            'alias' => 'MorbusType_id',
            'label' => 'Тип заболевания',
            'save' => 'trim',
            'type' => 'id'
        );
        $arr['medpersonal_id'] = array(
            'properties' => array(
                self::PROPERTY_IS_SP_PARAM,
            ),
            'alias' => 'MedPersonal_id',
            'label' => 'Врач, создавший извещение',
            'save' => 'trim|required',
            'type' => 'id'
        );
        $arr['nidate'] = array(
            'properties' => array(
                self::PROPERTY_NEED_TABLE_NAME,
                self::PROPERTY_IS_SP_PARAM,
                self::PROPERTY_DATE_TIME,
            ),
            'applyMethod'=>'_applyNiDate',
            'alias' => '_niDate',
            'label' => 'Дата не включения в  регистр',
            'save' => 'trim',
            'type' => 'date'
        );
        $arr['medpersonal_niid'] = array(
            'properties' => array(
                self::PROPERTY_IS_SP_PARAM,
            ),
            'alias' => 'MedPersonal_niid',
            'label' => 'Врач, не включивший в регистр',
            'save' => 'trim',
            'type' => 'id'
        );
        $arr['lpu_niid'] = array(
            'properties' => array(
                self::PROPERTY_IS_SP_PARAM,
            ),
            'alias' => 'Lpu_niid',
            'label' => 'Лпу, не включившее в регистр',
            'save' => 'trim',
            'type' => 'id'
        );
        $arr['personregisterfailincludecause_id'] = array(
            'properties' => array(
                self::PROPERTY_IS_SP_PARAM,
            ),
            'alias' => 'PersonRegisterFailIncludeCause_id',
            'label' => 'Причина невключения в регистр',
            'save' => 'trim',
            'type' => 'id'
        );
        return $arr;
    }

    /**
     * Определение идентификатора класса события
     * @return int
     */
    static function evnClassId()
    {
        return 85;
    }

    /**
     * Определение кода класса события
     * @return string
     */
    static function evnClassSysNick()
    {
        return 'EvnNotifyBase';
    }

    /**
     * Извлечение даты из входящих параметров
     * @param array $data
     * @return bool
     */
    protected function _applyNiDate($data)
    {
        return $this->_applyDate($data, 'nidate');
    }

    /**
     * Получение данных для формы
     */
    function doLoadEditForm($data)
    {
        $data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
        $this->applyData($data);
        $this->_validate();
        return array(array(
            $this->tableName() . '_id' => $this->id,
            $this->tableName() . '_pid' => $this->pid,
            $this->tableName() . '_setDate' => $this->setDate,
            'MedPersonal_id' => $this->MedPersonal_id,
            'Server_id' => $this->Server_id,
            'Person_id' => $this->Person_id,
            'PersonEvn_id' => $this->PersonEvn_id,
        ));
    }

    /**
     * Получаем данные для проверки наличия извещения/записи регистра
     *
     * В общем случае по одному заболеванию можно создать одно извещение и одну запись регистра
     * @param $Person_id
     * @param $evn_Diag_id
     * @return bool|array Если заболевание ещё не создано, возвращается пустой массив, а в случае ошибки - false
     */
    function loadDataCheckExists($Person_id, $evn_Diag_id = null)
    {
        $tableName = $this->tableName();
        $this->load->library('swMorbus');
        return swMorbus::getStaticMorbusCommon()->checkExistsExtended($this->morbusTypeSysNick, $Person_id, $evn_Diag_id,"
				,EN.{$tableName}_id as \"EvnNotifyBase_id\"
				,PR.PersonRegister_id as \"PersonRegister_id\"
				,PR.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\"" ,"
				left join v_{$tableName} EN on EN.Morbus_id = Morbus.Morbus_id
				left join v_PersonRegister PR on PR.Morbus_id = Morbus.Morbus_id"
        );
    }
}