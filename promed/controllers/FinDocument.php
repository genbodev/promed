<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов "Информация о счетах и платежных поручениях"
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       ModelGenerator
 * @version
 * @property FinDocument_model FinDocument_model
 */

class FinDocument extends swController
{
    /**
     * Конструктор
     */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'FinDocument_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RegistryLLO_id',
					'label' => 'Ссылка на реестр рецептов',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'FinDocumentType_id',
					'label' => 'Тип фин.документа',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'FinDocument_Number',
					'label' => '№ счета',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'FinDocument_Date',
					'label' => 'Дата счета',
					'rules' => '',
					'type' => 'date'
				),
                array(
                    'field' => 'UslugaComplex_id',
                    'label' => 'Услуга',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_mid',
                    'label' => 'Плательщик',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
					'field' => 'FinDocument_Sum',
					'label' => 'Сумма счета',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DocListJSON',
					'label' => 'Список платежных поручений',
					'rules' => '',
					'type' => 'string'
				)
			),
			'load' => array(
				array(
					'field' => 'FinDocument_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'FinDocument_id',
					'label' => 'идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_id',
					'label' => 'Ссылка на сводный реестр рецептов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'FinDocumentType_id',
					'label' => 'Тип фин.документа',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'FinDocument_Number',
					'label' => '№ счета',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'FinDocument_Date',
					'label' => 'Дата счета',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'FinDocument_Sum',
					'label' => 'Сумма счета',
					'rules' => '',
					'type' => 'float'
				)
			),
			'delete' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
            'loadOrgMidCombo' => array(
                array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string')
            )
		);
		$this->load->database();
		$this->load->model('FinDocument_model', 'FinDocument_model');
	}

    /**
     * Сохранение
     */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){

            //поиск существующего счета
            $doc_id = $this->FinDocument_model->getIdByRegistryLLO($data['RegistryLLO_id']);

            if ($doc_id > 0) {
                $data['FinDocument_id'] = $doc_id;
            }

			$response = $this->FinDocument_model->saveObject($this->FinDocument_model->schema.'.FinDocument', $data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Информация о счетах и платежных поручениях');

            $findocument_id = !empty($response['FinDocument_id']) ? $response['FinDocument_id'] : null;
            if (empty($findocument_id)) {
                $this->ReturnError('Ошибка при сохранении документа.');
                return true;
            }

            //сохранение связи между реестром и счетом
            if (empty($doc_id)) {
                $response = $this->FinDocument_model->saveObject($this->FinDocument_model->schema.'.RegistryLLOFinDocument', array(
                    'FinDocument_id' => $findocument_id,
                    'RegistryLLO_id' => $data['RegistryLLO_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));
            }

			if (isset($data['RegistryLLO_id']) && $data['RegistryLLO_id'] > 0) {
				//сохранение списка платежных поручений
				$response = $this->FinDocument_model->saveFinDocumentSpecFromJSON(array(
                    'RegistryLLO_id' => $data['RegistryLLO_id'],
					'json_str' => $data['DocListJSON'],
                    'pmUser_id' => $data['pmUser_id']
				));
			}

			// проверяем выполненеа ли оплата по реестру в полной мере, в зависимости от результатов проверки обновляем статус реестра
			$response = $this->FinDocument_model->setRegistryLLOAutoStatus(array(
                'FinDocument_id' => $findocument_id,
                'pmUser_id' => $data['pmUser_id']
            ));
			if (is_array($response)) {
				array_walk($response, 'ConvertFromWin1251ToUTF8');
				$this->ReturnData(array_merge($this->GetOutData(), $response));
			} else {
				$this->ReturnData();
			}
			return true;
		} else {
			return false;
		}
	}

    /**
     * Загрузка
     */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$response = $this->FinDocument_model->load($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Загрузка списка
     */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->FinDocument_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Удаление
     */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$response = $this->FinDocument_model->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Получение сгенерированного номера для платежного документа
     */
    function generateFinDocumentNumber() {
        $num = $this->FinDocument_model->getObjectNextNum($this->FinDocument_model->schema.'.FinDocument', 'FinDocument_Number');
        if (!empty($num)) {
            $response = array(
                'FinDocument_Number' => $num,
                'success' => true
            );
            $this->ProcessModelSave($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка организаций для комбобокса
     */
    function loadOrgMidCombo() {
        $data = $this->ProcessInputData('loadOrgMidCombo', true);
        if ($data){
            $response = $this->FinDocument_model->loadOrgMidCombo($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }
}