<?php

$allowed_params = ['usePostgre','usePostgreLis','DBTYPE_ENV'];
$boolean_params = ['usePostgre','usePostgreLis','DBTYPE_ENV'];

$params = [];

foreach($_REQUEST as $param => $value) {
	if (!in_array($param, $allowed_params)) {
		continue;
	}
	$_value = '';
	if (in_array($param, $boolean_params)) {
		if (in_array($value, ['true','1'])) {
			$_value = '1';
		} else if (in_array($value, ['false','0'])) {
			$_value = '0';
		}
	}
	if ($_value === '') {
		setcookie($param, '', time() - 3600);
		$params[$param] = 'default';
	} else {
		setcookie($param, $_value);
		$params[$param] = $_value;
	}
}

foreach($allowed_params as $param) {
	if (!array_key_exists($param, $params)) {
		$params[$param] = isset($_COOKIE[$param])?$_COOKIE[$param]:'default';
	}
}

foreach($params as $param => $value) {
	echo "{$param}={$value}<br/>";
}