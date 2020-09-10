<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnNotifyAbstract_model.php');

/**
 * EvnNotifyNephro_model - Модель "Извещение по нефрологии"
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      11.2014
 *
 * @property-read int $Diag_id Диагноз, справочник МКБ-10
 * @property-read DateTime $diagDate Дата установки
 * @property-read DateTime $firstDate Дата возникновения симптомов до установления диагноза
 * @property-read int $NephroDiagConfType_id Способ установления диагноза
 * @property-read int $NephroCRIType_id Наличие ХПН
 * @property-read int $IsHyperten Артериальная гипертензия (Да/Нет)
 * @property-read int $PersonHeight_id Рост (в см)
 * @property-read int $PersonHeight_Height Рост
 * @property-read int $PersonWeight_id Вес (в кг)
 * @property-read int $PersonWeight_Weight Масса
 * @property-read string $Treatment Назначенное лечение
 * @property-read string $Kreatinin Креатинин крови
 * @property-read string $Haemoglobin Гемоглобин
 * @property-read string $Protein Белок мочи
 * @property-read string $SpecWeight Удельный вес
 * @property-read string $Cast Цилиндры
 * @property-read string $Leysk Лейкоциты
 * @property-read string $Erythrocyt Эритроциты
 * @property-read string $Salt Соли
 * @property-read int $MedPersonal_hid Заведующий отделением
 *
 * @property-read PersonWeight_model $PersonWeight_model
 * @property-read PersonHeight_model $PersonHeight_model
 * @property-read MorbusNephro_model $MorbusNephro_model
 */
