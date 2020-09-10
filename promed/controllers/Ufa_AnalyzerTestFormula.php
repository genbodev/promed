<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * ufa_AnalyzerTestFormula.php - контроллер для работы с формулами при вычислении  расчитываемых тестов АРМ лаборанта
 * https://redmine.swan.perm.ru/issues/62598
 *
 *
 * @package			AnalyzerTestFormula
 * @author			Васинский Игорь 
 * @version			11.07.2016
 */

class Ufa_AnalyzerTestFormula extends swController
{
    var $model = "Ufa_AnalyzerTestFormula_model";
   
    /**
     * constructor
     */
    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->model($this->model, 'dbmodel');

        $this->inputRules  = array(
            'getAnalyzerTestFormula' =>array(
                array(
                    'field' => 'Analyzer_id',
                    'label' => 'экземпляр анализатора',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AnalyzerTest_id',
                    'label' => 'тест',
                    'rules' => 'trim',
                    'type' => 'int'
                ),                  
                array(
                    'field' => 'AnalyzerTest_pid',
                    'label' => 'исследование',
                    'rules' => 'trim',
                    'type' => 'int'
                ),                  
            ),
            'AnalyzerTestFormula_ins' => array(
                array(
                    'field' => 'Analyzer_id',
                    'label' => 'экземпляр анализатора',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AnalyzerTest_id',
                    'label' => 'тест',
                    'rules' => 'trim',
                    'type' => 'int'
                ),                  
                array(
                    'field' => 'AnalyzerTest_pid',
                    'label' => 'исследование',
                    'rules' => 'trim',
                    'type' => 'int'
                ),   
                array(
                    'field' => 'Usluga_ids',
                    'label' => 'коды услуг (через запятую)',
                    'rules' => 'trim',
                    'type' => 'string'
                ), 
                array(
                    'field' => 'AnalyzerTestFormula_Formula',
                    'label' => 'формулы',
                    'rules' => 'trim',
                    'type' => 'string'
                ),   
                array(
                    'field' => 'AnalyzerTestFormula_Comment',
                    'label' => 'описание формулы',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'pmUser_id',
                    'label' => 'pmUser_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'AnalyzerTestFormula_Code',
                    'label' => 'код формулы',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'AnalyzerTestFormula_ResultUnit',
                    'label' => 'ед.измерения',
                    'rules' => '',
                    'type' => 'string'
                ),
            ),
            'AnalyzerTestFormula_del' => array(
                array(
                    'field' => 'AnalyzerTestFormula_id',
                    'label' => 'id формулы',
                    'rules' => 'trim',
                    'type' => 'int'
                )         
            ),
            'AnalyzerTestFormulaAll_del' => array(
                array(
                    'field' => 'Analyzer_id',
                    'label' => 'id экземпляра анализатора',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AnalyzerTest_id',
                    'label' => 'id теста',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AnalyzerTest_pid',
                    'label' => 'id исследования',
                    'rules' => 'trim',
                    'type' => 'int'
                )                  
            ),            
            'AnalyzerTestFormula_upd' => array(
                array(
                    'field' => 'AnalyzerTestFormula_id',
                    'label' => 'id формулы',
                    'rules' => 'trim',
                    'type' => 'int'
                ),                   
                array(
                    'field' => 'Analyzer_id',
                    'label' => 'экземпляр анализатора',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AnalyzerTest_id',
                    'label' => 'тест',
                    'rules' => 'trim',
                    'type' => 'int'
                ),                  
                array(
                    'field' => 'AnalyzerTest_pid',
                    'label' => 'исследование',
                    'rules' => 'trim',
                    'type' => 'int'
                ),   
                array(
                    'field' => 'Usluga_ids',
                    'label' => 'коды услуг (чере запятую)',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'AnalyzerTestFormula_Code',
                    'label' => 'код формулы',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'AnalyzerTestFormula_Formula',
                    'label' => 'текст формулы',
                    'rules' => 'trim',
                    'type' => 'string'
                ),   
                array(
                    'field' => 'AnalyzerTestFormula_Comment',
                    'label' => 'описание формулы',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'AnalyzerTestFormula_ResultUnit',
                    'label' => 'ед.измерения',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'pmUser_id',
                    'label' => 'pmUser_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
            ),
            'loadAnalyzerTestGrid' => array(
                array(
                    'field' => 'AnalyzerTest_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AnalyzerTest_pid',
                    'label' => 'Родительский тест',
                    'rules' => '',
                    'type' => 'int'
                ),
                
                array(
                    'field' => 'Analyzer_id',
                    'label' => 'Анализатор',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'UslugaComplex_id',
                    'label' => 'Услуга теста',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'AnalyzerTestType_id',
                    'label' => 'Тип теста',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Unit_id',
                    'label' => 'Единица измерения',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AnalyzerWorksheetType_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'default' => 0,
                    'field' => 'start',
                    'label' => 'Номер стартовой записи',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'default' => 100,
                    'field' => 'limit',
                    'label' => 'Количество записей',
                    'rules' => '',
                    'type' => 'id'
                )               
            ),
            'checkAnalyzerTestFormula' => array(
                array(
                    'field' => 'AnalyzerTest_id',
                    'label' => 'id анализатора',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AnalyzerTest_pid',
                    'label' => 'Родительский тест',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'AnalyzerTestFormula_Code',
                    'label' => 'Код формулы',
                    'rules' => 'trim',
                    'type' => 'string'
                )
            )
        );            
    }
    
