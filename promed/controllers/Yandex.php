<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Yandex - контроллер для работы с Yandex api
 *
 */

class Yandex extends swController {

	public $inputRules = array(
		'getCoordinates' => array(
			array(	
				
				'field' => 'Address_AddressEdit',
				'label' => 'Адрес здания',
				'rules' => '',
				'type'  => 'float'
			)
		)
	);

	/**
     * Получение координат из адреса (Геокодирование)
     */
	
	function getCoordinates() {

		$data = $this->ProcessInputData('getCoordinates', true,false,false,true);
		if ($data === false) {return false;}

		$params = array(
    		
    		'geocode' => $data['Address_AddressEdit'], 	  // адрес
    		'format'  => 'json',                          // формат ответа
    		'results' => 1                                // количество выводимых результатов                  // ваш api key
		);
		
		$response = json_decode( @file_get_contents('https://geocode-maps.yandex.ru/1.x/?' . http_build_query($params, '', '&')));

		if ($response !== null && $response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {

			$text = $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
			$coordinates = explode(' ' , $text);
			$coordinates = array('lng' => $coordinates[0], 'lat' => $coordinates[1], 'message' => '');
		
		} else {
		
			$coordinates = array('lng' => '', 'lat' => '', 'message' => 'координаты не были найдены по данному адресу');
		}

		echo json_encode($coordinates);
	}
}