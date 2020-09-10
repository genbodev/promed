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
class Address4E extends swController{
	/**
	 * @desc
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model("Address_model4E", "amodel");

		$this->inputRules = array(
			'getCitiesFromName' => array(
				array(
					'field' => 'query',
					'label' => 'Наименование города',
					'rules' => '',
					'type' => 'string'
				),				
				array(
					'field' => 'region_id',
					'label' => 'Номер региона',
					'rules' => '',
					'type' => 'int'
				),		
				array(
					'field' => 'KLSubRGN_id',
					'label' => 'Номер региона',
					'rules' => '',
					'type' => 'int'
				),				
				array(
					'field' => 'city_default',
					'label' => 'Город по умолчанию',
					'rules' => '',
					'type' => 'int'
				),				
				array(
					'field' => 'region_name',
					'label' => 'Наименование района по умолчанию',
					'rules' => '',
					'type' => 'string'
				),

				// Доп. поля
				array(
					'field' => 'KLRgn_id',
					'label' => 'Номер региона',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'label' => 'Номер региона',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Номер подстанции',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Номер ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'label' => 'Номер региона',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'showUnformalizedAdresses',
					'label' => 'Отображать неформализованные адреса',
					'rules' => '',
					'type' => 'int'
				),
			),
			'getStreetsFromName'  => array(
				array(
					'field' => 'query',
					'label' => 'Наименование улицы',
					'rules' => '',
					'type' => 'string'
				),				
				array(
					'field' => 'town_id',
					'label' => 'ID города',
					'rules' => '',
					'type' => 'int'
				),			
				array(
					'field' => 'street_id',
					'label' => 'ID улицы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'lat',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'lng',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'unf_addr',
					'label' => 'неформ ид',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'лпу ид',
					'rules' => '',
					'type' => 'int'
				)				
			),
			'getAllStreetsFromCity' => array(
				array(
					'field' => 'town_id',
					'label' => 'ID города',
					'rules' => 'required',
					'type' => 'int'
				),				
				array(
					'field' => 'Lpu_id',
					'label' => 'лпу ид',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'UnformalizedAddressType_id',
					'label' => 'ID типа адреса',
					'rules' => '',
					'type' => 'int'
				),	
			),
			'getAddressFromLpuID' => array(),
			'getAddressFromLpuBuildingID' => array(
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Номер подстанции',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getRegions' => array(
				array('field' => 'country_id', 'label' => 'Идентификатор страны','rules' => '','type' => 'id')
			),
			'getSubRegions' => array(
				array('field' => 'region_id','label' => 'Идентификатор региона','rules' => '','type' => 'id')
			),
			'getCities' => array(
				array('field' => 'subregion_id','label' => 'Идентификатор региона','rules' => '','type' => 'id')
			),
			'getTowns' => array(
				array('field' => 'city_id','label' => 'Идентификатор города','rules' => '','type' => 'id')
			),
			'getStreets' => array(
				array('field' => 'town_id','label' => 'Идентификатор города','rules' => '','type' => 'id'),
			)

		);
	}

	/**
	 * @desc 
	 */
	function getAddressFromLpuBuildingID()
	{
		$data = $this->ProcessInputData('getAddressFromLpuBuildingID', true);

		$response = $this->amodel->getAddressFromLpuBuildingID($data);
		if ($response)
		{
			$this->ReturnData(array('success'=>true,
				'address'=>$response['address'],
				'LpuBuilding_setDefaultAddressCity' => $response['setDefaultAddressCity']
				));
			return true;
		}
	}	
	/**
	 * @desc 
	 */
	function getAddressFromLpuID()
	{
		$data = $this->ProcessInputData('getAddressFromLpuID', true);

		$response = $this->amodel->getAddressFromLpuID($data);
		if ($response)
		{
			$this->ReturnData(array('success'=>true,
				'address'=>$response
				));
			return true;
		}
	}
	/**
	 * @desc
	 */
	function getCitiesFromName()
	{
		$data = $this->ProcessInputData('getCitiesFromName', true);
        if ($data === false) return false;

		$TownsList = $this->amodel->getCitiesFromName($data);
		$val = array();
		if ( $TownsList != false && count($TownsList) > 0 )
		{
			foreach ($TownsList as $rows)
			{

				$val[] = array(
					'Town_id'=>trim($rows['KLArea_id']),
					'Area_pid'=>trim($rows['KLArea_pid']),

					'Area_Name'=>toUTF(trim($rows['KLArea_Name'])),

					'Socr_Nick'=>toUTF(trim($rows['KLSocr_Nick'])),

					'Socr_Name'=>toUTF(trim($rows['KLSocr_Name'])),
					//'Socr_id'=>trim($rows['KLSocr_id']),
					'Town_Name'=>toUTF(trim($rows['KLArea_Name'])),
					'Region_Name'=>toUTF(trim($rows['pKLArea_Name'])),
					'Region_Socr'=>toUTF(trim($rows['region'])),
					'Region_Nick'=>toUTF(trim($rows['regionSocr'])),
					'KLAreaStat_id'=>toUTF(trim($rows['KLAreaStat_id'])),
					'KLAreaLevel_id'=>toUTF(trim($rows['KLAreaLevel_id'])),
					'Region_id'=>trim($rows['Region_id']),
					'UAD_id' => $rows['UAD_id']
				);
			}
			$this->ReturnData($val);
		}
		else
			ajaxErrorReturn();
	}


