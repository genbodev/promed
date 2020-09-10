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

class Kareliya_Registry extends Registry {
	var $scheme = "r10";
	var $model_name = "Registry_model";
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		// Инициализация класса и настройки
		/*
		$this->inputRules['deleteRegistry'] = array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			)
		);
		*/

		$this->inputRules['setRegistryIsLocked'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Registry_IsLocked', 'label' => 'Признак "Реестр заблокирован"', 'rules' => '', 'type' => 'id')
		);
		
		$this->inputRules['cancelRegistryIdentification'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id')
		);

		$this->inputRules['loadRegistryNoPolis'] = array_merge($this->inputRules['loadRegistryNoPolis'], array(
			array(
				'field' => 'Person_OrgSmo',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Polis',
				'label' => '№ полиса',
				'rules' => '',
				'type' => 'string'
			)
		));

		$this->inputRules['saveRegistry'] = array(
			array(
				'field' => 'OrgSMO_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'DispClass_id',
				'label' => 'Тип дисп-ции/медосмотра:',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => Null,
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
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
				'field' => 'RegistryStacType_id',
				'label' => 'Тип реестра стационара',
				'rules' => '',
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
		
		$this->inputRules['importRegistryFLK'] = array(
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
			),
			array(
				'field' => 'FormatType',
				'label' => 'Формат',
				'rules' => 'required',
				'type' => 'int'
			)
		);

		$this->inputRules['checkRegistryHasPaidInside'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
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
		
		$this->inputRules['exportRegistryToXml'] = array(
			array(
				'field' => 'OverrideControlFlkStatus',
				'label' => 'Флаг пропуска контроля на статус Проведен контроль ФЛК',
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
				'field' => 'onlyLink',
				'label' => 'Флаг вывода только ссылки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'forSign',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'int'
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

		$this->inputRules['identifyRegistryErrorTFOMS'] = array();
		
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
				'field' => 'OrgSMO_id',
				'label' => 'СМО',
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
	 * Простановка признака "Заблокирован" для реестра
	 */
	function setRegistryIsLocked()  {
		$data = $this->ProcessInputData('setRegistryIsLocked', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->setRegistryIsLocked($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();

		return true;
	}
	
	/**
	 * Прервать идентификацию
	 */
	function cancelRegistryIdentification()  {
		$data = $this->ProcessInputData('cancelRegistryIdentification', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->cancelRegistryIdentification($data);
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
				$field = Array('object' => "PayType",'id' => "PayType_SysNick", 'name' => "PayType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
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
				$this->ReturnData(array('success' => true, 'exportfile' => ($res[0]['Registry_IsLocked'] == 2 ? 'only' : '') . 'exists'));
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
	 * @return bool
	 */
	protected function _exportRegistryToXml() {
		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportRegistryToXml($data);
		$this->ProcessModelSave($response, true, 'Ошибка при экспорте реестров')->ReturnData();

		return true;
	}

	/**
	 * Экспорт реестра для ТФОМС
	 */
	public function exportRegistryToXml() {
		$this->_exportRegistryToXml();
		return true;

		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ( $data === false ) { return false; }

		try {
			$this->dbmodel->beginTransaction();

			$alertMsg = '';

			$this->load->library('textlog', array('file'=>'exportRegistryToXml.log'));
			$this->textlog->add('');
			$this->textlog->add('exportRegistryToXml: Запуск');

			// Получаем данные по реестру
			$res = $this->dbmodel->GetRegistryXmlExport($data);

			if ( !is_array($res) || count($res) == 0 ) {
				throw new Exception('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			}

			$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
			$data['KatNasel_SysNick'] = $res[0]['KatNasel_SysNick'];
			$OrgSmo_Code = ''; // OrgSmo_f002smocod
			$type = 0;

			if ( $data['KatNasel_SysNick'] == 'oblast' ) {
				$OrgSmo_Code = $this->dbmodel->getOrgSmoCode($data['Registry_id']);

				if ( $OrgSmo_Code === false ) {
					throw new Exception('Ошибка при получении кода СМО');
				}
			}

			// Запрет экспорта и отправки в ТФОМС реестра нуждающегося в переформировании (refs #13648)
			if ( !empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2 ) {
				throw new Exception('Часть реестров нуждается в переформировании, экспорт невозможен.');
			}

			// Запрет экспорта и отправки в ТФОМС реестра при несовпадении суммы всем случаям (SUMV) итоговой сумме (SUMMAV). (refs #13806)
			if ( !empty($res[0]['Registry_SumDifference']) ) {
				throw new Exception('Экспорт невозможен. Неверная сумма по счёту и реестрам.', 12);
			}
			
			// Запрет экспорта и отправки в ТФОМС реестра при 0 записей
			if ( empty($res[0]['RegistryData_Count']) ) {
				throw new Exception('Экспорт невозможен. Нет случаев в реестре.', 13);
			}
			
			$this->textlog->add('exportRegistryToXml: Получили путь из БД:' . $res[0]['Registry_xmlExportPath']);

			if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
				throw new Exception('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			}
			// если уже выгружен реестр
			else if ( !empty($res[0]['Registry_xmlExportPath']) ) {
				$this->textlog->add('exportRegistryToXml: Реестр уже выгружен');

				if ( empty($data['OverrideExportOneMoreOrUseExist']) ) {
					throw new Exception('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)', 11);
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
					$this->textlog->add('exportRegistryToXml: Выход с передачей ссылкой: '.$link);

					$this->dbmodel->rollbackTransaction();

					return true;
				}
				// Запрет переформирования заблокированного реестра
				// @task https://redmine.swan.perm.ru/issues/74209
				else if ( !empty($res[0]['Registry_IsLocked']) && $res[0]['Registry_IsLocked'] == 2 ) {
					throw new Exception('Реестр заблокирован, переформирование невозможно.');
				}
				else {
					$type = $res[0]['RegistryType_id'];
				}
			}
			else 
			{
				$type = $res[0]['RegistryType_id'];
			}

			$registryIsUnion = ($type == 13);

			$dateX20180401 = '20180401';
			$dateX20180901 = '20180901';
			$dateX20181101 = '20181101';
			$dateX20181201 = '20181201';
			$dateX20190101 = '20190101';
			$dateX20191101 = '20191101';
			$data['registryIsAfter20180401'] = ($res[0]['Registry_begDate'] >= $dateX20180401);
			$data['registryIsAfter20180901'] = ($res[0]['Registry_begDate'] >= $dateX20180901);
			$data['registryIsAfter20181101'] = ($res[0]['Registry_begDate'] >= $dateX20181101);
			$data['registryIsAfter20181201'] = ($res[0]['Registry_begDate'] >= $dateX20181201);
			$data['registryIsAfter20190101'] = ($res[0]['Registry_begDate'] >= $dateX20190101);
			$data['registryIsAfter20191101'] = ($res[0]['Registry_begDate'] >= $dateX20191101);

			$data['PayType_SysNick'] = null; 
			$data['Registry_IsZNO'] = $res[0]['Registry_IsZNO'];

			// Если вернули тип оплаты реестра, то будем его использовать 
			if ( isset($res[0]['PayType_SysNick']) ) {
				$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];
			}

			$this->textlog->add('exportRegistryToXml: Тип оплаты реестра: ' . $data['PayType_SysNick']);
			$this->textlog->add('exportRegistryToXml: refreshRegistry: Пересчитываем реестр');

			// Удаление помеченных на удаление записей и пересчет реестра 
			if ( $this->refreshRegistry($data) === false ) {
				throw new Exception('При обновлении данных реестра произошла ошибка.');
			}

			$this->textlog->add('exportRegistryToXml: refreshRegistry: Реестр пересчитали');
			$this->textlog->add('exportRegistryToXml: Тип реестра: ' . $type);

			// Объединенные реестры могут содержать данные любого типа
			// Получаем список типов реестров, входящих в объединенный реестр
			if ( $registryIsUnion ) {
				$registrytypes = $this->dbmodel->getUnionRegistryTypes($data['Registry_id']);// array(1, 2, 6, 7, 9, 11, 12);
				if (!is_array($registrytypes) || count($registrytypes) == 0) {
					throw new Exception('При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
				}
			} else {
				$registrytypes = array($type);
			}

			$data['Status'] = '1';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add('exportRegistryToXml: SetXmlExportStatus: Установили статус реестра в 1');

			set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

			// RegistryData 
			$number = 0;
			$Registry_EvnNum = array();
			$registry_data_res = array('ZAP'=>array(), 'PACIENT'=>array(), 'SCHET'=>array());
			foreach($registrytypes as $type) {

				$exportMethod = 'loadRegistryDataForXmlUsing' . ($data['registryIsAfter20180401'] === true ? '2018' : '');

				$registry_data_res2 = $this->dbmodel->$exportMethod($type, $data, $number, $Registry_EvnNum);

				if ( $registry_data_res2 === false ) {
					throw new Exception('Ошибка при получении данных.');
				}

				$registry_data_res['ZAP'] = array_merge($registry_data_res['ZAP'], $registry_data_res2['ZAP']);
				$registry_data_res['PACIENT'] = array_merge($registry_data_res['PACIENT'], $registry_data_res2['PACIENT']);
			}
			$registry_data_res['SCHET'] = $registry_data_res2['SCHET'];

			$this->textlog->add('exportRegistryToXml: loadRegistryDataForXmlUsing: Выбрали данные');

			if ( $registry_data_res === false ) {
				throw new Exception($this->error_deadlock);
			}
			else if ( !is_array($registry_data_res) || count($registry_data_res) == 0 ) {
				throw new Exception('Данных по требуемому реестру нет в базе данных.');
			}

			if ( $data['registryIsAfter20180401'] === false ) {
				$registry_data_res = toAnsiR($registry_data_res, true);
			}

			foreach ( $registry_data_res['ZAP'] as $row ) {
				if ( !is_array($row) || !array_key_exists('PACIENT', $row) || !is_array($row['PACIENT']) || count($row['PACIENT']) == 0 ) {
					$alertMsg = 'Выгружаемый файл не соответствует структуре объединенного реестра, необходимо переформировать реестры и выгрузить повторно.';
					break;
				}
			}

			$registry_data_person = array('PACIENT' => $registry_data_res['PACIENT']);
			unset($registry_data_res['PACIENT']); // для экономии памяти при обработке и чтобы сразу выявить шибку, когда запрос по визиту и запрос по персону возвращают разное количество записей

			$this->textlog->add('exportRegistryToXml: Получили все данные из БД ');
			$this->load->library('parser');

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];
			mkdir(EXPORTPATH_REGISTRY . $out_dir);

			$packNum = $this->dbmodel->SetXmlPackNum($data);

			if ( empty($packNum) ) {
				throw new Exception('Ошибка при получении номера выгружаемого пакета.');
			}

			$first_code = 'S';
			$data_first_code = "H";
			$pers_first_code = "L";

			if (in_array($data['PayType_SysNick'], array('bud', 'fbud', 'subrf'))) {
				switch ($type) {
					case 1: // stac
						$first_code = 'S';
						break;
					case 2: // polka
						$first_code = 'P';
						break;
				}
			} else {
				if ($registryIsUnion) {
					switch ($res[0]['RegistryGroupType_id']) {
						case 1:
						case 18:
							if ( $data['Registry_IsZNO'] == 2 ) {
								$data_first_code = "C";
								$pers_first_code = "LC";
							}
							else {
								$data_first_code = "H";
								$pers_first_code = "L";
							}
							break;
						case 2:
							$data_first_code = "T";
							$pers_first_code = "LT";
							break;
						case 3:
						case 19:
							$data_first_code = "DP";
							$pers_first_code = "LP";
							break;
						case 4:
							$data_first_code = "DV";
							$pers_first_code = "LV";
							break;
						case 5:
						case 27:
						case 28:
							$data_first_code = "DS";
							$pers_first_code = "LS";
							break;
						case 6:
						case 29:
						case 30:
							$data_first_code = "DU";
							$pers_first_code = "LU";
							break;
						case 7:
							$data_first_code = "DR";
							$pers_first_code = "LR";
							break;
						case 8:
							$data_first_code = "DD";
							$pers_first_code = "LD";
							break;
						case 9:
						case 31:
						case 32:
							$data_first_code = "DF";
							$pers_first_code = "LF";
							break;
						case 10:
							$data_first_code = "DO";
							$pers_first_code = "LO";
							break;
						case 15:
							$data_first_code = "X";
							$pers_first_code = "LX";
							break;
					}
				}
			}

			$p_code = 'T';
			if (in_array($data['PayType_SysNick'], array('bud', 'fbud', 'subrf'))) {
				$p_code = 'Z';
			}
			if (in_array($data['PayType_SysNick'], array('bud', 'fbud', 'subrf'))) {
				$f_type = 'F'; // федеральный
				if ($data['PayType_SysNick'] == 'bud') {
					$f_type = 'L'; // местный
				}
				if ($data['PayType_SysNick'] == 'subrf') {
					$f_type = 'S'; // местный
				}
				$fname_part = "M" . $registry_data_res['SCHET'][0]['CODE_MO'] . $p_code . $data['session']['region']['number'] . "_" . $data['Registry_endMonth'] . $packNum;
				$fname = $f_type . "_" . $first_code . "_H" . $fname_part;
				$rname = $data_first_code . $fname_part;
				$pname = $pers_first_code . $fname_part;
			} else {
				$fname = "M" . $registry_data_res['SCHET'][0]['CODE_MO'] . ($data['KatNasel_SysNick'] == 'oblast' ? 'S' . $OrgSmo_Code : $p_code . $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
				$rname = $data_first_code . $fname;
				$pname = $pers_first_code . $fname;
			}

			if (in_array($data['PayType_SysNick'], array('bud', 'fbud', 'subrf')) || (!empty($res[0]['RegistryGroupType_id']) && in_array($res[0]['RegistryGroupType_id'], array(1,18)) && $data['registryIsAfter20190101'] === false)) {
				$zname = $fname;
			}
			else {
				$zname = $data_first_code . $fname;
			}

			$file_re_data_sign = $rname;
			$file_re_pers_data_sign = $pname;

			// файл-тело реестра
			$file_re_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";

			// файл-перс. данные
			$file_re_pers_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";

			$registry_data_res['SCHET'][0]['DATA'] = date('Y-m-d');
			$registry_data_res['SCHET'][0]['FILENAME'] = $file_re_data_sign;
			$registry_data_res['SCHET'][0]['SD_Z'] = $number;

			if (in_array($data['PayType_SysNick'], array('bud', 'fbud', 'subrf'))) {
				$version = '1.0';
				$templateModificator = "_bud";
			} else if ($data['registryIsAfter20190101'] === true) {
				$version = '3.1';
				$templateModificator = "_2019";

				if ( $data['Registry_IsZNO'] == 2 ) {
					$templateModificator = "_zno_2019";
				}
			} else if ($data['registryIsAfter20180901'] === true) {
				$version = '3.1';
				$templateModificator = "_2018";

				if ( $data['Registry_IsZNO'] == 2 ) {
					$templateModificator = "_zno_2018";
				}
			} else {
				$version = '3.0';

				if ( $data['registryIsAfter20180401'] == true ) {
					$templateModificator = "_2018";
				}
				else {
					$templateModificator = "";
				}
			}
			$registry_data_res['SCHET'][0]['VERSION'] = $version;

			$pers_version = '2.1';
			if (in_array($data['PayType_SysNick'], array('bud', 'fbud', 'subrf'))) {
				$pers_version = '1.0';
			} else if ($data['registryIsAfter20191101'] == true) {
				$pers_version = '3.2';
			} else if ($data['registryIsAfter20181201'] == true) {
				$pers_version = '3.1';
			}

			$registry_data_person['ZGLV'] = array(
				array(
					'VERSION' => $pers_version,
					'DATA' => date('Y-m-d'),
					'FILENAME' => $file_re_pers_data_sign,
					'FILENAME1' => $file_re_data_sign
				)
			);

			$templ = "registry_kareliya_pl{$templateModificator}";
			$templ_person = "registry_" . $data['session']['region']['nick'] . "_person";

			if (!empty($res[0]['RegistryGroupType_id']) && $res[0]['RegistryGroupType_id'] == 2) {
				$templ = "registry_kareliya_htm_2019";
			}
			else if (!empty($res[0]['RegistryGroupType_id']) && !in_array($res[0]['RegistryGroupType_id'], array(1, 15, 18))) {
				$templ = "registry_kareliya_disp{$templateModificator}";
			}

			// Соответствие полей в выгрузке и необходимых названий тегов в XML-файле
			// Реализовано, т.к. парсер неправильно подставляет значения для полей с одинаковыми названиями в блоках SLUCH и USL
			$altKeys = array(
				 'LPU_USL' => 'LPU'
				,'LPU_1_USL' => 'LPU_1'
				,'PODR_USL' => 'PODR'
				,'PROFIL_USL' => 'PROFIL'
				,'DET_USL' => 'DET'
				,'USL_USL' => 'USL'
				,'TARIF_USL' => 'TARIF'
				,'PRVS_USL' => 'PRVS'
				,'P_OTK_USL' => 'P_OTK'
			);

			// записывем header
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/'.$templ.'_header', $registry_data_res['SCHET'][0], true, false, $altKeys);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml);
			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/'.$templ_person.'_header', $registry_data_person['ZGLV'][0], true);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);
			unset($xml);
			unset($xml_pers);

			// записываем body
			$toFile = array('ZAP' => array());
			foreach($registry_data_res['ZAP'] as $one) {
				$toFile['ZAP'][] = $one;
				if (count($toFile['ZAP']) >= 1000) {
					$xml = $this->parser->parse_ext('export_xml/'.$templ.'_body', $toFile, true, false, $altKeys);
					$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
					file_put_contents($file_re_data_name, $xml, FILE_APPEND);
					unset($xml);
					$toFile = array('ZAP' => array());
				}
			}
			if (count($toFile['ZAP']) > 0) {
				$xml = $this->parser->parse_ext('export_xml/'.$templ.'_body', $toFile, true, false, $altKeys);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($xml);
				$toFile = array();
			}

			$toFile = array('PACIENT' => array());
			foreach($registry_data_person['PACIENT'] as $one) {
				$toFile['PACIENT'][] = $one;
				if (count($toFile['PACIENT']) >= 1000) {
					$xml_pers = $this->parser->parse_ext('export_xml/'.$templ_person.'_body', $toFile, true, false, $altKeys);
					$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
					file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
					unset($xml_pers);
					$toFile = array('PACIENT' => array());
				}
			}
			if (count($toFile['PACIENT']) > 0) {
				$xml_pers = $this->parser->parse_ext('export_xml/'.$templ_person.'_body', $toFile, true, false, $altKeys);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
				unset($xml_pers);
				$toFile = array();
			}

			// записываем footer
			$xml = $this->parser->parse_ext('export_xml/'.$templ.'_footer', array(), true, false, $altKeys);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			$xml_pers = $this->parser->parse_ext('export_xml/'.$templ_person.'_footer', array(), true);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml);
			unset($xml_pers);

			$this->textlog->add('exportRegistryToXml: создан '.$file_re_data_name);
			$this->textlog->add('exportRegistryToXml: создан '.$file_re_pers_data_name);

			$base_name = $_SERVER["DOCUMENT_ROOT"] . "/";
			
			$file_zip_sign = $zname;
			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".zip";
			$this->textlog->add('exportRegistryToXml: Создали XML-файлы: (' . $file_re_data_name . ' и ' . $file_re_pers_data_name . ')');

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_re_data_name, $file_re_data_sign . ".xml");
			$zip->AddFile($file_re_pers_data_name, $file_re_pers_data_sign . ".xml");
			$zip->close();
			$this->textlog->add('exportRegistryToXml: Упаковали в ZIP ' . $file_zip_name);
			
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
			if($PersonData_registryValidate || $EvnData_registryValidate) $this->textlog->add('exportRegistryToXml: Почистили папку за собой ');
			
			//unlink($file_re_data_name);
			//unlink($file_re_pers_data_name);
			//$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');

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

				if ( !empty($alertMsg) ) {
					$this->ReturnData(array('Alert_Msg' => $alertMsg, 'success' => true));
				}

				$this->textlog->add("exportRegistryToXml: Передали строку на клиент {'success':true,'Link':'$file_zip_name'}");

				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);

				if (!empty($data['forSign'])) {
					$this->dbmodel->commitTransaction();
					$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
					$filebase64 = base64_encode(file_get_contents($file_zip_name));
					$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
					return true;
				} else {
					$this->textlog->add('exportRegistryToXml: Передача ссылки: '.$file_zip_name);
				}
			}
			else{
				$this->textlog->add("exportRegistryToXml: Ошибка создания архива реестра!");
				$this->ReturnError('Ошибка создания архива реестра!');
			}

			$this->textlog->add("exportRegistryToXml: Финиш");

			$this->dbmodel->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->dbmodel->rollbackTransaction();
			$this->textlog->add('exportRegistryToXml: ' . $e->getMessage());
			$this->ReturnError($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * Проверка наличия в объединенном реестре оплаченных реестров
	 */
	function checkRegistryHasPaidInside() {
		$data = $this->ProcessInputData('checkRegistryHasPaidInside', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->hasRegistryPaid($data['Registry_id'])) {
			$this->ReturnData( array('success' => true, 'existPaid' => true) ) ;
			return true;
		}

		$this->ReturnData( array('success' => true, 'existPaid' => false) ) ;
		return true;
	}

	/**
	 * Импорт реестра из СМО
	 */
	function importRegistryFromXml()
	{
		set_time_limit(0);
		
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');
		
		$data = $this->ProcessInputData('importRegistryFromXml', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->hasRegistryPaid($data['Registry_id'])) {
			$this->ReturnError('Перед импортом снимите отметку "оплачен" у всех реестров, входящих в объединенный реестр', __LINE__);
			return false;
		}

		if (!isset($_FILES['RegistryFile'])) {
			$this->ReturnError('Не выбран файл реестра!', __LINE__);
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
			$this->ReturnError($message, __LINE__);
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnError('Данный тип файла не разрешен. Доступны для загрузки файлы форматов xml, zip, rar.', __LINE__);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', __LINE__);
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnError('Загрузка файла невозможна из-за прав пользователя.', __LINE__);
			return false;
		}

		if (strtolower($file_data['file_ext']) == 'xml') {
			$xmlfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnError('Не удаётся переместить файл.', __LINE__);
				return false;
			}
		} else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) {
				$xmlfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( strtoupper(mb_substr($filename, 0, 1)) != 'L' && preg_match('/^.*\.xml$/', strtolower($filename)) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}
				
				
		if (empty($xmlfile)) {
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		// Получаем данные по реестру
		$res = $this->dbmodel->GetRegistryXmlExport($data);

		if ( !is_array($res) || count($res) == 0 ) {
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.', __LINE__);
			return false;
		}

		$data['registryIsAfter20190101'] = ($res[0]['Registry_begDate'] >= '20190101');

		$response = array('success' => false);

		if ($data['FormatType'] == 1) {
			$response = $this->importRegistryErrorSmoOld($data, $upload_path.$xmlfile);
		}
		else if ($data['FormatType'] == 2) {
			if ( $data['registryIsAfter20190101'] === true ) {
				$response = $this->importRegistryErrorSmo2019($data, $upload_path.$xmlfile);
			}
			else {
				$response = $this->importRegistryErrorSmoNew($data, $upload_path.$xmlfile);
			}
		}

		$this->ReturnData($response);

		return true;
	}

	/**
	 * Импорт результата ФЛК
	 */
	function importRegistryFLK()
	{
		set_time_limit(0);

		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');

		$data = $this->ProcessInputData('importRegistryFLK', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->hasRegistryPaid($data['Registry_id'])) {
			$this->ReturnError('Перед импортом снимите отметку "оплачен" у всех реестров, входящих в объединенный реестр', __LINE__);
			return false;
		}

		if (!isset($_FILES['RegistryFile'])) {
			$this->ReturnError('Не выбран файл реестра!', __LINE__);
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
			$this->ReturnError($message, __LINE__);
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnError('Данный тип файла не разрешен. Доступны для загрузки файлы форматов xml, zip, rar.', __LINE__);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', __LINE__);
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnError('Загрузка файла невозможна из-за прав пользователя.', __LINE__);
			return false;
		}

		if (strtolower($file_data['file_ext']) == 'xml') {
			$xmlfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnError('Не удаётся переместить файл.', __LINE__);
				return false;
			}
		} else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) {
				$xmlfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/^(FLK|V).*\.xml$/', strtolower($filename)) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}


		if (empty($xmlfile)) {
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		$dom = new DOMDocument();
		$dom->load($upload_path.$xmlfile);
		if ($dom->getElementsByTagName('FLK_P')->length < 1) {
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		// Получаем данные по реестру
		$res = $this->dbmodel->GetRegistryXmlExport($data);

		if ( !is_array($res) || count($res) == 0 ) {
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.', __LINE__);
			return false;
		}

		$response = $this->importRegistryErrorSmoOld($data, $upload_path.$xmlfile);

		$this->ReturnData($response);

		return true;
	}
	
	
	/**
	 * Импорт реестра из ТФОМС
	 */
	function importRegistryFromTFOMS()
	{
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip');

		set_time_limit(0);
		
		$data = $this->ProcessInputData('importRegistryFromTFOMS', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->hasRegistryPaid($data['Registry_id'])) {
			$this->ReturnError('Перед импортом снимите отметку "оплачен" у всех реестров, входящих в объединенный реестр', __LINE__);
			return false;
		}

		if (!isset($_FILES['RegistryFile'])) {
			$this->ReturnError('Не выбран файл реестра!', __LINE__);
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
			$this->ReturnError($message, __LINE__);
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnError('Данный тип файла не разрешен.', __LINE__);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', __LINE__);
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnError('Загрузка файла невозможна из-за прав пользователя.', __LINE__);
			return false;
		}

		$KatNasel = $this->dbmodel->getKatNasel($data['Registry_id']);

		if (!is_array($KatNasel)) {
			$this->ReturnError('Ошибка определения категории населения в реестре.', __LINE__);
			return false;
		}

		$xmlfile2 = "";
		if (strtolower($file_data['file_ext']) == 'xml') {
			$xmlfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnError('Не удаётся переместить файл.', __LINE__);
				return false;
			}
		} else {
			// там должен быть файл ah*.xml, если его нет -> файл не является архивом реестра
			// upd: доработал проверку, т.к. в ответе может быть не обязательно ah*.xml, а, например, dpm*.xml
			// @task https://redmine.swan.perm.ru/issues/108244
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) 
			{

				$xmlfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( strtoupper(mb_substr($filename, 0, 1)) != 'L' && preg_match('/' . ($KatNasel['KatNasel_SysNick'] == 'inog' ? 'a' : '') . '.+\.xml/i', strtolower($filename)) > 0 ) {
						$xmlfile = $filename;
					}

					if ( preg_match('/' . ($KatNasel['KatNasel_SysNick'] == 'inog' ? 'a' : '') . 'l.*\.xml/i', strtolower($filename)) > 0 ) {
						$xmlfile2 = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}

		if (empty($xmlfile))
		{
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		$dom = new DOMDocument();
		$dom->load($upload_path.$xmlfile);

		if ($KatNasel['KatNasel_SysNick'] == 'inog') {
			$response = $this->importRegistryErrorTfoms($data, $upload_path.$xmlfile);
		} else if ($KatNasel['KatNasel_SysNick'] == 'all' && $dom->getElementsByTagName('PR')->length > 0) {
			$response = $this->importRegistryErrorSmoOld($data, $upload_path.$xmlfile);
		} else {
			if ( empty($xmlfile2) ) {
				$this->ReturnError('Файл не является архивом реестра.', __LINE__);
				return false;
			}

			$response = $this->dbmodel->importRegistryErrorTfomsForIdent($data, $upload_path.$xmlfile, $upload_path.$xmlfile2);
		}
		$response['KatNasel_SysNick'] = $KatNasel['KatNasel_SysNick'];

		$this->ReturnData($response);
		return true;
	}

	/**
	 * Идентификация пациентов по реестру ТФОМС
	 */
	function identifyRegistryErrorTFOMS() {
		$data = $this->ProcessInputData('identifyRegistryErrorTFOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->identifyRegistryErrorTFOMS($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Импорт ошибок из СМО (старый формат, ФЛК)
	 */
	function importRegistryErrorSmoOld($data, $xmlfilepath) {
		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recall = 0;

		libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$res = $dom->load($xmlfilepath);

		foreach (libxml_get_errors() as $error) {
			return array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.'));
		}
		libxml_clear_errors();

		$params = array();
		$params['pmUser_id'] = $data['pmUser_id'];

		$dom_flkp = $dom->getElementsByTagName('FLK_P');
		foreach($dom_flkp as $dom_oneflkp) {
			// идём по ошибкам
			$dom_pr = $dom_oneflkp->getElementsByTagName('PR');
			foreach ($dom_pr as $dom_onepr) {
				$recall++;

				$params['N_ZAP'] = 0;
				$params['SL_ID'] = 0;
				$params['OSHIB'] = 0;
				$params['IM_POL'] = '';
				$params['BAS_EL'] = '';
				$params['COMMENT'] = '';

				// берём ID
				$dom_nzap = $dom_onepr->getElementsByTagName('N_ZAP');
				foreach($dom_nzap as $dom_onenzap) {
					$params['N_ZAP'] = $dom_onenzap->nodeValue;
				}
				$dom_slid = $dom_onepr->getElementsByTagName('SL_ID');
				foreach($dom_slid as $dom_oneslid) {
					$params['SL_ID'] = $dom_oneslid->nodeValue;
				}

				$params['Registry_id'] = $data['Registry_id'];

				$evnDataArray = array();
				if (!empty($params['SL_ID'])) {
					$check = $this->dbmodel->checkErrorDataInRegistryBySLID($params);
					if (!is_array($check) || count($check) == 0) {
						$this->dbmodel->deleteRegistryErrorTFOMS($params);
						return array('success' => false, 'Error_Msg' => toUTF('Номер записи SL_ID = "' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен'));
					}

					$evnDataArray[] = $check;
				} else if (!empty($params['N_ZAP'])) {
					// достаём по N_ZAP все SL_ID
					$SL_IDs = $this->dbmodel->getSLByNZAP($params);
					if (!empty($SL_IDs)) {
						foreach ($SL_IDs as $SL_ID) {
							$params['SL_ID'] = $SL_ID;
							$check = $this->dbmodel->checkErrorDataInRegistryBySLID($params);
							if (!is_array($check) || count($check) == 0) {
								$this->dbmodel->deleteRegistryErrorTFOMS($params);
								return array('success' => false, 'Error_Msg' => toUTF('Номер записи N_ZAP = "' . $params['N_ZAP'] . '", SL_ID = "' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен'));
							}

							$evnDataArray[] = $check;
						}
					} else {
						return array('success' => false, 'Error_Msg' => toUTF('Номер записи N_ZAP = "' . $params['N_ZAP'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен'));
					}
				}

				$dom_oshib = $dom_onepr->getElementsByTagName('OSHIB');
				foreach ($dom_oshib as $dom_oneoshib) {
					$params['OSHIB'] = '30' . $dom_oneoshib->nodeValue;
				}

				$dom_impol = $dom_onepr->getElementsByTagName('IM_POL');
				foreach ($dom_impol as $dom_oneimpol) {
					$params['IM_POL'] = $dom_oneimpol->nodeValue;
				}

				$dom_basel = $dom_onepr->getElementsByTagName('BAS_EL');
				foreach ($dom_basel as $dom_onebasel) {
					$params['BAS_EL'] = $dom_onebasel->nodeValue;
				}

				$dom_comment = $dom_onepr->getElementsByTagName('COMMENT');
				foreach ($dom_comment as $dom_onecomment) {
					$params['COMMENT'] = toAnsi($dom_onecomment->nodeValue);
				}

				foreach($evnDataArray as $evnData) {
					$params['Registry_id'] = $evnData['Registry_id'];
					$params['RegistryType_id'] = $evnData['RegistryType_id'];
					$params['Evn_id'] = $evnData['Evn_id'];
					$params['Evn_sid'] = $evnData['Evn_sid'];
					$params['MaxEvn_id'] = $evnData['MaxEvn_id'];
					$params['Evn_rid'] = $evnData['Evn_rid'];

					$response = $this->dbmodel->setFLKErrorFromImportRegistry($params);
					if (!is_array($response)) {
						return array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!'));
					} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
						return array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg']));
					}
				}
			}
		}

		if ($recall>0)
		{
			// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']);
		}

		//$this->dbmodel->setRegistryPaid($data);

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		return array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'Message' => toUTF('Реестр успешно загружен.'));
	}

	/**
	 * Импорт ошибок из СМО (новый формат)
	 */
	function importRegistryErrorSmoNew($data, $xmlfilepath) {
		$this->load->library('textlog', array('file'=>'importRegistryErrorSmoNew_'.date('Y-m-d').'.log', 'duration' => true, 'logging' => false));
		$this->textlog->add('');
		$this->textlog->add('importRegistryErrorSmoNew: Запуск для Registry_id = '.$data['Registry_id']);

		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$this->textlog->add('importRegistryErrorSmoNew: отработал deleteRegistryErrorTFOMS');

		$recall = 0;

		libxml_use_internal_errors(true);

		$xml = new SimpleXMLElement(file_get_contents($xmlfilepath));

		$data['setDT']=null;
		$data['disDT']=null;
		$data['PODR']=null;
		foreach (libxml_get_errors() as $error) {
			return array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.'));
		}
		libxml_clear_errors();

		$params = array();
		$params['pmUser_id'] = $data['pmUser_id'];

		// Нужен год, подтянем его из самого файла
		// @task https://redmine.swan.perm.ru/issues/83671
		$YEAR = $xml->SCHET->YEAR->__toString();

		$this->textlog->add('importRegistryErrorSmoNew: начинаем идти по записям реестра');

		foreach($xml->ZAP as $onezap) {
			$ID_PAC = $onezap->PACIENT->ID_PAC->__toString();
			$NOVOR = $onezap->PACIENT->NOVOR->__toString();
			$isErrors = false;

			$data['isSMP'] = false;

			if (preg_match("/^(\d+)(_SMP){1}$/", $ID_PAC, $matches)) {
				$ID_PAC = $matches[1];
				$data['isSMP'] = true;
			}

			$data['ID_PAC'] = $ID_PAC;
			$data['NOVOR'] = $NOVOR;

			foreach($onezap->Z_SL->SL as $onesluch) {
				$data['SL_ID'] = $onesluch->SL_ID->__toString();
				$this->textlog->add('importRegistryErrorSmoNew: проверяем наличие записи (SL_ID = '.$data['SL_ID'].') в реестре');
				$check = $this->dbmodel->checkErrorDataInRegistryBySLID($data);
				$this->textlog->add('importRegistryErrorSmoNew: отработал checkErrorDataInRegistryBySLID');
				if (!is_array($check) || count($check) == 0) {
					$this->textlog->add('importRegistryErrorSmoNew: запись не найдена, выходим');
					return array('success' => false, 'Error_Msg' => toUTF('Запись SL_ID=' . $data['SL_ID'] . ' обнаружена в импортируемом файле, но отсутствует в реестре, импорт не произведен'));
				}
				$params['Registry_id'] = $check['Registry_id'];
				$params['RegistryType_id'] = $check['RegistryType_id'];
				$params['Evn_id'] = $check['Evn_id'];
				$params['Evn_sid'] = $check['Evn_sid'];
				$params['MaxEvn_id'] = $check['MaxEvn_id'];
				$params['Evn_rid'] = $check['Evn_rid'];
				$params['RegistryType_SysNick'] = $check['RegistryType_SysNick'];
				$params['EvnClass_Code'] = $check['EvnClass_Code'];
				$params['CmpCloseCard_id'] = $check['CmpCloseCard_id'];
				$params['Lpu_CodeSMO'] = '';

				if ( in_array($params['EvnClass_Code'], array(3, 6, 7, 8, 9, 11, 13, 101, 103, 104, 111)) ) {
					// @task https://redmine.swan.perm.ru/issues/83671
					// Для реестров с 2016 года тянем значение Lpu_CodeSMO из тега AMO_CODE
					if ( $YEAR >= 2016 ) {
						$params['Lpu_CodeSMO'] = $onesluch->AMO_CODE->__toString();
					}
					else {
						$COMENTSL = $onesluch->COMENTSL->__toString();
						$commentArray = array();

						if ( !empty($COMENTSL) ) {
							$commentArray = explode('|', $COMENTSL);
						}

						if ( count($commentArray) == 5 && mb_strlen($commentArray[4]) > 0 ) {
							$params['Lpu_CodeSMO'] = mb_substr($commentArray[4], 0, 6);
						}
					}

					if ( mb_strlen($params['Lpu_CodeSMO']) > 0 ) {
						$this->textlog->add('importRegistryErrorSmoNew: запуск setLpuCodeCMOForEvn');
						$response = $this->dbmodel->setLpuCodeCMOForEvn($params);
						$this->textlog->add('importRegistryErrorSmoNew: отработал setLpuCodeCMOForEvn');

						if ( !is_array($response) ) {
							$this->textlog->add('importRegistryErrorSmoNew: ошибка при обработке реестра, выходим');
							return array('success' => false, 'Error_Msg' => 'Ошибка при обработке реестра! (строка ' . __LINE__ . ')');
						}
						else if ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
							$this->textlog->add('importRegistryErrorSmoNew: ошибка при обработке реестра: '.$response[0]['Error_Msg'].', выходим');
							return array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']);
						}
					}
				}

				// идём по ошибкам на уровне случая
				foreach ($onesluch->SANK as $onesank) {
					$params['OSHIB'] = 0;
					$params['IM_POL'] = '';
					$params['BAS_EL'] = '';
					$params['COMMENT'] = '';

					$S_OSN = $onesank->S_OSN->__toString();
					if (!empty($S_OSN)) {
						$isErrors = true;
						$params['OSHIB'] = '30'.$S_OSN;
						$params['COMMENT'] = $onesank->S_COM->__toString();
					}

					if ($isErrors) {
						$this->textlog->add('importRegistryErrorSmoNew: сохраняем ошибку');
						$response = $this->dbmodel->setErrorFromImportRegistry($params);
						$this->textlog->add('importRegistryErrorSmoNew: отработал setErrorFromImportRegistry');
						if (!is_array($response))
						{
							return array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!'));
						} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
							return array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg']));
						}
					}
				}

				// идём по ошибкам на уровне законеченного случая
				foreach($onezap->Z_SL->SANK as $onesank) {
					$params['OSHIB'] = 0;
					$params['IM_POL'] = '';
					$params['BAS_EL'] = '';
					$params['COMMENT'] = '';

					$S_OSN = $onesank->S_OSN->__toString();
					if (!empty($S_OSN)) {
						$isErrors = true;
						$params['OSHIB'] = '30'.$S_OSN;
						$params['COMMENT'] = $onesank->S_COM->__toString();
					}

					if ($isErrors) {
						$this->textlog->add('importRegistryErrorSmoNew: сохраняем ошибку');
						$response = $this->dbmodel->setErrorFromImportRegistry($params);
						$this->textlog->add('importRegistryErrorSmoNew: отработал setErrorFromImportRegistry');
						if (!is_array($response))
						{
							return array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!'));
						} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
							return array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg']));
						}
					}
				}

				if ($isErrors) {
					$recall++; // записей с ошибками
				}
			}
		}

		$this->textlog->add('importRegistryErrorSmoNew: закончили идти по записям реестра');

		if ($recall>0)
		{
			// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']);
		}

		//$this->dbmodel->setRegistryPaid($data);

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		return array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'Message' => toUTF('Реестр успешно загружен.'));
	}

	/**
	 * Импорт ошибок из СМО (ультра-новый формат)
	 */
	protected function importRegistryErrorSmo2019($data, $xmlfilepath) {
		$this->load->library('textlog', array('file'=>'importRegistryErrorSmo2019_'.date('Y-m-d').'.log', 'duration' => true, 'logging' => false));
		$this->textlog->add('');
		$this->textlog->add('importRegistryErrorSmo2019: Запуск для Registry_id = '.$data['Registry_id']);

		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$this->textlog->add('importRegistryErrorSmo2019: отработал deleteRegistryErrorTFOMS');

		$recall = 0;

		libxml_use_internal_errors(true);

		$xml = new SimpleXMLElement(file_get_contents($xmlfilepath));

		$data['setDT'] = null;
		$data['disDT'] = null;
		$data['PODR'] = null;

		foreach (libxml_get_errors() as $error) {
			return array('success' => false, 'Error_Code' => __LINE__ , 'Error_Msg' => 'Файл не является архивом реестра.');
		}

		libxml_clear_errors();

		$matches = [];
		$params = array();
		$params['pmUser_id'] = $data['pmUser_id'];

		$this->textlog->add('importRegistryErrorSmo2019: начинаем идти по записям реестра');

		foreach($xml->ZAP as $onezap) {
			$ID_PAC = $onezap->PACIENT->ID_PAC->__toString();
			$NOVOR = $onezap->PACIENT->NOVOR->__toString();
			$hasErrors = false;

			$data['isSMP'] = false;

			if (preg_match("/^(\d+)(_SMP){1}$/", $ID_PAC, $matches)) {
				$ID_PAC = $matches[1];
				$data['isSMP'] = true;
			}

			$data['ID_PAC'] = $ID_PAC;
			$data['NOVOR'] = $NOVOR;

			foreach ( $onezap->Z_SL as $onezsl ) {
				$SL_ID_list = array();

				foreach ( $onezsl->SL as $onesl ) {
					$data['SL_ID'] = $onesl->SL_ID->__toString();

					$this->textlog->add('importRegistryErrorSmo2019: проверяем наличие записи (SL_ID = ' . $data['SL_ID'] . ') в реестре');
					$check = $this->dbmodel->checkErrorDataInRegistryBySLID($data);
					$this->textlog->add('importRegistryErrorSmo2019: отработал checkErrorDataInRegistryBySLID');

					if ( !is_array($check) || count($check) == 0 ) {
						$this->textlog->add('importRegistryErrorSmo2019: запись не найдена, выходим');
						return array('success' => false, 'Error_Msg' => 'Запись SL_ID=' . $data['SL_ID'] . ' обнаружена в импортируемом файле, но отсутствует в реестре, импорт не произведен');
					}

					$params['Registry_id'] = $check['Registry_id'];
					$params['RegistryType_id'] = $check['RegistryType_id'];
					$params['Evn_id'] = $check['Evn_id'];
					$params['Evn_sid'] = $check['Evn_sid'];
					$params['MaxEvn_id'] = $check['MaxEvn_id'];
					$params['Evn_rid'] = $check['Evn_rid'];
					$params['RegistryType_SysNick'] = $check['RegistryType_SysNick'];
					$params['EvnClass_Code'] = $check['EvnClass_Code'];
					$params['CmpCloseCard_id'] = $check['CmpCloseCard_id'];
					$params['Lpu_CodeSMO'] = '';
					$params['ErrorList'] = array();

					if ( in_array($params['EvnClass_Code'], array(3, 6, 7, 8, 9, 11, 13, 101, 103, 104, 111)) ) {
						$params['Lpu_CodeSMO'] = $onesl->AMO_CODE->__toString();

						if ( mb_strlen($params['Lpu_CodeSMO']) > 0 ) {
							$this->textlog->add('importRegistryErrorSmo2019: запуск setLpuCodeCMOForEvn');
							$response = $this->dbmodel->setLpuCodeCMOForEvn($params);
							$this->textlog->add('importRegistryErrorSmo2019: отработал setLpuCodeCMOForEvn');

							if ( !is_array($response) ) {
								$this->textlog->add('importRegistryErrorSmo2019: ошибка при обработке реестра, выходим');
								return array('success' => false, 'Error_Msg' => 'Ошибка при обработке реестра! (строка ' . __LINE__ . ')');
							}
							else if ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
								$this->textlog->add('importRegistryErrorSmo2019: ошибка при обработке реестра: ' . $response[0]['Error_Msg'] . ', выходим');
								return array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']);
							}
						}
					}

					$SL_ID_list[$data['SL_ID']] = $params;
				}

				if ( count($SL_ID_list) > 0 && property_exists($onezsl, 'SANK') ) {
					foreach ( $onezsl->SANK as $onesank ) {
						$SANK_SL_ID = $onesank->SL_ID->__toString();

						foreach ( $SL_ID_list as $SL_ID => $sankParams ) {
							if ( !empty($SANK_SL_ID) && $SANK_SL_ID != $SL_ID ) {
								continue;
							}

							$S_OSN = $onesank->S_OSN->__toString();

							if ( empty($S_OSN) ) {
								continue;
							}

							$S_COM = $onesank->S_COM->__toString();
							$S_TIP = $onesank->S_TIP->__toString();

							if ( in_array($S_TIP . '|| ' . $S_OSN . '||' . $S_COM, $sankParams['ErrorList']) ) {
								continue;
							}

							$SL_ID_list[$SL_ID]['ErrorList'][] = $S_TIP . '|| ' . $S_OSN . '||' . $S_COM;

							$hasErrors = true;

							$sankParams['IM_POL'] = '';
							$sankParams['BAS_EL'] = '';
							$sankParams['OSHIB'] = '30' . $S_OSN;
							$sankParams['COMMENT'] = $S_COM;

							$this->textlog->add('importRegistryErrorSmo2019: сохраняем ошибку');
							$response = $this->dbmodel->setErrorFromImportRegistry($sankParams);
							$this->textlog->add('importRegistryErrorSmo2019: отработал setErrorFromImportRegistry');

							if ( !is_array($response) ) {
								return array('success' => false, 'Error_Msg' => 'Ошибка при обработке реестра!');
							}
							else if ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
								return array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']);
							}
						}
					}
				}

				if ( $hasErrors ) {
					$recall++; // записей с ошибками
				}
			}
		}

		$this->textlog->add('importRegistryErrorSmo2019: закончили идти по записям реестра');

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		return array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll' => $recall, 'Message' => 'Реестр успешно загружен.');
	}

	/**
	 * Импорт ответа от ТФОМС по иногородним
	 */
	function importRegistryErrorTfoms($data, $xmlfilepath) {
		$recall = 0;
		$recerr = 0;

		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistry($data);
		if (!is_array($registrydata) || !isset($registrydata[0])) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка чтения данных реестра')));
			return false;
		} else {
			$registrydata = $registrydata[0];
		}

		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$res = $dom->load($xmlfilepath);

		foreach (libxml_get_errors() as $error) {
			return array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.'));
		}
		libxml_clear_errors();

		$params = array();
		$params['pmUser_id'] = $data['pmUser_id'];

		$IDCASE = null;
		$SANK_IT = null;
		$COMENTSL = null;

		$dom_zap = $dom->getElementsByTagName('ZAP');
		foreach($dom_zap as $dom_onezap) {
			$ID_PAC = 0;
			$NOVOR = 0;

			// берём ID_PAC
			// https://redmine.swan.perm.ru/issues/64258
			$dom_pacient = $dom_onezap->getElementsByTagName('PACIENT');
			foreach($dom_pacient as $dom_onepacient) {
				$dom_idpac = $dom_onepacient->getElementsByTagName('ID_PAC');
				foreach($dom_idpac as $dom_oneidpac) {
					$ID_PAC = $dom_oneidpac->nodeValue;
				}

				$dom_novor = $dom_onepacient->getElementsByTagName('NOVOR');
				foreach($dom_novor as $dom_onenovor) {
					$NOVOR = $dom_onenovor->nodeValue;
				}
			}

			$params['isSMP'] = false;

			if (preg_match("/^(\d+)(_SMP){1}$/", $ID_PAC, $matches)) {
				$ID_PAC = $matches[1];
				$params['isSMP'] = true;
			}

			$params['ID_PAC'] = $ID_PAC;
			$params['NOVOR'] = $NOVOR;

			// идём по случаям
			$dom_zsl = $dom_onezap->getElementsByTagName('Z_SL');
			foreach ($dom_zsl as $dom_onezsl) {
				$dom_sluch = $dom_onezsl->getElementsByTagName('SL');

				foreach ($dom_sluch as $dom_onesluch) {
					$recall++;

					// берём ID
					$SL_ID = null;
					$dom_slid = $dom_onesluch->getElementsByTagName('SL_ID');
					foreach ($dom_slid as $dom_oneslid) {
						$SL_ID = $dom_oneslid->nodeValue;
					}

					$params['SL_ID'] = $SL_ID;
					$params['Registry_id'] = $data['Registry_id'];
					$check = $this->dbmodel->checkErrorDataInRegistryBySLID($params);
					if (!is_array($check) || count($check) == 0) {
						return array('success' => false, 'Error_Msg' => toUTF('Запись SL_ID=' . $SL_ID . ' обнаружена в импортируемом файле, но отсутствует в реестре, импорт не произведен'));
					}
					$params['Evn_id'] = $check['Evn_id'];
					$params['Evn_sid'] = $check['Evn_sid'];
					$params['Registry_id'] = $check['Registry_id'];
					$params['RegistryType_id'] = $check['RegistryType_id'];

					$dom_sank = $dom_onesluch->getElementsByTagName('SANK_IT');
					foreach ($dom_sank as $dom_onesank) {
						$SANK_IT = $dom_onesank->nodeValue;
					}
					$dom_comentsl = $dom_onesluch->getElementsByTagName('COMENTSL');
					foreach ($dom_comentsl as $dom_onecomentsl) {
						$COMENTSL = $dom_onecomentsl->nodeValue;
					}

					if ($SANK_IT > 0 && !empty($COMENTSL)) {
						$comments = explode(';', $COMENTSL);

						foreach ($comments as $comment) {
							$recerr++;
							$params['COMMENT'] = trim($comment);
							$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params);
							if (!is_array($response)) {
								return array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!'));
							} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
								return array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg']));
							}
						}
					}
				}
			}
		}

		//$this->dbmodel->setRegistryPaid($data);

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		return array('success' => true, 'Registry_id' => $data['Registry_id'], 'recErr' => $recerr, 'recAll'=>$recall, 'errorlink'=>'', 'Message' => toUTF('Реестр успешно загружен.'));
	}
}
