<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Aparin Lew
* @version      27.09.2009
*/

class Eco_model extends swModel
{

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
        
    /**
     * изменение/добавление случая ЭКО
     */
    function ecoChange($data) 
    { 
            $q = " 
            declare 
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :ecoID;
            exec p_PersonRegisterEco_upd
				@PersonRegisterEco_id = @Res output,
				@Person_id = :PersID, 
				@PersonRegisterEco_AddDate = :dateAdd, 
				@EcoOplodType_id = :vOplod, 
				@PayType_id = :vOplat, 
				@PersonRegisterEco_IsGeneting = :genDiag, 
				@EmbrionCount_id = :embrCount, 
				@EcoResultType_id = :res,
				@PersonRegisterEco_ResultDate = :resDate, 
				@Diag_id = :ds, 
				@Diag_oid = :dsOsn,
				@EcoPregnancyType_id = :vidBer,
				@EcoChildCountType_id = :countPlod,
				@olsognen = :osl,
				@pmUser_updID = :pUser,
				@lpu_id = :lpu,
				@MedPersonal_sid = :MedPersonal_sid,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as PersonRegisterEco_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
            "; 

            $p = array( 
                'ecoID' => $data['s_eco_id'], 
                'PersID' => $data['s_pers_id'], 
                'dateAdd' => $data['s_dateAdd'], 
                'vOplod' => $data['s_vid_oplod'], 
                'vOplat' => $data['s_vid_oplat'], 
                'genDiag' => $data['s_gen_diag'], 
                'embrCount' => $data['s_count_embrion'], 
                'res' => $data['s_res_eco'], 
                'resDate' => $data['s_res_date'], 
                'ds' => $data['s_ds_eco'], 
                'osl' => $data['s_oslognen'],
                'pUser' => $data['s_pmUser'],
                'dsOsn'=> $data['dsOsn'],
                'vidBer'=> $data['vidBer'],
                'countPlod'=> $data['countPlod'],
                'lpu'=>$data['lpu'],
				'MedPersonal_sid'=>$data['MedPersonal_sid']

            ); 

            $r = $this->db->query($q, $p); 
            return array(
                            'Error_Msg' => '',
                            'success' => true
                        );
    }
    
    /**
     * добавить услугу
     */
    function addEcoUsl($data) 
    { 
		$proc = 'dbo.p_EcoUsluga_upd';
		
		if ($data['EcoUsluga_id'] == ''){
			$proc = 'p_EcoUsluga_ins';
		}
		
        $q = " 
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :EcoUsluga_id;

			exec ".$proc."
				@Person_id = :persID, 
				@EcoUsluga_uslDate = :dateUsl, 
				@UslugaComplex_id  = :codeUsl, 
				@pmUser_updID = :pUser,
				@EcoUsluga_id = @Res output
			select @Res as EcoUsluga_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
        "; 

        $p = array( 
            'persID' => $data['persID'], 
            'dateUsl' => $data['dateUsl'], 
            'codeUsl' => $data['codeUsl'], 
            'pUser' => $data['pmUser'],
			'EcoUsluga_id' => $data['EcoUsluga_id']

        ); 

        $r = $this->db->query($q, $p); 
        return array(
                        'Error_Msg' => '',
                        'success' => true
                    );
    }
        
    /**
     * Удалить услугу
     */
    function delEcoUsl($data) 
    { 
            $q = " 
            declare                
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :EcoUsluga_id;

            exec dbo.p_EcoUsluga_del
				@EcoUsluga_id = @Res output,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as EcoUsluga_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				
            "; 

            $p = array( 
                'EcoUsluga_id' => $data['uslId']                    
            ); 

            $r = $this->db->query($q, $p); 
            return array(
                            'Error_Msg' => '',
                            'success' => true
                        );
    }
        
    /**
     * Проверка последнего результата
     */
    function checkLastRes($data) { 
        $q = "
                   SELECT count ( * ) no_res
                   FROM    [dbo].[PersonRegister] r
                        RIGHT JOIN
                           [dbo].[PersonRegisterEco] re
                        ON re.PersonRegister_id = r.PersonRegister_id
                  WHERE re.EcoResultType_id IS NULL AND r.Person_id = :Pers_id               
           ";
        $result = $this->db->query($q, array('Pers_id' => $data['Pers_id'])); 
        if ( is_object($result) ) { 
            return $result->result('array'); 

            //return $result; 
        } else {
            return false;
        } 
    }
    
