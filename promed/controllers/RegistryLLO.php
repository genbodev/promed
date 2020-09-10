<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Контроллер для реестров рецептов
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version		07.2015
* @property 	RegistryLLO_model RegistryLLO_model
*/

class RegistryLLO extends swController {
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();

		$this->load->database();
		$this->load->model("RegistryLLO_model", "RegistryLLO_model");
		
		$this->inputRules = array(
			'save' => array(
				array('field' => 'RegistryLLO_id', 'label' => 'Реестр рецептов', 'rules' => '', 'type' => 'id'),
				array('field' => 'KatNasel_id', 'label' => 'Категория населения', 'rules' => '', 'type' => 'id'),
				array('field' => 'RegistryLLO_Date_Range', 'label' => 'Период', 'rules' => '', 'type' => 'daterange'),
				array('field' => 'RegistryLLO_accDate', 'label' => 'Дата реестра', 'rules' => '', 'type' => 'date'),
				array('field' => 'RegistryType_Code', 'label' => 'Код типа реестра', 'rules' => '', 'type' => 'int'),
				array('field' => 'RegistryStatus_id', 'label' => 'Cтатус', 'rules' => '', 'type' => 'id'),
				array('field' => 'RegistryStatus_Code', 'label' => 'Код статуса', 'rules' => '', 'type' => 'int'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расходов', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentSupply_id', 'label' => 'Контракт', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'id')
			),
            'saveRegistryLLOError' => array(
                array('field' => 'RegistryLLOError_id', 'label' => 'Ошибка', 'rules' => '', 'type' => 'id'),
                array('field' => 'RegistryLLO_id', 'label' => 'Реестр рецептов', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'ReceptOtov_id', 'label' => 'Рецепт', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'RegistryReceptErrorType_id', 'label' => 'Тип ошибки', 'rules' => 'required', 'type' => 'id')
            ),
			'load' => array(
				array('field' => 'RegistryLLO_id', 'label' => 'Реестр рецептов', 'rules' => '', 'type' => 'id')
			),
			'delete' => array(
				array('field' => 'id', 'label' => 'Реестр рецептов', 'rules' => 'required', 'type' => 'id')
			),
			'deleteRegistryLLOError' => array(
				array('field' => 'id', 'label' => 'Ошибка', 'rules' => 'required', 'type' => 'id')
			),
			'deleteRegistryDataRecept' => array(
				array('field' => 'id', 'label' => 'Рецепт', 'rules' => 'required', 'type' => 'id')
			),
			'loadList' => array(
				array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'id'),
				array('field' => 'KatNasel_id', 'label' => 'Категория населения', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Программа ЛЛО', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер контракта', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'RegistryStatus_id', 'label' => 'Статус', 'rules' => '', 'type' => 'id'),
				array('field' => 'RegistryStatus_Code', 'label' => 'Код статуса', 'rules' => '', 'type' => 'int'),
				array('field' => 'RegistryLLO_Date_Range', 'label' => 'Период', 'rules' => 'trim', 'type' => 'daterange'),
				array('field' => 'MinSum', 'label' => 'Минимальная сумма', 'rules' => '', 'type' => 'float'),
				array('field' => 'MaxSum', 'label' => 'Максимальная сумма', 'rules' => '', 'type' => 'float'),
				array('field' => 'Year', 'label' => 'Год', 'rules' => '', 'type' => 'int'),
				array('field' => 'ReceptUploadLog_setDT_range', 'label' => 'Дата передачи на экспертизу', 'rules' => '', 'type' => 'daterange'),
				array('field' => 'ReceptUploadStatus_id', 'label' => 'Статус реестра по экспертизе', 'rules' => '', 'type' => 'id'),
				array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100)
			),
			'loadRegistryDataReceptList' => array(
                array('field' => 'RegistryDataRecept_id', 'label' => 'Рецепт', 'rules' => '', 'type' => 'id'),
                array('field' => 'RegistryLLO_id', 'label' => 'Реестр', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'SupplierContragent_id', 'label' => 'Контрагент поставщика', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentUc_Num', 'label' => 'Номер контракта', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentCostItemType_id', 'label' => 'Статья расхода', 'rules' => '', 'type' => 'id'),
                array('field' => 'ReceptStatusFLKMEK_id', 'label' => 'Статус рецепта в реестре', 'rules' => '', 'type' => 'id'),
                array('field' => 'RegistryReceptErrorType_id', 'label' => 'Ошибка при экспертизе', 'rules' => '', 'type' => 'id'),
                array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'Person_SecName', 'label' => 'отчество', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'Person_Snils', 'label' => 'СНИЛС', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'EvnRecept_Ser', 'label' => 'Серия рецепта', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'EvnRecept_Num', 'label' => 'Номер рецепта', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'PrivilegeType_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
                array('field' => 'MedPersonal_Name', 'label' => 'Врач', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'Lpu_id', 'label' => 'ЛПУ', 'rules' => '', 'type' => 'id'),
                array('field' => 'EvnRecept_otpDate_Range', 'label' => 'Период обспечения', 'rules' => 'trim', 'type' => 'daterange'),
                array('field' => 'FarmacyContragent_id', 'label' => 'Контрагент аптеки', 'rules' => '', 'type' => 'id'),
                array('field' => 'Recept_isAfterDelay', 'label' => 'Признак отсрочки', 'rules' => '', 'type' => 'int'),
                array('field' => 'DrugComplexMnn_Name', 'label' => 'Комплексное МНН', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'Drug_Name', 'label' => 'Медикамент', 'rules' => 'trim', 'type' => 'string'),
                array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
                array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 100)
			),
            'loadRegistryLLOErrorList' => array(
                array('field' => 'RegistryLLO_id', 'label' => 'Реестр', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'ReceptOtov_id', 'label' => 'Рецепт', 'rules' => '', 'type' => 'id')
            ),
            'loadRegistryLLOExpertiseForm' => array(
                array('field' => 'RegistryLLO_id', 'label' => 'Реестр', 'rules' => 'required', 'type' => 'id'),
            ),
			'saveRegistryLLOExpertise' => array(
				array('field' => 'RegistryLLO_id', 'label' => 'Реестр', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'ReceptUploadStatus_id', 'label' => 'Статус реестра по экспертизе', 'rules' => 'required', 'type' => 'id'),
			),
			'forming' => array(
				array('field' => 'RegistryLLO_id', 'label' => 'Реестр рецептов', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'reforming', 'label' => 'Признак переформирования', 'rules' => '', 'type' => 'string')
			),
			'expertise' => array(
				array('field' => 'RegistryLLO_id', 'label' => 'Реестр рецептов', 'rules' => 'required', 'type' => 'id')
			),
			'recount' => array(
				array('field' => 'RegistryLLO_id', 'label' => 'Реестр рецептов', 'rules' => 'required', 'type' => 'id')
			),
			'setRegistryStatus' => array(
				array('field' => 'RegistryLLO_id', 'label' => 'Реестр рецептов', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'RegistryStatus_Code', 'label' => 'Код статуса реестра', 'rules' => 'required', 'type' => 'int')
			),
			'setReceptStatus' => array(
				array('field' => 'RegistryDataRecept_id', 'label' => 'Рецепт', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'ReceptStatusFLKMEK_Code', 'label' => 'Код статуса рецепта', 'rules' => 'required', 'type' => 'int')
			),
			'loadLpuCombo' => array(
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string')
			)
		);
	}

	/**
	 * Сохранение реестра рецептов
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (is_array($data['RegistryLLO_Date_Range']) && count($data['RegistryLLO_Date_Range']) == 2) {
				$data['RegistryLLO_begDate'] = $data['RegistryLLO_Date_Range'][0];
				$data['RegistryLLO_endDate'] = $data['RegistryLLO_Date_Range'][1];
			}

			if (empty($data['RegistryType_id'])) {
                if (!empty($data['RegistryType_Code'])) {
                    $data['RegistryType_id'] = $this->RegistryLLO_model->getObjectIdByCode('RegistryType', $data['RegistryType_Code']);
                } else {
                    unset($data['RegistryType_id']);
                }
            }

			if (empty($data['RegistryStatus_id']) && !empty($data['RegistryStatus_Code'])) {
				$data['RegistryStatus_id'] = $this->RegistryLLO_model->getObjectIdByCode('RegistryStatus', $data['RegistryStatus_Code']);
			}

			if (empty($data['RegistryLLO_Num'])) {
                if (empty($data['RegistryLLO_id'])) {
                    //генерация номера реестра
                    $data['RegistryLLO_Num'] = $this->RegistryLLO_model->getObjectNextNum($this->RegistryLLO_model->schema.'.RegistryLLO', 'RegistryLLO_Num');
                } else {
                    unset($data['RegistryLLO_Num']);
                }
			}

            if (empty($data['RegistryLLO_accDate'])) {
                unset($data['RegistryLLO_accDate']);
            }

            if (!empty($data['RegistryLLO_id']) && empty($data['Org_id'])) {
                unset($data['Org_id']);
            }

			//это поле пока не используется
			$data['RegistryLLO_IsActive'] = $this->RegistryLLO_model->getObjectIdByCode('YesNo', 1); //1 - Да

			$response = $this->RegistryLLO_model->saveObject($this->RegistryLLO_model->schema.'.RegistryLLO', $data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении реестра рецептов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение ошибки
	 */
	function saveRegistryLLOError() {
		$data = $this->ProcessInputData('saveRegistryLLOError', true);
		if ($data){
            $save_params = array(
                'RegistryLLOError_id' => $data['RegistryLLOError_id'],
                'RegistryLLO_id' => $data['RegistryLLO_id'],
                'ReceptOtov_id' => $data['ReceptOtov_id'],
                'RegistryReceptErrorType_id' => $data['RegistryReceptErrorType_id'],
                'pmUser_id' => $data['pmUser_id']
            );
            $response = $this->RegistryLLO_model->saveObject($this->RegistryLLO_model->schema.'.RegistryLLOError', $save_params);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении реестра рецептов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка реестра рецептов
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$response = $this->RegistryLLO_model->load($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление реестра рецептов
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true);
		if ($data) {
			$response = $this->RegistryLLO_model->delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление реестра рецептов
	 */
	function deleteRegistryLLOError() {
		$data = $this->ProcessInputData('deleteRegistryLLOError', true);
		if ($data) {
            $response = $this->RegistryLLO_model->deleteObject($this->RegistryLLO_model->schema.'.RegistryLLOError', array(
                'RegistryLLOError_id' => $data['id']
            ));
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Удаление рецепта из реестра
     */
    function deleteRegistryDataRecept() {
        $data = $this->ProcessInputData('deleteRegistryDataRecept', true);
        if ($data) {
            $response = $this->RegistryLLO_model->deleteRegistryDataRecept($data);
            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', false);
		if ($data){
            if (is_array($data['RegistryLLO_Date_Range']) && count($data['RegistryLLO_Date_Range']) == 2) {
                $data['RegistryLLO_begDate'] = $data['RegistryLLO_Date_Range'][0];
                $data['RegistryLLO_endDate'] = $data['RegistryLLO_Date_Range'][1];
            }
			$response = $this->RegistryLLO_model->loadList($data);
			$this->ProcessModelMultiList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка рецептов
	 */
	function loadRegistryDataReceptList() {
		$data = $this->ProcessInputData('loadRegistryDataReceptList', false);
		if ($data){
			$response = $this->RegistryLLO_model->loadRegistryDataReceptList($data);
			$this->ProcessModelMultiList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка ошибка для рецептов
	 */
	function loadRegistryLLOErrorList() {
		$data = $this->ProcessInputData('loadRegistryLLOErrorList', false);
		if ($data){
			$response = $this->RegistryLLO_model->loadRegistryLLOErrorList($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для редактирования статуса экспертизы
	 */
	function loadRegistryLLOExpertiseForm() {
		$data = $this->ProcessInputData('loadRegistryLLOExpertiseForm', false);
		if ($data === false) return false;
		$response = $this->RegistryLLO_model->loadRegistryLLOExpertiseForm($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Ручное изменение статуса экспертизы
	 */
	function saveRegistryLLOExpertise() {
		$data = $this->ProcessInputData('saveRegistryLLOExpertise', true);
		if ($data === false) return false;
		$response = $this->RegistryLLO_model->saveRegistryLLOExpertise($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Формирование реестра рецептов
	 */
	function forming() {
		$data = $this->ProcessInputData('forming', true);
		if ($data){
			$response = $this->RegistryLLO_model->forming($data);
			$this->ProcessModelSave($response, true, 'Ошибка при формировании реестра рецептов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Экспертиза реестра рецептов
	 */
	function expertise() {
		$data = $this->ProcessInputData('expertise', true);
		if ($data){
			$response = $this->RegistryLLO_model->expertise($data);
			$this->ProcessModelSave($response, true, 'Ошибка при проведении экспертизы реестра рецептов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Пересчет сумм для реестра рецептов
	 */
	function recount() {
		$data = $this->ProcessInputData('recount', true);
		if ($data){
			$response = $this->RegistryLLO_model->recount($data);
			$this->ProcessModelSave($response, true, 'Ошибка при пересчете сумм реестра рецептов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Установка статуса для реестра
	 */
	function setRegistryStatus() {
		$data = $this->ProcessInputData('setRegistryStatus', true);
		if ($data){
			$response = $this->RegistryLLO_model->setRegistryStatus($data);
			$this->ProcessModelSave($response, true, 'Ошибка при установке статуса реестра рецептов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Установка статуса для рецепта
	 */
	function setReceptStatus() {
		$data = $this->ProcessInputData('setReceptStatus', true);
		if ($data){
			$response = $this->RegistryLLO_model->setReceptStatus($data);
			$this->ProcessModelSave($response, true, 'Ошибка при установке статуса рецепта')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Загрузка списка ЛПУ для комбобокса
     */
    function loadLpuCombo() {
        $data = $this->ProcessInputData('loadLpuCombo', true);
        if ($data){
            $response = $this->RegistryLLO_model->loadLpuCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
        return true;
    }

    /**
     * Загрузка списка статусов рецепта в реестре для комбобокса
     */
    function loadReceptStatusFLKMEKCombo() {
        $response = $this->RegistryLLO_model->loadReceptStatusFLKMEKCombo();
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Загрузка списка ошибок экспертизы для комбобокса
     */
    function loadRegistryReceptErrorTypeCombo() {
        $response = $this->RegistryLLO_model->loadRegistryReceptErrorTypeCombo();
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }
}
?>
