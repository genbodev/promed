<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceRPN_model - модель для работы с оперблоком
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ServiceRPN
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Markoff Andrew
 * @version      07.2015
 *
 * @property swServiceKZ $swservicerpnkz
 * @property swServiceKZBot $RpnBot
 * @property ObjectSynchronLog_model $ObjectSynchronLog_model
 * @property Utils_model $Utils_model
 * @property Polka_PersonCard_model $PersonCard_model
 *
 */

require_once(APPPATH.'models/_pgsql/ServiceRPN_model.php');

class Kz_ServiceRPN_model extends ServiceRPN_model
{
	public $scheme = 'r101',
		   $ServiceListLog_id;

	protected $_syncObjectList = array();

	/**
	 *	Конструктор
	 */	
	function __construct() {
		parent::__construct();
		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('RpnKZ');
		$this->load->model('LpuStructure_model');
		$this->load->model('Utils_model');
		$this->load->model("Polka_PersonCard_model", "PersonCard_model");
		$this->load->model('PersonAmbulatCard_model', 'PersonAmbulatCard_model');
		$this->load->library('textlog', array('file' => 'ServiceRPN_'.date('Y-m-d').'.log'));
	}
	/**
	 * Json с ошибкой
	 */ 
	function err($msg) {
		return array(array(
				'Error_Msg' => $msg, 
				'Cancel_Error_Handle'=>true,
				'success' => false
			));
	}

