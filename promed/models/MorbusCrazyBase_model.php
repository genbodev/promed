<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusCrazyBase_model - модель психоспецифики общего заболевания.
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
 * @property int MorbusCrazyBase_id
 * @property int CrazyDeathCauseType_id
 * @property int MorbusCrazyBase_EarlyCareCount
 */
class MorbusCrazyBase_model extends MorbusBase_model
{
    /**
     * @return string
     */
    protected function getTableName()
    {
        return 'MorbusCrazyBase';
    }

    /**
     * @var Collection_model
     */
    private $MorbusCrazyBaseDrugStart;

    private $MorbusCrazyBaseFields = array(
        'MorbusCrazyBase_id'            ,//
        'CrazyDeathCauseType_id'        ,//Причина смерти
        'MorbusCrazyBase_EarlyCareCount',//Ранее находился на принудительном долечивании, число раз

    );

	/**
	 * @return bool
	 */
    protected function canDelete()
    {
        //todo реализовать проверки при удалении
        return true;
    }

	/**
	 * MorbusCrazyBase_model constructor.
	 */
    function __construct()
    {
        parent::__construct();
        foreach ($this->MorbusCrazyBaseFields as $value) {
            $this->fields[$value] = null;
        }
        $this->MorbusCrazyBaseDrugStart = new Collection_model();
        $this->MorbusCrazyBaseDrugStart->setTableName('MorbusCrazyBaseDrugStart');
        $this->MorbusCrazyBaseDrugStart->setInputRules(array(
            array('field'=>'MorbusCrazyBaseDrugStart_id'    ,'label' => '','rules' => '', 'type' => 'int'),// NULL
            array('field'=>'MorbusCrazyBase_id'             ,'label' => 'Общее заболевание','rules' => 'required', 'type' => 'id'),// Общее заболевание
            array('field'=>'MorbusCrazyBaseDrugStart_Name'  ,'label' => 'Наименование вещества','rules' => '', 'type' => 'string'),// Наименование вещества
            array('field'=>'CrazyDrugReceptType_id'         ,'label' => 'Тип приема','rules' => '', 'type' => 'id'),// Тип приема
            array('field'=>'MorbusCrazyBaseDrugStart_Age'   ,'label' => 'Полных лет','rules' => '', 'type' => 'id'),// Полных лет
            array('field'=>'pmUser_id','label' => 'идентификатор пользователя системы Промед','rules' => '', 'type' => 'id'),//
            array('field'=>'RecordStatus_Code','label' => 'идентификатор состояния записи','rules' => '', 'type' => 'int'),//
        ));
    }

    //todo добавить коллекцию Возраст начала употребления психоактивных средств

    private $parentLoaded = false;

	/**
	 * @param array $values
	 */
    public function assign($values)
    {
        parent::assign($values);
        if (!$this->parentLoaded) {
            if ($this->MorbusBase_id) {
                $this->parentLoaded = true;
                parent::load('MorbusBase_id', $this->MorbusBase_id);
            }
        }
        if (isset($values['MorbusCrazyBaseDrugStart'])){
            $this->MorbusCrazyBaseDrugStart->parseJson($values['MorbusCrazyBaseDrugStart']);
        }

    }

	/**
	 * @return $this
	 */
    public function validate()
    {
        $this->valid = true;

		// Причина смерти (необязательное поле)
		if ($this->CrazyDeathCauseType_id != NULL) {
			$this->validateByReference('CrazyDeathCauseType', 'Причина смерти');
		}
		
        return $this;
    }

	/**
	 * @return array|bool|mixed
	 * @throws Exception
	 */
    function save()
    {
        $result = parent::save();
        $save_ok = self::save_ok($result);
        if ($save_ok){
            $result = $this->MorbusCrazyBaseDrugStart->saveAll(array($this->getKeyFieldName() => $this->__get($this->getKeyFieldName()),'pmUser_id' => $this->pmUser_id));
        }
        return $result;
    }

	/**
	 * @return array
	 */
    function loadMorbusCrazyBaseDrugStart(){
        $this->MorbusCrazyBaseDrugStart->loadAll(
            'MorbusCrazyBase_id',
            $this->MorbusCrazyBase_id,
            '*,'.$this->getNameRetrieveFieldListEntry('CrazyDrugReceptType')
        );
        return $this->MorbusCrazyBaseDrugStart->getItems();
    }

	/**
	 * @return array
	 */
    public function getFields()
    {
        return array_merge(
            parent::getFields(),
            array('MorbusCrazyBaseDrugStart' => $this->MorbusCrazyBaseDrugStart->getItems())
        );
    }

	/**
	 * @param null $field
	 * @param null $value
	 * @return bool|mixed
	 * @throws Exception
	 */
    function load($field = null, $value = null)
    {
        if ($this->MorbusCrazyBase_id) {
            $result = parent::load($field, $value);
        } else {
            if ($this->MorbusBase_id) {
                $result = parent::load('MorbusBase_id', $this->MorbusBase_id);
            } else {
                throw new Exception('Для загрузки специфики общего заболевания по психиатрии/наркологии требуется указать его идентификатор или идентификатор общего заболевания');
            }
        }
        $result['MorbusCrazyBaseDrugStart'] = $this->loadMorbusCrazyBaseDrugStart();
        return $result;
    }

	/**
	 * @param Массив $params
	 * @param Массив $query_paramlist_exclude
	 * @return array
	 */
    protected function getParamList($params, $query_paramlist_exclude)
    {
        $query_paramlist = '';
        foreach ($this->MorbusCrazyBaseFields as $key => $value) {
            $params[$value] = $this->__get($value);
            if (!in_array($key, $query_paramlist_exclude)) {
                $query_paramlist = "$query_paramlist @$value = :$value,";
            }
        }
        $params['MorbusBase_id'] = $this->MorbusBase_id;
        $query_paramlist = "$query_paramlist @MorbusBase_id = :MorbusBase_id,";
        $params['pmUser_id'] = $this->pmUser_id;
        $query_paramlist = "$query_paramlist @pmUser_id = :pmUser_id,";
        $params[$this->getKeyFieldName()] = $this->__get($this->getKeyFieldName());
        return array($params, $query_paramlist);
    }

}