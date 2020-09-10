<?php

/**
 * Input_helper - хелпер, с функциями помогающими принимать и обрабатывать входящие параметры
 * вынесено из Main_helper.php
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      20.07.2009
 */
/**
 * Проверка что значение переменной содержит идентификатор
 *
 * Очевидно, что значение переменной содержит идентификатор если
 * 1. Оно вообще задано.
 * 2. Оно является целым числом
 * 3. Оно больше нуля
 *
 * @access	public
 * @param	int
 * @return	boolean
 */
/**
 * Night 06.02.2011
 *
 * Добавлена обработка типа checkbox для проверки логического типа поля и при необходимости преобразования в числа (0,1).
 */
/**
 * Lich 11.12.2009
 *
 * Добавлены правила minValue и maxValue в обработку входящих параметров.
 * Работают для типов int и date. 
 * Для date задается строкой в формате 'гггг-мм-дд', можно написать слово now, будет означать текущий день
 */
/**
 * Alf 23.04.2009
 *
 * Для типа date добавлена возможность загружать minValue из другого поля
 * Задается строкой вида 'field: <field_name>', где <field_name> - имя поля формы, из которого брать минимальное значение даты
 * 
 */
define("MIN_CORRECT_DATE", "1800-01-01"); //минимальная дата, считающаяся корректной

/**
 * Заполняет входящими параметрами массив $data из $_POST, при этом проводит проверку параметров по переданным правилам $inputRules
 * 
 * Если все хорошо - возвращает пустую строку, иначе возвращает строку со списком ошибок
 * 
 * @param array $data Массив, в который помещаются параметры. Может быть уже не пустой
 * @param array $inputRules Массив с правилами проверки
 * @param boolean $convertUTF8 Преобразовывать параметры из UTF8, по умолчанию да
 * @param null $inData Если не задано, берется $_POST
 * 
 * @return string Возвращает строку с ошибкой или пустую строку если ошибок нет
 */
