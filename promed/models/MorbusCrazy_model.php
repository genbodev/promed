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

class MorbusCrazy_model extends swModel
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
				select top 1 MorbusType_SysNick from v_MorbusType with(nolock) where MorbusType_id = :MorbusType_id
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
	 */
	public function setCauseEndSurveyType($data){
		$query = "
			select top 1
				MorbusCrazy_id,
				Diag_nid,
				Diag_sid,
				CrazyResultDeseaseType_id,
				CrazyCauseEndSurveyType_id
			from v_MorbusCrazy with (nolock)
			where Morbus_id = :Morbus_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ){
			$result = $result->result('array');
			$data['Diag_nid']=$result[0]['Diag_nid'];
			$data['Diag_sid']=$result[0]['Diag_sid'];
			$data['CrazyResultDeseaseType_id']=$result[0]['CrazyResultDeseaseType_id'];
			$data['MorbusCrazy_id']=$result[0]['MorbusCrazy_id'];
			if(!isset($data['MorbusCrazy_CardEndDT']))
				$data['MorbusCrazy_CardEndDT'] = null;
			if(!isset($data['MorbusCrazy_DeRegDT']))
				$data['MorbusCrazy_DeRegDT'] = null;
			$query = '
				declare
					@MorbusCrazy_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @MorbusCrazy_id = :MorbusCrazy_id;
				exec dbo.p_MorbusCrazy_upd
					@MorbusCrazy_id = @MorbusCrazy_id output,
					@Diag_nid = :Diag_nid,
					@Diag_sid = :Diag_sid,
					@CrazyResultDeseaseType_id = :CrazyResultDeseaseType_id,
					@CrazyCauseEndSurveyType_id = :CrazyCauseEndSurveyType_id,
					@MorbusCrazy_CardEndDT = :MorbusCrazy_CardEndDT,
					@MorbusCrazy_DeRegDT = :MorbusCrazy_DeRegDT,
					@Morbus_id = :Morbus_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @MorbusCrazy_id as MorbusCrazy_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';
			$res = $this->db->query($query, $data);
			if (is_object($res)) {
				$res = $res->result('array');
			} else {
				$res = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
			}
		} else {
			$res = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $res;
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
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				
				@MorbusBase_id bigint = :MorbusBase_id,
				@Person_id bigint = :Person_id,
				
				@Morbus_id bigint = :Morbus_id,
				@Diag_id bigint = :Diag_id,
				@Morbus_setDT datetime = :Morbus_setDT,
				
				@{$tableName}_id bigint = null,
				@MorbusCrazyPerson_id bigint = null,
				@MorbusCrazyBase_id bigint = null,
				@pmUser_id bigint = :pmUser_id,
				@Evn_pid bigint = :Evn_pid;

			-- должно быть одно на человека
			select top 1 @MorbusCrazyPerson_id = MorbusCrazyPerson_id from v_MorbusCrazyPerson with (nolock) where Person_id = @Person_id

			if isnull(@MorbusCrazyPerson_id, 0) = 0
			begin
				exec p_MorbusCrazyPerson_ins
					@MorbusCrazyPerson_id = @MorbusCrazyPerson_id output,
					@Person_id = @Person_id,
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
			end

			-- должно быть одно на MorbusBase
			select top 1 @MorbusCrazyBase_id = MorbusCrazyBase_id from v_MorbusCrazyBase with (nolock) where MorbusBase_id = @MorbusBase_id

			if isnull(@MorbusCrazyBase_id, 0) = 0
			begin
				exec p_MorbusCrazyBase_ins
					@MorbusCrazyBase_id = @MorbusCrazyBase_id output,
					@MorbusBase_id = @MorbusBase_id,
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
			end

			-- должно быть одно на Morbus
			select top 1 @{$tableName}_id = {$tableName}_id from v_{$tableName} with (nolock) where Morbus_id = @Morbus_id

			if isnull(@{$tableName}_id, 0) = 0
			begin
				exec p_{$tableName}_ins
					@{$tableName}_id = @{$tableName}_id output,
					@Morbus_id = @Morbus_id,
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @{$tableName}_id as {$tableName}_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end
			else
			begin
				select @{$tableName}_id as {$tableName}_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end
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
		if (true) {
			$queryParams = array();
			$queryParams['pmUser_id'] = $this->promedUserId;
			$queryParams['MorbusCrazyDiag_id'] = null;
			$queryParams['MorbusCrazy_id'] = $this->_saveResponse['MorbusCrazy_id'];
			$queryParams['MorbusCrazyDiag_setDT'] = $data['Morbus_setDT'];
			
			$queryParams['CrazyDiag_id'] = $this->getFirstResultFromQuery('
				select top 1 CrazyDiag_id 
				from v_CrazyDiag with (nolock) 
				where 
					Diag_id=:Diag_id
					and (CrazyDiag_endDate is null or CrazyDiag_endDate >= :nowDate)
					and (CrazyDiag_begDate is null or CrazyDiag_begDate <= :nowDate)
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
		$query = "select top 1
			ps.Server_id
			,ps.Person_id
			,ps.PersonEvn_id
			,ps.Person_SurName
			,ps.Person_FirName
			,ps.Person_SecName
			,DATEDIFF(SECOND, '1970', ps.Person_BirthDay) as Person_BirthDay
			from v_PersonState ps with(nolock)
			inner join v_PersonRegister pr with(nolock) on pr.Person_id = ps.Person_id
			inner join v_MorbusType mt with(nolock) on pr.MorbusType_id = mt.MorbusType_id
			where 
				ps.Person_deadDT is not null 
				and pr.PersonRegister_disDate is null
				and mt.MorbusType_SysNick = 'Crazy'
				and ps.Person_id = ?";
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
			PMUser_id 
		from 
			v_pmUserCache with(nolock)
		where 
			pmUser_groups like '%\"Crazy\"%'
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				case when (MainRec.MorbusCrazyDiag_id = MCD.MorbusCrazyDiag_id) then 1 else 0 end as isMain,
				MCD.MorbusCrazyDiag_id,
				MCD.MorbusCrazyDiagDepend_id,
				MC.MorbusCrazy_id,
				convert(varchar(10),MCD.MorbusCrazyDiag_setDT,104) as MorbusCrazyDiag_setDT,
				MCD.CrazyDiag_id,
				CrazyDiagId.CrazyDiag_Code + '. ' + CrazyDiagId.CrazyDiag_Name as CrazyDiag_id_Name,
				MCD.Diag_sid,
				CrazyDiagSid.Diag_Code + '. ' + CrazyDiagSid.Diag_Name as Diag_sid_Name,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyDiag MCD with (nolock)
				inner join v_MorbusCrazy MC with (nolock) on MC.MorbusCrazy_id = MCD.MorbusCrazy_id
				inner join v_MorbusBase MB with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_CrazyDiag CrazyDiagId (nolock) on CrazyDiagId.CrazyDiag_id = MCD.CrazyDiag_id
				left join v_Diag CrazyDiagSid (nolock) on CrazyDiagSid.Diag_id = MCD.Diag_sid
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
				outer apply (select top 1 MorbusCrazyDiag_id from v_MorbusCrazyDiag with (nolock) where MorbusCrazy_id = MCD.MorbusCrazy_id order by MorbusCrazyDiag_insDT) MainRec
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyDiag_id;
			exec " . $procedure . "
				@MorbusCrazyDiag_id = @Res output,
				@MorbusCrazy_id = :MorbusCrazy_id,
				@MorbusCrazyDiag_setDT = :MorbusCrazyDiag_setDT,
				@CrazyDiag_id = :CrazyDiag_id,
				@MorbusCrazyDiagDepend_id = :MorbusCrazyDiagDepend_id,
				@Diag_sid = :Diag_sid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyDiag_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MC.MorbusCrazy_id,
				MCDO.MorbusCrazyDynamicsObserv_id,
				MCDO.CrazyAmbulMonitoringType_id,
				CrazyAmbulMonitoringType.CrazyAmbulMonitoringType_Name,
				MCDO.Lpu_sid,
				Lpu.Lpu_Nick,
				convert(varchar(10),MCDO.MorbusCrazyDynamicsObserv_setDT,104) as MorbusCrazyDynamicsObserv_setDT,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyDynamicsObserv MCDO with (nolock)
				inner join v_MorbusCrazy MC with (nolock) on MC.MorbusCrazy_id = MCDO.MorbusCrazy_id
				inner join v_MorbusBase MB with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_CrazyAmbulMonitoringType CrazyAmbulMonitoringType (nolock) on CrazyAmbulMonitoringType.CrazyAmbulMonitoringType_id = MCDO.CrazyAmbulMonitoringType_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = MCDO.Lpu_sid
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyDynamicsObserv_id;
			exec " . $procedure . "
				@MorbusCrazyDynamicsObserv_id = @Res output,
				@MorbusCrazy_id = :MorbusCrazy_id,
				@Lpu_sid = :Lpu_sid,
				@CrazyAmbulMonitoringType_id = :CrazyAmbulMonitoringType_id,
				@MorbusCrazyDynamicsObserv_setDT = :MorbusCrazyDynamicsObserv_setDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyDynamicsObserv_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MM.MorbusCrazyVizitCheck_id,
				MC.MorbusCrazy_id,
				convert(varchar(10),MM.MorbusCrazyVizitCheck_setDT,104) as MorbusCrazyVizitCheck_setDT,
				convert(varchar(10),MM.MorbusCrazyVizitCheck_vizitDT,104) as MorbusCrazyVizitCheck_vizitDT,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyVizitCheck MM with (nolock)
				inner join v_MorbusCrazy MC with (nolock) on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				inner join v_MorbusBase MB with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyVizitCheck_id;
			exec " . $procedure . "
				@MorbusCrazyVizitCheck_id = @Res output,
				@MorbusCrazy_id = :MorbusCrazy_id,
				@MorbusCrazyVizitCheck_setDT = :MorbusCrazyVizitCheck_setDT,
				@MorbusCrazyVizitCheck_vizitDT = :MorbusCrazyVizitCheck_vizitDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyVizitCheck_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MM.MorbusCrazyDynamicsState_id,
				MC.MorbusCrazy_id,
				convert(varchar(10),MM.MorbusCrazyDynamicsState_begDT,104) as MorbusCrazyDynamicsState_begDT,
				convert(varchar(10),MM.MorbusCrazyDynamicsState_endDT,104) as MorbusCrazyDynamicsState_endDT,
				DATEDIFF(day, MorbusCrazyDynamicsState_begDT, IsNull(MorbusCrazyDynamicsState_endDT, GetDate())) as MorbusCrazyDynamicsState_Count, --Длительность ремисии
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyDynamicsState MM with (nolock)
				inner join v_MorbusCrazy MC with (nolock) on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				inner join v_MorbusBase MB with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyDynamicsState_id;
			exec " . $procedure . "
				@MorbusCrazyDynamicsState_id = @Res output,
				@MorbusCrazy_id = :MorbusCrazy_id,
				@MorbusCrazyDynamicsState_begDT = :MorbusCrazyDynamicsState_begDT,
				@MorbusCrazyDynamicsState_endDT = :MorbusCrazyDynamicsState_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyDynamicsState_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MorbusCrazyBasePS_id,
				MC.MorbusCrazy_id,
				MCB.MorbusCrazyBase_id,
				MM.Evn_id,
				case
					when IsNull(MM.Evn_id,0) = EvnEdit.Evn_id and EvnClass_SysNick = 'EvnSection' then 1
					when MM.Evn_id is null and IsNull(EvnClass_SysNick,'') != 'EvnSection' then 1
					else 0
				end accessEvn,
				MM.CrazyPurposeHospType_id,
				MM.CrazyPurposeDirectType_id,
				CrazyPurposeHospType.CrazyPurposeHospType_Name,
				convert(varchar(10),MM.MorbusCrazyBasePS_setDT,104) as MorbusCrazyBasePS_setDT,
				convert(varchar(10),MM.MorbusCrazyBasePS_disDT,104) as MorbusCrazyBasePS_disDT,
				MM.CrazyDiag_id,
				CrazyDiagId.CrazyDiag_Code + '. ' + CrazyDiagId.CrazyDiag_Name as CrazyDiag_id_Name,
				MM.Diag_id,
				DiagId.Diag_Code + '. ' + DiagId.Diag_Name as Diag_id_Name,
				MM.Lpu_id,
				Lpu.Lpu_Nick,
				CrazyHospType_id,
				CrazySupplyType_id,
				CrazyDirectType_id,
				CrazySupplyOrderType_id,
				CrazyDirectFromType_id,
				CrazyJudgeDecisionArt35Type_id,
				CrazyLeaveType_id,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyBasePS MM with (nolock)
				inner join v_MorbusCrazyBase MCB with (nolock) on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB with (nolock) on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC with (nolock) on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_CrazyPurposeHospType CrazyPurposeHospType (nolock) on CrazyPurposeHospType.CrazyPurposeHospType_id = MM.CrazyPurposeHospType_id
				left join v_CrazyDiag CrazyDiagId (nolock) on CrazyDiagId.CrazyDiag_id = MM.CrazyDiag_id
				left join v_Diag DiagId (nolock) on DiagId.Diag_id = MM.Diag_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = MM.Lpu_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyBasePS_id;
			exec " . $procedure . "
				@MorbusCrazyBasePS_id = @Res output,
				@MorbusCrazyBase_id = :MorbusCrazyBase_id,
				@Evn_id = :Evn_id,
				@CrazyPurposeHospType_id = :CrazyPurposeHospType_id,
				@CrazyPurposeDirectType_id = :CrazyPurposeDirectType_id,
				@MorbusCrazyBasePS_setDT = :MorbusCrazyBasePS_setDT,
				@MorbusCrazyBasePS_disDT = :MorbusCrazyBasePS_disDT,
				@CrazyDiag_id = :CrazyDiag_id,
				@Diag_id = :Diag_id,
				@Lpu_id = :Lpu_id,
				@CrazyHospType_id = :CrazyHospType_id,
				@CrazySupplyType_id = :CrazySupplyType_id,
				@CrazyDirectType_id = :CrazyDirectType_id,
				@CrazySupplyOrderType_id = :CrazySupplyOrderType_id,
				@CrazyDirectFromType_id = :CrazyDirectFromType_id,
				@CrazyJudgeDecisionArt35Type_id = :CrazyJudgeDecisionArt35Type_id,
				@CrazyLeaveType_id = :CrazyLeaveType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyBasePS_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				case
					when IsNull(MM.Evn_id,0) = EvnEdit.Evn_id and EvnClass_SysNick = 'EvnSection' then 1
					when MM.Evn_id is null and IsNull(EvnClass_SysNick,'') != 'EvnSection' then 1
					else 0
				end accessEvn,
				MorbusCrazyForceTreat_id,
				MM.CrazyForceTreatType_id,
				MC.MorbusCrazy_id,
				MCB.MorbusCrazyBase_id,
				CrazyForceTreatType.CrazyForceTreatType_Name,
				convert(varchar(10),MM.MorbusCrazyForceTreat_begDT,104) as MorbusCrazyForceTreat_begDT,
				convert(varchar(10),MM.MorbusCrazyForceTreat_endDT,104) as MorbusCrazyForceTreat_endDT,
				--MM.Lpu_id,
				--Lpu.Lpu_Nick,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyForceTreat MM with (nolock)
				inner join v_MorbusCrazyBase MCB with (nolock) on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB with (nolock) on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC with (nolock) on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_CrazyForceTreatType CrazyForceTreatType (nolock) on CrazyForceTreatType.CrazyForceTreatType_id = MM.CrazyForceTreatType_id
				--left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = MC.Lpu_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id

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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyForceTreat_id;
			exec " . $procedure . "
				@MorbusCrazyForceTreat_id = @Res output,
				@MorbusCrazyBase_id = :MorbusCrazyBase_id,
				@MorbusCrazyForceTreat_begDT = :MorbusCrazyForceTreat_begDT,
				@MorbusCrazyForceTreat_endDT = :MorbusCrazyForceTreat_endDT,
				@CrazyForceTreatType_id = :CrazyForceTreatType_id,
				@Evn_id = :Evn_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyForceTreat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MM.MorbusCrazyUpForceTreat_id,
				MM.CrazyForceTreatType_id,
				MC.MorbusCrazy_id,
				MCB.MorbusCrazyBase_id,
				MM.MorbusCrazyForceTreat_id,
				CrazyForceTreatType.CrazyForceTreatType_Name,
				convert(varchar(10),MM.MorbusCrazyUpForceTreat_setDT,104) as MorbusCrazyUpForceTreat_setDT,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyUpForceTreat MM with (nolock)
				inner join v_MorbusCrazyForceTreat MCFT with (nolock) on MCFT.MorbusCrazyForceTreat_id = MM.MorbusCrazyForceTreat_id
				inner join v_MorbusCrazyBase MCB with (nolock) on MCB.MorbusCrazyBase_id = MCFT.MorbusCrazyBase_id
				inner join v_MorbusBase MB with (nolock) on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC with (nolock) on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_CrazyForceTreatType CrazyForceTreatType (nolock) on CrazyForceTreatType.CrazyForceTreatType_id = MM.CrazyForceTreatType_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyUpForceTreat_id;
			exec " . $procedure . "
				@MorbusCrazyUpForceTreat_id = @Res output,
				@MorbusCrazyForceTreat_id = :MorbusCrazyForceTreat_id,
				@MorbusCrazyUpForceTreat_setDT = :MorbusCrazyUpForceTreat_setDT,
				@CrazyForceTreatType_id = :CrazyForceTreatType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyUpForceTreat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MorbusCrazyNdOsvid_id,
				MC.MorbusCrazy_id,
				MCB.MorbusCrazyBase_id,
				convert(varchar(10),MM.MorbusCrazyNdOsvid_setDT,104) as MorbusCrazyNdOsvid_setDT,
				MM.Lpu_id,
				Lpu.Lpu_Nick,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyNdOsvid MM with (nolock)
				inner join v_MorbusCrazyBase MCB with (nolock) on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB with (nolock) on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC with (nolock) on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = MM.Lpu_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyNdOsvid_id;
			exec " . $procedure . "
				@MorbusCrazyNdOsvid_id = @Res output,
				@MorbusCrazyBase_id = :MorbusCrazyBase_id,
				@MorbusCrazyNdOsvid_setDT = :MorbusCrazyNdOsvid_setDT,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyNdOsvid_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				case
					when IsNull(MM.Evn_id,0) = EvnEdit.Evn_id and EvnClass_SysNick = 'EvnSection' then 1
					when MM.Evn_id is null and IsNull(EvnClass_SysNick,'') != 'EvnSection' then 1
					else 0
				end accessEvn,
				MorbusCrazyPersonSurveyHIV_id,
				MC.MorbusCrazy_id,
				MCP.MorbusCrazyPerson_id,
				convert(varchar(10),MM.MorbusCrazyPersonSurveyHIV_setDT,104) as MorbusCrazyPersonSurveyHIV_setDT,
				MM.CrazySurveyHIVType_id,
				CrazySurveyHIVType.CrazySurveyHIVType_Name,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyPersonSurveyHIV MM with (nolock)
				inner join v_MorbusCrazyPerson MCP with (nolock) on MCP.MorbusCrazyPerson_id = MM.MorbusCrazyPerson_id
				inner join v_MorbusBase MB with (nolock) on MCP.Person_id = MB.Person_id
				inner join v_MorbusCrazy MC with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_CrazySurveyHIVType CrazySurveyHIVType (nolock) on CrazySurveyHIVType.CrazySurveyHIVType_id = MM.CrazySurveyHIVType_id
				--!!inner join v_MorbusCrazy MC with (nolock) on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				--!!inner join v_MorbusBase MB with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				--left join v_Diag CrazyDiagId (nolock) on CrazyDiagId.Diag_id = MM.CrazyDiag_id
				--left join v_Diag DiagId (nolock) on DiagId.Diag_id = MM.CrazyDiag_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyPersonSurveyHIV_id;
			exec " . $procedure . "
				@MorbusCrazyPersonSurveyHIV_id = @Res output,
				@MorbusCrazyPerson_id = :MorbusCrazyPerson_id,
				@MorbusCrazyPersonSurveyHIV_setDT = :MorbusCrazyPersonSurveyHIV_setDT,
				@CrazySurveyHIVType_id = :CrazySurveyHIVType_id,
				@Evn_id = :Evn_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyPersonSurveyHIV_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MorbusCrazyPersonStick_id,
				MC.MorbusCrazy_id,
				MCP.MorbusCrazyPerson_id,
				convert(varchar(10),MM.MorbusCrazyPersonStick_setDT,104) as MorbusCrazyPersonStick_setDT,
				convert(varchar(10),MM.MorbusCrazyPersonStick_disDT,104) as MorbusCrazyPersonStick_disDT,
				DATEDIFF(day, MorbusCrazyPersonStick_setDT, MorbusCrazyPersonStick_disDT) as MorbusCrazyPersonStick_Count, -- Число дней ВН
				MM.Diag_id,
				Diag.Diag_Code + '. ' + Diag.Diag_Name as Diag_Name,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyPersonStick MM with (nolock)
				inner join v_MorbusCrazyPerson MCP with (nolock) on MCP.MorbusCrazyPerson_id = MM.MorbusCrazyPerson_id
				inner join v_MorbusBase MB with (nolock) on MCP.Person_id = MB.Person_id
				inner join v_MorbusCrazy MC with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				--!!inner join v_MorbusCrazy MC with (nolock) on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				--!!inner join v_MorbusBase MB with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_Diag Diag (nolock) on Diag.Diag_id = MM.Diag_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyPersonStick_id;
			exec " . $procedure . "
				@MorbusCrazyPersonStick_id = @Res output,
				@MorbusCrazyPerson_id = :MorbusCrazyPerson_id,
				@MorbusCrazyPersonStick_setDT = :MorbusCrazyPersonStick_setDT,
				@MorbusCrazyPersonStick_disDT = :MorbusCrazyPersonStick_disDT,
				@MorbusCrazyPersonStick_Article = :MorbusCrazyPersonStick_Article,
				@Diag_id = :Diag_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyPersonStick_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				-- MM.MorbusCrazyPersonSuicidalAttempt_NumPP -- № п/п
				ROW_NUMBER() OVER (ORDER BY MorbusCrazyPersonSuicidalAttempt_setDT DESC) AS MorbusCrazyPersonSuicidalAttempt_Num,
				MorbusCrazyPersonSuicidalAttempt_id,
				MC.MorbusCrazy_id,
				MCP.MorbusCrazyPerson_id,
				convert(varchar(10),MM.MorbusCrazyPersonSuicidalAttempt_setDT,104) as MorbusCrazyPersonSuicidalAttempt_setDT,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyPersonSuicidalAttempt MM with (nolock)
				inner join v_MorbusCrazyPerson MCP with (nolock) on MCP.MorbusCrazyPerson_id = MM.MorbusCrazyPerson_id
				inner join v_MorbusBase MB with (nolock) on MCP.Person_id = MB.Person_id
				inner join v_MorbusCrazy MC with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				--!!inner join v_MorbusCrazy MC with (nolock) on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				--!!inner join v_MorbusBase MB with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyPersonSuicidalAttempt_id;
			exec " . $procedure . "
				@MorbusCrazyPersonSuicidalAttempt_id = @Res output,
				@MorbusCrazyPerson_id = :MorbusCrazyPerson_id,
				@MorbusCrazyPersonSuicidalAttempt_setDT = :MorbusCrazyPersonSuicidalAttempt_setDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyPersonSuicidalAttempt_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MorbusCrazyPersonSocDangerAct_id,
				MC.MorbusCrazy_id,
				MCP.MorbusCrazyPerson_id,
				convert(varchar(10),MM.MorbusCrazyPersonSocDangerAct_setDT,104) as MorbusCrazyPersonSocDangerAct_setDT,
				MM.MorbusCrazyPersonSocDangerAct_Article,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyPersonSocDangerAct MM with (nolock)
				inner join v_MorbusCrazyPerson MCP with (nolock) on MCP.MorbusCrazyPerson_id = MM.MorbusCrazyPerson_id
				inner join v_MorbusBase MB with (nolock) on MCP.Person_id = MB.Person_id
				inner join v_MorbusCrazy MC with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				--!!inner join v_MorbusCrazy MC with (nolock) on MC.MorbusCrazy_id = MM.MorbusCrazy_id
				--!!inner join v_MorbusBase MB with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyPersonSocDangerAct_id;
			exec " . $procedure . "
				@MorbusCrazyPersonSocDangerAct_id = @Res output,
				@MorbusCrazyPerson_id = :MorbusCrazyPerson_id,
				@MorbusCrazyPersonSocDangerAct_setDT = :MorbusCrazyPersonSocDangerAct_setDT,
				@MorbusCrazyPersonSocDangerAct_Article = :MorbusCrazyPersonSocDangerAct_Article,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyPersonSocDangerAct_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MorbusCrazyBaseDrugStart_id,
				MC.MorbusCrazy_id,
				MCB.MorbusCrazyBase_id,
				MorbusCrazyBaseDrugStart_Name,
				MM.CrazyDrugReceptType_id,
				CrazyDrugReceptType.CrazyDrugReceptType_Name,
				MM.MorbusCrazyBaseDrugStart_Age,

				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyBaseDrugStart MM with (nolock)
				inner join v_MorbusCrazyBase MCB with (nolock) on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB with (nolock) on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC with (nolock) on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_CrazyDrugReceptType CrazyDrugReceptType (nolock) on CrazyDrugReceptType.CrazyDrugReceptType_id = MM.CrazyDrugReceptType_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyBaseDrugStart_id;
			exec " . $procedure . "
				@MorbusCrazyBaseDrugStart_id = @Res output,
				@MorbusCrazyBase_id = :MorbusCrazyBase_id,
				@MorbusCrazyBaseDrugStart_Name = :MorbusCrazyBaseDrugStart_Name,
				@CrazyDrugReceptType_id = :CrazyDrugReceptType_id,
				@MorbusCrazyBaseDrugStart_Age = :MorbusCrazyBaseDrugStart_Age,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyBaseDrugStart_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MorbusCrazyDrug_id,
				MC.MorbusCrazy_id,
				MCB.MorbusCrazyBase_id,
				MorbusCrazyDrug_Name,
				MM.CrazyDrugType_id,
				CrazyDrugType.CrazyDrugType_Name,
				MM.CrazyDrugReceptType_id,
				CrazyDrugReceptType.CrazyDrugReceptType_Name,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyDrug MM with (nolock)
				inner join v_MorbusCrazyBase MCB with (nolock) on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB with (nolock) on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC with (nolock) on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_CrazyDrugType CrazyDrugType (nolock) on CrazyDrugType.CrazyDrugType_id = MM.CrazyDrugType_id
				left join v_CrazyDrugReceptType CrazyDrugReceptType (nolock) on CrazyDrugReceptType.CrazyDrugReceptType_id = MM.CrazyDrugReceptType_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyDrug_id;
			exec " . $procedure . "
				@MorbusCrazyDrug_id = @Res output,
				@MorbusCrazyBase_id = :MorbusCrazyBase_id,
				@MorbusCrazyDrug_Name = :MorbusCrazyDrug_Name,
				@CrazyDrugType_id = :CrazyDrugType_id,
				@CrazyDrugReceptType_id = :CrazyDrugReceptType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyDrug_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				case
					when IsNull(MM.Evn_id,0) = EvnEdit.Evn_id and EvnClass_SysNick = 'EvnSection' then 1
					when MM.Evn_id is null and IsNull(EvnClass_SysNick,'') != 'EvnSection' then 1
					else 0
				end accessEvn,
				MorbusCrazyDrugVolume_id,
				MC.MorbusCrazy_id,
				MCB.MorbusCrazyBase_id,
				MM.Lpu_id,
				Lpu.Lpu_Nick,
				convert(varchar(10),MM.MorbusCrazyDrugVolume_setDT,104) as MorbusCrazyDrugVolume_setDT,
				MM.CrazyDrugVolumeType_id,
				CrazyDrugVolumeType.CrazyDrugVolumeType_Name,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyDrugVolume MM with (nolock)
				inner join v_MorbusCrazyBase MCB with (nolock) on MCB.MorbusCrazyBase_id = MM.MorbusCrazyBase_id
				inner join v_MorbusBase MB with (nolock) on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC with (nolock) on MCB.MorbusBase_id = MC.MorbusBase_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = MM.Lpu_id
				left join v_CrazyDrugVolumeType CrazyDrugVolumeType (nolock) on CrazyDrugVolumeType.CrazyDrugVolumeType_id = MM.CrazyDrugVolumeType_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyDrugVolume_id;
			exec " . $procedure . "
				@MorbusCrazyDrugVolume_id = @Res output,
				@MorbusCrazyBase_id = :MorbusCrazyBase_id,
				@Lpu_id = :Lpu_id,
				@MorbusCrazyDrugVolume_setDT = :MorbusCrazyDrugVolume_setDT,
				@CrazyDrugVolumeType_id = :CrazyDrugVolumeType_id,
				@Evn_id = :Evn_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyDrugVolume_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MM.MorbusCrazyBBK_id,
				MC.MorbusCrazy_id,
				MM.MedicalCareType_id,
				MCT.MedicalCareType_Name,
				MM.CrazyDiag_id,
				CD.CrazyDiag_Name,
				MM.CrazyDiag_lid,
				CDL.CrazyDiag_Name as CrazyDiag_lName,
				convert(varchar(10),MM.MorbusCrazyBBK_setDT,104) as MorbusCrazyBBK_setDT,
				convert(varchar(10),MM.MorbusCrazyBBK_firstDT,104) as MorbusCrazyBBK_firstDT,
				convert(varchar(10),MM.MorbusCrazyBBK_lidDT,104) as MorbusCrazyBBK_lidDT,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyBBK MM with (nolock)
				inner join v_MorbusCrazy MC with (nolock) on MM.MorbusCrazy_id = MC.MorbusCrazy_id
				inner join v_MorbusBase MB with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				left join MedicalCareType MCT (nolock) on MCT.MedicalCareType_id = MM.MedicalCareType_id
				left join v_CrazyDiag CD (nolock) on CD.CrazyDiag_id = MM.CrazyDiag_id
				left join v_CrazyDiag CDL (nolock) on CDL.CrazyDiag_id = MM.CrazyDiag_lid
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyBBK_id;
			exec " . $procedure . "
				@MorbusCrazyBBK_id = @Res output,
				@MorbusCrazy_id = :MorbusCrazy_id,
				@MorbusCrazyBBK_setDT = :MorbusCrazyBBK_setDT,
				@CrazyDiag_id = :CrazyDiag_id,
				@MorbusCrazyBBK_firstDT = :MorbusCrazyBBK_firstDT,
				@MedicalCareType_id = :MedicalCareType_id,
				@CrazyDiag_lid = :CrazyDiag_lid,
				@MorbusCrazyBBK_lidDT = :MorbusCrazyBBK_lidDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyDrug_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MC.Morbus_disDT is null then 1 else 0 end as accessType,
				MorbusCrazyPersonInvalid_id,
				MC.MorbusCrazy_id,
				MCP.MorbusCrazyPerson_id,
				convert(varchar(10),MM.MorbusCrazyPersonInvalid_setDT,104) as MorbusCrazyPersonInvalid_setDT,
				MM.InvalidGroupType_id,
				InvalidGroupType.InvalidGroupType_Name,
				convert(varchar(10),MM.MorbusCrazyPersonInvalid_reExamDT,104) as MorbusCrazyPersonInvalid_reExamDT,
				MM.CrazyWorkPlaceType_id,
				CrazyWorkPlaceType.CrazyWorkPlaceType_Name,
				:Evn_id as MorbusCrazy_pid
			from
				v_MorbusCrazyPersonInvalid MM with (nolock)
				inner join v_MorbusCrazyPerson MCP with (nolock) on MCP.MorbusCrazyPerson_id = MM.MorbusCrazyPerson_id
				inner join v_MorbusBase MB with (nolock) on MCP.Person_id = MB.Person_id
				inner join v_MorbusCrazy MC with (nolock) on MC.MorbusBase_id = MB.MorbusBase_id
				left join v_InvalidGroupType InvalidGroupType (nolock) on InvalidGroupType.InvalidGroupType_id = MM.InvalidGroupType_id
				left join v_CrazyWorkPlaceType CrazyWorkPlaceType (nolock) on CrazyWorkPlaceType.CrazyWorkPlaceType_id = MM.CrazyWorkPlaceType_id
				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :Evn_id and MB.Person_id != :Evn_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusCrazyPersonInvalid_id;
			exec " . $procedure . "
				@MorbusCrazyPersonInvalid_id = @Res output,
				@MorbusCrazyPerson_id = :MorbusCrazyPerson_id,
				@MorbusCrazyPersonInvalid_setDT = :MorbusCrazyPersonInvalid_setDT,
				@InvalidGroupType_id = :InvalidGroupType_id,
				@MorbusCrazyPersonInvalid_reExamDT = :MorbusCrazyPersonInvalid_reExamDT,
				@MorbusCrazyPersonInvalid_Article = :MorbusCrazyPersonInvalid_Article,
				@CrazyWorkPlaceType_id = :CrazyWorkPlaceType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusCrazyPersonInvalid_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				" . swMorbus::getAccessTypeQueryPart('M', 'MB', 'MorbusCrazy_pid', '1', '0', 'accessType', 'AND not exists(
									select top 1 Evn.Evn_id from v_Evn Evn with (nolock)
									where
										Evn.Person_id = MB.Person_id
										and Evn.Morbus_id = M.Morbus_id
										and Evn.EvnClass_id in (11,13,32)
										and Evn.Evn_id <> :MorbusCrazy_pid
										and Evn.Evn_setDT > EvnEdit.Evn_setDT
										and (
											exists (
												select top 1 v_MorbusCrazyDrugVolume.Evn_id
												from v_MorbusCrazyDrugVolume with (nolock)
												where v_MorbusCrazyDrugVolume.Evn_id = Evn.Evn_id
											)
											OR exists (
												select top 1 v_MorbusCrazyPersonSurveyHIV.Evn_id
												from v_MorbusCrazyPersonSurveyHIV with (nolock)
												where v_MorbusCrazyPersonSurveyHIV.Evn_id = Evn.Evn_id
											)
											OR exists (
												select top 1 v_MorbusCrazyForceTreat.Evn_id
												from v_MorbusCrazyForceTreat with (nolock)
												where v_MorbusCrazyForceTreat.Evn_id = Evn.Evn_id
											)
											OR exists (
												select top 1 v_MorbusCrazyBasePS.Evn_id
												from v_MorbusCrazyBasePS with (nolock)
												where v_MorbusCrazyBasePS.Evn_id = Evn.Evn_id
											)
										)
								) /* можно редактировать, если нет более актуального документа в рамках которого изменялась специфика */') . ",
				case
					when (MorbusCrazyBasePS.RecordCount >= 1 and EvnEdit.EvnClass_SysNick = 'EvnSection') then 0
					else 1
				end accessMorbusCrazyBasePS,
				case
					when (MorbusCrazyForceTreat.RecordCount >= 1 and EvnEdit.EvnClass_SysNick = 'EvnSection') then 0
					else 1
				end accessMorbusCrazyForceTreat,
				case
					when (MorbusCrazyPersonSurveyHIV.RecordCount >= 1 and EvnEdit.EvnClass_SysNick = 'EvnSection') then 0
					else 1
				end accessMorbusCrazyPersonSurveyHIV,
				case
					when (MorbusCrazyDrugVolume.RecordCount >= 1 and EvnEdit.EvnClass_SysNick = 'EvnSection') then 0
					else 1
				end accessMorbusCrazyDrugVolume,
				EvnEdit.Evn_id,
				EvnEdit.Evn_pid,
				EvnEdit.EvnClass_SysNick,
				PR.PersonRegister_id,
				convert(varchar(10),PR.PersonRegister_setDate,104) as PersonRegister_setDate,
				convert(varchar(10),M.Morbus_setDT,104) as Morbus_setDT, -- Дата начала заболевания
				MC.Diag_nid,  --  Сопутствующее психическое (наркологическое) заболевание
				DiagNid.Diag_Code + '. ' + DiagNid.Diag_Name as Diag_nid_Name,
				MC.Diag_sid,  --  Сопутствующее соматическое (в т.ч. неврологическое) заболевание
				DiagSid.Diag_Code + '. ' + DiagSid.Diag_Name as Diag_sid_Name,
				MC.CrazyResultDeseaseType_id, -- Исход заболевания
				crdt.CrazyResultDeseaseType_Name as CrazyResultDeseaseType_id_Name,
				case when {$region} = 'ufa' then convert(varchar(10),FFF.MorbusCrazy_DeRegDT,104) else convert(varchar(10),M.Morbus_disDT,104) end as Morbus_disDT, -- Дата закрытия карты (снятия с учета)
				convert(varchar(10),FFF.MorbusCrazy_CardEndDT,104) as MorbusCrazy_CardEndDT, -- Дата закрытия карты (снятия с учета) для Уфы
				MC.CrazyCauseEndSurveyType_id, -- причина прекращения наблюдения
				ccest.CrazyCauseEndSurveyType_Name as CrazyCauseEndSurveyType_id_Name,
				MCB.MorbusCrazyBase_LTMDayCount, -- Число дней работы в ЛТМ
				MCB.MorbusCrazyBase_HolidayDayCount, -- Число дней лечебных отпусков
				MCB.MorbusCrazyBase_HolidayCount, -- Число лечебных отпусков (за период госпитализации)

				MCP.MorbusCrazyPerson_IsWowInvalid, -- Инвалид ВОВ
				IsWowInvalid.YesNo_Name as MorbusCrazyPerson_IsWowInvalid_Name,

				MCP.MorbusCrazyPerson_IsWowMember, -- участник ВОВ
				IsWowMember.YesNo_Name as MorbusCrazyPerson_IsWowMember_Name,

				cet.CrazyEducationType_id, -- Образование
				cet.CrazyEducationType_Name as CrazyEducationType_id_Name,

				MCP.MorbusCrazyPerson_CompleteClassCount, --число законченных классов среднеобразовательного учреждения

				MCP.MorbusCrazyPerson_IsEducation, -- учится
				IsEducation.YesNo_Name as MorbusCrazyPerson_IsEducation_Name,

				cslt.CrazySourceLivelihoodType_id, -- источник средств существования
				cslt.CrazySourceLivelihoodType_Name as CrazySourceLivelihoodType_id_Name,

				crt.CrazyResideType_id, -- проживает
				crt.CrazyResideType_Name as CrazyResideType_id_Name,

				crct.CrazyResideConditionsType_id, -- условия проживания
				crct.CrazyResideConditionsType_Name as CrazyResideConditionsType_id_Name,

				convert(varchar(10),MCB.MorbusCrazyBase_firstDT,104) as MorbusCrazyBase_firstDT, -- Дата обращения к психиатру (наркологу) впервые в жизни

				MCP.MorbusCrazyPerson_IsConvictionBeforePsych, -- судимости до обращения к психиатру (наркологу)
				IsConvictionBeforePsych.YesNo_Name as MorbusCrazyPerson_IsConvictionBeforePsych_Name,

				convert(varchar(10),MCB.MorbusCrazyBase_DeathDT,104) as MorbusCrazyBase_DeathDT, -- Дата смерти

				cdct.CrazyDeathCauseType_id, -- Причина смерти
				cdct.CrazyDeathCauseType_Name as CrazyDeathCauseType_id_Name,

				MCB.MorbusCrazyBase_IsUseAlienDevice, -- Использование чужих шприцов, игл, приспособлений
				IsUseAlienDevice.YesNo_Name as MorbusCrazyBase_IsUseAlienDevice_Name,
				MCB.MorbusCrazyBase_IsLivingConsumDrug, -- Проживание с потребителем психоактивных средств
				IsLivingConsumDrug.YesNo_Name as MorbusCrazyBase_IsLivingConsumDrug_Name,

				MC.MorbusCrazy_id,
				MC.Morbus_id,
				MB.MorbusBase_id,
				MCB.MorbusCrazyBase_id,
				MCP.MorbusCrazyPerson_id,
				:MorbusCrazy_pid as MorbusCrazy_pid,
				MB.Person_id,
				M.Diag_id,
				case when MT.MorbusType_SysNick like 'narc' then 'narko' else 'psycho' end as Diagtype,
				M.MorbusType_id,
				{$region} as regionNick
			from
				v_Morbus M with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = M.MorbusBase_id
				left join v_MorbusCrazyBase MCB with (nolock) on MCB.MorbusBase_id = MB.MorbusBase_id
				inner join v_MorbusCrazy MC with (nolock) on MC.Morbus_id = M.Morbus_id
				outer apply (
					select top 1 * from  v_MorbusCrazyPerson MCP with (nolock) where MCB.Person_id = MCP.Person_id order by MorbusCrazyPerson_insDT asc
				) MCP
				outer apply (
					select top 1 PR.PersonRegister_id, PR.PersonRegister_setDate  from  v_PersonRegister PR with (nolock)
					where PR.Person_id = MB.Person_id and PR.Morbus_id = M.Morbus_id {$pr_filter}
					order by PersonRegister_disDate ASC, PersonRegister_setDate DESC
				) PR
				outer apply (
					select top 1 
						FF.MorbusCrazy_CardEndDT
						,FF.MorbusCrazy_DeRegDT  
					from v_MorbusCrazy FF with (nolock) 
					where FF.Morbus_id = M.Morbus_id
				) FFF
				left join v_MorbusType MT with(nolock) on MT.MorbusType_id = M.MorbusType_id
				left join Diag DiagNid (nolock) on DiagNid.Diag_id = MC.Diag_nid
				left join Diag DiagSid (nolock) on DiagSid.Diag_id = MC.Diag_sid
				left join Diag DiagM (nolock) on DiagM.Diag_id = M.Diag_id
				left join v_CrazyResultDeseaseType crdt (nolock) on crdt.CrazyResultDeseaseType_id = MC.CrazyResultDeseaseType_id
				left join v_CrazyCauseEndSurveyType ccest (nolock) on ccest.CrazyCauseEndSurveyType_id = MC.CrazyCauseEndSurveyType_id
				left join v_YesNo IsWowInvalid with (nolock) on MCP.MorbusCrazyPerson_IsWowInvalid = IsWowInvalid.YesNo_id
				left join v_YesNo IsWowMember with (nolock) on MCP.MorbusCrazyPerson_IsWowMember = IsWowMember.YesNo_id
				left join v_CrazyEducationType cet with (nolock) on cet.CrazyEducationType_id = MCP.CrazyEducationType_id
				left join v_YesNo IsEducation with (nolock) on MCP.MorbusCrazyPerson_IsEducation = IsEducation.YesNo_id
				left join v_CrazySourceLivelihoodType cslt with (nolock) on cslt.CrazySourceLivelihoodType_id = MCP.CrazySourceLivelihoodType_id
				left join v_CrazyResideType crt with (nolock) on crt.CrazyResideType_id = MCP.CrazyResideType_id
				left join v_CrazyResideConditionsType crct with (nolock) on crct.CrazyResideConditionsType_id = MCP.CrazyResideConditionsType_id
				left join v_YesNo IsConvictionBeforePsych with (nolock) on MCP.MorbusCrazyPerson_IsConvictionBeforePsych = IsConvictionBeforePsych.YesNo_id
				left join v_CrazyDeathCauseType cdct with (nolock) on cdct.CrazyDeathCauseType_id = MCB.CrazyDeathCauseType_id
				left join v_YesNo IsUseAlienDevice with (nolock) on MCB.MorbusCrazyBase_IsUseAlienDevice = IsUseAlienDevice.YesNo_id
				left join v_YesNo IsLivingConsumDrug with (nolock) on MCB.MorbusCrazyBase_IsLivingConsumDrug = IsLivingConsumDrug.YesNo_id
				outer apply (
					Select count(*) as RecordCount from v_MorbusCrazyBasePS MM with(nolock) where MM.MorbusCrazyBase_id = MCB.MorbusCrazyBase_id and MM.Evn_id = :MorbusCrazy_pid
				) MorbusCrazyBasePS
				outer apply (
					Select count(*) as RecordCount from v_MorbusCrazyForceTreat MM with(nolock) where MM.MorbusCrazyBase_id = MCB.MorbusCrazyBase_id and MM.Evn_id = :MorbusCrazy_pid
				) MorbusCrazyForceTreat
				
				outer apply (
					Select count(*) as RecordCount from v_MorbusCrazyPersonSurveyHIV MM with(nolock) where MM.MorbusCrazyPerson_id = MCP.MorbusCrazyPerson_id and MM.Evn_id = :MorbusCrazy_pid
				) MorbusCrazyPersonSurveyHIV
				
				outer apply (
					Select count(*) as RecordCount from v_MorbusCrazyDrugVolume MM with(nolock) where MM.MorbusCrazyBase_id = MCB.MorbusCrazyBase_id and MM.Evn_id = :MorbusCrazy_pid
				) MorbusCrazyDrugVolume

				left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :MorbusCrazy_pid and MB.Person_id != :MorbusCrazy_pid
				--left join EvnClass with (nolock) on EvnClass.EvnClass_id = EvnEdit.EvnClass_id

			where
				M.Morbus_id = :Morbus_id
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
				$q = 'select top 1 '. implode(', ',$l_arr) .' from dbo.v_'. $entity .' WITH (NOLOCK) where '. $entity .'_id = :'. $entity .'_id';

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
						@'. $key .' = :'. $key .',';
				}
				$q = '
					declare
						@'. $entity .'_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @'. $entity .'_id = :'. $entity .'_id;
					exec dbo.p_'. $entity .'_upd
						@'. $entity .'_id = @'. $entity .'_id output, '. $field_str .'
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @'. $entity .'_id as '. $entity .'_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				';
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
			$filter .= " and (CrazyDiag_Code like :query+'%'  or CrazyDiag_Name like '%'+:query+'%')";
		}
		else {
			if ( !empty($data['CrazyDiag_id']) ) {
				$filter .= " and CrazyDiag_id = :CrazyDiag_id";
			}
			/*
			 if (strlen($data['CrazyDiag_Name'])>0) {
				$filter .= " and CrazyDiag_Name like :CrazyDiag_Name+'%'";
			}*/
		}
		if($data['type']=='narko'){
			$filter .=" and CrazyDiag_Code like 'F1%'";
		}else{
			$filter .=" and CrazyDiag_Code not like 'F1%'";
		}

		if ( !empty($data['date']) ) {
			$filter .= "
				and ISNULL(CrazyDiag_begDate, :date) <= :date
				and ISNULL(CrazyDiag_endDate, :date) >= :date
			";
		}

		$query = "
			select top 100
				CrazyDiag_id,
				Diag_id,
				CrazyDiag_Code,
				CrazyDiag_Name,
				convert(varchar(10), CrazyDiag_begDate, 104) as CrazyDiag_begDate,
				convert(varchar(10), CrazyDiag_endDate, 104) as CrazyDiag_endDate
			from
				v_CrazyDiag with (nolock)
			where
				{$filter}
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
				mb.Person_id,
				mb.Evn_pid,
				mb.MorbusBase_id,
				case when d.Diag_Code >= 'F10.0' and d.Diag_Code <= 'F19.9' then 2 else 1 end as MorbusType,
				-- Данные из регистра по психиатрии/наркологии (MorbusType_id=4): 
				PR.PersonRegister_id,
				PR.Diag_id,
				convert(varchar(10), PR.PersonRegister_setDT, 120) as PersonRegister_setDT,
				--- Данные из специфики психиатрии/наркологии:
				mcp.MorbusCrazyPerson_id,
				mcp.Person_id,
				case when mcp.MorbusCrazyPerson_IsWowInvalid = 2 then 1 else 0 end as MorbusCrazyPerson_IsWowInvalid,
				case when mcp.MorbusCrazyPerson_IsWowMember = 2 then 1 else 0 end as MorbusCrazyPerson_IsWowMember,
				mcp.CrazyEducationType_id,
				mcp.MorbusCrazyPerson_CompleteClassCount,
				case when mcp.MorbusCrazyPerson_IsEducation = 2 then 1 else 0 end as MorbusCrazyPerson_IsEducation,
				mcp.CrazySourceLivelihoodType_id,
				mcp.CrazyResideType_id,
				mcp.CrazyResideConditionsType_id,
				case when mcp.MorbusCrazyPerson_IsConvictionBeforePsych = 2 then 1 else 0 end as MorbusCrazyPerson_IsConvictionBeforePsych,
				--- Данные по диагнозу (используется запись с максимальной MorbusCrazyDiag_setDT):
				mcd.MorbusCrazyDiag_id,
				mcd.MorbusCrazy_id,
				convert(varchar(10), mcd.MorbusCrazyDiag_setDT, 120) as MorbusCrazyDiag_setDT,
				mcd.CrazyDiag_id,
				mcd.Diag_sid,
				--- Данные по инвалидности (используется запись с максимальной MorbusCrazyPersonInvalid_setDT):
				mcpi.MorbusCrazyPersonInvalid_id,
				convert(varchar(10), mcpi.MorbusCrazyPersonInvalid_setDT, 120) as MorbusCrazyPersonInvalid_setDT,
				mcpi.InvalidGroupType_id,
				mcpi.MorbusCrazyPersonInvalid_reExamDT,
				mcpi.MorbusCrazyPersonInvalid_Article,
				mcpi.CrazyWorkPlaceType_id
			from
				v_MorbusBase mb (nolock)
				inner join v_MorbusCrazyBase mcb (nolock) on mcb.MorbusBase_id = mb.MorbusBase_id
				inner join v_Morbus m (nolock) on m.MorbusBase_id = mb.MorbusBase_id
				inner join v_Diag d (nolock) on d.Diag_id = m.Diag_id
				inner join v_MorbusCrazy mc (nolock) on mc.Morbus_id = m.Morbus_id
				outer apply (
					select top 1 * from v_MorbusCrazyPerson MCP with (nolock) where MB.Person_id = MCP.Person_id order by MorbusCrazyPerson_insDT asc
				) mcp
				outer apply (
					select top 1 * from v_MorbusCrazyDiag MCD with (nolock) where MCD.MorbusCrazy_id = mc.MorbusCrazy_id order by MorbusCrazyDiag_setDT desc
				) mcd
				outer apply (
					select top 1 * from v_MorbusCrazyPersonInvalid MCPI with (nolock) where MCPI.MorbusCrazyPerson_id = mcp.MorbusCrazyPerson_id order by MorbusCrazyPersonInvalid_setDT desc
				) mcpi
				outer apply (
					select top 1
						PR.PersonRegister_id,
						PR.Diag_id,
						PR.PersonRegister_setDate as PersonRegister_setDT
					from
					 	v_PersonRegister PR with (nolock)
					where
						PR.Person_id = MB.Person_id
						and PR.Morbus_id = M.Morbus_id
					order by
						PersonRegister_disDate ASC,
						PersonRegister_setDate DESC
				) PR
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
				mcd.MorbusCrazyDrug_id,
				mcd.MorbusCrazyBase_id,
				mcd.MorbusCrazyDrug_Name,
				mcd.CrazyDrugType_id,
				mcd.CrazyDrugReceptType_id
			from
				v_MorbusCrazyDrug mcd (nolock)
				inner join v_MorbusCrazyBase mcb (nolock) on mcb.MorbusCrazyBase_id = mcd.MorbusCrazyBase_id
			where
				1=1
				{$filter}
		", $queryParams);
	}
}
