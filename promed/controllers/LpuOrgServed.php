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
 * @author	Sergey Khorev (sergey.khorev@yandex.ru)
 * @version			30.05.2012
 * @property LpuOrgServed_model dbmodel
 */

class LpuOrgServed extends swController{
	public $inputRules = array(
		'getLpuOrgServed' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'isClose',
				'label' => 'Флаг закрытия',
				'rules' => '',
				'type'  => 'int'
			)
		),
		'getCurrentLpuOrgServed' => array(
			array(
				'field' => 'LpuOrgServed_id',
				'label' => 'LpuOrgServed_id',
				'rules' => '',
				'type'  => 'id'
			)
		),
		'saveLpuOrgServed' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'LpuOrgServed_id',
				'label' => 'LpuOrgServed_id',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'LpuOrgServed_begDate',
				'label' => 'Дата начала',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'LpuOrgServed_endDate',
				'label' => 'Дата окончания',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'LpuOrgServiceType_id',
				'label' => 'Тип обслуживания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Кто добавил/обновил',
				'rules' => '',
				'type'  => 'id'
			)
			/*array(
				'field' => 'pmUser_updID',
				'label' => 'Кто обновил',
				'rules' => '',
				'type'  => 'id'
			),*/
			/*array(
				'field' => 'LpuOrgServed_insDate',
				'label' => 'Дата добавления',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'LpuOrgServed_updDate',
				'label' => 'Дата обновления',
				'rules' => '',
				'type'  => 'string'
			)*/

		),
		'deleteLpuOrgServed' => array(
			array(
				'field' => 'LpuOrgServed_id',
				'label' => 'LpuOrgServed_id',
				'rules' => '',
				'type'  => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->model('LpuOrgServed_model','dbmodel');
	}

	/**
	 * Получение списка обслуживаемых организаций в ЛПУ:
	 */
	function getLpuOrgServed(){
		$data = $this->ProcessInputData('getLpuOrgServed',true);
		if ($data === false)
		{
			return false;
		}
		$response = $this->dbmodel->getLpuOrgServed($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение информации о выбранной обслуживаемой организации
	 */
	function getCurrentLpuOrgServed()
	{
		$data  = $this->ProcessInputData('getCurrentLpuOrgServed',true);
		if ($data === false)
		{
			return false;
		}
		$response = $this->dbmodel->getCurrentLpuOrgServed($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
	}

	/**
	 * Сохранение обслуживаемой организации в ЛПУ:
	 */
	function saveLpuOrgServed(){
		$data = $this->ProcessInputData('saveLpuOrgServed',true);
		if ($data === false)
		{
			return false;
		}
		$this->load->model('MedService_model','msmodel');
		
		if (!empty($data['LpuOrgServiceType_id']) && $data['LpuOrgServiceType_id'] == 2) {
			$data['MedServiceType_id'] = 18;
			if (!$this->msmodel->checkMedServiceExistInLpu($data)) {
				$this->ReturnError('В ЛПУ отсутсвует служба ППД');
				return false;
			}
		}
		
		$response = $this->dbmodel->saveLpuOrgServed($data);
		$outdata = $this->ProcessModelSave($response, true, 'При сохранении сторонней организации произошла ошибка!')->GetOutData();
		$this->ReturnData($outdata);
		return true;
	}

	/**
	 * Удаление обслуживаемой организации в ЛПУ:
	 */
	function deleteLpuOrgServed(){
		$data = $this->ProcessInputData('deleteLpuOrgServed',true);
		if ($data === false)
		{
			return false;
		}
		$response = $this->dbmodel->deleteLpuOrgServed($data);
		$outdata = $this->ProcessModelSave($response,true,'При удалении сторонней организации произошла ошибка')->GetInData();
		if(!$outdata['success']){
			$this->ReturnData($outdata);
			return false;
		}
		return true;
	}
}