function getInputParams( &$data, $inputRules, $convertUTF8 = true, $inData = null, $useCiCheckStrength = false ) {
	//$useCiCheckStrengh - чтобы не ломать предыдущую логику, создадим новый флаг для проверки входных данных
	//стандартным CI-чекером, если данные переданны не через POST
	// $data = array(); // очищаем массив перед заполнением
	if ( !$inData ) {
		$useCiCheck = true;

		if ( $convertUTF8 && count($_POST) > 0 ) {
			array_walk_recursive($_POST, 'ConvertFromUTF8ToWin1251');
		}
	} else {
		$useCiCheck = false;

		if ( $convertUTF8 == true && is_array($inData) && count($inData) > 0 ) {
			//array_walk($inData, 'ConvertFromUTF8ToWin1251');
		}
	}

	if ($useCiCheckStrength && $inData) {
		$_POST = $inData;
	}

	$err = ""; // строка с описанием ошибки
	$CI = & get_instance();
	$CI->load->helper("Date");
	$CI->load->library("form_validation");
	$CI->form_validation->reset_validation();
	$CI->form_validation->set_rules($inputRules);
	
	//если имеем дело с постом,
	if ( $useCiCheck || $useCiCheckStrength) {
		//Сначала проверяем встроенным валидаторов CI
		if ( $CI->form_validation->run() == FALSE ) {
			$err = validation_errors();
			return $err;
		}
		$inData = $_POST;
	}

	foreach ( $inputRules as $rule ) { // проходим по всем правилам
		if ( isset($rule['rules']) && strpos($rule['rules'], 'dropifnotcome') !== false && !array_key_exists($rule['field'], $inData) ) {
			// если задано отбрасывать параметр если он не пришел с клиента, то так и делаем
			unset($data[$rule['field']]);
		} else {

			// переопределяем тип, чтобы избавиться от значения по умолчанию
			if (!empty($GLOBALS['transformCheckboxInputType']) && $rule['type'] === 'swcheckbox') {
				$rule['type'] = 'int';
			}

			switch ( $rule['type'] ) { //
				case 'id': // идентификатор, целое число больше нуля
					if ( !isset($inData[$rule['field']]) || $inData[$rule['field']] == '' ) { // если значение не передано через POST
						// если данные уже заданы, например в сессии, то не надо брать не значение по дефолту, 
						// и не обнулять значение - просто оставляем, то что есть 
						if ( (!isset($data[$rule['field']])) || (empty($data[$rule['field']])) || ($data[$rule['field']] == 0) ) {
							if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
								$data[$rule['field']] = $rule['default'];
							} else { // иначе по умолчанию ставим NULL
								$data[$rule['field']] = NULL;
							}
						}
					} else {
						// Обходим баг в IE, преобразуем текстовое значение null
						if ( strtolower($inData[$rule['field']]) == 'null' ) {
							$data[$rule['field']] = NULL;
						} else {
							if ( (!is_numeric($inData[$rule['field']])) || ($inData[$rule['field']] < 0) ) {
								$err .= "Неверный идентификатор в поле " . $rule['field'] . " (error) \n";
							} else {
								if ( $inData[$rule['field']] > 0 ) {
									$data[$rule['field']] = $inData[$rule['field']];
								} else {
									if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
										$data[$rule['field']] = $rule['default'];
									} else { // иначе по умолчанию ставим NULL
										$data[$rule['field']] = NULL;
									}
								}
							}
						}
					}

					if (!empty($rule['equalsession'])) {
						// поле должно быть таким же как в сессиионных параметрах
						$sp = getSessionParams();
						if (isset($data[$rule['field']]) && isset($sp[$rule['field']]) && $data[$rule['field']] == $sp[$rule['field']]) {
							// ок, доступ разрешён
						} else {
							$err .= "Нет доступа к " . $rule['field'] . " = " . $inData[$rule['field']] . "\n";
						}
					}

					if (!empty($rule['checklpu']) && isset($data[$rule['field']])) {
						// значение должно быть из своей МО
						$sp = getSessionParams();
						$field = mb_strtolower($rule['field']);
						$object = null;

						switch($field) {
							case 'evn_id':
							case 'evn_pid':
							case 'evnplbase_id':
							case 'evnvizitpl_id':
							case 'evnplstom_id':
							case 'evnvizitplstom_id':
							case 'evndiagplstom_id':
							case 'evnuslugastom_id':
							case 'diagplstomsop_id':
							case 'evnuslugaoper_id':
							case 'evnstickbase_id':
								$object = 'Evn';
								break;
							case 'lpusection_id':
								$object = 'LpuSection';
								break;
							case 'medstafffact_id':
							case 'medstafffact_sid':
								$object = 'MedStaffFact';
								break;
						}

						if (!empty($object)) {
							$CI->load->model('Common_model');
							if (!$CI->Common_model->checkRecordInLpu(array(
								'object' => $object,
								'value' => $data[$rule['field']],
								'Lpu_id' => $sp['Lpu_id']
							))) {
								$err .= "Не существует записи для " . $rule['field'] . " = " . $inData[$rule['field']] . "\n";
							}
						}
					}

					if (!empty($rule['checkobject']) && isset($data[$rule['field']])) {
						// значение должно быть из своей МО
						$sp = getSessionParams();
						$field = mb_strtolower($rule['field']);
						$CI->load->model('Common_model');
						if (!$CI->Common_model->checkRecordInLpu(array(
							'object' => $rule['checkobject'],
							'value' => $data[$rule['field']],
							'Lpu_id' => $sp['Lpu_id']
						))) {
							$err .= "Не существует записи для " . $rule['field'] . " = " . $inData[$rule['field']] . "\n";
						}
					}
					break;
				case 'multipleid': // идентификаторы, целые числа больше нуля через запятую
					if ( !isset($inData[$rule['field']]) || $inData[$rule['field']] == '' ) { // если значение не передано через POST
						// если данные уже заданы, например в сессии, то не надо брать не значение по дефолту,
						// и не обнулять значение - просто оставляем, то что есть
						if ( (!isset($data[$rule['field']])) || (empty($data[$rule['field']])) || ($data[$rule['field']] == 0) ) {
							if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
								$data[$rule['field']] = $rule['default'];
							} else { // иначе по умолчанию ставим NULL
								$data[$rule['field']] = NULL;
							}
						}
					} else {
						// Обходим баг в IE, преобразуем текстовое значение null
						if ( strtolower($inData[$rule['field']]) == 'null' ) {
							$data[$rule['field']] = NULL;
						} else {
							if ( (!preg_match('/^[0-9,]*$/', $inData[$rule['field']])) ) { // только запятые и цифры
								$err .= "Неверная группа идентификаторов в поле " . $rule['field'] . " (error) \n";
							} else {
								$data[$rule['field']] = array();
								$ids = explode(',', $inData[$rule['field']]);
								foreach($ids as $key => $id) {
									if ($id < 0) {
										$err .= "Неверная группа идентификаторов в поле " . $rule['field'] . " (error) \n";
									} else if ($id > 0) {
										$data[$rule['field']][] = $id;
									}
								}
							}
						}
					}
					break;
				case 'checkbox': // значение чекбокса (on/off)
					if ( !isset($inData[$rule['field']]) || $inData[$rule['field']] == '' ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим 0
							$data[$rule['field']] = 'off';
						}
					} else {
						$data[$rule['field']] = $inData[$rule['field']];
					}
					if ( strlen(trim($data[$rule['field']])) == 0 || $data[$rule['field']] === false || $data[$rule['field']] == 'false' ) {
						$data[$rule['field']] = 'off';
					}

					if ( strtolower($data[$rule['field']]) == 'on' ) {
						$data[$rule['field']] = 1;
					} elseif ( strtolower($data[$rule['field']]) == 'off' ) {
						$data[$rule['field']] = 0;
					}
					if ( ($data[$rule['field']] < 0) || ($data[$rule['field']] > 1) ) {
						$err .= "Неверное значение в поле " . $rule['label'] . "\n";
					}
					break;
				case 'swcheckbox': // значение чекбокса с сылкой на поле YesNo: 1 = нет, 2 = да
					if ( !isset($inData[$rule['field']]) || $inData[$rule['field']] == '' || $inData[$rule['field']] === 0 || $inData[$rule['field']] === '0') { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим 0
							$data[$rule['field']] = 'off';
						}
					} else {
						$data[$rule['field']] = $inData[$rule['field']];
					}
					if ( strlen(trim($data[$rule['field']])) == 0 || $data[$rule['field']] === false || $data[$rule['field']] === 'false' ) {
						$data[$rule['field']] = 'off';
					}
					if ( $data[$rule['field']] === true || $data[$rule['field']] === 'true' || $data[$rule['field']] === 1 | $data[$rule['field']] === '1' ) {
						$data[$rule['field']] = 'on';
					}

					if ( strtolower($data[$rule['field']]) == 'on' ) {
						$data[$rule['field']] = 2;
					} elseif ( strtolower($data[$rule['field']]) == 'off' ) {
						$data[$rule['field']] = 1;
					}
					if ( ($data[$rule['field']] < 0) || ($data[$rule['field']] > 2) ) {
						$err .= "Неверное значение в поле " . $rule['label'] . "\n";
					}
					break;

				case 'api_flag': // передают 0 или 1, конвертируем в YesNO (1 или 2)
					if ( !isset($inData[$rule['field']]) || $inData[$rule['field']] == '' ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе NULL
							$data[$rule['field']] = null;
						}
					} else {
						$data[$rule['field']] = $inData[$rule['field']];
					}

					if ($data[$rule['field']] === 1 || $data[$rule['field']] === '1') {
						// либо ДА
						$data[$rule['field']] = 2;
					} elseif ($data[$rule['field']] === 0 || $data[$rule['field']] === '0') {
						// либо НЕТ
						$data[$rule['field']] = 1;
					} else if (empty($data[$rule['field']])) {
						// либо не передали или передали пустое
						$data[$rule['field']] = null;
					} else {
						// либо некорректное значение
						$err .= "Неверное значение в поле " . $rule['label'] . "\n";
					}
					break;

				case 'api_flag_nc': // передают 0 или 1, конвертируется в YesNo вручную
					if ( !isset($inData[$rule['field']]) || $inData[$rule['field']] == '' ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе NULL
							$data[$rule['field']] = null;
						}
					} else {
						$data[$rule['field']] = $inData[$rule['field']];
					}

					if ($data[$rule['field']] === 1 || $data[$rule['field']] === '1') {
						// либо ДА
						$data[$rule['field']] = 1;
					} elseif ($data[$rule['field']] === 0 || $data[$rule['field']] === '0') {
						// либо НЕТ
						$data[$rule['field']] = 0;
					} else if (empty($data[$rule['field']])) {
						// либо не передали или передали пустое
						$data[$rule['field']] = null;
					} else {
						// либо некорректное значение
						$err .= "Неверное значение в поле " . $rule['label'] . "\n";
					}
					break;

				case 'int': // целое число
					if ( !isset($inData[$rule['field']]) || (isset($inData[$rule['field']]) && $inData[$rule['field']] === '') ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим NULL
							$data[$rule['field']] = NULL;
						}
					} else {
						// Проверяем переданное значение на соответствие целому числу
						if ( isset($inData[$rule['field']]) && is_numeric($inData[$rule['field']]) ) {
							if ( $inData[$rule['field']] == floor($inData[$rule['field']]) ) {
								$data[$rule['field']] = $inData[$rule['field']];
							} else {
								$err .= "Должно быть целое число в поле " . $rule['label'] . "\n";
							}
						} else {
							$err .= "Неверное числовое значение в поле " . $rule['label'] . "\n";
						}
					}
					if ( isset($rule['maxValue']) && isset($data[$rule['field']]) ) {
						if ( $data[$rule['field']] > $rule['maxValue'] ) {
							$err .= "Выход за границы диапазона в поле " . $rule['label'] . "\n";
						}
					}
					if ( isset($rule['minValue']) && isset($data[$rule['field']]) ) {
						if ( $data[$rule['field']] < $rule['minValue'] ) {
							$err .= "Выход за границы диапазона в поле " . $rule['label'] . "\n";
						}
					}
					break;
				case 'float': // число
					if ( !isset($inData[$rule['field']]) || (isset($inData[$rule['field']]) && $inData[$rule['field']] == '') ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим NULL
							$data[$rule['field']] = NULL;
						}
					} else {
						// Проверяем переданное значение на соответствие числу
						if ( isset( $inData[ $rule[ 'field' ] ] ) ) {
							$tmp_field = floatval( str_replace( ',', '.', $inData[ $rule[ 'field' ] ] ) );
							if ( is_numeric( $tmp_field ) ) {
								$data[ $rule[ 'field' ] ] = $inData[ $rule[ 'field' ] ];
							}
						} else {
							$err .= "Неверное числовое значение в поле ".$rule[ 'label' ]."\n";
						}
					}

					if ( isset($rule['maxValue']) && isset($data[$rule['field']]) ) {
						if ( $data[$rule['field']] > $rule['maxValue'] ) {
							$err .= "Выход за границы диапазона в поле " . $rule['label'] . "\n";
						}
					}
					if ( isset($rule['minValue']) && isset($data[$rule['field']]) ) {
						if ( $data[$rule['field']] < $rule['minValue'] ) {
							$err .= "Выход за границы диапазона в поле " . $rule['label'] . "\n";
						}
					}
					break;
				case 'string': // строка
					if ( !isset($inData[$rule['field']]) || (isset($inData[$rule['field']]) && $inData[$rule['field']] == '' && !(isset($rule['rules']) && strpos($rule['rules'], 'notnull') !== false)) ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим NULL
							$data[$rule['field']] = NULL;
						}
					} else
					// Проверяем переданное значение на соответствие числу
					if ( isset($inData[$rule['field']]) && is_string($inData[$rule['field']]) )
						$data[$rule['field']] = $inData[$rule['field']];
					else {
						$err .= "Неверное строковое значение в поле " . $rule['label'] . "\n";
					}
					// проверяем правило ban_percent (удаление знаков процента)
					if ( isset($rule['rules']) && strpos($rule['rules'], 'ban_percent') !== false && strlen($data[$rule['field']]) > 0 && preg_match("/[%_]/i", $data[$rule['field']]) !== false ) {
						// удаляем везде знаки процента
						$data[$rule['field']] = trim(str_replace(array('%','_'), '', $data[$rule['field']]));
						// проверяем, пустая ли строка получилась
						if ( strlen($data[$rule['field']]) == 0 ) {
							// если значение обязательно
							if ( isset($rule['rules']) && ((strpos($rule['rules'], 'required') !== false) || (strpos($rule['rules'], 'min_length') !== false)) ) {
								if ( isset($rule['default']) && strlen($rule['default']) > 0 )
									$data[$rule['field']] = $rule['default'];
								else
									$err .= "Поле " . $rule['label'] . " обязательно для заполнения (использование знаков % и _  недопустимо).\n";
							} else {
								if ( isset($rule['default']) )
									$data[$rule['field']] = $rule['default'];
								else
									$data[$rule['field']] = NULL;
							}
						}
					}
					if ( isset($rule['rules']) && strpos($rule['rules'], 'max_length') !== false && preg_match('/max_length\[(.*)\]/', $rule['rules'], $match) ) {
						$max_length = $match[1];
						if (mb_strlen($data[$rule['field']]) > $max_length) {
							$err .= "Длина поля {$rule['label']} не может превышать {$max_length} символов.\n";
						}
					}
					break;
				case 'engstring': // английская строка
					if ( !isset($inData[$rule['field']]) || (isset($inData[$rule['field']]) && $inData[$rule['field']] == '') ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим NULL
							$data[$rule['field']] = NULL;
						}
					} else
					// Проверяем переданное значение на соответствие числу
					if ( isset($inData[$rule['field']]) && is_string($inData[$rule['field']]) )
						$data[$rule['field']] = $inData[$rule['field']];
					else {
						$err .= "Неверное строковое значение в поле " . $rule['label'] . "\n";
					}
					// проверяем правило ban_percent (удаление знаков процента)
					if ( isset($rule['rules']) && strpos($rule['rules'], 'ban_percent') !== false && strlen($data[$rule['field']]) > 0 && preg_match("/[%_]/i", $data[$rule['field']]) !== false ) {
						// удаляем везде знаки процента
						$data[$rule['field']] = trim(str_replace(array('%','_'), '', $data[$rule['field']]));
						// проверяем, пустая ли строка получилась
						if ( strlen($data[$rule['field']]) == 0 ) {
							// если значение обязательно
							if ( isset($rule['rules']) && ((strpos($rule['rules'], 'required') !== false) || (strpos($rule['rules'], 'min_length') !== false)) ) {
								if ( isset($rule['default']) && strlen($rule['default']) > 0 )
									$data[$rule['field']] = $rule['default'];
								else
									$err .= "Поле " . $rule['label'] . " обязательно для заполнения (использование знаков % и _  недопустимо).\n";
							} else {
								if ( isset($rule['default']) )
									$data[$rule['field']] = $rule['default'];
								else
									$data[$rule['field']] = NULL;
							}
						}
					}
					break;
				case 'russtring': // русская строка
					if ( !isset($inData[$rule['field']]) || (isset($inData[$rule['field']]) && $inData[$rule['field']] == '') ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим NULL
							$data[$rule['field']] = NULL;
						}
					} else
					// Проверяем переданное значение на соответствие числу
					if ( isset($inData[$rule['field']]) && is_string($inData[$rule['field']]) )
						$data[$rule['field']] = $inData[$rule['field']];
					else {
						$err .= "Неверное строковое значение в поле " . $rule['label'] . "\n";
					}
					// проверяем правило ban_percent (удаление знаков процента)
					if ( isset($rule['rules']) && strpos($rule['rules'], 'ban_percent') !== false && strlen($data[$rule['field']]) > 0 && preg_match("/[%_]/i", $data[$rule['field']]) !== false ) {
						// удаляем везде знаки процента
						$data[$rule['field']] = trim(str_replace(array('%','_'), '', $data[$rule['field']]));
						// проверяем, пустая ли строка получилась
						if ( strlen($data[$rule['field']]) == 0 ) {
							// если значение обязательно
							if ( isset($rule['rules']) && ((strpos($rule['rules'], 'required') !== false) || (strpos($rule['rules'], 'min_length') !== false)) ) {
								if ( isset($rule['default']) && strlen($rule['default']) > 0 )
									$data[$rule['field']] = $rule['default'];
								else
									$err .= "Поле " . $rule['label'] . " обязательно для заполнения (использование знаков % и _  недопустимо).\n";
							} else {
								if ( isset($rule['default']) )
									$data[$rule['field']] = $rule['default'];
								else
									$data[$rule['field']] = NULL;
							}
						}
					}
					break;
				case 'date': // дата
					if ( !isset($inData[$rule['field']]) || strlen($inData[$rule['field']]) == 0 ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим NULL
							$data[$rule['field']] = NULL;
						}
					} else {
						// Проверяем переданное значение на соответствие дате в формате dd.mm.yyyy
						if ( isset($inData[$rule['field']]) && $inData[$rule['field']] == '__.__.____' ) {
							if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
								$data[$rule['field']] = $rule['default'];
							} else { // иначе по умолчанию ставим NULL
								$data[$rule['field']] = NULL;
							}
						} else {
							if ( CheckDateFormat($inData[$rule['field']]) == 0 || $inData[$rule['field']] == '' ) {
								if ( isset($rule['convertIntoObject']) && $rule['convertIntoObject'] ) {
									try {
										$data[$rule['field']] = new DateTime($inData[$rule['field']]);
									} catch ( Exception $e ) {
										$err .= "Неверно задана дата в поле " . $rule['label'] . "\n";
										$data[$rule['field']] = NULL;
									}
								} else {
									$data[$rule['field']] = ConvertDateFormat($inData[$rule['field']]);
									if ( $data[$rule['field']] < MIN_CORRECT_DATE ) {
										$err .= "Неверно задана дата в поле " . $rule['label'] . "\n";
										$data[$rule['field']] = NULL;
									}
								}
							} else {
								$err .= "Неверное значение даты в поле " . $rule['label'] . "\n";
							}
						}
					}

					if ( isset($rule['maxValue']) && isset($data[$rule['field']]) ) {
						if ( $rule['maxValue'] != 'now' ) {
							$dt = strtotime($rule['maxValue']);
						} else {
							$dt = strtotime(date('Y-m-d'));
						}

						if ( strtotime($data[$rule['field']]) > $dt ) {
							$err .= "Выход за границы диапазона в поле " . $rule['label'] . "\n";
						}
					}
					if ( isset($rule['minValue']) && isset($data[$rule['field']]) ) {
						if ( $rule['minValue'] != 'now' ) {
							// Признак того, что значением является другое значение из $inData
							$field_mark = 'field:';
							// За ним должно следовать имя поля формы
							$field_name = substr($rule['minValue'], strlen($field_mark));
							$field_value = $inData[trim($field_name)];
							if ( $field_value ) {
								$dt = strtotime($field_value);
							} else {
								$dt = strtotime($rule['maxValue']);
							}
						} else {
							$dt = strtotime(date('Y-m-d'));
						}
						if ( strtotime($data[$rule['field']]) < $dt ) {
							$err .= "Выход за границы диапазона в поле " . $rule['label'] . "\n";
						}
					}
					break;
				case 'datetime': // дата со временем
					if ( !isset($inData[$rule['field']]) || strlen($inData[$rule['field']]) == 0 ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим NULL
							$data[$rule['field']] = NULL;
						}
					} else {
						// Проверяем переданное значение на соответствие дате в формате dd.mm.yyyy
						if ( isset($inData[$rule['field']]) && $inData[$rule['field']] == '__.__.____ __:__' ) {
							if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
								$data[$rule['field']] = $rule['default'];
							} else { // иначе по умолчанию ставим NULL
								$data[$rule['field']] = NULL;
							}
						} else {
							$parsed = DateTime::createFromFormat('??? M d Y H:i:s +', $inData[$rule['field']]);
							if ( $parsed ) {
								$data[$rule['field']] = date_format($parsed, 'Y-m-d H:i:s');
							} else {
								$parsed = DateTime::createFromFormat('d.m.Y H:i:s', $inData[$rule['field']]);
								if ( $parsed ) {
									$data[$rule['field']] = date_format($parsed, 'Y-m-d H:i:s');
								} else {
									$parsed = DateTime::createFromFormat('d.m.Y H:i', $inData[$rule['field']]);
									if ( $parsed ) {
										$data[$rule['field']] = date_format($parsed, 'Y-m-d H:i:s');
									} else {
										$parsed = DateTime::createFromFormat('Y-m-d?H:i:s', $inData[$rule['field']]);
										if ( $parsed ) {
											$data[$rule['field']] = date_format($parsed, 'Y-m-d H:i:s');
										} else {
											$parsed = DateTime::createFromFormat('Y-m-d\TH:i:sP', $inData[$rule['field']]);
											if ( $parsed ) {
												$data[$rule['field']] = date_format($parsed, 'Y-m-d H:i:s');
											} else {
												if (CheckDateTimeFormat($inData[$rule['field']]) == 0 || $inData[$rule['field']] == '') {
													$data[$rule['field']] = ConvertDateTimeFormat($inData[$rule['field']]);
													if ($data[$rule['field']] < MIN_CORRECT_DATE) {
														$err .= "Неверно задана дата в поле " . $rule['label'] . "\n";
														$data[$rule['field']] = NULL;
													}
												} else {
													$err .= "Неверное значение даты в поле " . $rule['label'] . "\n";
												}
											}
										}
									}
								}
							}
						}
					}

					if ( isset($rule['maxValue']) && isset($data[$rule['field']]) ) {
						if ( $rule['maxValue'] != 'now' ) {
							$dt = strtotime($rule['maxValue']);
						} else {
							$dt = strtotime(date('Y-m-d H:i'));
						}

						if ( strtotime($data[$rule['field']]) > $dt ) {
							$err .= "Выход за границы диапазона в поле " . $rule['label'] . "\n";
						}
					}
					if ( isset($rule['minValue']) && isset($data[$rule['field']]) ) {
						if ( $rule['minValue'] != 'now' ) {
							// Признак того, что значением является другое значение из $inData
							$field_mark = 'field:';
							// За ним должно следовать имя поля формы
							$field_name = substr($rule['minValue'], strstr($rule['minValue'], $field_mark) + strlen($field_mark));
							$field_value = $inData[trim($field_name)];
							if ( $field_value ) {
								$dt = strtotime($field_value);
							} else {
								$dt = strtotime($rule['maxValue']);
							}
						} else {
							$dt = strtotime(date('Y-m-d H:i'));
						}
						if ( strtotime($data[$rule['field']]) < $dt ) {
							$err .= "Выход за границы диапазона в поле " . $rule['label'] . "\n";
						}
					}
					break;
				case 'daterange': // диапазон дат
					if ( !isset($inData[$rule['field']]) || (isset($inData[$rule['field']]) && $inData[$rule['field']] == '') ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим null
							$data[$rule['field']][0] = NULL;
							$data[$rule['field']][1] = NULL;
						}
					} else {
						// Проверяем переданное значение на соответствие диапазону дате в формате dd.mm.yyyy - dd.mm.yyyy
						if ( isset($inData[$rule['field']]) && (preg_match('/^\d|_{2}\.\d|_{2}\.\d|_{4} - \d|_{2}\.\d|_{2}\.\d|_{4}$/', $inData[$rule['field']]) || $inData[$rule['field']] == '') ) {
							$data[$rule['field']] = ExplodeTwinDate($inData[$rule['field']]);
							if ( ( isset($data[$rule['field']][0]) && $data[$rule['field']][0] < MIN_CORRECT_DATE) || ( isset($data[$rule['field']][1]) && $data[$rule['field']][1] < MIN_CORRECT_DATE) ) {
								$err .= "Неверно задана дата в поле " . $rule['label'] . "\n";
								$data[$rule['field']][0] = NULL;
								$data[$rule['field']][1] = NULL;
							}
						} else {
							$err .= "Неверное значение диапазона дат в поле " . $rule['label'] . "\n";
						}
					}
					break;
				case 'time': // время
					if ( !isset($inData[$rule['field']]) || (isset($inData[$rule['field']]) && ( $inData[$rule['field']] == '' || $inData[$rule['field']] == "__:__" )) ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим 00:00
							$data[$rule['field']] = null;
						}
					} else {
						// Проверяем переданное значение на соответствие времени в формате hh:mm
						if ( isset($inData[$rule['field']]) && (preg_match('/^\d{2}\:\d{2}$/', $inData[$rule['field']])) || $inData[$rule['field']] == '' ) {
							$time = explode(':', $inData[$rule['field']]);
							if ( $time[0] >= 0 && $time[0] < 24 && $time[1] >= 0 && $time[1] < 60 ) {
								$data[$rule['field']] = $inData[$rule['field']];
							} else {
								$err .= "Неверное значение времени в поле " . $rule['label'] . "\n";
							}
						} else {
							$err .= "Неверное значение времени в поле " . $rule['label'] . "\n";
						}
					}
					break;
				case 'time_with_seconds': // время с секундами
					if ( !isset($inData[$rule['field']]) || (isset($inData[$rule['field']]) && ( $inData[$rule['field']] == '' || $inData[$rule['field']] == "__:__:__" ) ) ) { // если значение не передано через POST
						if ( isset($rule['default']) ) { // если задано значение по умолчанию то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию ставим 00:00:00
							$data[$rule['field']] = '00:00:00';
						}
					} else {
						// Проверяем переданное значение на соответствие времени в формате hh:mm:ss
						if ( isset($inData[$rule['field']]) && (preg_match('/^\d{2}\:\d{2}:\d{2}$/', $inData[$rule['field']])) || $inData[$rule['field']] == '' ) {
							$time = explode(':', $inData[$rule['field']]);
							if ( $time[0] >= 0 && $time[0] < 24 && $time[1] >= 0 && $time[1] < 60 && $time[2] >= 0 && $time[2] < 60 ) {
								$data[$rule['field']] = $inData[$rule['field']];
							} else {
								$err .= "Неверное значение времени в поле " . $rule['label'] . "\n";
							}
						} else {
							$err .= "Неверное значение времени в поле " . $rule['label'] . "\n";
						}
					}
					break;
				case 'snils': // СНИЛС
					// если значение не передано через POST
					if ( empty($inData[$rule['field']]) || $inData[$rule['field']] == "___-___-___-__" ) {
						if ( !empty($rule['default']) ) { // если задано значение по умолчанию, то берем его
							$data[$rule['field']] = $rule['default'];
						} else { // иначе по умолчанию NULL
							$data[$rule['field']] = null;
						}
					} else {
						$data[$rule['field']] = $inData[$rule['field']];
					}

					if ( !empty($data[$rule['field']]) ) {
						$data[$rule['field']] = preg_replace("/[ \-]+/", "", $data[$rule['field']]);

						// Проверяем, чтобы переданное значение иди значение по-умолчанию состояло из 11 цифр
						if ( !preg_match('/^\d{11}$/', $data[$rule['field']]) ) {
							$err .= "Номер СНИЛС должен содержать 11 символов\n";// #106230#174
						}
					}
					break;
				case 'array':
					if ( isset($inData[$rule['field']]) && is_array($inData[$rule['field']])) {
						$data[$rule['field']] = array();
						foreach ( $inData[$rule['field']] as $k => $v ) {
							if ( !empty($v) ) {
								$data[$rule['field']][] = $v;
							}
						}
					} else {
						$data[$rule['field']] = array();
					}

					break;
				case 'json_array':
					if ( !empty($inData[$rule['field']]) ) {
						$assoc = !empty($rule['assoc']) && (bool)$rule['assoc'];
						$data[$rule['field']] = json_decode($inData[$rule['field']], $assoc, 512, JSON_BIGINT_AS_STRING);
						if ( $data[$rule['field']] === NULL ) { //Не удалось преобразовать json в массив
							$err .= " Ошибка преобразования JSON-массива " . $rule['label'] . "\n";
						}
						
						if (empty($data[$rule['field']])) {
							$data[$rule['field']] = array();
						}
					} else {
						$data[$rule['field']] = empty($rule['default']) ? array() : $rule['default'];
					}

					break;
				case 'boolean':
					$value = array_key_exists( $rule[ 'field' ], $inData ) ? $inData[ $rule[ 'field' ] ] : ( array_key_exists( 'default', $rule ) ? $rule['default'] : null );
					$data[ $rule[ 'field' ] ] = $value;

					// The value representing true status. Defaults to '1'.
					$true_value = !empty( $rule['trueValue'] ) ? $rule['trueValue'] : '1';
					// The value representing false status. Defaults to '0'.
					$false_value = !empty( $rule['falseValue'] ) ? $rule['falseValue'] : '0';
					// Whether the comparison to trueValue and falseValue is strict.
					$strict = !empty( $rule['strict'] ) ? $rule['strict'] : false;

					// Если значение обязательно
					$allow_empty = isset( $rule[ 'rules' ] ) && strpos( $rule[ 'rules' ], 'required' ) !== false ? false : true;
					// Функция empty тут не подходит
					$is_empty = $value === null || $value === array() || $value === '';

					if ( !$allow_empty && $is_empty ) {
						$err .= 'Поле ' . $rule[ 'label' ] . ' обязательно для заполнения'."\n";
					} elseif ( !$strict && $value != $true_value && $value != $false_value
							|| $strict && $value !== $true_value && $value !== $false_value ) {
						if ( !$allow_empty && $is_empty ) {
							$err .= 'Поле '.$rule[ 'label' ].' должно быть '.$true_value.' или '.$false_value."\n";
						}
					}
					unset( $value );
				break;
				case 'assoc_array':
					if ( isset($inData[$rule['field']]) && is_array($inData[$rule['field']])) {
						foreach ( $inData[$rule['field']] as $k => $v ) {
							$data[$rule['field']][$k] = $v;
						}
					} else {
						$data[$rule['field']] = array();
					}
					break;
				default:
					$err .= "Неправильный тип поля " . $rule['field'] . " - " . $rule['type'] . "\n";
					break;
			}
		}


		//Если значение пришедшее от клиента пустое и есть поле в сессии, из которого можно взять значение
		if ( !isset($data[$rule['field']]) && isset($rule['session_value']) ) {
			if ( isset($_SESSION[$rule['session_value']]) ) {
				$data[$rule['field']] = $_SESSION[$rule['session_value']];
			}
		}
	}
	return $err;
}

