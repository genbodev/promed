<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Библиотека для экспорта XML-файлов
 * @author Stanislav Bykov (stanislav.bykov@rtmis.ru)
 */
class XmlExporter {
	/**
	 * Инстанс для работы с БД
	 * Передается через конструктор
	 */
	private $db;

	/**
	 * Параметры по-умолчанию для XML-файла
	 */
	private $xmlDefaults = [
		/**
		 * Описание файла
		 * Может задаваться пользователем
		 * @default ''
		 */
		'description' => '',

		/**
		 * Шаблон базовой части имени файла
		 * Задается пользователем
		 */
		'signTemplate' => '',

		/**
		 * Расширение файла
		 * Может задаваться пользователем
		 * @default 'xml'
		 */
		'ext' => 'xml',

		/**
		 * Структура XML
		 * Задается пользователем
		 */
		'map' => [],

		/**
		 * Источник данных для шаблона базовой части имени файла
		 * Может задаваться пользователем
		 */
		'dataProvider' => '',

		/**
		 * Полное имя файла
		 * Получается путем конкатенации обработанного signTemplate и ext
		 */
		'name' => '',

		/**
		 * Количество записей, накапливаемых для записи во временный файл
		 * Используется для файлов с типом slave
		 * Может задаваться пользователем
		 * @default 0
		 */
		'recLimit' => 0,

		/**
		 * Количество символов Tab перед тегами
		 * Может задаваться пользователем
		 * @default 0
		 */
		'tabs' => 0,

		/**
		 * Объявление начала XML-файла
		 * Может задаваться пользователем
		 * @default '<?xml version="1.0" encoding="windows-1251" ?>'
		 */
		'xmlDeclaration' => [
			'attributes' => [
				'version' => [ 'value' => '1.0' ],
				'encoding' => [ 'value' => 'windows-1251' ],
			],
		],
	];

	/**
	 * Параметры по-умолчанию для ZIP-архива
	 */
	private $zipDefaults = [
		/**
		 * Шаблон базовой части имени ZIP-файла
		 * Задается пользователем
		 * Обязательный параметр
		 */
		'signTemplate' => '',

		/**
		 * Расширение ZIP-файла
		 * Может задаваться пользователем
		 * @default 'zip'
		 */
		'ext' => 'zip',

		/**
		 * Источник данных для шаблона базовой части имени ZIP-файла
		 * Задается пользователем
		 */
		'dataProvider' => '',

		/**
		 * Полное имя ZIP-файла
		 * Получается путем конкатенации обработанного signTemplate и ext
		 */
		'name' => '',

		/**
		 * Индексы файлов, которые необходимо включить в ZIP-архив
		 * Обязательный параметр
		 */
		'files' => [],
	];

	/**
	 * Параметры файлов с данными
	 */
	private $exportFiles = [];

	/**
	 * Параметры ZIP-файла
	 */
	private $zipParams = [];

	/**
	 * Массив данных для файлов
	 */
	private $filesData = [];

	/**
	 * Ошибка экспорта
	 */
	private $lastError = '';

	/**
	 * Правила для подстановки значений в запросах
	 */
	private $queryMap = [];

	/**
	 * Конфиг для источников данных
	 */
	private $dataProvidersConfig = [
		'commonDataProviders' => [],
		'customDataProviders' => [],
	];

	/**
	 * Источники данных
	 */
	private $dataProviders = [];

	/**
	 * Хранилище данных
	 */
	private $dataStorage = [];

	/**
	 * Исходные данные
	 */
	private $inputParams = [];

	/**
	 * Количество отступов
	 */
	private $tabCnt = 0;

	/**
	 * Информация, которую нужно выборочно хранить в отдельных
	 */
	private $dataToStore = [];

	/**
	 * Глобальные опции
	 */
	private $globals = [
		/**
		 * Признак необходимости "схлапывать" пустые атрибуты
		 */
		'collapseEmptyAttributes' => true,

		/**
		 * Признак необходимости "схлапывать" пустые теги
		 */
		'collapseEmptyTags' => true,

		/**
		 * Путь до папки с файлами
		 */
		'path' => '',
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		$CI =& get_instance();
		$this->db = $CI->db;
	}

	/**
	 * @param string $configFile
	 * @return $this
	 * Загрузка конфига
	 */
	public function loadConfig(string $configFile) {
		if (!empty($this->lastError)) {
			return $this;
		}

		try {
			if (empty($configFile) || !file_exists($configFile)) {
				throw new Exception('Не найден файл конфигурации');
			}

			$xmlConfig = new SimpleXMLElement($configFile, null, true);

			if (property_exists($xmlConfig, 'globals')) {
				if ($xmlConfig->globals['collapseEmptyAttributes'] == '0' || $xmlConfig->globals['collapseEmptyAttributes'] == '1') {
					$this->globals['collapseEmptyAttributes'] = ($xmlConfig->globals['collapseEmptyAttributes'] == '1');
				}

				if ($xmlConfig->globals['collapseEmptyTags'] == '0' || $xmlConfig->globals['collapseEmptyTags'] == '1') {
					$this->globals['collapseEmptyTags'] = ($xmlConfig->globals['collapseEmptyTags'] == '1');
				}

				if (!empty($xmlConfig->globals['path'])) {
					$this->globals['path'] = (string)$xmlConfig->globals['path'];

					if ( !file_exists($this->globals['path']) ) {
						mkdir($this->globals['path']);
					}

					$this->globals['path'] .= 'xmlExporter_' . time() . '/';

					if ( !file_exists($this->globals['path']) ) {
						mkdir($this->globals['path']);
					}
				}
			}

			if (property_exists($xmlConfig, 'inputParams')) {
				foreach ($xmlConfig->inputParams->param as $param) {
					$this->inputParams[(string)$param['name']] = (string)$param['value'];
				}
			}

			if (property_exists($xmlConfig, 'files')) {
				$this->setDataFiles($xmlConfig->files);
			}

			if (property_exists($xmlConfig, 'zip')) {
				$this->setZipParams($xmlConfig->zip);
			}

			if (property_exists($xmlConfig, 'queryMap')) {
				$this->setQueryMap($xmlConfig->queryMap);
			}

			if (property_exists($xmlConfig, 'dataProviders')) {
				$this->setDataProvidersConfig($xmlConfig->dataProviders);
			}

			if (property_exists($xmlConfig, 'dataToStore')) {
				$this->setDataToStore($xmlConfig->dataToStore);
			}
		}
		catch (Exception $e) {
			$this->lastError = $e->getMessage();
		}

		return $this;
	}

