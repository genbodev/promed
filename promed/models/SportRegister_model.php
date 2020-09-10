<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 * SportRegister_model - молеь для работы с данными регистра спортсменов (Башкирия)
 *
 * @package            SportRegistry
 * @author             Хамитов Марат
 * @version            12.2018
 */
class SportRegister_model extends swModel
{
	var $scheme = "dbo";
	
	/**
	 * comments
	 */
	
	function __construct ()
	{
		parent::__construct();
	}
	
	/**
	 *  Получаем виды спорта
	 */
	function getSportType ($data)
	{
		
		$params = array();
		
		$query = "select 
			ST.SportType_id,
			ST.SportType_name
		from dbo.SportType ST with(nolock)
		where ST.SportType_endDT is null or ST.SportType_endDT > getDate()";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем этапы спортивной подготовки
	 */
	function getSportStage ($data)
	{
		
		$params = array();
		
		$query = "select 
			SS.SportStage_id,
			SS.SportStage_name
		from dbo.SportStage SS with(nolock)
		where SS.SportStage_endDT is null or SS.SportStage_endDT > getDate()";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем спортивные разряды
	 */
	function getSportCategory ($data)
	{
		
		$params = array();
		
		$query = "select 
			SC.SportCategory_id,
			SC.SportCategory_name
		from dbo.SportCategory SC with(nolock)
		where SC.SportCategory_endDT is null or SC.SportCategory_endDT > getDate()";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем список спортивных школ
	 */
	function getSportOrg ($data)
	{
		
		$params = array();
		
		$query = "select 
			SO.SportOrg_id,
			SO.SportOrg_name
		from dbo.SportOrg SO with(nolock)";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем список врачей в регистре спортсменов (для фильтра)
	 */
	function getMedPersonalFilter ($data)
	{
		
		$params = array();
		
		$query = "select DISTINCT
			SRUMO.MedPersonal_pid,
			RTRIM (PS.PersonSurName_SurName) + ' '+ RTRIM (PS.PersonFirName_FirName) + ' ' + RTRIM (PS.PersonSecName_SecName) AS MedPersonal_pname
		from SportRegisterUMO SRUMO with (nolock)
		left join MedPersonalCache MP with (nolock) on MP.MedPersonal_id = SRUMO.MedPersonal_pid
		left join PersonState PS with (nolock) on PS.Person_id = MP.Person_id";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем список врачей
	 */
	function getMedPersonalP ($data)
	{
		
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		
		$query = "select DISTINCT
			MP.MedPersonal_id as MedPersonal_pid, 
			RTRIM (PS.PersonSurName_SurName) + ' '+ RTRIM (PS.PersonFirName_FirName) + ' ' + RTRIM (PS.PersonSecName_SecName) AS MedPersonal_pname
		from MedPersonalCache MP with (nolock)
		left join PersonState PS with (nolock) on PS.Person_id = MP.Person_id
		where MP.Lpu_id = :Lpu_id
		order by MedPersonal_pname";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем список медсестер
	 */
	function getMedPersonalS ($data)
	{
		
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		
		$query = "select DISTINCT
			MP.MedPersonal_id as MedPersonal_sid, 
			RTRIM (PS.PersonSurName_SurName) + ' '+ RTRIM (PS.PersonFirName_FirName) + ' ' + RTRIM (PS.PersonSecName_SecName) AS MedPersonal_sname
		from MedPersonalCache MP with (nolock)
		left join PersonState PS with (nolock) on PS.Person_id = MP.Person_id
		where MP.Lpu_id = :Lpu_id
		order by MedPersonal_sname";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем список тренеров
	 */
	function getSportTrainer ($data)
	{
		
		$params = array(
			'SportTrainer_name' => $data['SportTrainer_name']
		);
		
		$query = "select 
				* 
			from 
			(
				select
					ST.SportTrainer_id,
					RTRIM (PS.PersonSurName_SurName) + ' '+ RTRIM (PS.PersonFirName_FirName) + ' ' + RTRIM (PS.PersonSecName_SecName) AS SportTrainer_name
				from dbo.SportTrainer ST with(nolock)
				left join PersonState PS with (nolock) on PS.Person_id = ST.Person_id
				where (ST.SportTrainer_endDT is null or ST.SportTrainer_endDT > getDate()) 
			) STrainer
			where SportTrainer_name like :SportTrainer_name + '%'";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем группы инвалидности
	 */
	function getDisabilityGroup ($data)
	{
		$params = array();
		
		$query = "select 
			IGT.InvalidGroupType_id,
			IGT.InvalidGroupType_Name
		from dbo.InvalidGroupType IGT with(nolock)";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем паралимпийские группы
	 */
	function getSportParaGroup ($data)
	{
		$params = array();
		
		$query = "select 
			SPG.SportParaGroup_id,
			SPG.SportParaGroup_name
		from dbo.SportParaGroup SPG with(nolock)
		where SPG.SportParaGroup_endDT is null or SPG.SportParaGroup_endDT > getDate()";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем даты всех анкет УМО спортсмена
	 */
	function getPersonUMODates ($data)
	{
		$params = array(
			'SportRegister_id' => $data['SportRegister_id']
		);
		
		$query = "select 
			SRUMO.SportRegisterUMO_id,
			convert(varchar(10), SRUMO.SportRegisterUMO_UMODate, 104) as SportRegisterUMO_UMODate,
			--SRUMO.SportRegisterUMO_UMODate as SportRegisterUMO_UMODate,
			ST.SportType_name,
			SRUMO.SportRegisterUMO_delDT
		from dbo.SportRegister SR with(nolock)
			left join dbo.SportRegisterUMO SRUMO with (nolock) on SR.SportRegister_id = SRUMO.SportRegister_id
			left join dbo.SportType ST with (nolock) on ST.SportType_id = SRUMO.SportType_id
		where SR.SportRegister_id = :SportRegister_id
		order by SRUMO.SportRegisterUMO_UMODate desc";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем заключения УМО
	 */
	function getUMOResult ($data)
	{
		$params = array();
		
		$query = "select 
			UR.UMOResult_id,
			UR.UMOResult_name
		from dbo.UMOResult UR with(nolock)
		where UR.UMOResult_endDT is null or UR.UMOResult_endDT > getDate()";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Добавляем спортсмена в регистр спортсменов
	 */
	function addSportRegister ($data)
	{
		$params = array(
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']);
		
		$query = "declare 
				@Person_id bigint,
				@pmUser_id bigint,
				@SportRegister_id int,
				@SportRegister_insDT datetime,
				@SportRegister_updDT datetime,
				@SportRegister_delDT datetime,
				@Error_Code int,
				@Error_Message varchar(4000);
			exec dbo.p_SportRegister_ins
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@SportRegister_id =  @SportRegister_id output,
				@SportRegister_insDT =  @SportRegister_insDT output,
				@SportRegister_updDT =  @SportRegister_updDT output,
				@SportRegister_delDT =  @SportRegister_delDT output,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Code output;
				
			select 
				@Error_Code as Error_Code, 
				@Error_Message as Error_Message, 
				@SportRegister_id as SportRegister_id, 
				@SportRegister_insDT as SportRegister_insDT, 
				@SportRegister_updDT as SportRegister_updDT, 
				@SportRegister_delDT as SportRegister_delDT";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Добавляем спортсмена в регистр спортсменов
	 */
	function SportRegisterDateUpdate ($data)
	{
		$params = array(
			'SportRegister_id' => $data['SportRegister_id'],
			'pmUser_id' => $data['pmUser_id']);
		
		$query = "declare 
				@SportRegister_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			exec dbo.p_SportRegister_upd
				@SportRegister_id = :SportRegister_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Code output;
				
			select 
				@Error_Code as Error_Code, 
				@Error_Message as Error_Message";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Проверяем спортсмена в регистре спортсменов по идентификатору человека
	 */
	function checkInSportRegister ($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);
		
		$query = "select top 1 
			 * 
		from dbo.SportRegister
		where Person_id = :Person_id";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем данные УМО по идентификатору
	 */
	function getSportRegisterUMO ($data)
	{
		$params = array(
			'SportRegisterUMO_id' => $data['SportRegisterUMO_id']
		);
		
		$query = "select top 1 
			SRUMO.SportRegisterUMO_id,
			SRUMO.SportRegisterUMO_UMODate as SportRegisterUMO_UMODate,
			SRUMO.InvalidGroupType_id,
			IGT.InvalidGroupType_name,
			SRUMO.SportParaGroup_id,
			SPG.SportParaGroup_name,
			SRUMO.SportRegisterUMO_IsTeamMember,
			SRUMO.SportType_id,
			ST.SportType_name,
			SRUMO.SportOrg_id,
			SO.SportOrg_name,
			SRUMO.SportStage_id,
			SS.SportStage_name,
			SRUMO.SportCategory_id,
			SC.SportCategory_name,
			SRUMO.UMOResult_id,
			UR.UMOResult_name,
			SRUMO.SportRegisterUMO_AdmissionDtBeg,
			SRUMO.SportRegisterUMO_AdmissionDtEnd,
			SRUMO.UMOResult_comment,
			SRUMO.SportRegisterUMO_updDT,
			SRUMO.SportRegisterUMO_delDT
		from dbo.SportRegisterUMO SRUMO with (nolock)
		left join dbo.InvalidGroupType IGT with (nolock) on IGT.InvalidGroupType_id = SRUMO.InvalidGroupType_id
		left join dbo.SportParaGroup SPG with (nolock) on SPG.SportParaGroup_id = SRUMO.SportParaGroup_id
		left join dbo.SportType ST with (nolock) on ST.SportType_id = SRUMO.SportType_id
		left join dbo.SportOrg SO with (nolock) on SO.SportOrg_id = SRUMO.SportOrg_id
		left join dbo.SportCategory SC with (nolock) on SC.SportCategory_id = SRUMO.SportCategory_id
		left join dbo.SportStage SS with (nolock) on SS.SportStage_id = SRUMO.SportStage_id
		left join dbo.UMOResult UR with (nolock) on UR.UMOResult_id = SRUMO.UMOResult_id
		where SportRegisterUMO_id = :SportRegisterUMO_id";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Проверяем наличие тренера в регистре спорсменов по идентификатору человека
	 */
	function checkInSportTrainer ($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);
		
		$query = "select top 1 
			 * 
		from dbo.SportTrainer with (nolock)
		where Person_id = :Person_id and SportTrainer_endDT is null or SportTrainer_endDT > getDate()";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем получаем причины исключения из регистра
	 */
	function getOutCauses ($data)
	{
		$params = array();
		
		$query = "select 
			PersonRegisterOutCause_id,
			PersonRegisterOutCause_Name
		from dbo.PersonRegisterOutCause with (nolock)
		where PersonRegisterOutCause_Code in (1, 2)
		union all
		select 
			PersonRegisterOutCause_id,
			PersonRegisterOutCause_Name
		from dbo.PersonRegisterOutCause with (nolock)
		where PersonRegisterOutCause_Code = 20
		union all
		select 
			PersonRegisterOutCause_id,
			PersonRegisterOutCause_Name
		from dbo.PersonRegisterOutCause with (nolock)
		where PersonRegisterOutCause_Code = 9"; // "1. Смерть", "2. Смена места жительства", "9. Иное" и "20. Не обращался в течении трёх лет" (отсутствует в БД на 21.12.2018)
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Получаем имена тренера, врача и медсестры конкретной анкеты УМО
	 */
	function getNames ($data)
	{
		$params = array(
			'SportRegisterUMO_id' => $data['SportRegisterUMO_id']
		);
		
		$query = "select top 1 
				SRUMO.SportTrainer_id,
				RTRIM (PSST.PersonSurName_SurName) + ' '+ RTRIM (PSST.PersonFirName_FirName) + ' ' + RTRIM (PSST.PersonSecName_SecName) AS SportTrainer_name,
				SRUMO.MedPersonal_pid,
				RTRIM (PSMPp.PersonSurName_SurName) + ' '+ RTRIM (PSMPp.PersonFirName_FirName) + ' ' + RTRIM (PSMPp.PersonSecName_SecName) AS MedPersonal_pname,
				SRUMO.MedPersonal_sid,
				RTRIM (PSMPs.PersonSurName_SurName) + ' '+ RTRIM (PSMPs.PersonFirName_FirName) + ' ' + RTRIM (PSMPs.PersonSecName_SecName) AS MedPersonal_sname
	
			from dbo.SportRegisterUMO SRUMO with(nolock)
			-- Тренер {
			left join SportTrainer ST with (nolock) on ST.SportTrainer_id = SRUMO.SportTrainer_id
			left join PersonState PSST with (nolock) on PSST.Person_id = ST.Person_id
			-- }	
			-- Врач {
			outer apply (select top 1 * from dbo.MedPersonalCache where MedPersonal_id = SRUMO.MedPersonal_pid) as MPp
			left join dbo.PersonState PSMPp with (nolock) on PSMPp.Person_id = MPp.Person_id
			-- }	
			-- Медсестра {
			outer apply (select top 1 * from dbo.MedPersonalCache where MedPersonal_id = SRUMO.MedPersonal_sid) as MPs
			left join dbo.PersonState PSMPs with (nolock) on PSMPs.Person_id = MPs.Person_id
			-- }
			where SRUMO.SportRegisterUMO_id = :SportRegisterUMO_id";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Добавляем тренера в регистр спортсменов
	 */
	function addSportTrainer ($data)
	{
		$params = array(
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']);
		
		$query = "declare 
				@Person_id bigint,
				@pmUser_id bigint,
				@SportTrainer_id int,
				@Error_Code int,
				@Error_Message varchar(4000);
			exec dbo.p_SportTrainer_ins
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@SportTrainer_id =  @SportTrainer_id output,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Code output;
				
			select @Error_Code as Error_Code, @Error_Message as Error_Message, @SportTrainer_id as SportTrainer_id;";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Добавляем анкету УМО спортсмена
	 */
	function addSportRegisterUMO ($data)
	{
		$params = array(
			'SportRegister_id' => $data['SportRegister_id'],
			'pmUser_id' => $data['pmUser_id'],
			'SportRegisterUMO_UMODate' => $data['SportRegisterUMO_UMODate'],
			'InvalidGroupType_id' => $data['InvalidGroupType_id'],
			'SportParaGroup_id' => $data['SportParaGroup_id'],
			'SportRegisterUMO_IsTeamMember' => $data['SportRegisterUMO_IsTeamMember'],
			'MedPersonal_pid' => $data['MedPersonal_pid'],
			'MedPersonal_sid' => $data['MedPersonal_sid'],
			'SportType_id' => $data['SportType_id'],
			'SportOrg_id' => $data['SportOrg_id'],
			'SportTrainer_id' => $data['SportTrainer_id'],
			'SportStage_id' => $data['SportStage_id'],
			'Lpu_id' => $data['Lpu_id'],
			'SportCategory_id' => $data['SportCategory_id'],
			'UMOResult_id' => $data['UMOResult_id'],
			'SportRegisterUMO_AdmissionDtBeg' => $data['SportRegisterUMO_AdmissionDtBeg'],
			'SportRegisterUMO_AdmissionDtEnd' => $data['SportRegisterUMO_AdmissionDtEnd'],
			'UMOResult_comment' => $data['UMOResult_comment']);
		
		$query = "declare 
				@SportRegister_id bigint,
				@pmUser_id bigint,
				@SportRegisterUMO_UMODate datetime,
				@InvalidGroupType_id bigint,
				@SportParaGroup_id bigint,
				@SportRegisterUMO_IsTeamMember bigint,
				@MedPersonal_pid bigint,
				@MedPersonal_sid bigint,
				@SportType_id bigint,
				@SportOrg_id bigint,
				@SportTrainer_id bigint,
				@SportStage_id bigint,
				@Lpu_id bigint,
				@SportCategory_id bigint,
				@UMOResult_id bigint,
				@SportRegisterUMO_AdmissionDtBeg datetime,
				@SportRegisterUMO_AdmissionDtEnd datetime,
				@UMOResult_comment varchar(1024),
				@Error_Code int,
				@Error_Message varchar(4000);
				
			exec dbo.p_SportRegisterUMO_ins
				@SportRegister_id = :SportRegister_id,
				@pmUser_id = :pmUser_id,
				@SportRegisterUMO_UMODate = :SportRegisterUMO_UMODate,
				@InvalidGroupType_id = :InvalidGroupType_id,
				@SportParaGroup_id = :SportParaGroup_id,
				@SportRegisterUMO_IsTeamMember = :SportRegisterUMO_IsTeamMember,
				@MedPersonal_pid = :MedPersonal_pid,
				@MedPersonal_sid = :MedPersonal_sid,
				@SportType_id = :SportType_id,
				@SportOrg_id = :SportOrg_id,
				@SportTrainer_id = :SportTrainer_id,
				@SportStage_id = :SportStage_id,
				@Lpu_id = :Lpu_id,
				@SportCategory_id = :SportCategory_id,
				@UMOResult_id = :UMOResult_id,
				@SportRegisterUMO_AdmissionDtBeg = :SportRegisterUMO_AdmissionDtBeg,
				@SportRegisterUMO_AdmissionDtEnd = :SportRegisterUMO_AdmissionDtEnd,
				@UMOResult_comment = :UMOResult_comment,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Code output;
				
			select @Error_Code as Error_Code, @Error_Message as Error_Message;";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Обновляем анкету УМО спортсмена
	 */
	function updateSportRegisterUMO ($data)
	{
		$params = array(
			'SportRegisterUMO_id' => $data['SportRegisterUMO_id'],
			'SportRegister_id' => $data['SportRegister_id'],
			'pmUser_id' => $data['pmUser_id'],
			'SportRegisterUMO_UMODate' => $data['SportRegisterUMO_UMODate'],
			'InvalidGroupType_id' => $data['InvalidGroupType_id'],
			'SportParaGroup_id' => $data['SportParaGroup_id'],
			'SportRegisterUMO_IsTeamMember' => $data['SportRegisterUMO_IsTeamMember'],
			'MedPersonal_pid' => $data['MedPersonal_pid'],
			'MedPersonal_sid' => $data['MedPersonal_sid'],
			'SportType_id' => $data['SportType_id'],
			'SportOrg_id' => $data['SportOrg_id'],
			'SportTrainer_id' => $data['SportTrainer_id'],
			'SportStage_id' => $data['SportStage_id'],
			'Lpu_id' => $data['Lpu_id'],
			'SportCategory_id' => $data['SportCategory_id'],
			'UMOResult_id' => $data['UMOResult_id'],
			'SportRegisterUMO_AdmissionDtBeg' => $data['SportRegisterUMO_AdmissionDtBeg'],
			'SportRegisterUMO_AdmissionDtEnd' => $data['SportRegisterUMO_AdmissionDtEnd'],
			'UMOResult_comment' => $data['UMOResult_comment']);
		
		$query = "declare 
				@SportRegisterUMO_id bigint,
				@SportRegister_id bigint,
				@pmUser_id bigint,
				@SportRegisterUMO_UMODate datetime,
				@InvalidGroupType_id bigint,
				@SportParaGroup_id bigint,
				@SportRegisterUMO_IsTeamMember bigint,
				@MedPersonal_pid bigint,
				@MedPersonal_sid bigint,
				@SportType_id bigint,
				@SportOrg_id bigint,
				@SportTrainer_id bigint,
				@SportStage_id bigint,
				@Lpu_id bigint,
				@SportCategory_id bigint,
				@UMOResult_id bigint,
				@SportRegisterUMO_AdmissionDtBeg datetime,
				@SportRegisterUMO_AdmissionDtEnd datetime,
				@UMOResult_comment varchar(1024),
				@Error_Code int,
				@Error_Message varchar(4000);
				
			exec dbo.p_SportRegisterUMO_upd
				@SportRegisterUMO_id = :SportRegisterUMO_id,
				@SportRegister_id = :SportRegister_id,
				@pmUser_id = :pmUser_id,
				@SportRegisterUMO_UMODate = :SportRegisterUMO_UMODate,
				@InvalidGroupType_id = :InvalidGroupType_id,
				@SportParaGroup_id = :SportParaGroup_id,
				@SportRegisterUMO_IsTeamMember = :SportRegisterUMO_IsTeamMember,
				@MedPersonal_pid = :MedPersonal_pid,
				@MedPersonal_sid = :MedPersonal_sid,
				@SportType_id = :SportType_id,
				@SportOrg_id = :SportOrg_id,
				@SportTrainer_id = :SportTrainer_id,
				@SportStage_id = :SportStage_id,
				@Lpu_id = :Lpu_id,
				@SportCategory_id = :SportCategory_id,
				@UMOResult_id = :UMOResult_id,
				@SportRegisterUMO_AdmissionDtBeg = :SportRegisterUMO_AdmissionDtBeg,
				@SportRegisterUMO_AdmissionDtEnd = :SportRegisterUMO_AdmissionDtEnd,
				@UMOResult_comment = :UMOResult_comment,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Code output;
				
			select @Error_Code as Error_Code, @Error_Message as Error_Message;";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Удаляем анкету УМО спортсмена
	 */
	function deleteSportRegisterUMO ($data)
	{
		$params = array(
			'SportRegisterUMO_id' => $data['SportRegisterUMO_id'],
			'pmUser_id' => $data['pmUser_id']);
		
		$query = "declare 
				@SportRegisterUMO_id bigint,
				@pmUser_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
				
			exec dbo.p_SportRegisterUMO_del
				@SportRegisterUMO_id = :SportRegisterUMO_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Code output;
				
			select @Error_Code as Error_Code, @Error_Message as Error_Message;";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Восстанавливаем человека из регистра спортсменов
	 */
	function restoreSportRegister ($data)
	{
		$params = array(
			'SportRegister_id' => $data['SportRegister_id'],
			'pmUser_id' => $data['pmUser_id']);
		
		$query = "declare 
				@SportRegister_id bigint,
				@pmUser_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
				
			exec dbo.p_SportRegister_rst
				@SportRegister_id = :SportRegister_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
                @Error_Message = @Error_Code output;
                
            select @Error_Code as Error_Code, @Error_Message as Error_Message;";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  Удаляем человека из регистра спортсменов
	 */
	function deleteSportRegister ($data)
	{
		$params = array(
			'SportRegister_id' => $data['SportRegister_id'],
			'PersonRegisterOutCause_id' => $data['PersonRegisterOutCause_id'],
			'SportRegister_detDT' => $data['SportRegister_detDT'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$query = "declare 
				@SportRegister_id bigint,
				@PersonRegisterOutCause_id bigint,
				@pmUser_id bigint,
				@SportRegister_detDT datetime,
				@Error_Code int,
				@Error_Message varchar(4000);
				
			exec dbo.p_SportRegister_del
				@SportRegister_id = :SportRegister_id,
				@PersonRegisterOutCause_id = :PersonRegisterOutCause_id,
				@SportRegister_detDT = :SportRegister_detDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
                @Error_Message = @Error_Code output;
                
            select @Error_Code as Error_Code, @Error_Message as Error_Message;";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *  печать Анкеты
	 */
	function PrintSportRegisterUMO ($data)
	{
		$params = array(
			'SportRegisterUMO_id' => $data['SportRegisterUMO_id']
		);
		
		$query = "declare @getDT datetime = dbo.tzGetDate();
			select top 1 
			SRUMO.SportRegisterUMO_id,-- дата 
			convert(varchar(10),SRUMO.SportRegisterUMO_UMODate,104) as SportRegisterUMO_UMODate,
			PS.Person_SurName + ' ' + PS.Person_FirName + ' '+ PS.Person_SecName as SFS,
			dbo.Age(PS.Person_BirthDay, @getDT) as Person_BirthDay,
			IGT.InvalidGroupType_name,
			SPG.SportParaGroup_name,
			case when (SRUMO.SportRegisterUMO_IsTeamMember = 2) then 'Да' else 'Нет' end as SportRegisterUMO_IsTeamMember,
			RTRIM (PSST.PersonSurName_SurName) + ' '+ RTRIM (PSST.PersonFirName_FirName) + ' ' + RTRIM (PSST.PersonSecName_SecName) AS SportTrainer_name,
			RTRIM (PSMPp.PersonSurName_SurName) + ' '+ RTRIM (PSMPp.PersonFirName_FirName) + ' ' + RTRIM (PSMPp.PersonSecName_SecName) AS MedPersonal_pname,
			RTRIM (PSMPs.PersonSurName_SurName) + ' '+ RTRIM (PSMPs.PersonFirName_FirName) + ' ' + RTRIM (PSMPs.PersonSecName_SecName) AS MedPersonal_sname,
			ST.SportType_name,
			SO.SportOrg_name,
			SS.SportStage_name,
			SC.SportCategory_name,
			UR.UMOResult_name,
			convert(varchar(10),SRUMO.SportRegisterUMO_AdmissionDtBeg,104) as SportRegisterUMO_AdmissionDtBeg, 
			convert(varchar(10),SRUMO.SportRegisterUMO_AdmissionDtEnd,104) as SportRegisterUMO_AdmissionDtEnd,
			UMOResult_comment,
			convert(varchar(10), PR.SportRegister_updDT, 104) as SportRegister_updDT
		from dbo.SportRegisterUMO SRUMO with (nolock)
		left join dbo.SportRegister PR with (nolock) on PR.SportRegister_id = SRUMO.SportRegister_id
		left join dbo.v_PersonState PS with (nolock) on PS.Person_id = PR.Person_id
		left join dbo.InvalidGroupType IGT with (nolock) on IGT.InvalidGroupType_id = SRUMO.InvalidGroupType_id
		left join dbo.SportParaGroup SPG with (nolock) on SPG.SportParaGroup_id = SRUMO.SportParaGroup_id
		left join dbo.SportType ST with (nolock) on ST.SportType_id = SRUMO.SportType_id
		left join dbo.SportOrg SO with (nolock) on SO.SportOrg_id = SRUMO.SportOrg_id
		left join dbo.SportCategory SC with (nolock) on SC.SportCategory_id = SRUMO.SportCategory_id
		left join dbo.SportStage SS with (nolock) on SS.SportStage_id = SRUMO.SportStage_id
		left join dbo.UMOResult UR with (nolock) on UR.UMOResult_id = SRUMO.UMOResult_id
		-- Тренер {
			left join SportTrainer STR with (nolock) on STR.SportTrainer_id = SRUMO.SportTrainer_id
			left join PersonState PSST with (nolock) on PSST.Person_id = STR.Person_id
			-- }	
			-- Врач {
			outer apply (select top 1 * from dbo.MedPersonalCache where MedPersonal_id = SRUMO.MedPersonal_pid) as MPp
			left join dbo.PersonState PSMPp with (nolock) on PSMPp.Person_id = MPp.Person_id
			-- }	
			-- Медсестра {
			outer apply (select top 1 * from dbo.MedPersonalCache where MedPersonal_id = SRUMO.MedPersonal_sid) as MPs
			left join dbo.PersonState PSMPs with (nolock) on PSMPs.Person_id = MPs.Person_id
			-- }
		where SportRegisterUMO_id = :SportRegisterUMO_id";
		
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
		
	}
	
	/**
	 * Получаем сведения о пациенте
	 */
	function loadPersonData ($data)
	{
		// Если передали PersonEvn_id, значит определенная периодика нужна и читать будем из периодики
		$object = "v_PersonState";
		$filter = " (1=1)";
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$InnField = "isnull(ps.Person_Inn,'') as Person_Inn";
		if ((isset($data['PersonEvn_id'])) && ($data['PersonEvn_id'] > 0)) {
			$object = "v_Person_bdz";
			$params['Server_id'] = $data['Server_id'];
			$filter .= " and PS.Server_id = :Server_id";
			$params['PersonEvn_id'] = $data['PersonEvn_id'];
			$filter .= " and PS.PersonEvn_id = :PersonEvn_id";
			$InnField = "isnull(ps.PersonInn_Inn,'') as Person_Inn";
		} else {
			$params['Person_id'] = $data['Person_id'];
			$filter .= " and PS.Person_id = :Person_id";
			$InnField = "isnull(ps.Person_Inn,'') as Person_Inn";
		}
		$extendFrom = "";
		$extendSelect = "";
		if ((isset($data['EvnDirection_id'])) && (!empty($data['EvnDirection_id']))) {
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$extendSelect = "
				,ED.EvnDirection_id
				,ED.EvnDirection_Num
				,ED.EvnDirection_setDT
			";
			$extendFrom .= "
				OUTER apply
				(SELECT top 1
					ED.EvnDirection_id,
					ED.EvnDirection_Num,
					isnull(convert(varchar(10), ED.EvnDirection_setDT, 104), '') as EvnDirection_setDT
					
				FROM
					v_EvnDirection_all ED WITH (NOLOCK)
				WHERE
					ED.EvnDirection_id = :EvnDirection_id
				) as ED
				";
		}
		
		$query = "
			SELECT TOP 1
				ps.Person_id,
				(select top 1 BSKRegistry_riskGroup from dbo.BSKRegistry with(nolock) where MorbusType_id = 84 and Person_id =  ps.Person_id order by BSKRegistry_setDate DESC) as BSKRegistry_riskGroup,
				
				(select BSKRegistryData_data from dbo.BSKRegistryData with(nolock) where BSKRegistry_id = 
				(
				select top 1 BSKRegistry_id from dbo.BSKRegistry with(nolock) 
				where 
				  Person_id = ps.Person_id  
				and
				  MorbusType_id = 88  
				order by   BSKRegistry_setDate DESC
				)
				and BSKObservElement_id = 151) as BSKRegistry_functionClass,            
				(select BSKRegistryData_data from dbo.BSKRegistryData with(nolock) where BSKRegistry_id = 
				(
				select top 1 BSKRegistry_id from dbo.BSKRegistry with(nolock)
				where 
				  Person_id = ps.Person_id  
				and
				  MorbusType_id = 89  
				order by   BSKRegistry_setDate DESC
				)
				and BSKObservElement_id = 269) as BSKRegistry_gegreeRisk,                   
				{$InnField},
				[dbo].[getPersonPhones](ps.Person_id, ',') as Person_Phone,
				ps.Server_id as Server_id,
				ps.Server_pid as Server_pid,
				CASE WHEN (pcard.PersonCard_endDate IS NOT NULL) THEN isnull(RTRIM(lpu.Lpu_Nick), '') + ' (Прикрепление неактуально. Дата открепления: '+isnull(convert(varchar(10), pcard.PersonCard_endDate, 104), '')+')' ELSE isnull(RTRIM(lpu.Lpu_Nick), '') end as Lpu_Nick,
				PersonState.Lpu_id as Lpu_id,
				pcard.PersonCard_id,
				isnull(RTRIM(PS.Person_SurName), '') as Person_Surname,
				isnull(RTRIM(PS.Person_FirName), '') as Person_Firname,
				isnull(RTRIM(PS.Person_SecName), '') as Person_Secname,
				isnull(RTRIM(PS.PersonEvn_id), '') as PersonEvn_id,
				isnull(convert(varchar(10), PS.Person_BirthDay, 104), '') as Person_Birthday,
				(datediff(year, PS.Person_Birthday, dbo.tzGetDate())
					+ case when month(PS.Person_Birthday) > month(dbo.tzGetDate())
					or (month(PS.Person_Birthday) = month(dbo.tzGetDate()) and day(PS.Person_Birthday) > day(dbo.tzGetDate()))
					then -1 else 0 end) as Person_Age,
				case when (KLArea.KLSocr_id = 68) or (KLArea.KLSocr_id = 56) then '1' else '0' end as KLAreaType_id,
				isnull(RTRIM(PS.Person_Snils), '') as Person_Snils,
				isnull(RTRIM(Sex.Sex_Name), '') as Sex_Name,
				isnull(RTRIM(Sex.Sex_Code), '') as Sex_Code,
				isnull(RTRIM(Sex.Sex_id), '') as Sex_id,
				isnull(RTRIM(SocStatus.SocStatus_Name), '') as SocStatus_Name,
				ps.SocStatus_id,
				RTRIM(isnull(UAddress.Address_Nick, UAddress.Address_Address)) as Person_RAddress,
				RTRIM(isnull(PAddress.Address_Nick, PAddress.Address_Address)) as Person_PAddress,
				isnull(RTRIM(Document.Document_Num), '') as Document_Num,
				isnull(RTRIM(Document.Document_Ser), '') as Document_Ser,
				isnull(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				isnull(RTRIM(DO.Org_Name), '') as OrgDep_Name,
				isnull(OmsSprTerr.OmsSprTerr_id, 0) as OmsSprTerr_id,
				isnull(OmsSprTerr.OmsSprTerr_Code, 0) as OmsSprTerr_Code,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE isnull(RTRIM(Polis.Polis_Ser), '') END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(vper.Person_EdNum), '') ELSE isnull(RTRIM(Polis.Polis_Num), '') END AS Polis_Num,
				isnull(convert(varchar(10), pcard.PersonCard_begDate, 104), '') as PersonCard_begDate,
				isnull(convert(varchar(10), pcard.PersonCard_endDate, 104), '') as PersonCard_endDate,
				isnull(convert(varchar(10), pcard.LpuRegion_Name, 104), '') as LpuRegion_Name,
				isnull(convert(varchar(10), Polis.Polis_begDate, 104), '') as Polis_begDate,
				isnull(convert(varchar(10), Polis.Polis_endDate, 104), '') as Polis_endDate,
				isnull(RTRIM(PO.Org_Name), '') as OrgSmo_Name,
				isnull(RTRIM(PJ.Org_id), '') as JobOrg_id,
				isnull(RTRIM(PJ.Org_Name), '') as Person_Job,
				isnull(RTRIM(PP.Post_Name), '') as Person_Post,
				'' as Ident_Lpu,
				CASE WHEN PR.PersonRefuse_IsRefuse = 2
					THEN 'true' ELSE 'false' END as Person_IsRefuse,
				CASE WHEN PS.Server_pid = 0 THEN 1 ELSE 0 END AS Person_IsBDZ,
				CASE WHEN PersonPrivilegeFed.Person_id IS NOT NULL THEN 1 ELSE 0 END AS Person_IsFedLgot,
				isnull(convert(varchar(10), Person.Person_deadDT, 104), '') as Person_deadDT,
				isnull(convert(varchar(10), Person.Person_closeDT, 104), '') as Person_closeDT,
				Person.Person_IsDead,
				Person.PersonCloseCause_id,
				0 as Children_Count
				,PersonPrivilegeFed.PrivilegeType_id
				,PersonPrivilegeFed.PrivilegeType_Name
				{$extendSelect}
			FROM [{$object}] [PS] WITH (NOLOCK)
				left join [Sex] WITH (NOLOCK) on [Sex].[Sex_id] = [PS].[Sex_id]
				left join [SocStatus] WITH (NOLOCK) on [SocStatus].[SocStatus_id] = [PS].[SocStatus_id]
				left join [Address] [UAddress] WITH (NOLOCK) on [UAddress].[Address_id] = [PS].[UAddress_id]
				left join [v_KLArea] [KLArea] WITH (NOLOCK) on [KLArea].[KLArea_id] = [UAddress].[KLTown_id]
				left join [Address] [PAddress] WITH (NOLOCK) on [PAddress].[Address_id] = [PS].[PAddress_id]
				left join [v_Job] [Job] WITH (NOLOCK) on [Job].[Job_id] = [PS].[Job_id]
				left join [Org] [PJ] WITH (NOLOCK) on [PJ].[Org_id] = [Job].[Org_id]
				left join [Post] [PP] WITH (NOLOCK) on [PP].[Post_id] = [Job].[Post_id]
				left join [Document] WITH (NOLOCK) on [Document].[Document_id] = [PS].[Document_id]
				left join [OrgDep] WITH (NOLOCK) on [OrgDep].[OrgDep_id] = [Document].[OrgDep_id]
				left join [Org] [DO] WITH (NOLOCK) on [DO].[Org_id] = [OrgDep].[Org_id]
				left join [Polis] WITH (NOLOCK) on [Polis].[Polis_id] = [PS].[Polis_id]
				left join [PolisType] WITH (NOLOCK) on [PolisType].[PolisType_id] = [Polis].[PolisType_id]
				left join [OmsSprTerr] WITH (NOLOCK) on [OmsSprTerr].[OmsSprTerr_id] = [Polis].[OmsSprTerr_id]
				left join [OrgSmo] WITH (NOLOCK) on [OrgSmo].[OrgSmo_id] = [Polis].[OrgSmo_id]
				left join [Org] [PO] WITH (NOLOCK) on [PO].[Org_id] = [OrgSmo].[Org_id]
				left join [Person] WITH (NOLOCK) on [Person].[Person_id] = [PS].[Person_id]
				left join [PersonState] with (nolock) on [PS].[Person_id] = [PersonState].[Person_id]
				outer apply (
				select Top 1 vper.Person_edNum
				from v_Person_all vper with(nolock) 
				where vper.Person_id = [PS].[Person_id]
				and vper.PersonEvn_id = PS.PersonEvn_id
				)vper
				OUTER apply
				(SELECT top 1
					PP.Person_id
					,PP.PrivilegeType_id
					,PT.PrivilegeType_Name
				FROM
					v_PersonPrivilege PP WITH (NOLOCK)
					inner join v_PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
				WHERE
					PP.PrivilegeType_id <= 150 AND
					PP.PersonPrivilege_begDate <= dbo.tzGetDate() AND
					(PP.PersonPrivilege_endDate IS NULL OR
					PP.PersonPrivilege_endDate >= cast(dbo.tzGetDate() AS date)) AND
					PP.Person_id = PS.Person_id
				) PersonPrivilegeFed
				outer apply (select top 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate,
						pc.LpuRegion_Name
					from v_PersonCard pc WITH (NOLOCK)
					where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					) as pcard 
				left join v_Lpu lpu WITH (NOLOCK) on lpu.Lpu_id=PersonState.Lpu_id
				LEFT JOIN v_PersonRefuse PR WITH (NOLOCK) ON PR.Person_id = ps.Person_id and PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate())
				{$extendFrom}
			WHERE {$filter}
		";
		
		sql_log_message('error', 'Search_model loadPersonData: ', getDebugSql($query, $params));
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}

?>