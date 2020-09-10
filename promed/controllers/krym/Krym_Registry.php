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

class Krym_Registry extends Registry {
	var $scheme = "r91";
	var $model_name = "Registry_model";
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules['setRegistryCheckStatus'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryCheckStatus_SysNick', 'label' => 'Сис. ник статуса', 'rules' => '', 'type' => 'string'),
			array('field' => 'RegistryCheckStatus_id', 'label' => 'Идентификатор статуса', 'rules' => '', 'type' => 'id')
		);

		$this->inputRules['importRegistryFromXml'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryFile', 'label' => 'Файл', 'rules' => '', 'type' => 'string'),
		);

		$this->inputRules['saveRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номер счета',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsActive',
				'label' => 'Признак активного регистра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgRSchet_id',
				'label' => 'Расчетный счет',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_accDate',
				'label' => 'Дата счета',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_IsRepeated',
				'label' => 'Повторная подача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Тип дисп-ции/медосмотра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Список подразделений',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_IsOnceInTwoYears',
				'label' => 'Признак "Раз в 2 года"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'Признак "ЗНО"',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['exportRegistryToXml'] = array(
			array(
				'field' => 'OverrideExportOneMoreOrUseExist',
				'label' => 'Флаг использования существующего или экспорта нового XML',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'onlyLink',
				'label' => 'Флаг вывода только ссылки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'send',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'forSign',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['importRegistryFromTFOMS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		);

		$this->inputRules['getUnionRegistryNumber'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			)
		);

