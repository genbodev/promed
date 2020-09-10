<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Annotation - контроллер для работы с примечаниями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Aleksandr Chebukin
 * @version			12.11.2015
 *
 * @property Annotation_model dbmodel
 */

class Annotation extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array(
				'field' => 'Annotation_id',
				'label' => 'Идентификатор примечания',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadList' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Resource_id',
				'label' => 'Идентификатор ресурса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AnnotationDateRange',
				'label' => 'Даты примечаний',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'Annotation_DateRange',
				'label' => 'Даты примечаний',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'AnnotationType_id',
				'label' => 'Тип примечания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AnnotationVison_id',
				'label' => 'Видимость примечания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Annotation_Comment',
				'label' => 'Текст примечания',
				'rules' => 'trim',
				'type' => 'string'
			),
		),
		'load' => array(
			array(
				'field' => 'Annotation_id',
				'label' => 'Идентификатор примечания',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'save' => array(
			array(
				'field' => 'Annotation_id',
				'label' => 'Идентификатор примечания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Resource_id',
				'label' => 'Идентификатор ресурса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AnnotationType_id',
				'label' => 'Тип примечания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AnnotationVison_id',
				'label' => 'Видимость примечания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Annotation_Comment',
				'label' => 'Текст примечания',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Annotation_begDate',
				'label' => 'Период действия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Annotation_endDate',
				'label' => 'Период действия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Annotation_begTime',
				'label' => 'Время действия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Annotation_endTime',
				'label' => 'Время действия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ignore_doubles',
				'label' => 'Игнорировать дубли',
				'rules' => '',
				'type' => 'id'
			),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('Annotation_model', 'dbmodel');
	}

	/**
	 * Удаление примечания
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении примечания')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список примечаний
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает примечание
	 */
	function load()
	{
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение примечания
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		if ( !empty($data['Annotation_begDate']) && !empty($data['Annotation_endDate']) && $data['Annotation_begDate'] > $data['Annotation_endDate'] ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Дата начала не может быть больше даты окончания',
				'Error_Code' => 146,
				'success' => false
			));
			return false;
		}

		if ( !empty($data['Annotation_begTime']) && !empty($data['Annotation_endTime']) && $data['Annotation_begTime'] > $data['Annotation_endTime'] ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Время начала не может быть больше времени окончания',
				'Error_Code' => 146,
				'success' => false
			));
			return false;
		}		
		
		if ( true !== $this->dbmodel->checkMsfIsWork($data) ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Создание примечания невозможно: дата начала/окончания действия примечания не входит в период работы сотрудника',
				'Error_Code' => 146,
				'success' => false
			));
			return false;
		}

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}