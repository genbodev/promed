<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceEMIAS_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version      01.10.2019
 *
 */

class ServiceEMIAS_model extends SwModel {
	protected $ServiceList_id;
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->model('ServiceList_model');
		$this->load->helper('ServiceListLog');
		$this->ServiceList_id = $this->ServiceList_model->getServiceListId('API');
	}
	
	/**
	 * Выполнение запросов к сервису ЕМИАС API и обработка ошибок, которые возвращает сервис
	 */
	function exec($methodType, $method, $data, $log, $objectId = null) {
		$this->load->library('swServiceEMIAS', $this->config->item('EMIAS'), 'service');

		$result = $this->service->data($methodType, $method, $data, $log, $objectId);
		if (is_array($result) && !empty($result['errorMsg'])) {
			$log->add(false, $result['errorMsg']);
			return $result;
		}
		return $result;
	}
	
	/*
	 * Определение пользователя по переданному тикету
	 */
	function getUserByTicket($ticket) {
		$this->load->library('swServiceEMIAS', $this->config->item('EMIAS'), 'service');
		return $this->service->authData($ticket);
	}

	/*
	 * Определение серии и номера рецепта посредством сервиса ваимодействия с API СПО УЛО
	 */
	function getEvnReceptSerialNum($data) {
		//сперва найдем код, который будет использоваться
		$query = "
			declare @date date = cast(dbo.tzGetDate() as date);
			
			select top 1 LS.LpuUnit_id,LP.LpuPeriodDLO_Code,L.Lpu_Ouz
			from
				v_LpuSection LS with (nolock)
				left join v_LpuPeriodDLO LP with (nolock) on
					LP.LpuUnit_id = LS.LpuUnit_id
					and (@date between LP.LpuPeriodDLO_begDate and COALESCE(LP.LpuPeriodDLO_endDate,@date) )
				left join v_Lpu L with (nolock) on LS.Lpu_id = L.Lpu_id
			where LS.LpuSection_id = :LpuSection_id
				  and L.Lpu_id = :Lpu_id
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		
		$data['LpuUnit_id'] = $result['LpuUnit_id'];
		$data['LpuPeriodDLO_Code'] = $result['LpuPeriodDLO_Code'];
		$data['Lpu_Ouz'] = $result['Lpu_Ouz'];
		//$data['ClinicCode'] = !empty($data['LpuPeriodDLO_Code']) ? $data['LpuPeriodDLO_Code']:$data['Lpu_Ouz'];

		//получаем MCOD врача
		$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		$query = "
			declare @date date = cast(dbo.tzGetDate() as date);
			
			select top 1
				MPDP.MedPersonalDLOPeriod_MCOD
			from
				r50.MedPersonalDLOPeriod MPDP with(nolock)
				left join r50.MedstaffFactDLOPeriodLink MFDPL with(nolock) on
					MFDPL.MedPersonalDLOPeriod_id = MPDP.MedPersonalDLOPeriod_id
					and ( @date between MFDPL.MedstaffFactDLOPeriodLink_begDate and COALESCE(MFDPL.MedstaffFactDLOPeriodLink_endDate,@date) )
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MFDPL.MedStaffFact_id
			where
				MSF.Lpu_id = :Lpu_id
				and (MSF.LpuSection_id is null or MSF.LpuSection_id = :LpuSection_id)
				and MSF.MedPersonal_id = :MedPersonal_id
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		$data['ClinicCode'] = $result['MedPersonalDLOPeriod_MCOD'];

		//найдем свободный номер в таблице свободных номеров
		/*
		if (!empty($data['LpuPeriodDLO_Code'])) {
			$where = " and LpuUnit_id = :LpuUnit_id";
		}
		else {
			$where = " and LpuUnit_id is null";
		}*/
		$where = " and MCOD = :ClinicCode";
		$query = "
			select top 1 *
			from r50.ReceptFreeNum with (nolock)
			where (1 = 1)
				and Lpu_id = :Lpu_id
				{$where}
				and ( EvnRecept_id is null and DATEDIFF(MINUTE, ReceptFreeNum_updDT, dbo.tzGetDate())>=60 )
			order by ReceptFreeNum_updDT desc, ReceptFreeNum_insDT desc
		";
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		
		$result = $this->getFirstRowFromQuery($query, $queryParams);
		
		if (!empty($result['ReceptFreeNum_Num']) && !empty($result['ReceptFreeNum_Ser'])) {
			//бронируем его датой изменения
			$result['pmUser_id'] = $data['pmUser_id'];
			$this->queryResult("
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :ReceptFreeNum_id;

				exec r50.p_ReceptFreeNum_upd
					@ReceptFreeNum_id = @Res output,
					@Lpu_id = :Lpu_id,
					@LpuUnit_id = :LpuUnit_id,
					@ReceptFreeNum_Ser = :ReceptFreeNum_Ser,
					@ReceptFreeNum_Num = :ReceptFreeNum_Num,
					@EvnRecept_id = :EvnRecept_id,
					@MCOD = :MCOD,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output

				select @Res as ReceptFreeNum_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", $result);
			
			//возвращаем серию и номер
			$response = array(
				'Recipe' => array(
					'RecipeNumber' => $result['ReceptFreeNum_Num'],
					'RecipeSerial' => $result['ReceptFreeNum_Ser']
				)
			);
		}
		else {
			//если в таблице нет свободного номера, берем свободный номер из API
			$response = $this->doRequest($data);
		}

		return $response;
	}

	/*
	 * Сервис для получения номеров по крону
	 */
	function getEvnReceptSerialNumCron() {

		$query = "
			declare @date date = cast(dbo.tzGetDate() as date);
			
			select
				MPDP.MedPersonalDLOPeriod_MCOD,
				MSF.Lpu_id,
				MSF.LpuUnit_id
			from
				r50.MedPersonalDLOPeriod MPDP with(nolock)
				left join r50.MedstaffFactDLOPeriodLink MFDPL with(nolock) on
					MFDPL.MedPersonalDLOPeriod_id = MPDP.MedPersonalDLOPeriod_id
					and ( @date between MFDPL.MedstaffFactDLOPeriodLink_begDate and COALESCE(MFDPL.MedstaffFactDLOPeriodLink_endDate, @date) )
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MFDPL.MedStaffFact_id
			where
				MSF.Lpu_id is not null
			group by
				MPDP.MedPersonalDLOPeriod_MCOD, MSF.Lpu_id, MSF.LpuUnit_id
		";
		$llo_mp = $this->queryResult($query);

		foreach($llo_mp as $lfn) {
			$freeReceptNumCount = $this->getFirstRowFromQuery('
				select count(*) [cnt]
					from r50.ReceptFreeNum rfn (nolock)
					where
						rfn.Lpu_id = :Lpu_id
						and (rfn.LpuUnit_id = :LpuUnit_id or :LpuUnit_id is null)
						and rfn.MCOD = :MedPersonalDLOPeriod_MCOD
						and EvnRecept_id is null', $lfn);
			$lfn['free'] = $freeReceptNumCount['cnt'];

			if ($lfn['free'] <= 100) {

				$planval = 1000 - $lfn['free'];
				for($i=0;$i < $planval; $i++) {

					$response = $this->doRequest([
						'ClinicCode' => $lfn['MedPersonalDLOPeriod_MCOD'],
						'Lpu_id' => $lfn['Lpu_id'],
						'LpuUnit_id' => $lfn['LpuUnit_id'],
						'pmUser_id' => 1,
					]);
					if (!empty($response['Error_Msg'])) break;

					usleep(250000);
				}
			}
		}
	}

	/*
	 * Выполнение запроса и запись в БД
	 */
	function doRequest($data) {
		$log = new ServiceListLog($this->ServiceList_id, $data['pmUser_id']);
		$log->start();
		$response = $this->exec('GET', 'Recipe/GetNewRecipeTemplate', [
			'ClinicCode' => str_pad($data['ClinicCode'], 4, '0', STR_PAD_LEFT)
		], $log);
		$log->finish(!is_array($response) || empty($response['errorMsg']));

		//добавляем полученные номер и серию в таблицу свободных номеров
		if (!empty($response['Recipe']['RecipeNumber']) && !empty($response['Recipe']['RecipeSerial'])) {
			$this->queryResult("
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :ReceptFreeNum_id;

				exec r50.p_ReceptFreeNum_ins
					@ReceptFreeNum_id = @Res output,
					@Lpu_id = :Lpu_id,
					@LpuUnit_id = :LpuUnit_id,
					@ReceptFreeNum_Num = :ReceptFreeNum_Num,
					@ReceptFreeNum_Ser = :ReceptFreeNum_Ser,
					@EvnRecept_id = :EvnRecept_id,
					@MCOD = :MCOD,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output

				select @Res as ReceptFreeNum_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", array(
				'ReceptFreeNum_id' => null,
				'EvnRecept_id' => null,
				'MCOD' => $data['ClinicCode'],
				'ReceptFreeNum_Num' => $response['Recipe']['RecipeNumber'],
				'ReceptFreeNum_Ser' => $response['Recipe']['RecipeSerial'],
				'Lpu_id' => $data['Lpu_id'],
				'LpuUnit_id' => !empty($data['LpuUnit_id']) ? $data['LpuUnit_id'] : null,
				'pmUser_id' => $data['pmUser_id']
			));
		}
		else {
			$message = "";
			if (!empty($response['Message'])) {
				$message = " ({$response['Message']})";
			}
			return array('Error_Msg' => 'Рецепт выписать невозможно, так как не определены серия и номер рецепта из-за ошибки в работе сервиса предоставления номеров рецептов' . $message . '. Обратитесь к администратору системы');
		}

		return $response;
	}

	/*
	 * Получение сведений о наличии ЛП
	 */
	function getRemains($data) {

		if (
			(
				empty($data['Drug_id']) &&
				(empty($data['DrugComplexMnn_id']) || $this->getRegionNick() != 'msk') //получение информации по комплексному мнн в данный момент предусмотрено только для Москвы
			) ||
			empty($data['PrivilegeType_id']) ||
			empty($data['DrugFinance_id']) ||
			empty($data['LpuSection_id']) ||
			empty($data['WhsDocumentCostItemType_id']) ||
			empty($data['pmUser_id'])
		) {
			throw new Exception('Не переданы обязательные параметры');
		}

		$PrivilegeCategoryId = $this->getFirstResultFromQuery("select top 1 PrivilegeType_Code from v_PrivilegeType (nolock) where PrivilegeType_id = :PrivilegeType_id", $data);
		if (empty($PrivilegeCategoryId)) throw new Exception('Не удалось определить Идентификатор категории льготы');

		$FundingSourceId = $this->getFirstResultFromQuery("select FundingSource_id from r50.FundingSourceLink (nolock) where DrugFinance_id = :DrugFinance_id", $data);
		if (empty($FundingSourceId)) throw new Exception('Не удалось определить Источник финансирования');

		$Lpu_id = $data['Lpu_id'];

		//получаем MCOD врача
		$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		$query = "
			declare @date date = cast(dbo.tzGetDate() as date);
			
			select top 1
				MPDP.MedPersonalDLOPeriod_MCOD
			from
				r50.MedPersonalDLOPeriod MPDP with(nolock)
				left join r50.MedstaffFactDLOPeriodLink MFDPL with(nolock) on
					MFDPL.MedPersonalDLOPeriod_id = MPDP.MedPersonalDLOPeriod_id
					and ( @date between MFDPL.MedstaffFactDLOPeriodLink_begDate and COALESCE(MFDPL.MedstaffFactDLOPeriodLink_endDate, @date) )
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MFDPL.MedStaffFact_id
			where
				MSF.Lpu_id = :Lpu_id
				and (MSF.LpuSection_id is null or MSF.LpuSection_id = :LpuSection_id)
				and MSF.MedPersonal_id = :MedPersonal_id
		";
		$result = $this->getFirstRowFromQuery($query, $data);
		$ClinicCode = $result['MedPersonalDLOPeriod_MCOD'];

		if (empty($ClinicCode)) throw new Exception('Не удалось определить Код лечебного учреждения');

		$params = [
			'PrivilegeCategoryId' => $PrivilegeCategoryId,
			'FundingSourceId' => $FundingSourceId,
			'ClinicCode' => str_pad($ClinicCode, 4, '0', STR_PAD_LEFT),
			'PaymentTypeId' => 1 //Необязательный параметр. По умолчанию = 1; Значения: 1 – 100% льгота, 2 – 50% льгота
		];

		//получение кодов медикамента
		if (!empty($data['Drug_id'])) {
			$PrivilegedDrugId = $this->getFirstResultFromQuery("select top 1 DrugNomen_Code from rls.v_DrugNomen (nolock) where Drug_id = :Drug_id", $data);
			if (!empty($PrivilegedDrugId)) {
				$params['PrivilegedDrugId'] = $PrivilegedDrugId;
			} else {
				throw new Exception('Не удалось определить Номенклатурный номер товарной позиции');
			}
		} else if (!empty($data['DrugComplexMnn_id'])) {
			$query = "
				select
					sud.c_mnn,
					sud.c_lf,
					sud.DosageId
				from
					rls.v_Drug d with (nolock)
					inner join rls.v_DrugNomen dn with (nolock) on dn.Drug_id = d.Drug_id
					inner join r50.SPOULODrug sud with (nolock) on cast(sud.NOMK_LS as varchar) = dn.DrugNomen_Code
				 where
					d.DrugComplexMnn_id = :DrugComplexMnn_id and
					sud.c_mnn is not null and
					sud.c_lf is not null and
					sud.DosageId is not null
			";
			$mnn_data = $this->getFirstRowFromQuery($query, $data);
			if (!empty($mnn_data['c_mnn']) && !empty($mnn_data['c_lf'])) {
				$params['INNId'] = $mnn_data['c_mnn']; //Идентификатор МНН ЛП
				$params['DrugFormId'] = $mnn_data['c_lf']; //Идентификатор лекарственной формы ЛП
				$params['DosageId'] = $mnn_data['DosageId']; //Идентификатор дозировки
			} else {
				throw new Exception('Не удалось определить код ЛП в региональном справочнике');
			}
		}

		$log = new ServiceListLog($this->ServiceList_id, $data['pmUser_id']);
		$log->start();
		$response = $this->exec('GET', 'Drug/GetRemains', $params, $log);
		$log->finish(!is_array($response) || empty($response['errorMsg']));

		if (isset($response['Message']) && $response['Message'] == 'Не найдено') {
			return ['success' => true, 'Error_Msg' => ''];
		}

		if (isset($response['Remains']) && is_array($response['Remains']) && count($response['Remains']) == 0) {
			return ['success' => true, 'Error_Msg' => ''];
		}
		
		if (!count($response) || empty($response['Remains'])) {
			throw new Exception('Остатки не определены из-за ошибки в работе сервиса. Обратитесь к администратору системы');
		}

		$resp = $response['Remains'];
		
		if (!empty($resp['DrugstoreId'])) {
			$resp = [$resp];
		}
		
		foreach ($resp as $response) {

			$response['DrugQuantity'] = str_replace(',', '.', $response['DrugQuantity']);

			// Аптека
			$org = $this->getFirstRowFromQuery("
				select top 1 o.Org_id, orf.OrgFarmacy_id
				from v_Org o (nolock) 
				left join v_OrgFarmacy orf (nolock) on o.Org_id = orf.Org_id
				where o.OrgType_id = 4 and o.Org_Code = :Org_Code
			", ['Org_Code' => $response['DrugstoreId']]);

			if ($org == false || empty($org['OrgFarmacy_id'])) {

				$this->load->model('Org_model', 'Org_model');

				$org = $this->Org_model->saveOrg([
					'Org_id' => $org != false ? $org['Org_id'] : null,
					'Org_Code' => $response['DrugstoreId'],
					'Org_Name' => $response['DrugstoreId'],
					'Org_Nick' => $response['DrugstoreId'],
					'OrgType_id' => 4,
					'fromAPI' => true,
					'UAddress_id' => null,
					'PAddress_id' => null,
					'Org_INN' => null,
					'Org_OGRN' => null,
					'Server_id' => 0,
					'Org_Description' => null,
					'Org_rid' => null,
					'Org_begDate' => null,
					'Org_endDate' => null,
					'Okved_id' => null,
					'Oktmo_id' => null,
					'Okopf_id' => null,
					'Okfs_id' => null,
					'Org_OKATO' => null,
					'Org_KPP' => null,
					'Org_OKPO' => null,
					'Org_Phone' => null,
					'Org_Email' => null,
					'Org_StickNick' => null,
					'Org_ONMSZCode' => null,
					'Org_Marking' => null,
					'KLCountry_id' => null,
					'KLRGN_id' => null,
					'KLSubRGN_id' => null,
					'KLCity_id' => null,
					'KLTown_id' => null,
					'OrgType_SysNick' => 'farm',
					'OrgFarmacy_ACode' => $response['DrugstoreId'],
					'OrgFarmacy_HowGo' => null,
					'OrgFarmacy_IsEnabled' => 2,
					'OrgFarmacy_IsFedLgot' => 2,
					'OrgFarmacy_IsRegLgot' => 2,
					'OrgFarmacy_IsNozLgot' => 2,
					'OrgFarmacy_IsNarko' => 2,
					'OrgFarmacy_IsFarmacy' => 2,
					'pmUser_id' => $data['pmUser_id']
				]);

				if ($org != false) {
					$org = $org[0];
				} else {
					throw new Exception('При сохранении аптечной организации произошла ошибка');
				}
			}

			// связь МО и аптеки
			$OrgFarmacyIndex_id = $this->getFirstResultFromQuery("
				select top 1 OrgFarmacyIndex_id
				from v_OrgFarmacyIndex (nolock) 
				where 
					OrgFarmacy_id = :OrgFarmacy_id and 
					Lpu_id = :Lpu_id
			", [
				'OrgFarmacy_id' => $org['OrgFarmacy_id'],
				'Lpu_id' => $Lpu_id
			]);

			if ($OrgFarmacyIndex_id == false) {

				$org_farmacy_index = $this->getFirstResultFromQuery("
					select isnull(max(OrgFarmacyIndex_Index), 0)+1 as max_index
					from OrgFarmacyIndex with (nolock)
					where Lpu_id = :Lpu_id
				", [
					'Lpu_id' => $Lpu_id
				]);

				$sql = "
					declare
						@ErrCode int,
						@OFIndex_id bigint,
						@ErrMsg varchar(400);
					set @OFIndex_id = null;
					exec p_OrgFarmacyIndex_ins
						@Server_id = :Server_id,
						@OrgFarmacyIndex_id = @OFIndex_id output,
						@OrgFarmacy_id = :OrgFarmacy_id,
						@Lpu_id = :Lpu_id,
						@OrgFarmacyIndex_Index = :OrgFarmacyIndex_Index,
						@OrgFarmacyIndex_IsEnabled = 1,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;
					select @OFIndex_id as OrgFarmacyIndex_id, @ErrCode as ErrCode, @ErrMsg as ErrMsg
				";

				$queryParams = [];
				$queryParams['Lpu_id'] = $Lpu_id;
				$queryParams['OrgFarmacy_id'] = $org['OrgFarmacy_id'];
				$queryParams['OrgFarmacyIndex_Index'] = $org_farmacy_index;
				$queryParams['pmUser_id'] = $data['pmUser_id'];
				$queryParams['Server_id'] = 0;

				$res = $this->getFirstRowFromQuery($sql, $queryParams);

				if ( $res == false ) {
					throw new Exception('При сохранении аптечной организации произошла ошибка');
				}

				$OrgFarmacyIndex_id = $res['OrgFarmacyIndex_id'];
			}

			// Контрагент
			$Contragent_id = $this->getFirstResultFromQuery("
				select top 1 Contragent_id
				from v_Contragent (nolock)
				where 
					Org_id = :Org_id and 
					ContragentType_id = 3
			", [
				'Org_id' => $org['Org_id']
			]);
			
			if ($Contragent_id == false) {
				
				$sql = "
					declare
						@Contragent_id bigint,
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_Contragent_ins
						@Contragent_id = @Contragent_id output,
						@Contragent_Code = :Contragent_Code,
						@Contragent_Name = :Contragent_Name,
						@Org_id = :Org_id,
						@ContragentType_id = 3,
						@Server_id = 0,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					select @Contragent_id as Contragent_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
				";

				$queryParams = [
					'Contragent_Code' => $response['DrugstoreId'],
					'Contragent_Name' => $response['DrugstoreId'],
					'Org_id' => $org['Org_id'],
					'pmUser_id' => $data['pmUser_id']
				];

				$res = $this->getFirstRowFromQuery($sql, $queryParams);
				
				if ( $res == false ) {
					throw new Exception('При сохранении аптечной организации произошла ошибка');
				}
				
				$Contragent_id = $res['Contragent_id'];
			}
			
			// Склад Аптеки
			$Storage_id = $this->getFirstResultFromQuery("
				select top 1 Storage_id
				from StorageStructLevel (nolock)
				where Org_id = :Org_id
			", [
				'Org_id' => $org['Org_id']
			]);

			if ($Storage_id == false) {

				$sql = "
					declare
						@Storage_id bigint,
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_Storage_ins
						@Storage_id = @Storage_id output,
						@Storage_Code = :Storage_Code,
						@Storage_Name = :Storage_Name,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					select @Storage_id as Storage_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
				";

				$queryParams = [
					'Storage_Code' => $response['DrugstoreId'],
					'Storage_Name' => $response['DrugstoreId'],
					'pmUser_id' => $data['pmUser_id']
				];

				$res = $this->getFirstRowFromQuery($sql, $queryParams);

				if ( $res == false ) {
					throw new Exception('При сохранении аптечной организации произошла ошибка');
				}

				$Storage_id = $res['Storage_id'];

				$sql = "
					declare
						@StorageStructLevel_id bigint,
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_StorageStructLevel_ins
						@StorageStructLevel_id = @StorageStructLevel_id output,
						@Storage_id = :Storage_id,
						@Org_id = :Org_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					select @StorageStructLevel_id as StorageStructLevel_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
				";

				$queryParams = [
					'Storage_id' => $Storage_id,
					'Org_id' => $org['Org_id'],
					'pmUser_id' => $data['pmUser_id']
				];

				$res = $this->getFirstRowFromQuery($sql, $queryParams);

				if ( $res == false ) {
					throw new Exception('При сохранении аптечной организации произошла ошибка');
				}

				$StorageStructLevel_id = $res['StorageStructLevel_id'];
			}

			// Остатки
			$DrugOstatRegistry_id = $this->getFirstResultFromQuery("
				select top 1 DrugOstatRegistry_id 
				from v_DrugOstatRegistry (nolock)
				where 
					Contragent_id = :Contragent_id and
					DrugFinance_id = :DrugFinance_id and
					WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id and
					Drug_id = :Drug_id and
					PrepSeries_id is null and
					SubAccountType_id = 1
			", [
				'Contragent_id' => $Contragent_id,
				'DrugFinance_id' => $data['DrugFinance_id'],
				'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
				'Drug_id' => $data['Drug_id']
			], true);

			$proc = empty($DrugOstatRegistry_id) ? 'ins' : 'upd';

			$sql = "
				declare
					@Current_Date datetime,
					@DrugOstatRegistry_id bigint = :DrugOstatRegistry_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);

				set @Current_Date = dbo.tzGetDate();
					
				exec dbo.p_DrugOstatRegistry_{$proc}
					@DrugOstatRegistry_id = @DrugOstatRegistry_id output,
					@Org_id = :Org_id,
					@DrugFinance_id = :DrugFinance_id,
					@Drug_id = :Drug_id,
					@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
					@SubAccountType_id = 1,
					@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
					@Contragent_id = :Contragent_id,
					@Storage_id = :Storage_id,
					@DrugOstatRegistry_OstatDate = :DrugOstatRegistry_OstatDate,
					@DrugOstatRegistry_ImportDate = @Current_Date,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output
				select @DrugOstatRegistry_id as DrugOstatRegistry_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
			";

			$queryParams = [
				'DrugOstatRegistry_id' => $DrugOstatRegistry_id,
				'Org_id' => $org['Org_id'],
				'DrugFinance_id' => $data['DrugFinance_id'],
				'Drug_id' => $data['Drug_id'],
				'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
				'DrugOstatRegistry_Kolvo' => $response['DrugQuantity'],
				'Contragent_id' => $Contragent_id,
				'Storage_id' => $Storage_id,
				'DrugOstatRegistry_OstatDate' => date('Y-m-d', strtotime($response['TimeStamp'])),
				'pmUser_id' => $data['pmUser_id']
			];

			//echo getDebugSQL($sql, $queryParams); exit;
			$res = $this->getFirstRowFromQuery($sql, $queryParams);

			if ( $res == false ) {
				throw new Exception('При сохранении остатков произошла ошибка');
			}
		}
	
		return ['success' => true, 'Error_Msg' => ''];
	}

	function GetPatientId($data) {
		$log = new ServiceListLog($this->ServiceList_id, $data['pmUser_id']);
		$log->start();
		$response = $this->exec('GET', 'Patient/GetPatientId', $data, $log);
		$log->finish(!is_array($response) || empty($response['errorMsg']));

		if (false && empty($response['PatientId'])) {
			throw new Exception('Пациент с такими данными не найден');
		}
		return $response;
	}

	/**
	 *
	 * @param string $title - Заголовок блока данных
	 * @param array $data - Массив входных данных
	 * @return array Форматированный набор данных для записи в детальный лог
	 */
	private function preFormatedDetailedPatientLog($title, $data) {
		$arr = [
			$title,
			'ФИО: '. $data['Surname'] . ' ' . $data['Name'] . ' ' . $data['Patronymic'],
			'ДР: '. $data['Birthday'],
			'Пол: '. $data['Sex'],
			'Иногородний пациент: '. $data['Resident'],
			'Регион пациента: '. $data['AreaId'],
			'Адрес пациента: '. $data['Address'],
			'Сведения о документе удостоверяющим личность (Тип документа / Серия документа / Номер документа / Дата выдачи / Наименование органа выдавшего документ): '.
				'(' . $data['IdentityDocument']['TypeId'] . '/' .
				$data['IdentityDocument']['Serial'] . '/' .
				$data['IdentityDocument']['Number'] . '/' .
				$data['IdentityDocument']['IssueDate'] . '/' .
				$data['IdentityDocument']['Issuer'] . ')',
			'СНИЛС: '. $data['SNILS'],
			'Полис: '. $data['Polis']['Serial'] . ' ' . $data['Polis']['Number'],
			'Клиника (Код медицинской организации / ОГРН медицинской организации): ' . $data['Clinic']['Code'] . '/' . $data['Clinic']['OGRN']
		];
		if (isset($data['PatientId'])) {
			$arr[] = 'Id пациента: ' . $data['PatientId'];
		}
		return $arr;
	}

	function AddPatient($data) {
		$log = new ServiceListLog($this->ServiceList_id, $data['pmUser_id']);
		$log->start();
		$log->add(true, $this->preFormatedDetailedPatientLog('Отправка данных пациента в СПО УЛО', $data));
		$response = $this->exec('POST', 'Patient/AddPatient', $data, $log);
		$log->finish(!is_array($response) || empty($response['errorMsg']));

		if (false && empty($response['PatientId'])) {
			throw new Exception('Ошибка добавления пациента');
		}
		return $response;
	}

	function UpdatePatient($data) {
		$log = new ServiceListLog($this->ServiceList_id, $data['pmUser_id']);
		$log->start();
		$log->add(true, $this->preFormatedDetailedPatientLog('Отправка обновленных данных пациента в СПО УЛО', $data));
		$response = $this->exec('PUT', 'Patient/UpdatePatient', $data, $log);
		$log->finish(!is_array($response) || empty($response['errorMsg']));
		if (!empty($response)) {
			throw new Exception('Ошибка при обновлений данных пациента');
		}
		return true;
	}

	/**
	 * Передача данных в API СЛО УЛО
	 */
	function syncAll($data) {
		$log = new ServiceListLog($this->ServiceList_id, $data['pmUser_id']);
		$log->start();

		try {
			// Передача данных о пациентах
			$this->syncPerson($data, $log);

			// Передача данных о льготах
			$this->syncPersonPrivilege($data, $log);

			// Передача данных о рецептах
			$this->syncEvnRecept($data, $log);

			$log->finish(true);
			return ['Error_Msg' => ''];
		} catch (Exception $e) {
			$log->add(false, $e->getMessage());
			$log->finish(false);
			return ['Error_Msg' => $e->getMessage()];
		}
	}

	/**
	 * Передача данных о льготах
	 */
	function syncPersonPrivilege($data, $log) {
		// 4. Передача данных о новых льготах
		// 5. Передача данных об изменении льгот
		$filter = "";
		$queryParams = [];
		if (!empty($data['Person_id'])) {
			$filter .= " and pp.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		$resp_pp = $this->queryResult("
			declare @curDate date = dbo.tzGetDate();
			
			select
				pp.PersonPrivilege_id,
				sppr.SPOULOPrivilege_SPOULOId,
				spp.SPOULOPerson_SPOULOId,
				pt.PrivilegeType_Code,
				convert(varchar(10), pp.PersonPrivilege_begDate, 120) as PersonPrivilege_begDate,
				convert(varchar(10), pp.PersonPrivilege_endDate, 120) as PersonPrivilege_endDate,
				d.Diag_Code,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				ps.Person_Snils,
				convert(varchar(10), ps.Person_BirthDay, 120) as Person_BirthDay,
				case when ps.PolisType_id = 4 then '' else ps.Polis_Ser end as Polis_Ser,
				ISNULL(ps.Person_EdNum, ps.Polis_Num) as Polis_Num
			from
				v_PersonPrivilege pp (nolock)
				inner join r50.v_SPOULOPerson spp (nolock) on pp.Person_id = spp.Person_id
				inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
				inner join v_PersonState ps (nolock) on ps.Person_id = pp.Person_id
				left join v_Diag d (nolock) on d.Diag_id = pp.Diag_id
				left join r50.v_SPOULOPrivilege sppr (nolock) on sppr.PersonPrivilege_id = pp.PersonPrivilege_id
			where
				spp.SPOULOPerson_SPOULOId is not null
				and pp.PersonPrivilege_begDate <= @curDate
				and ISNULL(pp.PersonPrivilege_endDate, @curDate) >= @curDate
				and pt.PrivilegeType_Code = '510'
				and (
					sppr.SPOULOPrivilege_SPOULOId is null -- новая
					or pp.PersonPrivilege_updDT > sppr.SPOULOPrivilege_updDT -- изменена
				)
				{$filter}
		", $queryParams);

		foreach($resp_pp as $one_pp) {
			$log->add(true, [
				'Отправка данных о льготе',
				'ФИО: '. $one_pp['Person_SurName'] . ' ' . $one_pp['Person_FirName'] . ' ' . $one_pp['Person_SecName'],
				'ДР: '. $one_pp['Person_BirthDay'],
				'СНИЛС: '. $one_pp['Person_Snils'],
				'Полис: '. $one_pp['Polis_Ser'] . ' ' . $one_pp['Polis_Num'],
				'Код льготы: ' . $one_pp['PrivilegeType_Code'],
				'Период действия: ' . $one_pp['PersonPrivilege_begDate'] . '-' . $one_pp['PersonPrivilege_endDate']
			]);
			$params = [
				'PatientId' => $one_pp['SPOULOPerson_SPOULOId'],
				'CategoryId' => $one_pp['PrivilegeType_Code'],
				'BeginDate' => $one_pp['PersonPrivilege_begDate'],
				'EndDate' => $one_pp['PersonPrivilege_endDate'],
				'MKBCode' => $one_pp['Diag_Code']
			];
			$method = 'Privilege/AddPatientPrivilege';
			$methodType = 'POST';
			if (!empty($one_pp['SPOULOPrivilege_SPOULOId'])) {
				$params['PrivilegeId'] = $one_pp['SPOULOPrivilege_SPOULOId'];
				$method = 'Privilege/UpdatePatientPrivilege';
				$methodType = 'PUT';
			} else {
				$response = $this->exec('GET', 'Patient/GetPatientPrivileges', [
					'PatientId' => $one_pp['SPOULOPerson_SPOULOId']
				], $log);
				if (!empty($response['Privileges']) && is_array($response['Privileges'])) {
					foreach($response['Privileges'] as $onePrivilege) {
						if ($onePrivilege['PrivilegeCategoryId'] == $one_pp['PrivilegeType_Code']) {
							$params['PrivilegeId'] = $onePrivilege['PrivilegeId'];
							$method = 'Privilege/UpdatePatientPrivilege';
							$methodType = 'PUT';
							break;
						}
					}
				}
			}
			$response = $this->exec($methodType, $method, $params, $log, $one_pp['PersonPrivilege_id']);
			if (empty($response['PrivilegeId'])) {
				$log->add(false, 'Не удалось отправить льготу: ' . var_export($response, true));
			} else {
				// сохраняем PrivilegeId в SPOULOPrivilege
				$this->saveSPOULOPrivilege(array(
					'PersonPrivilege_id' => $one_pp['PersonPrivilege_id'],
					'SPOULOPrivilege_SPOULOId' => $response['PrivilegeId'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		// 8. Передача данных об удалении льгот
		$filter = "";
		$queryParams = [];
		if (!empty($data['Person_id'])) {
			$filter .= " and pp.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		$resp_pp = $this->queryResult("
			select
				pp.PersonPrivilege_id,
				sppr.SPOULOPrivilege_SPOULOId,
				spp.SPOULOPerson_SPOULOId,
				pt.PrivilegeType_Code,
				pp.PrivilegeCloseType_id,
				convert(varchar(10), pp.PersonPrivilege_begDate, 120) as PersonPrivilege_begDate,
				convert(varchar(10), pp.PersonPrivilege_endDate, 120) as PersonPrivilege_endDate,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				ps.Person_Snils,
				convert(varchar(10), ps.Person_BirthDay, 120) as Person_BirthDay,
				case when ps.PolisType_id = 4 then '' else ps.Polis_Ser end as Polis_Ser,
				ISNULL(ps.Person_EdNum, ps.Polis_Num) as Polis_Num
			from
				PersonPrivilege pp (nolock)
				inner join r50.v_SPOULOPerson spp (nolock) on pp.Person_id = spp.Person_id
				inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
				inner join r50.v_SPOULOPrivilege sppr (nolock) on sppr.PersonPrivilege_id = pp.PersonPrivilege_id
				inner join v_PersonState ps (nolock) on ps.Person_id = pp.Person_id
			where
				pp.PersonPrivilege_deleted = 2
				and spp.SPOULOPerson_SPOULOId is not null
				and sppr.SPOULOPrivilege_SPOULOId is not null
				and pp.PersonPrivilege_delDT > sppr.SPOULOPrivilege_updDT -- удалена позже последней отправки
				{$filter}
		", $queryParams);

		foreach($resp_pp as $one_pp) {
			$log->add(true, [
				'Отправка данных об удалении льготы',
				'ФИО: '. $one_pp['Person_SurName'] . ' ' . $one_pp['Person_FirName'] . ' ' . $one_pp['Person_SecName'],
				'ДР: '. $one_pp['Person_BirthDay'],
				'СНИЛС: '. $one_pp['Person_Snils'],
				'Полис: '. $one_pp['Polis_Ser'] . ' ' . $one_pp['Polis_Num'],
				'Код льготы: ' . $one_pp['PrivilegeType_Code'],
				'Период действия: ' . $one_pp['PersonPrivilege_begDate'] . '-' . $one_pp['PersonPrivilege_endDate']
			]);
			$params = [
				'PatientId' => $one_pp['SPOULOPerson_SPOULOId'],
				'CategoryId' => $one_pp['PrivilegeType_Code'],
				'PrivilegeId' => $one_pp['SPOULOPrivilege_SPOULOId'],
				'DeleteReasonId' => $one_pp['PrivilegeCloseType_id'] == 5 ? 6 : 3
			];
			$method = 'Privilege/RemovePatientPrivilege';
			$methodType = 'POST';
			$response = $this->exec($methodType, $method, $params, $log, $one_pp['PersonPrivilege_id']);
			if (empty($response['PrivilegeId'])) {
				$log->add(false, 'Не удалось отправить данные об удалении льготы: ' . var_export($response, true));
			} else {
				// сохраняем PrivilegeId в SPOULOPrivilege
				$this->saveSPOULOPrivilege(array(
					'PersonPrivilege_id' => $one_pp['PersonPrivilege_id'],
					'SPOULOPrivilege_SPOULOId' => $response['PrivilegeId'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}
	}

	/**
	 * Передача данных о рецепте
	 */
	function syncEvnRecept($data, $log) {
		// 6. Передача данных о новых льготных рецептах
		$filter = "";
		$queryParams = [];
		if (!empty($data['Person_id'])) {
			$filter .= " and er.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		$resp_er = $this->queryResult("
			with erf as (
				select
					er.EvnRecept_id,
					null as SPOULOPerson_SPOULOId,
					null as SPOULOPrivilege_SPOULOId,
					l.Lpu_Ouz,
					l.Lpu_OGRN
				from
					v_EvnRecept er (nolock)
					inner join v_Lpu l (nolock) on l.Lpu_id = er.Lpu_id
					left join r50.v_SPOULORecept spr (nolock) on er.EvnRecept_id = spr.EvnRecept_id
				where
					er.EvnRecept_IsPrinted = 2
					and	spr.SPOULORecept_id is null -- новый
					and er.ReceptFinance_id = 1
					
				union all
				
				select
					er.EvnRecept_id,
					spp.SPOULOPerson_SPOULOId,
					sppr.SPOULOPrivilege_SPOULOId,
					l.Lpu_Ouz,
					l.Lpu_OGRN
				from
					v_EvnRecept er (nolock)
					inner join v_Lpu l (nolock) on l.Lpu_id = er.Lpu_id
					inner join r50.v_SPOULOPerson spp (nolock) on er.Person_id = spp.Person_id
					inner join r50.v_SPOULOPrivilege sppr (nolock) on sppr.PersonPrivilege_id = er.PersonPrivilege_id
					left join r50.v_SPOULORecept spr (nolock) on er.EvnRecept_id = spr.EvnRecept_id
				where
					er.EvnRecept_IsPrinted = 2
					and	spr.SPOULORecept_id is null -- новый
			)
			
			select
				erf.EvnRecept_id,
				erf.SPOULOPerson_SPOULOId,
				erf.SPOULOPrivilege_SPOULOId,
				er.EvnRecept_Ser,
				er.EvnRecept_Num,
				rvlm.ReceptValidMsk_id,
				convert(varchar(10), er.EvnRecept_setDate, 120) as EvnRecept_setDate,
				fsl.FundingSource_id,
				fsl2.FundingSource_id as FundingSource_rid,
				er.ReceptDiscount_id,
				er.ReceptType_id,
				pac.PersonAmbulatCard_Num,
				ps.Person_Phone,
				ISNULL(ua.KLRgn_id, pa.KLRgn_id) as KLRgn_id,
				case
					when ukas.KLAreaStat_id is not null then ua.Address_Address
					when pkas.KLAreaStat_id is not null then pa.Address_Address
				end as Address_Address,
				ISNULL(lpd.LpuPeriodDLO_Code, erf.Lpu_Ouz) as LpuPeriodDLO_Code,
				erf.Lpu_OGRN,
				dn.DrugNomen_Code,
				er.EvnRecept_IsMnn,
				er.EvnRecept_Kolvo,
				er.EvnRecept_Signa,
				msf.MedPersonalDLOPeriod_PCOD,
				msf.MedPersonalDLOPeriod_MCOD,
				msf.Person_SurName as MPerson_SurName,
				msf.Person_FirName as MPerson_FirName,
				msf.Person_SecName as MPerson_SecName,
				convert(varchar(10), er.EvnRecept_VKProtocolDT, 120) as EvnRecept_VKProtocolDT,
				er.EvnRecept_VKProtocolNum,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				convert(varchar(10), ps.Person_BirthDay, 120) as Person_BirthDay,
				pt.PrivilegeType_Code,
				ps.Person_Snils,
			    case when ps.PolisType_id = 4 then '' else ps.Polis_Ser end as Polis_Ser,
				ISNULL(ps.Person_EdNum, ps.Polis_Num) as Polis_Num,
			    pt.ReceptFinance_id,
			    ps.Sex_id,
			    d.Diag_Code,
				dp.DocumentPrivilege_Ser,
				dp.DocumentPrivilege_Num,
				convert(varchar(10), dp.DocumentPrivilege_begDate, 120) as DocumentPrivilege_begDate,
				convert(varchar(10), pp.PersonPrivilege_begDate, 120) as PersonPrivilege_begDate,
				convert(varchar(10), pp.PersonPrivilege_endDate, 120) as PersonPrivilege_endDate
			from
				erf
				inner join v_EvnRecept er (nolock) on er.EvnRecept_id = erf.EvnRecept_id
				inner join v_PersonState ps (nolock) on ps.Person_id = er.Person_id
				left join v_PersonPrivilege pp (nolock) on pp.PersonPrivilege_id = er.PersonPrivilege_id
				left join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
				left join r50.v_ReceptValidLinkMsk rvlm (nolock) on rvlm.ReceptValid_id = er.ReceptValid_id
				left join r50.v_FundingSourceLink fsl (nolock) on fsl.DrugFinance_id = er.DrugFinance_id
				left join r50.v_FundingSourceLink fsl2 (nolock) on fsl2.WhsdocumentCostItemType_id = er.WhsdocumentCostItemType_id
				left join v_PersonAmbulatCard pac (nolock) on pac.PersonAmbulatCard_id = er.PersonAmbulatCard_id
				left join v_Address ua (nolock) on ua.Address_id = ps.UAddress_id
				left join v_Address pa (nolock) on pa.Address_id = ps.PAddress_id
				left join v_KLAreaStat ukas (nolock) on (
					(ua.KLCountry_id = ukas.KLCountry_id or ukas.KLCountry_id is null) and
                    (ua.KLRGN_id = ukas.KLRGN_id or ukas.KLRGN_id is null) and
                    (ua.KLSubRGN_id = ukas.KLSubRGN_id or ukas.KLSubRGN_id is null) and
                    (ua.KLCity_id = ukas.KLCity_id or ukas.KLCity_id is null) and
                    (ua.KLTown_id = ukas.KLTown_id or ukas.KLTown_id is null)
				)
				left join v_KLAreaStat pkas (nolock) on (
					(pa.KLCountry_id = pkas.KLCountry_id or pkas.KLCountry_id is null) and
                    (pa.KLRGN_id = pkas.KLRGN_id or pkas.KLRGN_id is null) and
                    (pa.KLSubRGN_id = pkas.KLSubRGN_id or pkas.KLSubRGN_id is null) and
                    (pa.KLCity_id = pkas.KLCity_id or pkas.KLCity_id is null) and
                    (pa.KLTown_id = pkas.KLTown_id or pkas.KLTown_id is null)
				)
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = er.LpuSection_id
				left join v_Diag d (nolock) on d.Diag_id = er.Diag_id
				outer apply (
					select top 1
						dp.DocumentPrivilege_Ser,
						dp.DocumentPrivilege_Num,
						dp.DocumentPrivilege_begDate
					from
						v_DocumentPrivilege dp (nolock)
					where
						dp.PersonPrivilege_id = er.PersonPrivilege_id
				) dp
				outer apply (
					select top 1
						lpd.LpuPeriodDLO_Code
					from
						v_LpuPeriodDLO lpd (nolock)
					where
						ls.LpuUnit_id = lpd.LpuUnit_id
						and ISNULL(LpuPeriodDLO_begDate, er.EvnRecept_setDate) <= er.EvnRecept_setDate
						and ISNULL(LpuPeriodDLO_endDate, er.EvnRecept_setDate) >= er.EvnRecept_setDate
				) lpd
				outer apply (
					select top 1
						dn.DrugNomen_Code
					from
						v_EvnRecept er2 (nolock)
						left join rls.v_Drug d with (nolock) on d.DrugComplexMnn_id = er2.DrugComplexMnn_id
						inner join rls.v_DrugNomen dn with (nolock) on dn.Drug_id = ISNULL(er2.Drug_rlsid, d.Drug_id)
						inner join r50.SPOULODrug sud with (nolock) on
							sud.NOMK_LS = dn.DrugNomen_Code
							and ISNULL(sud.SPOULODrug_begDT, er.EvnRecept_setDate) <= er.EvnRecept_setDate 
							and ISNULL(sud.SPOULODrug_endDT, er.EvnRecept_setDate) >= er.EvnRecept_setDate
							and (er.ReceptDiscount_id <> 1 or sale100 = 1)
							and (er.DrugFinance_id <> 3 or sud.fed = 1) 
							and (er.DrugFinance_id <> 27 or sud.reg = 1) 
					where
						er2.EvnRecept_id = er.EvnRecept_id
				) dn
				outer apply (
					select top 1
						mpdp.MedPersonalDLOPeriod_PCOD,
						mpdp.MedPersonalDLOPeriod_MCOD,
						msf.Person_SurName,
						msf.Person_FirName,
						msf.Person_SecName
					from v_MedStaffFact msf with (nolock)
						inner join r50.v_MedstaffFactDLOPeriodLink msfdpl (nolock) on msfdpl.MedStaffFact_id = msf.MedStaffFact_id
						inner join r50.v_MedPersonalDLOPeriod mpdp (nolock) on mpdp.MedPersonalDLOPeriod_id = msfdpl.MedPersonalDLOPeriod_id
					where msf.MedPersonal_id = ER.MedPersonal_id
						and msf.LpuSection_id = ER.LpuSection_id
						and ISNULL(msf.WorkData_begDate, ER.EvnRecept_setDate) <= ER.EvnRecept_setDate
						and ISNULL(msf.WorkData_endDate, ER.EvnRecept_setDate) >= ER.EvnRecept_setDate
						and ISNULL(msf.WorkData_dlobegDate, ER.EvnRecept_setDate) <= ER.EvnRecept_setDate
						and ISNULL(msf.WorkData_dloendDate, ER.EvnRecept_setDate) >= ER.EvnRecept_setDate
					order by MedPersonal_Code desc
				) MSF
			where
				(1=1)
				{$filter}
		", $queryParams);

		foreach($resp_er as $one_er) {
			$log->add(true, [
				'Отправка данных рецепта',
				'ФИО: '. $one_er['Person_SurName'] . ' ' . $one_er['Person_FirName'] . ' ' . $one_er['Person_SecName'],
				'ДР: '. $one_er['Person_BirthDay'],
				'СНИЛС: '. $one_er['Person_Snils'],
				'Код льготы: '. $one_er['PrivilegeType_Code'],
				'Серия рецепта: '. $one_er['EvnRecept_Ser'],
				'Номер рецепта: '. $one_er['EvnRecept_Num'],
			]);
			if (empty($one_er['DrugNomen_Code'])) {
				$log->add(false, 'Не найден код ЛП (DrugNomen_Code) для рецепта EvnRecept_id = ' . $one_er['EvnRecept_id']);
				continue;
			}

			if (!empty($one_er['Person_Phone'])) {
				$one_er['Person_Phone'] = '+7' . $one_er['Person_Phone'];
			}

			if ($one_er['ReceptFinance_id'] == 1) {
				$params = [
					'RecipeSeries' => $one_er['EvnRecept_Ser'],
					'RecipeNumber' => $one_er['EvnRecept_Num'],
					'PatientName' => $one_er['Person_FirName'],
					'PatientSurname' => $one_er['Person_SurName'],
					'PatientPatronymic' => $one_er['Person_SecName'],
					'PatientBirthDay' => $one_er['Person_BirthDay'],
					'PatientSNILS' => $one_er['Person_Snils'],
					'PatientPhone' => $one_er['Person_Phone'],
					'SexPrefix' => $one_er['Sex_id'],
					'MKBCode' => $one_er['Diag_Code'],
					'MedicalCardNumber' => $one_er['PersonAmbulatCard_Num'],
					'DurationId' => $one_er['ReceptValidMsk_id'],
					'Ordered' => $one_er['EvnRecept_setDate'],
					'FundingSourceId' => $one_er['FundingSource_id'],
					'RegistryTypeId' => $one_er['FundingSource_rid'],
					'PaymentTypeId' => $one_er['ReceptDiscount_id'],
					'OnHome' => $one_er['ReceptType_id'] == 1,
					'PrivilegeDocumentNumber' => $one_er['DocumentPrivilege_Ser'] . ' ' . $one_er['DocumentPrivilege_Num'],
					'PrivilegeDocumentDate' => $one_er['DocumentPrivilege_begDate'],
					'Clinic' => [
						'Code' => str_pad($one_er['MedPersonalDLOPeriod_MCOD'], 4, '0', STR_PAD_LEFT),
						'OGRN' => $one_er['Lpu_OGRN']
					],
					'Medicament' => [
						'Id' => $one_er['DrugNomen_Code'],
						'OrderTypeId' => $one_er['EvnRecept_IsMnn'] == 2 ? 2 : 1,
						'Quantity' => $one_er['EvnRecept_Kolvo'],
						'UsageWay' => $one_er['EvnRecept_Signa']
					],
					'Doctor' => [
						'Code' => $one_er['MedPersonalDLOPeriod_PCOD'],
						'Surname' => $one_er['MPerson_SurName'],
						'Name' => $one_er['MPerson_FirName'],
						'Patronymic' => $one_er['MPerson_SecName']
					],
					'PrivilegeCategoryId' => $one_er['PrivilegeType_Code'],
					'PrivilegeDateBegin' => $one_er['PersonPrivilege_begDate'],
					'PrivilegeDateEnd' => $one_er['PersonPrivilege_endDate'],
					'Polis' => [
						'Number' => $one_er['Polis_Num'],
						'Series' => $one_er['Polis_Ser']
					],
					'PatientAddress' => $one_er['Address_Address']
				];
				$method = 'Recipe/PrivilegedRecipe';
				$methodType = 'PUT';

				if (!empty($one_er['EvnRecept_VKProtocolDT']) || !empty($one_er['EvnRecept_VKProtocolNum'])) {
					$params['DoctorComission'] = [
						'Date' => $one_er['EvnRecept_VKProtocolDT'],
						'Number' => $one_er['EvnRecept_VKProtocolNum']
					];
				}
			} else {
				$params = [
					'Serial' => $one_er['EvnRecept_Ser'],
					'RecipeNumber' => $one_er['EvnRecept_Num'],
					'DurationId' => $one_er['ReceptValidMsk_id'],
					'OrderDate' => $one_er['EvnRecept_setDate'],
					'FundingSourceId' => $one_er['FundingSource_id'],
					'PaymentTypeId' => $one_er['ReceptDiscount_id'],
					'OnHome' => $one_er['ReceptType_id'] == 1,
					'Patient' => [
						'MedicalCardNumber' => $one_er['PersonAmbulatCard_Num'],
						'Phone' => str_replace(['(', ')', '-', ' '], '', $one_er['Person_Phone']),
						'Id' => $one_er['SPOULOPerson_SPOULOId'],
						'PrivilegeId' => $one_er['SPOULOPrivilege_SPOULOId'],
						'IsResident' => $one_er['KLRgn_id'] != 50 ? 1 : 0
					],
					'Clinic' => [
						'Code' => str_pad($one_er['MedPersonalDLOPeriod_MCOD'], 4, '0', STR_PAD_LEFT),
						'OGRN' => $one_er['Lpu_OGRN']
					],
					'Medicament' => [
						'Id' => $one_er['DrugNomen_Code'],
						'OrderTypeId' => $one_er['EvnRecept_IsMnn'] == 2 ? 2 : 1,
						'Quantity' => $one_er['EvnRecept_Kolvo'],
						'UsageWay' => $one_er['EvnRecept_Signa']
					],
					'Doctor' => [
						'Code' => $one_er['MedPersonalDLOPeriod_PCOD'],
						'Surname' => $one_er['MPerson_SurName'],
						'Name' => $one_er['MPerson_FirName'],
						'Patronymic' => $one_er['MPerson_SecName']
					]
				];
				$method = 'Recipe/SaveRecipe';
				$methodType = 'PUT';

				if (!empty($one_er['EvnRecept_VKProtocolDT']) || !empty($one_er['EvnRecept_VKProtocolNum'])) {
					$params['DoctorComission'] = [
						'Date' => $one_er['EvnRecept_VKProtocolDT'],
						'Number' => $one_er['EvnRecept_VKProtocolNum']
					];
				}
			}
			$response = $this->exec($methodType, $method, $params, $log, $one_er['EvnRecept_id']);
			if (!empty($response)) {
				$log->add(false, 'Не удалось отправить рецепт: ' . var_export($response, true));
			} else {
				// сохраняем рецепт в SPOULORecept
				$this->saveSPOULORecept(array(
					'EvnRecept_id' => $one_er['EvnRecept_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		// 7. Передача данных об  удалении льготных рецептов
		$filter = "";
		$queryParams = [];
		if (!empty($data['Person_id'])) {
			$filter .= " and e.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		$resp_er = $this->queryResult("
			select
				er.EvnRecept_id,
				er.EvnRecept_Num,
				rrct.ReceptRemoveCauseType_Code,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				convert(varchar(10), ps.Person_BirthDay, 120) as Person_BirthDay,
				pt.PrivilegeType_Code,
				ps.Person_Snils,
				er.EvnRecept_Ser,
				er.EvnRecept_Num
			from
				EvnRecept er (nolock)
				inner join Evn e (nolock) on e.Evn_id = er.Evn_id
				inner join r50.v_SPOULOPerson spp (nolock) on e.Person_id = spp.Person_id
				inner join r50.v_SPOULORecept spr (nolock) on er.EvnRecept_id = spr.EvnRecept_id
				inner join v_PersonState ps (nolock) on ps.Person_id = e.Person_id
				left join v_PersonPrivilege pp (nolock) on pp.PersonPrivilege_id = er.PersonPrivilege_id
				left join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
				left join v_ReceptRemoveCauseType rrct (nolocK) on rrct.ReceptRemoveCauseType_id = er.ReceptRemoveCauseType_id
			where
				e.Evn_deleted = 2
				and spp.SPOULOPerson_SPOULOId is not null
				and spr.SPOULORecept_id is not null
				and e.Evn_delDT > spr.SPOULORecept_updDT -- удален позже последней отправки
				{$filter}
		", $queryParams);

		foreach($resp_er as $one_er) {
			$log->add(true, [
				'Отправка данных об удалении рецепта',
				'ФИО: '. $one_er['Person_SurName'] . ' ' . $one_er['Person_FirName'] . ' ' . $one_er['Person_SecName'],
				'ДР: '. $one_er['Person_BirthDay'],
				'СНИЛС: '. $one_er['Person_Snils'],
				'Код льготы: '. $one_er['PrivilegeType_Code'],
				'Серия рецепта: '. $one_er['EvnRecept_Ser'],
				'Номер рецепта: '. $one_er['EvnRecept_Num'],
			]);
			$params = [
				'RecipeNumber' => $one_er['EvnRecept_Num'],
				'DeleteReasonId' => $one_er['ReceptRemoveCauseType_Code'] ?? 3
			];
			$method = 'Recipe/CancelRecipe';
			$methodType = 'POST';
			$response = $this->exec($methodType, $method, $params, $log, $one_er['EvnRecept_id']);
			if (!empty($response)) {
				$log->add(false, 'Не удалось отправить данные об удалении рецепта: ' . var_export($response, true));
			} else {
				// сохраняем рецепт в SPOULORecept
				$this->saveSPOULORecept(array(
					'EvnRecept_id' => $one_er['EvnRecept_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}
	}

	/**
	 * Передача данных о пациентах
	 */
	function syncPerson($data, $log) {
		// 1. Идентификация персон по реестру пациентов СПО УЛО
		// 2. Передача данных о новых пациентах
		// 3. Передача данных об изменении пациентов
		$filter = "";
		$queryParams = [];
		if (!empty($data['Person_id'])) {
			$filter .= " and ps.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		$resp_ps = $this->queryResult("
			declare @curDate date = dbo.tzGetDate();
			
			select
				ps.Person_id,
				spp.SPOULOPerson_SPOULOId,
				case when ps.PolisType_id = 4 then '' else ps.Polis_Ser end as Polis_Ser,
				ISNULL(ps.Person_EdNum, ps.Polis_Num) as Polis_Num,
				dtm.DocumentType_Code as DocumentTypeMsk_Code,
				d.Document_Ser,
				d.Document_Num,
				od.Org_Name,
				convert(varchar(10), d.Document_begDate, 120) as Document_begDate,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				ps.Person_Snils,
				convert(varchar(10), ps.Person_BirthDay, 120) as Person_BirthDay,
				ps.Sex_id,
				ua.Address_Address,
				ua.KLRgn_id,
				ISNULL(l.Lpu_Ouz, lpd.LpuPeriodDLO_Code) as Lpu_Ouz,
				l.Lpu_OGRN,
				a.Area_Code
			from
				v_PersonState ps (nolock)
				outer apply (
					select top 1 L.*
					from v_PersonCard PC with(nolock)
						inner join v_Lpu L with(nolock) on L.Lpu_id = PC.Lpu_id
					where 
						PC.Person_id = ps.Person_id and 
						PC.LpuAttachType_id in (1,4)
					order by 
						PC.LpuAttachType_id
				) l
				inner join v_Address ua (nolock) on ua.Address_id = coalesce(ps.UAddress_id, ps.PAddress_id)
				inner join r50.v_AreaLink al (nolock) on al.KLArea_id = ISNULL(ua.KLCity_id, ua.KLSubRgn_id)
				inner join r50.v_Area a (nolock) on a.Area_id = al.Area_id
				left join v_Document d (nolock) on d.Document_id = ps.Document_id
				left join v_OrgDep od (nolock) on od.OrgDep_id = d.OrgDep_id
				left join r50.v_SPOULOPerson spp (nolock) on ps.Person_id = spp.Person_id
				left join r50.v_DocumentTypeLinkMsk dtlm (nolock) on dtlm.DocumentType_id = d.DocumentType_id
				left join r50.v_DocumentTypeMsk dtm (nolock) on dtm.DocumentType_id = dtlm.DocumentTypeMsk_id
				outer apply (
					select top 1
						lpd.LpuPeriodDLO_Code
					from
						v_LpuPeriodDLO lpd (nolock)
						inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = lpd.LpuUnit_id
					where
						lu.Lpu_id = ps.Lpu_id
						and ISNULL(LpuPeriodDLO_begDate, @curDate) <= @curDate
						and ISNULL(LpuPeriodDLO_endDate, @curDate) >= @curDate
				) lpd
			where
				l.Lpu_id is not null and
				exists (
					select top 1
						pp.PersonPrivilege_id
					from
						v_PersonPrivilege pp (nolock)
						inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id 
					where
						pp.Person_id = ps.Person_id
				)
				and (
					spp.SPOULOPerson_SPOULOId is null -- новый
					or ps.PersonState_updDT > spp.SPOULOPerson_updDT -- изменен
				)
				{$filter}
		", $queryParams);

		foreach($resp_ps as $one_ps) {
			$log->add(true, [
				'Отправка данных пациента',
				'ФИО: '. $one_ps['Person_SurName'] . ' ' . $one_ps['Person_FirName'] . ' ' . $one_ps['Person_SecName'],
				'ДР: '. $one_ps['Person_BirthDay'],
				'СНИЛС: '. $one_ps['Person_Snils'],
				'Полис: '. $one_ps['Polis_Ser'] . ' ' . $one_ps['Polis_Num']
			]);
			if (empty($one_ps['DocumentTypeMsk_Code'])) {
				$log->add(false, 'Не найден тип ДУДЛ (DocumentTypeMsk_Code) для пациента Person_id = ' . $one_ps['Person_id']);
				continue;
			}
			if (empty($one_ps['Org_Name'])) {
				$log->add(false, 'Не найдены данные по организации, выдавшей ДУДЛ (Org_Name) для пациента Person_id = ' . $one_ps['Person_id']);
				continue;
			}
			if (empty($one_ps['Document_begDate'])) {
				$log->add(false, 'Не найдены данные о дате выдачи ДУДЛ (Document_begDate) для пациента Person_id = ' . $one_ps['Person_id']);
				continue;
			}
			if (!empty($one_ps['Document_Ser']) && $one_ps['DocumentTypeMsk_Code'] == 14) {
				// верный формат серии паспорта - "98 56"
				$one_ps['Document_Ser'] = mb_substr($one_ps['Document_Ser'], 0, 2) . ' ' . mb_substr($one_ps['Document_Ser'], 2, 2);
			}
			$params = [
				'Polis' => [
					'Serial' => $one_ps['Polis_Ser'],
					'Number' => $one_ps['Polis_Num']
				],
				'IdentityDocument' => [
					'TypeId' => $one_ps['DocumentTypeMsk_Code'],
					'Serial' => $one_ps['Document_Ser'],
					'Number' => $one_ps['Document_Num'],
					'Issuer' => mb_substr($one_ps['Org_Name'], 0, 80),
					'IssueDate' => $one_ps['Document_begDate']
				],
				'Surname' => $one_ps['Person_SurName'],
				'Name' => $one_ps['Person_FirName'],
				'Patronymic' => !empty($one_ps['Person_SecName']) ? $one_ps['Person_SecName'] : '-',
				'SNILS' => $one_ps['Person_Snils'],
				'Birthday' => $one_ps['Person_BirthDay'],
				'Sex' => $one_ps['Sex_id'] == 2 ? 2 : 1,
				'Address' => $one_ps['Address_Address'],
				'Resident' => $one_ps['KLRgn_id'] != 50 ? 1 : 0,
				'Clinic' => [
					'Code' => str_pad($one_ps['Lpu_Ouz'], 4, '0', STR_PAD_LEFT),
					'OGRN' => $one_ps['Lpu_OGRN']
				],
				'AreaId' => $one_ps['Area_Code']
			];
			$method = 'Patient/AddPatient';
			$methodType = 'POST';
			if (empty($one_ps['SPOULOPerson_SPOULOId'])) {
				$response = $this->exec('GET', 'Patient/GetPatientId', [
					'Surname' => $one_ps['Person_SurName'],
					'Name' => $one_ps['Person_FirName'],
					'Patronymic' => $one_ps['Person_SecName'],
					'Birthday' => $one_ps['Person_BirthDay'],
					'SNILS' => $one_ps['Person_Snils']
				], $log);
				if (!empty($response['PatientId'])) {
					$params['PatientId'] = $response['PatientId'];
					$method = 'Patient/UpdatePatient';
					$methodType = 'PUT';
				}

				$response = $this->exec($methodType, $method, $params, $log, $one_ps['Person_id']);

				if ( ($methodType == "POST") && empty($response['PatientId']) ) {
					$log->add(false, 'Не удалось отправить пациента: ' . var_export($response, true));
				}
				else {
					// сохраняем PatientId в SPOULOPerson
					$this->saveSPOULOPerson(array(
						'Person_id' => $one_ps['Person_id'],
						'SPOULOPerson_SPOULOId' => empty($params['PatientId']) ? $response['PatientId'] : $params['PatientId'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}

		// 9. Передача данных об удалении пациентов
		$filter = "";
		$queryParams = [];
		if (!empty($data['Person_id'])) {
			$filter .= " and p.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		$resp_ps = $this->queryResult("
			select
				p.Person_id,
				spp.SPOULOPerson_SPOULOId,
				ps.PersonSurName_SurName,
				ps.PersonFirName_FirName,
				ps.PersonSecName_SecName,
				ps.PersonSnils_Snils,
				convert(varchar(10), ps.PersonBirthDay_BirthDay, 120) as Person_BirthDay,
				case when ps.PolisType_id = 4 then '' else ps.Polis_Ser end as Polis_Ser,
				ISNULL(ps.PersonPolisEdNum_EdNum, ps.Polis_Num) as Polis_Num
			from
				Person p (nolock)
				inner join PersonState ps (nolock) on ps.Person_id = p.Person_id
				inner join r50.v_SPOULOPerson spp (nolock) on p.Person_id = spp.Person_id
			where
				p.Person_deleted = 2
				and spp.SPOULOPerson_SPOULOId is not null
				and p.Person_delDT > spp.SPOULOPerson_updDT -- удален позже последней отправки
				{$filter}
		", $queryParams);

		foreach($resp_ps as $one_ps) {
			$log->add(true, [
				'Отправка данных об удалении пациента',
				'ФИО: '. $one_ps['Person_SurName'] . ' ' . $one_ps['Person_FirName'] . ' ' . $one_ps['Person_SecName'],
				'ДР: '. $one_ps['Person_BirthDay'],
				'СНИЛС: '. $one_ps['Person_Snils'],
				'Полис: '. $one_ps['Polis_Ser'] . ' ' . $one_ps['Polis_Num']
			]);
			$params = [
				'PatientId' => $one_ps['SPOULOPerson_SPOULOId'],
				'DeleteReasonId' => 1
			];
			$method = 'Patient/CancelPatient';
			$methodType = 'POST';
			$response = $this->exec($methodType, $method, $params, $log, $one_ps['Person_id']);
			if (empty($response['PatientId'])) {
				$log->add(false, 'Не удалось отправить данные об удалении пациента: ' . var_export($response, true));
			} else {
				// сохраняем PatientId в SPOULOPerson
				$this->saveSPOULOPerson(array(
					'Person_id' => $one_ps['Person_id'],
					'SPOULOPerson_SPOULOId' => $response['PatientId'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}
	}

	/**
	 * Сохранение идентификатора пациента
	 * @param $data
	 */
	function saveSPOULOPerson($data) {
		$resp = $this->queryResult("
			select top 1
				SPOULOPerson_id
			from
				r50.v_SPOULOPerson (nolock)
			where
				Person_id = :Person_id
		", [
			'Person_id' => $data['Person_id']
		]);

		$proc = 'p_SPOULOPerson_ins';
		$queryParams = [
			'SPOULOPerson_id' => null,
			'Person_id' => $data['Person_id'],
			'SPOULOPerson_SPOULOId' => $data['SPOULOPerson_SPOULOId'],
			'pmUser_id' => $data['pmUser_id']
		];
		if (!empty($resp[0]['SPOULOPerson_id'])) {
			$proc = 'p_SPOULOPerson_upd';
			$queryParams['SPOULOPerson_id'] = $resp[0]['SPOULOPerson_id'];
		}

		return $this->queryResult("
			declare
				@SPOULOPerson_id bigint = :SPOULOPerson_id,
				@ErrCode int,
				@ErrMessage varchar;
				
			exec r50.{$proc}
				@SPOULOPerson_id = @SPOULOPerson_id output,
				@Person_id = :Person_id,
				@SPOULOPerson_SPOULOId = :SPOULOPerson_SPOULOId,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $queryParams);
	}

	/**
	 * Сохранение идентификатора рецепта
	 * @param $data
	 */
	function saveSPOULORecept($data) {
		$resp = $this->queryResult("
			select top 1
				SPOULORecept_id
			from
				r50.v_SPOULORecept (nolock)
			where
				EvnRecept_id = :EvnRecept_id
		", [
			'EvnRecept_id' => $data['EvnRecept_id']
		]);

		$proc = 'p_SPOULORecept_ins';
		$queryParams = [
			'SPOULORecept_id' => null,
			'EvnRecept_id' => $data['EvnRecept_id'],
			'pmUser_id' => $data['pmUser_id']
		];
		if (!empty($resp[0]['SPOULORecept_id'])) {
			$proc = 'p_SPOULORecept_upd';
			$queryParams['SPOULORecept_id'] = $resp[0]['SPOULORecept_id'];
		}

		return $this->queryResult("
			declare
				@SPOULORecept_id bigint = :SPOULORecept_id,
				@ErrCode int,
				@ErrMessage varchar;
				
			exec r50.{$proc}
				@SPOULORecept_id = @SPOULORecept_id output,
				@EvnRecept_id = :EvnRecept_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $queryParams);
	}

	/**
	 * Сохранение идентификатора льготы
	 * @param $data
	 */
	function saveSPOULOPrivilege($data) {
		$resp = $this->queryResult("
			select top 1
				SPOULOPrivilege_id
			from
				r50.v_SPOULOPrivilege (nolock)
			where
				PersonPrivilege_id = :PersonPrivilege_id
		", [
			'PersonPrivilege_id' => $data['PersonPrivilege_id']
		]);

		$proc = 'p_SPOULOPrivilege_ins';
		$queryParams = [
			'SPOULOPrivilege_id' => null,
			'PersonPrivilege_id' => $data['PersonPrivilege_id'],
			'SPOULOPrivilege_SPOULOId' => $data['SPOULOPrivilege_SPOULOId'],
			'pmUser_id' => $data['pmUser_id']
		];
		if (!empty($resp[0]['SPOULOPrivilege_id'])) {
			$proc = 'p_SPOULOPrivilege_upd';
			$queryParams['SPOULOPrivilege_id'] = $resp[0]['SPOULOPrivilege_id'];
		}

		return $this->queryResult("
			declare
				@SPOULOPrivilege_id bigint = :SPOULOPrivilege_id,
				@ErrCode int,
				@ErrMessage varchar;
				
			exec r50.{$proc}
				@SPOULOPrivilege_id = @SPOULOPrivilege_id output,
				@PersonPrivilege_id = :PersonPrivilege_id,
				@SPOULOPrivilege_SPOULOId = :SPOULOPrivilege_SPOULOId,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $queryParams);
	}
}
