<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusCrazySection_model - модель специфики движения по психиатрии/наркологии
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
require_once 'MorbusCrazyBase_model.php';
require_once 'Collection_model.php';
/**
 * @property int      $Evn_id                             ; //Идентификатор Движения КВС
 * @property int      $CrazyHospType_id                   ; //Госпитализирован
 * @property int      $CrazySupplyType_id                 ; //Поступление
 * @property int      $CrazyDirectType_id                 ; //Кем направлен
 * @property int      $CrazySupplyOrderType_id            ; //Порядок поступления
 * @property int      $CrazyDirectFromType_id             ; //Откуда поступил
 * @property int      $CrazyPurposeDirectType_id          ; //Цель направления
 * @property int      $CrazyJudgeDecisionArt35Type_id     ; //Решение судьи по ст. 35
 * @property int      $CrazyLeaveInvalidType_id           ; //Инвалидность при выписке по псих. заболеванию
 * @property int      $CrazySurveyHIVType_id              ; //Обследование больного на ВИЧ
 * @property int      $CrazyLeaveType_id                  ; //Выбыл
 * @property string   $MorbusCrazySection_NumCard         ; //Медицинская карта стационарного больного
 * @property int      $CrazyForceTreatResultType_id       ; //В случае окончания принудительного лечения
 * @property Datetime $MorbusCrazySection_LastForceDisDT  ; //Дата окончания предыдущего принудительного лечения
 * @property Datetime $MorbusCrazySection_LastLeaveDisDT  ; //Дата предыдущей выписки из психиатрического или наркологического стационара
 * @property int      $MorbusCrazySection_LTMDayCount     ; //Число дней работы в ЛТМ
 * @property int      $MorbusCrazySection_HolidayDayCount ; //Число дней лечебных отпусков
 * @property int      $MorbusCrazySection_HolidayCount    ; //Число лечебных отпусков
 * @property int      $MorbusCrazySection_IsAnotherSyringe; //Использование чужих шприцов, игл, приспособлений в течение последнего года
 * @property int      $MorbusCrazySection_IsLiveWithJunkie; //Проживание с потребителем психоактивных средств
 * @property int      $CrazyDrugVolumeType_id             ; //Полученный объем наркологической помощи в данном учреждении
 * @property MorbusCrazy_model $MorbusCrazy
 * @property int $pmUser_id
 * @property int $MorbusCrazySection_id
 */
class MorbusCrazySection_model extends Abstract_model
{
	/**
	 * @return string
	 */
    protected function getTableName()
    {
        return 'MorbusCrazySection';
    }

	/**
	 * @return bool
	 */
    protected function canDelete()
    {
        //todo реализовать проверки при удалении
        return true;
    }

    var $fields = array(
        'MorbusCrazySection_id'=>null,
        'Evn_id'                              => null, //Идентификатор Движения КВС
        'CrazyHospType_id'                    => null, //Госпитализирован
        'CrazySupplyType_id'                  => null, //Поступление
        'CrazyDirectType_id'                  => null, //Кем направлен
        'CrazySupplyOrderType_id'             => null, //Порядок поступления
        'CrazyDirectFromType_id'              => null, //Откуда поступил
        'CrazyPurposeDirectType_id'           => null, //Цель направления
        'CrazyJudgeDecisionArt35Type_id'      => null, //Решение судьи по ст. 35
        'CrazyLeaveInvalidType_id'            => null, //Инвалидность при выписке по псих. заболеванию
        'CrazySurveyHIVType_id'               => null, //Обследование больного на ВИЧ
        'CrazyLeaveType_id'                   => null, //Выбыл
        'MorbusCrazySection_NumCard'          => null, //Медицинская карта стационарного больного
        'CrazyForceTreatResultType_id'        => null, //В случае окончания принудительного лечения
        'MorbusCrazySection_LastForceDisDT'   => null, //Дата окончания предыдущего принудительного лечения
        'MorbusCrazySection_LastLeaveDisDT'   => null, //Дата предыдущей выписки из психиатрического или наркологического стационара
        'MorbusCrazySection_LTMDayCount'      => null, //Число дней работы в ЛТМ
        'MorbusCrazySection_HolidayDayCount'  => null, //Число дней лечебных отпусков
        'MorbusCrazySection_HolidayCount'     => null, //Число лечебных отпусков
        'MorbusCrazySection_IsAnotherSyringe' => null, //Использование чужих шприцов, игл, приспособлений в течение последнего года
        'MorbusCrazySection_IsLiveWithJunkie' => null, //Проживание с потребителем психоактивных средств
        'CrazyDrugVolumeType_id'              => null, //Полученный объем наркологической помощи в данном учреждении
        'pmUser_id'              => null, //
    );
    var $MorbusCrazy = null;

    private $MorbusCrazyForceTreat;
    //private $MorbusCrazyDrug;