/**
 * Возвращает ассоциативный массив с $_SESSION['lpu_id'], $_SESSION['server_id'], $_SESSION['pmuser_id']
 * 
 * @return	array
 */
function getSessionParams() {
	$ret = Array();
	if ( (isset($_SESSION['lpu_id'])) && (is_numeric($_SESSION['lpu_id'])) && ($_SESSION['lpu_id'] > 0) )
		$ret['Lpu_id'] = $_SESSION['lpu_id'];
	else
		$ret['Lpu_id'] = 0;

	if ( (isset($_SESSION['OrgFarmacy_id'])) && (is_numeric($_SESSION['OrgFarmacy_id'])) && ($_SESSION['OrgFarmacy_id'] > 0) )
		$ret['OrgFarmacy_id'] = $_SESSION['OrgFarmacy_id'];
	else
		$ret['OrgFarmacy_id'] = 0;

	if ( (isset($_SESSION['Contragent_id'])) && (is_numeric($_SESSION['Contragent_id'])) && ($_SESSION['Contragent_id'] > 0) )
		$ret['Contragent_id'] = $_SESSION['Contragent_id'];
	else
		$ret['Contragent_id'] = 0;

	if ( (isset($_SESSION['Mol_id'])) && (is_numeric($_SESSION['Mol_id'])) && ($_SESSION['Mol_id'] > 0) )
		$ret['Mol_id'] = $_SESSION['Mol_id'];
	else
		$ret['Mol_id'] = 0;

	if ( (isset($_SESSION['server_id'])) && (is_numeric($_SESSION['server_id'])) && ($_SESSION['server_id'] > 0) )
		$ret['Server_id'] = $_SESSION['server_id'];
	else
		$ret['Server_id'] = 0;

	if ( (isset($_SESSION['pmuser_id'])) && (is_numeric($_SESSION['pmuser_id'])) && ($_SESSION['pmuser_id'] > 0) )
		$ret['pmUser_id'] = $_SESSION['pmuser_id'];
	else
	// TODO: Разлогиниться по идее надо.. и выкинуть пользователя на первую страницу
		die('Критическая ошибка!'); // this shit should never happened

	if ( (isset($_SESSION['MedStaffFact_id'])) && (is_numeric($_SESSION['MedStaffFact_id'])) && ($_SESSION['MedStaffFact_id'] > 0) )
		$ret['MedStaffFact_id'] = $_SESSION['MedStaffFact_id'];
	else
		$ret['MedStaffFact_id'] = NULL;

	if ( (isset($_SESSION['lpuorg_id'])) && (is_numeric($_SESSION['lpuorg_id'])) && ($_SESSION['lpuorg_id'] > 0) )
		$ret['LpuOrg_id'] = $_SESSION['lpuorg_id'];
	else
		$ret['LpuOrg_id'] = NULL;

	if ( (isset($_SESSION['FarmacyOtdel_id'])) && (is_numeric($_SESSION['FarmacyOtdel_id'])) && ($_SESSION['FarmacyOtdel_id'] > 0) )
		$ret['FarmacyOtdel_id'] = $_SESSION['FarmacyOtdel_id'];
	else
		$ret['FarmacyOtdel_id'] = NULL;
	/*
	  if ((isset($_SESSION['medpersonal_id'])) && (is_numeric($_SESSION['medpersonal_id'])) && ($_SESSION['medpersonal_id'] > 0))
	  $ret['MedPersonal_id'] = $_SESSION['medpersonal_id'];
	  else
	  $ret['MedPersonal_id'] = NULL;
	 */
	// Сделано согласно http://redmine.swan.perm.ru/issues/4439#note-8. Соответственно в моделях используем $data['session'] вместо $_SESSION
	// TODO (closed): Если в результате доработок понадобится сессия не используя данную функцию (getSessionParams), эту строку можно будет перенести либо в базовый контроллер, либо в getInputParams
	// Примечание: перенесено в swController (ProcessInputData)
	// TODO: Пока оставил, поскольку не везде еще рефакторинг проведен, потом надо будет убрать!
	if ( isset($_SESSION) ) {
		$ret['session'] = $_SESSION;
	}
	return $ret;
}

