<?php

require_once(APPPATH.'models/_pgsql/EvnPS_model.php');

class Samara_EvnPS_model extends EvnPS_model {
	/**
	 * Конструктор
	 */
    function __construct() {
		parent::__construct();
    }
    
    /**
     * Возвращает список новорожденных с физиологическими данными (рост, вес, пол)
     * 
     * @param $evnSection_id - это id приемного отделения
     * @return mixed - список новорожденных
     */
    function getChildsData($evnSection_id) {
        // Sannikov
        
        
        $query = "
            SELECT
                 cps.Person_id as \"Person_id\"
                ,to_char(ps.Person_BirthDay, 'dd.mm.yyyy') AS \"child_Person_Bday\"
                ,CAST(coalesce(PH.PersonHeight_Height, 0) AS integer) AS \"PersonHeight_Height\"
                ,(cast(coalesce(PW.PersonWeight_Weight, 0) as float) / 1000) AS \"PersonWeight_Weight\"
                ,( SELECT
                		sex_name
				    FROM dbo.v_Sex s
				    WHERE s.sex_id = ps.Sex_id
				    limit 1
				) AS \"Sex_name\"
            FROM 
                (
                    SELECT
                        vel.Evn_id
                        ,vel.Evn_lid
                    FROM dbo.v_EvnLink AS vel
                    WHERE Evn_id = :EvnSection_pid --114537
                ) AS l
				LEFT JOIN dbo.v_EvnPS cps ON cps.EvnPS_id = l.Evn_lid
				LEFT JOIN dbo.v_EvnPS mps ON mps.EvnPS_id = l.Evn_id
				LEFT JOIN dbo.v_PersonState ps ON ps.Person_id = cps.Person_id    --mps.Person_id
				LEFT JOIN v_PersonHeight PH ON cps.Person_id = PH.Person_id
				LEFT JOIN v_PersonWeight PW ON cps.Person_id = PW.Person_id     
        ";

		$result = $this->db->query($query, array(
			'EvnSection_pid' => $evnSection_id
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
    
    /**
     * Возвращает список мертворожденных с физиологическими данными (рост, вес, пол)
     * 
     * @param type $evnSection_id - это id приемного отделения
     * @param type $Lpu_id
     * @return mixed
     */
    function getDeathChildsData($evnSection_id, $Lpu_id) {
        // Sannikov        
   
        
        $query = "
            SELECT 
            	ChildDeath_id as \"ChildDeath_id\",
                MedStaffFact_id as \"MedStaffFact_id\",
                (select Person_Fio from v_MedStaffFact where MedStaffFact_id = cd.MedStaffFact_id) AS \"MedStaffFact_Name\",
                Diag_id as \"Diag_id\",
                (SELECT diag_name FROM dbo.Diag AS d WHERE cd.diag_id = d.diag_id) AS \"Diag_Name\",
                Sex_id as \"Sex_id\",
                (SELECT Sex_Name FROM sex AS s WHERE s.sex_id = cd.sex_id) AS \"Sex_Name\",
                ChildDeath_Weight as \"ChildDeath_Weight\",
                ChildDeath_Height as \"ChildDeath_Height\",
                PntDeathTime_id as \"PntDeathTime_id\",
                (SELECT PntDeathTime_Name FROM dbo.PntDeathTime dt WHERE dt.PntDeathTime_id  = cd.PntDeathTime_id) as \"PntDeathTime_Name\",
                ChildTermType_id as \"ChildTermType_id\",
                (SELECT ChildTermType_Name FROM ChildTermType tt WHERE tt.ChildTermType_id = cd.ChildTermType_id) as \"ChildTermType_Name\",
                ChildDeath_Count as \"ChildDeath_Count\",
                BirthSvid_id as \"BirthSvid_id\",
                (SELECT BirthSvid_Num FROM BirthSvid AS bs WHERE bs.BirthSvid_id = cd.BirthSvid_id) AS \"BirthSvid_Num\",
                PntDeathSvid_id as \"PntDeathSvid_id\",
                (SELECT PntDeathSvid_Num FROM PntDeathSvid AS pds WHERE pds.PntDeathSvid_id = cd.PntDeathSvid_id) AS \"PntDeathSvid_Num\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                coalesce(to_char(ChildDeath_insDT, 'dd.mm.yyyy'), 0) as \"ChildDeath_insDT\",
                ChildDeath_updDT as \"ChildDeath_updDT\",
                Okei_wid as \"Okei_wid\",
                CAST(ChildDeath_Weight as VARCHAR) || ' '|| coalesce((SELECT Okei_NationSymbol FROM v_Okei o WHERE Okei_id = cd.Okei_wid), '') as \"ChildDeath_Weight_text\",
                1 AS \"RecordStatus_Code\"
            FROM dbo.v_ChildDeath cd
            WHERE BirthSpecStac_id IN ( 
                SELECT
                    BirthSpecStac_id
                FROM birthSpecStac
                WHERE EvnSection_id IN (
                    SELECT
                        ES.EvnSection_id
                    FROM v_EvnSection ES
                    inner join LpuSection LS on LS.LpuSection_id = ES.LpuSection_id
                    WHERE ES.EvnSection_pid = :EvnSection_pid ---114586
                        AND ES.Lpu_id = :Lpu_id --6011
                    limit 1
                )
                ORDER BY  BirthSpecStac_id DESC
                limit 1
            )
        ";

        //echo getDebugSQL($query, array('EvnSection_pid' => $evnSection_id,'Lpu_id' => $Lpu_id)); exit();
        
        $result = $this->db->query($query, array(
			'EvnSection_pid' => $evnSection_id,
            'Lpu_id' => $Lpu_id
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
    
    /**
     * Сопуствующие диагнозы
     * 
     * @param type $evnSection_id
     * @param type $Person_id
     * @return mixed
     */
    function getDiagSectList($evnSection_id, $Person_id = 0) {
        // Sannikov
        
        
        $query = "
            select
				EDPS.EvnDiagPS_id as \"EvnDiagPS_id\",
				EDPS.EvnDiagPS_pid as \"EvnDiagPS_pid\",
				EDPS.Person_id as \"Person_id\",
				EDPS.PersonEvn_id as \"PersonEvn_id\",
				EDPS.Server_id as \"Server_id\",
				Diag.Diag_id as \"Diag_id\",
				EDPS.DiagSetPhase_id as \"DiagSetPhase_id\",
				RTRIM(EDPS.EvnDiagPS_PhaseDescr) as \"EvnDiagPS_PhaseDescr\",
				DSC.DiagSetClass_id as \"DiagSetClass_id\",
				EDPS.DiagSetType_id as \"DiagSetType_id\",
				to_char(EDPS.EvnDiagPS_setDate, 'dd.mm.yyyy') as \"EvnDiagPS_setDate\",
				EDPS.EvnDiagPS_setTime as \"EvnDiagPS_setTime\",
				RTRIM(DSC.DiagSetClass_Name) as \"DiagSetClass_Name\",
				RTRIM(Diag.Diag_Code) as \"Diag_Code\",
				RTRIM(Diag.Diag_Name) as \"Diag_Name\",
				1 as \"RecordStatus_Code\"
			from v_EvnDiagPS EDPS
				left join Diag on Diag.Diag_id = EDPS.Diag_id
				left join DiagSetClass DSC on DSC.DiagSetClass_id = EDPS.DiagSetClass_id
			where 
				EDPS.EvnDiagPS_pid = :EvnSection_pid 
            	AND EDPS.DiagSetType_id = 2    
        ";

		$result = $this->db->query($query, array(
			'EvnSection_pid' => $evnSection_id
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
    
    /**
     * Осложнения операции
     * 
     * @param type $evnSection_id
     * @param type $Lpu_id
     * @param type $MesOperType
     * @return mixed
     */
    function getAggList($evnSection_id, $Lpu_id, $MesOperType = null) {
        // Sannikov
        
        
        $filters = '';
        if ($MesOperType === 'birth') {
            $filters = "AND MOT.MesOperType_id = 22";  // отфильтровать по виду лечения - роды (MesOperType_id = 22)
        }
        $query = "
            SELECT                                             
                 EU.EvnUsluga_id  as \"EvnUsluga_id\"
                ,EA.EvnAgg_pid as \"EvnAgg_pid\"
                ,EA.EvnAgg_id as \"EvnAgg_id\"
                ,EA.AggType_id as \"AggType_id\"
                ,AT.AggType_Code as \"AggType_Code\"
                ,RTRIM(coalesce(AT.AggType_Name, '')) AS \"AggType_Name\"
                ,RTRIM(coalesce(AW.AggWhen_Name, '')) AS \"AggWhen_Name\"
                ,MOT.MesOperType_id as \"MesOperType_id\"
                ,MOT.MesOperType_Name as \"MesOperType_Name\"
            FROM v_EvnSection ES
                INNER JOIN LpuSection LS ON ES.LpuSection_id = LS.LpuSection_id
                LEFT JOIN v_EvnUsluga EU ON ES.EvnSection_id = EU.EvnUsluga_pid     
                INNER JOIN v_EvnAgg EA ON EU.EvnUsluga_id = EA.EvnAgg_pid          
                LEFT JOIN AggType AT ON AT.AggType_id = EA.AggType_id
                LEFT JOIN AggWhen AW ON AW.AggWhen_id = EA.AggWhen_id
                left join MesOperType MOT on MOT.MesOperType_id = EU.MesOperType_id  
            WHERE
                ES.EvnSection_pid = :EvnSection_pid 
                AND ES.Lpu_id = :Lpu_id  
        ".$filters;

        //echo getDebugSQL($query, array('EvnSection_pid' => $evnSection_id,'Lpu_id' => $Lpu_id)); exit();
        
		$result = $this->db->query($query, array(
			'EvnSection_pid' => $evnSection_id
            ,'Lpu_id' => $Lpu_id
		));
        
        

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
    
    /**
     * Операции ()
     * 
     * @param type $evnSection_id
     * @param type $Lpu_id
     * @return mixed
     */
    function getOperationList($evnSection_id, $Lpu_id) {
        // Sannikov
        
        
        $query = "
            SELECT
                 EU.Usluga_id as \"Usluga_id\"
                ,EU.UslugaComplex_id as \"UslugaComplex_id\"
                ,EU.EvnClass_id as \"EvnClass_id\"
                ,RTRIM(coalesce(UC.UslugaComplex_Code, '')) as \"UslugaComplex_Code\"
                ,RTRIM(coalesce(UC.UslugaComplex_Name, '')) as \"UslugaComplex_Name\"
                ,to_char(EU.EvnUsluga_setDate, 'dd.mm.yyyy') as \"EvnUsluga_Date\"
            FROM v_EvnSection ES
                INNER JOIN LpuSection LS ON ES.LpuSection_id = LS.LpuSection_id
                LEFT JOIN v_EvnUsluga EU ON ES.EvnSection_id = EU.EvnUsluga_pid    
                LEFT JOIN UslugaComplex UC ON EU.UslugaComplex_id = UC.UslugaComplex_id
            WHERE
                ES.EvnSection_pid = :EvnSection_pid
                AND ES.Lpu_id = :Lpu_id
                AND EU.EvnClass_id = 43 
        ";  // EU.EvnClass_id = 43 - это оперативная услуга

        //echo getDebugSQL($query, array('EvnSection_pid' => $evnSection_id,'Lpu_id' => $Lpu_id)); exit();
        
		$result = $this->db->query($query, array(
			'EvnSection_pid' => $evnSection_id
            ,'Lpu_id' => $Lpu_id
		));
        
        

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
    
    /**
     * Возвращает тип стационара (дневной или нет)
     * 
     * @param type $evnSection_id
     * @param type $Lpu_id
     * @return boolean
     */
    function getDayStac($evnSection_id, $Lpu_id) {
        // Sannikov
        
        $query = "
            SELECT 
                 ES.LpuSection_id as \"LpuSection_id\"
                ,ES.EvnSection_id as \"EvnSection_id\"
                ,EOST.EvnOtherStac_id as \"EvnOtherStac_id\"
                ,EOST.EvnOtherStac_pid as \"EvnOtherStac_pid\"
                ,EOST.EvnOtherStac_rid as \"EvnOtherStac_rid\"
                ,EL.EvnLeave_id as \"EvnLeave_id\"
                ,ES.EvnSection_pid as \"EvnSection_pid\"
                ,ES.EvnSection_rid as \"EvnSection_rid\"
                ,ES.Lpu_id as \"Lpu_id\"
                ,LS.LpuUnit_id as \"LpuUnit_id\"
                ,LU.LpuUnitType_id as \"LpuUnitType_id\"
                ,LUT.LpuUnitType_Code as \"LpuUnitType_Code\"
                ,LUT.LpuUnitType_Name as \"LpuUnitType_Name\"
                ,LUT.LpuUnitType_Nick as \"LpuUnitType_Nick\"
                ,LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
                ,COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOSBP.ResultDesease_id, EOST.ResultDesease_id) as \"ResultDesease_id\"
                ,EL.EvnLeave_id as \"EvnLeave_id\"
                ,EPS.EvnPS_rid as \"EvnPS_rid\"
                ,EPS.LeaveType_id as \"LeaveType_id\"
                ,RD.ResultDesease_Code as \"ResultDesease_Code\"
                ,RD.ResultDesease_Name as \"ResultDesease_Name\"
                ,RD.ResultDesease_SysNick as \"ResultDesease_SysNick\"
            FROM v_EvnSection ES 
            INNER JOIN LpuSection LS ON ES.LpuSection_id = LS.LpuSection_id
            INNER JOIN LpuUnit LU ON LS.LpuUnit_id = LU.LpuUnit_id
            INNER JOIN v_LpuUnitType LUT ON LU.LpuUnitType_id = LUT.LpuUnitType_id
            LEFT JOIN v_EvnLeave EL ON ES.EvnSection_rid = EL.EvnLeave_rid
            LEFT JOIN v_EvnOtherLpu EOL ON ES.EvnSection_id = EOL.EvnOtherLpu_pid
            left join v_EvnOtherSectionBedProfile EOSBP on EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
            left join v_EvnOtherStac EOST on EOST.EvnOtherStac_pid = ES.EvnSection_id
            left join v_EvnOtherSection EOS on EOS.EvnOtherSection_pid = ES.EvnSection_id
            LEFT JOIN ResultDesease RD 
                ON COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOSBP.ResultDesease_id, EOST.ResultDesease_id) = RD.ResultDesease_id
            LEFT JOIN v_EvnPS EPS ON EPS.EvnPS_rid = ES.EvnSection_rid
            WHERE ES.EvnSection_pid = :EvnSection_pid 
                OR ES.EvnSection_rid = :EvnSection_pid
                AND ES.Lpu_id = :Lpu_id 
        ";

        //echo getDebugSQL($query, array('EvnSection_pid' => $evnSection_id,'Lpu_id' => $Lpu_id)); exit();
        
		$result = $this->db->query($query, array(
			'EvnSection_pid' => $evnSection_id
            ,'Lpu_id' => $Lpu_id
		));
        
        

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
    /**
	 * Некая абстрактная функция TODO: описать
	 */
    function getHeight($Person_id) {
        // Sannikov
        
        
        $query = '
            SELECT 
                CAST(coalesce(PH.PersonHeight_Height, 0) AS integer) AS "PersonHeight_Height"
                ,PH.HeightMeasureType_id as "HeightMeasureType_id"
            FROM v_PersonHeight PH
            WHERE PH.Person_id = :Person_id
            ORDER BY PH.HeightMeasureType_id    
        ';

		$result = $this->db->query($query, array(
			'Person_id' => $Person_id
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
    /**
	 * Некая абстрактная функция TODO: описать
	 */
    function getWeight($Person_id) {
        // Sannikov
        
        $query = '
            SELECT 
                (cast(coalesce(PW.PersonWeight_Weight, 0) as float) / 1000) AS "PersonWeight_Weight"
                ,PW.WeightMeasureType_id as "WeightMeasureType_id"
            FROM v_PersonWeight PW
            WHERE PW.Person_id = :Person_id
            ORDER BY PW.WeightMeasureType_id    
        ';

		$result = $this->db->query($query, array(
			'Person_id' => $Person_id
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
    
    /**
     * Данные матери новорожденного
     * 
     * @param type $LpuSection_id
     * @return boolean
     */
    function getMotherData($LpuSection_id) {

        // Sannikov
        
        $query = "
            SELECT 
                 PS.PersonSurName_SurName as \"PersonSurName_SurName\"
                ,PS.PersonFirName_FirName as \"PersonFirName_FirName\"
                ,PS.PersonSecName_SecName as \"PersonSecName_SecName\"
                ,RTRIM(RTRIM(coalesce(PS.PersonSurName_SurName, ''))
					|| ' ' || RTRIM(coalesce(PS.PersonFirName_FirName, ''))
					|| ' ' || RTRIM(coalesce(PS.PersonSecName_SecName, ''))
                ) as \"Mother_Fio\"
                ,EPS.EvnPS_rid as \"Mother_EvnPS_rid\"
                ,UA.Address_Address as \"Mother_UAddress\"
                ,PA.Address_Address as \"Mother_PAddress\"
                ,EPS.Person_Age as \"Mother_Age\"
                ,PS.FamilyStatus_id as \"Mother_FamilyStatus_id\"
                ,PS.PersonFamilyStatus_IsMarried as \"Mother_IsMarried\"
                ,LSW.LpuSectionWard_Name as \"Mother_Ward_Nam\"
                ,coalesce(bss.BirthSpecStac_CountPregnancy, 0) as \"Mother_CountPregnancy\"
                ,coalesce(bss.BirthSpecStac_CountBirth, 0) as \"Mother_CountBirth\"
                ,bss.BirthSpecStac_CountChild as \"Mother_CountChild\"
                ,coalesce(bss.BirthSpecStac_CountChildAlive, 0) as \"Mother_CountChildAlive\"
                ,coalesce((bss.BirthSpecStac_CountChild - bss.BirthSpecStac_CountChildAlive), '') as \"Mother_DeathChildCount\"
                ,P.Post_Name as \"Mother_Post_Name\"
            FROM v_EvnLink EL
				INNER JOIN v_EvnPS EPS ON EL.Evn_id = EPS.EvnPS_rid
				LEFT JOIN PersonState PS ON EPS.Person_id = PS.Person_id
				LEFT JOIN Address UA ON PS.UAddress_id = UA.Address_id
				LEFT JOIN Address PA ON PS.PAddress_id = PA.Address_id
				LEFT JOIN v_EvnSection ES ON ES.EvnSection_rid = EL.Evn_id
				LEFT JOIN LpuSectionWard LSW ON ES.LpuSectionWard_id = LSW.LpuSectionWard_id
				LEFT JOIN dbo.v_BirthSpecStac bss ON bss.EvnSection_id = ES.EvnSection_id
				LEFT JOIN Job J ON PS.Job_id = J.Job_id
				LEFT JOIN Post P ON J.Post_id = P.Post_id
            WHERE EL.Evn_lid = :LpuSection_id
        ";

        //echo getDebugSQL($query, array('EvnSection_pid' => $evnSection_id)); exit();
        
		$result = $this->db->query($query, array(
			'LpuSection_id' => $LpuSection_id
		));
        
        

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
    
    /**
     * Поучает список движений
     * 
     * @param type $LpuSection_id
     * @return boolean
     */
    function getLpuSectionData($LpuSection_id, $Lpu_id) {

        // Sannikov
        
        $query = "
            SELECT 
                v_ES.EvnSection_id as \"EvnSection_id\"
                ,vd.Diag_FullName as \"Diag_FullName\"
                ,v_ES.LpuSection_id as \"LpuSection_id\"
                ,vls.LpuSection_Code as \"LpuSection_Code\"
                ,vls.Lpu_id as \"Lpu_id\"
                ,vls.LpuSection_Name as \"LpuSection_Name\"
                ,vls.LpuSection_FullName as \"LpuSection_FullName\"
                ,RTRIM(coalesce(v_LS.LpuSection_FullName, '')) as \"LpuSection_FullName\"
            FROM v_EvnSection v_ES 
				left join v_EvnOtherSection v_EOS on v_EOS.EvnOtherSection_pid = v_ES.EvnSection_id
				left join v_EvnOtherStac v_EOST on v_EOST.EvnOtherStac_pid = v_ES.EvnSection_id
				left join v_EvnOtherSectionBedProfile v_EOSBP on v_EOSBP.EvnOtherSectionBedProfile_pid = v_ES.EvnSection_id
				LEFT JOIN v_LpuSection v_LS ON 
					v_LS.LpuSection_id = v_EOS.LpuSection_oid
					OR v_LS.LpuSection_id = v_EOSBP.LpuSection_oid
					OR v_LS.LpuSection_id = v_EOST.LpuSection_oid
				LEFT JOIN v_LpuSection vls ON v_ES.LpuSection_id = vls.LpuSection_id
				LEFT JOIN v_Diag vd ON v_ES.Diag_id = vd.Diag_id
            WHERE v_ES.EvnSection_pid = :LpuSection_id 
                AND v_ES.Lpu_id = :Lpu_id
            ORDER BY v_ES.EvnSection_id DESC
        ";

        //echo getDebugSQL($query, array('EvnSection_pid' => $evnSection_id,'Lpu_id' => $Lpu_id)); exit();
        
		$result = $this->db->query($query, array(
			'LpuSection_id' => $LpuSection_id,
            'Lpu_id' => $Lpu_id
		));
        
        

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
    /**
	 * Некая абстрактная функция TODO: описать
	 */
    function getLastSectionDiag($LpuSection_id, $Lpu_id) {

        // Sannikov
        
        $query = "
            SELECT
                vd.Diag_FullName as \"Diag_FullName\"
                ,ESLast.EvnSection_id as \"EvnSection_id\"
            from v_EvnPS EPS
				left join v_EvnSection ESLast on ESLast.EvnSection_pid = EPS.EvnPS_id
				LEFT JOIN v_Diag vd ON ESLast.Diag_id = vd.Diag_id --OR EPS.Diag_id = vd.Diag_id
            WHERE EPS.EvnPS_id = :LpuSection_id
            ORDER BY ESLast.EvnSection_id DESC
            limit 1
        ";

        //echo getDebugSQL($query, array('EvnSection_pid' => $evnSection_id,'Lpu_id' => $Lpu_id)); exit();
        
		$result = $this->db->query($query, array(
			'LpuSection_id' => $LpuSection_id,
            'Lpu_id' => $Lpu_id
		));
        
        

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
    }
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	function getEvnPSFields($data) {
	
		if (empty($data['EvnPS_id'])){
			//var_dump($data);
			//return false;
			$where = ' and EPS.EvnPS_id = (select EvnSection_pid from v_EvnSection where EvnSection_id = :EvnSection_id)';
		}
		else{
			$where = ' and EPS.EvnPS_id = :EvnPS_id';
		}
		//Golovin
		$query = "
			select
				RTRIM(coalesce(AnatomWhere.AnatomWhere_Name, '')) as \"AnatomWhere_Name\",
				RTRIM(coalesce(DiagA.Diag_Code, '')) as \"DiagA_Code\",
				RTRIM(coalesce(DiagA.Diag_Name, '')) as \"DiagA_Name\",
				RTRIM(coalesce(DiagH.Diag_Code, '')) as \"DiagH_Code\",
				RTRIM(coalesce(DiagH.Diag_Name, '')) as \"DiagH_Name\",
				RTRIM(coalesce(DiagP.Diag_Code, '')) as \"DiagP_Code\",
				RTRIM(coalesce(DiagP.Diag_Name, '')) as \"DiagP_Name\",
				EPS.Person_Age as \"Person_Age\",
				coalesce(to_char(Document.Document_begDate, 'dd.mm.yyyy'), '') as \"Document_begDate\",
				RTRIM(coalesce(Document.Document_Num, '')) as \"Document_Num\",
				RTRIM(coalesce(Document.Document_Ser, '')) as \"Document_Ser\",
				RTRIM(coalesce(DocumentType.DocumentType_Name, '')) as \"DocumentType_Name\",
				RTRIM(coalesce(DocumentType.DocumentType_Code, '')) as \"DocumentType_Code\",
				RTRIM(coalesce(DocumentType.DocumentType_MaskSer, '')) as \"DocumentType_MaskSer\",
				RTRIM(coalesce(DocumentType.DocumentType_MaskNum, '')) as \"DocumentType_MaskNum\",
				EPS.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EPS.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				to_char(EPS.EvnPS_disDate, 'dd.mm.yyyy') as \"EvnPS_disDate\",
				EPS.EvnPS_disTime as \"EvnPS_disTime\",
				EPS.EvnPS_HospCount as \"EvnPS_HospCount\",
				to_char(ED.EvnDie_expDate, 'dd.mm.yyyy') as \"EvnDie_expDate\",
				ED.EvnDie_expTime as \"EvnDie_expTime\",
				coalesce(IsAmbul.YesNo_Code, 0) as \"EvnLeave_IsAmbul\",
				coalesce(IsAnatom.YesNo_Code, 0) as \"EvnDie_IsAnatom\",
				coalesce(IsDiagMismatch.YesNo_Code, 0) as \"EvnPS_IsDiagMismatch\",
				coalesce(IsImperHosp.YesNo_Code, 0) as \"EvnPS_IsImperHosp\",
				coalesce(IsShortVolume.YesNo_Code, 0) as \"EvnPS_IsShortVolume\",
				coalesce(IsUnlaw.YesNo_Code, 0) as \"EvnPS_IsUnlaw\",
				coalesce(IsUnport.YesNo_Code, 0) as \"EvnPS_IsUnport\",
				coalesce(IsWrongCure.YesNo_Code, 0) as \"EvnPS_IsWrongCure\",
				coalesce(EPS.EvnPS_CodeConv, '') as \"EvnPS_CodeConv\",
				coalesce(EPS.EvnPS_NumCard, '') as \"EvnPS_NumCard\",
				coalesce(EPS.EvnPS_NumConv, '') as \"EvnPS_NumConv\",
				to_char(EPS.EvnPS_setDate, 'dd.mm.yyyy') as \"EvnPS_setDate\",
				EPS.EvnPS_setTime as \"EvnPS_setTime\",
                to_char(EPS.EvnPS_OutcomeDT, 'dd.mm.yyyy') as \"EvnPS_outcomeDate\",
				to_char(EPS.EvnPS_OutcomeDT, 'hh24:mi:ss') as \"EvnPS_outcomeTime\",
				EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\",
				EPS.Okei_id as \"Okei_id\",
				COALESCE(EL.EvnLeave_UKL, ED.EvnDie_UKL, EOL.EvnOtherLpu_UKL, EOS.EvnOtherSection_UKL, EOST.EvnOtherStac_UKL) as \"EvnLeave_UKL\",
				RTRIM(coalesce(L.Lpu_Name, '')) as \"Lpu_Name\",
				RTRIM(coalesce(L.Lpu_Nick, '')) as \"Lpu_Nick\",
				COALESCE('('
					||cast(PreHospLpu.Lpu_id as varchar)
					||')', '('||cast(PHOM.OrgMilitary_id as varchar)
					||')', '('||cast(PHO.Org_id as varchar)
					||')', ''
				) as \"PreHospLpu_Id\",
				RTRIM(COALESCE(PHLS.LpuSection_Name, PreHospLpu.Lpu_Name, PHOM.OrgMilitary_Name, PHO.Org_Name, '')) as \"PrehospOrg_Name\",
				RTRIM(COALESCE(PreHospLpu.Lpu_Nick, PHO.Org_Nick, '')) as \"PrehospOrg_Nick\",
				RTRIM(coalesce(L.UAddress_Address, '')) as \"LpuAddress\",
                CASE PrehospArrive.PrehospArrive_Code
                    WHEN 2 THEN PrehospArrive.PrehospArrive_Name
                    ELSE CASE PrehospDirect.PrehospDirect_Code
                            WHEN 1 THEN L.Lpu_Nick
                            ELSE RTRIM(COALESCE(PreHospLpu.Lpu_Nick, PHO.Org_Nick, '')) || '  ' || PrehospDirect.PrehospDirect_Name
                         END
                END as \"KemNapravlen\",
				RTRIM(coalesce(LS.LpuSection_Name, '')) as \"LpuSection_Name\",
				RTRIM(coalesce(OSTLS.LpuSection_Name, '')) as \"OtherStac_Name\",
				RTRIM(coalesce(OSTLUT.LpuUnitType_Name, '')) as \"OtherStacType_Name\",
				RTRIM(coalesce(LpuRegion.LpuRegion_Name, '')) as \"LpuRegion_Name\",
				RTRIM(coalesce(OD.Org_Name, '')) as \"OrgDep_Name\",
				RTRIM(coalesce(OJ.Org_Name, '')) as \"OrgJob_Name\",
				RTRIM(coalesce(OS.Org_Name, '')) as \"OrgSmo_Name\",
				RTRIM(coalesce(OS.Org_Nick, '')) as \"OrgSmo_Nick\",
				RTRIM(coalesce(OS.Org_Code, '')) as \"OrgSmo_Code\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				RTRIM(PC.PersonCard_Code) as \"PersonCard_Code\",
				RTRIM(RTRIM(coalesce(PS.Person_Surname, '')) || ' ' || RTRIM(coalesce(PS.Person_Firname, '')) || ' ' || RTRIM(coalesce(PS.Person_Secname, ''))) as \"Person_Fio\",
				RTRIM(coalesce(PS.Person_Snils, '')) as \"Person_Snils\",
				RTRIM(coalesce(PS.Person_EdNum, '')) as \"Person_EdNum\",
				RTRIM(coalesce(PAddr.Address_Address, '')) as \"PAddress_Name\",
				RTRIM(coalesce(UAddr.Address_Address, '')) as \"UAddress_Name\",
				RTRIM(coalesce(KLAreaType.KLAreaType_Name, '')) as \"KLAreaType_Name\",
				RTRIM(coalesce(KLAreaType.KLAreaType_SysNick, '')) as \"KLAreaType_SysNick\",
                coalesce(to_char(KO.Ocato),'') as \"Ocato\",                              -- PETROV
                coalesce(to_char(KLA.KLAdr_Ocatd),'') as \"OMSSprTerr_Code\",             -- PETROV
				RTRIM(COALESCE(LeaveCause.LeaveCause_Name, OLC.LeaveCause_Name, OSC.LeaveCause_Name, OSTC.LeaveCause_Name)) as \"LeaveCause_Name\",
				RTRIM(coalesce(LeaveType.LeaveType_Name, '')) as \"LeaveType_Name\",
				LTRIM(RTRIM(MPRec.Person_SurName)) || ' ' ||LTRIM(RTRIM(SUBSTRING(MPRec.Person_FirName,1,1)))||'.'||LTRIM(RTRIM(SUBSTRING(MPRec.Person_SecName,1,1)))||'.' as \"PreHospMedPersonal_Fio\",
				MPRec.Dolgnost_Name as \"Dolgnost_Name\",
                RTRIM(coalesce(MPRec.MedPersonal_Code, '')) as \"PreHospMedPersonal_Code\",           -- PETROV
				RTRIM(coalesce(EDAMP.MedPersonal_TabCode, '')) as \"AnatomMedPersonal_Code\",
				RTRIM(coalesce(EDAMP.Person_Fio, '')) as \"AnatomMedPersonal_Fio\",
				RTRIM(coalesce(EDMP.MedPersonal_TabCode, '')) as \"EvnDieMedPersonal_Code\",
				RTRIM(coalesce(EDMP.Person_Fin, '')) as \"EvnDieMedPersonal_Fin\",
				RTRIM(coalesce(OLC.LeaveCause_Name, '')) as \"OtherLpuCause_Name\",
				RTRIM(coalesce(OSC.LeaveCause_Name, '')) as \"OtherSectionCause_Name\",
				RTRIM(coalesce(OSTC.LeaveCause_Name, '')) as \"OtherStacCause_Name\",
				RTRIM(coalesce(OtherLpu.Lpu_Name, '')) as \"OtherLpu_Name\",
				to_char(Polis.Polis_begDate, 'dd.mm.yyyy') as \"Polis_begDate\",
				RTRIM(coalesce(case when Polis.PolisType_id = 4 then PS.Person_EdNum else Polis.Polis_Num end, '')) as \"Polis_Num\",
				RTRIM(coalesce(case when Polis.PolisType_id = 4 then '' else Polis.Polis_Ser end, '')) as \"Polis_Ser\",
				RTRIM(coalesce(PolisType.PolisType_Name, '')) as \"PolisType_Name\",
				RTRIM(coalesce(Post.Post_Name, '')) as \"Post_Name\",
				RTRIM(coalesce(PayType.PayType_Name, '')) as \"PayType_Name\",
				RTRIM(coalesce(PHT.PrehospTrauma_Name, '')) as \"PrehospTrauma_Name\",
				RTRIM(coalesce(PrehospArrive.PrehospArrive_Name, '')) as \"PrehospArrive_Name\",
				RTRIM(coalesce(PrehospDirect.PrehospDirect_Name, '')) as \"PrehospDirect_Name\",
				RTRIM(coalesce(PrehospToxic.PrehospToxic_Name, '')) as \"PrehospToxic_Name\",
				RTRIM(coalesce(PrehospType.PrehospType_Name, '')) as \"PrehospType_Name\",
				RTRIM(coalesce(PrehospType.PrehospType_SysNick, '')) as \"PrehospType_SysNick\",
				RTRIM(coalesce(ResultDesease.ResultDesease_Name, '')) as \"ResultDesease_Name\",
				RTRIM(coalesce(Sex.Sex_Name, '')) as \"Sex_Name\",
				RTRIM(coalesce(Sex.Sex_Code, '')) as \"Sex_Code\",
				RTRIM(coalesce(SocStatus.SocStatus_Name, '')) as \"SocStatus_Name\",
				RTRIM(coalesce(InvalidType.InvalidType_begDate, '')) as \"InvalidType_begDate\",
				RTRIM(coalesce(InvalidType.InvalidType_Code, '')) as \"InvalidType_Code\",
				RTRIM(coalesce(InvalidType.InvalidType_Name, '')) as \"InvalidType_Name\",
				RTRIM(coalesce(PersonPrivilege.PrivilegeType_Name, '')) as \"PrivilegeType_Name\",
				RTRIM(coalesce(PersonPrivilege.PrivilegeType_Code, '')) as \"PrivilegeType_Code\",
				RTRIM(coalesce(PersonPrivilege.PersonPrivilege_Serie, '')) as \"PersonPrivilege_Serie\",
				RTRIM(coalesce(PersonPrivilege.PersonPrivilege_Number, '')) as \"PersonPrivilege_Number\",
				to_char(COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate), 'dd.mm.yyyy') as \"EvnPS_disDate\",
				COALESCE(EL.EvnLeave_setTime, ED.EvnDie_setTime, EOL.EvnOtherLpu_setTime, EOS.EvnOtherSection_setTime, EOST.EvnOtherStac_setTime) as \"EvnPS_disTime\",
				RTRIM(coalesce(EvnUdost.EvnUdost_Ser, '')
					|| ' ' || coalesce(EvnUdost.EvnUdost_Num, '')
				) as \"EvnUdost_SerNum\",
				RTRIM(COALESCE(AnatomLpu.Lpu_Name, AnatomLS.LpuSection_Name, AnatomOrg.OrgAnatom_Name, '')) as \"EvnAnatomPlace\",
				coalesce(EvnSection.LpuUnitType_Code, 0) as \"LpuUnitType_Code\",
				ltrim(rtrim(MSF_did.Person_SurName))
					|| ' ' || ltrim(rtrim(MSF_did.Person_FirName))
					|| ' ' || ltrim(rtrim(MSF_did.Person_SecName)
				) as \"MedPersonal_did\",
				LTRIM(RTRIM(ESLast.LpuSection_Name)) as \"HospSection_Name\",
				ESLast.LpuSectionBedProfile_Name as \"HospSectionBedProfile_Name\",
				coalesce(to_char(ESLast.EvnSection_disDate, 'dd.mm.yyyy'), '') as \"Hosp_disDate\",
				ESLast.HospitalDays as \"HospitalDays\",
				ESLast.LeaveType_Name as \"HospLeaveType_Name\",
				ESLast.LeaveType_SysNick as \"HospLeaveType_Nick\",
				ESLast.ResultDesease_Name as \"HospResultDesease_Name\",
				ESLast.ResultDesease_SysNick as \"HospResultDesease_SysNick\",
				EPS.EntranceModeType_id as \"EntranceModeType_id\",
				DK.DeputyKind_Name as \"DeputyKind_Name\",
				EPS.EvnPS_DeputyFIO as \"EvnPS_DeputyFIO\",
                RTRIM(coalesce(EPS.EvnPS_DeputyContact, 'нет')) as \"EvnPS_DeputyContact\",
                Hosp.HospType_Name as \"HospType_Name\",
				RTRIM(coalesce(EPS.EvnPS_PhaseDescr_pid, ''))  as \"EvnPS_PhaseDescr_pid\",
				RTRIM(coalesce(EPS.EvnPS_PhaseDescr_did, ''))  as \"EvnPS_PhaseDescr_did\",
				RTRIM(coalesce(AttachedLpu.AttachedLpuName, '')) as \"AttachedLpuName\",
				RTRIM(coalesce(AttachedLpu.AttachedLpuNick, '')) as \"AttachedLpuNick\",
				PS.Person_Phone as \"Person_Phone\" -- Pavel Petrov

					-- Sannikov
				,
                    -- подсчет койкодней  
				case when ESLast.EvnSection_disDate is not null
				then
					case
						when DATEDIFF('DAY', ESLast.EvnSection_setDate, ESLast.EvnSection_disDate) + 1 > 1
						then DATEDIFF('DAY', ESLast.EvnSection_setDate, ESLast.EvnSection_disDate)
						else DATEDIFF('DAY', ESLast.EvnSection_setDate, ESLast.EvnSection_disDate) + 1
					end
				else null
				end as \"koikodni\",
				RTRIM(coalesce(PS.Person_Phone, '')) as \"PersonPhone_Phone\" -- TODO: дублирует код Павла Петрова
				,RTRIM(coalesce(PS.FamilyStatus_id, '')) as \"FamilyStatus_id\"  -- Семейное положение
                ,coalesce(PS.PersonFamilyStatus_IsMarried, '') as \"PersonFamilyStatus_IsMarried\"
                ,PS.Person_id as \"Person_id\"
				    -- Выписка
				,RTRIM(coalesce(LeaveType.LeaveType_Code, '')) as \"LeaveType_Code\"
                ,to_char(EPS.EvnPS_disDate, 'dd.mm.yyyy') as \"new_EvnPS_disDate\"
                ,RTRIM(coalesce(EPS.EvnPS_id, '555')) as \"LpuSection_id\"  -- ID Движения
                    -- Роды - данные (дети в отдельном запросе)
                ,bss.EvnSection_id as \"EvnSection_id\"  -- ТЕСТ joinа для родов
                ,bss.BirthSpecStac_id as \"BirthSpecStac_id\",
                bss.EvnSection_id as \"EvnSection_id\",
                coalesce(bss.BirthSpecStac_CountPregnancy, 0) as \"BirthSpecStac_CountPregnancy\",
                coalesce(bss.BirthSpecStac_CountBirth, 0) as \"BirthSpecStac_CountBirth\",
                bss.BirthSpecStac_CountChild as \"BirthSpecStac_CountChild\",
                coalesce(bss.BirthSpecStac_CountChildAlive, 0) as \"BirthSpecStac_CountChildAlive\",
                coalesce((bss.BirthSpecStac_CountChild - bss.BirthSpecStac_CountChildAlive), '') as \"DeathChildCount\",  -- TODO: разобраться с мертворожденными
                bss.BirthResult_id as \"BirthResult_id\",
                bss.BirthPlace_id as \"BirthPlace_id\",
                bss.BirthSpecStac_OutcomPeriod as \"BirthSpecStac_OutcomPeriod\",
                bss.BirthSpecStac_OutcomDT as \"BirthSpecStac_OutcomDT\",
                DATE_PART('hour', bss.BirthSpecStac_OutcomDT) as \"BirthSpecStac_OutcomDT_h\",
                DATE_PART('minute', bss.BirthSpecStac_OutcomDT) as \"BirthSpecStac_OutcomDT_m\",
                bss.BirthSpec_id as \"BirthSpec_id\",
                bss.BirthSpecStac_IsHIVtest as \"BirthSpecStac_IsHIVtest\",
                bss.BirthSpecStac_IsHIV as \"BirthSpecStac_IsHIV\",
                bss.AbortType_id as \"AbortType_id\",
                bss.BirthSpecStac_IsMedicalAbort as \"BirthSpecStac_IsMedicalAbort\",
                coalesce(bss.BirthSpecStac_BloodLoss, 0) as \"BirthSpecStac_BloodLoss\",
                bss.PregnancySpec_id as \"PregnancySpec_id\",
                bss.pmUser_insID as \"pmUser_insID\",
                bss.pmUser_updID as \"pmUser_updID\",
                bss.BirthSpecStac_insDT as \"BirthSpecStac_insDT\",
                bss.BirthSpecStac_updDT as \"BirthSpecStac_updDT\"
                    -- даты
                ,DATE_PART('year', EPS.EvnPS_setDate ) as \"EvnPS_setDate_Year\"
                ,DATE_PART('month', EPS.EvnPS_setDate ) as \"EvnPS_setDate_Month\"
                ,DATE_PART('day', EPS.EvnPS_setDate ) as \"EvnPS_setDate_Day\"
                ,DATE_PART('hour', EPS.EvnPS_setDate ) as \"EvnPS_setDate_Hour\"
                ,DATE_PART('minute', EPS.EvnPS_setDate ) as \"EvnPS_setDate_Minute\"
                ,to_char(EPS.EvnPS_setDate, 'yyyy-mm-dd hh24:mi:ss') as \"EvnPS_setDate_raw\"
                    -- Дата выписки из движения (для перевода)
                ,to_char(COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate), 'yyyy-mm-dd hh24:mi:ss') as \"EvnPS_disDate_raw\"    
                ,DATE_PART(yyyy, COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate) ) as \"EvnPS_disDate_Year\"
                ,DATE_PART(mm, COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate) ) as \"EvnPS_disDate_Month\"
                ,DATE_PART(dd, COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate) ) as \"EvnPS_disDate_Day\"
                ,DATE_PART(hh, COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate) ) as \"EvnPS_disDate_Hour\"
                ,DATE_PART(minute, COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate) ) as \"EvnPS_disDate_Minute\"
                    -- Дата выписки по КВС (из v_EvnPS)
                ,DATE_PART(yyyy, EPS.EvnPS_disDate ) as \"last_EvnPS_disDate_Year\"
                ,DATE_PART(mm, EPS.EvnPS_disDate ) as \"last_EvnPS_disDate_Month\"
                ,DATE_PART(dd, EPS.EvnPS_disDate ) as \"last_EvnPS_disDate_Day\"
                ,DATE_PART(hh, EPS.EvnPS_disDate ) as \"last_EvnPS_disDate_Hour\"
                ,DATE_PART(minute, EPS.EvnPS_disDate ) as \"last_EvnPS_disDate_Minute\"
                ,coalesce(EPS.EvnPS_disTime, '') as \"last_EvnPS_disTime\"
                ,to_char(COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate), 'dd.mm.yyyy') as \"other_EvnPS_disDate\"
				,COALESCE(EL.EvnLeave_setTime, ED.EvnDie_setTime, EOL.EvnOtherLpu_setTime, EOS.EvnOtherSection_setTime, EOST.EvnOtherStac_setTime) as \"other_EvnPS_disTime\"
				-- Диагноз ребенка (и не только - взято из Samara_Search_model)
                ,coalesce(Dtmp.Diag_FullName, '') as \"new_Diag_Name\"
                ,ESLast.EvnSection_id as \"my_EvnSection_id\"
                ,ESLast.Diag_id as \"my_Diag_id\"
                ,ESLast.LpuSection_id as \"my_LpuSection_id\"
                ,to_char(EPS.EvnPS_disDate, 'dd.mm.yyyy') as \"EvnPS_disDate\"
                ,to_char(EPS.EvnPS_disDate, 'dd.mm.yyyy') as \"EvnPS_disDate\"
                ,coalesce(vlsw.LpuSectionWard_Name, '') as \"LpuSectionWard_Name\"
                ,RTRIM(coalesce(v_LS.LpuSection_FullName, '')) as \"LpuSection_FullName\"
				-- END OF Sannikov
			from v_EvnPS EPS
				inner join v_Lpu L on L.Lpu_id = EPS.Lpu_id
				inner join v_PersonState PS on PS.Person_id = EPS.Person_id
					-- PS.Server_id = EPS.Server_id and PS.PersonEvn_id = EPS.PersonEvn_id
			--	left join v_EvnSection ESLast on ESLast.EvnSection_pid = EPS.EvnPS_id
				--	and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
                    
				------------------------------------------------------------------------------------------ samara
				left join 
				(select 
					EvnClass.EvnClass_id, 
					EvnClass.EvnClass_Name,
					EvnSection.EvnSection_id, 	
					cast(cast(Evn.Evn_setDT as date) as timestamp) as EvnSection_setDate,
					left(cast(Evn_setDT as time),5) as EvnSection_setTime,
					cast(cast(Evn.Evn_didDT as date) as timestamp) as EvnSection_didDate,
					left(cast(Evn_didDT as time),5) as EvnSection_didTime,
					cast(cast(Evn.Evn_disDT as date) as timestamp) as EvnSection_disDate,
					left(cast(Evn_disDT as time),5) as EvnSection_disTime,
					DateDiff('day', Evn.Evn_setDT, Evn_disDT) HospitalDays,
					Evn.Evn_pid as EvnSection_pid,
					Evn.Evn_rid as EvnSection_rid,
					Evn.Lpu_id as Lpu_id,
					Evn.Server_id as Server_id,
					Evn.PersonEvn_id as PersonEvn_id,
					Evn.Evn_setDT as EvnSection_setDT,
					Evn.Evn_disDT as EvnSection_disDT,
					Evn.Evn_didDT as EvnSection_didDT,
					Evn.Evn_insDT as EvnSection_insDT,
					Evn.Evn_updDT as EvnSection_updDT,
					Evn.Evn_Index as EvnSection_Index,
					Evn.Evn_Count as EvnSection_Count,
					Evn.pmUser_insID as pmUser_insID,
					Evn.pmUser_updID as pmUser_updID,
					Evn.Person_id as Person_id,
					Evn.Morbus_id as Morbus_id,
					Evn.Evn_IsSigned as EvnSection_IsSigned,
					Evn.pmUser_signID as pmUser_signID,
					Evn.Evn_signDT as EvnSection_signDT,
					Evn.Evn_IsArchive as EvnSection_IsArchive,
					Evn.Evn_Guid as EvnSection_Guid,
					EvnSection.LpuSection_id as LpuSection_id,
					EvnSection.Diag_id as Diag_id,
					EvnSection.Mes_id as Mes_id,
					EvnSection.PayType_id as PayType_id,
					EvnSection.TariffClass_id as TariffClass_id,
					EvnSection.MedPersonal_id as MedPersonal_id,
					EvnSection.EvnSection_IsInReg as EvnSection_IsInReg,
					EvnSection.Mes_OldCode as Mes_OldCode,
					EvnSection.LpuSectionWard_id as LpuSectionWard_id,
					EvnSection.DiagSetPhase_id as DiagSetPhase_id,
					EvnSection.EvnSection_PhaseDescr as EvnSection_PhaseDescr,
					EvnSection.LeaveType_id as LeaveType_id,
					EvnSection.EvnSection_IsAdultEscort as EvnSection_IsAdultEscort,
					EvnSection.Mes2_id as Mes2_id,
					LpuSection.LpuSection_Name,
					LpuSectionBedProfile.LpuSectionBedProfile_Name,	
					LeaveType_Name,
					LeaveType_SysNick,
					(select ResultDesease_Name from v_ResultDesease where ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOST.ResultDesease_id)) as ResultDesease_Name,
					(select ResultDesease_SysNick from v_ResultDesease where ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOST.ResultDesease_id)) as ResultDesease_SysNick
					
					,EOS.LpuSection_oid AS EOS_LpuSection_oid   -- Sannikov
					,EOST.LpuSection_oid AS EOST_LpuSection_oid  -- Sannikov
				from EvnClass 
				inner join Evn on EvnClass.EvnClass_id = Evn.EvnClass_id and Evn.EvnClass_id in (32)
				inner join EvnSection on Evn.Evn_id = EvnSection.Evn_id
				left join LpuSection on LpuSection.LpuSection_id = EvnSection.LpuSection_id
				left join LpuSectionBedProfile on LpuSectionBedProfile.LpuSectionBedProfile_id = LpuSection.LpuSectionBedProfile_id
				left join v_LeaveType on v_LeaveType.LeaveType_id = EvnSection.LeaveType_id
				left join v_EvnLeave EL on EL.EvnLeave_pid = EvnSection.EvnSection_id
				left join v_EvnOtherLpu EOL on EOL.EvnOtherLpu_pid = EvnSection.EvnSection_id
				left join v_EvnOtherSection EOS on EOS.EvnOtherSection_pid = EvnSection.EvnSection_id
				left join v_EvnOtherStac EOST on EOST.EvnOtherStac_pid = EvnSection.EvnSection_id
				where coalesce(Evn.Evn_deleted,1) = 1 
				) ESLast on ESLast.EvnSection_pid = EPS.EvnPS_id
				
								-- Sannikov (операции, роды, палата)
									-- Данные родов
								LEFT JOIN dbo.v_BirthSpecStac bss ON bss.EvnSection_id = ESLast.EvnSection_id
								
									-- Палата
								LEFT JOIN v_LpuSectionWard vlsw ON ESLast.LpuSectionWard_id = vlsw.LpuSectionWard_id
								
									-- Диагноз ребенка (и не только - взято из Samara_Search_model)
								left join v_Diag Dtmp on Dtmp.Diag_id = ESLast.Diag_id
									
									-- Другое ЛПУ или отделение или тип стационара
				
								LEFT JOIN v_LpuSection v_LS ON 
									v_LS.LpuSection_id = ESLast.EOS_LpuSection_oid
									OR v_LS.LpuSection_id = ESLast.EOST_LpuSection_oid
				
								-- END OF Sannikov
				---------------------------------------------------------------------------------------------------
                    
				left join Address UAddr on UAddr.Address_id = PS.UAddress_id
				left join Address PAddr on PAddr.Address_id = PS.PAddress_id
				left join KLAreaType on KLAreaType.KLAreaType_id = UAddr.KLAreaType_id
                left join lateral(
					select
						vak.KLADR_Ocatd as Ocato
					from v_Address_KLADR vak
					where vak.Address_id = PAddr.Address_id
					limit 1
				) KO on true
                left join lateral(
					select
						kla.KLAdr_Ocatd
					from KLArea kla
					where kla.KLArea_id = PAddr.KLRgn_id
					limit 1
				) KLA on true
				left join Document on Document.Document_id = PS.Document_id
				left join DocumentType on DocumentType.DocumentType_id = Document.DocumentType_id
				left join LpuSection LS on LS.LpuSection_id = EPS.LpuSection_pid
				left join LpuSection PHLS on PHLS.LpuSection_id = EPS.LpuSection_did
				left join OrgDep on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org OD on OD.Org_id = OrgDep.Org_id
				left join Org PHO on PHO.Org_id = EPS.Org_did
				left join v_OrgMilitary PHOM on PHOM.OrgMilitary_id = EPS.OrgMilitary_did
				left join Job on Job.Job_id = PS.Job_id
				left join Org OJ on OJ.Org_id = Job.Org_id
				left join v_PersonCard PC on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EPS.EvnPS_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EPS.EvnPS_insDT)
					and PC.Lpu_id = EPS.Lpu_id
				left join LpuRegion on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join Post on Post.Post_id = Job.Post_id
				left join Polis on Polis.Polis_id = PS.Polis_id
				left join PolisType on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
                left join v_OmsSprTerr OST on OST.OmsSprTerr_id = Polis.OmsSprTerr_id
				left join Org OS on OS.Org_id = OrgSmo.Org_id
				left join v_EvnLeave EL on EL.EvnLeave_pid = ESLast.EvnSection_id
				left join v_EvnDie ED on ED.EvnDie_pid = ESLast.EvnSection_id
				left join v_EvnOtherLpu EOL on EOL.EvnOtherLpu_pid = ESLast.EvnSection_id
				left join v_EvnOtherSection EOS on EOS.EvnOtherSection_pid = ESLast.EvnSection_id
				left join v_EvnOtherStac EOST on EOST.EvnOtherStac_pid = ESLast.EvnSection_id
				left join Diag DiagH on DiagH.Diag_id = EPS.Diag_did
				left join Diag DiagP on DiagP.Diag_id = EPS.Diag_pid
				left join Diag DiagA on DiagA.Diag_id = ED.Diag_aid
				left join AnatomWhere on AnatomWhere.AnatomWhere_id = ED.AnatomWhere_id
				left join LeaveCause on LeaveCause.LeaveCause_id = EL.LeaveCause_id
				left join LeaveCause OSC on OSC.LeaveCause_id = EOS.LeaveCause_id
				left join LeaveCause OSTC on OSTC.LeaveCause_id = EOST.LeaveCause_id
				left join LeaveCause OLC on OLC.LeaveCause_id = EOL.LeaveCause_id
				left join LeaveType on LeaveType.LeaveType_id = EPS.LeaveType_id
				left join v_Lpu OtherLpu on OtherLpu.Lpu_id = EOL.Lpu_oid
				left join v_Lpu PreHospLpu on PreHospLpu.Lpu_id = EPS.Lpu_did
				left join v_MedPersonal MPRec on MPRec.MedPersonal_id = EPS.MedPersonal_pid
					and MPRec.Lpu_id = EPS.Lpu_id
				left join v_MedPersonal EDMP on EDMP.MedPersonal_id = ED.MedPersonal_id
					and EDMP.Lpu_id = ED.Lpu_id
				left join v_MedPersonal EDAMP on EDAMP.MedPersonal_id = ED.MedPersonal_aid
					and EDAMP.Lpu_id = ED.Lpu_id
				left join v_Lpu AnatomLpu on AnatomLpu.Lpu_id = ED.Lpu_aid
				left join v_OrgAnatom AnatomOrg on AnatomOrg.OrgAnatom_id = ED.OrgAnatom_id
				left join LpuSection AnatomLS on AnatomLS.LpuSection_id = ED.LpuSection_aid
				left join LpuUnitType OSTLUT on OSTLUT.LpuUnitType_id = EOST.LpuUnitType_oid
				left join LpuSection OSTLS on OSTLS.LpuSection_id = EOST.LpuSection_oid
				left join PayType on PayType.PayType_id = EPS.PayType_id
				left join PrehospArrive on PrehospArrive.PrehospArrive_id = EPS.PrehospArrive_id
				left join PrehospDirect on PrehospDirect.PrehospDirect_id = EPS.PrehospDirect_id
				left join PrehospToxic on PrehospToxic.PrehospToxic_id = EPS.PrehospToxic_id
				left join v_PrehospTrauma PHT on PHT.PrehospTrauma_id = EPS.PrehospTrauma_id
				left join PrehospType on PrehospType.PrehospType_id = EPS.PrehospType_id
				left join ResultDesease on ResultDesease.ResultDesease_id = coalesce(EL.ResultDesease_id, coalesce(EOL.ResultDesease_id, coalesce(EOS.ResultDesease_id, EOST.ResultDesease_id)))
				left join Sex on Sex.Sex_id = PS.Sex_id
				left join SocStatus on SocStatus.SocStatus_id = PS.SocStatus_id
				left join YesNo IsAmbul on IsAmbul.YesNo_id = EL.EvnLeave_IsAmbul
				left join YesNo IsAnatom on IsAnatom.YesNo_id = ED.EvnDie_IsAnatom
				left join YesNo IsDiagMismatch on IsDiagMismatch.YesNo_id = EPS.EvnPS_IsDiagMismatch
				left join YesNo IsImperHosp on IsImperHosp.YesNo_id = EPS.EvnPS_IsImperHosp
				left join YesNo IsShortVolume on IsShortVolume.YesNo_id = EPS.EvnPS_IsShortVolume
				left join YesNo IsUnlaw on IsUnlaw.YesNo_id = EPS.EvnPS_IsUnlaw
				left join YesNo IsUnport on IsUnport.YesNo_id = EPS.EvnPS_IsUnport
				left join YesNo IsWrongCure on IsWrongCure.YesNo_id = EPS.EvnPS_IsWrongCure
				left join v_MedPersonal MSF_did on MSF_did.MedPersonal_id = EPS.MedPersonal_did and MSF_did.Lpu_id = :Lpu_id 
				left join lateral(
					select
						PrivilegeType_Code as InvalidType_Code,
						PersonPrivilege_Group as InvalidType_Name,
						to_char(PersonPrivilege_begDate, 'dd.mm.yyyy') as InvalidType_begDate
					from
						v_PersonPrivilege
					where PersonPrivilege_Group is not null
						and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) InvalidType on true
				left join lateral(
					select
						PrivilegeType_Name,
						PrivilegeType_Code,
						PersonPrivilege_Serie,
						PersonPrivilege_Number
					from
						v_PersonPrivilege
					where Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) PersonPrivilege on true
				left join lateral(
					select
						EvnUdost_Num,
						EvnUdost_Ser
					from
						v_EvnUdost
					where EvnUdost_setDate <= dbo.tzGetDate()
						and Person_id = PS.Person_id
					order by EvnUdost_setDate desc
					limit 1
				) EvnUdost on true
				left join lateral(
					select
						LUT2.LpuUnitType_Code
					from
						v_EvnSection ES2
						inner join LpuSection LS2 on LS2.LpuSection_id = ES2.LpuSection_id
						inner join LpuUnit LU2 on LU2.LpuUnit_id = LS2.LpuUnit_id
						inner join LpuUnitType LUT2 on LUT2.LpuUnitType_id = LU2.LpuUnitType_id
							and LUT2.LpuUnitType_Code in (2, 3, 4, 5)
					where ES2.EvnSection_pid = EPS.EvnPS_id
					order by ES2.EvnSection_setDate desc
					limit 1
				) EvnSection on true
                left join v_DeputyKind DK on DK.DeputyKind_id = EPS.DeputyKind_id
                left join lateral(
					select
						ps.HospType_id, ht.HospType_Name
					from EvnPS ps
					join v_HospType ht on ht.HospType_id = ps.HospType_id
					where ps.EvnPS_id = EPS.EvnPS_id
					limit 1
				) Hosp on true
				left join lateral(
					select
						Lpu_Name as AttachedLpuName, Lpu_Nick as AttachedLpuNick
					from v_Lpu
					where Lpu_id = PS.Lpu_Id
					limit 1
				) AttachedLpu on true
			where
				EPS.Lpu_id = :Lpu_id".$where
		 . "limit 1";
		//EPS.EvnPS_id = :EvnPS_id
		//";
        //print("<pre>"); echo getDebugSQL($query, array('EvnPS_id' => $data['EvnPS_id'], 'Lpu_id' => $data['Lpu_id'])); /*exit();*/ print("</pre>");
		if (is_null($data['EvnPS_id'])){
			$result = $this->db->query($query, array(
					'EvnSection_id' => $data['EvnSection_id'],
					'Lpu_id' => $data['Lpu_id']
			));
		}
		else{
			$result = $this->db->query($query, array(
					'EvnPS_id' => $data['EvnPS_id'],
					'Lpu_id' => $data['Lpu_id']
			));
		}
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
    /**
	 * Получение количества госпитализаций
	 */
    function getHospCount($data)
	{
		$currentYear = date("Y");
		$beginDate = "01.01.".$currentYear;
		$endDate = "01.01.".($currentYear + 1);
	
		$query = "
			select count(EvnPS.Evn_id) as \"EvnPS_HospCount\"
			from Evn
				inner join EvnPS on Evn.Evn_id = EvnPS.Evn_id
			where Person_id = :Person_id and Evn_setDT between :Evn_setDT_begin and :Evn_setDT_end 
		";
		$result = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'Evn_setDT_begin' => $beginDate,
			'Evn_setDT_end' => $endDate));

		if ( is_object($result) ) {
			return $result->result('object');
		}
		else {
			return false;
		}
	}
    
    
    /**
	 * Получение данных формы редактирования КВС
	 */
	function loadEvnPSEditForm($data) {
		$params = array(
			'EvnPS_id' => $data['EvnPS_id']
			,'Lpu_id' => $data['Lpu_id']
		);
		$accessType = 'EPS.Lpu_id = :Lpu_id';
		$withMedStaffFact_from = '';
		if ($data['session']['isMedStatUser'] == false && isset($data['session']['CurMedStaffFact_id']))
		{
			$accessType .= " AND LU.LpuUnitType_SysNick in ('stac','dstac','hstac','pstac')";
			$withMedStaffFact_from = 'left join v_MedStaffFact MSF on MSF.MedStaffFact_id = :MedStaffFact_id
				left join v_LpuUnit LU on MSF.LpuUnit_id = LU.LpuUnit_id
			';
			$params['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}
		/*if ( isSuperAdmin() ) {
			$accessType = '(1 = 1)';
			$withMedStaffFact_from = '';
		}*/
		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\"
				,EPS.EvnPS_id as \"EvnPS_id\"
				,EPS.EvnPS_IsSigned as \"EvnPS_IsSigned\"
				,EPS.Lpu_id as \"Lpu_id\"
				,EPS.EvnPS_IsCont as \"EvnPS_IsCont\"
				,EPS.Diag_did as \"Diag_did\"
				,EPS.DiagSetPhase_did as \"DiagSetPhase_did\"
				,EPS.EvnPS_PhaseDescr_did as \"EvnPS_PhaseDescr_did\"
				,EPS.Diag_pid as \"Diag_pid\"
				,EPS.DiagSetPhase_pid as \"DiagSetPhase_pid\"
				,EPS.EvnPS_PhaseDescr_pid as \"EvnPS_PhaseDescr_pid\"
				,RTRIM(EPS.EvnPS_NumCard) as \"EvnPS_NumCard\"
				,EPS.LeaveType_id as \"LeaveType_id\"
				,EPS.PayType_id as \"PayType_id\"
				,to_char(EPS.EvnPS_setDT, 'dd.mm.yyyy') as \"EvnPS_setDate\"
				,EPS.EvnPS_setTime as \"EvnPS_setTime\"
				,to_char(EPS.EvnPS_OutcomeDT, 'dd.mm.yyyy') as \"EvnPS_OutcomeDate\"
				,to_char(EPS.EvnPS_OutcomeDT, 'hh24:mi') as \"EvnPS_OutcomeTime\"
				,EPS.EvnDirection_id as \"EvnDirection_id\"
				,EPS.PrehospDirect_id as \"PrehospDirect_id\"
				,EPS.LpuSection_did as \"LpuSection_did\"
				,coalesce(EPS.Org_did, coalesce(LPU_DID.Org_id, EPS.OrgMilitary_did)) as \"Org_did\"
				,LpuDid.Lpu_id as \"Lpu_did\"
				,EPS.LpuSection_pid as \"LpuSection_pid\"
				,EPS.MedPersonal_pid as \"MedStaffFact_pid\"
				,EPS.EvnDirection_Num as \"EvnDirection_Num\"
				,to_char(EPS.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDate\"
				,EPS.PrehospArrive_id as \"PrehospArrive_id\"
				,EPS.EvnPS_CodeConv as \"EvnPS_CodeConv\"
				,EPS.EvnPS_NumConv as \"EvnPS_NumConv\"
				,EPS.PrehospToxic_id as \"PrehospToxic_id\"
				,EPS.PrehospType_id as \"PrehospType_id\"
				,EPS.EvnPS_HospCount as \"EvnPS_HospCount\"
				,EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\"
				,EPS.Okei_id as \"Okei_id\"
				,EPS.PrehospTrauma_id as \"PrehospTrauma_id\"
				,EPS.EvnPS_IsUnlaw as \"EvnPS_IsUnlaw\"
				,EPS.EvnPS_IsUnport as \"EvnPS_IsUnport\"
				,EPS.EvnPS_IsImperHosp as \"EvnPS_IsImperHosp\"
				,EPS.EvnPS_IsNeglectedCase as \"EvnPS_IsNeglectedCase\"
				,EPS.EvnPS_IsShortVolume as \"EvnPS_IsShortVolume\"
				,EPS.EvnPS_IsWrongCure as \"EvnPS_IsWrongCure\"
				,EPS.EvnPS_IsDiagMismatch as \"EvnPS_IsDiagMismatch\"
				,coalesce(EPS.EvnPS_IsWaif, 1) as \"EvnPS_IsWaif\"
				,EPS.EvnPS_IsPLAmbulance as \"EvnPS_IsPLAmbulance\"
				,EPS.PrehospWaifArrive_id as \"PrehospWaifArrive_id\"
				,EPS.PrehospWaifReason_id as \"PrehospWaifReason_id\"
				,ES.LpuSection_id as \"LpuSection_id\"
				,EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\"
				,EPS.EvnPS_IsTransfCall as \"EvnPS_IsTransfCall\"
				,EPS.Person_id as \"Person_id\"
				,EPS.PersonEvn_id as \"PersonEvn_id\"
				,EPS.Server_id as \"Server_id\"
				,EPS.EvnPS_IsWithoutDirection as \"EvnPS_IsWithoutDirection\"
				,EPS.EvnQueue_id as \"EvnQueue_id\"
				,EPS.EvnPS_IsPrehospAcceptRefuse as \"EvnPS_IsPrehospAcceptRefuse\"
				,to_char(EPS.EvnPS_PrehospAcceptRefuseDT, 'dd.mm.yyyy') as \"EvnPS_PrehospAcceptRefuseDT\"
				,to_char(EPS.EvnPS_PrehospWaifRefuseDT, 'dd.mm.yyyy') as \"EvnPS_PrehospWaifRefuseDT\"
				,EPS.LpuSection_eid as \"LpuSection_eid\"
				,EPS.PrehospStatus_id as \"PrehospStatus_id\"
                -- samara
		        ,EPS.MedPersonal_did as \"MedPersonal_did\"
				,EPS.EntranceModeType_id as \"EntranceModeType_id\"
				,EPS.EvnPS_DrugActions as \"EvnPS_DrugActions\"
                ,EPS.HospType_id as \"HospType_id\"
                ,EPS.DeputyKind_id as \"DeputyKind_id\"
                ,EPS.EvnPS_DeputyFIO as \"EvnPS_DeputyFIO\"
                ,EPS.EvnPS_DeputyContact as \"EvnPS_DeputyContact\"
                --,EPS.TimeDeseaseType_id
			FROM
				v_EvnPS EPS
				left join v_Lpu LPU_DID on LPU_DID.Lpu_id = EPS.Lpu_did
				left join v_EvnSection ES on EPS.EvnPS_id = ES.EvnSection_pid and ES.EvnSection_Index = 0
				left join lateral(
					select Lpu_id from v_Lpu where Org_id = coalesce(EPS.Org_did,EPS.OrgMilitary_did) limit 1
				) LpuDid on true
				{$withMedStaffFact_from}
			WHERE (1 = 1)
				and EPS.EvnPS_id = :EvnPS_id
			limit 1
		";
		$result = $this->db->query($query, $params);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
    
	/**
	 * @param $data
	 * @return array
	 */
	function saveEvnPS($data) {
		//$procedure = '';

		// Сохранение КВС
		if ( isset($data['EvnPS_id']) ) {
			$procedure = 'p_EvnPS_upd';
		}
		else {
			$procedure = 'p_EvnPS_ins';
		}

		if ( isset($data['EvnPS_disTime']) ) {
			$data['EvnPS_disDate'] .= ' ' . $data['EvnPS_disTime'];
		}

		if ( isset($data['EvnPS_setTime']) ) {
			$data['EvnPS_setDate'] .= ' ' . $data['EvnPS_setTime'];
		}

		$EvnPS_OutcomeDT = 'null';
		$data['EvnPS_OutcomeDT'] = 'null';
		if (!empty($data['EvnPS_OutcomeDate']) && !empty($data['EvnPS_OutcomeTime'])) {
			$data['EvnPS_OutcomeDT'] = $data['EvnPS_OutcomeDate'] . ' ' . $data['EvnPS_OutcomeTime'];
			$EvnPS_OutcomeDT = ":EvnPS_OutcomeDT";
			
			if ( isset($data['EvnPS_id']) ) {
				// Автоматическое изменение времени движения при изменение его в Исходе пребывания в приемном отделении (refs #19567)
				// Ищем первое движение
				$query = "
					select EvnSection_id as \"EvnSection_id\" from v_EvnSection where EvnSection_pid = :EvnPS_id order by EvnSection_setDT limit 1
				";
				$result = $this->db->query($query, array('EvnPS_id' => $data['EvnPS_id']));
				
				if (is_object($result)) {
					$res =  $result->result('array');
					if (count($res) > 0) {
						// обновляем ему дату/время поступления EvnSection_setDate/EvnSection_setTime
						$query = "
							update Evn
								set Evn_setDT = :Evn_setDT
							where Evn_id = :EvnSection_id
						";
						
						$result = $this->db->query($query, array('Evn_setDT' => $data['EvnPS_OutcomeDT'], 'EvnSection_id' => $res[0]['EvnSection_id']));
					}
				}
			}
		}
		// Если КВС сохраняется из ЭМК по нажатию кнопки "добавить новый случай", то нужно также указать дату и время исхода
		if (!empty($data['addEvnSection'])) {
			$EvnPS_OutcomeDT = 'dbo.tzgetdate()';
		}
		//если выбрано отделение или отказ и не определены дата и время исхода из приемного, то берем текущее время и дату
		if( 
			(!empty($data['LpuSection_eid']) || !empty($data['PrehospWaifRefuseCause_id']))
			 && 'null' == $EvnPS_OutcomeDT
		)
		{
			$EvnPS_OutcomeDT = 'dbo.tzgetdate()';
		}
		
		// проставляем дату отказа, если пустая.
		if(!empty($data['PrehospWaifRefuseCause_id']) && empty($data['EvnPS_PrehospWaifRefuseDT']))
		{
			$EvnPS_PrehospWaifRefuseDT = 'dbo.tzgetdate()';
		} 
		else 
		{
			$EvnPS_PrehospWaifRefuseDT = ":EvnPS_PrehospWaifRefuseDT";
		}
		
		$query = "
			select
				EvnPS_id as \"EvnPS_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procedure . "(
				EvnPS_id := :EvnPS_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnPS_setDT := :EvnPS_setDT,
				EvnPS_OutcomeDT := {$EvnPS_OutcomeDT},
				EvnPS_disDT := :EvnPS_disDT,
				EvnPS_NumCard := :EvnPS_NumCard,
				EvnPS_IsCont := :EvnPS_IsCont,
				Diag_aid := :Diag_aid,
				Diag_pid := :Diag_pid,
				DiagSetPhase_pid := :DiagSetPhase_pid,
				EvnPS_PhaseDescr_pid := :EvnPS_PhaseDescr_pid,
				Diag_did := :Diag_did,
				DiagSetPhase_did := :DiagSetPhase_did,
				EvnPS_PhaseDescr_did := :EvnPS_PhaseDescr_did,
				EvnQueue_id := :EvnQueue_id,
				EvnDirection_id := :EvnDirection_id,
				PrehospArrive_id := :PrehospArrive_id,
				PrehospDirect_id := :PrehospDirect_id,
				PrehospToxic_id := :PrehospToxic_id,
				PayType_id := :PayType_id,
				PrehospTrauma_id := :PrehospTrauma_id,
				PrehospType_id := :PrehospType_id,
				Lpu_did := :Lpu_did,
				Org_did := :Org_did,
				LpuSection_did := :LpuSection_did,
				OrgMilitary_did := :OrgMilitary_did,
				LpuSection_pid := :LpuSection_pid,
				MedPersonal_pid := :MedPersonal_pid,
				EvnDirection_Num := :EvnDirection_Num,
				EvnDirection_setDT := :EvnDirection_setDT,
				EvnPS_CodeConv := :EvnPS_CodeConv,
				EvnPS_NumConv := :EvnPS_NumConv,
				EvnPS_TimeDesease := :EvnPS_TimeDesease,
				Okei_id := :Okei_id,
				EvnPS_HospCount := :EvnPS_HospCount,
				EvnPS_IsUnlaw := :EvnPS_IsUnlaw,
				EvnPS_IsUnport := :EvnPS_IsUnport,
				EvnPS_IsImperHosp := :EvnPS_IsImperHosp,
				EvnPS_IsNeglectedCase := :EvnPS_IsNeglectedCase,
				EvnPS_IsShortVolume := :EvnPS_IsShortVolume,
				EvnPS_IsWrongCure := :EvnPS_IsWrongCure,
				EvnPS_IsDiagMismatch := :EvnPS_IsDiagMismatch,
				EvnPS_IsWithoutDirection := :EvnPS_IsWithoutDirection,
				EvnPS_IsWaif := :EvnPS_IsWaif,
				EvnPS_IsPLAmbulance := :EvnPS_IsPLAmbulance,
				PrehospWaifArrive_id := :PrehospWaifArrive_id,
				PrehospWaifReason_id := :PrehospWaifReason_id,
				PrehospWaifRefuseCause_id := :PrehospWaifRefuseCause_id,
				EvnPS_IsTransfCall := :EvnPS_IsTransfCall,
				EvnPS_IsPrehospAcceptRefuse := :EvnPS_IsPrehospAcceptRefuse,
				EvnPS_PrehospAcceptRefuseDT := :EvnPS_PrehospAcceptRefuseDT,
				EvnPS_PrehospWaifRefuseDT := {$EvnPS_PrehospWaifRefuseDT},
				LpuSection_eid := :LpuSection_eid,
				PrehospStatus_id := :PrehospStatus_id,
				pmUser_id := :pmUser_id,
                -- samara
                MedPersonal_did := :MedPersonal_did,
				EntranceModeType_id := :EntranceModeType_id,
				EvnPS_DrugActions := :EvnPS_DrugActions,
				HospType_id := :HospType_id,
				DeputyKind_id := :DeputyKind_id,
				EvnPS_DeputyFIO := :EvnPS_DeputyFIO,
				EvnPS_DeputyContact := :EvnPS_DeputyContact
            )
		";

		$queryParams = array(
			'EvnPS_id' => (!empty($data['EvnPS_id']) ? $data['EvnPS_id'] : NULL),
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnPS_setDT' => $data['EvnPS_setDate'],
			'EvnPS_disDT' => (!empty($data['EvnPS_disDate']) ? $data['EvnPS_disDate'] : NULL),
			'EvnPS_OutcomeDT' => (!empty($data['EvnPS_OutcomeDT']) ? $data['EvnPS_OutcomeDT'] : NULL),
			'EvnPS_NumCard' => (!empty($data['EvnPS_NumCard']) ? $data['EvnPS_NumCard'] : NULL),
			'EvnPS_IsCont' => (!empty($data['EvnPS_IsCont']) ? $data['EvnPS_IsCont'] : NULL),
			'Diag_aid' => (!empty($data['Diag_aid']) ? $data['Diag_aid'] : NULL),
			'Diag_pid' => (!empty($data['Diag_pid']) ? $data['Diag_pid'] : NULL),
			'DiagSetPhase_pid' => (!empty($data['DiagSetPhase_pid']) ? $data['DiagSetPhase_pid'] : NULL),
			'EvnPS_PhaseDescr_pid' => (!empty($data['EvnPS_PhaseDescr_pid']) ? $data['EvnPS_PhaseDescr_pid'] : NULL),
			'Diag_did' => (!empty($data['Diag_did']) ? $data['Diag_did'] : NULL),
			'DiagSetPhase_did' => (!empty($data['DiagSetPhase_did']) ? $data['DiagSetPhase_did'] : NULL),
			'EvnPS_PhaseDescr_did' => (!empty($data['EvnPS_PhaseDescr_did']) ? $data['EvnPS_PhaseDescr_did'] : NULL),
			'EvnQueue_id' => (!empty($data['EvnQueue_id']) ? $data['EvnQueue_id'] : NULL),
			'EvnDirection_id' => (!empty($data['EvnDirection_id']) ? $data['EvnDirection_id'] : NULL),
			'PrehospArrive_id' => (!empty($data['PrehospArrive_id']) ? $data['PrehospArrive_id'] : NULL),
			'PrehospDirect_id' => (!empty($data['PrehospDirect_id']) ? $data['PrehospDirect_id'] : NULL),
			'PrehospToxic_id' => (!empty($data['PrehospToxic_id']) ? $data['PrehospToxic_id'] : NULL),
			'PayType_id' => (!empty($data['PayType_id']) ? $data['PayType_id'] : NULL),
			'PrehospTrauma_id' => (!empty($data['PrehospTrauma_id']) ? $data['PrehospTrauma_id'] : NULL),
			'PrehospType_id' => (!empty($data['PrehospType_id']) ? $data['PrehospType_id'] : NULL),
			'Lpu_did' => (!empty($data['Lpu_did']) ? $data['Lpu_did'] : NULL),
			'Org_did' => (!empty($data['Org_did']) ? $data['Org_did'] : NULL),
			'LpuSection_did' => (!empty($data['LpuSection_did']) ? $data['LpuSection_did'] : NULL),
			'OrgMilitary_did' => (!empty($data['OrgMilitary_did']) ? $data['OrgMilitary_did'] : NULL),
			'LpuSection_pid' => (!empty($data['LpuSection_pid']) ? $data['LpuSection_pid'] : NULL),
			'MedPersonal_pid' => (!empty($data['MedPersonal_pid']) ? $data['MedPersonal_pid'] : NULL),
			'EvnDirection_Num' => (!empty($data['EvnDirection_Num']) ? $data['EvnDirection_Num'] : NULL),
			'EvnDirection_setDT' => (!empty($data['EvnDirection_setDate']) ? $data['EvnDirection_setDate'] : NULL),
			'EvnPS_CodeConv' => (!empty($data['EvnPS_CodeConv']) ? $data['EvnPS_CodeConv'] : NULL),
			'EvnPS_NumConv' => (!empty($data['EvnPS_NumConv']) ? $data['EvnPS_NumConv'] : NULL),
			'EvnPS_TimeDesease' => (!empty($data['EvnPS_TimeDesease']) ? $data['EvnPS_TimeDesease'] : NULL),
			'Okei_id' => (!empty($data['Okei_id']) ? $data['Okei_id'] : NULL),
			'EvnPS_HospCount' => (!empty($data['EvnPS_HospCount']) ? $data['EvnPS_HospCount'] : NULL),
			'EvnPS_IsUnlaw' => (!empty($data['EvnPS_IsUnlaw']) ? $data['EvnPS_IsUnlaw'] : NULL),
			'EvnPS_IsUnport' => (!empty($data['EvnPS_IsUnport']) ? $data['EvnPS_IsUnport'] : NULL),
			'EvnPS_IsImperHosp' => (!empty($data['EvnPS_IsImperHosp']) ? $data['EvnPS_IsImperHosp'] : NULL),
			'EvnPS_IsNeglectedCase' => (!empty($data['EvnPS_IsNeglectedCase']) ? $data['EvnPS_IsNeglectedCase'] : NULL),
			'EvnPS_IsShortVolume' => (!empty($data['EvnPS_IsShortVolume']) ? $data['EvnPS_IsShortVolume'] : NULL),
			'EvnPS_IsWrongCure' => (!empty($data['EvnPS_IsWrongCure']) ? $data['EvnPS_IsWrongCure'] : NULL),
			'EvnPS_IsDiagMismatch' => (!empty($data['EvnPS_IsDiagMismatch']) ? $data['EvnPS_IsDiagMismatch'] : NULL),
			'EvnPS_IsWithoutDirection' => (!empty($data['EvnPS_IsWithoutDirection']) ? $data['EvnPS_IsWithoutDirection'] : NULL),
			'EvnPS_IsWaif' => (!empty($data['EvnPS_IsWaif']) ? $data['EvnPS_IsWaif'] : NULL),
			'EvnPS_IsPLAmbulance' => (!empty($data['EvnPS_IsPLAmbulance']) ? $data['EvnPS_IsPLAmbulance'] : NULL),
			'PrehospWaifArrive_id' => (!empty($data['PrehospWaifArrive_id']) ? $data['PrehospWaifArrive_id'] : NULL),
			'PrehospWaifReason_id' => (!empty($data['PrehospWaifReason_id']) ? $data['PrehospWaifReason_id'] : NULL),
			'PrehospWaifRefuseCause_id' => (!empty($data['PrehospWaifRefuseCause_id']) ? $data['PrehospWaifRefuseCause_id'] : NULL),
			'EvnPS_IsTransfCall' => (!empty($data['EvnPS_IsTransfCall']) ? $data['EvnPS_IsTransfCall'] : NULL),
			'EvnPS_IsPrehospAcceptRefuse' => (!empty($data['EvnPS_IsPrehospAcceptRefuse']) ? $data['EvnPS_IsPrehospAcceptRefuse'] : NULL),
			'EvnPS_PrehospAcceptRefuseDT' => (!empty($data['EvnPS_PrehospAcceptRefuseDT']) ? $data['EvnPS_PrehospAcceptRefuseDT'] : NULL),
			'EvnPS_PrehospWaifRefuseDT' => (!empty($data['EvnPS_PrehospWaifRefuseDT']) ? $data['EvnPS_PrehospWaifRefuseDT'] : NULL),
			'LpuSection_eid' => (!empty($data['LpuSection_eid']) ? $data['LpuSection_eid'] : NULL),
			'PrehospStatus_id' => (!empty($data['PrehospStatus_id']) ? $data['PrehospStatus_id'] : NULL),
			'pmUser_id' => $data['pmUser_id']
            // samara
            ,
            'MedPersonal_did' => (!empty($data['MedPersonal_did']) ? $data['MedPersonal_did'] : NULL),
			'EntranceModeType_id' => (!empty($data['EntranceModeType_id']) ? $data['EntranceModeType_id'] : NULL),
			'EvnPS_DrugActions' => (!empty($data['EvnPS_DrugActions']) ? $data['EvnPS_DrugActions'] : NULL),
			'DeputyKind_id' => (!empty($data['DeputyKind_id']) ? $data['DeputyKind_id'] : NULL),
			'HospType_id' => (!empty($data['HospType_id']) ? $data['HospType_id'] : NULL),
			//'TimeDeseaseType_id' => (!empty($data['TimeDeseaseType_id']) ? $data['TimeDeseaseType_id'] : NULL),
			'EvnPS_DeputyFIO' => (!empty($data['EvnPS_DeputyFIO']) ? $data['EvnPS_DeputyFIO'] : NULL),
			'EvnPS_DeputyContact' => (!empty($data['EvnPS_DeputyContact']) ? $data['EvnPS_DeputyContact'] : NULL)
            //
		);
        
        

		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array(array('Error_Msg' => 'Ошибка при сохранении карты выбывшего из стационара'));
		}

		if ( isset($response[0]['Error_Msg']) && strlen($response[0]['Error_Msg']) > 0 ) {
			log_message('error', $response[0]['Error_Msg'].PHP_EOL.getDebugSQL($query, $queryParams));
			return $response;
		}

		if ( !isset($response[0]['EvnPS_id']) || $response[0]['EvnPS_id'] <= 0 ) {
			return array(array('Error_Msg' => 'Ошибка при сохранении карты выбывшего из стационара'));
		}

		$data['EvnPS_id'] = $response[0]['EvnPS_id'];

		return array(
			array(
				'EvnPS_id' => $data['EvnPS_id'],
				'Error_Msg' => ''
			)
		);
	}
   
    /**
	 * Получить общую сумму дней провердённых в отделениях
	 */
	function getEvnSectionsDays($data){

		$params = array();
    	$params['EvnSection_pid'] = $data['EvnPS_id'];
		$params['Lpu_id'] = $data['Lpu_id'];		
		
		$query = "
			select
				SUM(DATEDIFF('DAY', EvnSection_setDT, EvnSection_disDT)) as \"SectionsDays\"
			from v_EvnSection ES
			where ES.EvnSection_pid = :EvnSection_pid
				and ES.Lpu_id = :Lpu_id
		";
		
		$result = $this->db->query($query,$params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	
	/**
	*
	* Получает список записей для АРМа приемного:
	* - электронные направления не из очереди, с записью на койку или без, в т.ч. экстренные
	* - записи на койку (стац.бирки) в т.ч. экстренные бирки по СММП и бирки без эл.направлений
	* - КВС самостоятельно обратившихся (принятых не по направлению, не по бирке) и принятых из очереди
	* - записи из очереди с эл. направлением или без
	* Группы Не поступал, Находится в приемном, Госпитализирован, Отказ формировать на установленную дату
	* Не поступал - "план госпитализаций", пациенты ожидающие госпитализации на установленную дату
	* Находится в приемном - история болезни создана установленным днем, но исход из приемного другим днем.
	* Госпитализирован - исход из приемного - Госпитализирован на установленную дату
	* Отказ - исход из приемного - Отказ на установленную дату.
	* Очередь и план госпитализаций показывать на установленную дату
	*/
	function loadWorkPlacePriem($data)
	{
		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['date'] = $data['date'];// установленная дата
		//$params['begDT'] = date('Y-m-d H:m',(time()-86400)).':00.000';// сутки от текущего времени
		//$params['begDT2'] = date('Y-m-d H:m',(time()-172800)).':00.000';// 2 суток от текущего времени
		$params['LpuSection_id'] = $data['LpuSection_id']; // приемное отделение
		// фильтр на направления с бирками со статусом не поступал
		$filter_dir = '';
		// фильтр на направления из очереди со статусом не поступал
		$filter_eq = '';
		// фильтр на не экстренные бирки без эл.направлений со статусом не поступал
		$filter_tts = '';
		$filter = '';

		if (!empty($data['Person_SurName'])) 
		{
			$filter .= " AND PS.Person_Surname ILIKE :Person_SurName";
			$params['Person_SurName'] = rtrim($data['Person_SurName']).'%';
		}
		if (!empty($data['Person_FirName'])) 
		{
			$filter .= " AND PS.Person_Firname ILIKE :Person_FirName";
			$params['Person_FirName'] = rtrim($data['Person_FirName']).'%';
		}
		if (!empty($data['Person_SecName'])) 
		{
			$filter .= " AND PS.Person_Secname ILIKE :Person_SecName";
			$params['Person_SecName'] = rtrim($data['Person_SecName']).'%';
		}
		if (!empty($data['Person_BirthDay'])) 
		{
			$filter .= " AND cast(PS.Person_BirthDay as date) = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		
		if (!empty($data['PrehospStatus_id'])) 
		{
			$filter .= " AND EST.PrehospStatus_id = :PrehospStatus_id";
			$params['PrehospStatus_id'] = $data['PrehospStatus_id'];
		}
		
		if (empty($data['EvnDirectionShow_id'])) 
		{
			// На установленную дату
			$filter_dir .= " AND cast(coalesce(TTS.TimetableStac_setDate,ED.EvnDirection_setDT) as date) = :date";
			$filter_tts .= " AND cast(TTS.TimetableStac_setDate as date) = :date";
		}
		// иначе Все направления (отображать направления с бирками без признака отмены и без связки с КВС, но не зависимо от даты бирки)
		
		if (empty($data['EvnQueueShow_id'])) 
		{
			// не показывать очередь
			$filter_eq .= "(1=2) AND ";
		}
		else if($data['EvnQueueShow_id'] == 1)
		{
			// показать очередь, кроме записй из архива
			$filter_eq .= "coalesce(EQ.EvnQueue_IsArchived,1) = 1 AND";
		}
		// иначе отобразить все 
        if (!empty($data['date'])) {
		    $sql = "
			select
				'EvnDirection_'|| ED.EvnDirection_id as \"keyNote\",
				to_char(coalesce (EvnPS.EvnPS_setDate, TTS.TimetableStac_setDate, ED.EvnDirection_setDT), 'yyyyddmm') as \"sortDate\",
				EST.PrehospStatus_id as \"groupField\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				ED.Diag_id as \"Diag_did\",
				ED.LpuSection_id as \"LpuSection_did\",
				ED.Lpu_id as \"Lpu_did\",
				ED.DirType_id as \"DirType_id\",
				coalesce(DT.DirType_Name,'')
					||', '|| coalesce(LSP.LpuSectionProfile_Name,'')
					||', '|| coalesce(DiagD.Diag_Code,'')
					||'.'|| coalesce(DiagD.Diag_Name,''
				) as \"Direction_exists\",
				to_char(TTS.TimetableStac_setDate, 'dd.mm.yyyy') as \"TimetableStac_setDate\",
				null as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"TimetableStac_insDT\",
				null as \"EvnQueue_setDate\",
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as \"IsHospitalized\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy hh24:mi') as \"EvnPS_setDT\",--Дата/время поступления;  если есть КВС
				ED.TimetableStac_id as \"TimetableStac_id\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				ED.LpuSectionProfile_id as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				EST.PrehospStatus_Name as \"PrehospStatus_Name\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",
				puc.PMUser_Name as \"pmUser_Name\", --pmUser_Name Оператор. (направления/бирки, как в АРМ поликлиники)
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as \"IsRefusal\",-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				coalesce(EvnPS.EvnPS_IsTransfCall,1) as \"IsCall\", -- Передан активный вызов 
				coalesce(EvnPS.EvnPS_IsPLAmbulance,1) as \"IsSmmp\", -- Талон передан на ССМП
				EvnPS.PrehospArrive_id as \"PrehospArrive_id\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as \"Person_age\",
				PS.Person_Fio as \"Person_Fio\",
				ED.Person_id as \"Person_id\",
				ED.PersonEvn_id as \"PersonEvn_id\",
				ED.Server_id as \"Server_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				PP.PrivilegeType_Name as \"PrivilegeType_Name\",
				SS.SocStatus_Name as \"SocStatus_Name\"
			from 
				v_EvnDirection_all ED
				left join lateral (select * from v_Person_all PS where ED.Person_id = PS.Person_id and ED.PersonEvn_id = PS.PersonEvn_id and ED.Server_id = PS.Server_id limit 1) as PS on true
				left join v_EvnPS EvnPS on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_Diag DiagD on ED.Diag_id= DiagD.Diag_id
				left join v_TimetableStac_lite TTS on ED.EvnDirection_id = TTS.EvnDirection_id
				left join v_LpuSection LS on coalesce(EvnPS.LpuSection_eid,TTS.LpuSection_id) = LS.LpuSection_id
				left join v_Diag Diag on coalesce(EvnPS.Diag_pid,ED.Diag_id) = Diag.Diag_id
				left join v_PrehospStatus EST on coalesce(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
				left join pmUserCache puc on coalesce (TTS.pmUser_updId, ED.pmUser_insID) = puc.pmUser_id
				left join v_PersonPrivilege PP  on PP.Person_id = PS.Person_id
				left join SocStatus SS on PS.SocStatus_id = SS.SocStatus_id
			where
				ED.Lpu_did = :Lpu_id
				AND ED.EvnQueue_id is null
				AND ( ED.DirType_id is not null AND ED.DirType_id in (1,5,6) )
				AND ED.DirFailType_id is null --если не отменено
				AND (
					--со статусом не поступал на установленную дату 
					(EST.PrehospStatus_id = 1 {$filter_dir})
					--со статусом Находится в приемном - история болезни создана установленным днем
					OR (EST.PrehospStatus_id = 3 AND cast(EvnPS.EvnPS_setDT as date) = :date AND EvnPS.LpuSection_pid = :LpuSection_id)
					--со статусом Госпитализирован или Отказ на установленную дату 
					OR (EST.PrehospStatus_id = 4 AND cast(EvnPS.EvnPS_OutcomeDT as date) = :date AND EvnPS.LpuSection_pid = :LpuSection_id)
					OR (EST.PrehospStatus_id = 5 AND cast(EvnPS.EvnPS_OutcomeDT as date) = :date AND EvnPS.LpuSection_pid = :LpuSection_id)
				)
				{$filter}
			union all -- ####################################
			select
				'EvnPS_'|| EvnPS.EvnPS_id as \"keyNote\",
				to_char(EvnPS.EvnPS_setDate, 'yyyyddmm') as \"sortDate\",
				EST.PrehospStatus_id as \"groupField\",
				null as \"EvnDirection_id\",
				EvnPS.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EvnPS.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				EvnPS.Diag_did as \"Diag_did\",
				EvnPS.LpuSection_did as \"LpuSection_did\",
				EvnPS.Org_did as \"Lpu_did\",
				null as \"DirType_id\",
				null as \"Direction_exists\",
				null as \"TimetableStac_setDate\",
				null as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"TimetableStac_insDT\",
				null as \"EvnQueue_setDate\",
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as \"IsHospitalized\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy hh24:mi') as \"EvnPS_setDT\",--Дата/время поступления;  если есть КВС
				null as \"TimetableStac_id\",
				'' as \"LpuSectionProfile_Name\",
				null as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				EST.PrehospStatus_Name as \"PrehospStatus_Name\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",--Диагноз приемного; код, наименование
				'' as \"pmUser_Name\",
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as \"IsRefusal\",-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				coalesce(EvnPS.EvnPS_IsTransfCall,1) as \"IsCall\", -- Передан активный вызов 
				coalesce(EvnPS.EvnPS_IsPLAmbulance,1) as \"IsSmmp\", -- Талон передан на ССМП
				EvnPS.PrehospArrive_id as \"PrehospArrive_id\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as \"Person_age\",
				PS.Person_Fio as \"Person_Fio\",
				EvnPS.Person_id as \"Person_id\",
				EvnPS.PersonEvn_id as \"PersonEvn_id\",
				EvnPS.Server_id as \"Server_id\",
				LS.LpuSection_Name as \"LpuSection_FullName\",
				PP.PrivilegeType_Name as \"PrivilegeType_Name\",
				SS.SocStatus_Name as \"SocStatus_Name\"
			from v_EvnPS EvnPS
				left join lateral (select * from v_Person_all PS where EvnPS.Person_id = PS.Person_id and EvnPS.PersonEvn_id = PS.PersonEvn_id and EvnPS.Server_id = PS.Server_id limit 1) as PS on true
				left join v_LpuSection LS on EvnPS.LpuSection_eid = LS.LpuSection_id
				left join v_Diag Diag on EvnPS.Diag_pid = Diag.Diag_id
				left join v_PrehospStatus EST on EvnPS.PrehospStatus_id = EST.PrehospStatus_id
				left join v_TimetableStac_lite TTS on EvnPS.EvnPS_id = TTS.Evn_id
				left join v_PersonPrivilege PP on PP.Person_id = PS.Person_id
				left join v_PersonState vPS on vPS.Person_id = EvnPS.Person_id
				left join SocStatus SS on vPS.SocStatus_id = SS.SocStatus_id
			where 
				EvnPS.Lpu_id = :Lpu_id
				AND EvnPS.LpuSection_pid = :LpuSection_id
				AND EvnPS.EvnDirection_id is null
				AND TTS.TimetableStac_id is null
				AND (
					--со статусом Находится в приемном - история болезни создана установленным днем
					(EST.PrehospStatus_id = 3 AND cast(EvnPS.EvnPS_setDT as date) = :date) 
					--со статусом Госпитализирован или Отказ на установленную дату
					OR (EST.PrehospStatus_id = 4 AND cast(EvnPS.EvnPS_OutcomeDT as date) = :date)
					OR (EST.PrehospStatus_id = 5 AND cast(EvnPS.EvnPS_OutcomeDT as date) = :date)
				)
				{$filter}
			union all -- ####################################
			select
				'TimetableStac_'|| TTS.TimetableStac_id as \"keyNote\",
				to_char(coalesce(EvnPS.EvnPS_setDT,TTS.TimetableStac_setDate), 'yyyyddmm') as \"sortDate\",
				EST.PrehospStatus_id as \"groupField\",
				null as \"EvnDirection_id\",
				EvnPS.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EvnPS.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				coalesce(EvnPS.Diag_did,CmpDiag.Diag_id) as \"Diag_did\",
				EvnPS.LpuSection_did as \"LpuSection_did\",
				EvnPS.Org_did as \"Lpu_did\",
				null as \"DirType_id\",
				null as \"Direction_exists\",
				to_char(TTS.TimetableStac_setDate, 'dd.mm.yyyy') as \"TimetableStac_setDate\",
				case when TTS.TimetableType_id = 6 then 
					coalesce(TTSLS.LpuSectionProfile_Name,'') 
					||', '|| coalesce(CmpDiag.Diag_Code,'') ||'.'|| coalesce(CmpDiag.Diag_Name,'') 
					||', Бригада №'|| coalesce(EmD.EmergencyData_BrigadeNum,'')
				else 
					null
				end as \"SMMP_exists\",
				coalesce(EmD.EmergencyData_CallNum,'') as \"EvnPS_CodeConv\",
				to_char(TTS.TimetableStac_updDT, 'dd.mm.yyyy hh24:mi') as \"TimetableStac_insDT\",
				null as \"EvnQueue_setDate\",
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as \"IsHospitalized\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy hh24:mi') as \"EvnPS_setDT\",
				TTS.TimetableStac_id as \"TimetableStac_id\",
				TTSLS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				TTSLS.LpuSectionProfile_id as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				EST.PrehospStatus_Name as \"PrehospStatus_Name\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",
				puc.PMUser_Name as \"pmUser_Name\",
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as \"IsRefusal\",
				EvnPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				coalesce(EvnPS.EvnPS_IsTransfCall,1) as \"IsCall\",
				coalesce(EvnPS.EvnPS_IsPLAmbulance,1) as \"IsSmmp\",
				EvnPS.PrehospArrive_id as \"PrehospArrive_id\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as \"Person_age\",
				coalesce(PS.Person_SurName,'Не идентифицирован')
					||' '|| coalesce(PS.Person_FirName,'')
					||' '|| coalesce(PS.Person_SecName,''
				) as \"Person_Fio\",
				PS.Person_id as \"Person_id\",
				coalesce(EvnPS.PersonEvn_id,PS.PersonEvn_id) as \"PersonEvn_id\",
				coalesce(EvnPS.Server_id,PS.Server_id) as \"Server_id\",
				null as \"LpuSection_Name\",
				PP.PrivilegeType_Name as \"PrivilegeType_Name\",
				SS.SocStatus_Name as \"SocStatus_Name\"
			from v_TimetableStac_lite TTS
				left join v_EvnPS EvnPS on TTS.Evn_id = EvnPS.EvnPS_id
				left join v_LpuSection LS on coalesce(EvnPS.LpuSection_eid,TTS.LpuSection_id) = LS.LpuSection_id
				left join v_LpuSection TTSLS on TTS.LpuSection_id = TTSLS.LpuSection_id --and TTSLS.LpuUnitType_id in (1,6,9)
				--left join v_EmergencyData EmD on TTS.EmergencyData_id = EmD.EmergencyData_id
				left join EmergencyData EmD on TTS.EmergencyData_id = EmD.EmergencyData_id
				left join v_PersonState PS on coalesce(EvnPS.Person_id,TTS.Person_id,EmD.Person_lid) = PS.Person_id
				left join lateral(
					select
						EmergencyDataStatus_id
					from
						v_EmergencyDataHistory
					where
						EmergencyData_id = EmD.EmergencyData_id
					order by
						EmergencyDataHistory_id desc
					limit 1
				) EDH on true
				left join v_Diag Diag on coalesce(EvnPS.Diag_pid,EmD.Diag_id) = Diag.Diag_id
				--left join v_CmpDiag CmpDiag on EmD.CmpDiag_id = CmpDiag.CmpDiag_id
				left join v_Diag CmpDiag on EmD.Diag_id = CmpDiag.Diag_id
				left join v_PrehospStatus EST on coalesce(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
				left join pmUserCache puc on TTS.pmUser_updId = puc.pmUser_id
				left join v_EvnDirection_all ED on TTS.EvnDirection_id = ED.EvnDirection_id
				left join v_PersonPrivilege PP on PP.Person_id = PS.Person_id
				left join SocStatus SS on PS.SocStatus_id = SS.SocStatus_id
			where
				TTSLS.Lpu_id = :Lpu_id
				AND ED.TimetableStac_id is null
				AND (
					--со статусом не поступал экстр. бирки только с EmergencyDataStatus Койка забронирована на установленную дату
					(EST.PrehospStatus_id = 1 AND TTS.TimetableType_id = 6 AND cast(TTS.TimetableStac_setDate as date) = :date AND EDH.EmergencyDataStatus_id = 1 )
					--со статусом не поступал в зависимости от фильтра: все или на установленную дату
					OR (EST.PrehospStatus_id = 1 AND TTS.TimetableType_id != 6 AND TTS.Person_id is not null {$filter_tts})
					--со статусом Находится в приемном - история болезни создана установленным днем
					OR (EST.PrehospStatus_id = 3 AND cast(EvnPS.EvnPS_setDT as date) = :date AND EvnPS.LpuSection_pid = :LpuSection_id)
					--со статусом Госпитализирован или Отказ на установленную дату
					OR (EST.PrehospStatus_id = 4 AND cast(EvnPS.EvnPS_OutcomeDT as date) = :date AND EvnPS.LpuSection_pid = :LpuSection_id)
					OR (EST.PrehospStatus_id = 5 AND cast(EvnPS.EvnPS_OutcomeDT as date) = :date AND EvnPS.LpuSection_pid = :LpuSection_id)
				)
				{$filter}
			union all -- ####################################
			select
				'EvnQueue_'|| EQ.EvnQueue_id as \"keyNote\",
				to_char(EQ.EvnQueue_setDT, 'yyyyddmm') as \"sortDate\",
				EST.PrehospStatus_id as \"groupField\",
				EQ.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				ED.Diag_id as \"Diag_did\",
				ED.LpuSection_id as \"LpuSection_did\",
				ED.Lpu_id as \"Lpu_did\",
				ED.DirType_id as \"DirType_id\",
				coalesce(DT.DirType_Name,'')
					||', '|| coalesce(LSP.LpuSectionProfile_Name,'')
					||', '|| coalesce(Diag.Diag_Code,''
					||'.'|| coalesce(Diag.Diag_Name,''
				) as \"Direction_exists\",
				null as \"TimetableStac_setDate\",
				null as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"TimetableStac_insDT\",
				to_char(EQ.EvnQueue_setDT, 'dd.mm.yyyy') as \"EvnQueue_setDate\",
				1 as \"IsHospitalized\",
				null as \"EvnPS_id\",
				'' as \"EvnPS_setDT\",
				EQ.TimetableStac_id as \"TimetableStac_id\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				EQ.LpuSectionProfile_did as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				EST.PrehospStatus_Name as \"PrehospStatus_Name\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",
				puc.PMUser_Name as \"pmUser_Name\",
				1 as \"IsRefusal\",
				null as \"PrehospWaifRefuseCause_id\",
				1 as \"IsCall\",
				1 as \"IsSmmp\",
				null as \"PrehospArrive_id\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as \"Person_age\",
				PS.Person_Fio as \"Person_Fio\",
				EQ.Person_id as \"Person_id\",
				EQ.PersonEvn_id as \"PersonEvn_id\",
				EQ.Server_id as \"Server_id\",
				null as \"LpuSection_Name\",
				PP.PrivilegeType_Name as \"PrivilegeType_Name\",
				SS.SocStatus_Name as \"SocStatus_Name\"
			from v_EvnQueue EQ
				left join v_LpuUnit LU on EQ.LpuUnit_did = LU.LpuUnit_id
				left join lateral (select * from v_Person_all PS where EQ.Person_id = PS.Person_id and EQ.PersonEvn_id = PS.PersonEvn_id and EQ.Server_id = PS.Server_id limit 1) PS on true
				left join v_EvnDirection_all ED on EQ.EvnDirection_id = ED.EvnDirection_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP on EQ.LpuSectionProfile_did = LSP.LpuSectionProfile_id
				left join v_Diag Diag on coalesce(EQ.Diag_id,ED.Diag_id) = Diag.Diag_id
				left join v_PrehospStatus EST on 1 = EST.PrehospStatus_id
				left join pmUserCache puc on EQ.pmUser_insID = puc.pmUser_id
				left join v_PersonPrivilege PP on PP.Person_id = PS.Person_id
				left join SocStatus SS on PS.SocStatus_id = SS.SocStatus_id
			where 
				{$filter_eq}
				EQ.EvnQueue_failDT is null
				AND EQ.EvnQueue_recDT is null
				AND LU.Lpu_id = :Lpu_id
				AND LU.LpuUnitType_id in (1,9,6)
				AND (EQ.EvnDirection_id is null OR ED.EvnQueue_id is not null)
				{$filter}
			order by 
				sortDate DESC
		";
        }
        else {
            $sql = "
			select
				'EvnDirection_'|| ED.EvnDirection_id as \"keyNote\",
				to_char(coalesce (EvnPS.EvnPS_setDate, TTS.TimetableStac_setDate, ED.EvnDirection_setDT), 'yyyyddmm') as \"sortDate\",
				EST.PrehospStatus_id as \"groupField\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				ED.Diag_id as \"Diag_did\",
				ED.LpuSection_id as \"LpuSection_did\",
				ED.Lpu_id as \"Lpu_did\",
				ED.DirType_id as \"DirType_id\",
				coalesce(DT.DirType_Name,'')
					||', '|| coalesce(LSP.LpuSectionProfile_Name,'')
					||', '|| coalesce(DiagD.Diag_Code,'')
					||'.'|| coalesce(DiagD.Diag_Name, ''
				) as \"Direction_exists\",
				to_char(TTS.TimetableStac_setDate, 'dd.mm.yyyy') as \"TimetableStac_setDate\",
				null as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"TimetableStac_insDT\",
				null as \"EvnQueue_setDate\",
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as \"IsHospitalized\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy hh24:mi') as \"EvnPS_setDT\",--Дата/время поступления;  если есть КВС
				ED.TimetableStac_id as \"TimetableStac_id\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				ED.LpuSectionProfile_id as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				EST.PrehospStatus_Name as \"PrehospStatus_Name\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",
				puc.PMUser_Name as \"pmUser_Name\", --pmUser_Name Оператор. (направления/бирки, как в АРМ поликлиники)
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as \"IsRefusal\",-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				coalesce(EvnPS.EvnPS_IsTransfCall,1) as \"IsCall\", -- Передан активный вызов
				coalesce(EvnPS.EvnPS_IsPLAmbulance,1) as \"IsSmmp\", -- Талон передан на ССМП
				EvnPS.PrehospArrive_id as \"PrehospArrive_id\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as \"Person_age\",
				PS.Person_Fio as \"Person_Fio\",
				ED.Person_id as \"Person_id\",
				ED.PersonEvn_id as \"PersonEvn_id\",
				ED.Server_id as \"Server_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				PP.PrivilegeType_Name as \"PrivilegeType_Name\",
				SS.SocStatus_Name as \"SocStatus_Name\"
			from
				v_EvnDirection_all ED
				left join lateral (select * from v_Person_all PS where ED.Person_id = PS.Person_id and ED.PersonEvn_id = PS.PersonEvn_id and ED.Server_id = PS.Server_id limit 1) as PS on true
				left join v_EvnPS EvnPS on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_Diag DiagD on ED.Diag_id= DiagD.Diag_id
				left join v_TimetableStac_lite TTS on ED.EvnDirection_id = TTS.EvnDirection_id
				left join v_LpuSection LS on coalesce(EvnPS.LpuSection_eid,TTS.LpuSection_id) = LS.LpuSection_id
				left join v_Diag Diag on coalesce(EvnPS.Diag_pid,ED.Diag_id) = Diag.Diag_id
				left join v_PrehospStatus EST on coalesce(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
				left join pmUserCache puc on coalesce (TTS.pmUser_updId, ED.pmUser_insID) = puc.pmUser_id
				left join v_PersonPrivilege PP on PP.Person_id = PS.Person_id
				left join SocStatus SS on PS.SocStatus_id = SS.SocStatus_id
			where
				ED.Lpu_did = :Lpu_id
				AND ED.EvnQueue_id is null
				AND ( ED.DirType_id is not null AND ED.DirType_id in (1,5,6) )
				AND ED.DirFailType_id is null --если не отменено
				AND (
					--со статусом не поступал на установленную дату
					(EST.PrehospStatus_id = 1)
					--со статусом Находится в приемном - история болезни создана установленным днем
					OR (EST.PrehospStatus_id = 3 AND EvnPS.LpuSection_pid = :LpuSection_id)
					--со статусом Госпитализирован или Отказ на установленную дату
					OR (EST.PrehospStatus_id = 4 AND EvnPS.LpuSection_pid = :LpuSection_id)
					OR (EST.PrehospStatus_id = 5 AND EvnPS.LpuSection_pid = :LpuSection_id)
				)
				{$filter}
			union all -- ####################################
			select
				'EvnPS_'|| EvnPS.EvnPS_id as \"keyNote\",
				to_char(EvnPS.EvnPS_setDate, 'yyyyddmm') as \"sortDate\",
				EST.PrehospStatus_id as \"groupField\",
				null as \"EvnDirection_id\",
				EvnPS.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EvnPS.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				EvnPS.Diag_did as \"Diag_did\",
				EvnPS.LpuSection_did as \"LpuSection_did\",
				EvnPS.Org_did as \"Lpu_did\",
				null as \"DirType_id\",
				null as \"Direction_exists\",
				null as \"TimetableStac_setDate\",
				null as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"TimetableStac_insDT\",
				null as \"EvnQueue_setDate\",
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as \"IsHospitalized\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy hh24:mi') as \"EvnPS_setDT\",--Дата/время поступления;  если есть КВС
				null as \"TimetableStac_id\",
				'' as \"LpuSectionProfile_Name\",
				null as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				EST.PrehospStatus_Name as \"PrehospStatus_Name\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",--Диагноз приемного; код, наименование
				'' as \"pmUser_Name\",
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as \"IsRefusal\",-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				coalesce(EvnPS.EvnPS_IsTransfCall,1) as \"IsCall\", -- Передан активный вызов
				coalesce(EvnPS.EvnPS_IsPLAmbulance,1) as \"IsSmmp\", -- Талон передан на ССМП
				EvnPS.PrehospArrive_id as \"PrehospArrive_id\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as \"Person_age\",
				PS.Person_Fio as \"Person_Fio\",
				EvnPS.Person_id as \"Person_id\",
				EvnPS.PersonEvn_id as \"PersonEvn_id\",
				EvnPS.Server_id as \"Server_id\",
				LS.LpuSection_Name as \"LpuSection_FullName\",
				PP.PrivilegeType_Name as \"PrivilegeType_Name\",
				SS.SocStatus_Name as \"SocStatus_Name\"
			from v_EvnPS EvnPS
				left join lateral (select * from v_Person_all PS where EvnPS.Person_id = PS.Person_id
					and EvnPS.PersonEvn_id = PS.PersonEvn_id
					and EvnPS.Server_id = PS.Server_id limit 1) as PS on true
				left join v_LpuSection LS on EvnPS.LpuSection_eid = LS.LpuSection_id
				left join v_Diag Diag on EvnPS.Diag_pid = Diag.Diag_id
				left join v_PrehospStatus EST on EvnPS.PrehospStatus_id = EST.PrehospStatus_id
				left join v_TimetableStac_lite TTS on EvnPS.EvnPS_id = TTS.Evn_id
				left join v_PersonPrivilege PP on PP.Person_id = PS.Person_id
				left join v_PersonState vPS on vPS.Person_id = EvnPS.Person_id
				left join SocStatus SS on vPS.SocStatus_id = SS.SocStatus_id
			where
				EvnPS.Lpu_id = :Lpu_id
				AND EvnPS.LpuSection_pid = :LpuSection_id
				AND EvnPS.EvnDirection_id is null
				AND TTS.TimetableStac_id is null
				AND (
					--со статусом Находится в приемном - история болезни создана установленным днем
					(EST.PrehospStatus_id = 3)
					--со статусом Госпитализирован или Отказ на установленную дату
					OR (EST.PrehospStatus_id = 4)
					OR (EST.PrehospStatus_id = 5)
				)
				{$filter}
			union all -- ####################################
			select
				'TimetableStac_'|| TTS.TimetableStac_id as \"keyNote\",
				to_char(coalesce(EvnPS.EvnPS_setDT,TTS.TimetableStac_setDate), 'yyyyddmm') as \"sortDate\",
				EST.PrehospStatus_id as \"groupField\",
				null as \"EvnDirection_id\",
				EvnPS.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(EvnPS.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				coalesce(EvnPS.Diag_did,CmpDiag.Diag_id) as \"Diag_did\",
				EvnPS.LpuSection_did as \"LpuSection_did\",
				EvnPS.Org_did as \"Lpu_did\",
				null as \"DirType_id\",
				null as \"Direction_exists\",
				to_char(TTS.TimetableStac_setDate, 'dd.mm.yyyy') as \"TimetableStac_setDate\",
				case when TTS.TimetableType_id = 6 then
					coalesce(TTSLS.LpuSectionProfile_Name,'')
					||', '|| coalesce(CmpDiag.Diag_Code,'') ||'.'|| coalesce(CmpDiag.Diag_Name,'')
					||', Бригада №'|| coalesce(EmD.EmergencyData_BrigadeNum,'')
				else
					null
				end as \"SMMP_exists\",
				coalesce(EmD.EmergencyData_CallNum,'') as \"EvnPS_CodeConv\",
				to_char(TTS.TimetableStac_updDT, 'dd.mm.yyyy hh24:mi') as \"TimetableStac_insDT\",
				null as \"EvnQueue_setDate\",
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as \"IsHospitalized\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy hh24:mi') as \"EvnPS_setDT\",
				TTS.TimetableStac_id as \"TimetableStac_id\",
				TTSLS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				TTSLS.LpuSectionProfile_id as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				EST.PrehospStatus_Name as \"PrehospStatus_Name\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",
				puc.PMUser_Name as \"pmUser_Name\",
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as \"IsRefusal\",
				EvnPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				coalesce(EvnPS.EvnPS_IsTransfCall,1) as \"IsCall\",
				coalesce(EvnPS.EvnPS_IsPLAmbulance,1) as \"IsSmmp\",
				EvnPS.PrehospArrive_id as \"PrehospArrive_id\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as \"Person_age\",
				coalesce(PS.Person_SurName,'Не идентифицирован') ||' '|| coalesce(PS.Person_FirName,'') ||' '|| coalesce(PS.Person_SecName,'') as \"Person_Fio\",
				PS.Person_id as \"Person_id\",
				coalesce(EvnPS.PersonEvn_id,PS.PersonEvn_id) as \"PersonEvn_id\",
				coalesce(EvnPS.Server_id,PS.Server_id) as \"Server_id\",
				null as \"LpuSection_Name\",
				PP.PrivilegeType_Name as \"PrivilegeType_Name\",
				SS.SocStatus_Name as \"SocStatus_Name\"
			from v_TimetableStac_lite TTS
				left join v_EvnPS EvnPS on TTS.Evn_id = EvnPS.EvnPS_id
				left join v_LpuSection LS on coalesce(EvnPS.LpuSection_eid,TTS.LpuSection_id) = LS.LpuSection_id
				left join v_LpuSection TTSLS on TTS.LpuSection_id = TTSLS.LpuSection_id --and TTSLS.LpuUnitType_id in (1,6,9)
				left join EmergencyData EmD on TTS.EmergencyData_id = EmD.EmergencyData_id
				left join v_PersonState PS on coalesce(EvnPS.Person_id,TTS.Person_id,EmD.Person_lid) = PS.Person_id
				left join lateral(
					select
						EmergencyDataStatus_id
					from
						v_EmergencyDataHistory
					where
						EmergencyData_id = EmD.EmergencyData_id
					order by
						EmergencyDataHistory_id desc
					limit 1
				) EDH on true
				left join v_Diag Diag on coalesce(EvnPS.Diag_pid,EmD.Diag_id) = Diag.Diag_id
				--left join v_CmpDiag CmpDiag on EmD.CmpDiag_id = CmpDiag.CmpDiag_id
				left join v_Diag CmpDiag on EmD.Diag_id = CmpDiag.Diag_id
				left join v_PrehospStatus EST on coalesce(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
				left join pmUserCache puc on TTS.pmUser_updId = puc.pmUser_id
				left join v_EvnDirection_all ED on TTS.EvnDirection_id = ED.EvnDirection_id
				left join v_PersonPrivilege PP on PP.Person_id = PS.Person_id
				left join SocStatus SS on PS.SocStatus_id = SS.SocStatus_id
			where
				TTSLS.Lpu_id = :Lpu_id
				AND ED.TimetableStac_id is null
				AND (
					--со статусом не поступал экстр. бирки только с EmergencyDataStatus Койка забронирована на установленную дату
					(EST.PrehospStatus_id = 1 AND TTS.TimetableType_id = 6 AND EDH.EmergencyDataStatus_id = 1 )
					--со статусом не поступал в зависимости от фильтра: все или на установленную дату
					OR (EST.PrehospStatus_id = 1 AND TTS.TimetableType_id != 6 AND TTS.Person_id is not null)
					--со статусом Находится в приемном - история болезни создана установленным днем
					OR (EST.PrehospStatus_id = 3 AND EvnPS.LpuSection_pid = :LpuSection_id)
					--со статусом Госпитализирован или Отказ на установленную дату
					OR (EST.PrehospStatus_id = 4 AND EvnPS.LpuSection_pid = :LpuSection_id)
					OR (EST.PrehospStatus_id = 5 AND EvnPS.LpuSection_pid = :LpuSection_id)
				)
				{$filter}
			union all -- ####################################
			select
				'EvnQueue_'|| EQ.EvnQueue_id as \"keyNote\",
				to_char(EQ.EvnQueue_setDT, 'yyyyddmm') as \"sortDate\",
				EST.PrehospStatus_id as \"groupField\",
				EQ.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				ED.Diag_id as \"Diag_did\",
				ED.LpuSection_id as \"LpuSection_did\",
				ED.Lpu_id as \"Lpu_did\",
				ED.DirType_id as \"DirType_id\",
				coalesce(DT.DirType_Name,'')
					||', '|| coalesce(LSP.LpuSectionProfile_Name,'')
					||', '|| coalesce(Diag.Diag_Code,'')
					||'.'|| coalesce(Diag.Diag_Name,''
				) as \"Direction_exists\",
				null as \"TimetableStac_setDate\",
				null as \"SMMP_exists\",
				null as \"EvnPS_CodeConv\",
				null as \"TimetableStac_insDT\",
				to_char(EQ.EvnQueue_setDT, 'dd.mm.yyyy') as \"EvnQueue_setDate\",
				1 as \"IsHospitalized\",
				null as \"EvnPS_id\",
				'' as \"EvnPS_setDT\",
				EQ.TimetableStac_id as \"TimetableStac_id\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				EQ.LpuSectionProfile_did as \"LpuSectionProfile_did\",
				EST.PrehospStatus_id as \"PrehospStatus_id\",
				EST.PrehospStatus_Name as \"PrehospStatus_Name\",
				RTRIM(Diag.Diag_Code) || '. ' || RTRIM(Diag.Diag_Name) as \"Diag_CodeName\",
				puc.PMUser_Name as \"pmUser_Name\",
				1 as \"IsRefusal\",
				null as \"PrehospWaifRefuseCause_id\",
				1 as \"IsCall\",
				1 as \"IsSmmp\",
				null as \"PrehospArrive_id\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as \"Person_age\",
				PS.Person_Fio as \"Person_Fio\",
				EQ.Person_id as \"Person_id\",
				EQ.PersonEvn_id as \"PersonEvn_id\",
				EQ.Server_id as \"Server_id\",
				null as \"LpuSection_Name\",
				PP.PrivilegeType_Name as \"PrivilegeType_Name\",
				SS.SocStatus_Name as \"SocStatus_Name\"
			from v_EvnQueue EQ
				left join v_LpuUnit LU on EQ.LpuUnit_did = LU.LpuUnit_id
				left join lateral (select * from v_Person_all PS where EQ.Person_id = PS.Person_id and EQ.PersonEvn_id = PS.PersonEvn_id and EQ.Server_id = PS.Server_id limit 1) PS on true
				left join v_EvnDirection_all ED on EQ.EvnDirection_id = ED.EvnDirection_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP on EQ.LpuSectionProfile_did = LSP.LpuSectionProfile_id
				left join v_Diag Diag on coalesce(EQ.Diag_id,ED.Diag_id) = Diag.Diag_id
				left join v_PrehospStatus EST on 1 = EST.PrehospStatus_id
				left join pmUserCache puc on EQ.pmUser_insID = puc.pmUser_id
				left join v_PersonPrivilege PP on PP.Person_id = PS.Person_id
				left join SocStatus SS on PS.SocStatus_id = SS.SocStatus_id
			where
				{$filter_eq}
				EQ.EvnQueue_failDT is null
				AND EQ.EvnQueue_recDT is null
				AND LU.Lpu_id = :Lpu_id
				AND LU.LpuUnitType_id in (1,9,6)
				AND (EQ.EvnDirection_id is null OR ED.EvnQueue_id is not null)
				{$filter}
			order by
				sortDate DESC
		";
        }
			/*
			echo getDebugSql($sql, $params);
			exit;
			*/
		
		$res = $this->db->query($sql,$params);
		
		if ( is_object($res) ) {
			$res_array = $res->result('array');
			return $res_array;
		}
		else
			return false;
	}

	/**
	 * Получение списка назначений для журнала назначений
	 */
}
