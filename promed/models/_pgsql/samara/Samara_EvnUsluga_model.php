<?php

require_once(APPPATH.'models/_pgsql/EvnUsluga_model.php');

class Samara_EvnUsluga_model extends EvnUsluga_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
    }

	/**
	 * Получение данных для грида
	 */
	function loadEvnUslugaGrid($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		// todo: условие надо править
        /* убрал запрет редактирования услуг в зависимости от падания в реестры и проч
        (пока реплика обратная нормально не заработает) #16671 */
        $accessType = "case when EU.Lpu_id = :Lpu_id " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "
        and coalesce(EV.MedPersonal_id, ES.MedPersonal_id) in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as \"accessType\",";
        $addSelectclause = '';
        $select_clause = "
             EU.EvnUsluga_id as \"EvnUsluga_id\"
            ,RTRIM(EU.EvnClass_SysNick) as \"EvnClass_SysNick\"
            ,to_char(EU.EvnUsluga_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\"
            ,coalesce(EU.EvnUsluga_setTime, '') as \"EvnUsluga_setTime\"
			,coalesce(Usluga.Usluga_Code, UC.UslugaComplex_Code) as \"Usluga_Code\"
			,coalesce(Usluga.Usluga_Name, UC.UslugaComplex_Name) as \"Usluga_Name\"
            ,ROUND(cast(EU.EvnUsluga_Kolvo as float), 2) as \"EvnUsluga_Kolvo\"
			,ROUND(cast(EU.EvnUsluga_Price as float), 2) as \"EvnUsluga_Price\"
			,ROUND(cast(EU.EvnUsluga_Summa as float), 2) as \"EvnUsluga_Summa\"
            ,PT.PayType_id as \"PayType_id\"
            ,coalesce(PT.PayType_SysNick, '') as \"PayType_SysNick\"
            ,MOT.MesOperType_Name as \"MesOperType_Name\"
            ,EU.EvnUsluga_id as \"EvnUsluga_PriceKoef\"
            ,dbo.GetEvnUslugaPrice(EU.EvnUsluga_id) * EU.EvnUsluga_CoeffTariff as \"EvnUsluga_PriceKoef\"
			,dbo.GetEvnUslugaLpuSectionProfileCode(EU.EvnUsluga_id) as \"EvnUsluga_LpuSectionProfileCode\"
        ";
        $from_clause = "
            v_EvnUsluga EU
				left join v_Usluga Usluga on Usluga.Usluga_id = EU.Usluga_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_EvnVizit EV on EV.EvnVizit_id = EU.EvnUsluga_pid
				left join v_EvnSection ES on ES.EvnSection_id = EU.EvnUsluga_pid
				left join v_PayType PT on PT.PayType_id = EU.PayType_id
				left join MesOperType MOT on MOT.MesOperType_id = EU.MesOperType_id
        ";
		$p = array(
			'pid' => $data['pid'],
			'Lpu_id' => $data['Lpu_id']
		);
		if (isset($data['byMorbus']) && $data['byMorbus']) {
            if (isset($data['Morbus_id']) && (!empty($data['Morbus_id']))) {
				$evnFilter = "and EU.Morbus_id = :Morbus_id";
				$p['Morbus_id'] = $data['Morbus_id'];
			} else {
				$evnFilter = "and EU.Morbus_id = (SELECT morbus_id FROM dbo.v_Evn WHERE evn_id = :pid AND Morbus_id IS NOT null)";
			}
        } else {
            $evnFilter = "and (:pid in (EU.EvnUsluga_pid, EU.EvnUsluga_rid))";
        }
        $where_clause = "
            (1 = 1)
            $evnFilter
            and (EU.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
            and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper', 'EvnUslugaStom', 'EvnUslugaOnkoChem', 'EvnUslugaOnkoBeam', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoSurg')
        ";

		if ( in_array($data['session']['region']['nick'], array('ufa', 'pskov', 'ekb')) ) {
			$where_clause .= "
				and coalesce(EU.EvnUsluga_IsVizitCode, 1) = 1
			";
		}

        switch ( $data['class'] ) {
			case 'EvnUslugaStom':
                $select_clause = "
						EU.EvnUslugaStom_id as \"EvnUsluga_id\",
						EU.EvnUslugaStom_pid as \"EvnUsluga_pid\",
						to_char(EU.EvnUslugaStom_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\",
						coalesce(Usluga.Usluga_Code, UC.UslugaComplex_Code) as \"Usluga_Code\",
						coalesce(Usluga.Usluga_Name, UC.UslugaComplex_Name) as \"Usluga_Name\",
						ROUND(cast(EU.EvnUslugaStom_Kolvo as float), 2) as \"EvnUsluga_Kolvo\",
						ROUND(cast(EU.EvnUslugaStom_Price as float), 2) as \"EvnUsluga_Price\",
						ROUND(cast(EU.EvnUslugaStom_Summa as float), 2) as \"EvnUsluga_Summa\",
						PT.PayType_id as \"PayType_id\",
						coalesce(PT.PayType_SysNick, '') as \"PayType_SysNick\"
                ";
                $from_clause = "
					    v_EvnUslugaStom EU
							left join Usluga on Usluga.Usluga_id = EU.Usluga_id
							left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
							left join EvnVizit EV on EV.EvnVizit_id = EU.EvnUslugaStom_pid
							left join EvnSection ES on ES.EvnSection_id = EU.EvnUslugaStom_pid
							left join v_PayType PT on PT.PayType_id = EU.PayType_id
                ";
                $where_clause = "
					    (1 = 1)
						and EU.EvnUslugaStom_pid = :pid
						and (EU.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
                ";

				if ( in_array($data['session']['region']['nick'], array('ufa', 'pskov', 'ekb')) ) {
					$where_clause .= "
						and coalesce(EU.EvnUsluga_IsVizitCode, 1) = 1
					";
				}
			break;
			//case 'EvnUslugaOper':
            case 'EvnUslugaOnkoChem':
            case 'EvnUslugaOnkoBeam':
	        case 'EvnUslugaOnkoGormun':
	        case 'EvnUslugaOnkoSurg':
				$from_clause = "
					v_EvnUsluga_all EU
						left join v_Usluga Usluga on Usluga.Usluga_id = EU.Usluga_id
						left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
						left join EvnUslugaOnkoChem OnkoChem on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem'
							and OnkoChem.EvnUslugaOnkoChem_id = EU.EvnUsluga_id
						left join v_OnkoUslugaChemKindType ChemKindType on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem'
							and ChemKindType.OnkoUslugaChemKindType_id = OnkoChem.OnkoUslugaChemKindType_id
						left join v_OnkoUslugaChemFocusType ChemFocusType on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem'
							and ChemFocusType.OnkoUslugaChemFocusType_id = OnkoChem.OnkoUslugaChemFocusType_id
						left join EvnUslugaOnkoGormun OnkoGormun on EU.EvnClass_SysNick = 'EvnUslugaOnkoGormun'
							and OnkoGormun.EvnUslugaOnkoGormun_id = EU.EvnUsluga_id
						left join v_OnkoUslugaGormunFocusType GormunFocusType on EU.EvnClass_SysNick = 'EvnUslugaOnkoGormun'
							and GormunFocusType.OnkoUslugaGormunFocusType_id = OnkoGormun.OnkoUslugaGormunFocusType_id
						left join EvnUslugaOnkoBeam OnkoBeam on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam'
							and OnkoBeam.EvnUslugaOnkoBeam_id = EU.EvnUsluga_id
						left join v_OnkoUslugaBeamKindType OnkoBeamKindType on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam'
							and OnkoBeamKindType.OnkoUslugaBeamKindType_id = OnkoBeam.OnkoUslugaBeamKindType_id
						left join v_OnkoUslugaBeamRadioModifType OnkoBeamRadioModifType on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam'
							and OnkoBeamRadioModifType.OnkoUslugaBeamRadioModifType_id = OnkoBeam.OnkoUslugaBeamRadioModifType_id
						left join v_OnkoUslugaBeamMethodType OnkoBeamMethodType on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam'
							and OnkoBeamMethodType.OnkoUslugaBeamMethodType_id = OnkoBeam.OnkoUslugaBeamMethodType_id
						left join v_OnkoUslugaBeamIrradiationType OnkoBeamIrradiationType on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam'
							and OnkoBeamIrradiationType.OnkoUslugaBeamIrradiationType_id = OnkoBeam.OnkoUslugaBeamIrradiationType_id
						left join v_OnkoUslugaBeamFocusType BeamFocusType on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam'
							and BeamFocusType.OnkoUslugaBeamFocusType_id = OnkoBeam.OnkoUslugaBeamFocusType_id
						left join v_Lpu Lpu on EU.Lpu_uid = Lpu.Lpu_id
						left join EvnUslugaOnkoSurg OnkoSurg on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg'
							and OnkoSurg.EvnUslugaOnkoSurg_id = EU.EvnUsluga_id
						left join v_OperType OperType on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg'
							and OnkoSurg.OperType_id = OperType.OperType_id
						left join v_MedPersonal MP on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg'
							and EU.MedPersonal_id = MP.MedPersonal_id
							and EU.Lpu_uid = MP.Lpu_id
				";
				$select_clause = "
					 EU.EvnUsluga_id as \"EvnUsluga_id\"
					,RTRIM(EU.EvnClass_SysNick) as \"EvnClass_SysNick\"
					,to_char(EU.EvnUsluga_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\"
					,coalesce(EU.EvnUsluga_setTime, '') as \"EvnUsluga_setTime\"
					,coalesce(Usluga.Usluga_Code, UC.UslugaComplex_Code) as \"Usluga_Code\"
					,coalesce(Usluga.Usluga_Name, UC.UslugaComplex_Name) as \"Usluga_Name\"
					,to_char(EU.EvnUsluga_disDate, 'dd.mm.yyyy') as \"EvnUsluga_disDate\",
					EU.Person_id as \"Person_id\",
					EU.EvnUsluga_pid as \"EvnUsluga_pid\",
					CASE EU.UslugaPlace_id
						WHEN 1 THEN 	--1	Отделение ЛПУ
							(SELECT s.LpuSection_Name FROM dbo.v_LpuSection s WHERE s.LpuSection_id = EU.LpuSection_uid limit 1)
						WHEN 2 THEN 	--2	Другое ЛПУ
							(SELECT l.Lpu_Nick FROM v_lpu l WHERE l.Lpu_id = eu.Lpu_uid limit 1)
						WHEN 3 THEN		--3	Другая организация
							(SELECT o.Org_Nick FROM v_org o WHERE o.Org_id = eu.Org_uid limit 1)
					END AS \"place_name\",
					Lpu.Lpu_Nick as \"pu_Name\",
					MP.Person_Fio as \"MedPersonal_Name\",
					OperType.OperType_Name as \"OperType_Name\",
					ChemKindType.OnkoUslugaChemKindType_Name as \"OnkoUslugaChemKindType_Name\",
					OnkoGormun.EvnUslugaOnkoGormun_IsBeam as \"EvnUslugaOnkoGormun_IsBeam\",
					OnkoGormun.EvnUslugaOnkoGormun_IsSurg as \"EvnUslugaOnkoGormun_IsSurg\",
					OnkoGormun.EvnUslugaOnkoGormun_IsDrug as \"EvnUslugaOnkoGormun_IsDrug\",
					OnkoGormun.EvnUslugaOnkoGormun_IsOther as \"EvnUslugaOnkoGormun_IsOther\",
					coalesce(GormunFocusType.OnkoUslugaGormunFocusType_Name,ChemFocusType.OnkoUslugaChemFocusType_Name,BeamFocusType.OnkoUslugaBeamFocusType_Name) as \"FocusType_Name\",
					OnkoBeamIrradiationType.OnkoUslugaBeamIrradiationType_Name as \"OnkoUslugaBeamIrradiationType_Name\",
					OnkoBeamKindType.OnkoUslugaBeamKindType_Name as \"OnkoUslugaBeamKindType_Name\",
					OnkoBeamMethodType.OnkoUslugaBeamMethodType_Name as \"OnkoUslugaBeamMethodType_Name\",
					OnkoBeamRadioModifType.OnkoUslugaBeamRadioModifType_Name as \"OnkoUslugaBeamRadioModifType_Name\"
				";
				if(!empty($data['EvnEdit_id']))
				{
					$p['EvnEdit_id'] = $data['EvnEdit_id'];
					$accessType = 'case when EU.Lpu_id = :Lpu_id and M.Morbus_disDT is null and (';
					//условие при редактировании из регистра
					$accessType .= '(EU.EvnUsluga_pid is null and EU.Person_id = :EvnEdit_id)';
					$accessType .= ' or ';
					//условие при редактировании из учетного документа
					$accessType .= '(coalesce(EV.EvnVizit_IsInReg, 1) = 1 and coalesce(ES.EvnSection_IsInReg, 1) = 1 and EU.EvnUsluga_pid = :EvnEdit_id and coalesce(EvnEdit.Evn_IsSigned,1) = 1'. ($data['session']['isMedStatUser'] == false && isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? ' and coalesce(EV.MedPersonal_id, ES.MedPersonal_id) = ' . $data['session']['medpersonal_id'] : '') .')';
					$accessType .= ") then 'edit' else 'view' end as \"accessType\",";
					$addSelectclause .= '
					,EU.Morbus_id as "Morbus_id"
					,:EvnEdit_id as "MorbusOnko_pid"';
					$from_clause .= '
					left join v_Morbus M on M.Morbus_id = EU.Morbus_id
					left join v_Evn EvnEdit on EvnEdit.Evn_id = :EvnEdit_id and EU.Person_id != :EvnEdit_id';
				}
                $where_clause = "
					    (1 = 1)
						$evnFilter
						and (EU.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
						and EU.EvnClass_SysNick in ('{$data['class']}')
				";
                break;
		}
        $query = "
            select
              $accessType
              $select_clause
              $addSelectclause
            from
              $from_clause
            where
              $where_clause
        ";
		if (false && $data['class'] == 'EvnUslugaOnkoSurg') {
			echo getDebugSQL($query, $p); exit;
		}
		$result = $this->db->query($query, $p);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
