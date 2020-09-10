<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Address_model - модель для работы с адресами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @originalauthor       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      ?
*/
class Address_model4E extends swModel {

    /**
     * Конструктор
     */
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * Определение местоположения подстанции
	 */	
	function getAddressFromLpuBuildingID($data)
	{
		$queryParams = array();
		
		if(!isset($data["LpuBuilding_id"])){
			$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
			$lpuBuilding = $this->CmpCallCard_model4E->getLpuBuildingBySessionData($data);
			//@todo здесь аккуратней
			$queryParams['LpuBuilding_id'] = (!empty($lpuBuilding[0]["LpuBuilding_id"])) ? $lpuBuilding[0]["LpuBuilding_id"] : $lpuBuilding[0];
		}
		else{
			$queryParams['LpuBuilding_id'] = $data["LpuBuilding_id"];
		}

		$queryParams['Org_id'] = $data["session"]["org_id"];
		
		$sql = "
			SELECT TOP 1
				lb.LpuBuilding_setDefaultAddressCity
				from v_LpuBuilding lb
				WHERE lb.LpuBuilding_id = :LpuBuilding_id
		";
		//var_dump(getDebugSQL($sql, $queryParams)); exit;
		$result = $this->db->query($sql, $queryParams);
		
		//Если в настройках подразделения СМП, под которым осуществляется прием вызова (вкладка «Разное» формы «Структура МО» для подразделения СМП), 
		//установлен флаг «При приеме вызова населенный пункт заполнять по умолчанию», то заполняется автоматически по следующим правилам
		$resDefaultAddressCity = $result->result('array');
		if(isset($resDefaultAddressCity[0]) && isset($resDefaultAddressCity[0]["LpuBuilding_setDefaultAddressCity"]) && $resDefaultAddressCity[0]["LpuBuilding_setDefaultAddressCity"] == 2){

			//1.Подставляется значение поля «Населенный пункт» или, если не заполнено, «Город» или, если не заполнено, 
			//	«Территория» адреса Территории обслуживания МО (раздел «Территория обслуживания» вкладки «Обслуживаемое подразделение» Паспорта МО);
			//2.Если в Паспорте МО заведено больше одного адреса Территории обслуживания, то используется первый в списке адрес;
			//3.Если в Паспорте МО не заведено ни одного адреса Территории обслуживания, то подставляется значение поля «Нас. пункт» или, если не заполнено, 
			//	«Город» или, если не заполнено, «Территория»  юридического адреса МО (поле «Юридический адрес» вкладки «Идентификация» Паспорта МО).

			$sql = "
				select top 1
					COALESCE(ost.KLRgn_id, null) as KLRgn_id,
					COALESCE(ost.KLSubRgn_id, null) as KLSubRGN_id,
					COALESCE(ost.KLCity_id, null) as KLCity_id,
					COALESCE(ost.KLTown_id, null) as KLTown_id
				from
					v_OrgServiceTerr ost (nolock)
				where
					ost.Org_id = :Org_id	
			";
			
			$result = $this->db->query($sql, $queryParams);
			
			$rows = $result->result('array');

			if (!is_array($rows) || count($rows) == 0) {
				$sql = "
					SELECT TOP 1
						lb.LpuBuilding_setDefaultAddressCity,
						COALESCE(a.KLRgn_id, null) as KLRgn_id,
						COALESCE(a.KLSubRGN_id, null) as KLSubRGN_id,
						COALESCE(a.KLCity_id, null) as KLCity_id,
						COALESCE(a.KLTown_id, null) as KLTown_id

						from v_LpuBuilding lb
						left join v_lpu l with (nolock) on lb.lpu_id = l.lpu_id
						left join v_address a with (nolock) on l.UAddress_id = a.Address_id
						WHERE lb.LpuBuilding_id = :LpuBuilding_id
				";
				$result = $this->db->query($sql, $queryParams);
			}
		}
		
		if (is_object($result))
		{
			$res = $result->result('array');
			$response['address'] = $res[0];
			$response["setDefaultAddressCity"] = $resDefaultAddressCity[0]["LpuBuilding_setDefaultAddressCity"];

			return $response;
			
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Определение местоположения ЛПУ
	 */
	function getAddressFromLpuID($data)
	{
		$queryParams = array();
		$filter = "1=1";
		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and Lpu_id = :Lpu_id ";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$sql = "
			SELECT TOP 1
				o.KLCountry_id,
                o.KLRGN_id,
                o.KLSubRGN_id,
                o.KLCity_id,
                o.KLTown_id,
				a.KLRgn_id as UrKLRgn_id,
				a.KLSubRGN_id as UrKLSubRGN_id,
                a.KLCity_id as UrKLCity_id,
                a.KLTown_id as UrKLTown_id,
                a.KLAreaStat_id as UrKLStat_id
				from v_lpu l with (nolock)
				left join v_address a with (nolock) on l.UAddress_id = a.Address_id
				left join v_OrgServiceTerr o with (nolock) on o.Org_id = l.Org_id
				WHERE $filter
		";

		$result = $this->db->query($sql, $queryParams);

		if (is_object($result))
		{
			$res = $result->result('array');
			$res = $res[0];
			
			$outputRes = array();
			
			if( isset($res['KLCity_id'])||isset($res['KLTown_id']) ){
				$outputRes = array(
					'KLRGN_id' => $res['KLRGN_id'],
					'KLSubRGN_id' => $res['KLSubRGN_id'],
					'KLCity_id' => $res['KLCity_id'],
					'KLTown_id' => $res['KLTown_id'],
				);				
			}
			elseif( isset($res['UrKLCity_id']) || isset($res['UrKLTown_id']) ){
				$outputRes = array(
					'KLRGN_id' => $res['UrKLRgn_id'],
					'KLSubRGN_id' => $res['UrKLSubRGN_id'],
					'KLCity_id' => $res['UrKLCity_id'],
					'KLTown_id' => $res['UrKLTown_id'],
				);	
			}
			
			return $outputRes;
			//return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Определение территории по имени
	 */
	public function getCitiesFromName( $data ){
		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;
		$concat = $is_pg ? '||' : '+';
		$where = array();
		$queryParams = array();
		$uno = $is_pg ? 'LIMIT 100' : 'TOP 100';
		$LpuBuilding_result = null;
		$hasLpuBuildingAddress = false;
		
		
		
		if(isset($data['LpuBuilding_id'])){
			$this->load->model('LpuStructure_model', 'lpuStrmodel');
			$LpuBuilding_result = $this->lpuStrmodel->getLpuBuildingList($data);

			if ( isset( $LpuBuilding_result[ 0 ] ) && (!empty( $LpuBuilding_result[ 0 ])) ) {
				$data[ 'KLTown_id' ] = $LpuBuilding_result[0]['KLTown_id'];
				$data[ 'KLCity_id' ] = $LpuBuilding_result[0]['KLCity_id'];
				$data[ 'KLRGN_id' ] = $LpuBuilding_result[0]['KLRGN_id'];
				$data[ 'KLSubRGN_id' ] = $LpuBuilding_result[0]['KLSubRGN_id'];
			}
			$hasLpuBuildingAddress = isset($LpuBuilding_result[ 0 ]["Address_id"]); 
		}
		//копать здесь -  не определяется город

		//если указана лпу ид 
		//ищем по фактическому месту лпу
		if( (isset($data['Lpu_id'])) && !$hasLpuBuildingAddress ){
			//var_dump(111, $LpuBuilding_result); exit;
			$lpuAddress = "
				SELECT PAD.KLCity_id, PAD.KLTown_id
				FROM v_Lpu lpu with (nolock)
				LEFT JOIN v_Address PAD (nolock) on PAD.Address_id = lpu.PAddress_id
				WHERE lpu.Lpu_id = :Lpu_id";
				
			$lpuAddressResult = $this->db->query( $lpuAddress, array('Lpu_id' => $data[ 'Lpu_id' ]) );
			
			if ( is_object( $lpuAddressResult ) ) {
				$lpuAddressResult = $lpuAddressResult->result( 'array' );
				if ( isset( $lpuAddressResult[ 0 ] ) && (!empty( $lpuAddressResult[ 0 ])) ) {
					$data[ 'KLTown_id' ] = $lpuAddressResult[0]['KLTown_id'];
					$data[ 'KLCity_id' ] = $lpuAddressResult[0]['KLCity_id'];
					//$data[ 'KLAreaType_id' ] = $lpuAddressResult[0]['KLAreaType_id'];
					//$where[] = "area.\"KLAreaType_id\" = :KLAreaType_id";
				}
			}
		}
		

		$where[] = "area.\"KLAdr_Actual\"=0";

        // Если есть идентификатор, однозначно определяющий местоположение, нет смысла искать по региону
        if ( !(isset($data[ 'KLTown_id' ])) &&  !(isset($data[ 'KLCity_id' ])) ) {

            if ( $data[ 'region_id' ] ) {
                // @todo Так выбирать не правильно, т.к. внешний ключ ссылается
                // на идентификатор, который не совпадает с номером региона.
				if ($data[ 'region_id' ] == '91' || $data[ 'region_id' ] == '92') {
					$where[] = "(area.\"KLAdr_Code\" LIKE '91%' OR area.\"KLAdr_Code\" LIKE '92%' )";
				} elseif ($data[ 'region_id' ] == '101'){
					$where[] = "(area.KLCountry_id = 398 )";
				} else {
					$where[] = "area.\"KLAdr_Code\" LIKE :region_id ".$concat." '%'";
					$queryParams[ 'region_id' ] = str_pad( $data[ 'region_id' ], 2, '0', STR_PAD_LEFT );
				}
            }
			//если определен адрес подстанции пропускаем
			if(!$hasLpuBuildingAddress){
				if ( $data[ 'city_default' ] && $data[ 'region_name' ]) {
					$uno = $is_pg ? 'LIMIT 1' : 'TOP 1';
					$where[] = "area.\"KLArea_Name\" LIKE :region_name";
					$queryParams[ 'region_name' ] = $data[ 'region_name' ];
				} elseif ( $data['city_default'] ) {
					$where[] = " AND area.\"KLAreaCentreType_id\" IN (2,3)";
				} elseif ( $data[ 'query' ] ) {
					$where[] = "area.\"KLArea_Name\" LIKE '%' ".$concat." :city_name ".$concat." '%'";
					$queryParams[ 'city_name' ] = $data[ 'query' ];
				}
			}
           
        }

		// Добавил немного новой логики:
		// У карты вызова есть поля
		// KLRgn_id, KLSubRgn_id, KLCity_id, KLTown_id
		// И в зависимости от того что выбрано ранее, некоторые могут быть
		// заполнены, а некоторые нет. Пока там где просмотр добавил вывод
		// одного города
		if ( $data[ 'KLTown_id' ] ) {
			$where[] = "area.\"KLArea_id\"=:KLTown_id";
			$queryParams[ 'KLTown_id' ] = $data[ 'KLTown_id' ];
		} elseif ( $data[ 'KLCity_id' ] ) {
			$where[] = "area.\"KLArea_id\"=:KLCity_id";
			$queryParams[ 'KLCity_id' ] = $data[ 'KLCity_id' ];
		} elseif ( isset($data[ 'KLSubRGN_id' ]) ) {
			$where[] = "area.\"KLArea_id\"=:KLSubRGN_id";
			$queryParams[ 'KLSubRGN_id' ] = $data[ 'KLSubRGN_id' ];
		} elseif ( isset($data[ 'KLRGN_id' ]) ) {
			$where[] = "area.\"KLArea_id\"=:KLRGN_id";
			$queryParams[ 'KLRGN_id' ] = $data[ 'KLRGN_id' ];
		}

		$sql = "
			SELECT ".$uno."
				area.KLArea_id,
				isnull(area.KLArea_pid,area.KLArea_id) as KLArea_pid,
				area.KLAreaLevel_id,
				area.KLArea_Name,
				socr.KLSocr_Nick,
				socr.KLSocr_Name,
				p.KLArea_Name as pKLArea_Name,
				s.KLSocr_Name as region,
				s.KLSocr_Nick as regionSocr,
				stat.KLAreaStat_id,
				stat.Region_id,
				null as UAD_id
			FROM
				KLArea area with (nolock)
				LEFT JOIN KLSocr socr with (nolock) on (area.KLSocr_id = socr.KLSocr_id)
				LEFT JOIN KLArea p with (nolock) ON ( p.KLArea_id=area.KLArea_pid )
				LEFT JOIN KLSocr s with (nolock) on (p.KLSocr_id = s.KLSocr_id)
				--LEFT JOIN KLAreaStat stat with (nolock) on ( area.KLArea_id = stat.KLCity_id)
				LEFT JOIN KLAreaStat stat with (nolock) on ( p.KLArea_id = stat.KLSubRGN_id)
			WHERE
				".implode( "\nAND ", $where )."
			ORDER BY
				LEN(area.KLArea_Name) ASC,
				socr.KLAreaType_id ASC,
				socr.KLAreaLevel_id DESC,
				area.KLAreaCentreType_id DESC
		";
		
		//var_dump(getDebugSQL($sql, $queryParams)); exit;
		
		$res = $this->db->query( $sql, $queryParams );
		
		if(!empty($data['showUnformalizedAdresses'])){
			$UnformalizedAdressesQuery = "
				SELECT 
					null as KLArea_id,
					null as KLArea_pid,
					null as KLAreaLevel_id,
					UAD.UnformalizedAddressDirectory_Name as KLArea_Name,
					'СМП' as KLSocr_Nick,
					'СМП' as KLSocr_Name,
					null as pKLArea_Name,
					null as region,
					null as regionSocr,
					null as KLAreaStat_id,
					null as Region_id,
					null as KLAreaType_id,
					null as KLAreaCentreType_id,
					UAD.UnformalizedAddressDirectory_id as UAD_id
				FROM v_UnformalizedAddressDirectory UAD with (nolock)
				WHERE UAD.Lpu_id = :Lpu_id
				AND UAD.UnformalizedAddressDirectory_Name LIKE '%' ".$concat." :city_name ".$concat." '%'
			";
			$queryParams[ 'Lpu_id' ] = $data[ 'session' ][ 'lpu_id' ];
			$unfRes = $this->db->query( $UnformalizedAdressesQuery, $queryParams );
			
			if ( is_object( $unfRes ) ) {
				$unfResArray =  $unfRes->result( 'array' );
				$totalRes = array_merge($unfResArray,$res->result( 'array' ));
				
				return $totalRes;
			}
			
		}
		
		if ( is_object( $res ) ) {
			return $res->result( 'array' );
		}

		return false;
	}

	/**
	 * Определение улицы и неформализ. адреса по имени
	 */	
	function getStreetsFromName($data) {
	
		$queryParams = array();
		$filterKL = "KLAdr_Actual = 0";
		$filterUD = "1=1";
		
		if ( $data['town_id']) {
			$filterKL .= " and KLArea_id = :town_id";
			$queryParams['town_id'] = $data['town_id'];
		}
		
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		else {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		
		//разгружаем запрос
		//1- поиск только по тексту запросу
		if ( $data['query'])
			{
				$filterKL .= " and KLStreet_Name LIKE  '%'+ :street_name +'%'";
				$queryParams['street_name'] = $data['query'];
				$filterUD .= " and UAD.UnformalizedAddressDirectory_Name LIKE '%'+ :street_name +'%'";

				$query = "
				SELECT
					'ST.'+CAST([KLStreet].[KLStreet_id] as varchar(8)) as StreetAndUnformalizedAddressDirectory_id,
					RTRIM([KLStreet].[KLStreet_Name]) as StreetAndUnformalizedAddressDirectory_Name,
					[KLSocr].[KLSocr_Nick] as [Socr_Nick],
					null as lat,
					null as lng,
					null as UnformalizedAddressDirectory_id,
					[KLStreet].[KLStreet_id]
				from KLStreet with (nolock)
					left join [KLSocr] with (nolock) on [KLSocr].[KLSocr_id] = [KLStreet].[KLSocr_id]
				WHERE $filterKL
				UNION	
				SELECT
					'UA.'+CAST(UAD.UnformalizedAddressDirectory_id as varchar(20)) as StreetAndUnformalizedAddressDirectory_id,
					UAD.UnformalizedAddressDirectory_Name as StreetAndUnformalizedAddressDirectory_Name,
					'СМП' as Socr_Nick,
					UAD.UnformalizedAddressDirectory_lat as lat,
					UAD.UnformalizedAddressDirectory_lng as lng,
					UAD.UnformalizedAddressDirectory_id,
					null as KLStreet_id
				FROM
					v_UnformalizedAddressDirectory UAD with (nolock)
				WHERE 
					UAD.Lpu_id = :Lpu_id and
					$filterUD
				";
			}
		//2x- поиск по id
		else		
			{
			if ( $data['street_id']) {
					$filterKL .= " and KLStreet_id = :street_id";
					$queryParams['street_id'] = $data['street_id'];
				}

			if ( $data['unf_addr']) {
					//$filterUD .= " and UAD.UnformalizedAddressDirectory_id = :street_id";
					$queryParams['unf_addr'] = $data['unf_addr'];
				}
				//2.1 поиск неформализ. адресов
			if ($data['unf_addr']){
					$query = "
					SELECT
						'UA.'+CAST(UAD.UnformalizedAddressDirectory_id as varchar(20)) as StreetAndUnformalizedAddressDirectory_id,
						UAD.UnformalizedAddressDirectory_Name as StreetAndUnformalizedAddressDirectory_Name,
						'СМП' as Socr_Nick,
						UAD.UnformalizedAddressDirectory_lat as lat,
						UAD.UnformalizedAddressDirectory_lng as lng,
						UAD.UnformalizedAddressDirectory_id,
						null as KLStreet_id
					FROM
						v_UnformalizedAddressDirectory UAD with (nolock)
					WHERE 
						UAD.Lpu_id = :Lpu_id and
						UnformalizedAddressDirectory_id = :unf_addr
					";
				}
				//2.2 поиск обычных адресов
			else{

				$query = "
					SELECT
						'ST.'+CAST([KLStreet].[KLStreet_id] as varchar(8)) as StreetAndUnformalizedAddressDirectory_id,
						RTRIM([KLStreet].[KLStreet_Name]) as StreetAndUnformalizedAddressDirectory_Name,
						[KLSocr].[KLSocr_Nick] as [Socr_Nick],
						null as lat,
						null as lng,
						null as UnformalizedAddressDirectory_id,
						[KLStreet].[KLStreet_id]
					from KLStreet with (nolock)
						left join [KLSocr] with (nolock) on [KLSocr].[KLSocr_id] = [KLStreet].[KLSocr_id]
					WHERE $filterKL
					";
				}						
			}
		$result = $this->db->query($query, $queryParams);
		
		//echo(getDebugSQL($query, $queryParams));

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}	

	
	/**
	 * Список адресов, в т.ч. неформализованных для ЛПУ по ID города
	 *
	 * @param array $data Параметры
	 * @return false or array
	 */	
	public function getAllStreetsFromCity( $data ){
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data[ 'Lpu_id' ] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ' ) );
		}

		if ( $this->db->dbdriver == 'postgre' ) {
			$queryParams = array(
				'Lpu_id' => $data[ 'Lpu_id' ],
			);
			$filterKL = "\"KLAdr_Actual\"=0";
			$filterUnf = "UAD.\"Lpu_id\" = :Lpu_id";

			if ( $data[ 'town_id' ] ) {
				$filterKL .= " and \"KLArea_id\" = :town_id";
				$queryParams[ 'town_id' ] = $data[ 'town_id' ];
			}

			if ( $data[ 'UnformalizedAddressType_id' ] ) {
				$filterUnf .= " and UAD.\"UnformalizedAddressType_id\" = :UnformalizedAddressType_id";
				$filterKL .= " and 1=2";
				$queryParams[ 'UnformalizedAddressType_id' ] = $data[ 'UnformalizedAddressType_id' ];
			}

			$query = "
				SELECT
					'ST.' || KLStreet.\"KLStreet_id\" as \"StreetAndUnformalizedAddressDirectory_id\",
					RTRIM(KLStreet.\"KLStreet_Name\") as \"StreetAndUnformalizedAddressDirectory_Name\",
					KLSocr.\"KLSocr_Nick\" as \"Socr_Nick\",
					'' as \"lat\",
					'' as \"lng\",
					null as \"UnformalizedAddressDirectory_id\",
					null as \"UnformalizedAddressType_id\",
					(SELECT count(1) FROM dbo.\"KLHouse\" WHERE \"KLStreet_id\"=KLStreet.\"KLStreet_id\") as cnt,
					KLStreet.\"KLStreet_id\"
				FROM
					dbo.\"KLStreet\" as KLStreet with(nolock)
					left join dbo.\"KLSocr\" as KLSocr with(nolock) on KLSocr.\"KLSocr_id\"=KLStreet.\"KLSocr_id\"
				WHERE
					{$filterKL}
				UNION
				SELECT
					'UA.' || UAD.\"UnformalizedAddressDirectory_id\" as \"StreetAndUnformalizedAddressDirectory_id\",
					UAD.\"UnformalizedAddressDirectory_Name\" as \"StreetAndUnformalizedAddressDirectory_Name\",
					'СМП' as \"Socr_Nick\",
					CAST(UAD.UnformalizedAddressDirectory_lat as varchar(20)) as lat,
					CAST(UAD.UnformalizedAddressDirectory_lng as varchar(20)) as lng,
					UAD.\"UnformalizedAddressDirectory_id\",
					UAD.\"UnformalizedAddressType_id\",
					null as \"cnt\",
					null as \"KLStreet_id\"
				FROM
					dbo.\"v_UnformalizedAddressDirectory\" UAD with(nolock)
				WHERE
					{$filterUnf}
				ORDER BY
					\"cnt\" DESC,
					\"StreetAndUnformalizedAddressDirectory_Name\"
			";
		} else {
			$queryParams = array(
				'Lpu_id' => $data[ 'Lpu_id' ],
			);
			$filterKL = "klStreet.KLAdr_Actual = 0";
			$filterUnf = 'UAD.Lpu_id = :Lpu_id';

			if ( $data[ 'town_id' ] ) {
				$filterKL .= " and( klArea.KLArea_id = :town_id or klArea.KLArea_pid = :town_id )";
				$queryParams[ 'town_id' ] = $data[ 'town_id' ];
			}

			if ( $data[ 'UnformalizedAddressType_id' ] ) {
				$filterUnf .= " and UAD.UnformalizedAddressType_id = :UnformalizedAddressType_id";
				$filterKL .= " and 1=2";
				$queryParams[ 'UnformalizedAddressType_id' ] = $data[ 'UnformalizedAddressType_id' ];
			}

			$query = "
				SELECT					
					'ST.'+CAST(klStreet.KLStreet_id as varchar(20)) as StreetAndUnformalizedAddressDirectory_id,
					'' as LpuBuilding_id,
					--pklArea.KLArea_FullName as AddressOfTheObject,
					CASE WHEN ppklArea.KLAreaLevel_id IS NULL THEN COALESCE(ppklArea.KLArea_FullName+', ', '') else '' END +
                    	COALESCE(pklArea.KLArea_FullName+', ', '') +
                        COALESCE(klArea.KLArea_FullName, '') 
					as AddressOfTheObject,			
					replace(RTRIM(klStreet.KLStreet_Name), 'ё', 'е') as StreetSearch_Name,					
					RTRIM(klStreet.KLStreet_Name) as StreetAndUnformalizedAddressDirectory_Name,
					klSocr.KLSocr_Nick as Socr_Nick,
					null as lat,
					null as lng,
					null as UnformalizedAddressDirectory_id,
					null as UnformalizedAddressType_id,
					klArea.KLArea_FullName+', ' as Address_Name,
					klStreet.KLStreet_id,
					ppklArea.KLArea_id as KLRGN_id,
					pklArea.KLArea_id as KLSubRGN_id,
                    klArea.KLArea_id as KLTown_id,					
					(SELECT count(1) FROM v_KLHouse with (nolock) WHERE KLStreet_id = KLStreet.KLStreet_id) as cnt,
					null as UnformalizedAddressDirectory_StreetDom
				from v_KLArea as klArea with (nolock)
					left join v_KLStreet as klStreet with (nolock) on klArea.KLArea_id = KLStreet.KLArea_id
					left join v_KLSocr as klSocr with (nolock) on klSocr.KLSocr_id = klStreet.KLSocr_id
					left join v_KLArea pklArea (nolock) on klArea.KLArea_pid = pklArea.KLArea_id
					left join v_KLArea ppklArea (nolock) on pklArea.KLArea_pid = ppklArea.KLArea_id
					left join v_KLCountry klCountry (nolock) on klArea.KLCountry_id = klCountry.KLCountry_id
				WHERE $filterKL
				UNION
				SELECT
					'UA.'+CAST(UAD.UnformalizedAddressDirectory_id as varchar(20)) as StreetAndUnformalizedAddressDirectory_id,
					LB.LpuBuilding_id as LpuBuilding_id,
					CASE WHEN rgnArea.KLArea_FullName is not null THEN rgnArea.KLArea_FullName ELSE '' END +
					CASE WHEN cityArea.KLArea_FullName is not null THEN ', '+cityArea.KLArea_FullName ELSE '' END +
					CASE WHEN townArea.KLArea_FullName is not null THEN ', '+townArea.KLArea_FullName ELSE '' END 					
					as AddressOfTheObject,					
					UAD.UnformalizedAddressDirectory_Name as StreetAndUnformalizedAddressDirectory_Name,
					replace(RTRIM(UAD.UnformalizedAddressDirectory_Name), 'ё', 'е') as StreetSearch_Name,
					COALESCE(LPU.Lpu_Nick, LPU.Lpu_Name, '') + COALESCE(' / '+LB.LpuBuilding_Nick, ' / '+LB.LpuBuilding_Name, '') as Socr_Nick,
					CAST(UAD.UnformalizedAddressDirectory_lat as varchar(20)) as lat,
					CAST(UAD.UnformalizedAddressDirectory_lng as varchar(20)) as lng,
					UAD.UnformalizedAddressDirectory_id,
					UAD.UnformalizedAddressType_id,
					'' as Address_Name,
					null as KLStreet_id,
					UAD.KLRgn_id as KLRGN_id,
					UAD.KLSubRgn_id as KLSubRGN_id,
					COALESCE(UAD.KLTown_id, UAD.KLCity_id) as KLTown_id,
					null as cnt,
					case when Street.KLStreet_FullName is not null then 'ул.'+Street.KLStreet_Name else '' end +
					case when UAD.UnformalizedAddressDirectory_Dom is not null AND UAD.UnformalizedAddressDirectory_Dom != ' ' then ', д.'+UAD.UnformalizedAddressDirectory_Dom else '' end  as UnformalizedAddressDirectory_StreetDom
				FROM
					v_UnformalizedAddressDirectory UAD with (nolock)
					left join v_Lpu LPU with (nolock) on UAD.Lpu_id = LPU.Lpu_id
                    left join v_LpuBuilding LB with (nolock) on UAD.LpuBuilding_id = LB.LpuBuilding_id
					left join v_KLArea rgnArea (nolock) on UAD.KLRgn_id = rgnArea.KLArea_id
					left join v_KLArea cityArea (nolock) on UAD.KLCity_id = cityArea.KLArea_id
					left join v_KLArea townArea (nolock) on UAD.KLTown_id = townArea.KLArea_id
					left join v_KLStreet Street (nolock) on Street.KLStreet_id = UAD.KLStreet_id
				WHERE $filterUnf
				ORDER BY cnt DESC, StreetAndUnformalizedAddressDirectory_Name
			";
		}
		
		//var_dump(getDebugSQL($query, $queryParams)); exit;
		
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}	

	/**
	 * Получение списка областей
	 */
	public function getRegions($data)
	{
		$rules = array(
			array('field' => 'country_id', 'label' => 'Идентификатор страны','rules' => 'required','type' => 'id')
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = "
			SELECT
				kla.KLArea_id as Region_id,
				kla.KLArea_Name + ' ' + COALESCE(S.KLSocr_Nick,'') as Region_Name,
				S.KLSocr_id as Socr_id
			FROM
				KLArea kla with (nolock)
				left join v_KLSocr S with (nolock) on kla.KLSocr_id = S.KLSocr_id
			WHERE
				kla.KLAdr_Actual=0
				AND kla.KLCountry_id= :country_id
				AND kla.KLAreaLevel_id=1
		";
		
		return $this->queryResult($query, $queryParams);
	}


	/**
	 * Получение списка районов
	 */
	public function getSubRegions($data)
	{
		$rules = array(
			array('field' => 'region_id','label' => 'Идентификатор региона','rules' => 'required','type' => 'id')
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = "
			SELECT
				kla.KLArea_id as SubRGN_id,
				kla.KLArea_Name + ' ' + COALESCE(S.KLSocr_Nick,'') as SubRGN_Name,
				S.KLSocr_id as Socr_id
			FROM
				KLArea kla with (nolock)
				left join v_KLSocr S with (nolock) on kla.KLSocr_id = S.KLSocr_id
			WHERE
				kla.KLAdr_Actual=0
				AND kla.KLArea_pid= :region_id
				AND kla.KLAreaLevel_id=2
		";
		
		return $this->queryResult($query, $queryParams);
	}


	/**
	 * Получение списка городов
	 */
	public function getCities($data)
	{
		$rules = array(
			array('field' => 'subregion_id','label' => 'Идентификатор региона','rules' => '','type' => 'id')
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = "
			SELECT
				kla.KLArea_id as City_id,
				kla.KLArea_Name + ' ' + COALESCE(S.KLSocr_Nick,'') as City_Name,
				S.KLSocr_id as Socr_id
			FROM
				KLArea kla with (nolock)
				left join v_KLSocr S with (nolock) on kla.KLSocr_id = S.KLSocr_id
			WHERE
				kla.KLAdr_Actual=0
				AND (
               		(kla.KLArea_pid = :subregion_id AND kla.KLAreaLevel_id=3)
                	OR
                    (kla.KLArea_id = :subregion_id AND kla.KLAreaCentreType_id=5 )
                )
		";
		
		return $this->queryResult($query, $queryParams);
	}


	/**
	 * Получение списка населенных пунктов
	 */
	public function getTowns($data){
		$rules = array(
			array('field' => 'city_id','label' => 'Идентификатор города','rules' => '','type' => 'id')
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = "
			SELECT
				kla.KLArea_id as Town_id,
				kla.KLArea_Name + ' ' + COALESCE(S.KLSocr_Nick,'') as Town_Name,
				S.KLSocr_id as Socr_id
			FROM
				KLArea kla with (nolock)
				left join v_KLSocr S with (nolock) on kla.KLSocr_id = S.KLSocr_id
			WHERE
				kla.KLAdr_Actual=0
				AND kla.KLArea_pid= :city_id
				AND kla.KLAreaLevel_id=4
		";
		
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение списка улиц для комбобокса 
	 */
	public function getStreets($data)
	{
		$rules = array(
			array('field' => 'town_id','label' => 'Идентификатор города','rules' => '','type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		$query = "
			select
				kls.KLStreet_id as Street_id,
				kls.KLSocr_id as Socr_id,
				kls.KLStreet_Name + ' ' + COALESCE(S.KLSocr_Nick,'') as Street_Name
			from 
				v_KLStreet kls with (nolock)
				inner join v_KLSocr S with (nolock) on S.KLSocr_id = kls.KLSocr_id
			where 
				kls.KLAdr_Actual = 0 and
				kls.KLArea_id = :town_id
		";
		
		
		return $this->queryResult($query, $queryParams);
	}

}