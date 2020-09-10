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

class Buryatiya_Registry extends Registry {
	var $scheme = "r3";

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
		// Инициализация класса и настройки
		
		$this->inputRules['setRegistryCheckStatus'] = array(
			array('field' => 'Registry_id', 'label' => 'Идентификатор реестра', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryCheckStatus_Code', 'label' => 'Код статуса', 'rules' => '', 'type' => 'int'),
			array('field' => 'RegistryCheckStatus_id', 'label' => 'Идентификатор статуса', 'rules' => '', 'type' => 'id')
		);

		$this->inputRules['saveRegistry'] = array(
			array(
				'default' => null,
				'field' => 'DispClass_id',
				'label' => 'Тип дисп-ции/медосмотра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => 'required',
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
				'field' => 'Registry_IsRepeated',
				'label' => 'Повторная подача',
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
			),
			array(
				'field' => 'ignoreDeleteLinkRemind',
				'label' => 'Игнорировать проверку наличия связей в объединенных реестрах',
				'rules' => '',
				'type' => 'int'
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

		$this->inputRules['checkEvnInRegistry'] = array(
			array(
				'field' => 'CmpCloseCard_id',
				'label' => 'Карта СМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_id',
				'label' => 'Диспансеризация взрослого населения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispProf_id',
				'label' => 'Профосмотр',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispTeenInspection_id',
				'label' => 'Медосмотр несовершеннолетнего',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispOrp_id',
				'label' => 'Карта диспансеризации детей-сирот',
				'rules' => '',
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
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			)
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
	function exportRegistryToXml()
	{	
		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ($data === false) { return false; }
		
		$this->load->library('textlog', array('file'=>'exportRegistryToXml.log'));
		$this->textlog->add('');
		$this->textlog->add('exportRegistryToXml: Запуск формирования реестра (Registry_id='.$data['Registry_id'].')');
		
		$reg_endmonth = date('ym'); // savage: для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
		$type = 0;
		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		
		$res = $this->dbmodel->GetRegistryXmlExport($data);
		
		if ( !is_array($res) || count($res) == 0 ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.')));
			return false;
		}

		$data['KatNasel_SysNick'] = $res[0]['KatNasel_SysNick'];
		$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
		$data['Registry_IsZNO'] = $res[0]['Registry_IsZNO'];
		$data['RegistryGroupType_id'] = $res[0]['RegistryGroupType_id'];

		$dispclass_id = $res[0]['DispClass_id'];

		if ($res[0]['Registry_xmlExportPath'] == '1')
		{
			$this->textlog->add('exportRegistryToXml: Ошибка: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).')));
			return;
		}
		elseif ( !empty($res[0]['Registry_xmlExportPath']) && 1 == $data['OverrideExportOneMoreOrUseExist'] ) // если уже выгружен реестр
		{
			$link = $res[0]['Registry_xmlExportPath'];
			$this->textlog->add('exportRegistryToXml: вернули ссылку '.$link);
			echo "{'success':true,'Link':'$link'}";
			return;
		}
		elseif ( $res[0]['RegistryCheckStatus_Code'] == 1 ) {
			$this->textlog->add('exportRegistryToXml: Ошибка: Реестр заблокирован. Формирование нового XML-файла недопустимо.');
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Реестр заблокирован. Формирование нового XML-файла недопустимо.')));
			return false;
		}
		else 
		{
			$reg_endmonth = $res[0]['Registry_endMonth'];
			$type = $res[0]['RegistryType_id'];
			$this->textlog->add('exportRegistryToXml: Тип реестра '.$res[0]['RegistryType_id']);
		}

		$this->textlog->add('exportRegistryToXml: Количество записей в реестре: ' . $res[0]['RegistryData_Count']);

		// Формирование XML в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try
		{
			// Объединенные реестры могут содержать данные любого типа
			$data['Status'] = '1';
			$this->dbmodel->SetXmlExportStatus($data);

			set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

			$isNewExport = true; // выгрузка в новом формате, возможно нужно условие какое то для выгрузки старых реестров в старом формате
			$templateModificator = ($isNewExport == true ? "_2018" : "");

			$templ_header = "registry_buryatiya_pl_header{$templateModificator}";
			$templ_footer = "registry_buryatiya_pl_footer";
			$templ_person_header = "registry_buryatiya_person_header";
			$templ_person_footer = "registry_buryatiya_person_footer";

			$Registry_EvnNum = array();
			$SCHET = $this->dbmodel->loadRegistrySCHETForXmlUsingCommonUnion($data, $isNewExport);

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];
			mkdir(EXPORTPATH_REGISTRY . $out_dir);

			$packNum = $this->dbmodel->SetXmlPackNum($data);

			if ( empty($packNum) ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('exportRegistryToXml: Выход с ошибкой: Ошибка при получении номера выгружаемого пакета.');
				$this->ReturnError('Ошибка при получении номера выгружаемого пакета.');
				return false;
			}

			$packNum = sprintf('%02d', $packNum);

			switch ($res[0]['RegistryGroupType_id']) {
				case 1: // Стационар, поликлиника, СМП
					$first_code = ($data['Registry_IsZNO'] == 2 ? "C" : "H");
					$xml_file = "registry_buryatiya_pl_body{$templateModificator}";
					break;

				case 2: // ВМП
					$first_code = "T";
					$xml_file = "registry_buryatiya_hmp_body{$templateModificator}";
					break;

				case 11: // ДВН, ДДС, ПОВН, МОН
					$first_code = "D";
					$xml_file = "registry_buryatiya_disp_body{$templateModificator}";
					break;

				default:
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Не указан тип объединенного реестра!')));
					return false;
					break;
			}

			$rname = $first_code . "M" . $SCHET[0]['CODE_MO'] . 'T' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
			$pname = "L" . ($data['Registry_IsZNO'] == 2 ? $first_code : "") . "M" . $SCHET[0]['CODE_MO'] . 'T' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;
			$zname = $first_code . "M" . $SCHET[0]['CODE_MO'] . 'T' . sprintf('%02d', $data['session']['region']['number']) . "_" . $data['Registry_endMonth'] . $packNum;

			$file_re_data_sign = $rname;
			$file_re_pers_data_sign = $pname;

			// файл-тело реестра
			$file_re_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";

			// временный файл-тело реестра
			$file_re_data_name_tmp = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . "_tmp.xml";

			// файл-перс. данные
			$file_re_pers_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";

			$this->textlog->add('exportRegistryToXml: Определили наименования файлов: '.$file_re_data_name.' и '.$file_re_pers_data_name);

			$SCHET[0]['FILENAME'] = $file_re_data_sign;
			$ZGLV = array();
			$ZGLV[0]['VERSION'] = '3.1.2';
			$ZGLV[0]['FILENAME1'] = $file_re_data_sign;
			$ZGLV[0]['FILENAME'] = $file_re_pers_data_sign;

			$this->load->library('parser');

			// Заголовок для файла person
			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $templ_person_header, $ZGLV[0], true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);

			if ($isNewExport) {
				// Получаем список типов реестров, входящих в объединенный реестр
				$registrytypes = $this->dbmodel->getUnionRegistryTypes($data['Registry_id']);
				if ( !is_array($registrytypes) || count($registrytypes) == 0 ) {
					throw new Exception('При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
				}

				// Записываем данные
				$SD_Z = 0;
				foreach($registrytypes as $type) {
					$SD_Z += $this->dbmodel->loadRegistryDataForXmlUsing2018($type, $data, $Registry_EvnNum, $xml_file, $file_re_data_name_tmp, $file_re_pers_data_name);
				}
			} else {
				$SD_Z = $this->dbmodel->loadRegistryDataForXmlUsing($data, $Registry_EvnNum, $xml_file, $file_re_data_name_tmp, $file_re_pers_data_name);
			}
			$this->textlog->add('exportRegistryToXml: loadRegistryDataForXmlUsing: Выбрали данные');

			if ($SD_Z === false)
			{
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('exportRegistryToXml: '.$this->error_deadlock);
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
				return false;
			}

			$SCHET[0]['SD_Z'] = $SD_Z;
			$SCHET[0]['VERSION'] = '3.1.2';

			// Заголовок для файла с данными
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/'.$templ_header, $SCHET[0], true);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml);

			// Перегоняем данные из временного файла в основной
			if ( file_exists($file_re_data_name_tmp) ) {
				// Устанавливаем начитываемый объем данных
				$chunk = 10 * 1024 * 1024; // 10 MB

				$fh = @fopen($file_re_data_name_tmp, "rb");

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

				unlink($file_re_data_name_tmp);
			}

			// Конец
			$xml = $this->parser->parse('export_xml/' . $templ_footer, array(), true);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);

			// Конец для файла person
			$xml_pers = $this->parser->parse('export_xml/' . $templ_person_footer, array(), true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);

			$file_zip_sign = $zname;
			$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
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
			}elseif (file_exists($file_zip_name))
			{
				//header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
				//header("Content-Type: text/html");
				//header("Pragma: no-cache");
				$data['Status'] = $file_zip_name;
				$data['Registry_EvnNum'] = json_encode($Registry_EvnNum);
				$this->dbmodel->SetXmlExportStatus($data);
				//echo "{'success':true,'Link':'$file_zip_name'}";
				$this->textlog->add("exportRegistryToXml: Все закончилось, вроде успешно.");

				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);
			}
			else{
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
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml_check_volume/registry_buryatiya_pl', $registry_data_res, true);
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
	 * Импорт реестра из XML
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
		$file_data['file_ext'] = strtolower(end($x));
		if (!in_array($file_data['file_ext'], $allowed_types)) {
			$this->ReturnError('Данный тип файла не разрешен.', 100013);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', 100014);
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnError('Загрузка файла не возможна из-за прав пользователя.', 100015);
			return false;
		}

		if ($file_data['file_ext'] == 'xml') {
			$xmlfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnError('Не удаётся переместить файл.', 100016);
				return false;
			}
		} else {
			// там должен быть файл AHM*.xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) 
			{
				$xmlfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/(AHM|ACM|ADM).*\.(xml|XML)/', $filename) > 0 ) {
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
			$this->ReturnError('Файл не является архивом реестра.', 100017);
			return false;
		}

		$recall = 0;
		$recerr = 0;
		
		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistryForImport($data);
		if (empty($registrydata)) {
			$this->ReturnError('Ошибка чтения данных реестра');
			return false;
		}

		// @task https://redmine.swan.perm.ru/issues/82944
		if ( $registrydata['Registry_HasPaid'] == 2 ) {
			$this->ReturnError('Импорт невозможен, т.к. в объединенный реестр входят реестры, переведенные в оплаченные.');
			return false;
		}

		// Удаляем ответ по этому реестру, если он уже был загружен
		$rr = $this->dbmodel->deleteRegistryErrorTFOMS($data);

		libxml_use_internal_errors(true);

		$xml = new SimpleXMLElement(file_get_contents($upload_path.$xmlfile));

		foreach (libxml_get_errors() as $error) {
			$this->ReturnError('Файл не является архивом реестра.', 100018);
			return false;
		}
		libxml_clear_errors();

		$export_file_name_array = explode('/', $registrydata['Registry_xmlExportPath']);
		$export_file = array_pop($export_file_name_array);

		$xmlNode = $xml->ZGLV->FILENAME;
		if (!empty($xmlNode)) {
			$fname = $xmlNode->__toString();
			if ($fname . '.zip' != $export_file) {
				$this->ReturnError('Не совпадает название файла, импорт не произведен');
				return false;
			}
		} else {
			$this->ReturnError('Некорректный файл для импорта ПТК (не найден тег ZGLV->FILENAME), импорт не произведен');
			return false;
		}

		$params = array();
		$params['pmUser_id'] = $data['pmUser_id'];

		// идём по случаям
		foreach ( $xml->ZAP as $onezap ) {
			$recall++;

			$SANK = array();
			$SANK_SL = array();

			$hasErrors = false;

			// идём по ошибкам на уровне законеченного случая
			foreach ( $onezap->Z_SL->SANK as $onesank ) {
				$SL_ID = $onesank->SL_ID->__toString();
				$S_COM = $onesank->S_COM->__toString();

				if ( !empty($S_COM) ) {
					if (!empty($SL_ID)) {
						if (!isset($SANK_SL[$SL_ID])) {
							$SANK_SL[$SL_ID] = array();
						}

						$SANK_SL[$SL_ID][] = trim($S_COM);
					} else {
						$SANK[] = trim($S_COM);
					}

					$hasErrors = true;
				}
			}

			foreach ( $onezap->Z_SL->SL as $onesluch ) {
				if ( count($SANK) == 0 ) {
					continue;
				}

				$params['SL_ID'] = $onesluch->SL_ID->__toString();
				$params['Registry_EvnNum'] = $registrydata['Registry_EvnNum'];
				$params['Registry_id'] = $data['Registry_id'];

				$check = $this->dbmodel->checkTFOMSErrorDataInRegistry($params);
				if ( !$check ) {
					$this->dbmodel->deleteRegistryErrorTFOMS($data);
					$this->ReturnError('SL_ID = "' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
					return false;
				}
				$params['Evn_id'] = $check['Evn_id'];
				$params['Registry_id'] = $check['Registry_id'];

				// идём по ошибкам законеченных случаев
				foreach ($SANK as $COMMENT) {
					$params['COMMENT'] = $COMMENT;
					$response = $this->dbmodel->setErrorImportRegistry($params);

					if ( !is_array($response) ) {
						$this->dbmodel->deleteRegistryErrorTFOMS($data);
						$this->ReturnError('Ошибка при обработке реестра');
						return false;
					}
					else if ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
						$this->dbmodel->deleteRegistryErrorTFOMS($data);
						$this->ReturnError($response[0]['Error_Msg']);
						return false;
					}

					$hasErrors = true;
				}
			}

			// идём по ошибкам случаев
			foreach($SANK_SL as $SL_ID => $SANKARR) {
				$params['SL_ID'] = $SL_ID;
				$params['Registry_EvnNum'] = $registrydata['Registry_EvnNum'];
				$params['Registry_id'] = $data['Registry_id'];

				$check = $this->dbmodel->checkTFOMSErrorDataInRegistry($params);
				if ( !$check ) {
					$this->dbmodel->deleteRegistryErrorTFOMS($data);
					$this->ReturnError('SL_ID = "' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
					return false;
				}
				$params['Evn_id'] = $check['Evn_id'];
				$params['Registry_id'] = $check['Registry_id'];

				foreach ( $SANKARR as $COMMENT ) {
					$params['COMMENT'] = $COMMENT;
					$response = $this->dbmodel->setErrorImportRegistry($params);

					if ( !is_array($response) ) {
						$this->dbmodel->deleteRegistryErrorTFOMS($data);
						$this->ReturnError('Ошибка при обработке реестра');
						return false;
					}
					else if ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
						$this->dbmodel->deleteRegistryErrorTFOMS($data);
						$this->ReturnError($response[0]['Error_Msg']);
						return false;
					}

					$hasErrors = true;
				}
			}

			if ( $hasErrors == true ) {
				$recerr++;
			}
		}

		//$this->dbmodel->setRegistryPaid($data);

		// В реестре есть запись, а в файле импорта нет по полю SL.SL_ID - то запись ошибки в БД
		// @task https://jira.is-mis.ru/browse/PROMEDWEB-5632
		$this->load->library('textlog', array('file' => 'importRegistryFromXml_' . date('Y-m-d') . '.log'));
		$this->textlog->add("Запуск\n\r");
		$start = microtime(true);
		$this->textlog->add("Задействовано памяти до выполнения участка кода: " . memory_get_usage() . "\n\r");
		foreach(json_decode($registrydata['Registry_EvnNum'], true) as $key => $value) {
			$found = $xml->xpath("//Z_SL/SL/SL_ID[. ='$key']");
			if (!$found) {
				$recerr++;
				$data['Evn_id'] = $key;
				$data['CaseRegistry_id'] = $value['Registry_id'];
				$this->dbmodel->setRegistryErrorTfoms($data);
			}
		}
		$this->textlog->add("Задействовано памяти после выполнения участка кода: " . memory_get_usage() . "\n\r");
		$this->textlog->add('Время выполнения участка кода: ' . (microtime(true) - $start) . ' sec.' . "\n\r");
		$this->textlog->add("Выполнено\n\r");

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'Message' => 'Реестр успешно загружен.'));
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
			$this->ReturnError('Не выбран файл реестра!', 100011);
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
			$this->ReturnError('Данный тип файла не разрешен.', 100013);
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
			$this->ReturnError('Путь для загрузки файлов некорректен.', 100014);
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnError('Загрузка файла не возможна из-за прав пользователя.', 100015);
			return false;
		}

		if (strtolower($file_data['file_ext']) == 'xml') {
			$xmlfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnError('Не удаётся переместить файл.', 100016);
				return false;
			}
		} else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) {
				$xmlfile = "";

				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/FLK_HM.*\.(xml|XML)/', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}


		if (empty($xmlfile)) {
			$this->ReturnError('Файл не является архивом реестра.', 100017);
			return false;
		}

		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistryForImport($data);
		if (empty($registrydata)) {
			$this->ReturnError('Ошибка чтения данных реестра');
			return false;
		}

		// @task https://redmine.swan.perm.ru/issues/82944
		if ( $registrydata['Registry_HasPaid'] == 2 ) {
			$this->ReturnError('Импорт невозможен, т.к. в объединенный реестр входят реестры, переведенные в оплаченные.');
			return false;
		}

		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$IDCASE = null;
		$isErrors = false;
		$recerr = 0;
		$recall = 0;

		libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$res = $dom->load($upload_path.$xmlfile);
		foreach (libxml_get_errors() as $error) {
			if ($error->code != 100) { // Игнорирование ошибки xmlns: URI OMS-D1 is not absolute
				$this->ReturnError('Файл не является архивом реестра.', 100015);
				return false;
			}
		}
		libxml_clear_errors();

		$export_file_name_array = explode('/', $registrydata['Registry_xmlExportPath']);
		$export_file = array_pop($export_file_name_array);
		// проверка соответствия файла реестру
		$correctFileName = false;
		$dom_fnamei = $dom->getElementsByTagName('FNAME_I');
		foreach($dom_fnamei as $dom_onefnamei) {
			if ($dom_onefnamei->nodeValue.'.zip' == $export_file) {
				$correctFileName = true;
			}
		}
		if (!$correctFileName) {
			$this->ReturnError('Не совпадает название файла, импорт не произведен');
			return false;
		}

		$dom_pr = $dom->getElementsByTagName('PR');
		foreach ($dom_pr as $dom_onepr) {
			$recall++;
			$recerr++;

			$params = array(
				'Registry_EvnNum' => $registrydata['Registry_EvnNum'],
				'Registry_id' => $data['Registry_id'],
			);

			$params['N_ZAP'] = 0;
			$params['IDCASE'] = 0;
			$params['SL_ID'] = 0;
			$params['OSHIB'] = '';
			$params['IM_POL'] = '';
			$params['BAS_EL'] = '';
			$params['COMMENT'] = '';
			$params['FATALITY'] = '';

			$dom_slid = $dom_onepr->getElementsByTagName('SL_ID');
			foreach($dom_slid as $dom_oneslid) {
				$params['SL_ID'] = $dom_oneslid->nodeValue;
			}

			$dom_idcase = $dom_onepr->getElementsByTagName('IDCASE');
			foreach($dom_idcase as $dom_oneidcase) {
				$params['IDCASE'] = $dom_oneidcase->nodeValue;
			}

			$dom_nzap = $dom_onepr->getElementsByTagName('N_ZAP');
			foreach($dom_nzap as $dom_onenzap) {
				$params['N_ZAP'] = $dom_onenzap->nodeValue;
			}

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

			if (!empty($params['SL_ID'])) {
				$evnData = $this->dbmodel->checkTFOMSErrorDataInRegistry($params);
				if ($evnData === false) {
					$this->dbmodel->deleteRegistryErrorTFOMS($params);
					$this->ReturnError('SL_ID = "' . $params['SL_ID'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
					return false;
				}

				$params['Registry_id'] = $evnData['Registry_id'];
				$params['Evn_id'] = $evnData['Evn_id'];
				$params['pmUser_id'] = $data['pmUser_id'];
				$params['ROWNUM'] = !empty($evnData['N_ZAP']) ? $evnData['N_ZAP'] : $params['N_ZAP'];

				$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params, true);
				if (!is_array($response)) {
					$this->dbmodel->deleteRegistryErrorTFOMS($params);
					$this->ReturnError('Ошибка при обработке реестра!');
					return false;
				} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
					$this->dbmodel->deleteRegistryErrorTFOMS($params);
					$this->ReturnError($response[0]['Error_Msg']);
					return false;
				}
			} else if (!empty($params['IDCASE'])) {
				$evnDataArr = $this->dbmodel->checkTFOMSErrorDataInRegistryByIDCASE($params);
				if ($evnDataArr === false) {
					$this->dbmodel->deleteRegistryErrorTFOMS($params);
					$this->ReturnError('IDCASE = "' . $params['IDCASE'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
					return false;
				}

				foreach($evnDataArr as $evnData) {
					$params['Registry_id'] = $evnData['Registry_id'];
					$params['Evn_id'] = $evnData['Evn_id'];
					$params['ROWNUM'] = $evnData['N_ZAP'];
					$params['pmUser_id'] = $data['pmUser_id'];

					$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params, true);
					if (!is_array($response)) {
						$this->dbmodel->deleteRegistryErrorTFOMS($params);
						$this->ReturnError('Ошибка при обработке реестра!');
						return false;
					} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
						$this->dbmodel->deleteRegistryErrorTFOMS($params);
						$this->ReturnError($response[0]['Error_Msg']);
						return false;
					}
				}
			} else if (!empty($params['N_ZAP'])) {
				$evnDataArr = $this->dbmodel->checkTFOMSErrorDataInRegistryByNZAP($params);
				if ($evnDataArr === false) {
					$this->dbmodel->deleteRegistryErrorTFOMS($params);
					$this->ReturnError('N_ZAP = "' . $params['N_ZAP'] . '" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
					return false;
				}

				foreach($evnDataArr as $evnData) {
					$params['Registry_id'] = $evnData['Registry_id'];
					$params['Evn_id'] = $evnData['Evn_id'];
					$params['ROWNUM'] = $params['N_ZAP'];
					$params['pmUser_id'] = $data['pmUser_id'];

					$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($params, true);
					if (!is_array($response)) {
						$this->dbmodel->deleteRegistryErrorTFOMS($params);
						$this->ReturnError('Ошибка при обработке реестра!');
						return false;
					} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
						$this->dbmodel->deleteRegistryErrorTFOMS($params);
						$this->ReturnError($response[0]['Error_Msg']);
						return false;
					}
				}
			}
		}

		if ($recall>0)
		{
			// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']); // при импорте ничего не происходит по ТЗ :)
		}

		//$this->dbmodel->setRegistryPaid($data);

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'Message' => 'Реестр успешно загружен.'));
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
		$this->load->model('Utils_model', 'umodel');
		
		$data = $this->ProcessInputData('deleteRegistry', true);
		if ($data === false) { return false; }

		if (isset($data['ignoreDeleteLinkRemind']) && $data['ignoreDeleteLinkRemind']==1) {
			$this->dbmodel->deleteRegistryInGroupLink(array('Registry_id' => $data['id']));
		} else {
			if ($dataMessage = $this->dbmodel->checkDeleteRegistryInGroupLink(array('Registry_id' => $data['id']))) {
				$this->ReturnData($dataMessage);
				return false;
			}
		}

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
	 * Функция проверяет, есть ли уже выгруженный файл реестра
	 * Входящие данные: _POST (Registry_id),
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
			// если уже выгружен реестр и оплачен или заблокирован
			else if ( !empty($res[0]['Registry_xmlExportPath']) && ($res[0]['RegistryStatus_id'] == 4 || $res[0]['RegistryCheckStatus_Code'] == 1)) {
				$this->ReturnData(array('success' => true, 'exportfile' => 'onlyexists'));
				return true;
			}
			// если уже выгружен реестр и не оплаченный
			else if ( !empty($res[0]['Registry_xmlExportPath']) && $res[0]['RegistryStatus_id'] != 4 ) {
				$this->ReturnData(array('success' => true, 'exportfile' => 'exists'));
				return true;
			}
			else {
				$this->ReturnData(array('success' => true, 'exportfile' => 'empty'));
				return true;
			}
		} else {
			$this->ReturnError('Ошибка получения данных по реестру');
			return false;
		}
	}

	/**
	 * Проверка наличия редактируемого случа в реестре
	 */
	function checkEvnInRegistry() {

		$data = $this->ProcessInputData('checkEvnInRegistry', true);
		if ($data === false) { return false; }

		// Проверка есть ли в реестрах записи об этом случае
		// Цепляем реестровую БД
		$this->db = null;
		$this->load->database('registry');

		$registryData = $this->dbmodel->checkEvnInRegistry($data, 'edit');

		if ( is_array($registryData)&& count($registryData) > 0 && !empty($registryData[0]['Error_Msg']) ) {
			$this->ReturnError($registryData[0]['Error_Msg']);
			return false;
		}
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