	/**
	 * Выполнение запросов к сервису РПН и обработка ошибок, которые возвращает сервис
	 */
	function exec($method, $type = 'get', $data = null) {
		$this->load->library('swServiceKZ', $this->config->item('RPN'), 'swservicerpnkz');
		$this->textlog->add("exec method: $method, type: $type, data: ".print_r($data,true));
		$result = $this->swservicerpnkz->data($method, $type, $data);
		$this->textlog->add("result: ".print_r($result,true));
		if (is_object($result) && !empty($result->Message)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса РПН: '.$result->Message
			);
		}
		if (is_object($result) && !empty($result->ExceptionMessage)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса РПН: '.$result->ExceptionMessage
			);
		}
		return $result;
	}

	/**
	 * Получение параметров по умолчанию для загрузки пациентов прикрепленных к МО
	 */
	function getDefaultImportPersonListParams() {
		return array(
			'inProcess' => 0,
			'lastLpuRegion_id' => 0,
			'lastPage' => 0,
			'lastPageSize' => 0,
			'finished' => 0,
			'stop' => 0,
			'stopTime' => 0,
			'statPers' => array('all'=>0,'ins'=>0,'upd'=>0, 'del'=>0),
			'statPersCard' => array('all'=>0,'ins'=>0,'upd'=>0, 'del'=>0),
			'errorMsg' => 0,
			'errorCode' => 0
		);
	}

	/**
	 * Получение параметров загрузки пациентов прикрепленных к МО
	 */
	function getParamsFromFile($path, $filename) {
		$pathFile = $path.$filename;
		$params = $this->getDefaultImportPersonListParams();
		$tmp_params = array();
		if (file_exists($pathFile)) {
			$str = file_get_contents($pathFile);
			$arr = explode("\n", $str);
			$param = '';
			foreach($arr as $line) {
				if (strlen($line) > 0) {
					$tmp = explode(' ', $line);
					$value = '';
					if (isset($params[$tmp[0]])) {
						$param = $tmp[0];
					} else {
						$value = $tmp[0];
					}
					if (count($tmp) > 1) {
						for($i=1; $i<count($tmp); $i++) {
							$value .= ' '.$tmp[$i];
						}
					}
				}
				$value = trim($value);
				if (!empty($param) && !empty($value)) {
					$tmp_params[$param][] = $value;
				}
			}
			foreach($tmp_params as $param => $lines) {
				$value = implode("\n", $lines);
				if (!empty($value)) {
					if (in_array($param, array('statPers','statPersCard'))) {
						$params[$param] = json_decode($value, true);
					} else {
						$params[$param] = $value;
					}
				}
			}
		}
		return $params;
	}

	/**
	 * Срхранение параметров загрузки пациентов прикрепленных к МО
	 */
	function saveParamsToFile($path, $filename, $params) {
		$pathFile = $path.$filename;
		if (!@is_dir($path)) {
			mkdir($path);
		}
		$lines = array();
		foreach($params as $key => $value) {
			if (in_array($key, array('statPers','statPersCard'))) {
				$value = json_encode($value);
			}
			$lines[] = $key.' '.$value;
		}
		return file_put_contents($pathFile, implode("\n", $lines));
	}

	/**
	 * Создание исключений по ошибкам
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$errors = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$errors = "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$errors = "Fatal Error";
				break;
			default:
				$errors = "Unknown Error";
				break;
		}

		$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
		throw new ErrorException($msg, 0, $errno, $errfile, $errline);
	}

	/**
	 * Обработка Fatal Error
	 */
	function shutdownErrorHandler($path, $filename) {
		$error = error_get_last();

		if (!empty($error)) {
			switch ($error['type']) {
				case E_NOTICE:
				case E_USER_NOTICE:
					$type = "Notice";
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$type = "Warning";
					break;
				case E_ERROR:
				case E_USER_ERROR:
					$type = "Fatal Error";
					break;
				default:
					$type = "Unknown Error";
					break;
			}

			$msg = sprintf("%s:  %s in %s on line %d", $type, $error['message'], $error['file'], $error['line']);

			$importParams = $this->getParamsFromFile($path, $filename);

			$importParams['errorMsg'] = $msg ;
			$importParams['stop'] = 0;
			$importParams['inProcess'] = 0;
			$this->saveParamsToFile($path, $filename, $importParams);

			exit($error['type']);
		}
	}

	/**
	 * Получение синхронизованных объектов
	 */
	function getSyncObject($table, $id, $field = 'Object_id') {
		if (empty($id) || !in_array($field, array('Object_id','Object_sid'))) {
			return null;
		}

		$nick = $field;
		if (in_array($field, array('Object_sid'))) {
			$nick = 'Object_Value';
		}

		// ищем в памяти
		if (isset($this->_syncObjectList[$table]) && isset($this->_syncObjectList[$table][$nick]) && isset($this->_syncObjectList[$table][$nick][$id])) {
			return $this->_syncObjectList[$table][$nick][$id];
		}

		// ищем в бд
		$ObjectSynchronLogData = $this->ObjectSynchronLog_model->getObjectSynchronLog($table, $id, $field);
		if (!empty($ObjectSynchronLogData)) {
			$key = $ObjectSynchronLogData['Object_id'];
			$this->_syncObjectList[$table]['Object_id'][$key] = &$ObjectSynchronLogData;

			$key = $ObjectSynchronLogData['Object_Value'];
			$this->_syncObjectList[$table]['Object_Value'][$key] = &$ObjectSynchronLogData;

			return $ObjectSynchronLogData;
		}

		return null;
	}

	/**
	 * Сохранение синхронизованных объектов
	 */
	function saveSyncObject($table, $id, $value, $ins = false) {
		// сохраняем в БД
		$resp = $this->ObjectSynchronLog_model->saveObjectSynchronLog($table, $id, $value, $ins);

		// сохраняем в памяти
		$ObjectSynchronLogData = array(
			'ObjectSynchronLog_id' => $resp[0]['ObjectSynchronLog_id'],
			'Object_Name' => $table,
			'Object_id' => $id,
			'Object_Value' => $value,
		);

		$this->_syncObjectList[$table]['Object_id'][$id] = &$ObjectSynchronLogData;
		$this->_syncObjectList[$table]['Object_Value'][$value] = &$ObjectSynchronLogData;
		return $ObjectSynchronLogData;
	}

	/**
	 * Получение связи для объекта Sex
	 */
	function getSexLink($sexId) {
		$link = $this->getSyncObject('Sex',$sexId,'Object_sid');
		if (!is_array($link) || empty($link['Object_id'])) {
			$this->saveSyncObject('Sex', 1, 3);
			$this->saveSyncObject('Sex', 2, 2);

			$link = $this->getSyncObject('Sex',$sexId,'Object_sid');
		}
		return $link;
	}

	/**
	 * Конвертирования параметров по карте
	 *
	 * @param array $inpParams
	 * @param array $mapping
	 * @param array $listParams
	 * @return array
	 */
	function convertParams($inpParams, $mapping, $listParams = array()) {
		$params = array();
		if (count($listParams) == 0) {
			$listParams = array_keys($mapping);
		}
		foreach($listParams as $name_left) {
			if (array_key_exists($name_left, $inpParams) && array_key_exists($name_left, $mapping)) {
				if (is_array($mapping[$name_left])) {
					foreach($mapping[$name_left] as $name_right) {
						$params[$name_right] = $inpParams[$name_left];
					}
				} else {
					$name_right = $mapping[$name_left];
					$params[$name_right] = $inpParams[$name_left];
				}
			}
		}
		return $params;
	}

	/**
	 * Получение списка МО, связанных с МО из сервиса РПН
	 */
	function getLpuListLinkedRPN() {
		$query = "
			select
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\"
			from v_Lpu L 
			where exists (
				select ObjectSynchronLog_id
				from v_ObjectSynchronLog 
				where ObjectSynchronLogService_id = :Service_id and Object_Name = 'Lpu' and Object_id = L.Lpu_id
			)
		";
		$params = array('Service_id' => $this->ObjectSynchronLog_model->serviceId);
		return $this->queryResult($query, $params);
	}

	/**
	 * Формирования адреса для промеда из строки адреса, которую вернул сервис РПН
	 *
	 * @param string $addressText
	 * @return array
	 */
	function generateAddressData($addressText, $needAddressText = true) {
		//В некоторых случаях страна не указана
		if (substr_count($addressText, 'РЕСПУБЛИКА:') == 0) {
			$addressText = 'РЕСПУБЛИКА: Казахстан , '.$addressText;
		}

		$parts = explode(',', $addressText);
		$addressParams = array();
		$KLArea_pid = null;

		$fix_socr_map = array(
			'СЕЛЬСКИЙ ОКРУГ' => array('ПОСЕЛКОВЫЙ ОКРУГ'),
		);

		foreach($parts as $part) {
			$tmp = explode(':', $part);
			if (count($tmp) != 2) {
				continue;
			}
			$partName = trim($tmp[0]);
			$partValue = trim($tmp[1]);

			switch($partName) {
				case 'РЕСПУБЛИКА':
					$addressParams['KLCountry_id'] = $this->getFirstResultFromQuery("
						select KLCountry_id as \"KLCountry_id\"
						from v_KLCountry 
						where KLCountry_Name = :KLCountry_id
						limit 1
					", array('KLCountry_id' => $partValue));
					break;

				case 'ДОМ':
					$addressParams['Address_House'] = $partValue;
					break;

				case 'КОРПУС':
					$addressParams['Address_Corpus'] = $partValue;
					break;

				case 'КВАРТИРА':
					$addressParams['Address_Flat'] = $partValue;
					break;

				default:
					$query = "
						select 
							Area.KLArea_id as \"KLArea_id\",
							Area.KLSocr_id as \"KLSocr_id\",
							Level.KLAreaLevel_Code as \"KLAreaLevel_Code\",
							Level.KLAreaLevel_SysNick as \"KLAreaLevel_SysNick\"
						from
							v_KLArea Area 
							inner join {$this->scheme}.v_KZKLSocrLink SocrLink  on SocrLink.KLSocr_id = Area.KLSocr_id
							inner join {$this->scheme}.v_KZKLSocr kzSocr  on kzSocr.KZKLSocr_id = SocrLink.KZKLSocr_id
							inner join v_KLAreaLevel Level  on Level.KLAreaLevel_id = Area.KLAreaLevel_id
						where
							Area.KLCountry_id = :KLCountry_id
							and (:KLArea_pid is null or Area.KLArea_pid = :KLArea_pid)
							and Area.KLArea_Name iLIKE :KLArea_Name
							and kzSocr.KZKLSocr_Name iLIKE :KLSocr_Name
                        limit 1
					";
					$queryParams = array(
						'KLCountry_id' => $addressParams['KLCountry_id'],
						'KLArea_pid' => $KLArea_pid,
						'KLArea_Name' => $partValue,
						'KLSocr_Name' => $partName,
					);
					$KLAreaInfo = $this->getFirstRowFromQuery($query,$queryParams);

					if (!$KLAreaInfo && isset($fix_socr_map[$partName])) {
						foreach($fix_socr_map[$partName] as $fixedPartName) {
							$queryParams = array(
								'KLCountry_id' => $addressParams['KLCountry_id'],
								'KLArea_pid' => $KLArea_pid,
								'KLArea_Name' => $partValue,
								'KLSocr_Name' => $fixedPartName,
							);
							$KLAreaInfo = $this->getFirstRowFromQuery($query,$queryParams);
							if (is_array($KLAreaInfo)) break;
						}
					}

					if ($KLAreaInfo) {
						$KLArea_pid = $KLAreaInfo['KLArea_id'];
						switch($KLAreaInfo['KLAreaLevel_SysNick']) {
							case 'rgn':
								$addressParams['KLRgn_id'] = $KLAreaInfo['KLArea_id'];
								$addressParams['KLRgnSocr_id'] = $KLAreaInfo['KLSocr_id'];
								break;
							case 'subrgn':
								$addressParams['KLSubRgn_id'] = $KLAreaInfo['KLArea_id'];
								$addressParams['KLSubRgnSocr_id'] = $KLAreaInfo['KLSocr_id'];
								break;
							case 'city':
								$addressParams['KLCity_id'] = $KLAreaInfo['KLArea_id'];
								$addressParams['KLCitySocr_id'] = $KLAreaInfo['KLSocr_id'];
								break;
							case 'town':
								$addressParams['KLTown_id'] = $KLAreaInfo['KLArea_id'];
								$addressParams['KLTownSocr_id'] = $KLAreaInfo['KLSocr_id'];
								break;
							case 'street':
								$addressParams['KLStreet_id'] = $KLAreaInfo['KLArea_id'];
								$addressParams['KLStreetSocr_id'] = $KLAreaInfo['KLSocr_id'];
								break;
						}
					} else {
						$query = "
							select 
								Street.KLStreet_id as \"KLStreet_id\",
								Street.KLSocr_id as \"KLSocr_id\"
							from
								v_KLStreet Street 
								inner join v_KLSocr Socr  on Socr.KLSocr_id = Street.KLSocr_id
							where
								(:KLArea_id is null or Street.KLArea_id = :KLArea_id)
								and Street.KLStreet_Name iLIKE :KLArea_Name
								and Socr.KLSocr_Name iLIKE :KLSocr_Name
							limit 1
						";
						$queryParams = array(
							'KLArea_id' => $KLArea_pid,
							'KLArea_Name' => $partValue,
							'KLSocr_Name' => $partName,
						);
						$KLStreetInfo = $this->getFirstRowFromQuery($query, $queryParams);
						if ($KLStreetInfo) {
							$addressParams['KLStreet_id'] = $KLStreetInfo['KLStreet_id'];
							$addressParams['KLStreetSocr_id'] = $KLStreetInfo['KLSocr_id'];
						}
					}
			}
		}
		if ($needAddressText) {
			$address_address = $this->getFirstResultFromQuery("
				select dbo.Address_Compose(
					:KLCountry_id,
					:KLRgn_id,
					:KLSubRgn_id,
					:KLCity_id,
					:KLTown_id,
					:PersonSprTerrDop_id,
					:KLStreet_id,
					:Address_House,
					:Address_Corpus,
					:Address_Flat,
					:AddressSpecObject_id
				) as \"Address\"
			", array(
				'KLCountry_id' => !empty($addressParams['KLCountry_id'])?$addressParams['KLCountry_id']:null,
				'KLRgn_id' => !empty($addressParams['KLRgn_id'])?$addressParams['KLRgn_id']:null,
				'KLSubRgn_id' => !empty($addressParams['KLSubRgn_id'])?$addressParams['KLSubRgn_id']:null,
				'KLCity_id' => !empty($addressParams['KLCity_id'])?$addressParams['KLCity_id']:null,
				'KLTown_id' => !empty($addressParams['KLTown_id'])?$addressParams['KLTown_id']:null,
				'PersonSprTerrDop_id' => null,
				'KLStreet_id' => !empty($addressParams['KLStreet_id'])?$addressParams['KLStreet_id']:null,
				'Address_House' => !empty($addressParams['Address_House'])?$addressParams['Address_House']:null,
				'Address_Corpus' => !empty($addressParams['Address_Corpus'])?$addressParams['Address_Corpus']:null,
				'Address_Flat' => !empty($addressParams['Address_Flat'])?$addressParams['Address_Flat']:null,
				'AddressSpecObject_id' => null,
			));
			if ($address_address) {
				$addressParams['Address_Address'] = $addressParams['Address_AddressText'] = $address_address;
			}
		}
		return $addressParams;
	}

	/**
	 * Получение главного адреса человека
	 */
	function getPersonAddress($rpnPerson_id) {
		$address = $this->exec("/person/{$rpnPerson_id}/address");
		if (is_array($address) && !empty($address['errorMsg'])) {
			return $this->createError('', $address['errorMsg']);
		}
		return $address;
	}

	/**
	 * Получение адресов человека
	 */
	function getPersonAddresses($rpnPerson_id) {
		$addresses = $this->exec("/person/{$rpnPerson_id}/addresses");
		if (!is_array($addresses)) {
			return $this->createError('','Ошибка получения адресов человека.');
		}
		if (is_array($addresses) && !empty($addresses['errorMsg'])) {
			return $this->createError('', $addresses['errorMsg']);
		}
		return $addresses;
	}

	/**
	 * Получение данных адреса по объекту человека из сервиса РПН
	 *
	 * @param stdClass $person
	 * @return array
	 */
	function getPersonAddressesData($rpnPerson_id) {
		$addresses = $this->getPersonAddresses($rpnPerson_id);
		if (is_array($addresses) && isset($addresses[0]) && is_array($addresses[0]) && !empty($addresses[0]['Error_Msg'])) {
			return $addresses;
		}

		$UAddress = $PAddress = $BAddress = null;
		foreach($addresses as $address) {
			switch($address->addressTypeID) {
				case 3:
					if (!$UAddress || date_create($address->beginDate) > date_create($UAddress->beginDate)) {
						$UAddress = $address;
					}
					break;

				case 2:
				case 4:
					if (!$PAddress || date_create($address->beginDate) > date_create($PAddress->beginDate)) {
						$PAddress = $address;
					}
					break;

				case 310013005:
					if (!$BAddress || date_create($address->beginDate) > date_create($BAddress->beginDate)) {
						$BAddress = $address;
					}
					break;
			}
		}

		$response = array();
		$UAddressString = is_object($UAddress)?trim($UAddress->addressString):'';
		if (!empty($UAddressString)) {
			$addressParams = $this->generateAddressData($UAddressString);
			if (coalesce(
				!empty($addressParams['KLRgn_id'])?$addressParams['KLRgn_id']:null,
				!empty($addressParams['KLSubRgn_id'])?$addressParams['KLSubRgn_id']:null,
				!empty($addressParams['KLCity_id'])?$addressParams['KLCity_id']:null,
				!empty($addressParams['KLTown_id'])?$addressParams['KLTown_id']:null,
				!empty($addressParams['KLStreet_id'])?$addressParams['KLStreet_id']:null
			)) {
				foreach($addressParams as $key => $value) {
					$key = 'U'.str_replace('Rgn', 'RGN', $key);
					$response['UAddress'][$key] = $value;
				}
			} else {
				$response['UAddress']['UAddress_Address'] = $UAddressString;
				$response['UAddress']['UAddress_AddressText'] = $UAddressString;
			}
		}
		$PAddressString = is_object($PAddress)?trim($PAddress->addressString):'';
		if (!empty($PAddressString)) {
			$addressParams = $this->generateAddressData($PAddressString);
			if (coalesce(
				!empty($addressParams['KLRgn_id'])?$addressParams['KLRgn_id']:null,
				!empty($addressParams['KLSubRgn_id'])?$addressParams['KLSubRgn_id']:null,
				!empty($addressParams['KLCity_id'])?$addressParams['KLCity_id']:null,
				!empty($addressParams['KLTown_id'])?$addressParams['KLTown_id']:null,
				!empty($addressParams['KLStreet_id'])?$addressParams['KLStreet_id']:null
			)) {
				foreach($addressParams as $key => $value) {
					$key = 'P'.str_replace('Rgn', 'RGN', $key);
					$response['PAddress'][$key] = $value;
				}
			} else {
				$response['PAddress']['PAddress_Address'] = $PAddressString;
				$response['PAddress']['PAddress_AddressText'] = $PAddressString;
			}
		}
		$BAddressString = is_object($BAddress)?trim($BAddress->addressString):'';
		if (!empty($BAddressString)) {
			$addressParams = $this->generateAddressData($BAddressString);
			if (coalesce(
				!empty($addressParams['KLRgn_id'])?$addressParams['KLRgn_id']:null,
				!empty($addressParams['KLSubRgn_id'])?$addressParams['KLSubRgn_id']:null,
				!empty($addressParams['KLCity_id'])?$addressParams['KLCity_id']:null,
				!empty($addressParams['KLTown_id'])?$addressParams['KLTown_id']:null,
				!empty($addressParams['KLStreet_id'])?$addressParams['KLStreet_id']:null
			)) {
				foreach($addressParams as $key => $value) {
					$key = 'B'.str_replace('Rgn', 'RGN', $key);
					$response['BAddress'][$key] = $value;
				}
			} else {
				$response['BAddress']['BAddress_Address'] = $BAddressString;
				$response['BAddress']['BAddress_AddressText'] = $BAddressString;
			}
		}
		return $response;
	}

	/**
	 * Получение данных соц. статуса человека из сервиса РПН
	 *
	 * @param int|string $rpnPerson_id
	 * @param DateTime $onDate
	 * @return array
	 */
	function getSocStatusData($rpnPerson_id, $onDate = null) {
		$socStatusList = $this->exec("/person/{$rpnPerson_id}/socialstatus");
		if (!is_array($socStatusList)) {
			return $this->err('Ошибка получения соц. статусов человека.');
		} else if (!empty($socStatusList['errorMsg'])) {
			return $this->err($socStatusList['errorMsg']);
		}

		if (empty($onDate)) {
			$onDate = date_create($this->currentDT->format('Y-m-d'));
		}

		$socStatus = null;
		foreach($socStatusList as $socStatusItem) {
			if (empty($socStatusItem->SocialStatusId)) {
				continue;
			}

			$prevBegDate = date_create($socStatus?$socStatus->BeginDate:'1900-01-01');
			$begDate = date_create($socStatusItem->BeginDate);
			$endDate = !empty($socStatusItem->EndDate)?date_create($socStatusItem->EndDate):null;

			if ($begDate > $prevBegDate && $begDate <= $onDate && (!$endDate ||  $endDate > $onDate)) {
				$socStatus = $socStatusItem;
			}
		}

		$response = array('SocStatus_id' => null);
		if ($socStatus) {
			//$socStatusLink = $this->getSyncObject('SocStatus', $socStatus->SocialStatusId, 'Object_sid');
			$socStatusLink = null;
			if ($socStatusLink) {
				$response['SocStatus_id'] = $socStatusLink['Object_id'];
			} else {
				$SocStatus_id = $this->getFirstResultFromQuery("
					select SocStatus_id as \"SocStatus_id\"
					from v_SocStatus 
					where SocStatus_Code = :SocStatus_Code
					and :onDate between COALESCE(SocStatus_begDT, CAST(:onDate as date)) and  COALESCE(SocStatus_endDT, CAST(:onDate as date))
					limit 1
				", array(
					'SocStatus_Code' => $socStatus->SocialStatusId,
					'onDate' => $onDate
				), true);
				if ($SocStatus_id === false) {
					return array('Error_Msg' => 'Ошибка при получении идентификатора социального статуса.');
				}
				if (!empty($SocStatus_id)) {
					$this->saveSyncObject('SocStatus', $SocStatus_id, $socStatus->SocialStatusId);
					$response['SocStatus_id'] = $SocStatus_id;
				}
			}
		}
		return $response;
	}

	/**
	 * Сохранение данных пациента, полученного из сервиса РПН
	 *
	 * @param array $data Массив входных данных
	 * @param stdClass $person Объект с данными о человеке
	 * @param array $stat массив для сбора статистики о редактировании записей
	 * @return array
	 */
	function importPerson($data, $person, &$stat) {
		$Person_id = null;
		//Добавление если не будет найден
		$personlink = $this->getSyncObject('Person',$person->PersonID,'Object_sid');

		if ($personlink) {
			//Обновление данных человека
			$Person_id = $personlink['Object_id'];

			$query = "
				select 
					P.BDZ_id as \"BDZ_id\",
					PS.Person_id as \"Person_id\",
					PS.PersonEvn_id as \"PersonEvn_id\",
					PS.Server_id as \"Server_id\",
					PS.Person_IsInErz as \"Person_IsInErz\",
					PS.Person_SurName as \"Person_SurName\",
					PS.Person_FirName as \"Person_FirName\",
					PS.Person_SecName as \"Person_SecName\",
					PS.Person_SurName||' '||PS.Person_FirName||COALESCE(' '||PS.Person_SecName,'') as \"Person_Fio\",
					PS.Sex_id as \"PersonSex_id\",
					PS.Person_Inn as \"PersonInn_Inn\",
					PS.SocStatus_id as \"SocStatus_id\",
					to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\",
					to_char(PS.Person_deadDT, 'YYYY-MM-DD') as \"Person_deadDT\",
					PI.Ethnos_id as \"Ethnos_id\",
					PNS.KLCountry_id as \"KLCountry_id\"
				from
					v_PersonState PS 
					inner join Person P  on P.Person_id = PS.Person_id
					left join v_PersonNationalityStatus PNS on P.Person_id = PNS.Person_id
					left join v_PersonInfo PI  on PI.Person_id = PS.Person_id
				where PS.Person_id = :Person_id
				limit 1			
			";
			$queryParams = array('Person_id' => $Person_id);
			$promedPerson = $this->getFirstRowFromQuery($query, $queryParams);
			if (!is_array($promedPerson)) {
				return $this->err('Ошибка при получении данных человека');
			}

			/*$tmpData = array_merge($data, $promedPerson);
			$tmpData['BDZ_id'] = $person->PersonID;
			$tmpData['Person_IsInErz'] = 2;

			$resp = $this->identPerson($tmpData, $person);
			if (!$this->isSuccessful($resp)) {
				return $this->err($resp[0]['Error_Msg']);
			}*/

			$this->load->model('Person_model');

			$identResponse = $this->getPersonData($promedPerson['BDZ_id'], $person);
			$identResponse['Person_IsInErz'] = 2;

			if(isset($identResponse['Person_deadDT'])) {
				if ($identResponse['Person_deadDT'] != $promedPerson['Person_deadDT']) {
					if(!empty($identResponse['Person_deadDT'])) {
						$resp = $this->Person_model->killPerson(array(
							'Person_id' => $promedPerson['Person_id'],
							'Person_deadDT' => $identResponse['Person_deadDT'],
							'pmUser_id' => $data['pmUser_id'],
						));
						if (!empty($resp[0]['Error_Msg'])) {
							return $this->err($resp[0]['Error_Msg']);
						}
					}
				}
			}

			if ( !empty($identResponse['BDZ_id']) && ($identResponse['Person_IsInErz'] != $promedPerson['Person_IsInErz'] || $identResponse['BDZ_id'] != $promedPerson['BDZ_id']) ) {
				$resp = $this->Person_model->updatePerson(array(
					'Person_id' => $promedPerson['Person_id'],
					'BDZ_id' => $identResponse['BDZ_id'],
					'Person_IsInErz' => $identResponse['Person_IsInErz'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($resp[0]['Error_Msg'])) {
					return $this->err($resp[0]['Error_Msg']);
				}
			}

			//Гражданство
			if ((!empty($identResponse['KLCountry_id']) && !empty($promedPerson['KLCountry_id']) && ($identResponse['KLCountry_id'] != $promedPerson['KLCountry_id'])) ||
				(empty($promedPerson['KLCountry_id']) && !empty($identResponse['KLCountry_id']))) {
				$params = [
					'Server_id' => $data['Server_id'],
					'Person_id' => $promedPerson['Person_id'],
					'pmUser_id' => $data['pmUser_id'],
					'KLCountry_id' => $identResponse['KLCountry_id']
				];

				$sql = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PersonNationalityStatus_ins(
						Server_id := Server_id,
						Person_id := Person_id,
						KLCountry_id := KLCountry_id,
						pmUser_id := pmUser_id
					);
				";

				$result = $this->getFirstRowFromQuery($sql,$params);

				if (!empty($result['Error_Msg'])) {
					return $this->err($result['Error_Msg']);
				}
			}

			$stat['upd']++;
		} else {
			//Добавление данных человека
			$sexlink = $this->getSexLink($person->sex);
			$queryParams = array(
				'BDZ_id' => $person->PersonID,
				'Person_SurName' => $person->lastName,
				'Person_FirName' => $person->firstName,
				'Person_SecName' => !empty($person->secondName)?$person->secondName:null,
				'PersonInn_Inn' => $person->iin,
				'Sex_id' => $sexlink['Object_id'],
				'Person_BirthDay' => $person->birthDate,
				'Person_deadDT' => !empty($person->deathDate)?$person->deathDate:null,
				'SocStatus_id' => null,
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id'],
				'Person_IsInErz' => 2
			);

			$query = "
				select PE.Person_id as \"Person_id\"
				from v_Person_all PE 
				inner join Person P  on P.Person_id = PE.Person_id
				where P.BDZ_id = :BDZ_id or (
					(
						:Person_SurName is null and Person_SurName is null
						or Person_SurName iLIKE :Person_SurName
					)
					and (
						:Person_FirName is null and Person_FirName is null
						or Person_FirName iLIKE :Person_FirName
					)
					and (
						:Person_SecName is null and Person_SecName is null
						or Person_SecName iLIKE :Person_SecName
					)
					and (
						:PersonInn_Inn is null and PersonInn_Inn is null
						or PersonInn_Inn = :PersonInn_Inn
					)
				)
				limit 1
			";

			$Person_id = $this->getFirstResultFromQuery($query, $queryParams);

			if (!$Person_id) {
				$socStatusData = $this->getSocStatusData($person->PersonID);
				if (isset($socStatusData[0]) && !empty($socStatusData[0]['Error_Msg'])) {
					return $socStatusData;
				}

				$queryParams['SocStatus_id'] = $socStatusData['SocStatus_id'];

				$query = "
					select
						Person_id as \"Person_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_PersonAll_ins(
						Person_id := null,
						PersonSurName_SurName := :Person_SurName,
						PersonFirName_FirName := :Person_FirName,
						PersonSecName_SecName := :Person_SecName,
						PersonBirthDay_BirthDay := :Person_BirthDay,
						Person_deadDT := :Person_deadDT,
						PersonInn_Inn := :PersonInn_Inn,
						Person_IsInErz := :Person_IsInErz,
						SocStatus_id := :SocStatus_id,
						Sex_id := :Sex_id,
						BDZ_id := :BDZ_id,
						Server_id := :Server_id,
						pmUser_id := :pmUser_id
					);
				";

				$resp = $this->queryResult($query, $queryParams);

				if (!is_array($resp)) {
					return $this->err('Ошибка при добавлении информации о человеке [1]');
				}
				if (!empty($resp[0]['Error_Msg'])) {
					return $this->err($resp[0]['Error_Msg']);
				}
				$stat['ins']++;
				$Person_id = $resp[0]['Person_id'];

				if (!empty($person->national)) {
					//Добавление национальности
					$Ethnos_id = $this->getFirstResultFromQuery("
						select Ethnos_id as \"Ethnos_id\"
						from v_Ethnos 
						where Ethnos_Code = :Ethnos_Code
						limit 1
					", array(
						'Ethnos_Code' => $person->national
					));

					if ($Ethnos_id) {
						$queryParams = array(
							'Person_id' => $Person_id,
							'Server_id' => $data['Server_id'],
							'pmUser_id' => $data['pmUser_id'],
							'Ethnos_id' => $Ethnos_id
						);
						$query = "
						select
							PersonInfo_id as \"PersonInfo_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						   from p_PersonInfo_ins(
							   Server_id := :Server_id,
							   PersonInfo_id := null,
							   Person_id := :Person_id,
							   Ethnos_id := :Ethnos_id,
							   pmUser_id := :pmUser_id
							);
						";
						$resp = $this->queryResult($query, $queryParams);

						if (!is_array($resp)) {
							return $this->err('Ошибка при добавлении информации о человеке [2]');
						}
						if (!empty($resp[0]['Error_Msg'])) {
							return $this->err($resp[0]['Error_Msg']);
						}
					}
				}

				//Добавление адресов
				$addressesData = $this->getPersonAddressesData($person->PersonID);
				if (count($addressesData) > 0 && !empty($addressesData[0]['Error_Msg'])) {
					return $this->err($addressesData[0]['Error_Msg']);
				}

				$EvnTypeList = array();
				$params = array();
				foreach($addressesData as $addressTypeName => $addressData) {
					$EvnTypeList[] = $addressTypeName;
					$params = array_merge($params, $addressData);
				}

				if (count($EvnTypeList) > 0) {
					$params = array_merge($params, array(
						'EvnType' => implode('|',$EvnTypeList),
						'pmUser_id' => $data['pmUser_id'],
						'Server_id' => $data['Server_id'],
						'session' => $data['session'],
						'Person_id' => $Person_id,
						'PersonEvn_id' => null,
					));
					$this->load->model('Person_model');
					$this->Person_model->exceptionOnValidation = true;	//Создает исключение при ошибке
					try{
						$resp = $this->Person_model->editPersonEvnAttributeNew($params);
						if (!empty($resp[0]['Error_Msg'])) {
							throw new Exception($resp[0]['Error_Msg']);
						}
					} catch(Exception $e) {
						return $this->err($e->getMessage());
					}
				}
			}

			$this->saveSyncObject('Person',$Person_id,$person->PersonID);
		}
		
		$person_fio = $person->lastName.' '.$person->firstName.' '.(!empty($person->secondName)?$person->secondName:null);

		return array(array('success' => true, 'Person_id' => $Person_id, 'Person_Fio' => $person_fio));
	}

	/*function importSocialStatusRPN($data, $record) {
		$proc = 'p_PersonSocStatus_ins';

		$query = "
			declare
				@ErrCode int,
				@ErrMsg varchar(400),
				@Res bigint;
			set @Res = :PersonSocStatus_id;
			exec {$proc}
				@PersonSocStatus = @Res output,
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@SocStatus_id = :SocStatus_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as PersonSocStatus_id, @Error_Code as Error_Code, @ErrMsg as Error_Msg;

		";
	}*/

	/**
	 * Сохранение данных прикрепления, полученного из сервиса РПН
	 *
	 * @param array $data Массив входных данных
	 * @param int $rpnPerson_id Идентификатор РПН человека
	 * @param stdClass $personCard Объект с данными о прикреплении
	 * @param array $stat массив для сбора статистики о редактировании записей
	 * @return array
	 */
	function importPersonCard($data, $rpnPerson_id, $personCard, &$stat) {
		$r = $personCard;

		// Участок
		if (!empty($r->territoryServiceID)) {
			$link = $this->getSyncObject('LpuRegion',$r->territoryServiceID, 'Object_sid');
		}
		if (!isset($link['Object_id'])) {
			return $this->err('Связь с участками не установлена, сначала необходимо синхронизировать участки МО.');
		}
		// Тип участка
		$LpuRegionType_id = $this->getFirstResultFromQuery(
			'select LpuRegionType_id as "LpuRegionType_id" from v_LpuRegion  where LpuRegion_id = :LpuRegion_id limit 1',
			array('LpuRegion_id' => $link['Object_id'])
		);
		// МО
		if (!empty($r->orgHealthCare->id)) {
			$mo = $this->getSyncObject('Lpu', $r->orgHealthCare->id, 'Object_sid');
		}
		if (!isset($mo['Object_id'])) {
			$Lpu_id = null;
			// Попробуем получить МО по названию
			if (!empty($r->orgHealthCare->name)) {
				$Lpu_id = $this->getFirstResultFromQuery(
					'select Lpu_id  as "Lpu_id" from v_Lpu  where Lpu_Name = :Lpu_Name limit 1',
					array('Lpu_Name' => trim($r->orgHealthCare->name))
				);
				// Сохраним связь с МО, если по названию удалось определить
				if ($Lpu_id) {
					$this->saveSyncObject('Lpu',$Lpu_id, $r->orgHealthCare->id);
				}
			}
			$mo['Object_id'] = !empty($Lpu_id)?$Lpu_id:null;
			if (!isset($mo['Object_id'])) {
				return $this->err('Связь по МО '.$r->orgHealthCare->name.' не установлена.<br/>Предварительно нужно проставить связи между МО.');
			}
		}

		$rpnData = array(
			'Person_id'=> $data['Person_id'], // Наш Person_id
			'rpnPerson_id'=>$rpnPerson_id, // РПН Person_id
			'pmUser_id'=>$data['pmUser_id'],
			'PersonCard_id' => null,
			'PersonCard_Code'=> '', // № амб. карты
			'LpuAttachType_id'=> '1', // Тип прикрепления, по умолчанию Основное
			'PersonCard_begDate'=> (!empty($r->beginDate))?date("Y-m-d", strtotime($r->beginDate)):null, // РПН beginDate
			'PersonCard_endDate'=> (!empty($r->endDate))?date("Y-m-d", strtotime($r->endDate)):null, // РПН endDate
			'CardCloseCause_id'=> null, // Причина должна выбираться в зависимости
			// Участок определяем по ранее записанной связи
			'LpuRegion_id' => $link['Object_id'], // Тип участка определяем по участку
			'LpuRegionType_id' => ($LpuRegionType_id)?$LpuRegionType_id:null, // Тип участка определяем по участку
			'LpuRegion_Fapid' => null,
			'Lpu_id' => $mo['Object_id'], // МО получаем по связи
			'Server_id' => $mo['Object_id'], // Server_id
			'AttachmentID'=> $r->AttachmentID, // РПН AttachmentID
			'attachmentStatus'=> $r->attachmentStatus, // РПН attachmentStatus
			'territoryServiceID'=> $r->territoryServiceID, // РПН territoryServiceID
			'orgHealthCare_id'=> $r->orgHealthCare->id, // РПН orgHealthCare->id
			'orgHealthCare_name'=> $r->orgHealthCare->name, // РПН orgHealthCare->name
			'originalID'=> $r->orgHealthCare->originalID, // РПН originalID
			'oblId'=> $r->orgHealthCare->oblId // РПН oblId
		);

		// Нужно проверить есть ли такое прикрепление по данному человеку (МО, участок)
		$query = "
			select PersonCard_id as \"PersonCard_id\"
			from v_PersonCard_all 
			where
				Person_id = :Person_id
				and Lpu_id = :Lpu_id
				and LpuRegion_id = :LpuRegion_id
				and LpuRegionType_id = :LpuRegionType_id
			limit 1
		";
		$PersonCard_id = $this->getFirstResultFromQuery($query, $rpnData);
		// Если есть такое прикрепление, то свяжем их
		$link = !empty($PersonCard_id)?$this->saveSyncObject('PersonCard', $PersonCard_id, $rpnData['AttachmentID']):null;
		$insert = false;
		// Сохраняем новое прикрепление или редактируем текущее, если конечно данные подходят
		if (is_array($link) && isset($link['Object_id'])) { // Если есть, то либо ранее уже загружали, либо есть похожее прикрепление
			// Проверяем что поменялось в прикреплении, если поменялось
			//$PersonCardData = $this->PersonCard_model->getPersonCard(array('PersonCard_id'=>$link['Object_id'], 'Lpu_id'=>$rpnData['Lpu_id']));
			$PersonCardData = $this->getPersonCardData(array('PersonCard_id'=>$link['Object_id']));
			if (count($PersonCardData)>0 && isset($PersonCardData[0]['LpuRegion_id'])) {
				$PersonCard = $PersonCardData[0];
				$PersonCard['PersonCard_id'] = $link['Object_id']; // странно, но метод getPersonCard PersonCard_id не возвращает
				$PersonCard['PersonCard_begDate'] = !empty($PersonCard['PersonCard_begDate'])?ConvertDateFormat($PersonCard['PersonCard_begDate']):null;
				$PersonCard['PersonCard_endDate'] = !empty($PersonCard['PersonCard_endDate'])?ConvertDateFormat($PersonCard['PersonCard_endDate']):null;
				// Если поменялись участок, тип участка или МО
				if (($PersonCard['LpuRegion_id']!=$rpnData['LpuRegion_id']) ||
					($PersonCard['Lpu_id']!=$rpnData['Lpu_id'])
				) {
					// то нужно вставить новую запись о прикреплении
					$insert = true;
				} else {
					if ($rpnData['PersonCard_begDate']!=$PersonCard['PersonCard_begDate'] &&
						$rpnData['LpuRegionType_id']==$PersonCard['LpuRegionType_id'] &&
						$rpnData['PersonCard_endDate']==$PersonCard['PersonCard_endDate']
					) {
						//Изменилась только дата начала
						$PersonCard['PersonCard_begDate'] = $rpnData['PersonCard_begDate'];
						$PersonCard['Server_id'] = $data['Server_id'];
						$PersonCard['pmUser_id'] = $data['pmUser_id'];
						$query = "
							update PersonCard
							set PersonCard_begDate = :PersonCard_begDate,
								Server_id = :Server_id,
								pmUser_updID = :pmUser_id,
								PersonCard_updDT = dbo.tzGetDate()
							where PersonCard_id = :PersonCard_id;
							
							update PersonCardState
							set PersonCardState_begDate = :PersonCard_begDate,
								Server_id = :Server_id,
								pmUser_updID = :pmUser_id,
								PersonCardState_updDT = dbo.tzGetDate()
							where PersonCard_id = :PersonCard_id;
						";
						$this->db->query($query, $PersonCard);
						$stat['upd']++;
					} elseif (
						empty($PersonCard['PersonCard_endDate']) && (
							$rpnData['LpuRegionType_id']!=$PersonCard['LpuRegionType_id'] ||
							$rpnData['PersonCard_endDate']!=$PersonCard['PersonCard_endDate']
						)
					) {
						// Если поменялись то апдейтим
						$PersonCard['PersonCard_begDate'] = $rpnData['PersonCard_begDate'];
						$PersonCard['LpuRegionType_id'] = $rpnData['LpuRegionType_id'];

						$PersonCard['PersonCard_endDate'] = $rpnData['PersonCard_endDate'];
						if (!empty($PersonCard['PersonCard_endDate'])) {
							$PersonCard['CardCloseCause_id'] = $this->getCardCloseCause($rpnData['attachmentStatus'], $rpnData['causeOfAttach']);
						}

						$PersonCard['action'] = 'edit';
						$PersonCard['noTransferService'] = true;
						$PersonCard['ignorePersonDead'] = true;
						$PersonCard['Server_id'] = $data['Server_id'];
						$PersonCard['pmUser_id'] = $data['pmUser_id'];
						$PersonCard['session'] = $data['session'];
						$upd = $this->PersonCard_model->savePersonCard($PersonCard);
						// если все хорошо
						if (is_array($upd) && empty($upd[0]['Error_Msg'])) {
							// считаем проапдейченные
							$stat['upd']++;
						} else {
							return $this->err($upd[0]['Error_Msg']);
						}
						//}
					}
				}
			} else { // Если данных нет, то значит прикрепление удалили, а связь осталась
				$insert = true;
			}
		} else {
			$insert = true;
		}
		if ($insert) { // Заносим новое прикрепление
			$rpnData['action'] = 'add';
			$rpnData['PersonCardAttach_id'] = null;

			//Проверка наличия аналогичного прикрепления
			$resp = $this->getFirstRowFromQuery("
				select 
					PC.PersonCard_begDate as \"PersonCard_begDate\",
					L.Lpu_Nick as \"Lpu_Nick\",
					COALESCE(PS.Person_SurName,'')||COALESCE(' '||PS.Person_FirName,'')||COALESCE(' '||PS.Person_SecName,'')||COALESCE(' '||PS.PersonInn_Inn,'') as \"Person\"
				from
					v_PersonCard_all PC 
					left join v_Lpu L  on L.Lpu_id = PC.Lpu_id
					LEFT JOIN LATERAL (
						select *
						from v_Person_all PS 
						where PS.Person_id = PC.Person_id
						and cast(PS.PersonEvn_insDT as date) <= PC.PersonCard_begDate
                        limit 1
					) PS ON true
				where 
					PC.Person_id = :Person_id
					and PC.Lpu_id = :Lpu_id
					and PC.LpuAttachType_id = :LpuAttachType_id
					and PC.PersonCard_begDate <= :PersonCard_begDate
                limit 1
			", $rpnData, true);
			if ($resp === false) {
				return $this->err('Ошибка при поиске аналогичных прикреплений');
			}
			if (is_array($resp['PersonCard_begDate'])) {
				$error = "{$resp['Person_Fio']} {$rpnData['PersonCard_begDate']} {$resp['Lpu_Nick']}";
				if ($resp['PersonCard_begDate'] == $rpnData['PersonCard_begDate']) {
					$error .= " - прикрепление создано ранее";
				} else {
					$error .= " - получено прикрепление с датой меньше чем в Казмед";
				}
				return $this->err($error);
			}


			$PersonAmbulatCard_Num = $this->getFirstResultFromQuery("
				SELECT COALESCE((
					select max(cast(PersonAmbulatCard_Num as bigint))+1
					from v_PersonAmbulatCard 
					where ISNUMERIC(PersonAmbulatCard_Num) = 1
					and Lpu_id = :Lpu_id
				), 1) as \"PersonAmbulatCard_Num\"
			", array('Lpu_id' => $rpnData['Lpu_id']));

			$PersonAmbulatCard = $this->PersonAmbulatCard_model->checkPersonAmbulatCard(array(
				'Person_id'=>$data['Person_id'],
				'Lpu_id'=>$rpnData['Lpu_id'],
				'pmUser_id'=>$data['pmUser_id'],
				'Server_id'=>$data['Server_id'],
				'PersonAmbulatCard_Num'=>$PersonAmbulatCard_Num,
				'getCount'=>false
			));
			if (is_array($PersonAmbulatCard) && !empty($PersonAmbulatCard[0]['PersonAmbulatCard_id'])) {
				$rpnData['PersonAmbulatCard_id'] = $PersonAmbulatCard[0]['PersonAmbulatCard_id'];
				$rpnData['PersonCard_Code'] = $PersonAmbulatCard[0]['PersonCard_Code'];
			} else if (is_array($PersonAmbulatCard) && !empty($PersonAmbulatCard[0]['Error_Msg'])) {
				return $this->err($PersonAmbulatCard[0]['Error_Msg']);
			} else {
				return $this->err('Ошибка при получении идентификатора амбулаторной карты');
			}
			$rpnData['noTransferService'] = true;
			$rpnData['ignorePersonDead'] = true;
			$rpnData['session'] = $data['session'];
			$ins = $this->PersonCard_model->savePersonCard($rpnData);
			// если все хорошо
			if (is_array($ins) && empty($ins[0]['Error_Msg'])) {
				// сохраняем связь
				$link = $this->saveSyncObject('PersonCard',$ins[0]['PersonCard_id'], $rpnData['AttachmentID']);
				// считаем вставленные карты
				$stat['ins']++;
			} else {
				return $this->err($ins[0]['Error_Msg']);
			}
		}
		return array(array('success' => true));
	}

	/**
	 * Получение информации по прикреплению из сервиса РПН (и сохранение в БД, опционально)
	 */
	function getPersonCardList($data) {
		// Параметры для запроса - ищем сначала по фио и ИИН, если он есть, и далее по фио и дате рождения 
		// todo: Пока только по дате рождения 
		$params = json_encode(
			array(
				'fioiin'=> $data['Person_SurName']." ".$data['Person_FirName'].(isset($data['Person_SecName'])?" ".$data['Person_SecName']:""),
				'dtRoj'=> $data['Person_BirthDay'],
				'dtRojDo'=> $data['Person_BirthDay']
			));
		
		// Получаем одну страницу (данные по одному пациенту)
		$result = $this->exec('/person/search4/1/10', 'post', $params);
		// Проверим сколько данных мы получили 
		if (is_array($result) && count($result)>0) {
			if (count($result)>1) {
				return $this->err('Ошибка получения данных с портала РПН: найденных людей больше одного');
			}
		} else {
			//  Ошибка получения данных с портала РПН
			return $this->err('Ошибка получения данных с портала РПН: данные по человеку не найдены');
		}
		// общие данные
		$rpnPerson_id = $result[0]->PersonID;
		
		// устанавливаем связь между Person_id и идентификатором человека в сервисе, причем каждый раз (для истории)
		$this->saveSyncObject('Person', $data['Person_id'], $rpnPerson_id, true);
		
		// получаем историю прикрепления
		$result = $this->exec('/person/attachments?personId='.$rpnPerson_id);
		
		
		$stat = array('all'=>count($result),'ins'=>0,'upd'=>0, 'del'=>0);
		// Выбираем из найденной записи параметры
		if (is_array($result) && count($result)>0) {
			foreach ($result as $k=>$r) { // Обрабатываем найденную запись прикрепления
				$resp = $this->importPersonCard($data, $rpnPerson_id, $r, $stat);
				if (!empty($resp[0]['Error_Msg'])) {
					return $resp;
				}
			}
		}
		
		$message = "Всего получено прикреплений: ".$stat['all'];
		if ($stat['ins']==0 && $stat['upd']==0) {
			$message .= ", данные актуальны, обновление не требуется.<br/>";
		} else {
			$message .= ", добавлено: <b>".$stat['ins']."</b>, обновлено: <b>".$stat['upd']."</b><br/>";
		}
		return array(array('Message' => $message, 'success' => true));

		// Если вернулся один человек, то работать дальше, иначе непонятно что делать
		//$result = $this->exec('/person/attachments?personId='.'395447156');
		//print_r($result);

	}


	/**
	 * Разрыв соединения с клиентом
	 */
	function breakConnectionResponse($ret = false) {
		if($ret == false) {
			if (function_exists('fastcgi_finish_request')) {
				echo json_encode(array("success" => "true", "type" => 1));
				session_write_close();
				fastcgi_finish_request();
			} else {
				ignore_user_abort(true);

				ob_start();
				echo json_encode(array("success" => "true", "type" => 2));

				$size = ob_get_length();

				header("Content-Length: $size");
				header("Content-Encoding: none");
				header("Connection: close");

				ob_end_flush();
				ob_flush();
				flush();

				if (session_id()) session_write_close();
			}
		} else {
			return array(
				"success" => "true",
				"type" => "1"
			);
		}

	}

	/**
	 * Получение прикрепленных пациентов к участкам МО из сервиса РПН. Участки уже должны быть получены
	 */
	function startImportPersonList($data) {
		set_time_limit(0);
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");

		$path = './'.IMPORTPATH_ROOT.'importPersonRPN';
		$filename = '/Lpu_'.$data['Lpu_id'].'.txt';

		$importParams = $this->getParamsFromFile($path, $filename);
		$breakConnection = array(); // разработчик не предусмотрел refs #180864
		if (empty($_REQUEST['Debug'])) {
			$breakConnection = $this->breakConnectionResponse(true);
		}

		$this->load->helper('ShutdownErrorHandler');
		registerShutdownErrorHandler(array($this, 'shutdownErrorHandler'), array(realpath($path), $filename));

		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));

			$err = $this->createError('','');
			$rules = array(
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'id'),
			);
			$this->_checkInputData($rules, $data, $err, false);
			if (!empty($err[0]['Error_Msg'])) {
				throw new Exception($err[0]['Error_Msg']);
			}

			if ($importParams['inProcess']) {
				return $this->getImportPersonListResponse($importParams);
			}
			if ($importParams['finished']) {
				throw new Exception('Загрузка данных по МО уже была выполнена');
			}
			$importParams['inProcess'] = 1;
			$importParams['errorCode'] = 0;
			$importParams['errorMsg'] = 0;

			$this->saveParamsToFile($path, $filename, $importParams);

			$lastImport = array(
				'LpuRegion_id' => $importParams['lastLpuRegion_id'],
				'page' => $importParams['lastPage'],
				'pageSize' => $importParams['lastPageSize']
			);
			//$statPers = $importParams['statPers'];
			//$statPersCard = $importParams['statPersCard'];

			$query = "
				select
					LR.LpuRegion_id as \"LpuRegion_id\",
					rpnLR.Object_sid as \"rpnLpuRegion_id\"
				from
					v_LpuRegion LR 
					INNER JOIN LATERAL(
						select 
							Object_sid
						from
							v_ObjectSynchronLog 
						where
							Object_Name = 'LpuRegion'
							and Object_id = LR.LpuRegion_id
							and ObjectSynchronLogService_id = :ObjectSynchronLogService_id
						order by
							Object_setDT desc
						limit 1
					) rpnLR ON true
				where LR.Lpu_id = :Lpu_id
			";
			$queryParams = array(
				'Lpu_id' => $data['Lpu_id'],
				'ObjectSynchronLogService_id' => $this->ObjectSynchronLog_model->serviceId
			);
			$LpuRegions = $this->queryResult($query, $queryParams);
			foreach($LpuRegions as $LpuRegion) {
				if ($lastImport['LpuRegion_id']) {
					if ($lastImport['LpuRegion_id'] != $LpuRegion['LpuRegion_id']) {
						continue;
					}
					$lastImport['LpuRegion_id'] = 0;
				}
				$rpnLpuRegion_id = $LpuRegion['rpnLpuRegion_id'];
				//$this->textlog->add("start LpuRegion_id: {$rpnLpuRegion_id}");

				//Поиск людей, прикрепленных к участку
				$result = $this->exec("/person/search4/count", 'post', json_encode(array(
					'territory' => $rpnLpuRegion_id
				)));
				if (is_array($result) && !empty($result['errorMsg'])) {
					throw new Exception($result['errorMsg']);
				}

				if ($result->Count == 0) {
					continue;
				}

				$count = $result->Count;
				$pageSize = 50;
				$pageCount = ceil($count/$pageSize);
				for($page = 1; $page <= $pageCount; $page++) {
					if ($lastImport['page']) {
						$page = ceil(($lastImport['page']+1)*$lastImport['pageSize']/$pageSize);
						$lastImport['page'] = 0;
						$lastImport['pageSize'] = 0;
						if ($page > $pageCount) {
							continue;
						}
					}
					$this->textlog->add("page start LpuRegion_id: {$rpnLpuRegion_id} page: $page/$pageCount");
					$result = $this->exec("/person/search4/{$page}/{$pageSize}", 'post', json_encode(array(
						'territory' => $rpnLpuRegion_id
					)));
					if (is_array($result) && !empty($result['errorMsg'])) {
						throw new Exception($result['errorMsg']);
					}
					if (is_array($result) && count($result)>0) {
						foreach($result as $rpnPerson) {
							$tmpImportParams = $this->getParamsFromFile($path, $filename);
							if ($tmpImportParams['stop']) {
								$importParams['inProcess'] = 0;
								$importParams['stop'] = 0;
								$this->saveParamsToFile($path, $filename, $importParams);
								return array($this->getImportPersonListResponse($importParams));
							}

							$this->textlog->add("person start rpnPerson: {$rpnPerson->PersonID}, count {$count}, page {$page}, pageSize {$pageSize}, pageCount {$pageCount}");
							// общие данные
							$rpnPerson_id = $rpnPerson->PersonID;

							//Импорт человека
							$importParams['statPers']['all']++;
							//$statPers['all']++;
							$respPers = $this->importPerson($data, $rpnPerson, $importParams['statPers']);
							if (!empty($respPers[0]['Error_Msg'])) {
								throw new Exception("Import Person rpnPerson_id={$rpnPerson_id}: {$respPers[0]['Error_Msg']}");
							}

							$data['Person_id'] = $respPers[0]['Person_id'];

							//Импорт активного прикрепления, если имеет статус "2. Прикреплен" и человек не мертв
							$respPersCard = null;
							if (empty($rpnPerson->deathDate) && !empty($rpnPerson->activeAttachment) && $rpnPerson->activeAttachment->attachmentStatus == 2) {
								$importParams['statPersCard']['all']++;
								//$statPersCard['all']++;
								$respPersCard = $this->importPersonCard($data, $rpnPerson_id, $rpnPerson->activeAttachment, $importParams['statPersCard']);
								if (!empty($respPersCard[0]['Error_Msg'])) {
									throw new Exception("Import Person Card rpnPerson_id={$rpnPerson_id}: {$respPersCard[0]['Error_Msg']}");
								}
							}
							$this->textlog->add("person finish rpnPerson: {$rpnPerson->PersonID}, respPers: ".print_r($respPers,true).", respPersCard: ".print_r($respPersCard,true));
						}
					}

					$this->textlog->add("page finish LpuRegion_id: {$rpnLpuRegion_id} page: $page/$pageCount");
					$importParams['lastLpuRegion_id'] = $LpuRegion['LpuRegion_id'];
					$importParams['lastPage'] = $page;
					$importParams['lastPageSize'] = $pageSize;
					//$importParams['statPers'] = $statPers;
					//$importParams['statPersCard'] = $statPersCard;
					$tmpImportParams = $this->getParamsFromFile($path, $filename);
					if ($tmpImportParams['stop']) {
						$importParams['inProcess'] = 0;
						$importParams['stop'] = 0;
						$this->saveParamsToFile($path, $filename, $importParams);
						return array($this->getImportPersonListResponse($importParams));
					}
					$this->saveParamsToFile($path, $filename, $importParams);
				}
				//$this->textlog->add("finish LpuRegion_id: {$rpnLpuRegion_id}");
			}
			restore_error_handler();
		} catch(Exception $e) {
			$this->textlog->add('errorCode: '.$e->getCode().' errorMsg: '.$e->getMessage());
			$this->textlog->add($e->getTraceAsString());

			$importParams['errorMsg'] = $e->getMessage();
			$importParams['errorCode'] = $e->getCode()?$e->getCode():0;
			$importParams['stop'] = 0;
			if ($e->getCode() != 1) {
				$importParams['inProcess'] = 0;
			}
			$this->saveParamsToFile($path, $filename, $importParams);

			return $this->err($e->getMessage());
		}

		$importParams['inProcess'] = 0;
		$importParams['finished'] = 1;
		$this->saveParamsToFile($path, $filename, $importParams);

		return array_merge(
			$breakConnection,
			$this->getImportPersonListResponse($importParams)
		);
	}

	/**
	 * Отслеживает остановку получения информации о пациентах МО и их активных прикреплениях
	 */
	function stopImportPersonList($data) {
		set_time_limit(0);
		ini_set("max_execution_time", "0");

		$path = './'.IMPORTPATH_ROOT.'importPersonRPN';
		$filename = '/Lpu_'.$data['Lpu_id'].'.txt';

		$importParams = $this->getParamsFromFile($path, $filename);

		$importParams['stop'] = 1;
		$importParams['stopTime'] = time();
		$this->saveParamsToFile($path, $filename, $importParams);

		return array(array('success' => true));
	}

	/**
	 * Формирование ответа для загрузки пациентов
	 */
	function getImportPersonListResponse($importParams) {
		$message = '';
		if ($importParams['statPers']) {
			$statPers = $importParams['statPers'];
			$message = "Всего получено пациентов: ".$statPers['all'];
			if ($statPers['all'] == 0) {
				$message .= "<br/>";
			} else if ($statPers['ins']==0 && $statPers['upd']==0) {
				$message .= ", данные актуальны, обновление не требуется.<br/>";
			} else {
				$message .= ", добавлено: <b>".$statPers['ins']."</b>, обновлено: <b>".$statPers['upd']."</b><br/>";
			}
		}
		if ($importParams['statPersCard']) {
			$statPersCard = $importParams['statPersCard'];
			$message .= "Всего получено прикреплений: ".$statPersCard['all'];
			if ($statPersCard['all'] == 0) {
				$message .= "<br/>";
			} else if ($statPersCard['ins']==0 && $statPersCard['upd']==0) {
				$message .= ", данные актуальны, обновление не требуется.<br/>";
			} else {
				$message .= ", добавлено: <b>".$statPersCard['ins']."</b>, обновлено: <b>".$statPersCard['upd']."</b><br/>";
			}
		}
		switch(true) {
			case $importParams['finished']:
				$message .= "<b>Получение данных завершено</b>";break;
			case $importParams['stop']:
				$message .= "<b>Остановка получения данных</b>";break;
			case $importParams['inProcess']:
				$message .= "<b>Выполняется получение данных</b>";break;
			case !$importParams['inProcess'] && $importParams['lastLpuRegion_id']:
				$message .= "<b>Получение данных остановлено</b>";break;
		}
		$response = array(
			'success' => true,
			'message' => $message,
			'inProcess' => $importParams['inProcess'],
			'finished' => $importParams['finished'],
			'stop' => $importParams['stop'],
			'stopTime' => $importParams['stopTime'],
			'time' => time(),
			'Error_Msg' => $importParams['errorMsg']?$importParams['errorMsg']:'',
			'Error_Code' => $importParams['errorCode']?$importParams['errorCode']:'',
		);
		return $response;
	}

	/**
	 * Проверка состояния загрузки информации о пациентах МО и их активных прикреплениях
	 */
	function checkImportPersonListStatus($data) {
		$path = './'.IMPORTPATH_ROOT.'importPersonRPN';
		$filename = '/Lpu_'.$data['Lpu_id'].'.txt';

		if ($importParams = $this->getParamsFromFile($path, $filename)) {
			$response = $this->getImportPersonListResponse($importParams);
		} else {
			$response = array('success' => false);
		}
		return array($response);
	}

	/**
	 * Сброс параметров для получения информации о пациентах МО и их активных прикреплениях
	 */
	function resetImportPersonListParams($data) {
		$params = $this->getDefaultImportPersonListParams();
		$path = './'.IMPORTPATH_ROOT.'importPersonRPN';
		$filename = '/Lpu_'.$data['Lpu_id'].'.txt';

		if (!empty($data['paramList'])) {
			//Сбрасывает перечисленные в списке параметры
			$paramList = json_decode($data['paramList']);

			$params = $this->getParamsFromFile($path, $filename);

			foreach($paramList as $paramName) {
				$params[$paramName] = 0;
			}
		}

		$this->saveParamsToFile($path, $filename, $params);

		return array(array('success' => true));
	}

	/**
	 * Сброс параметров для получения информации о пациентах МО и их активных прикреплениях
	 */
	function setImportPersonListParams($data) {
		$params = $this->getDefaultImportPersonListParams();
		$path = './'.IMPORTPATH_ROOT.'importPersonRPN';
		$filename = '/Lpu_'.$data['Lpu_id'].'.txt';

		$paramList = json_decode($data['paramValueList']);

		$params = $this->getParamsFromFile($path, $filename);

		foreach($paramList as $paramName => $paramValue) {
			if (isset($params[$paramName])) {
				$params[$paramName] = $paramValue;
			}
		}

		$this->saveParamsToFile($path, $filename, $params);

		return array(array('success' => true));
	}

	/**
	 * Разбор массива объектов из РПН с адресами участков
	 *
	 * @param array $addresses
	 * @return array
	 */
	function parseLpuRegionAddresses($addresses) {
		$regions = $addresses;
		$cities = array();
		$address_list = array();

		foreach($regions as $region) {
			foreach($region->childs as $rgn_child) {
				$key = $region->nameRU;
				if (substr_count($rgn_child->nameRU, 'РЕСПУБЛИКА:') > 0) {
					$key = $rgn_child->nameRU;
				}

				foreach($rgn_child->childs as $item) {
					if (count($item->childs) > 0) {
						$town = $item;
						$street_list = $item->childs;

						foreach($street_list as $street) {
							$HouseSet = array();
							foreach($street->buildings as $building) {
								$HouseSet[] = $building->buildingNumber;
							}
							$address_list[$key][] = array(
								'addressId' => $street->ateID,
								'rgnChildName' => $rgn_child->nameRU,
								'rgnChildSocr' => $rgn_child->ateTypeID,
								'townName' => $town->nameRU,
								'townSocr' => $town->ateTypeID,
								'streetName' => $street->nameRU,
								'streetSocr' => $street->ateTypeID,
								'HouseSet' => $HouseSet
							);
						}
					} else {
						$street = $item;
						$HouseSet = array();
						foreach($street->buildings as $building) {
							$HouseSet[] = $building->buildingNumber;
						}
						$address_list[$key][] = array(
							'addressId' => $street->ateID,
							'rgnChildName' => $rgn_child->nameRU,
							'rgnChildSocr' => $rgn_child->ateTypeID,
							'streetName' => $street->nameRU,
							'streetSocr' => $street->ateTypeID,
							'HouseSet' => $HouseSet
						);
					}
				}
			}
		}

		$arr = array();
		foreach($address_list as $addressText => $elementSetList) {
			$addressData = $this->generateAddressData($addressText, false);

			foreach($elementSetList as $elementSet) {
				$ad = $addressData;

				if ((empty($ad['KLSubRgn_id']) || empty($ad['KLCity_id'])) && !empty($elementSet['rgnChildName'])) {
					$rgnChild = $this->getFirstRowFromQuery("
						select 
							Area.KLArea_id as \"KLArea_id\",
							SocrLink.KLSocr_id as \"KLSocr_id\",
							Level.KLAreaLevel_SysNick as \"KLAreaLevel_SysNick\"
						from
							v_KLArea Area 
							inner join v_KZKLSocrLink SocrLink  on SocrLink.KLSocr_id = Area.KLSocr_id
							inner join v_KLAreaLevel Level  on Level.KLAreaLevel_id = Area.KLAreaLevel_id
						where
							Area.KLArea_Name = :KLArea_Name
							and Area.KLArea_pid = :KLArea_pid
							and SocrLink.KZKLSocr_id = :KZKLSocr_id
                        limit 1
					", array(
						'KLArea_Name' => $elementSet['rgnChildName'],
						'KZKLSocr_id' => $elementSet['rgnChildSocr'],
						'KLArea_pid' => coalesce(
							!empty($ad['KLSubRgn_id'])?$ad['KLSubRgn_id']:null,
							!empty($ad['KLRgn_id'])?$ad['KLRgn_id']:null
						)
					));
					if ($rgnChild) {
						if ($rgnChild['KLAreaLevel_SysNick'] == 'subrgn') {
							$ad['KLSubRgn_id'] = $rgnChild['KLArea_id'];
							$ad['KLSubRgnSocr_id'] = $rgnChild['KLSocr_id'];
						} else {
							$ad['KLCity_id'] = $rgnChild['KLArea_id'];
							$ad['KLCitySocr_id'] = $rgnChild['KLSocr_id'];
						}
					}
				}
				if (empty($ad['KLTown_id']) && !empty($elementSet['townName'])) {
					$town = $this->getFirstRowFromQuery("
						select 
							Town.KLTown_id as \"KLTown_id\",
							SocrLink.KLSocr_id as \"KLTownSocr_id\"
						from
							v_KLTown Town 
							inner join v_KZKLSocrLink SocrLink  on SocrLink.KLSocr_id = Town.KLSocr_id
						where
							Town.KLTown_Name = :KLTown_Name
							and Town.KLArea_pid = :KLArea_pid
							and SocrLink.KZKLSocr_id = :KZKLSocr_id
						limit 1
					", array(
						'KLTown_Name' => $elementSet['townName'],
						'KZKLSocr_id' => $elementSet['townSocr'],
						'KLArea_pid' => coalesce(
							!empty($ad['KLCity_id'])?$ad['KLCity_id']:null,
							!empty($ad['KLSubRgn_id'])?$ad['KLSubRgn_id']:null,
							!empty($ad['KLRgn_id'])?$ad['KLRgn_id']:null
						)
					));
					if ($town) {
						$ad = array_merge($ad, $town);
					}
				}
				if (empty($ad['KLStreet_id']) && !empty($elementSet['streetName'])) {
					$street = $this->getFirstRowFromQuery("
						select 
							Street.KLStreet_id as \"KLStreet_id\",
							SocrLink.KLSocr_id as \"KLStreetSocr_id\"
						from
							v_KLStreet Street 
							inner join v_KZKLSocrLink SocrLink  on SocrLink.KLSocr_id = Street.KLSocr_id
						where
							Street.KLStreet_Name = :KLStreet_Name
							and Street.KLArea_id = :KLArea_id
							and SocrLink.KZKLSocr_id = :KZKLSocr_id
						limit 1
					", array(
						'KLStreet_Name' => $elementSet['streetName'],
						'KZKLSocr_id' => $elementSet['streetSocr'],
						'KLArea_id' => coalesce(
							!empty($ad['KLTown_id'])?$ad['KLTown_id']:null,
							!empty($ad['KLCity_id'])?$ad['KLCity_id']:null,
							!empty($ad['KLSubRgn_id'])?$ad['KLSubRgn_id']:null,
							!empty($ad['KLRgn_id'])?$ad['KLRgn_id']:null
						)
					));
					if ($street) {
						$ad = array_merge($ad, $street);
					}
				}
				//Если найдена улица, то можно сохранять адрес
				if (!empty($ad['KLStreet_id'])) {
					$ad['addressId'] = $elementSet['addressId'];
					$ad['HouseSet'] = array_map(function($item) {
						return str_replace(',','.',$item);
					}, $elementSet['HouseSet']);
					$arr[] = $ad;
				}
			}
		}

		return $arr;
	}

	/**
	 * Импорт адресов участка из РПН
	 *
	 * @param array $data
	 * @return array
	 */
	function importLpuRegionAddressesData($data) {
		if(isset($data['parsedAddresses']) && is_array($data['parsedAddresses'])) {
			$parsedAddresses = $data['parsedAddresses'];
		} else if (!empty($data['rpnLpuRegion_id'])) {
			$addresses = $this->exec("/territory/ter/{$data['rpnLpuRegion_id']}/addresses");

			if (!is_array($addresses)) {
				return $this->err('Ошибка получения адресов участка с портала РПН.');
			} else if (!empty($addresses['errorMsg'])) {
				return $this->err($addresses['errorMsg']);
			}

			$parsedAddresses = $this->parseLpuRegionAddresses($addresses);
		}

		foreach($parsedAddresses as $item) {
			$link = $this->getSyncObject('LpuRegionStreet_'.$data['LpuRegion_id'], $item['addressId'], 'Object_sid');

			$LpuRegionStreet_id = $link ? $link['Object_id'] : null;
			$HouseSet = array();

			$LpuRegionStreet = $this->getFirstRowFromQuery("
				select 
					LRS.LpuRegionStreet_id as \"LpuRegionStreet_id\",
					LRS.LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\"
				from
					v_LpuRegionStreet LRS 
				where
					LRS.LpuRegion_id = :LpuRegion_id
					and COALESCE(LRS.KLTown_id,0) = COALESCE(CAST(:KLTown_id as bigint),0)
					and COALESCE(LRS.KLStreet_id,0) = COALESCE(CAST(:KLStreet_id as bigint),0)
				limit 1
			", array(
				'LpuRegion_id' => $data['LpuRegion_id'],
				'KLTown_id' => !empty($item['KLTown_id'])?$item['KLTown_id']:null,
				'KLStreet_id' => !empty($item['KLStreet_id'])?$item['KLStreet_id']:null,
			));
			if ($LpuRegionStreet) {
				$LpuRegionStreet_id = $LpuRegionStreet['LpuRegionStreet_id'];
				$HouseSet = explode(',', $LpuRegionStreet['LpuRegionStreet_HouseSet']);
				array_map('trim', $HouseSet);
			}

			$needSave = false;
			if (empty($LpuRegionStreet_id)) {
				$needSave = true;
			} else {
				$needSave = (count(array_diff($item['HouseSet'], $HouseSet)) > 0);
			}

			if ($needSave) {
				$resp = $this->LpuStructure_model->SaveLpuRegionStreet(array(
					'LpuRegionStreet_id' => $LpuRegionStreet_id,
					'Server_id' => $data['Server_id'],
					'LpuRegion_id' => $data['LpuRegion_id'],
					'KLCountry_id' => !empty($item['KLCountry_id'])?$item['KLCountry_id']:null,
					'KLRGN_id' => !empty($item['KLRgn_id'])?$item['KLRgn_id']:null,
					'KLSubRGN_id' => !empty($item['KLSubRgn_id'])?$item['KLSubRgn_id']:null,
					'KLCity_id' => !empty($item['KLCity_id'])?$item['KLCity_id']:null,
					'KLTown_id' => !empty($item['KLTown_id'])?$item['KLTown_id']:null,
					'KLStreet_id' => !empty($item['KLStreet_id'])?$item['KLStreet_id']:null,
					'LpuRegionStreet_HouseSet' => implode(',', $item['HouseSet']),
					'pmUser_id' => $data['pmUser_id']
				));
				if (!is_array($resp)) {
					return $this->err('Ошибка при сохранении территории участка');
				}
				if (!empty($resp[0]['Error_Msg'])) {
					return $this->err($resp[0]['Error_Msg']);
				}
				$LpuRegionStreet_id = $resp[0]['LpuRegionStreet_id'];

				$this->saveSyncObject('LpuRegionStreet_'.$data['LpuRegion_id'], $LpuRegionStreet_id, $item['addressId'], true);
			}
		}
		return array(array('success' => true, 'Error_Msg' => ''));
	}

	/**
	 * Получение информации по участку из сервиса РПН (и сохранение в БД, опционально)
	 */
	function getLpuRegionList($data) {
		set_time_limit(0);
		$this->textlog->add("getLpuRegionList start");
		// сначала ищем связь по МО, чтобы получить все участки
		$link = $this->getSyncObject('Lpu', $data['Lpu_id']);
		
		if (empty($link['Object_Value'])) {
			return $this->err('Ошибка получения данных по участкам с портала РПН: текущая МО не сопоставлена с МО сервиса РПН.');
		} else {
			$rpnMo_id = $link['Object_Value'];
		}
		// Получаем данные по участку
		$result = $this->exec('/territory/org/'.$rpnMo_id);
		$this->textlog->add("result: ".print_r($result, true));
		$stat = array('all'=>count($result),'ins'=>0,'upd'=>0, 'del'=>0, 'err'=>0);
		// Обрабатываем весь список участков добавляя, редактируя и удаляя
		if (!is_array($result)) {
			$this->writeDetailLog((isset($data['Lpu_Nick']) ? $data['Lpu_Nick'] : $data['Lpu_id']) . ": ошибка при запросе данных об участках", 2);
			return $this->err('Ошибка получения данных по участкам с портала РПН.');
		} else if (!empty($result['errorMsg'])) {
			return $this->err($result['errorMsg']);
		} else if (count($result) == 0) {
			return $this->err('Отсутсвуют участки на портале РПН по данному МО.');
		} else {
			foreach ($result as $k=>$row) { // каждый из участков надо проверить на совпадение и сохранить 
				// получаем данные
				
				$region = array(
					'rpnLpuRegion_id'=>$row->TerritoryServiceID,
					'LpuRegion_Name'=>trim($row->territotyServiceNumber),
					'orgHealthCareID'=>$row->orgHealthCareID,
					'territotyServiceProfileID'=>$row->territotyServiceProfileID,
					'actualDoctorID'=>$row->actualDoctorID,
					//[doctors] => 
					'addreses'=>$row->addreses,
					'LpuRegion_begDate'=>$row->beginDate,
					'LpuRegion_endDate'=>$row->endDate,
					'LpuRegion_Descr'=>trim($row->territoryDescription),
					'LpuRegion_id'=>null,
					'LpuRegionType_id'=>1, // это по умолчанию если ничего не указано
					'LpuSection_id'=>null, 
					'Lpu_id'=>$data['Lpu_id'], 
					'Server_id'=>$data['Server_id'], 
					'pmUser_id'=>$data['pmUser_id']
				);
				if (!empty($row->territotyServiceProfileID)) {
					$region['LpuRegionType_id'] = $this->getLpuRegionTypeId($row->territotyServiceProfileID);
					if (!$region['LpuRegionType_id']) {
						$region['LpuRegionType_id'] = '1';
					}
				}
				
				// todo: Информация о врачах на участке, вообще видимо может быть массивом 
				/*if (isset($row->actualDoctor)) {
					$actualDoctor = $row->actualDoctor;
					$region['actualDoctor'] = array(
						'DoctorID'=>$actualDoctor->DoctorID,
						//'PersonID'=>$row->actualDoctor->PersonID,
						//'TerritoryServiceID'=>$row->actualDoctor->TerritoryServiceID,
						//'iin'=>$row->actualDoctor->iin,
						'doctorFio'=>$actualDoctor->doctorFio,
						//[staffTypeID] => 1
						//[staffPostID] => 1
						//[occupiedRates] => 1
						'beginDate'=>$actualDoctor->beginDate,
						'endDate'=>$actualDoctor->endDate
					);
				}*/
				// проверяем наличие связи установленной ранее с нашим участком (LpuRegion_id)
				$link = $this->getSyncObject('LpuRegion', $region['rpnLpuRegion_id'], 'Object_sid');
				$insert = false;
				$LpuRegion_id = null;

				if (!empty($link['Object_id'])) { // Если связь уже ранее установлена
					// проверяем необходимость обновления и редактируем по необходимости
					$LpuRegion_id = $link['Object_id'];
					$LpuRegionData = $this->Utils_model->GetObjectList(array('LpuRegion_id'=>$LpuRegion_id, 'Lpu_id'=>$data['Lpu_id'], 'object' =>'LpuRegion')); // получаем данные об участке
					if (is_array($LpuRegionData) && count($LpuRegionData)>0) { // если есть данные в Промеде
						$LpuRegion = $LpuRegionData[0];

						if (
							$region['LpuRegion_Name']!=$LpuRegion['LpuRegion_Name'] ||
							$region['LpuRegionType_id']!=$LpuRegion['LpuRegionType_id'] ||
							date("Y-m-d", strtotime($region['LpuRegion_begDate']))!=date("Y-m-d", strtotime($LpuRegion['LpuRegion_begDate'])) ||
							date("Y-m-d", strtotime($region['LpuRegion_endDate']))!=date("Y-m-d", strtotime($LpuRegion['LpuRegion_endDate']))
						) {
							// данные для апдейта
							$LpuRegion['LpuRegion_Name'] = $region['LpuRegion_Name'];
							$LpuRegion['LpuRegionType_id'] = $region['LpuRegionType_id'];
							$LpuRegion['LpuRegion_begDate'] = $region['LpuRegion_begDate'];
							$LpuRegion['LpuRegion_endDate'] = $region['LpuRegion_endDate'];
							$LpuRegion['LpuRegion_Descr'] = $region['LpuRegion_Descr'];
							$LpuRegion['Server_id'] = $region['Server_id'];
							$LpuRegion['pmUser_id'] = $region['pmUser_id'];
							$LpuRegion['noTransferService'] = true;
							$LpuRegion['allowEmptyMedPersonalData'] = true;
							$LpuRegion['ignorePersonCardCheck'] = true;
							// апдейтим данные
							$upd = $this->LpuStructure_model->SaveLpuRegion($LpuRegion);
							if (is_array($upd) && empty($upd[0]['Error_Msg'])) {
								// считаем проапдейченные
								$stat['upd']++;
								if (!empty($LpuRegion['LpuRegion_endDate'])) {
									$stat['del']++; // закрытые будем считать удаленными
								}
							} else {
								// ошибки при удалении
								if ($data['pmUser_id'] == 1) { // если запуск из сервиса, то только считаем и пишем в лог
									$stat['err']++;
									$this->writeDetailLog('Участок №' . $LpuRegion['LpuRegion_Name'] . ': ' . $upd[0]['Error_Msg']);
								} else {
									return $this->err($upd[0]['Error_Msg']);
								}
							}
						}
					} else {
						// почему то запись удалена, нужно добавить
						$insert = true;
					}
				} else {
					$insert = true;
				}
				if ($insert) { // сохраняем тех, которые не связанные, проверяя по номерам участков и связывая
					
					// сначала проверим наличие участка с таким же типом и номером
					$LpuRegion_id = $this->getFirstResultFromQuery("
						select 
							LR.LpuRegion_id as \"LpuRegion_id\"
						from
							v_LpuRegion LR 
							LEFT JOIN LATERAL(
								select Object_sid
								from v_ObjectSynchronLog OSL 
								where OSL.Object_id = LR.LpuRegion_id and OSL.Object_Name = 'LpuRegion'
								and OSL.ObjectSynchronLogService_id = :ObjectSynchronLogService_id
								limit 1
							) OSL ON true
						where
							LpuRegionType_id = :LpuRegionType_id
							and LpuRegion_Name = :LpuRegion_Name
							and Lpu_id = :Lpu_id
							and OSL.Object_sid is null
						limit 1
					", array(
						'LpuRegionType_id' => $region['LpuRegionType_id'],
						'LpuRegion_Name'=>$region['LpuRegion_Name'],
						'Lpu_id' => $data['Lpu_id'],
						'ObjectSynchronLogService_id' => $this->ObjectSynchronLog_model->serviceId,
					));
					$region['LpuRegion_id'] = ($LpuRegion_id)?$LpuRegion_id:null;
					
					// сохраняем
					$region['noTransferService'] = true;
					$region['allowEmptyMedPersonalData'] = true;
					$region['ignorePersonCardCheck'] = true;
					$ins = $this->LpuStructure_model->SaveLpuRegion($region);

					if (!empty($ins[0]['Error_Msg']) && !in_array($ins[0]['Error_Code'], array(1))) {
						return $this->err($ins[0]['Error_Msg']);
					} else if (!empty($ins[0]['Error_Message'])) {
						return $this->err($ins[0]['Error_Message']);
					}
					if (!is_array($ins)) {
						return $this->err('Ошибка при сохранении участка');
					}
					// сохраняем связь
					$link = $this->saveSyncObject('LpuRegion',$ins[0]['LpuRegion_id'], $region['rpnLpuRegion_id']);
					// считаем проапдейченные Связанные или вновь созданные и связанные
					if ($LpuRegion_id) {
						$stat['upd']++;
					} else {
						$stat['ins']++;
					}
				}

				if (!empty($LpuRegion_id)) {
					//Импорт территории участка
					$data['LpuRegion_id'] = $LpuRegion_id;
					$data['rpnLpuRegion_id'] = $region['rpnLpuRegion_id'];
					$resp = $this->importLpuRegionAddressesData($data);
					if (!empty($resp[0]['Error_Msg'])) {
						return $this->err($resp[0]['Error_Msg']);
					}
				}
			}
		}
		// после того как все проапдейтили нужно вывести информацию по участкам которые нужно удалить
		$message = "Всего получено участков: ".$stat['all'];
		if ($stat['ins']==0 && $stat['upd']==0) {
			$message .= ", данные актуальны, обновление не требуется.<br/>";
		} else {
			$message .= ", добавлено: <b>".$stat['ins']."</b>, обновлено: <b>".$stat['upd']."</b><br/>";
		}
		
		$query = "
			select
				LpuRegion.LpuRegion_id as \"LpuRegion_id\",
				LpuRegion.LpuRegion_Name as \"LpuRegion_Name\", 
				LpuRegionType_Name  as \"LpuRegionType_Name\"
			from v_LpuRegion LpuRegion 
			where
				Lpu_id = :Lpu_id and 
				not exists(Select 1 from ObjectSynchronLog   where Object_Name = 'LpuRegion' and ObjectSynchronLogService_id=2 and Object_id = LpuRegion.LpuRegion_id)";
		$result = $this->db->query($query, array('Lpu_id'=>$data['Lpu_id']));
		if (is_object($result)) {
			$regionForDelete = $result->result('array');
			if (count($regionForDelete)>0) {
				$message .= " <br/>В сервисе РПН нет следующих участков: <br/>";
				foreach ($regionForDelete as $k=>$v) {
					$message .= $v['LpuRegion_Name']." (".$v['LpuRegionType_Name'].")<br/> ";
				}
			}
		}
		$this->textlog->add("getLpuRegionList finish");
		
		$this->writeDetailLog((isset($data['Lpu_Nick']) ? $data['Lpu_Nick'] : $data['Lpu_id']) . ": новых участков {$stat['ins']}, закрыто: {$stat['del']} Не удалось закрыть: {$stat['err']}");
		
		return array(array('Message' => $message, 'success' => true));
		//$result = $this->exec('/territory/223264/doctors');
		//print_r($result);
	}
	/**
	 * Передача информации о прикреплении в сервис РПН
	 */
	function setPersonRPN($data, $response) {
		$this->textlog->add("\n\rsetPersonRPN start");
		// Проверяем наличие связи, если связь есть, значит нужно апдейтить, если связи нет, значит добавляем запись
		$link = $this->getSyncObject('PersonCard', $response[0]['PersonCard_id']);
		$this->textlog->add('Получили связь по прикреплению: '.(isset($link['Object_Value'])?$link['Object_Value']:'пусто по PersonCard_id = '.$response[0]['PersonCard_id']));
		// orgHealthCareID определяем по связи с МО
		$mo = $this->getSyncObject('Lpu', $data['Lpu_id']);
		$this->textlog->add('Получили связь по МО: '.(isset($mo['Object_Value'])?$mo['Object_Value']:'пусто по Lpu_id = '.$data['Lpu_id']));
		// todo: Если смена участка, то делаем api/attachment/person/reattach, если новое прикрепление, то api/Attachment
		
		// получаем Person_id сервиса, чтобы выполнить сохранение
		$person = $this->getSyncObject('Person', $data['Person_id']);
		$this->textlog->add('Получили связь по Person_id: '.(isset($person['Object_Value'])?$person['Object_Value']:'пусто по Person_id = '.$data['Person_id']));
		
		if ((!isset($mo['Object_Value'])) || (!isset($person['Object_Value']))) {
			$this->textlog->add('Связь по МО или по персону не установлена, передача данных об прикреплении в сервис РПН невозможна. Предварительно нужно проставить связи между МО и получить данные о прикреплении из сервиса.');
			//return $this->err('Связь по МО или по персону не установлена, передача данных об прикреплении в сервис РПН невозможна.<br/>Предварительно нужно проставить связи между МО и получить данные о прикреплении из сервиса.');
		} else {
			// получаем участок 
			$lpuregion = $this->getSyncObject('LpuRegion', $data['LpuRegion_id']);
			if (!$lpuregion['Object_Value']) {
				// Не удалось определить Участок, передача данных невозможна
				$this->textlog->add('Связь по участкам не установлена, передача данных об прикреплении в сервис РПН невозможна. Предварительно нужно получить информацию об участках из сервиса РПН.');
				//return $this->err('Связь по участкам не установлена, передача данных об прикреплении в сервис РПН невозможна.<br/>Предварительно нужно получить информацию об участках из сервиса РПН.');
			} else {
				$attach = array(
					'AttachmentID'=> $link['Object_Value'],
					'PersonID'=> $person['Object_Value'],
					//'attachmentStatus'=> null, // Статус прикрепления
					'beginDate'=> $data['PersonCard_begDate'],
					'endDate'=> $data['PersonCard_endDate'],
					'orgHealthCare'=> array('id'=>$mo['Object_Value']),
					//'causeOfAttach'=> null, //Причина прикрепления
					//'attachmentProfile'=> null, // Профиль
					'territoryServiceID'=> $lpuregion['Object_Value'],
					//'territotyServiceNumber' 	Номер участка
					//'territoryServiceProfileID' 	ID Профиля участка
					//'person'=> array('id'=>$person['Object_Value']),
				);
				$params = json_encode($attach);
				if (empty($link['Object_Value'])) { // Если нет связи, то добавим
					$this->textlog->add('Сохраняем данные о новом прикреплении и создаем связь: '.$response[0]['PersonCard_id'].' = '. $link['Object_Value']);
					$link['Object_Value'] = $this->exec('/Attachment', 'put', $params);
					// И заинсертим новую связь (пока только для добавленных)
					$this->saveSyncObject('PersonCard',$response[0]['PersonCard'], $link['Object_Value']);
				} else { // обновим участок если поменялся 
					// todo: Пока ничего не делаем, и пока не понятно что делать.
					//$result = $this->exec('attachment/person/reattach', 'put', $params);
				}
			}
		}
		$this->textlog->add("\n\rsetPersonRPN finish");
	}

	/**
	 * Передача информации о участке в сервис РПН
	 */
	function setLpuRegion($data, $response) {
		/*print_r($data);
		print_r($response);*/
		$this->textlog->add("\n\rsetLpuRegion start");
		// Проверяем наличие связи, если связь есть, значит нужно апдейтить, если связи нет, значит добавляем запись
		$link = $this->getSyncObject('LpuRegion', $response[0]['LpuRegion_id']);
		$this->textlog->add('Получили связь по участку: '.(isset($link['Object_Value'])?$link['Object_Value']:'пусто по LpuRegion_id = '.$response[0]['LpuRegion_id']));
		// orgHealthCareID определяем по связи с МО
		$mo = $this->getSyncObject('Lpu', $data['Lpu_id']);
		$this->textlog->add('Получили связь по МО: '.(isset($mo['Object_Value'])?$mo['Object_Value']:'пусто по Lpu_id = '.$data['Lpu_id']));
		if (!isset($mo['Object_Value'])) {
			$this->textlog->add('Связь по МО отсутствует, передача данных об участке в сервис РПН невозможна. Предварительно нужно проставить связи между МО.');
			//return $this->err('Связь по МО не установлена, передача данных об участке в сервис РПН невозможна.<br/>Предварительно нужно проставить связи между МО.');
		} else {
			// определяем тип участка по 
			$LpuRegionType_SysNick = $this->getFirstResultFromQuery(
				'select LpuRegionType_SysNick  as "LpuRegionType_SysNick" from v_LpuRegionType  where LpuRegionType_id = :LpuRegionType_id',
				array('LpuRegionType_id' => $data['LpuRegionType_id'])
			);
			$territotyServiceProfileID = 1;
			if (!$territotyServiceProfileID) {
				// Не удалось определить тип участка, передача данных невозможна
				$this->textlog->add('Связь по типу участка отсутствует, передача данных об участке в сервис РПН невозможна. Предварительно нужно проставить связи между типами участков.');
				//return $this->err('Связь по типу участка не установлена, передача данных об участке в сервис РПН невозможна.<br/>Предварительно нужно проставить связи между типами участков.');
			} else {
				$lpuregion = array(
					'TerritoryServiceID'=> $link['Object_Value'],
					'territotyServiceNumber'=>$data['LpuRegion_Name'],
					'orgHealthCareID'=>$mo['Object_Value'],
					'territotyServiceProfileID'=>$this->getTerrServiceProfileId($LpuRegionType_SysNick),
					//'actualDoctorID'=>null,
					//'doctors' 	Collection of Doctor 
					//'addreses' 	Collection of Ate 
					//'actualDoctor' 	Doctor 
					'endDate'=>$data['LpuRegion_endDate'],
					'territoryDescription'=>$data['LpuRegion_Descr'],
					'beginDate'=>$data['LpuRegion_begDate']
				);
				$params = json_encode($lpuregion);
				if (empty($link['Object_Value'])) { // Если нет связи, то добавим
					$this->textlog->add('Сохраняем данные о новом участке и создаем связь: '.$response[0]['LpuRegion_id'].' = '. $link['Object_Value']);
					$link['Object_Value'] = $this->exec('/territory/insert', 'put', $params);
					// И заинсертим новую связь (пока только для добавленных)
					$this->saveSyncObject('LpuRegion',$response[0]['LpuRegion_id'], $link['Object_Value']);
				} else { // обновим
					$this->textlog->add('Обновляем данные об участке');
					$result = $this->exec('/territory/update', 'put', $params);
					print_r($result);
					print_r($params);
				}
			}
		}
		$this->textlog->add('setLpuRegion finish');
	}
	
	/**
	 * Получение любого справочника
	 */
	function getSprList($data) {
		$name = $data['spr'];
		$page = !empty($data['page'])?$data['page']:1;
		$size = !empty($data['pagesize'])?$data['pagesize']:100;
		$result = $this->exec("/dict/{$name}/page/{$page}/{$size}");
		echo "<style> 
			td { border: #cccccc 1px dotted; }
			thead { font-weight: bolder; }
		</style>";

		echo "<table border='1' style='width:90%;min-width:800px;'>";
		echo "<thead><td>№</td><td>id</td><td>Наименование RU</td><td>Наименование KZ</td></thead>";
		foreach($result as $k=>$row) {
			echo "<tr><td>".($k+1)."</td><td>".$row->id."</td><td>".$row->nameRU."</td><td>".$row->nameKZ."</td></tr>";
		}
		echo "</table>";

		return true;
	}
	
	/**
	 * Получение getOblList областей
	 */
	function getOblList($data) {
		$result = $this->exec("/addresses/obls");
		//print_r($result);
		echo "<style> 
			td { border: #cccccc 1px dotted; }
			thead { font-weight: bolder; }
		</style>";
		echo "<table border='1' style='width:90%;min-width:800px;'>";
		echo "<thead><td>ateID</td><td>Наименование RU</td><td>Наименование KZ</td></thead>";
		foreach($result as $k=>$row) {
			// загрузим в БД
			//$sql_insert .= "(".$row->id.", ".$row->nameRU.", ".$row->nameRU." ) ,";
			echo "<tr><td>".$row->ateID."</td><td>".$row->nameRU."</td><td>".$row->nameKZ."</td></tr>";
		}
		echo "</table>";
		return true;
	}
	
	/**
	 * Получение информации об МО по области
	 */
	function getMOList($data) {
		$result = $this->exec("/addresses/pmsps/{$data['level']}");
		//print_r($result);
		echo "<style> 
			td { border: #cccccc 1px dotted; }
			thead { font-weight: bolder; }
		</style>";
		echo "<table border='1' style='width:90%;min-width:800px;'>";
		echo "<thead><td>id</td><td>Наименование МО</td><td>originalID</td><td>oblId</td></thead>";
		foreach($result as $k=>$row) {
			// загрузим в БД
			//$sql_insert .= "(".$row->id.", ".$row->nameRU.", ".$row->nameRU." ) ,";
			echo "<tr><td>".$row->id."</td><td>".$row->name."</td><td>".$row->originalID."</td><td>".$row->oblId."</td></tr>";
		}
		echo "</table>";
		return true;
	}

	/**
	 * Формирование данных о человеке для возврата на форму редактирования
	 */
	function createPersonResponseData($person) {
		$mapping = array(
			'PersonID' => 'BDZ_id',
			'lastName' => 'Person_SurName',
			'firstName' => 'Person_FirName',
			'secondName' => 'Person_SecName',
			'iin' => 'PersonInn_Inn',
			'birthDate' => 'Person_BirthDay',
			'deathDate' => 'Person_deadDT',
		);
		$personData = $this->convertParams(objectToArray($person), $mapping);
		$personData['Person_SurName'] = mb_strtoupper($personData['Person_SurName']);
		$personData['Person_FirName'] = mb_strtoupper($personData['Person_FirName']);
		$personData['Person_SecName'] = mb_strtoupper($personData['Person_SecName']);
		if (!empty($personData['Person_BirthDay'])) {
			$personData['Person_BirthDay'] = date_format(date_create($personData['Person_BirthDay']), 'd.m.Y');
		} else {
			$personData['Person_BirthDay'] = '';
		}
		if (!empty($personData['Person_deadDT'])) {
			$personData['Person_deadDT'] = date_format(date_create($personData['Person_deadDT']), 'd.m.Y');
		} else {
			$personData['Person_deadDT'] = '';
		}
		if (!empty($person->sex)) {
			$sexlink = $this->getSexLink($person->sex);
			$personData['PersonSex_id'] = $sexlink['Object_id'];
		}
		if (!empty($person->national)) {
			$Ethnos_id = $this->getFirstResultFromQuery("
				select Ethnos_id as \"Ethnos_id\"
				from v_Ethnos 
				where Ethnos_Code = :Ethnos_Code
				limit 1
			", array(
				'Ethnos_Code' => $person->national
			));
			$personData['Ethnos_id'] = $Ethnos_id?$Ethnos_id:null;
		}
		if (!empty($person->citizen)) {
			$KLCountry_id = $this->getFirstResultFromQuery("
				select KLCountry_id as \"KLCountry_id\"
				from r101.CitizenshipLink 
				where p_ID = :p_ID
				limit 1
			", array(
				'p_ID' => $person->citizen
			));
			$personData['KLCountry_id'] = $KLCountry_id?$KLCountry_id:null;
		}

		$socStatusData = $this->getSocStatusData($person->PersonID);
		if (isset($socStatusData[0]) && !empty($socStatusData[0]['Error_Msg'])) {
			return $socStatusData[0];
		}
		if (empty($socStatusData['SocStatus_id'])) {
			$socStatusData = array();
		}

		$addressesData = $this->getPersonAddressesData($person->PersonID);

		return array_merge($personData, $addressesData, $socStatusData);
	}

	/**
	 * Идентификация человека в сервисе РПН
	 *
	 * @param array $data
	 * @return array
	 */
	function doPersonIdentRequest($data) {
		set_time_limit(0);
		$this->load->model('Person_model');

		$mapping = array(
			'Person_SurName' => 'lastName',
			'Person_FirName' => 'firstName',
			'Person_SecName' => 'secondName',
			'PersonInn_Inn' => 'iin',
			'Person_BirthDay' => array('dtRoj','dtRojDo'),
		);

		//Идентификация по всем параметрам
		$params = json_encode($this->convertParams($data, $mapping));
		//$params = json_encode(array('fioiin' => $data['Person_SurName']));
		$result = $this->exec('/person/search4/1/2', 'post', $params);
		if (is_array($result) && !empty($result['errorMsg'])) {
			return array('Error_Msg' => $result['errorMsg']);
		}

		$person = null;
		$Person_IsInErz = null;	//Статус идентификации. 1 - отрецительный ответ, 2 - положительный ответ
		if (count($result) > 1) {
			$Person_IsInErz = 1;
		} else if(count($result) == 1) {
			$person = $result[0];
			$Person_IsInErz = 2;
		}

		if (!$Person_IsInErz) {
			//Идентификация по 4-м параметрам
			$combinations = array(
				array('Person_SurName','Person_FirName','Person_SecName','PersonInn_Inn'),
				array('Person_SurName','Person_FirName','Person_SecName','Person_BirthDay'),
				array('Person_FirName','Person_SecName','Person_BirthDay','PersonInn_Inn'),
				array('Person_SurName','Person_SecName','Person_BirthDay','PersonInn_Inn'),
				array('Person_SurName','Person_FirName','Person_BirthDay','PersonInn_Inn'),
			);
			$resultCountList = array();
			foreach($combinations as $combination) {
				$params = json_encode($this->convertParams($data, $mapping, $combination));
				$result = $this->exec('/person/search4/count', 'post', $params);
				if (is_array($result) && !empty($result['errorMsg'])) {
					return array('Error_Msg' => $result['errorMsg']);
				}
				$resultCountList[] = $result->Count;
			}
			$foundIndex = -1;
			$ident = true;
			foreach($resultCountList as $index => $count) {
				if ($count > 0) {
					$foundIndex = $index;
				}
				if ($count > 1) {
					$ident = false;
				}
			}
			if ($foundIndex >= 0) {
				if ($ident) {
					$Person_IsInErz = 2;
					$params = json_encode($this->convertParams($data, $mapping, $combinations[$foundIndex]));
					$result = $this->exec("/person/search4/1/1", 'post', $params);
					if (is_array($result) && !empty($result['errorMsg'])) {
						return array('Error_Msg' => $result['errorMsg']);
					}
					$person = $result[0];
				} else {
					$Person_IsInErz = 1;
				}
			}
		}

		if (!$Person_IsInErz) {
			//Идентификация по 3-м параметрам
			$combinations = array(
				array('Person_SurName','Person_FirName','Person_BirthDay'),
				array('Person_SurName','Person_FirName','PersonInn_Inn'),
			);
			if (!empty($data['Person_SecName'])) {
				$combinations = array_merge($combinations, array(
					array('Person_FirName','Person_SecName','Person_BirthDay'),
					array('Person_FirName','Person_SecName','PersonInn_Inn'),
					array('Person_SurName','Person_SecName','Person_BirthDay'),
					array('Person_SurName','Person_SecName','PersonInn_Inn'),
				));
			}
			$resultCountList = array();
			foreach($combinations as $combination) {
				$params = json_encode($this->convertParams($data, $mapping, $combination));
				$result = $this->exec('/person/search4/count', 'post', $params);
				if (is_array($result) && !empty($result['errorMsg'])) {
					return array('Error_Msg' => $result['errorMsg']);
				}
				$resultCountList[] = $result->Count;
			}
			$foundIndex = -1;
			$ident = true;
			foreach($resultCountList as $index => $count) {
				if ($count > 0) {
					$foundIndex = $index;
				}
				if ($count > 1) {
					$ident = false;
				}
			}
			if ($foundIndex >= 0) {
				if ($ident) {
					$Person_IsInErz = 2;
					$params = json_encode($this->convertParams($data, $mapping, $combinations[$foundIndex]));
					$result = $this->exec("/person/search4/1/1", 'post', $params);
					if (is_array($result) && !empty($result['errorMsg'])) {
						return array('Error_Msg' => $result['errorMsg']);
					}
					$person = $result[0];
				} else {
					$Person_IsInErz = 1;
				}
			}
		}

		$response = array('Person_IsInErz' => $Person_IsInErz, 'Person_identDT' => time());
		if ($Person_IsInErz == 2) {
			if (!is_object($person)) {
				return array('Error_Msg' => 'Отсутсвуют данные человека из сервиса РПН');
			}

			$personResponse = $this->createPersonResponseData($person);
			if (!empty($personResponse['Error_Msg'])) {
				return $personResponse;
			}

			$response = array_merge($response, $personResponse);
		}

		return $response;
	}

	/**
	 * Получение данных человека по ИД из РПН
	 */
	function getPersonData($rpnPerson_id, $rpnPerson = null) {
		if (!$rpnPerson) {
			$rpnPerson = $this->exec("/person/$rpnPerson_id");
			if (is_array($rpnPerson_id) && !empty($rpnPerson_id['errorMsg'])) {
				return array('Error_Msg' => $rpnPerson_id['errorMsg']);
			}
		}

		$response = $this->createPersonResponseData($rpnPerson);
		if (!empty($response['Error_Msg'])) {
			return $response;
		}

		return $response;
	}

	//Методы сервисов синхронизации
	/**
	 * Идентификация людей в РПН и обновление данных
	 */
	function identPerson($data, $rpnPerson = null) {
		if ($data['Person_IsInErz'] == 2 && !empty($data['BDZ_id'])) {
			$identResponse = $this->getPersonData($data['BDZ_id'], $rpnPerson);
			$identResponse['Person_IsInErz'] = 2;
		} else {
			$identResponse = $this->doPersonIdentRequest($data);
		}

		if (!empty($identResponse['Error_Msg'])) {
			return $this->createError('', $identResponse['Error_Msg']);
		}

		$this->beginTransaction();
		$this->isAllowTransaction = false;

		$this->load->model('Person_model');
		if ($identResponse['Person_IsInErz'] == 2) {
			$date_fields = array('Person_BirthDay','Person_deadDT');
			foreach($date_fields as $field) {
				if (!empty($identResponse[$field])) {
					$identResponse[$field] = ConvertDateFormat($identResponse[$field]);
				}
			}

			$PersonEvnAttributes = array(
				'Person_SurName','Person_FirName','Person_SecName','Person_BirthDay','PersonInn_Inn','PersonSex_id','SocStatus_id'
			);

			$EvnTypeList = array();
			$params = array();

			foreach($PersonEvnAttributes as $attribute) {
				if (array_key_exists($attribute, $identResponse) && $identResponse[$attribute] != $data[$attribute]) {
					$EvnTypeList[] = $attribute;
					$params[$attribute] = $identResponse[$attribute];
				}
			}
			if (!empty($identResponse['UAddress'])) {
				$EvnTypeList[] = 'UAddress';
				$params = array_merge($params, $identResponse['UAddress']);
			}
			if (!empty($identResponse['PAddress'])) {
				$EvnTypeList[] = 'PAddress';
				$params = array_merge($params, $identResponse['PAddress']);
			}
			if (!empty($identResponse['BAddress'])) {
				$EvnTypeList[] = 'BAddress';
				$params = array_merge($params, $identResponse['BAddress']);
			}
			if (!empty($identResponse['KLCountry_id'])) {
				$EvnTypeList[] = 'NationalityStatus';
				$params = array_merge($params, $identResponse['KLCountry_id']);
			}

			if (count($EvnTypeList) > 0) {
				$params = array_merge($params, array(
					'EvnType' => implode('|',$EvnTypeList),
					'pmUser_id' => $data['pmUser_id'],
					'Server_id' => $data['Server_id'],
					'session' => null,
					'Person_id' => $data['Person_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
				));

				$this->Person_model->exceptionOnValidation = true;	//Создает исключение при ошибке
				$resp = $this->Person_model->editPersonEvnAttributeNew($params);
				if (!empty($resp[0]['Error_Msg'])) {
					return $resp;
				}
			}

			if ($identResponse['Ethnos_id'] != $data['Ethnos_id']) {
				$resp = $this->Person_model->savePersonInfo(array(
					'Person_id' => $data['Person_id'],
					'Server_id' => $data['Server_id'],
					'Ethnos_id' => $identResponse['Ethnos_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($resp[0]['Error_Msg'])) {
					return $resp;
				}
			}

			if ($identResponse['Person_deadDT'] != $data['Person_deadDT']) {
				if (empty($identResponse['Person_deadDT']) && !empty($data['Person_deadDT'])) {
					$resp = $this->Person_model->revivePerson(array(
						'Person_id' => $data['Person_id'],
						'pmUser_id' => $data['pmUser_id'],
					));
					if (!empty($resp[0]['Error_Msg'])) {
						return $resp;
					}
				} else if(!empty($identResponse['Person_deadDT'])) {
					$resp = $this->Person_model->killPerson(array(
						'Person_id' => $data['Person_id'],
						'Person_deadDT' => $identResponse['Person_deadDT'],
						'pmUser_id' => $data['pmUser_id'],
					));
					if (!empty($resp[0]['Error_Msg'])) {
						return $resp;
					}
				}
			}

			if ($identResponse['Person_IsInErz'] != $data['Person_IsInErz'] || $identResponse['BDZ_id'] != $data['BDZ_id']) {
				$resp = $this->Person_model->updatePerson(array(
					'Person_id' => $data['Person_id'],
					'BDZ_id' => $identResponse['BDZ_id'],
					'Person_IsInErz' => $identResponse['Person_IsInErz'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($resp[0]['Error_Msg'])) {
					return $resp;
				}
			}

			if (!empty($identResponse['BDZ_id'])) {
				$this->saveSyncObject('Person', $data['Person_id'], $identResponse['BDZ_id']);
			}
		} else {
			if ($identResponse['Person_IsInErz'] == null) {
				$identResponse['Person_IsInErz'] = 1;
			}
			if ($identResponse['Person_IsInErz'] != $data['Person_IsInErz']) {
				$resp = $this->Person_model->updatePerson(array(
					'Person_id' => $data['Person_id'],
					'Person_IsInErz' => $identResponse['Person_IsInErz'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($resp[0]['Error_Msg'])) {
					return $resp;
				}
			}
		}

		$this->isAllowTransaction = true;
		$this->commitTransaction();

		return $identResponse;
	}

	/**
	 * Автоматическая идентификация людей в РПН (старый вариант)
	 */
	function startPersonIdent() {
		set_error_handler(array($this, 'exceptionErrorHandler'));
		try {
			$query = "
				select
					-- select
					P.BDZ_id as \"BDZ_id\",
					PS.Person_id as \"Person_id\",
					PS.PersonEvn_id as \"PersonEvn_id\",
					PS.Server_id as \"Server_id\",
					PS.Person_IsInErz as \"Person_IsInErz\",
					PS.Person_SurName as \"Person_SurName\",
					PS.Person_FirName as \"Person_FirName\",
					PS.Person_SecName as \"Person_SecName\",
					PS.Sex_id as \"PersonSex_id\",
					PS.Person_Inn as \"PersonInn_Inn\",
					PS.SocStatus_id as \"SocStatus_id\",
					to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\",
					to_char(PS.Person_deadDT, 'YYYY-MM-DD') as \"Person_deadDT\",
					PI.Ethnos_id as \"Ethnos_id\"
					-- end select
				from
					-- from
					v_PersonState PS 
					inner join Person P  on P.Person_id = PS.Person_id
					left join v_PersonInfo PI  on PI.Person_id = PS.Person_id
					-- end from
				where
					-- where
					PS.Person_IsInErz is null
					/*and exists(
						select * from ObjectSynchronLog
						where Object_id = PS.Person_id and Object_Name = 'Person' and ObjectSynchronLogService_id = 2
					)*/
					-- end where
				order by
					-- order by
					P.BDZ_id desc,
					PS.PersonState_updDT asc
					-- end order by
			";

			$count = $this->getFirstResultFromQuery(getCountSQLPH($query));
			if ($count === false) {
				throw new Exception('Ошибка при получении количества людей для идентификации');
			}

			$pageSize = 1000;
			$pageCount = ceil($count/$pageSize);
			$i = 0;

			$this->textlog->add("start ident count: $count, pageCount: $pageCount");

			$this->load->model('Person_model');
			for($page = 1; $page <= $pageCount; $page++) {
				$this->textlog->add("start page $page/$pageCount, pageSize: $pageSize");
				$person_list = $this->queryResult(getLimitSQLPH($query, $pageSize*($page-1), $pageSize));
				if (!is_array($person_list)) {
					throw new Exception('Ошибка при получении списка людей для идентификации');
				}

				foreach($person_list as $person) {
					$i++;
					$this->textlog->add("start ident person #$i id: {$person['Person_id']}");

					$person['pmUser_id'] = 1;
					$identResponse = $this->identPerson($person);
					$this->textlog->add("identResponse ".print_r($identResponse,true));

					if (!$this->isSuccessful($identResponse)) {
						$error = isset($identResponse[0])?$identResponse[0]['Error_Msg']:'Неизвестная ошибка при идентификации';
						throw new Exception($error);
					}

					$this->textlog->add("finish ident person #$i id: {$person['Person_id']} Person_IsInErz: {$identResponse['Person_IsInErz']}");
				}
				$this->textlog->add("finish page $page/$pageCount, pageSize: $pageSize");
			}
		} catch(Exception $e) {
			restore_exception_handler();

			$this->isAllowTransaction = true;
			$this->rollbackTransaction();

			$this->textlog->add("startPersonIdent Exception: ".$e->getCode()." ".$e->getMessage());
			$this->textlog->add($e->getTraceAsString());

			return $this->createError($e->getCode(), $e->getMessage());
		}
		restore_exception_handler();
		return array(array('success' => true));
	}

	/**
	 * Сохранение данных объектов
	 */
	function saveObject($objectData, $objectName, $idField = null, $allowEmptyId = false) {
		if (empty($idField)) {
			$idField = $objectName.'_id';
		}
		if (empty($objectData[$idField]) && !$allowEmptyId) {
			return $this->createError('','Отсутвует идентификатор объекта');
		}
		if (empty($objectData['pmUser_id'])) {
			return $this->createError('','Отсутвует идентификатор пользователя');
		}
		
		$objectData = array_change_key_case($objectData, CASE_LOWER);
		$idField = mb_strtolower($idField);
		
		$_objectName = $objectName;
		$objectName = mb_strtolower($objectName);

		$savedData = array($objectName.'_id' => !empty($objectData[$objectName.'_id'])?$objectData[$objectName.'_id']:null);
		if (!empty($objectData[$idField])) {
			$resp = $this->queryResult("
				select *
				from {$this->scheme}.v_{$objectName}
				where {$idField} = :{$idField}
				limit 1
			", array(
				$idField => $objectData[$idField]
			));
			if (isset($resp[0])) {
				$savedData = $resp[0];
			}
		}
		$objectData = array_merge($savedData, $objectData);
		$objectData[$objectName.'_id'] = $savedData[$objectName.'_id'];
		if (empty($objectData[$objectName.'_id'])) {
			$objectData[$objectName.'_id'] = $this->getFirstResultFromQuery("
				select COALESCE((select max({$objectName}_id) from {$this->scheme}.{$objectName} ),0)+1
			");
		}

		$ignoreFields = array('pmuser_insid','pmuser_updid',$objectName.'_insdt',$objectName.'_upddt');
		$ignoreFields = array_change_key_case($ignoreFields, CASE_LOWER);

		$queryParams = array();
		$execPartParams = array();
		foreach($objectData as $field => $value) {
			if (in_array($field, $ignoreFields)) {
				continue;
			}
			if ($field != $objectName.'_id') {
				$execPartParams[] = "{$field} := :{$field}";
			}
			if ($value instanceof DateTime) {
				$value = $value->format('Y-m-d H:i:s');
			}
			if (is_bool($value)) {
				$value = ($value)?2:1;
			}
			$queryParams[$field] = !empty($value)?$value:null;
		}
		$execPartParamsStr = implode(",\n", $execPartParams);

		$query = "
			CREATE OR REPLACE FUNCTION pg_temp.exp_Query
            (   out _id  bigint, out _Error_Code int, out _Error_Message text
            )
            LANGUAGE 'plpgsql'
            AS $$
            DECLARE
				v_id bigint;
                v_{$idField} bigint := :{$idField};
            BEGIN
				v_id := COALESCE(CAST(:{$objectName}_id as bigint), (
				select {$objectName}_id as id
				from {$this->scheme}.{$objectName} 
				where {$idField} = v_{$idField}
                limit 1
				));
				IF v_id IS NULL or not exists(select * from {$this->scheme}.{$objectName} where {$objectName}_id = v_id)
                THEN
                  SELECT 
                      {$objectName}_id,
                      Error_Code,
                      Error_Message
                  INTO 
                      v_id,
                      _Error_Code,
                      _Error_Message
                  FROM {$this->scheme}.p_{$objectName}_ins(
                                  {$objectName}_id := v_id,
                                  {$execPartParamsStr});
                ELSE
                  SELECT 
                      {$objectName}_id,
                      Error_Code,
                      Error_Message
                  INTO 
                      v_id,
                      _Error_Code,
                      _Error_Message
                  FROM {$this->scheme}.p_{$objectName}_upd(
                                  {$objectName}_id := v_id,
                                  {$execPartParamsStr});
                END IF;

                exception
            	    when others then _Error_Code:=SQLSTATE; _Error_Message:=SQLERRM;

            END;
            $$;
			select _id as \"{$_objectName}_id\", _Error_Code as \"Error_Code\", _Error_Message as \"Error_Msg\"
            from pg_temp.exp_Query();
		";
		//echo getDebugSQL($query, $queryParams);exit;
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение связи домов и участков из РПН
	 */
	function saveGetBuildingGetTerrService($data) {
		$params = array(
			'GetBuilding_id' => $data['GetBuilding_id'],
			'GetTerrService_id' => $data['GetTerrService_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			CREATE OR REPLACE FUNCTION pg_temp.exp_Query
            (   out _id  bigint, out _Error_Code int, out _Error_Message text
            )
            LANGUAGE 'plpgsql'

            AS $$
            DECLARE
				v_id bigint;
            BEGIN
				v_id := (
				select GetBuildingGetTerrService_id
				from {$this->scheme}.v_GetBuildingGetTerrService 
				where GetBuilding_id = :GetBuilding_id and GetTerrService_id = :GetTerrService_id
                limit 1
				);
				IF v_id IS NULL 
                THEN
                  SELECT 
                      GetBuildingGetTerrService_id,
                      Error_Code,
                      Error_Message
                  INTO 
                      v_id,
                      _Error_Code,
                      _Error_Message
                  FROM {$this->scheme}.p_GetBuildingGetTerrService_ins(
                      GetBuildingGetTerrService_id := null,
                      GetBuilding_id := :GetBuilding_id,
                      GetTerrService_id := :GetTerrService_id,
                      pmUser_id := :pmUser_id);
                END IF;
                exception
            	    when others then _Error_Code:=SQLSTATE; _Error_Message:=SQLERRM;
            END;
            $$;
			select _id as \"GetBuildingGetTerrService_id\", _Error_Code as \"Error_Code\", _Error_Message as \"Error_Msg\"
            from pg_temp.exp_Query();
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении связи участка и дома из сервиса РПН');
		}
		return $resp;
	}

	/**
	 * Сохранение данных о домах из РПН
	 */
	function saveGetBuilding($Building, $pmUser_id) {
		$params = array(
			'GetBuilding_id' => $Building->BuildingID,
			'GetAddressTerr_id' => $Building->ateID,
			'GetBuilding_Number' => $Building->buildingNumber,
			'GetBuilding_begDate' => $Building->beginDate,
			'GetBuilding_endDate' => $Building->endDate,
			'GetBuilding_Address' => $Building->addressStringRU,
			'GetBuilding_AddressKZ' => $Building->addressStringKZ,
			'GetBuilding_FirstFlat' => $Building->firstApartment,
			'GetBuilding_LastFlat' => $Building->lastApartment,
			'GetFlatDiaposon_id' => null,
			'pmUser_id' => $pmUser_id,
		);
		$resp = $this->saveObject($params, 'GetBuilding');
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении территории участка из сервиса РПН');
		}
		return $resp;
	}

	/**
	 * Рекурсивно сохраняются данные о полученных из РПН адресах территорий участка
	 *
	 * @param array $AddressTerr
	 * @param int $pmUser_id
	 * @return array
	 */
	function saveGetAddressTerr($AddressTerr, $pmUser_id, $GetTerrService_id = null) {
		foreach ($AddressTerr as $addressElement) {
			$socr = $this->getFirstResultFromQuery("
				select KZSocr.KZKLSocr_Name as \"KZKLSocr_Name\"
				from {$this->scheme}.v_KZKLSocr KZSocr
				where KZSocr.KZKLSocr_id = :KZKLSocr_id
				limit 1
			", array('KZKLSocr_id' => $addressElement->ateTypeID));

			$name = $addressElement->nameRU;
			$elements = explode(',', $name);
			for($i=0; $i<count($elements); $i++) {
				$element = explode(':', $elements[$i]);
				if (count($element) == 2 && mb_strtoupper(trim($element[0])) == mb_strtoupper($socr)) {
					$name = mb_strtoupper(trim($element[1]));
				}
			}

			$params = array(
				'GetAddressTerr_id' => $addressElement->ateID,
				'GetAddressTerr_pid' => $addressElement->parentAteID,
				'GetAddressTerrType_id' => $addressElement->ateTypeID,
				'GetAddressTerr_Name' => $name,
				'GetAddressTerr_NameKZ' => $addressElement->nameKZ,
				'pmUser_id' => $pmUser_id,
			);
			$resp = $this->saveObject($params, 'GetAddressTerr');
			if (!is_array($resp)) {
				return $this->createError('','Ошибка при сохранении территории участка из сервиса РПН');
			}
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}

			if (count($addressElement->childs) > 0) {
				$resp = $this->saveGetAddressTerr($addressElement->childs, $pmUser_id, $GetTerrService_id);
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
			}

			if (count($addressElement->buildings) > 0) {
				foreach($addressElement->buildings as $building) {
					$resp = $this->saveGetBuilding($building, $pmUser_id);
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}

					if (!empty($GetTerrService_id)) {
						$resp = $this->saveGetBuildingGetTerrService(array(
							'GetBuilding_id' => $resp[0]['GetBuilding_id'],
							'GetTerrService_id' => $GetTerrService_id,
							'pmUser_id' => $pmUser_id
						));
						if (!$this->isSuccessful($resp)) {
							return $resp;
						}
					}
				}
			}
		}
		return array(array('success' => true));
	}

	/**
	 * Сохранение полученных данных участка из РПН
	 */
	function saveGetTerrService($TerrService, $pmUser_id) {
		$params = array(
			'GetTerrService_id' => $TerrService->TerritoryServiceID,
			'GetTerrService_Number' => $TerrService->territotyServiceNumber,
			'Org_ID' => $TerrService->orgHealthCareID,
			'GetTerrServiceProfile_ID' => $TerrService->territotyServiceProfileID,
			'GetDoctor_id' => $TerrService->actualDoctorID,
			'GetTerrService_begDate' => $TerrService->beginDate,
			'GetTerrService_endDate' => $TerrService->endDate,
			'GetTerrService_Desc' => $TerrService->territoryDescription,
			'pmUser_id' => $pmUser_id
		);
		$resp = $this->saveObject($params, 'GetTerrService');
		if (!is_array($resp)) {
			return $this->createError('Ошибка при сохранении участка из сервиса РПН');
		}
		return $resp;
	}

	/**
	 * Сохранение полученных данных врача на участке из РПН
	 */
	function saveGetDoctor($Doctor, $pmUser_id) {
		$params = array(
			'GetDoctor_id' => $Doctor->DoctorID,
			'Person_id' => $Doctor->PersonID,
			'GetTerrService_id' => $Doctor->TerritoryServiceID,
			'GetDoctor_IIN' => $Doctor->iin,
			'GetDoctor_FIO' => $Doctor->doctorFio,
			'GetStaffType_id' => $Doctor->staffTypeID,
			'GetStaffPost_id' => $Doctor->staffPostID,
			'GetDoctor_Rate' => $Doctor->occupiedRates,
			'GetDoctor_begDate' => $Doctor->beginDate,
			'GetDoctor_endDate' => $Doctor->endDate,
			'pmUser_id' => $pmUser_id,
		);
		$resp = $this->saveObject($params, 'GetDoctor');
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении врача участка из сервиса РПН');
		}
		return $resp;
	}

	/**
	 * Импорт  недостающих элементов адресов
	 */
	function importMissedGetAddressTerr($data) {
		$query = "
			select distinct t.GetAddressTerr_pid as \"GetAddressTerr_pid\"
			from {$this->scheme}.v_GetAddressTerr t 
			where t.GetAddressTerr_pid is not null
			and not exists(
				select r.GetAddressTerr_id
				from {$this->scheme}.v_GetAddressTerr r 
				where r.GetAddressTerr_id = t.GetAddressTerr_pid
			)
		";
		$resp = $this->queryResult($query);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при определении недостающих элементов адресов');
		}

		$addresses = array();
		foreach($resp as $item) {
			$pid = $item['GetAddressTerr_pid'];
			while($pid && !isset($addresses[$pid])) {
				$address = $this->exec("/addresses/ate/{$pid}");
				if (!empty($result['errorMsg'])) {
					return $this->createError('',$result['errorMsg']);
				}
				$addresses[$address->ateID] = $address;
				$pid = $address->parentAteID;
			}
		}

		if (count($addresses) > 0) {
			$ids = implode(',',array_keys($addresses));
			$resp = $this->queryResult("
				select GetAddressTerr_id as \"GetAddressTerr_id\"
				from {$this->scheme}.v_GetAddressTerr 
				where GetAddressTerr_id in ({$ids})
			");
			foreach($resp as $item) {
				$id = $item['GetAddressTerr_id'];
				if (isset($addresses[$id])) {
					unset($addresses[$id]);
				}
			}

			if (count($addresses) > 0) {
				$resp = $this->saveGetAddressTerr($addresses, $data['pmUser_id']);
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Получение участков организации
	 */
	function importGetTerrServiceList($data) {
		set_time_limit(0);

		$link = $this->getSyncObject('Lpu', $data['Lpu_id']);
		if (empty($link['Object_Value'])) {
			return $this->err('Ошибка получения данных по участкам с портала РПН: текущая МО не сопоставлена с МО сервиса РПН.');
		} else {
			$rpnMo_id = $link['Object_Value'];
		}

		// Получаем данные по участку
		$TerrServiceList = $this->exec('/territory/org/'.$rpnMo_id);

		if (!is_array($TerrServiceList)) {
			return $this->createError('','Ошибка получения данных по участкам с портала РПН.');
		} else if (!empty($TerrServiceList['errorMsg'])) {
			return $this->createError('',$TerrServiceList['errorMsg']);
		}

		//$TerrServiceList = array((object)array('TerritoryServiceID' => 2758));

		foreach($TerrServiceList as $TerrService) {
			if (isset($data['GetTerrServiceIds']) && !in_array($TerrService->TerritoryServiceID, $data['GetTerrServiceIds'])) {
				continue;
			}

			//Сохранение участка из РПН
			$resp = $this->saveGetTerrService($TerrService, $data['pmUser_id']);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}

			//Сохранение адреса из РПН
			$addresses = $this->exec("/territory/ter/{$TerrService->TerritoryServiceID}/addresses");
			if (!is_array($addresses)) {
				return $this->createError('','Ошибка получения адресов участка с портала РПН.');
			} else if (!empty($result['errorMsg'])) {
				return $this->createError('',$result['errorMsg']);
			}
			$resp = $this->saveGetAddressTerr($addresses, $data['pmUser_id'], $resp[0]['GetTerrService_id']);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}

			//Сохранение врачей на участке
			$doctors = $this->exec("/territory/{$TerrService->TerritoryServiceID}/doctors");
			if (!is_array($doctors)) {
				return $this->createError('','Ошибка получения данных врачей на участках с портала РПН.');
			} else if (!empty($doctors['errorMsg'])) {
				if ($doctors['errorCode'] == 401) {
					//Отказано в авторизации, берем только основного врача участка
					$doctors = !empty($TerrService->actualDoctor)?array($TerrService->actualDoctor):array();
				} else {
					return $this->createError('',$TerrServiceList['errorMsg']);
				}
			}

			foreach($doctors as $doctor) {
				$resp = $this->saveGetDoctor($doctor, $data['pmUser_id']);
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
			}
		}

		$resp = $this->importMissedGetAddressTerr($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array(array('success' => true));
	}

	/**
	 * Сохранение заявления/прикрепления из РПН
	 */
	function saveGetAttachment($Attachment, $pmUser_id) {
		$params = array(
			'GetAttachment_id' => null,
			'GetAttachment_RPNid' => $Attachment->AttachmentID,
			'GetAttachment_pid' => $Attachment->ParentID,
			'GetAttachment_Number' => $Attachment->Num,
			'GetAttachment_begDate' => $Attachment->beginDate,
			'GetAttachment_endDate' => $Attachment->endDate,
			'Org_ID' => $Attachment->orgHealthCare->id,
			'Person_id' => $Attachment->PersonID,
			'PersonAddress_ID' => $Attachment->personAddressesID,
			'GetAttachmentStatus_id' => $Attachment->attachmentStatus,
			'GetAttachmentCase_id' => $Attachment->causeOfAttach,
			'GetAttachmentProfile_id' => $Attachment->attachmentProfile,
			'GetTerrService_id' => $Attachment->territoryServiceID,
			'GetTerrService_Number' => $Attachment->territotyServiceNumber,
			'GetTerrServiceProfile_id' => $Attachment->territoryServiceProfileID,
			'GetAttachment_IsCareHome' => $Attachment->careAtHome,
			'GetDoctor_id' => $Attachment->doctorID,
			'GetAttachment_IsMigrat' => $Attachment->isMigrated,
			'GetAttachment_IsComplet' => $Attachment->isCompletedOrRefused,
			'GetAttachment_Files' => /*$Attachment->attachmentFiles*/null,
			'ServApplication_ID' => $Attachment->servApplicationID,
			'pmUser_id' => $pmUser_id
		);
		$resp = $this->saveObject($params, 'GetAttachment', 'GetAttachment_RPNid');
		if (!is_array($resp)) {
			return $this->createError('Ошибка при сохранении участка из сервиса РПН');
		}
		return $resp;
	}

	/**
	 * Получение прикреплений человека
	 */
	function importGetAttachmentList($data) {
		if (empty($data['Person_id']) && empty($data['rpnPerson_id'])) {
			return $this->createError('','Не передан идентификатор человека');
		}

		if (!empty($data['rpnPerson_id'])) {
			$rpnPerson_id = $data['rpnPerson_id'];
		} else {
			$personlink = $this->getSyncObject('Person', $data['Person_id']);
			$rpnPerson_id = $personlink['Object_Value'];
		}

		$attachments = $this->exec('/person/attachments?personId='.$rpnPerson_id);

		if (!is_array($attachments)) {
			return $this->createError('','Ошибка получения данных по прикреплений человека из сервиса РПН.');
		} else if (!empty($attachments['errorMsg'])) {
			return $this->createError('',$attachments['errorMsg']);
		}
		//print_r($attachments);exit;
		foreach($attachments as $attachment) {
			$resp = $this->saveGetAttachment($attachment, $data['pmUser_id']);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Получение участка из РПН
	 */
	function getTerrService($rpnOrg_id, $rpnTerrService_id) {
		$resp = $this->exec("/territory/org/$rpnOrg_id");
		foreach($resp as $item) {
			if ($item->TerritoryServiceID == $rpnTerrService_id) {
				return $item;
			}
		}
		return null;
	}

	/**
	 * Сохранение заявления
	 */
	function saveAttachmentRequest($data) {
		$lpulink = $this->getSyncObject('Lpu', $data['Lpu_id']);
		if (empty($lpulink)) {
			return $this->createError('','Текущая МО не сопоставлена с МО сервиса РПН');
		}
		$lpuregionlink = $this->ServiceRPN_model->getSyncObject('LpuRegion', $data['LpuRegion_id']);
		if (empty($lpuregionlink)) {
			return $this->createError('','Участок не сопоставлен с участком сервиса РПН');
		}

		$rpnPerson_id = $this->getFirstResultFromQuery("
			select BDZ_id  as \"BDZ_id\" from v_Person where Person_IsInErz = 2 and Person_id = :Person_id limit 1
		", array('Person_id' => $data['Person_id']));
		if (empty($rpnPerson_id)) {
			return $this->createError('','Человек не идентифицирован в сервисе РПН');
		}

		$addressData = $this->getFirstRowFromQuery("
			select 
				KLCountry_id as \"KLCountry_id\",
				KLRgn_id as \"KLRgn_id\",
				KLSubRgn_id as \"KLSubRgn_id\",
				KLCity_id as \"KLCity_id\",
				KLTown_id as \"KLTown_id\",
				KLStreet_id as \"KLStreet_id\",
				Address_House as \"Address_House\",
				Address_Corpus as \"Address_Corpus\",
				Address_Flat as \"Address_Flat\",
				Address_Address as \"Address_Address\"
			from v_Address  where Address_id = :Address_id
			limit 1
		", array(
			'Address_id' => $data['Address_id']
		));
		if (!$addressData) {
			return $this->createError('','Не найден адрес человека');
		}

		$addresses = $this->getPersonAddresses($rpnPerson_id);
		if (is_array($addresses) && isset($addresses[0]) && is_array($addresses[0]) && !empty($addresses[0]['Error_Msg'])) {
			return $addresses;
		}

		$rpnAddress_id = null;
		foreach($addresses as $address) {
			if (!empty($address->addressString)) {
				$addressParams = $this->generateAddressData($address->addressString, false);
				if (
					$addressData['KLCountry_id'] == (array_key_exists('KLCountry_id', $addressParams)?$addressParams['KLCountry_id']:null)
					&& $addressData['KLRgn_id'] == (array_key_exists('KLRgn_id', $addressParams)?$addressParams['KLRgn_id']:null)
					&& $addressData['KLSubRgn_id'] == (array_key_exists('KLSubRgn_id', $addressParams)?$addressParams['KLSubRgn_id']:null)
					&& $addressData['KLCity_id'] == (array_key_exists('KLCity_id', $addressParams)?$addressParams['KLCity_id']:null)
					&& $addressData['KLTown_id'] == (array_key_exists('KLTown_id', $addressParams)?$addressParams['KLTown_id']:null)
					&& $addressData['KLStreet_id'] == (array_key_exists('KLStreet_id', $addressParams)?$addressParams['KLStreet_id']:null)
					&& $addressData['Address_House'] == (array_key_exists('Address_House', $addressParams)?$addressParams['Address_House']:null)
					&& $addressData['Address_Corpus'] == (array_key_exists('Address_Corpus', $addressParams)?$addressParams['Address_Corpus']:null)
					&& $addressData['Address_Flat'] == (array_key_exists('Address_Flat', $addressParams)?$addressParams['Address_Flat']:null)
				) {
					$rpnAddress_id = $address->PAddressID;
					break;
				}
			}
		}
		if (empty($rpnAddress_id)) {
			return $this->createError('',"Адрес не найден в РПН: {$addressData['Address_Address']}");
		}

		$LpuRegionType_SysNick = $this->getFirstResultFromQuery("
			select LpuRegionType_SysNick  as \"LpuRegionType_SysNick\" from v_LpuRegionType  where LpuRegionType_id = :LpuRegionType_id limit 1
		", array('LpuRegionType_id' => $data['LpuRegionType_id']));

		$LpuRegion_Name = $this->getFirstResultFromQuery("
			select LpuRegion_Name  as \"LpuRegion_Name\" from v_LpuRegion  where LpuRegion_id = :LpuRegion_id limit 1
		", array('LpuRegion_id' => $data['LpuRegion_id']));

		//todo: врача участка можно брать из GetDoctor, когда будет автоматическое обновление данных по участку
		$TerrService = $this->getTerrService($lpulink['Object_Value'], $lpuregionlink['Object_Value']);
		if (is_array($TerrService) && !empty($TerrService['errorMsg'])) {
			return $this->createError('', $TerrService['errorMsg']);
		}

		$Doctor_id = (is_object($TerrService) && is_object($TerrService->actualDoctor))?$TerrService->actualDoctor->DoctorID:null;

		$GetAttachment_id = null;
		$attachlink = $this->getSyncObject('PersonCardAttach', $data['PersonCardAttach_id']);
		if (!empty($attachlink)) {
			$GetAttachment_id = $attachlink['Object_Value'];
		} else {
			$GetAttachment_id = $this->getFirstResultFromQuery("
				select COALESCE((select max(GetAttachment_id) from {$this->scheme}.GetAttachment ),0)+1
			");
		}

		$params = array(
			'GetAttachment_id' => $GetAttachment_id,
			'GetAttachment_RPNid' => null,
			'GetAttachment_pid' => null,
			'GetAttachment_Number' => null,
			'GetAttachment_begDate' => $data['GetAttachment_begDate'],
			'GetAttachment_endDate' => !empty($data['GetAttachment_endDate'])?$data['GetAttachment_endDate']:null,
			'GetAttachmentProfile_id' => 1,		//???
			'Org_ID' => $lpulink['Object_Value'],
			'Person_id' => $rpnPerson_id,
			'PersonAddress_ID' => is_object($address)?$address->PAddressID:null,
			'GetAttachmentStatus_id' => 1,		//запрос на прикрепление
			'GetAttachmentCase_id' => $data['GetAttachmentCase_id'],
			'GetTerrServiceProfile_id' => $this->getTerrServiceProfileId($LpuRegionType_SysNick),
			'GetTerrService_id' => $lpuregionlink['Object_Value'],
			'GetTerrService_Number' => $LpuRegion_Name,
			'GetAttachment_IsCareHome' => $data['GetAttachment_IsCareHome'],
			'GetDoctor_id' => $Doctor_id,
			'GetAttachment_IsMigrat' => false,
			'GetAttachment_IsComplet' => false,
			'GetAttachment_Files' => null,
			'ServApplication_ID' => null,
			'pmUser_id' => $data['pmUser_id']
		);
		$resp = $this->saveObject($params, 'GetAttachment', 'GetAttachment_id', true);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении заявления на прикрепление к участку в РПН');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$this->saveSyncObject('PersonCardAttach', $data['PersonCardAttach_id'], $resp[0]['GetAttachment_id']);

		return $resp;
	}

	/**
	 * Удаление заявления
	 */
	function deleteAttachmentRequest($data) {
		$link = $this->getSyncObject('PersonCardAttach', $data['PersonCardAttach_id']);
		if (empty($link)) {
			return $this->createError('','Не найден идентификатор заявления для отправки в РПН');
		}

		$query = "
			select error_code as \"Error_Code\", error_message as \"Error_Msg\"                
			from p_GetAttachment_del(
				GetAttachment_id := :GetAttachment_id
			);
		";
		$resp = $this->queryResult($query, array(
			'GetAttachment_id' => $link['Object_Value']
		));

		return $resp;
	}

	/**
	 * Передача зявления в РПН
	 */
	function sendAttachmentRequestToRpn($data) {
		$link = $this->getSyncObject('PersonCardAttach', $data['PersonCardAttach_id']);

		$attachment = $this->getFirstRowFromQuery("
			select 
				GA.Org_ID as \"Org_id\",
				GA.GetAttachmentProfile_id as \"attachmentProfile\",
				GA.Person_id as \"PersonID\",
				to_char(GA.GetAttachment_begDate, 'YYYY-MM-DD') as \"beginDate\",
				COALESCE(GA.GetAttachment_IsCareHome, 0) as \"careAtHome\",
				GA.GetAttachmentCase_id as \"causeOfAttach\",
				GA.PersonAddress_ID as \"personAddressesID\",
				GA.GetTerrService_id as \"territoryServiceID\",
				GA.GetDoctor_id as \"doctorID\",
				PS.Person_Inn as \"iin\"
			from
				{$this->scheme}.v_GetAttachment GA 
				inner join Person P  on P.BDZ_id = GA.Person_id
				inner join v_PersonState PS  on PS.Person_id = P.Person_id
			where GA.GetAttachment_id = :GetAttachment_id
			limit 1
		", array(
			'GetAttachment_id' => $link['Object_Value']
		));
		if (!is_array($attachment)) {
			return $this->createError('','Ошибка при получении заявления для отправки в РПН');
		}
		$attachment['files'] = $data['files'];

		$this->load->library('swServiceKZBot', $this->config->item('RPN'), 'RpnBot');
		try {
			$result = $this->RpnBot->attachPerson($attachment);
		} catch(Exception $e) {
			return $this->createError('',$e->getMessage());
		}
		if (!is_array($result) || !isset($result['id']) || !isset($result['num'])) {
			return $this->createError('','Ошибка при отправке заявления в РПН');
		}

		$params = array(
			'GetAttachment_id' => $link['Object_Value'],
			'GetAttachment_RPNid' => $result['id'],
			'GetAttachment_Number' => $result['num'],
			'pmUser_id' => $data['pmUser_id']
		);
		$resp = $this->saveObject($params, 'GetAttachment');
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$this->saveSyncObject('PersonCardAttach_RPN', $data['PersonCardAttach_id'], $result['id']);

		return array(array('success' => true));
	}

	/**
	 * Получение статуса заявления из РПН
	 */
	function getAttachmentRequestStatus($data) {
		$attachlink = $this->getSyncObject('PersonCardAttach', $data['PersonCardAttach_id']);
		if (empty($attachlink)) {
			return $this->createError('','Не найдена связь с заявлением для отправки в РПН');
		}
		$GetAttachment_id = !empty($attachlink['Object_Value'])?$attachlink['Object_Value']:null;

		$request = $this->getFirstRowFromQuery("
			select
				GA.Person_id as \"Person_id\",
				GA.GetTerrService_id as \"GetTerrService_id\",
				GA.GetAttachment_RPNid as \"GetAttachment_RPNid\",
				GA.GetAttachment_Number as \"GetAttachment_Number\",
				to_char(GA.GetAttachment_begDate, 'YYYY-MM-DD') as \"GetAttachment_begDate\",
				to_char(GA.GetAttachment_endDate, 'YYYY-MM-DD') as \"GetAttachment_endDate\",
				GA.GetAttachmentCase_id as \"GetAttachmentCase_id\",
				GAS.GetAttachmentStatus_id as \"GetAttachmentStatus_id\",
				GAS.GetAttachmentStatus_Code as \"GetAttachmentStatus_Code\"
			from
				{$this->scheme}.v_GetAttachment GA 
				left join {$this->scheme}.v_GetAttachmentStatus GAS  on GAS.GetAttachmentStatus_id = GA.GetAttachmentStatus_id
			where GA.GetAttachment_id = :GetAttachment_id
			limit 1
		", array('GetAttachment_id' => $GetAttachment_id));
		if (!is_array($request)) {
			return $this->createError('','Не найдено заявление для отправки в РПН');
		}

		$attachments = $this->exec("/person/attachments?personId={$request['Person_id']}");
		if (is_array($attachments) && !empty($attachments['errorMsg'])) {
			return $this->createError($attachments['errorCode'], $attachments['errorMsg']);
		}

		$GetAttachment_RPNid = $request['GetAttachment_RPNid'];
		$GetAttachment_Number = $request['GetAttachment_Number'];
		$GetAttachmentStatus_id = $request['GetAttachmentStatus_id'];
		$GetAttachmentStatus_Code = $request['GetAttachmentStatus_Code'];

		if (empty($GetAttachment_RPNid)) {
			foreach($attachments as $attachment) {
				//$begDate = date_format(date_create($attachment->beginDate),'Y-m-d');
				if ($attachment->territoryServiceID == $request['GetTerrService_id']
					&& $attachment->PersonID == $request['Person_id']
					&& $attachment->causeOfAttach == $request['GetAttachmentCase_id']
					//&& $begDate == $request['GetAttachment_begDate']
					&& $attachment->attachmentStatus == $request['GetAttachmentStatus_id']
				) {
					$link = $this->getSyncObject('PersonCardAttach_RPN', $attachment->AttachmentID, 'Object_sid');
					if (empty($link)) {
						$GetAttachment_RPNid = $attachment->AttachmentID;
						$GetAttachment_Number = $attachment->Num;
						break;
					}
				}
			}
			if (!empty($GetAttachment_RPNid)) {
				$params = array(
					'GetAttachment_id' => $GetAttachment_id,
					'GetAttachment_RPNid' => $GetAttachment_RPNid,
					'GetAttachment_Number' => $attachment->Num,
					'GetAttachment_begDate' => date_format(date_create($attachment->beginDate),'Y-m-d'),
					'GetAttachment_begDate' => !empty($attachment->endDate)?date_format(date_create($attachment->endDate),'Y-m-d'):null,
					'pmUser_id' => $data['pmUser_id']
				);
				$resp = $this->saveObject($params, 'GetAttachment');
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
				$this->saveSyncObject('PersonCardAttach_RPN', $data['PersonCardAttach_id'], $GetAttachment_RPNid);
			} else {
				return $this->createError(404,'Не найдена заявка на прикрепление в сервисе РПН');
			}
		} else {
			foreach($attachments as $attachment) {
				if ($attachment->AttachmentID == $GetAttachment_RPNid) {
					$begDate = date_format(date_create($attachment->beginDate),'Y-m-d');
					$endDate = !empty($attachment->endDate)?date_format(date_create($attachment->endDate),'Y-m-d'):null;
					//Обновить даты, если они изменились
					if ($begDate != $request['GetAttachment_begDate'] || $endDate != $request['GetAttachment_endDate']) {
						$params = array(
							'GetAttachment_id' => $GetAttachment_id,
							'GetAttachment_begDate' => $begDate,
							'GetAttachment_endDate' => $endDate,
							'pmUser_id' => $data['pmUser_id']
						);
						$resp = $this->saveObject($params, 'GetAttachment');
						if (!$this->isSuccessful($resp)) {
							return $resp;
						}
					}
					break;
				}
			}
		}

		foreach($attachments as $attachment) {
			if ($attachment->ParentID == $request['GetAttachment_RPNid']) {
				$GetAttachmentStatus_id = $attachment->attachmentStatus;
				$GetAttachmentStatus_Code = $this->getFirstResultFromQuery("
					select GetAttachmentStatus_Code as \"GetAttachmentStatus_Code\"
					from {$this->scheme}.v_GetAttachmentStatus 
					where GetAttachmentStatus_id = :GetAttachmentStatus_id
					limit 1
				", array('GetAttachmentStatus_id' => $GetAttachmentStatus_id));
				break;
			}
		}

		return array(array(
			'success' => true,
			'GetAttachmentStatus_id' => $GetAttachmentStatus_id,
			'GetAttachmentStatus_Code' => $GetAttachmentStatus_Code
		));
	}

	/**
	 * @param $status
	 * @param $case
	 * @return string
	 */
	function getCardCloseCause($status, $case) {
		$result = '8';
		$statusMap = array('8' => '1', '7' => '5', '6' => '2', '14' => '4', '15' => '4');
		$caseMap = array('1' => '1', '2' => '1', '3' => '2', '5' => '4', '6' => '5', '7' => '4');
		if (isset($statusMap[$status])) {
			$result = $statusMap[$status];
		} else if (isset($caseMap[$case])) {
			$result = $caseMap[$case];
		}
		return $result;
	}

	/**
	 * @return array
	 */
	function getTerrServiceProfileMap() {
		return array(
			'1' => 'pmsp',
			'2' => 'ter',
			'3' => 'ped',
			'5' => 'vop',
		);
	}

	/**
	 * @param int $TerrServiceProfile_id
	 * @return null|string
	 */
	function getLpuRegionTypeSysNick($TerrServiceProfile_id) {
		$map = $this->getTerrServiceProfileMap();
		return isset($map[$TerrServiceProfile_id])?$map[$TerrServiceProfile_id]:null;
	}

	/**
	 * @param int $TerrServiceProfile_id
	 * @return null|int
	 */
	function getLpuRegionTypeId($TerrServiceProfile_id) {
		$sysNick = $this->getLpuRegionTypeSysNick($TerrServiceProfile_id);
		if (!$sysNick) return null;
		return $this->getFirstResultFromQuery("
			select LpuRegionType_id
			from v_LpuRegionType 
			where LpuRegionType_SysNick = :LpuRegionType_SysNick
			limit 1
		", array('LpuRegionType_SysNick' => $sysNick), true);
	}

	/**
	 * @param string $LpuRegionType_SysNick
	 * @return null|int
	 */
	function getTerrServiceProfileId($LpuRegionType_SysNick) {
		$map = array_flip($this->getTerrServiceProfileMap());
		return isset($map[$LpuRegionType_SysNick])?$map[$LpuRegionType_SysNick]:null;
	}

	/**
	 * Синхронизация участков. Данные для синхронизации берутся из r101.GetTerrService
	 */
	function syncLpuRegions($data) {
		$filters = "";
		$params = array('Service_id' => $this->ObjectSynchronLog_model->serviceId);

		if (!empty($data['Lpu_id'])) {
			$filters .= " and LL.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if (!empty($data['GetTerrServiceIds']) && is_array($data['GetTerrServiceIds'])) {
			$ids = implode(',', $data['GetTerrServiceIds']);
			$filters .= " and GTS.GetTerrService_id in ({$ids})";
		}

		$query = "
			with LpuList as (
				select distinct L.Lpu_id, OSL.Object_sid as Org_ID
				from v_Lpu L 
				inner join v_ObjectSynchronLog OSL  on OSL.Object_id = L.Lpu_id
					and OSL.ObjectSynchronLogService_id = :Service_id and OSL.Object_Name = 'Lpu'
			)
			select
				LL.Lpu_id as \"Lpu_id\",
				GTS.GetTerrService_id as \"GetTerrService_id\",
				GTS.GetTerrService_Number as \"GetTerrService_Number\",
				GTS.GetTerrServiceProfile_ID as \"GetTerrServiceProfile_ID\",
				to_char(GTS.GetTerrService_begDate, 'YYYYMMDD') as \"GetTerrService_begDate\",
				to_char(GTS.GetTerrService_endDate, 'YYYYMMDD') as \"GetTerrService_endDate\"
			from {$this->scheme}.v_GetTerrService GTS 
			left join LpuList LL on LL.Org_ID = GTS.Org_ID
			where (1=1) {$filters}
			order by
				LL.Lpu_id,
				GTS.GetTerrService_Number,
				case when GTS.GetTerrService_endDate is null then 0 else 1 end,--Синхронизировать сперва открытые участки
				GTS.GetTerrService_begDate
		";
		$TerrServiceList = $this->queryResult($query, $params);
		if (!is_array($TerrServiceList)) {
			return $this->createError('', 'Ошибка при запросе GetTerrService');
		}

		foreach($TerrServiceList as $TerrService) {
			$LpuRegion = null;
			$link = $this->getSyncObject('LpuRegion', $TerrService['GetTerrService_id'], 'Object_sid');

			if ($link) {
				$LpuRegion = $this->getFirstRowFromQuery("
					select 
						LR.LpuRegion_id as \"LpuRegion_id\",
						LR.LpuRegion_Name as \"LpuRegion_Name\",
						LR.LpuRegion_Descr as \"LpuRegion_Descr\",
						to_char(LR.LpuRegion_begDate, 'YYYYMMDD') as \"LpuRegion_begDate\",
						to_char(LR.LpuRegion_endDate, 'YYYYMMDD') as \"LpuRegion_endDate\",
						LR.LpuRegionType_id as \"LpuRegionType_id\",
						LR.Lpu_id as \"Lpu_id\",
						LR.LpuRegion_Guid as \"LpuRegion_Guid\",
						LR.LpuSection_id as \"LpuSection_id\"
					from v_LpuRegion LR 
					where LR.LpuRegion_id = :LpuRegion_id
					limit 1
				", array('LpuRegion_id' => $link['Object_id']));
				if (!$LpuRegion) {
					return $this->createError('','Ошибка при получении данных участка');
				}
			} else {
				$query = "
					select
						LR.LpuRegion_id as \"LpuRegion_id\",
						LR.LpuRegion_Name as \"LpuRegion_Name\",
						LR.LpuRegion_Descr as \"LpuRegion_Descr\",
						to_char(LR.LpuRegion_begDate, 'YYYYMMDD') as \"LpuRegion_begDate\",
						to_char(LR.LpuRegion_endDate, 'YYYYMMDD') as \"LpuRegion_endDate\",
						LR.LpuRegionType_id as \"LpuRegionType_id\",
						LR.Lpu_id as \"Lpu_id\",
						LR.LpuRegion_Guid as \"LpuRegion_Guid\",
						LR.LpuSection_id as \"LpuSection_id\",
						OSL.ObjectSynchronLog_id as \"ObjectSynchronLog_id\"
					from
						LpuRegion LR 
						inner join v_LpuRegionType LRT  on LRT.LpuRegionType_id = LR.LpuRegionType_id and Region_id = :Region_id
						LEFT JOIN LATERAL(
							select ObjectSynchronLog_id
							from v_ObjectSynchronLog 
							where ObjectSynchronLogService_id = :Service_id
							and Object_Name = 'LpuRegion' and Object_id = LR.LpuRegion_id
							order by Object_setDT desc
                            limit 1
						) OSL ON true
					where
						LR.Lpu_id = :Lpu_id
						and LR.LpuRegion_Name = :LpuRegion_Name
						and LRT.LpuRegionType_SysNick = :LpuRegionType_SysNick
						and LR.LpuRegion_begDate = :LpuRegion_begDate
						and COALESCE(LR.LpuRegion_endDate, dateadd('year', 50, dbo.tzGetDate())) = COALESCE(cast(:LpuRegion_endDate as timestamp), dateadd('year', 50, dbo.tzGetDate()))
					order by
						case when LR.LpuRegion_endDate is null then 0 else 1 end,
						LR.LpuRegion_begDate
				";
				$params = array(
					'Lpu_id' => $TerrService['Lpu_id'],
					'LpuRegion_Name' => $TerrService['GetTerrService_Number'],
					'LpuRegionType_SysNick' => $this->getLpuRegionTypeSysNick($TerrService['GetTerrServiceProfile_ID']),
					'LpuRegion_begDate' => $TerrService['GetTerrService_begDate'],
					'LpuRegion_endDate' => $TerrService['GetTerrService_endDate'],
					'Service_id' => $this->ObjectSynchronLog_model->serviceId,
					'Region_id' => getRegionNumber()
				);
				$resp = $this->queryResult($query, $params);
				if (!is_array($resp)) {
					return $this->createError('','Ошибка при поиске участка');
				}

				if (count($resp) > 1) {
					return $this->createError('',"Не удалось однозначно определить участок № {$TerrService['GetTerrService_Number']} профиль {$TerrService['GetTerrServiceProfile_ID']}");
				}
				if (count($resp) > 0) {
					if (empty($resp[0]['ObjectSynchronLog_id'])) {
						$LpuRegion = $resp[0];
					} else {
						continue;
					}
				}
			}
			if (!$LpuRegion) {
				$LpuRegion = array(
					'LpuRegion_id' => null,
					'LpuRegion_Name' => $TerrService['GetTerrService_Number'],
					'LpuRegion_Descr' => null,
					'LpuRegion_begDate' => $TerrService['GetTerrService_begDate'],
					'LpuRegion_endDate' => $TerrService['GetTerrService_endDate'],
					'LpuRegionType_id' => $this->getLpuRegionTypeId($TerrService['GetTerrServiceProfile_ID']),
					'Lpu_id' => $TerrService['Lpu_id'],
					'LpuRegion_Guid' => null,
					'LpuSection_id' => null,
					'noTransferService' => true,
					'allowEmptyMedPersonalData' => true,
					'ignorePersonCardCheck' => true,
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id'],
				);
			} else {
				$LpuRegion = array_merge($LpuRegion, array(
					'LpuRegion_begDate' => $TerrService['GetTerrService_begDate'],
					'LpuRegion_endDate' => $TerrService['GetTerrService_endDate'],
					'noTransferService' => true,
					'allowEmptyMedPersonalData' => true,
					'ignorePersonCardCheck' => true,
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
			}

			$resp = $this->LpuStructure_model->saveLpuRegion($LpuRegion);
			if (!is_array($resp)) {
				return $this->createError('','Ошибка при сохранении данных участка');
			}
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
			$this->saveSyncObject('LpuRegion', $resp[0]['LpuRegion_id'], $TerrService['GetTerrService_id']);

			if (!empty($resp[0]['LpuRegion_id'])) {
				$resp = $this->syncLpuRegionStreet(array(
					'LpuRegion_id' => $resp[0]['LpuRegion_id'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$this->isSuccessful($resp)) {
					return $resp;
				}
			}
		}
		return array(array('success' => true));
	}

	/**
	 * Синхронизация территорий участков из РПН
	 */
	function syncLpuRegionStreet($data) {
		$link = $this->getSyncObject('LpuRegion', $data['LpuRegion_id']);
		if (!$link) {
			return $this->createError('','Не найден идентификатор участка из РПН');
		}
		$GetTerrService_id = $link['Object_Value'];

		$query = "
			with recursive LeafAddressList as(
				select distinct GB.GetAddressTerr_id
				from {$this->scheme}.v_GetBuildingGetTerrService BTS 
				inner join {$this->scheme}.v_GetBuilding GB  on GB.GetBuilding_id = BTS.GetBuilding_id
				where BTS.GetTerrService_id = :GetTerrService_id
			),
			Rec as(
				SELECT
					t.GetAddressTerr_id,
					t.GetAddressTerr_pid,
					t.GetAddressTerr_Name,
					t.GetAddressTerr_NameKZ,
					t.GetAddressTerrType_id,
					t.GetAddressTerr_id as Leaf,
					1 as Level
				FROM {$this->scheme}.v_GetAddressTerr t
				WHERE t.GetAddressTerr_id in (select GetAddressTerr_id from LeafAddressList)
				UNION ALL
				SELECT
					e.GetAddressTerr_id,
					e.GetAddressTerr_pid,
					e.GetAddressTerr_Name,
					e.GetAddressTerr_NameKZ,
					e.GetAddressTerrType_id,
					d.Leaf,
					Level+1 as Level
				FROM {$this->scheme}.v_GetAddressTerr e
				INNER JOIN Rec AS d ON d.GetAddressTerr_pid = e.GetAddressTerr_id
			)
			select
				Rec.GetAddressTerr_id as \"GetAddressTerr_id\",
				Rec.GetAddressTerr_pid as \"GetAddressTerr_pid\",
				Rec.GetAddressTerr_Name as \"GetAddressTerr_Name\",
				Rec.GetAddressTerr_NameKZ as \"GetAddressTerr_NameKZ\",
				Rec.Leaf as \"Leaf\",
				Rec.Level as \"Level\",
				Socr.KLSocr_id as \"KLSocr_id\",
				Socr.KLSocr_Name as \"KLSocr_Name\",
				Level.KLAreaLevel_SysNick as \"KLAreaLevel_SysNick\"
			from Rec
				left join {$this->scheme}.v_KZKLSocrLink SocrLink  on SocrLink.KZKLSocr_id = Rec.GetAddressTerrType_id
				left join v_KLSocr Socr  on Socr.KLSocr_id = SocrLink.KLSocr_id
				left join v_KLAreaLevel Level  on Level.KLAreaLevel_id = Socr.KLAreaLevel_id
			order by \"Leaf\", \"Level\" desc
		";
		$resp = $this->queryResult($query, array('GetTerrService_id' => $GetTerrService_id));

		$addresses = array();
		foreach($resp as $item) {
			$addresses[$item['Leaf']][] = $item;
		}

		$parsedAddresses = array();
		foreach($addresses as $leaf => $address) {
			$allowSave = true;
			$addressParams = array(
				'KLCountry_id' => null,
				'KLRGN_id' => null,
				'KLSubRGN_id' => null,
				'KLCity_id' => null,
				'KLTown_id' => null,
				'KLStreet_id' => null,
				'HouseSet' => array()
			);

			$KLArea_pid = null;
			foreach($address as $element) {
				if ($element['KLSocr_Name'] == 'РЕСПУБЛИКА') {
					$query = "
						select KLCountry_id as \"KLCountry_id\"
						from v_KLCountry 
						where KLCountry_Name iLIKE :KLCountry_Name
					";
					$params = array('KLCountry_Name' => $element['GetAddressTerr_Name']);
					$addressParams['KLCountry_id'] = $this->getFirstResultFromQuery($query, $params);
				} else if ($element['KLAreaLevel_SysNick'] == 'street') {
					$query = "
						select Street.KLStreet_id as \"KLStreet_id\"
						from v_KLStreet Street 
						where
							Street.KLArea_id = :KLArea_id
							and Street.KLSocr_id = :KLSocr_id
						 	and (
								Street.KLStreet_Name iLIKE :KLStreet_Name
								or Street.KLStreet_LocalName iLIKE :KLStreet_LocalName
							)
					";
					$params = array(
						'KLStreet_Name' => $element['GetAddressTerr_Name'],
						'KLStreet_LocalName' => $element['GetAddressTerr_NameKZ'],
						'KLSocr_id' => $element['KLSocr_id'],
						'KLArea_id' => $KLArea_pid,
					);
					$addressParams['KLStreet_id'] = $this->getFirstResultFromQuery($query, $params);
					//Если из РПН получена улица, но в промеде она не найдена, то сохранять адрес нельзя
					if (empty($addressParams['KLStreet_id'])) {
						$allowSave = false;
					}
				} else {
					$query = "
						select Area.KLArea_id as \"KLArea_id\"
						from v_KLArea Area 
						where
							Area.KLCountry_id = :KLCountry_id
							and Area.KLArea_Name iLIKE :KLArea_Name
							and Area.KLSocr_id = :KLSocr_id
							and (:KLArea_pid is null or Area.KLArea_pid = :KLArea_pid)
						limit 1
					";
					$params = array(
						'KLCountry_id' => $addressParams['KLCountry_id'],
						'KLArea_Name' => $element['GetAddressTerr_Name'],
						'KLSocr_id' => $element['KLSocr_id'],
						'KLArea_pid' => $KLArea_pid,
					);
					$KLArea_id = $this->getFirstResultFromQuery($query, $params);

					switch($element['KLAreaLevel_SysNick']) {
						case 'rgn':
							$addressParams['KLRGN_id'] = $KLArea_id;
							break;
						case 'subrgn':
							$addressParams['KLSubRGN_id'] = $KLArea_id;
							break;
						case 'city':
							$addressParams['KLCity_id'] = $KLArea_id;
							break;
						case 'town':
							$addressParams['KLTown_id'] = $KLArea_id;
							break;
					}
					$KLArea_pid = $KLArea_id;
				}
			}
			if ($allowSave) {
				$HouseSet = array();
				$buildings = $this->queryResult("
					select GetBuilding_Number as \"GetBuilding_Number\"
					from {$this->scheme}.v_GetBuilding 
					where GetAddressTerr_id = :GetAddressTerr_id
				", array('GetAddressTerr_id' => $leaf));
				foreach($buildings as $building) {
					$HouseSet[] = trim($building['GetBuilding_Number']);
				}
				$addressParams['HouseSet'] = $HouseSet;
				$addressParams['addressId'] = $element['Leaf'];
				$parsedAddresses[] = $addressParams;
			}
		}

		$resp = $this->importLpuRegionAddressesData(array(
			'parsedAddresses' => $parsedAddresses,
			'LpuRegion_id' => $data['LpuRegion_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		return $resp;
	}

	/**
	 * Получени данных прикрепления из Промед
	 */
	function getPersonCardData($data) {
		$params = array('PersonCard_id' => $data['PersonCard_id']);
		$query = "
			SELECT
				PC.PersonCard_id as \"PersonCard_id\",
				rtrim(rtrim(PC.PersonCard_Code)) as \"PersonCard_Code\",
				PC.Person_id as \"Person_id\",
				PC.LpuAttachType_id as \"LpuAttachType_id\",
				PC.PersonCard_IsAttachCondit as \"PersonCard_IsAttachCondit\",
				LR.LpuRegionType_id as \"LpuRegionType_id\",
				to_char(cast(PC.PersonCard_begDate as timestamp), 'DD.MM.YYYY') as \"PersonCard_begDate\",
				to_char(cast(PC.PersonCard_endDate as timestamp), 'DD.MM.YYYY') as \"PersonCard_endDate\",
				PC.CardCloseCause_id as \"CardCloseCause_id\",
				PC.Lpu_id as \"Lpu_id\",
				LR.LpuRegion_id as \"LpuRegion_id\",
				PC.LpuRegion_fapid as \"LpuRegion_Fapid\",
				PC.PersonCard_DmsPolisNum as \"PersonCard_DmsPolisNum\",
				to_char(cast(PC.PersonCard_DmsBegDate as timestamp), 'DD.MM.YYYY') as \"PersonCard_DmsBegDate\",
				to_char(cast(PC.PersonCard_DmsEndDate as timestamp), 'DD.MM.YYYY') as \"PersonCard_DmsEndDate\",
				PC.OrgSMO_id as \"OrgSMO_id\",
				pc.PersonCardAttach_id as \"PersonCardAttach_id\",
				PACLink.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				PCA.PersonCardAttach_IsSMS-1 as \"PersonCardAttach_IsSMS\",
				CASE WHEN PCA.PersonCardAttach_SMS is null then null else ('+7 '||SUBSTRING(PCA.PersonCardAttach_SMS,1,3)||' '||SUBSTRING(PCA.PersonCardAttach_SMS,4,10)) end as \"PersonCardAttach_SMS\",
				PCA.PersonCardAttach_IsEmail-1 as \"PersonCardAttach_IsEmail\",
				PCA.PersonCardAttach_Email as \"PersonCardAttach_Email\"
			FROM
				PersonCard PC 
				left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_PersonCardAttach PCA  on PCA.PersonCardAttach_id = pc.PersonCardAttach_id
				LEFT JOIN LATERAL(
					select pac.PersonAmbulatCard_id from v_PersonAmbulatCard PAC 
					left join v_PersonAmbulatCardLink PACLink  on PACLink.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
					where PACLink.PersonCard_id = PC.PersonCard_id
                    limit 1
				) PACLink ON true
			WHERE
				PC.PersonCard_id = :PersonCard_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Синхронизация прикреплений человека
	 */
	function syncPersonCards($data) {
		$Person_id = $data['Person_id'];

		$personlink = $this->getSyncObject('Person', $Person_id);
		$rpnPerson_id = $personlink['Object_Value'];

		$updatePersonCard = function($PersonCard) {
			$this->db->query("
				update PersonCard
				set PersonCard_begDate = :PersonCard_begDate,
					PersonCard_endDate = :PersonCard_endDate,
					CardCloseCause_id = :CardCloseCause_id
				where PersonCard_id = :PersonCard_id
			", $PersonCard);
			return array(array(
				'PersonCard_id' => $PersonCard['PersonCard_id'],
				'Error_Code' => null,
				'Error_Msg' => null,
			));
		};

		//Выбор данных для создания/обновления данных в Промеде
		$query = "
			with LpuList as (
				select distinct L.Lpu_id, OSL.Object_sid as Org_ID
				from v_Lpu L 
				inner join v_ObjectSynchronLog OSL  on OSL.Object_id = L.Lpu_id
					and OSL.ObjectSynchronLogService_id = :Service_id and OSL.Object_Name = 'Lpu'
			)
			select
				GA.GetAttachment_RPNid as \"GetAttachment_RPNid\",
				GA.GetAttachment_Number as \"GetAttachment_Number\",
				GA.Org_ID as \"Org_ID\",
				GA.GetTerrService_id as \"GetTerrService_id\",
				GA.GetTerrService_Number as \"GetTerrService_Number\",
				GA.GetTerrServiceProfile_id as \"GetTerrServiceProfile_id\",
				to_char(GA.GetAttachment_begDate, 'YYYY-MM-DD') as \"GetAttachment_begDate\",
				to_char(GA.GetAttachment_endDate, 'YYYY-MM-DD') as \"GetAttachment_endDate\",
				LL.Lpu_id as \"Lpu_id\",
				LRT.LpuRegionType_id as \"LpuRegionType_id\",
				CCC.CardCloseCause_id as \"CardCloseCause_id\"
			from
				r101.v_GetAttachment GA 
				inner join LpuList LL on LL.Org_ID = GA.Org_ID
				inner join r101.v_GetAttachmentStatus GAS  on GAS.GetAttachmentStatus_id = GA.GetAttachmentStatus_id
				LEFT JOIN LATERAL (
					select case
						when GA.GetTerrServiceProfile_id = 1 then 'pmsp'
						when GA.GetTerrServiceProfile_id = 2 then 'ter'
						when GA.GetTerrServiceProfile_id = 3 then 'ped'
						when GA.GetTerrServiceProfile_id = 5 then 'vop'
					end as LpuRegionType_SysNick
                    limit 1
				) LRTL ON true
				left join v_LpuRegionType LRT  on LRT.LpuRegionType_SysNick = LRTL.LpuRegionType_SysNick
				LEFT JOIN LATERAL (
					select GAS1.GetAttachmentStatus_Code
					from r101.v_GetAttachment GA1 
					inner join r101.v_GetAttachmentStatus GAS1  on GAS1.GetAttachmentStatus_id = GA1.GetAttachmentStatus_id
					where GA1.Person_id = GA.Person_id
					and (
						GA1.GetAttachment_pid = GA.GetAttachment_RPNid 
						or (GA1.GetAttachment_pid is null and GA1.GetAttachment_begDate <= GA.GetAttachment_endDate)
					) 
					and GAS1.GetAttachmentStatus_Code in (700,800,900,1400,1500)
					order by GA1.GetAttachment_begDate desc
                    limit 1
				) GAClose ON true
				LEFT JOIN LATERAL (
					select case
						when GAClose.GetAttachmentStatus_Code = 700 then 1
						when GAClose.GetAttachmentStatus_Code = 800 then 5
						when GAClose.GetAttachmentStatus_Code = 900 then 2
						when GAClose.GetAttachmentStatus_Code = 1400 then 4
						when GAClose.GetAttachmentStatus_Code = 1500 then 4
						when GAS.GetAttachmentStatus_Code = 700 then 1
						when GAS.GetAttachmentStatus_Code = 800 then 5
						when GAS.GetAttachmentStatus_Code = 900 then 2
					end as Code
                    limit 1
				) CardCloseCause ON true
				left join v_CardCloseCause CCC  on CCC.CardCloseCause_Code = CardCloseCause.Code
			where
				GA.Person_id = :rpnPerson_id
				and GAS.GetAttachmentStatus_Code in (300,700,800/*,900*/)
			order by
				GA.GetAttachment_begDate,
				case when GA.GetAttachment_endDate is null then 1 else 0 end
		";
		$params = array(
			'rpnPerson_id' => $rpnPerson_id,
			'Service_id' => $this->ObjectSynchronLog_model->serviceId
		);
		$attachments = $this->queryResult($query, $params);
		if (!is_array($attachments)) {
			return $this->createError('','Ошибка при получении данных для синхронизации прикреплений');
		}

		//Проверка наличия участков, для прикреплений. Если учасктов не хватает, то создать
		$GetTerrService = array();
		foreach($attachments as $item) {
			$lpuregionlink = $this->getSyncObject('LpuRegion',$item['GetTerrService_id'], 'Object_sid');

			if (empty($lpuregionlink)) {
				$Lpu_id = $item['Lpu_id'];
				$GetTerrService[$Lpu_id][] = $item['GetTerrService_id'];
			}
		}
		foreach($GetTerrService as $Lpu_id => $ids) {
			$resp = $this->importGetTerrServiceList(array(
				'Lpu_id' => $Lpu_id,
				'GetTerrServiceIds' => $ids,
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}

			$resp = $this->syncLpuRegions(array(
				'Lpu_id' => $Lpu_id,
				'GetTerrServiceIds' => $ids,
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id']
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		$this->load->model('PersonCard_model');
		$this->load->model('PersonAmbulatCard_model');
		foreach($attachments as $item) {
			$lpuregionlink = $this->getSyncObject('LpuRegion',$item['GetTerrService_id'], 'Object_sid');

			$Lpu_id = $item['Lpu_id'];
			$LpuRegion_id = $lpuregionlink['Object_id'];

			$query = "
				select PC.PersonCard_id as \"PersonCard_id\"
				from v_PersonCard_all PC 
				inner join v_ObjectSynchronLog OSL  on OSL.Object_id = PC.PersonCard_id
					and OSL.Object_Name = 'PersonCard' and OSL.ObjectSynchronLogService_id = :Service_id
				where
					PC.Person_id = :Person_id
					and OSL.Object_sid = :Object_sid
				limit 1
			";
			$params = array(
				'Person_id' => $Person_id,
				'Object_sid' => $item['GetAttachment_RPNid'],
				'Service_id' => $this->ObjectSynchronLog_model->serviceId
			);
			$PersonCard_id = $this->getFirstResultFromQuery($query, $params, true);
			if ($PersonCard_id === false) {
				return $this->createError('','Ошибка при проверке существования прикрепления');
			}

			$query = "
				select 
					PC.PersonCard_id as \"PersonCard_id\",
					PC.PersonCard_begDate as \"PersonCard_begDate\",
					PC.PersonCard_endDate as \"PersonCard_endDate\"
				from v_PersonCard_all PC 
				left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegionType LRT  on LRT.LpuRegionType_id = LR.LpuRegionType_id
				where
					PC.Person_id = :Person_id
					and LRT.LpuRegionType_id = :LpuRegionType_id
					and LR.LpuRegion_Name = :GetTerrService_Number
				order by
					PC.PersonCard_begDate
			";
			$params = $item;
			$params['Person_id'] = $Person_id;
			$PersonCards = $this->queryResult($query, $params);

			if (!$PersonCard_id) {
				$query = "
					select PC.PersonCard_id as \"PersonCard_id\"
					from v_PersonCard_all PC 
					left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id
					left join v_LpuRegionType LRT  on LRT.LpuRegionType_id = LR.LpuRegionType_id
					where
						PC.Person_id = :Person_id
						and LRT.LpuRegionType_id = :LpuRegionType_id
						and LR.LpuRegion_Name = :GetTerrService_Number
						and (
							PC.PersonCard_begDate = :GetAttachment_begDate or (
								:GetAttachment_begDate = :GetAttachment_endDate
								and PC.PersonCard_endDate = :GetAttachment_endDate
							)
						)
						and not exists(
							select * from v_ObjectSynchronLog 
							where ObjectSynchronLogService_id = :Service_id and Object_Name = 'PersonCard' and Object_id = PC.PersonCard_id
						)
				";
				$params = $item;
				$params['Person_id'] = $Person_id;
				$params['Service_id'] = $this->ObjectSynchronLog_model->serviceId;
				$PersonCard_id = $this->getFirstResultFromQuery($query, $params, true);
				if ($PersonCard_id === false) {
					return $this->createError('','Ошибка при проверке существования прикрепления');
				}
			}

			if (!empty($PersonCard_id)) {
				/*$PersonCardData = $this->PersonCard_model->getPersonCard(array(
					'PersonCard_id' => $PersonCard_id, 'Lpu_id' => $Lpu_id
				));*/
				$PersonCardData = $this->getPersonCardData(array('PersonCard_id' => $PersonCard_id));
				if (!is_array($PersonCardData) || count($PersonCardData) == 0) {
					return $this->createError('','Ошибка при получении данных прикрепления');
				}
				$PersonCard = $PersonCardData[0];

				$PersonCard['PersonCard_begDate'] = !empty($PersonCard['PersonCard_begDate'])?ConvertDateFormat($PersonCard['PersonCard_begDate']):null;
				$PersonCard['PersonCard_endDate'] = !empty($PersonCard['PersonCard_endDate'])?ConvertDateFormat($PersonCard['PersonCard_endDate']):null;

				if (($PersonCard['PersonCard_endDate'] != $item['GetAttachment_endDate']) || (empty($PersonCard['PersonCard_endDate'])) && empty($item['GetAttachment_endDate'])) {
					$PersonCard['PersonCard_endDate'] = $item['GetAttachment_endDate'];
					$PersonCard['CardCloseCause_id'] = $item['CardCloseCause_id'];
					$PersonCard['action'] = 'edit';
					$PersonCard['noTransferService'] = true;
					$PersonCard['ignorePersonDead'] = true;
					$PersonCard['Server_id'] = $data['Server_id'];
					$PersonCard['pmUser_id'] = $data['pmUser_id'];
					$saveResponse = $this->PersonCard_model->savePersonCard($PersonCard);
					if (!$this->isSuccessful($saveResponse)) {
						if (is_array($saveResponse) && isset($saveResponse[0]) && !empty($saveResponse[0]['Error_Msg'])) {
							if ($saveResponse[0]['Error_Code'] == 333) {
								if (count($PersonCards) == 1) {
									$PersonCard['PersonCard_id'] = $PersonCards[0]['PersonCard_id'];
									$saveResponse = $updatePersonCard($PersonCard);
								}
							} else {
								return $saveResponse;
							}
						} else {
							return $this->createError('','Ошибка при сохранении прикрепления');
						}
					}
					if (!empty($saveResponse[0]['PersonCard_id'])) {
						$PersonCard_id = $saveResponse[0]['PersonCard_id'];
					}
					$this->saveSyncObject('PersonCard', $PersonCard_id, $item['GetAttachment_RPNid']);
				}
			} else {
				$PersonAmbulatCard_Num = $this->getFirstResultFromQuery("
					select max(cast(PersonAmbulatCard_Num as bigint))+1 as \"PersonAmbulatCard_Num\"
					from v_PersonAmbulatCard 
					where ISNUMERIC(PersonAmbulatCard_Num) = 1 and Lpu_id = :Lpu_id
					limit 1
				", array('Lpu_id' => $Lpu_id), true);
				if ($PersonAmbulatCard_Num === false) {
					return $this->createError('','Ошибка при получении номера амбулаторной карты');
				}
				if (empty($PersonAmbulatCard_Num)) {
					$PersonAmbulatCard_Num = 1;
				}

				$PersonAmbulatCard = $this->PersonAmbulatCard_model->checkPersonAmbulatCard(array(
					'Person_id' => $Person_id,
					'Lpu_id' => $Lpu_id,
					'pmUser_id' => $data['pmUser_id'],
					'Server_id'=> $data['Server_id'],
					'PersonAmbulatCard_Num' => $PersonAmbulatCard_Num,
					'getCount' => false
				));
				if (!$this->isSuccessful($PersonAmbulatCard)) {
					return $PersonAmbulatCard;
				}

				$PersonCard = array(
					'action' => 'add',
					'PersonCard_id' => null,
					'PersonCard_Code' => $PersonAmbulatCard[0]['PersonCard_Code'],
					'PersonAmbulatCard_id' => $PersonAmbulatCard[0]['PersonAmbulatCard_id'],
					'PersonAmbulatCard_Num' => $PersonAmbulatCard_Num,
					'Person_id' => $Person_id,
					'Lpu_id' => $Lpu_id,
					'PersonCard_begDate' => $item['GetAttachment_begDate'],
					'PersonCard_endDate' => $item['GetAttachment_endDate'],
					'PersonCardAttach_id' => null,
					'LpuAttachType_id' => 1,
					'CardCloseCause_id' => $item['CardCloseCause_id'],
					'LpuRegion_id' => $LpuRegion_id,
					'LpuRegionType_id' => $item['LpuRegionType_id'],
					'LpuRegion_Fapid' => null,
					'noTransferService' => true,
					'ignorePersonDead' => true,
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id'],
				);
				$saveResponse = $this->PersonCard_model->savePersonCard($PersonCard);
				if (!$this->isSuccessful($saveResponse)) {
					if (is_array($saveResponse) && isset($saveResponse[0]) && !empty($saveResponse[0]['Error_Msg'])) {
						if ($saveResponse[0]['Error_Code'] == 333) {
							if (count($PersonCards) == 1) {
								$PersonCard['PersonCard_id'] = $PersonCards[0]['PersonCard_id'];
								$saveResponse = $updatePersonCard($PersonCard);
							}
						} else {
							return $saveResponse;
						}
					} else {
						return $this->createError('','Ошибка при сохранении прикрепления');
					}
				}
				if (!empty($saveResponse[0]['PersonCard_id'])) {
					$PersonCard_id = $saveResponse[0]['PersonCard_id'];
					$this->saveSyncObject('PersonCard', $PersonCard_id, $item['GetAttachment_RPNid']);
				}
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Получение списка факторов беременности для передачи в сервис РПН
	 */
	function getPregnancyFactorsList($data) {
		$diagListQuery = "
			select ES.Diag_id as \"Diag_id\"
			from v_EvnSection ES 
			where ES.EvnSection_pid = :EvnPS_id
			union
			select distinct EDPS.Diag_id as \"Diag_id\"
			from v_EvnDiagPS EDPS 
			where EDPS.EvnDiagPS_rid = :EvnPS_id
		";
		$motherDiagList = $this->queryList($diagListQuery, array('EvnPS_id' => $data['mEvnPS_id']));
		if (!is_array($motherDiagList)) {
			return $this->createError('','Ошибка при получении диагнозов матери');
		}

		$childDiagList = $this->queryList($diagListQuery, array('EvnPS_id' => $data['cEvnPS_id']));
		if (!is_array($childDiagList)) {
			return $this->createError('','Ошибка при получении диагнозов новорожденного');
		}

		$motherDiagList_str = count($motherDiagList)>0?implode(',', $motherDiagList):'-1';
		$childDiagList_str = count($childDiagList)>0?implode(',', $childDiagList):'-1';

		//Факторы по матери
		$query = "
			with recursive list1 as (
				select PQ.QuestionType_id
				from v_PregnancyQuestion PQ 
				where PQ.PersonPregnancy_id = :PersonPregnancy_id
				and COALESCE(PQ.PregnancyQuestion_IsTrue,1) = 2
			),
			QuestionTypeList as (
				select t.QuestionType_id, t.QuestionType_pid
				from v_QuestionType t 
				where t.QuestionType_id in (select QuestionType_id from list1)
				union all
				select e.QuestionType_id, e.QuestionType_pid
				from v_QuestionType e 
				inner join QuestionTypeList as d on d.QuestionType_pid = e.QuestionType_id
			),
			DiagList as (
				select t.Diag_id, t.Diag_pid
				from v_Diag t 
				where t.Diag_id in ({$motherDiagList_str})
				union all
				select e.Diag_id, e.Diag_pid
				from v_Diag e 
				inner join DiagList as d on d.Diag_pid = e.Diag_id
			)
			select
				PFT.PregnancyFactorType_id as \"PregnancyFactorType_id\",
				PFT.PregnancyFactorType_fid as \"PregnancyFactorType_fid\",
				PFT.PregnancyFactorType_fpid as \"PregnancyFactorType_fpid\",
				PFT.PregnancyFactorType_NameRu as \"PregnancyFactorType_NameRu\",
				Diags.Diag_ids as \"Diag_ids\"
			from r101.v_PregnancyFactorType PFT 
			LEFT JOIN LATERAL (
					select
						string_agg(cast(PFUC.Diag_id as varchar), ',') as Diag_ids
					from r101.v_PregnancyFactorUslugaComplex PFUC
					where PFUC.PregnancyFactorType_id = PFT.PregnancyFactorType_id and PFUC.Diag_id is not null
			) as Diags ON true
			where
				PFT.PregnancyFactorType_fpid in (1,2,3,8)
				and exists(
					select *
					from r101.v_PregnancyFactorUslugaComplex PFUC 
					where
						PFUC.PregnancyFactorType_id = PFT.PregnancyFactorType_id
						and (
							PFUC.QuestionType_id in (select QuestionType_id from QuestionTypeList)
							or PFUC.Diag_id in (select Diag_id from DiagList)
						)
				)
		";
		$params = array(
			'PersonPregnancy_id' => $data['PersonPregnancy_id'],
			'EvnPS_id' => $data['mEvnPS_id']
		);
		//echo getDebugSQL($query, $params);exit;
		$resp1 = $this->queryResult($query, $params);

		//Факторы по ребенку
		$query = "
			with recursive DiagList as (
				select t.Diag_id, t.Diag_pid
				from v_Diag t 
				where t.Diag_id in ({$childDiagList_str})
				union all
				select e.Diag_id, e.Diag_pid
				from v_Diag e 
				inner join DiagList as d on d.Diag_pid = e.Diag_id
			)
			select
				PFT.PregnancyFactorType_id as \"PregnancyFactorType_id\",
				PFT.PregnancyFactorType_fid as \"PregnancyFactorType_fid\",
				PFT.PregnancyFactorType_fpid as \"PregnancyFactorType_fpid\",
				PFT.PregnancyFactorType_NameRu as \"PregnancyFactorType_NameRu\",
				Diags.Diag_ids as \"Diag_ids\"
			from r101.v_PregnancyFactorType PFT 
			LEFT JOIN LATERAL (
					select
						string_agg(cast(PFUC.Diag_id as varchar), ',') as Diag_ids
					from r101.v_PregnancyFactorUslugaComplex PFUC
					where PFUC.PregnancyFactorType_id = PFT.PregnancyFactorType_id and PFUC.Diag_id is not null
			) as Diags ON true
			where
				PFT.PregnancyFactorType_fpid in (5,6)
				and exists(
					select *
					from r101.v_PregnancyFactorUslugaComplex PFUC 
					where
						PFUC.PregnancyFactorType_id = PFT.PregnancyFactorType_id
						and PFUC.Diag_id in (select Diag_id from DiagList)
				)
		";
		$params = array(
			'EvnPS_id' => $data['cEvnPS_id']
		);
		//echo getDebugSQL($query, $params);exit;
		$resp2 = $this->queryResult($query, $params);

		$factors = array_merge($resp1, $resp2);

		$factorsByParent = array();
		foreach($factors as $factor) {
			if (empty($factor['Diag_ids'])) continue;
			$fid = $factor['PregnancyFactorType_fid'];
			$fpid = $factor['PregnancyFactorType_fpid'];
			$diagList = $this->queryList("
				with recursive DiagList as (
					select t.Diag_id, t.Diag_pid, t.DiagLevel_id
					from v_Diag t 
					where t.Diag_id in ({$factor['Diag_ids']})
					union all
					select e.Diag_id, e.Diag_pid, e.DiagLevel_id
					from v_Diag e 
					inner join DiagList as d on d.Diag_id = e.Diag_pid
				)
				select D.Diag_id as \"Diag_id\"
				from v_Diag D 
				inner join DiagList DL  on DL.Diag_id = D.Diag_id
				where D.DiagLevel_id = 4
			");
			if (!is_array($diagList)) {
				return $this->createError('','Ошибка при получении перечня диагнозов по фактору');
			}
			$factor['Diag_ids'] = implode(',', $diagList);

			$factorsByParent[$fpid][$fid] = $factor;
		}

		//Исключение факторов "Другие (указать)", если по всем диагнозам матери/ребенка найдены конкретные факторы
		$map = array(
			'1' => 34,
			'3' => 47,
			'5' => 60,
			'6' => 74,
		);
		foreach($map as $fpid => $other_fid) {
			if (isset($factorsByParent[$fpid]) && isset($factorsByParent[$fpid][$other_fid])) {
				$tmp_diag_list = in_array($fpid, array(1,3))?$motherDiagList:$childDiagList;
				foreach($factorsByParent[$fpid] as $fid => $factor) {
					if ($fid == $other_fid) continue;
					$factorDiagList = explode(',', $factor['Diag_ids']);
					$tmp_diag_list = array_diff($tmp_diag_list, $factorDiagList);
				}
				$other_diag_list = explode(',', $factorsByParent[$fpid][$other_fid]['Diag_ids']);
				if (count(array_intersect($tmp_diag_list, $other_diag_list)) == 0) {
					unset($factorsByParent[$fpid][$other_fid]);
				} else {
					$tmp_diag_list_str = implode(',', array_intersect($tmp_diag_list, $other_diag_list));
					$list = $this->queryList("select Diag_Name from v_Diag  where Diag_id in ({$tmp_diag_list_str})");
					if (!is_array($list)) {
						return $this->createError('','Ошибка при формировании строки "Другие факторы"');
					}
					$factorsByParent[$fpid][$other_fid]['OtherFactorText'] = implode(";\n", $list).".";
				}
			}
		}

		//Добавление факторов "Не было/Нет осложнений", если других факторов в разделах нет
		$map = array(
			'1' => 35,
			'3' => 48,
			'5' => 61,
			'6' => 75,
		);
		foreach($map as $fpid => $empty_fid) {
			if (!isset($factorsByParent[$fpid]) || count($factorsByParent[$fpid]) == 0) {
				$factor = $this->getFirstRowFromQuery("
					select 
						PFT.PregnancyFactorType_id as \"PregnancyFactorType_id\",
						PFT.PregnancyFactorType_fid as \"PregnancyFactorType_fid\",
						PFT.PregnancyFactorType_fpid as \"PregnancyFactorType_fpid\",
						PFT.PregnancyFactorType_NameRu as \"PregnancyFactorType_NameRu\",
						null as \"Diag_ids\"
					from r101.v_PregnancyFactorType PFT 
					where PFT.PregnancyFactorType_fid = :PregnancyFactorsType_fid
					limit 1
				", array('PregnancyFactorsType_fid' => $empty_fid));
				if (!is_array($factor)) {
					return $this->createError('',"Ошибка при получении данных фактора {$empty_fid}");
				}
				$factorsByParent[$fpid][$empty_fid] = $factor;
			}
		}

		$factorsResponse = array();
		foreach($factorsByParent as $factors) {
			$factorsResponse = array_merge($factorsResponse, array_values($factors));
		}

		return array(array(
			'success' => true,
			'Error_Msg' => '',
			'factors' => $factorsResponse
		));
	}

	/**
	 * Передача свидетельства о рождении в РПН
	 */
	function sendBirthSvidToRPN($data) {
		$params = array('BirthSvid_id' => $data['BirthSvid_id']);

		/*if ($this->getSyncObject('BirthSvid', $params['BirthSvid_id'])) {
			return $this->createError('','Свидетельство о рождении уже было передано в РПН');
		}*/

		$query = "
			select 
				Mother.Person_id as \"mPerson_id\",
				mP.BDZ_id as \"mPerson_rpnid\",
				Child.Person_id as \"cPerson_id\",
				cP.BDZ_id as \"cPerson_rpnid\",
				Child.Person_SurName as \"cPerson_SurName\",
				Child.Person_FirName as \"cPerson_FirName\",
				Child.Person_SecName as \"cPerson_SecName\",
				Child.Person_BirthDay as \"cPerson_BirthDay\",
				Child.Sex_id as \"cSex_id\",
				/*case
					when A.KLTown_id is not null then 4		--Село
					when A.KLCity_id is not null then 3		--Город
					when A.KLSubRgn_id is not null then 2	--Район
					when A.KLRgn_id is not null then 1		--Область
				end as TerritoryUnitType,*/
				case
					when A.KLCity_id is not null then 3		--Город
					else 4		--Село
				end as \"TerritoryUnitType\",
				BP.BirthPlace_Code as \"BirthPlace_Code\",
				BSS.BirthSpecStac_CountPregnancy as \"BirthSpecStac_CountPregnancy\",
				BSS.BirthSpecStac_CountBirth as \"BirthSpecStac_CountBirth\",
				BSS.BirthSpecStac_CountChild as \"BirthSpecStac_CountChild\",
				PNB.PersonNewborn_CountChild as \"PersonNewborn_CountChild\",
				CTT.ChildTermType_Code as \"ChildTermType_Code\",
				COALESCE(PNB.PersonNewborn_Weight, case
					when WeightOkei.Okei_NationSymbol = 'g' then BirthSvid_Mass
					when WeightOkei.Okei_NationSymbol = 'kg' then BirthSvid_Mass*1000
				end) as \"PersonNewborn_Weight\",
				COALESCE(PNB.PersonNewborn_Height, BS.BirthSvid_Height) as \"PersonNewborn_Height\",
				Apgar_1min.Value as \"Apgar_1min\",
				Apgar_5min.Value as \"Apgar_5min\",
				PNB.PersonNewborn_IsBreath as \"PersonNewborn_IsBreath\",
				PNB.PersonNewborn_IsHeart as \"PersonNewborn_IsHeart\",
				PNB.PersonNewborn_IsPulsation as \"PersonNewborn_IsPulsation\",
				PNB.PersonNewborn_IsMuscle as \"PersonNewborn_IsMuscle\",
				BSS.BirthSpecStac_OutcomDT as \"BirthSpecStac_OutcomDT\",
				BSS.BirthSpecStac_OutcomPeriod as \"BirthSpecStac_OutcomPeriod\",
				mEPS.EvnPS_id as \"mEvnPS_id\",
				cEPS.EvnPS_id as \"cEvnPS_id\",
				PNB.EvnSection_mid as \"EvnSection_id\",
				PP.PersonPregnancy_id as \"PersonPregnancy_id\",
				BS.Lpu_id as \"Lpu_id\",
				BS.BirthSvid_id as \"BirthSvid_id\",
				BS.BirthSvid_GiveDate as \"BirthSvid_GiveDate\",
				BS.BirthSvid_BirthDT as \"BirthSvid_BirthDT\",
				BA.Address_Address as \"BAddress_Address\",
				case
					when BE.BirthEducation_Code = 6 then 1
					when BE.BirthEducation_Code = 5 then 2
					when BE.BirthEducation_Code = 4 then 3
					when BE.BirthEducation_Code = 3 then 4
					when BE.BirthEducation_Code = 2 then 5
					when BE.BirthEducation_Code = 1 then 6
					else 9
				end as \"EducationType\",
				case
					when PFS.PregnancyFamilyStatus_Code = 4 then 1
					when PFS.PregnancyFamilyStatus_Code in (1,2) then 2
					--when PFS.PregnancyFamilyStatus_Code = 0 then 3
					when PFS.PregnancyFamilyStatus_Code = 5 then 4
					else 6
				end as \"FamilyStatus\"
			from
				v_BirthSvid BS 
				inner join v_PersonState Mother  on Mother.Person_id = BS.Person_id
				inner join v_PersonState Child  on Child.Person_id = BS.Person_cid
				inner join v_PersonNewBorn PNB  on PNB.Person_id = Child.Person_id
				left join v_BirthSpecStac BSS  on BSS.BirthSpecStac_id = PNB.BirthSpecStac_id
				left join Person mP  on mP.Person_id = Mother.Person_id
				left join Person cP  on cP.Person_id = Child.Person_id
				left join v_Address A  on A.Address_id = COALESCE(Child.PAddress_id, Child.UAddress_id)
				left join v_BirthPlace BP  on BP.BirthPlace_id = BS.BirthPlace_id
				left join v_ChildTermType CTT  on CTT.ChildTermType_id = PNB.ChildTermType_id
				left join v_Okei WeightOkei  on WeightOkei.Okei_id = BS.Okei_mid
				LEFT JOIN LATERAL (
					select cast(NewbornApgarRate_Values as int) as Value
					from v_NewbornApgarRate t 
					where PersonNewBorn_id = PNB.PersonNewBorn_id and NewbornApgarRate_Time = 1
                    limit 1
				) Apgar_1min ON true
				LEFT JOIN LATERAL (
					select cast(NewbornApgarRate_Values as int) as Value
					from v_NewbornApgarRate t 
					where PersonNewBorn_id = PNB.PersonNewBorn_id and NewbornApgarRate_Time = 5
                    limit 1
				) Apgar_5min ON true
				LEFT JOIN LATERAL(
					select EPS.EvnPS_id
					from v_EvnSection ES 
					inner join v_EvnPS EPS  on EPS.EvnPS_id = ES.EvnSection_pid
					where ES.EvnSection_id = BSS.EvnSection_id
                    limit 1
				) mEPS ON true
				LEFT JOIN LATERAL(
					select 
						PP.PersonPregnancy_id,
						PP.BirthEducation_id,
						PP.PregnancyFamilyStatus_id
					from v_PersonPregnancy PP 
					inner join v_PersonRegister PR  on PR.PersonRegister_id = PP.PersonRegister_id
					where PP.Person_id = Mother.Person_id
					and PR.PersonRegister_setDate < Child.Person_BirthDay
					and (PR.PersonRegister_disDate is null or PR.PersonRegister_disDate > Child.Person_BirthDay)
					order by PR.PersonRegister_setDate desc
                    limit 1
				) PP ON true
				left join v_BirthEducation BE  on BE.BirthEducation_id = PP.BirthEducation_id
				left join PregnancyFamilyStatus PFS  on PFS.PregnancyFamilyStatus_id = PP.PregnancyFamilyStatus_id
				left join v_EvnPS cEPS  on cEPS.EvnPS_id = PNB.EvnPS_id
				left join v_Address BA  on BA.Address_id = BS.Address_rid
			where
				BS.BirthSvid_id = :BirthSvid_id
		";
		//echo getDebugSQL($query, $params);exit;
		$birthInfo = $this->getFirstRowFromQuery($query, $params);
		if (!$birthInfo) {
			return $this->createError('','Ошибка при получении данных для свидетельсва о рождении');
		}

		if (empty($birthInfo['Apgar_1min']) || empty($birthInfo['Apgar_5min'])) {
			return $this->createError('',"В специфике новорожденного должна быть указана оценка<br/>состояния по шкале Апгар после 1 мин. и 5 мин.");
		}

		$period = $birthInfo['BirthSpecStac_OutcomPeriod'];
		if (empty($period) || $period < 22 || $period > 37) {
			return $this->createError('','Срок беременности должен быть от 22 до 37 недель.');
		}

		$lpulink = $this->getSyncObject('Lpu',$birthInfo['Lpu_id']);
		if (empty($lpulink)) {
			return $this->createError('','МО из свидетельства не сопоставлена с МО сервиса РПН');
		}
		$rpnLpu_id = $lpulink['Object_Value'];

		$sexlink = $this->getSyncObject('Sex', $birthInfo['cSex_id']);
		$rpnSex_id = $sexlink['Object_Value'];

		$mother = $this->exec("/person/{$birthInfo['mPerson_rpnid']}");
		if (is_array($mother) && !empty($mother['errorMsg'])) {
			return $this->createError('',$mother['errorMsg']);
		}

		$CharBirth = 5;	//При др.многопл. родах
		switch(true) {
			case ($birthInfo['BirthSpecStac_CountBirth'] == 1):
				$CharBirth = 2;	//При одноплодных родах
				break;
			case ($birthInfo['BirthSpecStac_CountBirth'] == 2 && $birthInfo['PersonNewborn_CountChild'] == 1):
				$CharBirth = 3;	//Первым из двойни
				break;
			case ($birthInfo['BirthSpecStac_CountBirth'] == 2 && $birthInfo['PersonNewborn_CountChild'] == 2):
				$CharBirth = 4;	//Вторым из двойни
				break;
		}

		$birthCriteries = array();
		if (!empty($birthInfo['PersonNewborn_IsBreath'])) {
			$birthCriteries[] = 1;
		}
		if (!empty($birthInfo['PersonNewborn_IsHeart'])) {
			$birthCriteries[] = 2;
		}
		if (!empty($birthInfo['PersonNewborn_IsPulsation'])) {
			$birthCriteries[] = 3;
		}
		if (!empty($birthInfo['PersonNewborn_IsMuscle'])) {
			$birthCriteries[] = 4;
		}

		$resp = $this->getPregnancyFactorsList($birthInfo);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$factorsBirths = array();
		foreach($resp[0]['factors'] as $factor) {
			$factorsBirths[] = array(
				'ID' => -1,				//Идентификатор сохраненного фактора
				'BirthRegID' => -1,		//Идентификатор свидетельсва о рождении
				'FactorsPregnancyDetailID' => $factor['PregnancyFactorType_fid'],
				'FactorsPregnancyDetailOther' => !empty($factor['OtherFactorText'])?$factor['OtherFactorText']:null
			);
		}
		//print_r($factorsBirths);exit;


		$Birth = array(
			'ID' => -1,			//birthRegID - BirthSvid_id
			'Loc' => $birthInfo['BirthPlace_Code']+1,
			'AddressText' => $birthInfo['BirthPlace_Code']==3?$birthInfo['BAddress_Address']:null,	//Заполняется если Loc = 'В другом месте'
			'Num' => null,			//GET /birth/nextnum
			'Dt' => null/*$birthInfo['BirthSvid_GiveDate']->format("Y-m-d\TH:i:s")*/, 	//GET /attachment/time - Дата выдачи свидетельства
			'MO' => $rpnLpu_id,
			'CharBirth' => $CharBirth,		//Ребенок родился
			'Mature' => $birthInfo['ChildTermType_Code']+1,		//Доношенность
			'Mass' => $birthInfo['PersonNewborn_Weight'],
			'Growth' => $birthInfo['PersonNewborn_Height'],
			'Apgar1' => $birthInfo['Apgar_1min'],
			'Apgar2' => $birthInfo['Apgar_5min'],
			'HouseholdPos' => $birthInfo['FamilyStatus'],
			'EducTypeID' => $birthInfo['EducationType'],
			'DateBirth' => $birthInfo['BirthSvid_BirthDT']->format("Y-m-d\TH:i:s"),
			'PeriodBornFrom' => $period,
			'PeriodBornTo' => $period,
			'PregnancyCount' => $birthInfo['BirthSpecStac_CountPregnancy'],
			'BirthCount' => $birthInfo['BirthSpecStac_CountBirth'],
			'BornChildCount' => $birthInfo['PersonNewborn_CountChild'],
			'TerritoryUnitType' => $birthInfo['TerritoryUnitType'],		//Житель города/села
			'ConfirmData' => true,			//Подтверждение. После него нельзя изменять свидетельство
			'birthCriteries' => $birthCriteries,
			'Sex' => $rpnSex_id,
			'FlId' => $birthInfo['cPerson_rpnid'],	//flPersonID - cPerson_id
			'flPerson' => array(
				'PersonID' => $birthInfo['cPerson_rpnid'],
				'lastName' => $birthInfo['cPerson_SurName'],
				'firstName' => $birthInfo['cPerson_FirName'],
				'secondName' => $birthInfo['cPerson_SecName'],
				'sexID' => $rpnSex_id,
				'birthDate' => $birthInfo['BirthSvid_BirthDT']->format("Y-m-d\TH:i:s"),
				//Гражданство и национальность берутся от матери
				'national' => array('id' => $mother->national, 'nameRU' => null, 'nameKZ' => null),
				'citizen' => array('id' => $mother->citizen, 'nameRU' => null, 'nameKZ' => null),
			),
			'Mother' => $birthInfo['mPerson_rpnid'],
			'motherAddressID' => null,
			'motherPerson' => array(
				'PersonID' => $birthInfo['mPerson_rpnid'],	//Заполнять ID. Другая информация не обязательна
				'lastName' => null,
				'firstName' => null,
				'secondName' => null,
				'sexID' => null,
				'birthDate' => null,
				'national' => array('id' => $mother->national, 'nameRU' => null, 'nameKZ' => null),
				'citizen' => array('id' => $mother->citizen, 'nameRU' => null, 'nameKZ' => null),
			),
			'form2009y' => array(
				'MO' => $rpnLpu_id,
				'MotherID' => $birthInfo['mPerson_rpnid'],
				'NumberDoc' => null,	//GET /birth/forma2009y/nextnumdoc
				'RegDate' => null/*$birthInfo['BirthSvid_GiveDate']->format("Y-m-d\TH:i:s")*/,
				'Transfer' => false,
				'TransferDate' => null,
				'WrittenOutDate' => null,
				'LevelMOBirth' => ($birthInfo['BirthPlace_Code']==1)?1:null,	//???
			),
			'factorsBirths' => $factorsBirths,
			'errorMessage' => null,
			'originalID' => null,
			'status' => null,
			'systemDatetime' => null
		);
		//print_r($Birth);exit;

		$this->load->library('swServiceKZBot', $this->config->item('RPN'), 'RpnBot');
		try {
			$result = $this->RpnBot->sendBirth($Birth);
		} catch(Exception $e) {
			return $this->createError('',$e->getMessage());
		}

		if (!is_array($result) || !isset($result['birthRegID'])) {
			return $this->createError('','Ошибка при передаче свидетельства о рождении в РПН');
		}

		$this->saveSyncObject('BirthSvid', $birthInfo['BirthSvid_id'], $result['birthRegID']);
		$this->saveSyncObject('form2009y', $birthInfo['cPerson_id'], $result['form2009yID']);

		$this->load->model('MedSvid_model');
		$resp = $this->MedSvid_model->updateMedSvidNum(array(
			'MedSvid_id' => $birthInfo['BirthSvid_id'],
			'MedSvid_Num' => $result['Num']
		), 'birth');
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array(array('success' => true, 'Error_Msg' => '', 'BirthSvid_Num' => $result['Num']));
	}
	
	/**
	 * Получение изменений истории прикрепления за период
	 */
	function getHistoryAttachByPeriod($data) {
		set_time_limit(0);
		
		$this->load->model('ServiceList_model');
		$ServiceList_id = 24;
		$begDT = date('Y-m-d H:i:s');
		$resp = $this->ServiceList_model->saveServiceListLog(array(
			'ServiceListLog_id' => null,
			'ServiceList_id' => $ServiceList_id,
			'ServiceListLog_begDT' => $begDT,
			'ServiceListResult_id' => 2,
			'pmUser_id' => 1
		));
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
		}
		$this->ServiceListLog_id = $resp[0]['ServiceListLog_id'];
		
		$lpu_ids = $this->queryResult("
			select l.Lpu_id as \"Lpu_id\", l.Lpu_Nick as \"Lpu_Nick\"
			from v_lpu l 
			inner join v_org o  on o.org_id = l.org_id
			/*inner join r101.GetMO MO  on MO.Lpu_id = l.Lpu_id*/
			where o.org_isaccess = 2
		");
		
		foreach($lpu_ids as $lpu) {
			$res = array(array('success' => false));
			for($i=0; ($i<3 && $res[0]['success'] != true); $i++) {
				$res = $this->getLpuRegionList(array(
					'Lpu_id' => $lpu['Lpu_id'],
					'Lpu_Nick' => $lpu['Lpu_Nick'],
					'Server_id' => $lpu['Lpu_id'],
					'pmUser_id' => 1
				));
				if ($res[0]['success'] != true && isset($res[0]['Error_Msg'])) {
					$this->writeDetailLog("{$lpu['Lpu_Nick']}: {$res[0]['Error_Msg']}", 2);
				}
			}
		}
		
		foreach($lpu_ids as $lpu) {
			$res = false;
			for($i=0; ($i<3 && $res !== true); $i++) {
				$res = $this->_getHistoryAttachByPeriod(array(
					'Lpu_id' => $lpu['Lpu_id'],
					'Lpu_Nick' => $lpu['Lpu_Nick'],
					'Server_id' => $lpu['Lpu_id'],
					'pmUser_id' => 1
				));
				sleep(5);
			}
		}

		foreach($lpu_ids as $lpu) {
			$this->closeIrrelevantAttachments(array(
				'Lpu_id' => $lpu['Lpu_id'],
				'Lpu_Nick' => $lpu['Lpu_Nick'],
				'Server_id' => $lpu['Lpu_id'],
				'pmUser_id' => 1,
			));
		}
		
		$endDT = date('Y-m-d H:i:s');
		$resp = $this->ServiceList_model->saveServiceListLog(array(
			'ServiceListLog_id' => $this->ServiceListLog_id,
			'ServiceList_id' => $ServiceList_id,
			'ServiceListLog_begDT' => $begDT,
			'ServiceListLog_endDT' => $endDT,
			'ServiceListResult_id' => 1,
			'pmUser_id' => 1
		));
		
		return array(array('success' => true, 'Error_Msg' => ''));
	}
	
	/**
	 * Получение изменений истории прикрепления за период
	 */
	function _getHistoryAttachByPeriod($data, $page = 1) {
		
		$this->textlog->add("getHistoryAttachByPeriod start");

		// Получаем данные по прикерплению
		$result = $this->exec('/attachment/GetHistoryAttachByPeriodV2', 'post', json_encode(array(
			'WithoutInnerRequest' => false,
			'OrgHealthCareId' => $data['Lpu_id'],
			'PageNo' => $page,
			'PageSize' => 5000,
			'StartRangeDate' => date('c', strtotime('-1 day')),
			'EndRangeDate' => date('c')
		)));
		
		$stat = array('all'=>count($result),'ins'=>0,'upd'=>0, 'del'=>0);
		$statPers = array('all'=>0,'ins'=>0,'upd'=>0, 'del'=>0);
		
		//$this->textlog->add("result: ".print_r($result, true));
		if (!is_array($result)) {
			$this->writeDetailLog("{$data['Lpu_Nick']}: ошибка при запросе  данных об  изменении прикреплений", 2);
			return $this->err("{$data['Lpu_Nick']}: ошибка при запросе  данных об  изменении прикреплений");
		} else if (!empty($result['errorMsg'])) {
			$this->writeDetailLog("{$data['Lpu_Nick']}: {$result['errorMsg']}", 2);
			return $this->err("{$data['Lpu_Nick']}: {$result['errorMsg']}");
		} else if (count($result) == 0) {
			return $this->err("{$data['Lpu_Nick']}: Нет обновлений");
		} else {
			foreach ($result as $k=>$row) {
				if ($row->AttachStatus == 'ATTACH') {
					$person = $this->getFirstRowFromQuery("
						select 
							p.Person_id as \"Person_id\", 
							pc.Lpu_id as \"Lpu_id\", 
							pc.LpuRegion_id as \"LpuRegion_id\", 
							l.Lpu_Nick as \"Lpu_Nick\",
							lr.LpuRegion_Name as \"LpuRegion_Name\",
							PS.Person_SurName||' '||PS.Person_FirName||COALESCE(' '||PS.Person_SecName,'') as \"Person_Fio\",
							to_char(pc.PersonCard_begDate, 'YYYY-MM-DD') as \"PersonCard_begDate\",
							to_char(pc.PersonCard_endDate, 'YYYY-MM-DD') as \"PersonCard_endDate\"
						from Person p  
							left join v_PersonState PS  on PS.Person_id = p.Person_id
							left join v_PersonCard_all pc  on pc.Person_id = p.Person_id and pc.LpuAttachType_id = 1
							left join v_Lpu l  on l.Lpu_id = pc.Lpu_id
							left join v_LpuRegion lr  on lr.LpuRegion_id = pc.LpuRegion_id
						where p.BDZ_id = ?", array($row->PersonId));
					if($person !== false) {
						// отсутствует прикерпление - сразу прикрепляем
						if(empty($person['Lpu_id'])) {
							$this->importPersonCard(array(
								'Person_id' => $person['Person_id'],
								'pmUser_id' => 1,
								'Server_id' => 1,
								'session' => null
							), $row->PersonId, $row, $stat);
							$this->writeDetailLog("{$person['Lpu_Nick']}: Пациент {$person['Person_Fio']} прикреплен к участку №{$row->TerritoryServiceId}", 1);
						}
						// совпало всё, пишем в лог и идем дальше
						elseif (
							$person['LpuRegion_id'] == $row->TerritoryServiceId && 
							$person['PersonCard_begDate'] == date('Y-m-d', strtotime($row->BeginDate))
						) {
							$this->writeDetailLog("{$person['Lpu_Nick']}: {$person['Person_Fio']} данные о прикреплении к участку №{$person['LpuRegion_Name']} актуальны", 1);
						}
						// совпал участок, но не совпала дата, пишем в лог и идем дальше
						elseif (
							$person['LpuRegion_id'] == $row->TerritoryServiceId && 
							$person['PersonCard_begDate'] != date('Y-m-d', strtotime($row->BeginDate))
						) {
							$this->writeDetailLog("{$person['Lpu_Nick']}: {$person['Person_id']} {$person['Person_Fio']} Дата начала прикрепления: к участку {$person['LpuRegion_id']} № участка {$person['LpuRegion_Name']} отличается от даты прикрепления в Казмед. От РПН получена дата прикрепления: " .  date('Y-m-d', strtotime($row->BeginDate)), 2);
						}
						// не совпал участок
						elseif ($person['LpuRegion_id'] != $row->TerritoryServiceId) {
							if ($person['PersonCard_begDate'] > date('Y-m-d', strtotime($row->BeginDate))) {
								$this->writeDetailLog("{$person['Lpu_Nick']}: {$person['Person_id']} {$person['Person_Fio']}  Получена дата начала прикрепления  к {$person['Lpu_Nick']} участок {$person['LpuRegion_id']} № участка {$person['LpuRegion_Name']}, меньшая чем дата начала открытого  прикрепления в Казмед", 1);
							}
							if ($person['PersonCard_begDate'] <= date('Y-m-d', strtotime($row->BeginDate))) {
								// переприкрепляем
								$this->importPersonCard(array(
									'Person_id' => $person['Person_id'],
									'pmUser_id' => 1,
									'Server_id' => 1,
									'session' => null
								), $row->PersonId, $row, $stat);
								$this->writeDetailLog("{$person['Lpu_Nick']}: Пациент {$person['Person_Fio']} откреплен от участка №{$person['LpuRegion_Name']}  и прикреплен к участку №{$row->TerritoryServiceId}", 1);
							}
						}
					} else {
						// отсутствует человек - создаем человека 
						$rpnPerson = $this->exec("/person/{$row->PersonId}");
						$respPers = $this->importPerson(array(
							'Person_id' => null,
							'pmUser_id' => 1,
							'Server_id' => 1,
							'session' => null
						), $rpnPerson, $statPers);
						// и прикрепляем
						if (empty($respPers[0]['Error_Msg'])) {
							$Person_id = $respPers[0]['Person_id'];
							$this->importPersonCard(array(
								'Person_id' => $Person_id,
								'pmUser_id' => 1,
								'Server_id' => 1,
								'session' => null
							), $row->PersonId, $row, $stat);
							$this->writeDetailLog("{$person['Lpu_Nick']}: Добавлен пациент {$respPers[0]['Person_Fio']}", 1);
						}
					}				
				} elseif ($row->AttachStatus == 'DETACH') {
					// открепляем
					if (!empty($person['Lpu_id']) && empty($person['PersonCard_endDate'])) {
						$this->importPersonCard(array(
							'Person_id' => $person['Person_id'],
							'pmUser_id' => 1,
							'Server_id' => 1,
							'session' => null
						), $row->PersonId, $row, $stat);
						$this->writeDetailLog("{$person['Lpu_Nick']}: Пациент {$person['Person_Fio']} откреплен от участка №{$person['LpuRegion_Name']}", 1);
					}
				}
			}
			
			// рекурсия, пока не закончатся записи
			if(count($result) == 5000) {
				$this->_getHistoryAttachByPeriod($data, $page++);
			}
		}
		
		$this->textlog->add("getHistoryAttachByPeriod end");
		return true;
	}

	/**
	 * Закрытие неактульных прикреплений
	 * @param array $data
	 * @return bool
	 */
	function closeIrrelevantAttachments($data) {
		$lpulink = $this->getSyncObject('Lpu', $data['Lpu_id']);
		if (empty($lpulink)) return true;
		$rpnLpu_id = $lpulink['Object_Value'];

		$query = "
			select 
				LR.LpuRegion_id as \"LpuRegion_id\",
				OSL.Object_sid as \"rpnLpuRegion_id\"
			from 
				v_LpuRegion LR 
				INNER JOIN LATERAL(
					select Object_sid
					from v_ObjectSynchronLog 
					where ObjectSynchronLogService_id = :Service_id 
					and Object_Name = 'LpuRegion' and Object_id = LR.LpuRegion_id
					order by Object_setDT desc
					limit 1
				) OSL ON true
			where
				LR.Lpu_id = :Lpu_id
		";
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'Service_id' => $this->ObjectSynchronLog_model->serviceId
		);
		$LpuRegionList = $this->queryResult($query, $params);

		foreach($LpuRegionList as $LpuRegion) {
			$rpnData = array();
			$pageSize = 100;
			$page = 0;

			do {
				$page++;
				$result = $this->exec("/person/search4/{$page}/{$pageSize}",  'post', json_encode(array(
					'territory' => $LpuRegion['rpnLpuRegion_id']
				)));
				$rpnData = array_merge($rpnData, objectToArray($result));
			} while(
				count($result) == $pageSize
			);

			$rpnDataByPerson = array();
			foreach($rpnData as $item) {
				$rpnDataByPerson[(string)$item['PersonID']] = $item;
			}

			$promedData = array();
			$pageSize = 500;
			$page = 0;

			$query = "
				select
					-- select
					OSL.Object_sid as \"rpnPerson_id\",
					PS.Person_id as \"Person_id\",
					PS.Person_Inn as \"Person_Inn\",
					PC.PersonCard_id as \"PersonCard_id\"
					-- end select
				from
					-- from
					v_PersonCard PC 
					inner join v_PersonState PS  on PS.Person_id = PC.Person_id
					INNER JOIN LATERAL (
						select OSL.*
						from v_ObjectSynchronLog OSL 
						where OSL.ObjectSynchronLogService_id = :Service_id
						and OSL.Object_Name = 'Person' and OSL.Object_id = pc.Person_id
                        limit 1
					) OSL ON true
					-- end from
				where
					-- where
					PC.LpuRegion_id = :LpuRegion_id
					-- end where
				order by
					-- order by
					PC.PersonCard_id
					-- end order by
			";

			do {
				$page++;
				$params = array(
					'LpuRegion_id' => $LpuRegion['LpuRegion_id'],
					'Service_id' => $this->ObjectSynchronLog_model->serviceId
				);
				$result = $this->queryResult(getLimitSQLPH($query, $pageSize*($page-1), $pageSize), $params);
				$promedData = array_merge($promedData, $result);
			} while(
				count($result) == $pageSize
			);

			foreach($promedData as $i => $item) {
				if (array_key_exists($item['rpnPerson_id'], $rpnDataByPerson)) {
					continue;
				}

				//Обработка прикрепления, не найденного в rpn: закрываем это прикрепление.
				$rpnItem = $this->exec("/person/{$item['rpnPerson_id']}");
				$attachment = $rpnItem->activeAttachment;

				$PersonCardData = $this->getPersonCardData($item);
				if (!is_array($PersonCardData) || count($PersonCardData) == 0) {
					continue;
				}
				$PersonCard = $PersonCardData[0];

				$PersonCard['PersonCard_begDate'] = ConvertDateFormat($PersonCard['PersonCard_begDate']);
				$PersonCard['action'] = 'edit';
				$PersonCard['noTransferService'] = true;
				$PersonCard['ignorePersonDead'] = true;
				$PersonCard['Server_id'] = $data['Server_id'];
				$PersonCard['pmUser_id'] = $data['pmUser_id'];

				if (!empty($attachment)) {
					$PersonCard['PersonCard_endDate'] = ConvertDateFormat($attachment->beginDate);
					$PersonCard['CardCloseCause_id'] = $this->getCardCloseCause($attachment->attachmentStatus, $attachment->causeOfAttach);
				} else {
					$PersonCard['PersonCard_endDate'] = ConvertDateFormat($this->currentDT);
					$PersonCard['CardCloseCause_id'] = 8;
				}
				if ($PersonCard['PersonCard_endDate'] < $PersonCard['PersonCard_begDate']) {
					$PersonCard['PersonCard_endDate'] = $PersonCard['PersonCard_begDate'];
				}

				$this->PersonCard_model->savePersonCard($PersonCard);
			}
		}

		return true;
	}
	

	/**
	 * Пишет сразу в текстовый и детальный лог (если есть)
	 */
	function writeDetailLog($message, $type = 1) {
		
		if(empty($this->ServiceListLog_id)) {
			return false;
		}
		
		$this->textlog->add($message);
		$this->ServiceList_model->saveServiceListDetailLog(array(
			'ServiceListLog_id' => $this->ServiceListLog_id,
			'ServiceListLogType_id' => $type,
			'ServiceListDetailLog_Message' => $message,
			'pmUser_id' => 1
		));
	}

	/**
	 * Получение даты и времени из сервиса РПН
	 */
	function getDateTime() {
		$dt = $this->exec("/attachment/time");

		if (is_array($dt) && !empty($dt['errorMsg'])) {
			return $this->createError('',$dt['errorMsg']);
		}
		return array(array('success' => true, 'Error_Msg' => '', 'DateTime' => $dt));
	}

	/**
	 * Функция для тестирования
	 */
	function test() {
		if (!empty($_REQUEST['breakConnection'])) {
			$this->breakConnectionResponse();
		}

		echo '<pre>';

		$result = $this->exec("/person/search4/1/10",  'post', json_encode(array(
			'fioiin' => $_REQUEST['iin'],
		)));
		print_r($result);

		exit;
	}
}
?>