class EvnNotifyNephro_model extends EvnNotifyAbstract_model
{
	private $PersonWeight_delid;
	private $PersonHeight_delid;
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnNotifyNephro_id';
		$arr['pid']['save'] = 'trim';
		$arr['pid']['alias'] = 'EvnNotifyNephro_pid';
		$arr['setdate']['alias'] = 'EvnNotifyNephro_setDate';
		$arr['disdt']['alias'] = 'EvnNotifyNephro_disDT';
		$arr['diddt']['alias'] = 'EvnNotifyNephro_didDT';
		$arr['nidate']['alias'] = 'EvnNotifyNephro_niDate';
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Диагноз',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['diagdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'applyMethod'=>'_applyDiagDate',
			'alias' => 'EvnNotifyNephro_diagDate',
			'label' => 'Дата установки',
			'save' => 'trim|required',
			'type' => 'date'
		);
		$arr['firstdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'applyMethod'=>'_applyFirstDate',
			'alias' => 'EvnNotifyNephro_firstDate',
			'label' => 'Дата возникновения симптомов до установления диагноза',
			'save' => 'trim|required',
			'type' => 'date'
		);
		$arr['nephrodiagconftype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'NephroDiagConfType_id',
			'label' => 'Способ установления диагноза',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['nephrocritype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'NephroCRIType_id',
			'label' => 'Наличие ХПН|required',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['ishyperten'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_IsHyperten',
			'label' => 'Артериальная гипертензия',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['personheight_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PersonHeight_id',
			'label' => 'Рост (в см)',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['personheight_height'] = array(
			'properties' => array(),
			'alias' => 'PersonHeight_Height',
			'label' => 'Рост',
			'save' => 'trim|max_length[3]',
			'type' => 'int',
			'select' => 'ph.PersonHeight_Height',
			'join' => 'left join v_PersonHeight ph on ph.PersonHeight_id = {ViewName}.PersonHeight_id',
		);
		$arr['personweight_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PersonWeight_id',
			'label' => 'Вес (в кг)',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['personweight_weight'] = array(
			'properties' => array(),
			'alias' => 'PersonWeight_Weight',
			'label' => 'Масса',
			'save' => 'trim|max_length[3]',
			'type' => 'int',
			'select' => 'pw.PersonWeight_Weight',
			'join' => 'left join v_PersonWeight pw on pw.PersonWeight_id = {ViewName}.PersonWeight_id',
		);
		$arr['treatment'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_Treatment',
			'label' => 'Назначенное лечение',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['kreatinin'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_Kreatinin',
			'label' => 'Креатинин крови',
			'save' => 'trim|required',
			'type' => 'string'
		);
		$arr['haemoglobin'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_Haemoglobin',
			'label' => 'Гемоглобин',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['protein'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_Protein',
			'label' => 'Белок мочи',
			'save' => 'trim|required',
			'type' => 'string'
		);
		$arr['specweight'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_SpecWeight',
			'label' => 'Удельный вес',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['cast'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_Cast',
			'label' => 'Цилиндры',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['leysk'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_Leysk',
			'label' => 'Лейкоциты',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['erythrocyt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_Erythrocyt',
			'label' => 'Эритроциты',
			'save' => 'trim|required',
			'type' => 'string'
		);
		$arr['salt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_Salt',
			'label' => 'Соли',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['urea'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_Urea',
			'label' => 'Мочевина',
			'save' => 'trim|required',
			'type' => 'string'
		);
		$arr['gfiltration'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_GFiltration',
			'label' => 'Клубочковая фильтрация',
			'save' => 'trim|required',
			'type' => 'string'
		);
		$arr['medpersonal_hid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_hid',
			'label' => 'Заведующий отделением',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['evnnotifynephro_isauto'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyNephro_IsAuto',
			'label' => 'Автоматически созданное извещение',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['fromDispCard'] = array(
			'properties' => array(
				self::PROPERTY_NOT_LOAD
			),
			'alias' => 'fromDispCard',
			'label' => 'Источник сохранения извещения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['Person_id'] = array(
			'properties' => array(
				self::PROPERTY_NOT_LOAD
			),
			'alias' => 'Person_id',
			'label' => 'Идентификатор пациента',
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
		return 172;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnNotifyNephro';
	}

	/**
	 * Определение типа заболевания
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'nephro';
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyFirstDate($data)
	{
		return $this->_applyDate($data, 'firstdate');
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyDiagDate($data)
	{
		return $this->_applyDate($data, 'diagdate');
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		//parent::_validate();
		// тут не проверяю обязательные поля, т.к. сохранение только из формы и обязательные поля указаны в правилах
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		if (in_array($this->getRegionNick(), array('perm', 'ufa')) && !empty($data['autoCreate'])){
			$this->setAttribute('EvnNotifyNephro_IsAuto', 2);
		}

		if (!isset($data['fromDispCard']) && empty($data['autoCreate'])) {
			unset($data['fromDispCard']);
			unset($data['Person_id']);
			parent::_beforeSave($data);
		} else if (isset($data['fromDispCard'])) {
			// Сохранение извещения из дисп карты
			$personId = $data['Person_id'];
			unset($data['fromDispCard']);
			unset($data['Person_id']);
			if (!empty($data)) {
				$this->applyData($data);
			}
			$this->_validate();
			if ($this->evnClassId != 176) {
				$this->load->library('swMorbus');
				$sessionParams = $this->sessionParams;
				$tmp = swMorbus::checkByEvn($this->morbusTypeSysNick, array(
					'Evn_pid' => null,
					'session' => $sessionParams,
					'Diag_id' => $data['Diag_id'],
					'Person_id' => $personId
				), 'onBeforeSaveEvnNotifyFromDispCard');
				$this->setAttribute('morbustype_id', $tmp['MorbusType_id']);
				$this->setAttribute('morbus_id', $tmp['Morbus_id']);
				$this->_params['Morbus_Diag_id'] = $tmp['Diag_id'];
			}
		} else {
			$this->Person_id = $data['Person_id'];
			unset($data['Person_id']);
			unset($data['autoCreate']);
			$this->applyData($data);
			$this->morbusTypeSysNick = $data['MorbusType_SysNick'];
			$this->sessionParams = $data['session'];
			$this->setDate = date('Y-m-d');
			if(!empty($data['fromLab'])){
				unset($data['fromLab']);
				$tmp = swMorbus::checkByEvn($this->morbusTypeSysNick, array(
					'Evn_pid' => null,
					'session' => $this->sessionParams,
					'Diag_id' => $data['Diag_id'],
					'Person_id' => $this->Person_id
				), 'onBeforeSaveEvnNotifyFromJrn');
				$this->setAttribute('morbustype_id', $tmp['MorbusType_id']);
				$this->setAttribute('morbus_id', $tmp['Morbus_id']);
				$this->_params['Morbus_Diag_id'] = $tmp['Diag_id'];
			} else {
				$this->pid = $data['pid'];
				parent::_beforeSave($data);
			}
		}

		$this->load->model('MorbusNephro_model');

		$this->PersonWeight_delid = NULL;
		$this->PersonHeight_delid = NULL;
		if ($this->_isAttributeChanged('PersonWeight_Weight')) {
			if ( empty($this->PersonWeight_Weight) ) {
				if (isset($this->PersonWeight_id)) {
					$this->PersonWeight_delid = $this->PersonWeight_id;
				}
				$this->setAttribute('PersonWeight_id', NULL);
			} else {
				// создаем или обновляем запись о весе
				$result = $this->MorbusNephro_model->savePersonWeight(array(
					'PersonWeight_id' => $this->PersonWeight_id,
					'PersonWeight_setDate' => $this->setDate,
					'PersonWeight_Weight' => $this->PersonWeight_Weight,
					'Server_id' => $this->sessionParams['server_id'],
					'Person_id' => $this->Person_id,
					'Evn_id' => $this->id,
					'pmUser_id' => $this->promedUserId
				));
				$this->setAttribute('PersonWeight_id', $result[0]['PersonWeight_id']);
			}
		}

		if ($this->_isAttributeChanged('PersonHeight_Height')) {
			if ( empty($this->PersonHeight_Height) ) {
				if (isset($this->PersonHeight_id)) {
					$this->PersonHeight_delid = $this->PersonHeight_id;
				}
				$this->setAttribute('PersonHeight_id', NULL);
			} else {
				// создаем или обновляем запись о росте
				$this->load->model('MorbusNephro_model');
				$result = $this->MorbusNephro_model->savePersonHeight(array(
					'PersonHeight_id' => $this->PersonHeight_id,
					'PersonHeight_setDate' => $this->setDate,
					'PersonHeight_Height' => $this->PersonHeight_Height,
					'Server_id' => $this->sessionParams['server_id'],
					'Person_id' => $this->Person_id,
					'Evn_id' => $this->id,
					'pmUser_id' => $this->promedUserId
				));
				$this->setAttribute('PersonHeight_id', $result[0]['PersonHeight_id']);
			}
		}
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		parent::_afterSave($result);
		if (isset($this->PersonWeight_delid)) {
			$this->MorbusNephro_model->deletePersonWeight($this->PersonWeight_delid);
		}
		if (isset($this->PersonHeight_delid)) {
			$this->MorbusNephro_model->deletePersonHeight($this->PersonHeight_delid);
		}

		if ($this->isNewRecord) {
			// обновляем заболевание
			$tmp = $this->MorbusNephro_model->setEvnNotifyNephro($this);
			if ( isset($tmp[0]['Error_Msg']) ) {
				throw new Exception($tmp[0]['Error_Msg']);
			}
		}
	}

	/**
	 * Получение данных для формы
	 */
	function doLoadEditForm($data)
	{
		$response = parent::doLoadEditForm($data);
		$response[0]['Diag_id'] = $this->Diag_id;
		$response[0]['EvnNotifyNephro_diagDate'] = !empty($this->diagDate)?$this->diagDate->format('d.m.Y'):null;
		$response[0]['EvnNotifyNephro_firstDate'] = !empty($this->firstDate)?$this->firstDate->format('d.m.Y'):null;
		$response[0]['NephroDiagConfType_id'] = $this->NephroDiagConfType_id;
		$response[0]['NephroCRIType_id'] = $this->NephroCRIType_id;
		$response[0]['EvnNotifyNephro_IsHyperten'] = $this->IsHyperten;
		$response[0]['PersonHeight_id'] = $this->PersonHeight_id;
		$response[0]['PersonHeight_Height'] = $this->PersonHeight_Height ? (int) $this->PersonHeight_Height : null;
		$response[0]['PersonWeight_id'] = $this->PersonWeight_id;
		$response[0]['PersonWeight_Weight'] = $this->PersonWeight_Weight ? (int) $this->PersonWeight_Weight : null;
		$response[0]['EvnNotifyNephro_Treatment'] = $this->Treatment;
		$response[0]['EvnNotifyNephro_Kreatinin'] = $this->Kreatinin;
		$response[0]['EvnNotifyNephro_Haemoglobin'] = $this->Haemoglobin;
		$response[0]['EvnNotifyNephro_Protein'] = $this->Protein;
		$response[0]['EvnNotifyNephro_SpecWeight'] = $this->SpecWeight;
		$response[0]['EvnNotifyNephro_Cast'] = $this->Cast;
		$response[0]['EvnNotifyNephro_Leysk'] = $this->Leysk;
		$response[0]['EvnNotifyNephro_Erythrocyt'] = $this->Erythrocyt;
		$response[0]['EvnNotifyNephro_Salt'] = $this->Salt;
		$response[0]['EvnNotifyNephro_Urea'] = $this->Urea;
		$response[0]['EvnNotifyNephro_GFiltration'] = $this->GFiltration;
		$response[0]['MedPersonal_hid'] = $this->MedPersonal_hid;
		return $response;
	}

	/**
	 * Получаем строку печатной формы
	 */
	function doPrint($data)
	{
		$parse_data = $this->loadPrintData($data['EvnNotifyNephro_id']);
		if (empty($parse_data)) {
			return 'Не удалось получить данные извещения';
		}
		$this->load->library('parser');
		return $this->parser->parse('print_evnnotifynephro', $parse_data, true);
	}

	/**
	 * Получаем данные для печатной формы
	 */
	protected function loadPrintData($id)
	{
		//Отделение врача, заполнившего Извещение (если несколько, то погружать отделение из посещения / карты ДУ из которого создано Извещение)
		$query = "
			select
				to_char(EN.EvnNotifyNephro_setDT, 'dd.mm.yyyy') as \"EvnNotifyNephro_setDate\",
				to_char(EN.EvnNotifyNephro_diagDate, 'dd.mm.yyyy') as \"EvnNotifyNephro_diagDate\",
				to_char(EN.EvnNotifyNephro_firstDate, 'dd.mm.yyyy') as \"EvnNotifyNephro_firstDate\",
				EN.EvnNotifyNephro_IsHyperten as \"EvnNotifyNephro_IsHyperten\",
				EN.EvnNotifyNephro_Treatment as \"EvnNotifyNephro_Treatment\",
				EN.EvnNotifyNephro_Kreatinin as \"EvnNotifyNephro_Kreatinin\",
				EN.EvnNotifyNephro_Leysk as \"EvnNotifyNephro_Leysk\",
				EN.EvnNotifyNephro_Protein as \"EvnNotifyNephro_Protein\",
				EN.EvnNotifyNephro_Salt as \"EvnNotifyNephro_Salt\",
				EN.EvnNotifyNephro_Urea as \"EvnNotifyNephro_Urea\",
				EN.EvnNotifyNephro_GFiltration as \"EvnNotifyNephro_GFiltration\",
				EN.EvnNotifyNephro_SpecWeight as \"EvnNotifyNephro_SpecWeight\",
				EN.EvnNotifyNephro_Haemoglobin as \"EvnNotifyNephro_Haemoglobin\",
				EN.EvnNotifyNephro_Erythrocyt as \"EvnNotifyNephro_Erythrocyt\",
				EN.EvnNotifyNephro_Cast as \"EvnNotifyNephro_Cast\",
				Lpu.Lpu_Nick as \"Lpu_Name\",
				null as \"LpuSection_Name\",
				MP.Person_Fin as \"MedPersonal_Fin\",
				MPH.Person_Fin as \"MedPersonal_Fih\",
				Diag.Diag_Code as \"Diag_Code\",
				Diag.Diag_Name as \"Diag_Name\",
				NephroDiagConfType.NephroDiagConfType_Code as \"NephroDiagConfType_Code\",
				NephroDiagConfType.NephroDiagConfType_Name as \"NephroDiagConfType_Name\",
				NephroCRIType.NephroCRIType_Code as \"NephroCRIType_Code\",
				NephroCRIType.NephroCRIType_Name as \"NephroCRIType_Name\",
				PersonHeight.PersonHeight_Height as \"PersonHeight_Height\",
				PersonWeight.PersonWeight_Weight as \"PersonWeight_Weight\",
				Sex.Sex_Code as \"Sex_Code\",
				Sex.Sex_Name as \"Sex_Name\",
				a.Address_Address as \"Person_Address\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				PS.Person_Phone as \"Person_Phone\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\"
			from
				v_EvnNotifyNephro EN
				inner join v_PersonState PS on PS.Person_id = EN.Person_id
				left join Sex on Sex.Sex_id = PS.Sex_id
				left join v_Address a on a.Address_id = coalesce(PS.PAddress_id,PS.UAddress_id)
				left join NephroDiagConfType on NephroDiagConfType.NephroDiagConfType_id = EN.NephroDiagConfType_id
				left join NephroCRIType on NephroCRIType.NephroCRIType_id = EN.NephroCRIType_id
				left join Diag on Diag.Diag_id = EN.Diag_id
				left join PersonHeight on PersonHeight.PersonHeight_id = EN.PersonHeight_id
				left join PersonWeight on PersonWeight.PersonWeight_id = EN.PersonWeight_id
				left join v_Lpu Lpu on Lpu.Lpu_id = EN.Lpu_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EN.MedPersonal_id
					and MP.Lpu_id = EN.Lpu_id
				left join v_MedPersonal MPH on MPH.MedPersonal_id = EN.MedPersonal_hid
					and MPH.Lpu_id = EN.Lpu_id
			where
				EN.EvnNotifyNephro_id = ?
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
	 * Проверка наличия извещения, записи регистра
	 */
	function checkNephroRegAndNotify($morbus_id)
	{
		$result = array(array('EvnNotifyNephro_id'=>null,'PersonRegister_id'=>null));
		//проверка наличия извещения, записи регистра
		$query = "
			select EN.EvnNotifyNephro_id as \"EvnNotifyNephro_id\"
			from v_Morbus M
			inner join v_EvnNotifyNephro EN on M.Person_id = EN.Person_id 
				and EN.Person_id is not null 
				and EN.EvnNotifyNephro_niDate is null
				and not exists (select 1 from v_PersonRegister PR2 where PR2.EvnNotifyBase_id = EN.EvnNotifyNephro_id limit 1)
			where M.Morbus_id = ?
			limit 1
		";
		$res = $this->db->query($query, array($morbus_id));
		if ( is_object($res) ) {
			$tmp = $res->result('array');
			if(!empty($tmp[0]['EvnNotifyNephro_id'])){
				$result[0]['EvnNotifyNephro_id'] = $tmp[0]['EvnNotifyNephro_id'];
			}
		}

		$query = "
			select PR.PersonRegister_id as \"PersonRegister_id\"
			from v_Morbus M
			inner join v_PersonRegister PR on M.Person_id = PR.Person_id
				and PR.PersonRegisterOutCause_id is null
			inner join v_MorbusType MT on MT.MorbusType_id = PR.MorbusType_id
			where MT.MorbusType_SysNick = 'nephro' and M.Morbus_id = ?
			limit 1
		";
		$res = $this->db->query($query, array($morbus_id));
		if ( is_object($res) ) {
			$tmp = $res->result('array');
			if(!empty($tmp[0]['PersonRegister_id'])){
				$result[0]['PersonRegister_id'] = $tmp[0]['PersonRegister_id'];
			}
		}
		return $result;
	}

	/**
	 * Проверка наличия извещения, записи регистра
	 */
	function getNephroNotifyDoubles($data)
	{
		//проверка наличия извещения, записи регистра
		$query = "
			select
				EN.EvnClass_id as \"EvnClass_id\",
				EN.EvnClass_Name as \"EvnClass_Name\",
				EN.EvnNotifyNephro_id as \"EvnNotifyNephro_id\",
				EN.EvnNotifyNephro_setDate as \"EvnNotifyNephro_setDate\",
				EN.EvnNotifyNephro_setTime as \"EvnNotifyNephro_setTime\",
				EN.EvnNotifyNephro_didDate as \"EvnNotifyNephro_didDate\",
				EN.EvnNotifyNephro_didTime as \"EvnNotifyNephro_didTime\",
				EN.EvnNotifyNephro_disDate as \"EvnNotifyNephro_disDate\",
				EN.EvnNotifyNephro_disTime as \"EvnNotifyNephro_disTime\",
				EN.EvnNotifyNephro_pid as \"EvnNotifyNephro_pid\",
				EN.EvnNotifyNephro_rid as \"EvnNotifyNephro_rid\",
				EN.Lpu_id as \"Lpu_id\",
				EN.Server_id as \"Server_id\",
				EN.PersonEvn_id as \"PersonEvn_id\",
				EN.EvnNotifyNephro_setDT as \"EvnNotifyNephro_setDT\",
				EN.EvnNotifyNephro_disDT as \"EvnNotifyNephro_disDT\",
				EN.EvnNotifyNephro_didDT as \"EvnNotifyNephro_didDT\",
				EN.EvnNotifyNephro_insDT as \"EvnNotifyNephro_insDT\",
				EN.EvnNotifyNephro_updDT as \"EvnNotifyNephro_updDT\",
				EN.EvnNotifyNephro_Index as \"EvnNotifyNephro_Index\",
				EN.EvnNotifyNephro_Count as \"EvnNotifyNephro_Count\",
				EN.pmUser_insID as \"pmUser_insID\",
				EN.pmUser_updID as \"pmUser_updID\",
				EN.Person_id as \"Person_id\",
				EN.Morbus_id as \"Morbus_id\",
				EN.EvnNotifyNephro_IsSigned as \"EvnNotifyNephro_IsSigned\",
				EN.pmUser_signID as \"pmUser_signID\",
				EN.EvnNotifyNephro_signDT as \"EvnNotifyNephro_signDT\",
				EN.EvnNotifyNephro_IsArchive as \"EvnNotifyNephro_IsArchive\",
				EN.EvnNotifyNephro_Guid as \"EvnNotifyNephro_Guid\",
				EN.EvnNotifyNephro_IndexMinusOne as \"EvnNotifyNephro_IndexMinusOne\",
				EN.EvnStatus_id as \"EvnStatus_id\",
				EN.EvnNotifyNephro_statusDate as \"EvnNotifyNephro_statusDate\",
				EN.EvnNotifyNephro_IsTransit as \"EvnNotifyNephro_IsTransit\",
				EN.MorbusType_id as \"MorbusType_id\",
				EN.MedPersonal_id as \"MedPersonal_id\",
				EN.EvnNotifyNephro_niDate as \"EvnNotifyNephro_niDate\",
				EN.MedPersonal_niid as \"MedPersonal_niid\",
				EN.Lpu_niid as \"Lpu_niid\",
				EN.PersonRegisterFailIncludeCause_id as \"PersonRegisterFailIncludeCause_id\",
				EN.PersonDisp_id as \"PersonDisp_id\",
				EN.NotifyStatus_id as \"NotifyStatus_id\",
				EN.EvnOnkoNotify_Comment as \"EvnOnkoNotify_Comment\",
				EN.EvnNotifyNephro_IsAuto as \"EvnNotifyNephro_IsAuto\",
				EN.MedStaffFact_id as \"MedStaffFact_id\",
				EN.MedStaffFact_zid as \"MedStaffFact_zid\",
				EN.Diag_id as \"Diag_id\",
				EN.EvnNotifyNephro_diagDate as \"EvnNotifyNephro_diagDate\",
				EN.EvnNotifyNephro_firstDate as \"EvnNotifyNephro_firstDate\",
				EN.NephroDiagConfType_id as \"NephroDiagConfType_id\",
				EN.NephroCRIType_id as \"NephroCRIType_id\",
				EN.EvnNotifyNephro_IsHyperten as \"EvnNotifyNephro_IsHyperten\",
				EN.PersonHeight_id as \"PersonHeight_id\",
				EN.PersonWeight_id as \"PersonWeight_id\",
				EN.EvnNotifyNephro_Treatment as \"EvnNotifyNephro_Treatment\",
				EN.EvnNotifyNephro_Kreatinin as \"EvnNotifyNephro_Kreatinin\",
				EN.EvnNotifyNephro_Haemoglobin as \"EvnNotifyNephro_Haemoglobin\",
				EN.EvnNotifyNephro_Protein as \"EvnNotifyNephro_Protein\",
				EN.EvnNotifyNephro_SpecWeight as \"EvnNotifyNephro_SpecWeight\",
				EN.EvnNotifyNephro_Cast as \"EvnNotifyNephro_Cast\",
				EN.EvnNotifyNephro_Leysk as \"EvnNotifyNephro_Leysk\",
				EN.EvnNotifyNephro_Erythrocyt as \"EvnNotifyNephro_Erythrocyt\",
				EN.EvnNotifyNephro_Salt as \"EvnNotifyNephro_Salt\",
				EN.MedPersonal_hid as \"MedPersonal_hid\",
				EN.EvnNotifyNephro_Urea as \"EvnNotifyNephro_Urea\",
				EN.EvnNotifyNephro_GFiltration as \"EvnNotifyNephro_GFiltration\"
			from v_EvnNotifyNephro EN
			left join lateral(select en2.* from v_EvnNotifyNephro en2 where en2.EvnNotifyNephro_id = :EvnNotifyNephro_id limit 1) firstEN on true
			where 
				EN.EvnNotifyNephro_pid = firstEN.EvnNotifyNephro_pid
				and EN.Morbus_id = firstEN.Morbus_id
				and EN.EvnNotifyNephro_setDate = firstEN.EvnNotifyNephro_setDate
				and EN.PersonEvn_id = firstEN.PersonEvn_id
			order by EN.EvnNotifyNephro_id desc
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$tmp = $res->result('array');
			return $tmp;
		}
	}

	/**
	 * Проверка наличия извещения, записи регистра
	 */
	function checkNephroRegAndNotifyByPerson($data)
	{
		//проверка наличия извещения, записи регистра
		$result = array(array('EvnNotifyNephro_id'=>null,'PersonRegister_id'=>null));
		//проверка наличия извещения, записи регистра
		$query = "
			select EN.EvnNotifyNephro_id as \"EvnNotifyNephro_id\"
			from v_Morbus M
			inner join v_EvnNotifyNephro EN on M.Person_id = EN.Person_id 
				and EN.Person_id is not null 
				and EN.EvnNotifyNephro_niDate is null
				and not exists (select 1 from v_PersonRegister PR2 where PR2.EvnNotifyBase_id = EN.EvnNotifyNephro_id limit 1)
			where M.Person_id = :Person_id
			limit 1
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$tmp = $res->result('array');
			if(!empty($tmp[0]['EvnNotifyNephro_id'])){
				$result[0]['EvnNotifyNephro_id'] = $tmp[0]['EvnNotifyNephro_id'];
			}
		}

		$query = "
			select PR.PersonRegister_id as \"PersonRegister_id\"
			from v_Morbus M
			inner join v_PersonRegister PR on M.Person_id = PR.Person_id
				and PR.PersonRegisterOutCause_id is null
			inner join v_MorbusType MT on MT.MorbusType_id = PR.MorbusType_id
			where MT.MorbusType_SysNick = 'nephro' and M.Person_id = :Person_id
			limit 1
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$tmp = $res->result('array');
			if(!empty($tmp[0]['PersonRegister_id'])){
				$result[0]['PersonRegister_id'] = $tmp[0]['PersonRegister_id'];
			}
		}
		return $result;
	}

	/**
	 * Проверка диагноза
	 */
	function checkDiagIsNephro($data)
	{
		if(empty($data['Diag_id'])){
			return array();
		}
		$query = "
			select MorbusDiag_id as \"MorbusDiag_id\"
			from v_MorbusDiag
			where MorbusType_id = 46 -- Тип Нефрология
			and Diag_id = :Diag_id
			limit 1
		";
		$res = $this->queryResult($query, $data);
		return $res;
	}
}