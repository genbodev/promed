<?php

require_once(APPPATH.'models/EvnPS_model.php');

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
        
        
        $query = '
            SELECT
                cps.Person_id
                ,convert(varchar, ps.Person_BirthDay,104) AS child_Person_Bday
                ,CAST(isnull(PH.PersonHeight_Height, 0) AS int) AS PersonHeight_Height
                ,(cast(isnull(PW.PersonWeight_Weight, 0) as float) / 1000) AS PersonWeight_Weight
                ,( SELECT   TOP 1  sex_name
				    FROM      dbo.v_Sex s WITH (NOLOCK)
				    WHERE     s.sex_id = ps.Sex_id
				) AS Sex_name
            FROM 
                (
                    SELECT
                        vel.Evn_id
                        ,vel.Evn_lid
                    FROM dbo.v_EvnLink AS vel WITH (NOLOCK)
                    WHERE Evn_id = :EvnSection_pid --114537
                ) AS l
            LEFT JOIN dbo.v_EvnPS cps WITH (NOLOCK) ON cps.EvnPS_id = l.Evn_lid
            LEFT JOIN dbo.v_EvnPS mps WITH (NOLOCK) ON mps.EvnPS_id = l.Evn_id
            LEFT JOIN dbo.v_PersonState ps WITH (NOLOCK) ON ps.Person_id = cps.Person_id    --mps.Person_id
            LEFT JOIN v_PersonHeight PH ON cps.Person_id = PH.Person_id
            LEFT JOIN v_PersonWeight PW ON cps.Person_id = PW.Person_id     
        ';

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
   
        
        $query = '
            SELECT  ChildDeath_id ,
                MedStaffFact_id ,
                (select Person_Fio from v_MedStaffFact where MedStaffFact_id = cd.MedStaffFact_id) AS MedStaffFact_Name ,
                Diag_id ,
                (SELECT diag_name FROM dbo.Diag AS d WITH (NOLOCK) WHERE cd.diag_id = d.diag_id) AS Diag_Name,
                Sex_id ,
                (SELECT Sex_Name FROM sex AS s WITH (NOLOCK) WHERE s.sex_id = cd.sex_id) AS Sex_Name,
                ChildDeath_Weight ,
                ChildDeath_Height ,
                PntDeathTime_id ,
                (SELECT PntDeathTime_Name FROM dbo.PntDeathTime dt WHERE dt.PntDeathTime_id  = cd.PntDeathTime_id) as PntDeathTime_Name,
                ChildTermType_id ,
                (SELECT ChildTermType_Name FROM ChildTermType tt WHERE tt.ChildTermType_id = cd.ChildTermType_id) as ChildTermType_Name,
                ChildDeath_Count ,
                BirthSvid_id ,
                (SELECT BirthSvid_Num FROM BirthSvid AS bs WITH (NOLOCK) WHERE bs.BirthSvid_id = cd.BirthSvid_id) AS BirthSvid_Num,
                PntDeathSvid_id ,
                (SELECT PntDeathSvid_Num FROM PntDeathSvid AS pds WITH (NOLOCK) WHERE pds.PntDeathSvid_id = cd.PntDeathSvid_id) AS PntDeathSvid_Num,
                pmUser_insID ,
                pmUser_updID ,
                ISNULL(convert(varchar(10), ChildDeath_insDT, 104), 0) as ChildDeath_insDT,
                ChildDeath_updDT,
                Okei_wid,
                CAST(ChildDeath_Weight as VARCHAR) + \' \'+ ISNULL((SELECT Okei_NationSymbol FROM v_Okei o WHERE Okei_id = cd.Okei_wid), \'\') as ChildDeath_Weight_text,
                1 AS RecordStatus_Code
            FROM    dbo.v_ChildDeath AS cd WITH (NOLOCK)
            WHERE   BirthSpecStac_id IN ( 
                SELECT TOP 1
                    BirthSpecStac_id
                FROM birthSpecStac WITH ( NOLOCK )
                WHERE EvnSection_id IN (
                    SELECT TOP 1 
                        ES.EvnSection_id 
                    FROM v_EvnSection ES
                    inner join LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
                    WHERE ES.EvnSection_pid = :EvnSection_pid ---114586 
                        AND ES.Lpu_id = :Lpu_id --6011
                ) 
                ORDER BY  BirthSpecStac_id DESC 
            )
            ';

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
        
        
        $query = '
            select
				EDPS.EvnDiagPS_id,
				EDPS.EvnDiagPS_pid,
				EDPS.Person_id,
				EDPS.PersonEvn_id,
				EDPS.Server_id,
				Diag.Diag_id,
				EDPS.DiagSetPhase_id,
				RTRIM(EDPS.EvnDiagPS_PhaseDescr) as EvnDiagPS_PhaseDescr,
				DSC.DiagSetClass_id,
				EDPS.DiagSetType_id,
				convert(varchar(10), EDPS.EvnDiagPS_setDate, 104) as EvnDiagPS_setDate,
				EDPS.EvnDiagPS_setTime,
				RTRIM(DSC.DiagSetClass_Name) as DiagSetClass_Name,
				RTRIM(Diag.Diag_Code) as Diag_Code,
				RTRIM(Diag.Diag_Name) as Diag_Name,
				1 as RecordStatus_Code
			from v_EvnDiagPS EDPS with (nolock)
				left join Diag with (nolock) on Diag.Diag_id = EDPS.Diag_id
				left join DiagSetClass DSC with (nolock) on DSC.DiagSetClass_id = EDPS.DiagSetClass_id
			where 
			 EDPS.EvnDiagPS_pid = :EvnSection_pid 
             AND EDPS.DiagSetType_id = 2    
        ';

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
                EU.EvnUsluga_id 
                ,EA.EvnAgg_pid
                ,EA.EvnAgg_id
                ,EA.AggType_id
                ,AT.AggType_Code
                ,RTRIM(ISNULL(AT.AggType_Name, '')) AS AggType_Name
                ,RTRIM(ISNULL(AW.AggWhen_Name, '')) AS AggWhen_Name
                ,MOT.MesOperType_id
                ,MOT.MesOperType_Name as MesOperType_Name
            FROM v_EvnSection ES WITH (NOLOCK)
                INNER JOIN LpuSection LS WITH (NOLOCK) ON ES.LpuSection_id = LS.LpuSection_id
                LEFT JOIN v_EvnUsluga EU WITH (NOLOCK) ON ES.EvnSection_id = EU.EvnUsluga_pid     
                INNER JOIN v_EvnAgg EA WITH (NOLOCK) ON EU.EvnUsluga_id = EA.EvnAgg_pid          
                LEFT JOIN AggType AT ON AT.AggType_id = EA.AggType_id
                LEFT JOIN AggWhen AW ON AW.AggWhen_id = EA.AggWhen_id
                left join MesOperType MOT with (nolock) on MOT.MesOperType_id = EU.MesOperType_id  
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
                EU.Usluga_id
                ,EU.UslugaComplex_id
                ,EU.EvnClass_id
                ,RTRIM(ISNULL(UC.UslugaComplex_Code, '')) as UslugaComplex_Code
                ,RTRIM(ISNULL(UC.UslugaComplex_Name, '')) as UslugaComplex_Name
                ,convert(varchar, EU.EvnUsluga_setDate, 104) EvnUsluga_Date
            FROM v_EvnSection ES WITH (NOLOCK)
                INNER JOIN LpuSection LS WITH (NOLOCK) ON ES.LpuSection_id = LS.LpuSection_id
                LEFT JOIN v_EvnUsluga EU WITH (NOLOCK) ON ES.EvnSection_id = EU.EvnUsluga_pid    
                LEFT JOIN UslugaComplex UC WITH (NOLOCK) ON EU.UslugaComplex_id = UC.UslugaComplex_id
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
                ES.LpuSection_id 
                ,ES.EvnSection_id ,EOST.EvnOtherStac_id ,EOST.EvnOtherStac_pid ,EOST.EvnOtherStac_rid
                ,EL.EvnLeave_id
                ,ES.EvnSection_pid
                ,ES.EvnSection_rid
                ,ES.Lpu_id
                ,LS.LpuUnit_id
                ,LU.LpuUnitType_id
                ,LUT.LpuUnitType_Code
                ,LUT.LpuUnitType_Name
                ,LUT.LpuUnitType_Nick
                ,LUT.LpuUnitType_SysNick
                ,COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOSBP.ResultDesease_id, EOST.ResultDesease_id) as ResultDesease_id
                ,EL.EvnLeave_id
                ,EPS.EvnPS_rid
                ,EPS.LeaveType_id
                ,RD.ResultDesease_Code
                ,RD.ResultDesease_Name
                ,RD.ResultDesease_SysNick
            FROM v_EvnSection ES WITH (NOLOCK) 
            INNER JOIN LpuSection LS WITH (NOLOCK) ON ES.LpuSection_id = LS.LpuSection_id
            INNER JOIN LpuUnit LU WITH (NOLOCK) ON LS.LpuUnit_id = LU.LpuUnit_id
            INNER JOIN v_LpuUnitType LUT WITH (NOLOCK) ON LU.LpuUnitType_id = LUT.LpuUnitType_id
            LEFT JOIN v_EvnLeave EL WITH (NOLOCK) ON ES.EvnSection_rid = EL.EvnLeave_rid
            LEFT JOIN v_EvnOtherLpu EOL WITH (NOLOCK) ON ES.EvnSection_id = EOL.EvnOtherLpu_pid
            left join v_EvnOtherSectionBedProfile EOSBP with (nolock) on EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
            left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ES.EvnSection_id
            left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = ES.EvnSection_id
            LEFT JOIN ResultDesease RD WITH (NOLOCK) 
                ON COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOS.ResultDesease_id, EOSBP.ResultDesease_id, EOST.ResultDesease_id) = RD.ResultDesease_id
            LEFT JOIN v_EvnPS EPS WITH (NOLOCK) ON EPS.EvnPS_rid = ES.EvnSection_rid
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
                CAST(isnull(PH.PersonHeight_Height, 0) AS int) AS PersonHeight_Height
                ,PH.HeightMeasureType_id
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
                (cast(isnull(PW.PersonWeight_Weight, 0) as float) / 1000) AS PersonWeight_Weight
                ,PW.WeightMeasureType_id
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
                PS.PersonSurName_SurName
                ,PS.PersonFirName_FirName
                ,PS.PersonSecName_SecName
                ,RTRIM(RTRIM(ISNULL(PS.PersonSurName_SurName, '')) + ' ' + RTRIM(ISNULL(PS.PersonFirName_FirName, '')) + ' ' + RTRIM(ISNULL(PS.PersonSecName_SecName, ''))) as Mother_Fio
                ,EPS.EvnPS_rid as Mother_EvnPS_rid
                ,UA.Address_Address as Mother_UAddress
                ,PA.Address_Address as Mother_PAddress
                ,EPS.Person_Age as Mother_Age
                ,PS.FamilyStatus_id as Mother_FamilyStatus_id
                ,PS.PersonFamilyStatus_IsMarried as Mother_IsMarried
                ,LSW.LpuSectionWard_Name as Mother_Ward_Name
                              
                ,isnull(bss.BirthSpecStac_CountPregnancy, 0) as Mother_CountPregnancy
                ,isnull(bss.BirthSpecStac_CountBirth, 0) as Mother_CountBirth
                ,bss.BirthSpecStac_CountChild as Mother_CountChild
                ,isnull(bss.BirthSpecStac_CountChildAlive, 0) as Mother_CountChildAlive
                ,isnull((bss.BirthSpecStac_CountChild - bss.BirthSpecStac_CountChildAlive), '') as Mother_DeathChildCount
                
                ,P.Post_Name as Mother_Post_Name
                
            FROM v_EvnLink EL WITH (NOLOCK) 
            INNER JOIN v_EvnPS EPS WITH (NOLOCK) ON EL.Evn_id = EPS.EvnPS_rid
            LEFT JOIN PersonState PS WITH (NOLOCK) ON EPS.Person_id = PS.Person_id
            LEFT JOIN Address UA WITH (NOLOCK) ON PS.UAddress_id = UA.Address_id
            LEFT JOIN Address PA WITH (NOLOCK) ON PS.PAddress_id = PA.Address_id
            
            LEFT JOIN v_EvnSection ES WITH (NOLOCK) ON ES.EvnSection_rid = EL.Evn_id
            LEFT JOIN LpuSectionWard LSW WITH (NOLOCK) ON ES.LpuSectionWard_id = LSW.LpuSectionWard_id
            
            LEFT JOIN dbo.v_BirthSpecStac bss WITH ( NOLOCK ) ON bss.EvnSection_id = ES.EvnSection_id
            
            LEFT JOIN Job J WITH (NOLOCK) ON PS.Job_id = J.Job_id
            LEFT JOIN Post P WITH (NOLOCK) ON J.Post_id = P.Post_id
            
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
                v_ES.EvnSection_id
                ,vd.Diag_FullName
                ,v_ES.LpuSection_id
                ,vls.LpuSection_Code
                ,vls.Lpu_id
                ,vls.LpuSection_Name
                ,vls.LpuSection_FullName 
                ,RTRIM(ISNULL(v_LS.LpuSection_FullName, '')) as LpuSection_FullName
            FROM v_EvnSection v_ES 

            left join v_EvnOtherSection v_EOS with (nolock) on v_EOS.EvnOtherSection_pid = v_ES.EvnSection_id
            left join v_EvnOtherStac v_EOST with (nolock) on v_EOST.EvnOtherStac_pid = v_ES.EvnSection_id
            left join v_EvnOtherSectionBedProfile v_EOSBP with (nolock) on v_EOSBP.EvnOtherSectionBedProfile_pid = v_ES.EvnSection_id
            LEFT JOIN v_LpuSection v_LS WITH (NOLOCK) ON 
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
            SELECT TOP 1 
                vd.Diag_FullName
                ,ESLast.EvnSection_id
            from v_EvnPS EPS WITH (NOLOCK)
            left join v_EvnSection ESLast with (nolock) on ESLast.EvnSection_pid = EPS.EvnPS_id
                                  --and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
            LEFT JOIN v_Diag vd ON ESLast.Diag_id = vd.Diag_id --OR EPS.Diag_id = vd.Diag_id
            WHERE EPS.EvnPS_id = :LpuSection_id
            ORDER BY ESLast.EvnSection_id DESC
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
			select top 1
				RTRIM(ISNULL(AnatomWhere.AnatomWhere_Name, '')) as AnatomWhere_Name,
				RTRIM(ISNULL(DiagA.Diag_Code, '')) as DiagA_Code,
				RTRIM(ISNULL(DiagA.Diag_Name, '')) as DiagA_Name,
				RTRIM(ISNULL(DiagH.Diag_Code, '')) as DiagH_Code,
				RTRIM(ISNULL(DiagH.Diag_Name, '')) as DiagH_Name,
				RTRIM(ISNULL(DiagP.Diag_Code, '')) as DiagP_Code,
				RTRIM(ISNULL(DiagP.Diag_Name, '')) as DiagP_Name,
				EPS.Person_Age,
				ISNULL(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				RTRIM(ISNULL(Document.Document_Num, '')) as Document_Num,
				RTRIM(ISNULL(Document.Document_Ser, '')) as Document_Ser,
				RTRIM(ISNULL(DocumentType.DocumentType_Name, '')) as DocumentType_Name,
				RTRIM(ISNULL(DocumentType.DocumentType_Code, '')) as DocumentType_Code,
				RTRIM(ISNULL(DocumentType.DocumentType_MaskSer, '')) as DocumentType_MaskSer,				
				RTRIM(ISNULL(DocumentType.DocumentType_MaskNum, '')) as DocumentType_MaskNum,				
				EPS.EvnDirection_Num,
				convert(varchar(10), EPS.EvnDirection_setDT, 104) as EvnDirection_setDate,
				convert(varchar(10), EPS.EvnPS_disDate, 104) as EvnPS_disDate,
				EPS.EvnPS_disTime,
				EPS.EvnPS_HospCount,
				convert(varchar(10), ED.EvnDie_expDate, 104) as EvnDie_expDate,
				ED.EvnDie_expTime,
				ISNULL(IsAmbul.YesNo_Code, 0) as EvnLeave_IsAmbul,
				ISNULL(IsAnatom.YesNo_Code, 0) as EvnDie_IsAnatom,
				ISNULL(IsDiagMismatch.YesNo_Code, 0) as EvnPS_IsDiagMismatch,
				ISNULL(IsImperHosp.YesNo_Code, 0) as EvnPS_IsImperHosp,
				ISNULL(IsShortVolume.YesNo_Code, 0) as EvnPS_IsShortVolume,
				ISNULL(IsUnlaw.YesNo_Code, 0) as EvnPS_IsUnlaw,
				ISNULL(IsUnport.YesNo_Code, 0) as EvnPS_IsUnport,
				ISNULL(IsWrongCure.YesNo_Code, 0) as EvnPS_IsWrongCure,
				ISNULL(EPS.EvnPS_CodeConv, '') as EvnPS_CodeConv,
				ISNULL(EPS.EvnPS_NumCard, '') as EvnPS_NumCard,
				ISNULL(EPS.EvnPS_NumConv, '') as EvnPS_NumConv,
				convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate,
				EPS.EvnPS_setTime,
                convert(varchar(10), EPS.EvnPS_OutcomeDT, 104) as EvnPS_outcomeDate,
				convert(varchar(10), EPS.EvnPS_OutcomeDT, 108) as EvnPS_outcomeTime,
				EPS.EvnPS_TimeDesease,
				EPS.Okei_id,
				COALESCE(EL.EvnLeave_UKL, ED.EvnDie_UKL, EOL.EvnOtherLpu_UKL, EOS.EvnOtherSection_UKL, EOST.EvnOtherStac_UKL) as EvnLeave_UKL,
				RTRIM(ISNULL(L.Lpu_Name, '')) as Lpu_Name,
				RTRIM(ISNULL(L.Lpu_Nick, '')) as Lpu_Nick,
				COALESCE('('+cast(PreHospLpu.Lpu_id as varchar)+')', '('+cast(PHOM.OrgMilitary_id as varchar)+')', '('+cast(PHO.Org_id as varchar)+')', '') as PreHospLpu_Id,
				RTRIM(COALESCE(PHLS.LpuSection_Name, PreHospLpu.Lpu_Name, PHOM.OrgMilitary_Name, PHO.Org_Name, '')) as PrehospOrg_Name,
				RTRIM(COALESCE(PreHospLpu.Lpu_Nick, PHO.Org_Nick, '')) as PrehospOrg_Nick,
				RTRIM(ISNULL(L.UAddress_Address, '')) as LpuAddress,
                CASE PrehospArrive.PrehospArrive_Code
                    WHEN 2 THEN PrehospArrive.PrehospArrive_Name
                    ELSE CASE PrehospDirect.PrehospDirect_Code
                            WHEN 1 THEN L.Lpu_Nick
                            ELSE RTRIM(COALESCE(PreHospLpu.Lpu_Nick, PHO.Org_Nick, '')) + '  ' + PrehospDirect.PrehospDirect_Name
                         END
                END as KemNapravlen,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				RTRIM(ISNULL(OSTLS.LpuSection_Name, '')) as OtherStac_Name,
				RTRIM(ISNULL(OSTLUT.LpuUnitType_Name, '')) as OtherStacType_Name,
				RTRIM(ISNULL(LpuRegion.LpuRegion_Name, '')) as LpuRegion_Name,
				RTRIM(ISNULL(OD.Org_Name, '')) as OrgDep_Name,
				RTRIM(ISNULL(OJ.Org_Name, '')) as OrgJob_Name,
				RTRIM(ISNULL(OS.Org_Name, '')) as OrgSmo_Name,
				RTRIM(ISNULL(OS.Org_Nick, '')) as OrgSmo_Nick,
				RTRIM(ISNULL(OS.Org_Code, '')) as OrgSmo_Code,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(PC.PersonCard_Code) as PersonCard_Code,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				RTRIM(ISNULL(PS.Person_Snils, '')) as Person_Snils,
				RTRIM(ISNULL(PS.Person_EdNum, '')) as Person_EdNum,
				RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name,
				RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name,
				RTRIM(ISNULL(KLAreaType.KLAreaType_Name, '')) as KLAreaType_Name,
				RTRIM(ISNULL(KLAreaType.KLAreaType_SysNick, '')) as KLAreaType_SysNick,
                ISNULL(CONVERT(varchar(2), KO.Ocato),'') as Ocato,                              -- PETROV
                ISNULL(CONVERT(varchar(2), KLA.KLAdr_Ocatd),'') as OMSSprTerr_Code,             -- PETROV
				RTRIM(COALESCE(LeaveCause.LeaveCause_Name, OLC.LeaveCause_Name, OSC.LeaveCause_Name, OSTC.LeaveCause_Name)) as LeaveCause_Name,
				RTRIM(ISNULL(LeaveType.LeaveType_Name, '')) as LeaveType_Name,
				LTRIM(RTRIM(MPRec.Person_SurName)) + ' ' +LTRIM(RTRIM(SUBSTRING(MPRec.Person_FirName,1,1)))+'.'+LTRIM(RTRIM(SUBSTRING(MPRec.Person_SecName,1,1)))+'.' as PreHospMedPersonal_Fio,
				MPRec.Dolgnost_Name as Dolgnost_Name,
                RTRIM(ISNULL(MPRec.MedPersonal_Code, '')) as PreHospMedPersonal_Code,           -- PETROV
				RTRIM(ISNULL(EDAMP.MedPersonal_TabCode, '')) as AnatomMedPersonal_Code,
				RTRIM(ISNULL(EDAMP.Person_Fio, '')) as AnatomMedPersonal_Fio,
				RTRIM(ISNULL(EDMP.MedPersonal_TabCode, '')) as EvnDieMedPersonal_Code,
				RTRIM(ISNULL(EDMP.Person_Fin, '')) as EvnDieMedPersonal_Fin,
				RTRIM(ISNULL(OLC.LeaveCause_Name, '')) as OtherLpuCause_Name,
				RTRIM(ISNULL(OSC.LeaveCause_Name, '')) as OtherSectionCause_Name,
				RTRIM(ISNULL(OSTC.LeaveCause_Name, '')) as OtherStacCause_Name,
				RTRIM(ISNULL(OtherLpu.Lpu_Name, '')) as OtherLpu_Name,
				convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
				RTRIM(ISNULL(case when Polis.PolisType_id = 4 then PS.Person_EdNum else Polis.Polis_Num end, '')) as Polis_Num,
				RTRIM(ISNULL(case when Polis.PolisType_id = 4 then '' else Polis.Polis_Ser end, '')) as Polis_Ser,
				RTRIM(ISNULL(PolisType.PolisType_Name, '')) as PolisType_Name,
				RTRIM(ISNULL(Post.Post_Name, '')) as Post_Name,
				RTRIM(ISNULL(PayType.PayType_Name, '')) as PayType_Name,
				RTRIM(ISNULL(PHT.PrehospTrauma_Name, '')) as PrehospTrauma_Name,
				RTRIM(ISNULL(PrehospArrive.PrehospArrive_Name, '')) as PrehospArrive_Name,
				RTRIM(ISNULL(PrehospDirect.PrehospDirect_Name, '')) as PrehospDirect_Name,
				RTRIM(ISNULL(PrehospToxic.PrehospToxic_Name, '')) as PrehospToxic_Name,
				RTRIM(ISNULL(PrehospType.PrehospType_Name, '')) as PrehospType_Name,
				RTRIM(ISNULL(PrehospType.PrehospType_SysNick, '')) as PrehospType_SysNick,
				RTRIM(ISNULL(ResultDesease.ResultDesease_Name, '')) as ResultDesease_Name,
				RTRIM(ISNULL(Sex.Sex_Name, '')) as Sex_Name,
				RTRIM(ISNULL(Sex.Sex_Code, '')) as Sex_Code,
				RTRIM(ISNULL(SocStatus.SocStatus_Name, '')) as SocStatus_Name,
				RTRIM(ISNULL(InvalidType.InvalidType_begDate, '')) as InvalidType_begDate,
				RTRIM(ISNULL(InvalidType.InvalidType_Code, '')) as InvalidType_Code,
				RTRIM(ISNULL(InvalidType.InvalidType_Name, '')) as InvalidType_Name,
				RTRIM(ISNULL(PersonPrivilege.PrivilegeType_Name, '')) as PrivilegeType_Name,
				RTRIM(ISNULL(PersonPrivilege.PrivilegeType_Code, '')) as PrivilegeType_Code,
				RTRIM(ISNULL(PersonPrivilege.PersonPrivilege_Serie, '')) as PersonPrivilege_Serie,
				RTRIM(ISNULL(PersonPrivilege.PersonPrivilege_Number, '')) as PersonPrivilege_Number,
				convert(varchar(10), COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate), 104) as EvnPS_disDate,
				COALESCE(EL.EvnLeave_setTime, ED.EvnDie_setTime, EOL.EvnOtherLpu_setTime, EOS.EvnOtherSection_setTime, EOST.EvnOtherStac_setTime) as EvnPS_disTime,
				RTRIM(ISNULL(EvnUdost.EvnUdost_Ser, '') + ' ' + ISNULL(EvnUdost.EvnUdost_Num, '')) as EvnUdost_SerNum,
				RTRIM(COALESCE(AnatomLpu.Lpu_Name, AnatomLS.LpuSection_Name, AnatomOrg.OrgAnatom_Name, '')) as EvnAnatomPlace,
				ISNULL(EvnSection.LpuUnitType_Code, 0) as LpuUnitType_Code,
				ltrim(rtrim(MSF_did.Person_SurName)) + ' ' + ltrim(rtrim(MSF_did.Person_FirName)) + ' ' + ltrim(rtrim(MSF_did.Person_SecName)) as MedPersonal_did,
				LTRIM(RTRIM(ESLast.LpuSection_Name)) as HospSection_Name,
				ESLast.LpuSectionBedProfile_Name as HospSectionBedProfile_Name,
				ISNULL(convert(varchar(10), ESLast.EvnSection_disDate, 104), '') as Hosp_disDate,
				ESLast.HospitalDays,
				ESLast.LeaveType_Name as HospLeaveType_Name,
				ESLast.LeaveType_SysNick as HospLeaveType_Nick,
				ESLast.ResultDesease_Name as HospResultDesease_Name,
				ESLast.ResultDesease_SysNick as HospResultDesease_SysNick,
				EPS.EntranceModeType_id,
				DK.DeputyKind_Name,
				EPS.EvnPS_DeputyFIO,
                RTRIM(ISNULL(EPS.EvnPS_DeputyContact, 'нет')) as EvnPS_DeputyContact,
                Hosp.HospType_Name,
				RTRIM(ISNULL(EPS.EvnPS_PhaseDescr_pid, ''))  as EvnPS_PhaseDescr_pid,
				RTRIM(ISNULL(EPS.EvnPS_PhaseDescr_did, ''))  as EvnPS_PhaseDescr_did,
				RTRIM(ISNULL(AttachedLpu.AttachedLpuName, '')) as AttachedLpuName,
				RTRIM(ISNULL(AttachedLpu.AttachedLpuNick, '')) as AttachedLpuNick,
				PS.Person_Phone -- Pavel Petrov

					-- Sannikov
				,
                    -- подсчет койкодней  
				case when ESLast.EvnSection_disDate is not null
				then
					case
						when DATEDIFF(DAY, ESLast.EvnSection_setDate, ESLast.EvnSection_disDate) + 1 > 1
						then DATEDIFF(DAY, ESLast.EvnSection_setDate, ESLast.EvnSection_disDate)
						else DATEDIFF(DAY, ESLast.EvnSection_setDate, ESLast.EvnSection_disDate) + 1
					end
				else null
				end as koikodni, 
                
				RTRIM(ISNULL(PS.Person_Phone, '')) as PersonPhone_Phone -- TODO: дублирует код Павла Петрова
				,RTRIM(ISNULL(PS.FamilyStatus_id, '')) as FamilyStatus_id  -- Семейное положение
                ,ISNULL(PS.PersonFamilyStatus_IsMarried, '') as PersonFamilyStatus_IsMarried

                ,PS.Person_id as Person_id

				    -- Выписка
				,RTRIM(ISNULL(LeaveType.LeaveType_Code, '')) as LeaveType_Code
                ,convert(varchar(10), EPS.EvnPS_disDate, 104) as new_EvnPS_disDate   

                ,RTRIM(ISNULL(EPS.EvnPS_id, '555')) as LpuSection_id  -- ID Движения
                
                    -- Роды - данные (дети в отдельном запросе)
                ,bss.EvnSection_id as EvnSection_id  -- ТЕСТ joinа для родов
                ,bss.BirthSpecStac_id,
                
                bss.EvnSection_id,
                isnull(bss.BirthSpecStac_CountPregnancy, 0) as BirthSpecStac_CountPregnancy,
                isnull(bss.BirthSpecStac_CountBirth, 0) as BirthSpecStac_CountBirth,
                bss.BirthSpecStac_CountChild,
                isnull(bss.BirthSpecStac_CountChildAlive, 0) as BirthSpecStac_CountChildAlive,
                isnull((bss.BirthSpecStac_CountChild - bss.BirthSpecStac_CountChildAlive), '') as DeathChildCount,  -- TODO: разобраться с мертворожденными
                bss.BirthResult_id,
                bss.BirthPlace_id,
                bss.BirthSpecStac_OutcomPeriod,
                bss.BirthSpecStac_OutcomDT as BirthSpecStac_OutcomDT,
                DATEPART(hh, bss.BirthSpecStac_OutcomDT) as BirthSpecStac_OutcomDT_h,
                DATEPART(mi, bss.BirthSpecStac_OutcomDT) as BirthSpecStac_OutcomDT_m,
                bss.BirthSpec_id,
                bss.BirthSpecStac_IsHIVtest,
                bss.BirthSpecStac_IsHIV,
                bss.AbortType_id,
                bss.BirthSpecStac_IsMedicalAbort,
                ISNULL(bss.BirthSpecStac_BloodLoss, 0) as BirthSpecStac_BloodLoss,
                bss.PregnancySpec_id,
                bss.pmUser_insID,
                bss.pmUser_updID,
                bss.BirthSpecStac_insDT,
                bss.BirthSpecStac_updDT
                
                    -- даты
                ,DATEPART(yyyy, EPS.EvnPS_setDate ) as EvnPS_setDate_Year
                ,DATEPART(mm, EPS.EvnPS_setDate ) as EvnPS_setDate_Month
                ,DATEPART(dd, EPS.EvnPS_setDate ) as EvnPS_setDate_Day
                ,DATEPART(hh, EPS.EvnPS_setDate ) as EvnPS_setDate_Hour
                ,DATEPART(minute, EPS.EvnPS_setDate ) as EvnPS_setDate_Minute
                ,convert(varchar(30), EPS.EvnPS_setDate, 120) as EvnPS_setDate_raw 
                
                    -- Дата выписки из движения (для перевода)
                ,convert(varchar(30), COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate), 120) as EvnPS_disDate_raw     
                    
                ,DATEPART(yyyy, COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate) ) as EvnPS_disDate_Year
                ,DATEPART(mm, COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate) ) as EvnPS_disDate_Month
                ,DATEPART(dd, COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate) ) as EvnPS_disDate_Day
                ,DATEPART(hh, COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate) ) as EvnPS_disDate_Hour
                ,DATEPART(minute, COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate) ) as EvnPS_disDate_Minute
                
                    -- Дата выписки по КВС (из v_EvnPS)
                ,DATEPART(yyyy, EPS.EvnPS_disDate ) as last_EvnPS_disDate_Year
                ,DATEPART(mm, EPS.EvnPS_disDate ) as last_EvnPS_disDate_Month
                ,DATEPART(dd, EPS.EvnPS_disDate ) as last_EvnPS_disDate_Day
                ,DATEPART(hh, EPS.EvnPS_disDate ) as last_EvnPS_disDate_Hour
                ,DATEPART(minute, EPS.EvnPS_disDate ) as last_EvnPS_disDate_Minute
                
                ,isnull(EPS.EvnPS_disTime, '') as last_EvnPS_disTime
                
                ,convert(varchar(10), COALESCE(EL.EvnLeave_setDate, ED.EvnDie_setDate, EOL.EvnOtherLpu_setDate, EOS.EvnOtherSection_setDate, EOST.EvnOtherStac_setDate), 104) as other_EvnPS_disDate
				,COALESCE(EL.EvnLeave_setTime, ED.EvnDie_setTime, EOL.EvnOtherLpu_setTime, EOS.EvnOtherSection_setTime, EOST.EvnOtherStac_setTime) as other_EvnPS_disTime

                
                    -- Диагноз ребенка (и не только - взято из Samara_Search_model)
                ,ISNULL(Dtmp.Diag_FullName, '') as new_Diag_Name
                
                ,ESLast.EvnSection_id as my_EvnSection_id
                ,ESLast.Diag_id as my_Diag_id
                ,ESLast.LpuSection_id as my_LpuSection_id
                ,convert(varchar(10), EPS.EvnPS_disDate, 104) as EvnPS_disDate
                
                ,convert(varchar(10), EPS.EvnPS_disDate, 104) as EvnPS_disDate
                ,isnull(vlsw.LpuSectionWard_Name, '') as LpuSectionWard_Name
                ,RTRIM(ISNULL(v_LS.LpuSection_FullName, '')) as LpuSection_FullName
				-- END OF Sannikov

			from v_EvnPS EPS WITH (NOLOCK)
				inner join v_Lpu L with (nolock) on L.Lpu_id = EPS.Lpu_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = EPS.Person_id
					-- PS.Server_id = EPS.Server_id and PS.PersonEvn_id = EPS.PersonEvn_id
			--	left join v_EvnSection ESLast with (nolock) on ESLast.EvnSection_pid = EPS.EvnPS_id
				--	and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
                    
