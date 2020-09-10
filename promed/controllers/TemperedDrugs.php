<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* TemperedDrugs - контроллер
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Импорт файла отпущенных ЛС в БД.
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       SWAN developers
* @version      29.03.2011
*/

class TemperedDrugs extends swController {
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules['importDrugsFromDbf'] = array(
			array(
				'field' => 'filePath',
				'label' => 'Путь к файлу DBF',
				'rules' => 'required',
				'type' => 'string'
			)
		);
	}

	/**
	 * Description
	 */
	function importDrugsFromDbf() {

		$data = $this->ProcessInputData('importDrugsFromDbf', true);
		if ($data === false) { return false; }

		$this->load->database();
		$this->load->model("TemperedDrugs_model", "dbmodel");

		$recall = 0;
		$h = dbase_open($data['filePath'], 0);
		if ($h) {
			if ($h) {
				if ( !$this->dbmodel->TemperedDrugsDelFromTable() ) {
					$this->ReturnError('Ошибка записи отпущенных ЛС в БД (удаление данных из временной таблицы)');
					dbase_close ($h);
					return false;
				}

				$r = dbase_numrecords($h);
				for ($i=1; $i <= $r; $i++) {
					$data = dbase_get_record_with_names($h, $i);
					array_walk($data, 'ConvertFromWin866ToUTF8');

					if ( !$this->dbmodel->importDrugsFromDbf($data) ){
						$this->ReturnError('Ошибка записи отпущенных ЛС в БД (добавление записи во временную таблицу)');
						dbase_close ($h);
						return false;
					}
					$recall = $i;
				}

				dbase_close ($h);
			}
			
			$response = $this->dbmodel->TemperedDrugsExProcedure();
			if ( !empty($response) ) {
				$this->ReturnError($response);
				return false;
			}
			echo json_encode(array('success' => true, 'Message' => toUTF('Отпущенные ЛС успешно загружены.'), 'Count' => toUTF('Записей добавлено: '.($recall))));
			return true;
		}
		else {
			$this->ReturnError('В файле с отпущенными ЛС обнаружена ошибка !');
			return false;
		}
	}
}