     /**
      * загрузка случаев ЭКО
     */
    function loadEcoSluch($data) { 
        $q = "
                    SELECT PersonRegisterEco_id as Eco_id, Convert(varchar,PersonRegisterEco_AddDate,104) as DateAdd, 
					PersonRegisterEco_AddDate as dSort, lpu_nick as lpu_nick, Convert(varchar,PersonRegisterEco_ResultDate,104) as PersonRegisterEco_ResultDate, EcoResultType_Name as EcoResultType_Name, lpu_id,
					PersonPregnancy_id
                    FROM dbo.v_PersonRegisterEcoSluch
                    WHERE 
                        Person_id = :PersID
						and PersonRegisterEco_deleted=1
                    ORDER BY  dSort   DESC                
            ";
        $result = $this->db->query($q, array('PersID' => $data['PersID'])); 
        if ( is_object($result) ) { 
            return $result->result('array'); 

            //return $result; 
        } else {
            return false;
        } 
    }
    
        
    /**
     * загрузка осложнений
     */
    function loadEcoOsl($data) { 	
		
        $q = "
            SELECT PersonRegisterEcoOslog_id as EcoOsl_id
                ,PersonRegisterEco_id as Eco_id
                ,Convert(varchar, PersonRegisterEcoOslog_OslDate,104) as Date_osl
                , Osl as Osl
                ,EcoOslogType_id as Osl_id
                ,Ds as Ds
                ,Diag_id as Ds_int
            FROM dbo.v_PersonRegisterEcoOsl
            WHERE PersonRegisterEco_id = :EcoID
			order by PersonRegisterEcoOslog_OslDate desc
            ";
        $result = $this->db->query($q, array('EcoID' => $data['Eco_id'])); 
        if ( is_object($result) ) { 
            return $result->result('array'); 

            return $result; 
        } else {
            return false;
        } 
    }

    /**
     * 
     * Загрузка данных случая
     */
    function loadEcoSluchData($data) { 
		$q = "
                SELECT Convert(varchar,RE.PersonRegisterEco_AddDate,104) as DateAdd, RE.EcoOplodType_id as VidOplod, RE.PayType_id as VidOplat, 
				RE.PersonRegisterEco_IsGeneting as GenetigDiag, RE.EmbrionCount_id as EmbrionCount, RE.EcoResultType_id as Result ,Convert(varchar,RE.PersonRegisterEco_ResultDate,104) as ResultDate,
                RE.Diag_id as DS, RE.Diag_oid as DS_osn,	   RE.EcoPregnancyType_id as VidBer,	   RE.EcoChildCountType_id as CountPlod, RE.pmUser_id, RE.lpu_id, RE.PersonRegister_id, RE.Person_id,

				/*Причина исключения из регистра по последней записи регистра ЭКО для персонажа*/				
				(select top 1 PR.PersonRegisterOutCause_id 
				from dbo.PersonRegister PR with (NOLOCK) where 
				PR.PersonRegisterType_id in (SELECT MT.MorbusType_id FROM dbo.MorbusType MT WITH (NOLOCK) WHERE MT.MorbusType_SysNick = 'Pregnancy')
				and PR.Person_id=RE.Person_id
				and (PR.PersonRegisterOutCause_id is null or not PR.PersonRegisterOutCause_id in (2))
				order by PR.PersonRegister_setDate desc) PersonRegisterOutCause_id,

				/*Идентификатор исхода из регистра по последней записи регистра ЭКО для персонажа*/				
				(select top 1 BSS.BirthSpecStac_id
				from dbo.PersonRegister PR with (NOLOCK) left join dbo.BirthSpecStac BSS with (NOLOCK) on BSS.PersonRegister_id = PR.PersonRegister_id where 
				PR.PersonRegisterType_id in (SELECT MT.MorbusType_id FROM dbo.MorbusType MT WITH (NOLOCK) WHERE MT.MorbusType_SysNick = 'Pregnancy')
				and PR.Person_id=RE.Person_id
				and (PR.PersonRegisterOutCause_id is null or not PR.PersonRegisterOutCause_id in (2))
				order by PR.PersonRegister_setDate desc) BirthSpecStac_id,

				/*Идентификатор случая из регистра беременных*/				
				(select top 1 PR.PersonRegister_id
				from dbo.PersonRegister PR with (NOLOCK) where 
				PR.PersonRegisterType_id in (SELECT MT.MorbusType_id FROM dbo.MorbusType MT WITH (NOLOCK) WHERE MT.MorbusType_SysNick = 'Pregnancy')
				and PR.Person_id=RE.Person_id
				and (PR.PersonRegisterOutCause_id is null or not PR.PersonRegisterOutCause_id in (2))
				order by PR.PersonRegister_setDate desc) PregnancyPersonRegister_id,

				/*Идентификатор исхода из РБ по привязанной записи регистра ЭКО для персонажа*/				
				(select BSSL.BirthSpecStac_id from dbo.BirthSpecStac BSSL where BSSL.PersonRegister_id = PP.PersonRegister_id)  BirthSpecStac_id_link,

				/*Причина исключения из РБ привязанной записи  к регистру ЭКО для персонажа*/				
				(select top 1 PR.PersonRegisterOutCause_id 
				from dbo.PersonRegister PR with (NOLOCK) where 
				PR.PersonRegisterType_id in (SELECT MT.MorbusType_id FROM dbo.MorbusType MT WITH (NOLOCK) WHERE MT.MorbusType_SysNick = 'Pregnancy')
				and PR.PersonRegister_id=PP.PersonRegister_id
				and (PR.PersonRegisterOutCause_id is null or not PR.PersonRegisterOutCause_id in (2))
				order by PR.PersonRegister_setDate desc) PersonRegisterOutCause_id_link,

				/*Идентификатор привязанного случая из РБ */	
				PP.PersonRegister_id as PersonRegister_id_link,

				/*Идентификатор исхода, созданного из регистра ЭКО*/
				RE.BirthSpecStac_id BirthSpecStac_id_Create_Eco,

				RE.MedPersonal_id as MedPersonal_id

                FROM dbo.v_PersonRegisterEcoSluchData RE left join dbo.v_PersonPregnancy PP on PP.PersonRegisterEco_id=RE.PersonRegisterEco_id
                WHERE RE.PersonRegisterEco_id = :EcoID
				order by RE.PersonRegisterEco_AddDate desc		
		";
        $result = $this->db->query($q, array('EcoID' => $data['Eco_id'])); 
        if ( is_object($result) ) { 
            return $result->result('array'); 
            //return $result; 
        } else { 
            return false; 
        } 
    }
        
