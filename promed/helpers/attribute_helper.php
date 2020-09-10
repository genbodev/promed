<?php
/**
 * Получение входных данных для загрузки атрибутов
 * @param $data
 * @return array|bool
 */
function processInputAttributesData($data) {
	$attrObjects = false;
	if (!empty($data['attrObjects'])) {
		$attrObjects = json_decode($data['attrObjects'], true);
		if (!is_array($attrObjects) || count($attrObjects) == 0) {
			$attrObjects = false;
		}
	}
	return $attrObjects;
}

/**
 * Получение идентификаторов записей из входных данных для загрузки значений атрибутов
 * @param $data
 * @param $attrObjects
 * @return array|bool
 */
function processInputIdentData($data, $attrObjects) {
	$attrIdents = array();
	foreach($attrObjects as $item) {
		if ( !empty($item['identField']) && !empty($data[$item['identField']]) ) {
			$attrIdents[$item['object']] = $data[$item['identField']];
		}
	}
	if (count($attrIdents) == 0) {
		$attrIdents = false;
	}
	return $attrIdents;
}

/**
 * Получение данных атрибутов
 * @param $attrObjects
 * @param bool $attrIdents
 * @return array
 */
function getAttributesData($attrObjects, $attrIdents = false) {
	$response = array(
		'attributes' => array(),
		'attrValues' => array(),
		'tableDirectInfo' => array(),
		'tableDirectData' => array()
	);

	$ci = & get_instance();
	if ($ci->usePostgreRegistry) {
		unset($ci->db);
	}
	$ci->load->database();
	$ci->load->model('Attribute_model');

	$attrParams = array('attrObjects' => array());
	$identFields = array();
	foreach($attrObjects as $item) {
		$attrParams['attrObjects'][] = $item['object'];
		$identFields[$item['object']] = $item['identField'];
	}

	$response['attributes'] = $ci->Attribute_model->getAttributesForObject($attrParams);

	$tableDirectInfoList = array();
	if (is_array($response['attributes'])) {
		foreach($response['attributes'] as &$item) {
			$item['Attribute_IdentField'] = $identFields[$item['AttributeVision_TableName']];
			if ($attrIdents) {
				if (empty($attrIdents[$item['AttributeVision_TableName']])) {
					$item['AttributeValue_ValueIdent'] = null;
				} else {
					$item['AttributeValue_ValueIdent'] = $attrIdents[$item['AttributeVision_TableName']];
				}
			}

			if (!empty($item['Attribute_TablePKey'])) {
				$tableDirectInfoList = $item['Attribute_TablePKey'];
			}
		}

		if ($attrIdents) {
			$response['attrValues'] = $ci->Attribute_model->getAttributesValues($response['attributes']);
			if (!$response['attrValues']) {$response['attrValues'] = array();}

			$values = array();
			foreach($response['attrValues'] as $value) {
				$key = $value['Attribute_SysNick'];
				$values[$key] = $value;
			}
			$emptyValue = array('AttributeValue_id' => null, 'AttributeValue_Value' => null);
			foreach($response['attributes'] as &$attribute) {
				$key = $attribute['Attribute_SysNick'];
				if (isset($values[$key])) {
					$attribute = array_merge($attribute, $values[$key]);
				} else {
					$attribute = array_merge($attribute, $emptyValue);
				}
			}
		}

		if (count($tableDirectInfoList) > 0) {
			$ci->load->model('TableDirect_model');
			$response['tableDirectData'] = $ci->TableDirect_model->loadTableDirectData($tableDirectInfoList);
		}
	}
	return $response;
}

/**
 * Подификация выходных данных с учетом найденных атрибутов
 * @param $outData
 * @param $attributesData
 * @return array
 */
function modifyOutDataWithAttributes($outData, $attributesData) {
	if (count($attributesData['attributes']) > 0) {
		$outData = array(
			'fieldsData' => $outData,
			'attributes' => $attributesData['attributes'],
			'metaData' => array(
				'root' => 'fieldsData',
				'fields' => array()
			)
		);

		if (count($attributesData['tableDirectData']) > 0) {
			$outData['tableDirectData'] = $attributesData['tableDirectData'];
		}

		if (count($attributesData['attrValues']) > 0) {
			foreach($attributesData['attrValues'] as $item) {
				$outData['fieldsData'][0][$item['Attribute_SysNick']] = $item['AttributeValue_Value'];
			}
		}

		$fields = array();
		foreach($outData['fieldsData'][0] as $fieldName => $field) {
			$fields[] = array('name' => $fieldName);
		}
		$outData['metaData']['fields'] = $fields;
	}
	return $outData;
}

/**
 * Сохранение значений атрибутов
 * @param $data
 * @param $savedData
 * @return array
 */
function saveAttributes($data, $savedData) {
	$attributes = json_decode($data['attributes'], true);

	$ci = & get_instance();
	$ci->load->database();
	$ci->load->model('Attribute_model');

	$attrResponse = array();
	foreach($attributes as &$attribute) {
		$key = $attribute['Attribute_SysNick'];
		$attribute['pmUser_id'] = $data['pmUser_id'];
		$identField = $attribute['identField'];
		if (!empty($savedData[$identField])) {
			$attribute['AttributeValue_ValueIdent'] = $savedData[$identField];
			$attrResponse[$key] = $ci->Attribute_model->saveAttributeValue($attribute);
		} else {
			$attrResponse[$key] = array('Error_Msg' => 'Не найден идентификатор записи, которой принадлежит атрибут');
		}
		if (!empty($attrResponse[$key]['Error_Msg'])) {
			print_r($attrResponse[$key]);exit;
		}
	}
	return $attrResponse;
}