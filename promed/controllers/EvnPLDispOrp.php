<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispOrp - контроллер для управления талонами диспансеризации детей-сирот
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      май 2010
*/

class EvnPLDispOrp extends swController
{
	/**
	 * Description
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model("EvnPLDispOrp_model", "dbmodel");
		
		$this->inputRules = array(
			'loadEvnPLDispOrpEditForm' => array(
				array(
						'field' => 'EvnPLDispOrp_id',
						'label' => 'Идентификатор талона по диспасеризации детей-сирот',
						'rules' => 'trim|required',
						'type' => 'id'
					)
			),
			'deleteEvnPLDispOrp' => array(
				array(
						'field' => 'EvnPLDispOrp_id',
						'label' => 'Идентификатор талона по диспасеризации детей-сирот',
						'rules' => 'trim|required',
						'type' => 'id'
					)
			),
			'checkIfEvnPLDispOrpExists' => array(
				array(
						'field' => 'Person_id',
						'label' => 'Идентификатор человека',
						'rules' => 'trim|required',
						'type' => 'id'
					)
			),
			'loadEvnVizitDispOrpGrid' => array(
				array(
						'field' => 'EvnPLDispOrp_id',
						'label' => 'Идентификатор талона по диспасеризации детей-сирот',
						'rules' => 'trim|required',
						'type' => 'id'
					)
			),
			'loadEvnUslugaDispOrpGrid' => array(
				array(
						'field' => 'EvnPLDispOrp_id',
						'label' => 'Идентификатор талона по диспасеризации детей-сирот',
						'rules' => 'trim|required',
						'type' => 'id'
					)
			),
			'searchEvnPLDispOrp' => array(
				array(
						'field' => 'DocumentType_id',
						'label' => 'Тип документа удостовряющего личность',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'EvnPLDispOrp_disDate',
						'label' => 'Дата завершения случая',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'EvnPLDispOrp_IsFinish',
						'label' => 'Случай завершен',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'EvnPLDispOrp_setDate',
						'label' => 'Дата начала случая',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'OMSSprTerr_id',
						'label' => 'Территория страхования',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Org_id',
						'label' => 'Место работы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OrgDep_id',
						'label' => 'Организация выдавшая документ',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'OrgSmo_id',
						'label' => 'Страховая компания',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'PersonAge_Min',
						'label' => 'Возраст с',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'PersonAge_Max',
						'label' => 'Возраст по',
						'rules' => 'trim',
						'type' => 'int'
					),
				array(
						'field' => 'PersonCard_Code',
						'label' => 'Номер амб. карты',
						'rules' => 'trim',
						'type' => 'string'
					),
				array(
						'field' => 'LpuRegion_id',
						'label' => 'Участок',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Person_Birthday',
						'label' => 'Дата рождения',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'Person_Surname',
						'label' => 'Фамилия',
						'rules' => 'trim',
						'type' => 'russtring'
					),
				array(
						'field' => 'Person_Firname',
						'label' => 'Имя',
						'rules' => 'trim',
						'type' => 'russtring'
					),
				array(
						'field' => 'Person_Secname',
						'label' => 'Отчество',
						'rules' => 'trim',
						'type' => 'russtring'
					),
				array(
						'field' => 'Person_Snils',
						'label' => 'СНИЛС',
						'rules' => 'trim',
						'type' => 'snils'
					),
				array(
						'field' => 'PolisType_id',
						'label' => 'Тип полиса',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Post_id',
						'label' => 'Должность',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'PrivilegeType_id',
						'label' => 'Категория льготы',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'Sex_id',
						'label' => 'Пол',
						'rules' => 'trim',
						'type' => 'id',
						'default' => -1
					),
				array(
						'field' => 'SocStatus_id',
						'label' => 'Социальный статус',
						'rules' => 'trim',
						'type' => 'id'
					),
			),
			'saveEvnPLDispOrp' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по ДД',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор человека в событии',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_IsFinish',
					'label' => 'Случай закончен',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'AttachType_id',
					'label' => 'Прикреплен для',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_aid',
					'label' => 'ЛПУ постоянного прикрепления',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnVizitDispOrp',
					'label' => 'Массив данных EvnVizitDispOrp',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaDispOrp',
					'label' => 'Массив данных EvnUslugaDispOrp',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadEvnPLDispOrpStreamList' => array(
				array(
					'field' => 'begDate',
					'label' => 'Дата',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'begTime',
					'label' => 'Время',
					'rules' => 'trim|required',
					'type' => 'string'
				)				
			)
		);
		$this->inputRules['searchEvnPLDispOrp'] = array_merge($this->inputRules['searchEvnPLDispOrp'],getAddressSearchFilter());
	}
	
	/**
	 * Печать талона ДД
	 * Входящие данные: $_GET['EvnPLDispOrp_id']
	 * На выходе: форма для печати талона ДД
	 * Используется: форма редактирования талона ДД
	 */
	function printEvnPLDispOrp() {
		$this->load->helper('Options');
		$this->load->library('parser');

		// Получаем сессионные переменные
		$data = getSessionParams();
		$data['EvnPLDispOrp_id'] = NULL;

		if ( (isset($_GET['EvnPLDispOrp_id'])) && (is_numeric($_GET['EvnPLDispOrp_id'])) && ($_GET['EvnPLDispOrp_id'] > 0) ) {
			$data['EvnPLDispOrp_id'] = $_GET['EvnPLDispOrp_id'];
		}

		if ( !isset($data['EvnPLDispOrp_id']) ) {
			echo 'Неверный параметр: EvnPLDispOrp_id';
			return true;
		}

		//// Получаем настройки
		//$options = getOptions();

		// Получаем данные по талону ДД
		$response = $this->dbmodel->getEvnPLDispOrpFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по талону ДД';
			return true;
		}
		
