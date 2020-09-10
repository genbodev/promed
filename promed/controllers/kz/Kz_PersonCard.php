<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonCard - контроллер для выполенния операций с картотекой пациентов.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version      01.06.2009
 *
 * @property Kz_Polka_PersonCard_model $pcmodel
 */
require_once(APPPATH.'controllers/PersonCard.php');

class Kz_PersonCard extends PersonCard {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules['loadPersonCardAttachGrid'] = array(
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type'  => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'trim',
				'type'  => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type'  => 'string'
			),
			array(
				'field' => 'Person_BirthDay_Range',
				'label' => 'Период даты рождения',
				'rules' => '',
				'type'  => 'daterange'
			),
			array(
				'field' => 'PersonCardAttach_setDate_Range',
				'label' => 'Период подачи заявления',
				'rules' => '',
				'type'  => 'daterange'
			),
			array(
				'field' => 'Lpu_aid',
				'label' => 'МО, принявшая заявление',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'PersonCardAttachStatusType_id',
				'label' => 'Статус заявления',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'GetAttachment_Number',
				'label' => 'Номер запроса (РПН)',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'GetAttachmentCase_id',
				'label' => 'Причина прикрепления',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Старт',
				'rules' => '',
				'type' => 'int'
			)
		);
		$this->inputRules['savePersonCardAttachRPN'] = array(
			array(
				'field' => 'PersonCardAttach_id',
				'label' => 'Идентификатор завявления',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'Lpu_aid',
				'label' => 'Идентификатор МО, привнявшая заявление',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'PersonCardAttach_setDate',
				'label' => 'Дата завявления',
				'rules' => 'required',
				'type'  => 'date'
			),
			array(
				'field' => 'GetAttachment_Number',
				'label' => 'Номер заявления (РПН)',
				'rules' => '',
				'type'  => 'int'
			),
			array(
				'field' => 'LpuRegionType_id',
				'label' => 'Идентификатор типа участка',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'LpuRegion_id',
				'label' => 'Идентификатор участка',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'GetAttachmentCase_id',
				'label' => 'Идентификатор причины',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'GetAttachment_IsCareHome',
				'label' => 'Флаг гарантированного обслуживания на дому',
				'rules' => '',
				'type'  => 'checkbox'
			),
			array(
				'field' => 'files',
				'label' => 'Файлы',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'ignorePersonCardExists',
				'label' => 'Пропустить проверку на прикрепление',
				'rules' => '',
				'type'  => 'int'
			),
		);
		$this->inputRules['loadPersonCardAttachForm'] = array(
			array(
				'field' => 'PersonCardAttach_id',
				'label' => 'Идентификатор заявления',
				'rules' => 'required',
				'type'  => 'id'
			),
		);
		$this->inputRules['sendPersonCardAttachToRPN'] = array(
			array(
				'field' => 'PersonCardAttach_id',
				'label' => 'Идентификатор заявления',
				'rules' => 'required',
				'type'  => 'id'
			),
		);
		$this->inputRules['deletePersonCardAttachRPN'] = array(
			array(
				'field' => 'PersonCardAttach_id',
				'label' => 'Идентификатор заявления',
				'rules' => 'required',
				'type'  => 'id'
			),
		);
		$this->inputRules['getPersonCardAttachStatusFromRPN'] = array(
			array(
				'field' => 'PersonCardAttach_id',
				'label' => 'Идентификатор заявления',
				'rules' => 'required',
				'type'  => 'id'
			),
		);
	}

	/**
	 * Сохранение заявления на прикрепление
	 */
	function savePersonCardAttachRPN() {
		$data = $this->ProcessInputData('savePersonCardAttachRPN', true);
		if ($data === false) { return false; }

		$response = $this->pcmodel->savePersonCardAttachRPN($data);

		$this->load->model("PMMediaData_model", "mdmodel");
		if( empty($data['files']) && empty($res[0]['Error_Msg'])){
			$par_MediaData = array(
				'ObjectID' => $data['PersonCardAttach_id'],
				'pmMediaData_ObjectName' => 'PersonCardAttach'
			);
			$res_cur_MediaData = $this->mdmodel->getpmMediaData($par_MediaData);
			foreach ($res_cur_MediaData as $md)
			{
				$res_del = $this->mdmodel->deletepmMediaData(array('pmMediaData_id' => $md['pmMediaData_id']));
			}
		}
		if( !empty($data['files']) && empty($response[0]['Error_Msg']) ) {
			$files = explode("|", $data['files']);
			$rootDir = IMPORTPATH_ROOT . "personcardattaches/";
			$folderName = $rootDir . $response[0]['PersonCardAttach_id'] . "/";
			if( !is_dir($folderName) ) {
				if( !mkdir($folderName) ) {
					DieWithError("Ошибка! Не удалось создать папку для хранения файлов заявления!");
					return false;
				}
			}

			if(!empty($data['PersonCardAttach_id'])){
				$file_names = array();
				foreach($files as $file){
					$f = explode("::",$file);
					$file_names[] = $f[0];
				}
				$par_MediaData = array(
					'ObjectID' => $data['PersonCardAttach_id'],
					'pmMediaData_ObjectName' => 'PersonCardAttach'
				);
				$res_cur_MediaData = $this->mdmodel->getpmMediaData($par_MediaData);
				foreach ($res_cur_MediaData as $md)
				{
					if(!in_array($md['pmMediaData_FileName'],$file_names))
					{
						$res_del = $this->mdmodel->deletepmMediaData(array('pmMediaData_id' => $md['pmMediaData_id']));
					}
				}
			}

			foreach($files as $file) {
				$f = explode("::", $file);
				$file_name = $folderName . $f[0];
				$file_tmp_name = $f[1];
				if( is_file($file_tmp_name) ) {
					if( !@rename( $file_tmp_name, iconv("UTF-8", "cp1251",$file_name) ) ) {
						DieWithError("Ошибка! Не удалось сохранить файл заявления!");
						return false;
					}
					$rsp = $this->mdmodel->savepmMediaData(array(
						'pmMediaData_id' => isset($data['pmMediaData_id']) ? $data['pmMediaData_id'] : null,
						'ObjectName' => 'PersonCardAttach',
						'ObjectID' => $response[0]['PersonCardAttach_id'],
						'orig_name' => $f[0],
						'file_name' => $file_name,
						'description' => 'filesize = ' . filesize(iconv("UTF-8", "cp1251",$file_name)),
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}

		$Alert_Msg = $this->pcmodel->getAlertMsg();
		if (!empty($Alert_Msg)) {
			$response[0]['Alert_Msg'] = $Alert_Msg;
		}

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление заявления на прикрепление
	 */
	function deletePersonCardAttachRPN() {
		$data = $this->ProcessInputData('deletePersonCardAttachRPN', true);
		if ($data === false) { return false; }

		$response = $this->pcmodel->deletePersonCardAttachRPN($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для редактирования заявления на прикрепление
	 */
	function loadPersonCardAttachForm() {
		$data = $this->ProcessInputData('loadPersonCardAttachForm', true);
		if ($data === false) { return false; }

		$response = $this->pcmodel->loadPersonCardAttachForm($data);

		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Отправка заявления в сервис РПН
	 */
	function sendPersonCardAttachToRPN() {
		$data = $this->ProcessInputData('sendPersonCardAttachToRPN', true);
		if ($data === false) { return false; }

		$response = $this->pcmodel->sendPersonCardAttachToRPN($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение статуса заявления из сервиса РПН
	 */
	function getPersonCardAttachStatusFromRPN() {
		$data = $this->ProcessInputData('getPersonCardAttachStatusFromRPN', true);
		if ($data === false) { return false; }

		$response = $this->pcmodel->getPersonCardAttachStatusFromRPN($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}