		$this->inputRules['loadUnionRegistryGrid'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['loadUnionRegistryChildGrid'] = array(
			array(
				'field' => 'Registry_pid',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['loadUnionRegistryEditForm'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['deleteUnionRegistry'] = array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['saveUnionRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номер',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_accDate',
				'label' => 'Дата счета',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'RegistryGroupType_id',
				'label' => 'Тип объединенного реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgSMO_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsRepeated',
				'label' => 'Повторная подача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsOnceInTwoYears',
				'label' => 'Признак "Раз в 2 года"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'Признак "ЗНО"',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['loadUnionRegistryData'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Полис',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'filterRecords',
				'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			),
			array(
				'field' => 'forPrint',
				'label' => 'Признак получения данных для печати',
				'rules' => '',
				'type' => 'int'
			),
		);

		$this->inputRules['loadUnionRegistryErrorTFOMS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
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
				'field' => 'RegistryErrorType_Code',
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
				'field' => 'RegistryErrorTFOMS_Comment',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['loadRegistryNoPolis'] = array_merge($this->inputRules['loadRegistryNoPolis'], array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_FIO', 'label' => 'ФИО', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_OrgSmo', 'label' => 'СМО', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_Polis', 'label' => '№ полиса', 'rules' => '', 'type' => 'string'),
		));
	}

	/**
	 * Удаление объединённого реестра
	 */
	function deleteUnionRegistry() {
		$data = $this->ProcessInputData('deleteUnionRegistry', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->deleteUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();	
		return true;
	}

	/**
	 * Простановка статуса реестра
	 */
	function setRegistryCheckStatus() {
		$data = $this->ProcessInputData('setRegistryCheckStatus', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->setRegistryCheckStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * Сохранение объединённого реестра
	 */
	function saveUnionRegistry() {
		$data = $this->ProcessInputData('saveUnionRegistry', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		return true;
	}

	/**
	 * Получение номера объединённого реестра
	 */
	function getUnionRegistryNumber() {
		$data = $this->ProcessInputData('getUnionRegistryNumber', true);
		if ($data === false) { return false; }
		$Registry_Num = $this->dbmodel->getUnionRegistryNumber($data);
		$this->ReturnData(array(
			'UnionRegistryNumber' => $Registry_Num
		));
		return true;
	}

	/**
	 * Загрузка списка объединённых реестров
	 */
	function loadUnionRegistryGrid() {
		$data = $this->ProcessInputData('loadUnionRegistryGrid', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadUnionRegistryGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка формы редактирования объединённого реестра
	 */
	function loadUnionRegistryEditForm() {
		$data = $this->ProcessInputData('loadUnionRegistryEditForm', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadUnionRegistryEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	function loadUnionRegistryChildGrid() {
		$data = $this->ProcessInputData('loadUnionRegistryChildGrid', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadUnionRegistryChildGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Функция возвращает данные для дерева реестров в json-формате
	 */
	function loadRegistryTree() {
		/**
		 *	Получение ветки дерева реестров
		 */
		function getRegistryTreeChild($childrens, $field, $lvl, $node_id = "") {
			$val = array();
			$i = 0;

			if ( !empty($node_id) ) {
				$node_id = "/" . $node_id;
			}

			if ( $childrens != false && count($childrens) > 0 ) {
				foreach ( $childrens as $rows ) {
					$node = array(
						'text'=>trim($rows[$field['name']]),
						'id'=>$field['object'].".".$lvl.".".$rows[$field['id']].$node_id,
						'object'=>$field['object'],
						'object_id'=>$field['id'],
						'object_value'=>$rows[$field['id']],
						'leaf'=>$field['leaf'],
						'iconCls'=>$field['iconCls'],
						'cls'=>$field['cls']
					);
					$val[] = $node;
				}
			}

			return $val;
		}

		// TODO: Тут надо поменять на ProcessInputData
		$data = array();
		$data = $_POST;
		$data = array_merge($data, getSessionParams());
		$c_one = array();
		$c_two = array();

		// Текущий уровень
		if ( (!isset($data['level'])) || (!is_numeric($data['level'])) ) {
			$val = array();
			$this->ReturnData($val);
			return;
		}

		$node = "";

		if ( isset($data['node']) ) {
			$node = $data['node'];
		}

		if (mb_strpos($node, 'PayType.1.bud') !== false) {
			if ($data['level'] >= 2) {
				$data['level']++; // для бюджета нет объединённых реестров
			}
			$data['PayType_SysNick'] = 'bud';
		}

		$response = array();

		switch ( $data['level'] ) {
			case 0: // Уровень Root. ЛПУ
				$this->load->model("LpuStructure_model", "lsmodel");
				$childrens = $this->lsmodel->GetLpuNodeList($data);

				$field = array('object' => "Lpu",'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
			break;

			case 1: // Уровень 1. ОМС или бюджет
				$childrens = array(
					array('PayType_SysNick' => 'oms', 'PayType_Name' => 'ОМС'),
					array('PayType_SysNick' => 'bud', 'PayType_Name' => 'Местный и федеральный бюджет')
				);
				$field = Array('object' => "PayType",'id' => "PayType_SysNick", 'name' => "PayType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;

			case 2: // Уровень 2. Объединённые реестры
				$childrens = array(
					array('RegistryType_id' => 13, 'RegistryType_Name' => 'Объединённые реестры'),
				);
				$field = array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
			break;

			case 3: // Уровень 3. Типочки
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
			break;

			case 4: // Уровень 4. Статусы реестров
				$childrens = $this->dbmodel->loadRegistryStatusNode($data);
				$field = array('object' => "RegistryStatus",'id' => "RegistryStatus_id", 'name' => "RegistryStatus_Name", 'iconCls' => 'regstatus-16', 'leaf' => true, 'cls' => "file");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
			break;
		}

		if ( count($c_two) > 0 ) {
			$c_one = array_merge($c_one,$c_two);
		}

		$this->ReturnData($c_one);

		return true;
	}

	/**
	 * Функция проверяет, есть ли уже выгруженный файл реестра
	 * Входящие данные: _POST (Registry_id)
	 * На выходе: JSON-строка.
	 */
	function exportRegistryToXmlCheckExist() {
		$data = $this->ProcessInputData('exportRegistryToXmlCheckExist', true);
		if ($data === false) { return false; }

		$res = $this->dbmodel->GetRegistryXmlExport($data);

		if ( is_array($res) && count($res) > 0 ) {
			if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
				$this->ReturnData(array('success' => true, 'exportfile' => 'inprogress'));
				return true;
			}
			else if ( !empty($res[0]['Registry_xmlExportPath']) ) {
				$this->ReturnData(array('success' => true, 'exportfile' => ($res[0]['RegistryCheckStatus_Code'] == 1 ? 'only' : '') . 'exists'));
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
	 *	Экспорт реестра для ТФОМС
	 */
	function exportRegistryToXml() {
		ignore_user_abort(true);
		set_time_limit(60 * 60); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится; upd: 1 час, чтобы при включенном ignore_user_abort скрипт не выполнялся вечно

		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ( $data === false ) { return false; }

		if ( empty($data['Registry_id']) ) {
			$this->ReturnError('Ошибка. Неверно задан идентификатор счета!');
			return false;
		}

		$this->load->library('textlog', array('file'=>'exportRegistryToXml_' . date('Y-m-d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('Запуск');

		// Определяем надо ли при успешном формировании проставлять статус и соответсвенно не выводить ссылки
		if ( empty($data['send']) ) {
			$data['send'] = 0;
		}

		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		$this->textlog->add('GetRegistryXmlExport: Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр');
		$res = $this->dbmodel->GetRegistryXmlExport($data);
		$this->textlog->add('GetRegistryXmlExport: Проверка закончена');

		if ( !is_array($res) || count($res) == 0 ) {
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return false;
		}

		$data['KatNasel_id'] = $res[0]['KatNasel_id'];
		$data['KatNasel_SysNick'] = $res[0]['KatNasel_SysNick'];
		$data['OrgSmo_f002smocod'] = $res[0]['OrgSmo_f002smocod'];
		$data['OrgSMO_id'] = $res[0]['OrgSMO_id'];
		$data['Registry_begDate'] = $res[0]['Registry_begDate'];
		$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
		$data['Registry_IsZNO'] = $res[0]['Registry_IsZNO'];
		$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];
		$DispClass_id = $res[0]['DispClass_id'];
		$type = $res[0]['RegistryType_id'];

		$registryIsUnion = ($type == 13);

		$data['registryIsAfter20180601'] = ($data['Registry_begDate'] >= '20180601');
		$data['registryIsAfter20180925'] = ($data['Registry_begDate'] >= '20180925');
		$data['registryIsAfter20181225'] = ($data['Registry_begDate'] >= '20181225');

		if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
			$templateModificator = "_bud";
		}
		else if ( $data['registryIsAfter20181225'] === true ) {
			$templateModificator = "_2019";
		}
		else {
			$templateModificator = "_2018";

			if (in_array($type, array(1, 2, 14)) && $data['registryIsAfter20180925'] === false) {
				$templateModificator .= '_old';
			}
		}

		// Запрет экспорта и отправки в ТФОМС реестра нуждающегося в переформировании (refs #13648)
		if ( !empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2 ) {
			if ( $registryIsUnion ) {
				$this->ReturnError('Часть реестров нуждается в переформировании, экспорт невозможен.');
			}
			else {
				$this->ReturnError('Реестр нуждается в переформировании, экспорт невозможен.');
			}

			return false;
		}
		
		// Запрет экспорта и отправки в ТФОМС реестра при несовпадении суммы всем случаям (SUMV) итоговой сумме (SUMMAV). (refs #13806)
		// Проверка закомментирована
		// @task https://redmine.swan.perm.ru/issues/92630
		/*if ( !empty($res[0]['Registry_SumDifference']) ) {
			// добавляем ошибку
			// $data['RegistryErrorType_Code'] = 3;
			// $res = $this->dbmodel->addRegistryErrorCom($data);
			if ( $res[0]['RegistryType_id'] == 13 ) {
				$this->ReturnError('Экспорт невозможен. Неверная сумма по счёту и реестрам.', 12);
			}
			else {
				$this->ReturnError('Экспорт невозможен. Неверная сумма по счёту и реестру.', 12);
			}

			return false;
		}*/
		
		// Запрет экспорта и отправки в ТФОМС реестра при 0 записей
		if ( empty($res[0]['RegistryData_Count']) ) {
			$this->ReturnError('Экспорт невозможен. Нет случаев в реестре.', 13);
			return false;
		}
		
		$this->textlog->add('Получили путь из БД:' . $res[0]['Registry_xmlExportPath']);

		if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
			$this->textlog->add('Выход с сообщением: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			$this->ReturnError('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return false;
		}
		// если уже выгружен реестр
		else if ( !empty($res[0]['Registry_xmlExportPath']) ) {
			$this->textlog->add('Реестр уже выгружен');

			if ( empty($data['OverrideExportOneMoreOrUseExist']) ) {
				$this->textlog->add('Выход с вопросом: Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)');
				$this->ReturnError('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)', 11);
				return false;
			}
			else if ( $data['OverrideExportOneMoreOrUseExist'] == 1 ) {
				if (!empty($data['forSign'])) {
					$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
					$filebase64 = base64_encode(file_get_contents($res[0]['Registry_xmlExportPath']));
					$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
					return true;
				}
				$link = $res[0]['Registry_xmlExportPath'];
				$usePrevXml = '';

				if ( empty($data['onlyLink']) ) {
					$usePrevXml = 'usePrevXml: true, ';
				}

				echo "{'success':true, $usePrevXml'Link':'$link'}";
				$this->textlog->add('Выход с передачей ссылкой: '.$link);

				return true;
			}
			// Запрет переформирования заблокированного реестра
			// @task https://redmine.swan.perm.ru/issues/74209
			else if ( !empty($res[0]['RegistryCheckStatus_Code']) && $res[0]['RegistryCheckStatus_Code'] == 1 ) {
				$this->textlog->add('Выход с сообщением: Реестр заблокирован, переформирование невозможно.');
				$this->ReturnError('Реестр заблокирован, переформирование невозможно.');
				return false;
			}
		}

		$this->textlog->add('exportRegistryToXml: Тип оплаты реестра: ' . $data['PayType_SysNick']);
		$this->textlog->add('refreshRegistry: Пересчитываем реестр');

		// Удаление помеченных на удаление записей и пересчет реестра 
		if ( $this->refreshRegistry($data) === false ) {
			// выход с ошибкой
			$this->textlog->add('refreshRegistry: При обновлении данных реестра произошла ошибка.');
			$this->ReturnError('При обновлении данных реестра произошла ошибка.');
			return false;
		}

		$this->textlog->add('refreshRegistry: Реестр пересчитали');
		$this->textlog->add('Тип реестра: ' . $type);

		$data['isVzaimoraschet'] = false;

		// Формирование XML в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить XML не приходилось обращаться к разработчикам
		try {
			$data['Status'] = '1';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add('SetXmlExportStatus: Установили статус реестра в 1');

			// Объединенные реестры могут содержать данные любого типа
			// Получаем список типов реестров, входящих в объединенный реестр
			if ( $registryIsUnion ) {
				$registrytypes = $this->dbmodel->getUnionRegistryTypes($data['Registry_id']);

				if ( !is_array($registrytypes) || count($registrytypes) == 0 ) {
					// выход с ошибкой
					$this->textlog->add('getUnionRegistryTypes: При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
					throw new Exception('При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
				}

				// https://redmine.swan-it.ru/issues/126232
				if ( $res[0]['RegistryGroupType_id'] == 17 && !in_array(1, $registrytypes) ) {
					$registrytypes[] = 1;
					$data['isVzaimoraschet'] = true;
				}
			}
			else {
				$registrytypes[] = $type;

				// https://redmine.swan-it.ru/issues/126232
				if ( $type == 19 ) {
					$registrytypes[] = 1;
					$data['isVzaimoraschet'] = true;
				}
			}

			if (!empty($res[0]['Registry_FileNum'])) {
				$packNum = $res[0]['Registry_FileNum']; // Для конкретного объединённого реестра счетов номер пакета остаётся неизменным с момента создания.
			} else {
				$packNum = $this->dbmodel->SetXmlPackNum($data);

				if (empty($packNum)) {
					$this->textlog->add('Выход с ошибкой: Ошибка при получении номера выгружаемого пакета.');
					throw new Exception('Ошибка при получении номера выгружаемого пакета.');
				}
			}

			$Registry_EvnNum = array();
			$SCHET = $this->dbmodel->loadRegistrySCHETForXmlUsing($data, $type);

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];
			mkdir(EXPORTPATH_REGISTRY . $out_dir);

			$data_first_code = "H";
			$pers_first_code = "L";

			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				switch ($type) {
					case 1: // stac
						$first_code = 'S';
						break;
					case 2: // polka
						$first_code = 'P';
						break;
					case 14: // вмп
						$first_code = 'V';
						break;

					default:
						return false;
						break;
				}
			} else {
				if ($registryIsUnion) {
					switch ($res[0]['RegistryGroupType_id']) {
						case 1: // Оказании медицинской помощи кроме высокотехнологичной
							if ( $data['registryIsAfter20180925'] === true && $data['Registry_IsZNO'] == 2 ) {
								$data_first_code = "C";
							}
							else {
								$data_first_code = "H";
							}
							$pers_first_code = "L";
							break;
						case 2: // Оказании высокотехнологичной медицинской помощи
							$data_first_code = "T";
							$pers_first_code = "LT";
							break;
						case 3: // Дисп-ция взр. населения 1-ый этап
							$data_first_code = "DP";
							$pers_first_code = "LP";
							break;
						case 4: // Дисп-ция взр. населения 2-ый этап
							$data_first_code = "DV";
							$pers_first_code = "LV";
							break;
						case 5: // Дисп-ция детей-сирот стационарных 1-ый этап
						case 27: // Дисп-ция детей-сирот стационарных 1-ый этап
						case 28: // Дисп-ция детей-сирот стационарных 1-ый этап
							$data_first_code = "DS";
							$pers_first_code = "LS";
							break;
						case 6: // Дисп-ция детей-сирот усыновленных 1-ый этап
						case 29: // Дисп-ция детей-сирот усыновленных 1-ый этап
						case 30: // Дисп-ция детей-сирот усыновленных 1-ый этап
							$data_first_code = "DU";
							$pers_first_code = "LU";
							break;
						case 7: // Периодические осмотры несовершеннолетних
							$data_first_code = "DR";
							$pers_first_code = "LR";
							break;
						case 8: // Предварительные осмотры несовершеннолетних
							$data_first_code = "DD";
							$pers_first_code = "LD";
							break;
						case 9: // Профилактические осмотры несовершеннолетних
						case 31: // Профилактические осмотры несовершеннолетних
						case 32: // Профилактические осмотры несовершеннолетних
							$data_first_code = "DF";
							$pers_first_code = "LF";
							break;
						case 10: // Профилактические осмотры взрослого населения
							$data_first_code = "DO";
							$pers_first_code = "LO";
							break;
						case 16: // Взаиморасчеты по диспансеризации
							$data_first_code = "V";
							$pers_first_code = "L";
							break;
						case 17: // Взаиморасчеты по лечебно-диагностическим услугам
							$data_first_code = "W";
							$pers_first_code = "L";
							break;
					}
				} else {
					switch ($type) {
						case 1: //stac
						case 2: //polka
						case 15: //parka
							if ( $data['registryIsAfter20180925'] === true && $data['Registry_IsZNO'] == 2 ) {
								$data_first_code = "C";
							}
							else {
								$data_first_code = "H";
							}
							$pers_first_code = "L";
							break;

						case 6: //smp
							$data_first_code = "H";
							$pers_first_code = "L";
							break;

						case 14: //htm
							$data_first_code = "T";
							$pers_first_code = "L";
							break;

						case 7: //dd
							if ($DispClass_id == 2) {
								$data_first_code = "DV";
								$pers_first_code = "LV";
							} else {
								$data_first_code = "DP";
								$pers_first_code = "LP";
							}
							break;

						case 9: //orp
							if ($DispClass_id == 7 || $DispClass_id == 8) {
								// Дисп-ция детей-сирот усыновленных
								$data_first_code = "DU";
								$pers_first_code = "LU";
							} else {
								// Дисп-ция детей-сирот стационарных
								$data_first_code = "DS";
								$pers_first_code = "LS";
							}
							break;

						case 11: //prof
							$data_first_code = "DO";
							$pers_first_code = "LO";
							break;

						case 12: //teen inspection
							if ($DispClass_id == 6) {
								$data_first_code = "DR";
								$pers_first_code = "LR";
							} else if ($DispClass_id == 9 || $DispClass_id == 11) {
								$data_first_code = "DD";
								$pers_first_code = "LD";
							} else if ($DispClass_id == 10 || $DispClass_id == 12) {
								$data_first_code = "DF";
								$pers_first_code = "LF";
							}
							break;

						case 18: //calcdisp
							$data_first_code = "V";
							$pers_first_code = "L";
							break;

						case 19: //calcusluga
							$data_first_code = "W";
							$pers_first_code = "L";
							break;
					}
				}
			}

			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				$f_type = 'F'; // федеральный
				if ($data['PayType_SysNick'] == 'bud') {
					$f_type = 'L'; // местный
				}
				$fname_part = "M" . $SCHET[0]['CODE_MO'] . "Z" . $data['session']['region']['number'] . "_" . $data['Registry_endMonth'] . sprintf('%05d', $packNum);
				$rname = $data_first_code . $fname_part;
				$pname = $pers_first_code . $fname_part;
				$zname = $f_type . "_" . $first_code . "_H" . $fname_part;
			} else {
				if ( $data['registryIsAfter20180925'] === false ) {
					$packNum = sprintf('%05d', $packNum);
				}

				$platSign = 'T85000';
				$fname = "M" . $SCHET[0]['CODE_MO'] . $platSign . '_' . $data['Registry_endMonth'] . $packNum;

				$prefix = ($registryIsUnion ? '' : 'P');

				$rname = $prefix . $data_first_code . $fname;
				$pname = $prefix . $pers_first_code . $fname;
				$zname = $prefix . $data_first_code . $fname;
			}

			$file_zip_sign = $zname;
			$file_re_data_sign = $rname;
			$file_re_pers_data_sign = $pname;

			// файл-тело реестра
			$file_re_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";

			// временный файл-тело реестра
			$file_re_data_name_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . "_tmp.xml";

			// файл-перс. данные
			$file_re_pers_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";

			// Файл для журнала ошибок
			$error_log = EXPORTPATH_REGISTRY . $out_dir . "/" . swGenRandomString() . "_" . $rname . ".log";

			while ( file_exists($error_log) ) {
				$error_log = EXPORTPATH_REGISTRY . $out_dir . "/" . swGenRandomString() . "_" . $rname . ".log";
			}

			$SCHET[0]['FILENAME'] = $file_re_data_sign;
			$SCHET[0]['SD_Z'] = 0;
			$ZGLV = array();
			$ZGLV[0]['FILENAME1'] = $file_re_data_sign;
			$ZGLV[0]['FILENAME'] = $file_re_pers_data_sign;

			$this->load->library('parser');

			// Определяем шаблоны
			$registry_data_type = "pl";

			if ( $registryIsUnion ) {
				if (!empty($res[0]['RegistryGroupType_id']) && $res[0]['RegistryGroupType_id'] > 1) {
					if ( $res[0]['RegistryGroupType_id'] == 2 ) {
						$registry_data_type = "hmp";
					}
					else if ( in_array($res[0]['RegistryGroupType_id'], array(16, 17)) ) {
						$registry_data_type = "calc";
					}
					else {
						$registry_data_type = "disp";
					}
				}
			}
			else {
				if ( !in_array($type, array(1, 2, 6, 15)) ) {
					if ( $type == 14 ) {
						$registry_data_type = "hmp";
					}
					else if ( in_array($type, array(18, 19)) ) {
						$registry_data_type = "calc";
					}
					else {
						$registry_data_type = "disp";
					}
				}
			}

			// Разбиваем на части, ибо парсер не может пережевать большие объемы данных
			$person_data_template_body = "registry_krym_person_body";
			$person_data_template_header = "registry_krym_person_header";
			$person_data_template_footer = "registry_krym_person_footer";
			$registry_data_template_body = "registry_krym_{$registry_data_type}{$templateModificator}_body";
			$registry_data_template_header = "registry_krym_{$registry_data_type}_header";
			$registry_data_template_footer = "registry_krym_all_footer";

			$ZGLV[0]['PROGRAM'] = 'PROMED';

			if ( in_array($data['PayType_SysNick'], array('bud', 'fbud')) ) {
				$ZGLV[0]['VERSION'] = '1.0';
			}
			else {
				$ZGLV[0]['VERSION'] = '3.1';
			}

			$this->textlog->add("Чистим {$this->scheme}.RegistryDataRowNum для " . ($registryIsUnion === true ? "объединенного " : "") . " реестра " . $data['Registry_id']);

			$clearRowNumData = $this->dbmodel->clearRegistryDataRowNum($data['Registry_id'], $registryIsUnion);

			if ( $clearRowNumData === false ) {
				$this->textlog->add('Ошибка при удалении данных из таблицы с номерами случаев');
				throw new Exception('Ошибка при удалении данных из таблицы с номерами случаев');
			}
			else if ( is_array($clearRowNumData) && !empty($clearRowNumData['Error_Msg']) ) {
				$this->textlog->add($clearRowNumData['Error_Msg']);
				throw new Exception($clearRowNumData['Error_Msg']);
			}

			$this->textlog->add("... выполнено");

			// Заголовок для файла person
			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $person_data_template_header, $ZGLV[0], true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);

			foreach ( $registrytypes as $type ) {
				$this->textlog->add('Тип реестров: ' . $type);

				// Новый метод заменяет старые
				$SD_Z = $this->dbmodel->loadRegistryDataForXmlUsing2018($type, $data, $Registry_EvnNum, $file_re_data_name_tmp, $file_re_pers_data_name, $registry_data_template_body, $person_data_template_body, $registryIsUnion);

				if ( $SD_Z === false ) {
					$this->textlog->add('Ошибка при выгрузке данных');
					throw new Exception('Ошибка при выгрузке данных');
				}
				else if ( is_array($SD_Z) && !empty($SD_Z['Error_Msg']) ) {
					$this->textlog->add($SD_Z['Error_Msg']);
					throw new Exception($SD_Z['Error_Msg']);
				}

				$SCHET[0]['SD_Z'] += $SD_Z;
			}

			if ( $this->dbmodel->hasInvalidEvns() === true ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);

				$invalidEvnList = $this->dbmodel->getInvalidEvnList();

				// Если лог не указан, выдаем сообщение о первой ошибке
				if ( empty($error_log) ) {
					$array = array_shift($invalidEvnList);

					$this->ReturnError('Выгружаемый файл не соответствует структуре реестра. Необходимо переформировать реестр №' . $array['Registry_Num'] . ' (' . $this->dbmodel->getRegistryTypeById($array['RegistryType_id']) . ') и выгрузить повторно или удалить из реестра случай ' . $array['EvnList'][0] . ' и пересчитать реестр.');
				}
				else {
					$f = fopen($error_log, 'a');
					fwrite($f, 'Выгружаемый файл не соответствует структуре реестра. Необходимо переформировать реестр(ы) и выгрузить повторно или удалить из реестра указанные случаи и пересчитать реестр(ы)' . PHP_EOL);
					fwrite($f, PHP_EOL);

					foreach ( $invalidEvnList as $Registry_id => $InvalidRegistryData ) {
						fwrite($f, 'Реестр №' . $InvalidRegistryData['Registry_Num'] . ', идентификатор ' . $Registry_id . ' (' . $this->dbmodel->getRegistryTypeById($InvalidRegistryData['RegistryType_id']) . ')' . PHP_EOL);
						fwrite($f, 'Идентификаторы событий: ' . implode(', ', $InvalidRegistryData['EvnList']) . PHP_EOL);
						fwrite($f, PHP_EOL);
					}

					fclose($f);

					$zip = new ZipArchive();
					$zip->open($error_log . '.zip', ZIPARCHIVE::CREATE);
					$zip->AddFile($error_log, "error.log");
					$zip->close();
					$this->textlog->add('exportRegistryToXml: Упаковали в ZIP журнал ошибок ' . $error_log . '.zip');

					$this->ReturnError('<p>Обнаружены ошибки в структуре реестра</p><p><a href="' . $error_log . '.zip' . '" target="_blank">Скачать журнал ошибок</a></p>');
				}

				return false;
			}

			$this->textlog->add('Получили все данные из БД');

			$SCHET[0]['PROGRAM'] = 'PROMED';

			if ( in_array($data['PayType_SysNick'], array('bud', 'fbud')) ) {
				$SCHET[0]['VERSION'] = '1.0';
			}
			else if ( $data['registryIsAfter20181225'] === true ) {
				$SCHET[0]['VERSION'] = '3.1.2';
			}
			else if ( $data['registryIsAfter20180925'] === true ) {
				$SCHET[0]['VERSION'] = '3.1.1';
			}
			else {
				$SCHET[0]['VERSION'] = '3.1';
			}
			//$SCHET[0]['PLAT'] = '85000';

			// Заголовок файла с данными
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $registry_data_template_header, $SCHET[0], true);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml);

			// Тело файла с данными начитываем из временного (побайтно)
			if ( file_exists($file_re_data_name_tmp) ) {
				// Устанавливаем начитываемый объем данных
				$chunk = 10 * 1024 * 1024; // 10 MB

				$fh = @fopen($file_re_data_name_tmp, "rb");

				if ( $fh === false ) {
					$this->textlog->add('Ошибка при открытии файла');
					throw new Exception('Ошибка при открытии файла');
				}

				while ( !feof($fh) ) {
					file_put_contents($file_re_data_name, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($file_re_data_name_tmp);
			}

			$this->textlog->add('Перегнали данные из временного файла со случаями в основной файл');

			$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_footer, array(), true);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);

			$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_footer, array(), true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);

			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".zip";
			$this->textlog->add('Создали XML-файлы: (' . $file_re_data_name . ' и ' . $file_re_pers_data_name . ')');

			$this->textlog->add('Формируем ZIP-архив');

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_re_data_name, $file_re_data_sign . ".xml");
			$zip->AddFile($file_re_pers_data_name, $file_re_pers_data_sign . ".xml");
			$zip->close();
			$this->textlog->add('Упаковали в ZIP ' . $file_zip_name);
			
			$PersonData_registryValidate = true;
			$EvnData_registryValidate = true;
			if(array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK'] && $res[0]){
				$upload_path = 'RgistryFields/';
				// если включена проверка ФЛК в параметрах системы
				// получим xsd шаблон для проверки
				$settingsFLK = $this->dbmodel->loadRegistryEntiesSettings($res[0]);
				if($settingsFLK && count($settingsFLK) > 0){
					//если запись найдена
					$settingsFLK = $settingsFLK[0];
					$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
					$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;	

					//Проверка со случаями
					if($tplEvnDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplEvnDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$fileEvnDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplEvnDataXSD;
						//Файл с ошибками					
						$validateEvnData_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_EvnData_".$dirTpl.'.html';						
						if(file_exists($fileEvnDataXSD)) {
							$EvnData_registryValidate = $this->dbmodel->Reconciliation($file_re_data_name, $fileEvnDataXSD, 'file', $validateEvnData_err_file);
						}
					}
					//Проверка с персональными данными
					if($tplPersonDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplPersonDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$filePersonDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplPersonDataXSD;
						//Файл с ошибками					
						$validatePersonData_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_PersonData_".$dirTpl.'.html';
						if(file_exists($filePersonDataXSD)) {
							$PersonData_registryValidate = $this->dbmodel->Reconciliation($file_re_pers_data_name, $filePersonDataXSD, 'file', $validatePersonData_err_file);
						}
					}
				}
			}
			
			if($PersonData_registryValidate) unlink($file_re_data_name);
			if($EvnData_registryValidate) unlink($file_re_pers_data_name);
			if($PersonData_registryValidate || $EvnData_registryValidate) $this->textlog->add('Почистили папку за собой');
			/*
			unlink($file_re_data_name);
			unlink($file_re_pers_data_name);
			$this->textlog->add('Почистили папку за собой');
			*/
			
			if(!$PersonData_registryValidate || !$EvnData_registryValidate){
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
						<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
						<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
						<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
			}elseif (!$PersonData_registryValidate) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br>
						<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
						<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
			} elseif (!$EvnData_registryValidate) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p><br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
						<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
			}elseif ( file_exists($file_zip_name) ) {
				$data['Status'] = $file_zip_name;

				$data['Registry_EvnNum'] = json_encode($Registry_EvnNum);
				$this->dbmodel->SetXmlExportStatus($data);

				if ( $data['send'] == 1 ) {
					$this->textlog->add('Реестр успешно отправлен');
					$this->ReturnData(array('success' => true));
				} else if (!empty($data['forSign'])) {
					$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
					$filebase64 = base64_encode(file_get_contents($file_zip_name));
					$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
					return true;
				} else {
					$this->textlog->add('Передача ссылки: ' . $file_zip_name);
					// echo "{'success':true,'Link':'{$file_zip_name}'}";
				}

				$this->textlog->add("Передали строку на клиент {'success':true,'Link':'$file_zip_name'}");

				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);
			}
			else {
				$this->textlog->add("Ошибка создания архива реестра!");
				$this->ReturnError('Ошибка создания архива реестра!');
			}

			$this->textlog->add("Финиш");
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add($e->getMessage());
			$this->ReturnError($e->getMessage());
		}

		return true;
	}

	/**
	 * Импорт реестра из ТФОМС
	 */
	function importRegistryFromTFOMS() {
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip');

		set_time_limit(0);
		
		$data = $this->ProcessInputData('importRegistryFromTFOMS', true);
		if ($data === false) { return false; }

		$this->load->library('textlog', array('file'=>'importRegistryFromTFOMS_' . date('Y-m-d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('Запуск импорта, реестр: ' . $data['Registry_id']);

		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistry($data);

		if ( !is_array($registrydata) || count($registrydata) == 0 ) {
			$this->ReturnError('Ошибка чтения данных реестра', 100001);
			$this->textlog->add('Ошибка чтения данных реестра');
			return false;
		}

		$registrydata = $registrydata[0];

		$data['RegistryType_id'] = $registrydata['RegistryType_id'];
		$registryIsUnion = ($registrydata['RegistryType_id'] == 13);

		if($registryIsUnion){// #143614 для объединенного реестра проверка на наличие оплаченных предварительных реестров
			if((int)$registrydata['RegistryChildCheckStatus']){
				$message = 'Импорт реестра из ТФОМС невозможен, так как объединенный реестр содержит предварительный реестр в статусе «Оплаченные». Переведите реестр в статус «К оплате».';
				$this->ReturnError($message);// существует ли ErrorCode для этого случая?..
				$this->textlog->add($message);
				return false;
			}
		}

		/*if ( $this->dbmodel->hasRegistryPaid($data['Registry_id']) ) {
			$this->ReturnError('Перед импортом снимите отметку "оплачен" у всех реестров, входящих в объединенный реестр', 100011);
			return false;
		}*/

		if ( !isset($_FILES['RegistryFile'])) {
			$this->ReturnError('Не выбран файл реестра!', 100011);
			$this->textlog->add('Не выбран файл реестра!');
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

			$this->ReturnError($message, 100012);
			$this->textlog->add($message);
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			$this->ReturnError('Данный тип файла не разрешен.', 100013);
			$this->textlog->add('Данный тип файла не разрешен.');
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			mkdir( $upload_path );
		}

		if ( !@is_dir($upload_path) ) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', 100014);
			$this->textlog->add('Путь для загрузки файлов некорректен.');
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if ( !is_writable($upload_path) ) {
			$this->ReturnError('Загрузка файла невозможна из-за прав пользователя.', 100015);
			$this->textlog->add('Загрузка файла невозможна из-за прав пользователя.');
			return false;
		}

		$fileList = array();

		if ( strtolower($file_data['file_ext']) == 'xml' ) {
			$fileList[] = $_FILES['RegistryFile']['name'];

			if ( !move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$_FILES['RegistryFile']['name']) ) {
				$this->ReturnError('Не удаётся переместить файл.', 100016);
				$this->textlog->add('Не удаётся переместить файл.');
				return false;
			}
		}
		else {
			$zip = new ZipArchive;

			if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$fileList[] = $zip->getNameIndex($i);
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}

			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}

		$this->textlog->add('Распаковали/переместили файл');

		$recall = 0;
		$recerr = 0;
		$xmlfile = '';

		libxml_use_internal_errors(true);

		foreach ( $fileList as $filename ) {
			$this->textlog->add('Прочитали XML-файл: ' . $filename);
			$xmlString = file_get_contents($upload_path . $filename);
			$checkString = substr($xmlString, 0, 2048);
			if ( strpos($checkString, '<SCHET>') !== false ) {
				$xmlfile = $filename;
				break;
			}
		}

		if ( empty($xmlfile) ) {
			$this->ReturnError('Файл не является архивом реестра.', 100017);
			$this->textlog->add('Файл не является архивом реестра.');
			return false;
		}

		$header = substr($checkString, 0, strpos($checkString, '</SCHET>') + strlen('</SCHET>'));
		$footer = '</ZL_LIST>';

		unset($checkString);

		$xmlString = substr($xmlString, strlen($header));

		// 10 MB
		$chunkSize = 1024 * 1024 * 10;

		$this->textlog->add('Начинаем обработку реестра');
		$firstRead = true;

		$simpleRegistryList = array();
		$Registry_EvnNum = array();
		if ($registryIsUnion) {
			$resp = $this->dbmodel->queryResult("
				select top 1 Registry_EvnNum from {$this->scheme}.v_Registry (nolock) where Registry_id = :Registry_id
			", array(
				'Registry_id' => $data['Registry_id']
			));

			if (!empty($resp[0]['Registry_EvnNum'])) {
				$Registry_EvnNum = json_decode($resp[0]['Registry_EvnNum'], true);
			}
		}

		while ( !empty($xmlString) ) {
			// Нагребаем остатки, если размер оставшегося куска файла меньше $chunkSize МБ
			if (strlen($xmlString) <= $chunkSize + strlen($footer) + 2 /* учтем перевод строки */) {
				$xmlData = substr($xmlString, 0, strlen($xmlString) - strlen($footer));
				$xmlString = '';
			}
			// или данные по $chunkSize МБ
			else {
				$xmlData = substr($xmlString, 0, $chunkSize);
				$xmlString = substr($xmlString, $chunkSize);

				if (strpos($xmlString, '</ZAP>') !== false) {
					$xmlData .= substr($xmlString, 0, strpos($xmlString, '</ZAP>') + strlen('</ZAP>'));
					$xmlString = substr($xmlString, strpos($xmlString, '</ZAP>') + strlen('</ZAP>'));

					if (trim($xmlString) == $header) {
						$xmlString = '';
					}
				}
			}

			$this->textlog->add('Оставшийся размер файла: ' . strlen($xmlString));

			$xml = new SimpleXMLElement($header . $xmlData . $footer);

			foreach (libxml_get_errors() as $error) {
				if ($error->code != 100) { // Игнорирование ошибки xmlns: URI OMS-D1 is not absolute
					$this->ReturnError('Файл не является архивом реестра.', 100017);
					$this->textlog->add('Файл не является архивом реестра.');
					return false;
				}
			}

			if ($firstRead) {
				$dschet = $xml->SCHET->DSCHET->__toString();
				$nschet = $xml->SCHET->NSCHET->__toString();

				$data['dateMode'] = 120;
				$resp = $this->dbmodel->getRegistryNumberAndDate($data);

				if ($nschet != $resp[0]['Registry_Num']) {
					$this->ReturnError('Номер счета из файла не соответствует реестру.', 100019);
					$this->textlog->add('Номер счета из файла не соответствует реестру.');
					return false;
				}

				if ($dschet != $resp[0]['Registry_accDate']) {
					$this->ReturnError('Дата счета из файла не соответствует реестру.', 100020);
					$this->textlog->add('Дата счета из файла не соответствует реестру.');
					return false;
				}

				if (!$registryIsUnion) {
					// удаляем ошибки по всем записям
					$this->dbmodel->deleteRegistryErrorTFOMS($data);
					// удаляем записи по незастрахованным
					$this->dbmodel->deleteRegistryNoPolis($data);

					$this->textlog->add('Удалили ошибки и записи по незастрахованным');
				}

				$firstRead = false;
			}

			// Сохраняем сумму к оплате
			$summap = $xml->SCHET->SUMMAP->__toString();
			$this->dbmodel->updateRegistrySumPaid(array('Registry_id' => $data['Registry_id'], 'Registry_SumPaid' => $summap));

			foreach ($xml->ZAP as $onezap) {
				$Evn_rid = null;
				$Registry_id = null;
				$RegistryType_id = null;
				$recall++;

				$N_ZAP = $onezap->N_ZAP->__toString();
				$SUMV = $onezap->Z_SL->SUMV->__toString();

				if ( empty($SUMV) ) {
					$SUMV = 0;
				}

				$slErrList = array();

				foreach ($onezap->Z_SL->SL as $onesl) {
					$recordHasErrors = false;

					$SL_ID = $onesl->SL_ID->__toString();

					// Ищем случай в реестре
					$params = array();
					$params['SL_ID'] = $SL_ID;
					$params['Registry_id'] = $data['Registry_id'];
					$params['RegistryType_id'] = $data['RegistryType_id'];
					$params['Registry_EvnNum'] = $Registry_EvnNum;

					$check = $this->dbmodel->checkErrorDataInRegistry($params);

					if (!$check) {
						$this->ReturnError('Запись SL_ID=' . $SL_ID . ' обнаружена в импортируемом файле, но отсутствует в реестре, импорт не произведен');
						$this->textlog->add('Запись SL_ID=' . $SL_ID . ' обнаружена в импортируемом файле, но отсутствует в реестре, импорт не произведен');
						return false;
					}

					$Evn_rid = $check['Evn_rid'];
					$Registry_id = $check['Registry_id'];
					$RegistryType_id = $check['RegistryType_id'];

					if ($registryIsUnion === true && !in_array($check['Registry_id'], $simpleRegistryList)) {
						$simpleRegistryList[] = $check['Registry_id'];
					}

					$params = array();
					$params['pmUser_id'] = $data['pmUser_id'];
					$params['Registry_id'] = $check['Registry_id'];
					$params['RegistryType_id'] = $check['RegistryType_id'];
					$params['Evn_id'] = $check['Evn_id'];
					$params['TARIF'] = $onesl->TARIF->__toString();
					$params['SUM_M'] = $onesl->SUM_M->__toString();

					if (empty($params['TARIF'])) {
						$params['TARIF'] = 0;
					}

					if (empty($params['SUM_M'])) {
						$params['SUM_M'] = 0;
					}

					if ($registryIsUnion === false) {
						// Обновляем поля RegistryData.RegistryData_Tariff и RegistryData.RegistryData_ItogSum
						// @task https://redmine.swan.perm.ru/issues/92630
						if ($params['TARIF'] != $check['RegistryData_Tariff'] || $params['SUM_M'] != $check['RegistryData_ItogSum']) {
							$response = $this->dbmodel->setRegistryDataParams($params);

							if (!is_array($response)) {
								$this->ReturnError('Ошибка при обработке реестра!');
								$this->textlog->add('Ошибка при обработке реестра!');
								return false;
							} else if (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
								$this->ReturnError($response[0]['Error_Msg']);
								$this->textlog->add($response[0]['Error_Msg']);
								return false;
							}
						}
					}

					$params = array();
					$params['pmUser_id'] = $data['pmUser_id'];
					$params['Registry_id'] = $check['Registry_id'];
					$params['RegistryType_id'] = $check['RegistryType_id'];
					$params['Evn_id'] = $check['Evn_id'];

					$slErrList[$SL_ID] = array();

					foreach ($onezap->Z_SL->SANK as $onesank) {
						if ( property_exists($onesank, 'SL_ID') ) {
							$SANK_SL_ID = $onesank->SL_ID->__toString();
						}
						else {
							$SANK_SL_ID = null;
						}

						if ( !empty($SANK_SL_ID) && $SANK_SL_ID != $SL_ID ) {
							continue;
						}

						$S_COM = $onesank->S_COM->__toString();
						$S_OSN = $onesank->S_OSN->__toString();
						$S_TIP = $onesank->S_TIP->__toString();

						if ( in_array($S_TIP . '|| ' . $S_OSN . '||' . $S_COM, $slErrList[$SL_ID]) ) {
							continue;
						}

						$slErrList[$SL_ID][] = $S_TIP . '|| ' . $S_OSN . '||' . $S_COM;

						if ($recordHasErrors == false) {
							$recerr++;
							$recordHasErrors = true;
						}

						// Добавляем ошибку
						$params['S_CODE'] = $onesank->S_CODE->__toString();
						$params['S_COM'] = $onesank->S_COM->__toString();

						$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params);

						if (!is_array($response)) {
							$this->ReturnError('Ошибка при обработке реестра!');
							$this->textlog->add('Ошибка при обработке реестра!');
							return false;
						} else if (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
							$this->ReturnError($response[0]['Error_Msg']);
							$this->textlog->add($response[0]['Error_Msg']);
							return false;
						}

						// Добавляем данные в RegistryNoPolis
						// @task https://redmine.swan-it.ru/issues/122768
						// Добавил проверку наличия записи в RegistryNoPolis
						// @task https://redmine.swan-it.ru/issues/157658
						if ($params['S_CODE'] == '1902' && $this->dbmodel->checkRegistryNoPolis($params) === false) {
							$response = $this->dbmodel->setRegistryNoPolis($params);

							if (!is_array($response)) {
								$this->ReturnError('Ошибка при обработке реестра!');
								$this->textlog->add('Ошибка при обработке реестра!');
								return false;
							} else if (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
								$this->ReturnError($response[0]['Error_Msg']);
								$this->textlog->add($response[0]['Error_Msg']);
								return false;
							}
						}
					}

					if ($registryIsUnion && !$recordHasErrors) {
						// удаляем ошибки по конкретной записи
						$this->dbmodel->deleteRegistryErrorTFOMS($params);
					}

					if (empty($data['OmsSprTerr_id'])) {
						$data['OmsSprTerr_id'] = $this->dbmodel->getOmsSprTerr(array(
							'OmsSprTerr_Code' => 1 // Симферополь
						));
					}

					$polisParams = array();
					$polisParams['Polis_Num'] = $onezap->PACIENT->NPOLIS->__toString();
					$polisParams['Orgsmo_f002smocod'] = $onezap->PACIENT->SMO->__toString();
					$polisParams['Person_id'] = $check['Person_id'];
					$polisParams['Server_id'] = $data['Server_id'];
					$COMENTSL = $onesl->COMENTSL->__toString();
					if (!empty($COMENTSL)) { // если есть коммент, берём из него
						$comments = explode(';', $COMENTSL);
						foreach ($comments as $comment) {
							$comment = preg_replace('/\s+/', ' ', $comment);
							$mask = "/полис\s*(\d{9,16}) \((\d+)\) действует с (\d{2}\.\d{2}\.\d{4} \d{1,2}:\d{2}:\d{2}) по (\d{2}\.\d{2}\.\d{4} \d{1,2}:\d{2}:\d{2})?\)$/iu";

							if (preg_match($mask, $comment, $match)) {
								if (is_array($match) && (count($match) == 4 || count($match) == 5)) {
									$polisParams['Polis_Ser'] = '';
									$polisParams['Polis_Num'] = $match[1];
									//$polisParams['Orgsmo_f002smocod'] = $match[2];
									$polisParams['Polis_begDate'] = new DateTime($match[3]);
									$polisParams['Polis_endDate'] = (!empty($match[4]) ? new DateTime($match[4]) : null);
								}
							}
						}
					}

					$identifyOrgSMO = $this->dbmodel->identifyOrgSMO(array('Orgsmo_f002smocod' => $polisParams['Orgsmo_f002smocod']));
					$polisParams['OrgSMO_id'] = !empty($identifyOrgSMO) ? $identifyOrgSMO['OrgSMO_id'] : null;
					$polisParams['KLRgn_id'] = !empty($identifyOrgSMO) ? $identifyOrgSMO['KLRgn_id'] : null;

					if($polisParams['KLRgn_id'] != $check['KLRgn_id']){
						$polisParams['OmsSprTerr_id'] = $this->dbmodel->getOmsSprTerr(array(
							'KLRgn_id' => $polisParams['KLRgn_id']
						));
					}else{
						$polisParams['OmsSprTerr_id'] = $data['OmsSprTerr_id'];
					}

					$polisParams['pmUser_id'] = $data['pmUser_id'];

					// @task https://redmine.swan-it.ru/issues/125218
					if (empty($polisParams['OrgSMO_id'])) {
						// Если указан код СМО, отличный от хранящегося в Промеде, но при этом в Промеде нет СМО, с кодом который указал ТФОМС,
						// то СМО в периодике не изменяется, а к случаю добавляется предупреждение
						$params['RegistryErrorClass_id'] = 2;
						$params['IM_POL'] = 'SMO';
						$params['S_CODE'] = '150';
						$params['S_COM'] = "СМО с кодом {$polisParams['Orgsmo_f002smocod']}, указанным ТФОМС, отсутствует в системе";

						$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params);

						if (!is_array($response)) {
							$this->ReturnError('Ошибка при обработке реестра!');
							$this->textlog->add('Ошибка при обработке реестра!');
							return false;
						} else if (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
							$this->ReturnError($response[0]['Error_Msg']);
							$this->textlog->add($response[0]['Error_Msg']);
							return false;
						}
					}
					else if ($polisParams['Polis_Num'] != $check['Polis_Num'] && empty($COMENTSL)) {
						// Если указан номер полиса, отличный от хранящегося в Промеде, но при этом не заполнен тег COMENTSL,
						// то периодика не изменяется, а к случаю добавляется предупреждение
						$params['RegistryErrorClass_id'] = 2;
						$params['IM_POL'] = 'NPOLIS';
						$params['S_CODE'] = '151';
						$params['S_COM'] = "Номер полиса {$polisParams['Polis_Num']}, указанный ТФОМС, отличается от хранящегося в системе. Уточните полисные данные пациента";

						$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params);

						if (!is_array($response)) {
							$this->ReturnError('Ошибка при обработке реестра!');
							$this->textlog->add('Ошибка при обработке реестра!');
							return false;
						} else if (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
							$this->ReturnError($response[0]['Error_Msg']);
							$this->textlog->add($response[0]['Error_Msg']);
							return false;
						}
					}
					else if (
						(!empty($polisParams['KLRgn_id']) && $polisParams['KLRgn_id'] != $check['KLRgn_id'])
						|| (!empty($polisParams['OrgSMO_id']) && (empty($polisParams['Polis_endDate']) || $polisParams['Polis_endDate'] >= $polisParams['Polis_begDate']))
					) {
						$resp = $this->dbmodel->addNewPolisToPerson($polisParams);
					}

					if ($registryIsUnion === false) {
						foreach ($onesl->USL as $oneusl) {
							// Обновляем поля RegistryUsluga.RegistryUsluga_TARIF и RegistryUsluga.RegistryUsluga_SUMV
							// @task https://redmine.swan.perm.ru/issues/92630
							$params = array();
							$params['pmUser_id'] = $data['pmUser_id'];
							$params['Registry_id'] = $check['Registry_id'];
							$params['RegistryType_id'] = $check['RegistryType_id'];
							$params['EvnUsluga_id'] = $oneusl->IDSERV->__toString();
							$params['TARIF'] = $oneusl->TARIF->__toString();
							$params['SUMV'] = $oneusl->SUMV_USL->__toString();

							if (empty($params['TARIF'])) {
								$params['TARIF'] = 0;
							}

							if (empty($params['SUMV'])) {
								$params['SUMV'] = 0;
							}

							$response = $this->dbmodel->setRegistryUslugaParams($params);

							if (!is_array($response)) {
								$this->ReturnError('Ошибка при обработке реестра!');
								return false;
							} else if (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
								$this->ReturnError($response[0]['Error_Msg']);
								return false;
							}
						}
					}
				}

				if ($registryIsUnion === false && !empty($Evn_rid) && in_array($RegistryType_id, array(1, 14, 19))) {
					// Обновляем поле RegistryDataSL.RegistryData_ItogSum
					// @task https://redmine.swan.perm.ru/issues/135248
					$params = array();
					$params['pmUser_id'] = $data['pmUser_id'];
					$params['Registry_id'] = $Registry_id;
					$params['RegistryType_id'] = $RegistryType_id;
					$params['Evn_rid'] = $Evn_rid;
					$params['SUMV'] = $SUMV;


					$response = $this->dbmodel->setRegistryDataSLParams($params);

					if (!is_array($response)) {
						$this->ReturnError('Ошибка при обработке реестра!');
						$this->textlog->add('Ошибка при обработке реестра!');
						return false;
					} else if (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
						$this->ReturnError($response[0]['Error_Msg']);
						$this->textlog->add($response[0]['Error_Msg']);
						return false;
					}
				}
			}
		}
		$this->textlog->add('Закончили обработку реестра');

		if ( $registryIsUnion === true ) {
			// Проставляем _indexrep = 1 для случаев, входящих в объединенный реестр
			// Выполняем для каждого простого реестра, входящего в объединенный
			// @task https://redmine.swan.perm.ru/issues/104532
			// Убрал проставление _indexrep = 1
			// @task https://redmine.swan.perm.ru/issues/113626
			/*foreach ( $simpleRegistryList as $Registry_id ) {
				$response = $this->dbmodel->afterImportRegistryFromTFOMS(array(
					'Registry_id' => $Registry_id,
					'pmUser_id' => $data['pmUser_id']
				));

				if ( !is_array($response) ) {
					$this->ReturnError('Ошибка при обработке реестра!');
					return false;
				}
				else if ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
					$this->ReturnError($response[0]['Error_Msg']);
					return false;
				}
			}*/
		}
		else {
			$res = $this->dbmodel->recountRegistrySum($data);

			if ( !is_array($res) || count($res) == 0 ) {
				$this->ReturnError('Ошибка при пересчете суммы реестра');
				$this->textlog->add('Ошибка при пересчете суммы реестра');
				return false;
			}
			else if ( !empty($res[0]['Error_Msg']) ) {
				$this->ReturnError($res[0]['Error_Msg']);
				$this->textlog->add($res[0]['Error_Msg']);
				return false;
			}

			$res = $this->dbmodel->setRegistryIsLoadTFOMS($data);

			if ( !is_array($res) || count($res) == 0 ) {
				$this->ReturnError('Ошибка при установке признака загрузки ответа ТФОМС');
				$this->textlog->add('Ошибка при установке признака загрузки ответа ТФОМС');
				return false;
			}
			else if ( !empty($res[0]['Error_Msg']) ) {
				$this->ReturnError($res[0]['Error_Msg']);
				$this->textlog->add($res[0]['Error_Msg']);
				return false;
			}
		}

		$this->textlog->add('Пишем информацию об импорте в историю');

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->textlog->add('Закончили импорт');

		$response = array('success' => true, 'Registry_id' => $data['Registry_id'], 'recErr' => $recerr, 'recAll' => $recall, 'errorlink' => '', 'Message' => 'Реестр успешно загружен.');

		$this->ReturnData($response);

		return true;
	}

	/**
	 * Получение списка данных объединённого реестра
	 */
	function loadUnionRegistryData() {
		$data = $this->ProcessInputData('loadUnionRegistryData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryData($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Функция возвращает ошибки данных реестра по версии ТФОМС
	 */
	function loadUnionRegistryErrorTFOMS() {
		$data = $this->ProcessInputData('loadUnionRegistryErrorTFOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryErrorTFOMS($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Импорт ФЛК
	 */
	public function importRegistryFromXml() {
		$upload_path = './' . IMPORTPATH_ROOT . 'importRegistryFromXml/' . $_SESSION['lpu_id'] . '/';
		$allowed_types = explode('|','zip|xml');
		
		$data = $this->ProcessInputData('importRegistryFromXml', true);
		if ($data === false) { return false; }

		if (! isset($_FILES['RegistryFile']) ) {
			$this->ReturnError('Не выбран файл реестра!', __LINE__) ;
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
		$file_data['file_ext'] = strtolower(end($x));

		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			$this->ReturnError('Данный тип файла не разрешен.', __LINE__);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		$path = '';
		$folders = explode('/', $upload_path);

		for ( $i = 0; $i < count($folders); $i++ ) {
			if ( $folders[$i] == '' ) {
				continue;
			}

			$path .= $folders[$i] . '/';

			if (!@is_dir($path)) {
				mkdir( $path );
			}
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

		if ( $file_data['file_ext'] == 'xml' ) {
			$xmlfile = $_FILES['RegistryFile']['name'];

			if ( !move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile) ) {
				$this->ReturnError('Не удаётся переместить файл.', __LINE__);
				return false;
			}
		}
		else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive();

			if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
				$xmlfile = "";

				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$filename = $zip->getNameIndex($i);

					if ( preg_match('/.*.xml/i', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}

			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}

		if ( empty($xmlfile) ) {
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}
		
		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistry($data);

		if ( !is_array($registrydata) || !isset($registrydata[0]) ) {
			$this->ReturnError('Ошибка чтения данных реестра', __LINE__);
			return false;
		}

		$registrydata = $registrydata[0];

		$data['RegistryType_id'] = $registrydata['RegistryType_id'];
		$data['RegistryErrorStageType_id'] = 1;
		
		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recall = 0;
		
		libxml_use_internal_errors(true);
		
		$xml = new SimpleXMLElement(file_get_contents($upload_path . $xmlfile));
		
		foreach ( libxml_get_errors() as $error ) {
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		libxml_clear_errors();

		if ( !property_exists($xml, 'FNAME_I') ) {
			if ( !property_exists($xml, 'PR') ) {
				$this->ReturnError('Файл не является архивом реестра.', __LINE__);
				return false;
			}

			$errorMessage = 'Передана ошибка всего реестра';

			if ( property_exists($xml->PR, 'OSHIB') ) {
				$errorMessage .= ' ' . $xml->PR->OSHIB->__toString();
			}

			if ( property_exists($xml->PR, 'COMMENT') ) {
				$errorMessage .= ' ' . $xml->PR->COMMENT->__toString();
			}

			$this->ReturnError($errorMessage, __LINE__);
			return false;
		}

		$FNAME_I = $xml->FNAME_I->__toString();

		$export_file_name_array = explode('/', $registrydata['Registry_xmlExportPath']);
		$export_file = array_pop($export_file_name_array);


		if ( $export_file != $FNAME_I . '.zip' ) {
			$this->ReturnError('Не совпадает название файла экпорта, импорт не произведен.', __LINE__);
			return false;
		}

		$Registry_EvnNum = array();

		$resp = $this->dbmodel->getFirstResultFromQuery("
			select top 1 Registry_EvnNum from {$this->scheme}.v_Registry (nolock) where Registry_id = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (!empty($resp)) {
			$Registry_EvnNum = json_decode($resp, true);
		}

		if ( !is_array($Registry_EvnNum) || count($Registry_EvnNum) == 0 ) {
			$this->ReturnError('Ошибка при получении связок идентификаторов.', __LINE__);
			return false;
		}

		$this->dbmodel->setRegistryEvnNumByNZAP($Registry_EvnNum);
		unset($Registry_EvnNum);

		try {
			foreach ( $xml->PR as $onepr ) {
				$recall++;

				$params = array();

				$params['OSHIB'] = property_exists($onepr, 'OSHIB') ? $onepr->OSHIB->__toString() : null;
				$params['IM_POL'] = property_exists($onepr, 'IM_POL') ? $onepr->IM_POL->__toString() : null;
				$params['BAS_EL'] = property_exists($onepr, 'BAS_EL') ? $onepr->BAS_EL->__toString() : null;
				$params['N_ZAP'] = property_exists($onepr, 'N_ZAP') ? $onepr->N_ZAP->__toString() : null;
				$params['IDCASE'] = property_exists($onepr, 'IDCASE') ? $onepr->IDCASE->__toString() : null;
				$params['SL_ID'] = property_exists($onepr, 'SL_ID') ? $onepr->SL_ID->__toString() : null;
				$params['IDSERV'] = property_exists($onepr, 'IDSERV') ? $onepr->IDSERV->__toString() : null;
				$params['COMMENT'] = property_exists($onepr, 'COMMENT') ? $onepr->COMMENT->__toString() : null;

				if ( empty($params['N_ZAP']) && !empty($params['IDCASE']) ) {
					$params['N_ZAP'] = $params['IDCASE'];
				}

				$EvnDataArray = array();

				$N_ZAP_data = $this->dbmodel->getRegistryEvnNumByNZAP($params['N_ZAP']);

				if ( $N_ZAP_data === false ) {
					throw new Exception('Номер записи N_ZAP = "' . $params['N_ZAP'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен', __LINE__);
				}

				foreach ( $N_ZAP_data as $row ) {
					if ( !empty($params['SL_ID']) && $row['s'] != $params['SL_ID'] ) {
						continue;
					}

					$EvnDataArray[] = $row;
				}

				foreach ( $EvnDataArray as $EvnData ) {
					$checkEvn = $this->dbmodel->checkErrorDataInRegistry(array(
						'Registry_id' => $data['Registry_id'],
						'RegistryType_id' => $data['RegistryType_id'],
						'SL_ID' => $EvnData['s'],
						'Evn_id' => $EvnData['e'],
					));

					if ( $checkEvn === false ) {
						throw new Exception('Номер записи SL_ID = "' . $EvnData['s'] . '" отсутствует в реестре, импорт не произведен', __LINE__);
					}

					$params['S_CODE'] = $params['OSHIB'];
					$params['S_COM'] = $params['COMMENT'];
					$params['SL_ID'] = $EvnData['s'];
					$params['Evn_id'] = $EvnData['e'];
					$params['Registry_id'] = $checkEvn['Registry_id'];
					$params['RegistryType_id'] = $checkEvn['RegistryType_id'];
					$params['pmUser_id'] = $data['pmUser_id'];
						
					$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params);

					if ( !is_array($response) ) {
						throw new Exception('Ошибка при обработке реестра!', __LINE__);
					}
					else if ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
						throw new Exception($response[0]['Error_Msg'], __LINE__);
					}
				}

				if ( count($N_ZAP_data) > 0 ) {
					$this->dbmodel->setRegistryDataZSLIsPaid(array(
						'Registry_id' => $N_ZAP_data[0]['r'],
						'Evn_id' => $N_ZAP_data[0]['e'],
						'RegistryData_IsPaid' => 1,
					));
				}
			}
			
			// Пишем информацию об импорте в историю
			$this->dbmodel->dumpRegistryInformation($data, 3);

			$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll' => $recall, 'Message' => 'Реестр успешно загружен.'));
		}
		catch ( Exception $e ) {
			$this->dbmodel->deleteRegistryErrorTFOMS($data);
			$this->ReturnError($e->getMessage(), $e->getCode());
		}

		return true;
	}
}
