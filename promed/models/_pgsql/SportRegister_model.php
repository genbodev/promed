<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 * SportRegister_model - молеь для работы с данными регистра спортсменов (Башкирия)
 *
 * @package            SportRegistry
 * @author             Хамитов Марат
 * @version            12.2018
 */
class SportRegister_model extends SwPgModel
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
			ST.SportType_id as \"SportType_id\",
			ST.SportType_name as \"SportType_name\"
		from dbo.SportType ST 
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
			SS.SportStage_id as \"SportStage_id\",
			SS.SportStage_name as \"SportStage_name\"
		from dbo.SportStage SS 
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
			SC.SportCategory_id as \"SportCategory_id\",
			SC.SportCategory_name as \"SportCategory_name\"
		from dbo.SportCategory SC 
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
			SO.SportOrg_id as \"SportOrg_id\",
			SO.SportOrg_name as \"SportOrg_name\"
		from dbo.SportOrg SO ";

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
			SRUMO.MedPersonal_pid as \"MedPersonal_pid\",
			RTRIM (PS.PersonSurName_SurName) || ' ' || RTRIM (PS.PersonFirName_FirName) || ' ' || RTRIM (PS.PersonSecName_SecName) AS \"MedPersonal_pname\"
		from SportRegisterUMO SRUMO 
		left join MedPersonalCache MP  on MP.MedPersonal_id = SRUMO.MedPersonal_pid
		left join PersonState PS  on PS.Person_id = MP.Person_id";

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
			MP.MedPersonal_id as \"MedPersonal_pid\",
			RTRIM (PS.PersonSurName_SurName) || ' ' || RTRIM (PS.PersonFirName_FirName) || ' ' || RTRIM (PS.PersonSecName_SecName) AS \"MedPersonal_pname\"
		from MedPersonalCache MP 
		left join PersonState PS  on PS.Person_id = MP.Person_id
		where MP.Lpu_id = :Lpu_id
		order by \"MedPersonal_pname\"";

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
			MP.MedPersonal_id as \"MedPersonal_sid\",
			RTRIM (PS.PersonSurName_SurName) || ' ' || RTRIM (PS.PersonFirName_FirName) || ' ' || RTRIM (PS.PersonSecName_SecName) AS \"MedPersonal_sname\"
		from MedPersonalCache MP 
		left join PersonState PS  on PS.Person_id = MP.Person_id
		where MP.Lpu_id = :Lpu_id
		order by \"MedPersonal_sname\"";

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
				SportTrainer_id as \"SportTrainer_id\",
				SportTrainer_name as \"SportTrainer_name\"
			from
			(
				select
					ST.SportTrainer_id,
					RTRIM (PS.PersonSurName_SurName) || ' ' || RTRIM (PS.PersonFirName_FirName) || ' ' || RTRIM (PS.PersonSecName_SecName) AS SportTrainer_name
				from dbo.SportTrainer ST
				left join PersonState PS on PS.Person_id = ST.Person_id
				where (ST.SportTrainer_endDT is null or ST.SportTrainer_endDT > getDate())
			) STrainer
			where SportTrainer_name ilike :SportTrainer_name || '%'";

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
			IGT.InvalidGroupType_id as \"InvalidGroupType_id\",
			IGT.InvalidGroupType_Name as \"InvalidGroupType_Name\"
		from dbo.InvalidGroupType IGT ";

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
			SPG.SportParaGroup_id as \"SportParaGroup_id\",
			SPG.SportParaGroup_name as \"SportParaGroup_name\"
		from dbo.SportParaGroup SPG 
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
			SRUMO.SportRegisterUMO_id as \"SportRegisterUMO_id\",
			to_char(SRUMO.SportRegisterUMO_UMODate, 'dd.mm.yyyy') as \"SportRegisterUMO_UMODate\",
			--SRUMO.SportRegisterUMO_UMODate as SportRegisterUMO_UMODate,
			ST.SportType_name as \"SportType_name\",
			SRUMO.SportRegisterUMO_delDT as \"SportRegisterUMO_delDT\"
		from dbo.SportRegister SR 
			left join dbo.SportRegisterUMO SRUMO on SR.SportRegister_id = SRUMO.SportRegister_id
			left join dbo.SportType ST on ST.SportType_id = SRUMO.SportType_id
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
			UR.UMOResult_id as \"UMOResult_id\",
			UR.UMOResult_name as \"UMOResult_name\"
		from dbo.UMOResult UR 
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

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Message\",
            SportRegister_id as \"SportRegister_id\",
			SportRegister_insDT as \"SportRegister_insDT\",
			SportRegister_updDT as \"SportRegister_updDT\",
			SportRegister_delDT as \"SportRegister_delDT\"
        from dbo.p_SportRegister_ins
            (
 				Person_id := :Person_id,
				pmUser_id := :pmUser_id
            )";



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


        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Message\"
        from dbo.p_SportRegister_upd
            (
				SportRegister_id := :SportRegister_id,
				pmUser_id := :pmUser_id
            )";


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

		$query = "select
            SportRegister_id as \"SportRegister_id\",
            Person_id as \"Person_id\",
            pmUser_insID as \"pmUser_insID\",
            pmUser_updID as \"pmUser_updID\",
            pmUser_delID as \"pmUser_delID\",
            SportRegister_insDT as \"SportRegister_insDT\",
            SportRegister_updDT as \"SportRegister_updDT\",
            SportRegister_delDT as \"SportRegister_delDT\",
            PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
            Region_id as \"Region_id\",
            SportRegister_deleted as \"SportRegister_deleted\"
		from dbo.SportRegister
		where Person_id = :Person_id
        limit 1";

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

		$query = "select 
			SRUMO.SportRegisterUMO_id as \"SportRegisterUMO_id\",
			SRUMO.SportRegisterUMO_UMODate as \"SportRegisterUMO_UMODate\",
			SRUMO.InvalidGroupType_id as \"InvalidGroupType_id\",
			IGT.InvalidGroupType_name as \"InvalidGroupType_name\",
			SRUMO.SportParaGroup_id as \"SportParaGroup_id\",
			SPG.SportParaGroup_name as \"SportParaGroup_name\",
			SRUMO.SportRegisterUMO_IsTeamMember as \"SportRegisterUMO_IsTeamMember\",
			SRUMO.SportType_id as \"SportType_id\",
			ST.SportType_name as \"SportType_name\",
			SRUMO.SportOrg_id as \"SportOrg_id\",
			SO.SportOrg_name as \"SportOrg_name\",
			SRUMO.SportStage_id as \"SportStage_id\",
			SS.SportStage_name as \"SportStage_name\",
			SRUMO.SportCategory_id as \"SportCategory_id\",
			SC.SportCategory_name as \"SportCategory_name\",
			SRUMO.UMOResult_id as \"UMOResult_id\",
			UR.UMOResult_name as \"UMOResult_name\",
			SRUMO.SportRegisterUMO_AdmissionDtBeg as \"SportRegisterUMO_AdmissionDtBeg\",
			SRUMO.SportRegisterUMO_AdmissionDtEnd as \"SportRegisterUMO_AdmissionDtEnd\",
			SRUMO.UMOResult_comment as \"UMOResult_comment\",
			SRUMO.SportRegisterUMO_updDT as \"SportRegisterUMO_updDT\",
			SRUMO.SportRegisterUMO_delDT as \"SportRegisterUMO_delDT\"
		from dbo.SportRegisterUMO SRUMO 
		left join dbo.InvalidGroupType IGT  on IGT.InvalidGroupType_id = SRUMO.InvalidGroupType_id
		left join dbo.SportParaGroup SPG  on SPG.SportParaGroup_id = SRUMO.SportParaGroup_id
		left join dbo.SportType ST  on ST.SportType_id = SRUMO.SportType_id
		left join dbo.SportOrg SO  on SO.SportOrg_id = SRUMO.SportOrg_id
		left join dbo.SportCategory SC  on SC.SportCategory_id = SRUMO.SportCategory_id
		left join dbo.SportStage SS  on SS.SportStage_id = SRUMO.SportStage_id
		left join dbo.UMOResult UR  on UR.UMOResult_id = SRUMO.UMOResult_id
		where SportRegisterUMO_id = :SportRegisterUMO_id
        limit 1
        ";

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

		$query = "select
			SportTrainer_id as \"SportTrainer_id\",
            Person_id as \"Person_id\",
            SportTrainer_begDT as \"SportTrainer_begDT\",
            SportTrainer_endDT as \"SportTrainer_endDT\",
            pmUser_insID as \"pmUser_insID\",
            pmUser_updID as \"pmUser_updID\",
            pmUser_delID as \"pmUser_delID\",
            SportTrainer_insDT as \"SportTrainer_insDT\",
            SportTrainer_updDT as \"SportTrainer_updDT\",
            SportTrainer_delDT as \"SportTrainer_delDT\",
            Region_id as \"Region_id\",
            SportTrainer_deleted as \"SportTrainer_deleted\"
		from dbo.SportTrainer
		where Person_id = :Person_id and SportTrainer_endDT is null or SportTrainer_endDT > getDate()
        limit 1
        ";

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

		$query = "
        (
        select
			PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\"
		from dbo.PersonRegisterOutCause
		where PersonRegisterOutCause_Code in (1, 2)
        )
		union all
        (
		select
			PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\"
		from dbo.PersonRegisterOutCause
		where PersonRegisterOutCause_Code = 20
        )
		union all
        (
		select
			PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
			PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\"
		from dbo.PersonRegisterOutCause
		where PersonRegisterOutCause_Code = 9
        )";
        // "1. Смерть", "2. Смена места жительства", "9. Иное" и "20. Не обращался в течении трёх лет" (отсутствует в БД на 21.12.2018)
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

		$query = "select
				SRUMO.SportTrainer_id as \"SportTrainer_id\",
				RTRIM (PSST.PersonSurName_SurName) || ' ' || RTRIM (PSST.PersonFirName_FirName) || ' ' || RTRIM (PSST.PersonSecName_SecName) AS \"SportTrainer_name\",
				SRUMO.MedPersonal_pid as \"MedPersonal_pid\",
				RTRIM (PSMPp.PersonSurName_SurName) || ' ' || RTRIM (PSMPp.PersonFirName_FirName) || ' ' || RTRIM (PSMPp.PersonSecName_SecName) AS \"MedPersonal_pname\",
				SRUMO.MedPersonal_sid as \"MedPersonal_sid\",
				RTRIM (PSMPs.PersonSurName_SurName) || ' ' || RTRIM (PSMPs.PersonFirName_FirName) || ' ' || RTRIM (PSMPs.PersonSecName_SecName) AS \"MedPersonal_sname\"

			from dbo.SportRegisterUMO SRUMO
			-- Тренер {
			left join SportTrainer ST  on ST.SportTrainer_id = SRUMO.SportTrainer_id
			left join PersonState PSST  on PSST.Person_id = ST.Person_id
			-- }
			-- Врач {
			LEFT JOIN LATERAL (
                        select  *
                        from dbo.MedPersonalCache
                        where MedPersonal_id = SRUMO.MedPersonal_pid
                        limit 1
                        ) as MPp on true
			left join dbo.PersonState PSMPp  on PSMPp.Person_id = MPp.Person_id
			-- }
			-- Медсестра {
			LEFT JOIN LATERAL (
                        select *
                        from dbo.MedPersonalCache
                        where MedPersonal_id = SRUMO.MedPersonal_sid
                        limit 1
                        ) as MPs on true
			left join dbo.PersonState PSMPs  on PSMPs.Person_id = MPs.Person_id
			-- }
			where SRUMO.SportRegisterUMO_id = :SportRegisterUMO_id
            limit 1
            ";

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

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Message\",
            SportTrainer_id as \"SportTrainer_id\"
        from dbo.p_SportTrainer_ins
            (
 				Person_id := :Person_id,
				pmUser_id := :pmUser_id
            )";


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


        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Message\"
        from dbo.p_SportRegisterUMO_ins
            (
 				SportRegister_id := :SportRegister_id,
				pmUser_id := :pmUser_id,
				SportRegisterUMO_UMODate := :SportRegisterUMO_UMODate,
				InvalidGroupType_id := :InvalidGroupType_id,
				SportParaGroup_id := :SportParaGroup_id,
				SportRegisterUMO_IsTeamMember := :SportRegisterUMO_IsTeamMember,
				MedPersonal_pid := :MedPersonal_pid,
				MedPersonal_sid := :MedPersonal_sid,
				SportType_id := :SportType_id,
				SportOrg_id := :SportOrg_id,
				SportTrainer_id := :SportTrainer_id,
				SportStage_id := :SportStage_id,
				Lpu_id := :Lpu_id,
				SportCategory_id := :SportCategory_id,
				UMOResult_id := :UMOResult_id,
				SportRegisterUMO_AdmissionDtBeg := :SportRegisterUMO_AdmissionDtBeg,
				SportRegisterUMO_AdmissionDtEnd := :SportRegisterUMO_AdmissionDtEnd,
				UMOResult_comment := :UMOResult_comment
            )";


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

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Message\"
        from dbo.p_SportRegisterUMO_upd
            (
 				SportRegisterUMO_id := :SportRegisterUMO_id,
				SportRegister_id := :SportRegister_id,
				pmUser_id := :pmUser_id,
				SportRegisterUMO_UMODate := :SportRegisterUMO_UMODate,
				InvalidGroupType_id := :InvalidGroupType_id,
				SportParaGroup_id := :SportParaGroup_id,
				SportRegisterUMO_IsTeamMember := :SportRegisterUMO_IsTeamMember,
				MedPersonal_pid := :MedPersonal_pid,
				MedPersonal_sid := :MedPersonal_sid,
				SportType_id := :SportType_id,
				SportOrg_id := :SportOrg_id,
				SportTrainer_id := :SportTrainer_id,
				SportStage_id := :SportStage_id,
				Lpu_id := :Lpu_id,
				SportCategory_id := :SportCategory_id,
				UMOResult_id := :UMOResult_id,
				SportRegisterUMO_AdmissionDtBeg := :SportRegisterUMO_AdmissionDtBeg,
				SportRegisterUMO_AdmissionDtEnd := :SportRegisterUMO_AdmissionDtEnd,
				UMOResult_comment := :UMOResult_comment
            )";


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

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Message\"
        from dbo.p_SportRegisterUMO_del
            (
				SportRegisterUMO_id := :SportRegisterUMO_id,
				pmUser_id := :pmUser_id
            )";


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

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Message\"
        from dbo.p_SportRegister_rst
            (
				SportRegister_id := :SportRegister_id,
				pmUser_id := :pmUser_id
            )";

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

 /*
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
*/

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Message\"
        from dbo.p_SportRegister_del
            (
				SportRegister_id := :SportRegister_id,
				PersonRegisterOutCause_id := :PersonRegisterOutCause_id,
				SportRegister_detDT := :SportRegister_detDT,
				pmUser_id := :pmUser_id
            )";



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

		$query = "
			select
			SRUMO.SportRegisterUMO_id as \"SportRegisterUMO_id\",-- дата
			to_char(SRUMO.SportRegisterUMO_UMODate,'dd.mm.yyyy') as \"SportRegisterUMO_UMODate\",
			PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || PS.Person_SecName as \"SFS\",
			dbo.Age(PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_BirthDay\",
			IGT.InvalidGroupType_name as \"InvalidGroupType_name\",
			SPG.SportParaGroup_name as \"SportParaGroup_name\",
			case when (SRUMO.SportRegisterUMO_IsTeamMember = 2) then 'Да' else 'Нет' end as \"SportRegisterUMO_IsTeamMember\",
			RTRIM (PSST.PersonSurName_SurName) || ' ' || RTRIM (PSST.PersonFirName_FirName) || ' ' || RTRIM (PSST.PersonSecName_SecName) AS \"SportTrainer_name\",
			RTRIM (PSMPp.PersonSurName_SurName) || ' ' || RTRIM (PSMPp.PersonFirName_FirName) || ' ' || RTRIM (PSMPp.PersonSecName_SecName) AS \"MedPersonal_pname\",
			RTRIM (PSMPs.PersonSurName_SurName) || ' ' || RTRIM (PSMPs.PersonFirName_FirName) || ' ' || RTRIM (PSMPs.PersonSecName_SecName) AS \"MedPersonal_sname\",
			ST.SportType_name as \"SportType_name\",
			SO.SportOrg_name as \"SportOrg_name\",
			SS.SportStage_name as \"SportStage_name\",
			SC.SportCategory_name as \"SportCategory_name\",
			UR.UMOResult_name as \"UMOResult_name\",
			to_char(SRUMO.SportRegisterUMO_AdmissionDtBeg,'dd.mm.yyyy') as \"SportRegisterUMO_AdmissionDtBeg\",
			to_char(SRUMO.SportRegisterUMO_AdmissionDtEnd,'dd.mm.yyyy') as \"SportRegisterUMO_AdmissionDtEnd\",
			UMOResult_comment as \"UMOResult_comment\",
			to_char(PR.SportRegister_updDT, 'dd.mm.yyyy') as \"SportRegister_updDT\"
		from dbo.SportRegisterUMO SRUMO 
		left join dbo.SportRegister PR  on PR.SportRegister_id = SRUMO.SportRegister_id
		left join dbo.v_PersonState PS  on PS.Person_id = PR.Person_id
		left join dbo.InvalidGroupType IGT on IGT.InvalidGroupType_id = SRUMO.InvalidGroupType_id
		left join dbo.SportParaGroup SPG  on SPG.SportParaGroup_id = SRUMO.SportParaGroup_id
		left join dbo.SportType ST  on ST.SportType_id = SRUMO.SportType_id
		left join dbo.SportOrg SO  on SO.SportOrg_id = SRUMO.SportOrg_id
		left join dbo.SportCategory SC  on SC.SportCategory_id = SRUMO.SportCategory_id
		left join dbo.SportStage SS  on SS.SportStage_id = SRUMO.SportStage_id
		left join dbo.UMOResult UR on UR.UMOResult_id = SRUMO.UMOResult_id
		-- Тренер {
			left join SportTrainer STR  on STR.SportTrainer_id = SRUMO.SportTrainer_id
			left join PersonState PSST  on PSST.Person_id = STR.Person_id
			-- }
			-- Врач {
			LEFT JOIN LATERAL (
                    select  *
                    from dbo.MedPersonalCache
                    where MedPersonal_id = SRUMO.MedPersonal_pid
                    limit 1
                        ) as MPp on true
			left join dbo.PersonState PSMPp  on PSMPp.Person_id = MPp.Person_id
			-- }
			-- Медсестра {
			LEFT JOIN LATERAL (
                    select *
                    from dbo.MedPersonalCache
                    where MedPersonal_id = SRUMO.MedPersonal_sid
                    limit 1
                        ) as MPs on true
			left join dbo.PersonState PSMPs  on PSMPs.Person_id = MPs.Person_id
			-- }
		where SportRegisterUMO_id = :SportRegisterUMO_id
        limit 1
        ";

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
		$InnField = "Coalesce(ps.Person_Inn,'') as \"Person_Inn\"";
		if ((isset($data['PersonEvn_id'])) && ($data['PersonEvn_id'] > 0)) {
			$object = "v_Person_bdz";
			$params['Server_id'] = $data['Server_id'];
			$filter .= " and PS.Server_id = :Server_id";
			$params['PersonEvn_id'] = $data['PersonEvn_id'];
			$filter .= " and PS.PersonEvn_id = :PersonEvn_id";
			$InnField = "Coalesce(ps.PersonInn_Inn,'') as \"Person_Inn\"";
		} else {
			$params['Person_id'] = $data['Person_id'];
			$filter .= " and PS.Person_id = :Person_id";
			$InnField = "Coalesce(ps.Person_Inn,'') as \"Person_Inn\"";
		}
		$extendFrom = "";
		$extendSelect = "";
		if ((isset($data['EvnDirection_id'])) && (!empty($data['EvnDirection_id']))) {
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$extendSelect = "
				,ED.EvnDirection_id as \"EvnDirection_id\"
				,ED.EvnDirection_Num as \"EvnDirection_Num\"
				,ED.EvnDirection_setDT as \"EvnDirection_setDT\"
			";
			$extendFrom .= "
				LEFT JOIN LATERAL
				(SELECT
					ED.EvnDirection_id,
					ED.EvnDirection_Num,
					Coalesce(to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy'), '') as EvnDirection_setDT

				FROM
					v_EvnDirection_all ED
				WHERE
					ED.EvnDirection_id = :EvnDirection_id
                limit 1
				) as ED
				";
		}

		$query = "
			SELECT
				ps.Person_id as \"Person_id\",
				(select BSKRegistry_riskGroup
                from dbo.BSKRegistry
                where MorbusType_id = 84 and Person_id =  ps.Person_id
                order by BSKRegistry_setDate DESC
                limit 1
                ) as \"BSKRegistry_riskGroup\",

				(select BSKRegistryData_data
                from dbo.BSKRegistryData
                where BSKRegistry_id =
				        (
				        select  BSKRegistry_id from dbo.BSKRegistry
				        where
				          Person_id = ps.Person_id
				        and
				          MorbusType_id = 88
				        order by   BSKRegistry_setDate DESC
                        limit 1
				        )
				        and BSKObservElement_id = 151
                ) as \"BSKRegistry_functionClass\",
				(select BSKRegistryData_data
                from dbo.BSKRegistryData
                where BSKRegistry_id =
				        (
				        select BSKRegistry_id from dbo.BSKRegistry
				        where
				          Person_id = ps.Person_id
				        and
				          MorbusType_id = 89
				        order by   BSKRegistry_setDate DESC
                        limit 1
				        )
				            and BSKObservElement_id = 269
                        ) as \"BSKRegistry_gegreeRisk\",
				{$InnField},
				dbo.getPersonPhones(ps.Person_id, ',') as \"Person_Phone\",
				ps.Server_id as \"Server_id\",
				ps.Server_pid as \"Server_pid\",
				CASE WHEN (pcard.PersonCard_endDate IS NOT NULL) THEN Coalesce(RTRIM(lpu.Lpu_Nick), '') || ' (Прикрепление неактуально. Дата открепления: ' || Coalesce(to_char(pcard.PersonCard_endDate, 'dd.mm.yyyy'), '') || ')' ELSE Coalesce(RTRIM(lpu.Lpu_Nick), '') end as \"Lpu_Nick\",
				PersonState.Lpu_id as \"Lpu_id\",
				pcard.PersonCard_id as \"PersonCard_id\",
				Coalesce(RTRIM(PS.Person_SurName), '') as \"Person_Surname\",
				Coalesce(RTRIM(PS.Person_FirName), '') as \"Person_Firname\",
				Coalesce(RTRIM(PS.Person_SecName), '') as \"Person_Secname\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				Coalesce(to_char(PS.Person_BirthDay, 'dd.mm.yyyy'), '') as \"Person_Birthday\",
				(date_part('year',dbo.tzGetDate()-PS.Person_Birthday)
					+ case when date_part('month',PS.Person_Birthday) > date_part('month',dbo.tzGetDate())
					or (date_part('month',PS.Person_Birthday) = date_part('month',dbo.tzGetDate()) and date_part('day',PS.Person_Birthday) > date_part('day',dbo.tzGetDate()))
					then -1 else 0 end) as \"Person_Age\",
				case when (KLArea.KLSocr_id = 68) or (KLArea.KLSocr_id = 56) then '1' else '0' end as \"KLAreaType_id\",
				Coalesce(RTRIM(PS.Person_Snils), '') as \"Person_Snils\",
				Coalesce(RTRIM(Sex.Sex_Name), '') as \"Sex_Name\",
				Sex.Sex_Code as \"Sex_Code\",
				Sex.Sex_id as \"Sex_id\",
				Coalesce(RTRIM(SocStatus.SocStatus_Name), '') as \"SocStatus_Name\",
				ps.SocStatus_id as \"SocStatus_id\",
				RTRIM(Coalesce(UAddress.Address_Nick, UAddress.Address_Address)) as \"Person_RAddress\",
				RTRIM(Coalesce(PAddress.Address_Nick, PAddress.Address_Address)) as \"Person_PAddress\",
				Coalesce(RTRIM(Document.Document_Num), '') as \"Document_Num\",
				Coalesce(RTRIM(Document.Document_Ser), '') as \"Document_Ser\",
				Coalesce(to_char(Document.Document_begDate, 'dd.mm.yyyy'), '') as \"Document_begDate\",
				Coalesce(RTRIM(DoOrg.Org_Name), '') as \"OrgDep_Name\",
				Coalesce(OmsSprTerr.OmsSprTerr_id, 0) as \"OmsSprTerr_id\",
				Coalesce(OmsSprTerr.OmsSprTerr_Code, 0) as \"OmsSprTerr_Code\",
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE Coalesce(RTRIM(Polis.Polis_Ser), '') END as \"Polis_Ser\",
				CASE WHEN PolisType.PolisType_Code = 4 then Coalesce(RTRIM(vper.Person_EdNum), '') ELSE Coalesce(RTRIM(Polis.Polis_Num), '') END AS \"Polis_Num\",
				Coalesce(to_char(pcard.PersonCard_begDate, 'dd.mm.yyyy'), '') as \"PersonCard_begDate\",
				Coalesce(to_char(pcard.PersonCard_endDate, 'dd.mm.yyyy'), '') as \"PersonCard_endDate\",
				Coalesce(pcard.LpuRegion_Name, '') as \"LpuRegion_Name\",
				Coalesce(to_char(Polis.Polis_begDate, 'dd.mm.yyyy'), '') as \"Polis_begDate\",
				Coalesce(to_char(Polis.Polis_endDate, 'dd.mm.yyyy'), '') as \"Polis_endDate\",
				Coalesce(RTRIM(PO.Org_Name), '') as \"OrgSmo_Name\",
				PJ.Org_id as \"JobOrg_id\",
				Coalesce(RTRIM(PJ.Org_Name), '') as \"Person_Job\",
				Coalesce(RTRIM(PP.Post_Name), '') as \"Person_Post\",
				'' as \"Ident_Lpu\",
				CASE WHEN PR.PersonRefuse_IsRefuse = 2
					THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
				CASE WHEN PS.Server_pid = 0 THEN 1 ELSE 0 END AS \"Person_IsBDZ\",
				CASE WHEN PersonPrivilegeFed.Person_id IS NOT NULL THEN 1 ELSE 0 END AS \"Person_IsFedLgot\",
				Coalesce(to_char(Person.Person_deadDT, 'dd.mm.yyyy'), '') as \"Person_deadDT\",
				Coalesce(to_char(Person.Person_closeDT, 'dd.mm.yyyy'), '') as \"Person_closeDT\",
				Person.Person_IsDead as \"Person_IsDead\",
				Person.PersonCloseCause_id as \"PersonCloseCause_id\",
				0 as \"Children_Count\"
				,PersonPrivilegeFed.PrivilegeType_id as \"PrivilegeType_id\"
				,PersonPrivilegeFed.PrivilegeType_Name as \"PrivilegeType_Name\"
				{$extendSelect}
			FROM {$object} PS
				left join Sex  on Sex.Sex_id = PS.Sex_id
				left join SocStatus  on SocStatus.SocStatus_id = PS.SocStatus_id
				left join Address UAddress  on UAddress.Address_id = PS.UAddress_id
				left join v_KLArea KLArea  on KLArea.KLArea_id = UAddress.KLTown_id
				left join Address PAddress  on PAddress.Address_id = PS.PAddress_id
				left join v_Job Job  on Job.Job_id = PS.Job_id
				left join Org PJ  on PJ.Org_id = Job.Org_id
				left join Post PP  on PP.Post_id = Job.Post_id
				left join Document  on Document.Document_id = PS.Document_id
				left join OrgDep  on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org DoOrg  on DoOrg.Org_id = OrgDep.Org_id
				left join Polis  on Polis.Polis_id = PS.Polis_id
				left join PolisType  on PolisType.PolisType_id = Polis.PolisType_id
				left join OmsSprTerr  on OmsSprTerr.OmsSprTerr_id = Polis.OmsSprTerr_id
				left join OrgSmo  on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org PO  on PO.Org_id = OrgSmo.Org_id
				left join Person  on Person.Person_id = PS.Person_id
				left join PersonState  on PS.Person_id = PersonState.Person_id
				LEFT JOIN LATERAL (
				select
                    vper.Person_edNum
				from v_Person_all vper
				where vper.Person_id = PS.Person_id
				and vper.PersonEvn_id = PS.PersonEvn_id
                limit 1
				) as vper on true
				LEFT JOIN LATERAL
				(SELECT
					PP.Person_id
					,PP.PrivilegeType_id
					,PT.PrivilegeType_Name
				FROM
					v_PersonPrivilege PP
					inner join v_PrivilegeType PT  on PT.PrivilegeType_id = PP.PrivilegeType_id
				WHERE
					PP.PrivilegeType_id <= 150 AND
					PP.PersonPrivilege_begDate <= dbo.tzGetDate() AND
					(PP.PersonPrivilege_endDate IS NULL OR
					PP.PersonPrivilege_endDate >= cast(dbo.tzGetDate() AS date)) AND
					PP.Person_id = PS.Person_id
                limit 1
				) as PersonPrivilegeFed on true
				LEFT JOIN LATERAL (select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate,
						pc.LpuRegion_Name
					from v_PersonCard pc
					where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
					order by PersonCard_begDate desc
                    limit 1
					) as pcard on true
				left join v_Lpu lpu  on lpu.Lpu_id=PersonState.Lpu_id
				LEFT JOIN v_PersonRefuse PR  ON PR.Person_id = ps.Person_id and PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = date_part('year',dbo.tzGetDate())
				{$extendFrom}
			WHERE {$filter}
            limit 1
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