<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry - операции с реестрами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      12.11.2009
*/
require_once(APPPATH.'controllers/Registry.php');

class Khak_Registry extends Registry {
	public $db = "registry";
	public $scheme = "r19";
	public $model_name = "Registry_model";
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Инициализация класса и настройки
		$this->inputRules['saveRegistry'] = array_merge($this->inputRules['saveRegistry'], array(
			array('field' => 'OrgSMO_id', 'label' => 'СМО', 'rules' => '', 'type' => 'id'),
			array(
				'field' => 'Registry_IsOnceInTwoYears',
				'label' => 'Признак "Раз в 2 года"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsFinanc',
				'label' => 'Подушевое финансирование',
				'rules' => '',
				'type' => 'id'
			)
		));

		$this->inputRules['exportUnionRegistryToXmlCheckExist'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id'),
		);

		$this->inputRules['exportRegistryToXml'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id'),
			array('field' => 'OverrideExportOneMoreOrUseExist', 'label' => 'Скачать с сервера или перезаписать', 'rules' => '', 'type' => 'int'),
		);

		$this->inputRules['importRegistryFLK'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryFile', 'label' => 'Файл', 'rules' => '', 'type' => 'string'),
		);

		$this->inputRules['importRegistryFromTFOMS'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryFile', 'label' => 'Файл', 'rules' => '', 'type' => 'string'),
		);
	}

	/**
	 * Функция проверяет, есть ли уже выгруженный файл реестра
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	public function exportUnionRegistryToXmlCheckExist() {
		$data = $this->ProcessInputData('exportUnionRegistryToXmlCheckExist', true);
		if ($data === false) { return false; }

		$res = $this->dbmodel->GetRegistryXmlExport($data);

		if (is_array($res) && count($res) > 0) {
			if ($res[0]['Registry_xmlExportPath'] == '1')
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'inprogress'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath']) > 0 && $res[0]['RegistryStatus_id'] == 4) // если уже выгружен реестр и оплаченный
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'onlyexists'));
				return true;
			}
			else if (strlen($res[0]['Registry_xmlExportPath']) > 0)
			{
				$this->ReturnData(array('success' => true, 'exportfile' => 'exists'));
				return true;
			}
			else {
				$this->ReturnData(array('success' => true, 'exportfile' => 'empty'));
				return true;
			}
		}
		else {
			$this->ReturnError('Ошибка получения данных по реестру');
			return false;
		}
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	public function exportRegistryToXml() {
		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportRegistryToXml($data);
		$this->ProcessModelSave($response, true, 'Ошибка при экспорте реестров')->ReturnData();
	}

	/**
	 * Импорт протоколов ФЛК
	 */
	public function importRegistryFLK() {
		$upload_path = './' . IMPORTPATH_ROOT . $_SESSION['lpu_id'] . '/';
		$allowed_types = explode('|','zip|xml');
		
		$data = $this->ProcessInputData('importRegistryFLK', true);
		if ( $data === false ) { return false; }

		$format_file="";
		$first_let_filename="";

		if (!empty($_POST['KatNasel_SysNick'])) {

			switch ($_POST['KatNasel_SysNick']) {
				case 'inog':
					$format_file = 'FNAME_1';
					$first_let_filename = 'v';
					break;
				case  'oblast':
					$format_file = 'FNAME_I';
					$first_let_filename = 'f';
					break;
			}
		}

		if ( !isset($_FILES['RegistryFile']) ) {
			$this->ReturnError('Не выбран файл реестра!', __LINE__);
			return false;
		}
		
		if ( !is_uploaded_file($_FILES['RegistryFile']['tmp_name']) ) {
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];

			switch ( $error ) {
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}

			$this->ReturnError($message, __LINE__);

			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);

		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			$this->ReturnError('Данный тип файла не разрешен.', __LINE__);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			mkdir( $upload_path );
		}

		if ( !@is_dir($upload_path) ) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', __LINE__);
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if ( !is_writable($upload_path) ) {
			$this->ReturnError('Загрузка файла не возможна из-за прав пользователя.', __LINE__);
			return false;
		}

		if ( strtolower($file_data['file_ext']) == 'xml' ) {
			$xmlfile = $_FILES['RegistryFile']['name'];

			if ( !move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile) ) {
				$this->ReturnError('Не удаётся переместить файл.', __LINE__);
				return false;
			}
		}
		else {
			// там должен быть файл m*.xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive();

			if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
				$xmlfile = "";

				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/^'.$first_let_filename.'.*\.xml$/i', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo($upload_path);
				$zip->close();
			}

			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}
				
		if ( empty($xmlfile) ) {
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		libxml_use_internal_errors(true);

		$xml = new SimpleXMLElement(file_get_contents($upload_path . $xmlfile));

		foreach ( libxml_get_errors() as $error ) {
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		libxml_clear_errors();

		if ( !property_exists($xml, $format_file) ) {
			$this->ReturnError('Неверный формат файла.', __LINE__);
			return false;
		}

		// Проверяем соответствие файла реестру
		$FNAME = $xml->$format_file->__toString();

		if ( empty($FNAME) ) {
			$this->ReturnError('Ошибка при получении имени исходного файла из загруженного файла.', __LINE__);
			return false;
		}

		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistryForImportXml($data);

		if ( !is_array($registrydata) ) {
			$this->ReturnError('Ошибка чтения данных реестра', __LINE__);
			return false;
		}

		$export_file_name_array = explode('/', $registrydata['Registry_xmlExportPath']);
		$export_file = array_pop($export_file_name_array);

		if ( mb_substr($FNAME, 1) != mb_substr($export_file, 1, mb_strlen($FNAME) - 1) ) {
			$this->ReturnError('Не совпадает название файла, импорт не произведен', __LINE__);
			return false;
		}

		$data['OrgSmo_id'] = $registrydata['OrgSmo_id'];
		$data['RegistryType_id'] = $registrydata['RegistryType_id'];
		$data['Registry_xmlExportPath'] = $registrydata['Registry_xmlExportPath'];

		$this->dbmodel->setRegistryEvnNum($data);

		if ( isset($this->dbmodel->registryEvnNum) && is_array($this->dbmodel->registryEvnNum) ) {
			$this->dbmodel->setRegistryEvnNumByNZAP($data);
		}

		//$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recAll = 0;
		$recErr = 0;
		$errorstxt = "";

		foreach ( $xml->PR as $onepr ) {
			$params = array(
				'pmUser_id' => $data['pmUser_id'],
				'Registry_id' => $data['Registry_id'],
				'N_ZAP' => $onepr->N_ZAP->__toString(),
				'IDCASE' => $onepr->IDCASE->__toString(),
				'SL_ID' => $onepr->SL_ID->__toString(),
				'ID_PAC' => $onepr->ID_PAC->__toString(),
				'OSHIB' => $onepr->OSHIB->__toString(),
				'IM_POL' => $onepr->IM_POL->__toString(),
				'BAS_EL' => $onepr->BAS_EL->__toString(),
				'COMMENT' => $onepr->COMMENT->__toString()
			);

			$recAll++;
			$recErr++;
			$SL_ID_array = [];

			if ( !empty($params['SL_ID']) ) {
				$SL_ID_array[] = $params['SL_ID'];
			}
			else if ( !empty($params['N_ZAP']) && isset($this->dbmodel->registryEvnNum) && is_array($this->dbmodel->registryEvnNum) ) {
				if (isset($this->dbmodel->registryEvnNumByNZAP[$params['N_ZAP']]) ) {
					$SL_ID_array = $this->dbmodel->registryEvnNumByNZAP[$params['N_ZAP']];
				}
				else if ( isset($this->dbmodel->registryEvnNum[$params['N_ZAP']]) ) {
					$SL_ID_array = $this->dbmodel->registryEvnNum[$params['N_ZAP']];
				}
			}

			foreach ( $SL_ID_array as $SL_ID ) {
				$params['SL_ID'] = $SL_ID;
				$evnData = $this->dbmodel->checkErrorDataInRegistry($params);

				if ( $evnData === false ) {
					$this->dbmodel->deleteRegistryErrorTFOMS($params);
					throw new Exception('Номер записи N_ZAP="' . $params['N_ZAP'] . '", SL_ID="' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен', __LINE__);
				}

				$params['Evn_id'] = $evnData['Evn_id'];

				$response = $this->dbmodel->setErrorFromImportRegistry($params);

				if ( !is_array($response) || count($response) == 0 ) {
					throw new Exception('Ошибка при обработке реестра!', __LINE__);
				}
				else if ( !empty($response['Error_Msg']) ) {
					throw new Exception($response['Error_Msg'], __LINE__);
				}
			}
		}

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recErr' => $recErr, 'recAll' => $recAll, 'Message' => 'Реестр успешно загружен.'));

		return true;
	}

	/**
	 * Импорт ответа из ТФОМС/СМО
	 */
	public function importRegistryFromTFOMS() {
		$upload_path = './' . IMPORTPATH_ROOT . $_SESSION['lpu_id'] . '/';
		$allowed_types = explode('|','zip|xml');
		
		$data = $this->ProcessInputData('importRegistryFromTFOMS', true);
		if ( $data === false ) { return false; }

		if ( !isset($_FILES['RegistryFile']) ) {
			$this->ReturnError('Не выбран файл реестра!', __LINE__);
			return false;
		}
		
		if ( !is_uploaded_file($_FILES['RegistryFile']['tmp_name']) ) {
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];

			switch ( $error ) {
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}

			$this->ReturnError($message, __LINE__);

			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);

		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			$this->ReturnError('Данный тип файла не разрешен.', __LINE__);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			mkdir( $upload_path );
		}

		if ( !@is_dir($upload_path) ) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', __LINE__);
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if ( !is_writable($upload_path) ) {
			$this->ReturnError('Загрузка файла не возможна из-за прав пользователя.', __LINE__);
			return false;
		}

		if ( strtolower($file_data['file_ext']) == 'xml' ) {
			$xmlfile = $_FILES['RegistryFile']['name'];

			if ( !move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile) ) {
				$this->ReturnError('Не удаётся переместить файл.', __LINE__);
				return false;
			}
		}
		else {
			// там должен быть файл h*.xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive();

			if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
				$xmlfile = "";

				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/^h.*\.xml$/i', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo($upload_path);
				$zip->close();
			}

			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}
				
		if ( empty($xmlfile) ) {
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		libxml_use_internal_errors(true);

		$xml = new SimpleXMLElement(file_get_contents($upload_path . $xmlfile));

		foreach ( libxml_get_errors() as $error ) {
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		libxml_clear_errors();

		if ( !property_exists($xml, 'ZGLV') || !property_exists($xml->ZGLV, 'FILENAME') ) {
			$this->ReturnError('Неверный формат файла.', __LINE__);
			return false;
		}

		// Проверяем соответствие файла реестру
		$FILENAME = $xml->ZGLV->FILENAME->__toString();

		if ( empty($FILENAME) ) {
			$this->ReturnError('Ошибка при получении имени исходного файла из загруженного файла.', __LINE__);
			return false;
		}

		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistryForImportXml($data);

		if ( !is_array($registrydata) ) {
			$this->ReturnError('Ошибка чтения данных реестра', __LINE__);
			return false;
		}

		$export_file_name_array = explode('/', $registrydata['Registry_xmlExportPath']);
		$export_file = array_pop($export_file_name_array);

		if ( mb_substr($FILENAME, 1) != mb_substr($export_file, 1, mb_strlen($FILENAME) - 1) ) {
			$this->ReturnError('Не совпадает название файла, импорт не произведен', __LINE__);
			return false;
		}

		//$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recAll = 0;
		$recErr = 0;
		$errorstxt = "";

		if ( !property_exists($xml, 'ZAP') ) {
			$this->ReturnError('Неверный формат файла.', __LINE__);
			return false;
		}

		foreach ( $xml->ZAP as $onezap ) {
			if ( !property_exists($onezap, 'Z_SL') ) {
				continue;
			}

			foreach ( $onezap->Z_SL as $onezsl ) {
				if ( !property_exists($onezsl, 'SL') ) {
					continue;
				}

				$IDCASE = $onezsl->IDCASE->__toString();

				foreach ( $onezsl->SL as $onesl ) {
					$recAll++;

					if ( !property_exists($onesl, 'SL_ID') || !property_exists($onesl, 'SANK') ) {
						continue;
					}

					$SL_ID = $onesl->SL_ID->__toString();

					foreach ( $onesl->SANK as $onesank ) {
						$recErr++;

						$params = array(
							'pmUser_id' => $data['pmUser_id'],
							'Registry_id' => $data['Registry_id'],
							'SL_ID' => $SL_ID,
							'OSHIB' => (property_exists($onesank, 'S_OSN') ? $onesank->S_OSN->__toString() : null),
							'COMMENT' => (property_exists($onesank, 'S_COM') ? $onesank->S_COM->__toString() : null),
							'BAS_EL' => null,
							'IDCASE' => $IDCASE,
							'IM_POL' => null,
							'N_ZAP' => $onezap->N_ZAP->__toString(),
						);

						$evnData = $this->dbmodel->checkErrorDataInRegistry($params);

						if ( $evnData === false ) {
							//$this->dbmodel->deleteRegistryErrorTFOMS($params);
							$this->ReturnError('Случай "' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре. Импорт не произведен.', __LINE__);
							return false;
						}

						$params['Registry_id'] = $evnData['Registry_id'];
						$params['Evn_id'] = $evnData['Evn_id'];

						$response = $this->dbmodel->setErrorFromImportRegistry($params);

						if ( !is_array($response) || count($response) == 0 ) {
							$this->ReturnError('Ошибка при обработке реестра!', __LINE__);
							return false;
						}
						else if ( !empty($response[0]['Error_Msg']) ) {
							$this->ReturnError($response[0]['Error_Msg'], __LINE__);
							return false;
						}
					}
				}
			}
		}

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recErr' => $recErr, 'recAll' => $recAll, 'Message' => 'Реестр успешно загружен.'));

		return true;
	}
}