------------------------------------------------------------------------------------------ samara
left join 
(select 
	EvnClass.EvnClass_id, 
	EvnClass.EvnClass_Name,
	EvnSection.EvnSection_id, 	
	cast(cast(Evn.Evn_setDT as date) as datetime) as EvnSection_setDate,
	left(cast(Evn_setDT as time),5) as EvnSection_setTime,
	cast(cast(Evn.Evn_didDT as date) as datetime) as EvnSection_didDate,
	left(cast(Evn_didDT as time),5) as EvnSection_didTime,
	cast(cast(Evn.Evn_disDT as date) as datetime) as EvnSection_disDate,
	left(cast(Evn_disDT as time),5) as EvnSection_disTime,
	DateDiff(day, Evn.Evn_setDT, Evn_disDT) HospitalDays,
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
where isnull(Evn.Evn_deleted,1) = 1 
) ESLast on ESLast.EvnSection_pid = EPS.EvnPS_id

				-- Sannikov (операции, роды, палата)
                    -- Данные родов
                LEFT JOIN dbo.v_BirthSpecStac bss WITH ( NOLOCK ) ON bss.EvnSection_id = ESLast.EvnSection_id
                
                    -- Палата
                LEFT JOIN v_LpuSectionWard vlsw WITH (NOLOCK) ON ESLast.LpuSectionWard_id = vlsw.LpuSectionWard_id
                
                    -- Диагноз ребенка (и не только - взято из Samara_Search_model)
                left join v_Diag Dtmp with (nolock) on Dtmp.Diag_id = ESLast.Diag_id
                    
                    -- Другое ЛПУ или отделение или тип стационара

                LEFT JOIN v_LpuSection v_LS WITH (NOLOCK) ON 
                    v_LS.LpuSection_id = ESLast.EOS_LpuSection_oid
                    OR v_LS.LpuSection_id = ESLast.EOST_LpuSection_oid

				-- END OF Sannikov