    /**
     * загрузка услуг
     */           
    function loadEcoUsl($data) { 
        $q = "DECLARE @Person_id   bigint =:PersID
                    ,@Date_beg    DATE = :DateUslBeg
                    ,@Date_end    DATE = :DateUslEnd

                 (SELECT distinct 0 Eco_usl_id,
                                    CAST (EU.EvnUsluga_setDate AS date) Date_usl,
                                    Convert(varchar,EU.EvnUsluga_setDate,104) as  DateUslStr,
                                    EU.UslugaComplex_id usl_id,
                                    us.UslugaComplex_Code Code_usl,
                                    us.UslugaComplex_Name Name_usl,
                                    EU.Person_id Person_id,
                                    d.Diag_FullName DS,
                                    Lpu.Lpu_Nick MO,
									'no' del
                    FROM    v_EvnUsluga EU
                                    JOIN
                                    dbo.v_UslugaComplex us
                                            ON us.UslugaComplex_id = EU.UslugaComplex_id
                                    Left JOIN dbo.v_Diag d
                                            on d.Diag_id = EU.Diag_id
                                    LEFT JOIN v_Lpu Lpu WITH (NOLOCK)
                                            ON Lpu.Lpu_id = EU.Lpu_id
                    WHERE (EU.EvnClass_SysNick IN
                                            ('EvnUslugaCommon',
                                                    'EvnUslugaOper',
                                                    'EvnUslugaPregnancySpec',
                                                    'EvnUslugaTelemed')
                                    OR (    EU.EvnClass_SysNick = 'EvnUslugaPar'
                                            AND EU.EvnDirection_id IS NOT NULL
                                            AND EU.EvnUsluga_setDate IS NOT NULL))
                                    AND EU.Person_id = @Person_id
									and US.uslugacategory_id=4
                                    AND (CAST (EU.EvnUsluga_setDate AS date) BETWEEN @Date_beg
                                                                                                                            AND  @Date_end
                                            OR CAST (EU.EvnUsluga_disDate AS date) BETWEEN @Date_beg
                                                                                                                                    AND  @Date_end))
                 UNION ALL
                 (SELECT distinct usl.EcoUsluga_id,
                        usl.EcoUsluga_uslDate,
                        Convert(varchar,EcoUsluga_uslDate,104) as  DateUslStr, 
                        usl.UslugaComplex_id,
                        us.UslugaComplex_Code Code_usl,
                        us.UslugaComplex_Name Name_usl,
                        pr.Person_id,
                        d.Diag_FullName DS,
                        Lpu.Lpu_Nick MO,
						'yes' del
                FROM          dbo.PersonRegisterEco sl
                                JOIN
                                        dbo.PersonRegister pr
                                ON pr.PersonRegister_id = sl.PersonRegister_id and (CAST (sl.PersonRegisterEco_AddDate AS date) BETWEEN @Date_beg AND @Date_end)
                        JOIN
                                dbo.EcoUsluga usl
                        ON usl.Person_id = pr.Person_id
                                 AND (CAST (usl.EcoUsluga_uslDate AS date) BETWEEN @Date_beg AND @Date_end) 
                        JOIN
                        dbo.v_UslugaComplex us
                        ON us.UslugaComplex_id = usl.UslugaComplex_id
                        Left JOIN dbo.v_Diag d
                                on d.Diag_id = sl.Diag_oid
                        LEFT JOIN v_Lpu Lpu WITH (NOLOCK)
                                ON Lpu.Lpu_id = sl.Lpu_id
                WHERE pr.Person_id = @Person_id)
				ORDER BY  2   DESC 
              ";  

		//echo "<pre>".print_r($data, 1)."</pre>";
		
        $result = $this->db->query($q, array(
            'PersID'     => $data['PersID'],
            'DateUslBeg' => $data['DateUslBeg'],
            'DateUslEnd' => $data['DateUslEnd']
                )
            ); 
        if ( is_object($result) ) { 
            return $result->result('array'); 
            //return $result; 
        } else { 
            return false; 
        } 
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
                            from v_EvnSection with (nolock)
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
                            from v_EvnVizitPL EVPL with (nolock)
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
                            from v_EvnDiagPLSop EDL with (nolock)
                            left join v_EvnVizit ev with (nolock) on EDL.EvnDiagPLSop_pid=ev.EvnVizit_id
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
                            from v_EvnDiagPS EDS with (nolock)
                            left join v_EvnSection es with (nolock) on EDS.EvnDiagPS_pid=es.EvnSection_id
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
                            from v_EvnDiagSpec EDS with (nolock)
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
                            from v_EvnUslugaDispDop EVNU with(nolock)
                            inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EVNU.EvnUslugaDispDop_pid
                            inner join v_Diag diag with(nolock) on diag.Diag_id=EVDD.Diag_id
                            left join v_DopDispInfoConsent DDIC with(nolock) on EVDD.DopDispInfoConsent_id=DDIC.DopDispInfoConsent_id
                            left join v_SurveyTypeLink STL with(nolock) on STL.SurveyTypeLink_id=DDIC.SurveyTypeLink_id
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
                            v_EvnDiagDopDisp EDDD (nolock)
                    where
                            (1=1) and EDDD.Person_id = :Person_id and EDDD.DeseaseDispType_id = '2'
                    )

