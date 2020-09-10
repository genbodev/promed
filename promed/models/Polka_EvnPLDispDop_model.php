<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Polka_EvnPLDispDop_model - модель для работы с талонами по доп. диспансеризации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      24.06.2009
*/

class Polka_EvnPLDispDop_model extends CI_Model
{
	/**
	 * Конструктор
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnPLDispDop($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPLDispDop_del
				@EvnPLDispDop_id = :EvnPLDispDop_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnPLDispDop_id' => $data['EvnPLDispDop_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление талона ДД)');
		}
	}

	
	/**
	 * Получение талонов ДД для истории лечения человека
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив талонов ДД человека
	 */
	function loadEvnPLDispDopForPerson($data) {
		$query = "";
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id']
		);
		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$query .= "
				, case when ISNULL(EPLDD.EvnPLDispDop_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and ISNULL(EPLDD.EvnPLDispDop_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and ISNULL(EPLDD.EvnPLDispDop_IsArchive, 1) = 2";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		$query = "
			select
					[EPLDD].[EvnPLDispDop_id] as [EvnPLDispDop_id],
					[EPLDD].[Person_id] as [Person_id],
					[EPLDD].[Server_id] as [Server_id],
					[EPLDD].[PersonEvn_id] as [PersonEvn_id],
					[EPLDD].[EvnPLDispDop_VizitCount] as [EvnPLDispDop_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispDop_IsFinish],
					convert(varchar(10), [EPLDD].[EvnPLDispDop_setDate], 104) as [EvnPLDispDop_setDate],
					convert(varchar(10), [EPLDD].[EvnPLDispDop_disDate], 104) as [EvnPLDispDop_disDate]
					{$query}
			from
					v_PersonState PS with (nolock)
					inner join [v_EvnPLDispDop] [EPLDD] with (nolock) on [PS].[Person_id] = [EPLDD].[Person_id] and [EPLDD].Lpu_id = :Lpu_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispDop_IsFinish]
			where
				(1 = 1)
				and [EPLDD].Person_id = :Person_id
				{$filter}
			order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnPLDispDopEditForm($data)
	{
		$query = "
			SELECT TOP 1
				EPLDD.EvnPLDispDop_id,
				EPLDD.EvnPLDispDop_IsBud,
				EPLDD.EvnPLDispDop_PassportGive,
				EPLDD.EvnPLDispDop_IsFinish,
				convert(varchar(10), EPLDD.EvnPLDispDop_setDate, 104) as EvnPLDispDop_setDate,
				Okved_id as EvnPLDispDop_Okved_id,
				EPLDD.AttachType_id,
				EPLDD.Lpu_aid,
				EPLDD.PersonEvn_id,
				CASE WHEN ISNULL(EPLDD.EvnPLDispDop_IsNotMammograf, 1) = 2 THEN 1 ELSE 0 END as EvnPLDispDop_IsNotMammograf,
				CASE WHEN ISNULL(EPLDD.EvnPLDispDop_IsNotCito, 1) = 2 THEN 1 ELSE 0 END as EvnPLDispDop_IsNotCito
			FROM
				v_EvnPLDispDop EPLDD (nolock)
			WHERE
				(1 = 1)
				and EPLDD.EvnPLDispDop_id = ?
				and EPLDD.Lpu_id = ?
		";
        $result = $this->db->query($query, array($data['EvnPLDispDop_id'], $data['Lpu_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
 	    else
 	    {
            return false;
        }
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPLDispDopFields($data)
	{
		$query = "
			SELECT TOP 1
				rtrim(lp.Lpu_Name) as Lpu_Name,
				rtrim(isnull(lp1.Lpu_Name, '')) as Lpu_AName,
				rtrim(isnull(addr1.Address_Address, '')) as Lpu_AAddress,
				rtrim(lp.Lpu_OGRN) as Lpu_OGRN,
				isnull(pc.PersonCard_Code, '') as PersonCard_Code,
				ps.Person_SurName + ' ' + ps.Person_FirName + ' ' + isnull(ps.Person_SecName, '') as Person_FIO,
				sx.Sex_Name,
				isnull(osmo.OrgSMO_Nick, '') as OrgSMO_Nick,
				isnull(ps.Polis_Ser, '') as Polis_Ser,
				isnull(ps.Polis_Num, '') as Polis_Num,
				isnull(osmo.OrgSMO_Name, '') as OrgSMO_Name,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				isnull(addr.Address_Address, '') as Person_Address,
				jborg.Org_Nick,
				case when EPLDD.EvnPLDispDop_IsBud = 2 then 'Да' else 'Нет' end as EvnPLDispDop_IsBud,
				atype.AttachType_Name,
				convert(varchar(10),  EPLDD.EvnPLDispDop_disDate, 104) as EvnPLDispDop_disDate
			FROM
				v_EvnPLDispDop EPLDD (nolock)
				inner join v_Lpu lp (nolock) on lp.Lpu_id = EPLDD.Lpu_id
				left join v_Lpu lp1 (nolock) on lp1.Lpu_id = EPLDD.Lpu_aid
				left join Address addr1 (nolock) on addr1.Address_id = lp1.UAddress_id
				left join v_PersonCard pc (nolock) on pc.Person_id = EPLDD.Person_id and pc.LpuAttachType_id = 1
				inner join v_PersonState ps (nolock) on ps.Person_id = EPLDD.Person_id
				inner join Sex sx (nolock) on sx.Sex_id = ps.Sex_id
				left join Polis pls (nolock) on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo osmo (nolock) on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Address addr (nolock) on addr.Address_id = ps.PAddress_id
				left join Job jb (nolock) on jb.Job_id = ps.Job_id
				left join Org jborg (nolock) on jborg.Org_id = jb.Org_id
				left join AttachType atype (nolock) on atype.AttachType_id = EPLDD.AttachType_id
			WHERE
				(1 = 1)
				and EPLDD.EvnPLDispDop_id = ?
				and EPLDD.Lpu_id = ?
		";
        $result = $this->db->query($query, array($data['EvnPLDispDop_id'], $data['Lpu_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
 	    else
 	    {
            return false;
        }
	}

	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispDop_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispDopGrid($data)
	{
		
		$query = "
			select
				EVZDD.EvnVizitDispDop_id,
				convert(varchar(10), EVZDD.EvnVizitDispDop_setDate, 104) as EvnVizitDispDop_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(DDS.DopDispSpec_Name) as DopDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.DopDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispDop_IsSanKur,
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id,
				EVZDD.EvnVizitDispDop_Recommendations,
				1 as Record_Status
			from v_EvnVizitDispDop EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispDop_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}
	
	/**
	 * Получение данных для редактирования посещения врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnVizitDispDop_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispDopEditForm($data)
	{
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		
		$query = "
			select top 1
				EVZDD.EvnVizitDispDop_id,
				convert(varchar(10), EVZDD.EvnVizitDispDop_setDate, 104) as EvnVizitDispDop_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(DDS.DopDispSpec_Name) as DopDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.DopDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispDop_IsSanKur,
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id,
				EVZDD.EvnVizitDispDop_Recommendations,
				1 as RecordStatus,
				case when EVZDD.Lpu_id = :Lpu_id " . (count($med_personal_list)>0 ? "and EVZDD.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType
			from v_EvnVizitDispDop EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id and MP.Lpu_id = EVZDD.Lpu_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_id = :EvnVizitDispDop_id
		";
		$result = $this->db->query($query, array('EvnVizitDispDop_id' => $data['EvnVizitDispDop_id'], 'Lpu_id' => $data['Lpu_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}
	
	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispDop_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispDopData($data)
	{
		
		$query = "
			select
				EVZDD.EvnVizitDispDop_id,
				convert(varchar(10), EVZDD.EvnVizitDispDop_setDate, 104) as EvnVizitDispDop_setDate,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(isnull(MP.MedPersonal_TabCode, '')) as MedPersonal_TabCode,
				RTRIM(DDS.DopDispSpec_Name) as DopDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.DopDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispDop_IsSanKur,
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id,
				EVZDD.EvnVizitDispDop_Recommendations,
				1 as Record_Status
			from v_EvnVizitDispDop EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispDop_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDispDop_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispDopGrid($data)
	{
		
		$query = "
			select
				EUDD.EvnUslugaDispDop_id,
				convert(varchar(10), EUDD.EvnUslugaDispDop_setDate, 104) as EvnUslugaDispDop_setDate,
				convert(varchar(10), EUDD.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				EUDD.DopDispUslugaType_id,
				RTRIM(DDUT.DopDispUslugaType_Name) as DopDispUslugaType_Name,
				EUDD.LpuSection_uid as LpuSection_id,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				EUDD.MedPersonal_id,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				EUDD.Usluga_id as Usluga_id,
				RTRIM(U.Usluga_Name) as Usluga_Name,
				RTRIM(U.Usluga_Code) as Usluga_Code,
				EUDD.ExaminationPlace_id, 
				1 as Record_Status
			from v_EvnUslugaDispDop EUDD (nolock)
				left join DopDispUslugaType DDUT (nolock) on DDUT.DopDispUslugaType_id = EUDD.DopDispUslugaType_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = EUDD.LpuSection_uid
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EUDD.MedPersonal_id
				left join v_Usluga U (nolock) on U.Usluga_id = EUDD.Usluga_id
			where EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop_id
		";
		
		$result = $this->db->query($query, array('EvnPLDispDop_id' => $data['EvnPLDispDop_id']));
	
        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDispDop_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispDopData($data)
	{		
		$query = "
			select
				EUDD.EvnUslugaDispDop_id,
				convert(varchar(10), EUDD.EvnUslugaDispDop_setDate, 104) as EvnUslugaDispDop_setDate,
				convert(varchar(10), EUDD.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				EUDD.DopDispUslugaType_id
			from v_EvnUslugaDispDop EUDD (nolock)				
			where EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop_id
		";	
		
		$result = $this->db->query($query, array('EvnPLDispDop_id' => $data['EvnPLDispDop_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnPLDispDopStreamList($data)
	{
		$filter = '';
		$queryParams = array();

       	$filter .= " and [EPL].[pmUser_insID] = :pmUser_id ";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if ( preg_match('/^\d{2}:\d{2}:\d{2}$/', $data['begTime']) )
		{
        	$filter .= " and [EPL].[EvnPL_insDT] >= :date_time";
			$queryParams['date_time'] = $data['begDate'] . " " . $data['begTime'];
		}

        if ( isset($data['Lpu_id']) )
        {
        	$filter .= " and [EPL].[Lpu_id] = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
        }

        $query = "
        	SELECT DISTINCT TOP 100
				[EPL].[EvnPL_id] as [EvnPL_id],
				[EPL].[Person_id] as [Person_id],
				[EPL].[Server_id] as [Server_id],
				[EPL].[PersonEvn_id] as [PersonEvn_id],
				RTRIM([EPL].[EvnPL_NumCard]) as [EvnPL_NumCard],
				RTRIM([PS].[Person_Surname]) as [Person_Surname],
				RTRIM([PS].[Person_Firname]) as [Person_Firname],
				RTRIM([PS].[Person_Secname]) as [Person_Secname],
				convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
				convert(varchar(10), [EPL].[EvnPL_setDate], 104) as [EvnPL_setDate],
				convert(varchar(10), [EPL].[EvnPL_disDate], 104) as [EvnPL_disDate],
				[EPL].[EvnPL_VizitCount] as [EvnPL_VizitCount],
				[IsFinish].[YesNo_Name] as [EvnPL_IsFinish]
			FROM [v_EvnPL] [EPL] (nolock)
				inner join [v_PersonState] [PS] (nolock) on [PS].[Person_id] = [EPL].[Person_id]
				left join [YesNo] [IsFinish] (nolock) on [IsFinish].[YesNo_id] = [EPL].[EvnPL_IsFinish]
			WHERE (1 = 1)
				" . $filter . "
			ORDER BY [EPL].[EvnPL_id] desc
    	";
        $result = $this->db->query($query, $queryParams);

        if (is_object($result))
        {
            return $result->result('array');
        }
 	    else
 	    {
            return false;
        }
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnVizitPLDispDopGrid($data)
	{
		$query = "
			select
				EVPL.EvnVizitPL_id,
				EVPL.LpuSection_id,
				EVPL.MedPersonal_id,
				EVPL.MedPersonal_sid,
				EVPL.PayType_id,
				EVPL.ProfGoal_id,
				EVPL.ServiceType_id,
				EVPL.VizitType_id,
				EVPL.EvnVizitPL_Time,
				convert(varchar(10), EVPL.EvnVizitPL_setDate, 104) as EvnVizitPL_setDate,
				EVPL.EvnVizitPL_setTime,
				RTrim(LS.LpuSection_Name) as LpuSection_Name,
				RTrim(MP.Person_Fio) as MedPersonal_Fio,
				RTrim(PT.PayType_Name) as PayType_Name,
				RTrim(ST.ServiceType_Name) as ServiceType_Name,
				RTrim(VT.VizitType_Name) as VizitType_Name,
				1 as Record_Status
			from v_EvnVizitPL EVPL (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
				left join PayType PT (nolock) on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT (nolock) on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPL_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
            return false;
        }
	}

	/**
 	 * Проверка того, что человек есть в регистре по ДД и у него заведены все необходимые данные
	 */
	function checkPersonData($data)
	{
		$query = "
			select
				Sex_id,
				SocStatus_id,
				ps.UAddress_id as Person_UAddress_id,
				ps.Polis_Ser,
				ps.Polis_Num,
				o.Org_Name,
				o.Org_INN,
				o.Org_OGRN,
				o.UAddress_id as Org_UAddress_id,
				o.Okved_id,
				os.OrgSmo_Name,
				(datediff(year, PS.Person_Birthday, dbo.tzGetDate())
				+ case when month(ps.Person_Birthday) > month(dbo.tzGetDate())
				or (month(ps.Person_Birthday) = month(dbo.tzGetDate()) and day(ps.Person_Birthday) > day(dbo.tzGetDate()))
				then -1 else 0 end) as Person_Age,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
			from v_persondopdisp pdd (nolock)
			left join v_PersonState ps (nolock) on ps.Person_id=pdd.Person_id
			left join v_Job j (nolock) on j.Job_id=ps.Job_id
			left join v_Org o (nolock) on o.Org_id=j.Org_id
			left join v_Polis pol (nolock) on pol.Polis_id=ps.Polis_id
			left join v_OrgSmo os (nolock) on os.OrgSmo_id=pol.OrgSmo_id
			where pdd.Person_id = ?
		";

		$result = $this->db->query($query, array($data['Person_id']));
		$response = $result->result('array');
		
		if ( !is_array($response) || count($response) == 0 )
			return array(array('Error_Msg' => 'Этого человека нет в регистре по ДД!'));
		
		$error = Array();
		if (ArrayVal($response[0], 'Sex_id') == '')
			$errors[] = 'Не заполнен Пол';
		if (ArrayVal($response[0], 'SocStatus_id') == '')
			$errors[] = 'Не заполнен Соц. статус';
		if (ArrayVal($response[0], 'Person_UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес по месту регистрации';
		if (ArrayVal($response[0], 'Polis_Num') == '')
			$errors[] = 'Не заполнен Номер полиса';
		if (ArrayVal($response[0], 'Polis_Ser') == '')
			$errors[] = 'Не заполнена Серия полиса';
		if (ArrayVal($response[0], 'OrgSmo_id') == '')
			$errors[] = 'Не заполнена Организация, выдавшая полис';
		if (ArrayVal($response[0], 'Org_UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес места работы';
		if (ArrayVal($response[0], 'Org_INN') == '')
			$errors[] = 'Не заполнен ИНН места работы';
		if (ArrayVal($response[0], 'Org_OGRN') == '')
			$errors[] = 'Не заполнена ОГРН места работы';
		if (ArrayVal($response[0], 'Okved_id') == '')
			$errors[] = 'Не заполнен ОКВЭД места работы';
		
		If (count($error)>0) { // есть ошибки в заведении
			$errstr = implode("<br/>", $errors);
			return array(array('Error_Msg' => 'Проверьте полноту заведения данных у человека!<br/>'.$errstr));
		}
		return array( "Ok", ArrayVal($response[0], 'Sex_id'), ArrayVal($response[0], 'Person_Age'), ArrayVal($response[0], 'Person_Birthday') );
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function saveEvnPLDispDop($data)
    {		
		// Проверяем что человек находится в регистре по ДД и у него заведены все необходимые данные
		$checkResult = $this->checkPersonData($data);
		
		If ( $checkResult[0]!="Ok" ) {
			return $checkResult;
		}
		
		// поверяем, есть ли все обязательные осмотры и исследования, если проставляется законченность случая								
		$err_str = "";
		if ( isset($data['EvnPLDispDop_IsFinish']) && $data['EvnPLDispDop_IsFinish'] == 2 )
		{
			if ( $data['EvnVizitDispDop'] )
				$test_vizits = $data['EvnVizitDispDop'];
			else
				$test_vizits = array();
				
			if ( $data['EvnUslugaDispDop'] )
				$test_usluga = $data['EvnUslugaDispDop'];
			else
				$test_usluga = array();
			
			if ( isset($data['EvnPLDispDop_id']) )
			{
				$sel = $this -> loadEvnVizitDispDopGrid( $data );
				if ( count($sel) > 0 ) {
					foreach ( $sel as $record ) {
						$test_vizits[] = $record;
					}
				}
				$sel = $this -> loadEvnUslugaDispDopGrid($data);
				if ( count($sel) > 0 ) {
					foreach ( $sel as $record ) {
						$test_usluga[] = $record;
					}
				}
			}
			
			// осмотры
			// массив обязательных осмотров
			$vizits_array = array(
				'1' => 'Терапевт',			
				'3' => 'Невролог',
				'5' => 'Хирург',
				'6' => 'Офтальмолог'
			);

			$deleted_vizits = array();
			$ter_time = time();
			$pers_time = strtotime($checkResult[3]);
			foreach ( $test_vizits as $key => $record )
			{
				if ( $record['DopDispSpec_id'] == 1 )
				{
					$ter_time = strtotime($record['EvnVizitDispDop_setDate']);					
				}
				if ( $record['Record_Status'] == 3 )
						$deleted_vizits[] = $record['EvnVizitDispDop_id'];
			}
			
			if ( $checkResult[1] == 2  )
			{
				$vizits_array['2'] = 'Акушер-гинеколог';
			}
			
			if ( $test_vizits )
			{
				$deleted_vizits = array();
				foreach ( $test_vizits as $key => $record ) {
					if ( $record['Record_Status'] != 3 && isset($vizits_array[(string)$record['DopDispSpec_id']]) && !in_array($record['EvnVizitDispDop_id'], $deleted_vizits) )
					{
						unset($vizits_array[(string)$record['DopDispSpec_id']]);
					}
					if ( $record['Record_Status'] == 3 )
						$deleted_vizits[] = $record['EvnVizitDispDop_id'];
				}
			}
			
			if ( !(isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa') )
			{
				if ( count($vizits_array) > 0 )
				{

					$err_str = "<p>В талоне отсутствуют осмотры следующих специалистов:</p>";
					foreach ( $vizits_array as $value )				
					{
						$err_str .= "<p>".$value."</p>";
					}				
				}
			}
			
			// исследования
			// массив обязательных исследований
			$usluga_array = array(
				'3' => 'клинический анализ крови (02000101)',
				'13' => 'общий белок (02000401)',
				'1' => 'холестерин (02000456 и 02000410)',
				'9' => 'липопротеиды низкой плотности сыворотки крови (02003623)',
				'10' => 'триглицериды сыворотки крови (02003624)',
				'14' => 'креатинин (02000403)',
				'15' => 'мочевая кислота (02000406)',
				'16' => 'билирубин (02000435)',
				'17' => 'амилаза (02000423)',
				'2' => 'сахар крови (02000071 и 02000432)',
				'4' => 'клинический анализ мочи (02000130)',
				'7' => 'электрокардиография (02001101)',
				'6' => 'флюорография (02002301)'
			);
			//if ( $checkResult[2] >= 45 )
			if ( strtotime('+15 year', $pers_time) < strtotime('-30 year', $ter_time) )
			{
				// женщины
				if ( $checkResult[1] == 2 )
					$usluga_array['11'] = 'Онкомаркёр специфический CA-125 (02000592)';
				// мужчины
				else
					$usluga_array['12'] = 'Онкомаркёр специфический PSI (02000593)';
				
			}
			//if ( $checkResult[2] >= 40 && $checkResult[1] == 2 )
			if ( strtotime('+10 year', $pers_time) < strtotime('-30 year', $ter_time) && $checkResult[1] == 2 )
			{
				if ( !isset($data['EvnPLDispDop_IsNotMammograf']) || $data['EvnPLDispDop_IsNotMammograf'] != 'on' )
					$usluga_array['5'] = 'Обзорная маммография (02002230)';
			}
			
			if ( $checkResult[1] == 2 )
			{
				if ( !isset($data['EvnPLDispDop_IsNotCito']) || $data['EvnPLDispDop_IsNotCito'] != 'on' )
					$usluga_array['18'] = 'Диагностич. соскоб цервикального канала (02003316)';
			}
			
			if ( $test_usluga )
			{
				$deleted_usluga = array();
				foreach ( $test_usluga as $key => $record ) {
					if ( $record['Record_Status'] != 3 && isset($usluga_array[(string)$record['DopDispUslugaType_id']]) && !in_array($record['EvnUslugaDispDop_id'], $deleted_usluga) )
					{
						unset($usluga_array[(string)$record['DopDispUslugaType_id']]);
					}
					if ( $record['Record_Status'] == 3 )
						$deleted_usluga[] = $record['EvnUslugaDispDop_id'];
				}
			}
			if ( !(isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa') )
			{
				if ( count($usluga_array) > 0 )
				{
					$err_str .= "<p>&nbsp;</p><p>В талоне отсутствуют следующие исследования:</p>";
					foreach ( $usluga_array as $value )				
					{
						$err_str .= "<p>".$value."</p>";
					}
				}

				if ( $err_str != "" )
					return array(array('Error_Msg' => '<p>Случай не может быть закончен!</p><p>&nbsp;</p>' . $err_str));			 
			}
			 
		}						
		
    	$procedure = '';
    	if ( !isset($data['EvnPLDispDop_id']) )
    	{
	    	$data['EvnPLDispDop_setDT'] = date('Y-m-d');
			$data['EvnPLDispDop_disDT'] = null;
			$data['EvnPLDispDop_didDT'] = null;
			$data['EvnPLDispDop_VizitCount'] = 0;
			$procedure = 'p_EvnPLDispDop_ins';			
	    }
	    else
    	{
	    	// достаем дату начала, дату окончания, количество посещений
			$query = "
				select
					convert(varchar,cast(EvnPLDispDop_setDT as datetime),112) as EvnPLDispDop_setDT,
					convert(varchar,cast(EvnPLDispDop_disDT as datetime),112) as EvnPLDispDop_disDT,
					convert(varchar,cast(EvnPLDispDop_didDT as datetime),112) as EvnPLDispDop_didDT,					
					EvnPLDispDop_VizitCount
				from
					v_EvnPLDispDop (nolock)
				where EvnPLDispDop_id = ?
			";
			$result = $this->db->query($query, array($data['EvnPLDispDop_id']));
			$response = $result->result('array');
			$data['EvnPLDispDop_setDT'] = $response[0]['EvnPLDispDop_setDT'];
			$data['EvnPLDispDop_disDT'] = $response[0]['EvnPLDispDop_disDT'];
			$data['EvnPLDispDop_didDT'] = $response[0]['EvnPLDispDop_didDT'];
			$data['EvnPLDispDop_VizitCount'] = $response[0]['EvnPLDispDop_VizitCount'];
			$procedure = 'p_EvnPLDispDop_upd';
	    }
		
		if ( isset($data['EvnPLDispDop_IsNotMammograf']) && $data['EvnPLDispDop_IsNotMammograf'] == 'on' )
			$data['EvnPLDispDop_IsNotMammograf'] = 2;
		else
			$data['EvnPLDispDop_IsNotMammograf'] = 1;
		
		if ( isset($data['EvnPLDispDop_IsNotCito']) && $data['EvnPLDispDop_IsNotCito'] == 'on' )
			$data['EvnPLDispDop_IsNotCito'] = 2;
		else
			$data['EvnPLDispDop_IsNotCito'] = 1;
		
   		$query = "
		    declare
		        @Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;
			set @curdate = dbo.tzGetDate();
			set @Res = ?;
			exec " . $procedure . " "
				. "@EvnPLDispDop_id = @Res output, "
				. "@Lpu_id = ?, "
				. "@Server_id = ?, "
				. "@PersonEvn_id = ?, "
				. "@EvnPLDispDop_setDT = ?, "
				. "@EvnPLDispDop_disDT = ?, "
				. "@EvnPLDispDop_didDT = ?, "
				. "@EvnPLDispDop_VizitCount = ?, "
				. "@EvnPLDispDop_IsFinish = ?, "
				. "@AttachType_id = ?, "
				. "@Lpu_aid = ?, "
				. "@EvnPLDispDop_IsBud = ?, "
				. "@EvnPLDispDop_PassportGive = ?, "
				. "@Okved_id = ?, "
				. "@EvnPLDispDop_IsNotMammograf = ?, "
				. "@EvnPLDispDop_IsNotCito = ?, "
				. "@pmUser_id = ?, "
				. "@Error_Code = @ErrCode output, "
				. "@Error_Message = @ErrMessage output;
			select @Res as EvnPLDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, 
			array(
				$data['EvnPLDispDop_id'],
				$data['Lpu_id'],
				$data['Server_id'],
				$data['PersonEvn_id'],
				$data['EvnPLDispDop_setDT'],
				$data['EvnPLDispDop_disDT'],
				$data['EvnPLDispDop_didDT'],
				$data['EvnPLDispDop_VizitCount'],
				$data['EvnPLDispDop_IsFinish'],
				$data['AttachType_id'],
				$data['Lpu_aid'],
				$data['EvnPLDispDop_IsBud'],
				$data['EvnPLDispDop_PassportGive'],
				$data['EvnPLDispDop_Okved_id'],
				$data['EvnPLDispDop_IsNotMammograf'],
				$data['EvnPLDispDop_IsNotCito'],
				$data['pmUser_id']
			)
		);
		
        if (!is_object($result))
        {
            return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение талона по ДД)');
        }

		$response = $result->result('array');

        if (!is_array($response) || count($response) == 0)
        {
          	return false;
        }
        else if ($response[0]['Error_Msg'])
        {
        	return $response;
        }

		if ( !isset($data['EvnPLDispDop_id']) )
		{
			$data['EvnPLDispDop_id'] = $response[0]['EvnPLDispDop_id'];
		}
		
		// Осмотры врача-специалиста
		foreach ($data['EvnVizitDispDop'] as $key => $record) {
			if ( strlen($record['EvnVizitDispDop_id']) > 0 ) {
				if ( $record['Record_Status'] == 3 ) {// удаление посещений
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec p_EvnVizitDispDop_del "
							. "@EvnVizitDispDop_id = ?, "
							. "@pmUser_id = ?, "
							. "@Error_Code = @ErrCode output, "
							. "@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query($query, array($record['EvnVizitDispDop_id'], $data['pmUser_id']));

					if (!is_object($result))
					{
						return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление осмотра врача-специалиста)'));
					}

					$response = $result->result('array');

					if (!is_array($response) || count($response) == 0)
					{
						return array(0 => array('Error_Msg' => 'Ошибка при удалении осмотра врача-специалиста'));
					}
					else if (strlen($response[0]['Error_Msg']) > 0)
					{
						return $response;
					}
				}
				else {
					if ($record['Record_Status'] == 0)
					{
						$procedure = 'p_EvnVizitDispDop_ins';
					}
					else
					{
						$procedure = 'p_EvnVizitDispDop_upd';
					}
					// проверяем, есть ли уже такое посещение
					$query = "
						select 
							count(*) as cnt
						from
							v_EvnVizitDispDop (nolock)
						where
							EvnVizitDispDop_pid = ?
							and DopDispSpec_id = ?
							and ( EvnVizitDispDop_id <> isnull(?, 0) )
					";
					$result = $this->db->query(
						$query,
						array(
							$data['EvnPLDispDop_id'],
							$record['DopDispSpec_id'],
							$record['Record_Status'] == 0 ? null : $record['EvnVizitDispDop_id']
						)
					);
					if (!is_object($result))
					{
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
					}
					$response = $result->result('array');
					if (!is_array($response) || count($response) == 0)
					{
					 	return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
					}
					else if ($response[0]['cnt'] >= 1)
					{
						return array(array('Error_Msg' => 'Обнаружено дублирование осмотров, это недопустимо.'));
					}
					// окончание проверки
					
					$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @Res = ?;
						exec " . $procedure . " "
							. "@EvnVizitDispDop_id = @Res output, "
							. "@EvnVizitDispDop_pid = ?, "
							. "@Lpu_id = ?, "
							. "@Server_id = ?, "
							. "@PersonEvn_id = ?, "
							. "@EvnVizitDispDop_setDT = ?, "
							. "@EvnVizitDispDop_disDT = null, "
							. "@EvnVizitDispDop_didDT = null, "
							. "@LpuSection_id = ?, "
							. "@MedPersonal_id = ?, "
							. "@MedPersonal_sid = null, "
							. "@PayType_id = null, "
							. "@DopDispSpec_id = ?, "
							. "@Diag_id = ?, "
							. "@HealthKind_id = ?, "
							. "@DeseaseStage_id = ?, "
							. "@DopDispDiagType_id = ?, "
							. "@EvnVizitDispDop_IsSanKur = ?, "
							. "@EvnVizitDispDop_IsOut = ?, "
							. "@DopDispAlien_id = ?, "
							. "@EvnVizitDispDop_Recommendations = ?, "
							. "@pmUser_id = ?, "
							. "@Error_Code = @ErrCode output, "
							. "@Error_Message = @ErrMessage output;
						select @Res as EvnVizitDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result = $this->db->query($query, array(
						$record['Record_Status'] == 0 ? null : $record['EvnVizitDispDop_id'],
						$data['EvnPLDispDop_id'],
						$data['Lpu_id'],
						$data['Server_id'],
						$data['PersonEvn_id'],
						$record['EvnVizitDispDop_setDate'],
						$record['LpuSection_id'],
						(isset($record['MedPersonal_id']) && $record['MedPersonal_id'] > 0) ? $record['MedPersonal_id'] : null,
						$record['DopDispSpec_id'],
						$record['Diag_id'],
						$record['HealthKind_id'],
						(isset($record['DeseaseStage_id']) && $record['DeseaseStage_id'] > 0) ? $record['DeseaseStage_id'] : null,
						(isset($record['DopDispDiagType_id']) && $record['DopDispDiagType_id'] > 0) ? $record['DopDispDiagType_id'] : null,
						$record['EvnVizitDispDop_IsSanKur'],
						$record['EvnVizitDispDop_IsOut'],						
						$record['DopDispAlien_id'],
						$record['EvnVizitDispDop_Recommendations'],
						$data['pmUser_id']
					));

					if (!is_object($result))
					{
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
					}

					$response = $result->result('array');

					if (!is_array($response) || count($response) == 0)
					{
					  	return false;
					}
					else if ($response[0]['Error_Msg'])
					{
						return $response;
					}

					$record['EvnVizitDispDop_id'] = $response[0]['EvnVizitDispDop_id'];
				}
			}
		}
		
		// Лабораторные исследования
		$usluga_array = array();
		
		foreach ($data['EvnUslugaDispDop'] as $key => $record) {
			if ($record['Record_Status'] == 3) {// удаление исследований
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_EvnUslugaDispDop_del "
						. "@EvnUslugaDispDop_id = ?, "
						. "@pmUser_id = ?, "
						. "@Error_Code = @ErrCode output, "
						. "@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array($record['EvnUslugaDispDop_id'], $data['pmUser_id']));

				if (!is_object($result))
				{
					return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление лабораторного исследования)'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0)
				{
					return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
				}
				else if (strlen($response[0]['Error_Msg']) > 0)
				{
					return $response;
				}
			}
			else {
				if ($record['Record_Status'] == 0)
				{
					$procedure = 'p_EvnUslugaDispDop_ins';
				}
				else
				{
					$procedure = 'p_EvnUslugaDispDop_upd';
				}
				
				// проверяем, есть ли уже такое исследование
				$query = "
					select 
						count(*) as cnt
					from
						v_EvnUslugaDispDop (nolock)
					where
						EvnUslugaDispDop_pid = ?
						and DopDispUslugaType_id = ?
						and ( EvnUslugaDispDop_id <> isnull(?, 0) )
						and DopDispUslugaType_id <> 8
				";
				$result = $this->db->query(
					$query,
					array(
						$data['EvnPLDispDop_id'],
						$record['DopDispUslugaType_id'],
						$record['Record_Status'] == 0 ? null : $record['EvnUslugaDispDop_id']
					)
				);
				
				if (!is_object($result))
				{
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение исследования)'));
				}
				$response = $result->result('array');
				if (!is_array($response) || count($response) == 0)
				{
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение исследования)'));
				}
				else if ($response[0]['cnt'] >= 1)
				{
					return array(array('Error_Msg' => 'Обнаружено дублирование исследований, это недопустимо.'));
				}
				
				$pay_type = 7;
				// для Уфы PayType отдельно
				if ( isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa' )
					$pay_type = 14;
				
				// окончание проверки
				if ($record['LpuSection_id']=='')
					$record['LpuSection_id'] = Null;
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = ?;
					exec " . $procedure . " "
						. "@EvnUslugaDispDop_id = @Res output, "
						. "@EvnUslugaDispDop_pid = ?, "
						. "@Lpu_id = ?, "
						. "@Server_id = ?, "
						. "@PersonEvn_id = ?, "
						. "@EvnUslugaDispDop_setDT = ?, "
						. "@EvnUslugaDispDop_disDT = null, "
						. "@EvnUslugaDispDop_didDT = ?, "
						. "@LpuSection_uid = ?, "
						. "@MedPersonal_id = ?, "
						. "@DopDispUslugaType_id = ?, "
						. "@Usluga_id = ?, "
						. "@PayType_id = ?, "
						. "@UslugaPlace_id = 1, "
						. "@Lpu_uid = ?, "
						. "@EvnUslugaDispDop_Kolvo = 1, "
						. "@ExaminationPlace_id = ?, "
						. "@EvnPrescrTimetable_id = null, "
						. "@EvnPrescr_id = null, "
						. "@pmUser_id = ?, "
						. "@Error_Code = @ErrCode output, "
						. "@Error_Message = @ErrMessage output;
					select @Res as EvnUslugaDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				
				/*echo getDebugSql($query, array(
					$record['Record_Status'] == 0 ? null : $record['EvnUslugaDispDop_id'],
					$data['EvnPLDispDop_id'],
					$data['Lpu_id'],
					$data['Server_id'],
					$data['PersonEvn_id'],
					$record['EvnUslugaDispDop_setDate'],
					$record['EvnUslugaDispDop_didDate'],
					$record['LpuSection_id'],
					$record['MedPersonal_id'],
					$record['DopDispUslugaType_id'],
					$record['Usluga_id'],
					$data['Lpu_id'],
					$record['ExaminationPlace_id'],
					$data['pmUser_id']
				));
				exit;*/
				
				if ( isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa' && empty($record['Usluga_id']) )
					$record['Usluga_id'] = null;
				
				$result = $this->db->query($query, array(
					$record['Record_Status'] == 0 ? null : $record['EvnUslugaDispDop_id'],
					$data['EvnPLDispDop_id'],
					$data['Lpu_id'],
					$data['Server_id'],
					$data['PersonEvn_id'],
					$record['EvnUslugaDispDop_setDate'],
					$record['EvnUslugaDispDop_didDate'],
					$record['LpuSection_id'],
					(isset($record['MedPersonal_id']) && $record['MedPersonal_id'] > 0) ? $record['MedPersonal_id'] : null,
					$record['DopDispUslugaType_id'],
					$record['Usluga_id'],
					$pay_type,
					$data['Lpu_id'],					
					$record['ExaminationPlace_id'],
					$data['pmUser_id']
				));

				if (!is_object($result))
				{
					return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
				}
				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0)
				{
					return false;
				}
				else if ($response[0]['Error_Msg'])
				{
					return $response;
				}

				$record['EvnUslugaDispDop_id'] = $response[0]['EvnUslugaDispDop_id'];
				$usluga_array[] = array('id' => $record['EvnUslugaDispDop_id'], 'data' =>  $record['RateGrid_Data']);
			}
		}
		return array(0 => array('EvnPLDispDop_id' => $data['EvnPLDispDop_id'], 'usluga_array' => $usluga_array, 'Error_Msg' => ''));
    }
	
	
	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 */
	function getEvnPLDispDopYears($data)
    {
  		$sql = "
			SELECT
				count(Evn_id) as count,
				year(Evn_setDT) as EvnPLDispDop_Year
			FROM
				Evn with (nolock)
			WHERE
				Lpu_id = ?
				and EvnClass_id = 8
				and isnull(Evn.Evn_deleted, 1) = 1
			GROUP BY
				year(Evn_setDT)
			ORDER BY
				year(Evn_setDT)
		";

		$res = $this->db->query($sql, array($data['Lpu_id']));
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }
	
	/**
	 * Проверка, есть ли талон на этого человека в этом году
	 */
	function checkIfEvnPLDispDopExists($data)
    {
  		$sql = "
			SELECT
				count(EvnPLDispDop_id) as count
			FROM
				v_EvnPLDispDop (nolock)
			WHERE
				Person_id = ? and Lpu_id = ? and year(EvnPLDispDop_setDate) = year(dbo.tzGetDate())
		";

		$res = $this->db->query($sql, array($data['Person_id'], $data['Lpu_id']));
		if ( is_object($res) )
		{
 	    	$sel = $res->result('array');
			if ( $sel[0]['count'] == 0 )
				return array(array('isEvnPLDispDopExists' => false, 'Error_Msg' => ''));
			else
				return array(array('isEvnPLDispDopExists' => true, 'Error_Msg' => ''));
		}
 	    else
 	    	return false;
    }
	
	
	/**
	 * Данные человека по талону
	 */
	function getEvnPLDispDopPassportFields($data) {
		$dt = array();
		$person_id = 0;
		
  		$sql = "
			SELECT 
				dd.EvnPLDispDop_setDT,
				dd.Person_id,
				ps.Person_FirName,
				ps.Person_SecName,
				ps.Person_SurName,
				datepart(DD,ps.Person_BirthDay) as Person_BirthDay_Day,
				datepart(MM,ps.Person_BirthDay) as Person_BirthDay_Month,
				datepart(YYYY,ps.Person_BirthDay) as Person_BirthDay_Year,
				ua.Address_House,
				ua.Address_Corpus,
				ua.Address_Flat,
				ua.KLStreet_Name,
				(
						ua.KLRGN_Name+' '+ua.KLRGN_Socr
						+ISNULL(', '+ua.KLCity_Socr+' '+ua.KLCity_Name,'')
						+ISNULL(', '+ua.KLTown_Socr+' '+ua.KLTown_Name,'')
				) as Address_Info,
				l.Lpu_Name,
				l.Org_Phone
			FROM 
				v_EvnPLDispDop dd (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = dd.Person_id
				left join v_Address_all ua (nolock) on ua.Address_id = ps.UAddress_id
				left join v_Lpu_all l (nolock) on l.Lpu_id = dd.Lpu_id
			where
				EvnPLDispDop_id = :EvnPLDispDop_id
		";

		$res = $this->db->query($sql, array('EvnPLDispDop_id' => $data['EvnPLDispDop_id']));
		if (is_object($res)) {
 	    	$res = $res->result('array');
			$dt = array_merge($dt, $res[0]);
			if (isset($res[0]['Person_id']) && $res[0]['Person_id'] != '')
				$person_id = $res[0]['Person_id'];
		}
		
		$sql = "
			select
				RT.RateType_SysNick as nick,
				(
					CASE RVT.RateValueType_SysNick
						WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
						WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(10,2)) as varchar)
						WHEN 'string' THEN R.Rate_ValueStr
						WHEN 'template' THEN R.Rate_ValueStr
						WHEN 'reference' THEN RV.RateValue_Name
					END
				) as value,	
				CONVERT(varchar(10), EUDD.EvnUslugaDispDop_setDate, 104) as date
			from v_EvnUslugaDispDop EUDD (nolock)
				inner join EvnUslugaDispDop EUD (nolock) on EUD.EvnUslugaDispDop_id = EUDD.EvnUslugaDispDop_id	
				left join EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUD.EvnUsluga_id
				left join Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join RateType RT (nolock) on RT.RateType_id = R.RateType_id
				left join RateValueType RVT (nolock) on RVT.RateValueType_id = RT.RateValueType_id
				left join RateValue RV (nolock) on RV.RateType_id = RT.RateType_id and RV.RateValue_id = R.Rate_ValueInt and RVT.RateValueType_SysNick = 'reference'
			where
				EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop_id
				and RT.RateType_SysNick is not null
				ORDER BY RT.RateType_SysNick, EUDD.EvnUslugaDispDop_setDate DESC
		";
		
		$res = $this->db->query($sql, array('EvnPLDispDop_id' => $data['EvnPLDispDop_id']));
		$dt['usluga_rate'] = array();
		if (is_object($res)) {
 	    	$res = $res->result('array');
			$rate = array();
			foreach($res as $row) {
				$nick = $row['nick'];
				if(!isset($rate[$nick]))
					$rate[$nick] = array();
				if (count($rate[$nick]) < 4)
					array_unshift($rate[$nick], array('date'=>$row['date'], 'value'=>$row['value']));
			}			
			$dt['usluga_rate'] = $rate;
		}
		
		$sql="
			select	
				RT.RateType_SysNick as nick,
				(
					CASE RVT.RateValueType_SysNick
						WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
						WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(10,2)) as varchar)
						WHEN 'string' THEN R.Rate_ValueStr
						WHEN 'template' THEN R.Rate_ValueStr
						WHEN 'reference' THEN RV.RateValue_Name
					END
				) as value,	
				CONVERT(varchar(10), PM.PersonMeasure_setDT, 104) as date,
				datepart(year,PM.PersonMeasure_setDT) as year
			from v_PersonMeasure PM (nolock)
				inner join PersonRate PR (nolock) on PR.PersonMeasure_id = PM.PersonMeasure_id
				left join Rate R (nolock) on R.Rate_id = PR.Rate_id
				left join RateType RT (nolock) on RT.RateType_id = R.RateType_id
				left join RateValueType RVT (nolock) on RVT.RateValueType_id = RT.RateValueType_id
				left join RateValue RV (nolock) on RV.RateType_id = RT.RateType_id and RV.RateValue_id = R.Rate_ValueInt and RVT.RateValueType_SysNick = 'reference'		
			where
				PM.Person_id = :Person_id
				and RT.RateType_SysNick is not null
				ORDER BY RT.RateType_SysNick, year DESC, PM.PersonMeasure_setDT ASC
		";
		$res = $this->db->query($sql, array('Person_id' => $person_id));
		$dt['person_rate'] = array();
		if (is_object($res)) {
 	    	$res = $res->result('array');
			$rate = array();
			foreach($res as $row) {
				$nick = $row['nick'];
				if(!isset($rate[$nick]))
					$rate[$nick] = array();
				$rate[$nick][$row['year']] = $row['value'];
				$rate[$nick]['last_value'] = $row['value'];
			}			
			$dt['person_rate'] = $rate;
		}
		
		$sql = "
			select
				EPDD.EvnPLDispDop_IsFinish,
				HK.HealthKind_Name as value,
				CONVERT(varchar(10), EVDD.EvnVizitDispDop_setDate, 104) as date,
				datepart(year,EVDD.EvnVizitDispDop_setDate) as year
			from v_EvnPLDispDop EPDD (nolock)
				inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_pid = EPDD.EvnPLDispDop_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVDD.DopDispSpec_id
				left join HealthKind HK (nolock) on HK.HealthKind_id = EVDD.HealthKind_id	
			where
				EPDD.Person_id = :Person_id
				and DDS.DopDispSpec_Code = 1
				and EPDD.EvnPLDispDop_IsFinish = 2
				ORDER BY year, date desc 
		";
		$res = $this->db->query($sql, array('Person_id' => $person_id));
		$dt['health_groups'] = array();
		if (is_object($res)) {
 	    	$res = $res->result('array');
			$groups = array();
			foreach($res as $row) {
				$year = $row['year'];				
				$groups[$year] = array('date' => $row['date'], 'value' => $row['value']);				
			}			
			$dt['health_groups'] = $groups;
		}
		
		//recommendations
		$sql = "
			select
				DDS.DopDispSpec_Code as spec,
				EVDD.EvnVizitDispDop_Recommendations as value,
				CONVERT(varchar(10), EVDD.EvnVizitDispDop_setDate, 104) as date,
				datepart(year,EVDD.EvnVizitDispDop_setDate) as year
			from v_EvnPLDispDop EPDD (nolock)
				inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_pid = EPDD.EvnPLDispDop_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVDD.DopDispSpec_id
			where
				EPDD.Person_id = :Person_id
				ORDER BY spec, year, date desc 
		";
		$res = $this->db->query($sql, array('Person_id' => $person_id));
		$dt['recommendations'] = array();
		if (is_object($res)) {
 	    	$res = $res->result('array');
			$rec = array();
			foreach($res as $row) {				
				$rec[$row['spec']][$row['year']] = $row['value'];
			}			
			$dt['recommendations'] = $rec;
		}
		
		//diseases
		$sql = "
			select	
				D.Diag_Code as code,
				D.Diag_Name as name,
				CONVERT(varchar(10), EVDD.EvnVizitDispDop_setDate, 104) as date,
				datepart(year,EVDD.EvnVizitDispDop_setDate) as year
			from v_EvnPLDispDop EPDD (nolock)
				inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_pid = EPDD.EvnPLDispDop_id
				left join v_Diag D (nolock) on D.Diag_id = EVDD.Diag_id
			where
				EPDD.Person_id = :Person_id
				and SUBSTRING(D.Diag_Code, 1, 1) != 'Z'
				ORDER BY year, date desc
		";
		$res = $this->db->query($sql, array('Person_id' => $person_id));
		$dt['diseases'] = array();
		if (is_object($res)) {
 	    	$res = $res->result('array');
			$rec = array();
			foreach($res as $row) {				
				$rec[$row['year']][] = array(
					'date' => $row['date'],
					'name' => $row['name'],
					'code' => $row['code']
				);
			}			
			$dt['diseases'] = $rec;
		}
 	    
 	    return $dt;
    }
}
?>