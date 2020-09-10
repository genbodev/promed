<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property Morbus_model Morbus
 * @property MorbusOnkoSpecifics_model MorbusOnkoSpecifics
 * @property EvnDiagPLStom_model $EvnDiagPLStom_model
 * @property EvnVizitPL_model $EvnVizitPL_model
 * @property Kz_UslugaMedType_model $UslugaMedType_model
 */
class EvnVizit_model extends swPgModel {
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 *	Удаление посещения, должа использоваться только из реестров
	 */
	function deleteEvnVizitPL($data, $ignoreRegistryCheck = false) {
		//нельзя удалить подписанное посещение (кроме случая когда удаляем вместе с ним дубли посещений)
		$deletable = isset($data['RegistryDouble']) && $data['RegistryDouble'] === true;
		$query = "
			select
				EvnVizitPL_id as \"EvnVizitPL_id\"
			from
				v_EvnVizitPL
			where
				EvnVizitPL_id = :EvnVizitPL_id
				and EvnVizitPL_IsSigned = 2
		";
		$result = $this->db->query($query, array('EvnVizitPL_id' => $data['EvnVizitPL_id']));
		if ( is_object($result) ) {
			$response = $result->result('array');
			if ( is_array($response) && count($response) > 0 && !$deletable )
				return array(array('Error_Msg' => 'Удаление посещения пациентом поликлиники невозможно, т.к. посещение подписано'));
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка подписания документов)'));
		}

		// получаем ТАП
		$EvnPL_id = null;
		$EvnVizitPL_Count = 0;
		$query = "
			select
				evpl.EvnVizitPL_pid as \"EvnPL_id\",
				evplcount.cnt as \"EvnVizitPL_Count\"
			from
				v_EvnVizitPL evpl
				left join lateral(
					select
						count(evpl2.EvnVizitPL_id) as cnt
					from
						v_EvnVizitPL evpl2
					where
						evpl2.EvnVizitPL_pid = evpl.EvnVizitPL_pid
				) evplcount on true
			where
				evpl.EvnVizitPL_id = :EvnVizitPL_id
		";
		$result = $this->db->query($query, array('EvnVizitPL_id' => $data['EvnVizitPL_id']));
		if ( is_object($result) ) {
			$response = $result->result('array');
			if (!empty($response[0]['EvnPL_id'])) {
				$EvnPL_id = $response[0]['EvnPL_id'];
				$EvnVizitPL_Count = $response[0]['EvnVizitPL_Count'];
			}
		}

		$TimetableGraf_id = 0;
		$is_recorded = null;
		$EvnVizit_id = 0;
		$vizit_object = 'EvnVizitPL';
		//получить данные бирки по этому посещению
		$query = "
			(select
				TimetableGraf.TimetableGraf_id as \"TimetableGraf_id\",
				TimetableGraf.TimetableGraf_begTime as \"TimetableGraf_begTime\",
				EvnVizit.EvnVizit_id as \"EvnVizit_id\"
			from
				v_TimetableGraf_lite TimetableGraf
				inner join v_{$vizit_object} on v_{$vizit_object}.{$vizit_object}_id = :{$vizit_object}_id
				inner join v_EvnVizit on {$vizit_object}.{$vizit_object}_id = EvnVizit.EvnVizit_id
					AND TimetableGraf.TimetableGraf_id = EvnVizit.TimetableGraf_id
			limit 1
			)
			union all
			
			-- бирки без записи, которые могли остаться без связи по EvnVizit.TimetableGraf_id по какой то причине
			select
				TimetableGraf.TimetableGraf_id as \"TimetableGraf_id\",
				TimetableGraf.TimetableGraf_begTime as \"TimetableGraf_begTime\",
				TimetableGraf.Evn_id as \"EvnVizit_id\"
			from
				v_TimetableGraf_lite TimetableGraf
			where
				TimetableGraf.Evn_id = :{$vizit_object}_id
				and TimetableGraf_begTime is null
		";
		$params = array(
			$vizit_object.'_id' => $data[$vizit_object.'_id']
		);
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			$response = $result->result('array');
			if (count($response) > 0 AND !empty($response[0]['TimetableGraf_id']) AND !empty($response[0]['EvnVizit_id']))
			{
				$TimetableGraf_id = $response[0]['TimetableGraf_id'];
				$EvnVizit_id = $response[0]['EvnVizit_id'];
				$is_recorded = !empty($response[0]['TimetableGraf_begTime']);
			}
		}
		else
		{
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение данных о записи пациента на посещение поликлиники)'));
		}

		//в [p_EvnVizitPL_setdel] чистится TimetableGraf_id
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_EvnVizitPL_del(
				EvnVizitPL_id := :EvnVizitPL_id,
				pmUser_id := :pmUser_id
			)	
		";
		$result = $this->db->query($query, array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) )
		{
			$response = $result->result('array');
		}
		else
		{
			$response = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление посещения пациентом поликлиники)'));
		}
		//выход, если есть ошибка при удалении посещения
		if (!empty($response[0]['Error_Msg']))
		{
			return $response;
		}

		// после удаления посещения удаляём и ТАП, если было 1 посещение.
		if (!empty($EvnPL_id) && $EvnVizitPL_Count < 2) {
			$query = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_EvnPL_del(
					EvnPL_id := :EvnPL_id,
					pmUser_id := :pmUser_id
				)	
			";
			$result = $this->db->query($query, array(
				'EvnPL_id' => $EvnPL_id,
				'pmUser_id' => $data['pmUser_id']
			));

			if ( is_object($result) )
			{
				$response = $result->result('array');
			}
			else
			{
				$response = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление тап)'));
			}
			//выход, если есть ошибка при удалении ТАП
			if (!empty($response[0]['Error_Msg']))
			{
				return $response;
			}
		}

		// После удаления посещения нужно почистить TimetableGraf_factTime, если человек посещал по записи, чтобы на эту бирку можно было завести другое посещение.
		if ($is_recorded === true AND $TimetableGraf_id > 0)
		{
			$query = "
				UPDATE TimetableGraf  set
				TimetableGraf_factTime = NULL
				where TimetableGraf_id = :TimetableGraf_id
			";
			$result = $this->db->query($query, array(
				'TimetableGraf_id' => $TimetableGraf_id
			));
			if ( $result == true )
			{
				$response = array(array('Error_Msg' => ''));
			}
			else
			{
				$response = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (очистка времени фактического посещения)'));
			}
		}

		// После удаления посещения удалять бирку, если она создана на человека без записи.
		if ($is_recorded === false AND $TimetableGraf_id > 0)
		{
			$query = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_TimetableGraf_del(
					TimetableGraf_id := :TimetableGraf_id,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, array(
				'TimetableGraf_id' => $TimetableGraf_id,
				'pmUser_id' => $data['pmUser_id']
			));
			if ( is_object($result) )
			{
				$response = $result->result('array');
			}
			else
			{
				$response = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление записи о посещении пациентом поликлиники без записи)'));
			}
		}

		if (getRegionNick() == 'perm' && !empty($EvnPL_id)) {
			$this->load->model('EvnPL_model');
			$this->EvnPL_model->checkEvnPLCrossed(array(
				'EvnPL_id' => $EvnPL_id
			));
		}

		return $response;
	}

	/**
	 *	Получение данных посещения для ЭМК
	 */
