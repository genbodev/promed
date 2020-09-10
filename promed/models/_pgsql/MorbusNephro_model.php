<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusNephro_model - модель для MorbusNephro
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      11.2014
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 * @property-read Morbus_model $Morbus
 * @property PersonWeight_model $PersonWeight_model
 * @property PersonHeight_model $PersonHeight_model
 * @property-read EvnDiagNephro_model $evnDiagNephro
 * @property-read MorbusNephroLab_model $morbusNephroLab
 * @property-read MorbusNephroDisp_model $morbusNephroDisp
 */
class MorbusNephro_model extends swPgModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;
	/*
	* Список полей для метода updateMorbusSpecific
	*/
	private $entityFields = array(
		'MorbusNephro' => array(
			'Morbus_id',
			'MorbusNephro_begDate',// Дата постановки на учет
			'MorbusNephro_firstDate',// Давность заболевания до установления диагноза
			'NephroDiagConfType_id',// Способ установления диагноза
			'NephroDiagConfType_cid',// Способ подтверждения диагноза
			'NephroCRIType_id',// Наличие ХПН
			'MorbusNephro_CRIDinamic',// Динамика ХПН
			'MorbusNephro_IsHyperten',// Артериальная гипертензия (Да/Нет)
			'PersonHeight_id',// Рост (в см)
			'PersonWeight_id',// Вес (в кг)
			'DialysisType_id',//
			'MorbusNephro_dialDate',//
			'KidneyTransplantType_id',//
			'MorbusNephro_transDate',//
			'MorbusNephro_Treatment',// Назначенное лечение
			'DispGroupType_id',// Группа диспансерного учета
			'NephroResultType_id',// Исход наблюдения
			'MorbusNephro_deadDT',//Дата смерти
		),
		'Morbus' => array(
			'MorbusBase_id',
			'Evn_pid', //Учетный документ, в рамках которого добавлено заболевание
			'Diag_id','MorbusKind_id','Morbus_Name','Morbus_Nick',
			'Morbus_setDT','Morbus_disDT','MorbusResult_id'
		),
		'MorbusBase' => array(
			'Person_id','Evn_pid','MorbusType_id','MorbusBase_setDT',
			'MorbusBase_disDT','MorbusResult_id'
		)
	);

	protected $_MorbusType_id = 46;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_EDIT_FORM,
		));
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'nephro';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->getMorbusTypeSysNick());
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Не удалось определить тип заболевания', 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * @return string
	 */
	function getGroupRegistry()
	{
		return 'NephroRegistry';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusNephro';
	}

	/**
	 * Удаление данных специфик заболевания заведенных из регистра, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeletePersonRegister
	 * @param PersonRegister_model $model
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление записи регистра
	 */
	public function onBeforeDeletePersonRegister(PersonRegister_model $model, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых нет ссылки на Evn
		// если таковых разделов нет, то этот метод можно убрать
	}

	/**
	 * Удаление данных специфик заболевания заведенных в учетном документе, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeleteEvn
	 * @param EvnAbstract_model $evn
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление учетного документа
	 */
	public function onBeforeDeleteEvn(EvnAbstract_model $evn, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых есть ссылка на Evn
		// если таковых нет, то этот метод можно убрать
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = array();
		switch ($name) {
			case self::SCENARIO_DO_SAVE:
				$rules[self::ID_KEY] = array(
					'field' => 'MorbusNephro_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор специфики',
					'type' => 'id'
				);
				$rules['morbus_id'] = array(
					'field' => 'Morbus_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор простого заболевания',
					'type' => 'id'
				);
				$rules['person_id'] = array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['diag_id'] = array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['evn_pid'] = array(
					'field' => 'Evn_pid',
					'label' => 'Учетный документ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['begdate'] = array(
					'field' => 'MorbusNephro_begDate',
					'label' => 'Дата постановки на учет',
					'rules' => 'trim',
					'type' => 'date'
				);
				$rules['firstdate'] = array(
					'field' => 'MorbusNephro_firstDate',
					'label' => 'Дата возникновения симптомов до установления диагноза',
					'rules' => 'trim',
					'type' => 'date'
				);
				$rules['nephrodiagconftype_id'] = array(
					'field' => 'NephroDiagConfType_id',
					'label' => 'Способ установления диагноза',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['nephrodiagconftype_cid'] = array(
					'field' => 'NephroDiagConfType_cid',
					'label' => 'Способ подтверждения диагноза',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['nephrocritype_id'] = array(
					'field' => 'NephroCRIType_id',
					'label' => 'Стадия ХБП',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['cridinamic'] = array(
					'field' => 'MorbusNephro_CRIDinamic',
					'label' => 'Динамика ХПН',
					'rules' => 'trim',
					'type' => 'string'
				);
				$rules['ishyperten'] = array(
					'field' => 'MorbusNephro_IsHyperten',
					'label' => 'Артериальная гипертензия',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['personheight_id'] = array(
					'field' => 'PersonHeight_id',
					'label' => 'Рост',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['personheight_height'] = array(
					'field' => 'PersonHeight_Height',
					'label' => 'Рост',
					'rules' => 'trim|max_length[3]',
					'type' => 'int',
				);
				$rules['personweight_id'] = array(
					'field' => 'PersonWeight_id',
					'label' => 'Вес',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['personweight_weight'] = array(
					'field' => 'PersonWeight_Weight',
					'label' => 'Масса',
					'rules' => 'trim|max_length[3]',
					'type' => 'int',
				);
				$rules['treatment'] = array(
					'field' => 'MorbusNephro_Treatment',
					'label' => 'Назначенное лечение',
					'rules' => 'trim|max_length[100]',
					'type' => 'string'
				);
				$rules['dispgrouptype_id'] = array(
					'field' => 'DispGroupType_id',
					'label' => 'Группа диспансерного учета',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['nephroresulttype_id'] = array(
					'field' => 'NephroResultType_id',
					'label' => 'Исход наблюдения',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['ishemodialysis'] = array(
					'field' => 'MorbusNephro_IsHemodialysis',
					'label' => 'Признак гемодиализ / перитонеальный диализ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['iscadtratsplant'] = array(
					'field' => 'MorbusNephro_IsCadTratsplant',
					'label' => 'Признак трупная / родственная трансплантация почки',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['deaddt'] = array(
					'field' => 'MorbusNephro_deadDT',
					'label' => 'Дата смерти',
					'rules' => 'trim',
					'type' => 'date'
				);
				$rules['dialysistype_id'] = array(
					'field' => 'DialysisType_id',
					'label' => 'Диализ. Тип',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['dialdate'] = array(
					'field' => 'MorbusNephro_dialDate',
					'label' => 'Диализ. Дата',
					'rules' => 'trim',
					'type' => 'date'
				);
				$rules['kidneytransplanttype_id'] = array(
					'field' => 'KidneyTransplantType_id',
					'label' => 'Трансплантация почки. Тип',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['transdate'] = array(
					'field' => 'MorbusNephro_transDate',
					'label' => 'Трансплантация почки. Дата',
					'rules' => 'trim',
					'type' => 'date'
				);
				$rules['Mode'] = array(
					'field' => 'Mode',
					'label' => 'Режим сохранения',
					'rules' => 'trim|required',
					'type' => 'string'
				);
				break;
			case 'doSavePersonDispForm':
				$rules[self::ID_KEY] = array(
					'field' => 'MorbusNephro_id',
					'rules' => 'trim',
					'label' => 'Идентификатор специфики',
					'type' => 'id'
				);
				$rules['morbus_id'] = array(
					'field' => 'Morbus_id',
					'rules' => 'trim',
					'label' => 'Идентификатор простого заболевания',
					'type' => 'id'
				);
				$rules['person_id'] = array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['diag_id'] = array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['firstdate'] = array(
					'field' => 'MorbusNephro_firstDate',
					'label' => 'Дата возникновения симптомов до установления диагноза',
					'rules' => 'trim',
					'type' => 'date'
				);
				$rules['nephrodiagconftype_id'] = array(
					'field' => 'NephroDiagConfType_id',
					'label' => 'Способ установления диагноза',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['nephrocritype_id'] = array(
					'field' => 'NephroCRIType_id',
					'label' => 'Стадия ХБП',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['ishyperten'] = array(
					'field' => 'MorbusNephro_IsHyperten',
					'label' => 'Артериальная гипертензия',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['personheight_id'] = array(
					'field' => 'PersonHeight_id',
					'label' => 'Рост',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['personheight_height'] = array(
					'field' => 'PersonHeight_Height',
					'label' => 'Рост',
					'rules' => 'trim|max_length[3]',
					'type' => 'int',
				);
				$rules['personweight_id'] = array(
					'field' => 'PersonWeight_id',
					'label' => 'Вес',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['personweight_weight'] = array(
					'field' => 'PersonWeight_Weight',
					'label' => 'Масса',
					'rules' => 'trim|max_length[3]',
					'type' => 'int',
				);
				$rules['treatment'] = array(
					'field' => 'MorbusNephro_Treatment',
					'label' => 'Назначенное лечение',
					'rules' => 'trim|max_length[100]',
					'type' => 'string'
				);
				$rules['MorbusNephroLabList'] = array(
					'field' => 'MorbusNephroLabList',
					'label' => 'Лабораторные исследования',
					'rules' => 'trim',
					'type' => 'string'
				);
				break;
			case 'checkByPersonDispForm':
				$rules['person_id'] = array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['diag_id'] = array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
				$rules[self::ID_KEY] = array(
					'field' => 'Morbus_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор',
					'type' => 'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Получение списка пользователей регистра
	 */
	function loadUsersRegistry($data, $group)
	{
		if (empty($group)) {
			$group = $this->groupRegistry;
		}
		$query = "
			select PMUser_id as \"PMUser_id\"
			from v_pmUserCache
			where pmUser_groups ilike '%{$group}%'
				and Lpu_id = ?
		";
		$result = $this->db->query($query, array($data['Lpu_id']));
		$response = $result->result('array');
		if ( is_array($response) && count($response) > 0 ) {
			return $response;
		} else	{
			return false;
		}
	}

	/**
	 * Проверка обязательных параметров специфики
	 *
	 * @params Mode
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 *	- persondisp_form - это ввод данных специфики в форме "Диспансерные карты пациентов: Добавление / Редактирование"
	 */
	protected function checkParams($data)
	{
		if ( empty($data['Mode']) ) {
			throw new Exception('Не указан режим сохранения');
		}
		$fields = array(
			'Diag_id' => 'Идентификатор диагноза'
		,'Person_id' => 'Идентификатор человека'
		,'Evn_pid' => 'Идентификатор движения/посещения'
		,'pmUser_id' => 'Идентификатор пользователя'
		,'Morbus_id' => 'Идентификатор заболевания'
		,'MorbusNephro_id' => 'Идентификатор специфики заболевания'
		);
		switch ($data['Mode']) {
			case 'check_by_evn':
				$check_fields_list = array('Evn_pid','Diag_id','Person_id','pmUser_id');
				break;
			case 'check_by_personregister':
				$check_fields_list = array('Diag_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'persondisp_form':
			case 'personregister_viewform':
				$check_fields_list = array('MorbusNephro_id','Morbus_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'evnsection_viewform':
			case 'evnvizitpl_viewform':
				$check_fields_list = array('MorbusNephro_id','Morbus_id','Evn_pid','pmUser_id');
				break;
			default:
				throw new Exception('Указан неправильный режим сохранения');
				break;
		}
		$errors = array();
		foreach($check_fields_list as $field) {
			if( empty($data[$field]) )
			{
				$errors[] = 'Не указан '. $fields[$field];
			}
		}
		if( count($errors) > 0 ) {
			throw new Exception(implode('<br />',$errors));
		}
		return $data;
	}

	/**
	 * Сохранение полей записи регистра из формы "Диспансерные карты пациентов: Добавление / Редактирование"
	 *
	 * @param array $data
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @throws Exception
	 */
	function doSavePersonDispForm($data)
	{
		$this->isAllowTransaction = false;
		$data['Mode'] = 'persondisp_form';
		try {
			if (empty($data['Person_id']) || empty($data['Diag_id'])) {
				throw new Exception('Не указан человек или диагноз');
			}
			if (empty($data['Morbus_id']) || empty($data['MorbusNephro_id'])) {
				$this->load->library('swMorbus');
				$data['Morbus_setDT'] = $this->currentDT->format('Y-m-d');
				$tmp = swMorbus::checkByPersonRegister($this->getMorbusTypeSysNick(), $data, 'onBeforeSavePersonRegister');
				$data['Morbus_id'] = $tmp['Morbus_id'];
				$data['MorbusNephro_id'] = $tmp['MorbusNephro_id'];
			}
			$data = $this->checkParams($data);
			//exit(var_export($data, true));

			// Стартуем транзакцию
			$this->isAllowTransaction = true;
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}
			$PersonWeight_delid = NULL;
			$PersonHeight_delid = NULL;
			if (array_key_exists('PersonWeight_Weight', $data)) {
				if ( empty($data['PersonWeight_Weight']) ) {
					if (isset($data['PersonWeight_id'])) {
						$PersonWeight_delid = $data['PersonWeight_id'];
					}
					$data['PersonWeight_id'] = NULL;
				} else {
					// создаем или обновляем запись о весе
					$result = $this->savePersonWeight(array(
						'Server_id' => $data['Server_id'],
						'PersonWeight_id' => $data['PersonWeight_id'],
						'Person_id' => $data['Person_id'],
						'PersonWeight_setDate' => date('Y-m-d'),
						'PersonWeight_Weight' => $data['PersonWeight_Weight'],
						'Evn_id'=>null,
						'pmUser_id' => $data['pmUser_id']
					));
					$data['PersonWeight_id'] = $result[0]['PersonWeight_id'];
				}
			}

			if (array_key_exists('PersonHeight_Height', $data)) {
				if ( empty($data['PersonHeight_Height']) ) {
					if (isset($data['PersonHeight_id'])) {
						$PersonHeight_delid = $data['PersonHeight_id'];
					}
					$data['PersonHeight_id'] = NULL;
				} else {
					// создаем или обновляем запись о росте
					$result = $this->savePersonHeight(array(
						'Server_id' => $data['Server_id'],
						'PersonHeight_id' => $data['PersonHeight_id'],
						'Person_id' => $data['Person_id'],
						'PersonHeight_setDate' => date('Y-m-d'),
						'PersonHeight_Height' => $data['PersonHeight_Height'],
						'Evn_id'=>null,
						'pmUser_id' => $data['pmUser_id']
					));
					$data['PersonHeight_id'] = $result[0]['PersonHeight_id'];
				}
			}

			//update таблиц Morbus, MorbusNephro
			$tmp = $this->updateMorbusSpecific($data);
			if ( !empty($tmp[0]['Error_Msg']) ) {
				throw new Exception($tmp[0]['Error_Msg']);
			}
			$response = $tmp;

			if (isset($PersonWeight_delid)) {
				// удаляем запись о весе
				$this->deletePersonWeight($PersonWeight_delid);
			}
			if (isset($PersonHeight_delid)) {
				$this->deletePersonHeight($PersonHeight_delid);
			}


			if ( !empty($data['MorbusNephroLabList']) ) {
				$this->load->model('MorbusNephroLab_model', 'morbusNephroLab');
				ConvertFromWin1251ToUTF8($data['MorbusNephroLabList']);
				$MorbusNephroLabList = json_decode($data['MorbusNephroLabList'], true);
				if ( is_array($MorbusNephroLabList) ) {
					for ( $i = 0; $i < count($MorbusNephroLabList); $i++ ) {
						$MorbusNephroLab = array(
							'session' => $data['session'],
							'MorbusNephro_id' => $data['MorbusNephro_id']
						);
						if( empty($MorbusNephroLabList[$i]['RateType_id'])
							|| !is_numeric($MorbusNephroLabList[$i]['RateType_id'])
							|| empty($MorbusNephroLabList[$i]['MorbusNephroLab_Date'])
							|| CheckDateFormat($MorbusNephroLabList[$i]['MorbusNephroLab_Date']) > 0
							|| empty($MorbusNephroLabList[$i]['Rate_ValueStr'])
							|| !is_string($MorbusNephroLabList[$i]['Rate_ValueStr'])
							|| !isset($MorbusNephroLabList[$i]['RecordStatus_Code'])
							|| !is_numeric($MorbusNephroLabList[$i]['RecordStatus_Code'])
							|| !in_array($MorbusNephroLabList[$i]['RecordStatus_Code'], array(0, 2, 3))
						) {
							continue;
						}
						$MorbusNephroLab['MorbusNephroLab_id'] = $MorbusNephroLabList[$i]['MorbusNephroLab_id'];
						if ($MorbusNephroLab['MorbusNephroLab_id'] <= 0) {
							$MorbusNephroLab['MorbusNephroLab_id'] = null;
						}
						$MorbusNephroLab['Rate_id'] = $MorbusNephroLabList[$i]['Rate_id'];
						$MorbusNephroLab['RateType_id'] = $MorbusNephroLabList[$i]['RateType_id'];
						$MorbusNephroLab['Rate_ValueStr'] = $MorbusNephroLabList[$i]['Rate_ValueStr'];
						$MorbusNephroLab['MorbusNephroLab_Date'] = ConvertDateFormat($MorbusNephroLabList[$i]['MorbusNephroLab_Date']);
						$tmp = null;
						$this->morbusNephroLab->reset();
						switch ( $MorbusNephroLabList[$i]['RecordStatus_Code'] ) {
							case 0:
							case 2:
								$this->morbusNephroLab->setScenario(swModel::SCENARIO_DO_SAVE);
								$tmp = $this->morbusNephroLab->doSave($MorbusNephroLab, false);
								break;
							case 3:
								$tmp = $this->morbusNephroLab->doDelete($MorbusNephroLab, false);
								break;
						}
						if ( empty($tmp) ) {
							throw new Exception('Ошибка', 500);
						}
						if ( !empty($tmp['Error_Msg']) ) {
							throw new Exception($tmp['Error_Msg']);
						}
					}
				}
			}
			$this->commitTransaction();
			return $response;
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Сохранение специфики заболевания. <br />'. $e->getMessage()));
		}
	}

	/**
	 * Сохранение
	 *
	 * @param array $data Обязательные параметры:
	 * 1) Evn_pid или пара Person_id и Diag_id
	 * 2) pmUser_id
	 * 3) Mode
	 * @param bool $isAllowTransaction
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @throws Exception
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	function doSave($data = array(), $isAllowTransaction = true) {
		try {
			$this->isAllowTransaction = false;
			$data = $this->checkParams($data);
			//exit(var_export($data, true));
			$this->_beforeSave($data);
			$data['Evn_aid'] = null;

			if (!empty($data['NephroResultType_id']) && !empty($data['MorbusNephro_id'])) {
				// надо проставить дату исключения в списке нуждающегося в диализе
				$resp_mnd = $this->queryResult("
					select
						MorbusNephroDialysis_id as \"MorbusNephroDialysis_id\"
					from
						v_MorbusNephroDialysis
					where
						MorbusNephroDialysis_endDT is null
						and MorbusNephro_id = :MorbusNephro_id
				", array(
					'MorbusNephro_id' => $data['MorbusNephro_id']
				));

				foreach($resp_mnd as $one_mnd) {
					$this->db->query("
						update
							MorbusNephroDialysis
						set
							MorbusNephroDialysis_endDT = dbo.tzGetDate(),
							MorbusNephroDialysis_updDT = dbo.tzGetDate(),
							pmUser_updID = :pmUser_id,
							PersonRegisterOutCause_id = :PersonRegisterOutCause_id
						where
							MorbusNephroDialysis_id = :MorbusNephroDialysis_id
					", array(
						'MorbusNephroDialysis_id' => $one_mnd['MorbusNephroDialysis_id'],
						'PersonRegisterOutCause_id' => $data['NephroResultType_id'] == 4 ? 1 : 9,
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}

			if (in_array($data['Mode'],array(
				'evnsection_viewform','evnvizitpl_viewform'
			))) {
				// Проверка существования у человека актуального учетного документа с данной группой диагнозов для привязки к нему заболевания и определения последнего диагноза заболевания
				if (empty($data['Evn_pid'])) {
					$data['Evn_pid'] = null;
				}
				if (empty($data['Person_id'])) {
					$data['Person_id'] = null;
				}
				if (empty($data['Diag_id'])) {
					$data['Diag_id'] = null;
				}
				$this->load->library('swMorbus');
				$result = swMorbus::getStaticMorbusCommon()->loadLastEvnData($this->getMorbusTypeSysNick(), $data['Evn_pid'], $data['Person_id'], $data['Diag_id']);
				if ( !empty($result) ) {
					//учетный документ найден
					$data['Evn_aid'] = $result[0]['Evn_id'];
					$data['Diag_id'] = $result[0]['Diag_id'];
					$data['Person_id'] = $result[0]['Person_id'];
				} else {
					throw new Exception('Ошибка определения актуального учетного документа с данным заболеванием');
				}
			}

			if (in_array($data['Mode'],array(
					'personregister_viewform','persondisp_form'
				)) || $data['Evn_pid'] == $data['Evn_aid']) {
				// Если редактирование происходит из актуального учетного документа
				// или из панели просмотра в форме записи регистра, то сохраняем данные
				// Стартуем транзакцию
				$this->isAllowTransaction = $isAllowTransaction;
				if ( !$this->beginTransaction() ) {
					$this->isAllowTransaction = false;
					throw new Exception('Ошибка при попытке запустить транзакцию');
				}
				$PersonWeight_delid = NULL;
				$PersonHeight_delid = NULL;
				if (array_key_exists('PersonWeight_Weight', $data)) {
					if ( empty($data['PersonWeight_Weight']) ) {
						if (isset($data['PersonWeight_id'])) {
							$PersonWeight_delid = $data['PersonWeight_id'];
						}
						$data['PersonWeight_id'] = NULL;
					} else {
						// создаем или обновляем запись о весе
						$result = $this->savePersonWeight(array(
							'Server_id' => $data['Server_id'],
							'PersonWeight_id' => $data['PersonWeight_id'],
							'Person_id' => $data['Person_id'],
							'PersonWeight_setDate' => date('Y-m-d'),
							'PersonWeight_Weight' => $data['PersonWeight_Weight'],
							'Evn_id'=>$data['Evn_pid'],
							'pmUser_id' => $data['pmUser_id']
						));
						$data['PersonWeight_id'] = $result[0]['PersonWeight_id'];
					}
				}

				if (array_key_exists('PersonHeight_Height', $data)) {
					if ( empty($data['PersonHeight_Height']) ) {
						if (isset($data['PersonHeight_id'])) {
							$PersonHeight_delid = $data['PersonHeight_id'];
						}
						$data['PersonHeight_id'] = NULL;
					} else {
						// создаем или обновляем запись о росте
						$result = $this->savePersonHeight(array(
							'Server_id' => $data['Server_id'],
							'PersonHeight_id' => $data['PersonHeight_id'],
							'Person_id' => $data['Person_id'],
							'PersonHeight_setDate' => date('Y-m-d'),
							'PersonHeight_Height' => $data['PersonHeight_Height'],
							'Evn_id'=>$data['Evn_pid'],
							'pmUser_id' => $data['pmUser_id']
						));
						$data['PersonHeight_id'] = $result[0]['PersonHeight_id'];
					}
				}

				//update таблиц Morbus, MorbusNephro
				$tmp = $this->updateMorbusSpecific($data);
				if ( isset($tmp[0]['Error_Msg']) ) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				$response = $tmp;

				if (isset($PersonWeight_delid)) {
					// удаляем запись о весе
					$this->deletePersonWeight($PersonWeight_delid);
				}
				if (isset($PersonHeight_delid)) {
					$this->deletePersonHeight($PersonHeight_delid);
				}
				if (!empty($data['Evn_pid']) && !empty($data['Morbus_id'])) {
					$this->load->library('swMorbus');
					$tmp = swMorbus::updateMorbusIntoEvn(array(
						'Evn_id' => $data['Evn_pid'],
						'Morbus_id' => $data['Morbus_id'],
						'session' => $data['session'],
						'mode' => 'onAfterSaveMorbusSpecific',
					));
					if (isset($tmp['Error_Msg'])) {
						//нужно откатить транзакцию
						throw new Exception($tmp['Error_Msg']);
					}
				}

				$this->commitTransaction();
				return $response;
			} else {
				//Ничего не сохраняем
				throw new Exception('Данные не были сохранены, т.к. данный учетный документ не является актуальным для данного заболевания.');
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Сохранение специфики заболевания. <br />'. $e->getMessage()));
		}
	}

	/**
	 * Применение данных извещения
	 * @param EvnNotifyNephro_model $evn
	 * @return array
	 * @throws Exception
	 */
	function setEvnNotifyNephro(EvnNotifyNephro_model $evn)
	{
		$morbus = $this->getFirstRowFromQuery('
			select
				MorbusBase_id as "MorbusBase_id",
				MorbusNephro_id as "MorbusNephro_id"
			from v_MorbusNephro
			where Morbus_id = :Morbus_id
			limit 1
		', array('Morbus_id' => $evn->Morbus_id));
		if (empty($morbus)) {
			throw new Exception('Не удалось получить данные заболевания!', 500);
		}
		$data = array(
			'MorbusBase_id' => $morbus['MorbusBase_id'],
			'MorbusBase_setDT' => !empty($evn->firstDate)?$evn->firstDate->format('Y-m-d'):date('Y-m-d'),

			'Morbus_id' => $evn->Morbus_id,
			'Diag_id' => $evn->Diag_id,
			'Morbus_setDT' => !empty($evn->firstDate)?$evn->firstDate->format('Y-m-d'):date('Y-m-d'),

			'MorbusNephro_id' => $morbus['MorbusNephro_id'],
			'NephroDiagConfType_id' => $evn->NephroDiagConfType_id,
			'NephroCRIType_id' => $evn->NephroCRIType_id,
			'MorbusNephro_IsHyperten' => $evn->IsHyperten,
			'MorbusNephro_firstDate' => !empty($evn->firstDate)?$evn->firstDate->format('Y-m-d'):date('Y-m-d'),

			'pmUser_id' => $evn->promedUserId,
		);
		if (!empty($evn->PersonHeight_id)) {
			$data['PersonHeight_id'] = $evn->PersonHeight_id;
		}
		if (!empty($evn->PersonWeight_id)) {
			$data['PersonWeight_id'] = $evn->PersonWeight_id;
		}
		if (!empty($evn->Treatment)) {
			$data['MorbusNephro_Treatment'] = $evn->Treatment;
		}
		return $this->updateMorbusSpecific($data);
	}

	/**
	 * Сохранение специфики
	 * @param $data
	 * @return array
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	private function updateMorbusSpecific($data) {
		$err_arr = array();
		$entity_saved_arr = array();
		$not_edit_fields = array('Evn_pid', 'Person_id','MorbusNephro_id', 'Morbus_id', 'MorbusBase_id','MorbusType_id','Morbus_disDT','MorbusBase_disDT');
		if (isset($data['field_notedit_list']) && is_array($data['field_notedit_list'])) {
			$not_edit_fields = array_merge($not_edit_fields,$data['field_notedit_list']);
		}

		foreach($this->entityFields as $entity => $l_arr) {

			$allow_save = false;
			foreach($data as $key => $value) {
				if (in_array($key, $l_arr) && !in_array($key, $not_edit_fields)) {
					$allow_save = true;
					break;
				}
			}
			if ( $allow_save && !empty($data[$entity.'_id']) ) {
				$q = 'select '. implode(', ', ($l_arr . ' as "' . $l_arr . '"')) .' from dbo.v_'. $entity .' where '. $entity .'_id = :'. $entity .'_id limit 1';
				$p = array($entity.'_id' => $data[$entity.'_id']);
				$r = $this->db->query($q, $data);
				//echo getDebugSQL($q, $data);
				if (is_object($r)) {
					$result = $r->result('array');
					if( empty($result) || !is_array($result[0]) || count($result[0]) == 0 ) {
						$err_arr[] = 'Получение данных '. $entity .' По идентификатору '. $data[$entity.'_id'] .' данные не получены';
						continue;
					}
					foreach($result[0] as $key => $value) {
						if (is_object($value) && $value instanceof DateTime)
						{
							$value = $value->format('Y-m-d H:i:s');
						}
						//в $data[$key] может быть null
						$p[$key] = array_key_exists($key, $data)?$data[$key]:$value;
						// ситуация, когда пользователь удалил какое-то значение
						$p[$key] = (empty($p[$key]) || $p[$key]=='0')?null:$p[$key];
					}
				} else {
					$err_arr[] = 'Получение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
				$field_str = '';
				foreach($l_arr as $key) {
					$field_str .= '
						'. $key .' := :'. $key .',';
				}
				$q = "
					select
						{$entity}_id as \"{$entity}_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from dbo.p_{$entity}_upd(
						{$entity}_id := :{$entity}_id,
						{$field_str}
						pmUser_id := pmUser_id
					)
				";
				$p['pmUser_id'] = $data['pmUser_id'];
				$r = $this->db->query($q, $p);
				if (is_object($r)) {
					$result = $r->result('array');
					if( !empty($result[0]['Error_Msg']) )
					{
						$err_arr[] = 'Сохранение данных '. $entity .' '. $result[0]['Error_Msg'];
						continue;
					}
					$entity_saved_arr[$entity .'_id'] = $data[$entity.'_id'];
				} else {
					$err_arr[] = 'Сохранение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
			} else {
				continue;
			}
		}
		$entity_saved_arr['Morbus_id'] = $data['Morbus_id'];
		$entity_saved_arr['Error_Msg'] = (count($err_arr) > 0) ? implode('<br />',$err_arr) : null;
		return array($entity_saved_arr);
	}

	/**
	 * Создание заболевания с проверкой на существование заболевания у человека
	 */
	function checkByPersonDispForm($data)
	{
		//проверка диагноза
		$this->load->library('swMorbus');
		$this->_saveResponse['MorbusType_id'] = $this->getMorbusTypeId();
		$arr = swMorbus::getMorbusTypeListByDiag($data['Diag_id']);
		if ( empty($arr[$this->getMorbusTypeId()]) ) {
			return $this->_saveResponse;
		}
		try {
			$this->load->library('swMorbus');
			$data['Morbus_setDT'] = $this->currentDT->format('Y-m-d');
			$tmp = swMorbus::checkByPersonRegister($this->getMorbusTypeSysNick(), $data, 'onBeforeSavePersonRegister');
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			return $this->_saveResponse;
		}
		$this->_saveResponse = array_merge($tmp, $this->_saveResponse);
		//проверка наличия извещения, записи регистра
		$query = "
			select
				EN.EvnNotifyNephro_id as \"EvnNotifyNephro_id\",
				PR.PersonRegister_id as \"PersonRegister_id\",
				to_char(M.Morbus_insDT, 'dd.mm.yyyy') as \"dateNotify\",
				MP.Person_Fin as \"MedPersonalFio\",
				MPH.Person_Fin as \"MedPersonalZavFio\"
			from v_Morbus M
				left join v_EvnNotifyNephro EN on M.Morbus_id = EN.Morbus_id
				left join v_MedPersonal mp on MP.MedPersonal_id = EN.MedPersonal_id
				left join v_MedPersonal mph on MPH.MedPersonal_id = EN.MedPersonal_hid
				left join v_PersonRegister PR on M.Morbus_id = PR.Morbus_id
					and PR.PersonRegisterOutCause_id is null
			where M.Morbus_id = ?
			limit 1
		";
		$res = $this->db->query($query, array($this->_saveResponse['Morbus_id']));
		if ( is_object($res) ) {
			$tmp = $res->result('array');
		} else {
			$this->_saveResponse['Error_Msg'] = 'Не удалось выполнить проверку наличия извещения, записи регистра';
			return $this->_saveResponse;
		}
		return array_merge($tmp[0], $this->_saveResponse);
	}

	/**
	 * Создание специфики заболевания
	 * Должно выполняться внутри транзакции
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data['MorbusBase_id']) ||empty($data['Person_id'])
			|| empty($data['Morbus_id']) || empty($data['Diag_id']) || empty($data['Morbus_setDT'])
			|| empty($data['mode'])
			|| false == in_array($data['mode'], array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify', 'onBeforeSaveEvnNotifyFromJrn', 'onAfterSaveEvn'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = array();
		$queryParams['pmUser_id'] = $this->promedUserId;
		$queryParams['Morbus_id'] = $data['Morbus_id'];
		$queryParams['Person_id'] = $data['Person_id'];
		// todo: запрос лучше разделить в php для того чтобы иметь возможность получать ошибки из хранимки
		$query = "
			-- должно быть одно на Morbus
            WITH cte0 AS (
            	select {$tableName}_id 
                from v_{$tableName} 
                where Morbus_id = :Morbus_id
                limit 1
            ),
            cte1 AS (
	            SELECT
                	CASE WHEN (SELECT {$tableName}_id FROM cte0) IS NULL THEN 
                    (SELECT 
                    	{$tableName}_id
                    FROM p_{$tableName}_ins(
					{$tableName}_id := null,
					Morbus_id := :Morbus_id,
					pmUser_id := :pmUser_id))
                    ELSE 
                    	(SELECT {$tableName}_id FROM cte0)
                    END AS {$tableName}_id
            ),
            cte2 AS (
            	select 
                	PersonEvn_id, 
                	Server_id 
                from v_PersonState
				where Person_id = :Person_id 
                limit 1
            )

			SELECT 
                    CASE WHEN (SELECT {$tableName}_id FROM cte0) IS NULL 
                    THEN (SELECT {$tableName}_id FROM cte1) 
                    ELSE (SELECT {$tableName}_id FROM cte0) END
                AS \"{$tableName}_id\",
                    CASE WHEN (SELECT {$tableName}_id FROM cte0) IS NULL 
                    THEN (SELECT PersonEvn_id FROM cte2) 
                    ELSE null END
                AS \"PersonEvn_id\",
                    CASE WHEN (SELECT {$tableName}_id FROM cte0) IS NULL 
                    THEN (SELECT Server_id FROM cte2) 
                    ELSE null END
                AS \"Server_id\",
                    CASE WHEN (SELECT {$tableName}_id FROM cte0) IS NULL 
                    THEN 2
                    ELSE 1 END
                AS \"IsCreate\",
                '' AS \"Error_Code\",
                '' AS \"Error_Msg\";
			";

		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка БД', 500);
		}
		$resp = $result->result('array');
		if (!empty($resp[0]['Error_Msg'])) {
			throw new Exception($resp[0]['Error_Msg'], 500);
		}
		if (empty($resp[0][$tableName . '_id'])) {
			throw new Exception('Что-то пошло не так', 500);
		}
		$this->_saveResponse[$tableName . '_id'] = $resp[0][$tableName . '_id'];

		if ( 2 == $resp[0]['IsCreate'] ) {
			$this->load->model('EvnDiagNephro_model', 'evnDiagNephro');
			$this->evnDiagNephro->setScenario(swModel::SCENARIO_AUTO_CREATE);
			$tmp = $this->evnDiagNephro->doSave(array(
				'session' => $data['session'],
				'Morbus_id' => $data['Morbus_id'],
				'EvnDiagNephro_setDate' => $data['Morbus_setDT'],// Y-m-d
				'Diag_id' => $data['Diag_id'],
				'Person_id' => $data['Person_id'],
				'PersonEvn_id' => $resp[0]['PersonEvn_id'],
				'Server_id' => $resp[0]['Server_id'],
			), false);
			if ( !empty($tmp['Error_Msg']) ) {
				throw new Exception($tmp['Error_Msg']);
			}
			$this->_saveResponse['EvnDiagNephro_id'] = $tmp['EvnDiagNephro_id'];
		}

		return $this->_saveResponse;
	}

	/**
	 * Метод получения данных для панели просмотра
	 * При вызове из формы просмотра записи регистра параметр MorbusNephro_pid будет содержать Person_id, также будет передан PersonRegister_id
	 * При вызове из формы просмотра движения/посещения параметр MorbusNephro_pid будет содержать Evn_id просматриваемого движения/посещения
	 */
	function getViewData($data)
	{
		if (empty($data['session'])) { $data['session'] = null; }
		if (empty($data['MorbusNephro_pid'])) { $data['MorbusNephro_pid'] = null; }
		if (empty($data['PersonRegister_id'])) { $data['PersonRegister_id'] = null; }
		$this->load->library('swMorbus');
		$params = swMorbus::onBeforeViewData($this->getMorbusTypeSysNick(), $data['session'], $data['MorbusNephro_pid'], $data['PersonRegister_id']);
		if ($params['Error_Msg']) {
			throw new Exception($params['Error_Msg']);
		}
		$params['MorbusNephro_pid'] = $data['MorbusNephro_pid'];
		// предусмотрено создание специфических учетных документов (в которых есть ссылка на посещение/движение из которого они созданы)
		$query = "
			select
				" . swMorbus::getAccessTypeQueryPart('MV', 'MB', 'MorbusNephro_pid', '1', '0', 'accessType', 'AND not exists(
									select Evn.Evn_id from v_Evn Evn
									where
										Evn.Person_id = MB.Person_id
										and Evn.Morbus_id = MV.Morbus_id
										and Evn.EvnClass_id in (11,13,32)
										and Evn.Evn_id <> :MorbusNephro_pid
										and Evn.Evn_setDT > EvnEdit.Evn_setDT
										and exists (
											(select v_PersonHeight.Evn_id
											from v_PersonHeight
											where v_PersonHeight.Evn_id = Evn.Evn_id
											limit 1)
											union all
											(select v_PersonWeight.Evn_id
											from v_PersonWeight
											where v_PersonWeight.Evn_id = Evn.Evn_id
											limit 1)
										)
									limit 1
								) /* можно редактировать, если нет более актуального документа в рамках которого изменялась специфика */') . ",
				to_char(MV.MorbusNephro_begDate, 'dd.mm.yyyy') as \"MorbusNephro_begDate\",
				to_char(MV.MorbusNephro_firstDate, 'dd.mm.yyyy') as \"MorbusNephro_firstDate\",
				to_char(MV.MorbusNephro_deadDT, 'dd.mm.yyyy') as \"MorbusNephro_deadDT\",
				to_char(MV.MorbusNephro_dialDate, 'dd.mm.yyyy') as \"MorbusNephro_dialDate\",
				to_char(MV.MorbusNephro_transDate, 'dd.mm.yyyy') as \"MorbusNephro_transDate\",
				ndst.NephroDiagConfType_id as \"NephroDiagConfType_id\",
				ndst.NephroDiagConfType_Name as \"NephroDiagConfType_Name\",
				MV.NephroDiagConfType_cid as \"NephroDiagConfType_cid\",
				ndct.NephroDiagConfType_Name as \"NephroDiagConfType_cName\",
				nct.NephroCRIType_id as \"NephroCRIType_id\",
				nct.NephroCRIType_Name as \"NephroCRIType_Name\",
				ph.PersonHeight_id as \"PersonHeight_id\",
				pw.PersonWeight_id as \"PersonWeight_id\",
				ph.PersonHeight_Height \"PersonHeight_Height\",
				pw.PersonWeight_Weight \"PersonWeight_Weight\",
				ktt.KidneyTransplantType_id as \"KidneyTransplantType_id\",
				ktt.KidneyTransplantType_Name as \"KidneyTransplantType_Name\",
				dt.DialysisType_id as \"DialysisType_id\",
				dt.DialysisType_Name as \"DialysisType_Name\",
				dgt.DispGroupType_id as \"DispGroupType_id\",
				dgt.DispGroupType_Name as \"DispGroupType_Name\",
				nrt.NephroResultType_id as \"NephroResultType_id\",
				nrt.NephroResultType_Name as \"NephroResultType_Name\",
				MV.MorbusNephro_IsHyperten as \"MorbusNephro_IsHyperten\",
				IsHyperten.YesNo_Name as \"IsHyperten_Name\",
				MV.MorbusNephro_CRIDinamic as \"MorbusNephro_CRIDinamic\",
				MV.MorbusNephro_Treatment as \"MorbusNephro_Treatment\",
				MV.Diag_id as \"Diag_id\",
				MV.MorbusNephro_id as \"MorbusNephro_id\",
				MV.Morbus_id as \"Morbus_id\",
				MB.MorbusBase_id as \"MorbusBase_id\",
				:MorbusNephro_pid as \"MorbusNephro_pid\",
				MB.Person_id as \"Person_id\"
			from
				v_MorbusNephro MV
				inner join v_MorbusBase MB on MB.MorbusBase_id = MV.MorbusBase_id
				left join v_NephroDiagConfType ndst on ndst.NephroDiagConfType_id = MV.NephroDiagConfType_id
				left join v_NephroDiagConfType ndct on ndct.NephroDiagConfType_id = MV.NephroDiagConfType_cid
				left join v_NephroCRIType nct on nct.NephroCRIType_id = MV.NephroCRIType_id
				left join v_PersonHeight ph on ph.PersonHeight_id = MV.PersonHeight_id
				left join v_PersonWeight pw on pw.PersonWeight_id = MV.PersonWeight_id
				left join v_KidneyTransplantType ktt on ktt.KidneyTransplantType_id = MV.KidneyTransplantType_id
				left join v_DialysisType dt on dt.DialysisType_id = MV.DialysisType_id
				left join v_DispGroupType dgt on dgt.DispGroupType_id = MV.DispGroupType_id
				left join v_NephroResultType nrt on nrt.NephroResultType_id = MV.NephroResultType_id
				left join v_YesNo IsHyperten on IsHyperten.YesNo_id = MV.MorbusNephro_IsHyperten
			where
				MV.Morbus_id = :Morbus_id
		";
		//echo getDebugSql($query, $params);die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Читает одну строку для формы редактирования
	 * @param array $data
	 * @return array
	 */
	function doLoadEditForm($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$params = array(
			'Morbus_id' => $data['Morbus_id']
		);
		$query = "
			select
				case when MV.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				to_char(MV.MorbusNephro_begDate, 'dd.mm.yyyy') as \"MorbusNephro_begDate\",
				to_char(MV.MorbusNephro_firstDate, 'dd.mm.yyyy') as \"MorbusNephro_firstDate\",
				to_char(MV.MorbusNephro_deadDT, 'dd.mm.yyyy') as \"MorbusNephro_deadDT\",
				to_char(MV.MorbusNephro_dialDate, 'dd.mm.yyyy') as \"MorbusNephro_dialDate\",
				to_char(MV.MorbusNephro_transDate, 'dd.mm.yyyy') as \"MorbusNephro_transDate\",
				MV.NephroDiagConfType_id as \"NephroDiagConfType_id\",
				MV.NephroDiagConfType_cid as \"NephroDiagConfType_cid\",
				MV.NephroCRIType_id as \"NephroCRIType_id\",
				ph.PersonHeight_id as \"PersonHeight_id\",
				pw.PersonWeight_id as \"PersonWeight_id\",
				ph.PersonHeight_Height \"PersonHeight_Height\",
				pw.PersonWeight_Weight \"PersonWeight_Weight\",
				MV.KidneyTransplantType_id as \"KidneyTransplantType_id\",
				MV.DialysisType_id as \"DialysisType_id\",
				MV.DispGroupType_id as \"DispGroupType_id\",
				MV.NephroResultType_id as \"NephroResultType_id\",
				MV.MorbusNephro_IsHyperten as \"MorbusNephro_IsHyperten\",
				MV.MorbusNephro_CRIDinamic as \"MorbusNephro_CRIDinamic\",
				MV.MorbusNephro_Treatment as \"MorbusNephro_Treatment\",
				MV.Diag_id as \"Diag_id\",
				MV.MorbusNephro_id as \"MorbusNephro_id\",
				MV.Morbus_id as \"Morbus_id\",
				MB.MorbusBase_id as \"MorbusBase_id\",
				MB.Person_id as \"Person_id\"
			from
				v_MorbusNephro MV
				inner join v_MorbusBase MB on MB.MorbusBase_id = MV.MorbusBase_id
				left join v_PersonHeight ph on ph.PersonHeight_id = MV.PersonHeight_id
				left join v_PersonWeight pw on pw.PersonWeight_id = MV.PersonWeight_id
			where
				MV.Morbus_id = :Morbus_id
			limit 1
		";
		//echo getDebugSql($query, $params);die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array();
		}
	}


	/**
	 * Запись роста
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function savePersonHeight($data)
	{
		$this->load->model('PersonHeight_model');
		$result = $this->PersonHeight_model->savePersonHeight(array(
			'Server_id' => $data['Server_id'],
			'PersonHeight_id' => $data['PersonHeight_id'],
			'Person_id' => $data['Person_id'],
			'PersonHeight_setDate' => $data['PersonHeight_setDate'],
			'PersonHeight_Height' => $data['PersonHeight_Height'],
			'PersonHeight_IsAbnorm' => NULL,
			'HeightAbnormType_id' => NULL,
			'HeightMeasureType_id' => NULL,
			'Evn_id'=>$data['Evn_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!empty($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg']);
		}
		if (empty($result[0]['PersonHeight_id'])) {
			throw new Exception('Ошибка при сохранении роста');
		}
		return $result;
	}

	/**
	 * Удаляет запись о росте
	 * @param int $id
	 * @return void
	 * @throws Exception
	 */
	function deletePersonHeight($id)
	{
		$this->load->model('PersonHeight_model');
		$result = $this->PersonHeight_model->deletePersonHeight(array(
			'PersonHeight_id' => $id
		));
		if (empty($result)) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (удаление результатов измерения роста пациента)');
		}
		if (!empty($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg']);
		}
	}

	/**
	 * Запись массы
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function savePersonWeight($data)
	{
		// создаем или обновляем запись о весе
		$this->load->model('PersonWeight_model');
		$result = $this->PersonWeight_model->savePersonWeight(array(
			'Server_id' => $data['Server_id'],
			'PersonWeight_id' => $data['PersonWeight_id'],
			'Person_id' => $data['Person_id'],
			'PersonWeight_setDate' => $data['PersonWeight_setDate'],
			'PersonWeight_Weight' => $data['PersonWeight_Weight'],
			'PersonWeight_IsAbnorm' => NULL,
			'WeightAbnormType_id' => NULL,
			'WeightMeasureType_id' => NULL,
			'Evn_id'=>$data['Evn_id'],
			'Okei_id' => 37,//кг
			'pmUser_id' => $data['pmUser_id']
		));
		if (!empty($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg']);
		}
		if (empty($result[0]['PersonWeight_id'])) {
			throw new Exception('Ошибка при сохранении массы');
		}
		return $result;
	}

	/**
	 * Удаляет запись о весе
	 * @param int $id
	 * @return void
	 * @throws Exception
	 */
	function deletePersonWeight($id)
	{
		// удаляем запись о весе
		$this->load->model('PersonWeight_model');
		$result = $this->PersonWeight_model->deletePersonWeight(array(
			'PersonWeight_id' => $id
		));
		if (empty($result)) {
			throw new Exception('Ошибка при выполнении запроса к базе данных (удаление результатов измерения массы пациента)');
		}
		if (!empty($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg']);
		}
	}
	/**
	 * Вывод печатной формы «Карта динамического наблюдения»
	 */
	function doPrint($data)
	{
		$parse_data = $this->loadPrintData($data['Morbus_id']);
		if (empty($parse_data)) {
			return 'Не удалось получить данные карты';
		}
		$parse_data['MorbusNephroDispDates'] = array();
		$parse_data['MorbusNephroDisp'] = array();
		$data['MorbusNephro_id'] = $parse_data['MorbusNephro_id'];
		$data['isOnlyLast'] = 0;
		$this->load->model('MorbusNephroDisp_model', 'morbusNephroDisp');
		$tmp = $this->morbusNephroDisp->doLoadGrid($data);
		if (is_array($tmp)) {
			foreach ($tmp as $row) {
				$d = $row['MorbusNephroDisp_Date'];
				$t = $row['RateType_id'];
				if (!in_array($d, $parse_data['MorbusNephroDispDates'])) {
					$parse_data['MorbusNephroDispDates'][] = $d;
				}
				if (empty($parse_data['MorbusNephroDisp'][$t])) {
					$parse_data['MorbusNephroDisp'][$t] = array(
						'RateType_Name' => $row['RateType_Name'],
						'RateValues' => array(),
					);
				}
				$parse_data['MorbusNephroDisp'][$t]['RateValues'][$d] = $row['Rate_ValueStr'];
			}
		}
		$this->load->library('parser');
		return $this->parser->parse('print_nephroregistry', $parse_data, true);
	}

	/**
	 * Получаем данные для печатной формы
	 */
	protected function loadPrintData($id)
	{
		$query = "
			select
				MN.MorbusNephro_id as \"MorbusNephro_id\",
				to_char( EN.EvnNotifyNephro_diagDate, 'dd.mm.yyyy') as \"MorbusNephro_diagDate\",
				to_char( MN.MorbusNephro_transDate, 'dd.mm.yyyy') as \"MorbusNephro_transDate\",
				to_char( MN.MorbusNephro_dialDate, 'dd.mm.yyyy') as \"MorbusNephro_dialDate\",
				to_char( MN.MorbusNephro_firstDate, 'dd.mm.yyyy') as \"MorbusNephro_firstDate\",
				to_char( MN.MorbusNephro_begDate, 'dd.mm.yyyy') as \"MorbusNephro_begDate\",
				to_char( MN.MorbusNephro_deadDT, 'dd.mm.yyyy') as \"MorbusNephro_deadDT\",
				MN.MorbusNephro_CRIDinamic as \"MorbusNephro_CRIDinamic\",
				MN.KidneyTransplantType_id as \"KidneyTransplantType_id\",
				MN.DialysisType_id as \"DialysisType_id\",
				MN.DispGroupType_id as \"DispGroupType_id\",
				MN.NephroResultType_id as \"NephroResultType_id\",
				Diag.Diag_Code as \"Diag_Code\",
				Diag.Diag_Name as \"Diag_Name\",
				NephroDiagConfType.NephroDiagConfType_Code as \"NephroDiagConfType_Code\",
				NephroDiagConfType.NephroDiagConfType_Name as \"NephroDiagConfType_Name\",
				NephroDiagConfTypeC.NephroDiagConfType_Code as \"NephroDiagConfTypeC_Code\",
				NephroDiagConfTypeC.NephroDiagConfType_Name as \"NephroDiagConfTypeC_Name\",
				NephroCRIType.NephroCRIType_Code as \"NephroCRIType_Code\",
				NephroCRIType.NephroCRIType_Name as \"NephroCRIType_Name\",
				PersonHeight.PersonHeight_Height PersonHeight_Height as \"PersonHeight_Height PersonHeight_Height\",
				PersonWeight.PersonWeight_Weight PersonWeight_Weight as \"PersonWeight_Weight PersonWeight_Weight\",
				PR.PersonRegister_id as \"PersonRegister_Num\",
				Sex.Sex_Code as \"Sex_Code\",
				Sex.Sex_Name as \"Sex_Name\",
				a.Address_Address as \"Person_Address\",
				to_char( PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				PS.Person_Phone as \"Person_Phone\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\"
			from
				v_MorbusNephro MN
				inner join v_MorbusBase MB on MB.MorbusBase_id = MN.MorbusBase_id
				inner join v_PersonState PS on PS.Person_id = MB.Person_id
				left join Sex on Sex.Sex_id = PS.Sex_id
				left join v_Address a on a.Address_id = coalesce(PS.PAddress_id,PS.UAddress_id)
				left join NephroDiagConfType on NephroDiagConfType.NephroDiagConfType_id = MN.NephroDiagConfType_id
				left join NephroDiagConfType NephroDiagConfTypeC on NephroDiagConfTypeC.NephroDiagConfType_id = MN.NephroDiagConfType_cid
				left join NephroCRIType on NephroCRIType.NephroCRIType_id = MN.NephroCRIType_id
				left join lateral(
					select v_EvnDiag.Diag_id
					from v_EvnDiag
					where v_EvnDiag.Morbus_id = MN.Morbus_id
					order by v_EvnDiag.EvnDiag_setDT desc
					limit 1
				) EvnDiagNephro on true
				left join Diag on Diag.Diag_id = EvnDiagNephro.Diag_id
				left join PersonHeight on PersonHeight.PersonHeight_id = MN.PersonHeight_id
				left join PersonWeight on PersonWeight.PersonWeight_id = MN.PersonWeight_id
				left join v_PersonRegister PR on PR.Morbus_id = MN.Morbus_id
				left join v_EvnNotifyNephro EN on PR.Morbus_id = MN.Morbus_id
			where
				EN.Morbus_id = ?
			limit 1
		";
		$res = $this->db->query($query, array($id));
		if ( is_object($res) ) {
			$tmp = $res->result('array');
			if (is_array($tmp) && !empty($tmp)) {
				return $tmp[0];
			}
		}
		return array();
	}

	/**
	 * Добавление полей для метода updateMorbusSpecific
	 */
	protected function addEntityField($entity, $field) {
		$this->entityFields[$entity][] = $field;
	}
}
