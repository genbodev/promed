<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusTub_model - модель для MorbusTub
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       A.Markoff <markov@swan.perm.ru>
 * @version      2012/11
 */
/**
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 * @property PersonRegister_model $PersonRegister_model
 * @property Morbus_model $Morbus
 * @property PersonWeight_model $PersonWeight_model
 */

class MorbusTub_model extends swPgModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;

	public $isAllowPersonResidenceType = 0;
	public $isAllowMorbusTubMDR = 0;

	private $entityFields = array(
		'MorbusTub' => array(
			'Morbus_id',
			'MorbusTub_begDT',
			'MorbusTub_FirstDT',
			'MorbusTub_DiagDT',
			'MorbusTub_ResultDT',
			'MorbusTub_deadDT',
			'TubBreakChemType_id',
			'MorbusTub_ConvDT',
			'MorbusTub_CountDay',
			'MorbusTub_breakDT',
			'MorbusTub_disDT',
			'PersonDispGroup_id',
			'PersonDecreedGroup_id',
			'PersonLivingFacilies_id',
			'MorbusTub_unsetDT',
			'TubResultDeathType_id',
			'TubSickGroupType_id',
			'TubResultChemClass_id',
			'TubResultChemType_id',
			'TubDiag_id',
			'TubPhase_id',			
			'TubDisability_id',
			'MorbusTub_SanatorDT',
			'PersonResidenceType_id',
			'MorbusTub_RegNumCard'
		),
		'Morbus' => array( //allow Deleted
			'MorbusBase_id'
		,'Evn_pid' //Учетный документ, в рамках которого добавлено заболевание
		,'Diag_id'
		,'MorbusKind_id'
		,'Morbus_Name'
		,'Morbus_Nick'
		,'Morbus_disDT'
		,'Morbus_setDT'
		,'MorbusResult_id'
		),
		'MorbusBase' => array(
			'Person_id'
		,'Evn_pid'
		,'MorbusType_id'
		,'MorbusBase_setDT'
		,'MorbusBase_disDT'
		,'MorbusResult_id'
		)
	);

	protected $_MorbusType_id = null;//7

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->isAllowPersonResidenceType = ('perm' == $this->regionNick) ? 1 : 0;
		$this->isAllowMorbusTubMDR = (in_array($this->regionNick, array(
			'astra', 'ufa', 'buryatiya', 'kareliya', 'pskov', 'ekb', 'khak'
		))) ? 1 : 0;
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'tub';
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
		return 'Tub';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusTub';
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
	 * Проверка обязательных параметров специфики
	 *
	 * @params Mode
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 */
	private function checkParams($data)
	{
		if( empty($data['Mode']) )
		{
			throw new Exception('Не указан режим сохранения');
		}
		$check_fields_list = array();
		$fields = array(
			'Diag_id' => 'Идентификатор диагноза'
		,'Person_id' => 'Идентификатор человека'
		,'Evn_pid' => 'Идентификатор движения/посещения'
		,'pmUser_id' => 'Идентификатор пользователя'
		,'Morbus_id' => 'Идентификатор заболевания'
		,'MorbusTub_id' => 'Идентификатор специфики заболевания'
			//,'Lpu_id' => 'ЛПУ, в которой впервые установлен диагноз орфанного заболевания'
		);
		switch ($data['Mode']) {
			case 'personregister_viewform':
				$check_fields_list = array('MorbusTub_id','Morbus_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'evnsection_viewform':
			case 'evnvizitpl_viewform':
				$check_fields_list = array('MorbusTub_id','Morbus_id','Evn_pid','pmUser_id'); //'Diag_id','Person_id',
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
		if( count($errors) > 0 )
		{
			throw new Exception(implode('<br />',$errors));
		}
		return $data;
	}
	/**
	 * Сохранение специфики заболевания
	 * Обязательные параметры:
	 * 1) Evn_pid или Person_id
	 * 2) pmUser_id
	 * 3) Mode
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 * @author Alexander Permyakov aka Alexpm
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	function saveMorbusSpecific($data) {
		try {
			$data = $this->checkParams($data);
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
			$tmp = swMorbus::getStaticMorbusCommon()->loadLastEvnData($this->getMorbusTypeSysNick(), $data['Evn_pid'], $data['Person_id'], $data['Diag_id']);
			if (empty($tmp)) {
				if ( in_array($data['Mode'],array('evnsection_viewform','evnvizitpl_viewform')) ) {
					throw new Exception('Ошибка определения актуального учетного документа с данным заболеванием');
				}
				$data['Evn_aid'] = null;
			} else {
				//учетный документ найден
				$data['Evn_aid'] = $tmp[0]['Evn_id'];
				$data['Diag_id'] = empty($data['Diag_id']) ? $tmp[0]['Diag_id'] : $data['Diag_id'];
				$data['Person_id'] = $tmp[0]['Person_id'];
			}
			if ($data['Mode'] == 'personregister_viewform' || $data['Evn_pid'] == $data['Evn_aid']) {
				if ($data['Mode'] == 'personregister_viewform' && !empty($data['Diag_id'])) {
					// Diag_id содержит значение диагноза записи регистра
					// В режиме personregister_viewform диагноз записи регистра сохраняется в PersonRegister
					$this->load->model('PersonRegister_model');
					if ( empty($data['PersonRegister_id']) ) {
						throw new Exception('Не указан идентификатор записи регистра');
					}
					$this->PersonRegister_model->setPersonRegister_id($data['PersonRegister_id']);
					$result = $this->PersonRegister_model->load();
					if ( empty($result) ) {
						throw new Exception('Ошибка загрузки записи регистра');
					}
					if ( $this->PersonRegister_model->getPersonRegister_disDate() ) {
						throw new Exception('Пациент исключен из регистра');
					}
					$this->PersonRegister_model->setPersonRegister_setDate( $this->PersonRegister_model->getPersonRegister_setDate());
					$this->PersonRegister_model->setDiag_id($data['Diag_id']);
					$this->PersonRegister_model->setSessionParams($data['session']);
					$result = $this->PersonRegister_model->save();
					if ( isset($result[0]['Error_Msg']) ) {
						throw new Exception($result[0]['Error_Msg']);
					}
				}
				// Если редактирование происходит из актуального учетного документа или из панели просмотра в форме записи регистра, то сохраняем данные
				return $this->updateMorbusSpecific($data);
			} else {
				//Ничего не сохраняем
				throw new Exception('Данные не были сохранены, т.к. данный учетный документ не является актуальным для данного заболевания.');
			}
		} catch (Exception $e) {
			return array(array('Error_Msg' => 'Сохранение специфики заболевания. <br />'. $e->getMessage()));
		}
	}

	/**
	 * Создание специфики заболевания
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
			|| false == in_array($data['mode'], array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify', 'onBeforeSaveEvnNotifyFromJrn'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = array();
		$queryParams['pmUser_id'] = $this->promedUserId;
		$queryParams['Morbus_id'] = $data['Morbus_id'];
		// @todo убрать
		$queryParams['MorbusTub_RegNumCard'] = 1;

		$res = $this->queryResult("
			select
				{$tableName}_id as \"{$tableName}_id\"
			from v_{$tableName}
			where Morbus_id = :Morbus_id
			limit 1
		", $queryParams);
		if (!isset($res[0]) || !isset($res[0]["{$tableName}_id"])) {
			$queryParams["{$tableName}_id"] = null;
			$query = "
			select
				{$tableName}_id as \"{$tableName}_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_{$tableName}_ins(
				{$tableName}_id := :{$tableName}_id,
				Morbus_id := :Morbus_id,
				MorbusTub_RegNumCard := cast(:MorbusTub_RegNumCard as varchar),
				pmUser_id := :pmUser_id
			)
		";
		} else {
			$queryParams["{$tableName}_id"] = $res[0]["{$tableName}_id"];
			$query = "
				select
					:{$tableName}_id as \"{$tableName}\"
			";
		}
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
		return $this->_saveResponse;
	}

	/**
	 * Проверка на наличие в системе записи регистра с пустым атрибутом «Дата исключения из регистра»
	 * на данного человека с указанной «Датой смерти»
	 */
	function checkPersonDead($data)
	{
		$query = "select
			 ps.Server_id as \"Server_id\"
			,ps.Person_id as \"Person_id\"
			,ps.PersonEvn_id as \"PersonEvn_id\"
			,ps.Person_SurName as \"Person_SurName\"
			,ps.Person_FirName as \"Person_FirName\"
			,ps.Person_SecName as \"Person_SecName\"
			,date_part('SECOND', ps.Person_BirthDay - cast('01.01.1970' as date)) as \"Person_BirthDay\"
			from v_PersonState ps
			inner join v_PersonRegister pr on pr.Person_id = ps.Person_id
			inner join v_MorbusType mt on pr.MorbusType_id = mt.MorbusType_id
			where 
				ps.Person_deadDT is not null 
				and pr.PersonRegister_disDate is null
				and mt.MorbusType_SysNick = 'Tub'
				and ps.Person_id = ?
			limit 1";
		$result = $this->db->query($query, array($data['Person_id']));
		$response = $result->result('array');
		if ( is_array($response) && count($response) > 0 ) {
			return $response;
		} else	{
			return false;
		}
	}

	/**
	 *  Получение списка пользователей с группой «Регистр по орфанным заболеваниям»
	 */
	function getUsersTub($data)
	{
		$query = "
		select 
			PMUser_id as \"PMUser_id\"
		from 
			v_pmUserCache
		where 
			pmUser_groups like '%\"Tub\"%'
			and Lpu_id = ?";
		$result = $this->db->query($query, array($data['Lpu_id']));
		$response = $result->result('array');
		if ( is_array($response) && count($response) > 0 ) {
			return $response;
		} else	{
			return false;
		}
	}

	/**
	 * Сопутствующие заболевания
	 */
	function getMorbusTubDiagSopViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusTubDiagSop_id']) and ($data['MorbusTubDiagSop_id'] > 0)) {
			$filter = "MM.MorbusTubDiagSop_id = :MorbusTubDiagSop_id";
		} else {
			if(empty($data['MorbusTub_id']) or $data['MorbusTub_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTub_id = :MorbusTub_id";
		}

		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusTubDiagSop_id as \"MorbusTubDiagSop_id\",
				MT.MorbusTub_id as \"MorbusTub_id\",
				MM.MorbusTubDiagSop_OtherDiag as \"MorbusTubDiagSop_OtherDiag\",
				MM.TubDiagSop_id as \"TubDiagSop_id\",
				TubDiagSop_Name as \"TubDiagSop_Name\",
				to_char(MM.MorbusTubDiagSop_setDT, 'dd.mm.yyyy') as \"MorbusTubDiagSop_setDT\",
				:Evn_id as \"MorbusTub_pid\"
			from
				v_MorbusTubDiagSop MM
				inner join v_MorbusTub MT on MT.MorbusTub_id = MM.MorbusTub_id
				inner join v_Morbus M on MT.Morbus_id = M.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_TubDiagSop TubDiagSop on TubDiagSop.TubDiagSop_id = MM.TubDiagSop_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";

		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Генерализованные формы
	 */
	function getMorbusTubDiagGeneralFormViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['TubDiagGeneralForm_id']) and ($data['TubDiagGeneralForm_id'] > 0)) {
			$filter = "MM.TubDiagGeneralForm_id = :TubDiagGeneralForm_id";
		} else {
			if(empty($data['MorbusTub_id']) or $data['MorbusTub_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTub_id = :MorbusTub_id";
		}

		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.TubDiagGeneralForm_id as \"TubDiagGeneralForm_id\",
				MT.MorbusTub_id as \"MorbusTub_id\",
				MM.Diag_id as \"Diag_id\",
				Diag.Diag_Name as \"Diag_Name\",
				to_char(MM.TubDiagGeneralForm_setDT, 'dd.mm.yyyy') as \"TubDiagGeneralForm_setDT\",
				:Evn_id as \"MorbusTub_pid\"
			from
				v_TubDiagGeneralForm MM
				inner join v_MorbusTub MT on MT.MorbusTub_id = MM.MorbusTub_id
				left join v_Morbus M on MT.Morbus_id = M.Morbus_id
				left join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_Diag Diag on Diag.Diag_id = MM.Diag_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";

		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Сопутствующие заболевания"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubDiagSop($data) {
		$procedure = '';

		if ( (!isset($data['MorbusTubDiagSop_id'])) || ($data['MorbusTubDiagSop_id'] <= 0) ) {
			$procedure = 'p_MorbusTubDiagSop_ins';
		}
		else {
			$procedure = 'p_MorbusTubDiagSop_upd';
		}

		$query = "
			select
				MorbusTubDiagSop_id as \"MorbusTubDiagSop_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubDiagSop_id := :MorbusTubDiagSop_id,
				MorbusTub_id := :MorbusTub_id,
				TubDiagSop_id := :TubDiagSop_id,
				MorbusTubDiagSop_OtherDiag := :MorbusTubDiagSop_OtherDiag,
				MorbusTubDiagSop_setDT := :MorbusTubDiagSop_setDT,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Генерализованные формы"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveTubDiagGeneralForm($data) {
		$procedure = '';

		if ( (!isset($data['TubDiagGeneralForm_id'])) || ($data['TubDiagGeneralForm_id'] <= 0) ) {
			$procedure = 'p_TubDiagGeneralForm_ins';
		}
		else {
			$procedure = 'p_TubDiagGeneralForm_upd';
		}

		$query = "
			select
				TubDiagGeneralForm_id as \"TubDiagGeneralForm_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				TubDiagGeneralForm_id := :TubDiagGeneralForm_id,
				MorbusTub_id := :MorbusTub_id,
				Diag_id := :Diag_id,
				TubDiagGeneralForm_setDT := :TubDiagGeneralForm_setDT,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Режимы химиотерапии
	 */
	function getMorbusTubConditChemViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusTubConditChem_id']) and ($data['MorbusTubConditChem_id'] > 0)) {
			$filter = "MM.MorbusTubConditChem_id = :MorbusTubConditChem_id";
		} else {
			if(empty($data['MorbusTub_id']) or $data['MorbusTub_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTub_id = :MorbusTub_id";
		}

		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusTubConditChem_id as \"MorbusTubConditChem_id\",
				MT.MorbusTub_id as \"MorbusTub_id\",
				MM.TubStandartConditChemType_id as \"TubStandartConditChemType_id\",
				TubStandartConditChemType_Name as \"TubStandartConditChemType_Name\",
				MM.TubTreatmentChemType_id as \"TubTreatmentChemType_id\",
				TubTreatmentChemType_Name as \"TubTreatmentChemType_Name\",
				MM.TubStageChemType_id as \"TubStageChemType_id\",
				TubStageChemType_Name as \"TubStageChemType_Name\",
				MM.TubVenueType_id as \"TubVenueType_id\",
				TubVenueType_Name as \"TubVenueType_Name\",
				to_char(MM.MorbusTubConditChem_BegDate, 'dd.mm.yyyy') as \"MorbusTubConditChem_BegDate\",
				to_char(MM.MorbusTubConditChem_EndDate, 'dd.mm.yyyy') as \"MorbusTubConditChem_EndDate\",
				:Evn_id as \"MorbusTub_pid\"
			from
				v_MorbusTubConditChem MM
				inner join v_MorbusTub MT on MT.MorbusTub_id = MM.MorbusTub_id
				inner join v_Morbus M on MT.Morbus_id = M.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_TubTreatmentChemType TubTreatmentChemType on TubTreatmentChemType.TubTreatmentChemType_id = MM.TubTreatmentChemType_id
				left join v_TubStandartConditChemType TubStandartConditChemType on TubStandartConditChemType.TubStandartConditChemType_id = MM.TubStandartConditChemType_id
				left join v_TubStageChemType TubStageChemType on TubStageChemType.TubStageChemType_id = MM.TubStageChemType_id
				left join v_TubVenueType TubVenueType on TubVenueType.TubVenueType_id = MM.TubVenueType_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";

		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Режимы химиотерапии"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubConditChem($data) {
		$procedure = '';

		if ( (!isset($data['MorbusTubConditChem_id'])) || ($data['MorbusTubConditChem_id'] <= 0) ) {
			$procedure = 'p_MorbusTubConditChem_ins';
		}
		else {
			$procedure = 'p_MorbusTubConditChem_upd';
		}

		$query = "
			select
				MorbusTubConditChem_id as \"MorbusTubConditChem_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubConditChem_id := :MorbusTubConditChem_id,
				MorbusTub_id := :MorbusTub_id,
				TubStageChemType_id := :TubStageChemType_id,
				TubTreatmentChemType_id := :TubTreatmentChemType_id,
				TubStandartConditChemType_id := :TubStandartConditChemType_id,
				TubVenueType_id := :TubVenueType_id,
				MorbusTubConditChem_BegDate := :MorbusTubConditChem_BegDate,
				MorbusTubConditChem_EndDate := :MorbusTubConditChem_EndDate,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Получает список данных "Лекарственное назначение"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubPrescrViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusTubPrescr_id']) and ($data['MorbusTubPrescr_id'] > 0)) {
			$filter = "MM.MorbusTubPrescr_id = :MorbusTubPrescr_id";
		} else {
			if(empty($data['MorbusTub_id']) or $data['MorbusTub_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTub_id = :MorbusTub_id and MM.MorbusTubMDR_id is null";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusTub_id as \"MorbusTub_id\",
				MM.MorbusTubPrescr_id as \"MorbusTubPrescr_id\",
				to_char(MM.MorbusTubPrescr_setDT, 'dd.mm.yyyy') as \"MorbusTubPrescr_setDT\",
				to_char(MM.MorbusTubPrescr_endDate, 'dd.mm.yyyy') as \"MorbusTubPrescr_endDate\",
				MM.TubDrug_id as \"TubDrug_id\",
				MM.Drug_id as \"Drug_id\",
				coalesce(TubDrug_Name || ', ' || Drug.Drug_Name, TubDrug_Name, Drug.Drug_Name) as \"TubDrug_Name\",
				MM.TubStageChemType_id as \"TubStageChemType_id\",
				TubStageChemType_Name as \"TubStageChemType_Name\",
				MM.MorbusTubPrescr_DoseDay as \"MorbusTubPrescr_DoseDay\",
				MM.MorbusTubPrescr_DoseTotal as \"MorbusTubPrescr_DoseTotal\",
				MM.MorbusTubPrescr_Schema as \"MorbusTubPrescr_Schema\",
				:Evn_id as \"MorbusTub_pid\"
			from
				v_MorbusTubPrescr MM
				inner join v_MorbusTub MT on MT.MorbusTub_id = MM.MorbusTub_id
				inner join v_Morbus M on MT.Morbus_id = M.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_TubStageChemType TubStageChemType on TubStageChemType.TubStageChemType_id = MM.TubStageChemType_id
				left join v_TubDrug TubDrug on TubDrug.TubDrug_id = MM.TubDrug_id
				left join rls.v_Drug Drug on Drug.Drug_id = MM.Drug_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
			order by cast(MorbusTubPrescr_setDT as date)
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Лекарственное назначение"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubPrescr($data) {
		if ( (!isset($data['MorbusTubPrescr_id'])) || ($data['MorbusTubPrescr_id'] <= 0) ) {
			$procedure = 'p_MorbusTubPrescr_ins';
		}
		else {
			$procedure = 'p_MorbusTubPrescr_upd';
		}

		$query = "
			select
				MorbusTubPrescr_id as \"MorbusTubPrescr_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubPrescr_id := :MorbusTubPrescr_id,
				MorbusTub_id := :MorbusTub_id,
				MorbusTubPrescr_setDT := :MorbusTubPrescr_setDT,
				MorbusTubPrescr_endDate := :MorbusTubPrescr_endDate,
				TubDrug_id := :TubDrug_id,
				Drug_id := :Drug_id,
				MorbusTubPrescr_DoseDay := :MorbusTubPrescr_DoseDay,
				MorbusTubPrescr_Schema := :MorbusTubPrescr_Schema,
				MorbusTubPrescr_DoseTotal := :MorbusTubPrescr_DoseTotal,
				TubStageChemType_id := :TubStageChemType_id,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Получает список данных "Направление на проведение микроскопических исследований на туберкулез"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getEvnDirectionTubViewData($data) {
		$filter = "(1=1)";
		if (isset($data['EvnDirectionTub_id']) and ($data['EvnDirectionTub_id'] > 0)) {
			$filter = "MM.EvnDirectionTub_id = :EvnDirectionTub_id";
		} else {
			if(empty($data['MorbusTub_id']) or $data['MorbusTub_id'] < 0) {
				return array();
			}
			$filter = "MT.MorbusTub_id = :MorbusTub_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.EvnDirectionTub_id as \"EvnDirectionTub_id\",
				to_char(MM.EvnDirectionTub_setDT, 'dd.mm.yyyy') as \"EvnDirectionTub_setDT\",
				MT.MorbusTub_id as \"MorbusTub_id\",
				MM.EvnDirectionTub_PersonRegNum as \"EvnDirectionTub_PersonRegNum\",
				MM.TubDiagnosticMaterialType_id as \"TubDiagnosticMaterialType_id\",
				MM.EvnDirectionTub_OtherMeterial as \"EvnDirectionTub_OtherMeterial\",
				MM.TubTargetStudyType_id as \"TubTargetStudyType_id\",
				MM.EvnDirectionTub_NumLab as \"EvnDirectionTub_NumLab\",
				MM.MedPersonal_lid as \"MedPersonal_lid\",
				MM.MedPersonal_id as \"MedPersonal_id\",
				to_char(MM.EvnDirectionTub_ResDT, 'dd.mm.yyyy') as \"EvnDirectionTub_ResDT\",
				TubDiagnosticMaterialType.TubDiagnosticMaterialType_Name as \"TubDiagnosticMaterialType_Name\",
				:Evn_id as \"MorbusTub_pid\"
			from
				v_EvnDirectionTub MM
				inner join v_MorbusTub MT on MT.MorbusTub_id = MM.MorbusTub_id
				inner join v_Morbus M on MT.Morbus_id = M.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				--left join v_MedPersonal MedPersonal on MedPersonal.MedPersonal_id = MM.MedPersonal_lid
				left join v_TubDiagnosticMaterialType TubDiagnosticMaterialType on MM.TubDiagnosticMaterialType_id = TubDiagnosticMaterialType.TubDiagnosticMaterialType_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
			order by cast(EvnDirectionTub_setDT as date)
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Направление на проведение микроскопических исследований на туберкулез"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveEvnDirectionTub($data) {
		$procedure = '';

		if ( (!isset($data['EvnDirectionTub_id'])) || ($data['EvnDirectionTub_id'] <= 0) ) {
			$procedure = 'p_EvnDirectionTub_ins';
		}
		else {
			$procedure = 'p_EvnDirectionTub_upd';
		}
		$query = "
			select
				EvnDirectionTub_id as \"EvnDirectionTub_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				EvnDirectionTub_id := :EvnDirectionTub_id,
				MorbusTub_id := :MorbusTub_id,
				EvnDirectionTub_setDT := :EvnDirectionTub_setDT,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnDirectionTub_Num := 0,
				EvnDirectionTub_PersonRegNum := :EvnDirectionTub_PersonRegNum,
				TubDiagnosticMaterialType_id := :TubDiagnosticMaterialType_id,
				EvnDirectionTub_OtherMeterial := :EvnDirectionTub_OtherMeterial,
				TubTargetStudyType_id := :TubTargetStudyType_id,
				EvnDirectionTub_NumLab := :EvnDirectionTub_NumLab,
				EvnDirectionTub_ResDT := :EvnDirectionTub_ResDT,
				MedPersonal_lid := :MedPersonal_lid,
				MedPersonal_id := :MedPersonal_id,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получает список данных "Результаты микроскопических исследований"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getTubMicrosResultViewData($data) {
		$filter = "(1=1)";
		if (isset($data['TubMicrosResult_id']) and ($data['TubMicrosResult_id'] > 0)) {
			$filter = "MM.TubMicrosResult_id = :TubMicrosResult_id";
		} else {
			if(empty($data['EvnDirectionTub_id']) or $data['EvnDirectionTub_id'] < 0) {
				return array();
			}
			$filter = "MM.EvnDirectionTub_id = :EvnDirectionTub_id";
		}
		$query = "
			select
				--case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as accessType,
				MM.TubMicrosResult_id as \"TubMicrosResult_id\",
				MM.EvnDirectionTub_id as \"EvnDirectionTub_id\",
				TubMicrosResult_Num as \"TubMicrosResult_Num\",
				to_char(MM.TubMicrosResult_setDT, 'dd.mm.yyyy') as \"TubMicrosResult_setDT\",
				to_char(MM.TubMicrosResult_MicrosDT, 'dd.mm.yyyy') as \"TubMicrosResult_MicrosDT\",
				MM.TubMicrosResultType_id as \"TubMicrosResultType_id\",
				TubMicrosResultType.TubMicrosResultType_Name as \"TubMicrosResultType_Name\",
				MM.TubMicrosResult_EdResult as \"TubMicrosResult_EdResult\",
				MM.TubMicrosResult_Comment as \"TubMicrosResult_Comment\",
				:Evn_id as \"MorbusTub_pid\"
			from
				v_TubMicrosResult MM
				left join v_TubMicrosResultType TubMicrosResultType on TubMicrosResultType.TubMicrosResultType_id = MM.TubMicrosResultType_id
			where
				{$filter}

		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Результаты микроскопических исследований"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveTubMicrosResult($data) {
		$procedure = '';

		if ( (!isset($data['TubMicrosResult_id'])) || ($data['TubMicrosResult_id'] <= 0) ) {
			$procedure = 'p_TubMicrosResult_ins';
		}
		else {
			$procedure = 'p_TubMicrosResult_upd';
		}

		$query = "
			select
				TubMicrosResult_id as \"TubMicrosResult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				TubMicrosResult_id := :TubMicrosResult_id,
				EvnDirectionTub_id := :EvnDirectionTub_id,
				TubMicrosResult_Num := :TubMicrosResult_Num,
				TubMicrosResult_setDT := :TubMicrosResult_setDT,
				TubMicrosResult_MicrosDT := :TubMicrosResult_MicrosDT,
				TubMicrosResultType_id := :TubMicrosResultType_id,
				TubMicrosResult_EdResult := :TubMicrosResult_EdResult,
				TubMicrosResult_Comment := :TubMicrosResult_Comment,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Получает список данных "График исполнения назначения процедур"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubPrescrTimetableViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusTubPrescrTimetable_id']) and ($data['MorbusTubPrescrTimetable_id'] > 0)) {
			$filter = "MM.MorbusTubPrescrTimetable_id = :MorbusTubPrescrTimetable_id";
		} else {
			if(empty($data['MorbusTubPrescr_id']) or $data['MorbusTubPrescr_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTubPrescr_id = :MorbusTubPrescr_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusTubPrescrTimetable_id as \"MorbusTubPrescrTimetable_id\",
				MTP.MorbusTubPrescr_id as \"MorbusTubPrescr_id\",
				to_char(MM.MorbusTubPrescrTimetable_setDT, 'dd.mm.yyyy') as \"MorbusTubPrescrTimetable_setDT\",
				MM.MorbusTubPrescrTimetable_IsExec as \"MorbusTubPrescrTimetable_IsExec\",
				IsExec.YesNo_Name as \"MorbusTubPrescrTimetable_IsExec_Name\",
				MM.MedPersonal_id as \"MedPersonal_id\",
				MedPersonal.Person_Fio as \"MedPersonal_Fio\",
				:Evn_id as \"MorbusTub_pid\"
			from
				v_MorbusTubPrescrTimetable MM
				inner join v_MorbusTubPrescr MTP on MTP.MorbusTubPrescr_id = MM.MorbusTubPrescr_id
				inner join v_MorbusTub MT on MT.MorbusTub_id = MTP.MorbusTub_id
				inner join v_Morbus M on M.Morbus_id = MT.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_MedPersonal MedPersonal on MedPersonal.MedPersonal_id = MM.MedPersonal_id
				left join v_YesNo IsExec on MM.MorbusTubPrescrTimetable_IsExec = IsExec.YesNo_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
			order by cast(MorbusTubPrescrTimetable_setDT as date)
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "График исполнения назначения процедур"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubPrescrTimetable($data) {
		$procedure = '';

		if ( (!isset($data['MorbusTubPrescrTimetable_id'])) || ($data['MorbusTubPrescrTimetable_id'] <= 0) ) {
			$procedure = 'p_MorbusTubPrescrTimetable_ins';
		}
		else {
			$procedure = 'p_MorbusTubPrescrTimetable_upd';
		}

		$query = "
			select
				MorbusTubPrescrTimetable_id as \"MorbusTubPrescrTimetable_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubPrescrTimetable_id := :MorbusTubPrescrTimetable_id,
				MorbusTubPrescr_id := :MorbusTubPrescr_id,
				MorbusTubPrescrTimetable_setDT := :MorbusTubPrescrTimetable_setDT,
				MorbusTubPrescrTimetable_IsExec := :MorbusTubPrescrTimetable_IsExec,
				MedPersonal_id := :MedPersonal_id,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получает список данных "Результаты исследований микроскопии"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubStudyMicrosResultViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusTubStudyMicrosResult_id']) and ($data['MorbusTubStudyMicrosResult_id'] > 0)) {
			$filter = "MM.MorbusTubStudyMicrosResult_id = :MorbusTubStudyMicrosResult_id";
		} else {
			if(empty($data['MorbusTubStudyResult_id']) or $data['MorbusTubStudyResult_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id";
		}
		$query = "
			select
				MM.MorbusTubStudyMicrosResult_id as \"MorbusTubStudyMicrosResult_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				to_char(MM.MorbusTubStudyMicrosResult_setDT, 'dd.mm.yyyy') as \"MorbusTubStudyMicrosResult_setDT\",
				MM.MorbusTubStudyMicrosResult_NumLab as \"MorbusTubStudyMicrosResult_NumLab\",
				MM.MorbusTubStudyMicrosResult_EdResult as \"MorbusTubStudyMicrosResult_EdResult\",
				MM.TubMicrosResultType_id as \"TubMicrosResultType_id\",
				TubMicrosResultType.TubMicrosResultType_Name as \"TubMicrosResultType_Name\"
			from
				v_MorbusTubStudyMicrosResult MM
				left join v_TubMicrosResultType TubMicrosResultType on TubMicrosResultType.TubMicrosResultType_id = MM.TubMicrosResultType_id
			where
				{$filter}
		";


		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Результаты исследований микроскопии"
	 * @param array $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubStudyMicrosResult($data) {

		$procedure = '';
		if ( (!isset($data['MorbusTubStudyMicrosResult_id'])) || ($data['MorbusTubStudyMicrosResult_id'] <= 0) ) {
			$procedure = 'p_MorbusTubStudyMicrosResult_ins';
		}
		else {
			$procedure = 'p_MorbusTubStudyMicrosResult_upd';
		}

		$query = "
			select
				MorbusTubStudyMicrosResult_id as \"MorbusTubStudyMicrosResult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubStudyMicrosResult_id := :MorbusTubStudyMicrosResult_id,
				MorbusTubStudyResult_id := :MorbusTubStudyResult_id,
				MorbusTubStudyMicrosResult_NumLab := :MorbusTubStudyMicrosResult_NumLab,
				TubMicrosResultType_id := :TubMicrosResultType_id,
				MorbusTubStudyMicrosResult_EdResult := :MorbusTubStudyMicrosResult_EdResult,
				MorbusTubStudyMicrosResult_setDT := :MorbusTubStudyMicrosResult_setDT,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получает список данных "Результаты исследований посева"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubStudySeedResultViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusTubStudySeedResult_id']) and ($data['MorbusTubStudySeedResult_id'] > 0)) {
			$filter = "MM.MorbusTubStudySeedResult_id = :MorbusTubStudySeedResult_id";
		} else {
			if(empty($data['MorbusTubStudyResult_id']) or $data['MorbusTubStudyResult_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id";
		}
		$query = "
			select
				MM.MorbusTubStudySeedResult_id as \"MorbusTubStudySeedResult_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				to_char(MM.MorbusTubStudySeedResult_setDT, 'dd.mm.yyyy') as \"MorbusTubStudySeedResult_setDT\",
				MM.TubSeedResultType_id as \"TubSeedResultType_id\",
				TubSeedResultType.TubSeedResultType_Name as \"TubSeedResultType_Name\"
			from
				v_MorbusTubStudySeedResult MM
				left join v_TubSeedResultType TubSeedResultType on TubSeedResultType.TubSeedResultType_id = MM.TubSeedResultType_id
			where
				{$filter}
		";


		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Результаты исследований посева"
	 * @param array $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubStudySeedResult($data) {

		$procedure = '';
		if ( (!isset($data['MorbusTubStudySeedResult_id'])) || ($data['MorbusTubStudySeedResult_id'] <= 0) ) {
			$procedure = 'p_MorbusTubStudySeedResult_ins';
		}
		else {
			$procedure = 'p_MorbusTubStudySeedResult_upd';
		}

		$query = "
			select
				MorbusTubStudySeedResult_id as \"MorbusTubStudySeedResult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubStudySeedResult_id := :MorbusTubStudySeedResult_id,
				MorbusTubStudyResult_id := :MorbusTubStudyResult_id,
				TubSeedResultType_id := :TubSeedResultType_id,
				MorbusTubStudySeedResult_setDT := :MorbusTubStudySeedResult_setDT,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получает список данных "Результаты исследований гистология"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubStudyHistolResultViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusTubStudyHistolResult_id']) and ($data['MorbusTubStudyHistolResult_id'] > 0)) {
			$filter = "MM.MorbusTubStudyHistolResult_id = :MorbusTubStudyHistolResult_id";
		} else {
			if(empty($data['MorbusTubStudyResult_id']) or $data['MorbusTubStudyResult_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id";
		}
		$query = "
			select
				MM.MorbusTubStudyHistolResult_id as \"MorbusTubStudyHistolResult_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				to_char(MM.MorbusTubStudyHistolResult_setDT, 'dd.mm.yyyy') as \"MorbusTubStudyHistolResult_setDT\",
				MM.TubDiagnosticMaterialType_id as \"TubDiagnosticMaterialType_id\",
				TubDiagnosticMaterialType.TubDiagnosticMaterialType_Name as \"TubDiagnosticMaterialType_Name\",
				MM.TubHistolResultType_id as \"TubHistolResultType_id\",
				TubHistolResultType.TubHistolResultType_Name as \"TubHistolResultType_Name\"
			from
				v_MorbusTubStudyHistolResult MM
				left join v_TubDiagnosticMaterialType TubDiagnosticMaterialType on TubDiagnosticMaterialType.TubDiagnosticMaterialType_id = MM.TubDiagnosticMaterialType_id
				left join v_TubHistolResultType TubHistolResultType on TubHistolResultType.TubHistolResultType_id = MM.TubHistolResultType_id
			where
				{$filter}
		";


		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Результаты исследований гистология"
	 * @param array $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubStudyHistolResult($data) {
		if ( (!isset($data['MorbusTubStudyHistolResult_id'])) || ($data['MorbusTubStudyHistolResult_id'] <= 0) ) {
			$procedure = 'p_MorbusTubStudyHistolResult_ins';
		}
		else {
			$procedure = 'p_MorbusTubStudyHistolResult_upd';
		}

		$query = "
			select
				MorbusTubStudyHistolResult_id as \"MorbusTubStudyHistolResult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubStudyHistolResult_id := :MorbusTubStudyHistolResult_id,
				MorbusTubStudyResult_id := :MorbusTubStudyResult_id,
				TubDiagnosticMaterialType_id := :TubDiagnosticMaterialType_id,
				TubHistolResultType_id := :TubHistolResultType_id,
				MorbusTubStudyHistolResult_setDT := :MorbusTubStudyHistolResult_setDT,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получает список данных "Результаты исследований ренгена"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubStudyXrayResultViewData($data)
	{
		if (isset($data['MorbusTubStudyXrayResult_id']) and ($data['MorbusTubStudyXrayResult_id'] > 0)) {
			$filter = "MM.MorbusTubStudyXrayResult_id = :MorbusTubStudyXrayResult_id";
		} else {
			if(empty($data['MorbusTubStudyResult_id']) or $data['MorbusTubStudyResult_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id";
		}
		$query = "
			select
				MM.MorbusTubStudyXrayResult_id as \"MorbusTubStudyXrayResult_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				to_char(MM.MorbusTubStudyXrayResult_setDT, 'dd.mm.yyyy') as \"MorbusTubStudyXrayResult_setDT\",
				MM.MorbusTubStudyXrayResult_Comment as \"MorbusTubStudyXrayResult_Comment\",
				MM.TubXrayResultType_id as \"TubXrayResultType_id\",
				TubXrayResultType.TubXrayResultType_Name as \"TubXrayResultType_Name\"
			from
				v_MorbusTubStudyXrayResult MM
				left join v_TubXrayResultType TubXrayResultType on TubXrayResultType.TubXrayResultType_id = MM.TubXrayResultType_id
			where
				{$filter}
		";


		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Результаты исследований ренгена"
	 * @param array $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubStudyXrayResult($data) {
		if ( (!isset($data['MorbusTubStudyXrayResult_id'])) || ($data['MorbusTubStudyXrayResult_id'] <= 0) ) {
			$procedure = 'p_MorbusTubStudyXrayResult_ins';
		}
		else {
			$procedure = 'p_MorbusTubStudyXrayResult_upd';
		}

		$query = "
			select
				MorbusTubStudyXrayResult_id as \"MorbusTubStudyXrayResult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubStudyXrayResult_id := :MorbusTubStudyXrayResult_id,
				MorbusTubStudyResult_id := :MorbusTubStudyResult_id,
				TubXrayResultType_id := :TubXrayResultType_id,
				MorbusTubStudyXrayResult_Comment := :MorbusTubStudyXrayResult_Comment,
				MorbusTubStudyXrayResult_setDT := :MorbusTubStudyXrayResult_setDT,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получает список данных "Результаты исследований - Тест на лекарственную чувствительность"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubStudyDrugResultViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusTubStudyDrugResult_id']) and ($data['MorbusTubStudyDrugResult_id'] > 0)) {
			$filter = "MM.MorbusTubStudyDrugResult_id = :MorbusTubStudyDrugResult_id";
		} else {
			if(empty($data['MorbusTubStudyResult_id']) or $data['MorbusTubStudyResult_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id";
		}
		$query = "
			select
				MM.MorbusTubStudyDrugResult_id as \"MorbusTubStudyDrugResult_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				to_char(MM.MorbusTubStudyDrugResult_setDT, 'dd.mm.yyyy') as \"MorbusTubStudyDrugResult_setDT\",
				MM.MorbusTubStudyDrugResult_IsResult as \"MorbusTubStudyDrugResult_IsResult\",
				YesNo.YesNo_Name as \"MorbusTubStudyDrugResult_IsResult_Name\",
				MM.TubDrug_id as \"TubDrug_id\",
				TubDrug.TubDrug_Name as \"TubDrug_Name\"
			from
				v_MorbusTubStudyDrugResult MM
				left join v_YesNo YesNo on YesNo.YesNo_id = MM.MorbusTubStudyDrugResult_IsResult
				left join v_TubDrug TubDrug on TubDrug.TubDrug_id = MM.TubDrug_id
			where
				{$filter}
		";


		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Результаты исследований - Тест на лекарственную чувствительность"
	 * @param array $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubStudyDrugResult($data) {

		$procedure = '';
		if ( (!isset($data['MorbusTubStudyDrugResult_id'])) || ($data['MorbusTubStudyDrugResult_id'] <= 0) ) {
			$procedure = 'p_MorbusTubStudyDrugResult_ins';
		}
		else {
			$procedure = 'p_MorbusTubStudyDrugResult_upd';
		}

		$query = "
			select
				MorbusTubStudyDrugResult_id as \"MorbusTubStudyDrugResult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubStudyDrugResult_id := :MorbusTubStudyDrugResult_id,
				MorbusTubStudyResult_id := :MorbusTubStudyResult_id,
				MorbusTubStudyDrugResult_IsResult := :MorbusTubStudyDrugResult_IsResult,
				MorbusTubStudyDrugResult_setDT := :MorbusTubStudyDrugResult_setDT,
				TubDrug_id := :TubDrug_id,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получает список данных "Результаты исследований - Молекулярно-генетические методы"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubMolecularViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusTubMolecular_id']) and ($data['MorbusTubMolecular_id'] > 0)) {
			$filter = "MM.MorbusTubMolecular_id = :MorbusTubMolecular_id";
		} else {
			if(empty($data['MorbusTubStudyResult_id']) or $data['MorbusTubStudyResult_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id";
		}
		$query = "
			select
				MM.MorbusTubMolecular_id as \"MorbusTubMolecular_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				to_char(MM.MorbusTubMolecular_setDT, 'dd.mm.yyyy') as \"MorbusTubMolecular_setDT\",
				MM.MorbusTubMolecular_IsResult as \"MorbusTubMolecular_IsResult\",
				YesNo.YesNo_Name as \"MorbusTubMolecular_IsResult_Name\",
				MM.MorbusTubMolecularType_id as \"MorbusTubMolecularType_id\",
				TubDrug.MorbusTubMolecularType_Name as \"MorbusTubMolecularType_Name\"
			from
				v_MorbusTubMolecular MM
				left join v_YesNo YesNo on YesNo.YesNo_id = MM.MorbusTubMolecular_IsResult
				left join v_MorbusTubMolecularType TubDrug on TubDrug.MorbusTubMolecularType_id = MM.MorbusTubMolecularType_id
			where
				{$filter}
		";


		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Результаты исследований - Молекулярно-генетические методы"
	 * @param array $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubMolecular($data) {

		$procedure = '';
		if ( (!isset($data['MorbusTubMolecular_id'])) || ($data['MorbusTubMolecular_id'] <= 0) ) {
			$procedure = 'p_MorbusTubMolecular_ins';
		}
		else {
			$procedure = 'p_MorbusTubMolecular_upd';
		}

		$query = "
			select
				MorbusTubMolecular_id as \"MorbusTubMolecular_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubMolecular_id := :MorbusTubMolecular_id,
				MorbusTubStudyResult_id := :MorbusTubStudyResult_id,
				MorbusTubMolecular_IsResult := :MorbusTubMolecular_IsResult,
				MorbusTubMolecular_setDT := :MorbusTubMolecular_setDT,
				MorbusTubMolecularType_id := :MorbusTubMolecularType_id,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получает список данных "Результаты исследований"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubStudyResultViewData($data)
	{

		$filter = "(1=1)";
		if (isset($data['MorbusTubStudyResult_id']) and ($data['MorbusTubStudyResult_id'] > 0)) {
			$query = "
			select
				case when M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				MT.MorbusTub_id as \"MorbusTub_id\",
				MB.Person_id as \"Person_id\",
				MM.TubStageChemType_id as \"TubStageChemType_id\",
				TubStageChemType_Code || '. ' || TubStageChemType_Name as \"TubStageChemType_Name\",
				MM.PersonWeight_id as \"PersonWeight_id\",
				PersonWeight.PersonWeight_Weight as \"PersonWeight_Weight\"
			from
				v_MorbusTubStudyResult MM
				inner join v_MorbusTub MT on MT.MorbusTub_id = MM.MorbusTub_id
				inner join v_Morbus M on MT.Morbus_id = M.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_TubStageChemType TubStageChemType on TubStageChemType.TubStageChemType_id = MM.TubStageChemType_id
				left join v_PersonWeight PersonWeight on PersonWeight.PersonWeight_id = MM.PersonWeight_id
			where
				MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id
		";
		} else {
			if(empty($data['MorbusTub_id']) or $data['MorbusTub_id'] < 0) {
				return array();
			}
			$query = "
			select
				case when M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				coalesce(MM.MorbusTubStudyResult_id, -TT.TubStageChemType_id) as \"MorbusTubStudyResult_id\",
				coalesce(MT.MorbusTub_id, :MorbusTub_id) as \"MorbusTub_id\",
				MB.Person_id as \"Person_id\",
				MM.TubStageChemType_id as \"TubStageChemType_id\",
				TubStageChemType_Code || '. ' || TubStageChemType_Name as \"TubStageChemType_Name\",
				MM.PersonWeight_id as \"PersonWeight_id\",
				PersonWeight.PersonWeight_Weight as \"PersonWeight_Weight\",
				Micros.TubMicrosResultType_Name as \"TubMicrosResultType_Name\",
				Micros.MorbusTubStudyMicrosResult_setDT as \"MorbusTubStudyMicrosResult_setDT\",
				Seed.TubSeedResultType_Name as \"TubSeedResultType_Name\",
				Seed.MorbusTubStudySeedResult_setDT as \"MorbusTubStudySeedResult_setDT\",
				Xray.TubXrayResultType_Name as \"TubXrayResultType_Name\",
				Xray.MorbusTubStudyXrayResult_setDT as \"MorbusTubStudyXrayResult_setDT\",
				Histol.TubHistolResultType_Name as \"TubHistolResultType_Name\",
				Histol.MorbusTubStudyHistolResult_setDT as \"MorbusTubStudyHistolResult_setDT\",
				:Evn_id as \"MorbusTub_pid\"
			from v_TubStageChemType TT
				left join v_MorbusTubStudyResult MM on TT.TubStageChemType_id = MM.TubStageChemType_id
					and MM.MorbusTub_id = :MorbusTub_id
				left join v_MorbusTub MT on MT.MorbusTub_id = MM.MorbusTub_id
				left join v_Morbus M on MT.Morbus_id = M.Morbus_id
				left join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_PersonWeight PersonWeight  on PersonWeight.PersonWeight_id = MM.PersonWeight_id
				left join lateral(
					select
						to_char( Result.MorbusTubStudyMicrosResult_setDT, 'dd.mm.yyyy') as MorbusTubStudyMicrosResult_setDT,
						TubMicrosResultType.TubMicrosResultType_Name
					from v_MorbusTubStudyMicrosResult Result
					inner join TubMicrosResultType on TubMicrosResultType.TubMicrosResultType_id = Result.TubMicrosResultType_id
					where Result.MorbusTubStudyResult_id = MM.MorbusTubStudyResult_id
					order by Result.MorbusTubStudyMicrosResult_updDT DESC
					limit 1
				) Micros on true
				left join lateral(
					select
						to_char( Result.MorbusTubStudySeedResult_setDT, 'dd.mm.yyyy') as MorbusTubStudySeedResult_setDT,
						TubSeedResultType.TubSeedResultType_Name
					from v_MorbusTubStudySeedResult Result
					inner join TubSeedResultType on TubSeedResultType.TubSeedResultType_id = Result.TubSeedResultType_id
					where Result.MorbusTubStudyResult_id = MM.MorbusTubStudyResult_id
					order by Result.MorbusTubStudySeedResult_updDT DESC
					limit 1
				) Seed on true
				left join lateral(
					select
						to_char( Result.MorbusTubStudyXrayResult_setDT, 'dd.mm.yyyy') as MorbusTubStudyXrayResult_setDT,
						TubXrayResultType.TubXrayResultType_Name
					from v_MorbusTubStudyXrayResult Result
					inner join TubXrayResultType on TubXrayResultType.TubXrayResultType_id = Result.TubXrayResultType_id
					where Result.MorbusTubStudyResult_id = MM.MorbusTubStudyResult_id
					order by Result.MorbusTubStudyXrayResult_updDT DESC
					limit 1
				) Xray on true
				left join lateral(
					select
						to_char( Result.MorbusTubStudyHistolResult_setDT, 'dd.mm.yyyy') as MorbusTubStudyHistolResult_setDT,
						TubHistolResultType.TubHistolResultType_Name
					from v_MorbusTubStudyHistolResult Result
					inner join TubHistolResultType on TubHistolResultType.TubHistolResultType_id = Result.TubHistolResultType_id
					where Result.MorbusTubStudyResult_id = MM.MorbusTubStudyResult_id
					order by Result.MorbusTubStudyHistolResult_updDT DESC
					limit 1
				) Histol	 on true
			order by TT.TubStageChemType_id
		";
		}


		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получает список данных "Консультация фтизиохирурга"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubAdviceViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusTubAdvice_id']) and ($data['MorbusTubAdvice_id'] > 0)) {
			$filter = "MM.MorbusTubAdvice_id = :MorbusTubAdvice_id";
		} else {
			if(empty($data['MorbusTub_id']) or $data['MorbusTub_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTub_id = :MorbusTub_id";
		}
		$query = "
			select
				case when M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusTubAdvice_id as \"MorbusTubAdvice_id\",
				MT.MorbusTub_id as \"MorbusTub_id\",
				MB.Person_id as \"Person_id\",
				MM.TubAdviceResultType_id as \"TubAdviceResultType_id\",
				TubAdviceResultType.TubAdviceResultType_Name as \"TubAdviceResultType_Name\",
				to_char(MM.MorbusTubAdvice_setDT, 'dd.mm.yyyy') as \"MorbusTubAdvice_setDT\",
				:Evn_id as \"MorbusTub_pid\"
			from
				MorbusTubAdvice MM
				inner join v_MorbusTub MT on MT.MorbusTub_id = MM.MorbusTub_id
				inner join v_Morbus M on MT.Morbus_id = M.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_TubAdviceResultType TubAdviceResultType on TubAdviceResultType.TubAdviceResultType_id = MM.TubAdviceResultType_id
			where
				{$filter}
			order by MM.MorbusTubAdvice_id
		";

		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Консультация фтизиохирурга"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubAdvice($data) {
		$procedure = '';
		if ( (!isset($data['MorbusTubAdvice_id'])) || ($data['MorbusTubAdvice_id'] <= 0) ) {
			$procedure = 'p_MorbusTubAdvice_ins';
		}
		else {
			$procedure = 'p_MorbusTubAdvice_upd';
		}

		$query = "
			select
				MorbusTubAdvice_id as \"MorbusTubAdvice_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubAdvice_id := :MorbusTubAdvice_id,
				MorbusTub_id := :MorbusTub_id,
				TubAdviceResultType_id := :TubAdviceResultType_id,
				MorbusTubAdvice_setDT := :MorbusTubAdvice_setDT,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получает список данных "Оперативное лечение"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubAdviceOperViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusTubAdviceOper_id']) and ($data['MorbusTubAdviceOper_id'] > 0)) {
			$filter = "MM.MorbusTubAdviceOper_id = :MorbusTubAdviceOper_id";
		} else {
			if(empty($data['MorbusTubAdvice_id']) or $data['MorbusTubAdvice_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTubAdvice_id = :MorbusTubAdvice_id";
		}
		$query = "
			select
				MM.MorbusTubAdviceOper_id as \"MorbusTubAdviceOper_id\",
				MM.MorbusTubAdvice_id as \"MorbusTubAdvice_id\",
				to_char(MM.MorbusTubAdviceOper_setDT, 'dd.mm.yyyy') as \"MorbusTubAdviceOper_setDT\",
				MM.UslugaComplex_id as \"UslugaComplex_id\",
				UslugaComplex_Code || '. ' || UslugaComplex_Name as \"UslugaComplex_Name\"
			from
				v_MorbusTubAdviceOper MM
				inner join v_UslugaComplex UslugaComplex on UslugaComplex.UslugaComplex_id = MM.UslugaComplex_id
			where
				{$filter}
		";


		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Оперативное лечение"
	 * @param array $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubAdviceOper($data) {
		if ( (!isset($data['MorbusTubAdviceOper_id'])) || ($data['MorbusTubAdviceOper_id'] <= 0) ) {
			$procedure = 'p_MorbusTubAdviceOper_ins';
		}
		else {
			$procedure = 'p_MorbusTubAdviceOper_upd';
		}

		$query = "
			select
				MorbusTubAdviceOper_id as \"MorbusTubAdviceOper_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubAdviceOper_id := :MorbusTubAdviceOper_id,
				MorbusTubAdvice_id := :MorbusTubAdvice_id,
				UslugaComplex_id := :UslugaComplex_id,
				MorbusTubAdviceOper_setDT := :MorbusTubAdviceOper_setDT,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Результаты исследований"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubStudyResult($data) {

		$this->load->model('PersonWeight_model');
		if ( empty($data['PersonWeight_Weight']) ) {
			if (isset($data['PersonWeight_id'])) {
				// удаляем запись о весе
				$result = $this->PersonWeight_model->deletePersonWeight($data);
				if (empty($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление результатов измерения массы пациента)'));
				}
				if (!empty($result[0]['Error_Msg'])) {
					return $result;
				}
			}
			$data['PersonWeight_id'] = NULL;
		} else {
			// создаем или обновляем запись о весе
			$result = $this->PersonWeight_model->savePersonWeight(array(
				'Server_id' => $data['Server_id'],
				'PersonWeight_id' => $data['PersonWeight_id'],
				'Person_id' => $data['Person_id'],
				'PersonWeight_setDate' => date('Y-m-d'),
				'PersonWeight_Weight' => $data['PersonWeight_Weight'],
				'PersonWeight_IsAbnorm' => NULL,
				'WeightAbnormType_id' => NULL,
				'WeightMeasureType_id' => 3,
				'Evn_id'=>isset($data['Evn_id']) ? $data['Evn_id'] : NULL,
				'Okei_id' => 37,//кг
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($result[0]['Error_Msg'])) {
				return $result;
			}
			$data['PersonWeight_id'] = $result[0]['PersonWeight_id'];
		}

		if ( (!isset($data['MorbusTubStudyResult_id'])) || ($data['MorbusTubStudyResult_id'] <= 0) ) {
			$procedure = 'p_MorbusTubStudyResult_ins';
		} else {
			$procedure = 'p_MorbusTubStudyResult_upd';
		}

		$query = "
			select
				MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubStudyResult_id := :MorbusTubStudyResult_id,
				MorbusTub_id := :MorbusTub_id,
				TubStageChemType_id := :TubStageChemType_id,
				PersonWeight_id := :PersonWeight_id,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Метод получения данных по туберкулезу
	 * При вызове из формы просмотра записи регистра параметр MorbusTub_pid будет содержать Person_id, также будет передан PersonRegister_id
	 * При вызове из формы просмотра движения/посещения параметр MorbusTub_pid будет содержать Evn_id просматриваемого движения/посещения
	 */
	function getMorbusTubViewData($data)
	{
		if (empty($data['session'])) { $data['session'] = null; }
		if (empty($data['MorbusTub_pid'])) { $data['MorbusTub_pid'] = null; }
		if (empty($data['PersonRegister_id'])) { $data['PersonRegister_id'] = null; }
		$this->load->library('swMorbus');
		$params = swMorbus::onBeforeViewData($this->getMorbusTypeSysNick(), $data['session'], $data['MorbusTub_pid'], $data['PersonRegister_id']);
		if ($params['Error_Msg']) {
			throw new Exception($params['Error_Msg']);
		}
		$params['MorbusTub_pid'] = $data['MorbusTub_pid'];

		$add_select = '';
		$add_join = '';
		if ($this->isAllowPersonResidenceType) {
			$add_select .= '
				,PResT.PersonResidenceType_id as "PersonResidenceType_id"
				,PResT.PersonResidenceType_Name as "PersonResidenceType_Name"';
			$add_join .= "
				left join v_PersonState PS on PS.Person_id = MB.Person_id
				left join v_SocStatus SocStatus on SocStatus.SocStatus_id = PS.SocStatus_id
				left join v_PersonResidenceType PResT
					on PResT.PersonResidenceType_id = case
						when MT.PersonResidenceType_id is not null then MT.PersonResidenceType_id
						when SocStatus.SocStatus_SysNick like 'bomzh' then 2
						else 1
					end
			";
		}
		if ($this->isAllowMorbusTubMDR) {
			$add_select .= "
				,MDR.MorbusTubMDR_id as \"MorbusTubMDR_id\"
				,MDR.MorbusTubMDR_RegNumPerson as \"MorbusTubMDR_RegNumPerson\"
				,MDR.MorbusTubMDR_RegNumCard as \"MorbusTubMDR_RegNumCard\"
				,to_char(MDR.MorbusTubMDR_regDT, 'dd.mm.yyyy') as \"MorbusTubMDR_regDT\"
				,to_char(MDR.MorbusTubMDR_regdiagDT, 'dd.mm.yyyy') as \"MorbusTubMDR_regdiagDT\"
				,to_char(MDR.MorbusTubMDR_begDT, 'dd.mm.yyyy') as \"MorbusTubMDR_begDT\"
				,MDR.MorbusTubMDR_GroupDisp as \"MorbusTubMDR_GroupDisp\"
				,MDR.TubDiag_id as \"MorbusTubMDR_TubDiag_id\"
				,MDRTD.TubDiag_Name as \"MorbusTubMDR_TubDiag_Name\"
				,MDR.TubSickGroupType_id as \"MorbusTubMDR_TubSickGroupType_id\"
				,MDRTSGT.TubSickGroupType_Name as \"MorbusTubMDR_TubSickGroupType_Name\"
				,MDR.MorbusTubMDR_IsPathology as \"MorbusTubMDR_IsPathology\"
				,IsPathology.YesNo_Name as \"IsPathology_Name\"
				,MDR.MorbusTubMDR_IsART as \"MorbusTubMDR_IsART\"
				,IsART.YesNo_Name as \"IsART_Name\"
				,MDR.MorbusTubMDR_IsCotrim as \"MorbusTubMDR_IsCotrim\"
				,IsCotrim.YesNo_Name as \"IsCotrim_Name\"
				,MDR.MorbusTubMDR_IsDrugFirst as \"MorbusTubMDR_IsDrugFirst\"
				,IsDrugFirst.YesNo_Name as \"IsDrugFirst_Name\"
				,MDR.MorbusTubMDR_IsDrugSecond as \"MorbusTubMDR_IsDrugSecond\"
				,IsDrugSecond.YesNo_Name as \"IsDrugSecond_Name\"
				,MDR.MorbusTubMDR_IsDrugResult as \"MorbusTubMDR_IsDrugResult\"
				,IsDrugResult.YesNo_Name as \"IsDrugResult_Name\"
				,MDR.MorbusTubMDR_IsEmpiric as \"MorbusTubMDR_IsEmpiric\"
				,IsEmpiric.YesNo_Name as \"IsEmpiric_Name\"";
			$add_join .= "
				left join v_MorbusTubMDR MDR on MDR.Morbus_id = M.Morbus_id
				left join v_TubSickGroupType MDRTSGT on MDRTSGT.TubSickGroupType_id = MDR.TubSickGroupType_id
				left join v_TubDiag MDRTD on MDRTD.TubDiag_id = MDR.TubDiag_id
				left join v_YesNo IsPathology on IsPathology.YesNo_id = MDR.MorbusTubMDR_IsPathology
				left join v_YesNo IsDrugFirst on IsDrugFirst.YesNo_id = MDR.MorbusTubMDR_IsDrugFirst
				left join v_YesNo IsART on IsART.YesNo_id = MDR.MorbusTubMDR_IsART
				left join v_YesNo IsCotrim on IsCotrim.YesNo_id = MDR.MorbusTubMDR_IsCotrim
				left join v_YesNo IsDrugResult on IsDrugResult.YesNo_id = MDR.MorbusTubMDR_IsDrugResult
				left join v_YesNo IsDrugSecond on IsDrugSecond.YesNo_id = MDR.MorbusTubMDR_IsDrugSecond
				left join v_YesNo IsEmpiric on IsEmpiric.YesNo_id = MDR.MorbusTubMDR_IsEmpiric
			";
		}
		$query = "
			select
				" . swMorbus::getAccessTypeQueryPart('M', 'MB', 'MorbusTub_pid', '1', '0', 'accessType') . ",
				to_char(MT.MorbusTub_begDT, 'dd.mm.yyyy') as \"MorbusTub_begDT\",
				to_char(MT.MorbusTub_FirstDT, 'dd.mm.yyyy') as \"MorbusTub_FirstDT\",
				to_char(MT.MorbusTub_DiagDT, 'dd.mm.yyyy') as \"MorbusTub_DiagDT\",
				to_char(MT.MorbusTub_ResultDT, 'dd.mm.yyyy') as \"MorbusTub_ResultDT\",
				to_char(MT.MorbusTub_SanatorDT, 'dd.mm.yyyy') as \"MorbusTub_SanatorDT\",
				MT.TubSickGroupType_id as \"TubSickGroupType_id\", -- Группа больных
				TubSickGroupType.TubSickGroupType_Name as \"TubSickGroupType_Name\",
				MT.TubResultChemClass_id as \"TubResultChemClass_id\", -- Вид исхода курса химиотерапии
				TubResultChemClass.TubResultChemClass_Name as \"TubResultChemClass_Name\",
				MT.TubResultChemType_id as \"TubResultChemType_id\", -- Тип исхода курса химиотерапии
				TubResultChemType.TubResultChemType_Name as \"TubResultChemType_Name\",
				MT.MorbusTub_RegNumCard as \"MorbusTub_RegNumCard\", -- Региональный регистрационный номер пациента
				Diag.Diag_id as \"Diag_id\",
				Diag.Diag_FullName as \"Diag_Name\",
				MT.TubDiag_id as \"TubDiag_id\",
				TubDiag.TubDiag_Name as \"TubDiag_Name\",
				MT.TubPhase_id as \"TubPhase_id\",
				TubPhase.TubPhase_Name as \"TubPhase_Name\",
				MT.TubDisability_id as \"TubDisability_id\",
				TubDisability.TubDisability_Name as \"TubDisability_Name\",
				MT.TubResultDeathType_id as \"TubResultDeathType_id\", -- Причина смерти
				TubResultDeathType.TubResultDeathType_Name as \"TubResultDeathType_Name\", -- Причина смерти
				to_char(MT.MorbusTub_deadDT, 'dd.mm.yyyy') as \"MorbusTub_deadDT\", -- Дата смерти
				MT.MorbusTub_CountDay as \"MorbusTub_CountDay\", -- Общее кол-во дней нетрудоспособности
				to_char(MT.MorbusTub_ConvDT, 'dd.mm.yyyy') as \"MorbusTub_ConvDT\", -- Дата перевода в III группу ДУ
				MT.TubBreakChemType_id as \"TubBreakChemType_id\", -- Причина прерывания химиотерапии
				tbct.TubBreakChemType_Name as \"TubBreakChemType_Name\",
				to_char(MT.MorbusTub_breakDT, 'dd.mm.yyyy') as \"MorbusTub_breakDT\", -- Дата прерывания курса химиотерапии
				to_char(MT.MorbusTub_disDT, 'dd.mm.yyyy') as \"MorbusTub_disDT\", -- Дата выбытия
				to_char(MT.MorbusTub_unsetDT, 'dd.mm.yyyy') as \"MorbusTub_unsetDT\", -- Дата снятия диагноза туберкулеза
				MT.MorbusTub_id as \"MorbusTub_id\",
				M.Morbus_id as \"Morbus_id\",
				MB.MorbusBase_id as \"MorbusBase_id\",
				:MorbusTub_pid as \"MorbusTub_pid\",
				MB.Person_id as \"Person_id\",
				PR.PersonRegister_id as \"PersonRegister_id\",
				PDG.PersonDecreedGroup_id as \"PersonDecreedGroup_id\",
				PDG.PersonDecreedGroup_Name as \"PersonDecreedGroup_Name\",
				PLF.PersonLivingFacilies_id as \"PersonLivingFacilies_id\",
				PLF.PersonLivingFacilies_Name as \"PersonLivingFacilies_Name\",
				PDispG.PersonDispGroup_id as \"PersonDispGroup_id\",
				PDispG.PersonDispGroup_Name as \"PersonDispGroup_Name\",
				{$this->isAllowPersonResidenceType} as \"isAllowPersonResidenceType\",
				{$this->isAllowMorbusTubMDR} as \"isAllowMorbusTubMDR\"
				{$add_select}
			from
				v_Morbus M
				inner join v_MorbusBase MB on MB.MorbusBase_id = M.MorbusBase_id
				inner join v_MorbusTub MT on MT.Morbus_id = M.Morbus_id
				left join v_TubSickGroupType TubSickGroupType on TubSickGroupType.TubSickGroupType_id = MT.TubSickGroupType_id
				left join v_TubResultChemClass TubResultChemClass on TubResultChemClass.TubResultChemClass_id = MT.TubResultChemClass_id
				left join v_TubResultChemType TubResultChemType on TubResultChemType.TubResultChemType_id = MT.TubResultChemType_id
				left join v_TubResultDeathType TubResultDeathType on TubResultDeathType.TubResultDeathType_id = MT.TubResultDeathType_id
				left join v_TubDiag TubDiag on TubDiag.TubDiag_id = MT.TubDiag_id
				left join v_TubBreakChemType tbct on tbct.TubBreakChemType_id = MT.TubBreakChemType_id
				left join v_PersonRegister PR on PR.Morbus_id = M.Morbus_id
				left join v_Diag Diag on Diag.Diag_id = coalesce(PR.Diag_id, M.Diag_id)
				left join PersonDecreedGroup PDG on PDG.PersonDecreedGroup_id = MT.PersonDecreedGroup_id
				left join v_PersonLivingFacilies PLF on PLF.PersonLivingFacilies_id = MT.PersonLivingFacilies_id 
				left join v_PersonDispGroup PDispG on PDispG.PersonDispGroup_id = MT.PersonDispGroup_id
				left join v_TubPhase TubPhase on TubPhase.TubPhase_id = MT.TubPhase_id
				left join v_TubDisability TubDisability on TubDisability.TubDisability_id = MT.TubDisability_id 
				{$add_join}
			where
				M.Morbus_id = :Morbus_id
			limit 1
		";
		//echo getDebugSql($query, $params);die();
        $result = $this->db->query($query, $params);
        if ( !is_object($result) ) {
            return false;
        }

        $result = $result->result('array');

        if(!count($result)) {
            return false;
        }

        $res = $this->getTubSopDiags($result[0]['MorbusTub_id']);
        $result[0] = array_merge($result[0], $res);
        $resl = $this->getTubRiskTypes($result[0]['MorbusTub_id']);
        $result[0] = array_merge($result[0], $resl);

        return $result;
	}

	/**
	 * Получение сопутствующих заболеваний по заболеванию
	 */
	function getTubSopDiags($morbusTub)
	{
		if(empty($morbusTub)) { return array(); }
		$query = "
			select 
				 tds.TubDiagSop_id as \"TubDiagSop_id\",
				tdsl.TubDiagSopLink_id as \"TubDiagSopLink_id\",
				tdsl.TubDiagSopLink_Descr as \"TubDiagSopLink_Descr\"
			from v_TubDiagSop tds
			left join v_TubDiagSopLink tdsl on tdsl.TubDiagSop_id = tds.TubDiagSop_id and tdsl.MorbusTub_id = :MorbusTub_id
			where tds.TubDiagSop_id not in ('9','10')
		";
		//echo getDebugSQL($query, $data);
		$res = $this->db->query($query, array(
			'MorbusTub_id' => $morbusTub
		));
		if (is_object($res)) {
			$res = $res->result('array');
			$resl = array();
			foreach ($res as $value) {
				$resl['SopDiag'.$value['TubDiagSop_id']] = ((!empty($value['TubDiagSopLink_id']))?2:1);
				if($value['TubDiagSop_id'] == 7){
					$resl['SopDiag_Descr'] = ((!empty($value['TubDiagSopLink_Descr']))?$value['TubDiagSopLink_Descr']:null);
				}
			}
			return $resl;
		} else {
			throw new Exception('Ошибка при запросе к БД');
			return array();
		}
	}

	/**
	 * Получение фпкторов риска по заболеванию
	 */
	function getTubRiskTypes($morbusTub)
	{
		if(empty($morbusTub)) { return array(); }
		$query = "
			select 
				tds.TubRiskFactorType_id as \"TubRiskFactorType_id\", 
				tdsl.TubRiskFactorTypeLink_id as \"TubRiskFactorTypeLink_id\"
			from v_TubRiskFactorType tds
			left join v_TubRiskFactorTypeLink tdsl on tdsl.TubRiskFactorType_id = tds.TubRiskFactorType_id and tdsl.MorbusTub_id = :MorbusTub_id
		";
		//echo getDebugSQL($query, $data);
		$res = $this->db->query($query, array(
			'MorbusTub_id' => $morbusTub
		));
		if (is_object($res)) {
			$res = $res->result('array');
			$resl = array();
			foreach ($res as $value) {
				$resl['RiskType'.$value['TubRiskFactorType_id']] = ((!empty($value['TubRiskFactorTypeLink_id']))?2:1);
			}
			return $resl;
		} else {
			throw new Exception('Ошибка при запросе к БД');
			return array();
		}
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
		$not_edit_fields = array('Evn_pid', 'Person_id','MorbusTub_id', 'Morbus_id', 'MorbusBase_id','MorbusType_id','Morbus_setDT','Morbus_disDT','MorbusBase_setDT','MorbusBase_disDT');
		if(isset($data['field_notedit_list']) && is_array($data['field_notedit_list']))
		{
			$not_edit_fields = array_merge($not_edit_fields,$data['field_notedit_list']);
		}
		foreach($this->entityFields as $entity => $l_arr) {
			$allow_save = false;
			foreach($data as $key => $value) {
				if(in_array($key, $l_arr) && !in_array($key, $not_edit_fields))
				{
					$allow_save = true;
					break;
				}
			}
			if ( $allow_save && !empty($data[$entity.'_id']) )
			{
				$str = array();
				foreach($l_arr as $i) {
					array_push($str,$entity . '.' . $i . ' as "' . $i . '"');
				}
				$q = 'select '. implode(', ', $str) .' from dbo.v_'. $entity .' '. $entity .' where '. $entity .'_id = :'. $entity .'_id limit 1';
				$p = array($entity.'_id' => $data[$entity.'_id']);
				$r = $this->db->query($q, $data);
				//echo getDebugSQL($q, $data);
				if (is_object($r))
				{
					$result = $r->result('array');
					if( empty($result) || !is_array($result[0]) || count($result[0]) == 0 )
					{
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
				}
				else
				{
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
						{$entity}_id := :{$entity}_id, ". $field_str . "
						pmUser_id := :pmUser_id
					)
				";
				$p['pmUser_id'] = $data['pmUser_id'];
				//echo getDebugSQL($q, $p);
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
			}
			else
			{
				continue;
			}
		}
		if (!empty($data['MorbusTubMDR_id'])) {
			$result = $this->saveMorbusTubMDR($data);
			if ( empty($result) ) {
				$err_arr[] = 'Сохранение данных MorbusTubMDR. Ошибка при выполнении запроса к базе данных';
			}
			if ( !empty($result[0]['Error_Msg']) ) {
				$err_arr[] = 'Сохранение данных MorbusTubMDR '. $result[0]['Error_Msg'];
			}
			$entity_saved_arr['MorbusTubMDR_id'] = $data['MorbusTubMDR_id'];
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
		if(!empty($data['SopDiags'])){
			$res = $this->saveSopDiags($data);
			if (isset($res['Error_Msg'])) {
				//нужно откатить транзакцию
				throw new Exception($res['Error_Msg']);
			}
			$entity_saved_arr = array_merge($entity_saved_arr,$res);
		}
		if(!empty($data['RiskTypes'])){
			$res = $this->saveRiskTypes($data);
			if (isset($res['Error_Msg'])) {
				//нужно откатить транзакцию
				throw new Exception($res['Error_Msg']);
			}
			$entity_saved_arr = array_merge($entity_saved_arr,$res);
		}
		$entity_saved_arr['Morbus_id'] = $data['Morbus_id'];
		$entity_saved_arr['Error_Msg'] = (count($err_arr) > 0) ? implode('<br />',$err_arr) : null;
		return array($entity_saved_arr);
	}

	/**
	 * Сохраняет специфику по туберкулезу
	 * @param $data
	 * Обязательные параметры:
	 * 1) Evn_pid или Person_id
	 * 2) pmUser_id
	 * 3) Mode
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 * @author Alexander Permyakov aka Alexpm
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 * @throws Exception
	 */
	function saveMorbusTub($data)
	{
		return $this->saveMorbusSpecific($data);
	}
	/**
	 *
	 * @param array $data
	 * @return array
	 */
	function getTubDiag($data) {
		$filter = "(1=1)";
		if (strlength($data['query'])>0) {
			$filter .= " and (TubDiag_Code like :query||'%'  or TubDiag_Name like '%'||:query||'%')";
		} else {
			if (strlength($data['TubDiag_id'])>0) {
				$filter .= " and TubDiag_id = :TubDiag_id";
			}
			/*
			 if (strlength($data['TubDiag_Name'])>0) {
				$filter .= " and TubDiag_Name like :TubDiag_Name||'%'";
			}*/
		}

		$query = "
			select
				TubDiag_id as \"TubDiag_id\",
				Diag_id as \"Diag_id\",
				TubDiag_Code as \"TubDiag_Code\",
				TubDiag_Name as \"TubDiag_Name\"
			from
				v_TubDiag
			where
				{$filter}
			limit 100
		";
		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Сохраняет специфику "Результаты микроскопических исследований"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubMDR($data) {
		if ( empty($data['MorbusTubMDR_id']) ) {
			$procedure = 'p_MorbusTubMDR_ins';
			$data['MorbusTubMDR_id'] = null;
		} else {
			$procedure = 'p_MorbusTubMDR_upd';
		}
		$query = "
			select
				MorbusTubMDR_id as \"MorbusTubMDR_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubMDR_id := :MorbusTubMDR_id,
				Morbus_id := :Morbus_id,
				MorbusTubMDR_RegNumPerson := cast(:MorbusTubMDR_RegNumPerson as varchar),
				MorbusTubMDR_RegNumCard := cast(:MorbusTubMDR_RegNumCard as varchar),
				MorbusTubMDR_regDT := :MorbusTubMDR_regDT,
				MorbusTubMDR_regdiagDT := :MorbusTubMDR_regdiagDT,
				MorbusTubMDR_begDT := :MorbusTubMDR_begDT,
				MorbusTubMDR_GroupDisp := :MorbusTubMDR_GroupDisp,
				TubDiag_id := :MorbusTubMDR_TubDiag_id,
				TubSickGroupType_id := :MorbusTubMDR_TubSickGroupType_id,
				MorbusTubMDR_IsPathology := :MorbusTubMDR_IsPathology,
				MorbusTubMDR_IsART := :MorbusTubMDR_IsART,
				MorbusTubMDR_IsCotrim := :MorbusTubMDR_IsCotrim,
				MorbusTubMDR_IsDrugFirst := :MorbusTubMDR_IsDrugFirst,
				MorbusTubMDR_IsDrugSecond := :MorbusTubMDR_IsDrugSecond,
				MorbusTubMDR_IsDrugResult := :MorbusTubMDR_IsDrugResult,
				MorbusTubMDR_IsEmpiric := :MorbusTubMDR_IsEmpiric,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Сопутствующие заболевания"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveSopDiags($data) {

		if(!empty($data['SopDiags'])){
			$sopDiags = json_decode($data['SopDiags']);
		} else {
			return false;
		}

		$query = "
			select TubDiagSopLink_id 
			from v_TubDiagSopLink
			where MorbusTub_id = :MorbusTub_id and TubDiagSop_id = :TubDiagSop_id
		";
		foreach ($sopDiags as $key=>$value) {
			$tubDiagSop_id = substr(trim($key),-1);
			$params = array();
			$params['MorbusTub_id'] = $data['MorbusTub_id'];
			$params['TubDiagSop_id'] = $tubDiagSop_id;
			$res = $this->db->query($query, $params);
			if (is_object($res)) {
				$old = $res->result('array');
				if($value == 1 && !empty($old[0]['TubDiagSopLink_id'])){
					$query1 = "			
						select
							TubDiagSopLink_id as \"TubDiagSopLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_TubDiagSopLink_del(
							TubDiagSopLink_id := :TubDiagSopLink_id
						)
					";
					foreach ($old as $item) {
						$qp = array();
						$qp['TubDiagSopLink_id'] = $item['TubDiagSopLink_id'];
						$result = $this->db->query($query1, $qp);
						$result = $result->result('array');
						if (!empty($result['Error_Msg'])) {
							//нужно откатить транзакцию
							throw new Exception($result['Error_Msg']);
						}
					}
				} else if ($value == 2 && empty($old[0]['TubDiagSopLink_id'])) {
					$query2 = "
						select
							TubDiagSopLink_id as \"TubDiagSopLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_TubDiagSopLink_ins(
							TubDiagSopLink_id := :TubDiagSopLink_id,
							TubDiagSop_id := :TubDiagSop_id,
							EvnNotifyTub_id := :EvnNotifyTub_id,
							MorbusTub_id := :MorbusTub_id,
							TubDiagSopLink_Descr := :TubDiagSopLink_Descr,
							pmUser_id := :pmUser_id
						)
					";
					$queryPrms = array();
					$queryPrms['TubDiagSopLink_id'] = null;
					$queryPrms['TubDiagSop_id'] = $tubDiagSop_id;
					$queryPrms['EvnNotifyTub_id'] = null;
					$queryPrms['MorbusTub_id'] = $data['MorbusTub_id'];
					$queryPrms['TubDiagSopLink_Descr'] = ($tubDiagSop_id == 7) ? $data['SopDiag_Descr'] : null;
					$queryPrms['pmUser_id'] = $data['pmUser_id'];
					$result = $this->db->query($query2, $queryPrms);
					$result = $result->result('array');
					if (!empty($result['Error_Msg'])) {
						//нужно откатить транзакцию
						throw new Exception($result['Error_Msg']);
					}
				}
			} else {
				throw new Exception('Ошибка при запросе к БД');
			}
		}
		return $this->getTubSopDiags($data['MorbusTub_id']);
	}

	/**
	 * Сохраняет специфику "Факторы риска"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveRiskTypes($data) {

		if(!empty($data['RiskTypes'])){
			$RiskTypes = json_decode($data['RiskTypes']);
		} else {
			return false;
		}

		$query = "
			select TubRiskFactorTypeLink_id as \"TubRiskFactorTypeLink_id\" 
			from v_TubRiskFactorTypeLink
			where MorbusTub_id = :MorbusTub_id and TubRiskFactorType_id = :TubRiskFactorType_id
		";
		foreach ($RiskTypes as $key=>$value) {
			$tubRiskType_id = substr(trim($key),-1);
			$params = array();
			$params['MorbusTub_id'] = $data['MorbusTub_id'];
			$params['TubRiskFactorType_id'] = $tubRiskType_id;
			$res = $this->db->query($query, $params);
			if (is_object($res)) {
				$old = $res->result('array');
				if($value == 1 && !empty($old[0]['TubRiskFactorTypeLink_id'])){
					$query1 = "			
						select
							TubRiskFactorTypeLink_id as \"TubRiskFactorTypeLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_TubRiskFactorTypeLink_del(
							TubRiskFactorTypeLink_id := :TubRiskFactorTypeLink_id
						)
					";
					foreach ($old as $item) {
						$qp = array();
						$qp['TubRiskFactorTypeLink_id'] = $item['TubRiskFactorTypeLink_id'];
						$result = $this->db->query($query1, $qp);
						$result = $result->result('array');
						if (!empty($result['Error_Msg'])) {
							//нужно откатить транзакцию
							throw new Exception($result['Error_Msg']);
						}
					}
				} else if ($value == 2 && empty($old[0]['TubRiskFactorTypeLink_id'])) {
					$query2 = "
						select
							TubRiskFactorTypeLink_id as \"TubRiskFactorTypeLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_TubRiskFactorTypeLink_ins(
							TubRiskFactorTypeLink_id := :TubRiskFactorTypeLink_id,
							TubRiskFactorType_id := :TubRiskFactorType_id,
							EvnNotifyTub_id := :EvnNotifyTub_id,
							MorbusTub_id := :MorbusTub_id,
							TubRiskFactorTypeLink_Descr := null,
							pmUser_id := :pmUser_id
						)
					";
					$queryPrms = array();
					$queryPrms['TubRiskFactorTypeLink_id'] = null;
					$queryPrms['TubRiskFactorType_id'] = $tubRiskType_id;
					$queryPrms['EvnNotifyTub_id'] = null;
					$queryPrms['MorbusTub_id'] = $data['MorbusTub_id'];
					$queryPrms['pmUser_id'] = $data['pmUser_id'];
					$result = $this->db->query($query2, $queryPrms);
					$result = $result->result('array');
					if (!empty($result['Error_Msg'])) {
						//нужно откатить транзакцию
						throw new Exception($result['Error_Msg']);
					}
				}
			} else {
				throw new Exception('Ошибка при запросе к БД');
			}
		}
		return $this->getTubRiskTypes($data['MorbusTub_id']);
	}

	/**
	 * Получает список данных "Лечебные мероприятия"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusTubMDRPrescrViewData($data) {
		if (isset($data['MorbusTubPrescr_id']) and ($data['MorbusTubPrescr_id'] > 0)) {
			$filter = "MM.MorbusTubPrescr_id = :MorbusTubPrescr_id";
		} else {
			if (empty($data['MorbusTub_id']) or $data['MorbusTub_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusTub_id = :MorbusTub_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and M.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusTub_id as \"MorbusTub_id\",
				MM.MorbusTubMDR_id as \"MorbusTubMDR_id\",
				MM.MorbusTubPrescr_id as \"MorbusTubPrescr_id\",
				to_char(MM.MorbusTubPrescr_setDT, 'dd.mm.yyyy') as \"MorbusTubPrescr_setDT\",
				to_char(MM.MorbusTubPrescr_endDate, 'dd.mm.yyyy') as \"MorbusTubPrescr_endDate\",
				MM.TubDrug_id as \"TubDrug_id\",
				TubDrug.TubDrug_Name as \"TubDrug_Name\",
				MM.Lpu_id as \"Lpu_id\",
				MM.LpuSection_id as \"LpuSection_id\",
				MM.PersonWeight_id as \"PersonWeight_id\",
				MB.Person_id as \"Person_id\",
				PW.PersonWeight_Weight as \"PersonWeight_Weight\",
				MM.MorbusTubPrescr_DoseMiss as \"MorbusTubPrescr_DoseMiss\",
				MM.MorbusTubPrescr_DoseDay as \"MorbusTubPrescr_DoseDay\",
				MM.MorbusTubPrescr_DoseTotal as \"MorbusTubPrescr_DoseTotal\",
				MM.MorbusTubPrescr_SetDay as \"MorbusTubPrescr_SetDay\",
				MM.MorbusTubPrescr_MissDay as \"MorbusTubPrescr_MissDay\",
				MM.MorbusTubPrescr_Comment as \"MorbusTubPrescr_Comment\",
				:Evn_id as \"MorbusTub_pid\"
			from
				v_MorbusTubPrescr MM
				inner join v_MorbusTub MT on MT.MorbusTub_id = MM.MorbusTub_id
				inner join v_Morbus M on MT.Morbus_id = M.Morbus_id
				inner join v_MorbusBase MB on M.MorbusBase_id = MB.MorbusBase_id
				left join v_TubStageChemType TubStageChemType on TubStageChemType.TubStageChemType_id = MM.TubStageChemType_id
				left join v_TubDrug TubDrug on TubDrug.TubDrug_id = MM.TubDrug_id
				left join v_PersonWeight PW on PW.PersonWeight_id = MM.PersonWeight_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter} and MM.MorbusTubMDR_id is not null
			order by MM.MorbusTubPrescr_setDT
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Лечебные мероприятия"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusTubMDRPrescr($data) {
		if ( (!isset($data['MorbusTubPrescr_id'])) || ($data['MorbusTubPrescr_id'] <= 0) ) {
			$procedure = 'p_MorbusTubPrescr_ins';
		} else {
			$procedure = 'p_MorbusTubPrescr_upd';
		}
		if ( empty($data['MorbusTubMDR_id']) ) {
			return false;
		}

		$this->load->model('PersonWeight_model');
		if ( empty($data['PersonWeight_Weight']) ) {
			if (isset($data['PersonWeight_id'])) {
				// удаляем запись о весе
				$result = $this->PersonWeight_model->deletePersonWeight($data);
				if (empty($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление результатов измерения массы пациента)'));
				}
				if (!empty($result[0]['Error_Msg'])) {
					return $result;
				}
			}
			$data['PersonWeight_id'] = NULL;
		} else {
			// создаем или обновляем запись о весе
			$result = $this->PersonWeight_model->savePersonWeight(array(
				'Server_id' => $data['Server_id'],
				'PersonWeight_id' => $data['PersonWeight_id'],
				'Person_id' => $data['Person_id'],
				'PersonWeight_setDate' => $data['MorbusTubPrescr_setDT'],
				'PersonWeight_Weight' => $data['PersonWeight_Weight'],
				'PersonWeight_IsAbnorm' => NULL,
				'WeightAbnormType_id' => NULL,
				'WeightMeasureType_id' => NULL,
				'Evn_id'=>$data['Evn_id'],
				'Okei_id' => 37,//кг
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($result[0]['Error_Msg'])) {
				return $result;
			}
			$data['PersonWeight_id'] = $result[0]['PersonWeight_id'];
		}

		$query = "
			select
				MorbusTubPrescr_id as \"MorbusTubPrescr_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubPrescr_id := :MorbusTubPrescr_id,
				MorbusTub_id := :MorbusTub_id,
				MorbusTubMDR_id := :MorbusTubMDR_id,
				MorbusTubPrescr_setDT := :MorbusTubPrescr_setDT,
				MorbusTubPrescr_endDate := :MorbusTubPrescr_endDate,
				TubDrug_id := :TubDrug_id,
				Lpu_id := :Lpu_id,
				LpuSection_id := :LpuSection_id,
				PersonWeight_id := :PersonWeight_id,
				MorbusTubPrescr_SetDay := :MorbusTubPrescr_SetDay,
				MorbusTubPrescr_MissDay := :MorbusTubPrescr_MissDay,
				MorbusTubPrescr_Comment := :MorbusTubPrescr_Comment,
				MorbusTubPrescr_DoseMiss := :MorbusTubPrescr_DoseMiss,
				MorbusTubPrescr_DoseDay := :MorbusTubPrescr_DoseDay,
				MorbusTubPrescr_DoseTotal := :MorbusTubPrescr_DoseTotal,
				pmUser_id := :pmUser_id
			)
		";

		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получает список данных "Тест на лекарственную чувствительность"
	 * @param $data
	 * @return array|bool
	 */
	function getMorbusTubMDRStudyDrugResultViewData($data)
	{
		if (isset($data['MorbusTubMDRStudyDrugResult_id']) and ($data['MorbusTubMDRStudyDrugResult_id'] > 0)) {
			$filter = "t.MorbusTubMDRStudyDrugResult_id = :MorbusTubMDRStudyDrugResult_id";
		} else {
			if(empty($data['MorbusTubMDRStudyResult_id']) or $data['MorbusTubMDRStudyResult_id'] < 0) {
				return array();
			}
			$filter = "t.MorbusTubMDRStudyResult_id = :MorbusTubMDRStudyResult_id";
		}
		$query = "
			select
				1 as \"accessType\",
				t.MorbusTubMDRStudyDrugResult_id as \"MorbusTubMDRStudyDrugResult_id\",
				t.MorbusTubMDRStudyResult_id as \"MorbusTubMDRStudyResult_id\",
				to_char(t.MorbusTubMDRStudyDrugResult_setDT, 'dd.mm.yyyy') as \"MorbusTubMDRStudyDrugResult_setDT\",
				t.TubDiagResultType_id as \"TubDiagResultType_id\",
				TubDiagResultType_Name as \"TubDiagResultType_Name\",
				t.TubDrug_id as \"TubDrug_id\",
				TubDrug_Name as \"TubDrug_Name\",
				:Evn_id as \"MorbusTub_pid\"
			from
				v_MorbusTubMDRStudyDrugResult t
				left join v_TubDiagResultType on t.TubDiagResultType_id = v_TubDiagResultType.TubDiagResultType_id
				left join v_TubDrug on t.TubDrug_id = v_TubDrug.TubDrug_id
			where
				{$filter}
			order by t.MorbusTubMDRStudyDrugResult_setDT
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Тест на лекарственную чувствительность"
	 * @param $data
	 * @return array|bool
	 */
	function saveMorbusTubMDRStudyDrugResult($data)
	{
		if ( (empty($data['MorbusTubMDRStudyDrugResult_id'])) || ($data['MorbusTubMDRStudyDrugResult_id'] < 0) ) {
			$procedure = 'p_MorbusTubMDRStudyDrugResult_ins';
		} else {
			$procedure = 'p_MorbusTubMDRStudyDrugResult_upd';
		}
		if ( empty($data['MorbusTubMDRStudyResult_id']) ) {
			return false;
		}
		$query = "
			select
				MorbusTubMDRStudyDrugResult_id as \"MorbusTubMDRStudyDrugResult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubMDRStudyDrugResult_id := :MorbusTubMDRStudyDrugResult_id,
				MorbusTubMDRStudyResult_id := :MorbusTubMDRStudyResult_id,
				MorbusTubMDRStudyDrugResult_setDT := :MorbusTubMDRStudyDrugResult_setDT,
				TubDrug_id := :TubDrug_id,
				TubDiagResultType_id := :TubDiagResultType_id,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получает список данных "Результаты исследований"
	 * @param $data
	 * @return array|bool
	 */
	function getMorbusTubMDRStudyResultViewData($data)
	{
		$addSelect = '';
		if (isset($data['MorbusTubMDRStudyResult_id']) and ($data['MorbusTubMDRStudyResult_id'] > 0)) {
			$filter = "t.MorbusTubMDRStudyResult_id = :MorbusTubMDRStudyResult_id";
		} else {
			if (empty($data['MorbusTub_id']) or $data['MorbusTub_id'] < 0) {
				return array();
			}
			$filter = "m.MorbusTub_id = :MorbusTub_id";
			$addSelect = ",(
				select to_char(dr.MorbusTubMDRStudyDrugResult_setDT, 'dd.mm.yyyy')
				from v_MorbusTubMDRStudyDrugResult dr
				where dr.MorbusTubMDRStudyResult_id = t.MorbusTubMDRStudyResult_id
				order by dr.MorbusTubMDRStudyDrugResult_setDT desc
				limit 1
			) as \"MorbusTubMDRStudyDrugResult_setDT\"";
		}
		$query = "
			select
				1 as \"accessType\",
				t.MorbusTubMDRStudyResult_id as \"MorbusTubMDRStudyResult_id\",
				t.MorbusTubMDR_id as \"MorbusTubMDR_id\",
				m.MorbusTub_id as \"MorbusTub_id\",
				to_char(t.MorbusTubMDRStudyResult_setDT, 'dd.mm.yyyy') as \"MorbusTubMDRStudyResult_setDT\",
				t.MorbusTubMDRStudyResult_Month as \"MorbusTubMDRStudyResult_Month\",
				t.MorbusTubMDRStudyResult_NumLab as \"MorbusTubMDRStudyResult_NumLab\",
				t.TubHistolResultType_id as \"TubHistolResultType_id\",
				t.TubMicrosResultType_id as \"TubMicrosResultType_id\",
				t.TubSeedResultType_id as \"TubSeedResultType_id\",
				t.TubXrayResultType_id as \"TubXrayResultType_id\",
				TubXrayResultType_Name as \"TubXrayResultType_Name\",
				t.MorbusTubMDRStudyResult_Comment as \"MorbusTubMDRStudyResult_Comment\",
				:Evn_id as \"MorbusTub_pid\"
				{$addSelect}
			from
				v_MorbusTub m
				inner join v_MorbusTubMDR mdr on m.Morbus_id = mdr.Morbus_id
				inner join v_MorbusTubMDRStudyResult t on mdr.MorbusTubMDR_id = t.MorbusTubMDR_id
				left join v_TubXrayResultType on t.TubXrayResultType_id = v_TubXrayResultType.TubXrayResultType_id
			where
				{$filter}
			order by t.MorbusTubMDRStudyResult_setDT
		";
		/*
				TubSeedResultType_Name,
				TubMicrosResultType_Name,
				TubHistolResultType_Name,
				left join v_TubHistolResultType on t.TubHistolResultType_id = v_TubHistolResultType.TubHistolResultType_id
				left join v_TubMicrosResultType on t.TubMicrosResultType_id = v_TubMicrosResultType.TubMicrosResultType_id
				left join v_TubSeedResultType on t.TubSeedResultType_id = v_TubSeedResultType.TubSeedResultType_id
		 */
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Результаты исследований"
	 * @param $data
	 * @return array|bool
	 */
	function saveMorbusTubMDRStudyResult($data)
	{
		if ( (empty($data['MorbusTubMDRStudyResult_id'])) || ($data['MorbusTubMDRStudyResult_id'] < 0) ) {
			$procedure = 'p_MorbusTubMDRStudyResult_ins';
		} else {
			$procedure = 'p_MorbusTubMDRStudyResult_upd';
		}
		if ( empty($data['MorbusTubMDR_id']) ) {
			return false;
		}
		$query = "
			select
				MorbusTubMDRStudyResult_id as \"MorbusTubMDRStudyResult_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusTubMDRStudyResult_id := :MorbusTubMDRStudyResult_id,
				MorbusTubMDR_id := :MorbusTubMDR_id,
				MorbusTubMDRStudyResult_setDT := :MorbusTubMDRStudyResult_setDT,
				MorbusTubMDRStudyResult_Month := :MorbusTubMDRStudyResult_Month,
				MorbusTubMDRStudyResult_NumLab := :MorbusTubMDRStudyResult_NumLab,
				TubHistolResultType_id := :TubHistolResultType_id,
				TubMicrosResultType_id := :TubMicrosResultType_id,
				TubSeedResultType_id := :TubSeedResultType_id,
				TubXrayResultType_id := :TubXrayResultType_id,
				MorbusTubMDRStudyResult_Comment := :MorbusTubMDRStudyResult_Comment,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаляет специфику "Результаты исследований"
	 * @param $data
	 * @return array
	 */
	function deleteMorbusTubMDRStudyResult($data)
	{
		$this->isAllowTransaction = true;
		$trans_started = $this->beginTransaction();
		try {
			$result = $this->db->query("
				select MorbusTubMDRStudyDrugResult_id as \"MorbusTubMDRStudyDrugResult_id\"
				from MorbusTubMDRStudyDrugResult
				where MorbusTubMDRStudyResult_id = :MorbusTubMDRStudyResult_id
			", array(
				'MorbusTubMDRStudyResult_id' => $data['MorbusTubMDRStudyResult_id'],
			));
			if ( false == is_object($result) ) {
				throw new Exception('Не удалось выполнить запрос результатов на ТЛЧ', 500);
			}
			$tmp = $result->result('array');
			foreach ($tmp as $row) {
				$result = $this->execCommonSP('p_MorbusTubMDRStudyDrugResult_del', array(
					'MorbusTubMDRStudyDrugResult_id' => $row['MorbusTubMDRStudyDrugResult_id'],
				), 'array_assoc');
				if ( empty($result) ) {
					throw new Exception('Не удалось выполнить запрос удаления результата на ТЛЧ', 500);
				}
				if ( !empty($result['Error_Msg']) ) {
					throw new Exception($result['Error_Msg'], $result['Error_Code']);
				}
			}

			$result = $this->execCommonSP('p_MorbusTubMDRStudyResult_del', array(
				'MorbusTubMDRStudyResult_id' => $data['MorbusTubMDRStudyResult_id'],
			), 'array_assoc');
			if ( empty($result) ) {
				throw new Exception('Не удалось выполнить запрос удаления результатов исследований', 500);
			}
			if ( !empty($result['Error_Msg']) ) {
				throw new Exception($result['Error_Msg'], $result['Error_Code']);
			}
		} catch (Exception $e) {
			if ($trans_started) {
				$this->rollbackTransaction();
			}
			$result = array();
			$result['Error_Code'] = $e->getCode();
			$result['Error_Msg'] = $e->getMessage();
		}
		if ($trans_started) {
			$this->commitTransaction();
		}
		return $result;
	}

	/**
	 * @return array
	 */
	function getFieldsMapForXLS() {
		return array(
			'SNILS' => 'СНИЛС',
			'Person_SurName' => 'Фамилия',
			'Person_FirName' => 'Имя',
			'Person_SecName' => 'Отчество',
			'Person_BirthDay' => 'Дата рождения',
			'Sex' => 'Пол',
			'SocStatus' => 'Соц.статус',
			'PersonDecreedGroup' => 'Группа',
			'OrgJob_Name' => 'Место работы (учебы)',
			'DocumentType' => 'Тип документа',
			'Document_Ser' => 'Серия документа',
			'Document_Num' => 'Номер документа',
			'Document_begDate' => 'Дата выдачи',
			'OrgDep_Nick' => 'Выдан',
			'PersonRegister_setDate' => 'Дата включения в регистр',
			'PersonRegister_disDate' => 'Дата исключения из регистра',
			'OutCause' => 'Причина исключения',
			'OID' => 'OID медицинской организации',
			'PersonCategoryType' => 'Категория',
			'PersonLivingFacilies' => 'Жилищные условия',
			'TubDetectionPlaceType' => 'Место выявления',
			'TubDetectionFactType' => 'Обстоятельства выявления',
			'TubRegCrazyType' => 'Учет в наркологическом диспансере',
			'FirstDT' => 'Дата первого обращения за медицинской помощью',
			'RegDT' => 'Дата постановки на диспансерный учет',
			'hasB20_B95' => 'Наличие В20-24',
			'IsInvalid' => 'Наличие инвалидности',
			'TubDiagSop' => 'Сопутствующие заболевания',
			'IsConfirmedDiag' => 'Диагноз подтвержден',
			'DiagConfirmDT' => 'Дата подтверждения диагноза ЦВК',
			'PersonDispGroup' => 'Группа диспансерного наблюдения',
			'TubBacterialExcretion' => 'Бактериовыделение',
			'TubMethodConfirmBactType' => 'Метод подтверждения бактериовыделения',
			'TubDetectionMethodType' => 'Метод выявления',
			'DrugResistenceTest' => 'Тестирование на ЛУ',
			'IsDestruction' => 'Наличие распада',
			'Comment' => 'Примечания',
			'AddressType' => 'Тип адреса',
			'Rgn' => 'Субъект',
			'SubRgn' => 'Район',
			'TownSocr' => 'Префикс населенного пункта',
			'Town' => 'Населенный пункт',
			'StreetSocr' => 'Префикс улицы',
			'Street' => 'Улица',
			'House' => 'Дом',
			'_1' => 'Литера дома',
			'Corpus' => 'Номер корпуса',
			'Flat' => 'Квартира',
			'Phone' => 'Телефон',
		);
	}

	/**
	 * @param array $data
	 * return array
	 */
	function getDataForXLS($data) {
		$filters = array();
		$params = array();

		$params['Range_begDate'] = $data['Range'][0];
		$params['Range_endDate'] = $data['Range'][1];

		switch($data['ExportType_id']) {
			case 1:	//в регистре
				$filters[] = "PR.PersonRegister_setDate between :Range_begDate and :Range_endDate";
				$filters[] = "(PR.PersonRegister_disDate is null or PR.PersonRegister_disDate not between :Range_begDate and :Range_endDate)";
				break;

			case 2:	//исключенные из регистра
				$filters[] = "PR.PersonRegister_disDate between :Range_begDate and :Range_endDate";
				break;

			case 3:	//все
				$filters[] = "(PR.PersonRegister_setDate between :Range_begDate and :Range_endDate or PR.PersonRegister_disDate between :Range_begDate and :Range_endDate)";
				break;
		}

		if (!empty($data['Lpu_oid'])) {
			$filters[] = "PR.Lpu_iid = :Lpu_oid";
			$params['Lpu_oid'] = $data['Lpu_oid'];
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
				(
					substring(PS.Person_Snils,1,3)||'-'||
					substring(PS.Person_Snils,4,3)||'-'||
					substring(PS.Person_Snils,7,3)||' '||
					substring(PS.Person_Snils,10,2)
				) as \"SNILS\",
				rtrim(PS.Person_SurName) as \"Person_SurName\",
				rtrim(PS.Person_FirName) as \"Person_FirName\",
				rtrim(PS.Person_SecName) as \"Person_SecName\",
				to_char( PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				case
					when Sex.Sex_fedid = 1 then '1-М'
					when Sex.Sex_fedid = 2 then '2-Ж'
				end as \"Sex\",
				cast(SS.SocStatus_id as varchar)||'-'||SS.SocStatus_Name as \"SocStatus\",
				PDG.PersonDecreedGroup_Code||'-'||PDG.PersonDecreedGroup_Name as \"PersonDecreedGroup\",
				OJ.Org_Name as \"OrgJob_Name\",
				cast(DT.DocumentType_Code as varchar)||'-'||DT.DocumentType_Name as \"DocumentType\",
				Doc.Document_Ser as \"Document_Ser\",
				Doc.Document_Num as \"Document_Num\",
				to_char( Doc.Document_begDate, 'dd.mm.yyyy') as \"Document_begDate\",
				OD.OrgDep_Nick as \"OrgDep_Nick\",
				to_char( PR.PersonRegister_setDate, 'dd.mm.yyyy') as \"PersonRegister_setDate\",
				to_char( PR.PersonRegister_disDate, 'dd.mm.yyyy') as \"PersonRegister_disDate\",
				case
					when OC.PersonRegisterOutCause_SysNick like 'OutFromRF' then '1-Выезд за пределы РФ'
					when OC.PersonRegisterOutCause_SysNick like 'Death' then '2-Смерть'
					when OC.PersonRegisterOutCause_SysNick like 'sdisp' then '3-Прекращение диспансерного наблюдения'
					when OC.PersonRegisterOutCause_id is not null then '4-Иное'
				end as \"OutCause\",
				OID.PassportToken_tid as \"OID\",
				cast(PCT.PersonCategoryType_Code as varchar)||'-'||PCT.PersonCategoryType_Name as \"PersonCategoryType\",
				cast(PLF.PersonLivingFacilies_Code as varchar)||'-'||PLF.PersonLivingFacilies_Name as \"PersonLivingFacilies\",
				cast(TDPT.TubDetectionPlaceType_Code as varchar)||'-'||TDPT.TubDetectionPlaceType_Name as \"TubDetectionPlaceType\",
				cast(TDFT.TubDetectionFactType_Code as varchar)||'-'||TDFT.TubDetectionFactType_Name as \"TubDetectionFactType\",
				cast(TRCT.TubRegCrazyType_Code as varchar)||'-'||TRCT.TubRegCrazyType_Name as \"TubRegCrazyType\",
				to_char( coalesce(MO.MorbusTub_FirstDT, ENT.EvnNotifyTub_FirstDT), 'dd.mm.yyyy') as \"FirstDT\",
				to_char( ENT.EvnNotifyTub_RegDT, 'dd.mm.yyyy') as \"RegDT\",
				case 
					when substring(D.Diag_Code,1,3) between 'B20' and 'B24' 
					then 'да' else 'нет'
				end as \"hasB20_B95\",
				case 
					when PT.PrivilegeType_id is not null
					then 'да' else 'нет'
				end as \"IsInvalid\",
				null as \"TubDiagSop\",
				case 
					when ENT.EvnNotifyTub_IsConfirmedDiag = 2
					then 'да' else 'нет'
				end as \"IsConfirmedDiag\",
				to_char( ENT.EvnNotifyTub_DiagConfirmDT, 'dd.mm.yyyy') as \"DiagConfirmDT\",
				cast(PDispG.PersonDispGroup_Code as varchar)||'-'||PDispG.PersonDispGroup_Name as \"PersonDispGroup\",
				cast(TBE.TubBacterialExcretion_Code as varchar)||'-'||TBE.TubBacterialExcretion_Name as \"TubBacterialExcretion\",
				cast(TMCBT.TubMethodConfirmBactType_Code as varchar)||'-'||TMCBT.TubMethodConfirmBactType_Name as \"TubMethodConfirmBactType\",
				cast(TDMT.TubDetectionMethodType_Code as varchar)||'-'||TDMT.TubDetectionMethodType_Name as \"TubDetectionMethodType\",
				cast(DRT.DrugResistenceTest_Code as varchar)||'-'||DRT.DrugResistenceTest_Name as \"DrugResistenceTest\",
				case 
					when ENT.EvnNotifyTub_IsDestruction = 2 
					then 'да' else 'нет'
				end as \"IsDestruction\",
				rtrim(ENT.EvnNotifyTub_Comment) as \"Comment\",
				case
					when A.Address_id = PS.PAddress_id then '2-Адрес места пребывания'
					when A.Address_id = PS.UAddress_id then '2-Адрес места жительства'
				end as \"AddressType\",
				(
					cast(Rgn.KLRgn_id as varchar)||'-'||
					UPPER(LEFT(Rgn.KLRgn_Name,1))||LOWER(SUBSTRING(Rgn.KLRgn_Name,2,length(Rgn.KLRgn_Name)))||' '||
					UPPER(LEFT(RgnSocr.KLSocr_Name,1))||LOWER(SUBSTRING(RgnSocr.KLSocr_Name,2,length(RgnSocr.KLSocr_Name)))
				) as \"Rgn\",
				(
					UPPER(LEFT(SubRgn.KLSubRgn_Name,1))||LOWER(SUBSTRING(SubRgn.KLSubRgn_Name,2,length(SubRgn.KLSubRgn_Name)))
				) as \"SubRgn\",
				case
					when A.KLCity_id is not null then (
						LOWER(CitySocr.KLSocr_Nick)||' - '||
						UPPER(LEFT(CitySocr.KLSocr_Name,1))||LOWER(SUBSTRING(CitySocr.KLSocr_Name,2,length(CitySocr.KLSocr_Name)))
					)
					when A.KLTown_id is not null then (
						LOWER(TownSocr.KLSocr_Nick)||' - '||
						UPPER(LEFT(TownSocr.KLSocr_Name,1))||LOWER(SUBSTRING(TownSocr.KLSocr_Name,2,length(TownSocr.KLSocr_Name)))
					)
				end as \"TownSocr\",
				case
					when A.KLCity_id is not null then (
						UPPER(LEFT(City.KLCity_Name,1))||LOWER(SUBSTRING(City.KLCity_Name,2,length(City.KLCity_Name)))
					)
					when A.KLTown_id is not null then (
						UPPER(LEFT(Town.KLTown_Name,1))||LOWER(SUBSTRING(Town.KLTown_Name,2,length(Town.KLTown_Name)))
					)
				end as \"Town\",
				(
					LOWER(StreetSocr.KLSocr_Nick)||' - '||
					UPPER(LEFT(StreetSocr.KLSocr_Name,1))||LOWER(SUBSTRING(StreetSocr.KLSocr_Name,2,length(StreetSocr.KLSocr_Name)))
				) as \"StreetSocr\",
				(
					UPPER(LEFT(Street.KLStreet_Name,1))||LOWER(SUBSTRING(Street.KLStreet_Name,2,length(Street.KLStreet_Name)))
				) as \"Street\",
				A.Address_House as \"House\",
				A.Address_Corpus as \"Corpus\",
				A.Address_Flat as \"Flat\",
				A.Address_Zip as \"Zip\",
				'+7'||PS.Person_Phone as \"Phone\"
			from
				v_PersonRegister PR
				inner join v_PersonState PS on PS.Person_id = PR.Person_id
				inner join v_MorbusTub MO on MO.Morbus_id = PR.Morbus_id
				left join v_EvnNotifyTub ENT on ENT.Morbus_id = PR.Morbus_id
				left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
				left join nsi.v_SocStatusLink SSL on SSL.SocStatus_did = PS.SocStatus_id
				left join nsi.v_SocStatus SS on SS.SocStatus_id = SSL.SocStatus_nid
				left join v_Job Job on Job.Job_id = PS.Job_id
				left join v_Org OJ on OJ.Org_id = Job.Org_id
				left join v_PersonDecreedGroup PDG on PDG.PersonDecreedGroup_id = coalesce(MO.PersonDecreedGroup_id, ENT.PersonLivingFacilies_id)
				left join v_Document Doc on Doc.Document_id = PS.Document_id
				left join v_DocumentType DT on DT.DocumentType_id = Doc.DocumentType_id
				left join v_OrgDep OD on OD.OrgDep_id = Doc.OrgDep_id
				left join v_PersonRegisterOutCause OC on OC.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_Lpu L on L.Lpu_id = PR.Lpu_iid
				left join fed.v_PassportToken OID on OID.Lpu_id = L.Lpu_id
				left join v_PersonCategoryType PCT on PCT.PersonCategoryType_id = ENT.PersonCategoryType_id
				left join v_PersonLivingFacilies PLF on PLF.PersonLivingFacilies_id = coalesce(MO.PersonLivingFacilies_id, ENT.PersonLivingFacilies_id)
				left join v_PersonDispGroup PDispG on PDispG.PersonDispGroup_id = coalesce(MO.PersonDispGroup_id, ENT.PersonDispGroup_id)
				left join v_TubDetectionPlaceType TDPT on TDPT.TubDetectionPlaceType_id = ENT.TubDetectionPlaceType_id
				left join v_TubDetectionFactType TDFT on TDFT.TubDetectionFactType_id = ENT.TubDetectionFactType_id
				left join v_TubRegCrazyType TRCT on TRCT.TubRegCrazyType_id = ENT.TubRegCrazyType_id
				left join v_Diag D on D.Diag_id = MO.Diag_id
				left join lateral(
					select PT.PrivilegeType_id
					from v_PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
					where PP.Person_id = PS.Person_id and PP.PersonPrivilege_endDate is null
					and PT.PrivilegeType_Code in ('81','82','83')
					order by PP.PersonPrivilege_begDate desc
					limit 1
				) PT on true
				left join v_TubBacterialExcretion TBE on TBE.TubBacterialExcretion_id = ENT.TubBacterialExcretion_id
				left join v_TubMethodConfirmBactType TMCBT on TMCBT.TubMethodConfirmBactType_id = ENT.TubMethodConfirmBactType_id
				left join v_TubDetectionMethodType TDMT on TDMT.TubDetectionMethodType_id = ENT.TubDetectionMethodType_id
				left join v_DrugResistenceTest DRT on DRT.DrugResistenceTest_id = ENT.DrugResistenceTest_id
				left join v_Address A on A.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id)/*PS.UAddress_id*/	-- todo: check
				left join v_KLRgn Rgn on Rgn.KLRgn_id = A.KLRgn_id
				left join v_KLSocr RgnSocr on RgnSocr.KLSocr_id = Rgn.KLSocr_id
				left join v_KLSubRgn SubRgn on SubRgn.KLSubRgn_id = A.KLSubRgn_id
				left join v_KLSocr SubRgnSocr on SubRgnSocr.KLSocr_id = SubRgn.KLSocr_id
				left join v_KLCity City on City.KLCity_id = A.KLCity_id
				left join v_KLSocr CitySocr on CitySocr.KLSocr_id = City.KLSocr_id
				left join v_KLTown Town on Town.KLTown_id = A.KLTown_id
				left join v_KLSocr TownSocr on TownSocr.KLSocr_id = Town.KLSocr_id
				left join v_KLStreet Street on Street.KLStreet_id = A.KLStreet_id
				left join v_KLSocr StreetSocr on StreetSocr.KLSocr_id = Street.KLSocr_id
			where
				{$filters_str}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Создание специфики по туберкулезу. Метод API
	 */
	function saveMorbusTubAPI($data)
	{
		return array('Error_msg' => 'Метод создания специфики по туберкулезу не предусмотрен');
		//$res = $this->saveMorbusSpecific($data);
		//return $res;
	}

	/**
	 * Изменение специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubAPI($data)
	{
		if(empty($data['MorbusTub_id'])) return false;
		if(!empty($data['TubDiagSop_id_Link'])){
			$data['SopDiags'] = $data['TubDiagSop'];
		}
		if(!empty($data['TubDiagSopLink_Descr'])){
			$data['SopDiag_Descr'] = $data['TubDiagSopLink_Descr'];
		}
		if(!empty($data['TubRiskFactorType_id_Link'])){
			$data['RiskTypes'] = $data['TubRiskFactorType_id'];
		}
		$data['Mode'] = 'personregister_viewform';

		$record = $this->getMorbusTubAPI($data);
		$this->entityFields['MorbusTub'][] = 'PersonDecreedGroup_id';
		$this->entityFields['MorbusTub'][] = 'PersonLivingFacilies_id';

		foreach ($data as $i=>$row) {
			if ($row === null)
				unset($data[$i]);
		}
		$ct = 0;
		foreach ($this->entityFields['MorbusTub'] as $key => $value) {
			if(!empty($data[$value])) $ct++;
		}
		if($ct == 0){
			return array('Error_Msg' => 'не переданы параметры изменений');
		}

		if(is_array($record) && count($record)>0){
			$data['Person_id'] = (empty($data['Person_id'])) ? $record[0]['Person_id'] : $data['Person_id'];
			$data['PersonRegister_id'] = (empty($data['PersonRegister_id'])) ? $record[0]['PersonRegister_id'] : $data['PersonRegister_id'];
			$data['Diag_id'] = (empty($data['Diag_id'])) ? $record[0]['Diag_id'] : $data['Diag_id'];
			$data['Evn_pid'] = (empty($data['Evn_pid'])) ? $record[0]['Evn_pid'] : $data['Evn_pid'];
			$data['Morbus_id'] = (empty($data['Morbus_id'])) ? $record[0]['Morbus_id'] : $data['Morbus_id'];
		}
		$result = $this->saveMorbusSpecific($data);
		return $result;
	}

	/**
	 * получение специфики по туберкулезу. Метод API
	 */
	function getMorbusTubAPI($data){
		if(empty($data['MorbusTub_id'])) return false;

		$query = "
			select
				MT.MorbusTub_id as \"MorbusTub_id\",
				PR.PersonRegister_id as \"PersonRegister_id\",
				PR.Person_id as \"Person_id\",
				MT.PersonResidenceType_id as \"PersonResidenceType_id\",
				MT.PersonDecreedGroup_id as \"PersonDecreedGroup_id\",
				MT.PersonLivingFacilies_id as \"PersonLivingFacilies_id\",
				MT.Diag_id as \"Diag_id\",
				MT.TubDiag_id as \"TubDiag_id\",
				(
					select string_agg(TRFTL.TubRiskFactorType_id, ',') 
					from v_TubRiskFactorTypeLink TRFTL
					where TRFTL.MorbusTub_id = MT.MorbusTub_id
				) as \"TubRiskFactorType_id_list\",
				(
					select string_agg(TDSL.TubDiagSop_id, ',') 
					from v_TubDiagSopLink TDSL
					where TDSL.MorbusTub_id = MT.MorbusTub_id AND TDSL.TubDiagSop_id is not null
				) as \"TubDiagSop_id_list\",
				(
					select string_agg(TDSL.TubDiagSopLink_Descr, ',') 
					from v_TubDiagSopLink TDSL
					where TDSL.MorbusTub_id = MT.MorbusTub_id AND TDSL.TubDiagSopLink_Descr is not null
				) as \"TubDiagSopLink_Descr_list\",
				MT.TubSickGroupType_id as \"TubSickGroupType_id\",
				to_char(MT.MorbusTub_begDT, 'dd.mm.yyyy') as \"MorbusTub_begDT\",
				to_char(MT.MorbusTub_FirstDT, 'dd.mm.yyyy') as \"MorbusTub_FirstDT\",
				to_char(MT.MorbusTub_DiagDT, 'dd.mm.yyyy') as \"MorbusTub_DiagDT\",
				MT.TubResultChemType_id as \"TubResultChemType_id\",
				to_char(MT.MorbusTub_ResultDT, 'dd.mm.yyyy') as \"MorbusTub_ResultDT\",
				MT.TubResultDeathType_id as \"TubResultDeathType_id\",
				to_char(MT.MorbusTub_deadDT, 'dd.mm.yyyy') as \"MorbusTub_deadDT\",
				to_char(MT.MorbusTub_breakDT, 'dd.mm.yyyy') as \"MorbusTub_breakDT\",
				MT.TubBreakChemType_id as \"TubBreakChemType_id\",
				MT.MorbusTub_disDT as \"MorbusTub_disDT\",
				MT.PersonDispGroup_id as \"PersonDispGroup_id\",
				to_char(MT.MorbusTub_ConvDT, 'dd.mm.yyyy') as \"MorbusTub_ConvDT\",
				to_char(MT.MorbusTub_unsetDT, 'dd.mm.yyyy') as \"MorbusTub_unsetDT\",
				MT.MorbusTub_CountDay as \"MorbusTub_CountDay\",
				MT.Evn_pid as \"Evn_pid\",
				MT.Morbus_id as \"Morbus_id\"
			from v_MorbusTub MT
				left join v_PersonRegister PR on PR.Morbus_id = MT.Morbus_id
				--left join v_TubDiagSopLink TDSL on TDSL.MorbusTub_id = MT.MorbusTub_id
				--left join v_TubRiskFactorTypeLink TRFTL on TRFTL.MorbusTub_id = MT.MorbusTub_id
			WHERE 1=1
				AND MT.MorbusTub_id = :MorbusTub_id
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение записи Генерализированные формы в рамках специфики по туберкулезу. Метод API
	 */
	function updateTubDiagGeneralFormAPI($data){
		if(empty($data['TubDiagGeneralForm_id'])) return false;
		$query = '
			SELECT
				TubDiagGeneralForm_id as "TubDiagGeneralForm_id",
				Diag_id as "Diag_id",
				TubDiagGeneralForm_setDT as "TubDiagGeneralForm_setDT",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				TubDiagGeneralForm_insDT as "TubDiagGeneralForm_insDT",
				TubDiagGeneralForm_updDT as "TubDiagGeneralForm_updDT",
				MorbusTub_id as "MorbusTub_id"
			FROM v_TubDiagGeneralForm
			WHERE TubDiagGeneralForm_id = :TubDiagGeneralForm_id';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['TubDiagGeneralForm_id'])){
			return array('Error_Msg' => 'запись Генерализированные формы не найдена');
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}
		return $this->saveTubDiagGeneralForm($data);
	}

	/**
	 * Генерализованные формы. Метод для API
	 */
	function getMorbusTubDiagGeneralFormAPI($data){
		if(empty($data['MorbusTub_id']) && empty($data['TubDiagGeneralForm_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MM.MorbusTub_id = :MorbusTub_id';
		}
		if(!empty($data['TubDiagGeneralForm_id'])){
			$where .= ' AND MM.TubDiagGeneralForm_id = :TubDiagGeneralForm_id';
		}
		$query = "
			select
				MM.TubDiagGeneralForm_id as \"TubDiagGeneralForm_id\",
				MM.MorbusTub_id as \"MorbusTub_id\",
				MM.Diag_id as \"Diag_id\",
				to_char(MM.TubDiagGeneralForm_setDT, 'dd.mm.yyyy') as \"TubDiagGeneralForm_setDT\"
			from
				v_TubDiagGeneralForm MM
			WHERE 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение записи режима химиотерапии в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubConditChemAPI($data){
		if(empty($data['MorbusTubConditChem_id'])) return false;
		$query = '
			SELECT
				MorbusTubConditChem_id as "MorbusTubConditChem_id",
				MorbusTub_id as "MorbusTub_id",
				TubStandartConditChemType_id as "TubStandartConditChemType_id",
				TubTreatmentChemType_id as "TubTreatmentChemType_id",
				TubStageChemType_id as "TubStageChemType_id",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubConditChem_insDT as "MorbusTubConditChem_insDT",
				MorbusTubConditChem_updDT as "MorbusTubConditChem_updDT",
				TubVenueType_id as "TubVenueType_id",
				MorbusTubConditChem_BegDate as "MorbusTubConditChem_BegDate",
				MorbusTubConditChem_EndDate as "MorbusTubConditChem_EndDate"
			FROM v_MorbusTubConditChem
			WHERE MorbusTubConditChem_id = :MorbusTubConditChem_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubConditChem_id'])){
			return array('Error_Msg' => 'не найдена запись режима химиотерапии в рамках специфики по туберкулезу');
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}
		return $this->saveMorbusTubConditChem($data);
	}

	/**
	 * Получение режима химиотерапии в рамках специфики по туберкулезу. Метод для API
	 */
	function getMorbusTubConditChemAPI($data){
		if(empty($data['MorbusTub_id']) && empty($data['MorbusTubConditChem_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MM.MorbusTub_id = :MorbusTub_id';
		}
		if(!empty($data['MorbusTubConditChem_id'])){
			$where .= ' AND MM.MorbusTubConditChem_id = :MorbusTubConditChem_id';
		}
		$query = "
			select 
				MM.MorbusTubConditChem_id as \"MorbusTubConditChem_id\",
				MM.MorbusTub_id as \"MorbusTub_id\",
				MM.TubStandartConditChemType_id as \"TubStandartConditChemType_id\",
				MM.TubTreatmentChemType_id as \"TubTreatmentChemType_id\",
				MM.TubStageChemType_id as \"TubStageChemType_id\",
				MM.TubVenueType_id as \"TubVenueType_id\",
				to_char(MM.MorbusTubConditChem_BegDate, 'dd.mm.yyyy') as \"MorbusTubConditChem_BegDate\",
				to_char(MM.MorbusTubConditChem_EndDate, 'dd.mm.yyyy') as \"MorbusTubConditChem_EndDate\"
			from
				v_MorbusTubConditChem MM
			WHERE 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение консультации фтизиохирурга в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubAdviceAPI($data){
		if(empty($data['MorbusTubAdvice_id'])) return false;
		$query = '
			SELECT
				MorbusTubAdvice_id as "MorbusTubAdvice_id",
				MorbusTub_id as "MorbusTub_id",
				MorbusTubAdvice_setDT as "MorbusTubAdvice_setDT",
				TubAdviceResultType_id as "TubAdviceResultType_id",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubAdvice_insDT as "MorbusTubAdvice_insDT",
				MorbusTubAdvice_updDT as "MorbusTubAdvice_updDT"
			FROM v_MorbusTubAdvice
			WHERE MorbusTubAdvice_id = :MorbusTubAdvice_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubAdvice_id'])){
			return array('Error_Msg' => 'не найдена запись консультации фтизиохирурга в рамках специфики по туберкулезу');
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}
		return $this->saveMorbusTubAdvice($data);
	}

	/**
	 * Получение консультации фтизиохирурга в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubAdviceAPI($data){
		if(empty($data['MorbusTub_id']) && empty($data['MorbusTubAdvice_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MM.MorbusTub_id = :MorbusTub_id';
		}
		if(!empty($data['MorbusTubAdvice_id'])){
			$where .= ' AND MM.MorbusTubAdvice_id = :MorbusTubAdvice_id';
		}
		$query = "
			select
				MorbusTubAdvice_id as \"MorbusTubAdvice_id\",
				MorbusTub_id as \"MorbusTub_id\",
				to_char(MM.MorbusTubAdvice_setDT, 'dd.mm.yyyy') as \"MorbusTubAdvice_setDT\",
				TubAdviceResultType_id as \"TubAdviceResultType_id\"
			from
				v_MorbusTubAdvice MM
			WHERE 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение консультации фтизиохирурга в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubAdviceOperAPI($data){
		if(empty($data['MorbusTubAdviceOper_id'])) return false;
		$query = '
			SELECT
				MorbusTubAdviceOper_id as "MorbusTubAdviceOper_id",
				MorbusTubAdvice_id as "MorbusTubAdvice_id",
				MorbusTubAdviceOper_setDT as "MorbusTubAdviceOper_setDT",
				UslugaComplex_id as "UslugaComplex_id",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubAdviceOper_insDT as "MorbusTubAdviceOper_insDT",
				MorbusTubAdviceOper_updDT as "MorbusTubAdviceOper_updDT"
			FROM v_MorbusTubAdviceOper
			WHERE MorbusTubAdviceOper_id = :MorbusTubAdviceOper_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubAdviceOper_id'])){
			return array('Error_Msg' => 'не найдена запись консультации фтизиохирурга в рамках специфики по туберкулезу');
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}
		return $this->saveMorbusTubAdviceOper($data);
	}

	/**
	 * Получение консультации фтизиохирурга в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubAdviceOperAPI($data){
		if(empty($data['MorbusTub_id']) && empty($data['MorbusTubAdviceOper_id']) && empty($data['MorbusTubAdvice_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTubAdvice_id'])){
			$where .= ' AND MM.MorbusTubAdvice_id = :MorbusTubAdvice_id';
		}
		if(!empty($data['MorbusTubAdviceOper_id'])){
			$where .= ' AND MM.MorbusTubAdviceOper_id = :MorbusTubAdviceOper_id';
		}
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MM.MorbusTub_id = :MorbusTub_id';
		}
		$query = "
			select
				MM.MorbusTubAdviceOper_id as \"MorbusTubAdviceOper_id\",
				MM.MorbusTubAdvice_id as \"MorbusTubAdvice_id\",
				to_char(MM.MorbusTubAdviceOper_setDT, 'dd.mm.yyyy') as \"MorbusTubAdviceOper_setDT\",
				MM.UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_MorbusTubAdviceOper MM
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Создание направления на проведение микроскопических исследований на туберкулез. Метод API
	 */
	function saveEvnDirectionTubAPI($data){
		//нужен PersonEvn_id для процедуры
		//не понятно --- ДОДЕЛАТЬ
		$query = "
			SELECT 
				 PS.PersonEvn_id as \"PersonEvn_id\"
				,PS.Person_id as \"Person_id\"
				,PS.Server_id as \"Server_id\"
			FROM 
				v_PersonState PS						
				inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = 'tub'
				inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
				left join v_EvnNotifyTub EN on EN.EvnNotifyTub_id = PR.EvnNotifyBase_id
				left join v_MorbusTub MO on MO.Morbus_id = coalesce(EN.Morbus_id,PR.Morbus_id)
			WHERE MO.MorbusTub_id = :MorbusTub_id";
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['PersonEvn_id'])){
			return array(array('Error_Msg' => 'Не найдены данные по состоянию человека'));
		}
		$data['PersonEvn_id'] = (!empty($res['PersonEvn_id'])) ? $res['PersonEvn_id'] : null;
		$data['Server_id'] = (!empty($res['Server_id'])) ? $res['Server_id'] : 0;  //без этого процедура не работает
		return $this->saveEvnDirectionTub($data);
	}

	/**
	 * Изменение записи направления на проведение микроскопических исследований на туберкулез. Метод API
	 */
	function updateEvnDirectionTubAPI($data){
		if(empty($data['EvnDirectionTub_id'])) return false;
		$query = '
			SELECT
				EvnClass_id as "EvnClass_id",
				EvnClass_Name as "EvnClass_Name",
				EvnDirectionTub_id as "EvnDirectionTub_id",
				EvnDirectionTub_setDate as "EvnDirectionTub_setDate",
				EvnDirectionTub_setTime as "EvnDirectionTub_setTime",
				EvnDirectionTub_didDate as "EvnDirectionTub_didDate",
				EvnDirectionTub_didTime as "EvnDirectionTub_didTime",
				EvnDirectionTub_disDate as "EvnDirectionTub_disDate",
				EvnDirectionTub_disTime as "EvnDirectionTub_disTime",
				EvnDirectionTub_pid as "EvnDirectionTub_pid",
				EvnDirectionTub_rid as "EvnDirectionTub_rid",
				Lpu_id as "Lpu_id",
				Server_id as "Server_id",
				PersonEvn_id as "PersonEvn_id",
				EvnDirectionTub_setDT as "EvnDirectionTub_setDT",
				EvnDirectionTub_disDT as "EvnDirectionTub_disDT",
				EvnDirectionTub_didDT as "EvnDirectionTub_didDT",
				EvnDirectionTub_insDT as "EvnDirectionTub_insDT",
				EvnDirectionTub_updDT as "EvnDirectionTub_updDT",
				EvnDirectionTub_Index as "EvnDirectionTub_Index",
				EvnDirectionTub_Count as "EvnDirectionTub_Count",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				Person_id as "Person_id",
				Morbus_id as "Morbus_id",
				EvnDirectionTub_IsSigned as "EvnDirectionTub_IsSigned",
				pmUser_signID as "pmUser_signID",
				EvnDirectionTub_signDT as "EvnDirectionTub_signDT",
				EvnDirectionTub_IsArchive as "EvnDirectionTub_IsArchive",
				EvnDirectionTub_Guid as "EvnDirectionTub_Guid",
				EvnDirectionTub_IndexMinusOne as "EvnDirectionTub_IndexMinusOne",
				EvnStatus_id as "EvnStatus_id",
				EvnDirectionTub_statusDate as "EvnDirectionTub_statusDate",
				EvnDirectionTub_IsTransit as "EvnDirectionTub_IsTransit",
				DirType_id as "DirType_id",
				Diag_id as "Diag_id",
				EvnDirectionTub_Num as "EvnDirectionTub_Num",
				EvnDirectionTub_Descr as "EvnDirectionTub_Descr",
				LpuSection_id as "LpuSection_id",
				MedPersonal_id as "MedPersonal_id",
				MedPersonal_zid as "MedPersonal_zid",
				LpuSectionProfile_id as "LpuSectionProfile_id",
				PrehospType_did as "PrehospType_did",
				Lpu_did as "Lpu_did",
				LpuUnit_did as "LpuUnit_did",
				LpuSection_did as "LpuSection_did",
				MedPersonal_did as "MedPersonal_did",
				TimeTableGraf_id as "TimeTableGraf_id",
				TimeTableStac_id as "TimeTableStac_id",
				TimeTablePar_id as "TimeTablePar_id",
				DirFailType_id as "DirFailType_id",
				pmUser_failID as "pmUser_failID",
				EvnDirectionTub_failDT as "EvnDirectionTub_failDT",
				EvnDirectionTub_Ser as "EvnDirectionTub_Ser",
				EvnDirectionTub_IsConfirmed as "EvnDirectionTub_IsConfirmed",
				pmUser_confID as "pmUser_confID",
				EvnDirectionTub_confDT as "EvnDirectionTub_confDT",
				EvnDirectionTub_IsAuto as "EvnDirectionTub_IsAuto",
				EvnQueue_id as "EvnQueue_id",
				TimeTableMedService_id as "TimeTableMedService_id",
				EvnDirectionTub_IsCito as "EvnDirectionTub_IsCito",
				Org_sid as "Org_sid",
				Lpu_sid as "Lpu_sid",
				PrehospDirect_id as "PrehospDirect_id",
				MedService_id as "MedService_id",
				Post_id as "Post_id",
				EvnDirectionTub_desDT as "EvnDirectionTub_desDT",
				EvnDirectionTub_IsReceive as "EvnDirectionTub_IsReceive",
				PayType_id as "PayType_id",
				RemoteConsultCause_id as "RemoteConsultCause_id",
				TimeTableResource_id as "TimeTableResource_id",
				EvnDirectionTub_IsNeedOper as "EvnDirectionTub_IsNeedOper",
				ARMType_id as "ARMType_id",
				EvnCourse_id as "EvnCourse_id",
				MedStaffFact_id as "MedStaffFact_id",
				Resource_id as "Resource_id",
				DopDispInfoConsent_id as "DopDispInfoConsent_id",
				LpuUnitType_id as "LpuUnitType_id",
				MedSpec_fid as "MedSpec_fid",
				UslugaComplex_did as "UslugaComplex_did",
				EvnDirectionTub_TalonCode as "EvnDirectionTub_TalonCode",
				StudyTarget_id as "StudyTarget_id",
				MedicalCareFormType_id as "MedicalCareFormType_id",
				MedPersonal_Code as "MedPersonal_Code",
				ConsultingForm_id as "ConsultingForm_id",
				MedStaffFact_fid as "MedStaffFact_fid",
				Lpu_cid as "Lpu_cid",
				RecMethodType_id as "RecMethodType_id",
				EvnDirectionTub_ferSendDT as "EvnDirectionTub_ferSendDT",
				Org_oid as "Org_oid",
				ConsultationForm_id as "ConsultationForm_id",
				EvnDirectionTub_isBrowsed as "EvnDirectionTub_isBrowsed",
				EvnDirectionTub_PersonRegNum as "EvnDirectionTub_PersonRegNum",
				TubDiagnosticMaterialType_id as "TubDiagnosticMaterialType_id",
				EvnDirectionTub_OtherMeterial as "EvnDirectionTub_OtherMeterial",
				TubTargetStudyType_id as "TubTargetStudyType_id",
				EvnDirectionTub_NumLab as "EvnDirectionTub_NumLab",
				MedPersonal_lid as "MedPersonal_lid",
				EvnDirectionTub_ResDT as "EvnDirectionTub_ResDT",
				MorbusTub_id as "MorbusTub_id"
			FROM v_EvnDirectionTub
			WHERE EvnDirectionTub_id = :EvnDirectionTub_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['EvnDirectionTub_id'])){
			return array('Error_Msg' => 'не найдена запись направления на проведение микроскопических исследований на туберкулез');
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}
		return $this->saveEvnDirectionTub($data);
	}

	/**
	 * Получение записи направления на проведение микроскопических исследований на туберкулез. Метод API
	 */
	function getEvnDirectionTubAPI($data){
		if(empty($data['MorbusTub_id']) && empty($data['EvnDirectionTub_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MM.MorbusTub_id = :MorbusTub_id';
		}
		if(!empty($data['EvnDirectionTub_id'])){
			$where .= ' AND MM.EvnDirectionTub_id = :EvnDirectionTub_id';
		}
		$query = "
			SELECT
				MM.EvnDirectionTub_id as \"EvnDirectionTub_id\",
				MM.MorbusTub_id as \"MorbusTub_id\",
				to_char(MM.EvnDirectionTub_setDT, 'dd.mm.yyyy') as \"EvnDirectionTub_setDT\",
				MM.TubDiagnosticMaterialType_id as \"TubDiagnosticMaterialType_id\",
				MM.TubTargetStudyType_id as \"TubTargetStudyType_id\",
				MM.EvnDirectionTub_PersonRegNum as \"EvnDirectionTub_PersonRegNum\",
				MM.MedPersonal_id as \"MedPersonal_id\",
				MM.MedPersonal_lid as \"MedPersonal_lid\",
				MM.EvnDirectionTub_NumLab as \"EvnDirectionTub_NumLab\",
				to_char(MM.EvnDirectionTub_ResDT, 'dd.mm.yyyy') as \"EvnDirectionTub_ResDT\"
			FROM v_EvnDirectionTub MM
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение результатов микроскопических исследований в рамках специфики по туберкулезу. Метод API
	 */
	function updateTubMicrosResultAPI($data){
		if(empty($data['TubMicrosResult_id'])) return false;
		$query = '
			SELECT
				TubMicrosResult_id as "TubMicrosResult_id",
				EvnDirectionTub_id as "EvnDirectionTub_id",
				TubMicrosResult_Num as "TubMicrosResult_Num",
				TubMicrosResult_setDT as "TubMicrosResult_setDT",
				TubMicrosResult_MicrosDT as "TubMicrosResult_MicrosDT",
				TubMicrosResultType_id as "TubMicrosResultType_id",
				TubMicrosResult_EdResult as "TubMicrosResult_EdResult",
				TubMicrosResult_Comment as "TubMicrosResult_Comment",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				TubMicrosResult_insDT as "TubMicrosResult_insDT",
				TubMicrosResult_updDT as "TubMicrosResult_updDT"
			FROM v_TubMicrosResult
			WHERE TubMicrosResult_id = :TubMicrosResult_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['TubMicrosResult_id'])){
			return array('Error_Msg' => 'не найдена запись направления на проведение микроскопических исследований на туберкулез');
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}
		return $this->saveTubMicrosResult($data);
	}

	/**
	 * Получение результатов микроскопических исследований в рамках специфики по туберкулезу. Метод API
	 */
	function getTubMicrosResultAPI($data){
		if(empty($data['TubMicrosResult_id']) && empty($data['EvnDirectionTub_id'])) return false;
		$where = '';
		if(!empty($data['TubMicrosResult_id'])){
			$where .= ' AND TubMicrosResult_id = :TubMicrosResult_id';
		}
		if(!empty($data['EvnDirectionTub_id'])){
			$where .= ' AND EvnDirectionTub_id = :EvnDirectionTub_id';
		}
		$query = "
			SELECT 
				EvnDirectionTub_id as \"EvnDirectionTub_id\",
				TubMicrosResult_id as \"TubMicrosResult_id\",
				to_char( TubMicrosResult_MicrosDT, 'dd.mm.yyyy') as \"TubMicrosResult_MicrosDT\",
				TubMicrosResult_Num as \"TubMicrosResult_Num\",
				to_char(TubMicrosResult_setDT, 'dd.mm.yyyy') as \"TubMicrosResult_setDT\",
				TubMicrosResultType_id as \"TubMicrosResultType_id\",
				TubMicrosResult_EdResult as \"TubMicrosResult_EdResult\"
			FROM TubMicrosResult 
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение лекарственных назначений в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubPrescrAPI($data){
		if(empty($data['MorbusTubPrescr_id'])) return false;
		$query = '
			SELECT
				MorbusTubPrescr_id as "MorbusTubPrescr_id",
				MorbusTub_id as "MorbusTub_id",
				MorbusTubPrescr_setDT as "MorbusTubPrescr_setDT",
				TubDrug_id as "TubDrug_id",
				MorbusTubPrescr_DoseDay as "MorbusTubPrescr_DoseDay",
				TubStageChemType_id as "TubStageChemType_id",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubPrescr_insDT as "MorbusTubPrescr_insDT",
				MorbusTubPrescr_updDT as "MorbusTubPrescr_updDT",
				MorbusTubPrescr_Schema as "MorbusTubPrescr_Schema",
				MorbusTubPrescr_DoseTotal as "MorbusTubPrescr_DoseTotal",
				MorbusTubPrescr_endDate as "MorbusTubPrescr_endDate",
				MorbusTubMDR_id as "MorbusTubMDR_id",
				MorbusTubPrescr_DoseMiss as "MorbusTubPrescr_DoseMiss",
				MorbusTubPrescr_SetDay as "MorbusTubPrescr_SetDay",
				MorbusTubPrescr_MissDay as "MorbusTubPrescr_MissDay",
				PersonWeight_id as "PersonWeight_id",
				Lpu_id as "Lpu_id",
				LpuSection_id as "LpuSection_id",
				MorbusTubPrescr_Comment as "MorbusTubPrescr_Comment",
				Drug_id as "Drug_id"
			FROM v_MorbusTubPrescr
			WHERE MorbusTubPrescr_id = :MorbusTubPrescr_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubPrescr_id'])){
			return array('Error_Msg' => 'не найдена запись лекарственных назначений в рамках специфики по туберкулезу');
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}
		return $this->saveMorbusTubPrescr($data);
	}

	/**
	 * Получение лекарственных назначений в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubPrescrAPI($data){
		if(empty($data['MorbusTubPrescr_id']) && empty($data['MorbusTub_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTubPrescr_id'])){
			$where .= ' AND MorbusTubPrescr_id = :MorbusTubPrescr_id';
		}
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MorbusTub_id = :MorbusTub_id';
		}
		$query = "
			SELECT 
				MorbusTub_id as \"MorbusTub_id\",
				MorbusTubPrescr_id as \"MorbusTubPrescr_id\",
				TubStageChemType_id as \"TubStageChemType_id\",
				to_char(MorbusTubPrescr_setDT, 'dd.mm.yyyy') as \"MorbusTubPrescr_setDT\",
				to_char(MorbusTubPrescr_endDate, 'dd.mm.yyyy') as \"MorbusTubPrescr_endDate\",
				TubDrug_id as \"TubDrug_id\",
				Drug_id as \"Drug_id\",
				MorbusTubPrescr_DoseDay as \"MorbusTubPrescr_DoseDay\",
				MorbusTubPrescr_Schema as \"MorbusTubPrescr_Schema\",
				MorbusTubPrescr_DoseTotal as \"MorbusTubPrescr_DoseTotal\"
			FROM MorbusTubPrescr 
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение графика исполнения назначения процедур в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubPrescrTimetableAPI($data){
		if(empty($data['MorbusTubPrescrTimetable_id'])) return false;
		$query = '
			SELECT
				MorbusTubPrescrTimeTable_id as "MorbusTubPrescrTimeTable_id",
				MorbusTubPrescr_id as "MorbusTubPrescr_id",
				MorbusTubPrescrTimeTable_setDT as "MorbusTubPrescrTimeTable_setDT",
				MorbusTubPrescrTimeTable_IsExec as "MorbusTubPrescrTimeTable_IsExec",
				MedPersonal_id as "MedPersonal_id",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubPrescrTimeTable_insDT as "MorbusTubPrescrTimeTable_insDT",
				MorbusTubPrescrTimeTable_updDT as "MorbusTubPrescrTimeTable_updDT"
			FROM v_MorbusTubPrescrTimetable
			WHERE MorbusTubPrescrTimetable_id = :MorbusTubPrescrTimetable_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubPrescrTimeTable_id'])){
			return array('Error_Msg' => 'не найдена запись графика исполнения назначения процедур в рамках специфики по туберкулезу');
		}
		$data['MorbusTubPrescrTimeTable_IsExec'] = $data['MorbusTubPrescrTimetable_IsExec'];
		$data['MorbusTubPrescrTimeTable_setDT'] = $data['MorbusTubPrescrTimetable_setDT'];
		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}
		$data['MorbusTubPrescrTimetable_IsExec'] = $data['MorbusTubPrescrTimeTable_IsExec'];
		$data['MorbusTubPrescrTimetable_setDT'] = $data['MorbusTubPrescrTimeTable_setDT'];

		return $this->saveMorbusTubPrescrTimetable($data);
	}

	/**
	 * Получение графика исполнения назначения процедур в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubPrescrTimetableAPI($data){
		if(empty($data['MorbusTubPrescrTimetable_id']) && empty($data['MorbusTubPrescr_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTubPrescrTimetable_id'])){
			$where .= ' AND MorbusTubPrescrTimetable_id = :MorbusTubPrescrTimetable_id';
		}
		if(!empty($data['MorbusTubPrescr_id'])){
			$where .= ' AND MorbusTubPrescr_id = :MorbusTubPrescr_id';
		}
		$query = "
			SELECT
				MorbusTubPrescrTimetable_id as \"MorbusTubPrescrTimetable_id\",
				MorbusTubPrescr_id as \"MorbusTubPrescr_id\",
				MorbusTubPrescrTimetable_IsExec as \"MorbusTubPrescrTimetable_IsExec\",
				MedPersonal_id as \"MedPersonal_id\"
			FROM MorbusTubPrescrTimetable
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение результатов исследования в рамках специфики по туберкулезу. Метод API
	 */
	function saveMorbusTubStudyResultAPI($data){
		if(!empty($data['MorbusTubStudyResult_id'])){
			$query = '
				SELECT
					MorbusTubStudyResult_id as "MorbusTubStudyResult_id",
					MorbusTub_id as "MorbusTub_id",
					TubStageChemType_id as "TubStageChemType_id",
					PersonWeight_id as "PersonWeight_id",
					pmUser_insID as "pmUser_insID",
					pmUser_updID as "pmUser_updID",
					MorbusTubStudyResult_insDT as "MorbusTubStudyResult_insDT",
					MorbusTubStudyResult_updDT as "MorbusTubStudyResult_updDT",
					MorbusTubMDR_id as "MorbusTubMDR_id"
				FROM v_MorbusTubStudyResult
				WHERE MorbusTubStudyResult_id = :MorbusTubStudyResult_id
			';
			$res = $this->getFirstRowFromQuery($query, $data);
			if(empty($res['MorbusTubStudyResult_id'])){
				return array(array('Error_Msg' => 'не найдена запись результатов исследования в рамках специфики по туберкулезу'));
			}
			if(empty($data['MorbusTub_id'])) $data['MorbusTub_id'] = $res['MorbusTub_id'];
		}

		//получим пациента
		$query = '
			SELECT 
				M.Person_id as "Person_id"
				,PW.PersonWeight_id as "PersonWeight_id"
			FROM v_Morbus M
				left join v_MorbusTub MT on M.Morbus_id = MT.Morbus_id
				left join v_PersonWeight PW on PW.Person_id = M.Person_id
			WHERE MT.MorbusTub_id = :MorbusTub_id';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['Person_id'])){
			return array(array('Error_Msg' => 'не найдена запись пациента'));
		}
		if(!empty($data['PersonWeight_id']) && $data['PersonWeight_id'] != $res['PersonWeight_id']){
			return array(array('Error_Msg' => 'дентификатор веса не соответствует человеку'));
		}
		$data['Person_id'] = $res['Person_id'];
		$data['PersonWeight_id'] = (!empty($res['PersonWeight_id'])) ? $res['PersonWeight_id'] : null;

		if(!empty($data['MorbusTubStudyResult_id'])){
			foreach ($data as $key => $value) {
				if(empty($data[$key]) && !empty($res[$key])){
					$data[$key] = $res[$key];
				}
			}
		}

		return $this->saveMorbusTubStudyResult($data);
	}

	/**
	 * Получение результатов исследования в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubStudyResultAPI($data){
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTub_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTubStudyResult_id'])){
			$where .= ' AND MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id';
		}
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MM.MorbusTub_id = :MorbusTub_id';
		}
		$query = "
			SELECT
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				MM.MorbusTub_id as \"MorbusTub_id\",
				PW.PersonWeight_id as \"PersonWeight_id\",
				PW.PersonWeight_Weight as \"PersonWeight_Weight\",
				MM.TubStageChemType_id as \"TubStageChemType_id\"
			FROM v_MorbusTubStudyResult MM
				left join v_PersonWeight PW on PW.PersonWeight_id = MM.PersonWeight_id
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение результатов тестов на лекарственную чувствительность в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubStudyDrugResultAPI($data){
		$query = '
			SELECT
				MorbusTubStudyDrugResult_id as "MorbusTubStudyDrugResult_id",
				MorbusTubStudyResult_id as "MorbusTubStudyResult_id",
				MorbusTubStudyDrugResult_setDT as "MorbusTubStudyDrugResult_setDT",
				TubDrug_id as "TubDrug_id",
				MorbusTubStudyDrugResult_IsResult as "MorbusTubStudyDrugResult_IsResult",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubStudyDrugResult_insDT as "MorbusTubStudyDrugResult_insDT",
				MorbusTubStudyDrugResult_updDT as "MorbusTubStudyDrugResult_updDT"
			FROM v_MorbusTubStudyDrugResult
			WHERE MorbusTubStudyDrugResult_id = :MorbusTubStudyDrugResult_id';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubStudyDrugResult_id'])){
			return array(array('Error_Msg' => 'не найдена запись результатов тестов на лекарственную чувствительность в рамках специфики по туберкулезу'));
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}

		return $this->saveMorbusTubStudyDrugResult($data);
	}

	/**
	 * Получение результатов тестов на лекарственную чувствительность в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubStudyDrugResultAPI($data){
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubStudyDrugResult_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTubStudyResult_id'])){
			$where .= ' AND MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id';
		}
		if(!empty($data['MorbusTubStudyDrugResult_id'])){
			$where .= ' AND MM.MorbusTubStudyDrugResult_id = :MorbusTubStudyDrugResult_id';
		}
		$query = "
			SELECT
				MT.MorbusTub_id as \"MorbusTub_id\",
				MM.MorbusTubStudyDrugResult_id as \"MorbusTubStudyDrugResult_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				MM.TubDrug_id as \"TubDrug_id\",
				MM.MorbusTubStudyDrugResult_IsResult as \"MorbusTubStudyDrugResult_IsResult\"
			FROM v_MorbusTubStudyDrugResult MM
				left join MorbusTubStudyResult MT on MT.MorbusTubStudyResult_id = MM.MorbusTubStudyResult_id
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение молекулярно–генетические методов в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubMolecularAPI($data){
		$query = '
			SELECT
				MorbusTubMolecular_id as "MorbusTubMolecular_id",
				MorbusTubStudyResult_id as "MorbusTubStudyResult_id",
				MorbusTubMolecular_setDT as "MorbusTubMolecular_setDT",
				MorbusTubMolecularType_id as "MorbusTubMolecularType_id",
				MorbusTubMolecular_IsResult as "MorbusTubMolecular_IsResult",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubMolecular_insDT as "MorbusTubMolecular_insDT",
				MorbusTubMolecular_updDT as "MorbusTubMolecular_updDT"
			FROM v_MorbusTubMolecular
			WHERE MorbusTubMolecular_id = :MorbusTubMolecular_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubMolecular_id'])){
			return array(array('Error_Msg' => 'не найдена запись молекулярно–генетических методов в рамках специфики по туберкулезу'));
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}

		return $this->saveMorbusTubMolecular($data);
	}

	/**
	 * Получение молекулярно–генетические методов в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubMolecularAPI($data){
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubMolecular_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTubStudyResult_id'])){
			$where .= ' AND MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id';
		}
		if(!empty($data['MorbusTubMolecular_id'])){
			$where .= ' AND MM.MorbusTubMolecular_id = :MorbusTubMolecular_id';
		}
		$query = "
			SELECT 
				MT.MorbusTub_id as \"MorbusTub_id\",
				MM.MorbusTubMolecular_id as \"MorbusTubMolecular_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				MM.MorbusTubMolecular_IsResult as \"MorbusTubMolecular_IsResult\",
				to_char( MM.MorbusTubMolecular_setDT, 'dd.mm.yyyy') as \"MorbusTubMolecular_setDT\",
				MM.MorbusTubMolecularType_id as \"MorbusTubMolecularType_id\"
			FROM v_MorbusTubMolecular MM
				left join MorbusTubStudyResult MT on MT.MorbusTubStudyResult_id = MM.MorbusTubStudyResult_id
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение микроскопии в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubStudyMicrosResultAPI($data){
		$query = '
			SELECT
				MorbusTubStudyMicrosResult_id as "MorbusTubStudyMicrosResult_id",
				MorbusTubStudyResult_id as "MorbusTubStudyResult_id",
				MorbusTubStudyMicrosResult_NumLab as "MorbusTubStudyMicrosResult_NumLab",
				MorbusTubStudyMicrosResult_setDT as "MorbusTubStudyMicrosResult_setDT",
				TubMicrosResultType_id as "TubMicrosResultType_id",
				MorbusTubStudyMicrosResult_EdResult as "MorbusTubStudyMicrosResult_EdResult",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubStudyMicrosResult_insDT as "MorbusTubStudyMicrosResult_insDT",
				MorbusTubStudyMicrosResult_updDT as "MorbusTubStudyMicrosResult_updDT"
			FROM v_MorbusTubStudyMicrosResult
			WHERE MorbusTubStudyMicrosResult_id = :MorbusTubStudyMicrosResult_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubStudyMicrosResult_id'])){
			return array(array('Error_Msg' => 'не найдена запись микроскопии в рамках специфики по туберкулезу'));
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}

		return $this->saveMorbusTubStudyMicrosResult($data);
	}

	/**
	 * Получение микроскопии в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubStudyMicrosResultAPI($data){
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubStudyMicrosResult_id']) && empty($data['MorbusTub_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTubStudyResult_id'])){
			$where .= ' AND MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id';
		}
		if(!empty($data['MorbusTubStudyMicrosResult_id'])){
			$where .= ' AND MM.MorbusTubStudyMicrosResult_id = :MorbusTubStudyMicrosResult_id';
		}
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MT.MorbusTub_id = :MorbusTub_id';
		}
		$query = "
			SELECT 
				MT.MorbusTub_id as \"MorbusTub_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				MM.MorbusTubStudyMicrosResult_id as \"MorbusTubStudyMicrosResult_id\",
				MM.MorbusTubStudyMicrosResult_EdResult as \"MorbusTubStudyMicrosResult_EdResult\",
				MM.MorbusTubStudyMicrosResult_NumLab as \"MorbusTubStudyMicrosResult_NumLab\",
				to_char( MM.MorbusTubStudyMicrosResult_setDT, 'dd.mm.yyyy') as \"MorbusTubStudyMicrosResult_setDT\",
				MM.TubMicrosResultType_id as \"TubMicrosResultType_id\"
			FROM v_MorbusTubStudyMicrosResult MM
				left join MorbusTubStudyResult MT on MT.MorbusTubStudyResult_id = MM.MorbusTubStudyResult_id
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение микроскопии в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubStudySeedResultAPI($data){
		$query = '
			SELECT
				MorbusTubStudySeedResult_id as "MorbusTubStudySeedResult_id",
				MorbusTubStudyResult_id as "MorbusTubStudyResult_id",
				TubSeedResultType_id as "TubSeedResultType_id",
				MorbusTubStudySeedResult_setDT as "MorbusTubStudySeedResult_setDT",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubStudySeedResult_insDT as "MorbusTubStudySeedResult_insDT",
				MorbusTubStudySeedResult_updDT as "MorbusTubStudySeedResult_updDT"
			FROM v_MorbusTubStudySeedResult
			WHERE MorbusTubStudySeedResult_id = :MorbusTubStudySeedResult_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubStudySeedResult_id'])){
			return array(array('Error_Msg' => 'не найдена запись микроскопии в рамках специфики по туберкулезу'));
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}

		return $this->saveMorbusTubStudySeedResult($data);
	}

	/**
	 * Получение микроскопии в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubStudySeedResultAPI($data){
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubStudySeedResult_id']) && empty($data['MorbusTub_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTubStudyResult_id'])){
			$where .= ' AND MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id';
		}
		if(!empty($data['MorbusTubStudySeedResult_id'])){
			$where .= ' AND MM.MorbusTubStudySeedResult_id = :MorbusTubStudySeedResult_id';
		}
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MT.MorbusTub_id = :MorbusTub_id';
		}
		$query = "
			SELECT
				MM.MorbusTubStudySeedResult_id as \"MorbusTubStudySeedResult_id\",
				MT.MorbusTub_id as \"MorbusTub_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				MM.TubSeedResultType_id as \"TubSeedResultType_id\"
			FROM v_MorbusTubStudySeedResult MM
				left join MorbusTubStudyResult MT on MT.MorbusTubStudyResult_id = MM.MorbusTubStudyResult_id
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение результатов гистологии в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubStudyHistolResultAPI($data){
		$query = '
			SELECT
				MorbusTubStudyHistolResult_id as "MorbusTubStudyHistolResult_id",
				MorbusTubStudyResult_id as "MorbusTubStudyResult_id",
				MorbusTubStudyHistolResult_setDT as "MorbusTubStudyHistolResult_setDT",
				TubHistolResultType_id as "TubHistolResultType_id",
				TubDiagnosticMaterialType_id as "TubDiagnosticMaterialType_id",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubStudyHistolResult_insDT as "MorbusTubStudyHistolResult_insDT",
				MorbusTubStudyHistolResult_updDT as "MorbusTubStudyHistolResult_updDT"
			FROM v_MorbusTubStudyHistolResult
			WHERE MorbusTubStudyHistolResult_id = :MorbusTubStudyHistolResult_id
		';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubStudyHistolResult_id'])){
			return array(array('Error_Msg' => 'не найдена запись результатов гистологии в рамках специфики по туберкулезу'));
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}

		return $this->saveMorbusTubStudyHistolResult($data);
	}

	/**
	 * Получение результатов гистологии в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubStudyHistolResultAPI($data){
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubStudyHistolResult_id']) && empty($data['MorbusTub_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTubStudyResult_id'])){
			$where .= ' AND MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id';
		}
		if(!empty($data['MorbusTubStudyHistolResult_id'])){
			$where .= ' AND MM.MorbusTubStudyHistolResult_id = :MorbusTubStudyHistolResult_id';
		}
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MT.MorbusTub_id = :MorbusTub_id';
		}
		$query = "
			SELECT
				MM.MorbusTubStudyHistolResult_id as \"MorbusTubStudyHistolResult_id\",
				MT.MorbusTub_id as \"MorbusTub_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				to_char( MM.MorbusTubStudyHistolResult_setDT, 'dd.mm.yyyy') as \"MorbusTubStudyHistolResult_setDT\",
				MM.TubDiagnosticMaterialType_id as \"TubDiagnosticMaterialType_id\",
				MM.TubHistolResultType_id as \"TubHistolResultType_id\"
			FROM v_MorbusTubStudyHistolResult MM
				left join MorbusTubStudyResult MT on MT.MorbusTubStudyResult_id = MM.MorbusTubStudyResult_id
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}

	/**
	 * Изменение результатов рентгена в рамках специфики по туберкулезу. Метод API
	 */
	function updateMorbusTubStudyXrayResultAPI($data){
		$query = '
			SELECT
				MorbusTubStudyXrayResult_id as "MorbusTubStudyXrayResult_id",
				MorbusTubStudyResult_id as "MorbusTubStudyResult_id",
				MorbusTubStudyXrayResult_setDT as "MorbusTubStudyXrayResult_setDT",
				TubXrayResultType_id as "TubXrayResultType_id",
				MorbusTubStudyXrayResult_Comment as "MorbusTubStudyXrayResult_Comment",
				pmUser_insID as "pmUser_insID",
				pmUser_updID as "pmUser_updID",
				MorbusTubStudyXrayResult_insDT as "MorbusTubStudyXrayResult_insDT",
				MorbusTubStudyXrayResult_updDT as "MorbusTubStudyXrayResult_updDT"
			FROM v_MorbusTubStudyXrayResult
			WHERE MorbusTubStudyXrayResult_id = :MorbusTubStudyXrayResult_id
			';
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['MorbusTubStudyXrayResult_id'])){
			return array(array('Error_Msg' => 'не найдена запись результатов рентгена в рамках специфики по туберкулезу'));
		}

		foreach ($data as $key => $value) {
			if(empty($data[$key]) && !empty($res[$key])){
				$data[$key] = $res[$key];
			}
		}

		return $this->saveMorbusTubStudyXrayResult($data);
	}

	/**
	 * Получение результатов рентгена в рамках специфики по туберкулезу. Метод API
	 */
	function getMorbusTubStudyXrayResultAPI($data){
		if(empty($data['MorbusTubStudyResult_id']) && empty($data['MorbusTubStudyXrayResult_id']) && empty($data['MorbusTub_id'])) return false;
		$where = '';
		if(!empty($data['MorbusTubStudyResult_id'])){
			$where .= ' AND MM.MorbusTubStudyResult_id = :MorbusTubStudyResult_id';
		}
		if(!empty($data['MorbusTubStudyXrayResult_id'])){
			$where .= ' AND MM.MorbusTubStudyXrayResult_id = :MorbusTubStudyXrayResult_id';
		}
		if(!empty($data['MorbusTub_id'])){
			$where .= ' AND MT.MorbusTub_id = :MorbusTub_id';
		}
		$query = "
			SELECT
				MM.MorbusTubStudyXrayResult_id as \"MorbusTubStudyXrayResult_id\",
				MT.MorbusTub_id as \"MorbusTub_id\",
				MM.MorbusTubStudyResult_id as \"MorbusTubStudyResult_id\",
				to_char( MM.MorbusTubStudyXrayResult_setDT, 'dd.mm.yyyy') as \"MorbusTubStudyXrayResult_setDT\",
				MM.TubXrayResultType_id as \"TubXrayResultType_id\",
				MM.MorbusTubStudyXrayResult_Comment as \"MorbusTubStudyXrayResult_Comment\"
			FROM v_MorbusTubStudyXrayResult MM
				left join MorbusTubStudyResult MT on MT.MorbusTubStudyResult_id = MM.MorbusTubStudyResult_id
			where 1=1
				{$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		}else{
			return false;
		}
	}
	function checkMorbusTubSpecIsSet($data) {
		$query = "select MT.MorbusTub_id as \"MorbusTub_id\",
				MT.TubResultDeathType_id as \"TubResultDeathType_id\",
				MT.MorbusTub_deadDT as \"MorbusTub_deadDT\"
				from v_Morbus M
				join v_MorbusTub MT on MT.Morbus_id = M.Morbus_id
				where M.Person_id = :Person_id
				limit 1";
		return $this->getFirstRowFromQuery($query, $data);
	}
}