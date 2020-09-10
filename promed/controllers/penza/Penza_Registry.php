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
* @version      17.10.2014
*/
require(APPPATH.'controllers/Registry.php');

class Penza_Registry extends Registry {
	var $scheme = "r58";

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
		// Инициализация класса и настройки
		
		$this->inputRules['getRegistryFileNum'] = array(
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Registry_endDate', 'label' => 'Дата окончания периода', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'RegistryGroupType_id', 'label' => 'Тип объединенного реестра', 'rules' => 'required', 'type' => 'id'),
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
				'field' => 'Registry_IsAddAcc',
				'label' => 'Признак "Дополнительный счет"',
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
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['deleteRegistry'] = array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
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
				'field' => 'importMode',
				'label' => 'Метод загрузки',
				'rules' => '',
				'type' => 'int'
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
		
		$this->inputRules['exportRegistryToXml'] = array(
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
				'field' => 'send',
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
				'field' => 'Registry_FileNum',
				'label' => 'Номер пакета',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
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
				'label' => 'МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsAddAcc',
				'label' => 'Признак "Дополнительный счет"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsOnceInTwoYears',
				'label' => 'Признак "Раз в 2 года"',
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
	 * Получение номера пакета для выгружаемых файлов
	 */
	public function getRegistryFileNum()
	{
		$data = $this->ProcessInputData('getRegistryFileNum', true);
		if ($data === false) { return false; }
		
		$Registry_FileNum = $this->dbmodel->getRegistryFileNum($data);
		$this->ReturnData(array(
			'Registry_FileNum' => $Registry_FileNum
		));

		return true;
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
	 * Отправка сообщения по почте
	 */
	function sendMail($file, $data)
	{
		// Невозможно
		return false;
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
	public function exportRegistryToXml()
	{	
		ignore_user_abort(true);
		set_time_limit(60 * 60); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится; upd: 1 час, чтобы при включенном ignore_user_abort скрипт не выполнялся вечно

		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }
		
		$this->load->library('parser');
		$this->load->library('textlog', array('file'=>'exportRegistryToXml_' . date('Y-m-d') . '.log'));

		$this->textlog->add('');
		$this->textlog->add('exportRegistryToXml: Запуск формирования реестра (Registry_id='.$data['Registry_id'].')');

		$dateX20180501 = '20180501';
		$type = 0;
		
		$res = $this->dbmodel->GetRegistryXmlExport($data);

		if ( !is_array($res) || count($res) == 0 ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return false;
		}

		$data['Registry_begDate'] = $res[0]['Registry_begDate'];
		$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
		$data['Registry_FileNum'] = $res[0]['Registry_FileNum'];
		$data['RegistryGroupType_id'] = $res[0]['RegistryGroupType_id'];

		$data['registryIsAfter20180501'] = ($data['Registry_begDate'] >= $dateX20180501);

		$templateModificator = ($data['registryIsAfter20180501'] == true ? "_2018" : "");

		if ($res[0]['Registry_xmlExportPath'] == '1')
		{
			$this->textlog->add('exportRegistryToXml: Ошибка: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			$this->ReturnError('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return false;
		}
		elseif ( !empty($res[0]['Registry_xmlExportPath']) && 1 == $data['OverrideExportOneMoreOrUseExist'] ) // если уже выгружен реестр
		{
			$link = $res[0]['Registry_xmlExportPath'];
			$this->textlog->add('exportRegistryToXml: вернули ссылку '.$link);
			echo "{'success':true,'Link':'$link'}";
			return true;
		}
		else 
		{
			$type = $res[0]['RegistryType_id'];
			$this->textlog->add('exportRegistryToXml: Тип реестра '.$res[0]['RegistryType_id']);
		}
		
		// Формирование XML в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try
		{
			$Registry_EvnNum = array();
			$SCHET = $this->dbmodel->loadRegistrySCHETForXmlUsingCommonUnion($data);

			if ( !is_array($SCHET) || count($SCHET) == 0 ) {
				$this->textlog->add('exportRegistryToXml: Ошибка при получении данных по реестру');
				$this->ReturnError('Ошибка при получении данных по реестру');
				return false;
			}

			// Объединенные реестры могут содержать данные любого типа
			$data['Status'] = '1';
			$this->dbmodel->SetXmlExportStatus($data);

			$templ_header = "registry_penza_pl_header";
			$templ_footer = "registry_penza_pl_footer";
			$templ_person_header = "registry_penza_person_header";
			$templ_person_footer = "registry_penza_person_footer";

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];
			mkdir(EXPORTPATH_REGISTRY . $out_dir);

			/*$packNum = $this->dbmodel->SetXmlPackNum($data);

			if ( empty($packNum) ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('exportRegistryToXml: Выход с ошибкой: Ошибка при получении номера выгружаемого пакета.');
				$this->ReturnError('Ошибка при получении номера выгружаемого пакета.');
				return false;
			}*/

			$packNum = sprintf('%02d', $data['Registry_FileNum']);

			// Номер счета формируем с учетом номера пакета
			// @task https://redmine.swan.perm.ru/issues/98847
			$SCHET[0]['NSCHET'] = $data['Registry_endMonth'] . '-' . $packNum . '/' . $SCHET[0]['Lpu_RegNomN2'];
			$SCHET[0]['DATA'] = date('Y-m-d');
			$SCHET[0]['VERSION'] = '2.1';
			$SCHET[0]['PN'] = array();

			if ( $data['registryIsAfter20180501'] === true ) {
				$SCHET[0]['VERSION'] = '3.1';
			}

			// Шаблон реестра и коды определяем по RegistryGroupType_id
			// @task https://redmine.swan.perm.ru/issues/98121
			switch ($data['RegistryGroupType_id']) {
				case 1: // Оказании медицинской помощи кроме высокотехнологичной
					$xml_file = "registry_penza_pl{$templateModificator}_body";

					if ( $data['registryIsAfter20180501'] === true ) {
						$templ_header = "registry_penza_pl_2018_header";
					}
				break;

				case 2: // Оказании высокотехнологичной медицинской помощи
					$xml_file = "registry_penza_hmp{$templateModificator}_body";

					if ( $data['registryIsAfter20180501'] === true ) {
						$templ_header = "registry_penza_hmp_2018_header";
					}
				break;

				case 3: // Дисп-ция взр. населения 1-ый этап
				case 4: // Дисп-ция взр. населения 2-ый этап
				case 5: // Дисп-ция детей-сирот стационарных 1-ый этап
				case 6: // Дисп-ция детей-сирот усыновленных 1-ый этап
				case 7: // Периодические осмотры несовершеннолетних
				case 8: // Предварительные осмотры несовершеннолетних
				case 9: // Профилактические осмотры несовершеннолетних
				case 10: // Профилактические осмотры взрослого населения
					$xml_file = "registry_penza_disp{$templateModificator}_body";

					if ( $data['registryIsAfter20180501'] === true ) {
						$templ_header = "registry_penza_disp_2018_header";
					}
				break;
				case 26:
					$xml_file = "registry_penza_spec_body";
					$templ_header = "registry_penza_spec_header";
				break;

				default:
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->ReturnError('Неверный тип объединенного реестра!');
					return false;
				break;
			}

			switch ($data['RegistryGroupType_id']) {
				case 1: // Оказании медицинской помощи кроме высокотехнологичной
					$first_code_zap = "HH";
					$first_code_pers = "LH";
				break;

				case 2: // Оказании высокотехнологичной медицинской помощи
					$first_code_zap = "TT";
					$first_code_pers = "LT";
				break;

				case 3: // Дисп-ция взр. населения 1-ый этап
					$first_code_zap = "DP";
					$first_code_pers = "LP";
				break;

				case 4: // Дисп-ция взр. населения 2-ый этап
					$first_code_zap = "DV";
					$first_code_pers = "LV";
				break;

				case 5: // Дисп-ция детей-сирот стационарных 1-ый этап
					$first_code_zap = "DS";
					$first_code_pers = "LS";
				break;

				case 6: // Дисп-ция детей-сирот усыновленных 1-ый этап
					$first_code_zap = "DU";
					$first_code_pers = "LU";
				break;

				case 7: // Периодические осмотры несовершеннолетних
					$first_code_zap = "DR";
					$first_code_pers = "LR";
				break;

				case 8: // Предварительные осмотры несовершеннолетних
					$first_code_zap = "DD";
					$first_code_pers = "LD";
				break;

				case 9: // Профилактические осмотры несовершеннолетних
					$first_code_zap = "DF";
					$first_code_pers = "LF";
				break;

				case 10: // Профилактические осмотры взрослого населения
					$first_code_zap = "DO";
					$first_code_pers = "LO";
				break;

				case 26: // Профилактические осмотры взрослого населения
					$first_code_zap = "V";
					$first_code_pers = "L";
				break;
			}

			$rname = $first_code_zap . "M" . $SCHET[0]['Lpu_RegNomN2'] . 'T' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
			$pname = $first_code_pers . "M" . $SCHET[0]['Lpu_RegNomN2'] . 'T' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
			$zname = $rname;

			$file_re_data_sign = $rname;
			$file_re_pers_data_sign = $pname;

			// файл-тело реестра
			$file_re_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";

			// файл-перс. данные
			$file_re_pers_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";

			$this->textlog->add('exportRegistryToXml: Определили наименования файлов: '.$file_re_data_name.' и '.$file_re_pers_data_name);

			// Формируем временный файл для тела реестра
			$data_file_path_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . swGenRandomString() . "_" . $rname . "_tmp.xml";

			while ( file_exists($data_file_path_tmp) ) {
				$data_file_path_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . swGenRandomString() . "_" . $rname . "_tmp.xml";
			}

			// Формируем временный файл для списка пациентов
			$person_file_path_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . swGenRandomString() . "_" . $pname . "_tmp.xml";

			while ( file_exists($person_file_path_tmp) ) {
				$person_file_path_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . swGenRandomString() . "_" . $pname . "_tmp.xml";
			}

			// Файл для журнала ошибок
			$error_log = EXPORTPATH_REGISTRY . $out_dir . "/" . swGenRandomString() . "_" . $rname . ".log";

			while ( file_exists($error_log) ) {
				$error_log = EXPORTPATH_REGISTRY . $out_dir . "/" . swGenRandomString() . "_" . $rname . ".log";
			}

			$exportMethod = 'loadRegistryDataForXmlUsing' . (($data['registryIsAfter20180501'] === true && $data['RegistryGroupType_id'] != 26) ? '2018' : '');

			$exportData = $this->dbmodel->$exportMethod($data, $Registry_EvnNum, $xml_file, $data_file_path_tmp, $person_file_path_tmp, $error_log);
			$this->textlog->add('exportRegistryToXml: loadRegistryDataForXmlUsing: Сформировали временный файл с данными');

			if ($exportData === false) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('exportRegistryToXml: '.$this->error_deadlock);
				$this->ReturnError($this->error_deadlock);
				return false;
			}

			$this->textlog->add('exportRegistryToXml: loadRegistryDataForXmlUsing: Количество случаев: ' . $exportData['ZSL']);

			$SCHET[0]['FILENAME'] = $file_re_data_sign;
			$SCHET[0]['SD_Z'] = $exportData['ZSL'];
			$ZGLV = array();
			$ZGLV[0]['FILENAME1'] = $file_re_data_sign;
			$ZGLV[0]['FILENAME'] = $file_re_pers_data_sign;

			// Заголовок
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/'.$templ_header, $SCHET[0], true);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml);

			// Заголовок для файла person
			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $templ_person_header, $ZGLV[0], true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);

			// Устанавливаем начитываемый объем данных
			$chunk = 10 * 1024 * 1024; // 10 MB

			// Тело файла с данными начитываем из временного (побайтно)
			if ( file_exists($data_file_path_tmp) ) {
				$fh = fopen($data_file_path_tmp, "rb");

				if ( $fh === false ) {
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->textlog->add('exportRegistryToXml: Ошибка при открытии файла');
					$this->ReturnError('Ошибка при открытии файла');
					return false;
				}

				while ( !feof($fh) ) {
					file_put_contents($file_re_data_name, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($data_file_path_tmp);
			}

			// Тело файла с пациентами начитываем из временного (побайтно)
			if ( file_exists($person_file_path_tmp) ) {
				$fh = fopen($person_file_path_tmp, "rb");

				if ( $fh === false ) {
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->textlog->add('exportRegistryToXml: Ошибка при открытии файла');
					$this->ReturnError('Ошибка при открытии файла');
					return false;
				}

				while ( !feof($fh) ) {
					file_put_contents($file_re_pers_data_name, fread($fh, $chunk), FILE_APPEND);
				}

				fclose($fh);

				unlink($person_file_path_tmp);
			}

			// Конец
			$xml = $this->parser->parse('export_xml/' . $templ_footer, array(), true);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);

			// Конец для файла person
			$xml_pers = $this->parser->parse('export_xml/' . $templ_person_footer, array(), true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);

			$file_zip_sign = $zname;
			$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
			$file_evn_num_name = EXPORTPATH_REGISTRY.$out_dir."/evnnum.txt";
			$this->textlog->add('exportRegistryToXml: Создали XML-файлы: ('.$file_re_data_name.' и '.$file_re_pers_data_name.')');
			
			$zip=new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
			$zip->AddFile( $file_re_pers_data_name, $file_re_pers_data_sign . ".xml" );
			$zip->close();
			$this->textlog->add('exportRegistryToXml: Упаковали в ZIP '.$file_zip_name);
			
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
			/*
			unlink($file_re_data_name);
			unlink($file_re_pers_data_name);
			$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');
			*/
			
			$additionalLink = '';
			if ( file_exists($error_log) && filesize($error_log) > 0 ) {
				$zip = new ZipArchive();
				$zip->open($error_log . '.zip', ZIPARCHIVE::CREATE);
				$zip->AddFile($error_log, "error.log");
				$zip->close();
				$this->textlog->add('exportRegistryToXml: Упаковали в ZIP журнал ошибок ' . $error_log . '.zip');
				$additionalLink = '<br /><a href="' . $error_log . '.zip' . '" target="_blank">дополнительный лог-файл</a>';
			}

			if (!$PersonData_registryValidate || !$EvnData_registryValidate) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
					<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
					<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
					<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
					<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>' . $additionalLink
				);
			}
			elseif (!$PersonData_registryValidate) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br>
					<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
					<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
					<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>' . $additionalLink
				);
			}
			elseif (!$EvnData_registryValidate) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p><br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
					<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
					<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>' . $additionalLink
				);
			}
			elseif (!empty($additionalLink)) {
				$data['Status'] = $file_zip_name;
				$this->saveRegistryEvnNum(array(
					'Registry_EvnNum' => $Registry_EvnNum,
					'Registry_id' => $data['Registry_id'],
					'FileName' => $file_evn_num_name
				));
				$data['Registry_EvnNum'] = null; // в бд не сохраняем, храним в файле
				$data['Registry_FileNameCase'] = $file_re_data_sign;
				$data['Registry_FileNamePersonalData'] = $file_re_pers_data_sign;
				$data['Registry_CaseCount'] = $exportData['SL_ID'];
				$data['Registry_PersonalDataCount'] = $exportData['N_ZAP'];

				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p>
					<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>' . $additionalLink
				);
			}
			elseif (file_exists($file_zip_name)) {
				//header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
				//header("Content-Type: text/html");
				//header("Pragma: no-cache");
				$data['Status'] = $file_zip_name;
				$this->saveRegistryEvnNum(array(
					'Registry_EvnNum' => $Registry_EvnNum,
					'Registry_id' => $data['Registry_id'],
					'FileName' => $file_evn_num_name
				));
				$data['Registry_EvnNum'] = null; // в бд не сохраняем, храним в файле
				$data['Registry_FileNameCase'] = $file_re_data_sign;
				$data['Registry_FileNamePersonalData'] = $file_re_pers_data_sign;
				$data['Registry_CaseCount'] = $exportData['SL_ID'];
				$data['Registry_PersonalDataCount'] = $exportData['N_ZAP'];

				$this->dbmodel->SetXmlExportStatus($data);
				//echo "{'success':true,'Link':'$file_zip_name'}";
				$this->textlog->add("exportRegistryToXml: Все закончилось, вроде успешно.");

				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);
			}
			else {
				$this->textlog->add("exportRegistryToXml: Ошибка создания архива реестра!");
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка создания архива реестра!')));
			}
			$this->textlog->add("exportRegistryToXml: Финиш");
		}
		catch (Exception $e)
		{
			$data['Status'] = '';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add("exportRegistryToXml:".toUtf($e->getMessage()));
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf($e->getMessage())));
		}
	}

	/**
	 * Сохранение Registry_EvnNum
	 */
	function saveRegistryEvnNum($data) {
		$toWrite = array();
		foreach($data['Registry_EvnNum'] as $key => $record) {
			$toWrite[$key] = $record;
			if (count($toWrite) >= 1000) {
				$str = json_encode($toWrite).PHP_EOL;
				file_put_contents($data['FileName'], $str, FILE_APPEND);
				$toWrite = array();
			}
		}

		if (count($toWrite) > 0) {
			$str = json_encode($toWrite).PHP_EOL;
			file_put_contents($data['FileName'], $str, FILE_APPEND);
		}
	}

	/**
	 * Получение Registry_EvnNum
	 */
	function getRegistryEvnNum($data) {
		if (!empty($data['Registry_EvnNum'])) {
			return json_decode($data['Registry_EvnNum'], true);
		} else if (!empty($data['Registry_xmlExportPath'])) {
			$filename = basename($data['Registry_xmlExportPath']);
			$evnNumPath = str_replace('/'.$filename, '/evnnum.txt', $data['Registry_xmlExportPath']);
			if (file_exists($evnNumPath)) {
				$fileContents = file_get_contents($evnNumPath);
				$exploded = explode(PHP_EOL, $fileContents);
				$Registry_EvnNum = array();
				foreach($exploded as $one) {
					if (!empty($one)) {
						$unjsoned = json_decode($one, true);
						if (is_array($unjsoned)) {
							foreach ($unjsoned as $key => $value) {
								$Registry_EvnNum[$key] = $value;
							}
						}
					}
				}
				return $Registry_EvnNum;
			}
		}

		return array();
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
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml_check_volume/registry_penza_pl', $registry_data_res, true);
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
	 * Импорт реестра из XML (ФЛК/МЭК)
	 */
	public function importRegistryFromXml() {
		ignore_user_abort(true);
		set_time_limit(60 * 60); // 1 час, чтобы при включенном ignore_user_abort скрипт не выполнялся вечно

		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');
		
		$data = $this->ProcessInputData('importRegistryFromXml', true);
		if ( $data === false ) { return false; }

		try {
			if ( !isset($_FILES['RegistryFile']) ) {
				throw new Exception('Не выбран файл реестра!');
			}

			if ( !is_uploaded_file($_FILES['RegistryFile']['tmp_name']) ) {
				$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];

				switch ( $error ) {
					case 1:
						$message = 'Загружаемый файл превышает максимально допустимый размер.';
						break;
					case 2:
						$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
						break;
					case 3:
						$message = 'Файл был загружен не полностью.';
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

				throw new Exception($message);
			}
			
			// Тип файла разрешен к загрузке?
			$x = explode('.', $_FILES['RegistryFile']['name']);
			$file_data['file_ext'] = strtolower(end($x));

			if ( !in_array($file_data['file_ext'], $allowed_types) ) {
				throw new Exception('Данный тип файла не разрешен.');
			}

			$path = '';
			$folders = explode('/', $upload_path);

			for ( $i = 0; $i < count($folders); $i++ ) {
				$path .= $folders[$i] . '/';

				if ( !@is_dir($path) ) {
					mkdir($path);
				}
			}

			if ( !@is_dir($upload_path) ) {
				throw new Exception('Путь для загрузки файлов некорректен.');
			}
			
			// Имеет ли директория для загрузки права на запись?
			if ( !is_writable($upload_path) ) {
				throw new Exception('Загрузка файла невозможна из-за прав пользователя.');
			}

			if ( $file_data['file_ext'] == 'xml' ) {
				$xmlfile = $_FILES['RegistryFile']['name'];

				if ( !move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path . $xmlfile) ) {
					throw new Exception('Не удаётся переместить файл.');
				}
			}
			else {
				$zip = new ZipArchive();

				if ( $zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE ) {
					$xmlfile = "";

					for ( $i = 0; $i < $zip->numFiles; $i++ ) {
						$filename = $zip->getNameIndex($i);

						if ( preg_match('/^.*\.xml$/', $filename) > 0 && strtoupper(substr($filename, 0, 1)) != 'L' ) {
							$xmlfile = $filename;
						}
					}

					$zip->extractTo($upload_path);
					$zip->close();
				}

				unlink($_FILES["RegistryFile"]["tmp_name"]);
			}

			if ( empty($xmlfile) ) {
				throw new Exception('Файл не является архивом реестра.');
			}

			$dateX20180501 = '2018-05-01';
			$recall = 0;
			$recerr = 0;
			
			// получаем данные реестра
			$registrydata = $this->dbmodel->loadRegistryForImport($data);

			if ( empty($registrydata) || $registrydata === false ) {
				throw new Exception('Ошибка чтения данных реестра');
			}

			$is2018 = ($registrydata['Registry_begDate'] >= $dateX20180501);

			$rdata = array();
			$rdatakeys = array();
			$Registry_EvnNum = array();

			$caseField = ($is2018 == true ? 'SL_ID' : 'IDCASE');

			if (!empty($data['importMode']) && $data['importMode'] == 2) {
				// для этого метода нам нужен список всех записей в реестре, а именно соответствие между DATE_1/DATE_2/DS1/PersonEvn_id и Evn_id/Registry_id
				$rdata = $this->dbmodel->getRegistryDataForImport(array(
					'Registry_id' => $data['Registry_id']
				));
				$rdatakeys = array_keys($rdata);
			} else {
				// для этого лишь связь IDCASE/SL_ID и Evn_id/Registry_id из Registry_EvnNum
				$Registry_EvnNum = $this->getRegistryEvnNum($registrydata);
			}
			
			// Удаляем ответ по этому реестру, если он уже был загружен
			$this->dbmodel->deleteRegistryErrorTFOMS($data);

			// Отсюда начинаем обработку по частям
			$xmlString = file_get_contents($upload_path.$xmlfile);
			// либо начитываем построчно

			$checkString = substr($xmlString, 0, 2048);

			if ( strpos($checkString, '<ZAP>') !== false ) {
				$importMode = 'MEK';
			}
			else if ( strpos($checkString, '<PR>') !== false ) {
				$importMode = 'FLK';
				unset($xmlString);
			}
			else {
				throw new Exception('Неизвестный формат файла.');
			}
			
			libxml_use_internal_errors(true);

			$params = array();
			$params['pmUser_id'] = $data['pmUser_id'];

			if ( $is2018 === true ) {
				switch ( $importMode ) {
					case 'MEK':
						$importMode = 'Импорт МЭК';

						$header = substr($checkString, 0, strpos($checkString, '</SCHET>') + strlen('</SCHET>'));
						$footer = '</ZL_LIST>';

						unset($checkString);

						$xmlString = substr($xmlString, strlen($header));

						// 20 MB
						$chunkSize = 1024 * 1024 * 20;

						// список идешников SL_ID случаев, которые есть в реестре-ответе
						$SLIDArray = array();

						while ( !empty($xmlString) ) {
							// Нагребаем остатки, если размер оставшегося куска файла меньше $chunkSize МБ
							if ( strlen($xmlString) <= $chunkSize + strlen($footer) + 2 /* учтем перевод строки */ ) {
								$xmlData = substr($xmlString, 0, strlen($xmlString) - strlen($footer));
								$xmlString = '';
							}
							// или данные по $chunkSize МБ
							else {
								$xmlData = substr($xmlString, 0, $chunkSize);
								$xmlString = substr($xmlString, $chunkSize);

								if ( strpos($xmlString, '</ZAP>') !== false ) {
									$xmlData .= substr($xmlString, 0, strpos($xmlString, '</ZAP>') + strlen('</ZAP>'));
									$xmlString = substr($xmlString, strpos($xmlString, '</ZAP>') + strlen('</ZAP>'));

									if ( trim($xmlString) == $header ) {
										$xmlString = '';
									}
								}
							}

							$xml = new SimpleXMLElement($header . $xmlData . $footer);

							foreach ( libxml_get_errors() as $error ) {
								if ( $error->code != 100 ) { // Игнорирование ошибки xmlns: URI OMS-D1 is not absolute
									throw new Exception('Файл не является архивом реестра.');
								}
							}

							if ( property_exists($xml, 'SCHET') && $xml->SCHET->CODE->__toString() != $data['Registry_id']) {
								throw new Exception('Поле CODE в импортируемом файле отличается от реестра, импорт не произведен.');
							}

							libxml_clear_errors();

							if ( !property_exists($xml, 'ZAP') ) {
								$xmlString = '';
								continue;
							}

							foreach ( $xml->ZAP as $onezap ) {
								$ID_PAC = $onezap->PACIENT->ID_PAC->__toString();

								foreach ( $onezap->Z_SL as $onezsl ) {
									$IDCASE = $onezsl->IDCASE->__toString();

									foreach ( $onezsl->SL as $onesl ) {
										$recall++;

										$SL_ID = $onesl->SL_ID->__toString();
										$COMENTSL = $onesl->COMENTSL->__toString();
										$REFREASON = $onesl->REFREASON->__toString();

										$params['Registry_id'] = $data['Registry_id'];
										$params['Registry_EvnNum'] = $registrydata['Registry_EvnNum'];

										if (!empty($data['importMode']) && $data['importMode'] == 2) {
											$params['DATE_1'] = $onesl->DATE_1->__toString();
											$params['DATE_2'] = $onesl->DATE_2->__toString();
											$params['DS1'] = $onesl->DS1->__toString();
											$params['PersonEvn_id'] = preg_replace('/\_.*/', '', $ID_PAC);

											if (isset($rdata['pe_'.$params['PersonEvn_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']])) {
												$check = $rdata['pe_'.$params['PersonEvn_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']];
												$rdata['pe_'.$params['PersonEvn_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']]['cnt']++;
											} else {
												if (empty($params['DS1'])) {
													$keyPattern = 'pe_'.$params['PersonEvn_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_';
													$keyLength = strlen($keyPattern);

													foreach ( $rdatakeys as $key ) {
														if ( substr($key, 0, $keyLength) == $keyPattern ) {
															$check = $rdata[$key];
															$rdata[$key]['cnt']++;
															break;
														}
													}
												}
												else {
													// получаем Person_id и проверяем по нему
													$params['Person_id'] = $this->dbmodel->getFirstResultFromQuery("select Person_id from PersonEvn (nolock) where PersonEvn_id = :PersonEvn_id", array(
														'PersonEvn_id' => $params['PersonEvn_id']
													));
													if (!empty($params['Person_id']) && isset($rdata['p_'.$params['Person_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']])) {
														$check = $rdata['p_'.$params['Person_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']];
														$rdata['p_'.$params['Person_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']]['cnt']++;
													} else {
														$check = false;
													}
												}
											}

											if (!$check) {
												$this->dbmodel->deleteRegistryErrorTFOMS($data);
												throw new Exception('Запись с ID_PAC = "' . $ID_PAC . '", DATE_1 = "' . $params['DATE_1'] . '", DATE_2 = "' . $params['DATE_2'] . '", DS1 = "' . $params['DS1'] . '" обнаружена в импортируемом файле, но отсутствует в реестре, импорт не произведен.');
											}
										} else {
											$params['SL_ID'] = $SL_ID;

											$check = $this->dbmodel->checkTFOMSErrorDataInRegistry2018($params, $Registry_EvnNum);

											if (!$check) {
												$this->dbmodel->deleteRegistryErrorTFOMS($data);
												throw new Exception('Идентификатор SL_ID = "' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен.');
											}
										}

										$params['Evn_id'] = $check['Evn_id'];
										$params['Registry_id'] = $check['Registry_id'];

										if ( !empty($COMENTSL) && !empty($REFREASON) && $REFREASON != "0" ) {
											$recerr++;
											$params['COMMENT'] = trim($COMENTSL);
											$params['REFREASON'] = $REFREASON;
											$response = $this->dbmodel->setErrorImportRegistry($params);

											if ( !is_array($response) || count($response) == 0 ) {
												$this->dbmodel->deleteRegistryErrorTFOMS($data);
												throw new Exception('Ошибка при обработке реестра');
											}
											else if ( !empty($response[0]['Error_Msg']) ) {
												$this->dbmodel->deleteRegistryErrorTFOMS($data);
												throw new Exception($response[0]['Error_Msg']);
											}
										}

										$SLIDArray[] = $SL_ID;
									}
								}
							}
						}

						if (!empty($data['importMode']) && $data['importMode'] == 2) {
							foreach ($rdata as $key => $EvnData) {
								if ( $EvnData['cnt'] > 0 ) {
									continue;
								}

								$recerr++;

								$params = array(
									'Evn_id' => $EvnData['Evn_id'],
									'Registry_id' => $EvnData['Registry_id'],
									'pmUser_id' => $data['pmUser_id'],
								);

								$response = $this->dbmodel->setManualFLKError($params);

								if (!is_array($response) || count($response) == 0) {
									$this->dbmodel->deleteRegistryErrorTFOMS($data);
									throw new Exception('Ошибка при обработке реестра');
								} else if (!empty($response[0]['Error_Msg'])) {
									$this->dbmodel->deleteRegistryErrorTFOMS($data);
									throw new Exception($response[0]['Error_Msg']);
								}
							}
						} else {
							if (count($Registry_EvnNum) > 0) {
								foreach ($Registry_EvnNum as $SL_ID => $EvnData) {
									if (in_array($SL_ID, $SLIDArray)) {
										continue; // пропускаем те, что были в ответе
									}

									// остальным добавляем ошибку
									$recerr++;

									if (isset($EvnData['e'])) {
										$params = array(
											'Evn_id' => $EvnData['e'],
											'Registry_id' => $EvnData['r'],
											'pmUser_id' => $data['pmUser_id'],
										);
									} else {
										$params = array(
											'Evn_id' => $EvnData['Evn_id'],
											'Registry_id' => $EvnData['Registry_id'],
											'pmUser_id' => $data['pmUser_id'],
										);
									}

									$response = $this->dbmodel->setManualFLKError($params);

									if (!is_array($response) || count($response) == 0) {
										$this->dbmodel->deleteRegistryErrorTFOMS($data);
										throw new Exception('Ошибка при обработке реестра');
									} else if (!empty($response[0]['Error_Msg'])) {
										$this->dbmodel->deleteRegistryErrorTFOMS($data);
										throw new Exception($response[0]['Error_Msg']);
									}
								}
							}
						}
						break;

					case 'FLK':
						$importMode = 'Импорт ФЛК';

						$xml = new SimpleXMLElement(file_get_contents($upload_path.$xmlfile));

						foreach ( libxml_get_errors() as $error ) {
							if ( $error->code != 100 ) { // Игнорирование ошибки xmlns: URI OMS-D1 is not absolute
								throw new Exception('Файл не является архивом реестра.');
							}
						}

						libxml_clear_errors();

						$export_file_name_array = explode('/', $registrydata['Registry_xmlExportPath']);
						$export_file = array_pop($export_file_name_array);
						// проверка соответствия файла реестру
						$FNAME_I = $xml->FNAME_I->__toString();

						if ( empty($FNAME_I) ) {
							throw new Exception('Ошибка при получении имени исходного файла из загруженного файла, импорт не произведен');
						}

						if ( $FNAME_I != mb_substr($export_file, 0, mb_strlen($FNAME_I)) ) {
							throw new Exception('Не совпадает название файла, импорт не произведен');
						}

						foreach ( $xml->PR as $onepr ) {
							$recall++;
							$recerr++;

							$params['N_ZAP'] = $onepr->N_ZAP->__toString();
							$params['IDCASE'] = $onepr->IDCASE->__toString();
							$params['SL_ID'] = $onepr->SL_ID->__toString();
							$params['OSHIB'] = $onepr->OSHIB->__toString();
							$params['IM_POL'] = $onepr->IM_POL->__toString();
							$params['BAS_EL'] = $onepr->BAS_EL->__toString();
							$params['COMMENT'] = $onepr->COMMENT->__toString();

							$params['Registry_id'] = $data['Registry_id'];

							if ( !empty($params['SL_ID']) ) {
								$evnData = $this->dbmodel->checkTFOMSErrorDataInRegistry2018($params, $Registry_EvnNum);

								if ( $evnData === false ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception('SL_ID = "' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
								}

								$params['Registry_id'] = $evnData['Registry_id'];
								$params['Evn_id'] = $evnData['Evn_id'];
								$params['pmUser_id'] = $data['pmUser_id'];

								$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params, true);

								if ( !is_array($response) || count($response) == 0 ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception('Ошибка при обработке реестра!');
								}
								else if ( !empty($response[0]['Error_Msg']) ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception($response[0]['Error_Msg']);
								}
							}
							else if ( !empty($params['IDCASE']) ) {
								$evnData = $this->dbmodel->checkTFOMSErrorDataInRegistry($params, $Registry_EvnNum);

								if ( $evnData === false ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception('IDCASE = "' . $params['IDCASE'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
								}

								$params['Registry_id'] = $evnData['Registry_id'];
								$params['Evn_id'] = $evnData['Evn_id'];
								$params['pmUser_id'] = $data['pmUser_id'];

								$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params, true);

								if ( !is_array($response) || count($response) == 0 ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception('Ошибка при обработке реестра!');
								}
								else if ( !empty($response[0]['Error_Msg']) ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception($response[0]['Error_Msg']);
								}
							}
							else if ( !empty($params['N_ZAP']) ) {
								$evnDataArr = $this->dbmodel->checkTFOMSErrorDataInRegistryByNZAP($params, $Registry_EvnNum);

								if ( $evnDataArr === false ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception('N_ZAP = "' . $params['N_ZAP'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
								}

								foreach ( $evnDataArr as $evnData ) {
									$params['Registry_id'] = $evnData['Registry_id'];
									$params['Evn_id'] = $evnData['Evn_id'];
									$params['pmUser_id'] = $data['pmUser_id'];

									$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params, true);

									if ( !is_array($response) || count($response) == 0 ) {
										$this->dbmodel->deleteRegistryErrorTFOMS($params);
										throw new Exception('Ошибка при обработке реестра!');
									}
									else if ( !empty($response[0]['Error_Msg']) ) {
										$this->dbmodel->deleteRegistryErrorTFOMS($params);
										throw new Exception($response[0]['Error_Msg']);
									}
								}
							}
						}
						break;
				}
			}
			else {
				switch ( $importMode ) {
					case 'MEK':
						$importMode = 'Импорт МЭК';

						$header = substr($checkString, 0, strpos($checkString, '</SCHET>') + strlen('</SCHET>'));
						$footer = '</ZL_LIST>';

						unset($checkString);

						$xmlString = substr($xmlString, strlen($header));

						// 20 MB
						$chunkSize = 1024 * 1024 * 20;

						while ( !empty($xmlString) ) {
							// Нагребаем остатки, если размер оставшегося куска файла меньше $chunkSize МБ
							if ( strlen($xmlString) <= $chunkSize + strlen($footer) + 2 /* учтем перевод строки */ ) {
								$xmlData = substr($xmlString, 0, strlen($xmlString) - strlen($footer));
								$xmlString = '';
							}
							// или данные по $chunkSize МБ
							else {
								$xmlData = substr($xmlString, 0, $chunkSize);
								$xmlString = substr($xmlString, $chunkSize);

								if ( strpos($xmlString, '</ZAP>') !== false ) {
									$xmlData .= substr($xmlString, 0, strpos($xmlString, '</ZAP>') + strlen('</ZAP>'));
									$xmlString = substr($xmlString, strpos($xmlString, '</ZAP>') + strlen('</ZAP>'));

									if ( trim($xmlString) == $header ) {
										$xmlString = '';
									}
								}
							}

							$xml = new SimpleXMLElement($header . $xmlData . $footer);

							foreach ( libxml_get_errors() as $error ) {
								if ( $error->code != 100 ) { // Игнорирование ошибки xmlns: URI OMS-D1 is not absolute
									throw new Exception('Файл не является архивом реестра.');
								}
							}

							if ( property_exists($xml, 'SCHET') && $xml->SCHET->CODE->__toString() != $data['Registry_id']) {
								throw new Exception('Поле CODE в импортируемом файле отличается от реестра, импорт не произведен.');
							}

							libxml_clear_errors();

							if ( !property_exists($xml, 'ZAP') ) {
								$xmlString = '';
								continue;
							}

							foreach ( $xml->ZAP as $onezap ) {
								$ID_PAC = $onezap->PACIENT->ID_PAC->__toString();
								foreach ( $onezap->SLUCH as $onesluch ) {
									$recall++;

									$IDCASE = $onesluch->IDCASE->__toString();
									$COMENTSL = $onesluch->COMENTSL->__toString();
									$REFREASON = $onesluch->REFREASON->__toString();

									$params['Registry_id'] = $data['Registry_id'];

									if (!empty($data['importMode']) && $data['importMode'] == 2) {
										$params['DATE_1'] = $onesluch->DATE_1->__toString();
										$params['DATE_2'] = $onesluch->DATE_2->__toString();
										$params['DS1'] = $onesluch->DS1->__toString();
										$params['PersonEvn_id'] = preg_replace('/\_.*/', '', $ID_PAC);

										if (isset($rdata['pe_'.$params['PersonEvn_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']])) {
											$check = $rdata['pe_'.$params['PersonEvn_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']];
											unset($rdata['pe_'.$params['PersonEvn_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']]);
										} else {
											// получаем Person_id и проверяем по нему
											$params['Person_id'] = $this->dbmodel->getFirstResultFromQuery("select Person_id from PersonEvn (nolock) where PersonEvn_id = :PersonEvn_id", array(
												'PersonEvn_id' => $params['PersonEvn_id']
											));
											if (!empty($params['Person_id']) && isset($rdata['p_'.$params['Person_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']])) {
												$check = $rdata['p_'.$params['Person_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']];
												unset($rdata['p_'.$params['Person_id'].'_'.$params['DATE_1'].'_'.$params['DATE_2'].'_'.$params['DS1']]);
											} else {
												$check = false;
											}
										}

										if (!$check) {
											$this->dbmodel->deleteRegistryErrorTFOMS($data);
											throw new Exception('Запись с ID_PAC = "' . $ID_PAC . '", DATE_1 = "' . $params['DATE_1'] . '", DATE_2 = "' . $params['DATE_2'] . '", DS1 = "' . $params['DS1'] . '" обнаружена в импортируемом файле, но отсутствует в реестре, импорт не произведен.');
										}
									} else {
										$params['IDCASE'] = $IDCASE;

										$check = $this->dbmodel->checkTFOMSErrorDataInRegistry($params, $Registry_EvnNum);

										if (!$check) {
											$this->dbmodel->deleteRegistryErrorTFOMS($data);
											throw new Exception('Запись с IDCASE = "' . $params['IDCASE'] . '" обнаружена в импортируемом файле, но отсутствует в реестре, импорт не произведен.');
										}
									}

									$params['Evn_id'] = $check['Evn_id'];
									$params['Registry_id'] = $check['Registry_id'];

									if ( !empty($COMENTSL) && !empty($REFREASON) && $REFREASON != "0" ) {
										$recerr++;
										$params['COMMENT'] = trim($COMENTSL);
										$params['REFREASON'] = $REFREASON;
										$response = $this->dbmodel->setErrorImportRegistry($params);

										if ( !is_array($response) || count($response) == 0 ) {
											$this->dbmodel->deleteRegistryErrorTFOMS($data);
											throw new Exception('Ошибка при обработке реестра');
										}
										else if ( !empty($response[0]['Error_Msg']) ) {
											$this->dbmodel->deleteRegistryErrorTFOMS($data);
											throw new Exception($response[0]['Error_Msg']);
										}
									}

									if ( !empty($Registry_EvnNum[$IDCASE]) ) {
										unset($Registry_EvnNum[$IDCASE]);
									}
								}
							}
						}

						if (!empty($data['importMode']) && $data['importMode'] == 2) {
							if (count($rdata) > 0) {
								foreach ($rdata as $key => $EvnData) {
									$recerr++;

									$params = array(
										'Evn_id' => $EvnData['Evn_id'],
										'Registry_id' => $EvnData['Registry_id'],
										'pmUser_id' => $data['pmUser_id'],
									);

									$response = $this->dbmodel->setManualFLKError($params);

									if (!is_array($response) || count($response) == 0) {
										$this->dbmodel->deleteRegistryErrorTFOMS($data);
										throw new Exception('Ошибка при обработке реестра');
									} else if (!empty($response[0]['Error_Msg'])) {
										$this->dbmodel->deleteRegistryErrorTFOMS($data);
										throw new Exception($response[0]['Error_Msg']);
									}
								}
							}
						} else {
							if (count($Registry_EvnNum) > 0) {
								foreach ($Registry_EvnNum as $IDCASE => $EvnData) {
									$recerr++;

									if (isset($EvnData['e'])) {
										$params = array(
											'Evn_id' => $EvnData['e'],
											'Registry_id' => $EvnData['r'],
											'pmUser_id' => $data['pmUser_id'],
										);
									} else {
										$params = array(
											'Evn_id' => $EvnData['Evn_id'],
											'Registry_id' => $EvnData['Registry_id'],
											'pmUser_id' => $data['pmUser_id'],
										);
									}

									$response = $this->dbmodel->setManualFLKError($params);

									if (!is_array($response) || count($response) == 0) {
										$this->dbmodel->deleteRegistryErrorTFOMS($data);
										throw new Exception('Ошибка при обработке реестра');
									} else if (!empty($response[0]['Error_Msg'])) {
										$this->dbmodel->deleteRegistryErrorTFOMS($data);
										throw new Exception($response[0]['Error_Msg']);
									}
								}
							}
						}
						break;

					case 'FLK':
						$importMode = 'Импорт ФЛК';

						$xml = new SimpleXMLElement(file_get_contents($upload_path.$xmlfile));

						foreach ( libxml_get_errors() as $error ) {
							if ( $error->code != 100 ) { // Игнорирование ошибки xmlns: URI OMS-D1 is not absolute
								throw new Exception('Файл не является архивом реестра.');
							}
						}

						libxml_clear_errors();

						$export_file_name_array = explode('/', $registrydata['Registry_xmlExportPath']);
						$export_file = array_pop($export_file_name_array);
						// проверка соответствия файла реестру
						$FNAME_I = $xml->FNAME_I->__toString();

						if ( empty($FNAME_I) ) {
							throw new Exception('Ошибка при получении имени исходного файла из загруженного файла, импорт не произведен');
						}

						if ( $FNAME_I != mb_substr($export_file, 0, mb_strlen($FNAME_I)) ) {
							throw new Exception('Не совпадает название файла, импорт не произведен');
						}

						foreach ( $xml->PR as $onepr ) {
							$recall++;
							$recerr++;

							$params['N_ZAP'] = $onepr->N_ZAP->__toString();
							$params['IDCASE'] = $onepr->IDCASE->__toString();
							$params['OSHIB'] = $onepr->OSHIB->__toString();
							$params['IM_POL'] = $onepr->IM_POL->__toString();
							$params['BAS_EL'] = $onepr->BAS_EL->__toString();
							$params['COMMENT'] = $onepr->COMMENT->__toString();

							$params['Registry_id'] = $data['Registry_id'];

							if ( !empty($params['IDCASE']) ) {
								$evnData = $this->dbmodel->checkTFOMSErrorDataInRegistry($params, $Registry_EvnNum);

								if ( $evnData === false ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception('IDCASE = "' . $params['IDCASE'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
								}

								$params['Registry_id'] = $evnData['Registry_id'];
								$params['Evn_id'] = $evnData['Evn_id'];
								$params['pmUser_id'] = $data['pmUser_id'];

								$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params, true);

								if ( !is_array($response) || count($response) == 0 ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception('Ошибка при обработке реестра!');
								}
								else if ( !empty($response[0]['Error_Msg']) ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception($response[0]['Error_Msg']);
								}
							}
							else if ( !empty($params['N_ZAP']) ) {
								$evnDataArr = $this->dbmodel->checkTFOMSErrorDataInRegistryByNZAP($params, $Registry_EvnNum);

								if ( $evnDataArr === false ) {
									$this->dbmodel->deleteRegistryErrorTFOMS($params);
									throw new Exception('N_ZAP = "' . $params['N_ZAP'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
								}

								foreach ( $evnDataArr as $evnData ) {
									$params['Registry_id'] = $evnData['Registry_id'];
									$params['Evn_id'] = $evnData['Evn_id'];
									$params['pmUser_id'] = $data['pmUser_id'];

									$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params, true);

									if ( !is_array($response) || count($response) == 0 ) {
										$this->dbmodel->deleteRegistryErrorTFOMS($params);
										throw new Exception('Ошибка при обработке реестра!');
									}
									else if ( !empty($response[0]['Error_Msg']) ) {
										$this->dbmodel->deleteRegistryErrorTFOMS($params);
										throw new Exception($response[0]['Error_Msg']);
									}
								}
							}
						}
						break;
				}
			}

			$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll' => $recall, 'recErr' => $recerr, 'Message' => 'Реестр успешно загружен.'));

			// Пишем информацию об импорте в историю
			$this->dbmodel->dumpRegistryInformation($data, 3);
		}
		catch ( Exception $e ) {
			$this->ReturnError((!empty($importMode) ? $importMode . ": " : "") . $e->getMessage());
		}
		
		return true;
	}

	/**
	 * Удаление реестра
	 */
	function deleteRegistry() 
	{
		$this->load->model('Utils_model', 'umodel');
		
		$data = $this->ProcessInputData('deleteRegistry', true);
		if ($data === false) { return false; }

		$result = $this->checkDeleteRegistry($data);
		if (!$result) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Извините, удаление реестра невозможно!'));
		}

		//$sch = "r2";
		$object = "Registry";
		$id = $data['id'];
		
		$response = $this->umodel->ObjectRecordDelete($data, $object, false, $id, $this->scheme);
		if (isset($response[0]['Error_Message'])) { $response[0]['Error_Msg'] = $response[0]['Error_Message']; } else { $response[0]['Error_Msg'] = ''; }
		
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}
}
