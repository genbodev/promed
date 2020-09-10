<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Mes - методы для работы с МЭСами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      16.02.2010
 * @property Mes_model dbmodel
*/

class Mes extends swController {
	/**
	 * @dsg
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'loadMesOldVizit'=> array(
				array(
					'field' => 'UslugaComplexPartition_CodeList',
					'label' => 'Список кодов МЭС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_Codes',
					'label' => 'Список кодов МЭС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesType_id',
					'label' => 'MesType_id',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field'	=> 'EvnDate',
					'label'	=> 'Дата события',
					'rules'	=> '',
					'type'	=> 'string'
				)
			),
			'exportMesToDbf' => array(
				array(
					'field' => 'MesStatus_id',
					'label' => 'Статус',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesProf_id',
					'label' => 'Специальность',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesAgeGroup_id',
					'label' => 'Возрастная группа',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesLevel_id',
					'label' => 'Уровень',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_KoikoDni_From',
					'label' => 'Нормативный срок лечения c',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_KoikoDni_To',
					'label' => 'Нормативный срок лечения по',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'OmsLpuUnitType_id',
					'label' => 'Тип стационара',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_begDT_Range',
					'label' => 'Дата начала',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'Mes_endDT_Range',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'daterange'
				)
			),
			'loadMesOldComboSearchList' =>array(				
				array(
					'field' => 'Diag_Name',
					'label' => 'Наименование',
					'rules' => 'trim',
					'type' => 'string'
				)
			),	
			'loadMesOldCodeList' =>array(
				array(
					'field' => 'Mes_id',
					'label' => 'Идентификатор МЭС',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Код МЭС',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadMesSearchList' =>array(
				array(
					'field' => 'MesStatus_id',
					'label' => 'Статус',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesProf_id',
					'label' => 'Специальность',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesAgeGroup_id',
					'label' => 'Возрастная группа',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MesLevel_id',
					'label' => 'Уровень',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_KoikoDni_From',
					'label' => 'Нормативный срок лечения c',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_KoikoDni_To',
					'label' => 'Нормативный срок лечения по',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'OmsLpuUnitType_id',
					'label' => 'Тип стационара',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_begDT_Range',
					'label' => 'Дата начала',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'Mes_endDT_Range',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'default' => 0,
	                'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 100,
	                'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => 'trim',
					'type' => 'int'
				)
			),
			'addMes' =>array(
				array(
					'field' => 'MesProf_id',
					'label' => 'Специальность',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MesAgeGroup_id',
					'label' => 'Возрастная группа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MesLevel_id',
					'label' => 'Уровень',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OmsLpuUnitType_id',
					'label' => 'Тип стационара',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_KoikoDni',
					'label' => 'Нормативный срок лечения',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_begDT',
					'label' => 'Дата начала',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'Mes_endDT',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Mes_DiagClinical',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_DiagVolume',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_Consulting',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_CureVolume',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_QualityMeasure',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_ResultClass',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Mes_ComplRisk',
					'label' => 'Текст',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'action',
					'label' => 'Действие',
					'rules' => 'trim|required',
					'type' => 'string'
				)
			),
			'editMes' =>array(
				array(
					'field' => 'Mes_id',
					'label' => 'Идентификатор МЭСа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_Code',
					'label' => 'Код МЭС',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
			'loadMes' =>array(
				array(
					'field' => 'Mes_id',
					'label' => 'Идентификатор МЭСа',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}
	
	/**
	* Сохранение МЭС
	*/
	function saveMes() {
		$this->load->database();
		$this->load->model("Mes_model", "dbmodel");

		$data = array();
		$val  = array();
		
		$data = array_merge($data, getSessionParams());
		$err = getInputParams($data, $this->inputRules['addMes']);
		if (strlen($err) > 0) 
		{
			echo json_return_errors($err);
			return false;
		}
		
		if ( $data['action'] == 'edit' )
		{
			$err = getInputParams($data, $this->inputRules['editMes'], false);
			if (strlen($err) > 0) 
			{
				echo json_return_errors($err);
				return false;
			}
		}
		
		$response = $this->dbmodel->saveMes($data);
		if (is_array($response) && count($response) > 0) 
		{
			if (!isset($response[0]['success'])) 
			{
				if (strlen($response[0]['Error_Msg']) == 0) 
				{
					$response[0]['success'] = true;
				}
				else
				{
					$response[0]['success'] = false;
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
	* Выгрузка МЭСов в DBF
	*/
	function exportMesToDbf() {
		$this->load->database();
		$this->load->model("Mes_model", "dbmodel");

		if (!isSuperadmin())
		{
			echo "Доступ только для учетной записи администратора СВАН.";
			exit;
		}
		
		$data = array();
		
		$data = array_merge($data, getSessionParams());
		$err = getInputParams($data, $this->inputRules['exportMesToDbf']);
		if (strlen($err) > 0) 
		{
			echo json_return_errors($err);
			return false;
		}
		
		$response = $this->dbmodel->loadMesListForDbf($data);
		
		if ( $response===false )
		{
			echo 'Ошибка: обрыв коннекта. Повторите попытку.';
			return false;
		}
		if ( !is_array($response) || !(count($response) > 0) )
		{
			echo 'В БД нет ' . getMESAlias() . 'ов.';
			return true;
		}
		
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		// формируем массив с описанием полей бд
		
		$mes_def = array(
			array( "codespec", "C",3 , 0 ),
			array( "vzdet", "N",1 , 0 ),
			array( "namespec", "C",50 , 0 ),
			array( "codemkb", "C",5 , 0 ),
			array( "level", "N",1 , 0 ),
			array( "stactype", "N",1 , 0 ),
			array( "stac", "N",3 , 0 ),
			array( "mes_id", "N",10 , 0 ),
			array( "codemes", "C",16 , 0 ),
			array( "namemkb", "C",200 , 0 ),
			array( "datebeg", "D",8 , 0 ),
			array( "dateend", "D",8 , 0 ),
			array( "diagclin", "M"),
			array( "diag_vol", "M"),
			array( "consult", "M",),
			array( "cure_vol", "M"),
			array( "qual_cri", "M"),
			array( "s_result", "M"),
			array( "agg_risk", "M")
		);

		$def_arr = array();
		for ( $i = 0; $i < count($mes_def); $i++ )
		{
			for ( $j = 0; $j < count($mes_def[$i]); $j++ )
				$def_arr[] = $mes_def[$i][$j];
		}
		$def_str = implode($def_arr, ';');
		
		$out_dir = "mes_".time();
		if ( !file_exists(EXPORTPATH_MES) )
			mkdir( EXPORTPATH_MES );
		mkdir( EXPORTPATH_MES.$out_dir );
		
		$file_tmp_sign = "tmp";
		$file_tmp_name = EXPORTPATH_MES.$out_dir."/".$file_tmp_sign.".csv";
		
		$file_mes_sign = "mes";
		$file_mes_name = EXPORTPATH_MES.$out_dir."/".$file_mes_sign.".dbf";
		
		$file_mesfpt_sign = "mes";
		$file_mesfpt_name = EXPORTPATH_MES.$out_dir."/".$file_mesfpt_sign.".FPT";
		
		$file_zip_sign = "mes";
		$file_zip_name = EXPORTPATH_MES.$out_dir."/".$file_zip_sign.".zip";
		
		if ($fh = fopen($file_tmp_name, "w"))
		{
			fwrite($fh, $def_str.chr(13).chr(10));
			foreach ($response as $row)
			{
				// определяем которые даты и конвертируем их
				foreach ($mes_def as $descr)
				{
					if ( $descr[1] == "D" )
						if (!empty($row[$descr[0]]))
							$row[$descr[0]] = date("d/m/Y",strtotime($row[$descr[0]]));
						else
							$row[$descr[0]] = '01/01/1970';
							
				}
				array_walk($row, 'ConvertFromWin1251ToCp866');
				foreach ($row as $key => $value)
				{
					$row[$key] = base64_encode($value);
				}
				$row_str = implode($row, ';');
				fwrite($fh, $row_str.chr(13).chr(10));
			}
			fclose($fh);
		}
		$base_name = $_SERVER["DOCUMENT_ROOT"];
		if ( substr( $base_name, strlen($base_name) - 1, 1 ) != '/' )
			$base_name = $base_name.'/';
		exec("start /B ".$base_name.EXPORTPATH_MES."csvtodbf.exe ".str_replace('/', '\\', $base_name.$file_tmp_name)." ".str_replace('/', '\\', $base_name.$file_mes_name));
		
		$zip=new ZipArchive();			
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $file_mes_name, "mes.dbf" );
		$zip->AddFile( $file_mesfpt_name, "mes.FPT" );
		$zip->close();

		if ( file_exists($file_mes_name) )
		unlink($file_mes_name);
		if ( file_exists($file_mesfpt_name) )
		unlink($file_mesfpt_name);
		if ( file_exists($file_tmp_name) )
		unlink($file_tmp_name);

		// отдаем файл клиенту
		echo json_encode(array("success" => true, "url" => "/".$file_zip_name));
		/*if ($fh = fopen($file_zip_name, "r"))
		{
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=".$file_mes_sign.".zip");
			$file = fread($fh, filesize($file_zip_name));
			print $file;
			fclose($fh);
		}
		else
		{
			echo 'Ошибка создания архива '.getMESAlias().'!';
		}*/
				
	}

	/**
	* Поиск МЭС по наименованию диагноза
	*/
	function loadMesOldComboSearchList() {
		$this->load->database();
		$this->load->model("Mes_model", "dbmodel");

		$data = array();
		$val  = array();
		
		$data = array_merge($data, getSessionParams());
		$err = getInputParams($data, $this->inputRules['loadMesOldComboSearchList']);
		if (strlen($err) > 0) {
			echo json_return_errors($err);
			return false;
		}
		
		$response = $this->dbmodel->loadMesOldComboSearchList($data);		
		
		if (is_array($response['data']) && (count($response['data'])>0)) {
			foreach ($response['data'] as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);
		return true;
	}
	
	/**
	* Для комбобокса код МЭС
	*/
	function loadMesOldCodeList() {
		$this->load->database();
		$this->load->model("Mes_model", "dbmodel");

		$data = array();
		$val  = array();
		
		$data = array_merge($data, getSessionParams());
		$err = getInputParams($data, $this->inputRules['loadMesOldCodeList']);
		if (strlen($err) > 0) {
			echo json_return_errors($err);
			return false;
		}
		
		$response = $this->dbmodel->searchFullMesOldCodeList($data);
		
		if (is_array($response['data']) && (count($response['data'])>0)) {
			foreach ($response['data'] as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);
		return true;
	}
	/**
	 * @dsf
	 */
	function loadMesOldVizit(){
		$this->load->database();
		$this->load->model("Mes_model", "dbmodel");
		$data = $this->ProcessInputData('loadMesOldVizit', true);
		$response = $this->dbmodel->loadMesOldVizit($data);
		$val  = array();
		if (is_array($response['data']) && (count($response['data'])>0)) {
			foreach ($response['data'] as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);
		return true;
		
	}
	/**
	* Поиск по МЭСам
	*/
	function loadMesSearchList() {
		$this->load->database();
		$this->load->model("Mes_model", "dbmodel");

		$data = $this->ProcessInputData('loadMesSearchList', true);
		if ($data === false) {
			return false;
		}
		
		$response = $this->dbmodel->loadMesSearchList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	* Загрузка данных формы МЭСов
	*/
	function loadMes() {
		$this->load->database();
		$this->load->model("Mes_model", "dbmodel");

		$data = array();
		$val  = array();
		
		$data = array_merge($data, getSessionParams());
		$err = getInputParams($data, $this->inputRules['loadMes']);
		if (strlen($err) > 0) 
		{
			echo json_return_errors($err);
			return false;
		}
			
		$response = $this->dbmodel->loadMes($data);
		
		if ( is_array($response) && (count($response)>0) )
		{
			foreach ($response as $row)
			{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
		}
		$this->ReturnData($val);
		return true;
	}
	
}
