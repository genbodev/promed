<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * ufa_AnalizerTestFormula.php - контроллер для работы с формулами при вычислении  расчитываемых тестов АРМ лаборанта
 * https://redmine.swan.perm.ru/issues/62598
 *
 *
 * @package			AnalizerTestFormula
 * @author			Васинский Игорь 
 * @version			11.07.2016
 */

class Ufa_AnalizerTestFormula extends swController
{
    var $model = "ufa_AnalizerTestFormula_model";
   

    /**
     * constructor
     */
    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->model($this->model, 'dbmodel');

        $this->inputRules  = array(
            'getAnalizerTestFormula' =>array(
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
                    'label' => 'коды услуг (чере запятую)',
                    'rules' => 'trim',
                    'type' => 'string'
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
                    'field' => 'pmUser_id',
                    'label' => 'pmUser_id',
                    'rules' => 'trim',
                    'type' => 'id'
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
                    'field' => 'pmUser_id',
                    'label' => 'pmUser_id',
                    'rules' => 'trim',
                    'type' => 'id'
                ),                  
            ),            
        );            
    } 


    /**
     * Получение списка формул для теста, исследования, экземпляра анализатора
     */
    function getAnalizerTestFormula(){
        $data = $this->ProcessInputData('getAnalizerTestFormula', true);
        if ($data === false) { return false; }
        $response = $this->dbmodel->getAnalizerTestFormula($data);
        $this->ProcessModelList($response, true, true)->ReturnData();  
        return true;
    }   
    
    /**
     * Сохранение формулы, создание группы аргументов, сохранение аргументов (кодов услуг)
     */
    function AnalyzerTestFormula_ins(){
        $data = $this->ProcessInputData('AnalyzerTestFormula_ins', true);
        if ($data === false) { return false; }
        $response = $this->dbmodel->AnalyzerTestFormula_ins($data);
        $this->ProcessModelList($response, true, true)->ReturnData();   
        return true;
    }

    
    /**
     * Удаление формулы
     */
    function AnalizerTestFormula_del(){
        $data = $this->ProcessInputData('AnalizerTestFormula_del', true);
        if ($data === false) { return false; }
        $response = $this->dbmodel->AnalizerTestFormula_del($data);
        $this->ProcessModelList($response, true, true)->ReturnData();  
        return true;
    }     
    
    /**
     * Редактирование формулы
     */
    function AnalizerTestFormula_upd(){
        $data = $this->ProcessInputData('AnalizerTestFormula_upd', true);
        if ($data === false) { return false; }
        $response = $this->dbmodel->AnalizerTestFormula_upd($data);
        $this->ProcessModelList($response, true, true)->ReturnData();  
        return true;
    }     
}    
