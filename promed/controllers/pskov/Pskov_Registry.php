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
require(APPPATH.'controllers/Registry.php');
class Pskov_Registry extends Registry {
	var $scheme = "r60";
	// Вынес префиксы для файлов с данными в массив, чтобы было удобнее проверять имена файлов при импорте ответа из ТФОМС
	// @task https://redmine.swan.perm.ru/issues/78076
	private $_xmlDataFilePrefixesOMS = array(
		1 => "H", // Стационар, поликлиника, СМП
		2 => "T", // ВМП
		3 => "DP", // Дисп-ция взр. населения 1-ый этап
		4 => "DV", // Дисп-ция взр. населения 2-ый этап
		5 => "DS", // Дисп-ция детей-сирот стационарных 1-ый этап
		6 => "DU", // Дисп-ция детей-сирот усыновленных 1-ый этап
		7 => "DR", // Периодические осмотры несовершеннолетних
		8 => "DD", // Предварительные осмотры несовершеннолетних
		9 => "DF", // Профилактические осмотры несовершеннолетних
		10 => "DO", // Профилактические осмотры взрослого населения
		11 => "D", // ДВН, ДДС, ПОВН, МОН
	);
	private $_xmlPersonDataFilePrefixesOMS = array(
		1 => "L", // Стационар, поликлиника, СМП
		2 => "LT", // ВМП
		3 => "LP", // Дисп-ция взр. населения 1-ый этап
		4 => "LV", // Дисп-ция взр. населения 2-ый этап
		5 => "LS", // Дисп-ция детей-сирот стационарных 1-ый этап
		6 => "LU", // Дисп-ция детей-сирот усыновленных 1-ый этап
		7 => "LR", // Периодические осмотры несовершеннолетних
		8 => "LD", // Предварительные осмотры несовершеннолетних
		9 => "LF", // Профилактические осмотры несовершеннолетних
		10 => "LO", // Профилактические осмотры взрослого населения
		11 => "L", // ДВН, ДДС, ПОВН, МОН
	);
	private $_xmlDataFilePrefixesBUD = array(
		1 => "S", // Стационар
		2 => "P", // Поликлиника
		6 => "", // СМП
		14 => "V", // ВМП
		15 => "", // Параклиника
	);

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
		// Инициализация класса и настройки

		$this->inputRules['setRegistryCheckStatus'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryCheckStatus_SysNick', 'label' => 'Сис. ник статуса', 'rules' => '', 'type' => 'string'),
			array('field' => 'RegistryCheckStatus_id', 'label' => 'Идентификатор статуса', 'rules' => '', 'type' => 'id')
		);
		
