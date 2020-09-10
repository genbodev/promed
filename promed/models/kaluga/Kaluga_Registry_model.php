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
require_once(APPPATH.'models/Registry_model.php');

class Kaluga_Registry_model extends Registry_model {
	var $scheme = "r40";
	var $region = "kaluga";
	var $Registry_EvnNum = null;

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Получение дополнительных полей для сохранения реестра
	 */
	function getSaveRegistryAdditionalFields() {
		return "
			@DispClass_id = :DispClass_id,
		";
	}

	/**
	 *	Список случаев по пациентам без документов ОМС
	 */
	function loadRegistryNoPolis($data)
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
		if ($data['Registry_id'] == 6) {
			$evn_join = "";
			$set_date_time = " null as Evn_setDT";
		} else {
			$evn_join = " left join v_Evn Evn with(nolock) on Evn.Evn_id = RNP.Evn_id";
			$set_date_time = " convert(varchar(10), Evn.Evn_setDT, 104)+' '+convert(varchar(5), Evn.Evn_setDT, 108) as Evn_setDT";
		}

		$filters = "";
		if (!empty($data['Person_OrgSmo'])) {
			$params['Person_OrgSmo'] = $data['Person_OrgSmo'];
			$filters .= " and IsNull(OrgSMO.Orgsmo_f002smocod,'') + ' ' + IsNull(OrgSMO.OrgSMO_Nick,'') like '%'+:Person_OrgSmo+'%'";
		}

		if (!empty($data['Person_Polis'])) {
			$params['Person_Polis'] = $data['Person_Polis'];
			$filters .= " and pol.Polis_Num like '%'+:Person_Polis+'%'";
		}

		$query = "
		Select
			RNP.Registry_id,
			RNP.Evn_id,
			RNP.Evn_rid as Evn_rid,
			RNP.Person_id,
			RNP.Server_id,
			RNP.PersonEvn_id,
			rtrim(IsNull(RNP.Person_SurName,'')) + ' ' + rtrim(IsNull(RNP.Person_FirName,'')) + ' ' + rtrim(isnull(RNP.Person_SecName, '')) as Person_FIO,
			RTrim(IsNull(convert(varchar,cast(RNP.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
			rtrim(LpuSection.LpuSection_Code) + '. ' + LpuSection.LpuSection_Name as LpuSection_Name,
			rtrim(IsNull(pol.Polis_Ser, '')) +rtrim(IsNull(' №'+pol.Polis_Num,'')) as Person_Polis,
			IsNull(convert(varchar,cast(pol.Polis_begDate as datetime),104),'...') + ' - ' + IsNull(convert(varchar,cast(pol.Polis_endDate as datetime),104),'...') as Person_PolisDate,
			IsNull(OrgSMO.Orgsmo_f002smocod,'') + ' ' + IsNull(OrgSMO.OrgSMO_Nick,'') as Person_OrgSmo,
			{$set_date_time}
		from {$this->scheme}.v_RegistryNoPolis RNP with (NOLOCK)
			left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = RNP.PersonEvn_id and ps.Server_id = RNP.Server_id
			left join v_Polis pol with (nolock) on pol.Polis_id = ps.Polis_id
			left join v_OrgSmo OrgSmo with (NOLOCK) on OrgSmo.OrgSmo_id = pol.OrgSmo_id
		left join v_LpuSection LpuSection with (NOLOCK) on LpuSection.LpuSection_id = RNP.LpuSection_id
		{$evn_join}
		where
			RNP.Registry_id=:Registry_id
			{$filters}
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
	 * Получение дополнительных полей
	 */
	function getReformErrRegistryAdditionalFields() {
		return ",OrgSMO_id,DispClass_id";
	}

	/**
	 * Получение номера выгружаемого файла реестра в отчетном периоде
	 */
	function SetRegistryFileNum($data) {
		$query = "
			declare
				 @fileNum int
				,@Err_Msg varchar(400);

			set nocount on;

			begin try
				set @fileNum = (
					select max(Registry_FileNum)
					from {$this->scheme}.v_Registry r with (nolock)
					where Lpu_id = :Lpu_id
						and MONTH(Registry_endDate) = :Registry_endMonth
						and YEAR(Registry_endDate) = :Registry_endYear
						and Registry_FileNum is not null
				);

				set @fileNum = ISNULL(@fileNum, 0) + 1;

				update {$this->scheme}.Registry with (rowlock)
				set Registry_FileNum = @fileNum
				where Registry_id = :Registry_id
			end try

			begin catch
				set @Err_Msg = error_message();
				set @fileNum = null;
			end catch

			set nocount off;

			select @fileNum as fileNum, @Err_Msg as Error_Msg;
		";
		$result = $this->db->query($query, $data);

		$fileNum = 0;

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && !empty($response[0]['fileNum']) ) {
				$fileNum = $response[0]['fileNum'];
			}
		}

		return $fileNum;
	}

	/**
	 * Установка признака "Оплачен" для случаев без ошибок
	 */
	function setRegistryPaid($data)
	{
		$registry_list = array();
		$query = "
			select top 1
				RT.RegistryType_SysNick
			from
				{$this->scheme}.v_Registry R with(nolock)
				inner join v_RegistryType RT with(nolock) on RT.RegistryType_id = R.RegistryType_id
			where
				R.Registry_id = :Registry_id
		";
		$RegistryType_SysNick = $this->getFirstResultFromQuery($query, $data);

		if ($RegistryType_SysNick == 'group') {
			$query = "
				select RGL.Registry_id
				from {$this->scheme}.v_RegistryGroupLink RGL with(nolock)
				where RGL.Registry_pid = :Registry_id
			";
			$result = $this->db->query($query, $data);
			$registry_list = $result->result('array');
		} else {
			$registry_list[] = array('Registry_id' => $data['Registry_id']);
		}

		foreach($registry_list as $registry) {
			$params = array(
				'Registry_id' => $registry['Registry_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$query = "
				declare @ErrCode int,
						@ErrMsg varchar(400)
				exec {$this->scheme}.p_Registry_setPaid
					@Registry_id = :Registry_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output
				select @ErrMsg as ErrMsg
			";
			$resp = $this->getFirstRowFromQuery($query, $params);
			if (!$resp || !empty($resp['Error_Msg'])) {
				return false;
			}
		}
		return true;
	}

	/**
	 *	Какая-то проверка
	 */
	function checkErrorDataInRegistry($data)
	{
		$params = array();
		$params['Registry_id'] = $data['Registry_id'];
		$params['Person_id'] = $data['Person_id'];

		$query = "
			select
				rd.Registry_id,
				r.RegistryType_id,
				rd.Evn_id,
				ps.Person_EdNum,
				pol.OrgSMO_id,
				pol.Polis_Ser,
				pol.Polis_Num,
				convert(varchar(8), pol.Polis_begDate, 112) as Polis_begDate,
				convert(varchar(8), pol.Polis_endDate, 112) as Polis_endDate
			from
				{$this->scheme}.v_RegistryGroupLink RGL (nolock)
				inner join {$this->scheme}.v_Registry r (nolock) on r.Registry_id = rgl.Registry_id
				inner join {$this->scheme}.v_RegistryData rd (nolock) on rd.Registry_id = r.Registry_id
				outer apply (
					select top 1
						Polis_id,
						Person_EdNum
					from v_Person_bdz with (nolock)
					where Person_id = rd.Person_id
						and PersonEvn_insDT <= cast(rd.Evn_setDate as date)
					order by PersonEvn_insDT desc
				) ps
				left join v_Polis pol (nolock) on pol.Polis_id = ps.Polis_id
			where
				RGL.Registry_pid = :Registry_id
				and rd.Person_id = :Person_id

			union all

			select
				rd.Registry_id,
				r.RegistryType_id,
				rd.Evn_id,
				ps.Person_EdNum,
				pol.OrgSMO_id,
				pol.Polis_Ser,
				pol.Polis_Num,
				convert(varchar(8), pol.Polis_begDate, 112) as Polis_begDate,
				convert(varchar(8), pol.Polis_endDate, 112) as Polis_endDate
			from
				{$this->scheme}.v_RegistryGroupLink RGL (nolock)
				inner join {$this->scheme}.v_Registry r (nolock) on r.Registry_id = rgl.Registry_id
				inner join {$this->scheme}.v_RegistryDataCmp rd (nolock) on rd.Registry_id = r.Registry_id
				outer apply (
					select top 1
						Polis_id,
						Person_EdNum
					from v_Person_bdz with (nolock)
					where Person_id = rd.Person_id
						and PersonEvn_insDT <= cast(rd.Evn_setDate as date)
					order by PersonEvn_insDT desc
				) ps
				left join v_Polis pol (nolock) on pol.Polis_id = ps.Polis_id
			where
				RGL.Registry_pid = :Registry_id
				and rd.Person_id = :Person_id
		";
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$row = $result->result('array');

			if ( is_array($row) && count($row) > 0 ) {
				return $row; // возвращаем данные о случае
			}
		}

		return false;
	}

	/**
	 *	Идентификация СМО по Orgsmo_f002smocod и QM_OGRN
	 */
	function identifyOrgSMO($data)
	{
		if ( empty($data['QM_OGRN']) || !in_array($data['QM_OGRN'], array('4055', '4058')) ) {
			return false;
		}

		switch ( $data['QM_OGRN'] ) {
			case '4055': $Orgsmo_f002smocod = '40002'; break;
			case '4058': $Orgsmo_f002smocod = '40001'; break;
		}

		$query = "
			select top 1 OrgSMO_id
			from v_OrgSMO (nolock)
			where Orgsmo_f002smocod = :Orgsmo_f002smocod
		";

		$queryParams = array(
			'Orgsmo_f002smocod' => $Orgsmo_f002smocod
		);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result))
		{
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				return $row[0]['OrgSMO_id'];
			}
		}
		return false;
	}

