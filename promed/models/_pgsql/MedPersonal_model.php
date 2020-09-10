<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2009 Swan Ltd.
 * @author				Petukhov Ivan aka Lich (megatherion@list.ru)
 *						Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 *						Bykov Stas aka Savage (savage@swan.perm.ru)
 * @link				http://swan.perm.ru/PromedWeb
 * @version				16.07.2009
 *
 * Класс модели для работы с медицинским персоналом
 *
 * @package		Common
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 *				Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 *
 * @property CI_DB_driver $db
 */
class MedPersonal_model extends SwPgModel {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Получение формы редактирования профиля сотрудника
	 */
	function loadMedStaffFactProfileEditForm($data) {
		$dopSpecSelect = '';
		$dopSpecJoin = '';
		if($data['session']['region']['nick'] == 'ekb'){// #137959 для Екатеринбурга выводить доп. специальность
			$dopSpecSelect = ',MSFMSO.MedSpecOms_id as "MedSpecOmsExt_id"';
			$dopSpecJoin = 'left join r66.MedStaffFactMedSpecOms MSFMSO on MSFMSO.MedStaffFact_id = msf.MedStaffFact_id';

		}

		$query = "
			select
				MSF.MedStaffFact_id as \"MedStaffFact_id\",
				MSF.Person_Fio as \"Person_Fio\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				MSO.MedSpecOms_Name as \"MedSpecOms_Name\",
				wp.LpuSectionProfile_id as \"LpuSectionProfile_id\"
				,MSO.MedSpecOms_id as \"mso_id\"
				{$dopSpecSelect}
			from
				v_MedStaffFact msf 
				inner join persis.WorkPlace wp  on wp.id = msf.MedStaffFact_id
				left join v_LpuSection LS  on LS.LpuSection_id = MSF.LpuSection_id
				left join v_MedSpecOms MSO  on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				{$dopSpecJoin}
			where
				msf.MedStaffFact_id = :MedStaffFact_id
		";

		return $this->queryResult($query, array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
		));
	}

	/**
	 * Загрузка профилей для формы редактирования профиля сотрудника
	 */
	function loadLpuSectionProfileForMedStaffFact($data) {
		$filter = "";

		$LpuSection_id = $this->getFirstResultFromQuery("
			select LpuSection_id  as \"LpuSection_id\" from v_MedStaffFact  where MedStaffFact_id = :MedStaffFact_id limit 1
		", array( 'MedStaffFact_id' => $data['MedStaffFact_id'] ));

		if ($this->regionNick == 'ekb') {
			$LpuSectionProfileMedSpecOms_id = $this->getFirstResultFromQuery("
				select 
					lspmso.LpuSectionProfileMedSpecOms_id as \"LpuSectionProfileMedSpecOms_id\"
				from
					r66.v_LpuSectionProfileMedSpecOms lspmso
					inner join v_MedStaffFact msf  on msf.MedSpecOms_id = lspmso.MedSpecOms_id
					inner join v_LpuUnit lu  on lu.LpuUnit_id = msf.LpuUnit_id and lu.LpuUnitType_id = lspmso.LpuUnitType_id
				where
					MedStaffFact_id = :MedStaffFact_id
				limit 1
			", array('MedStaffFact_id' => $data['MedStaffFact_id']));

		if (!empty($LpuSectionProfileMedSpecOms_id)) {
			$filter = "
				and exists(
					select 
						lspmso.LpuSectionProfileMedSpecOms_id
					from
						r66.v_LpuSectionProfileMedSpecOms lspmso 
						inner join v_MedStaffFact msf2  on msf2.MedSpecOms_id = lspmso.MedSpecOms_id
						inner join v_LpuUnit lu  on lu.LpuUnit_id = msf2.LpuUnit_id and lu.LpuUnitType_id = lspmso.LpuUnitType_id
					where
						msf2.MedStaffFact_id = :MedStaffFact_id
						and lspmso.LpuSectionProfile_id = LSP.LpuSectionProfile_id
					limit 1
				)
			";
			}
		}

		if (!empty($LpuSection_id)) {
			$query = "
				select
					1 as \"defaultValue\",
					LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				from
					v_MedStaffFact msf
					inner join v_LpuSection LS on LS.LpuSection_id = msf.LpuSection_id
					inner join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				where
					msf.MedStaffFact_id = :MedStaffFact_id
					{$filter}

				union

				select
					0 as \"defaultValue\",
					LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				from
					v_MedStaffFact msf
					inner join dbo.v_LpuSectionLpuSectionProfile LSLSP on LSLSP.LpuSection_id = msf.LpuSection_id
					inner join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LSLSP.LpuSectionProfile_id
				where
					msf.MedStaffFact_id = :MedStaffFact_id
					{$filter}
			";
		} else {
			$query = "
				select
					0 as \"defaultValue\",
					LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				from
					v_LpuSectionProfile LSP
				where
					1=1
					{$filter}
			";
		}

		return $this->queryResult($query, array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
		));
	}

    /**
     * Загрузка профилей для формы редактирования профиля сотрудника
     */
    function loadMedStaffFactDLOPeriodLinkEditForm(array $data):array {
        return $this->queryResult("
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msfdpl.MedPersonalDLOPeriod_id as \"MedPersonalDLOPeriod_id\",
				to_char (COALESCE(msfdpl.MedstaffFactDLOPeriodLink_begDate, dbo.tzGetDate()), 'DD.MM.YYYY') as \"MedstaffFactDLOPeriodLink_begDate\",
				to_char (msfdpl.MedstaffFactDLOPeriodLink_endDate, 'dd.mm.yyyy') as \"MedstaffFactDLOPeriodLink_endDate\"
			from
				v_MedStaffFact msf
				LEFT JOIN LATERAL (
					select 
						msfdpl.MedPersonalDLOPeriod_id,
						msfdpl.MedstaffFactDLOPeriodLink_begDate,
						msfdpl.MedstaffFactDLOPeriodLink_endDate
					from
						r50.v_MedStaffFactDLOPeriodLink msfdpl
					where
						msfdpl.MedStaffFact_id = msf.MedStaffFact_id
					order by
						msfdpl.MedStaffFactDLOPeriodLink_updDT desc
                    limit 1
				) msfdpl ON true
			where
				MedStaffFact_id = :MedStaffFact_id
            limit 1
		", [
            'MedStaffFact_id' => $data['MedStaffFact_id']
        ]);
    }

    /**
     * Загрузка справочника кодов ЛЛО
     */
    function loadMedPersonalDLOPeriod(array $data):array {
        return $this->queryResult("
			select
				mpdp.MedPersonalDLOPeriod_id as \"MedPersonalDLOPeriod_id\",
				mpdp.MedPersonalDLOPeriod_PCOD as \"MedPersonalDLOPeriod_PCOD\",
				mpdp.MedPersonalDLOPeriod_MCOD as \"MedPersonalDLOPeriod_MCOD\",
				COALESCE(mpdp.MedPersonalDLOPeriod_SurName, '') || COALESCE(' ' || mpdp.MedPersonalDLOPeriod_FirName, '') || COALESCE(' ' || mpdp.MedPersonalDLOPeriod_SecName, '') as \"MedPersonalDLOPeriod_Fio\",
				to_char (mpdp.MedPersonalDLOPeriod_begDate, 'dd.mm.yyyy') as \"MedPersonalDLOPeriod_begDate\",
				to_char (mpdp.MedPersonalDLOPeriod_endDate, 'dd.mm.yyyy') as \"MedPersonalDLOPeriod_endDate\"
			from
				v_MedStaffFact msf
				inner join v_Lpu l on l.Lpu_id = msf.Lpu_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				inner join r50.v_MedPersonalDLOPeriod mpdp  on
					mpdp.MedPersonalDLOPeriod_SurName = msf.Person_SurName 
					and mpdp.MedPersonalDLOPeriod_FirName = msf.Person_FirName 
					and mpdp.MedPersonalDLOPeriod_SecName = msf.Person_SecName
					and mpdp.MedPersonalDLOPeriod_OGRN = l.Lpu_OGRN
			where
				MedStaffFact_id = :MedStaffFact_id and 
                not exists(
					select
						MedStaffFactDLOPeriodLink_id
					from
						r50.v_MedStaffFactDLOPeriodLink 
					where
						MedPersonalDLOPeriod_id = mpdp.MedPersonalDLOPeriod_id
				)
			union
			(select 
				mpdp.MedPersonalDLOPeriod_id as \"MedPersonalDLOPeriod_id\",
				mpdp.MedPersonalDLOPeriod_PCOD as \"MedPersonalDLOPeriod_PCOD\",
				mpdp.MedPersonalDLOPeriod_MCOD as \"MedPersonalDLOPeriod_MCOD\",
				COALESCE(mpdp.MedPersonalDLOPeriod_SurName, '') || COALESCE(' ' || mpdp.MedPersonalDLOPeriod_FirName, '') || COALESCE(' ' || mpdp.MedPersonalDLOPeriod_SecName, '') as \"MedPersonalDLOPeriod_Fio\",
				to_char (mpdp.MedPersonalDLOPeriod_begDate, 'dd.mm.yyyy') as \"MedPersonalDLOPeriod_begDate\",
				to_char (mpdp.MedPersonalDLOPeriod_endDate, 'dd.mm.yyyy') as \"MedPersonalDLOPeriod_endDate\"
			from
				v_MedStaffFact msf 
				INNER JOIN LATERAL (
					select 
						msfdpl.MedPersonalDLOPeriod_id,
						msfdpl.MedstaffFactDLOPeriodLink_begDate,
						msfdpl.MedstaffFactDLOPeriodLink_endDate
					from
						r50.v_MedStaffFactDLOPeriodLink msfdpl 
					where
						msfdpl.MedStaffFact_id = msf.MedStaffFact_id
					order by
						msfdpl.MedStaffFactDLOPeriodLink_updDT desc
                    limit 1
				) msfdpl ON true
				inner join r50.v_MedPersonalDLOPeriod mpdp  on msfdpl.MedPersonalDLOPeriod_id = mpdp.MedPersonalDLOPeriod_id
			where
				MedStaffFact_id = :MedStaffFact_id
            limit 1)
		", [
            'MedStaffFact_id' => $data['MedStaffFact_id']
        ]);
    }

    /**
     * Сохранение связи рабочего места врача с внешним кодом ЛЛО
     */
    function saveMedStaffFactDLOPeriodLink(array $data):array {
        $queryParams = [
            'MedStaffFactDLOPeriodLink_id' => null,
            'MedPersonalDLOPeriod_id' => $data['MedPersonalDLOPeriod_id'],
            'MedStaffFact_id' => $data['MedStaffFact_id'],
            'MedstaffFactDLOPeriodLink_begDate' => $data['MedstaffFactDLOPeriodLink_begDate'],
            'MedstaffFactDLOPeriodLink_endDate' => $data['MedstaffFactDLOPeriodLink_endDate'],
            'pmUser_id' => $data['pmUser_id'],
        ];

        $resp_msfdpl = $this->queryResult("
			select
				MedStaffFactDLOPeriodLink_id as \"MedStaffFactDLOPeriodLink_id\"
			from
				r50.v_MedStaffFactDLOPeriodLink 
			where
				MedPersonalDLOPeriod_id = :MedPersonalDLOPeriod_id
				and MedStaffFact_id = :MedStaffFact_id
            limit 1
		", $queryParams);

        $proc = "p_MedStaffFactDLOPeriodLink_ins";
        if (!empty($resp_msfdpl[0]['MedStaffFactDLOPeriodLink_id'])) {
            $proc = "p_MedStaffFactDLOPeriodLink_upd";
            $queryParams['MedStaffFactDLOPeriodLink_id'] = $resp_msfdpl[0]['MedStaffFactDLOPeriodLink_id'];
        } else {
            // закрываем все связки предыдущей датой
            $this->db->query("
				update
					r50.MedStaffFactDLOPeriodLink
				set
					MedstaffFactDLOPeriodLink_endDate = :MedstaffFactDLOPeriodLink_endDate
				where
					COALESCE(MedstaffFactDLOPeriodLink_endDate, CAST(:MedstaffFactDLOPeriodLink_begDate as date)) >= CAST(:MedstaffFactDLOPeriodLink_begDate as date)
					and MedStaffFact_id = :MedStaffFact_id
			", [
                'MedStaffFact_id' => $data['MedStaffFact_id'],
                'MedstaffFactDLOPeriodLink_begDate' => $data['MedstaffFactDLOPeriodLink_begDate'],
                'MedstaffFactDLOPeriodLink_endDate' => date('Y-m-d', strtotime($data['MedstaffFactDLOPeriodLink_begDate']) - 24 * 60 * 60)
            ]);
        }

        return $this->queryResult("
			select MedStaffFactDLOPeriodLink_id as \"MedStaffFactDLOPeriodLink_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from r50.{$proc}(
				MedStaffFactDLOPeriodLink_id := :MedStaffFactDLOPeriodLink_id,
				MedPersonalDLOPeriod_id := :MedPersonalDLOPeriod_id,
				MedStaffFact_id := :MedStaffFact_id,
				MedstaffFactDLOPeriodLink_begDate := :MedstaffFactDLOPeriodLink_begDate,
				MedstaffFactDLOPeriodLink_endDate := :MedstaffFactDLOPeriodLink_endDate,
				pmUser_id := :pmUser_id);
		", $queryParams);
    }

	/**
	 * Сохранение профиля для формы редактирования профиля сотрудника
	 */
	function saveMedStaffFactProfileEditForm($data) {
		if($data['session']['region']['nick'] == 'ekb'){// #137959 обновить или вставить доп. место работы в форме Сотрудник: Выбор профиля
			$query = "SELECT MedStaffFactMedSpecOms_id as \"MedStaffFactMedSpecOms_id\" FROM r66.MedStaffFactMedSpecOms WHERE MedStaffFact_id = :MedStaffFact_id";
			$result = $this->db->query($query, array('MedStaffFact_id' => $data['MedStaffFact_id']));
			if(! is_object($result)) array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');

			$MedStaffFactMedSpecOms_id = 0;
			$res = $result->result('array');
			if(count($res) && count($res[0])){
				$MedStaffFactMedSpecOms_id = (int)$res[0]['MedStaffFactMedSpecOms_id'];
			}

			if($MedStaffFactMedSpecOms_id){// есть запись, значит, обновить или удалить
				if (isset($data['MedSpecOmsExt_id'])){
					$params = array();
					$params[] = $MedStaffFactMedSpecOms_id;
					$params[] = $data['MedStaffFact_id'];
					$params[] = $data['MedSpecOmsExt_id'];
					$params[] = $data['pmUser_id'];
					getSQLParams($params);

                    $sql = "
                    SELECT
                    error_code as \"Error_Code\",
                    error_message as \"Error_Msg\"
                    FROM
                    r66.p_MedStaffFactMedSpecOms_upd(
                        MedStaffFactMedSpecOms_id => ?,
						MedStaffFact_id => ?,
						MedSpecOms_id => ?,
						pmUser_id => ?
                    )
                    ";

					$result = $this->db->query($sql, $params);
					if(!is_object($result)) return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
					$res = $result->result('array');
					if(count($res)&& count($res[0]) && $res[0]['Error_Code']) return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
				}
				else{
					$sql = "
					SELECT
					error_code as \"Error_Code\",
                    error_message as \"Error_Msg\"
                    FROM
                    r66.p_MedStaffFactMedSpecOms_del(
                      MedStaffFactMedSpecOms_id => ?
                    )
					";

					$result = $this->db->query($sql, array($MedStaffFactMedSpecOms_id));
					if(!is_object($result)) return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
					$res = $result->result('array');
					if(count($res)&& count($res[0]) && $res[0]['Error_Code']) return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
				}
			}
			elseif(! empty($data['MedSpecOmsExt_id'])){// записи нет, добавить
				$params = array();
				$params[] = $data['MedStaffFact_id'];
				$params[] = $data['MedSpecOmsExt_id'];
				$params[] = $data['pmUser_id'];
				getSQLParams($params);

				$sql = "
				SELECT
				error_code as \"Error_Code\",
                error_message as \"Error_Msg\"
                FROM
                r66.p_MedStaffFactMedSpecOms_ins(                    
					MedStaffFact_id := ?,
					MedSpecOms_id := ?,
					pmUser_id := ?
                )
				";

				$result = $this->db->query($sql, $params);
				if(is_object($result))
					return $result->result('array');
				else
					return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		}

		$query = "
			update persis.WorkPlace set LpuSectionProfile_id = :LpuSectionProfile_id where id = :MedStaffFact_id
		";

		$this->db->query($query, array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id']
		));

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        persis.p_WorkPlace_upd(
          WorkPlace_id => :MedStaffFact_id
        )
        ";

		return $this->queryResult($query, array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
		));
	}

	/**
	 * Получение каких-то данных по врачу, где-то используется. Автор в курсе
	 */
	function getMedPersonInfo($data) 
	{
		if (!empty($data['MedStaffFact_id'])) {
			$params = array(
				$data['MedStaffFact_id']
			);
			$sql = "
				select 
					MSF.MedPersonal_id as \"MedPersonal_id\",
					MP.Dolgnost_Name as \"PostMed_Name\",
					MP.Person_Fio as \"MedPersonal_FIO\",
					LS.LpuSection_id as \"LpuSection_id\",
					LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					LS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
					MSF.LpuUnit_id as \"LpuUnit_id\"
				from
					v_MedStaffFact MSF 
					left join v_MedPersonal MP  on MSF.MedPersonal_id = MP.MedPersonal_id AND MSF.Lpu_id = MP.Lpu_id
					left join v_LpuSection LS  on LS.LpuSection_id = MSF.LpuSection_id
					--left join v_MedSpec MS  on MSF.MedSpec_id = MS.MedSpec_id

				where
					MSF.MedStaffFact_id = ?
					limit 1
			";
		} else {
			if (!empty($data['MedPersonal_id'])) {
				$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			}
			$params = array(
				'MedPersonal_id' => $data['MedPersonal_id'],
				'Lpu_id' => $data['Lpu_id']
			);
			$sql = "
				select 
					MP.Dolgnost_Name as \"PostMed_Name\",
					MP.Person_Fio as \"MedPersonal_FIO\"
				from
					v_MedPersonal MP 
				where
					MP.MedPersonal_id = :MedPersonal_id AND MP.Lpu_id = :Lpu_id
					limit 1
			";
		}
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение по идентификатору врача данных по его специальности по справочнику ОМС
	 */
	function getMedStaffFactMedSpecOmsInfo($MedStaffFact_id) 
	{
		
		$sql = "
			select 
				mso.MedSpecOms_Code as \"MedSpecOms_Code\"
			from 
				v_MedStaffFact msf 
				left join v_MedSpecOms mso  on msf.MedSpecOms_id = mso.MedSpecOms_id
			where
				MSF.MedStaffFact_id = :MedStaffFact_id
				limit 1
		";
		//echo getDebugSQL($sql, $data);
		$result = $this->db->query($sql, array('MedStaffFact_id' => $MedStaffFact_id));
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
	 * Получение данных по врачу для регистратуры
	 */
	function getMedPersonInfoForReg($data)
	{
		$params = array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
		);
		$sql = "
			select 
				msf.MedstaffFact_id as \"MedstaffFact_id\",
				MedPersonal_FIO as \"MedPersonal_FIO\",
				ls.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lr.LpuRegion_Name as \"LpuRegion_Name\",
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				MedStaffFact_IsQueueOnFree as \"MedStaffFact_IsQueueOnFree\",
				RecType_id as \"RecType_id\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				a.Address_Address as \"Address_Address\",
				l.Lpu_Nick as \"Lpu_Nick\",
				lu.LpuUnit_id as \"LpuUnit_id\",
				msf.Lpu_id as \"Lpu_id\",
				l.Org_id as \"Org_id\",
				rtrim(msf.MedStaffFact_Descr) as \"MedStaffFact_Descr\",
				msf.MedStaffFact_updDT as \"MedStaffFact_updDT\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				COALESCE(msf.MedStaffFact_IsDirRec, 1) as \"MedStaffFact_IsDirRec\",
				msf.MedPersonal_id as \"MedPersonal_id\"
			from v_MedstaffFact_ER msf
			left join v_MedStaffRegion msr on msr.MedPersonal_id = msf.MedPersonal_id
			left outer join v_LpuRegion lr on msr.LpuRegion_Id = lr.LpuRegion_Id
			left outer join v_LpuSection_ER ls on msf.LpuSection_Id = ls.LpuSection_Id
			left join v_LpuUnit_ER lu on lu.LpuUnit_id = msf.LpuUnit_id
			left outer join Address a on lu.Address_id = a.Address_id
			left join v_Lpu l on l.lpu_id = lu.lpu_id
			left join v_pmUser u on u.pmUser_id = msf.pmUser_updID
			where 
				msf.MedStaffFact_id = :MedStaffFact_id
				limit 1
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$res = $result->result('array');
			return $res[0];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Поиск медперсонала по ФИО и ДР
	 */
	function searchDoctorByFioBirthday($data) 
	{
		$params = array(
			$data['Person_BirthDay'],
			$data['Person_SurName'],
			$data['Person_FirName'],
			$data['Lpu_id']
		);
		if (empty($data['Person_SecName']))
		{
			$filter = 'and Person_SecName is null';
		}
		else
		{
			$params[] = $data['Person_SecName'];
			$filter = 'and Person_SecName = ?';
		}
		$sql = "
			select 
				MedPersonal_id as \"MedPersonal_id\"
			from
				MedPersonalCache 
			where
				to_char(Person_BirthDay, 'DD.MM.YYYY') = ?
				and Person_SurName = ?
				and Person_FirName = ?
				and Lpu_id = ?
				{$filter}
				limit 1
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$response = $result->result('array');
			if (count($response) > 0) 
			{
				$response[0]['found'] = true;
			}
			else
			{
				$response[0]['found'] = false;
			}
			return $response;
			
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загрузка данных формы редактирования медперсонала
	 */
	function loadMedPersonal($data)
	{
		$fromtable = "v_MedPersonal ";
		if ($data['session']['region']['nick'] == 'ufa') $fromtable = "v_MedPersonal_old ";
		$sql = "
			select
				MedPersonal_id as \"MedPersonal_id\",
				MedPersonal_Code as \"MedPersonal_Code\",
				MedPersonal_TabCode as \"MedPersonal_TabCode\",
				Person_SurName as \"Person_SurName\",
				Person_FirName as \"Person_FirName\",
				Person_SecName as \"Person_SecName\",
				to_char(cast(Person_BirthDay as timestamp), 'DD.MM.YYYY') as \"Person_BirthDay\",
				to_char(cast(WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"WorkData_begDate\",
				to_char(cast(WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"WorkData_endDate\",
				WorkData_IsDlo as \"WorkData_IsDlo\",
				Person_Snils as \"Person_Snils\"
			from
				--MedPersonalCache
				--v_MedPersonal
				".$fromtable."  
			where
				Lpu_id = ? and
				MedPersonal_id = ?
		";
        $result = $this->db->query($sql, array($data['Lpu_id'], $data['MedPersonal_id']));

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
	 * Сохранение медперсонала
	 */
	function saveMedPersonal($data) 
	{
		
		$set_mp_id = "";
		
		//проверка табельного кода на уникальность
		$query = "
			select
				count(MedPersonal_id) as \"cnt\"
			from
				MedPersonalCache
			where
				MedPersonal_TabCode = :MedPersonal_TabCode and
				Lpu_id = :Lpu_id and
				MedPersonal_id <> :MedPersonal_id
		";
		
		$result = $this->db->query($query, array(
			'MedPersonal_TabCode' => $data['MedPersonal_TabCode'],
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		));
		
		if (is_object($result)) {
			$res = $result->result('array');			
			if ($res[0]['cnt'] > 0) 
				return array(array('success' => false, 'Error_Msg' => 'Данный табельный номер уже используется')); 
		}
		
		if ( isset($data['action']) && $data['action'] == 'edit' )
		{
			$proc = 'p_MedPersonalCache_upd';
		}
		if ( isset($data['action']) && $data['action'] == 'add' )
		{
			$proc = 'p_MedPersonalCache_ins';
			$data['MedPersonal_id'] = NULL;
		}

        $query = "
        SELECT
        MedPersonal_id as \"MedPersonal_id\",
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        {$proc}(
                MedPersonal_id => ?,
				MedPersonal_Code => ?,
				MedPersonal_TabCode => ?,
				Person_SurName => ?,
				Person_FirName => ?,
				Person_SecName => ?,
				Person_BirthDay => ?,
				Lpu_id  => ?,
				WorkData_begDate => ?,
				WorkData_endDate => ?,
				WorkData_IsDlo => ?,
				Person_Snils => ?,
				pmUser_id => ?
        )
        ";

		$result = $this->db->query($query, array(
			$data['MedPersonal_id'],
			$data['MedPersonal_Code'],
			$data['MedPersonal_TabCode'],
			strtoupper($data['Person_SurName']),
			strtoupper($data['Person_FirName']),
			!empty($data['Person_SecName']) ? strtoupper($data['Person_SecName']) : '- - -',
			$data['Person_BirthDay'],
			$data['Lpu_id'],
			$data['WorkData_begDate'],
			$data['WorkData_endDate'],
			$data['WorkData_IsDlo'],
			$data['Person_Snils'],
			$data['pmUser_id']
		));
		
		
		if (is_object($result)) 
		{
			return $result->result('array');
		}
		else 
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}	
	
	/**
	 * Получение списка медперсонала
	 * Используется: окно поиска мед. персонала.
	 */
	public function loadMedPersonalSearchList($data) {
		$filter = " ( 1 = 1 ) ";
		$filter .= " and Lpu_id = :Lpu_id ";
		$queryParams = array();
		$queryParams['start'] = $data['start'];
		$queryParams['limit'] = $data['limit'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		
		if ( isset($data['Person_SurName']) )
		{
			$filter .= "
			and Person_SurName ilike :Person_SurName 
			";
			$queryParams['Person_SurName'] = $data['Person_SurName']."%";
		}
		
		if ( isset($data['Person_FirName']) )
		{
			$filter .= "
			and Person_FirName ilike :Person_FirName 
			";
			$queryParams['Person_FirName'] = $data['Person_FirName']."%";
		}
		
		if ( isset($data['Person_SecName']) )
		{
			$filter .= "
			and Person_SecName ilike :Person_SecName 
			";
			$queryParams['Person_SecName'] = $data['Person_SecName']."%";
		}
			
		$sql = "
			select
			-- select
				MedPersonal_id as \"MedPersonal_id\",
				MedPersonal_Code as \"MedPersonal_Code\",
				MedPersonal_TabCode as \"MedPersonal_TabCode\",
				Person_SurName as \"Person_SurName\",
				Person_FirName as \"Person_FirName\",
				Person_SecName as \"Person_SecName\",
				to_char(cast(Person_BirthDay as timestamp), 'DD.MM.YYYY') as \"Person_BirthDay\",
				to_char(cast(WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"WorkData_begDate\",
				to_char(cast(WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"WorkData_endDate\",
				case when COALESCE(WorkData_IsDlo, 1) = 1 then 'false' else 'true' end as \"WorkData_IsDlo\",
				Person_Snils as \"Person_Snils\"
			-- end select
			from
			-- from
				v_MedPersonal  

			-- end from
			where
			-- where
			" . $filter . "
			-- end where
			order by
			-- order by
				Person_SurName
			-- end order by
		";
		
		// замена функции преобразования запроса в запрос для получения количества записей
		$count_sql = getCountSQLPH($sql);
		if ( isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 )
		{
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
		}
		
		$result = $this->db->query($sql, $queryParams);
			
		if ( is_object($result) )
		{
			$res = $result->result('array');

			if ( is_array($res) )
			{
				if ( $data['start'] == 0 && count($res) < 100 )
				{
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				}
				else
				{
					$response['data'] = $res;

					$get_count_query = getCountSQLPH($count_sql);
					$get_count_result = $this->db->query($get_count_query, $queryParams);

					if ( is_object($get_count_result) )
					{
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					}
					else
					{
						return false;
					}
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
		
		return $response;
	 }

	/**
	 * Список врачей для Уфы из старого ЕРМП
	 */
	public function loadMedPersonalSearchList_Ufa_Old_ERMP($data) {
		$filter = " ( 1 = 1 ) ";
		$filter .= " and Lpu_id = :Lpu_id ";
		$queryParams = array();
		$queryParams['start'] = $data['start'];
		$queryParams['limit'] = $data['limit'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if ( isset($data['Person_SurName']) )
		{
			$filter .= "
			and Person_SurName ilike :Person_SurName
			";
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName'])."%";
		}

		if ( isset($data['Person_FirName']) )
		{
			$filter .= "
			and Person_FirName ilike :Person_FirName
			";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName'])."%";
		}

		if ( isset($data['Person_SecName']) )
		{
			$filter .= "
			and Person_SecName ilike :Person_SecName
			";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName'])."%";
		}

		$sql = "
			select
			-- select
				MedPersonal_id as \"MedPersonal_id\",
				MedPersonal_Code as \"MedPersonal_Code\",
				MedPersonal_TabCode as \"MedPersonal_TabCode\",
				Person_SurName as \"Person_SurName\",
				Person_FirName as \"Person_FirName\",
				Person_SecName as \"Person_SecName\",
				to_char(cast(Person_BirthDay as timestamp), 'DD.MM.YYYY') as \"Person_BirthDay\",
				to_char(cast(WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"WorkData_begDate\",
				to_char(cast(WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"WorkData_endDate\",
				case when COALESCE(WorkData_IsDlo, 1) = 1 then 'false' else 'true' end as \"WorkData_IsDlo\",
				Person_Snils as \"Person_Snils\"
			-- end select
			from
			-- from
				v_MedPersonal_old
			-- end from
			where
			-- where
			" . $filter . "
			-- end where
			order by
			-- order by
				Person_SurName
			-- end order by
		";

		// замена функции преобразования запроса в запрос для получения количества записей
		$count_sql = getCountSQLPH($sql);
		if ( isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 )
		{
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
		}

		$result = $this->db->query($sql, $queryParams);

		if ( is_object($result) )
		{
			$res = $result->result('array');

			if ( is_array($res) )
			{
				if ( $data['start'] == 0 && count($res) < 100 )
				{
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				}
				else
				{
					$response['data'] = $res;

					$get_count_query = getCountSQLPH($count_sql);
					$get_count_result = $this->db->query($get_count_query, $queryParams);

					if ( is_object($get_count_result) )
					{
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					}
					else
					{
						return false;
					}
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}

		return $response;
	}

	/**
	 * Получение списка мест работы врача
	 * Используется: окно просмотра и редактирования мед. персонала.
	 */
	public function getMedStaffFactEditWindow($data) {
		$sql = "
			SELECT
				Lpu_id as \"Lpu_idEdit\",
				LpuUnit_id as \"LpuUnit_idEdit\",
				LpuSection_id as \"LpuSection_idEdit\",
				Post_id as \"PostMed_idEdit\",
				MedStaffFact_Stavka as \"MedStaffFact_StavkaEdit\",
				--MedSpec_id as MedSpec_idEdit,
				PostKind_id as \"PostMedType_idEdit\",
				PostMedClass_id as \"PostMedClass_idEdit\",
				--PostMedCat_id as PostMedCat_idEdit,
				MedStaffFact_IsOMS as \"MedStaffFact_IsOMSEdit\",
				MedSpecOms_id as \"MedSpecOms_id\",
				MedStaffFact_IsSpecialist as \"MedStaffFact_IsSpecialistEdit\",
				to_char(cast(WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"MedStaffFact_setDateEdit\",
				to_char(cast(WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"MedStaffFact_disDateEdit\",
				MSF.RecType_id as \"RecType_id\",
				COALESCE(MSF.MedStaffFact_PriemTime, '') as \"MedStaffFact_PriemTime\",
				MSF.MedStatus_id as \"MedStatus_id\",
				CASE WHEN COALESCE(MSF.MedStaffFact_IsDirRec, 1) = 1 THEN '' ELSE 'true' END as \"MedStaffFact_IsDirRec\",
				CASE WHEN COALESCE(MSF.MedStaffFact_IsQueueOnFree, 1) = 1 THEN '' ELSE 'true' END as \"MedStaffFact_IsQueueOnFree\",
				COALESCE(MSF.MedStaffFact_Descr, '') as \"MedStaffFact_Descr\",
				COALESCE(MSF.MedStaffFact_Contacts, '') as \"MedStaffFact_Contacts\"
			from v_MedStaffFact MSF 

			where MedStaffFact_id = ? and Lpu_id = ?
		";
		if ($data['session']['region']['nick'] == 'ufa')
			$sql = "
			SELECT
				Lpu_id as \"Lpu_idEdit\",
				LpuUnit_id as \"LpuUnit_idEdit\",
				LpuSection_id as \"LpuSection_idEdit\",
				Post_id as \"PostMed_idEdit\",
				MedStaffFact_Stavka as \"MedStaffFact_StavkaEdit\",
				MedSpec_id as \"MedSpec_idEdit\",
				PostKind_id as \"PostMedType_idEdit\",
				PostMedClass_id as \"PostMedClass_idEdit\",
				PostMedCat_id as \"PostMedCat_idEdit\",
				MedStaffFact_IsOMS as \"MedStaffFact_IsOMSEdit\",
				MedSpecOms_id as \"MedSpecOms_id\",
				MedStaffFact_IsSpecialist as \"MedStaffFact_IsSpecialistEdit\",
				to_char(cast(WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"MedStaffFact_setDateEdit\",
				to_char(cast(WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"MedStaffFact_disDateEdit\",
				MSF.RecType_id as \"RecType_id\",
				COALESCE(MSF.MedStaffFact_PriemTime, '') as \"MedStaffFact_PriemTime\",
				MSF.MedStatus_id as \"MedStatus_id\",
				CASE WHEN COALESCE(MSF.MedStaffFact_IsDirRec, 1) = 1 THEN '' ELSE 'true' END as \"MedStaffFact_IsDirRec\",
				CASE WHEN COALESCE(MSF.MedStaffFact_IsQueueOnFree, 1) = 1 THEN '' ELSE 'true' END as \"MedStaffFact_IsQueueOnFree\",
				COALESCE(MSF.MedStaffFact_Descr, '') as \"MedStaffFact_Descr\",
				COALESCE(MSF.MedStaffFact_Contacts, '') as \"MedStaffFact_Contacts\"
			from v_MedStaffFact_old MSF 
			where MedStaffFact_id = ? and Lpu_id = ?
		";
		//end if

		$res = $this->db->query($sql, array($data['MedStaffFact_id'], $data['Lpu_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffFactEditWindow()


	/**
	 * Получение списка медицинского персонала для SwMedPersonalAllCombo
	 * Используется: комбобокс
	 * @param $data
	 * @return array|bool
	 */
	public function getMedPersonalCombo($data)
	{
		$queryParams = [];
		$where = "WHERE Lpu_id = :Lpu_id";
		if ((isset($data["Org_id"])) and ($data["Org_id"] > 0)) {
			$queryParams["Org_id"] = $data["Org_id"];
			$where = "WHERE Lpu_id = (select Lpu_id from v_Lpu where Org_id = :Org_id limit 1)";
		} elseif ((isset($data["Org_ids"])) and (!empty($data["Org_ids"]))) {
			$queryParams["Org_ids"] = json_decode($data["Org_ids"]);
			if (count($queryParams["Org_ids"]) > 1) {
				$where = "WHERE Lpu_id IN (select Lpu_id from v_Lpu  where Org_id IN (" . implode(",", $queryParams["Org_ids"]) . "))";
			} elseif (count($queryParams["Org_ids"]) == 1) {
				$queryParams["Org_id"] = $queryParams["Org_ids"][0];
				$where = "WHERE Lpu_id = (select Lpu_id from v_Lpu  where Org_id = :Org_id limit 1)";
			} else {
				$where = "WHERE (1=0) ";
			}
		} elseif ((isset($data["Lpu_id"])) and ($data["Lpu_id"] > 0)) {
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		} elseif (isset($data["session"]["lpu_id"])) {
			$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
		} else {
			$queryParams["Lpu_id"] = 0;
		}
		if ((isset($data["MedPersonal_id"])) and ($data["MedPersonal_id"] > 0)) {
			$queryParams["MedPersonal_id"] = $data["MedPersonal_id"];
		}
		$inner_filter = "";
		if (!empty($data["LpuSection_id"])) {
			$inner_filter = " and LpuSection_id = :LpuSection_id ";
			$queryParams["LpuSection_id"] = $data["LpuSection_id"];
		}
		$sql = "
			select distinct
				MP.MedPersonal_id as \"MedPersonal_id\",
				coalesce(MP.MedPersonal_TabCode, '') as \"MedPersonal_Code\",
				trim(MP.Person_SurName)||' '||trim(MP.Person_FirName)||' '||trim(coalesce(MP.Person_SecName, '')) as \"MedPersonal_FIO\",
				to_char(MSF.WorkData_begDate, 'DD.MM.YYYY') as \"WorkData_begDate\",
				to_char(MSF.WorkData_endDate, 'DD.MM.YYYY') as \"WorkData_endDate\"
			from
				v_MedPersonal MP 
				inner join lateral (
					select
						WorkData_begDate,
					    WorkData_endDate
					from v_MedStaffFact
					where MedPersonal_id = MP.MedPersonal_id
					  and Lpu_id = MP.Lpu_id
					  {$inner_filter}
					order by
						WorkData_endDate,
					  	WorkData_begDate
					limit 1
				) as MSF on true
			{$where}
			ORDER BY \"MedPersonal_FIO\", \"MedPersonal_Code\"
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * для SwMedPersonalIsOpenMOCombo
	 * Получение списка медицинского персонала в действующих МО на текущую дату. 
	 * Используется: комбобокс
	 */
	public function getMedPersonalIsOpenMOCombo($data) {		
		if(empty($data['Lpu_id'])) return false;

		$sql = "
			SELECT distinct
				MP.MedPersonal_id as \"MedPersonal_id\",
				COALESCE(MP.MedPersonal_TabCode, '') as \"MedPersonal_Code\",
				ltrim(rtrim(MP.Person_SurName)) || ' ' || ltrim(rtrim(MP.Person_FirName)) || ' ' || ltrim(rtrim(COALESCE(MP.Person_SecName,''))) as \"MedPersonal_FIO\",
				to_char(MSF.WorkData_begDate, 'DD.MM.YYYY') as \"WorkData_begDate\",
				to_char(MSF.WorkData_endDate, 'DD.MM.YYYY') as \"WorkData_endDate\"
			FROM v_MedPersonal MP 
				INNER JOIN LATERAL (
					select distinct MedPersonal_id, MedPersonal_Code, Lpu_id, WorkData_begDate, WorkData_endDate
					from v_MedStaffFact 
					where 1=1
						and (WorkData_endDate is null or (WorkData_endDate > dbo.tzGetDate() and WorkData_endDate > MP.WorkData_begDate)) 
						and WorkData_begDate < COALESCE(MP.WorkData_endDate, dbo.tzGetDate())
						and	Lpu_id = MP.Lpu_id 
						and MedPersonal_id = MP.MedPersonal_id
				) MSF on true				
			WHERE MP.Lpu_id = :Lpu_id
			ORDER BY \"MedPersonal_FIO\", \"MedPersonal_Code\"
		";
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Получение списка медицинского персонала (только участковых врачей)
	 * Используется: комбобокс
	 */
	public function getMedPersonalWithLpuRegionCombo($data) {
		
		$queryParams = array();
		// Фильтры
		$f = "";
		if ((isset($data['Lpu_id'])) and ($data['Lpu_id']>0)) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id'])) {
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}
		else {
			$queryParams['Lpu_id'] = 0;
		}
		if ((isset($data['MedPersonal_id'])) and ($data['MedPersonal_id']>0)) {
			$f .= " or (MedPersonal_id = :MedPersonal_id )";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ((isset($data['StomRequest'])) && $data['StomRequest'] == 1) {
			$f .= " and MP.Dolgnost_id in ('191','192','194','195') ";
			//$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		
		/*if ( isset($data['LpuRegion_id']) ) {
			$f = " and (MedStaffRegion.LpuRegion_id = :LpuRegion_id )";
			$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
		}*/

		$sql = "
			SELECT
				MedPersonal_id as \"MedPersonal_id\",
				COALESCE(MedPersonal_TabCode, '') as \"MedPersonal_Code\",
				rtrim(Person_SurName) || ' ' || rtrim(Person_FirName) || COALESCE(' ' || rtrim(Person_SecName), '') as \"MedPersonal_FIO\",
				to_char(cast(WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"WorkData_begDate\",
				to_char(cast(WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"WorkData_endDate\",
				MedStaffRegion.LpuRegion_id as \"LpuRegion_id\",
				MedStaffRegion.MedStaffRegion_isMain as \"MedStaffRegion_isMain\",
				MP.Dolgnost_id as \"Dolgnost_id\",
				(
					select string_agg(cast(LpuRegion_id as varchar), ',')
					from v_MedStaffRegion 
					where MedPersonal_id = MP.MedPersonal_Id
						and MedStaffRegion_begDate <= dbo.tzGetDate()
						and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > dbo.tzGetDate())
				) as \"LpuRegion_List\",
				(
					select string_agg(cast(LpuRegion_id as varchar), ',')
					from v_MedStaffRegion 
					where MedPersonal_id = MP.MedPersonal_Id
						and MedStaffRegion_begDate <= dbo.tzGetDate()
						and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > dbo.tzGetDate())
						and COALESCE(MedStaffRegion_isMain,1) = 2
				) as \"LpuRegion_MainList\"
			FROM v_MedPersonal MP 
				INNER JOIN LATERAL (
					select
						LpuRegion_id,
						MedStaffRegion_isMain
					from v_MedStaffRegion 
					where MedPersonal_id = MP.MedPersonal_Id
						and MedStaffRegion_begDate <= dbo.tzGetDate()
						and (MedStaffRegion_endDate is null or MedStaffRegion_endDate > dbo.tzGetDate())
						limit 1
				) as MedStaffRegion on true
			WHERE ( (WorkData_endDate is null or WorkData_endDate > dbo.tzGetDate()) {$f})
				and (Lpu_id = :Lpu_id)
			ORDER BY \"MedPersonal_FIO\", MedPersonal_TabCode
		";

		//echo getDebugSql($sql, $queryParams);exit;

		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedPersonalCombo()


	/**
	 * Получение списка врачей для ВМП
	 */
	public function getMedPersonalListWithPosts($data) {

		$params = array();
		$where = '';

		$surname = trim($data['query']);
		if(!empty($data['MedPersonal_id'])) {
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
			$where .= ' and MP.MedPersonal_id = :MedPersonal_id ';
		} else if(!empty($surname)) {
			$where .= " and Person_Surname ilike '{$surname}%'";
		}

		if(!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$where .= ' and MP.Lpu_id = :Lpu_id ';
		}

		$query = "			
			select
				MP.MedPersonal_id as \"MedPersonal_id\",
				MP.Person_Fio as \"Person_Fio\",
				MP.MedPersonal_TabCode as \"MedPersonal_TabCode\",
				MP.MedPersonal_Code as \"MedPersonal_Code\",
				Person_Fio || ' (' || MP.Dolgnost_Name || ')' as \"MedPersonal_full\",
				to_char(MP.WorkData_begDate, 'DD.MM.YYYY') as \"WorkData_begDate\",
				to_char(MP.WorkData_endDate, 'DD.MM.YYYY') as \"WorkData_endDate\",
				case when WorkData_endDate is null or MP.WorkData_endDate < dbo.tzGetDate() then 0 else 1 end as \"notWork\",
				MP.Dolgnost_id as \"Post_id\",
				MP.Dolgnost_Name as \"Post_Name\"
			from v_MedPersonal MP
			where (1=1) $where";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка медицинского персонала. Для гридов и комбобоксов
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function getMedPersonalGrid($data) {
		$fromtable = "v_MedPersonal ";
		if ($data['session']['region']['nick'] == 'ufa') $fromtable = "v_MedPersonal_old ";
		$sql = "
			SELECT
				MedPersonal_id as \"MedPersonal_id\",
				COALESCE(MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				COALESCE(MedPersonal_Code, '') as \"MedPersonal_Code\",
				rtrim(Person_SurName) || ' ' || rtrim(Person_FirName) || COALESCE(' ' || rtrim(Person_SecName), '') as \"MedPersonal_FIO\",
				CASE WHEN WorkData_IsDlo = 1 THEN 'false' ELSE 'true' END as \"MedPersonal_IsDlo\"
			FROM ".$fromtable."  
			WHERE Lpu_id = ?
			ORDER BY \"MedPersonal_FIO\", \"MedPersonal_TabCode\"
		";
		$res = $this->db->query($sql, array($data['session']['lpu_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedPersonalGrid()
	
	/**
	 * Получение списка медицинского персонала.
	 * Используется: списки мед персонала ЛПУ
	 */
	public function getMedPersonalList($data) {
		$params = array('Lpu_id' => $data['Lpu_id']);
		$dop_where = '';
		if ( isset($data['view_one_doctor']) )
		{
			$med_personal_id = !empty($data['session']['medpersonal_id']) ? $data['session']['medpersonal_id'] : null;
			$med_personal_id = !empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : $med_personal_id;
			if ($med_personal_id) {
				$dop_where .= ' AND MedPersonal_id = :MedPersonal_id';
				$params['MedPersonal_id'] = $med_personal_id;
			}
		}
		if (!empty($data['from']) && $data['from'] == 'PersonDisp') {
			$this->load->helper('Options');
			$this->setSessionParams($data['session']);
			$options = $this->globalOptions['globals'];
			if (!empty($options['allowed_disp_med_staff_fact_group']) && $options['allowed_disp_med_staff_fact_group'] == 2) {
				$dop_where .= ' and PostKind.code in (1,2)';
			}
		}
		if (!empty($data['onDate'])){
			$end_date = ":onDate";
			$params['onDate'] = $data['onDate'];
		} else {
			$end_date = "dbo.tzGetDate()";
		}

		$sql = "
			SELECT
				MedPersonal_id as \"MedPersonal_id\",
				COALESCE(MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				COALESCE(MedPersonal_Code, '') as \"MedPersonal_Code\",
				rtrim(Person_SurName) || ' ' || rtrim(Person_FirName) || COALESCE(' ' || rtrim(Person_SecName), '') as \"MedPersonal_FIO\",
				CASE WHEN WorkData_IsDlo = 1 THEN 'false' ELSE 'true' END as \"MedPersonal_IsDlo\"
			FROM
				v_MedPersonal MP 
				left join persis.v_Post Post  on Post.id = MP.Dolgnost_id
				left join persis.v_PostKind PostKind  on PostKind.id = Post.PostKind_id
			WHERE (WorkData_endDate is null or WorkData_endDate > {$end_date})
				and Lpu_id = :Lpu_id {$dop_where}
			ORDER BY \"MedPersonal_FIO\"
		";
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedPersonalList()

	/**
	 * Получение списка медицинского персонала.
	 * Используется: списки мед персонала ЛПУ
	 */
	public function getMedStaffFactPersonalList($data) {
		$params = array('Lpu_id' => $data['Lpu_id']);
		$dop_where = '';
		
		$this->load->helper('Options');
			$this->setSessionParams($data['session']);
			$options = $this->globalOptions['globals'];
			
		if ( isset($data['view_one_doctor']) )
		{
			$med_personal_id = !empty($data['session']['medpersonal_id']) ? $data['session']['medpersonal_id'] : null;
			$med_personal_id = !empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : $med_personal_id;
			if ($med_personal_id) {
				$dop_where .= ' AND MP.MedPersonal_id = :MedPersonal_id';
				$params['MedPersonal_id'] = $med_personal_id;
			}
		}
		if (!empty($data['from']) && $data['from'] == 'PersonDisp') {
			$this->load->helper('Options');
			$this->setSessionParams($data['session']);
			$options = $this->globalOptions['globals'];
			if (!empty($options['allowed_disp_med_staff_fact_group']) && $options['allowed_disp_med_staff_fact_group'] == 2) {
				$dop_where .= ' and PostKind.code in (1,2)';
			}
		}
		if (!empty($data['onDate'])){
			$end_date = ":onDate";
			$params['onDate'] = $data['onDate'];
		} else {
			$end_date = "dbo.tzGetDate()";
		}

		$sql = "
			SELECT distinct
				MP.MedPersonal_id as \"MedPersonal_id\",
				COALESCE(MP.MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				COALESCE(MP.MedPersonal_Code, '') as \"MedPersonal_Code\",
				rtrim(MP.Person_SurName) || ' ' || rtrim(MP.Person_FirName) || COALESCE(' ' || rtrim(MP.Person_SecName), '') as \"MedPersonal_FIO\",
				CASE WHEN MP.WorkData_IsDlo = 1 THEN 'false' ELSE 'true' END as \"MedPersonal_IsDlo\"
			FROM
				v_MedPersonal MP 
				left join v_MedStaffFact msf  on msf.MedPersonal_id = MP.MedPersonal_id
				left join persis.v_Post Post  on Post.id = msf.Post_id
				left join persis.v_PostKind PostKind  on PostKind.id = Post.PostKind_id
			WHERE (msf.WorkData_endDate is null or msf.WorkData_endDate > {$end_date})
				and MP.Lpu_id = :Lpu_id {$dop_where}
			ORDER BY \"MedPersonal_FIO\"
		";
		//echo getDebugSQL($sql, $params);die();
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffFactPersonalList()
	
	/**
	 * Получение детальной информации о местах работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function getMedPersonalGridDetail($data) {
		$fields = "";
		$filters = array();
		$join = array();
		$queryParams = array();
		if ($data['Lpu_id']>0) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filters[] = "msf.Lpu_id = :Lpu_id";
		}

		if (isset($data['LpuUnit_id']) and ($data['LpuUnit_id']>0)) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "msf.LpuUnit_id = :LpuUnit_id";
			
		}
		if (isset($data['LpuSection_id']) and ($data['LpuSection_id']>0)) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
		}
		
		if (isset($data['LpuBuilding_id']) and ($data['LpuBuilding_id']>0)) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filters[] = "lb.LpuBuilding_id = :LpuBuilding_id";
		}
		
		if (isset($data['MedPersonal_id']) and ($data['MedPersonal_id']>0)) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters[] = "msf.MedPersonal_id = :MedPersonal_id";
		}

		if (!empty($data['Search_Fio'])) {
			$queryParams['Search_Fio'] = rtrim($data['Search_Fio']);
			$filters[] = "msf.Person_Fio ILIKE ('%' || :Search_Fio || '%')";
		}

		if (!empty($data['Search_BirthDay'])) {
			$queryParams['Search_BirthDay'] = $data['Search_BirthDay'];
			$filters[] = "msf.Person_Birthday = :Search_BirthDay";
		}

		if (!empty($data['Person_Snils'])) {
			$queryParams['Person_Snils'] = rtrim($data['Person_Snils']);
			$join[] = "inner join v_MedPersonal mp  on mp.MedPersonal_id = msf.MedPersonal_id";

			$join[] = "inner join v_PersonState person  on person.Person_id = mp.Person_id";

			$filters[] = "person.Person_Snils = :Person_Snils";
		}

		if (!empty($data['PostMed_id'])) {
			$queryParams['PostMed_id'] = $data['PostMed_id'];
			$filters[] = "ps.PostMed_id = :PostMed_id";
		}

		if (!empty($data['LpuUnitType_id'])) {
			$queryParams['LpuUnitType_id'] = $data['LpuUnitType_id'];
			$filters[] = "lu.LpuUnitType_id = :LpuUnitType_id";
		}

		if (!empty($data['WorkType_id'])) {
			$queryParams['PostOccupationType_id'] = $data['WorkType_id'];
			$filters[] = "msf.PostOccupationType_id = :PostOccupationType_id";
		}

		if ($data['medStaffFactDateRange'] && !empty($data['MedStaffFact_date_range'][0]) && !empty($data['MedStaffFact_date_range'][1])) {
			$queryParams['MedStaffFact_date_begin'] = $data['MedStaffFact_date_range'][0];
			$queryParams['MedStaffFact_date_end'] = $data['MedStaffFact_date_range'][1];
			$filters[] = "msf.WorkData_begDate <= :MedStaffFact_date_end";
			$filters[] = "(msf.WorkData_endDate is null or msf.WorkData_endDate > :MedStaffFact_date_begin)";
		}

		if ($data['medStaffFactEndDateRange'] && !empty($data['MedStaffFact_disDate_range'][0]) && !empty($data['MedStaffFact_disDate_range'][1])) {
			$queryParams['MedStaffFact_disDate_begin'] = $data['MedStaffFact_disDate_range'][0];
			$queryParams['MedStaffFact_disDate_end'] = $data['MedStaffFact_disDate_range'][1];
			$filters[] = "msf.WorkData_endDate between :MedStaffFact_disDate_begin and :MedStaffFact_disDate_end";
		}

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filters[] = "(msf.WorkData_endDate is null or msf.WorkData_endDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filters[] = "msf.WorkData_endDate <= dbo.tzGetDate()";
		}

		if (getRegionNick() == 'kz') {
			$fields .= ",PW.ID as \"SURWorkPlace_id\"";
			$fields .= ",PW.PostFuncRU as \"SURWorkPlace_Name\"";
			$join[] = "left join r101.v_GetPersonalHistoryWP PHWP  on PHWP.WorkPlace_id = msf.MedStaffFact_id";

			$join[] = "left join r101.v_GetPersonalWork PW  on PW.GetPersonalHistory_id = PHWP.GetPersonalHistory_id";

		}
        if (getRegionNick() == 'msk') {
            $fields .= ",mpdp.MedPersonalDLOPeriod_PCOD as \"MedPersonalDLOPeriod_PCOD\"";
            $join[] = "
				LEFT JOIN LATERAL (
					select 
						mpdp.MedPersonalDLOPeriod_PCOD
					from
						r50.v_MedPersonalDLOPeriod mpdp 
						inner join r50.v_MedStaffFactDLOPeriodLink msfdpl on msfdpl.MedPersonalDLOPeriod_id = mpdp.MedPersonalDLOPeriod_id
					where
						msfdpl.MedStaffFact_id = msf.MedStaffFact_id
						and coalesce(msfdpl.MedstaffFactDLOPeriodLink_begDate, dbo.tzGetDate()) <= dbo.tzGetDate()
						and coalesce(msfdpl.MedstaffFactDLOPeriodLink_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
			        LIMIT 1
				) mpdp ON true
			";
        }

		if (getRegionNick() != 'kz') {
			$fields .= ",wpcp.WorkPlaceCovidPeriod_id as \"WorkPlaceCovidPeriod_id\"";
			$join[] = "
				LEFT JOIN LATERAL (
					select
						wpcp.WorkPlaceCovidPeriod_id
					from
						v_WorkPlaceCovidPeriod wpcp
					where
						wpcp.WorkPlace_id = msf.MedStaffFact_id
						and coalesce(wpcp.WorkPlaceCovidPeriod_begDate, dbo.tzGetDate()) <= cast(dbo.tzGetDate() as date)
						and coalesce(wpcp.WorkPlaceCovidPeriod_endDate, dbo.tzGetDate()) >= cast(dbo.tzGetDate() as date)
					limit 1
				) wpcp ON true
			";
		}

		$sql = "
			SELECT
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.Person_id as \"Person_id\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				COALESCE(msf.MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				rtrim(msf.Person_FIO) as \"MedPersonal_FIO\",
				rtrim(msf.Person_SurName) as \"PersonSurName_SurName\",
				rtrim(msf.Person_FirName) as \"PersonFirName_FirName\",
				rtrim(msf.Person_SecName) as \"PersonSecName_SecName\",
				to_char(msf.Person_BirthDay, 'DD.MM.YYYY') as \"PersonBirthDay_BirthDay\",
				p.Server_id as \"Server_id\",
				ps.PostMed_Name as \"PostMed_Name\",
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name, LpuUnit_Name, lb.LpuBuilding_Name, Lpu_Nick) as \"LpuSection_Name\",
				msf.MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(msf.WorkData_begDate, 'DD.MM.YYYY') as \"MedStaffFact_setDate\",
				to_char(msf.WorkData_endDate, 'DD.MM.YYYY') as \"MedStaffFact_disDate\",
				msf.ArriveOrderNumber as \"ArriveOrderNumber\"
				{$fields}
			FROM v_MedStaffFact msf 
				LEFT JOIN v_Lpu Lpu  on Lpu.Lpu_id=msf.Lpu_id
				LEFT JOIN v_LpuBuilding lb  on lb.LpuBuilding_id=msf.LpuBuilding_id
				LEFT JOIN v_LpuUnit lu  on lu.LpuUnit_id=msf.LpuUnit_id
				LEFT JOIN v_LpuSection ls  on ls.LpuSection_id=msf.LpuSection_id
				LEFT JOIN v_PostMed ps  on ps.PostMed_id=msf.Post_id
				LEFT JOIN v_PersonState p  on p.Person_id = msf.Person_id
				".implode(" ", $join)."
				".ImplodeWhere($filters);
			
			//echo getDebugSql($sql, $queryParams); exit;

		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedPersonalGridDetail()
	
	/**
	 * @ee
	 */
	public function getHistSpec($data){
		if(!isset($data["LpuSection_id"])||empty($data["LpuSection_id"])){
			return false;
		}
		$q = "SELECT
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				ps.PostMed_Name as \"PostMed_Name\",
				coalesce(LpuSection_Name,LpuUnit_Name, lb.LpuBuilding_Name, Lpu_Nick) as \"LpuSection_Name\",
				to_char(cast(msf.WorkData_begDate as timestamp), 'DD.MM.YYYY') || ' - ' || COALESCE(to_char(cast(msf.WorkData_endDate as timestamp), 'DD.MM.YYYY'),'...') as \"MedStaffFact_Interval\"
				
			FROM v_MedStaffFact msf 
				LEFT JOIN v_Lpu Lpu  on Lpu.Lpu_id=msf.Lpu_id
				LEFT JOIN v_LpuBuilding lb  on lb.LpuBuilding_id=msf.LpuBuilding_id
				LEFT JOIN v_LpuUnit lu  on lu.LpuUnit_id=msf.LpuUnit_id
				LEFT JOIN v_LpuSection ls  on ls.LpuSection_id=msf.LpuSection_id
				LEFT JOIN v_PostMed ps  on ps.PostMed_id=msf.Post_id

				WHERE  msf.LpuSection_id = ?";
		$res = $this->db->query($q, array($data["LpuSection_id"]));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * Получение постраничной информации о местах работы врача
	 * Используется: АРМ кадровика
	 */
	public function getMedPersonalGridPaged($data) {
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) 
		{
			return false;
		}
		
		$filters = array();
		$filters[] = "(1=1) ";
		$join = array();
		$queryParams = array();
		if ($data['Lpu_id']>0) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filters[] = "msf.Lpu_id = :Lpu_id";
		}

		if (isset($data['LpuUnit_id']) and ($data['LpuUnit_id']>0)) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "msf.LpuUnit_id = :LpuUnit_id";
			
		}
		if (isset($data['LpuSection_id']) and ($data['LpuSection_id']>0)) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
		}
		
		if (isset($data['LpuBuilding_id']) and ($data['LpuBuilding_id']>0)) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filters[] = "lu.LpuBuilding_id = :LpuBuilding_id";
		}
		
		if (isset($data['MedPersonal_id']) and ($data['MedPersonal_id']>0)) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters[] = "msf.MedPersonal_id = :MedPersonal_id";
		}

		if (isset($data['Search_BirthDay']) and !empty($data['Search_BirthDay'])) {
			$queryParams['Search_BirthDay'] = $data['Search_BirthDay'];
			$filters[] = "msf.Person_BirthDay = :Search_BirthDay";
		}
				
		if (isset($data['Search_FirName']) and !empty($data['Search_FirName'])) {
			$queryParams['Search_FirName'] = rtrim($data['Search_FirName']);
			$filters[] = "msf.Person_FirName ILIKE (:Search_FirName || '%')";
		}
				
		if (isset($data['Search_SecName']) and !empty($data['Search_SecName'])) {
			$queryParams['Search_SecName'] = rtrim($data['Search_SecName']);
			$filters[] = "msf.Person_SecName ILIKE (:Search_SecName || '%')";
		}
				
		if (isset($data['Search_SurName']) and !empty($data['Search_SurName'])) {
			$queryParams['Search_SurName'] = rtrim($data['Search_SurName']);
			$filters[] = "msf.Person_SurName ILIKE (:Search_SurName || '%')";
		}

		if (!empty($data['Search_Fio'])) {
			$queryParams['Search_Fio'] = rtrim($data['Search_Fio']);
			$filters[] = "msf.Person_Fio ILIKE ('%' || :Search_Fio || '%')";
		}

		if (!empty($data['Person_Snils'])) {
			$queryParams['Person_Snils'] = rtrim($data['Person_Snils']);
			$join[] = "inner join v_MedPersonal mp  on mp.MedPersonal_id = msf.MedPersonal_id";

			$join[] = "inner join v_PersonState person  on person.Person_id = mp.Person_id";

			$filters[] = "person.Person_Snils = :Person_Snils";
		}

		if (!empty($data['PostMed_id'])) {
			$queryParams['PostMed_id'] = $data['PostMed_id'];
			$filters[] = "ps.PostMed_id = :PostMed_id";
		}

		if (!empty($data['LpuUnitType_id'])) {
			$queryParams['LpuUnitType_id'] = $data['LpuUnitType_id'];
			$filters[] = "lu.LpuUnitType_id = :LpuUnitType_id";
		}

		if (!empty($data['WorkType_id'])) {
			$queryParams['PostOccupationType_id'] = $data['WorkType_id'];
			$filters[] = "msf.PostOccupationType_id = :PostOccupationType_id";
		}

		if ($data['medStaffFactDateRange'] && !empty($data['MedStaffFact_date_range'][0]) && !empty($data['MedStaffFact_date_range'][1])) {
			$queryParams['MedStaffFact_date_begin'] = $data['MedStaffFact_date_range'][0];
			$queryParams['MedStaffFact_date_end'] = $data['MedStaffFact_date_range'][1];
			$filters[] = "msf.WorkData_begDate <= :MedStaffFact_date_end";
			$filters[] = "(msf.WorkData_endDate is null or msf.WorkData_endDate > :MedStaffFact_date_begin)";
		}

		if ($data['medStaffFactEndDateRange'] && !empty($data['MedStaffFact_disDate_range'][0]) && !empty($data['MedStaffFact_disDate_range'][1])) {
			$queryParams['MedStaffFact_disDate_begin'] = $data['MedStaffFact_disDate_range'][0];
			$queryParams['MedStaffFact_disDate_end'] = $data['MedStaffFact_disDate_range'][1];
			$filters[] = "msf.WorkData_endDate between :MedStaffFact_disDate_begin and :MedStaffFact_disDate_end";
		}
        if (!empty($data['isClose']) && $data['isClose'] == 1) {
            $filters[] = "(msf.WorkData_endDate is null or msf.WorkData_endDate > dbo.tzGetDate())";
        } elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
            $filters[] = "msf.WorkData_endDate <= dbo.tzGetDate()";
        }
		$orderby = "";
		if ( !empty($data['sort']) && !empty($data['dir']) ) {
			switch ($data['sort']) {
				case 'MedPersonal_TabCode':
					$data['sort'] = "COALESCE(msf.MedPersonal_TabCode, '')";

				break;
				
				case 'MedPersonal_FIO':
					$data['sort'] = "rtrim(msf.Person_FIO)";
				break;
				
				case 'LpuSection_Name':
					$data['sort'] = "coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick)";
				break;
				
				case 'PostMed_Name':
					$data['sort'] = "ps.PostMed_Name";
				break;
				
				case 'MedStaffFact_Stavka':
					$data['sort'] = "MedStaffFact_Stavka";
				break;
				
				case 'MedStaffFact_setDate':
					$data['sort'] = "WorkData_begDate";
				break;
				
				case 'MedStaffFact_disDate':
					$data['sort'] = "WorkData_endDate";
				break;
				
				default:
					$data['sort'] = "ms.{$data['sort']}";
			}
			$orderby = "{$data['sort']} {$data['dir']},";
		}
		
		$sql = "
			SELECT
			-- select
				msf.MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				COALESCE(msf.MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				rtrim(msf.Person_FIO) as \"MedPersonal_FIO\",
				ps.PostMed_Name as \"PostMed_Name\",
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick) as \"LpuSection_Name\",
				MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(cast(msf.WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"MedStaffFact_setDate\",
				to_char(cast(msf.WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"MedStaffFact_disDate\"
			-- end select
			FROM
			-- from 
				v_MedStaffFact msf 
				LEFT JOIN v_Lpu Lpu  on Lpu.Lpu_id=msf.Lpu_id
				LEFT JOIN v_LpuUnit lu  on lu.LpuUnit_id=msf.LpuUnit_id
				LEFT JOIN v_LpuSection ls  on ls.LpuSection_id=msf.LpuSection_id
				LEFT JOIN v_PostMed ps  on ps.PostMed_id=msf.Post_id
				".implode(' ', $join)."
			-- end from
			where 
			-- where 
				".implode(' and ', $filters)."
			-- end where
			order by 
			-- order by 
				{$orderby} msf.MedStaffFact_id
			-- end order by";

		//echo getDebugSql($sql, $queryParams);exit;

		$res = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $queryParams);
		$res_count = $this->db->query(getCountSQLPH($sql), $queryParams);
		
		if (is_object($res_count))
		{
			$cnt_arr = $res_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}		
		
		if ( is_object($res) ) {
			$response = array();
			$response['data'] = $res->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	} //end getMedPersonalGridPaged()
	
	/**
	 * Получение постраничной информации о местах работы врача
	 * Используется: АРМ кадровика
	 */
	public function ufa_getMedPersonalGridPaged($data) {
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) 
		{
			return false;
		}
		
		$filters = array();
		$filters[] = "(1=1) ";
		$join = array();
		$queryParams = array();
		if ($data['Lpu_id']>0) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filters[] = "msf.Lpu_id = :Lpu_id";
		}
		
		if (isset($data['Search_FirName']) and !empty($data['Search_FirName'])) {
			$queryParams['Search_FirName'] = rtrim($data['Search_FirName']);
			$filters[] = "msf.Person_FirName ILIKE (:Search_FirName || '%')";
		}
				
		if (isset($data['Search_SecName']) and !empty($data['Search_SecName'])) {
			$queryParams['Search_SecName'] = rtrim($data['Search_SecName']);
			$filters[] = "msf.Person_SecName ILIKE (:Search_SecName || '%')";
		}
				
		if (isset($data['Search_SurName']) and !empty($data['Search_SurName'])) {
			$queryParams['Search_SurName'] = rtrim($data['Search_SurName']);
			$filters[] = "msf.Person_SurName ILIKE (:Search_SurName || '%')";
		}

		if (!empty($data['Search_Fio'])) {
			$queryParams['Search_Fio'] = rtrim($data['Search_Fio']);
			$filters[] = "msf.Person_Fio ILIKE ('%' || :Search_Fio || '%')";
		}

		if (!empty($data['PostMed_id'])) {
			$queryParams['PostMed_id'] = $data['PostMed_id'];
			$filters[] = "ps.PostMed_id = :PostMed_id";
		}
		
		if (!empty($data['TabCode'])) {
			$queryParams['TabCode'] = rtrim($data['TabCode']);
			$filters[] = "msf.MedPersonal_TabCode = :TabCode";
		}
		
		if (!empty($data['CodeDLO'])) {
			$queryParams['CodeDLO'] = rtrim($data['CodeDLO']);
			$filters[] = "msf.MedPersonal_Code = :CodeDLO";
		}
		
		if (!empty($data['WorkType_id'])) {
			$queryParams['PostOccupationType_id'] = $data['WorkType_id'];
			$filters[] = "msf.PostOccupationType_id = :PostOccupationType_id";
		} 
		
		if (!empty($data['RegistryDloON'])) {
			if ($data['RegistryDloON'] == 2) //  Врачи, имеющие право на выписку рецептов ЛЛО
			    $filters[] = "msf.WorkData_dlobegDate <= getDate() and (msf.WorkData_dloendDate >= getDate() or msf.WorkData_dloendDate is null) and msf.MedPersonal_Code is not null";
			if ($data['RegistryDloON'] == 1) //  Врачи, не имеющие право на выписку рецептов ЛЛО
			    $filters[] = "(msf.WorkData_dlobegDate is null or msf.WorkData_dlobegDate > getDate() or msf.WorkData_dloendDate < getDate())";
		}
		//var_dump($data['WorkPlace4DloApplyStatus_id']);
		if (isset($data['WorkPlace4DloApplyStatus_id'])) {
			$queryParams['WorkPlace4DloApplyStatus_id'] = rtrim($data['WorkPlace4DloApplyStatus_id']);
			$filters[] = "app.WorkPlace4DloApplyStatus_id = :WorkPlace4DloApplyStatus_id";
		}
		
		if (!empty($data['WorkPlace4DloApplyTYpe_id'])) {
			$queryParams['WorkPlace4DloApplyTYpe_id'] = rtrim($data['WorkPlace4DloApplyTYpe_id']);
			$filters[] = "app.WorkPlace4DloApplyTYpe_id = :WorkPlace4DloApplyTYpe_id";
		}
		
		
		

		
		$sql = "
			SELECT
			-- select
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				COALESCE(msf.MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				COALESCE(msf.MedPersonal_Code, '') as \"MedPersonal_Code\",
				rtrim(msf.Person_FIO) as \"MedPersonal_FIO\",
				ps.PostMed_Name as \"PostMed_Name\",
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick) as \"LpuSection_Name\",
				MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(cast(msf.WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"MedStaffFact_setDate\",
				to_char(cast(msf.WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"MedStaffFact_disDate\",
				to_char(cast(msf.WorkData_dlobegDate as timestamp), 'DD.MM.YYYY') as \"WorkData_dlobegDate\",
				to_char(cast(msf.WorkData_dloendDate as timestamp), 'DD.MM.YYYY') as \"WorkData_dloendDate\",
				--,WorkData_begDate, WorkData_endDate--, MedPersonal_IsDlo WorkData_IsDlo
				COALESCE((Select 1 where exists(Select 1 from  v_MedStaffFact t  
					where COALESCE(t.MedPersonal_TabCode, '') = COALESCE(msf.MedPersonal_TabCode, '') 
						and t.MedPersonal_id <> msf.MedPersonal_id and COALESCE(t.lpu_id, 0) = COALESCE(msf.lpu_id, 0) )), 0) 
				ctrl_tabCode as \"ctrl_tabCode\",
				COALESCE(app.WorkPlace4DloApplyTYpe_id, 0) as \"WorkPlace4DloApplyTYpe_id\",
				case
					when app.WorkPlace4DloApplyTYpe_id = 1
						then 'На включение в регистр'
					when app.WorkPlace4DloApplyTYpe_id = 2
						then 'На исключение из регистра'
					else
						''
				end as \"WorkPlace4DloApplyTYpe_Name\"
				, case
					when msf.WorkData_endDate is not null
					    then 0
					when app.WorkPlace4DloApplyTYpe_id = 1
						then 21
					when app.WorkPlace4DloApplyTYpe_id = 2
						then 22
					when (msf.WorkData_dlobegDate <= getDate() and (msf.WorkData_dloendDate >= getDate() or msf.WorkData_dloendDate is null) and msf.MedPersonal_Code is not null)
						then 10
					when (msf.WorkData_dlobegDate is null or msf.WorkData_dlobegDate > getDate() or msf.WorkData_dloendDate < getDate())
						then 30
					else null
				end as \"recStatus_id\",
				app.WorkPlace4DloApply_id as \"WorkPlace4DloApply_id\",
				msf.lpu_id as \"lpu_id\", 
				Lpu.lpu_nick as \"lpu_nick\",
				lpu.lpu_ogrn as \"lpu_ogrn\"
			-- end select
			FROM
			-- from 
				v_MedStaffFact msf 
				--left join  dbo.MedPersonalCache Cache  on Cache.Person_id = msf.Person_id
				left join  dbo.WorkPlace4DloApply app  on app.MedStaffFact_id = msf.MedStaffFact_id
					and app.WorkPlace4DloApplyStatus_id = 0
				INNER JOIN v_Lpu Lpu  on Lpu.Lpu_id=msf.Lpu_id
				INNER JOIN v_LpuPeriodDLO  LpuDlo  on LpuDlo.Lpu_id=msf.Lpu_id 
					and LpuDlo.LpuPeriodDLO_begDate <= GetDate() and COALESCE(LpuDlo.LpuPeriodDLO_endDate, getDate()) >= GetDate()				
				INNER JOIN v_LpuUnit lu  on lu.LpuUnit_id=msf.LpuUnit_id  and  LpuUnitType_SysNick in('polka', 'fap')
				INNER JOIN v_LpuSection ls  on ls.LpuSection_id=msf.LpuSection_id  and COALESCE(LpuSectionProfile_SysNick, '') <> 'priem'
				LEFT JOIN v_PostMed ps  on ps.PostMed_id=msf.Post_id
				".implode(' ', $join)."
			-- end from
			where 
			-- where 
				".implode(' and ', $filters). " 
				    and LpuSection_Name <> 'Фиктивные ставки'
				    -- берем уволенных за последнии три года
				    and COALESCE(msf.WorkData_endDate, getDate()) >= DATEADD('year', -3, GetDate ())

			-- end where
			order by 
			-- order by 
				--MedPersonal_FIO
				Person_FIO
			-- end order by";

		//echo getDebugSql($sql, $queryParams);exit;
		
		//$dbrep = $this->load->database('bdwork', true);
 
		$dbrep = $this->db;
		
		$res = $dbrep->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $queryParams);
		$res_count = $dbrep->query(getCountSQLPH($sql), $queryParams);
		
		
		if (is_object($res_count))
		{
			$cnt_arr = $res_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}		
		
		if ( is_object($res) ) {
			$response = array();
			$response['data'] = $res->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	} //end ufa_getMedPersonalGridPaged()
	
	/**
	 * Сохранение заявки на изменения в регистре врачей ЛЛО
	 */
	function saveWorkPlace4DloApply($data)
	{
	    $queryParams = array();
	    // WorkPlace4DloApply_id
	    if (isset($data['WorkPlace4DloApply_id'])) { // Редактируем запись
			$sql = "			
			update dbo.WorkPlace4DloApply
			    set WorkPlace4DloApplyStatus_id = :WorkPlace4DloApplyStatus_id,
				pmUser_updID = :pmUser_id,
				WorkPlace4DloApply_updDT = GetDate()
			where WorkPlace4DloApply_id = :WorkPlace4DloApply_id;
		";
			$queryParams['WorkPlace4DloApply_id'] = $data['WorkPlace4DloApply_id'];
	    }
	    else if ($data['WorkPlace4DloApplyStatus_id'] == 10) {  //  Если надо аннулировать, а ИД нет
			$sql = "			
			update dbo.WorkPlace4DloApply
			    set WorkPlace4DloApplyStatus_id = :WorkPlace4DloApplyStatus_id,
				pmUser_updID = :pmUser_id,
				WorkPlace4DloApply_updDT = GetDate()
			where MedStaffFact_id = :MedStaffFact_id
			    and WorkPlace4DloApplyStatus_id = 0;
		";
	    }
	    else {
	   
			$sql = "			
			insert into dbo.WorkPlace4DloApply
            (
            MedStaffFact_id
            ,WorkPlace4DloApplyTYpe_id
            ,WorkPlace4DloApplyStatus_id
            ,pmUser_insID
            ,pmUser_updID
            ,WorkPlace4DloApply_insDT
            ,WorkPlace4DloApply_updDT
            )
            values (
            :MedStaffFact_id,
            :WorkPlace4DloApplyTYpe_id,
            :WorkPlace4DloApplyStatus_id,
            :pmUser_id,
            :pmUser_id, 
            GetDate(),
            GetDate()	
            ) 
            returning WorkPlace4DloApply_id as \"WorkPlace4DloApply_id\"
			";
	    }
  
	    $queryParams['WorkPlace4DloApplyTYpe_id'] = $data['WorkPlace4DloApplyTYpe_id'];
	    $queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
	    $queryParams['WorkPlace4DloApplyStatus_id'] = $data['WorkPlace4DloApplyStatus_id'];
	    
	    $queryParams['pmUser_id']  = $_SESSION['pmuser_id'];
		
		$res = $this->db->query($sql, $queryParams);
		//var_dump($res);
		
		
		return array(
			0 => array( 'Error_Msg' => '')
		);
	} //end saveWorkPlace4DloApply()

	/**
	 * Получение информации о враче
	 */
	function getMedPersonalInfo($data) {
		$query = "
			SELECT 
				P.Person_Fio as \"Person_Fio\",
				LPU.Org_Nick as \"Org_Nick\"
			FROM 
				v_MedStaffFact MSF 
				inner join v_Person_all P  on P.Person_id = MSF.Person_id
				inner join v_Lpu_all LPU   on LPU.Lpu_id = MSF.Lpu_id
			where 
				MSF.MedStaffFact_id = :MedStaffFact_id
			order by P.PersonEvn_updDT desc
			limit 1
		";
		$queryParams = array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
		);

		return $this->queryResult($query, $queryParams);
	}
	
	
	/**
	 * Получение детальной информации о местах работы врача
	 * Используется: окно просмотра и редактирования мест работы мед. персонала
	 */
	public function getMedStaffGridDetail($data) {
		$filters = array();
		$queryParams = array();
		if ($data['session']['lpu_id']>0) {
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$filters[] = "msf.Lpu_id = :Lpu_id";
		}

		if (isset($data['LpuUnit_id']) and ($data['LpuUnit_id']>0)) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "msf.LpuUnit_id = :LpuUnit_id";
			
		}
		if (isset($data['LpuSection_id']) and ($data['LpuSection_id']>0)) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
		}
		
		if (isset($data['LpuBuilding_id']) and ($data['LpuBuilding_id']>0)) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filters[] = "lu.LpuBuilding_id = :LpuBuilding_id";
		}
		
		if (isset($data['MedPersonal_id']) and ($data['MedPersonal_id']>0)) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters[] = "msf.MedPersonal_id = :MedPersonal_id";
		}

		$sql = "
			SELECT
				msf.MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				COALESCE(msf.MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				rtrim(msf.Person_FIO) as \"MedPersonal_FIO\",
				ps.PostMed_Name as \"PostMed_Name\",
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick) as \"LpuSection_Name\",
				MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(cast(WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"setDate\",
				to_char(cast(WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"disDate\"
			FROM v_MedStaffFact msf 
			LEFT JOIN v_Lpu Lpu  on Lpu.Lpu_id=msf.Lpu_id
			LEFT JOIN v_LpuUnit lu  on lu.LpuUnit_id=msf.LpuUnit_id
			LEFT JOIN v_LpuSection ls  on ls.LpuSection_id=msf.LpuSection_id
			LEFT JOIN v_PostMed ps  on ps.PostMed_id=msf.Post_id
			--LEFT JOIN persis.post ps  on ps.id=msf.Post_id
			".ImplodeWhere($filters);
			/*
			echo getDebugSql($sql, $queryParams);
			exit;
			*/
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffGridDetail()

	/**
	 * Список мест работы для Уфы из старого ЕРМП
	 */
	public function getMedStaffGridDetail_Ufa_Old_ERMP($data) {
		$filters = array();
		$queryParams = array();
		if ($data['session']['lpu_id']>0) {
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$filters[] = "msf.Lpu_id = :Lpu_id";
		}

		if (isset($data['LpuUnit_id']) and ($data['LpuUnit_id']>0)) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "msf.LpuUnit_id = :LpuUnit_id";

		}
		if (isset($data['LpuSection_id']) and ($data['LpuSection_id']>0)) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
		}

		if (isset($data['LpuBuilding_id']) and ($data['LpuBuilding_id']>0)) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filters[] = "lu.LpuBuilding_id = :LpuBuilding_id";
		}

		if (isset($data['MedPersonal_id']) and ($data['MedPersonal_id']>0)) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters[] = "msf.MedPersonal_id = :MedPersonal_id";
		}

		$sql = "
			SELECT
				msf.MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				COALESCE(msf.MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				rtrim(msf.Person_FIO) as \"MedPersonal_FIO\",
				ps.PostMed_Name as \"PostMed_Name\",
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick) as \"LpuSection_Name\",
				MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(cast(WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"setDate\",
				to_char(cast(WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"disDate\"
			FROM v_MedStaffFact_old msf 
			LEFT JOIN v_Lpu Lpu  on Lpu.Lpu_id=msf.Lpu_id
			LEFT JOIN v_LpuUnit lu  on lu.LpuUnit_id=msf.LpuUnit_id
			LEFT JOIN v_LpuSection ls  on ls.LpuSection_id=msf.LpuSection_id
			LEFT JOIN v_PostMed ps  on ps.PostMed_id=msf.Post_id
			--LEFT JOIN PostMed ps  on ps.PostMed_id=msf.Post_id
			--LEFT JOIN persis.post ps  on ps.id=msf.Post_id
			".ImplodeWhere($filters);
		/*
		   echo getDebugSql($sql, $queryParams);
		   exit;
		   */
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffGridDetail()

	/**
	 * Получение детальной информации о строках штатного расписания
	 * Используется: окно просмотра и редактирования строк шатного расписания
	 */
	public function getStaffTTGridDetail($data) {
		$filters = array();
		$queryParams = array();
		
		if ($data['Lpu_id']>0) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filters[] = "st.Lpu_id = :Lpu_id";
		} 

		if (isset($data['LpuUnit_id']) and ($data['LpuUnit_id']>0)) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "st.LpuUnit_id = :LpuUnit_id";
			
		}
		if (isset($data['LpuSection_id']) and ($data['LpuSection_id']>0)) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "st.LpuSection_id = :LpuSection_id";
		}
		
		if (isset($data['LpuBuilding_id']) and ($data['LpuBuilding_id']>0)) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filters[] = "st.LpuBuilding_id = :LpuBuilding_id";
		}

		if (!empty($data['PostMed_id'])) {
			$queryParams['PostMed_id'] = $data['PostMed_id'];
			$filters[] = "st.Post_id = :PostMed_id";
		}

		if (!empty($data['MedicalCareKind_id'])) {
			$queryParams['MedicalCareKind_id'] = $data['MedicalCareKind_id'];
			$filters[] = "st.MedicalCareKind_id = :MedicalCareKind_id";
		}

		if ($data['medStaffFactDateRange'] && !empty($data['Staff_Date_range'][0]) && !empty($data['Staff_Date_range'][1])) {
			$queryParams['Staff_Date_begin'] = $data['Staff_Date_range'][0];
			$queryParams['Staff_Date_end'] = $data['Staff_Date_range'][1];
			$filters[] = "st.BeginDate between :Staff_Date_begin and :Staff_Date_end";
			$filters[] = "(st.EndDate is null or st.EndDate > :Staff_Date_begin)";
		}

		if ($data['medStaffFactEndDateRange'] && !empty($data['Staff_endDate_range'][0]) && !empty($data['Staff_endDate_range'][1])) {
			$queryParams['Staff_endDate_begin'] = $data['Staff_endDate_range'][0];
			$queryParams['Staff_endDate_end'] = $data['Staff_endDate_range'][1];
			$filters[] = "st.endDate between :Staff_endDate_begin and :Staff_endDate_end";
		}

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filters[] = "(st.endDate is null or st.endDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filters[] = "st.endDate <= dbo.tzGetDate()";
		}

		$sql = "
			select
				-- идентификатор строки штатки
				st.id as \"Staff_id\",
				-- Структурный элемент ЛПУ
				CASE
					WHEN ls.LpuSection_id IS NOT NULL THEN rtrim(ls.LpuSection_Name)
					WHEN lu.LpuUnit_id IS NOT NULL THEN rtrim(lu.LpuUnit_Name)
					WHEN lb.LpuBuilding_id IS NOT NULL THEN rtrim(lb.LpuBuilding_Name)
					WHEN l.Lpu_id IS NOT NULL THEN rtrim(l.Lpu_Nick)
				END as \"StructElement_Name\",
				-- Должность
				rtrim(pst.name) as \"Post_Name\",
				-- Вид Мп
				mck.name as \"MedicalCareKind_Name\",
				-- Дата создания	
				to_char(cast(st.BeginDate as timestamp), 'DD.MM.YYYY') as \"BeginDate\",
				-- Комментарий
				rtrim(st.Comments) as \"Staff_Comment\",
				-- Количество ставок
				st.Rate as \"Staff_Rate\",
				-- Из них занято
				COALESCE(RateTotal.RateSum, 0) as \"Staff_RateSum\",
				-- Количество сотрудников
				RateTotal.RateCount as \"Staff_RateCount\",
				st.Lpu_id as \"Lpu_id\",
				st.LpuBuilding_id as \"LpuBuilding_id\",
				st.LpuUnit_id as \"LpuUnit_id\",
				st.LpuSection_id as \"LpuSection_id\"
			from
				persis.v_Staff st 
				left join persis.Post as pst  on pst.id = st.Post_id
				left join v_Lpu as l  on l.Lpu_id = st.Lpu_id
				left join v_LpuBuilding as lb  on lb.LpuBuilding_id = st.LpuBuilding_id
				left join v_LpuUnit as lu  on lu.LpuUnit_id = st.LpuUnit_id
				left join v_LpuSection as ls  on ls.LpuSection_id = st.LpuSection_id
				left join persis.MedicalCareKind as mck  on mck.id = st.MedicalCareKind_id
				LEFT JOIN LATERAL (
					select
					    sum(case when sp.id is null and mmw.id is null then wp.Rate else null end) as RateSum,
						COUNT(wp.id) as RateCount
					from
					    persis.WorkPlace wp 
						LEFT JOIN LATERAL (

							select sp.id
							from persis.SkipPayment sp 

								inner join persis.SkipPaymentReason spr on spr.id = sp.SkipPaymentReason_id
							where sp.WorkPlace_id = wp.id
								and dbo.tzGetDate() between sp.StartDate and sp.EndDate
								and spr.code in (1,2,3)
								limit 1
						) sp ON true
						LEFT JOIN LATERAL (
							select mmw.id
							from persis.MoveMedWorker mmw 
							where mmw.WorkPlace_id = wp.id
								and mmw.StartDate <= dbo.tzGetDate()
								and (mmw.EndDate is null or mmw.EndDate >= dbo.tzGetDate())
								limit 1
						) mmw ON true
					where
					    wp.Staff_id = st.id
					    and (wp.EndDate is null or wp.EndDate >= dbo.tzGetDate())
						and wp.IsDummyWP != 1 --фиктивные ставки не учитываем
				) as RateTotal on true
			".ImplodeWhere($filters);
		//echo getDebugSql($sql, $queryParams);exit;
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getStaffTTGridDetail()

	/**
	 * Удаление места работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function dropMedStaffFact($data) {
		$sql = "
		SELECT
		error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_MedStaffFact_del(
          MedStaffFact_id => ?
        )
		";
		$res = $this->db->query($sql, array($data['MedStaffFact_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end dropMedStaffFact()
	
	/**
	 * Проверка привязан ли врач к отделению
	 */
	public function checkIfLpuSectionExists($data)
	{
		if ( !isset($data['LpuSection_idEdit']) || !isset($data['Lpu_id']) || !isset($data['MedPersonal_idEdit']) )
			return false;
			
		$params = array($data['Lpu_id'], $data['LpuSection_idEdit'], $data['MedPersonal_idEdit']);

		$filter_for_edit_action = "";
		if ( isset($data['MedStaffFact_idEdit']) && $data['MedStaffFact_idEdit'] > 0 )
		{
			$filter_for_edit_action = " and MedStaffFact_id <> ? ";
			$params[] = $data['MedStaffFact_idEdit'];
		}
			
		$sql = "
			SELECT 
				count(*) as \"cnt\"
			FROM
				MedStaffFact 

			WHERE
				Lpu_id = ?
				and LpuSection_id = ?
				and MedPersonal_id = ?
				" . $filter_for_edit_action . "
		";
		$res = $this->db->query($sql, $params );
		if ( is_object($res) )
		{
			$result = $res->result('array');
			if ( $result[0]['cnt'] > 0 )
				return true;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Добавление новой записи о месте работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function insertMedStaffFact($data) {
		$params = array();
		$params[] = $data['Lpu_id'];
		$params[] = $data['Server_id'];
		$params[] = $data['LpuSection_idEdit'];
		$params[] = $data['MedPersonal_idEdit'];
		$params[] = $data['MedStaffFact_StavkaEdit'];
		$params[] = $data['MedStaffFact_IsSpecialistEdit'];
		$params[] = $data['MedStaffFact_IsOMSEdit'];
		$params[] = $data['MedSpecOms_id'];
		$params[] = $data['MedStaffFact_setDateEdit'];
		$params[] = $data['MedStaffFact_disDateEdit'];
		$params[] = $data['MedSpec_idEdit'];
		$params[] = $data['PostMed_idEdit'];
		$params[] = $data['PostMedClass_idEdit'];
		$params[] = $data['PostMedType_idEdit'];
		$params[] = $data['PostMedCat_idEdit'];
		$params[] = $data['RecType_id'];
		$params[] = isset($data['MedStaffFact_PriemTime'])?$data['MedStaffFact_PriemTime']:null;
		$params[] = $data['MedStatus_id'];
		$params[] = ($data['MedStaffFact_PriemTime'] == "") ? 1 : 2;
		$params[] = ($data['MedStaffFact_IsQueueOnFree'] == "") ? 1 : 2;
		$params[] = $data['MedStaffFact_Descr'];
		$params[] = $data['MedStaffFact_Contacts'];
		$params[] = $data['pmUser_id'];
		getSQLParams($params);
		$sql = "
		SELECT
		MedStaffFact_id as \"MedStaffFact_id\",
		error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_MedStaffFact_ins(
                Lpu_id => ?,
				Server_id => ?,
				LpuSection_id => ?,
				MedPersonal_id => ?,
				MedStaffFact_Stavka => ?,
				MedStaffFact_IsSpecialist => ?,
				MedStaffFact_IsOMS => ?,
				MedSpecOms_id => ?,
				MedStaffFact_setDate => ?,
				MedStaffFact_disDate => ?,
				MedSpec_id => ?,
				PostMed_id => ?,
				PostMedClass_id => ?,
				PostMedType_id => ?,
				PostMedCat_id => ?,
				RecType_id => ?,
				MedStaffFact_PriemTime => ?,
				MedStatus_id => ?,
				MedStaffFact_IsDirRec => ?,
				MedStaffFact_IsQueueOnFree => ?,
				MedStaffFact_Descr => ?,
				MedStaffFact_Contacts => ?,
				pmUser_id => ?
        )
		";
		$result = $this->db->query($sql, $params);
		if ( is_object($result) )
			return $result->result('array');
		else
			return false;
	} //end insertMedStaffFact()


	/**
	 * Изменение записи о месте работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function updateMedStaffFact($data) {
		$params = array();
		$params[] = $data['MedStaffFact_idEdit'];
		$params[] = $data['Lpu_id'];
		$params[] = $data['Server_id'];
		$params[] = $data['LpuSection_idEdit'];
		$params[] = $data['MedPersonal_idEdit'];
		$params[] = $data['MedStaffFact_StavkaEdit'];
		$params[] = $data['MedStaffFact_IsSpecialistEdit'];
		$params[] = $data['MedStaffFact_IsOMSEdit'];
		$params[] = $data['MedSpecOms_id'];
		$params[] = $data['MedStaffFact_setDateEdit'];
		$params[] = $data['MedStaffFact_disDateEdit'];
		$params[] = $data['MedSpec_idEdit'];
		$params[] = $data['PostMed_idEdit'];
		$params[] = $data['PostMedClass_idEdit'];
		$params[] = $data['PostMedType_idEdit'];
		$params[] = $data['PostMedCat_idEdit'];
		$params[] = $data['RecType_id'];
		$params[] = isset($data['MedStaffFact_PriemTime'])?$data['MedStaffFact_PriemTime']:null;
		$params[] = $data['MedStatus_id'];
		$params[] = ($data['MedStaffFact_IsDirRec'] == "") ? 1 : 2;
		$params[] = ($data['MedStaffFact_IsQueueOnFree'] == "") ? 1 : 2;
		$params[] = $data['MedStaffFact_Descr'];
		$params[] = $data['MedStaffFact_Contacts'];
		$params[] = $data['pmUser_id'];
		getSQLParams($params);
		$sql = "
		SELECT
		MedStaffFact_id as \"MedStaffFact_id\",
		error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_MedStaffFact_upd(
                MedStaffFact_id => ?,
				Lpu_id => ?,
				Server_id => ?,
				LpuSection_id => ?,
				MedPersonal_id => ?,
				MedStaffFact_Stavka => ?,
				MedStaffFact_IsSpecialist => ?,
				MedStaffFact_IsOMS => ?,
				MedSpecOms_id => ?,
				MedStaffFact_setDate => ?,
				MedStaffFact_disDate => ?,
				MedSpec_id => ?,
				PostMed_id => ?,
				PostMedClass_id => ?,
				PostMedType_id => ?,
				PostMedCat_id => ?,
				RecType_id => ?,
				MedStaffFact_PriemTime => ?,
				MedStatus_id => ?,
				MedStaffFact_IsDirRec => ?,
				MedStaffFact_IsQueueOnFree => ?,
				MedStaffFact_Descr => ?,
				MedStaffFact_Contacts => ?,
				pmUser_id => ?
        )
		";
		$result = $this->db->query($sql, $params);
		if ( is_object($result) )
			return $result->result('array');
		else
			return false;
	} //end updateMedStaffFact()


	/**
	 * Проверка на существование врача с заданным $medpersonal_id в БД. Используется при перекэшировании данных пользователей
	 */
	function checkMedPersonalExist($medpersonal_id) {
	    if(empty($medpersonal_id)) return false;
		$sql = "select MedPersonal_id as \"MedPersonal_id\" from v_MedPersonal  where MedPersonal_id = :MedPersonal_id limit 1";

		$params = array('MedPersonal_id' => $medpersonal_id);
		
		$result = $this->db->query($sql, $params);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Загрузка списка медицинского персонала
	 *
	 * @param string $data Фильтры
	 * @param boolean $dloonly Загружать только врачей ЛЛО?
	 */
	function loadMedPersonalList($data, $dloonly = false)
    {
        $filters = array();
        $filtersMP = array();
        $filtersMSF = array();
		$queryParams = array();

        $declare = "";
        $select = "
            MedStaffFact_id as \"MedStaffFact_id\",
            MedPersonal_id as \"MedPersonal_id\",
            MedPersonal_Code as \"MedPersonal_Code\",
            MedPersonal_Fio as \"MedPersonal_Fio\",
            WorkData_endDate as \"WorkData_endDate\",
            notWork as \"notWork\",
            LpuSection_id as \"LpuSection_id\"
        ";
        $join = "";
        $join_inner = "";

		$filtersMP[] = 'MSF.MedStaffFact_id is null';

		if(isset($data['fromRegistryViewForm']) && $data['fromRegistryViewForm'] == 2){ //https://redmine.swan.perm.ru/issues/51050 - для реестров только врачи и средний и младший мед персонал
			$filters[] = "MSF.PostKind_id in (1,3,6)";
		}
		// https://redmine.swan-it.ru/issues/147636#note-13
		// 1. Включать и исключать может любой врач. поэтому делаем ограничение - только врачебные должности
		// 2. для всех, кроме Админа ЦОД и Пользователя МЗ нужно поставить ограничение - отображать рабочие места связанные с пользователем.
		// Комбобокс используется во многих формах, поэтому опцию п.1 убрал в клиентскую часть через параметр withPosts
		else if (isset($data['withPosts'])) {
			$filters[] = "MSF.PostKind_id in ({$data['withPosts']})";
		}

		$isAdmin = isSuperadmin() || isOuzSpec();
		if(
			empty($data['MedPersonalNotNeeded']) //добавлено, т.к. в формы редактирования проб и исследований иначе нельзя подгрузить весь персонал службы
			&& !$isAdmin){// 2. для всех, кроме Админа ЦОД и Пользователя МЗ ограничение
			$medPersonal_id = 0;
			if(!empty($data['MedPersonal_id'])){
				$medPersonal_id = (int)$data['MedPersonal_id'];
			}elseif(!empty($data['session']['medpersonal_id'])){
				$medPersonal_id = (int)$data['session']['medpersonal_id'];
			}
			if($medPersonal_id > 0){// else можно было бы вернуть ошибку
				$filters[] = 'MSF.MedPersonal_id = '.$medPersonal_id;
			}
		}

		// #143613 коммент 57: Надо реализовывать загрузку не всего списка, а с фильтрацией по ФИО
		if($isAdmin && isset($data['querystr']) && mb_strlen($data['querystr']) > 0){// из #147636 коммента 13 следует, что только админы видят весь список врачей, у остальных не будет выбора
			$querystr = trim(mb_substr($data['querystr'], 0, 15));
			$querystr = preg_replace('/[^а-яё әғқңөұүһі]+/iu', '', $querystr);

			if(mb_strlen($querystr) > 2){
				$querystrArr = explode(' ', $querystr, 2);// на 2 части
				$querystr = $querystrArr[0];// первая часть должна быть всегда (можно почистить от опасных символов)
				if(count($querystrArr) > 1){// если есть вторая часть
					$querystrArr[1] = trim(mb_substr($querystrArr[1], 0, 2));// первые 2 символа имени хватит для страны
					if(mb_strlen($querystrArr[1])) {// если что-то осталось от 2-го слова
						$querystr = implode(' ', $querystrArr);
					}
				}
			}

			if(mb_strlen($querystr) > 0){
				$filtersMP[] = "MP.Person_FIO ilike '{$querystr}%'";
				$filtersMSF[] = "MSF.Person_FIO ilike '{$querystr}%'";
			}
		}
		
		//gaf ufa 08052018
        if ($data['Lpu_iid'] > 0) {
        	$filtersMP[] = "MP.Lpu_id = :Lpu_iid";
        	$filtersMSF[] = "MSF.Lpu_id = :Lpu_iid";
			$queryParams['Lpu_iid'] = $data['Lpu_iid'];
		}else{
			if ($data['Lpu_id'] > 0 && !isFarmacy()) {
				$filtersMP[] = "MP.Lpu_id = :Lpu_id";
				$filtersMSF[] = "MSF.Lpu_id = :Lpu_id";
				$queryParams['Lpu_id'] = $data['Lpu_id'];
			}			
		}				

		if ( $data['LpuSection_id'] > 0 ) {
			$filters[] = "MSF.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		} else if (isFarmacy() && isset($data['session']['OrgFarmacy_id'])) {
			//TODO: выборка отделений конечно некорректная, в будущем необходимо уточнить критерии
			$filters[] = "MSF.LpuSection_id in (select LpuSection_id from Contragent  where OrgFarmacy_id = :OrgFarmacy_id or OrgFarmacy_id is null)";

			$queryParams['OrgFarmacy_id'] = $data['session']['OrgFarmacy_id'];
		}
		
		if ( $data['LpuUnit_id'] > 0 )
		{
			
			$filters[] = "MSF.LpuUnit_id = :LpuUnit_id";
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}


		if ( ! empty($data['LpuUnitType_SysNick'])) {

			$data['LpuUnitType_SysNick'] = json_decode($data['LpuUnitType_SysNick']);

			if ( ! empty($data['LpuUnitType_SysNick'])) {
				$filters[] = "LU.LpuUnitType_SysNick in ('" . implode("','", $data['LpuUnitType_SysNick']) . "')";
				$join_inner .= "
					INNER JOIN v_LpuUnit LU ON LU.LpuUnit_id = MSF.LpuUnit_id
				";
			}
		}



		if ( $data['LpuBuilding_id'] > 0 )
		{
			
			$filters[] = "MSF.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if ( $data['MedPersonal_id'] > 0 )
		{
			$filtersMP[] = "MP.MedPersonal_id = :MedPersonal_id";
			$filtersMSF[] = "MSF.MedPersonal_id = :MedPersonal_id";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ($dloonly) {
			if ( $data['session']['region']['nick'] == 'ufa' ) {
				$filtersMP[] = "COALESCE(MP.MedPersonal_TabCode, '0') != '0'";
				$filtersMSF[] = "COALESCE(MSF.MedPersonal_TabCode, '0') != '0'";
			}
			else {
				$filtersMP[] = "COALESCE(MP.MedPersonal_Code, '0') != '0'";
				$filtersMSF[] = "COALESCE(MSF.MedPersonal_Code, '0') != '0'";
			}
		}
		
		if ($data['onlyWorkInLpu'])
		{
			if(!isset($data['endDate'])) //https://redmine.swan.perm.ru/issues/51050
				$filtersMP[] = "MP.WorkData_begDate is not null and MP.WorkData_begDate <= dbo.tzGetDate()";
				$filtersMSF[] = "MSF.WorkData_begDate is not null and MSF.WorkData_begDate <= dbo.tzGetDate()";
			//$filters[] = "([MP].[WorkData_endDate] is null or [MP].[WorkData_endDate] > @today)";
		}

		if ($data['checkDloDate'] && $data['session']['region']['nick'] != 'ufa')
		{
			$filters[] = "MSF.WorkData_dlobegDate is not null and MSF.WorkData_dlobegDate <= dbo.tzGetDate()";
			$filters[] = "(MSF.WorkData_dloendDate is null or MSF.WorkData_dloendDate > dbo.tzGetDate())";
		}

		if (!empty($data['LpuRegion_begDate'])){
			$data['begDate'] = $data['LpuRegion_begDate'];
		}

		if (!empty($data['LpuRegion_endDate'])){
			$data['endDate'] = $data['LpuRegion_endDate'];
		}

		if(!empty($data['begDate'])){ //https://redmine.swan.perm.ru/issues/51050
			$filtersMP[] = "(MP.WorkData_endDate is null or  MP.WorkData_endDate >= :begDate)";
			$filtersMSF[] = "(MSF.WorkData_endDate is null or  MSF.WorkData_endDate >= :begDate)";
			$queryParams['begDate'] = $data['begDate'];
		}
		if(!empty($data['endDate'])){ //https://redmine.swan.perm.ru/issues/51050
			$filtersMP[] = "MP.WorkData_begDate <= :endDate";
			$filtersMSF[] = "MSF.WorkData_begDate <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}
		if (!empty($data['hideNotWork'])) {
			$filtersMP[] = "(MP.WorkData_endDate is null
								or cast(MP.WorkData_endDate as date) >= cast(dbo.tzGetDate() as date))";
			$filtersMSF[] = "(MSF.WorkData_endDate is null
								or cast(MSF.WorkData_endDate as date) >= cast(dbo.tzGetDate() as date))";
		}

        if (!empty($data['displayHmsSpec']) && !empty($data['DrugRequestPeriod_id'])) {
            $drugRequestPeriod_begDate = $this->getFirstResultFromQuery("select DrugRequestPeriod_begDate  as \"DrugRequestPeriod_begDate\" from v_DrugRequestPeriod  where DrugRequestPeriod_id = :DrugRequestPeriod_id limit 1", array(
                'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id']
            ));
            $select = "
                List.MedStaffFact_id as \"MedStaffFact_id\",
                List.MedPersonal_id as \"MedPersonal_id\",
                List.MedPersonal_Code as \"MedPersonal_Code\",
                (
                    List.MedPersonal_Fio ||
                    COALESCE(' ' || replace(replace(hms.HeadMedSpecType_Name, 'Главный внештатный специалист', 'Гл. вн. спец.'), 'Главный внештатный детский специалист', 'Гл. вн. дет. спец.'), '')
                ) as \"MedPersonal_Fio\",
                List.WorkData_endDate as \"WorkData_endDate\",
                List.notWork as \"notWork\",
                --List.WorkData_endDate,
                List.LpuSection_id as \"LpuSection_id\"
            ";
            $join .= "
                LEFT JOIN LATERAL ( -- проверка на включение врача заявки в перечень главных внештатных специалистов
                    select 
                        i_hms.HeadMedSpec_id,
                        i_hmst.HeadMedSpecType_Name
                    from
                        v_MedPersonal i_mp 
                        left join persis.v_MedWorker i_mw  on i_mw.Person_id = i_mp.Person_id
                        inner join v_HeadMedSpec i_hms  on i_hms.MedWorker_id = i_mw.MedWorker_id
                        left join v_HeadMedSpecType i_hmst  on i_hmst.HeadMedSpecType_id = i_hms.HeadMedSpecType_id
                    where
                        i_mp.MedPersonal_id = List.MedPersonal_id and
                        (i_hms.HeadMedSpec_begDT is null or i_hms.HeadMedSpec_begDT <= '{$drugRequestPeriod_begDate}') and
						(i_hms.HeadMedSpec_endDT is null or i_hms.HeadMedSpec_endDT >= '{$drugRequestPeriod_begDate}')
						limit 1
                ) hms on true
            ";
            $queryParams['DrugRequestPeriod_id'] = $data['DrugRequestPeriod_id'];
        }


		$query = "			
			SELECT
				{$select}
			FROM (
				(SELECT 
					MSF.MedStaffFact_id,
					MP.MedPersonal_id AS MedPersonal_id,
					ltrim(rtrim(COALESCE(MP.MedPersonal_TabCode,''))) AS MedPersonal_Code,
					ltrim(rtrim(COALESCE(MP.Person_FIO,''))) AS MedPersonal_Fio,
					to_char(MP.WorkData_endDate, 'DD.MM.YYYY') as WorkData_endDate,
					case when MP.WorkData_endDate < dbo.tzGetDate() then 2 else 1 end as notWork,
					--MP.WorkData_endDate as WorkData_endDate,
					null as LpuSection_id
				FROM
					v_MedPersonal MP 
					LEFT JOIN LATERAL (
						select *
						from v_MedStaffFact 
						where MedPersonal_id = MP.MedPersonal_id
						limit 1
					) MSF on true
					{$join_inner}
					".ImplodeWhere(array_merge($filtersMP, $filters))."
					".($data['All_Rec'] == '' ? " limit 2000" : "").")
				UNION
				(SELECT 
					MSF.MedStaffFact_id,
					MSF.MedPersonal_id AS MedPersonal_id,
					ltrim(rtrim(COALESCE(MSF.MedPersonal_TabCode,''))) AS MedPersonal_Code,
					ltrim(rtrim(COALESCE(MSF.Person_FIO,''))) AS MedPersonal_Fio,
					to_char(MSF.WorkData_endDate, 'DD.MM.YYYY') as WorkData_endDate,
					case when MSF.WorkData_endDate < dbo.tzGetDate() then 2 else 1 end as notWork,
					--MP.WorkData_endDate as WorkData_endDate,
					MSF.LpuSection_id AS LpuSection_id
				FROM
					v_MedStaffFact MSF 
					{$join_inner}
					".ImplodeWhere(array_merge($filtersMSF, $filters))."
					".($data['All_Rec'] == '' ? " limit 2000" : "").")
			) List
			{$join}
			ORDER BY
				ltrim(rtrim(COALESCE(List.MedPersonal_Fio,''))),
				List.WorkData_endDate,
				List.MedPersonal_Code desc";

		//exit(getDebugSQL($query, $queryParams));
        $result = $this->db->query($query, $queryParams);

        if (is_object($result))
        {
            return $result->result('array');
        }
 	    else
 	    {
            return false;
        }
    } //end loadDloMedPersonalList()

	/**
	 * Получение списка медицинского персонала по заданными фильтрами
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с данными медицинского персонала
	 */
	function loadMedStaffFactList($data) {
		$filter = "(1 = 1)";
		$filter_st = "(1 = 1)";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

        $fields = '';
        $join = '';

        $this->load->library('swCache');
		//$data['mode'] = (isset($data['mode']) && $data['mode']=='all')?'all':'';
		
		if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
			if ($resCache = $this->swcache->get("MedStaffFactList_".$data['Lpu_id'])) {
				return $resCache;
			}
		}
		
		if (getRegionNick() == 'buryatiya' && $data['mode'] == 'dispcontractcombo') {
			// Если на дату оказания услуги с выбранной МО нет действующих договоров по сторонним специалистам, то грузим не по договорам
			$filter_ldc = "";
			$params_ldc = [
				'Lpu_oid' => $queryParams['Lpu_id'],
				'Lpu_id' => $data['session']['lpu_id']
			];
			if (!empty($data['onDate'])) {
				$filter_ldc .= " and coalesce(ldc.LpuDispContract_setDate, :onDate) <= :onDate";
				$filter_ldc .= " and coalesce(ldc.LpuDispContract_disDate, :onDate) >= :onDate";
				$params_ldc['onDate'] = date( 'Y-m-d H:i:s' , strtotime($data['onDate']));
			}
			$resp_ldc = $this->queryResult("
				select
					ldc.LpuDispContract_id
				from
					v_LpuDispContract ldc
				where
					ldc.Lpu_oid = :Lpu_oid
					and ldc.Lpu_id = :Lpu_id
					{$filter_ldc}
				limit 1
			", $params_ldc);
			if (empty($resp_ldc[0]['LpuDispContract_id'])) {
				$data['mode'] = 'combo';
			}
		}

        //http://redmine.swan.perm.ru/issues/14521
        if (!empty($data['ignoreDisableInDocParam'])) {
            $IsDisableInDocFilter = '';
        } else {
            $IsDisableInDocFilter = 'and COALESCE(MSF.MedStaffFactCache_IsDisableInDoc, 1) = 1';
        }

		$LPU='
			select
				cast(:Lpu_id as bigint) as Lpu_id
		';
		if (isset($data['Org_id']) && $data['Org_id'] > 0) {
			$queryParams['Org_id'] = $data['Org_id'];
			$LPU='
				select
					cast(Lpu_id as bigint) as Lpu_id
				from v_Lpu_all
				where Org_id = :Org_id
				limit 1
			';
		}

		$dispContractFilterList = array();

        $lp_dlo_date = !empty($data['onDate']) ? $data['onDate'] : date('Y-m-d');

        if (!empty($lp_dlo_date)) {
            $fields .= " ,lp_dlo.LpuPeriodDLO_Code  as \"LpuPeriodDLO_Code\"";
            $fields .= " ,to_char(lp_dlo.LpuPeriodDLO_begDate, 'DD.MM.YYYY') as \"LpuPeriodDLO_begDate\"";
            $fields .= " ,to_char(lp_dlo.LpuPeriodDLO_endDate, 'DD.MM.YYYY') as \"LpuPeriodDLO_endDate\"";

            $join .= "
				LEFT JOIN LATERAL (
					select 
						i_lpd.LpuPeriodDLO_Code,
						i_lpd.LpuPeriodDLO_begDate,
						i_lpd.LpuPeriodDLO_endDate
					from
						v_LpuPeriodDLO i_lpd
					where
						i_lpd.Lpu_id = ls.Lpu_id and
						(
							i_lpd.LpuUnit_id is null or
							i_lpd.LpuUnit_id = ls.LpuUnit_id			
						) and
						i_lpd.LpuPeriodDLO_begDate <= CAST(:lp_dlo_date as date) and
						(
							i_lpd.LpuPeriodDLO_endDate is null or
							i_lpd.LpuPeriodDLO_endDate >= CAST(:lp_dlo_date as date)
						)
					order by
						i_lpd.LpuUnit_id desc, LpuPeriodDLO_Code desc
				    limit 1
				) lp_dlo ON true
			";

            $queryParams['lp_dlo_date'] = date( 'Y-m-d H:i:s' , strtotime($lp_dlo_date));
        }

		if (!empty($data['onDate'])) {
			$filter .= " and (LS.LpuSection_setDate IS NULL OR LS.LpuSection_setDate <= :onDate) and (COALESCE(LS.LpuSection_disDate, L.Lpu_endDate) IS NULL OR COALESCE(LS.LpuSection_disDate, L.Lpu_endDate) >= :onDate)";
			$filter .= " and (MSF.WorkData_begDate IS NULL OR MSF.WorkData_begDate <= :onDate) and (MSF.WorkData_endDate IS NULL OR MSF.WorkData_endDate >= :onDate)";

			$filter_st .= " and (LS.LpuSection_setDate IS NULL OR LS.LpuSection_setDate <= :onDate) and (COALESCE(LS.LpuSection_disDate, L.Lpu_endDate) IS NULL OR COALESCE(LS.LpuSection_disDate, L.Lpu_endDate) >= :onDate)";
			$filter_st .= " and (MSF.WorkData_begDate IS NULL OR MSF.WorkData_begDate <= :onDate) and (MSF.WorkData_endDate IS NULL OR MSF.WorkData_endDate >= :onDate)";

			$dispContractFilterList[] = '(LpuDispContract_setDate is null or LpuDispContract_setDate <= :onDate)';
			$dispContractFilterList[] = '(LpuDispContract_disDate is null or LpuDispContract_disDate >= :onDate)';

			$queryParams['onDate'] = date( 'Y-m-d H:i:s' , strtotime($data['onDate']));
		}

		if ( !empty($data['LpuUnit_id']) && $data['LpuUnit_id'] > 0 ) {
			$filter .= " and LU.LpuUnit_id = :LpuUnit_id";
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( !empty($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0 ) {
			$filter .= " and MSF.MedPersonal_id = :MedPersonal_id";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ( !empty($data['MedStaffFact_id']) && $data['MedStaffFact_id'] > 0 ) {
			// определённый врач
			$filter .= " and MSF.MedStaffFact_id = :MedStaffFact_id";
			$filter_st .= " and MSF.MedStaffFact_id = :MedStaffFact_id";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		} else if ( !empty($data['LpuSection_id']) && $data['LpuSection_id'] > 0 ) {
			// определёное отделение
			$filter .= " and MSF.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		} else {
			if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) {
				if (isFarmacy() && isset($data['session']['OrgFarmacy_id'])) {
					//TODO: выборка отделений конечно некорректная, в будущем необходимо уточнить критерии
					$filter .= " and LS.LpuSection_id in (select LpuSection_id from Contragent  where OrgFarmacy_id = :OrgFarmacy_id or OrgFarmacy_id is null)";
					$queryParams['OrgFarmacy_id'] = $data['session']['OrgFarmacy_id'];
				}
				//else if ( array_key_exists('linkedLpuIdList', $data['session']) ) {
				else if ( array_key_exists('linkedLpuIdList', $data['session']) && (empty($data['mode']) || !in_array($data['mode'], array('combo', 'addSubProfile'))) ) {
					$filter .= " and MSF.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ") or MSF.Lpu_id = (select Lpu_id from mv)";
				}
				else {
					$filter .= " and MSF.Lpu_id = (select Lpu_id from mv)";
				}
			}
		}

		if ( !empty($data['PostMedType_Code']) && $data['PostMedType_Code'] > 0 ) {
			$filter .= " and (PK.code is null or COALESCE(PK.code, 0) = :PostMedType_Code)";

			$queryParams['PostMedType_Code'] = $data['PostMedType_Code'];
		}
		
		if ( !empty($data['withDloCodeOnly']) && $data['withDloCodeOnly'] == 1 ) {
			$filter .= " and MSF.MedPersonal_Code IS NOT NULL";
			$filter_st .= " and MSF.MedPersonal_Code IS NOT NULL";
			$queryParams['MedSpecOms_id'] = $data['MedSpecOms_id'];
		}

		if ( !empty($data['MedSpecOms_id']) ) {
			$filter .= " and MSF.MedSpecOms_id = :MedSpecOms_id";
			$filter_st .= " and MSF.MedSpecOms_id = :MedSpecOms_id";
			$queryParams['MedSpecOms_id'] = $data['MedSpecOms_id'];
		}

		if ( !empty($data['andWithoutLpuSection']) && ($data['andWithoutLpuSection'] == 1 || $data['mode'] == 'addSubProfile')) {
			$filter .= " and LS.LpuSection_id is not null";
			$filter .= " and LS.LpuSection_setDate is not null ";
			$filter .= " and LS.LpuSectionProfile_id is not null";
			$filter .= " and LU.LpuUnit_id is not null";
			$filter .= " and LUT.LpuUnitType_id is not null";
		}

		if (!empty($data['isDoctor'])) {
			$filter .= " and (MSF.PostKind_id = 1 or p.code = 6)";
		}

		if (!empty($data['isMidMedPersonal'])) {
			$filter .= " and (MSF.PostKind_id IN (3,6) or p.code = 115)";
		}

		// Добавил в список Астрахань
		// https://redmine.swan.perm.ru/issues/17450
		// Изменил подход к выборке полей WorkData_dloBegDate и WorkData_dloEndDate
		// https://redmine.swan.perm.ru/issues/95205

		if ( in_array($data['session']['region']['nick'], array('saratov'/*, 'ufa'*/)) ) { //Исключил Уфу в рамках задачи https://redmine.swan.perm.ru/issues/98008
			$fields .= "
				,null as \"WorkData_dloBegDate\"
				,null as \"WorkData_dloEndDate\"
			";
		}
		else {
			$fields .= "
				,to_char(MSF.WorkData_dlobegDate, 'DD.MM.YYYY') as \"WorkData_dloBegDate\"

				,to_char(MSF.WorkData_dloendDate, 'DD.MM.YYYY') as \"WorkData_dloEndDate\"

			";
		}

		if ( $data['mode'] == 'all' ) {
			$fields .= ',L.Lpu_Nick as "Lpu_Name"';
			$fields .= ',case when MSF.Lpu_id = :Lpu_id then 1 else 2 end as "sortID"';
		}

		if(!empty($data['MedService_id'])) {
			$join .= 'left join v_MedServiceMedPersonal MSMP on MSMP.MedPersonal_id = MSF.MedPersonal_id';
			$filter .= ' and MSMP.MedService_id = :MedService_id';
			$filter_st .= ' and MSMP.MedService_id = :MedService_id';
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		// скрываем фиктивные рабочие места или места с фиктивными строками в ставке
		if (!empty($data['hideDummy'])) {

			$join .= " 
				left join lateral(
					select
						wp.WorkPlace_id
					from persis.v_WorkPlace wp
					left join persis.v_Staff staff on staff.id = wp.Staff_id
					where (1=1)
						and wp.WorkPlace_id = MSF.MedStaffFact_id
						and wp.IsDummyWP != 1
						and staff.IsDummyStaff != 1
					limit 1
				) wp on true
			";

			$filter .= " and wp.WorkPlace_id is not null ";
		}

		if (isset($data['mode']) && $data['mode'] == 'emd') {
			$fields .= ' ,L.Lpu_Nick as "Lpu_Name" ';
			$fields .= ' ,LS.LpuSection_Name as "LpuSection_RawName" ';

			if (!empty($data['LpuBuilding_id'])) {
				$filter .= ' and MSF.LpuBuilding_id = :LpuBuilding_id ';
				$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}

			if (!empty($data['hideFired'])) {
				$filter .= ' and MSF.WorkData_endDate is null ';
			}
		}

		if ($this->getRegionNick() == 'kareliya'){
			$fields .= ",MSRF3.LpuRegion_dates as \"LpuRegion_DatesList\"";
			$join .= " left join lateral ( 
                        select string_agg(
                            cast(MSR3.LpuRegion_id as varchar) || ':' ||
                            coalesce(to_char(MSR3.MedStaffRegion_begDate, 'DD.MM.YYYY'), '0') || ':' ||
                            coalesce(to_char(MSR3.MedStaffRegion_endDate, 'DD.MM.YYYY'), '0')
                        , ',') as LpuRegion_dates
                        from v_MedStaffRegion MSR3
                        where MSR3.MedStaffFact_id = MSF.MedStaffFact_id
                    ) MSRF3 on true ";
		} else {
			$fields .= ",null as \"LpuRegion_DatesList\"";
		}

		$adminpersonal = "";
		
		if ( !empty($data['loadAdminPersonal']) ) {
			$adminpersonal = "
				union all
				-- административный персонал
				select distinct
					cast(MSF.MedStaffFact_id as varchar) || '_' as \"MedStaffFactKey_id\",
					'main' as \"listType\",
					MSF.MedStaffFact_id as \"MedStaffFact_id\",
					MSF.MedPersonal_id as \"MedPersonal_id\",
					COALESCE(LTRIM(RTRIM(MSF.Person_Snils)), '') as \"Person_Snils\",
					COALESCE(LTRIM(RTRIM(MSF.MedPersonal_TabCode)), '') as \"MedPersonal_TabCode\",
					COALESCE(LTRIM(RTRIM(MSF.MedPersonal_Code)), '') as \"MedPersonal_DloCode\",
					LTRIM(RTRIM(MSF.Person_FIO)) as \"MedPersonal_Fio\",
					MSF.Person_SurName || ' ' || COALESCE(SUBSTRING(MSF.Person_FirName,1,1) || '.', '') || COALESCE(SUBSTRING(MSF.Person_SecName,1,1) || '.', '') as \"MedPersonal_Fin\",
					'[' || LTRIM(RTRIM(LS.LpuSection_Code)) || '. ' || LTRIM(RTRIM(LS.LpuSection_Name)) || ']' as \"LpuSection_Name\",
					COALESCE(LSP.LpuSectionProfile_Code, '') as \"LpuSectionProfile_Code\",
					COALESCE(LSP.LpuSectionProfile_SysNick, '') as \"LpuSectionProfile_SysNick\",
					COALESCE(LSP.LpuSectionProfile_Name, '') as \"LpuSectionProfile_Name\",
					MSF.LpuSectionProfile_id as \"LpuSectionProfile_msfid\",
					COALESCE(cast(LUT.LpuUnitType_Code as varchar),cast(LUTm.LpuUnitType_Code as varchar), '') as \"LpuUnitType_Code\",
					COALESCE(LUT.LpuUnitType_SysNick,LUTm.LpuUnitType_SysNick, '') as \"LpuUnitType_SysNick\",
					MSF.Lpu_id as \"Lpu_id\",
					COALESCE(LU.LpuBuilding_id,MSF.LpuBuilding_id) as \"LpuBuilding_id\",
					COALESCE(LB.LpuBuildingType_id,LBmsf.LpuBuildingType_id) as \"LpuBuildingType_id\",
					LU.LpuUnit_id as \"LpuUnit_id\",
					LU.LpuUnitSet_id as \"LpuUnitSet_id\",
					LS.LpuSection_id as \"LpuSection_id\",
					LS.LpuSection_pid as \"LpuSection_pid\",
					LS.LpuSectionAge_id as \"LpuSectionAge_id\",
					to_char(COALESCE(LS.LpuSection_disDate, L.Lpu_endDate), 'DD.MM.YYYY') as \"LpuSection_disDate\",
					to_char(LS.LpuSection_setDate, 'DD.MM.YYYY') as \"LpuSection_setDate\",
					to_char(MSF.WorkData_begDate, 'DD.MM.YYYY') as \"WorkData_begDate\",
					to_char(MSF.WorkData_endDate, 'DD.MM.YYYY') as \"WorkData_endDate\",
					PK.id as \"PostKind_id\",
					p.id as \"PostMed_id\",
					p.code as \"PostMed_Code\",
					p.name as \"PostMed_Name\",
					p.frmpEntry_id as \"frmpEntry_id\",
					MSO.MedSpecOms_id as \"MedSpecOms_id\",
					MSO.MedSpecOms_Code as \"MedSpecOms_Code\",
					FMS.MedSpec_id as \"FedMedSpec_id\",
					FMS.MedSpec_Code as \"FedMedSpec_Code\",
					FMSP.MedSpec_Code as \"FedMedSpecParent_Code\",
					COALESCE(MSF.MedStaffFactCache_IsDisableInDoc, 1) as \"MedStaffFactCache_IsDisableInDoc\"
					,cast(MSF.MedStaffFact_Stavka as varchar) as \"MedStaffFact_Stavka\"
					,case when p.PrimaryHealthCare = 1 then 2 else 1 end as \"Post_IsPrimaryHealthCare\"
					,MSRF.LpuRegion_ids as \"LpuRegion_List\"
					,MSRF2.LpuRegion_mids as \"LpuRegion_MainList\"
					,MSFC.MedStaffFactCache_IsHomeVisit as \"MedStaffFactCache_IsHomeVisit\"
					,MPost.MedPost_pid as \"MedPost_pid\"
					,nsiMPP.MedPersonalPost_Code as \"MedPersonalPost_Code\"

					" . $fields . "
				from v_MedStaffFact MSF 
					left join v_Lpu L  on L.Lpu_id = MSF.Lpu_id
					LEFT join v_LpuSection LS  on LS.LpuSection_id = MSF.LpuSection_id
					LEFT join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id 
					LEFT join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
					LEFT join v_LpuUnit LUm  on LUm.LpuUnit_id = MSF.LpuUnit_id
					LEFT join v_LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id
					LEFT join v_LpuUnitType LUTm  on LUTm.LpuUnitType_id = LUm.LpuUnitType_id
					LEFT join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
					LEFT join v_LpuBuilding LBmsf  on LBmsf.LpuBuilding_id = MSF.LpuBuilding_id
					left join v_MedSpecOms MSO  on MSO.MedSpecOms_id = MSF.MedSpecOms_id
					left join fed.v_MedSpec FMS  on FMS.MedSpec_id = MSO.MedSpec_id
					left join fed.v_MedSpec FMSP  on FMSP.MedSpec_id = FMS.MedSpec_pid
					left join persis.PostKind PK  on PK.id = MSF.PostKind_id
					LEFT JOIN persis.Post p  on p.id = msf.Post_id



					inner join persis.FRMPPost fp  on fp.id = p.frmpEntry_id
					left join nsi.MedPersonalPost nsiMPP on nsiMPP.MedPersonalPost_id = fp.Frmr_id

					LEFT JOIN LATERAL(
						select
							string_agg(cast(MSR.LpuRegion_id as varchar), ',') as LpuRegion_ids
						from
							v_MedStaffRegion MSR 
						where
							MSR.MedStaffFact_id = MSF.MedStaffFact_id
					) MSRF on true
					LEFT JOIN LATERAL(
						select
							string_agg(cast(MSR2.LpuRegion_id as varchar), ',') as LpuRegion_mids
						from
							v_MedStaffRegion MSR2 
						where
							MSR2.MedStaffFact_id = MSF.MedStaffFact_id
							and COALESCE(MSR2.MedStaffRegion_isMain,1) = 2
					) MSRF2 on true
					left join v_MedStaffFactCache MSFC  on MSFC.MedStaffFact_id = MSF.MedStaffFact_id and MSFC.Server_id = MSF.Server_id
					
                    LEFT JOIN nsi.MedPost MPost ON MPost.MedPost_id = p.MedPost_id

					" . $join . "
				where " . $filter . "
					and MSF.WorkData_begDate is not null
					and fp.parent in (1,5)
					$IsDisableInDocFilter
			";
		}
		
		$query = "
			with mv as (
				{$LPU}
			)

			select * from (
				select distinct
					 cast(MSF.MedStaffFact_id as varchar) || '_' as \"MedStaffFactKey_id\"
					,'main' as \"listType\"
					,MSF.MedStaffFact_id as \"MedStaffFact_id\"
					,MSF.MedPersonal_id as \"MedPersonal_id\"
					,COALESCE(LTRIM(RTRIM(MSF.Person_Snils)), '') as \"Person_Snils\"
					,COALESCE(LTRIM(RTRIM(MSF.MedPersonal_TabCode)), '') as \"MedPersonal_TabCode\"
					,COALESCE(LTRIM(RTRIM(MSF.MedPersonal_Code)), '') as \"MedPersonal_DloCode\"
					,LTRIM(RTRIM(MSF.Person_FIO)) as \"MedPersonal_Fio\"
					,MSF.Person_SurName || ' ' || COALESCE(SUBSTRING(MSF.Person_FirName,1,1) || '.', '') || COALESCE(SUBSTRING(MSF.Person_SecName,1,1) || '.', '') as \"MedPersonal_Fin\"
					,'[' || LTRIM(RTRIM(LS.LpuSection_Code)) || '. ' || LTRIM(RTRIM(LS.LpuSection_Name)) || ']' as \"LpuSection_Name\"
					,COALESCE(LSP.LpuSectionProfile_Code, '') as \"LpuSectionProfile_Code\"
					,COALESCE(LSP.LpuSectionProfile_SysNick, '') as \"LpuSectionProfile_SysNick\"
					,COALESCE(LSP.LpuSectionProfile_Name, '') as \"LpuSectionProfile_Name\"
					,MSF.LpuSectionProfile_id as \"LpuSectionProfile_msfid\"
					,COALESCE(cast(LUT.LpuUnitType_Code as varchar), cast(LUTm.LpuUnitType_Code as varchar), '') as \"LpuUnitType_Code\"
					,COALESCE(LUT.LpuUnitType_SysNick,LUTm.LpuUnitType_SysNick, '') as \"LpuUnitType_SysNick\"
					,MSF.Lpu_id as \"Lpu_id\"
					,COALESCE(LU.LpuBuilding_id,MSF.LpuBuilding_id) as \"LpuBuilding_id\"
					,COALESCE(LB.LpuBuildingType_id,LBmsf.LpuBuildingType_id) as \"LpuBuildingType_id\"
					,LU.LpuUnit_id as \"LpuUnit_id\"
					,LU.LpuUnitSet_id as \"LpuUnitSet_id\"
					,LS.LpuSection_id as \"LpuSection_id\"
					,LS.LpuSection_pid as \"LpuSection_pid\"
					,LS.LpuSectionAge_id as \"LpuSectionAge_id\"
					,to_char(COALESCE(LS.LpuSection_disDate, L.Lpu_endDate), 'DD.MM.YYYY') as \"LpuSection_disDate\"
					,to_char(LS.LpuSection_setDate, 'DD.MM.YYYY') as \"LpuSection_setDate\"
					,to_char(MSF.WorkData_begDate, 'DD.MM.YYYY') as \"WorkData_begDate\"
					,to_char(MSF.WorkData_endDate, 'DD.MM.YYYY') as \"WorkData_endDate\"
					,PK.id as \"PostKind_id\"
					,p.id as \"PostMed_id\"
					,p.code as \"PostMed_Code\"
					,p.name as \"PostMed_Name\"
					,p.frmpEntry_id as \"frmpEntry_id\"
					,MSO.MedSpecOms_id as \"MedSpecOms_id\"
					,MSO.MedSpecOms_Code as \"MedSpecOms_Code\"
					,FMS.MedSpec_id as \"FedMedSpec_id\"
					,FMS.MedSpec_Code as \"FedMedSpec_Code\"
					,FMSP.MedSpec_Code as \"FedMedSpecParent_Code\"
					,COALESCE(MSF.MedStaffFactCache_IsDisableInDoc, 1) as \"MedStaffFactCache_IsDisableInDoc\"
					,cast(MSF.MedStaffFact_Stavka as varchar) as \"MedStaffFact_Stavka\"
					,case when p.PrimaryHealthCare = 1 then 2 else 1 end as \"Post_IsPrimaryHealthCare\"
					,MSRF.LpuRegion_ids as \"LpuRegion_List\"
					,MSRF2.LpuRegion_mids as \"LpuRegion_MainList\"
					,MSFC.MedStaffFactCache_IsHomeVisit as \"MedStaffFactCache_IsHomeVisit\"
					,MPost.MedPost_pid as \"MedPost_pid\"
					,nsiMPP.MedPersonalPost_Code as \"MedPersonalPost_Code\"

					" . $fields . "
				from v_MedStaffFact MSF 



					left join v_Lpu L  on L.Lpu_id = MSF.Lpu_id
					left join v_LpuSection LS  on LS.LpuSection_id = MSF.LpuSection_id
					left join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					LEFT join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
					LEFT join v_LpuUnit LUm  on LUm.LpuUnit_id = MSF.LpuUnit_id
					LEFT join v_LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id
					LEFT join v_LpuUnitType LUTm  on LUTm.LpuUnitType_id = LUm.LpuUnitType_id
					LEFT join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
					LEFT join v_LpuBuilding LBmsf  on LBmsf.LpuBuilding_id = MSF.LpuBuilding_id
					left join v_MedSpecOms MSO  on MSO.MedSpecOms_id = MSF.MedSpecOms_id
					left join fed.v_MedSpec FMS  on FMS.MedSpec_id = MSO.MedSpec_id
					left join fed.v_MedSpec FMSP  on FMSP.MedSpec_id = FMS.MedSpec_pid
					left join persis.PostKind PK  on PK.id = MSF.PostKind_id
					left join persis.Post p  on p.id = MSF.Post_id
					
					left join persis.FRMPPost fp on fp.id = p.frmpEntry_id
					left join nsi.MedPersonalPost nsiMPP on nsiMPP.MedPersonalPost_id = fp.Frmr_id

					LEFT JOIN LATERAL(
						select
							string_agg(cast(MSR.LpuRegion_id as varchar), ',') as LpuRegion_ids
						from
							v_MedStaffRegion MSR 
						where
							MSR.MedStaffFact_id = MSF.MedStaffFact_id
					) MSRF on true
					LEFT JOIN LATERAL(
						select
							string_agg(cast(MSR2.LpuRegion_id as varchar), ',') as LpuRegion_mids
						from
							v_MedStaffRegion MSR2 
						where
							MSR2.MedStaffFact_id = MSF.MedStaffFact_id
							and COALESCE(MSR2.MedStaffRegion_isMain,1) = 2
					) MSRF2 on true
					left join v_MedStaffFactCache MSFC  on MSFC.MedStaffFact_id = MSF.MedStaffFact_id and MSFC.Server_id = MSF.Server_id
                    LEFT JOIN nsi.MedPost MPost ON MPost.MedPost_id = p.MedPost_id
                    
					" . $join . "
				where " . $filter . "
					and MSF.WorkData_begDate is not null
					$IsDisableInDocFilter
				union all
				select distinct
					 cast(MSF.MedStaffFact_id as varchar) || '_' || cast(WGLS.WorkGraphLpuSection_id as varchar) as \"MedStaffFactKey_id\"
					,'main' as \"listType\"
					,MSF.MedStaffFact_id as \"MedStaffFact_id\"
					,MSF.MedPersonal_id as \"MedPersonal_id\"
					,COALESCE(LTRIM(RTRIM(MSF.Person_Snils)), '') as \"Person_Snils\"
					,COALESCE(LTRIM(RTRIM(MSF.MedPersonal_TabCode)), '') as \"MedPersonal_TabCode\"
					,COALESCE(LTRIM(RTRIM(MSF.MedPersonal_Code)), '') as \"MedPersonal_DloCode\"
					,LTRIM(RTRIM(MSF.Person_FIO)) as \"MedPersonal_Fio\"
					,MSF.Person_SurName || ' ' || COALESCE(SUBSTRING(MSF.Person_FirName,1,1) || '.', '') || COALESCE(SUBSTRING(MSF.Person_SecName,1,1) || '.', '') as \"MedPersonal_Fin\"
					,'[' || LTRIM(RTRIM(LS.LpuSection_Code)) || '. ' || LTRIM(RTRIM(LS.LpuSection_Name)) || ']' as \"LpuSection_Name\"
					,COALESCE(LSP.LpuSectionProfile_Code, '') as \"LpuSectionProfile_Code\"
					,COALESCE(LSP.LpuSectionProfile_SysNick, '') as \"LpuSectionProfile_SysNick\"
					,COALESCE(LSP.LpuSectionProfile_Name, '') as \"LpuSectionProfile_Name\"
					,MSF.LpuSectionProfile_id as \"LpuSectionProfile_msfid\"
					,COALESCE(cast(LUT.LpuUnitType_Code as varchar), '') as \"LpuUnitType_Code\"
					,COALESCE(LUT.LpuUnitType_SysNick, '') as \"LpuUnitType_SysNick\"
					,MSF.Lpu_id as \"Lpu_id\"
					,COALESCE(LU.LpuBuilding_id,MSF.LpuBuilding_id) as \"LpuBuilding_id\"
					,COALESCE(LB.LpuBuildingType_id,LBmsf.LpuBuildingType_id) as \"LpuBuildingType_id\"
					,LU.LpuUnit_id as \"LpuUnit_id\"
					,LU.LpuUnitSet_id as \"LpuUnitSet_id\"
					,LS.LpuSection_id as \"LpuSection_id\"
					,LS.LpuSection_pid as \"LpuSection_pid\"
					,LS.LpuSectionAge_id as \"LpuSectionAge_id\"
					,to_char(COALESCE(LS.LpuSection_disDate, L.Lpu_endDate), 'DD.MM.YYYY') as \"LpuSection_disDate\"
					,to_char(LS.LpuSection_setDate, 'DD.MM.YYYY') as \"LpuSection_setDate\"
					,to_char(MSF.WorkData_begDate, 'DD.MM.YYYY') as \"WorkData_begDate\"
					,to_char(MSF.WorkData_endDate, 'DD.MM.YYYY') as \"WorkData_endDate\"
					,PK.id as \"PostKind_id\"
					,p.id as \"PostMed_id\"
					,p.code as \"PostMed_Code\"
					,p.name as \"PostMed_Name\"
					,p.frmpEntry_id as \"frmpEntry_id\"
					,MSO.MedSpecOms_id as \"MedSpecOms_id\"
					,MSO.MedSpecOms_Code as \"MedSpecOms_Code\"
					,FMS.MedSpec_id as \"FedMedSpec_id\"
					,FMS.MedSpec_Code as \"FedMedSpec_Code\"
					,FMSP.MedSpec_Code as \"FedMedSpecParent_Code\"
					,COALESCE(MSF.MedStaffFactCache_IsDisableInDoc, 1) as \"MedStaffFactCache_IsDisableInDoc\"
					,cast(MSF.MedStaffFact_Stavka as varchar) as \"MedStaffFact_Stavka\"
					,case when p.PrimaryHealthCare = 1 then 2 else 1 end as \"Post_IsPrimaryHealthCare\"
					,MSRF.LpuRegion_ids as \"LpuRegion_List\"
					,MSRF2.LpuRegion_mids as \"LpuRegion_MainList\"
					,MSFC.MedStaffFactCache_IsHomeVisit as \"MedStaffFactCache_IsHomeVisit\"
					,MPost.MedPost_pid as \"MedPost_pid\"
					,nsiMPP.MedPersonalPost_Code as \"MedPersonalPost_Code\"
					" . $fields . "
				from v_MedStaffFact MSF 
					left join v_Lpu L  on L.Lpu_id = MSF.Lpu_id
					inner join v_WorkGraph WG on (
						WG.MedStaffFact_id = msf.MedStaffFact_id and
						(
							CAST(WG.WorkGraph_begDT as date) <= CAST(dbo.tzGetDate() as date)
							and CAST(WG.WorkGraph_endDT as date) >= CAST(dbo.tzGetDate() as date)
						)
					)
					left join v_WorkGraphLpuSection WGLS on WGLS.WorkGraph_id = WG.WorkGraph_id
					left join v_LpuSection ls  on ls.LpuSection_id = WGLS.LpuSection_id
					left join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
					left join v_LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id
					LEFT join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
					LEFT join v_LpuBuilding LBmsf  on LBmsf.LpuBuilding_id = MSF.LpuBuilding_id
					left join v_MedSpecOms MSO  on MSO.MedSpecOms_id = MSF.MedSpecOms_id
					left join fed.v_MedSpec FMS  on FMS.MedSpec_id = MSO.MedSpec_id
					left join fed.v_MedSpec FMSP  on FMSP.MedSpec_id = FMS.MedSpec_pid
					left join persis.PostKind PK  on PK.id = MSF.PostKind_id
					left join persis.Post p  on p.id = MSF.Post_id
					left join persis.FRMPPost fp on fp.id = p.frmpEntry_id
					left join nsi.MedPersonalPost nsiMPP on nsiMPP.MedPersonalPost_id = fp.Frmr_id

					LEFT JOIN LATERAL(
						select
							string_agg(cast(MSR.LpuRegion_id as varchar), ',') as LpuRegion_ids
						from
							v_MedStaffRegion MSR 
						where
							MSR.MedStaffFact_id = MSF.MedStaffFact_id
					) MSRF on true
					LEFT JOIN LATERAL(
						select
							string_agg(cast(MSR2.LpuRegion_id as varchar), ',') as LpuRegion_mids
						from
							v_MedStaffRegion MSR2 
						where
							MSR2.MedStaffFact_id = MSF.MedStaffFact_id
							and COALESCE(MSR2.MedStaffRegion_isMain,1) = 2
					) MSRF2 on true
					left join v_MedStaffFactCache MSFC  on MSFC.MedStaffFact_id = MSF.MedStaffFact_id and MSFC.Server_id = MSF.Server_id
                    LEFT JOIN nsi.MedPost MPost ON MPost.MedPost_id = p.MedPost_id
					" . $join . "
				where " . $filter . "
					and MSF.WorkData_begDate is not null
					$IsDisableInDocFilter
		";

		$dispcontractquery = "
			-- сторонние специалисты (пока по ним нет даты окончания договора, поэтому если появится - то просто добавить в последний join)
			select distinct
				 cast(MSF.MedStaffFact_id as varchar) || '_' as \"MedStaffFactKey_id\"
				,'dispcontract' as \"listType\"
				,MSF.MedStaffFact_id as \"MedStaffFact_id\"
				,MSF.MedPersonal_id as \"MedPersonal_id\"
				,COALESCE(LTRIM(RTRIM(MSF.Person_Snils)), '') as \"Person_Snils\"
				,COALESCE(LTRIM(RTRIM(MSF.MedPersonal_TabCode)), '') as \"MedPersonal_TabCode\"
				,COALESCE(LTRIM(RTRIM(MSF.MedPersonal_Code)), '') as \"MedPersonal_DloCode\"
				,LTRIM(RTRIM(MSF.Person_FIO)) as \"MedPersonal_Fio\"
				,MSF.Person_SurName || ' ' || COALESCE(SUBSTRING(MSF.Person_FirName,1,1) || '.', '') || COALESCE(SUBSTRING(MSF.Person_SecName,1,1) || '.', '') as \"MedPersonal_Fin\"
				,'[' || LTRIM(RTRIM(LS.LpuSection_Code)) || '. ' || LTRIM(RTRIM(LS.LpuSection_Name)) || ']' as \"LpuSection_Name\"
				,COALESCE(LSP.LpuSectionProfile_Code, '') as \"LpuSectionProfile_Code\"
				,COALESCE(LSP.LpuSectionProfile_SysNick, '') as \"LpuSectionProfile_SysNick\"
				,COALESCE(LSP.LpuSectionProfile_Name, '') as \"LpuSectionProfile_Name\"
				,MSF.LpuSectionProfile_id as \"LpuSectionProfile_msfid\"
				,COALESCE(cast(LUT.LpuUnitType_Code as varchar), '') as \"LpuUnitType_Code\"
				,COALESCE(LUT.LpuUnitType_SysNick, '') as \"LpuUnitType_SysNick\"
				,MSF.Lpu_id as \"Lpu_id\"
				,COALESCE(LU.LpuBuilding_id,MSF.LpuBuilding_id) as \"LpuBuilding_id\"
				,COALESCE(LB.LpuBuildingType_id,LBmsf.LpuBuildingType_id) as \"LpuBuildingType_id\"
				,LU.LpuUnit_id as \"LpuUnit_id\"
				,LU.LpuUnitSet_id as \"LpuUnitSet_id\"
				,LS.LpuSection_id as \"LpuSection_id\"
				,LS.LpuSection_pid as \"LpuSection_pid\"
				,LS.LpuSectionAge_id as \"LpuSectionAge_id\"
				,to_char(COALESCE(LS.LpuSection_disDate, L.Lpu_endDate), 'DD.MM.YYYY') as \"LpuSection_disDate\"
				,to_char(LS.LpuSection_setDate, 'DD.MM.YYYY') as \"LpuSection_setDate\"
				,to_char(MSF.WorkData_begDate, 'DD.MM.YYYY') as \"WorkData_begDate\"
				,to_char(MSF.WorkData_endDate, 'DD.MM.YYYY') as \"WorkData_endDate\"
				,PK.id as \"PostKind_id\"
				,p.id as \"PostMed_id\"
				,p.code as \"PostMed_Code\"
				,p.name as \"PostMed_Name\"
				,p.frmpEntry_id as \"frmpEntry_id\"
				,MSO.MedSpecOms_id as \"MedSpecOms_id\"
				,MSO.MedSpecOms_Code as \"MedSpecOms_Code\"
				,FMS.MedSpec_id as \"FedMedSpec_id\"
				,FMS.MedSpec_Code as \"FedMedSpec_Code\"
				,FMSP.MedSpec_Code as \"FedMedSpecParent_Code\"
				,COALESCE(MSF.MedStaffFactCache_IsDisableInDoc, 1) as \"MedStaffFactCache_IsDisableInDoc\"
				,cast(MSF.MedStaffFact_Stavka as varchar) as \"MedStaffFact_Stavka\"
				,case when p.PrimaryHealthCare = 1 then 2 else 1 end as \"Post_IsPrimaryHealthCare\"
				,MSRF.LpuRegion_ids as \"LpuRegion_List\"
				,MSRF2.LpuRegion_mids as \"LpuRegion_MainList\"
				,MSFC.MedStaffFactCache_IsHomeVisit as \"MedStaffFactCache_IsHomeVisit\"
				,MPost.MedPost_pid as \"MedPost_pid\"
			    ,nsiMPP.MedPersonalPost_Code as \"MedPersonalPost_Code\"
				" . $fields . "
			from v_MedStaffFact MSF 
				left join v_Lpu L  on L.Lpu_id = MSF.Lpu_id
				inner join v_LpuSection LS  on LS.LpuSection_id = MSF.LpuSection_id
					and LS.LpuSection_setDate is not null
				inner join v_LpuSectionProfile LSP  on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				inner join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
				inner join v_LpuBuilding LB  on LU.LpuBuilding_id = LB.LpuBuilding_id
				inner join v_LpuUnitType LUT  on LUT.LpuUnitType_id = LU.LpuUnitType_id
				LEFT join v_LpuBuilding LBmsf  on LBmsf.LpuBuilding_id = MSF.LpuBuilding_id
				left join v_MedSpecOms MSO  on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				left join fed.v_MedSpec FMS  on FMS.MedSpec_id = MSO.MedSpec_id
				left join fed.v_MedSpec FMSP  on FMSP.MedSpec_id = FMS.MedSpec_pid
				left join persis.PostKind PK  on PK.id = MSF.PostKind_id
				left join persis.Post p  on p.id = MSF.Post_id
				
				left join persis.FRMPPost fp on fp.id = p.frmpEntry_id
				left join nsi.MedPersonalPost nsiMPP on nsiMPP.MedPersonalPost_id = fp.Frmr_id
					

				inner join LpuDispContract LDC  on LDC.Lpu_id = (select Lpu_id from mv)

				LEFT JOIN LATERAL(
					select
						string_agg(cast(MSR.LpuRegion_id as varchar), ',') as LpuRegion_ids
					from
						v_MedStaffRegion MSR 
					where
						MSR.MedStaffFact_id = MSF.MedStaffFact_id
				) MSRF on true
				LEFT JOIN LATERAL(
					select
						string_agg(cast(MSR2.LpuRegion_id as varchar), ',') as LpuRegion_mids
					from
						v_MedStaffRegion MSR2 
					where
						MSR2.MedStaffFact_id = MSF.MedStaffFact_id
						and COALESCE(MSR2.MedStaffRegion_isMain,1) = 2
				) MSRF2 on true
				left join v_MedStaffFactCache MSFC  on MSFC.MedStaffFact_id = MSF.MedStaffFact_id and MSFC.Server_id = MSF.Server_id
                LEFT JOIN nsi.MedPost MPost ON MPost.MedPost_id = p.MedPost_id
				" . $join . "
			where " . $filter_st . "
				" . (count($dispContractFilterList) > 0 ? "and " . implode(' and ', $dispContractFilterList) : "") . "
				and MSF.Lpu_id = LDC.Lpu_oid
				and MSF.Lpu_id != (select Lpu_id from mv)
				and (MSF.LpuSection_id = LDC.LpuSection_id or LDC.LpuSection_id is null)
				and LDC.LpuSectionProfile_id in (
					select LpuSectionProfile_id from v_LpuSectionLpuSectionProfile  where LpuSection_id = LS.LpuSection_id
					union all
					select LS.LpuSectionProfile_id
				)
				and MSF.WorkData_begDate is not null
				{$IsDisableInDocFilter}
			 " . $adminpersonal . "
		";
		// Нужны ли сторонние специалисты, если явно указан идентификатор отделения?
		if ( empty($data['LpuSection_id']) &&( empty($data['andWithoutLpuSection']) || $data['andWithoutLpuSection'] != 3 )) {
			$query .= "
				union all
					{$dispcontractquery}
			";
		}

		if ( $data['mode'] == 'dispcontractcombo' ) {
			$query = "
				with mv as (
					select
						cast(:Lpu_id as bigint) as Lpu_id
				)
				". $dispcontractquery . "
				AND MSF.Lpu_id = :Lpu_oid
				order by
					 \"MedPersonal_Fio\"
					,\"LpuSection_Name\"
			";
			// фильтр по заданной МО
			$queryParams['Lpu_oid'] = $data['Lpu_id'];
			// фильтр по контрактам с текущей МО
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		} else {
			$query .= "
				) as MedPersonal
				order by
					 MedPersonal.\"MedPersonal_Fio\"
					,MedPersonal.\"LpuSection_Name\"
			";
		}

		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
				$this->swcache->set("MedStaffFactList_".$data['Lpu_id'], $response, array('ttl'=>1800)); // кэшируем на полчаса
			}
			return $response;
		}
		else {
			return false;
		}
	} // end loadMedStaffFactList()
	
	
	/**
	 * Получение комментария места работы врача (и типа записи)
	 */
	public function getMedStaffFactComment($data) {
		$sql = "
			SELECT
				rtrim(msf.MedStaffFact_Descr) as \"MedStaffFact_Descr\",
				msf.MedStaffFact_updDT as \"MedStaffFact_updDT\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				RecType_id as \"RecType_id\",
				MSF.Lpu_id as \"Lpu_id\",
				COALESCE(msf.MedStaffFact_IsDirRec, 1) as \"MedStaffFact_IsDirRec\",
				l.Org_id as \"Org_id\",
				msf.LpuUnit_id as \"LpuUnit_id\",
				msf.MedPersonal_id as \"MedPersonal_id\"
			from v_MedStaffFact MSF 
				left join v_pmUser u  on u.pmUser_id = msf.pmUser_updID
				left join v_Lpu l  on l.Lpu_id = msf.Lpu_id
			where MedStaffFact_id = :MedStaffFact_id
		";
		$res = $this->db->query(
			$sql,
			array(
				'MedStaffFact_id' => $data['MedStaffFact_id']
			)
		);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffFactEditWindow()
	
	
	/**
	 * Сохранение комментария места работы врача
	 */
	public function saveMedStaffFactComment($data) {
		
		//Редактируем схему persis. Правильно ли?
		$sql = "
			update persis.WorkPlace
			set
				Descr = :MedStaffFact_Descr,
				updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where
				id = :MedStaffFact_id;
			select persis.p_WorkPlace_upd(
			    WorkPlace_id := :MedStaffFact_id, 
			    IsReload := 0);
		";
		
		$res = $this->db->query(
			$sql,
			array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'MedStaffFact_Descr' => $data['MedStaffFact_Descr'],
				'pmUser_id' => $data['pmUser_id']
			)
		);
		
		return array(
			0 => array( 'Error_Msg' => '')
		);
	} //end getMedStaffFactEditWindow()
	
	
	/**
	 * Получение длительности времени приёма врача
	 */
	public function getMedStaffFactDuration($data) {
		$sql = "
			SELECT
				MedStaffFact_PriemTime as \"MedStaffFact_PriemTime\"
			from v_MedStaffFact MSF 
			left join v_pmUser u  on u.pmUser_id = msf.pmUser_updID
			where MedStaffFact_id = :MedStaffFact_id
		";
		$res = $this->db->query(
			$sql,
			array(
				'MedStaffFact_id' => $data['MedStaffFact_id']
			)
		);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffFactEditWindow()
	
	
	/**
	 * Получение ФИО врача, к которому привязан текущий пользователь
	 */
	public function getUserMedPersonalFio($id) {
		if ( empty($id) || !is_numeric($id) ) {
			return '';
		}

		$sql = "
			select Person_Fio as \"Person_Fio\"
			from v_MedPersonal 
			where MedPersonal_id = :MedPersonal_id
			limit 1
		";
		$res = $this->db->query($sql, array('MedPersonal_id' => $id));

		if ( !is_object($res) ) {
			return '';
		}

		$response = $res->result('array');

		if ( is_array($response) && count($response) == 1 && !empty($response[0]['Person_Fio']) ) {
			return trim($response[0]['Person_Fio']);
		}
		else {
			return '';
		}

	} //end getUserMedPersonalFio()
	
	
	/**
	 * Получение списка мест работы доступных для регистратуры
	 */
	public function getMedStaffFactListForReg($data) {
		
		$queryParams = array();
		$join = "";
		
		if (isset($data['LpuUnit_id'])) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "msf.LpuUnit_id = :LpuUnit_id";
			
		}
		if (isset($data['LpuSection_id'])) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
		}
		
		if (isset($data['LpuSectionPid_id'])) {
			$queryParams['LpuSectionPid_id'] = $data['LpuSectionPid_id'];
			$filters[] = "msf.LpuSection_id = :LpuSectionPid_id";
		}

		if (isset($data['MedService_id'])) {
			$join = " left join v_MedServiceMedPersonal msmp  on msmp.MedPersonal_id = msf.MedPersonal_id
					LEFT JOIN LATERAL(select * from v_MedService m  where m.MedService_id = :MedService_id limit 1) ms on true
			 ";
			$queryParams['MedService_id'] = $data['MedService_id'];
			$filters[] = "msmp.MedService_id = :MedService_id";
			$filters[] = "msmp.MedServiceMedPersonal_id is not null";
			$filters[] = "msf.Lpu_id = ms.Lpu_id";
			$filters[] = "COALESCE(msf.LpuBuilding_id,0) = COALESCE(ms.LpuBuilding_id,0)";
			$filters[] = "COALESCE(msf.LpuSection_id,0) = COALESCE(ms.LpuSection_id,0)";
			$filters[] = "COALESCE(msf.LpuUnit_id,0) = COALESCE(ms.LpuUnit_id,0)";
		}
		
		$filters[] = "msf.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		
		$filters[] = "COALESCE(MedStatus_id, '1') = '1' and (COALESCE(msf.RecType_id, 6) != 6)";
		
		$sql = "
			SELECT
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				rtrim(msf.Person_SurName) || ' ' || rtrim(msf.Person_FirName) || COALESCE(' ' || rtrim(msf.Person_SecName), '') as \"MedPersonal_FIO\",
				rtrim(ls.LpuSection_Name) as \"LpuSection_Name\",
				rtrim(ls.LpuSectionProfile_Name) as \"LpuSectionProfile_Name\",
				to_char(cast(msf.WorkData_begDate as timestamp), 'DD.MM.YYYY') as \"MedStaffFact_setDate\",
				to_char(cast(msf.WorkData_endDate as timestamp), 'DD.MM.YYYY') as \"MedStaffFact_disDate\",
				case when msf.WorkData_begDate <= dbo.tzGetDate() and COALESCE(cast(msf.WorkData_endDate as date), '2030-01-01') >= dbo.tzGetDate() then 0 else 1 end as \"isClosed\",
				msf.LpuUnit_id as \"LpuUnit_id\"
			FROM v_MedStaffFact msf 
			left join v_LpuSection ls  on msf.LpuSection_id = ls.LpuSection_id
			{$join}
			".ImplodeWhere($filters)."
			ORDER BY \"MedPersonal_FIO\"
		";
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffFactListForReg()

	/**
	 * Получение данных для экспорта реестра мед персонала в xml
	 */
	function getDataForMPExport($data)
	{
		$queryParams = array();
		$resp = array();
		//$queryParams['MPExportDate'] = $data['MPExportDate'];
		$query = "
			SELECT
			--MedStaffFact.MedPersonal_id,
				COALESCE(Lpu.Lpu_f003mcod,'') AS mcod,--Код МО – место работы
				COALESCE(CAST(MedStaffFact.Medpersonal_id AS varchar),'') AS \"IDDOKT\",--Код медицинского работника
				COALESCE(MedStaffFact.Person_SurName,'') AS \"Famil\",--Фамилия медицинского работника
				COALESCE(MedStaffFact.Person_FirName,'') AS \"NAME\",--Имя медицинского работника
				COALESCE(MedStaffFact.Person_SecName,'') AS \"Ot\",--Отчество медицинского работника
				COALESCE(PersonState.Person_Snils,'') AS \"SNILS\",--СНИЛС медицинского работника
				COALESCE(LpuSection.LpuSection_Code,'') AS \"LPU_1\",--Код подразделения МО, в котором медицинский работник осуществляет деятельность
				1 AS \"PR_VR\",--Форма трудовых отношений
				CASE WHEN MedStaffFact.PostKind_id=1 THEN 2 ELSE 1 END AS \"Middle_VR\",--Признак среднего медперсонала
				COALESCE(CAST(LpuSectionProfileFED.LpuSectionProfile_Code AS varchar),'') AS \"Profil\",--Профиль отделения, в котором закреплен медицинский работник
				to_char(MedStaffFact.WorkData_begDate, 'DD.MM.YYYY') AS \"DateBeg\",--Дата включения в реестр
				to_char(MedStaffFact.WorkData_endDate, 'DD.MM.YYYY') AS \"DateEnd\",--Дата исключения из реестра
				--LpuSection.LpuSection_id,
				--LpuSection.LpuSectionProfile_id
				COALESCE(CAST(Sertificate.Docum AS varchar),'') AS \"Docum\",--Тип документа об образовании
				COALESCE(Sertificate.SpecSert,'') AS \"SpecSert\",--Специальность медицинского работника по диплому/сертификату
				COALESCE(Sertificate.SerSert,'') AS \"SerSert\",--Серия диплома/сертификата
				COALESCE(Sertificate.NumSert,'') AS  \"NumSert\",--Номер диплома/сертификата
				case when Docum = 1 THEN CAST(date_part('year',Sertificate.Sert_DN) AS VARCHAR) ELSE to_char(Sertificate.Sert_DN, 'YYYY-MM-DD HH24:MI:SS') end \"Sert_DN\",
				to_char(Sertificate.Sert_DK, 'DD.MM.YYYY') AS \"Sert_DK\" --Дата окончания действия сертификата

			FROM dbo.v_MedStaffFact AS MedStaffFact 
			--Сведения о враче
			INNER JOIN dbo.v_lpu AS Lpu  ON Lpu.Lpu_id = MedStaffFact.Lpu_id
			INNER JOIN dbo.v_PersonState as PersonState  ON PersonState.Person_id = MedStaffFact.Person_id
			LEFT JOIN dbo.v_LpuSection AS LpuSection  ON LpuSection.LpuSection_id = MedStaffFact.LpuSection_id
			LEFT JOIN dbo.v_LpuSectionProfile AS LpuSectionProfile  ON LpuSectionProfile.LpuSectionProfile_id = LpuSection.LpuSectionProfile_id
			LEFT JOIN fed.v_LpuSectionProfile AS LpuSectionProfileFED  ON LpuSectionProfileFED.LpuSectionProfile_id = LpuSectionProfile.LpuSectionProfile_fedid
			--Данные об образовании и специализации
			LEFT JOIN
			(
				SELECT Certificate.MedWorker_id,2 AS Docum,Speciality.name AS SpecSert,Certificate.CertificateSeries AS SerSert,Certificate.CertificateNumber AS NumSert,CAST(Certificate.CertificateReceipDate AS DATE)as Sert_DN, NULL::timestamp AS Sert_DK
				FROM persis.Certificate AS Certificate 
				left JOIN persis.Speciality AS Speciality  ON Speciality.id = Certificate.Speciality_id
				UNION ALL
				SELECT SpecialityDiploma.MedWorker_id,1 AS Docum,DiplomaSpeciality.name AS SpecSert,SpecialityDiploma.DiplomaSeries asSerSert,SpecialityDiploma.DiplomaNumber AS NumSert, CAST('01-' || '01-' || CAST(SpecialityDiploma.YearOfGraduation AS VARCHAR) AS DATE) AS Sert_DN, NULL Sert_DKAS
				FROM persis.SpecialityDiploma AS SpecialityDiploma 
				LEFT JOIN persis.DiplomaSpeciality AS DiplomaSpeciality  ON DiplomaSpeciality.id = SpecialityDiploma.DiplomaSpeciality_id
			) as Sertificate ON Sertificate.MedWorker_id=MedStaffFact.MedPersonal_id
			WHERE
			--тестовые ЛПУ
			MedStaffFact.Lpu_id NOT IN (100,101)
			--проверка ОМС
			AND MedStaffFact_IsOMS =2
			--Список должностей 1- врач, 6 - средний мед.перс.
			AND MedStaffFact.PostKind_id IN (1,6)
			AND COALESCE(MedStaffFact.MedStaffFact_Stavka,0) > 0
			AND Lpu.Lpu_endDate is null
			AND Lpu.Lpu_OmsBegDate is not null
			AND (Lpu.Lpu_OmsEndDate is null or Lpu.Lpu_OmsEndDate <= dbo.tzgetDate())
			--AND Lpu_f003mcod >593102
			--AND Lpu_f003mcod<=594101
			ORDER BY Lpu_f003mcod,Medpersonal_id,WorkData_begDate
		";
		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$omsDoctor = $result->result('array');
		$resp['omsDoctor'] = $omsDoctor;
		return $resp;
	}

	/**
	 * Получение данных для экспорта информации по наличию сертификатов мед персонала в xml
	 */
	function exportMedCert2XML($data)
	{
		$queryParams = array();
		$filter ='';
		$join ='';
		$date_range = '';
		if ($data['Lpu_id'] == 'all') {
			$join .= ' inner join fed.v_PasportMO P  on P.Lpu_id = MP.Lpu_id and COALESCE(P.PasportMO_IsNoFRMP,1) = 1';


		} else {
			$filter .= ' and MP.Lpu_id = :Lpu_id';
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		
		if($data['Date_range'][0] && $data['Date_range'][1]){
		    $date_range .= " and cast(C.CertificateReceipDate as date) <= '".$data['Date_range'][1]."'";
		    $date_range .= " and cast(DATEADD('YEAR',5,C.CertificateReceipDate) as date) >= '".$data['Date_range'][0]."'";
		}
		
		$query = "
			select
				SUBSTRING(MP.Person_Snils,1,3) || '-' || SUBSTRING(MP.Person_Snils,4,3) || '-' || SUBSTRING(MP.Person_Snils,7,3) || '-' || SUBSTRING(MP.Person_Snils,10,2) as \"SS\",
				MP.Person_SurName as \"F\",
				MP.Person_FirName as \"I\",
				MP.Person_SecName as \"O\",
				to_char(MP.Person_BirthDay, 'DD.MM.YYYY')  as \"DR\",
				case
					when PS.Sex_id = 1 then 'М'
					when PS.Sex_id = 2 then 'Ж'
					else ''
				end as \"SEX\",
				C.Speciality_id as \"PROFIL\",
				to_char(C.CertificateReceipDate, 'DD.MM.YYYY') as \"DAT_B\",
				to_char(DATEADD('YEAR',5,C.CertificateReceipDate), 'DD.MM.YYYY') as \"DAT_E\"
			from
				persis.Certificate C 
				INNER JOIN LATERAL (
					select 
						MedPersonal_id,
						Person_SurName,
						Person_FirName,
						Person_SecName,
						Person_Snils, 
						Person_id, 
						Lpu_id, 
						Person_BirthDay
					from
						dbo.v_MedPersonal MP 
					where
						C.MedWorker_id = MedPersonal_id {$filter}
						limit 1
				) MP on true
				INNER JOIN LATERAL (
					select 
						Person_id, Sex_id
					from
						dbo.v_PersonState 
					where
						MP.Person_id = Person_id
						limit 1
				) PS on true
				{$join}
			where
				MP.Person_Snils is not null
				and C.CertificateReceipDate is not null
				and C.Speciality_id is not null
				{$date_range}
				{$filter}
		";

		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$response = Array();
		$response['MED_PERS'] = $result->result('array');

		$query = "
			select
				case
					when C.Speciality_id is null then 'Специальность по диплому не заполнена ' || MP.Person_FIO || ' ' || to_char(MP.Person_BirthDay, 'DD.MM.YYYY')
					when C.CertificateReceipDate is null then 'Дата начала действия сертификата не заполнена ' || MP.Person_FIO || ' ' || to_char(MP.Person_BirthDay, 'DD.MM.YYYY')
				end as \"MP_info\"
			from
				persis.Certificate C 
				INNER JOIN LATERAL (
					select 
						MedPersonal_id,
						Person_Snils, 
						Person_FIO, 
						Person_id, 
						Lpu_id, 
						Person_BirthDay
					from
						dbo.v_MedPersonal MP 
					where
						C.MedWorker_id = MedPersonal_id {$filter}
						limit 1
				) MP on true
				INNER JOIN LATERAL (
					select 
						Person_id, Sex_id
					from
						dbo.v_PersonState 
					where
						MP.Person_id = Person_id
						limit 1
				) PS on true
				{$join}
			where
				(C.Speciality_id is null or C.CertificateReceipDate is null)
				{$filter}
		";

		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$response['ERRORS'] = $result->result('array');
		return $response;
	}

	/**
	 * Получение идентификатора рабочего места
	 */
	function getMedStaffFactId($data) {
		$params = array(
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuSection_id' => $data['LpuSection_id']
		);

		$res = $this->getFirstResultFromQuery("
			select 
				MedStaffFact_id as \"MedStaffFact_id\"
			from v_MedStaffFact 

			where
				MedPersonal_id = :MedPersonal_id
				and LpuSection_id = :LpuSection_id
			order by
				COALESCE(WorkData_endDate, dbo.tzGetDate()) desc
				limit 1

		", $params);

		return [
			'MedStaffFact_id' => $res
		];
	}

	/**
	 * Получение ФИО по идентификатору места работы врача
	 */
	function getFioFromMedPersonal($data) {
		$res = $this->queryResult("
			select 
				Person_Fin as \"Person_Fin\",
				Person_SurName as \"Person_SurName\",
				Person_FirName as \"Person_FirName\",
				Person_SecName as \"Person_SecName\"
			from v_MedPersonal 

			where MedPersonal_id = :MedPersonal_id
			limit 1
			", $data);

		return isset($res[0]) ? $res[0] : '';
	}

	/**
	 * Возвращает список отделений, со всех мест работы врача в МО
	 */
	function loadLpuSectionList($data) {
		$params = array(
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$query = "
			select distinct
				t.LpuSection_id as \"LpuSection_id\"
			from (
				select
					MSF.LpuSection_id
				from v_MedStaffFact MSF 

				where
					MSF.MedPersonal_id = :MedPersonal_id
					and MSF.LpuSection_id is not null
					and MSF.Lpu_id = :Lpu_id
				union
				select
					MSF.LpuSection_id
				from v_MedStaffFactLink MSFL 
					inner join v_MedStaffFact MSF  on MSF.MedStaffFact_id = MSFL.MedStaffFact_id
					inner join v_MedStaffFact sMSF  on sMSF.MedStaffFact_id = MSFL.MedStaffFact_sid
				where
					sMSF.MedPersonal_id = :MedPersonal_id
					and MSF.LpuSection_id is not null
					and MSF.Lpu_id = :Lpu_id
					and (MSFL.MedStaffFactLink_begDT <= dbo.tzGetDate() or MSFL.MedStaffFactLink_begDT is null)
					and (MSFL.MedStaffFactLink_endDT > dbo.tzGetDate() or MSFL.MedStaffFactLink_endDT is null)
			) t
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Выгрузка регистра медработников для ТФОМС
	 */
	function exportMedPersonalToXMLFRMP($data)
	{
        $this->db->query_timeout = 1200000;
        $Lpu_ids = explode(',', $data['Lpu_ids']);
        $exportMode = 'all_in_one'; // Все в один файл

        if ( $this->regionNick == 'kareliya' ) {
            $exportMode = 'each_mo_in_different_file'; // Каждая МО в отдельном файле
        }
        else if ( !empty($data['Lpu_id']) ) {
            $exportMode = 'one_mo'; // Одна МО
        }
        $and_date_WorkPlace = '';
        $and_date_Person = '';
        $and_date_MedWorker = '';
        $or_date_filter = '';
        if(!empty($data['date_from'])){
            $and_date_WorkPlace = ' and WorkPlace.updDT >= :date_from';
            $and_date_Person = ' and Person.PersonState_updDT >= :date_from';
            $and_date_MedWorker = ' and mw.updDT >= :date_from';
            $or_date_filter = "
				or (MedWorker.MedWorker_id in (
							select MedWorker_id from persis.v_WorkPlace WorkPlace 
							left join persis.v_Staff Staff  on WorkPlace.Staff_id = Staff.id
							inner join persis.Staff StaffTable  on Staff.id = StaffTable.id
							left join persis.Post Post  on Staff.Post_id = Post.id
							where (1 = 1)
								and WorkPlace.Lpu_id = Lpu.Lpu_id
								and COALESCE(StaffTable.IsDummyStaff,0) = 0 --исключаем фиктивные строки штатки

								and WorkPlace.Rate > 0.2
								and Post.frmpEntry_id is not null
				)
				{$and_date_Person} 
				)
				or (MedWorker.MedWorker_id in (
							select mw.MedWorker_id from persis.v_WorkPlace WorkPlace 
							inner join persis.v_MedWorker mw  on mw.MedWorker_id = WorkPlace.MedWorker_id
							left join persis.v_Staff Staff  on WorkPlace.Staff_id = Staff.id
							inner join persis.Staff StaffTable  on Staff.id = StaffTable.id
							left join persis.Post Post  on Staff.Post_id = Post.id
							where (1 = 1)
								and WorkPlace.Lpu_id = Lpu.Lpu_id
								and COALESCE(StaffTable.IsDummyStaff,0) = 0 --исключаем фиктивные строки штатки

								and WorkPlace.Rate > 0.2
								and Post.frmpEntry_id is not null
								{$and_date_MedWorker}
						)
					)
				
			";
        }
        $query = "
			select
				l.Lpu_id as \"Lpu_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				p.PasportMO_IsNoFRMP as \"PasportMO_IsNoFRMP\",
				/*COALESCE(l.Org_Code,0) as Lpu_Code*/

				case when (l.Lpu_f003mcod is null or l.Lpu_f003mcod = '') 
					then l.Lpu_id::text else l.Lpu_f003mcod end as \"Lpu_Code\"
			from v_Lpu l 

				LEFT JOIN LATERAL (

					select PasportMO_IsNoFRMP
					from fed.v_PasportMO 

					where Lpu_id = l.Lpu_id
					limit 1
				) p on true
		";
        $queryParams = array();

        if ( !empty($data['Lpu_id']) ) {
            $query .= "where l.Lpu_id = :Lpu_id";
            $queryParams['Lpu_id'] = $data['Lpu_id'];
        } elseif(!empty($Lpu_ids[0])) {
            $query .= "where l.Lpu_id in(" . implode(',', $Lpu_ids) . ")";
        }

        $lpuArray = $this->queryResult($query, $queryParams);
        if ( $lpuArray === false ) {
            return array('success' => false, 'Error_Msg' => 'Ошибка при получении данных МО!');
        }
        else if ( !empty($data['Lpu_id']) && $lpuArray[0]['PasportMO_IsNoFRMP'] == 2 ) {
            return array('success' => false, 'Error_Msg' => 'Выбранная МО не учитывается при выгрузке для ФРМР!');
        }

        $path = EXPORTPATH_ROOT."medpersonal_data_frmr/";
        if (!file_exists($path)) {
            mkdir( $path );
        }

        $out_dir = "re_xml_".time()."_"."medpersonalDataFrmr";
        if (!file_exists($path.$out_dir)) {
            mkdir( $path.$out_dir );
        }

        $file_zip_sign = 'medpersonal_data_frmr';
        $file_zip_name = $path.$out_dir."/".$file_zip_sign.".zip";

        $fileList = array();

        $zip = new ZipArchive();
        $zip->open($file_zip_name, ZIPARCHIVE::CREATE);

        if ( $exportMode == 'all_in_one' ) {
            $file_name = "XML_" . time() . "-ALL";
            $file_path = $path.$out_dir."/".$file_name.".xml";

            $fileList[] = $file_path;

            $xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n<ArrayOfEmployee xmlns=\"http://service.rosminzdrav.ru/MedStaff\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"2.0.11\" formatVersion=\"2.0\" system=\"ARM\">";
            file_put_contents($file_path, $xml);
            unset($xml);
        }
        $data_exists = 0;
		$medSpecOmsJoin = 'left join dbo.v_MedSpecOms mso on mso.MedSpecOms_Code = cast(Speciality.frmpEntry_id as varchar(50))';

		$accreditationJoin = '';
        //$specialityField = 'Speciality.frmpEntry_id';
        $specialityField = 'mso.MedSpecClass_id';
        $specialityDiplomaEducationField = 'mso.MedSpecClass_id';
        $specialityAccreditationField = 'mso.MedSpecClass_id';
        $specialityAccreditationNameField = 'msc.MedSpecClass_Name';

        $institutionID = ' EducationInstitution.frmpEntry_id ';
        if (getRegionNick() == 'kareliya') {
            $accreditationJoin .= ' left join nsi.v_FRMRSpeciality frmrs  on frmrs.id = DiplomaSpeciality.Frmr_id';

            $specialityDiplomaEducationField = 'Speciality.code';
            $specialityAccreditationField = 'DiplomaSpeciality.Frmr_id';
            $specialityAccreditationNameField = 'frmrs.name';
            $institutionID = ' EducationInstitution.code ';
        }

        // сведения по аккредитации сотрудников
        $accreditationList = "
			,(
				select  xmlelement(name AccreditationList, xmlagg( xmlelement(name Accreditation,
				xmlelement(name DiplomaSerie, Accreditation.DocumentSeries ),
				xmlelement(name DiplomaNumber, Accreditation.DocumentNumber),
				xmlelement(name DiplomaRegNumber, Accreditation.RegNumber ),
				xmlelement(name VID ,xmlelement(name ID,AccreditationType.id ),	xmlelement(name Name, AccreditationType.name )),
				xmlelement(name Speciality,xmlelement(name ID, {$specialityAccreditationField} ),xmlelement(name Name, {$specialityAccreditationNameField} )),
				xmlelement(name ProfStandard,  xmlelement(name ID,ProfStandard.id ),	xmlelement(name Name,ProfStandard.name )),
				xmlelement(name Institution,xmlelement(name  ID, {$institutionID} ),xmlelement(name Name,EducationInstitution.name )),
				xmlelement(name IssueDate,to_char(Accreditation.PassDate, 'YYYY-MM-DD HH24:MI:SS') ))))
				from persis.Accreditation Accreditation 
				left join persis.AccreditationType AccreditationType  on AccreditationType.id = Accreditation.AccreditationType_id
				left join persis.Certificate Certificate  on Certificate.MedWorker_id = Accreditation.MedWorker_id
				left join persis.Speciality Speciality  on Speciality.id = Certificate.Speciality_id  
				left join persis.EducationInstitution EducationInstitution  on EducationInstitution.id = Accreditation.EducationInstitution_id
				left join persis.ProfStandard ProfStandard  on ProfStandard.id =  Accreditation.ProfStandard_id
				left join persis.DiplomaSpeciality DiplomaSpeciality  on Accreditation.DiplomaSpeciality_id = DiplomaSpeciality.id
				{$medSpecOmsJoin}
				{$accreditationJoin}
				left join fed.v_MedSpecClass msc  on msc.MedSpecClass_id = mso.MedSpecClass_id
				where Accreditation.MedWorker_id = MedWorker.MedWorker_id
				
			)
		";

        foreach ( $lpuArray as $lpu ) {
            if ( $lpu['PasportMO_IsNoFRMP'] == 2 ) {
                continue;
            }

            if ( $exportMode == 'each_mo_in_different_file' || $exportMode == 'one_mo' ) {
                if ( $this->regionNick == 'kareliya' ) {
                    // Убираем все символы, кроме букв, цифр, пробела и точки
                    $lpu['Lpu_Nick'] = preg_replace("/[^\w\d \.]/ui", "", $lpu['Lpu_Nick']);
                    if($lpu['Lpu_Code'] == 0)
                        $partname = $lpu['Lpu_Nick'];
                    else
                        $partname = $lpu['Lpu_Code'];
                    //$file_name = $lpu['Lpu_Nick'] . '_' . date('d-m-Y') . '_' . $lpu['Lpu_id'];
                    $file_name = 'ФРМП_' . $partname . '_' .$data['on_date'];
                }
                else {
                    $file_name = "XML_" . time() . "-" . $lpu['Lpu_id'];
                }

                $file_temp_name = "XML_" . time() . "-" . $lpu['Lpu_id'];

                $file_path = $path.$out_dir."/".$file_temp_name.".xml";

                $fileList[] = $file_path;

                $xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n<ArrayOfEmployee xmlns=\"http://service.rosminzdrav.ru/MedStaff\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"2.0.11\" formatVersion=\"2.0\" system=\"ARM\">";
                file_put_contents($file_path, $xml);
                unset($xml);
            }

            $query = "
				select
				(
					select  xmlagg(xmlelement(name Employee,
					(select
						 xmlelement(name AddressList, xmlagg( xmlelement(name Address,
						xmlelement (name Apartment, rtrim(ltrim(Address_Flat))) ,
						xmlelement (name Building,rtrim(ltrim(Address_Corpus))),
						xmlelement (name House,rtrim(ltrim(Address_House))) ,
						xmlelement (name Location,xmlelement (name ID,rtrim(ltrim(KLArea.KLAdr_Code)))) ,
						xmlelement (name PostIndex,rtrim(ltrim(Address_Zip))) ,
						xmlelement (name Street,rtrim(ltrim(KLStreet_Name))),
						xmlelement (name Housing,null),
						xmlelement (name RegistrationDateEnd,null ),
						xmlelement (name Registration ,xmlelement(name ID ,1)))))
						from v_Address Address 
						left join v_KLArea KLArea  on coalesce(Address.KLTown_id, Address.KLCity_id, Address.KLSubRgn_id, Address.KLRgn_id) = KLArea.KLArea_id
						left join v_KLStreet KLStreet  on Address.KLStreet_id = KLStreet.KLStreet_id
						where Address_id = COALESCE(Person.PAddress_id, Person.UAddress_id)
					),
					(select  xmlelement(name AwardList, xmlagg( xmlelement(name Award,
						xmlelement(name AwardNomination, Reward.FRMPNomination_id ),
						xmlelement(name Issued, Reward.date ),
						xmlelement(name Name, Reward.name ),
						xmlelement(name Number, Reward.number ))))
						from persis.Reward Reward 
						where Reward.MedWorker_id = MedWorker.MedWorker_id
						
					),
					(
						select xmlelement(name CardRecordList, xmlagg( xmlelement(name CardRecord,
						xmlelement(name AdditionalLaborAgreement ,xmlelement(name ID,AdditionalLaborAgreement)),
						xmlelement(name Care,xmlelement(name ID,Care )),
						xmlelement(name Conditions,xmlelement(name ID,Conditions)),
						xmlelement(name DateBegin,DateBegin ),
						xmlelement(name DateEnd,DateEnd ),
						xmlelement(name IsVacation,IsVacation),
						xmlelement(name Military,xmlelement(name ID,Military )),
						xmlelement(name OrderIn,OrderIn ),
						xmlelement(name OrderIn,OrderOut ),
						xmlelement(name Organization, xmlelement(name Name, OrganizationName),	xmlelement(name OID, OrganizationOid)),
						xmlelement(name Population, Population ),
						xmlelement(name PositionType,xmlelement(name ID,PositionType)),
						xmlelement(name Post,xmlelement(name ID,Post ),	xmlelement(name PostName)),
						xmlelement(name PostType,xmlelement(name ID,PostType )),
						xmlelement(name Regime,xmlelement(name ID,Regime )),
						xmlelement(name SubdivisionName ,SubdivisionName),
						xmlelement(name SubdivisionType,xmlelement(name ID,SubdivisionType )),
						xmlelement(name TypeIn,xmlelement(name ID,TypeIn )),
						xmlelement(name TypeInAdd,xmlelement(name ID,TypeInAdd)),
						xmlelement(name TypeInGoIn,xmlelement(name ID,TypeInGoIn)),
						xmlelement(name TypeOut,xmlelement(name ID,TypeOut )),
						xmlelement(name TypeOutDel,xmlelement(name ID,TypeOutDel )),
						xmlelement(name TypeOutGoIn,xmlelement(name ID,TypeOutGoIn )),
						xmlelement(name Wage,Wage ))))
						from (
							select
							2 as AdditionalLaborAgreement,
							COALESCE(MedicalCareKind_Code, 1) as Care,
							case
								LpuUnit.LpuUnitType_id
								when 2 then 1
								when 1 then 2
								when 13 then 3
								when 9 then 4
								when 6 then 5
								when 7 then 6
								else 7
							end as Conditions,
							to_char(WorkPlace.BeginDate, 'YYYY-MM-DD HH24:MI:SS') as DateBegin,
							to_char(WorkPlace.EndDate, 'YYYY-MM-DD HH24:MI:SS') as DateEnd,
							null as IsVacation,
							MilitaryRelation.code as Military,
							WorkPlace.ArriveOrderNumber as OrderIn,
							WorkPlace.LeaveOrderNumber as OrderOut,
							Lpu.Lpu_Name as OrganizationName,
							PassportToken.PassportToken_tid as OrganizationOid,
							WorkPlace.Population as Population,
							PostOccupationType.code as PositionType,
							COALESCE(CAST(Post.frmpEntry_id AS varchar), '') as Post,

							FRMPPost.name as PostName,
							PostKind.code as PostType,
							WorkMode.frmpEntry_id as Regime,
							--LpuBuilding.LpuBuilding_Name as SubdivisionName,
							CASE
								WHEN LpuSection.LpuSection_id IS NOT NULL THEN rtrim(LpuSection.LpuSection_Name)
								WHEN LpuUnit.LpuUnit_id IS NOT NULL THEN rtrim(LpuUnit.LpuUnit_Name)
								WHEN LpuBuilding.LpuBuilding_id IS NOT NULL THEN rtrim(LpuBuilding.LpuBuilding_Name)
							END as SubdivisionName,
							WorkPlace.FRMPSubdivision_id as SubdivisionType,
							WorkPlace.ArriveRecordType_id as TypeIn,
							WorkPlace.ArriveRecordAddType_id as TypeInAdd,
							case COALESCE(WorkPlace.ArriveRecordType_id, 0)

								when 8 then 4 --если \"Движение кадров внутри организации\" то \"Перевод на другую должность\"
								else null
							end as TypeInGoIn,
							WorkPlace.LeaveRecordType_id as TypeOut,
							case
								when WorkPlace.LeaveRecordType_id in (2, 3) then 9
								else null
							end as TypeOutDel,
							case WorkPlace.LeaveRecordType_id
								when 12 then 4 --если \"Движение кадров внутри организации\" то \"Перевод на другую должность\"
								else null
							end as TypeOutGoIn,
							cast(WorkPlace.Rate as numeric(10, 2)) as Wage
							from 
								persis.WorkPlace WorkPlace 
								left join persis.v_Staff Staff  on WorkPlace.Staff_id = Staff.id
								inner join persis.Staff StaffTable  on Staff.id = StaffTable.id
								left join persis.MedicalCareKind MedicalCareKind  on Staff.MedicalCareKind_id = MedicalCareKind.id
								left join nsi.MedicalCareKind FedMedicalCareKind  on MedicalCareKind.MedicalCareKind_fedid = FedMedicalCareKind.MedicalCareKind_id
								left join persis.MilitaryRelation MilitaryRelation  on WorkPlace.MilitaryRelation_id = MilitaryRelation.id
								left join persis.PostOccupationType PostOccupationType  on WorkPlace.PostOccupationType_id = PostOccupationType.id
								left join persis.Post Post  on Staff.Post_id = Post.id
								left join persis.FRMPPost FRMPPost  on FRMPPost.id = Post.frmpEntry_id
								left join persis.PostKind PostKind  on Post.PostKind_id = PostKind.id
								left join persis.WorkMode WorkMode  on WorkPlace.WorkMode_id = WorkMode.id
								left join v_LpuBuilding LpuBuilding  on Staff.LpuBuilding_id = LpuBuilding.LpuBuilding_id
								left join v_LpuUnit LpuUnit  on Staff.LpuUnit_id = LpuUnit.LpuUnit_id
								left join v_LpuSection as LpuSection  on LpuSection.LpuSection_id = Staff.LpuSection_id
							where MedWorker_id = MedWorker.MedWorker_id
								and Staff.Lpu_id = Lpu.Lpu_id
								and COALESCE(StaffTable.IsDummyStaff,0) = 0 --исключаем фиктивные строки штатки
								and WorkPlace.Rate > 0.2
								and Post.frmpEntry_id is not null
							/*union
							select
							2 as AdditionalLaborAgreement,
							COALESCE(MedicalCareKind_Code, 1) as Care,
							case
								LpuUnit.LpuUnitType_id
								when 2 then 1
								when 1 then 2
								when 13 then 3
								when 9 then 4
								when 6 then 5
								when 7 then 6
								else 7
							end as Conditions,
							to_char(SkipPayment.StartDate, 'YYYY-MM-DD HH24:MI:SS') as DateBegin,

							null as DateEnd,
							null as IsVacation,
							MilitaryRelation.code as Military,
							WorkPlace.ArriveOrderNumber as OrderIn,
							WorkPlace.LeaveOrderNumber as OrderOut,
							Lpu.Lpu_Name as OrganizationName,
							PassportToken.PassportToken_tid as OrganizationOid,
							WorkPlace.Population as Population,
							PostOccupationType.code as PositionType,
							COALESCE(CAST(Post.frmpEntry_id as varchar), '') as Post,

							FRMPPost.name as PostName,
							PostKind.code as PostType,
							WorkMode.frmpEntry_id as Regime,
							LpuBuilding.LpuBuilding_Name as SubdivisionName,
							WorkPlace.FRMPSubdivision_id as SubdivisionType,
							8 as TypeIn, --\"Движение кадров внутри организации\"
							null as TypeInAdd,
							3 as TypeInGoIn, --\"Уход в отпуск (ставку будет занимать временный сотрудник)\"
							WorkPlace.LeaveRecordType_id as TypeOut,
							case
								when WorkPlace.LeaveRecordType_id in (2, 3) then 9
								else null
							end as TypeOutDel,
							case WorkPlace.LeaveRecordType_id
								when 12 then 4 --если \"Движение кадров внутри организации\" то \"Перевод на другую должность\"
								else null
							end as TypeOutGoIn,
							cast(WorkPlace.Rate as numeric(10, 2)) as Wage
							from persis.WorkPlace WorkPlace 
							inner join persis.SkipPayment SkipPayment  on WorkPlace.id = SkipPayment.WorkPlace_id and (SkipPayment.EndDate is null or SkipPayment.EndDate >= dbo.tzGetDate())
							left join persis.v_Staff Staff  on WorkPlace.Staff_id = Staff.id
							inner join persis.Staff StaffTable  on Staff.id = StaffTable.id
							left join persis.MedicalCareKind MedicalCareKind  on Staff.MedicalCareKind_id = MedicalCareKind.id
							left join nsi.MedicalCareKind FedMedicalCareKind  on MedicalCareKind.MedicalCareKind_fedid = FedMedicalCareKind.MedicalCareKind_id
							left join persis.MilitaryRelation MilitaryRelation  on WorkPlace.MilitaryRelation_id = MilitaryRelation.id
							left join persis.PostOccupationType PostOccupationType  on WorkPlace.PostOccupationType_id = PostOccupationType.id
							left join persis.Post Post  on Staff.Post_id = Post.id
							left join persis.PostKind PostKind  on Post.PostKind_id = PostKind.id
							left join persis.WorkMode WorkMode  on WorkPlace.WorkMode_id = WorkMode.id
							left join v_LpuBuilding LpuBuilding  on Staff.LpuBuilding_id = LpuBuilding.LpuBuilding_id
							left join v_LpuUnit LpuUnit  on Staff.LpuUnit_id = LpuUnit.LpuUnit_id
							where MedWorker_id = MedWorker.MedWorker_id
							and Staff.Lpu_id = Lpu.Lpu_id
							and COALESCE(StaffTable.IsDummyStaff,0) = 0 --исключаем фиктивные строки штатки
							and WorkPlace.Rate > 0.2
							and Post.frmpEntry_id is not null
							*/
							--==============================================================--
							union
							select
							2 as AdditionalLaborAgreement,
							COALESCE(MedicalCareKind_Code, 1) as Care,
							case
								LpuUnit.LpuUnitType_id
								when 2 then 1
								when 1 then 2
								when 13 then 3
								when 9 then 4
								when 6 then 5
								when 7 then 6
								else 7
							end as Conditions,
							to_char(MoveMedWorker.StartDate, 'YYYY-MM-DD HH24:MI:SS') as DateBegin, --дата начала
							to_char(MoveMedWorker.EndDate, 'YYYY-MM-DD HH24:MI:SS') as DateEnd, --дата окончания
							null as IsVacation,
							MilitaryRelation.code as Military,
							MoveMedWorker.OrderNumIn as OrderIn, --номер приказа начала
							MoveMedWorker.OrderNumOut as OrderOut, -- номер приказа окончания
							Lpu.Lpu_Name as OrganizationName,
							PassportToken.PassportToken_tid as OrganizationOid,
							WorkPlace.Population as Population,
							PostOccupationType.code as PositionType,
							COALESCE(CAST(Post.frmpEntry_id as varchar), '') as Post,
							FRMPPost.name as PostName,
							PostKind.code as PostType,
							WorkMode.frmpEntry_id as Regime,
							--LpuBuilding.LpuBuilding_Name as SubdivisionName,
							CASE
								WHEN LpuSection.LpuSection_id IS NOT NULL THEN rtrim(LpuSection.LpuSection_Name)
								WHEN LpuUnit.LpuUnit_id IS NOT NULL THEN rtrim(LpuUnit.LpuUnit_Name)
								WHEN LpuBuilding.LpuBuilding_id IS NOT NULL THEN rtrim(LpuBuilding.LpuBuilding_Name)
							END as SubdivisionName,
							WorkPlace.FRMPSubdivision_id as SubdivisionType,
							8 as TypeIn, --Тип начала записи \"Движение кадров внутри организации\"
							null as TypeInAdd,
							MoveMedWorker.MoveInOrgRecordType_id as TypeInGoIn, --Движение кадров внутри организации (Начало)
							case COALESCE(MoveMedWorker.MoveOutOrgRecordType_id,0) when 0 then null else 12 end as TypeOut, --Тип окончания записи \"\"Движение кадров внутри организации\"\"
							null as TypeOutDel,
							MoveMedWorker.MoveOutOrgRecordType_id as TypeOutGoIn, --Движение кадров внутри организации (Окончание)
							cast(WorkPlace.Rate as numeric(10, 2)) as Wage
							from 
								persis.WorkPlace WorkPlace 
								inner join persis.MoveMedWorker MoveMedWorker  on MoveMedWorker.WorkPlace_id = WorkPlace.id
								left join persis.v_Staff Staff  on WorkPlace.Staff_id = Staff.id
								inner join persis.Staff StaffTable  on Staff.id = StaffTable.id
								left join persis.MedicalCareKind MedicalCareKind  on Staff.MedicalCareKind_id = MedicalCareKind.id
								left join nsi.MedicalCareKind FedMedicalCareKind  on MedicalCareKind.MedicalCareKind_fedid = FedMedicalCareKind.MedicalCareKind_id
								left join persis.MilitaryRelation MilitaryRelation  on WorkPlace.MilitaryRelation_id = MilitaryRelation.id
								left join persis.PostOccupationType PostOccupationType  on WorkPlace.PostOccupationType_id = PostOccupationType.id
								left join persis.Post Post  on Staff.Post_id = Post.id
								left join persis.FRMPPost FRMPPost  on FRMPPost.id = Post.frmpEntry_id
								left join persis.PostKind PostKind  on Post.PostKind_id = PostKind.id
								left join persis.WorkMode WorkMode  on WorkPlace.WorkMode_id = WorkMode.id
								left join v_LpuBuilding LpuBuilding  on Staff.LpuBuilding_id = LpuBuilding.LpuBuilding_id
								left join v_LpuUnit LpuUnit  on Staff.LpuUnit_id = LpuUnit.LpuUnit_id
								left join v_LpuSection as LpuSection  on LpuSection.LpuSection_id = Staff.LpuSection_id
							where MedWorker_id = MedWorker.MedWorker_id
								and Staff.Lpu_id = Lpu.Lpu_id
								and COALESCE(StaffTable.IsDummyStaff,0) = 0 --исключаем фиктивные строки штатки
								and WorkPlace.Rate > 0.2
								and Post.frmpEntry_id is not null
							--==============================================================--
						) CardRecord
						--for xml path('CardRecord'), root('CardRecordList'), type
					),
					(
						select  xmlelement(name CertificateEducationList, xmlagg( xmlelement(name CertificateEducation,
						xmlelement(name CertificateNumber,Certificate.CertificateNumber),
						xmlelement(name CertificateSerie,Certificate.CertificateSeries),
						xmlelement(name Institution,xmlelement(name ID,COALESCE(cast(EducationInstitution.frmpEntry_id as varchar), ''))),
						xmlelement(name IssueDate, to_char(Certificate.CertificateReceipDate, 'YYYY-MM-DD HH24:MI:SS')),
						xmlelement(name Speciality,xmlelement(name ID,{$specialityField} )))))
						from persis.Certificate Certificate 
						left join persis.EducationInstitution EducationInstitution  on Certificate.EducationInstitution_id = EducationInstitution.id
						left join persis.Speciality Speciality  on Certificate.Speciality_id = Speciality.id
						{$medSpecOmsJoin}
						where MedWorker_id = MedWorker.MedWorker_id
						--for xml path('CertificateEducation'), root('CertificateEducationList'), type
					),
					(
						select xmlelement(name DiplomaEducationList, xmlagg( xmlelement(name DiplomaEducation,
						xmlelement(name Aim,'false' ),
						xmlelement(name DiplomaSerie,SpecialityDiploma.DiplomaSeries ),
						xmlelement(name DiplomaNumber,SpecialityDiploma.DiplomaNumber) ,
						xmlelement(name Institution,xmlelement(name ID,COALESCE(cast(EducationInstitution.frmpEntry_id as varchar), '') )),
						xmlelement(name Speciality,xmlelement(name ID,{$specialityDiplomaEducationField} )),
						xmlelement(name TerritoryRf,xmlelement(name ID,(select left(KLAdr_Ocatd, 2) from v_KLArea  where KLArea_id = dbo.GetRegion()) )),
						xmlelement(name Type,xmlelement(name ID,cast(EducationType.frmpEntry_id as varchar) )),
						xmlelement(name YearGraduation,SpecialityDiploma.YearOfGraduation ))))
						from persis.SpecialityDiploma SpecialityDiploma 
						left join persis.EducationInstitution EducationInstitution  on SpecialityDiploma.EducationInstitution_id = EducationInstitution.id
						left join persis.DiplomaSpeciality Speciality  on SpecialityDiploma.DiplomaSpeciality_id = Speciality.id
						left join persis.EducationType EducationType  on SpecialityDiploma.EducationType_id = EducationType.id
						{$medSpecOmsJoin}
						where MedWorker_id = MedWorker.MedWorker_id
						--for xml path('DiplomaEducation'), root('DiplomaEducationList'), type
					),
					xmlelement(name Document,
				 	xmlelement(name INN,Person.Person_Inn ),
					xmlelement(name Issued,COALESCE(OrgDep.OrgDep_Name, ' ') ),
					xmlelement(name IssueDate,to_char(Document.Document_begDate, 'YYYY-MM-DD HH24:MI:SS') ),
					xmlelement(name Number,Document.Document_Num ),
					xmlelement(name Serie,COALESCE(Document.Document_Ser, ' ') ),
					xmlelement(name SNILS,substring(Person.Person_Snils, 1, 3) || '-' || substring(Person.Person_Snils, 4, 3) || '-' || substring(Person.Person_Snils, 7, 3) || '-' || right(Person.Person_Snils, 2) ),
					xmlelement(name TabelNumber,TabCode.TabCode ),
					xmlelement(name Type,xmlelement(name ID,DocumentType.DocumentType_Code))),
					
					xmlelement(name Extended,
					xmlelement(name CitezenshipState,xmlelement(name ID,case
						when Document.DocumentType_id between 1 and 18 then 1
						when NS.NationalityStatus_IsTwoNation = 2 then 2
						when Document.DocumentType_id = 19 then 3
						when Document.DocumentType_id = 20 then 4
					end  )),
					xmlelement(name MarriageState,xmlelement(name ID,case
						when Person.PersonFamilyStatus_IsMarried = 2 then 2
						else FamilyStatus_id
					end)),
					xmlelement(name Phone,Person.Person_Phone ),
					xmlelement(name HasAuto,'false'),
					xmlelement(name HasChildren,'false' )),

					xmlelement(name FIO,Person.Person_SurName || ' ' || Person.Person_FirName || ' ' || Person.Person_SecName ),
					
					xmlelement(name General,
					xmlelement(name Birthdate,to_char(Person.Person_BirthDay, 'YYYY-MM-DD HH24:MI:SS') ),
					xmlelement(name Deathdate,to_char(Person.Person_deadDT, 'YYYY-MM-DD HH24:MI:SS')),
					xmlelement(name Name,Person.Person_FirName ),
					xmlelement(name Patroname,Person.Person_SecName ),
					xmlelement(name Sex,case when Person.Sex_id = 1 then 'Male' when Person.Sex_id = 2 then 'Female' end ),
					xmlelement(name Surname,Person.Person_SurName )),

					xmlelement(name Organization,xmlelement(name Name,Lpu.Lpu_Name ),
					xmlelement(name OID,PassportToken.PassportToken_tid )),

					xmlelement(name TimeFactList,null ),
					xmlelement(name VocationList,null),
					(
						select xmlelement(name PostGraduateEducationList, xmlagg( xmlelement(name PostGraduateEducation,
						xmlelement(name Aim ,'false'),
						xmlelement(name DateBegin,to_char(PostgraduateEducation.startDate, 'YYYY-MM-DD HH24:MI:SS') ),
						xmlelement(name DateDocum,to_char(PostgraduateEducation.graduationDate, 'YYYY-MM-DD HH24:MI:SS') ),
						xmlelement(name DateEnd,PostgraduateEducation.endDate ),
						xmlelement(name Degree,xmlelement(name ID,AcademicMedicalDegree.code )),
						xmlelement(name DiplomaSerie,PostgraduateEducation.DiplomaSeries ),
						xmlelement(name DiplomaNumber,PostgraduateEducation.DiplomaNumber) ,
						xmlelement(name Institution,xmlelement(name ID,COALESCE(cast(EducationInstitution.frmpEntry_id as varchar), '') )),

						xmlelement(name Speciality,xmlelement(name ID,{$specialityField} )),
						xmlelement(name Type ,xmlelement(name ID,PostgraduateEducationType.code)))))
						from persis.PostgraduateEducation PostgraduateEducation 
						left join persis.AcademicMedicalDegree AcademicMedicalDegree  on PostgraduateEducation.AcademicMedicalDegree_id = AcademicMedicalDegree.id
						left join persis.EducationInstitution EducationInstitution  on PostgraduateEducation.EducationInstitution_id = EducationInstitution.id
						left join persis.Speciality Speciality  on PostgraduateEducation.Speciality_id = Speciality.id
						left join persis.PostgraduateEducationType  on PostgraduateEducation.PostgraduateEducationType_id = PostgraduateEducationType.id
						{$medSpecOmsJoin}
						where MedWorker_id = MedWorker.MedWorker_id
						--for xml path('PostGraduateEducation'), root('PostGraduateEducationList'), type
					),
					(
						select xmlelement(name QualificationList, xmlagg( xmlelement(name Qualification,
						xmlelement(name Category,xmlelement(name ID,Category.code)),
						xmlelement(name DateGet,to_char(QualificationCategory.AssigmentDate, 'YYYY-MM-DD HH24:MI:SS') ),

						xmlelement(name Speciality,xmlelement(name ID,{$specialityField} ))
						)))
						from persis.QualificationCategory QualificationCategory 
						left join persis.Category Category  on QualificationCategory.Category_id = Category.id
						left join persis.Speciality Speciality  on QualificationCategory.Speciality_id = Speciality.id
						{$medSpecOmsJoin}
						where MedWorker_id = MedWorker.MedWorker_id
						--for xml path('Qualification'), root('QualificationList'), type
					),
					(
						select xmlelement(name QualificationList, xmlagg( xmlelement(name Qualification,
						xmlelement(name DiplomaSerie,RetrainingCourse.DocumentSeries ),
						xmlelement(name DiplomaNumber,RetrainingCourse.DocumentNumber) ,
						xmlelement(name Hours,RetrainingCourse.HoursCount ),
						xmlelement(name Institution,xmlelement(name ID,COALESCE(CAST(EducationInstitution.frmpEntry_id AS varchar), '') )),

						xmlelement(name Speciality,xmlelement(name ID,{$specialityField} )),
						xmlelement(name YearPassing,RetrainingCourse.PassYear)
						)))
						from persis.RetrainingCourse RetrainingCourse 

						left join persis.EducationInstitution EducationInstitution  on RetrainingCourse.EducationInstitution_id = EducationInstitution.id

						left join persis.Speciality Speciality  on RetrainingCourse.Speciality_id = Speciality.id

						{$medSpecOmsJoin}
						where MedWorker_id = MedWorker.MedWorker_id
						--for xml path('Retrainment'), root('RetrainmentList'), type
					),
					(
						select xmlelement(name QualificationList, xmlagg( xmlelement(name Qualification,
						xmlelement(name Cycle,QualificationImprovementCourse.Round ),
						xmlelement(name DiplomaSerie,QualificationImprovementCourse.DocumentSeries ),
						xmlelement(name DiplomaNumber,QualificationImprovementCourse.DocumentNumber),
						xmlelement(name Hours,QualificationImprovementCourse.HoursCount),
						xmlelement(name Institution,xmlelement(name ID,COALESCE(CAST(EducationInstitution.frmpEntry_id AS varchar), '') ),

						xmlelement(name IssueDate,to_char(QualificationImprovementCourse.DocumentRecieveDate, 'YYYY-MM-DD HH24:MI:SS')),

						xmlelement(name Speciality,xmlelement(name ID,{$specialityField} )),
						xmlelement(name YearPassing,QualificationImprovementCourse.Year )
						))))
						from persis.QualificationImprovementCourse QualificationImprovementCourse 

						left join persis.EducationInstitution EducationInstitution  on QualificationImprovementCourse.EducationInstitution_id = EducationInstitution.id

						left join persis.Speciality Speciality  on QualificationImprovementCourse.Speciality_id = Speciality.id

						{$medSpecOmsJoin}
						where MedWorker_id = MedWorker.MedWorker_id
						--for xml path('SkillImprovement'), root('SkillImprovementList'), type
					),
					(
						select xmlelement(name QualificationList, xmlagg( xmlelement(name Qualification,
						xmlelement(name DateBegin,to_char(SkipPayment.StartDate, 'YYYY-MM-DD HH24:MI:SS') ),
						xmlelement(name DateEnd,to_char(SkipPayment.EndDate, 'YYYY-MM-DD HH24:MI:SS')) ,
						xmlelement(name Reason,xmlelement(name ID ,SkipPaymentReason.frmpEntry_id ))
						)))
						from persis.SkipPayment SkipPayment 
						inner join persis.WorkPlace WorkPlace  on SkipPayment.WorkPlace_id = WorkPlace.id
						left join persis.SkipPaymentReason SkipPaymentReason  on SkipPayment.SkipPaymentReason_id = SkipPaymentReason.id
						where WorkPlace.MedWorker_id = MedWorker.MedWorker_id
						--for xml path('SkipPayment'), root('SkipPaymentList'), type
					)
					".$accreditationList."
					))
					from persis.v_MedWorker MedWorker 
					inner join v_PersonState Person  on MedWorker.Person_id = Person.Person_id
					left join v_Document Document  on Person.Document_id = Document.Document_id
					left join v_DocumentType DocumentType  on Document.DocumentType_id = DocumentType.DocumentType_id
					left join v_NationalityStatus NS  on Person.NationalityStatus_id = NS.NationalityStatus_id
					left join v_OrgDep OrgDep  on Document.OrgDep_id = OrgDep.OrgDep_id
					LEFT JOIN LATERAL (
					select t1.TabCode from persis.WorkPlace t1 
					where t1.MedWorker_id = MedWorker.MedWorker_id and t1.TabCode is not null
					order by t1.PostOccupationType_id, t1.BeginDate limit 1) TabCode ON true
					where MedWorker.MedWorker_id in (
						select MedWorker_id from persis.v_WorkPlace WorkPlace 
						left join persis.v_Staff Staff  on WorkPlace.Staff_id = Staff.id
						inner join persis.Staff StaffTable  on Staff.id = StaffTable.id
						left join persis.Post Post  on Staff.Post_id = Post.id
						where (1 = 1)
							and WorkPlace.Lpu_id = Lpu.Lpu_id
							and COALESCE(StaffTable.IsDummyStaff,0) = 0 --исключаем фиктивные строки штатки
							and WorkPlace.Rate > 0.2
							and Post.frmpEntry_id is not null
							{$and_date_WorkPlace}
					)
					{$or_date_filter}
					---for xml path('Employee'), type
				)
				from v_Lpu Lpu 
				LEFT JOIN LATERAL (select PassportToken_tid from fed.v_PassportToken  where Lpu_id = Lpu.Lpu_id order by PassportToken_updDT desc limit 1) PassportToken ON true
				where Lpu.Lpu_id = :Lpu_id
			";
            /*
            echo getDebugSQL($query, array(
                'Lpu_id' => $lpu['Lpu_id'],
                'date_from' => $data['date_from']
            ));die;
            */
            $data_xml_arr = $this->queryResult($query, array(
                'Lpu_id' => $lpu['Lpu_id'],
                'date_from' => $data['date_from']
            ));

            $xml = "";
            foreach ($data_xml_arr as $row) {
                foreach ($row as $one) {
                    $xml .= $one;
                }
            }

            if ( empty($xml) ) {
                continue;
            }
            else{
                $data_exists = 1;
            }

            file_put_contents($file_path, toAnsi($xml, true), FILE_APPEND);
			unset($xml);

            if ( $exportMode == 'each_mo_in_different_file' || $exportMode == 'one_mo' ) {
                $xml = "</ArrayOfEmployee>";
                file_put_contents($file_path, $xml, FILE_APPEND);
                unset($xml);

                $zip->AddFile( $file_path, iconv('utf-8', 'cp866', $file_name) . ".xml" );
            }
        }

        if ( $exportMode == 'all_in_one' ) {
            $xml = "</ArrayOfEmployee>";
            file_put_contents($file_path, $xml, FILE_APPEND);
            unset($xml);

            $zip->AddFile( $file_path, iconv('utf-8', 'cp866', $file_name) . ".xml" );
        }

        $zip->close();

        foreach ( $fileList as $row ) {
            if ( file_exists($row) ) {
                unlink($row);
            }
        }

        if ( file_exists($file_zip_name) ) {
            return array('success' => true, 'Link' => $file_zip_name);
        }

        if($data_exists == 0)
            return array('success' => false, 'Error_Msg' => 'Нет данных на указанную дату');

        return array('success' => false, 'Error_Msg' => 'Ошибка создания архива!');
	}

	/**
	 * Выгрузка регистра медработников для ТФОМС
	 * @issues 196665
	 */
	function exportMedPersonalToXMLFRMPStaff($data)
	{
		$this->db->query_timeout = 600000;
		if(!empty($data['Lpu_ids'])){
			$Lpu_ids = explode(',', $data['Lpu_ids']);
		} else {
			$Lpu_ids = '';
		}
		$exportMode = 'all_in_one'; // Все в один файл

		if ( $this->regionNick == 'kareliya' ) {
			$exportMode = 'each_mo_in_different_file'; // Каждая МО в отдельном файле
		}
		else if ( !empty($data['Lpu_id']) ) {
			$exportMode = 'one_mo'; // Одна МО
		}

		$query = "
			select
				l.Lpu_id as \"Lpu_id\",
				l.Lpu_Nick as \"Lpu_Nick\",
				p.PasportMO_IsNoFRMP as \"PasportMO_IsNoFRMP\",
				/*COALESCE(l.Org_Code,0) as Lpu_Code*/

				case when (l.Lpu_f003mcod is null or l.Lpu_f003mcod = '') 
					then l.Lpu_id::text else l.Lpu_f003mcod end as \"Lpu_Code\"
			from v_Lpu l 

				LEFT JOIN LATERAL (

					select PasportMO_IsNoFRMP
					from fed.v_PasportMO 

					where Lpu_id = l.Lpu_id
					limit 1
				) p  on true
		";
		$queryParams = array();

		if ( !empty($data['Lpu_id']) ) {
			$query .= "where l.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$query .= " and COALESCE(p.PasportMO_IsNoFRMP,2) <> 2";
		} elseif(!empty($Lpu_ids[0])) {
			$query .= "where l.Lpu_id in(" . implode(',', $Lpu_ids) . ")";
			$query .= " and COALESCE(p.PasportMO_IsNoFRMP,2) <> 2";
		}
		else
			$query .= "where COALESCE(p.PasportMO_IsNoFRMP,2) <> 2";

		//echo getDebugSQL($query, $queryParams);die;

		$lpuArray = $this->queryResult($query, $queryParams);

		//var_dump($data['Lpu_id']);die;

		if(is_array($lpuArray) && count($lpuArray) == 0)
		{
			return array('success' => false, 'Error_Msg' => 'Выбранные МО не учитываются при выгрузке для ФРМР!');
		}
		else
		{
			if ( $lpuArray === false ) {
				return array('success' => false, 'Error_Msg' => 'Ошибка при получении данных МО!');
			}
			else if ( !empty($data['Lpu_id']) && $lpuArray[0]['PasportMO_IsNoFRMP'] == 2 ) {
				return array('success' => false, 'Error_Msg' => 'Выбранная МО не учитывается при выгрузке для ФРМР!');
			}
		}
		$path = EXPORTPATH_ROOT."medpersonal_data_frmr/";
		if (!file_exists($path)) {
			mkdir( $path );
		}

		$out_dir = "re_xml_".time()."_"."medpersonalDataFrmr";
		if (!file_exists($path.$out_dir)) {
			mkdir( $path.$out_dir );
		}

		$file_zip_sign = 'medpersonal_data_frmr';
		$file_zip_name = $path.$out_dir."/".$file_zip_sign.".zip";

		$fileList = array();

		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);

		if ( $exportMode == 'all_in_one' ) {
			$file_name = "XML_" . time() . "-ALL";
			$file_path = $path.$out_dir."/".$file_name.".xml";

			$fileList[] = $file_path;

			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n<ArrayOfStaffEntry xmlns=\"http://service.rosminzdrav.ru/MedStaff\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"2.0.11\" formatVersion=\"2.0\" system=\"ARM\">";
			file_put_contents($file_path, $xml);
			unset($xml);
		}

		$itemExists = 0;

		foreach ( $lpuArray as $lpu ) {
			if ( $lpu['PasportMO_IsNoFRMP'] == 2 ) {
				continue;
			}

			if ( $exportMode == 'each_mo_in_different_file' || $exportMode == 'one_mo' ) {
				if ( $this->regionNick == 'kareliya' ) {
					// Убираем все символы, кроме букв, цифр, пробела и точки
					$lpu['Lpu_Nick'] = preg_replace("/[^\w\d \.]/ui", "", $lpu['Lpu_Nick']);
					if($lpu['Lpu_Code'] == 0)
						$partname = $lpu['Lpu_Nick'];
					else
						$partname = $lpu['Lpu_Code'];
					$file_name = 'ШР_' . $partname . '_' .$data['on_date'];
					$tmp_file_name = "SS_" . time() . "-" . $lpu['Lpu_id'];
				}
				else {
					$file_name = "XML_" . time() . "-" . $lpu['Lpu_id'];
					$tmp_file_name = $file_name;
				}

				$file_path = $path.$out_dir."/".$tmp_file_name.".xml";

				$fileList[] = $file_path;

				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n<ArrayOfStaffEntry xmlns=\"http://service.rosminzdrav.ru/MedStaff\" xmlns:i=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"2.0.11\" formatVersion=\"2.0\" system=\"ARM\">";
				file_put_contents($file_path, $xml);
				unset($xml);
			}

			$query = "
				select
                (select string_agg(str,'') from
                (select
                '<StaffEntry><Organization><Name>'||Lpu.Lpu_Name||'</Name>'||
                '<OID>'||coalesce(PassportToken.PassportToken_tid,'')||'</OID></Organization>'||
                '<Post><ID>'||COALESCE(cast(Post.frmpEntry_id as varchar), '') || '</ID>'||
                '<Name>'||coalesce(FRMPPost.name,'') ||'</Name></Post>'||
                '<SubdivisionName>'||coalesce(LpuBuilding.LpuBuilding_Name,'') ||'</SubdivisionName>'||
                '<SubdivisionType><ID>'||COALESCE(cast(StaffTable.FRMPSubdivision_id as varchar), '')||'</ID>'||
                '<Name>'||FRMPSubdivision.name ||'</Name></SubdivisionType>'||
                '<TotalWage>'||cast(sum(cast(Staff.Rate as numeric(10, 2))) as varchar)||'</TotalWage></StaffEntry>' as \"str\"
                from persis.Staff Staff 
                inner join persis.Staff StaffTable on Staff.id = StaffTable.id and COALESCE(StaffTable.IsDummyStaff,0) = 0
                left join persis.FRMPSubdivision FRMPSubdivision on FRMPSubdivision.id = StaffTable.FRMPSubdivision_id
                left join persis.Post Post on Staff.Post_id = Post.id
                left join persis.FRMPPost FRMPPost on FRMPPost.id = Post.frmpEntry_id
                left join v_LpuBuilding LpuBuilding on Staff.LpuBuilding_id = LpuBuilding.LpuBuilding_id
                where Staff.Lpu_id = Lpu.Lpu_id
                and (Staff.EndDate is null or Staff.EndDate >= dbo.tzGetDate())
                and Post.frmpEntry_id is not null
                group by cast(Post.frmpEntry_id as varchar), LpuBuilding.LpuBuilding_Name, COALESCE(cast(StaffTable.FRMPSubdivision_id as varchar),'')
                ,FRMPPost.name, FRMPSubdivision.name ) a
                )
                from v_Lpu Lpu 
                LEFT JOIN LATERAL (select PassportToken_tid from fed.v_PassportToken where Lpu_id = Lpu.Lpu_id order by PassportToken_updDT desc limit 1) PassportToken on true
                where Lpu.Lpu_id = :Lpu_id
			";

			/*echo getDebugSQL($query, array(
				'Lpu_id' => $lpu['Lpu_id']
			));*/
			$data_xml_arr = $this->queryResult($query, array(
				'Lpu_id' => $lpu['Lpu_id']
			));

			$xml = "";
			foreach ($data_xml_arr as $row) {
				foreach ($row as $one) {
					$xml .= $one;
				}
			}

			if(!empty($xml))
				$itemExists = 1;
			if ( empty($xml) ) {
				continue;
			}

			file_put_contents($file_path, $xml, FILE_APPEND);
			unset($xml);

			if ( $exportMode == 'each_mo_in_different_file' || $exportMode == 'one_mo' ) {
				$xml = "</ArrayOfStaffEntry>";
				file_put_contents($file_path, $xml, FILE_APPEND);
				unset($xml);

				$zip->AddFile( $file_path, iconv('utf-8', 'cp866', $file_name) . ".xml" );
			}
		}

		if ( $exportMode == 'all_in_one' ) {
			$xml = "</ArrayOfStaffEntry>";
			file_put_contents($file_path, $xml, FILE_APPEND);
			unset($xml);

			$zip->AddFile( $file_path, iconv('utf-8', 'cp866', $file_name) . ".xml" );
		}

		$zip->close();
		//var_dump($fileList);die;
		foreach ( $fileList as $row ) {
			if ( file_exists($row) ) {
				unlink($row);
			}
		}

		if ( file_exists($file_zip_name) ) {
			return array('success' => true, 'Link' => $file_zip_name);
		}

		if($itemExists == 0)
		{
			return array('success' => false, 'Error_Msg' => toUtf('По выбранным МО отсутствуют данные!'));
		}

		return array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива!'));
	}
	
	/**
	 * Метод загрузки фотографии медицинского сотрудника
	 * формирует два файла по пути вида вида: 
	 * uploads/medpersonals/[MedPersonal_id]/photos/[MedPersonal_id].(jpg|png|gif)
	 * uploads/medpersonals/[MedPersonal_id]/photos/thumbs/[MedPersonal_id].(jpg|png|gif)
	 */
	function uploadMedPersonalPhoto($data, $files) {
		/**
		 * Создание каталогов
		 */
		function createDir($path) {
			if(!is_dir($path)) { // Если нет корневой папки для хранения файлов организаций
				// то создадим ее
				$success = mkdir($path, 0777);
				if(!$success) {
					DieWithError('Не удалось создать папку "'.$path.'"');
					return false;
				}
			}
			return true;
		}
		$this->load->helper('Image_helper');
		
		if (!defined('MEDPERSONALSPATH')) {
			return array('success' => false, 'Error_Msg'=>'Необходимо задать константу с указанием каталога для загрузки файлов медсотрудника (config/promed,php): MEDPERSONALSPATH');
		}
		
		$checked = checkImage($files, 'mp_photo');
		if ($checked !== true && is_array($checked)) {
			return $checked;
		}
		
		$source = $files['mp_photo']['tmp_name'];
		// Если файл успешно загрузился в темповую директорию $source
		if(is_uploaded_file($source)) {
			// Наименование файла
			$flname = $files['mp_photo']['name'];
			$fltype = $files['mp_photo']['type'];
			$ext = pathinfo($flname, PATHINFO_EXTENSION);
			if ($data['MedPersonal_id']>0) {
				$name = $data['MedPersonal_id'];
				
				// Создание директорий, если нужно 
				createDir(MEDPERSONALSPATH);
				createDir(MEDPERSONALSPATH.$data['MedPersonal_id']); // Корневой каталог медсотрудника
				$dir = MEDPERSONALSPATH.$data['MedPersonal_id']."/photos/"; // Каталог медсотрудника, где будут лежать фотографии
				createDir($dir);
				createDir($dir."thumbs/"); // Каталог для уменьшенных копий
				
				// удаляем все файлы с таким названием и любым расширением (если они есть)
				array_map("unlink", glob($dir.$name.".*"));
				
				// todo: Здесь можно выбирать имена (например добавляя _1, _2), что даст возможность загружать несколько фотографий 
				
				// Расширение файла 
				$name .= ".".$ext;
				
				// создаем уменьшенную копию изображения
				createThumb($source, $fltype, $dir."thumbs/".$name, 300, 300);
				
				// Перемещаем загруженный файл в директорию пользователя с новым именем
				move_uploaded_file($source, $dir.$name);
				
				return array(
					'success' => true,
					'file_url' => $dir."thumbs/".$name."?t=".time() // добавляем параметр, чтобы не застывал в кеше
				);
			} else {
				return array('success' => false, 'Error_Msg'=>'Не удалось загрузить файл, т.к. медицинский сотрудник не определен!');
			}
		}
		else {
			return array('success' => false, 'Error_Msg'=>'Не удалось загрузить файл!');
		}
	}
	
	/**
	 * Метод чтения фотографии медицинского работника
	 * получает thumbs: 
	 * uploads/medpersonals/[MedPersonal_id]/photos/[MedPersonal_id].(jpg|png|gif)
	 * uploads/medpersonals/[MedPersonal_id]/photos/thumbs/[MedPersonal_id].(jpg|png|gif)
	 */
	function getMedPersonalPhoto($data) {
		if (!defined('MEDPERSONALSPATH')) {
			return false;
		}
		$dir = MEDPERSONALSPATH.$data['MedPersonal_id']."/photos/"; // Директория медсотрудника, где будут лежать фотографии
		if ($data['MedPersonal_id']>0) {
			$name = $data['MedPersonal_id'];
			// ищем файл с нужным расширением и берем первый попавшися
			foreach (glob($dir.$name.".*") as $fn) {
				$ext = pathinfo($fn, PATHINFO_EXTENSION);
				break;
			}
			
			$name .= ".".(isset($ext)?$ext:"jpg");
			$dir .= "thumbs/";
			if (file_exists($dir.$name)) {
				return $dir.$name."?t=".time(); // добавляем параметр, чтобы не застывал в кеше
			}
		}
		return false;
	}

	/**
	 * Метод удаления фотографии медицинского работника
	 * удаляет: 
	 * uploads/medpersonals/[MedPersonal_id]/photos/[MedPersonal_id].(jpg|png|gif)
	 * uploads/medpersonals/[MedPersonal_id]/photos/thumbs/[MedPersonal_id].(jpg|png|gif)
	 */
	function deleteMedPersonalPhoto($data) {
		if (!defined('MEDPERSONALSPATH')) {
			return false;
		}
		$dir = MEDPERSONALSPATH.$data['MedPersonal_id']."/photos/"; // Директория медсотрудника, где будут лежать фотографии
		if ($data['MedPersonal_id']>0) {
			$name = $data['MedPersonal_id'];
			array_map("unlink", glob($dir.$name.".*"));
			
			$dir .= "thumbs/";
			array_map("unlink", glob($dir.$name.".*"));
			return true;
		}
		return false;
	}

	/**
	 * Формирование Кода ДЛО нв основании заявок на изменение (таблица WorkPlace4DloApply)
	 */
	public function treatmentWorkPlace4DloApply($data) {

        $query = "
        SELECT
        error_code as \"Error_Code\",
        error_message as \"Error_Msg\"
        FROM
        p_WorkPlace4DloApply_Treatment(
          WorkPlace4DloApply_id => :WorkPlace4DloApply_id
        )
        ";

		return $this->queryResult($query, array(
			'WorkPlace4DloApply_id' => $data['WorkPlace4DloApply_id']
		));
	}

	/**
	 * Получение идентификатора пациента по врачу
	 * @param $data
	 */
	public function getPersonIdByMedPersonal($data) {
		$resp = $this->queryResult("
			select
				Person_id as \"Person_id\"
			from
				v_MedPersonal
			where
				MedPersonal_id = :MedPersonal_id
		", array(
			'MedPersonal_id' => $data['MedPersonal_id']
		));

		if (!empty($resp[0]['Person_id'])) {
			return $resp[0]['Person_id'];
		} else {
			return null;
		}
	}

	/**
	 * Получить MedPersonal_id на основании MedStaffFact_id.
	 * @param $data
	 * @return mixed|null
	 */
	function getMedPersonalIdByMedStaffFactId($data)
	{
		$sql = "
			select MedStaffFact_id, MedPersonal_id
			from v_MedStaffFact
			where MedStaffFact_id = :MedStaffFact_id
			limit 1
		";
		$resp = $this->queryResult($sql, ['MedStaffFact_id' => $data['MedStaffFact_id']]);
		return (!empty($resp[0]['MedPersonal_id'])) ? $resp[0]['MedPersonal_id'] : null;
	}
}
// END MedPersonal_model class
