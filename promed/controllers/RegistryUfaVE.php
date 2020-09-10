<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry - операции с реестрами (модификация оригинального RegistryUfa.php для групповой постановке реестров на очередь формирования Task#18011)
*
* @package      Admin
* @access       public
* @version      26/04/2013
* 
*/
require("RegistryVE.php");
class RegistryUfaVE extends RegistryVE {
	var $model_name = "RegistryUfa_modelVE";

	//Task#18694 Шаблоны для ФЛК XML реестров
	var $H_xsd = '/documents/xsd/OMS-D1.xsd';
	var $L_xsd = '/documents/xsd/OMS-D2.xsd';
	/**
	 * comment
	 */ 
	function __construct() {

		parent::__construct();
		// Инициализация класса и настройки
		
		$this->inputRules['saveRegistry'] = array(
			array(
				'default' => null,
				'field' => 'OrgSmo_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'id'
			),          
			array(
				'field' => 'LpuUnitSet_id',
				'label' => 'Подразделение',
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
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistrySubType',
				'label' => 'Подтип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsActive',
				'label' => 'Признак активного регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_accDate',
				'label' => 'Дата счета',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'xml',
				'label' => 'XML строка',
				'rules' => '',
				'type' => 'string'
			)            
		);

		$this->inputRules['exportRegistryToXml'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['getSmoName'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
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
	 * Функция формирует группу файлов в XML формате для выгрузки данных.
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: JSON-строка.
	 * Task#18694
	 */
	function exportRegistryGroupToXml()
	{	
		$_POST['Registry_id'] = json_decode($_POST['Registry_id'], 1);
		$_POST['RegistryTypeList'] = json_decode($_POST['RegistryType_id'], 1);
		$_POST['Registry_Num'] = json_decode($_POST['Registry_Num'], 1);
		
		//Странно..., после джейсона null превращается в строку "null"
		$_POST['KatNasel_id'] = ($_POST['KatNasel_id'] == 'null') ? array() : json_decode($_POST['KatNasel_id'], 1);
		$_POST['OverrideControlFlkStatus'] = isset($_POST['OverrideControlFlkStatus']) ? json_decode($_POST['OverrideControlFlkStatus'], 1) : array();
		$_POST['OverrideExportOneMoreOrUseExist'] = isset($_POST['OverrideExportOneMoreOrUseExist']) ? json_decode($_POST['OverrideExportOneMoreOrUseExist'], 1) : array();
		$_POST['onlyLink'] = isset($_POST['onlyLink']) ? json_decode($_POST['onlyLink'], 1) : array();
		$_POST['send'] = isset($_POST['send']) ? json_decode($_POST['send'], 1) : array();
		
		$groupResult = array();
		
		$groupResult['success'] = true;        
		
		if(is_array($_POST['Registry_id'])){
			//Все архивы в один большой архив
			//$dir = 'big_archive_'.date('d_m_Y');
			
			//if(!file_exists(EXPORTPATH_REGISTRY.$dir))
			//    mkdir(EXPORTPATH_REGISTRY.$dir);            
			
			//Имя большого архива
			$file_zip_name = EXPORTPATH_REGISTRY.'/'.'Big_'.date('d.m.Y_h-i-s').'.zip';

			require("RegistryUfa.php");
			$RegistryUfa = new RegistryUfa();

			$duplicates = 0;
			$used = array();
			foreach($_POST['Registry_id'] as $k=>$Registry_id){
				$_POST['RegistryType_id'] = $_POST['RegistryTypeList'][$k];
				$_REQUEST['RegistryType_id'] = $_POST['RegistryType_id'];
				$_POST['KatNasel_id'] = ($_POST['KatNasel_id'] == null) ? null : $_POST['KatNasel_id'][$k];
				$_POST['OverrideControlFlkStatus'] = ($_POST['OverrideControlFlkStatus'] == null) ? null : $_POST['OverrideControlFlkStatus'][$k];
				$_POST['OverrideExportOneMoreOrUseExist'] = ($_POST['OverrideExportOneMoreOrUseExist'] == null) ? null : $_POST['OverrideExportOneMoreOrUseExist'][$k];
				$_POST['onlyLink'] = ($_POST['onlyLink'] == null) ? null : $_POST['onlyLink'][$k];
				$_POST['send'] = ($_POST['send'] == null) ? null : $_POST['send'][$k];
				
				ob_start();
				
				$res = $RegistryUfa->exportUnionRegistryToXml();
				
				$buff = ob_get_contents();
				
				ob_end_clean();
				
				$res = json_decode($buff, 1);

				$groupResult[] = $res;
				$groupResult[$k]['number'] =  $_POST['Registry_Num'][$k];

				if(isset($groupResult[$k]['Link'])){
					
					//Если нашёлся хотябы 1 маленький архив то
					if(!file_exists($file_zip_name)){
						file_put_contents($file_zip_name, '');                        
									
						$zip=new ZipArchive();
						$zip->open($file_zip_name, ZIPARCHIVE::CREATE);                       
					}

					$registry_archive = explode('/',$groupResult[$k]['Link']);
                    //Упаковываем и ложим в папку с именем СМО
                    $resNameSmo = $this->getSmoName($Registry_id);
                    $resNameSmo = explode(' (', $resNameSmo[0]['Smo_Name']);
                    $dir_smo = iconv('utf-8', 'CP866', $resNameSmo[0]);
					if (in_array($registry_archive[3], $used)) { // если уже есть с таким названием, добавим что нибудь, а то не выгружается.
						$duplicates++;
						$registry_archive[3] = $duplicates.'_'.$registry_archive[3];
					}

					$used[] = $registry_archive[3];
					$zip->AddFile($groupResult[$k]['Link'], $dir_smo.'/'.$registry_archive[3]);
			   }

			}

			if(file_exists($file_zip_name)){
				$zip->close();
				//$this->textlog->add('exportRegistryToXml: Упаковали в большой ZIP '.$file_zip_name); 

				$groupResult['big'] = array(
											 'file_name'=>$file_zip_name
											);  
						   
			} 
		}

		echo json_encode($groupResult);
		
	}
    /**
     * Функция получения названия СМО реестра 
     */ 
    function getSmoName($data){
		$data = $this->ProcessInputData('getSmoName', true);
		if ($data === false) { return false; }       
        
        $res = $this->dbmodel->getSmoName($data); 
        
        if($res){
            return $res;
        }
        else{
            $this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при чтении СМО реестра')));
        }
    }
	/**
	 * Функция формирует файлы в XML формате для выгрузки данных.
	 * Входящие данные: $_POST (Registry_id),
	 * На выходе: JSON-строка.
	 */
	function exportRegistryToXml()
	{	
		$data = $this->ProcessInputData('exportRegistryToDbf', true);
		if ($data === false) { return false; }
		
		// видимо временное решение
		ini_set('memory_limit', '2048M');
		
		$this->load->library('textlog', array('file'=>'exportRegistryToXml.log'));
		$this->textlog->add('');
		$this->textlog->add('exportRegistryToXml: Запуск формирования реестра (Registry_id='.$data['Registry_id'].')');
		
		$reg_endmonth = date('ym'); // savage: для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
		$type = 0;
		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		
		$data['Status'] = NULL;
		$this->dbmodel->SetXmlExportStatus($data);

		$res = $this->dbmodel->GetRegistryXmlExport($data);
		
		//echo '<pre>' . print_r($res, 1) . '</pre>';
		//Временная заглушка - чтобы каждый раз происходило формирование архивов
		//$res[0]['Registry_xmlExportPath'] = null;

		if (is_array($res) && count($res) > 0) 
		{
			$data['OrgSmo_id'] = $res[0]['OrgSmo_id'];
			$data['Registry_IsNotInsur'] = $res[0]['Registry_IsNotInsur'];

			if ($res[0]['Registry_xmlExportPath'] == '1')
			{
				$this->textlog->add('exportRegistryToXml: Ошибка: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).')));
				return;
			}
			elseif (strlen($res[0]['Registry_xmlExportPath'])>0) // если уже выгружен реестр
			{
				$link = $res[0]['Registry_xmlExportPath'];
				$this->textlog->add('exportRegistryToXml: вернули ссылку '.$link);
				echo "{'success':true,'Link':'$link', 'this':'yeep'}";
				return;
			}
			else 
			{
				$reg_endmonth = $res[0]['Registry_endMonth'];
				$type = $res[0]['RegistryType_id'];
				$this->textlog->add('exportRegistryToXml: Тип реестра '.$res[0]['RegistryType_id']);
			}
		}
		else 
		{
			$this->textlog->add('exportRegistryToXml: Ошибка: Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
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
			if (!empty($res[0]['RegistrySubType_id']) && $res[0]['RegistrySubType_id'] == 2) {
				$this->load->model('RegistryUfa_model');
				$registry_data_res = $this->RegistryUfa_model->loadRegistryDataForXmlUsingCommonUnion($type, $data);
			} else {
				if ($type == 4)
					$registry_data_res = $this->dbmodel->loadRegistryDataForXmlUsing($type, $data);
				else
					$registry_data_res = $this->dbmodel->loadRegistryDataForXmlUsingCommon($type, $data);
			}
			array_walk_recursive($registry_data_res, 'ConvertFromUTF8ToWin1251', true);
			$this->textlog->add('exportRegistryToXml: loadRegistryDataForXmlUsingCommon.');
			if ($registry_data_res === false)
			{
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('exportRegistryToXml: '.$this->error_deadlock);
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
				return false;
			}
			if ( empty($registry_data_res) )
			{
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('exportRegistryToXml: Данных по требуемому реестру нет в базе данных.');
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данных по требуемому реестру нет в базе данных.')));
				return false;
			}
			$this->textlog->add('exportRegistryToXml: Получили все данные из БД ');
			$this->textlog->add('exportRegistryToXml: Количество записей реестра = '.count($registry_data_res['ZAP']));
			$this->textlog->add('exportRegistryToXml: Количество людей в реестре = '.count($registry_data_res['PACIENT']));
			
			array_walk_recursive($registry_data_res, 'RegistryUfaVE::encodeForXmlExport');
			
			switch ($type) {
				case 1: case 2: case 6: case 14:
					$xml_file_person = "registry_ufa_person";
					$xml_file = "registry_ufa_pl";
					break;
				case 4: 
					$xml_file_person = "registry_ufa_dd_person";
					$xml_file = "registry_ufa_dd";
					break;
				case 5: 
					$xml_file_person = "registry_ufa_person";
					$xml_file = "registry_ufa_orp";
					break;
				default: 
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данный тип реестров не обрабатывается!')));
					return false;
					break;
			}
			
			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_".time()."_".$data['Registry_id'];
			//Проверка на наличие созданной ранее директории
			if(!file_exists(EXPORTPATH_REGISTRY.$out_dir))
				mkdir( EXPORTPATH_REGISTRY.$out_dir );
			
			$this->textlog->add('exportRegistryToXml: создали каталог '.EXPORTPATH_REGISTRY.$out_dir);
			
			if (isset($registry_data_res['SCHET'][0]['KatNasel_Liter'])) {
				$Liter = $registry_data_res['SCHET'][0]['KatNasel_Liter'];
			} else {
				$Liter = 'T';
			}
			// файл-тело реестра
			if ($type!=4) {
				$file_re_data_sign = "HM".$registry_data_res['SCHET'][0]['PODR'].$Liter.$registry_data_res['SCHET'][0]['code_smo']."_".$reg_endmonth."1";
				$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".xml";
				// файл-перс. данные
				$file_re_pers_data_sign = "LM".$registry_data_res['SCHET'][0]['PODR'].$Liter.$registry_data_res['SCHET'][0]['code_smo']."_".$reg_endmonth."1";
				$file_re_pers_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_pers_data_sign.".xml";
			} else {
				$file_re_data_sign = "HM".$registry_data_res['SCHET'][0]['CODE_MO'].$Liter."02_".$reg_endmonth."1";
				$file_re_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_data_sign.".xml";
				// файл-перс. данные
				$file_re_pers_data_sign = "LM".$registry_data_res['SCHET'][0]['CODE_MO'].$Liter."02_".$reg_endmonth."1";
				$file_re_pers_data_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_re_pers_data_sign.".xml";
			}
			$this->textlog->add('exportRegistryToXml: Определили наименования файлов: '.$file_re_data_name.' и '.$file_re_pers_data_name);
			
			$this->load->library('parser');
			$registry_data_res['SCHET'][0]['FILENAME'] = $file_re_data_sign;
			//print_r($registry_data_res);
			/*
			if ($type == 4)
				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/'.$xml_file.'', $registry_data_res, true);
			else 
			*/
			$registry_data_person = array('PACIENT'=>$registry_data_res['PACIENT']);
			unset($registry_data_res['PACIENT']); // для экономии памяти при обработке и чтобы сразу выявить шибку, когда запрос по визиту и запрос по персону возвращают разное количество записей
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/'.$xml_file.'', $registry_data_res, true);
			$this->textlog->add('exportRegistryToXml: Создали Xml записей в памяти');
			reset($registry_data_res);
			$registry_data_res['ZGLV'][0]['FILENAME'] = $file_re_pers_data_sign;
			$registry_data_res['ZGLV'][0]['FILENAME1'] = $file_re_data_sign;
			$registry_data_person['ZGLV'] = $registry_data_res['ZGLV'];
			/*
			if ($type == 4)
				$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/'.$xml_file_person.'', $registry_data_res, true);
			else 
			*/
			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/'.$xml_file_person.'', $registry_data_person, true);
			$this->textlog->add('exportRegistryToXml: Создали Xml людей в памяти');                      
			
			//--Task#18964 ФЛК контроль
 
			//Проверяем валидность 1го реестра
			//Путь до шаблона
			$H_xsd_tpl = $_SERVER['DOCUMENT_ROOT'].$this->H_xsd;
			//Файл с ошибками, если понадобится
			$H_validate_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_".$file_re_data_sign.'.html';
			//Проверка
			$H_registryValidate = $this->Reconciliation($xml, $H_xsd_tpl, 'string', $H_validate_err_file);
			//Проверяем 2й реестр
			//Путь до шаблона
			$L_xsd_tpl = $_SERVER['DOCUMENT_ROOT'].$this->L_xsd;
			//Файл с ошибками, если понадобится
			$L_validate_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_".$file_re_pers_data_sign.'.html';
			//Проверка            
			$L_registryValidate = $this->Reconciliation($xml_pers, $L_xsd_tpl, 'string', $L_validate_err_file);
			
			/**/
			if(file_exists($file_re_data_name))
				unlink($file_re_data_name);
			
				file_put_contents($file_re_data_name, $xml);
				$this->textlog->add('exportRegistryToXml: создан '.$file_re_data_name);
			   
				if(file_exists($file_re_pers_data_name))
					unlink($file_re_pers_data_name);               
			   
				file_put_contents($file_re_pers_data_name, $xml_pers);
				$this->textlog->add('exportRegistryToXml: создан '.$file_re_pers_data_name);     
			
				$base_name = $_SERVER["DOCUMENT_ROOT"]."/";
			
			if ($type==2) {
				$file_zip_sign = "P".$registry_data_res['SCHET'][0]['PODR'].'_'.$registry_data_res['SCHET'][0]['code_smo'];
			} elseif ($type==1) {
				$file_zip_sign = "S".$registry_data_res['SCHET'][0]['PODR'].'_'.$registry_data_res['SCHET'][0]['code_smo'];
			} else {
					$file_zip_sign = "M".$registry_data_res['SCHET'][0]['CODE_MO']."T02_".$reg_endmonth."1";
				}
			
				$file_zip_name = EXPORTPATH_REGISTRY.$out_dir."/".$file_zip_sign.".zip";
				$this->textlog->add('exportRegistryToXml: Создали XML-файлы: ('.$file_re_data_name.' и '.$file_re_pers_data_name.')');
			
				$zip=new ZipArchive();
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile( $file_re_data_name, $file_re_data_sign . ".xml" );
				$zip->AddFile( $file_re_pers_data_name, $file_re_pers_data_sign . ".xml" );
				$zip->close();
				$this->textlog->add('exportRegistryToXml: Упаковали в ZIP '.$file_zip_name);
			
				$data['Status'] = $file_zip_name;
				$this->dbmodel->SetXmlExportStatus($data);            
			
				unlink($file_re_data_name);
				unlink($file_re_pers_data_name);
				$this->textlog->add('exportRegistryToXml: Почистили папку за собой ');

				   
			/**/            
			
			if(!$H_registryValidate  && !$L_registryValidate && $res[0]['OrgSmo_id'] != 0){
				$data['Status'] = NULL;
				$this->dbmodel->SetXmlExportStatus($data);

				//Формирование XML не прошедших валидацию
				$this->textlog->add('exportRegistryToXml: Создаем XML файлы с ошибками на диске ');
			
				if(file_exists($file_re_data_name))
					unlink($file_re_data_name);
			
				file_put_contents($file_re_data_name, $xml);
				$this->textlog->add('exportRegistryToXml: создан '.$file_re_data_name);
			
				if(file_exists($file_re_pers_data_name))
					unlink($file_re_pers_data_name);               
			
				file_put_contents($file_re_pers_data_name, $xml_pers);
				$this->textlog->add('exportRegistryToXml: создан '.$file_re_pers_data_name);                
				
				$this->ReturnData(array(
										'success' => false, 
										'Error_Msg' => toUTF(
															'- не пройден ФЛК: <a target="_blank" href="'.$file_re_data_name.'">H файл реестра</a> 
																					(<a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>),
																					<a target="_blank" href="'.$file_re_pers_data_name.'">L файл реестра</a>
																					(<a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a>), 
																					<a href="'.$file_zip_name.'" target="_blank">zip</a> <br/>'),
										'Error_Code' => 20
										)
								 );                                              
			}
			elseif(!$H_registryValidate && $res[0]['OrgSmo_id'] != 0){
				//Скинули статус 
				$data['Status'] = NULL;
				$this->dbmodel->SetXmlExportStatus($data);

				$this->textlog->add('exportRegistryToXml: Создаем XML H файл с ошибками на диске ');
				file_put_contents($file_re_data_name, $xml);
				$this->textlog->add('exportRegistryToXml: создан '.$file_re_data_name);
				
				$this->ReturnData(array(
										'success' => false, 
										'Error_Msg' => toUTF('- файл H реестра не прошёл проверку ФЛК:  
																					<a target="_blank" href="'.$file_re_data_name.'">H файл реестра</a>
																					(<a target="_blank" href="'.$H_validate_err_file.'">отчёт H</a>), 
																					<a href="'.$file_zip_name.'" target="_blank">zip</a><br/>'),
										'Error_Code' => 21
										)
								);
			}
			elseif(!$L_registryValidate && $res[0]['OrgSmo_id'] != 0){
				//Скинули статус  
				$data['Status'] = NULL;
				$this->dbmodel->SetXmlExportStatus($data);                
				
				$this->textlog->add('exportRegistryToXml: Создаем XML L файл с ошибками на диске ');
				file_put_contents($file_re_pers_data_name, $xml);
				$this->textlog->add('exportRegistryToXml: создан '.$file_re_pers_data_name);               
				
				$this->ReturnData(array(
										'success' => false, 
										'Error_Msg' => toUTF('- файл L реестра не прошёл ФЛК:  
																					<a target="_blank" href="'.$file_re_pers_data_name.'">L файл реестра</a>
																					(<a target="_blank" href="'.$L_validate_err_file.'">отчёт L</a>), 
																					<a href="'.$file_zip_name.'" target="_blank">zip</a><br/>'),
										'Error_Code' => 22
										)
								);
			}
			else{
				//Формирование XML и архивов реестров - ФЛК успешно пройден
				$this->ReturnData(array(
										'success' => true, 
										'Registry_xmlExportPath' => $file_zip_name
										)
								 );            
				
			}
			
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
	* ФЛК контроль
	* Метод для формирования листа ошибок при сверке xml по шаблоны xsd
	* Task#18694
	* @return (string)
	*/
	function libxml_display_errors() 
	{
		$errors = libxml_get_errors();
		
		foreach ($errors as $error) 
		{
			$return = "<br/>\n";
	
			switch($error->level) 
			{
				case LIBXML_ERR_WARNING:
					$return .= "<b>Warning $error->code</b>: ";
					break;
				case LIBXML_ERR_ERROR:
					$return .= "<b>Error $error->code</b>: ";
					break;
				case LIBXML_ERR_FATAL:
					$return .= "<b>Fatal Error $error->code</b>: ";
					break;
			} 
	
			$return .= trim($error->message);

			if($error->file) 
			{
				$return .=    " in <b>$error->file</b>";
			}
	
			$return .= " on line <b>$error->line</b>\n";

			print $return;        
		}
	
		libxml_clear_errors();
	}
	

	/**
	* comment
	*/
	function Reconciliation($xml_data, $xsd_tpl, $type = "string", $output_file_name = 'err_xml.html')
	{
		libxml_use_internal_errors(true);  
	
		$xml = new DOMDocument();     
	
		if($type == 'file'){
			$xml->load($xml_data); 
		}
		elseif($type == 'string'){
			$xml->loadXML($xml_data);   
		}
	
		if (!$xml->schemaValidate($xsd_tpl)) {
			ob_start();
			$this->libxml_display_errors();
			$res_errors = ob_get_contents();
			ob_end_clean();
		
			file_put_contents($output_file_name, $res_errors);

			return false;
		}    
		else
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
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml_check_volume/registry_ufa_pl', $registry_data_res, true);
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

}
?>
