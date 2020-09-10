<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusCrazySpecifics_model - Модель для работы со спецификой по психиатрии и наркологии
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
require_once 'Abstract_model.php';

/**
 * @property MorbusCrazySection_model $MorbusCrazySection
 * @property MorbusCrazyBase_model    $MorbusCrazyBase
 * @property MorbusCrazy_model    $MorbusCrazy
 * @property MorbusCrazyPerson_model  $MorbusCrazyPerson
 */
class MorbusCrazySpecifics_model extends Abstract_model
{
    public $MorbusCrazySection; //специфика движения по психиатрии/наркологии
    public $MorbusCrazyBase;    //специфика общего заболевания по психиатрии/наркологии
    public $MorbusCrazy;    //специфика простого заболевания по психиатрии/наркологии
    public $MorbusCrazyPerson;  //специфика человека по психиатрии/наркологии
    public $MorbusCrazyDrug; //коллекция "Употребление психоактивных веществ на момент госпитализации"

    /**
     * @return bool
     */
    protected function canDelete()
    {
        //todo реализовать проверки при удалении
        return true;
    }

	/**
	 * @throws Exception
	 */
    protected function getTableName()
    {
        throw new Exception('У данной модели нет таблицы в базе данных');
    }

	/**
	 * MorbusCrazySpecifics_model constructor.
	 */
    function __construct()
    {
        parent::__construct();
        $this->load->model('MorbusCrazySection_model','MorbusCrazySection');
        $this->load->model('MorbusCrazyBase_model','MorbusCrazyBase');
        $this->load->model('MorbusCrazy_model','MorbusCrazy');
        $this->load->model('MorbusCrazyPerson_model','MorbusCrazyPerson');
        $this->load->model('Collection_model','MorbusCrazyDrug');
        $this->MorbusCrazyDrug->setTableName('MorbusCrazyDrug');
        $this->MorbusCrazyDrug->setInputRules([
            ['field'=>'MorbusCrazyDrug_id'    ,'label' => 'Идентификатор Употребление психоактивных веществ на момент госпитализации','rules' => '', 'type' => 'int'],//  Идентификатор Употребление психоактивных веществ на момент госпитализации
            ['field'=>'MorbusCrazyBase_id' ,'label' => 'Специфика общего психического/наркологического заболевания','rules' => 'required', 'type' => 'id'],//  Специфика движения
            ['field'=>'MorbusCrazySection_id' ,'label' => 'Специфика движения','rules' => 'required', 'type' => 'id'],//  Специфика движения
            ['field'=>'MorbusCrazyDrug_Name'  ,'label' => 'Наименование','rules' => 'required', 'type' => 'string'],//  Наименование
            ['field'=>'CrazyDrugType_id'      ,'label' => 'Вид вещества','rules' => '', 'type' => 'int'],//  Вид вещества
            ['field'=>'CrazyDrugReceptType_id','label' => 'Тип приема','rules' => '', 'type' => 'int'],//  Тип приема
            ['field'=>'pmUser_id'             ,'label' => 'идентификатор пользователя системы Промед','rules' => '', 'type' => 'id'],//
            ['field'=>'RecordStatus_Code'     ,'label' => 'идентификатор состояния записи','rules' => '', 'type' => 'int'],//
        ]);
    }

	/**
	 * @param null $Morbus_id
	 * @param null $EvnSection_id
	 * @return array
	 * @throws Exception
	 */
    function load($Morbus_id, $EvnSection_id){
        $this->MorbusCrazy->Morbus_id = $Morbus_id;
        $this->MorbusCrazy->load();
        $this->MorbusCrazySection->MorbusCrazy = $this->MorbusCrazy;
        $this->MorbusCrazySection->Evn_id = $EvnSection_id;
        $this->MorbusCrazySection->load();
        $this->MorbusCrazyBase->MorbusBase_id = $this->MorbusCrazy->MorbusBase->MorbusBase_id;
        $this->MorbusCrazyBase->load();
        $this->MorbusCrazyPerson->Person_id = $this->MorbusCrazy->getPersonId();
        $this->MorbusCrazyPerson->load();
        $this->loadMorbusCrazyDrug();
        return $this->assemblyToArray();
    }