	/**
	 * @desc
	 */
	function getStreetsFromName()
	{
		$data = $this->ProcessInputData('getStreetsFromName', false);
		if ($data === false) return false;

		$StreetsList = $this->amodel->getStreetsFromName($data);
		$val = array();
		if ( $StreetsList != false && count($StreetsList) > 0 )
		{
			foreach ($StreetsList as $rows)
			{

				$val[] = array(
					'StreetAndUnformalizedAddressDirectory_id'=>trim($rows['StreetAndUnformalizedAddressDirectory_id']),
					'UnformalizedAddressDirectory_id'=>trim($rows['UnformalizedAddressDirectory_id']),

					'StreetAndUnformalizedAddressDirectory_Name'=>toUTF(trim($rows['StreetAndUnformalizedAddressDirectory_Name'])),

					'KLStreet_id'=>toUTF(trim($rows['KLStreet_id'])),
					'Socr_Nick'=>toUTF(trim($rows['Socr_Nick'])),
					'lat'=>toUTF(trim($rows['lat'])),
					'lng'=>toUTF(trim($rows['lng']))
				);
			}
			$this->ReturnData($val);
		}
		else
			ajaxErrorReturn();
	}

	/**
	 * @desc
	 */
	function getAllStreetsFromCity()
	{
		$data = $this->ProcessInputData('getAllStreetsFromCity', false);
		if ($data === false) return false;

		$StreetsList = $this->amodel->getAllStreetsFromCity($data);
		$val = array();
		if ( $StreetsList != false && count($StreetsList) > 0 )
		{
			foreach ($StreetsList as $rows)
			{

				$val[] = array(
					'StreetAndUnformalizedAddressDirectory_id'=>trim($rows['StreetAndUnformalizedAddressDirectory_id']),
					'UnformalizedAddressDirectory_id'=>trim($rows['UnformalizedAddressDirectory_id']),
					'UnformalizedAddressType_id'=>trim($rows['UnformalizedAddressType_id']),
					'KLRGN_id'=>trim($rows['KLRGN_id']),
					'KLSubRGN_id'=>trim($rows['KLSubRGN_id']),
					'KLTown_id'=>trim($rows['KLTown_id']),
					'StreetAndUnformalizedAddressDirectory_Name'=>toUTF(trim($rows['StreetAndUnformalizedAddressDirectory_Name'])),
					'Address_Name'=>toUTF(trim($rows['Address_Name'])),
					'KLStreet_id'=>toUTF(trim($rows['KLStreet_id'])),
					'Socr_Nick'=>toUTF(trim($rows['Socr_Nick'])),
					'lat'=>toUTF(trim($rows['lat'])),
					'lng'=>toUTF(trim($rows['lng'])),
					'LpuBuilding_id'=>trim($rows['LpuBuilding_id']),
					'AddressOfTheObject'=>trim($rows['AddressOfTheObject']),
					'UnformalizedAddressDirectory_StreetDom'=>toUTF(trim($rows['UnformalizedAddressDirectory_StreetDom'])),
					'StreetSearch_Name'=>trim($rows['StreetSearch_Name'])
				);
			}
			$this->ReturnData($val);
		}
		else
			ajaxErrorReturn();
	}


	/**
	 * Получение списка областей
	 */
	public function getRegions()
	{
		$data = $this->ProcessInputData('getRegions', false);
		if ($data === false) return false;

		$response = $this->amodel->getRegions($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 * Получение списка районов
	 */
	public function getSubRegions()
	{
		$data = $this->ProcessInputData('getSubRegions', false);
		if ($data === false) return false;
		$response = $this->amodel->getSubRegions($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 * Получение списка городов
	 */
	public function getCities()
	{
		$data = $this->ProcessInputData('getCities', false);
		if ($data === false) return false;
		$response = $this->amodel->getCities($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 * Получение списка населенных пунктов
	 */
	public function getTowns(){
		$data = $this->ProcessInputData( 'getTowns', false );
		if ( $data === false ) {
			return false;
		}
		$response = $this->amodel->getTowns($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка улиц для комбобокса 
	 */
	public function getStreets()
	{
		$data = $this->ProcessInputData('getStreets', false);
		if ($data === false) return false;
		$response = $this->amodel->getStreets($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

}
?>