    /**
     * Загружает список тестов в исследовании для создании формулы
     */
    function loadAnalyzerTestGrid() {
        $data = $this->ProcessInputData('loadAnalyzerTestGrid', true);
        if ($data) {
            $response = $this->dbmodel->loadAnalyzerTestGrid($data);
            $this->ProcessModelMultiList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверка, может ли редактируемый тест быть расчетным
     */
    function checkAnalyzerTestFormula() {
        $data = $this->ProcessInputData('checkAnalyzerTestFormula', true);
        if ($data) {
            $response = $this->dbmodel->checkAnalyzerTestFormula($data);
            $this->ProcessModelList($response, true, true)->ReturnData();   
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение списка формул для теста, исследования, экземпляра анализатора
     */
    function getAnalyzerTestFormula(){
        $data = $this->ProcessInputData('getAnalyzerTestFormula', true);
        if ($data === false) { return false; }
        $response = $this->dbmodel->getAnalyzerTestFormula($data);
        $this->ProcessModelList($response, true, true)->ReturnData();  
        return true;
    }
    
    /**
     * Сохранение формулы, создание группы аргументов, сохранение аргументов (кодов услуг)
     */
    function AnalyzerTestFormula_ins(){
        $data = $this->ProcessInputData('AnalyzerTestFormula_ins', true);
        if ($data === false) { return false; }
        
        // если есть формула
        $result = $this->dbmodel->getAnalyzerTestFormula($data);
        if(count($result) > 0) {
            return true;
        }

        $response = $this->dbmodel->AnalyzerTestFormula_ins($data);
        $this->ProcessModelList($response, true, true)->ReturnData();   
        return true;
    }
    
    /**
     * Удаление формулы
     */
    function AnalyzerTestFormula_del(){
        $data = $this->ProcessInputData('AnalyzerTestFormula_del', true);
        if ($data === false) { return false; }
        $response = $this->dbmodel->AnalyzerTestFormula_del($data);
        $this->ProcessModelList($response, true, true)->ReturnData();  
        return true;
    }     
    
    /**
     * Удаление всех формул теста
     */
    function AnalyzerTestFormulaAll_del(){
        $data = $this->ProcessInputData('AnalyzerTestFormulaAll_del', true);
        if ($data === false) { return false; }
        $response = $this->dbmodel->AnalyzerTestFormulaAll_del($data);
        $this->ProcessModelList($response, true, true)->ReturnData();  
        return true;
    }        
    
    /**
     * Редактирование формулы
     */
    function AnalyzerTestFormula_upd(){
        $data = $this->ProcessInputData('AnalyzerTestFormula_upd', true);
        if ($data === false) { return false; }
        $response = $this->dbmodel->AnalyzerTestFormula_upd($data);
        $this->ProcessModelList($response, true, true)->ReturnData();  
        return true;
    }     
}    