	/**
	 * @return array|bool|mixed
	 * @throws Exception
	 */
    function save(){
        $this->start_transaction();
        $this->MorbusCrazy->transactional = false;
        $result = $this->MorbusCrazy->save();
        $save_ok = self::save_ok($result);
        if ($save_ok) {
            //специфика на заболевании сохранена успешно, продолжнаем сохранение
            $this->MorbusCrazySection->start_transaction();
            $this->MorbusCrazySection->MorbusCrazy = $this->MorbusCrazy;
            $result = $this->MorbusCrazySection->save();
            $save_ok = self::save_ok($result);
            if ($save_ok) {
                $save_ok = self::save_ok($result);
                if ($save_ok){
                    $this->MorbusCrazyBase->transactional = false;
                    $result = $this->MorbusCrazyBase->save();
                    if ($save_ok) {
                        $this->MorbusCrazyDrug->transactional = false;
                        $result = $this->MorbusCrazyDrug->saveAll(
                            array(
                                $this->MorbusCrazySection->getKeyFieldName() => $this->MorbusCrazySection->__get($this->MorbusCrazySection->getKeyFieldName()),
                                $this->MorbusCrazyBase->getKeyFieldName() => $this->MorbusCrazyBase->__get($this->MorbusCrazyBase->getKeyFieldName()),
                                'pmUser_id' => $this->MorbusCrazySection->pmUser_id//не хочу думать откуда правильнее взять это, по идее везде один и тот же
                            )
                        );
                        $save_ok = self::save_ok($result);
                        if ($save_ok) {
                            $this->MorbusCrazyPerson->transactional = false;
                            $result = $this->MorbusCrazyPerson->save();
                            if (self::save_ok($result)) {
                                $this->commit();
                            } else {
                                $this->rollback();
                            }
                        } else {
                            $this->rollback();
                        }
                    } else {
                        $this->rollback();
                    }
                } else {
                    $this->rollback();
                }
            } else {
                $this->rollback();
            }
        } else {
            $this->rollback();
        }
        return $result;
    }

	/**
	 * @return array
	 */
    function loadMorbusCrazyDrug(){
        $this->MorbusCrazyDrug->loadAll(
            'MorbusCrazySection_id',
            $this->MorbusCrazySection->MorbusCrazySection_id,
            '*,'.$this->getNameRetrieveFieldListEntry('CrazyDrugType').','.
                $this->getNameRetrieveFieldListEntry('CrazyDrugReceptType')
        );
        return $this->MorbusCrazyDrug->getItems();
    }

    /**
     * @param $data array
     * @throws Exception
     */
    public function assign ($data){
        $this->MorbusCrazy->assign($data);
        $this->MorbusCrazySection->assign($data);
        $this->MorbusCrazyBase->MorbusBase_id = $this->MorbusCrazy->getMorbusBase()->MorbusBase_id;
        $this->MorbusCrazyBase->assign($data);
        $this->MorbusCrazyPerson->Person_id = $this->MorbusCrazy->getPersonId();
        $this->MorbusCrazyPerson->assign($data);
        if (isset($data['MorbusCrazyDrug'])){
            $this->MorbusCrazyDrug->parseJson($data['MorbusCrazyDrug']);
        }
    }

    /**
     * @return array
     */
    private function assemblyToArray(){
        $result = array_merge(
            $this->MorbusCrazyBase->getFields(),
            $this->MorbusCrazyPerson->getFields(),
            $this->MorbusCrazySection->getFields(),
            array('MorbusCrazyDrug' => $this->MorbusCrazyDrug->getItems())
        );
        return $result;
    }

	/**
	 * @return $this
	 */
    public function validate()
    {
        //не рекомендуется использовать, все проверки выполняются при сохранении
        if ($this->MorbusCrazy->validate()->isValid()) {
            $this->addError('Специфика заболевания по психиатрии/наркологии имеет ошибки');
        };
        if ($this->MorbusCrazyBase->validate()->isValid()) {
            $this->addError('Специфика общего заболевания по психиатрии/наркологии имеет ошибки');
        };
        if ($this->MorbusCrazyPerson->validate()->isValid()) {
            $this->addError('Специфика человека по психиатрии/наркологии имеет ошибки');
        }
        if ($this->MorbusCrazySection->validate()->isValid()) {
            $this->addError('Специфика движения КВС по психиатрии/наркологии имеет ошибки');
        }
        return $this;//чтобы делать цепочку $this->validate()->isValid()
    }

}