<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Org_model4E - модель, для работы с таблицей Org и производными (OrgDep, OrgSmo)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*/
class Org_model4E extends CI_Model {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	
	
	/**
	* Получение сисника для OrgType_id
	*/	
	function getOrgTypeSysNick($OrgType_id) 
	{
		$query = "
			select
				OrgType_SysNick
			from 
				v_OrgType with (nolock)
			where
				OrgType_id = :OrgType_id
		";
		$res = $this->db->query($query, array( 'OrgType_id' => $OrgType_id ));

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (count($resp) > 0) {
				return $resp[0]['OrgType_SysNick'];
			}
		}

		return '';
	}
	
	/**
	* Сохраняет организацию
	*/
	function saveOrg($data) {
		$procedure_action = '';
		
		if ( !isSuperAdmin() && !havingGroup('AdminOrgReference') && isset($data['Org_id']) && $data['Org_id'] > 0 && $data["isminzdrav"]==false)
		{
			// проверка на возможность редактирования
			$sql = "
				select top 1 Server_id
				from Org with(nolock)
				where Org_id = ?
			";
			$res = $this->db->query($sql, array($data['Org_id']));
			if ( is_object($res) )
			{
				$sel = $res->result('array');
				if ( $sel[0]['Server_id'] == 0 )
				{
					$sel[0]['Error_Code'] = '666';
					$sel[0]['Error_Msg'] = 'Вы не можете редактировать эту организацию.';
					return $sel;
				}
			}
		}
		
		// проверяем INN
		if ( $data['Org_INN'] != '' )
		{
			$sql = "
				select
					dbo.CheckINN('{$data['Org_INN']}') as is_inn_valid
			";
			$res = $this->db->query($sql);
			if ( is_object($res) )
			{
				$sel = $res->result('array');
				if ($sel[0]['is_inn_valid'] > 0)
				{
					$sel[0]['Error_Code'] = '666';
					$sel[0]['Error_Msg'] = 'ИНН не соответствует алгоритму формирования.';
					return $sel;
				}
			}
			else
			{
				$sel[0]['Error_Code'] = 1;
				$sel[0]['Error_Msg'] = 'Не удалось проверить валидность ИНН';
				return $sel;
			}
			
		
		}
		
		// проверяем ОГРН
		if ( $data['Org_OGRN'] != '' )
		{
			$sql = "
				select
					dbo.CheckOGRN('{$data['Org_OGRN']}') as is_ogrn_valid
			";
			$res = $this->db->query($sql);
			if ( is_object($res) )
			{
				$sel = $res->result('array');
				if ($sel[0]['is_ogrn_valid'] > 0)
				{
					$sel[0]['Error_Code'] = '666';
					$sel[0]['Error_Msg'] = 'ОГРН не соответствует алгоритму формирования.';
					return $sel;
				}
			}
			else
			{
				$sel[0]['Error_Code'] = 1;
				$sel[0]['Error_Msg'] = 'Не удалось проверить валидность ОГРН';
				return $sel;
			}
			
			$id_filter = "";
			if ( isset($data['Org_id']) && $data['Org_id'] > 0 )
				$id_filter = " and Org_id <> {$data['Org_id']} ";			
			if ( !isset($data['check_double_ogrn_cancel']) || !($data['check_double_ogrn_cancel'] == 2) )
			{
				// проверка на двойников по OGRN
				$sql = "
					select 
						top 1 
						rtrim(Org_Name) as Org_Name, 
						count(Org_id) as cnt
					from Org with(nolock)
					where Org_OGRN = '{$data['Org_OGRN']}'
					{$id_filter}
					group by Org_Name
					having count(Org_id) > 0
				";
				$res = $this->db->query($sql);
				if ( is_object($res) )
				{
					$sel = $res->result('array');
					if ( is_array($sel) && count($sel) > 0 && $sel[0]['cnt'] > 0 )
					{
						if ( !isset($data['check_double_inn_cancel']) || !($data['check_double_inn_cancel'] == 2) )
							$sel[0]['Error_Code'] = '888';
						else
							$sel[0]['Error_Code'] = '889';
						$sel[0]['Error_Msg'] = 'ОГРН совпадает с ОГРН организации: "' . $sel[0]['Org_Name'] . '".';
						return $sel;
					}
				}
				else
				{
					$sel[0]['Error_Code'] = 1;
					$sel[0]['Error_Msg'] = 'Не удалось проверить валидность ОГРН';
					return $sel;
				}
			}
		}
		
		// проверяем INN
		if ( $data['Org_INN'] != '' )
		{
			$id_filter = "";
			if ( isset($data['Org_id']) && $data['Org_id'] > 0 )
				$id_filter = " and Org_id <> {$data['Org_id']} ";			
			if ( !isset($data['check_double_inn_cancel']) || !($data['check_double_inn_cancel'] == 2) )
			{
				// проверка на двойников по INN
				$sql = "
					select 
						top 1 
						rtrim(Org_Nick) as Org_Nick,
						count(Org_id) as cnt
					from Org with(nolock)
					where Org_INN = '{$data['Org_INN']}'
					{$id_filter}
					group by Org_Nick
					having count(Org_id) > 0
				";
				$res = $this->db->query($sql);
				if ( is_object($res) )
				{
					$sel = $res->result('array');
					if ( is_array($sel) && count($sel) > 0 && $sel[0]['cnt'] > 0 )
					{						
						if ( !isset($data['check_double_ogrn_cancel']) || !($data['check_double_ogrn_cancel'] == 2) )
							$sel[0]['Error_Code'] = '777';
						else
							$sel[0]['Error_Code'] = '778';
						$sel[0]['Error_Msg'] = 'ИНН совпадает с ИНН организации: "'.$sel[0]['Org_Nick'].'". ';
						return $sel;
					}
				}
				else
				{
					$sel[0]['Error_Code'] = 1;
					$sel[0]['Error_Msg'] = 'Не удалось проверить валидность ИНН';
					return $sel;
				}
			}
		}
		
		// Сохраняем или редактируем адрес

		// PAddress
		if ( !isset($data['PAddress_AddressText']) ) {
			$data['PAddress_id'] = NULL;
		}
		else {
			$procedure_action = "ins";
			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :PAddress_id;

				exec p_Address_" . $procedure_action . "
					@Server_id = :Server_id,
					@Address_id = @Res output,					
					@Address_Address = :Address_Address,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

				select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
			";

			$queryParams = array(
				'PAddress_id' => $data['PAddress_id'],
				'Server_id' => $data['Server_id'],
				'KLCountry_id' => '643',
				'KLRgn_id' => '59',
				'KLSubRgn_id' => null,
				'KLCity_id' => null,
				'KLTown_id' => null,
				'KLStreet_id' => null,
				'Address_Zip' => null,
				'Address_House' => null,
				'Address_Corpus' => null,
				'Address_Flat' => null,
				'Address_Address' => $data['PAddress_AddressText'],
				'pmUser_id' => $data['pmUser_id']
			);
			$res = $this->db->query($query, $queryParams);
			if(is_object($res)){
				$response = $res->result('array');
				if(isset($response[0]) && strlen($response[0]['Error_Msg']) == 0){
					$data['PAddress_id'] = $response[0]['Address_id'];
				} else {
					return $response;
				}
			} else {
				return false;
			}
		}

		// UAddress
		if ( !isset($data['UAddress_AddressText']) ) {
			$data['UAddress_id'] = NULL;
		} else {
			$procedure_action = "ins";
			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :UAddress_id;

				exec p_Address_" . $procedure_action . "
					@Server_id = :Server_id,
					@Address_id = @Res output,
					@KLAreaType_id = NULL,
					@KLCountry_id = :KLCountry_id,
					@KLRgn_id = :KLRgn_id,
					@KLSubRgn_id = :KLSubRgn_id,
					@KLCity_id = :KLCity_id,
					@KLTown_id = :KLTown_id,
					@KLStreet_id = :KLStreet_id,
					@Address_Zip = :Address_Zip,
					@Address_House = :Address_House,
					@Address_Corpus = :Address_Corpus,
					@Address_Flat = :Address_Flat,
					@Address_Address = :Address_Address,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

				select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
			";

			$queryParams = array(
				'UAddress_id' => $data['UAddress_id'],
				'Server_id' => $data['Server_id'],
				'KLCountry_id' => '643',
				'KLRgn_id' => '59',
				'KLSubRgn_id' => null,
				'KLCity_id' => null,
				'KLTown_id' => null,
				'KLStreet_id' => null,
				'Address_Zip' => null,
				'Address_House' => null,
				'Address_Corpus' => null,
				'Address_Flat' => null,
				'Address_Address' => $data['UAddress_AddressText'],
				'pmUser_id' => $data['pmUser_id']
			);

			$res = $this->db->query($query, $queryParams);

			if ( is_object($res) ) {
				$response = $res->result('array');

				if ( isset($response[0]) && strlen($response[0]['Error_Msg']) == 0 ) {
					$data['UAddress_id'] = $response[0]['Address_id'];
				} else {
					return $response;
				}
			}
			else {
				return false;
			}
		}

		if ( !isset($data['Org_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000),
				@Okogu_id			bigint,
				@Okonh_id			bigint,
				@Org_OKDP			varchar(20),
				@Org_Rukovod		varchar(100) = :Org_Rukovod,
				@Org_Buhgalt		varchar(100) = :Org_Buhgalt,
                @Org_IsEmailFixed	bigint,
                @Org_KBK			varchar(20),
                @Org_pid			bigint,
				@Org_isAccess		bigint,
                @Org_RGN			varchar(15),
                @Org_WorkTime		varchar(64),
                @Org_Www			varchar(100),
				@DepartAffilType_id	bigint;
			set @Res = :Org_id;

			select
				@Okogu_id			= Okogu_id,
				@Okonh_id			= Okonh_id,
				@Org_OKDP			= Org_OKDP,
				@Org_Rukovod		= isnull(:Org_Rukovod,Org_Rukovod),
				@Org_Buhgalt		= isnull(:Org_Buhgalt,Org_Buhgalt),
                @Org_IsEmailFixed	= Org_IsEmailFixed,
                @Org_KBK			= Org_KBK,
                @Org_pid			= Org_pid,
				@Org_isAccess		= ISNULL(Org_isAccess, 1),
                @Org_RGN			= Org_RGN,
                @Org_WorkTime		= Org_WorkTime,
                @Org_Www			= Org_Www,
				@DepartAffilType_id = DepartAffilType_id
            from
                v_org with(nolock)
            where
                org_id = :Org_id;

			exec p_Org_" . $procedure_action . "
				@Server_id = :Server_id,
				@Org_id = @Res {$out},
				@Org_Code = :Org_Code,
				@Org_Nick = :Org_Nick,
				@Org_rid = :Org_rid,
				@Org_nid = :Org_nid,
				@Org_begDate = :Org_begDate,
				@Org_endDate = :Org_endDate,
				@Org_Description = :Org_Description,
				@Org_Name = :Org_Name,
				@Okved_id = :Okved_id,
				@Org_INN = :Org_INN,
				@Org_OGRN = :Org_OGRN,
				@Org_Phone = :Org_Phone,
				@Org_Email = :Org_Email,
				@OrgType_id = :OrgType_id,
				@UAddress_id = :UAddress_id,
				@PAddress_id = :PAddress_id,
				@Okopf_id    = :Okopf_id   ,
				@Okogu_id    = @Okogu_id   ,
				@Okonh_id    = @Okonh_id   ,
				@Okfs_id			= :Okfs_id,
				@Org_KPP			= :Org_KPP,
				@Org_OKPO			= :Org_OKPO,
				@Org_OKATO			= :Org_OKATO,
				@Org_OKDP			= @Org_OKDP,
				@Org_Rukovod		= @Org_Rukovod,
				@Org_Buhgalt		= @Org_Buhgalt,
				@Org_StickNick		= :Org_StickNick,
                @Org_IsEmailFixed	= @Org_IsEmailFixed,
                @Org_KBK			= @Org_KBK,
                @Org_pid			= @Org_pid,
                @Org_RGN			= @Org_RGN,
                @Org_WorkTime		= @Org_WorkTime,
                @Org_Www			= @Org_Www,
				@Org_isAccess		= @Org_isAccess,
				@DepartAffilType_id = @DepartAffilType_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

			select @Res as Org_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$queryParams = array(
			'Org_id' => $data['Org_id'],
			'Server_id' => $data['Server_id'],
			'Org_Code' => $data['Org_Code'],
			'Org_Nick' => $data['Org_Nick'],
			'Org_StickNick' => $data['Org_StickNick'],
			'Org_Description' => $data['Org_Description'],
			'Org_rid' => $data['Org_rid'],
			'Org_nid' => !empty($data['Org_nid']) ? $data['Org_nid'] : $data['Org_rid'],
			'Org_begDate' => $data['Org_begDate'],
			'Org_endDate' => $data['Org_endDate'],
			'Org_Name' => $data['Org_Name'],
			'Okved_id' => $data['Okved_id'],
			'Okopf_id' => $data['Okopf_id'],
			'Okfs_id' => $data['Okfs_id'],
			'Org_INN' => $data['Org_INN'],
			'Org_OKATO' => $data['Org_OKATO'],
			'Org_KPP' => $data['Org_KPP'],
			'Org_OGRN' => $data['Org_OGRN'],
			'Org_OKPO' => $data['Org_OKPO'],
			'Org_Phone' => $data['Org_Phone'],
			'Org_Email' => $data['Org_Email'],
			'OrgType_id' => $data['OrgType_id'],
			'UAddress_id' => $data['UAddress_id'],
			'PAddress_id' => $data['PAddress_id'],
			'KLCountry_id' => $data['KLCountry_id'],
			'KLRGN_id' => $data['KLRGN_id'],
			'KLSubRGN_id' => $data['KLSubRGN_id'],
			'KLCity_id' => $data['KLCity_id'],
			'KLTown_id' => $data['KLTown_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Org_Rukovod' => isset($data['Org_Rukovod']) && !empty($data['Org_Rukovod']) ? $data['Org_Rukovod'] : null,
			'Org_Buhgalt' => isset($data['Org_Buhgalt']) && !empty($data['Org_Buhgalt']) ? $data['Org_Buhgalt'] : null
		);
		//die(getDebugSQL($query, $queryParams));
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			$response = $res->result('array');

			if ( isset($response[0]) && strlen($response[0]['Error_Msg']) == 0 ) {
				$data['Org_id'] = $response[0]['Org_id'];
			}
			else {
				return $response;
			}
		}
		else {
			return false;
		}

		// https://redmine.swan.perm.ru/issues/31050
		if ( !empty($data['OrgStac_Code']) ) {
			// Проверяем код на дубли
			$query = "
				select top 1 OrgStac_id
				from fed.v_OrgStac with (nolock)
				where OrgStac_Code = :OrgStac_Code
					and Org_id != :Org_id
			";
			$resTmp = $this->db->query($query, $data);

			if ( !is_object($resTmp) ) {
				return false;
			}

			$response = $resTmp->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				return array(array('Error_Code' => '347', 'Error_Msg' => 'Указанный код стационарного учреждения уже используется для другой организации'));
			}

			$data['OrgStac_id'] = null;

			// Получаем идентификатор стационарного учреждения для организации
			if ( $procedure_action == 'upd' ) {
				$query = "
					select top 1 OrgStac_id
					from fed.v_OrgStac with (nolock)
					where Org_id = :Org_id
				";
				$resTmp = $this->db->query($query, $data);

				if ( !is_object($resTmp) ) {
					return false;
				}

				$response = $resTmp->result('array');

				if ( is_array($response) && count($response) == 1 && !empty($response[0]['OrgStac_id']) ) {
					$data['OrgStac_id'] = $response[0]['OrgStac_id'];
				}
			}

			$query = "
				declare
					@Reg bigint,
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);

				set @Reg = dbo.getRegion();
				set @Res = :OrgStac_id;

				exec fed.p_OrgStac_" . (!empty($data['OrgStac_id']) ? "upd" : "ins") . "
					@OrgStac_id = @Res output,
					@OrgStac_Code = :OrgStac_Code,
					@Org_id = :Org_id,
					@Region_id = @Reg,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;

				select @Res as OrgStac_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			$resTmp = $this->db->query($query, $data);

			if ( !is_object($resTmp) ) {
				return false;
			}

			$response = $resTmp->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				return false;
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				return $response;
			}
		}
		else if ( $procedure_action == 'upd' ) {
			$query = "
				select OrgStac_id
				from fed.v_OrgStac with (nolock)
				where Org_id = :Org_id
			";
			//echo getDebugSQL($query, array('Org_id' => $data['Org_id']));
			$resTmp = $this->db->query($query, array('Org_id' => $data['Org_id']));

			if ( !is_object($resTmp) ) {
				return false;
			}

			$response = $resTmp->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				$query = "
					declare
						@ErrCode bigint,
						@ErrMsg varchar(4000);

					exec fed.p_OrgStac_del
						@OrgStac_id = :OrgStac_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				foreach ( $response as $array ) {
					$resTmp = $this->db->query($query, array('OrgStac_id' => $array['OrgStac_id']));
					//echo getDebugSQL($query, array('OrgStac_id' => $array['OrgStac_id']));
					if ( !is_object($resTmp) ) {
						return false;
					}

					$respTmp = $resTmp->result('array');

					if ( !is_array($respTmp) || count($respTmp) == 0 ) {
						return false;
					}
					else if ( !empty($respTmp[0]['Error_Msg']) ) {
						return $respTmp;
					}
				}
			}
		}

		// Сохраняем данные, если редактируется OrgDep, OrgFarmacy или OrgSmo
		switch ($data['OrgType_SysNick']) {
			case 'dep':
				if ( !isset($data['OrgDep_id']) ) {
					$procedure_action = "ins";
				}
				else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :OrgDep_id;

					exec p_OrgDep_" . $procedure_action . "
						@Server_id = :Server_id,
						@OrgDep_id = @Res output,
						@Org_id = :Org_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @Res as OrgDep_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$queryParams = array(
					'OrgDep_id' => !isset($data['OrgDep_id'])?NULL:$data['OrgDep_id'],
					'Server_id' => $data['Server_id'],
					'Org_id' => $data['Org_id'],
					'pmUser_id' => $data['pmUser_id']
				);

				$res = $this->db->query($query, $queryParams);
			break;
			
			case 'smo':
				if ( !isset($data['OrgSMO_id']) ) {
					$procedure_action = "ins";
				}
				else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :OrgSMO_id;

					exec p_OrgSMO_" . $procedure_action . "
						@OrgSMO_isDMS = :OrgSMO_isDMS,
						@OrgSMO_RegNomC = :OrgSMO_RegNomC,
						@OrgSMO_RegNomN = :OrgSMO_RegNomN,
						@Orgsmo_f002smocod = :Orgsmo_f002smocod,
						@KLRGN_id = :KLRGNSmo_id,
						@OrgSMO_id = @Res output,
						@Org_id = :Org_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @Res as OrgSMO_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$queryParams = array(
					'OrgSMO_id' => !isset($data['OrgSMO_id'])?NULL:$data['OrgSMO_id'],
					'OrgSMO_isDMS' => $data['OrgSMO_isDMS'],
					'OrgSMO_RegNomC' => $data['OrgSMO_RegNomC'],
					'OrgSMO_RegNomN' => $data['OrgSMO_RegNomN'],
					'Orgsmo_f002smocod' => $data['Orgsmo_f002smocod'],
					'KLRGNSmo_id' => $data['KLRGNSmo_id'],
					'Org_id' => $data['Org_id'],
					'pmUser_id' => $data['pmUser_id']
				);

				$res = $this->db->query($query, $queryParams);
			break;

			case 'farm':
				if ( !isset($data['OrgFarmacy_id']) ) {
					$procedure_action = "ins";
				}
				else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :OrgFarmacy_id;

					exec p_OrgFarmacy_" . $procedure_action . "
						@OrgFarmacy_id = @Res output,
						@Org_id = :Org_id,
						@OrgFarmacy_ACode = :OrgFarmacy_ACode,
						@OrgFarmacy_HowGo = :OrgFarmacy_HowGo,
						@OrgFarmacy_IsEnabled = :OrgFarmacy_IsEnabled,
						@OrgFarmacy_IsFedLgot = :OrgFarmacy_IsFedLgot,
						@OrgFarmacy_IsRegLgot = :OrgFarmacy_IsRegLgot,
						@OrgFarmacy_IsNozLgot = :OrgFarmacy_IsNozLgot,
						@OrgFarmacy_IsFarmacy = :OrgFarmacy_IsFarmacy,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @Res as OrgFarmacy_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$queryParams = array(
					'OrgFarmacy_id' => !isset($data['OrgFarmacy_id'])?NULL:$data['OrgFarmacy_id'],
					'Org_id' => $data['Org_id'],
					'OrgFarmacy_ACode' => $data['OrgFarmacy_ACode'],
					'OrgFarmacy_HowGo' => $data['OrgFarmacy_HowGo'],
					'OrgFarmacy_IsEnabled' => $data['OrgFarmacy_IsEnabled'],
					'OrgFarmacy_IsFedLgot' => $data['OrgFarmacy_IsFedLgot'],
					'OrgFarmacy_IsRegLgot' => $data['OrgFarmacy_IsRegLgot'],
					'OrgFarmacy_IsNozLgot' => $data['OrgFarmacy_IsNozLgot'],
					'OrgFarmacy_IsFarmacy' => $data['OrgFarmacy_IsFarmacy'],
					'pmUser_id' => $data['pmUser_id']
				);

				// echo getDebugSql($query, $queryParams); die();
				$res = $this->db->query($query, $queryParams);
			break;
			
			case 'bank':
				if ( !isset($data['OrgBank_id']) ) {
					$procedure_action = "ins";
				}
				else {
					$procedure_action = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :OrgBank_id;

					exec p_OrgBank_" . $procedure_action . "
						@OrgBank_id = @Res output,
						@Org_id = :Org_id,
						@OrgBank_KSchet = :OrgBank_KSchet,
						@OrgBank_BIK = :OrgBank_BIK,
						@Okved_id = :Okved_id,
						@pmUser_id = :pmUser_id,
						@Server_id = :Server_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @Res as OrgBank_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				$queryParams = array(
					'OrgBank_id' => !isset($data['OrgBank_id'])?NULL:$data['OrgBank_id'],
					'Org_id' => $data['Org_id'],
					'OrgBank_KSchet' => $data['OrgBank_KSchet'],
					'OrgBank_BIK' => $data['OrgBank_BIK'],
					'Okved_id' => $data['Okved_id'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id']
				);

				// echo getDebugSql($query, $queryParams); die();
				$res = $this->db->query($query, $queryParams);
			break;
			default:
				break;
		}
		if ( is_object($res) ) {
			$response = $res->result('array');
			$response[0]['Org_id'] = $data['Org_id'];
			return $response;
		}
		else {
			return false;
		}
	}
	
}