<?php

/**
 * Class ExemptVaccine  - медотвод / отказ от вакцинации
 *
 * @access       public
 * @copyright    Copyright (c) 2020
 * @author       Islamov AN
 * @version      21.04.2020
 */
class ExemptVaccine extends swController
{
	/**
	 * @var string $model
	 */
	protected $model = 'ExemptVaccine_model';

	/**
	 * ExemptVaccine constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model($this->model, "dbmodel");
	}

	/**
	 * Получение списка отводов пациента
	 *
	 * @return array
	 */
	public function getPersonVaccinationRefuseTypeList()
	{
		$response = $this->dbmodel->getPersonVaccinationRefuseTypeList(array());

		echo json_encode(array(
			'success' => true,
			'data' => $response
		));
		return true;
	}

	
}