		$evn_vizit_pl_dd_data = array();
		$evn_usluga_pl_dd_data = array();

		$evn_vizit_pl_dd_data = array('1' => array(), '2' => array(), '3' => array(), '4' => array(), '5' => array(), '6' => array());
		foreach ( $evn_vizit_pl_dd_data as $key => $val)
		{
			$evn_vizit_pl_dd_data[$key] = array('', '', '', '', '', '', '', '', '', '', '', '');
		}
		$response_temp = $this->dbmodel->loadEvnVizitDispOrpData($data);
		if ( is_array($response_temp) ) {
			foreach ($response_temp as $row)
			{
				switch ($row['OrpDispSpec_id'])
				{
					case 1: 
						$key = '1';
					break;
					case 2: 
						$key = '2';
					break;
					case 3: 
						$key = '3';
					break;
					case 5: 
						$key = '4';
					break;
					case 6: 
						$key = '5';
					break;
					default: 
						$key = '6';					
				}
				
				$evn_vizit_pl_dd_data[$key][0] = $row['MedPersonal_TabCode'];
				$evn_vizit_pl_dd_data[$key][1] = $row['EvnVizitDispOrp_setDate'];
				if ( $row['DopDispDiagType_id'] == 1 )
				{
					$evn_vizit_pl_dd_data[$key][2]	= $row['Diag_Code'];
				}
				else
				{
					$evn_vizit_pl_dd_data[$key][3]	= $row['Diag_Code'];
				}
				if ( $row['DeseaseStage_id'] == 2 )
					$evn_vizit_pl_dd_data[$key][4]	= $row['Diag_Code'];
				switch ( $row['HealthKind_id'] )
				{
					case 1: 
						$evn_vizit_pl_dd_data[$key][5] = '+';
					break;
					case 2: 
						$evn_vizit_pl_dd_data[$key][6] = '+';
					break;
					case 3: 
						$evn_vizit_pl_dd_data[$key][7] = '+';
						if ( $row['DopDispDiagType_id'] == 2 )
							$evn_vizit_pl_dd_data[$key][8] = '+';
					break;
					case 4: 
						$evn_vizit_pl_dd_data[$key][9] = '+';
					break;
					case 5: 
						$evn_vizit_pl_dd_data[$key][10] = '+';
					break;						
				}
				if ( $row['EvnVizitDispOrp_IsSanKur'] == 2 )
					$evn_vizit_pl_dd_data[$key][11] = '+';
			}
		}
		