		$this->inputRules['saveRegistry'] = array(
			array(
				'default' => null,
				'field' => 'DispClass_id',
				'label' => 'Тип дисп-ции/медосмотра:',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'id'
			),
			/*array(
				'field' => 'LpuUnitSet_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),*/
			array(
				'default' => null,
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
				'type' => 'int'
			),
			array(
				'field' => 'Registry_rid',
				'label' => 'Первичный реестр',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			)
		);
		$this->inputRules['deleteRegistry'] = array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			)
		);
		
		$this->inputRules['importRegistryFromDbf'] = array(
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
		
		$this->inputRules['importRegistryFromXml'] = array(
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
		
		$this->inputRules['importRegistrySmoDataFromDbf'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		);
		
		$this->inputRules['printRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);
		
		$this->inputRules['exportRegistryErrorDataToDbf'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Идентификатор типа реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);
		
		$this->inputRules['exportRegistryToDbf'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'send',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'int'
			)
		);
		
		$this->inputRules['exportRegistryToXml'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryRecipient_id',
				'label' => 'Получатель',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OverrideExportOneMoreOrUseExist',
				'label' => 'Флаг использования существующего или экспорта нового XML',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'send',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'forSign',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'int'
			)
		);
		
		$this->inputRules['exportRegistryToXmlCheckVolume'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
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
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryGroupType_id',
				'label' => 'Тип объединенного реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Лпу',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsRepeated',
				'label' => 'Повторная подача',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			)
		);
		$this->inputRules['getRegistryPrimaryCombo'] = array(
			array(
				'default' => null,
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Лпу',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Идентификатор типа реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => '',
				'type' => 'string'
			)
		);
	}

	/**
	 * Удаление объединённого реестра
	 */
	function deleteUnionRegistry()
	{
		$data = $this->ProcessInputData('deleteUnionRegistry', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->deleteUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();	
	}

	/**
	 * Простановка статуса реестра
	 */
	function setRegistryCheckStatus()  {
		$data = $this->ProcessInputData('setRegistryCheckStatus', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->setRegistryCheckStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();

		return true;
	}
	
	/**
	 * Сохранение объединённого реестра
	 */
	function saveUnionRegistry()
	{
		$data = $this->ProcessInputData('saveUnionRegistry', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}
	
	/**
	 * Получение номера объединённого реестра
	 */
	function getUnionRegistryNumber()
	{
		$data = $this->ProcessInputData('getUnionRegistryNumber', true);
		if ($data === false) { return false; }
		
		$Registry_Num = $this->dbmodel->getUnionRegistryNumber($data);
		$this->ReturnData(array(
			'UnionRegistryNumber' => $Registry_Num
		));
	}
	
	/**
	 * Загрузка списка объединённых реестров
	 */
	function loadUnionRegistryGrid()
	{
		$data = $this->ProcessInputData('loadUnionRegistryGrid', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadUnionRegistryGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка формы редактирования объединённого реестра
	 */
	function loadUnionRegistryEditForm()
	{
		$data = $this->ProcessInputData('loadUnionRegistryEditForm', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadUnionRegistryEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	function loadUnionRegistryChildGrid()
	{
		$data = $this->ProcessInputData('loadUnionRegistryChildGrid', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadUnionRegistryChildGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 * Функция возвращает данные для дерева реестров в json-формате
	 */
	function loadRegistryTree()
	{
		/**
		 *	Получение ветки дерева реестров
		 */
		function getRegistryTreeChild($childrens, $field, $lvl, $node_id = "")
		{
			$val = array();
			$i = 0;
			if (!empty($node_id))
			{
				$node_id = "/".$node_id;
			}
			if ( $childrens != false && count($childrens) > 0 )
			{
				foreach ($childrens as $rows)
				{
					$node = array(
						'text'=>toUTF(trim($rows[$field['name']])),
						'id'=>$field['object'].".".$lvl.".".$rows[$field['id']].$node_id,
						//'new'=>$rows['New'],
						'object'=>$field['object'],
						'object_id'=>$field['id'],
						'object_value'=>$rows[$field['id']],
						'leaf'=>$field['leaf'],
						'iconCls'=>$field['iconCls'],
						'cls'=>$field['cls']
						);
					//$val[] = array_merge($node,$lrt,$lst);
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
		if ((!isset($data['level'])) || (!is_numeric($data['level'])))
		{
			$val = array();//gabdushev: $val не определена в этом scope, добавил определение чтобы не было ворнинга, не проверял.
            $this->ReturnData($val);
			return;
		}

		$node = "";
		if (isset($data['node']))
		{
			$node = $data['node'];
		}

		if (mb_strpos($node, 'PayType.1.bud') !== false) {
			if ($data['level'] >= 2) {
				$data['level']++; // для бюджета нет объединённых реестров
			}
			$data['PayType_SysNick'] = 'bud';
		}

		$response = array();

		Switch ($data['level'])
		{
			case 0: // Уровень Root. ЛПУ
			{
				$this->load->model("LpuStructure_model", "lsmodel");
				$childrens = $this->lsmodel->GetLpuNodeList($data);

				$field = Array('object' => "Lpu",'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}

			case 1: // Уровень 1. ОМС или бюджет
			{
				$childrens = array(
					array('PayType_SysNick' => 'oms', 'PayType_Name' => 'ОМС'),
					array('PayType_SysNick' => 'bud', 'PayType_Name' => 'Местный и федеральный бюджет')
				);
				$field = Array('object' => "PayType", 'id' => "PayType_SysNick", 'name' => "PayType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}

			case 2: // Уровень 2. Объединённые реестры
			{
				$childrens = array(
					array('RegistryType_id' => 13, 'RegistryType_Name' => 'Объединённые реестры'),
				);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 3: // Уровень 3. Типочки
			{
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
			}
			case 4: // Уровень 4. Статусы реестров
			{
				$childrens = $this->dbmodel->loadRegistryStatusNode($data);
				$field = Array('object' => "RegistryStatus",'id' => "RegistryStatus_id", 'name' => "RegistryStatus_Name", 'iconCls' => 'regstatus-16', 'leaf' => true, 'cls' => "file");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
				break;
			}
		}
		if ( count($c_two)>0 )
		{
			$c_one = array_merge($c_one,$c_two);
		}

		$this->ReturnData($c_one);
	}

	/**
	 *	Отправка сообщения по почте
	 */
	function sendMail($file, $data)
	{
		// Невозможно
		return false;
	}
	
	/**
	 * функция для выгрузки данных реестра для сверки
	 */
	function exportRegistryErrorDataToDbf() {
		$data = $this->ProcessInputData('exportRegistryErrorDataToDbf', true);
		if ( $data === false ) { return false; }
		
		try {
			// данные о пациенте из реестра по ошибке: "Страховая организация указана не верно"
			$registry_person = $this->dbmodel->loadPersonInfoFromErrorRegistry($data);

			if ( $registry_person === false ) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
				return false;
			}

			if ( !is_array($registry_person) || count($registry_person['data']) == 0 ) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибок страховой по этому реестру нет в базе данных.')));
				return false;
			}

			// Формируем массив перс. данных с индексами, равными идентификатору застрахованного
			$personData = array();

			foreach ( $registry_person['data'] as $array ) {
				$personData[$array['ID']] = $array;
			}

			$lpu_code = $registry_person['lpu_code'];

			unset($registry_person);

			switch ( $data['RegistryType_id'] ) {
				// данные по ошибочным движениям в стационаре
				case 1:
					$evnData = $this->dbmodel->loadEvnSectionErrorData($data);

					if ( $evnData === false ) {
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
						return false;
					}

					if ( !is_array($evnData) || count($evnData) == 0 ) {
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данных по движениям в стационаре с ошибками нет в базе данных.')));
						return false;
					}
				break;

				// данные по ошибочным посещениям
				case 2:
					$evnData = $this->dbmodel->loadEvnVizitErrorData($data);

					if ( $evnData === false ) {
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
						return false;
					}

					if ( !is_array($evnData) || count($evnData) == 0 ) {
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данных по посещениям с ошибками нет в базе данных.')));
						return false;
					}
				break;
			}
			
			$person_def = array(
				array( "ID",		"C",	36, 0 ),
				array( "FAM",		"C",	25, 0 ),
				array( "NAM",		"C",	25, 0 ),
				array( "FNAM",		"C",	25, 0 ),
				array( "DATE_BORN",	"D",	 8, 0 ),
				array( "SEX",		"C",	 1, 0 ),
				array( "DOC_TYPE",	"C",	 2, 0 ),
				array( "DOC_SER",	"C",	10, 0 ),
				array( "DOC_NUM",	"C",	16, 0 ),
				array( "INN",		"C",	12, 0 ),
				array( "KLADR",		"C",	19, 0 ),
				array( "HOUSE",		"C",	 5, 0 ),
				array( "ROOM",		"C",	 5, 0 ),
				array( "SMO",		"N",	 3, 0 ),
				array( "POL_NUM",	"C",	16, 0 ),
				array( "STATUS",	"N",	 2, 0 )
			);
			
			$evn_data_def = array(
				array( "ID_POS",	"C",	36, 0 ),
				array( "ID",		"C",	36, 0 ),
				array( "DATE_POS",	"D",	 8, 0 ),
				array( "SMO",		"C",	 3, 0 ),
				array( "POL_NUM",	"C",	16, 0 ),
				array( "ID_STATUS",	"C",	 2, 0 ),
				array( "NAM",		"C",	25, 0 ),
				array( "FNAM",		"C",	25, 0 ),
				array( "DATE_BORN",	"D",	 8, 0 ),
				array( "SEX",		"C",	 1, 0 ),
				array( "SNILS",		"C",	14, 0 ),
				array( "DATE_SV",	"D",	 8, 0 ),
				array( "FLAG",		"C",	 1, 0 )
			);
			
			// каталог в котором лежат выгружаемые файлы
			$out_dir = "reerd_" . time() . "_" . $data['Registry_id'];
			mkdir(EXPORTPATH_REGISTRY . $out_dir);
			
			// данные о пациенте
			$file_reerd_sign = "patient";
			$file_reerd_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_reerd_sign . ".dbf";
			
			// данные о посещении
			$file_reerd_vizit_sign = "visit";
			$file_reerd_vizit_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_reerd_vizit_sign . ".dbf";

			// Начало цикла для формирования файлов
			$i = 0;
			$linkArray = array();
			$recordsLimit = 10000;

			$file_zip_sign = "Z" . $lpu_code;
			
			foreach ( $evnData as $row ) {
				if ( $i % $recordsLimit == 0 ) {
					// Если это не первый проход по циклу, то закрываем ссылки на dbf-файлы и формируем архив
					if ( $i > 0 ) {
						if ( !empty($evnDBF) ) {
							dbase_close($evnDBF);
						}

						if ( !empty($persDBF) ) {
							dbase_close($persDBF);
						}

						$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . "_" . (floor($i / $recordsLimit)) . ".zip";

						if ( file_exists($file_zip_name) ) {
							unlink($file_zip_name);
						}

						$zip = new ZipArchive();
						$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
						$zip->AddFile($file_reerd_name, $file_reerd_sign . ".dbf");
						$zip->AddFile($file_reerd_vizit_name, $file_reerd_vizit_sign . ".dbf");
						$zip->close();
						
						unlink($file_reerd_name);
						unlink($file_reerd_vizit_name);
						
						if ( file_exists($file_zip_name) ) {
							$linkArray[] = $file_zip_name;
						}
						else {
							throw new Exception('Ошибка создания архива');
						}
					}

					// Создаем новые файлы dbf
					$evnDBF = dbase_create($file_reerd_vizit_name, $evn_data_def);
					$persDBF = dbase_create($file_reerd_name, $person_def);

					// Список людей для отдельного visit.dbf
					$personArray = array();
				}

				$i++;

				foreach ( $evn_data_def as $descr ) {
					if ( $descr[1] == "C" ) {
						$row[$descr[0]] = str_replace('«', '"', $row[$descr[0]]);
						$row[$descr[0]] = str_replace('»', '"', $row[$descr[0]]);
					}
					else if ( $descr[1] == "D" ) {
						if ( !empty($row[$descr[0]]) ) {
							if ( $row[$descr[0]] == '31.12.9999' ) {
								$row[$descr[0]] = '99991231';
							}
						}
					}
				}

				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record($evnDBF, array_values($row));

				// Добавляем соответствующего пациента в patient.dbf
				if ( !in_array($personData[$row['ID']], $personArray) ) {
					$personArray[] = $personData[$row['ID']];

					$personRow = $personData[$row['ID']];

					if ( is_array($personRow) && count($personRow) > 0 ) {
						foreach ( $person_def as $descr ) {
							if ( $descr[1] == "C" ) {
								$personRow[$descr[0]] = str_replace('«', '"', $personRow[$descr[0]]);
								$personRow[$descr[0]] = str_replace('»', '"', $personRow[$descr[0]]);
							}
							else if ( $descr[1] == "D" ) {
								if ( !empty($personRow[$descr[0]]) ) {
									if ( $personRow[$descr[0]] == '31.12.9999' ) {
										$personRow[$descr[0]] = '99991231';
									}
								}
							}
						}
						
						array_walk($personRow, 'ConvertFromUtf8ToCp866');
						dbase_add_record($persDBF, array_values($personRow));
					}
				}
			}

			// Если после выхода из общего цикла последний zip-архив не сформирован, то формируем его
			if ( ($i > 0) && ($i % $recordsLimit != 0) ) {
				if ( !empty($evnDBF) ) {
					dbase_close($evnDBF);
				}

				if ( !empty($persDBF) ) {
					dbase_close($persDBF);
				}

				// Если это не первый проход по циклу, то формируем архив
				$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . "_" . (floor($i / $recordsLimit) + 1) . ".zip";

				if ( file_exists($file_zip_name) ) {
					unlink($file_zip_name);
				}

				$zip = new ZipArchive();
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile($file_reerd_name, $file_reerd_sign . ".dbf");
				$zip->AddFile($file_reerd_vizit_name, $file_reerd_vizit_sign . ".dbf");
				$zip->close();
					
				unlink($file_reerd_name);
				unlink($file_reerd_vizit_name);
					
				if ( file_exists($file_zip_name) ) {
					$linkArray[] = $file_zip_name;
				}
				else {
					throw new Exception('Ошибка создания архива');
				}
			}

			if ( count($linkArray) > 0 ) {
				$this->ReturnData(array('success' => true, 'Link' => $linkArray));
			}
			else {
				throw new Exception('Не создано ни одного файла');
			}
			/*
			// Старый вариант с одним файлом
			$h = dbase_create( $file_reerd_name, $person_def );
			foreach ($registry_person['data'] as $row)
			{
				// определяем которые даты и конвертируем их					
				foreach ($person_def as $descr)
				{
					if ( $descr[1] == "C" ) {
						$row[$descr[0]] = str_replace('«', '"', $row[$descr[0]]);
						$row[$descr[0]] = str_replace('»', '"', $row[$descr[0]]);
					}
					else if ( $descr[1] == "D" )
						if (!empty($row[$descr[0]]))
						{
							if ( $row[$descr[0]] == '31.12.9999' ) {
								$row[$descr[0]] = '99991231';
							}
							else {
								$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
							}
						}
				}
				
				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record( $h, array_values($row) );
			}
			dbase_close ($h);
			
			$h = dbase_create( $file_reerd_vizit_name, $evn_data_def );
			foreach ($evn_data as $row)
			{
				// определяем которые даты и конвертируем их
				foreach ($evn_data_def as $descr)
				{
					if ( $descr[1] == "C" ) {
						$row[$descr[0]] = str_replace('«', '"', $row[$descr[0]]);
						$row[$descr[0]] = str_replace('»', '"', $row[$descr[0]]);
					}
					else if ( $descr[1] == "D" )
					{
						if (!empty($row[$descr[0]]))
						{
							if ( $row[$descr[0]] == '31.12.9999' ) {
								$row[$descr[0]] = '99991231';
							}
							else {
								$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
							}
						}
					}
				}
			
				array_walk($row, 'ConvertFromUtf8ToCp866');
				dbase_add_record( $h, array_values($row) );
			}
			dbase_close ($h);
			
			$base_name = $_SERVER["DOCUMENT_ROOT"]."/";
			
			$file_zip_sign = "Z".$registry_person['lpu_code'];
			$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
			
			$zip=new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_reerd_name, $file_reerd_sign.".dbf" );
			$zip->AddFile( $file_reerd_vizit_name, $file_reerd_vizit_sign.".dbf" );
			$zip->close();
			
			unlink($file_reerd_name);
			unlink($file_reerd_vizit_name);
			
			if (file_exists($file_zip_name))
			{
				$link = $file_zip_name;
				echo "{'success':true,'Link':'$link'}";				
			}
			else{
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка создания архива!')));
			}
			*/
		}
		catch (Exception $e)
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($e->getMessage())));
			return false;
		}
	}
	
	/**
	 * некоторые символы в файлах формата XML кодируются по особому (refs #8013)
	 */
	function encodeForXmlExport(&$word) 
	{
		$word = str_replace('&','&amp;amp;',$word);
		$word = str_replace('"','&amp;quot;',$word);
		$word = str_replace('\'','&amp;apos;',$word);
		$word = str_replace('<','&amp;lt;',$word);
		$word = str_replace('>','&amp;gt;',$word);
		$word = str_replace('&amp;lt;CODE&amp;gt;3&amp;lt;/CODE&amp;gt;','<CODE>3</CODE>',$word); // костыль для #12078
	}
	
	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	public function exportRegistryToXml() {
		ignore_user_abort(true);
		set_time_limit(60 * 60); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится; upd: 1 час, чтобы при включенном ignore_user_abort скрипт не выполнялся вечно

		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }

		if ( empty($data['Registry_id']) ) {
			$this->ReturnError('Ошибка. Неверно задан идентификатор счета!');
			return false;
		}
		
		$this->load->library('textlog', array('file'=>'exportRegistryToXml.log'));
		$this->textlog->add('');
		$this->textlog->add('exportRegistryToXml: Запуск формирования реестра (Registry_id='.$data['Registry_id'].')');
		
		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		$res = $this->dbmodel->GetRegistryXmlExport($data);
		
		if ( !is_array($res) || count($res) == 0 ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return false;
		}

		$data['KatNasel_SysNick'] = $res[0]['KatNasel_SysNick'];
		$data['PayType_id'] = $res[0]['PayType_id'];
		$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];
		if (empty($data['PayType_SysNick'])) {
			// если вид оплаты не сохранён, значит по ОМС
			$data['PayType_SysNick'] = 'oms';
		}
		$data['Registry_IsZNO'] = $res[0]['Registry_IsZNO'];
		$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
		$data['RegistryGroupType_id'] = $res[0]['RegistryGroupType_id'];
		$data['RegistryType_id'] = $res[0]['RegistryType_id'];
		$data['RegistryIsAfter20180901'] = ($res[0]['Registry_begDate'] >= '2018-09-01');

		if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			$this->ReturnError('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return false;
		}

		if ( !empty($res[0]['Registry_xmlExportPath']) && 1 == $data['OverrideExportOneMoreOrUseExist'] ) { // если уже выгружен реестр
			if (!empty($data['forSign'])) {
				$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
				$filebase64 = base64_encode(file_get_contents($res[0]['Registry_xmlExportPath']));
				$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
				return true;
			}
			$link = $res[0]['Registry_xmlExportPath'];
			$this->textlog->add('exportRegistryToXml: вернули ссылку '.$link);
			$respdata = array(
				'success' => true,
				'Link' => $link
			);

			if (!empty($res[0]['Registry_xmlExpPathErr'])) {
				$respdata['ErrorLink'] = $res[0]['Registry_xmlExpPathErr'];
			}

			$this->ReturnData($respdata);

			return true;
		}

		if ( $data['KatNasel_SysNick'] == 'oblast' && empty($data['RegistryRecipient_id']) ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Не указан получатель реестра.');
			$this->ReturnError('Не указан получатель реестра.');
			return false;
		}

		$this->textlog->add('exportRegistryToXml: Тип реестра ' . $res[0]['RegistryType_id']);
		$this->textlog->add('exportRegistryToXml: Вид оплаты ' . $res[0]['PayType_SysNick']);
		
		// Формирование XML в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			$data['Status'] = '1';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add('SetXmlExportStatus: Установили статус реестра в 1');

			// Объединенные реестры могут содержать данные любого типа, получаем список типов реестров, входящих в объединенный реестр
			if ( $data['PayType_SysNick'] == 'oms' ) {
				$registrytypes = $this->dbmodel->getUnionRegistryTypes($data['Registry_id']);

				if ( !is_array($registrytypes) || count($registrytypes) == 0 ) {
					throw new Exception('При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
				}
			}
			else {
				$registrytypes = array($data['RegistryType_id']);
			}

			$packNum = ($res[0]['Registry_IsRepeated'] == 2) ? 2 : 1;

			if ( $data['PayType_SysNick'] == 'oms' && !array_key_exists($res[0]['RegistryGroupType_id'], $this->_xmlDataFilePrefixesOMS) ) {
				throw new Exception('Недопустимый тип объединенного реестра!');
			}

			$Registry_EvnNum = array();
			$SCHET = $this->dbmodel->loadRegistrySCHETForXmlUsing($data);

			array_walk_recursive($SCHET[0], 'ConvertFromUTF8ToWin1251', true);

			switch ( $data['PayType_SysNick'] ) {
				case 'oms':
					$first_code = ($res[0]['RegistryGroupType_id'] == 1 && $data['Registry_IsZNO'] == 2) ? "C" : $this->_xmlDataFilePrefixesOMS[$res[0]['RegistryGroupType_id']];
					$pfirst_code = ($res[0]['RegistryGroupType_id'] == 1 && $data['Registry_IsZNO'] == 2) ? "LC" : $this->_xmlPersonDataFilePrefixesOMS[$res[0]['RegistryGroupType_id']];
					break;

				case 'bud':
					$first_code = 'L' . $this->_xmlDataFilePrefixesBUD[$res[0]['RegistryType_id']] . 'H';
					$pfirst_code = 'L' . $this->_xmlDataFilePrefixesBUD[$res[0]['RegistryType_id']] . 'L';
					break;

				case 'fbud':
					$first_code = 'F' . $this->_xmlDataFilePrefixesBUD[$res[0]['RegistryType_id']] . 'H';
					$pfirst_code = 'F' . $this->_xmlDataFilePrefixesBUD[$res[0]['RegistryType_id']] . 'L';
					break;

				default:
					throw new Exception('Недопустимый вид оплаты реестра!');
					break;
			}

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];
			mkdir(EXPORTPATH_REGISTRY . $out_dir);

			switch ( $data['PayType_SysNick'] ) {
				case 'oms':
					if ( 'oblast' == $data['KatNasel_SysNick'] && $data['RegistryRecipient_id'] == 'S' ) {
						$rname = $first_code . "M" . $SCHET[0]['CODE_MO'] . 'S60001_' . $data['Registry_endMonth'] . $packNum;
						$pname = $pfirst_code . "M" . $SCHET[0]['CODE_MO'] . 'S60001_' . $data['Registry_endMonth'] . $packNum;
						$zname = $first_code . "M" . $SCHET[0]['CODE_MO'] . 'S60001_' . $data['Registry_endMonth'] . $packNum;
					}
					else {
						$rname = $first_code . "M" . $SCHET[0]['CODE_MO'] . 'T' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
						$pname = $pfirst_code . "M" . $SCHET[0]['CODE_MO'] . 'T' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
						$zname = $first_code . "M" . $SCHET[0]['CODE_MO'] . 'T' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;

						if ( 'oblast' == $data['KatNasel_SysNick'] ) {
							$zname = "I" . $zname;
						}
					}
					break;

				case 'bud':
				case 'fbud':
					$rname = $first_code . "M" . $SCHET[0]['CODE_MO'] . 'Z' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
					$pname = $pfirst_code . "M" . $SCHET[0]['CODE_MO'] . 'Z' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
					$zname = $first_code . "M" . $SCHET[0]['CODE_MO'] . 'Z' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
					break;
			}

			$file_re_data_sign = $rname;
			$file_re_pers_data_sign = $pname;

			// файл-тело реестра
			$file_re_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";

			// временный файл-тело реестра
			$file_re_data_name_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . "_tmp.xml";

			// файл-перс. данные
			$file_re_pers_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";

			$SCHET[0]['FILENAME'] = $file_re_data_sign;
			$SCHET[0]['SD_Z'] = 0;
			$ZGLV = array();
			$ZGLV[0]['FILENAME1'] = $file_re_data_sign;
			$ZGLV[0]['FILENAME'] = $file_re_pers_data_sign;

			$this->load->library('parser');

			// Определяем шаблоны
			switch ( $data['PayType_SysNick'] ) {
				case 'oms':
					$SCHET[0]['VERSION'] = '3.1.1';
					$ZGLV[0]['VERSION'] = '3.2';

					// Стационар, поликлиника, СМП, параклиника
					$registry_data_type = "pl";

					switch ($res[0]['RegistryGroupType_id']) {
						case 2: // ВМП
							$registry_data_type = "hmp";
							break;

						case 3: // Дисп-ция взр. населения 1-ый этап
						case 4: // Дисп-ция взр. населения 2-ый этап
						case 5: // Дисп-ция детей-сирот стационарных 1-ый этап
						case 6: // Дисп-ция детей-сирот усыновленных 1-ый этап
						case 7: // Периодические осмотры несовершеннолетних
						case 8: // Предварительные осмотры несовершеннолетних
						case 9: // Профилактические осмотры несовершеннолетних
						case 10: // Профилактические осмотры взрослого населения
						case 11: // ДВН, ДДС, ПОВН, МОН
							$registry_data_type = "disp";
							break;
					}

					$xml_file_person = "registry_pskov_person";

					// Разбиваем на части, ибо парсер не может пережевать большие объемы данных
					$person_data_template_body = "registry_pskov_person_body";
					$person_data_template_header = "registry_pskov_person_header";
					$person_data_template_footer = "registry_pskov_person_footer";
					$registry_data_template_body = "registry_pskov_{$registry_data_type}_2018_body";
					$registry_data_template_header = "registry_pskov_{$registry_data_type}_2018_header";
					$registry_data_template_footer = "registry_pskov_all_footer";
					break;

				case 'bud':
				case 'fbud':
					$SCHET[0]['VERSION'] = '1.0';
					$ZGLV[0]['VERSION'] = '1.0';

					$person_data_template_body = "registry_pskov_person_bud_body";
					$person_data_template_header = "registry_pskov_person_bud_header";
					$person_data_template_footer = "registry_pskov_person_bud_footer";
					$registry_data_template_body = "registry_pskov_bud_body";
					$registry_data_template_header = "registry_pskov_bud_header";
					$registry_data_template_footer = "registry_pskov_all_footer";
					break;
			}

			// Заголовок для файла person
			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $person_data_template_header, $ZGLV[0], true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);

			$errors = "";

			if ( $data['PayType_SysNick'] == 'oms' && $res[0]['Registry_IsRepeated'] == 2 ) {
				// берём данные о предыдущих случаях
				$prevData = $this->dbmodel->getPrevRegistryData($data);
				$this->dbmodel->setIDCASE($prevData['maxNumber']);
				$data['prevRegistryData'] = $prevData['prevRegistryData'];
			}

			foreach ( $registrytypes as $type ) {
				$this->textlog->add('Тип реестров: ' . $type);

				$SD_Z = $this->dbmodel->loadRegistryDataForXmlUsing($type, $data, $Registry_EvnNum, $errors, $file_re_data_name_tmp, $file_re_pers_data_name, $registry_data_template_body, $person_data_template_body);

				if ( $SD_Z === false ) {
					$this->textlog->add('Ошибка при выгрузке данных');
					throw new Exception('Ошибка при выгрузке данных');
				}
				else if ( gettype($SD_Z) == 'array' && !empty($SD_Z['Error_Msg']) ) {
					$this->textlog->add($SD_Z['Error_Msg']);
					throw new Exception($SD_Z['Error_Msg']);
				}

				$SCHET[0]['SD_Z'] += $SD_Z;
			}

			$this->textlog->add('Получили все данные из БД');

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

			$file_zip_sign = $zname;
			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".zip";
			$this->textlog->add('Создали XML-файлы: (' . $file_re_data_name . ' и ' . $file_re_pers_data_name . ')');

			$this->textlog->add('Формируем ZIP-архив');

			$file_errors_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign."Errors.txt";
			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
			$zip->AddFile( $file_re_pers_data_name, $file_re_pers_data_sign . ".xml" );
			$zip->close();
			$this->textlog->add('exportRegistryToXml: Упаковали в ZIP '.$file_zip_name);
			$PersonData_registryValidate = true;
			$EvnData_registryValidate = true;
			if($data['PayType_SysNick'] == 'oms' && array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK'] && $res[0]){
				$upload_path = 'RgistryFields/';
				// если включена проверка ФЛК в параметрах системы
				// получим xsd шаблон для проверки
				$settingsFLK = $this->dbmodel->loadRegistryEntiesSettings($res[0]);

				if(@$_GET['q'] == 1){
					echo '$res[0]';
					var_dump($res[0]);
					echo '<br>';

					echo '$settingsFLK';
					var_dump($settingsFLK);
					echo '<br>';

				}

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

							if(@$_GET['q'] == 1){
								echo '$file_re_data_name';
								var_dump($file_re_data_name);
								echo '<br>';

								echo '$fileEvnDataXSD';
								var_dump($fileEvnDataXSD);
								echo '<br>';

								echo '$validateEvnData_err_file';
								var_dump($validateEvnData_err_file);
								echo '<br>';
							}

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

							if(@$_GET['q'] == 1){
								echo '$file_re_pers_data_name';
								var_dump($file_re_pers_data_name);
								echo '<br>';

								echo '$filePersonDataXSD';
								var_dump($filePersonDataXSD);
								echo '<br>';

								echo '$validatePersonData_err_file';
								var_dump($validatePersonData_err_file);
								echo '<br>';
							}

							$PersonData_registryValidate = $this->dbmodel->Reconciliation($file_re_pers_data_name, $filePersonDataXSD, 'file', $validatePersonData_err_file);
						}
					}
				}
			}
			if($PersonData_registryValidate) unlink($file_re_data_name);
			if($EvnData_registryValidate) unlink($file_re_pers_data_name);
			if($PersonData_registryValidate || $EvnData_registryValidate) $this->textlog->add('exportRegistryToXml: Почистили папку за собой ');
			/*
			unlink($file_re_data_name);
			unlink($file_re_pers_data_name);
			$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');
			*/

			if ( !$PersonData_registryValidate && !$EvnData_registryValidate ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
						<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
						<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
						<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
			}
			else if ( !$PersonData_registryValidate ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br>
						<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
						<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
			}
			else if ( !$EvnData_registryValidate ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p><br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
						<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
			}
			else if ( file_exists($file_zip_name) ) {
				@header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
				@header("Content-Type: text/html");
				@header("Pragma: no-cache");

				$data['Status'] = $file_zip_name;
				$data['Registry_EvnNum'] = json_encode($Registry_EvnNum);

				if ( !empty($errors) ) {
					file_put_contents($file_errors_name, toUTF($errors, true));
					$data['Registry_xmlExpPathErr'] = $file_errors_name;
				}

				$this->dbmodel->SetXmlExportStatus($data);
				//echo "{'success':true,'Link':'$file_zip_name'}";
				$this->textlog->add("exportRegistryToXml: Все закончилось, вроде успешно.");

				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);

				if (!empty($data['forSign'])) {
					$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
					$filebase64 = base64_encode(file_get_contents($file_zip_name));
					$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
					return true;
				} else {
					$this->textlog->add('exportRegistryToXml: Передача ссылки: '.$file_zip_name);
				}
			}
			else{
				throw new Exception('Ошибка создания архива реестра!');
			}

			$this->textlog->add("exportRegistryToXml: Финиш");
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add("exportRegistryToXml: " . $e->getMessage());
			$this->ReturnError($e->getMessage());
		}

		return true;
	}

	/**
	 * Функция формирует файлы в XML формате для выгрузки данных для проверки объемов.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportRegistryToXmlCheckVolume()
	{	
		$data = $this->ProcessInputData('exportRegistryToDbf', true);
		if ($data === false) { return false; }

		$type = 0;
		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		// нафиг проверять
		$res = $this->dbmodel->GetRegistryXmlExport($data);
		
		if (is_array($res) && count($res) > 0) 
		{
			if ($res[0]['Registry_xmlExportPath'] == '1')
			{
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).')));
				return;
			}
			elseif (strlen($res[0]['Registry_xmlExportPath'])>0) // если уже выгружен реестр
			{
				$link = $res[0]['Registry_xmlExportPath'];
				echo "{'success':true,'Link':'$link'}";
				return;
			}
			else 
			{
				$type = $res[0]['RegistryType_id'];
			}
		}
		else 
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.')));
			return;
		}
		
		// Формирование XML в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try
		{
			$data['Status'] = '1';
			$this->dbmodel->SetXmlExportStatus($data);
			
			set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
			// RegistryData 
			$registry_data_res = $this->dbmodel->loadRegistryDataForXmlCheckVolumeUsing($type, $data);
			if ($registry_data_res === false)
			{
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
				return false;
			}
			if ( empty($registry_data_res) )
			{
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данных по требуемому реестру нет в базе данных.')));
				return false;
			}
			
			$this->load->library('parser');
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml_check_volume/registry_pskov_pl', $registry_data_res, true);
			reset($registry_data_res);
										
			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_check_volume_".time()."_".$data['Registry_id'];
			mkdir( EXPORTPATH_REGISTRY.$out_dir );
							
			// файл-тело реестра
			$file_re_data_sign = $registry_data_res['lpu_code'] . '_' . date('Y_m') . '_' . count($registry_data_res['registry_data']) . "_2";
			$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".xml";				
			
			file_put_contents($file_re_data_name, $xml);

			$base_name = $_SERVER["DOCUMENT_ROOT"]."/";
			
			$file_zip_sign = $file_re_data_sign;
			$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
			
			$zip=new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
			$zip->close();
			
			unlink($file_re_data_name);
			
			if (file_exists($file_zip_name))
			{
				$link = $file_zip_name;
				echo "{'success':true,'Link':'$link'}";					
				$data['Status'] = $file_zip_name;
				$this->dbmodel->SetExportStatus($data);
			}
			else{
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка создания архива реестра!')));
			}
			
		}
		catch (Exception $e)
		{
			$data['Status'] = '';
			$this->dbmodel->SetExportStatus($data);
			$this->ReturnData(array('success' => false, 'Error_Msg' => $this->error_deadlock));
		}
	}
	
	/**
	 * Импорт реестра из DBF
	 */
	function importRegistryFromDbf()
	{
		
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar');
		$dbffile = "REG_LPU.DBF";
		$dbfhfile = "LPU_INFO.DBF";
		
		$data = $this->ProcessInputData('importRegistryFromDbf', true);
		if ($data === false) { return false; }
		
		if (!isset($_FILES['RegistryFile'])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл реестра!') ) );
			return false;
		}
		
		if (!is_uploaded_file($_FILES['RegistryFile']['tmp_name']))
		{
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];
			switch($error)
			{
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
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message)));
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array($file_data['file_ext'], $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}

		
		
		$zip = new ZipArchive;
		if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) 
		{
			$zip->extractTo( $upload_path );
			$zip->close();
		}
		unlink($_FILES["RegistryFile"]["tmp_name"]);
		// там должен быть файл REG_LPU.DBF
		if ((!file_exists($upload_path.$dbffile)) || (!file_exists($upload_path.$dbfhfile)))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}
		
		$recall = 0;
		$recerr = 0;
		
		$h = dbase_open($upload_path.$dbffile, 0);
		if ($h) 
		{
			// Определяем номер реестра из файла - нужно будет доделать
			$hd = dbase_open($upload_path.$dbfhfile, 0);
			$datah = array('ID_SMO'=>null,'ID_SUBLPU'=>null,'DATE_BEG'=>null,'DATE_END'=>null);
			if ($hd)
			{
				$r = dbase_numrecords($hd);
				for ($i=1; $i <= $r; $i++) 
				{
					$rech = dbase_get_record_with_names($hd, $i);
					$datah['ID_SMO'] = iconv("cp866","cp1251",trim($rech['ID_SMO']));
					$datah['ID_SUBLPU'] = iconv("cp866","cp1251",trim($rech['ID_SUBLPU']));
					$datah['DATE_BEG'] = iconv("cp866","cp1251",date("d.m.Y",strtotime(trim($rech['DATE_BEG']))));
					$datah['DATE_END'] = iconv("cp866","cp1251",date("d.m.Y",strtotime(trim($rech['DATE_END']))));
				}
				// Дальше запрос к базе и определение реестра 
				dbase_close ($hd);
			}
			
			// Удаляем ответ по этому реестру, если он уже был загружен
			$rr = $this->dbmodel->deleteRegistryError($data);
			// Всего записей в пришедшем реестре 
			$Rec_Count = dbase_numrecords($h);
			for ($i=1; $i <= $Rec_Count; $i++) 
			{
				$rec = dbase_get_record_with_names($h, $i);
				$d = array();
				/*
				foreach($rec as $k=>&$v)
				{
					if ($v=='')
					{
						$d[$k] = null;
					}
					else
					{
						$d[$k] = iconv("cp866","cp1251",$v);
					}
				}
				*/
				$d['ID'] = iconv("cp866","cp1251",trim($rec['ID']));
				$dstr = iconv("cp866","cp1251",trim($rec['FLAG']));
				if (strlen($dstr)>0)
				{
					// Залить в базу
					$dd = explode(',', $dstr);
					$rr = count($dd);
					if ($rr>0)
					{
						for ($ii=0; $ii < $rr; $ii++) 
						{
							$d['FLAG'] = $dd[$ii];
							$response = $this->dbmodel->setErrorFromImportRegistry($d,$data);
							if (!is_array($response)) 
							{
								$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!')));
								return false;
							}
						}
						// 
					}
					$recerr++; // Записей с ошибками
				}
				$recall++; // Всего загружено записей
			}
			if ($recall>0)
			{
				// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']);
			}
			
			// Залили
			// После этого надо вывести количество обработанных записей всего 
			// записей с ошибками
			// и разнести их нормально по записям отображая ошибки ()
			dbase_close ($h);
			/*
			unlink($upload_path.$dbffile);
			unlink($upload_path.$dbfhfile);
			*/

			// Пишем информацию об импорте в историю
			$this->dbmodel->dumpRegistryInformation($data, 3);

			$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'dateBeg'=>$datah['DATE_BEG'], 'dateEnd'=>$datah['DATE_END'], 'Message' => toUTF('Реестр успешно загружен.')));
			return true;
		}
		else
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка чтения dbf!')));
			return false;
		}
	}
	
	
	/**
	 * Импорт реестра из XML
	 */
	function importRegistryFromXml()
	{
		
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');
		
		$data = $this->ProcessInputData('importRegistryFromXml', true);
		if ($data === false) { return false; }
		
		if (!isset($_FILES['RegistryFile'])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл реестра!') ) ) ;
			return false;
		}
		
		if (!is_uploaded_file($_FILES['RegistryFile']['tmp_name']))
		{
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];
			switch($error)
			{
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
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message)));
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array($file_data['file_ext'], $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}

		if ($file_data['file_ext'] == 'xml') {
			$xmlfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл HM*.xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) 
			{
				$xmlfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/HM.*xml/', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}
				
				
		if (empty($xmlfile))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}
		
		// признаки нахождения внутри тегов.
		$sluch = false;
		$refreason = false;
		$schet = false;
		$pacient = false;
		$errors = array();
		
		$idcase = 0;
		$recall = 0;
		$recerr = 0;
		$oldid = 0;
		$matches = array();
		
		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistry($data);		
		if (!is_array($registrydata) || !isset($registrydata[0])) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка чтения данных реестра')));
			return false;
		} else {
			$registrydata = $registrydata[0];
		}
		
		// Удаляем ответ по этому реестру, если он уже был загружен
		$rr = $this->dbmodel->deleteRegistryError($data);
		
		$handle = @fopen($upload_path.$xmlfile, "r");

		if ($handle) {
			while (($buffer = fgets($handle, 4096)) !== false) {
				// двигаемся до SCHET
				if (preg_match('/<SCHET>/',$buffer) > 0) {
					$schet = true;
				}

				if (preg_match('/<\/SCHET>/',$buffer) > 0) {
					$schet = false;
				}
				
				if ($schet) {
					// проверяем соответсвию поля
					if (preg_match('/<CODE>(.*)<\/CODE>/',$buffer,$matches) > 0) {
						if ($registrydata['Registry_id'] != trim($matches[1])) {
							$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Не совпадает идентификатор реестра и импортируемого файла, импорт не произведен')));
							return false;
						}
					}
					
					if (preg_match('/<DSCHET>(.*)<\/DSCHET>/',$buffer,$matches) > 0) {
						if ($registrydata['Registry_accDate'] != date('d.m.Y',strtotime(trim($matches[1])))) {
							$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Не совпадает дата реестра и импортируемого файла, импорт не произведен')));
							return false;
						}
					}
				}
				
				/* ПРОВЕРКА НА наличие ID_PERS в реестре, убрал, т.к. в ответах ID_PERS = IDCASE, а не Person_id.
				// если ещё не добавляли ошибок к реестру
				if ($recerr == 0) {
					// двигаемся до PACIENT и проверяем соответсвие
					if (preg_match('/<PACIENT>/',$buffer) > 0) {
						$pacient = true;
						$spolis = '';
						$npolis = '';
					}
					
					if (preg_match('/<\/PACIENT>/',$buffer) > 0) {
						$pacient = false;
						if(!empty($id_pers)){
							$data['ID_PERS'] = $id_pers;
							$check = $this->dbmodel->checkErrorDataInRegistry($data);
							if (!$check) {
								$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Идентификатор "'.$id_pers.'" пациента полис серия:"'.$spolis.'" №:"'.$npolis.'" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен')));
								return false;
							}
						}
					}
					
					if ($pacient) {
						if (preg_match('/<ID_PERS>(.*)<\/ID_PERS>/',$buffer,$matches) > 0) {
							$id_pers = trim($matches[1]);
						}
						
						if (preg_match('/<SPOLIS>(.*)<\/SPOLIS>/',$buffer,$matches) > 0) {
							$spolis = trim($matches[1]);
						}
						
						if (preg_match('/<NPOLIS>(.*)<\/NPOLIS>/',$buffer,$matches) > 0) {
							$npolis = trim($matches[1]);
						}
					}
				}
				*/
				
				// двигаемся до SLUCH
				if (preg_match('/<SLUCH>/',$buffer) > 0) {
					$sluch = true;
				}
				
				if (preg_match('/<\/SLUCH>/',$buffer) > 0) {
					$sluch = false;
				}
				
				// если находимся внутри SLUCH
				if ($sluch) {
					// берём ID
					if (preg_match('/<IDCASE>(.*)<\/IDCASE>/',$buffer,$matches) > 0) {
						$recall++;
						$idcase = trim($matches[1]);
						if (!isset($errors[$idcase])) {
							$errors[$idcase] = array();
						}
					}
					
					if (($recerr == 0) && (preg_match('/<NHISTORY>(.*)<\/NHISTORY>/',$buffer,$matches) > 0) && (!empty($idcase))) {
						$nhistory = trim($matches[1]);
						$data['IDCASE'] = $idcase;
						// если ещё не добавляли ошибок к реестру, то проверяем соотвествие параметра реестру
						$check = $this->dbmodel->checkErrorDataInRegistry($data);
						if (!$check){
							$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Идентификатор "'.$idcase.'" для случая № "'.$nhistory.'" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен')));
							return false;
						}
					}
										
					if (preg_match('/<REFREASON>/',$buffer) > 0) {
						$refreason = true;
					}

					if (preg_match('/<\/REFREASON>/',$buffer) > 0) {
						$refreason = false; 
					}
					
					// если находимся внутри REFREASON				
					if ($refreason) {
						if ($oldid != $idcase) {
							$recerr++; // записей с ошибками
							$oldid = $idcase;
						}
						
						// берём код ошибки
						if (preg_match('/<CODE>(.*)<\/CODE>/',$buffer,$matches) > 0) {
							$codeid = trim($matches[1]);
							
							if (isset($errors[$idcase]) && !in_array($codeid, $errors[$idcase])) {
								$errors[$idcase][] = $codeid;
								
								$d['FLAG'] = $codeid;
								$d['IDCASE'] = $idcase;
								
								$response = $this->dbmodel->setErrorFromImportRegistry($d,$data);
								if (!is_array($response)) 
								{
									$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!')));
									return false;
								} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
									$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg'])));
									return false;
								}
							}
						}
					}
				}
			}
			fclose($handle);
		}
		
	
		if ($recall>0)
		{
			// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']);
		}

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'Message' => toUTF('Реестр успешно загружен.')));
		return true;
		
	}

	/**
	 * Импорт реестра из ТФОМС
	 */
	function importRegistryFromTFOMS()
	{
		$upload_path = './'.IMPORTPATH_ROOT.'importRegistryFromTFOMS/'.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml|mp');

		$data = $this->ProcessInputData('importRegistryFromXml', true);
		if ($data === false) { return false; }

		if (!isset($_FILES['RegistryFile'])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл реестра!') ) ) ;
			return false;
		}

		if (!is_uploaded_file($_FILES['RegistryFile']['tmp_name']))
		{
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];
			switch($error)
			{
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
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message)));
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		$path = '';
		$folders = explode('/', $upload_path);
		for($i=0; $i<count($folders); $i++) {
			$path .= $folders[$i].'/';
			if (!@is_dir($path)) {
				mkdir( $path );
			}
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}

		if (strtolower($file_data['file_ext']) == 'xml') {
			$xmlfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) {
				$xmlfile = "";

				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/(' . implode("|", $this->_xmlDataFilePrefixesOMS) . ').*\.(xml|XML)/', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}


		if (empty($xmlfile))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}

		libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$res = $dom->load($upload_path.$xmlfile);
		foreach (libxml_get_errors() as $error) {
			if ($error->code != 100) {		//Игнорирование ошибки xmlns: URI OMS-D1 is not absolute
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
				return false;
			}
		}
		libxml_clear_errors();

		$response = array('success' => false);
		switch($dom->documentElement->tagName) {	//Формат выбирается по root'овому элементу
			case 'ZL_LIST':
				$response = $this->importRegistryFromTfomsFormat1($data, $upload_path.$xmlfile);
				break;

			case 'FLK_P':
				$response = $this->importRegistryFromTfomsFormat2($data, $upload_path.$xmlfile);;
				break;
		}

		$this->ReturnData($response);
		return true;
	}

	/**
	 * Импорт реестра из ТФОМС
	 */
	function importRegistryFromTfomsFormat1($data, $xmlfilepath) {
		$this->load->library('textlog', array('file'=>'importRegistryFromTFOMS_' . date('Y-m-d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('Запуск импорта, реестр: ' . $data['Registry_id']);

		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistry($data);
		if (!is_array($registrydata) || !isset($registrydata[0])) {
			$this->textlog->add('Ошибка чтения данных реестра');
			return array('success' => false, 'Error_Msg' => toUTF('Ошибка чтения данных реестра'));
		} else {
			$registrydata = $registrydata[0];
		}

		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$IDCASE = null;
		$isErrors = false;
		$recerr = 0;
		$recall = 0;
		$osnCount = 0;
		$osnFlkOnly = true;

		libxml_use_internal_errors(true);

		$this->textlog->add('Прочитали XML-файл: ' . $xmlfilepath);
		$xmlString = file_get_contents($xmlfilepath);
		$checkString = substr($xmlString, 0, 2048);
		if ( strpos($checkString, '<SCHET>') === false ) {
			$this->textlog->add('Файл не является архивом реестра.');
			return array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.'));
		}

		$header = substr($checkString, 0, strpos($checkString, '</SCHET>') + strlen('</SCHET>'));
		$footer = '</ZL_LIST>';

		unset($checkString);

		$xmlString = substr($xmlString, strlen($header));

		// 10 MB
		$chunkSize = 1024 * 1024 * 10;

		$this->textlog->add('Начинаем обработку реестра');
		$firstRead = true;

		while (!empty($xmlString)) {
			// Нагребаем остатки, если размер оставшегося куска файла меньше $chunkSize МБ
			if (strlen($xmlString) <= $chunkSize + strlen($footer) + 2 /* учтем перевод строки */) {
				$xmlData = substr($xmlString, 0, strlen($xmlString) - strlen($footer));
				$xmlString = '';
			} // или данные по $chunkSize МБ
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
					$this->textlog->add('Файл не является архивом реестра.');
					return array('success' => false, 'Error_Msg' => 'Файл не является архивом реестра.');
				}
			}

			if ($firstRead) {
				$dschet = $xml->SCHET->DSCHET->__toString();
				$code = $xml->SCHET->CODE->__toString();

				if ($code != $registrydata['Registry_id']) {
					$this->ReturnError('Номер счета из файла не соответствует реестру.', 100019);
					$this->textlog->add('Номер счета из файла не соответствует реестру.');
					return false;
				}

				if (date('d.m.Y', strtotime($dschet)) != $registrydata['Registry_accDate']) {
					$this->textlog->add('Не совпадает идентификатор реестра и импортируемого файла, импорт не произведен');
					return array('success' => false, 'Error_Msg' => toUTF('Не совпадает дата реестра и импортируемого файла, импорт не произведен'));
				}

				$firstRead = false;
			}

			foreach ($xml->ZAP as $onezap) {
				foreach ($onezap->Z_SL as $onezsl) {
					$recall++;
					$isErrors = false;

					foreach ($onezsl->SL as $onesl) {
						$IDCASE = $onesl->SL_ID->__toString();
						$data['IDCASE'] = $IDCASE;
						$check = $this->dbmodel->checkErrorDataInRegistry($data);
						if (!$check) {
							$this->textlog->add('Идентификатор "' . $IDCASE . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
							return array('success' => false, 'Error_Msg' => 'Идентификатор "' . $IDCASE . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
						}

						// идём по ошибкам на уровне законеченного случая
						foreach ( $onesl->SANK as $onesank ) {
							$SANK_SL_ID = (property_exists($onesank, 'SL_ID') ? $onesank->SL_ID->__toString() : null);

							if (empty($SANK_SL_ID) || $SANK_SL_ID == $IDCASE) { // если SL_ID не заполнен, то ошибка записывается для всех случаев (SL), входящих в законченный случай
								$data['IDCASE'] = $IDCASE;
								$data['S_OSN'] = $onesank->S_OSN->__toString();
								$data['COMMENT'] = $onesank->S_COM->__toString();

								$isErrors = true;
								if (!in_array($data['S_OSN'], array('50', '51', '52', '53', '54', '55'))) {
									$osnFlkOnly = false;
								}
								$osnCount++;

								$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($data);
								if (!is_array($response)) {
									$this->textlog->add('Ошибка при обработке реестра!');
									return array('success' => false, 'Error_Msg' => 'Ошибка при обработке реестра!');
								} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
									$this->textlog->add($response[0]['Error_Msg']);
									return array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']);
								}
							}
						}
					}

					if ($isErrors) {
						$recerr++; // записей с ошибками
					}
				}
			}
		}

		$this->textlog->add('Закончили обработку реестра');

		if ($recall>0)
		{
			// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']); // при импорте ничего не происходит по ТЗ :)
		}

		/*if ($osnCount == 0 || !$osnFlkOnly) { // если ни одной ошибки или не только ошибки ФЛК, то переводим реестр в статус "Загружен ТФОМС"
			$this->dbmodel->setRegistryCheckStatus(array(
				'Registry_id' => $data['Registry_id'],
				'RegistryCheckStatus_SysNick' => 'UploadTFOMS',
				'pmUser_id' => $data['pmUser_id']
			));
		}*/

		$this->textlog->add('Пишем информацию об импорте в историю');

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->textlog->add('Закончили импорт');

		return array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'Message' => toUTF('Реестр успешно загружен.'));
	}

	/**
	 * Импорт реестра из ТФОМС
	 */
	function importRegistryFromTfomsFormat2($data, $xmlfilepath) {
		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistry($data);
		if (!is_array($registrydata) || !isset($registrydata[0])) {
			return array('success' => false, 'Error_Msg' => toUTF('Ошибка чтения данных реестра'));
		} else {
			$registrydata = $registrydata[0];
		}

		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$N_ZAP = null;
		$recerr = 0;
		$recall = 0;

		libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$res = $dom->load($xmlfilepath);
		foreach (libxml_get_errors() as $error) {
			if ($error->code != 100) {		//Игнорирование ошибки xmlns: URI OMS-D1 is not absolute
				return array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.'));
			}
		}
		libxml_clear_errors();

		$export_file_name_array = explode('/', $registrydata['Registry_xmlExportPath']);
		$export_file = array_pop($export_file_name_array);

		// проверка соответствия файла реестру
		$dom_fnamei = $dom->getElementsByTagName('FNAME_I');
		foreach($dom_fnamei as $dom_onefnamei) {
			if ($export_file != $dom_onefnamei->nodeValue.'.zip') {
				return array('success' => false, 'Error_Msg' => toUTF('Не совпадает название файла экпорта, импорт не произведен'));
			}
		}

		// идём по случаям
		$dom_pr = $dom->getElementsByTagName('PR');
		foreach($dom_pr as $dom_onepr) {
			$dom_nzap = $dom_onepr->getElementsByTagName('N_ZAP');
			foreach($dom_nzap as $dom_onenzap) {
				$N_ZAP = $dom_onenzap->nodeValue;
			}
			$data['IDCASE'] = null;
			$data['N_ZAP'] = $N_ZAP;
			$check = $this->dbmodel->checkErrorDataInRegistry($data);
			if (!$check) {
				return array('success' => false, 'Error_Msg' => toUTF('Идентификатор "'.$N_ZAP.'" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен'));
			}

			$data['IDCASE'] = $this->dbmodel->getEvnIdByRowNum($data['Registry_id'], $N_ZAP);
			if (!$data['IDCASE']) {
				return array('success' => false, 'Error_Msg' => toUTF('Ошибка при получения идентификатора события'));
			}
			$data['S_OSN'] = null;
			$data['COMMENT'] = null;

			$dom_oshib = $dom_onepr->getElementsByTagName('OSHIB');
			foreach($dom_oshib as $dom_oneoshib) {
				$data['S_OSN'] = $dom_oneoshib->nodeValue;
			}
			$dom_comment = $dom_onepr->getElementsByTagName('COMMENT');
			foreach($dom_comment as $dom_onecomment) {
				$data['COMMENT'] = $dom_onecomment->nodeValue;
			}

			$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($data);
			if (!is_array($response))
			{
				return array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!'));
			} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
				return array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg']));
			}

			$recall++;
			$recerr++; // записей с ошибками
		}

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		return array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'Message' => toUTF('Реестр успешно загружен.'));
	}
	
	/**
	 * Импорт данных СМО реестра из DBF
	 */
	function importRegistrySmoDataFromDbf()
	{
		$this->load->database('default', false);
		$this->load->model('Registry_model', 'dbmodel');

		
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$allowed_types = explode('|','zip|rar');
		$dbfpatfile = "patient.dbf";
		$dbfprotofile = "lpu_rep.dbf";
		$dbfvizfile = "visit.dbf";
		$upload_path = './' . IMPORTPATH_ROOT . $_SESSION['lpu_id'] . '/';

		$data = $this->ProcessInputData('importRegistrySmoDataFromDbf', true);
		if ($data === false) { return false; }
		
		if ( !isset($_FILES['RegistryFile']) ) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл данных!') ) );
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

			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message)));
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);

		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			mkdir( $upload_path );
		}

		if ( !@is_dir($upload_path) ) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if ( !is_writable($upload_path) ) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}


		$zip = new ZipArchive();

		if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
			$zip->extractTo( $upload_path );
			$zip->close();
		}

		unlink($_FILES["RegistryFile"]["tmp_name"]);

		if ( (!file_exists($upload_path.$dbfpatfile)) || (!file_exists($upload_path.$dbfvizfile)) || (!file_exists($upload_path.$dbfprotofile)) ) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом исправленных данных.')));
			return false;
		}
		
		$recall = 0;
		$recerr = 0;
		$recupd = 0;
		
		$h = dbase_open($upload_path . $dbfvizfile, 0);

		if ( $h ) {
			$personData = array();
			$r = dbase_numrecords($h);

			$cnt = 0;

			for ( $i = 1; $i <= $r; $i++ ) {
				$rech = dbase_get_record_with_names($h, $i);

				foreach ( $rech as $key => $value ) {
					$rech[$key] = trim($rech[$key]);
				}

				if ( /*!in_array($rech['ID'], $personData) &&*/ $rech['SMO'] != "NO" ) {
					//$personData[] = $rech['ID'];

					array_walk($rech, 'ConvertFromWin866ToCp1251');
					/*
					switch ( $rech['ID_STATUS'] ) {
						case 1:
						case 2:
							$rech['ID_STATUS'] = 3;
						break;

						case 3:
							$rech['ID_STATUS'] = 4;
						break;

						case 4:
							$rech['ID_STATUS'] = 1;
						break;

						case 5:
						case 6:
						case 8:
							$rech['ID_STATUS'] = 5;
						break;

						case 7:
							$rech['ID_STATUS'] = 2;
						break;
					}
					*/
					switch ( $rech['SEX'] ) {
						case 'м':
						case 'М':
							$rech['SEX'] = 1;
						break;

						case 'ж':
						case 'Ж':
							$rech['SEX'] = 2;
						break;

						default:
							$rech['SEX'] = NULL;
						break;
					}

					if ( !empty($rech['SNILS']) ) {
						$rech['SNILS'] = str_replace(' ', '', str_replace('-', '', $rech['SNILS']));
					}

					$rs = $this->dbmodel->savePersonData(array_merge($data, $rech));
					$recall++;

					if ( is_array($rs) && count($rs) > 0 ) {
						if ( is_array($rs[0]) && array_key_exists('Error_Msg', $rs[0]) ) {
							if ( !empty($rs[0]['Error_Msg']) ) {
								$recerr++;
							}
							else {
								$cnt++;
							}
						}
						else {
							$recerr++;
						}
					}
					else {
						$recerr++;
					}

					if ( $cnt == 100 || $i == $r ) {
						// Запуска процесса обработки загруженных данных
						$response = $this->dbmodel->updatePersonErrorData($data);

						if ( $response === false ) {
							$this->ReturnData( array('success' => false, 'Error_Code' => 100027 , 'Error_Msg' => toUTF('Ошибка при выполнении обработки загруженных данных.')));
							return false;
						}

						$cnt = 0;
						$recupd += (!empty($response[0]['CountUpd']) ? $response[0]['CountUpd'] : 0);
					}
				}
			}

			dbase_close($h);

			if ( $recupd > 0 ) {
				$this->db = null;
				$this->load->database('registry');

				$resposne = $this->dbmodel->setRegistryIsNeedReform(array(
					 'Registry_id' => $data['Registry_id']
					,'Registry_IsNeedReform' => 2
					,'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'recAll' => $recall, 'recUpd' => $recupd, 'recErr' => $recerr, 'Message' => toUTF('Данные обработаны.')));
		return true;
	}


	/**
	 * Печать реестра
	 */
	function printRegistry() {
		$this->load->library('parser');

		$template = '';

		$data = $this->ProcessInputData('printRegistry', true);
		if ($data === false) { return false; }

		// Получаем данные по счету
		$response = $this->dbmodel->getRegistryFields($data);

		if ( (!is_array($response)) || (count($response) == 0) || (!isset($response['Registry_Sum'])) ) {
			echo 'Ошибка при получении данных по счету';
			return true;
		}
		else {
			switch ( $response['RegistryType_Code'] ) {
				case 2:
					$template = 'print_registry_account_polka';
				break;

				case 4:
					$template = 'print_registry_account_dopdisp';
				break;

				case 5:
					$template = 'print_registry_account_orpdisp';
				break;
				
				case 6:
					$template = 'print_registry_account_smp';
				break;
				
				default:
					echo 'По данному реестру счет невозможно получить - функционал находится в разработке!';
					return true;
				break;
			}
		}

		$m = new money2str();
		$registry_sum_words = trim($m->work(number_format($response['Registry_Sum'], 2, '.', ''), 2));
		$registry_sum_words = mb_strtoupper(mb_substr($registry_sum_words, 0, 1)) . mb_substr($registry_sum_words, 1, strlen($registry_sum_words) - 1);

		$month = array(
			'январе',
			'феврале',
			'марте',
			'апреле',
			'мае',
			'июне',
			'июле',
			'августе',
			'сентябре',
			'октябре',
			'ноябре',
			'декабре'
		);

		//array_walk($month, 'ConvertFromUTF8ToWin1251');

		$parse_data = array(
			'Lpu_Account' => isset($response['Lpu_Account']) ? $response['Lpu_Account'] : '&nbsp;',
			'Lpu_Address' => isset($response['Lpu_Address']) ? $response['Lpu_Address'] : '&nbsp;',
			'Lpu_Director' => isset($response['Lpu_Director']) ? $response['Lpu_Director'] : '&nbsp;',
			'Lpu_GlavBuh' => isset($response['Lpu_GlavBuh']) ? $response['Lpu_GlavBuh'] : '&nbsp;',
			'Lpu_INN' => isset($response['Lpu_INN']) ? $response['Lpu_INN'] : '&nbsp;',
			'Lpu_KPP' => isset($response['Lpu_KPP']) ? $response['Lpu_KPP'] : '&nbsp;',
			'Lpu_Name' => isset($response['Lpu_Name']) ? $response['Lpu_Name'] : '&nbsp;',
			'Lpu_OKPO' => isset($response['Lpu_OKPO']) ? $response['Lpu_OKPO'] : '&nbsp;',
			'Lpu_OKVED' => isset($response['Lpu_OKVED']) ? $response['Lpu_OKVED'] : '&nbsp;',
			'Lpu_Phone' => isset($response['Lpu_Phone']) ? $response['Lpu_Phone'] : '&nbsp;',
			'LpuBank_BIK' => isset($response['LpuBank_BIK']) ? $response['LpuBank_BIK'] : '&nbsp;',
			'LpuBank_Name' => isset($response['LpuBank_Name']) ? $response['LpuBank_Name'] : '&nbsp;',
			'Month' => isset($response['Registry_Month']) && isset($month[$response['Registry_Month'] - 1]) ? $month[$response['Registry_Month'] - 1] : '&nbsp;',
			'Registry_accDate' => isset($response['Registry_accDate']) ? $response['Registry_accDate'] : '&nbsp;',
			'Registry_Num' => isset($response['Registry_Num']) ? $response['Registry_Num'] : '&nbsp;',
			'Registry_Sum' => isset($response['Registry_Sum']) ? number_format($response['Registry_Sum'], 2, '.', ' ') : '&nbsp;',
			'Registry_Sum_Words' => $registry_sum_words,
			'Year' => isset($response['Registry_Year']) ? $response['Registry_Year'] : '&nbsp;'
		);

		$this->parser->parse($template, $parse_data);

		return true;
	}

	/**
	 * Удаление реестра
	 */
	function deleteRegistry() 
	{
		
		$data = $this->ProcessInputData('deleteRegistry', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->deleteRegistry($data);
		
		if (isset($response[0]['Error_Message'])) { $response[0]['Error_Msg'] = $response[0]['Error_Message']; } else { $response[0]['Error_Msg'] = ''; }
		
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Загрузка данных для комбобокса "Первичный реестр"
	 */
	function getRegistryPrimaryCombo()
	{
		$data = $this->ProcessInputData('getRegistryPrimaryCombo', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getRegistryPrimaryCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
