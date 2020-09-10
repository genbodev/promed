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

/**
 * Class Ekb_Registry
 * @property Ekb_Registry_model $dbmodel
 */
class Ekb_Registry extends Registry {
	var $scheme = "r66";
	var $model_name = "Registry_model";

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		// Инициализация класса и настройки
		$this->inputRules['importIdentificationData'] = array(
			array(
				'field' => 'IdentificationFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			),
			array(
					'field' => 'path',
					'label' => 'Путь к уже загруженному файлу',
					'rules' => '',
					'type' => 'string'
			)
		);
		$this->inputRules['saveRegistry'] = array(
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
				'default' => Null,
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
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
				'field' => 'RegistryEventType_id',
				'label' => 'Тип случаев реестра',
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
				'field' => 'Registry_IsRepeated',
				'label' => 'Повторная подача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsFLK',
				'label' => 'ФЛК',
				'rules' => '',
				'type' => 'checkbox'
			),
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

		$this->inputRules['checkExportRegistryToRMIS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['exportRegistryToRMIS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'fromBegin',
				'label' => 'Экспорт с начала',
				'rules' => '',
				'type' => 'checkbox'
			),
		);

		$this->inputRules['cancelExportRegistryToRMIS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);
		
		$this->inputRules['exportRegistryDataForIdentification'] = array(
			array(
				'field' => 'Registry_ids',
				'label' => 'Идентификаторы реестров',
				'rules' => 'required',
				'type' => 'json_array'
			)
		);

		$this->inputRules['importRegistryFromXlsx'] = array(
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

		$this->inputRules['importRegistryPersonCheck'] = array(
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

		$this->inputRules['importRegistryActMEC'] = array(
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
				'field' => 'RegistryGroupType_id',
				'label' => 'Тип объединенного реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номер',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_FileNum',
				'label' => 'Номер пакета',
				'rules' => 'required',
				'type' => 'int'
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
				'field' => 'Lpu_id',
				'label' => 'Лпу',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['loadRegistryDataPaid'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => 'required',
				'type' => 'id'
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
			case 1: // Уровень 1. Объединённые реестры
			{
				$childrens = array(
					array('RegistryType_id' => 13, 'RegistryType_Name' => 'Объединённые реестры'),
				);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 2: // Уровень 2. Типочки
			{
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 3: // Уровень 3. Статусы реестров
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
	 *	Экспорт реестра для ТФОМС
	 */
	function exportRegistryToXml() {
		set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ( $data === false ) { return false; }

		if ( empty($data['Registry_id']) ) {
			$this->ReturnError('Ошибка. Неверно задан идентификатор счета!');
			return false;
		}

		$this->load->library('textlog', array('file'=>'exportRegistryToXml_' . date('Y_m_d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('Запуск');
		$this->textlog->add('Идентификатор реестра: ' . $data['Registry_id']);

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

		$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
		$data['Registry_FileNum'] = $res[0]['Registry_FileNum'];
		$data['RegistryGroupType_id'] = $res[0]['RegistryGroupType_id'];
		$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];
		$data['Registry_begDate'] = $res[0]['Registry_begDate'];
		$type = $res[0]['RegistryType_id'];

		$data['registryIsAfter20180601'] = ($data['Registry_begDate'] >= '20180601');
		$data['registryIsAfter20190101'] = ($data['Registry_begDate'] >= '20190101');

		$registryIsUnion = ($type == 13);

		if ( !empty($res[0]['RegistryCheckStatus_id']) ) {
			$data['RegistryCheckStatus_id'] = $res[0]['RegistryCheckStatus_id'];
		}

		// Запрет отправки в ТФОМС реестра "Проведён контроль ФЛК"
		if ( !isSuperAdmin() && $data['send'] == 1 && $res[0]['RegistryCheckStatus_id'] === 5 ) {
			$this->textlog->add('Выход с сообщением: При статусе "Проведен контроль (ФЛК)" запрещено отправлять реестр в ТФОМС.');
			$this->ReturnError('При статусе "Проведен контроль (ФЛК)" запрещено отправлять реестр в ТФОМС');
			return false;
		}

		// Запрет экспорта и отправки в ТФОМС реестра нуждающегося в переформировании (refs #13648)
		if ( !empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2 ) {
			if ( $registryIsUnion ) {
				$this->ReturnError('Часть реестров нуждается в переформировании, экспорт невозможен.');
				$this->textlog->add('Выход с сообщением: Часть реестров нуждается в переформировании, экспорт невозможен.');
			}
			else {
				$this->ReturnError('Реестр нуждается в переформировании, экспорт невозможен.');
				$this->textlog->add('Выход с сообщением: Реестр нуждается в переформировании, экспорт невозможен.');
			}

			return false;
		}
		
		// Запрет экспорта и отправки в ТФОМС реестра при несовпадении суммы всем случаям (SUMV) итоговой сумме (SUMMAV). (refs #13806)
		if ( !empty($res[0]['Registry_SumDifference']) ) {
			// добавляем ошибку
			// $data['RegistryErrorType_Code'] = 3;
			// $res = $this->dbmodel->addRegistryErrorCom($data);
			$this->ReturnError('Экспорт невозможен. Неверная сумма по счёту и реестрам.', '12');
			return false;
		}
		
		// Запрет экспорта и отправки в ТФОМС реестра при 0 записей
		if ( empty($res[0]['RegistryData_Count']) ) {
			$this->ReturnError('Экспорт невозможен. Нет случаев в реестре.', '13');
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
				$this->ReturnError('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)', '11');
				return false;
			}
			else if ( $data['OverrideExportOneMoreOrUseExist'] == 1 ) {
				// Если реестр уже отправлен и имеет место быть попытка отправить еще раз, то не отправляем, а сообщаем что уже отправлено 
				$link = $res[0]['Registry_xmlExportPath'];
				$usePrevXml = '';

				if (empty($data['onlyLink'])) {
					$usePrevXml = 'usePrevXml: true, ';
				}

				echo "{'success':true, $usePrevXml'Link':'$link'}";
				$this->textlog->add('Выход с передачей ссылкой: '.$link);

				return true;
			}
		}

		$this->textlog->add('refreshRegistry: Пересчитываем реестр');

		// Удаление помеченных на удаление записей и пересчет реестра 
		if ( $this->refreshRegistry($data) === false ) {
			// выход с ошибкой
			$this->textlog->add('refreshRegistry: При обновлении данных реестра произошла ошибка.');
			$this->ReturnError('При обновлении данных реестра произошла ошибка.');
			return false;
		}

		$this->textlog->add('refreshRegistry: Реестр пересчитали');
		
		// Формирование XML в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			// Объединенные реестры могут содержать данные любого типа
			// Получаем список типов реестров, входящих в объединенный реестр
			if ( $registryIsUnion ) {
				$registrytypes = $this->dbmodel->getUnionRegistryTypes($data['Registry_id']);
				if ( !is_array($registrytypes) || count($registrytypes) == 0 ) {
					// выход с ошибкой
					$this->textlog->add('getUnionRegistryTypes: При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
					$this->ReturnError('При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
					return false;
				}
			}
			else {
				$registrytypes[] = $type;
			}

			$data['Status'] = '1';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add('SetXmlExportStatus: Установили статус реестра в 1');

			if ( empty($data['Registry_FileNum']) ) {
				$data['Registry_FileNum'] = $this->dbmodel->SetXmlPackNum($data);
			}

			if ( empty($data['Registry_FileNum']) ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('Выход с ошибкой: Ошибка при получении номера выгружаемого пакета.');
				$this->ReturnError('Ошибка при получении номера выгружаемого пакета.');
				return false;
			}

			$number = 0;
			$Registry_EvnNum = array();
			$SCHET = $this->dbmodel->loadRegistrySCHETForXmlUsing($data);

			$data['TemplateModificator'] = ($SCHET[0]['YEAR'] >= 2017 ? "_2017" : "");

			if($data['registryIsAfter20180601'] == true){
				$data['TemplateModificator'] = "_2018";
			}

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];
			mkdir(EXPORTPATH_REGISTRY . $out_dir);

			$registryFileNames = $this->dbmodel->getRegistryFileNameForExport(array(
				'Registry_id' => $data['Registry_id'],
				'RegistryData' => $res[0]
			));

			if ( $registryFileNames === false ) {
				$this->ReturnError('Ошибка при получении имени исходного файла.', 100020);
				return false;
			}

			$file_re_data_sign = $registryFileNames['dataFile'];
			$file_re_pers_data_sign = $registryFileNames['persFile'];

			// файл-тело реестра
			$file_re_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";

			// временный файл-тело реестра
			$file_re_data_name_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . "_tmp.xml";

			// файл-перс. данные
			$file_re_pers_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";

			$SCHET[0]['FILENAME'] = $file_re_data_sign;
			$SCHET[0]['VERSION'] = '3.2';
			$SCHET[0]['SD_Z'] = 0;
			$ZGLV = array();
			$ZGLV[0]['FILENAME1'] = $file_re_data_sign;
			$ZGLV[0]['FILENAME'] = $file_re_pers_data_sign;
			$ZGLV[0]['VERSION'] = '2.1';

			$this->load->library('parser');

			// Разбиваем на части, ибо парсер не может пережевать большие объемы данных
			$person_data_template_header = "registry_ekb_person_header" . $data['TemplateModificator'];
			$person_data_template_footer = "registry_ekb_person_footer" . $data['TemplateModificator'];
			$registry_data_template_header = "registry_ekb_pl_header" . $data['TemplateModificator'];
			$registry_data_template_footer = "registry_ekb_pl_footer" . $data['TemplateModificator'];

			// Заголовок для файла person
			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $person_data_template_header, $ZGLV[0], true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);

			foreach($registrytypes as $type) {
				$this->textlog->add('Тип реестров: ' . $type);

				$exportMethod = 'loadRegistryDataForXmlUsing' . ($data['registryIsAfter20180601'] === true ? '2018' : '');

				$SD_Z = $this->dbmodel->$exportMethod($type, $data, $number, $Registry_EvnNum, $file_re_data_name_tmp, $file_re_pers_data_name, $registryIsUnion);

				if ( $SD_Z === false ) {
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->textlog->add('Ошибка при выгрузке данных');
					$this->ReturnError('Ошибка при выгрузке данных');
					return false;
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
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->textlog->add('Ошибка при открытии файла');
					$this->ReturnError('Ошибка при открытии файла');
					return false;
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

			$file_zip_sign = $file_re_data_sign;
			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".zip";
			$this->textlog->add('Создали XML-файлы: (' . $file_re_data_name . ' и ' . $file_re_pers_data_name . ')');

			$this->textlog->add('Формируем ZIP-архив');

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_re_data_name, $file_re_data_sign . ".xml");
			$zip->AddFile($file_re_pers_data_name, $file_re_pers_data_sign . ".xml");
			$zip->close();
			$this->textlog->add('Упаковали в ZIP ' . $file_zip_name);
			
			//--------------проверка ФЛК--------------//
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
			//--------------END проверка ФЛК--------------//
			
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
				}
				else {
					$this->textlog->add('Передача ссылки: ' . $file_zip_name);
					// echo "{'success':true,'Link':'{$file_zip_name}'}";
				}

				$this->textlog->add("Передали строку на клиент {'success':true,'Link':'$file_zip_name'}");

				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);
			}
			else{
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
	 * Разрыв соединения c клиентом после запуска импорта
	 */
	function sendImportResponse() {
		ignore_user_abort(true);
		$response = array(
			"success" => "true",
			"Message" => "Обработка ответа производится в фоновом режиме",
			"type" => null,
		);

		if (function_exists('fastcgi_finish_request')) {
			$response['type'] = 1;
			echo json_encode($response);
			if (session_id()) session_write_close();
			fastcgi_finish_request();
		} else {
			ob_start();
			$response['type'] = 2;
			echo json_encode($response);

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();

			if (session_id()) session_write_close();
		}
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
	 * Импорт данных идентификации
	 */
	function importIdentificationData() {
		set_time_limit(0);
		ignore_user_abort(true);
		ini_set("memory_limit", "3096M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "2048");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "2048");

		$this->load->library('textlog', array('file'=>'importPersonIdentPackageResponse_' . date('Y-m-d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('Запуск импорта Ekb_Registry->importIdentificationData');

		// 1. проверить тип файла
		$allowed_types = explode('|','RAR|ASC');

		$sendMessage = function($message, $data) {
			$messageData = array(
				'autotype' => 1,
				'title' => 'Обработка ответа ТФОМС на запрос идентификации',
				'type' => 1,
				'User_rid' => $data['pmUser_id'],
				'pmUser_id' => $data['pmUser_id'],
				'text' => $message
			);
			$this->load->model('Messages_model');
			$this->Messages_model->autoMessage($messageData);
		};

		$data = $this->ProcessInputData('importIdentificationData', true);
		if ($data === false) { return false; }

		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));

			// Работаем с основной БД
			$this->db = $this->load->database('default', true);

			if (!isset($_FILES['IdentificationFile'])) {
				$this->ReturnData(array('success' => false, 'Error_Code' => 100011, 'Error_Msg' => 'Не выбран файл!'));
				return false;
			}
			$file = $_FILES['IdentificationFile'];

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

			// Тип файла разрешен к загрузке?
			$x = explode('.', $file['name']);
			$file_ext = mb_strtoupper(end($x));
			if (!in_array($file_ext, $allowed_types)) {
				$this->ReturnError('Данный тип файла не разрешен.', 100013);
				return false;
			}

			//посылаем ответ клиенту...
			if (empty($_REQUEST['getDebug'])) {
				$this->textlog->add('Посылаем ответ клиенту');
				$this->sendImportResponse();
			}

			//... и продолжаем выполнять скрипт на стороне сервера.
			$this->load->model('PersonIdentPackage_model');

			$stat = array('RecAll' => 0, 'RecIdent' => 0, 'RecOk' => 0, 'RecErr' => 0, 'Errors' => array());
			$response = $this->PersonIdentPackage_model->importPersonIdentPackageResponse($data, $file, $stat);

			if (isset($response[0]) && !empty($response[0]['Error_Msg'])) {
				$stat['Errors'][] = $response[0]['Error_Msg'];
			}

			$recAll = $stat['RecAll'];
			$recIdent = $stat['RecIdent'];
			$recOk = $stat['RecOk'];
			$recErr = $stat['RecErr'];

			$errors = "";
			foreach($stat['Errors'] as $error) {
				$errors .= "<div>{$error}</div>";
			}

			$message = "<div>Завершена обработка ответа ТФОМС на запрос идентификации.</div>";
			$message .= "<div>Всего записей в файле: {$recAll}.</div>";
			$message .= "<div>Распознано в Промеде: {$recIdent}.</div>";
			$message .= "<div>Успешно обработано: {$recOk}.</div>";
			$message .= "<div>Ошибок: {$recErr}.</div>";

			if ( !empty($errors) ) {
				$message .= "<div>&nbsp;</div>";
				$message .= "<div>Ошибки:</div>";
				$message .= $errors;
			}

			$sendMessage($message, $data);
			restore_error_handler();
		} catch (Exception $e) {
			restore_error_handler();
			$sendMessage('Обработка ответа от ТФОМС завершилась с ошибкой: '.$e->getMessage(), $data);
			$this->ReturnError($e->getMessage());
			return false;
		}

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Возвращает количество отправленных случаев из реестра в РМИС
	 */
	function checkExportRegistryToRMIS() {
		$data = $this->ProcessInputData('checkExportRegistryToRMIS', true);
		if ($data === false) { return false; }

		$resp = $this->dbmodel->getRegistryToRmis($data, 'count');
		if (!is_array($resp)) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Ошибка при получении количества случаев'));
		}
		$allCount = $resp[0]['EvnCount'];
		$sendCount = $resp[0]['SyncedEvnCount'];

		$response = array('Error_Msg' => '', 'allCount' => $allCount, 'sendCount' => $sendCount);

		$this->ProcessModelSave($response, true, 'Ошибка экспорта в РМИС')->ReturnData();

		return true;
	}

	/**
	 * Экспорт реестра в РМИС
	 */
	function exportRegistryToRMIS() {
		ignore_user_abort(true); // игнорирует отключение пользователя и позволяет скрипту быть запущенным постоянно
		set_time_limit(0); // это может выполняться весьма и весьма долго

		$data = $this->ProcessInputData('exportRegistryToRMIS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportRegistryToRMIS($data);
		$this->ProcessModelSave($response, true, 'Ошибка экспорта в РМИС')->ReturnData();

		return true;
	}

	/**
	 * Отмена экспорта реестра в РМИС
	 */
	function cancelExportRegistryToRMIS() {
		$data = $this->ProcessInputData('cancelExportRegistryToRMIS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->cancelExportRegistryToRMIS($data);
		$this->ProcessModelSave($response, true, 'Ошибка отмены экспорта в РМИС')->ReturnData();

		return true;
	}

	/**
	 * Импорт реестра из СМО
	 */
	function importRegistryFromXml()
	{
		
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');
		
		$data = $this->ProcessInputData('importRegistryFromXml', true);
		if ($data === false) { return false; }

		if (!isset($_FILES['RegistryFile'])) {
			$this->ReturnError('Не выбран файл реестра!', 100011) ;
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
			$this->ReturnError($message, 100012);
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnError('Данный тип файла не разрешен', 100013);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnError('Путь для загрузки файлов некорректен', 100014);
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnError('Загрузка файла не возможна из-за прав пользователя', 100015);
			return false;
		}

		if ($file_data['file_ext'] == 'xml') {
			$xmlfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnError('Не удаётся переместить файл', 100016);
				return false;
			}
		} else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) 
			{
			
				$xmlfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/.*\.xml/i', strtolower($filename)) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}
				
				
		if (empty($xmlfile)) {
			$this->ReturnError('Файл не является архивом реестра (1)', 100017);
			return false;
		}
		
		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recall = 0;

		libxml_use_internal_errors(true);
		
		$dom = new DOMDocument();
		$res = $dom->load($upload_path.$xmlfile);
		
		foreach (libxml_get_errors() as $error) {
			$this->ReturnError('Файл не является архивом реестра (2)', 100018);
			return false;
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
				$params['OSHIB'] = 0;
				$params['IM_POL'] = '';
				$params['BAS_EL'] = '';
				$params['COMMENT'] = '';
				
				// берём ID
				$dom_nzap = $dom_onepr->getElementsByTagName('N_ZAP');
				foreach($dom_nzap as $dom_onenzap) {
					$params['N_ZAP'] = $dom_onenzap->nodeValue;
				}
				
				$params['Registry_id'] = $data['Registry_id'];
				$evnData = $this->dbmodel->checkErrorDataInRegistry($params);
				if ($evnData === false) {
					$this->dbmodel->deleteRegistryErrorTFOMS($params);
					$this->ReturnError('Номер записи "'.$params['N_ZAP'].'" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен', 100019);
					return false;
				}
				
				$params['Registry_id'] = $evnData['Registry_id'];
				$params['Evn_id'] = $evnData['Evn_id'];
				
				$dom_oshib = $dom_onepr->getElementsByTagName('OSHIB');
				foreach($dom_oshib as $dom_oneoshib) {
					$params['OSHIB'] = $dom_oneoshib->nodeValue;
				}
				
				$dom_impol = $dom_onepr->getElementsByTagName('IM_POL');
				foreach($dom_impol as $dom_oneimpol) {
					$params['IM_POL'] = $dom_oneimpol->nodeValue;
				}
				
				$dom_basel = $dom_onepr->getElementsByTagName('BAS_EL');
				foreach($dom_basel as $dom_onebasel) {
					$params['BAS_EL'] = $dom_onebasel->nodeValue;
				}
				
				$dom_comment = $dom_onepr->getElementsByTagName('COMMENT');
				foreach($dom_comment as $dom_onecomment) {
					$params['COMMENT'] = toAnsi($dom_onecomment->nodeValue);
				}
				
				$response = $this->dbmodel->setErrorFromImportRegistry($params);
				if (!is_array($response)) 
				{
					$this->ReturnError('Ошибка при обработке реестра!', 100020);
					return false;
				} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
					$this->ReturnError($response[0]['Error_Msg'], 100021);
					return false;
				}
			}
		}
		
		if ($recall>0)
		{
			// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']);
		}
		
		$params = array();
		$params['RegistryData_isPaid'] = ($recall>0)?1:2;
		$params['Registry_id'] = $data['Registry_id'];
		$this->dbmodel->setRegistryDataIsPaid($params);

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'Message' => toUTF('Реестр успешно загружен.')));
		return true;
	}
	
	/**
	 * Экспорт людей для идентификации
	 */
	function exportRegistryDataForIdentification() {
		$data = $this->ProcessInputData('exportRegistryDataForIdentification', true);
		if ( $data === false ) { return true; }
		
		$response = $this->dbmodel->exportRegistryDataForIdentification($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Импорт реестра из СМО
	 */
	function importRegistryFromXlsx()
	{

		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xlsx');

		$data = $this->ProcessInputData('importRegistryFromXlsx', true);
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

		if ($file_data['file_ext'] == 'xlsx') {
			$xlsxfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xlsxfile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл .xlsx, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE)
			{

				$xlsxfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/.*.xlsx/i', $filename) > 0 ) {
						$xlsxfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}


		if (empty($xlsxfile))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}

		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recall = 0;

		$params = array();
		$params['pmUser_id'] = $data['pmUser_id'];

		try {
			require_once('vendor/autoload.php');
			$xls = \PhpOffice\PhpSpreadsheet\IOFactory::load($upload_path . $xlsxfile);
			$xls->setActiveSheetIndex(0);
			$sheet = $xls->getActiveSheet();
		} catch(Exception $e) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удалось прочитать файл.')));
			return false;
		}

		$number_n_date = explode(' ', $sheet->getCell('E8')->getValue());
		$number = $number_n_date[0];
		$date = $number_n_date[2];

		$params['Registry_id'] = $data['Registry_id'];
		$resp = $this->dbmodel->getRegistryNumberAndDate($params);
		if (!$resp) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удалось получить дату и номер выбранного реестра.')));
			return false;
		} elseif ($resp[0]['Registry_Num'] != $number || $resp[0]['Registry_accDate'] != $date) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Дата и номер счета в файле не совпадают с реестром.')));
			return false;
		}

		$arr = array();
		$startDataIndex = 0; $endDataIndex = 0;
		$rowIterator = $sheet->getRowIterator();
		foreach($rowIterator as $row) {
			$cellValue = toAnsi(trim($sheet->getCellByColumnAndRow(0, $row->getRowIndex())->getValue()));
			if ($cellValue == 'Перечень отклоненных позиций к оплате в счете (реестре)') {
				$startDataIndex = $row->getRowIndex() + 3;
			}
			if ($cellValue == 'Итого по акту на сумму:') {
				$endDataIndex = $row->getRowIndex() - 1;
				break;
			}
		}
		if ($startDataIndex > 0 && $endDataIndex > 0) {
			$startRange = 'A'.$startDataIndex;
			$endRange = 'J'.$endDataIndex;
			$arr = $sheet->rangeToArray($startRange.':'.$endRange);
			array_walk_recursive($arr, 'ConvertFromUTF8ToWin1251');
		}

		foreach ($arr as $item) {
			$recall++;

			$params['N_ZAP'] = $item[0];
			$params['OSHIB'] = $item[6];
			$params['IM_POL'] = '';
			$params['BAS_EL'] = '';
			$params['COMMENT'] = '';
			$params['ROWNUM'] = $item[0];

			$params['Registry_id'] = $data['Registry_id'];
			$evnData = $this->dbmodel->checkErrorDataInRegistry($params);
			if ($evnData === false) {
				$this->dbmodel->deleteRegistryErrorTFOMS($params);
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Номер записи "'.$params['N_ZAP'].'" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен')));
				return false;
			}

			$params['Registry_id'] = $evnData['Registry_id'];
			$params['Evn_id'] = $evnData['Evn_id'];

			$response = $this->dbmodel->setErrorFromImportRegistry($params);
			if (!is_array($response))
			{
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!')));
				return false;
			} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg'])));
				return false;
			}
		}

		if ($recall>0)
		{
			// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']);
		}

		$params = array();
		$params['RegistryData_isPaid'] = ($recall>0)?1:2;
		$params['Registry_id'] = $data['Registry_id'];
		$this->dbmodel->setRegistryDataIsPaid($params);

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'Message' => toUTF('Реестр успешно загружен.')));
		return true;
	}

	/**
	 * Импорт ответов по проверке персональных данных
	 */
	function importRegistryPersonCheck()
	{

		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xls');

		$data = $this->ProcessInputData('importRegistryFromXlsx', true);
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

		if ($file_data['file_ext'] == 'xls') {
			$xlsfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xlsfile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл .xls, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE)
			{

				$xlsfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/.*.xls/i', $filename) > 0 ) {
						$xlsfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}


		if (empty($xlsfile))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}

		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recall = 0;
		$recerr = 0;

		$params = array();
		$params['pmUser_id'] = $data['pmUser_id'];

		try {
			require_once('vendor/autoload.php');
			$xls = \PhpOffice\PhpSpreadsheet\IOFactory::load($upload_path . $xlsfile);
			$xls->setActiveSheetIndex(0);
			$sheet = $xls->getActiveSheet();
		} catch(Exception $e) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удалось прочитать файл.')));
			return false;
		}

		$number_n_date = explode(' ', $sheet->getCell('D5')->getValue());
		$number = $number_n_date[0];
		$date = $number_n_date[2];

		$filename = $sheet->getCell('D7')->getValue();

		$params['Registry_id'] = $data['Registry_id'];
		/*$resp = $this->dbmodel->getRegistryNumberAndDate($params);
		if (!$resp) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удалось получить дату и номер выбранного реестра.')));
			return false;
		} elseif ($resp[0]['Registry_Num'] != $number || $resp[0]['Registry_accDate'] != $date) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Дата и номер счета в файле не совпадают с реестром.')));
			return false;
		}*/

		$resp = $this->dbmodel->getRegistryXmlExportPath($params);
		if (!$resp) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удалось получить назввание файла выгрузки реестра.')));
			return false;
		}
		$reg_path = $resp[0]['Registry_xmlExportPath'];
		$tmparr = explode('/', $reg_path);
		$reg_filename = $tmparr[count($tmparr)-1];