	/**
	 * @param SimpleXMLElement $config
	 * @return $this
	 * Установка параметров файлов с данными
	 */
	private function setDataFiles(SimpleXMLElement $config) {
		if (!empty($this->lastError)) {
			return $this;
		}

		try {
			foreach ($config->file as $file) {
				// Индекс файла
				if (empty($file['name'])) {
					throw new Exception('Не указан индекс файла');
				}

				$name = (string)$file['name'];

				// Тип файла
				if (empty($file['type'])) {
					throw new Exception('Не указан тип файла (' . $name . ')');
				}

				$type = (string)$file['type'];

				if (!in_array($type, ['master','slave'])) {
					throw new Exception('Неверный тип файла (' . $name . ')');
				}

				// Базовая часть имени файла обязательна для заполнения
				if (empty($file['signTemplate'])) {
					throw new Exception('Не указана базовая часть имени файла (' . $name . ')');
				}

				// Описание структуры XML-файла обязательно для заполнения
				if (!property_exists($file, 'map') || $file->map->count() == 0) {
					throw new Exception('Не указана структура файла (' . $name . ')');
				}

				// Инициализация массива данных для файла
				$this->filesData[$name] = [];

				$xmlParams = $this->xmlDefaults;

				$this->convertFileMap($xmlParams['map'], $file->map);

				$xmlParams['signTemplate'] = (string)$file['signTemplate'];
				$xmlParams['type'] = $type;

				if (!empty($file['dataProvider'])) {
					$xmlParams['dataProvider'] = (string)$file['dataProvider'];
				}

				if (!empty($file['description'])) {
					$xmlParams['description'] = (string)$file['description'];
				}

				if (!empty($file['ext'])) {
					$xmlParams['ext'] = (string)$file['ext'];
				}

				if (!empty($file['recLimit'])) {
					$xmlParams['recLimit'] = (int)$file['recLimit'];
				}

				if (!empty($file['tabs'])) {
					$xmlParams['tabs'] = (int)$file['tabs'];
				}

				if (property_exists($file, 'xmlDeclaration')) {
					// @todo добавить возможность задавать объявление XML-файла
				}

				//$xmlParams['name'] = $xmlParams['signTemplate'] . '.' . $xmlParams['ext'];

				$this->exportFiles[$name] = $xmlParams;
				//var_dump($xmlParams['map']);
			}
			//throw new Exception('Неверный тип файла (' . $name . ')');
		}
		catch (Exception $e) {
			$this->lastError = $e->getMessage();
		}

		return $this;
	}

	/**
	 * @param array $resultMap
	 * @param SimpleXMLElement $map
	 * @return bool
	 * Конвертация структуры файла из XML в массив
	 */
	private function convertFileMap(array &$resultMap, SimpleXMLElement $map) {
		foreach ($map->elements->element as $element) {
			if (empty($element['name'])) {
				continue;
			}

			$name = (string)$element['name'];

			$resultMap[$name] = [];

			if (!empty($element['required'])) {
				$resultMap[$name]['required'] = ((int)$element['required'] == 1);
			}

			if (!empty($element['multiple'])) {
				$resultMap[$name]['multiple'] = ((int)$element['multiple'] == 1);
			}

			if (!empty($element['source'])) {
				$resultMap[$name]['source'] = (string)$element['source'];
			}

			if (!empty($element['dataProvider'])) {
				$resultMap[$name]['dataProvider'] = (string)$element['dataProvider'];
			}

			if (!empty($element['dataToStore'])) {
				$resultMap[$name]['dataToStore'] = (string)$element['dataToStore'];
			}

			if (!empty($element['groupKeyDataProvider'])) {
				$resultMap[$name]['groupKeyDataProvider'] = (string)$element['groupKeyDataProvider'];
			}

			if (!empty($element['groupKey'])) {
				$resultMap[$name]['groupKey'] = (string)$element['groupKey'];
			}

			if (!empty($element['iterate'])) {
				$resultMap[$name]['iterate'] = (int)$element['iterate'];
			}
			else if (!empty($element['iterateDefault'])) {
				$resultMap[$name]['iterate'] = [];

				$resultMap[$name]['iterate']['default'] = (int)$element['iterateDefault'];

				if (property_exists($element, 'iterateConditions') && $element->iterateConditions->count() > 0) {
					foreach ($element->iterateConditions->iterateCondition as $iterateCondition) {
						if (empty($iterateCondition['value'])) {
							continue;
						}

						$iterateValue = (string)$iterateCondition['value'];

						foreach ($iterateCondition->condition as $condition) {
							if (empty($condition['param'])) {
								continue;
							}

							$paramName = (string)$condition['param'];
							$paramValues = [];

							foreach ($condition->value as $paramValue) {
								if (!empty($paramValue['value'])) {
									$paramValues[] = (string)$paramValue['value'];
								}
							}

							$resultMap[$name]['iterate'][$iterateValue] = [
								'value' => $iterateValue,
								'conditions' => [
									$paramName => $paramValues
								],
							];
						}
					}
				}
			}

			if (property_exists($element, 'conditions') && $element->conditions->count() > 0) {
				$resultMap[$name]['conditions'] = [];

				foreach ($element->conditions->condition as $condition) {
					if (empty($condition['param'])) {
						continue;
					}

					$paramName = (string)$condition['param'];
					$paramValues = [];

					foreach ($condition->value as $paramValue) {
						if (!empty($paramValue['value'])) {
							$paramValues[] = (string)$paramValue['value'];
						}
					}

					$resultMap[$name]['conditions'][$paramName] = $paramValues;
				}
			}

			if (property_exists($element, 'attributes') && $element->attributes->count() > 0) {
				$resultMap[$name]['attributes'] = [];

				foreach ($element->attributes->attribute as $attribute) {
					if (empty($attribute['name'])) {
						continue;
					}

					$attributeName = (string)$attribute['name'];
					$attributeParams = [];

					if (!empty($attribute['required'])) {
						$attributeParams['required'] = ((int)$attribute['required'] == 1);
					}

					if (!empty($attribute['alias'])) {
						$attributeParams['alias'] = (string)$attribute['alias'];
					}

					$resultMap[$name]['attributes'][$attributeName] = $attributeParams;
				}
			}

			if (property_exists($element, 'elements') && $element->elements->count() > 0) {
				$resultMap[$name]['elements'] = [];
				$this->convertFileMap($resultMap[$name]['elements'], $element);
			}
		}

		return true;
	}

