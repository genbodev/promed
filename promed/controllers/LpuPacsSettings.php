<?php	defined('BASEPATH') or die ('No direct script access allowed'); /**
 * LpuOrgServed - контроллер для работы с обслуживаемыми организациями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Stac
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author	Sergey Tokarev
 * @version			22.02.2013
 * @property LpuPacsSettings_model dbmodel
 */

class LpuPacsSettings extends swController{
	public $inputRules = array(
		'getCurrentPacsSettings' => array(
			array(
				'field' => 'LpuSection_id',
				'label' => 'ID_ЛПУ',
				'rules' => '',
				'type'  => 'id'
				)
		),
		'saveLpuPacsData' => array(
			array(
				'field' => 'LpuPacs_aetitle',
				'label' => 'Заголовок',
				'rules' => '',
				'type'  => 'string'
				),
			array(
				'field' => 'LpuPacs_desc',
				'label' => 'Описание',
				'rules' => '',
				'type'  => 'string'
				),
			array(
				'field' => 'LpuPacs_port',
				'label' => 'Порт',
				'rules' => '',
				'type'  => 'string'
				),
			array(
				'field' => 'LpuPacs_ip',
				'label' => 'IP',
				'rules' => '',
				'type'  => 'string'
				),
            array(
				'field' => 'LpuSection_id',
				'label' => 'ID отделения',
				'rules' => '',
				'type'  => 'string'
				),
			array(
				'field' => 'LpuPacs_id',
				'label' => 'IDPacs',
				'rules' => '',
				'type'  => 'string'
				),
			array(
				'field' => 'LpuPacs_wadoPort',
				'label' => 'PacsWADOport',
				'rules' => '',
				'type'  => 'string'
				)
		),
		'deleteLpuPacsData' => array(
			array(
				'field' => 'LpuPacs_id',
				'label' => 'ID_Pacs',
				'rules' => '',
				'type'  => 'id'
				)
		)       
             
	);

	/**
	 * LpuPacsSettings constructor.
	 */
	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->model('LpuPacsSettings_model','dbmodel');
	}

	/**
	 * Получение информации о настройках ПАКС
	 */
	function getCurrentPacsSettings()
	{
		$data  = $this->ProcessInputData('getCurrentPacsSettings',true);
		if ($data === false)
		{
			return false;
		}
		$response = $this->dbmodel->getCurrentPacsSettings($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
	}

	/**
	 * Сохранение настроек ПАКС:
	 */
	function saveLpuPacsData(){
		$data = $this->ProcessInputData('saveLpuPacsData',true);
		if ($data === false)
		{
			return false;
		}
		
		$response = $this->dbmodel->saveLpuPacsData($data);
		$outdata = $this->ProcessModelSave($response, true, 'При сохранении настроек ПАКС произошла ошибка!')->GetOutData();
		return true;
	}

	/**
	 * Удаление настроек ПАКС:
	 */
	function deleteLpuPacsData(){
		$data = $this->ProcessInputData('deleteLpuPacsData',true);
		if ($data === false)
		{
			return false;
		}
		$response = $this->dbmodel->deleteLpuPacsData($data);
		$outdata = $this->ProcessModelSave($response,true,'При удалении ПАКС произошла ошибка')->GetInData();
		/*if(!$outdata['success']){
			$this->ReturnData($outdata);
			return false;
		}*/
		return true;
	}

}