		$evn_usluga_pl_dd_data = array('1' => array(), '2' => array(), '3' => array(), '4' => array(), '5' => array(), '6' => array(), '7' => array(), '8' => array(), '9' => array(), '10' => array(), '11' => array(), '12' => array(), '13' => array(), '14' => array(), '15' => array(), '16' => array(), '17' => array(), '18' => array(), '19' => array());
		foreach ( $evn_usluga_pl_dd_data as $key => $val)
		{
			$evn_usluga_pl_dd_data[$key] = array('', '');
		}
		$response_temp = $this->dbmodel->loadEvnUslugaDispOrpData($data);
		if ( is_array($response_temp) ) {
			foreach ($response_temp as $row)
			{
				switch ($row['OrpDispUslugaType_id'])
				{
					case 1: 
						$key = '4';
					break;
					case 2: 
						$key = '11';
					break;
					case 3: 
						$key = '1';
					break;
					case 4: 
						$key = '12';
					break;
					case 5: 
						$key = '17';
					break;
					case 6: 
						$key = '16';
					break;
					case 7: 
						$key = '15';
					break;
					case 8: 
						$key = '19';
					break;
					case 9: 
						$key = '5';
					break;
					case 10: 
						$key = '6';
					break;
					case 11: 
						$key = '13';
					break;
					case 12: 
						$key = '14';
					break;
					case 13: 
						$key = '3';
					break;
					case 14: 
						$key = '7';
					break;
					case 15: 
						$key = '8';
					break;
					case 16: 
						$key = '9';
					break;
					case 17: 
						$key = '10';
					break;
					case 18: 
						$key = '18';
					break;
				}				
				$evn_usluga_pl_dd_data[$key][0] = !empty($row['EvnUslugaDispOrp_setDate'])?$row['EvnUslugaDispOrp_setDate']:'';
				$evn_usluga_pl_dd_data[$key][1] = !empty($row['EvnUslugaDispOrp_didDate'])?$row['EvnUslugaDispOrp_didDate']:'';
			}
		}

		//$template = 'evn_pl_disp_dop_template_list_a4';
		$template = 'evn_pl_disp_orp_template_list_a4';

		$print_data = $response[0];
		$print_data['evn_vizit_pl_dd_data'] = $evn_vizit_pl_dd_data;
		$print_data['evn_usluga_pl_dd_data'] = $evn_usluga_pl_dd_data;