	/**
	 *  Корректировка полисных данных
	 */
	function addNewPolisToPerson($data)
	{
		$added = false;

		// проверяем есть ли у человека такой полис в PersonPolis, если нет добавляем
		$query = "
			select top 1
				PersonPolis_id,
				ISNULL(Polis_Ser, '') as Polis_Ser,
				ISNULL(Polis_Num, '') as Polis_Num
			from
				v_PersonPolis (nolock)
			where
				Person_id = :Person_id
				and OrgSMO_id = :OrgSMO_id
				and Polis_begDate = :Polis_begDate
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 ) {
				// Если серия и номер не совпадают, то обновляем
				if ( 
					($resp[0]['Polis_Ser'] != $data['Polis_Ser'] || $resp[0]['Polis_Num'] != $data['Polis_Num'])
					&& (empty($data['Polis_endDate']) || $data['Polis_endDate'] >= $data['Polis_begDate'])
				) {
					$data['PersonPolis_id'] = $resp[0]['PersonPolis_id'];

					$query = "
						declare
							@ErrCode int,
							@PersonPolis_id bigint,
							@ErrMsg varchar(400);

						set @PersonPolis_id = :PersonPolis_id;

						exec p_PersonPolis_upd
							@PersonPolis_id = @PersonPolis_id output,
							@Server_id = :Server_id,
							@Person_id = :Person_id,
							@OmsSprTerr_id = :OmsSprTerr_id,
							@PolisType_id = :PolisType_id,
							@OrgSMO_id = :OrgSMO_id,
							@Polis_Ser = :Polis_Ser,
							@Polis_Num = :Polis_Num,
							@Polis_begDate = :Polis_begDate,
							@Polis_endDate = :Polis_endDate,
							@PersonPolis_insDT = :Polis_begDate,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output;

						select @PersonPolis_id as PersonPolis_id;
					";
					$result = $this->db->query($query, $data);
					$resp = $result->result('array');

					$added = true;
				}
				else {
					$resp[0]['PersonPolis_id'] = null;
				}
			}
			// Если документа ОМС нет, то добавляем
			else {
				$query = "
					declare @ErrCode int,
						@PersonPolis_id bigint,
						@ErrMsg varchar(400);

					exec p_PersonPolis_ins
						@PersonPolis_id = @PersonPolis_id output,
						@Server_id = :Server_id,
						@Person_id = :Person_id,
						@OmsSprTerr_id = :OmsSprTerr_id,
						@PolisType_id = :PolisType_id,
						@OrgSMO_id = :OrgSMO_id,
						@Polis_Ser = :Polis_Ser,
						@Polis_Num = :Polis_Num,
						@Polis_begDate = :Polis_begDate,
						@Polis_endDate = :Polis_endDate,
						@PersonPolis_insDT = :Polis_begDate,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @PersonPolis_id as PersonPolis_id;
				";
				if(empty($data['Polis_endDate']) || $data['Polis_endDate'] >= $data['Polis_begDate']){
					$result = $this->db->query($query, $data);
					$resp = $result->result('array');

					$added = true;
				}
			}