	/**
	 * @param SimpleXMLElement $map
	 * @return $this
	 * Установка параметров ZIP-архива
	 */
	private function setZipParams(SimpleXMLElement $map) {
		if (!empty($this->lastError)) {
			return $this;
		}

		try {
			// Базовая часть имени ZIP-файла обязательна для заполнения
			if (empty($map['signTemplate'])) {
				throw new Exception('Не указана базовая часть имени ZIP-файла');
			}

			// Список файлов, включаемых в архив, не может быть пустым
			if (!property_exists($map, 'files')) {
				throw new Exception('Не указаны файлы, которые необходимо включить в ZIP-архив');
			}

			$this->zipParams = $this->zipDefaults;

			foreach ($map->files->file as $file) {
				if (empty($file['name'])) {
					throw new Exception('Не задан индекс файла');
				}

				$fileIndex = (string)$file['name'];

				if (!isset($this->exportFiles[$fileIndex])) {
					throw new Exception('Указан несуществующий файл экспорта: ' . $fileIndex);
				}

				$this->zipParams['files'][] = $fileIndex;
			}

			if (count($this->zipParams['files']) == 0) {
				throw new Exception('Не указаны файлы, которые необходимо включить в ZIP-архив');
			}

			$this->zipParams['dataProvider'] = (!empty($map['dataProvider']) ? (string)$map['dataProvider'] : '');
			$this->zipParams['signTemplate'] = (string)$map['signTemplate'];

			if (!empty($map['ext'])) {
				$this->zipParams['ext'] = $map['ext'];
			}

			$this->zipParams['name'] = $this->zipParams['signTemplate'] . '.' . $this->zipParams['ext'];
		}
		catch (Exception $e) {
			$this->lastError = $e->getMessage();
		}

		return $this;
	}

	/**
	 * @param SimpleXMLElement $queryMap
	 * @return $this
	 * Инициализация правил для подстановки значений в запросах
	 */
	private function setQueryMap(SimpleXMLElement $queryMap) {
		if (!empty($this->lastError)) {
			return $this;
		}

		try {
			foreach ($queryMap->queryMapElem as $queryMapElem) {
				if (empty($queryMapElem['depends'])) {
					continue;
				}

				$queryMap = [
					'depends' => (string)$queryMapElem['depends'],
					'placeholders' => [],
				];

				foreach ($queryMapElem->param as $param) {
					if (empty($param['name'])) {
						continue;
					}

					$placeholderConditions = [];
					$placeholderName = (string)$param['name'];

					foreach ($param->elem as $placeholderElem) {
						if (empty($placeholderElem['input'])) {
							continue;
						}

						$input = (string)$placeholderElem['input'];

						$placeholderConditions[$input] = (!empty($placeholderElem['value']) ? (string)$placeholderElem['value'] : '');
					}

					$queryMap['placeholders'][$placeholderName] = $placeholderConditions;
				}

				$this->queryMap[] = $queryMap;
			}
		}
		catch (Exception $e) {
			$this->lastError = $e->getMessage();
		}

		return $this;
	}

	/**
	 * @param string $param
	 * @return mixed
	 * Получение наименования параметра
	 */
	private function getParamName(string $param) {
		$paramName = '';
		$paramParts = explode(':', $param);

		switch ($paramParts[0]) {
			case 'inputParams':
				if (!empty($paramParts[1]) && isset($this->inputParams[$paramParts[1]])) {
					$paramName = $paramParts[1];
				}
				break;

			case 'dataStorage':
				if (!empty($paramParts[1]) && !empty($paramParts[2]) && isset($this->dataStorage[$paramParts[1]]) && isset($this->dataStorage[$paramParts[1]][$paramParts[2]])) {
					$paramName = $paramParts[2];
				}
				break;
		}

		return $paramName;
	}

