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
class Address_model extends SwPgModel {

	/**
	 * @var array Конфиг перевода запросов на MongoDb
	 */
	protected $mongo_switch;

	/**
	 * @var array Правила для входящих параметров для API 
	 */
	public $inputRules = array(
		'loadAddress' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'AddressType_id', 'label' => 'Тип адреса', /* (1 - Адрес регистрации, 2 - Адрес проживания, 3 - Адрес рождения) */ 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Address_id', 'label' => 'Идентификатор человека', 'rules' => 'trim', 'type' => 'id'),
		),
		'createAddress' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'AddressType_id', 'label' => 'Тип адреса', /* (1 - Адрес регистрации, 2 - Адрес проживания, 3 - Адрес рождения) */ 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Address_Zip', 'label' => 'Индекс', 'rules' => 'trim', 'type' => 'string' ),
			array('field' => 'KLCountry_id', 'label' => 'Страна', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KLRgn_id', 'label' => 'Регион', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLSubRgn_id', 'label' => 'Район', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'KLCity_id', 'label' => 'Город', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'KLTown_id', 'label' => 'Нас. Пункт', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'KLStreet_id', 'label' => 'Улица', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_House', 'label' => 'Дом', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'Address_Corpus', 'label' => 'Корпус', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Address_Flat', 'label' => 'Квартира', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'AoidArea', 'label' => 'Идентификатор адресного объекта (населенного пункта, города) по ФИАС', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'AoidStreet', 'label' => 'Идентификатор адресного объекта (улицы) по ФИАС', 'rules' => 'trim', 'type' => 'string'),
		),
		'updateAddress' => array(
			array('field' => 'Address_id', 'label' => 'Идентификатор адреса', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'AddressType_id', 'label' => 'Тип адреса', /* (1 - Адрес регистрации, 2 - Адрес проживания, 3 - Адрес рождения) */ 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Address_Zip', 'label' => 'Индекс', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'KLCountry_id', 'label' => 'Страна', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'KLRgn_id', 'label' => 'Регион', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'KLSubRgn_id', 'label' => 'Район', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'KLCity_id', 'label' => 'Город', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'KLTown_id', 'label' => 'Нас. Пункт', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'KLStreet_id', 'label' => 'Улица', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'Address_House', 'label' => 'Дом', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Address_Corpus', 'label' => 'Корпус', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Address_Flat', 'label' => 'Квартира', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'AoidArea', 'label' => 'Идентификатор населенного пункта по ФИАС', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'AoidStreet', 'label' => 'Идентификатор улицы по ФИАС', 'rules' => 'trim', 'type' => 'string'),
		),
        'loadChildLists' => array(
            array(
                'field' => 'KLArea_id',
                'label' => 'Идентификатор KLArea',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'KLAreaLevel_id',
                'label' => 'Идентификатор KLAreaLevel',
                'rules' => '',
                'type' => 'id'
            )
        ),
        'searchAddress' => array(
            array(
                'field' => 'query',
                'label' => 'Строка поиска',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'KLCountry_id',
                'label' => 'Страна',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'KLRgn_id',
                'label' => 'Регион',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'mGetLpuStreetsByLpuRegion' => array(
            array(
                'field' => 'Lpu_id',
                'label' => 'Идентификатор ЛПУ',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
	);

    /**
     * Конструктор
     */
	public function __construct(){
		parent::__construct();
		if ( extension_loaded( 'mongo' ) ) {
			$this->load->library('swMongodb');
			$this->load->library('swMongoExt');
			$this->load->helper('MongoDB');

			$this->config->load( 'mongodb', true );
			$this->mongo_switch = $this->config->item('mongo_switch');
		}
	}

    /**
     * Получение обслуживаемых улиц по участкам ЛПУ
     */
    function getLpuServedStreets($data) {

        $response = array(
            'towns' => array(),
            'cities' => array()
        );

        $regions = $this->queryResult("
			select distinct  
				LpuRegion_id as \"LpuRegion_id\"
			from v_LpuRegion lr
			where (1=1)
				and lr.LpuRegion_endDate is null
				and lr.Lpu_id = :Lpu_id
			limit 5000
		", $data);

        if (!empty($regions)) {

            $this->load->model('LpuStructure_model');
            $areas = $this->LpuStructure_model->getLpuRegionStreet(array(
                'LpuRegion_list' => implode(',', array_column($regions,'LpuRegion_id'))
            ));

            if (!empty($areas)) {
                foreach ($areas as $address) {
                    if (!empty($address['KLTown_id']) && !empty($address['KLStreet_id'])){

                        if (!isset($response['towns'][$address['KLTown_id']])) {
                            $response['towns'][$address['KLTown_id']] =  array(
                                'KLTown_Name' => $address['KLTown_Name'],
                                'KLTown_id' => $address['KLTown_id'],
                                'KLCity_id' => $address['KLCity_id'],
                                'streets' => array(),
                                'filled' => false
                            );
                        }

                        $working_array = &$response['towns'][$address['KLTown_id']];

                        if (!isset($working_array['streets'][$address['KLStreet_id']])) {
                            $working_array['streets'][$address['KLStreet_id']] = array(
                                'Street_id' => $address['KLStreet_id'],
                                'Street_Name' => $address['KLStreet_Name'],
                                'Address_Name' => $address['KLRgn_FullName'].', '.$address['KLTown_Name'].', '.$address['KLStreet_Name']
                            );
                        }

                    } else if (!empty($address['KLTown_id']) && empty($address['KLStreet_id'])) {

                        if (!isset($response['towns'][$address['KLTown_id']])
                            || (!empty($response['towns'][$address['KLTown_id']])
                                && $response['towns'][$address['KLTown_id']]['filled'] === false
                            )
                        ) {

                            // нагребем улицы населенного пункта
                            $town_streets = $this->dbmodel->getStreetsList(array(
                                'town_id' => $address['tKLArea_id'],
                                'showSocr' => 1,
                                'fromMobile' => true
                            ));

                            if (!empty($town_streets)) {

                                foreach ($town_streets as &$str) {
                                    $str['Address_Name'] = $address['KLRgn_FullName'].', '.$address['KLTown_Name'].', '.$str['Street_Name'];
                                }

                                $response['towns'][$address['KLTown_id']] = array(
                                    'KLTown_Name' => $address['KLTown_Name'],
                                    'KLTown_id' => $address['KLTown_id'],
                                    'KLCity_id' => $address['KLCity_id'],
                                    'streets' => $town_streets,
                                    'filled' => true
                                );
                            }
                        }

                    } else if (empty($address['KLTown_id']) && !empty($address['KLStreet_id']) && !empty($address['KLCity_id'])) {

                        if (!isset($response['cities'][$address['KLCity_id']])) {
                            $response['cities'][$address['KLCity_id']] = array(
                                'KLTown_Name' => $address['KLTown_Name'],
                                'KLTown_id' => $address['KLTown_id'],
                                'KLCity_id' => $address['KLCity_id'],
                                'streets' => array()
                            );
                        }

                        $working_array = &$response['cities'][$address['KLCity_id']];
                        if (!isset($working_array['streets'][$address['KLStreet_id']])) {
                            $working_array['streets'][$address['KLStreet_id']] = array(
                                'Street_id' => $address['KLStreet_id'],
                                'Street_Name' => $address['KLStreet_Name'],
                                'Address_Name' => $address['KLRgn_FullName'].', '.$address['KLTown_Name'].', '.$address['KLStreet_Name']
                            );
                        }
                    }
                }
            }
        }

        $resp = array_merge($response['towns'], $response['cities']);

        foreach ($resp as &$towns){
            if (!empty($towns['streets'])) {
                $towns['streets'] = array_values($towns['streets']);
            }

            if (isset($towns['filled'])) unset($towns['filled']);
        }

        return $resp;
    }

    /**
     * Получение ОКАТО по адресу
     */
	function loadOkatoField($data)
	{
		$KLAdr_Ocatd = "";
		
		$query = "
			SELECT
				KLAreaLevel_id as\"KLAreaLevel_id\",
				KLAdr_Ocatd as \"KLAdr_Ocatd\"
			FROM
				v_KLArea
			WHERE
				KLArea_id IN (:KLRGN_id, :KLSubRGN_id, :KLCity_id, :KLTown_id)
				and KLAdr_Ocatd is not null
				
			UNION ALL
			
			SELECT
				5 AS \"KLAreaLevel_id\",
				KLAdr_Ocatd as \"KLAdr_Ocatd\"
			FROM
				KLStreet
			WHERE
				KLStreet_id = :KLStreet_id
				and KLAdr_Ocatd is not null
				
			ORDER BY
				\"KLAreaLevel_id\" DESC
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$KLAdr_Ocatd = $resp[0]['KLAdr_Ocatd'];
			}
		}
		
		return array('success' => true, 'KLAdr_Ocatd' => $KLAdr_Ocatd);
	}
	
	/**
	* Получение массива домой из текстового представления
	*/
	function getHouseArray($arr)
	{
		$arr = trim($arr);
		//print $arr.": ";
		if (preg_match( "/^([Ч|Н])\((\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)\)$/iu", $arr, $matches))
		{
			// Четный или нечетный 
			$matches[count($matches)] = 1;
			return $matches;
		}
		elseif (preg_match( "/^([\s]?)(\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)$/iu", $arr, $matches))
		{
			// Обычный диапазон
			$matches[count($matches)] = 2;
			return $matches;
		}
		elseif (preg_match( "/^(\d+[а-яА-Я]?[\/]?\d{0,3}[а-яА-Я]?(\s[к]\d{0,3})?)$/iu", $arr, $matches))
		{
			//print $arr." ";
			if (preg_match( "/^(\d+)/i", $matches[1], $ms))
			{
				$matches[count($matches)] = $ms[1];
			}
			else 
			{
				$matches[count($matches)] = '';
			}
			$matches[count($matches)] = 3;
			return $matches;
		}
		return array();
	}
	
	/**
	 * @return array
	 */
	function loadAddressSpecObject($data){
		$filter='';
		$params=array();
		
		if(isset($data['query'])&&$data['query']!=''){
			$filter .=" and AddressSpecObject_Name ilike :query";
			$params['query']="%".$data['query']."%";
		}
		
		$sql = "
			select 
				AddressSpecObject_id as \"AddressSpecObject_id\",
				AddressSpecObject_Name as \"AddressSpecObject_Name\"
			from
				AddressSpecObject
			where (1=1){$filter}
		";
			//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if (is_object($result))
        {
			return $result->result('array');
		}else{
			return false;
		}
	}
	
	/**
	* Получение идентификатора района по номеру дома и идентификатору улицы
	*/
	function getSprTerrDopByStreetAndHouse($data)
	{
		// получаем данные об улице
		$sql = "
			select 
				pstd.PersonSprTerrDop_id as \"PersonSprTerrDop_id\",
				klh.KLHouse_Name as \"KLHouse_Name\",
				klh.KLAdr_Ocatd as \"KLAdr_Ocatd\"
			from
				KLHouse klh
				inner join PersonSprTerrDop pstd on klh.KLStreet_id = ? and pstd.KLAdr_Ocatd = klh.KLAdr_Ocatd
		";
		$result = $this->db->query($sql, array($data['street_id']));
        if (is_object($result))
        {
            $sel = $result->result('array');
			if ( count($sel) > 0 )
			{
				$data['house'] = preg_replace('/[А-Яа-яa-zA-Z]+/iu', '', $data['house']);
				foreach ($sel as $row)
				{
					$houses = explode(',', $row['KLHouse_Name']);
					foreach( $houses as $house )
					{
						$hr = $this->getHouseArray($house);

						if ( is_array($hr) && count($hr) > 0 ) {
							// если четный диапазон
							if ( $hr[1] == 'Ч' && !$data['house']%2==0 && $data['house'] >= $hr[2] && $data['house'] <= $hr[4] )
								return $row['PersonSprTerrDop_id'];
							
							// если не четный диапазон
							if ( $hr[1] == 'Н' && $data['house']%2<>0 && $data['house'] >= $hr[2] && $data['house'] <= $hr[4] )
								return $row['PersonSprTerrDop_id'];
							
							// обычный диапазон
							if ( $hr[count($hr)-1] == 2 && $data['house'] >= $hr[2] && $data['house'] <= $hr[4] )
							{
								return $row['PersonSprTerrDop_id'];
							}
							// один дом
							if ( $hr[count($hr)-1] == 3 && $data['house'] == $hr[0] )
								return $row['PersonSprTerrDop_id'];
						}
					}
				}
			}
        }
		return false;
	}

    /**
     *  Поиск индекса по улице
     */
    function getZipAddressByStreetAndHome($data)
    {
        //  Если переданы улица и дом
        if (isset($data['street_id']) && isset($data['house']))  {

            //  получаем данные об индексе
            $sql = "
                select
                    KLAdr_Index as \"KLAdr_Index\",
                    KLHouse_Name as \"KLHouse_Name\"
                from
                    KLHouse
                where
                    KLAdr_Index is not null
                    and KLHouse_Name is not null
                    and KLStreet_id = ".$data['street_id']."
            ";

            //echo getDebugSQL($sql, array($data['street_id'])); exit;
            $result = $this->db->query($sql, array($data['street_id']));

            if (is_object($result))
            {
                $sel = $result->result('array');
                if ( count($sel) > 0 )
                {
                    $data['house'] = preg_replace('/[А-Яа-яa-zA-Z]+/iu', '', $data['house']);
                    foreach ($sel as $row)
                    {
                        $houses = explode(',', $row['KLHouse_Name']);
                        foreach( $houses as $house )
                        {
                            $hr = $this->getHouseArray($house);
                            if (count($hr) > 0) {
                                // если четный диапазон
                                if ( $hr[1] == 'Ч' && !$data['house']%2==0 && $data['house'] >= $hr[2] && $data['house'] <= $hr[4] )
                                    return $row['KLAdr_Index'];

                                // если не четный диапазон
                                if ( $hr[1] == 'Н' && $data['house']%2<>0 && $data['house'] >= $hr[2] && $data['house'] <= $hr[4] )
                                    return $row['KLAdr_Index'];

                                // обычный диапазон
                                if ( $hr[count($hr)-1] == 2 && $data['house'] >= $hr[2] && $data['house'] <= $hr[4] )
                                    return $row['KLAdr_Index'];

                                // один дом
                                if ( $hr[count($hr)-1] == 3 && $data['house'] == $hr[0] )
                                    return $row['KLAdr_Index'];
                            }
                        }
                    }
                } else {//Если индекс не найден в таблице KLHouse - ищем в таблице KLStreet
                    $sql = "
                        select
                            KLAdr_Index as \"KLAdr_Index\"
                        from
                            KLStreet
                        where
                            KLAdr_Index is not null
                            and KLStreet_id = ".$data['street_id']."
                    ";

                    $result = $this->db->query($sql, array($data['street_id']));

                    if (is_object($result))
                    {
                        $response =  $result->result('array');
                        if (!empty($response[0]['KLAdr_Index'])) {
                            return $response[0]['KLAdr_Index'];
                        }
                    }
                }
            }
        }

        if (isset($data['town_id'])) { //  Если передан населенный пункт, то ищем по нему

            $sql = "
                select
                    KLAdr_Index as \"KLAdr_Index\"
                from
                    KLArea
                where
                    KLAdr_Index is not null
                    and KLArea_id = ".$data['town_id']."
                ";

            //echo getDebugSQL($sql, array($data['town_id'])); exit;
            $result = $this->db->query($sql, array($data['town_id']));

            if (is_object($result))
            {
                $response =  $result->result('array');
                if (!empty($response[0]['KLAdr_Index'])) {
                    return $response[0]['KLAdr_Index'];
                }
            }
        }

        return false;
    }

    /**
	 * Поиск населенного пункта
	 */
	function searchKLTown($data)
	{
		$queryParams = array();
		$filter = "";
		if ( $data['KLCity_id'] > 0 ) {
			$filter .= " and KLCity.KLArea_id = :KLCity_id ";
			$queryParams['KLCity_id'] = $data['KLCity_id'];
		}
		else
		{
			if ( $data['KLSubRegion_id'] > 0 ) {
				$filter .= " and KLSubRegion.KLArea_id = :KLSubRegion_id ";
				$queryParams['KLSubRegion_id'] = $data['KLSubRegion_id'];
			}
		}
		if ( $data['KLRegion_id'] > 0 ) {
			$filter .= " and KLRegion.KLArea_id = :KLRegion_id ";
			$queryParams['KLRegion_id'] = $data['KLRegion_id'];
		}			
		$queryParams['KLTown_Name'] = "%".$data['KLTown_Name']."%";
		$sql = "SELECT 
					coalesce(KLRegion.KLArea_id, 0) as \"KLRegion_id\",
					coalesce(KLSubRegion.KLArea_id, 0) as \"KLSubRegion_id\",
					coalesce(KLCity.KLArea_id, 0) as \"KLCity_id\",
					KLTown.KLArea_id as \"KLTown_id\",
					KLTown.KLCountry_id as \"KLCountry_id\",
					KLTown.KLArea_Name as \"KLTown_Name\",
					KLCity.KLArea_Name as \"KLCity_Name\",
					KLSocr.KLSocr_Name as \"KLTown_Socr\",
					CASE WHEN KLCity.KLArea_id is not null
					THEN
						KLCity.KLArea_Name
					ELSE
						KLSubRegion.KLArea_Name 
					END as \"KLSubRegionCity_Name\",
					KLRegion.KLArea_Name as \"KLRegion_Name\"
				FROM
					KLArea KLTown
					LEFT JOIN KLArea KLCity ON KLCity.KLAreaLevel_id=3 and KLTown.KLArea_pid = KLCity.KLArea_id
					LEFT JOIN KLArea KLSubRegion ON KLSubRegion.KLAreaLevel_id = 2 and ( (KLCity.KLArea_pid = KLSubRegion.KLArea_id ) or ( KLTown.KLArea_pid = KLSubRegion.KLArea_id) )
					LEFT JOIN KLArea KLRegion ON KLRegion.KLAreaLevel_id = 1 and ( (KLSubRegion.KLArea_pid = KLRegion.KLArea_id ) or (KLCity.KLArea_pid = KLRegion.KLArea_id ) or ( KLTown.KLArea_pid = KLRegion.KLArea_id ) )
					LEFT JOIN KLSocr on KLSocr.KLSocr_id = KLTown.KLSocr_id
				WHERE
					KLTown.KLAdr_Actual = 0 and
					KLTown.KLAreaLevel_id = 4 and
					KLTown.KLArea_Name ILIKE :KLTown_Name
					".$filter
					." LIMIT 200"
					;
                					
					
		$result = $this->db->query($sql, $queryParams);
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
	 * Поиск города
	 */
	function searchKLCity($data)
	{
		$queryParams = array();
		$filter = "";
		if ( $data['KLSubRegion_id'] > 0 ) {
			$filter .= " and KLSubRegion.KLArea_id = :KLSubRegion_id ";
			$queryParams['KLSubRegion_id'] = $data['KLSubRegion_id'];
		}
		if ( $data['KLRegion_id'] > 0 ) {
			$filter .= " and KLRegion.KLArea_id = :KLRegion_id ";
			$queryParams['KLRegion_id'] = $data['KLRegion_id'];
		}
		$queryParams['KLCity_Name'] = "%".$data['KLCity_Name']."%";
		$sql = "SELECT 
					coalesce(KLRegion.KLArea_id, 0) as \"KLRegion_id\",
					coalesce(KLSubRegion.KLArea_id, 0) as \"KLSubRegion_id\",
					KLCity.KLArea_id as \"KLCity_id\",
					KLCity.KLCountry_id as \"KLCountry_id\",
					KLCity.KLArea_Name as \"KLCity_Name\",
					KLCity.KLArea_Name as \"KLCity_Name\",
					KLSocr.KLSocr_Name as \"KLCity_Socr\",
					KLSubRegion.KLArea_Name as \"KLSubRegion_Name\",
					KLRegion.KLArea_Name as \"KLRegion_Name\"
				FROM
					KLArea KLCity
					LEFT JOIN KLArea KLSubRegion ON KLSubRegion.KLAreaLevel_id = 2 and ( (KLCity.KLArea_pid = KLSubRegion.KLArea_id ) or ( KLCity.KLArea_pid = KLSubRegion.KLArea_id) )
					LEFT JOIN KLArea KLRegion ON KLRegion.KLAreaLevel_id = 1 and ( (KLSubRegion.KLArea_pid = KLRegion.KLArea_id ) or (KLCity.KLArea_pid = KLRegion.KLArea_id ) or ( KLCity.KLArea_pid = KLRegion.KLArea_id ) )
					LEFT JOIN KLSocr on KLSocr.KLSocr_id = KLCity.KLSocr_id
				WHERE
					KLCity.KLAdr_Actual = 0 and
					KLCity.KLAreaLevel_id = 3 and
					KLCity.KLArea_Name ILIKE :KLCity_Name "
					.$filter
					." LIMIT 200";
					
		$result = $this->db->query($sql, $queryParams);
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
	 * Получение всех низлежащих списков
	 */
	function loadChildLists($data)
	{
		$sql = "";
		if ( $data['KLAreaLevel_id'] == 0 )
		{
			$sql = "
					SELECT
						KLArea_id as \"KLArea_id\",
						KLSocr_id as \"KLSocr_id\",
						KLArea_Name as \"KLArea_Name\",
						KLAreaLevel_id as \"KLAreaLevel_id\",
						'' as \"KLAdr_Ocatd\",
						'' as \"KLSocr_Nick\"
					FROM
						KLArea
					WHERE
						KLAreaLevel_id = 1 and
						KLAdr_Actual = 0 and
						KLCountry_id = :KLArea_id
					UNION ALL
					SELECT
						KLArea_id as \"KLArea_id\",
						KLSocr_id as \"KLSocr_id\",
						KLArea_Name as \"KLArea_Name\",
						KLAreaLevel_id as \"KLAreaLevel_id\",
						'' as \"KLAdr_Ocatd\",
						'' as \"KLSocr_Nick\"
					FROM
						KLArea
					WHERE
						KLAreaLevel_id = 2 and
						KLAdr_Actual = 0 and
						KLCountry_id = :KLArea_id
			";
		}
		else
		{
            $select1 = ''; $select2 = '';
            $from = '';
            $where = ' kla.KLArea_id = :KLArea_id ';
            if(!empty($data['Form']) && $data['Form'] == 'swAddressEditWindow'){
                $select1 = "
					,null AS \"KLAreaLevel_pid\"
					,null AS \"KLArea_pid\"
					,'' AS \"AreaPID_Name\"
				";
                $select2 = "
					,cast (klapid.KLAreaLevel_id as text) AS \"KLAreaLevel_pid\"
					,cast (klapid.KLArea_id as text) AS \"KLArea_pid\"
					,klapid.KLArea_Name || ' ' || klspid.KLSocr_Nick AS \"AreaPID_Name\"
				";
                $from = "
					LEFT JOIN KLArea klapid ON klapid.KLArea_id = kla.KLArea_id
					left join KLSocr klspid on klapid.KLSocr_id = klspid.KLSocr_id
				";
                if($data['KLAreaLevel_id'] == 3){
                    //к городу загружаем все улицы учитывая входящие нас.пункты
                    $where = " (kla.KLArea_id = :KLArea_id OR kla.KLArea_id IN (SELECT KLArea_id FROM KLArea WHERE KLAdr_Actual = 0 AND KLArea_pid = :KLArea_id) ) ";
                }
            }
            $sql = "
				SELECT
					kla.KLArea_id as \"KLArea_id\",
					kla.KLSocr_id as \"KLSocr_id\",
					kla.KLArea_Name as \"KLArea_Name\",
					kla.KLAreaLevel_id as \"KLAreaLevel_id\",
					'' as \"KLAdr_Ocatd\",
					'' as \"KLSocr_Nick\"
					{$select1}
				FROM
					KLArea kla
				WHERE
					kla.KLAdr_Actual = 0 and
					kla.KLArea_pid = :KLArea_id
				";
		
			if (getRegionNumber() == '10' && $data['KLArea_id'] == '10') {//#10288
				$sql .= "
				UNION ALL
					
				SELECT
					kla.KLCity_id AS \"KLArea_id\",
					klaid.KLSocr_id as \"KLSocr_id\",
					kla.KLCity_Name as \"KLArea_Name\",
					klaid.KLAreaLevel_id as \"KLAreaLevel_id\",
					'' as \"KLAdr_Ocatd\",
					'' as \"KLSocr_Nick\"
					{$select1}
				FROM
					v_KLAreaStat kla
					LEFT JOIN KLArea klaid ON klaid.KLArea_id = kla.KLCity_id
				where 
					kla.KLCity_id is not null
				";
			}
			
			$sql .= "
				UNION ALL

				SELECT
					kla.KLStreet_id AS \"KLArea_id\",
					kls.KLSocr_id as \"KLSocr_id\",
					kla.KLStreet_Name AS \"KLArea_Name\",
					'5' AS \"KLAreaLevel_id\",
					kla.KLAdr_Ocatd as \"KLAdr_Ocatd\",
					kls.KLSocr_Nick as \"KLSocr_Nick\"
					{$select2}
				FROM
					KLStreet kla
					left join v_KLSocr kls on kla.KLSocr_id = kls.KLSocr_id
					{$from}
				WHERE
					kla.KLAdr_Actual = 0 and
					{$where}

				ORDER BY
					\"KLAreaLevel_id\",
					\"KLArea_Name\"
			";
		}
		//echo getDebugSql($sql, array('KLArea_id' => $data['KLArea_id'])); die;
		$result = $this->db->query($sql, array('KLArea_id' => $data['KLArea_id']));

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
     *TODO: Сохранение нового адреса
     */
    function saveChildLists($data){

        $output = array();
        $newUpLevel = false;

        $country_id = (empty($data['KLCountry_idEdit'])?NULL:$data['KLCountry_idEdit']);
        $zip = (empty($data['Address_ZipEdit'])?NULL:$data['Address_ZipEdit']);

        $region = mb_strtoupper((empty($data['KLRgn_idEdit'])?NULL:$data['KLRgn_idEdit']));
        $sub_region = mb_strtoupper((empty($data['KLSubRgn_idEdit'])?NULL:$data['KLSubRgn_idEdit']));
        $town = mb_strtoupper((empty($data['KLTown_idEdit'])?NULL:$data['KLTown_idEdit']));
        $city = mb_strtoupper((empty($data['KLCity_idEdit'])?NULL:$data['KLCity_idEdit']));

        $street = mb_strtoupper((empty($data['KLStreet_idEdit'])?NULL:$data['KLStreet_idEdit']));
        $house = (empty($data['Address_HouseEdit'])?NULL:$data['Address_HouseEdit']);
        $corpus = (empty($data['Address_CorpusEdit'])?NULL:$data['Address_CorpusEdit']);
        $flat = (empty($data['Address_FlatEdit'])?NULL:$data['Address_FlatEdit']);


        $region_socr = (empty($data['Rgn_Socr'])?NULL:$data['Rgn_Socr']);
        $city_socr = (empty($data['City_Socr'])?NULL:$data['City_Socr']);
        $town_socr = (empty($data['Town_Socr'])?NULL:$data['Town_Socr']);
        $sub_region_socr = (empty($data['SubRgn_Socr'])?NULL:$data['SubRgn_Socr']);
        
        $street_socr = (empty($data['Street_Socr'])?NULL:$data['Street_Socr']);


$sql="
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				KLArea_id as \"Area_id\"
			from p_KLArea_ins (
			    KLSocr_id := ?,
                KLCountry_id := ?,
                KLAreaLevel_id := ?,
                KLArea_pid := ?,
                KLArea_Name := ?,
                Server_id := ?,
                pmUser_id  := ?,
                KLAdr_Actual := 0
			)
		";


        $sqlStreet = "
            select
                Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
                KLStreet_id as \"KLStreet_id\"
            from   p_KLStreet_ins
                (
                KLArea_id := ?,
                KLSocr_id := ?,
                KLStreet_localName := ?,
                Server_id := ?,
                pmUser_id := ?,
                KLAdr_Code := ?,
                KLAdr_Actual := 0
                )
            ";

        if($country_id != NULL) {

            if ($region != NULL) {

                if (is_numeric($region) ){
                    $output['KLRgn_idEdit'] = $region;
                    $area_id = $region;
                }
                else {
                    $queryParams = array(
                        $region_socr,
                        $country_id,
                        1,
                        NULL,
                        $region,
                        $data['Server_id'],
                        $data['pmUser_id']
                    );

                    $res = $this->db->query($sql, $queryParams);

                    if (($area_id =$this->ValidateInsertQuery($res)) == NULL ){
                        return false;
                    }

					$output['KLRgn_idEdit'] = $area_id;

                }
            }

            if ($sub_region != NULL ) {

                if (is_numeric($sub_region)) {
                    $output['KLSubRGN_idEdit'] = $sub_region;
                    $area_id = $sub_region;
                } else {

                    $area_id = (empty($area_id)?NULL:$area_id);

                    $queryParams = array(
                        $sub_region_socr,
                        $country_id,
                        2,
                        $area_id,
                        $sub_region,
                        $data['Server_id'],
                        $data['pmUser_id']
                    );

                    $res = $this->db->query($sql, $queryParams);

                    if (($area_id =$this->ValidateInsertQuery($res)) == NULL ){
                        return false;
                    }

					$output['KLSubRGN_idEdit'] = $area_id;

                 }        
            }


            if ($city != NULL) {

                if (is_numeric($city)){
                    $output['KLCity_idEdit'] = $city;
                    $area_id = $city;
                } else {

                    $area_id = (empty($area_id)?NULL:$area_id);

                    $queryParams = array(
                        $city_socr,
                        $country_id,
                        3,
                        $area_id,
                        $city,
                        $data['Server_id'],
                        $data['pmUser_id']

                    );

                    $res = $this->db->query($sql, $queryParams);

                    if (($area_id =$this->ValidateInsertQuery($res)) == NULL ){
                        return false;
                    }

					$output['KLCity_idEdit'] = $area_id;

                }

            }


            if ($town != NULL) {

                if (is_numeric($town)){
                    $output['KLTown_idEdit'] = $town;
                    $area_id = $town;
                } else {

                    $area_id = (empty($area_id)?NULL:$area_id);

                    $queryParams = array(
                        $town_socr,
                        $country_id,
                        4,
                        $area_id,
                        $town,
                        $data['Server_id'],
                        $data['pmUser_id']
                    );

                    $res = $this->db->query($sql, $queryParams);

                    if (($area_id =$this->ValidateInsertQuery($res)) == NULL ){
                        return false;
                    }

					$output['KLTown_idEdit'] = $area_id;

                }

            }


            if (( ($sub_region != NULL) || ($town != NULL) || ($city != NULL) ) && ($street != NULL) ) {

                if (is_numeric($street)) {
                    $output['KLStreet_idEdit'] = $street;
                    $area_id = $street;
                } else {

                    $queryParams = array(
                        $area_id,
                        $street_socr,
                        $street,
                        $data['Server_id'],
                        $data['pmUser_id'],
						NULL
                    );

                    $res = $this->db->query($sqlStreet, $queryParams);

                    if (($street_id=$this->ValidateInsertQuery($res)) == NULL ){
                        return false;
                    }

					$output['KLStreet_idEdit'] = $street_id;
                }
            }

            
        }

        return $output;

    }

	/**
	 *  Получение одного из списков "Регион", "Район", "Город", "Нас. пункт", "Улица"
	 */
	function getAddressCombo($data)
	{
		$filter = ' (1 = 1)';
		$table = '';
		$queryParams = array();
		
		if (in_array($data['level'], array(1, 2, 3, 4)))
		{
			$filter .= " and KLArea.KLAreaLevel_id = :KLAreaLevel_id";
			$queryParams['KLAreaLevel_id'] = $data['level'];

			if ($data['level'] == 1)
			{
				$filter .= " and KLArea.KLCountry_id = :KLCountry_id";
				$queryParams['KLCountry_id'] = $data['country_id'];
			}
			else
			{
				$filter .= " and KLArea.KLArea_pid = :KLArea_pid";
				$queryParams['KLArea_pid'] = $data['value'];
			}

			$query = "
				select
					KLArea.KLArea_id as \"KLArea_id\",
					RTRIM(KLArea.KLArea_Name) || ' ' || RTRIM(KLSocr.KLSocr_Nick) as \"KLArea_Name\"
				from KLArea 
					inner join KLSocr  on KLSocr.KLSocr_id = KLArea.KLSocr_id
				where KLAdr_Actual = 0 and " . $filter . "
			";
		}
		else if (5 == $data['level'])
		{
			$filter .= " and KLStreet.KLArea_id = :KLArea_id";
			$queryParams['KLArea_id'] = $data['value'];

			$query = "
				select
					KLStreet.KLStreet_id as \"KLStreet_id\",
					RTRIM(KLStreet.KLStreet_Name) || ' ' || RTRIM(KLSocr.KLSocr_Nick) as \"KLStreet_Name\"
				from KLStreet 
					inner join KLSocr  on KLSocr.KLSocr_id = KLStreet.KLSocr_id
				where KLAdr_Actual = 0 and 
				" . $filter . "
			";
		}
		else
		{
			return false;
		}

		$result = $this->db->query($query, $queryParams);

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
     * Получение данных об адресе
     */
	function loadAddressData($data)
	{
		$sql = "
			select
				rtrim(Address_Zip) as \"Address_ZipEdit\",
				KLCountry_id as \"KLCountry_idEdit\",
				KLRGN_id as \"KLRgn_idEdit\",
				KLSubRGN_id as \"KLSubRgn_idEdit\",
				KLCity_id as \"KLCity_idEdit\",
				KLTown_id as \"KLTown_idEdit\",
				KLStreet_id as \"KLStreet_idEdit\",
				rtrim(Address_House) as \"Address_HouseEdit\",
				rtrim(Address_Corpus) as \"Address_CorpusEdit\",
				rtrim(Address_Flat) as \"Address_FlatEdit\",
				rtrim(Address_Address) as \"Address_AddressEdit\",
				PersonSprTerrDop_id as \"PersonSprTerrDop_idEdit\"
			from
				v_Address
			where
				Address_id = ?
		";

        //echo getDebugSQL($sql, array($data['Address_id']));die;
		$result = $this->db->query($sql, array($data['Address_id']));

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
	 * Загрузка справочника сокращений
	 */
	function getKLSocrSpr()
	{
		$sql = "
			SELECT
                KLSocr_id as \"KLSocr_id\",
                KLSocr_Name as \"KLSocr_Name\",
                KLSocr_Nick as \"KLSocr_Nick\",
                KLAreaType_id as \"KLAreaType_id\",
                KLAreaLevel_id as \"KLAreaLevel_id\",
                KLSocr_ObjType as \"KLSocr_ObjType\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                KLSocr_insDT as \"KLSocr_insDT\",
                KLSocr_updDT as \"KLSocr_updDT\",
                KLSocr_LocalName as \"KLSocr_LocalName\",
                KLSocr_LocalNick as \"KLSocr_LocalNick\",
                KLCountry_id as \"KLCountry_id\",
                KLSocr_Rowversion as \"KLSocr_Rowversion\"
			from KLSocr
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка областей
	 */
public function getRegionsList( $data ) {
		if ( isset( $this->mongo_switch['kladr'] ) && $this->mongo_switch['kladr'] === true ) {
			$collection = 'KLArea';
			$result = $this->swmongodb->where(array(
				'KLAdr_Actual' => 0,
				'KLCountry_id' => (int)$data[ 'country_id' ],
				'KLAreaLevel_id' => 1,
			))->get( $collection );

			return $result;
		} else {
			$sql = "
				SELECT
					KLArea_id as \"KLArea_id\",
                    KLSocr_id as \"KLSocr_id\",
                    KLArea_Name as \"KLArea_Name\"
				FROM
					KLArea
				WHERE
					KLAdr_Actual=0
					AND KLCountry_id=?
					and KLAreaLevel_id=1
			";
			$query = $this->db->query( $sql, array( $data[ 'country_id' ] ) );
			if ( is_object( $query ) ) {
				return $query->result_array();
			}
		}

		return false;
	}

	/**
	 * Получение списка районов
	 */
public function getSubRegionsList( $data ) {
		if ( isset( $this->mongo_switch['kladr'] ) && $this->mongo_switch['kladr'] === true ) {
			$collection = 'KLArea';
			$result = $this->swmongodb->where(array(
				'KLAdr_Actual' => 0,
				'KLArea_pid' => (int)$data[ 'region_id' ],
				'KLAreaLevel_id' => 2,
			))->get( $collection );

			return $result;
		} else {
			$sql = "
				SELECT
				    KLArea_id as \"KLArea_id\",
                    KLSocr_id as \"KLSocr_id\",
                    KLArea_Name as \"KLArea_Name\"
				FROM
					KLArea
				WHERE
					KLAdr_Actual=0
					AND KLArea_pid=?
					AND KLAreaLevel_id=2
			";
			$query = $this->db->query( $sql, array( $data[ 'region_id' ] ) );
			if ( is_object( $query ) ) {
				return $query->result_array();
			}
		}

		return false;
	}

	/**
	 * Получение списка городов
	 */
public function getCitiesList( $data ) {
		if ( isset( $this->mongo_switch['kladr'] ) && $this->mongo_switch['kladr'] === true ) {
			$collection = 'KLArea';
			$result = $this->swmongodb->where(array(
				'KLAdr_Actual' => 0,
				'KLArea_pid' => (int)$data[ 'subregion_id' ],
				'KLAreaLevel_id' => 3,
			))->get( $collection );

			return $result;
		} else {

            $addictWhere = '';

            //это условие отрабатывает только для казахстана
            if($_SESSION['region']['nick'] == 'kz'){
                $addictWhere = " (kla.KLAdr_Code ILIKE :subregion_id || '%' AND kla.KLAreaLevel_id = 3 ) OR ";
            };

			$sql = "
				SELECT
					KLArea_id as \"KLArea_id\",
                    KLSocr_id as \"KLSocr_id\",
                    KLArea_Name as \"KLArea_Name\"
				FROM
					KLArea kla
				WHERE
					KLAdr_Actual=0
					and (
						(KLArea_pid = :subregion_id and kla.KLAreaLevel_id = 3)
						OR (
							{$addictWhere}
							(kla.KLAdr_Code ILIKE :subregion_id || '%' AND kla.KLAreaCentreType_id=5 )
						)
					)
			";

			$data[ 'subregion_id' ] = str_pad( $data[ 'subregion_id' ], 2, '0', STR_PAD_LEFT );

			$query = $this->db->query( $sql, array( 'subregion_id' => $data[ 'subregion_id' ] ) );
			if ( is_object( $query ) ) {
				return $query->result_array();
			}
		}

		return false;
	}

	/**
	 * Получение списка населенных пунктов
	 */
public function getTownsList( $data ) {
		if ( isset( $this->mongo_switch['kladr'] ) && $this->mongo_switch['kladr'] === true ) {
			$collection = 'KLArea';
			$result = $this->swmongodb->where(array(
				'KLAdr_Actual' => 0,
				'KLArea_pid' => (int)$data[ 'city_id' ],
				'KLAreaLevel_id' => 4,
			))->get( $collection );

			return $result;
		} else {
			$sql = "
				SELECT
					KLArea_id as \"KLArea_id\",
                    KLSocr_id as \"KLSocr_id\",
                    KLArea_Name as \"KLArea_Name\"
				FROM
					KLArea
				WHERE
					KLAdr_Actual=0
					AND KLArea_pid = ?
					AND KLAreaLevel_id = 4
			";
			$query = $this->db->query( $sql, array( $data[ 'city_id' ] ) );
			if ( is_object( $query ) ) {
				return $query->result_array();
			}
		}

		return false;
	}

	/**
	 * Получение списка улиц
	 */
    function getStreetsList($data)
    {
        $select = ",
			kls.KLSocr_id as \"Socr_id\",
			kls.KLSocr_Nick as \"Socr_Nick\"
		";

        $join = "";
        $where = " AND kla.KLArea_id = :KLArea_id ";
        if(!empty($data['Form'])){
            $join .= "
				LEFT JOIN KLArea klapid ON klapid.KLArea_id = kla.KLArea_id
				LEFT join KLSocr klspid on klapid.KLSocr_id = klspid.KLSocr_id
			";
            $select = ",
				klapid.KLAreaLevel_id AS \"KLAreaLevel_pid\",
				klapid.KLArea_id AS \"KLArea_pid\",
				klapid.KLArea_Name || ' ' || klspid.KLSocr_Nick AS \"AreaPID_Name\"
			";
            $where = " AND (kla.KLArea_id = :KLArea_id OR kla.KLArea_id IN (SELECT KLArea_id FROM KLArea WHERE KLAdr_Actual = 0 AND KLArea_pid = :KLArea_id) )";
        }

        if (!empty($data['fromMobile'])) $select = "";

        if (isset($data['showSocr']) && ($data['showSocr'] == 1)) {
            $sql = "
				select
					kla.KLStreet_id as \"Street_id\",
					RTRIM(kla.KLStreet_Name) as \"Street_Name\"
					{$select}
				from KLStreet kla 
					inner join KLSocr kls on kls.KLSocr_id = kla.KLSocr_id
					{$join}
				where kla.KLAdr_Actual = 0
				{$where}
			";
        } else {
            $sql = "
				SELECT
					kla.KLStreet_id as \"Street_id\",
					RTRIM(kla.KLStreet_Name) as \"Street_Name\"
					{$select}
				from KLStreet kla
					left join KLSocr kls on kls.KLSocr_id = kla.KLSocr_id
					{$join}
				where
					kla.KLAdr_Actual = 0
					{$where}
			";
        }
        //echo getDebugSQL($sql, array($data['town_id'])); die();
        $res = $this->db->query($sql, array('KLArea_id' => $data['town_id']));
        if ( is_object($res) )
            return $res->result('array');
        else
            return false;
    }

	/**
	 * Загрузка справочника территорий
	 */
	function getKLAreaStatSpr()
	{
		$sql = "
			SELECT
                KLTown_Name as \"KLTown_Name\",
                KLTown_Socr as \"KLTown_Socr\",
                KLArea_id as \"KLArea_id\",
                KLArea_Name as \"KLArea_Name\",
                KLAreaStat_FullName as \"KLAreaStat_FullName\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                KLAreaStat_insDT as \"KLAreaStat_insDT\",
                KLAreaStat_updDT as \"KLAreaStat_updDT\",
                KLAreaStat_id as \"KLAreaStat_id\",
                KLAreaStat_Code as \"KLAreaStat_Code\",
                KLCountry_id as \"KLCountry_id\",
                KLRGN_id as \"KLRGN_id\",
                KLSubRGN_id as \"KLSubRGN_id\",
                KLCity_id as \"KLCity_id\",
                KLTown_id as \"KLTown_id\",
                KLCountry_Name as \"KLCountry_Name\",
                KLRGN_Name as \"KLRGN_Name\",
                KLRGN_Socr as \"KLRGN_Socr\",
                KLSubRGN_Name as \"KLSubRGN_Name\",
                KLSubRGN_Socr as \"KLSubRGN_Socr\",
                KLCity_Name as \"KLCity_Name\",
                KLCity_Socr as \"KLCity_Socr\"
			from v_KLAreaStat
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение всех полей для окна редактирования адреса
	 */
	function getAddressEditWindow($data)
	{
		$sql = "
			SELECT
                KLTown_Name as \"KLTown_Name\",
                KLTown_Socr as \"KLTown_Socr\",
                KLArea_id as \"KLArea_id\",
                KLArea_Name as \"KLArea_Name\",
                KLAreaStat_FullName as \"KLAreaStat_FullName\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                KLAreaStat_insDT as \"KLAreaStat_insDT\",
                KLAreaStat_updDT as \"KLAreaStat_updDT\",
                KLAreaStat_id as \"KLAreaStat_id\",
                KLAreaStat_Code as \"KLAreaStat_Code\",
                KLCountry_id as \"KLCountry_id\",
                KLRGN_id as \"KLRGN_id\",
                KLSubRGN_id as \"KLSubRGN_id\",
                KLCity_id as \"KLCity_id\",
                KLTown_id as \"KLTown_id\",
                KLCountry_Name as \"KLCountry_Name\",
                KLRGN_Name as \"KLRGN_Name\",
                KLRGN_Socr as \"KLRGN_Socr\",
                KLSubRGN_Name as \"KLSubRGN_Name\",
                KLSubRGN_Socr as \"KLSubRGN_Socr\",
                KLCity_Name as \"KLCity_Name\",
                KLCity_Socr as \"KLCity_Socr\"
			from v_Address
			where Address_id = {$data['address_id']}
            limit 1

			
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка результатов выполнения запроса, возврат ошибки если что то пошло не так
	 */
	function ValidateInsertQuery($res)
	{
		if ( is_object($res) )
		{
			foreach ($res->result('array') as $rows)
			{
				if ( !empty( $rows['ErrMsg'] ) )
				{
                    return NULL;
				} else if (!empty( $rows['Area_id']) ){
                    return $rows['Area_id'];
                } else if (!empty( $rows['Street_id'])){
                        return $rows['Street_id'];
                    }
            }
        }
		else
		{
            return NULL;
		}
	}

	/**
	 * Получение списка территорий
	 */
	function getKLAreaList($data) {
		$where = "(1=1)";
		$params = array();

		if (!empty($data['KLAreaLevel_id'])) {
			$where .= " and A.KLAreaLevel_id = :KLAreaLevel_id";
			$params['KLAreaLevel_id'] = $data['KLAreaLevel_id'];
		}

		$params['KLArea_pid'] = $data['KLArea_pid'];

		$query = "
			with RECURSIVE Rec(KLArea_id, KLArea_pid, KLArea_Name, KLAreaLevel_id)
			as
			(
				select A.KLArea_id, A.KLArea_pid, A.KLArea_Name, A.KLAreaLevel_id
				from v_KLArea A 
				where
					A.KLArea_pid = :KLArea_pid
					and A.KLAdr_Actual = 0
				union ALL
				select A.KLArea_id, A.KLArea_pid, A.KLArea_Name, A.KLAreaLevel_id
				from v_KLArea A 
					JOIN Rec R on A.KLArea_pid = R.KLArea_id
				where
					A.KLAdr_Actual = 0
			)
			select
				A.KLArea_id as \"KLArea_id\",
				A.KLArea_pid as \"KLArea_pid\",
				A.KLArea_Name as \"KLArea_Name\",
				A.KLAreaLevel_id as \"KLAreaLevel_id\"
			from
				Rec A 
			where
				{$where}
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Поиск в кэше адресов по тексту
	 * @return array
	 */
	function searchAddress($data){
		$filter='';
		$params=array();
		if (isset($data['query']) && strlen(trim($data['query']))>0) {
			$filter .=" and KladrCache_Text ilike :query";
			$params['query']="%".str_replace(' ', '%', trim($data['query']))."%";
		}
		if ($data['KLCountry_id']>0) {
			$filter .=" and KLCountry_id = :KLCountry_id";
			$params['KLCountry_id']=$data['KLCountry_id'];
		}
		if ($data['KLRgn_id']>0) {
			$filter .=" and KLRgn_id = :KLRgn_id";
			$params['KLRgn_id']=$data['KLRgn_id'];
		}
		
		$sql = "
			select
				KladrCache_id as \"KladrCache_id\",
				KLCountry_id as \"KLCountry_id\",
				KLRgn_id as \"KLRgn_id\",
				KLSubRgn_id as \"KLSubRgn_id\",
				KLCity_id as \"KLCity_id\",
				KLTown_id as \"KLTown_id\",
				KLStreet_id as \"KLStreet_id\",
				KladrCache_Text as \"KladrCache_Text\"
			from
				KladrCache 
			where (1=1){$filter}
			limit 20
		";
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение адреса. Метод для API
	 */
	function saveAddress($data){
		if ( empty($data['Address_id']) ) {
			$procedure_action = "ins";
		}
		else {
			$procedure_action = "upd";
		}


	

	$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				Address_id as \"Address_id\"
			from p_Address_" . $procedure_action . "
			(
			    Server_id := :Server_id,
                Address_id := :Address_id,
                
				KLCountry_id := :KLCountry_id,
				KLRgn_id := :KLRgn_id,
				KLSubRgn_id := :KLSubRgn_id,
				KLCity_id := :KLCity_id,
				KLTown_id := :KLTown_id,
				KLAreaType_id := :KLAreaType_id,
				KLStreet_id := :KLStreet_id,
				Address_Zip := :Address_Zip,
				Address_House := :Address_House,
				Address_Corpus := :Address_Corpus,
				Address_Flat := :Address_Flat,
				Address_Address := :Address_Address,
				KLAreaStat_id := :KLAreaStat_id,
				Address_begDate := CAST(:Address_begDate as date),
				Address_Nick := :Address_Nick,
				PersonSprTerrDop_id := :PersonSprTerrDop_id,
				AddressSpecObject_id := :AddressSpecObject_id,
				pmUser_id := :pmUser_id
			)
		";

		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение адреса регистрации. Метод для API
	 */
	function savePersonUAddress($data){
		if ( !isset($data['PersonUAddress_id']) ) {
			$procedure_action = "ins";
		}
		else {
			$procedure_action = "upd";
		}

	


$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				Address_id as \"Address_id\"
			from p_PersonUAddress_" . $procedure_action . "
			(
			    Server_id := :Server_id,
				PersonUAddress_id := :PersonUAddress_id,
				Person_id := :Person_id,
				PersonUAddress_Index := :PersonUAddress_Index,
				PersonUAddress_Count := :PersonUAddress_Count,
				PersonUAddress_begDT := :PersonUAddress_begDT,
				Address_id := :Address_id,
				KLCountry_id := :KLCountry_id,
				KLRgn_id := :KLRgn_id,
				KLSubRgn_id := :KLSubRgn_id,
				KLCity_id := :KLCity_id,
				KLTown_id := :KLTown_id,
				KLAreaType_id := :KLAreaType_id,
				KLStreet_id := :KLStreet_id,
				Address_Zip := :Address_Zip,
				Address_House := :Address_House,
				Address_Corpus := :Address_Corpus,
				Address_Flat := :Address_Flat,
				Address_Address := :Address_Address,
				KLAreaStat_id := :KLAreaStat_id,
				Address_begDate := CAST(:Address_begDate as date),
				Address_Nick := :Address_Nick,
				PersonSprTerrDop_id := :PersonSprTerrDop_id,
				AddressSpecObject_id := :AddressSpecObject_id,
				pmUser_id := :pmUser_id
			)
		";


		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение адреса проживания. Метод для API
	 */
	function savePersonPAddress($data){
		if ( !isset($data['PersonPAddress_id']) ) {
			$procedure_action = "ins";
		}
		else {
			$procedure_action = "upd";
		}



$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				Address_id as \"Address_id\"
			from p_PersonPAddress_" . $procedure_action . "
			(
			    Server_id := :Server_id,
				PersonPAddress_id := :PersonPAddress_id,
				Person_id := :Person_id,
				PersonPAddress_Index := :PersonPAddress_Index,
				PersonPAddress_Count := :PersonPAddress_Count,
				PersonPAddress_begDT := :PersonPAddress_begDT,
				Address_id := :Address_id,
				KLCountry_id := :KLCountry_id,
				KLRgn_id := :KLRgn_id,
				KLSubRgn_id := :KLSubRgn_id,
				KLCity_id := :KLCity_id,
				KLTown_id := :KLTown_id,
				KLAreaType_id := :KLAreaType_id,
				KLStreet_id := :KLStreet_id,
				Address_Zip := :Address_Zip,
				Address_House := :Address_House,
				Address_Corpus := :Address_Corpus,
				Address_Flat := :Address_Flat,
				Address_Address := :Address_Address,
				KLAreaStat_id := :KLAreaStat_id,
				Address_begDate := CAST(:Address_begDate as date),
				Address_Nick := :Address_Nick,
				PersonSprTerrDop_id := :PersonSprTerrDop_id,
				AddressSpecObject_id := :AddressSpecObject_id,
				pmUser_id := :pmUser_id
			)
		";
		
		
		
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Сохранение места рождения. Метод для API
	 */
	function savePersonBirthPlace($data){
		if ( !isset($data['PersonBirthPlace_id']) ) {
			$procedure_action = "ins";
		}
		else {
			$procedure_action = "upd";
		}
		

$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				PersonBirthPlace_id as \"PersonBirthPlace_id\"
			from p_PersonBirthPlace_" . $procedure_action . "
			(
			    PersonBirthPlace_id := :PersonBirthPlace_id,
				Person_id := :Person_id,
				Address_id := :Address_id,
				pmUser_id := :pmUser_id
			)
		";
		
		//echo getDebugSQL($query, $data);exit;
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Получение места рождения. Метод для API
	 */
	function getPersonBirthPlace($data){
		$query = "
			select
				PersonBirthPlace_id as \"PersonBirthPlace_id\",
				Address_id as \"Address_id\",
				Person_id as \"Person_id\"
			from v_PersonBirthPlace
			where
				Person_id = :Person_id
		";
		//echo getDebugSQL($query, $data);exit;
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     *  Получение данных об адресе
     */
	function loadAddress($data,$addressType = null,$extraData = false)
	{
		if(!array_key_exists('AddressType_id', $data)){
			$data['AddressType_id'] = null;
		}
		if(!array_key_exists('Person_id', $data)){
			$data['Person_id'] = null;
		}

		$extraSelect = "";
		if(!empty($addressType)){
			$data['AddressType_id'] = $addressType;
		}
		if($extraData){
			$extraSelect = " 
				,a.Server_id as \"Server_id\"
				,a.KLAreaType_id as \"KLAreaType_id\"
				,a.KLAreaStat_id as \"KLAreaStat_id\"
				,a.Address_Nick as \"Address_Nick\"
				,a.PersonSprTerrDop_id as \"PersonSprTerrDop_id\"
				,a.AddressSpecObject_id as \"AddressSpecObject_id\"
				,a.Address_begDate as \"Address_begDate\"
			";
		}
		$query = "
			select
				:Person_id as \"Person_id\",
				a.Address_id as \"Address_id\",
				:AddressType_id as \"AddressType_id\",
				rtrim(a.Address_Zip) as \"Address_Zip\",
				a.KLCountry_id as \"KLCountry_id\",
				a.KLRgn_id as \"KLRgn_id\",
				a.KLSubRgn_id as \"KLSubRgn_id\",
				a.KLCity_id as \"KLCity_id\",
				a.KLTown_id as \"KLTown_id\",
				a.KLStreet_id as \"KLStreet_id\",
				rtrim(a.Address_House) as \"Address_House\",
				rtrim(a.Address_Corpus) as \"Address_Corpus\",
				rtrim(a.Address_Flat) as \"Address_Flat\",
				a.Address_Address as \"Address_Address\",
				to_char( a.Address_insDT, 'yyyy-mm-dd hh:mi:ss') as \"Address_insDT\",
				kla.KLArea_AOID as \"AoidArea\",
				kls.KLStreet_AOID as \"AoidStreet\"
				{$extraSelect}
			from
				v_Address a
				left join v_KLArea kla on kla.KLArea_id = coalesce(a.KLTown_id, a.KLCity_id, a.KLSubRgn_id, a.KLRgn_id)
				left join v_KLStreet kls on kls.KLStreet_id = a.KLStreet_id
			where
				a.Address_id = :Address_id
		";

		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     *  Получение идентификатора адреса регистрации
     */
	function loadUAddressId($data)
	{
		$query = "
			select
				Address_id as \"Address_id\",
				Person_id as \"Person_id\"
			from
				v_PersonUAddress
			where
				Person_id = :Person_id
		";

		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
     *  Получение идентификатора адреса проживания
     */
	function loadPAddressId($data)
	{
		$query = "
			select
				Address_id as \"Address_id\",
				Person_id as \"Person_id\"
			from
				v_PersonPAddress
			where
				Person_id = :Person_id
		";

		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение параметров KLRgn_id, KLSubRgn_id, KLCity_id, KLTown_id, KLStreet_id по кодам ФИАС
	 */
	public function getAddressParamsByFIASCode($AoidArea = null, $AoidStreet = null) {
		$response = array(
			'KLRgn_id' => null,
			'KLSubRgn_id' => null,
			'KLCity_id' => null,
			'KLTown_id' => null,
			'KLStreet_id' => null,
		);

		if ( !empty($AoidStreet) ) {
			$streetData = $this->getFirstRowFromQuery("
				select 
				    kls.KLStreet_id as \"KLStreet_id\", 
				    kla.KLArea_pid as \"KLArea_pid\", 
				    kla.KLArea_id as \"KLArea_id\", 
				    kla.KLAreaLevel_id as \"KLAreaLevel_id\"
				from v_KLStreet kls
					inner join v_KLArea kla on kla.KLArea_id = kls.KLArea_id
				where kls.KLStreet_AOID = :KLStreet_AOID
				limit 1
			", array(
				'KLStreet_AOID' => $AoidStreet
			));

			if ( $streetData === false ) {
				return false;
			}

			$KLArea_pid = $streetData['KLArea_pid'];
			$KLAreaLevel_id = $streetData['KLAreaLevel_id'];

			$response['KLStreet_id'] = $streetData['KLStreet_id'];

			if ( $this->_getAddressIdentName($KLAreaLevel_id) === false ) {
				return false;
			}

			$response[$this->_getAddressIdentName($KLAreaLevel_id)] = $streetData['KLArea_id'];
			$i = 0;

			while ( $KLAreaLevel_id > 1 && $i < 5 ) {
				$i++;

				$areaData = $this->getFirstRowFromQuery("
					select 
					    KLArea_pid as \"KLArea_pid\", 
					    KLArea_id as \"KLArea_id\", 
					    KLAreaLevel_id as \"KLAreaLevel_id\"
					from v_KLArea
					where KLArea_id = :KLArea_pid
					limit 1
				", array(
					'KLArea_pid' => $KLArea_pid
				));

				if ( $areaData === false ) {
					return false;
				}

				$KLArea_pid = $areaData['KLArea_pid'];
				$KLAreaLevel_id = $areaData['KLAreaLevel_id'];

				if ( $this->_getAddressIdentName($KLAreaLevel_id) === false ) {
					return false;
				}

				$response[$this->_getAddressIdentName($KLAreaLevel_id)] = $areaData['KLArea_id'];
			}
		}

		if ( !empty($AoidArea) && empty($response['KLRgn_id']) ) {
			$areaData = $this->getFirstRowFromQuery("
				select 
				    KLArea_pid as \"KLArea_pid\", 
				    KLArea_id as \"KLArea_id\", 
				    KLAreaLevel_id as \"KLAreaLevel_id\"
				from v_KLArea
				where KLArea_AOID = :KLArea_AOID
				limit 1
			", array(
				'KLArea_AOID' => $AoidArea
			));

			if ( $areaData === false ) {
				return false;
			}

			$KLArea_pid = $areaData['KLArea_pid'];
			$KLAreaLevel_id = $areaData['KLAreaLevel_id'];

			if ( $this->_getAddressIdentName($KLAreaLevel_id) === false ) {
				return false;
			}

			$response[$this->_getAddressIdentName($KLAreaLevel_id)] = $areaData['KLArea_id'];
			$i = 0;

			while ( $KLAreaLevel_id > 1 && $i < 5 ) {
				$i++;

				$areaData = $this->getFirstRowFromQuery("
					select 
					    KLArea_pid as \"KLArea_pid\", 
					    KLArea_id as \"KLArea_id\", 
					    KLAreaLevel_id as \"KLAreaLevel_id\"
					from v_KLArea
					where KLArea_id = :KLArea_pid
					limit 1
				", array(
					'KLArea_pid' => $KLArea_pid
				));

				if ( $areaData === false ) {
					return false;
				}

				$KLArea_pid = $areaData['KLArea_pid'];
				$KLAreaLevel_id = $areaData['KLAreaLevel_id'];

				if ( $this->_getAddressIdentName($KLAreaLevel_id) === false ) {
					return false;
				}

				$response[$this->_getAddressIdentName($KLAreaLevel_id)] = $areaData['KLArea_id'];
			}
		}

		return $response;
	}

	/**
	 * Получение названия идентификатора по KLAreaLevel_id
	 */
	private function _getAddressIdentName($KLAreaLevel_id = null) {
		$response = false;

		switch ( $KLAreaLevel_id ) {
			case 1: $response = 'KLRgn_id'; break;
			case 2: $response = 'KLSubRgn_id'; break;
			case 3: $response = 'KLCity_id'; break;
			case 4: $response = 'KLTown_id'; break;
		}

		return $response;
	}

	/**
	 * Получить адрес как строку вида: Город, Улица, д. N, кв. M
	 *
	 * @param int $address_id Идентификатор адреса
	 */
	public function getAddressTextBrief($address_id) {
		$result = $this->getFirstRowFromQuery("
			select
    			rtrim(a.Address_House) as \"Address_House\",
                rtrim(a.Address_Corpus) as \"Address_Corpus\",
                rtrim(a.Address_Flat) as \"Address_Flat\",
    			rtrim(ci.KLArea_Name) as \"CityName\",
    			rtrim(cis.KLSocr_Nick) as \"CitySocr\",
    			rtrim(t.KLArea_Name) as \"TownName\",
    			rtrim(ts.KLSocr_Nick) as \"TownSocr\",
    			rtrim(s.KLStreet_Name) as \"StreetName\",
    			rtrim(ss.KLSocr_Nick) as \"StreetSocr\"
    		from
    			Address a 
                left outer join KLArea ci  on a.KLCity_id = ci.KLArea_id
                left outer join KLSocr cis  on ci.KLSocr_id = cis.KLSocr_id
                left outer join KLArea t  on a.KLTown_id = t.KLArea_id
                left outer join KLSocr ts  on t.KLSocr_id = ts.KLSocr_id
                left outer join KLStreet s  on a.KLStreet_id = s.KLStreet_id
                left outer join KLSocr ss  on s.KLSocr_id = ss.KLSocr_id
            where a.Address_id = :address_id",array('address_id' => $address_id));

		$address_text_raw = array();

		if (!empty($result['CityName'])) {
			$address_text_raw[] = strtolower($result['CitySocr']) . " " . ucwords($result['CityName']);
		}
		if (!empty($result['TownName'])) {
			$address_text_raw[] = $result['TownName'] . " " . $result['TownSocr'];
		}
		if (!empty($result['StreetName'])) {
			$address_text_raw[] = strtolower($result['StreetSocr']) . " " . ucwords($result['StreetName']);
		}
		if (!empty($result['Address_House'])) {
			$address_text_raw[] = 'д' . " " . $result['Address_House'];
		}
		if (!empty($result['Address_Corpus'])) {
			$address_text_raw[] = 'корпус' . " " . $result['Address_Corpus'];
		}
		if (!empty($result['Address_Flat'])) {
			$address_text_raw[] = 'кв' . " " . $result['Address_Flat'];
		}

		$address_text = addslashes(implode(", ", $address_text_raw));

		return $address_text;
	}
}
