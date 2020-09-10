<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusHIV - Специфика по ВИЧ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *-TODO: Do some explanation, preamble and describing
 * @package      Foobaring
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Markoff
 * @version      2012-10
 */
/**
 * @property MorbusHIV_model $dbmodel
 */
class MorbusHIV extends swController
{
    var $model_name = "MorbusHIV_model";

	/**
	 * Типадок
	*/
    function __construct()
    {
		parent::__construct();
		$this->load->database();
		$this->load->model($this->model_name, 'dbmodel');
		$this->inputRules = array(
			'saveMorbusSpecific' => array(
				array('field' => 'Morbus_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
				//array('field' => 'MorbusBase_id','label' => 'Идентификатор заболевания','rules' => 'required', 'type' => 'id'),
				//array('field' => 'Morbus_setDT','label' => 'Дата начала заболевания','rules' => '', 'type' => 'date'),
				//array('field' => 'Morbus_disDT','label' => 'Дата закрытия карты (снятия с учета)','rules' => '', 'type' => 'date'),
				array('field' => 'Person_id','label' => 'Пациент','rules' => 'required', 'type' => 'id'),
				array('field' => 'Evn_pid','label' => 'Учетный документ (движение/посещение поликлиники)','rules' => '', 'type' => 'id'),// из которого редактируется специфика
				
				array('field' => 'MorbusHIV_id','label' => 'Специфика','rules' => 'required', 'type' => 'id'),
				array('field' => 'MorbusHIV_DiagDT','label' => 'Дата установления диагноза','rules' => '', 'type' => 'date'),			
				array('field' => 'HIVPregPathTransType_id','label' => 'Предполагаемый путь инфицирования','rules' => '', 'type' => 'id'),
				array('field' => 'HIVPregInfectStudyType_id','label' => 'Стадия ВИЧ-инфекции','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIV_CountCD4','label' => 'Количество CD4 Т-лимфоцитов (мм)','rules' => '', 'type' => 'int'),
				array('field' => 'MorbusHIV_PartCD4','label' => 'Процент содержания CD4 Т-лимфоцитов','rules' => '', 'type' => 'float'),
				array('field' => 'MorbusHIVOut_endDT','label' => 'Дата снятия с диспансерного наблюдения','rules' => '', 'type' => 'date'),
				array('field' => 'HIVDispOutCauseType_id','label' => 'Причина снятия с диспансерного наблюдения','rules' => '', 'type' => 'id'),
				array('field' => 'DiagD_id','label' => 'Причина смерти','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIV_NumImmun','label' => '№ иммуноблота','rules' => '', 'type' => 'int'),

				array('field' => 'HIVContingentTypeP_id','label' => 'Гражданство','rules' => '', 'type' => 'id'),
				array('field' => 'HIVContingentType_id_list','label' => 'Код контингента','rules' => '', 'type' => 'string'),//id через запятую
				array('field' => 'MorbusHIV_confirmDate','label' => 'Дата подтверждения диагноза','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusHIV_EpidemCode','label' => 'Эпидемиологический код','rules' => '', 'type' => 'string'),

				array('field' => 'MorbusHIVLab_id','label' => 'Лабораторная диагностика ВИЧ-инфекции','rules' => '', 'type' => 'id'),			
				array('field' => 'MorbusHIVLab_BlotDT','label' => 'Дата постановки реакции иммуноблота','rules' => '','type' => 'date'),
				array('field' => 'MorbusHIVLab_TestSystem','label' => 'Тип тест-системы','rules' => 'max_length[64]','type' => 'string'),
				array('field' => 'MorbusHIVLab_BlotNum','label' => 'N серии','rules' => 'max_length[64]','type' => 'string'),
				array('field' => 'MorbusHIVLab_BlotResult','label' => 'Выявленные белки и гликопротеиды','rules' => 'max_length[100]','type' => 'string'),
				array('field' => 'Lpuifa_id','label' => 'Учреждение, первично выявившее положительный результат в ИФА','rules' => '','type' => 'id'),
				array('field' => 'MorbusHIVLab_IFADT','label' => 'Дата ИФА','rules' => '','type' => 'date'),
				array('field' => 'MorbusHIVLab_IFAResult','label' => 'Результат ИФА','rules' => 'max_length[30]','type' => 'string'),
				array('field' => 'MorbusHIVLab_PCRDT','label' => 'Дата ПЦР','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusHIVLab_PCRResult','label' => 'Результат ПЦР','rules' => '', 'type' => 'string'),
				array('field' => 'LabAssessmentResult_iid','label' => 'Результат рекции иммунноблота','rules' => '', 'type' => 'id'),
				array('field' => 'LabAssessmentResult_cid','label' => 'Результат полимеразной цепной реакции','rules' => '', 'type' => 'id'),

				array('field' => 'HIVInfectType_id','label' => 'Тип вируса','rules' => '','type' => 'id'),
				array('field' => 'Mode','label' => 'Режим сохранения','rules' => '', 'type' => 'string')
			),
			'loadMorbusHIVChemPreg' => array(
				array('field' => 'MorbusHIVChemPreg_id','label' => 'Идентификатор записи','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyBase_id','label' => 'Идентификатор извещения','rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id','label' => 'Препарат','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIVChemPreg_Dose','label' => 'Доза','rules' => '', 'type' => 'string'),
				array('field' => 'HIVPregnancyTermType_id','label' => 'Период','rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id','label' => 'Учетный документ (движение/посещение поликлиники)','rules' => '', 'type' => 'id'),
			),
			'loadMorbusHIVChem' => array(
				array('field' => 'MorbusHIVChem_id','label' => 'Идентификатор записи','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyBase_id','label' => 'Идентификатор извещения','rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id','label' => 'Препарат','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIVChem_Dose','label' => 'Доза','rules' => '', 'type' => 'string'),
				array('field' => 'MorbusHIVChem_begDT','label' => 'Дата начала','rules' => '', 'type' => 'date'),
				array('field' => 'MorbusHIVChem_endDT','label' => 'Дата окончания','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Учетный документ (движение/посещение поликлиники)','rules' => '', 'type' => 'id'),
			),
			'loadMorbusHIVVac' => array(
				array('field' => 'MorbusHIVVac_id','label' => 'Идентификатор записи','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyBase_id','label' => 'Идентификатор извещения','rules' => '', 'type' => 'id'),
				array('field' => 'Drug_id','label' => 'Препарат','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIVVac_setDT','label' => 'Дата записи','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Учетный документ (движение/посещение поликлиники)','rules' => '', 'type' => 'id'),
			),
			'loadMorbusHIVSecDiag' => array(
				array('field' => 'MorbusHIVSecDiag_id','label' => 'Идентификатор записи','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIV_id','label' => 'Идентификатор специфики заболевания ВИЧ','rules' => '', 'type' => 'id'),
				array('field' => 'EvnNotifyBase_id','label' => 'Идентификатор извещения','rules' => '', 'type' => 'id'),
				array('field' => 'Diag_id','label' => 'Заболевание','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIVSecDiag_setDT','label' => 'Дата записи','rules' => '', 'type' => 'date'),
				array('field' => 'Evn_id','label' => 'Учетный документ (движение/посещение поликлиники)','rules' => '', 'type' => 'id'),
			),
			'definePatriality' => array(
				array('field' => 'Person_id','label' => 'Человек','rules' => 'required', 'type' => 'id'),
			),
			'defineBirthSvidLpu' => array(
				array('field' => 'Person_id','label' => 'Ребенок','rules' => 'required', 'type' => 'id'),
			),
			'getHIVContingentType' => array(
				array('field' => 'Region_id','label' => 'ID региона','rules' => '', 'type' => 'id'),
				array('field' => 'Nationality','label' => 'Национальность','rules' => '', 'type' => 'id'),
				array('field' => 'MorbusHIV_id','label' => 'Связь случая и типа контингента','rules' => '', 'type' => 'id'),
				array('field' => 'Checked','label' => 'Выбранный пункт','rules' => '', 'type' => 'id'),
				array('field' => 'HIVContingentType_id','label' => 'ID Контингента','rules' => '', 'type' => 'id'),
				array('field' => 'HIVContingentType_code','label' => 'CODE Контингента','rules' => '', 'type' => 'id'),
			),
			'exportToXLS' => array(
				array('field' => 'Lpu_oid','label' => 'МО','rules' => '', 'type' => 'id'),
				array('field' => 'Range','label' => 'Период','rules' => 'required', 'type' => 'daterange'),
				array('field' => 'ExportType_id','label' => 'Тип включения в файл','rules' => 'required', 'type' => 'id'),
			),
        );
    }

	/**
	 * Типадок
	*/
	function definePatriality()
    {
        $data = $this->ProcessInputData('definePatriality', true);
        if ($data) {
            $response = $this->dbmodel->definePatriality($data);
            $this->ProcessModelSave($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

	/**
	 * Типадок
	*/
	function defineBirthSvidLpu()
    {
        $data = $this->ProcessInputData('defineBirthSvidLpu', true);
        if ($data) {
            $response = $this->dbmodel->defineBirthSvidLpu($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

	/**
	 * Сохранение специфики по ВИЧ
	*/
	function saveMorbusSpecific()
	{
		$data = $this->ProcessInputData('saveMorbusSpecific', true);
		if ($data) {
			$data['HIVContingentType_pid'] = $data['HIVContingentTypeP_id'];
			$data['Diag_cid'] = $data['DiagD_id'];
			$response = $this->dbmodel->saveMorbusSpecific($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Типадок
	*/
    function loadMorbusHIVChemPreg() {
		$data = $this->ProcessInputData('loadMorbusHIVChemPreg', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusHIVChemPregViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Типадок
	*/
	function saveMorbusHIVChemPreg() {
		$data = $this->ProcessInputData('loadMorbusHIVChemPreg', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusHIVChemPreg($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Типадок
	*/
    function loadMorbusHIVChem() {
		$data = $this->ProcessInputData('loadMorbusHIVChem', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusHIVChemViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Типадок
	*/
	function saveMorbusHIVChem() {
		$data = $this->ProcessInputData('loadMorbusHIVChem', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusHIVChem($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Типадок
	*/
	function loadMorbusHIVVac() {
		$data = $this->ProcessInputData('loadMorbusHIVVac', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusHIVVacViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Типадок
	*/
	function saveMorbusHIVVac() {
		$data = $this->ProcessInputData('loadMorbusHIVVac', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusHIVVac($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Типадок
	*/
	function loadMorbusHIVSecDiag() {
		$data = $this->ProcessInputData('loadMorbusHIVSecDiag', true);
		if ($data) {
			$response = $this->dbmodel->getMorbusHIVSecDiagViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Типадок
	*/
	function saveMorbusHIVSecDiag() {
		$data = $this->ProcessInputData('loadMorbusHIVSecDiag', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusHIVSecDiag($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение типов контингента с кодами
	 */
	function getHIVContingentType() {
		$data = $this->ProcessInputData('getHIVContingentType', true);
		if ($data) {
			$response = $this->dbmodel->getHIVContingentType($data);
			$this->ProcessModelMultiList( $response, true, true )->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Выгрузка сведений регистра ВИЧ в Excel
	 * @return bool
	 */
	function exportToXLS() {
		$data = $this->ProcessInputData('exportToXLS', true);
		if (!$data) return false;

		$exportData = $this->dbmodel->getDataForXLS($data);
		if (!is_array($exportData)) {
			$this->ReturnError('Ошибка при запросе данных для выгрузки');
			return false;
		}
		if (count($exportData) == 0) {
			$this->ReturnError('Отсутствуют данные для выгрузки');
			return false;
		}

		$fieldsMap = $this->dbmodel->getFieldsMapForXLS();

		$fileName = 'tub_register_'.time();

		require_once('vendor/autoload.php');
		$objPHPExcel = new PhpOffice\PhpSpreadsheet\Spreadsheet();
		$objPHPExcel->getProperties();
		$objPHPExcel->getActiveSheet()->setTitle('Лист1');
		$sheet = $objPHPExcel->setActiveSheetIndex(0);

		$colIdx = 0;
		$rowIdx = 1;
		foreach($fieldsMap as $name => $title) {
			$sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $title);
			$colIdx++;
		}
		foreach($exportData as $rowData) {
			$rowIdx++;
			$colIdx = 0;
			foreach($fieldsMap as $name => $title) {
				if (isset($rowData[$name])) {
					$sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $rowData[$name]);
				}
				$colIdx++;
			}
		}

		$objWriter = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);

		$path = EXPORTPATH_ROOT.'tub_register';
		if (!file_exists($path)) {
			mkdir($path);
		}

		$file = "{$path}/{$fileName}.xlsx";
		$objWriter->save($file);

		$response = array('success' => true, 'file' => $file);

		$this->ProcessModelSave($response, true, 'Ошибка при выгрузке')->ReturnData();
		return true;
	}

    /*
	function save()
    {
        $data = $this->ProcessInputData('save', true);
        if ($data) {
            $this->dbmodel->assign($data);
            $response = $this->dbmodel->save();
            $this->ProcessModelSave($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    function load() {
   		$data = $this->ProcessInputData('load', true);
   		if ($data) {
            $response = $this->dbmodel->load($data['Morbus_id'], $data['Evn_id']);
   			$this->ProcessModelList(array($response), true, true)->formatDatetimeFields()->ReturnData();
   			return true;
   		} else {
   			return false;
   		}
   	}

	function getHIVDiag() {
		$data = $this->ProcessInputData('getHIVDiag', true);
		if ($data) {
			$response = $this->dbmodel->getHIVDiag($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	 **/

}