		return $this->parser->parse($template, $print_data);
	}
	
	/**
	 * Удаление талона по доп диспансеризации детей-сирот
	 */
	function deleteEvnPLDispOrp() {
		$data = $this->ProcessInputData('deleteEvnPLDispOrp', true);
		if ($data === false) return false;

		$response = $this->dbmodel->deleteEvnPLDispOrp($data);
		$this->ProcessModelSave($response, true, 'При удалении талона ДД возникли ошибки')->ReturnData();

		return true;
	}


	/**
	 * Проверка на наличие талона на этого человека в этом году
	 * Входящие данные: $_POST['Person_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function checkIfEvnPLDispOrpExists()
	{
		$data = $this->ProcessInputData('checkIfEvnPLDispOrpExists', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->checkIfEvnPLDispOrpExists($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 * Получение данных для формы редактирования талона по ДД
	 * Входящие данные: $_POST['EvnPLDispOrp_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnPLDispOrpEditForm()
	{
		$this->load->helper('Text');
		$this->load->helper('Main');
		
		$val  = array();

		$data = $this->ProcessInputData('loadEvnPLDispOrpEditForm', true);
		if ($data) 
		{
			$response = $this->dbmodel->loadEvnPLDispOrpEditForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}


	/**
	 * Получение списка посещений в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispOrp_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnVizitDispOrpGrid()
	{
		$this->load->helper('Text');
		$this->load->helper('Main');
		
		$data = $this->ProcessInputData('loadEvnVizitDispOrpGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnVizitDispOrpGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}


	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispOrp_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnUslugaDispOrpGrid()
	{
		$this->load->helper('Text');
		$this->load->helper('Main');

		$data = $this->ProcessInputData('loadEvnUslugaDispOrpGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnUslugaDispOrpGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Сохранение талона амбулаторного пациента
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function saveEvnPLDispOrp()
	{
		$val  = array();

		$data = $this->ProcessInputData('saveEvnPLDispOrp', true, true);
		
		if ($data) {
			if ( $data['AttachType_id'] == 1 )
			{
				if ( !isset($data['Lpu_aid']) )
				{
					echo json_return_errors("<p>Поле ЛПУ постоянного прикрепления обязательно для заполнения.</p>");
					return true;
				}					
			}
			
			// Осмотры специалиста
			if ((isset($data['EvnVizitDispOrp'])) && (strlen(trim($data['EvnVizitDispOrp'])) > 0) && (trim($data['EvnVizitDispOrp']) != '[]'))
			{
				$data['EvnVizitDispOrp'] = json_decode(trim($data['EvnVizitDispOrp']), true);
				
				if ( !(count($data['EvnVizitDispOrp']) == 1 && $data['EvnVizitDispOrp'][0]['EvnVizitDispOrp_id'] == '') )
				{
					for ($i = 0; $i < count($data['EvnVizitDispOrp']); $i++) // обработка посещений в цикле
					{
						array_walk($data['EvnVizitDispOrp'][$i], 'ConvertFromUTF8ToWin1251');

						if ((!isset($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate'])) || (strlen(trim($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate'])) == 0))
						{
							echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении осмотра (не задано поле "Дата осмотра")'));
							return false;
						}

						$data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate'] = ConvertDateFormat(trim($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate']));

					}
				}
				else
					$data['EvnVizitDispOrp'] = array();
			} else {
				$data['EvnVizitDispOrp'] = array();
			}

			// Лабораторные исследования
			if ((isset($data['EvnUslugaDispOrp'])) && (strlen(trim($data['EvnUslugaDispOrp'])) > 0) && (trim($data['EvnUslugaDispOrp']) != '[]'))
			{
				$data['EvnUslugaDispOrp'] = json_decode(trim($data['EvnUslugaDispOrp']), true);

				if ( !(count($data['EvnUslugaDispOrp']) == 1 && $data['EvnUslugaDispOrp'][0]['EvnUslugaDispOrp_id'] == '') )
				{
					for ($i = 0; $i < count($data['EvnUslugaDispOrp']); $i++) // обработка услуг в цикле
					{
						array_walk($data['EvnUslugaDispOrp'][$i], 'ConvertFromUTF8ToWin1251');

						if ((!isset($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate'])) || (strlen(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate'])) == 0))
						{
							echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении лабораторного исследования (не задано поле "Дата исследования")'));
							return false;
						}
						
						if ((!isset($data['EvnUslugaDispOrp'][$i]['Usluga_id'])) || (!($data['EvnUslugaDispOrp'][$i]['Usluga_id'] > 0)))
						{
							echo json_encode(array('success' => false, 'cancelErrorHandle'=>true, 'Error_Msg' => toUTF('Ошибка при сохранении лабораторного исследования (не задана услуга)')));
							return false;
						}

						$data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate'] = ConvertDateFormat(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate']));
						$data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_didDate'] = ConvertDateFormat(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_didDate']));

					}
				}
				else
					$data['EvnUslugaDispOrp'] = array();
			} else {
				$data['EvnUslugaDispOrp'] = array();
			}
			
			$server_id = $data['Server_id'];

			$data = array_merge($data, getSessionParams());
			
			$data['Server_id'] = $server_id;

			$response = $this->dbmodel->saveEvnPLDispOrp($data);

			if (is_array($response) && count($response) > 0)
			{
				if ((isset($response[0]['Error_Msg'])) && (strlen($response[0]['Error_Msg']) == 0))
				{
					$val['success'] = true;
				}
				else
				{
					$val = $response[0];
					$val['success'] = false;
					$val['Cancel_Error_Handle'] = true;
					$val['Error_Code'] = 10;
				}
			}
			else
			{
				$val = array('success' => false, 'Error_Msg' => 'В какой-то момент времени что-то пошло не так [2]');
			}

			array_walk($val, 'ConvertFromWin1251ToUTF8');

			$this->ReturnData($val);
		}
	}


	/**
	 * Получение числа талонов с распределением по годам, для формирования списка на клиенте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма поиска/потокового ввода талонов по ДД
	 */
	function getEvnPLDispOrpYears()
	{
		$this->load->helper('Text');
		
		$data = getSessionParams();
		$year = 2012;
		$info = $this->dbmodel->getEvnPLDispOrpYears($data);
   		if ( is_array($info) && count($info) > 0 ) {
			$val = array();
			$flag = false;
	   		foreach ($info as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				if ( $row['EvnPLDispOrp_Year'] == $year )
					$flag = true;
				$val[] = $row;
	        }
			if (!$flag)
				$val[] = array('EvnPLDispOrp_Year'=>$year, 'count'=>0);
	        $this->ReturnData($val);

        }
        else {
        	$val = array();
			$val[] = array('EvnPLDispOrp_Year'=>$year, 'count'=>0);
			$this->ReturnData($val);
		}
	}
}
?>