	/**
	 * MorbusCrazySection_model constructor.
	 */
    function __construct()
    {
        parent::__construct();
        $this->MorbusCrazy = new MorbusCrazy_model();
        $this->MorbusCrazyForceTreat = new Collection_model();
        $this->MorbusCrazyForceTreat->setTableName('MorbusCrazyForceTreat');
        $this->MorbusCrazyForceTreat->setInputRules(array(
            array('field'=>'MorbusCrazyForceTreat_id'	        ,'label' => 'Идентификатор принудительного лечения','rules' => '', 'type' => 'int'),//
            array('field'=>'MorbusCrazy_id'	            ,'label' => 'Специфика психического/наркологического заболевания','rules' => 'required', 'type' => 'id'),//Специфика движения
            array('field'=>'MorbusCrazySection_id'	            ,'label' => 'Специфика движения                            ','rules' => 'required', 'type' => 'id'),//Специфика движения
            array('field'=>'MorbusCrazyForceTreat_setDT'        ,'label' => 'Дата изменения (продления)                    ','rules' => 'required', 'type' => 'date'),//Дата изменения (продления)
            array('field'=>'CrazyForceTreatType_id'             ,'label' => 'Вид принудительного лечения                   ','rules' => '', 'type' => 'id'),//Вид принудительного лечения
            array('field'=>'CrazyForceTreatJudgeDecisionType_id','label' => 'Статус решений суда по принудительному лечению','rules' => '', 'type' => 'id'),//Статус решений суда по принудительному лечению
            array('field'=>'pmUser_id'                          ,'label' => 'идентификатор пользователя системы Промед','rules' => '', 'type' => 'id'),//
            array('field'=>'RecordStatus_Code'                  ,'label' => 'идентификатор состояния записи','rules' => '', 'type' => 'int'),//
        ));
    }

	/**
	 * @return $this
	 */
    public function validate() {
        $this->valid = true;

		// Обязательные поля со ссылками на справочники
		$fields_required_reference = array(
			'Госпитализирован' => 'CrazyHospType',
			'Поступление' => 'CrazySupplyType',
			'Кем направлен' => 'CrazyDirectType',
			'Порядок поступления' => 'CrazySupplyOrderType',
			'Откуда поступил' => 'CrazyDirectFromType',
			'Цель направления' => 'CrazyPurposeDirectType'
		);

		// Проверки обязательных полей и существование в справочниках
		foreach ($fields_required_reference as $label => $name) {
			if ($this->validateRequired($name . '_id', $label)) {
				$this->validateByReference($name, $label);
			}
		}

		// Необязательные поля со ссылками на справочники
		$fields_reference = array(
			'Решение судьи по ст. 35' => 'CrazyJudgeDecisionArt35Type',
			'Инвалидность при выписке по псих. заболеванию' => 'CrazyLeaveInvalidType',
			'Обследование больного на ВИЧ' => 'CrazySurveyHIVType',
			'Выбыл' => 'CrazyLeaveType',
			'В случае окончания принудительного лечения' => 'CrazyForceTreatResultType',
			'Полученный объем наркологической помощи в данном учреждении' => 'CrazyDrugVolumeType'
		);

		// Проверки необязательных полей и существование в справочниках
		foreach ($fields_reference as $label => $name) {
			$field = $name . '_id';
			if ($this->$field != NULL) {
				$this->validateByReference($name, $label);
			}
		}

        return $this;
    }

	/**
	 * @param array $values
	 * @throws Exception
	 */
    public function assign($values) {
        parent::assign($values);
        if (isset($values['MorbusCrazyForceTreat'])){
            $this->MorbusCrazyForceTreat->parseJson($values['MorbusCrazyForceTreat']);
        }
        //Если у этого Движения КВС уже есть специфика, находим ее ИД, чтобы при сохранении новые данные легли в ту же запись
        $this_id = $this->getFirstResultFromQuery("Select {$this->getKeyFieldName()} from {$this->getSourceTableName()} where Evn_id = :Evn_id", array('Evn_id' => $this->Evn_id));
        if ($this_id) {
            $this->__set($this->getKeyFieldName(), $this_id);
        }
    }

	/**
	 * @return array|bool|mixed
	 * @throws Exception
	 */
    public function save(){
        $result = parent::save();
        $save_ok = self::save_ok($result);
        if ($save_ok){
            $result = $this->MorbusCrazyForceTreat->saveAll(
                array(
                    $this->getKeyFieldName() => $this->__get($this->getKeyFieldName()),
                    $this->MorbusCrazy->getKeyFieldName() => $this->MorbusCrazy->__get($this->MorbusCrazy->getKeyFieldName()),
                    'pmUser_id' => $this->pmUser_id
                )
            );
        }
        return $result;
    }

	/**
	 * @return bool|mixed
	 */
    function load()
    {
        $result = parent::load('Evn_id', $this->Evn_id);
        $result['loadMorbusCrazyForceTreat'] = $this->loadMorbusCrazyForceTreat();
        return $result;
    }

	/**
	 * @return array
	 */
    function loadMorbusCrazyForceTreat(){
        $this->MorbusCrazyForceTreat->loadAll(
            'MorbusCrazySection_id',
            $this->MorbusCrazySection_id,
            '*,'.$this->getNameRetrieveFieldListEntry('CrazyForceTreatType').','.
                $this->getNameRetrieveFieldListEntry('CrazyForceTreatJudgeDecisionType')
        );
        return $this->MorbusCrazyForceTreat->getItems();
    }

	/**
	 * @return array
	 */
    public function getFields()
    {
        return array_merge(
            parent::getFields(),
            $this->MorbusCrazy->getFields(),
            array('MorbusCrazyForceTreat' => $this->MorbusCrazyForceTreat->getItems())
        );
    }

	/**
	 * @param Массив $params
	 * @param Массив $query_paramlist_exclude
	 * @return array
	 */
    protected function getParamList($params, $query_paramlist_exclude)
    {
        list($params, $query_paramlist) = parent::getParamList($params, $query_paramlist_exclude);
        $params['MorbusCrazy_id'] = $this->MorbusCrazy->MorbusCrazy_id;
        $query_paramlist = "$query_paramlist MorbusCrazy_id := :MorbusCrazy_id,";
        $params[$this->getKeyFieldName()] = $this->__get($this->getKeyFieldName());
        return array($params, $query_paramlist);
    }

}
