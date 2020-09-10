<?php
/**
 * @param callable $errorHandler Функция-обработчик ошибки
 * @param mixed|array $params Один параметр или несколько параметров в массиве
 */
function registerShutdownErrorHandler($errorHandler, $params = array()) {
	register_shutdown_function(function($errorHandler, $params) {
		if (is_array($params)) {
			call_user_func_array($errorHandler, $params);
		} else {
			call_user_func($errorHandler, $params);
		}
	}, $errorHandler, $params);
}