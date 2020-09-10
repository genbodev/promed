<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Polka_EvnPLDispTeen14_model - модель для работы с талонами по диспансеризации подростков 14ти лет
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
 * @author       Pshenitcyn Ivan aka IVP (ipshon@gmail.com)
 * @version      01.08.2011
 */
class Polka_EvnPLDispTeen14_model extends SwPgModel
{

    protected $dateTimeForm104 = "'dd.mm.yyyy'";
    protected $dateTimeForm112 = "'yyyymmdd'";

    /**
     * @param $data
     * @return array
     * @throws Exception
     */
    public function deleteEvnPLDispTeen14($data)
    {
        $query = "
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_EvnPLDispTeen14_del
			(
				EvnPLDispTeen14_id := :EvnPLDispTeen14_id,
				pmUser_id := :pmUser_id
			)
		";
        $result = $this->db->query($query, [
            'EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id'],
            'pmUser_id' => $data['pmUser_id']
        ]);

        if (!is_object($result)) {
            throw new Exception('Ошибка при выполнении запроса к базе данных (удаление талона ДД)');
        }

        return $result->result('array');
    }

    /**
     * @param $data
     * @return bool
     */
    public function loadEvnPLDispTeen14EditForm($data)
    {
        $query = "
			SELECT
				PersonEvn_id as \"PersonEvn_id\",
				EPLDT14.EvnPLDispTeen14_id as \"EvnPLDispTeen14_id\",				
				EPLDT14.EvnPLDispTeen14_IsFinish as \"EvnPLDispTeen14_IsFinish\",
				to_char(EPLDT14.EvnPLDispTeen14_setDate, {$this->dateTimeForm104}) as \"EvnPLDispTeen14_setDate\",
				pw.Okei_id as \"Okei_id\",
				pw.PersonWeight_Weight as \"PersonWeight_Weight\",
				coalesce(pw.PersonWeight_IsAbnorm, 1) as \"PersonWeight_IsWeightAbnorm\",
				pw.WeightAbnormType_id as \"WeightAbnormType_id\",
				PersonHeight.PersonHeight_Height as \"PersonChild_Height\",
				coalesce(PersonHeight.PersonHeight_IsAbnorm, 1) as \"PersonChild_IsHeightAbnorm\",
				PersonHeight.HeightAbnormType_id as \"HeightAbnormType_id\",
				PsychicalConditionType_id as \"PsychicalConditionType_id\",
				SexualConditionType_id as \"SexualConditionType_id\",
				DopDispResType_id as \"DopDispResType_id\",
				DispResMedicalMeasureType_id as \"DispResMedicalMeasureType_id\",
				EvnPLDispTeen14_isHTAid as \"EvnPLDispTeen14_isHTAid\",				
				to_char(EPLDT14.EvnPLDispTeen14_HTAidDT, {$this->dateTimeForm104}) as \"EvnPLDispTeen14_HTAidDT\",
				coalesce(InvalidType_id, 1) as \"InvalidType_id\"
			FROM
				v_EvnPLDispTeen14 EPLDT14
				left join lateral (
					select
					    *
					from
						v_PersonHeight
					where
						Evn_id = EPLDT14.EvnPLDispTeen14_id
					order by PersonHeight_insDT desc
					limit 1
				) as PersonHeight on true
				left join lateral (
					select
						*
					from
						v_PersonWeight
					where
						Evn_id = EPLDT14.EvnPLDispTeen14_id
					order by PersonWeight_insDT desc
					limit 1
				) as pw on true
			WHERE
			    EPLDT14.EvnPLDispTeen14_id = :EvnPLDispTeen14_id
            and
				EPLDT14.Lpu_id = :Lpu_id
            limit 1
		";
        $result = $this->db->query($query, ['EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id'], 'Lpu_id' => $data['Lpu_id']]);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * @param $data
     * @return bool
     */
    public function getEvnPLDispTeen14Fields($data)
    {
        $query = "
			SELECT
				rtrim(lp.Lpu_Name) as \"Lpu_Name\",
				rtrim(coalesce(lp1.Lpu_Name, '')) as \"Lpu_AName\",
				rtrim(coalesce(addr1.Address_Address, '')) as \"Lpu_AAddress\",
				rtrim(lp.Lpu_OGRN) as \"Lpu_OGRN\",
				coalesce(pc.PersonCard_Code, '') as \"PersonCard_Code\",
				ps.Person_SurName || ' ' || ps.Person_FirName || ' ' || coalesce(ps.Person_SecName, '') as \"Person_FIO\",
				sx.Sex_Name as \"Sex_Name\",
				coalesce(osmo.OrgSMO_Nick, '') as \"OrgSMO_Nick\",
				coalesce(ps.Polis_Ser, '') as \"Polis_Ser\",
				coalesce(ps.Polis_Num, '') as \"Polis_Num\",
				coalesce(osmo.OrgSMO_Name, '') as \"OrgSMO_Name\",
				to_char(ps.Person_BirthDay, {$this->dateTimeForm104}) as \"Person_BirthDay\",
				coalesce(addr.Address_Address, '') as \"Person_Address\",
				jborg.Org_Nick as \"Org_Nick\",
				case when EPLDT14.EvnPLDispTeen14_IsBud = 2 then 'Да' else 'Нет' end as \"EvnPLDispTeen14_IsBud\",
				atype.AttachType_Name,
				to_char( EPLDT14.EvnPLDispTeen14_disDate, {$this->dateTimeForm104}) as \"EvnPLDispTeen14_disDate\"
			FROM
				v_EvnPLDispTeen14 EPLDT14
				inner join v_Lpu lp on lp.Lpu_id = EPLDT14.Lpu_id
				left join v_Lpu lp1 on lp1.Lpu_id = EPLDT14.Lpu_aid
				left join Address addr1 on addr1.Address_id = lp1.UAddress_id
				left join v_PersonCard pc on pc.Person_id = EPLDT14.Person_id and pc.LpuAttachType_id = 1
				inner join v_PersonState ps on ps.Person_id = EPLDT14.Person_id
				inner join Sex sx on sx.Sex_id = ps.Sex_id
				left join Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo osmo on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Address addr on addr.Address_id = ps.PAddress_id
				left join Job jb on jb.Job_id = ps.Job_id
				left join Org jborg on jborg.Org_id = jb.Org_id
				left join AttachType atype on atype.AttachType_id = EPLDT14.AttachType_id
			WHERE
				(1 = 1)
				and EPLDT14.EvnPLDispTeen14_id = :EvnPLDispTeen14_id
				and EPLDT14.Lpu_id = :Lpu_id
			limit 1
		";
        $result = $this->db->query($query, [
            'EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id'],
            'Lpu_id' => $data['Lpu_id']
        ]);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение списка осмотров врача-специалиста в талоне по ДД
     * Входящие данные: $data['EvnPLDispTeen14_id']
     * На выходе: ассоциативный массив результатов запроса
     * @param $data
     * @return bool
     */
    public function loadEvnVizitDispTeen14Grid($data)
    {
        $query = "
			select
				EVZDT14.EvnVizitDispTeen14_id as \"EvnVizitDispTeen14_id\",
				to_char(EVZDT14.EvnVizitDispTeen14_setDate, {$this->dateTimeForm104}) as \"EvnVizitDispTeen14_setDate\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(DDS.Teen14DispSpecType_Name) as \"Teen14DispSpecType_Name\",
				RTRIM(D.Diag_Code) || ' ' || RTRIM(D.Diag_Name) as \"Diag_Code\",
				EVZDT14.MedPersonal_id as \"MedPersonal_id\",
				EVZDT14.Teen14DispSpecType_id as \"Teen14DispSpecType_id\",
				EVZDT14.LpuSection_id as \"LpuSection_id\",
				EVZDT14.Diag_id as \"Diag_id\",
				EVZDT14.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDT14.DeseaseStage_id as \"DeseaseStage_id\",
				EVZDT14.HealthKind_id as \"HealthKind_id\",
				EVZDT14.EvnVizitDispTeen14_IsSanKur as \"EvnVizitDispTeen14_IsSanKur\",
				EVZDT14.EvnVizitDispTeen14_IsOut as \"EvnVizitDispTeen14_IsOut\",				
				EVZDT14.DopDispAlien_id as \"DopDispAlien_id\",
				EVZDT14.EvnVizitDispTeen14_Descr as \"EvnVizitDispTeen14_Descr\",
				EVZDT14.DeseaseFuncType_id as \"DeseaseFuncType_id\",
				case when DeseaseFuncType_Code is null then '' else DeseaseFuncType_Code || '. ' || DeseaseFuncType_Name end as \"DeseaseFuncType_Name\",
				EVZDT14.DiagType_id as \"DiagType_id\",
				case when DiagType_Code is null then '' else DiagType_Code || '. ' || DiagType_Name end as \"DiagType_Name\",				
				EVZDT14.DispRegistrationType_id as \"DispRegistrationType_id\",
				case when DispRegistrationType_Code is null then '' else DispRegistrationType_Code || '. ' || DispRegistrationType_Name end as \"DispRegistrationType_Name\",				
				EVZDT14.EvnVizitDispTeen14_isFirstDetected as \"EvnVizitDispTeen14_isFirstDetected\",
				case when yn.YesNo_Code is null then '' else yn.YesNo_Code || '. ' || yn.YesNo_Name end as \"EvnVizitDispTeen14_isFirstDetected_Name\",
				EVZDT14.RecommendationsTreatmentType_id as \"RecommendationsTreatmentType_id\",
				EVZDT14.EvnVizitDispTeen14_isVMPRecommented as \"EvnVizitDispTeen14_isVMPRecommented\",
				EVZDT14.RecommendationsTreatmentDopType_id as \"RecommendationsTreatmentDopType_id\",
				1 as \"Record_Status\"
			from v_EvnVizitDispTeen14 EVZDT14
				left join LpuSection LS on LS.LpuSection_id = EVZDT14.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDT14.MedPersonal_id
				left join Teen14DispSpecType DDS on DDS.Teen14DispSpecType_id = EVZDT14.Teen14DispSpecType_id
				left join Diag D on D.Diag_id = EVZDT14.Diag_id
				left join DeseaseFuncType dft on dft.DeseaseFuncType_id = EVZDT14.DeseaseFuncType_id
				left join DiagType dgt on dgt.DiagType_id = EVZDT14.DiagType_id
				left join DispRegistrationType drt on drt.DispRegistrationType_id = EVZDT14.DispRegistrationType_id
				left join YesNo yn on yn.YesNo_id = EVZDT14.EvnVizitDispTeen14_isFirstDetected
			where EVZDT14.EvnVizitDispTeen14_pid = :EvnPLDispTeen14_id
		";
        $result = $this->db->query($query, ['EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id']]);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение данных для редактирования посещения врача-специалиста в талоне по ДД
     * Входящие данные: $data['EvnVizitDispTeen14_id']
     * На выходе: ассоциативный массив результатов запроса
     * @param $data
     * @return bool
     */
    public function loadEvnVizitDispTeen14EditForm($data)
    {
        $this->load->helper('MedStaffFactLink');
        $med_personal_list = getMedPersonalListWithLinks();

        $case = (count($med_personal_list) > 0 ? "and EVZDT14.MedPersonal_id in (" . implode(',', $med_personal_list) . ")" : "");
        $query = "
			select
				EVZDT14.EvnVizitDispTeen14_id as \"EvnVizitDispTeen14_id\",
				to_char(EVZDT14.EvnVizitDispTeen14_setDate, {$this->dateTimeForm104}) as \"EvnVizitDispTeen14_setDate\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(DDS.Teen14DispSpecType_Name) as \"Teen14DispSpecType_Name\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDT14.MedPersonal_id as \"MedPersonal_id\",
				EVZDT14.Teen14DispSpecType_id as \"Teen14DispSpecType_id\",
				EVZDT14.LpuSection_id as \"LpuSection_id\",
				EVZDT14.Diag_id as \"Diag_id\",
				EVZDT14.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDT14.DeseaseStage_id as \"DeseaseStage_id\",
				EVZDT14.HealthKind_id as \"HealthKind_id\",
				EVZDT14.EvnVizitDispTeen14_IsSanKur as \"EvnVizitDispTeen14_IsSanKur\",
				EVZDT14.EvnVizitDispTeen14_IsOut as \"EvnVizitDispTeen14_IsOut\",				
				EVZDT14.DopDispAlien_id as \"DopDispAlien_id\",
				EVZDT14.EvnVizitDispTeen14_Descr as \"EvnVizitDispTeen14_Descr\",
				1 as \"RecordStatus\",
				case when EVZDT14.Lpu_id = :Lpu_id {$case} then 'edit' else 'view' end as \"accessType\"
			from v_EvnVizitDispTeen14 EVZDT14
				left join LpuSection LS on LS.LpuSection_id = EVZDT14.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDT14.MedPersonal_id and MP.Lpu_id = EVZDT14.Lpu_id
				left join Teen14DispSpecType DDS on DDS.Teen14DispSpecType_id = EVZDT14.Teen14DispSpecType_id
				left join Diag D on D.Diag_id = EVZDT14.Diag_id
			where
			    EVZDT14.EvnVizitDispTeen14_id = :EvnVizitDispTeen14_id
			limit 1
		";
        $result = $this->db->query($query, [
            'EvnVizitDispTeen14_id' => $data['EvnVizitDispTeen14_id'],
            'Lpu_id' => $data['Lpu_id']
        ]);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение списка осмотров врача-специалиста в талоне по ДД
     * Входящие данные: $data['EvnPLDispTeen14_id']
     * На выходе: ассоциативный массив результатов запроса
     * @param $data
     * @return bool
     */
    public function loadEvnVizitDispTeen14Data($data)
    {

        $query = "
			select
				EVZDT14.EvnVizitDispTeen14_id as \"EvnVizitDispTeen14_id\",
				to_char(EVZDT14.EvnVizitDispTeen14_setDate, {$this->dateTimeForm104}) as \"EvnVizitDispTeen14_setDate\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(coalesce(MP.MedPersonal_TabCode, '')) as \"MedPersonal_TabCode\",
				RTRIM(DDS.Teen14DispSpecType_Name) as \"Teen14DispSpecType_Name\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDT14.MedPersonal_id as \"MedPersonal_id\",
				EVZDT14.Teen14DispSpecType_id as \"Teen14DispSpecType_id\",
				EVZDT14.LpuSection_id as \"LpuSection_id\",
				EVZDT14.Diag_id as \"Diag_id\",
				EVZDT14.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDT14.DeseaseStage_id as \"DeseaseStage_id\",
				EVZDT14.HealthKind_id as \"HealthKind_id\",
				EVZDT14.EvnVizitDispTeen14_IsSanKur as \"EvnVizitDispTeen14_IsSanKur\",
				EVZDT14.EvnVizitDispTeen14_IsOut as \"EvnVizitDispTeen14_IsOut\",				
				EVZDT14.DopDispAlien_id as \"DopDispAlien_id\",
				EVZDT14.EvnVizitDispTeen14_Descr as \"EvnVizitDispTeen14_Descr\",
				1 as \"Record_Status\"
			from v_EvnVizitDispTeen14 EVZDT14
				left join LpuSection LS on LS.LpuSection_id = EVZDT14.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDT14.MedPersonal_id
				left join Teen14DispSpecType DDS on DDS.Teen14DispSpecType_id = EVZDT14.Teen14DispSpecType_id
				left join Diag D on D.Diag_id = EVZDT14.Diag_id
			where EVZDT14.EvnVizitDispTeen14_pid = :EvnPLDispTeen14_id
		";
        $result = $this->db->query($query, ['EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id']]);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Получение списка исследований в талоне по ДД
     * Входящие данные: $data['EvnPLDispTeen14_id']
     * На выходе: ассоциативный массив результатов запроса
     * @param $data
     * @return bool
     */
    public function loadEvnUslugaDispTeen14Grid($data)
    {

        $query = "
			select
				EUDT14.EvnUslugaDispTeen14_id as \"EvnUslugaDispTeen14_id\",
				to_char(EUDT14.EvnUslugaDispTeen14_setDate, {$this->dateTimeForm104}) as \"EvnUslugaDispTeen14_setDate\",
				to_char(EUDT14.EvnUslugaDispTeen14_didDate, {$this->dateTimeForm104}) as \"EvnUslugaDispTeen14_didDate\",
				EUDT14.DispUslugaTeen14Type_id as \"DispUslugaTeen14Type_id\",
				RTRIM(DT14UT.DispUslugaTeen14Type_Name) as \"DispUslugaTeen14Type_Name\",
				EUDT14.LpuSection_uid as \"LpuSection_id\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				EUDT14.MedPersonal_id as \"MedPersonal_id\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				EUDT14.Usluga_id as \"Usluga_id\",
				EUDT14.StudyType_id as \"StudyType_id\",
				st.StudyType_Code || '. ' || st.StudyType_Name as \"StudyType_Name\",
				RTRIM(U.Usluga_Name) as \"Usluga_Name\",
				RTRIM(U.Usluga_Code) as \"Usluga_Code\",
				EUDT14.ExaminationPlace_id as \"ExaminationPlace_id\",
				1 as \"Record_Status\"
			from v_EvnUslugaDispTeen14 EUDT14
				left join DispUslugaTeen14Type DT14UT on DT14UT.DispUslugaTeen14Type_id = EUDT14.DispUslugaTeen14Type_id
				left join v_LpuSection LS on LS.LpuSection_id = EUDT14.LpuSection_uid
				left join v_MedPersonal MP on MP.MedPersonal_id = EUDT14.MedPersonal_id
				left join v_Usluga U on U.Usluga_id = EUDT14.Usluga_id
				left join v_StudyType st on st.StudyType_id = EUDT14.StudyType_id
			where
			    EUDT14.EvnUslugaDispTeen14_pid = :EvnPLDispTeen14_id
		";

        $result = $this->db->query($query, ['EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id']]);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение списка исследований в талоне по ДД
     * Входящие данные: $data['EvnPLDispTeen14_id']
     * На выходе: ассоциативный массив результатов запроса
     */
    public function loadEvnUslugaDispTeen14Data($data)
    {
        $query = "
			select
				EUDT14.EvnUslugaDispTeen14_id as \"EvnUslugaDispTeen14_id\",
				to_char(EUDT14.EvnUslugaDispTeen14_setDate, {$this->dateTimeForm104}) as \"EvnUslugaDispTeen14_setDate\",
				to_char(EUDT14.EvnUslugaDispTeen14_didDate, {$this->dateTimeForm104}) as \"EvnUslugaDispTeen14_didDate\",
				EUDT14.DispUslugaTeen14Type_id as \"DispUslugaTeen14Type_id\"
			from
			    v_EvnUslugaDispTeen14 EUDT14
			where
			    EUDT14.EvnUslugaDispTeen14_pid = :EvnPLDispTeen14_id
		";

        $result = $this->db->query($query, ['EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id']]);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');

    }

    /**
     * @param $data
     * @return bool
     */
    public function loadEvnPLDispTeen14StreamList($data)
    {
        $filter = ['EPL.pmUser_insID = :pmUser_id'];
        $queryParams = ['pmUser_id' => $data['pmUser_id']];

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $data['begTime'])) {
            $filter[] = "EPL.EvnPL_insDT >= :date_time";
            $queryParams['date_time'] = $data['begDate'] . " " . $data['begTime'];
        }

        if (isset($data['Lpu_id'])) {
            $filter[] = "EPL.Lpu_id = :Lpu_id";
            $queryParams['Lpu_id'] = $data['Lpu_id'];
        }

        $filter = implode(' and', $filter);
        $query = "
        	SELECT DISTINCT
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.Person_id as \"Person_id\",
				EPL.Server_id as \"Server_id\",
				EPL.PersonEvn_id as \"PersonEvn_id\",
				RTRIM(EPL.EvnPL_NumCard) as \"EvnPL_NumCard\",
				RTRIM(PS.Person_Surname) as \"Person_Surname\",
				RTRIM(PS.Person_Firname) as \"Person_Firname\",
				RTRIM(PS.Person_Secname) as \"Person_Secname\",
				to_char(PS.Person_Birthday, {$this->dateTimeForm104}) as \"Person_Birthday\",
				to_char(EPL.EvnPL_setDate, {$this->dateTimeForm104}) as \"EvnPL_setDate\",
				to_char(EPL.EvnPL_disDate, {$this->dateTimeForm104}) as \"EvnPL_disDate\",
				EPL.EvnPL_VizitCount as \"EvnPL_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPL_IsFinish\"
			FROM v_EvnPL EPL
				inner join v_PersonState PS on PS.Person_id = EPL.Person_id
				left join YesNo IsFinish on IsFinish.YesNo_id = EPL.EvnPL_IsFinish
			WHERE (1 = 1)
				" . $filter . "
			ORDER BY EPL.EvnPL_id desc
			limit 100
    	";
        $result = $this->db->query($query, $queryParams);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * @param $data
     * @return bool
     */
    public function loadEvnVizitPLDispTeen14Grid($data)
    {
        $query = "
			select
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				EVPL.LpuSection_id as \"LpuSection_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedPersonal_sid as \"MedPersonal_sid\",
				EVPL.PayType_id as \"PayType_id\",
				EVPL.ProfGoal_id as \"ProfGoal_id\",
				EVPL.ServiceType_id as \"ServiceType_id\",
				EVPL.VizitType_id as \"VizitType_id\",
				EVPL.EvnVizitPL_Time as \"EvnVizitPL_Time\",
				to_char(EVPL.EvnVizitPL_setDate, {$this->dateTimeForm104}) as \"EvnVizitPL_setDate\",
				EVPL.EvnVizitPL_setTime as \"EvnVizitPL_setTime\",
				RTrim(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTrim(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTrim(PT.PayType_Name) as \"PayType_Name\",
				RTrim(ST.ServiceType_Name) as \"ServiceType_Name\",
				RTrim(VT.VizitType_Name) as \"VizitType_Name\",
				DeseaseFuncType_id as \"DeseaseFuncType_id\",
				DiagType_id as \"DiagType_id\",
				DispRegistrationType_id as \"DispRegistrationType_id\",
				EvnVizitDispTeen14_isFirstDetected as \"EvnVizitDispTeen14_isFirstDetected\",
				RecommendationsTreatmentType_id as \"RecommendationsTreatmentType_id\",
				EvnVizitDispTeen14_isVMPRecommented as \"EvnVizitDispTeen14_isVMPRecommented\",
				RecommendationsTreatmentType_id as \"RecommendationsTreatmentType_id\",
				1 as \"Record_Status\"
			from v_EvnVizitPL EVPL
				left join LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVPL.MedPersonal_id
				left join PayType PT on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = ?
		";
        $result = $this->db->query($query, ['EvnVizitPL_pid' => $data['EvnPL_id']]);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Проверка того, что человек есть в регистре по ДД и у него заведены все необходимые данные
     * @param $data
     * @return array
     * @throws Exception
     */
    public function checkPersonData($data)
    {
        $query = "
			select
				Sex_id as \"Sex_id\",
				SocStatus_id as \"SocStatus_id\",
				ps.UAddress_id as \"Person_UAddress_id\",
				ps.Polis_Ser as \"Polis_Ser\",
				ps.Polis_Num as \"Polis_Num\",
				o.Org_Name as \"Org_Name\",
				o.Org_INN as \"Org_INN\",
				o.Org_OGRN as \"Org_OGRN\",
				o.UAddress_id as \"Org_UAddress_id\",
				o.Okved_id as \"Okved_id\",
				os.OrgSmo_Name as \"OrgSmo_Name\",
				(datediff('year', PS.Person_Birthday, dbo.tzGetDate())
				+ case when date_part('month'. ps.Person_Birthday) > date_part('month', dbo.tzGetDate())
				or (date_part('month', ps.Person_Birthday) = date_part('month', dbo.tzGetDate()) and date_part('day', ps.Person_Birthday) > date_part('day', dbo.tzGetDate()))
				then -1 else 0 end) as \"Person_Age\",
				to_char(PS.Person_Birthday, {$this->dateTimeForm104}) as \"Person_Birthday\"
			from
			    v_persondopdisp pdt14
                left join v_PersonState ps on ps.Person_id = pdt14.Person_id
                left join v_Job j on j.Job_id=ps.Job_id
                left join v_Org o on o.Org_id=j.Org_id
                left join v_Polis pol on pol.Polis_id=ps.Polis_id
                left join v_OrgSmo os on os.OrgSmo_id=pol.OrgSmo_id
			where
			    pdt14.Person_id = :Person_id
		";

        $result = $this->db->query($query, ['Person_id' => $data['Person_id']]);
        $response = $result->result('array');

        if (!is_array($response) || count($response) == 0)
            throw new Exception('Этого человека нет в регистре по ДД!');

        $error = [];
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

        If (count($error)) { // есть ошибки в заведении
            $errstr = implode("<br/>", $errors);
            throw new Exception('Проверьте полноту заведения данных у человека!<br/>' . $errstr);
        }

        return [
            "Ok",
            ArrayVal($response[0], 'Sex_id'),
            ArrayVal($response[0], 'Person_Age'),
            ArrayVal($response[0], 'Person_Birthday')
        ];
    }


    /**
     * @param $data
     * @return bool
     */
    public function checkPediatrVizitDate($data) //В соответствие с задачей 13387 проверяем дату осмотра педиатра - не должна быть меньше даты других осмотров
    {
        $sql = "
            select
                *
            from
                v_EvnVizitDispTeen14 EVDT
            where
                EVDT.EvnVizitDispTeen14_pid = :EvnPLDispTeen14_id
            and
                EVDT.EvnVizitDispTeen14_setDT > :SetDate
            and
				EVDT.Teen14DispSpecType_id <> '1'
			";
        $params['EvnPLDispTeen14_id'] = $data['EvnPLDispTeen14_id'];
        $params['SetDate'] = $data['PediatrDate'];

        $result = $this->db->query($sql, $params);
        $result = $result->result('array');

        return empty($result);
    }

    /**
     * @param $data
     * @return array|bool
     * @throws Exception
     */
    public function saveEvnPLDispTeen14($data)
    {
        $procedure = '';
        if (isset($data['EvnPLDispTeen14_id'])) {
            // достаем дату начала, дату окончания, количество посещений
            $query = "
				select
					to_char(cast(EvnPLDispTeen14_setDT as timestamp), {$this->dateTimeForm112}) as \"EvnPLDispTeen14_setDT\",
					to_char(cast(EvnPLDispTeen14_disDT as timestamp), {$this->dateTimeForm112}) as \"EvnPLDispTeen14_disDT\",
					to_char(cast(EvnPLDispTeen14_didDT as timestamp), {$this->dateTimeForm112}) as \"EvnPLDispTeen14_didDT\",					
					EvnPLDispTeen14_VizitCount as \"EvnPLDispTeen14_VizitCount\"
				from
					v_EvnPLDispTeen14
				where
				    EvnPLDispTeen14_id = :EvnPLDispTeen14_id
				limit 1
			";
            $result = $this->db->query($query, ['EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id']]);
            $response = $result->result('array')[0];
            $data['EvnPLDispTeen14_setDT'] = $response['EvnPLDispTeen14_setDT'];
            $data['EvnPLDispTeen14_disDT'] = $response['EvnPLDispTeen14_disDT'];
            $data['EvnPLDispTeen14_didDT'] = $response['EvnPLDispTeen14_didDT'];
            $data['EvnPLDispTeen14_VizitCount'] = $response['EvnPLDispTeen14_VizitCount'];
            $procedure = 'p_EvnPLDispTeen14_upd';

            $query = "
            	select
                	EvnPLDispTeen14_id as \"EvnPLDispTeen14_id\",
                	Error_Code as \"Error_Code\",
                	Error_Message as \"Error_Msg\"
		from p_EvnPLDispTeen14_upd(
			EvnPLDispTeen14_id := :EvnPLDispTeen14_id,
			Lpu_id := :Lpu_id,
			Server_id := :Server_id,
			PersonEvn_id := :PersonEvn_id,
			EvnPLDispTeen14_setDT := :EvnPLDispTeen14_setDT,
			EvnPLDispTeen14_disDT := :EvnPLDispTeen14_disDT,
			EvnPLDispTeen14_didDT := :EvnPLDispTeen14_didDT,
			PsychicalConditionType_id := :PsychicalConditionType_id,
			SexualConditionType_id := :SexualConditionType_id,
			DopDispResType_id := :DopDispResType_id,
			DispResMedicalMeasureType_id := :DispResMedicalMeasureType_id,
			EvnPLDispTeen14_isHTAid := :EvnPLDispTeen14_isHTAid,
			EvnPLDispTeen14_HTAidDT := :EvnPLDispTeen14_HTAidDT,
			InvalidType_id := :InvalidType_id,
			pmUser_id := :pmUser_id
		)";
        } else {
            $data['EvnPLDispTeen14_setDT'] = date('Y-m-d');
            $data['EvnPLDispTeen14_disDT'] = null;
            $data['EvnPLDispTeen14_didDT'] = null;
            $data['EvnPLDispTeen14_VizitCount'] = 0;

            $query = "
            	select
                	EvnPLDispTeen14_id as \"EvnPLDispTeen14_id\",
                	Error_Code as \"Error_Code\",
                	Error_Message as \"Error_Msg\"
		from p_EvnPLDispTeen14_ins(
			EvnPLDispTeen14_id := :EvnPLDispTeen14_id,
			Lpu_id := :Lpu_id,
			Server_id := :Server_id,
			PersonEvn_id := :PersonEvn_id,
			EvnPLDispTeen14_setDT := :EvnPLDispTeen14_setDT,
			EvnPLDispTeen14_disDT := :EvnPLDispTeen14_disDT,
			EvnPLDispTeen14_didDT := :EvnPLDispTeen14_didDT,
			EvnPLDispTeen14_VizitCount := :EvnPLDispTeen14_VizitCount,
			EvnPLDispTeen14_IsFinish := :EvnPLDispTeen14_IsFinish,
			PsychicalConditionType_id := :PsychicalConditionType_id,
			SexualConditionType_id := :SexualConditionType_id,
			DopDispResType_id := :DopDispResType_id,
			DispResMedicalMeasureType_id := :DispResMedicalMeasureType_id,
			EvnPLDispTeen14_isHTAid := :EvnPLDispTeen14_isHTAid,
			EvnPLDispTeen14_HTAidDT := :EvnPLDispTeen14_HTAidDT,
			InvalidType_id := :InvalidType_id,
			pmUser_id := :pmUser_id
		)";
        }

        $result = $this->db->query($query,
            [
                'EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id'],
                'Lpu_id' => $data['Lpu_id'],
                'Server_id' => $data['Server_id'],
                'PersonEvn_id' => $data['PersonEvn_id'],
                'EvnPLDispTeen14_setDT' => $data['EvnPLDispTeen14_setDT'],
                'EvnPLDispTeen14_disDT' => $data['EvnPLDispTeen14_disDT'],
                'EvnPLDispTeen14_didDT' => $data['EvnPLDispTeen14_didDT'],
                'EvnPLDispTeen14_VizitCount' => $data['EvnPLDispTeen14_VizitCount'],
                'EvnPLDispTeen14_IsFinish' => $data['EvnPLDispTeen14_IsFinish'],
                'PsychicalConditionType_id' => $data['PsychicalConditionType_id'],
                'SexualConditionType_id' => $data['SexualConditionType_id'],
                'DopDispResType_id' => $data['DopDispResType_id'],
                'DispResMedicalMeasureType_id' => $data['DispResMedicalMeasureType_id'],
                'EvnPLDispTeen14_isHTAid' => $data['EvnPLDispTeen14_isHTAid'],
                'EvnPLDispTeen14_HTAidDT' => $data['EvnPLDispTeen14_HTAidDT'],
                'InvalidType_id' => $data['InvalidType_id'],
                'pmUser_id' => $data['pmUser_id']
            ]
        );

        if (!is_object($result)) {
           throw new Exception('Ошибка при выполнении запроса к базе данных (сохранение талона по ДД)');
        }

        $response = $result->result('array');

        if (!is_array($response) || count($response) == 0) {
            return false;
        } else if ($response[0]['Error_Msg']) {
            return $response;
        }
        
        // новый ли талон
        $pl_is_new = false;
        if (!isset($data['EvnPLDispTeen14_id']) || !($data['EvnPLDispTeen14_id'] > 0)) {
            $pl_is_new = true;
            $data['EvnPLDispTeen14_id'] = $response[0]['EvnPLDispTeen14_id'];
        }

        // показатели веса и роста человека
        // получаем идентификатор записи о показателях роста
        $height_procedure = 'p_PersonHeight_ins';
        $data['PersonHeight_id'] = null;
        if (!$pl_is_new) {
            $sql = "
				select
				    PersonHeight_id as \"PersonHeight_id\"
				from
					v_PersonHeight
				where
					Evn_id = ?
				order by PersonHeight_insDT desc
				limit 1
			";
            $res = $this->db->query($sql, [$data['EvnPLDispTeen14_id']]);
            if (is_object($res)) {
                $sel = $res->result('array');
                if (count($sel) > 0) {
                    $data['PersonHeight_id'] = $sel[0]['PersonHeight_id'];
                    $height_procedure = 'p_PersonHeight_upd';
                }
            }
        }
        // сохраняем рост
        $query = "
            select
                PersonHeight_id as \"PersonHeight_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from {$height_procedure}
			(
				Server_id := :Server_id,
				Evn_id := :Evn_id,
				PersonHeight_id := :PersonHeight_id,
				Person_id := :Person_id,
				PersonHeight_setDT := dbo.tzGetDate(),
				PersonHeight_Height := :PersonHeight_Height,
				PersonHeight_IsAbnorm := :PersonHeight_IsAbnorm,
				HeightAbnormType_id := :HeightAbnormType_id,
				pmUser_id := :pmUser_id
			)
		";
        $queryParams = [
            'Server_id' => $data['Server_id'],
            'Evn_id' => $data['EvnPLDispTeen14_id'],
            'PersonHeight_id' => ($data['PersonHeight_id'] > 0 ? $data['PersonHeight_id'] : NULL),
            'Person_id' => $data['Person_id'],
            'PersonHeight_Height' => (int)$data['PersonChild_Height'] > 0 ? $data['PersonChild_Height'] : NULL,
            'PersonHeight_IsAbnorm' => (!empty($data['PersonChild_IsHeightAbnorm']) ? $data['PersonChild_IsHeightAbnorm'] : NULL),
            'HeightAbnormType_id' => (!empty($data['HeightAbnormType_id']) ? $data['HeightAbnormType_id'] : NULL),
            'pmUser_id' => $data['pmUser_id']
        ];
        $this->db->query($query, $queryParams);

        // получаем идентификатор записи о показателях веса
        $weight_procedure = 'p_PersonWeight_ins';
        $data['PersonWeight_id'] = null;
        if (!$pl_is_new) {
            $sql = "
				select
					PersonWeight_id as \"PersonWeight_id\"
                from
					v_PersonWeight
				where
					Evn_id = :EvnPLDispTeen14_id
				order by PersonWeight_insDT desc
				limit 1
			";
            $res = $this->db->query($sql, ['EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id']]);
            if (is_object($res)) {
                $sel = $res->result('array');
                if (count($sel) > 0) {
                    $data['PersonWeight_id'] = $sel[0]['PersonWeight_id'];
                    $weight_procedure = 'p_PersonWeight_upd';
                }
            }
        }
        // сохраняем вес
        $query = "
            select
                PersonWeight_id as \"PersonWeight_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from {$weight_procedure}
			(
				Server_id := :Server_id,
				Evn_id := :Evn_id,
				PersonWeight_id := :PersonWeight_id,
				Person_id := :Person_id,
				PersonWeight_setDT := dbo.tzGetDate(),
				PersonWeight_Weight := :PersonWeight_Weight,
				PersonWeight_IsAbnorm := :PersonWeight_IsAbnorm,
				WeightAbnormType_id := :WeightAbnormType_id,
				Okei_id := :Okei_id,
				pmUser_id := :pmUser_id
			)
		";

        $queryParams = [
            'Server_id' => $data['Server_id'],
            'Evn_id' => $data['EvnPLDispTeen14_id'],
            'PersonWeight_id' => ($data['PersonWeight_id'] > 0 ? $data['PersonWeight_id'] : NULL),
            'Person_id' => $data['Person_id'],
            'PersonWeight_Weight' => (int)$data['PersonWeight_Weight'] > 0 ? $data['PersonWeight_Weight'] : NULL,
            'PersonWeight_IsAbnorm' => (!empty($data['PersonWeight_IsWeightAbnorm']) ? $data['PersonWeight_IsWeightAbnorm'] : NULL),
            'WeightAbnormType_id' => (!empty($data['WeightAbnormType_id']) ? $data['WeightAbnormType_id'] : NULL),
            'Okei_id' => $data['Okei_id'],
            'pmUser_id' => $data['pmUser_id']
        ];

        $result = $this->db->query($query, $queryParams);

        // Осмотры врача-специалиста
        foreach ($data['EvnVizitDispTeen14'] as $key => $record) {
            if (strlen($record['EvnVizitDispTeen14_id']) > 0) {
                if ($record['Record_Status'] == 3) {// удаление посещений
                    $query = "
                        select
                            Error_Code as \"Error_Code\",
                            Error_Message as \"Error_Msg\"
						from p_EvnVizitDispTeen14_del 
						(
                            EvnVizitDispTeen14_id = :EvnVizitDispTeen14_id, 
                            pmUser_id = :pmUser_id
                        )
					";
                    $result = $this->db->query($query, ['EvnVizitDispTeen14_id' => $record['EvnVizitDispTeen14_id'], 'pmUser_id' => $data['pmUser_id']]);

                    if (!is_object($result)) {
                       throw new Exception('Ошибка при выполнении запроса к базе данных (удаление осмотра врача-специалиста)');
                    }

                    $response = $result->result('array');

                    if (!is_array($response) || count($response) == 0) {
                        throw new Exception('Ошибка при удалении осмотра врача-специалиста');
                    } else if (strlen($response[0]['Error_Msg']) > 0) {
                        return $response;
                    }
                } else {
                    if ($record['Record_Status'] == 0) {
                        $procedure = 'p_EvnVizitDispTeen14_ins';
                    } else {
                        $procedure = 'p_EvnVizitDispTeen14_upd';
                    }
                    // проверяем, есть ли уже такое посещение
                    $query = "
						select 
							count(*) as cnt
						from
							v_EvnVizitDispTeen14
						where
							EvnVizitDispTeen14_pid = :EvnPLDispTeen14_id
							and Teen14DispSpecType_id = :Teen14DispSpecType_id
							and ( EvnVizitDispTeen14_id <> coalesce(cast(:EvnVizitDispTeen14_id as bigint), 0) )
					";
                    $result = $this->db->query(
                        $query,
                        [
                            'EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id'],
                            'Teen14DispSpecType_id' => $record['Teen14DispSpecType_id'],
                            'EvnVizitDispTeen14_id' => $record['Record_Status'] == 0 ? null : $record['EvnVizitDispTeen14_id']
                        ]
                    );
                    if (!is_object($result)) {
                        throw new Exception('Ошибка при выполнении запроса к базе данных (сохранение посещения)');
                    }
                    $response = $result->result('array');
                    if (!is_array($response) || count($response) == 0) {
                        throw new Exception('Ошибка при выполнении запроса к базе данных (сохранение посещения)');
                    } else if ($response[0]['cnt'] >= 1) {
                        throw new Exception('Обнаружено дублирование осмотров, это недопустимо.');
                    }
                    // окончание проверки

                    $query = "
						select 
						    EvnVizitDispTeen14_id as \"EvnVizitDispTeen14_id\",
						    Error_Code as \"Error_Code\",
						    Error_Message as \"Error_Msg\"
						from {$procedure}
						(
                            EvnVizitDispTeen14_id := :EvnVizitDispTeen14_id, 
                            EvnVizitDispTeen14_pid := :EvnPLDispTeen14_id, 
                            Lpu_id := :Lpu_id, 
                            Server_id := :Server_id, 
                            PersonEvn_id := :PersonEvn_id, 
                            EvnVizitDispTeen14_setDT := :EvnVizitDispTeen14_setDate, 
                            EvnVizitDispTeen14_disDT := null, 
                            EvnVizitDispTeen14_didDT := null, 
                            LpuSection_id := :LpuSection_id, 
                            MedPersonal_id := :MedPersonal_id, 
                            MedStaffFact_id := :MedStaffFact_id, 
                            MedPersonal_sid := null, 
                            PayType_id := null, 
                            Teen14DispSpecType_id := :Teen14DispSpecType_id, 
                            Diag_id := :Diag_id, 
                            HealthKind_id := :HealthKind_id, 
                            DeseaseStage_id := :DeseaseStage_id, 
                            DopDispDiagType_id := :DopDispDiagType_id, 
                            EvnVizitDispTeen14_IsSanKur := :EvnVizitDispTeen14_IsSanKur, 
                            EvnVizitDispTeen14_IsOut := :EvnVizitDispTeen14_IsOut, 
                            DopDispAlien_id := :DopDispAlien_id, 
                            EvnVizitDispTeen14_Descr := :EvnVizitDispTeen14_Descr, 
                            DeseaseFuncType_id := :DeseaseFuncType_id, 
                            DiagType_id := :DiagType_id, 
                            DispRegistrationType_id := :DispRegistrationType_id, 
                            EvnVizitDispTeen14_isFirstDetected := :EvnVizitDispTeen14_isFirstDetected, 
                            RecommendationsTreatmentType_id := :RecommendationsTreatmentType_id, 
                            EvnVizitDispTeen14_isVMPRecommented := :EvnVizitDispTeen14_isVMPRecommented, 
                            RecommendationsTreatmentDopType_id := :RecommendationsTreatmentDopType_id, 
                            pmUser_id := :pmUser_id
                        )
					";
                   
                    $result = $this->db->query($query, [
                        'EvnVizitDispTeen14_id' => $record['Record_Status'] == 0 ? null : $record['EvnVizitDispTeen14_id'],
                        'EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id'],
                        'Lpu_id' => $data['Lpu_id'],
                        'Server_id' => $data['Server_id'],
                        'PersonEvn_id' => $data['PersonEvn_id'],
                        'EvnVizitDispTeen14_setDate' => $record['EvnVizitDispTeen14_setDate'],
                        'LpuSection_id' => isset($record['LpuSection_id']) && $record['LpuSection_id'] > 0 ? $record['LpuSection_id'] : null,
                        'MedPersonal_id' => (isset($record['MedPersonal_id']) && $record['MedPersonal_id'] > 0) ? $record['MedPersonal_id'] : null,
                        'MedStaffFact_id' => (isset($record['MedStaffFact_id']) && $record['MedStaffFact_id'] > 0) ? $record['MedStaffFact_id'] : null,
                        'Teen14DispSpecType_id' => $record['Teen14DispSpecType_id'],
                        'Diag_id' => $record['Diag_id'],
                        'HealthKind_id' => $record['HealthKind_id'],
                        'DeseaseStage_id' => (isset($record['DeseaseStage_id']) && $record['DeseaseStage_id'] > 0) ? $record['DeseaseStage_id'] : null,
                        'DopDispDiagType_id' => (isset($record['DopDispDiagType_id']) && $record['DopDispDiagType_id'] > 0) ? $record['DopDispDiagType_id'] : null,
                        'EvnVizitDispTeen14_IsSanKur' => $record['EvnVizitDispTeen14_IsSanKur'],
                        'EvnVizitDispTeen14_IsOut' => $record['EvnVizitDispTeen14_IsOut'],
                        'DopDispAlien_id' => $record['DopDispAlien_id'],
                        'EvnVizitDispTeen14_Descr' => $record['EvnVizitDispTeen14_Descr'],
                        'DeseaseFuncType_id' => (isset($record['DeseaseFuncType_id']) && $record['DeseaseFuncType_id'] > 0) ? $record['DeseaseFuncType_id'] : null,
                        'DiagType_id' => (isset($record['DiagType_id']) && $record['DiagType_id'] > 0) ? $record['DiagType_id'] : null,
                        'DispRegistrationType_id' => (isset($record['DispRegistrationType_id']) && $record['DispRegistrationType_id'] > 0) ? $record['DispRegistrationType_id'] : null,
                        'EvnVizitDispTeen14_isFirstDetected' => (isset($record['EvnVizitDispTeen14_isFirstDetected']) && $record['EvnVizitDispTeen14_isFirstDetected'] > 0) ? $record['EvnVizitDispTeen14_isFirstDetected'] : null,
                        'RecommendationsTreatmentType_id' => (isset($record['RecommendationsTreatmentType_id']) && $record['RecommendationsTreatmentType_id'] > 0) ? $record['RecommendationsTreatmentType_id'] : null,
                        'EvnVizitDispTeen14_isVMPRecommented' => (isset($record['EvnVizitDispTeen14_isVMPRecommented']) && $record['EvnVizitDispTeen14_isVMPRecommented'] > 0) ? $record['EvnVizitDispTeen14_isVMPRecommented'] : null,
                        'RecommendationsTreatmentDopType_id' => (isset($record['RecommendationsTreatmentDopType_id']) && $record['RecommendationsTreatmentDopType_id'] > 0) ? $record['RecommendationsTreatmentDopType_id'] : null,
                        'pmUser_id' => $data['pmUser_id']
                    ]);

                    if (!is_object($result)) {
                       throw new Exception('Ошибка при выполнении запроса к базе данных (сохранение посещения)');
                    }

                    $response = $result->result('array');

                    if (!is_array($response) || count($response) == 0) {
                        return false;
                    } else if ($response[0]['Error_Msg']) {
                        return $response;
                    }

                    $record['EvnVizitDispTeen14_id'] = $response[0]['EvnVizitDispTeen14_id'];
                }
            }
        }

        // Лабораторные исследования
        $usluga_array = [];

        foreach ($data['EvnUslugaDispTeen14'] as $key => $record) {
            if ($record['Record_Status'] == 3) {// удаление исследований
                $query = "
                    select
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\"
					from p_EvnUslugaDispTeen14_del
					( 
                        EvnUslugaDispTeen14_id := :EvnUslugaDispTeen14_id, 
                        pmUser_id := :pmUser_id
					);
				";
                $result = $this->db->query($query, ['EvnUslugaDispTeen14_id' => $record['EvnUslugaDispTeen14_id'], 'pmUser_id' => $data['pmUser_id']]);

                if (!is_object($result)) {
                    throw new Exception('Ошибка при выполнении запроса к базе данных (удаление лабораторного исследования)');
                }

                $response = $result->result('array');

                if (!is_array($response) || count($response) == 0) {
                    throw new Exception('Ошибка при удалении лабораторного исследования');
                } else if (strlen($response[0]['Error_Msg']) > 0) {
                    return $response;
                }
            } else {
                if ($record['Record_Status'] == 0) {
                    $procedure = 'p_EvnUslugaDispTeen14_ins';
                } else {
                    $procedure = 'p_EvnUslugaDispTeen14_upd';
                }

                
                $pay_type = 7;
                // для Уфы PayType отдельно
                if (isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa')
                    $pay_type = 14;

                // окончание проверки
                if ($record['LpuSection_id'] == '')
                    $record['LpuSection_id'] = Null;
                $query = "
					select
					    EvnUslugaDispTeen14_id as \"EvnUslugaDispTeen14_id\",
					    Error_Code as \"Error_Code\",
					    Error_Message as \"Error_Msg\"
					from {$procedure}
					(
                        EvnUslugaDispTeen14_id := ?,
                        EvnUslugaDispTeen14_pid := ?,
                        Lpu_id := ?,
                        Server_id := ?,
                        PersonEvn_id := ?,
                        EvnUslugaDispTeen14_setDT := ?,
                        EvnUslugaDispTeen14_disDT := null,
                        EvnUslugaDispTeen14_didDT := ?,
                        LpuSection_uid := ?,
                        MedPersonal_id := ?,
                        MedStaffFact_id := ?,
                        DispUslugaTeen14Type_id := ?,
                        Usluga_id := ?,
                        StudyType_id := ?,
                        PayType_id := ?,
                        UslugaPlace_id := 1,
                        Lpu_uid := ?,
                        EvnUslugaDispTeen14_Kolvo := 1,
                        ExaminationPlace_id := ?,
                        EvnPrescrTimetable_id := null,
                        EvnPrescr_id := null,
                        pmUser_id := ?
                    );
				";

                if (isset($data['session']['region']) && $data['session']['region']['nick'] == 'ufa' && empty($record['Usluga_id']))
                    $record['Usluga_id'] = null;

                $result = $this->db->query($query, [
                    $record['Record_Status'] == 0 ? null : $record['EvnUslugaDispTeen14_id'],
                    $data['EvnPLDispTeen14_id'],
                    $data['Lpu_id'],
                    $data['Server_id'],
                    $data['PersonEvn_id'],
                    $record['EvnUslugaDispTeen14_setDate'],
                    $record['EvnUslugaDispTeen14_didDate'],
                    $record['LpuSection_id'],
                    (isset($record['MedPersonal_id']) && $record['MedPersonal_id'] > 0) ? $record['MedPersonal_id'] : null,
                    (isset($record['MedStaffFact_id']) && $record['MedStaffFact_id'] > 0) ? $record['MedStaffFact_id'] : null,
                    1,//$record['DispUslugaTeen14Type_id'],
                    $record['Usluga_id'],
                    $record['StudyType_id'],
                    $pay_type,
                    $data['Lpu_id'],
                    $record['ExaminationPlace_id'],
                    $data['pmUser_id']
                ]);

                if (!is_object($result)) {
                    throw new Exception('Ошибка при выполнении запроса к базе данных (сохранение посещения)');
                }
                $response = $result->result('array');

                if (!is_array($response) || count($response) == 0) {
                    return false;
                } else if ($response[0]['Error_Msg']) {
                    return $response;
                }

                $record['EvnUslugaDispTeen14_id'] = $response[0]['EvnUslugaDispTeen14_id'];
                $usluga_array[] = array('id' => $record['EvnUslugaDispTeen14_id'], 'data' => $record['RateGrid_Data']);
            }
        }
        return [
            0 => [
                'EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id'],
                'usluga_array' => $usluga_array,
                'Error_Msg' => ''
            ]
        ];
    }

    /**
     * Поиск талонов по ДД
     * @param $data
     * @return bool
     */
    public function searchEvnPLDispTeen14($data)
    {
        $filter = "";
        $join_str = "";

        if ($data['PersonAge_Min'] > $data['PersonAge_Max']) {
            return false;
        }

        $queryParams = [];

        if (($data['DocumentType_id'] > 0) || ($data['OrgDep_id'] > 0)) {
            $join_str .= " inner join Document on Document.Document_id = PS.Document_id";

            if ($data['DocumentType_id'] > 0) {
                $join_str .= " and Document.DocumentType_id = :DocumentType_id";
                $queryParams['DocumentType_id'] = $data['DocumentType_id'];
            }

            if ($data['OrgDep_id'] > 0) {
                $join_str .= " and Document.OrgDep_id = :OrgDep_id";
                $queryParams['OrgDep_id'] = $data['OrgDep_id'];
            }
        }

        if (($data['OMSSprTerr_id'] > 0) || ($data['OrgSmo_id'] > 0) || ($data['PolisType_id'] > 0)) {
            $join_str .= " inner join Polis on Polis.Polis_id = PS.Polis_id]";

            if ($data['OMSSprTerr_id'] > 0) {
                $join_str .= " and Polis.OmsSprTerr_id = :OMSSprTerr_id";
                $queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
            }

            if ($data['OrgSmo_id'] > 0) {
                $join_str .= " and Polis.OrgSmo_id = :OrgSmo_id";
                $queryParams['OrgSmo_id'] = $data['OrgSmo_id'];
            }

            if ($data['PolisType_id'] > 0) {
                $join_str .= " and Polis.PolisType_id = :PolisType_id";
                $queryParams['PolisType_id'] = $data['PolisType_id'];
            }
        }

        if (($data['Org_id'] > 0) || ($data['Post_id'] > 0)) {
            $join_str .= " inner join Job on Job.Job_id = PS.Job_id";

            if ($data['Org_id'] > 0) {
                $join_str .= " and Job.Org_id = :Org_id";
                $queryParams['Org_id'] = $data['Org_id'];
            }

            if ($data['Post_id'] > 0) {
                $join_str .= " and Job.Post_id = :Post_id";
                $queryParams['Post_id'] = $data['Post_id'];
            }
        }

        if (($data['KLRgn_id'] > 0) || ($data['KLSubRgn_id'] > 0) || ($data['KLCity_id'] > 0) || ($data['KLTown_id'] > 0) || ($data['KLStreet_id'] > 0) || (strlen($data['Address_House']) > 0)) {
            $join_str .= " inner join Address on Address.Address_id = PS.UAddress_id";

            if ($data['KLRgn_id'] > 0) {
                $filter .= " and Address.KLRgn_id = :KLRgn_id";
                $queryParams['KLRgn_id'] = $data['KLRgn_id'];
            }

            if ($data['KLSubRgn_id'] > 0) {
                $filter .= " and Address.KLSubRgn_id = :KLSubRgn_id";
                $queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
            }

            if ($data['KLCity_id'] > 0) {
                $filter .= " and Address.KLCity_id = :KLCity_id";
                $queryParams['KLCity_id'] = $data['KLCity_id'];
            }

            if ($data['KLTown_id'] > 0) {
                $filter .= " and Address.KLTown_id = :KLTown_id";
                $queryParams['KLTown_id'] = $data['KLTown_id'];
            }

            if ($data['KLStreet_id'] > 0) {
                $filter .= " and Address.KLStreet_id = :KLStreet_id";
                $queryParams['KLStreet_id'] = $data['KLStreet_id'];
            }

            if (strlen($data['Address_House']) > 0) {
                $filter .= " and Address.Address_House = :Address_House";
                $queryParams['Address_House'] = $data['Address_House'];
            }
        }

        if (isset($data['EvnPLDispTeen14_disDate'][1])) {
            $filter .= " and EvnPLDispTeen14.EvnPLDispTeen14_disDate <= :EvnPLDispTeen14_disDate1";
            $queryParams['EvnPLDispTeen14_disDate1'] = $data['EvnPLDispTeen14_disDate'][1];
        }

        if (isset($data['EvnPLDispTeen14_disDate'][0])) {
            $filter .= " and EvnPLDispTeen14.EvnPLDispTeen14_disDate >= :EvnPLDispTeen14_disDate1";
            $queryParams['EvnPLDispTeen14_disDate0'] = $data['EvnPLDispTeen14_disDate'][0];
        }

        if ($data['EvnPLDispTeen14_IsFinish'] > 0) {
            $filter .= " and EvnPLDispTeen14.EvnPLDispTeen14_IsFinish = :EvnPLDispTeen14_IsFinish";
            $queryParams['EvnPLDispTeen14_IsFinish'] = $data['EvnPLDispTeen14_IsFinish'];
        }

        if (isset($data['EvnPLDispTeen14_setDate'][1])) {
            $filter .= " and EvnPLDispTeen14.EvnPLDispTeen14_setDate <= :EvnPLDispTeen14_setDate1";
            $queryParams['EvnPLDispTeen14_setDate1'] = $data['EvnPLDispTeen14_setDate'][1];
        }

        if (isset($data['EvnPLDispTeen14_setDate'][0])) {
            $filter .= " and EvnPLDispTeen14.EvnPLDispTeen14_setDate >= :EvnPLDispTeen14_setDate0";
            $queryParams['EvnPLDispTeen14_setDate0'] = $data['EvnPLDispTeen14_setDate'][0];
        }

        if ($data['PersonAge_Max'] > 0) {
            $filter .= " and [EvnPLDispTeen14].[Person_Age] <= :PersonAge_Max";
            $queryParams['PersonAge_Max'] = $data['PersonAge_Max'];
        }

        if ($data['PersonAge_Min'] > 0) {
            $filter .= " and EvnPLDispTeen14.Person_Age >= :PersonAge_Min";
            $queryParams['PersonAge_Min'] = $data['PersonAge_Min'];
        }

        if (($data['PersonCard_Code'] != '') || ($data['LpuRegion_id'] > 0)) {
            $join_str .= " inner join v_PersonCard PC on PC.Person_id = PS.Person_id";

            if (strlen($data['PersonCard_Code']) > 0) {
                $filter .= " and PC.PersonCard_Code = :PersonCard_Code";
                $queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
            }

            if (strlen($data['LpuRegion_id']) > 0) {
                $filter .= " and PC.LpuRegion_id = :LpuRegion_id";
                $queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
            }
        }
        if (isset($data['Person_Birthday'][1])) {
            $filter .= " and PS.Person_Birthday <= :Person_Birthday1";
            $queryParams['Person_Birthday1'] = $data['Person_Birthday'][1];
        }

        if (isset($data['Person_Birthday'][0])) {
            $filter .= " and PS.Person_Birthday >= :Person_Birthday0";
            $queryParams['Person_Birthday0'] = $data['Person_Birthday'][0];
        }

        if (strlen($data['Person_Firname']) > 0) {
            $filter .= " and PS.Person_Firname ilike :Person_Firname";
            $queryParams['Person_Firname'] = $data['Person_Firname'] . "%";
        }

        if (strlen($data['Person_Secname']) > 0) {
            $filter .= " and PS.Person_Secname ilike :Person_Secname";
            $queryParams['Person_Secname'] = $data['Person_Secname'] . "%";
        }

        if ($data['Person_Snils'] > 0) {
            $filter .= " and PS.Person_Snils = :Person_Snils";
            $queryParams['Person_Snils'] = $data['Person_Snils'];
        }

        if (strlen($data['Person_Surname']) > 0) {
            $filter .= " and PS.Person_Surname ilike :Person_Surname";
            $queryParams['Person_Surname'] = $data['Person_Surname'] . "%";
        }

        if ($data['PrivilegeType_id'] > 0) {
            $join_str .= "
                inner join v_PersonPrivilege PP on PP.Person_id = EvnPLDispTeen14.Person_id 
                        and PP.PrivilegeType_id = :PrivilegeType_id and PP.PersonPrivilege_begDate is not null 
                        and PP.PersonPrivilege_begDate <= dbo.tzGetDate() 
                        and (PP.PersonPrivilege_endDate is null 
                        or PP.PersonPrivilege_endDate >= cast(to_char(dbo.tzGetDate(), {$this->dateTimeForm112}) as timestamp)) 
                        and PP.Lpu_id = :Lpu_id
            ";
            $queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
            $queryParams['Lpu_id'] = $data['Lpu_id'];
        }

        if ($data['Sex_id'] >= 0) {
            $filter .= " and PS.Sex_id = :Sex_id";
            $queryParams['Sex_id'] = $data['Sex_id'];
        }

        if ($data['SocStatus_id'] > 0) {
            $filter .= " and PS.SocStatus_id = :SocStatus_id";
            $queryParams['SocStatus_id'] = $data['SocStatus_id'];
        }

        $query = "
			SELECT DISTINCT
				EvnPLDispTeen14.EvnPLDispTeen14_id as \"EvnPLDispTeen14_id\",
				EvnPLDispTeen14.Person_id as \"Person_id\",
				EvnPLDispTeen14.Server_id as \"Server_id\",
				EvnPLDispTeen14.PersonEvn_id as \"PersonEvn_id\",
				RTRIM(PS.Person_Surname) as \"Person_Surname\",
				RTRIM(PS.Person_Firname) as \"Person_Firname\",
				RTRIM(PS.Person_Secname) as \"Person_Secname\",
				to_char(PS.Person_Birthday, {$this->dateTimeForm104}) as \"Person_Birthday\",
				EvnPLDispTeen14.EvnPLDispTeen14_VizitCount as \"EvnPLDispTeen14_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPLDispTeen14_IsFinish\",
				to_char(EvnPLDispTeen14.EvnPLDispTeen14_setDate, {$this->dateTimeForm104}) as \"EvnPLDispTeen14_setDate\",
				to_char(EvnPLDispTeen14.EvnPLDispTeen14_disDate, {$this->dateTimeForm104}) as \"EvnPLDispTeen14_disDate\"
			FROM v_EvnPLDispTeen14 EvnPLDispTeen14
				inner join v_PersonState PS on PS.Person_id = EvnPLDispTeen14.Person_id
				left join YesNo IsFinish on IsFinish.YesNo_id = EvnPLDispTeen14.EvnPLDispTeen14_IsFinish
				" . $join_str . "
			WHERE (1 = 1)
				" . $filter . "
			limit 100
		";

        $result = $this->db->query($query, $queryParams);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение списка записей для потокового ввода
     * @param $data
     * @return bool
     */
    public function getEvnPLDispTeen14StreamList($data)
    {

        $query = "
			SELECT DISTINCT
				EvnPLDispTeen14.EvnPLDispTeen14_id as \"EvnPLDispTeen14_id\",
				EvnPLDispTeen14.Person_id as \"Person_id\",
				EvnPLDispTeen14.Server_id as \"Server_id\",
				EvnPLDispTeen14.PersonEvn_id as \"PersonEvn_id\",
				RTRIM(PS.Person_Surname) || ' ' || RTRIM(PS.Person_Firname) || ' ' || RTRIM(PS.Person_Secname) as \"Person_Fio\",
				to_char(PS.Person_Birthday, {$this->dateTimeForm104}) as Person_Birthday,
				EvnPLDispTeen14.EvnPLDispTeen14_VizitCount as \"EvnPLDispTeen14_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPLDispTeen14_IsFinish\",
				to_char(EvnPLDispTeen14.EvnPLDispTeen14_setDate, {$this->dateTimeForm104}) as \"EvnPLDispTeen14_setDate\",
				to_char(EvnPLDispTeen14.EvnPLDispTeen14_disDate, {$this->dateTimeForm104}) as \"EvnPLDispTeen14_disDate\"
			FROM v_EvnPLDispTeen14 EvnPLDispTeen14
				inner join v_PersonState PS on PS.Person_id = EvnPLDispTeen14.Person_id
				left join YesNo IsFinish on IsFinish.YesNo_id = EvnPLDispTeen14.EvnPLDispTeen14_IsFinish
			WHERE
			    EvnPLDispTeen14_updDT >= :EvnPLDispTeen14_updDT::timestamp and EvnPLDispTeen14.pmUser_updID = :pmUser_updID
			limit 100
		";

        $result = $this->db->query($query, ['EvnPLDispTeen14_updDT' => $data['begDate'] . " " . $data['begTime'], 'pmUser_updID' => $data['pmUser_id']]);

        if (!is_object($result)) {
            return false;
        }

        return $result->result('array');
    }

    /**
     * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
     */
    public function getEvnPLDispTeen14Years($data)
    {
        $sql = "
			SELECT
				count(EvnPLDispTeen14_id) as count,
				date_part('year', EvnPLDispTeen14_setDT) as \"EvnPLDispTeen14_Year\"
			FROM
				v_EvnPLDispTeen14
			WHERE
				Lpu_id = :Lpu_id
				and Person_id is not null
			GROUP BY
				date_part('year', EvnPLDispTeen14_setDT)
			ORDER BY
				date_part('year', EvnPLDispTeen14_setDT)
		";

        $res = $this->db->query($sql, ['Lpu_id' => $data['Lpu_id']]);
        if (!is_object($res))
            return false;

        return $res->result('array');
    }

    /**
     * Проверка, есть ли талон на этого человека в этом году
     * @param $data
     * @return array|bool
     */
    public function checkIfEvnPLDispTeen14Exists($data)
    {
        $sql = "
			SELECT
				count(EvnPLDispTeen14_id) as count
			FROM
				v_EvnPLDispTeen14
			WHERE
				Person_id = :Person_id
		";

        $res = $this->db->query($sql, ['Person_id' => $data['Person_id']]);
        if (!is_object($res)) {
            return false;
        }

        $sel = $res->result('array');
        if ($sel[0]['count'] == 0)
            return [['Error_Msg' => '', 'isEvnPLDispTeen14Exists' => false]];
        else
            return [['Error_Msg' => '', 'isEvnPLDispTeen14Exists' => true]];
    }

    /**
     * @param $data
     * @return array
     */
    public function getEvnPLDispTeen14PassportFields($data)
    {
        $dt = [];
        $person_id = 0;

        $sql = "
			SELECT 
				dt14.EvnPLDispTeen14_setDT as \"EvnPLDispTeen14_setDT\",
				dt14.Person_id as \"Person_id\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Person_SurName as \"Person_SurName\",
				date_part('day', ps.Person_BirthDay) as \"Person_BirthDay_Day\",
				date_part('month', ps.Person_BirthDay) as \"Person_BirthDay_Month\",
				date_part('year', ps.Person_BirthDay) as \"Person_BirthDay_Year\",
				ua.Address_House as \"Address_House\",
				ua.Address_Corpus as \"Address_Corpus\",
				ua.Address_Flat as \"Address_Flat\",
				ua.KLStreet_Name as \"KLStreet_Name\",
				(
                    ua.KLRGN_Name || ' ' || ua.KLRGN_Socr
                    || coalesce(', ' || ua.KLCity_Socr || ' ' || ua.KLCity_Name,'')
                    || coalesce(', ' || ua.KLTown_Socr || ' '|| ua.KLTown_Name,'')
				) as \"Address_Info\",
				l.Lpu_Name as \"Lpu_Name\",
				l.Org_Phone as \"Org_Phone\"
			FROM 
				v_EvnPLDispTeen14 dt14
				inner join v_PersonState ps on ps.Person_id = dt14.Person_id
				left join v_Address_all ua on ua.Address_id = ps.UAddress_id
				left join v_Lpu_all l on l.Lpu_id = dt14.Lpu_id
			where
				EvnPLDispTeen14_id = :EvnPLDispTeen14_id
		";

        $res = $this->db->query($sql, ['EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id']]);
        if (is_object($res)) {
            $res = $res->result('array');
            if (is_array($res) && count($res)) {
                $dt = array_merge($dt, $res[0]);
                if (isset($res[0]['Person_id']) && $res[0]['Person_id'] != '')
                    $person_id = $res[0]['Person_id'];
            } else {
                return false;
            }
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
				to_char(EUDT14.EvnUslugaDispTeen14_setDate, {$this->dateTimeForm104}) as date
			from v_EvnUslugaDispTeen14 EUDT14
				inner join EvnUslugaDispTeen14 EUD on EUD.EvnUslugaDispTeen14_id = EUDT14.EvnUslugaDispTeen14_id	
				left join EvnUslugaRate EUR on EUR.EvnUsluga_id = EUD.EvnUsluga_id
				left join Rate R on R.Rate_id = EUR.Rate_id
				left join RateType RT on RT.RateType_id = R.RateType_id
				left join RateValueType RVT on RVT.RateValueType_id = RT.RateValueType_id
				left join RateValue RV on RV.RateType_id = RT.RateType_id and RV.RateValue_id = R.Rate_ValueInt and RVT.RateValueType_SysNick = 'reference'
			where
				EUDT14.EvnUslugaDispTeen14_pid = :EvnPLDispTeen14_id
            and 
                RT.RateType_SysNick is not null
            ORDER BY
                RT.RateType_SysNick,
                EUDT14.EvnUslugaDispTeen14_setDate
            DESC
		";

        $res = $this->db->query($sql, ['EvnPLDispTeen14_id' => $data['EvnPLDispTeen14_id']]);
        $dt['usluga_rate'] = [];
        if (is_object($res)) {
            $res = $res->result('array');
            $rate = [];
            foreach ($res as $row) {
                $nick = $row['nick'];
                if (!isset($rate[$nick]))
                    $rate[$nick] = [];
                if (count($rate[$nick]) < 4)
                    array_unshift($rate[$nick], ['date' => $row['date'], 'value' => $row['value']]);
            }
            $dt['usluga_rate'] = $rate;
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
				to_char(PM.PersonMeasure_setDT, {$this->dateTimeForm104}) as date,
				date_part('year', PM.PersonMeasure_setDT) as year
			from v_PersonMeasure PM
				inner join PersonRate PR on PR.PersonMeasure_id = PM.PersonMeasure_id
				left join Rate R on R.Rate_id = PR.Rate_id
				left join RateType RT on RT.RateType_id = R.RateType_id
				left join RateValueType RVT on RVT.RateValueType_id = RT.RateValueType_id
				left join RateValue RV on RV.RateType_id = RT.RateType_id and RV.RateValue_id = R.Rate_ValueInt and RVT.RateValueType_SysNick = 'reference'		
			where
				PM.Person_id = :Person_id
				and RT.RateType_SysNick is not null
				ORDER BY RT.RateType_SysNick, year DESC, PM.PersonMeasure_setDT ASC
		";
        $res = $this->db->query($sql, ['Person_id' => $person_id]);
        $dt['person_rate'] = [];
        if (is_object($res)) {
            $res = $res->result('array');
            $rate = [];
            foreach ($res as $row) {
                $nick = $row['nick'];
                if (!isset($rate[$nick]))
                    $rate[$nick] = [];
                $rate[$nick][$row['year']] = $row['value'];
                $rate[$nick]['last_value'] = $row['value'];
            }
            $dt['person_rate'] = $rate;
        }

        $sql = "
			select
				EPDT14.EvnPLDispTeen14_IsFinish as \"EvnPLDispTeen14_IsFinish\",
				HK.HealthKind_Name as value,
				to_char(EVDT14.EvnVizitDispTeen14_setDate, {$this->dateTimeForm104}) as date,
				date_part('year', EVDT14.EvnVizitDispTeen14_setDate) as year
			from
			    v_EvnPLDispTeen14 EPDT14
				inner join v_EvnVizitDispTeen14 EVDT14 on EVDT14.EvnVizitDispTeen14_pid = EPDT14.EvnPLDispTeen14_id
				left join Teen14DispSpecType DT14S on DT14S.Teen14DispSpecType_id = EVDT14.Teen14DispSpecType_id
				left join HealthKind HK on HK.HealthKind_id = EVDT14.HealthKind_id	
			where
				EPDT14.Person_id = :Person_id
            and
                DT14S.Teen14DispSpecType_Code = 1
            and
                EPDT14.EvnPLDispTeen14_IsFinish = 2
            ORDER BY year, date desc 
		";
        $res = $this->db->query($sql, ['Person_id' => $person_id]);
        $dt['health_groups'] = [];
        if (is_object($res)) {
            $res = $res->result('array');
            $groups = [];
            foreach ($res as $row) {
                $year = $row['year'];
                $groups[$year] = ['date' => $row['date'], 'value' => $row['value']];
            }
            $dt['health_groups'] = $groups;
        }

        //recommendations
        $sql = "
			select
				DT14S.Teen14DispSpecType_Code as spec,
				EVDT14.EvnVizitDispTeen14_Descr as value,
				to_char(EVDT14.EvnVizitDispTeen14_setDate, {$this->dateTimeForm104}) as date,
				date_part('year', EVDT14.EvnVizitDispTeen14_setDate) as year
			from
			    v_EvnPLDispTeen14 EPDT14
				inner join v_EvnVizitDispTeen14 EVDT14 on EVDT14.EvnVizitDispTeen14_pid = EPDT14.EvnPLDispTeen14_id
				left join Teen14DispSpecType DT14S on DT14S.Teen14DispSpecType_id = EVDT14.Teen14DispSpecType_id
			where
				EPDT14.Person_id = :Person_id
				ORDER BY spec, year, date desc 
		";
        $res = $this->db->query($sql, ['Person_id' => $person_id]);
        $dt['recommendations'] = [];
        if (is_object($res)) {
            $res = $res->result('array');
            $rec = [];
            foreach ($res as $row) {
                $rec[$row['spec']][$row['year']] = $row['value'];
            }
            $dt['recommendations'] = $rec;
        }

        //diseases
        $sql = "
			select	
				D.Diag_Code as code,
				D.Diag_Name as name,
				to_char(EVDT14.EvnVizitDispTeen14_setDate, {$this->dateTimeForm104}) as date,
				date_part('year', EVDT14.EvnVizitDispTeen14_setDate) as year
			from
			    v_EvnPLDispTeen14 EPDT14
				inner join v_EvnVizitDispTeen14 EVDT14 on EVDT14.EvnVizitDispTeen14_pid = EPDT14.EvnPLDispTeen14_id
				left join v_Diag D on D.Diag_id = EVDT14.Diag_id
			where
				EPDT14.Person_id = :Person_id
            and
                SUBSTRING(D.Diag_Code, 1, 1) != 'Z'
            ORDER BY year, date desc
		";
        $res = $this->db->query($sql, ['Person_id' => $person_id]);
        $dt['diseases'] = [];
        if (is_object($res)) {
            $res = $res->result('array');
            $rec = [];
            foreach ($res as $row) {
                $rec[$row['year']][] = [
                    'date' => $row['date'],
                    'name' => $row['name'],
                    'code' => $row['code']
                ];
            }
            $dt['diseases'] = $rec;
        }

        return $dt;
    }
}