function getEvnVizitPLViewData($data) {
		$filter = "1=1";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		if (isset($data['EvnVizitPL_pid']))
		{
			$filter .= ' and EvnVizit.EvnVizitPL_pid = :EvnVizitPL_pid';
			$queryParams['EvnVizitPL_pid'] = $data['EvnVizitPL_pid'];
		}
		else
		{
			$filter .= ' and EvnVizit.EvnVizitPL_id = :EvnVizitPL_id';
			$queryParams['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and ( $diagFilter )";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnVizit.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LpuUnit.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}
		$accessType = 'EvnVizit.Lpu_id = :Lpu_id';
		// and coalesce(EvnVizit.EvnVizitPL_IsInReg, 1) = 1 AND coalesce(EvnVizit.EvnVizitPL_IsSigned,1) != 2
		$add_join = '';
		$add_select = '';
		/*if ( $data['session']['region']['nick'] != 'ufa' )
		{
        //Везде кроме Уфы закрыта возможность редактировать посещение закрытого случая АПЛ refs #5033
        $accessType .= ' AND coalesce(EvnPL.EvnPL_IsFinish,1) != 2';
        $add_join = 'left join v_EvnPL EvnPL on EvnVizit.EvnVizitPL_pid = EvnPL.EvnPL_id';
		}*/

		if ( 'ekb' == $this->regionNick ) {
			$add_join .= "
				left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = EvnVizit.LpuSectionProfile_id
			";
		} else {
			$add_join .= "
				left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = coalesce(EvnVizit.LpuSectionProfile_id,LpuSection.LpuSectionProfile_id)
			";
		}
		
		if(getRegionNick() == 'kz') {
			$add_select .= "
				,gbel.PayTypeKAZ_id as \"PayTypeKAZ_id\"
				,ptkz.PayTypeKAZ_Name as \"PayTypeKAZ_Name\"
				,gbel.ScreenType_id as \"ScreenType_id\"
				,st.ScreenType_Name as \"ScreenType_Name\"
			";
			$add_join .= "
				left join r101.EvnLinkAPP gbel on gbel.Evn_id = EvnVizit.EvnVizitPL_id
				left join r101.PayTypeKAZ ptkz on ptkz.PayTypeKAZ_id = gbel.PayTypeKAZ_id
				left join r101.ScreenType st on st.ScreenType_id = gbel.ScreenType_id
			";
		}

		if (isset($data['user_MedStaffFact_id']))
		{
			//врач может редактировать, если посещение создано в его ЛПУ, оно не оплачено, оно создано им, в его отделении и случай АПЛ не закончен
			$accessType .= ' and EvnVizit.LpuSection_id = MSF.LpuSection_id';
			$add_select .= "
				,MSF.MedSpecOms_id as \"MedSpecOms_id\",
				MSO.MedSpec_id as \"FedMedSpec_id\"
			";
			$add_join .= '
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = :user_MedStaffFact_id
				left join v_MedSpecOms MSO on MSO.MedSpecOms_id = MSF.MedSpecOms_id
			';
			$queryParams['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$add_join .= "
				left join lateral(
					select
						t1.EvnUslugaCommon_id,
						t1.UslugaComplex_id as UslugaComplex_uid
					from
						v_EvnUslugaCommon t1
					where
						t1.EvnUslugaCommon_pid = EvnVizit.EvnVizitPL_id
						and coalesce(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
					order by
						t1.EvnUslugaCommon_setDT desc
					limit 1
				) EU on true
				left join v_UslugaComplex UC on UC.UslugaComplex_id = coalesce(EU.UslugaComplex_uid, EvnVizit.UslugaComplex_id)
			";
		}

		if ( 'ekb' == $this->regionNick ) {
			$add_select .= "
				,RS.RankinScale_id as \"RankinScale_id\"
				,RS.RankinScale_Name as \"RankinScale_Name\"
			";

			$add_join .= "
				left join v_MedStaffFact VizitMSF on VizitMSF.MedStaffFact_id = EvnVizit.MedStaffFact_id
				left join v_RankinScale RS on RS.RankinScale_id = EvnVizit.RankinScale_id
			";
			$add_join .= "
				left join lateral(
					select
						UCP.UslugaComplexPartition_id,
						UCP.UslugaComplexPartition_Code,
						UCP.UslugaComplexPartition_Name
					from r66.v_UslugaComplexPartitionLink UCPL
						inner join r66.v_UslugaComplexPartition UCP on UCP.UslugaComplexPartition_id = UCPL.UslugaComplexPartition_id
					where
						UCPL.UslugaComplex_id = EU.UslugaComplex_uid
						and (UCPL.Sex_id is null or UCPL.Sex_id = PS.Sex_id)
						and (UCPL.MedSpecOms_id is null or UCPL.MedSpecOms_id = VizitMSF.MedSpecOms_id)
						and (UCPL.LpuSectionProfile_id is null or UCPL.LpuSectionProfile_id = LSP.LpuSectionProfile_id)
						and UCPL.PayType_id = PayType.PayType_id
						and coalesce(UCPL.UslugaComplexPartitionLink_IsMes, 1) = (case when EvnVizit.Mes_id is null then 1 else 2 end)
					limit 1
				) Partition on true
			";
			$add_select .= ",VizitMSF.MedSpecOms_id as \"VizitMedSpecOms_id\"";
			$add_select .= ",Partition.UslugaComplexPartition_id as \"UslugaComplexPartition_id\"
			,Partition.UslugaComplexPartition_Code as \"UslugaComplexPartition_Code\"
			,Partition.UslugaComplexPartition_Name as \"UslugaComplexPartition_Name\"";

			$add_select .= ",EvnVizit.EvnVizitPL_IsZNORemove as \"EvnVizitPL_IsZNORemove\",
				to_char(EvnVizit.EvnVizitPL_BiopsyDate, 'dd.mm.yyyy') as \"EvnVizitPL_BiopsyDate\"";

		}

        if (getRegionNick() == 'kz') {
            $add_select .= "
                    ,UMT.UslugaMedType_id as \"UslugaMedType_id\"
                    ,UMT.UslugaMedType_Code as \"UslugaMedType_Code\"
                    ,UMT.UslugaMedType_Name as \"UslugaMedType_Name\"
                ";
            $add_join .= "
                    left join r101.v_UslugaMedTypeLink UMTL ON UMTL.Evn_id=EvnVizit.EvnVizitPL_id
                    left join r101.v_UslugaMedType UMT ON UMT.UslugaMedType_id=UMTL.UslugaMedType_id
                ";
        }

		$this->load->model('CureStandart_model');
		$cureStandartCountQuery = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'coalesce(EvnVizit.EvnVizitPL_setDT,dbo.tzGetDate())');
		$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('Diag');

		// получаем список EvnVizit
		$query = "
			Select
				EvnVizit.EvnVizitPL_id as \"EvnVizit_id\"
			from v_EvnVizitPL EvnVizit
				left join lateral(select * from v_Diag Diag where Diag.Diag_id = EvnVizit.Diag_id limit 1) Diag on true
				left join v_LpuSection LpuSection on LpuSection.LpuSection_id = EvnVizit.LpuSection_id
				left join v_LpuUnit LpuUnit on LpuSection.LpuUnit_id = LpuUnit.LpuUnit_id
			where
				{$filter}
				and EvnVizit.EvnClass_id != 13
		";
		//echo getDebugSQL($query, $queryParams); exit();

		$qr = $this->db->query($query, $queryParams);
		if ( !is_object($qr) ) {
			return false;
		}
		$evnvizitpl = $qr->result('array');

		if (count($evnvizitpl)>0) {
			// преобразуем evnvizitpl в массив для фильтра
			$evId = array();
			foreach ($evnvizitpl as $v) {
				$evId[] = $v['EvnVizit_id'];
			}
			$list_evId = implode(",", $evId);
			if (count($evId)==1) {
				$filterEv = "EvnVizit.EvnVizitPL_id = :EvnVizitPL_id";
				$queryParams['EvnVizitPL_id'] = $evId[0];
			}  else {
				$filterEv = "EvnVizit.EvnVizitPL_id in (".$list_evId.")";
			}

			$query = "
			Select
				EvnVizit.Lpu_id as \"Lpu_id\",
				EvnVizit.EvnVizitPL_Index as \"EvnVizitPL_Index\",
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				case
					when {$accessType}
						then 1
						else 0
					end as \"allowUnsign\",
				EvnVizit.EvnVizitPL_id as \"EvnVizitPL_id\",
				EvnVizit.EvnVizitPL_pid as \"EvnVizitPL_pid\",
				EvnVizit.EvnVizitPL_IsSigned as \"EvnVizitPL_IsSigned\",
				EvnVizit.EvnClass_id as \"EvnClass_id\",
				EvnVizit.EvnClass_Name as \"EvnClass_Name\",
				coalesce(to_char(EvnVizitPL_setDT, 'dd.mm.yyyy'),null) as \"EvnVizitPL_setDate\",
				to_char(EvnVizit.EvnVizitPL_setDT, 'yyyy-mm-dd') as \"EvnVizitPL_setDate120\",
				to_char(EvnVizitPL_setTime, 'hh24:mi') as \"EvnVizitPL_setTime\",
				Lpu.Lpu_Name as \"Lpu_Name\",
				Lpu.UAddress_Address as \"Lpu_Address\",
				LpuSection.LpuSection_id as \"LpuSection_id\",
				LpuSection.LpuSection_Code as \"LpuSection_Code\",
				LpuSection.LpuSectionCode_id as \"LpuSectionCode_id\",
				LpuSection.LpuSection_Name as \"LpuSection_Name\",
				LpuSection.LpuSectionAge_id as \"LpuSectionAge_id\",
				LpuUnit.LpuUnitSet_id as \"LpuUnitSet_id\",
				MedPersonal.MedStaffFact_id as \"MedStaffFact_id\",
				MedPersonal.MedPersonal_id as \"MedPersonal_id\",
				MedPersonal.MedPersonal_TabCode as \"MedPersonal_TabCode\",
				MedPersonal.Person_SurName ||' '|| coalesce(SUBSTRING(MedPersonal.Person_FirName from 1 for 1) ||'.', '')|| coalesce(SUBSTRING(MedPersonal.Person_SecName from 1 for 1) ||'.', '') as \"MedPersonal_Fin\",
				SMedPersonal.MedPersonal_id as \"MedPersonal_sid\",
				SMedPersonal.Person_SurName ||' '|| coalesce(SUBSTRING(SMedPersonal.Person_FirName from 1 for 1) ||'.', '')|| coalesce(SUBSTRING(SMedPersonal.Person_SecName from 1 for 1) ||'.', '') as \"MedPersonal_sFin\",
				EvnVizit.VizitClass_id as \"VizitClass_id\",
				VizitClass.VizitClass_Name as \"VizitClass_Name\",
				TreatmentClass.TreatmentClass_id as \"TreatmentClass_id\",
				TreatmentClass.TreatmentClass_Code as \"TreatmentClass_Code\",
				coalesce(TreatmentClass.TreatmentClass_Name,'') as \"TreatmentClass_Name\",
				ServiceType.ServiceType_id as \"ServiceType_id\",
				coalesce(ServiceType.ServiceType_SysNick,'') as \"ServiceType_SysNick\",
				coalesce(ServiceType.ServiceType_Code,'') as \"ServiceType_Code\",
				coalesce(ServiceType.ServiceType_Name,'') as \"ServiceType_Name\",
				VizitType.VizitType_id as \"VizitType_id\",
				EvnVizit.RiskLevel_id as \"RiskLevel_id\",
				EvnVizit.WellnessCenterAgeGroups_id as \"WellnessCenterAgeGroups_id\",
				RL.RiskLevel_Name as \"RiskLevel_Name\",
				WCAG.WellnessCenterAgeGroups_Name as \"WellnessCenterAgeGroups_Name\",
				Trim(coalesce(VizitType.VizitType_Name,'')) as \"VizitType_Name\",
				VizitType.VizitType_SysNick as \"VizitType_SysNick\",
				PG.ProfGoal_id as \"ProfGoal_id\",
				PG.ProfGoal_Name as \"ProfGoal_Name\",
				DPGT.DispProfGoalType_id as \"DispProfGoalType_id\",
				DPGT.DispProfGoalType_Name as \"DispProfGoalType_Name\",
				PayType.PayType_id as \"PayType_id\",
				PayType.PayType_SysNick as \"PayType_SysNick\",
				case
					when coalesce(EvnVizit.EvnVizitPL_IsInReg, 1) = 1
						then coalesce(PayType.PayType_Name,'')
						else '<b>' || coalesce(PayType.PayType_Name,'')  || '</b>'
					end as \"PayType_Name\",
				PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || coalesce(PS.Person_SecName, '') as \"Person_FIO\",
				date_part('year', dbo.tzGetDate()) - date_part('year', PS.Person_BirthDay)
				+ case
					when date_part('month', PS.Person_BirthDay) > date_part('month', dbo.tzGetDate())
						or date_part('month', PS.Person_BirthDay) = date_part('month', dbo.tzGetDate())
							and date_part('day', PS.Person_BirthDay) > date_part('day', dbo.tzGetDate())
							then -1
							else 0
					end as \"Person_Age\",
				(date_part('year', EvnVizit.EvnVizitPL_setDT) - date_part('year', PS.Person_BirthDay)) as \"Person_Age_On_Vizit_Date\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				Sex.Sex_Name as \"Person_Sex_Name\",
				Sex.Sex_SysNick as \"Sex_SysNick\",
				PS.Person_id as \"Person_id\",
				EvnVizit.Diag_id as \"Diag_id\",
				Diag.Diag_pid as \"Diag_pid\",
				coalesce(Diag.Diag_Code, '') as \"Diag_Code\",
				coalesce(Diag.Diag_Name, '') as \"Diag_Name\",
				case
					when Diag.Diag_id in (
						select
							PRD.Diag_id
						from v_PersonRegisterDiag PRD
							inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PRD.PersonRegisterType_id
						where PRT.PersonRegisterType_SysNick = 'pregnancy'
					)
						then 1
						else 0
					end as \"isPregDiag\",
				coalesce(DiagF.DiagFinance_IsOms, null) as \"DiagFinance_IsOms\",
				coalesce(DiagF.DiagFinance_IsRankin, 1) as \"DiagFinance_IsRankin\",
				DT.DeseaseType_id as \"DeseaseType_id\",
				DT.DeseaseType_Name as \"DeseaseType_Name\",
				DT.DeseaseType_SysNick as \"DeseaseType_SysNick\",
				TS.TumorStage_id as \"TumorStage_id\",
				TS.TumorStage_Name as \"TumorStage_Name\",
				HT.HealthKind_id as \"HealthKind_id\",
				HT.HealthKind_Name as \"HealthKind_Name\",
				PI.PainIntensity_id as \"PainIntensity_id\",
				PI.PainIntensity_Name as \"PainIntensity_Name\",
				'' as \"Diag_Text\",
				'' as \"PrehospDirect_Name\",
				0 as \"Cabinet_Num\",
				FM.CureStandart_Count as \"CureStandart_Count\",
				DFM.DiagFedMes_FileName as \"DiagFedMes_FileName\",
				(select count(Evn_id) from v_Evn where Evn_pid = EvnVizit.EvnVizitPL_id) as \"Children_Count\",
				trim(coalesce(pucins.PMUser_surName,pucins.PMUser_Name,''))
					||' '|| trim(coalesce(pucins.PMUser_firName,''))
					||' '|| trim(coalesce(pucins.PMUser_secName,''))
					as \"ins_Name\",
				trim(coalesce(pucsign.PMUser_surName,pucsign.PMUser_Name,''))
					||' '|| trim(coalesce(pucsign.PMUser_firName,''))
					||' '|| trim(coalesce(pucsign.PMUser_secName,''))
					as \"sign_Name\",
				to_char(EvnVizit.EvnVizitPL_insDT, 'dd.mm.yyyy HH24:MI') as \"insDT\",
				to_char(EvnVizit.EvnVizitPL_signDT, 'dd.mm.yyyy HH24:MI') as \"signDT\",
				LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				LSP.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				EvnVizit.Mes_id as \"Mes_id\",
				mo.Mes_Code as \"Mes_Code\",
				mo.Mes_Name as \"Mes_Name\",
				EvnVizit.MedicalCareKind_id as \"MedicalCareKind_id\",
				mck.MedicalCareKind_Code as \"MedicalCareKind_Code\",
				mck.MedicalCareKind_Name as \"MedicalCareKind_Name\",
				Pers.Person_IsAnonym as \"Person_IsAnonym\",
				LEV.EvnVizitPL_setDate as \"LastEvnVizitPL_setDate\",
				PEVPL.PregnancyEvnVizitPL_Period as \"PregnancyEvnVizitPL_Period\",
				IsZNO.YesNo_id as \"EvnVizitPL_IsZNO\",
				IsZNO.YesNo_Name as \"IsZNO_Name\",
				EvnVizit.Diag_spid as \"Diag_spid\",
				coalesce(DiagSpid.Diag_Code,'') as \"DiagSpid_Code\",
				coalesce(DiagSpid.Diag_Name,'') as \"DiagSpid_Name\",
				ex.EvnXml_id as \"EvnXml_id\",
				ex.EvnXml_IsSigned as \"EvnXml_IsSigned\",
				PD.PersonDisp_id as \"PersonDisp_id\",
				case
					when PD.PersonDisp_id is not null
						then coalesce(to_char(PD.PersonDisp_begDate, 'dd.mm.yyyy'), '...')
							||' - '|| coalesce(to_char(PD.PersonDisp_endDate, 'dd.mm.yyyy'),'...')
							|| ' ' || coalesce(PDDiag.Diag_Code || ' ', '')
							|| coalesce(PDDiag.Diag_Name || ' ', '')
						else ''
				 	end as \"PersonDisp_Name\"
				-- Услуга
				" .
				($this->EvnVizitPL_model->isUseVizitCode ?
					",EU.EvnUslugaCommon_id as \"EvnUslugaCommon_id\",
					 coalesce(EU.UslugaComplex_uid, EvnVizit.UslugaComplex_id) as \"UslugaComplex_uid\",
					 UC.UslugaComplex_Code as \"UslugaComplex_Code\",
					 UC.UslugaComplex_Name as \"UslugaComplex_Name\"
					 "
					:
					",NULL as \"EvnUslugaCommon_id\",
					NULL as \"UslugaComplex_uid\",
					NULL as \"UslugaComplex_Code\",
					NULL as \"UslugaComplex_Name\"")
				. "
				,case when exists (
					select MO.MorbusOnko_id
					from v_MorbusOnko MO
					inner join v_Morbus M on M.Morbus_id = MO.Morbus_id
					where 
						M.Person_id = EvnVizit.Person_id and
						MO.Diag_id = EvnVizit.Diag_spid and 
						M.Morbus_disDT is null
					limit 1
				) then 2 else 0 end as \"isMorbusOnkoExists\"
				{$add_select}
			from v_EvnVizitPL EvnVizit
				left join v_MesOld mo on mo.Mes_id = EvnVizit.Mes_id
				left join v_LpuSection LpuSection on LpuSection.LpuSection_id = EvnVizit.LpuSection_id
				left join v_LpuUnit LpuUnit on LpuSection.LpuUnit_id = LpuUnit.LpuUnit_id
				left join v_Lpu Lpu on EvnVizit.Lpu_id = Lpu.Lpu_id
				left join v_PersonState PS on EvnVizit.Person_id = PS.Person_id
				left join v_Person Pers on Pers.Person_id = PS.Person_id
				left join v_Sex Sex on PS.Sex_id = Sex.Sex_id
				left join RiskLevel RL on EvnVizit.RiskLevel_id = RL.RiskLevel_id
				left join WellnessCenterAgeGroups WCAG on EvnVizit.WellnessCenterAgeGroups_id = WCAG.WellnessCenterAgeGroups_id
				--left join v_LpuUnit LpuUnit on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
				left join v_MedStaffFact MedPersonal on MedPersonal.MedStaffFact_id =EvnVizit.MedStaffFact_id
				left join v_MedPersonal SMedPersonal on sMedPersonal.MedPersonal_id = EvnVizit.MedPersonal_sid and sMedPersonal.Lpu_id = EvnVizit.Lpu_id
				left join v_TreatmentClass TreatmentClass on TreatmentClass.TreatmentClass_id = EvnVizit.TreatmentClass_id
				left join v_ServiceType ServiceType on ServiceType.ServiceType_id = EvnVizit.ServiceType_id
				left join v_VizitType VizitType on VizitType.VizitType_id = EvnVizit.VizitType_id
				left join v_ProfGoal PG on VizitType.VizitType_SysNick = 'prof' and PG.ProfGoal_id = EvnVizit.ProfGoal_id
				left join v_DispProfGoalType DPGT on DPGT.DispProfGoalType_id = EvnVizit.DispProfGoalType_id
				left join v_VizitClass VizitClass on VizitClass.VizitClass_id = EvnVizit.VizitClass_id
				left join v_PayType PayType on PayType.PayType_id = EvnVizit.PayType_id
				left join fed.v_MedicalCareKind mck on mck.MedicalCareKind_id = EvnVizit.MedicalCareKind_id
				left join lateral (select Diag_id,Diag_pid,Diag_Code,Diag_Name from  v_Diag Diag where Diag.Diag_id = EvnVizit.Diag_id  limit 1) Diag on true
				left join lateral (
					select
						*
					from
						v_DiagFinance t1
					where
						t1.Diag_id = Diag.Diag_id
					order by
						t1.DiagFinance_updDT desc
					limit 1
				) DiagF on true
				left join v_DeseaseType DT on EvnVizit.DeseaseType_id = DT.DeseaseType_id
				left join v_TumorStage TS on EvnVizit.TumorStage_id = TS.TumorStage_id
				left join v_HealthKind HT on EvnVizit.HealthKind_id = HT.HealthKind_id
				left join v_PainIntensity PI on PI.PainIntensity_id = EvnVizit.PainIntensity_id
				left join v_pmUserCache pucins on EvnVizit.pmUser_insID = pucins.PMUser_id
				left join v_pmUserCache pucsign on EvnVizit.pmUser_signID = pucsign.PMUser_id
				left join v_PregnancyEvnVizitPL PEVPL on PEVPL.EvnVizitPL_id = EvnVizit.EvnVizitPL_id
				left join v_YesNo IsZNO on IsZNO.YesNo_id = EvnVizit.EvnVizitPL_IsZNO
				left join lateral (select Diag_Code,Diag_Name from  v_Diag DiagSpid where DiagSpid.Diag_id = EvnVizit.Diag_spid limit 1) DiagSpid on true
				left join v_EvnXml ex on ex.Evn_id = EvnVizit.EvnVizitPL_id and ex.XmlType_id = 3
				left join v_PersonDisp PD on PD.PersonDisp_id = EvnVizit.PersonDisp_id
				left join lateral (select Diag_Code,Diag_Name from  v_Diag PDDiag where PDDiag.Diag_id = PD.Diag_id limit 1) PDDiag on true
				{$add_join}
				left join lateral(
					{$cureStandartCountQuery}
				) FM on true
				left join lateral(
					{$diagFedMesFileNameQuery}
				) DFM on true
				left join lateral(
					select
						coalesce(to_char(EvnVizitPL_setDT,'dd.mm.yyyy'),'') as EvnVizitPL_setDate
					from v_EvnVizitPL
					where EvnVizitPL_pid = EvnVizit.EvnVizitPL_pid
					order by EvnVizitPL_setDT DESC
					limit 1
				) LEV on true
			where
				{$filterEv}
			order by EvnVizit.EvnVizitPL_setDT DESC
			";
			// echo getDebugSQL($query, $queryParams); exit();

			$result = $this->db->query($query, $queryParams);
			if ( !is_object($result) ) {
				return false;
			}
			$response = $result->result('array');
			//$response = swFilterResponse::filterNotViewDiag($response, $data);
			$this->load->library('swMorbus');
			$response = swMorbus::processingEvnData($response, 'EvnVizitPL');
			$this->load->library('swPersonRegister');
			$response = swPersonRegister::processingEvnData($response, 'EvnVizitPL');
			foreach ($response as $key => $value) {
				$response[$key]['regionNick'] = $this->getRegionNick();
			}
			return $response;
		} else {
			return false;
		}
	}

	/**
	 *	Получение данных стомат. посещения для ЭМК
	 */
function getEvnVizitPLStomViewData($data) {
		$filter = "1=1";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		if (isset($data['EvnVizitPLStom_pid']))
		{
			$filter .= ' and EvnVizit.EvnVizitPLStom_pid = :EvnVizitPLStom_pid';
			$queryParams['EvnVizitPLStom_pid'] = $data['EvnVizitPLStom_pid'];
		}
		else
		{
			$filter .= ' and EvnVizit.EvnVizitPLStom_id = :EvnVizitPLStom_id';
			$queryParams['EvnVizitPLStom_id'] = $data['EvnVizitPLStom_id'];
		}

		$needAccessFilter = true; //https://redmine.swan.perm.ru/issues/104824
		if(isset($data['from_MZ']) && $data['from_MZ'] == 2)
			$needAccessFilter = false;
		if($needAccessFilter){
			$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
			if (!empty($diagFilter)) {
				$filter .= " and ( $diagFilter )";
			}
			$lpuFilter = getAccessRightsLpuFilter('EvnVizit.Lpu_id');
			if (!empty($lpuFilter)) {
				$filter .= " and $lpuFilter";
			}
			$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('LpuUnit.LpuBuilding_id');
			if (!empty($lpuBuildingFilter)) {
				$filter .= " and $lpuBuildingFilter";
			}
		}
		$accessType = 'EvnVizit.Lpu_id = :Lpu_id';
		// and coalesce(EvnVizit.EvnVizitPLStom_IsInReg, 1) = 1 AND coalesce(EvnVizit.EvnVizitPLStom_IsSigned,1) != 2
		$add_join = '';
		$add_select = '';
		if (isset($data['user_MedStaffFact_id']))
		{
			//врач может редактировать, если посещение создано в его ЛПУ, оно не оплачено, оно создано им, в его отделении и случай АПЛ не закончен
			$accessType .= ' and EvnVizit.LpuSection_id = MSF.LpuSection_id';
			$add_select .= "
				,MSO.MedSpec_id as \"FedMedSpec_id\"
			";
			$add_join .= "
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = :user_MedStaffFact_id
				left join v_MedSpecOms MSO on MSO.MedSpecOms_id = MSF.MedSpecOms_id
			";
			$queryParams['user_MedStaffFact_id'] = $data['user_MedStaffFact_id'];
		}

		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$add_join .= "
				left join lateral(
					select
						t1.EvnUsluga_id,
						t1.UslugaComplex_id as UslugaComplex_uid,
						t1.UslugaComplexTariff_id,
						t1.EvnUsluga_Price,
						t2.UslugaComplex_Code,
						t2.UslugaComplex_Name
					from
						v_EvnUsluga t1
						left join v_UslugaComplex t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
						left join v_UslugaCategory t3 on t3.UslugaCategory_id = t2.UslugaCategory_id
					where
						t1.EvnUsluga_pid = EvnVizit.EvnVizitPLStom_id
						and coalesce(t1.EvnUsluga_IsVizitCode, 1) = 2
					order by
						t1.EvnUsluga_setDT desc
					limit 1
				) EU on true
			";
			$add_join .= "
				left join v_UslugaComplexTariff uct on uct.UslugaComplexTariff_id = EU.UslugaComplexTariff_id
			";
		}

		$this->load->model('CureStandart_model');
		$cureStandartCountQuery = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'coalesce(EvnVizit.EvnVizitPLStom_setDT,dbo.tzGetDate())');
		$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('Diag');

		if (getRegionNick() === 'kz') {
            $add_select .= "
                    ,UMT.UslugaMedType_id as \"UMT.UslugaMedType_id\"
                    ,UMT.UslugaMedType_Code as \"UMT.UslugaMedType_Code\"
                    ,UMT.UslugaMedType_Name as \"UslugaMedType_Name\",
                    ,gbel.PayTypeKAZ_id as \"PayTypeKAZ_id\"
					,ptkz.PayTypeKAZ_Name as \"PayTypeKAZ_Name\"
					,gbel.VizitActiveType_id as \"VizitActiveType_id\"
					,VAT.VizitActiveType_Name as \"VizitActiveType_Name\"
                ";
            $add_join .= "
                    left join r101.v_UslugaMedTypeLink UMTL ON UMTL.Evn_id=EvnVizit.EvnVizitPLStom_id
                    left join r101.v_UslugaMedType UMT ON UMT.UslugaMedType_id=UMTL.UslugaMedType_id
                    left join r101.EvnLinkAPP gbel on gbel.Evn_id = EvnVizit.EvnVizitPLStom_id
					left join r101.PayTypeKAZ ptkz on ptkz.PayTypeKAZ_id = gbel.PayTypeKAZ_id
					left join v_VizitActiveType VAT on VAT.VizitActiveType_id = gbel.VizitActiveType_id
                ";
        }

		$query = "
		Select
			EvnVizit.Lpu_id as \"Lpu_id\",
			EvnVizit.EvnVizitPLStom_Index as \"EvnVizitPLStom_Index\",
			case
				when {$accessType}
					then 'edit'
					else 'view'
				end as \"accessType\",
			EvnVizit.EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
			EvnVizit.EvnVizitPLStom_pid as \"EvnVizitPLStom_pid\",
			coalesce(EvnVizit.EvnVizitPLStom_IsSigned,1) as \"EvnVizitPLStom_IsSigned\",
			EvnVizit.EvnClass_id as \"EvnClass_id\",
			EvnVizit.EvnClass_Name as \"EvnClass_Name\",
			coalesce(to_char(EvnPL.EvnPLStom_setDT,'dd.mm.yyyy'),'') as \"EvnPLStom_setDate\",
			coalesce(to_char(EvnVizitPLStom_setDT,'dd.mm.yyyy'),'') as \"EvnVizitPLStom_setDate\",
			to_char(EvnVizitPLStom_setTime, 'hh24:mi') as \"EvnVizitPLStom_setTime\",
			Lpu.Lpu_Name as \"Lpu_Name\",
			Lpu.UAddress_Address as \"Lpu_Address\",
			LpuSection.LpuSection_id as \"LpuSection_id\",
			LpuSection.LpuSection_Code as \"LpuSection_Code\",
			LpuSection.LpuSection_Name as \"LpuSection_Name\",
			LpuUnit.LpuUnitSet_id as \"LpuUnitSet_id\",
			MedPersonal.MedStaffFact_id as \"MedStaffFact_id\",
			MedPersonal.MedPersonal_id as \"MedPersonal_id\",
			MedPersonal.MedPersonal_TabCode as \"MedPersonal_TabCode\",
			MedPersonal.Person_SurName ||' '|| coalesce(SUBSTRING(MedPersonal.Person_FirName from 1 for 1) ||'.', '')
				|| coalesce(SUBSTRING(MedPersonal.Person_SecName from 1 for 1) ||'.', '')
				as \"MedPersonal_Fin\",
				
			SMedPersonal.MedPersonal_id as \"MedPersonal_sid\",
			SMedPersonal.Person_SurName ||' '|| coalesce(SUBSTRING(SMedPersonal.Person_FirName from 1 for 1) ||'.', '')
				|| coalesce(SUBSTRING(SMedPersonal.Person_SecName from 1 for 1) ||'.', '')
				as \"MedPersonal_sFin\",
				
			EvnVizit.VizitClass_id as \"VizitClass_id\",
			VizitClass.VizitClass_Name as \"VizitClass_Name\",
			EvnVizit.EvnVizitPLStom_IsPrimaryVizit as \"EvnVizitPLStom_IsPrimaryVizit\",
			IsPrimaryVizit.YesNo_Name as \"IsPrimaryVizit_Name\",
			coalesce(EvnVizit.LpuSectionProfile_id,LSPLS.LpuSectionProfile_id) as \"LpuSectionProfile_id\",
			case
				when EvnVizit.LpuSectionProfile_id is not null
					then LSP.LpuSectionProfile_Code
					else LSPLS.LpuSectionProfile_Code
				end as \"LpuSectionProfile_Code\",
			case
				when EvnVizit.LpuSectionProfile_id is not null
					then LSP.LpuSectionProfile_Name
					else LSPLS.LpuSectionProfile_Name
				end as \"LpuSectionProfile_Name\",
			TreatmentClass.TreatmentClass_id as \"TreatmentClass_id\",
			coalesce(TreatmentClass.TreatmentClass_Name,'') as \"TreatmentClass_Name\",
			ServiceType.ServiceType_id as \"ServiceType_id\",
			coalesce(ServiceType.ServiceType_SysNick,'') as \"ServiceType_SysNick\",
			coalesce(ServiceType.ServiceType_Code,'') as \"ServiceType_Code\",
			coalesce(ServiceType.ServiceType_Name,'') as \"ServiceType_Name\",
			VizitType.VizitType_id as \"VizitType_id\",
			Trim(coalesce(VizitType.VizitType_Name,'')) as \"VizitType_Name\",
			VizitType.VizitType_SysNick as \"VizitType_SysNick\",
			PG.ProfGoal_Name as \"ProfGoal_Name\",
			PayType.PayType_id as \"PayType_id\",
			PayType.PayType_SysNick as \"PayType_SysNick\",
			case
				when coalesce(EvnVizit.EvnVizitPLStom_IsInReg, 1) = 1
					then coalesce(PayType.PayType_Name,'')
					else '<b>' || coalesce(PayType.PayType_Name,'')  || '</b>'
				end as \"PayType_Name\",
			PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || coalesce(PS.Person_SecName, '') as \"Person_FIO\",
			(date_part('year', dbo.tzGetDate()) - date_part('year', PS.Person_BirthDay)
				+ case
					when date_part('month', PS.Person_BirthDay) > date_part('month', dbo.tzGetDate())
						or (date_part('month', PS.Person_BirthDay) = date_part('month', dbo.tzGetDate())
							and date_part('day', PS.Person_BirthDay) > date_part('day', dbo.tzGetDate()))
						then -1
						else 0
					end) as \"Person_Age\",
			to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
			Sex.Sex_Name as \"Person_Sex_Name\",
			PS.Person_id as \"Person_id\",
			EvnVizit.Diag_id as \"Diag_id\",
			Diag.Diag_pid as \"Diag_pid\",
			coalesce(Diag.Diag_Code,'') as \"Diag_Code\",
			coalesce(Diag.Diag_Name,'') as \"Diag_Name\",
			DT.DeseaseType_id as \"DeseaseType_id\",
			DT.DeseaseType_Name as \"DeseaseType_Name\",
			HK.HealthKind_id as \"HealthKind_id\",
			HK.HealthKind_Name as \"HealthKind_Name\",
			FM.CureStandart_Count as \"CureStandart_Count\",
			DFM.DiagFedMes_FileName as \"DiagFedMes_FileName\",
			(select count(Evn_id) from v_Evn where Evn_pid = EvnVizit.EvnVizitPLStom_id) as \"Children_Count\",
			trim(coalesce(pucins.PMUser_surName, pucins.PMUser_Name, '')) 
				|| ' ' || trim(coalesce(pucins.PMUser_firName, '')) 
				|| ' ' || trim(coalesce(pucins.PMUser_secName, '')) as \"ins_Name\",
			trim(coalesce(pucsign.PMUser_surName,pucsign.PMUser_Name,'')) 
				||' '|| trim(coalesce(pucsign.PMUser_firName,'')) 
				||' '|| trim(coalesce(pucsign.PMUser_secName,'')) as \"sign_Name\",
			to_char(EvnVizit.EvnVizitPLStom_insDT, 'dd.mm.yyyy HH24:MI') as \"insDT\",
			to_char(EvnVizit.EvnVizitPLStom_signDT, 'dd.mm.yyyy HH24:MI') as \"signDT\",
			v_Tooth.Tooth_Code as \"Tooth_Code\",
			v_Tooth.Tooth_id as \"Tooth_id\",
			EvnVizit.EvnVizitPLStom_ToothSurface as \"json_data\",
			Mes.Mes_id as \"Mes_id\",
			Mes.Mes_Code as \"Mes_Code\",
			Mes.Mes_Name as \"Mes_Name\",
			DPGT.DispProfGoalType_id as \"DispProfGoalType_id\",
			DPGT.DispProfGoalType_Name as \"DispProfGoalType_Name\",
			Mes.Mes_KoikoDni as \"EvnVizitPLStom_MesUet\", --УЕТ(норматив по МЭС)
			EvnVizit.EvnVizitPLStom_UetOMS as \"EvnVizitPLStom_UetOMS\", --УЕТ(факт по ОМС)
			EvnVizit.EvnVizitPLStom_Uet as \"EvnVizitPLStom_Uet\", --УЕТ(факт)
			Parodontogram.EvnUslugaStom_id as \"EvnUslugaParodontogram_id\",
			Parodontogram.EvnUslugaStom_pid as \"EvnUslugaParodontogram_pid\",
			Pers.Person_IsAnonym,
			LEV.EvnVizitPLStom_setDate as \"LastEvnVizitPLStom_setDate\",
			EvnVizit.MedicalCareKind_id as \"MedicalCareKind_id\",
			mck.MedicalCareKind_Code as \"MedicalCareKind_Code\",
			mck.MedicalCareKind_Name as \"MedicalCareKind_Name\",
			BPD.BitePersonType_id as \"BitePersonType_id\",
			BPD.BitePersonType_Name as \"BitePersonType_Name\",
			PD.PersonDisp_id as \"PersonDisp_id\",
			case
				when PD.PersonDisp_id is not null
					then coalesce(to_char(PD.PersonDisp_begDate, 'dd.mm.yyyy'), '...')
						|| ' - ' || coalesce(to_char(PD.PersonDisp_endDate, 'dd.mm.yyyy'),'...')
						|| ' ' || coalesce(PDDiag.Diag_Code || ' ', '') || coalesce(PDDiag.Diag_Name || ' ', '')
					else ''
			 	end as \"PersonDisp_Name\"
			-- Услуга
			" . ($this->EvnVizitPL_model->isUseVizitCode
				? "
					,EU.EvnUsluga_id as \"EvnUslugaStom_id\",
					EU.UslugaComplex_uid as \"UslugaComplex_uid\",
					EU.UslugaComplex_Code as \"UslugaComplex_Code\",
					EU.UslugaComplex_Name as \"UslugaComplex_Name\",
					EU.UslugaComplex_uid as \"UslugaComplex_uid\",
					EU.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
					uct.UslugaComplexTariff_Code as \"UslugaComplexTariff_Code\",
					uct.UslugaComplexTariff_Name as \"UslugaComplexTariff_Name\",
					ROUND(CAST(EU.EvnUsluga_Price as numeric), 2) as \"EvnUslugaStom_UED\"
				"
				: "
					,NULL as \"EvnUslugaStom_id\",
					NULL as \"UslugaComplex_uid\",
					NULL as \"UslugaComplex_Code\",
					NULL as \"UslugaComplex_Name\",
					NULL as \"UslugaComplex_uid\",
					NULL as \"UslugaComplexTariff_id\",
					NULL as \"UslugaComplexTariff_Code\",
					NULL as \"UslugaComplexTariff_Name\",
					NULL as \"EvnUslugaStom_UED\"
				") ."
			{$add_select}
		from v_EvnVizitPLStom EvnVizit
			inner join v_EvnPLStom EvnPL on EvnPL.EvnPLStom_id = EvnVizit.EvnVizitPLStom_pid
			left join v_LpuSection LpuSection on LpuSection.LpuSection_id = EvnVizit.LpuSection_id
			left join v_DispProfGoalType DPGT on DPGT.DispProfGoalType_id = EvnVizit.DispProfGoalType_id
			left join v_LpuUnit LpuUnit on LpuSection.LpuUnit_id = LpuUnit.LpuUnit_id
			left join v_Lpu Lpu on EvnVizit.Lpu_id = Lpu.Lpu_id
			left join v_PersonState PS on EvnVizit.Person_id = PS.Person_id
			left join v_Person Pers on Pers.Person_id = PS.Person_id
			left join v_Sex Sex on PS.Sex_id = Sex.Sex_id
			--left join v_LpuUnit LpuUnit on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
			left join v_MedStaffFact MedPersonal on MedPersonal.MedStaffFact_id = EvnVizit.MedStaffFact_id
			left join v_MedPersonal SMedPersonal on sMedPersonal.MedPersonal_id = EvnVizit.MedPersonal_sid and sMedPersonal.Lpu_id = EvnVizit.Lpu_id
			left join v_TreatmentClass TreatmentClass on TreatmentClass.TreatmentClass_id = EvnVizit.TreatmentClass_id
			left join v_ServiceType ServiceType on ServiceType.ServiceType_id = EvnVizit.ServiceType_id
			left join v_VizitType VizitType on VizitType.VizitType_id = EvnVizit.VizitType_id
			left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = EvnVizit.LpuSectionProfile_id
			left join v_LpuSectionProfile LSPLS on LSPLS.LpuSectionProfile_id = LpuSection.LpuSectionProfile_id
			left join v_ProfGoal PG on VizitType.VizitType_SysNick = 'prof' and PG.ProfGoal_id = EvnVizit.ProfGoal_id
			left join v_VizitClass VizitClass on VizitClass.VizitClass_id = EvnVizit.VizitClass_id
			left join v_PayType PayType on PayType.PayType_id = EvnVizit.PayType_id
			left join fed.v_MedicalCareKind mck on mck.MedicalCareKind_id = EvnVizit.MedicalCareKind_id
			left join v_Diag Diag on Diag.Diag_id = EvnVizit.Diag_id
			left join v_DeseaseType DT on EvnVizit.DeseaseType_id = DT.DeseaseType_id
			left join v_HealthKind HK on EvnVizit.HealthKind_id = HK.HealthKind_id
			left join v_pmUserCache pucins on EvnVizit.pmUser_insID = pucins.PMUser_id
			left join v_pmUserCache pucsign on EvnVizit.pmUser_signID = pucsign.PMUser_id
			left join v_MesOld Mes on Mes.Mes_id = EvnVizit.Mes_id
			left join v_Tooth on v_Tooth.Tooth_id = EvnVizit.Tooth_id
			left join v_YesNo IsPrimaryVizit on IsPrimaryVizit.YesNo_id = EvnVizit.EvnVizitPLStom_IsPrimaryVizit
			left join v_PersonDisp PD on PD.PersonDisp_id = EvnVizit.PersonDisp_id
			left join v_Diag PDDiag on PDDiag.Diag_id = PD.Diag_id
			{$add_join}
			left join lateral(
				{$cureStandartCountQuery}
			) FM on true
			left join lateral(
				{$diagFedMesFileNameQuery}
			) DFM on true
			left join lateral(
				select
					e.EvnUslugaStom_id,
					e.EvnUslugaStom_pid
				from v_EvnUslugaStom e
                	inner join v_Evn v on v.Evn_id = e.EvnUslugaStom_pid
				where e.Person_id = EvnVizit.Person_id
					and e.EvnUslugaStom_setDate <= EvnVizit.EvnVizitPLStom_setDate
					and exists(
						select
							p.Parodontogram_id
						from v_Parodontogram p
						where p.EvnUslugaStom_id = e.EvnUslugaStom_id
						limit 1
					)
				order by
					case
						when e.EvnUslugaStom_pid = EvnVizit.EvnVizitPLStom_id
							then 1
							else 2
						end,
					v.Evn_setDT desc
				limit 1
			) Parodontogram on true
			left join lateral(
				select
					coalesce(to_char(EvnVizitPLStom_setDT,'dd.mm.yyyy'),'') as EvnVizitPLStom_setDate
				from v_EvnVizitPLStom
				where EvnVizitPLStom_pid = EvnVizit.EvnVizitPLStom_pid
				order by EvnVizitPLStom_setDT DESC
				limit 1
			) LEV on true
			left join lateral(
				select
					bpt.BitePersonType_Name,
					bpt.BitePersonType_id
				from v_BitePersonData bpData
				left join v_BitePersonType bpt on bpt.BitePersonType_id = bpData.BitePersonType_id
				where bpData.EvnVizitPLStom_id = EvnVizit.EvnVizitPLStom_id
					--and BitePersonData_disDate is null
					order by BitePersonData_updDT DESC
				limit 1
			) BPD on true
		where
			{$filter}
		order by EvnVizit.EvnVizitPLStom_setDT DESC
		";
		//echo getDebugSQL($query, $queryParams); exit();
		// select *from v_DiagFinance d where d.DiagFinance_IsOms = 1
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response = $result->result('array');
			//$response = swFilterResponse::filterNotViewDiag($response, $data);
			$this->load->library('swMorbus');
			$response = swMorbus::processingEvnData($response, 'EvnVizitPLStom');
			$this->load->library('swPersonRegister');
			$response = swPersonRegister::processingEvnData($response, 'EvnVizitPLStom');
			$this->load->model('EvnDiagPLStom_model', 'EvnDiagPLStom_model');
			foreach ($response as $index => $row) {
				$dataToothSurface = $this->EvnDiagPLStom_model->processingToothSurface($response[$index]['json_data'], true);
				$response[$index]['ToothSurfaceType_id_list'] = implode(',', $dataToothSurface['ToothSurfaceTypeIdList']);
				$response[$index]['ToothSurfaceType_list'] = implode(', ', $dataToothSurface['ToothSurfaceTypeNameList']);
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных для формы редактирования посещения
	 */
	function loadEvnVizitPLEditForm($data) {
		$joinQuery = "";
		$fields = "";

		if ( isset($data['FormType']) && $data['FormType'] == 'EvnVizitPLWow' ) {
			$fields .= "EVPL.DispWowSpec_id,";
			$prefix = "WOW";
		}
		else {
			$fields .= "coalesce(EVPL.EvnDirection_id, 0) as \"EvnDirection_id\",
			coalesce(EVPL.TimetableGraf_id, 0) as \"TimetableGraf_id\",
			EVPL.EvnPrescr_id as \"EvnPrescr_id\",";
			$prefix = "";
		}

		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$joinQuery .= "
				left join lateral(
					select
						t1.EvnUsluga_id as EvnUslugaCommon_id,
						t1.UslugaComplex_id as UslugaComplex_uid
					from
						v_EvnUsluga t1
					where
						t1.EvnUsluga_pid = :EvnVizitPL_id
						and coalesce(t1.EvnUsluga_IsVizitCode, 1) = 2
					order by
						t1.EvnUsluga_setDT desc
					limit 1
				) EU on true
			";
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$access_type = '
			case
				when EVPL.Lpu_id = :Lpu_id and (EVPL.LpuSection_id = SMP.LpuSection_id OR EVPL.MedStaffFact_sid = :MedStaffFact_id) then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EVPL.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and coalesce(EVPL.EvnVizitPL' . $prefix . '_IsTransit, 1) = 2 then 1' : '') . '
				when (:isMedStatUser::boolean = TRUE or :withoutMedPersonal::boolean = TRUE) and EVPL.Lpu_id = :Lpu_id then 1
				when :isSuperAdmin::boolean = TRUE then 1
				else 0
			end = 1
		';

		if ($this->regionNick == 'pskov') {
			$access_type .= "and coalesce(EVPL.EvnVizitPL{$prefix}_IsPaid, 1) = 1
			 	and not exists(
					select
						RD.Registry_id
					from r60.v_RegistryData RD
						inner join v_Registry R on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EVPL.EvnVizitPL{$prefix}_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
					limit 1
				)
			";
		}
		if ($this->regionNick == 'ekb') {
			$fields .= "EVPL.EvnVizitPL{$prefix}_IsZNORemove as \"EvnVizitPL{$prefix}_IsZNORemove\",
				to_char(EVPL.EvnVizitPL{$prefix}_BiopsyDate, 'dd.mm.yyyy') as \"EvnVizitPL{$prefix}_BiopsyDate\",";
		}
		
		if(getRegionNick() == 'kz') {
			$fields .= "
				gbel.PayTypeKAZ_id as \"PayTypeKAZ_id\",
				gbel.VizitActiveType_id as \"VizitActiveType_id\",
			";
			$joinQuery .= "
				left join r101.EvnLinkAPP gbel on gbel.Evn_id = :EvnVizitPL_id
				";
		}

		$lpuFilter = "";
		if (!isset($data['session']['CurArmType']) || $data['session']['CurArmType'] != 'spec_mz') {
			$lpuFilter = "and (EVPL.Lpu_id " . getLpuIdFilter($data) . " or " . (!empty($data['session']['medpersonal_id']) ? 1 : 0) . " = 1)";
		}

		// https://redmine.swan.perm.ru/issues/28433 условие на accessType
		$query = "
			select
				case
					when {$access_type} and coalesce(EVPL.EvnVizitPL{$prefix}_IsSigned, 1) = 1 " .
			        ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 && !isSuperadmin() ? "and exists(
						select
							MedStaffFact_id
						from v_MedStaffFact
						where
							MedPersonal_id in (".implode(',',$med_personal_list).")
							and LpuSection_id = EVPL.LpuSection_id
							and WorkData_begDate <= EVPL.EvnVizitPL{$prefix}_setDate
							and (WorkData_endDate is null or WorkData_endDate >= EVPL.EvnVizitPL{$prefix}_setDate)
						limit 1
						)" : "") . "
					then 'edit'
					else 'view'
					end as \"accessType\",
                EVPL.EvnVizitPL{$prefix}_NumGroup as \"EvnVizitPL_NumGroup\",
                coalesce(EVPL.EvnVizitPL{$prefix}_id, 0) as \"EvnVizitPL_id\",
				coalesce(EVPL.EvnVizitPL{$prefix}_pid, 0) as \"EvnPL_id\",
				EVPL.EvnDirection_id as \"EvnDirection_id\",
				coalesce(EVPL.Person_id, 0) as \"Person_id\",
				coalesce(EVPL.PersonEvn_id, 0) as \"PersonEvn_id\",
				coalesce(EVPL.Server_id, -1) as \"Server_id\",
				" . $fields . "
				EVPL.DeseaseType_id as \"DeseaseType_id\",
				EVPL.TumorStage_id as \"TumorStage_id\",
				EVPL.EvnVizitPL{$prefix}_Index as \"EvnVizitPL_Index\",
				EVPL.Diag_id as \"Diag_id\",
				CASE WHEN mainDiag.Diag_Code IN ('I50.0', 'I50.1', 'I50.9')
					THEN dhd.HSNStage_id
					ELSE NULL
				END AS \"HSNStage_id\",
				CASE WHEN mainDiag.Diag_Code IN ('I50.0', 'I50.1', 'I50.9')
					THEN dhd.HSNFuncClass_id
					ELSE NULL
				END AS \"HSNFuncClass_id\",
				EVPL.Diag_agid as \"Diag_agid\",
				CASE WHEN complDiag.Diag_Code IN ('I50.0', 'I50.1', 'I50.9')
					THEN dhd.HSNStage_id
					ELSE NULL
				END AS \"ComplDiagHSNStage_id\",
				CASE WHEN complDiag.Diag_Code IN ('I50.0', 'I50.1', 'I50.9')
					THEN dhd.HSNFuncClass_id
					ELSE NULL
				END AS \"ComplDiagHSNFuncClass_id\",
				EVPL.Diag_id as \"Diag_id\",
				EVPL.HealthKind_id as \"HealthKind_id\",
				LU.LpuBuilding_id as \"LpuBuilding_id\",
				LU.LpuUnit_id as \"LpuUnit_id\",
				LU.LpuUnitSet_id as \"LpuUnitSet_id\",
				EVPL.MedStaffFact_id as \"MedStaffFact_id\",
				EVPL.LpuSection_id as \"LpuSection_id\",
				EVPL.Lpu_id as \"Lpu_id\",
				EVPL.RiskLevel_id as \"RiskLevel_id\",
				EVPL.WellnessCenterAgeGroups_id as \"WellnessCenterAgeGroups_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedStaffFact_id as \"MedStaffFact_id\",
				EVPL.MedPersonal_sid as \"MedPersonal_sid\",
				EVPL.PayType_id as \"PayType_id\",
				EVPL.MedicalCareKind_id as \"MedicalCareKind_id\",
				EVPL.ProfGoal_id as \"ProfGoal_id\",
				EVPL.TreatmentClass_id as \"TreatmentClass_id\",
				EVPL.ServiceType_id as \"ServiceType_id\",
				EVPL.VizitClass_id as \"VizitClass_id\",
				EVPL.VizitType_id as \"VizitType_id\",
				EVPL.EvnVizitPL{$prefix}_Time as \"EvnVizitPL{$prefix}_Time\",
				EVPL.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EVPL.Mes_id as \"Mes_id\",
				ROUND(CAST(EVPL.EvnVizitPL{$prefix}_Uet as numeric), 2) as \"EvnVizitPL_Uet\",
				ROUND(CAST(EVPL.EvnVizitPL{$prefix}_UetOMS as numeric), 2) as \"EvnVizitPL_UetOMS\",
				to_char(EVPL.EvnVizitPL{$prefix}_setDate, 'DD.MM.YYYY') as \"EvnVizitPL_setDate\",
				to_char(EVPL.EvnVizitPL{$prefix}_setTime, 'hh24:mi') as \"EvnVizitPL_setTime\",
				EVPL.DispClass_id as \"DispClass_id\",
				EVPL.DispProfGoalType_id as \"DispProfGoalType_id\",
				EVPL.EvnPLDisp_id as \"EvnPLDisp_id\",
				EVPL.PersonDisp_id as \"PersonDisp_id\",
				EVPL.RankinScale_id as \"RankinScale_id\",
				EVPL.EvnVizitPL{$prefix}_IsZNO as \"EvnVizitPL{$prefix}_IsZNO\",
				EVPL.Diag_spid as \"Diag_spid\",
				EVPL.PainIntensity_id as \"PainIntensity_id\",
				EVPL.EvnVizitPL{$prefix}_IsPaid as \"EvnVizitPL{$prefix}_IsPaid\",
				MPreg.MorbusPregnancy_id as \"MorbusPregnancy_id\",
				EL.Evn_lid as \"EvnPL_lid\",
				PEVPL.PregnancyEvnVizitPL_Period as \"PregnancyEvnVizitPL_Period\",
				-- Услуга
				" . ($this->EvnVizitPL_model->isUseVizitCode ? "
				EU.EvnUslugaCommon_id as \"EvnUslugaCommon_id\",
				coalesce(EU.UslugaComplex_uid, EVPL.UslugaComplex_id) as \"UslugaComplex_uid\"
				" : "
				NULL as \"EvnUslugaCommon_id\",
				NULL as \"UslugaComplex_uid\"
				") . "
			from
				v_EvnVizitPL" . $prefix . " EVPL
				LEFT JOIN v_DiagHSNDetails dhd ON dhd.Evn_id = EVPL.EvnVizitPL_id
				LEFT JOIN v_Diag mainDiag ON mainDiag.Diag_id = EVPL.Diag_id
				LEFT JOIN v_Diag complDiag ON complDiag.Diag_id = EVPL.Diag_agid
				left join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_MorbusPregnancy MPreg on MPreg.Evn_pid = EVPL.EvnVizitPL{$prefix}_id
				left join v_PregnancyEvnVizitPL PEVPL on PEVPL.EvnVizitPL_id = EVPL.EvnVizitPL{$prefix}_id
				left join lateral(
					select LpuSection_id
					from v_MedStaffFact SMP
					where SMP.MedStaffFact_id = :MedStaffFact_id
				) SMP on true
				left join lateral(
					select
						EL.Evn_lid
					from
						v_EvnLink EL
					where
						EL.Evn_id = evpl.EvnVizitPL{$prefix}_pid
					limit 1
				) EL on true
				" . $joinQuery . "
			where (1 = 1)
				and EVPL.EvnVizitPL{$prefix}_id = :EvnVizitPL_id
				{$lpuFilter}
			limit 1
		";

		$queryParams = array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedStaffFact_id' => !empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null,
			'isMedStatUser' => isMstatArm($data),
			'isSuperAdmin' => isSuperadmin(),
			'withoutMedPersonal' => ((isLpuAdmin() || isLpuUser()) && $data['session']['medpersonal_id'] == 0)
		);

		//echo getDebugSQL($query, $queryParams); exit;

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnVizitPL_id'])) {
				// получаем схемы
                $resp[0]['DrugTherapyScheme_ids'] = "";
                $resp_scheme = $this->queryResult("
					select
						EvnVizitPLDrugTherapyLink_id as \"EvnVizitPLDrugTherapyLink_id\",
						DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
					from
						v_EvnVizitPLDrugTherapyLink
					where
						EvnVizitPL_id = :EvnVizitPL_id
				", array(
                    'EvnVizitPL_id' => $resp[0]['EvnVizitPL_id']
                ));
                foreach($resp_scheme as $one_scheme) {
					if (!empty($resp[0]['DrugTherapyScheme_ids'])) {
						$resp[0]['DrugTherapyScheme_ids'] .= ",";
					}
					$resp[0]['DrugTherapyScheme_ids'] .= $one_scheme['DrugTherapyScheme_id'];
				}
                if ($this->regionNick === 'kz') {
                    $this->load->model('UslugaMedType_model');

                    $UslugaMedType_id = $this->UslugaMedType_model->getUslugaMedTypeIdByEvnId($resp[0]['EvnVizitPL_id']);
                    if ($UslugaMedType_id) {
                        $resp[0]['UslugaMedType_id'] = $UslugaMedType_id;
                    }
                }
			}
			return $resp;
		}
		else {
			return array('Error_Msg' => 'Ошибка при получении данных для формы редактирования ЛВН', 'success' => false);
		}
	}


	/**
	 *	Получение списка посещений
	 */
	function loadEvnVizitPLGrid($data) {
		$filter = "(1 = 1)";
		$queryParams = [];

		if ( (isset($data['FormType'])) && ($data['FormType'] == 'EvnVizitPLWow') )  {
			$fields = "EVPL.DispWowSpec_id as \"DispWowSpec_id\",";
			$prefix = "WOW";
		}
		else {
			$fields = "EVPL.TimetableGraf_id as \"TimetableGraf_id\",
			EVPL.EvnDirection_id as \"EvnDirection_id\",
			";
			$prefix = "";
		}

		$filter .= " and EVPL.Lpu_id " . getLpuIdFilter($data);

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['MedStaffFact_id'] = !empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null;
		$queryParams['isMedStatUser'] = isMstatArm($data);
		$queryParams['isSuperAdmin'] = isSuperadmin();
		$queryParams['withoutMedPersonal'] = ((isLpuAdmin() || isLpuUser()) && $data['session']['medpersonal_id'] == 0);

		// если не передан родитель, зачем его проверять
		if ( isset($data['EvnVizitPL_id']) ) {
			$filter .= " and EVPL.EvnVizitPL" . $prefix . "_id = :EvnVizitPL_id";
			$queryParams['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
		}
		else if ( isset($data['EvnPL_id']) ) {
			$filter .= " and EVPL.EvnVizitPL" . $prefix . "_pid = :EvnPL_id";
			$queryParams['EvnPL_id'] = $data['EvnPL_id'];
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$access_type = '
			case
				when EVPL.Lpu_id = :Lpu_id and (EVPL.LpuSection_id = SMP.LpuSection_id OR EVPL.MedStaffFact_sid = :MedStaffFact_id) then 1
					' . (count($data['session']['linkedLpuIdList']) > 1 ? '
				when EVPL.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and coalesce(EVPL.EvnVizitPL' . $prefix . '_IsTransit, 1) = 2
					then 1' : '') . '
				when (:isMedStatUser = 1 or :withoutMedPersonal = 1) and EVPL.Lpu_id = :Lpu_id then 1
				when :isSuperAdmin = 1 then 1
					else 0
				end = 1
		';

		if ($this->regionNick == 'pskov') {
			$access_type .= "and coalesce(EVPL.EvnVizitPL{$prefix}_IsPaid, 1) = 1
			 	and not exists(
					select
						RD.Registry_id
					from r60.v_RegistryData RD
						inner join v_Registry R on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EVPL.EvnVizitPL{$prefix}_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
					limit 1
				)
			";
		}

		// https://redmine.swan.perm.ru/issues/28433 условие на accessType
		$query = "
			select
				case
					when {$access_type} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 && !isSuperadmin() ? "and exists (
						select 
							MedStaffFact_id
						from v_MedStaffFact
						where
							MedPersonal_id in (".implode(',',$med_personal_list).")
								and LpuSection_id = EVPL.LpuSection_id
								and WorkData_begDate <= EVPL.EvnVizitPL_setDate
								and (WorkData_endDate is null or WorkData_endDate >= EVPL.EvnVizitPL_setDate)
						limit 1
						)" : "") . "
						then 'edit'
						else 'view'
					end as \"accessType\",
				EVPL.EvnVizitPL" . $prefix . "_id as \"EvnVizitPL_id\",
				EVPL.EvnVizitPL" . $prefix . "_pid as \"EvnPL_id\",
				" . $fields . "
				EVPL.Person_id as \"Person_id\",
				EVPL.PersonEvn_id as \"PersonEvn_id\",
				EVPL.Server_id as \"Server_id\",
				EVPL.DeseaseType_id as \"DeseaseType_id\",
				EVPL.Diag_agid as \"Diag_agid\",
				Diag.Diag_id as \"Diag_id\",
				EVPL.MedStaffFact_id as \"MedStaffFact_id\",
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				MP.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedPersonal_sid as \"MedPersonal_sid\",
				PT.PayType_id as \"PayType_id\",
				EVPL.ProfGoal_id as \"ProfGoal_id\",
				ST.ServiceType_id as \"ServiceType_id\",
				VC.VizitClass_id as \"VizitClass_id\",
				VT.VizitType_id as \"VizitType_id\",
				EVPL.EvnVizitPL" . $prefix . "_AssignedCure as \"EvnVizitPL_AssignedCure\",
				EVPL.EvnVizitPL" . $prefix . "_Examination as \"EvnVizitPL_Examination\",
				EVPL.EvnVizitPL" . $prefix . "_ObjectiveData as \"EvnVizitPL_ObjectiveData\",
				EVPL.EvnVizitPL" . $prefix . "_Recomendations as \"EvnVizitPL_Recomendations\",
				EVPL.EvnVizitPL" . $prefix . "_Time as \"EvnVizitPL_Time\",
				EVPL.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				to_char(EVPL.EvnVizitPL" . $prefix . "_setDate, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\",
				to_char(EVPL.EvnVizitPL" . $prefix . "_setTime, 'hh24:mi') as \"EvnVizitPL_setTime\",
				TRIM(Diag.Diag_Code) as \"Diag_Code\",
				TRIM(Diag.Diag_Name) as \"Diag_Name\",
				LS.LpuSection_Code || '. ' || TRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				TRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				TRIM(PT.PayType_Name) as \"PayType_Name\",
				TRIM(ST.ServiceType_Name) as \"ServiceType_Name\",
				TRIM(VT.VizitType_Name) as \"VizitType_Name\"
			from
				v_EvnVizitPL" . $prefix . " EVPL
				left join Diag on Diag.Diag_id = EVPL.Diag_id
				left join LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVPL.MedPersonal_id
					and MP.Lpu_id = EVPL.Lpu_id
				left join PayType PT on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitClass VC on VC.VizitClass_id = EVPL.VizitClass_id
				left join VizitType VT on VT.VizitType_id = EVPL.VizitType_id
				left join lateral (
					select
						LpuSection_id
					from
						v_MedStaffFact SMP
					where
						SMP.MedStaffFact_id = :MedStaffFact_id
				) SMP on true
			where " . $filter . "
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
	 * @return array|bool
	 */
	function loadEvnVizitPLGridAll($data) {
		$filter = "(1 = 1)";
		$queryParams = array();




		if( ! empty($data['begDate'])){
			$filter .= " and EvnVizitPL_setDate >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}

		if( ! empty($data['endDate'])){
			$filter .= " and EvnVizitPL_setDate <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}

		// Врач посещения
		if( ! empty($data['MedPersonal_id'])){
			$filter .= " and EVPL.MedPersonal_id = :MedPersonal_id";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		// Врач, Место работы
		if( ! empty($data['MedStaffFact_id'])){
			$filter .= " and EVPL.MedStaffFact_id = :MedStaffFact_id";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		// Фамилия
		if( ! empty($data['Person_Surname'])){
			$filter .= " and PS.Person_SurName ilike :Person_Surname";
			$queryParams['Person_Surname'] = rtrim($data['Person_Surname']) . '%';
		}

		// Имя
		if( ! empty($data['Person_Firname'])){
			$filter .= " and PS.Person_FirName ilike :Person_Firname";
			$queryParams['Person_Firname'] = rtrim($data['Person_Firname']) . '%';
		}

		// Отчество
		if( ! empty($data['Person_Secname'])){
			$filter .= " and PS.Person_SecName ilike :Person_Secname";
			$queryParams['Person_Secname'] = rtrim($data['Person_Secname']) . '%';
		}

		// Дата рождения
		if( ! empty($data['Person_Birthday'])){
			$filter .= " and PS.Person_BirthDay = :Person_Birthday";
			$queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}



		if( ! empty($data['Lpu_id'])){
			$filter .= " and EVPL.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}


		$query = "
			select
				-- select
				EVPL.Person_id as \"Person_id\",
				EVPL.PersonEvn_id as \"PersonEvn_id\",
				EVPL.Server_id as \"Server_id\",
				EVPL.EvnVizitPL_pid as \"EvnVizitPL_pid\",
				to_char(EvnVizitPL_setDate, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\",
				to_char(EvnVizitPL_setTime, 'hh24:mi') as \"EvnVizitPL_setTime\",
				coalesce(trim(PS.Person_SurName), '') || ' ' ||  coalesce(trim(PS.Person_FirName), '') || ' ' || coalesce(trim(PS.Person_SecName), '') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",
				LpuSection_Name as \"LpuSection_Name\",
				Person_Fin as \"MedPersonal_Fio\"
				-- end select
			FROM
				-- from
				v_EvnVizitPL EVPL
				left join v_PersonState_All PS on PS.Person_id = EVPL.Person_id
				left join Diag on Diag.Diag_id = EVPL.Diag_id
				left join LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVPL.MedPersonal_id
					and MP.Lpu_id = EVPL.Lpu_id
				left join PayType PT on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitClass VC on VC.VizitClass_id = EVPL.VizitClass_id
				left join VizitType VT on VT.VizitType_id = EVPL.VizitType_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				EVPL.EvnVizitPL_setDate desc, EVPL.EvnVizitPL_setTime desc
				-- end order by
		";

		//echo getDebugSQL($query, $queryParams);die();
		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}

	/**
	 *	Получение данных для формы редактирования стомат. посещения
	 */
	function loadEvnVizitPLStomEditForm($data) {
		$fields = "";
		$joinQuery = "";

		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$joinQuery .= "
				left join lateral(
					select
						t1.EvnUslugaCommon_id,
						t1.UslugaComplex_id as UslugaComplex_uid,
						t1.UslugaComplexTariff_id,
						t1.EvnUslugaCommon_Price
					from
						v_EvnUslugaCommon t1
					where
						t1.EvnUslugaCommon_pid = :EvnVizitPLStom_id
						and coalesce(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
					order by
						t1.EvnUslugaCommon_setDT desc
					limit 1
				) EU on true
			";
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$access_type = '
			case
				when EVPLS.Lpu_id = :Lpu_id and (EVPLS.LpuSection_id = SMP.LpuSection_id OR EVPLS.MedStaffFact_sid = :MedStaffFact_id) then 1
					' . (count($data['session']['linkedLpuIdList']) > 1 ? '
				when EVPLS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and coalesce(EVPLS.EvnVizitPLStom_IsTransit, 1) = 2
					then 1' : '') . '
				when (:isMedStatUser::boolean = TRUE or :withoutMedPersonal::boolean = TRUE) and EVPLS.Lpu_id = :Lpu_id then 1
				when :isSuperAdmin::boolean = TRUE then 1
				else 0
			end = 1
		';

		if ($this->regionNick == 'pskov') {
			$access_type .= "and coalesce(EVPLS.EvnVizitPLStom_IsPaid, 1) = 1
			 	and not exists(
					select
						RD.Registry_id
					from r60.v_RegistryData RD
						inner join v_Registry R on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EVPLS.EvnVizitPLStom_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
					limit 1
				)
			";
		}

		if ($this->regionNick == 'ufa') {
			$fields .= "EPLS.ResultClass_id as \"ResultClass_id\",";
		}

		$add_where = "and (EVPLS.Lpu_id " . getLpuIdFilter($data) . " or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)";
		if(isset($data['fromMZ']) && $data['fromMZ'] == '2')
		{
			$add_where = '';
		}
        if (getRegionNick() === 'kz') {
            $fields .= "
                UMTL.UslugaMedType_id as \"UslugaMedType_id\",
                gbel.PayTypeKAZ_id as \"PayTypeKAZ_id\",
                gbel.VizitActiveType_id as \"VizitActiveType_id\",
				ptkz.PayTypeKAZ_Name as \"PayTypeKAZ_Name\",
		    ";
            $joinQuery .= "
                left join r101.v_UslugaMedTypeLink UMTL on UMTL.Evn_id=EVPLS.EvnVizitPLStom_id
                left join r101.EvnLinkAPP gbel on gbel.Evn_id = EVPLS.EvnVizitPLStom_id
				left join r101.PayTypeKAZ ptkz on ptkz.PayTypeKAZ_id = gbel.PayTypeKAZ_id
            ";
        }


        // https://redmine.swan.perm.ru/issues/28433 условие на accessType
		$query = "
			select
				case
					when {$access_type} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 && !isSuperadmin() ? "and exists (
						select
							MedStaffFact_id
						from v_MedStaffFact
						where MedPersonal_id in (".implode(',',$med_personal_list).")
							and LpuSection_id = EVPLS.LpuSection_id
							and WorkData_begDate <= EVPLS.EvnVizitPLStom_setDate
							and (WorkData_endDate is null or WorkData_endDate >= EVPLS.EvnVizitPLStom_setDate)
						limit 1
						)" : "") . "
							then 'edit'
							else 'view'
					end as \"accessType\",
				coalesce(EVPLS.EvnVizitPLStom_id, 0) as \"EvnVizitPLStom_id\",
				coalesce(EVPLS.EvnVizitPLStom_pid, 0) as \"EvnPLStom_id\",
				EVPLS.EvnDirection_id as \"EvnDirection_id\",
				coalesce(EVPLS.Person_id, 0) as \"Person_id\",
				coalesce(EVPLS.PersonEvn_id, 0) as \"PersonEvn_id\",
				coalesce(EVPLS.Server_id, -1) as \"Server_id\",
				EVPLS.EvnVizitPLStom_NumGroup as \"EvnVizitPLStom_NumGroup\",
				EVPLS.MedStaffFact_id as \"MedStaffFact_id\",
				EVPLS.LpuSection_id as \"LpuSection_id\",
				EVPLS.Lpu_id as \"Lpu_id\",
				EVPLS.DispProfGoalType_id as \"DispProfGoalType_id\",
				EVPLS.MedPersonal_id as \"MedPersonal_id\",
				EVPLS.MedStaffFact_id as \"MedStaffFact_id\",
				EVPLS.MedPersonal_sid as \"MedPersonal_sid\",
				EVPLS.PayType_id as \"PayType_id\",
				EVPLS.LpuDispContract_id as \"LpuDispContract_id\",
				EVPLS.ProfGoal_id as \"ProfGoal_id\",
				EVPLS.ServiceType_id as \"ServiceType_id\",
				EVPLS.VizitType_id as \"VizitType_id\",
				EVPLS.TimetableGraf_id as \"TimetableGraf_id\",
				EVPLS.EvnVizitPLStom_Time as \"EvnVizitPLStom_Time\",
				EVPLS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				ROUND(cast(EVPLS.EvnVizitPLStom_Uet as numeric), 2) as \"EvnVizitPLStom_Uet\",
				ROUND(cast(EVPLS.EvnVizitPLStom_UetOMS as numeric), 2) as \"EvnVizitPLStom_UetOMS\",
				to_char(EPLS.EvnPLStom_setDT, 'dd.mm.yyyy') as \"EvnPLStom_setDate\",
				to_char(EVPLS.EvnVizitPLStom_setDate, 'dd.mm.yyyy') as \"EvnVizitPLStom_setDate\",
				to_char(EVPLS.EvnVizitPLStom_setTime, 'hh24:mi') as \"EvnVizitPLStom_setTime\",
				EVPLS.Diag_id as \"Diag_id\",
				EVPLS.DeseaseType_id as \"DeseaseType_id\",
				v_Tooth.Tooth_Code as \"Tooth_Code\",
				v_Tooth.Tooth_id as \"Tooth_id\",
				EVPLS.EvnVizitPLStom_ToothSurface as \"EvnVizitPLStom_ToothSurface\",
				EVPLS.VizitClass_id as \"VizitClass_id\",
				EVPLS.EvnVizitPLStom_IsPrimaryVizit as \"EvnVizitPLStom_IsPrimaryVizit\",
				EVPLS.DispClass_id as \"DispClass_id\",
				EVPLS.EvnPLDisp_id as \"EvnPLDisp_id\",
				m.Mes_id as \"Mes_id\",
				m.Mes_KoikoDni as \"EvnVizitPLStom_MesUet\",
				EPLS.EvnPLStom_IsFinish as \"EvnPLStom_IsFinish\",
				coalesce(EVPLS.EvnVizitPLStom_IsPaid, 1) as \"EvnVizitPLStom_IsPaid\",
				coalesce(EVPLS.EvnVizitPLStom_IndexRep, 0) as \"EvnVizitPLStom_IndexRep\",
				coalesce(EVPLS.EvnVizitPLStom_IndexRepInReg, 1) as \"EvnVizitPLStom_IndexRepInReg\",
				case
					when EVPLS.EvnVizitPLStom_Index > 0
						then 1
						else 0
					end as \"is_repeat_vizit\",
				EVPLS.TreatmentClass_id as \"TreatmentClass_id\",
				EVPLS.MedicalCareKind_id as \"MedicalCareKind_id\",
				EVPLS.HealthKind_id as \"HealthKind_id\",
				BPD.BitePersonType_id as \"BitePersonType_id\",
				{$fields}
				-- Услуга
				" . ($this->EvnVizitPL_model->isUseVizitCode ? "EU.EvnUslugaCommon_id as \"EvnUslugaStom_id\",
				EU.UslugaComplex_uid as \"UslugaComplex_uid\",
				EU.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				EU.EvnUslugaCommon_Price as \"EvnUslugaStom_UED\"" : "NULL as \"EvnUslugaStom_id\",
				NULL as \"UslugaComplex_uid\",
				NULL as \"UslugaComplexTariff_id\",
				NULL as \"EvnUslugaStom_UED\"" ) . "
			from
				v_EvnVizitPLStom EVPLS
				left join v_EvnPLStom EPLS on EPLS.EvnPLStom_id = EVPLS.EvnVizitPLStom_pid
				left join v_MesOld m on m.Mes_id = EVPLS.Mes_id
				left join v_Tooth on v_Tooth.Tooth_id = EVPLS.Tooth_id
				left join lateral(
					select *
					from v_BitePersonData
					where EvnVizitPLStom_id = EVPLS.EvnVizitPLStom_id
						--and BitePersonData_disDate is null
					order by BitePersonData_updDT DESC
					limit 1
				) BPD on true
				left join lateral (
					select
						LpuSection_id
					from
						v_MedStaffFact SMP
					where
						SMP.MedStaffFact_id = :MedStaffFact_id
				) SMP on true
				" . $joinQuery . "
			where (1 = 1)
				and EVPLS.EvnVizitPLStom_id = :EvnVizitPLStom_id
				{$add_where}
			limit 1
				
		";

		$queryParams = array(
			'EvnVizitPLStom_id' => $data['EvnVizitPLStom_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedStaffFact_id' => !empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null,
			'isMedStatUser' => isMstatArm($data),
			'isSuperAdmin' => isSuperadmin(),
			'withoutMedPersonal' => ((isLpuAdmin() || isLpuUser()) && $data['session']['medpersonal_id'] == 0) ? 1 : 0
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
			if (isset($response[0])) {
				$this->load->model('EvnDiagPLStom_model', 'EvnDiagPLStom_model');
				$dataToothSurface = $this->EvnDiagPLStom_model->processingToothSurface($response[0]['EvnVizitPLStom_ToothSurface'], false);
				$response[0]['ToothSurfaceType_id_list'] = implode(',', $dataToothSurface['ToothSurfaceTypeIdList']);
			}
			return $response;
		}
		else {
			return array('Error_Msg' => 'Ошибка при получении данных для формы редактирования ЛВН', 'success' => false);
		}
	}


	/**
	 *	Получение списка стомат. посещений
	 */
	function loadEvnVizitPLStomGrid($data) {

		$fields = "";
		$joinQuery = "";

		// Тянем код посещения в грид
		$this->load->model('EvnVizitPL_model');
		// (refs #15626)
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$fields .= "UC.UslugaComplex_Code as \"UslugaComplex_Code\",";
			$fields .= "coalesce(UC.UslugaComplex_Code||'. ','') || UC.UslugaComplex_Name as \"UslugaComplex_Name\",";
			$joinQuery = "
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EVPLS.UslugaComplex_id
			";
		}
		else {
			$fields .= "null as \"UslugaComplex_Name\",";
			$joinQuery = "";
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$access_type = '
			case
				when EVPLS.Lpu_id = :Lpu_id and (EVPLS.LpuSection_id = SMP.LpuSection_id OR EVPLS.MedStaffFact_sid = :MedStaffFact_id) then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? '
				when EVPLS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and coalesce(EVPLS.EvnVizitPLStom_IsTransit, 1) = 2
					then 1' : '') . '
				when (:isMedStatUser::boolean = TRUE or :withoutMedPersonal::boolean = TRUE) and EVPLS.Lpu_id = :Lpu_id then 1
				when :isSuperAdmin::boolean = TRUE then 1
				else 0
			end = 1
		';

		if ($this->regionNick == 'pskov') {
			$access_type .= "and coalesce(EVPLS.EvnVizitPLStom_IsPaid, 1) = 1
			 	and not exists(
					select RD.Registry_id
					from r60.v_RegistryData RD
						inner join v_Registry R on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EVPLS.EvnVizitPLStom_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
					limit 1
				)
			";
		}

		// https://redmine.swan.perm.ru/issues/28433 условие на accessType
		$query = "
			select
				case
					when {$access_type} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 && !isSuperadmin() ? "
						and exists (
							select
								MedStaffFact_id
							from v_MedStaffFact
							where MedPersonal_id in (".implode(',',$med_personal_list).")
								and LpuSection_id = EVPLS.LpuSection_id
								and WorkData_begDate <= EVPLS.EvnVizitPLStom_setDate
								and (WorkData_endDate is null or WorkData_endDate >= EVPLS.EvnVizitPLStom_setDate)
						limit 1
						)" : "") . " 
						then 'edit'
						else 'view'
					end as \"accessType\",
				EVPLS.EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
				EVPLS.EvnVizitPLStom_pid as \"EvnPLStom_id\",
				coalesce(EVPLS.EvnVizitPLStom_IsSigned, 1) as \"EvnVizitPLStom_IsSigned\",
				EVPLS.EvnVizitPLStom_NumGroup as \"EvnVizitPLStom_NumGroup\",
				EVPLS.Person_id as \"Person_id\",
				EVPLS.PersonEvn_id as \"PersonEvn_id\",
				EVPLS.Server_id as \"Server_id\",
				to_char(EVPLS.EvnVizitPLStom_setDate, 'dd.mm.yyyy') as \"EvnVizitPLStom_setDate\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				LS.LpuSection_Code || '. ' || TRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				EVPLS.MedStaffFact_id as \"MedStaffFact_id\",
				MP.MedPersonal_id as \"MedPersonal_id\",
				LS.LpuSection_id as \"LpuSection_id\",
				LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				EVPLS.PayType_id as \"PayType_id\",
				EVPLS.TreatmentClass_id as \"TreatmentClass_id\",
				VT.VizitType_SysNick as \"VizitType_SysNick\",
				TRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				TRIM(PT.PayType_Name) as \"PayType_Name\",
				TRIM(ST.ServiceType_Name) as \"ServiceType_Name\",
				{$fields}
				TRIM(VT.VizitType_Name) as \"VizitType_Name\",
				coalesce(LU.LpuUnitSet_Code, 0) as \"LpuUnitSet_Code\",
				ROUND(
					cast(
						coalesce(UETEV.EvnVizitPLStom_Uet, 0) + coalesce(UETV.UslugaComplexTariff_UED, 0) as numeric
					), 2
				) as \"EvnVizitPLStom_Uet\"
			from
				v_EvnVizitPLStom EVPLS
				left join lateral(
					select
						uct.UslugaComplexTariff_UED
					from
						v_EvnUsluga eus
						left join v_UslugaComplexTariff uct on uct.UslugaComplexTariff_id = eus.UslugaComplexTariff_id
					where
						eus.EvnUsluga_pid = evpls.EvnVizitPLStom_id
						and eus.EvnUsluga_IsVizitCode = 2
					limit 1
				) UETV on true
				left join v_LpuSection LS on LS.LpuSection_id = EVPLS.LpuSection_id
				left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = EVPLS.LpuSectionProfile_id
				left join lateral(
					select
						 MedPersonal_id
						,Person_Fio
					from v_MedPersonal
					where MedPersonal_id = EVPLS.MedPersonal_id
						and Lpu_id = EVPLS.Lpu_id
					limit 1
				) MP on true
				left join lateral(
					select
						SUM(case
							when coalesce(EDPLS.EvnDiagPLStom_IsClosed, 1) = 1
								then 0
							when EDPLS.Mes_id is not null
								then mes.Mes_KoikoDni -- УЕТ (норматив по КСГ)
							else coalesce(UET1.EvnUslugaStom_Summa, 0) + coalesce(UET2.EvnUslugaStom_Summa, 0) -- УЕТ (факт по ОМС)
						end) as EvnVizitPLStom_Uet
					from
						v_EvnDiagPLStom EDPLS
						left join v_MesOld mes on mes.Mes_id = EDPLS.Mes_id
						left join lateral(
							select
								SUM(eus.EvnUslugaStom_Summa) as EvnUslugaStom_Summa
							from
								v_EvnUslugaStom eus
								inner join v_PayType pt on pt.PayType_id = eus.PayType_id and pt.PayType_SysNick = 'oms'
							where
								eus.EvnDiagPLStom_id = EDPLS.EvnDiagPLStom_id
								and coalesce(eus.EvnUslugaStom_IsAllMorbus, 1) = 1
								and coalesce(eus.EvnUslugaStom_IsVizitCode, 1) = 1
						) UET1 on true
						left join lateral(
							select
								SUM(eus.EvnUslugaStom_Summa) as EvnUslugaStom_Summa
							from
								v_EvnUslugaStom eus
								inner join v_PayType pt on pt.PayType_id = eus.PayType_id and pt.PayType_SysNick = 'oms'
							where
								eus.EvnUslugaStom_rid = EDPLS.EvnDiagPLStom_rid
								and eus.EvnUslugaStom_IsAllMorbus = 2
								and coalesce(eus.EvnUslugaStom_IsVizitCode, 1) = 1
						) UET2 on true
					where
						EDPLS.EvnDiagPLStom_pid = EVPLS.EvnVizitPLStom_id
				) UETEV on true
				left join lateral (
					select
						LpuSection_id
					from
						v_MedStaffFact SMP
					where
						SMP.MedStaffFact_id = :MedStaffFact_id
				) SMP on true
				left join v_PayType PT on PT.PayType_id = EVPLS.PayType_id
				left join v_ServiceType ST on ST.ServiceType_id = EVPLS.ServiceType_id
				left join v_VizitType VT on VT.VizitType_id = EVPLS.VizitType_id
				left join v_Diag D on D.Diag_id = EVPLS.Diag_id
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				{$joinQuery}
			where (1 = 1)
				and EVPLS.Lpu_id " . getLpuIdFilter($data) . "
				and EVPLS.EvnVizitPLStom_pid = :EvnVizitPLStom_pid
		";

		$queryParams = array(
			'EvnVizitPLStom_pid' => $data['EvnVizitPLStom_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'MedStaffFact_id' => !empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null,
			'isMedStatUser' => isMstatArm($data),
			'isSuperAdmin' => isSuperadmin(),
			'withoutMedPersonal' => ((isLpuAdmin() || isLpuUser()) && $data['session']['medpersonal_id'] == 0)
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение идешника последнего стоматологического посещения
	 */
	function loadLastEvnPLStomData($data) {
		return $this->getFirstRowFromQuery("
			select
            	EvnVizitPLStom_id as \"EvnVizitPLStom_id\",
            	EvnVizitPLStom_pid as \"EvnPLStom_id\"
			from v_EvnVizitPLStom
			where Person_id = :Person_id
			order by EvnVizitPLStom_setDT desc
			limit 1
		", array('Person_id' => $data['Person_id']));
	}


	/**
	 *	Получение данных по первому посещению в рамках ТАП
	 */
	function loadFirstEvnVizitPLData($data) {
		$joinQuery = "";

		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$joinQuery .= "
				left join lateral(
					select
						t1.UslugaComplex_id as UslugaComplex_uid
					from
						v_EvnUslugaCommon t1
						left join v_UslugaComplex t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
						left join v_UslugaCategory t3 on t3.UslugaCategory_id = t2.UslugaCategory_id
					where
						t1.EvnUslugaCommon_pid = EVPL.EvnVizitPL_id
						and coalesce(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
					order by
						t1.EvnUslugaCommon_setDT desc
					limit 1
				) EU on true
			";
		}

		$query = "
			select
				EVPL.DeseaseType_id as \"DeseaseType_id\",
				EVPL.Diag_id as \"Diag_id\",
				EVPL.LpuSection_id as \"LpuSection_id\",
				LU.LpuBuilding_id as \"LpuBuilding_id\",
				LU.LpuUnit_id as \"LpuUnit_id\",
				LU.LpuUnitSet_id as \"LpuUnitSet_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedPersonal_sid as \"MedPersonal_sid\",
				EVPL.PayType_id as \"PayType_id\",
				EVPL.ServiceType_id as \"ServiceType_id\",
				EVPL.VizitType_id as \"VizitType_id\",
				EVPL.Mes_id, as \"Mes_id\",
				-- Услуга
				" . ($this->EvnVizitPL_model->isUseVizitCode ? "EU.UslugaComplex_uid as \"UslugaComplex_uid\"" : "NULL as \"UslugaComplex_uid\"") . "
			from
				v_EvnVizitPL EVPL
				left join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				" . $joinQuery . "
			where
				EVPL.EvnVizitPL_pid = :EvnVizitPL_pid
				and EVPL.EvnVizitPL_Index = 0
			limit 1
		";
		$result = $this->db->query($query, array('EvnVizitPL_pid' => $data['EvnVizitPL_pid']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных по предыдущему посещению в рамках ТАП
	 */
	function loadLastEvnVizitPLData($data) {
		$joinQuery = "";
		$selectQuery = "";
		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$joinQuery .= "
				left join lateral(
					select
						t1.UslugaComplex_id as UslugaComplex_uid
					from
						v_EvnUslugaCommon t1
						left join v_UslugaComplex t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
						left join v_UslugaCategory t3 on t3.UslugaCategory_id = t2.UslugaCategory_id
					where
						t1.EvnUslugaCommon_pid = EVPL.EvnVizitPL_id
						and coalesce(t1.EvnUslugaCommon_IsVizitCode, 1) = 2
					order by
						t1.EvnUslugaCommon_setDT desc
					limit 1
				) EU on true
			";
		}
		if (getRegionNick() == 'kareliya') {
			$region_id = getRegionNumber();
			$joinQuery .= "
				left join lateral(
					select *
					from VizitType
					where 
						Region_id = {$region_id}
						or Region_id is null
				) VT on true
			";

			$selectQuery .= "VT.VizitType_Code as \"VizitType_Code\",";
		}

		$query = "
			select
				EVPL.DeseaseType_id as \"DeseaseType_id\",
				EVPL.Diag_id as \"Diag_id\",
				EVPL.LpuSection_id as \"LpuSection_id\",
				to_char(EVPL.EvnVizitPL_setDate, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\",
				LU.LpuBuilding_id as \"LpuBuilding_id\",
				LU.LpuUnit_id as \"LpuUnit_id\",
				LU.LpuUnitSet_id as \"LpuUnitSet_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedStaffFact_id as \"MedStaffFact_id\",
				EVPL.MedPersonal_sid as \"MedPersonal_sid\",
				EVPL.PayType_id as \"PayType_id\",
				EVPL.ServiceType_id as \"ServiceType_id\",
				EVPL.VizitType_id as \"VizitType_id\",
				{$selectQuery}
				EVPL.Mes_id as \"Mes_id\",
				-- Услуга
				" . ($this->EvnVizitPL_model->isUseVizitCode ? "EU.UslugaComplex_uid as \"UslugaComplex_uid\"" : "NULL as \"UslugaComplex_uid\"") . "
			from
				v_EvnVizitPL EVPL
				left join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				" . $joinQuery . "
			where
				EVPL.EvnVizitPL_pid = :EvnVizitPL_pid
			order by
				EVPL.EvnVizitPL_setDT desc
			limit 1
		";

		//echo getDebugSQL($query, array('EvnVizitPL_pid' => $data['EvnVizitPL_pid']));die;
		$result = $this->db->query($query, array('EvnVizitPL_pid' => $data['EvnVizitPL_pid']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Получение данных по предыдущему стомат. посещению в рамках стомат. ТАП
	 */
	function loadLastEvnVizitPLStomData($data) {
		$joinQuery = "";

		$this->load->model('EvnVizitPL_model');
		if ( $this->EvnVizitPL_model->isUseVizitCode ) {
			$joinQuery .= "
				left join lateral(
					select
						t1.UslugaComplex_id as UslugaComplex_uid
					from
						v_EvnUsluga t1
						left join v_UslugaComplex t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
						left join v_UslugaCategory t3 on t3.UslugaCategory_id = t2.UslugaCategory_id
					where
						t1.EvnUsluga_pid = EVPLS.EvnVizitPLStom_id
						and coalesce(t1.EvnUsluga_IsVizitCode, 1) = 2
					order by
						t1.EvnUsluga_setDT desc
					limit 1
				) EU on true
			";
		}

		$query = "
			select
				EVPLS.DeseaseType_id as \"DeseaseType_id\",
				EVPLS.Diag_id as \"Diag_id\",
				EVPLS.LpuSection_id as \"LpuSection_id\",
				EVPLS.MedPersonal_id as \"MedPersonal_id\",
				EVPLS.MedStaffFact_id as \"MedStaffFact_id\",
				EVPLS.MedPersonal_sid as \"MedPersonal_sid\",
				EVPLS.PayType_id as \"PayType_id\",
				EVPLS.ServiceType_id as \"ServiceType_id\",
				EVPLS.VizitType_id as \"VizitType_id\",
				v_Tooth.Tooth_Code as \"Tooth_Code\",
				v_Tooth.Tooth_id as \"Tooth_id\",
				EVPLS.EvnVizitPLStom_ToothSurface as \"EvnVizitPLStom_ToothSurface\",
				EVPLS.Mes_id as \"Mes_id\",
                v_EvnPLStom.EvnPLStom_isFinish as \"EvnPLStom_isFinish\",
                BPD.BitePersonType_id as \"BitePersonType_id\",
				-- Услуга
				" . ($this->EvnVizitPL_model->isUseVizitCode ? "EU.UslugaComplex_uid as \"UslugaComplex_uid\"" : "NULL as \"UslugaComplex_uid\"") . "
			from
				v_EvnVizitPLStom EVPLS
				left join v_Tooth on v_Tooth.Tooth_id = EVPLS.Tooth_id
				left join v_EvnPLStom on v_EvnPLStom.EvnPLStom_id = EVPLS.EvnVizitPLStom_pid
				left join lateral(
					select *
					from v_BitePersonData
					where Person_id = EVPLS.Person_id and BitePersonData_disDate is null
					limit 1
				) BPD on true
				" . $joinQuery . "
			where
				EVPLS.EvnVizitPLStom_pid = :EvnVizitPLStom_pid
			order by
				EVPLS.EvnVizitPLStom_setDT desc
			limit 1
		";
		$result = $this->db->query($query, array('EvnVizitPLStom_pid' => $data['EvnVizitPLStom_pid']));

		if ( is_object($result) ) {
			$response = $result->result('array');
			if (isset($response[0])) {
				$this->load->model('EvnDiagPLStom_model', 'EvnDiagPLStom_model');
				$dataToothSurface = $this->EvnDiagPLStom_model->processingToothSurface($response[0]['EvnVizitPLStom_ToothSurface'], false);
				$response[0]['ToothSurfaceType_id_list'] = implode(',', $dataToothSurface['ToothSurfaceTypeIdList']);
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных посещения для копирования
	 *
	 * в проекте не находятся использования метода
	 */
	/*function getEvnVizitPLforCopy($data)
	{
		$query = "
			select * from v_EvnVizitPL where EvnVizitPL_id = :EvnVizitPL_id
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}*/

	/**
	 *	https://redmine.swan.perm.ru/issues/15931
	 */
	function loadMesOldList($data) {
		$filter = '';

		if (!empty($data['EvnVizitPLStom_id'])) { // КСГ на дату последнего посещения в ТАП.
			$filter_check = "";
			if (!empty($data['EvnVizit_setDate'])) {
				$filter_check .= " and ev2.EvnVizit_setDate >= :EvnVizit_setDate";
			}
			$resp = $this->queryResult("
				select
					to_char(ev2.EvnVizit_setDate, 'yyyy-mm-dd') as \"EvnVizit_setDate\"
				from
					v_EvnVizit ev
					inner join v_EvnVizit ev2 on ev2.EvnVizit_pid = ev.EvnVizit_pid and ev2.EvnVizit_id <> ev.EvnVizit_id
				where
					ev.EvnVizit_id = :EvnVizitPLStom_id
					{$filter_check}
				order by
					ev2.EvnVizit_setDate desc
				limit 1
			", array(
				'EvnVizitPLStom_id' => $data['EvnVizitPLStom_id'],
				'EvnVizit_setDate' => $data['EvnVizit_setDate']
			));

			if (!empty($resp[0]['EvnVizit_setDate'])) {
				$data['EvnVizit_setDate'] = $resp[0]['EvnVizit_setDate'];
			}
		}

		if ( !empty($data['mode']) && $data['mode'] == 'morbus' && !empty($data['EvnVizitPLStom_id']) && $this->getRegionNick() == 'astra' ) {
			return $this->loadMesOldAstraList($data);
		}

		if ( !empty($data['mode']) && $data['mode'] == 'morbus' && !empty($data['EvnVizitPLStom_id']) && $this->getRegionNick() == 'vologda' ) {
			return $this->loadMesOldVologdaList($data);
		}

		if ( !empty($data['mode']) && $data['mode'] == 'morbus' ) {
			$query = "
				with myvars as (
					select
						date_part('year', CAST(:EvnVizit_setDate as date)) - date_part('year', Person_BirthDay) as age
					from v_PersonState
					where Person_id = :Person_id
				)
				select
					 m.Mes_id as \"Mes_id\",
					 m.Mes_Code as \"Mes_Code\",
					 m.Mes_Name as \"Mes_Name\",
					 m.Mes_KoikoDni as \"Mes_KoikoDni\",
					 case
						when m.MesAgeGroup_id = 1
							then 'Взрослые'
						when m.MesAgeGroup_id = 2
							then 'Дети'
						else ''
					 end as \"MesAgeGroup_Name\",
					 mck.MedicalCareKind_Name as \"MedicalCareKind_Name\",
					 1 as \"MesNewUslovie\", -- признак выполнения нового условия по МЭСам
					 m.Mes_VizitNumber as \"Mes_VizitNumber\",
					 m.MesOld_IsNeedTooth as \"MesOld_IsNeedTooth\"
				from v_MesOld m
					left join v_MedicalCareKind mck on mck.MedicalCareKind_id = m.MedicalCareKind_id
					left join v_LpuSection ls on ls.LpuSection_id = :LpuSection_id
					left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitTypeMedicalCareKindLink lutmckl on lutmckl.LpuUnitType_id = lu.LpuUnitType_id
						and lutmckl.MedicalCareKind_id = m.MedicalCareKind_id
				where (
					(
						-- Грузим взрослый КСГ, если пациенту 18 лет и более
						(m.MesAgeGroup_id = 1 and (select age from myvars) >= 18)
						-- Грузим детский КСГ, если пациенту меньше 18 лет
						or (m.MesAgeGroup_id = 2 and (select age from myvars) < 18)
						or m.MesAgeGroup_id is null
					)
					and m.Lpu_id is null
					and m.Diag_id = :Diag_id
					and m.Mes_begDT <= :EvnVizit_setDate
					and (m.Mes_endDT is null or m.Mes_endDT >= :EvnVizit_setDate)
					and m.MesType_id = 7
				)
				order by
					m.Mes_Code
			";
		}
		else {
			$query = "
				with myvars as (
					select
						date_part('year', CAST(:EvnVizit_setDate as date)) - date_part('year', Person_BirthDay) as age
					from v_PersonState
					where Person_id = :Person_id
				)
				select
					 m.Mes_id as \"Mes_id\",
					 m.Mes_Code as \"Mes_Code\",
					 m.Mes_Name as \"Mes_Name\",
					 m.Mes_KoikoDni as \"Mes_KoikoDni\",
					 case
						when m.MesAgeGroup_id = 1
							then 'Взрослые'
						when m.MesAgeGroup_id = 2
							then 'Дети'
						else ''
					 end as \"MesAgeGroup_Name\",
					 mck.MedicalCareKind_Name as \"MedicalCareKind_Name\",
					 1 as \"MesNewUslovie\", --признак выполнения нового условия по МЭСам
					 m.Mes_VizitNumber as \"Mes_VizitNumber\"
				from v_MesOld m
					inner join v_MedicalCareKind mck on mck.MedicalCareKind_id = m.MedicalCareKind_id
					inner join v_LpuSection ls on ls.LpuSection_id = :LpuSection_id
					inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					inner join v_LpuUnitTypeMedicalCareKindLink lutmckl on lutmckl.LpuUnitType_id = lu.LpuUnitType_id
						and lutmckl.MedicalCareKind_id = m.MedicalCareKind_id
				where (
					(
						-- https://redmine.swan.perm.ru/issues/16158
						-- Грузим взрослый МЭС, если пациенту 18 лет и более
						(m.MesAgeGroup_id = 1 and (select age from myvars) >= 18)
						-- Грузим детский МЭС, если пациенту меньше 18 лет
						or (m.MesAgeGroup_id = 2 and (select age from myvars) < 18)
						or m.MesAgeGroup_id is null
					)
					and m.Lpu_id is null
					and m.Diag_id = :Diag_id
					and m.Mes_begDT <= :EvnVizit_setDate
					and (m.Mes_endDT is null or m.Mes_endDT >= :EvnVizit_setDate)
					and exists (
						select ProfileMesProf_id
						from ProfileMesProf
						where MesProf_id = m.MesProf_id
							and LpuSectionProfile_id = ls.LpuSectionProfile_id
							and (ProfileMesProf_begDT is null or (ProfileMesProf_begDT <= :EvnVizit_setDate))
							and (ProfileMesProf_endDT is null or (ProfileMesProf_endDT >= :EvnVizit_setDate))
					)
					and m.MesLevel_id = ls.MesLevel_id
				)
				order by
					m.Mes_Code
			";
		}

		// die(getDebugSql($query, $queryParams));

		$result = $this->db->query($query, array(
			'Diag_id' => $data['Diag_id'],
			'EvnVizit_id' => $data['EvnVizit_id'],
			'EvnVizit_setDate' => $data['EvnVizit_setDate'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Person_id' => $data['Person_id']
		));

		if ( is_object($result) ) {
            return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка списка МЭС для стомат посещений Астрахани
	 */
	function loadMesOldAstraList($data) {

		$queryParams = array(
			'Diag_id' => $data['Diag_id'],
			'EvnVizit_id' => $data['EvnVizit_id'],
			'EvnVizit_setDate' => $data['EvnVizit_setDate'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id']
		);

		// вариант 1, только по диагнозу
		$query = "
			select
				 m.Mes_id as \"Mes_id\",
				 m.Mes_Code as \"Mes_Code\",
				 m.Mes_Name as \"Mes_Name\",
				 '' as \"Mes_KoikoDni\",
				 case
					when m.MesAgeGroup_id = 1
						then 'Взрослые'
					when m.MesAgeGroup_id = 2
						then 'Дети'
					else ''
				 end as \"MesAgeGroup_Name\",
				 mck.MedicalCareKind_Name as \"MedicalCareKind_Name\",
				 1 as \"MesNewUslovie\", -- признак выполнения нового условия по МЭСам
				 m.Mes_VizitNumber as \"Mes_VizitNumber\",
				 m.MesOld_IsNeedTooth as \"MesOld_IsNeedTooth\"
			from v_MesOld m
				inner join v_MesOldDiag md on md.Mes_id = m.Mes_id
				left join v_MedicalCareKind mck on mck.MedicalCareKind_id = m.MedicalCareKind_id
			where
				md.Diag_id = :Diag_id
				and m.Mes_begDT <= :EvnVizit_setDate
				and (m.Mes_endDT is null or m.Mes_endDT >= :EvnVizit_setDate)
				and m.MesType_id = 7
			order by
				m.Mes_Code
		";

		//die(getDebugSql($query, $queryParams));

		$resp = $this->queryResult($query, $queryParams);

		if (count($resp) == 1) {
			return $resp;
		}

		// вариант 2, по диагнозу и профилю
		$query ="
			with myvars as (
				select
					lsp.LpuSectionProfile_id
				from v_LpuSectionProfile lsp 
					inner join v_EvnVizitPLStom evs on evs.LpuSectionProfile_id = lsp.LpuSectionProfile_id
				where lsp.LpuSectionProfile_Code in ('88', '89', '90') 
					and evs.EvnVizitPLStom_id = :EvnVizit_id
			), x as (
				select
					case when (select LpuSectionProfile_id from myvars) is nut null
						then (select LpuSectionProfile_id from myvars)
						else (select LpuSectionProfile_id from v_LpuSectionProfile where LpuSectionProfile_Code = '89' limit 1)
					end as LpuSectionProfile_id
			)

			select
				 m.Mes_id as \"Mes_id\",
				 m.Mes_Code as \"Mes_Code\",
				 m.Mes_Name as \"Mes_Name\",
				 '' as \"Mes_KoikoDni\",
				 case
					when m.MesAgeGroup_id = 1
						then 'Взрослые'
					when m.MesAgeGroup_id = 2
						then 'Дети'
					else ''
				 end as \"MesAgeGroup_Name\",
				 mck.MedicalCareKind_Name as \"MedicalCareKind_Name\",
				 1 as \"MesNewUslovie\", -- признак выполнения нового условия по МЭСам
				 m.Mes_VizitNumber as \"Mes_VizitNumber\",
				 m.MesOld_IsNeedTooth as \"MesOld_IsNeedTooth\"
			from v_MesOld m
				inner join v_MesOldDiag md on md.Mes_id = m.Mes_id
				left join v_MedicalCareKind mck on mck.MedicalCareKind_id = m.MedicalCareKind_id
				left join v_MesTariff mt on mt.Mes_id = m.Mes_id
					and mt.MesPayType_id = 11 
					and mt.MesTariff_begDT <= :EvnVizit_setDate
					and (coalesce(mt.MesTariff_endDT, :EvnVizit_setDate)>= :EvnVizit_setDate)
			where
				md.Diag_id = :Diag_id
				and m.Mes_begDT <= :EvnVizit_setDate
				and (m.Mes_endDT is null or m.Mes_endDT >= :EvnVizit_setDate)
				and m.MesType_id = 7
				and m.LpuSectionProfile_id = (select LpuSectionProfile_id from x)
			order by
				mt.MesTariff_Value desc
		";

		$resp = $this->queryResult($query, $queryParams);

		return $resp;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function loadMesOldVologdaList($data) {
		$params = array(
			'Diag_id' => $data['Diag_id'],
			'EvnVizit_setDate' => $data['EvnVizit_setDate'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'Person_id' => $data['Person_id'],
		);

		$query = "
			with myvars as (
				select
					date_part('year', CAST(:EvnVizit_setDate as date)) - date_part('year', Person_BirthDay) as age
				from v_PersonState
				where Person_id = :Person_id
			), mv1 as (
				select
					case
						when (select age from myvars) < 18
							then 2
							else 1
					end as MedAgeGroup_id		
			), mv2 as (
				select
					Post_id
				from v_MedStaffFact
				where MedStaffFact_id = :MedStaffFact_id
				limit 1
			)
			
			select distinct
				m.Mes_id as \"Mes_id\",
				m.Mes_Code as \"Mes_Code\",
				m.Mes_Name as \"Mes_Name\",
				'' as \"Mes_KoikoDni\",
				case
					when muc.MesAgeGroup_id = 1
						then 'Взрослые'
					when muc.MesAgeGroup_id = 2
						then 'Дети'
					else ''
				end as \"MesAgeGroup_Name\",
				mck.MedicalCareKind_Name as \"MedicalCareKind_Name\",
				1 as \"MesNewUslovie\", --признак выполнения нового условия по МЭСам
				m.Mes_VizitNumber as \"Mes_VizitNumber\",
				m.MesOld_IsNeedTooth as \"MesOld_IsNeedTooth\"
			from
				v_MesOld m
					inner join v_MesOldUslugaComplex muc on muc.Mes_id = m.Mes_id
					left join v_MedicalCareKind mck on mck.MedicalCareKind_id = m.MedicalCareKind_id
			where
				muc.Diag_id = :Diag_id
				and m.Mes_begDT <= :EvnVizit_setDate
				and (m.Mes_endDT is null or m.Mes_endDT >= :EvnVizit_setDate)
				and m.MesType_id = 7
				and muc.MesAgeGroup_id = (select MedAgeGroup_id from mv1)
				and coalesce(muc.Post_id, (select Post_id from mv2)) = (select Post_id from mv2)
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка списка МЭС для стомат посещений Екатеринбурга
	 */
	function loadMesOldEkbList($data) {
		$where = '(1=1)';
		$params = array();

		if (!empty($data['Mes_id'])) {
			$where .= " and m.Mes_id = :Mes_id";
			$params['Mes_id'] = $data['Mes_id'];
		}

		if (!empty($data['query'])) {
			$where .= " and m.Mes_Name ilike :Mes_Name";
			$params['Mes_Name'] = '%' . $data['query'] . '%';
		}

		if (!empty($data['MesType_id'])) {
			$where .= " and m.MesType_id = :MesType_id";
			$params['MesType_id'] = $data['MesType_id'];
		}

		if (!empty($data['Mes_Date'])) {
			$where .= " and (m.Mes_begDT is null or m.Mes_begDT <= :Mes_Date)";
			$where .= " and (m.Mes_endDT is null or m.Mes_endDT >= :Mes_Date)";
			$params['Mes_Date'] = $data['Mes_Date'];
		}

		if (!empty($data['Mes_Codes'])) {
			$mes_codes = json_decode($data['Mes_Codes'], true);
			$mes_codes_str = "'".implode("','", $mes_codes)."'";
			$where .= " and m.Mes_Code in ({$mes_codes_str})";
		}

		if (!empty($data['UslugaComplex_id'])) {
			$where .= " and exists(
				select
					mu.UslugaComplex_id
				from
					v_MesUsluga mu
				where
					mu.Mes_id = m.Mes_id
					and mu.UslugaComplex_id = :UslugaComplex_id
					" . (!empty($data['Mes_Date']) ? "and (mu.MesUsluga_begDT is null or mu.MesUsluga_begDT <= :Mes_Date)" : "") . "
					" . (!empty($data['Mes_Date']) ? "and (mu.MesUsluga_endDT is null or mu.MesUsluga_endDT >= :Mes_Date)" : "") . "
				limit 1
			)";
			$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		if (!empty($data['EvnLabRequest_id'])) {
			$where .= " and exists(
				select
					mu.UslugaComplex_id
				from
					v_EvnLabRequest elr
					inner join v_EvnUslugaPar eup on eup.EvnDirection_id = elr.EvnDirection_id
					inner join v_MesUsluga mu on mu.UslugaComplex_id = eup.UslugaComplex_id
				where
					mu.Mes_id = m.Mes_id
					and elr.EvnLabRequest_id = :EvnLabRequest_id
				limit 1
				union all
				
				select
					mu.UslugaComplex_id
				from
					v_EvnLabRequest elr
					inner join v_EvnUslugaPar eup on eup.EvnDirection_id = elr.EvnDirection_id
					inner join v_EvnUslugaPar eup_child on eup_child.EvnUslugaPar_pid = eup.EvnUslugaPar_id
					inner join v_MesUsluga mu on mu.UslugaComplex_id = eup_child.UslugaComplex_id
				where
					mu.Mes_id = m.Mes_id
					and elr.EvnLabRequest_id = :EvnLabRequest_id
				limit 1
			)";
			$params['EvnLabRequest_id'] = $data['EvnLabRequest_id'];
		}

		$query = "
			select
				m.Mes_id as \"Mes_id\",
				m.Mes_Code as \"Mes_Code\",
				m.Mes_Name as \"Mes_Name\"
			from
				v_MesOld m
			where
				{$where}
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 *	Получение данных по зубной карте
	 */
	function loadToothCard($data) {
		$queryParams = array(
			'EvnVizitPLStom_id' => $data['EvnVizitPLStom_id'],
            'Person_id' => $data['Person_id']
		);
		$toothCard = array();

		// Пока закрываю, ибо https://redmine.swan.perm.ru/issues/34482 и https://redmine.swan.perm.ru/issues/26533
		//запрос не переписан
		/*if ( !empty($data['EvnVizitPLStom_id']) ) {
			$query = "
				select
					 tc.ToothCard_NumTooth as Tooth_Num
					,tc.ToothCard_id
					,tc.ToothType_id
					,tt.ToothType_Code
					,tt.ToothType_Name
					,'' as ToothState_Values
				from v_ToothCard tc
					inner join v_ToothType tt on tt.ToothType_id = tc.ToothType_id
				where tc.EvnVizitPLStom_id = :EvnVizitPLStom_id
			";
		}
		else {
			$query = "
				select
					 tc.ToothCard_NumTooth as Tooth_Num
					,tc.ToothCard_id
					,tc.ToothType_id
					,tt.ToothType_Code
					,tt.ToothType_Name
					,'' as ToothState_Values
				from v_ToothCard tc
					inner join v_ToothType tt on tt.ToothType_id = tc.ToothType_id
				where tc.EvnVizitPLStom_id = (
					select top 1 EvnVizitPLStom_id
					from v_EvnVizitPLStom
					where Person_id = :Person_id
					order by EvnVizitPLStom_setDT desc
				)
			";
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$toothCard = $result->result('array');

			if ( is_array($toothCard) && count($toothCard) > 0 ) {
				$toothCardIdArray = array();
				$toothStateValues = array();

				// Тянем идентификаторы ToothCard_id
				foreach ( $toothCard as $row ) {
					$toothCardIdArray[] = $row['ToothCard_id'];
				}

				$query = "
					select ToothCard_id, ToothStateClass_id
					from v_ToothState
					where ToothCard_id in (" . implode(", ", $toothCardIdArray) . ")
				";
				$result = $this->db->query($query);

				if ( is_object($result) ) {
					$response = $result->result('array');

					if ( is_array($response) && count($response) > 0 ) {
						foreach ( $response as $row ) {
							if ( !array_key_exists($row['ToothCard_id'], $toothStateValues) ) {
								$toothStateValues[$row['ToothCard_id']] = array();
							}

							$toothStateValues[$row['ToothCard_id']][] = $row['ToothStateClass_id'];
						}
					}
				}

				// Цепляем данные из ToothState к ToothCard
				foreach ( $toothCard as $key => $row ) {
					$toothCard[$key]['ToothState_Values'] = json_encode(array_key_exists($row['ToothCard_id'], $toothStateValues) ? $toothStateValues[$row['ToothCard_id']] : array());
				}
			}
		}*/

		return $toothCard;
	}


	/**
	 *	Получение данных по зубной карте для спец. маркера
	 */
	function getToothCard($data) {
		$queryParams = array(
			'EvnVizitPLStom_id' => $data['EvnVizitPLStom_id']
		);
		$toothCard = array();

		for ( $i = 1; $i <= 4; $i++ ) {
			for ( $j = 1; $j <= 8; $j++ ) {
				$toothCard['Tooth_' . $i . $j] = '&nbsp;';
			}
		}

		// Пока закрываю, ибо https://redmine.swan.perm.ru/issues/34482 и https://redmine.swan.perm.ru/issues/26533
		//запрос не переписан
		/*$query = "
			select
				 tc.ToothCard_NumTooth
				,tt.ToothType_Code
				,tsc.ToothStateClass_List
			from v_ToothCard tc
				left join v_ToothType tt on tt.ToothType_id = tc.ToothType_id
				left join lateral(
					SELECT
						STUFF((
							select
								',' || ToothStateClass_Code
							from
								v_ToothState t1
								inner join v_ToothStateClass t2 on t2.ToothStateClass_id = t1.ToothStateClass_id
							where
								t1.ToothCard_id = tc.ToothCard_id
							FOR XML PATH ('')
						), 1, 1, '') as ToothStateClass_List
				) tsc on true
			where tc.EvnVizitPLStom_id = :EvnVizitPLStom_id
		";
		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				foreach ( $response as $array ) {
					if ( empty($array['ToothType_Code']) ) {
						$toothCard['Tooth_' . $array['ToothCard_NumTooth']] = '&nbsp;';
					}
					else if ( $array['ToothType_Code'] == 3 ) {
						$toothCard['Tooth_' . $array['ToothCard_NumTooth']] = 'О';
					}
					else if ( $array['ToothType_Code'] == 4 ) {
						$toothCard['Tooth_' . $array['ToothCard_NumTooth']] = 'И';
					}
					else if ( !empty($array['ToothStateClass_List']) ) {
						$toothCard['Tooth_' . $array['ToothCard_NumTooth']] = $array['ToothStateClass_List'];
					}
					else if ( $array['ToothType_Code'] == 2 ) {
						$toothCard['Tooth_' . $array['ToothCard_NumTooth']] = strval(intval(substr($array['ToothCard_NumTooth'], 0, 1)) || 4) . substr($array['ToothCard_NumTooth'], 1, 1);
					}
					else {
						$toothCard['Tooth_' . $array['ToothCard_NumTooth']] = $array['ToothCard_NumTooth'];
					}
				}
			}
		}*/

		return $toothCard;
	}

	/**
	 * loadEvnVizitCombo
	 * @param $data
	 * @return bool
	 */
	function loadEvnVizitCombo($data) {
		$query = "
			select
				EV.EvnVizit_id as \"Evn_id\",
				EV.EvnVizit_pid as \"Evn_pid\",
				EV.EvnVizit_rid as \"Evn_rid\",
				EV.LpuSection_id as \"LpuSection_id\",
				EV.MedStaffFact_id as \"MedStaffFact_id\",
				EV.MedPersonal_id as \"MedPersonal_id\",
				EV.PayType_id as \"PayType_id\",
				to_char(EV.EvnVizit_setDT, 'dd.mm.yyyy') as \"Evn_setDate\",
				to_char(EV.EvnVizit_setDT, 'HH24:MI') as \"Evn_setTime\",
				TRIM(coalesce(LS.LpuSection_Name, '')) as \"LpuSection_Name\",
				TRIM(coalesce(MP.Person_Fio, '')) as \"MedPersonal_Fio\",
				to_char(EV.EvnVizit_setDT, 'dd.mm.yyyy') || ' / ' || TRIM(coalesce(LS.LpuSection_Name, '')) || ' / ' || TRIM(coalesce(MP.Person_Fio, '')) as \"Evn_Title\"
			from v_EvnVizit EV
				left join v_LpuSection LS on LS.LpuSection_id = EV.LpuSection_id
				left join lateral(
					select
						Person_Fio
					from v_MedPersonal
					where MedPersonal_id = EV.MedPersonal_id
					limit 1
				) MP on true
			where
				EV.EvnVizit_rid = :rid
		";
		$result = $this->db->query($query, array(
			'rid' => $data['rid']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * loadDiagCombo
	 * @param $data
	 * @return bool
	 */
	function loadDiagCombo($data) {
		$queryParams = array(
			'EvnPLStom_id' => $data['EvnPLStom_id'],
			'EvnVizitPLStom_id' => $data['EvnVizitPLStom_id']
		);

		$query = "
			select
				EDPLS.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				EDPLS.DeseaseType_id as \"DeseaseType_id\",
				DT.DeseaseType_Name as \"DeseaseType_Name\",
				case when EDPLS.EvnDiagPLStom_pid = :EvnVizitPLStom_id
					then 2
					else 1
				end as \"Diag_IsCurrent\"
			from
				v_EvnDiagPLStom EDPLS
				left join v_Diag D on D.Diag_id = EDPLS.Diag_id
				left join v_DeseaseType DT on DT.DeseaseType_id = EDPLS.DeseaseType_id
			where
				EDPLS.EvnDiagPLStom_rid = :EvnPLStom_id
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
	 *  Получение дублей
	 */
	function getEvnVizitPLDoubles($data) {
		$resp = $this->queryResult("select EvnClass_id from v_Evn where Evn_id = :EvnVizitPL_id", array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id']
		));
		$object = 'EvnVizitPL';
		if (!empty($resp[0]['EvnClass_id']) && $resp[0]['EvnClass_id'] == 13) {
			$object = 'EvnVizitPLStom';
		}
		$model = $object.'_model';
		$this->load->model($model);
		$this->{$model}->applyData(array(
			$object.'_id' => $data['EvnVizitPL_id'],
			'session' => $data['session']
		));
		$doublesEvnPL = $this->{$model}->getEvnVizitPLDoubles();
		return array('Error_Msg' => '', 'doublesEvnPL' => $doublesEvnPL);
	}

	/**
	 *  Сохранение дублей
	 */
	function saveEvnVizitPLDoubles($data) {
		foreach($data['EvnVizitPLDoublesData'] as $oneDouble) {
			if (isset($oneDouble['EvnVizitPL_id']) && isset($oneDouble['VizitPLDouble_id'])) {
				$this->db->query("update EvnVizitPL  set VizitPLDouble_id = :VizitPLDouble_id where Evn_id = :EvnVizitPL_id", array(
					'EvnVizitPL_id' => $oneDouble['EvnVizitPL_id'],
					'VizitPLDouble_id' => $oneDouble['VizitPLDouble_id']
				));
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 *  Получение списка для таблицы записей на приём
	 *  Используется: форма "Рабочее место сотрудника картохранилища"
	 */
	function loadReceptionTableGrid($data) {
		$filter = "(1 = 1) and MSF.Lpu_id = :Lpu_id ";
		$filterAmbulatCard = '';
		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];

		//$begDay_id = TimeToDay( strtotime( $data['begDate'] ) );
		//$endDay_id = TimeToDay( strtotime( $data['endDate'] ) );
		if ( empty( $data['begDate'] ) ) {
			$begDay = TimeToDay( mktime( 0,
				0,
				0,
				date( "m" ),
				date( "d" ),
				date( "Y" ) ) );
			$endDay = TimeToDay( mktime( 0,
				0,
				0,
				date( "m" ),
				date( "d" ) + 15,
				date( "Y" ) ) );
		} else {
			$begDay = TimeToDay( strtotime( $data['begDate'] ) );
			$endDay = TimeToDay( strtotime( $data['endDate'] ) );
		}

		if ( !empty( $data['Person_id'] ) ) {
			$filter .= " and p.Person_id = :Person_id ";
			$params['Person_id'] = $data['Person_id'];
		}
		if ( !empty( $data['PersonAmbulatCard_id'] ) ) {
			//$filter .= " and ambulatCard.PersonAmbulatCard_id = :PersonAmbulatCard_id ";
			$filterAmbulatCard = ' AND PAC.PersonAmbulatCard_id = :PersonAmbulatCard_id ';
			$params['PersonAmbulatCard_id'] = $data['PersonAmbulatCard_id'];
		}
		if ( !empty($data['PersonAmbulatCard_id']) && !empty( $data['Person_id'] ) ){
			//данные со штрих кода
			$data['limit'] = 1;
			$data['offset'] = 0;
		}

		if ( !empty( $data['Person_SurName'] ) ) {
			$filter .= " and p.Person_SurName ilike (:Person_SurName||'%')";
			$params['Person_SurName'] = rtrim( $data['Person_SurName'] );
		}

		if ( !empty( $data['Person_FirName'] ) ) {
			$filter .= " and p.Person_FirName ilike (:Person_FirName||'%')";
			$params['Person_FirName'] = rtrim( $data['Person_FirName'] );
		}
		if ( !empty( $data['Person_SecName'] ) ) {
			$filter .= " and p.Person_SecName ilike (:Person_SecName||'%')";
			$params['Person_SecName'] = rtrim( $data['Person_SecName'] );
		}
		if ( !empty( $data['Person_BirthDay'] ) ) {
			$filter .= " and p.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if ( ! empty($data['MedStaffFact_id'])){
			$filter .= " and MSF.MedStaffFact_id = :MedStaffFact_id";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		if ( ! empty($data['MedPersonal_id'])){
			$filter .= " and MSF.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ( ! empty($data['CardAtTheReception'])){
			//Карта на приёме?
			if($data['CardAtTheReception'] == 'not'){
				$filter .= ' AND MSF.MedStaffFact_id != coalesce(ambulatCard.MedStaffFact_id, 0)';
			}else if($data['CardAtTheReception'] == 'yes'){
				$filter .= ' AND MSF.MedStaffFact_id = coalesce(ambulatCard.MedStaffFact_id, 0)';
			}
			$params['CardAtTheReception'] = $data['CardAtTheReception'];
		}
		if ( ! empty($data['RequestFromTheDoctor'])){
			if($data['RequestFromTheDoctor'] == 'not'){
				$filter .= ' AND coalesce(ACR.AmbulatCardRequestStatus_id, 0) <> 1 ';
			}else if($data['RequestFromTheDoctor'] == 'yes'){
				$filter .= ' AND ACR.AmbulatCardRequestStatus_id = 1 ';
			}
		}
		if ( ! empty($data['field_numberCard'])){
			//№ амб. карты
			$filter .= ' AND ambulatCard.PersonAmbulatCard_Num = :PersonAmbulatCard_Num';
			$params['PersonAmbulatCard_Num'] = $data['field_numberCard'];
		}

		if(!empty($data['attachmentLpuBuilding_id'])){
			$fromAmbulatCard = "
				left join lateral(
					select
						ACLB.LpuBuilding_id, ACLB.PersonAmbulatCard_id
					from v_AmbulatCardLpuBuilding ACLB 
						left join v_PersonAmbulatCard vpac on ACLB.PersonAmbulatCard_id = vPAC.PersonAmbulatCard_id
					WHERE AmbulatCardLpuBuilding_endDate is null AND ACLB.LpuBuilding_id = :attachmentLpuBuilding_id AND vPAC.Person_id = p.Person_id
					ORDER BY vpac.PersonAmbulatCard_id DESC
					limit 1
				) locatAttachmentLpuBuilding on true";
			$params['attachmentLpuBuilding_id'] = $data['attachmentLpuBuilding_id'];
			if(!$filterAmbulatCard){
				$filterAmbulatCard = ' AND PAC.PersonAmbulatCard_id = COALESCE(ttg.PersonAmbulatCard_id, locatAttachmentLpuBuilding.PersonAmbulatCard_id, PAC.PersonAmbulatCard_id) ';
			}
		}else{
			$fromAmbulatCard = "";
			if(!$filterAmbulatCard){
				$filterAmbulatCard = ' AND PAC.PersonAmbulatCard_id = COALESCE(ttg.PersonAmbulatCard_id, PAC.PersonAmbulatCard_id) ';
			}
		}

		$query = "
			SELECT
				-- select
				ttg.TimetableGraf_id as \"TimetableGraf_id\",
				ttg.Person_id as \"Person_id\",
				pcMain.PersonCard_id as \"PersonCard_id\",
				CASE 
					WHEN MSF.MedStaffFact_id = ambulatCard.MedStaffFact_id THEN 'да'
					ELSE 'нет'
				END as \"CardAtTheReception\",
				ACR.AmbulatCardRequestStatus_id as \"AmbulatCardRequestStatus_id\",
				ACR.AmbulatCardRequest_id as \"AmbulatCardRequest_id\",
				case
					when coalesce(ACR.AmbulatCardRequestStatus_id, 0) = 1
					then 1
					else 0
				end as \"AmbulatCardRequest\",
				case
					when ttg.TimetableGraf_begTime is not null
						then to_char(TimetableGraf_begTime,'dd.mm.yyyy')
					when ttg.TimetableGraf_factTime is not null
						then to_char(TimetableGraf_factTime,'dd.mm.yyyy')
					else to_char(TimetableGraf_insDT,'dd.mm.yyyy')
				end || ' ' || coalesce(to_char(ttg.TimetableGraf_begTime, 'HH24:MI'),'б/з') as \"TimetableGraf_Date\", --Время приёма
				LS.LpuSection_Name as \"LpuSection_Name\",  --отделение
				LB.LpuBuilding_Name as \"LpuBuilding_Name\", --подразделение
				trim(msf.Person_FIO) || ' (' || ps.PostMed_Name || coalesce(' ' || msf.MedPersonal_TabCode, '') || ')' as \"Doctor\", -- ФИО врача
				MSF.MedStaffFact_id as \"MedStaffFact_id\",
				MSF.MedPersonal_id as \"MedPersonal_id\",
				trim(trim(p.Person_Surname) || ' ' || coalesce(trim(p.Person_Firname), '') || ' ' || coalesce(trim(p.Person_Secname), '')) as \"Person_FIO\", --ФИО пациента
				trim(p.Person_Surname) as \"Person_Surname\",
				trim(p.Person_Firname) as \"Person_Firname\",
				trim(p.Person_Secname) as \"Person_Secname\",
				to_char(p.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\", --Дата рождения пациента
				coalesce(pcMain.Lpu_Nick, '') as \"MainLpu_Nick\", -- МО прикрепления (осн.)
				coalesce(pcMain.LpuRegion_Name, '') as \"LpuRegion_Name\", --участок
				coalesce(pcGin.Lpu_Nick, '') as \"GinLpu_Nick\",   -- МО прикрепления (гинек.)
				coalesce(pcStom.Lpu_Nick, '') as \"StomLpu_Nick\", --МО прикрепления(стомат .)
				coalesce(trim(ambulatCard.PersonAmbulatCard_Num), '') as \"PersonAmbulatCard_Num\", --№ амб. карты
				ambulatCard.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				ambulatCard.MedStaffFact_id as \"CardLocationMedStaffFact_id\", -- у какого врача карта
				coalesce(ambulatCard.ACLB_LpuBuilding_Name, '') as \"AttachmentLpuBuilding_Name\", --Подразделение прикрепления карты
				ambulatCard.MapLocation as \"Location_Amb_Cards\", -- Местонахождение амб. карты 
				to_char(ttg.TimetableGraf_insDT,'dd.mm.yyyy') as \"TimetableGraf_insDT\", --Когда записан
				coalesce(ttt.TimetableType_Name, '') as \"TimetableType_Name\" --Тип записи (типи бирки)
				-- end select
			FROM
				-- from
				dbo.TimetableGraf ttg
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_TimetableType ttt on ttt.TimetableType_id = ttg.TimetableType_id
				left join v_PostMed ps on ps.PostMed_id=msf.Post_id
				left join v_PersonState_all p on p.Person_id = ttg.Person_id
				left join v_LpuSection LS on LS.LpuSection_id = MSF.LpuSection_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = MSF.LpuBuilding_id
				left join lateral(
					select
						vpc.PersonCard_id, 
						vpc.PersonCard_Code, 
						vpc.LpuAttachType_id, 
						vpc.LpuRegion_id, 
						LB.LpuBuilding_id, 
						LB.LpuBuilding_Name, 
						trim(l.Lpu_Nick) as Lpu_Nick,
						LR.LpuRegion_Name || coalesce(' ('||LpuRegion_Descr||')', '') as LpuRegion_Name
					from v_PersonCard VPC
						left join v_LpuRegion LR on LR.LpuRegion_id = vpc.LpuRegion_id
						left join v_Lpu l on l.Lpu_id = vpc.Lpu_id
					where LpuAttachType_id=1
						AND Person_id = p.Person_id
					ORDER BY VPC.PersonCard_id DESC
					limit 1
				) pcMain on true --МО прикрепления (осн.)
				left join lateral(
					select
						PersonCard_id,
						LpuAttachType_id,
						Trim(l.Lpu_Nick) as Lpu_Nick
					from v_PersonCard VPC
						left join v_Lpu l on l.Lpu_id = vpc.Lpu_id
					where LpuAttachType_id=2 AND Person_id = p.Person_id
					ORDER BY VPC.PersonCard_id DESC
					limit 1
				) pcGin on true --МО прикрепления (гин.)
				left join lateral(
					select
						PersonCard_id,
						LpuAttachType_id,
						Trim(l.Lpu_Nick) as Lpu_Nick
					from v_PersonCard VPC
						left join v_Lpu l on l.Lpu_id = vpc.Lpu_id
					where LpuAttachType_id=3 AND Person_id = p.Person_id
					ORDER BY VPC.PersonCard_id DESC
					limit 1
				) pcStom on true ----МО прикрепления (стом.)
				left join lateral(
					select
						PAC.PersonAmbulatCard_id,
						PAC.PersonAmbulatCard_Num,
						PACL.LpuBuilding_id,
						LB.LpuBuilding_Name,
						ACLB_LB.LpuBuilding_Name as ACLB_LpuBuilding_Name,
						AMSF.MedStaffFact_id,
						coalesce(ACLT.AmbulatCardLocatType_Name, '') || coalesce(', '||LB.LpuBuilding_Name, '') ||  coalesce(', '||AMSF.Person_Fio, '') as MapLocation
					from v_PersonAmbulatCard PAC
						left join v_AmbulatCardLpuBuilding ACLB on ACLB.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
						left join v_PersonAmbulatCardLocat PACL on PACL.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
						left join AmbulatCardLocatType ACLT on PACL.AmbulatCardLocatType_id = ACLT.AmbulatCardLocatType_id
						left join v_LpuBuilding LB on LB.LpuBuilding_id = PACL.LpuBuilding_id
						left join v_MedStaffFact AMSF on AMSF.MedStaffFact_id = PACL.MedStaffFact_id
						left join v_LpuBuilding ACLB_LB on ACLB_LB.LpuBuilding_id = ACLB.LpuBuilding_id --прикрепление к подразделению
						{$fromAmbulatCard}
					where 1=1
						AND PAC.Lpu_id = :Lpu_id
						AND PAC.Person_id = p.Person_id
						AND coalesce(ACLB.AmbulatCardLpuBuilding_begDate, dbo.tzGetDate()) <= dbo.tzGetDate() AND coalesce(ACLB.AmbulatCardLpuBuilding_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
						{$filterAmbulatCard}
					ORDER BY PACL.PersonAmbulatCardLocat_begDate DESC
					limit 1
				) ambulatCard on true -- амбулаторная карта
				left join v_AmbulatCardRequest ACR on ACR.PersonAmbulatCard_id = ambulatCard.PersonAmbulatCard_id AND ACR.TimeTableGraf_id = ttg.TimeTableGraf_id
				-- end from
			WHERE
				-- where
				{$filter}
				and ttg.Person_id is not null
				--and MSF.MedStaffFact_id = 99560023079
				and ttg.TimetableGraf_Day between $begDay and $endDay
				-- end where
			ORDER BY
				-- order by
				case when coalesce(ACR.AmbulatCardRequestStatus_id, 0) = 1 then 1 else 0 end DESC,
				coalesce(ttg.TimetableGraf_begTime, '01-01-2100') ASC,
				ttg.TimetableGraf_factTime ASC
				-- end order by
		";
		//echo getDebugSQL($query, $params);die();
		//$result = $this->db->query($query, $params);

		if ( !empty($data['limit']) ) {
			return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		}
		else {
			return $this->queryResult($query, $params);
		}
	}

	/**
	 * Загрузка схемы лекарственной терапии для Посещения
	 */
	function getEvnVizitPLDrugTherapyScheme($data){
		if(empty($data['EvnVizitPL_id'])) return array();

		return $this->queryResult("
					select
						EvnVizitPLDrugTherapyLink_id as \"EvnVizitPLDrugTherapyLink_id\",
						DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
					from
						v_EvnVizitPLDrugTherapyLink
					where
						EvnVizitPL_id = :EvnVizitPL_id
				", array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id']
		));
	}

    //Получения признака заполнения поля "цель посещения" для региона Пенза. задача - 166824
    function checkPenzaVizitTypeId( $data ){
        return $this->queryResult("
			SELECT 
				EVP.VizitType_id as \"VizitType_id\"
			FROM 
				dbo.Evn E
				inner join dbo.EvnVizit EV on E.Evn_id = EV.Evn_id
				inner join dbo.EvnVizitPL EVP on EVP.EvnVizit_id = EV.EvnVizit_id
			WHERE 
				E.Evn_pid = :Evn_pid;
		", ['Evn_pid' => $data['Evn_pid']]);
    }

    /**
     * @param $data
     * @return array|bool
     * @description Возвращает список посещений для АРМ врача ЛЛО поликлиники
     */
    public function loadEvnVizitPLListForLLO($data) {
        $filterList = [];
        $queryParams = [];

        $queryParams['Lpu_id'] = $data['Lpu_id'];

        if (!isSuperAdmin()) {
            $filterList[] = "EVPL.pmUser_insID = :pmUser_id";
            $queryParams['pmUser_id'] = $data['pmUser_id'];
        }
        else if (!empty($data['pmUser_insID'])) {
            $filterList[] = "EVPL.pmUser_insID = :pmUser_id";
            $queryParams['pmUser_id'] = $data['pmUser_insID'];
        }

        $filterList[] = "EVPL.Lpu_id = :Lpu_id";

        if( !empty($data['begDate']) ) {
            $filterList[] = "EVPL.EvnVizitPL_setDate >= :begDate";
            $queryParams['begDate'] = $data['begDate'];
        }

        if( !empty($data['endDate']) ) {
            $filterList[] = "EVPL.EvnVizitPL_setDate <= :endDate";
            $queryParams['endDate'] = $data['endDate'];
        }

        if( !empty($data['Person_SurName']) ) {
            $filterList[] = "PS.Person_SurName ilike :Person_SurName || '%'";
            $queryParams['Person_SurName'] = rtrim($data['Person_SurName']);
        }

        if( !empty($data['Person_FirName']) ) {
            $filterList[] = "PS.Person_FirName ilike :Person_FirName || '%'";
            $queryParams['Person_FirName'] = rtrim($data['Person_FirName']);
        }

        if( !empty($data['Person_SecName']) ) {
            $filterList[] = "PS.Person_SecName ilike :Person_SecName || '%'";
            $queryParams['Person_SecName'] = rtrim($data['Person_SecName']);
        }

        if( !empty($data['Person_BirthDay']) ) {
            $filterList[] = "PS.Person_BirthDay = :Person_BirthDay";
            $queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
        }

        if( !empty($data['Person_Snils']) ) {
            $filterList[] = "PS.Person_Snils = :Person_Snils";
            $queryParams['Person_Snils'] = $data['Person_Snils'];
        }

        $query = "
			-- variables
			with cte as (
						select dbo.tzGetdate() as curDT
			)
			-- end variables

			select
				-- select
				 EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				 EVPL.Person_id as \"Person_id\",
				 EVPL.PersonEvn_id as \"PersonEvn_id\",
				 EVPL.Server_id as \"Server_id\",
				 EVPL.LpuSection_id as \"LpuSection_id\",
				 EVPL.MedStaffFact_id as \"MedStaffFact_id\",
				 EVPL.Diag_id as \"Diag_id\",
				 to_char(EVPL.EvnVizitPL_setDT, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\",
				 rtrim(PS.Person_SurName) as \"Person_Surname\",
				 rtrim(PS.Person_FirName) as \"Person_Firname\",
				 rtrim(PS.Person_SecName) as \"Person_Secname\",
				 to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",
				 CASE WHEN FL.Person_id is not null THEN 'true' ELSE 'false' END as \"Person_IsFedLgot\",
				 CASE WHEN RL.Person_id is not null THEN 'true' ELSE 'false' END as \"Person_IsRegLgot\",
				 CASE WHEN PR.PersonRefuse_IsRefuse = 2 THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
				 MP.Person_Fio || case when p.name is not null then ', ' || p.name else '' end as \"MedPersonal_Data\",
				 PMU.pmUser_Name as \"pmUser_Name\",
				 PPR.val as \"PersonPrivilegeReq_Data\"
				-- end select
			from
				-- from
				v_EvnVizitPL as EVPL
				inner join v_Lpu as L on L.Lpu_id = EVPL.Lpu_id
				inner join v_Person_FIO as PS on PS.Server_id = EVPL.Server_id
					and PS.PersonEvn_id = EVPL.PersonEvn_id
				left join lateral (
					select
					    t1.Person_Fio,
                        t1.Post_id
					from
					    dbo.v_MedStaffFact as t1
					where
					    t1.MedStaffFact_id = EVPL.MedStaffFact_id
					limit 1
				) MP on true
				left join persis.Post as P on P.id = MP.Post_id
				left join pmUserCache as PMU on PMU.pmUser_id = EVPL.pmUser_insID
				left join lateral (
					select 
					    t1.PersonRefuse_IsRefuse
					from
					    v_PersonRefuse as t1
					where
						t1.Person_id = PS.Person_id
                    and 
                        t1.PersonRefuse_IsRefuse = 2
                    and 
                        t1.PersonRefuse_Year = date_part('year', (select curDT from cte))
					limit 1
				) PR on true
				left join lateral (
					select 
					    t1.Person_id
					from 
					    v_PersonPrivilege t1
						inner join v_PrivilegeType t2 on t2.PrivilegeType_id = t1.PrivilegeType_id
					where
						t1.Person_id = PS.Person_id
                    and 
                        t2.ReceptFinance_id = 1
                    and 
                        t1.PersonPrivilege_begDate <= (select curDT from cte)
                    and 
                        (coalesce(t1.PersonPrivilege_endDate,  (select curDT from cte)) >= cast( (select curDT from cte) as date))
                    limit 1
				) FL on true
				left join lateral (
					select t1.Person_id
					from 
					    v_PersonPrivilege t1
						inner join v_PrivilegeType t2 on t2.PrivilegeType_id = t1.PrivilegeType_id
					where
                        t1.Person_id = PS.Person_id
                    and 
                        t2.ReceptFinance_id = 2
                    and 
                        t1.PersonPrivilege_begDate <= (select curDT from cte)
                    and 
                        (coalesce(t1.PersonPrivilege_endDate, (select curDT from cte)) >= cast((select curDT from cte) as date))
					limit 1
				) RL on true
				left join lateral (
                    select
                        string_agg(
                            coalesce(i_pt.PrivilegeType_VCode, i_pt.PrivilegeType_Code::varchar)||
                            coalesce(' - '|| i_st.state, '') || ';', ','
                        ) as val
                    from
                        (
                            select distinct
                                ii_pp.PrivilegeType_id
                            from
                                v_PersonPrivilege ii_pp
                            where
                                ii_pp.Person_id = PS.Person_id
                            and 
                                ii_pp.PersonPrivilege_begDate <= (select curDT from cte)
                            and 
                                (coalesce(ii_pp.PersonPrivilege_endDate, (select curDT from cte)) >= cast((select curDT from cte) as date))
                        ) i_pp
                        inner join v_PrivilegeType i_pt on i_pt.PrivilegeType_id = i_pp.PrivilegeType_id
                        left join lateral(
                            select
                                ii_ppr.PersonPrivilegeReq_id,
                                ii_ppra.PersonPrivilegeReqStatus_id, 
                                ii_ppra.PersonPrivilegeReqAns_IsInReg,
                                ii_ppra.PersonPrivilegeReqAns_DeclCause
                            from
                                v_PersonPrivilegeReq ii_ppr
                                left join v_PersonPrivilegeReqAns ii_ppra on ii_ppra.PersonPrivilegeReq_id = ii_ppr.PersonPrivilegeReq_id
                            where
                                ii_ppr.Person_id = PS.Person_id
                            and
                                ii_ppr.PrivilegeType_id = i_pp.PrivilegeType_id
                            order by
                                ii_ppr.PersonPrivilegeReq_id desc
                            limit 1
                        ) i_ppr on true
                        left join lateral (
                            select
                                (case
                                    when i_ppr.PersonPrivilegeReqStatus_id = 1 then 'не передан в СЦ' -- 1 - Новый
                                    when i_ppr.PersonPrivilegeReqStatus_id = 2 then 'на рассмотрении' -- 2 - На рассмотрении
                                    when i_ppr.PersonPrivilegeReqStatus_id = 3 and coalesce(i_ppr.PersonPrivilegeReqAns_IsInReg, 1) = 2 then 'одобрен' -- 3 - Ответ получен; 2 - В регистре: Да
                                    when i_ppr.PersonPrivilegeReqStatus_id = 3 and coalesce(i_ppr.PersonPrivilegeReqAns_IsInReg, 1) = 1 then 'отклонен'||coalesce(': '||i_ppr.PersonPrivilegeReqAns_DeclCause, '') -- 3 - Ответ получен; 1 - В регистре: Нет
                                    else null
                                end) as state
                        ) i_st ON true
				) as PPR ON true
				-- end from
			where
				-- where
				" . implode(" and ", $filterList) . "
				-- end where
			order by
				-- order by
				EVPL.EvnVizitPL_setDT desc
				-- end order by
		";

        return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
    }

    /**
     * Идентификация пациента и создание прикрепления, амбулаторной карты и посещения
     */
    public function createEvnVizitPLForLLO($data) {
        // Выполняется поиск пациента. Описание поиска пациента приведено в п.2.3.1 (будет уточнено позднее).
        $resp_ps = $this->queryResult("
			select
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.UAddress_id as \"UAddress_id\",
				PS.PAddress_id as \"PAddress_id\",
				PS.Person_Snils as \"Person_Snils\",
				PS.PersonState_IsSnils as \"PersonState_IsSnils\",
				PS.Document_id as \"Document_id\",
				DT.DocumentType_Code as \"DocumentType_Code\",
				D.Document_Ser as \"Document_Ser\",
				D.Document_Num as \"Document_Num\",
				O.OrgDep_id as \"OrgDep_id\",
				to_char(D.Document_begDate, 'YYYY-MM-DD') as \"Document_begDate\"
			from
				v_PersonState PS
				left join v_Document D on D.Document_id = PS.Document_id
				left join v_DocumentType DT on DT.DocumentType_id = D.DocumentType_id
				left join v_OrgDep O on O.OrgDep_id = D.OrgDep_id
			where
				PS.Person_SurName = :Person_SurName
            and
                PS.Person_FirName = :Person_FirName
            and
                PS.Person_SecName = :Person_SecName
            and
                PS.Person_BirthDay = :Person_BirthDay
		", [
            'Person_SurName' => $data['Sm'],
            'Person_FirName' => $data['Nm'],
            'Person_SecName' => $data['Pm'],
            'Person_BirthDay' => $data['BD']
        ]);
	
		// Получим данные введенного документа
		$DocumentType = $this->getFirstRowFromQuery("
					select DocumentType_id as \"DocumentType_id\" from v_DocumentType where DocumentType_Code = :DocumentType_Code
				", array(
			'DocumentType_Code' => $data['DocTypeId']
		));
		if (empty($DocumentType)) {
			return array('Error_Msg' => 'Не удалось определить тип документа, удостоверяющего личность (DocTypeId)');
		}
		$OrgDep = $this->getFirstRowFromQuery("
					select OrgDep_id as \"OrgDep_id\" from v_OrgDep where OrgDep_Name like ('%'+:OrgDep_Name+'%')
				", array(
			'OrgDep_Name' => $data['DocTypeWhom']
		));
		if (empty($DocumentType)) {
			return array('Error_Msg' => 'Не удалось определить организацию, выдавшую документ, удостоверяющий личность (DocTypeWhom)');
		}
		$data['DocTypeDate'] = date('Y-m-d', strtotime($data['DocTypeDate']));

        if (!empty($resp_ps[0]['Person_id'])) {
            $Person_id = $resp_ps[0]['Person_id'];
		}

		if (!empty($resp_ps[0]['Person_id'])) {
			if (!empty($data['Ar'])) {// empty($resp_ps[0]['UAddress_id'])) {
				// Пытаемся идентифицировать адрес регистрации
				$resp_adr = $this->getFirstRowFromQuery("
					select
						KLRgn_id as \"KLRgn_id\",
						KLSubRgn_id as \"KLSubRgn_id\",
						KLCity_id as \"KLCity_id\",
						KLTown_id as \"KLTown_id\",
						KLStreet_id as \"KLStreet_id\",
						Address_House as \"Address_House\",
						Address_Corpus as \"Address_Corpus\",
						Address_Flat as \"Address_Flat\",
						Address_Zip as \"Address_Zip\",
						Address_Address as \"Address_Address\"
					from AddressParse(:Address_Address)
				", array(
					'Address_Address' => $data['Ar']
				));
				if (is_array($resp_adr) && !empty($resp_adr) && !empty($resp_adr['KLRgn_id'])) {
					$personData = array(
						'Person_id' => $resp_ps[0]['Person_id'],
						'Server_id' => $resp_ps[0]['Person_id'],
						'insDT' => date('Y-m-d').' 00:00:00.000',
						'PersonEvn_id' => $resp_ps[0]['PersonEvn_id'],
						'KLCountry_id' => 643,
						'KLRgn_id' => $resp_adr['KLRgn_id'],
						'KLSubRgn_id' => $resp_adr['KLSubRgn_id'],
						'KLCity_id' => $resp_adr['KLCity_id'],
						'KLTown_id' => $resp_adr['KLTown_id'],
						'KLStreet_id' => $resp_adr['KLStreet_id'],
						'Address_House' => $resp_adr['Address_House'],
						'Address_Corpus' => $resp_adr['Address_Corpus'],
						'Address_Flat' => $resp_adr['Address_Flat'],
						'Address_Zip' => $resp_adr['Address_Zip'],
						'Address_Address' => $resp_adr['Address_Address'],
						'PersonSprTerrDop_id' => null,
						'pmUser_id' => $data['pmUser_id'],
						'session' => $data['session']
					);
				}
				else {
					$resp_adr_area = $this->getFirstRowFromQuery("
						select * from dbo.KLArea where KLArea_id = :KLArea_id and KLAreaLevel_id = 2
					", array(
						'KLArea_id' => $data['AreaReg']
					));
					if (is_array($resp_adr_area) && !empty($resp_adr_area)) {
						$personData = array(
							'Person_id' => $resp_ps[0]['Person_id'],
							'Server_id' => $resp_ps[0]['Person_id'],
							'insDT' => date('Y-m-d').' 00:00:00.000',
							'PersonEvn_id' => $resp_ps[0]['PersonEvn_id'],
							'KLCountry_id' => $resp_adr_area['KLCountry_id'],
							'KLRgn_id' => $resp_adr_area['KLArea_pid'],
							'KLSubRgn_id' => $resp_adr_area['KLArea_id'],
							'Address_Address' => $data['Ar'],
							'pmUser_id' => $data['pmUser_id'],
							'session' => $data['session']
						);
					}
					else {
						$personData = array(
							'Person_id' => $resp_ps[0]['Person_id'],
							'Server_id' => $resp_ps[0]['Person_id'],
							'insDT' => date('Y-m-d').' 00:00:00.000',
							'PersonEvn_id' => $resp_ps[0]['PersonEvn_id'],
							'Address_Address' => $data['Ar'],
							'pmUser_id' => $data['pmUser_id'],
							'session' => $data['session']
						);
					}
				}
				$this->load->model('Person_model');
				$this->Person_model->savePersonUAddress($personData);
			}
			if (!empty($data['AL'])) { //&& empty($resp_ps[0]['PAddress_id'])) {
				// Пытаемся идентифицировать адрес проживания
				$resp_adr = $this->getFirstRowFromQuery("
					select
						KLRgn_id as \"KLRgn_id\",
						KLSubRgn_id as \"KLSubRgn_id\",
						KLCity_id as \"KLCity_id\",
						KLTown_id as \"KLTown_id\",
						KLStreet_id as \"KLStreet_id\",
						Address_House as \"Address_House\",
						Address_Corpus as \"Address_Corpus\",
						Address_Flat as \"Address_Flat\",
						Address_Zip as \"Address_Zip\",
						Address_Address as \"Address_Address\"
					from AddressParse(:Address_Address)
				", array(
					'Address_Address' => $data['AL']
				));
				if (is_array($resp_adr) && !empty($resp_adr) && !empty($resp_adr['KLRgn_id'])) {
					$personData = array(
						'Person_id' => $resp_ps[0]['Person_id'],
						'Server_id' => $resp_ps[0]['Person_id'],
						'insDT' => date('Y-m-d').' 00:00:00.000',
						'PersonEvn_id' => $resp_ps[0]['PersonEvn_id'],
						'KLCountry_id' => 643,
						'KLRgn_id' => $resp_adr['KLRgn_id'],
						'KLSubRgn_id' => $resp_adr['KLSubRgn_id'],
						'KLCity_id' => $resp_adr['KLCity_id'],
						'KLTown_id' => $resp_adr['KLTown_id'],
						'KLStreet_id' => $resp_adr['KLStreet_id'],
						'Address_House' => $resp_adr['Address_House'],
						'Address_Corpus' => $resp_adr['Address_Corpus'],
						'Address_Flat' => $resp_adr['Address_Flat'],
						'Address_Zip' => $resp_adr['Address_Zip'],
						'Address_Address' => $resp_adr['Address_Address'],
						'PersonSprTerrDop_id' => null,
						'pmUser_id' => $data['pmUser_id'],
						'session' => $data['session']
					);
				}
				else {
					$resp_adr_area = $this->getFirstRowFromQuery("
						select * from dbo.KLArea where KLArea_id = :KLArea_id and KLAreaLevel_id = 2
					", array(
						'KLArea_id' => $data['AreaLive']
					));
					if (is_array($resp_adr_area) && !empty($resp_adr_area)) {
						$personData = array(
							'Person_id' => $resp_ps[0]['Person_id'],
							'Server_id' => $resp_ps[0]['Person_id'],
							'insDT' => date('Y-m-d').' 00:00:00.000',
							'PersonEvn_id' => $resp_ps[0]['PersonEvn_id'],
							'KLCountry_id' => $resp_adr_area['KLCountry_id'],
							'KLRgn_id' => $resp_adr_area['KLArea_pid'],
							'KLSubRgn_id' => $resp_adr_area['KLArea_id'],
							'Address_Address' => $data['AL'],
							'pmUser_id' => $data['pmUser_id'],
							'session' => $data['session']
						);
					}
					else {
						$personData = array(
							'Person_id' => $resp_ps[0]['Person_id'],
							'Server_id' => $resp_ps[0]['Person_id'],
							'insDT' => date('Y-m-d').' 00:00:00.000',
							'PersonEvn_id' => $resp_ps[0]['PersonEvn_id'],
							'Address_Address' => $data['AL'],
							'pmUser_id' => $data['pmUser_id'],
							'session' => $data['session']
						);
					}
				}
				
				$this->load->model('Person_model');
				$this->Person_model->savePersonUAddress($personData, 'P');
			}

			// Проверяем нужно ли обновлять СНИЛС
			$Snils = preg_replace('/[^0-9]/', '', $data['Snl']);
			if (!empty($Snils) && $resp_ps[0]['PersonState_IsSnils'] != 2 && $resp_ps[0]['Person_Snils'] != $Snils) {
				$personData = array(
					'Person_id' => $resp_ps[0]['Person_id'],
					'Server_id' => $resp_ps[0]['Person_id'],
					'Person_Snils' => $Snils,
					'pmUser_id' => $data['pmUser_id'],
					'session' => $data['session']
				);

				$this->load->model('Person_model');
				$this->Person_model->savePersonSnils($personData);
			}
			
			if (
				empty($resp_ps[0]['Document_id']) ||
				$resp_ps[0]['DocumentType_Code'] != $data['DocTypeId'] ||
				$resp_ps[0]['Document_Ser'] != $data['DocTypeSeries'] ||
				$resp_ps[0]['Document_Num'] != $data['DocTypeNumber'] ||
				$resp_ps[0]['Document_begDate'] != $data['DocTypeDate'] ||
				$resp_ps[0]['OrgDep_id'] != $OrgDep['OrgDep_id']
			) {
				// Обновляем данные документа УДЛ
				$this->load->model('Person_model');
				$this->Person_model->editPersonEvnAttributeNew(array(
					'Server_id'=>$data['Server_id'],
					'Person_id'=>$resp_ps[0]['Person_id'],
					'PersonEvn_id'=>$resp_ps[0]['Document_id'],
					'EvnType'=>'Document',
					'DocumentType_id'=>$DocumentType['DocumentType_id'],
					'OrgDep_id'=>$OrgDep['OrgDep_id'],
					'Document_Ser'=>$data['DocTypeSeries'],
					'Document_Num'=>$data['DocTypeNumber'],
					'Document_begDate'=>$data['DocTypeDate'],
					'pmUser_id'=>$data['pmUser_id']
				));
			}
        } else {
            $personData = [
                'Person_SurName' => $data['Sm'],
                'Person_FirName' => $data['Nm'],
                'Person_SecName' => $data['Pm'],
                'Person_BirthDay' => $data['BD'],
                'PersonSex_id' => $data['Sx'] == 1 ? 1 : 2,
                'PersonPhone_Phone' => null,
                'Person_SNILS' => preg_replace('/[^0-9]/', '', $data['Snl']),
                'Polis_Num' => $data['NI'],
                'Polis_Ser' => $data['SI'],
                'Polis_begDate' => $data['PolisDt'],
                'Person_id' => null,
                'SocStatus_id' => null,
                'mode' => 'add',
                'oldValues' => '',
                'Server_id' => $data['Server_id'],
                'pmUser_id' => $data['pmUser_id'],
                'session' => $data['session']
            ];
			// Пытаемся идентифицировать адрес регистрации
			if (!empty($data['Ar'])) {
				$resp_adr = $this->getFirstRowFromQuery("
					select
						KLRgn_id as \"KLRgn_id\",
						KLSubRgn_id as \"KLSubRgn_id\",
						KLCity_id as \"KLCity_id\",
						KLTown_id as \"KLTown_id\",
						KLStreet_id as \"KLStreet_id\",
						Address_House as \"Address_House\",
						Address_Corpus as \"Address_Corpus\",
						Address_Flat as \"Address_Flat\",
						Address_Zip as \"Address_Zip\",
						Address_Address as \"Address_Address\"
					 from
						AddressParse(:Address_Address)
				", array(
					'Address_Address' => $data['Ar']
				));
				if (is_array($resp_adr) && !empty($resp_adr && !empty($resp_adr['KLRgn_id']))) {
					$personData = array_merge($personData, array(
						'UKLCountry_id' => 643,
						'UKLRGN_id' => $resp_adr['KLRgn_id'],
						'UKLSubRGN_id' => $resp_adr['KLSubRgn_id'],
						'UKLCity_id' => $resp_adr['KLCity_id'],
						'UKLTown_id' => $resp_adr['KLTown_id'],
						'UKLStreet_id' => $resp_adr['KLStreet_id'],
						'UAddress_House' => $resp_adr['Address_House'],
						'UAddress_Corpus' => $resp_adr['Address_Corpus'],
						'UAddress_Flat' => $resp_adr['Address_Flat'],
						'UAddress_Zip' => $resp_adr['Address_Zip'],
						'UAddress_Address' => $resp_adr['Address_Address']
					));
				}
				else {
					$resp_adr_area = $this->getFirstRowFromQuery("
						select * from dbo.KLArea where KLArea_id = :KLArea_id and KLAreaLevel_id = 2
					", array(
						'KLArea_id' => $data['AreaReg']
					));
					if (is_array($resp_adr_area) && !empty($resp_adr_area)) {
						$personData = array_merge($personData, array(
							'UKLCountry_id' => $resp_adr_area['KLCountry_id'],
							'UKLRGN_id' => $resp_adr_area['KLArea_pid'],
							'UKLSubRGN_id' => $resp_adr_area['KLArea_id'],
							'UAddress_Address' => $data['Ar']
						));
					}
					else {
						$personData = array_merge($personData, array(
							'UAddress_Address' => $data['Ar']
						));
					}
				}
			}
			// Пытаемся идентифицировать адрес проживания
			if (!empty($data['AL'])) {
				$resp_adr = $this->getFirstRowFromQuery("
					select
						KLRgn_id as \"KLRgn_id\",
						KLSubRgn_id as \"KLSubRgn_id\",
						KLCity_id as \"KLCity_id\",
						KLTown_id as \"KLTown_id\",
						KLStreet_id as \"KLStreet_id\",
						Address_House as \"Address_House\",
						Address_Corpus as \"Address_Corpus\",
						Address_Flat as \"Address_Flat\",
						Address_Zip as \"Address_Zip\",
						Address_Address as \"Address_Address\"
					 from
						AddressParse(:Address_Address)
				", array(
					'Address_Address' => $data['AL']
				));
				if (is_array($resp_adr) && !empty($resp_adr) && !empty($resp_adr['KLRgn_id'])) {
					$personData = array_merge($personData, array(
						'PKLCountry_id' => 643,
						'PKLRGN_id' => $resp_adr['KLRgn_id'],
						'PKLSubRGN_id' => $resp_adr['KLSubRgn_id'],
						'PKLCity_id' => $resp_adr['KLCity_id'],
						'PKLTown_id' => $resp_adr['KLTown_id'],
						'PKLStreet_id' => $resp_adr['KLStreet_id'],
						'PAddress_House' => $resp_adr['Address_House'],
						'PAddress_Corpus' => $resp_adr['Address_Corpus'],
						'PAddress_Flat' => $resp_adr['Address_Flat'],
						'PAddress_Zip' => $resp_adr['Address_Zip'],
						'PAddress_Address' => $resp_adr['Address_Address']
					));
				}
				else {
					$resp_adr_area = $this->getFirstRowFromQuery("
						select * from dbo.KLArea where KLArea_id = :KLArea_id and KLAreaLevel_id = 2
					", array(
						'KLArea_id' => $data['AreaLive']
					));
					if (is_array($resp_adr_area) && !empty($resp_adr_area)) {
						$personData = array_merge($personData, array(
							'PKLCountry_id' => $resp_adr_area['KLCountry_id'],
							'PKLRGN_id' => $resp_adr_area['KLArea_pid'],
							'PKLSubRGN_id' => $resp_adr_area['KLArea_id'],
							'PAddress_Address' => $data['Ar']
						));
					}
					else {
						$personData = array_merge($personData, array(
							'PAddress_Address' => $data['Ar']
						));
					}
				}
			}

            // Пытаемся идентифицировать форму полиса
            switch($data['PolisForm']) {
                case 1:
                    $personData['PolisFormType_id'] = 55;
                    break;
                case 2:
                    $personData['PolisFormType_id'] = 56;
                    break;
                case 3:
                    $personData['PolisFormType_id'] = 57;
                    break;
                case 4:
                    $personData['PolisFormType_id'] = 58;
                    break;
                default:
                    $personData['PolisFormType_id'] = null;
                    break;
            }

            // Пытаемся идентифицировать тип полиса
            switch($data['PolisType']) {
                case 1:
                    $personData['PolisType_id'] = 1;
                    break;
                case 2:
                    $personData['PolisType_id'] = 3;
                    break;
                case 3:
                    $personData['PolisType_id'] = 4;
                    $personData['Federal_Num'] = $data['NI'];
                    break;
                default:
                    return array('Error_Msg' => 'Не удалось определить тип полиса');
                    break;
            }

            // Пытаемся идентифицировать территорию страхования
            $resp_ost = $this->queryResult("
				select
					ost.OMSSprTerr_id as \"OMSSprTerr_id\"
				from
					v_OmsSprTerr ost
					left join v_KLArea kr on kr.KLArea_id = ost.KLRgn_id
					left join v_KLArea ksr on ksr.KLArea_id = ost.KLSubRgn_id
					left join v_KLArea kc on kc.KLArea_id = ost.KLCity_id
					left join v_KLArea kt on kt.KLArea_id = ost.KLTown_id
				where
					COALESCE(kt.KLAdr_Ocatd, kc.KLAdr_Ocatd, ksr.KLAdr_Ocatd, kr.KLAdr_Ocatd) = :KLAdr_Ocatd
				limit 1
			", [
                'KLAdr_Ocatd' => $data['Terr']
            ]);
            if (!empty($resp_ost[0]['OMSSprTerr_id'])) {
                $personData['OMSSprTerr_id'] = $resp_ost[0]['OMSSprTerr_id'];
            } else {
                throw new Exception('Не удалось определить территорию страхования');
            }

            // Пытаемся идентифицировать организацию, выдавшую полис
            $resp_os = $this->queryResult("
				select
					OrgSMO_id as \"OrgSMO_id\"
				from
					v_OrgSMO
				where
					OrgSMO_f002smocod = :OrgSMO_f002smocod
				limit 1
			", [
                'OrgSMO_f002smocod' => $data['PolisOrg']
            ]);
            if (!empty($resp_os[0]['OrgSMO_id'])) {
                $personData['OrgSMO_id'] = $resp_os[0]['OrgSMO_id'];
            } else {
                return array('Error_Msg' => 'Не удалось определить организацию, выдавшую полис');
            }
			
			// Заполняем данные документа УДЛ
			$personData['DocumentType_id'] = $DocumentType['DocumentType_id'];
			$personData['OrgDep_id'] = $OrgDep['OrgDep_id'];
			$personData['Document_Ser'] = $data['DocTypeSeries'];
			$personData['Document_Num'] = $data['DocTypeNumber'];
			$personData['Document_begDate'] = $data['DocTypeDate'];

            // Если пациент не найден, то – создается новый человек с указанными в запросе ФИО, ДР, неформализованным адресом.
            $this->load->model('Person_model');
            $resp_saveps = $this->Person_model->savePersonEditWindow($personData);
            if (!empty($resp_saveps[0]['Person_id'])) {
                $Person_id = $resp_saveps[0]['Person_id'];
            }
        }

        if (empty($Person_id)) {
            if (!empty($resp_saveps[0]['Error_Msg'])) {
                return $resp_saveps[0];
            } else {
                throw new Exception('Ошибка при сохранении пациента');
            }
        }

        // o   По ФИО + ДР  выполняется поиск места работы врача в МО, под которым выполнена авторизация.
        $resp_msf = $this->queryResult("
            with cte as (
                select  dbo.tzGetDate() as curDate
            )
            
			select
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.LpuSectionProfile_id as \"LpuSectionProfile_id\"
			from
				v_MedStaffFact msf
				inner join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
			where
				msf.Person_SurName = :Person_SurName
            and msf.Person_FirName = :Person_FirName
            and coalesce(msf.Person_SecName, '') = coalesce(:Person_SecName, '')
            and msf.Person_BirthDay = :Person_BirthDay
            and msf.MedPersonal_id = :MedPersonal_id
            and lu.LpuUnitType_id = 2 -- поликлиника
			and lu.LpuUnit_Code = :LpuUnit_Code
			limit 1
		", [
            'Person_SurName' => $data['SmV'],
            'Person_FirName' => $data['NmV'],
            'Person_SecName' => $data['PmV'],
            'Person_BirthDay' => $data['BDV'],
            'MedPersonal_id' => $data['session']['medpersonal_id'],
            'LpuUnit_Code' => $data['mcod'],
            'mc' => $data['mc']
        ]);

        if (!is_array($resp_msf) || count($resp_msf) == 0 ) {
          throw new Exception('Ошибка при определении рабочего места врача');
        }

        // данные по пациенту берем из PersonState
        $resp_ps = $this->queryResult("
			select
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\"
			from
				v_PersonState
			where
				Person_id = :Person_id
		", array(
            'Person_id' => $Person_id
        ));
        if (empty($resp_ps[0]['Person_id'])) {
            throw new Exception('Ошибка получения периодики пациента');
        }

        $setDate = date('Y-m-d');

        // ищем прикрепление
        $resp_pc = $this->queryResult("
			select
				PersonCard_id as \"PersonCard_id\"
			from
				v_PersonCard
			where
				Lpu_id = :Lpu_id
            and 
                Person_id = :Person_id
		", [
            'Person_id' => $resp_ps[0]['Person_id'],
            'Lpu_id' => $data['Lpu_id']
        ]);

        if (empty($resp_pc[0]['PersonCard_id'])) {
            // получаем код амбулаторной карты
            $this->load->model("Polka_PersonCard_model");
            $resp_code = $this->Polka_PersonCard_model->getPersonCardCode($data);
            if (empty($resp_code[0]['PersonCard_Code'])) {
                throw new Exception('Ошибка получения кода амбулаторной карты');
            }
            $PersonCard_Code = $resp_code[0]['PersonCard_Code'];

            $this->load->model('PersonAmbulatCard_model');
            $resp_pac = $this->PersonAmbulatCard_model->savePersonAmbulatCard([
                'Person_id' => $resp_ps[0]['Person_id'],
                'Server_id' => $resp_ps[0]['Server_id'],
                'Lpu_id' => $data['Lpu_id'],
                'PersonAmbulatCard_Num' => $PersonCard_Code,
                'pmUser_id' => $data['pmUser_id']
            ]);
            if (empty($resp_pac[0]['PersonAmbulatCard_id'])) {
                throw new Exception('Ошибка создания амбулаторной карты');
            }

            // создаем служебное прикрепление
            $resp_pc = $this->Polka_PersonCard_model->savePersonCard([
                'action' => 'add',
                'PersonCard_id' => null,
                'Person_id' => $resp_ps[0]['Person_id'],
                'Lpu_id' => $data['Lpu_id'],
                'PersonCard_Code' => $PersonCard_Code,
                'LpuAttachType_id' => 4,
                'PersonCard_begDate' => $setDate, // дата прикрепления
                'PersonCard_endDate' => null,
                'PersonCardAttach_id' => null,
                'LpuRegion_Fapid' => null,
                'LpuRegion_id' => null,
                'LpuRegionType_id' => null,
                'PersonAmbulatCard_id' => $resp_pac[0]['PersonAmbulatCard_id'],
                'Server_id' => $resp_ps[0]['Server_id'],
                'pmUser_id' => $data['pmUser_id']
            ]);
        }

        // если посещение уже есть, то новое не создаем
        $resp_ev = $this->queryResult("
			select
				EvnVizitPL_id as \"EvnVizitPL_id\"
			from
				v_EvnVizitPL
			where
				Person_id = :Person_id
				and Lpu_id = :Lpu_id
				and pmUser_insID = :pmUser_id
				and EvnVizitPL_setDate = :EvnVizitPL_setDate
		", [
            'Person_id' => $resp_ps[0]['Person_id'],
            'Lpu_id' => $data['Lpu_id'],
            'pmUser_id' => $data['pmUser_id'],
            'EvnVizitPL_setDate' => $setDate
        ]);

        if (empty($resp_ev[0]['EvnVizitPL_id'])) {
            // o   Создается ТАП и посещение на пациента по данным, полученным из ЕМИАС:
            // §  Дата, диагноз, врач – если найден.
            $this->load->model('EvnPL_model');
            $this->EvnPL_model->applyData([
                'EvnPL_id' => null,
                'session' => $data['session']
            ]);
            $this->EvnPL_model->setAttribute('lpu_id', $data['Lpu_id']);
            $this->EvnPL_model->setAttribute('setdt', $setDate);
            $this->EvnPL_model->setAttribute('person_id', $resp_ps[0]['Person_id']);
            $this->EvnPL_model->setAttribute('personevn_id', $resp_ps[0]['PersonEvn_id']);
            $this->EvnPL_model->setAttribute('server_id', $resp_ps[0]['Server_id']);
            $resp_epl = $this->EvnPL_model->_save();
            if (!empty($resp_epl[0]['EvnPL_id'])) {
                $EvnPL_id = $resp_epl[0]['EvnPL_id'];
            } else if (!empty($resp_epl[0]['Error_Msg'])) {
                return $resp_epl[0];
            } else {
               throw new Exception('Ошибка создания ТАП');
            }

            $this->load->model('EvnVizitPL_model');
            $this->EvnVizitPL_model->applyData([
                'EvnVizitPL_id' => null,
                'session' => $data['session']
            ]);
            $this->EvnVizitPL_model->setAttribute('pid', $EvnPL_id);
            $this->EvnVizitPL_model->setAttribute('lpu_id', $data['Lpu_id']);
            $this->EvnVizitPL_model->setAttribute('setdt', $setDate);
            $this->EvnVizitPL_model->setAttribute('person_id', $resp_ps[0]['Person_id']);
            $this->EvnVizitPL_model->setAttribute('personevn_id', $resp_ps[0]['PersonEvn_id']);
            $this->EvnVizitPL_model->setAttribute('server_id', $resp_ps[0]['Server_id']);
            $this->EvnVizitPL_model->setAttribute('medstafffact_id', $resp_msf[0]['MedStaffFact_id']);
            $this->EvnVizitPL_model->setAttribute('medpersonal_id', $resp_msf[0]['MedPersonal_id']);
            $this->EvnVizitPL_model->setAttribute('lpusection_id', $resp_msf[0]['LpuSection_id']);
            $this->EvnVizitPL_model->setAttribute('lpusectionprofile_id', $resp_msf[0]['LpuSectionProfile_id']);
            if (!empty($data['DiagCode'])) {
                $Diag_id= $this->getFirstResultFromQuery("
					select 
						Diag_id as \"Diag_id\"
					from
						v_Diag
					where
						Diag_Code = :Diag_Code
                    and
                        DiagLevel_id = 4
					limit 1
				", [
                    'Diag_Code' => $data['DiagCode']
                ]);

                if ($Diag_id !== false) {
                    $this->EvnVizitPL_model->setAttribute('diag_id', $Diag_id);
                }
            }
            $resp_evpl = $this->EvnVizitPL_model->_save();
            if (!empty($resp_evpl[0]['EvnVizitPL_id'])) {
                $EvnVizitPL_id = $resp_evpl[0]['EvnVizitPL_id'];
            } else if (!empty($resp_evpl[0]['Error_Msg'])) {
                return $resp_evpl[0];
            } else {
               throw new Exception('Ошибка создания посещения');
            }
        }

        return ['Error_Msg' => ''];
    }

	/**
	 * Получение списка открытых посещений поликлиники пациента для комбо
	 */
	function loadListOfOpenVisitsToThePatientClinic($data) {
		if(empty($data['MedStaffFact_id']) || empty($data['Person_id'])) return false;
		$query = "
			SELECT
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.EvnPL_NumCard as \"EvnPL_NumCard\",
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				to_char(EVPL.EvnVizitPL_setDT, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\",
				to_char(EVPL.EvnVizitPL_setDT, 'hh24:mi') as \"EvnVizitPL_setTime\",
				'Талон № ' || EPL.EvnPL_NumCard || '. Посещение ' || to_char(EVPL.EvnVizitPL_setDT, 'dd.mm.yyyy hh24:mi') as \"EvnVizitPL_text\"
			from v_EvnPL EPL
				INNER JOIN v_EvnVizitPL EVPL ON EVPL.EvnVizitPL_pid = EPL.EvnPL_id
			where 1=1 
				AND EPL.Person_id = :Person_id
				and coalesce(EPL.EvnPL_IsFinish,1) != 2
				and EVPL.EvnClass_id != 13 -- посещение стоматологии
				and EVPL.MedStaffFact_id = :MedStaffFact_id
			ORDER BY EVPL.EvnVizitPL_setDT DESC
		";
		//return $this->queryResult($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных о посещении
	 */
	function loadDataEvnVizitPL($data) {
		if(empty($data['EvnVizitPL_id'])) return false;
		$query = "
			SELECT 
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\"
				,EVPL.EvnVizitPL_pid as \"EvnVizitPL_pid\"
				,EVPL.PersonEvn_id as \"PersonEvn_id\"
				,EVPL.Person_id as \"Person_id\"
				,EVPL.Server_id as \"Server_id\"
				,EVPL.TimetableGraf_id as \"TimetableGraf_id\"
				,PS.Person_Firname as \"Person_Firname\"
				,PS.Person_Birthday as \"Person_Birthday\"
				,PS.Person_Secname as \"Person_Secname\"
				,PS.Person_Surname as \"Person_Surname\"
				,to_char(EVPL.EvnVizitPL_setDT, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\"
				,to_char(EVPL.EvnVizitPL_disDT, 'dd.mm.yyyy') as \"EvnVizitPL_disDate\"
			FROM v_EvnVizitPL EVPL
				left join v_PersonState_All PS on PS.Person_id = EVPL.Person_id
			WHERE EvnVizitPL_id = :EvnVizitPL_id
		";
		//return $this->queryResult($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получить ID последнего посещения пациента на текущий день
	 */
	public function getLastEvnVisitPLToday($data) {
		return $this->getFirstRowFromQuery("
			select
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_EvnVizitPL EVPL
			where
				EVPL.Person_id = :Person_id and
				cast(EVPL.EvnVizitPL_setDT as date) = cast(dbo.tzGetDate() as date)
			order by
				\"EvnVizitPL_setDT\" desc
			limit 1
		", array('Person_id' => $data['Person_id']));
	}
}
