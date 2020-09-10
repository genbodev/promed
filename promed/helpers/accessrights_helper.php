<?php
/**
 * Формирование фильтров по кодам диагнозов на основе объектов доступа к диагнозам из сессии
 *
 * @param array|string $diag_code_fields
 * @param bool $as_array
 * @return array|string
 */
function getAccessRightsDiagFilter($diag_code_fields, $as_array = false) {
	$result = $as_array ? array() : '';

	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['diag'])) {
		return $result;
	}
	if (empty($diag_code_fields)) {
		return $result;
	}

	$AccessRightsDiag = $_SESSION['access_rights']['diag'];
	$filters = array();
	$single_codes = array();
	$codes_list = array();

	if (!is_array($diag_code_fields)) {
		$diag_code_fields = array($diag_code_fields);
	}

	foreach($AccessRightsDiag as $diag) {
		if (!$diag['hasAccess']) {
			if (empty($diag['Diag_tid'])) {
				$single_codes[] = $diag['Diag_fCode'];
			} else {
				$codes_list[] = array($diag['Diag_fCode'], $diag['Diag_tCode']);
			}
		}
	}
	foreach($diag_code_fields as $field) {
		if (count($single_codes) > 0) {
			$filters[] = "coalesce($field, '') not in('".implode("','",$single_codes)."')";
		}
		foreach($codes_list as $codes) {
			$filters[] = "coalesce($field, '') not between '{$codes[0]}' and '{$codes[1]}'";
		}
	}

	return $as_array ? $filters : implode(' and ', $filters);
}

/**
 * Формирование обратных фильтров по кодам диагнозов на основе объектов доступа к диагнозам из сессии
 *
 * @param array|string $diag_code_fields
 * @param bool $as_array
 * @return array|string
 */
function getRevertAccessRightsDiagFilter($diag_code_fields, $as_array = false) {
	$result = $as_array ? array() : '';

	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['diag'])) {
		return $result;
	}

	$AccessRightsDiag = $_SESSION['access_rights']['diag'];
	$filters = array();
	$single_codes = array();
	$codes_list = array();

	if (!is_array($diag_code_fields)) {
		$diag_code_fields = array($diag_code_fields);
	}

	foreach($AccessRightsDiag as $diag) {
		if (!$diag['hasAccess']) {
			if (empty($diag['Diag_tid'])) {
				$single_codes[] = $diag['Diag_fCode'];
			} else {
				$codes_list[] = array($diag['Diag_fCode'], $diag['Diag_tCode']);
			}
		}
	}
	foreach($diag_code_fields as $field) {
		if (count($single_codes) > 0) {
			$filters[] = "(coalesce($field, '') in('".implode("','",$single_codes)."'))";
		}
		foreach($codes_list as $codes) {
			$filters[] = "(coalesce($field, '') between '{$codes[0]}' and '{$codes[1]}')";
		}
	}

	return $as_array ? $filters : implode(' or ', $filters);
}

/**
 * Получение огранчений доступа к диагнозам для глобальных настроек
 *
 * @return array
 */
function getDeniedDiagOptions() {
	$result = array(
		'code_list' => array(),
		'code_range_list' => array()
	);
	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['diag'])) {
		return $result;
	}

	$AccessRightsDiag = $_SESSION['access_rights']['diag'];

	foreach($AccessRightsDiag as $diag) {
		if (!$diag['hasAccess']) {
			if (empty($diag['Diag_tid'])) {
				$result['code_list'][] = $diag['Diag_fCode'];
			} else {
				$result['code_range_list'][] = array($diag['Diag_fCode'], $diag['Diag_tCode']);
			}
		}
	}

	return $result;
}

/**
 * Проверка прав доступа к диагнозу
 *
 * @param string $Diag_Code
 * @return bool
 */
function checkDiagAccessRights($Diag_Code) {
	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['diag'])) {
		return true;
	}
	$AccessRightsDiag = $_SESSION['access_rights']['diag'];
	foreach($AccessRightsDiag as $diag) {
		if (!$diag['hasAccess']) {
			if (empty($diag['Diag_tid']) && $Diag_Code == $diag['Diag_fCode']) {
				return false;
			} else if ($Diag_Code >= $diag['Diag_fCode'] && $Diag_Code <= $diag['Diag_tCode']) {
				return false;
			}
		}
	}
	return true;
}

/**
 * Формирование фильтров по диагнозам на основе объектов доступа к диагнозам из сессии
 *
 * @param array|string $lpu_id_fields
 * @param bool $as_array
 * @return array|string
 */
function getAccessRightsLpuFilter($lpu_id_fields, $as_array = false) {
	$result = $as_array ? array() : '';

	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['lpu'])) {
		return $result;
	}

	$AccessRightsLpu = $_SESSION['access_rights']['lpu'];
	$filters = array();
	$lpu_list = array();

	if (!is_array($lpu_id_fields)) {
		$lpu_id_fields = array($lpu_id_fields);
	}

	foreach($AccessRightsLpu as $lpu) {
		if (!$lpu['hasAccess'] && $lpu['Lpu_id'] != $_SESSION['lpu_id']) {
			$lpu_list[] = $lpu['Lpu_id'];
		}
	}
	if (count($lpu_list) > 0) {
		foreach($lpu_id_fields as $field) {
			$filters[] = "coalesce($field, 0) not in(".implode(",", $lpu_list).")";
		}
	}

	return $as_array ? $filters : implode(' and ', $filters);
}

