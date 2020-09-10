<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MorbusVener_model - модель для MorbusVener
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
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
*/

class MorbusVener_model extends swModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;

	private $entityFields = array(
		'MorbusVener' => array(
			'Morbus_id',
			'MorbusVener_DiagDT',
			'VenerDetectType_id',
			'MorbusVener_IsVizitProf',
			'MorbusVener_IsPrevent',
			'MorbusVener_updDiagDT',
			'MorbusVener_HospDT',
			'MorbusVener_BegTretDT',
			'Lpu_bid',
			'MorbusVener_EndTretDT',
			'Lpu_eid',
			'MorbusVener_DeRegDT',
			'VenerDeRegCauseType_id',
			'MorbusVener_LiveCondit',
			'MorbusVener_WorkCondit',
			'MorbusVener_Heredity',
			'MorbusVener_UseAlcoNarc',
			'MorbusVener_PlaceInfect',
			'MorbusVener_IsAlco',
			'MorbusVener_MensBeg',
			'MorbusVener_MensEnd',
			'MorbusVener_MensOver',
			'MorbusVener_MensLastDT',
			'MorbusVener_SexualInit',
			'MorbusVener_CountPregnancy',
			'MorbusVener_CountBirth',
			'MorbusVener_CountAbort'
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

	protected $_MorbusType_id = null;//8

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
	function getMorbusTypeSysNick()
	{
		return 'vener';
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
		return 'Vener';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusVener';
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
			,'MorbusVener_id' => 'Идентификатор специфики заболевания'

			//,'Lpu_id' => 'ЛПУ, в которой впервые установлен диагноз орфанного заболевания'
		);
		switch ($data['Mode']) {
			case 'personregister_viewform':
				$check_fields_list = array('MorbusVener_id','Morbus_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'evnsection_viewform':
			case 'evnvizitpl_viewform':
				$check_fields_list = array('MorbusVener_id','Morbus_id','Evn_pid','pmUser_id');
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
			|| false == in_array($data['mode'], array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = array();
		$queryParams['pmUser_id'] = $this->promedUserId;
		$queryParams['Morbus_id'] = $data['Morbus_id'];

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Morbus_id bigint = :Morbus_id,
				@{$tableName}_id bigint = null,
				@pmUser_id bigint = :pmUser_id;

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
		return $this->_saveResponse;
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
				and mt.MorbusType_SysNick = 'Vener'
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
	function getUsersVener($data)
	{
		$query = "
		select 
			PMUser_id 
		from 
			v_pmUserCache  with(nolock)
		where 
			pmUser_groups like '%\"Vener\"%'
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
	 * Получает список "Люди, контактировавшие с больным"
	 */
	function getMorbusVenerContactViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusVenerContact_id']) and ($data['MorbusVenerContact_id'] > 0)) {
			$filter = "MM.MorbusVenerContact_id = :MorbusVenerContact_id";
		} else {
			if(empty($data['MorbusVener_id']) or $data['MorbusVener_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusVener_id = :MorbusVener_id";
		}

		$query = "
			select
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MV.Morbus_disDT is null then 1 else 0 end as accessType,
				MM.Person_cid,
				PS.Person_SurName + ' ' + PS.Person_FirName + ' ' + isnull(PS.Person_SecName, '') as Person_Fio,
				MM.MorbusVenerContact_RelationSick,
				MM.MorbusVenerContact_IsSourceInfect,
				MM.MorbusVenerContact_IsFamSubjServey,
				convert(varchar(10),MM.MorbusVenerContact_CallDT,104) as MorbusVenerContact_CallDT,
				convert(varchar(10),MM.MorbusVenerContact_PresDT,104) as MorbusVenerContact_PresDT,
				convert(varchar(10),MM.MorbusVenerContact_FirstDT,104) as MorbusVenerContact_FirstDT,
				convert(varchar(10),MM.MorbusVenerContact_FinalDT,104) as MorbusVenerContact_FinalDT,
				MM.Diag_id,
				Diag.Diag_Code + '. ' + Diag.Diag_Name as Diag_Name,
				MM.MorbusVenerContact_Comment,
				MM.MorbusVenerContact_id,
				MV.MorbusVener_id,
				:Evn_id as MorbusVener_pid
			from
				v_MorbusVenerContact MM with (nolock)
				left join v_PersonState PS with(nolock) on PS.Person_id = MM.Person_cid
				inner join v_MorbusVener MV with (nolock) on MV.MorbusVener_id = MM.MorbusVener_id
				inner join v_MorbusBase MB with (nolock) on MV.MorbusBase_id = MB.MorbusBase_id
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
	 * Сохраняет специфику "Люди, контактировавшие с больным"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusVenerContact($data) {
		$procedure = '';

		if ( (!isset($data['MorbusVenerContact_id'])) || ($data['MorbusVenerContact_id'] <= 0) ) {
			$procedure = 'p_MorbusVenerContact_ins';
		}
		else {
			$procedure = 'p_MorbusVenerContact_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusVenerContact_id;
			exec " . $procedure . "
				@MorbusVenerContact_id = @Res output,
				@MorbusVener_id = :MorbusVener_id,
				@Person_cid = :Person_cid,
				@MorbusVenerContact_RelationSick = :MorbusVenerContact_RelationSick,
				@MorbusVenerContact_IsSourceInfect = :MorbusVenerContact_IsSourceInfect,
				@MorbusVenerContact_IsFamSubjServey = :MorbusVenerContact_IsFamSubjServey,
				@MorbusVenerContact_CallDT = :MorbusVenerContact_CallDT,
				@MorbusVenerContact_PresDT = :MorbusVenerContact_PresDT,
				@MorbusVenerContact_FirstDT = :MorbusVenerContact_FirstDT,
				@MorbusVenerContact_FinalDT = :MorbusVenerContact_FinalDT,
				@Diag_id = :Diag_id,
				@MorbusVenerContact_Comment = :MorbusVenerContact_Comment,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusVenerContact_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	 * Получает список "Лечение больного сифилисом"
	 */
	function getMorbusVenerTreatSyphViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusVenerTreatSyph_id']) and ($data['MorbusVenerTreatSyph_id'] > 0)) {
			$filter = "MM.MorbusVenerTreatSyph_id = :MorbusVenerTreatSyph_id";
		} else {
			if(empty($data['MorbusVener_id']) or $data['MorbusVener_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusVener_id = :MorbusVener_id";
		}

		$query = "
			select
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MV.Morbus_disDT is null then 1 else 0 end as accessType,
				MM.MorbusVenerTreatSyph_NumCourse,
				convert(varchar(10),MM.MorbusVenerTreatSyph_begDT,104) as MorbusVenerTreatSyph_begDT,
				convert(varchar(10),MM.MorbusVenerTreatSyph_endDT,104) as MorbusVenerTreatSyph_endDT,
				MM.Drug_id,
				Drug.Drug_Name as Drug_Name,
				MM.MorbusVenerTreatSyph_SumDose as MorbusVenerTreatSyph_SumDose,
				convert(varchar(10),MM.MorbusVenerTreatSyph_RSSBegCourse,104) as MorbusVenerTreatSyph_RSSBegCourse,
				convert(varchar(10),MM.MorbusVenerTreatSyph_RSSEndCourse,104) as MorbusVenerTreatSyph_RSSEndCourse,
				MM.MorbusVenerTreatSyph_Comment,
				MM.MorbusVenerTreatSyph_id,
				MV.MorbusVener_id,
				:Evn_id as MorbusVener_pid
			from
				v_MorbusVenerTreatSyph MM with (nolock)
				inner join v_MorbusVener MV with (nolock) on MV.MorbusVener_id = MM.MorbusVener_id
				inner join v_MorbusBase MB with (nolock) on MV.MorbusBase_id = MB.MorbusBase_id
				left join rls.v_Drug Drug (nolock) on Drug.Drug_id = MM.Drug_id
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
	 * Сохраняет специфику "Лечение больного сифилисом"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusVenerTreatSyph($data) {
		$procedure = '';

		if ( (!isset($data['MorbusVenerTreatSyph_id'])) || ($data['MorbusVenerTreatSyph_id'] <= 0) ) {
			$procedure = 'p_MorbusVenerTreatSyph_ins';
		}
		else {
			$procedure = 'p_MorbusVenerTreatSyph_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusVenerTreatSyph_id;
			exec " . $procedure . "
				@MorbusVenerTreatSyph_id = @Res output,
				@MorbusVener_id = :MorbusVener_id,
				@MorbusVenerTreatSyph_NumCourse = :MorbusVenerTreatSyph_NumCourse,
				@MorbusVenerTreatSyph_begDT = :MorbusVenerTreatSyph_begDT,
				@MorbusVenerTreatSyph_endDT = :MorbusVenerTreatSyph_endDT,
				@Drug_id = :Drug_id,
				@MorbusVenerTreatSyph_SumDose = :MorbusVenerTreatSyph_SumDose,
				@MorbusVenerTreatSyph_RSSBegCourse = :MorbusVenerTreatSyph_RSSBegCourse,
				@MorbusVenerTreatSyph_RSSEndCourse = :MorbusVenerTreatSyph_RSSEndCourse,
				@MorbusVenerTreatSyph_Comment = :MorbusVenerTreatSyph_Comment,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusVenerTreatSyph_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	 * Получает список "Контроль за аккуратностью лечения"
	 */
	function getMorbusVenerAccurTreatViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusVenerAccurTreat_id']) and ($data['MorbusVenerAccurTreat_id'] > 0)) {
			$filter = "MM.MorbusVenerAccurTreat_id = :MorbusVenerAccurTreat_id";
		} else {
			if(empty($data['MorbusVener_id']) or $data['MorbusVener_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusVener_id = :MorbusVener_id";
		}

		$query = "
			select
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MV.Morbus_disDT is null then 1 else 0 end as accessType,
				convert(varchar(10),MM.MorbusVenerAccurTreat_AbandDT,104) as MorbusVenerAccurTreat_AbandDT,
				convert(varchar(10),MM.MorbusVenerAccurTreat_CallDT,104) as MorbusVenerAccurTreat_CallDT,
				convert(varchar(10),MM.MorbusVenerAccurTreat_PresDT,104) as MorbusVenerAccurTreat_PresDT,
				MM.MorbusVenerAccurTreat_id,
				MV.MorbusVener_id,
				:Evn_id as MorbusVener_pid
			from
				v_MorbusVenerAccurTreat MM with (nolock)
				inner join v_MorbusVener MV with (nolock) on MV.MorbusVener_id = MM.MorbusVener_id
				inner join v_MorbusBase MB with (nolock) on MV.MorbusBase_id = MB.MorbusBase_id
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
	 * Сохраняет специфику "Контроль за аккуратностью лечения"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusVenerAccurTreat($data) {
		$procedure = '';

		if ( (!isset($data['MorbusVenerAccurTreat_id'])) || ($data['MorbusVenerAccurTreat_id'] <= 0) ) {
			$procedure = 'p_MorbusVenerAccurTreat_ins';
		}
		else {
			$procedure = 'p_MorbusVenerAccurTreat_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusVenerAccurTreat_id;
			exec " . $procedure . "
				@MorbusVenerAccurTreat_id = @Res output,
				@MorbusVener_id = :MorbusVener_id,
				@MorbusVenerAccurTreat_AbandDT = :MorbusVenerAccurTreat_AbandDT,
				@MorbusVenerAccurTreat_CallDT = :MorbusVenerAccurTreat_CallDT,
				@MorbusVenerAccurTreat_PresDT = :MorbusVenerAccurTreat_PresDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusVenerAccurTreat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	 * Получает список "Контроль по окончании лечения"
	 */
	function getMorbusVenerEndTreatViewData($data)
	{
		$filter = "(1=1)";
		if (isset($data['MorbusVenerEndTreat_id']) and ($data['MorbusVenerEndTreat_id'] > 0)) {
			$filter = "MM.MorbusVenerEndTreat_id = :MorbusVenerEndTreat_id";
		} else {
			if(empty($data['MorbusVener_id']) or $data['MorbusVener_id'] < 0) {
				return array();
			}
			$filter = "MM.MorbusVener_id = :MorbusVener_id";
		}

		$query = "
			select
				case when (isnull(EvnEdit.Evn_IsSigned,1) = 1) and MV.Morbus_disDT is null then 1 else 0 end as accessType,
				convert(varchar(10),MM.MorbusVenerEndTreat_setDT,104) as MorbusVenerEndTreat_setDT,
				convert(varchar(10),MM.MorbusVenerEndTreat_CallDT,104) as MorbusVenerEndTreat_CallDT,
				convert(varchar(10),MM.MorbusVenerEndTreat_PresDT,104) as MorbusVenerEndTreat_PresDT,
				MM.MorbusVenerEndTreat_id,
				MV.MorbusVener_id,
				:Evn_id as MorbusVener_pid
			from
				v_MorbusVenerEndTreat MM with (nolock)
				inner join v_MorbusVener MV with (nolock) on MV.MorbusVener_id = MM.MorbusVener_id
				inner join v_MorbusBase MB with (nolock) on MV.MorbusBase_id = MB.MorbusBase_id
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
	 * Сохраняет специфику "Контроль по окончании лечения"
	 * @param $data
	 * @return array|bool|mixed
	 */
	function saveMorbusVenerEndTreat($data) {
		$procedure = '';

		if ( (!isset($data['MorbusVenerEndTreat_id'])) || ($data['MorbusVenerEndTreat_id'] <= 0) ) {
			$procedure = 'p_MorbusVenerEndTreat_ins';
		}
		else {
			$procedure = 'p_MorbusVenerEndTreat_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorbusVenerEndTreat_id;
			exec " . $procedure . "
				@MorbusVenerEndTreat_id = @Res output,
				@MorbusVener_id = :MorbusVener_id,
				@MorbusVenerEndTreat_setDT = :MorbusVenerEndTreat_setDT,
				@MorbusVenerEndTreat_CallDT = :MorbusVenerEndTreat_CallDT,
				@MorbusVenerEndTreat_PresDT = :MorbusVenerEndTreat_PresDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MorbusVenerEndTreat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	 * При вызове из формы просмотра записи регистра параметр MorbusVener_pid будет содержать Person_id, также будет передан PersonRegister_id
	 * При вызове из формы просмотра движения/посещения параметр MorbusVener_pid будет содержать Evn_id просматриваемого движения/посещения
	 */
	function getMorbusVenerViewData($data)
	{
		if (empty($data['session'])) { $data['session'] = null; }
		if (empty($data['MorbusVener_pid'])) { $data['MorbusVener_pid'] = null; }
		if (empty($data['PersonRegister_id'])) { $data['PersonRegister_id'] = null; }
		$this->load->library('swMorbus');
		$params = swMorbus::onBeforeViewData($this->getMorbusTypeSysNick(), $data['session'], $data['MorbusVener_pid'], $data['PersonRegister_id']);
		if ($params['Error_Msg']) {
			throw new Exception($params['Error_Msg']);
		}
		$params['MorbusVener_pid'] = $data['MorbusVener_pid'];

		$query = "
			select top 1
				" . swMorbus::getAccessTypeQueryPart('M', 'MB', 'MorbusVener_pid', '1', '0') . ",
				M.Diag_id, -- Диагноз
				--Diag.Diag_Code + '. ' + Diag.Diag_Name as Diag_Name,
				convert(varchar(10),MV.MorbusVener_DiagDT,104) as MorbusVener_DiagDT, -- Дата установления диагноза
				MV.VenerDetectType_id, -- Обстоятельства выявления заболевания
				VenerDetectType.VenerDetectType_Name,
				MV.MorbusVener_IsVizitProf, -- Посещал пункт индивидуальной профилактики венерических болезней
				IsVizitProf.YesNo_Name as IsVizitProf_Name,
				MV.MorbusVener_IsPrevent, -- Ознакомлен с предупреждением
				IsPrevent.YesNo_Name as IsPrevent_Name,

				convert(varchar(10),MV.MorbusVener_updDiagDT,104) as MorbusVener_updDiagDT, -- Дата изменения диагноза
				convert(varchar(10),MV.MorbusVener_HospDT,104) as MorbusVener_HospDT, -- Дата госпитализации
				convert(varchar(10),MV.MorbusVener_BegTretDT,104) as MorbusVener_BegTretDT, -- Дата начала лечения
				MV.Lpu_bid, -- ЛПУ, где начал лечение
				Bid.Lpu_nick as LpuBid_Nick,
				convert(varchar(10),MV.MorbusVener_EndTretDT,104) as MorbusVener_EndTretDT, -- Дата окончания лечения
				MV.Lpu_eid, -- ЛПУ, где окончил лечение
				Eid.Lpu_nick as LpuEid_Nick,
				convert(varchar(10),MV.MorbusVener_DeRegDT,104) as MorbusVener_DeRegDT, -- Дата снятия с учета
				MV.VenerDeRegCauseType_id, -- Причина снятия с учета
				VenerDeRegCauseType.VenerDeRegCauseType_Name,
				MorbusVener_LiveCondit,
				MorbusVener_WorkCondit,
				MorbusVener_Heredity,
				MorbusVener_UseAlcoNarc,
				MorbusVener_PlaceInfect,
				MorbusVener_IsAlco,
				IsAlco.YesNo_Name as IsAlco_Name,
				MorbusVener_MensBeg,
				MorbusVener_MensEnd,
				MorbusVener_MensOver,
				convert(varchar(10),MV.MorbusVener_MensLastDT,104) as MorbusVener_MensLastDT,
				MorbusVener_SexualInit,
				MorbusVener_CountPregnancy,
				MorbusVener_CountBirth,
				MorbusVener_CountAbort,
				MV.MorbusVener_id,
				MV.Morbus_id,
				MB.MorbusBase_id,
				:MorbusVener_pid as MorbusVener_pid,
				MB.Person_id
			from
				v_Morbus M with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = M.MorbusBase_id
				inner join v_MorbusVener MV with (nolock) on MV.Morbus_id = M.Morbus_id
				--left join Diag Diag (nolock) on Diag.Diag_id = MV.Diag_id
				left join v_VenerDetectType VenerDetectType (nolock) on VenerDetectType.VenerDetectType_id = MV.VenerDetectType_id
				left join v_YesNo IsVizitProf with (nolock) on MV.MorbusVener_IsVizitProf = IsVizitProf.YesNo_id
				left join v_YesNo IsPrevent with (nolock) on MV.MorbusVener_IsPrevent = IsPrevent.YesNo_id
				left join v_YesNo IsAlco with (nolock) on MV.MorbusVener_IsAlco = IsAlco.YesNo_id
				left join v_Lpu Bid (nolock) on Bid.Lpu_id = MV.Lpu_bid
				left join v_Lpu Eid (nolock) on Eid.Lpu_id = MV.Lpu_eid
				left join v_VenerDeRegCauseType VenerDeRegCauseType (nolock) on VenerDeRegCauseType.VenerDeRegCauseType_id = MV.VenerDeRegCauseType_id
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
		$not_edit_fields = array('Evn_pid', 'Person_id','MorbusVener_id', 'Morbus_id', 'MorbusBase_id','MorbusType_id','Morbus_setDT','Morbus_disDT','MorbusBase_setDT','MorbusBase_disDT');
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
	 * Сохраняет специфицику по психиатрии
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveMorbusVener($data)
	{
		return $this->saveMorbusSpecific($data);
	}

	/**
	 * @param array $data
	 * @return bool|array
	 */
	function getVenerDiag($data) {
		$filter = "(1=1)";
		if (strlen($data['query'])>0) {
			$filter .= " and (VenerDiag_Code like :query+'%'  or VenerDiag_Name like '%'+:query+'%')";
		} else {
			if (strlen($data['VenerDiag_id'])>0) {
				$filter .= " and VenerDiag_id = :VenerDiag_id";
			}
			/*
			 if (strlen($data['VenerDiag_Name'])>0) {
				$filter .= " and VenerDiag_Name like :VenerDiag_Name+'%'";
			}*/
		}

		$query = "
			select top 100
				VenerDiag_id,
				Diag_id,
				VenerDiag_Code,
				VenerDiag_Name
			from
				v_VenerDiag with (nolock)
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

}