---------------------------------------------------------------------------------------------------
                    
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join KLAreaType with (nolock) on KLAreaType.KLAreaType_id = UAddr.KLAreaType_id
                outer apply (
					select top 1 vak.KLADR_Ocatd as Ocato
					from v_Address_KLADR vak WITH (NOLOCK)
					where vak.Address_id = PAddr.Address_id
				) KO
                outer apply (
					select top 1 kla.KLAdr_Ocatd
					from KLArea kla WITH (NOLOCK)
					where kla.KLArea_id = PAddr.KLRgn_id
				) KLA
				left join Document with (nolock) on Document.Document_id = PS.Document_id
				left join DocumentType with (nolock) on DocumentType.DocumentType_id = Document.DocumentType_id
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EPS.LpuSection_pid
				left join LpuSection PHLS with (nolock) on PHLS.LpuSection_id = EPS.LpuSection_did
				left join OrgDep with (nolock) on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org OD with (nolock) on OD.Org_id = OrgDep.Org_id
				left join Org PHO with (nolock) on PHO.Org_id = EPS.Org_did
				left join v_OrgMilitary PHOM with (nolock) on PHOM.OrgMilitary_id = EPS.OrgMilitary_did
				left join Job with (nolock) on Job.Job_id = PS.Job_id
				left join Org OJ with (nolock) on OJ.Org_id = Job.Org_id
				left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EPS.EvnPS_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EPS.EvnPS_insDT)
					and PC.Lpu_id = EPS.Lpu_id
				left join LpuRegion with (nolock) on LpuRegion.LpuRegion_id = PC.LpuRegion_id
				left join Post with (nolock) on Post.Post_id = Job.Post_id
				left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
				left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo with (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
                left join v_OmsSprTerr OST with (nolock) on OST.OmsSprTerr_id = Polis.OmsSprTerr_id
				left join Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
				left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = ESLast.EvnSection_id
				left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ESLast.EvnSection_id
				left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = ESLast.EvnSection_id
				left join v_EvnOtherSection EOS with (nolock) on EOS.EvnOtherSection_pid = ESLast.EvnSection_id
				left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ESLast.EvnSection_id
				left join Diag DiagH with (nolock) on DiagH.Diag_id = EPS.Diag_did
				left join Diag DiagP with (nolock) on DiagP.Diag_id = EPS.Diag_pid
				left join Diag DiagA with (nolock) on DiagA.Diag_id = ED.Diag_aid
				left join AnatomWhere with (nolock) on AnatomWhere.AnatomWhere_id = ED.AnatomWhere_id
				left join LeaveCause with (nolock) on LeaveCause.LeaveCause_id = EL.LeaveCause_id
				left join LeaveCause OSC with (nolock) on OSC.LeaveCause_id = EOS.LeaveCause_id
				left join LeaveCause OSTC with (nolock) on OSTC.LeaveCause_id = EOST.LeaveCause_id
				left join LeaveCause OLC with (nolock) on OLC.LeaveCause_id = EOL.LeaveCause_id
				left join LeaveType with (nolock) on LeaveType.LeaveType_id = EPS.LeaveType_id
				left join v_Lpu OtherLpu with (nolock) on OtherLpu.Lpu_id = EOL.Lpu_oid
				left join v_Lpu PreHospLpu with (nolock) on PreHospLpu.Lpu_id = EPS.Lpu_did
				left join v_MedPersonal MPRec with (nolock) on MPRec.MedPersonal_id = EPS.MedPersonal_pid
					and MPRec.Lpu_id = EPS.Lpu_id
				left join v_MedPersonal EDMP with (nolock) on EDMP.MedPersonal_id = ED.MedPersonal_id
					and EDMP.Lpu_id = ED.Lpu_id
				left join v_MedPersonal EDAMP with (nolock) on EDAMP.MedPersonal_id = ED.MedPersonal_aid
					and EDAMP.Lpu_id = ED.Lpu_id
				left join v_Lpu AnatomLpu with (nolock) on AnatomLpu.Lpu_id = ED.Lpu_aid
				left join v_OrgAnatom AnatomOrg with (nolock) on AnatomOrg.OrgAnatom_id = ED.OrgAnatom_id
				left join LpuSection AnatomLS with (nolock) on AnatomLS.LpuSection_id = ED.LpuSection_aid
				left join LpuUnitType OSTLUT with (nolock) on OSTLUT.LpuUnitType_id = EOST.LpuUnitType_oid
				left join LpuSection OSTLS with (nolock) on OSTLS.LpuSection_id = EOST.LpuSection_oid
				left join PayType with (nolock) on PayType.PayType_id = EPS.PayType_id
				left join PrehospArrive with (nolock) on PrehospArrive.PrehospArrive_id = EPS.PrehospArrive_id
				left join PrehospDirect with (nolock) on PrehospDirect.PrehospDirect_id = EPS.PrehospDirect_id
				left join PrehospToxic with (nolock) on PrehospToxic.PrehospToxic_id = EPS.PrehospToxic_id
				left join v_PrehospTrauma PHT with (nolock) on PHT.PrehospTrauma_id = EPS.PrehospTrauma_id
				left join PrehospType with (nolock) on PrehospType.PrehospType_id = EPS.PrehospType_id
				left join ResultDesease with (nolock) on ResultDesease.ResultDesease_id = ISNULL(EL.ResultDesease_id, ISNULL(EOL.ResultDesease_id, ISNULL(EOS.ResultDesease_id, EOST.ResultDesease_id)))
				left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join SocStatus with (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
				left join YesNo IsAmbul with (nolock) on IsAmbul.YesNo_id = EL.EvnLeave_IsAmbul
				left join YesNo IsAnatom with (nolock) on IsAnatom.YesNo_id = ED.EvnDie_IsAnatom
				left join YesNo IsDiagMismatch with (nolock) on IsDiagMismatch.YesNo_id = EPS.EvnPS_IsDiagMismatch
				left join YesNo IsImperHosp with (nolock) on IsImperHosp.YesNo_id = EPS.EvnPS_IsImperHosp
				left join YesNo IsShortVolume with (nolock) on IsShortVolume.YesNo_id = EPS.EvnPS_IsShortVolume
				left join YesNo IsUnlaw with (nolock) on IsUnlaw.YesNo_id = EPS.EvnPS_IsUnlaw
				left join YesNo IsUnport with (nolock) on IsUnport.YesNo_id = EPS.EvnPS_IsUnport
				left join YesNo IsWrongCure with (nolock) on IsWrongCure.YesNo_id = EPS.EvnPS_IsWrongCure
				left join v_MedPersonal MSF_did on MSF_did.MedPersonal_id = EPS.MedPersonal_did and MSF_did.Lpu_id = :Lpu_id 
				outer apply (
					select top 1
						PrivilegeType_Code as InvalidType_Code,
						PersonPrivilege_Group as InvalidType_Name,
						convert(varchar(10), PersonPrivilege_begDate, 104) as InvalidType_begDate
					from
						v_PersonPrivilege WITH (NOLOCK)
					where PersonPrivilege_Group is not null
						and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
				) InvalidType
				outer apply (
					select top 1
						PrivilegeType_Name,
						PrivilegeType_Code,
						PersonPrivilege_Serie,
						PersonPrivilege_Number
					from
						v_PersonPrivilege WITH (NOLOCK)
					where Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
				) PersonPrivilege
				outer apply (
					select top 1
						EvnUdost_Num,
						EvnUdost_Ser
					from
						v_EvnUdost WITH (NOLOCK)
					where EvnUdost_setDate <= dbo.tzGetDate()
						and Person_id = PS.Person_id
					order by EvnUdost_setDate desc
				) EvnUdost
				outer apply (
					select top 1
						LUT2.LpuUnitType_Code
					from
						v_EvnSection ES2 with (nolock)
						inner join LpuSection LS2 with (nolock) on LS2.LpuSection_id = ES2.LpuSection_id
						inner join LpuUnit LU2 with (nolock) on LU2.LpuUnit_id = LS2.LpuUnit_id
						inner join LpuUnitType LUT2 with (nolock) on LUT2.LpuUnitType_id = LU2.LpuUnitType_id
							and LUT2.LpuUnitType_Code in (2, 3, 4, 5)
					where ES2.EvnSection_pid = EPS.EvnPS_id
					order by ES2.EvnSection_setDate desc
				) EvnSection
                left join v_DeputyKind DK on DK.DeputyKind_id = EPS.DeputyKind_id
                outer apply(
					select top 1 ps.HospType_id, ht.HospType_Name
					from EvnPS ps with (nolock)
					join v_HospType ht on ht.HospType_id = ps.HospType_id
					where ps.EvnPS_id = EPS.EvnPS_id
				) Hosp
				outer apply (
					select top 1 
					Lpu_Name as AttachedLpuName, Lpu_Nick as AttachedLpuNick
					from v_Lpu WITH (NOLOCK)
					where Lpu_id = PS.Lpu_Id
				) AttachedLpu
			where
				EPS.Lpu_id = :Lpu_id".$where;
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
			select count(EvnPS.Evn_id) as 'EvnPS_HospCount'
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
			$withMedStaffFact_from = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :MedStaffFact_id
				left join v_LpuUnit LU with (nolock) on MSF.LpuUnit_id = LU.LpuUnit_id
			';
			$params['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}
		/*if ( isSuperAdmin() ) {
			$accessType = '(1 = 1)';
			$withMedStaffFact_from = '';
		}*/
		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType
				,EPS.EvnPS_id
				,EPS.EvnPS_IsSigned
				,EPS.Lpu_id
				,EPS.EvnPS_IsCont
				,EPS.Diag_did
				,EPS.DiagSetPhase_did
				,EPS.EvnPS_PhaseDescr_did
				,EPS.Diag_pid
				,EPS.DiagSetPhase_pid
				,EPS.EvnPS_PhaseDescr_pid
				,RTRIM(EPS.EvnPS_NumCard) as EvnPS_NumCard
				,EPS.LeaveType_id
				,EPS.PayType_id
				,convert(varchar(10), EPS.EvnPS_setDT, 104) as EvnPS_setDate
				,EPS.EvnPS_setTime
				,convert(varchar(10), EPS.EvnPS_OutcomeDT, 104) as EvnPS_OutcomeDate
				,convert(varchar(5), EPS.EvnPS_OutcomeDT, 108) as EvnPS_OutcomeTime
				,EPS.EvnDirection_id
				,EPS.PrehospDirect_id
				,EPS.LpuSection_did
				,ISNULL(EPS.Org_did, ISNULL(LPU_DID.Org_id, EPS.OrgMilitary_did)) as Org_did
				,LpuDid.Lpu_id as Lpu_did
				,EPS.LpuSection_pid
				,EPS.MedPersonal_pid as MedStaffFact_pid
				,EPS.EvnDirection_Num
				,convert(varchar(10), EPS.EvnDirection_setDT, 104) as EvnDirection_setDate
				,EPS.PrehospArrive_id
				,EPS.EvnPS_CodeConv
				,EPS.EvnPS_NumConv
				,EPS.PrehospToxic_id
				,EPS.PrehospType_id
				,EPS.EvnPS_HospCount
				,EPS.EvnPS_TimeDesease
				,EPS.Okei_id
				,EPS.PrehospTrauma_id
				,EPS.EvnPS_IsUnlaw
				,EPS.EvnPS_IsUnport
				,EPS.EvnPS_IsImperHosp
				,EPS.EvnPS_IsNeglectedCase
				,EPS.EvnPS_IsShortVolume
				,EPS.EvnPS_IsWrongCure
				,EPS.EvnPS_IsDiagMismatch
				,ISNULL(EPS.EvnPS_IsWaif, 1) as EvnPS_IsWaif
				,EPS.EvnPS_IsPLAmbulance
				,EPS.PrehospWaifArrive_id
				,EPS.PrehospWaifReason_id
				,ES.LpuSection_id
				,EPS.PrehospWaifRefuseCause_id
				,EPS.EvnPS_IsTransfCall
				,EPS.Person_id
				,EPS.PersonEvn_id
				,EPS.Server_id
				,EPS.EvnPS_IsWithoutDirection
				,EPS.EvnQueue_id
				,EPS.EvnPS_IsPrehospAcceptRefuse
				,convert(varchar(10), EPS.EvnPS_PrehospAcceptRefuseDT, 104) as EvnPS_PrehospAcceptRefuseDT
				,convert(varchar(10), EPS.EvnPS_PrehospWaifRefuseDT, 104) as EvnPS_PrehospWaifRefuseDT
				,EPS.LpuSection_eid
				,EPS.PrehospStatus_id
                -- samara
		        ,EPS.MedPersonal_did
				,EPS.EntranceModeType_id
				,EPS.EvnPS_DrugActions
                ,EPS.HospType_id                
                ,EPS.DeputyKind_id
                ,EPS.EvnPS_DeputyFIO
                ,EPS.EvnPS_DeputyContact
                --,EPS.TimeDeseaseType_id
                --
			FROM
				v_EvnPS EPS with (nolock)
				left join v_Lpu LPU_DID with (nolock) on LPU_DID.Lpu_id = EPS.Lpu_did
				left join v_EvnSection ES with (NOLOCK) on EPS.EvnPS_id = ES.EvnSection_pid and ES.EvnSection_Index = 0
				outer apply(
					select top 1 Lpu_id from v_Lpu with (nolock) where Org_id = ISNULL(EPS.Org_did,EPS.OrgMilitary_did)
				) LpuDid
				{$withMedStaffFact_from}
			WHERE (1 = 1)
				and EPS.EvnPS_id = :EvnPS_id
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
			$data['EvnPS_disDate'] .= ' ' . $data['EvnPS_disTime'] . ':00.000';
		}

		if ( isset($data['EvnPS_setTime']) ) {
			$data['EvnPS_setDate'] .= ' ' . $data['EvnPS_setTime'] . ':00.000';
		}

		$EvnPS_OutcomeDT = 'null';
		$data['EvnPS_OutcomeDT'] = 'null';
		if (!empty($data['EvnPS_OutcomeDate']) && !empty($data['EvnPS_OutcomeTime'])) {
			$data['EvnPS_OutcomeDT'] = $data['EvnPS_OutcomeDate'] . ' ' . $data['EvnPS_OutcomeTime'] . ':00.000';
			$EvnPS_OutcomeDT = ":EvnPS_OutcomeDT";
			
			if ( isset($data['EvnPS_id']) ) {
				// Автоматическое изменение времени движения при изменение его в Исходе пребывания в приемном отделении (refs #19567)
				// Ищем первое движение
				$query = "
					select top 1 EvnSection_id from v_EvnSection (nolock) where EvnSection_pid = :EvnPS_id order by EvnSection_setDT
				";
				$result = $this->db->query($query, array('EvnPS_id' => $data['EvnPS_id']));
				
				if (is_object($result)) {
					$res =  $result->result('array');
					if (count($res) > 0) {
						// обновляем ему дату/время поступления EvnSection_setDate/EvnSection_setTime
						$query = "
							update Evn with (rowlock)
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
			$EvnPS_OutcomeDT = '@curdate';
		}
		//если выбрано отделение или отказ и не определены дата и время исхода из приемного, то берем текущее время и дату
		if( 
			(!empty($data['LpuSection_eid']) || !empty($data['PrehospWaifRefuseCause_id']))
			 && 'null' == $EvnPS_OutcomeDT
		)
		{
			$EvnPS_OutcomeDT = '@curdate';
		}
		
		// проставляем дату отказа, если пустая.
		if(!empty($data['PrehospWaifRefuseCause_id']) && empty($data['EvnPS_PrehospWaifRefuseDT']))
		{
			$EvnPS_PrehospWaifRefuseDT = '@curdate';
		} 
		else 
		{
			$EvnPS_PrehospWaifRefuseDT = ":EvnPS_PrehospWaifRefuseDT";
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;
			set @curdate = dbo.tzGetDate();
			set @Res = :EvnPS_id;
			exec " . $procedure . "
				@EvnPS_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPS_setDT = :EvnPS_setDT,
				@EvnPS_OutcomeDT = {$EvnPS_OutcomeDT},
				@EvnPS_disDT = :EvnPS_disDT,
				@EvnPS_NumCard = :EvnPS_NumCard,
				@EvnPS_IsCont = :EvnPS_IsCont,
				@Diag_aid = :Diag_aid,
				@Diag_pid = :Diag_pid,
				@DiagSetPhase_pid = :DiagSetPhase_pid,
				@EvnPS_PhaseDescr_pid = :EvnPS_PhaseDescr_pid,
				@Diag_did = :Diag_did,
				@DiagSetPhase_did = :DiagSetPhase_did,
				@EvnPS_PhaseDescr_did = :EvnPS_PhaseDescr_did,
				@EvnQueue_id = :EvnQueue_id,
				@EvnDirection_id = :EvnDirection_id,
				@PrehospArrive_id = :PrehospArrive_id,
				@PrehospDirect_id = :PrehospDirect_id,
				@PrehospToxic_id = :PrehospToxic_id,
				@PayType_id = :PayType_id,
				@PrehospTrauma_id = :PrehospTrauma_id,
				@PrehospType_id = :PrehospType_id,
				@Lpu_did = :Lpu_did,
				@Org_did = :Org_did,
				@LpuSection_did = :LpuSection_did,
				@OrgMilitary_did = :OrgMilitary_did,
				@LpuSection_pid = :LpuSection_pid,
				@MedPersonal_pid = :MedPersonal_pid,
				@EvnDirection_Num = :EvnDirection_Num,
				@EvnDirection_setDT = :EvnDirection_setDT,
				@EvnPS_CodeConv = :EvnPS_CodeConv,
				@EvnPS_NumConv = :EvnPS_NumConv,
				@EvnPS_TimeDesease = :EvnPS_TimeDesease,
				@Okei_id = :Okei_id,
				@EvnPS_HospCount = :EvnPS_HospCount,
				@EvnPS_IsUnlaw = :EvnPS_IsUnlaw,
				@EvnPS_IsUnport = :EvnPS_IsUnport,
				@EvnPS_IsImperHosp = :EvnPS_IsImperHosp,
				@EvnPS_IsNeglectedCase = :EvnPS_IsNeglectedCase,
				@EvnPS_IsShortVolume = :EvnPS_IsShortVolume,
				@EvnPS_IsWrongCure = :EvnPS_IsWrongCure,
				@EvnPS_IsDiagMismatch = :EvnPS_IsDiagMismatch,
				@EvnPS_IsWithoutDirection = :EvnPS_IsWithoutDirection,
				@EvnPS_IsWaif = :EvnPS_IsWaif,
				@EvnPS_IsPLAmbulance = :EvnPS_IsPLAmbulance,
				@PrehospWaifArrive_id = :PrehospWaifArrive_id,
				@PrehospWaifReason_id = :PrehospWaifReason_id,
				@PrehospWaifRefuseCause_id = :PrehospWaifRefuseCause_id,
				@EvnPS_IsTransfCall = :EvnPS_IsTransfCall,
				@EvnPS_IsPrehospAcceptRefuse = :EvnPS_IsPrehospAcceptRefuse,
				@EvnPS_PrehospAcceptRefuseDT = :EvnPS_PrehospAcceptRefuseDT,
				@EvnPS_PrehospWaifRefuseDT = {$EvnPS_PrehospWaifRefuseDT},
				@LpuSection_eid = :LpuSection_eid,
				@PrehospStatus_id = :PrehospStatus_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output
                -- samara
                ,
				@MedPersonal_did = :MedPersonal_did,
				@EntranceModeType_id = :EntranceModeType_id,
				@EvnPS_DrugActions = :EvnPS_DrugActions,
				@HospType_id = :HospType_id,
				@DeputyKind_id = :DeputyKind_id,
				@EvnPS_DeputyFIO = :EvnPS_DeputyFIO,
				@EvnPS_DeputyContact = :EvnPS_DeputyContact
				--,@TimeDeseaseType_id = TimeDeseaseType_id
                --
                ;
			select @Res as EvnPS_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
		
		$query = "select SUM(DATEDIFF(DAY, EvnSection_setDT, EvnSection_disDT)) as SectionsDays
			from v_EvnSection ES with (nolock)
			where ES.EvnSection_pid = :EvnSection_pid and ES.Lpu_id = :Lpu_id";
		
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
			$filter .= " AND PS.Person_Surname LIKE :Person_SurName";
			$params['Person_SurName'] = rtrim($data['Person_SurName']).'%';
		}
		if (!empty($data['Person_FirName'])) 
		{
			$filter .= " AND PS.Person_Firname LIKE :Person_FirName";
			$params['Person_FirName'] = rtrim($data['Person_FirName']).'%';
		}
		if (!empty($data['Person_SecName'])) 
		{
			$filter .= " AND PS.Person_Secname LIKE :Person_SecName";
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
			$filter_dir .= " AND cast(isnull(TTS.TimetableStac_setDate,ED.EvnDirection_setDT) as date) = :date";
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
			$filter_eq .= "isnull(EQ.EvnQueue_IsArchived,1) = 1 AND";
		}
		// иначе отобразить все 
        if (!empty($data['date'])) {
		    $sql = "
			select
				'EvnDirection_'+ convert(varchar, ED.EvnDirection_id) as keyNote,
				convert(varchar(8), coalesce (EvnPS.EvnPS_setDate, TTS.TimetableStac_setDate, ED.EvnDirection_setDT), 112) as sortDate,
				EST.PrehospStatus_id as groupField,
				ED.EvnDirection_id as EvnDirection_id,
				ED.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),ED.EvnDirection_setDate,104) as EvnDirection_setDate,
				ED.Diag_id as Diag_did,
				ED.LpuSection_id as LpuSection_did,
				ED.Lpu_id as Lpu_did,
				ED.DirType_id as DirType_id,
				isnull(DT.DirType_Name,'') +', '+ isnull(LSP.LpuSectionProfile_Name,'') +', '+ isnull(DiagD.Diag_Code,'')+'.'+ isnull(DiagD.Diag_Name,'') as Direction_exists,
				convert(varchar(10), TTS.TimetableStac_setDate, 104) as TimetableStac_setDate,
				null as SMMP_exists,
				null as EvnPS_CodeConv,
				null as TimetableStac_insDT,
				null as EvnQueue_setDate,
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as IsHospitalized,
				EvnPS.EvnPS_id as EvnPS_id, 
				convert(varchar(10), EvnPS.EvnPS_setDT, 104) +' '+ convert(varchar(5), EvnPS.EvnPS_setDT, 108) as EvnPS_setDT,--Дата/время поступления;  если есть КВС
				ED.TimetableStac_id as TimetableStac_id,
				LSP.LpuSectionProfile_Name,
				ED.LpuSectionProfile_id as LpuSectionProfile_did,
				EST.PrehospStatus_id as PrehospStatus_id,
				EST.PrehospStatus_Name as PrehospStatus_Name,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,
				puc.PMUser_Name as pmUser_Name, --pmUser_Name Оператор. (направления/бирки, как в АРМ поликлиники)
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as IsRefusal,-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id,
				isnull(EvnPS.EvnPS_IsTransfCall,1) as IsCall, -- Передан активный вызов 
				isnull(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp, -- Талон передан на ССМП
				EvnPS.PrehospArrive_id as PrehospArrive_id,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as Person_age,
				PS.Person_Fio,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Server_id,
				LS.LpuSection_Name,
				PP.PrivilegeType_Name,
				SS.SocStatus_Name
			from 
				v_EvnDirection_all ED with (NOLOCK)
				left join v_Person_all PS with (NOLOCK) on ED.Person_id = PS.Person_id and ED.PersonEvn_id = PS.PersonEvn_id and ED.Server_id = PS.Server_id
				left join v_EvnPS EvnPS with (NOLOCK) on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP with (NOLOCK) on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_Diag DiagD with (NOLOCK) on ED.Diag_id= DiagD.Diag_id
				left join v_TimetableStac_lite TTS with (NOLOCK) on ED.EvnDirection_id = TTS.EvnDirection_id
				left join v_LpuSection LS with (NOLOCK) on isnull(EvnPS.LpuSection_eid,TTS.LpuSection_id) = LS.LpuSection_id
				left join v_Diag Diag with (NOLOCK) on isnull(EvnPS.Diag_pid,ED.Diag_id) = Diag.Diag_id
				left join v_PrehospStatus EST with (NOLOCK) on isnull(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
				left join pmUserCache puc with (NOLOCK) on isnull (TTS.pmUser_updId, ED.pmUser_insID) = puc.pmUser_id
				left join v_PersonPrivilege PP  with (NOLOCK) on PP.Person_id = PS.Person_id
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
				'EvnPS_'+ convert(varchar, EvnPS.EvnPS_id) as keyNote,
				convert(varchar(8), EvnPS.EvnPS_setDate, 112) as sortDate,
				EST.PrehospStatus_id as groupField,
				null as EvnDirection_id,
				EvnPS.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),EvnPS.EvnDirection_setDT,104) as EvnDirection_setDate,
				EvnPS.Diag_did,
				EvnPS.LpuSection_did,
				EvnPS.Org_did as Lpu_did,
				null as DirType_id,
				null as Direction_exists,
				null as TimetableStac_setDate,
				null as SMMP_exists,
				null as EvnPS_CodeConv,
				null as TimetableStac_insDT,
				null as EvnQueue_setDate,
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as IsHospitalized,
				EvnPS.EvnPS_id as EvnPS_id, 
				convert(varchar(10), EvnPS.EvnPS_setDT, 104) +' '+ convert(varchar(5), EvnPS.EvnPS_setDT, 108) as EvnPS_setDT,--Дата/время поступления;  если есть КВС
				null as TimetableStac_id,
				'' as LpuSectionProfile_Name,
				null as LpuSectionProfile_did,
				EST.PrehospStatus_id as PrehospStatus_id,
				EST.PrehospStatus_Name as PrehospStatus_Name,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,--Диагноз приемного; код, наименование
				'' as pmUser_Name,
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as IsRefusal,-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id,
				isnull(EvnPS.EvnPS_IsTransfCall,1) as IsCall, -- Передан активный вызов 
				isnull(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp, -- Талон передан на ССМП
				EvnPS.PrehospArrive_id as PrehospArrive_id,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as Person_age,
				PS.Person_Fio,
				EvnPS.Person_id,
				EvnPS.PersonEvn_id,
				EvnPS.Server_id,
				LS.LpuSection_Name as LpuSection_FullName,
				PP.PrivilegeType_Name,
				SS.SocStatus_Name
			from v_EvnPS EvnPS with (NOLOCK)
				left join v_Person_all PS with (NOLOCK) on EvnPS.Person_id = PS.Person_id and EvnPS.PersonEvn_id = PS.PersonEvn_id and EvnPS.Server_id = PS.Server_id
				left join v_LpuSection LS with (NOLOCK) on EvnPS.LpuSection_eid = LS.LpuSection_id
				left join v_Diag Diag with (NOLOCK) on EvnPS.Diag_pid = Diag.Diag_id
				left join v_PrehospStatus EST with (NOLOCK) on EvnPS.PrehospStatus_id = EST.PrehospStatus_id
				left join v_TimetableStac_lite TTS with (NOLOCK) on EvnPS.EvnPS_id = TTS.Evn_id
				left join v_PersonPrivilege PP  with (NOLOCK) on PP.Person_id = PS.Person_id
				left join v_PersonState vPS with(NOLOCK) on vPS.Person_id = EvnPS.Person_id
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
				'TimetableStac_'+ convert(varchar, TTS.TimetableStac_id) as keyNote,
				convert(varchar(8), isnull(EvnPS.EvnPS_setDT,TTS.TimetableStac_setDate), 112) as sortDate,
				EST.PrehospStatus_id as groupField,
				null as EvnDirection_id,
				EvnPS.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),EvnPS.EvnDirection_setDT,104) as EvnDirection_setDate,
				isnull(EvnPS.Diag_did,CmpDiag.Diag_id) as Diag_did,
				EvnPS.LpuSection_did as LpuSection_did,
				EvnPS.Org_did as Lpu_did,
				null as DirType_id,
				null as Direction_exists,
				convert(varchar(10), TTS.TimetableStac_setDate, 104) as TimetableStac_setDate,		
				case when TTS.TimetableType_id = 6 then 
					isnull(TTSLS.LpuSectionProfile_Name,'') 
					+', '+ isnull(CmpDiag.Diag_Code,'') +'.'+ isnull(CmpDiag.Diag_Name,'') 
					+', Бригада №'+ isnull(EmD.EmergencyData_BrigadeNum,'')
				else 
					null
				end as SMMP_exists,
				isnull(EmD.EmergencyData_CallNum,'') as EvnPS_CodeConv,
				convert(varchar(10), TTS.TimetableStac_updDT, 104) + ' ' + convert(varchar(5), TTS.TimetableStac_updDT, 108) as TimetableStac_insDT,
				null as EvnQueue_setDate,
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as IsHospitalized,
				EvnPS.EvnPS_id as EvnPS_id, 
				convert(varchar(10), EvnPS.EvnPS_setDT, 104) +' '+ convert(varchar(5), EvnPS.EvnPS_setDT, 108) as EvnPS_setDT,
				TTS.TimetableStac_id as TimetableStac_id,
				TTSLS.LpuSectionProfile_Name as LpuSectionProfile_Name,
				TTSLS.LpuSectionProfile_id as LpuSectionProfile_did,
				EST.PrehospStatus_id as PrehospStatus_id,
				EST.PrehospStatus_Name as PrehospStatus_Name,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,
				puc.PMUser_Name as pmUser_Name,
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as IsRefusal,
				EvnPS.PrehospWaifRefuseCause_id,
				isnull(EvnPS.EvnPS_IsTransfCall,1) as IsCall,
				isnull(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp,
				EvnPS.PrehospArrive_id as PrehospArrive_id,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as Person_age,
				isnull(PS.Person_SurName,'Не идентифицирован') +' '+ isnull(PS.Person_FirName,'') +' '+ isnull(PS.Person_SecName,'') as Person_Fio,
				PS.Person_id,
				isnull(EvnPS.PersonEvn_id,PS.PersonEvn_id) as PersonEvn_id,
				isnull(EvnPS.Server_id,PS.Server_id) as Server_id,
				null as LpuSection_Name,
				PP.PrivilegeType_Name,
				SS.SocStatus_Name
			from v_TimetableStac_lite TTS with (NOLOCK)
				left join v_EvnPS EvnPS with (NOLOCK) on TTS.Evn_id = EvnPS.EvnPS_id
				left join v_LpuSection LS with (NOLOCK) on isnull(EvnPS.LpuSection_eid,TTS.LpuSection_id) = LS.LpuSection_id
				left join v_LpuSection TTSLS with (NOLOCK) on TTS.LpuSection_id = TTSLS.LpuSection_id --and TTSLS.LpuUnitType_id in (1,6,9)
				--left join v_EmergencyData EmD with (NOLOCK) on TTS.EmergencyData_id = EmD.EmergencyData_id
				left join EmergencyData EmD with (NOLOCK) on TTS.EmergencyData_id = EmD.EmergencyData_id
				left join v_PersonState PS with (NOLOCK) on coalesce(EvnPS.Person_id,TTS.Person_id,EmD.Person_lid) = PS.Person_id
				outer apply (
					select top 1
						EmergencyDataStatus_id
					from
						v_EmergencyDataHistory with (nolock)
					where
						EmergencyData_id = EmD.EmergencyData_id
					order by
						EmergencyDataHistory_id desc
				) EDH
				left join v_Diag Diag with (NOLOCK) on isnull(EvnPS.Diag_pid,EmD.Diag_id) = Diag.Diag_id
				--left join v_CmpDiag CmpDiag with (NOLOCK) on EmD.CmpDiag_id = CmpDiag.CmpDiag_id
				left join v_Diag CmpDiag with (NOLOCK) on EmD.Diag_id = CmpDiag.Diag_id
				left join v_PrehospStatus EST with (NOLOCK) on isnull(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
				left join pmUserCache puc with (NOLOCK) on TTS.pmUser_updId = puc.pmUser_id
				left join v_EvnDirection_all ED with (NOLOCK) on TTS.EvnDirection_id = ED.EvnDirection_id
				left join v_PersonPrivilege PP  with (NOLOCK) on PP.Person_id = PS.Person_id
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
				'EvnQueue_'+ convert(varchar, EQ.EvnQueue_id) as keyNote,
				convert(varchar(8), EQ.EvnQueue_setDT, 112) as sortDate,
				EST.PrehospStatus_id as groupField,
				EQ.EvnDirection_id as EvnDirection_id,
				ED.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),ED.EvnDirection_setDate,104) as EvnDirection_setDate,
				ED.Diag_id as Diag_did,
				ED.LpuSection_id as LpuSection_did,
				ED.Lpu_id as Lpu_did,
				ED.DirType_id as DirType_id,
				isnull(DT.DirType_Name,'') +', '+ isnull(LSP.LpuSectionProfile_Name,'') +', '+ isnull(Diag.Diag_Code,'')+'.'+ isnull(Diag.Diag_Name,'') as Direction_exists,
				null as TimetableStac_setDate,
				null as SMMP_exists,
				null as EvnPS_CodeConv,
				null as TimetableStac_insDT,
				convert(varchar(10), EQ.EvnQueue_setDT, 104) as EvnQueue_setDate,
				1 as IsHospitalized,
				null as EvnPS_id, 
				'' as EvnPS_setDT,
				EQ.TimetableStac_id as TimetableStac_id,
				LSP.LpuSectionProfile_Name as LpuSectionProfile_Name,
				EQ.LpuSectionProfile_did as LpuSectionProfile_did,
				EST.PrehospStatus_id as PrehospStatus_id,
				EST.PrehospStatus_Name as PrehospStatus_Name,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,
				puc.PMUser_Name as pmUser_Name,
				1 as IsRefusal,
				null as PrehospWaifRefuseCause_id,
				1 as IsCall,
				1 as IsSmmp,
				null as PrehospArrive_id,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as Person_age,
				PS.Person_Fio,
				EQ.Person_id,
				EQ.PersonEvn_id,
				EQ.Server_id,
				null as LpuSection_Name,
				PP.PrivilegeType_Name,
				SS.SocStatus_Name
			from v_EvnQueue EQ with (NOLOCK)
				left join v_LpuUnit LU with (NOLOCK) on EQ.LpuUnit_did = LU.LpuUnit_id
				left join v_Person_all PS with (NOLOCK) on EQ.Person_id = PS.Person_id and EQ.PersonEvn_id = PS.PersonEvn_id and EQ.Server_id = PS.Server_id
				left join v_EvnDirection_all ED with (NOLOCK) on EQ.EvnDirection_id = ED.EvnDirection_id
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP with (NOLOCK) on EQ.LpuSectionProfile_did = LSP.LpuSectionProfile_id
				left join v_Diag Diag with (NOLOCK) on isnull(EQ.Diag_id,ED.Diag_id) = Diag.Diag_id
				left join v_PrehospStatus EST with (NOLOCK) on 1 = EST.PrehospStatus_id
				left join pmUserCache puc with (NOLOCK) on EQ.pmUser_insID = puc.pmUser_id
				left join v_PersonPrivilege PP  with (NOLOCK) on PP.Person_id = PS.Person_id
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
				'EvnDirection_'+ convert(varchar, ED.EvnDirection_id) as keyNote,
				convert(varchar(8), coalesce (EvnPS.EvnPS_setDate, TTS.TimetableStac_setDate, ED.EvnDirection_setDT), 112) as sortDate,
				EST.PrehospStatus_id as groupField,
				ED.EvnDirection_id as EvnDirection_id,
				ED.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),ED.EvnDirection_setDate,104) as EvnDirection_setDate,
				ED.Diag_id as Diag_did,
				ED.LpuSection_id as LpuSection_did,
				ED.Lpu_id as Lpu_did,
				ED.DirType_id as DirType_id,
				isnull(DT.DirType_Name,'') +', '+ isnull(LSP.LpuSectionProfile_Name,'') +', '+ isnull(DiagD.Diag_Code,'')+'.'+ isnull(DiagD.Diag_Name,'') as Direction_exists,
				convert(varchar(10), TTS.TimetableStac_setDate, 104) as TimetableStac_setDate,
				null as SMMP_exists,
				null as EvnPS_CodeConv,
				null as TimetableStac_insDT,
				null as EvnQueue_setDate,
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as IsHospitalized,
				EvnPS.EvnPS_id as EvnPS_id,
				convert(varchar(10), EvnPS.EvnPS_setDT, 104) +' '+ convert(varchar(5), EvnPS.EvnPS_setDT, 108) as EvnPS_setDT,--Дата/время поступления;  если есть КВС
				ED.TimetableStac_id as TimetableStac_id,
				LSP.LpuSectionProfile_Name,
				ED.LpuSectionProfile_id as LpuSectionProfile_did,
				EST.PrehospStatus_id as PrehospStatus_id,
				EST.PrehospStatus_Name as PrehospStatus_Name,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,
				puc.PMUser_Name as pmUser_Name, --pmUser_Name Оператор. (направления/бирки, как в АРМ поликлиники)
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as IsRefusal,-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id,
				isnull(EvnPS.EvnPS_IsTransfCall,1) as IsCall, -- Передан активный вызов
				isnull(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp, -- Талон передан на ССМП
				EvnPS.PrehospArrive_id as PrehospArrive_id,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as Person_age,
				PS.Person_Fio,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Server_id,
				LS.LpuSection_Name,
				PP.PrivilegeType_Name,
				SS.SocStatus_Name
			from
				v_EvnDirection_all ED with (NOLOCK)
				left join v_Person_all PS with (NOLOCK) on ED.Person_id = PS.Person_id and ED.PersonEvn_id = PS.PersonEvn_id and ED.Server_id = PS.Server_id
				left join v_EvnPS EvnPS with (NOLOCK) on ED.EvnDirection_id = EvnPS.EvnDirection_id
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP with (NOLOCK) on ED.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_Diag DiagD with (NOLOCK) on ED.Diag_id= DiagD.Diag_id
				left join v_TimetableStac_lite TTS with (NOLOCK) on ED.EvnDirection_id = TTS.EvnDirection_id
				left join v_LpuSection LS with (NOLOCK) on isnull(EvnPS.LpuSection_eid,TTS.LpuSection_id) = LS.LpuSection_id
				left join v_Diag Diag with (NOLOCK) on isnull(EvnPS.Diag_pid,ED.Diag_id) = Diag.Diag_id
				left join v_PrehospStatus EST with (NOLOCK) on isnull(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
				left join pmUserCache puc with (NOLOCK) on isnull (TTS.pmUser_updId, ED.pmUser_insID) = puc.pmUser_id
				left join v_PersonPrivilege PP  with (NOLOCK) on PP.Person_id = PS.Person_id
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
				'EvnPS_'+ convert(varchar, EvnPS.EvnPS_id) as keyNote,
				convert(varchar(8), EvnPS.EvnPS_setDate, 112) as sortDate,
				EST.PrehospStatus_id as groupField,
				null as EvnDirection_id,
				EvnPS.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),EvnPS.EvnDirection_setDT,104) as EvnDirection_setDate,
				EvnPS.Diag_did,
				EvnPS.LpuSection_did,
				EvnPS.Org_did as Lpu_did,
				null as DirType_id,
				null as Direction_exists,
				null as TimetableStac_setDate,
				null as SMMP_exists,
				null as EvnPS_CodeConv,
				null as TimetableStac_insDT,
				null as EvnQueue_setDate,
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as IsHospitalized,
				EvnPS.EvnPS_id as EvnPS_id,
				convert(varchar(10), EvnPS.EvnPS_setDT, 104) +' '+ convert(varchar(5), EvnPS.EvnPS_setDT, 108) as EvnPS_setDT,--Дата/время поступления;  если есть КВС
				null as TimetableStac_id,
				'' as LpuSectionProfile_Name,
				null as LpuSectionProfile_did,
				EST.PrehospStatus_id as PrehospStatus_id,
				EST.PrehospStatus_Name as PrehospStatus_Name,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,--Диагноз приемного; код, наименование
				'' as pmUser_Name,
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as IsRefusal,-- признак наличия отказа
				EvnPS.PrehospWaifRefuseCause_id,
				isnull(EvnPS.EvnPS_IsTransfCall,1) as IsCall, -- Передан активный вызов
				isnull(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp, -- Талон передан на ССМП
				EvnPS.PrehospArrive_id as PrehospArrive_id,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as Person_age,
				PS.Person_Fio,
				EvnPS.Person_id,
				EvnPS.PersonEvn_id,
				EvnPS.Server_id,
				LS.LpuSection_Name as LpuSection_FullName,
				PP.PrivilegeType_Name,
				SS.SocStatus_Name
			from v_EvnPS EvnPS with (NOLOCK)
				left join v_Person_all PS with (NOLOCK) on EvnPS.Person_id = PS.Person_id and EvnPS.PersonEvn_id = PS.PersonEvn_id and EvnPS.Server_id = PS.Server_id
				left join v_LpuSection LS with (NOLOCK) on EvnPS.LpuSection_eid = LS.LpuSection_id
				left join v_Diag Diag with (NOLOCK) on EvnPS.Diag_pid = Diag.Diag_id
				left join v_PrehospStatus EST with (NOLOCK) on EvnPS.PrehospStatus_id = EST.PrehospStatus_id
				left join v_TimetableStac_lite TTS with (NOLOCK) on EvnPS.EvnPS_id = TTS.Evn_id
				left join v_PersonPrivilege PP  with (NOLOCK) on PP.Person_id = PS.Person_id
				left join v_PersonState vPS with(NOLOCK) on vPS.Person_id = EvnPS.Person_id
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
				'TimetableStac_'+ convert(varchar, TTS.TimetableStac_id) as keyNote,
				convert(varchar(8), isnull(EvnPS.EvnPS_setDT,TTS.TimetableStac_setDate), 112) as sortDate,
				EST.PrehospStatus_id as groupField,
				null as EvnDirection_id,
				EvnPS.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),EvnPS.EvnDirection_setDT,104) as EvnDirection_setDate,
				isnull(EvnPS.Diag_did,CmpDiag.Diag_id) as Diag_did,
				EvnPS.LpuSection_did as LpuSection_did,
				EvnPS.Org_did as Lpu_did,
				null as DirType_id,
				null as Direction_exists,
				convert(varchar(10), TTS.TimetableStac_setDate, 104) as TimetableStac_setDate,
				case when TTS.TimetableType_id = 6 then
					isnull(TTSLS.LpuSectionProfile_Name,'')
					+', '+ isnull(CmpDiag.Diag_Code,'') +'.'+ isnull(CmpDiag.Diag_Name,'')
					+', Бригада №'+ isnull(EmD.EmergencyData_BrigadeNum,'')
				else
					null
				end as SMMP_exists,
				isnull(EmD.EmergencyData_CallNum,'') as EvnPS_CodeConv,
				convert(varchar(10), TTS.TimetableStac_updDT, 104) + ' ' + convert(varchar(5), TTS.TimetableStac_updDT, 108) as TimetableStac_insDT,
				null as EvnQueue_setDate,
				case when EvnPS.LpuSection_eid is not null then 2 else 1 end as IsHospitalized,
				EvnPS.EvnPS_id as EvnPS_id,
				convert(varchar(10), EvnPS.EvnPS_setDT, 104) +' '+ convert(varchar(5), EvnPS.EvnPS_setDT, 108) as EvnPS_setDT,
				TTS.TimetableStac_id as TimetableStac_id,
				TTSLS.LpuSectionProfile_Name as LpuSectionProfile_Name,
				TTSLS.LpuSectionProfile_id as LpuSectionProfile_did,
				EST.PrehospStatus_id as PrehospStatus_id,
				EST.PrehospStatus_Name as PrehospStatus_Name,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,
				puc.PMUser_Name as pmUser_Name,
				case when EvnPS.PrehospWaifRefuseCause_id is not null then 2 else 1 end as IsRefusal,
				EvnPS.PrehospWaifRefuseCause_id,
				isnull(EvnPS.EvnPS_IsTransfCall,1) as IsCall,
				isnull(EvnPS.EvnPS_IsPLAmbulance,1) as IsSmmp,
				EvnPS.PrehospArrive_id as PrehospArrive_id,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as Person_age,
				isnull(PS.Person_SurName,'Не идентифицирован') +' '+ isnull(PS.Person_FirName,'') +' '+ isnull(PS.Person_SecName,'') as Person_Fio,
				PS.Person_id,
				isnull(EvnPS.PersonEvn_id,PS.PersonEvn_id) as PersonEvn_id,
				isnull(EvnPS.Server_id,PS.Server_id) as Server_id,
				null as LpuSection_Name,
				PP.PrivilegeType_Name,
				SS.SocStatus_Name
			from v_TimetableStac_lite TTS with (NOLOCK)
				left join v_EvnPS EvnPS with (NOLOCK) on TTS.Evn_id = EvnPS.EvnPS_id
				left join v_LpuSection LS with (NOLOCK) on isnull(EvnPS.LpuSection_eid,TTS.LpuSection_id) = LS.LpuSection_id
				left join v_LpuSection TTSLS with (NOLOCK) on TTS.LpuSection_id = TTSLS.LpuSection_id --and TTSLS.LpuUnitType_id in (1,6,9)
				--left join v_EmergencyData EmD with (NOLOCK) on TTS.EmergencyData_id = EmD.EmergencyData_id
				left join EmergencyData EmD with (NOLOCK) on TTS.EmergencyData_id = EmD.EmergencyData_id
				left join v_PersonState PS with (NOLOCK) on coalesce(EvnPS.Person_id,TTS.Person_id,EmD.Person_lid) = PS.Person_id
				outer apply (
					select top 1
						EmergencyDataStatus_id
					from
						v_EmergencyDataHistory with (nolock)
					where
						EmergencyData_id = EmD.EmergencyData_id
					order by
						EmergencyDataHistory_id desc
				) EDH
				left join v_Diag Diag with (NOLOCK) on isnull(EvnPS.Diag_pid,EmD.Diag_id) = Diag.Diag_id
				--left join v_CmpDiag CmpDiag with (NOLOCK) on EmD.CmpDiag_id = CmpDiag.CmpDiag_id
				left join v_Diag CmpDiag with (NOLOCK) on EmD.Diag_id = CmpDiag.Diag_id
				left join v_PrehospStatus EST with (NOLOCK) on isnull(EvnPS.PrehospStatus_id,1) = EST.PrehospStatus_id
				left join pmUserCache puc with (NOLOCK) on TTS.pmUser_updId = puc.pmUser_id
				left join v_EvnDirection_all ED with (NOLOCK) on TTS.EvnDirection_id = ED.EvnDirection_id
				left join v_PersonPrivilege PP  with (NOLOCK) on PP.Person_id = PS.Person_id
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
				'EvnQueue_'+ convert(varchar, EQ.EvnQueue_id) as keyNote,
				convert(varchar(8), EQ.EvnQueue_setDT, 112) as sortDate,
				EST.PrehospStatus_id as groupField,
				EQ.EvnDirection_id as EvnDirection_id,
				ED.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10),ED.EvnDirection_setDate,104) as EvnDirection_setDate,
				ED.Diag_id as Diag_did,
				ED.LpuSection_id as LpuSection_did,
				ED.Lpu_id as Lpu_did,
				ED.DirType_id as DirType_id,
				isnull(DT.DirType_Name,'') +', '+ isnull(LSP.LpuSectionProfile_Name,'') +', '+ isnull(Diag.Diag_Code,'')+'.'+ isnull(Diag.Diag_Name,'') as Direction_exists,
				null as TimetableStac_setDate,
				null as SMMP_exists,
				null as EvnPS_CodeConv,
				null as TimetableStac_insDT,
				convert(varchar(10), EQ.EvnQueue_setDT, 104) as EvnQueue_setDate,
				1 as IsHospitalized,
				null as EvnPS_id,
				'' as EvnPS_setDT,
				EQ.TimetableStac_id as TimetableStac_id,
				LSP.LpuSectionProfile_Name as LpuSectionProfile_Name,
				EQ.LpuSectionProfile_did as LpuSectionProfile_did,
				EST.PrehospStatus_id as PrehospStatus_id,
				EST.PrehospStatus_Name as PrehospStatus_Name,
				RTRIM(Diag.Diag_Code) + '. ' + RTRIM(Diag.Diag_Name) as Diag_CodeName,
				puc.PMUser_Name as pmUser_Name,
				1 as IsRefusal,
				null as PrehospWaifRefuseCause_id,
				1 as IsCall,
				1 as IsSmmp,
				null as PrehospArrive_id,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_Birthday,dbo.tzGetDate()) as Person_age,
				PS.Person_Fio,
				EQ.Person_id,
				EQ.PersonEvn_id,
				EQ.Server_id,
				null as LpuSection_Name,
				PP.PrivilegeType_Name,
				SS.SocStatus_Name
			from v_EvnQueue EQ with (NOLOCK)
				left join v_LpuUnit LU with (NOLOCK) on EQ.LpuUnit_did = LU.LpuUnit_id
				left join v_Person_all PS with (NOLOCK) on EQ.Person_id = PS.Person_id and EQ.PersonEvn_id = PS.PersonEvn_id and EQ.Server_id = PS.Server_id
				left join v_EvnDirection_all ED with (NOLOCK) on EQ.EvnDirection_id = ED.EvnDirection_id
				left join v_DirType DT with (NOLOCK) on ED.DirType_id = DT.DirType_id
				left join v_LpuSectionProfile LSP with (NOLOCK) on EQ.LpuSectionProfile_did = LSP.LpuSectionProfile_id
				left join v_Diag Diag with (NOLOCK) on isnull(EQ.Diag_id,ED.Diag_id) = Diag.Diag_id
				left join v_PrehospStatus EST with (NOLOCK) on 1 = EST.PrehospStatus_id
				left join pmUserCache puc with (NOLOCK) on EQ.pmUser_insID = puc.pmUser_id
				left join v_PersonPrivilege PP  with (NOLOCK) on PP.Person_id = PS.Person_id
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
?>
