<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property PersonIdentPackage_model dbmodel
 */

class PersonIdentPackage extends SwController {
	public $inputRules = array(
		'loadPersonIdentPackageGrid' => array(
			array(
				'field' => 'PersonIdentPackage_DateRange',
				'label' => 'Дата пакета',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonIdentPackage_IsResponseRetrieved',
				'label' => 'Ответ закгружен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadPersonIdentPackagePosGrid' => array(
			array(
				'field' => 'PersonIdentPackage_id',
				'label' => 'Идентификатор пакета для идентификации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonIdentPackagePosErrorType_Code',
				'label' => 'Код ошибки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonIdentState_id',
				'label' => 'Статус',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'int'
			),
		),
		'loadPersonIdentPackagePosHistoryGrid' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 30,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'int'
			),
		),
		'addPersonIdentPackagePos' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonIdentPackageTool_id',
				'label' => 'Идентификатор механизма добавления на идентификацию',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'addPersonIdentPackage' => array(
			array(
				'field' => 'RegistryFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PackageCount',
				'label' => 'Количестов пакетов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PackageSize',
				'label' => 'Размер пакета',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'StepSize',
				'label' => 'Размер шага',
				'rules' => '',
				'type' => 'int'
			),
		),
		'deletePersonIdentPackage' => array(
			array(
				'field' => 'PersonIdentPackage_id',
				'label' => 'Идентификатор пакета для идентификации',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'importPersonIdentPackageResponse' => array(
			array(
				'field' => 'PersonIdentPackage_Response',
				'label' => 'Файл ответа от сервиса идентификации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ARMType',
				'label' => 'Тип АРМа',
				'rules' => '',
				'type' => 'string'
			),
		)
	);

	/**
	 * @construct
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PersonIdentPackage_model', 'dbmodel');
	}

	/**
	 * Полученеи списка сформированных пакетов для идентификации
	 */
	function loadPersonIdentPackageGrid() {
		$data = $this->ProcessInputData('loadPersonIdentPackageGrid', true);
		if ($data == false) return false;

		$response = $this->dbmodel->loadPersonIdentPackageGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка записей в пакете для идентификации
	 */
	function loadPersonIdentPackagePosGrid() {
		$data = $this->ProcessInputData('loadPersonIdentPackagePosGrid', true);
		if ($data == false) return false;

		$response = $this->dbmodel->loadPersonIdentPackagePosGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка записей в пакете для идентификации
	 */
	function loadPersonIdentPackagePosHistoryGrid() {
		$data = $this->ProcessInputData('loadPersonIdentPackagePosHistoryGrid', true);
		if ($data == false) return false;

		$response = $this->dbmodel->loadPersonIdentPackagePosHistoryGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function addPersonIdentPackagePos() {
		$data = $this->ProcessInputData('addPersonIdentPackagePos', true);
		if ($data == false) return false;

		$response = $this->dbmodel->addPersonIdentPackagePos($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Добавление пакетов для идентфикации
	 */
	function addPersonIdentPackage() {
		$data = $this->ProcessInputData('addPersonIdentPackage', true);
		if ($data == false) return false;

		$response = $this->dbmodel->addPersonIdentPackage($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление пакета для идентификации
	 */
	function deletePersonIdentPackage() {
		$data = $this->ProcessInputData('deletePersonIdentPackage', true);
		if ($data == false) return false;

		$response = $this->dbmodel->deletePersonIdentPackage($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Импорт файла-ответа от сервиса идентификации
	 */
	function importPersonIdentPackageResponse() {
		$data = $this->ProcessInputData('importPersonIdentPackageResponse', true);
		if ($data == false) return false;

		if (!isset($_FILES['PersonIdentPackage_Response'])) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100011, 'Error_Msg' => 'Не выбран файл!')) ;
			return false;
		}
		$file = $_FILES['PersonIdentPackage_Response'];

		if (!is_uploaded_file($file['tmp_name'])) {
			$error = (!isset($file['error'])) ? 4 : $file['error'];
			switch($error) {
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
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012, 'Error_Msg' => $message));
			return false;
		}

		$this->load->library('textlog', array('file'=>'importPersonIdentPackageResponse_' . date('Y-m-d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('Запуск импорта PersonIdentPackage->importPersonIdentPackageResponse');

		$response = $this->dbmodel->importPersonIdentPackageResponse($data, $file);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}