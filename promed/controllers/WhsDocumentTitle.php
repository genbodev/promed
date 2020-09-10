<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Контроллер для объектов Правоустанавливающие документы
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       ModelGenerator
* @version
* @property WhsDocumentTitle_model WhsDocumentTitle_model
*/

class WhsDocumentTitle extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'WhsDocumentTitle_id',
					'label' => 'WhsDocumentTitle_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentTitle_Name',
					'label' => 'Наименование документа',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentTitleType_id',
					'label' => 'Тип правоустанавливающего документа',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentStatusType_id',
					'label' => 'Статус документа',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentTitle_begDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'WhsDocumentTitle_endDate',
					'label' => 'Дата окончания дийствия',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'RightRecipientJSON',
					'label' => 'Строка правополучатели',
					'default' => '',
					'rules' => ''/*'required'*/,
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'Родительский ГК',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveWhsDocumentTitleTariff' => array(
				array('field' => 'WhsDocumentTitleTariff_id', 'label' => 'Идентификатор связи', 'rules' => '', 'type' => 'id'),
				array('field' => 'WhsDocumentTitle_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UslugaComplexTariff_id', 'label' => 'Идентификатор тарифа', 'rules' => 'required', 'type' => 'id')
			),
			'load' => array(
				array(
					'field' => 'WhsDocumentTitle_id',
					'label' => 'WhsDocumentTitle_id',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'WhsDocumentTitle_id',
					'label' => 'WhsDocumentTitle_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentSupply_id',
					'label' => 'WhsDocumentSupply_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentTitle_Name',
					'label' => 'Наименование документа',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'WhsDocumentTitleType_id',
					'label' => 'Тип правоустанавливающего документа',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentStatusType_id',
					'label' => 'Статус документа',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WhsDocumentTitle_begDate',
					'label' => 'Дата начала действия',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'WhsDocumentTitle_endDate',
					'label' => 'Дата окончания дийствия',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				)
			),
			'delete' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор документа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'deleteWhsDocumentTitleTariff' => array(
				array('field' => 'id', 'label' => 'Идентификатор связи', 'rules' => 'required', 'type' => 'int')
			),
			'execute' => array(
				array(
					'field' => 'WhsDocumentTitle_id',
					'label' => 'WhsDocumentTitle_id',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'copy' => array(
				array(
					'field' => 'WhsDocumentTitle_id',
					'label' => 'WhsDocumentTitle_id',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadWhsDocumentSupplyList' => array(
				array(
					'field' => 'WhsDocumentUc_id',
					'label' => 'WhsDocumentUc_id',
					'rules' => '',
					'type' => 'int'
				)
			)
		 );
		$this->load->database();
		$this->load->model('WhsDocumentTitle_model', 'WhsDocumentTitle_model');
		$this->load->model('WhsDocumentRightRecipient_model', 'WhsDocumentRightRecipient_model');
		$this->load->model('PMMediaData_model', 'PMMediaData_model');
	}


	/**
	 * Сохранение документа
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data){
			if (isset($data['WhsDocumentTitle_id'])) {
				$this->WhsDocumentTitle_model->setWhsDocumentTitle_id($data['WhsDocumentTitle_id']);
			}
			if (isset($data['WhsDocumentTitle_Name'])) {
				$this->WhsDocumentTitle_model->setWhsDocumentTitle_Name($data['WhsDocumentTitle_Name']);
			}
			if (isset($data['WhsDocumentTitleType_id'])) {
				$this->WhsDocumentTitle_model->setWhsDocumentTitleType_id($data['WhsDocumentTitleType_id']);
			}
			if (isset($data['WhsDocumentStatusType_id'])) {
				$this->WhsDocumentTitle_model->setWhsDocumentStatusType_id($data['WhsDocumentStatusType_id']);
			}
			if (isset($data['WhsDocumentTitle_begDate'])) {
				$this->WhsDocumentTitle_model->setWhsDocumentTitle_begDate($data['WhsDocumentTitle_begDate']);
			}
			if (isset($data['WhsDocumentTitle_endDate'])) {
				$this->WhsDocumentTitle_model->setWhsDocumentTitle_endDate($data['WhsDocumentTitle_endDate']);
			}
			if (isset($data['WhsDocumentUc_id'])) {
				$this->WhsDocumentTitle_model->setWhsDocumentUc_id($data['WhsDocumentUc_id']);
			}
			$response = $this->WhsDocumentTitle_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении правоустанавливающего документа')->ReturnData();
			
			if (isset($this->OutData['WhsDocumentTitle_id']) && $this->OutData['WhsDocumentTitle_id'] > 0) {				
				$response = $this->WhsDocumentRightRecipient_model->saveFromJSON(array(
					'WhsDocumentTitle_id' => $this->OutData['WhsDocumentTitle_id'],
					'json_str' => $data['RightRecipientJSON'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Сохранение связи между документом и тарифом
	 */
	function saveWhsDocumentTitleTariff() {
		$data = $this->ProcessInputData('saveWhsDocumentTitleTariff', true);
		if ($data){
			$response = $this->WhsDocumentTitle_model->saveWhsDocumentTitleTariff($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
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
			$this->WhsDocumentTitle_model->setWhsDocumentTitle_id($data['WhsDocumentTitle_id']);
			$response = $this->WhsDocumentTitle_model->load();
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
			$response = $this->WhsDocumentTitle_model->loadList($filter);
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
			$id = $data['id'];
			
			//удаление правополучателей
			$item_list = $this->WhsDocumentRightRecipient_model->loadList(array('WhsDocumentTitle_id' => $id));
			foreach($item_list as $item) {
				if ($item['WhsDocumentRightRecipient_id'] > 0) {
					$this->WhsDocumentRightRecipient_model->setWhsDocumentRightRecipient_id($item['WhsDocumentRightRecipient_id']);
					$response = $this->WhsDocumentRightRecipient_model->Delete();
				}
			}
			
			//удаление файлов
			$item_list = $this->PMMediaData_model->loadpmMediaDataListGrid(array('ObjectName' => 'WhsDocumentTitle', 'ObjectID' => $id)); 
			foreach($item_list as $item) {
				$this->PMMediaData_model->deletepmMediaData(array(
					'pmMediaData_id' => $item['pmMediaData_id']
				));
				
				$path = './'.IMPORTPATH_ROOT.$item['pmMediaData_FilePath'];
				if (is_file($path))
					unlink($path);
			}
			
			//удаление документа
			$this->WhsDocumentTitle_model->setWhsDocumentTitle_id($id);
			$response = $this->WhsDocumentTitle_model->Delete();
			
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Удаление связи между документом и тарифом
	 */
	function deleteWhsDocumentTitleTariff() {
		$data = $this->ProcessInputData('deleteWhsDocumentTitleTariff', true, true);
		if ($data) {
			$response = $this->WhsDocumentTitle_model->deleteWhsDocumentTitleTariff($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Исполнение документа
	 */
	function execute() {
		$data = $this->ProcessInputData('execute', true, true);
		if ($data) {
			$this->WhsDocumentTitle_model->setWhsDocumentTitle_id($data['WhsDocumentTitle_id']);
			$doc_data = $this->WhsDocumentTitle_model->load();

			$err = $this->WhsDocumentTitle_model->executeCheck($doc_data[0]);

			if (empty($err)) {
				$item_list = $this->WhsDocumentRightRecipient_model->loadList(array('WhsDocumentTitle_id' => $data['WhsDocumentTitle_id']));
				foreach($item_list as $item) {
					if ($item['WhsDocumentRightRecipient_id'] > 0) {
						$contragent_type_id = $this->WhsDocumentTitle_model->getWhsDocumentTitleType_id() == 2 ? 6 : 3;
						$params = array(
							'WhsDocumentRightRecipient_id' => $item['WhsDocumentRightRecipient_id'],
							'ContragentType_id' => $contragent_type_id,
							'Server_id' => $data['Server_id']//нужно ли оно?
						);
						//Для приложений к ГК, проставляем контрагенты текущему пользователю
						//$params['Lpu_id'] = $doc_data[0]['WhsDocumentUc_id'] > 0 ? $data['Lpu_id'] : null;
						$params['Lpu_id'] = $data['Lpu_id'] > 0 ? $data['Lpu_id'] : null;
						$this->WhsDocumentRightRecipient_model->setContragent($params);
					}
				}

				$this->WhsDocumentTitle_model->setWhsDocumentStatusType_id(2);
				$response = $this->WhsDocumentTitle_model->save();
			} else {
				$response = array('Error_Msg' => $err);
			}
			$this->ProcessModelSave($response, true, $response)->ReturnData();

			return true;
		} else {
			return false;
		}
	}


	/**
	 * Полное копирование документа (включая приложенные файлы), на выходе идентификатор копии
	 */
	function copy() {
		$data = $this->ProcessInputData('copy', true, true);
		if ($data) {
			$old_id = $data['WhsDocumentTitle_id'];
			$this->WhsDocumentTitle_model->setWhsDocumentTitle_id($old_id);
			$doc_data = $this->WhsDocumentTitle_model->load();
			
			//копируем документ
			$this->WhsDocumentTitle_model->setWhsDocumentTitle_id(0);
			$this->WhsDocumentTitle_model->setWhsDocumentStatusType_id(1);
			$response = $this->WhsDocumentTitle_model->save();
			
			if ($response[0] && $response[0]['WhsDocumentTitle_id'] > 0) {
				$new_id = $response[0]['WhsDocumentTitle_id'];
				
				//копируем правополучателей
				$item_list = $this->WhsDocumentRightRecipient_model->loadList(array('WhsDocumentTitle_id' => $old_id));
				foreach($item_list as $item) {
					if ($item['WhsDocumentRightRecipient_id'] > 0) {
						$this->WhsDocumentRightRecipient_model->setWhsDocumentRightRecipient_id(0);
						$this->WhsDocumentRightRecipient_model->setWhsDocumentTitle_id($new_id);
						$this->WhsDocumentRightRecipient_model->setOrg_id($item['Org_id']);
						$this->WhsDocumentRightRecipient_model->setContragent_id($item['Contragent_id']);
						$this->WhsDocumentRightRecipient_model->setWhsDocumentRightRecipient_begDate(ConvertDateFormat($item['WhsDocumentRightRecipient_begDate']));
						$this->WhsDocumentRightRecipient_model->setWhsDocumentRightRecipient_endDate(ConvertDateFormat($item['WhsDocumentRightRecipient_endDate']));
						$res = $this->WhsDocumentRightRecipient_model->save();
					}
				}
				
				//копируем приложенные файлы
				$item_list = $this->PMMediaData_model->loadpmMediaDataListGrid(array('ObjectName' => 'WhsDocumentTitle', 'ObjectID' => $old_id)); 
				foreach($item_list as $item) {
					$path = './'.IMPORTPATH_ROOT.$item['pmMediaData_FilePath'];
					$tmp = explode('.', $item['pmMediaData_FileName']);
					$new_filename = md5($item['pmMediaData_FileName'].time()).'.'.end($tmp);
					$new_path = './'.IMPORTPATH_ROOT.$new_filename;
					if (is_file($path)) {
						copy($path, $new_path);
						if (is_file($new_path)) {
							$this->PMMediaData_model->savepmMediaData(array(
								'ObjectName' => 'WhsDocumentTitle',
								'ObjectID' => $new_id,
								'orig_name' => $item['pmMediaData_FileName'],
								'file_name' => $new_filename,
								'description' => $item['pmMediaData_Comment'],
								'pmUser_id' => $this->WhsDocumentTitle_model->getpmUser_id()
							));
						}
					}
				}
			}
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении правоустанавливающего документа')->ReturnData();	
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Получение списка гос. контрактов - потенциальных "родителей" для пр-уст. документов
	 */
	function loadWhsDocumentSupplyList() {
		$data = $this->ProcessInputData('loadWhsDocumentSupplyList', true);
		if ($data) {
			$filter = $data;
			$response = $this->WhsDocumentTitle_model->loadWhsDocumentSupplyList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}