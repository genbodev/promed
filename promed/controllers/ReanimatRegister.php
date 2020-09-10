<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * ReanimatRegister - контроллер для работы с регистром пациентов в реанимации
 *
 * @author Muskat Boris 
 * @version			18.10.2017
 */

//СПРАВОЧНИКИ
//RRW_NSI() - формирование справочников для формы регистра реанимации
//getMorbusType() - Список морбусов
//getRegisterOutCaseType() - Список причин исключения из регистра
//ДЕЙСТВИЯ
//ReanimatRegisterOut() - Исключение из регистра реанимации
//ReanimatRegisterEndRP()	 -  Пометка в регистре реанимации окончания реанимационного периода

//ОТЧЁТЫ
//getAliveDead($data) - Сводная информация по началам, окончаниям и исходам реанимационных периодов


class ReanimatRegister  extends swController {

	public $inputRules = array(
		'ReanimatRegisterOut' => array(
			array(
				'field' => 'ReanimatRegister_id',
				'label' => 'ReanimatRegister_id',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ReanimatRegister_disDate',			//BOB - 23.01.2018
				'label' => 'ReanimatRegister_disDate',			//BOB - 23.01.2018
				'rules' => 'required',
				'type' => 'date'
			),				
			array(
				'field' => 'PersonRegisterOutCause_id',
				'label' => 'Причина исключения из регистра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_did',
				'label' => 'Кто исключил человека из регистра - врач',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'Кто исключил человека из регистра - ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getAliveDead' => array(
			array(
				'field' => 'BeginDate',
				'label' => 'Дата начала отчёта',
				'rules' => 'required',
				'type' => 'string'
			),		
			array(
				'field' => 'EndDate',
				'label' => 'Дата конца отчёта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'МО госпитализации',
				'rules' => '',
				'type' => 'id'
			)
		),
		'ReanimatRegisterEndRP' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);
	
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('ReanimatRegister_model', 'dbmodel');
	}

	
	/**
     * BOB - 03.11.2017
     * формирование справочников для формы регистра реанимации
	 */
	function RRW_NSI() {
		$response = $this->dbmodel->RRW_NSI();
		//	echo '<pre>' . print_r($response, 1) . '</pre>'; //BOB - 29.05.2017
		$this->ReturnData($response);
		return true;
	}
	
	
	 /**
     *  Список морбусов
	 * BOB - 18.10.2017
     */
    function getMorbusType()
    {
        $list = $this->dbmodel->getMorbusType();
        
        return $this->ReturnData($list);
    }

	 /**
     *  Список причин исключения из регистра
	 * BOB - 23.10.2017
     */
    function getRegisterOutCaseType()
    {

        $list = $this->dbmodel->getRegisterOutCaseType();
        
        return $this->ReturnData($list);
    }

	 /**
     *  Исключение из регистра реанимации
	 * BOB - 24.10.2017
     */
    function ReanimatRegisterOut()
    {
		$data = $this->ProcessInputData('ReanimatRegisterOut', true);
		if ($data === false) return false;
		$arg = array(        //BOB - 23.01.2018
			'ReanimatRegister_id' => $data['ReanimatRegister_id'],
			'ReanimatRegister_disDate' => $data['ReanimatRegister_disDate'],
			'PersonRegisterOutCause_id' => $data['PersonRegisterOutCause_id'],
			'MedPersonal_did' => $data['MedPersonal_did'],
			'Lpu_did' => $data['Lpu_did'],
			'pmUser_id' => $data['pmUser_id'] 

		);
		// 		echo '<pre> $arg = ' . print_r($arg, 1) . '</pre>'; //BOB - 20.10.2017
		// 		echo '<pre> $data = '  . print_r($data, 1) . '</pre>'; //BOB - 20.10.2017
				
        $response = $this->dbmodel->ReanimatRegisterOut($arg);
        
        return $this->ReturnData($response);
    }
	 /**
     *  Пометка в регистре реанимации окончания реанимационного периода
	 * BOB - 20.11.2017
     */
    function ReanimatRegisterEndRP()
    {
		$data = $this->ProcessInputData('ReanimatRegisterEndRP', true);
		if ($data === false) return false;
				
        $response = $this->dbmodel->ReanimatRegisterEndRP($data);
        
        return $this->ReturnData($response);
    }
	
	
	 /**
     *  Сводная информация по началам, окончаниям и исходам реанимационных периодов
	 * BOB - 31.10.2017
     */
    function getAliveDead()
    {
		$data = $this->ProcessInputData('getAliveDead', false);
		if ($data === false) return false;
		// 		echo '<pre> $data1 = '  . print_r($data, 1) . '</pre>'; //BOB - 20.10.2017
				
        $response = $this->dbmodel->getAliveDead($data);
        
        return $this->ReturnData($response);
    }

    /**
     *  Help my a to hana
     * BOB - 17.03.2018
     */
    function doHelp()
    {
				
        $response = $this->dbmodel->doHelp();
        
        return $this->ReturnData($response);
    }
	
	
	
}
