<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2009 - 2011 Swan Ltd.
 * @author				Pshenitcyn Ivan
 * @link				http://swan.perm.ru/PromedWeb
 * @version				?
 */

/**
 * Класс контроллера для выгрузки данных для МИАЦ
 */
class MiacExport extends swController {
	
	var $NeedCheckLogin = false;

	public $inputRules = array(		
	);

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('MiacExport_model', 'dbmodel');

		$this->inputRules = array(
			'getMiacExportFileLink' => array (
				array(
					'field' => 'range1',
					'label' => 'Дата 1',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'range2',
					'label' => 'Дата 2',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'default' => 0,
					'field' => 'marker_r',
					'label' => '',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'marker_u',
					'label' => '',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'marker_d',
					'label' => '',
					'rules' => 'trim',
					'type' => 'int'
				)
			),
			'saveMiacExportSheduleOptions' => array (
				array(
					'default' => '00:00',
					'field' => 'DayTime',
					'label' => 'Время ежедневно',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'default' => null,
					'field' => 'IsDay',
					'label' => 'Ежедневная выгрузка',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => null,
					'field' => 'IsMonth',
					'label' => 'Ежемесячная выгрузка',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => null,
					'field' => 'IsWeek',
					'label' => 'Еженедельная выгрузка',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => null,
					'field' => 'Marker_D',
					'label' => 'Маркер D',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => null,
					'field' => 'Marker_R',
					'label' => 'Маркер R',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => null,
					'field' => 'Marker_U',
					'label' => 'Маркер U',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => 1,
					'field' => 'MonthDay',
					'label' => 'День месяца',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => '00:00',
					'field' => 'MonthTime',
					'label' => 'Время месяца',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'default' => '',
					'field' => 'UploadPath',
					'label' => 'Путь для выгрузки',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default' => 1,
					'field' => 'WeekDay',
					'label' => 'День недели',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => '00:00',
					'field' => 'WeekTime',
					'label' => 'Время в дне недели',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'default' => null,
					'field' => 'DataStorage_id',
					'label' => 'Идентификатор записи с настройкой',
					'rules' => 'trim',
					'type' => 'id'
				)
			)
		);
	}
	
	/**
	 * Проверка необходимости выгрузки. Получение необходимых параметров для выгрузки.
	 * На входе:
	 * @options строка с настройками
	 * На выходе:
	 * array(
	 *	'r' => boolean,
	 *  'u' => boolean,
	 *  'd' => boolean,
	 *	'start_date' => 'd.m.Y', // может не быть
	 *  'end_date => 'd.m.Y, // может не быть
	 *  'path' => string
	 * )
	 * или
	 * false, если выгрузка не нужна.
	 */
	private function parseMiacExportOptions($options) {
		$ret = false;
		$options = json_decode($options, true);		
		if ( is_array($options) ) {
			$params_array = array();			
			// проверяем маркеры, если ни один не задан, то сразу возвращаем false
			if ( !$options['Marker_R'] && !$options['Marker_U'] && !$options['Marker_D'] )
				return false;
			// проверяем, какие выгрузки необходимы
			$start_date = false;
			$end_date = false;
			// дневная
			if ( $options['IsDay'] && (abs(time() - strtotime(date('d.m.Y') . ' ' . $options['DayTime'] . ':00')) < 300) ) {
				// берем предыдущий день
				$time = time() - 60*60*24;
				$start_date = date('Y-m-d', $time);
				$end_date = date('Y-m-d 23:59:59', $time);
			}
			
			// недельная
			if ( $options['IsWeek'] && (int)date('N') == $options['WeekDay'] && (abs(time() - strtotime(date('d.m.Y') . ' ' . $options['WeekTime'] . ':00')) < 300) ) {
				// берем предыдущую неделю
				$start_date = date('Y-m-d', time() - 60*60*24 * ((int)date('N') + 6));
				$end_date = date('Y-m-d 23:59:59', time() - 60*60*24 * (int)date('N'));					
			}
			
			// месячная
			if ( $options['IsMonth'] && (int)date('j') == $options['MonthDay'] && (abs(time() - strtotime(date('d.m.Y') . ' ' . $options['MonthTime'] . ':00')) < 300) ) {
				// берем предыдущий месяц
				$start_date = date('Y-m-01', time() - 60*60*24 * (int)date('j'));
				$end_date = date('Y-m-d 23:59:59', time() - 60*60*24 * (int)date('j'));
			}
			if ( $start_date !== false && $end_date !== false )
				return array(
					'r' => $options['Marker_R'],
					'u' => $options['Marker_U'],
					'd' => $options['Marker_D'],
					'start_date' => $start_date,
					'end_date' => $end_date,
					'path' => toAnsi(str_replace('\\', '/', $options['UploadPath']))
				);
		}
		return $ret;
	}
	
	/**
	 * Метод автоматической выгрузки
	 */
	public function autoMiacExport() {
		$res = $this->dbmodel->getMiacExportOptions();
		if ( is_array($res) )
		{
			// пробегаемся по всем ЛПУ
			foreach ($res as $value)
			{
				$lpu_id = $value['Lpu_id'];
				$export_check = $this->parseMiacExportOptions($value['DataStorage_Value']);
				
				echo "ЛПУ: " . $value['Lpu_id'] . "\r\n" . $value['DataStorage_Value'] . " \r\n Опции: \r\n ";
				echo print_r($export_check, true) . " \r\n";
				if ( is_array($export_check) ) {
					echo "Выгрузка нужна\r\n";
					$data = array();
					$data['Lpu_id'] = $lpu_id;
					// получаем инфрмацию о текущей ЛПУ
					$lpu_data = $this->dbmodel->getCurrentLpuData($data);
					if ( $lpu_data === false )
					{
						return false;
					}
					
					$data['range1'] = $export_check['start_date'];
					$data['range2'] = $export_check['end_date'];

					if ( $export_check['r'] === true ) {
						// даные рецептов
						$recepts_data = $this->dbmodel->getReceptsData($data);
						if ( $recepts_data != false )
						{
							echo "Рецепты: " . count($recepts_data) . " \r\n ";
							$file = $this->getReceptsFileData($recepts_data, $lpu_data, $export_check['path']);
							if ( $file != false )
							{
								$response['marker_r_link'] = $file['link'];
								$response['marker_r_filename'] = $file['filename'];
							}
							else
							{
								echo json_return_errors('Не удалось сформировать файл выгрузки.');
								return false;
							}
						}
					}

					// даные посещений
					if ( $export_check['u'] === true )
					{
						$visits_data = $this->dbmodel->getVisitsData($data);
						if ( $visits_data != false )
						{
							echo "Посещения: " . count($visits_data) . " \r\n ";
							$file = $this->getVisitsFileData($visits_data, $lpu_data, $export_check['path']);
							if ( $file != false )
							{
								$response['marker_u_link'] = $file['link'];
								$response['marker_u_filename'] = $file['filename'];
							}
							else
							{
								echo json_return_errors('Не удалось сформировать файл выгрузки.');
								return false;
							}
						}
					}

					// даные о листах нетрудоспособности
					if ( $export_check['d'] === true )
					{
						$sticks_data = $this->dbmodel->getSticksData($data);
						if ( $sticks_data != false )
						{
							echo "Больничные: " . count($visits_data) . " \r\n ";
							$file = $this->getSticksFileData($sticks_data, $lpu_data, $export_check['path']);
							if ( $file != false )
							{
								$response['marker_d_link'] = $file['link'];
								$response['marker_d_filename'] = $file['filename'];
							}
							else
							{
								echo json_return_errors('Не удалось сформировать файл выгрузки.');
								return false;
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * Метод сохраняет настройки автоматической выгрузки для МИАЦ
	 */
	public function saveMiacExportSheduleOptions() {
		$data = $this->ProcessInputData('saveMiacExportSheduleOptions', true);
		if ($data === false) { return false; }
		
		$save_data = array();
		$save_data['Marker_R'] = isset($data['Marker_R']) ? true : false;
		$save_data['Marker_U'] = isset($data['Marker_U']) ? true : false;
		$save_data['Marker_D'] = isset($data['Marker_D']) ? true : false;
		$save_data['IsDay'] = isset($data['IsDay']) ? true : false;
		$save_data['IsWeek'] = isset($data['IsWeek']) ? true : false;
		$save_data['IsMonth'] = isset($data['IsMonth']) ? true : false;
		$save_data['WeekDay'] = $data['WeekDay'];
		$save_data['MonthDay'] = $data['MonthDay'];
		$save_data['DayTime'] = $data['DayTime'];
		$save_data['WeekTime'] = $data['WeekTime'];
		$save_data['MonthTime'] = $data['MonthTime'];
		$save_data['UploadPath'] = toUTF($data['UploadPath']);
				
		$data['SaveData'] = json_encode($save_data);
		
		$result = $this->dbmodel->saveMiacExportSheduleOptions($data);
		if ( $result === false )
		{
			echo json_return_errors('Не удалось сохранить настройки.');
			return false;
		}
	}
	
	/**
	 * Получение текущих настроек, если не удается получить из БД
	 */
	public function getMiacExportSheduleOptions() {
		$data = array();
		$data = array_merge($data, getSessionParams());	
		$default_data = array();
		$default_data['Marker_R'] = false;
		$default_data['Marker_U'] = false;
		$default_data['Marker_D'] = false;
		$default_data['IsDay'] = false;
		$default_data['IsWeek'] = false;
		$default_data['IsMonth'] = false;
		$default_data['WeekDay'] = 1;
		$default_data['MonthDay'] = 1;
		$default_data['DayTime'] = '00:00';
		$default_data['WeekTime'] = '00:00';
		$default_data['MonthTime'] = '00:00';
		$default_data['UploadPath'] = '';
		$default_data['DataStorage_id'] = null;
		
		$saved_data = $this->dbmodel->getMiacExportSheduleOptions($data);
		
		if ( $saved_data === false )
			$saved_data = $default_data;
		
		$this->ReturnData(array($saved_data));
	}
	
	/**
	 * Метод сохраняет xml в файл
	 */
	function saveXmlToFile($filename, $xml, $filename_is_fullpath = false)
	{
		if ( !$filename_is_fullpath )
		{
			$data = array();
			$data = array_merge($data, getSessionParams());
			$base_name = ($_SERVER["DOCUMENT_ROOT"][strlen($_SERVER["DOCUMENT_ROOT"])-1]=="/")?$_SERVER["DOCUMENT_ROOT"]:$_SERVER["DOCUMENT_ROOT"]."/";
			if ( !file_exists($base_name . '/export/miac_export/' . $data['Lpu_id'] . '/') )
				//создаем директорию для LPU
					mkdir ($base_name . '/export/miac_export/' . $data['Lpu_id'] . '/');
			$filepath = $base_name . '/export/miac_export/' . $data['Lpu_id'] . '/' . $filename;
			$filelink = '/export/miac_export/' . $data['Lpu_id'] . '/' . $filename;
		}
		else
		{
			$filepath = $filename;
			$filelink = true;
		}
		if ( file_exists($filepath) )
			unlink ($filepath);
		$success = file_put_contents($filepath, $xml);
		if ( $success )
			return $filelink;
		return false;
	}
	
	/**
	 * Метод формирует файл для выгрузки с информацией о рецептах
	 */
	function getReceptsFileData($recepts_data, $lpu_data, $path = false)	{
		if ( !is_array($recepts_data) || count($recepts_data) == 0 )
			return false;

		// данные для формирования xml
		$recepts_data_forxml = array();

		$recepts_data_forxml['Lpu_Inn'] = $lpu_data['Lpu_Inn'];
		$recepts_data_forxml['Lpu_Kpp'] = $lpu_data['Lpu_Kpp'];
		$recepts_data_forxml['Document_Version'] = 1;
		$recepts_data_forxml['Document_Date'] = date('Y-m-d');
		$recepts_data_forxml['recepts'] = array();

		foreach ( $recepts_data as $rec ) {
			$recepts_data_forxml['recepts'][] = $rec;
		}
		
		$this->load->library('parser');
		$xml = $this->parser->parse('miac_export_recepts', $recepts_data_forxml, true);
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . $xml;
		$xml = toUTF($xml);

		$filename = 'R_' . date('Y') . '.' . date('m') . '.' . date('d') . '.xml';
		
		$use_path = false;
		if ( $path != false )
		{
			$filename = $path . '/' . $filename;
			$use_path = true;
		}
		$link = $this->saveXmlToFile($filename, $xml, $use_path);
		if ( $link )
		{
			return array(
				'link' => $link,
				'filename' => $filename
			);
		}
		return false;
	}

	/**
	 * Метод формирует файл для выгрузки с информацией о посещениях
	 */
	function getVisitsFileData($visits_data, $lpu_data, $path = false)	{
		if ( !is_array($visits_data) || count($visits_data) == 0 )
			return false;

		// данные для формирования xml
		$visits_data_forxml = array();

		$visits_data_forxml['Lpu_Inn'] = $lpu_data['Lpu_Inn'];
		$visits_data_forxml['Lpu_Kpp'] = $lpu_data['Lpu_Kpp'];
		$visits_data_forxml['Document_Version'] = 1;
		$visits_data_forxml['Document_Date'] = date('Y-m-d');
		$visits_data_forxml['visits'] = array();

		foreach ( $visits_data as $rec ) {
			$visits_data_forxml['visits'][] = $rec;
		}

		$this->load->library('parser');
		$xml = $this->parser->parse('miac_export_visits', $visits_data_forxml, true);
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . $xml;
		$xml = toUTF($xml);

		$filename = 'U_' . date('Y') . '.' . date('m') . '.' . date('d') . '.xml';
		
		$use_path = false;
		if ( $path != false )
		{
			$filename = $path . '/' . $filename;
			$use_path = true;
		}

		$link = $this->saveXmlToFile($filename, $xml, $use_path);
		if ( $link )
		{
			return array(
				'link' => $link,
				'filename' => $filename
			);
		}
		return false;
	}

	/**
	 * Метод формирует файл для выгрузки с информацией о больничных листах
	 */
	function getSticksFileData($sticks_data, $lpu_data, $path = false)	{
		if ( !is_array($sticks_data) || count($sticks_data) == 0 )
			return false;

		// данные для формирования xml
		$sticks_data_forxml = array();

		$sticks_data_forxml['Lpu_Inn'] = $lpu_data['Lpu_Inn'];
		$sticks_data_forxml['Lpu_Kpp'] = $lpu_data['Lpu_Kpp'];
		$sticks_data_forxml['Document_Version'] = 1;
		$sticks_data_forxml['Document_Date'] = date('Y-m-d');
		$sticks_data_forxml['sticks'] = array();

		foreach ( $sticks_data as $rec ) {
			$sticks_data_forxml['sticks'][] = $rec;
		}

		$this->load->library('parser');
		$xml = $this->parser->parse('miac_export_sticks', $sticks_data_forxml, true);
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . $xml;
		$xml = toUTF($xml);

		$filename = 'D_' . date('Y') . '.' . date('m') . '.' . date('d') . '.xml';
		
		$use_path = false;
		if ( $path != false )
		{
			$filename = $path . '/' . $filename;
			$use_path = true;
		}

		$link = $this->saveXmlToFile($filename, $xml, $use_path);
		if ( $link )
		{
			return array(
				'link' => $link,
				'filename' => $filename
			);
		}
		return false;
	}
	
	/**
	 * Публичный метод формирования файлов выгрузки
	 * Возвращает на клиента стандартный JSON-ответ с сообщением об успешности формирования или ошибке.
	 * В случае успеха возвращается ссылка для скачивания файла
	 */
	public function getMiacExportFileLink() {
		$data = $this->ProcessInputData('getMiacExportFileLink', true);
		if ($data === false) { return false; }
		
		if ( $data['marker_r'] == 0 && $data['marker_u'] == 0 && $data['marker_d'] == 0 )
		{
			echo json_return_errors('Не выбран ни один файл для выгрузки.');
			return false;
		}

		$response = array(
			'success' => true,
			'marker_r_link' => '',
			'marker_r_filename' => '',
			'marker_u_link' => '',
			'marker_u_filename' => '',
			'marker_d_link' => '',
			'marker_d_filename' => '',
			'error_message' => ''
		);

		// получаем инфрмацию о текущей ЛПУ
		$lpu_data = $this->dbmodel->getCurrentLpuData($data);
		if ( $lpu_data === false )
		{
			echo json_return_errors('Не удалось получить даные о текущей ЛПУ.');
			return false;
		}

		if ( $data['marker_r'] == 1 ) {
			// даные рецептов
			$recepts_data = $this->dbmodel->getReceptsData($data);
			if ( $recepts_data != false )
			{
				$file = $this->getReceptsFileData($recepts_data, $lpu_data);
				if ( $file != false )
				{
					$response['marker_r_link'] = $file['link'];
					$response['marker_r_filename'] = $file['filename'];
				}
				else
				{
					echo json_return_errors('Не удалось сформировать файл выгрузки.');
					return false;
				}
			}
		}

		// даные посещений
		if ( $data['marker_u'] )
		{
			$visits_data = $this->dbmodel->getVisitsData($data);
			if ( $visits_data != false )
			{
				$file = $this->getVisitsFileData($visits_data, $lpu_data);
				if ( $file != false )
				{
					$response['marker_u_link'] = $file['link'];
					$response['marker_u_filename'] = $file['filename'];
				}
				else
				{
					echo json_return_errors('Не удалось сформировать файл выгрузки.');
					return false;
				}
			}
		}

		// даные о листах нетрудоспособности
		if ( $data['marker_d'] )
		{
			$sticks_data = $this->dbmodel->getSticksData($data);
			if ( $sticks_data != false )
			{
				$file = $this->getSticksFileData($sticks_data, $lpu_data);
				if ( $file != false )
				{
					$response['marker_d_link'] = $file['link'];
					$response['marker_d_filename'] = $file['filename'];
				}
				else
				{
					echo json_return_errors('Не удалось сформировать файл выгрузки.');
					return false;
				}
			}
		}

		array_walk($response, 'ConvertFromWin1251ToCp866');
		$this->ReturnData($response);
		return true;
	}
}