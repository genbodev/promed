<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Address_model - контроллер для работы с адресами
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
 * @property Address_model amodel
 */
class Address extends swController
{
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database();
     	$this->load->model("Address_model", "amodel");
		
		$this->inputRules = array(
			'loadOkatoField' => array(
                array(
                    'field' => 'KLRGN_id',
                    'label' => 'Регион',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLSubRGN_id',
                    'label' => 'Район',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLCity_id',
                    'label' => 'Город',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLTown_id',
                    'label' => 'Населенный пункт',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLStreet_id',
                    'label' => 'Улица',
                    'rules' => 'trim',
                    'type' => 'id'
                )
			),
			'saveChildLists' => array(
				array('field' => 'Address_AddressEdit', 'label' => 'Полный адрес', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Address_CorpusEdit', 'label' => 'Корпус', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Address_FlatEdit', 'label' => 'Квартира', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'Address_HouseEdit', 'label' => 'Дом', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'Address_ZipEdit', 'label' => 'Индекс', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'City_Socr', 'label' => 'Сокращение типа города', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'KLCity_idEdit', 'label' => 'Город', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'KLCountry_idEdit', 'label' => 'ID Страны', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'Rgn_Socr', 'label' => 'Сокращение типа региона', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'KLRgn_idEdit', 'label' => 'Регион', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Street_Socr', 'label' => 'Сокращение типа улицы', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'KLStreet_idEdit', 'label' => 'Улица', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'SubRgn_Socr', 'label' => 'Сокращение типа района', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'KLSubRgn_idEdit', 'label' => 'Район', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Town_Socr', 'label' => 'Сокращение типа населённого пункта', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'KLTown_idEdit', 'label' => 'Нас. пункт', 'rules' => 'trim', 'type' => 'string')
			),
			'getSprTerrDopByStreetAndHouse' => array(
				array(
					'field' => 'street_id',
					'label' => 'Идентификатор улицы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'house',
					'label' => 'Дом',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
            'getZipAddressByStreetAndHome' => array(
                array(
                    'field' => 'street_id',
                    'label' => 'Идентификатор улицы',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'town_id',
                    'label' => 'Идентификатор населенного пункта',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'house',
                    'label' => 'Дом',
                    'rules' => 'trim',
                    'type' => 'string'
                )
            ),
			'getRegionsList' => array(
				array(
					'field' => 'country_id',
					'label' => 'Идентификатор страны',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'region_id',
					'label' => 'Идентификатор региона',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getSubRegionsList' => array(
				array(
					'field' => 'region_id',
					'label' => 'Идентификатор региона',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getCitiesList' => array(
				array(
					'field' => 'subregion_id',
					'label' => 'Идентификатор региона',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getTownsList' => array(
				array(
					'field' => 'city_id',
					'label' => 'Идентификатор города',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getAddressCombo' => array(
				array(
					'field' => 'country_id',
					'label' => 'Идентификатор страны',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'level',
					'label' => 'Уровень',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'value',
					'label' => 'Значение',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadAddressData' => array(
				array(
					'field' => 'Address_id',
					'label' => 'Идентификатор адреса',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadAddressSpecObject'=>array(
				array(
					'field'=>'query',
					'label'=>'условие поиска',
					'rules'=>'trim',
					'type'=>'string'
				)
			),
			'getKLAreaList' => array(
				array(
					'field' => 'KLAreaLevel_id',
					'label' => 'Идентификатор KLAreaLevel',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLArea_pid',
					'label' => 'Идентификатор родительского KLArea',
					'rules' => '',
					'type' => 'id'
				)
			),
			'searchKLTown' => array(
				array(
					'field' => 'KLCity_id',
					'label' => 'Идентификатор города',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLSubRegion_id',
					'label' => 'Идентификатор района',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLRegion_id',
					'label' => 'Идентификатор региона',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLTown_Name',
					'label' => 'Значение',
					'rules' => '',
					'type' => 'string'
				)
			),
			'searchKLCity' => array(
				array(
					'field' => 'KLSubRegion_id',
					'label' => 'Идентификатор района',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLRegion_id',
					'label' => 'Идентификатор региона',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLCity_Name',
					'label' => 'Значение',
					'rules' => '',
					'type' => 'string'
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
					'rules' => (getRegionNick()=='kz')?'required':'',
					'type' => 'id'
				)
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
				),
				array(
					'field' => 'Form',
					'label' => 'Форма',
					'rules' => '',
					'type' => 'string'
				),
			),
			'getStreetsList' => array(
				array(
					'field' => 'town_id',
					'label' => 'Идентификатор города',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'showSocr',
					'label' => 'Показывать сокращение',
					'rules' => '',
					'type' => 'int',
					'default' => 0
				),
				array(
					'field' => 'Form',
					'label' => 'Форма',
					'rules' => '',
					'type' => 'string'
				)
			)
		);
	}

    /**
	 * Получение индекса по улице и Дому
	 */
    function getZipAddressByStreetAndHome()
    {
        $data = $this->ProcessInputData('getZipAddressByStreetAndHome', false);
        if ($data === false)
            return false;

        if ( (($data['street_id'] < 1) || ($data['house'] == '')) && ($data['town_id'] < 1) )
        {
            $this->ReturnData(array('success'=>false));
            return true;
        }

        $response = $this->amodel->getZipAddressByStreetAndHome($data);
        if ($response > 0)
        {
            $this->ReturnData(array('success'=>true,
                'Address_Zip'=>$response
                ));
            return true;
        } else if ($response == 0) {
            $this->ReturnData(array('success'=>true,
                'Address_Zip'=> 'Index not found'
            ));
            return true;

        } else {
            $this->ReturnData(array('success'=>false));
            return true;
        }
    }


	/**
	 * Получение района города по номеру дома и идентификатору улицы
	 */
	function getSprTerrDopByStreetAndHouse()
	{

		$data = $this->ProcessInputData('getSprTerrDopByStreetAndHouse', false);
		if ($data === false) return false;

		if ( $data['street_id'] == -1 || $data['house'] == '' )
		{
			$this->ReturnData(array('success'=>false));
			return true;
		}

		$response = $this->amodel->getSprTerrDopByStreetAndHouse($data);
		if ($response > 0)
		{
			$this->ReturnData(array('success'=>true, 'PersonSprTerrDop_id'=>$response));
			return true;
		}
		else
		{
			$this->ReturnData(array('success'=>false));
			return true;
		}
	}


	/**
	 * Получение одного из списков "Регион", "Район", "Город", "Нас. пункт", "Улица"
	 * Входящие данные: $_POST['country_id'],
	 *                  $_POST['level'],
	 *                  $_POST['value']
	 * На выходе: JSON-строка
	 * Используется: форма поиска ТАП (вкладка "Адрес")
	 */
    function getAddressCombo()
    {
		$data = $this->ProcessInputData('getAddressCombo', false);
		if ($data === false) return false;

		$response = $this->amodel->getAddressCombo($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
    }

	/**
	 *
	 * @return type 
	 */
	function loadAddressSpecObject(){
		$data = $this->ProcessInputData('loadAddressSpecObject', false);
		if ($data === false) return false;
		
		$response = $this->amodel->loadAddressSpecObject($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
	}
	
	/**
	 * Загрузка данных адреса у человека
	 */
	function loadAddressData()
    {
		$data = $this->ProcessInputData('loadAddressData', false);
		if ($data === false) return false;
		
		$response = $this->amodel->loadAddressData($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
    }


    /**
     * Поиск населенного пункта
     */
	function searchKLTown()
	{
		$data = $this->ProcessInputData('searchKLTown', false);
		if ($data === false) return false;

		$response = $this->amodel->searchKLTown($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
	}
	
    /**
     * Поиск города
     */
	function searchKLCity()
	{
		$data = $this->ProcessInputData('searchKLCity', false);
		if ($data === false) return false;

		$response = $this->amodel->searchKLCity($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
	}
	
	/**
     * Получение списка всех чаилдов
     */
	function loadChildLists()
	{
		$data = $this->ProcessInputData('loadChildLists', false);
		if ($data === false) return false;
		
		$KLChildLists = $this->amodel->loadChildLists($data);
		$response = array();
		if ( ($KLChildLists != false) && (count($KLChildLists) > 0) )
		{
			
			$response['data'] = array();
			if ( count($KLChildLists) == 0 )
			{
				$response['success'] = false;
			}
			else
			{
				$response['success'] = true;
			}
			foreach ($KLChildLists as $rows)
			{
				if ( !array_key_exists( $rows['KLAreaLevel_id'],  $response['data']) )
				{
					$response['data'][$rows['KLAreaLevel_id']] = array();
				}
				if ( !isset($rows['KLAdr_Ocatd']) )
					$rows['KLAdr_Ocatd'] = '';
				if ( !isset($rows['KLSocr_Nick']) )
					$rows['KLSocr_Nick'] = '';

				$arrRes = array(
					'KLArea_id' => $rows['KLArea_id'],
					'KLSocr_id' => $rows['KLSocr_id'],
					'KLArea_Name' => toUTF($rows['KLArea_Name']),
					'KLAdr_Ocatd' => $rows['KLAdr_Ocatd'],
					'KLSocr_Nick' => toUTF($rows['KLSocr_Nick'])
				);
				if($rows['KLAreaLevel_id'] == 5){
					//улицы
					$arrStreet = array(
						'AreaPID_Name' => (!empty($rows['AreaPID_Name'])) ? $rows['AreaPID_Name'] : '',
						'KLAreaLevel_pid' => (!empty($rows['KLAreaLevel_pid'])) ? $rows['KLAreaLevel_pid'] : '',
						'KLArea_pid' => (!empty($rows['KLArea_pid'])) ? $rows['KLArea_pid'] : ''
					);
					$arrRes = array_merge($arrRes, $arrStreet);
				}

				$response['data'][$rows['KLAreaLevel_id']][] = $arrRes;
				/*
				$response['data'][$rows['KLAreaLevel_id']][] = array(
					'KLArea_id' => $rows['KLArea_id'],
					'KLSocr_id' => $rows['KLSocr_id'],
					'KLArea_Name' => toUTF($rows['KLArea_Name']),
					'KLAdr_Ocatd' => $rows['KLAdr_Ocatd'],
					'KLSocr_Nick' => toUTF($rows['KLSocr_Nick'])
				);

				 */
			}
			$this->ReturnData($response);
		}
		else
			$this->ReturnData($response);
	}


    /**
	 * Добавление нового адреса за пределами России в БД
	 */
    function saveChildLists()
	{
        $save = false;
        
		$data = $this->ProcessInputData('saveChildLists', true);
		if ($data === false) return false;

        $save = $this->amodel->saveChildLists($data);

        if (is_array($save)) {
            $this->ReturnData(array('success' => true, 'Message' => toUTF('Данные о новом адресе успешно добавлены в БД'), 'AddressId'=>$save ));
			return true;
        } else {
            $this->ReturnData(array('success' => false, 'Message' => toUTF('Ошибка при попытке добавления нового адреса')));
			return false;
        }
    }


    /**
     * Получение списка областей
     */
    function getRegions()
	{
        $data = $this->ProcessInputData('getRegionsList', false);
		if ($data === false) return false;
		
        $RegionsList = $this->amodel->getRegionsList($data);
        $val = array();
        if ( $RegionsList != false && count($RegionsList) > 0 )
        {
			foreach ($RegionsList as $rows)
         	{
				$val[] = array('Region_id'=>trim($rows['KLArea_id']),
							'Socr_id'=>trim($rows['KLSocr_id']),
							'Region_Name'=>toUTF(trim($rows['KLArea_Name']))
				);
			}
   	        $this->ReturnData($val);
	    }
        else
        	ajaxErrorReturn();
	}


    /**
     * Получение списка районов
     */
    function getSubRegions()
	{
        $data = $this->ProcessInputData('getRegionsList', false);
		if ($data === false) return false;
		
        $SubRegionsList = $this->amodel->getSubRegionsList($data);
        $val = array();
        if ( $SubRegionsList != false && count($SubRegionsList) > 0 )
        {
			foreach ($SubRegionsList as $rows)
         	{
				$val[] = array('SubRGN_id'=>trim($rows['KLArea_id']),
							'Socr_id'=>trim($rows['KLSocr_id']),
							'SubRGN_Name'=>toUTF(trim($rows['KLArea_Name']))
				);
			}
   	        $this->ReturnData($val);
	    }
        else
        	ajaxErrorReturn();
	}


    /**
     * Получение списка городов
     */
    function getCities()
	{
        $data = $this->ProcessInputData('getCitiesList', false);
		if ($data === false) return false;
		
        $CityesList = $this->amodel->getCitiesList($data);
        $val = array();
        if ( $CityesList !== false )
        {
			foreach ($CityesList as $rows)
         	{
				$val[] = array('City_id'=>trim($rows['KLArea_id']),
							'Socr_id'=>trim($rows['KLSocr_id']),
							'City_Name'=>toUTF(trim($rows['KLArea_Name']))
				);
			}
   	        $this->ReturnData($val);
	    }
        else
        	ajaxErrorReturn();
	}


    /**
     * Получение списка населенных пунктов
     */
	public function getTowns(){
		$data = $this->ProcessInputData( 'getTownsList', false );
		if ( $data === false ) {
			return false;
		}
		
		$list = $this->amodel->getTownsList( $data );
		if ( $list !== false && sizeof( $list ) ) {
			$result = array();
			foreach( $list as $item ) {
				$result[] = array(
					'Town_id' => $item[ 'KLArea_id' ],
					'Socr_id' => $item[ 'KLSocr_id'],
					'Town_Name' => trim( $item[ 'KLArea_Name' ] ),
				);
			}
			$this->ReturnData( $result );
		} else {
			ajaxErrorReturn();
		}
	}

	/**
     * Получение списка улиц для комбобокса 
     */
	function getStreets()
	{
		$data = $this->ProcessInputData('getStreetsList', false);
		if ($data === false) return false;
		
        $StreetsList = $this->amodel->getStreetsList($data);
        $this->ProcessModelList($StreetsList, true, true)->ReturnData();
	}
	
    /**
     * Получение ОКАТО по адресу
     */
	function loadOkatoField()
	{
		$data = $this->ProcessInputData('loadOkatoField', true);
		if ($data === false) { return false; }
	
		$response = $this->amodel->loadOkatoField($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении ОКАТО')->ReturnData();
	}

	/**
	 * Получение списка территорий
	 */
	function getKLAreaList(){
		$data = $this->ProcessInputData('getKLAreaList', true);
		if ($data === false) { return false; }

		$response = $this->amodel->getKLAreaList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Поиск в кэше адресов по тексту
	 */
	function searchAddress()
	{
		$data = $this->ProcessInputData('searchAddress', false);
		if ($data === false) return false;
		
		$response = $this->amodel->searchAddress($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
	}

}
?>