		if ($filename.'.zip' != $reg_filename) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Имя файла не совпадают с реестром.')));
			return false;
		}

		$arr = array();
		$startDataIndex = 10; $endDataIndex = 0;
		$rowIterator = $sheet->getRowIterator($startDataIndex);
		foreach($rowIterator as $row) {
			$endDataIndex = $row->getRowIndex();
			$cellValue = toAnsi(trim($sheet->getCellByColumnAndRow(0, $row->getRowIndex())->getValue()));
			if (empty($cellValue)) {
				$endDataIndex = $row->getRowIndex() - 1;
				break;
			}
		}
		if ($startDataIndex > 0 && $endDataIndex > 0) {
			$startRange = 'A'.$startDataIndex;
			$endRange = 'R'.$endDataIndex;
			$arr = $sheet->rangeToArray($startRange.':'.$endRange);
			array_walk_recursive($arr, 'ConvertFromUTF8ToWin1251');
		}

		foreach ($arr as $item) {
			$recall++;

			$registry_data_status = $item[17];

			$params['N_ZAP'] = $item[0];
			$params['OSHIB'] = $registry_data_status == 2 ? '5.2.4.' : '';
			$params['IM_POL'] = '';
			$params['BAS_EL'] = '';
			$params['COMMENT'] = $filename;
			$params['FATALITY'] = $registry_data_status == 2 ? 1 : '';
			$params['ROWNUM'] = $item[0];

			$params['Registry_id'] = $data['Registry_id'];
			$evnData = $this->dbmodel->checkErrorDataInRegistry($params);
			if ($evnData === false) {
				$this->dbmodel->deleteRegistryErrorTFOMS($params);
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Номер записи "'.$params['N_ZAP'].'" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен')));
				return false;
			}

			$params['Registry_id'] = $evnData['Registry_id'];
			$params['Evn_id'] = $evnData['Evn_id'];

			if (!empty($params['OSHIB'])) {
				$recerr++;
				$response = $this->dbmodel->setErrorFromImportRegistry($params);
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

		if ($recall>0)
		{
			// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']);
		}

		$params = array();
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		// $this->dbmodel->setRegistryPaid($params);

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'Message' => toUTF('Реестр успешно загружен.')));
		return true;
	}


	/**
	 * Импорт акта МЭК
	 */
	function importRegistryActMEC()
	{

		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xlsx');

		$data = $this->ProcessInputData('importRegistryFromXlsx', true);
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

		if ($file_data['file_ext'] == 'xlsx') {
			$xlsxfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xlsxfile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл .xlsx, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE)
			{

				$xlsxfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/.*.xlsx/i', $filename) > 0 ) {
						$xlsxfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}


		if (empty($xlsxfile))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является xlsx-файлом либо его архивом.')));
			return false;
		}

		// Удаляем ответ по этому реестру, если он уже был загружен
		//$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recall = 0;
		$recerr = 0;

		$params = array();
		$params['pmUser_id'] = $data['pmUser_id'];

		try {
			require_once('vendor/autoload.php');
			$xls = \PhpOffice\PhpSpreadsheet\IOFactory::load($upload_path . $xlsxfile);
			$xls->setActiveSheetIndex(1);
			$sheet = $xls->getActiveSheet();
		} catch(Exception $e) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удалось прочитать файл.')));
			return false;
		}

		$number_n_date = explode(' ', $sheet->getCell('E9')->getValue());
		$number = $number_n_date[0];
		$date = $number_n_date[2];

		//$filename = $sheet->getCell('D7')->getValue();

		$params['Registry_id'] = $data['Registry_id'];
		$resp = $this->dbmodel->getRegistryNumberAndDate($params);
		if (!$resp) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удалось получить дату и номер выбранного реестра.')));
			return false;
		} elseif ($resp[0]['Registry_Num'] != $number || $resp[0]['Registry_accDate'] != $date) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Дата и номер счета в файле не совпадают с реестром.')));
			return false;
		}

		/*$resp = $this->dbmodel->getRegistryXmlExportPath($params);
		if (!$resp) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удалось получить название файла выгрузки реестра.')));
			return false;
		}
		$reg_path = $resp[0]['Registry_xmlExportPath'];
		$tmparr = explode('/', $reg_path);
		$reg_filename = $tmparr[count($tmparr)-1];

		if ($filename.'.zip' != $reg_filename) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Имя файла не совпадают с реестром.')));
			return false;
		}*/

		$arr = array();
		$startDataIndex = 16; $endDataIndex = 0;

		$rowIterator = $sheet->getRowIterator($startDataIndex);
		foreach($rowIterator as $row) {
			$endDataIndex = $row->getRowIndex();
			$cellValue = toAnsi(trim($sheet->getCellByColumnAndRow(0, $row->getRowIndex())->getValue()));
			if (!is_numeric($cellValue)) {
				$endDataIndex = $row->getRowIndex() - 1;
				break;
			}
		}
		if ($startDataIndex > 0 && $endDataIndex > 0) {
			$startRange = 'A'.$startDataIndex;
			$endRange = 'I'.$endDataIndex;
			$arr = $sheet->rangeToArray($startRange.':'.$endRange);
			array_walk_recursive($arr, 'ConvertFromUTF8ToWin1251');
		}

		$absentCases = array();

		foreach ($arr as $item) {
			$recall++;

			$params['N_ZAP'] = $item[0];
			$params['OSHIB'] = $item[6];
			$params['IM_POL'] = '';
			$params['BAS_EL'] = '';
			$params['ROWNUM'] = $item[0];

			$fatality = $this->dbmodel->getRegistryErrorClassId($params);
			$params['FATALITY'] = (!empty($fatality[0]['RegistryErrorClass_id'])) ? $fatality[0]['RegistryErrorClass_id'] : 1;

			$params['Registry_id'] = $data['Registry_id'];
			$evnData = $this->dbmodel->checkErrorDataInRegistryMod($params);
			if ($evnData === false) {
				array_push($absentCases, $params['N_ZAP']);
			}

			$params['Registry_id'] = $evnData['Registry_id'];
			$params['Evn_id'] = $evnData['Evn_id'];

			$resp = $this->dbmodel->getRegistryXmlExportPath($params);

			if(!empty($resp[0]['Registry_xmlExportPath'])){
				$path = $resp[0]['Registry_xmlExportPath'];
				$path = explode('/', $path);
				$params['COMMENT'] = array_pop($path);
			} else {
				$params['COMMENT'] = '';
			}

			if (!empty($params['OSHIB']) && !empty($params['Registry_id'])) {
				$recerr++;
				$response = $this->dbmodel->setErrorFromImportRegistry($params,true);
				if (!is_array($response))
				{
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!')));
					return false;
				} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg'])));
					return false;
				}
				$params['RegistryData_isPaid'] = 1;
				$this->dbmodel->setRegistryDataIsPaid($params);
			}
		}

		$params = array();
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$message = 'Реестр успешно загружен.';
		$absCount = count($absentCases);
		if($absCount>0){
			$absent = implode(', ', $absentCases);
			if($absCount == 1){
				$message .= ' Номер записи "'.$absent.'" обнаружен в импортируемом файле, но отсутствует в реестре';
			} else {
				$message .= ' Номера записей "'.$absent.'" обнаружены в импортируемом файле, но отсутствуют в реестре';
			}
		}

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'Message' => toUTF($message)));
		return true;
	}
	/**
	 * Импорт протокола ФЛК
	 */
	public function importRegistryFLK() {
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xlsx');

		$data = $this->ProcessInputData('importRegistryFromXlsx', true);
		if ($data === false) { return false; }

		if ( !isset($_FILES['RegistryFile']) ) {
			$this->ReturnError('Не выбран файл реестра!', 100011);
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

			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);

		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			$this->ReturnError('Данный тип файла не разрешен.', 100013);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			mkdir($upload_path);
		}

		if ( !@is_dir($upload_path) ) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', 100014);
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if ( !is_writable($upload_path) ) {
			$this->ReturnError('Загрузка файла не возможна из-за прав пользователя.', 100015);
			return false;
		}

		if ( $file_data['file_ext'] == 'xlsx' ) {
			$xlsxfile = time() . '_' . $_FILES['RegistryFile']['name'];

			if ( !move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path . $xlsxfile) ) {
				$this->ReturnError('Не удаётся переместить файл.', 100016);
				return false;
			}
		}
		else {
			// там должен быть файл .xlsx, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive();

			$upload_path .= time() . '/';

			// Правильно ли указана директория для загрузки?
			if ( !is_dir($upload_path) ) {
				mkdir($upload_path);
			}

			if ( !is_dir($upload_path) ) {
				$this->ReturnError('Путь для загрузки файлов некорректен.', 100017);
				return false;
			}

			// Имеет ли директория для загрузки права на запись?
			if ( !is_writable($upload_path) ) {
				$this->ReturnError('Загрузка файла не возможна из-за прав пользователя.', 100018);
				return false;
			}

			if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
				$xlsxfile = "";

				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$filename = $zip->getNameIndex($i);

					if ( preg_match('/.*\.xlsx$/i', $filename) > 0 ) {
						$xlsxfile = $filename;
					}
				}

				$zip->extractTo($upload_path);
				$zip->close();
			}

			@unlink($_FILES["RegistryFile"]["tmp_name"]);
		}

		if ( empty($xlsxfile) ) {
			$this->ReturnError('Файл не является xlsx-файлом либо его архивом.', 100019);
			return false;
		}

		// Удаляем ответ по этому реестру, если он уже был загружен
		//$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recall = 0;
		$recerr = 0;

		$params = array();
		$params['pmUser_id'] = $data['pmUser_id'];

		try {
			require_once('vendor/autoload.php');
			$xls = \PhpOffice\PhpSpreadsheet\IOFactory::load($upload_path . $xlsxfile);
			$xls->setActiveSheetIndex(0);
			$sheet = $xls->getActiveSheet();
		}
		catch ( Exception $e ) {
			$this->ReturnError('Не удалось прочитать файл.', 100020);
			return false;
		}

		$number_n_date = explode(' ', $sheet->getCell('B4')->getValue());
		$number = $number_n_date[0];
		$date = $number_n_date[2];

		$params['Registry_id'] = $data['Registry_id'];
		$resp = $this->dbmodel->getRegistryNumberAndDate($params);
		if ( !$resp ) {
			$this->ReturnError('Не удалось получить дату и номер выбранного реестра.', 100021);
			return false;
		}
		else if ( /*$resp[0]['Registry_Num'] != $number ||*/ $resp[0]['Registry_accDate'] != $date ) {
			// Убрал проверку номера
			// @task https://redmine.swan.perm.ru/issues/121574
			// $this->ReturnError('Дата и номер счета в файле не совпадают с реестром.', 100022);
			$this->ReturnError('Дата счета в файле не совпадают с реестром.', 100022);
			return false;
		}

		$arr = array();
		$startDataIndex = 9;
		$endDataIndex = 0;

		$rowIterator = $sheet->getRowIterator($startDataIndex);

		foreach ( $rowIterator as $row ) {
			$endDataIndex = $row->getRowIndex();
			$cellValue = toAnsi(trim($sheet->getCellByColumnAndRow(0, $row->getRowIndex())->getValue()));

			if ( !is_numeric($cellValue) ) {
				$endDataIndex = $row->getRowIndex() - 1;
				break;
			}
		}

		if ( $startDataIndex > 0 && $endDataIndex > 0 ) {
			$startRange = 'A'.$startDataIndex;
			$endRange = 'G'.$endDataIndex;
			$arr = $sheet->rangeToArray($startRange.':'.$endRange);
			array_walk_recursive($arr, 'ConvertFromUTF8ToWin1251');
		}

		$absentCases = array();

		foreach ( $arr as $item ) {
			$recall++;

			$params['N_ZAP'] = $item[0];
			$params['OSHIB'] = '5.2.2.';
			$params['IM_POL'] = '';
			$params['BAS_EL'] = '';
			$params['ROWNUM'] = $item[0];
			$params['COMMENT'] = '';

			$fatality = $this->dbmodel->getRegistryErrorClassId($params);
			$params['FATALITY'] = (!empty($fatality[0]['RegistryErrorClass_id'])) ? $fatality[0]['RegistryErrorClass_id'] : 1;

			$params['Registry_id'] = $data['Registry_id'];
			$evnData = $this->dbmodel->checkErrorDataInRegistryMod($params);
			if ( $evnData === false ) {
				array_push($absentCases, $params['N_ZAP']);
			}

			$params['Registry_id'] = $evnData['Registry_id'];
			$params['Evn_id'] = $evnData['Evn_id'];

			if ( !empty($params['OSHIB']) && !empty($params['Registry_id']) ) {
				$recerr++;
				$response = $this->dbmodel->setErrorFromImportRegistry($params,true);

				if ( !is_array($response) ) {
					$this->ReturnError('Ошибка при обработке реестра!', 100023);
					return false;
				}
				else if ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
					$this->ReturnError($response[0]['Error_Msg'], 100024);
					return false;
				}

				$params['RegistryData_isPaid'] = 1;

				$this->dbmodel->setRegistryDataIsPaid($params);
			}
		}

		$params = array();
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$message = 'Реестр успешно загружен.';
		$absCount = count($absentCases);

		if ( $absCount > 0 ) {
			$absent = implode(', ', $absentCases);
			if ( $absCount == 1 ) {
				$message .= ' Номер записи "'.$absent.'" обнаружен в импортируемом файле, но отсутствует в реестре';
			}
			else {
				$message .= ' Номера записей "'.$absent.'" обнаружены в импортируемом файле, но отсутствуют в реестре';
			}
		}

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'Message' => $message));

		return true;
	}
	
	/**
	 * Импорт реестра из ТФОМС
	 */
	public function importRegistryFromTFOMS() {
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');
		
		$data = $this->ProcessInputData('importRegistryFromTFOMS', true);
		if ( $data === false ) { return false; }

		if ( !isset($_FILES['RegistryFile']) ) {
			$this->ReturnError('Не выбран файл реестра!', 100011);
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

			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);

		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			$this->ReturnError('Данный тип файла не разрешен.', 100013);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			mkdir( $upload_path );
		}

		if ( !@is_dir($upload_path) ) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', 100014);
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if ( !is_writable($upload_path) ) {
			$this->ReturnError('Загрузка файла не возможна из-за прав пользователя.', 100015);
			return false;
		}

		if ( strtolower($file_data['file_ext']) == 'xml' ) {
			$xmlfile = $_FILES['RegistryFile']['name'];

			if ( !move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile) ) {
				$this->ReturnError('Не удаётся переместить файл.', 100016);
				return false;
			}
		}
		else {
			// там должен быть файл a*.xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive();

			if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
				$xmlfile = "";

				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/a.*\.xml/i', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo($upload_path);
				$zip->close();
			}

			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}
				
		if ( empty($xmlfile) ) {
			$this->ReturnError('Файл не является архивом реестра.', 100017);
			return false;
		}

		libxml_use_internal_errors(true);

		$xml = new SimpleXMLElement(file_get_contents($upload_path . $xmlfile));

		foreach ( libxml_get_errors() as $error ) {
			$this->ReturnError('Файл не является архивом реестра.', 100018);
			return false;
		}

		libxml_clear_errors();

		// Проверяем соответствие файла реестру
		$FNAME_I = $xml->FNAME_I->__toString();

		if ( empty($FNAME_I) ) {
			$this->ReturnError('Ошибка при получении имени исходного файла из загруженного файла.', 100019);
			return false;
		}

		$registryFileNames = $this->dbmodel->getRegistryFileNameForExport($data);

		if ( $registryFileNames === false ) {
			$this->ReturnError('Ошибка при получении имени исходного файла.', 100020);
			return false;
		}
		else if ( $FNAME_I != $registryFileNames['dataFile'] ) {
			$this->ReturnError('Файл ответа не соответствует реестру.', 100021);
			return false;
		}

		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recall = 0;
		$recErr = 0;
		$errorstxt = "";

		foreach ( $xml->PR as $onepr ) {
			$params = array();
			$params['OSHIB'] = '';
			$params['IM_POL'] = '';
			$params['BAS_EL'] = '';
			$params['COMMENT'] = '';
			$params['FATALITY'] = '';

			$params['N_ZAP'] = $onepr->N_ZAP->__toString();
			$params['ROWNUM'] = $onepr->N_ZAP->__toString();

			if ( empty($params['N_ZAP']) || $params['N_ZAP'] == 0 ) {
				continue;
			}

			$recall++;

			$params['Registry_id'] = $data['Registry_id'];
			$evnData = $this->dbmodel->checkErrorDataInRegistry($params);

			if ( $evnData === false ) {
				$this->dbmodel->deleteRegistryErrorTFOMS($params);
				$this->ReturnError('Номер записи "'.$params['N_ZAP'].'" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен', 100022);
				return false;
			}

			$params['Registry_id'] = $evnData['Registry_id'];
			$params['Evn_id'] = $evnData['Evn_id'];
			$params['pmUser_id'] = $data['pmUser_id'];

			$params['OSHIB'] = $onepr->OSHIB->__toString();
			$params['IM_POL'] = $onepr->IM_POL->__toString();
			$params['BAS_EL'] = $onepr->BAS_EL->__toString();
			$params['COMMENT'] = $onepr->COMMENT->__toString();
			$params['FATALITY'] = ($onepr->FATALITY->__toString() == '0'?2:1);

			if ( $params['FATALITY'] == 1 ) {
				$recErr++;
			}

			$response = $this->dbmodel->setErrorFromImportRegistry($params, true);

			if ( !is_array($response) || count($response) == 0 ) {
				$this->ReturnError('Ошибка при обработке реестра!', 100023);
				return false;
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				$this->ReturnError($response[0]['Error_Msg'], 100024);
				return false;
			}
		}

		if ( $recall > 0 ) {
			// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']);
		}

		/*$params = array();
		$params['RegistryData_isPaid'] = ($recErr>0)?1:2;
		$params['Registry_id'] = $data['Registry_id'];
		$this->dbmodel->setRegistryDataIsPaid($params);*/

		$params = array();
		$params['Registry_id'] = $data['Registry_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		// $this->dbmodel->setRegistryPaid($params);

		$errorlink = '';

		if ( !empty($errorstxt) ) {
			// Записать ошибки в файл и отдать его на клиент.
			$out_dir = "re_errors_" . time() . "_" . $data['Registry_id'];
			mkdir( EXPORTPATH_REGISTRY . $out_dir );

			$filepath = EXPORTPATH_REGISTRY.$out_dir."/errors.txt";
			if ( file_exists($filepath) )
				unlink ($filepath);

			file_put_contents($filepath, $errorstxt);

			$errorlink = $filepath;
		}

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'errorlink' => $errorlink, 'Registry_id' => $data['Registry_id'], 'recErr' => $recErr, 'recAll'=>$recall, 'Message' => 'Реестр успешно загружен.'));

		return true;
	}

	/**
	 * Отметки об оплате случаев
	 */
	function loadRegistryDataPaid()
	{
		$data = $this->ProcessInputData('loadRegistryDataPaid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryDataPaid($data);
		$this->ProcessModelList($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}
}
