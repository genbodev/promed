<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * ONMKRegister - модель для регистра ОНМК
 * @author			Bondarev Valery
 * @version			13.01.2020
 */

class ONMKRegister_model extends swPgModel
{
    /**
     * comments
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     *  Проверка записи пациента в PersonRegister
     */
    function checkPersonRegister($data)
    {
        $params = array(
            'Person_id' => $data['Person_id'],
            'MorbusType_id' => $data['MorbusType_id']
        );

        $query = "select 
                    Person_id as \"Person_id\", 
                    MorbusType_id as \"MorbusType_id\"
                  from PersonRegister
                  where Person_id = :Person_id
                    and MorbusType_id = :MorbusType_id                   
        ";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            $dataInDB = $result->result('array');

            if (!empty($dataInDB)) {
                return false;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Добавления пациента в PersonRegister
     */
    function savePersonRegister($data)
    {

        $params = array(
            'PersonRegister_id' => $data['PersonRegister_id'],
            'Person_id' => $data['Person_id'],
            'MorbusType_id' => $data['MorbusType_id'],
            'Diag_id' => $data['Diag_id'],
            'PersonRegister_Code' => $data['PersonRegister_Code'],
            'PersonRegister_setDate' => $data['PersonRegister_setDate'],
            'PersonRegister_disDate' => $data['PersonRegister_disDate'],
            'Morbus_id' => $data['Morbus_id'],
            'PersonRegisterOutCause_id' => $data['PersonRegisterOutCause_id'],
            'MedPersonal_iid' => $data['MedPersonal_iid'],
            'Lpu_iid' => $data['Lpu_iid'],
            'MedPersonal_did' => $data['MedPersonal_did'],
            'Lpu_did' => $data['Lpu_did'],
            'EvnNotifyBase_id' => $data['EvnNotifyBase_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        //echo '<pre>' . print_r($params, 1) . '</pre>';
        $query = "
            select 
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Message\"
            from dbo.p_PersonRegister_ins (
                  Person_id := :Person_id,
                  MorbusType_id := :MorbusType_id,
                  Diag_id := :Diag_id,
                  PersonRegister_Code := :PersonRegister_Code,
                  PersonRegister_setDate := :PersonRegister_setDate,
                  MedPersonal_iid := :MedPersonal_iid,
                  Lpu_iid := :Lpu_iid,
                  EvnNotifyBase_id := :EvnNotifyBase_id,
                  pmUser_id := :pmUser_id,
                  MedPersonal_did := :MedPersonal_did,
                  Lpu_did := :Lpu_did,
                  Morbus_id := :Morbus_id,
                  PersonRegisterOutCause_id := :PersonRegisterOutCause_id
                  );";
        //echo getDebugSql($query, $params);
        //exit;

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
        //}
    }

    /**
     * Добавление/редактирование случая ОНМК из карточки КВС
     */
    function saveOnmkFromKvc($data) {

        //echo print_r($data, 1);exit;

        //Ext.isEmpty переписать
        if(is_null($data['Person_id']) || $data['Person_id'] == ''){
            return array('Error_Msg' => 'Ошибка сохранения ОНМК! Не определен пациент!');
        }

        $data['MorbusType_id'] = 100;

        $data['ONMKRegistry_IsNew'] = 1;
        $data['ONMKRegistry_IsMonitor'] = 1;
        $data['ONMKRegistry_IsConfirmed'] = 1;

        $query = "
            select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"

			from dbo.p_ONMKRegistry_ins (
				ONMKRegistry_id := NULL,
			    Person_id := :Person_id,
				ONMKRegistry_IsNew := :ONMKRegistry_IsNew,
				ONMKRegistry_IsMonitor := :ONMKRegistry_IsMonitor,
				ONMKRegistry_IsConfirmed := :ONMKRegistry_IsConfirmed,				
			    ONMKRegistry_NumKVC := :EvnPS_NumCard,
				Diag_id := :Diag_id,
				lpu_id := :Lpu_id,
				
				ONMKRegistry_MRTDT := :MRTDT,
				ONMKRegistry_KTDT := :KTDT,
				ONMKRegistry_TLTDT := :TLTDT,
				
				ONMKRegistry_Evn_setDT := :LpuDT,
				ONMKRegistry_Evn_DTDesease := :PainDT,
								
				LpuSection_pid := :LpuSection_pid,
				MedStaffFact_pid := :MedStaffFact_pid,				
				RankinScale_id := :RankinScale_id,
				RankinScale_sid := :RankinScale_sid,
				EvnSection_InsultScale := :EvnSection_InsultScale,
				LeaveType_id := :LeaveType_id,
				EvnSection_id := :evn_section_id,
				EvnPS_id := :EvnPS_id,
				
				pmUser_updID := :pmUser_id
				);
			";

        $result = $this->db->query( $query, $data );
        $result = $result->result( 'array' );

        return $result;
    }

    /**
     *  Получение идентификатора регистра ОНМК
     */
    function getONMKRegistry_id($data) {

        $params = array(
            'Person_id' => $data['Person_id'],
            'EvnPS_NumCard' => $data['EvnPS_NumCard']
        );

        $query = "			
				SELECT
                        P.Person_id as \"Person_id\",
                        P.Person_deadDT as \"Person_deadDT\",
                        ONMK.ONMKRegistry_id as \"ONMKRegistry_id\"
                    FROM
                        v_Person P
                        left join lateral (select ONMKRegistry_id from ONMKRegistry where  ONMKRegistry_NumKVS = :EvnPS_NumCard) ONMK on true
                    WHERE
                        P.Person_id = :Person_id
                    LIMIT 1
        ";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * загрузка случаев ОНМК
     */
    function loadSluch($data) {
        $q = "
			SELECT onmk.ONMKRegistry_id as \"ID\", 
			        onmk.ONMKRegistry_id as \"ONMKRegistry_id\", 
			        (case when evnps.Evn_deleted = 2 then '<font color=#aaa>' else '<font>' end) 
			        || to_char(onmk.ONMKRegistry_EvnDT,'dd.mm.yyyy') || ' ' 
			        || substring(to_char(onmk.ONMKRegistry_EvnDT,'hh24:mi:ss'), 1, 5) 
			        || ' (' || dia.diag_code || ')' || '</font>'  as \"ONMKRegistry_SetDate\"
			FROM v_ONMKRegistry onmk, v_PersonRegister pr, v_diag dia, v_EvnPS_Del evnps
			WHERE 
				onmk.PersonRegister_id=pr.PersonRegister_id and dia.diag_id=onmk.diag_id
				and onmk.ONMKRegistry_deleted = 1 and onmk.EvnPS_id=evnps.EvnPS_id
				and pr.Person_id = :Person_id
			ORDER BY ONMKRegistry_SetDate DESC
		";
        //echo getDebugSQL($q, array('PersID' => $data['PersID']));exit;
        $result = $this->db->query($q, array('Person_id' => $data['Person_id']));
        if ( is_object($result) ) {
            return $result->result('array');

            //return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * Загрузка данных случая
     */
    function loadSluchData($data) {

        $q = "
                    SELECT 
					onmk.ONMKRegistry_id as \"ONMKRegistry_id\", 					
                    onmk.lpu_id as \"lpu_id\",
					(SELECT Org.Org_Phone 	FROM v_Lpu Lpu left join v_Org as Org on Lpu.Org_id = Org.Org_id WHERE Lpu.Lpu_id = '35' LIMIT 1) as \"Lpu_Phone\",
					onmk.Diag_id as \"Diag_id\",
					Age(PS.Person_BirthDay, onmk.ONMKRegistry_EvnDT) \"Person_Year\",
					to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
					(to_char(ONMK.ONMKRegistry_EvnDTDesease, 'dd.mm.yyyy') || ' ' || to_char(ONMK.ONMKRegistry_EvnDTDesease, 'hh24:mi:ss')) as \"ONMKRegistry_Evn_DTDesease\",					
					(case when ONMK.ONMKRegistry_EvnDT = null then '' when ONMK.ONMKRegistry_EvnDTDesease = null then ''
					else GetPeriodName(ONMK.ONMKRegistry_EvnDTDesease, ONMK.ONMKRegistry_EvnDT)
					end) 					
					as \"TimeBeforeStac\",
					(to_char(ONMK.ONMKRegistry_EvnDT, 'dd.mm.yyyy') || ' ' || to_char(ONMK.ONMKRegistry_EvnDT, 'hh24:mi:ss')) as \"ONMKRegistry_Evn_setDT\", 
					(to_char(ONMK.ONMKRegistry_insDT, 'dd.mm.yyyy') || ' ' || to_char(ONMK.ONMKRegistry_insDT, 'hh24:mi:ss')) as \"ONMKRegistry_insDT\",
					ONMK.LpuSection_pid as \"LpuSection_pid\",
					ONMK.MedStaffFact_pid as \"MedStaffFact_pid\",
					ONMK.ONMKRegistry_NumKVS as \"EvnPS_NumCard\",
					RS.RankinScale_code as \"RankinScale_Name\",
					RSS.RankinScale_code as \"RankinScale_Name_s\",					
					ONMK.ONMKRegistry_InsultScale as \"ONMKRegistry_InsultScale\",
					GetReanimat(ONMK.EvnPS_id, 1) as \"ConsciousType\",
					GetReanimat(ONMK.EvnPS_id, 2) as \"BreathingType\",
					GetONMKMO(onmk.Lpu_id) as \"MO_OK\",
					(to_char(ONMK.ONMKRegistry_SetDate, 'dd.mm.yyyy') || ' ' || to_char(ONMK.ONMKRegistry_SetDate, 'hh24:mi:ss')) as \"ONMKRegistry_SetDate\",
					ONMK.EvnPS_id as \"EvnPS_id\",
					ONMK.ONMKRegistry_NIHSSAfterTLT as \"ONMKRegistry_NIHSSAfterTLT\",
					ONMK.ONMKRegistry_NIHSSLeave as \"ONMKRegistry_NIHSSLeave\"
					FROM v_ONMKRegistry onmk
					inner join v_PersonRegister PR on pr.PersonRegister_id=onmk.PersonRegister_id
					inner join v_PersonState PS on PS.Person_id=PR.Person_id
					left join v_RankinScale RS on RS.RankinScale_id=ONMK.RankinScale_id
					left join v_RankinScale RSS on RSS.RankinScale_id=ONMK.RankinScale_sid
                    WHERE 						
                        onmk.ONMKRegistry_id = :ONMKRegistry_id
                    ORDER BY ONMKRegistry_SetDate DESC
		";
        //echo getDebugSQL($q, array('ONMKRegistry_id' => $data['ONMKRegistry_id']));exit;

        $result = $this->db->query($q, array('ONMKRegistry_id' => $data['ONMKRegistry_id']));
        if ( is_object($result) ) {
            return $result->result('array');
            //return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * загрузка услуг
     */
    function loadEvnUslugaGrid($data) {


        $q = " 		
			select

                 EU.EvnUsluga_id as \"EvnUsluga_id\"
                ,EU.EvnUsluga_pid as \"EvnUsluga_pid\"
                --,ISNULL(UC.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code
                --,ISNULL(UC.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name			
                ,UC.UslugaComplex_Code as \"Usluga_Code\"
                ,UC.UslugaComplex_Name as \"Usluga_Name\"
                ,to_char(EU.EvnUsluga_setDT, 'dd.mm.yyyy') as \"EvnUsluga_setDT\" 
                ,to_char(EU.EvnUsluga_insDT, 'dd.mm.yyyy') as \"EvnUsluga_insDT\"
                ,EU.EvnClass_SysNick as \"EvnClass_SysNick\"
                ,UC.UslugaComplex_id as \"UslugaComplex_id\",


			(select 1 from v_UslugaComplexAttribute UCA
			inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
			where UCA.UslugaComplex_id = UC.UslugaComplex_id and UCAT.UslugaComplexAttributeType_SysNick in ('consult') limit 1) as \"consultAttr\",

			(select 1 from v_UslugaComplexAttribute UCA
			inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
			where UCA.UslugaComplex_id = UC.UslugaComplex_id and UCAT.UslugaComplexAttributeType_SysNick in ('oper', 'operblock') limit 1) as \"operAttr\",

			(select 1 from v_UslugaComplexAttribute UCA
			inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
			where UCA.UslugaComplex_id = UC.UslugaComplex_id and UCAT.UslugaComplexAttributeType_SysNick in ('lab', 'func', 'lazer', 'ray', 'registry', 'xray') limit 1) as \"commonAttr\"

			from
				v_EvnUsluga_Del EU
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_EvnPS_Del EPS on EPS.EvnPS_id = EU.EvnUsluga_rid
				left join v_org org on org.org_id = EU.Org_uid
				left join v_Evn_delonmk ParentEvn on ParentEvn.Evn_id = EU.EvnUsluga_pid
				left join v_Lpu L inner join v_org org1 on org1.Org_id=l.Org_id on L.Lpu_id = EU.Lpu_id
			left join lateral (
					select
						MP.MedPersonal_id,
						MP.Person_FIO as MedPersonal_FIO
					from v_MedPersonal MP
					where MP.MedPersonal_id = EU.MedPersonal_id
					limit 1
				) MP on true
			where
				--EPS.EvnPS_id=:EvnPS_id
				EU.Person_id=(select pr.person_id from v_ONMKRegistry onmk1, v_PersonRegister pr where onmk1.EvnPS_id=:EvnPS_id and onmk1.personregister_id=pr.personregister_id)
				And (EPS.EvnPS_id=:EvnPS_id or EPS.EvnPS_id is null)
				and COALESCE(ParentEvn.EvnClass_SysNick, '') not like 'EvnUsluga%'
				
				and exists(
					select 1 from v_UslugaComplexAttribute UCA
					inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
					where UCA.UslugaComplex_id = UC.UslugaComplex_id and UCAT.UslugaComplexAttributeType_SysNick in ('consult', 'oper', 'operblock', 'lab', 'func', 'lazer', 'ray', 'registry', 'xray')
				)
				and datediff('day', (select ONMKRegistry_EvnDT from v_ONMKRegistry where EvnPS_id=:EvnPS_id limit 1), EU.EvnUsluga_setDate) >= 0				
				and (datediff('day', (select evnps1.EvnPS_disDT from v_EvnPS_Del evnps1 where evnps1.EvnPS_id=:EvnPS_id), EU.EvnUsluga_setDate) <= 0 or (select evnps1.EvnPS_disDT from v_EvnPS_Del evnps1 where evnps1.EvnPS_id=:EvnPS_id) is null)
				and eu.Evn_deleted=1 and eu.EvnUsluga_setDT is not null
				-- end where
			order by
				-- order by
				EU.EvnUsluga_setDate		
		";

        $result = $this->db->query($q, array('EvnPS_id' => $data['EvnPS_id']));
        if ( is_object($result) ) {
            return $result->result('array');
            //return $result;
        } else {
            return false;
        }
    }

    /**
     *
     * сохранение статуса случая ОНМК
     */
    function saveONMKStatus($data) {

        //echo print_r($data, 1);exit;

        //Ext.isEmpty переписать
        if(is_null($data['ONMKRegistry_id']) || $data['ONMKRegistry_id'] == ''){
            return array('Error_Msg' => 'Ошибка сохранения ОКС! Не определен случай!');
        }

        $data['ONMKRegistry_IsNew'] = 0;
        //$data['ONMKRegistry_IsMonitor'] = 1;
        //$data['ONMKRegistry_IsConfirmed'] = 1;

        $query = "
            select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
            from dbo.p_ONMKRegistry_ins (
				ONMKRegistry_id := :ONMKRegistry_id,
			    Person_id := NULL,
				ONMKRegistry_IsNew := 2,
				ONMKRegistry_IsMonitor := NULL,
				ONMKRegistry_IsConfirmed := NULL,				
			    ONMKRegistry_NumKVC := NULL,
				Diag_id := NULL,
				lpu_id := NULL,
				
				ONMKRegistry_MRTDT := NULL,
				ONMKRegistry_KTDT := NULL,
				ONMKRegistry_TLTDT := NULL,
				
				ONMKRegistry_Evn_setDT := NULL,
				ONMKRegistry_Evn_DTDesease := NULL,
								
				LpuSection_pid := NULL,
				MedStaffFact_pid := NULL,				
				RankinScale_id  := NULL,
				RankinScale_sid  := NULL,
				EvnSection_InsultScale := NULL,
				LeaveType_id := NULL,
				EvnSection_id := NULL,
				EvnPS_id := NULL,
				
				pmUser_updID := NULL
				);";

        $result = $this->db->query( $query, $data );
        $result = $result->result( 'array' );

        return '';
    }

    /**
     * загрузка уточненных диагнозов
     */
    function getDiagList($data){
        $where = '';
        $query = "
                    with EvnDiag(
                            EvnClass_SysNick,
                            spec_id,
                            Person_id,
                            Diag_id,
                            Diag_setDate,
                            Lpu_id
                            ,MedPersonal_id
                            ,LpuSection_id
                    ) as (
                            select 
                                    'EvnSection',
                                    0,
                                    Person_id,
                                    Diag_id,
                                    EvnSection_setDate,
                                    Lpu_id
                                    ,MedPersonal_id
                                    ,LpuSection_id
                            from v_EvnSection
                            where
                                    Person_id = :Person_id
                                    and Diag_id is not null
                            union all
                            select 
                                    'EvnVizitPL',
                                    0,
                                    Person_id,
                                    Diag_id,
                                    EvnVizitPL_setDate,
                                    Lpu_id
                                    ,MedPersonal_id
                                    ,LpuSection_id
                            from v_EvnVizitPL EVPL
                            where
                                    Person_id = :Person_id
                                    and Diag_id is not null
                            union all
                            select
                                    'EvnDiagPLSop',
                                    0,
                                    EDL.Person_id,
                                    EDL.Diag_id,
                                    EDL.EvnDiagPLSop_setDate,
                                    EDL.Lpu_id
                                    ,ev.MedPersonal_id
                                    ,LpuSection_id
                            from v_EvnDiagPLSop EDL
                            left join v_EvnVizit ev on EDL.EvnDiagPLSop_pid=ev.EvnVizit_id
                            where 
                                    EDL.Person_id = :Person_id
                                    and EDL.Diag_id is not null
                            union all
                            select
                                    'EvnDiagPS',
                                    0,
                                    eds.Person_id,
                                    eds.Diag_id,
                                    EDS.EvnDiagPS_setDate,
                                    eds.Lpu_id
                                    ,es.MedPersonal_id
                                    ,LpuSection_id
                            from v_EvnDiagPS EDS
                            left join v_EvnSection es on EDS.EvnDiagPS_pid=es.EvnSection_id
                            where 
                                    eds.Person_id = :Person_id
                                    and eds.Diag_id is not null
                            union all
                            select
                                    'EvnDiagSpec',
                                    eds.EvnDiagSpec_id,
                                    eds.Person_id,
                                    eds.Diag_id,
                                    EDS.EvnDiagSpec_didDT,
                                    eds.Lpu_id
                                    ,0
                                    ,0
                            from v_EvnDiagSpec EDS
                            where 
                                    eds.Person_id = :Person_id
                                    and eds.Diag_id is not null
                            union all
                            select
                             'EvnVizitDispDop',
                                    0,
                                    EVDD.Person_id,
                                    EVDD.Diag_id,
                                    EVDD.EvnVizitDispDop_setDate,
                                    EVDD.Lpu_id
                                    ,EVDD.MedPersonal_id
                                    ,EVDD.LpuSection_id
                            from v_EvnUslugaDispDop EVNU
                            inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EVNU.EvnUslugaDispDop_pid
                            inner join v_Diag diag on diag.Diag_id=EVDD.Diag_id
                            left join v_DopDispInfoConsent DDIC on EVDD.DopDispInfoConsent_id=DDIC.DopDispInfoConsent_id
                            left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id=DDIC.SurveyTypeLink_id
                            where EVNU.Person_id=:Person_id and STL.SurveyType_id=19 and EVDD.DopDispDiagType_id=2 and diag.Diag_Code not like 'Z%'
                            union all
                            select
                                    'EvnDiagDopDisp',
                                    0,
                                    EDDD.Person_id,
                                    EDDD.Diag_id,
                                    EDDD.EvnDiagDopDisp_setDate,
                                    EDDD.Lpu_id
                                    ,0
                                    ,0
                            from
                            v_EvnDiagDopDisp EDDD
                    where
                            (1=1) and EDDD.Person_id = :Person_id and EDDD.DeseaseDispType_id = '2'
                    )

                    select
                            ED.EvnClass_SysNick as \"EvnClass_SysNick\",
                            ED.Person_id as \"Person_id\",
                            Ed.spec_id as \"spec_id\",
                            ED.Person_id as \"pid\",
                            0 as \"Children_Count\",
                            ED.Lpu_id as \"Lpu_id\",
                            --ED.Evn_id as Diag_pid,
                            ED.Diag_id as \"Diag_id\",
                            ED.Diag_id as \"DiagList_id\",
                            to_char(ED.Diag_setDate, 'dd.mm.yyyy') as \"Diag_setDate\",
                            RTRIM(COALESCE(Diag.Diag_Code, '')) as \"Diag_Code\",
                            RTRIM(COALESCE(Diag.Diag_Name, '')) as \"Diag_Name\",
                            case ED.spec_id when 0 then RTRIM(COALESCE(Lpu.Lpu_Nick, '')) else EDS.EvnDiagSpec_Lpu end as \"Lpu_Nick\"
                            ,case ED.spec_id when 0 then RTRIM(COALESCE(MP.Person_Fio, '')) else COALESCE(EDS.EvnDiagSpec_MedWorker, MSF.Person_Fio) end as \"MedPersonal_Fio\"
                            ,case ED.spec_id when 0 then COALESCE(LS.LpuSectionProfile_Name, '') else EDS.EvnDiagSpec_LpuSectionProfile end as \"LpuSectionProfile_Name\"
                    from EvnDiag ED
                            left join v_Diag as Diag on Diag.Diag_id = ED.Diag_id
                            left join v_Lpu Lpu on Lpu.Lpu_id = ED.Lpu_id
                            left join v_EvnDiagSpec EDS on ED.spec_id = EDS.EvnDiagSpec_id
                            left join v_MedStaffFact MSF on MSF.MedStaffFact_id = EDS.MedStaffFact_id
                            left join v_MedPersonal MP on MP.MedPersonal_id = ED.MedPersonal_id and MP.Lpu_id = ED.Lpu_id
                            left join v_LpuSection LS on LS.LpuSection_id = ED.LpuSection_id
                    where (1=1) ".$where."
                    order by
                            ED.Diag_setDate";


        $result = $this->db->query($query, array(
            'Person_id' => $data['Person_id']
        ));

        if ( is_object($result) ){
            $resp = $result->result('array');
            $diagArr = array();
            $respfiltered = array();
            foreach($resp as $respone){
                // фильтруем одинаковые диагнозы в посещениях
                if (!in_array($respone['Diag_id'], $diagArr)) {
                    $diagArr[] = $respone['Diag_id'];
                    $respfiltered[] = $respone;
                }
            }
            /*if(!$isKz){
                return swFilterResponse::filterNotViewDiag($respfiltered, $data);
            }*/
            $diagArray=Array();
            $res=Array();
            foreach($respfiltered as $val){
                if(!in_array($val['Diag_id'],$diagArray)){
                    if($val['spec_id']>0){
                        if($val['MedPersonal_Fio']!=''){
                            $val['LpuSectionProfile_Name']='<a id="DiagList_'.$val["Diag_id"].'_'.$val["spec_id"].'_viewDiag">'.$val['MedPersonal_Fio'].'</a>';
                        }else{
                            $val['LpuSectionProfile_Name']='<a id="DiagList_'.$val["Diag_id"].'_'.$val["spec_id"].'_viewDiag">'.'Просмотр'.'</a>';
                        }
                    }
                    $res[]=$val;
                    $diagArray[]=$val['Diag_id'];
                }
            }
            return $res;
        }
        else
            return false;

    }

    /**
     * обновление признаков Подтвержден и Мониторинг
     */
    function updateSluchData() {
        $params = array();
        $query = "
            select 
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
            from p_ONMKRegistry_update();
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

}
?>