                    select
                            ED.EvnClass_SysNick,
                            ED.Person_id,
                            Ed.spec_id,
                            ED.Person_id as pid,
                            0 as Children_Count,
                            ED.Lpu_id,
                            --ED.Evn_id as Diag_pid,
                            ED.Diag_id,
                            ED.Diag_id as DiagList_id,
                            CONVERT(varchar(10), ED.Diag_setDate, 104) as Diag_setDate,
                            RTRIM(ISNULL(Diag.Diag_Code, '')) as Diag_Code,
                            RTRIM(ISNULL(Diag.Diag_Name, '')) as Diag_Name,
                            case ED.spec_id when 0 then RTRIM(ISNULL(Lpu.Lpu_Nick, ''))else EDS.EvnDiagSpec_Lpu end as Lpu_Nick
                            ,case ED.spec_id when 0 then RTRIM(ISNULL(MP.Person_Fio, ''))else ISNULL(EDS.EvnDiagSpec_MedWorker, MSF.Person_Fio) end as MedPersonal_Fio
                            ,case ED.spec_id when 0 then ISNULL(LS.LpuSectionProfile_Name, '')else EDS.EvnDiagSpec_LpuSectionProfile end as LpuSectionProfile_Name
                    from EvnDiag ED with (nolock)
                            left join v_Diag as Diag with (nolock) on Diag.Diag_id = ED.Diag_id
                            left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = ED.Lpu_id
                            left join v_EvnDiagSpec EDS with(nolock) on ED.spec_id = EDS.EvnDiagSpec_id
                            left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = EDS.MedStaffFact_id
                            left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ED.MedPersonal_id and MP.Lpu_id = ED.Lpu_id
                            left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ED.LpuSection_id
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
     * проверка пересечения случаев
     */
    function checkCrossingSluch($data){
            $q = "
                    SELECT 
                    Convert(varchar,max(re.PersonRegisterEco_ResultDate),104) as
                     ResDate
                    FROM [dbo].[PersonRegister] r
                    join dbo.PersonRegisterEco re on re.PersonRegister_id = r.PersonRegister_id
                    WHERE MorbusType_id in (SELECT MorbusType_id
                                          FROM dbo.MorbusType
                                          WHERE MorbusType_SysNick = 'eco')
                                  and r.Person_id = :Person_id
		";
            $result = $this->db->query($q, array(
                'Person_id' => $data['Person_id']
                    )
                ); 
        if ( is_object($result) ) { 
                return $result->result('array'); 
                //return $result; 
        } else { 
            return false; 
            }
            
    }
	
