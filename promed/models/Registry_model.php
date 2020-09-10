<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Registry_model - модель для работы с таблицей Registry
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Bykov Stas aka Savage (savage@swan.perm.ru)
 * @version      12.11.2009
 */

class Registry_model extends swModel {
    var $scheme = "dbo";
	var $region = "perm";
	var $RegistryType_id = null;
	var $RegistryDataObject = 'RegistryData';
	var $RegistryDataSLObject = 'RegistryDataSL';
	var $RegistryDataTempObject = 'RegistryDataTmp';
	var $RegistryErrorComObject = 'RegistryErrorCom';
	var $RegistryErrorObject = 'RegistryError';
	var $RegistryDataEvnField = 'Evn_id';
	var $RegistryPersonObject = 'RegistryPerson';
	var $RegistryDoubleObject = 'RegistryDouble';
	var $RegistryNoPolisObject = 'RegistryNoPolis';
	var $upload_path = 'RgistryFields/';
	var $MaxEvnField = "Evn_id";

	protected $_unionRegistryTypes = [];

	// Массив для хранения связок Registry_id => RegistryType_id
	// Используется для метода setRegistryParamsByType, т.к. в некоторых случаях приходится много раз менять параметры реестра,
	// например, при импорте ошибок ТФОМС по объединенному реестру
	protected $_registryTypesList = array();

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Корректировка ошибок на реестрах со смещением
	 */
	function correctErrors($data) {
		// для начала достанем Registry_EvnNum
		$resp = $this->getFirstResultFromQuery("
			select top 1
				Registry_EvnNum
			from
				{$this->scheme}.Registry (nolock)
			where
				Registry_id = :Registry_id
		", [
			'Registry_id' => $data['Registry_id']
		]);
		if (empty($resp)) {
			return ['Error_Msg' => 'Не заполнен Registry_EvnNum, корректировка невозможна'];
		}

		$Registry_EvnNum = json_decode($resp, true);

		// Достаём все ошибки по объед. реестру и обновляем им Registry_id / Evn_id
		$query = "
			select
				RegistryErrorTFOMS_id,
				RegistryErrorTFOMS_IdCase
			from
				{$this->scheme}.RegistryErrorTFOMS with (nolock)
			where
				Registry_id = :Registry_id

			union all

			select
				RegistryErrorTFOMS_id,
				RegistryErrorTFOMS_IdCase
			from
				{$this->scheme}.RegistryErrorTFOMS with (nolock)
			where
				Registry_id IN (select Registry_id from v_RegistryGroupLink (nolock) where Registry_pid = :Registry_id)
		";

		$resp = $this->queryResult($query, [
			'Registry_id' => $data['Registry_id']
		]);

		foreach($resp as $respone) {
			$respone['CmpCallCard_id'] = null;
			$respone['Evn_id'] = null;

			// цепляем новые данные
			$newNum = $respone['RegistryErrorTFOMS_IdCase'] - $data['offset'] + 1;

			if (!empty($Registry_EvnNum[$newNum])) {
				$respone['Registry_id'] = $Registry_EvnNum[$newNum]['Registry_id'];

				if (
					!isset($this->_registryTypesList[$respone['Registry_id']])
					|| $this->RegistryType_id != $this->_registryTypesList[$respone['Registry_id']]
				) {
					$this->setRegistryParamsByType(['Registry_id' => $respone['Registry_id']], true);
				}

				$respone[$this->RegistryDataEvnField] = $Registry_EvnNum[$newNum]['Evn_id'];
			}
			else {
				$respone['Registry_id'] = $data['Registry_id'];
			}

			// апдейтим
			$query = "
				update RegistryErrorTFOMS with (rowlock)
				set
					Registry_id = :Registry_id,
					Evn_id = :Evn_id,
					CmpCallCard_id = :CmpCallCard_id
				where RegistryErrorTFOMS_id = :RegistryErrorTFOMS_id
			";
			$this->db->query($query, [
				'Registry_id' => $respone['Registry_id'],
				'Evn_id' => $respone['Evn_id'],
				'CmpCallCard_id' => $respone['CmpCallCard_id'],
				'RegistryErrorTFOMS_id' => $respone['RegistryErrorTFOMS_id']
			]);
		}

		// Достаём все ошибки по объед. реестру и обновляем им Registry_id / Evn_id
		$query = "
			select
				RegistryErrorBDZ_id,
				RegistryErrorBDZ_IdCase
			from
				{$this->scheme}.RegistryErrorBDZ with (nolock)
			where
				Registry_id = :Registry_id

			union all

			select
				RegistryErrorBDZ_id,
				RegistryErrorBDZ_IdCase
			from
				{$this->scheme}.RegistryErrorBDZ with (nolock)
			where
				Registry_id IN (select Registry_id from v_RegistryGroupLink (nolock) where Registry_pid = :Registry_id)
		";

		$resp = $this->queryResult($query, [
			'Registry_id' => $data['Registry_id']
		]);

		foreach($resp as $respone) {
			$respone['CmpCallCard_id'] = null;
			$respone['Evn_id'] = null;

			// цепляем новые данные
			$newNum = $respone['RegistryErrorBDZ_IdCase'] - $data['offset'] + 1;

			if (!empty($Registry_EvnNum[$newNum])) {
				$respone['Registry_id'] = $Registry_EvnNum[$newNum]['Registry_id'];

				if (
					!isset($this->_registryTypesList[$respone['Registry_id']])
					|| $this->RegistryType_id != $this->_registryTypesList[$respone['Registry_id']]
				) {
					$this->setRegistryParamsByType(['Registry_id' => $respone['Registry_id']], true);
				}

				$respone[$this->RegistryDataEvnField] = $Registry_EvnNum[$newNum]['Evn_id'];
			}
			else {
				$respone['Registry_id'] = $data['Registry_id'];
			}

			// апдейтим
			$query = "
				update RegistryErrorBDZ with (rowlock)
				set
					Registry_id = :Registry_id,
					Evn_id = :Evn_id,
					CmpCallCard_id = :CmpCallCard_id
				where RegistryErrorBDZ_id = :RegistryErrorBDZ_id
			";
			$this->db->query($query, [
				'Registry_id' => $respone['Registry_id'],
				'Evn_id' => $respone['Evn_id'],
				'CmpCallCard_id' => $respone['CmpCallCard_id'],
				'RegistryErrorBDZ_id' => $respone['RegistryErrorBDZ_id']
			]);
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Возвращает тип переданного регистра
	 */
	function getRegistryType($data)
	{
		if ($data['Registry_id']==0)
		{
			return false;
		}

		$query = "
			select top 1
				R.RegistryType_id
			from
				{$this->scheme}.v_Registry R with (NOLOCK)
			where R.Registry_id = :Registry_id
		";
 		$result = $this->db->query($query, $data);


		if (is_object($result)) {
			$res_arr = $result->result('array');
			if (is_array($res_arr) && count($res_arr) == 1) {
				return $res_arr[0];
			}
		}
		return false;
	}
	/**
	 *	Получение списка случаев реестра
	 */
	public function loadRegistryData($data)
	{
		if ($data['Registry_id']==0)
		{
			return false;
		}
		if (isset($data['RegistryType_id']) && $data['RegistryType_id']==0)
		{
			return false;
		}

		if ((isset($data['start']) && (isset($data['limit'])))&&(!(($data['start'] >= 0) && ($data['limit'] >= 0))))
		{
			return false;
		}
		$filterAddQueryTemp = null;
		$filterAddQuery = "";
		$returnQueryOnly = isset($data['returnQueryOnly']);
		if(isset($data['Filter']) && in_array($this->regionNick, array('buryatiya','kaluga','kareliya','penza'))){
			$filterData = json_decode(toUTF(trim($data['Filter'],'"')), 1);

			if(is_array($filterData)){

				foreach($filterData as $column=>$value){

					if(is_array($value)){
						$r = null;

						foreach($value as $d){
							$r .= "'".trim(toAnsi($d))."',";
						}

						if($column == 'Diag_Code')
							$column = 'D.'.$column;
						elseif($column == 'EvnPL_NumCard')
							$column = 'RD.NumCard';
						elseif($column == 'LpuSection_name')
							$column = 'RD.'.$column;
						elseif($column == 'LpuBuilding_Name')
							$column = 'LB.'.$column;
						elseif($column == 'Usluga_Code')
							$column = ($data['RegistryType_id'] != 1) ? 'U.UslugaComplex_Code' : 'm.Mes_Code';
						elseif($column == 'Paid')
							$column = 'RD.Paid_id';
						elseif($column == 'Evn_id')
							$column = 'RD.Evn_id';

						$r = rtrim($r, ',');
						$filterAddQueryTemp[] = $column.' IN ('.$r.')';

					}
				}
			}

			if(is_array($filterAddQueryTemp)){
				$filterAddQuery = "and ".implode(" and ", $filterAddQueryTemp);
			}
			else
				$filterAddQuery = "";
		}

		$filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;
		$this->setRegistryParamsByType($data);

		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id'=>$data['session']['lpu_id']
		);
		$filter="(1=1)";
		if (isset($data['Person_SurName']))
		{
			$filter .= " and RD.Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = rtrim($data['Person_SurName'])."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and RD.Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = rtrim($data['Person_FirName'])."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and RD.Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = rtrim($data['Person_SecName'])."%";
		}
		if(!empty($data['Polis_Num'])) {
			$filter .= " and RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}
		
		if(!empty($data['MedPersonal_id'])) {
			$filter .= " and RD.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if (!empty($data['LpuSection_id']))
		{
			$filter .= " and RD.LpuSection_id = :LpuSection_id ";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (!empty($data['LpuSectionProfile_id']))
		{
			$filter .= " and LS.LpuSectionProfile_id = :LpuSectionProfile_id ";
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if (!empty($data['NumCard']))
		{
			$filter .= " and RD.NumCard = :NumCard ";
			$params['NumCard'] = $data['NumCard'];
		}

		if(!empty($data['Evn_id'])) {
			$filter .= " and RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}
		
		$join = "";
		$fields = "";

		if($this->regionNick == 'buryatiya' && $data['RegistryType_id'] == 15){
			$join .= "left join v_EvnUslugaPar EUP (nolock) on EUP.EvnUslugaPar_id = RD.Evn_id ";
			$join .= "left join v_EvnFuncRequest efr (nolock) on efr.EvnFuncRequest_pid = EUP.EvnDirection_id ";
			$join .= "left join v_EvnLabRequest  elr (nolock) on elr.EvnDirection_id = EUP.EvnDirection_id ";
			$fields .= "case when efr.EvnFuncRequest_id is not null then 'true' else 'false' end as isEvnFuncRequest, ";
			$fields .= "case when elr.EvnLabRequest_id is not null then 'true' else 'false' end as isEvnLabRequest, ";
			$fields .= "elr.MedService_id, ";
			$fields .= "EUP.EvnDirection_id, ";
			$fields .= "e.EvnClass_SysNick, ";
		}

		//if (!in_array($this->regionNick, array('ufa','pskov')))
		if (!in_array($data['session']['region']['nick'], array('ufa','pskov','buryatiya','penza','vologda')))
		{
			$join = "
				outer apply (
					select top 1 RDLT.Person_id from RegistryDataLgot RDLT with (NOLOCK) where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
				) RDL
			";
			$join .= "left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id ";
			$fields .= "case when RDL.Person_id is null then 0 else 1 end as IsRDL, ";
			$fields .= "RD.needReform, RD.checkReform, RD.timeReform, ";
			$fields .= "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit, ";
		}
		else
		{
		
			if(!empty($data['LpuBuilding_id'])) {
				$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}

			if($this->regionNick == 'buryatiya'){
				$joinMes="
					left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
					left join v_MesOld m (nolock) on m.Mes_id = mt.Mes_id
				";
			}else{
				$joinMes="
					left join v_MesOld m (nolock) on m.Mes_id = ES.Mes_id
				";
			}

			$join .= "
				left join v_UslugaComplex U with (NOLOCK) on ISNULL(RD.Usluga_id, RD.UslugaComplex_id) = U.UslugaComplex_id -- связь стала с UslugaComplex_id (refs #13509)
				left join v_Diag D with (NOLOCK) on RD.Diag_id =  D.Diag_id
				left join v_EvnSection es (nolock) on ES.EvnSection_id = RD.Evn_id
				{$joinMes}
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			";
			$fields .= "
				m.Mes_Code + ' ' + m.MesOld_Num as Mes_Code,
				U.UslugaComplex_Code as Usluga_Code,
			";
			$fields .= "D.Diag_Code, case when RD.Paid_id = 1 then 'Нет' when RD.Paid_id = 2 then 'Да' else '' end as Paid, ";
			$fields .= "LB.LpuBuilding_Name, ";

			if ($this->regionNick == 'ufa') {
				$fields .= "Mes.Mes_Code + ISNULL(' ' + Mes.MesOld_Num, '') as Mes_Code_KSG, RD.Mes_Code_KPG, ";
				$join .= "
					left join v_MesOld Mes with (nolock) on Mes.Mes_id = rd.MesItog_id
				";
				if ($this->RegistryDataObject == 'RegistryDataEvnPS') {
					$fields .= "htm.HTMedicalCareClass_GroupCode, ";
					$fields .= "htm.HTMedicalCareClass_Name, ";
					$join .= "
						left join v_HTMedicalCareClass htm (nolock) on htm.HTMedicalCareClass_id = rd.HTMedicalCareClass_id
					";
					
					$join .= " left join v_VolumeType vt2 (nolock) on vt2.VolumeType_id = rd.VolumeType_sid ";
					$fields .= "case when rd.RegistryDataEvnPS_IsBadVolSec = 2 then '<b>' + vt2.VolumeType_Code + '</b>' else vt2.VolumeType_Code end as VolumeType_Code2, ";

					if (!empty($data['VolumeType_id'])) {
						$filter .= " and (RD.VolumeType_id = :VolumeType_id OR RD.VolumeType_sid = :VolumeType_id)";
						$params['VolumeType_id'] = $data['VolumeType_id'];
					}
				} else {
					if (!empty($data['VolumeType_id'])) {
						$filter .= " and RD.VolumeType_id = :VolumeType_id";
						$params['VolumeType_id'] = $data['VolumeType_id'];
					}
				}

				$join .= " left join v_VolumeType vt (nolock) on vt.VolumeType_id = rd.VolumeType_id ";
				$fields .= "vt.VolumeType_Code, ";

				if(!empty($data['RegistryData_IsBadVol']) && $data['RegistryData_IsBadVol'] == 2) {
					$filter .= " and RD.RegistryData_IsBadVol = 2";
				} else {
					$filter .= " and ISNULL(RD.RegistryData_IsBadVol, 1) = 1";
				}
			}
		}
		
		if ( !empty($data['filterRecords']) ) {
			if ( $data['filterRecords'] == 2 ) {
				$filter .= " and ISNULL(RD.{$this->isPaidField}, 1) = 2";
			}
			else if ($data['filterRecords'] == 3) {
				$filter .= " and ISNULL(RD.{$this->isPaidField}, 1) = 1";
			}
		}

		if (in_array($this->getRegionNick(), array('buryatiya'))) {
			$fields .= "RD.RegistryData_IsPaid as RegistryData_IsPaid, ";
		}

		if (in_array($this->getRegionNick(), array('perm'))) {

			// в реестрах со статусом частично принят помечаем оплаченные случаи
			$join .= "left join v_RegistryCheckStatus RCS with (nolock) on R.RegistryCheckStatus_id = RCS.RegistryCheckStatus_id ";
			$fields .= "case when RCS.RegistryCheckStatus_Code = 3 then ISNULL(RD.RegistryData_IsPaid,1) else 0 end as RegistryData_IsPaid, ";

			if ( $this->RegistryType_id == 15 ) {
				$join .= "
					outer apply (
						select top 1 Evn_id
						from {$this->scheme}.{$this->RegistryDataObject} with (nolock)
						where Registry_id = R.Registry_id
							and MaxEvn_id = RD.Evn_id
							and [Number] is null
					) GroupInfo
				";
				$fields .= "case when GroupInfo.Evn_id is not null then 2 else 1 end as IsGroupEvn, ";
			}
		}

		if ($data['session']['region']['nick'] == 'ekb') {
			$join .= "left join v_PayMedType PMT (nolock) on PMT.PayMedType_id = RD.PayMedType_id ";
			$fields .= "PMT.PayMedType_Code, ";
		}

        if ( in_array($data['session']['region']['nick'], array('buryatiya', 'penza', 'pskov')) )  {
            $fields .= "0 as RegistryData_Sum_R,";
        }
		$select_uet = "RD.RegistryData_KdFact as RegistryData_Uet, ";
		// Определение УЕТ по регионам (для поликлиники)
		if ( in_array($this->RegistryType_id, array(2,16)) ) {
			switch($this->regionNick) {
				case 'ufa':
					$select_uet = "RD.EvnVizit_UetOMS as RegistryData_Uet, ";
				break;

				/*case 'kaluga':
				case 'kareliya':
					//В региональной модели
				break;*/

				case 'khak':
					$select_uet = "case when (VT.VizitType_id=4 and dbo.AgeYMD(RD.Person_BirthDay,RD.Evn_disDate ,1)<18) then 1 else RD.RegistryData_KdPay end as RegistryData_Uet, ";
					$join .= "left join v_EvnVizitPL EVPL with (NOLOCK)on EVPL.EvnVizitPL_id = RD.Evn_id ";
					$join .= "left join v_VizitType VT with (NOLOCK)on VT.VizitType_id = EVPL.VizitType_id ";
				break;

				/*case 'astra':
					//В региональной модели
				break;*/

				case 'pskov':
					$select_uet = "
						case when (RD.LpuSectionProfile_Code in ('529', '530', '629', '630', '829', '830') or Usluga.UslugaComplex_id is not null)
						then EVPL.EvnVizitPL_UetOMS else 1
						end as RegistryData_Uet,
						EVPL.EvnVizitPL_Count,
					";
					$join .= "left join v_EvnVizitPL EVPL with (NOLOCK)on EVPL.EvnVizitPL_id = RD.Evn_id ";
					$join .= "
						outer apply (
							select top 1
								UslugaComplex.UslugaComplex_id,
								UslugaComplex.UslugaComplex_Code
							from
								v_EvnUsluga EvnUsluga with (nolock)
								left join UslugaComplex with(nolock) on UslugaComplex.UslugaComplex_id = EvnUsluga.UslugaComplex_id
							where
								EvnUsluga.EvnUsluga_pid = RD.Evn_id
								and LEFT(UslugaComplex.UslugaComplex_Code,4) = 'A.07'
								and rd.LpuSectionProfile_Code in ('577','677','877')
							order by EvnUsluga_id
						) as Usluga
					";
				break;
			}
		}
		$fields .= $select_uet;

		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$join .= "left join EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
			$fields .= "epd.DispClass_id, ";
		}

		if (isset($data['RegistryStatus_id']) && (6==$data['RegistryStatus_id'])) {
			$source_table = 'v_RegistryDeleted_Data';
		} else {
			$source_table = 'v_' . $this->RegistryDataObject;
		}

		if ($data['session']['region']['nick'] == 'ufa') {
			if (!empty($data['OrgSmo_id'])) {
				if ($data['OrgSmo_id'] == 8) {
					$filter .= " and IsNull(os.OrgSMO_RegNomC,'')='' and RD.Polis_id IS NOT NULL";
				} else {
					$filter .= " and RD.OrgSmo_id = :OrgSmo_id";
					$params['OrgSmo_id'] = $data['OrgSmo_id'];
				}
			}
			$join .= " left join v_OrgSmo os with (nolock) on os.OrgSmo_id = RD.OrgSmo_id ";
            $fields .= "
            	case
            		when OS.OrgSMO_RegNomC is not null then OS.OrgSmo_Nick
            		when RD.Polis_id IS NOT NULL and IsNull(os.OrgSMO_RegNomC,'')='' then 'Инотерриториальные'
            		else ''
				end as OrgSmo_Nick,
			";
            $fields .= "isnull(RD.RegistryData_Sum_R, 0) as RegistryData_Sum_R,";
			$fields .= "ISNULL(RD.Paid_id,1) as RegistryData_IsPaid,";
            $this->scheme = 'r2';
            if ($data['RegistryType_id'] && ($data['RegistryType_id'] == 1 || $data['RegistryType_id'] == 14) ) {
                $source_table = 'v_RegistryDataEvnPS';
            }
		}
		// https://redmine.swan.perm.ru/issues/35331
		if ( in_array($data['session']['region']['nick'], array('buryatiya', 'penza', 'pskov', 'khak')) ) {
			$evnVizitPLSetDateField = 'Evn_setDate';
		} else {
			$evnVizitPLSetDateField = ($this->RegistryType_id == 7 ? 'Evn_didDate' : 'Evn_setDate');
		}

		$from = "
			{$this->scheme}.v_Registry R with (NOLOCK)
			inner join {$this->scheme}.{$source_table} RD with (NOLOCK) on RD.Registry_id = R.Registry_id
		";
		if (
			getRegionNick() == 'ufa'
			&& !empty($data['RegistrySubType_id'])
			&& $data['RegistrySubType_id'] == 2
		) {
			// для финального берём по другому

			$regData = $this->queryResult("select Registry_IsNotInsur, OrgSmo_id from {$this->scheme}.v_Registry (nolock) where Registry_id = :Registry_id", array('Registry_id' => $data['Registry_id']));

			if (empty($regData[0])) {
				return array('Error_Msg' => 'Ошибка получения данных по реестру');
			}
			$Registry_IsNotInsur = $regData[0]['Registry_IsNotInsur'];
			$OrgSmo_id = $regData[0]['OrgSmo_id'];
			$filter_rd = " and RD.OrgSmo_id = R.OrgSmo_id";
			if ($Registry_IsNotInsur == 2) {
				// если реестр по СМО, то не зависимо от соц. статуса
				if ($this->RegistryType_id == 6) {
					$filter_rd = " and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
				} else {
					$filter_rd = " and ((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079,112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
				}
			} else if ($OrgSmo_id == 8) {
				// инотеры
				$filter_rd = " and RD.Polis_id IS NOT NULL";
				$filter .= " and IsNull(os.OrgSMO_RegNomC,'')=''";
			}

			$from = "
				{$this->scheme}.v_Registry R with (NOLOCK)
				inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_pid = R.Registry_id
				inner join {$this->scheme}.{$source_table} RD with (NOLOCK) on RD.Registry_id = RGL.Registry_id {$filter_rd}
			";
		}

		if (!empty($data['ExtendedFilterField']) && !empty($data['ExtendedFilterFieldValue'])) {
			$filter .= " and " . $data['ExtendedFilterField'] . " like '%' + :ExtendedFilterFieldValue + '%'";
			$params['ExtendedFilterFieldValue'] = $data['ExtendedFilterFieldValue'];
		}

		$orderBy="RD.Person_FIO";
		if (!empty($data['sort']) && $this->regionNick == 'buryatiya') {
			$sortDir = ('DESC' == $data['dir']) ? 'DESC' : 'ASC';
			switch($data['sort']) {
				case 'EvnVizitPL_setDate':
					$data['sort']="RD.{$evnVizitPLSetDateField}";
					break;
				case 'Person_BirthDay': 
				case 'Evn_disDate':
					$data['sort'] = "RD.".$data['sort'];
					break;
				case 'EvnPL_NumCard':
					$data['sort'] = "case when RD.NumCard like '%/%' then left(RD.NumCard,charindex('/',RD.NumCard)-1) else CAST(RD.NumCard AS BIGINT) END";
					break;
				case 'Polis_Num':
					$data['sort'] = "CAST(RD.Polis_Num AS BIGINT)";
					break;
				case 'LpuSection_name':
					$data['sort'] = "cast(LS.LpuSection_Code as BIGINT)";
					break;
			}
			
			$orderBy = " {$data['sort']} {$sortDir}";
			$params['sort'] = $data['sort'];
		}
		
		$query = "
			Select
				-- select
				RD.Evn_id,
				RD.Evn_rid,
				RD.{$this->MaxEvnField} as MaxEvn_id,
				RD.EvnClass_id,
				RD.Registry_id,
				RD.RegistryType_id,
				RD.Person_id,
				RD.Server_id,
				PersonEvn.PersonEvn_id,
				{$fields}
				RD.RegistryData_deleted,
				RD.Polis_Num,
				RTrim(RD.NumCard) as EvnPL_NumCard,
				RTrim(RD.Person_FIO) as Person_FIO,
				RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RD.LpuSection_id,
				RTrim(RD.LpuSection_name) as LpuSection_name,
				RTrim(RD.MedPersonal_Fio) as MedPersonal_Fio,
				RTrim(IsNull(convert(varchar,cast(RD.{$evnVizitPLSetDateField} as datetime),104),'')) as EvnVizitPL_setDate,
				RTrim(IsNull(convert(varchar,cast(RD.Evn_disDate as datetime),104),'')) as Evn_disDate,
				RD.RegistryData_Tariff RegistryData_Tariff,
				--RD.RegistryData_KdFact as RegistryData_Uet,
				RD.RegistryData_KdPay as RegistryData_KdPay,
				RD.RegistryData_KdPlan as RegistryData_KdPlan,
				RD.RegistryData_ItogSum as RegistryData_ItogSum,
				RegistryError.Err_Count as Err_Count,
				RegistryErrorTFOMS.ErrTfoms_Count as ErrTfoms_Count,
				case when ISNULL(e.Evn_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
				-- end select
			from
				-- from
				{$from}
				left join v_Evn e (nolock) on e.Evn_id = rd.Evn_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = RD.LpuSection_id
				{$join}
				outer apply
				(
					Select count(*) as Err_Count
					from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK) where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
				) RegistryError
				outer apply
				(
					Select count(*) as ErrTfoms_Count
					from {$this->scheme}.v_RegistryErrorTFOMS RET with (NOLOCK) where RD.Evn_id = RET.Evn_id and RD.Registry_id = RET.Registry_id
				) RegistryErrorTFOMS
				outer apply
				(
					Select top 1 PersonEvn_id
					from v_PersonEvn PE with (NOLOCK)
					where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
				) PersonEvn
			-- end from
			where
				-- where
				R.Registry_id = :Registry_id
				and
				{$filter}
				{$filterAddQuery}
				-- end where
			order by
				-- order by
				{$orderBy}
				-- end order by
		";

		if ($returnQueryOnly === true) {
			return [
				'query' => $query,
				'params' => $params,
			];
		}

		//echo getDebugSQL($query,$params);die;

		if (!empty($data['nopaging'])) {
			$result = $this->db->query($query, $params);

			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return false;
			}
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}


	/**
	 * @param $data
	 * @return array|bool
	 */
	public function loadRegistryErrorTFOMSFilter($data)
	{
		if (in_array($this->regionNick, ['buryatiya','kareliya','penza','ufa'])) {
			return $this->_loadRegistryErrorTFOMSFilter($data);
		}

		return [];
	}

	/**
	 * Загрузка данных в фильтр грида https://redmine.swan.perm.ru/issues/51270
	 */
	function loadRegistryDataFilter($data)
	{
		if (in_array($this->regionNick, ['buryatiya','kareliya','penza','ufa'])) {
			return $this->_loadRegistryDataFilter($data);
		}

		if ($data['Registry_id']==0)
		{
			return false;
		}
		if ($data['RegistryType_id']==0)
		{
			return false;
		}

		$this->setRegistryParamsByType($data);

		//Фильтр грида
		$json = isset($data['Filter']) ? toUTF(trim($data['Filter'],'"')) : false;

		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false;

		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id'=>$data['session']['lpu_id'],
			'Value'=>($filter_mode['value'] == "_") ? "%%" : "%".trim(toAnsi($filter_mode['value']))."%"
		);

		$filter="(1=1)";

		$join = "";
		$fields = "";
			$join = "
				outer apply (
					select top 1 RDLT.Person_id from RegistryDataLgot RDLT with (NOLOCK) where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
				) RDL
			";
			$join .= "left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id ";

        if ($filter_mode['type'] == 'unicFilter') {
            $prefix = '';
            //Подгоняем поля под запрос с WITH
            if ($filter_mode['cell'] == 'EvnPL_NumCard') {
                $field = 'NumCard';
                $orderBy = 'RD.NumCard';
            } else if ($filter_mode['cell'] == 'Diag_Code') {
                $field = 'D.Diag_Code';
                $orderBy = 'D.Diag_Code';
            } else if ($filter_mode['cell'] == 'LpuSection_name') {
                $field = 'RD.LpuSection_name';
                $orderBy = 'RD.LpuSection_name';
            } else if ($filter_mode['cell'] == 'LpuBuilding_Name') {
                $field = 'LB.LpuBuilding_Name';
                $orderBy = 'LB.LpuBuilding_Name';
            } else if ($filter_mode['cell'] == 'Paid') {
                $field = 'RD.Paid_id';
                $orderBy = 'RD.Paid_id';
            } else if ($filter_mode['cell'] == 'Evn_id') {
                $field = 'RD.Evn_id';
                $orderBy = 'RD.Evn_id';
            } else if ($filter_mode['cell'] == 'Usluga_Code') {

                if ($data['RegistryType_id'] != 1) {
                    $field = 'U.UslugaComplex_Code';
                    $orderBy = 'U.UslugaComplex_Code';
                } else {
                    $field = 'm.Mes_Code';
                    $orderBy = 'm.Mes_Code';
                }
            } else if ($filter_mode['cell'] == 'EvnVizitPL_setDate') {
                //$field = 'RD.Evn_setDate';
                //$orderBy = 'RD.Evn_setDate';
                //RTrim(IsNull(convert(varchar,cast(RD.{$evnVizitPLSetDateField} as datetime),104),'')) as EvnVizitPL_setDate
                $field = "RTrim(IsNull(convert(varchar,cast(RD.Evn_setDate as datetime),104),''))";
                $orderBy = "RTrim(IsNull(convert(varchar,cast(RD.Evn_setDate as datetime),104),''))";
            }//EvnVizitPL_setDate

            else if ($filter_mode['cell'] == 'Person_BirthDay') {
                $field = "RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),''))";
                $orderBy = "RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),''))";
            } elseif ($filter_mode['cell'] == 'Evn_ident') {
                $field = 'RD.Evn_id';
                $orderBy = 'RD.Evn_id';
                if (in_array($this->regionNick, array('kaluga')) && $data['RegistryType_id'] == 1) {
                    $field = 'RD.Evn_rid';
                    $orderBy = 'RD.Evn_rid';
                } else if (in_array($this->regionNick, array('kareliya')) && $data['RegistryType_id'] == 1) {
                    $field = 'RD.Evn_sid';
                    $orderBy = 'RD.Evn_sid';
                }
            } else {
                $field = $filter_mode['cell'];
                $orderBy = isset($orderBy) ? $orderBy : $filter_mode['cell'];
            }
            $Like = ($filter_mode['specific'] === false) ? "" : " and " . $orderBy . " like  :Value";
            $with = "WITH";
            $distinct = 'DISTINCT';
        } else {
            return false;
        }
        $datatable = $this->scheme . '.v_' . $this->RegistryDataObject;
        if (getRegionNick() != 'buryatiya' && $data['RegistryType_id'] == 6) {
            $datatable = $this->scheme . '.v_RegistryDataCmp';
        }
        $orderBy = isset($orderBy) ? $orderBy : null;

        $distinct = isset($distinct) ? $distinct : '';
        $with = isset($with) ? $with : '';

        $evnVizitPLSetDateField = 'Evn_setDate';
		$evn_sid = (getRegionNick() == 'kareliya') ? 'RD.Evn_sid,' : '';

        $query = "
			Select
				-- select
				RD.Evn_id,
				RD.Evn_rid,
				{$evn_sid}
				RD.NumCard,
				RD.EvnClass_id,
				RD.Registry_id,
				RD.RegistryType_id,
				RD.Person_id,
				RD.Server_id,
				PersonEvn.PersonEvn_id,
				{$fields}
				RD.RegistryData_deleted,
				RTrim(RD.NumCard) as EvnPL_NumCard,
				RTrim(RD.Person_FIO) as Person_FIO,
				RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RD.LpuSection_id,
				RTrim(RD.LpuSection_name) as LpuSection_name,
				RTrim(RD.MedPersonal_Fio) as MedPersonal_Fio,
				RTrim(IsNull(convert(varchar,cast(RD.{$evnVizitPLSetDateField} as datetime),104),'')) as EvnVizitPL_setDate,
				RTrim(IsNull(convert(varchar,cast(RD.Evn_disDate as datetime),104),'')) as Evn_disDate,
				RD.RegistryData_Tariff RegistryData_Tariff,
				--RD.RegistryData_KdFact as RegistryData_Uet,
				RD.RegistryData_KdPay as RegistryData_KdPay,
				RD.RegistryData_KdPlan as RegistryData_KdPlan,
				RD.RegistryData_ItogSum as RegistryData_ItogSum,
				RegistryError.Err_Count as Err_Count
				-- end select
			from
				-- from
				{$datatable} RD with (NOLOCK)
				{$join}
				outer apply
				(
					Select count(*) as Err_Count
					from {$this->scheme}.v_RegistryError RE with (NOLOCK) where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
				) RegistryError
				outer apply
				(
					Select top 1 PersonEvn_id
					from v_PersonEvn PE with (NOLOCK)
					where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
				) PersonEvn
				-- end from
			where
				-- where
				RD.Registry_id=:Registry_id
				and
				{$filter}
				-- end where
			group by
				-- group by
				{$field}
				-- end group by
			order by
				-- order by
				{$field}
				-- end order by
			";

		//echo getDebugSQL($query, $params);die;
		//exit;
		//$data['limit'] = 3000;

		$result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $field, $data['start'], $data['limit'], $Like, $orderBy), $params);
		$result_count = $this->db->query($this->_getCountSQLPH($query, $field, $distinct, $orderBy), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			if(is_array($cnt_arr) && sizeof($cnt_arr)){
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			}
			else
				return false;
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $query
	 * @param $distinct
	 * @param $field
	 * @param int $start
	 * @param int $limit
	 * @param $like
	 * @param string $order_row
	 * @return string
	 */
	function _getLimitSQLPH($query, $distinct, $field, $start = 0, $limit = 1000, $like, $order_row = '') {
		$exp = preg_match("/--[\s]*from([\w\W]*)--[\s]*end from/i", $query, $maches);
		$from = $maches[1];

		$exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $query, $maches);

		if ( isset($maches[1]) ) {
			$where = $maches[1] . " " . $like;
		}

		$query = "
			WITH temptable AS (
				SELECT " . $distinct . "
					" . $order_row . " AS field,
					DENSE_RANK() OVER (ORDER BY " . $order_row . ") AS RowNumber
				FROM " . $from . "
				" . (!empty($where) ? "WHERE " . $where : "") . "
			)

			SELECT field
			FROM temptable with(nolock)
			ORDER BY RowNumber"
			. " OFFSET " . $start . " ROWS"
			. " FETCH NEXT " . $limit . " ROWS ONLY
		";
		//WHERE RowNumber BETWEEN ".($start + 1)." AND ".($start + $limit)." ".(!empty($order_row) ? "" : "field").";

		return $query;
	}

	/**
	 * comments
	 */
	function _getCountSQLPH($sql, $field, $distinct) {
		$sql = preg_replace("/--[\s]*select[\w\W]*--[\s]*end select/i", " count( ". $distinct ." ".$field." ) AS cnt ", $sql);

		$exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $sql, $maches);
		if ( isset($maches[1]) )
		{
			$where = $maches[1];
		}

		$sql = preg_replace("/ORDER BY[\s]*--[\s]*order by[\w\W]*--[\s]*end order by/i", "", $sql);

		//$sql = preg_replace("/GROUP BY[\s]*--[\s]*group by[\w\W]*--[\s]*end group by/i", "", $sql);
		$sql = str_replace(['-- group by', '-- end group by'], "", $sql);
		$sql = str_replace(['GROUP BY', 'group by'], "-- group by", $sql);

		return $sql;
	}

	/**
	 * Отметки об оплате случаев
	 */
	function loadRegistryDataPaid($data)
	{
		$this->setRegistryParamsByType($data);

		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id']
		);
		$join = "";
		$fields = "";

		//if (!in_array($this->regionNick, array('ufa','pskov')))
		if (!in_array($data['session']['region']['nick'], array('ufa','pskov','buryatiya','penza','vologda')))
		{
			$join = "
				outer apply (
					select top 1 RDLT.Person_id from RegistryDataLgot RDLT with (NOLOCK) where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
				) RDL
			";
			$join .= "left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id ";
			$fields .= "case when RDL.Person_id is null then 0 else 1 end as IsRDL, ";
			$fields .= "RD.needReform, RD.checkReform, RD.timeReform, ";
			$fields .= "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit, ";
		}
		else
		{
			$join .= "
				left join v_UslugaComplex U with (NOLOCK) on ISNULL(RD.Usluga_id,RD.UslugaComplex_id) =  U.UslugaComplex_id -- связь стала с UslugaComplex_id (refs #13509)
				left join v_Diag D with (NOLOCK) on RD.Diag_id =  D.Diag_id
				left join v_EvnSection es (nolock) on ES.EvnSection_id = RD.Evn_id
				left join v_MesOld m (nolock) on m.Mes_id = ES.Mes_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			";
			$fields .= "
				m.Mes_Code,
				U.UslugaComplex_Code as Usluga_Code,
			";
			$fields .= "D.Diag_Code, case when RD.Paid_id = 1 then 'Нет' when RD.Paid_id = 2 then 'Да' else '' end as Paid, ";
			$fields .= "LB.LpuBuilding_Name, ";

			if ($this->regionNick == 'ufa') {
				$fields .= "Mes.Mes_Code + ISNULL(' ' + Mes.MesOld_Num, '') as Mes_Code_KSG, RD.Mes_Code_KPG, htm.HTMedicalCareClass_Name, ";
				$join .= "
					left join v_MesOld Mes with (nolock) on Mes.Mes_id = rd.MesItog_id
					left join v_HTMedicalCareClass htm (nolock) on htm.HTMedicalCareClass_id = ES.HTMedicalCareClass_id
				";
			}
		}

		if ($data['session']['region']['nick'] == 'perm') {
			// в реестрах со статусом частично принят помечаем оплаченные случаи
			$join .= "left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id ";
			$join .= "left join v_RegistryCheckStatus RCS with (nolock) on R.RegistryCheckStatus_id = RCS.RegistryCheckStatus_id ";
			$fields .= "case when RCS.RegistryCheckStatus_Code = 3 then ISNULL(RD.RegistryData_IsPaid,1) else 0 end as RegistryData_IsPaid, ";
		}

		if ( in_array($data['session']['region']['nick'], array('buryatiya', 'pskov', 'penza')) ) {
			$fields .= "0 as RegistryData_Sum_R,";
		}
		$select_uet = "RD.RegistryData_KdFact as RegistryData_Uet, ";
		// Определение УЕТ по регионам (для поликлиники)
		if ( in_array($this->RegistryType_id, array(2,16)) ) {
			switch($this->regionNick) {
				case 'ufa':
					$select_uet = "RD.EvnVizit_UetOMS as RegistryData_Uet, ";
					break;

				/*case 'kaluga':
				case 'kareliya':
					//В региональной модели
				break;*/

				case 'khak':
					$select_uet = "case when (VT.VizitType_id=4 and dbo.AgeYMD(RD.Person_BirthDay,RD.Evn_disDate ,1)<18) then 1 else RD.RegistryData_KdPay end as RegistryData_Uet, ";
					$join .= "left join v_EvnVizitPL EVPL with (NOLOCK)on EVPL.EvnVizitPL_id = RD.Evn_id ";
					$join .= "left join v_VizitType VT with (NOLOCK)on VT.VizitType_id = EVPL.VizitType_id ";
					break;

				/*case 'astra':
					//В региональной модели
				break;*/

				case 'pskov':
					$select_uet = "
						case when (RD.LpuSectionProfile_Code in ('529', '530', '629', '630', '829', '830') or Usluga.UslugaComplex_id is not null)
						then EVPL.EvnVizitPL_UetOMS else 1
						end as RegistryData_Uet,
					";
					$join .= "left join v_EvnVizitPL EVPL with (NOLOCK)on EVPL.EvnVizitPL_id = RD.Evn_id ";
					$join .= "
						outer apply (
							select top 1
								UslugaComplex.UslugaComplex_id,
								UslugaComplex.UslugaComplex_Code
							from
								v_EvnUsluga EvnUsluga with (nolock)
								left join UslugaComplex with(nolock) on UslugaComplex.UslugaComplex_id = EvnUsluga.UslugaComplex_id
							where
								EvnUsluga.EvnUsluga_pid = RD.Evn_id
								and LEFT(UslugaComplex.UslugaComplex_Code,4) = 'A.07'
								and rd.LpuSectionProfile_Code in ('577','677','877')
							order by EvnUsluga_id
						) as Usluga
					";
					break;
			}
		}
		$fields .= $select_uet;

		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$join .= "left join EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
			$fields .= "epd.DispClass_id, ";
		}

		if (isset($data['RegistryStatus_id']) && (6==$data['RegistryStatus_id'])) {
			$source_table = 'v_RegistryDeleted_Data';
		} else {
			$source_table = 'v_' . $this->RegistryDataObject;
		}

		if ($data['session']['region']['nick'] == 'ufa') {
			$fields .= "isnull(RD.RegistryData_Sum_R, 0) as RegistryData_Sum_R,";
			$fields .= "ISNULL(RD.Paid_id,1) as RegistryData_IsPaid,";
			$this->scheme = 'r2';
			if ($data['RegistryType_id'] && ($data['RegistryType_id'] == 1 || $data['RegistryType_id'] == 14) ) {
				$source_table = 'v_RegistryDataEvnPS';
			}
		}
		// https://redmine.swan.perm.ru/issues/35331
		if ( in_array($data['session']['region']['nick'], array('buryatiya', 'pskov', 'penza')) ) {
			$evnVizitPLSetDateField = 'Evn_setDate';
		} else {
			$evnVizitPLSetDateField = ($this->RegistryType_id == 7 ? 'Evn_didDate' : 'Evn_setDate');
		}

		$query = "
			Select
				-- select
				RD.Evn_id,
				RD.Evn_rid,
				RD.EvnClass_id,
				RD.Registry_id,
				RD.RegistryType_id,
				RD.Person_id,
				RD.Server_id,
				PersonEvn.PersonEvn_id,
				{$fields}
				RD.RegistryData_deleted,
				RTrim(RD.NumCard) as EvnPL_NumCard,
				RD.Person_FirName,
				RD.Person_SurName,
				RD.Person_SecName,
				RD.Polis_Num,
				RTrim(RD.Person_FIO) as Person_FIO,
				RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RD.LpuSection_id,
				RTrim(RD.LpuSection_name) as LpuSection_name,
				RTrim(RD.MedPersonal_Fio) as MedPersonal_Fio,
				RTrim(IsNull(convert(varchar,cast(RD.{$evnVizitPLSetDateField} as datetime),104),'')) as EvnVizitPL_setDate,
				RTrim(IsNull(convert(varchar,cast(RD.Evn_disDate as datetime),104),'')) as Evn_disDate,
				RD.RegistryData_Tariff RegistryData_Tariff,
				--RD.RegistryData_KdFact as RegistryData_Uet,
				RD.RegistryData_KdPay as RegistryData_KdPay,
				RD.RegistryData_KdPlan as RegistryData_KdPlan,
				RD.RegistryData_ItogSum as RegistryData_ItogSum,
				RegistryError.Err_Count as Err_Count
				-- end select
			from
				-- from
				{$this->scheme}.{$source_table} RD with (NOLOCK)
				{$join}
				outer apply
				(
					Select count(*) as Err_Count
					from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK) where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
				) RegistryError
				outer apply
				(
					Select top 1 PersonEvn_id
					from v_PersonEvn PE with (NOLOCK)
					where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
				) PersonEvn
			-- end from
			where
				-- where
				RD.Registry_id=:Registry_id
				-- end where
			order by
				-- order by
				RD.Person_FIO
				-- end order by
		";

		$result = $this->db->query($query, $params);

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
	 *	Получение списка каких-то ошибок
	 */
	function loadRegistryErrorCom($data)
	{
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		if ( isset($data['start']) && isset($data['limit']) && !($data['start'] >= 0 && $data['limit'] >= 0) )  {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$filterList = [
			'RE.Registry_id = :Registry_id'
		];
		$params = [
			'Registry_id' => $data['Registry_id']
		];
		$returnQueryOnly = isset($data['returnQueryOnly']);

		if (!in_array($this->regionNick, ['ufa','pskov','buryatiya','penza','vologda'])) {
			$tempscheme = $this->scheme;
		}
		else {
			$tempscheme = 'dbo';
		}

		if (isset($data['Filter'])) {
			$filterData = json_decode(trim($data['Filter'], '"'), 1);

			if (is_array($filterData)) {
				foreach ($filterData as $column => $value) {
					if (!is_array($value)) {
						continue;
					}

					$r = null;

					foreach ($value as $d) {
						$r .= "'" . trim(toAnsi($d)) . "',";
					}

					$r = rtrim($r, ',');

					$filterList[] = $column . ' IN (' . $r . ')';
				}
			}
		}

		if (!empty($data['ExtendedFilterField']) && !empty($data['ExtendedFilterFieldValue'])) {
			$filterList[] = $data['ExtendedFilterField'] . " like '%' + :ExtendedFilterFieldValue + '%'";
			$params['ExtendedFilterFieldValue'] = $data['ExtendedFilterFieldValue'];
		}

		$query = "
			select
				-- select
				RE.Registry_id,
				RE.RegistryErrorType_id,
				RE.RegistryErrorType_Code,
				RTrim(RE.RegistryErrorType_Name) as RegistryErrorType_Name,
				RE.RegistryErrorType_Descr,
				RE.RegistryErrorClass_id,
				RTrim(RE.RegistryErrorClass_Name) as RegistryErrorClass_Name
				-- end select
			from
				-- from
				{$tempscheme}.v_{$this->RegistryErrorComObject} RE with (NOLOCK)
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by
		";

		if ($returnQueryOnly === true) {
			return [
				'query' => $query,
				'params' => $params,
			];
		}

		//echo getDebugSQL($query, $params);die;

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Получение списка структурных подразделений
	 */
	function getLpuOidList($data) {
		return $this->queryResult("
			select distinct
				L.Lpu_id,
				L.Lpu_Name,
				L.Lpu_Nick,
				convert(varchar(10), L.Lpu_BegDate, 104) as Lpu_BegDate,
				convert(varchar(10), L.Lpu_EndDate, 104) as Lpu_EndDate
			from
				v_Lpu l (nolock)
				inner join v_LpuUnitSet lus (nolock) on lus.Lpu_oid = l.Lpu_id
			where
				lus.Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 *	Получение списка ошибок
	 */
	function loadRegistryError($data)
	{
		$this->setRegistryParamsByType($data);

		$filterAddQueryTemp = null;
		$returnQueryOnly = isset($data['returnQueryOnly']);

		if(isset($data['Filter'])){
			$filterData = json_decode(toUTF(trim($data['Filter'],'"')), 1);

			if(is_array($filterData)){

				foreach($filterData as $column=>$value){

					if(is_array($value)){
						$r = null;

						foreach($value as $d){
							$r .= "'".trim(toAnsi($d))."',";
						}

						if($column == 'Evn_id')
							$column = 'RE.'.$column;
						elseif($column == 'Person_FIO')
							$column = 'RE.'.$column;
						elseif($column == 'LpuSection_name')
							$column = 'RE.'.$column;
						elseif($column == 'LpuBuilding_Name')
							$column = 'LB.'.$column;
                        elseif ($column == 'LpuSectionProfile_Name')
                            $column = 'LSP.' . $column;
                        elseif ($column == 'MedSpecOms_Name')
                            $column = 'MSO.' . $column;
						elseif($column == 'Usluga_Code')
							$column = ($this->RegistryType_id != 1 && $this->RegistryType_id != 14 ? 'U.UslugaComplex_Code' : 'm.Mes_Code');
						elseif($column == 'Evn_ident') {
							$column = 'RE.Evn_id';
							if (in_array($this->regionNick, array('kaluga')) && $this->RegistryType_id == 1) {
								$column = 'RE.Evn_rid';
							}
							else if (in_array($this->regionNick, array('kareliya')) && $this->RegistryType_id == 1) {
								$column = 'RE.Evn_sid';
							}
						}

						$r = rtrim($r, ',');

						$filterAddQueryTemp[] = $column.' IN ('.$r.')';
					}
				}

			}

			if(is_array($filterAddQueryTemp)){
				$filterAddQuery = "and ".implode(" and ", $filterAddQueryTemp);
			}
			else
				$filterAddQuery = "";
		}

		$filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;


		if ($data['Registry_id']<=0)
		{
			return false;
		}
		if (empty($data['nopaging'])) {
			if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
			{
				return false;
			}
		}

		if ( 'perm' == $this->regionNick && $this->RegistryType_id == 1 ) {
			$EvnIdField = 'Evn_pid';
		}
		else {
			$EvnIdField = 'Evn_id';
		}

		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$filter="(1=1)";
		if (isset($data['Person_SurName']))
		{
			$filter .= " and RE.Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and RE.Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and RE.Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryError_Code']))
		{
			$filter .= " and RE.RegistryErrorType_Code = :RegistryError_Code ";
			$params['RegistryError_Code'] = $data['RegistryError_Code'];
		}
		if (isset($data['RegistryErrorType_id']))
		{
			$filter .= " and RE.RegistryErrorType_id = :RegistryErrorType_id ";
			$params['RegistryErrorType_id'] = $data['RegistryErrorType_id'];
		}
		if (!empty($data['LpuBuilding_id'])) {
			$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id ";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		$Evn_ident = "RE.{$EvnIdField} as Evn_ident,";
		if ( !empty($data['Evn_id']) && !($this->regionNick == 'kareliya' && $this->RegistryType_id == 1) ) {
			$filter .= " and RE.{$EvnIdField} = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}
		
		if (in_array($this->regionNick, array('kaluga')) && $this->RegistryType_id == 1) {
			$Evn_ident = "RE.Evn_rid as Evn_ident,";
			if (!empty($data['Evn_id'])) {
				$filter .= " and RE.Evn_rid = :Evn_id";
				$params['Evn_id'] = $data['Evn_id'];
			}
		}

		if (in_array($this->regionNick, array('kareliya')) && $this->RegistryType_id == 1) {
			$Evn_ident = "RE.Evn_sid as Evn_ident,";
			if (!empty($data['Evn_id'])) {
				$filter .= " and RE.Evn_sid = :Evn_id";
				$params['Evn_id'] = $data['Evn_id'];
			}
		}

		$join = "";
		$fields = "";

		if ( $this->regionNick == 'perm' && !in_array($this->RegistryDataObject, array('RegistryDataCmp', 'RegistryDataPar')) ) {
			$join .= "left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_pid";
		} else if ( $this->regionNick == 'perm' && in_array($this->RegistryDataObject, array('RegistryDataCmp')) ) {
			$join .= "left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_rid";
		} else {
			$join .= "left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id";
		}

		if ( !empty($data['filterIsZNO']) && $data['filterIsZNO'] == 2 ) {
			$filter .= " and RD.RegistryData_IsZNO in (2, 3)";
		}

		if (in_array($this->regionNick, array('ufa','vologda'))) {
			$join .= " 
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = rd.MedSpec_id
		 	";
			$fields .= "
				ISNULL(LSP.LpuSectionProfile_Code + '. ', '') + LSP.LpuSectionProfile_Name as LpuSectionProfile_Name,
				ISNULL(MSO.MedSpecOms_Code + '. ', '') + MSO.MedSpecOms_Name as MedSpecOms_Name,
			";
		}

		if ( in_array($this->regionNick, array('perm')) && $this->RegistryType_id == 15 ) {
			$join .= "
				outer apply (
					select top 1 Evn_id
					from {$this->scheme}.{$this->RegistryDataObject} with (nolock)
					where Registry_id = RE.Registry_id
						and MaxEvn_id = RD.Evn_id
						and [Number] is null
				) GroupInfo
			";
			$fields .= "case when GroupInfo.Evn_id is not null then 2 else 1 end as IsGroupEvn, ";
		}

		// @task https://redmine.swan.perm.ru/issues/70857
		// @task https://redmine.swan.perm.ru/issues/87921
		if ( in_array($this->regionNick, array('buryatiya')) ) {
			$EvnVizitPLJoinField = 'EvnVizitPL_rid';

			if($data['RegistryType_id'] != 15) {
				$orderby="";
				if ($data['RegistryType_id'] == 6) {
					$E_connection = "{$this->RegistryObject}_id = RE.Evn_id";
					$E_numCard = 'Ngod';
				} else if (in_array($data['RegistryType_id'], [7, 9, 11, 12])) {
					$E_connection = "
						Person_id = RE.Person_id
						and LpuAttachType_id = 1
						and isnull(PersonCard_begDate, RE.Evn_setDate) <= RE.Evn_setDate
						and isnull(PersonCard_endDate, RE.Evn_setDate) >= RE.Evn_setDate
					";
					$E_numCard = 'Code';
					$orderby="order by PersonCard_begDate desc";
				} else {
					$E_connection = "{$this->RegistryObject}_id = RE.Evn_rid";
					$E_numCard = 'NumCard';
				}

				$fields .= "NC.NumCard as NumCard, ";
				$join .= "
					outer apply (
						select top 1
						 	RTrim({$this->RegistryObject}_{$E_numCard}) as NumCard
						from
						 	v_{$this->RegistryObject} with (nolock)
						where
							{$E_connection}
							{$orderby} 
					) NC
				";
			}
		}
		else {
			$EvnVizitPLJoinField = 'EvnVizitPL_id';
		}

		if (!in_array($this->regionNick, array('ufa','pskov','buryatiya','penza','vologda')))
		{
			if (!empty($data['MedPersonal_id'])) {
				$filter .= " and RE.MedPersonal_id = :MedPersonal_id";
				$params['MedPersonal_id'] = $data['MedPersonal_id'];
			}

			$join .= "
				left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = RE.MedPersonal_id
				) MP
			";
			$fields .= "RD.needReform, RE.RegistryErrorType_Form, RE.MedStaffFact_id,"; // , RD.checkReform, RD.timeReform
			$fields .= "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit, ";
			$fields .= "RE.LpuUnit_id, RE.MedPersonal_id, MP.Person_Fio as MedPersonal_Fio, ";

			if ($this->regionNick == 'krym') {
				$join .= "
					left join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RE.Registry_id
					left join v_LpuSection LS (nolock) on LS.LpuSection_id = RE.LpuSection_id
				";

				if ($this->RegistryType_id == 6) {
					$join .= "
						left join CmpCloseCard CCC with (nolock) on CCC.CmpCloseCard_id = RE.Evn_id
						left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = CCC.LpuBuilding_id			
					";
				} else {
					$join .= "
						left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id			
					";
				}

				$fields .= "
					R.RegistryType_id,
					LB.LpuBuilding_id,
					LB.LpuBuilding_Name,
				";
			}

			if ($this->regionNick == 'kareliya' && $this->RegistryType_id == 6) {
				$join .= "
					left join v_CmpCallCard CCC with (nolock) on CCC.CmpCallCard_id = RE.Evn_id
					left join v_CmpCloseCard CLC with (nolock) on CLC.CmpCallCard_id = CCC.CmpCallCard_id
				";

				$fields .= "
					CLC.CmpCloseCard_id,
				";
			}
		}
		else
		{
			if(!empty($data['LpuBuilding_id'])) {
				$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			
			$join .= "
				left join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RE.Registry_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = RE.LpuSection_id
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			";
			$fields .= "
				R.RegistryType_id, 
				LB.LpuBuilding_Name, 
			";

			switch ( $this->RegistryType_id ) {
				case 1:
				case 14:
					$join .= "
						left join v_EvnSection es (nolock) on ES.EvnSection_id = RE.Evn_id
						left join v_MesOld m (nolock) on m.Mes_id = ES.Mes_id
						outer apply(
							select top 1 Person_Fio
							from v_MedPersonal with (nolock)
							where MedPersonal_id = ES.MedPersonal_id
						) as MP
					";
					$fields .= "
						m.Mes_Code,
						ISNULL(MP.Person_Fio, RD.MedPersonal_Fio) as MedPersonal_Fio,
					";
					if (!empty($data['MedPersonal_id'])) {
						$filter .= " and ISNULL(ES.MedPersonal_id, RD.MedPersonal_id) = :MedPersonal_id";
						$params['MedPersonal_id'] = $data['MedPersonal_id'];
					}
				break;

				case 2:
					$join .= "
						left join v_EvnVizitPL evpl (nolock) on evpl.{$EvnVizitPLJoinField} = RE.Evn_id
						outer apply (
							select top 1
								t1.EvnUslugaCommon_id,
								t1.UslugaComplex_id as UslugaComplex_uid
							from
								v_EvnUslugaCommon t1 with (nolock)
								left join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
								left join v_UslugaCategory t3 with (nolock) on t3.UslugaCategory_id = t2.UslugaCategory_id
							where
								t1.EvnUslugaCommon_pid = evpl.EvnVizitPL_id
								and t3.UslugaCategory_SysNick in ('tfoms', 'lpusection')
							order by
								t1.EvnUslugaCommon_setDT desc
						) EU
						left join v_UslugaComplex U (nolock) on U.UslugaComplex_id = EU.UslugaComplex_uid
						outer apply(
							select top 1 Person_Fio
							from v_MedPersonal with (nolock)
							where MedPersonal_id = evpl.MedPersonal_id
						) as MP
					";
					$fields .= "
						U.UslugaComplex_Code as Usluga_Code,
						ISNULL(MP.Person_Fio, RD.MedPersonal_Fio) as MedPersonal_Fio,
					";
					if (!empty($data['MedPersonal_id'])) {
						$filter .= " and ISNULL(evpl.MedPersonal_id, RD.MedPersonal_id) = :MedPersonal_id";
						$params['MedPersonal_id'] = $data['MedPersonal_id'];
					}
				break;
				case 16:
					$join .= "
						left join v_EvnDiagPLStom edpls (nolock) on edpls.EvnDiagPLStom_id = RE.Evn_id
						left join v_EvnVizitPLStom evpls (nolock) on evpls.EvnVizitPLStom_id = edpls.EvnDiagPLStom_pid

						outer apply (
							select top 1
								t1.EvnUslugaCommon_id,
								t1.UslugaComplex_id as UslugaComplex_uid
							from
								v_EvnUslugaCommon t1 with (nolock)
								left join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
								left join v_UslugaCategory t3 with (nolock) on t3.UslugaCategory_id = t2.UslugaCategory_id
							where
								t1.EvnUslugaCommon_pid = evpls.EvnVizitPLStom_id
								and t3.UslugaCategory_SysNick in ('tfoms', 'lpusection')
							order by
								t1.EvnUslugaCommon_setDT desc
						) EU
						left join v_UslugaComplex U (nolock) on U.UslugaComplex_id = EU.UslugaComplex_uid
						outer apply(
							select top 1 Person_Fio
							from v_MedPersonal with (nolock)
							where MedPersonal_id = evpls.MedPersonal_id
						) as MP
					";
					$fields .= "
						U.UslugaComplex_Code as Usluga_Code,
						ISNULL(MP.Person_Fio, RD.MedPersonal_Fio) as MedPersonal_Fio,
					";
					if (!empty($data['MedPersonal_id'])) {
						$filter .= " and ISNULL(evpls.MedPersonal_id, RD.MedPersonal_id) = :MedPersonal_id";
						$params['MedPersonal_id'] = $data['MedPersonal_id'];
					}
				break;

				case 4:
				case 7:
					$join .= "
						left join v_EvnVizitDispDop evdd (nolock) on evdd.EvnVizitDispDop_id = RE.Evn_id
						left join v_EvnUslugaDispDop eudd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
						left join v_UslugaComplex U (nolock) on U.UslugaComplex_id = eudd.UslugaComplex_id
						outer apply(
							select top 1 Person_Fio
							from v_MedPersonal with (nolock)
							where MedPersonal_id = eudd.MedPersonal_id
						) as MP
					";
					$fields .= "
						U.UslugaComplex_Code as Usluga_Code,
						ISNULL(MP.Person_Fio, RD.MedPersonal_Fio) as MedPersonal_Fio,
					";
					if (!empty($data['MedPersonal_id'])) {
						$filter .= " and ISNULL(eudd.MedPersonal_id, RD.MedPersonal_id) = :MedPersonal_id";
						$params['MedPersonal_id'] = $data['MedPersonal_id'];
					}
				break;

				case 5:
				case 9:
					$join .= "
						left join v_EvnVizitDispOrp evdo (nolock) on evdo.EvnVizitDispOrp_id = RE.Evn_id
						left join v_EvnUslugaDispOrp eudo (nolock) on eudo.EvnUslugaDispOrp_pid = evdo.EvnVizitDispOrp_id
						left join v_UslugaComplex U (nolock) on U.UslugaComplex_id = eudo.UslugaComplex_id
						outer apply(
							select top 1 Person_Fio
							from v_MedPersonal with (nolock)
							where MedPersonal_id = evdo.MedPersonal_id
						) as MP
					";
					$fields .= "
						U.UslugaComplex_Code as Usluga_Code,
						ISNULL(MP.Person_Fio, RD.MedPersonal_Fio) as MedPersonal_Fio,
					";
					if (!empty($data['MedPersonal_id'])) {
						$filter .= " and ISNULL(evdo.MedPersonal_id, RD.MedPersonal_id) = :MedPersonal_id";
						$params['MedPersonal_id'] = $data['MedPersonal_id'];
					}
				break;

				default:
					$fields .= "
						RD.MedPersonal_Fio,
					";
					if (!empty($data['MedPersonal_id'])) {
						$filter .= " and RD.MedPersonal_id = :MedPersonal_id";
						$params['MedPersonal_id'] = $data['MedPersonal_id'];
					}
				break;
			}
		}

		if (getRegionNick() == 'ekb') {
			$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
			if (!empty($diagFilter)) {
				$join .= "left join v_Diag D with (nolock) on D.Diag_id = RD.Diag_id ";
				$filter .= " and {$diagFilter}";
			}
		}

		if ( in_array($this->RegistryType_id, array(7, 9, 12, 17)) ) {
			$join .= "left join EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = COALESCE(RD.Evn_rid, RE.Evn_rid, RE.Evn_id) ";
			$fields .= "epd.DispClass_id, ";
		}
		//Начало и окончание для поликлиники http://redmine.swan.perm.ru/issues/64952
		if ( in_array($this->RegistryType_id, array(2)) && ($this->regionNick == 'kareliya') ) {
			$join .= " outer apply (
						select top 1
							EvnViz.EvnVizit_setDT as maxEvn_setDate
						from v_EvnVizit EvnViz with (nolock)
						where EvnViz.EvnVizit_id = RE.Evn_id
						order by EvnViz.EvnVizit_setDT DESC
					) maxSetDate 
					outer apply (
						select top 1
							EvnViz.EvnVizit_setDT as minEvn_setDate
						from v_EvnVizit EvnViz with (nolock)
						where EvnViz.EvnVizit_rid = RE.Evn_rid
						order by EvnViz.EvnVizit_setDT ASC
					) minSetDate ";
			$datesSetEnd = "convert(varchar(10), maxSetDate.maxEvn_setDate, 104) as Evn_disDate, 
							convert(varchar(10), minSetDate.minEvn_setDate, 104) as Evn_setDate, ";
		} else {
			$datesSetEnd = "convert(varchar(10), RE.Evn_setDate, 104) as Evn_setDate,
							convert(varchar(10), RE.Evn_disDate, 104) as Evn_disDate,";
		}

		if ( $this->regionNick == 'ufa' && $data['RegistryType_id'] == 19 ) {
			$join .= " left join v_UslugaComplex as uc on uc.UslugaComplex_id = RD.UslugaComplex_id ";

			$fields .= "RD.MedPersonal_LabFIO,";
			$fields .= "uc.UslugaComplex_Code,";
			$fields .= "uc.UslugaComplex_Name,";
		}

		if (!empty($data['ExtendedFilterField']) && !empty($data['ExtendedFilterFieldValue'])) {
			$filter .= " and " . $data['ExtendedFilterField'] . " like '%' + :ExtendedFilterFieldValue + '%'";
			$params['ExtendedFilterFieldValue'] = $data['ExtendedFilterFieldValue'];
		}

		if(in_array($this->regionNick, array('ekb','buryatiya')) && $data['RegistryType_id'] == 15){
			$join .= "left join v_EvnUslugaPar EUP (nolock) on EUP.EvnUslugaPar_id = RE.Evn_id ";
			$join .= "left join v_EvnFuncRequest efr (nolock) on efr.EvnFuncRequest_pid = EUP.EvnDirection_id ";
			$join .= "left join v_EvnLabRequest  elr (nolock) on elr.EvnDirection_id = EUP.EvnDirection_id ";
			$fields .= "case when efr.EvnFuncRequest_id is not null then 'true' else 'false' end as isEvnFuncRequest, ";
			$fields .= "case when elr.EvnLabRequest_id is not null then 'true' else 'false' end as isEvnLabRequest, ";
			$fields .= "elr.MedService_id, ";
			$fields .= "EUP.EvnDirection_id, ";
		}

		$orderBy = "
			RE.RegistryErrorType_Code,
			RE.Registry_id,
			RE.RegistryErrorType_id,
			RE.Evn_id
		";
		if (!empty($data['sort']) && $this->regionNick == 'buryatiya') {
			$sortDir = ('DESC' == $data['dir']) ? 'DESC' : 'ASC';
			switch($data['sort']) {
				case 'RegistryErrorType_Code':
					$data['sort']="cast(RE.RegistryErrorType_Code as BIGINT)";
					break;
			}
			$orderBy = " {$data['sort']} {$sortDir}";
			$params['sort'] = $data['sort'];
		}
		
		$query = "
			Select
				-- select
				ROW_NUMBER() OVER (ORDER by RE.Registry_id, RE.RegistryErrorType_id, RE.Evn_id) as RegistryError_id,
				RE.Registry_id,
				RE.{$EvnIdField} as Evn_id,
				{$Evn_ident}
				RE.Evn_rid,
				RE.EvnClass_id,
				RE.RegistryErrorType_id,
				RE.RegistryErrorType_Code,
				{$fields}
				RTrim(RE.RegistryErrorType_Name) as RegistryErrorType_Name,
				RE.RegistryError_Desc,
				RE.RegistryErrorType_Descr,
				RE.Person_id,
				RE.Server_id,
				RE.PersonEvn_id,
				RTrim(RE.Person_FIO) as Person_FIO,
				convert(varchar(10), RE.Person_BirthDay, 104) as Person_BirthDay,
				CASE WHEN RE.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RE.LpuSection_id,
				RTrim(RE.LpuSection_name) as LpuSection_name,
				{$datesSetEnd}
				RE.RegistryErrorClass_id,
				RTrim(RE.RegistryErrorClass_Name) as RegistryErrorClass_Name,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
				-- end select
			from
				-- from
				{$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK)
				{$join}
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
				{$filterAddQuery}
				-- end where
			order by
				-- order by
				{$orderBy}
				-- end order by
		";

		if ($returnQueryOnly === true) {
			return [
				'query' => $query,
				'params' => $params,
			];
		}

		if (!empty($data['nopaging'])) {

			$result = $this->db->query($query, $params);

			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return false;
			}
		}
		//echo getDebugSQL($query, $params);exit;

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadRegistryErrorFilter($data)
	{
		if (in_array($this->regionNick, ['buryatiya','kareliya','penza','ufa'])) {
			return $this->_loadRegistryErrorFilter($data);
		}

		if ($data['Registry_id']<=0)
		{
			return false;
		}
		if (empty($data['nopaging'])) {
			if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
			{
				return false;
			}
		}

		$this->setRegistryParamsByType($data);

		//Фильтр грида
		$json = isset($data['Filter']) ? toUTF(trim($data['Filter'],'"')) : false;
		//echo $json.'<br/>';
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false;


		$params = array(
			'Registry_id' => $data['Registry_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'Value'=>($filter_mode['value'] == "_") ? "%%" : trim(toAnsi($filter_mode['value']))."%"
		);
		$filter="(1=1)";

		$join = "";
		$fields = "";

		if($filter_mode['type'] == 'unicFilter')
		{
			$prefix = '';
			//Подгоняем поля под запрос с WITH
			if($filter_mode['cell'] == 'Person_FIO'){
				$field = 'RE.Person_FIO';
				$orderBy = 'RE.Person_FIO';
			}
			elseif($filter_mode['cell'] == 'Usluga_Code'){
				if($data['RegistryType_id'] != 1){
					$field = 'U.UslugaComplex_Code';
					$orderBy = 'U.UslugaComplex_Code';
				}
				else{
					$field = 'm.Mes_Code';
					$orderBy = 'm.Mes_Code';
				}
			}
			elseif($filter_mode['cell'] == 'LpuSection_name'){
				$field = 'RE.LpuSection_name';
				$orderBy = 'RE.LpuSection_name';
			}
			elseif($filter_mode['cell'] == 'LpuBuilding_Name'){
				$field = 'LB.LpuBuilding_Name';
				$orderBy = 'LB.LpuBuilding_Name';
			}
			elseif($filter_mode['cell'] == 'LpuSectionProfile_Name'){
				$field = 'LSP.LpuSectionProfile_Name';
				$orderBy = 'LSP.LpuSectionProfile_Name';
			}
			elseif($filter_mode['cell'] == 'Evn_ident'){
				$field = 'RE.Evn_id';
				$orderBy = 'RE.Evn_id';
				if (in_array($this->regionNick, array('kaluga')) && $data['RegistryType_id'] == 1) {
					$field = 'RE.Evn_rid';
					$orderBy = 'RE.Evn_rid';
				}
				else if (in_array($this->regionNick, array('kareliya')) && $data['RegistryType_id'] == 1) {
					$field = 'RE.Evn_sid';
					$orderBy = 'RE.Evn_sid';
				}
			}
			else {
				$field = $filter_mode['cell'];
			}

			$orderBy = isset($orderBy) ?  $orderBy : $filter_mode['cell'];
			$Like = ($filter_mode['specific'] === false) ? "" : " and ".$orderBy." like  :Value";
			$with = "WITH";
			$distinct = 'DISTINCT';
		}
		else{
			return false;
		}

		$orderBy = isset($orderBy) ? $orderBy : null;

		$distinct = isset($distinct) ? $distinct : '';
		$with = isset($with) ? $with : '';

		//$view_db = $this->getRegistryDataObject($data);

		$query = "
		Select
			-- select
			RTrim(cast(RE.Registry_id as char))+RTrim(cast(IsNull(RE.Evn_id,0) as char))+RTrim(cast(RE.RegistryErrorType_id as char)) as RegistryError_id,
			RE.Registry_id,
			RE.Evn_id,
			RE.Evn_rid,
			RE.EvnClass_id,
			RE.RegistryErrorType_id,
			RE.RegistryErrorType_Code,
			{$fields}
			RTrim(RE.RegistryErrorType_Name) as RegistryErrorType_Name,
			RE.RegistryErrorType_Descr,
			RE.Person_id,
			RE.Server_id,
			RE.PersonEvn_id,
			RTrim(RE.Person_FIO) as Person_FIO,
			RTrim(IsNull(convert(varchar,cast(RE.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
			CASE WHEN RE.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
			RE.LpuSection_id,
			RTrim(RE.LpuSection_name) as LpuSection_name,
			RTrim(IsNull(convert(varchar,cast(RE.Evn_setDate as datetime),104),'')) as Evn_setDate,
			RTrim(IsNull(convert(varchar,cast(RE.Evn_disDate as datetime),104),'')) as Evn_disDate,
			RE.RegistryErrorClass_id,
			RTrim(RE.RegistryErrorClass_Name) as RegistryErrorClass_Name,
			case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
			-- end select
		from
			-- from
			{$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK)
			left join {$this->scheme}.v_RegistryData RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
			{$join}
			-- end from
		where
			-- where
			RE.Registry_id=:Registry_id
			and
			{$filter}
			-- end where
		group by
			-- group by
			{$field}
			-- end group by
		order by
			-- order by
			{$field}
			-- end order by     
		";
		//echo getDebugSQL($query,$params);die;
		if (!empty($data['nopaging'])) {
			$result = $this->db->query($query, $params);
			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return false;
			}
		}

		$result = $this->db->query($this->_getLimitSQLPH($query, $distinct, $field, $data['start'], $data['limit'], $Like, $orderBy), $params);
		$result_count = $this->db->query($this->_getCountSQLPH($query, $field, $distinct, $orderBy), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			if(is_array($cnt_arr) && sizeof($cnt_arr)){
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			}
			else
				return false;
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			//var_dump($response);die;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Неведомая функция
	 */
	function doRegistryPersonIsDifferent($data)
	{
		$this->setRegistryParamsByType($data);

		$query = "
			declare
				 @Error_Code bigint
				,@Person1_id bigint
				,@Person2_id bigint
				,@Error_Message varchar(4000);
			
			select
				@Person1_id = Person_id,
				@Person2_id = Person2_id
			from 
				{$this->scheme}.v_{$this->RegistryPersonObject} with (nolock)
			where
				Registry_id = :Registry_id
				and MaxEvnPerson_id = :MaxEvnPerson_id;

			if ( @Person1_id is not null and @Person2_id is not null )
				exec pd.p_PersonNotDoubles_ins 
					@Person_id = @Person1_id,
					@Person_did = @Person2_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
			else
				set @Error_Message = 'Не найдены данные в {$this->RegistryPersonObject}';

			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (empty($resp[0]['Error_Msg'])) {
				$query = "
					update {$this->scheme}.{$this->RegistryPersonObject} with (rowlock)
					set
						{$this->RegistryPersonObject}_IsDifferent = 2
					where
						Registry_id = :Registry_id
						and MaxEvnPerson_id = :MaxEvnPerson_id
				";
					
				$result = $this->db->query($query, $data);
			}
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 *	Комментарий
	 */
	function loadRegistryErrorType($data)
	{
		$this->setRegistryParamsByType($data);

		if ( empty($this->RegistryErrorObject) ) {
			return false;
		}

		$params = array();
		$join = "";
		$filter = "(1=1)";
		if (!empty($data['Registry_id'])) {
			$params = array('Registry_id'=>$data['Registry_id']);
			$join .= "
				cross apply(
					Select top 1 Evn_id
					from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (nolock)
					where RE.Registry_id = :Registry_id and RET.RegistryErrorType_id = RE.RegistryErrorType_id
				) as Registry
			";
		} else if ($data['dotted'] == 1) {
			$filter .= " and RegistryErrorType_Code like '%.%'";
		} else {
			return false;
		}

		$query = "
		Select distinct
			RET.RegistryErrorType_id,
			RET.RegistryErrorType_Code,
			RET.RegistryErrorType_Name,
			--RET.RegistryErrorType_Descr,
			RET.RegistryType_id,
			RET.RegistryErrorClass_id
		from {$this->scheme}.v_RegistryErrorType RET with (nolock)
			{$join}
		where {$filter}
		";
		/*
		 echo getDebugSql($query, $params);
		 exit;
		 */
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Список случаев по пациентам без документов ОМС
	 */
	function loadRegistryNoPolis($data)
	{
		$this->setRegistryParamsByType($data);

		$join = "";
		$filter = "(1=1)";
		$filterAddQueryTemp = null;
		$returnQueryOnly = isset($data['returnQueryOnly']);
		if(isset($data['Filter'])){
			$filterData = json_decode(toUTF(trim($data['Filter'],'"')), 1);

			if(is_array($filterData)){

				foreach($filterData as $column=>$value){

					if(is_array($value)){
						$r = null;

						foreach($value as $d){
							$r .= "'".trim(toAnsi($d))."',";
						}

						if($column == 'Evn_id')
							$column = 'RNP.'.$column;
						elseif($column == 'Person_FIO')
							$column = "rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, ''))";//'RE.'.$column;
						elseif($column == 'LpuSection_name')
							$column = "(rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name)";//'RD.'.$column;

						$r = rtrim($r, ',');

						$filterAddQueryTemp[] = $column.' IN ('.$r.')';
					}
				}

			}

			if(is_array($filterAddQueryTemp)){
				$filterAddQuery = "and ".implode(" and ", $filterAddQueryTemp);
			}
			else
				$filterAddQuery = "and (1=1)";
		}

		$filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;

		if ($data['Registry_id']<=0)
		{
			return false;
		}
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		$params = array('Registry_id' => $data['Registry_id']);
		if ($this->RegistryType_id == 6) {
			$set_date_time = " null as Evn_setDT";
		} else {
			$set_date_time = " convert(varchar(10), Evn.Evn_setDT, 104)+' '+convert(varchar(5), Evn.Evn_setDT, 108) as Evn_setDT";
		}

		if (getRegionNick() == 'ekb') {
			$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
			if (!empty($diagFilter)) {
				$join .= "left join {$this->scheme}.v_RegistryData RD with (NOLOCK) on RD.Registry_id = RNP.Registry_id and RD.Evn_id = RNP.Evn_id ";
				$join .= "left join v_Diag D with (nolock) on D.Diag_id = RD.Diag_id ";
				$filter .= " and {$diagFilter}";
			}
		}

		if (!empty($data['ExtendedFilterField']) && !empty($data['ExtendedFilterFieldValue'])) {
			$filter .= " and " . $data['ExtendedFilterField'] . " like '%' + :ExtendedFilterFieldValue + '%'";
			$params['ExtendedFilterFieldValue'] = $data['ExtendedFilterFieldValue'];
		}

		$query = "
			select
				-- select
				RNP.Registry_id,
				RNP.Evn_id,
				Evn.Evn_rid as Evn_rid,
				RNP.Person_id,
				Evn.Server_id,
				Evn.PersonEvn_id,
				rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, '')) as Person_FIO,
				RTrim(IsNull(convert(varchar,cast(RNP.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name as LpuSection_Name,
				{$set_date_time}
				-- end select
			from
				-- from
				{$this->scheme}.v_{$this->RegistryNoPolisObject} RNP with (NOLOCK)
				left join v_LpuSection LpuSection with (NOLOCK) on LpuSection.LpuSection_id = RNP.LpuSection_id
				left join v_Evn Evn with(nolock) on Evn.Evn_id = RNP.Evn_id
				{$join}
				-- end from
			where
				-- where
				RNP.Registry_id=:Registry_id
				and {$filter}
				{$filterAddQuery}
				-- end where
			order by
				-- order by
				RNP.Person_SurName,
				RNP.Person_FirName,
				RNP.Person_SecName,
				LpuSection.LpuSection_Name
				-- end order by
		";

		if ($returnQueryOnly === true) {
			return [
				'query' => $query,
				'params' => $params,
			];
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadRegistryNoPolisFilter($data)
	{
		if (in_array($this->regionNick, ['buryatiya','kareliya','penza','ufa'])) {
			return $this->_loadRegistryNoPolisFilter($data);
		}

		$this->setRegistryParamsByType($data);
		//Фильтр грида
		$json = isset($data['Filter']) ? toUTF(trim($data['Filter'],'"')) : false;
		//echo $json.'<br/>';
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false;


		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Value'=>($filter_mode['value'] == "_") ? "%%" : trim(toAnsi($filter_mode['value']))."%"
		);
		$filter="(1=1)";

		$join = "";
		$fields = "";

		if($filter_mode['type'] == 'unicFilter')
		{
			$prefix = '';
			//Подгоняем поля под запрос с WITH
			if($filter_mode['cell'] == 'Person_FIO'){
				//$field = 'RE.Person_FIO';
				$orderBy = "rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, ''))";
				$field = "rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, ''))";//'RE.'.$column;
			}
			/*elseif($filter_mode['cell'] == 'Usluga_Code'){
				if($data['RegistryType_id'] != 1){
					$field = 'U.UslugaComplex_Code';
					$orderBy = 'U.UslugaComplex_Code';
				}
				else{
					$field = 'm.Mes_Code';
					$orderBy = 'm.Mes_Code';
				}
			}*/
			elseif($filter_mode['cell'] == 'LpuSection_Name'){
				$field = "(rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name)";//'RE.LpuSection_name';
				$orderBy = "(rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name)";//'RE.LpuSection_name';
			}
			elseif($filter_mode['cell'] == 'Evn_id'){
				$field = 'RNP.Evn_id';
				$orderBy = 'RNP.Evn_id';
			}
			elseif($filter_mode['cell'] == 'Evn_ident'){
				$field = 'RNP.Evn_id';
				$orderBy = 'RNP.Evn_id';
				if (in_array($this->regionNick, array('kaluga')) && $data['RegistryType_id'] == 1) {
					$field = 'RNP.Evn_rid';
					$orderBy = 'RNP.Evn_rid';
				}
				else if (in_array($this->regionNick, array('kareliya')) && $data['RegistryType_id'] == 1) {
					$field = 'RNP.Evn_sid';
					$orderBy = 'RNP.Evn_sid';
				}
			}
			else {
				$field = $filter_mode['cell'];
			}

			$orderBy = isset($orderBy) ?  $orderBy : $filter_mode['cell'];
			$Like = ($filter_mode['specific'] === false) ? "" : " and ".$orderBy." like  :Value";
			$with = "WITH";
			$distinct = 'DISTINCT';
		}
		else{
			return false;
		}

		$orderBy = isset($orderBy) ? $orderBy : null;

		$distinct = isset($distinct) ? $distinct : '';
		$with = isset($with) ? $with : '';

		$query = "
			select
				-- select
				RNP.Registry_id,
				RNP.Evn_id,
				Evn.Evn_rid as Evn_rid,
				RNP.Person_id,
				Evn.Server_id,
				Evn.PersonEvn_id,
				rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, '')) as Person_FIO,
				RTrim(IsNull(convert(varchar,cast(RNP.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name as LpuSection_Name
				-- end select
			from 
				-- from
				{$this->scheme}.v_{$this->RegistryNoPolisObject} RNP with (NOLOCK)
				left join v_Evn Evn with (nolock) on Evn.Evn_id = RNP.Evn_id
				left join v_LpuSection LpuSection with (NOLOCK) on LpuSection.LpuSection_id = RNP.LpuSection_id
				-- end from
			where
				-- where
				RNP.Registry_id=:Registry_id
				and
				{$filter}
				-- end where
			group by
				-- group by
				{$field}
				-- end group by
		order by
			-- order by
			{$field}
			-- end order by 
		";
		if (!empty($data['nopaging'])) {
			$result = $this->db->query($query, $params);
			if ( is_object($result) ) {
				return $result->result('array');
			}
			else {
				return false;
			}
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 *	Комментарий
	 */
	function loadRegistryPerson($data)
	{
		if ($data['Registry_id']<=0)
		{
			return false;
		}
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$this->setRegistryParamsByType($data);

		if ( empty($this->RegistryDataObject) ) {
			return false;
		}

		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$fields = '';
		$filter = '';
		$join = '';
		if ( !in_array($this->regionNick, array('ufa','pskov','buryatiya','penza')) ) {
			$fields .= "
				RD.needReform,
				case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit,
			";
		}
		if ( in_array($this->regionNick, array('perm')) ) {
			$filter .= "
				and ISNULL(RP.{$this->RegistryPersonObject}_IsMerge, 1) = 1
			";
		}

		if (getRegionNick() == 'ekb') {
			$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
			if (!empty($diagFilter)) {
				$join .= "left join v_Diag D with (nolock) on D.Diag_id = RD.Diag_id ";
				$filter .= " and {$diagFilter}";
			}
		}

		$query = "
			Select
				-- select
				RP.MaxEvnPerson_id,
				RP.MaxEvnPerson_id as PersonEvn_id,
				RP.Registry_id,
				RP.Person_id,
				RP.Person2_id,
				case when ISNULL(RP.Person2_SurName,'')!='' and RP.Person_SurName != RP.Person2_SurName then
					rtrim(IsNull(RP.Person_SurName,'')) + IsNull('<br/><font color=\"red\">'+RP.Person2_SurName+'<font>','')
				else
					rtrim(IsNull(RP.Person_SurName,''))
				end as Person_SurName,
				case when ISNULL(RP.Person2_FirName,'')!='' and RP.Person_FirName != RP.Person2_FirName then
					rtrim(IsNull(RP.Person_FirName,'')) + IsNull('<br/><font color=\"red\">'+RP.Person2_FirName+'<font>','')
				else
					rtrim(IsNull(RP.Person_FirName,''))
				end as Person_FirName,
				case when ISNULL(RP.Person2_SecName,'')!='' and RP.Person_SecName != RP.Person2_SecName then
					rtrim(IsNull(RP.Person_SecName,'')) + IsNull('<br/><font color=\"red\">'+RP.Person2_SecName+'<font>','')
				else
					rtrim(IsNull(RP.Person_SecName,''))
				end as Person_SecName,
				case when RP.Person2_BirthDay is not null and RP.Person_BirthDay != RP.Person2_BirthDay then
					rtrim(convert(varchar,cast(RP.Person_BirthDay as datetime),104)) + IsNull('<br/><font color=\"red\">'+rtrim(convert(varchar,cast(RP.Person2_BirthDay as datetime),104))+'<font>','')
				else
					rtrim(convert(varchar,cast(RP.Person_BirthDay as datetime),104))
				end as Person_BirthDay,
				{$fields}
				rtrim(IsNull(RP.Polis_Ser, '')) +' №'+rtrim(RP.Polis_Num)  + IsNull('<br/><font color=\"red\">'+rtrim(IsNull(RP.Polis2_Ser,'')) +' №'+rtrim(RP.Polis2_Num)+'<font>','') as Person_Polis,

				IsNull(convert(varchar,cast(RP.Polis2_begDate as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(RP.Polis2_endDate as datetime),104),'...') as Person_PolisDate,

				IsNull(convert(varchar,cast(Evn.Evn_setDT as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(RD.Evn_disDate as datetime),104),'...') as Person_EvnDate,

				IsNull(cast(OrgSMO.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO.OrgSMO_RegNomN as varchar(10)),'') + IsNull('<br/><font color=\"red\">'+IsNull(cast(OrgSMO2.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO2.OrgSMO_RegNomN as varchar(10)),'')+' '+RTrim(OrgSMO2.OrgSMO_Nick)+'<font>','') as Person_OrgSmo
			-- end select
			from
				-- from
				{$this->scheme}.v_{$this->RegistryPersonObject} RP with (NOLOCK)
			left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (NOLOCK) on RD.Registry_id = RP.Registry_id and RD.Evn_id = RP.MaxEvnPerson_id
			left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id
			left join Evn with (NOLOCK) on Evn.Evn_id = RD.Evn_id
			left join v_OrgSmo OrgSmo with (NOLOCK) on OrgSmo.OrgSmo_id = RP.OrgSmo_id
			left join v_OrgSmo OrgSmo2 with (NOLOCK) on OrgSmo2.OrgSmo_id = RP.OrgSmo2_id
			{$join}
			-- end from
			where
				-- where
				RP.Registry_id = :Registry_id
				and ISNULL(RP.{$this->RegistryPersonObject}_IsDifferent, 1) = 1
				{$filter}
				-- end where
			order by
				-- order by
				RP.Person_SurName, RP.Person_FirName
				-- end order by
		";

		/*
		echo getDebugSql($query, $params);
		exit;
		*/

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}
	
	/**
	 *	Список ошибок ТФОМС
	 */
	function loadRegistryErrorTFOMS($data)
	{
		if ($data['Registry_id']<=0)
		{
			return false;
		}
		$data['RegistryType_id'] = $this->getFirstResultFromQuery("SELECT RegistryType_id FROM {$this->scheme}.v_Registry with (nolock) WHERE Registry_id = :Registry_id", array('Registry_id'=>$data['Registry_id']));
		
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$this->setRegistryParamsByType($data);

		if ( empty($this->RegistryDataObject) ) {
			$response = array();
			$response['data'] = array();
			$response['totalCount'] = 0	;
			return $response;
		}

		$filter="(1=1)";
		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$returnQueryOnly = isset($data['returnQueryOnly']);

		if (isset($data['Person_SurName']))
		{
			$filter .= " and ps.Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and ps.Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and ps.Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryErrorType_Code']))
		{
			$filter .= " and RE.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}
		
		$addToSelect = "";
		$leftjoin = "";
		
		if ($data['session']['region']['nick'] == 'perm') {
			$addToSelect = ", retl.RegistryErrorTFOMSLevel_Name";
			$leftjoin .= " left join v_RegistryErrorTFOMSLevel retl with (nolock) on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id";
		}

		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$leftjoin .= " left join EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
			$addToSelect .= ", epd.DispClass_id";
		}

		if (!in_array($this->regionNick, array(/*'pskov'*/))) { $tempscheme = $this->scheme; } else { $tempscheme = 'dbo'; }

		if (in_array($this->regionNick, array('khak'))) {
			$leftjoin .= " left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = RD.PersonEvn_id and ps.Server_id = RD.Server_id ";
		}else{
			$leftjoin .= " left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id ";
		}

		$query = "
		Select 
			-- select
			RegistryErrorTFOMS_id,
			RE.Registry_id,
			Evn.Evn_rid,
			RE.Evn_id,
			Evn.EvnClass_id,
			ret.RegistryErrorType_Code,
			RegistryErrorType_Name as RegistryError_FieldName,
			RegistryErrorType_Descr + ' (' +RETF.RegistryErrorTFOMSField_Name + ')' as RegistryError_Comment,
			rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
			ps.Person_id, 
			ps.PersonEvn_id, 
			ps.Server_id, 
			RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
			RegistryErrorTFOMS_FieldName,
			RegistryErrorTFOMS_BaseElement,
			RegistryErrorTFOMS_Comment,
			ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
			case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
			{$addToSelect}
			-- end select
		from 
			-- from
			{$tempscheme}.v_RegistryErrorTFOMS RE with (nolock)
			left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
			left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
			left join RegistryErrorTFOMSField RETF with (nolock) on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
			left join RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
			{$leftjoin}
			-- end from
		where
			-- where
			RE.Registry_id=:Registry_id
			and
			{$filter}
			-- end where
		order by
			-- order by
			RE.RegistryErrorType_Code
			-- end order by
		";

		if ($returnQueryOnly === true) {
			return [
				'query' => $query,
				'params' => $params,
			];
		}

		//echo getDebugSql($query, $params);exit;

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 *	Список ошибок МЗ
	 */
	function loadRegistryHealDepResErrGrid($data)
	{
		if ($data['Registry_id'] <= 0) {
			return false;
		}
		$data['RegistryType_id'] = $this->getFirstResultFromQuery("SELECT RegistryType_id FROM {$this->scheme}.v_Registry with (nolock) WHERE Registry_id = :Registry_id", array('Registry_id' => $data['Registry_id']));

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		if (empty($this->RegistryDataObject)) {
			$response = array();
			$response['data'] = array();
			$response['totalCount'] = 0;
			return $response;
		}

		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$filter = "(1=1)";
		if (isset($data['Person_SurName'])) {
			$filter .= " and ps.Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName'] . "%";
		}
		if (isset($data['Person_FirName'])) {
			$filter .= " and ps.Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName'] . "%";
		}
		if (isset($data['Person_SecName'])) {
			$filter .= " and ps.Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName'] . "%";
		}
		if (isset($data['RegistryHealDepErrorType_Code'])) {
			$filter .= " and ret.RegistryHealDepErrorType_Code = :RegistryHealDepErrorType_Code ";
			$params['RegistryHealDepErrorType_Code'] = $data['RegistryHealDepErrorType_Code'];
		}
		if (isset($data['Person_FIO'])) {
			$filter .= " and rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO'] . "%";
		}
		if (!empty($data['Evn_id'])) {
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}

		$addToSelect = "";
		$leftjoin = "";

		if (in_array($data['RegistryType_id'], array(7, 9, 12))) {
			$leftjoin .= " left join EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
			$addToSelect .= ", epd.DispClass_id";
		}

		$query = "
		Select 
			-- select
			RE.RegistryHealDepResErr_id,
			RE.Registry_id,
			Evn.Evn_rid,
			RE.Evn_id,
			Evn.EvnClass_id,
			ret.RegistryHealDepErrorType_Code,
			ret.RegistryHealDepErrorType_Name,
			ret.RegistryHealDepErrorType_Descr,
			rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
			ps.Person_id, 
			ps.PersonEvn_id, 
			ps.Server_id, 
			RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
			ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
			case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
			{$addToSelect}
			-- end select
		from 
			-- from
			v_RegistryHealDepResErr RE with (nolock)
			left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
			left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
			left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
			left join v_RegistryHealDepErrorType ret with (nolock) on ret.RegistryHealDepErrorType_id = RE.RegistryHealDepErrorType_id
			{$leftjoin}
			-- end from
		where
			-- where
			RE.Registry_id=:Registry_id
			and
			{$filter}
			-- end where
		order by
			-- order by
			ret.RegistryHealDepErrorType_Code
			-- end order by";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 *	Список ошибок БДЗ
	 */
	function loadRegistryErrorBDZ($data)
	{
		if ($data['Registry_id']<=0)
		{
			return false;
		}

		$this->setRegistryParamsByType($data);

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$filter="(1=1)";
		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(isnull(re.Person_SurName,'')) + ' ' + rtrim(isnull(re.Person_FirName,'')) + ' ' + rtrim(isnull(re.Person_SecName, '')) like :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (!empty($data['RegistryErrorBDZType_id']))
		{
			if ($data['RegistryErrorBDZType_id'] == 1) {
				$filter .= " and RE.BDZ_id is null ";
			} else if ($data['RegistryErrorBDZType_id'] == 2) {
				$filter .= " and RE.BDZ_id is not null ";
			}
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if (!empty($data['RegistryType_id']) && $data['RegistryType_id'] == 6) {
			$query = "
			Select 
				-- select
				RegistryErrorBDZ_id,
				RE.Registry_id,
				CCC.CmpCallCard_id as Evn_rid,
				RE.Evn_id,
				null as EvnClass_id,
				case when (rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) <> rtrim(isnull(re.Person_SurName,'')) + ' ' + rtrim(isnull(re.Person_FirName,'')) + ' ' + rtrim(isnull(re.Person_SecName, ''))) then
					rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) + '<br/><font color=\"red\">'+rtrim(isnull(re.Person_SurName,'')) + ' ' + rtrim(isnull(re.Person_FirName,'')) + ' ' + rtrim(isnull(re.Person_SecName, ''))+'</font>'
				else
					rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))
				end as Person_FIO,
				ps.Person_id, 
				re.Person_id as Person2_id,
				ps.PersonEvn_id, 
				ps.Server_id, 
				case when RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) != RTrim(IsNull(convert(varchar,cast(re.Person_BirthDay as datetime),104),'')) then
					RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) + '<br/><font color=\"red\">'+RTrim(IsNull(convert(varchar,cast(re.Person_BirthDay as datetime),104),''))+'</font>'
				else
					RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),''))
				end as Person_BirthDay,
				case when rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,'')) <> rtrim(IsNull(re.Polis_Ser, '')) +' №'+rtrim(IsNull(re.Polis_Num,'')) then
					rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,'')) + '<br/><font color=\"red\">'+rtrim(IsNull(re.Polis_Ser, '')) +' №'+rtrim(IsNull(re.Polis_Num,'')) +'</font>'
				else 
					rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,''))
				end
				as Person_Polis,
				-- IsNull(convert(varchar,cast(pol.Polis_begDate as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(pol.Polis_endDate as datetime),104),'...') as Person_PolisDate,
				IsNull(convert(varchar,cast(CCC.CmpCallCard_prmDT as datetime),104),'...') as Person_EvnDate,
				IsNull(cast(OrgSMO.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO.OrgSMO_RegNomN as varchar(10)),'') + IsNull('<br/><font color=\"red\">'+IsNull(cast(OrgSMO2.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO2.OrgSMO_RegNomN as varchar(10)),'')+' '+RTrim(OrgSMO2.OrgSMO_Nick)+'<font>','') as Person_OrgSmo,
				RegistryErrorBDZ_Comment as RegistryError_Comment,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				RE.RegistryErrorBDZ_Comment
				-- end select
			from 
				-- from
				{$this->scheme}.v_RegistryErrorBDZ RE with (nolock)
				left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_CmpCallCard ccc with (nolock) on ccc.CmpCallCard_id = RE.Evn_id
				outer apply(
					select top 1
						 pa.PersonEvn_id
						,pa.Server_id
						,pa.Person_id
						,ISNULL(pa.Person_SurName, '') as Person_Surname
						,ISNULL(pa.Person_FirName, '') as Person_Firname
						,ISNULL(pa.Person_SecName, '') as Person_Secname
						,pa.Person_BirthDay as Person_Birthday
						,ISNULL(pa.Sex_id, 0) as Sex_id
						,pa.Person_EdNum
						,pa.Polis_id
					from
						v_Person_bdz pa with (nolock)
					where
						Person_id = CCC.Person_id
						and PersonEvn_insDT <= CCC.CmpCallCard_prmDT
					order by
						PersonEvn_insDT desc
				) PS
				left join v_Polis pol with (nolock) on pol.Polis_id = ps.Polis_id
				left join v_OrgSmo OrgSmo with (nolock) on OrgSmo.OrgSmo_id = RE.OrgSmo_id
				left join v_OrgSmo OrgSmo2 with (nolock) on OrgSmo2.OrgSmo_id = RE.OrgSmo_bdzid
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
				-- end where
			order by
				-- order by
				RE.RegistryErrorBDZ_id
				-- end order by";
		} else {
			$query = "
			Select 
				-- select
				RegistryErrorBDZ_id,
				RE.Registry_id,
				Evn.Evn_rid,
				RE.Evn_id,
				Evn.EvnClass_id,
				case when (rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) <> rtrim(isnull(re.Person_SurName,'')) + ' ' + rtrim(isnull(re.Person_FirName,'')) + ' ' + rtrim(isnull(re.Person_SecName, ''))) then
					rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) + '<br/><font color=\"red\">'+rtrim(isnull(re.Person_SurName,'')) + ' ' + rtrim(isnull(re.Person_FirName,'')) + ' ' + rtrim(isnull(re.Person_SecName, ''))+'</font>'
				else
					rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))
				end as Person_FIO,
				ps.Person_id, 
				re.Person_id as Person2_id,
				ps.PersonEvn_id, 
				ps.Server_id, 
				case when RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) != RTrim(IsNull(convert(varchar,cast(re.Person_BirthDay as datetime),104),'')) then
					RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) + '<br/><font color=\"red\">'+RTrim(IsNull(convert(varchar,cast(re.Person_BirthDay as datetime),104),''))+'</font>'
				else
					RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),''))
				end as Person_BirthDay,
				case when rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,'')) <> rtrim(IsNull(re.Polis_Ser, '')) +' №'+rtrim(IsNull(re.Polis_Num,'')) then
					rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,'')) + '<br/><font color=\"red\">'+rtrim(IsNull(re.Polis_Ser, '')) +' №'+rtrim(IsNull(re.Polis_Num,'')) +'</font>'
				else 
					rtrim(IsNull(pol.Polis_Ser, '')) +' №'+rtrim(IsNull(pol.Polis_Num,''))
				end
				as Person_Polis,
				-- IsNull(convert(varchar,cast(pol.Polis_begDate as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(pol.Polis_endDate as datetime),104),'...') as Person_PolisDate,
				IsNull(convert(varchar,cast(Evn.Evn_setDT as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(Evn.Evn_disDate as datetime),104),'...') as Person_EvnDate,
				IsNull(cast(OrgSMO.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO.OrgSMO_RegNomN as varchar(10)),'') + IsNull('<br/><font color=\"red\">'+IsNull(cast(OrgSMO2.OrgSMO_RegNomC as varchar(10)),'') + '-' + IsNull(cast(OrgSMO2.OrgSMO_RegNomN as varchar(10)),'')+' '+RTrim(OrgSMO2.OrgSMO_Nick)+'<font>','') as Person_OrgSmo,
				RegistryErrorBDZ_Comment as RegistryError_Comment,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				RE.RegistryErrorBDZ_Comment
				-- end select
			from 
				-- from
				{$this->scheme}.v_RegistryErrorBDZ RE with (nolock)
				left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
				left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
				left join v_Polis pol with (nolock) on pol.Polis_id = ps.Polis_id
				left join v_OrgSmo OrgSmo with (nolock) on OrgSmo.OrgSmo_id = RE.OrgSmo_id
				left join v_OrgSmo OrgSmo2 with (nolock) on OrgSmo2.OrgSmo_id = RE.OrgSmo_bdzid
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
				-- end where
			order by
				-- order by
				RE.RegistryErrorBDZ_id
				-- end order by";
		}
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 *	Данные по реестру
	 */
	function loadRegData($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id']))?$data['Lpu_id']:$data['session']['lpu_id'], 'Registry_id' => $data['Registry_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';
		$filter .= ' and R.Registry_id = :Registry_id';

		$query = "
			select
				R.Registry_id,
				R.RegistryType_id,
				R.KatNasel_id,
				R.Registry_Num,
				Lpu.Lpu_Email,
				Lpu.Lpu_Nick
			from {$this->scheme}.v_Registry R
			left join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = R.Lpu_id
			where
			{$filter}
		";
		/*
		 echo getDebugSql($query, $params);
		 exit;
		 */
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Получение данных реестра для использования в выгрузке в DBF
	 */
	function loadRegistryForDbfUsing($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id']))?$data['Lpu_id']:$data['session']['lpu_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';

		if ( isset($data['Registry_id']) )
		{
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}
		$query = "
			select
				LP.Lpu_Ouz as HC,
				RTrim(R.Registry_Num) as NSH,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as DSH,
				isnull(Lpu_RegNomC, '') as RG,
				isnull(Lpu_RegNomN, '') as RNL,
				cast(isnull(R.Registry_Sum, 0.00) as float) as SUMS,
				0 as TS,
				1 as VR,
				1 as RE,
				'За выполненные услуги с TX1 по TX2' as TX,
				OB.OrgBank_Code as K_BANR,
				OB.OrgBank_KSchet as KS_BAN,
				case
					when RegistryType_id=4 then 9
					when RegistryType_id=5 then 7
					else '' end as MFO_BAN,
				ORS.OrgRSchet_RSchet as SCHET,
				1 as RC_P,
				368 as RN_P,
				RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),104),'')) as DISMEN,
				OG.Org_INN as INN,
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as DNP,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as DKP,
				OG.Org_Phone as TEL,
				OHGlav.OrgHeadPerson_Fio as GL,
				OHIspoln.OrgHeadPerson_Fio as ISP
			from {$this->scheme}.v_Registry R with (NOLOCK)
			inner join v_Lpu LP with(nolock) on LP.Lpu_id = R.Lpu_id
			inner join Org OG with(nolock) on LP.Org_id = OG.Org_id
			left join OrgRSchet ORS with(nolock) on R.OrgRSchet_id = ORS.OrgRSchet_id
			left join v_OrgBank OB with(nolock) on OB.OrgBank_id = ORS.OrgBank_id
			outer apply (
				select
					top 1 rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as OrgHeadPerson_Fio
				from OrgHead OH with(nolock)
				inner join v_PersonState PS with(nolock) on PS.Person_id = OH.Person_id
				where
					OH.Lpu_id=LP.Lpu_id and OH.OrgHeadPost_id = 1
			) as OHGlav
			outer apply (
				select
					top 1 rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as OrgHeadPerson_Fio
				from OrgHead OH with(nolock)
				inner join v_PersonState PS with(nolock) on PS.Person_id = OH.Person_id
				where
					OH.Lpu_id=LP.Lpu_id and OH.OrgHeadPost_id = 3
			) as OHIspoln
			where
			{$filter}
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
	 *	Получение данных по услугам для выгрузки реестров в DBF
	 */
	function loadRegistryUslDataForDbfUsing($type, $data)
	{
		if ($data['Registry_id']==0)
		{
			return false;
		}
		$params = array();
		$params['Registry_id'] = $data['Registry_id'];

		switch ($type)
		{
			case 1: //stac
				$query = "exec {$this->scheme}.p_RegistryPS_expRU @Registry_id = :Registry_id";
				break;
			case 2: //polka
			case 16: //stom
				$query = "exec {$this->scheme}.p_RegistryPL_expRU @Registry_id = :Registry_id";
				break;
			case 3: //receipt
				$query = "";
				break;
			case 4: //dd
				$query = "exec {$this->scheme}.p_RegistryDD_expRU @Registry_id = :Registry_id";
				break;
			case 5: //orp
				$query = "exec {$this->scheme}.p_RegistryOrp_expRU @Registry_id = :Registry_id";
				break;
			case 6: //smp
				$query = "exec {$this->scheme}.p_RegistrySmp_expRU @Registry_id = :Registry_id";
				break;
			case 7: //dd
				$query = "exec {$this->scheme}.p_RegistryDDFirst_expRU @Registry_id = :Registry_id";
				break;
			case 8: //dd
				$query = "exec {$this->scheme}.p_RegistryDDSecond_expRU @Registry_id = :Registry_id";
				break;
			case 9: //orp
				$query = "exec {$this->scheme}.p_RegistryOrpFirst_expRU @Registry_id = :Registry_id";
				break;
			case 10: //orp
				$query = "exec {$this->scheme}.p_RegistryOrpSecond_expRU @Registry_id = :Registry_id";
				break;
			case 11: //orp
				$query = "exec {$this->scheme}.p_RegistryProfSurvey_expRU @Registry_id = :Registry_id";
				break;
			case 12: //teen inspection
				$query = "exec {$this->scheme}.p_RegistryProfTeen_expRU @Registry_id = :Registry_id";
				break;
			case 14: //htm
				$query = "exec {$this->scheme}.p_RegistryPS_expRU @Registry_id = :Registry_id";
				break;
		}

		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Получение данных для выгрузки реестров в DBF
	 */
	function loadRegistryDataForDbfUsing($type, $data)
	{
		if ($data['Registry_id']==0)
		{
			return false;
		}
		$params = array();
		$params['Registry_id'] = $data['Registry_id'];

		switch ($type)
		{
			case 1: //stac
				$query = "exec {$this->scheme}.p_RegistryPS_exp @Registry_id = :Registry_id";
				break;
			case 2: //polka
			case 16: //stom
				$query = "exec {$this->scheme}.p_RegistryPL_exp @Registry_id = :Registry_id";
				break;
			case 3: //receipt
				$query = "";
				break;
			case 4: //dd
				$query = "exec {$this->scheme}.p_RegistryDD_exp @Registry_id = :Registry_id";
				break;
			case 5: //orp
				$query = "exec {$this->scheme}.p_RegistryOrp_exp @Registry_id = :Registry_id";
				break;
			case 6: //smp
				$query = "exec {$this->scheme}.p_RegistrySmp_exp @Registry_id = :Registry_id";
				break;
			case 7: //dd
				$query = "exec {$this->scheme}.p_RegistryDDFirst_exp @Registry_id = :Registry_id";
				break;
			case 8: //dd
				$query = "exec {$this->scheme}.p_RegistryDDSecond_exp @Registry_id = :Registry_id";
				break;
			case 9: //orp
				$query = "exec {$this->scheme}.p_RegistryOrpFirst_exp @Registry_id = :Registry_id";
				break;
			case 10: //orp
				$query = "exec {$this->scheme}.p_RegistryOrpSecond_exp @Registry_id = :Registry_id";
				break;
			case 11: //orp
				$query = "exec {$this->scheme}.p_RegistryProfSurvey_exp @Registry_id = :Registry_id";
				break;
			case 12: //orp
				$query = "exec {$this->scheme}.p_RegistryProfTeen_exp @Registry_id = :Registry_id";
				break;
			case 14: //htm
				$query = "exec {$this->scheme}.p_RegistryPS_exp @Registry_id = :Registry_id";
				break;
		}
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			//Вместо сгенерированных данных результата возвращаем сам объект результата
			//данные из него будем получать по строкам. Память то не резиновая.
			return $result;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 *	Получение данных для выгрузки реестров в XML
	 */
	function loadRegistryDataForXmlUsingCommon($type, $data/*, &$number, &$Registry_EvnNum*/)
	{
		$person_field = "ID_PAC";
		$paytype = '';
		if (isset($data['PayType_SysNick']) && ($data['PayType_SysNick']=='ovd')) {
			$paytype = 'OVD';
		}
		switch ($type)
		{
			case 1: //stac
				$p_schet = $this->scheme.".p_Registry_EvnPS".$paytype."_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPS".$paytype."_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPS".$paytype."_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPS".$paytype."_expPac";
				break;
			case 2: //polka
			case 16: //stom
				$p_schet = $this->scheme.".p_Registry_EvnPL".$paytype."_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPL".$paytype."_expVizit";
				switch ($this->scheme)
				{
					case "r2":
					case "r60":
						$p_usl = $this->scheme.".p_Registry_EvnPL".$paytype."_expUsl";
						$person_field = "ID_PERS";
						break;
					default: 
						$p_usl = $this->scheme.".p_Registry_EvnPL".$paytype."_expUsl";
						$person_field = "ID_PAC";
						break;
				}
				$p_pers = $this->scheme.".p_Registry_EvnPL".$paytype."_expPac";
				break;			
			case 4: //dd
				$p_schet = $this->scheme.".p_Registry_EvnPLDD_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPLDD_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLDD_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLDD_expPac";
				break;
			case 5: //orp
				$p_schet = $this->scheme.".p_Registry_EvnPLOrp_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPLOrp_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLOrp_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLOrp_expPac";
				break;
			case 6: //smp
				$p_schet = $this->scheme.".p_Registry_SMP_expScet";
				$p_vizit = $this->scheme.".p_Registry_SMP_expVizit";
				switch ($this->scheme)
				{
					case "r2":
						//
						break;
					default: 
						$p_usl = $this->scheme.".p_Registry_SMP_expUsl";
						break;
				}
				$p_pers = $this->scheme.".p_Registry_SMP_expPac";
				break;
			case 7: //dd
				$p_schet = $this->scheme.".p_Registry_EvnPLDD13_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPLDD13_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLDD13_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLDD13_expPac";
				break;
			case 8: //dd
				$p_schet = $this->scheme.".p_Registry_EvnPLDD13_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPLDD13_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLDD13_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLDD13_expPac";
				break;
			case 9: //orp
				$p_schet = $this->scheme.".p_Registry_EvnPLOrp13_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPLOrp13_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLOrp13_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLOrp13_expPac";
				break;
			case 10: //orp
				$p_schet = $this->scheme.".p_Registry_EvnPLOrp13_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPLOrp13_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLOrp13_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLOrp13_expPac";
				break;
			case 11: //orp
				$p_schet = $this->scheme.".p_Registry_EvnPLProf_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPLProf_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLProf_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLProf_expPac";
				break;
			case 12: //teen inspection
				$p_schet = $this->scheme.".p_Registry_EvnPLProfTeen_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnPLProfTeen_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnPLProfTeen_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnPLProfTeen_expPac";
				break;
			case 14: //htm
				$p_schet = $this->scheme.".p_Registry_EvnHTM_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnHTM_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnHTM_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnHTM_expPac";
				break;
			case 15: //par
				$p_schet = $this->scheme.".p_Registry_EvnUslugaPar_expScet";
				$p_vizit = $this->scheme.".p_Registry_EvnUslugaPar_expVizit";
				$p_usl = $this->scheme.".p_Registry_EvnUslugaPar_expUsl";
				$p_pers = $this->scheme.".p_Registry_EvnUslugaPar_expPac";
				break;
			default:
				return false;
		}
		// шапка
		$query = "
			exec {$p_schet} @Registry_id = ?
		";

		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$header = $result->result('array');

			if ( !is_array($header) || count($header) == 0 ) {
				return false;
			}
		}
		else {
			return false;
		}
        //var_dump($header);
		// посещения
		$query = "
			exec {$p_vizit} @Registry_id = ?
		";
		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$visits = $result->result('array');
			$SLUCH = array();
			// привязываем услуги к случаю
			foreach( $visits as $visit )
			{
				if ( !empty($visit['IDCASE']) ) {
					if ( !isset($SLUCH[$visit['IDCASE']]) ) {
						$SLUCH[$visit['IDCASE']] = array();
					}

					// https://redmine.swan.perm.ru/issues/32154
					// Костыль! Вынести в региональный контроллер!
					if ( $data['session']['region']['nick'] == 'ufa' ) {
						if ( !empty($visit['WEIGHT']) ) {
							$visit['VNOV_M'] = array(array(
								'WEIGHT' => $visit['WEIGHT']
							));

							unset($visit['WEIGHT']);
						}
						else {
							$visit['VNOV_M'] = array();
						}
					}

					$SLUCH[$visit['IDCASE']][] = $visit;
				}
			}
			unset($visits);
		}
		else {
			return false;
		}
				
		// услуги
		if (!empty($p_usl)) {
			$query = "
				exec {$p_usl} @Registry_id = ?
			";		
			$result = $this->db->query($query, array($data['Registry_id']));

			if ( is_object($result) ) {
				$uslugi = $result->result('array');
				$USL = array();
				// привязываем услуги к случаю
				$i = 1;
				foreach( $uslugi as $usluga )
				{
					if ( !isset($USL[$usluga['MaxEvn_id']]) ) {
						$USL[$usluga['MaxEvn_id']] = array();
					}

					if ( false && in_array($this->regionNick, array('ufa')) ) { // это решили убрать refs #81079 refs Кириллова Анастасия.
						// https://redmine.swan.perm.ru/issues/63987
						// Для стационара и ВМП услугу надо добавить столько раз, сколько указано в поле KOL_USL
						if ( in_array($type, array(1, 14)) && $usluga['KOL_USL'] > 1 ) {
							$KOL_USL = $usluga['KOL_USL'];
							$usluga['KOL_USL'] = 1;
						}
						else {
							$KOL_USL = 1;
						}

						for ( $j = 1; $j <= $KOL_USL; $j++ ) {
							$usluga['IDSERV'] = $i;
							$USL[$usluga['MaxEvn_id']][] = $usluga;
							$i++;
						}
					}
					else {
						// $usluga['IDSERV'] = $i; убрал по задаче #59966
						$USL[$usluga['MaxEvn_id']][] = $usluga;
						$i++;
					}
				}
				unset($uslugi);
			}
			else {
				return false;
			}
		}
		// люди
		$query = "
			exec {$p_pers} @Registry_id = ?
		";		
		$result = $this->db->query($query, array($data['Registry_id']));

		if ( is_object($result) ) {
			$person = $result->result('array');
			$PACIENT = array();
			// привязываем персона к случаю
			foreach( $person as $pers ) {
				if ( !empty($pers[$person_field]) ) {
					if ( $this->regionNick == 'perm' ) {
						$pers['DOST'] = array();
						$pers['DOST_P'] = array();

						if ( $pers['NOVOR'] == '0' ) {
							if ( empty($pers['FAM']) ) {
								$pers['DOST'][] = array('DOST_VAL' => 2);
							}

							if ( empty($pers['IM']) ) {
								$pers['DOST'][] = array('DOST_VAL' => 3);
							}

							if ( empty($pers['OT']) || mb_strtoupper($pers['OT']) == 'НЕТ' ) {
								$pers['DOST'][] = array('DOST_VAL' => 1);
							}
						}
						else {
							if ( empty($pers['FAM_P']) ) {
								$pers['DOST_P'][] = array('DOST_P_VAL' => 2);
							}

							if ( empty($pers['IM_P']) ) {
								$pers['DOST_P'][] = array('DOST_P_VAL' => 3);
							}

							if ( empty($pers['OT_P']) || mb_strtoupper($pers['OT_P']) == 'НЕТ' ) {
								$pers['DOST_P'][] = array('DOST_P_VAL' => 1);
							}
						}

						if ( count($pers['DOST']) == 0 ) {
							$pers['DOST'][] = array('DOST_VAL' => '');
						}

						if ( count($pers['DOST_P']) == 0 ) {
							$pers['DOST_P'][] = array('DOST_P_VAL' => '');
						}
					}

					$PACIENT[$pers[$person_field]] = $pers;
				}
			}
			unset($person);
		}
		else {
			return false;
		}
		// собираем массив для выгрузки
		$data = array();
		$data['SCHET'] = array($header[0]);
		// массив с записями
		$data['ZAP'] = array();
		foreach ( $PACIENT as $key => $value )
			$data['ZAP'][$key]['PACIENT'] = array($value);
		/*
		echo "<pre>";
		print_r($SLUCH);
		die();
		*/
		foreach($SLUCH as $key => $value )
		{
			foreach($value as $k => $val)
				if ( isset($USL[$key]) )
					$value[$k]['USL'] = $USL[$key];
				else
					$value[$k]['USL'] = $this->getEmptyUslugaXmlRow();
			$data['ZAP'][$key]['SLUCH'] = $value;

			if ( is_array($value) && count($value) > 0 ) {
				$data['ZAP'][$key]['PR_NOV'] = (array_key_exists('PR_NOV', $value[0]) ? $value[0]['PR_NOV'] : 0);
				$data['ZAP'][$key]['N_ZAP_P'] = (array_key_exists('N_ZAP_P', $value[0]) ? $value[0]['N_ZAP_P'] : null);
				$data['ZAP'][$key]['NSCHET_P'] = (array_key_exists('NSCHET_P', $value[0]) ? $value[0]['NSCHET_P'] : null);
				$data['ZAP'][$key]['DSCHET_P'] = (array_key_exists('DSCHET_P', $value[0]) ? $value[0]['DSCHET_P'] : null);
			}
		}

		switch ( $this->regionNick ) {
			case 'perm':
				foreach ( $data['ZAP'] as $key => $value ) {
					if ( isset($value['SLUCH']) ) {
						$data['ZAP'][$key]['N_ZAP'] = $value['SLUCH'][0]['IDCASE'];
					}
					else {
						unset($data['ZAP'][$key]);
					}
				}
			break;

			default:
				$i = 1;
				foreach ( $data['ZAP'] as $key => $value )
				{
					$data['ZAP'][$key]['N_ZAP'] = $i;
					$i++;
					if ( !isset($data['ZAP'][$key]['SLUCH']) )
						unset($data['ZAP'][$key]);
				}
			break;
		}
		$data['PACIENT'] = $PACIENT;
        //var_dump($data);
		return $data;
	}

	/**
	 *	Данные по очереди формирования реестров
	 */
	function loadRegistryQueue($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id'])) ? $data['Lpu_id'] : $data['session']['lpu_id']);
		$filter .= ' and RQ.Lpu_id = :Lpu_id';


		if (isset($data['Registry_id']))
		{
			$filter .= ' and RQ.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}

		$query = "
		Select
			RegistryQueue_id,
			RegistryQueue_Position
		from {$this->scheme}.v_RegistryQueue RQ with (NOLOCK)
		where
		{$filter}
			";
		//echo getDebugSQL($query, $params);

		$result = $this->db->query($query, $params);

		if ( is_object($result) )
		{
			$r = $result->result('array');
			if (count($r)==0)
			{
				// Сформировался реестр или ничего не было
				$r[0]['RegistryQueue_id'] = 0;
				$r[0]['RegistryQueue_Position'] = 0;
			}
			return $r;
		}
		else {
			return false;
		}
	}

    /**
	 *	Комментарий
	 */
	function loadRegistry($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => (isset($data['Lpu_id']))?$data['Lpu_id']:$data['session']['lpu_id']);
		$filter .= ' and R.Lpu_id = :Lpu_id';

		$this->setRegistryParamsByType($data);
		
		$addToSelect = "";
		$leftjoin = "";
		
		if (in_array(getRegionNick(), array('msk', 'perm'))) {
			$addToSelect .= ", R.DispClass_id, R.Registry_IsRepeated, R.PayType_id, pt.PayType_Name as PayType_Name";
		}

		if (in_array(getRegionNick(), ['astra'])) {
			if ( !empty($data['Registry_id']) ) {
				$addToSelect .= ", ISNULL(R.Registry_IsZNO, 1) as Registry_IsZNO";
			} else {
				$addToSelect .= ", case when R.Registry_IsZNO = 2 then 'Да' else '' end as Registry_IsZNO";
			}
		}
		
		if (isset($data['Registry_id']))
		{
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}
		if (isset($data['RegistryType_id']))
		{
			$filter .= ' and R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}

		$deletedRegistryStatus_id = 6;
		$queueRegistryStatus_id = 5;
		if (in_array(getRegionNick(), array('astra', 'kareliya'))) {
			if (empty($data['Registry_id'])) {
				if (!empty($data['PayType_SysNick']) && $data['PayType_SysNick'] == 'bud') {
					// реесты по бюджету
					$filter .= " and pt.PayType_SysNick in ('bud','fbud')";
				} else {
					$filter .= " and ISNULL(pt.PayType_SysNick, '') not in ('bud','fbud')";
				}
			}

			$deletedRegistryStatus_id = 12;
			$queueRegistryStatus_id = 11;
			$addToSelect .= ", pt.PayType_SysNick, r.PayType_id";
		}

		if (in_array(getRegionNick(), array('khak'))) {
			if ( !empty($data['Registry_id']) ) {
				$addToSelect .= ", ISNULL(R.Registry_IsFinanc, 1) as Registry_IsFinanc";
			} else {
				$addToSelect .= ", case when R.Registry_IsFinanc = 2 then 'true' else 'false' end as Registry_IsFinanc";
			}
		}
		
		$loadDeleted = (isset($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == $deletedRegistryStatus_id);
		$loadQueue = (isset($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == $queueRegistryStatus_id);

		if ($loadQueue) {
			//запрос для реестров в очереди
			if (($data['session']['region']['nick'] == 'perm') || ($data['session']['region']['nick'] == 'msk')) {
				$addToSelect .= ", 0 as MekErrors_IsData, 0 as FlkErrors_IsData, 0 as BdzErrors_IsData, 0 as Registry_SumPaid, '' as Registry_sendDate, Org_mid, OrgRSchet_mid";
			}
			
			$addToSelect .= $this->getLoadRegistryQueueAdditionalFields();
			$leftjoin .= $this->getLoadRegistryAdditionalJoin();
			
			$query = "
			Select
				R.RegistryQueue_id as Registry_id,
				R.KatNasel_id,
				R.RegistryType_id,
				{$queueRegistryStatus_id} as RegistryStatus_id,
				R.RegistryStacType_id,
				2 as Registry_IsActive,
				RTrim(R.Registry_Num)+' / в очереди: '+LTrim(cast(RegistryQueue_Position as varchar)) as Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as Registry_begDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as Registry_endDate,
				--R.Registry_Sum,
				KatNasel.KatNasel_Name,
				KatNasel.KatNasel_SysNick,
				RTrim(RegistryStacType.RegistryStacType_Name) as RegistryStacType_Name,
				R.Lpu_id,
				R.OrgRSchet_id,
				R.LpuBuilding_id,
				LpuBuilding.LpuBuilding_Name,
				0 as Registry_Count,
				0 as Registry_RecordPaidCount,
				0 as Registry_KdCount,
				0 as Registry_KdPaidCount,
				0 as Registry_Sum,
				1 as Registry_IsProgress,
				1 as Registry_IsNeedReform,
				'' as Registry_updDate,
				-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay
				0 as RegistryErrorCom_IsData,
				0 as RegistryError_IsData,
				0 as RegistryPerson_IsData,
				0 as RegistryNoPolis_IsData,
				0 as RegistryNoPay_IsData,
				0 as RegistryErrorTFOMS_IsData,
				0 as RegistryErrorTFOMSType_id,
				0 as RegistryNoPay_Count,
				0 as RegistryNoPay_UKLSum,
				0 as RegistryNoPaid_Count, 
				null as RegistryCheckStatus_id,
				-1 as RegistryCheckStatus_Code,
				'' as RegistryCheckStatus_Name,
				null as RegistryCheckStatus_SysNick,
				1 as Registry_IsNeedReform,
				0 as RegistryHealDepCheckJournal_AccRecCount,
				0 as RegistryHealDepCheckJournal_DecRecCount,
				0 as RegistryHealDepCheckJournal_UncRecCount
				{$addToSelect}
			from {$this->scheme}.v_RegistryQueue R with (NOLOCK)
			left join KatNasel with (nolock) on KatNasel.KatNasel_id = R.KatNasel_id
			left join LpuBuilding with (nolock) on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
			left join RegistryStacType with (nolock) on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
			left join v_PayType pt with (nolock) on pt.PayType_id = R.PayType_id
			{$leftjoin}
			where {$filter}";
		}
		else
		{//для всех реестров, кроме тех что в очереди
			$source_table = 'v_Registry';
			if (isset($data['RegistryStatus_id']))
			{
				if ($loadDeleted) {
					// если запрошены удаленные реестры
					$source_table = 'v_Registry_deleted';
					//т.к. для удаленных реестров статус не важен - не накладываем никаких условий на статус реестра.
				} else {
					$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
					$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
				}
				// только если оплаченные!!!
				// или удаленные
				if( 4 == (int)$data['RegistryStatus_id'] || 12 == (int)$data['RegistryStatus_id'] ) {
					if( $data['Registry_accYear'] > 0 ) {
						$filter .= ' and year(R.Registry_begDate) = :Registry_accYear';
						$params['Registry_accYear'] = $data['Registry_accYear'];
					}
				}
			}
			if (!in_array($this->regionNick, array('ufa','pskov','buryatiya','penza'))) { $tempscheme = $this->scheme; } else { $tempscheme = 'dbo'; }
			
			if ($data['session']['region']['nick'] == 'perm') {
				$addToSelect .= ", RegistryErrorMEK.MekErrors_IsData, RegistryErrorFLK.FlkErrors_IsData, RegistryErrorBDZ.BdzErrors_IsData,
				RTrim(IsNull(convert(varchar,cast(R.Registry_sendDT as datetime),104),''))+' '+RTrim(IsNull(convert(varchar,cast(R.Registry_sendDT as datetime),108),'')) as Registry_sendDate,
				isnull(R.Registry_SumPaid, 0.00) as Registry_SumPaid, R.DispClass_id, R.Registry_IsRepeated, DispClass_Name
				";
				$leftjoin .= " 
					left join v_DispClass DispClass (nolock) on R.DispClass_id = DispClass.DispClass_id
					outer apply(
						select top 1 case when RE.Registry_id is not null then 1 else 0 end as MekErrors_IsData 
						from 
							{$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) 
							left join RegistryErrorTFOMSType RET with (nolock) on RET.RegistryErrorTFOMSType_id = RE.RegistryErrorTFOMSType_id
						where RE.Registry_id = R.Registry_id and RET.RegistryErrorTFOMSType_SysNick = 'Err_MEK'
					) RegistryErrorMEK
					outer apply(
						select top 1 case when RE.Registry_id is not null then 1 else 0 end as FlkErrors_IsData 
						from 
							{$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) 
							left join RegistryErrorTFOMSType RET with (nolock) on RET.RegistryErrorTFOMSType_id = RE.RegistryErrorTFOMSType_id
						where RE.Registry_id = R.Registry_id and RET.RegistryErrorTFOMSType_SysNick = 'Err_FLK'
					) RegistryErrorFLK
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as BdzErrors_IsData from RegistryErrorBDZ RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorBDZ
				
				";
				$addToSelect .= ", ISNULL('<a href=''#'' onClick=''getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:' + CAST(R.Registry_id as varchar) + '});''>'+RegistryCheckStatus.RegistryCheckStatus_Name+'</a>','') as RegistryCheckStatus_Name";
				$addToSelect .= ", R.Org_mid, R.OrgRSchet_mid";
				if ( in_array($data['session']['region']['nick'], array('perm'))) {
					$addToSelect .= ", R.Registry_NoErrSum";
				}
			} else {
				$addToSelect .= ", RegistryCheckStatus.RegistryCheckStatus_Name";
				if ( in_array($data['session']['region']['nick'], array('astra', 'ekb', 'kaluga', 'kareliya', 'khak')) && $source_table != 'v_Registry_deleted' ) {
					$addToSelect .= ", 	R.OrgSMO_id";
				}
			}

			if ($this->regionNick == 'kareliya') {
				$addToSelect .= ", isnull(RegistryStom.RegistryStom_KdPlan, 0) as RegistryStom_KdPlan
				";
				$leftjoin .= "
					outer apply(
						select
							sum(RS.RegistryData_KdPlan) as RegistryStom_KdPlan
						from {$this->scheme}.v_RegistryData RS with (NOLOCK)
						inner join v_Evn Evn with(nolock) on RS.Evn_id = Evn.Evn_id
						where RS.Registry_id = R.Registry_id and Evn.EvnClass_SysNick in ('EvnVizitPLStom', 'EvnPLStom', 'EvnUslugaStom')
					) RegistryStom
				";
			}
			
			$addToSelect .= $this->getLoadRegistryAdditionalFields();
			$leftjoin .= $this->getLoadRegistryAdditionalJoin();
			
			$query = "
			Select
				R.Registry_id,
				R.KatNasel_id,
				R.RegistryType_id,
				" . (!empty($data['RegistryStatus_id']) && $deletedRegistryStatus_id == (int)$data['RegistryStatus_id'] ? "{$deletedRegistryStatus_id} as RegistryStatus_id" : "R.RegistryStatus_id") . ",
				R.RegistryStacType_id, 
				R.Registry_IsActive,
				RTrim(R.Registry_Num) as Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_begDate as datetime),104),'')) as Registry_begDate,
				RTrim(IsNull(convert(varchar,cast(R.Registry_endDate as datetime),104),'')) as Registry_endDate,
				--R.Registry_Sum,
				KatNasel.KatNasel_Name,
				KatNasel.KatNasel_SysNick,
				R.LpuBuilding_id,
				LpuBuilding.LpuBuilding_Name,
				RTrim(RegistryStacType.RegistryStacType_Name) as RegistryStacType_Name,
				R.Lpu_id,
				R.OrgRSchet_id,
				isnull(R.Registry_RecordCount, 0) as Registry_Count,
				isnull(R.Registry_RecordPaidCount, 0) as Registry_RecordPaidCount,
				isnull(R.Registry_KdCount, 0) as Registry_KdCount,
				isnull(R.Registry_KdPaidCount, 0) as Registry_KdPaidCount,
				isnull(R.Registry_Sum, 0.00) as Registry_Sum,
				".(($source_table=='v_Registry' && in_array($data['session']['region']['nick'], array('astra','kaluga','kareliya'))) ? " isnull(R.Registry_SumPaid, 0.00) as Registry_SumPaid, ":" ")."
				case when RQ.RegistryQueue_id is not null then 1 else 0 end as Registry_IsProgress,
				isnull(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				--isnull(RData.Registry_Count, 0) as Registry_Count,
				--isnull(RData.Registry_Sum, 0.00) as Registry_Sum,
				RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),104),''))+' '+
				RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),108),'')) as Registry_updDate,

				-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay
				RegistryErrorCom.RegistryErrorCom_IsData,
				RegistryError.RegistryError_IsData,
				RegistryPerson.RegistryPerson_IsData,
				RegistryNoPolis.RegistryNoPolis_IsData,
				RegistryErrorTFOMS.RegistryErrorTFOMS_IsData,
				RegistryErrorTFOMS.RegistryErrorTFOMSType_id,
				case when RegistryNoPay_Count>0 then 1 else 0 end as RegistryNoPay_IsData,
				RegistryNoPay.RegistryNoPay_Count as RegistryNoPay_Count,
				RegistryNoPay.RegistryNoPay_UKLSum as RegistryNoPay_UKLSum,
				RegistryNoPaid.RegistryNoPaid_Count as RegistryNoPaid_Count,
				convert(varchar, RQH.RegistryQueueHistory_endDT, 104) + ' ' + convert(varchar, RQH.RegistryQueueHistory_endDT, 108) as ReformTime,
				R.RegistryCheckStatus_id,
				ISNULL(RegistryCheckStatus.RegistryCheckStatus_Code,-1) as RegistryCheckStatus_Code,
				RegistryCheckStatus.RegistryCheckStatus_SysNick,
				case when exists (" . $this->getRegistryDoubleCheckQuery($tempscheme) . ") then 1 else 0 end as issetDouble,
				rhdcj.RegistryHealDepCheckJournal_AccRecCount,
				rhdcj.RegistryHealDepCheckJournal_DecRecCount,
				rhdcj.RegistryHealDepCheckJournal_UncRecCount
				{$addToSelect}
			from {$this->scheme}.{$source_table} R with (NOLOCK)
			left join KatNasel with (nolock) on KatNasel.KatNasel_id = R.KatNasel_id
			left join RegistryStacType with (NOLOCK) on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
			left join LpuBuilding with(nolock) on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
			left join RegistryCheckStatus with (NOLOCK) on RegistryCheckStatus.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			left join v_PayType pt with (nolock) on pt.PayType_id = R.PayType_id
			{$leftjoin}
			outer apply(
				select top 1 RegistryQueue_id
				from {$this->scheme}.v_RegistryQueue with (NOLOCK)
				where Registry_id = R.Registry_id
			) RQ
			outer apply(
				select top 1 RegistryQueueHistory_endDT
				from {$this->scheme}.RegistryQueueHistory with (NOLOCK)
				where Registry_id = R.Registry_id
					and RegistryQueueHistory_endDT is not null
				order by RegistryQueueHistory_id desc
			) RQH
			outer apply (
				select top 1
					rhdcj.RegistryHealDepCheckJournal_AccRecCount,
					rhdcj.RegistryHealDepCheckJournal_DecRecCount,
					rhdcj.RegistryHealDepCheckJournal_UncRecCount
				from
					v_RegistryHealDepCheckJournal rhdcj with (nolock)
				where
					rhdcj.Registry_id = r.Registry_id
				order by
					rhdcj.RegistryHealDepCheckJournal_Count desc,
					rhdcj.RegistryHealDepCheckJournal_id desc
			) rhdcj
			outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorCom_IsData from {$tempscheme}.v_{$this->RegistryErrorComObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorCom
			outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryError
			outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryPerson_IsData from {$this->scheme}.v_{$this->RegistryPersonObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id and ISNULL(RE.{$this->RegistryPersonObject}_IsDifferent, 1) = 1) RegistryPerson
			outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_{$this->RegistryNoPolis} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryNoPolis
			outer apply(
				select
					count(RegistryNoPay.Evn_id) as RegistryNoPay_Count,
					sum(RegistryNoPay.RegistryNoPay_UKLSum) as RegistryNoPay_UKLSum
				from {$this->scheme}.v_RegistryNoPay RegistryNoPay with (NOLOCK)
				where RegistryNoPay.Registry_id = R.Registry_id
			) RegistryNoPay
			outer apply(
				select
					count(RDnoPaid.Evn_id) as RegistryNoPaid_Count
				from {$this->scheme}.v_{$this->RegistryDataObject} RDnoPaid with (NOLOCK)
				where RDnoPaid.Registry_id = R.Registry_id and ISNULL(RDnoPaid.RegistryData_isPaid, 1) = 1
			) RegistryNoPaid
			outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData, RegistryErrorTFOMSType_id from {$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorTFOMS
			where
			{$filter}
			order by
				R.Registry_endDate DESC,
				RQH.RegistryQueueHistory_endDT DESC
				";
		}
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	*	Функция возрвращает массив годов, в которых есть реестры
	*/
	public function getYearsList($data)
	{
		$filterList = array();
		$joinList = array();

		if ( $data['RegistryStatus_id'] == 12 ) {
			$filterList[] = 'r.Registry_deleted = 2';
		}
		else {
			$filterList[] = 'r.RegistryStatus_id = :RegistryStatus_id';
		}

		if ( !empty($data['PayType_SysNick']) ) {
			$joinList[] = 'left join v_PayType pt on pt.PayType_id = r.PayType_id';

			switch ( $data['PayType_SysNick'] ) {
				case 'oms':
					$filterList[] = "pt.PayType_SysNick = 'oms'";
					break;

				case 'bud':
					$filterList[] = "pt.PayType_SysNick in ('bud', 'fbud')";
					break;
			}
		}

		$query = "
			select distinct
				YEAR(r.Registry_begDate) as reg_year
			from
				{$this->scheme}.Registry r with (nolock)
				" . implode(' and ', $joinList) . "
			where
				r.Lpu_id = :Lpu_id
				and r.RegistryType_id = :RegistryType_id
				and " . implode(' and ', $filterList) . "
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				$response = array(array('reg_year' => date('Y')));
			}

			return $response;
		} else {
			return false;
		}
	}

	/**
	 *	Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	function loadRegistryTypeNode($data)
	{
		$result = array(
			array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'),
			array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'),
			/*array('RegistryType_id' => 3, 'RegistryType_Name' => 'Рецепты'),*/
			/*array('RegistryType_id' => 4, 'RegistryType_Name' => 'Дополнительная диспансеризация'),*/
			/*array('RegistryType_id' => 5, 'RegistryType_Name' => 'Диспансеризация детей-сирот'),*/
			array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь'),
			array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения с 2013 года'),
			//array('RegistryType_id' => 8, 'RegistryType_Name' => 'Дисп-ция взр. населения 2-ой этап'),
			array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот с 2013 года'),
			//array('RegistryType_id' => 10, 'RegistryType_Name' => 'Дисп-ция детей-сирот 2-ой этап'),
			array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения'),
			//array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних'),
		);

		return $result;
	}

	/**
	 * Функция возрвращает набор данных для дерева реестра 2-го уровня (статус реестра)
	 */
	function loadRegistryStatusNode($data)
	{
		$result = array(
			array('RegistryStatus_id' => 5, 'RegistryStatus_Name' => 'В очереди'),
			array('RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'),
			array('RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'),
			array('RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные'),
			array('RegistryStatus_id' => 6, 'RegistryStatus_Name' => 'Удаленные')
		);
		return $result;
	}

	/**
	 * Функция возрвращает набор данных для дерева реестра 2-го уровня (статус реестра)
	 */
	function loadRegistryMzStatusNode($data)
	{
		$result = array(
			array('Status_SysNick' => 'new', 'Status_Name' => 'Новые'),
			array('Status_SysNick' => 'work', 'Status_Name' => 'На проверке'),
			array('Status_SysNick' => 'accepted', 'Status_Name' => 'Принятые'),
			array('Status_SysNick' => 'journal', 'Status_Name' => 'Журнал проверок')
		);
		return $result;
	}
	
	/**
	 * Получение дополнительных полей для сохранения реестра
	 */
	function getSaveRegistryAdditionalFields() {
		return "";
	}

	/**
	 *	Сохранение реестра
	 */
	function saveRegistry($data)
	{
		$addToQuery = $this->getSaveRegistryAdditionalFields();

		if (0 == $data['Registry_id'])
		{
			$data['Registry_IsActive']=2;
			$proc = 'p_Registry_ins';
		}
		else
		{
			$proc = 'p_Registry_upd';
		}
		$params = array
		(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['Lpu_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'],
			'OrgRSchet_id' => $data['OrgRSchet_id'],
			'Registry_accDate' => $data['Registry_accDate'],
			'KatNasel_id' => $data['KatNasel_id'],
			'Org_mid' => (!empty($data['Org_mid']) ? $data['Org_mid'] : null),
			'OrgRSchet_mid' => (!empty($data['OrgRSchet_mid']) ? $data['OrgRSchet_mid'] : null),
			'PayType_id' => (!empty($data['PayType_id']) ? $data['PayType_id'] : null),
			'DispClass_id' => (!empty($data['DispClass_id']) ? $data['DispClass_id'] : null),
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'Registry_IsRepeated' => (!empty($data['Registry_IsRepeated']) ? $data['Registry_IsRepeated'] : null),
			'pmUser_id' => $data['pmUser_id'],
			'reform' => $data['reform'],
		);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Registry_id bigint = :Registry_id,
				@curdate datetime = dbo.tzGetDate();
			exec ".$this->scheme.".".$proc. "
				@Registry_id = @Registry_id output,
				@Lpu_id = :Lpu_id,
				@RegistryType_id = :RegistryType_id,
				@RegistryStatus_id = :RegistryStatus_id,
				@Registry_begDate = :Registry_begDate,
				@Registry_endDate = :Registry_endDate,
				@KatNasel_id = :KatNasel_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@Registry_Num = :Registry_Num,
				@Registry_Sum = 0,
				@Registry_IsActive = :Registry_IsActive,
				@OrgRSchet_id = :OrgRSchet_id,
				@Registry_accDate = @curdate,
				@variant = 1,
				@reform = :reform,
				@pmUser_id = :pmUser_id,
				{$addToQuery}
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @Registry_id as Registry_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}

	/*
	function saveRegistry($data)
	{
		$procedure_name = '';
		return array('success' => false, 'Error_Msg' => 'Для использования необходимо актуализировать процедуру saveRegistry');
		// Сохранение нового реестра
		if (0 == $data['Registry_id'])
		{
		$data['Registry_IsActive']=2;
		$proc = 'p_Registry_ins';
		}
		else
		{
		$proc = 'p_Registry_upd';
		}
		$params = array
		(
		'Registry_id' => $data['Registry_id'],
		'Lpu_id' => $data['Lpu_id'],
		'RegistryType_id' => $data['RegistryType_id'],
		'RegistryStatus_id' => $data['RegistryStatus_id'],
		'Registry_begDate' => $data['Registry_begDate'],
		'Registry_endDate' => $data['Registry_endDate'],
		'Registry_Num' => $data['Registry_Num'],
		'Registry_IsActive' => $data['Registry_IsActive'],
		'OrgRSchet_id' => $data['OrgRSchet_id'],
		'Registry_accDate' => $data['Registry_accDate'],
		'pmUser_id' => $data['pmUser_id']
		);

		$query = "
		declare
		@Error_Code bigint,
		@Error_Message varchar(4000),
		@Registry_id bigint = :Registry_id,
		@curdate datetime = dbo.tzGetDate();
		exec " .$proc. "
		@Registry_id = @Registry_id output,
		@Lpu_id = :Lpu_id,
		@RegistryType_id = :RegistryType_id,
		@RegistryStatus_id = :RegistryStatus_id,
		@Registry_begDate = :Registry_begDate,
		@Registry_endDate = :Registry_endDate,
		@KatNasel_id = :KatNasel_id,
		@Registry_Num = :Registry_Num,
		@Registry_Sum = 0,
		@Registry_IsActive = :Registry_IsActive,
		@OrgRSchet_id = :OrgRSchet_id,
		@Registry_accDate = @curdate, -- :Registry_accDate
		@pmUser_id = :pmUser_id,
		@Error_Code = @Error_Code output,
		@Error_Message = @Error_Message output;
		select @Registry_id as Registry_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
		return $result->result('array');
		}
		else
		{
		return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
		}
	*/

	/**
	 * Различные региональные проверки перед переформированием
	 */
	public function checkBeforeSaveRegistryQueue($data)
	{
		if ( empty($data['Registry_id']) ) {
			return true;
		}

		// Проверка добавлена как защита от ушлых пользователей
		// @task https://redmine.swan.perm.ru/issues/119346
		$CurrentRegistryStatus_id = $this->getFirstResultFromQuery("SELECT RegistryStatus_id FROM {$this->scheme}.v_Registry with (nolock) WHERE Registry_id = :Registry_id", array('Registry_id' => $data['Registry_id']));

		if ( $CurrentRegistryStatus_id == 4 ) {
			return array(array('success' => false, 'Error_Msg' => 'Реестр оплачен, переформирование запрещено.'));
		}

		return true;
	}

	/**
	 * Проверка включен ли реестр в объединённый
	 */
	function checkRegistryInGroupLink($data) {
		return false;
	}

	/**
	 * Проверка на уникальность номера
	 */
	function checkRegistryNumUnique($data) {
		return true;
	}

	/**
	 * Проверка в архиве ли реестр
	 */
	function checkRegistryInArchive($data) {
		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$archive_database_date = $this->config->item('archive_database_date');
			if (!empty($data['Registry_id'])) {

				$Registry_begDate = $this->getFirstResultFromQuery("select convert(varchar(10), Registry_begDate, 104) from {$this->scheme}.v_Registry (nolock) where Registry_id = :Registry_id", $data);

				if (strtotime($Registry_begDate) < strtotime($archive_database_date)) {
					return true;
				}
			}
			else if ( !empty($data['Registry_begDate']) && strtotime($data['Registry_begDate']) < strtotime($archive_database_date) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Проверка возможности формирования реестра
	 */
	function checkRegistryTypeAllowedToReform($data) {
		if (!empty($data['RegistryType_id']) && in_array($data['RegistryType_id'], array(4,5))) {
			return false;
		}
		return true;
	}
	
	/**
	 *	saveRegistryQueue
	 *	Установка реестра в очередь на формирование
	 *	Возвращает номер в очереди
	 */
	function  saveRegistryQueue($data)
	{
		if ( !in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) )
		{
			return array(array('success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!'));
		}

		// Сохранение нового реестра
		if (0 == $data['Registry_id'])
		{
			$data['Registry_IsActive']=2;
			$operation = 'insert';
		}
		else
		{
			$operation = 'update';
		}

		// Возвращает строку
		$checkRegistryQueueDoubles = $this->checkRegistryQueueDoubles($data);

		if ( !empty($checkRegistryQueueDoubles) ) {
			return array(array('success' => false, 'Error_Msg' => $checkRegistryQueueDoubles));
		}
		
		if (!empty($data['Registry_id'])) {
			$resp = $this->checkBeforeSaveRegistryQueue($data);
			if (is_array($resp)) {
				return $resp;
			}

			$re = $this->loadRegistryQueue($data);
			if (is_array($re) && (count($re) > 0))
			{
				if ($re[0]['RegistryQueue_Position']>0)
				{
					return array(array('success' => false, 'Error_Msg' => '<b>Запрос ЛПУ по данному реестру уже находится в очереди на формирование.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания текущего формирования реестра.'));
				}
			}
		}

		$params = array
		(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['Lpu_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'RegistryStacType_id' => $data['RegistryStacType_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'],
			'OrgRSchet_id' => $data['OrgRSchet_id'],
			'Registry_accDate' => $data['Registry_accDate'],
			'KatNasel_id' => (!empty($data['KatNasel_id']) ? $data['KatNasel_id'] : null),
			'LpuFilial_id' => (!empty($data['LpuFilial_id']) ? $data['LpuFilial_id'] : null),
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id']) ? $data['LpuBuilding_id'] : null),
			'pmUser_id' => $data['pmUser_id']
		);
		$fields = "";

		switch ( $data['RegistryType_id'] ) {
			case 2:
			case 16:
				if (isset($data['reform']))
				{
					$params['reform'] = $data['reform'];
					$fields .= "@reform = :reform,";
				}
			break;
		}

		// https://redmine.swan.perm.ru/issues/10155
		if ( $data['session']['region']['nick'] == 'perm' ) {
			$params['Org_mid'] = $data['Org_mid'];
			$params['OrgRSchet_mid'] = $data['OrgRSchet_mid'];
			$params['PayType_id'] = $data['PayType_id'];
			$params['DispClass_id'] = $data['DispClass_id'];
			$params['Registry_IsRepeated'] = (!empty($data['Registry_id']) ? $this->getFirstResultFromQuery("select top 1 Registry_IsRepeated from v_Registry with (nolock) where Registry_id = :Registry_id", array('Registry_id' => $data['Registry_id'])): $data['Registry_IsRepeated']);
			$params['Registry_IsNew'] = $data['Registry_IsNew'];
			$params['LpuFilial_id'] = $data['LpuFilial_id'];

			$fields .= "@Org_mid = :Org_mid,";
			$fields .= "@OrgRSchet_mid = :OrgRSchet_mid,";
			$fields .= "@PayType_id = :PayType_id,";
			$fields .= "@DispClass_id = :DispClass_id,";
			$fields .= "@Registry_IsRepeated = :Registry_IsRepeated,";
			$fields .= "@Registry_IsNew = :Registry_IsNew,";
			$fields .= "@LpuFilial_id = :LpuFilial_id,";
		}
		// https://redmine.swan.perm.ru/issues/42644
		else if ( $data['session']['region']['nick'] == 'khak' ) {
			$params['OrgSMO_id'] = $data['OrgSMO_id'];
			$params['DispClass_id'] = $data['DispClass_id'];
			$params['Registry_IsOnceInTwoYears'] = $data['Registry_IsOnceInTwoYears'];
			$params['Registry_IsZNO'] = $data['Registry_IsZNO'];
			$params['Registry_IsFinanc'] = $data['Registry_IsFinanc'];

			$fields .= "@OrgSMO_id = :OrgSMO_id, @DispClass_id = :DispClass_id, @Registry_IsOnceInTwoYears = :Registry_IsOnceInTwoYears, @Registry_IsZNO = :Registry_IsZNO, @Registry_IsFinanc = :Registry_IsFinanc,";
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@RegistryQueue_id bigint = null,
				@RegistryQueue_Position bigint = null,
				@curdate datetime = dbo.tzGetDate();
			exec {$this->scheme}.p_RegistryQueue_ins
				@RegistryQueue_id = @RegistryQueue_id output,
				@RegistryQueue_Position = @RegistryQueue_Position output,
				@RegistryStacType_id = :RegistryStacType_id,
				@Registry_id = :Registry_id,
				@RegistryType_id = :RegistryType_id,
				@Lpu_id = :Lpu_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@OrgRSchet_id = :OrgRSchet_id,
				@KatNasel_id = :KatNasel_id,
				@Registry_begDate = :Registry_begDate,
				@Registry_endDate = :Registry_endDate,
				{$fields}
				@Registry_Num = :Registry_Num,
				@Registry_accDate = :Registry_accDate,
				@RegistryStatus_id = :RegistryStatus_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @RegistryQueue_id as RegistryQueue_id, @RegistryQueue_Position as RegistryQueue_Position, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 *	Переформирование реестра
	 */
	function reformRegistry($data)
	{
		$addToSelect = $this->getReformRegistryAdditionalFields();

		$query = "
			select
				--Registry_id,
				--Lpu_id,
				RegistryType_id,
				RegistryStatus_id,
				RegistryStacType_id,
				convert(varchar,cast(Registry_begDate as datetime),112) as Registry_begDate,
				convert(varchar,cast(Registry_endDate as datetime),112) as Registry_endDate,
				KatNasel_id,
				LpuBuilding_id,
				Registry_Num,
				Registry_Sum,
				Registry_IsActive,
				OrgRSchet_id,
				convert(varchar,cast(Registry_accDate as datetime),112) as Registry_accDate
				{$addToSelect}
			from
				{$this->scheme}.v_Registry Registry with (NOLOCK)
			where
				Registry_id = ?
		";

		$result = $this->db->query($query, array($data['Registry_id']));

		if (is_object($result))
		{
			$row = $result->result('array');
			if ( is_array($row) && count($row) > 0 )
			{
				foreach ( $row[0] as $key => $value ) {
					$data[$key] = $value;
				}
				$data['reformRegistry'] = true;
				// Переформирование реестра
				//return  $this->saveRegistry($data);
				// Постановка реестра в очередь
				return  $this->saveRegistryQueue($data);
			}
			else
			{
				return array('success' => false, 'Error_Msg' => 'Реестр не найден в базе');
			}
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}

	/**
	 *	Комментарий
	 */
	function reformErrRegistry($data)
	{
		$addToSelect = "";
		if ($data['session']['region']['nick'] == 'perm') {
			$addToSelect = ",Org_mid,OrgRSchet_mid,PayType_id,Registry_IsRepeated,LpuFilial_id";
		}
		if ( in_array($data['session']['region']['nick'], array('ekb', 'khak', 'kaluga', 'kareliya')) ) {
			$addToSelect = ",OrgSMO_id";
		}
		if ( in_array($data['session']['region']['nick'], array('perm', 'kaluga', 'kareliya', 'khak')) ) {
			$addToSelect = ",DispClass_id";
		}
		if ( in_array($data['session']['region']['nick'], array('khak')) ) {
			$addToSelect = ",Registry_IsOnceInTwoYears";
		}
		
		$query = "
			select
				Registry_id,
				Lpu_id,
				RegistryType_id,
				RegistryStatus_id,
				RegistryStacType_id,
				convert(varchar,cast(Registry_begDate as datetime),112) as Registry_begDate,
				convert(varchar,cast(Registry_endDate as datetime),112) as Registry_endDate,
				KatNasel_id,
				LpuBuilding_id,
				Registry_Num,
				Registry_Sum,
				Registry_IsActive,
				OrgRSchet_id,
				convert(varchar,cast(Registry_accDate as datetime),112) as Registry_accDate
				{$addToSelect}
			from
				{$this->scheme}.v_Registry Registry with (NOLOCK)
			where
				Registry_id = ?
		";

		$result = $this->db->query($query, array($data['Registry_id']));

		if (is_object($result))
		{
			$row = $result->result('array');
			if ( is_array($row) && count($row) > 0 )
			{
				//$data['Registry_id'] = $data['Registry_id'];
				//$data['Lpu_id'] = $data['Lpu_id'];
				$data['RegistryType_id'] = $row[0]['RegistryType_id'];
				$data['RegistryStatus_id'] = $row[0]['RegistryStatus_id'];
				$data['RegistryStacType_id'] = $row[0]['RegistryStacType_id'];
				$data['Registry_begDate'] = $row[0]['Registry_begDate'];
				$data['Registry_endDate'] = $row[0]['Registry_endDate'];
				$data['KatNasel_id'] = $row[0]['KatNasel_id'];
				if ($data['session']['region']['nick'] == 'perm') {
					$data['Org_mid'] = $row[0]['Org_mid'];
					$data['OrgRSchet_mid'] = $row[0]['OrgRSchet_mid'];
					$data['PayType_id'] = $row[0]['PayType_id'];
					$data['Registry_IsRepeated'] = $row[0]['Registry_IsRepeated'];
					$data['LpuFilial_id'] = $row[0]['LpuFilial_id'];
				}
				if ( in_array($data['session']['region']['nick'], array('perm', 'kaluga', 'kareliya', 'khak')) ) {
					$data['DispClass_id'] = $row[0]['DispClass_id'];
				}
				if ( in_array($data['session']['region']['nick'], array('ekb', 'kaluga', 'kareliya', 'khak')) ) {
					$data['OrgSMO_id'] = $row[0]['OrgSMO_id'];
				}
				if ( in_array($data['session']['region']['nick'], array('khak')) ) {
					$data['Registry_IsOnceInTwoYears'] = $row[0]['Registry_IsOnceInTwoYears'];
				}
				$data['LpuBuilding_id'] = $row[0]['LpuBuilding_id'];
				$data['Registry_Num'] = $row[0]['Registry_Num'];
				$data['Registry_IsActive'] = $row[0]['Registry_IsActive'];
				$data['OrgRSchet_id'] = $row[0]['OrgRSchet_id'];
				$data['Registry_accDate'] = $row[0]['Registry_accDate'];
				$data['reform'] = 1;
				//$data['pmUser_id'] = $data['pmUser_id'];
				// Переформирование реестра
				if ((isset($data['session']['setting']['lpu']['check_access_reform'])) && ($data['session']['setting']['lpu']['check_access_reform']==1)) // здесь надо добавить настройку-проверку, если ЛПУ можно выгружать реестры без постановки в очередь
				{
					if ($this->checkActualRecordRegistry($data)===true) // проверка на то, что все изменения по записям уже дошли на реплику
					{
						return  $this->saveRegistry($data);
					}
					else
					{
						return array('success' => false, 'Error_Msg' => 'Переформирование реестра на данный момент невозможно, <br/> поскольку не все измененные записи актуальны для базы реестров.<br/>Дождитесь синхронизации измененных данных и повторите попытку.');
					}
				}
				else
				{
					// Постановка реестра в очередь
					return  $this->saveRegistryQueue($data);
				}
			}
			else
			{
				return array('success' => false, 'Error_Msg' => 'Реестр не найден в базе');
			}
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}

	/**
	 *	Комментарий
	 */
	function closeRegistryQueueHistory($data)
	{

		if (0 != $data['Registry_id'])
		{
			$params =  array(
				'Registry_id' => $data['Registry_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$query = "
				Declare
					@Error_Code bigint = 0,
					@Error_Message varchar(4000) = '';
				set nocount on
				begin try
					update {$this->scheme}.RegistryQueueHistory with (rowlock)
					set
						RegistryQueueHistory_endDT = dbo.tzGetDate(),
						RegistryQueueHistory_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					where
						RegistryQueueHistory_id = :Registry_id
				end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
				set nocount off
				Select :Registry_id as RegistryQueue_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
			";

			$result = $this->db->query($query,$params);
			if (is_object($result))
			{
				return $result->result('array');
			}
			else
			{
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
	}

	/**
	 *	Установка признака необходимости переформирования реестра
	 */
	function setNeedReform($data)
	{

		if ((0 != $data['Registry_id']) && (0 != $data['Evn_id']))
		{
			$query = "
				exec {$this->scheme}.p_Registry_setNeedReform :Registry_id, :Evn_id, 2
			";

			$result = $this->db->query($query, array(
				'Registry_id' => $data['Registry_id'],
				'Evn_id' => $data['Evn_id']
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
		else
		{
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
	}

	/**
	 *	Установка статуса реестра
	 */
	function setRegistryStatus($data)
	{
		if ( empty($data['Registry_id']) || empty($data['RegistryStatus_id']) ) {
			return array(array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров'));
		}

		$this->setRegistryParamsByType($data);

		//#11018 При статусах "Готов к отправке в ТФОМС" и "Отправлен в ТФОМС" запретить перемещать реестр из состояния "К оплате".
		if (!isSuperAdmin() && !in_array($this->regionNick, array('ufa','pskov','buryatiya','penza'))) {
			$RegistryCheckStatus_id = $this->getFirstResultFromQuery("SELECT RegistryCheckStatus_id FROM {$this->scheme}.v_Registry WHERE Registry_id = :Registry_id", array('Registry_id'=>$data['Registry_id']));
			//"Готов к отправке в ТФОМС"
			if ($RegistryCheckStatus_id === '1') {
				throw new Exception("При статусе ''Готов к отправке в ТФОМС'' запрещено перемещать реестр из состояния ''К оплате''");
			}
			//"Отправлен в ТФОМС"
			if ($RegistryCheckStatus_id === '2') {
				throw new Exception("При статусе ''Отправлен в ТФОМС'' запрещено перемещать реестр из состояния ''К оплате''");
			}
			//"Проведён контроль (ФЛК)"
			if ($RegistryCheckStatus_id === '5') {
				throw new Exception("При статусе ''Проведен контроль (ФЛК)'' запрещено перемещать реестр из состояния ''К оплате''");
			}

			if ($this->regionNick == 'perm') {
				//"Принят частично"
				if ($RegistryCheckStatus_id === '4') {
					throw new Exception("При статусе ''Принят частично'' запрещено перемещать реестр из состояния ''К оплате''");
				}
				//"Принят"
				if ($RegistryCheckStatus_id === '15') {
					throw new Exception("При статусе ''Принят'' запрещено перемещать реестр из состояния ''К оплате''");
				}
			}
		}

		$r = $this->getFirstRowFromQuery("
			select top 1
				RegistryType_id,
				RegistryStatus_id
			from {$this->scheme}.v_Registry with (NOLOCK)
			where Registry_id = :Registry_id
		", [
			'Registry_id' => $data['Registry_id']
		]);

		if ( $r === false || !is_array($r) || count($r) == 0 ) {
			return [[ 'success' => false, 'Error_Msg' => 'Ошибка при получении данных реестра' ]];
		}

		$RegistryType_id = $r['RegistryType_id'];
		$RegistryStatus_id = $r['RegistryStatus_id'];

		$fields = "";

		// @task https://redmine.swan-it.ru/issues/185166
		// Снять отметку «к оплате»
		// если реестр включен в объединенный реестр, то выводится сообщение:
		// «Действие недоступно, так как реестр включен в объединенный реестр. Для продолжения работы с реестром удалите объединенный реестр. Ок»
		// При нажатии на кнопку «Ок» сообщение закрывается, состояние реестра не меняется.
		if ($this->regionNick == 'pskov' && $data['RegistryStatus_id'] == 3 && $RegistryStatus_id == 2) {
			$RegistryGroupLinkRecord = $this->getFirstRowFromQuery("
				select top 1
					convert(varchar(10), r.Registry_accDate, 104) as Registry_accDate,
					r.Registry_Num
				from {$this->scheme}.v_RegistryGroupLink as rgl with (nolock)
					inner join {$this->scheme}.v_Registry as r on r.Registry_id = rgl.Registry_pid
				where rgl.Registry_id = :Registry_id
			",[
				'Registry_id' => $data['Registry_id']
			]);

			if ( $RegistryGroupLinkRecord !== false && is_array($RegistryGroupLinkRecord) && !empty($RegistryGroupLinkRecord['Registry_Num']) ) {
				return [[ 'success' => false, 'Error_Msg' => 'Действие недоступно, так как реестр включен в объединенный реестр № ' . $RegistryGroupLinkRecord['Registry_Num'] . ' от ' . $RegistryGroupLinkRecord['Registry_accDate'] . '. Для продолжения работы с реестром удалите объединенный реестр.' ]];
			}
		}

		if ( $data['RegistryStatus_id'] == 3 ) { // если перевели в работу, то снимаем признак формирования
			$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, RegistryCheckStatus_id = null, ";
		}

		if (($data['RegistryStatus_id']==2) && (in_array($RegistryType_id, $this->getAllowedRegistryTypes())) && (isset($data['session']['setting']['server']['check_registry_exists_errors']) && $data['session']['setting']['server']['check_registry_exists_errors']==1) && (!isSuperadmin())) // если переводим "к оплате" и проверка установлена, и это не суперадмин то проверяем на ошибки
		{
			if (!in_array($this->regionNick, array('ufa','pskov','buryatiya','penza'))) { $tempscheme = $this->scheme; } else { $tempscheme = 'dbo'; }
			
			$query = "
				 Select
				(
					Select count(*) as err
					from {$this->scheme}.v_{$this->RegistryErrorObject} RegistryError with (NOLOCK)
						left join {$this->scheme}.v_{$this->RegistryDataObject} rd with (NOLOCK) on rd.Evn_id = RegistryError.Evn_id
						left join RegistryErrorType  with (NOLOCK) on RegistryErrorType.RegistryErrorType_id = RegistryError.RegistryErrorType_id
					where RegistryError.registry_id = :Registry_id
						and RegistryErrorType.RegistryErrorClass_id = 1
						and RegistryError.RegistryErrorClass_id = 1
						and IsNull(rd.RegistryData_deleted,1)=1
						and rd.Evn_id is not null
				) +
				(
					Select count(*) as err
					from {$tempscheme}.v_{$this->RegistryErrorComObject} RegistryErrorCom with (NOLOCK)
						left join RegistryErrorType  with (NOLOCK) on RegistryErrorType.RegistryErrorType_id = RegistryErrorCom.RegistryErrorType_id
					where registry_id = :Registry_id
						and RegistryErrorType.RegistryErrorClass_id = 1
				)
				as err
			";

			$r = $this->db->query($query, array('Registry_id' => $data['Registry_id']));
			if (is_object($r))
			{
				$res = $r->result('array');
				if ($res[0]['err']>0)
				{
					return array(array('success' => false, 'Error_Msg' => 'Невозможно отметить реестр "К оплате", так как в нем присутствуют ошибки.<br/>Пожалуйста, исправьте ошибки по реестру и повторите операцию.'));
				}
			}
		}

		// для Астрахани, Хакасии, Пскова
		if (in_array($this->getRegionNick(), array('astra', 'khak', 'pskov'))) {
			$CurrentRegistryStatus_id = $this->getFirstResultFromQuery("SELECT RegistryStatus_id FROM {$this->scheme}.v_Registry with (nolock) WHERE Registry_id = :Registry_id", array('Registry_id' => $data['Registry_id']));

			if ($data['RegistryStatus_id']==4) { // если переводим в оплаченные, то вызываем p_Registry_setPaid
				// https://redmine.swan.perm.ru/issues/65245
				if ( !isSuperadmin() && !havingGroup('RegistryUser') ) {
					return array(array('success' => false, 'Error_Msg' => 'Недостаточно прав для отметки реестра как оплаченного'));
				}

				$query = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000)
					exec {$this->scheme}.p_Registry_setPaid
						@Registry_id = :Registry_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select 4 as RegistryStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
				//echo getDebugSQL($query, $data);die;
				$result = $this->db->query($query, $data);
				if (!is_object($result))
				{
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к БД (отметка реестра как оплаченного)'));
				}

				$res = $result->result('array');

				if ( !is_array($res) || count($res) == 0 ) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке реестра как оплаченного'));
				}
				else if ( !empty($res[0]['Error_Msg']) ) {
					return array(array('success' => false, 'Error_Msg' => $res[0]['Error_Msg']));
				}
			}
			else if ($data['RegistryStatus_id'] == 2 && $CurrentRegistryStatus_id == 4) { // если переводим к оплате p_Registry_setUnPaid и реестр был в оплаченных
				$check154914 = $this->checkRegistryDataIsInOtherRegistry($data);

				if ( !empty($check154914) ) {
					return array(array('success' => false, 'Error_Msg' => $check154914));
				}

				$query = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000)
					exec {$this->scheme}.p_Registry_setUnPaid
						@Registry_id = :Registry_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select 2 as RegistryStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
				$result = $this->db->query($query, $data);

				if (!is_object($result))
				{
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к БД (перевод реестра в статус "К оплате")'));
				}

				$res = $result->result('array');

				if ( !is_array($res) || count($res) == 0 ) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при переводе реестра в статус "К оплате"'));
				}
				else if ( !empty($res[0]['Error_Msg']) ) {
					return array(array('success' => false, 'Error_Msg' => $res[0]['Error_Msg']));
				}
			}
		}

		$query = "
			Declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '',
				@RegistryStatus_id bigint =  :RegistryStatus_id
			set nocount on
			begin try
				update {$this->scheme}.Registry with (rowlock)
				set
					RegistryStatus_id = @RegistryStatus_id,
					Registry_updDT = dbo.tzGetDate(),
					{$fields}
					pmUser_updID = :pmUser_id
				where
					Registry_id = :Registry_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			Select @RegistryStatus_id as RegistryStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$result = $this->db->query($query, array(
			'Registry_id' => $data['Registry_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( !is_object($result) ) {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		if ( $data['RegistryStatus_id'] == 4 ) {
			// пишем информацию о смене статуса в историю
			$this->dumpRegistryInformation(array('Registry_id' => $data['Registry_id']), 4);
		}

		return $result->result('array');
	}

	/**
	 *	Комментарий
	 */
	function setRegistryActive($data)
	{

		if (0 != $data['Registry_id'])
		{
			$data['Registry_IsActive'] = 1;
			$query = "
				Declare
					@Error_Code bigint = 0,
					@Error_Message varchar(4000) = '',
					@Registry_IsActive bigint =  :Registry_IsActive
				set nocount on
				begin try
					update {$this->scheme}.Registry with (rowlock)
					set
						Registry_IsActive = @Registry_IsActive,
						Registry_updDT = dbo.tzGetDate(),
						pmUser_updID = :pmUser_id
					where
						Registry_id = :Registry_id
				end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
				set nocount off
				Select @Registry_IsActive as Registry_IsActive, @Error_Code as Error_Code, @Error_Message as Error_Msg
			";

			$result = $this->db->query($query, array(
				'Registry_id' => $data['Registry_id'],
			//'Lpu_id' => $data['Lpu_id'],
				'Registry_IsActive' => $data['Registry_IsActive'],
				'pmUser_id' => $data['pmUser_id']
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
		else
		{
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
	}
	
	/**
	 * Получаем статус отправки реестра
	 */
	function getRegistryCheckStatus($data)
	{
		if ((0 != $data['Registry_id']))
		{
			$query = "
				select
					IsNull(Registry.RegistryCheckStatus_id,0) as RegistryCheckStatus_id,
					rcs.RegistryCheckStatus_Name as RegistryCheckStatus_Name
				from {$this->scheme}.Registry
				left join RegistryCheckStatus rcs with(nolock) on rcs.RegistryCheckStatus_id = Registry.RegistryCheckStatus_id 
				where
					Registry_id = :Registry_id
			";
			/*
			echo getDebugSql($query, array(
					'Registry_id' => $data['Registry_id']
			));
			exit;
			*/

			$result = $this->db->query($query,
				array(
					'Registry_id' => $data['Registry_id']
				)
			);


			if (is_object($result))
			{
				$r = $result->result('array');
				if ( is_array($r) && count($r) > 0 )
				{
					return $r;
				}
			}
			else
			{
				return array(array('RegistryCheckStatus_id' => 0, 'RegistryCheckStatus_Name' => ''));
			}
		}
		else
		{
			return array(array('RegistryCheckStatus_id' => 0, 'RegistryCheckStatus_Name' => ''));
		}
	}

	/**
	 * Изменение отметки об оплате случаев
	 */
	function setRegistryDataPaidFromJSON($data)
	{
		if (!empty($data['RegistryDataPaid'])) {
			$RegistryDataPaid = json_decode($data['RegistryDataPaid'],true);

			foreach($RegistryDataPaid as $record) {
				$response = $this->setRegistryDataPaid($record);
				if (!empty($response[0]['Error_Msg'])) {
					return $response;
				}
			}
		}
		return array(array('Registry_id' => $data['Registry_id'], 'success' => true));
	}

	/**
	 * Изменение отметки об оплате случаев
	 */
	function setRegistryDataPaid($data)
	{
		$params = array(
			'Evn_id' => $data['Evn_id'],
			'RegistryData_IsPaid' => $data['RegistryData_IsPaid'],
		);

		$query = "
			declare
				@Evn_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			begin try
				set nocount on;
				update
					{$this->scheme}.RegistryData with(rowlock)
				set
					RegistryData_IsPaid = :RegistryData_IsPaid
				where
					Evn_id = :Evn_id;
				set nocount off;
				set @Evn_id = :Evn_id;
			end try

			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch

			select @Evn_id as Evn_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}

		$response = $result->result('array');
		if (!is_array($response) || count($response) == 0) {
			return array(array('Error_Msg' => 'Ошибка при обновлении статуса оплаты случая'));
		}
		$response['success'] = true;
		return $response;
	}

	/**
	 * Добавление общей ошибки // TO-DO хранимку p_RegistryErrorCom_ins
	 */	
	function addRegistryErrorCom($data) 
	{
		$this->setRegistryParamsByType($data);

		$query = "
			merge into {$this->scheme}.{$this->RegistryErrorComObject} AS Target
			using (select top 1 :Registry_id as Registry_id, RegistryErrorType_id FROM {$this->scheme}.RegistryErrorType with (nolock) WHERE RegistryErrorType_Code = '3' and RegistryErrorClass_id = 1) as Source ON (Target.Registry_id = Source.Registry_id and Target.RegistryErrorType_id = Source.RegistryErrorType_id)
			when NOT MATCHED then
			insert 
				(Registry_id, RegistryErrorType_id, pmUser_insID, pmUser_updID, RegistryErrorCom_insDT, RegistryErrorCom_updDT)
			values
				(Source.Registry_id, Source.RegistryErrorType_id, :pmUser_id, :pmUser_id, dbo.tzGetDate(), dbo.tzGetDate());
		";
		// echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);			
	}
	
	/**
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	function GetRegistryExport($data)
	{
		if ((0 != $data['Registry_id']))
		{
			$this->setRegistryParamsByType($data);

			$query = "
				select
					case when ( Registry_expDT is null or datediff(mi, Registry_expDT, dbo.tzGetDate()) < 5 ) then RTrim(Registry_ExportPath) else NULL end as Registry_ExportPath,
					ISNULL(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as Registry_SumDifference,
					RegistryType_id
				from {$this->scheme}.Registry R (nolock)
				outer apply(
					select 
						SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) where RD.Registry_id = R.Registry_id
				) RDSum
				where
					Registry_id = :Registry_id
			";

			$result = $this->db->query($query,
			array(
					'Registry_id' => $data['Registry_id']
			)
			);
			if (is_object($result))
			{
				$r = $result->result('array');
				if ( is_array($r) && count($r) > 0 )
				{
					return $r;
					/*
					 if ($r[0]['Registry_ExportPath'] == '')
					 {
						return false;
						}
						else if ($r[0]['Registry_ExportPath'] == '1')
						{
						return '1';
						}
						else
						{
						return $r[0]['Registry_ExportPath'];
						}
						*/
				}
			}
			else
			{
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
	}

	/**
	 * Получаем состояние реестра в данный момент, и тип реестра
	 */
	function GetRegistryXmlExport($data)
	{
		$fields = '';
		$join = '';
		if (!in_array($this->scheme, array('r2','r60'))) {
			$fields = '
					IsNull(R.RegistryCheckStatus_id,0) as RegistryCheckStatus_id,
					IsNull(rcs.RegistryCheckStatus_Code,-1) as RegistryCheckStatus_Code,
					rcs.RegistryCheckStatus_Name as RegistryCheckStatus_Name,
			';
			$join = 'left join RegistryCheckStatus rcs with (nolock) on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id ';
			if( $this->scheme == 'dbo' ) {
				$fields .= 'PayType.PayType_SysNick as PayType_SysNick,';
				$join .= 'left join v_PayType PayType with (nolock) on PayType.PayType_id = R.PayType_id ';
			}
		}
		if ((0 != $data['Registry_id']))
		{
			$this->setRegistryParamsByType($data);

			// Закомментировал условие выбора пути до файла
			// @task https://redmine.swan.perm.ru/issues/60634
			/*$xmlExportPath = 'case when ( Registry_xmlExpDT is null or datediff(mi, Registry_xmlExpDT, dbo.tzGetDate()) < 5 ) then RTrim(Registry_xmlExportPath) else NULL end as Registry_xmlExportPath,';

			if (isSuperadmin()) {
				$xmlExportPath = 'RTrim(Registry_xmlExportPath) as Registry_xmlExportPath,';
			}*/

			$xmlExportPath = 'RTrim(Registry_xmlExportPath) as Registry_xmlExportPath,';
			
			$query = "
				select
					{$xmlExportPath}
					RegistryType_id,
					RegistryStatus_id,
					ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
					ISNULL(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as Registry_SumDifference,
					RDSum.RegistryData_Count as RegistryData_Count,
					convert(varchar(10), Registry_endDate, 104) as Registry_endDate,
					{$fields}
					SUBSTRING(CONVERT(varchar(10), Registry_endDate, 112), 3, 4) as Registry_endMonth -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
				from {$this->scheme}.Registry R with (nolock)
				outer apply(
					select 
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) where RD.Registry_id = R.Registry_id
				) RDSum
				{$join}
				where
					Registry_id = :Registry_id
			";

			$result = $this->db->query($query,
			array(
					'Registry_id' => $data['Registry_id']
			)
			);
			if (is_object($result))
			{
				$r = $result->result('array');
				if ( is_array($r) && count($r) > 0 )
				{
					return $r;
					/*
					 if ($r[0]['Registry_ExportPath'] == '')
					 {
						return false;
						}
						else if ($r[0]['Registry_ExportPath'] == '1')
						{
						return '1';
						}
						else
						{
						return $r[0]['Registry_ExportPath'];
						}
						*/
				}
			}
			else
			{
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
	}

	/**
	 *	Установка статуса экспорта реестра
	 */
	function SetExportStatus($data) {
		if ((0 != $data['Registry_id']))
		{
			$query = "
				update {$this->scheme}.Registry with (rowlock)
				set
					Registry_ExportPath = :Status,
					Registry_expDT = dbo.tzGetDate()
				where Registry_id = :Registry_id
			";
			/*die (getDebugSQL($query, array(
			 'Registry_id' => $data['Registry_id'],
			 'Status' => $data['Status']
				)));*/
			$result = $this->db->query($query,
			array(
					'Registry_id' => $data['Registry_id'],
					'Status' => $data['Status']
			)
			);
			if (is_object($result))
			{
				return true;
			}
			else
			{
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
	}

	/**
	 *	Простановка статуса реестра.
	 */
	function setRegistryCheckStatus($data) 
	{
		if (!isset($data['RegistryCheckStatus_id'])) {
			$data['RegistryCheckStatus_id'] = null;
		}
		
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@curdate datetime = dbo.tzGetDate();
			exec {$this->scheme}.p_Registry_setRegistryCheckStatus
				@Registry_id = :Registry_id,
				@RegistryCheckStatus_id = :RegistryCheckStatus_id,
				@Registry_RegistryCheckStatusDate = @curdate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении человека в базе данных');
		}
	}
	
	
	/**
	 *	Установка статуса импорта реестра в XML
	 */
	function SetXmlExportStatus($data) 
	{
		if ($this->scheme=='dbo') {
			$this->setRegistryCheckStatus($data);
		}
		if ((0 != $data['Registry_id']))
		{
			$query = "
				update {$this->scheme}.Registry with (rowlock)
				set
					Registry_xmlExportPath = :Status,
					Registry_xmlExpDT = dbo.tzGetDate()
				where Registry_id = :Registry_id
			";
			/*die (getDebugSQL($query, array(
			 'Registry_id' => $data['Registry_id'],
			 'Status' => $data['Status']
				)));*/

			$result = $this->db->query($query,
				array(
					'Registry_id' => $data['Registry_id'],
					'Status' => $data['Status']
				)
			);
			if (is_object($result))
			{
				return true;
			}
			else
			{
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
	}

	/**
	 *	Получение данных реестра для печати
	 */
	function getRegistryFields($data) {
		$filter = "(1 = 1)";
		$queryParams = array();

		$filter .= " and Registry.Registry_id = :Registry_id";
		$queryParams['Registry_id'] = $data['Registry_id'];

		if ( !isMinZdrav() ) {
			$filter .= " and Registry.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				Registry.Registry_id,
				RTRIM(Registry.Registry_Num) as Registry_Num,
				ISNULL(convert(varchar(10), cast(Registry.Registry_accDate as datetime), 104), '') as Registry_accDate,
				RTRIM(ISNULL(Org.Org_Name, '')) as Lpu_Name,
				ISNULL(Lpu.Lpu_RegNomC, '') as Lpu_RegNomC,
				ISNULL(Lpu.Lpu_RegNomN, '') as Lpu_RegNomN,
				RTRIM(LpuAddr.Address_Address) as Lpu_Address,
				RTRIM(Org.Org_Phone) as Lpu_Phone,
				ORS.OrgRSchet_RSchet as Lpu_Account,
				OB.OrgBank_Name as LpuBank_Name,
				OB.OrgBank_BIK as LpuBank_BIK,
				Org.Org_INN as Lpu_INN,
				Org.Org_KPP as Lpu_KPP,
				Okved.Okved_Code as Lpu_OKVED,
				Org.Org_OKPO as Lpu_OKPO,
				OO.Oktmo_Code as Lpu_OKTMO,
				month(Registry.Registry_begDate) as Registry_Month,
				year(Registry.Registry_begDate) as Registry_Year,
				cast(isnull(Registry.Registry_Sum, 0.00) as float) as Registry_Sum,
				cast(isnull(Registry.Registry_SumPaid, 0.00) as float) as Registry_SumPaid,
				OHDirector.OrgHeadPerson_Fio as Lpu_Director,
				OHGlavBuh.OrgHeadPerson_Fio as Lpu_GlavBuh,
				RT.RegistryType_id,
				RT.RegistryType_Code,
				KN.KatNasel_SysNick,
				(select OrgRSchet_RSchet from OrgRSchet with(nolock) where OrgRSchet_Name = 'Иные территории') as OrgRSchet_RSchet
			from {$this->scheme}.v_Registry Registry with (NOLOCK)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = Registry.Lpu_id
				inner join v_Org Org with (nolock) on Org.Org_id = Lpu.Org_id
				inner join v_RegistryType RT with (nolock) on RT.RegistryType_id = Registry.RegistryType_id
				left join v_Okved Okved with (nolock) on Okved.Okved_id = Org.Okved_id
				left join [Address] LpuAddr with (nolock) on LpuAddr.Address_id = Org.UAddress_id
				left join OrgRSchet ORS with (nolock) on Registry.OrgRSchet_id = ORS.OrgRSchet_id
				left join v_OrgBank OB with (nolock) on OB.OrgBank_id = ORS.OrgBank_id
				left join v_KatNasel KN with (nolock) on KN.KatNasel_id = Registry.KatNasel_id
				left join v_Oktmo OO with (nolock) on OO.Oktmo_id = Org.Oktmo_id
				outer apply (
					select
						top 1 substring(RTRIM(PS.Person_FirName), 1, 1) + '.' + substring(RTRIM(PS.Person_SecName), 1, 1) + '. ' + RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH with (NOLOCK)
						inner join v_PersonState PS with (NOLOCK) on PS.Person_id = OH.Person_id
					where
						OH.Lpu_id = Lpu.Lpu_id
						and OH.LpuUnit_id is null
						and OH.OrgHeadPost_id = 1
				) as OHDirector
				outer apply (
					select
						top 1 substring(RTRIM(PS.Person_FirName), 1, 1) + '.' + substring(RTRIM(PS.Person_SecName), 1, 1) + '. ' + RTRIM(PS.Person_SurName) as OrgHeadPerson_Fio
					from OrgHead OH with (NOLOCK)
						inner join v_PersonState PS with (NOLOCK) on PS.Person_id = OH.Person_id
					where
						OH.Lpu_id = Lpu.Lpu_id
						and OH.LpuUnit_id is null
						and OH.OrgHeadPost_id = 2
				) as OHGlavBuh
			where " . $filter . "
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			$this->getAdditionalPrintInfo($response[0]);

			return $response[0];
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение списка типов документов ОМС
	 */
	function getPolisTypes($data)
	{
		$query = "
			SELECT PolisType_Code, PolisType_id, PolisType_Name		
			FROM v_PolisType with(nolock)
			WHERE PolisType_CodeF008 IS NOT NULL
		";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Комментарий
	 */
	function getPersonEdit($data)
	{
		$filter = "(1=1)";
		$params = array();
		if (isset($data['Person_id']))
		{
			$filter .= ' and Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		else
		{
			return false;
		}
		if (isset($data['Evn_id']))
		{
			$filter .= ' and Evn_id = :Evn_id';
			$params['Evn_id'] = $data['Evn_id'];
		}
		$query = "
			Select
				Evn_id,
				Person_id,
				Person_SurName,
				Person_FirName,
				Person_SecName,
				RTrim(IsNull(convert(varchar,cast(Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				Polis_Num,
				Polis_Ser,
				PolisType_id,
				OrgSMO_id,
				OMSSprTerr_id
			from RegistryDataLgot RDL with (NOLOCK)
			where
			{$filter}
		";
		/*
		 echo getDebugSQL($query, $params);
		 exit;
		 */
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			$res = $result->result('array');
			if (count($res)>0)
			{
				return $res;
			}
		}
		// Если данные не найдены по событию то может быть уже есть сохраненные данные именно на человека
		if (isset($data['Evn_id']))
		{
			$query = "
				Select
					Evn_id,
					Person_id,
					Person_SurName,
					Person_FirName,
					Person_SecName,
					RTrim(IsNull(convert(varchar,cast(Person_BirthDay as datetime),104),'')) as Person_BirthDay,
					Polis_Num,
					Polis_Ser,
					PolisType_id,
					OrgSMO_id,
					OMSSprTerr_id
				from RegistryDataLgot RDL with (NOLOCK)
				where
					Person_id = :Person_id and Evn_id is null
			";
			/*
			 echo getDebugSQL($query, $params);
			 exit;
			 */
			$result = $this->db->query($query, $params);
			if (is_object($result))
			{
				$res = $result->result('array');
				if (count($res)>0)
				{
					return $res;
				}
			}
		}
		$params = array();
		$filter = "(1=1)";
		if (isset($data['Person_id']))
		{
			$filter .= ' and Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		else
		{
			return false;
		}
		// Если нет сохраненных данных, то берем из человека
		$query = "
			Select top 1
				Person_id,
				Person_SurName,
				Person_FirName,
				Person_SecName,
				RTrim(IsNull(convert(varchar,cast(Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				ps.Polis_Num,
				ps.Polis_Ser,
				OrgSMO_id,
				OMSSprTerr_id,
				PolisType_id
			from v_PersonState ps with (NOLOCK)
			left join v_Polis Polis with (NOLOCK) on Polis.Polis_id = ps.Polis_id
			where
			{$filter}
		";
		/*
		 echo getDebugSQL($query, $params);
		 exit;
		 */
		$result = $this->db->query($query, $params);

		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Комментарий
	 */
	function savePersonEdit($data)
	{
		// Сохранение нового реестра
		$params = array
		(
			'Person_id' => $data['Person_id'],
			'Evn_id' => $data['Evn_id'],
			'Server_id' => $data['Server_id'],
			'Person_SurName' => $data['Person_SurName'],
			'Person_FirName' => $data['Person_FirName'],
			'Person_SecName' => $data['Person_SecName'],
			'Person_BirthDay' => $data['Person_BirthDay'],
			'OMSSprTerr_id' => $data['OMSSprTerr_id'],
			'Polis_Ser' => $data['Polis_Ser'],
			'PolisType_id' => $data['PolisType_id'],
			'Polis_Num' => $data['Polis_Num'],
			'OrgSMO_id' => $data['OrgSMO_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Person_id bigint = :Person_id,
				@curdate datetime = dbo.tzGetDate();
			exec p_RegistryDataLgot_set
				@Person_id = @Person_id output,
				@Evn_id = :Evn_id,
				@Server_id = :Server_id,
				@Person_SurName = :Person_SurName,
				@Person_FirName = :Person_FirName,
				@Person_SecName = :Person_SecName,
				@Person_BirthDay = :Person_BirthDay,
				@OMSSprTerr_id = :OMSSprTerr_id,
				@Polis_Ser = :Polis_Ser,
				@Polis_Num = :Polis_Num,
				@PolisType_id = :PolisType_id,
				@OrgSMO_id = :OrgSMO_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Person_id as Person_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении человека в базе данных');
		}
	}

	/**
	 *	Сохранение ошибки
	 */
	function saveRegistryErrorTFOMS($data)
	{
		if (empty($data['RegistryErrorTFOMSType_id'])) {
			$data['RegistryErrorTFOMSType_id'] = null;
		}

		if (empty($data['OrgSMO_id'])) {
			$data['OrgSMO_id'] = null;
		}

		if (empty($data['RegistryErrorType_id'])) {
			$data['RegistryErrorType_id'] = null;
		}

		if (empty($data['RegistryErrorTFOMSLevel_id'])) {
			$data['RegistryErrorTFOMSLevel_id'] = null;
		}

		if (empty($data['RegistryErrorTFOMS_Severity'])) {
			$data['RegistryErrorTFOMS_Severity'] = null;
		}

		if (empty($data['RegistryErrorTFOMS_IdCase'])) {
			$data['RegistryErrorTFOMS_IdCase'] = null;
		}

		if (empty($data['Registry_Task'])) {
			$data['Registry_Task'] = null;
		}

		if($data['session']['region']['nick']=='astra'){ //https://redmine.swan.perm.ru/issues/65516
			$data['RegistryErrorTFOMSLevel_id'] = '1';
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@RegistryErrorTFOMS_id bigint = null;

			exec {$this->scheme}.p_RegistryErrorTFOMS_ins
				@RegistryErrorTFOMS_id = @RegistryErrorTFOMS_id output,
				@Registry_id = :Registry_id,
				@Evn_id = :Evn_id,
				@RegistryErrorType_id = :RegistryErrorType_id,
				@RegistryErrorType_Code = :RegistryErrorType_Code,
				@RegistryErrorTFOMS_FieldName = :RegistryErrorTFOMS_FieldName,
				@RegistryErrorTFOMS_BaseElement = :RegistryErrorTFOMS_BaseElement,
				@RegistryErrorTFOMS_Comment = :RegistryErrorTFOMS_Comment,
				@RegistryErrorTFOMSType_id = :RegistryErrorTFOMSType_id,
				@RegistryErrorTFOMSLevel_id = :RegistryErrorTFOMSLevel_id,
				@RegistryErrorTFOMS_Severity = :RegistryErrorTFOMS_Severity,
				@RegistryErrorTFOMS_IdCase = :RegistryErrorTFOMS_IdCase,
				@Registry_Task = :Registry_Task,
				@OrgSMO_id = :OrgSMO_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @RegistryErrorTFOMS_id as RegistryErrorTFOMS_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Выгрузка принятых ОКНО случаев в XML
	 */
	function exportOnko($data) {
		set_time_limit(0);
		$this->load->library('textlog', array('file'=>'exportOnkoToXml_' . date('Y-m-d') . '.log', 'logging' => true));
		$this->textlog->add('');
		$this->textlog->add('Запуск');

		if (!isSuperAdmin() && (!isset($data['Lpu_id']) || $data['Lpu_id'] != 10011168)) {
			if (!empty($data['Lpu_id'])) {
				$data['Lpu_oid'] = $data['Lpu_id'];
			} else {
				return array('Error_Msg' => 'Не указана МО, выгрузка невозможна');
			}
		}

		$filter = "";
		$queryParams = array(
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate']
		);
		if (!empty($data['Lpu_oid'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_oid'];
		}

		$resp_r = $this->queryResult("
			select
				Registry_id,
				Registry_xmlExportPath,
				Registry_EvnNum
			from
				{$this->scheme}.v_Registry (nolock)
			where
				RegistryType_id = 13
				and Registry_EvnNum is not null -- с номером
				and Registry_xmlExportPath is not null -- с файлом
				and Registry_begDate <= :Registry_endDate
				and Registry_endDate >= :Registry_begDate 
				{$filter}
		", $queryParams);

		$out_dir = EXPORTPATH_REGISTRY . "re_xml_" . time() . "_" . rand(10000, 99999) . "_onk";
		mkdir($out_dir);
		$temp_dir = $out_dir . "/temp";
		mkdir($temp_dir);

		$file_zip_name = $out_dir . "/hm.zip";
		$newSlFile = $out_dir . "/hm.xml";
		$newPersFile = $out_dir . "/lm.xml";

		// пишем хедер
		file_put_contents($newSlFile, "<ZL_LIST>");
		file_put_contents($newPersFile, "<PERS_LIST>");

		$toFile = array();
		$toPersFile = array();

		$N_ZAP = 0;
		$ID_PAC = 0;
		$Z_SL_ID = 0;
		$IDCASE = 0;

		foreach($resp_r as $one_r) {
			$this->textlog->add('Обрабатываем реестр ' . $one_r['Registry_id']);
			$this->textlog->add('Задействовано памяти ' . (memory_get_usage() / 1024 / 1024) . " MB");
			if (file_exists($one_r['Registry_xmlExportPath'])) {
				// 1. Берём из RegistryData оплаченные Evn_id
				$paidEvnIds = array();
				$resp_e = $this->queryResult("
					select
						rd.Evn_id
					from
						{$this->scheme}.v_RegistryGroupLink rgl (nolock)
						inner join {$this->scheme}.v_RegistryData rd (nolock) on rd.Registry_id = rgl.Registry_id
					where
						rgl.Registry_pid = :Registry_id
						and ISNULL(rd.Evn_disDate, rd.Evn_setDate) >= :Registry_begDate
						and ISNULL(rd.Evn_disDate, rd.Evn_setDate) <= :Registry_endDate
						and rd.RegistryData_IsPaid = 2
					
					union all
						
					select
						rd.Evn_id
					from
						{$this->scheme}.v_RegistryGroupLink rgl (nolock)
						inner join {$this->scheme}.v_RegistryDataEvnPS rd (nolock) on rd.Registry_id = rgl.Registry_id
					where
						rgl.Registry_pid = :Registry_id
						and ISNULL(rd.Evn_disDate, rd.Evn_setDate) >= :Registry_begDate
						and ISNULL(rd.Evn_disDate, rd.Evn_setDate) <= :Registry_endDate
						and rd.RegistryData_IsPaid = 2
					
					union all
						
					select
						rd.Evn_id
					from
						{$this->scheme}.v_RegistryGroupLink rgl (nolock)
						inner join {$this->scheme}.v_RegistryDataDisp rd (nolock) on rd.Registry_id = rgl.Registry_id
					where
						rgl.Registry_pid = :Registry_id
						and ISNULL(rd.Evn_disDate, rd.Evn_setDate) >= :Registry_begDate
						and ISNULL(rd.Evn_disDate, rd.Evn_setDate) <= :Registry_endDate
						and rd.RegistryData_IsPaid = 2
					
					union all
						
					select
						rd.Evn_id
					from
						{$this->scheme}.v_RegistryGroupLink rgl (nolock)
						inner join {$this->scheme}.v_RegistryDataProf rd (nolock) on rd.Registry_id = rgl.Registry_id
					where
						rgl.Registry_pid = :Registry_id
						and ISNULL(rd.Evn_disDate, rd.Evn_setDate) >= :Registry_begDate
						and ISNULL(rd.Evn_disDate, rd.Evn_setDate) <= :Registry_endDate
						and rd.RegistryData_IsPaid = 2
				", array(
					'Registry_id' => $one_r['Registry_id'],
					'Registry_begDate' => $data['Registry_begDate'],
					'Registry_endDate' => $data['Registry_endDate']
				));
				foreach($resp_e as $one_e) {
					$paidEvnIds[] = $one_e['Evn_id'];
				}
				unset($resp_e);

				// 2. Берём из Registry поле Registry_EvnNum ищем в нём соответствия между оплаченными Evn_id и IDCASE / N_ZAP.
				$paidNZAPs = array();
				$paidIDPACs = array();
				$Registry_EvnNum = json_decode($one_r['Registry_EvnNum'], true);
				unset($one_r['Registry_EvnNum']);
				foreach($Registry_EvnNum as $key => $oneEvnNum) {
					if (!empty($oneEvnNum['N_ZAP']) && in_array($oneEvnNum['Evn_id'], $paidEvnIds)) {
						$paidNZAPs[] = $oneEvnNum['N_ZAP'];
					}
				}
				unset($Registry_EvnNum);

				// 3. Берём файл реестра в XML и отбираем оттуда нужные IDCASE и складываем в новый файл XML.
				$zip = new ZipArchive();
				if ($zip->open($one_r['Registry_xmlExportPath']) === TRUE) {
					$zip->extractTo($temp_dir);
					$zip->close();

					$slFile = null;
					$persFile = null;
					$files = scandir($temp_dir);
					foreach($files as $file) {
						if (mb_substr($file, 0, 2) == "HM") {
							$slFile = $file;
						}
						if (mb_substr($file, 0, 2) == "LM") {
							$persFile = $file;
						}
					}

					if (!empty($slFile) && !empty($persFile)) {
						$count = 0;

						$xmlString = file_get_contents($temp_dir . '/' . $slFile);
						$header = substr($xmlString, 0, strpos($xmlString, '</SCHET>') + strlen('</SCHET>'));
						$footer = '</ZL_LIST>';
						$xmlString = substr($xmlString, strlen($header));
						$chunkSize = 1024 * 1024 * 10; // 10 MB
						while (!empty($xmlString)) {
							// Нагребаем остатки, если размер оставшегося куска файла меньше $chunkSize МБ
							if (strlen($xmlString) <= $chunkSize + strlen($footer) + 2 /* учтем перевод строки */) {
								$xmlData = substr($xmlString, 0, strlen($xmlString) - strlen($footer));
								$xmlString = '';
							} // или данные по $chunkSize МБ
							else {
								$xmlData = substr($xmlString, 0, $chunkSize);
								$xmlString = substr($xmlString, $chunkSize);

								if (strpos($xmlString, '</ZAP>') !== false) {
									$xmlData .= substr($xmlString, 0, strpos($xmlString, '</ZAP>') + strlen('</ZAP>'));
									$xmlString = substr($xmlString, strpos($xmlString, '</ZAP>') + strlen('</ZAP>'));

									if (trim($xmlString) == $header) {
										$xmlString = '';
									}
								}
							}

							$xml = new SimpleXMLElement($header . $xmlData . $footer);
							unset($xmlData);
							foreach ($xml->ZAP as $oneZAP) {
								if (in_array($oneZAP->N_ZAP->__toString(), $paidNZAPs)) {
									$isOnk = false;
									foreach($oneZAP->Z_SL as $oneZSL) {
										foreach($oneZSL->SLUCH as $oneSLUCH) {
											$DS_ONK = $oneSLUCH->DS_ONK->__toString();
											if (!empty($DS_ONK)) {
												$isOnk = true;
												break;
											}

											if (isset($oneSLUCH->ONK_SL)) {
												$ONK_SL = $oneSLUCH->ONK_SL->asXML();
												if (!empty($ONK_SL)) {
													$isOnk = true;
													break;
												}
											}
										}
									}

									if ($isOnk) {
										$N_ZAP++;
										$oneZAP->N_ZAP = $N_ZAP;
										$ID_PAC++;
										$oldIDPAC = $oneZAP->PACIENT->ID_PAC->__toString();
										$oneZAP->PACIENT->ID_PAC = $ID_PAC;
										foreach($oneZAP->Z_SL as $oneZSL) {
											$Z_SL_ID++;
											$oneZSL->Z_SL_ID = $Z_SL_ID;
											foreach($oneZSL->SLUCH as $oneSLUCH) {
												$IDCASE++;
												$oneSLUCH->IDCASE = $IDCASE;
											}
										}

										$count++;
										$toFile[] = $oneZAP->asXML();
										if (count($toFile) > 1000) {
											file_put_contents($newSlFile, implode(PHP_EOL, $toFile), FILE_APPEND);
											unset($toFile);
										}
										$paidIDPACs[$oldIDPAC] = $ID_PAC;
									}
								}
							}
							unset($xml);
						}

						@unlink($temp_dir . '/' . $slFile);

						$xmlString = file_get_contents($temp_dir . '/' . $persFile, FILE_APPEND);
						$header = substr($xmlString, 0, strpos($xmlString, '</ZGLV>') + strlen('</ZGLV>'));
						$footer = '</PERS_LIST>';
						$xmlString = substr($xmlString, strlen($header));
						$chunkSize = 1024 * 1024 * 10; // 10 MB
						while (!empty($xmlString)) {
							// Нагребаем остатки, если размер оставшегося куска файла меньше $chunkSize МБ
							if (strlen($xmlString) <= $chunkSize + strlen($footer) + 2 /* учтем перевод строки */) {
								$xmlData = substr($xmlString, 0, strlen($xmlString) - strlen($footer));
								$xmlString = '';
							} // или данные по $chunkSize МБ
							else {
								$xmlData = substr($xmlString, 0, $chunkSize);
								$xmlString = substr($xmlString, $chunkSize);

								if (strpos($xmlString, '</PERS>') !== false) {
									$xmlData .= substr($xmlString, 0, strpos($xmlString, '</PERS>') + strlen('</PERS>'));
									$xmlString = substr($xmlString, strpos($xmlString, '</PERS>') + strlen('</PERS>'));

									if (trim($xmlString) == $header) {
										$xmlString = '';
									}
								}
							}

							$xml = new SimpleXMLElement($header . $xmlData . $footer);
							unset($xmlData);
							foreach ($xml->PERS as $onePERS) {
								$oldIDPAC = $onePERS->ID_PAC->__toString();
								if (!empty($paidIDPACs[$oldIDPAC])) {
									$onePERS->ID_PAC = $paidIDPACs[$oldIDPAC];
									$toPersFile[] = $onePERS->asXML();
									if (count($toPersFile) > 1000) {
										file_put_contents($newPersFile, implode(PHP_EOL, $toPersFile), FILE_APPEND);
										unset($toPersFile);
									}
								}
							}
							unset($xml);
						}

						@unlink($temp_dir . '/' .$persFile);

						$this->textlog->add('В реестре ' . $one_r['Registry_id'] . ' оплачено: ' . count($paidNZAPs) . ' случаев, из них онко случаев: ' . $count);
					} else {
						$this->textlog->add('В файле ' . $one_r['Registry_xmlExportPath'] . ' для реестра ' . $one_r['Registry_id'] . ' не найдены XML-файлы случаев и пациентов');
					}
				} else {
					$this->textlog->add('Не удалось распаковать файл ' . $one_r['Registry_xmlExportPath'] . ' для реестра ' . $one_r['Registry_id']);
				}
			} else {
				$this->textlog->add('Файл ' . $one_r['Registry_xmlExportPath'] . ' для реестра ' . $one_r['Registry_id'] . ' не найден');
			}
		}

		if (count($toFile) > 0) {
			file_put_contents($newSlFile, implode(PHP_EOL, $toFile), FILE_APPEND);
			unset($toFile);
		}

		if (count($toPersFile) > 0) {
			file_put_contents($newPersFile, implode(PHP_EOL, $toPersFile), FILE_APPEND);
			unset($toPersFile);
		}

		// пишем футер
		file_put_contents($newSlFile, "</ZL_LIST>", FILE_APPEND);
		file_put_contents($newPersFile, "</PERS_LIST>", FILE_APPEND);

		// запаковываем
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $newSlFile, "hm.xml" );
		$zip->AddFile( $newPersFile, "lm.xml" );
		$zip->close();

		@unlink($newSlFile);
		@unlink($newPersFile);

		$this->textlog->add('Конец');

		return array('Error_Msg' => '', 'link' => $file_zip_name);
	}


	/**
	 *	Удаление ошибки
	 */
	function deleteRegistryErrorTFOMS($data)
	{
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);

			exec {$this->scheme}.p_RegistryErrorTFOMS_del
				@RegistryErrorTFOMS_id = :RegistryErrorTFOMS_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			if (!empty($data['Registry_id']) && isset($data['setNoErrorSum']) && $data['setNoErrorSum'] && $this->getRegionNick() == 'perm') {

				//Выполняем вычисление суммы без ошибок
				$response = $this->setNoErrorSum($data['Registry_id']);
				if (!is_array($response) || !empty($response[0]['Error_Msg'])) {
					return array(
						array('Error_Msg' => 'Произошла ошибка при вычислении суммы без ошибок.')
					);
				}
			}
			return $result->result('array');
		}

		return false;
	}

	/**
	 *	Комментарий
	 */
	function deletePersonEdit($data, $object, $person_id, $evn_id, $scheme = "dbo")
	{
		$params = Array();
		if ($person_id <= 0)
		{
			return false;
		}
		$params['person_id'] = $person_id;
		$params['evn_id'] = $evn_id;
		if (strpos(strtoupper($object), "EVN")!==false)
		{
			$fields = ":pmUser_id, ";
			$params['pmUser_id'] = $data['session']['pmuser_id'];
		}
		else
		{
			$fields = "";
		}

		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$scheme}.p_RegistryDataLgot_del
				:person_id, :evn_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$res = $this->db->query($query, $params);
		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение признака формирования реестра
	 * @task https://redmine.swan.perm.ru/issues/79543
	 */
	function getRegistryQueueReformStatus($data) {
		$params = array('Registry_id' => $data['Registry_id']);
		$response = -1;

		$query = "
			select top 1 isnull(reform, 1) as reform
			from {$this->scheme}.v_RegistryQueue with(nolock)
			where RegistryQueue_id = :Registry_id
		";
		$res = $this->db->query($query, $params);

		if ( is_object($res) ) {
			$queryData = $res->result('array');

			if ( is_array($queryData) && count($queryData) == 1 ) {
				$response = $queryData[0]['reform'];
			}
		}

		return $response;
	}

	/**
	 *	Комментарий
	 */
	function checkDeleteRegistry($data)
	{
		if ($data['id']>0)
		{
			$sql = "
				SELECT
					R.RegistryStatus_id,
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as Registry_IsProgress
				FROM
					{$this->scheme}.v_Registry R with(nolock)
					outer apply(
						select top 1 RegistryQueue_id
						from {$this->scheme}.v_RegistryQueue with(nolock)
						where Registry_id = R.Registry_id
					) RQ
				WHERE
					R.Registry_id = :Registry_id
			";
			/*
			 echo getDebugSql($sql, array('Registry_id' => $data['id']));
			 exit;
			 */

 			$res = $this->db->query($sql, array('Registry_id' => $data['id']));

			if (is_object($res))
			{
				$resa = $res->result('array');
				if (count($resa)>0)
				{
					return ($resa[0]['RegistryStatus_id']!=4 && $resa[0]['Registry_IsProgress']!=1);
				}
				else
				{
					return false;
				}
			}
		}
	}

	/**
	 *	Комментарий
	 */
	function checkActualRecordRegistry($data)
	{
		if ($data['Registry_id']>0)
		{
			$this->setRegistryParamsByType($data);

			$sql = "
				Select COUNT(*) as rec from {$this->scheme}.v_{$this->RegistryDataObject} with (nolock)
				where needReform = 2
				and RegistryData_updDT > dbo.MirrorUpdTime()
				and Registry_id = :Registry_id
			";
			$res = $this->db->query($sql, array('Registry_id' => $data['Registry_id']));
			if (is_object($res))
			{
				$resa = $res->result('array');
				if (count($resa)>0)
				{
					return ($resa[0]['rec']==0);
				}
				else
				{
					return false;
				}
			}
		}
	}

	/**
	 *	Получение данных Случаи без оплаты (RegistryNoPay) для стационарных реестров
	 */
	function loadRegistryNoPay($data)
	{
		if ($data['Registry_id']<=0)
		{
			return false;
		}
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		$params = array('Registry_id' => $data['Registry_id']);
		$query = "
		Select
			RNP.Registry_id,
			RNP.Evn_id,
			RNP.Person_id,
			RNP.Evn_rid,
			RNP.EvnClass_id,
			RNP.Server_id,
			RNP.PersonEvn_id,
			rtrim(RNP.Person_SurName) + ' ' + rtrim(RNP.Person_FirName) + ' ' + rtrim(isnull(RNP.Person_SecName, '')) as Person_FIO,
			RTrim(IsNull(convert(varchar,cast(RNP.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
			rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name as LpuSection_Name,
			RNP.RegistryNoPay_Tariff,
			RNP.RegistryNoPay_KdFact,
			RNP.RegistryNoPay_KdPlan,
			RNP.RegistryNoPay_KdPay,
			RNP.RegistryNoPay_UKLSum
		from {$this->scheme}.v_RegistryNoPay RNP with (NOLOCK)
		left join v_LpuSection LpuSection with(nolock) on LpuSection.LpuSection_id = RNP.LpuSection_id
		where
			RNP.Registry_id=:Registry_id
		order by RNP.Person_SurName, RNP.Person_FirName, RNP.Person_SecName, LpuSection.LpuSection_Name";
		$result = $this->db->query(getLimitSQL($query, $data['start'], $data['limit']), $params);

		$result_count = $this->db->query(getCountSQL($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 *	Получение данных Дубли посещений (RegistryDouble) для поликлин. реестров
	 */
	function loadRegistryDouble($data)
	{
		$fields = "";
		$filter = "";
		$join = "";
		$params = [
			'Registry_id' => $data['Registry_id']
		];
		$returnQueryOnly = isset($data['returnQueryOnly']);
		$vizit_id_field = in_array($this->regionNick, ['ufa','vologda']) ? 'EvnVizitPL_id' : 'EvnVizitPL_rid';

		$this->setRegistryParamsByType($data);

		if (isset($data['Filter'])) {
			$filterData = json_decode(trim($data['Filter'], '"'), 1);

			if (is_array($filterData)) {
				foreach ($filterData as $column => $value) {
					if (!is_array($value)) {
						continue;
					}

					$r = null;

					foreach ($value as $d) {
						$r .= "'" . trim(toAnsi($d)) . "',";
					}

					if ($column == 'Person_FIO')
						$column = 'rtrim(IsNull(RD.Person_SurName,\'\')) + \' \' + rtrim(IsNull(RD.Person_FirName,\'\')) + \' \' + rtrim(isnull(RD.Person_SecName, \'\'))';
					elseif ($column == 'LpuSection_name' || $column == 'LpuSection_Name')
						$column = 'LS.' . $column;
					elseif ($column == 'EvnPL_NumCard')
						$column = 'EPL.' . $column;
					elseif ($column == 'LpuBuilding_Name')
						$column = 'LB.' . $column;
					elseif ($column == 'LpuSectionProfile_Name')
						$column = 'LSP.' . $column;
					elseif ($column == 'MedPersonal_Fio')
						$column = 'MP.Person_Fio';
					elseif ($column == 'MedSpecOms_Name')
						$column = 'MSO.' . $column;
					elseif ($column == 'Usluga_Code')
						$column = ($this->RegistryType_id != 1 && $this->RegistryType_id != 14 ? 'U.UslugaComplex_Code' : 'm.Mes_Code');
					elseif(strripos($column, '_id') !== false)
						$column   = 'RD.' . $column;

					$r = rtrim($r, ',');

					$filter .= ' and ' . $column . ' IN (' . $r . ')';
				}
			}
		}

		if(!empty($data['MedPersonal_id'])) {
			$filter .= " and MP.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
			
		if (in_array($this->regionNick, ['ufa','pskov','buryatiya','penza']))
		{
			if(!empty($data['LpuBuilding_id'])) {
				$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			
			$join .= "
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			";
			$fields .= "
				, LB.LpuBuilding_Name
			";
		}

		if (in_array($this->regionNick, ['ufa']))
		{
			if(!empty($data['LpuBuilding_id'])) {
				$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}

			$join .= "
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = EVPL.LpuSectionProfile_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = EVPL.MedStaffFact_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
			";
			$fields .= "
				, ISNULL(LSP.LpuSectionProfile_Code + '. ', '') + LSP.LpuSectionProfile_Name as LpuSectionProfile_Name
				, ISNULL(MSO.MedSpecOms_Code + '. ', '') + MSO.MedSpecOms_Name as MedSpecOms_Name
			";

			$join .= "
				left join {$this->scheme}.v_{$this->RegistryDataObject} RData with (nolock) on RData.Registry_id = RD.Registry_id and RData.Evn_id = RD.Evn_id
			";

			if (!empty($data['filterIsZNO']) && $data['filterIsZNO'] == 2 ) {
				$filter .= " and RData.RegistryData_IsZNO in (2, 3) ";
			}

			if (isset($data['Person_SurName']))
			{
				$filter .= " and RD.Person_SurName like :Person_SurName ";
				$params['Person_SurName'] = $data['Person_SurName']."%";
			}
			if (isset($data['Person_FirName']))
			{
				$filter .= " and RD.Person_FirName like :Person_FirName ";
				$params['Person_FirName'] = $data['Person_FirName']."%";
			}
			if (isset($data['Person_SecName']))
			{
				$filter .= " and RD.Person_SecName like :Person_SecName ";
				$params['Person_SecName'] = $data['Person_SecName']."%";
			}
		}

		if (!empty($data['ExtendedFilterField']) && !empty($data['ExtendedFilterFieldValue'])) {
			$filter .= " and " . $data['ExtendedFilterField'] . " like '%' + :ExtendedFilterFieldValue + '%'";
			$params['ExtendedFilterFieldValue'] = $data['ExtendedFilterFieldValue'];
		}

		$query = "
			select
				-- select
				 RD.Registry_id
				,RD.Evn_id
				,EPL.EvnPL_id as Evn_rid
				,RD.Person_id
				,rtrim(IsNull(RD.Person_SurName,'')) + ' ' + rtrim(IsNull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, '')) as Person_FIO
				,convert(varchar(10), RD.Person_BirthDay, 104) as Person_BirthDay
				,EPL.EvnPL_NumCard
				,RTI.RegistryType_id
				,LS.LpuSection_FullName
				,MP.Person_Fio as MedPersonal_Fio
				,convert(varchar(10), EVPL.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate
				,null as CmpCallCard_id
				{$fields}
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryDouble RD with (NOLOCK)
				left join v_EvnVizitPL EVPL with (nolock) on EVPL.{$vizit_id_field} = RD.Evn_id
				left join v_EvnPL EPL with (nolock)  on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
				left join v_LpuSection LS with (nolock)  on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_Registry RTI with (nolock) on RTI.Registry_id = RD.Registry_id
				outer apply(
					select top 1 Person_Fio, MedPersonal_id from v_MedPersonal with(nolock) where MedPersonal_id = EVPL.MedPersonal_id
				) as MP
				{$join}
				-- end from
			where
				-- where
				RD.Registry_id = :Registry_id
				{$filter}
				-- end where
			order by
				-- order by
				RD.Person_SurName, RD.Person_FirName, RD.Person_SecName
				-- end order by
		";

		if ($returnQueryOnly === true) {
			return [
				'query' => $query,
				'params' => $params,
			];
		}

		if (!empty($data['withoutPaging'])) {
			return $this->queryResult($query, $params);
		}

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 *	Получение данных Дубли посещений (RegistryDoublePL) для поликлин. реестров
	 */
	function loadRegistryDoublePL($data)
	{
		$filter = "";

		if(!empty($data['MedPersonal_id'])) {
			$filter .= " and MP.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		$query = "
			select
				-- select
				 RD.Registry_id
				,RD.Evn_id
				,E.Evn_rid
				,RD.Person_id
				,rtrim(IsNull(RD.Person_SurName,'')) + ' ' + rtrim(IsNull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, '')) as Person_FIO
				,RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay
				,RD.EvnPL_NumCard
				,RTI.RegistryType_id
				,LS.LpuSection_FullName
				,MP.Person_Fio as MedPersonal_Fio
				,convert(varchar(10), RD.RegistryDoublePL_begDate, 104) as EvnPL_setDate
				,convert(varchar(10), RD.RegistryDoublePL_endDate, 104) as EvnPL_disDate
				,null as CmpCallCard_id
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryDoublePL RD with (NOLOCK)
				left join v_Evn E with (nolock) on e.Evn_id = rd.Evn_id
				left join v_LpuSection LS with (nolock)  on LS.LpuSection_id = RD.LpuSection_id
				left join v_Registry RTI with (nolock) on RTI.Registry_id = RD.Registry_id
				outer apply(
					select top 1 Person_Fio, MedPersonal_id from v_MedPersonal with(nolock) where MedPersonal_id = RD.MedPersonal_id
				) as MP
				-- end from
			where
				-- where
				RD.Registry_id = :Registry_id
				{$filter}
				-- end where
			order by
				-- order by
				RD.Person_SurName, RD.Person_FirName, RD.Person_SecName
				-- end order by
		";

		if (!empty($data['withoutPaging'])) {

			$res = $this->db->query($query, $data);

			if (is_object($res))
			{
				return $res->result('array');
			}
			else
			{
				return false;
			}
		} else {
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
			if (is_object($result))
			{
				$response = array();
				$response['data'] = $result->result('array');

				$count = count($response['data']);
				if (intval($data['start']) != 0 || $count >= intval($data['limit'])) { // считаем каунт только если записей не меньше, чем лимит или не первая страничка грузится
					$result_count = $this->db->query(getCountSQLPH($query), $data);

					if (is_object($result_count)) {
						$cnt_arr = $result_count->result('array');
						$count = $cnt_arr[0]['cnt'];
						unset($cnt_arr);
					} else {
						$count = 0;
					}
				}

				$response['totalCount'] = $count;
				return $response;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Получение данных о персонах из RegistryPerson.
	 * Эти данные получаем с реестровой базы.
	 *
	 * @param Array $records Записи пришедшие с клиента
	 */
	function getCountRegistryPerson($data) {
		$this->setRegistryParamsByType($data);

		$query = "
			select
				count(*) as rec
			from {$this->scheme}.{$this->RegistryPersonObject} with (nolock)
			where
				Person2_id = :Person_id and Person_id = :Person_did
		";
		$result = $this->db->query($query, array('Person_did' => $data['Person_did'], 'Person_id' => $data['Person_id']))->result_array();
		if (is_array($result) && count($result) == 1)
		return $result[0]['rec'];
		else
		return 0;
	}

	/**
	 *	Восстановление реестра
	 */
	function registryRevive($data, $id, $scheme = "dbo")
	{
		$params = Array();
		if ($id <= 0)
		{
			return false;
		}
		$params['id'] = $id;
		$params['pmUser_id'] = $data['session']['pmuser_id'];

		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$scheme}.p_Registry_revive
				@Registry_id = :id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Message;
		";
		$res = $this->db->query($query, $params);
		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Помечаем запись реестра на удаление 
	 */
	function deleteRegistryData($data)
	{
		foreach ($data['EvnIds'] as $EvnId) {
			$data['Evn_id'] = $EvnId;
			
			$query = "
				Declare @Error_Code bigint;
				Declare @Error_Message varchar(4000);
				exec {$this->scheme}.p_RegistryData_del
					@Evn_id = :Evn_id,
					@Registry_id = :Registry_id,
					@RegistryType_id = :RegistryType_id,
					@RegistryData_deleted = :RegistryData_deleted,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select
					@Error_Code as Error_Code,
					@Error_Message as Error_Msg;
			";
 			$res = $this->db->query($query, $data);

		}
		
		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Помечаем запись реестра на удаление
	 */
	function deleteRegistryDataAll($data)
	{
		$this->setRegistryParamsByType($data);

		$EvnIds = array();
		if (!empty($data['type'])) {
			switch ($data['type']) {
				case 'RegistryError':
					$query = "
						select
							Evn_id
						from
							{$this->scheme}.v_{$this->RegistryErrorObject} with (nolock)
						where
							Registry_id = :Registry_id
					";

					$result = $this->queryResult($query, array(
						'Registry_id' => $data['Registry_id']
					));

					if (is_array($result)) {
						foreach($result as $one) {
							$EvnIds[] = $one['Evn_id'];
						}
					}
				break;

				case 'RegistryErrorTFOMS':
					if ($this->regionNick == 'perm') {
						$EvnField = $this->RegistryDataEvnField;
					}
					else {
						$EvnField = 'Evn_id';
					}

					$query = "
						select {$EvnField} as Evn_id
						from {$this->scheme}.v_RegistryErrorTFOMS with (nolock)
						where Registry_id = :Registry_id
					";

					$result = $this->queryResult($query, array(
						'Registry_id' => $data['Registry_id']
					));

					if (is_array($result)) {
						foreach($result as $one) {
							$EvnIds[] = $one['Evn_id'];
						}
					}
				break;
			}
		}

		foreach ($EvnIds as $EvnId) {
			$data['Evn_id'] = $EvnId;

			$query = "
				Declare @Error_Code bigint;
				Declare @Error_Message varchar(4000);
				exec {$this->scheme}.p_RegistryData_del
					@Evn_id = :Evn_id,
					@Registry_id = :Registry_id,
					@RegistryType_id = :RegistryType_id,
					@RegistryData_deleted = :RegistryData_deleted,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select
					@Error_Code as Error_Code,
					@Error_Message as Error_Msg;
			";
			$res = $this->db->query($query, $data);

			if (is_object($res))
			{
				$resp = $res->result('array');
				if (!empty($resp[0]['Error_Msg'])) {
					return $resp[0];
				}
			}
			else
			{
				return false;
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 *	Установка суммы реестра без ошибок
	 */
	function setNoErrorSum($Registry_id)
	{

		//Выполняем вычисление суммы без ошибок
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Registry_id bigint = null;
			exec dbo.p_Registry_setNoErrorSum
				@Registry_id = :Registry_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Registry_id as Registry_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'Registry_id' => $Registry_id,
			'pmUser_id' => $this->getPromedUserId()
		));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление помеченных на удаление записей и пересчет реестра
	 * В региональных моделях: Крым, Пермь
	 */
	public function refreshRegistry($data) {
		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$this->scheme}.p_RegistryData_Refresh
				@Registry_id = :Registry_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		//echo getDebugSql($query, $data);exit;
		$res = $this->db->query($query, $data);

		if (is_object($res)) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Комментарий
	 */
	function ckeckOrgRSchetOnUsedInRegistry($data)
	{
		$query = "
			select top 1
				Registry_id
			from
				{$this->scheme}.v_Registry with (nolock)
			where
				Lpu_id = :Lpu_id
				and OrgRSchet_id = :OrgRSchet_id
		";
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			return array(
				array('Error_Msg' => 'Ошибка БД!')
			);
		}
		$resp = $res->result('array');
		if( count($resp) > 0 ) {
			return array(
				array('Error_Msg' => 'Удаление невозможно, поскольку данный счет используется в реестрах!')
			);
		} else {
			return true;
		}
	}

	/**
	 * Установка признака "Нуждается в переформировании" для всех реестров содержащих случай
	 */
	function setRegistryIsNeedReformByEvnId($data)
	{
		if (empty($data['Evn_id'])) {
			return false;
		}
		
		$query = "
			select
				r.Registry_id
			from
				{$this->scheme}.v_Registry r (nolock)
				inner join {$this->scheme}.v_RegistryData rd (nolock) on rd.Registry_id = r.Registry_id
			where
				rd.Evn_id = :Evn_id
		";
		
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			return array(
				array('Error_Msg' => 'Ошибка БД!')
			);
		}
		
		$resp = $res->result('array');
		foreach($resp as $respone) {
			$resposne = $this->setRegistryIsNeedReform(array(
				 'Registry_id' => $respone['Registry_id']
				,'Registry_IsNeedReform' => 2
				,'pmUser_id' => $data['pmUser_id']
			));
		}

		return true;
	}

	/**
	 *	Проверка вхождения случая в реестр
	 */
	function checkEvnInRegistryOld($data, $action = 'delete')
	{
		$filter = "";

		if(isset($data['EvnPL_id'])) {
			$filter .= " and Evn_rid = :EvnPL_id";
			$data['RegistryType_id'] = 2;
		}
		if(isset($data['EvnPS_id'])) {
			$filter .= " and Evn_rid = :EvnPS_id";
			$data['RegistryType_id'] = 1;
		}
		if(isset($data['EvnPLStom_id'])) {
			$filter .= " and Evn_rid = :EvnPLStom_id";
			$data['RegistryType_id'] = 16;
		}
		if(isset($data['EvnVizitPL_id'])) {
			$filter .= " and Evn_id = :EvnVizitPL_id";
			$data['RegistryType_id'] = 2;
		}
		if(isset($data['EvnSection_id'])) {
			$filter .= " and Evn_id = :EvnSection_id";
			$data['RegistryType_id'] = 1;
		}
		if(isset($data['EvnVizitPLStom_id'])) {
			$filter .= " and Evn_id = :EvnVizitPLStom_id";
			$data['RegistryType_id'] = 16;
		}

		if(isset($data['EvnPLDispDop13_id'])) {
			$filter .= " and Evn_id = :EvnPLDispDop13_id";
			$data['RegistryType_id'] = 7;
		}

		if(isset($data['EvnPLDispProf_id'])) {
			$filter .= " and Evn_id = :EvnPLDispProf_id";
			$data['RegistryType_id'] = 11;
		}

		if(isset($data['EvnPLDispOrp_id'])) {
			$filter .= " and Evn_id = :EvnPLDispOrp_id";
			$data['RegistryType_id'] = 9;
		}

		if(isset($data['EvnPLDispTeenInspection_id'])) {
			$filter .= " and Evn_id = :EvnPLDispTeenInspection_id";
			$data['RegistryType_id'] = 12;
		}

		if(isset($data['EvnUslugaPar_id'])) {
			$filter .= " and Evn_id = :EvnUslugaPar_id";
			$data['RegistryType_id'] = 15;
		}

		if(isset($data['EvnUslugaTelemed_id']) && in_array(getRegionNick(), array('ekb', 'perm'))) {
			$filter .= " and Evn_id = :EvnUslugaTelemed_id";
			$data['RegistryType_id'] = 15;
		}

		if (isset($data['CmpCallCard_id'])) {
			$filter .= " and Evn_id = :CmpCallCard_id";
			$data['RegistryType_id'] = 6;
		}

		if (empty($filter)) {
			return false;
		}

		$filter = "(1 = 1)" . $filter;

		$this->setRegistryParamsByType($data);

		//#51767
		if (in_array($data['RegistryType_id'], array(7,9,11,12))) {
			if ($action == 'edit') {
				return false;
			}

			$query = "
				select top 1 DC.DispClass_Code
				from v_Evn E with(nolock)
				inner join v_EvnPLDisp EPLD with(nolock) on EPLD.EvnPLDisp_id = E.Evn_id
				inner join v_DispClass DC with(nolock) on DC.DispClass_id = EPLD.DispClass_id
				where {$filter}
			";
			$result = $this->db->query($query, $data);

			if (!is_object($result)) {
				return $this->createError('', 'Ошибка при определении класса диспансеризации');
			}

			$resp = $result->result('array');

			if (is_array($resp) && count($resp) > 0 && in_array($resp[0]['DispClass_Code'], array(4,8,11,12))) {
				if (isset($data['EvnPLDispTeenInspection_id'])) {
					$data['Evn_id'] = $data['EvnPLDispTeenInspection_id'];
				} else {
					$data['Evn_id'] = $data['EvnPLDispOrp_id'];
				}


				$query = "
					select
						RD.Evn_id,
						R.Registry_Num,
						RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
					from
						{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_EvnVizitDisp EVD with(nolock) on EVD.EvnVizitDisp_id = RD.Evn_id
					where
						EVD.EvnVizitDisp_pid = :Evn_id
						and R.RegistryStatus_id = 4 and isnull(RD.{$this->getIsPaidField()},1) = 2
				";
				$resp = $this->queryResult($query, $data);
				$actiontxt = 'Удаление';
				switch($action) {
					case 'delete':
						$actiontxt = 'Удаление';
						break;
					case 'edit':
						$actiontxt = 'Редактирование';
						break;
				}

				if( is_array($resp) && count($resp) > 0 ) {
					return array(
						array('Error_Msg' => 'Запись используется в реестре '.$resp[0]['Registry_Num'].' от '.$resp[0]['Registry_accDate']. '.<br/>'.$actiontxt.' записи невозможно.')
					);
				} else {
					return false;
				}
			}
		}

		$query = "
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
				left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
				--left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			where
				{$filter}
				and (ISNULL(RD.{$this->getIsPaidField()},1) = 2 or R.RegistryStatus_id != 4) --Если запись не оплачена и входит в оплаченный реестр - позволять удалять/редактировать
				and R.Lpu_id = :Lpu_id
			
			union
			
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				{$this->scheme}.{$this->RegistryDataTempObject} RD with (nolock) -- в процессе формирования
				left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
			where
				{$filter}
				and R.Lpu_id = :Lpu_id
		";
		//echo getDebugSql($query, $data); exit;
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			return array(
				array('Error_Msg' => 'Ошибка БД!')
			);
		}

		$actiontxt = 'Удаление';
		switch($action) {
			case 'delete':
				$actiontxt = 'Удаление';
				break;
			case 'edit':
				$actiontxt = 'Редактирование';
				break;
		}

		$resp = $res->result('array');
		if( count($resp) > 0 ) {
			return array(
				array('Error_Msg' => 'Запись используется в реестре '.$resp[0]['Registry_Num'].' от '.$resp[0]['Registry_accDate']. '.<br/>'.$actiontxt.' записи невозможно.')
			);
		} else {
			return false;
		}
	}

	/**
	 *	Проверка вхождения случая в реестр (новая, с учетом поля Registry_sid в случае)
	 */
	function checkEvnInRegistry($data, $action = 'delete')
	{
		if (!in_array(getRegionNick(), array('perm', 'penza'))) {
			// вызываем старый метод, не использующий поле Registry_sid
			return $this->checkEvnInRegistryOld($data, $action);
		}

		$filter = "";

		$idField = "Evn_id";
		$from = "v_Evn E (nolock)";
		if (isset($data['EvnPL_id'])) {
			$filter .= " and E.Evn_rid = :EvnPL_id";
			$data['RegistryType_id'] = 2;
			$rdObject = "EvnVizitPL";
		}
		if (isset($data['EvnPS_id'])) {
			$filter .= " and E.Evn_rid = :EvnPS_id";
			$data['RegistryType_id'] = 1;
			$rdObject = "EvnSection";
		}
		if (isset($data['EvnPLStom_id'])) {
			$filter .= " and E.Evn_rid = :EvnPLStom_id";
			$data['RegistryType_id'] = 16;
			$rdObject = "EvnVizitPLStom";
		}
		if (isset($data['EvnVizitPL_id'])) {
			$filter .= " and E.Evn_id = :EvnVizitPL_id";
			$data['RegistryType_id'] = 2;
			$rdObject = "EvnVizitPL";
		}
		if (isset($data['EvnSection_id'])) {
			$filter .= " and E.Evn_id = :EvnSection_id";
			$data['RegistryType_id'] = 1;
			$rdObject = "EvnSection";
		}
		if (isset($data['EvnVizitPLStom_id'])) {
			$filter .= " and E.Evn_id = :EvnVizitPLStom_id";
			$data['RegistryType_id'] = 16;
			$rdObject = "EvnVizitPLStom";
		}
		if (isset($data['EvnPLDispDop13_id'])) {
			$filter .= " and E.Evn_id = :EvnPLDispDop13_id";
			$data['RegistryType_id'] = 7;
			$rdObject = "EvnPLDispDop13";
		}
		if (isset($data['EvnPLDispProf_id'])) {
			$filter .= " and E.Evn_id = :EvnPLDispProf_id";
			$data['RegistryType_id'] = 11;
			$rdObject = "EvnPLDispProf";
		}
		if (isset($data['EvnPLDispOrp_id'])) {
			$filter .= " and E.Evn_id = :EvnPLDispOrp_id";
			$data['RegistryType_id'] = 9;
			$rdObject = "EvnPLDispOrp";
		}
		if (isset($data['EvnPLDispTeenInspection_id'])) {
			$filter .= " and E.Evn_id = :EvnPLDispTeenInspection_id";
			$data['RegistryType_id'] = 12;
			$rdObject = "EvnPLDispTeenInspection";
		}
		if (isset($data['EvnUslugaPar_id'])) {
			$filter .= " and E.Evn_id = :EvnUslugaPar_id";
			$data['RegistryType_id'] = 15;
			$rdObject = "EvnUslugaPar";
		}
		if (isset($data['EvnUslugaTelemed_id']) && in_array(getRegionNick(), array('ekb', 'perm'))) {
			$filter .= " and E.Evn_id = :EvnUslugaTelemed_id";
			$data['RegistryType_id'] = 15;
			$rdObject = "EvnUslugaTelemed";
		}
		if (isset($data['CmpCallCard_id'])) {
			$from = "v_CmpCallCard E (nolock)";
			$idField = "CmpCallCard_id";
			$filter .= " and E.CmpCallCard_id = :CmpCallCard_id";
			$data['RegistryType_id'] = 6;
			$rdObject = "CmpCallCard";
		}

		if (empty($filter)) {
			return false;
		}

		$filter = "(1 = 1)" . $filter;

		$this->setRegistryParamsByType($data);

		$join = "inner join v_{$rdObject} RD (nolock) on RD.{$rdObject}_id = E.{$idField}";
		$paidField = $rdObject."_IsPaid";

		//#51767
		if (in_array($data['RegistryType_id'], array(7,9,11,12))) {
			if ($action == 'edit') {
				return false;
			}

			$query = "
				select top 1 DC.DispClass_Code
				from v_Evn E with(nolock)
				inner join v_EvnPLDisp EPLD with(nolock) on EPLD.EvnPLDisp_id = E.Evn_id
				inner join v_DispClass DC with(nolock) on DC.DispClass_id = EPLD.DispClass_id
				where {$filter}
			";
			$result = $this->db->query($query, $data);

			if (!is_object($result)) {
				return $this->createError('', 'Ошибка при определении класса диспансеризации');
			}

			$resp = $result->result('array');

			if (is_array($resp) && count($resp) > 0 && in_array($resp[0]['DispClass_Code'], array(4,8,11,12))) {
				if (isset($data['EvnPLDispTeenInspection_id'])) {
					$data['Evn_id'] = $data['EvnPLDispTeenInspection_id'];
				} else {
					$data['Evn_id'] = $data['EvnPLDispOrp_id'];
				}


				$query = "
					select
						E.{$idField} as Evn_id,
						R.Registry_Num,
						RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
					from
						{$from}
						{$join}
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_sid
						inner join v_EvnVizitDisp EVD with(nolock) on EVD.EvnVizitDisp_id = RD.Evn_id
					where
						EVD.EvnVizitDisp_pid = :Evn_id
						and R.RegistryStatus_id = 4 and isnull({$paidField}, 1) = 2
				";
				$resp = $this->queryResult($query, $data);
				$actiontxt = 'Удаление';
				switch($action) {
					case 'delete':
						$actiontxt = 'Удаление';
						break;
					case 'edit':
						$actiontxt = 'Редактирование';
						break;
				}

				if( is_array($resp) && count($resp) > 0 ) {
					return array(
						array('Error_Msg' => 'Запись используется в реестре '.$resp[0]['Registry_Num'].' от '.$resp[0]['Registry_accDate']. '.<br/>'.$actiontxt.' записи невозможно.')
					);
				} else {
					return false;
				}
			}
		}

		$query = "
			select top 1
				E.{$idField} as Evn_id,
				R.Registry_Num,
				RTrim(IsNull(convert(varchar,cast(R.Registry_accDate as datetime),104),'')) as Registry_accDate
			from
				{$from}
				{$join}
				left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_sid
				--left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			where
				{$filter}
				and (ISNULL(RD.{$paidField}, 1) = 2 or R.RegistryStatus_id != 4) --Если запись не оплачена и входит в оплаченный реестр - позволять удалять/редактировать
				and R.Lpu_id = :Lpu_id
		";
		//echo getDebugSql($query, $data); exit;
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			return array(
				array('Error_Msg' => 'Ошибка БД!')
			);
		}
		
		$actiontxt = 'Удаление';
		switch($action) {
			case 'delete':
				$actiontxt = 'Удаление';
			break;
			case 'edit':
				$actiontxt = 'Редактирование';
			break;
		}
		
		$resp = $res->result('array');
		if( count($resp) > 0 ) {
			return array(
				array('Error_Msg' => 'Запись используется в реестре '.$resp[0]['Registry_Num'].' от '.$resp[0]['Registry_accDate']. '.<br/>'.$actiontxt.' записи невозможно.')
			);
		} else {
			return false;
		}
	}

	/**
	 * Аналог checkEvnInRegistry, болеее универсальный, учитывающий настройки
	 */
	function checkEvnAccessInRegistryOld($data, $action = 'delete') {
		$dbreg = $this->load->database(getRegistryChecksDBConnection(), true);

		$baseFilter = "(1=1)";
		$join = "";
		$registryTypeArray = array();

		if (isset($data['EvnPS_id'])) {
			$baseFilter .= " and Evn_rid = :EvnPS_id";
			$registryTypeArray[] = 1;
		}
		if (isset($data['EvnSection_id'])) {
			$baseFilter .= " and Evn_id = :EvnSection_id";
			$registryTypeArray[] = 1;
		}
		if (isset($data['EvnPL_id'])) {
			$baseFilter .= " and Evn_rid = :EvnPL_id";
			$registryTypeArray[] = 2;

			if (getRegionNick() == 'penza') {
				$baseFilter .= " and R.RegistryType_id = :RegistryType_id";
			}

			if (getRegionNick() == 'ufa') {
				$registryTypeArray[] = 17;
			}
		}
		if (isset($data['EvnVizitPL_id'])) {
			$baseFilter .= " and Evn_id = :EvnVizitPL_id";
			$registryTypeArray[] = 2;

			if (getRegionNick() == 'ufa') {
				$registryTypeArray[] = 17;
			}
		}
		if (isset($data['EvnPLStom_id'])) {
			$baseFilter .= " and Evn_rid = :EvnPLStom_id";
			$registryTypeArray[] = 16;
		}
		if (isset($data['EvnVizitPLStom_id'])) {
			$baseFilter .= " and Evn_id = :EvnVizitPLStom_id";
			$registryTypeArray[] = 16;
		}
		if (isset($data['EvnPLDispDop13_id'])) {
			if (getRegionNick() == 'ufa') {
				$baseFilter .= " and Evn_rid = :EvnPLDispDop13_id";
			}
			else {
				$baseFilter .= " and Evn_id = :EvnPLDispDop13_id";
			}

			$registryTypeArray[] = 7;
		}
		if (isset($data['EvnPLDispOrp_id'])) {
			if (getRegionNick() == 'ufa') {
				$baseFilter .= " and Evn_rid = :EvnPLDispOrp_id";
			}
			else {
				$baseFilter .= " and Evn_id = :EvnPLDispOrp_id";
			}

			$registryTypeArray[] = 9;
		}
		if (isset($data['EvnPLDispProf_id'])) {
			$baseFilter .= " and Evn_id = :EvnPLDispProf_id";
			$registryTypeArray[] = 11;
		}
		if (isset($data['EvnPLDispTeenInspection_id'])) {
			$baseFilter .= " and Evn_id = :EvnPLDispTeenInspection_id";
			$registryTypeArray[] = 12;
		}
		if (isset($data['CmpCallCard_id'])) {
			$baseFilter .= " and Evn_id = :CmpCallCard_id";
			$registryTypeArray[] = 6;
		}
		if (isset($data['CmpCloseCard_id'])) {
			$baseFilter .= " and Evn_id = :CmpCloseCard_id";
			$registryTypeArray[] = 6;
		}
		if (isset($data['EvnUslugaPar_id'])) {
			$baseFilter .= " and Evn_id = :EvnUslugaPar_id";
			$registryTypeArray[] = 15;
		}

		if ( count($registryTypeArray) == 0 ) {
			return false;
		}

		if ( in_array($this->regionNick, array('pskov', 'ufa', 'vologda')) ) {
			$paidField = 'Paid_id';
		}
		else {
			$paidField = 'RegistryData_IsPaid';
		}

		$this->load->model('Options_model');
		$globalOptions = $this->Options_model->getOptionsGlobals($data);
		$disableEditInReg = !empty($globalOptions['globals']['registry_disable_edit_inreg'])?intval($globalOptions['globals']['registry_disable_edit_inreg']):2;
		$disableEditPaid = !empty($globalOptions['globals']['registry_disable_edit_paid'])?intval($globalOptions['globals']['registry_disable_edit_paid']):2;

		$checkPriorityArray = array();

		// Сперва проверяем запреты
		if ( $disableEditInReg == 2 ) {
			$checkPriorityArray[] = 'disableEditInReg';
			$checkPriorityArray[] = 'disableEditPaid';
		}
		else {
			$checkPriorityArray[] = 'disableEditPaid';
			$checkPriorityArray[] = 'disableEditInReg';
		}

		$actiontxt = 'Удаление';

		switch ( $action ) {
			case 'edit':
				$actiontxt = 'Редактирование';
				break;
		}

		foreach ( $registryTypeArray as $RegistryType_id ) {
			$data['RegistryType_id'] = $RegistryType_id;
			$filter = $baseFilter;

			// Для 2-ых этапов ДДС и МОН особая логика (в реестр попадает посещение)
			// Либо эта логика устарела, либо нужна только для каких то регионов. На Перми в реестр попадают сами карты.
			if (false && in_array($data['RegistryType_id'], array(7, 9, 11, 12))) {
				$query = "
					select top 1 DC.DispClass_Code
					from v_Evn E with(nolock)
					inner join v_EvnPLDisp EPLD with(nolock) on EPLD.EvnPLDisp_id = E.Evn_id
					inner join v_DispClass DC with(nolock) on DC.DispClass_id = EPLD.DispClass_id
					where {$filter}
				";
				$result = $this->db->query($query, $data);

				if (!is_object($result)) {
					return array('Error_Msg' => 'Ошибка при определении класса диспансеризации');
				}

				$resp = $result->result('array');

				if (is_array($resp) && count($resp) > 0 && in_array($resp[0]['DispClass_Code'], array(4, 8, 11, 12))) {
					if (isset($data['EvnPLDispTeenInspection_id'])) {
						$filter = "EVD.EvnVizitDisp_pid = :EvnPLDispTeenInspection_id";
					} else {
						$filter = "EVD.EvnVizitDisp_pid = :EvnPLDispOrp_id";
					}

					$join .= "inner join v_EvnVizitDisp EVD with (nolock) on EVD.EvnVizitDisp_id = RD.Evn_id";
				}
			}

			$this->setRegistryParamsByType($data, true);

			if ($action == 'delete') {
				// Если случай в реестре, то удаление запрещено, вне зависимости от настроек
				$query = "
					select top 1
						RD.Evn_id,
						R.Registry_Num,
						convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate
					from
						{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
						{$join}
						left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
					where
						{$filter}
						and R.Lpu_id = :Lpu_id
				";
				$res = $dbreg->query($query, $data);
				if (!is_object($res)) {
					return array('Error_Msg' => 'Ошибка БД!');
				}

				$resp = $res->result('array');
				if (count($resp) > 0) {
					return array('Error_Msg' => 'Запись используется в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>Удаление записи невозможно.');
				}

				return false;
			}

			/*
			if (getRegionNick() == 'vologda') {
				// не зависимо от настроек проверяем входит ли в реестр по СМО со статусом «Оплаченные», при этом посещение оплачено
				$ignoreCheck = false;
				if (!empty($data['EvnPL_id']) || !empty($data['EvnPLStom_id']) || !empty($data['EvnVizitPL_id']) || !empty($data['EvnVizitPLStom_id'])) {
					$checkParams = array();
					switch(true) {
						case !empty($data['EvnPL_id']):
							$checkParams['Evn_id'] = $data['EvnPL_id'];
							break;
						case !empty($data['EvnPLStom_id']):
							$checkParams['Evn_id'] = $data['EvnPLStom_id'];
							break;
						case !empty($data['EvnVizitPL_id']):
							$checkParams['Evn_id'] = $data['EvnVizitPL_id'];
							break;
						case !empty($data['EvnVizitPLStom_id']):
							$checkParams['Evn_id'] = $data['EvnVizitPLStom_id'];
							break;
					}
					// проверяем закрыт ли ТАП
					$data['EvnPLBase_id'] = $this->getFirstResultFromQuery("
						select top 1
							e.Evn_id
						from
							v_Evn e (nolock)
							inner join v_EvnPLBase eplb (nolock) on eplb.EvnPLBase_id = e.Evn_rid 
						where
							eplb.EvnPLBase_IsFinish = 2
							and e.Evn_id = :Evn_id
					", $checkParams, true);

					if (!empty($data['EvnPLBase_id'])) {
						$filter = "Evn_rid = :EvnPLBase_id"; // ищем хотя бы один оплаченый случай во всем ТАП
					} else if (empty($data['EvnVizitPL_id']) && empty($data['EvnVizitPLStom_id'])) {
						$ignoreCheck = true; // ТАП доступен для редактирования, если не закрыт
					}
				}

				if (!$ignoreCheck) {

					$res = $dbreg->query("
						select
							ost.OmsSprTerr_id,
							ost.KLRgn_id
						from
							{$this->scheme}.v_{$this->RegistryDataObject} rd (nolock)
							left join v_OmsSprTerr ost with (nolock) on ost.OmsSprTerr_id = rd.OmsSprTerr_id
						where
							{$filter}
					", $data);

					$regData = $res->result('array');
					$filtersmo = "";
					if(!empty($regData[0]['OmsSprTerr_id'])){
						$filtersmo .= " and pt.PayType_SysNick = 'oms'";
					}

					if(!empty($regData[0]['KLRgn_id']) && $regData[0]['KLRgn_id'] == 35){
						$filtersmo .= " and kn.KatNasel_SysNick = 'oblast'";
					}else{
						$filtersmo .= " and kn.KatNasel_SysNick <> 'oblast'";
					}

					$query = "
						select top 1
							RD.Evn_id,
							R.Registry_Num,
							R.RegistryStatus_id,
							convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate
						from
							{$this->scheme}.v_{$this->RegistryDataObject} RD with (NOLOCK)
							{$join}
							left join {$this->scheme}.v_RegistryGroupLink RGL with (nolock) on RD.Registry_id = RGL.Registry_id 
							left join {$this->scheme}.v_Registry R with (NOLOCK) on RGL.Registry_pid = R.Registry_id
							left join v_KatNasel kn (nolock) on kn.KatNasel_id = R.KatNasel_id
							left join v_PayType pt (nolock) on pt.PayType_id = R.PayType_id
						where
							{$filter}
							{$filtersmo}
							and (
								R.RegistryStatus_id = 2
								OR (
									RD.{$paidField} = 2
									and R.RegistryStatus_id = 4
								)
							)
							and R.Lpu_id = :Lpu_id
					";

					$res = $dbreg->query($query, $data);
					if (!is_object($res)) {
						return array('Error_Msg' => 'Ошибка БД!');
					}

					$resp = $res->result('array');
					if (count($resp) > 0 && !($this->getRegionNick() == 'buryatiya' && !empty($data['ArmType']) && $data['ArmType'] == 'mstat')) {
						if ($resp[0]['RegistryStatus_id'] == 4) {
							return array('Error_Msg' => 'Запись входит в реестр по СМО в статусе "Оплаченные" и оплачена в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи невозможно.');
						} else {
							return array('Error_Msg' => 'Запись входит в реестр по СМО в статусе "К оплате" ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи невозможно.');
						}
					}
				}
			}
			*/
			foreach ( $checkPriorityArray as $checkPriority ) {
				switch ( $checkPriority ) {
					case 'disableEditPaid':
						if ( ($disableEditPaid == 3 && empty($data['ignoreCheckRegistry'])) || $disableEditPaid == 2)  {
							// проверяем признак оплаченности
							if($this->getRegionNick() == 'ekb' && isset($data['EvnPS_id'])) {//https://redmine.swan.perm.ru/issues/116984 - если это Екатеринбург и это КВС
								$join .= " left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = Evn_rid ";
								$filter .= " and (EPS.EvnPS_id is null or ISNULL(EPS.EvnPS_IsPaid,1)=2) ";
							}
							$query = "
								select top 1
									RD.Evn_id,
									R.Registry_Num,
									convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate
								from
									{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
									{$join}
									left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
								where
									{$filter}
									and RD.{$paidField} = 2
									and R.Lpu_id = :Lpu_id
							";
							$res = $dbreg->query($query, $data);
							if (!is_object($res)) {
								return array('Error_Msg' => 'Ошибка БД!');
							}

							$resp = $res->result('array');
							if (count($resp) > 0) {
								if ($disableEditPaid == 2 && !($this->getRegionNick() == 'buryatiya' && !empty($data['ArmType']) && $data['ArmType'] == 'mstat')) {
									return array('Error_Msg' => 'Запись оплачена в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи невозможно.');
								} else {
									return array('Error_Msg' => '', 'Alert_Msg' => 'Запись оплачена в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи нежелательно!');
								}
							}
						}
						break;


					case 'disableEditInReg':
						if (($disableEditInReg == 3 && empty($data['ignoreCheckRegistry'])) || $disableEditInReg == 2) {
							// на Астрахани в КВС проверяем только оплаченные
							if($this->getRegionNick() == 'astra' && isset($data['EvnPS_id'])) {
								return false;
							}
							// проверяем наличие в реестре "К оплате"
							$query = "
								select top 1
									RD.Evn_id,
									R.Registry_Num,
									convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate
								from
									{$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock)
									{$join}
									left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
								where
									{$filter}
									and (
										R.RegistryStatus_id in (2, 5, 6)
										or (R.RegistryStatus_id = 4 and ISNULL(RD.{$paidField}, 1) = 2)
									)
									and R.Lpu_id = :Lpu_id
							";
							$res = $dbreg->query($query, $data);
							if (!is_object($res)) {
								return array('Error_Msg' => 'Ошибка БД!');
							}

							$resp = $res->result('array');
							if (count($resp) > 0) {
								if ($disableEditInReg == 2 && !($this->getRegionNick() == 'buryatiya' && !empty($data['ArmType']) && $data['ArmType'] == 'mstat')) {
									return array('Error_Msg' => 'Запись используется в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . ' в статусе "К оплате".<br/>' . $actiontxt . ' записи невозможно.');
								} else {
									return array('Error_Msg' => '', 'Alert_Msg' => 'Запись используется в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . ' в статусе "К оплате".<br/>' . $actiontxt . ' записи нежелательно!');
								}
							}
						}
						break;
				}
			}
		}

		return false;
	}

	/**
	 * Аналог checkEvnInRegistry, болеее универсальный, учитывающий настройки (новый, с учетом поля Registry_sid в случае)
	 */
	function checkEvnAccessInRegistry($data, $action = 'delete') {
		if (!in_array(getRegionNick(), array('perm', 'penza', 'vologda'))) {
			// вызываем старый метод, не использующий поле Registry_sid
			return $this->checkEvnAccessInRegistryOld($data, $action);
		}

		$dbreg = $this->load->database(getRegistryChecksDBConnection(), true);

		$baseFilter = "(1=1)";
		$joinList = array();
		$registryTypeArray = array();

		$idField = "Evn_id";
		$from = "v_Evn E (nolock)";
		if (isset($data['EvnPS_id'])) {
			$baseFilter .= " and E.Evn_rid = :EvnPS_id";
			$registryTypeArray[] = 1;
			$rdObject = "EvnSection";

		}
		if (isset($data['EvnSection_id'])) {
			$baseFilter .= " and E.Evn_id = :EvnSection_id";
			$registryTypeArray[] = 1;
			$rdObject = "EvnSection";

			if ($this->regionNick == 'vologda') {

				$joinList['RD'] = "
					inner join v_EvnSection RD with (nolock) on RD.EvnSection_pid = E.Evn_pid
				";
				$baseFilter .= "
					and (R.RegistryStatus_id <>4 or (ISNULL(RD.EvnSection_IsPaid,1) = 2 and R.RegistryStatus_id = 4))
				";
			}
		}
		if (isset($data['EvnPL_id'])) {
			$baseFilter .= " and E.Evn_rid = :EvnPL_id";
			$registryTypeArray[] = 2;
			$rdObject = "EvnVizitPL";

			if (getRegionNick() == 'penza') {
				$baseFilter .= " and R.RegistryType_id = :RegistryType_id";
			}

			if (getRegionNick() == 'ufa') {
				$registryTypeArray[] = 17;
			}

		}
		if (isset($data['EvnVizitPL_id'])) {
			$baseFilter .= " and E.Evn_id = :EvnVizitPL_id";
			$registryTypeArray[] = 2;
			$rdObject = "EvnVizitPL";

			if (getRegionNick() == 'ufa') {
				$registryTypeArray[] = 17;
			}

			if ($this->regionNick == 'vologda') {

				$joinList['RD'] = "
					inner join v_EvnVizitPL RD with (nolock) on RD.EvnVizitPL_id = E.Evn_id
				";

				$baseFilter = " EvnVizitPL_id = :EvnVizitPL_id";
			}

		}
		if (isset($data['EvnPLStom_id'])) {
			$baseFilter .= " and E.Evn_rid = :EvnPLStom_id";
			$registryTypeArray[] = 16;
			$rdObject = "EvnVizitPLStom";

		}
		if (isset($data['EvnVizitPLStom_id'])) {
			$baseFilter .= " and E.Evn_id = :EvnVizitPLStom_id";
			$registryTypeArray[] = 16;
			$rdObject = "EvnVizitPLStom";

			if ($this->regionNick == 'vologda') {
				$joinList['RD'] = "
					inner join v_EvnVizitPLStom RD with (nolock) on RD.EvnVizitPLStom_id = E.Evn_id
				";

				$baseFilter = " EvnVizitPLStom_id = :EvnVizitPLStom_id";
			}

		}
		if (isset($data['EvnPLDispDop13_id'])) {
			if (getRegionNick() == 'ufa') {
				$baseFilter .= " and E.Evn_rid = :EvnPLDispDop13_id";
			}
			else {
				$baseFilter .= " and E.Evn_id = :EvnPLDispDop13_id";
			}

			$registryTypeArray[] = 7;
			$rdObject = "EvnPLDispDop13";
		}
		if (isset($data['EvnPLDispOrp_id'])) {
			if (getRegionNick() == 'ufa') {
				$baseFilter .= " and E.Evn_rid = :EvnPLDispOrp_id";
			}
			else {
				$baseFilter .= " and E.Evn_id = :EvnPLDispOrp_id";
			}

			$registryTypeArray[] = 9;
			$rdObject = "EvnPLDispOrp";
		}
		if (isset($data['EvnPLDispProf_id'])) {
			$baseFilter .= " and E.Evn_id = :EvnPLDispProf_id";
			$registryTypeArray[] = 11;
			$rdObject = "EvnPLDispProf";
		}
		if (isset($data['EvnPLDispTeenInspection_id'])) {
			$baseFilter .= " and E.Evn_id = :EvnPLDispTeenInspection_id";
			$registryTypeArray[] = 12;
			$rdObject = "EvnPLDispTeenInspection";
		}
		if (isset($data['CmpCallCard_id'])) {
			$from = "v_CmpCallCard E (nolock)";
			$idField = "CmpCallCard_id";
			$baseFilter .= " and E.CmpCallCard_id = :CmpCallCard_id";
			$registryTypeArray[] = 6;
			$rdObject = "CmpCallCard";
		}
		if (isset($data['CmpCloseCard_id'])) {
			$from = "v_CmpCloseCard E (nolock)";
			$idField = "CmpCloseCard_id";
			$baseFilter .= " and E.CmpCloseCard_id = :CmpCloseCard_id";
			$registryTypeArray[] = 6;
			$rdObject = "CmpCloseCard";
		}
		if (isset($data['EvnUslugaPar_id'])) {
			$baseFilter .= " and E.Evn_id = :EvnUslugaPar_id";
			$registryTypeArray[] = 15;
			$rdObject = "EvnUslugaPar";
		}

		if ( count($registryTypeArray) == 0 ) {
			return false;
		}

		if (!isset($joinList['RD'])) {
			$joinList['RD'] = "inner join v_{$rdObject} RD (nolock) on RD.{$rdObject}_id = E.{$idField}";
		}

		$paidField = $rdObject."_IsPaid";

		$this->load->model('Options_model');
		$globalOptions = $this->Options_model->getOptionsGlobals($data);
		$disableEditInReg = !empty($globalOptions['globals']['registry_disable_edit_inreg'])?intval($globalOptions['globals']['registry_disable_edit_inreg']):2;
		$disableEditPaid = !empty($globalOptions['globals']['registry_disable_edit_paid'])?intval($globalOptions['globals']['registry_disable_edit_paid']):2;

		$checkPriorityArray = array();

		// Сперва проверяем запреты
		if ( $disableEditInReg == 2 ) {
			$checkPriorityArray[] = 'disableEditInReg';
			$checkPriorityArray[] = 'disableEditPaid';
		}
		else {
			$checkPriorityArray[] = 'disableEditPaid';
			$checkPriorityArray[] = 'disableEditInReg';
		}

		$actiontxt = 'Удаление';

		switch ( $action ) {
			case 'edit':
				$actiontxt = 'Редактирование';
				break;
		}

		foreach ( $registryTypeArray as $RegistryType_id ) {
			$data['RegistryType_id'] = $RegistryType_id;
			$filter = $baseFilter;

			$this->setRegistryParamsByType($data, true);

			if ($action == 'delete') {
				
				if ((isset($data['EvnVizitPL_id']) || isset($data['EvnVizitPLStom_id'])) && $this->regionNick == 'vologda') {

					$query = "
						select 
							convert(varchar(10), RD.{$rdObject}_setDate, 104) as SetDate,
							E.{$idField} as Evn_id,
							R.Registry_Num,
							convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
							R.RegistryStatus_id,
							isnull(RD.{$paidField},1)  as IsPaid
						from
							{$from}
							" . implode(' ', $joinList) . "
							left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_sid
							outer apply (
								select 
									{$rdObject}_NumGroup,
									{$rdObject}_pid
								from
									v_{$rdObject} (nolock)
								where
									{$baseFilter}
									and Lpu_id = :Lpu_id
							) evplng
						where
							RD.{$rdObject}_pid=evplng.{$rdObject}_pid
							and RD.{$rdObject}_NumGroup = evplng.{$rdObject}_NumGroup
					";

					$res = $dbreg->query($query, $data);
					if (!is_object($res)) {
						return array('Error_Msg' => 'Ошибка БД!');
					}

					$resp = $res->result('array');
					if (count($resp) > 0) {
						$Error_Msg = "";
						foreach ($resp as $key => $value) {

							if ($value['RegistryStatus_id'] == 2) {
								$Error_Msg .= '<br/> Посещение от ' . $value['SetDate'] . '  входит в реестр в статусе "К оплате" ' . $value['Registry_Num'] . ' от ' . $value['Registry_accDate'];
							}
							if ($value['RegistryStatus_id'] == 4) {
								if ($value['IsPaid'] == 2) {
									$Error_Msg .= '<br/> Посещение от ' . $value['SetDate'] . ' оплачено в реестре ' . $value['Registry_Num'] . ' от ' . $value['Registry_accDate'];
								}
							}
						}

						if (!empty($Error_Msg)) {
							return array('Error_Msg' => 'Посещение нельзя удалять.' . $Error_Msg);
						}
					}

					return false;
				}
				// Если случай в реестре, то удаление запрещено, вне зависимости от настроек
				$query = "
					select top 1
						E.{$idField} as Evn_id,
						R.Registry_Num,
						convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate
					from
						{$from}
						" . implode(' ', $joinList) . "
						left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_sid
					where
						{$filter}
						and R.Lpu_id = :Lpu_id
				";
				$res = $dbreg->query($query, $data);
				if (!is_object($res)) {
					return array('Error_Msg' => 'Ошибка БД!');
				}

				$resp = $res->result('array');
				if (count($resp) > 0) {
					return array('Error_Msg' => 'Запись используется в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>Удаление записи невозможно.');
				}

				return false;
			}

			//ТАП/КВС НЕ доступен/-на для редактирования, если выполняется одно из условий: (#178822)
			//1.ТАП/КВС закрыт/-та и хотя бы одно посещение/движение входит в реестр со статусом «К оплате»;
			//2.ТАП/КВС закрыт/-та и хотя бы одно посещение/движение входит в реестр со статусом «Оплаченные» и имеет признак оплаты (IsPaid = 2).
			if (getRegionNick() == 'vologda' && $action != 'delete') {
				if (!empty($data['EvnPL_id']) || !empty($data['EvnPLStom_id']) ||  !empty($data['EvnPS_id']) ) {
					$filter = "(1=1)";
					$checkParams = array();
					switch(true) {
						case !empty($data['EvnPL_id']):
							$checkParams['Evn_id'] = $data['EvnPL_id'];
							$filter .= " and RD.TreatmentClass_id <> 2";
							break;
						case !empty($data['EvnPLStom_id']):
							$checkParams['Evn_id'] = $data['EvnPLStom_id'];
							$filter .= " and RD.TreatmentClass_id <> 2";
							break;
						case !empty($data['EvnPS_id']):
							$checkParams['Evn_id'] = $data['EvnPS_id'];
							break;
					}
					// проверяем закрыт ли ТАП/КВС
					if(!empty($data['EvnPS_id'])) {
						$data['EvnPLBase_id'] = $this->getFirstResultFromQuery("
								select top 1
									e.EvnPS_id
								from
									v_EvnPS e (nolock)
								where
									e.EvnPS_disDT is not null
									and e.EvnPS_id = :Evn_id
					", $checkParams, true);
					}else {
						$data['EvnPLBase_id'] = $this->getFirstResultFromQuery("
						select top 1
							EvnPLBase_id
						from 
							v_EvnPLBase with (nolock)
						where
							EvnPLBase_id = :Evn_id
							and EvnPLBase_IsFinish=2
					", $checkParams,true);
					}

					if (!empty($data['EvnPLBase_id'])) {
						$filter .= " and Evn_rid = :EvnPLBase_id";
						
						$query = "
							select
								convert(varchar(10), RD.{$rdObject}_setDate, 104) as SetDate,
								E.{$idField} as Evn_id,
								R.Registry_Num,
								convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
								R.RegistryStatus_id as RegistryStatus_id
							from
								{$from}
								" . implode(' ', $joinList) . "
								left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_sid
							where
								{$filter}
								and (
									R.RegistryStatus_id = 2
									OR (
										RD.{$paidField} = 2
										and R.RegistryStatus_id = 4
										)
								)
								and R.Lpu_id = :Lpu_id
						";
						
						$res = $dbreg->query($query, $data);
						if (!is_object($res)) {
							return array('Error_Msg' => 'Ошибка БД!');
						}
						
						$resp = $res->result('array');
						if (count($resp) > 0) {
							foreach ($resp as $key=>$value) {
								if(!empty($data['EvnPS_id'])){
									$vid_usl='Движение';
									$Error_Msg='КВС нельзя редактировать.';
								}else {
									$vid_usl='Посещение';
									$Error_Msg = 'ТАП нельзя редактировать.';
								}
								if($value['RegistryStatus_id']==2) {
									$Error_Msg .= '<br/>'.$vid_usl.' от '.$value['SetDate'].'  входит в реестр в статусе "К оплате" ' . $value['Registry_Num'] . ' от ' . $value['Registry_accDate'];
								}
								if($value['RegistryStatus_id']==4) {
									$Error_Msg .= '<br/> '.$vid_usl.' от '.$value['SetDate'].' оплачено в реестре ' . $value['Registry_Num'] . ' от ' . $value['Registry_accDate'];
								}
							}
							return array('Error_Msg' => $Error_Msg);
						}else{
							return false;
						}
					} else {
						return false; // ТАП/КВС доступен для редактирования, если не закрыт
					}
				}
			}

			foreach ( $checkPriorityArray as $checkPriority ) {
				switch ( $checkPriority ) {
					case 'disableEditPaid':
						if ( ($disableEditPaid == 3 && empty($data['ignoreCheckRegistry'])) || $disableEditPaid == 2)  {
							// проверяем признак оплаченности
							if($this->getRegionNick() == 'ekb' && isset($data['EvnPS_id'])) {//https://redmine.swan.perm.ru/issues/116984 - если это Екатеринбург и это КВС
								$joinList[] = "left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = Evn_rid";
								$filter .= " and (EPS.EvnPS_id is null or ISNULL(EPS.EvnPS_IsPaid,1)=2) ";
							}
							$query = "
								select top 1
									E.{$idField} as Evn_id,
									R.Registry_Num,
									convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate
								from
									{$from}
									" . implode(' ', $joinList) . "
									left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_sid
								where
									{$filter}
									and RD.{$paidField} = 2
									and R.Lpu_id = :Lpu_id
							";
							$res = $dbreg->query($query, $data);
							if (!is_object($res)) {
								return array('Error_Msg' => 'Ошибка БД!');
							}

							$resp = $res->result('array');
							if (count($resp) > 0) {
								if ($disableEditPaid == 2 && !($this->getRegionNick() == 'buryatiya' && !empty($data['ArmType']) && $data['ArmType'] == 'mstat')) {
									return array('Error_Msg' => 'Запись оплачена в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи невозможно.');
								} else {
									return array('Error_Msg' => '', 'Alert_Msg' => 'Запись оплачена в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . '.<br/>' . $actiontxt . ' записи нежелательно!');
								}
							}
						}
						break;


					case 'disableEditInReg':
						if (($disableEditInReg == 3 && empty($data['ignoreCheckRegistry'])) || $disableEditInReg == 2) {
							// на Астрахани в КВС проверяем только оплаченные
							if($this->getRegionNick() == 'astra' && isset($data['EvnPS_id'])) {
								return false;
							}
							// проверяем наличие в реестре "К оплате"
							$query = "
								select top 1
									E.{$idField} as Evn_id,
									R.Registry_Num,
									convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate
								from
									{$from}
									" . implode(' ', $joinList) . "
									left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_sid
								where
									{$filter}
									and (
										R.RegistryStatus_id in (2, 5, 6)
										or (R.RegistryStatus_id = 4 and ISNULL(RD.{$paidField}, 1) = 2)
									)
									and R.Lpu_id = :Lpu_id
							";
							$res = $dbreg->query($query, $data);
							if (!is_object($res)) {
								return array('Error_Msg' => 'Ошибка БД!');
							}

							$resp = $res->result('array');
							if (count($resp) > 0) {
								if ($disableEditInReg == 2 && !($this->getRegionNick() == 'buryatiya' && !empty($data['ArmType']) && $data['ArmType'] == 'mstat')) {
									return array('Error_Msg' => 'Запись используется в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . ' в статусе "К оплате".<br/>' . $actiontxt . ' записи невозможно.');
								} else {
									return array('Error_Msg' => '', 'Alert_Msg' => 'Запись используется в реестре ' . $resp[0]['Registry_Num'] . ' от ' . $resp[0]['Registry_accDate'] . ' в статусе "К оплате".<br/>' . $actiontxt . ' записи нежелательно!');
								}
							}
						}
						break;
				}
			}
		}

		return false;
	}
	
	/**
	 *	Проверка вхождения случаев, в которых указано отделение, в реестр
	 *	В региональных моделях: perm, ufa
	 */
	function checkLpuSectionInRegistry($data)
	{
		$filter = "1=1";

		if(isset($data['LpuUnit_id'])) {
			$filter .= " and LS.LpuUnit_id = :LpuUnit_id";
		}
		if(isset($data['LpuSection_id'])) {
			$filter .= " and RD.LpuSection_id = :LpuSection_id";
		}
		
		$query = "
			select top 1
				RD.Evn_id,
				R.Registry_Num,
				convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate
			from
				{$this->scheme}.v_RegistryData RD with (nolock)
				left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
				left join v_LpuSection LS with (nolock) on RD.LpuSection_id = LS.LpuSection_id
			where
				{$filter}
				and R.RegistryStatus_id = 4
				and isnull(RD.RegistryData_deleted, 1) = 1
		";
		// echo getDebugSql($query, $data); exit;
		$res = $this->db->query($query, $data);
		if (!is_object($res)) {
			return array(
				array('Error_Msg' => 'Ошибка БД!')
			);
		}
		$resp = $res->result('array');
		if( count($resp) > 0 ) {
			if(isset($data['LpuSection_id'])) {
				return "Изменение профиля отделения невозможно, для отделения существуют оплаченные реестры.";
			} else {
				return "Изменение типа группы отделений невозможно, для некоторых отделений существуют оплаченные реестры.";
			}
		} else {
			return "";
		}
	}
	
	
	/**
	 * Проверяет находится ли карта вызова в реестре?
	 * 
	 * @param array $data Набор параметров
	 * @return bool|array on error
	 */
	function checkCmpCallCardInRegistry( $data ){
		
		if ( !array_key_exists( 'CmpCallCard_id', $data ) || !$data['CmpCallCard_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор карты вызова' ) );
		}
		
		if ( empty( $data[ 'session' ][ 'region' ][ 'number' ] ) ) {
			return $this->createError('', 'Не указан код региона. Обратитесь к администратору');
		}
		
		$region = $data[ 'session' ][ 'region' ][ 'number' ];
		
		
		switch ( $region ) {
			case 2:
			case 60:
				$sql = "
					select top 1
						CClC.CmpCloseCard_id
					from
						v_CmpCloseCard CClC with(nolock)
						left join {$this->scheme}.RegistryDataCmp rd on rd.CmpCloseCard_id = CClC.CmpCloseCard_id
						inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
					where
						CClC.CmpCallCard_id = :CmpCallCard_id
						and (rd.RegistryDataCmp_IsPaid = 2 or (r.LpuUnitSet_id is not null and r.OrgSmo_id is not null))
					";
				break ;
			case 66:
				$sql = "
					select top 1
						CCC.CmpCallCard_id
					from 
						v_CmpCallCard CCC with (NOLOCK)
						inner join  {$this->scheme}.RegistryDataCmp   with (NOLOCK) on CCC.CmpCallCard_id = RegistryDataCmp.CmpCallCard_id
						inner join  {$this->scheme}.v_Registry r on r.Registry_id = RegistryDataCmp.Registry_id
					where
						CCC.CmpCallCard_id = :CmpCallCard_id AND CCC.CmpCallCard_IsInReg = 2
				";
				break ;
			default:
				$sql = "
					SELECT TOP 1
						ccc.CmpCallCard_id
					FROM
						v_CmpCallCard as ccc with(nolock)
						LEFT JOIN {$this->scheme}.v_RegistryDataCmp as rdc ON( rdc.Evn_id=ccc.CmpCallCard_id )
						LEFT JOIN {$this->scheme}.v_Registry r ON( r.Registry_id=rdc.Registry_id )
						LEFT JOIN v_RegistryCheckStatus rcs with(nolock) ON( rcs.RegistryCheckStatus_id=r.RegistryCheckStatus_id )
					WHERE
						ccc.CmpCallCard_id=:CmpCallCard_id
						AND (ccc.CmpCallCard_IsInReg=2 OR rcs.RegistryCheckStatus_Code!=3)
				";
				break ;
		}
		
		$query = $this->db->query( $sql, $data );
		if ( is_object( $query ) ) {
			
			$result = $query->result('array');
			if ( sizeof( $result ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	
	/**
	 *	Комментарий
	 */
	function deleteRegistryDouble($data)
	{	
		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$this->scheme}.p_RegistryDouble_del
				@Registry_id = :Registry_id,
				@Evn_id = :Evn_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Комментарий
	 */
	function deleteRegistryDoubleAll($data)
	{
		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$this->scheme}.p_RegistryDouble_del_all
				@Registry_id = :Registry_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}


	/**
	 *	Установка реестру признака "нуждается в переформировании"
	 */
	function setRegistryIsNeedReform($data) {
		$query = "
			declare
				 @Error_Code bigint
				,@Error_Message varchar(4000);

			exec " . $this->scheme . ".p_Registry_setIsNeedReform 
				@Registry_id = :Registry_id,
				@Registry_IsNeedReform = :Registry_IsNeedReform,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		";
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Отметка на удаление записей в RegistryData, относящихся к $Evn_id, для списка реестров $registryList
	 *	Метод используется при переносе случая на другого человека (Evn->setAnotherPersonForDocument)
	 *	$registryList: ключи - Registry_id, значения - RegistryType_SysNick
	 */
	function setRegistryDataDeleted($registryList, $Evn_id = null) {
		if ( !is_array($registryList) || count($registryList) == 0 ) {
			return false;
		}

		foreach ( $registryList as $id => $type ) {
			switch ( $type ) {
				case 'omsstac':
				case 'omspol':
				case 'stom':
				case 'dopdispfirst':
				case 'dopdispsecond':
				case 'orpdispfirst':
				case 'orpdispsecond':
				case 'profsurvey':
				case 'htm':
				case 'parusl':
					$query = "
						declare
							@Error_Code bigint = null,
							@Error_Message varchar(4000) = '';

						set nocount on

						begin try
							update {$this->scheme}.RegistryData" . (in_array($type, array('htm', 'omsstac')) ? "EvnPS" : "") . " with (rowlock)
							set RegistryData_deleted = 2
							where Registry_id = :Registry_id
								and Evn_rid = :Evn_rid
						end try

						begin catch
							set @Error_Code = error_number()
							set @Error_Message = error_message()
						end catch

						set nocount off

						select @Error_Code as Error_Code, @Error_Message as Error_Msg
					";

					$queryParams = array('Registry_id' => $id, 'Evn_rid' => $Evn_id);
				break;

				case 'smp':
					$query = "
						declare
							@Error_Code bigint = null,
							@Error_Message varchar(4000) = '';

						set nocount on

						begin try
							update {$this->scheme}.RegistryDataCmp with (rowlock)
							set RegistryDataCmp_deleted = 2
							where Registry_id = :Registry_id
								and CmpCallCard_id = :CmpCallCard_id
						end try

						begin catch
							set @Error_Code = error_number()
							set @Error_Message = error_message()
						end catch

						set nocount off

						select @Error_Code as Error_Code, @Error_Message as Error_Msg
					";

					$queryParams = array('Registry_id' => $id, 'CmpCallCard_id' => $Evn_id);
				break;

				default:
					return array(array('Error_Msg' => 'Недопустимый тип реестра'));
				break;
			}

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
			}

			$res = $result->result('array');

			if ( !is_array($res) || count($res) == 0 ) {
				return array(array('Error_Msg' => 'Ошибка при отметке записей реестра на удаление'));
			}
			else if ( !empty($res[0]['Error_Msg']) ) {
				return $res;
			}
		}

		return array(array('Error_Msg' => ''));
	}


	/**
	 *	Комментарий
	 */
	function loadRegistryCheckStatusHistory($data) 
	{
		$query = "
			Select
				-- select
				RCSH.RegistryCheckStatusHistory_id,
				RCS.RegistryCheckStatus_Name,
				RTrim(IsNull(convert(varchar,cast(RCSH.Registry_CheckStatusDate as datetime),104) + ' ' + convert(varchar(5), RCSH.Registry_CheckStatusDate, 114), '')) as Registry_CheckStatusDate,
				RTrim(IsNull(convert(varchar,cast(RCSH.Registry_CheckStatusTFOMSDate as datetime),104) + ' ' + convert(varchar(5), RCSH.Registry_CheckStatusTFOMSDate, 114), '')) as Registry_CheckStatusTFOMSDate
				-- end select
			from 
				-- from
				{$this->scheme}.v_RegistryCheckStatusHistory RCSH with (NOLOCK)
				left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = RCSH.RegistryCheckStatus_id
				-- end from
			where
				-- where
				RCSH.Registry_id = :Registry_id
				-- end where
			order by 
				-- order by
				ISNULL(RCSH.Registry_CheckStatusDate,RCSH.Registry_CheckStatusTFOMSDate)
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Получение списк СМО входящих в реестр
	 */
	function getOrgSMOListForExportRegistry($data) {
		$query = "
			select distinct 
				smo.OrgSMO_id,
				smo.OrgSMO_Nick,
				smo.OrgSMO_Name
			from
				{$this->scheme}.RegistryData rd
				inner join v_Polis Polis with (nolock) on Polis.Polis_id = rd.Polis_id
				inner join v_OrgSMO smo with(nolock) on smo.OrgSMO_id = Polis.OrgSMO_id
			where
				rd.Registry_id = :Registry_id
		";
		
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение системное наименование указанной категории населения
	 */
	function getKatNaselSysNickById($KatNasel_id = null) {
		if ( empty($KatNasel_id) ) {
			return false;
		}

		$query = "
			select top 1 KatNasel_SysNick
			from v_KatNasel with (nolock)
			where KatNasel_id = :KatNasel_id
		";

		$queryParams = array('KatNasel_id' => $KatNasel_id);

		return $this->getFirstResultFromQuery($query, $queryParams);
	}

	/**
	 * Получение категории населения в реестре
	 */
	function getKatNasel($Registry_id) {
		if ( empty($Registry_id) ) {
			return false;
		}

		$query = "
			select top 1
				KN.KatNasel_id,
				KN.KatNasel_Code,
				KN.KatNasel_Name,
				KN.KatNasel_SysNick
			from
				{$this->scheme}.v_Registry R with(nolock)
				inner join v_KatNasel KN with(nolock) on KN.KatNasel_id = R.KatNasel_id
			where R.Registry_id = :Registry_id
		";

		$queryParams = array('Registry_id' => $Registry_id);

		return $this->getFirstRowFromQuery($query, $queryParams);
	}

	/**
	 *	Получение кода СМО
	 */
	function getOrgSmoCode($Registry_id = null) {
		if ( empty($Registry_id) ) {
			return false;
		}

		$query = "
			select top 1 os.OrgSmo_f002smocod
			from {$this->scheme}.v_Registry r with (nolock)
				inner join v_OrgSmo os with (nolock) on os.OrgSmo_id = r.OrgSmo_id
			where r.Registry_id = :Registry_id
		";

		$queryParams = array('Registry_id' => $Registry_id);

		return $this->getFirstResultFromQuery($query, $queryParams);
	}

	/**
	 * Получение даты и номера реестра
	 */
	function getRegistryNumberAndDate($data) {
		$params = array('Registry_id' => $data['Registry_id']);
		$dateMode = !empty($data['dateMode'])?$data['dateMode']:'104';
		$query = "
			select top 1
				R.Registry_Num,
				convert(varchar(10),R.Registry_accDate,{$dateMode}) as Registry_accDate
			from {$this->scheme}.v_Registry R with(nolock)
			where R.Registry_id = :Registry_id
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение пути до файла выгрузки по реестру
	 */
	function getRegistryXmlExportPath($data) {
		$params = array('Registry_id' => $data['Registry_id']);
		$query = "
			select top 1 RTrim(R.Registry_xmlExportPath) as Registry_xmlExportPath
			from {$this->scheme}.v_Registry R with(nolock)
			where R.Registry_id = :Registry_id
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getLoadRegistryAdditionalFields() {
		return '';
	}
	
	/**
	 *	Получение списка дополнительных джойнов для запроса
	 */
	function getLoadRegistryAdditionalJoin() {
		return '';
	}
	
	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getLoadRegistryQueueAdditionalFields() {
		return '';
	}
	
	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getReformRegistryAdditionalFields() {
		return '';
	}

	/**
	 *	Получение пустой строки для услуги в выгрузке реестра в XML
	 */
	function getEmptyUslugaXmlRow() {
		return array();
	}

	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	public function setRegistryParamsByType($data = array(), $force = false) {
		if ( !empty($this->RegistryType_id) && $force == false ) {
			return false;
		}

		if ( !empty($data['RegistryType_id']) && is_numeric($data['RegistryType_id']) ) {
			$this->RegistryType_id = $data['RegistryType_id'];
		}
		else if ( !empty($data['Registry_id']) ) {
			if ( !isset($this->_registryTypesList[$data['Registry_id']]) ) {
				$this->_registryTypesList[$data['Registry_id']] = $this->__getRegistryTypeFromDB($data['Registry_id']);
			}

			$this->RegistryType_id = $this->_registryTypesList[$data['Registry_id']];
		}

		$this->RegistryDataEvnField = 'Evn_id';
		$this->RegistryDataObject = 'RegistryData';
		$this->RegistryDataTempObject = 'RegistryDataTmp';
		$this->RegistryDataObjectTable = 'RegistryData';
		$this->RegistryErrorObject = 'RegistryError';
		$this->RegistryErrorComObject = 'RegistryErrorCom';
		$this->RegistryPersonObject = 'RegistryPerson';
		$this->RegistryDoubleObject = 'RegistryDouble';
		$this->RegistryNoPolis = 'RegistryNoPolis';
		$this->RegistryNoPolisObject = 'RegistryNoPolis';
	}

	/**
	 * Получение типа реестра из БД
	 */
	function __getRegistryTypeFromDB($Registry_id = null) {
		if ( empty($Registry_id) ) {
			return null;
		}

		$query = "
			select top 1 RegistryType_id, 1 as sort_field
			from {$this->scheme}.v_Registry with (nolock)
			where Registry_id = :Registry_id
			union all
			select top 1 RegistryType_id, 2 as sort_field
			from {$this->scheme}.v_RegistryQueue with (nolock)
			where RegistryQueue_id = :Registry_id
		";

		if ( $this->__checkRegistryDeletedViewExists() === true ) {
			$query .= "
				union all
				select top 1 RegistryType_id, 1 as sort_field
				from {$this->scheme}.v_Registry_deleted with (nolock)
				where Registry_id = :Registry_id
			";
		}

		$query .= "
			order by sort_field
		";

		$res = $this->db->query($query, array('Registry_id' => $Registry_id));

		if ( !is_object($res) ) {
			return null;
		}

		$resp = $res->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			return null;
		}
		else {
			return $resp[0]['RegistryType_id'];
		}
	}

	/**
	 * Получение информации о наличии в $this->scheme объекта v_Registry_deleted
	 * @task https://redmine.swan.perm.ru/issues/42392
	 * @return boolean
	 */
	private function __checkRegistryDeletedViewExists() {
		$query = "
			select top 1 o.[object_id]
			from [sys].[objects] o with (nolock)
				inner join [sys].[schemas] s with (nolock) on s.[schema_id] = o.[schema_id]
			where o.[type] = 'V'
				and s.[name] = '{$this->scheme}'
				and o.[name] = 'v_Registry_deleted'
		";
		$res = $this->db->query($query);

		if ( !is_object($res) ) {
			return false;
		}

		$resp = $res->result('array');

		return (is_array($resp) && count($resp) > 0 && !empty($resp[0]['object_id']));
	}

	/**
	 * Получение списка типов реестров, входящих в объединенный реестр
	 */
	function getUnionRegistryTypes($Registry_pid = 0) {
		if ( !is_array($this->_unionRegistryTypes) || count($this->_unionRegistryTypes) == 0 ) {
			$this->_unionRegistryTypes = [];

			$resp = $this->queryResult("
				select distinct r.RegistryType_id
				from {$this->scheme}.v_RegistryGroupLink rgl with (nolock)
					inner join {$this->scheme}.v_Registry r with (nolock) on r.Registry_id = rgl.Registry_id
				where rgl.Registry_pid = :Registry_pid
			", [
				'Registry_pid' => $Registry_pid
			]);

			foreach ( $resp as $rec ) {
				$this->_unionRegistryTypes[] = $rec['RegistryType_id'];
			}
		}

		return $this->_unionRegistryTypes;
	}

	/**
	 * Получение списка идентификаторов и типов реестров, входящих в объединенный реестр
	 */
	function getUnionRegistryContent($Registry_pid = 0) {
		$query = "
			select r.Registry_id, r.RegistryType_id
			from {$this->scheme}.v_RegistryGroupLink rgl with (nolock)
				inner join {$this->scheme}.v_Registry r with (nolock) on r.Registry_id = rgl.Registry_id
			where rgl.Registry_pid = :Registry_pid
		";
		$result = $this->db->query($query, array('Registry_pid' => $Registry_pid));

		if ( !is_object($result) ) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Получение данные для экспорта в DBF
	 * @task https://redmine.swan.perm.ru/issues/40204
	 */
	function loadRegistryDataForDbfExport($data, $type) {
		$response = array();

		if ( 13 == $type ) { // Объединенный реестр
			$unionRegistryContent = $this->getUnionRegistryContent($data['Registry_id']);
		}
		else if ( !empty($type) && is_numeric($type) ) {
			$unionRegistryContent = array(
				array('Registry_id' => $data['Registry_id'], 'RegistryType_id' => $type)
			);
		}

		if ( !is_array($unionRegistryContent) || count($unionRegistryContent) == 0 ) {
			return false;
		}

		foreach ( $unionRegistryContent as $registry ) {
			$object = '';
			$procedures = array();

			switch ( $registry['RegistryType_id'] ) {
				case 1: // stac
					$object = 'EvnPS';
				break;

				case 2: // polka
				case 16: // stom
					$object = 'EvnPL';
				break;

				case 4: // dd
					$object = 'EvnPLDD';
				break;

				case 5: // orp
					$object = 'EvnPLOrp';
				break;

				case 6: // smp
					$object = 'SMP';
				break;

				case 7: // dd-2013-1
				case 8: // dd-2013-2
					$object = 'EvnPLDD13';
				break;

				case 9: // orp-2013-1
				case 10: // orp-2013-2
					$object = 'EvnPLOrp13';
				break;

				case 11: // prof
					$object = 'EvnPLProf';
				break;

				case 12: // teen inspection
					$object = 'EvnPLProfTeen';
				break;

				case 14: // htm
					$object = 'EvnHTM';
				break;

				case 15: // par
					$object = 'EvnUslugaPar';
				break;

				default:
					return false;
				break;
			}

			$procedures['PERS'] = $this->scheme.".p_Registry_".$object."_expPac";
			$procedures['SCHET'] = $this->scheme.".p_Registry_".$object."_expScet";
			$procedures['USL'] = $this->scheme.".p_Registry_".$object."_expUsl";
			$procedures['VIZIT'] = $this->scheme.".p_Registry_".$object."_expVizit";

			$query = "exec {$procedures['SCHET']} @Registry_id = :Registry_id";
			$result = $this->db->query($query, array('Registry_id' => $registry['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			$response['data'] = array();
			$response['header'] = $result->result('array');

			$persFields = array();
			$uslugaFields = array();
			$vizitFields = array();

			// Получаем данные по случаям
			$query = "exec {$procedures['VIZIT']} @Registry_id = :Registry_id";
			$result = $this->db->query($query, array('Registry_id' => $registry['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			$resp = $result->result('array');

			if ( !is_array($resp) || count($resp) == 0 ) {
				return $response;
			}

			$vizitData = array();
			$vizitFields = array();

			foreach ( $resp[0] as $key => $value ) {
				if (in_array($key,array('USL','MaxEvn_id'))) continue;
				$newKey = 'S_' . $key;

				switch($key) {
					case 'CODE_MES1':
						$newKey = 'S_CODEMES1';
						break;

					case 'CODE_MES2':
						$newKey = 'S_CODEMES2';
						break;

					default:
						if ( strlen($newKey) > 10 ) {
							$newKey = substr($newKey, 0, 10);
						}
				}

				$vizitFields[$key] = $newKey;
			}

			foreach ( $resp as $visit ) {
				$vizitData[$visit['IDCASE']] = $visit;
			}

			// Получаем данные по пациентам
			$query = "exec {$procedures['PERS']} @Registry_id = :Registry_id";
			$result = $this->db->query($query, array('Registry_id' => $registry['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			$resp = $result->result('array');

			if ( !is_array($resp) || count($resp) == 0 ) {
				return $response;
			}

			$persData = array();
			$persFields = array();

			foreach ( $resp[0] as $key => $value ) {
				if (in_array($key,array('person_id','MaxEvn_id'))) continue;
				$newKey = 'P_' . $key;

				if ( strlen($newKey) > 10 ) {
					$newKey = substr($newKey, 0, 10);
				}

				$persFields[$key] = $newKey;
			}

			foreach ( $resp as $person ) {
				$persData[$person['ID_PAC']] = $person;
			}

			// Получаем данные по услугам
			$query = "exec {$procedures['USL']} @Registry_id = :Registry_id";
			$result = $this->db->query($query, array('Registry_id' => $registry['Registry_id']));

			if ( !is_object($result) ) {
				return false;
			}

			$resp = $result->result('array');

			$uslData = array();
			$uslFields = array();

			if ( is_array($resp) && count($resp) > 0 ) {
				foreach ( $resp[0] as $key => $value ) {
					$newKey = 'U_' . $key;

					if ( strlen($newKey) > 10 ) {
						$newKey = substr($newKey, 0, 10);
					}

					$uslFields[$key] = $newKey;
				}

				foreach ( $resp as $usluga ) {
					$uslData[$usluga['MaxEvn_id']][] = $usluga;
				}
			}
			//var_dump($uslData);

			$responseRowFields = array_merge($persFields, $vizitFields, $uslFields);
			//var_dump($responseRowFields);

			// Склеиваем все в кучку
			foreach ( $vizitData as $key => $vizit ) {
				$row = array();

				foreach ( $responseRowFields as $respKey => $value ) {
					$row[$value] = null;
				}

				if ( in_array($vizit['IDCASE'], array_keys($persData)) ) {
					foreach ( $persData[$vizit['IDCASE']] as $persKey => $value ) {
						if ( isset($responseRowFields[$persKey]) && array_key_exists($responseRowFields[$persKey], $row) ) {
							$row[$responseRowFields[$persKey]] = $value;
						}
					}
				}

				foreach ( $vizit as $vizitKey => $value ) {
					if ( isset($responseRowFields[$vizitKey]) && array_key_exists($responseRowFields[$vizitKey], $row) ) {
						$row[$responseRowFields[$vizitKey]] = $value;
					}
				}

				if ( array_key_exists($vizit['IDCASE'], $uslData) ) {
					foreach ( $uslData[$vizit['IDCASE']] as $usluga ) {
						$tmpRow = $row;

						foreach ( $usluga as $uslugaKey => $value ) {
							if ( isset($responseRowFields[$uslugaKey]) && array_key_exists($responseRowFields[$uslugaKey], $row) ) {
								$tmpRow[$responseRowFields[$uslugaKey]] = $value;
							}
						}

						$response['data'][] = $tmpRow;
					}
				}
				else {
					$response['data'][] = $row;
				}
			}
		}

		return $response;
	}

	/**
	 * Функция возвращает список допустимых типов реестров
	 */
	function getAllowedRegistryTypes($data = array()) {
		$result = array();

		$registryTypeData = $this->loadRegistryTypeNode($data);

		if ( is_array($registryTypeData) && count($registryTypeData) > 0 ) {
			foreach ( $registryTypeData as $array ) {
				if ( is_array($array) && count($array) > 0 && !empty($array['RegistryType_id']) && !in_array($array['RegistryType_id'], $result) ) {
					$result[] = $array['RegistryType_id'];
				}
			}
		}

		return $result;
	}

	/**
	 * Функция возвращает список допустимых статусов реестров
	 */
	public function getAllowedRegistryStatuses($data = array()) {
		$result = array();

		$registryStatusData = $this->loadRegistryStatusNode($data);

		if ( is_array($registryStatusData) && count($registryStatusData) > 0 ) {
			foreach ( $registryStatusData as $array ) {
				if ( is_array($array) && count($array) > 0 && !empty($array['RegistryStatus_id']) && !in_array($array['RegistryStatus_id'], $result) ) {
					$result[] = $array['RegistryStatus_id'];
				}
			}
		}

		return $result;
	}

	/**
	 * Запрос для проверки наличия данных для вкладки "Дублеи посещений"
	 */
	function getRegistryDoubleCheckQuery($scheme = 'dbo') {
		return "
			select top 1 Evn_id from {$scheme}.v_RegistryDouble with(nolock) where Registry_id = R.Registry_id
		";
	}

	/**
	 * Получение дополнительных данных для печати счета
	 */
	function getAdditionalPrintInfo(&$data) {
		return true;
	}

	/**
	 * Проверка статуса реестра
	 * Возващает true, если статус "Заблокирован"
	 * @task https://redmine.swan.perm.ru/issues/70754
	 */
	function checkRegistryIsBlocked($data) {
		return false;
	}

	/**
	 * Проверка на дубли при добавлении реестра в очередь
	 * @task https://redmine.swan.perm.ru/issues/76752
	 */
	function checkRegistryQueueDoubles($data) {
		return '';
	}

	/**
	 * Проверка наличия посещения в реестре (нельзя убирать в очередь такие ТАП)
	 */
	function getRegistryIdForEvnVizit($data) {
		
		if ($this->usePostgreRegistry) {
			$dbreg = $this->load->database('postgres', true);
		} else {
			$dbreg = $this->load->database('registry', true);
		}
		
		$query = "
			select top 1
				Reg.Registry_id
			from
				{$this->scheme}.v_RegistryData RD with(nolock)
				inner join {$this->scheme}.v_Registry Reg with(nolock) on RD.Registry_id = Reg.Registry_id
			where
				RD.Evn_id = :Evn_id
				and (Reg.RegistryStatus_id!=4 or (Reg.RegistryStatus_id=4 and RD.{$this->getIsPaidField()} = 2))
		";
		$result = $dbreg->query($query, array(
			'Evn_id' => $data['Evn_id']
		));
		if (is_object($result)) {
			$rresp = $result->result('array');
			if (!empty($rresp[0]['Registry_id'])) {
				return $rresp[0]['Registry_id'];
			}
		} else {
			throw new Exception('Ошибка проверки оплаченности посещений.', 400);
		}

		return null;
	}

	/**
	 * Действия, выполняемые перед удалением реестра из очереди
	 */
	public function beforeDeleteRegistryQueue($RegistryQueue_id = null) {
		return true;
	}

	/**
	 * Действия, выполняемые перед удалением реестра из очереди
	 */
	public function deleteRegistryGroupLink($data) {
		$query = "
			declare @datetime datetime = dbo.tzGetDate();
			
			update
				{$this->scheme}.RegistryGroupLink with (ROWLOCK)
			set
				pmUser_delID = :pmUser_id,
				RegistryGroupLink_delDT = @datetime,
				RegistryGroupLink_Deleted = 2
			where Registry_pid = :Registry_pid
		";
		$this->db->query($query, array(
			'Registry_pid' => $data['Registry_pid'],
			'pmUser_id' => $data['pmUser_id']
		));

		return true;
	}
	
	/**
	 * Возвращает список настроек ФЛК
	 */
	function loadRegistryEntiesGrid($data)
	{
		$where=' WHERE (1=1) ';
		$region = getRegionNick();
		
		/*
		$registryIsNotGroupType = array('perm','ufa','khak','astra');
		$registryIsNotType = array('kareliya','buryatiya','penza');
		$regionRegistryGroupTypeWhere = array(
			'kaluga' => ' AND FLKS.RegistryGroupType_id <> 2 and RegistryGroupType_id <> 11',
			'ekb' => ' AND FLKS.RegistryGroupType_id in (12, 13, 14)',
			'buryatiya' => ' AND FLKS.RegistryGroupType_id in (1, 2, 11)',
			'kareliya' => ' AND FLKS.RegistryGroupType_id > 0',
			'krym' => ' AND FLKS.RegistryGroupType_id in (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 16, 17)',
			'penza' => ' AND FLKS.RegistryGroupType_id >= 1 and RegistryGroupType_id <= 10',
			'pskov' => ' AND FLKS.RegistryGroupType_id < 11'
		);
		$regionRegistryTypeWhere = array(
			'kaluga' => ' AND FLKS.RegistryType_id in (1,2)',
			'ekb' => ' AND FLKS.RegistryType_id in (1,2,6,7,9,11,12,15)',
			'buryatiya' => '  AND FLKS.RegistryType_id in (1,2,6,7,9,11,12,14)',
			'kareliya' =>  '  AND FLKS.RegistryType_id in (1,2,6,7,9,11,12,15)',
			'krym' => ' AND FLKS.RegistryType_id in (2,6)',
			'penza' => ' AND FLKS.RegistryType_id in (1,2,7)',
			'pskov' => ' AND FLKS.RegistryType_id in (1,2,6,7,9,11,12,14,15)',
			'perm' => ' AND FLKS.RegistryType_id in (1,2,6,7,9,11,12,13,14,15,16)',
			'ufa' =>  ' AND FLKS.RegistryType_id in (1,2,6,7,9,14)',
			'hakasiya' => ' AND FLKS.RegistryType_id in (1,2)',
			'astra' => ' AND FLKS.RegistryType_id in (1,2,6,7,9,11,12,14)',
		);
		*/
		$regionWhere =  array(
			'kaluga' => ' AND ( ( FLKS.RegistryGroupType_id <> 2 and RegistryGroupType_id <> 11 ) OR FLKS.RegistryType_id in (1,2) )',
			'ekb' => ' AND ( FLKS.RegistryGroupType_id in (12, 13, 14) OR FLKS.RegistryType_id in (1,2,6,7,9,11,12,15) )',
			'buryatiya' => ' AND ( FLKS.RegistryGroupType_id in (1, 2, 11) OR FLKS.RegistryType_id in (1,2,6,7,9,11,12,14) )',
			'kareliya' =>  '  AND FLKS.RegistryType_id in (1,2,6,7,9,11,12,15)',
			'krym' => ' AND ( FLKS.RegistryGroupType_id in (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 16, 17) OR FLKS.RegistryType_id in (2,6) )',
			'penza' => ' AND ( (FLKS.RegistryGroupType_id >= 1 and RegistryGroupType_id <= 10) OR FLKS.RegistryType_id in (1,2,7) )',
			'pskov' => ' AND ( FLKS.RegistryGroupType_id < 11 OR FLKS.RegistryType_id in (1,2,6,7,9,11,12,14,15) )',
			'perm' => ' AND FLKS.RegistryType_id in (1,2,6,7,9,11,12,13,14,15,16) AND FLKS.RegistryGroupType_id is not null',
			'ufa' =>  ' AND FLKS.RegistryType_id in (1,2,6,7,9,14) AND FLKS.RegistryGroupType_id is not null',
			'hakasiya' => ' AND FLKS.RegistryType_id in (1,2) AND FLKS.RegistryGroupType_id is not null',
			'astra' => ' AND FLKS.RegistryType_id in (1,2,6,7,9,11,12,14) AND FLKS.RegistryGroupType_id is not null',
		);
		
		$where .= " AND FLKS.FLKSettings_EvnData LIKE '%".$region."%'";
		$params = array();
		$query = "
			select
				FLKS.FLKSettings_id,
				FLKS.RegistryType_id,
				FLKS.RegistryGroupType_id,
				RT.RegistryType_Name,
				RGT.RegistryGroupType_Name,
				FLKS.FLKSettings_EvnData,
				FLKS.FLKSettings_PersonData,
				convert(varchar(10), FLKS.FLKSettings_begDate, 104) as FLKSettings_begDate,
				convert(varchar(10), FLKS.FLKSettings_endDate, 104) as FLKSettings_endDate
			from
				v_FLKSettings FLKS with(nolock)
				left join v_RegistryType RT with(nolock) on FLKS.RegistryType_id = RT.RegistryType_id
				left join v_RegistryGroupType RGT with(nolock) on FLKS.RegistryGroupType_id = RGT.RegistryGroupType_id
		".$where;

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response['data'] = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}	
	
	/**
	 * Возвращает данные для редактирования значения настройки ФЛК
	 */
	function loadRegistryEntiesForm($data)
	{
		if( empty($data['FLKSettings_id']) ) return false;
		$params = array('FLKSettings_id' => $data['FLKSettings_id']);

		$query = "
			select
				FLKS.FLKSettings_id,
				FLKS.RegistryType_id,
				FLKS.RegistryGroupType_id,
				RT.RegistryType_Name,
				RGT.RegistryGroupType_Name,
				FLKS.FLKSettings_EvnData,
				FLKS.FLKSettings_PersonData,
				convert(varchar(10), FLKS.FLKSettings_begDate, 104) as FLKSettings_begDate,
				convert(varchar(10), FLKS.FLKSettings_endDate, 104) as FLKSettings_endDate
			from
				v_FLKSettings FLKS with(nolock)
				left join v_RegistryType RT with(nolock) on FLKS.RegistryType_id = RT.RegistryType_id
				left join v_RegistryGroupType RGT with(nolock) on FLKS.RegistryGroupType_id = RGT.RegistryGroupType_id
			where FLKS.FLKSettings_id = :FLKSettings_id
		";

		$result = $this->db->query($query, $params);

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
	 * сохранение регистров настройки ФЛК
	 */
	function saveRegistryEntries($data) {
		$putEvnData=false; $putPersonData=false;
		$resultSaveFiles=array(
			'RegistryFileCase' => false, 
			'RegistryFilePersonalData' => false, 
			'success' => true
		);
		$periodTime=$this->intersectionOfActionPeriodsFLK($data);
		if($periodTime['count']>0){
			// если есть пересечения периодов действия записей относящихся к одному типу реестра/объединенного реестра
			return array(
				'success' => false,
				'Error_Msg' => 'Найдены пересечения периодов действия записей относящихся к одному типу. Сохранение отменено ' 
			);
		}
		
		$RegistryFilePersonalData = is_uploaded_file($_FILES['RegistryFilePersonalData']['tmp_name']);
		$RegistryFileCase = is_uploaded_file($_FILES['RegistryFileCase']['tmp_name']);
		$timePut = time();
		$filename = getRegionNick().uniqid();
		if( $data['FLKSettings_id'] ){
			// если редактируем запись
			$listFLK = $this->loadRegistryEntiesForm($data);
			if( $RegistryFileCase ){
				$resultSaveFiles['RegistryFileCase'] = $this->uploadFile('RegistryFileCase', $timePut, $filename.'c');
				
				$file = IMPORTPATH_ROOT.$this->upload_path.$listFLK[0]['FLKSettings_EvnData'];
				if($resultSaveFiles['RegistryFileCase']['success'] && $listFLK[0]['FLKSettings_EvnData'] && file_exists( $file )){
					//удалим старый файл
					@unlink($file);
				}
				$resultSaveFiles['success'] = $resultSaveFiles['RegistryFileCase']['success'];
				$putEvnData = ($resultSaveFiles['success']) ? $resultSaveFiles['RegistryFileCase']['name'] : false;
			}else{
				$putEvnData = $listFLK[0]['FLKSettings_EvnData'];
			}
			if( $RegistryFilePersonalData ){
				$resultSaveFiles['RegistryFilePersonalData'] = $this->uploadFile('RegistryFilePersonalData', $timePut, $filename.'p');
				//$n=file_exists( IMPORTPATH_ROOT.$this->upload_path.$listFLK[0]['FLKSettings_PersonData'] );
				$file = IMPORTPATH_ROOT.$this->upload_path.$listFLK[0]['FLKSettings_PersonData'];
				if($resultSaveFiles['RegistryFilePersonalData']['success'] && $listFLK[0]['FLKSettings_PersonData'] && file_exists( $file )){
					//удалим старый файл
					@unlink($file);
				}
				$resultSaveFiles['success'] = $resultSaveFiles['RegistryFilePersonalData']['success'];
				$putPersonData = ($resultSaveFiles['RegistryFilePersonalData']['success']) ? $resultSaveFiles['RegistryFilePersonalData']['name'] : false;
			}else{
				$putPersonData = $listFLK[0]['FLKSettings_PersonData'];
			}
		}elseif($RegistryFilePersonalData || $RegistryFileCase){
			//добавление новой записи
			$resultSaveFiles['RegistryFileCase'] = $this->uploadFile('RegistryFileCase', $timePut, $filename.'c');
			$putEvnData = $resultSaveFiles['RegistryFileCase']['name'];
			$resultSaveFiles['RegistryFilePersonalData'] = $this->uploadFile('RegistryFilePersonalData', $timePut, $filename.'p');
			if($resultSaveFiles['RegistryFilePersonalData']['success'] && $resultSaveFiles['RegistryFileCase']['success']){
				$putEvnData = $resultSaveFiles['RegistryFileCase']['name'];
				$putPersonData = $resultSaveFiles['RegistryFilePersonalData']['name'];
				$resultSaveFiles['success'] = true;
			}else{
				$resultSaveFiles['success'] = false;
			}
		}
		
		if($resultSaveFiles['success'] == false || !$putEvnData || !$putPersonData){
			return array(
				'success' => false,
				'resultSaveFiles' => $resultSaveFiles,
				'Error_Msg' => 'Произошла ошибка при сохранении файла. Сохранение отменено ' 
			);
		}
		
		$params = array(
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryGroupType_id' => $data['RegistryGroupType_id'],
			'FLKSettings_EvnData' => $putEvnData,
			'FLKSettings_PersonData' => $putPersonData,
			'FLKSettings_endDate' => $data['FLKSettings_endDate'],
			'FLKSettings_begDate' => $data['FLKSettings_begDate'],
			'FLKSettings_id' => ($data['FLKSettings_id']) ? $data['FLKSettings_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($params['FLKSettings_id']) && $params['FLKSettings_id'] > 0) {
			$procedure = 'p_FLKSettings_upd';
		} else {
			$params['FLKSettings_id'] = null;
			$procedure = 'p_FLKSettings_ins';
		}
		
		$query = "	
			declare
				@Res bigint,
				@curdate datetime = dbo.tzGetDate(),
				@FLKSettings_id bigint = null,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @Res = :FLKSettings_id;
			exec {$procedure}
				@FLKSettings_id = @Res output,
				@RegistryType_id = :RegistryType_id,
				@RegistryGroupType_id = :RegistryGroupType_id,
				@FLKSettings_EvnData = :FLKSettings_EvnData,
				@FLKSettings_PersonData = :FLKSettings_PersonData,
				@FLKSettings_begDate = :FLKSettings_begDate,
				@FLKSettings_endDate = :FLKSettings_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Res as FLKSettings_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при сохранении записи ФЛК'));
		}
		$response = $result->result('array');

		return $response;
	}
	
	/**
	 * Проверяет существование пересечений времени настройк ФЛК
	 */
	function intersectionOfActionPeriodsFLK($data){
		$where=' WHERE ';
		$region = getRegionNick();
		$periodTime = '';
		$dataBeg = $data['FLKSettings_begDate'];
		$dataEnd = ($data['FLKSettings_endDate']) ? $data['FLKSettings_endDate'] : '2030-01-01';
		
		if($data['RegistryType_id']){
			$where = " WHERE FLKS.RegistryType_id = ".$data['RegistryType_id'];
		}elseif ($data['RegistryGroupType_id']) {
			$where = " WHERE FLKS.RegistryGroupType_id = ".$data['RegistryGroupType_id'];
		}
		$periodTime = " '".$dataBeg."' <=
				case when FLKSettings_endDate is null
					then '2030-01-01'
					else FLKSettings_endDate
				end
				AND
				'".$dataEnd."' >= FLKSettings_begDate";
		$where = $where.' AND ( '.$periodTime.')';
		if($data['FLKSettings_id']){
			//если редактирование записи, то исключим из поиска саму запись
			$where .= ' AND FLKS.FLKSettings_id <> '.$data['FLKSettings_id'];
		}
		$where .= " AND FLKS.FLKSettings_EvnData LIKE '%".$region."%'";
		$params = array();
		$query = "
			select
				COUNT(*) as count
			from
				v_FLKSettings FLKS with(nolock)
			".$where;

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response = $result->result('array');
			return $response[0];
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * загрузка файла, шаблона xsd для ФЛК проверки
	 */
	function uploadFile($fil, $put=false, $nam=false){
		$name = $fil;
		$expansion='xsd';
		//$upload_path = IMPORTPATH_ROOT.'RgistryFields/';
		$timePut = ($put) ? $put : time();
		$filename = ($nam) ? $nam : uniqid();
		$upload_path = IMPORTPATH_ROOT.$this->upload_path.$timePut.'/';

		if (!isset($_FILES[$name])) {
			return array('success' => false, 'Error_Code' => '' , 'Error_Msg' => toUTF('Файл не существует'));
		}
		
		$path_info = pathinfo($_FILES[$name]['name']);
		if($path_info['extension'] != $expansion='xsd'){
			return array('success' => false, 'Error_Code' => '' , 'Error_Msg' => toUTF('Неверный формат файла'));
		}
		
		if (!is_uploaded_file($_FILES[$name]['tmp_name']))
		{
			$error = (!isset($_FILES[$name]['error'])) ? 4 : $_FILES[$name]['error'];
			switch($error)
			{
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}
			return array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message));
		}

		// Правильно ли указана директория для загрузки?
		$path = '';
		$folders = explode('/', $upload_path);
		for($i=0; $i<count($folders); $i++) {
			if ($folders[$i] == '') {continue;}
			$path .= $folders[$i].'/';
			if (!@is_dir($path)) {
				mkdir( $path );
			}
		}
		if (!@is_dir($upload_path)) {
			return array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.'));
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			return array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.'));
		}

		$file_name = $timePut.'_'.$filename;
		$file_name = substr($file_name, 0, 46).'.'.$expansion;

		if ( move_uploaded_file ($_FILES[$name]['tmp_name'], $upload_path.$file_name)) {
			$val['DocNormative_File'] = $upload_path.$file_name;
			$val['Error_Code'] = '';
			$val['Error_Msg'] = '';
			$val['name'] = $file_name;
			$val['success'] = true;
			return $val;
		} else {
			return array('success' => false, 'Error_Code' => 706 , 'Error_Msg' => toUTF('Невозможно скопировать файл в указанное место после его загрузки.') );
		}
	}
	
	/**
	 * удаляет загруженные файлы из тбл Настройка ФЛК
	 * @return bool
	 */
	function deleteRegistryFLKFiles($data){
		$listFLK = $data;
		$dir = false;
		if($listFLK[0]['FLKSettings_EvnData']){
			// имя файла типа ДИРЕКТОРИЯ_ИМЯ.XSD
			$dirr = explode("_", $listFLK[0]['FLKSettings_EvnData']);
			$dir = IMPORTPATH_ROOT.$this->upload_path.$dirr[0];
		}elseif($listFLK[0]['FLKSettings_PersonData']){
			// имя файла типа ДИРЕКТОРИЯ_ИМЯ.XSD
			$dirr = explode("_", $listFLK[0]['FLKSettings_PersonData']);
			$dir = IMPORTPATH_ROOT.$this->upload_path.$dirr[0];
		}
		
		if (is_dir($dir)) { 
			$files = array_diff(scandir($dir), array('.','..'));
			foreach ($files as $file) {
				if( is_dir("$dir/$file") ){
					delTree("$dir/$file");
				}else{
					@unlink("$dir/$file");
				}
			}

			rmdir($dir);
		}
		return true; 
	}
	
	/**
	 * удаляет запись из таблицы Настройка ФЛК 
	 * @return bool
	 */
	function deleteRegistryFLKSettings($data){
		if(empty($data['FLKSettings_id'])) return false;

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_FLKSettings_del
				@FLKSettings_id = :FLKSettings_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'FLKSettings_id' => $data['FLKSettings_id']
			)
		);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * удаляет запись из таблицы Настройка ФЛК и загруженные файлы
	 * @return bool
	 */
	function deleteRegistryFLK($data){
		if(empty($data['FLKSettings_id'])) return false;

		$listFLK = $this->loadRegistryEntiesForm($data);
		
		$res = $this->deleteRegistryFLKSettings($data);
		if($res){
			if( count($listFLK) > 0 ){
				$resFiles = $this->deleteRegistryFLKFiles($listFLK);
			}
			return $res;
		}else{
			return false;
		}
		
	}
	
	/**
	 * Возвращает список настроек ФЛК
	 */
	public function loadRegistryEntiesSettings($data)
	{
		if(!$data['RegistryType_id'] && !$data['RegistryGroupType_id']) return false;
		
		if( isset($data['RegistryGroupType_id']) ){
			$where = ' AND RegistryGroupType_id = '.$data['RegistryGroupType_id'];
		}else{
			$where = ' AND RegistryType_id = '.$data['RegistryType_id'];
		}
		
		$params = array('region' => $this->regionNick);
		$query = "
			SELECT top 1
				FLKSettings_id
				,RegistryType_id
				,FLKSettings_EvnData
				,FLKSettings_PersonData
			FROM v_FLKSettings with (nolock)
			WHERE 
				getdate() between FLKSettings_begDate and 
					case when FLKSettings_endDate is null
						then '2030-01-01'
						else FLKSettings_endDate
					end
				and FLKSettings_EvnData like '%[_]' + :region + '%'
		".$where;
		$result = $this->db->query($query, $params);
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
	 *  ФЛК контроль 
	 */
	function Reconciliation($xml_data, $xsd_tpl, $type = "string", $output_file_name = 'err_xml.html')
    {
		if( !file_exists($xsd_tpl) || !$xml_data) return false;

		libxml_use_internal_errors(true);  
		$xml = new DOMDocument();
	
		if($type == 'file'){
			$xml->load($xml_data); 
		}
		elseif($type == 'string'){
			$xml->loadXML($xml_data);   
		}
	
		if (!@$xml->schemaValidate($xsd_tpl)) {
			ob_start();
			$this->libxml_display_errors();
			$res_errors = ob_get_contents();
			ob_end_clean();
	
			file_put_contents($output_file_name, $res_errors);
			return false;
		}
		else{
			return true;
		}
	}
	
	/**
	* ФЛК контроль
	* Метод для формирования листа ошибок при сверке xml по шаблону xsd
	* @return (string)
	*/
	function libxml_display_errors() 
	{
		$errors = libxml_get_errors();		
		foreach ($errors as $error) 
		{
			$return = "<br/>\n";	
			switch($error->level) 
			{
				case LIBXML_ERR_WARNING:
					$return .= "<b>Warning $error->code</b>: ";
					break;
				case LIBXML_ERR_ERROR:
					$return .= "<b>Error $error->code</b>: ";
					break;
				case LIBXML_ERR_FATAL:
					$return .= "<b>Fatal Error $error->code</b>: ";
					break;
			} 
	
			$return .= trim($error->message);
			if($error->file) 
			{
				$return .=    " in <b>$error->file</b>";
			}
	
			$return .= " on line <b>$error->line</b>\n";
			print $return;        
		}	
		libxml_clear_errors();
	}

	/**
	 * Возвращает имя поля, отвечающего за признак оплаты случая
	 */
	protected function getIsPaidField() {
		return (in_array($this->regionNick, array('pskov', 'ufa', 'vologda')) ? 'Paid_id' : 'RegistryData_IsPaid');
	}

	/**
	 * Функция фиксации состояния реестра в БД на момент выполнения события (экспорт, импорт)
	 * @task https://redmine.swan.perm.ru/issues/128914
	 */
	public function dumpRegistryInformation($data, $RegistryCondition_id = null) {
		if ( empty($RegistryCondition_id) || empty($data['Registry_id']) ) {
			return false;
		}

		// Тянем список полей из <схема>.Registry
		// Список полей можно кэшировать и обновлять с некоторой периодичностью
		$rsp = $this->queryResult("
			select 
				c.name
			from
				sys.columns c (nolock)
				inner join sys.objects o on o.object_id = c.object_id
				inner join sys.schemas s on s.schema_id = o.schema_id
			where
				o.name = :table_name
				and o.type = 'U'
				and s.name = :schema
		", array(
			'table_name' => 'Registry',
			'schema' => $this->scheme,
		));

		if ( $rsp === false || !is_array($rsp) || count($rsp) == 0 ) {
			return false;
		}

		$fields = array();

		foreach ( $rsp as $row ) {
			if(strtolower($row['name']) == 'registry_rowversion'){
				continue; // Поле присутвует в Registry на некотороых регионах, но отсутвует в dbo.RegistryHistory
			} 
			$fields[] = $row['name'];
		}

		// Выгребаем данные по Registry_id
		// Нужно для update данных в RegistryCache
		$registryData = $this->getFirstRowFromQuery("
			select " . implode(', ', $fields) . " from {$this->scheme}.Registry with (nolock) where Registry_id = :Registry_id
		", $data);

		if ( $registryData === false || !is_array($registryData) || count($registryData) == 0 ) {
			return false;
		}

		// Приводим даты к строковому виду
		foreach ( $registryData as $key => $value ) {
			if ( $value instanceof DateTime ) {
				$registryData[$key] = mb_substr($value->format('Y-m-d H:i:s.u'), 0, 23); // то же самое, что и $value->format('Y-m-d H:i:s.v') в PHP 7 и новее.
			}
		}

		// Добавляем данные в dbo.RegistryHistory
		$rsp = $this->getFirstRowFromQuery("
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';

			set nocount on;

			begin try
				insert into dbo.RegistryHistory (" . implode(', ', $fields) . ", RegistryCondition_id, RegistryHistory_insDT, RegistryHistory_updDT)
				select top 1 " . implode(', ', $fields) . ", :RegistryCondition_id, dbo.tzGetdate(), dbo.tzGetdate()
				from {$this->scheme}.Registry with (nolock)
				where Registry_id = :Registry_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch

			set nocount off;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", array(
			'Registry_id' => $data['Registry_id'],
			'RegistryCondition_id' => $RegistryCondition_id,
		));

		if ( $rsp === false || !is_array($rsp) || count($rsp) == 0 ) {
			return false;
		}
		else if ( !empty($rsp['Error_Msg']) ) {
			return $rsp;
		}

		$additionalUpdatePairs = array();
		$updatePairs = array();

		foreach ( $fields as $name ) {
			$updatePairs[] = $name . " = :" . $name;
		}

		switch ( $RegistryCondition_id ) {
			case 1:
				$additionalUpdatePairs[] = "RegistryCache_GenCount = ISNULL(RegistryCache_GenCount, 0) + 1";
				break;

			case 2:
				$additionalUpdatePairs[] = "RegistryCache_ExpCount = ISNULL(RegistryCache_ExpCount, 0) + 1";
				break;

			case 3:
				$additionalUpdatePairs[] = "RegistryCache_ResponseCount = ISNULL(RegistryCache_ResponseCount, 0) + 1";
				break;
		}

		$updatePairs = array_merge($updatePairs, $additionalUpdatePairs);

		// Добавляем/обновляем данные в dbo.RegistryCache
		$rsp = $this->getFirstRowFromQuery("
			declare
				@RegistryCache_id bigint,
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';

			set nocount on;

			begin try
				set @RegistryCache_id = (select top 1 RegistryCache_id from dbo.RegistryCache with (nolock) where Registry_id = :Registry_id);

				if ( @RegistryCache_id is null )
					begin
						insert into dbo.RegistryCache (" . implode(', ', $fields) . ")
						select top 1 " . implode(', ', $fields) . "
						from {$this->scheme}.Registry with (nolock)
						where Registry_id = :Registry_id

						set @RegistryCache_id = (select scope_identity());

						" . (count($additionalUpdatePairs) > 0 ? "
						update dbo.RegistryCache with (rowlock)
						set " . implode(",", $additionalUpdatePairs) . "
						where RegistryCache_id = @RegistryCache_id" : "") . "
					end
				else
					update dbo.RegistryCache with (rowlock)
					set " . implode(",", $updatePairs) . "
					where RegistryCache_id = @RegistryCache_id
					
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch

			set nocount off;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", $registryData);

		return true;
	}

	/**
	 * Загрузка списка реестров для формы работы с историей реестров
	 */
	public function loadRegistryCacheList($data) {
		$filterList = array('(1 = 1)');
		$joinList = array();
		$queryParams = array();
		$variables = '';

		if ( !empty($data['Lpu_id']) ) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filterList[] = "rc.Lpu_id = :Lpu_id";
		}

		if ( !empty($data['begDT']) ) {
			$queryParams['begDT'] = $data['begDT'];
			$filterList[] = "rc.Registry_begDate >= :begDT";
		}

		if ( !empty($data['endDT']) ) {
			$queryParams['endDT'] = $data['endDT'];
			$filterList[] = "rc.Registry_endDate <= :endDT";
		}

		$query = (!empty($variables) ? "
			-- variables
			{$variables}
			-- end variables

		" : "") . "
			select
				-- select
				 rc.RegistryCache_id
				,case when r.Registry_id is null then 1 else 0 end as Registry_deleted
				,rc.Registry_id
				,l.Lpu_Nick as Lpu_Name
				,rt.RegistryType_Name
				,rs.RegistryStatus_Name
				,rc.Registry_Num + ' от ' + convert(varchar(10), rc.Registry_accDate, 104) as Registry_accDateNum
				,rc.Registry_Num
				,convert(varchar(10), rc.Registry_accDate, 104) as Registry_accDate
				,convert(varchar(10), rc.Registry_begDate, 104) as Registry_begDate
				,convert(varchar(10), rc.Registry_endDate, 104) as Registry_endDate
				,rcon.RegistryCondition_Name
				,ISNULL(rc.Registry_RecordCount, 0) as Registry_Count
				,ISNULL(rc.Registry_Sum, 0.00) as Registry_Sum
				,ISNULL(rc.Registry_RecordPaidCount, 0) as Registry_RecordPaidCount
				,ISNULL(rc.Registry_SumPaid, 0.00) as Registry_SumPaid
				,case when ISNULL(rc.Registry_Sum, 0) > 0 then rc.Registry_SumPaid / rc.Registry_Sum * 100 else null end as Registry_SumPaidPercent
				,rc.RegistryCache_GenCount
				,rc.RegistryCache_ExpCount
				,rc.RegistryCache_ResponseCount
				,convert(varchar(19), rc.Registry_updDT, 120) as Registry_updDT
				,convert(varchar(19), rh.RegistryHistory_insDT, 120) as RegistryHistory_insDT
				-- end select
			from
				-- from
				v_RegistryCache rc with (nolock)
				left join v_Lpu l with (nolock) on l.Lpu_id = rc.Lpu_id
				left join v_RegistryType rt with (nolock) on rt.RegistryType_id = rc.RegistryType_id
				left join v_RegistryStatus rs with (nolock) on rs.RegistryStatus_id = rc.RegistryStatus_id
				outer apply (
					select top 1 RegistryCondition_id, RegistryHistory_insDT
					from v_RegistryHistory with (nolock)
					where Registry_id = rc.Registry_id
					order by RegistryHistory_id desc
				) rh
				outer apply (
					select top 1 Registry_id
					from {$this->scheme}.v_Registry with (nolock)
					where Registry_id = rc.Registry_id
				) r
				left join v_RegistryCondition rcon with (nolock) on rcon.RegistryCondition_id = rh.RegistryCondition_id
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				rc.RegistryCache_id desc
				-- end order by
		";

		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true, true);

		return $response;
	}

	/**
	 * Загрузка списка событий реестра для формы работы с историей реестров
	 */
	public function loadRegistryHistoryList($data) {
		$filterList = array('rh.Registry_id = :Registry_id');
		$queryParams = array('Registry_id' => $data['Registry_id']);

		$query = "
			select
				-- select
				 rh.RegistryHistory_id
				,rh.Registry_id
				,rcon.RegistryCondition_Name
				,ISNULL(rh.Registry_RecordCount, 0) as Registry_Count
				,ISNULL(rh.Registry_Sum, 0.00) as Registry_Sum
				,ISNULL(rh.Registry_ErrorCount, 0) as Registry_ErrorCount
				,ISNULL(rh.Registry_NoErrSum, 0.00) as Registry_ErrSum
				,convert(varchar(19), rh.RegistryHistory_insDT, 120) as RegistryHistory_insDT
				-- end select
			from
				-- from
				v_RegistryHistory rh with (nolock)
				left join v_RegistryCondition rcon with (nolock) on rcon.RegistryCondition_id = rh.RegistryCondition_id
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				rh.RegistryHistory_id desc
				-- end order by
		";

		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true, true);

		return $response;
	}

	/**
	 * Загрузка списка ошибок МЗ
	 */
	function loadRegistryHealDepErrorTypeGrid($data) {
		$filterList = array(
			"1=1"
		);
		$queryParams = array();

		switch($data['filterRecords']) {
			case 2:
				$filterList[] = "ISNULL(rhdet.RegistryHealDepErrorType_endDate, @curDate) >= @curDate";
				break;
			case 3:
				$filterList[] = "rhdet.RegistryHealDepErrorType_endDate < @curDate";
				break;
		}

		if (!empty($data['RegistryHealDepErrorType_Code'])) {
			$filterList[] = "rhdet.RegistryHealDepErrorType_Code = :RegistryHealDepErrorType_Code";
			$queryParams['RegistryHealDepErrorType_Code'] = $data['RegistryHealDepErrorType_Code'];
		}

		$query = "
			-- variables 
			declare @curDate date = dbo.tzGetDate();
			-- end variables
			
			select
				-- select
				rhdet.RegistryHealDepErrorType_id,
				rhdet.RegistryHealDepErrorType_Code,
				rhdet.RegistryHealDepErrorType_Name,
				rhdet.RegistryHealDepErrorType_Descr,
				convert(varchar(10), rhdet.RegistryHealDepErrorType_begDate, 104) as RegistryHealDepErrorType_begDate,
				convert(varchar(10), rhdet.RegistryHealDepErrorType_endDate, 104) as RegistryHealDepErrorType_endDate
				-- end select
			from
				-- from
				v_RegistryHealDepErrorType rhdet with (nolock)
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				rhdet.RegistryHealDepErrorType_id
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true, true);
	}

	/**
	 * Удаление ошибки МЗ
	 */
	function deleteRegistryHealDepErrorType($data) {
		$query = "	
			declare
				@Error_Code int,
				@Error_Message varchar(4000);
			exec p_RegistryHealDepErrorType_del
				@RegistryHealDepErrorType_id = :id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Сохранение ошибки МЗ
	 */
	function saveRegistryHealDepErrorType($data) {
		// проверка дублей
		$resp_check = $this->queryResult("
			select
				RegistryHealDepErrorType_id
			from
				v_RegistryHealDepErrorType (nolock)
			where
				RegistryHealDepErrorType_Code = :RegistryHealDepErrorType_Code
		", array(
			'RegistryHealDepErrorType_Code' => $data['RegistryHealDepErrorType_Code']
		));

		if (!empty($resp_check[0]['RegistryHealDepErrorType_id'])) {
			return array('Error_Msg' => 'Ошибка с таким кодом уже существует');
		}

		if (!empty($data['RegistryHealDepErrorType_id'])) {
			$procedure = 'p_RegistryHealDepErrorType_upd';
		} else {
			$procedure = 'p_RegistryHealDepErrorType_ins';
			$data['RegistryHealDepErrorType_id'] = null;
		}

		$data['Region_id'] = getRegionNumber();

		$query = "	
			declare
				@RegistryHealDepErrorType_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @RegistryHealDepErrorType_id = :RegistryHealDepErrorType_id;
			exec {$procedure}
				@RegistryHealDepErrorType_id = @RegistryHealDepErrorType_id output,
				@RegistryHealDepErrorType_Code = :RegistryHealDepErrorType_Code,
				@RegistryHealDepErrorType_Name = :RegistryHealDepErrorType_Name,
				@RegistryHealDepErrorType_Descr  = :RegistryHealDepErrorType_Descr,
				@RegistryHealDepErrorType_begDate = :RegistryHealDepErrorType_begDate,
				@RegistryHealDepErrorType_endDate = :RegistryHealDepErrorType_endDate,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @RegistryHealDepErrorType_id as RegistryHealDepErrorType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Загрузка формы редактирования ошибки МЗ
	 */
	function loadRegistryHealDepErrorTypeEditWindow($data) {
		return $this->queryResult("
			select
				rhdet.RegistryHealDepErrorType_id,
				rhdet.RegistryHealDepErrorType_Code,
				rhdet.RegistryHealDepErrorType_Name,
				rhdet.RegistryHealDepErrorType_Descr,
				convert(varchar(10), rhdet.RegistryHealDepErrorType_begDate, 104) as RegistryHealDepErrorType_begDate,
				convert(varchar(10), rhdet.RegistryHealDepErrorType_endDate, 104) as RegistryHealDepErrorType_endDate,
				case when rhdre.RegistryHealDepResErr_id is not null then 2 else 1 end as IsUsed
			from
				v_RegistryHealDepErrorType rhdet with (nolock)
				outer apply (
					select top 1
						rhdre.RegistryHealDepResErr_id
					from
						v_RegistryHealDepResErr rhdre (nolock)
					where
						rhdre.RegistryHealDepErrorType_id = rhdet.RegistryHealDepErrorType_id
				) rhdre
			where
				rhdet.RegistryHealDepErrorType_id = :RegistryHealDepErrorType_id
		", array(
			'RegistryHealDepErrorType_id' => $data['RegistryHealDepErrorType_id']
		));
	}

	/**
	 * Загрузка списка реестров
	 */
	function loadRegistryMzGrid($data) {
		$filterList = array(
			"r.RegistryType_id = :RegistryType_id"
		);
		$queryParams = array(
			'RegistryType_id' => $data['RegistryType_id']
		);

		$from = "
			{$this->scheme}.v_Registry r with (nolock)
			outer apply (
				select top 1
					*
				from
					v_RegistryHealDepCheckJournal rhdcj with (nolock)
				where
					rhdcj.Registry_id = r.Registry_id
				order by
					rhdcj.RegistryHealDepCheckJournal_Count desc,
					rhdcj.RegistryHealDepCheckJournal_id desc
			) rhdcj
		";

		switch($data['Status_SysNick']) {
			case 'new':
				$filterList[] = "rcs.RegistryCheckStatus_SysNick = 'SendMZ'";
				break;
			case 'work':
				$filterList[] = "rcs.RegistryCheckStatus_SysNick = 'CheckMZ'";
				break;
			case 'accepted':
				$filterList[] = "rcs.RegistryCheckStatus_SysNick IN ('HalfAcceptMZ', 'AcceptMZ')";
				break;
			case 'journal':
				$from = "
					{$this->scheme}.v_Registry r with (nolock)
					inner join v_RegistryHealDepCheckJournal rhdcj with (nolock) on rhdcj.Registry_id = r.Registry_id
				";

				$filterList[] = "rcs.RegistryCheckStatus_SysNick IN ('HalfAcceptMZ', 'AcceptMZ', 'RejectMZ')";
				break;
			default:
				return false;
		}

		if (!empty($data['filterLpu_id'])) {
			$queryParams['filterLpu_id'] = $data['filterLpu_id'];
			$filterList[] = "r.Lpu_id = :filterLpu_id";
		}

		if (isset($data['RegistryHealDepCheckJournal_sendHDDT_Range'][0])) {
			$queryParams['RegistryHealDepCheckJournal_sendHDDT_Range_0'] = $data['RegistryHealDepCheckJournal_sendHDDT_Range'][0];
			$filterList[] = "rhdcj.RegistryHealDepCheckJournal_sendHDDT >= :RegistryHealDepCheckJournal_sendHDDT_Range_0";
		}

		if (isset($data['RegistryHealDepCheckJournal_sendHDDT_Range'][1])) {
			$queryParams['RegistryHealDepCheckJournal_sendHDDT_Range_1'] = $data['RegistryHealDepCheckJournal_sendHDDT_Range'][1];
			$filterList[] = "rhdcj.RegistryHealDepCheckJournal_sendHDDT <= :RegistryHealDepCheckJournal_sendHDDT_Range_1";
		}

		if (isset($data['RegistryHealDepCheckJournal_sendDT_Range'][0])) {
			$queryParams['RegistryHealDepCheckJournal_sendDT_Range_0'] = $data['RegistryHealDepCheckJournal_sendDT_Range'][0];
			$filterList[] = "rhdcj.RegistryHealDepCheckJournal_sendDT >= :RegistryHealDepCheckJournal_sendDT_Range_0";
		}

		if (isset($data['RegistryHealDepCheckJournal_sendDT_Range'][1])) {
			$queryParams['RegistryHealDepCheckJournal_sendDT_Range_1'] = $data['RegistryHealDepCheckJournal_sendDT_Range'][1];
			$filterList[] = "rhdcj.RegistryHealDepCheckJournal_sendDT <= :RegistryHealDepCheckJournal_sendDT_Range_1";
		}

		if (isset($data['RegistryHealDepCheckJournal_endCheckDT_Range'][0])) {
			$queryParams['RegistryHealDepCheckJournal_endCheckDT_Range_0'] = $data['RegistryHealDepCheckJournal_endCheckDT_Range'][0];
			$filterList[] = "rhdcj.RegistryHealDepCheckJournal_endCheckDT >= :RegistryHealDepCheckJournal_endCheckDT_Range_0";
		}

		if (isset($data['RegistryHealDepCheckJournal_endCheckDT_Range'][1])) {
			$queryParams['RegistryHealDepCheckJournal_endCheckDT_Range_1'] = $data['RegistryHealDepCheckJournal_endCheckDT_Range'][1];
			$filterList[] = "rhdcj.RegistryHealDepCheckJournal_endCheckDT <= :RegistryHealDepCheckJournal_endCheckDT_Range_1";
		}

		if (isset($data['Registry_begDate_Range'][0])) {
			$queryParams['Registry_begDate_Range_0'] = $data['Registry_begDate_Range'][0];
			$filterList[] = "r.Registry_begDate >= :Registry_begDate_Range_0";
		}

		if (isset($data['Registry_begDate_Range'][1])) {
			$queryParams['Registry_begDate_Range_1'] = $data['Registry_begDate_Range'][1];
			$filterList[] = "r.Registry_begDate <= :Registry_begDate_Range_1";
		}

		if (isset($data['Registry_endDate_Range'][0])) {
			$queryParams['Registry_endDate_Range_0'] = $data['Registry_endDate_Range'][0];
			$filterList[] = "r.Registry_endDate >= :Registry_endDate_Range_0";
		}

		if (isset($data['Registry_endDate_Range'][1])) {
			$queryParams['Registry_endDate_Range_1'] = $data['Registry_endDate_Range'][1];
			$filterList[] = "r.Registry_endDate <= :Registry_endDate_Range_1";
		}

		$query = "
			-- variables 
			declare @curDate date = dbo.tzGetDate();
			-- end variables
			
			select
				-- select
				r.Registry_id,
				r.Lpu_id,
				l.Lpu_Nick,
				convert(varchar(10), r.Registry_accDate, 104) as Registry_accDate,
				convert(varchar(10), r.Registry_begDate, 104) as Registry_begDate,
				convert(varchar(10), r.Registry_endDate, 104) as Registry_endDate,
				r.Registry_Num,
				pt.PayType_Name,
				isnull(R.Registry_RecordCount, 0) as Registry_Count,
				isnull(R.Registry_Sum, 0.00) as Registry_Sum,
				convert(varchar(10), rhdcj.RegistryHealDepCheckJournal_sendHDDT, 104) as RegistryHealDepCheckJournal_sendHDDT,
				pushd.pmUser_Name as pmUser_sendHDName,
				convert(varchar(10), rhdcj.RegistryHealDepCheckJournal_sendDT, 104) as RegistryHealDepCheckJournal_sendDT,
				pus.pmUser_Name as pmUser_sendName,
				convert(varchar(10), rhdcj.RegistryHealDepCheckJournal_endCheckDT, 104) as RegistryHealDepCheckJournal_endCheckDT,
				rcsj.RegistryCheckStatus_Name,
				rcs.RegistryCheckStatus_SysNick,
				puec.pmUser_Name as pmUser_endCheckName,
				rhdcj.RegistryHealDepCheckJournal_AccRecCount,
				rhdcj.RegistryHealDepCheckJournal_DecRecCount,
				rhdcj.RegistryHealDepCheckJournal_UncRecCount,
				rhdcj.RegistryHealDepCheckJournal_AccRecSum
				-- end select
			from
				-- from
				{$from}
				left join v_Lpu l with (nolock) on l.Lpu_id = r.Lpu_id
				left join v_RegistryCheckStatus rcs with (nolock) on r.RegistryCheckStatus_id = rcs.RegistryCheckStatus_id
				left join v_PayType pt with (nolock) on pt.PayType_id = r.PayType_id
				left join v_pmUser pushd with (nolock) on pushd.pmUser_id = rhdcj.pmUser_sendHDID 
				left join v_pmUser pus with (nolock) on pus.pmUser_id = rhdcj.pmUser_sendID 
				left join v_pmUser puec with (nolock) on puec.pmUser_id = rhdcj.pmUser_endCheckID 
				left join v_RegistryCheckStatus rcsj with (nolock) on rcsj.RegistryCheckStatus_id = rhdcj.RegistryCheckStatus_id
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				r.Registry_id
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true, true);
	}

	/**
	 * Загрузка списка случаев реестров
	 */
	function loadRegistryDataMzGrid($data) {
		$filterList = array(
			"rd.Registry_id = :Registry_id"
		);
		$joinList = array();
		$queryParams = array(
			'Registry_id' => $data['Registry_id']
		);
		$selectList = array();

		$EvnField = 'Evn_id';

		$this->setRegistryParamsByType($data);

		switch ( $this->RegistryType_id ) {
			case 1:
			case 14:
				$dataObject = "EvnSection";
				break;
			case 2:
				$dataObject = "EvnVizitPL";
				break;
			case 6:
				$dataObject = "CmpCallCard";
				$EvnField = 'CmpCallCard_id';
				break;
			case 7:
				$dataObject = "EvnPLDispDop13";
				break;
			case 9:
				$dataObject = "EvnPLDispOrp";
				break;
			case 11:
				$dataObject = "EvnPLDispProf";
				break;
		}

		if ( in_array($this->RegistryType_id, array(7, 9, 11)) ) {
			$joinList[] = 'left join v_EvnPLDisp epld with (nolock) on epld.EvnPLDisp_id = RD.Evn_id';
			$selectList[] = 'epld.DispClass_id';
		}
		else {
			$selectList[] = 'null as DispClass_id';
		}

		if ( $this->RegistryType_id == 6 ) {
			$joinList[] = '
				left join v_CmpCloseCard cclc (nolock) on cclc.CmpCallCard_id = DO.CmpCallCard_id
			';
			$selectList[] = "cclc.CmpCloseCard_id";
			$selectList[] = "DO.CmpCallCardInputType_id";
		}
		else {
			$selectList[] = "null as CmpCloseCard_id";
			$selectList[] = "null as CmpCallCardInputType_id";
		}

		if (!empty($data['Person_SurName'])) {
			$filterList[] = "rd.Person_SurName like :Person_SurName  + '%'";
			$queryParams['Person_SurName'] = $data['Person_SurName'];
		}

		if (!empty($data['Person_FirName'])) {
			$filterList[] = "rd.Person_FirName like :Person_FirName  + '%'";
			$queryParams['Person_FirName'] = $data['Person_FirName'];
		}

		if (!empty($data['Person_SecName'])) {
			$filterList[] = "rd.Person_SecName like :Person_SecName  + '%'";
			$queryParams['Person_SecName'] = $data['Person_SecName'];
		}

		if (!empty($data['RegistryHealDepResType_id'])) {
			if ($data['RegistryHealDepResType_id'] == -1) {
				$filterList[] = "RHDCR.RegistryHealDepResType_id IS NULL";
			} else {
				$filterList[] = "RHDCR.RegistryHealDepResType_id = :RegistryHealDepResType_id";
				$queryParams['RegistryHealDepResType_id'] = $data['RegistryHealDepResType_id'];
			}
		}

		if (!empty($data['RegistryHealDepErrorType_id'])) {
			$filterList[] = "RHDRE.RegistryHealDepErrorType_id = :RegistryHealDepErrorType_id";
			$queryParams['RegistryHealDepErrorType_id'] = $data['RegistryHealDepErrorType_id'];
		}

		if (!empty($data['Evn_id'])) {
			$filterList[] = "RD.Evn_id = :Evn_id";
			$queryParams['Evn_id'] = $data['Evn_id'];
		}

		if (!empty($data['MedicalCareBudgType_id'])) {
			$filterList[] = "DO.MedicalCareBudgType_id = :MedicalCareBudgType_id";
			$queryParams['MedicalCareBudgType_id'] = $data['MedicalCareBudgType_id'];
		}

		if (!empty($data['getIdsOnly'])) {
			// получение списка всех идешников для выделения всех случаев на всех страницах в гриде
			$query = "
				select
					rd.Evn_id
				from
					{$this->scheme}.v_{$this->RegistryDataObject} RD with (NOLOCK)
					left join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.{$EvnField} = RD.Evn_id
					left join v_RegistryHealDepResErr RHDRE (nolock) on RHDRE.Registry_id = RD.Registry_id and RHDRE.{$EvnField} = RD.Evn_id
				where
					" . implode(' and ', $filterList) . "
			";

			$resp = $this->queryResult($query, $queryParams);
			$response = array(
				'Error_Msg' => '',
				'ids' => array()
			);
			foreach($resp as $respone) {
				$response['ids'][] = $respone['Evn_id'];
			}
			return $response;
		}

		$query = "
			-- variables 
			declare @curDate date = dbo.tzGetDate();
			-- end variables
			
			select
				-- select
				rd.Evn_id,
				rd.Evn_rid,
				rd.Person_id,
				rd.PersonEvn_id,
				rd.Server_id,
				rd.EvnClass_id,
				RHDCR.RegistryHealDepResType_id, 
				RHDERT.RegistryHealDepErrorType_Name,
				RD.Person_FIO,
				convert(varchar(10), RD.Person_BirthDay, 104) as Person_BirthDay,
				convert(varchar(10), RD.Evn_setDate, 104) as Evn_setDate,
				convert(varchar(10), RD.Evn_disDate, 104) as Evn_disDate,
				RD.RegistryData_ItogSum,
				'' as MedicalCareType_Name
				" . (count($selectList) > 0 ? "," . implode(',', $selectList) : "") . "
				-- end select
			from
				-- from
				{$this->scheme}.v_{$this->RegistryDataObject} RD with (NOLOCK)
				left join v_{$dataObject} DO (nolock) on DO.{$dataObject}_id = RD.Evn_id
				left join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.{$EvnField} = RD.Evn_id
				left join v_RegistryHealDepResErr RHDRE (nolock) on RHDRE.Registry_id = RD.Registry_id and RHDRE.{$EvnField} = RD.Evn_id
				left join v_RegistryHealDepErrorType RHDERT (nolock) on RHDERT.RegistryHealDepErrorType_id = RHDRE.RegistryHealDepErrorType_id
				" . implode(' ', $joinList) . "
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				rd.Evn_id
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true, true);
	}

	/**
	 * Загрузка счётчиков списка случаев реестров
	 */
	function getRegistryDataMzGridCounters($data) {
		// тянем счётчики
		$resp = $this->queryResult("
			select
				rhdcj.RegistryHealDepCheckJournal_AccRecCount,
				rhdcj.RegistryHealDepCheckJournal_DecRecCount,
				rhdcj.RegistryHealDepCheckJournal_UncRecCount,
				'' as Error_Msg
			from
				v_RegistryHealDepCheckJournal rhdcj with (nolock)
			where
				rhdcj.Registry_id = :Registry_id
			order by
				rhdcj.RegistryHealDepCheckJournal_Count desc,
				rhdcj.RegistryHealDepCheckJournal_id desc
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (isset($resp[0])) {
			return $resp[0];
		} else {
			return false;
		}
	}

	/**
	 * Изменяет статус реестра МЗ
	 */
	function setRegistryMzCheckStatus($data) {
		// сначала получаем текущий статус реестра, чтобы убедиться в правомерности смены статуса
		$resp = $this->queryResult("
			select
				r.Registry_id,
				r.RegistryType_id,
				rcs.RegistryCheckStatus_SysNick,
				rcs.RegistryCheckStatus_Name,
				rcs2.RegistryCheckStatus_id,
				rhdcj.RegistryHealDepCheckJournal_id
			from
				{$this->scheme}.v_Registry r (nolock)
				left join v_RegistryCheckStatus rcs (nolock) on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
				inner join v_RegistryCheckStatus rcs2 (nolock) on rcs2.RegistryCheckStatus_SysNick = :RegistryCheckStatus_SysNick
				outer apply (
					select top 1
						rhdcj.RegistryHealDepCheckJournal_id
					from
						v_RegistryHealDepCheckJournal rhdcj with (nolock)
					where
						rhdcj.Registry_id = r.Registry_id
					order by
						rhdcj.RegistryHealDepCheckJournal_Count desc,
						rhdcj.RegistryHealDepCheckJournal_id desc
				) rhdcj
			where
				r.Registry_id = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id'],
			'RegistryCheckStatus_SysNick' => $data['RegistryCheckStatus_SysNick']
		));

		if (empty($resp[0]['Registry_id'])) {
			return array('Error_Msg' => 'Не удалось получить данные по реестру');
		}

		$data['RegistryType_id'] = $resp[0]['RegistryType_id'];

		$this->setRegistryParamsByType($data);

		$EvnField = ($this->RegistryType_id == 6 ? 'CmpCallCard_id' : 'Evn_id');

		$allowed = false;
		switch($resp[0]['RegistryCheckStatus_SysNick']) {
			case null:
				if ($data['RegistryCheckStatus_SysNick'] == 'SignECP') {
					$allowed = true;
				}
			case 'SignECP': // подписан ЭЦП
				if ($data['RegistryCheckStatus_SysNick'] == 'SendMZ') {
					$allowed = true;
				}
			case 'SendMZ': // новые
				if ($data['RegistryCheckStatus_SysNick'] == 'CheckMZ') {
					$allowed = true;
				}
			case 'CheckMZ': // на проверке
				if (in_array($data['RegistryCheckStatus_SysNick'], array('AcceptMZ', 'HalfAcceptMZ', 'RejectMZ'))) {
					$allowed = true;
				}
				break;
		}

		if (!$allowed) {
			return array('Error_Msg' => 'Нельзя сменить статус реестра, т.к. его статус "'.$resp[0]['RegistryCheckStatus_Name'].'"');
		}

		if (empty($resp[0]['RegistryCheckStatus_id'])) {
			return array('Error_Msg' => 'Ошибка получения идентификатора статуса');
		}

		if ($data['RegistryCheckStatus_SysNick'] == 'RejectMZ') {
			// Если в реестре нет отклонённых случаев, появляется сообщение об ошибке: «Реестр не может быть отклонён, так как не содержит отклонённых от оплаты случаев».
			$resp_check = $this->queryResult("
				select top 1
					rd.Evn_id
				from
					{$this->scheme}.v_Registry r with (nolock)
					inner join {$this->scheme}.v_{$this->RegistryDataObject} RD with (NOLOCK) on RD.Registry_id = :Registry_id
					inner join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.{$EvnField} = RD.Evn_id
				where
					r.Registry_id = :Registry_id
					and RHDCR.RegistryHealDepResType_id = 2
			", array(
				'Registry_id' => $data['Registry_id']
			));

			if (empty($resp_check[0]['Evn_id'])) {
				return array('Error_Msg' => 'Реестр не может быть отклонён, так как не содержит отклонённых от оплаты случаев');
			}
		}

		$data['RegistryCheckStatus_id'] = $resp[0]['RegistryCheckStatus_id'];

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@curdate datetime = dbo.tzGetDate();
			exec {$this->scheme}.p_Registry_setRegistryCheckStatus
				@Registry_id = :Registry_id,
				@RegistryCheckStatus_id = :RegistryCheckStatus_id,
				@Registry_RegistryCheckStatusDate = @curdate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$resp_save = $result->result('array');

			if ($data['RegistryCheckStatus_SysNick'] == 'SendMZ' || (empty($resp[0]['RegistryHealDepCheckJournal_id']) && $data['RegistryCheckStatus_SysNick'] != 'SignECP')) {
				// если вдруг журнала ещё нет или мы только отправляем реестр на проверку в МЗ, то создадим новый журнал
				$resp = $this->saveRegistryHealDepCheckJournal(array(
					'Registry_id' => $data['Registry_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp[0]['RegistryHealDepCheckJournal_id'])) {
					$data['RegistryHealDepCheckJournal_id'] = $resp[0]['RegistryHealDepCheckJournal_id'];
				} else if (!empty($resp[0]['Error_Msg'])) {
					return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении журнала проверки реестра: ' . $resp[0]['Error_Msg']);
				} else {
					return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении журнала проверки реестра');
				}
			} else {
				$data['RegistryHealDepCheckJournal_id'] = $resp[0]['RegistryHealDepCheckJournal_id'];
			}

			switch ($data['RegistryCheckStatus_SysNick']) {
				case 'SendMZ':
					$this->db->query("
						update
							RegistryHealDepCheckJournal with (rowlock)
						set
							RegistryHealDepCheckJournal_sendHDDT = dbo.tzGetDate(),
							pmUser_sendHDID = :pmUser_id,
							pmUser_updID = :pmUser_id,
							RegistryHealDepCheckJournal_updDT = dbo.tzGetDate()
						where
							RegistryHealDepCheckJournal_id = :RegistryHealDepCheckJournal_id
					", array(
						'RegistryHealDepCheckJournal_id' => $data['RegistryHealDepCheckJournal_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					break;
				case 'CheckMZ':
					$this->db->query("
						update
							RegistryHealDepCheckJournal with (rowlock)
						set
							RegistryHealDepCheckJournal_sendDT = dbo.tzGetDate(),
							pmUser_sendID = :pmUser_id,
							pmUser_updID = :pmUser_id,
							RegistryHealDepCheckJournal_updDT = dbo.tzGetDate()
						where
							RegistryHealDepCheckJournal_id = :RegistryHealDepCheckJournal_id
					", array(
						'RegistryHealDepCheckJournal_id' => $data['RegistryHealDepCheckJournal_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					$resp_e = $this->queryResult("
						select
							rd.Evn_id
						from
							{$this->scheme}.v_Registry r with (nolock)
							inner join {$this->scheme}.v_{$this->RegistryDataObject} RD with (NOLOCK) on RD.Registry_id = :Registry_id
							left join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.{$EvnField} = RD.Evn_id
						where
							r.Registry_id = :Registry_id
							and RHDCR.RegistryHealDepCheckRes_id is null
					", array(
						'Registry_id' => $data['Registry_id']
					));

					foreach ($resp_e as $one_e) {
						// выполняется создание записи без статуса проверки случая
						$resp_rhdcr = $this->saveRegistryHealDepCheckRes(array(
							'Registry_id' => $data['Registry_id'],
							$EvnField => $one_e['Evn_id'],
							'RegistryHealDepResType_id' => null,
							'pmUser_id' => $data['pmUser_id']
						));

						if (!empty($resp_rhdcr[0]['Error_Msg'])) {
							return array('Error_Msg' => 'Ошибка при сохранении признака проверки случая: ' . $resp[0]['Error_Msg']);
						} else if (empty($resp_rhdcr[0]['RegistryHealDepCheckRes_id'])) {
							return array('Error_Msg' => 'Ошибка при сохранении признака проверки случая');
						}
					}
					break;
				case 'AcceptMZ':
				case 'HalfAcceptMZ':
				case 'RejectMZ':
					$this->db->query("
						update
							RegistryHealDepCheckJournal with (rowlock)
						set
							RegistryHealDepCheckJournal_endCheckDT = dbo.tzGetDate(),
							pmUser_endCheckID = :pmUser_id,
							RegistryCheckStatus_id = :RegistryCheckStatus_id,
							pmUser_updID = :pmUser_id,
							RegistryHealDepCheckJournal_updDT = dbo.tzGetDate()
						where
							RegistryHealDepCheckJournal_id = :RegistryHealDepCheckJournal_id
					", array(
						'RegistryHealDepCheckJournal_id' => $data['RegistryHealDepCheckJournal_id'],
						'RegistryCheckStatus_id' => $data['RegistryCheckStatus_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					break;
			}

			return $resp_save;
		} else {
			return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении статуса реестра');
		}
	}

	/**
	 * Рекеширование кол-ва принятых / непринятых / непроверенных случаев и суммы к оплате в журнале проврок
	 */
	function recacheRegistryHealDepCheckJournal($data) {
		$this->setRegistryParamsByType($data);

		$EvnField = ($this->RegistryType_id == 6 ? 'CmpCallCard_id' : 'Evn_id');

		$this->db->query("
			declare
				@RegistryHealDepCheckJournal_AllRecCount int,
				@RegistryHealDepCheckJournal_AccRecCount int,
				@RegistryHealDepCheckJournal_UncRecCount int,
				@RegistryHealDepCheckJournal_DecRecCount int,
				@RegistryHealDepCheckJournal_AllRecSum numeric(9,2),
				@RegistryHealDepCheckJournal_AccRecSum numeric(9,2);

			select
				@RegistryHealDepCheckJournal_AllRecCount = count(rd.Evn_id),
				@RegistryHealDepCheckJournal_AccRecCount = sum(case when RHDCR.RegistryHealDepResType_id = 1 then 1 else 0 end),
				@RegistryHealDepCheckJournal_DecRecCount = sum(case when RHDCR.RegistryHealDepResType_id = 2 then 1 else 0 end),
				@RegistryHealDepCheckJournal_UncRecCount = sum(case when RHDCR.RegistryHealDepResType_id is null then 1 else 0 end),
				@RegistryHealDepCheckJournal_AllRecSum = sum(rd.RegistryData_ItogSum),
				@RegistryHealDepCheckJournal_AccRecSum = sum(case when RHDCR.RegistryHealDepResType_id = 1 then rd.RegistryData_ItogSum else 0 end)
			from
				v_RegistryHealDepCheckJournal rhdcj with (nolock)
				inner join {$this->scheme}.v_{$this->RegistryDataObject} RD with (NOLOCK) on RD.Registry_id = rhdcj.Registry_id
				left join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.{$EvnField} = RD.Evn_id
			where
				rhdcj.RegistryHealDepCheckJournal_id = :RegistryHealDepCheckJournal_id
				
			update
				RegistryHealDepCheckJournal with (rowlock)
			set
				RegistryHealDepCheckJournal_AllRecCount = @RegistryHealDepCheckJournal_AllRecCount,
				RegistryHealDepCheckJournal_AccRecCount = @RegistryHealDepCheckJournal_AccRecCount,
				RegistryHealDepCheckJournal_DecRecCount = @RegistryHealDepCheckJournal_DecRecCount,
				RegistryHealDepCheckJournal_UncRecCount = @RegistryHealDepCheckJournal_UncRecCount,
				RegistryHealDepCheckJournal_AllRecSum = @RegistryHealDepCheckJournal_AllRecSum,
				RegistryHealDepCheckJournal_AccRecSum = @RegistryHealDepCheckJournal_AccRecSum,
				pmUser_updID = :pmUser_id,
				RegistryHealDepCheckJournal_updDT = dbo.tzGetDate()
			where
				RegistryHealDepCheckJournal_id = :RegistryHealDepCheckJournal_id
		", array(
			'RegistryHealDepCheckJournal_id' => $data['RegistryHealDepCheckJournal_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}

	/**
	 * Сохранение записи в журнале проверок реестров
	 */
	function saveRegistryHealDepCheckJournal($data) {
		$resp = $this->queryResult("
			declare
				@RegistryHealDepCheckJournal_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			exec p_RegistryHealDepCheckJournal_ins
				@RegistryHealDepCheckJournal_id = @RegistryHealDepCheckJournal_id output,
				@Registry_id = :Registry_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @RegistryHealDepCheckJournal_id as RegistryHealDepCheckJournal_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		", array(
			'Registry_id' => $data['Registry_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($resp[0]['RegistryHealDepCheckJournal_id'])) {
			$this->recacheRegistryHealDepCheckJournal(array(
				'RegistryHealDepCheckJournal_id' => $resp[0]['RegistryHealDepCheckJournal_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return $resp;
	}

	/**
	 * Принятие реестра
	 */
	function acceptRegistryMz($data)
	{
		$this->setRegistryParamsByType($data);

		$EvnField = ($this->RegistryType_id == 6 ? 'CmpCallCard_id' : 'Evn_id');

		// достаём не проверенные случаи
		$resp = $this->queryResult("
			select
				rd.Evn_id,
				rhdcj.RegistryHealDepCheckJournal_id
			from
				{$this->scheme}.v_Registry r with (nolock)
				outer apply (
					select top 1
						rhdcj.RegistryHealDepCheckJournal_id
					from
						v_RegistryHealDepCheckJournal rhdcj with (nolock)
					where
						rhdcj.Registry_id = r.Registry_id
					order by
						rhdcj.RegistryHealDepCheckJournal_Count desc,
						rhdcj.RegistryHealDepCheckJournal_id desc
				) rhdcj
				inner join {$this->scheme}.v_{$this->RegistryDataObject} RD with (NOLOCK) on RD.Registry_id = :Registry_id
				left join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.{$EvnField} = RD.Evn_id
			where
				r.Registry_id = :Registry_id
				and RHDCR.RegistryHealDepResType_id is null
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (!empty($resp[0]['Evn_id'])) {
			// что делать с непроверенными случаями?
			switch ($data['action']) {
				case 'acceptAll':
				case 'rejectAll':
					foreach ($resp as $respone) {
						// принимаем/отклоняем случаи
						$resp_save = $this->saveRegistryHealDepCheckRes(array(
							'Registry_id' => $data['Registry_id'],
							$EvnField => $respone['Evn_id'],
							'RegistryHealDepResType_id' => ($data['action'] == 'acceptAll') ? 1 : 2,
							'pmUser_id' => $data['pmUser_id']
						));

						if (!empty($resp_save[0]['Error_Msg'])) {
							return array('Error_Msg' => 'Ошибка при сохранении признака проверки случая: ' . $resp[0]['Error_Msg']);
						} else if (empty($resp_save[0]['RegistryHealDepCheckRes_id'])) {
							return array('Error_Msg' => 'Ошибка при сохранении признака проверки случая');
						}
					}
					break;
				default:
					return array('Error_Msg' => 'Реестр не может быть принят, так как есть непроверенные случаи');
					break;
			}

			$this->recacheRegistryHealDepCheckJournal(array(
				'RegistryHealDepCheckJournal_id' => $resp[0]['RegistryHealDepCheckJournal_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// проверяем есть ли отклонённые случаи
		$resp_check = $this->queryResult("
			select top 1
				RHDCR.{$EvnField} as Evn_id
			from
				{$this->scheme}.v_{$this->RegistryDataObject} RD with (NOLOCK)
				inner join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.{$EvnField} = RD.Evn_id
			where
				RD.Registry_id = :Registry_id
				and RHDCR.RegistryHealDepResType_id = 2
		", array(
			'Registry_id' => $data['Registry_id'],
		));

		if (!empty($resp_check[0]['Evn_id'])) {
			// Принят частично МЗ
			$data['RegistryCheckStatus_SysNick'] = 'HalfAcceptMZ';
		} else {
			// Принят полностью МЗ
			$data['RegistryCheckStatus_SysNick'] = 'AcceptMZ';
		}

		return $this->setRegistryMzCheckStatus($data);
	}

	/**
	 * Принятие случаев в реестре
	 */
	function processRegistryDataMz($data) {
		if (empty($data['Evn_ids'])) {
			return array('Error_Msg' => 'Не передан список случаев');
		}

		$this->setRegistryParamsByType($data);

		$EvnField = ($this->RegistryType_id == 6 ? 'CmpCallCard_id' : 'Evn_id');

		switch($data['action']) {
			case 'accept':
				$RegistryHealDepResType_id = 1;
				break;
			case 'reject':
				$RegistryHealDepResType_id = 2;
				break;
			case 'reset':
				$RegistryHealDepResType_id = null;
				break;
			default:
				return array('Error_Msg' => 'Не передано действие');
		}

		foreach($data['Evn_ids'] as $Evn_id) {
			// производится удаление ошибок для случая в текущем реестре;
			$resp = $this->queryResult("
				select
					rd.Evn_id,
					rhdre.RegistryHealDepResErr_id
				from
					{$this->scheme}.v_{$this->RegistryDataObject} rd (nolock)
					left join v_RegistryHealDepResErr rhdre (nolock) on rhdre.Registry_id = :Registry_id and rhdre.{$EvnField} = :Evn_id
				where
					rd.Registry_id = :Registry_id
					and rd.Evn_id = :Evn_id
			", array(
				'Registry_id' => $data['Registry_id'],
				'Evn_id' => $Evn_id,
			));

			if (!empty($resp[0]['RegistryHealDepResErr_id'])) {
				foreach($resp as $respone) {
					$resp_del = $this->deleteRegistryHealDepResErr(array(
						'RegistryHealDepResErr_id' => $respone['RegistryHealDepResErr_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					if (!empty($resp_del[0]['Error_Msg'])) {
						return array(
							'Error_Msg' => $resp_del[0]['Error_Msg']
						);
					}
				}
			}

			if (!empty($resp[0]['Evn_id'])) {
				if (!empty($data['RegistryHealDepErrorType_id']) && $data['action'] == 'reject') {
					// сохраняем ошибку
					$resp_save = $this->saveRegistryHealDepResErr(array(
						'Registry_id' => $data['Registry_id'],
						'Evn_id' => $resp[0]['Evn_id'],
						'RegistryHealDepErrorType_id' => $data['RegistryHealDepErrorType_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					if (!empty($resp_save[0]['Error_Msg'])) {
						return array(
							'Error_Msg' => $resp_save[0]['Error_Msg']
						);
					}
				}

				// случай приобретают результат проверки «Принят».
				$resp_save = $this->saveRegistryHealDepCheckRes(array(
					'Registry_id' => $data['Registry_id'],
					$EvnField => $resp[0]['Evn_id'],
					'RegistryHealDepResType_id' => $RegistryHealDepResType_id,
					'pmUser_id' => $data['pmUser_id']
				));

				if (!empty($resp_save[0]['Error_Msg'])) {
					return array(
						'Error_Msg' => $resp_save[0]['Error_Msg']
					);
				}
			}
		}

		$resp = $this->queryResult("
			select top 1
				rhdcj.RegistryHealDepCheckJournal_id
			from
				v_RegistryHealDepCheckJournal rhdcj with (nolock)
			where
				rhdcj.Registry_id = :Registry_id
			order by
				rhdcj.RegistryHealDepCheckJournal_Count desc,
				rhdcj.RegistryHealDepCheckJournal_id desc
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (!empty($resp[0]['RegistryHealDepCheckJournal_id'])) {
			$this->recacheRegistryHealDepCheckJournal(array(
				'RegistryHealDepCheckJournal_id' => $resp[0]['RegistryHealDepCheckJournal_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return $resp_save;
	}

	/**
	 * Удаление ошибок в реестрах МЗ
	 */
	function deleteRegistryHealDepResErr($data) {
		return $this->queryResult("
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_RegistryHealDepResErr_del
				@RegistryHealDepResErr_id = :RegistryHealDepResErr_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
		", array(
			'RegistryHealDepResErr_id' => $data['RegistryHealDepResErr_id']
		));
	}

	/**
	 * Сохранение признака приёма/отклонения случая
	 */
	function saveRegistryHealDepCheckRes($data) {
		$this->setRegistryParamsByType($data);

		$EvnField = ($this->RegistryType_id == 6 ? 'CmpCallCard_id' : 'Evn_id');

		// сперва ищем, может уже сохранён признак
		$resp_check = $this->queryResult("
			select
				RegistryHealDepCheckRes_id
			from
				v_RegistryHealDepCheckRes (nolock)
			where
				Registry_id = :Registry_id
				and {$EvnField} = :Evn_id
		", array(
			'Registry_id' => $data['Registry_id'],
			'Evn_id' => $data[$EvnField]
		));

		if (!empty($resp_check[0]['RegistryHealDepCheckRes_id'])) {
			$data['RegistryHealDepCheckRes_id'] = $resp_check[0]['RegistryHealDepCheckRes_id'];
			$procedure = 'p_RegistryHealDepCheckRes_upd';
		} else {
			$data['RegistryHealDepCheckRes_id'] = null;
			$procedure = 'p_RegistryHealDepCheckRes_ins';
		}

		$query = "	
			declare
				@RegistryHealDepCheckRes_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @RegistryHealDepCheckRes_id = :RegistryHealDepCheckRes_id;
			exec {$procedure}
				@RegistryHealDepCheckRes_id = @RegistryHealDepCheckRes_id output,
				@RegistryHealDepResType_id = :RegistryHealDepResType_id,
				@Registry_id = :Registry_id,
				@Evn_id = :Evn_id,
				@CmpCallCard_id = :CmpCallCard_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @RegistryHealDepCheckRes_id as RegistryHealDepCheckRes_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		return $this->queryResult($query, array(
			'RegistryHealDepCheckRes_id' => $data['RegistryHealDepCheckRes_id'],
			'Registry_id' => $data['Registry_id'],
			'Evn_id' => (!empty($data['Evn_id']) ? $data['Evn_id'] : null),
			'CmpCallCard_id' => (!empty($data['CmpCallCard_id']) ? $data['CmpCallCard_id'] : null),
			'RegistryHealDepResType_id' => $data['RegistryHealDepResType_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}

	/**
	 * Сохранение ошибки
	 */
	function saveRegistryHealDepResErr($data) {
		$this->setRegistryParamsByType($data);

		$EvnField = ($this->RegistryType_id == 6 ? 'CmpCallCard_id' : 'Evn_id');

		// сперва ищем, может уже сохранён признак
		$resp_check = $this->queryResult("
			select
				RegistryHealDepResErr_id
			from
				v_RegistryHealDepResErr (nolock)
			where
				Registry_id = :Registry_id
				and {$EvnField} = :Evn_id
		", array(
			'Registry_id' => $data['Registry_id'],
			'Evn_id' => $data['Evn_id']
		));

		if (!empty($resp_check[0]['RegistryHealDepResErr_id'])) {
			$data['RegistryHealDepResErr_id'] = $resp_check[0]['RegistryHealDepResErr_id'];
			$procedure = 'p_RegistryHealDepResErr_upd';
		} else {
			$data['RegistryHealDepResErr_id'] = null;
			$procedure = 'p_RegistryHealDepResErr_ins';
		}

		$query = "	
			declare
				@RegistryHealDepResErr_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @RegistryHealDepResErr_id = :RegistryHealDepResErr_id;
			exec {$procedure}
				@RegistryHealDepResErr_id = @RegistryHealDepResErr_id output,
				@RegistryHealDepErrorType_id = :RegistryHealDepErrorType_id,
				@Registry_id = :Registry_id,
				@{$EvnField} = :Evn_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @RegistryHealDepResErr_id as RegistryHealDepResErr_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		return $this->queryResult($query, array(
			'RegistryHealDepResErr_id' => $data['RegistryHealDepResErr_id'],
			'Registry_id' => $data['Registry_id'],
			'Evn_id' => $data['Evn_id'],
			'RegistryHealDepErrorType_id' => $data['RegistryHealDepErrorType_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}

	/**
	 * Получение списка ошибок для комбобокса
	 */
	function loadRegistryHealDepErrorTypeCombo($data) {
		return $this->queryResult("
			select
				rhdet.RegistryHealDepErrorType_id,
				rhdet.RegistryHealDepErrorType_Code,
				rhdet.RegistryHealDepErrorType_Name
			from
				v_RegistryHealDepErrorType rhdet with (nolock)
		");
	}

	/**
	 * Отправка в МЗ
	 */
	function sendRegistryToMZ($data)
	{
		// 1. получаем файл
		$query = "
			SELECT
				R.Registry_xmlExportPath,
				R.Registry_ExportSign,
				R.RegistryCheckStatus_id,
				RCS.RegistryCheckStatus_SysNick
			FROM
				{$this->scheme}.v_Registry R (nolock)
				left join v_RegistryCheckStatus rcs (nolock) on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			WHERE
				Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array('Registry_id'=>$data['Registry_id']));
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_xmlExportPath'])) {
				$data['Registry_xmlExportPath'] = $resp[0]['Registry_xmlExportPath'];
			}

			if (!empty($resp[0]['Registry_ExportSign'])) {
				$data['Registry_ExportSign'] = $resp[0]['Registry_ExportSign'];
			}

			if ($resp[0]['RegistryCheckStatus_SysNick'] != 'SignECP') {
				return array('Error_Msg' => 'Нельзя отправить реестр в МЗ, т.к. статус реестра отличается от "Подписан (ЭЦП)".');
			}
		}
		if (empty($data['Registry_xmlExportPath']) || !file_exists($data['Registry_xmlExportPath'])) {
			return array('Error_Msg' => 'XML-файл реестра не найден');
		}
		if (empty($data['Registry_ExportSign']) || !file_exists($data['Registry_ExportSign'])) {
			return array('Error_Msg' => 'Подписанный XML-файл реестра не найден');
		}

		// 2. меняем статус реестра
		$data['RegistryCheckStatus_SysNick'] = 'SendMZ'; // Отправлен в МЗ
		return $this->setRegistryMzCheckStatus($data);
	}

	/**
	 * Подписание реестра
	 */
	function signRegistry($data)
	{
		// 1. получаем файл
		$query = "
			SELECT
				R.Registry_xmlExportPath
			FROM
				{$this->scheme}.v_Registry R (nolock)
			WHERE
				Registry_id = :Registry_id
		";
		$result = $this->db->query($query, array('Registry_id'=>$data['Registry_id']));
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_xmlExportPath'])) {
				$data['Registry_xmlExportPath'] = $resp[0]['Registry_xmlExportPath'];
			}
		}
		if (empty($data['Registry_xmlExportPath']) || !file_exists($data['Registry_xmlExportPath'])) {
			return array('Error_Msg' => 'XML-файл реестра не найден');
		}
		$file_zip_sign = basename($data['Registry_xmlExportPath']);
		// 2. запихиваем его в зип вместе с подписью
		$out_dir = "re_xmlsigned_".time()."_".$data['Registry_id'];
		mkdir( EXPORTPATH_REGISTRY.$out_dir );
		$file_sign_name = EXPORTPATH_REGISTRY.$out_dir."/sign.txt";
		if ($data['documentSigned'] == 'notvalid') {
			// кладём левую подпись
			$data['documentSigned'] = 'MIIcPAYJKoZIhvcNAQcCoIIcLTCCHCkCAQExDDAKBgYqhQMCAgkFADBnBgkqhkiG9w0BBwGgWgRY
SwBnAGQAcQBhAGwAMgAwAEsAZwBMAEMAMwBkADIAKwBsAHAAVwBRAFEAVgBDAEUAZQBIAEQAWAA3
AFkARwBIAGUANwBxAE0AbgBjAGwANwBhAE0AdwA9AKCCF+owggWCMIIFMaADAgECAhA0PO3IOX6g
sEc0H5GtGllyMAgGBiqFAwICAzCCAUsxGDAWBgUqhQNkARINMTAyNzczOTcxMjg1NzEaMBgGCCqF
AwOBAwEBEgwwMDc3MjcwMzIzODIxKDAmBgNVBAkMH9Cd0L7QstC+0YHQu9C+0LHQvtC00YHQutCw
0Y8gMzcxHjAcBgkqhkiG9w0BCQEWD3VjZm9tc0BmZm9tcy5ydTELMAkGA1UEBhMCUlUxFTATBgNV
BAgMDNCc0L7RgdC60LLQsDEVMBMGA1UEBwwM0JzQvtGB0LrQstCwMS8wLQYDVQQKDCbQpNC10LTQ
tdGA0LDQu9GM0L3Ri9C5INGE0L7QvdC0INCe0JzQoTE0MDIGA1UECwwr0JrQvtGA0L3QtdCy0L7Q
uSDQo9CmINGB0LjRgdGC0LXQvNGLINCe0JzQoTEnMCUGA1UEAwwe0JrQvtGA0L3QtdCy0L7QuSDQ
o9CmINCk0J7QnNChMB4XDTEzMTAxNzExMDAxOVoXDTE4MTAxNzExMDk1NFowggFLMRgwFgYFKoUD
ZAESDTEwMjc3Mzk3MTI4NTcxGjAYBggqhQMDgQMBARIMMDA3NzI3MDMyMzgyMSgwJgYDVQQJDB/Q
ndC+0LLQvtGB0LvQvtCx0L7QtNGB0LrQsNGPIDM3MR4wHAYJKoZIhvcNAQkBFg91Y2ZvbXNAZmZv
bXMucnUxCzAJBgNVBAYTAlJVMRUwEwYDVQQIDAzQnNC+0YHQutCy0LAxFTATBgNVBAcMDNCc0L7R
gdC60LLQsDEvMC0GA1UECgwm0KTQtdC00LXRgNCw0LvRjNC90YvQuSDRhNC+0L3QtCDQntCc0KEx
NDAyBgNVBAsMK9Ca0L7RgNC90LXQstC+0Lkg0KPQpiDRgdC40YHRgtC10LzRiyDQntCc0KExJzAl
BgNVBAMMHtCa0L7RgNC90LXQstC+0Lkg0KPQpiDQpNCe0JzQoTBjMBwGBiqFAwICEzASBgcqhQMC
AiMBBgcqhQMCAh4BA0MABEAFkEQFCaJdiBqBllRwBHIz45pNRkEoJ4Aqq3zkRikt+obJPHy9C5zO
y3qnEBpUsNQMbXj62qLVO+Vcpvg9Nvsuo4IB6TCCAeUwNgYFKoUDZG8ELQwrItCa0YDQuNC/0YLQ
vtCf0YDQviBDU1AiICjQstC10YDRgdC40Y8gMy42KTCCATMGBSqFA2RwBIIBKDCCASQMKyLQmtGA
0LjQv9GC0L7Qn9GA0L4gQ1NQIiAo0LLQtdGA0YHQuNGPIDMuNikMUyLQo9C00L7RgdGC0L7QstC1
0YDRj9GO0YnQuNC5INGG0LXQvdGC0YAgItCa0YDQuNC/0YLQvtCf0YDQviDQo9CmIiDQstC10YDR
gdC40LggMS41DE/QodC10YDRgtC40YTQuNC60LDRgiDRgdC+0L7RgtCy0LXRgtGB0YLQstC40Y8g
4oSWINCh0KQvMTIxLTE4NTkg0L7RgiAxNy4wNi4yMDEyDE/QodC10YDRgtC40YTQuNC60LDRgiDR
gdC+0L7RgtCy0LXRgtGB0YLQstC40Y8g4oSWINCh0KQvMTI4LTE4MjIg0L7RgiAwMS4wNi4yMDEy
MAsGA1UdDwQEAwIBhjAPBgNVHRMBAf8EBTADAQH/MB0GA1UdDgQWBBSme4NErHVD5Y8g22vdTR7n
TmLZ1TAQBgkrBgEEAYI3FQEEAwIBADAlBgNVHSAEHjAcMAgGBiqFA2RxATAIBgYqhQNkcQIwBgYE
VR0gADAIBgYqhQMCAgMDQQDR5yrHTDSFOxra21wZaLgsweGk1g0k2PVV1MxKFzCjPvtcyv8auHWM
UgZ6zBeYwwxcmq622s3AVqj2wnXH6LVJMIIILTCCB9ygAwIBAgIKYaRiywABAAAADTAIBgYqhQMC
AgMwggFLMRgwFgYFKoUDZAESDTEwMjc3Mzk3MTI4NTcxGjAYBggqhQMDgQMBARIMMDA3NzI3MDMy
MzgyMSgwJgYDVQQJDB/QndC+0LLQvtGB0LvQvtCx0L7QtNGB0LrQsNGPIDM3MR4wHAYJKoZIhvcN
AQkBFg91Y2ZvbXNAZmZvbXMucnUxCzAJBgNVBAYTAlJVMRUwEwYDVQQIDAzQnNC+0YHQutCy0LAx
FTATBgNVBAcMDNCc0L7RgdC60LLQsDEvMC0GA1UECgwm0KTQtdC00LXRgNCw0LvRjNC90YvQuSDR
hNC+0L3QtCDQntCc0KExNDAyBgNVBAsMK9Ca0L7RgNC90LXQstC+0Lkg0KPQpiDRgdC40YHRgtC1
0LzRiyDQntCc0KExJzAlBgNVBAMMHtCa0L7RgNC90LXQstC+0Lkg0KPQpiDQpNCe0JzQoTAeFw0x
NDA1MDcxMzAzMDBaFw0xOTA1MDcxMjIwMDBaMIIBOTEYMBYGBSqFA2QBEg0xMDI3NzM5NzEyODU3
MRowGAYIKoUDA4EDAQESDDAwNzcyNzAzMjM4MjEoMCYGA1UECQwf0J3QvtCy0L7RgdC70L7QsdC+
0LTRgdC60LDRjyAzNzEeMBwGCSqGSIb3DQEJARYPdWNmb21zQGZmb21zLnJ1MQswCQYDVQQGEwJS
VTEVMBMGA1UECAwM0JzQvtGB0LrQstCwMRUwEwYDVQQHDAzQnNC+0YHQutCy0LAxLzAtBgNVBAoM
JtCk0LXQtNC10YDQsNC70YzQvdGL0Lkg0YTQvtC90LQg0J7QnNChMTowOAYDVQQLDDHQn9C+0LTR
h9C40L3QtdC90L3Ri9C5INCj0KYg0YHQuNGB0YLQtdC80Ysg0J7QnNChMQ8wDQYDVQQDEwZTVUJD
QVEwYzAcBgYqhQMCAhMwEgYHKoUDAgIjAQYHKoUDAgIeAQNDAARAX59o2Mj/QKaCHx55KQ3VSxBb
P/czkUgdBj7s6OiAlPbWiNqbINwfaPHneII5CTSC6PUULUd6UYeV7CtNZQ5gdKOCBKwwggSoMDYG
BSqFA2RvBC0MKyLQmtGA0LjQv9GC0L7Qn9GA0L4gQ1NQIiAo0LLQtdGA0YHQuNGPIDMuNikwgfQG
BSqFA2RwBIHqMIHnDCsi0JrRgNC40L/RgtC+0J/RgNC+IENTUCIgKNCy0LXRgNGB0LjRjyAzLjYp
DGQi0KPQtNC+0YHRgtC+0LLQtdGA0Y/RjtGJ0LjQuSDRhtC10L3RgtGAICLQmtGA0LjQv9GC0L7Q
n9GA0L4g0KPQpiIg0LLQtdGA0YHQuNC4IDEuNSDQutC70LDRgdGBINCa0KEyDCnQodCkLzEyNC0y
MDgzINC+0YIgMjAg0LzQsNGA0YLQsCAyMDEzINCzLgwn0KHQpC8xMjgtMTgyMiDQvtGCIDAxINC4
0Y7RgNGPIDIwMTIg0LMuMBIGCSsGAQQBgjcVAQQFAgMBAAEwHQYDVR0OBBYEFHy2RqGv2bL0Nd6m
QhNM+YciUrbBMCUGA1UdIAQeMBwwCAYGKoUDZHEBMAgGBiqFA2RxAjAGBgRVHSAAMBQGCSsGAQQB
gjcUAgQHFgVTdWJDQTALBgNVHQ8EBAMCAYYwDwYDVR0TAQH/BAUwAwEB/zCCAYwGA1UdIwSCAYMw
ggF/gBSme4NErHVD5Y8g22vdTR7nTmLZ1aGCAVOkggFPMIIBSzEYMBYGBSqFA2QBEg0xMDI3NzM5
NzEyODU3MRowGAYIKoUDA4EDAQESDDAwNzcyNzAzMjM4MjEoMCYGA1UECQwf0J3QvtCy0L7RgdC7
0L7QsdC+0LTRgdC60LDRjyAzNzEeMBwGCSqGSIb3DQEJARYPdWNmb21zQGZmb21zLnJ1MQswCQYD
VQQGEwJSVTEVMBMGA1UECAwM0JzQvtGB0LrQstCwMRUwEwYDVQQHDAzQnNC+0YHQutCy0LAxLzAt
BgNVBAoMJtCk0LXQtNC10YDQsNC70YzQvdGL0Lkg0YTQvtC90LQg0J7QnNChMTQwMgYDVQQLDCvQ
mtC+0YDQvdC10LLQvtC5INCj0KYg0YHQuNGB0YLQtdC80Ysg0J7QnNChMScwJQYDVQQDDB7QmtC+
0YDQvdC10LLQvtC5INCj0KYg0KTQntCc0KGCEAUlWzO07juIRzZKKVNuQy8wgYMGA1UdHwR8MHow
eKB2oHSGcmZpbGU6Ly9XSU4tQkQ0NEo5UEUyN0cvY2VydGVucm9sbC8hMDQxYSEwNDNlITA0NDAh
MDQzZCEwNDM1ITA0MzIhMDQzZSEwNDM5JTIwITA0MjMhMDQyNiUyMCEwNDI0ITA0MWUhMDQxYyEw
NDIxLmNybDCBpQYIKwYBBQUHAQEEgZgwgZUwgZIGCCsGAQUFBzAChoGFZmlsZTovL1dJTi1CRDQ0
SjlQRTI3Ry9jZXJ0ZW5yb2xsL1dJTi1CRDQ0SjlQRTI3R18hMDQxYSEwNDNlITA0NDAhMDQzZCEw
NDM1ITA0MzIhMDQzZSEwNDM5JTIwITA0MjMhMDQyNiUyMCEwNDI0ITA0MWUhMDQxYyEwNDIxKDEp
LmNydDArBgNVHRAEJDAigA8yMDE0MDUwNzEzMDMwMFqBDzIwMTkwNTA3MTMwMzAwWjAIBgYqhQMC
AgMDQQAIJMIaqq4RYHICvCgZX1aAMztN8r+96wnhQs1GxTuzdfqqSgvyCc3M2sAyG4HFMstICevi
85K8CgyshDyBktxSMIIKLzCCCd6gAwIBAgIKavec3AABAAALCDAIBgYqhQMCAgMwggE5MRgwFgYF
KoUDZAESDTEwMjc3Mzk3MTI4NTcxGjAYBggqhQMDgQMBARIMMDA3NzI3MDMyMzgyMSgwJgYDVQQJ
DB/QndC+0LLQvtGB0LvQvtCx0L7QtNGB0LrQsNGPIDM3MR4wHAYJKoZIhvcNAQkBFg91Y2ZvbXNA
ZmZvbXMucnUxCzAJBgNVBAYTAlJVMRUwEwYDVQQIDAzQnNC+0YHQutCy0LAxFTATBgNVBAcMDNCc
0L7RgdC60LLQsDEvMC0GA1UECgwm0KTQtdC00LXRgNCw0LvRjNC90YvQuSDRhNC+0L3QtCDQntCc
0KExOjA4BgNVBAsMMdCf0L7QtNGH0LjQvdC10L3QvdGL0Lkg0KPQpiDRgdC40YHRgtC10LzRiyDQ
ntCc0KExDzANBgNVBAMTBlNVQkNBUTAeFw0xNTAxMjAwNzA0MDBaFw0xNjA0MjAwNzE0MDBaMIIB
yTEWMBQGBSqFA2QDEgswMzg4MzI2MzY4MDEYMBYGBSqFA2QBEg0xMDI1OTAwNTIwMjI3MRowGAYI
KoUDA4EDAQESDDAwNTkwMjI5MTE2MzEkMCIGCSqGSIb3DQEJARYVcGVybWdzc21wQGxwdS5wZXJt
LnJ1MQswCQYDVQQGEwJSVTEjMCEGA1UECB4aBB8ENQRABDwEQQQ6BDgEOQAgBDoEQAQwBDkxEzAR
BgNVBAceCgQfBDUEQAQ8BEwxgYYwgYMGA1UECh58BBMEEQQjBBcAIAQfBBoAIACrBB8ENQRABDwE
QQQ6BDAETwAgBDMEPgRABD4ENARBBDoEMARPACAEQQRCBDAEPQRGBDgETwAgBEEEOgQ+BEAEPgQ5
ACAEPAQ1BDQEOARGBDgEPQRBBDoEPgQ5ACAEPwQ+BDwEPgRJBDgAuzEjMCEGA1UECx4aBBAENAQ8
BDgEPQQ4BEEEQgRABDAERgQ4BE8xOzA5BgNVBAMeMgQaBDAEPAQ6BDgEPQAgBBUEMgQzBDUEPQQ4
BDkAIAQSBDAEOwQ1BEAETAQ1BDIEOARHMSEwHwYDVQQMHhgEEwQ7BDAEMgQ9BEsEOQAgBDIEQAQw
BEcwYzAcBgYqhQMCAhMwEgYHKoUDAgIkAAYHKoUDAgIeAQNDAARADSK/zCEG4tLJ6tkxM6yHcMkd
aGlwP9bM8ZS2F7RKhshJR7KxwBtJKD0YlI2ApWa7XfkTjP9PUHN3BNpAH6Y2P6OCBjAwggYsMA4G
A1UdDwEB/wQEAwIE8DAZBgkqhkiG9w0BCQ8EDDAKMAgGBiqFAwICFTA+BgNVHSUENzA1BgYqhQNk
cQEGBiqFA2RxAgYGKoUDZHICBgcqhQMCAiIGBggrBgEFBQcDAgYIKwYBBQUHAwQwHQYDVR0OBBYE
FAsLt+x7p7z8886yLapLPYEA0XVuMIIBhgYDVR0jBIIBfTCCAXmAFHy2RqGv2bL0Nd6mQhNM+Yci
UrbBoYIBU6SCAU8wggFLMRgwFgYFKoUDZAESDTEwMjc3Mzk3MTI4NTcxGjAYBggqhQMDgQMBARIM
MDA3NzI3MDMyMzgyMSgwJgYDVQQJDB/QndC+0LLQvtGB0LvQvtCx0L7QtNGB0LrQsNGPIDM3MR4w
HAYJKoZIhvcNAQkBFg91Y2ZvbXNAZmZvbXMucnUxCzAJBgNVBAYTAlJVMRUwEwYDVQQIDAzQnNC+
0YHQutCy0LAxFTATBgNVBAcMDNCc0L7RgdC60LLQsDEvMC0GA1UECgwm0KTQtdC00LXRgNCw0LvR
jNC90YvQuSDRhNC+0L3QtCDQntCc0KExNDAyBgNVBAsMK9Ca0L7RgNC90LXQstC+0Lkg0KPQpiDR
gdC40YHRgtC10LzRiyDQntCc0KExJzAlBgNVBAMMHtCa0L7RgNC90LXQstC+0Lkg0KPQpiDQpNCe
0JzQoYIKYaRiywABAAAADTCCAlwGA1UdHwSCAlMwggJPMEegRaBDhkFodHRwOi8vc3ViY2FxL2Nh
L2NkcC8yY2U3MmU3NjQxODUyMjU0OWJmMWUxZjE4MmY3NjlmNDg1NGVlYWQ1LmNybDBHoEWgQ4ZB
aHR0cDovL3N1YmNhcS9jYS9jZHAvYTY3YjgzNDRhYzc1NDNlNThmMjBkYjZiZGQ0ZDFlZTc0ZTYy
ZDlkNS5jcmwweqB4oHaGdGh0dHA6Ly9vcmEuZmZvbXMucnUvcG9ydGFsL3BhZ2UvcG9ydGFsL3Rv
cC9hYm91dC9nZW5lcmFsL2FjdGl2aXR5L2NlbnRyL2E2N2I4MzQ0YWM3NTQzZTU4ZjIwZGI2YmRk
NGQxZWU3NGU2MmQ5ZDUuY3JsMHqgeKB2hnRodHRwOi8vb3JhLmZmb21zLnJ1L3BvcnRhbC9wYWdl
L3BvcnRhbC90b3AvYWJvdXQvZ2VuZXJhbC9hY3Rpdml0eS9jZW50ci8yY2U3MmU3NjQxODUyMjU0
OWJmMWUxZjE4MmY3NjlmNDg1NGVlYWQ1LmNybDBHoEWgQ4ZBaHR0cDovL3N1YmNhcS9jYS9jZHAv
N2NiNjQ2YTFhZmQ5YjJmNDM1ZGVhNjQyMTM0Y2Y5ODcyMjUyYjZjMS5jcmwweqB4oHaGdGh0dHA6
Ly9vcmEuZmZvbXMucnUvcG9ydGFsL3BhZ2UvcG9ydGFsL3RvcC9hYm91dC9nZW5lcmFsL2FjdGl2
aXR5L2NlbnRyLzdjYjY0NmExYWZkOWIyZjQzNWRlYTY0MjEzNGNmOTg3MjI1MmI2YzEuY3JsMEkG
CCsGAQUFBwEBBD0wOzA5BggrBgEFBQcwAoYtZmlsZTovL1NVQkNBUS9jZXJ0ZW5yb2xsL1NVQkNB
UV9TVUJDQVEoMSkuY3J0MCsGA1UdEAQkMCKADzIwMTUwMTIwMDcwNDAwWoEPMjAxNjA0MjAwNzA0
MDBaMB0GA1UdIAQWMBQwCAYGKoUDZHEBMAgGBiqFA2RxAjA0BgUqhQNkbwQrDCnQmtGA0LjQv9GC
0L7Qn9GA0L4gQ1NQICjQstC10YDRgdC40Y8gMy42KTCB6AYFKoUDZHAEgd4wgdsMKdCa0YDQuNC/
0YLQvtCf0YDQviBDU1AgKNCy0LXRgNGB0LjRjyAzLjYpDFTQo9C00L7RgdGC0L7QstC10YDRj9GO
0YnQuNC5INGG0LXQvdGC0YAgItCa0YDQuNC/0YLQvtCf0YDQviDQo9CmIiAo0LLQtdGA0YHQuNGP
IDEuNSkMK+KEliDQodCkLzEyMS0xODU3INC+0YIgMTcg0LjRjtC90Y8gMjAxMiDQsy4MK+KEliDQ
odCkLzEyOC0xODIyINC+0YIgMDEg0LjRjtC90Y8gMjAxMiDQsy4wCAYGKoUDAgIDA0EADNUPh8wO
NRyeT0ADwUcjV1UvP6NZ2JoaW4vTbCcyhbiGMF2qSdk/VObwwFb7fOJORcNvd+onvjrs3gW3fDwj
vjGCA70wggO5AgEBMIIBSTCCATkxGDAWBgUqhQNkARINMTAyNzczOTcxMjg1NzEaMBgGCCqFAwOB
AwEBEgwwMDc3MjcwMzIzODIxKDAmBgNVBAkMH9Cd0L7QstC+0YHQu9C+0LHQvtC00YHQutCw0Y8g
MzcxHjAcBgkqhkiG9w0BCQEWD3VjZm9tc0BmZm9tcy5ydTELMAkGA1UEBhMCUlUxFTATBgNVBAgM
DNCc0L7RgdC60LLQsDEVMBMGA1UEBwwM0JzQvtGB0LrQstCwMS8wLQYDVQQKDCbQpNC10LTQtdGA
0LDQu9GM0L3Ri9C5INGE0L7QvdC0INCe0JzQoTE6MDgGA1UECwwx0J/QvtC00YfQuNC90LXQvdC9
0YvQuSDQo9CmINGB0LjRgdGC0LXQvNGLINCe0JzQoTEPMA0GA1UEAxMGU1VCQ0FRAgpq95zcAAEA
AAsIMAoGBiqFAwICCQUAoIICCzAYBgkqhkiG9w0BCQMxCwYJKoZIhvcNAQcBMBwGCSqGSIb3DQEJ
BTEPFw0xNTAyMTMxNDI0NDJaMC8GCSqGSIb3DQEJBDEiBCBtsKv8aI1lmDOi12Ujp41axLxVG6T7
7R+xOV4lrNRaeTCCAZ4GCyqGSIb3DQEJEAIvMYIBjTCCAYkwggGFMIIBgTAIBgYqhQMCAgkEIAZT
A9CllYVtK24186oujAKgLDSZfUzd+ULFv2tGgcXNMIIBUTCCAUGkggE9MIIBOTEYMBYGBSqFA2QB
Eg0xMDI3NzM5NzEyODU3MRowGAYIKoUDA4EDAQESDDAwNzcyNzAzMjM4MjEoMCYGA1UECQwf0J3Q
vtCy0L7RgdC70L7QsdC+0LTRgdC60LDRjyAzNzEeMBwGCSqGSIb3DQEJARYPdWNmb21zQGZmb21z
LnJ1MQswCQYDVQQGEwJSVTEVMBMGA1UECAwM0JzQvtGB0LrQstCwMRUwEwYDVQQHDAzQnNC+0YHQ
utCy0LAxLzAtBgNVBAoMJtCk0LXQtNC10YDQsNC70YzQvdGL0Lkg0YTQvtC90LQg0J7QnNChMTow
OAYDVQQLDDHQn9C+0LTRh9C40L3QtdC90L3Ri9C5INCj0KYg0YHQuNGB0YLQtdC80Ysg0J7QnNCh
MQ8wDQYDVQQDEwZTVUJDQVECCmr3nNwAAQAACwgwCgYGKoUDAgITBQAEQEGVRVEHF3U9BtCy0WCO
/DUa1+8FpUJe/K3VIztOX8rJsTUsvlOCx4s4jtu2bB9RDQmo+o0Ltf2MfOcCwc/qBcc=';
		}
		file_put_contents($file_sign_name, '----- BEGIN PKCS7 SIGNED -----'.PHP_EOL.$data['documentSigned'].PHP_EOL.'----- END PKCS7 SIGNED -----'.PHP_EOL);

		$this->db->query("
			update
				{$this->scheme}.Registry with (rowlock)
			set
				Registry_ExportSign = :Registry_ExportSign,
				Registry_xmlExpDT = dbo.tzGetDate()
			where
				Registry_id = :Registry_id
		", array(
			'Registry_ExportSign' => $file_sign_name,
			'Registry_id' => $data['Registry_id']
		));

		// 2. меняем статус реестра
		$data['RegistryCheckStatus_SysNick'] = 'SignECP'; // Подписан ЭЦП
		return $this->setRegistryMzCheckStatus($data);
	}

	/**
	 * Проверка существования файла экспорта реестра
	 */
	function checkRegistryXmlExportExists($data)
	{
		$resp = $this->queryResult("
			select top 1
				Registry_xmlExportPath
			from
				{$this->scheme}.v_Registry (nolock)
			where
				Registry_id = :Registry_id
		", [
			'Registry_id' => $data['Registry_id']
		]);

		return ['Error_Msg' => '', 'exists' => (!empty($resp[0]['Registry_xmlExportPath']) && $resp[0]['Registry_xmlExportPath'] != '1' && file_exists($resp[0]['Registry_xmlExportPath']))];
	}

	/**
	 * Проверка вхождения случая в другой реестр при снятии с реестра отметки "Оплачен"
	 * @task https://redmine.swan-it.ru/issues/154914
	 */
	public function checkRegistryDataIsInOtherRegistry($data) {
		$response = '';

		try {
			switch ( $this->RegistryType_id ) {
				case 1:
				case 14:
					$EvnClass_SysNick_Array = array('EvnSection');
					break;

				case 2:
				case 16:
					$EvnClass_SysNick_Array = array('EvnVizit');
					break;

				case 6:
					if ( in_array($this->regionNick, array('ekb', 'kareliya', 'perm')) ) {
						$EvnClass_SysNick_Array = array('CmpCallCard');
					}
					else {
						$EvnClass_SysNick_Array = array('CmpCloseCard');
					}
					break;

				case 7:
				case 9:
				case 11:
				case 12:
					$EvnClass_SysNick_Array = array('EvnPLDisp');
					break;

				case 15:
				case 18:
				case 20:
					$EvnClass_SysNick_Array = array('EvnUsluga');
					break;

				case 19:
					$EvnClass_SysNick_Array = array('EvnPL', 'EvnSection', 'EvnUsluga');
					break;

				default:
					throw new Exception('Ошибка при определении класса событий реестра.');
					break;
			}

			foreach ( $EvnClass_SysNick_Array as $EvnClass_SysNick ) {
				$filter = "";
				$join = "";

				if ($this->regionNick == 'kareliya') {
					$field = 'rd.Evn_id';
					$filter = ' and r.RegistryStatus_id in (2,3)';
				}else{
					$field = "e.{$EvnClass_SysNick}_id";
					$filter = "
						and ISNULL(e.{$EvnClass_SysNick}_IsInReg, 1) = 2
						and rd.RegistryData_IsPaid = 1
					";
					$join = "
					inner join {$EvnClass_SysNick} e with (nolock) on e.{$EvnClass_SysNick}_id = rd.Evn_id
					";
				}
				
				if ( $this->RegistryType_id != 6 ) {
					$join .= "inner join Evn on Evn.Evn_id = {$field}";
					$filter .= "and ISNULL(Evn.Evn_deleted, 1) = 1";
				}

				$checkResult = $this->getFirstResultFromQuery("
					select top 1 r.Registry_Num
					from {$this->scheme}.v_{$this->RegistryDataObject} rd with (nolock)
						{$join}
						inner join {$this->scheme}.v_{$this->RegistryDataObject} rdo with (nolock) on rdo.Evn_id = {$field}
							and rdo.Registry_id != :Registry_id
						inner join {$this->scheme}.v_Registry r with (nolock) on r.Registry_id = rdo.Registry_id
					where rd.Registry_id = :Registry_id
						{$filter}
				", $data, true);

				if ( $checkResult === false ) {
					throw new Exception('Ошибка при определении вхождения случаев в реестр.');
				}
				else if ( !empty($checkResult) ) {
					throw new Exception('Снятие отметки «Оплачен» невозможно, так как реестр содержит случаи, включенные в другие неоплаченные реестры: ' . $checkResult . '. Для снятия отметки нужно исключить такие случаи из других неоплаченных реестров.');
				}
			}
		}
		catch ( Exception $e ) {
			$response = $e->getMessage();
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	protected function _loadRegistryDataFilter($data)
	{
		//Фильтр грида
		$json = isset($data['Filter']) ? trim($data['Filter'],'"') : false;
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false;

		if ($filter_mode['type'] != 'unicFilter' || empty($filter_mode['cell'])) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		// Подгоняем поля под запрос с WITH
		if ($filter_mode['cell'] == 'Evn_ident') {
			$field = 'RD.Evn_id';

			if ($this->regionNick == 'kareliya') {
				if ($this->RegistryType_id == 1) {
					$field = 'RD.Evn_sid';
				}
			}
		}
		else if ($filter_mode['cell'] == 'Evn_id') {
			$field = 'RD.Evn_id';
		}
		else if ($filter_mode['cell'] == 'Diag_Code') {
			$field = 'D.Diag_Code';
		}
		else if ($filter_mode['cell'] == 'EvnPL_NumCard') {
			$field = 'RD.NumCard';
		}
		else if ($filter_mode['cell'] == 'LpuSection_name' || $filter_mode['cell'] == 'LpuSection_Name') {
			$field = 'RD.LpuSection_name';
		}
		else if ($filter_mode['cell'] == 'LpuBuilding_Name') {
			$field = 'LB.LpuBuilding_Name';
		}
		else if ($filter_mode['cell'] == 'LpuSectionProfile_Name') {
			$field = 'LSP.LpuSectionProfile_Name';
		}
		else if ($filter_mode['cell'] == 'MedSpecOms_Name') {
			$field = 'msc.MedSpecClass_Name';
		}
		else if ($filter_mode['cell'] == 'Mes_Code_KSG') {
			$field = 'Mes.Mes_Code + ISNULL(\' \' + Mes.MesOld_Num, \'\')';
		}
		else if ($filter_mode['cell'] == 'Paid') {
			$field = 'RD.Paid_id';
		}
		else if ($filter_mode['cell'] == 'RegistryData_Uet') {
			$field = 'RD.RegistryData_KdFact';
		}
		else if ($filter_mode['cell'] == 'Usluga_Code') {
			if ($this->RegistryType_id != 1 && $this->RegistryType_id != 14) {
				$field = 'U.UslugaComplex_Code';
			}
			else {
				$field = 'm.Mes_Code';
			}
		}
		else if ($filter_mode['cell'] == 'VolumeType_Code') {
			$field = 'VT.VolumeType_Code';
		}
		else {
			$field = $filter_mode['cell'];
		}

		if ($filter_mode['specific'] !== false && !empty($filter_mode['value']) && $filter_mode['value'] != "_") {
			$data['ExtendedFilterField'] = $field;
			$data['ExtendedFilterFieldValue'] = trim($filter_mode['value']);
		}

		$data['returnQueryOnly'] = 1;
		$queryData = $this->loadRegistryData($data);

		$query = $queryData['query'];
		$params = $queryData['params'];

		$count = $this->getFirstResultFromQuery($this->_getCountSQLPH($query, $field, 'DISTINCT'), $params);
		$response = [];

		if (empty($count)) {
			$count = 0;
		}

		if ($count > 0) {
			$response = $this->queryResult($this->_getLimitSQLPH($query, 'DISTINCT', $field, $data['start'], $data['limit'], null, $field), $params);

			if ($response === false) {
				$response = [];
			}
		}

		return [
			'data' => $response,
			'totalCount' => $count,
		];
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	protected function _loadRegistryErrorFilter($data)
	{
		//Фильтр грида
		$json = isset($data['Filter']) ? trim($data['Filter'],'"') : false;
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false;

		if ($filter_mode['type'] != 'unicFilter' || empty($filter_mode['cell'])) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		// Подгоняем поля под запрос с WITH
		if ($filter_mode['cell'] == 'Evn_ident') {
			$field = 'RE.Evn_id';

			if ($this->regionNick == 'kareliya') {
				if ($this->RegistryType_id == 1) {
					$field = 'RE.Evn_sid';
				}
			}
		}
		elseif ($filter_mode['cell'] == 'Evn_id') {
			$field = 'RE.Evn_id';
		}
		elseif ($filter_mode['cell'] == 'RegistryErrorType_Code') {
			$field = 'RE.RegistryErrorType_Code';
		}
		else if ($filter_mode['cell'] == 'Person_FIO') {
			$field = 'RE.Person_FIO';
		}
		else if ($filter_mode['cell'] == 'MedSpecOms_Name') {
			$field = 'MSO.MedSpecOms_Name';
		}
		else if ($filter_mode['cell'] == 'LpuBuilding_Name') {
			$field = 'LB.LpuBuilding_Name';
		}
		elseif ($filter_mode['cell'] == 'LpuSection_name' || $filter_mode['cell'] == 'LpuSection_Name') {
			$field = 'RE.LpuSection_name';
		}
		elseif ($filter_mode['cell'] == 'LpuSectionProfile_Name') {
			$field = 'LSP.LpuSectionProfile_Name';
		}
		else if ($filter_mode['cell'] == 'Usluga_Code') {
			if ($this->RegistryType_id != 1 && $this->RegistryType_id != 14) {
				$field = 'U.UslugaComplex_Code';
			}
			else {
				$field = 'm.Mes_Code';
			}
		}
		else {
			$field = $filter_mode['cell'];
		}

		if ($filter_mode['specific'] !== false && !empty($filter_mode['value']) && $filter_mode['value'] != "_") {
			$data['ExtendedFilterField'] = $field;
			$data['ExtendedFilterFieldValue'] = trim($filter_mode['value']);
		}

		$data['returnQueryOnly'] = 1;
		$queryData = $this->loadRegistryError($data);

		$query = $queryData['query'];
		$params = $queryData['params'];

		$count = $this->getFirstResultFromQuery($this->_getCountSQLPH($query, $field, 'DISTINCT'), $params);
		$response = [];

		if (empty($count)) {
			$count = 0;
		}

		if ($count > 0) {
			$response = $this->queryResult($this->_getLimitSQLPH($query, 'DISTINCT', $field, $data['start'], $data['limit'], null, $field), $params);

			if ($response === false) {
				$response = [];
			}
		}

		return [
			'data' => $response,
			'totalCount' => $count,
		];
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public function loadRegistryErrorComFilter($data)
	{
		//Фильтр грида
		$json = isset($data['Filter']) ? trim($data['Filter'],'"') : false;
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false;

		if ($filter_mode['type'] != 'unicFilter' || empty($filter_mode['cell'])) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		// Подгоняем поля под запрос с WITH
		if ($filter_mode['cell'] == 'Evn_ident') {
			$field = 'RE.Evn_id';

			if ($this->regionNick == 'kareliya') {
				if ($this->RegistryType_id == 1) {
					$field = 'RE.Evn_sid';
				}
			}
		}
		elseif ($filter_mode['cell'] == 'Evn_id') {
			$field = 'RE.Evn_id';
		}
		elseif ($filter_mode['cell'] == 'RegistryErrorType_Code') {
			$field = 'RE.RegistryErrorType_Code';
		}
		else if ($filter_mode['cell'] == 'Person_FIO') {
			$field = 'RE.Person_FIO';
		}
		elseif ($filter_mode['cell'] == 'LpuSection_name' || $filter_mode['cell'] == 'LpuSection_Name') {
			$field = 'RE.LpuSection_name';
		}
		else {
			$field = $filter_mode['cell'];
		}

		if ($filter_mode['specific'] !== false && !empty($filter_mode['value']) && $filter_mode['value'] != "_") {
			$data['ExtendedFilterField'] = $field;
			$data['ExtendedFilterFieldValue'] = trim($filter_mode['value']);
		}

		$data['returnQueryOnly'] = 1;
		$queryData = $this->loadRegistryErrorCom($data);

		$query = $queryData['query'];
		$params = $queryData['params'];

		$count = $this->getFirstResultFromQuery($this->_getCountSQLPH($query, $field, 'DISTINCT'), $params);
		$response = [];

		if (empty($count)) {
			$count = 0;
		}

		if ($count > 0) {
			$response = $this->queryResult($this->_getLimitSQLPH($query, 'DISTINCT', $field, $data['start'], $data['limit'], null, $field), $params);

			if ($response === false) {
				$response = [];
			}
		}

		return [
			'data' => $response,
			'totalCount' => $count,
		];
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	protected function _loadRegistryNoPolisFilter($data)
	{
		//Фильтр грида
		$json = isset($data['Filter']) ? trim($data['Filter'],'"') : false;
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false;

		if ($filter_mode['type'] != 'unicFilter' || empty($filter_mode['cell'])) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		// Подгоняем поля под запрос с WITH
		if ($filter_mode['cell'] == 'Evn_ident') {
			$field = 'RNP.Evn_id';

			if ($this->regionNick == 'kareliya') {
				if ($this->RegistryType_id == 1) {
					$field = 'RNP.Evn_sid';
				}
				else if ($this->RegistryType_id == 14) {
					$field = 'RNP.Evn_rid';
				}
			}
		}
		else if ($filter_mode['cell'] == 'Evn_id') {
			$field = "RNP.Evn_id";
		}
		else if ($filter_mode['cell'] == 'Person_FIO') {
			$field = "rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, ''))";
		}
		else if ($filter_mode['cell'] == 'LpuSection_name' || $filter_mode['cell'] == 'LpuSection_Name') {
			$field = "(rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name)";
		}
		else {
			$field = $filter_mode['cell'];
		}

		if ($filter_mode['specific'] !== false && !empty($filter_mode['value']) && $filter_mode['value'] != "_") {
			$data['ExtendedFilterField'] = $field;
			$data['ExtendedFilterFieldValue'] = trim($filter_mode['value']);
		}

		$data['returnQueryOnly'] = 1;
		$queryData = $this->loadRegistryNoPolis($data);

		$query = $queryData['query'];
		$params = $queryData['params'];

		$count = $this->getFirstResultFromQuery($this->_getCountSQLPH($query, $field, 'DISTINCT'), $params);
		$response = [];

		if (empty($count)) {
			$count = 0;
		}

		if ($count > 0) {
			$response = $this->queryResult($this->_getLimitSQLPH($query, 'DISTINCT', $field, $data['start'], $data['limit'], null, $field), $params);

			if ($response === false) {
				$response = [];
			}
		}

		return [
			'data' => $response,
			'totalCount' => $count,
		];
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	protected function _loadRegistryErrorTFOMSFilter($data)
	{
		//Фильтр грида
		$json = isset($data['Filter']) ? trim($data['Filter'],'"') : false;
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false;

		if ($filter_mode['type'] != 'unicFilter' || empty($filter_mode['cell'])) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		// Подгоняем поля под запрос с WITH
		if ($filter_mode['cell'] == 'Evn_ident') {
			$field = 'RE.Evn_id';

			if ($this->regionNick == 'kareliya') {
				if ($this->RegistryType_id == 1) {
					$field = 'RE.Evn_sid';
				}
				else if ($this->RegistryType_id == 14) {
					$field = 'ES2.EvnSection_pid';
				}
			}
		}
		else if ($filter_mode['cell'] == 'Evn_id') {
			$field = 'RE.Evn_id';
		}
		else if ($filter_mode['cell'] == 'Person_id') {
			$field = 'ps.Person_id';
		}
		else if ($filter_mode['cell'] == 'RegistryErrorType_Code') {
			$field = 'ret.RegistryErrorType_Code';
		}
		else if ($filter_mode['cell'] == 'RegistryErrorTFOMS_Comment') {
			$field = 'RE.RegistryErrorTFOMS_Comment';
		}
		else if ($filter_mode['cell'] == 'Person_FIO') {
			$field = "rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))";
		}
		else if ($filter_mode['cell'] == 'LpuSection_name' || $filter_mode['cell'] == 'LpuSection_Name') {
			$field = 'LS.LpuSection_Name';
		}
		else if ($filter_mode['cell'] == 'LpuBuilding_Name') {
			$field = 'lb.LpuBuilding_Name';
		}
		else if ($filter_mode['cell'] == 'LpuSectionProfile_Name') {
			$field = 'lsp.LpuSectionProfile_Name';
		}
		else if ($filter_mode['cell'] == 'MedSpecOms_Name') {
			$field = 'msc.MedSpecClass_Name';
		}
		else {
			$field = $filter_mode['cell'];
		}

		if ($filter_mode['specific'] !== false && !empty($filter_mode['value']) && $filter_mode['value'] != "_") {
			$data['ExtendedFilterField'] = $field;
			$data['ExtendedFilterFieldValue'] = trim($filter_mode['value']);
		}

		$data['returnQueryOnly'] = 1;
		$queryData = $this->loadRegistryErrorTFOMS($data);

		$query = $queryData['query'];
		$params = $queryData['params'];

		$count = $this->getFirstResultFromQuery($this->_getCountSQLPH($query, $field, 'DISTINCT'), $params);
		$response = [];

		if (empty($count)) {
			$count = 0;
		}

		if ($count > 0) {
			$response = $this->queryResult($this->_getLimitSQLPH($query, 'DISTINCT', $field, $data['start'], $data['limit'], null, $field), $params);

			if ($response === false) {
				$response = [];
			}
		}

		return [
			'data' => $response,
			'totalCount' => $count,
		];
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public function loadRegistryDoubleFilter($data)
	{
		//Фильтр грида
		$json = isset($data['Filter']) ? trim($data['Filter'],'"') : false;
		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false;

		if ($filter_mode['type'] != 'unicFilter' || empty($filter_mode['cell'])) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		// Подгоняем поля под запрос с WITH
		if ($filter_mode['cell'] == 'Evn_ident') {
			$field = 'RD.Evn_id';
		}
		else if ($filter_mode['cell'] == 'Evn_id') {
			$field = 'RD.Evn_id';
		}
		else if ($filter_mode['cell'] == 'EvnPL_NumCard') {
			$field = 'EPL.EvnPL_NumCard';
		}
		else if ($filter_mode['cell'] == 'Person_id') {
			$field = 'RD.Person_id';
		}
		else if ($filter_mode['cell'] == 'Person_FIO') {
			$field = "rtrim(IsNull(RD.Person_SurName,'')) + ' ' + rtrim(IsNull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, ''))";
		}
		else if ($filter_mode['cell'] == 'LpuSection_name' || $filter_mode['cell'] == 'LpuSection_Name') {
			$field = 'LS.LpuSection_Name';
		}
		else if ($filter_mode['cell'] == 'LpuBuilding_Name') {
			$field = 'LB.LpuBuilding_Name';
		}
		else if ($filter_mode['cell'] == 'LpuSectionProfile_Name') {
			$field = 'LSP.LpuSectionProfile_Name';
		}
		else if ($filter_mode['cell'] == 'MedPersonal_Fio') {
			$field = 'MP.Person_Fio';
		}
		else if ($filter_mode['cell'] == 'MedSpecOms_Name') {
			$field = 'MSO.MedSpecOms_Name';
		}
		else {
			$field = $filter_mode['cell'];
		}

		if ($filter_mode['specific'] !== false && !empty($filter_mode['value']) && $filter_mode['value'] != "_") {
			$data['ExtendedFilterField'] = $field;
			$data['ExtendedFilterFieldValue'] = trim($filter_mode['value']);
		}

		$data['returnQueryOnly'] = 1;
		$queryData = $this->loadRegistryDouble($data);

		$query = $queryData['query'];
		$params = $queryData['params'];

		$count = $this->getFirstResultFromQuery($this->_getCountSQLPH($query, $field, 'DISTINCT'), $params);
		$response = [];

		if (empty($count)) {
			$count = 0;
		}

		if ($count > 0) {
			$response = $this->queryResult($this->_getLimitSQLPH($query, 'DISTINCT', $field, $data['start'], $data['limit'], null, $field), $params);

			if ($response === false) {
				$response = [];
			}
		}

		return [
			'data' => $response,
			'totalCount' => $count,
		];
	}
}