	/**
	 * @param string $param
	 * @return mixed|null
	 * Получение значения параметра
	 */
	private function getParamValue(string $param) {
		$paramValue = null;
		$paramParts = explode(':', $param);

		switch ($paramParts[0]) {
			case 'inputParams':
				if (!empty($paramParts[1]) && isset($this->inputParams[$paramParts[1]])) {
					$paramValue = $this->inputParams[$paramParts[1]];
				}
				break;

			case 'dataStorage':
				if (!empty($paramParts[1]) && !empty($paramParts[2]) && isset($this->dataStorage[$paramParts[1]]) && isset($this->dataStorage[$paramParts[1]][$paramParts[2]])) {
					$paramValue = $this->dataStorage[$paramParts[1]][$paramParts[2]];
				}
				break;
		}

		return $paramValue;
	}

	/**
	 * @param SimpleXMLElement $dataProviders
	 * @return $this
	 * Инициализация конфига для источников данных
	 */
	private function setDataProvidersConfig(SimpleXMLElement $dataProviders) {
		if (!empty($this->lastError)) {
			return $this;
		}

		try {
			foreach ($dataProviders->dataProvider as $dataProvider) {
				if (empty($dataProvider['name'])) {
					throw new Exception('Не указано имя для группы источников данных');
				}

				$providerName = (string)$dataProvider['name'];

				$this->dataProvidersConfig[$providerName] = [];

				foreach ($dataProvider->elem as $elem) {
					if (empty($elem['name'])) {
						throw new Exception('Не указано имя для источника данных');
					}

					$name = (string)$elem['name'];

					$providerConfig = [];

					$providerConfig['source'] = (string)$elem['source'];

					if (!empty($elem['method'])) {
						$providerConfig['method'] = (string)$elem['method'];
					}

					if (!empty($elem['groupKey'])) {
						$providerConfig['groupKey'] = (string)$elem['groupKey'];
					}

					if (!empty($elem['multiple']) && $elem['multiple'] == '1') {
						$providerConfig['multiple'] = true;
					}

					switch ($providerConfig['source']) {
						case 'query':
							if (!property_exists($elem, 'query')) {
								throw new Exception('Не указан запрос для источника данных');
							} else if (!property_exists($elem, 'baseFields') || $elem->baseFields->count() == 0) {
								throw new Exception('Не указаны поля для запроса');
							}

							$query = $elem->query->__toString();

							if (empty($query)) {
								throw new Exception('Не указан запрос для источника данных');
							}

							$providerConfig['query'] = '';
							$providerConfig['fields'] = [
								'base' => [],
								'additional' => [],
							];
							$providerConfig['queryParams'] = [];

							if (property_exists($elem, 'conditions') && $elem->conditions->count() > 0) {
								$conditions = [];

								foreach ($elem->conditions->condition as $elemCondition) {
									if (empty($elemCondition['param'])) {
										throw new Exception('Не указан параметр для условия в источнике данных (' . $name . ')');
									}

									$paramName = (string)$elemCondition['param'];
									$paramValues = [];

									foreach ($elemCondition->value as $paramValue) {
										if (!empty($paramValue['value'])) {
											$paramValues[] = (string)$paramValue['value'];
										}
									}

									$conditions[$paramName] = $paramValues;
								}

								$providerConfig['conditions'] = $conditions;
							}

							$baseFields = [];

							foreach ($elem->baseFields->field as $baseField) {
								$field = $baseField->__toString();

								if (!empty($field)) {
									$baseFields[] = $field;
								}
							}

							if (count($baseFields) == 0) {
								throw new Exception('Не указаны поля для запроса');
							}

							$additionalFields = [];

							if (property_exists($elem, 'additionalFields') && $elem->additionalFields->count() > 0) {
								foreach ($elem->additionalFields->elem as $additionalFieldElem) {
									if (!property_exists($additionalFieldElem, 'fields') || $additionalFieldElem->fields->count() == 0) {
										throw new Exception('Не указаны дополнительные поля для запроса');
									} else if (!property_exists($additionalFieldElem, 'conditions') || $additionalFieldElem->conditions->count() == 0) {
										throw new Exception('Не указаны условия для дополнительных полей');
									}

									$additionalFieldsSet = [
										'conditions' => [],
										'fields' => [],
									];

									foreach ($additionalFieldElem->conditions->condition as $additionalFieldCondition) {
										if (empty($additionalFieldCondition['param'])) {
											throw new Exception('Не указан параметр для условия включения дополнительных полей');
										}

										$paramName = (string)$additionalFieldCondition['param'];
										$paramValues = [];

										foreach ($additionalFieldCondition->value as $additionalParamValue) {
											if (!empty($additionalParamValue['value'])) {
												$paramValues[] = (string)$additionalParamValue['value'];
											}
										}

										$additionalFieldsSet['conditions'][$paramName] = $paramValues;
									}

									foreach ($additionalFieldElem->fields->field as $additionalField) {
										$field = $additionalField->__toString();

										if (!empty($field)) {
											$additionalFieldsSet['fields'][] = $field;
										}
									}

									$additionalFields[] = $additionalFieldsSet;
								}
							}

							$queryParams = [];

							if (property_exists($elem, 'queryParams') && $elem->queryParams->count() > 0) {
								foreach ($elem->queryParams->param as $param) {
									if (!empty($param['value'])) {
										$queryParams[] = (string)$param['value'];
									}
								}
							}

							$providerConfig['fields']['additional'] = $additionalFields;
							$providerConfig['fields']['base'] = $baseFields;
							$providerConfig['query'] = $query;
							$providerConfig['queryParams'] = $queryParams;
							break;
					}

					$this->dataProvidersConfig[$providerName][$name] = $providerConfig;
				}
			}
		}
		catch (Exception $e) {
			$this->lastError = $e->getMessage();
		}

		return $this;
	}

