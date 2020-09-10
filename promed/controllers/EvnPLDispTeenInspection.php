<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispTeenInspection - контроллер для управления талонами по периодическим осмотрам несовершеннолетних
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Власенко Дмитрий
* @version      01.08.2013
*/

class EvnPLDispTeenInspection extends swController
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model("EvnPLDispTeenInspection_model", "dbmodel");
		$this->inputRules = $this->dbmodel->getInputRulesAdv();
	}
	
	/**
	 * Получение списка диагнозы и рекомендации в осмотре
	 * Входящие данные: $_POST['EvnPLDispTeenInspection_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnDiagAndRecomendationGrid()
	{
		$data = $this->ProcessInputData('loadEvnDiagAndRecomendationGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDiagAndRecomendationGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Получение списка диагнозы и рекомендации в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispDop_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnDiagAndRecomendationSecGrid()
	{
		$data = $this->ProcessInputData('loadEvnDiagAndRecomendationSecGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnDiagAndRecomendationSecGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispDop_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnUslugaDispDopSecGrid()
	{
		$this->load->helper('Text');
		$this->load->helper('Main');

		$data = $this->ProcessInputData('loadEvnUslugaDispDopSecGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnUslugaDispDopSecGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Получение списка посещений в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispDop_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnVizitDispDopSecGrid()
	{
		$data = $this->ProcessInputData('loadEvnVizitDispDopSecGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnVizitDispDopSecGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Получение числа талонов с распределением по годам, для формирования списка на клиенте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма поиска/потокового ввода талонов по ДД
	 */
	function getEvnPLDispTeenInspectionYears()
	{
		$data = $this->ProcessInputData('getEvnPLDispTeenInspectionYears', true);
		if ($data === false) { return false; }

		$year = date('Y');
		$info = $this->dbmodel->getEvnPLDispTeenInspectionYears($data);
		$outdata = $this->ProcessModelList($info, true, true)->GetOutData();
		
		$flag = false;
		foreach ($outdata as $row) {
			if ( $row['EvnPLDispTeenInspection_Year'] == $year ) { $flag = true; }
		}
		if (!$flag) { $outdata[] = array('EvnPLDispTeenInspection_Year'=>$year, 'count'=>0); }
		
		$this->ReturnData($outdata);
	}

	/**
	 * Удаление осмотра
	 */
	function deleteEvnPLDispTeenInspection() {
		$data = $this->ProcessInputData('deleteEvnPLDispTeenInspection', true);
		if ($data === false) { return false; }

		$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
		$registryData = $this->Reg_model->checkEvnAccessInRegistry($data);

        if ( is_array($registryData) ) {
            $response = $registryData;
        } else {
		    $response = $this->dbmodel->deleteEvnPLDispTeenInspection($data);
        }

		$this->ProcessModelSave($response, true, 'При удалении осмотра возникли ошибки')->ReturnData();

		return true;
	}
	
	/**
	*  Проверка на наличие талона на этого человека в этом году
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования талона по ДД
	*/
	function checkIfEvnPLDispTeenInspectionExists()
	{
		$data = $this->ProcessInputData('checkIfEvnPLDispTeenInspectionExists', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->checkIfEvnPLDispTeenInspectionExists($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Сохранение диагнозов и рекомендаций по посещению
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования осмотра
	 */	
	function saveEvnDiagAndRecomendation()
	{
		$data = $this->ProcessInputData('saveEvnDiagAndRecomendation', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveEvnDiagAndRecomendation($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();	
	}
	
	/**
	 * Сохранение осмотра
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования осмотра
	 */
	function saveEvnPLDispTeenInspection()
	{
		$this->load->model('AssessmentHealth_model');
		$ahInputRules = $this->AssessmentHealth_model->getInputRules(swModel::SCENARIO_DO_SAVE);
		unset($ahInputRules['EvnPLDisp_id']); // определится в процессе сохранения.
		$this->inputRules['saveEvnPLDispTeenInspection'] = array_merge($this->inputRules['saveEvnPLDispTeenInspection'], $ahInputRules);

		$data = $this->ProcessInputData('saveEvnPLDispTeenInspection', true);
		if ($data === false) { return false; }

        if( in_array($data['DispClass_id'], array(9, 11)) || $this->dbmodel->checkEvnPLDispTeenInspectionAgeGroup($data) ){
            $response = $this->dbmodel->saveEvnPLDispTeenInspection($data);
        } else {
            $response = array('Error_Msg' => 'На пациента с указанием одной возрастной группы может быть заведена только одна карта за год');
        }

		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	/**
	 * Сохранение осмотра 2 этапа
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования осмотра 2 этапа
	 */
	function saveEvnPLDispTeenInspectionSec()
	{
		$this->load->model('AssessmentHealth_model');
		$ahInputRules = $this->AssessmentHealth_model->getInputRules(swModel::SCENARIO_DO_SAVE);
		unset($ahInputRules['EvnPLDisp_id']); // определится в процессе сохранения.
		$this->inputRules['saveEvnPLDispTeenInspectionSec'] = array_merge($this->inputRules['saveEvnPLDispTeenInspectionSec'], $ahInputRules);

		$data = $this->ProcessInputData('saveEvnPLDispTeenInspectionSec', true);
		if ($data === false) { return false; }

		// Осмотры специалиста
		if ((isset($data['EvnVizitDispDop'])) && (strlen(trim($data['EvnVizitDispDop'])) > 0) && (trim($data['EvnVizitDispDop']) != '[]'))
		{
			$data['EvnVizitDispDop'] = json_decode(trim($data['EvnVizitDispDop']), true);

			if ( !(count($data['EvnVizitDispDop']) == 1 && $data['EvnVizitDispDop'][0]['EvnVizitDispDop_id'] == '') )
			{
				for ($i = 0; $i < count($data['EvnVizitDispDop']); $i++) // обработка посещений в цикле
				{
					array_walk($data['EvnVizitDispDop'][$i], 'ConvertFromUTF8ToWin1251');

					if ((!isset($data['EvnVizitDispDop'][$i]['EvnVizitDispDop_setDate'])) || (strlen(trim($data['EvnVizitDispDop'][$i]['EvnVizitDispDop_setDate'])) == 0))
					{
						echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении осмотра (не задано поле "Дата осмотра")'));
						return false;
					}

					$data['EvnVizitDispDop'][$i]['EvnVizitDispDop_setDate'] = ConvertDateFormat(trim($data['EvnVizitDispDop'][$i]['EvnVizitDispDop_setDate']));

					if (!empty($data['EvnVizitDispDop'][$i]['EvnVizitDispDop_disDate'])) {
						$data['EvnVizitDispDop'][$i]['EvnVizitDispDop_disDate'] = ConvertDateFormat(trim($data['EvnVizitDispDop'][$i]['EvnVizitDispDop_disDate']));
					}

				}
			}
			else
				$data['EvnVizitDispDop'] = array();
		} else {
			$data['EvnVizitDispDop'] = array();
		}

		// Лабораторные исследования
		if ((isset($data['EvnUslugaDispDop'])) && (strlen(trim($data['EvnUslugaDispDop'])) > 0) && (trim($data['EvnUslugaDispDop']) != '[]'))
		{
			$data['EvnUslugaDispDop'] = json_decode(trim($data['EvnUslugaDispDop']), true);

			if ( !(count($data['EvnUslugaDispDop']) == 1 && $data['EvnUslugaDispDop'][0]['EvnUslugaDispDop_id'] == '') )
			{
				for ($i = 0; $i < count($data['EvnUslugaDispDop']); $i++) // обработка услуг в цикле
				{
					array_walk($data['EvnUslugaDispDop'][$i], 'ConvertFromUTF8ToWin1251');

					if ((!isset($data['EvnUslugaDispDop'][$i]['EvnUslugaDispDop_setDate'])) || (strlen(trim($data['EvnUslugaDispDop'][$i]['EvnUslugaDispDop_setDate'])) == 0))
					{
						echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении лабораторного исследования (не задано поле "Дата исследования")'));
						return false;
					}
					
					if ((!isset($data['EvnUslugaDispDop'][$i]['UslugaComplex_id'])) || (!($data['EvnUslugaDispDop'][$i]['UslugaComplex_id'] > 0)))
					{
						echo json_encode(array('success' => false, 'cancelErrorHandle'=>true, 'Error_Msg' => toUTF('Ошибка при сохранении лабораторного исследования (не задана услуга)')));
						return false;
					}

					$data['EvnUslugaDispDop'][$i]['EvnUslugaDispDop_setDate'] = ConvertDateFormat(trim($data['EvnUslugaDispDop'][$i]['EvnUslugaDispDop_setDate']));
					$data['EvnUslugaDispDop'][$i]['EvnUslugaDispDop_disDate'] = ConvertDateFormat(trim($data['EvnUslugaDispDop'][$i]['EvnUslugaDispDop_disDate']));
					$data['EvnUslugaDispDop'][$i]['EvnUslugaDispDop_didDate'] = ConvertDateFormat(trim($data['EvnUslugaDispDop'][$i]['EvnUslugaDispDop_didDate']));

				}
			}
			else
				$data['EvnUslugaDispDop'] = array();
		} else {
			$data['EvnUslugaDispDop'] = array();
		}

		// грид диагнозы и рекомендации
		$data['EvnDiagAndRecomendation'] = toUtf($data['EvnDiagAndRecomendation']);
		if ((isset($data['EvnDiagAndRecomendation'])) && (strlen(trim($data['EvnDiagAndRecomendation'])) > 0) && (trim($data['EvnDiagAndRecomendation']) != '[]'))
		{
			$data['EvnDiagAndRecomendation'] = json_decode(trim($data['EvnDiagAndRecomendation']), true);
		} else {
			$data['EvnDiagAndRecomendation'] = array();
		}
			
        if( in_array($data['DispClass_id'], array(9, 11)) || $this->dbmodel->checkEvnPLDispTeenInspectionAgeGroup($data) ){
            $response = $this->dbmodel->saveEvnPLDispTeenInspectionSec($data);
        } else {
            $response = array('Error_Msg' => 'На пациента с указанием одной возрастной группы может быть заведена только одна карта за год');
        }

		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}
	
	/**
	 * Сохранение данных по информир. добр. согласию
	 */
	function saveDopDispInfoConsent() {
		$data = $this->ProcessInputData('saveDopDispInfoConsent', true);
		if ($data === false) { return false; }

        if( in_array($data['DispClass_id'], array(9, 11)) || $this->dbmodel->checkEvnPLDispTeenInspectionAgeGroup($data) ){
            $response = $this->dbmodel->saveDopDispInfoConsent($data);
        } else {
            $response = array('Error_Msg' => 'На пациента с указанием одной возрастной группы может быть заведена только одна карта за год');
        }

		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}
	
	/**
	*  Получение грида "информированное добровольное согласие по ДД 2013"
	*  Входящие данные: EvnPLDispTeenInspection_id
	*/	
	function loadDopDispInfoConsent() {
		$data = $this->ProcessInputData('loadDopDispInfoConsent', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadDopDispInfoConsent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispTeenInspection_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnUslugaDispDopGrid()
	{
		$data = $this->ProcessInputData('loadEvnUslugaDispDopGrid', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnUslugaDispDopGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 *	Получение данных для формы редактирования карты мед. осмотра несовершеннолетнего
	 */
	function loadEvnPLDispTeenInspectionEditForm()
	{
		$data = $this->ProcessInputData('loadEvnPLDispTeenInspectionEditForm', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadEvnPLDispTeenInspectionEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Получение данных формы направления на осмотр (исследование)
	 * Входящие данные: $_POST['EvnUslugaDispDop_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnUslugaDispDopDirection()
	{
		$data = $this->ProcessInputData('loadEvnUslugaDispDopDirection', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnUslugaDispDopDirection($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	*  Получение грида для направлений
	*/	
	function loadEvnUslugaDispDopGridForDirection() {
		$data = $this->ProcessInputData('loadEvnUslugaDispDopGridForDirection', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadEvnUslugaDispDopGridForDirection($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Сохранение направления на осмотр (исследование)
	*/	
	function saveEvnUslugaDispDopDirection() {
		$data = $this->ProcessInputData('saveEvnUslugaDispDopDirection', true);
		if ($data === false) { return false; }
	
		$data['AgeGroupDisp_id'] = $this->dbmodel->getAgeGroupDispFromPersonDispOrp($data);
		$data['EvnPLDispTeenInspection_consDate'] = $this->dbmodel->getPersonDispOrpSetDateFromPersonDispOrp($data);

	    if( in_array($data['DispClass_id'], array(9, 11)) || $this->dbmodel->checkEvnPLDispTeenInspectionAgeGroup($data) ){
            $response = $this->dbmodel->saveEvnUslugaDispDopDirection($data);
        } else {
            $response = array('Error_Msg' => 'На пациента с указанием одной возрастной группы может быть заведена только одна карта за год');
        }
		
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении направления на осмотр (исследование)')->ReturnData();
	}
	
	/**
	*  Сохранение осмотра
	*/	
	function saveEvnVizitDispDop() {
		$data = $this->ProcessInputData('saveEvnVizitDispDop', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnVizitDispDop($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}
	
	/**
	*  Удаление осмотра
	*/	
	function deleteEvnVizitDispDop() {
		$data = $this->ProcessInputData('deleteEvnVizitDispDop', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEvnVizitDispDop($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}
}
?>