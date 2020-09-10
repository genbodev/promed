<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusCrazy_model - модель для MorbusCrazy
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       A.Markoff <markov@swan.perm.ru>
 * @version      2012/10
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry

 * @property PersonRegister_model PersonRegister_model
 */

class MorbusCrazy_model extends swPgModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;

	private $entityFields = array(
		'MorbusCrazy' => array(
			'Morbus_id',
			'Diag_nid',
			'Diag_sid',
			'CrazyResultDeseaseType_id',
			'CrazyCauseEndSurveyType_id',
			'MorbusCrazy_CardEndDT',
			'MorbusCrazy_DeRegDT'
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
		),
		'MorbusCrazyBase' => array(
			'MorbusBase_id',
			'MorbusCrazyBase_LTMDayCount',
			'MorbusCrazyBase_HolidayDayCount',
			'MorbusCrazyBase_HolidayCount',
			'MorbusCrazyBase_DeathDT',
			'CrazyDeathCauseType_id',
			'MorbusCrazyBase_firstDT',
			'MorbusCrazyBase_IsUseAlienDevice',
			'MorbusCrazyBase_IsLivingConsumDrug'
		),
		'MorbusCrazyPerson' => array(
			'Person_id',
			'MorbusCrazyPerson_IsWowInvalid',
			'MorbusCrazyPerson_IsWowMember',
			'CrazyEducationType_id',
			'MorbusCrazyPerson_CompleteClassCount',
			'MorbusCrazyPerson_IsEducation',
			'CrazySourceLivelihoodType_id',
			'CrazyResideType_id',
			'CrazyResideConditionsType_id',
			'MorbusCrazyPerson_IsConvictionBeforePsych'
		)
	);

	protected $_MorbusType_id = null;//4

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick($data = array())
	{
		if (!empty($data['MorbusType_id'])) {
			return $this->getFirstResultFromQuery("
				select MorbusType_SysNick as \"MorbusType_SysNick\" from v_MorbusType where MorbusType_id = :MorbusType_id limit 1
			", $data);
		}
		return 'crazy';
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
	 *
	 * @param type $data
	 * @return array
	 * @throws Exception
	 */
	public function setCauseEndSurveyType($data){
		$query = "
			select
				MorbusCrazy_id as \"MorbusCrazy_id\",
				Diag_nid as \"Diag_nid\",
				Diag_sid as \"Diag_sid\",
				CrazyResultDeseaseType_id as \"CrazyResultDeseaseType_id\",
				CrazyCauseEndSurveyType_id as \"CrazyCauseEndSurveyType_id\"
			from v_MorbusCrazy
			where Morbus_id = :Morbus_id
			limit 1
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result) || !count($result = $result->result('array'))) {
			throw new Exception('Ошибка при выполнении запроса к базе данных');
		}
		
		$result = $result[0];
		$data['Diag_nid'] = $result['Diag_nid'];
		/** @var TYPE_NAME $data */
		$data['Diag_sid'] = $result['Diag_sid'];
		$data['CrazyResultDeseaseType_id'] = $result['CrazyResultDeseaseType_id'];
		$data['MorbusCrazy_id'] = $result['MorbusCrazy_id'];
		
		
		$data['MorbusCrazy_CardEndDT'] = $data['MorbusCrazy_CardEndDT'] ?? null;
		$data['MorbusCrazy_DeRegDT'] = $data['MorbusCrazy_DeRegDT'] ?? null;
		
		$query = "
			select
				MorbusCrazy_id as \"MorbusCrazy_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_MorbusCrazy_upd(
				MorbusCrazy_id := :MorbusCrazy_id,
				Diag_nid := :Diag_nid,
				Diag_sid := :Diag_sid,
				CrazyResultDeseaseType_id := :CrazyResultDeseaseType_id,
				CrazyCauseEndSurveyType_id := :CrazyCauseEndSurveyType_id,
				MorbusCrazy_CardEndDT := :MorbusCrazy_CardEndDT,
				MorbusCrazy_DeRegDT := :MorbusCrazy_DeRegDT,
				Morbus_id := :Morbus_id,
				pmUser_id := :pmUser_id
			)
		";
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			throw new Exception('Ошибка при выполнении запроса к базе данных');
		}
		
		return $res = $res->result('array');
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
		return 'Crazy';//Narko
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusCrazy';
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
			|| false == in_array($data['mode'], array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify' ,'repairSpecifics'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = array();
		$queryParams['pmUser_id'] = $this->promedUserId;
		$queryParams['MorbusBase_id'] = $data['MorbusBase_id'];
		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['Morbus_id'] = $data['Morbus_id'];
		$queryParams['Diag_id'] = $data['Diag_id'];
		$queryParams['Morbus_setDT'] = $data['Morbus_setDT'];
		$queryParams['Evn_pid'] = isset($data['Evn_pid'])?$data['Evn_pid']:null;

		$res = $this->getFirstResultFromQuery("
			select
				MorbusCrazyPerson_id as \"MorbusCrazyPerson_id\"
			from v_MorbusCrazyPerson
			where Person_id = :Person_id
			limit 1
		", $queryParams);

		if (empty($res)) {
			$res = $this->queryResult("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_MorbusCrazyPerson_ins(
					Person_id := :Person_id,
					pmUser_id := :pmUser_id
				);
			", $queryParams);
			if (!$this->isSuccessful($res)) {
				throw new Exception($res[0]['Error_Msg'], 500);
			}
		}

		$res = $this->getFirstResultFromQuery("
			select
				MorbusCrazyBase_id as \"MorbusCrazyBase_id\"
			from v_MorbusCrazyBase
			where MorbusBase_id = :MorbusBase_id
			limit 1
		", $queryParams);

		if(empty($res)) {
			$res = $this->queryResult("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_MorbusCrazyBase_ins(
					MorbusBase_id := :MorbusBase_id,
					pmUser_id := :pmUser_id
				);
			", $queryParams);
			if (!$this->isSuccessful($res)) {
				throw new Exception($res[0]['Error_Msg'], 500);
			}
		}

		$res = $this->getFirstResultFromQuery("
			select
				{$tableName}_id as \"{$tableName}_id\"
			from v_{$tableName}
			where Morbus_id = :Morbus_id
			limit 1
		", $queryParams);

		if(empty($res)) {
			$query = "
				select
					{$tableName}_id as \"{$tableName}_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_{$tableName}_ins(
					Morbus_id := :Morbus_id,
					pmUser_id := :pmUser_id
				);
			";
		} else {
			$query = "
				select
				{$tableName}_id as \"{$tableName}_id\",
				0 as \"Error_Code\", 
				'' as \"Error_Msg\"
			from v_{$tableName}
			where Morbus_id = :Morbus_id
			limit 1
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
		if (true) {
			$queryParams = array();
			$queryParams['pmUser_id'] = $this->promedUserId;
			$queryParams['MorbusCrazyDiag_id'] = null;
			$queryParams['MorbusCrazy_id'] = $this->_saveResponse['MorbusCrazy_id'];
			$queryParams['MorbusCrazyDiag_setDT'] = $data['Morbus_setDT'];

			$queryParams['CrazyDiag_id'] = $this->getFirstResultFromQuery('
				select
					CrazyDiag_id as "CrazyDiag_id" 
				from v_CrazyDiag
				where 
					Diag_id=:Diag_id
					and (CrazyDiag_endDate is null or CrazyDiag_endDate >= cast(:nowDate as date))
					and (CrazyDiag_begDate is null or CrazyDiag_begDate <= cast(:nowDate as date))
				limit 1
			',
				array(
					'Diag_id'=>$data['Diag_id'],
					'nowDate' => date('Y-m-d')
				)
			);
			$queryParams['Diag_sid'] = $data['Diag_id'];
			if (empty($queryParams['CrazyDiag_id'])) {
				throw new Exception('Ошибка запроса CrazyDiag_id', 500);
			}
			$resp = $this->execCommonSP('p_MorbusCrazyDiag_ins', $queryParams);
			if (empty($resp)) {
				throw new Exception('Ошибка запроса записи MorbusCrazyDiag', 500);
			}
			if (isset($resp[0]['Error_Msg'])) {
				throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
			}
		}
		return $this->_saveResponse;
	}

	/**
	 * Проверка обязательных параметров специфики
	 *
	 * @params Mode
	 *	- check_by_personregister - это создание нового заболевания при ручном вводе новой записи регистра из формы "Регистр по ..." (если есть открытое заболевание, то ничего не сохраняем. В регистре сохранится связь с открытым или созданным заболевание)
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 *	- check_by_evn - это создание нового заболевания при редактировании данных движения/посещения (если есть открытое заболевание и диагноз уточнился и посещение/движение актуально, то сохраняем диагноз и привязываем заболевание к этому посещению/движению)
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
		,'MorbusCrazy_id' => 'Идентификатор специфики заболевания'
		,'Morbus_setDT' => 'Дата заболевания'
			//,'Lpu_id' => 'ЛПУ, в которой впервые установлен диагноз орфанного заболевания'
		);
		switch ($data['Mode']) {
			case 'check_by_evn':
				$check_fields_list = array('Evn_pid','pmUser_id');//'Diag_id','Person_id', - не обязательные, но рекомендуемые
				break;
			case 'check_by_personregister':
				$check_fields_list = array('Morbus_setDT','Diag_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'personregister_viewform':
				$check_fields_list = array('MorbusCrazy_id','Morbus_id','Morbus_setDT','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'evnsection_viewform':
			case 'evnvizitpl_viewform':
				$check_fields_list = array('MorbusCrazy_id','Morbus_id','Evn_pid','pmUser_id'); //'Diag_id','Person_id',
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
	 * @param array $data
	 * @return array
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
			$tmp = swMorbus::getStaticMorbusCommon()->loadLastEvnData($this->getMorbusTypeSysNick($data), $data['Evn_pid'], $data['Person_id'], $data['Diag_id']);
			if (empty($tmp)) {
				if ( in_array($data['Mode'],array('evnsection_viewform','evnvizitpl_viewform')) ) {
					throw new Exception('Ошибка определения актуального учетного документа с данным заболеванием');
				}
				$data['Evn_aid'] = null;
			} else {
				//учетный документ найден
				$data['Evn_aid'] = $tmp[0]['Evn_id'];
				$data['Diag_id'] = $tmp[0]['Diag_id'];
				$data['Person_id'] = $tmp[0]['Person_id'];
			}
			if ($data['Mode'] == 'personregister_viewform' || $data['Evn_pid'] == $data['Evn_aid']) {
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
			,date_part('SECOND', ps.Person_BirthDay - interval '1970 years') as \"Person_BirthDay\"
			from v_PersonState ps
				inner join v_PersonRegister pr on pr.Person_id = ps.Person_id
				inner join v_MorbusType mt on pr.MorbusType_id = mt.MorbusType_id
			where 
				ps.Person_deadDT is not null 
				and pr.PersonRegister_disDate is null
				and mt.MorbusType_SysNick = 'Crazy'
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
	function getUsersCrazy($data)
	{
		$query = "
		select 
			PMUser_id as \"PMUser_id\" 
		from 
			v_pmUserCache
		where 
			pmUser_groups ilike '%Crazy%'
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
	 * Получает список диагнозов для специфики по психиатрии
	 */
	function getMorbusCrazyDiagViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyDiag_id']) and ($data['MorbusCrazyDiag_id'] > 0)) {
			$filter = "MCD.MorbusCrazyDiag_id = :MorbusCrazyDiag_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MCD.MorbusCrazy_id = :MorbusCrazy_id";
		}

		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				case when (MainRec.MorbusCrazyDiag_id = MCD.MorbusCrazyDiag_id) then 1 else 0 end as \"isMain\",
				MCD.MorbusCrazyDiag_id as \"MorbusCrazyDiag_id\",
				MCD.MorbusCrazyDiagDepend_id as \"MorbusCrazyDiagDepend_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				to_char(MCD.MorbusCrazyDiag_setDT, 'dd.mm.yyyy') as \"MorbusCrazyDiag_setDT\",
				MCD.CrazyDiag_id as \"CrazyDiag_id\",
				CrazyDiagId.CrazyDiag_Code || '. ' || CrazyDiagId.CrazyDiag_Name as \"CrazyDiag_id_Name\",
				MCD.Diag_sid as \"Diag_sid\",
				CrazyDiagSid.Diag_Code || '. ' || CrazyDiagSid.Diag_Name as \"Diag_sid_Name\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyDiag MCD
				inner join v_MorbusCrazy MC on MC.MorbusCrazy_id = MCD.MorbusCrazy_id
				inner join v_MorbusBase MB on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_CrazyDiag CrazyDiagId on CrazyDiagId.CrazyDiag_id = MCD.CrazyDiag_id
				left join v_Diag CrazyDiagSid on CrazyDiagSid.Diag_id = MCD.Diag_sid
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
				left join lateral (select MorbusCrazyDiag_id from v_MorbusCrazyDiag where MorbusCrazy_id = MCD.MorbusCrazy_id order by MorbusCrazyDiag_insDT limit 1) MainRec on true
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
	 * Сохраняет специфику "Диагноз"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyDiag($data) {
		$procedure = '';

		if ( (!isset($data['MorbusCrazyDiag_id'])) || ($data['MorbusCrazyDiag_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyDiag_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyDiag_upd';
		}

		$query = "
			select
				MorbusCrazyDiag_id as \"MorbusCrazyDiag_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyDiag_id := :MorbusCrazyDiag_id,
				MorbusCrazy_id := :MorbusCrazy_id,
				MorbusCrazyDiag_setDT := :MorbusCrazyDiag_setDT,
				CrazyDiag_id := :CrazyDiag_id,
				MorbusCrazyDiagDepend_id := :MorbusCrazyDiagDepend_id,
				Diag_sid := :Diag_sid,
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
	 * Получает список данных по динамике наблюдения
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyDynamicsObservViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyDynamicsObserv_id']) and ($data['MorbusCrazyDynamicsObserv_id'] > 0)) {
			$filter = "MCDO.MorbusCrazyDynamicsObserv_id = :MorbusCrazyDynamicsObserv_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MCDO.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCDO.MorbusCrazyDynamicsObserv_id as \"MorbusCrazyDynamicsObserv_id\",
				MCDO.CrazyAmbulMonitoringType_id as \"CrazyAmbulMonitoringType_id\",
				CrazyAmbulMonitoringType.CrazyAmbulMonitoringType_Name as \"CrazyAmbulMonitoringType_Name\",
				MCDO.Lpu_sid as \"Lpu_sid\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				to_char(MCDO.MorbusCrazyDynamicsObserv_setDT, 'dd.mm.yyyy') as \"MorbusCrazyDynamicsObserv_setDT\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyDynamicsObserv MCDO
				inner join v_MorbusCrazy MC on MC.MorbusCrazy_id = MCDO.MorbusCrazy_id
				inner join v_MorbusBase MB on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_CrazyAmbulMonitoringType CrazyAmbulMonitoringType on CrazyAmbulMonitoringType.CrazyAmbulMonitoringType_id = MCDO.CrazyAmbulMonitoringType_id
				left join v_Lpu Lpu on Lpu.Lpu_id = MCDO.Lpu_sid
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
	 * Сохраняет специфику "Динамика наблюдения"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyDynamicsObserv($data) {
		$procedure = '';

		if ( (!isset($data['MorbusCrazyDynamicsObserv_id'])) || ($data['MorbusCrazyDynamicsObserv_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyDynamicsObserv_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyDynamicsObserv_upd';
		}

		$query = "
			select
				MorbusCrazyDynamicsObserv_id as \"MorbusCrazyDynamicsObserv_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyDynamicsObserv_id := :MorbusCrazyDynamicsObserv_id,
				MorbusCrazy_id := :MorbusCrazy_id,
				Lpu_sid := :Lpu_sid,
				CrazyAmbulMonitoringType_id := :CrazyAmbulMonitoringType_id,
				MorbusCrazyDynamicsObserv_setDT := :MorbusCrazyDynamicsObserv_setDT,
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
	 * Получает список данных по контролю посещений
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyVizitCheckViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyVizitCheck_id']) and ($data['MorbusCrazyVizitCheck_id'] > 0)) {
			$filter = "MM.MorbusCrazyVizitCheck_id = :MorbusCrazyVizitCheck_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusCrazyVizitCheck_id as \"MorbusCrazyVizitCheck_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				to_char(MM.MorbusCrazyVizitCheck_setDT, 'dd.mm.yyyy') as \"MorbusCrazyVizitCheck_setDT\",
				to_char(MM.MorbusCrazyVizitCheck_vizitDT, 'dd.mm.yyyy') as \"MorbusCrazyVizitCheck_vizitDT\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyVizitCheck MM
				inner join v_MorbusCrazy MC on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				inner join v_MorbusBase MB on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";
		$result = $this->db->query($query, $data);
		//echo getDebugSQL($query, $data); exit();
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Контроль посещений"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyVizitCheck($data) {
		$procedure = '';

		if ( (!isset($data['MorbusCrazyVizitCheck_id'])) || ($data['MorbusCrazyVizitCheck_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyVizitCheck_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyVizitCheck_upd';
		}

		$query = "
			select
				MorbusCrazyVizitCheck_id as \"MorbusCrazyVizitCheck_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyVizitCheck_id := :MorbusCrazyVizitCheck_id,
				MorbusCrazy_id := :MorbusCrazy_id,
				MorbusCrazyVizitCheck_setDT := :MorbusCrazyVizitCheck_setDT,
				MorbusCrazyVizitCheck_vizitDT := :MorbusCrazyVizitCheck_vizitDT,
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
	 * Получает список записей по динамике состояний
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyDynamicsStateViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyDynamicsState_id']) and ($data['MorbusCrazyDynamicsState_id'] > 0)) {
			$filter = "MM.MorbusCrazyDynamicsState_id = :MorbusCrazyDynamicsState_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusCrazyDynamicsState_id as \"MorbusCrazyDynamicsState_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				to_char(MM.MorbusCrazyDynamicsState_begDT, 'dd.mm.yyyy') as \"MorbusCrazyDynamicsState_begDT\",
				to_char(MM.MorbusCrazyDynamicsState_endDT, 'dd.mm.yyyy') as \"MorbusCrazyDynamicsState_endDT\",
				DATEDIFF('day', MorbusCrazyDynamicsState_begDT, coalesce(MorbusCrazyDynamicsState_endDT, dbo.tzGetDate())) as \"MorbusCrazyDynamicsState_Count\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyDynamicsState MM
				inner join v_MorbusCrazy MC on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				inner join v_MorbusBase MB on MC.MorbusBase_id = MB.MorbusBase_id
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
	 * Сохраняет специфику "Динамика состояний"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyDynamicsState($data) {
		$procedure = '';

		if ( (!isset($data['MorbusCrazyDynamicsState_id'])) || ($data['MorbusCrazyDynamicsState_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyDynamicsState_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyDynamicsState_upd';
		}

		$query = "
			select
				MorbusCrazyDynamicsState_id as \"MorbusCrazyDynamicsState_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyDynamicsState_id := :MorbusCrazyDynamicsState_id,
				MorbusCrazy_id := :MorbusCrazy_id,
				MorbusCrazyDynamicsState_begDT := :MorbusCrazyDynamicsState_begDT,
				MorbusCrazyDynamicsState_endDT := :MorbusCrazyDynamicsState_endDT,
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
	 * Получает список данных по сведениям по госпитализации
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyBasePSViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyBasePS_id']) and ($data['MorbusCrazyBasePS_id'] > 0)) {
			$filter = "MM.MorbusCrazyBasePS_id = :MorbusCrazyBasePS_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MorbusCrazyBasePS_id as \"MorbusCrazyBasePS_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCB.MorbusCrazyBase_id as \"MorbusCrazyBase_id\",
				MM.Evn_id as \"Evn_id\",
				case
					when coalesce(MM.Evn_id,0) = EvnEdit.Evn_id and EvnClass_SysNick = 'EvnSection' then 1
					when MM.Evn_id is null and coalesce(EvnClass_SysNick,'') != 'EvnSection' then 1
					else 0
				end as \"accessEvn\",
				MM.CrazyPurposeHospType_id as \"CrazyPurposeHospType_id\",
				MM.CrazyPurposeDirectType_id as \"CrazyPurposeDirectType_id\",
				CrazyPurposeHospType.CrazyPurposeHospType_Name as \"CrazyPurposeHospType_Name\",
				to_char(MM.MorbusCrazyBasePS_setDT, 'dd.mm.yyyy') as \"MorbusCrazyBasePS_setDT\",
				to_char(MM.MorbusCrazyBasePS_disDT, 'dd.mm.yyyy') as \"MorbusCrazyBasePS_disDT\",
				MM.CrazyDiag_id as \"CrazyDiag_id\",
				CrazyDiagId.CrazyDiag_Code || '. ' || CrazyDiagId.CrazyDiag_Name as \"CrazyDiag_id_Name\",
				MM.Diag_id as \"Diag_id\",
				DiagId.Diag_Code || '. ' || DiagId.Diag_Name as \"Diag_id_Name\",
				MM.Lpu_id as \"Lpu_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				CrazyHospType_id as \"CrazyHospType_id\",
				CrazySupplyType_id as \"CrazySupplyType_id\",
				CrazyDirectType_id as \"CrazyDirectType_id\",
				CrazySupplyOrderType_id as \"CrazySupplyOrderType_id\",
				CrazyDirectFromType_id as \"CrazyDirectFromType_id\",
				CrazyJudgeDecisionArt35Type_id as \"CrazyJudgeDecisionArt35Type_id\",
				CrazyLeaveType_id as \"CrazyLeaveType_id\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyBasePS MM
				inner join v_MorbusCrazyBase MCB on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_CrazyPurposeHospType CrazyPurposeHospType on CrazyPurposeHospType.CrazyPurposeHospType_id = MM.CrazyPurposeHospType_id
				left join v_CrazyDiag CrazyDiagId on CrazyDiagId.CrazyDiag_id = MM.CrazyDiag_id
				left join v_Diag DiagId on DiagId.Diag_id = MM.Diag_id
				left join v_Lpu Lpu on Lpu.Lpu_id = MM.Lpu_id
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
	 * Сохраняет специфику "Сведения о госпитализации"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyBasePS($data) {
		$procedure = '';

		if ( (!isset($data['MorbusCrazyBasePS_id'])) || ($data['MorbusCrazyBasePS_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyBasePS_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyBasePS_upd';
		}

		$query = "
			select
				MorbusCrazyBasePS_id as \"MorbusCrazyBasePS_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyBasePS_id := :MorbusCrazyBasePS_id,
				MorbusCrazyBase_id := :MorbusCrazyBase_id,
				Evn_id := :Evn_id,
				CrazyPurposeHospType_id := :CrazyPurposeHospType_id,
				CrazyPurposeDirectType_id := :CrazyPurposeDirectType_id,
				MorbusCrazyBasePS_setDT := :MorbusCrazyBasePS_setDT,
				MorbusCrazyBasePS_disDT := :MorbusCrazyBasePS_disDT,
				CrazyDiag_id := :CrazyDiag_id,
				Diag_id := :Diag_id,
				Lpu_id := :Lpu_id,
				CrazyHospType_id := :CrazyHospType_id,
				CrazySupplyType_id := :CrazySupplyType_id,
				CrazyDirectType_id := :CrazyDirectType_id,
				CrazySupplyOrderType_id := :CrazySupplyOrderType_id,
				CrazyDirectFromType_id := :CrazyDirectFromType_id,
				CrazyJudgeDecisionArt35Type_id := :CrazyJudgeDecisionArt35Type_id,
				CrazyLeaveType_id := :CrazyLeaveType_id,
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
	 * Возвращает список "принудительное лечение"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyForceTreatViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyForceTreat_id']) and ($data['MorbusCrazyForceTreat_id'] > 0)) {
			$filter = "MM.MorbusCrazyForceTreat_id = :MorbusCrazyForceTreat_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				case
					when coalesce(MM.Evn_id,0) = EvnEdit.Evn_id and EvnClass_SysNick = 'EvnSection' then 1
					when MM.Evn_id is null and coalesce(EvnClass_SysNick,'') != 'EvnSection' then 1
					else 0
				end as \"accessEvn\",
				MorbusCrazyForceTreat_id as \"MorbusCrazyForceTreat_id\",
				MM.CrazyForceTreatType_id as \"CrazyForceTreatType_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCB.MorbusCrazyBase_id as \"MorbusCrazyBase_id\",
				CrazyForceTreatType.CrazyForceTreatType_Name as \"CrazyForceTreatType_Name\",
				to_char(MM.MorbusCrazyForceTreat_begDT, 'dd.mm.yyyy') as \"MorbusCrazyForceTreat_begDT\",
				to_char(MM.MorbusCrazyForceTreat_endDT, 'dd.mm.yyyy') as \"MorbusCrazyForceTreat_endDT\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyForceTreat MM
				inner join v_MorbusCrazyBase MCB on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_CrazyForceTreatType CrazyForceTreatType on CrazyForceTreatType.CrazyForceTreatType_id = MM.CrazyForceTreatType_id
				--left join v_Lpu Lpu on Lpu.Lpu_id = MC.Lpu_id
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
	 * Сохраняет специфику "Принудительное лечение"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyForceTreat($data) {
		$procedure = '';

		if ( (!isset($data['MorbusCrazyForceTreat_id'])) || ($data['MorbusCrazyForceTreat_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyForceTreat_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyForceTreat_upd';
		}

		$query = "
			select
				MorbusCrazyForceTreat_id as \"MorbusCrazyForceTreat_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyForceTreat_id := :MorbusCrazyForceTreat_id,
				MorbusCrazyBase_id := :MorbusCrazyBase_id,
				MorbusCrazyForceTreat_begDT := :MorbusCrazyForceTreat_begDT,
				MorbusCrazyForceTreat_endDT := :MorbusCrazyForceTreat_endDT,
				CrazyForceTreatType_id := :CrazyForceTreatType_id,
				Evn_id := :Evn_id,
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
	 * Возвращает список "Изменение вида"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyUpForceTreatViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyUpForceTreat_id']) and ($data['MorbusCrazyUpForceTreat_id'] > 0)) {
			$filter = "MM.MorbusCrazyUpForceTreat_id = :MorbusCrazyUpForceTreat_id";
		} else {
			if(empty($data['MorbusCrazyForceTreat_id']) or $data['MorbusCrazyForceTreat_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusCrazyForceTreat_id = :MorbusCrazyForceTreat_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusCrazyUpForceTreat_id as \"MorbusCrazyUpForceTreat_id\",
				MM.CrazyForceTreatType_id as \"CrazyForceTreatType_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCB.MorbusCrazyBase_id as \"MorbusCrazyBase_id\",
				MM.MorbusCrazyForceTreat_id as \"MorbusCrazyForceTreat_id\",
				CrazyForceTreatType.CrazyForceTreatType_Name as \"CrazyForceTreatType_Name\",
				to_char(MM.MorbusCrazyUpForceTreat_setDT, 'dd.mm.yyyy') as \"MorbusCrazyUpForceTreat_setDT\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyUpForceTreat MM
				inner join v_MorbusCrazyForceTreat MCFT on MCFT.MorbusCrazyForceTreat_id = MM.MorbusCrazyForceTreat_id
				inner join v_MorbusCrazyBase MCB on MCB.MorbusCrazyBase_id = MCFT.MorbusCrazyBase_id
				inner join v_MorbusBase MB on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_CrazyForceTreatType CrazyForceTreatType on CrazyForceTreatType.CrazyForceTreatType_id = MM.CrazyForceTreatType_id
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
	 * Сохраняет специфику "Изменение вида"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyUpForceTreat($data) {
		$procedure = '';

		if ( (!isset($data['MorbusCrazyUpForceTreat_id'])) || ($data['MorbusCrazyUpForceTreat_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyUpForceTreat_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyUpForceTreat_upd';
		}

		$query = "
			select
				MorbusCrazyUpForceTreat_id as \"MorbusCrazyUpForceTreat_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyUpForceTreat_id := :MorbusCrazyUpForceTreat_id,
				MorbusCrazyForceTreat_id := :MorbusCrazyForceTreat_id,
				MorbusCrazyUpForceTreat_setDT := :MorbusCrazyUpForceTreat_setDT,
				CrazyForceTreatType_id := :CrazyForceTreatType_id,
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
	 * Возвращает список "Недобровольное освидетельствование"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyNdOsvidViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyNdOsvid_id']) and ($data['MorbusCrazyNdOsvid_id'] > 0)) {
			$filter = "MM.MorbusCrazyNdOsvid_id = :MorbusCrazyNdOsvid_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MorbusCrazyNdOsvid_id as \"MorbusCrazyNdOsvid_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCB.MorbusCrazyBase_id as \"MorbusCrazyBase_id\",
				to_char(MM.MorbusCrazyNdOsvid_setDT, 'dd.mm.yyyy') as \"MorbusCrazyNdOsvid_setDT\",
				MM.Lpu_id as \"Lpu_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyNdOsvid MM
				inner join v_MorbusCrazyBase MCB on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_Lpu Lpu on Lpu.Lpu_id = MM.Lpu_id
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
	 * Сохраняет специфику "Недобровольное освидетельствование"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyNdOsvid($data) {
		$procedure = '';

		if ( (!isset($data['MorbusCrazyNdOsvid_id'])) || ($data['MorbusCrazyNdOsvid_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyNdOsvid_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyNdOsvid_upd';
		}

		$query = "
			select
				MorbusCrazyNdOsvid_id as \"MorbusCrazyNdOsvid_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyNdOsvid_id := :MorbusCrazyNdOsvid_id,
				MorbusCrazyBase_id := :MorbusCrazyBase_id,
				MorbusCrazyNdOsvid_setDT := :MorbusCrazyNdOsvid_setDT,
				Lpu_id := :Lpu_id,
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
	 * Возвращает список "Обследование на ВИЧ"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyPersonSurveyHIVViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyPersonSurveyHIV_id']) and ($data['MorbusCrazyPersonSurveyHIV_id'] > 0)) {
			$filter = "MM.MorbusCrazyPersonSurveyHIV_id = :MorbusCrazyPersonSurveyHIV_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				case
					when coalesce(MM.Evn_id,0) = EvnEdit.Evn_id and EvnClass_SysNick = 'EvnSection' then 1
					when MM.Evn_id is null and coalesce(EvnClass_SysNick,'') != 'EvnSection' then 1
					else 0
				end as \"accessEvn\",
				MorbusCrazyPersonSurveyHIV_id as \"MorbusCrazyPersonSurveyHIV_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCP.MorbusCrazyPerson_id as \"MorbusCrazyPerson_id\",
				to_char(MM.MorbusCrazyPersonSurveyHIV_setDT, 'dd.mm.yyyy') as \"MorbusCrazyPersonSurveyHIV_setDT\",
				MM.CrazySurveyHIVType_id as \"CrazySurveyHIVType_id\",
				CrazySurveyHIVType.CrazySurveyHIVType_Name as \"CrazySurveyHIVType_Name\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyPersonSurveyHIV MM
				inner join v_MorbusCrazyPerson MCP on MCP.MorbusCrazyPerson_id = MM.MorbusCrazyPerson_id
				inner join v_MorbusBase MB on MCP.Person_id = MB.Person_id
				inner join v_MorbusCrazy MC on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_CrazySurveyHIVType CrazySurveyHIVType on CrazySurveyHIVType.CrazySurveyHIVType_id = MM.CrazySurveyHIVType_id
				--!!inner join v_MorbusCrazy MC on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				--!!inner join v_MorbusBase MB on MC.MorbusBase_id = MB.MorbusBase_id
				--left join v_Diag CrazyDiagId on CrazyDiagId.Diag_id = MM.CrazyDiag_id
				--left join v_Diag DiagId on DiagId.Diag_id = MM.CrazyDiag_id
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
	 * Сохраняет специфику "Обследование на ВИЧ"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyPersonSurveyHIV($data) {
		$procedure = '';

		if ( (!isset($data['MorbusCrazyPersonSurveyHIV_id'])) || ($data['MorbusCrazyPersonSurveyHIV_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyPersonSurveyHIV_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyPersonSurveyHIV_upd';
		}

		$query = "
			select
				MorbusCrazyPersonSurveyHIV_id as \"MorbusCrazyPersonSurveyHIV_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyPersonSurveyHIV_id := :MorbusCrazyPersonSurveyHIV_id,
				MorbusCrazyPerson_id := :MorbusCrazyPerson_id,
				MorbusCrazyPersonSurveyHIV_setDT := :MorbusCrazyPersonSurveyHIV_setDT,
				CrazySurveyHIVType_id := :CrazySurveyHIVType_id,
				Evn_id := :Evn_id,
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
	 * Возвращает список "Временная нетрудоспособность"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyPersonStickViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyPersonStick_id']) and ($data['MorbusCrazyPersonStick_id'] > 0)) {
			$filter = "MM.MorbusCrazyPersonStick_id = :MorbusCrazyPersonStick_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MorbusCrazyPersonStick_id as \"MorbusCrazyPersonStick_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCP.MorbusCrazyPerson_id as \"MorbusCrazyPerson_id\",
				to_char(MM.MorbusCrazyPersonStick_setDT, 'dd.mm.yyyy') as \"MorbusCrazyPersonStick_setDT\",
				to_char(MM.MorbusCrazyPersonStick_disDT, 'dd.mm.yyyy') as \"MorbusCrazyPersonStick_disDT\",
				DATEDIFF('day', MorbusCrazyPersonStick_setDT, MorbusCrazyPersonStick_disDT) as \"MorbusCrazyPersonStick_Count\", -- Число дней ВН
				MM.Diag_id as \"Diag_id\",
				Diag.Diag_Code || '. ' || Diag.Diag_Name as \"Diag_Name\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyPersonStick MM
				inner join v_MorbusCrazyPerson MCP on MCP.MorbusCrazyPerson_id = MM.MorbusCrazyPerson_id
				inner join v_MorbusBase MB on MCP.Person_id = MB.Person_id
				inner join v_MorbusCrazy MC on MC.MorbusBase_id = MB.MorbusBase_id
				--!!inner join v_MorbusCrazy MC on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				--!!inner join v_MorbusBase MB on MC.MorbusBase_id = MB.MorbusBase_id
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
	 * Сохраняет специфику "Временная нетрудоспособность"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyPersonStick($data) {
		$procedure = '';

		if ( (!isset($data['MorbusCrazyPersonStick_id'])) || ($data['MorbusCrazyPersonStick_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyPersonStick_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyPersonStick_upd';
		}

		$query = "
			select
				MorbusCrazyPersonStick_id as \"MorbusCrazyPersonStick_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyPersonStick_id := :MorbusCrazyPersonStick_id,
				MorbusCrazyPerson_id := :MorbusCrazyPerson_id,
				MorbusCrazyPersonStick_setDT := :MorbusCrazyPersonStick_setDT,
				MorbusCrazyPersonStick_disDT := :MorbusCrazyPersonStick_disDT,
				MorbusCrazyPersonStick_Article := :MorbusCrazyPersonStick_Article,
				Diag_id := :Diag_id,
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
	 * Возвращает список "Суицидальные попытки"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyPersonSuicidalAttemptViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyPersonSuicidalAttempt_id']) and ($data['MorbusCrazyPersonSuicidalAttempt_id'] > 0)) {
			$filter = "MM.MorbusCrazyPersonSuicidalAttempt_id = :MorbusCrazyPersonSuicidalAttempt_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				ROW_NUMBER() OVER (ORDER BY MorbusCrazyPersonSuicidalAttempt_setDT DESC) AS \"MorbusCrazyPersonSuicidalAttempt_Num\",
				MorbusCrazyPersonSuicidalAttempt_id as \"MorbusCrazyPersonSuicidalAttempt_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCP.MorbusCrazyPerson_id as \"MorbusCrazyPerson_id\",
				to_char(MM.MorbusCrazyPersonSuicidalAttempt_setDT, 'dd.mm.yyyy') as \"MorbusCrazyPersonSuicidalAttempt_setDT\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyPersonSuicidalAttempt MM
				inner join v_MorbusCrazyPerson MCP on MCP.MorbusCrazyPerson_id = MM.MorbusCrazyPerson_id
				inner join v_MorbusBase MB on MCP.Person_id = MB.Person_id
				inner join v_MorbusCrazy MC on MC.MorbusBase_id = MB.MorbusBase_id
				--!!inner join v_MorbusCrazy MC on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				--!!inner join v_MorbusBase MB on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";
		$result = $this->db->query($query, $data);
		//echo getDebugSQL($query, $data); exit();
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Сохраняет специфику "Суицидальные попытки"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyPersonSuicidalAttempt($data) {
		$procedure = '';
		if ( (!isset($data['MorbusCrazyPersonSuicidalAttempt_id'])) || ($data['MorbusCrazyPersonSuicidalAttempt_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyPersonSuicidalAttempt_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyPersonSuicidalAttempt_upd';
		}

		$query = "
			select
				MorbusCrazyPersonSuicidalAttempt_id as \"MorbusCrazyPersonSuicidalAttempt_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyPersonSuicidalAttempt_id := :MorbusCrazyPersonSuicidalAttempt_id,
				MorbusCrazyPerson_id := :MorbusCrazyPerson_id,
				MorbusCrazyPersonSuicidalAttempt_setDT := :MorbusCrazyPersonSuicidalAttempt_setDT,
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
	 * Возвращает список "Общественно-опасные действия"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyPersonSocDangerActViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyPersonSocDangerAct_id']) and ($data['MorbusCrazyPersonSocDangerAct_id'] > 0)) {
			$filter = "MM.MorbusCrazyPersonSocDangerAct_id = :MorbusCrazyPersonSocDangerAct_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MorbusCrazyPersonSocDangerAct_id as \"MorbusCrazyPersonSocDangerAct_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCP.MorbusCrazyPerson_id as \"MorbusCrazyPerson_id\",
				to_char(MM.MorbusCrazyPersonSocDangerAct_setDT, 'dd.mm.yyyy') as \"MorbusCrazyPersonSocDangerAct_setDT\",
				MM.MorbusCrazyPersonSocDangerAct_Article as \"MorbusCrazyPersonSocDangerAct_Article\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyPersonSocDangerAct MM
				inner join v_MorbusCrazyPerson MCP on MCP.MorbusCrazyPerson_id = MM.MorbusCrazyPerson_id
				inner join v_MorbusBase MB on MCP.Person_id = MB.Person_id
				inner join v_MorbusCrazy MC on MC.MorbusBase_id = MB.MorbusBase_id
				--!!inner join v_MorbusCrazy MC on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				--!!inner join v_MorbusBase MB on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";
		$result = $this->db->query($query, $data);
		//echo getDebugSQL($query, $data); exit();
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Общественно-опасные действия"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyPersonSocDangerAct($data) {
		$procedure = '';
		if ( (!isset($data['MorbusCrazyPersonSocDangerAct_id'])) || ($data['MorbusCrazyPersonSocDangerAct_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyPersonSocDangerAct_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyPersonSocDangerAct_upd';
		}

		$query = "
			select
				MorbusCrazyPersonSocDangerAct_id as \"MorbusCrazyPersonSocDangerAct_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyPersonSocDangerAct_id := :MorbusCrazyPersonSocDangerAct_id,
				MorbusCrazyPerson_id := :MorbusCrazyPerson_id,
				MorbusCrazyPersonSocDangerAct_setDT := :MorbusCrazyPersonSocDangerAct_setDT,
				MorbusCrazyPersonSocDangerAct_Article := :MorbusCrazyPersonSocDangerAct_Article,
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
	 * Возвращает список "Возраст начала употребления психоактивных веществ"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyBaseDrugStartViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyBaseDrugStart_id']) and ($data['MorbusCrazyBaseDrugStart_id'] > 0)) {
			$filter = "MM.MorbusCrazyBaseDrugStart_id = :MorbusCrazyBaseDrugStart_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MorbusCrazyBaseDrugStart_id as \"MorbusCrazyBaseDrugStart_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCB.MorbusCrazyBase_id as \"MorbusCrazyBase_id\",
				MorbusCrazyBaseDrugStart_Name as \"MorbusCrazyBaseDrugStart_Name\",
				MM.CrazyDrugReceptType_id as \"CrazyDrugReceptType_id\",
				CrazyDrugReceptType.CrazyDrugReceptType_Name as \"CrazyDrugReceptType_Name\",
				MM.MorbusCrazyBaseDrugStart_Age as \"MorbusCrazyBaseDrugStart_Age\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyBaseDrugStart MM
				inner join v_MorbusCrazyBase MCB on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_CrazyDrugReceptType CrazyDrugReceptType on CrazyDrugReceptType.CrazyDrugReceptType_id = MM.CrazyDrugReceptType_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";
		$result = $this->db->query($query, $data);
		//echo getDebugSQL($query, $data); exit();
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Сохраняет специфику "Возраст начала употребления психоактивных веществ"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyBaseDrugStart($data) {
		$procedure = '';
		if ( (!isset($data['MorbusCrazyBaseDrugStart_id'])) || ($data['MorbusCrazyBaseDrugStart_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyBaseDrugStart_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyBaseDrugStart_upd';
		}

		$query = "
			select
				MorbusCrazyBaseDrugStart_id as \"MorbusCrazyBaseDrugStart_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyBaseDrugStart_id := :MorbusCrazyBaseDrugStart_id,
				MorbusCrazyBase_id := :MorbusCrazyBase_id,
				MorbusCrazyBaseDrugStart_Name := :MorbusCrazyBaseDrugStart_Name,
				CrazyDrugReceptType_id := :CrazyDrugReceptType_id,
				MorbusCrazyBaseDrugStart_Age := :MorbusCrazyBaseDrugStart_Age,
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
	 * Возвращает список "Употребление психоактивных веществ"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyDrugViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyDrug_id']) and ($data['MorbusCrazyDrug_id'] > 0)) {
			$filter = "MM.MorbusCrazyDrug_id = :MorbusCrazyDrug_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MorbusCrazyDrug_id as \"MorbusCrazyDrug_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCB.MorbusCrazyBase_id as \"MorbusCrazyBase_id\",
				MorbusCrazyDrug_Name as \"MorbusCrazyDrug_Name\",
				MM.CrazyDrugType_id as \"CrazyDrugType_id\",
				CrazyDrugType.CrazyDrugType_Name as \"CrazyDrugType_Name\",
				MM.CrazyDrugReceptType_id as \"CrazyDrugReceptType_id\",
				CrazyDrugReceptType.CrazyDrugReceptType_Name as \"CrazyDrugReceptType_Name\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyDrug MM
				inner join v_MorbusCrazyBase MCB on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_CrazyDrugType CrazyDrugType on CrazyDrugType.CrazyDrugType_id = MM.CrazyDrugType_id
				left join v_CrazyDrugReceptType CrazyDrugReceptType on CrazyDrugReceptType.CrazyDrugReceptType_id = MM.CrazyDrugReceptType_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";
		$result = $this->db->query($query, $data);
		//echo getDebugSQL($query, $data); exit();
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Употребление психоактивных веществ"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyDrug($data) {
		$procedure = '';
		if ( (!isset($data['MorbusCrazyDrug_id'])) || ($data['MorbusCrazyDrug_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyDrug_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyDrug_upd';
		}

		$query = "
			select
				MorbusCrazyDrug_id as \"MorbusCrazyDrug_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyDrug_id := :MorbusCrazyDrug_id,
				MorbusCrazyBase_id := :MorbusCrazyBase_id,
				MorbusCrazyDrug_Name := :MorbusCrazyDrug_Name,
				CrazyDrugType_id := :CrazyDrugType_id,
				CrazyDrugReceptType_id := :CrazyDrugReceptType_id,
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
	 * Возвращает список "Полученный объем наркологической помощи"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyDrugVolumeViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyDrugVolume_id']) and ($data['MorbusCrazyDrugVolume_id'] > 0)) {
			$filter = "MM.MorbusCrazyDrugVolume_id = :MorbusCrazyDrugVolume_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				case
					when coalesce(MM.Evn_id,0) = EvnEdit.Evn_id and EvnClass_SysNick = 'EvnSection' then 1
					when MM.Evn_id is null and coalesce(EvnClass_SysNick,'') != 'EvnSection' then 1
					else 0
				end \"accessEvn\",
				MorbusCrazyDrugVolume_id as \"MorbusCrazyDrugVolume_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCB.MorbusCrazyBase_id as \"MorbusCrazyBase_id\",
				MM.Lpu_id as \"Lpu_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				to_char(MM.MorbusCrazyDrugVolume_setDT, 'dd.mm.yyyy') as \"MorbusCrazyDrugVolume_setDT\",
				MM.CrazyDrugVolumeType_id as \"CrazyDrugVolumeType_id\",
				CrazyDrugVolumeType.CrazyDrugVolumeType_Name as \"CrazyDrugVolumeType_Name\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyDrugVolume MM
				inner join v_MorbusCrazyBase MCB on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_Lpu Lpu on Lpu.Lpu_id = MM.Lpu_id
				left join v_CrazyDrugVolumeType CrazyDrugVolumeType on CrazyDrugVolumeType.CrazyDrugVolumeType_id = MM.CrazyDrugVolumeType_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";
		$result = $this->db->query($query, $data);
		//echo getDebugSQL($query, $data); exit();
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Полученный объем наркологической помощи"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyDrugVolume($data) {
		$procedure = '';
		if ( (!isset($data['MorbusCrazyDrugVolume_id'])) || ($data['MorbusCrazyDrugVolume_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyDrugVolume_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyDrugVolume_upd';
		}

		$query = "
			select
				MorbusCrazyDrugVolume_id as \"MorbusCrazyDrugVolume_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyDrugVolume_id := :MorbusCrazyDrugVolume_id,
				MorbusCrazyBase_id := :MorbusCrazyBase_id,
				Lpu_id := :Lpu_id,
				MorbusCrazyDrugVolume_setDT := :MorbusCrazyDrugVolume_setDT,
				CrazyDrugVolumeType_id := :CrazyDrugVolumeType_id,
				Evn_id := :Evn_id,
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
	 * Возвращает список "Военно-врачебная комиссия"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyBBKViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyBBK_id']) and ($data['MorbusCrazyBBK_id'] > 0)) {
			$filter = "MM.MorbusCrazyBBK_id = :MorbusCrazyBBK_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MM.MorbusCrazyBBK_id as \"MorbusCrazyBBK_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MM.MedicalCareType_id as \"MedicalCareType_id\",
				MCT.MedicalCareType_Name as \"MedicalCareType_Name\",
				MM.CrazyDiag_id as \"CrazyDiag_id\",
				CD.CrazyDiag_Name as \"CrazyDiag_Name\",
				MM.CrazyDiag_lid as \"CrazyDiag_lid\",
				CDL.CrazyDiag_Name as \"CrazyDiag_lName\",
				to_char(MM.MorbusCrazyBBK_setDT, 'dd.mm.yyyy') as \"MorbusCrazyBBK_setDT\",
				to_char(MM.MorbusCrazyBBK_firstDT, 'dd.mm.yyyy') as \"MorbusCrazyBBK_firstDT\",
				to_char(MM.MorbusCrazyBBK_lidDT, 'dd.mm.yyyy') as \"MorbusCrazyBBK_lidDT\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyBBK MM
				inner join v_MorbusCrazy MC on MM.MorbusCrazy_id = MC.MorbusCrazy_id
				inner join v_MorbusBase MB on MC.MorbusBase_id = MB.MorbusBase_id
				left join MedicalCareType MCT on MCT.MedicalCareType_id = MM.MedicalCareType_id
				left join v_CrazyDiag CD on CD.CrazyDiag_id = MM.CrazyDiag_id
				left join v_CrazyDiag CDL on CDL.CrazyDiag_id = MM.CrazyDiag_lid
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";
		$result = $this->db->query($query, $data);
		//echo getDebugSQL($query, $data); exit();
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Военно-врачебная комиссия"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyBBK($data) {
		$procedure = '';
		if ( (!isset($data['MorbusCrazyBBK_id'])) || ($data['MorbusCrazyBBK_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyBBK_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyBBK_upd';
		}

		$query = "
			select
				MorbusCrazyBBK_id as \"MorbusCrazyBBK_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyBBK_id := :MorbusCrazyBBK_id,
				MorbusCrazy_id := :MorbusCrazy_id,
				MorbusCrazyBBK_setDT := :MorbusCrazyBBK_setDT,
				CrazyDiag_id := :CrazyDiag_id,
				MorbusCrazyBBK_firstDT := :MorbusCrazyBBK_firstDT,
				MedicalCareType_id := :MedicalCareType_id,
				CrazyDiag_lid := :CrazyDiag_lid,
				MorbusCrazyBBK_lidDT := :MorbusCrazyBBK_lidDT,
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
	 * Возвращает список "Инвалидность по психическому заболеванию"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function getMorbusCrazyPersonInvalidViewData($data) {
		$filter = "(1=1)";
		if (isset($data['MorbusCrazyPersonInvalid_id']) and ($data['MorbusCrazyPersonInvalid_id'] > 0)) {
			$filter = "MM.MorbusCrazyPersonInvalid_id = :MorbusCrazyPersonInvalid_id";
		} else {
			if(empty($data['MorbusCrazy_id']) or $data['MorbusCrazy_id'] < 0) {
				return array();
			}
			$filter = "MC.MorbusCrazy_id = :MorbusCrazy_id";
		}
		$query = "
			select
				case when (coalesce(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as \"accessType\",
				MorbusCrazyPersonInvalid_id as \"MorbusCrazyPersonInvalid_id\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MCP.MorbusCrazyPerson_id as \"MorbusCrazyPerson_id\",
				to_char(MM.MorbusCrazyPersonInvalid_setDT, 'dd.mm.yyyy') as \"MorbusCrazyPersonInvalid_setDT\",
				MM.InvalidGroupType_id as \"InvalidGroupType_id\",
				InvalidGroupType.InvalidGroupType_Name as \"InvalidGroupType_Name\",
				to_char(MM.MorbusCrazyPersonInvalid_reExamDT, 'dd.mm.yyyy') as \"MorbusCrazyPersonInvalid_reExamDT\",
				MM.CrazyWorkPlaceType_id as \"CrazyWorkPlaceType_id\",
				CrazyWorkPlaceType.CrazyWorkPlaceType_Name as \"CrazyWorkPlaceType_Name\",
				:Evn_id as \"MorbusCrazy_pid\"
			from
				v_MorbusCrazyPersonInvalid MM
				inner join v_MorbusCrazyPerson MCP on MCP.MorbusCrazyPerson_id = MM.MorbusCrazyPerson_id
				inner join v_MorbusBase MB on MCP.Person_id = MB.Person_id
				inner join v_MorbusCrazy MC on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_InvalidGroupType InvalidGroupType on InvalidGroupType.InvalidGroupType_id = MM.InvalidGroupType_id
				left join v_CrazyWorkPlaceType CrazyWorkPlaceType on CrazyWorkPlaceType.CrazyWorkPlaceType_id = MM.CrazyWorkPlaceType_id
				left join v_Evn EvnEdit on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
			where
				{$filter}
		";
		$result = $this->db->query($query, $data);
		//echo getDebugSQL($query, $data); exit();
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет специфику "Инвалидность по психическому заболеванию"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusCrazyPersonInvalid($data) {
		$procedure = '';
		if ( (!isset($data['MorbusCrazyPersonInvalid_id'])) || ($data['MorbusCrazyPersonInvalid_id'] <= 0) ) {
			$procedure = 'p_MorbusCrazyPersonInvalid_ins';
		}
		else {
			$procedure = 'p_MorbusCrazyPersonInvalid_upd';
		}

		$query = "
			select
				MorbusCrazyPersonInvalid_id as \"MorbusCrazyPersonInvalid_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				MorbusCrazyPersonInvalid_id := :MorbusCrazyPersonInvalid_id,
				MorbusCrazyPerson_id := :MorbusCrazyPerson_id,
				MorbusCrazyPersonInvalid_setDT := :MorbusCrazyPersonInvalid_setDT,
				InvalidGroupType_id := :InvalidGroupType_id,
				MorbusCrazyPersonInvalid_reExamDT := :MorbusCrazyPersonInvalid_reExamDT,
				MorbusCrazyPersonInvalid_Article := :MorbusCrazyPersonInvalid_Article,
				CrazyWorkPlaceType_id := :CrazyWorkPlaceType_id,
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
	 * Метод получения данных по психиатрии
	 * При вызове из формы просмотра записи регистра параметр MorbusCrazy_pid будет содержать Person_id, также будет передан PersonRegister_id
	 * При вызове из формы просмотра движения/посещения параметр MorbusCrazy_pid будет содержать Evn_id просматриваемого движения/посещения
	 */
	function getMorbusCrazyViewData($data)
	{
		if (empty($data['session'])) { $data['session'] = null; }
		if (empty($data['MorbusCrazy_pid'])) { $data['MorbusCrazy_pid'] = null; }
		if (empty($data['PersonRegister_id'])) { $data['PersonRegister_id'] = null; }
		$this->load->library('swMorbus');
		$params = swMorbus::onBeforeViewData($this->getMorbusTypeSysNick($data), $data['session'], $data['MorbusCrazy_pid'], $data['PersonRegister_id']);
		if ($params['Error_Msg']) {
			throw new Exception($params['Error_Msg']);
		}
		$params['MorbusCrazy_pid'] = $data['MorbusCrazy_pid'];
		if (isset($data['PersonRegister_id'])) {
			$params['PersonRegister_id'] = $data['PersonRegister_id'];
			$pr_filter = ' AND PR.PersonRegister_id = :PersonRegister_id';
		} else {
			$pr_filter = '';
		}
		$region = "'".$this->getRegionNick()."'";

		//предусмотрено создание специфических учетных документов (в которых есть ссылка на посещение/движение из которого они созданы)
		$query = "
			select
				" . swMorbus::getAccessTypeQueryPart('M', 'MB', 'MorbusCrazy_pid', '1', '0', 'accessType', 'AND not exists(
									select Evn.Evn_id from v_Evn Evn
									where
										Evn.Person_id = MB.Person_id
										and Evn.Morbus_id = M.Morbus_id
										and Evn.EvnClass_id in (11,13,32)
										and Evn.Evn_id <> :MorbusCrazy_pid
										and Evn.Evn_setDT > EvnEdit.Evn_setDT
										and (
											exists (
												select v_MorbusCrazyDrugVolume.Evn_id
												from v_MorbusCrazyDrugVolume
												where v_MorbusCrazyDrugVolume.Evn_id = Evn.Evn_id
												limit 1
											)
											OR exists (
												select v_MorbusCrazyPersonSurveyHIV.Evn_id
												from v_MorbusCrazyPersonSurveyHIV
												where v_MorbusCrazyPersonSurveyHIV.Evn_id = Evn.Evn_id
												limit 1
											)
											OR exists (
												select v_MorbusCrazyForceTreat.Evn_id
												from v_MorbusCrazyForceTreat
												where v_MorbusCrazyForceTreat.Evn_id = Evn.Evn_id
												limit 1
											)
											OR exists (
												select v_MorbusCrazyBasePS.Evn_id
												from v_MorbusCrazyBasePS
												where v_MorbusCrazyBasePS.Evn_id = Evn.Evn_id
												limit 1
											)
										)
									limit 1
								) /* можно редактировать, если нет более актуального документа в рамках которого изменялась специфика */') . ",
				case
					when (MorbusCrazyBasePS.RecordCount >= 1 and EvnEdit.EvnClass_SysNick = 'EvnSection') then 0
					else 1
				end \"accessMorbusCrazyBasePS\",
				case
					when (MorbusCrazyForceTreat.RecordCount >= 1 and EvnEdit.EvnClass_SysNick = 'EvnSection') then 0
					else 1
				end \"accessMorbusCrazyForceTreat\",
				case
					when (MorbusCrazyPersonSurveyHIV.RecordCount >= 1 and EvnEdit.EvnClass_SysNick = 'EvnSection') then 0
					else 1
				end \"accessMorbusCrazyPersonSurveyHIV\",
				case
					when (MorbusCrazyDrugVolume.RecordCount >= 1 and EvnEdit.EvnClass_SysNick = 'EvnSection') then 0
					else 1
				end \"accessMorbusCrazyDrugVolume\",
				EvnEdit.Evn_id as \"Evn_id\",
				EvnEdit.Evn_pid as \"Evn_pid\",
				EvnEdit.EvnClass_SysNick as \"EvnClass_SysNick\",
				PR.PersonRegister_id as \"PersonRegister_id\",
				to_char(PR.PersonRegister_setDate, 'dd.mm.yyyy') as \"PersonRegister_setDate\",
				to_char(M.Morbus_setDT, 'dd.mm.yyyy') as \"Morbus_setDT\", -- Дата начала заболевания
				MC.Diag_nid as \"Diag_nid\",  --  Сопутствующее психическое (наркологическое) заболевание
				DiagNid.Diag_Code || '. ' || DiagNid.Diag_Name as \"Diag_nid_Name\",
				MC.Diag_sid as \"Diag_sid\",  --  Сопутствующее соматическое (в т.ч. неврологическое) заболевание
				DiagSid.Diag_Code || '. ' || DiagSid.Diag_Name as \"Diag_sid_Name\",
				MC.CrazyResultDeseaseType_id as \"CrazyResultDeseaseType_id\", -- Исход заболевания
				crdt.CrazyResultDeseaseType_Name as \"CrazyResultDeseaseType_id_Name\",
				case when {$region} = 'ufa' then to_char(FFF.MorbusCrazy_DeRegDT, 'dd.mm.yyyy') else to_char(M.Morbus_disDT, 'dd.mm.yyyy') end as \"Morbus_disDT\", -- Дата закрытия карты (снятия с учета)
				to_char(FFF.MorbusCrazy_CardEndDT, 'dd.mm.yyyy') as \"MorbusCrazy_CardEndDT\", -- Дата закрытия карты (снятия с учета) для Уфы
				MC.CrazyCauseEndSurveyType_id as \"CrazyCauseEndSurveyType_id\", -- причина прекращения наблюдения
				ccest.CrazyCauseEndSurveyType_Name as \"CrazyCauseEndSurveyType_id_Name\",
				MCB.MorbusCrazyBase_LTMDayCount as \"MorbusCrazyBase_LTMDayCount\", -- Число дней работы в ЛТМ
				MCB.MorbusCrazyBase_HolidayDayCount as \"MorbusCrazyBase_HolidayDayCount\", -- Число дней лечебных отпусков
				MCB.MorbusCrazyBase_HolidayCount as \"MorbusCrazyBase_HolidayCount\", -- Число лечебных отпусков (за период госпитализации)
				MCP.MorbusCrazyPerson_IsWowInvalid as \"MorbusCrazyPerson_IsWowInvalid\", -- Инвалид ВОВ
				IsWowInvalid.YesNo_Name as \"MorbusCrazyPerson_IsWowInvalid_Name\",
				MCP.MorbusCrazyPerson_IsWowMember as \"MorbusCrazyPerson_IsWowMember\", -- участник ВОВ
				IsWowMember.YesNo_Name as \"MorbusCrazyPerson_IsWowMember_Name\",
				cet.CrazyEducationType_id as \"CrazyEducationType_id\", -- Образование
				cet.CrazyEducationType_Name as \"CrazyEducationType_id_Name\",
				MCP.MorbusCrazyPerson_CompleteClassCount as \"MorbusCrazyPerson_CompleteClassCount\", --число законченных классов среднеобразовательного учреждения
				MCP.MorbusCrazyPerson_IsEducation as \"MorbusCrazyPerson_IsEducation\", -- учится
				IsEducation.YesNo_Name as \"MorbusCrazyPerson_IsEducation_Name\",
				cslt.CrazySourceLivelihoodType_id as \"CrazySourceLivelihoodType_id\", -- источник средств существования
				cslt.CrazySourceLivelihoodType_Name as \"CrazySourceLivelihoodType_id_Name\",
				crt.CrazyResideType_id as \"CrazyResideType_id\", -- проживает
				crt.CrazyResideType_Name as \"CrazyResideType_id_Name\",
				crct.CrazyResideConditionsType_id as \"CrazyResideConditionsType_id\", -- условия проживания
				crct.CrazyResideConditionsType_Name as \"CrazyResideConditionsType_id_Name\",
				to_char(MCB.MorbusCrazyBase_firstDT, 'dd.mm.yyyy') as \"MorbusCrazyBase_firstDT\", -- Дата обращения к психиатру (наркологу) впервые в жизни
				MCP.MorbusCrazyPerson_IsConvictionBeforePsych as \"MorbusCrazyPerson_IsConvictionBeforePsych\", -- судимости до обращения к психиатру (наркологу)
				IsConvictionBeforePsych.YesNo_Name as \"MorbusCrazyPerson_IsConvictionBeforePsych_Name\",
				to_char(MCB.MorbusCrazyBase_DeathDT, 'dd.mm.yyyy') as \"MorbusCrazyBase_DeathDT\", -- Дата смерти
				cdct.CrazyDeathCauseType_id as \"CrazyDeathCauseType_id\", -- Причина смерти
				cdct.CrazyDeathCauseType_Name as \"CrazyDeathCauseType_id_Name\",
				MCB.MorbusCrazyBase_IsUseAlienDevice as \"MorbusCrazyBase_IsUseAlienDevice\", -- Использование чужих шприцов, игл, приспособлений
				IsUseAlienDevice.YesNo_Name as \"MorbusCrazyBase_IsUseAlienDevice_Name\",
				MCB.MorbusCrazyBase_IsLivingConsumDrug as \"MorbusCrazyBase_IsLivingConsumDrug\", -- Проживание с потребителем психоактивных средств
				IsLivingConsumDrug.YesNo_Name as \"MorbusCrazyBase_IsLivingConsumDrug_Name\",
				MC.MorbusCrazy_id as \"MorbusCrazy_id\",
				MC.Morbus_id as \"Morbus_id\",
				MB.MorbusBase_id as \"MorbusBase_id\",
				MCB.MorbusCrazyBase_id as \"MorbusCrazyBase_id\",
				MCP.MorbusCrazyPerson_id as \"MorbusCrazyPerson_id\",
				:MorbusCrazy_pid as \"MorbusCrazy_pid\",
				MB.Person_id as \"Person_id\",
				M.Diag_id as \"Diag_id\",
				case when MT.MorbusType_SysNick ilike 'narc' then 'narko' else 'psycho' end as \"Diagtype\",
				M.MorbusType_id as \"MorbusType_id\",
				{$region} as \"regionNick\"
			from
				v_Morbus M
				inner join v_MorbusBase MB on MB.MorbusBase_id = M.MorbusBase_id
				left join v_MorbusCrazyBase MCB on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC on MC.Morbus_id = M.Morbus_id
				left join lateral (
					select * from  v_MorbusCrazyPerson MCP where MB.Person_id = MCP.Person_id order by MorbusCrazyPerson_insDT asc limit 1
				) MCP on true
				left join lateral (
					select PR.PersonRegister_id, PR.PersonRegister_setDate  from  v_PersonRegister PR
					where PR.Person_id = MB.Person_id and PR.Morbus_id = M.Morbus_id {$pr_filter}
					order by PersonRegister_disDate ASC, PersonRegister_setDate DESC
					limit 1
				) PR on true
				left join lateral (
					select 
						FF.MorbusCrazy_CardEndDT
						,FF.MorbusCrazy_DeRegDT  
					from v_MorbusCrazy FF 
					where FF.Morbus_id = M.Morbus_id
					limit 1
				) FFF on true
				left join v_MorbusType MT on MT.MorbusType_id = M.MorbusType_id
				left join Diag DiagNid on DiagNid.Diag_id = MC.Diag_nid
				left join Diag DiagSid on DiagSid.Diag_id = MC.Diag_sid
				left join Diag DiagM on DiagM.Diag_id = M.Diag_id
				left join v_CrazyResultDeseaseType crdt on crdt.CrazyResultDeseaseType_id = MC.CrazyResultDeseaseType_id
				left join v_CrazyCauseEndSurveyType ccest on ccest.CrazyCauseEndSurveyType_id = MC.CrazyCauseEndSurveyType_id
				left join v_YesNo IsWowInvalid on MCP.MorbusCrazyPerson_IsWowInvalid = IsWowInvalid.YesNo_id
				left join v_YesNo IsWowMember on MCP.MorbusCrazyPerson_IsWowMember = IsWowMember.YesNo_id
				left join v_CrazyEducationType cet on cet.CrazyEducationType_id = MCP.CrazyEducationType_id
				left join v_YesNo IsEducation on MCP.MorbusCrazyPerson_IsEducation = IsEducation.YesNo_id
				left join v_CrazySourceLivelihoodType cslt on cslt.CrazySourceLivelihoodType_id = MCP.CrazySourceLivelihoodType_id
				left join v_CrazyResideType crt on crt.CrazyResideType_id = MCP.CrazyResideType_id
				left join v_CrazyResideConditionsType crct on crct.CrazyResideConditionsType_id = MCP.CrazyResideConditionsType_id
				left join v_YesNo IsConvictionBeforePsych on MCP.MorbusCrazyPerson_IsConvictionBeforePsych = IsConvictionBeforePsych.YesNo_id
				left join v_CrazyDeathCauseType cdct on cdct.CrazyDeathCauseType_id = MCB.CrazyDeathCauseType_id
				left join v_YesNo IsUseAlienDevice on MCB.MorbusCrazyBase_IsUseAlienDevice = IsUseAlienDevice.YesNo_id
				left join v_YesNo IsLivingConsumDrug on MCB.MorbusCrazyBase_IsLivingConsumDrug = IsLivingConsumDrug.YesNo_id
				left join lateral (
					Select count(*) as RecordCount from v_MorbusCrazyBasePS MM where MM.MorbusCrazyBase_id = MCB.MorbusCrazyBase_id and MM.Evn_id = :MorbusCrazy_pid
				) MorbusCrazyBasePS on true
				left join lateral (
					Select count(*) as RecordCount from v_MorbusCrazyForceTreat MM where MM.MorbusCrazyBase_id = MCB.MorbusCrazyBase_id and MM.Evn_id = :MorbusCrazy_pid
				) MorbusCrazyForceTreat on true
				
				left join lateral (
					Select count(*) as RecordCount from v_MorbusCrazyPersonSurveyHIV MM where MM.MorbusCrazyPerson_id = MCP.MorbusCrazyPerson_id and MM.Evn_id = :MorbusCrazy_pid
				) MorbusCrazyPersonSurveyHIV on true
				
				left join lateral (
					Select count(*) as RecordCount from v_MorbusCrazyDrugVolume MM where MM.MorbusCrazyBase_id = MCB.MorbusCrazyBase_id and MM.Evn_id = :MorbusCrazy_pid
				) MorbusCrazyDrugVolume on true

				left join v_Evn EvnEdit on EvnEdit.Evn_id = :MorbusCrazy_pid and MB.Person_id != :MorbusCrazy_pid
				--left join EvnClass on EvnClass.EvnClass_id = EvnEdit.EvnClass_id

			where
				M.Morbus_id = :Morbus_id
			limit 1
		";
		//echo getDebugSql($query, $params);die();
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
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
        $not_edit_fields = array('Evn_pid', 'Person_id','MorbusCrazy_id', 'Morbus_id', 'MorbusBase_id','MorbusType_id','MorbusBase_setDT','MorbusBase_disDT');
		if(isset($data['field_notedit_list']) && is_array($data['field_notedit_list']))
		{
			$not_edit_fields = array_merge($not_edit_fields,$data['field_notedit_list']);
		}
		if ($data['Mode'] == 'personregister_viewform'
			&& (empty($data['PersonRegister_id']) || empty($data['PersonRegister_setDate']))
		) {
			$entity_saved_arr['Error_Msg'] = 'Дата включения в регистр не может быть пустой';
			return array($entity_saved_arr);
		}
		if ($data['Mode'] == 'personregister_viewform') {
			$this->load->model('PersonRegister_model', 'PersonRegister_model');
			$this->PersonRegister_model->setSessionParams($data['session']);
			$this->PersonRegister_model->setPersonRegister_id($data['PersonRegister_id']);
			if ($this->PersonRegister_model->load()) {
				$this->PersonRegister_model->setPersonRegister_setDate($data['PersonRegister_setDate']);
				$result = $this->PersonRegister_model->save();
				if (empty($result)) {
					$entity_saved_arr['Error_Msg'] = 'Запись регистра не сохранилась';
					return array($entity_saved_arr);
				}
				if (isset($result[0]['Error_Msg'])) {
					return $result;
				}
			} else {
				$entity_saved_arr['Error_Msg'] = 'Запись регистра не читается';
				return array($entity_saved_arr);
			}
			$entity_saved_arr['PersonRegister_id'] = $data['PersonRegister_id'];
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
				$fields = [];
				foreach($l_arr as $field) {
					$fields[] = "{$field} as \"{$field}\"";
				}
				$q = 'select '. implode(', ', $fields) .' from dbo.v_'. $entity .' where '. $entity .'_id = :'. $entity .'_id limit 1';

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
						{$entity}_id := :{$entity}_id,
						{$field_str}
						pmUser_id := :pmUser_id
					)
				";
				$p['pmUser_id'] = $data['pmUser_id'];
				//echo getDebugSQL($q, $p);exit;
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
		$entity_saved_arr['Morbus_id'] = $data['Morbus_id'];
		$entity_saved_arr['Error_Msg'] = (count($err_arr) > 0) ? implode('<br />',$err_arr) : null;
		return array($entity_saved_arr);
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getCrazyDiag($data) {
		$filter = "(1=1)";
		if ( !empty($data['query']) ) {
			$filter .= " and (CrazyDiag_Code ilike :query||'%'  or CrazyDiag_Name ilike '%'||:query||'%')";
		}
		else {
			if ( !empty($data['CrazyDiag_id']) ) {
				$filter .= " and CrazyDiag_id = :CrazyDiag_id";
			}
			/*
			 if (strlen($data['CrazyDiag_Name'])>0) {
				$filter .= " and CrazyDiag_Name like :CrazyDiag_Name||'%'";
			}*/
		}
		if($data['type']=='narko'){
			$filter .=" and CrazyDiag_Code ilike 'F1%'";
		}else{
			$filter .=" and CrazyDiag_Code not ilike 'F1%'";
		}

		if ( !empty($data['date']) ) {
			$filter .= "
				and coalesce(CrazyDiag_begDate, :date) <= :date
				and coalesce(CrazyDiag_endDate, :date) >= :date
			";
		}

		$query = "
			select
				CrazyDiag_id as \"CrazyDiag_id\",
				Diag_id as \"Diag_id\",
				CrazyDiag_Code as \"CrazyDiag_Code\",
				CrazyDiag_Name as \"CrazyDiag_Name\",
				to_char( CrazyDiag_begDate, 'dd.mm.yyyy') as \"CrazyDiag_begDate\",
				to_char( CrazyDiag_endDate, 'dd.mm.yyyy') as \"CrazyDiag_endDate\"
			from
				v_CrazyDiag
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
	 * Получение данных по специфике психиатрии, наркологии. Метод для API.
	 */
	function getMorbusCrazyPersonForAPI($data) {
		$queryParams = array();
		$filter = "";

		if (!empty($data['Person_id'])) {
			$filter .= " and mb.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		if (!empty($data['Evn_pid'])) {
			$filter .= " and mb.Evn_pid = :Evn_pid";
			$queryParams['Evn_pid'] = $data['Evn_pid'];
		}
		if (!empty($data['MorbusType']) && $data['MorbusType'] == 1) {
			$filter .= " and (
				(d.Diag_Code >= 'F00.0' and d.Diag_Code < 'F10.0')
				or
				(d.Diag_Code >= 'F20.0' and d.Diag_Code <= 'F99.9')
			)";
		} else if (!empty($data['MorbusType']) && $data['MorbusType'] == 2) {
			$filter .= "
				and d.Diag_Code >= 'F10.0'
				and d.Diag_Code <= 'F19.9'
			";
		}

		if (empty($filter)) {
			return array();
		}

		return $this->queryResult("
			select
				mb.Person_id as \"Person_id\",
				mb.Evn_pid as \"Evn_pid\",
				mb.MorbusBase_id as \"MorbusBase_id\",
				case when d.Diag_Code >= 'F10.0' and d.Diag_Code <= 'F19.9' then 2 else 1 end as \"MorbusType\",
				-- Данные из регистра по психиатрии/наркологии (MorbusType_id=4): 
				PR.PersonRegister_id as \"PersonRegister_id\",
				PR.Diag_id as \"Diag_id\",
				to_char( PR.PersonRegister_setDT, 'yyyy-mm-dd') as \"PersonRegister_setDT\",
				--- Данные из специфики психиатрии/наркологии:
				mcp.MorbusCrazyPerson_id as \"MorbusCrazyPerson_id\",
				mcp.Person_id as \"Person_id\",
				case when mcp.MorbusCrazyPerson_IsWowInvalid = 2 then 1 else 0 end as \"MorbusCrazyPerson_IsWowInvalid\",
				case when mcp.MorbusCrazyPerson_IsWowMember = 2 then 1 else 0 end as \"MorbusCrazyPerson_IsWowMember\",
				mcp.CrazyEducationType_id as \"CrazyEducationType_id\",
				mcp.MorbusCrazyPerson_CompleteClassCount as \"MorbusCrazyPerson_CompleteClassCount\",
				case when mcp.MorbusCrazyPerson_IsEducation = 2 then 1 else 0 end as \"MorbusCrazyPerson_IsEducation\",
				mcp.CrazySourceLivelihoodType_id as \"CrazySourceLivelihoodType_id\",
				mcp.CrazyResideType_id as \"CrazyResideType_id\",
				mcp.CrazyResideConditionsType_id as \"CrazyResideConditionsType_id\",
				case when mcp.MorbusCrazyPerson_IsConvictionBeforePsych = 2 then 1 else 0 end as \"MorbusCrazyPerson_IsConvictionBeforePsych\",
				--- Данные по диагнозу (используется запись с максимальной MorbusCrazyDiag_setDT):
				mcd.MorbusCrazyDiag_id as \"MorbusCrazyDiag_id\",
				mcd.MorbusCrazy_id as \"MorbusCrazy_id\",
				to_char( mcd.MorbusCrazyDiag_setDT, 'yyyy-mm-dd') as \"MorbusCrazyDiag_setDT\",
				mcd.CrazyDiag_id as \"CrazyDiag_id\",
				mcd.Diag_sid as \"Diag_sid\",
				--- Данные по инвалидности (используется запись с максимальной MorbusCrazyPersonInvalid_setDT):
				mcpi.MorbusCrazyPersonInvalid_id as \"MorbusCrazyPersonInvalid_id\",
				to_char( mcpi.MorbusCrazyPersonInvalid_setDT, 'yyyy-mm-dd') as \"MorbusCrazyPersonInvalid_setDT\",
				mcpi.InvalidGroupType_id as \"InvalidGroupType_id\",
				mcpi.MorbusCrazyPersonInvalid_reExamDT as \"MorbusCrazyPersonInvalid_reExamDT\",
				mcpi.MorbusCrazyPersonInvalid_Article as \"MorbusCrazyPersonInvalid_Article\",
				mcpi.CrazyWorkPlaceType_id as \"CrazyWorkPlaceType_id\"
			from
				v_MorbusBase mb
				inner join v_MorbusCrazyBase mcb on mcb.MorbusBase_id = mb.MorbusBase_id
				inner join v_Morbus m on m.MorbusBase_id = mb.MorbusBase_id
				inner join v_Diag d on d.Diag_id = m.Diag_id
				inner join v_MorbusCrazy mc on mc.Morbus_id = m.Morbus_id
				left join lateral (
					select * from v_MorbusCrazyPerson MCP where MB.Person_id = MCP.Person_id order by MorbusCrazyPerson_insDT asc limit 1
				) mcp on true
				left join lateral (
					select * from v_MorbusCrazyDiag MCD where MCD.MorbusCrazy_id = mc.MorbusCrazy_id order by MorbusCrazyDiag_setDT desc limit 1
				) mcd on true
				left join lateral (
					select * from v_MorbusCrazyPersonInvalid MCPI where MCPI.MorbusCrazyPerson_id = mcp.MorbusCrazyPerson_id order by MorbusCrazyPersonInvalid_setDT desc limit 1
				) mcpi on true
				left join lateral (
					select
						PR.PersonRegister_id,
						PR.Diag_id,
						PR.PersonRegister_setDate as PersonRegister_setDT
					from
					 	v_PersonRegister PR
					where
						PR.Person_id = MB.Person_id
						and PR.Morbus_id = M.Morbus_id
					order by
						PersonRegister_disDate ASC,
						PersonRegister_setDate DESC
					limit 1
				) PR on true
			where
				1=1
				{$filter}
		", $queryParams);
	}

	/**
	 * Получение данных по употребляемым психоактивным веществам. Метод для API.
	 */
	function getMorbusCrazyDrugForAPI($data) {
		$queryParams = array();
		$filter = "";

		if (!empty($data['MorbusBase_id'])) {
			$filter .= " and mcb.MorbusBase_id = :MorbusBase_id";
			$queryParams['MorbusBase_id'] = $data['MorbusBase_id'];
		}

		if (empty($filter)) {
			return array();
		}

		return $this->queryResult("
			select
				mcd.MorbusCrazyDrug_id as \"MorbusCrazyDrug_id\",
				mcd.MorbusCrazyBase_id as \"MorbusCrazyBase_id\",
				mcd.MorbusCrazyDrug_Name as \"MorbusCrazyDrug_Name\",
				mcd.CrazyDrugType_id as \"CrazyDrugType_id\",
				mcd.CrazyDrugReceptType_id as \"CrazyDrugReceptType_id\"
			from
				v_MorbusCrazyDrug mcd
				inner join v_MorbusCrazyBase mcb on mcb.MorbusCrazyBase_id = mcd.MorbusCrazyBase_id
			where
				1=1
				{$filter}
		", $queryParams);
	}
}