/**
 * Конвертирует значения в массиве параметров, предназначенных для передачи в SQL-запрос
 * 
 * @return	true
 */
function getSQLParams( &$arr ) {
	reset($arr);
	while ( list($key, $value) = each($arr) ) {
		if ( $value == 'NULL' ) {
			$arr[$key] = NULL;
		}
	}
	return true;
}

/**
 * Возвращает массив по правилам для входящих параметров для вкладки Адрес на формах поиска
 * 
 * @return array
 */
function getAddressSearchFilter() {
	return array(
		array(
			'field' => 'KLAreaType_id',
			'label' => 'Территория',
			'rules' => 'trim',
			'type' => 'id'
		),
		array(
			'field' => 'KLCountry_id',
			'label' => 'Страна',
			'rules' => 'trim',
			'type' => 'id'
		),
		array(
			'field' => 'KLRgn_id',
			'label' => 'Регион',
			'rules' => 'trim',
			'type' => 'id'
		),
		array(
			'field' => 'KLSubRgn_id',
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
		),
		array(
			'field' => 'Address_House',
			'label' => 'Номер дома',
			'rules' => 'trim',
			'type' => 'string'
		)
	);
}

/**
 * Правила описаны в общем хэлпере, так как используются в нескольких классах
 */
function getMorbusOnkoSpecificsRules() {
	return array(
		'load' => array(
			array('field' => 'Morbus_id', 'label' => 'Идентификатор заболевания', 'rules' => '', 'type' => 'int'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'int'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор учетного документа', 'rules' => '', 'type' => 'int'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'int'),
			array('fild' => 'Evn_pid', 'label' => 'Идентификатор КВС', 'rules' => '', 'type' => 'int'),
		),
		'save' => array(
			//ОнкоСпецифика заболевания
			array('field' => 'Morbus_id', 'label' => 'Идентификатор заболевания                                       ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_id', 'label' => 'NULL                                                            ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_firstSignDT', 'label' => 'Дата появления первых признаков заболевания                     ', 'rules' => '', 'type' => 'date'),
			array('field' => 'MorbusOnko_firstVizitDT', 'label' => 'Дата первого обращения                                          ', 'rules' => '', 'type' => 'date'),
			array('field' => 'Lpu_foid', 'label' => 'В какое медицинское учреждение                                  ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_NumCard', 'label' => 'Порядковый номер регистрационной карты                          ', 'rules' => '', 'type' => 'string'),
			array('field' => 'OnkoRegType_id', 'label' => 'Взят на учет в ОД                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoRegOutType_id', 'label' => 'Причина снятия с учета                                          ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoLesionSide_id', 'label' => 'Сторона поражения                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoDiag_mid', 'label' => 'Морфологический тип опухоли. (Гистология опухоли)               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_NumHisto', 'label' => 'Номер гистологического исследования                             ', 'rules' => '', 'type' => 'string'),
			array('field' => 'OnkoT_id', 'label' => 'T                                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoN_id', 'label' => 'N                                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoM_id', 'label' => 'M                                                               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorStage_id', 'label' => 'Стадия опухолевого процесса                                     ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoUnknown', 'label' => 'Локализация отдаленных метастазов: Неизвестна                   ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoLympha', 'label' => 'Локализация отдаленных метастазов: Отдаленные лимфатические узлы', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoBones', 'label' => 'Локализация отдаленных метастазов: Кости                        ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoLiver', 'label' => 'Локализация отдаленных метастазов: Печень                       ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoLungs', 'label' => 'Локализация отдаленных метастазов: Легкие и/или плевра          ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoBrain', 'label' => 'Локализация отдаленных метастазов: Головной мозг                ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoSkin', 'label' => 'Локализация отдаленных метастазов: Кожа                         ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoKidney', 'label' => 'Локализация отдаленных метастазов: Почки                        ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoOvary', 'label' => 'Локализация отдаленных метастазов: Яичники                      ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoPerito', 'label' => 'Локализация отдаленных метастазов: Брюшина                      ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoMarrow', 'label' => 'Локализация отдаленных метастазов: Костный мозг                 ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoOther', 'label' => 'Локализация отдаленных метастазов: Другие органы                ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsTumorDepoMulti', 'label' => 'Множественные                                                   ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsDiagConfUnknown', 'label' => 'Метод подтверждения диагноза: Неизвестен                        ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsDiagConfMorfo', 'label' => 'Метод подтверждения диагноза: Морфологический                ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsDiagConfCito', 'label' => 'Метод подтверждения диагноза: Цитологический                    ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsDiagConfExplo', 'label' => 'Метод подтверждения диагноза: Эксплоротивная операция           ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsDiagConfLab', 'label' => 'Метод подтверждения диагноза: Лабораторно-инструментальный      ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_IsDiagConfClinic', 'label' => 'Метод подтверждения диагноза: Только клинический                ', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorCircumIdentType_id', 'label' => 'Обстоятельства выявления опухоли                                ', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoLateDiagCause_id', 'label' => 'Причины поздней диагностики                                     ', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorAutopsyResultType_id', 'label' => 'Результат аутопсии применительно к данной опухоли               ', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorPrimaryTreatType_id', 'label' => 'Проведенное лечение первичной опухоли                           ', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorRadicalTreatIncomplType_id', 'label' => 'Причины незавершенности радикального лечения                    ', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnko_specSetDT', 'label' => 'Дата начала специального лечения                                ', 'rules' => '', 'type' => 'date'),
			array('field' => 'MorbusOnko_specDisDT', 'label' => 'Дата окончания специального лечения                             ', 'rules' => '', 'type' => 'date'),
			array('field' => 'MorbusOnko_IsMainTumor', 'label' => 'Призак основной опухоли', 'rules' => '', 'type' => 'int'),
			array('field' => 'MorbusOnko_setDiagDT', 'label' => 'Дата установления дигноза', 'rules' => '', 'type' => 'date'),
			//ОнкоСпецифика общего заболевания
			array('field' => 'MorbusOnkoBase_id', 'label' => 'Идентификатор онкоспецифики общего заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusBase_id', 'label' => 'Общее заболевание', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnkoBase_NumCard', 'label' => 'Порядковый номер регистрационной карты', 'rules' => '', 'type' => 'string'),
			array('field' => 'OnkoInvalidType_id', 'label' => 'Инвалидность по основному (онкологическому) заболеванию', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnkoBase_deadDT', 'label' => 'Дата смерти', 'rules' => '', 'type' => 'date'),
			array('field' => 'Diag_did', 'label' => 'Диагноз причины смерти', 'rules' => '', 'type' => 'id'),
			array('field' => 'MorbusOnkoBase_deathCause', 'label' => 'Причина смерти', 'rules' => '', 'type' => 'id'),
			array('field' => 'AutopsyPerformType_id', 'label' => 'Аутопсия', 'rules' => '', 'type' => 'id'),
			array('field' => 'TumorPrimaryMultipleType_id', 'label' => 'Первично-множественная опухоль', 'rules' => '', 'type' => 'id'),
			//array('field' => 'MorbusOnkoBase_IsLateComplication','label' => 'Поздние осложнения лечения'                             ,'rules' => '', 'type' => 'id'),
			//ОнкоСпецифика на человеке
			array('field' => 'MorbusOnkoPerson_id', 'label' => 'NULL', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Человек', 'rules' => '', 'type' => 'id'),
			array('field' => 'Ethnos_id', 'label' => 'Этническая группа', 'rules' => '', 'type' => 'id'),
			array('field' => 'OnkoOccupationClass_id', 'label' => 'Социально-профессиональная группа', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLAreaType_id', 'label' => 'Житель', 'rules' => '', 'type' => 'id'),
			//гриды
			array('field' => 'MorbusOnkoBasePersonState', 'label' => 'Состояние пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'MorbusOnkoBaseLateComplTreat', 'label' => 'Поздние осложнения лечения', 'rules' => '', 'type' => 'string'),
			array('field' => 'MorbusOnkoBaseStatusYearEnd', 'label' => 'Состояние на конец отчетного периода', 'rules' => '', 'type' => 'string'),
		)
	);
}

/**
 * Правило описаны в общем хэлпере, так как используется в нескольких классах
 */
function getsaveEvnUslugaComplexOrderRule() {
	return array(
		array(
			'field' => 'object',
			'label' => 'Объект',
			'rules' => '',
			'type' => 'string'
		),
		array(
			'field' => 'EvnUsluga_id',
			'label' => 'Идентификатор комплексной услуги',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'EvnUsluga_pid',
			'label' => 'Идентификатор родителя',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'Lpu_id',
			'label' => 'ЛПУ',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'PrehospDirect_id',
			'label' => 'Идентификатор типа направления',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'Lpu_did',
			'label' => 'Идентификатор ЛПУ заказавшего услугу',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'LpuSection_did',
			'label' => 'Идентификатор отделения ЛПУ заказавшего услугу',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'MedPersonal_did',
			'label' => 'Идентификатор врача заказавшего услугу',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'Org_did',
			'label' => 'Идентификатор организации заказавшей услугу',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'EvnUslugaComplex_setDate',
			'label' => 'Дата оказания комплексной услуги',
			'rules' => '',
			'type' => 'date'
		),
		array(
			'field' => 'EvnUslugaComplex_setTime',
			'label' => 'Время оказания комплексной услуги',
			'rules' => '',
			'type' => 'time'
		),
		array(
			'field' => 'Lpu_uid',
			'label' => 'ЛПУ, которому назначается оказание услуги',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'Org_uid',
			'label' => 'Другая организация',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'LpuSection_uid',
			'label' => 'Отделение, которому назначается оказание услуги',
			'rules' => 'trim',
			'type' => 'id'
		),
		array(
			'field' => 'MedPersonal_id',
			'label' => 'Врач',
			'rules' => 'trim',
			'type' => 'id'
		),
		array(
			'field' => 'Usluga_isCito',
			'label' => 'Cito',
			'rules' => 'required',
			'type' => 'id'
		),
		array(
			'field' => 'time_table',
			'label' => 'Тип расписания (параклиника, поликлиника, стационар)',
			'rules' => 'trim',
			'type' => 'string'
		),
		array(
			'field' => 'TimetablePar_id',
			'label' => 'Идентификатор записи расписания параклиники',
			'rules' => 'trim',
			'type' => 'id'
		),
		array(
			'field' => 'TimetableMedService_id',
			'label' => 'Идентификатор записи расписания службы/услуги',
			'rules' => 'trim',
			'type' => 'id'
		),
		array(
			'field' => 'TimetableResource_id',
			'label' => 'Идентификатор записи расписания ресурса',
			'rules' => 'trim',
			'type' => 'id'
		),
		array(
			'field' => 'Person_id',
			'label' => 'Идентификатор человека',
			'rules' => 'required',
			'type' => 'id'
		),
		array(
			'field' => 'PersonEvn_id',
			'label' => 'Идентификатор состояния человека',
			'rules' => 'required',
			'type' => 'id'
		),
		array(
			'field' => 'Server_id',
			'label' => 'Идентификатор сервера',
			'rules' => 'required',
			'type' => 'int'
		),
		array(
			'field' => 'UslugaComplex_id',
			'label' => 'Услуга',
			'rules' => '',
			'type' => 'id'
		),
		array(
			'field' => 'checked',
			'label' => 'пометки',
			'rules' => '',
			'type' => 'string'
		),
		array(
			'field' => 'PayType_id',
			'label' => 'Вид оплаты',
			'rules' => '',
			'type' => 'id'
		)
	);
}
