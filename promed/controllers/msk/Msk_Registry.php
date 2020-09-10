<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry - операции с реестрами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @region       Msk
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Shayahmetov
* @version      023.11.2019
*/
require_once(APPPATH.'controllers/Registry.php');

class Msk_Registry extends Registry {
public $db = "registry";
public $scheme = "r50";

	/**
	* comment
	*/
	function __construct()
	{
		parent::__construct();
		
		if ($this->usePostgreRegistry) {
			unset($this->db);
			$this->load->database('postgres');
		}
		
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
				'field' => 'Lpu_id',
				'label' => 'Лпу',
				'rules' => '',
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
			)
		);
		
		$this->inputRules['exportUnionRegistryToDBFCheckExist'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);
		
		$this->inputRules['exportUnionRegistryToDBF'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
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
					if ($this->usePostgreRegistry) {
						unset($this->db);
						$this->load->database('default');
					}
					
					$this->load->model("LpuStructure_model", "lsmodel");
					$childrens = $this->lsmodel->GetLpuNodeList($data);
					
					if ($this->usePostgreRegistry) {
						unset($this->db);
						$this->load->database('postgres');
					}
					
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
	 * Функция проверяет, есть ли уже выгруженный файл реестра
	 * Входящие данные: _POST (Registry_id)
	 * На выходе: JSON-строка.
	 */
	function exportUnionRegistryToDBFCheckExist() {
		$data = $this->ProcessInputData('exportUnionRegistryToDBFCheckExist', true);
		if ($data === false) { return false; }
		
		$res = $this->dbmodel->GetUnionRegistryDBFExport($data);
		
		if ( is_array($res) && count($res) > 0 ) {
			if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
				$this->ReturnData(array('success' => true, 'exportfile' => 'empty' /*'inprogress'*/));
				return true;
			}
			else if ( !empty($res[0]['Registry_xmlExportPath']) ) {
				$this->ReturnData(array('success' => true, 'exportfile' => ($res[0]['RegistryCheckStatus_Code'] == 2 ? 'only' : '') . 'exists'));
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
	 * Функция формирует файлы в DBF формате для выгрузки данных
	 * Входящие данные: _POST (Registry_id)
	 * На выходе: JSON-строка
	 */
	function exportUnionRegistryToDBF() {
		if ( !extension_loaded('dbase') ) {
			$this->ReturnError('Не загружен модулья для работы с DBF');
			return false;
		}
		
		set_time_limit(0);
		
		$data = $this->ProcessInputData('exportUnionRegistryToDBF', true);
		if ( $data === false ) { return false; }
		
		$this->load->library('textlog', array('file'=>'exportUnionRegistryToDBF' . date('Y_m_d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('exportUnionRegistryToDBF: Запуск');
		
		// Определяем надо ли при успешном формировании проставлять статус и, соответсвенно, не выводить ссылки
		if (!isset($data['send']))
			$data['send'] = 0;
		
		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		$this->textlog->add('exportUnionRegistryToDBF: GetRegistryDBFExport: Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр');
		$res = $this->dbmodel->GetUnionRegistryDBFExport($data);
		$this->textlog->add('exportUnionRegistryToDBF: GetRegistryDBFExport: Проверка закончена');
		
		if ( !is_array($res) || count($res) == 0 ) {
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return false;
		}
		
		// Запрет экспорта и отправки в ТФОМС реестра, нуждающегося в переформировании (refs #13648)
		if ( !empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2 ) {
			$this->textlog->add('exportUnionRegistryToDBF: Выход с сообщением: Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
			$this->ReturnError('Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
			return false;
		}
		
		// Запрет экспорта реестра при 0 записей
		if ( empty($res[0]['RegistryData_Count']) ) {
			$this->textlog->add('exportUnionRegistryToDBF: Выход с сообщением: Нет записей в реестре.');
			$this->ReturnError('Экспорт невозможен. Нет случаев в реестре.', 13);
			return false;
		}
		
		$Registry_endDate = date_create($res[0]['Registry_endDate']);
		$data['Registry_endMonth'] = $Registry_endDate->format('m');
		$data['Registry_endYear'] = $Registry_endDate->format('y');
		
		$CC = '';
		switch($res[0]['KatNasel_SysNick']){
			case 'oblast':
				$CC = substr($res[0]['Orgsmo_f002smocod'], -2);
				break;
			case 'inog':
				$CC = '50';
				break;
			case 'uninsured':
				$CC = '99';
				break;
				
		}
		
		
		$this->textlog->add('exportUnionRegistryToDBF: Получили путь из БД: ' . $res[0]['Registry_xmlExportPath']);
		
		if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
			$this->textlog->add('exportUnionRegistryToDBF: Выход с сообщением: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			$this->ReturnError('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return false;
		}
		// если уже выгружен реестр
		else if ( !empty($res[0]['Registry_xmlExportPath']) ) {
			$this->textlog->add('exportUnionRegistryToDBF: Реестр уже выгружен');
			
			if ( empty($data['OverrideExportOneMoreOrUseExist']) ) {
				$this->textlog->add('exportUnionRegistryToDBF: Выход с вопросом: Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)');
				$this->ReturnError('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)', 11);
				return false;
			}
			else if ( $data['OverrideExportOneMoreOrUseExist'] == 1 ) {
				$link = $res[0]['Registry_xmlExportPath'];
				$usePrevXml = '';
				
				if ( empty($data['onlyLink']) ) {
					$usePrevXml = 'usePrevXml: true, ';
				}
				
				echo "{'success':true, $usePrevXml'Link':'$link'}";
				$this->textlog->add('exportUnionRegistryToDBF: Выход с передачей ссылкой: '.$link);
				
				return true;
			}
		}
		
		$this->textlog->add('exportUnionRegistryToDBF: refreshRegistry: Пересчитываем реестр');
		
		// Удаление помеченных на удаление записей и пересчет реестра
		if ( $this->refreshRegistry($data) === false ) {
			// выход с ошибкой
			$this->textlog->add('exportUnionRegistryToDBF: refreshRegistry: При обновлении данных реестра произошла ошибка.');
			$this->ReturnError('При обновлении данных реестра произошла ошибка.');
			return false;
		}
		
		$this->textlog->add('exportUnionRegistryToDBF: refreshRegistry: Реестр пересчитали');
		
		// Формирование DBF в зависимости от типа
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			$data['RegistryCheckStatus_id'] = null; // сбрасываем статус при новом экспорте
			$data['Status'] = '1';
			
			$this->dbmodel->SetExportStatus($data);
			$this->textlog->add('exportUnionRegistryToDBF: SetExportStatus: Установили статус реестра в 1');
			set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
			
			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_dbf_" . time() . "_".$data['Registry_id'];
			while ( file_exists(EXPORTPATH_REGISTRY . $out_dir) ) {
				$out_dir = "re_dbf_" . time() . "_".$data['Registry_id'];
			}
			
			mkdir(EXPORTPATH_REGISTRY . $out_dir);
			
			$exportParams = array(
				'sl' => array(
					'fileName' => 'U' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C',  4),
						array('CODE_LPU', 'C', 6),
						array('MPCOD', 'C', 8),
						array('MSK_OT', 'C', 2),
						array('PERSCODE', 'C', 15),
						array('VID_MP', 'C', 4),
						array('USL_OK', 'C', 1),
						array('NHISTORY', 'C', 15),
						array('PROFIL', 'C', 3),
						array('MKB1', 'C', 6),
						array('MKB2', 'C', 6),
						array('MKB3', 'C', 6),
						array('CODE_USL', 'C', 15),
						array('CODE_MD', 'C', 6),
						array('DATE_IN', 'C', 10),
						array('DATE_OUT', 'C', 10),
						array('KOL_USL', 'C', 6),
						array('KOL_FACT', 'C', 3),
						array('ISH_MOV', 'C', 3),
						array('RES_GOSP', 'C', 3),
						array('VID_SF', 'C',  2),
						array('TARIF_B', 'C', 12),
						array('TARIF_S', 'C', 12),
						array('TARIF_D', 'C', 12),
						array('SUM_RUB', 'C', 12),
						array('VID_TR', 'C',  2),
						array('EXTR', 'C', 1),
						array('CODE_OTD', 'C',  12),
						array('SOUF', 'C', 1),
						array('MED_AREA', 'C', 15),
						array('TAL_HMP', 'C', 17),
						array('DATE_HMP', 'C', 10),
						array('INV', 'C', 1),
						array('MCOD_OUT', 'C', 6),
						array('NOM_NPR', 'C', 20),
						array('DATE_NPR', 'C', 10),
						array('FOR_POM', 'C', 1),
						array('MSE', 'C', 1),
						array('P_CEL', 'C', 3),
						array('MKB0', 'C', 6),
						array('DN', 'C', 1),
						array('TAL_P', 'C', 10),
						array('DS_ONK', 'C', 1),
						array('C_ZAB', 'C', 1),
						array('PROFIL_K', 'C', 3),
						array('NAPR_MO', 'C', 6),
						array('OT_PER_U', 'C', 4),
						array('IDCASE', 'C', 16)
					)
				),
				'ksg' => array(
					'fileName' => 'G' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C', 4),
						array('CODE_LPU', 'C', 6),
						array('MSK_OT', 'C', 2),
						array('PERSCODE', 'C', 15),
						array('VID_SF', 'C', 2),
						array('VID_MP', 'C', 4),
						array('USL_OK', 'C', 1),
						array('NHISTORY', 'C', 15),
						array('PROFIL', 'C', 3),
						array('MKB1', 'C', 6),
						array('MKB2', 'C', 6),
						array('MKB3', 'C', 6),
						array('CODE_USL', 'C', 15),
						array('CODE_OTD', 'C', 12),
						array('CODE_MD', 'C', 6),
						array('DATE_IN', 'C', 10),
						array('DATE_OUT', 'C', 10),
						array('KOL_FACT', 'C', 3),
						array('ISH_MOV', 'C', 3),
						array('RES_GOSP', 'C', 3),
						array('SUM_RUB', 'C', 13),
						array('CODE_NOM1', 'C', 15),
						array('CODE_NOM2', 'C', 15),
						array('CODE_NOM3', 'C', 15),
						array('EXTR', 'C', 1),
						array('VID_TR', 'C', 2),
						array('SP_CASE', 'C', 1),
						array('KZ', 'C', 10),
						array('KUS', 'C', 9),
						array('KU', 'C', 11),
						array('KSLP', 'C', 7.5),
						array('KPS', 'C', 7),
						array('VB_P', 'C', 1),
						array('PROFIL_K', 'C', 3),
						array('P_PER', 'C', 1),
						array('DKK1', 'C', 10),
						array('DKK2', 'C', 10),
						array('Z_SL1', 'C', 14),
						array('Z_SL2', 'C', 14),
						array('Z_SL3', 'C', 14)
					)
				),
				'zno' => array(
					'fileName' => 'C' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C', 4),
						array('CODE_LPU', 'C', 6),
						array('MSK_OT', 'C', 2),
						array('PERSCODE', 'C', 15),
						array('NHISTORY', 'C', 15),
						array('PROFIL', 'C', 3),
						array('CODE_USL', 'C', 15),
						array('DATE_IN', 'C', 10),
						array('DATE_OUT', 'C', 10),
						array('DS1_T', 'C', 1),
						array('STAD', 'C', 3),
						array('ONK_T', 'C', 3),
						array('ONK_N', 'C', 3),
						array('ONK_M', 'C', 3),
						array('MTSTZ', 'C', 1),
						array('DIAG_TIP', 'C', 1),
						array('DIAG_CODE', 'C', 3),
						array('DIAG_RSLT', 'C', 3),
						array('PROT', 'C', 1),
						array('NAPR_DATE', 'C', 10),
						array('NAPR_V', 'C', 1),
						array('MET_ISSL', 'C', 1),
						array('NAPR_USL', 'C', 15),
						array('PR_CONS', 'C', 1),
						array('USL_TIP', 'C', 1),
						array('HIR_TIP', 'C', 1),
						array('LEK_TIP_L', 'C', 1),
						array('LEK_TIP_V', 'C', 1),
						array('LUCH_TIP', 'C', 1),
						array('SOD', 'C', 9),
						array('DS1_F', 'C', 1),
						array('DIAG_DATE', 'C', 10),
						array('REC_RSLT', 'C', 1),
						array('DT_CONS', 'C', 10),
						array('REGNUM', 'C', 6),
						array('DATE_INJ', 'C', 10),
						array('B_PROT', 'C', 1),
						array('K_FR', 'C', 2),
						array('WEI', 'C', 7),
						array('HEI', 'C', 3),
						array('BSA', 'C', 7),
						array('PPTR', 'C', 1),
						array('CODE_SH', 'C', 10),
						array('VID_VME', 'C', 15),
						array('NUM_CONS', 'C', 10)
					)
				),
				'person' => array(
					'fileName' => 'P' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C', 4),
						array('CODE_LPU', 'C', 6),
						array('MSK_OT', 'C', 2),
						array('PERSCODE', 'C', 15),
						array('ENP', 'C', 16),
						array('DOMC_TYPE', 'C', 2),
						array('SERIES', 'C', 12),
						array('NUMBER', 'C', 20),
						array('CODE_MSK', 'C', 2),
						array('NAME_MSK', 'C', 250),
						array('OKATO_INS', 'C', 8),
						array('FAM', 'C', 40),
						array('IM', 'C', 40),
						array('OT', 'C', 40),
						array('BIRTHDAY', 'C', 10),
						array('SEX', 'C', 2),
						array('OKATO_NAS', 'C', 11),
						array('COUNTRY', 'C', 3),
						array('PASP_SER', 'C', 10),
						array('PASP_NUM', 'C', 12),
						array('PASP_VID', 'C', 2),
						array('FAM1', 'C', 40),
						array('IM1', 'C', 40),
						array('OT1', 'C', 40),
						array('SEX_P', 'C', 2),
						array('BIRTHDAY_P', 'C', 10),
						array('PASP_SER_P', 'C', 10),
						array('PASP_NUM_P', 'C', 12),
						array('PASP_VID_P', 'C', 2),
						array('MR', 'C', 100),
						array('NOVOR', 'C', 9),
						array('OS_SLUCH', 'C', 1),
						array('SNILS', 'C', 14),
						array('VNOV', 'C', 4),
						array('DOST_FIO', 'C', 1),
						array('DOST_FIO_D', 'C', 1),
						array('DOST_DR', 'C', 1),
						array('VNOV_M', 'C', 4),
						array('PR_LG', 'C', 2),
						array('DOCDATE', 'C', 11),
						array('DOCORG', 'C', 100)
					)
				),
				'medpers' => array(
					'fileName' => 'D' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C', 4),
						array('CODE_LPU', 'C', 6),
						array('CODE_MD', 'C', 6),
						array('FIO_MD', 'C', 50),
						array('KATEG_MD', 'C', 2),
						array('SPEC_MD', 'C', 10),
						array('POST_MD', 'C', 10),
						array('MD_SS', 'C', 14),
						array('PRVS', 'C', 4)
					)
				),
				'coef' => array(
					'fileName' => 'K' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C', 4),
						array('CODE_LPU', 'C', 6),
						array('MSK_OT', 'C', 2),
						array('VID_MP', 'C', 4),
						array('USL_OK', 'C', 1),
						array('VID_SF', 'C', 2),
						array('VID_KOEFF', 'C', 2),
						array('VAL_KOEFF', 'C', 21)
					)
				),
				'disp' => array(
					'fileName' => 'M' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C', 4),
						array('CODE_LPU', 'C', 6),
						array('MSK_OT', 'C', 2),
						array('PERSCODE', 'C', 15),
						array('NHISTORY', 'C', 15),
						array('CODE_USL', 'C', 15),
						array('DATE_DVN', 'C', 10),
						array('ISP_DVN', 'C', 2),
						array('NEW_DVN', 'C', 1)
					)
				),
				/*
				 * Отказались от этого объекта в последний момент
				 * пусть пока полежит тут
				'plan' => array(
					'fileName' => 'T' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C', 4),
						array('CODE_UR', 'C', 6),
						array('USL_TMP', 'C', 2),
						array('VAL_TMP', 'C', 12)
					)
				),
				*/
				'smp' => array(
					'fileName' => 'X' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C', 4),
						array('CODE_LPU', 'C', 6),
						array('MSK_OT', 'C', 2),
						array('PERSCODE', 'C', 15),
						array('NHISTORY', 'C', 15),
						array('CODE_USL', 'C', 15),
						array('DATE_IN', 'C', 10),
						array('DATE_OUT', 'C', 10),
						array('TIME_FIX', 'C', 4),
						array('TIME_IN', 'C', 4),
						array('TIME_OUT', 'C', 4),
						array('DATE_FIX', 'C', 10)
					)
				),
				'prof' => array(
					'fileName' => 'N' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C', 4),
						array('CODE_LPU', 'C', 6),
						array('MSK_OT', 'C', 2),
						array('PERSCODE', 'C', 15),
						array('NHISTORY', 'C', 15),
						array('PROFIL', 'C', 3),
						array('CODE_USL', 'C', 15),
						array('DATE_IN', 'C', 10),
						array('DATE_OUT', 'C', 10),
						array('DISP', 'C', 3),
						array('VBR', 'C', 1),
						array('RSLT_D', 'C', 2),
						array('DS1_PR', 'C', 1),
						array('PR_D_N', 'C', 1),
						array('DS2_PR', 'C', 1),
						array('PR_DS2_N', 'C', 1),
						array('NAZ_R', 'C', 2),
						array('NAZ_SP', 'C', 4),
						array('NAZ_V', 'C', 1),
						array('NAZ_PMP', 'C', 3),
						array('NAZ_PK', 'C', 3)
					)
				),
				'stom' => array(
					'fileName' => 'Z' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.dbf',
					'DBF' => array(
						array('OT_PER', 'C', 4),
						array('CODE_LPU', 'C', 6),
						array('MSK_OT', 'C', 2),
						array('PERSCODE', 'C', 15),
						array('NHISTORY', 'C', 15),
						array('CODE_USL', 'C', 15),
						array('DATE_OUT', 'C', 10),
						array('TEETH_CODE', 'C', 3),
						array('OCCLUSION', 'C', 1)
					)
				)


			);
			
			$dataForDbfExport = $this->dbmodel->getDataForDbfExport($data['Registry_id']);
			foreach ( $exportParams as $fileType => $exportParam ) {
				$this->textlog->add("exportUnionRegistryToDBF: Формируем файл " . $exportParam['fileName']);
				
				$h = dbase_create(EXPORTPATH_REGISTRY . $out_dir . "/" . $exportParam['fileName'], $exportParam['DBF']);
				
				if (in_array($fileType, ['sl','person','zno'])) {
					$exportData = $dataForDbfExport[$fileType];
				} else if (in_array($fileType, ['medpers','ksg','coef', 'plan'])) {
					$exportData = $dataForDbfExport['sl'];
				} else {
					$exportData = [];
				}

				if(count($exportData) > 0){
					foreach ($exportData as $row) {
						$insert = [];
						foreach ($exportParam['DBF'] as $field) {
							if (array_key_exists($field[0], $row)) {
								$insert[] = $row[$field[0]];
							}
						}
						dbase_add_record($h, $insert);
					}
				}
				dbase_close($h);
				$this->textlog->add("exportUnionRegistryToDBF: Файл " . $exportParam['fileName'] . ' успешно сформирован');
			}

			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $res[0]['Lpu_Code'] . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . ".zip";
			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			
			foreach ( $exportParams as $exportParam ) {
				
				$zip->AddFile(EXPORTPATH_REGISTRY . $out_dir . "/" . $exportParam['fileName'], $exportParam['fileName']);
			}
			
			$this->load->model('ReportRun_model');
			$birtParams = array(
				'Report_Params' => urlencode('&paramRegistry=' . $data['Registry_id']),
				'Report_Format' => 'pdf',
				'Report_FileName' => 'printSvodVed_Stac_pg.rptdesign'
			);
			$response = $this->ReportRun_model->RunByFileName($birtParams, true);
			
			file_put_contents(EXPORTPATH_REGISTRY . $out_dir . "/" . 'S' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.pdf', $response);
			$zip->AddFile(EXPORTPATH_REGISTRY . $out_dir . "/" . 'S' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.pdf', 'S' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.pdf');
			$zip->close();
			
			$this->textlog->add('exportUnionRegistryToDBF: Упаковали в ZIP ' . $file_zip_name);
			
			foreach ( $exportParams as $exportParam ) {
				unlink(EXPORTPATH_REGISTRY . $out_dir . "/" . $exportParam['fileName']);
			}
			unlink(EXPORTPATH_REGISTRY . $out_dir . "/" . 'S' . $CC . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.pdf');
			$this->textlog->add('exportUnionRegistryToDBF: Почистили папку');
			
			if ( file_exists($file_zip_name) ) {
				$data['Status'] = $file_zip_name;
				
				$this->dbmodel->SetExportStatus($data);
				
				$this->textlog->add('exportUnionRegistryToDBF: Передача ссылки: ' . $file_zip_name);
				// echo "{'success':true,'Link':'{$file_zip_name}'}";
				
				$this->textlog->add("exportUnionRegistryToDBF: Передали строку на клиент {'success':true,'Link':'$file_zip_name'}");
				
				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);
			}
			else{
				throw new Exception('Ошибка создания архива реестра!');
			}
			
			$this->textlog->add("exportUnionRegistryToDBF: Финиш");
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->dbmodel->SetExportStatus($data);
			$this->textlog->add("exportUnionRegistryToDBF: " . $e->getMessage());
			$this->ReturnError($e->getMessage());
		}
		
		return true;
	}
	
	/**
	 *	Экспорт реестра в XML для ТФОМС
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
		
		// Проверяем наличие и состояние реестра
		$this->textlog->add('GetRegistryXmlExport: Проверяем наличие и состояние реестра');
		$res = $this->dbmodel->GetUnionRegistryDBFExport($data);
		$this->textlog->add('GetRegistryXmlExport: Проверка закончена');
		
		if ( !is_array($res) || count($res) == 0 ) {
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return false;
		}
		
		$type = $res[0]['RegistryType_id'];
		
		$registryIsUnion = ($type == 13);
		
		// Запрет экспорта и отправки в ТФОМС реестра нуждающегося в переформировании (refs #13648)
		if ( !empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2 ) {
			$this->textlog->add('Выход с сообщением: Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
			if ( $registryIsUnion ) {
				$this->ReturnError('Часть реестров нуждается в переформировании, экспорт невозможен.');
			}
			else {
				$this->ReturnError('Реестр нуждается в переформировании, экспорт невозможен.');
			}
			
			return false;
		}
		
		// Запрет экспорта и отправки в ТФОМС реестра при 0 записей
		if ( empty($res[0]['RegistryData_Count']) ) {
			$this->textlog->add('Выход с сообщением: Нет записей в реестре.');
			$this->ReturnError('Экспорт невозможен. Нет случаев в реестре.', '13');
			return false;
		}
		
		$this->textlog->add('Получили путь из БД:' . $res[0]['Registry_xmlExportPath']);
		
		if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
			$this->textlog->add('Выход с сообщением: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			$this->ReturnError('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return false;
		}
		else if ( strlen($res[0]['Registry_xmlExportPath']) > 0 ) { // если уже выгружен реестр
			$this->textlog->add('Реестр уже выгружен');

			if ( empty($data['OverrideExportOneMoreOrUseExist']) ) {
				$this->textlog->add('Выход с вопросом: Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)');
				$this->ReturnError('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)', 11);
				return false;
			}
			else if ( $data['OverrideExportOneMoreOrUseExist'] == 1 ) {
				// Если реестр уже отправлен и имеет место быть попытка отправить еще раз, то не отправляем, а сообщаем что уже отправлено
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
		
		$data['PayType_SysNick'] = null;
		
		// Если вернули тип оплаты реестра, то будем его использовать
		if ( isset($res[0]['PayType_SysNick']) ) {
			$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];
		}
		
		$this->textlog->add('Тип оплаты реестра: ' . $data['PayType_SysNick']);
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
		
		// В случае возникновения ошибки - необходимо снять статус равный 1
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
			}
			else {
				$registrytypes = array($type);
			}
			
			$nznumber = 0; // счётчик N_ZAP
			$Registry_EvnNum = [];
			$registry_data_res = [];
			
			foreach($registrytypes as $typeq) {
				$registry_data_res_temp = $this->dbmodel->loadRegistryDataForXmlUsing($typeq, $data, $nznumber, $Registry_EvnNum);
				
				if ( $registry_data_res_temp === false ) {
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->textlog->add('Ошибка при выгрузке данных');
					$this->ReturnError('Ошибка при выгрузке данных');
					return false;
				}
				
				$registry_data_res = array_merge($registry_data_res, $registry_data_res_temp);
			}
			
			$this->textlog->add('loadRegistryDataForXmlUsingCommon: Выбрали данные');
			
			if (count($registry_data_res) == 0 ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('Выход с ошибкой: Данных по требуемому реестру нет в базе данных.');
				$this->ReturnError('Данных по требуемому реестру нет в базе данных.');
				return false;
			}
			
			$this->load->library('parser');
			
			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];
			mkdir(EXPORTPATH_REGISTRY . $out_dir);
			
			$Registry_endDate = date_create($res[0]['Registry_endDate']);
			$data['Registry_endMonth'] = $Registry_endDate->format('m');
			$data['Registry_endYear'] = $Registry_endDate->format('y');
			
			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . "EHR" . $res[0]['Lpu_Code'] . $data['Registry_endMonth'] . $data['Registry_endYear'] . '.zip';
			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			
			$xmlfiles = [];
			foreach($registry_data_res as $smo => $zap_array){
				
				$file_stac_data = EXPORTPATH_REGISTRY . $out_dir . "/" . 'EHRS' . $smo . $data['Registry_endMonth']  . $data['Registry_endYear'] . ".xml";
				$file_polka_data = EXPORTPATH_REGISTRY . $out_dir . "/" . 'EHRA' . $smo . $data['Registry_endMonth']  . $data['Registry_endYear'] . ".xml";
				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . 'registry_msk_ehrs', array('Version' => '1.0', 'Date' => date("Y-m-d H:i:s"), 'ZAP' => $zap_array), true);
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_stac_data, $xml);
				file_put_contents($file_polka_data, ''); //Всегда пустой
				
				$zip->AddFile($file_stac_data, 'EHRS' . $smo . $data['Registry_endMonth']  . $data['Registry_endYear'] . ".xml");
				$zip->AddFile($file_polka_data, 'EHRA' . $smo . $data['Registry_endMonth']  . $data['Registry_endYear'] . ".xml");
				$xmlfiles[] = $file_stac_data;
				$xmlfiles[] = $file_polka_data;
			}
			
			$zip->close();
			$this->textlog->add('Упаковали в ZIP ' . $file_zip_name);
			
			unset($registry_data_res);
			
			foreach($xmlfiles as $xmlfile){
				unlink($xmlfile);
			}
			
			$this->textlog->add('Почистили папку за собой');
			
			if ( file_exists($file_zip_name) ) {
				$data['Status'] = $file_zip_name;
				$data['Registry_EvnNum'] = json_encode($Registry_EvnNum);
				$this->dbmodel->SetXmlExportStatus($data);
				
				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);
				
				$this->textlog->add('exportRegistryToXml: Передача ссылки: '.$file_zip_name);
				
			}
			else{
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
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
	 * Удаление реестра
	 */
	function deleteRegistry()
	{
		$data = $this->ProcessInputData('deleteRegistry', true);
		if ($data === false) { return false; }
		
		if ($this->dbmodel->checkRegistryInArchive(array('Registry_id' => $data['id']))) {
			$this->ReturnError('Дата начала реестра попадает в архивный период, все действия над реестром запрещены.');
			return false;
		}
		
		if ($this->dbmodel->checkRegistryInGroupLink(array('Registry_id' => $data['id']))) {
			$this->ReturnError('Реестр включён в объединённый, все действия над реестром запрещены.');
			return false;
		}
		
		$result = $this->checkDeleteRegistry($data);
		if ($result==true)
		{
			
			
			$response = $this->dbmodel->ObjectRecordDelete($data, "Registry", true, $data['id'], $this->scheme);
			if (isset($response[0]['Error_Message'])) { $response[0]['Error_Msg'] = $response[0]['Error_Message']; } else { $response[0]['Error_Msg'] = ''; }
			
			
			$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
		}
		else
		{
			$val = array('success' => false, 'Error_Msg' => 'Извините, удаление реестра невозможно!');
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
		}
	}
	
	/**
	 * Удаление реестра из очереди
	 */
	function deleteRegistryQueue()
	{
		$this->load->model('Utils_model', 'umodel');
		$this->load->model('Registry_model', 'dbmodel');
		$val  = array();
		
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('deleteRegistryQueue', true);
		if ($data === false) { return false; }
		
		$reform = $this->dbmodel->getRegistryQueueReformStatus($data);
		if ($reform == -1) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Реестр не найден в очереди на формирование'));
			return false;
		}
		if ($reform == 2) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Реестр в процессе формирования'));
			return false;
		}
		
		if ( $this->dbmodel->beforeDeleteRegistryQueue($data['Registry_id']) === false ) {
			$this->ReturnError('Ошибка при удалении реестра из очереди на формирование');
			return false;
		}
		
		$id = $data['Registry_id'];
		$response = $this->dbmodel->ObjectRecordDelete($data, 'RegistryQueue', true, $id, $this->scheme);
		
		if (is_array($response) && count($response) > 0)
		{
			if (!isset($response[0]['success']))
			{
				if (strlen($response[0]['Error_Message']) == 0)
				{
					// Делаем update истории реестров, если запись удалена из RegistryQueue
					$r = $this->dbmodel->closeRegistryQueueHistory($data);
					if (is_array($r) && count($r) > 0)
					{
						if (strlen($r[0]['Error_Msg']) > 0)
						{
							$val = $r[0];
						}
						else
						{
							$response[0]['success'] = true; // Удалено из очереди без ошибок
						}
					}
				}
				else
				{
					$response[0]['success'] = false;
					$response[0]['Error_Msg'] = $response[0]['Error_Message'];
				}
			}
			$val = $response[0];
		}
		else
		{
			$val = array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
		
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		
		$this->ReturnData($val);
	}
	
	/**
	 * Удаление помеченных на удаление записей и пересчет реестра
	 */
	function refreshRegistry($data)
	{
		$this->load->model('Registry_model', 'dbmodel');
		
		$response = $this->dbmodel->refreshRegistry($data);
		if ($response===false) {
			return array('Error_Msg' => 'Пересчет реестра невозможен, обратитесь к разработчикам.');
		} else {
			return true;
		}
	}
}