/**
 * Получение огранчений доступа к МО для глобальных настроек
 *
 * @return array
 */
function getDeniedLpuOptions() {
	$result = array();
	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['lpu'])) {
		return $result;
	}

	$AccessRightsLpu = $_SESSION['access_rights']['lpu'];

	foreach($AccessRightsLpu as $lpu) {
		if (!$lpu['hasAccess'] && $lpu['Lpu_id'] != $_SESSION['lpu_id']) {
			$result[] = $lpu['Lpu_id'];
		}
	}

	return $result;
}

/**
 * Формирование фильтров по подразделениям на основе объектов доступа к подразделениям из сессии
 *
 * @param array|string $lpu_building_id_fields
 * @param bool $as_array
 * @return array|string
 */
function getAccessRightsLpuBuildingFilter($lpu_building_id_fields, $as_array = false) {
	$result = $as_array ? array() : '';

	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['lpu_building'])) {
		return $result;
	}

	$AccessRightsLpuBuilding = $_SESSION['access_rights']['lpu_building'];
	$filters = array();
	$lpu_building_list = array();

	if (!is_array($lpu_building_id_fields)) {
		$lpu_building_id_fields = array($lpu_building_id_fields);
	}

	foreach($AccessRightsLpuBuilding as $lpu_building) {
		if (!$lpu_building['hasAccess']) {
			$lpu_building_list[] = $lpu_building['LpuBuilding_id'];
		}
	}
	if (count($lpu_building_list) > 0) {
		foreach($lpu_building_id_fields as $field) {
			$filters[] = "coalesce($field, 0) not in(".implode(",", $lpu_building_list).")";
		}
	}

	return $as_array ? $filters : implode(' and ', $filters);
}

/**
 * Получение огранчений доступа к подразделениям для глобальных настроек
 *
 * @return array
 */
function getDeniedLpuBuildingOptions() {
	$result = array();
	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['lpu_building'])) {
		return $result;
	}

	$AccessRightsLpuBuilding = $_SESSION['access_rights']['lpu_building'];

	foreach($AccessRightsLpuBuilding as $lpu_building) {
		if (!$lpu_building['hasAccess']) {
			$result[] = $lpu_building['LpuBuilding_id'];
		}
	}

	return $result;
}

/**
 * Получение огранчений доступа к тестам по СЗЗ для глобальных настроек
 *
 * @return array
 */
function getAccessRightsTestFilter($test_fields, $as_array = false, $in = false) {
	$result = $as_array ? array() : '';

	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['test'])) {
		return $result;
	}

	$AccessRightsTest = $_SESSION['access_rights']['test'];
	$filters = array();
	$UslugaComplex_ids = array();

	if (!is_array($test_fields)) {
		$test_fields = array($test_fields);
	}

	foreach($AccessRightsTest as $test) {
		if (!$test['hasAccess']) {
			if (!empty($test['UslugaComplex_id'])) {
				$UslugaComplex_ids[] = $test['UslugaComplex_id'];
			}
		}
	}

	foreach($test_fields as $field) {
		$filters[] = "coalesce($field, 0) ".($in?'':'not ')."in('".implode("','",$UslugaComplex_ids)."')";
	}

	return $as_array ? $filters : implode(' and ', $filters);
}


/**
 * Получение огранчений доступа к льготам
 *
 * @return array
 */
function getAccessRightsPrivilegeTypeFilter($PrivilegeType_fields, $as_array = false, $in = false) {
	$result = $as_array ? array() : '';

	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['privilege'])) {
		return $result;
	}

	$AccessRightsPrivilegeType = $_SESSION['access_rights']['privilege'];
	$filters = array();
	$PrivilegeType_ids = array();

	if (!is_array($PrivilegeType_fields)) {
		$PrivilegeType_fields = array($PrivilegeType_fields);
	}

	foreach($AccessRightsPrivilegeType as $privilege) {
		if (!$privilege['hasAccess'] && !empty($privilege['PrivilegeType_id'])) {
			$PrivilegeType_ids[] = $privilege['PrivilegeType_id'];
		}
	}

	foreach($PrivilegeType_fields as $field) {
		$filters[] = "coalesce($field, 0) ".($in?'':'not ')."in('".implode("','",$PrivilegeType_ids)."')";
	}

	return $as_array ? $filters : implode(' and ', $filters);
}

/**
 * Проверка прав доступа к льготам
 *
 * @param string $PrivilegeType_id
 * @return bool
 */
function checkPrivilegeTypeAccessRights($PrivilegeType_id) {
	if (empty($_SESSION['access_rights']) || empty($_SESSION['access_rights']['privilege'])) {
		return true;
	}
	$AccessRightsPrivilege = $_SESSION['access_rights']['privilege'];
	foreach($AccessRightsPrivilege as $privilege) {
		if (!$privilege['hasAccess'] && $PrivilegeType_id == $privilege['PrivilegeType_id']) {
			return false;
		}
	}
	return true;
}