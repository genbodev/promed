<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * OnkoCtrl - контроллер для Работы режима онкоконроль

 *
 * @access       public
 * @copyright    Copyright (c) 2012 Progress
 * @author       Nigmatullin Tagir
 * @version      12.09.2014
 *  
 * @property OnkoCtrl_model $dbmodel
 */
class OnkoCtrl extends swController {

    /**
     * Описание правил для входящих параметров
     * @var array
     */
    public $inputRules = array();
    var $model_name = "OnkoCtrl_model";

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model($this->model_name, "dbmodel");
        $this->inputRules = array(
            'GetOnkoCtrlProfileJurnal' => array(
                array(
                    'field' => 'Filter',
                    'label' => 'Json строка для фильтра',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор ЛПУ',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'FirName',
                    'label' => 'Имя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SurName',
                    'label' => 'Фамилия',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SecName',
                    'label' => 'Отчество',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BirthDayRange',
                    'label' => 'Период Дата рождения',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ), 
                array(
                    'field' => 'BirthDay',
                    'label' => 'Дата рождения',
                    'rules' => '',
                    'type' => 'date'
                ), 
                array(
                    'field' => 'PeriodRange',
                    'label' => 'Период анкетирования',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'Doctor',
                    'label' => 'Врач',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'StatusOnkoProfile_id',
                    'label' => 'Статус',
                    'rules' => '',
                    'type' => 'int'
                ), 
                 array(
                    'field' => 'OnkoQuestions_id',
                    'label' => 'Результат',
                    'rules' => '',
                    'type' => 'int'
                ), 
                array(
                    'field' => 'Monitored',
                    'label' => 'Онкоконтроль',
                    'rules' => '',
                    'type' => 'int'
                ), 
                array(
                    'field' => 'Uch',
                    'label' => 'Участок',
                    'rules' => '',
                    'type' => 'string'
                ),
                 array(
                    'field' => 'Sex_id',
                    'label' => 'Пол',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OnkoType_id',
                    'label' => 'Тип данных',
                    'rules' => '',
                    'type' => 'int'
                ), 
                array(
                    'field' => 'Empty',
                    'label' => 'Empty',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'loadOnkoContrProfileFormInfo' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PersonOnkoProfile_id',
                    'label' => 'Идентификатор анкеты',
                    'rules' => '',
                    'type' => 'int'
                )
             ),
			'checkIsNeedOnkoControl' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор рабочего места врача',
					'rules' => 'trim|required',
					'type' => 'id'
				),
			),
			'updateEvnId' => array(
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор посещения',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonOnkoProfile_id',
					'label' => 'Идентификатор анкеты',
					'rules' => 'trim|required',
					'type' => 'id'
				),
			),
            'getOnkoQuestions' => array(
                array(
                    'field' => 'PersonOnkoProfile_id',
                    'label' => 'Идентификатор анкеты',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
				array(
                    'field' => 'OnkoCtrl_Date',
                    'label' => 'Идентификатор анкеты',
                    'rules' => '',
                    'type' => 'date'
                ),
				array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => 'required',
                    'type' => 'id'
                ),
             ),
             'savePersonOnkoProfile' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PersonOnkoProfile_id',
                    'label' => 'Идентификатор анкеты',
                    'rules' => '',
                    'type' => 'int'
                ),
                 array(
                    'field' => 'Profile_Date',
                    'label' => 'Дата анкетирования',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
               array(
                    'field' => 'MedStaffFact_id',
                    'label' => 'Идентификатор ',
                    'rules' => '',
                    'type' => 'int'
                ), 
                  array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
					'session_value' => 'lpu_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                  array(
                    'field' => 'Questions',
                    'label' => ' Строка идентификаторов',
                    'rules' => '',
                    'type' => 'string'
                ),
                  array(
                    'field' => 'QuestionAnswer',
                    'label' => 'Список вопросов и ответов',
                    'rules' => '',
                    'type' => 'json_array'
                )
                 
                 /*
                 array(
                    'field' => 'PeriodRange',
                    'label' => 'Период анкетирования',
                    'rules' => 'trim',
                    'type' => 'daterange'
                )
                  */
                 
                 
             ),
            'getOnkoReportSetZNO' => array(
                array(
                    'field' => 'PeriodRange',
                    'label' => 'Период анкетирования',
                    'rules' => 'trim',
                    'type' => 'daterange'
                )
                ),
            'deleteOnkoProfile' => array(
                array(
                    'field' => 'PersonOnkoProfile_id',
                    'label' => 'id анкеты',
                    'rules' => 'required|trim',
                    'type' => 'id'
                )
            ),
            'getOnkoReportSetZNO_Detail'  => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'id поликлиники',
                    'rules' => 'required|trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PeriodRange',
                    'label' => 'Период отчетности',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'Field',
                    'label' => 'Поле отчета',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                )
                
            ),
            'getOnkoReportMonitoring' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'id поликлиники',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PeriodRange',
                    'label' => 'Период анкетирования',
                    'rules' => 'trim',
                    'type' => 'daterange'
                )
                ),
            'getOnkoReportMonitoring_Detail'  => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'id поликлиники',
                    'rules' => 'required|trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PeriodRange',
                    'label' => 'Период отчетности',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'Field',
                    'label' => 'Поле отчета',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Начальный номер записи',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество возвращаемых записей',
                    'rules' => 'trim',
                    'type' => 'int'
                )
                
            ),
            'deleteOnkoProfile' => array(
                array(
                    'field' => 'PersonOnkoProfile_id',
                    'label' => 'id анкеты',
                    'rules' => 'required|trim',
                    'type' => 'id'
                )
            ),
            'GetZNO4Person' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'loadPersonOnkoProfileList' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            
        );
    }

   
    /**
     * Журнал анкет по онкоконтролю
     */
    public function GetOnkoCtrlProfileJurnal() {
		
		// log_message('debug', 'GetOnkoCtrlProfileJurnal');
		$data = $this->ProcessInputData('GetOnkoCtrlProfileJurnal', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->GetOnkoCtrlProfileJurnal($data);
		if(is_array($response)) {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		} else {
			return false;
		}
	}
    
 	/**
	 * Получение списка вопросов для анкеты по онкоконтролю
	 * И
	 */
	
    function getOnkoQuestions() {

        $data = $this->ProcessInputData('getOnkoQuestions', true); 
        $response = $this->dbmodel->getOnkoQuestions($data);
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
            return false;
        }                        
    }
    
     /**
     * Загрузка доп инфы для формы анкетирования онкоконтроля
     */
    function loadOnkoContrProfileFormInfo() {
        log_message('debug', 'loadOnkoContrProfileFormInfo');
        $data = $this->ProcessInputData('loadOnkoContrProfileFormInfo', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->loadOnkoContrProfileFormInfo($data);
        array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
    }
    
    
     /**
     * сохранение информации об анкетировании пациента
     */

    public function savePersonOnkoProfile() {
        $data = $this->ProcessInputData('savePersonOnkoProfile', true);
        if ($data === false) {
            return false;
        }

        $response = $this->dbmodel->savePersonOnkoProfile($data);

        array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        echo json_encode(array('success' => true, 'rows' => $response));
        return true;
    }
    
        /**
     * Получение Отчета "Установлено ЗНО"
     *
     */
	
    function getOnkoReportSetZNO() {
        
        $data = $this->ProcessInputData('getOnkoReportSetZNO', true); 
        log_message('debug', 'getOnkoReportSetZNO');
        $response = $this->dbmodel->getOnkoReportSetZNO($data);
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
            return false;
        }                        
    }
    
    
      /**
     * Получение Отчета "Установлено ЗНО (детализация)"
     *
     */
	
    function getOnkoReportSetZNO_Detail() {
        $data = $this->ProcessInputData('getOnkoReportSetZNO_Detail', true);
        if ($data === false) { return false; }  
        
         $listData = $this->dbmodel->getOnkoReportSetZNO_Detail($data);
         
         
        if (is_array($listData) && count($listData) > 0) {
            $val = null;
            $val = array();
            $count = 0;
            foreach ($listData as $row) {
                if (isset($row['__countOfAllRows'])) {
                    $count = $row['__countOfAllRows'];
                }
                else {
                    array_walk($row, 'ConvertFromWin1251ToUTF8');  //!!!
                    $val['data'][] = $row;
                    $count++;
                }
            }
            $val['totalCount'] = $count;
        }

        if ($val['totalCount'] == 0 && !isset($val['data'])) {
            //data обязательно должен присутствовать, иначе листалка глючит при пустом storage
            $val['data'] = array();
        }
        log_message('debug', 'totalCount = ' .$val['totalCount']);

        $this->ReturnData($val);
        }
        
        
     /**
     * Получение Отчета "Мониторинг реализации системы "Онкоконтроль"
     *
     */
	
    function getOnkoReportMonitoring() {
        
        $data = $this->ProcessInputData('getOnkoReportMonitoring', true); 
        //log_message('debug', 'getOnkoReportSetZNO');
        $response = $this->dbmodel->getOnkoReportMonitoring($data);
        if(is_array($response)) {
            $this->ProcessModelList($response, true, true)->ReturnData();
        } else {
            return false;
        }                        
    }
    
     /**
     * Получение Отчета "Мониторинг реализации системы "Онкоконтроль" (детализация)"
     *
     */
	
    function getOnkoReportMonitoring_Detail() {
        $data = $this->ProcessInputData('getOnkoReportMonitoring_Detail', true);
        if ($data === false) { return false; }  
        
       
        $response = $this->dbmodel->getOnkoReportMonitoring_Detail($data);
		if(is_array($response)) {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		} else {
			return false;
		}
        
        }
    
        /**
     * Удаление анкеты по онкоконтролю
     */
    public function deleteOnkoProfile() {
        $data = $this->ProcessInputData('deleteOnkoProfile', true);
        if ($data === false) {
            return false;
        }

        $val = array();
        $response = $this->dbmodel->deleteOnkoProfile($data);

        foreach ($response as $row) {
            array_walk($row, 'ConvertFromWin1251ToUTF8');
            $val[] = $row;
        }

        echo json_encode(array('success' => true, 'rows' => $val));
        return true;
    }
    
     /**
     * Получаем результаты анкетирования по онкоконтролю
     */
    public function GetOnkoCtrlProfileResult() {
       
        //log_message('debug', 'GetOnkoCtrlProfileResult');
        $val = array();
        $response = $this->dbmodel->GetOnkoCtrlProfileResult();

        foreach ($response as $row) {
            array_walk($row, 'ConvertFromWin1251ToUTF8');
            $val[] = $row;
        }

        Echo '{rows:' . json_encode($val) . '}';
        return true;
    }  //  End GetOnkoCtrlProfileResult  
   
     /**
     * Получение списка диагнозов по онкологии 
     *
     */
	
    function GetZNO4Person() {
        
        $data = $this->ProcessInputData('GetZNO4Person', true); 
        //log_message('debug', 'getOnkoReportSetZNO');
        $response = $this->dbmodel->GetZNO4Person($data);
        
        //$response = $this->dbmodel->loadOnkoContrProfileFormInfo($data);
        array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
        $this->ReturnData(array('data' => $response));
                         
    }
    
    
	/**
	 * Возвращает результат проверки необходимо ли заполнять анкету при сохранении посещения врачом
	 */
	public function checkIsNeedOnkoControl()
	{
		$data = $this->ProcessInputData('checkIsNeedOnkoControl', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->checkIsNeedOnkoControl($data);
		if (is_array($response)) {
			$val = array(
				'success' => true,
				'IsNeedOnkoControl' => $response['IsNeedOnkoControl']
			);
		} else {
			$val = array(
				'success' => false,
				'Error_Msg' => toUTF('Не удалось выполнить проверку необходим ли онкоконтроль')
			);
		}
		$this->ReturnData($val);
		return true;
	}
	
	/**
	 * Сохраняет идентификатор посещения в поле Evn_id таблицы onko.PersonOnkoProfile
	 */
	public function updateEvnId()
	{
		$data = $this->ProcessInputData('updateEvnId', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->updateEvnId($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка анкет
	 */
	public function loadPersonOnkoProfileList() {
		$data = $this->ProcessInputData('loadPersonOnkoProfileList', true);
		if ($data === false) return;
		$response = $this->dbmodel->loadPersonOnkoProfileList($data);

		$delIndex = -1;
		foreach($response as $index => $item) {
			if ($item['PersonOnkoProfile_id'] == -1) {
				$delIndex = $index;
				break;
			}
		}
		if ($delIndex >= 0) {
			array_splice($response, $delIndex, 1);
		}

		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}