    /**
     * проверка наличия открытых случаев в других МО
     */	 
    function checkOpenEco($data) { 
		
        $q = "SELECT 1 FROM dbo.v_ECORegistry vER with (nolock) where vER.Person_id = :Person_id and vER.Result is null and not vER.Lpu_id = :Lpu_id";
		
        $result = $this->db->query($q, $data); 		
        if ( is_object($result) ) { 
            return $result->result('array'); 

            return $result; 
        } else {
            return false;
        } 
    }
	
    /**
     * проверка наличия открытых случаев в других МО
     */
    function getBirthSpecStacId($data) { 
		if ($data['Status'] == 4){
			//выводим исход из РБ без возможности редактирования
			$q = "select BSS.BirthSpecStac_id from dbo.PersonPregnancy PP left join dbo.BirthSpecStac BSS on BSS.PersonRegister_id=PP.PersonRegister_id where PP.PersonRegisterEco_id = :PersonRegisterEco_id";			
		}else{		
			//выводим исход, созданные из регистра ЭКО, если имеется
			$q = "select PP.BirthSpecStac_id from v_ECORegistry PP where PP.PersonRegisterEco_id = :PersonRegisterEco_id";					
		}
		
		$result = $this->db->query($q, $data);
		
        if ( is_object($result) ) {
            return $result->result('array'); 
        } else {
            return false;
        } 
	}
	
	/**
	 * Сохранение данных исхода беременности
	 */
	function saveBirthSpecStac($data, $isAllowTransaction = true) {
		//echo 1; exit;
		
		
		$response = array('success' => true, 'BirthSpecStac_id' => null);

		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();

		$OutcomPeriod = $data['BirthSpecStac_OutcomPeriod'];

		$PregnancyResult_Code = $this->getFirstResultFromQuery("
			select top 1 PregnancyResult_Code from v_PregnancyResult with(nolock) where PregnancyResult_id = :PregnancyResult_id
		", array('PregnancyResult_id' => $data['PregnancyResult_id']));
		if (!$PregnancyResult_Code) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении кода исхода беременности');
		}

		$data['BirthSpecStac_OutcomDT'] = $data['BirthSpecStac_OutcomDate'].' '.$data['BirthSpecStac_OutcomTime'];

		if (empty($data['ChildDeathData'])) {
			$data['ChildDeathData'] = array();
		}
		if (is_string($data['ChildDeathData'])) {
			$data['ChildDeathData'] = json_decode($data['ChildDeathData'], true);
		}

		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			//Общая часть
			'BirthSpecStac_id' => !empty($data['BirthSpecStac_id'])?$data['BirthSpecStac_id']:null,
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PregnancySpec_id' => !empty($data['PregnancySpec_id'])?$data['PregnancySpec_id']:null,
			'Evn_id' => $data['Evn_id'],
			'EvnSection_id' => $data['EvnSection_id'],
			'Lpu_id' => $data['Lpu_oid'],
			'BirthSpecStac_OutcomDT' => $data['BirthSpecStac_OutcomDT'],
			'BirthSpecStac_OutcomPeriod' => $data['BirthSpecStac_OutcomPeriod'],
			'PregnancyResult_id' => $data['PregnancyResult_id'],
			'BirthSpecStac_CountPregnancy' => $data['BirthSpecStac_CountPregnancy'],
			'BirthSpecStac_CountChild' => $data['BirthSpecStac_CountChild'],
			'BirthSpecStac_BloodLoss' => $data['BirthSpecStac_BloodLoss'],
			'BirthSpecStac_IsRWtest' => !empty($data['BirthSpecStac_IsRWtest'])?$data['BirthSpecStac_IsRWtest']:1,
			'BirthSpecStac_IsRW' => !empty($data['BirthSpecStac_IsRW'])?$data['BirthSpecStac_IsRW']:1,
			'BirthSpecStac_IsHIVtest' => !empty($data['BirthSpecStac_IsHIVtest'])?$data['BirthSpecStac_IsHIVtest']:1,
			'BirthSpecStac_IsHIV' => !empty($data['BirthSpecStac_IsHIV'])?$data['BirthSpecStac_IsHIV']:1,
			'BirthSpecStac_IsHBtest' => !empty($data['BirthSpecStac_IsHBtest'])?$data['BirthSpecStac_IsHBtest']:1,
			'BirthSpecStac_IsHB' => !empty($data['BirthSpecStac_IsHB'])?$data['BirthSpecStac_IsHB']:1,
			'BirthSpecStac_IsHCtest' => !empty($data['BirthSpecStac_IsHCtest'])?$data['BirthSpecStac_IsHCtest']:1,
			'BirthSpecStac_IsHC' => !empty($data['BirthSpecStac_IsHC'])?$data['BirthSpecStac_IsHC']:1,
			'BirthResult_id' => null,		//Рассчитываемое поле
			'ignoreCheckBirthSpecStacDate' => !empty($data['ignoreCheckBirthSpecStacDate'])?$data['ignoreCheckBirthSpecStacDate']:0,
			//Роды
			'BirthPlace_id' => null,
			'BirthSpec_id' => null,
			'BirthCharactType_id' => null,
			'BirthSpecStac_CountBirth' => null,
			'BirthSpecStac_CountChildAlive' => null,
			'BirthSpecStac_IsContrac' => null,
			'BirthSpecStac_ContracDesc' => null,
			//Аборт
			'AbortLpuPlaceType_id' => null,
			'AbortLawType_id' => null,
			'AbortMethod_id' => null,
			'AbortIndicat_id' => null,
			'BirthSpecStac_InjectVMS' => null,
			'AbortType_id' => null,		//Рассчитываемое поле
			'BirthSpecStac_IsMedicalAbort' => null,	//Рассчитываемое поле
			//Внематочная беременность
			'BirthSpecStac_SurgeryVolume' => null,
			'Status' => $data['Status'],
			'Eco_id' => $data['Eco_id'],			
		);