			// если вставили открытый полис, то все остальные открытые закрываем датой открытия нового минус один день
			if (!empty($resp[0]['PersonPolis_id']) && empty($data['Polis_endDate'])) {
				$query = "
					update
						p with (rowlock)
					set
						p.Polis_endDate = :Polis_endDate
					from
						Polis p
						inner join v_PersonPolis pp (nolock) on pp.Polis_id = p.Polis_id
					where
						pp.Person_id = :Person_id and pp.PersonPolis_id <> :PersonPolis_id and p.Polis_endDate is null
				";

				$this->db->query($query, array(
					'PersonPolis_id' => $resp[0]['PersonPolis_id'],
					'Person_id' => $data['Person_id'],
					'Polis_endDate' => date('Y-m-d', (strtotime($data['Polis_begDate']) - 60*60*24))
				));
			}
		}
		// для единого номера полиса проверяем есть ли у человека такой полис в PersonPolisEdNum, если нет добавляем
		if ($data['PolisType_id'] == 4) {
			$query = "
				select top 1
					PersonPolisEdNum_id
				from
					v_PersonPolisEdNum (nolock)
				where
					Person_id = :Person_id
					and PersonPolisEdNum_EdNum = :Polis_Num
					and PersonPolisEdNum_begDT = :Polis_begDate
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (is_object($result)) {
					if (empty($resp[0]['PersonPolisEdNum_id'])) {
						$query = "
							declare @ErrCode int,
								@PersonPolisEdNum_id bigint,
								@ErrMsg varchar(400);

							exec p_PersonPolisEdNum_ins
								@PersonPolisEdNum_id = @PersonPolisEdNum_id output,
								@Server_id = :Server_id,
								@Person_id = :Person_id,
								@PersonPolisEdNum_EdNum = :Polis_Num,
								@PersonPolisEdNum_begDT = :Polis_begDate,
								@PersonPolisEdNum_insDT = :Polis_begDate,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output;

							select @PersonPolisEdNum_id as PersonPolisEdNum_id;
						";

						$result = $this->db->query($query, $data);
						$added = true;
					}
				}
			}
		}

		// запускаем xp_PersonAllocatePersonEvnByEvn, если что то добавили
		if ($added) {
			$query = "
				declare
					@ErrCode int,
					@ErrMsg varchar(4000);

				exec xp_PersonAllocatePersonEvnByEvn
					@Person_id = :Person_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;

				select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			$this->db->query($query, $data);
		}
	}
	
	/**
	 *	Установка еще какого-то признака
	 */
	function setRegistryDataNoPolis($data)
	{
		if ( $data['RegistryType_id'] == 6 ) {
			$query = "
				Insert {$this->scheme}.RegistryCmpNoPolis (Registry_id, Evn_id, Person_id, Evn_Code, Person_SurName, Person_FirName, Person_SecName, Person_BirthDay, pmUser_insID, pmUser_updID, RegistryNoPolis_insDT, RegistryNoPolis_updDT)
				Select 
				rd.Registry_id, rd.Evn_id, rd.Person_id, '', rd.Person_SurName, rd.Person_FirName, rd.Person_SecName, rd.Person_BirthDay, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryNoPolis_insDT, dbo.tzGetDate() as RegistryNoPolis_updDT 
				from {$this->scheme}.v_RegistryDataCmp rd with (nolock)
				where rd.Registry_id = :Registry_id  and rd.Evn_id = :Evn_id;
			";
		}
		else {
			$query = "
				Insert {$this->scheme}.RegistryNoPolis (Registry_id, Evn_id, Person_id, Evn_Code, Person_SurName, Person_FirName, Person_SecName, Person_BirthDay, pmUser_insID, pmUser_updID, RegistryNoPolis_insDT, RegistryNoPolis_updDT)
				Select 
				rd.Registry_id, rd.Evn_id, rd.Person_id, '', rd.Person_SurName, rd.Person_FirName, rd.Person_SecName, rd.Person_BirthDay, :pmUser_id, :pmUser_id, dbo.tzGetDate() as RegistryNoPolis_insDT, dbo.tzGetDate() as RegistryNoPolis_updDT 
				from {$this->scheme}.v_RegistryData rd with (nolock)
				where rd.Registry_id = :Registry_id  and rd.Evn_id = :Evn_id;
			";
		}
		
		$result = $this->db->query($query, $data);
	}

	/**
	 * Удаление данных в реестре о пациентах без полиса
	 */
	function deleteRegistryNoPolis($data)
	{
		$params = array('Registry_id' => $data['Registry_id']);

		$registry_list = array();
		$query = "
			select top 1
				RT.RegistryType_SysNick,
				RT.RegistryType_id
			from
				{$this->scheme}.v_Registry R with(nolock)
				inner join v_RegistryType RT with(nolock) on RT.RegistryType_id = R.RegistryType_id
			where
				R.Registry_id = :Registry_id
		";
		$RegistryType_SysNick = $this->getFirstResultFromQuery($query, $data);

		if ($RegistryType_SysNick == 'group') {
			$query = "
				select RGL.Registry_id, R.RegistryType_id
				from {$this->scheme}.v_RegistryGroupLink RGL with(nolock)
					inner join {$this->scheme}.v_Registry R with(nolock) on R.Registry_id = RGL.Registry_id
				where RGL.Registry_pid = :Registry_id
			";
			$result = $this->db->query($query, $data);
			$registry_list = $result->result('array');
		} else {
			$registry_list[] = array('Registry_id' => $data['Registry_id'], 'RegistryType_id' => $data['RegistryType_id']);
		}

		foreach($registry_list as $registry) {
			$params = array(
				'Registry_id' => $registry['Registry_id'],
				'pmUser_id' => $data['pmUser_id']
			);

			$object = ($registry['RegistryType_id'] == 6 ? 'RegistryCmpNoPolis' : 'RegistryNoPolis');
			$query = "
				declare @ErrCode int
				declare @ErrMsg varchar(400)

				exec {$this->scheme}.p_{$object}_del
				@Registry_id = :Registry_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

				select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			$resp = $this->getFirstRowFromQuery($query, $params);
			if (!$resp || !empty($resp['Error_Msg'])) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 *	Загрузка данных по реестру
	 */
	function loadRegistryData($data)
	{
		if ($data['Registry_id']==0)
		{
			return false;
		}
		if ($data['RegistryType_id']==0)
		{
			return false;
		}

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$this->setRegistryParamsByType($data);

		$filterAddQueryTemp = null;
		$filterAddQuery = "";
		if(isset($data['Filter']) && in_array($this->region, array('kaluga'))){
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
						elseif($column == 'Evn_ident') {
							$column = 'RD.Evn_id';
							if ($this->RegistryType_id == 1) {
								$column = 'RD.Evn_rid';
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
		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id'=>$data['session']['lpu_id']
		);
		$filter="(1=1)";
		$join = "";
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

		if ($data['RegistryType_id'] == 1) {
			$Evn_ident = "RD.Evn_rid as Evn_ident,";
			if (!empty($data['Evn_id'])) {
				$filter .= " and RD.Evn_rid = :Evn_id";
				$params['Evn_id'] = $data['Evn_id'];
			}
		} else {
			$Evn_ident = "RD.Evn_id as Evn_ident,";
			if (!empty($data['Evn_id'])) {
				$filter .= " and RD.Evn_id = :Evn_id";
				$params['Evn_id'] = $data['Evn_id'];
			}
		}

		if ( !empty($data['filterRecords']) ) {
			if ($data['filterRecords'] == 2) {
				$filter .= " and ISNULL(RD.RegistryData_IsPaid,1) = 2";
			} elseif ($data['filterRecords'] == 3) {
				$filter .= " and ISNULL(RD.RegistryData_IsPaid,1) = 1";
			}
		}

		if ( in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) )
		{
			$fields = '';
			$select_mes = "'' as Mes_Code,";
			if (isset($data['RegistryStatus_id']) && (6==$data['RegistryStatus_id'])) {
                $source_table = 'v_RegistryDeleted_Data';
            } else {
                $source_table = 'v_' . $this->RegistryDataObject;
				$join .= "left join v_MesOld MOLD (nolock) on MOLD.Mes_id=RD.MesItog_id";
				$select_mes = "MOLD.Mes_Code,";
            }
			//УЕТ для поликлиники
			if ($data['RegistryType_id'] == 2) {
				$join .= "
					outer apply (
						select
							count(distinct EvnViz.EvnVizit_id) as VizitCount,
							sum(isnull(case when UslugaComplex.UslugaComplex_Code = 'A.18.30.001' then EvnUsluga.EvnUsluga_Kolvo end,0)) as UslugaCount
						from v_EvnVizit EvnViz with (nolock)
							left join v_EvnUsluga EvnUsluga with (nolock) on EvnUsluga.EvnUsluga_pid=EvnViz.EvnVizit_id
							left join UslugaComplex with (nolock) on UslugaComplex.UslugaComplex_id=EvnUsluga.UslugaComplex_id
						where EvnViz.EvnVizit_pid = RD.Evn_rid and EvnViz.Lpu_id = RD.Lpu_id
					)Cnt
				";

				$fields .= "case
								when ISNULL(EPL.Lpu_CodeSMO, '') = '' then ''
								when EPL.Lpu_CodeSMO = Lpu.Lpu_f003mcod then 'Да'
								when PolkaAttachLpu.Lpu_Nick is not null then PolkaAttachLpu.Lpu_Nick
								when PolkaAttachLpu.Lpu_Nick is null then 'Нет'
							end as attachToMO,
				";

				//Мо прикрепления
				$join .= "
					left join EvnPL EPL with (NOLOCK) on EPL.EvnPL_id = RD.Evn_rid
					outer apply
					(
						Select top 1
							Latt.Lpu_Nick
						from
							v_Lpu Latt with (NOLOCK)
						where
						 	EPL.Lpu_CodeSMO = Latt.Lpu_f003mcod
					) PolkaAttachLpu
				";
			}

			if ($data['RegistryType_id'] == 6) {

				$fields .= "case
								when ISNULL(CCC.Lpu_CodeSMO, '') = '' then ''
								when CCC.Lpu_CodeSMO = Lpu.Lpu_f003mcod then 'Да'
								when CMPAttachLpu.Lpu_Nick is not null then CMPAttachLpu.Lpu_Nick
								when CMPAttachLpu.Lpu_Nick is null then 'Нет'
							end as attachToMO,
				";
				$join .= "
					left join CmpCloseCard CCC with (nolock) on CCC.CmpCloseCard_id = RD.Evn_id
					outer apply
					(
						Select top 1 Lcmp.Lpu_Nick
						from
							v_Lpu Lcmp with (NOLOCK)
						where
							CCC.Lpu_CodeSMO = Lcmp.Lpu_f003mcod
					) CMPAttachLpu
				";
			}

			if (in_array($data['RegistryType_id'], array(2, 6))) {
				$join .= " outer apply ( select top 1 Lpu_f003mcod from v_Lpu where Lpu_id = :Lpu_id) Lpu";
			}

			if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
				$join .= " left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
				$fields .= "epd.DispClass_id, ";
			}

			if ( in_array($data['RegistryType_id'], array(1, 14)) ) {
				$setDateField = 'RegistryData_ReceiptDate';
			}
			else {
				$setDateField = 'Evn_setDate';
			}

            $query = "
				Select
					-- select
					RD.Evn_id,
					{$Evn_ident}
					RD.Evn_rid,
					RD.EvnClass_id,
					RD.Registry_id,
					RD.RegistryType_id,
					RD.Person_id,
					PersonEvn.Server_id,
					PersonEvn.PersonEvn_id,
					case when RDL.Person_id is null then 0 else 1 end as IsRDL,
					RD.needReform, RD.checkReform, RD.timeReform,
					case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit,
					RD.RegistryData_deleted,
					RTrim(RD.NumCard) as EvnPL_NumCard,
					RTrim(RD.Person_FIO) as Person_FIO,
					RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
					CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					RD.LpuSection_id,
					RTrim(RD.LpuSection_name) as LpuSection_name,
					RTrim(RD.MedPersonal_Fio) as MedPersonal_Fio,
					RTrim(IsNull(convert(varchar,cast(RD.{$setDateField} as datetime),104),'')) as EvnVizitPL_setDate,
					RTrim(IsNull(convert(varchar,cast(RD.Evn_disDate as datetime),104),'')) as Evn_disDate,
					RD.RegistryData_Tariff RegistryData_Tariff,
					RD.RegistryData_KdFact as RegistryData_Uet,
					{$fields}
					{$select_mes}
					RD.RegistryData_KdPay as RegistryData_KdPay,
					RD.RegistryData_KdPlan as RegistryData_KdPlan,
					RD.RegistryData_ItogSum as RegistryData_ItogSum,
					RegistryError.Err_Count as Err_Count,
					RD.RegistryData_IsPaid
					-- end select
				from
					-- from
					{$this->scheme}.{$source_table} RD with (NOLOCK)
					left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id
					outer apply (
						select top 1 RDLT.Person_id from RegistryDataLgot RDLT with (NOLOCK) where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
					) RDL
					outer apply
					(
						Select count(*) as Err_Count
						from {$this->scheme}.v_RegistryError RE with (NOLOCK) where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
						union
						Select count(*) as Err_Count
						from {$this->scheme}.v_RegistryErrorTFOMS RET with (NOLOCK) where RD.Evn_id = RET.Evn_id and RD.Registry_id = RET.Registry_id
					) RegistryError
					outer apply
					(
						Select top 1 PersonEvn_id, Server_id
						from v_PersonEvn PE with (NOLOCK)
						where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.{$setDateField})
						order by PersonEvn_insDT desc
					) PersonEvn
					{$join}
				-- end from
				where
					-- where
					RD.Registry_id=:Registry_id
					and
					{$filter}
					{$filterAddQuery}
					-- end where
				order by
					-- order by
					RD.Person_FIO
					-- end order by
			";
		}

		//echo getDebugSQL($query, $params);die;
		/*
		echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		echo getDebugSql(getCountSQLPH($query), $params);
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
	 *	Установка реестра в очередь на формирование
	 *	Возвращает номер в очереди
	 */
	function saveRegistryQueue($data)
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

		$re = $this->loadRegistryQueue($data);
		if (is_array($re) && (count($re) > 0))
		{
			if ($operation=='update')
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
			'OrgSMO_id' => $data['OrgSMO_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'RegistryStacType_id' => $data['RegistryStacType_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'],
			'OrgRSchet_id' => $data['OrgRSchet_id'],
			'Registry_accDate' => $data['Registry_accDate'],
			'DispClass_id' => $data['DispClass_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$fields = "";
		
		$params['KatNasel_id'] = $data['KatNasel_id'];
		$fields .= "@KatNasel_id = :KatNasel_id,";
				
		switch ($data['RegistryType_id'])
		{
			case 1:
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				$fields .= "@LpuBuilding_id = :LpuBuilding_id,";
				break;
			case 2:
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				$fields .= "@LpuBuilding_id = :LpuBuilding_id,";
				// Переформирование по записям, пока только на полке
				if (isset($data['reform']))
				{
					$params['reform'] = $data['reform'];
					$fields .= "@reform = :reform,";
				}
				break;
			default:
				break;
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
				@OrgSMO_id = :OrgSMO_id,
				@OrgRSchet_id = :OrgRSchet_id,
				@Registry_begDate = :Registry_begDate,
				@Registry_endDate = :Registry_endDate,
				@DispClass_id = :DispClass_id,
				{$fields}
				@Registry_Num = :Registry_Num,
				@Registry_accDate = @curdate,
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
	 *	Получение списка ошибок ТФОМС
	 */
	function loadRegistryErrorTFOMS($data)
	{

		$filterAddQueryTemp = null;
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
							$column = "rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))";//'RE.'.$column;
						elseif($column == 'LpuSection_Name')
							$column = 'LS.'.$column;
						elseif($column == 'RegistryErrorType_Code')
							$column = 'ret.'.$column;
						elseif($column == 'Evn_ident') {
							$column = 'Evn.Evn_rid';
							if ($this->RegistryType_id == 1) {
								$column = 'RE.Evn_id';
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

		$this->setRegistryParamsByType($data);
		//$data['RegistryType_id'] = $this->getFirstResultFromQuery("SELECT RegistryType_id FROM {$this->scheme}.v_Registry with (nolock) WHERE Registry_id = :Registry_id", array('Registry_id'=>$data['Registry_id']));

		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$filter="(1=1)";
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

		if ($this->RegistryType_id == 1) {
			$Evn_ident = "Evn.Evn_rid as Evn_ident,";
			if (!empty($data['Evn_id'])) {
				$filter .= " and Evn.Evn_rid = :Evn_id";
				$params['Evn_id'] = $data['Evn_id'];
			}
		} else {
			$Evn_ident = "RE.Evn_id as Evn_ident,";
			if (!empty($data['Evn_id'])) {
				$filter .= " and RE.Evn_id = :Evn_id";
				$params['Evn_id'] = $data['Evn_id'];
			}
		}

		if (!empty($data['RegistryErrorTFOMS_Comment']))
		{
			$filter .= " and RE.RegistryErrorTFOMS_Comment like '%'+:RegistryErrorTFOMS_Comment+'%'";
			$params['RegistryErrorTFOMS_Comment'] = $data['RegistryErrorTFOMS_Comment'];
		}

		$addToSelect = "";
		$leftjoin = "";
		
		if ( in_array($this->RegistryType_id, array(7, 9, 12)) ) {
			$leftjoin .= " left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
			$addToSelect .= ",epd.DispClass_id";
		}

		switch ( $this->RegistryType_id ) {
			case 6:
				$query = "
					Select 
						-- select
						RegistryErrorTFOMS_id,
						RE.Registry_id,
						null as Evn_rid,
						RE.Evn_id,
						RE.Evn_id as Evn_ident,
						null as EvnClass_id,
						ret.RegistryErrorType_Code,
						rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
						ps.Person_id, 
						ps.PersonEvn_id, 
						ps.Server_id, 
						convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
						RE.RegistryErrorTFOMS_FieldName,
						RE.RegistryErrorTFOMS_BaseElement,
						RE.RegistryErrorTFOMS_Comment,
						MP.Person_Fio as MedPersonal_Fio,
						LB.LpuBuilding_Name, 
						LS.LpuSection_Name,
						ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
						case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
						{$addToSelect}
						-- end select
					from 
						-- from
						{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
						left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
						left join v_CmpCloseCard ccc (nolock) on ccc.CmpCloseCard_id = RE.Evn_id
						left join v_LpuSection LS (nolock) on LS.LpuSection_id = ccc.LpuSection_id
						left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
						outer apply(
							select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = ccc.MedPersonal_id
						) as MP
						outer apply (
							select top 1
								PersonEvn_id,
								Server_id,
								Person_BirthDay,
								Polis_id,
								Person_SurName,
								Person_FirName,
								Person_SecName,
								Person_id,
								Person_EdNum
							from v_Person_bdz with (nolock)
							where Person_id = rd.Person_id
								and PersonEvn_insDT <= cast(rd.Evn_setDate as date)
							order by PersonEvn_insDT desc
						) ps
						left join {$this->scheme}.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
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
			break;

			default:
				$query = "
					Select 
						-- select
						RegistryErrorTFOMS_id,
						RE.Registry_id,
						Evn.Evn_rid,
						RE.Evn_id,
						{$Evn_ident}
						Evn.EvnClass_id,
						ret.RegistryErrorType_Code,
						rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
						ps.Person_id, 
						ps.PersonEvn_id, 
						ps.Server_id, 
						RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
						RegistryErrorTFOMS_FieldName,
						RegistryErrorTFOMS_BaseElement,
						RegistryErrorTFOMS_Comment,
						MP.Person_Fio as MedPersonal_Fio,
						LB.LpuBuilding_Name, 
						LS.LpuSection_Name,
						ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
						case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
						{$addToSelect}
						-- end select
					from 
						-- from
						{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
						left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
						left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
						left join v_EvnSection es (nolock) on ES.EvnSection_id = RE.Evn_id
						left join v_EvnVizitPL evpl (nolock) on evpl.EvnVizitPL_id = RE.Evn_id
						left join v_LpuSection LS (nolock) on LS.LpuSection_id = ISNULL(ES.LpuSection_id, evpl.LpuSection_id)
						left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
						outer apply(
							select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = ISNULL(ES.MedPersonal_id, evpl.MedPersonal_id)
						) as MP
						left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
						left join {$this->scheme}.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
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
			break;
		}

		//echo getDebugSql($query, $params);die;

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
	function loadRegistryErrorTFOMSFilter($data)
	{

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
				$orderBy = "rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))";
				$field = "rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))";//'RE.'.$column;
			}
			elseif($filter_mode['cell'] == 'LpuSection_Name'){
				$field = 'LS.LpuSection_Name';
				$orderBy = 'LS.LpuSection_Name';
			}
			elseif($filter_mode['cell'] == 'Evn_id'){
				$field = 'RE.Evn_id';
				$orderBy = 'RE.Evn_id';
			}
			elseif($filter_mode['cell'] == 'RegistryErrorType_Code'){
				$field = 'ret.RegistryErrorType_Code';
				$orderBy = 'ret.RegistryErrorType_Code';
			}
			elseif($filter_mode['cell'] == 'Evn_ident'){
				$field = 'RE.Evn_id';
				$orderBy = 'RE.Evn_id';
				if ($this->region == 'kaluga' && $data['RegistryType_id'] == 1) {
					$field = 'Evn.Evn_rid';
					$orderBy = 'Evn.Evn_rid';
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
		Select
			-- select
			RegistryErrorTFOMS_id,
			RE.Registry_id,
			Evn.Evn_rid,
			RE.Evn_id,
			Evn.EvnClass_id,
			ret.RegistryErrorType_Code,
			rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
			ps.Person_id,
			ps.PersonEvn_id,
			ps.Server_id,
			RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
			RegistryErrorTFOMS_FieldName,
			RegistryErrorTFOMS_BaseElement,
			RegistryErrorTFOMS_Comment,
			--MP.Person_Fio as MedPersonal_Fio,
			RTRIM(RD.MedPersonal_Fio) as MedPersonal_Fio,
			LB.LpuBuilding_Name,
			LS.LpuSection_Name,
			ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
			case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
			-- end select
		from
			-- from
			{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
			left join {$this->scheme}.v_RegistryData RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
			left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
			left join v_EvnSection es (nolock) on ES.EvnSection_id = RE.Evn_id
			left join v_EvnVizitPL evpl (nolock) on evpl.EvnVizitPL_id = RE.Evn_id
			left join v_LpuSection LS (nolock) on LS.LpuSection_id = ISNULL(ES.LpuSection_id, evpl.LpuSection_id)
			left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			outer apply(
				select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = ISNULL(ES.MedPersonal_id, evpl.MedPersonal_id)
			) as MP
			left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
			left join {$this->scheme}.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
			-- end from
		where
			-- where
			RE.Registry_id=:Registry_id
			and
			{$filter}
			-- end where
			-- group by
			group by {$field}
			-- end group by
		order by
			-- order by
			{$field}
			-- end order by";

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
	 * Удаление объединённого реестра
	 */
	function deleteUnionRegistry($data)
	{
		// 1. удаляем все связи
		$query = "
			delete {$this->scheme}.RegistryGroupLink with (ROWLOCK)
			where Registry_pid = :Registry_id
		";
		$this->db->query($query, array(
			'Registry_id' => $data['id']
		));
		
		// 2. удаляем сам реестр
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$this->scheme}.p_Registry_del
				@Registry_id = :Registry_id,
				@pmUser_delID = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		$result = $this->db->query($query, array(
			 'Registry_id' => $data['id']
			,'pmUser_id' => $data['pmUser_id']
		));
		
		if (is_object($result)) 
		{
			return $result->result('array');
		}
		
		return false;
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

		$sql = "
		select top 1
			c.CmpCloseCard_id
		from 
			v_CmpCloseCard c with (nolock)
			inner join v_CmpCallCard cc with (nolock) on cc.cmpcallcard_id = c.CmpCallCard_id
			inner join {$this->scheme}.RegistryDataCmp rd with (nolock) on rd.cmpclosecard_id = c.cmpclosecard_id
			inner join {$this->scheme}.v_Registry r with (nolock) on r.Registry_id = rd.Registry_id
		where 
			cc.CmpCallCard_id = :CmpCallCard_id
			and ((rd.RegistryDataCmp_IsPaid = 2 and r.RegistryStatus_id = 4) or r.RegistryStatus_id in (2,3))			
		";
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
	 * Различные региональные проверки перед переформированием
	 */
	public function checkBeforeSaveRegistryQueue($data)
	{
		$result = parent::checkBeforeSaveRegistryQueue($data);

		if ( $result !== true ) {
			return $result;
		}

		$query = "
			select top 1
				R.Registry_id
			from
				{$this->scheme}.v_RegistryGroupLink RGL (nolock)
				inner join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RGL.Registry_pid
			where
				RGL.Registry_id = :Registry_id
				and R.Registry_xmlExportPath = '1'
		";
		
		$result = $this->db->query($query, $data);
		if (is_object($result)) 
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array(array('success' => false, 'Error_Msg' => '<b>По данному реестру формируется выгрузка в XML.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания выгрузки реестра.'));
			}
		}
		
		return true;
	}
	
	/**
	 * Сохранение объединённого реестра
	 */
	function saveUnionRegistry($data)
	{
		// проверка уникальности номера реестра по лпу в одном году
		$query = "
			select top 1
				Registry_id
			from
				{$this->scheme}.v_Registry (nolock)
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and Registry_Num = :Registry_Num
				and year(Registry_accDate) = year(:Registry_accDate)
				and (Registry_id <> :Registry_id OR :Registry_id IS NULL)
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_id'])) {
				return array('Error_Msg' => 'Номер счета не должен повторяться в году');
			}
		}
		
		// 1. сохраняем объединённый реестр
		$proc = 'p_Registry_ins';
		if (!empty($data['Registry_id'])) {
			$proc = 'p_Registry_upd';
		}
		$query = "
			declare
				@Error_Code bigint,
				@KatNasel_Code bigint = (select top 1 KatNasel_Code from v_KatNasel (nolock) where KatNasel_id = :KatNasel_id),
				@Error_Message varchar(4000),
				@Registry_id bigint = :Registry_id,
				@curdate datetime = dbo.tzGetDate();
			exec {$this->scheme}.{$proc}
				@Registry_id = @Registry_id output,
				@RegistryType_id = 13,
				@RegistryStatus_id = 1,
				@Registry_Sum = NULL,
				@Registry_IsActive = 2,
				@Registry_Num = :Registry_Num,
				@Registry_accDate = :Registry_accDate,
				@Registry_begDate = :Registry_begDate,
				@Registry_endDate = :Registry_endDate,
				@KatNasel_id = :KatNasel_id,
				@OrgSMO_id = :OrgSMO_id,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Registry_id as Registry_id, @KatNasel_Code as KatNasel_Code, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//		@RegistryGroupType_id = :RegistryGroupType_id,
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) 
		{
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Registry_id'])) {
				// 2. удаляем все связи
				$query = "
					delete {$this->scheme}.RegistryGroupLink with (ROWLOCK)
					where Registry_pid = :Registry_id
				";
				$this->db->query($query, array(
					'Registry_id' => $resp[0]['Registry_id']
				));
				
				// 3. выполняем поиск реестров которые войдут в объединённый
				$orgsmofilter = "";
				if ($resp[0]['KatNasel_Code'] == 1) {
					$orgsmofilter = " and R.OrgSMO_id = :OrgSMO_id";
				}
				
				$registrytypefilter = "";
				/*if ($resp[0]['KatNasel_Code'] == 1) {
					switch ($data['RegistryGroupType_id']) {
						case 1:
							$registrytypefilter = " and R.RegistryType_id IN (1,2,6,15)";
						break;
						case 3:
							$registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 1";
						break;
						case 4:
							$registrytypefilter = " and R.RegistryType_id IN (7) and R.DispClass_id = 2";
						break;
						case 5:
							$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 3";
						break;
						case 6:
							$registrytypefilter = " and R.RegistryType_id IN (9) and R.DispClass_id = 7";
						break;
						case 7:
							$registrytypefilter = " and R.RegistryType_id IN (12) and R.DispClass_id = 6";
						break;
						case 8:
							$registrytypefilter = " and R.RegistryType_id IN (12) and R.DispClass_id = 9";
						break;
						case 9:
							$registrytypefilter = " and R.RegistryType_id IN (12) and R.DispClass_id = 10";
						break;
						case 10:
							$registrytypefilter = " and R.RegistryType_id IN (11)";
						break;
					}
				}*/
				
				$query = "
					select
						R.Registry_id
					from
						{$this->scheme}.v_Registry R (nolock)
					where
						R.RegistryType_id <> 13
						and R.RegistryStatus_id = 2 -- к оплате
						and R.KatNasel_id = :KatNasel_id
						and R.Lpu_id = :Lpu_id
						{$orgsmofilter}
						and R.Registry_begDate >= :Registry_begDate
						and R.Registry_endDate <= :Registry_endDate
						and not exists(select top 1 RegistryGroupLink_id from {$this->scheme}.v_RegistryGroupLink (nolock) where Registry_id = R.Registry_id)
						{$registrytypefilter}
				";
				$result_reg = $this->db->query($query, array(
					'KatNasel_id' => $data['KatNasel_id'],
					'OrgSMO_id' => $data['OrgSMO_id'],
					'Lpu_id' => $data['Lpu_id'],
					'Registry_begDate' => $data['Registry_begDate'],
					'Registry_endDate' => $data['Registry_endDate']
				));
				
				if (is_object($result_reg)) 
				{
					$resp_reg = $result_reg->result('array');
					// 4. сохраняем новые связи
					foreach($resp_reg as $one_reg) {
						$query = "
							declare
								@Error_Code bigint,
								@Error_Message varchar(4000),
								@RegistryGroupLink_id bigint = null;
							exec {$this->scheme}.p_RegistryGroupLink_ins
								@RegistryGroupLink_id = @RegistryGroupLink_id output,
								@Registry_pid = :Registry_pid,
								@Registry_id = :Registry_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
							select @RegistryGroupLink_id as RegistryGroupLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;						
						";
						
						$this->db->query($query, array(
							'Registry_pid' => $resp[0]['Registry_id'],
							'Registry_id' => $one_reg['Registry_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}

				// пишем информацию о формировании реестра в историю
				$this->dumpRegistryInformation(array(
					'Registry_id' => $resp[0]['Registry_id']
				), 1);
			}
			
			return $resp;
		}
		
		return false;
	}
	
	/**
	 * Получение номера объединённого реестра
	 */
	function getUnionRegistryNumber($data)
	{
		$query = "
			select
				ISNULL(MAX(cast(Registry_Num as bigint)),0) + 1 as Registry_Num
			from
				{$this->scheme}.v_Registry (nolock)
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and ISNUMERIC(Registry_Num) = 1
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Registry_Num'])) {
				return $resp[0]['Registry_Num'];
			}
		}
		
		return 1;
	}
	
	/**
	 * Загрузка формы редактирования объединённого реестра
	 */
	function loadUnionRegistryEditForm($data)
	{
		$query = "
			select
				R.Registry_id,
				R.Registry_Num,
				convert(varchar,R.Registry_accDate,104) as Registry_accDate,
				convert(varchar,R.Registry_begDate,104) as Registry_begDate,
				convert(varchar,R.Registry_endDate,104) as Registry_endDate,
				R.KatNasel_id,
				R.RegistryGroupType_id,
				R.OrgSMO_id,
				R.Lpu_id,
				RCS.RegistryCheckStatus_id,
				RCS.RegistryCheckStatus_Code
			from
				{$this->scheme}.v_Registry R (nolock)
				left join v_RegistryCheckStatus RCS with (nolock) on RCS.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			where
				R.Registry_id = :Registry_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) 
		{
			return $result->result('array');
		}
		
		return false;
	}
	
	/**
	 * Загрузка списка объединённых реестров
	 */
	function loadUnionRegistryGrid($data)
	{
		$query = "
		Select 
			-- select
			R.Registry_id,
			R.Registry_Num,
			convert(varchar,R.Registry_accDate,104) as Registry_accDate,
			convert(varchar,R.Registry_begDate,104) as Registry_begDate,
			convert(varchar,R.Registry_endDate,104) as Registry_endDate,
			R.KatNasel_id,
			KN.KatNasel_Name,
			KN.KatNasel_SysNick,
			RGT.RegistryGroupType_Name,
			OS.OrgSMO_Nick,
			ISNULL(RS.Registry_SumPaid, 0.00) as Registry_SumPaid,
			rcs.RegistryCheckStatus_Code,
			rcs.RegistryCheckStatus_Name
			-- end select
		from 
			-- from
			{$this->scheme}.v_Registry R (nolock) -- объединённый реестр
			left join v_RegistryCheckStatus rcs (nolock) on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			left join v_RegistryGroupType RGT (nolock) on RGT.RegistryGroupType_id = R.RegistryGroupType_id
			left join v_OrgSMO OS (nolock) on OS.OrgSMO_id = R.OrgSMO_id
			left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
			outer apply(
				select
					SUM(ISNULL(R2.Registry_SumPaid,0)) as Registry_SumPaid
				from {$this->scheme}.v_Registry R2 (nolock)
					inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on R2.Registry_id = RGL.Registry_id
				where
					RGL.Registry_pid = R.Registry_id
			) RS
			-- end from
		where
			-- where
			R.Lpu_id = :Lpu_id
			and R.RegistryType_id = 13
			-- end where
		order by
			-- order by
			R.Registry_id
			-- end order by";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
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
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	function loadUnionRegistryChildGrid($data)
	{
		$query = "
		Select 
			-- select
			R.Registry_id,
			R.Registry_Num,
			convert(varchar,R.Registry_accDate,104) as Registry_accDate,
			convert(varchar,R.Registry_begDate,104) as Registry_begDate,
			convert(varchar,R.Registry_endDate,104) as Registry_endDate,
			KN.KatNasel_Name,
			RT.RegistryType_Name,
			ISNULL(R.Registry_Sum, 0.00) as Registry_Sum,
			ISNULL(R.Registry_SumPaid, 0.00) as Registry_SumPaid,
			PT.PayType_Name,
			LB.LpuBuilding_Name,
			convert(varchar,R.Registry_updDT,104) as Registry_updDate
			-- end select
		from 
			-- from
			{$this->scheme}.v_RegistryGroupLink RGL (nolock)
			inner join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RGL.Registry_id -- обычный реестр
			left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
			left join v_RegistryType RT (nolock) on RT.RegistryType_id = R.RegistryType_id
			left join v_PayType PT (nolock) on PT.PayType_id = R.PayType_id
			left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = R.LpuBuilding_id
			-- end from
		where
			-- where
			RGL.Registry_pid = :Registry_pid
			-- end where
		order by
			-- order by
			R.Registry_id
			-- end order by";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
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
	 *	Получение списка дополнительных полей для выборки
	 */
	function getReformRegistryAdditionalFields() {
		return ',OrgSMO_id,DispClass_id';
	}
	
	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getLoadRegistryQueueAdditionalFields() {
		return ', R.DispClass_id';
	}
	
	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	function getLoadRegistryAdditionalFields() {
		return ', R.DispClass_id';
	}

	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = array(), $force = false) {
		parent::setRegistryParamsByType($data, $force);

		switch ( $this->RegistryType_id ) {
			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryDataEvnField = 'CmpCloseCard_id';
				$this->RegistryDoubleObject = 'RegistryCmpDouble';
			break;
		}
	}

	/**
	 *	Установка статуса экспорта реестра
	 */
	function SetExportStatus($data) {
		if ( empty($data['Registry_EvnNum']) ) {
			$data['Registry_EvnNum'] = null;
		}

		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}

		$query = "
			update
				{$this->scheme}.Registry with (rowlock)
			set
				Registry_xmlExportPath = :Status,
				Registry_EvnNum = :Registry_EvnNum,
				Registry_xmlExpDT = dbo.tzGetDate()
			where
				Registry_id = :Registry_id
		";

		$result = $this->db->query($query,
			array(
				'Registry_id' => $data['Registry_id'],
				'Registry_EvnNum' => $data['Registry_EvnNum'],
				'Status' => $data['Status']
			)
		);

		if ( is_object($result) ) {
			return true;
		}
		else {
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}

	/**
	 * Проверка наличия оплаченных реестров внутри объединенного
	 */
	function hasRegistryPaid($Registry_id) {
		$query = "
			select top 1
				r.Registry_id
			from
				{$this->scheme}.v_RegistryGroupLink RGL (nolock)
				inner join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RGL.Registry_id
			where
				RGL.Registry_pid = :Registry_pid
				and R.RegistryStatus_id = 4
		";

		$resp = $this->queryResult($query, array(
			'Registry_pid' => $Registry_id
		));

		if (count($resp) > 0) {
			return true;
		}

		return false;
	}

	/**
	 *	Установка статуса реестра
	 */
	function setRegistryStatus($data) {
		if ( empty($data['Registry_id']) || empty($data['RegistryStatus_id']) ) {
			return array(array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров'));
		}

		//#11018 При статусах "Готов к отправке в ТФОМС" и "Отправлен в ТФОМС" запретить перемещать реестр из состояния "К оплате".
		if ( !isSuperAdmin() ) {
			$RegistryCheckStatus_id = $this->getFirstResultFromQuery("SELECT RegistryCheckStatus_id FROM {$this->scheme}.v_Registry WHERE Registry_id = :Registry_id", array('Registry_id'=>$data['Registry_id']));

			// "Готов к отправке в ТФОМС"
			if ( $RegistryCheckStatus_id === '1' ) {
				throw new Exception('При статусе "Готов к отправке в ТФОМС" запрещено перемещать реестр из состояния "К оплате"');
			}

			// "Отправлен в ТФОМС"
			if ( $RegistryCheckStatus_id === '2' ) {
				throw new Exception('При статусе "Отправлен в ТФОМС" запрещено перемещать реестр из состояния "К оплате"');
			}

			// "Проведён контроль (ФЛК)"
			if ( $RegistryCheckStatus_id === '5' ) {
				throw new Exception('При статусе "Проведен контроль (ФЛК)" запрещено перемещать реестр из состояния "К оплате"');
			}
		}

		// Предварительно получаем тип реестра
		$RegistryType_id = 0;
		$RegistryStatus_id = 0;

		$query = "
			select RegistryType_id, RegistryStatus_id
			from {$this->scheme}.v_Registry Registry with (NOLOCK)
			where Registry_id = :Registry_id
		";
		$r = $this->db->query($query, array('Registry_id' => $data['Registry_id']));

		if (is_object($r) ) {
			$res = $r->result('array');

			if ( is_array($res) && count($res) > 0 ) {
				$RegistryType_id = $res[0]['RegistryType_id'];
				$RegistryStatus_id = $res[0]['RegistryStatus_id'];
			}
		}

		$fields = "";
			
		if ( $data['RegistryStatus_id'] == 3 ) { // если перевели в работу, то снимаем признак формирования
			//#11018 2. При перемещении реестра в других статусах в состояние "В работу " дополнительно сбрасывать Registry_xmlExpDT
			$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, RegistryCheckStatus_id = null, ";
		}

		if ($data['is_manual']!=1) {
			if ($data['RegistryStatus_id']==4) { // если переводим в оплаченные, то вызываем p_Registry_setPaid
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
				$result = $this->db->query($query, $data);
				if (!is_object($result))
				{
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке как оплаченный'));
				}
			} elseif ($RegistryStatus_id==4 && $data['RegistryStatus_id']==2) { // если переводим из "Оплаченный" в "К оплате" p_Registry_setUnPaid
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
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при отметке к оплате'));
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
				update {$this->scheme}.Registry set
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

			select @RegistryStatus_id as RegistryStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
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
	 *	Функция возрвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	function loadRegistryTypeNode($data)
	{
		$result = array(
			array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'),
			array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'),
			/*array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь'),
			array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения с 2013 года'),
			array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот с 2013 года'),
			array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения'),
			array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних'),
			array('RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги')*/
		);
			
		return $result;
	}

	/**
	 * Получение списка типов реестров, входящих в объединенный реестр
	 */
	function getUnionRegistryTypes($Registry_pid = 0) {
		$query = "
			select distinct r.RegistryType_id
			from {$this->scheme}.v_RegistryGroupLink rgl with (nolock)
				inner join {$this->scheme}.v_Registry r with (nolock) on r.Registry_id = rgl.Registry_id
			where rgl.Registry_pid = :Registry_pid
		";
		$result = $this->db->query($query, array('Registry_pid' => $Registry_pid));

		if ( !is_object($result) ) {
			return false;
		}

		$registryTypes = array();
		$resp = $result->result('array');

		foreach ( $resp as $rec ) {
			$registryTypes[] = $rec['RegistryType_id'];
		}

		return $registryTypes;
	}

	/**
	 * Получение группы случаев из реестров по стационару
	 */
	function getRegistryDataGroupForDelete($data)
	{
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Evn_id' => $data['Evn_id']
		);

		$this->setRegistryParamsByType($data);

		$query = "
			select top 1 MaxEvn_id, Evn_rid
			from {$this->scheme}.v_{$this->RegistryDataObject} RD with(nolock)
			where RD.Registry_id = :Registry_id and RD.Evn_id = :Evn_id
		";
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp) || count($resp) == 0) {
			return  $resp;
		}
		$params = array_merge($params, $resp[0]);

		$query = "
			select RD.Evn_id
			from {$this->scheme}.v_{$this->RegistryDataObject} RD with(nolock)
			where RD.Registry_id = :Registry_id and RD.Evn_rid = :Evn_rid
		";

		return $this->queryResult($query, $params);
	}

	/**
	 *	Помечаем запись реестра на удаление
	 */
	function deleteRegistryData($data)
	{
		$evn_list = $data['EvnIds'];

		//На Карелии случаи в стационаре группируются
		//При удалении одного случая из группы нужно удалить всю группу
		if ($data['RegistryType_id'] == 1) {
			$new_evn_list = array();

			foreach ($evn_list as $EvnId) {
				$resp = $this->getRegistryDataGroupForDelete(array(
					'Registry_id' => $data['Registry_id'],
					'Evn_id' => $EvnId
				));
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
				foreach($resp as $item) {
					$new_evn_list[] = $item['Evn_id'];
				}
			}
			$evn_list = array_unique($new_evn_list);
		}

		foreach ($evn_list as $EvnId) {
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
	 * Получение данных Дубли посещений (RegistryDouble)
	 */
	function loadRegistryDouble($data) {
		$this->setRegistryParamsByType($data);

		$filter = "";
		
		if ( !empty($data['MedPersonal_id']) ) {
			$filter .= " and MP.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		switch ( $this->RegistryType_id ) {
			case 6:
				$query = "
					select
						-- select
						 RD.Registry_id
						,RD.Evn_id
						,null as Evn_rid
						,RD.Person_id
						,rtrim(IsNull(RD.Person_SurName,'')) + ' ' + rtrim(IsNull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, '')) as Person_FIO
						,RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay
						,CCC.Year_num as Evn_Num
						,ETS.EmergencyTeamSpec_Name as LpuSection_FullName
						,MP.Person_Fio as MedPersonal_Fio
						,convert(varchar(10), CCC.AcceptTime, 104) as Evn_setDate
						,CCC.CmpCallCard_id
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD with (NOLOCK)
						left join v_CmpCloseCard CCC with (nolock) on CCC.CmpCloseCard_id = RD.Evn_id
						left join v_EmergencyTeamSpec ETS with (nolock) on ETS.EmergencyTeamSpec_id = CCC.EmergencyTeamSpec_id
						outer apply(
							select top 1 Person_Fio, MedPersonal_id from v_MedPersonal with(nolock) where MedPersonal_id = CCC.MedPersonal_id
						) as MP
						-- end from
					where
						-- where
						RD.Registry_id = :Registry_id
						{$filter}
						-- end where
					order by
						-- order by
						RD.Person_SurName,
						RD.Person_FirName,
						RD.Person_SecName
						-- end order by
				";
			break;

			default:
				$query = "
					select
						-- select
						 RD.Registry_id
						,RD.Evn_id
						,EPL.EvnPL_id as Evn_rid
						,RD.Person_id
						,rtrim(IsNull(RD.Person_SurName,'')) + ' ' + rtrim(IsNull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, '')) as Person_FIO
						,RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay
						,EPL.EvnPL_NumCard as Evn_Num
						,LS.LpuSection_FullName
						,MP.Person_Fio as MedPersonal_Fio
						,convert(varchar(10), EVPL.EvnVizitPL_setDT, 104) as Evn_setDate
						,null as CmpCallCard_id
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD with (NOLOCK)
						left join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = RD.Evn_id
						left join v_EvnPL EPL with (nolock)  on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
						left join v_LpuSection LS with (nolock)  on LS.LpuSection_id = EVPL.LpuSection_id
						outer apply(
							select top 1 Person_Fio, MedPersonal_id from v_MedPersonal with(nolock) where MedPersonal_id = EVPL.MedPersonal_id
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
			break;
		}

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
	}

	/**
	 * Запрос для проверки наличия данных для вкладки "Дублеи посещений"
	 */
	function getRegistryDoubleCheckQuery($scheme = 'dbo') {
		return "
			select top 1 Evn_id from {$scheme}.v_RegistryDouble with(nolock) where Registry_id = R.Registry_id
			union all
			select top 1 Evn_id from {$scheme}.v_RegistryCmpDouble with(nolock) where Registry_id = R.Registry_id
		";
	}

	/**
	 *	Комментарий
	 */
	function deleteRegistryDouble($data)
	{
		$data['RegistryType_id'] = $this->RegistryType_id;

		$query = "
			Declare @Error_Code bigint;
			Declare @Error_Message varchar(4000);
			exec {$this->scheme}.p_RegistryDouble_del
				@Registry_id = :Registry_id,
				@RegistryType_id = :RegistryType_id,
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
	 * Получение дополнительных данных для печати счета
	 */
	function getAdditionalPrintInfo(&$data) {
		if ( !is_array($data) || empty($data['Registry_id']) ) {
			return false;
		}

		if ( !empty($data['KatNasel_SysNick']) && $data['KatNasel_SysNick'] == 'oblast' ) {
			$query = "
				select top 1
					RTRIM(ISNULL(o.Org_Name, o.Org_Nick)) as OrgP_Name,
					oa.Address_Address as OrgP_Address,
					o.Org_Phone as OrgP_Phone,
					ors.OrgRSchet_RSchet as OrgP_RSchet,
					ob.OrgBank_Name as OrgP_Bank,
					ob.OrgBank_BIK as OrgP_BankBIK,
					o.Org_INN as OrgP_INN,
					o.Org_KPP as OrgP_KPP,
					Okved.Okved_Code as OrgP_OKVED,
					o.Org_OKPO as OrgP_OKPO,
					Oktmo.Oktmo_Code as OrgP_OKTMO
				from {$this->scheme}.v_Registry r with (NOLOCK)
					inner join v_OrgSmo os with (nolock) on os.OrgSmo_id = r.OrgSmo_id
					inner join v_Org o with (nolock) on o.Org_id = os.Org_id
					left join [Address] oa with (nolock) on oa.Address_id = o.UAddress_id
					outer apply (
						select top 1
							OrgRSchet_RSchet,
							OrgBank_id
						from v_OrgRSchet with (nolock)
						where Org_id = o.Org_id
							and OrgRSchetType_id = 1 -- Расчетный
					) ors
					left join v_OrgBank ob with (nolock) on ob.OrgBank_id = ors.OrgBank_id
					left join v_Okved Okved with (nolock) on Okved.Okved_id = o.Okved_id
					left join v_Oktmo Oktmo with (nolock) on Oktmo.Oktmo_id = o.Oktmo_id
				where r.Registry_id = :Registry_id
			";
			$result = $this->db->query($query, $data);

			if ( is_object($result) ) {
				$response = $result->result('array');

				if ( is_array($response) && count($response) > 0 ) {
					$data = array_merge($data, $response[0]);
				}
			}
			else {
				return false;
			}
		}

		return true;
	}

	/**
	 * Загрузка списка статусов реестра
	 */
	function loadRegistryStatusNode($data)
	{
		$result = array(
		array('RegistryStatus_id' => 5, 'RegistryStatus_Name' => 'В очереди'),
		array('RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'),
		array('RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'),
		array('RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные'),
		//array('RegistryStatus_id' => 6, 'RegistryStatus_Name' => 'Удаленные')
		);
		return $result;
	}

	/**
	 * Получаем состояние реестра в данный момент и тип реестра
	 */
	function GetUnionRegistryDBFExport($data) {
		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}

		$query = "
			with RD (
				Evn_id,
				Evn_rid,
				RegistryData_ItogSum
			) as (
				select
					RDE.Evn_id,
					RDE.Evn_rid,
					RDE.RegistryData_ItogSum
				from
					{$this->scheme}.v_RegistryData RDE (nolock)
					inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_id = RDE.Registry_id
				where
					RGL.Registry_pid = :Registry_id

				union all

				select
					RDE.Evn_id,
					RDE.Evn_rid,
					RDE.RegistryData_ItogSum
				from
					{$this->scheme}.v_RegistryDataCmp RDE (nolock)
					inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_id = RDE.Registry_id
				where
					RGL.Registry_pid = :Registry_id
			)

			select
				RTrim(R.Registry_xmlExportPath) as Registry_xmlExportPath,
				R.Registry_FileNum,
				R.RegistryType_id,
				R.RegistryStatus_id,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				ISNULL(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as Registry_SumDifference,
				RDSum.RegistryData_Count as RegistryData_Count,
				R.Registry_endDate,
				L.Lpu_RegNomN2 as Lpu_Code,
				rcs.RegistryCheckStatus_Code
			from {$this->scheme}.v_Registry R with (nolock)
				inner join v_Lpu L with (nolock) on L.Lpu_id = R.Lpu_id
				outer apply(
					select
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from RD with (nolock)
				) RDSum
				left join v_RegistryCheckStatus rcs with (nolock) on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
			where
				Registry_id = :Registry_id
		";

		$result = $this->db->query($query,
			array(
				'Registry_id' => $data['Registry_id']
			)
		);

		if ( !is_object($result) ) {
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}

		return $result->result('array');
	}

	/**
	 * Получение данных для экспорта объединенного реестра в DBF
	 * @return ссылка на ресурс
	 */
	function getDataForExport($sp, $Registry_id) {
		if ( empty($sp) || empty($Registry_id) ) {
			return false;
		}

		$query = "
			exec {$this->scheme}.{$sp} @Registry_id = :Registry_id
		";

		return $this->db->query($query, array('Registry_id' => $Registry_id));
	}
}