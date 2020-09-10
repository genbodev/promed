<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Newslatter - контроллер для работы с рассылками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Aleksandr Chebukin
 * @version			18.12.2015
 *
 * @property Newslatter_model dbmodel
 */

class Newslatter extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array(
				'field' => 'Newslatter_ids',
				'label' => 'Идентификаторы рассылок',
				'rules' => 'required',
				'type' => 'string'
			),
		),
		'cancel' => array(
			array(
				'field' => 'Newslatter_ids',
				'label' => 'Идентификаторы рассылок',
				'rules' => 'required',
				'type' => 'string'
			),
		),
		'activate' => array(
			array(
				'field' => 'Newslatter_ids',
				'label' => 'Идентификаторы рассылок',
				'rules' => 'required',
				'type' => 'string'
			),
		),
		'loadList' => array(
			array(
				'field' => 'Newslatter_insDT',
				'label' => 'Дата создания',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'Newslatter_Date',
				'label' => 'Период рассылки',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'NewslatterType_id',
				'label' => 'Тип рассылки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Newslatter_IsActive',
				'label' => 'Активность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Fio',
				'label' => 'ФИО',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Newslatter_Text',
				'label' => 'Текст сообщения',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'start',
				'label' => '',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'limit',
				'label' => '',
				'rules' => 'required',
				'type' => 'int'
			),
		),
		'load' => array(
			array(
				'field' => 'Newslatter_id',
				'label' => 'Идентификатор рассылки',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadPersonNewslatterList' => array(
			array(
				'field' => 'Newslatter_id',
				'label' => 'Идентификатор рассылки',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'save' => array(
			array(
				'field' => 'Newslatter_id',
				'label' => 'Идентификатор рассылки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'МО',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Newslatter_IsActive',
				'label' => 'Активная',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Newslatter_IsSMS',
				'label' => 'СМС рассылка',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Newslatter_IsEmail',
				'label' => 'E-mail рассылка',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Newslatter_begDate',
				'label' => 'Период действия',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Newslatter_endDate',
				'label' => 'Период действия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Newslatter_begTime',
				'label' => 'Время рассылки',
				'rules' => 'required',
				'type' => 'time'
			),
			array(
				'field' => 'PersonNewslatterData',
				'label' => 'Пациенты',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Newslatter_Text',
				'label' => 'Текст рассылки',
				'rules' => 'required|trim',
				'type' => 'string'
			),
			array(
				'field' => 'NewslatterType_id',
				'label' => 'Тип рассылки',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionNewslatterData',
				'label' => 'Список отделений',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuRegionNewslatterData',
				'label' => 'Список участков',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'NewslatterGroupType_id',
				'label' => 'Тип  группировки',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadLpuSectionNewslatterList' => array(
			array(
				'field' => 'Newslatter_id',
				'label' => 'Идентификатор рассылки',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadLpuRegionNewslatterList' => array(
			array(
				'field' => 'Newslatter_id',
				'label' => 'Идентификатор рассылки',
				'rules' => 'required',
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
		$this->load->model('Newslatter_model', 'dbmodel');
	}

	/**
	 * Удаление рассылки
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении рассылок')->ReturnData();
		return true;
	}

	/**
	 * Отмена рассылки
	 */
	function cancel()
	{
		$data = $this->ProcessInputData('cancel');
		if ($data === false) { return false; }

		$response = $this->dbmodel->cancel($data);
		$this->ProcessModelSave($response, true, 'Ошибка при отмене рассылок')->ReturnData();
		return true;
	}

	/**
	 * Активация рассылки
	 */
	function activate()
	{
		$data = $this->ProcessInputData('activate');
		if ($data === false) { return false; }

		$response = $this->dbmodel->activate($data);
		$this->ProcessModelSave($response, true, 'Ошибка при активации рассылок')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список рассылок
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает рассылку
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
	 * Возвращает список пациентов рассылки
	 */
	function loadPersonNewslatterList()
	{
		$data = $this->ProcessInputData('loadPersonNewslatterList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonNewslatterList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение рассылки
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		if ( empty($data['Newslatter_IsEmail']) && empty($data['Newslatter_IsSMS']) ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Один из флагов (СМС или E-mail) должен быть обязательно выбран',
				'success' => false
			));
			return false;
		}

		if ( !empty($data['Newslatter_begDate']) && !empty($data['Newslatter_endDate']) && $data['Newslatter_begDate'] > $data['Newslatter_endDate'] ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Дата начала не может быть больше даты окончания',
				'success' => false
			));
			return false;
		}

		if (isset($data['Newslatter_IsSMS']) && $data['Newslatter_IsSMS'] > 0) {
			$textLimit = preg_match("#[А-Яа-яЁё]#uis", $data['Newslatter_Text']) ? 140 : 320;
			if ( mb_strlen($data['Newslatter_Text']) > $textLimit ) {
				$this->ReturnData(array(
					'Error_Msg' => "Длина текста СМС не может быть больше {$textLimit} символов",
					'success' => false
				));
				return false;
			}
		}

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список отделений рассылки
	 */
	function loadLpuSectionNewslatterList()
	{
		$data = $this->ProcessInputData('loadLpuSectionNewslatterList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuSectionNewslatterList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Возвращает список участков рассылки
	 */
	function loadLpuRegionNewslatterList()
	{
		$data = $this->ProcessInputData('loadLpuRegionNewslatterList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuRegionNewslatterList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}