	/**
	 * @param SimpleXMLElement $map
	 * @return $this
	 * Установка параметров для данных, которые необходимо сохранять отдельно
	 */
	private function setDataToStore(SimpleXMLElement $map) {
		if (!empty($this->lastError)) {
			return $this;
		}

		try {
			foreach ($map->elem as $elem) {
				if (empty($elem['name'])) {
					throw new Exception('Не указано имя записи для сохранения данных');
				}

				$name = (string)$elem['name'];

				if (empty($elem['key'])) {
					throw new Exception('Не указано ключевое поле для сохранения данных (' . $name . ')');
				}

				if (empty($elem['file'])) {
					throw new Exception('Не указан файл для сохранения данных (' . $name . ')');
				}

				if (!property_exists($elem, 'fields') || $elem->fields->count() == 0) {
					throw new Exception('Не указан набор полей для сохранения данных (' . $name . ')');
				}

				$fields = [];

				foreach ($elem->fields->field as $field) {
					if (empty($field['index']) || empty($field['name'])) {
						continue;
					}

					$index = (string)$field['index'];
					$fieldName = (string)$field['name'];

					$fields[$index] = $fieldName;
				}

				if (count($fields) == 0) {
					throw new Exception('Неверно указан набор полей для сохранения данных (' . $name . ')');
				}

				$config = [
					'key' => (string)$elem['key'],
					'file' => (string)$elem['file'],
					'fields' => $fields,
					'packSize' => null,
					'format' => 'json',
					'data' => [],
				];

				if (!empty($elem['packSize'])) {
					$config['packSize'] = (int)$elem['packSize'];
				}

				if (!empty($elem['format']) && in_array((string)$elem['format'], ['json'])) {
					$config['format'] = (string)$elem['format'];
				}

				$this->dataToStore[$name] = $config;
			}
		}
		catch (Exception $e) {
			$this->lastError = $e->getMessage();
		}

		return $this;
	}

