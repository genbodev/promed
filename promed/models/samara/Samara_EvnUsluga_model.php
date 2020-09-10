<?php

require_once(APPPATH.'models/EvnUsluga_model.php');

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
        $accessType = "case when EU.Lpu_id = :Lpu_id /*and ISNULL(EV.EvnVizit_IsInReg, 1) = 1 and ISNULL(ES.EvnSection_IsInReg, 1) = 1*/ " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and ISNULL(EV.MedPersonal_id, ES.MedPersonal_id) in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType,";
        $addSelectclause = '';
        $select_clause = "
             EU.EvnUsluga_id
            ,RTRIM(EU.EvnClass_SysNick) as EvnClass_SysNick
            ,convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate
            ,ISNULL(EU.EvnUsluga_setTime, '') as EvnUsluga_setTime
			,ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code
			,ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name
            ,ROUND(cast(EU.EvnUsluga_Kolvo as float), 2) as EvnUsluga_Kolvo
			,ROUND(cast(EU.EvnUsluga_Price as float), 2) as EvnUsluga_Price
			,ROUND(cast(EU.EvnUsluga_Summa as float), 2) as EvnUsluga_Summa
            ,PT.PayType_id
            ,ISNULL(PT.PayType_SysNick, '') as PayType_SysNick
            ,MOT.MesOperType_Name as MesOperType_Name
            ,EU.EvnUsluga_id as EvnUsluga_PriceKoef
            ,dbo.GetEvnUslugaPrice(EU.EvnUsluga_id) * EU.EvnUsluga_CoeffTariff as EvnUsluga_PriceKoef
			,dbo.GetEvnUslugaLpuSectionProfileCode(EU.EvnUsluga_id) as EvnUsluga_LpuSectionProfileCode
        ";
        $from_clause = "
            v_EvnUsluga EU with (nolock)
			left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
			left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
            left join v_EvnVizit EV with (nolock) on EV.EvnVizit_id = EU.EvnUsluga_pid
            left join v_EvnSection ES with (nolock) on ES.EvnSection_id = EU.EvnUsluga_pid
			left join v_PayType PT with (nolock) on PT.PayType_id = EU.PayType_id
			left join MesOperType MOT with (nolock) on MOT.MesOperType_id = EU.MesOperType_id
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
				and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
			";
		}

        switch ( $data['class'] ) {
			case 'EvnUslugaStom':
                $select_clause = "
						EU.EvnUslugaStom_id as EvnUsluga_id,
						EU.EvnUslugaStom_pid as EvnUsluga_pid,
						convert(varchar(10), EU.EvnUslugaStom_setDate, 104) as EvnUsluga_setDate,
						ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code,
						ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name,
						ROUND(cast(EU.EvnUslugaStom_Kolvo as float), 2) as EvnUsluga_Kolvo,
						ROUND(cast(EU.EvnUslugaStom_Price as float), 2) as EvnUsluga_Price,
						ROUND(cast(EU.EvnUslugaStom_Summa as float), 2) as EvnUsluga_Summa,
						PT.PayType_id,
						ISNULL(PT.PayType_SysNick, '') as PayType_SysNick
                ";
                $from_clause = "
					    v_EvnUslugaStom EU with (nolock)
						left join Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
						left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
						left join EvnVizit EV with (nolock) on EV.EvnVizit_id = EU.EvnUslugaStom_pid
						left join EvnSection ES with (nolock) on ES.EvnSection_id = EU.EvnUslugaStom_pid
						left join v_PayType PT with (nolock) on PT.PayType_id = EU.PayType_id
                ";
                $where_clause = "
					    (1 = 1)
						and EU.EvnUslugaStom_pid = :pid
						and (EU.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
                ";

				if ( in_array($data['session']['region']['nick'], array('ufa', 'pskov', 'ekb')) ) {
					$where_clause .= "
						and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
					";
				}
			break;
			//case 'EvnUslugaOper':
            case 'EvnUslugaOnkoChem':
            case 'EvnUslugaOnkoBeam':
	        case 'EvnUslugaOnkoGormun':
	        case 'EvnUslugaOnkoSurg':
				$from_clause = "
					v_EvnUsluga_all EU with (nolock)
					left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
					left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
					left join EvnUslugaOnkoChem OnkoChem with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem' and OnkoChem.EvnUslugaOnkoChem_id = EU.EvnUsluga_id
					left join v_OnkoUslugaChemKindType ChemKindType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem' and ChemKindType.OnkoUslugaChemKindType_id = OnkoChem.OnkoUslugaChemKindType_id
					left join v_OnkoUslugaChemFocusType ChemFocusType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem' and ChemFocusType.OnkoUslugaChemFocusType_id = OnkoChem.OnkoUslugaChemFocusType_id
					left join EvnUslugaOnkoGormun OnkoGormun with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoGormun' and OnkoGormun.EvnUslugaOnkoGormun_id = EU.EvnUsluga_id
					left join v_OnkoUslugaGormunFocusType GormunFocusType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoGormun' and GormunFocusType.OnkoUslugaGormunFocusType_id = OnkoGormun.OnkoUslugaGormunFocusType_id
					left join EvnUslugaOnkoBeam OnkoBeam with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeam.EvnUslugaOnkoBeam_id = EU.EvnUsluga_id
					left join v_OnkoUslugaBeamKindType OnkoBeamKindType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamKindType.OnkoUslugaBeamKindType_id = OnkoBeam.OnkoUslugaBeamKindType_id
					left join v_OnkoUslugaBeamRadioModifType OnkoBeamRadioModifType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamRadioModifType.OnkoUslugaBeamRadioModifType_id = OnkoBeam.OnkoUslugaBeamRadioModifType_id
					left join v_OnkoUslugaBeamMethodType OnkoBeamMethodType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamMethodType.OnkoUslugaBeamMethodType_id = OnkoBeam.OnkoUslugaBeamMethodType_id
					left join v_OnkoUslugaBeamIrradiationType OnkoBeamIrradiationType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and OnkoBeamIrradiationType.OnkoUslugaBeamIrradiationType_id = OnkoBeam.OnkoUslugaBeamIrradiationType_id
					left join v_OnkoUslugaBeamFocusType BeamFocusType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoBeam' and BeamFocusType.OnkoUslugaBeamFocusType_id = OnkoBeam.OnkoUslugaBeamFocusType_id
					left join v_Lpu Lpu with (nolock) on EU.Lpu_uid = Lpu.Lpu_id
					left join EvnUslugaOnkoSurg OnkoSurg with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg' and OnkoSurg.EvnUslugaOnkoSurg_id = EU.EvnUsluga_id
					left join v_OperType OperType with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg' and OnkoSurg.OperType_id = OperType.OperType_id
					left join v_MedPersonal MP with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoSurg' and EU.MedPersonal_id = MP.MedPersonal_id and EU.Lpu_uid = MP.Lpu_id
				";
				$select_clause = "
				 EU.EvnUsluga_id
				,RTRIM(EU.EvnClass_SysNick) as EvnClass_SysNick
				,convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate
				,ISNULL(EU.EvnUsluga_setTime, '') as EvnUsluga_setTime
				,ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code
				,ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name
				,convert(varchar(10), EU.EvnUsluga_disDate, 104) as EvnUsluga_disDate,
				EU.Person_id,
				EU.EvnUsluga_pid,
				CASE EU.UslugaPlace_id
					WHEN 1 THEN 	--1	Отделение ЛПУ
						(SELECT TOP 1 s.LpuSection_Name FROM dbo.v_LpuSection s WHERE s.LpuSection_id = EU.LpuSection_uid)
					WHEN 2 THEN 	--2	Другое ЛПУ
						(SELECT TOP 1 l.Lpu_Nick FROM v_lpu l WHERE l.Lpu_id = eu.Lpu_uid)
					WHEN 3 THEN		--3	Другая организация
						(SELECT TOP 1 o.Org_Nick FROM v_org o WHERE o.Org_id = eu.Org_uid)
				END AS place_name,
				Lpu.Lpu_Nick as Lpu_Name,
				MP.Person_Fio as MedPersonal_Name,
				OperType.OperType_Name,
				ChemKindType.OnkoUslugaChemKindType_Name,
				OnkoGormun.EvnUslugaOnkoGormun_IsBeam,
				OnkoGormun.EvnUslugaOnkoGormun_IsSurg,
				OnkoGormun.EvnUslugaOnkoGormun_IsDrug,
				OnkoGormun.EvnUslugaOnkoGormun_IsOther,
				coalesce(GormunFocusType.OnkoUslugaGormunFocusType_Name,ChemFocusType.OnkoUslugaChemFocusType_Name,BeamFocusType.OnkoUslugaBeamFocusType_Name) as FocusType_Name,
				OnkoBeamIrradiationType.OnkoUslugaBeamIrradiationType_Name,
				OnkoBeamKindType.OnkoUslugaBeamKindType_Name,
				OnkoBeamMethodType.OnkoUslugaBeamMethodType_Name,
				OnkoBeamRadioModifType.OnkoUslugaBeamRadioModifType_Name";
				if(!empty($data['EvnEdit_id']))
				{
					$p['EvnEdit_id'] = $data['EvnEdit_id'];
					$accessType = 'case when EU.Lpu_id = :Lpu_id and M.Morbus_disDT is null and (';
					//условие при редактировании из регистра
					$accessType .= '(EU.EvnUsluga_pid is null and EU.Person_id = :EvnEdit_id)';
					$accessType .= ' or ';
					//условие при редактировании из учетного документа
					$accessType .= '(ISNULL(EV.EvnVizit_IsInReg, 1) = 1 and ISNULL(ES.EvnSection_IsInReg, 1) = 1 and EU.EvnUsluga_pid = :EvnEdit_id and isnull(EvnEdit.Evn_IsSigned,1) = 1'. ($data['session']['isMedStatUser'] == false && isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? ' and ISNULL(EV.MedPersonal_id, ES.MedPersonal_id) = ' . $data['session']['medpersonal_id'] : '') .')';
					$accessType .= ") then 'edit' else 'view' end as accessType,";
					$addSelectclause .= '
					,EU.Morbus_id
					,:EvnEdit_id as MorbusOnko_pid';
					$from_clause .= '
					left join v_Morbus M with (nolock) on M.Morbus_id = EU.Morbus_id
					left join v_Evn EvnEdit with (nolock) on EvnEdit.Evn_id = :EvnEdit_id and EU.Person_id != :EvnEdit_id';
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
?>