		switch(true) {
			//Роды в срок
			case ($PregnancyResult_Code == 1 && $OutcomPeriod >= 38): $params['BirthResult_id'] = 1;break;
			//Преждевременные роды
			case ($PregnancyResult_Code == 1 && $OutcomPeriod <= 37): $params['BirthResult_id'] = 2;break;
			//Аборт
			case ($PregnancyResult_Code == 3): $params['BirthResult_id'] = 3;break;
			//Выкидыш
			case ($PregnancyResult_Code == 2): $params['BirthResult_id'] = 4;break;
			//В остальных случаях - выкидыш
			default: $params['BirthResult_id'] = 4;
		}

		$params['BirthPlace_id'] = $this->getBirthPlaceId(3);	//По умолчанию "В другом месте"
		if (!$params['BirthPlace_id']) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении идентификатора места родов');
		}

		switch($PregnancyResult_Code) {
			case 1:	//Роды
				$params['BirthPlace_id'] = !empty($data['BirthPlace_id'])?$data['BirthPlace_id']:null;
				$params['BirthSpec_id'] = !empty($data['BirthSpec_id'])?$data['BirthSpec_id']:null;
				$params['BirthCharactType_id'] = !empty($data['BirthCharactType_id'])?$data['BirthCharactType_id']:null;
				$params['BirthSpecStac_CountBirth'] = !empty($data['BirthSpecStac_CountBirth'])?$data['BirthSpecStac_CountBirth']:null;
				$params['BirthSpecStac_CountChildAlive'] = !empty($data['BirthSpecStac_CountChildAlive'])?$data['BirthSpecStac_CountChildAlive']:null;
				$params['BirthSpecStac_IsContrac'] = !empty($data['BirthSpecStac_IsContrac'])?$data['BirthSpecStac_IsContrac']:null;
				$params['BirthSpecStac_ContracDesc'] = !empty($data['BirthSpecStac_ContracDesc'])?$data['BirthSpecStac_ContracDesc']:null;
				break;

			case 2:	//Самопроизвольный аборт
				$AbortType_id = $this->getAbortTypeId($params);
				if ($AbortType_id === false) {
					$this->rollbackTransaction();
					return $this->createError('','Ошибка при рассчете типа аборта');
				}
				$params['AbortType_id'] = !empty($AbortType_id)?$AbortType_id:null;
				break;

			case 3:	//Искусственный аборт
				$params['AbortLpuPlaceType_id'] = !empty($data['AbortLpuPlaceType_id'])?$data['AbortLpuPlaceType_id']:null;
				$params['AbortLawType_id'] = !empty($data['AbortLawType_id'])?$data['AbortLawType_id']:null;
				$params['AbortMethod_id'] = !empty($data['AbortMethod_id'])?$data['AbortMethod_id']:null;
				$params['AbortIndicat_id'] = !empty($data['AbortIndicat_id'])?$data['AbortIndicat_id']:null;
				$params['BirthSpecStac_InjectVMS'] = !empty($data['BirthSpecStac_InjectVMS'])?$data['BirthSpecStac_InjectVMS']:null;

				$params['BirthSpecStac_IsMedicalAbort'] = ($params['AbortMethod_id'] == 1)?2:1;

				$AbortType_id = $this->getAbortTypeId($params);
				if ($AbortType_id === false) {
					$this->rollbackTransaction();
					return $this->createError('','Ошибка при рассчете типа аборта');
				}
				$params['AbortType_id'] = $AbortType_id;
				break;

			case 4:	//Внематочная беременность
				$params['BirthSpecStac_SurgeryVolume'] = !empty($data['BirthSpecStac_SurgeryVolume'])?$data['BirthSpecStac_SurgeryVolume']:null;
				break;
		}
	
		if (empty($data['BirthSpecStac_id'])) {
			$procedure = 'p_BirthSpecStacEco_ins';
		} else {
			$procedure = 'p_BirthSpecStacEco_upd';
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :BirthSpecStac_id;
			exec {$procedure}
				@BirthSpecStac_id = @Res output,
				@PersonRegister_id = :PersonRegister_id,
				@PregnancySpec_id = :PregnancySpec_id,
				@Evn_id = :Evn_id,
				@EvnSection_id = :EvnSection_id,
				@Lpu_id = :Lpu_id,
				@BirthSpecStac_OutcomDT = :BirthSpecStac_OutcomDT,
				@BirthSpecStac_OutcomPeriod = :BirthSpecStac_OutcomPeriod,
				@PregnancyResult_id = :PregnancyResult_id,
				@BirthSpecStac_CountPregnancy = :BirthSpecStac_CountPregnancy,
				@BirthSpecStac_CountBirth = :BirthSpecStac_CountBirth,
				@BirthSpecStac_CountChild = :BirthSpecStac_CountChild,
				@BirthSpecStac_CountChildAlive = :BirthSpecStac_CountChildAlive,
				@BirthSpecStac_BloodLoss = :BirthSpecStac_BloodLoss,
				@BirthSpecStac_IsRWtest = :BirthSpecStac_IsRWtest,
				@BirthSpecStac_IsRW = :BirthSpecStac_IsRW,
				@BirthSpecStac_IsHIVtest = :BirthSpecStac_IsHIVtest,
				@BirthSpecStac_IsHIV = :BirthSpecStac_IsHIV,
				@BirthSpecStac_IsHBtest = :BirthSpecStac_IsHBtest,
				@BirthSpecStac_IsHB = :BirthSpecStac_IsHB,
				@BirthSpecStac_IsHCtest = :BirthSpecStac_IsHCtest,
				@BirthSpecStac_IsHC = :BirthSpecStac_IsHC,
				@BirthSpecStac_IsContrac = :BirthSpecStac_IsContrac,
				@BirthSpecStac_ContracDesc = :BirthSpecStac_ContracDesc,
				@BirthResult_id = :BirthResult_id,
				@BirthPlace_id = :BirthPlace_id,
				@BirthSpec_id = :BirthSpec_id,
				@BirthCharactType_id = :BirthCharactType_id,
				@AbortLpuPlaceType_id = :AbortLpuPlaceType_id,
				@AbortLawType_id = :AbortLawType_id,
				@AbortMethod_id = :AbortMethod_id,
				@AbortIndicat_id = :AbortIndicat_id,
				@AbortType_id = :AbortType_id,
				@BirthSpecStac_IsMedicalAbort = :BirthSpecStac_IsMedicalAbort,
				@BirthSpecStac_InjectVMS = :BirthSpecStac_InjectVMS,
				@BirthSpecStac_SurgeryVolume = :BirthSpecStac_SurgeryVolume,
				@pmUser_id = :pmUser_id,
				@Status = :Status,
				@Eco_id = :Eco_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as BirthSpecStac_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$response['BirthSpecStac_id'] = $data['BirthSpecStac_id'] = $resp[0]['BirthSpecStac_id'];

		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Получение идентификатора места родов по коду
	 */
	function getBirthPlaceId($BirthPlace_Code) {
		$params = array('BirthPlace_Code' => $BirthPlace_Code);
		$query = "select top 1 BirthPlace_id from v_BirthPlace with(nolock) where BirthPlace_Code = :BirthPlace_Code";
		return $this->getFirstResultFromQuery($query, $params);
	}	
	
	/**
	 * Рассчет типа аборта
	 */
	function getAbortTypeId($data) {
		$params = array(
			'PregnancyResult_id' => $data['PregnancyResult_id'],
			'BirthSpecStac_OutcomPeriod' => $data['BirthSpecStac_OutcomPeriod'],
			'AbortLawType_id' => !empty($data['AbortLawType_id'])?$data['AbortLawType_id']:null,
			'AbortIndicat_id' => !empty($data['AbortIndicat_id'])?$data['AbortIndicat_id']:null,
			'AbortMethod_id' => !empty($data['AbortMethod_id'])?$data['AbortMethod_id']:null,
		);

		$conditions = "when 1=1 then ''";
		if (getRegionNick() == 'kz') {
			$conditions = "
				when @PregnancyResult_Code = 2 then 'Self'
				when @PregnancyResult_Code = 3 and @OutcomPeriod <= 12 then 'Med'
				when @PregnancyResult_Code = 3 and @AbortMethod_Code = 2 then 'Mini'
				when @PregnancyResult_Code = 3 and @AbortIndicat_Code = 3 then 'SocP'
				when @PregnancyResult_Code = 3 and @AbortIndicat_Code in (1,2) then 'MedP'
				when @PregnancyResult_Code = 3 and @AbortLawType_Code = 2 then 'Crime'
			";
		} else {
			$conditions = "
				when @PregnancyResult_Code = 3 and @AbortIndicat_Code = 1 then 'medpok'
				when @PregnancyResult_Code = 3 and @AbortIndicat_Code = 2 then 'anom'
				when @PregnancyResult_Code = 3 and @AbortIndicat_Code = 3 then 'socpok'
				when @PregnancyResult_Code = 3 and @AbortLawType_Code = 1 then 'med'
				when @PregnancyResult_Code = 3 and @AbortLawType_Code = 2 then 'krim'
			";
		}

		$query = "
			declare @PregnancyResult_Code int = (
				select top 1 PregnancyResult_Code from v_PregnancyResult where PregnancyResult_id = :PregnancyResult_id
			)
			declare @AbortLawType_Code int = (
				select top 1 AbortLawType_Code from v_AbortLawType where AbortLawType_id = :AbortLawType_id
			)
			declare @AbortIndicat_Code int = (
				select top 1 AbortIndicat_Code from v_AbortIndicat where AbortIndicat_id = :AbortIndicat_id
			)
			declare @AbortMethod_Code int = (
				select top 1 AbortMethod_Code from v_AbortMethod where AbortMethod_id = :AbortMethod_id
			)
			declare @OutcomPeriod int = :BirthSpecStac_OutcomPeriod

			select top 1 AbortType_id
			from v_AbortType with(nolock)
			where AbortType_SysNick like (case {$conditions} end)
		";

		return $this->getFirstResultFromQuery($query, $params, true);
	}

	/**
	*  Получение списка случаев пациента в регистре ЭКО
	*/
	function getPersonRegisterEco($data) {		
		$q = "
			SELECT vER.PersonRegisterEco_id as Eco_id, convert (VARCHAR (10), vER.PersonRegisterEco_AddDate, 104) AS PersonRegister_setDate, vER.opl_name 
			FROM dbo.v_ECORegistry vER 
            WHERE vER.PersonRegister_id=:PersonRegister_id
			ORDER BY vER.PersonRegisterEco_AddDate desc
		";
        $result = $this->db->query($q, array('PersonRegister_id' => $data['PersonRegister_id'])); 
        if ( is_object($result) ) { 
            return $result->result('array'); 
        } else { 
            return false; 
        } 
    }	
	
	/**
	*  Удаление случая регистра ЭКО
	*/
	function Delete($data) {		
		$q =	"begin 
					declare @BirthSpecStac_id int = NULL;
					declare @PersonRegister_id int = NULL;
					declare @PersonRegisterEco_id int = NULL;
					select @BirthSpecStac_id = BirthSpecStac_id from dbo.PersonRegisterEco where PersonRegisterEco_id=:PersonRegisterEco_id;
					if (@BirthSpecStac_id is not null)
					begin
						select @PersonRegister_id = PersonRegister_id from dbo.BirthSpecStac where BirthSpecStac_id = @BirthSpecStac_id;
						update dbo.PersonRegisterEco set BirthSpecStac_id  = null where PersonRegisterEco_id=:PersonRegisterEco_id;
						delete from dbo.BirthSpecStac where BirthSpecStac_id = @BirthSpecStac_id; 
						update PersonRegister set PersonRegisterOutCause_id =NULL where PersonRegister_id = @PersonRegister_id;
					end;		
					
					select @PersonRegisterEco_id=null;
					select @PersonRegisterEco_id=old.PersonRegisterEco_id from dbo.PersonRegisterEco old, dbo.PersonRegisterEco new 
					where old.PersonRegister_id=new.PersonRegister_id and old.PersonRegisterEco_AddDate=new.PersonRegisterEco_AddDate and old.PersonRegisterEco_deleted=2 and new.PersonRegisterEco_id=:PersonRegisterEco_id;
					if (@PersonRegisterEco_id is null)
					begin
						update dbo.PersonRegisterEco set PersonRegisterEco_deleted  = 2 where PersonRegisterEco_id=:PersonRegisterEco_id;
					end else
					begin
						update dbo.PersonPregnancy set PersonRegisterEco_id = null where PersonRegisterEco_id=@PersonRegisterEco_id;
						delete from dbo.PersonRegisterEco where PersonRegisterEco_id=@PersonRegisterEco_id;
						update dbo.PersonRegisterEco set PersonRegisterEco_deleted  = 2 where PersonRegisterEco_id=:PersonRegisterEco_id;
					end
				end;";
        $result = $this->db->query($q, array('PersonRegisterEco_id' => $data['Eco_id'])); 		
		
        if ( is_object($result) ) { 			
			return false;
        } else {
            return false; 
        }
    }		
	
}
?>