	/**
	 * @param array $conditions
	 * @return bool
	 * Проверка условий
	 */
	private function _checkConditions(array $conditions = []) {
		$result = false;

		foreach ($conditions as $param => $value) {
			$paramValue = $this->getParamValue($param);

			if (empty($paramValue)) {
				continue;
			}

			if (is_array($value)) {
				$result = ($result || in_array($paramValue, $value));
			}
			else {
				$result = ($result || ($paramValue == $value));
			}
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return $this
	 * Инициализация входящих параметров
	 */
	public function setInputParams(array $data = []) {
		if (!empty($this->lastError)) {
			return $this;
		}

		foreach ($this->inputParams as $key => $value) {
			if (isset($data[$key])) {
				$this->inputParams[$key] = $data[$key];
			}
		}

		return $this;
	}

	/**
	 * @param string $key
	 * @param null $value
	 * @return bool
	 * Инициализация одного входящего параметра
	 */
	public function setInputParam(string $key, $value = null) {
		$this->inputParams[$key] = $value;
		return true;
	}

	/**
	 * @param string $dataProviders
	 * @return bool
	 * Проверка наличия конфига для указанной группы источников данных
	 */
	public function isSetDataProvidersConfig(string $dataProviders) {
		return (isset($this->dataProvidersConfig[$dataProviders]) && count($this->dataProvidersConfig[$dataProviders]) > 0);
	}

	/**
	 * @param string $dataProviders
	 * @return $this
	 * Запуск получения данных для указанной группы источников данных
	 */
	public function runDataProviders(string $dataProviders) {
		if (!empty($this->lastError)) {
			return $this;
		}

		try {
			if (empty($dataProviders)) {
				throw new Exception('Не указана группа источников данных');
			}
			else if (!isset($this->dataProvidersConfig[$dataProviders])) {
				throw new Exception('Указана несуществующая группа источников данных');
			}

			$queryMap = [];

			if (count($this->queryMap) > 0) {
				foreach ($this->queryMap as $rule) {
					if (empty($rule['depends'])) {
						continue;
					}
					else if (!isset($rule['placeholders']) || !is_array($rule['placeholders']) || count($rule['placeholders']) == 0) {
						continue;
					}

					foreach ($rule['placeholders'] as $placeholder => $placeholderConfig) {
						$value = $this->getParamValue($rule['depends']);

						if (isset($placeholderConfig[$value])) {
							$queryMap[$placeholder] = $placeholderConfig[$value];
						}
					}
				}
			}

			foreach ($this->dataProvidersConfig[$dataProviders] as $provider => $config) {
				if (isset($this->dataProviders[$provider])) {
					unset($this->dataProviders[$provider]);
				}

				if (isset($this->dataStorage[$provider])) {
					unset($this->dataStorage[$provider]);
				}

				if (isset($config['conditions']) && count($config['conditions']) > 0 && !$this->_checkConditions($config['conditions'])) {
					continue;
				}

				$this->dataProviders[$provider] = $config;
				$this->dataStorage[$provider] = [];

				switch ($config['source']) {
					case 'query':
						$query = $config['query'];
						$queryParams = [];

						if (isset($config['queryParams']) && is_array($config['queryParams'])) {
							foreach ($config['queryParams'] as $paramName) {
								$queryParams[$this->getParamName($paramName)] = $this->getParamValue($paramName);
							}
						}

						if (count($queryMap) > 0) {
							$query = strtr($query, $queryMap);
						}

						$fields = $config['fields']['base'];

						if (isset($config['fields']['additional'])) {
							foreach ($config['fields']['additional'] as $fieldsSet) {
								if (!isset($fieldsSet['fields'])) {
									continue;
								}
								else if (isset($fieldsSet['conditions']) && $this->_checkConditions($fieldsSet['conditions'])) {
									$fields = array_merge($fields, $fieldsSet['fields']);
								}
							}
						}

						$query = str_replace('{{fields}}', implode(', ', $fields), $query);

						if ($config['method'] == 'fetch') {
							$this->dataProviders[$provider]['resourceLink'] = $this->db->query($query, $queryParams);
						}
						else if ($config['method'] == 'array') {
							$queryResult = $this->db->query($query, $queryParams);

							if (is_object($queryResult)) {
								if (isset($config['multiple']) && $config['multiple'] === true) {
									while ($row = $queryResult->_fetch_assoc()) {
										array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

										if (!empty($config['groupKey']) && isset($row[$config['groupKey']])) {
											$groupKey = $row[$config['groupKey']];

											if (!isset($this->dataStorage[$provider][$groupKey])) {
												$this->dataStorage[$provider][$groupKey] = [];
											}

											$this->dataStorage[$provider][$groupKey][] = $row;
										}
										else {
											$this->dataStorage[$provider][] = $row;
										}
									}
								}
								else {
									$row = $queryResult->_fetch_assoc();

									if (is_array($row)) {
										array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
										$this->dataStorage[$provider] = $row;
									}
								}
							}
						}
						break;
				}
			}
		}
		catch (Exception $e) {
			$this->lastError = $e->getMessage();
		}

		return $this;
	}

	/**
	 * @return $this
	 * Формирование массивов данных
	 */
	public function prepareMasterData() {
		return $this->_prepareData(false);
	}

	/**
	 * @return $this
	 * Формирование и запись данных во временные файлы
	 */
	public function prepareSlaveData() {
		return $this->_prepareData(true);
	}

	/**
	 * @return $this
	 * @param bool $isSlave
	 * Формирование массивов данных
	 */
	private function _prepareData(bool $isSlave = false) {
		if (!empty($this->lastError)) {
			return $this;
		}

		try {
			foreach ($this->exportFiles as $fileIndex => $fileConfig) {
				if (empty($fileConfig['name'])) {
					$this->exportFiles[$fileIndex]['name'] = $this->prepareFileName(
						$fileConfig['signTemplate'],
						$fileConfig['ext'],
						$fileConfig['dataProvider']
					);
				}
			}

			foreach ($this->exportFiles as $fileIndex => $fileConfig) {
				if ($isSlave === true && $fileConfig['type'] != 'slave') {
					continue;
				}
				else if ($isSlave === false && $fileConfig['type'] == 'slave') {
					continue;
				}

				$this->filesData[$fileIndex] = [];

				$this->_createData($fileIndex, $this->filesData[$fileIndex], $fileConfig['map'], $isSlave);

				//var_dump($this->filesData[$fileIndex]);
			}
		}
		catch (Exception $e) {
			$this->lastError = $e->getMessage();
		}

		return $this;
	}

	/**
	 * @param string $fileIndex
	 * @param array $fileData
	 * @param array $map
	 * @param bool $isSlave
	 * @return bool
	 */
	private function _createData(string $fileIndex, array &$fileData, array $map, bool $isSlave = false) {
		foreach ($map as $elementName => $elementMap) {
			if (isset($elementMap['conditions']) && !$this->_checkConditions($elementMap['conditions'])) {
				continue;
			}
			else if (!empty($elementMap['source'])) {
				$fileData[] = [
					'name' => $elementName,
					'source' => $this->exportFiles[$elementMap['source']]['name'],
				];
				continue;
			}

			if (isset($elementMap['multiple']) && $elementMap['multiple'] === true) {
				if (!isset($elementMap['dataProvider']) || !isset($this->dataProviders[$elementMap['dataProvider']])) {
					continue;
				}

				$dataProvider = $elementMap['dataProvider'];

				if ($this->dataProviders[$dataProvider]['source'] == 'query' && $this->dataProviders[$dataProvider]['method'] == 'fetch') {
					while ($row = $this->dataProviders[$dataProvider]['resourceLink']->_fetch_assoc()) {
						if (isset($elementMap['dataToStore'])) {
							$this->_saveDataToStore($elementMap['dataToStore'], $row);
						}

						array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

						$element = [
							'name' => $elementName,
							'attributes' => [],
							'value' => null,
							'elements' => [],
						];

						$this->dataStorage[$dataProvider] = $row;
						$this->_setElement($fileIndex, $element, $elementMap, $row, $isSlave);

						if (
							$this->globals['collapseEmptyTags'] === false
							|| count($element['attributes']) > 0
							|| count($element['elements']) > 0
							|| isset($element['value'])
						) {
							$fileData[] = $element;
						}

						if (
							!empty($this->exportFiles[$fileIndex]['recLimit'])
							&& !empty($this->exportFiles[$fileIndex]['name'])
							&& count($fileData) >= $this->exportFiles[$fileIndex]['recLimit']
						) {
							$this->compile($fileIndex);
							$fileData = [];
						}
					}

					if (
						!empty($this->exportFiles[$fileIndex]['recLimit'])
						&& !empty($this->exportFiles[$fileIndex]['name'])
						&& count($fileData) > 0
					) {
						$this->compile($fileIndex);
						$fileData = [];
					}
				}
				else {
					if (isset($this->dataProviders[$dataProvider]['groupKey'])) {
						if (
							isset($elementMap['groupKeyDataProvider'])
							&& isset($elementMap['groupKey'])
							&& isset($this->dataStorage[$elementMap['groupKeyDataProvider']][$elementMap['groupKey']])
							&& isset($this->dataStorage[$dataProvider][$this->dataStorage[$elementMap['groupKeyDataProvider']][$elementMap['groupKey']]])
						) {
							foreach ($this->dataStorage[$dataProvider][$this->dataStorage[$elementMap['groupKeyDataProvider']][$elementMap['groupKey']]] as $key => $row) {
								$element = [
									'name' => $elementName,
									'attributes' => [],
									'value' => null,
									'elements' => [],
								];

								$this->dataStorage[$dataProvider . ':current'] = $row;
								$this->_setElement($fileIndex, $element, $elementMap, $row, $isSlave);

								if (
									$this->globals['collapseEmptyTags'] === false
									|| count($element['attributes']) > 0
									|| count($element['elements']) > 0
									|| isset($element['value'])
								) {
									$fileData[] = $element;
								}
							}
						}
					}
					else {
						foreach ($this->dataStorage[$dataProvider] as $key => $row) {
							$element = [
								'name' => $elementName,
								'attributes' => [],
								'value' => null,
								'elements' => [],
							];

							$this->dataStorage[$dataProvider . ':current'] = $row;
							$this->_setElement($fileIndex, $element, $elementMap, $row, $isSlave);

							if (
								$this->globals['collapseEmptyTags'] === false
								|| count($element['attributes']) > 0
								|| count($element['elements']) > 0
								|| isset($element['value'])
							) {
								$fileData[] = $element;
							}
						}
					}
				}
			}
			else if (isset($elementMap['iterate'])) {
				$elementData = isset($elementMap['dataProvider']) ? $this->dataStorage[$elementMap['dataProvider']] : [];

				if (is_array($elementMap['iterate'])) {
					$iterate = (isset($elementMap['iterate']['default']) ? $elementMap['iterate']['default'] : 1);

					foreach ($elementMap['iterate'] as $key => $row) {
						if ($key == 'default') {
							continue;
						}

						if (
							is_array($row)
							&& (!isset($row['conditions']) || $this->_checkConditions($row['conditions']))
							&& isset($row['value'])
						) {
							$iterate = $row['value'];
						}
					}
				}
				else {
					$iterate = $elementMap['iterate'];
				}

				for ($i = 1; $i <= $iterate; $i++) {
					$element = [
						'name' => $elementName,
						'attributes' => [],
						'value' => null,
						'elements' => [],
					];

					$this->_setElement($fileIndex, $element, $elementMap, $elementData, $isSlave, strval($i));

					if (
						$this->globals['collapseEmptyTags'] === false
						|| count($element['attributes']) > 0
						|| count($element['elements']) > 0
						|| isset($element['value'])
					) {
						$fileData[] = $element;
					}
				}
			}
			else {
				$element = [
					'name' => $elementName,
					'attributes' => [],
					'value' => null,
					'elements' => [],
				];

				$elementData = isset($elementMap['dataProvider']) ? $this->dataStorage[$elementMap['dataProvider']] : [];
				$this->_setElement($fileIndex, $element, $elementMap, $elementData, $isSlave);

				if (
					$this->globals['collapseEmptyTags'] === false
					|| count($element['attributes']) > 0
					|| count($element['elements']) > 0
					|| isset($element['value'])
				) {
					$fileData[] = $element;
				}
			}
		}

		return true;
	}

	/**
	 * @param string $fileIndex
	 * @param array $fileData
	 * @param array $elementMap
	 * @param array $elementData
	 * @param bool $isSlave
	 * @param string $dataSuffix
	 * @return bool
	 */
	protected function _setElement(string $fileIndex, array &$fileData, array $elementMap, array $elementData = [], bool $isSlave = false, string $dataSuffix = '') {
		if (isset($elementMap['attributes'])) {
			$fileData['attributes'] = $this->_getTagAttributes($elementMap['attributes'], $elementData, $dataSuffix);
		}

		if (isset($elementMap['elements'])) {
			$this->_createData($fileIndex, $fileData['elements'], $elementMap['elements'], $isSlave);
		}

		return true;
	}

	/**
	 * @param array $attrList
	 * @param array $attrData
	 * @param string $dataSuffix
	 * @return array
	 */
	protected function _getTagAttributes(array $attrList = [], array $attrData = [], string $dataSuffix = '') {
		$attributes = [];

		foreach ($attrList as $attrName => $attrParams) {
			if (!is_array($attrParams)) {
				continue;
			}

			$attrAlias = (!empty($attrParams['alias']) ? $attrParams['alias'] : $attrName);

			if (isset($attrParams['value'])) {
				$attributes[$attrName] = trim($attrParams['value']);
			}
			else if ($this->globals['collapseEmptyAttributes'] === false) {
				$attributes[$attrName] = isset($attrData[$attrAlias . $dataSuffix]) ? trim($attrData[$attrAlias . $dataSuffix]) : '';
			}
			else if (isset($attrData[$attrAlias . $dataSuffix]) && strlen('' . $attrData[$attrAlias . $dataSuffix]) > 0) {
				$attributes[$attrName] = trim($attrData[$attrAlias . $dataSuffix]);
			}
		}

		return $attributes;
	}

	/**
	 * @param string $signTemplate
	 * @param string $ext
	 * @param string $dataProvider
	 * @return string
	 * Получение обработанного имени файла
	 */
	private function prepareFileName(string $signTemplate, string $ext, string $dataProvider) {
		if (!empty($dataProvider) && isset($this->dataStorage[$dataProvider])) {
			foreach ($this->dataStorage[$dataProvider] as $field => $value) {
				$signTemplate = str_replace('{{' . $field . '}}', $value, $signTemplate);
			}
		}

		return $signTemplate . '.' . $ext;
	}

	/**
	 * @param string $fileIndexOnly
	 * @return $this
	 * Сборка файлов
	 */
	public function compile(string $fileIndexOnly = '') {
		if (!empty($this->lastError)) {
			return $this;
		}

		foreach ($this->filesData as $fileIndex => $fileData) {
			if (!empty($fileIndexOnly) && $fileIndexOnly != $fileIndex) {
				continue;
			}
			else if (empty($fileIndexOnly) && $this->exportFiles[$fileIndex]['type'] == 'slave') {
				continue;
			}

			$this->tabCnt = (!empty($this->exportFiles[$fileIndex]['tabs']) ? $this->exportFiles[$fileIndex]['tabs'] : 0);

			$compiledData = '';

			if ($this->exportFiles[$fileIndex]['type'] == 'master') {
				$compiledData .= '<?xml';
				foreach ($this->exportFiles[$fileIndex]['xmlDeclaration']['attributes'] as $attrName => $attrData) {
					$compiledData .= ' ' . $attrName . '="' . $attrData['value'] . '"';
				}
				$compiledData .= " ?>\r\n";
			}

			$this->_flushData($fileIndex, $fileData, $compiledData);

			file_put_contents($this->globals['path'] . $this->exportFiles[$fileIndex]['name'], $compiledData, FILE_APPEND);

			if ($this->exportFiles[$fileIndex]['type'] == 'master') {
				foreach ($this->dataToStore as $item) {
					switch ($item['format']) {
						case 'json':
							$filename = $this->globals['path'] . $item['file'];

							if (empty($item['packSize'])) {
								@file_put_contents($filename, json_encode($item['data']), FILE_APPEND);
							}
							else {
								$toWrite = [];

								foreach ( $item['data'] as $key => $record ) {
									$toWrite[$key] = $record;

									if ( count($toWrite) == $item['packSize'] ) {
										$str = json_encode($toWrite) . PHP_EOL;
										@file_put_contents($filename, $str, FILE_APPEND);
										$toWrite = [];
									}
								}

								if ( count($toWrite) > 0 ) {
									$str = json_encode($toWrite) . PHP_EOL;
									file_put_contents($filename, $str, FILE_APPEND);
								}
							}
							break;
					}
				}
			}
		}

		return $this;
	}

	/**
	 * @param string $fileIndex
	 * @param array $fileData
	 * @param string $compiledData
	 * @return bool
	 */
	private function _flushData(string $fileIndex, array $fileData, string &$compiledData) {
		foreach ($fileData as $element) {
			$textIndent = ($this->tabCnt > 0 ? str_pad(" ", $this->tabCnt * 4) : '');
			$compiledData .= $textIndent . '<' . $element['name'];

			if (isset($element['attributes']) && count($element['attributes']) > 0) {
				foreach ($element['attributes'] as $attrName => $attrValue) {
					$compiledData .= ' ' . $attrName . '="' . $attrValue . '"';
				}
			}

			if (isset($element['source'])) {
				$compiledData .= ">\r\n";

				file_put_contents($this->globals['path'] . $this->exportFiles[$fileIndex]['name'], $compiledData, FILE_APPEND);

				// Тело файла с данными начитываем из временного (побайтно)
				if ( file_exists($this->globals['path'] . $element['source']) ) {
					// Устанавливаем начитываемый объем данных
					$chunk = 1 * 1024 * 1024; // 1 MB

					$fh = @fopen($this->globals['path'] . $element['source'], "rb");

					while ( !feof($fh) ) {
						$chunkData = fread($fh, $chunk);
						file_put_contents($this->globals['path'] . $this->exportFiles[$fileIndex]['name'], $chunkData, FILE_APPEND);
					}

					fclose($fh);

					unlink($this->globals['path'] . $element['source']);
				}

				$compiledData = $textIndent . '</' . $element['name'] . ">\r\n";
			}
			else if (!empty($element['value'])) {
				$compiledData .= '>' . $element['value'] . '</' . $element['name'] . ">\r\n";
			}
			else if (isset($element['elements']) && count($element['elements']) > 0) {
				$compiledData .= ">\r\n";
				$this->tabCnt++;
				$this->_flushData($fileIndex, $element['elements'], $compiledData);
				$this->tabCnt--;
				$compiledData .= $textIndent . '</' . $element['name'] . ">\r\n";
			}
			else {
				$compiledData .= "/>\r\n";
			}
		}

		return true;
	}

	/**
	 * Создание архива
	 */
	public function archive() {
		if (!empty($this->lastError)) {
			return $this;
		}

		if (count($this->zipParams) == 0 ) {
			return $this;
		}

		$this->zipParams['name'] = $this->prepareFileName(
			$this->zipParams['signTemplate'],
			$this->zipParams['ext'],
			$this->zipParams['dataProvider']
		);

		$file_zip_name = $this->globals['path'] . $this->zipParams['name'];

		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);

		foreach ($this->exportFiles as $fileConfig) {
			if ($fileConfig['type'] != 'master') {
				continue;
			}

			$zip->AddFile($this->globals['path'] . $fileConfig['name'], $fileConfig['name']);
		}

		$zip->close();

		return $this;
	}

	/**
	 * @param string $index
	 * @param array $data
	 * @return bool
	 */
	private function _saveDataToStore(string $index, array $data) {
		if (!isset($this->dataToStore[$index])) {
			return false;
		}
		else if (!isset($data[$this->dataToStore[$index]['key']])) {
			return false;
		}

		$record = [];

		foreach ($this->dataToStore[$index]['fields'] as $key => $field) {
			if (isset($data[$field])) {
				$record[$key] = $data[$field];
			}
			else if (isset($this->inputParams[$field])) {
				$record[$key] = $this->inputParams[$field];
			}
			else {
				$record[$key] = null;
			}
		}

		$this->dataToStore[$index]['data'][$data[$this->dataToStore[$index]['key']]] = $record;

		return true;
	}

	/**
	 * @return string
	 * Получение текста ошибки
	 */
	public function getError() {
		return $this->lastError;
	}

	/**
	 * @return string
	 * Получение ссылки на файл
	 */
	public function getLink() {
		return $this->globals['path'] . $this->zipParams['name'];
	}
}
